/* Native Gutenberg sidebar for the two FIBERLAN home pages. */
(function () {
  'use strict';
  if (!window.wp || !wp.plugins || !wp.editPost || !wp.blockEditor) return;

  const el = wp.element.createElement;
  const Fragment = wp.element.Fragment;
  const { useEffect, useRef } = wp.element;
  const { useSelect, useDispatch } = wp.data;
  const { TextControl, TextareaControl, Button, Notice } = wp.components;
  const { MediaUpload, MediaUploadCheck } = wp.blockEditor;
  const { PluginSidebar } = wp.editPost;

  function hasClass(block, className) {
    return String(block?.attributes?.className || '').split(/\s+/).includes(className);
  }

  function findHero(blocks) {
    for (const block of blocks || []) {
      if (block.name === 'core/group' && hasClass(block, 'fl-hero')) return block;
      const nested = findHero(block.innerBlocks);
      if (nested) return nested;
    }
    return null;
  }

  function heroTargets(hero) {
    const found = {};
    function walk(block) {
      if (block.name === 'core/paragraph' && hasClass(block, 'fl-eyebrow')) found.eyebrow = block;
      if (block.name === 'core/paragraph' && hasClass(block, 'fl-lead')) found.lead = block;
      if (block.name === 'core/heading' && Number(block.attributes.level || 2) === 1) found.heading = block;
      if (block.name === 'core/image' && hasClass(block, 'fl-hero-figure')) found.image = block;
      if (block.name === 'core/buttons' && hasClass(block, 'fl-actions')) {
        const buttons = (block.innerBlocks || []).filter(item => item.name === 'core/button');
        found.primary = buttons[0];
        found.secondary = buttons[1];
      }
      (block.innerBlocks || []).forEach(walk);
    }
    walk(hero);
    return found;
  }

  function plainText(value) {
    if (value == null) return '';
    const box = document.createElement('div');
    box.innerHTML = String(value);
    return box.textContent || '';
  }

  function HomeSidebar() {
    const targets = useSelect(select => {
      const blocks = select('core/block-editor')?.getBlocks() || [];
      const hero = findHero(blocks);
      return hero ? heroTargets(hero) : null;
    }, []);
    const { updateBlockAttributes, selectBlock } = useDispatch('core/block-editor');
    const initialized = useRef(false);

    useEffect(() => {
      if (initialized.current) return;
      initialized.current = true;
      const editor = wp.data.dispatch('core/edit-post');
      if (editor && typeof editor.openGeneralSidebar === 'function') {
        editor.openGeneralSidebar('fiberlan-home/hero');
      }
    }, []);

    function field(block, label, attribute, multiline) {
      if (!block) return null;
      return el(multiline ? TextareaControl : TextControl, {
        key: label,
        label,
        value: plainText(block.attributes[attribute]),
        rows: multiline ? 4 : undefined,
        onChange: value => updateBlockAttributes(block.clientId, { [attribute]: value })
      });
    }

    function buttonFields(block, prefix) {
      if (!block) return null;
      return el(Fragment, { key: prefix },
        el('h3', { className: 'fl-home-editor-heading' }, prefix),
        field(block, 'Tekst przycisku', 'text'),
        field(block, 'Adres linku (URL lub #kotwica)', 'url')
      );
    }

    function imageFields(block) {
      if (!block) return null;
      const attachmentId = Number(block.attributes.id) || 0;
      const photo = block.attributes.url;
      return el('div', { className: 'fl-home-editor-image' },
        el('h3', { className: 'fl-home-editor-heading' }, 'Zdjęcie urządzenia'),
        photo ? el('img', { src: photo, alt: '', className: 'fl-home-editor-thumb' }) : null,
        el(MediaUploadCheck, null,
          el(MediaUpload, {
            value: attachmentId,
            allowedTypes: ['image'],
            onSelect: media => {
              if (!media || !media.id) return;
              updateBlockAttributes(block.clientId, {
                id: Number(media.id),
                url: media.url || media.source_url,
                alt: media.alt || media.alt_text || '',
                sizeSlug: 'full'
              });
            },
            render: ({ open }) => el(Button, { variant: 'secondary', onClick: open },
              photo ? 'Zmień zdjęcie' : 'Wybierz zdjęcie')
          })
        ),
        field(block, 'Tekst alternatywny (ALT)', 'alt'),
        el(Button, {
          variant: 'tertiary',
          onClick: () => selectBlock(block.clientId)
        }, 'Zaznacz blok zdjęcia w podglądzie')
      );
    }

    return el(PluginSidebar, {
      name: 'hero',
      title: 'FIBERLAN · Strona główna',
      icon: 'edit',
      className: 'fl-home-editor-sidebar'
    },
      el('div', { className: 'fl-home-editor-panel' },
        el('h2', null, 'Edycja strony głównej'),
        el('p', { className: 'fl-home-editor-help' },
          'Zmieniaj pola tutaj. Zmiany pojawiają się w blokach strony. Kliknij Zapisz w górnym pasku Gutenberga, aby je opublikować.'),
        !targets ? el(Notice, { status: 'warning', isDismissible: false },
          'Nie znaleziono sekcji Wprowadzenie na tej stronie. Otwórz widok listy bloków i sprawdź strukturę strony.') : null,
        targets ? el(Fragment, null,
          field(targets.eyebrow, 'Nadtytuł', 'content'),
          field(targets.heading, 'Nagłówek H1', 'content'),
          field(targets.lead, 'Opis', 'content', true),
          imageFields(targets.image),
          buttonFields(targets.primary, 'Pierwszy przycisk'),
          buttonFields(targets.secondary, 'Drugi przycisk'),
          el('p', { className: 'fl-home-editor-help' },
            'Pozostałe sekcje, zdjęcia i układ edytujesz natywnymi blokami. Otwórz Widok listy w lewym górnym rogu.')
        ) : null
      )
    );
  }

  wp.plugins.registerPlugin('fiberlan-home', { render: HomeSidebar, icon: 'edit' });
})();
