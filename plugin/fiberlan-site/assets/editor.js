(() => {
	const { registerBlockType, getBlockType } = wp.blocks;
	const { createElement: h, Fragment } = wp.element;
	const { InspectorControls, useBlockProps } = wp.blockEditor;
	const { PanelBody, TextControl } = wp.components;
	const labels = {
		name: 'Imię i nazwisko', company: 'Firma', email: 'E-mail', phone: 'Telefon',
		subject: 'Temat', message: 'Wiadomość', privacy: 'Etykieta potwierdzenia',
		policy: 'Link do polityki', submit: 'Przycisk', success: 'Potwierdzenie wysłania',
		invalid: 'Błąd walidacji', failure: 'Błąd wysyłki', limited: 'Limit zgłoszeń',
		label: 'Etykieta nawigacji', all: 'Wszystkie realizacje', empty: 'Brak wyników',
		suggestion: 'Sugestia zmiany języka'
	};
	for (const [name, title] of Object.entries({
		'fiberlan/contact': 'Formularz zapytania',
		'fiberlan/projects': 'Katalog realizacji',
		'fiberlan/languages': 'Przełącznik języka'
	})) {
		registerBlockType(name, {
			title,
			edit({ attributes, setAttributes }) {
				const keys = Object.keys(getBlockType(name).attributes).filter(key => key in labels);
				const props = useBlockProps({ className: 'fl-editor-preview' });
				const fields = keys.map(key => h(TextControl, {
					key, label: labels[key], value: attributes[key] ?? '', onChange: value => setAttributes({ [key]: value })
				}));
				const preview = name === 'fiberlan/contact' ? h('div', { className: 'fl-form-grid' },
					...['name', 'company', 'email', 'phone', 'subject', 'message'].map(key => h('div', { key, className: 'fl-editor-field' }, attributes[key] || labels[key])),
					h('strong', null, attributes.submit || 'Wyślij wiadomość')
				) : h('p', null, name === 'fiberlan/languages' ? 'PL / EN' : attributes.all || 'Wszystkie realizacje');
				return h(Fragment, null,
					h(InspectorControls, null, h(PanelBody, { title: 'Teksty bloku' }, ...fields)),
					h('div', props, h('strong', null, title), preview)
				);
			},
			save: () => null
		});
	}
})();
