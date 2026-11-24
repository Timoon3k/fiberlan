"""Verify the stage 4 layout, keyboard navigation and motion in local WordPress."""
import argparse
import re
import shutil
from pathlib import Path
from urllib.parse import urlsplit

from playwright.sync_api import expect, sync_playwright

from stage_two_smoke import PAIRS


def check_layout(page, base, path):
    page.goto(base + path)
    page.evaluate("document.fonts.ready")
    nav = page.locator("header .fl-nav-row")
    page.evaluate("window.scrollTo(0, document.documentElement.scrollHeight)")
    top = nav.bounding_box()
    assert top and -2 <= top["y"] <= 2, (path, top)
    back = page.locator(".fl-floating-top")
    if page.evaluate("scrollY >= 600"):
        expect(back).to_be_visible()
        back.press("Enter")
        expect(page.locator("#fl-page-top")).to_be_focused()
        assert page.evaluate("scrollY") == 0, path
    else:
        expect(back).to_be_hidden()
    assert page.evaluate("""['Manrope', 'FIBERLAN Britannic'].every(name =>
        [...document.fonts].some(font => font.family === name && font.status === 'loaded'))"""), path
    # Exercise unsupplied final copy without changing any WordPress record.
    page.locator("main h1").evaluate("n => n.textContent = 'Światłowody international infrastructure '.repeat(4)")
    lead = page.locator("main .fl-lead").first
    lead.evaluate("n => n.textContent += (' Wielowłóknowa infrastructure acceptance.').repeat(15)")
    assert page.evaluate("document.documentElement.scrollWidth <= innerWidth"), path
    boxes = [item.bounding_box() for item in (page.locator("main h1"), lead)]
    a, b = boxes
    assert a["y"] + a["height"] <= b["y"] + 1 or a["x"] + a["width"] <= b["x"] + 1, (path, boxes)


def check_motion(browser, base, mode):
    for width in (390, 1440):
        page = browser.new_page(viewport={"width": width, "height": 700}, reduced_motion=mode)
        for pair in PAIRS[:6]:
            for path in pair:
                page.goto(base + path)
                expect(page.locator("main h1")).to_have_css("opacity", "1")
                assert page.evaluate("scrollY") == 0
                pending = page.locator("main .fl-reveal:not(.is-revealed)")
                if mode == "reduce":
                    expect(pending).to_have_count(0)
                elif path in ("/", "/en/"):
                    assert pending.count() > 0
                    expect(pending.last).to_have_css("opacity", "0")
        page.goto(base + "/uslugi/#pomiary")
        heading = page.locator("#pomiary h2").bounding_box()
        nav = page.locator(".fl-nav-row").bounding_box()
        assert heading["y"] >= nav["y"] + nav["height"] - 1
        page.close()
        print(f"{width}px: motion preference {mode} verified on 12 pages", flush=True)


