(() => {
	const root = document.documentElement;
	const header = document.querySelector('.wp-site-blocks > header');
	const row = header?.querySelector('.fl-nav-row');
	if (row && window.ResizeObserver) {
		const strip = header.querySelector('.fl-contactbar');
		const syncHeader = () => {
			root.style.setProperty('--fl-strip-height', (strip?.offsetHeight || 0) + 'px');
			root.style.setProperty('--fl-sticky-height', row.offsetHeight + 'px');
		};
		const resize = new ResizeObserver(syncHeader);
		resize.observe(row);
		if (strip) resize.observe(strip);
		syncHeader();
	}

	const motion = matchMedia('(prefers-reduced-motion: no-preference)');
	const footerLink = document.querySelector('footer .fl-back-top a[href="#fl-page-top"]');
	const top = document.getElementById('fl-page-top');
	if (footerLink && top) {
		const link = document.createElement('a');
		link.className = 'fl-floating-top';
		link.href = footerLink.hash;
		link.setAttribute('aria-label', footerLink.getAttribute('aria-label') || footerLink.textContent.trim());
		footerLink.closest('footer').append(link);

		let footerVisible = false;
		let pending = false;
		function update() {
			pending = false;
			link.classList.toggle('is-visible', scrollY >= 600 && !footerVisible);
		}
		window.addEventListener('scroll', () => {
			if (pending) return;
			pending = true;
			requestAnimationFrame(update);
		}, { passive: true });
		if (window.IntersectionObserver) {
			const footerObserver = new IntersectionObserver(([entry]) => {
				footerVisible = entry.isIntersecting;
				update();
			});
			footerObserver.observe(footerLink);
		}
		window.addEventListener('pageshow', update);
		update();
		link.addEventListener('click', event => {
			if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
			event.preventDefault();
			top.focus({ preventScroll: true });
			window.scrollTo({ top: 0, behavior: motion.matches ? 'smooth' : 'instant' });
		});
	}

	if (!window.IntersectionObserver || !motion.matches) return;
	const observer = new IntersectionObserver(entries => {
		for (const { target, isIntersecting } of entries) {
			if (!isIntersecting) continue;
			target.classList.add('fl-in-view');
			observer.unobserve(target);
		}
	}, { rootMargin: '0px 0px -70px 0px' });
	const items = document.querySelectorAll([
		'main .fl-section > .fl-shell:not(.fl-split,.fl-contact-layout):not(:has(.fl-sector-grid,.fl-equipment-item,.fl-case-grid))',
		'main .fl-section-head', 'main .fl-project-intro', 'main .fl-contact-row',
		'main :is(.fl-service-grid,.fl-project-grid,.fl-case-grid,.fl-sector-grid) > *',
		'main .fl-split > :is(.fl-split-copy,.fl-section-image)', 'main .fl-equipment-item > *',
		'main :is(.fl-milestone,.fl-ribbon-copy,.fl-contact-sidebar,.fl-form-panel,.fl-service-index)'
	].join(','));
	for (const item of items) {
		if (item.getBoundingClientRect().top >= innerHeight * .6) observer.observe(item);
	}
})();
