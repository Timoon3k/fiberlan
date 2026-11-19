(() => {
	const result = document.querySelector('[data-fiberlan-lead]');
	if (!result) return;

	// Remove the one-time receipt before this page is recorded by campaign tags.
	const url = new URL(location.href);
	url.searchParams.delete('received');
	history.replaceState(history.state, '', url.href);

	let emitted = false;
	function recordLead(consent) {
		if (emitted) return;
		const categories = consent?.categories;
		const accepted = Array.isArray(consent?.accepted) ? consent.accepted : [];
		const analytics = accepted.includes('analytics') || categories?.analytics === true;
		const advertising = accepted.includes('advertisement') || categories?.advertisement === true;
		if (!analytics && !advertising) return;

		emitted = true;
		(window.dataLayer ||= []).push({
			event: 'fiberlan_lead',
			form_id: 'fiberlan_contact',
			form_language: result.dataset.fiberlanLead,
			analytics_consent: analytics,
			advertising_consent: advertising
		});
	}

	document.addEventListener('cookieyes_consent_update', event => recordLead(event.detail));
	const readConsent = () => recordLead(window.getCkyConsent?.());
	readConsent();
	document.addEventListener('cookieyes_banner_load', readConsent);
	window.addEventListener('load', readConsent, { once: true });
})();
