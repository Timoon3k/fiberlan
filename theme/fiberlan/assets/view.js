(() => {
	const root = document.documentElement;
	const header = document.querySelector('.wp-site-blocks > header');
	if (header && window.ResizeObserver) {
		const strip = header.querySelector('.fl-contactbar');
		const syncHeader = () => {
			const stripHeight = strip?.offsetHeight || 0;
			root.style.setProperty('--fl-strip-height', stripHeight + 'px');
			root.style.setProperty('--fl-sticky-height', header.offsetHeight - stripHeight + 'px');
		};
		const resize = new ResizeObserver(syncHeader);
		resize.observe(header);
		if (strip) resize.observe(strip);
		syncHeader();
	}

	if (!window.IntersectionObserver || !matchMedia('(prefers-reduced-motion: no-preference)').matches) return;
	const observer = new IntersectionObserver(entries => {
		for (const { target, isIntersecting } of entries) {
			if (!isIntersecting) continue;
			target.classList.add('fl-in-view');
			observer.unobserve(target);
		}
	});
	// Animate only below-the-fold items. Content is visible at all times.
	const items = document.querySelectorAll('main .fl-section > .fl-shell, main .fl-section-head, main .fl-project-intro, main .fl-contact-row, footer .fl-footer-base');
	for (const item of items) {
		if (item.getBoundingClientRect().top >= innerHeight) observer.observe(item);
	}
})();
