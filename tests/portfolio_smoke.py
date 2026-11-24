"""Check the installed bilingual project pages with the child theme active."""
import argparse
import shutil

from playwright.sync_api import expect, sync_playwright

from urllib.parse import urlparse

from stage_two_smoke import BASE, PAIRS


def check_pages(page, lang, width, base=BASE):
    home, about, services, equipment, projects = [pair[lang] for pair in PAIRS[:5]]
    placeholder = 'img[src$="/photo-placeholder.svg"]'
    page.goto(base + home)
    expect(page.locator(".fl-service-card")).to_have_count(4)
    for index, card in enumerate(page.locator(".fl-service-card").all()):
        expect(card.locator(placeholder)).to_have_count(int(index in (0, 3)))
    expect(page.locator(".fl-project-grid " + placeholder)).to_have_count(3)
    page.goto(base + about)
    story = page.locator(".fl-about-story")
    photo, copy = [story.locator(selector).bounding_box() for selector in (".fl-team-photo", ".fl-about-story-copy")]
    if width >= 1024:
        assert photo["x"] + photo["width"] < copy["x"]
        assert abs(photo["y"] - copy["y"]) < max(64, photo["height"] / 2)
        assert story.bounding_box()["height"] <= max(photo["height"], copy["height"]) + 4
    else:
        assert copy["y"] >= photo["y"] + photo["height"]
    for path, selector in ((services, ".fl-fluke-service"), (equipment, ".fl-equipment-item:has(.fl-fluke-cutout)")):
        page.goto(base + path)
        if path == services:
            expect(page.locator("#lan " + placeholder)).to_have_count(1)
        frame = page.locator(selector + " .fl-fluke-cutout")
        img = frame.locator("img")
        frame.scroll_into_view_if_needed()
        img.evaluate("img => img.decode()")
        image, clip = img.bounding_box(), frame.bounding_box()
        assert abs(image["width"] / image["height"] - .75) < .01
        # Coordinates of the two testers inside the supplied 1200 x 1600 camera file.
        for y in (455, 1310):
            assert clip["y"] <= image["y"] + y / 1600 * image["height"] <= clip["y"] + clip["height"]
    page.goto(base + projects + "?utm_source=portfolio-test")
    expect(page.locator(".fl-operator-badges > p")).to_have_text(["T-Mobile", "Orange", "Plus"])
    expect(page.locator(".fl-case-grid " + placeholder)).to_have_count(3)
    expect(page.locator(".fl-project-gallery " + placeholder)).to_have_count(6)
    started = page.evaluate("performance.timeOrigin")
    current = page.locator(".fl-filters a").first
    industry = page.locator('.fl-filters a[href*="-industry"]')
    with page.expect_response(lambda response: response.request.resource_type == "fetch" and "-industry" in response.url):
        if width == 390:
            industry.tap()
        else:
            industry.click()
    expect(industry).to_have_attribute("aria-current", "true")
    expect(page.locator(".fl-case-grid article")).to_have_count(1)
    assert page.evaluate("performance.timeOrigin") == started
    assert "-industry" in page.url and "utm_source=portfolio-test" in page.url
    page.go_back()
    expect(current).to_have_attribute("aria-current", "true")
    expect(page.locator(".fl-case-grid article")).to_have_count(3)
    badges = page.locator(".fl-operator-badges")
    badges.scroll_into_view_if_needed()
    expect(badges.locator("p").last).to_have_css("opacity", "1")
    assert badges.locator("p").first.evaluate("n => getComputedStyle(n).animationName") == "none"
    reports = page.locator(".fl-case-grid article a").evaluate_all("links => links.map(a => a.href)")
    for report in reports:
        page.goto(report)
        expect(page.locator(".fl-report-gallery " + placeholder)).to_have_count(3)


def check_export_copy(page, base):
    for lang in (0, 1):
        services = base + PAIRS[2][lang]
        page.goto(services)
        for anchor, keyword in (("instalacje", "rack"), ("pomiary", "otdr"), ("data-center", "mpo"), ("serwis", "24/7"), ("lan", "copper" if lang else "miedziane")):
            assert keyword in page.locator(f"#{anchor} h2").inner_text().lower()
        page.goto(base + PAIRS[3][lang])
        items = page.locator(".fl-equipment-item")
        expect(items).to_have_count(4)
        for item, anchor in zip(items.all(), ("spawanie", "lan", "data-center", "pomiary")):
            expect(item.locator(".fl-equipment-copy a")).to_have_attribute("href", services + "#" + anchor)
        page.goto(base + PAIRS[1][lang])
        for text in (("Direct contact with the experts", "24/7 support across Poland", "Quality verified by testing") if lang else
                     ("Bezpośredni kontakt z ekspertami", "Gotowość 24/7 w Polsce", "Certyfikowana jakość")):
            expect(page.get_by_role("heading", name=text, exact=True)).to_have_count(1)
        page.goto(base + PAIRS[0][lang])
        for card, pair in zip(page.locator(".fl-project-card").all(), PAIRS[-3:]):
            expect(card.locator("h3 a")).to_have_attribute("href", base + pair[lang])


if __name__ == "__main__":
    parser = argparse.ArgumentParser()
    parser.add_argument("--url", default=BASE)
    parser.add_argument("--export-copy", action="store_true", help="Check the translated sections from the supplied database.")
    options = parser.parse_args()
    base = options.url.rstrip("/")
    if urlparse(base).hostname not in {"localhost", "127.0.0.1", "::1"}:
        parser.error("Use a local WordPress URL.")
    with sync_playwright() as runner:
        browser = runner.chromium.launch(executable_path=shutil.which("chromium"))
        for width in (390, 1440):
            page = browser.new_page(viewport={"width": width, "height": 820}, has_touch=width == 390, is_mobile=width == 390, reduced_motion="reduce")
            for lang in (0, 1):
                check_pages(page, lang, width, base)
            if options.export_copy:
                check_export_copy(page, base)
            page.close()
            print(f"{width}px: service and portfolio media, team layout, Fluke framing and report galleries PL/EN OK", flush=True)
        page = browser.new_page(java_script_enabled=False)
        page.goto(base + PAIRS[4][1])
        page.locator('.fl-filters a[href*="-industry"]').click()
        expect(page.locator(".fl-case-grid article")).to_have_count(1)
        page.close()
        browser.close()
