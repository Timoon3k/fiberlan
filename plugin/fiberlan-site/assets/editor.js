(() => {
	const { registerBlockType, registerBlockVariation } = wp.blocks;
	const { createElement: h, Fragment, useState } = wp.element;
	const { RichText, useBlockProps, URLInput } = wp.blockEditor;
	const { Button, SelectControl, Spinner } = wp.components;
	// Identify the existing native groups in List View without rewriting saved posts.
	for (const [className, title] of Object.entries({
		'fl-hero': 'Wprowadzenie', 'fl-subhero': 'Wprowadzenie podstrony',
		'fl-experience': 'Pasek doświadczenia', 'fl-ribbon': 'Doświadczenie i Ribbon',
		'fl-services': 'Oferta usług', 'fl-projects': 'Realizacje międzynarodowe',
		'fl-contact': 'Zaproszenie do kontaktu', 'fl-service-card': 'Karta usługi',
		'fl-equipment-item': 'Pozycja katalogu sprzętu', 'fl-form-panel': 'Zapytanie ofertowe'
	})) {
		registerBlockVariation('core/group', {
			name: className, title, scope: ['block'], attributes: { className },
			isActive: attrs => (attrs.className || '').split(/\s+/).includes(className)
		});
	}

	// Save plain strings in the existing attributes. RichText passes escaped HTML.
	function Copy({ field, caption, tagName = 'span', className, attributes, defaults, setAttributes }) {
		const text = attributes[field] ?? defaults[field] ?? '';
		return h(RichText, {
			tagName, className, 'aria-label': caption, 'data-fl-copy': field,
			value: wp.richText.toHTMLString({ value: wp.richText.create({ text }) }),
			allowedFormats: [], disableLineBreaks: true, placeholder: defaults[field] || caption,
			onChange: html => setAttributes({ [field]: wp.richText.getTextContent(wp.richText.create({ html })) })
		});
	}

	function Destination({ field, label, attributes, defaults, setAttributes }) {
		return h('label', { className: 'fl-editor-url' }, label,
			h(URLInput, {
				value: attributes[field] ?? defaults[field] ?? '',
				onChange: url => setAttributes({ [field]: url })
			})
		);
	}

	const fieldLabels = {
		name: 'Imię i nazwisko', company: 'Firma', email: 'E-mail', phone: 'Telefon',
		subject: 'Temat', message: 'Wiadomość'
	};
	function Form({ copy, destination }) {
		const [status, show] = useState('success');
		const required = ['name', 'email', 'subject', 'message'];
		const states = { success: 'Potwierdzenie', invalid: 'Błąd walidacji', failure: 'Błąd wysyłki', limited: 'Limit wysyłek' };
		return h(Fragment, null,
			h('div', { className: 'fl-form-grid' }, ...Object.entries(fieldLabels).map(([key, caption]) =>
				h('div', { key, className: 'fl-field fl-field-' + key },
					h('div', null, copy(key, caption), required.includes(key) && ' *'),
					h('span', { className: 'fl-editor-field', 'aria-hidden': true })
				)
			)),
			copy('privacy', 'Treść potwierdzenia prywatności', 'p', 'fl-form-privacy'),
			copy('policy', 'Tekst linku do polityki', 'p'),
			destination('policyUrl', 'Adres polityki prywatności'),
			copy('submit', 'Tekst przycisku wysyłki', 'span', 'wp-element-button'),
			h('div', { className: 'fl-editor-states' },
				h(SelectControl, {
					label: 'Podgląd komunikatu po wysłaniu', value: status,
					options: Object.entries(states).map(([value, label]) => ({ value, label })), onChange: show
				}),
				copy(status, states[status], 'p', 'fl-form-status ' + (status === 'success' ? 'is-success' : 'is-error'))
			)
		);
	}

	function Projects({ parent, config, copy }) {
		const [page, setPage] = useState(1);
		const { records, isResolving, totalPages } = wp.coreData.useEntityRecords('postType', 'page', {
			parent, page, status: 'publish', per_page: 20, orderby: 'menu_order', order: 'asc', _fields: 'id,title'
		});
		return h(Fragment, null,
			copy('label', 'Nazwa nawigacji kategorii', 'p'),
			copy('all', 'Filtr wszystkich realizacji', 'span', 'fl-editor-filter'),
			isResolving ? h(Spinner) : h('ul', { className: 'fl-editor-projects' }, ...(records || []).map(project =>
				h('li', { key: project.id }, h('a', {
					href: wp.url.addQueryArgs(config.admin + 'post.php', { post: project.id, action: 'edit' }),
					target: '_blank', rel: 'noopener'
				}, wp.htmlEntities.decodeEntities(project.title.rendered)))
			)),
			totalPages > 1 && h('div', null,
				h(Button, { disabled: page === 1, onClick: () => setPage(page - 1) }, 'Poprzednie'),
				h(Button, { disabled: page >= totalPages, onClick: () => setPage(page + 1) }, 'Następne')
			),
			h('details', { className: 'fl-editor-states' }, h('summary', null, 'Podgląd pustej kategorii'),
				copy('empty', 'Komunikat pustej kategorii', 'p')),
			config.projectParents.includes(parent) && h('form', {
				action: config.admin + 'admin-post.php', method: 'post', target: '_blank', rel: 'noopener'
			}, ...Object.entries({ action: 'fl_create_project', parent, _wpnonce: config.newProjectNonce }).map(([name, value]) =>
				h('input', { key: name, type: 'hidden', name, value })
			), h(Button, { type: 'submit', variant: 'secondary' }, 'Dodaj realizację')),
			h('a', { href: config.admin + 'edit-tags.php?taxonomy=fl_sector&post_type=page', target: '_blank', rel: 'noopener' }, 'Edytuj nazwy kategorii')
		);
	}

	function Edit({ name, attributes, setAttributes, isSelected, context }) {
		const { config, language, postId } = wp.data.useSelect(select => {
			const config = window.fiberlanEditor;
			const editor = select('core/editor');
			const entity = String(editor?.getCurrentPostId() || '');
			const template = editor?.getEditedPostAttribute('template');
			const en = /-en$/.test(entity) || ['page-en', 'project-en'].includes(template);
			return { config, language: en ? 'en' : config?.language, postId: context?.postId };
		}, [context?.postId]);
		const props = useBlockProps({ className: 'fl-editor-preview fl-editor-' + name.split('/')[1] });
		if (!config) return h('div', props, wp.blocks.getBlockType(name).title);
		const block = name.split('/')[1];
		const fields = { attributes, setAttributes, defaults: config.copy[language || 'pl'][block] };
		const copy = (field, caption, tagName, className) => h(Copy, { key: field, field, caption, tagName, className, ...fields });
		const destination = (field, label) => isSelected && h(Destination, { field, label, ...fields });
		let content;
		switch (block) {
			case 'contact':
				content = h(Form, { copy, destination });
				break;
			case 'projects':
				content = postId && context?.postType === 'page'
					? h(Projects, { parent: postId, config, copy })
					: h(Fragment, null, copy('label', 'Nazwa katalogu'), copy('all', 'Wszystkie realizacje'), copy('empty', 'Pusta kategoria'));
				break;
			case 'languages':
				content = h(Fragment, null, copy('label', 'Nazwa nawigacji językowej', 'p'),
					copy('plText', 'Etykieta języka polskiego'), ' / ', copy('enText', 'Etykieta języka angielskiego'),
					copy('suggestion', 'Sugestia zmiany języka', 'p'));
				break;
			case 'breadcrumbs':
				content = h(Fragment, null, copy('label', 'Nazwa ścieżki nawigacji', 'p'),
					copy('home', 'Etykieta strony głównej'), ' / ', h('span', null, 'Tytuł bieżącej strony'));
				break;
			case 'service-links':
				content = h(Fragment, null, copy('heading', 'Nagłówek spisu usług', 'h2'),
					h('p', null, 'Pozycje spisu pochodzą z nagłówków sekcji na stronie Usługi.'));
				break;
			case 'not-found':
				content = h(Fragment, null, copy('eyebrow', 'Oznaczenie błędu', 'p'), copy('heading', 'Tytuł strony błędu', 'h1'),
					copy('body', 'Opis błędu', 'p'), copy('button', 'Tekst przycisku', 'span', 'wp-element-button'),
					destination('url', 'Adres przycisku'));
				break;
			case 'consent':
				content = copy('label', 'Tekst przycisku ustawień zgód', 'span', 'fl-editor-filter');
				break;
		}
		return h('div', props, content);
	}

	for (const block of ['contact', 'projects', 'languages', 'breadcrumbs', 'service-links', 'not-found', 'consent']) {
		registerBlockType('fiberlan/' + block, { edit: Edit, save: () => null });
	}

	// Footers saved before 4.1 can still edit their original CookieYes button in place.
	function legacyConsent(html) {
		if (!html?.includes('fl-consent-settings')) return null;
		const doc = new DOMParser().parseFromString(html, 'text/html');
		const button = doc.body.firstElementChild;
		return doc.body.childNodes.length === 1 && button?.matches('button.fl-consent-settings') && !button.children.length ? button : null;
	}
	wp.hooks.addFilter('editor.BlockEdit', 'fiberlan/legacy-consent', Original => function LegacyEdit(props) {
		return props.name === 'core/html' ? h(LegacyHtml, { Original, props }) : h(Original, props);
	});

	function LegacyHtml({ Original, props }) {
		const block = wp.data.useSelect(select => select('core/block-editor').getBlock(props.clientId), [props.clientId]);
		const html = props.attributes.content ?? (block && wp.blocks.getBlockContent(block));
		const button = legacyConsent(html?.trim());
		if (!button) return h(Original, props);
		function updateLabel({ label }) {
			button.textContent = label;
			// WordPress 7.1 serializes Custom HTML as inner content. Earlier versions use an attribute.
			if (Array.isArray(block.innerContent)) {
				wp.data.dispatch('core/block-editor').updateBlock(props.clientId, { innerContent: [button.outerHTML] });
			} else {
				props.setAttributes({ content: button.outerHTML });
			}
		}
		return h(LegacyButton, { label: button.textContent, updateLabel });
	}

	function LegacyButton({ label, updateLabel }) {
		const props = useBlockProps({ className: 'fl-editor-preview' });
		return h('div', props, h(Copy, {
			field: 'label', caption: 'Tekst przycisku ustawień zgód', attributes: { label }, defaults: {},
			setAttributes: updateLabel
		}));
	}
})();
