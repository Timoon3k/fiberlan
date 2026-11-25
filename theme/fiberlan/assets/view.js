(() => {
	const root = document.documentElement;
	const motion = matchMedia('(prefers-reduced-motion: no-preference)');
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
			// Keep WordPress in charge of the focus trap, scroll lock and returning focus.
			let leaving;
			let nativeClose = false;
			function dismiss(event) {
				if (nativeClose || !overlay.classList.contains('is-menu-open')) return;
				if (!motion.matches || !dialog.animate) return;
				event.preventDefault();
				event.stopImmediatePropagation();
				if (leaving) return;
				const slide = dialog.animate(
					[{ transform: getComputedStyle(dialog).transform }, { transform: 'translateX(100%)' }],
					{ duration: 260, easing: 'cubic-bezier(.4,0,.7,.2)', fill: 'forwards' }
				);
				const shade = overlay.animate(
					[{ backgroundColor: getComputedStyle(overlay).backgroundColor }, { backgroundColor: 'transparent' }],
					{ duration: 260, fill: 'forwards' }
				);
				leaving = slide;
				slide.finished.then(() => {
					nativeClose = true;
					if (overlay.classList.contains('is-menu-open')) close.click();
					nativeClose = false;
					slide.cancel();
					shade.cancel();
					leaving = null;
				});
			}
			close.addEventListener('click', dismiss, true);
			overlay.addEventListener('keydown', event => {
				if (event.key === 'Escape' && !event.target.closest('[aria-expanded="true"], .wp-block-navigation-submenu:focus-within')) dismiss(event);
			}, true);
			motion.addEventListener('change', () => { if (!motion.matches) leaving?.finish(); });
			// Some touch browsers retarget a tap at the panel edge to a nearby link.
			let pressedBackdrop = false;
			overlay.addEventListener('pointerdown', event => {
				pressedBackdrop = !dialog.contains(document.elementFromPoint(event.clientX, event.clientY));
			});
			overlay.addEventListener('click', event => {
				const outside = !dialog.contains(event.target) || (event.detail > 0 && pressedBackdrop);
				pressedBackdrop = false;
				if (!outside || !overlay.classList.contains('is-menu-open')) return;
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

	const link = document.querySelector('.fl-floating-top');
	const top = document.getElementById('fl-page-top');
	if (header || link) {
		let pending = false;
		function update() {
			pending = false;
			link?.classList.toggle('is-visible', scrollY >= 600);
			header?.classList.toggle('fl-scrolled', scrollY > 8);
		}
		window.addEventListener('scroll', () => {
			if (pending) return;
			pending = true;
			requestAnimationFrame(update);
		}, { passive: true });
		window.addEventListener('pageshow', update);
		update();
	}
	if (link && top) {
		link.addEventListener('click', event => {
			if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
			event.preventDefault();
			top.focus({ preventScroll: true });
			window.scrollTo({ top: 0, behavior: motion.matches ? 'smooth' : 'instant' });
		});
	}

	const main = document.querySelector('main');
	if (!main) return;
	// Register individual columns and cards so that neighbouring items can enter in sequence.
	const targets = [
		'.fl-section-head', '.fl-fact', '.fl-service-card', '.fl-project-intro', '.fl-project-card',
		'.fl-split-copy', '.fl-section-image', '.fl-equipment-copy', '.fl-team-photo', '.fl-about-story-copy',
		'.fl-milestone', '.fl-ribbon-copy', '.fl-reviews-intro', '.fl-reviews-widget',
		'.fl-contact-sidebar', '.fl-form-panel', '.fl-case-grid > article', '.fl-project-feature',
		'.fl-project-gallery > figure', '.fl-operator-badges > p', '.fl-case-illustration figure',
		'.fl-section :is(.fl-reading,.fl-wide,.fl-shell) > h2', '.fl-equipment-intro',
		'.wp-block-columns > .wp-block-column', '.wp-block-post-template > .wp-block-post'
	].join(',');
	const observer = 'IntersectionObserver' in window ? new IntersectionObserver(entries => {
		for (const { target, isIntersecting } of entries) {
			if (!isIntersecting) continue;
			target.classList.add('is-revealed');
			observer.unobserve(target);
		}
	}, { rootMargin: '0px 0px -8% 0px' }) : null;
	const seen = new WeakSet();
	function observeContent(scope, replaced = false) {
		if (!motion.matches || !observer) return;
		const items = [...scope.querySelectorAll(targets)];
		const containers = new Set(items.map(el => el.parentElement.closest(targets)));
		// Measure everything before adding styles. Nested columns reveal their individual cards.
		const incoming = items.filter(el => {
			if (seen.has(el) || containers.has(el)) return false;
			seen.add(el);
			return !el.matches(':focus-within,:target') && !el.closest(':target') &&
				(replaced || el.getBoundingClientRect().top > innerHeight * .92);
		});
		const siblings = new Map();
		for (const el of incoming) {
			const index = siblings.get(el.parentElement) || 0;
			siblings.set(el.parentElement, index + 1);
			el.style.setProperty('--fl-stagger', Math.min(index, 5) * 110 + 'ms');
			el.classList.add('fl-reveal');
			observer.observe(el);
		}
	}
	function finishReveal(el) {
		observer?.unobserve(el);
		el.classList.remove('fl-reveal', 'is-revealed');
		el.style.removeProperty('--fl-stagger');
	}
	main.addEventListener('animationend', event => {
		if (event.animationName === 'fl-section-lift') finishReveal(event.target);
	});
	main.addEventListener('focusin', event => {
		const el = event.target.closest('.fl-reveal');
		if (el) finishReveal(el);
	});
	function showAnchor() {
		const anchor = main.querySelector(':target');
		if (!anchor) return;
		const parent = anchor.closest('.fl-reveal');
		if (parent) finishReveal(parent);
		anchor.querySelectorAll('.fl-reveal').forEach(finishReveal);
	}
	window.addEventListener('hashchange', showAnchor);

	const number = main.querySelector('.fl-milestone-number');
	// Only animate a plain integer. Rich text and numbers with editorial formatting keep their original markup.
	const countText = number?.childNodes.length === 1 && number.firstChild.nodeType === Node.TEXT_NODE ? number.textContent : '';
	let counter, stopCount;
	let counted = false;
	function observeCounter() {
		if (!observer || !motion.matches || !/^\d+$/.test(countText) || !Number.isSafeInteger(Number(countText)) ||
			number.getBoundingClientRect().top <= innerHeight * .92 || counted || counter) return;
		counter = new IntersectionObserver((entries, active) => {
			if (active !== counter || !entries.some(entry => entry.isIntersecting)) return;
			active.disconnect();
			counted = true;
			const spoken = document.createElement('span');
			spoken.className = 'screen-reader-text';
			spoken.textContent = countText;
			const digits = document.createElement('span');
			digits.setAttribute('aria-hidden', 'true');
			number.replaceChildren(spoken, digits);
			const start = performance.now();
			let frame;
			stopCount = () => {
				cancelAnimationFrame(frame);
				number.textContent = countText;
				stopCount = null;
			};
			function tick(now) {
				const t = Math.min(1, (now - start) / 1600);
				digits.textContent = String(Math.round(Number(countText) * (1 - (1 - t) ** 3)));
				if (t < 1) frame = requestAnimationFrame(tick);
				else stopCount();
			}
			tick(start);
		}, { rootMargin: '0px 0px -10% 0px' });
		counter.observe(number);
	}
	function clearMotion() {
		for (const el of main.querySelectorAll('.fl-reveal')) {
			if (!el.classList.contains('is-revealed')) seen.delete(el);
			finishReveal(el);
		}
		counter?.disconnect();
		counter = null;
		stopCount?.();
	}
	function configureMotion() {
		if (motion.matches) {
			observeContent(main);
			observeCounter();
		} else clearMotion();
	}
	motion.addEventListener('change', configureMotion);
	window.addEventListener('beforeprint', clearMotion);
	window.addEventListener('afterprint', configureMotion);
	configureMotion();

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

			const previous = portfolio.querySelector('.fl-case-grid');
			previous.querySelectorAll('.fl-reveal').forEach(finishReveal);
			previous.replaceWith(grid);
			observeContent(grid, true);
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


/* FIBERLAN gallery: keyboard, touch and full-size media, no dependencies. */
(() => {
  const en = document.documentElement.lang.toLowerCase().startsWith('en');
  const t = (pl, english) => en ? english : pl;
  const groups = document.querySelectorAll('.fl-project-gallery');
  if (typeof HTMLDialogElement !== 'undefined' && groups.length) {
    const dialog = document.createElement('dialog');
    dialog.className = 'fl-gallery-viewer';
    dialog.setAttribute('aria-label',t('Powiększenie zdjęcia','Enlarged photograph'));
    const btn = (cl,label,txt) => {const b=document.createElement('button');b.type='button';b.className=cl;b.setAttribute('aria-label',label);b.textContent=txt;return b;};
    const close=btn('fl-gallery-close',t('Zamknij','Close'),'×');
    const prev=btn('fl-gallery-prev',t('Poprzednie zdjęcie','Previous photo'),'‹');
    const next=btn('fl-gallery-next',t('Następne zdjęcie','Next photo'),'›');
    const image=document.createElement('img');
    const status=document.createElement('p'); status.className='fl-gallery-status';status.setAttribute('aria-live','polite');
    dialog.append(close,prev,image,next,status);document.body.append(dialog);
    let photos=[],index=0,opener=null,scrollStyle='',touchX=null;
    const show = n => {
      index=(n+photos.length)%photos.length;
      const source=photos[index];
      image.src=source.dataset.flFull||source.getAttribute('src')||source.currentSrc;
      image.alt=source.alt||'';
      status.textContent=(index+1)+' / '+photos.length+(image.alt?' · '+image.alt:'');
    };
    close.addEventListener('click',()=>dialog.close());
    prev.addEventListener('click',()=>show(index-1));next.addEventListener('click',()=>show(index+1));
    dialog.addEventListener('keydown',e=>{if(e.key==='ArrowLeft'){e.preventDefault();show(index-1)}if(e.key==='ArrowRight'){e.preventDefault();show(index+1)}});
    dialog.addEventListener('click',e=>{if(e.target===dialog)dialog.close()});
    dialog.addEventListener('touchstart',e=>{touchX=e.changedTouches[0].clientX},{passive:true});
    dialog.addEventListener('touchend',e=>{if(touchX===null)return;const dx=e.changedTouches[0].clientX-touchX;touchX=null;if(Math.abs(dx)>50)show(index+(dx<0?1:-1))},{passive:true});
    dialog.addEventListener('close',()=>{document.body.style.overflow=scrollStyle;image.removeAttribute('src');opener?.focus()});
    groups.forEach(group=>{
      const entries=[...group.querySelectorAll('figure.wp-block-image img')].filter(img=>img.getAttribute('src')&&!img.src.includes('photo-placeholder.svg')&&!img.closest('a, .wp-lightbox-container'));
      entries.forEach((img,i)=>{
        const figure=img.closest('figure');
        if(!figure||figure.querySelector('.fl-gallery-open'))return;
        const open=btn('fl-gallery-open',t('Powiększ zdjęcie','Enlarge photograph')+(img.alt?' — '+img.alt:''),'');
        figure.append(open);
        open.addEventListener('click',()=>{photos=entries;opener=open;show(i);scrollStyle=document.body.style.overflow;document.body.style.overflow='hidden';dialog.showModal();close.focus()});
      });
    });
  }
  document.querySelectorAll('.fl-operator-marquee').forEach(marquee=>{
    const logos=[...marquee.querySelectorAll(':scope > .fl-operator-logo')];
    if(!logos.length)return;
    const set=document.createElement('div');set.className='fl-logo-set';
    logos.forEach(logo=>{
      const img=logo.querySelector('img');
      if(img){
        const fallback=()=>{if(logo.querySelector('.fl-logo-fallback'))return;const name=document.createElement('span');name.className='fl-logo-fallback';name.textContent=img.alt;img.hidden=true;logo.append(name)};
        img.addEventListener('error',fallback);if(img.complete&&!img.naturalWidth)fallback();
      }
      set.append(logo);
    });
    const track=document.createElement('div');track.className='fl-logo-track';const clone=set.cloneNode(true);clone.setAttribute('aria-hidden','true');clone.inert=true;
    clone.querySelectorAll('img').forEach(img=>{img.alt='';img.loading='eager'});
    track.append(set,clone);marquee.append(track);
    const measure=()=>marquee.style.setProperty('--fl-logo-cell',Math.max(160,Math.ceil(marquee.clientWidth/logos.length))+'px');
    measure();
    if(window.ResizeObserver)new ResizeObserver(measure).observe(marquee);
    else window.addEventListener('resize',measure);
    clone.querySelectorAll('img').forEach((img,i)=>{
      const fail=()=>{const fig=img.closest('figure');if(!fig||fig.querySelector('.fl-logo-fallback'))return;img.hidden=true;const name=document.createElement('span');name.className='fl-logo-fallback';name.textContent=logos[i]?.querySelector('img')?.alt||'';fig.append(name)};
      img.addEventListener('error',fail);
      if(img.complete&&!img.naturalWidth)fail();
    });
    const toggle=document.createElement('button');toggle.type='button';toggle.className='fl-logo-toggle';toggle.setAttribute('aria-pressed','false');toggle.textContent=t('Wstrzymaj animację','Pause animation');
    toggle.addEventListener('click',()=>{const pause=track.classList.toggle('is-paused');toggle.setAttribute('aria-pressed',String(pause));toggle.textContent=pause?t('Wznów animację','Resume animation'):t('Wstrzymaj animację','Pause animation')});
    marquee.after(toggle);
  });
})();
