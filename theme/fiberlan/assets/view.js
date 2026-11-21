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


	// Use the header's configured identity and translated links inside the native dialog.
	const languageNav = row?.querySelector('.fl-languages');
	const mobileContent = row?.querySelector('.fl-nav .wp-block-navigation__responsive-container-content');
	const englishMenu = root.lang.toLowerCase().startsWith('en');
	if (mobileContent) {
		const brand = document.createElement('div');
		brand.className = 'fl-menu-brand';
		const logo = row.querySelector('.fl-brand .wp-block-site-logo img');
		if (logo) {
			const image = logo.cloneNode(false);
			image.removeAttribute('id');
			brand.append(image);
		} else {
			brand.textContent = row.querySelector('.fl-brand .wp-block-site-title')?.textContent || '';
		}
		mobileContent.before(brand);
		const overlay = mobileContent.closest('.wp-block-navigation__responsive-container');
		const dialog = mobileContent.closest('.wp-block-navigation__responsive-dialog');
		const close = dialog?.querySelector('.wp-block-navigation__responsive-container-close');
		if (overlay && close) {
			// Touch browsers can retarget a tap on the narrow backdrop to the adjacent list.
			let pressedBackdrop = false;
			overlay.addEventListener('pointerdown', event => {
				pressedBackdrop = !dialog.contains(document.elementFromPoint(event.clientX, event.clientY));
			});
			overlay.addEventListener('click', event => {
				const dismiss = !dialog.contains(event.target) || (event.detail > 0 && pressedBackdrop);
				pressedBackdrop = false;
				if (!overlay.classList.contains('is-menu-open') || !dismiss) return;
				event.preventDefault();
				event.stopPropagation();
				close.click();
			}, true);
		}
	}
	if (languageNav && mobileContent && !mobileContent.querySelector('.fl-mobile-locales')) {
		const switcher = document.createElement('div');
		switcher.className = 'fl-mobile-locales';
		const label = document.createElement('span');
		label.textContent = englishMenu ? 'Language' : 'Język';
		switcher.append(label);
		for (const original of languageNav.querySelectorAll('a[data-language]:not(.fl-language-hint)')) {
			const link = original.cloneNode(true);
			link.addEventListener('click', () => {
				try { localStorage.setItem('fiberlan-language', link.dataset.language); } catch {}
			});
			switcher.append(link);
		}
		mobileContent.append(switcher);
	}
	const office = header?.querySelector('.fl-contactbar a[href^="tel:"]');
	if (mobileContent && office) {
		const phone = document.createElement('a');
		phone.className = 'fl-menu-phone';
		phone.href = office.href;
		const number = document.createElement('strong');
		number.textContent = office.textContent.trim() || office.getAttribute('href').slice(4);
		phone.append(englishMenu ? 'Call the office' : 'Zadzwoń do biura', number);
		mobileContent.append(phone);
	}

	// A focusable, icon-free tooltip: hover or keyboard focus on desktop,
	// touch/click toggles the same description on mobile.
	const deviceTriggers = [...document.querySelectorAll('.fl-device-trigger')];
	for (const trigger of deviceTriggers) {
		trigger.addEventListener('click', () => {
			const opening = trigger.getAttribute('aria-expanded') !== 'true';
			for (const item of deviceTriggers) {
				item.classList.remove('is-open');
				item.setAttribute('aria-expanded', 'false');
			}
			if (opening) {
				trigger.classList.add('is-open');
				trigger.setAttribute('aria-expanded', 'true');
			}
		});
		trigger.addEventListener('keydown', event => {
			if (event.key !== 'Escape') return;
			trigger.classList.remove('is-open');
			trigger.setAttribute('aria-expanded', 'false');
			trigger.blur();
		});
	}
	document.addEventListener('click', event => {
		if (deviceTriggers.some(trigger => trigger.contains(event.target))) return;
		for (const trigger of deviceTriggers) {
			trigger.classList.remove('is-open');
			trigger.setAttribute('aria-expanded', 'false');
		}
	});


	// Localise only Trustindex's display labels on English pages.
	// Customer-authored review content remains in the original language.
	if ((document.documentElement.lang || '').toLowerCase().startsWith('en')) {
		const reviewWidget = document.querySelector('.fl-reviews-widget');
		if (reviewWidget) {
			const translations = new Map([
				['DOSKONAŁA', 'EXCELLENT'],
				['Doskonała', 'Excellent'],
				['Na podstawie', 'Based on'],
				['opinii', 'reviews'],
				['Opublikowano na Google', 'Posted on Google'],
				['Potwierdzono przez:', 'Verified by:'],
				['Potwierdzono przez', 'Verified by'],
				['Dowiedz się więcej', 'Learn more'],
				['Trustindex sprawdza, czy pierwotnym źródłem recenzji jest Google.',
					'Trustindex checks whether Google is the original source of the review.'],
				['Trustindex zweryfikował, że te opinie zostały opublikowane w Profilu Firmy w Google należącym do FIBERLAN SC.',
					'Trustindex verified that these reviews were published on the FIBERLAN SC Google Business Profile.']
			]);
			const localise = () => {
				const walker = document.createTreeWalker(reviewWidget, NodeFilter.SHOW_TEXT);
				let node;
				while ((node = walker.nextNode())) {
					if (node.parentElement?.closest('.ti-review-content,.ti-review-text,.ti-review-body,blockquote')) continue;
					const old = node.nodeValue;
					const phrase = old.trim();
					let translated = translations.get(phrase);
					if (!translated) {
						const count = phrase.match(/^(\d+)\s+opinii$/);
						if (count) translated = count[1] + ' reviews';
					}
					if (translated) node.nodeValue = old.replace(phrase, translated);
				}
			};
			localise();
			const observer = new MutationObserver(localise);
			observer.observe(reviewWidget, { childList: true, subtree: true });
		}
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

	const main = document.querySelector('main');
	if (!main) return;
	main.classList.add('fl-timed-content');

	// Bring the operator nameplates into focus together when their section enters view.
	const badges = main.querySelector('.fl-operator-badges');
	if (badges && 'IntersectionObserver' in window) {
		const observer = new IntersectionObserver(entries => {
			if (!entries.some(entry => entry.isIntersecting)) return;
			badges.classList.add('fl-badges-visible');
			observer.disconnect();
		});
		observer.observe(badges);
	}

	const portfolio = main.querySelector('#portfolio');
	if (!portfolio || !window.fetch || !window.AbortController) return;
	const status = document.createElement('p');
	status.className = 'screen-reader-text';
	status.setAttribute('role', 'status');
	portfolio.after(status);

	let displayed = location.pathname + location.search;
	let activeRequest;
	async function filterProjects(url, push) {
		activeRequest?.abort();
		const request = new AbortController();
		activeRequest = request;
		portfolio.setAttribute('aria-busy', 'true');
		try {
			const response = await fetch(url, { signal: request.signal, credentials: 'same-origin' });
			if (!response.ok) throw new Error('Unavailable');
			const page = new DOMParser().parseFromString(await response.text(), 'text/html');
			const grid = page.querySelector('#portfolio .fl-case-grid');
			const selected = page.querySelector('#portfolio .fl-filters [aria-current]');
			if (!grid || !selected) throw new Error('Missing project catalogue');
			if (request.signal.aborted) return;

			portfolio.querySelector('.fl-case-grid').replaceWith(grid);
			const sector = new URL(selected.getAttribute('href'), url).searchParams.get('sector');
			for (const link of portfolio.querySelectorAll('.fl-filters a')) {
				if (new URL(link.href).searchParams.get('sector') === sector) link.setAttribute('aria-current', 'true');
				else link.removeAttribute('aria-current');
			}
			if (push && url.href !== location.href) history.pushState(null, '', url);
			displayed = url.pathname + url.search;
			const count = grid.querySelectorAll('article').length;
			status.textContent = [selected.textContent, englishMenu ? 'Projects' : 'Realizacje', count].join(' · ');
		} catch (error) {
			if (!request.signal.aborted) location.assign(url);
		} finally {
			if (activeRequest === request) portfolio.removeAttribute('aria-busy');
		}
	}

	portfolio.addEventListener('click', event => {
		const link = event.target.closest('.fl-filters a');
		if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
		const url = new URL(link.href);
		if (url.origin !== location.origin || url.pathname !== location.pathname) return;
		event.preventDefault();
		filterProjects(url, true);
	});
	window.addEventListener('popstate', () => {
		activeRequest?.abort();
		if (displayed !== location.pathname + location.search) filterProjects(new URL(location.href), false);
	});
})();
