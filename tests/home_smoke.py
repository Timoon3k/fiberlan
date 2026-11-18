"""Check the FIBERLAN homepage at seven viewport widths in a local WordPress.

Usage: python tests/home_smoke.py http://127.0.0.1:9400 --screens /tmp/fiberlan-screens
Requires Python Playwright and Chromium (or its Playwright-managed build).
"""
import argparse
import shutil
from urllib.parse import unquote, urlsplit
from pathlib import Path
from urllib.parse import urlparse

from playwright.sync_api import expect, sync_playwright

WIDTHS = (1920, 1440, 1280, 1024, 768, 390, 360)
NAV_LABELS = ["Strona główna", "O nas", "Usługi", "Sprzęt", "Realizacje", "Kontakt"]


def check_page(browser, base, width, screens):
    page = browser.new_page(viewport={"width": width, "height": 1000}, reduced_motion="reduce")
    errors = []
    page.on("pageerror", lambda error: errors.append(str(error)))
    try:
        response = page.goto(base, wait_until="domcontentloaded")
        assert response and response.ok, f"HTTP {response and response.status}"
        expect(page.locator("h1")).to_have_count(1)
        expect(page.locator("h1")).to_have_text("Spawanie światłowodów")
        page.evaluate("document.fonts.ready")
        # Walking the page triggers native lazy-loading as a reader scrolls.
        height = page.evaluate("document.documentElement.scrollHeight")
        for top in range(0, height + 800, 800):
            page.evaluate("y => window.scrollTo({ top: y, behavior: 'instant' })", top)
            page.wait_for_timeout(60)
        page.wait_for_function("Array.from(document.images).every(im => im.complete)")
        bad = page.locator("img").evaluate_all(
            "images => images.filter(i => !i.naturalWidth || !i.hasAttribute('alt')).map(i => i.src)"
        )
        assert not bad, f"Broken image or missing alt attribute: {bad}"
        page.evaluate("window.scrollTo({ top: 0, behavior: 'instant' })")
        overflow = page.evaluate("document.documentElement.scrollWidth - innerWidth")
        assert overflow <= 1, f"Horizontal overflow of {overflow}px"
        nav = page.locator("header nav.wp-block-navigation").first
        expect(nav).to_be_attached()
        toggle = nav.locator("button.wp-block-navigation__responsive-container-open")
        if screens:
            screens.mkdir(parents=True, exist_ok=True)
            page.screenshot(path=str(screens / f"home-{width}.png"), full_page=True)
        if toggle.is_visible():
            toggle.focus()
            page.keyboard.press("Enter")
            expect(nav.locator(".is-menu-open")).to_be_visible()
            assert [a.inner_text().strip() for a in nav.locator("a").all()] == NAV_LABELS
            page.keyboard.press("Escape")
            expect(nav.locator(".is-menu-open")).to_have_count(0)
        else:
            assert [a.inner_text().strip() for a in nav.locator("a").all()] == NAV_LABELS
            for link in nav.locator("a").all():
                target = urlsplit(link.get_attribute("href") or "").fragment
                if target:
                    assert page.evaluate("id => !!document.getElementById(id)", unquote(target)), target
        if width == 390 and toggle.is_visible():
            toggle.click()
            service = nav.get_by_role("link", name="Usługi", exact=True)
            url = service.get_attribute("href")
            service.click()
            expect(page).to_have_url(url)
            expect(nav.locator(".is-menu-open")).to_have_count(0)
        assert not errors, f"Browser error(s): {errors}"
        print(f"{width}px: OK, {page.locator('img').count()} images, overflow={overflow}px", flush=True)
    finally:
        page.close()


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("base_url")
    parser.add_argument("--screens", type=Path)
    args = parser.parse_args()
    if urlparse(args.base_url).hostname not in {"localhost", "127.0.0.1", "::1"}:
        parser.error("Provide the local WordPress URL.")
    with sync_playwright() as playwright:
        browser = playwright.chromium.launch(executable_path=shutil.which("chromium"))
        for width in WIDTHS:
            check_page(browser, args.base_url, width, args.screens)
        browser.close()


if __name__ == "__main__":
    main()
