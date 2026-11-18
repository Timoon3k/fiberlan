(() => {
	const feedback = document.querySelector('#fl-form-status');
	if (feedback) feedback.focus();

	const nav = document.querySelector('.fl-languages');
	if (nav) {
		const current = nav.dataset.lang;
		const hint = nav.querySelector('.fl-language-hint');
		let preference;
		try { preference = localStorage.getItem('fiberlan-language'); } catch {}
		const zone = Intl.DateTimeFormat().resolvedOptions().timeZone;
		const preferred = preference || (zone === 'Europe/Warsaw' || /^pl\b/i.test(navigator.language) ? 'pl' : 'en');
		if (hint && preferred !== current) hint.hidden = false;
		nav.addEventListener('click', event => {
			const link = event.target.closest('[data-language]');
			if (!link) return;
			try { localStorage.setItem('fiberlan-language', link.dataset.language); } catch {}
			const url = new URL(link.href);
			for (const [key, value] of new URLSearchParams(location.search)) {
				if (/^(utm_\w+|gclid)$/i.test(key)) url.searchParams.set(key, value);
			}
			link.href = url.href;
		});
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
