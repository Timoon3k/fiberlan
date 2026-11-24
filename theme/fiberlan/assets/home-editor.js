/* Editable FIBERLAN content panel for every Gutenberg page in PL and EN. */
(function () {
'use strict';
if (!window.wp || !wp.plugins || !wp.editPost?.PluginSidebar || !wp.blockEditor) return;
const h=wp.element.createElement;
const {useEffect,useState}=wp.element;
const {useSelect,useDispatch}=wp.data;
const {PanelBody,TextControl,TextareaControl,Button,Notice}=wp.components;
const {MediaUpload,MediaUploadCheck}=wp.blockEditor;
const {PluginSidebar}=wp.editPost;
function plain(value) {
  const div=document.createElement('div');div.innerHTML=String(value||'');return div.textContent||'';
}
function hasMarkup(value) {return /<\/?[a-z][^>]*>/i.test(String(value||''));}
function leaves(root) {
  const result=[];
  function walk(block) {
    if (['core/heading','core/paragraph','core/button','core/image','core/details','core/shortcode','core/spacer','core/list-item','core/video','core/gallery','core/block'].includes(block.name) || block.name.startsWith('fiberlan/')) result.push(block);
    for(const child of block.innerBlocks||[])walk(child);
  }
  walk(root);return result;
}
function sectionTitle(root,n) {
  if(root.attributes?.metadata?.name)return plain(root.attributes.metadata.name);
  function find(block) {
    if(block.name==='core/heading')return plain(block.attributes.content);
    for(const child of block.innerBlocks||[]) {const txt=find(child);if(txt)return txt;}
    return '';
  }
  return (find(root)||'Sekcja '+(n+1)).slice(0,60);
}
function Panel() {
  const blocks=useSelect(select=>select('core/block-editor')?.getBlocks()||[],[]);
  const meta=useSelect(select=>select('core/editor')?.getEditedPostAttribute('meta')||{},[]);
  const postType=useSelect(select=>select('core/editor')?.getCurrentPostType(),[]);
  const {updateBlockAttributes,selectBlock}=useDispatch('core/block-editor');
  const {editPost}=useDispatch('core/editor');
  const options=window.fiberlanEditorPanel||{};
  const [query,setQuery]=useState('');
  const language=options.language==='en'?'en':'pl';
  useEffect(()=>{
    const editor=wp.data.dispatch('core/edit-post');
    if(editor?.openGeneralSidebar)editor.openGeneralSidebar('fiberlan-content-editor/content');
  },[]);
  if(postType!=='page')return null;
  const jump=(block,label)=>h(Button,{variant:'tertiary',onClick:()=>selectBlock(block.clientId)},label||'Edytuj na podglądzie');
  function field(block,attribute,label,multiline) {
    const raw=block.attributes[attribute]||'';
    if(hasMarkup(raw))return h('div',{key:attribute,className:'fl-home-editor-field'},
      h('p',{className:'fl-home-editor-field-title'},label),
      h('p',{className:'fl-home-editor-help'},plain(raw).slice(0,95)),
      jump(block,'Edytuj z zachowaniem formatowania'));
    const Control=multiline?TextareaControl:TextControl;
    return h(Control,{key:attribute,label,value:String(raw),rows:multiline?4:undefined,
      onChange:value=>updateBlockAttributes(block.clientId,{[attribute]:value})});
  }
  function image(block) {
    const src=block.attributes.url||'';
    return h('div',{className:'fl-home-editor-field fl-home-editor-image',key:block.clientId},
      h('p',{className:'fl-home-editor-field-title'},'Zdjęcie'),
      src?h('img',{src,alt:'',className:'fl-home-editor-thumb'}):null,
      h(MediaUploadCheck,null,h(MediaUpload,{
        value:Number(block.attributes.id)||0,allowedTypes:['image'],
        onSelect:media=>{
          if(!media?.id)return;
          updateBlockAttributes(block.clientId,{
            id:media.id,url:media.url||media.source_url,
            alt:media.meta?.['_fl_alt_'+language]||media.alt||media.alt_text||'',sizeSlug:'full',
            className:String(block.attributes.className||'').split(/\s+/).filter(x=>x!=='fl-photo-placeholder'&&x!=='fl-client-cutout').join(' ')
          });
        },
        render:({open})=>h(Button,{variant:'secondary',onClick:open},src?'Zastąp zdjęcie':'Dodaj zdjęcie')
      })),
      field(block,'alt','Tekst alternatywny ALT',false),
      jump(block,'Kadr / podpis w podglądzie'));
  }
  function entry(block,n) {
    const key=block.clientId;
    switch(block.name) {
      case 'core/heading':return h('div',{key},field(block,'content','Nagłówek '+(n+1),false));
      case 'core/paragraph':return h('div',{key},field(block,'content','Opis / tekst '+(n+1),true));
      case 'core/list-item':return h('div',{key},field(block,'content','Element listy '+(n+1),true));
      case 'core/button':return h('div',{key,className:'fl-home-editor-field'},
        field(block,'text','Przycisk',false),field(block,'url','Adres przycisku',false));
      case 'core/details':return h('div',{key},field(block,'summary','Tytuł rozwijanej sekcji',false),
        jump(block,'Treść panelu'));
      case 'core/shortcode':return h('div',{key},field(block,'text','Shortcode widżetu',true));
      case 'core/spacer':return h('div',{key},field(block,'height','Wysokość odstępu',false));
      case 'core/image':return image(block);
      case 'core/video':return h('div',{key,className:'fl-home-editor-field'},
        h('p',{className:'fl-home-editor-field-title'},'Film'),
        h(MediaUploadCheck,null,h(MediaUpload,{allowedTypes:['video'],value:block.attributes.id,
          onSelect:media=>updateBlockAttributes(key,{id:media.id,src:media.url||media.source_url}),
          render:({open})=>h(Button,{variant:'secondary',onClick:open},'Wybierz / zastąp film')})),
        jump(block,'Ustawienia odtwarzania i miniatura'));
      case 'core/gallery':return h('div',{key},jump(block,'Zarządzaj galerią w podglądzie'));
      case 'core/block':return h('div',{key},jump(block,'Edytuj współdzielony wzorzec'));
      default:
        if(block.name.startsWith('fiberlan/'))return h('div',{key,className:'fl-home-editor-field'},
          h('p',{className:'fl-home-editor-field-title'},'Blok '+block.name.split('/')[1]),
          jump(block,'Otwórz ustawienia bloku'));
        return null;
    }
  }
  const groups=blocks.map((root,index)=>({id:root.clientId,title:sectionTitle(root,index),
    items:leaves(root)})).filter(group=>group.items.length);
  const filtered=groups.filter(group=>!query.trim()||[group.title,...group.items.map(b=>plain(b.attributes?.content||b.attributes?.alt||b.attributes?.text||''))].join(' ').toLowerCase().includes(query.trim().toLowerCase()));
  const seo=options.seo?h(PanelBody,{title:'SEO strony',initialOpen:false},
    h('p',{className:'fl-home-editor-help'},'Puste pola korzystają z domyślnych danych SEO.'),
    ...[['rank_math_title','Tytuł SEO','title'],['rank_math_description','Opis meta','description']].map(([key,label,defaultKey])=>
      h(TextareaControl,{key,label,rows:defaultKey==='description'?4:2,
        value:meta[key]||'',placeholder:options.seoDefaults?.[defaultKey]||'',
        onChange:value=>editPost({meta:{...meta,[key]:value}})}))):null;
  return h(PluginSidebar,{name:'content',title:'FIBERLAN · Edycja strony',icon:'edit',
    className:'fl-home-editor-sidebar'},
    h('div',{className:'fl-home-editor-panel'},
      h('h2',null,'Edytuj sekcje strony'),
      h('p',{className:'fl-home-editor-help'},
        'Wybierz sekcję, zmień tekst, zdjęcia lub linki, a następnie kliknij Zapisz w górnym pasku.'),
      h(TextControl,{label:'Szukaj sekcji lub tekstu',value:query,onChange:setQuery}),
      h('p',{className:'fl-home-editor-help'},'Język strony: '+language.toUpperCase()),
      options.managerUrl?h('a',{href:options.managerUrl,target:'_blank',rel:'noopener'},'FIBERLAN — panel zarządzania'):null,
      filtered.length?filtered.map((group,index)=>h(PanelBody,{
        key:group.id,title:(index+1)+'. '+group.title,initialOpen:index===0},
        group.items.map(entry))):h(Notice,{status:'info',isDismissible:false},'Brak bloków do edycji.'),
      seo,
      h('p',{className:'fl-home-editor-help'},'Linki wewnątrz sformatowanych tekstów oraz szczegółowe ustawienia formularza edytuj bezpośrednio w blokach, aby nie usunąć formatowania.')
    ));
}
wp.plugins.registerPlugin('fiberlan-content-editor',{render:Panel,icon:'edit'});
})();