def check_home_photograph(page, base):
    for path in ("/", "/en/"):
        page.goto(base + path)
        figure = page.locator(".fl-hero-figure")
        photo = figure.locator("picture.fl-splicer-scene img")
        photo.evaluate("img => img.decode()")
        lighting = photo.evaluate("""img => {
          const layer = getComputedStyle(img.parentElement, '::before');
          return [layer.animationName, layer.backgroundImage.includes(img.currentSrc),
                  getComputedStyle(img).filter, getComputedStyle(img).animationName];
        }""")
        assert lighting == ["none", True, "none", "none"], lighting
        mask = photo.evaluate("img => getComputedStyle(img).maskImage")
        url = re.fullmatch(r'url\("(.+)"\)', mask).group(1)
        response = page.request.get(url)
        assert response.ok and "image/svg+xml" in response.headers["content-type"], url
        assert "1600 1200" in response.text()
        assert photo.evaluate("img => img.naturalWidth > 0 && img.getBoundingClientRect().height > 100")
        # The splicer and the cleaver span x=156..1487 and y=262..1111 on the original.
        rect = photo.bounding_box()
        frame = figure.locator("picture").bounding_box()
        for axis, size, fraction in (("x", "width", 821.5 / 1600), ("y", "height", 686.5 / 1200)):
            object_centre = rect[axis] + rect[size] * fraction
            frame_centre = frame[axis] + frame[size] / 2
            assert abs(object_centre - frame_centre) < 2, (path, axis)
        assert 'filter="url(#defocused-edge)"' in response.text()
        assert photo.evaluate("img => img.currentSrc === img.src")
        assert abs(rect["width"] / rect["height"] - 4 / 3) < .005
        for x, y in ((156, 262), (1487, 1111)):
            assert frame["x"] <= rect["x"] + rect["width"] * x / 1600 <= frame["x"] + frame["width"]
            assert frame["y"] <= rect["y"] + rect["height"] * y / 1200 <= frame["y"] + frame["height"]
        for selector in (".fl-hero .fl-eyebrow", ".fl-services .fl-section-head h2", ".fl-projects .fl-eyebrow"):
            marker = page.locator(selector).evaluate("n => getComputedStyle(n, '::before').content")
            assert marker in ("none", "normal"), (selector, marker)
        expect(figure.locator("figcaption")).to_have_count(0)
        expect(photo).to_have_attribute("alt", re.compile("open and display lit" if path == "/en/" else "włączonym ekranem"))
        figure.evaluate("node => node.classList.add('is-style-fiberlan-full')")
        assert photo.evaluate("img => getComputedStyle(img).maskImage") == "none"
        assert photo.evaluate("img => getComputedStyle(img.parentElement, '::before').content") in ("none", "normal")
        reviews = page.locator(".fl-reviews")
        expect(reviews.get_by_role("heading")).to_have_text("Opinie z Google" if path == "/" else "Google reviews")
        expect(reviews.locator(".fl-reviews-widget")).to_have_count(1)
        expect(reviews.get_by_role("link").first).to_have_attribute("href", re.compile(r"^https://www\.google\.com/maps/search/"))
        expect(page.locator(".fl-contact-row")).to_have_count(0)
        expect(page.locator(".fl-languages [aria-current]")).to_have_text("PL" if path == "/" else "EN")
        service = page.locator(".fl-service-card h3 a").first
        service.focus()
        assert service.evaluate("link => getComputedStyle(link).textDecorationLine") == "none"
        assert service.evaluate("link => getComputedStyle(link.closest('.fl-service-card')).outlineStyle") == "solid"
        projects = page.locator(".fl-project-card h3 a")
        expect(projects).to_have_count(3)
        for index, pair in enumerate(PAIRS[-3:]):
            expect(projects.nth(index)).to_have_attribute("href", base + pair[int(path == "/en/")])


def check_device_details(page, base):
    for lang, path in enumerate(("/", "/en/")):
        page.goto(base + path)
        trigger = page.get_by_role("button", name=re.compile("Fujikura FSM-62S"))
        tooltip = trigger.get_by_role("tooltip")
        trigger.press("Enter")
        expect(trigger).to_have_attribute("aria-expanded", "true")
        expect(tooltip).to_be_visible()
        expect(tooltip).to_contain_text("Fujikura FSM-62S")
        page.keyboard.press("Escape")
        expect(trigger).to_have_attribute("aria-expanded", "false")
        expect(tooltip).to_be_hidden()
        expect(page.locator('.fl-contactbar a[href="tel:+48662211200"]')).to_have_text("+48 66 22 11 200")
        expect(page.locator('main img[alt*="VIAVI SmartClass Fiber MPOLx"]')).to_have_count(1)
        page.goto(base + PAIRS[3][lang])
        expect(page.get_by_role("heading", name="Fluke Networks DSX-5000")).to_be_visible()
        mask = page.locator(".fl-equipment-item .fl-fluke-cutout img").evaluate("img => getComputedStyle(img).maskImage")
        assert "fluke-versiv-mask.svg" in mask


