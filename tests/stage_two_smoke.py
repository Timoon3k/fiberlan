"""Test the local bilingual WordPress and its loopback Mailpit transport.

Run from the repository after ./dev/start.sh.
"""
from pathlib import Path
from uuid import uuid4
import argparse
import shutil

from playwright.sync_api import expect, sync_playwright

BASE = "http://127.0.0.1:8080"
INBOX = "http://127.0.0.1:8025"
PAIRS = [
    ("/", "/en/"), ("/o-nas/", "/en/about/"), ("/uslugi/", "/en/services/"),
    ("/sprzet/", "/en/equipment/"), ("/realizacje/", "/en/projects/"),
    ("/kontakt/", "/en/contact/"), ("/polityka-prywatnosci/", "/en/privacy-policy/"),
    ("/polityka-cookies/", "/en/cookie-policy/"),
    *((f"/realizacje/{key}/", f"/en/projects/{key}/") for key in ("frankfurt", "bordeaux", "marine"))
]


def check_pages(browser, screenshots, axe_path):
    for width in (390, 1440):
        for paths in PAIRS:
            for index, path in enumerate(paths):
                lang = "en" if index else "pl"
                page = browser.new_page(viewport={"width": width, "height": 1000})
                errors = []
                page.on("pageerror", lambda err: errors.append(str(err)))
                response = page.goto(BASE + path)
                assert response.ok and page.url == BASE + path, path
                expect(page.locator("h1")).to_have_count(1)
                assert page.locator("html").get_attribute("lang").startswith(lang)
                expect(page.locator('link[rel="canonical"]')).to_have_attribute("href", BASE + path)
                other = "pl" if index else "en"
                expect(page.locator(f'link[hreflang="{other}"]')).to_have_attribute("href", BASE + paths[1-index])
                expect(page.locator(f'.fl-languages a[data-language="{other}"]:not([hidden])').first).to_have_attribute("href", BASE + paths[1-index])
                expect(page.locator(".fl-nav .wp-block-navigation-item")).to_have_count(6)
                assert page.evaluate("document.documentElement.scrollWidth <= innerWidth"), path
                page.evaluate("document.fonts.ready")
                for img in page.locator("main img").all():
                    img.scroll_into_view_if_needed()
                    img.evaluate("img => img.decode()")
                assert not errors, (path, errors)
                if axe_path and path in ("/", "/realizacje/", "/kontakt/", "/en/contact/"):
                    page.add_script_tag(path=str(axe_path))
                    issues = page.evaluate("""async () => (await axe.run(document, {
                        runOnly: {type: 'tag', values: ['wcag2a','wcag2aa','wcag21a','wcag21aa','wcag22aa']}
                    })).violations.map(x => x.id)""")
                    assert not issues, (path, width, issues)
                if screenshots and (index == 0 or path == "/en/") and paths in PAIRS[:6]:
                    page.screenshot(path=str(screenshots / f"{path.strip('/') or 'home'}-{width}.png"), full_page=True)
                page.close()
        print(f"{width}px: 22 pages OK", flush=True)


def check_interactions(browser, send_mail):
    page = browser.new_page(locale="en-GB", timezone_id="Europe/London")
    page.goto(BASE + "/uslugi/?utm_source=qa&gclid=sample")
    expect(page.locator(".fl-language-hint")).to_be_visible()
    page.locator('.fl-languages a[data-language="en"]').first.click()
    expect(page).to_have_url(BASE + "/en/services/?utm_source=qa&gclid=sample")
    assert page.evaluate("localStorage.getItem('fiberlan-language')") == "en"
    page.goto(BASE + "/")
    summary = page.locator(".fl-ribbon summary")
    summary.press("Enter")
    expect(page.locator(".fl-ribbon details")).to_have_attribute("open", "")
    summary.press("Escape")
    expect(page.locator(".fl-ribbon details")).not_to_have_attribute("open", "")
    expect(summary).to_be_focused()
    for url, slug in (("/realizacje/", "pl-marine"), ("/en/projects/", "en-marine")):
        page.goto(BASE + url)
        expect(page.locator(".fl-case-grid article")).to_have_count(3)
        page.locator(f'.fl-filters a[href*="{slug}"]').click()
        expect(page.locator(".fl-case-grid article")).to_have_count(1)
        expect(page.locator(".fl-case-grid article a")).to_have_attribute("href", BASE + url + "marine/")
    page.goto(BASE + "/kontakt/")
    result = page.request.post(BASE + "/kontakt/", form={"fl_action": "contact", "fl_nonce": "bogus"})
    assert result.status == 400
    token = page.locator('[name="fl_nonce"]').get_attribute("value")
    result = page.request.post(BASE + "/kontakt/", form={"fl_action": "contact", "fl_nonce": token, "fl_website": "spam"})
    assert result.status == 400
    result = page.request.post(BASE + "/kontakt/", form={"fl_action": "contact", "fl_nonce": token, "fl_email": "invalid"})
    assert result.status in ((422,) if send_mail else (422, 429))
    if send_mail:
        for lang, url in (("pl", "/kontakt/"), ("en", "/en/contact/")):
            page.goto(BASE + url + "?utm_source=stage-two")
            ticket = f"FIBERLAN QA {lang} {uuid4().hex[:8]}"
            for key, value in {"name": "QA FIBERLAN", "email": "qa@example.test", "subject": ticket, "message": "Local delivery test"}.items():
                page.locator(f'[name="fl_{key}"]').fill(value)
            page.locator('[name="fl_privacy"]').check()
            page.locator(".fl-form button").click()
            expect(page.locator(".fl-form-status.is-success")).to_be_visible()
            assert "utm_source=stage-two" in page.url
            result = page.request.get(INBOX + "/api/v1/messages").json()
            mail = next(m for m in result["messages"] if ticket in m["Subject"])
            assert mail["To"][0]["Address"] == "info@fiberlan.pl"
            email = page.request.get(INBOX + "/api/v1/message/" + mail["ID"]).json()
            assert "Local delivery test" in email["Text"]
            assert email["ReplyTo"][0]["Address"] == "qa@example.test"
            page.reload()
            expect(page.locator(".fl-form-status.is-success")).to_have_count(0)
            print(f"{lang}: accepted by local SMTP and verified in Mailpit", flush=True)
    page.close()
    print("Language preference, Ribbon, project filters, nonce, honeypot and validation OK", flush=True)


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--mail", action="store_true")
    parser.add_argument("--screens", type=Path)
    parser.add_argument("--axe", type=Path)
    args = parser.parse_args()
    if args.screens:
        args.screens.mkdir(parents=True, exist_ok=True)
    with sync_playwright() as p:
        browser = p.chromium.launch(executable_path=shutil.which("chromium"))
        check_pages(browser, args.screens, args.axe)
        check_interactions(browser, args.mail)
        browser.close()
