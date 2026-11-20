(() => {
	const root = document.documentElement;
	const header = document.querySelector('.wp-site-blocks > header');
	const row = header?.querySelector('.fl-nav-row');
	if (row) {
		const strip = header.querySelector('.fl-contactbar');
		const syncHeader = () => {
			root.style.setProperty('--fl-strip-height', (strip?.offsetHeight || 0) + 'px');
			root.style.setProperty('--fl-sticky-height', row.offsetHeight + 'px');
		};
		if (window.ResizeObserver) {
			const resize = new ResizeObserver(syncHeader);
			resize.observe(row);
			if (strip) resize.observe(strip);
		} else {
			window.addEventListener('resize', syncHeader);
		}
		syncHeader();
	}

	const motion = matchMedia('(prefers-reduced-motion: no-preference)');
	const link = document.querySelector('.fl-floating-top');
	const top = document.getElementById('fl-page-top');
	if (link && top) {
		let pending = false;
		function update() {
			pending = false;
			link.classList.toggle('is-visible', scrollY >= 600);
		}
		window.addEventListener('scroll', () => {
			if (pending) return;
			pending = true;
			requestAnimationFrame(update);
		}, { passive: true });
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
			target.classList.replace('fl-reveal-pending', 'fl-in-view');
			observer.unobserve(target);
		}
	});
	const items = document.querySelectorAll([
		'main .fl-section > .fl-shell:not(.fl-split,.fl-contact-layout):not(:has(.fl-sector-grid,.fl-equipment-item,.fl-case-grid))',
		'main .fl-section-head', 'main .fl-project-intro', 'main .fl-contact-row', 'main .fl-reviews-layout',
		'main :is(.fl-service-grid,.fl-project-grid,.fl-case-grid,.fl-sector-grid) > *',
		'main .fl-split > :is(.fl-split-copy,.fl-section-image)', 'main .fl-equipment-item > *',
		'main :is(.fl-milestone,.fl-ribbon-copy,.fl-contact-sidebar,.fl-service-index)',
		'main .fl-form :is(.fl-form-privacy,.wp-element-button)',
		'main .fl-project-report :is(.wp-block-image,.wp-block-gallery)'
	].join(','));
	// Measure before applying styles so the initial pass does not alternate reads and writes.
	const offscreen = Array.from(items).filter(item => item.getBoundingClientRect().top >= innerHeight);
	for (const item of offscreen) {
		if (item.parentElement.closest('.fl-reveal-pending')) continue;
		item.classList.add('fl-reveal-pending');
		observer.observe(item);
	}
})();
