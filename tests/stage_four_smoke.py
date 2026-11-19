"""Verify the stage 4 layout, keyboard navigation and motion in local WordPress."""
import argparse
import re
import shutil
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
    back = page.locator("footer .fl-back-top a")
    back.press("Enter")
    expect(page.locator("#fl-page-top")).to_be_focused()
    assert page.evaluate("scrollY") == 0, path
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
    page = browser.new_page(viewport={"width": 390, "height": 840}, reduced_motion=mode)
    page.goto(base + "/uslugi/")
    section = page.locator("#pomiary .fl-shell").first
    section.scroll_into_view_if_needed()
    if mode == "reduce":
        assert section.evaluate("n => getComputedStyle(n).animationName") == "none"
        expect(page.locator(".fl-in-view")).to_have_count(0)
    else:
        expect(section).to_have_class(re.compile(r"\bfl-in-view\b"))
    page.goto(base + "/uslugi/#pomiary")
    heading = page.locator("#pomiary h2").bounding_box()
    nav = page.locator(".fl-nav-row").bounding_box()
    assert heading["y"] >= nav["y"] + nav["height"] - 1
    page.close()


if __name__ == "__main__":
    args = argparse.ArgumentParser(description=__doc__)
    args.add_argument("--url", default="http://127.0.0.1:8080")
    base = args.parse_args().url.rstrip("/")
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
        page = browser.new_page(java_script_enabled=False)
        page.goto(base + "/en/services/")
        expect(page.locator("#pomiary h2")).to_be_visible()
        page.locator(".fl-back-top a").click()
        assert page.url.endswith("#fl-page-top")
        print("Reduced motion, scroll animation, anchor clearance and no-JS return OK")
        browser.close()
