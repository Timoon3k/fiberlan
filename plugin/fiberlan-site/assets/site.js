(() => {
	const feedback = document.querySelector('#fl-form-status');
	if (feedback) feedback.focus();

	for (const nav of document.querySelectorAll('.fl-languages')) {
		const current = nav.dataset.lang;
		const hint = nav.querySelector('.fl-language-hint');
		let preference;
		try { preference = localStorage.getItem('fiberlan-language'); } catch {}
		const zone = Intl.DateTimeFormat().resolvedOptions().timeZone;
		const preferred = preference || (zone === 'Europe/Warsaw' || /^pl\b/i.test(navigator.language) ? 'pl' : 'en');
		for (const link of nav.querySelectorAll('[data-language]')) {
			link.addEventListener('click', () => {
				try { localStorage.setItem('fiberlan-language', link.dataset.language); } catch {}
			});
		}
		if (hint && preferred !== current) {
			const strip = nav.closest('header')?.querySelector('.fl-contactbar .fl-shell');
			if (strip) {
				strip.append(hint);
				hint.hidden = false;
			}
		}
	}
	const campaign = [...new URLSearchParams(location.search)].filter(([key]) => /^(utm_\w+|gclid)$/i.test(key));
	if (campaign.length) {
		document.addEventListener('click', event => {
			const link = event.target.closest?.('a[href]');
			if (!link || link.hasAttribute('download') || link.getAttribute('href').startsWith('#')) return;
			const url = new URL(link.href);
			if (url.origin !== location.origin || url.pathname.includes('/wp-admin/')) return;
			for (const [key, value] of campaign) {
				if (!url.searchParams.has(key)) url.searchParams.set(key, value);
			}
			link.href = url.href;
		}, true);
	}
	document.addEventListener('keydown', event => {
		const details = event.target.closest?.('.fl-ribbon details[open]');
		if (event.key === 'Escape' && details) {
			details.open = false;
			details.querySelector('summary').focus();
		}
	});
	const controls = document.querySelectorAll('.fl-consent-settings');
	function showConsentControls() {
		if (typeof window.revisitCkyConsent === 'function') {
			controls.forEach(control => { control.hidden = false; });
		}
	}
	controls.forEach(control => control.addEventListener('click', () => window.revisitCkyConsent?.()));
	showConsentControls();
	window.addEventListener('load', showConsentControls);
	document.addEventListener('cookieyes_banner_load', showConsentControls);
})();