def check_reveal_timing(page, base):
    page.goto(base)
    cards = page.locator(".fl-service-grid > .fl-service-card")
    for card, delay in zip(cards.all(), ("0ms", "110ms", "220ms", "330ms")):
        expect(card).to_have_css("--fl-stagger", delay)
        expect(card).to_have_css("translate", "0px 28px")
        expect(card).to_have_css("animation-name", "none")
    cards.last.scroll_into_view_if_needed()
    for card in cards.all():
        expect(card).to_have_css("opacity", "1")
        expect(card).to_have_css("animation-name", "none")
    page.evaluate("scrollTo(0,0)")
    cards.last.scroll_into_view_if_needed()
    expect(cards.last).to_have_css("animation-name", "none")
    number = page.locator(".fl-milestone-number")
    final_number = number.inner_text()
    number.scroll_into_view_if_needed()
    expect(number.locator("span.screen-reader-text")).to_have_text(final_number)
    expect(number.locator("span")).to_have_count(0)
    expect(number).to_have_text(final_number)
    page.emulate_media(reduced_motion="reduce")
    expect(page.locator(".fl-reveal")).to_have_count(0)
    page.emulate_media(reduced_motion="no-preference")

    # The AJAX catalogue has its own lifecycle. Capture the inserted card's animation
    # at its start, even when the test runner takes longer than the transition to resume.
    page.goto(base + "/en/projects/")
    page.evaluate("""document.addEventListener('animationstart', e => {
        if (e.animationName === 'fl-section-lift' && e.target.matches('.fl-case-grid article'))
            window.revealedProject = e.target;
    })""")
    page.locator('.fl-filters a[href*="-industry"]').click()
    project = page.locator(".fl-case-grid article")
    expect(project).to_have_count(1)
    project.scroll_into_view_if_needed()
    page.wait_for_function("window.revealedProject === document.querySelector('.fl-case-grid article')")
    expect(project).to_have_css("opacity", "1")


def check_footer(page, base):
    for idx, lang in enumerate(("pl", "en")):
        page.goto(base + PAIRS[0][idx])
        footer = page.locator("footer")
        assert footer.locator(".custom-logo").is_visible()
        expect(footer.locator('a[href="#fl-page-top"]')).to_have_count(0)
        expect(page.locator(".fl-floating-top")).to_have_count(1)
        expect(footer.locator(".fl-footer-links").first.locator("a")).to_have_count(6)
        for anchor, pair in zip(footer.locator(".fl-footer-links").first.locator("a").all(), PAIRS[:6]):
            expect(anchor).to_have_attribute("href", base + pair[idx])
        expect(footer.locator('.fl-footer-links a[href$="#spawanie"]')).to_have_attribute("href", base + PAIRS[2][idx] + "#spawanie")
        expect(footer.locator('a[href="tel:+48662211200"]')).to_have_count(1)
        expect(footer.locator('a[href="tel:+48696610440"]')).to_have_count(1)
        expect(footer.locator('a[href="mailto:info@fiberlan.pl"]')).to_have_count(1)
        legal = footer.locator(".fl-footer-main .fl-footer-legal")
        expect(legal.get_by_role("heading")).to_have_text("Legal" if lang == "en" else "Regulacje prawne")
        expect(legal.locator("a")).to_have_count(2)
        expect(legal.locator("a").first).to_have_attribute("href", base + PAIRS[6][idx])
        expect(legal.locator("a").last).to_have_attribute("href", base + PAIRS[7][idx])
        expect(legal.locator(".fl-consent-settings")).to_have_text("Cookie settings" if lang == "en" else "Ustawienia cookies")
        expect(footer.locator(".fl-footer-base")).to_have_count(0)
        rights = "Wszelkie prawa zastrzeżone." if lang == "pl" else "All rights reserved."
        expect(footer.locator(".fl-copyright")).to_have_text(re.compile(r"© 2026(–[0-9]{4})? FIBERLAN SC\. " + re.escape(rights)))
        expect(footer.locator('.fl-footer-credit a[href="https://majewskitomasz.pl"]')).to_have_text("majewskitomasz.pl")


