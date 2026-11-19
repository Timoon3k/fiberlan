(() => {
	const { registerBlockType, getBlockType, registerBlockVariation } = wp.blocks;
	const { createElement: h, Fragment } = wp.element;
	const { InspectorControls, useBlockProps } = wp.blockEditor;
	const { PanelBody, TextControl, Spinner } = wp.components;
	const labels = {
		name: 'Imię i nazwisko', company: 'Firma', email: 'E-mail', phone: 'Telefon',
		subject: 'Temat', message: 'Wiadomość', privacy: 'Etykieta potwierdzenia',
		policy: 'Link do polityki', submit: 'Przycisk', success: 'Potwierdzenie wysłania',
		invalid: 'Błąd walidacji', failure: 'Błąd wysyłki', limited: 'Limit zgłoszeń',
		label: 'Etykieta nawigacji', all: 'Wszystkie realizacje', empty: 'Brak wyników',
		suggestion: 'Sugestia zmiany języka'
	};
	const messages = ['success', 'invalid', 'failure', 'limited'];

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

	function ProjectList({ parent, empty }) {
		const { records, isResolving } = wp.coreData.useEntityRecords('postType', 'page', {
			parent, status: 'publish', per_page: 100, orderby: 'menu_order', order: 'asc', _fields: 'id,title'
		});
		if (isResolving) return h(Spinner);
		if (!records?.length) return h('p', null, empty || 'Tutaj pojawią się opublikowane strony potomne tej realizacji.');
		return h('ul', { className: 'fl-editor-projects' }, ...records.map(project =>
			h('li', { key: project.id }, wp.htmlEntities.decodeEntities(project.title.rendered))
		));
	}

	function FormPreview({ attributes }) {
		const required = ['name', 'email', 'subject', 'message'];
		return h(Fragment, null,
			h('div', { className: 'fl-form-grid' },
				...['name', 'company', 'email', 'phone', 'subject', 'message'].map(key =>
					h('div', { key, className: 'fl-field fl-field-' + key },
						h('span', null, (attributes[key] || labels[key]) + (required.includes(key) ? ' *' : '')),
						h('span', { className: 'fl-editor-field', 'aria-hidden': true })
					)
				)
			),
			h('p', null, attributes.privacy),
			h('strong', null, attributes.submit || 'Wyślij wiadomość')
		);
	}

	for (const [name, title] of Object.entries({
		'fiberlan/contact': 'Formularz zapytania',
		'fiberlan/projects': 'Katalog realizacji',
		'fiberlan/languages': 'Przełącznik języka',
		'fiberlan/breadcrumbs': 'Ścieżka nawigacji',
		'fiberlan/service-links': 'Spis usług',
		'fiberlan/not-found': 'Strona błędu 404'
	})) {
		registerBlockType(name, {
			title,
			edit({ attributes, setAttributes, context }) {
				const keys = Object.keys(getBlockType(name).attributes || {}).filter(key => key in labels);
				const props = useBlockProps({ className: 'fl-editor-preview' });
				const panel = (title, fields, initialOpen) => fields.length > 0 && h(PanelBody, { title, initialOpen },
					...fields.map(key => h(TextControl, {
						key, label: labels[key], value: attributes[key] ?? '', onChange: value => setAttributes({ [key]: value })
					}))
				);
				let preview;
				if (name === 'fiberlan/contact') {
					preview = h(FormPreview, { attributes });
				} else if (name === 'fiberlan/projects' && context.postType === 'page' && context.postId) {
					preview = h(ProjectList, { parent: context.postId, empty: attributes.empty });
				} else if (name === 'fiberlan/languages') {
					preview = h('span', null, 'PL / EN');
				}
				return h(Fragment, null,
					keys.length > 0 && h(InspectorControls, null,
						panel('Teksty bloku', keys.filter(key => !messages.includes(key)), true),
						panel('Komunikaty po wysłaniu', keys.filter(key => messages.includes(key)), false)
					),
					h('div', props, h('strong', null, title), preview)
				);
			},
			save: () => null
		});
	}
})();