def check_sticky_controls(page, base, mobile):
    page.goto(base + "/en/services/")
    nav = page.locator(".fl-nav")
    expect(nav.locator('a[aria-current="page"]')).to_have_attribute("href", base + "/en/services/")
    button = page.locator(".fl-floating-top")
    expect(button).to_be_hidden()
    page.evaluate("window.scrollTo(0, 500)")
    expect(button).to_be_hidden()
    page.evaluate("window.scrollTo(0, 670)")
    expect(button).to_be_visible()
    expect(page.locator(".wp-site-blocks > header")).to_have_class(re.compile(r"fl-scrolled"))
    expect(button).to_have_accessible_name("Back to top")
    page.locator("footer .fl-footer-main").evaluate("""footer => {
        footer.insertAdjacentHTML('beforeend',
            '<p class="fl-back-top"><a href="#fl-page-top">Legacy return link</a></p>');
    }""")
    expect(page.locator("footer .fl-back-top a")).to_be_hidden()
    expect(button).to_be_visible()
    switch = page.locator('.fl-languages a[data-language="pl"]').first
    assert switch.bounding_box()["y"] >= 0
    if mobile:
        before = page.evaluate("scrollY")
        menu = nav.get_by_role("button", name="Open menu")
        menu.click()
        panel = nav.locator(".is-menu-open")
        expect(panel).to_be_visible()
        items = panel.locator("li a")
        expect(items).to_have_count(6)
        for item in items.all():
            bounds = item.bounding_box()
            assert bounds["width"] >= 240 and bounds["height"] >= 44
            expect(item).to_have_css("text-decoration-line", "none")
        expect(button).to_be_hidden()
        page.keyboard.press("Escape")
        expect(menu).to_be_focused()
        assert page.evaluate("scrollY") == before
        expect(button).to_be_visible()
    page.evaluate("""() => {
        const banner = document.createElement('div');
        banner.className = 'cky-consent-container';
        document.body.append(banner);
    }""")
    expect(button).to_be_hidden()
    page.evaluate("document.querySelector('.cky-consent-container').classList.add('cky-hide')")
    expect(button).to_be_visible()
    bottom = button.bounding_box()["y"]
    page.evaluate("""() => {
        const tab = document.createElement('div');
        tab.className = 'cky-revisit-bottom-right';
        document.body.append(tab);
    }""")
    assert button.bounding_box()["y"] <= bottom - 44
    page.evaluate("""() => {
        window.revisitCkyConsent = () => { window.consentOpened = true; };
        document.dispatchEvent(new Event('cookieyes_banner_load'));
    }""")
    control = page.locator("footer .fl-footer-legal .fl-consent-settings")
    expect(control).to_be_visible()
    control.click()
    assert page.evaluate("window.consentOpened")
    page.evaluate("window.scrollTo(0, 670)")
    expect(button).to_be_visible()
    button.press("Enter")
    expect(page.locator("#fl-page-top")).to_be_focused()
    page.wait_for_function("scrollY === 0")
    page.goto(base + "/en/projects/frankfurt/")
    expect(nav.locator('a[aria-current="location"]')).to_have_attribute("href", base + "/en/projects/")
    page.goto(base + "/en/missing-page-for-qa/")
    expect(page.locator('.fl-languages a[data-language="pl"]').first).to_have_attribute("href", base + "/")
    expect(page.locator('head link[rel="alternate"][hreflang]')).to_have_count(0)


def check_drawer(browser, base):
    for width, height in ((320, 568), (390, 844), (740, 390), (1180, 820)):
        page = browser.new_page(viewport={"width": width, "height": height}, has_touch=True, is_mobile=True)
        page.goto(base + "/uslugi/")
        menu = page.locator("header .wp-block-navigation__responsive-container-open")
        menu.tap()
        dialog = page.get_by_role("dialog")
        expect(dialog.locator(".fl-menu-brand img")).to_be_visible()
        # The drawer travels horizontally. Its final right edge meets the viewport.
        expect(dialog).to_have_css("animation-name", "fl-drawer-enter")
        page.evaluate("""() => Promise.all(document.querySelector('[role="dialog"]').getAnimations()
            .map(animation => animation.finished))""")
        panel = dialog.bounding_box()
        assert panel["x"] > 18 and abs(panel["x"] + panel["width"] - width) < 1, (width, panel)
        assert panel["width"] <= 416.1 and abs(panel["height"] - height) < 2, (width, height, panel)
        current = dialog.locator("li a[aria-current]")
        expect(current).to_have_text("Usługi")
        expect(current).to_have_css("text-decoration-line", "none")
        expect(current).to_have_css("text-align", "center")
        expect(dialog.locator(".fl-mobile-locales [aria-current]")).to_have_css("text-decoration-line", "none")
        phone = page.locator('.fl-contactbar a[href^="tel:"]').first
        contact = dialog.locator(".fl-menu-phone")
        expect(contact).to_have_attribute("href", phone.get_attribute("href"))
        expect(contact).to_contain_text(phone.text_content().strip())
        contact.scroll_into_view_if_needed()
        assert contact.bounding_box()["y"] + contact.bounding_box()["height"] <= height + 1
        contact.focus()
        page.keyboard.press("Tab")
        expect(dialog.get_by_role("button")).to_be_focused()
        page.keyboard.press("Shift+Tab")
        expect(contact).to_be_focused()
        page.keyboard.press("Escape")
        expect(dialog).to_be_hidden()
        expect(menu).to_be_focused()
        menu.tap()
        expect(dialog).to_be_visible()
        page.touchscreen.tap(6, 120)
        expect(dialog).to_be_hidden()
        expect(menu).to_be_focused()
        menu.tap()
        dialog.get_by_role("button").tap()
        expect(dialog).to_be_hidden()
        menu.tap()
        dialog.locator('.fl-mobile-locales a[data-language="en"]').tap()
        expect(page).to_have_url(base + "/en/services/")
        page.close()


def check_admin_bar(browser, base, password):
    page = browser.new_page(viewport={"width": 1440, "height": 850})
    page.goto(base + "/wp-login.php")
    page.locator("#user_login").fill("fiberlan-editor")
    page.locator("#user_pass").fill(password.read_text().strip())
    page.locator("#wp-submit").click()
    page.wait_for_url("**/wp-admin/**")
    for width, height in ((1440, 850), (768, 840), (390, 840), (740, 390)):
        page.set_viewport_size({"width": width, "height": height})
        page.goto(base + "/en/equipment/")
        if width < 960:
            page.get_by_role("button", name="Open menu").click()
            page.get_by_role("button", name="Close menu").click()
            expect(page.get_by_role("dialog")).to_be_hidden()
        admin = page.locator("#wpadminbar")
        expect(admin).to_be_visible()
        h1 = page.locator("main h1")
        anchor = h1.bounding_box()
        nav = page.locator(".fl-nav-row")
        initial_height = nav.bounding_box()["height"]
        page.evaluate("window.scrollTo(0, 800)")
        page.wait_for_function("scrollY === 800")
        expected_y = admin.bounding_box()["height"] if width > 600 else 0
        assert abs(nav.bounding_box()["y"] - expected_y) <= 1, width
        assert nav.bounding_box()["height"] == initial_height, width
        assert abs(h1.bounding_box()["y"] + 800 - anchor["y"]) <= 1, width
    page.close()


if __name__ == "__main__":
    args = argparse.ArgumentParser(description=__doc__)
    args.add_argument("--url", default="http://127.0.0.1:8080")
    args.add_argument("--password-file", type=Path)
    options = args.parse_args()
    base = options.url.rstrip("/")
    if urlsplit(base).hostname not in {"localhost", "127.0.0.1", "::1"}:
        args.error("Use a local WordPress URL.")
    with sync_playwright() as runner:
        browser = runner.chromium.launch(executable_path=shutil.which("chromium"))
        for width in (320, 390, 768, 1024, 1440):
            page = browser.new_page(viewport={"width": width, "height": 840}, reduced_motion="reduce")
            for pair in PAIRS[:6]:
                for path in pair:
                    check_layout(page, base, path)
            page.close()
            print(f"{width}px: 12 pages, long copy, local fonts, sticky navigation and keyboard return OK", flush=True)
        for mode in ("reduce", "no-preference"):
            check_motion(browser, base, mode)
        check_drawer(browser, base)
        for width in (390, 1440):
            page = browser.new_page(viewport={"width": width, "height": 840}, reduced_motion="reduce" if width < 600 else "no-preference")
            if width == 1440:
                check_reveal_timing(page, base)
            check_home_photograph(page, base)
            check_device_details(page, base)
            check_footer(page, base)
            check_sticky_controls(page, base, width < 600)
            page.close()
        if options.password_file:
            check_admin_bar(browser, base, options.password_file)
            print("Sticky header with WordPress admin bar verified", flush=True)
        page = browser.new_page(java_script_enabled=False, viewport={"width": 390, "height": 840})
        page.goto(base + "/en/services/")
        expect(page.locator("#pomiary h2")).to_be_visible()
        expect(page.locator('header .fl-nav a[href$="/en/services/"]')).to_be_visible()
        page.locator(".fl-floating-top").click()
        assert page.url.endswith("#fl-page-top")
        print("Reduced motion, viewport reveals, anchor clearance and no-JS return OK")
        browser.close()
