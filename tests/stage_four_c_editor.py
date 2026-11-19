"""Exercise direct Gutenberg editing on a disposable local FIBERLAN Child installation."""
import argparse
import re
import shutil
from pathlib import Path
from urllib.parse import parse_qs, urlparse
from uuid import uuid4

from playwright.sync_api import expect, sync_playwright

from editor_smoke import check_blocks, open_entity as load_entity, rest, wait_editor


def canvas(page):
    return page.frame_locator('iframe[name="editor-canvas"]')


def open_entity(page, base, collection, key):
    load_entity(page, base, collection, key)
    dismiss_guides(page)


def dismiss_guides(page):
    page.evaluate("""() => {
        const prefs = wp.data.dispatch('core/preferences');
        for (const scope of ['core/edit-post', 'core/edit-site', 'core'])
            for (const key of ['welcomeGuide', 'welcomeGuideTemplate', 'welcomeGuideStyles', 'welcomeGuidePage'])
                prefs.set(scope, key, false);
    }""")
    dialog = page.locator('.components-modal__screen-overlay .components-guide')
    if dialog.count() and dialog.first.is_visible():
        page.keyboard.press("Escape")


def save(page):
    page.evaluate("async () => { await wp.data.dispatch('core/editor').savePost(); }")


def fill_copy(block, field, text):
    element = block.locator(f'[data-fl-copy="{field}"]')
    element.click()
    element.fill(text)
    element.press("Tab")


def forms(page, base, suffix):
    for slug, template in (("kontakt", "page-fiberlan"), ("contact", "page-en")):
        source = rest(page, f"/wp/v2/pages?slug={slug}&context=edit")[0]
        data = {"status": "draft", "title": suffix, "content": source["content"]["raw"],
                "template": template, "parent": source["parent"]}
        draft = rest(page, "/wp/v2/pages", "POST", data)
        endpoint = f"/wp/v2/pages/{draft['id']}"
        try:
            open_entity(page, base, "pages", draft["id"])
            block = canvas(page).locator('[data-type="fiberlan/contact"]')
            keys = "name company email phone subject message privacy policy submit".split()
            states = "success invalid failure limited".split()
            values = {k: f"{slug} {k} · Łódź & R&D {suffix}" for k in keys + states}
            for key in keys:
                fill_copy(block, key, values[key])
            for key in states:
                block.locator("select").select_option(key)
                fill_copy(block, key, values[key])
            url = f"{base}/?qa={slug}"
            block.locator(".fl-editor-url input").fill(url)
            block.locator(".fl-editor-url input").press("Tab")
            save(page)
            open_entity(page, base, "pages", draft["id"])
            for key in keys:
                expect(block.locator(f'[data-fl-copy="{key}"]')).to_have_text(values[key])
            for key in states:
                block.locator("select").select_option(key)
                expect(block.locator(f'[data-fl-copy="{key}"]')).to_have_text(values[key])
            check_blocks(page, rest(page, endpoint + "?context=edit")["content"]["raw"], slug)
            preview = page.context.new_page()
            try:
                preview.goto(f"{base}/?page_id={draft['id']}&preview=true")
                expect(preview.locator(".fl-form button")).to_have_text(values["submit"])
                expect(preview.locator(".fl-form").get_by_role("link", name=values["policy"])).to_have_attribute("href", url)
            finally:
                preview.close()
        finally:
            rest(page, endpoint + "?force=true", "DELETE")


def new_projects(page, base, suffix):
    for slug, lang in (("realizacje", "pl"), ("projects", "en")):
        archive = rest(page, f"/wp/v2/pages?slug={slug}")[0]
        open_entity(page, base, "pages", archive["id"])
        with page.context.expect_page() as popup:
            canvas(page).get_by_role("button", name="Dodaj realizację").click()
        edit = popup.value
        edit.wait_for_url("**/wp-admin/post.php?post=*&action=edit")
        project_id = int(parse_qs(urlparse(edit.url).query)["post"][0])
        endpoint = f"/wp/v2/pages/{project_id}"
        try:
            wait_editor(edit)
            dismiss_guides(edit)
            item = rest(page, endpoint + "?context=edit")
            assert (item["status"], item["parent"], item["template"]) == ("draft", archive["id"], "project-" + lang)
            title = f"QA {lang} {suffix}"
            body = canvas(edit)
            body.locator('.editor-post-title__input, .wp-block-post-title[contenteditable=true]').first.fill(title)
            body.locator('[data-type="core/paragraph"][contenteditable=true]').fill("Próba edycji treści " + suffix)
            image = body.locator('[data-type="core/image"]')
            image.click()
            image.get_by_role("button", name=re.compile("Media Library|Biblioteka mediów", re.I)).click()
            media = rest(page, "/wp/v2/media?search=Fluke")[0]
            modal = edit.locator(".media-modal:visible")
            modal.locator(f'.attachment[data-id="{media["id"]}"]').click()
            modal.locator("button.media-button-select").click()
            expect(image.locator("img")).to_be_visible()
            image.click()
            alt = edit.get_by_role("textbox", name=re.compile("alternative text|tekst alternatywny", re.I))
            if not alt.is_visible():
                edit.locator('button[aria-label="Settings"],button[aria-label="Ustawienia"]').first.click()
            alt.fill(title)
            alt.press("Tab")
            save(edit)
            open_entity(edit, base, "pages", project_id)
            expect(canvas(edit).locator('[data-type="core/image"] img')).to_have_attribute("alt", title)
            check_blocks(edit, rest(page, endpoint + "?context=edit")["content"]["raw"], "new project " + lang)
            published = rest(page, endpoint, "POST", {"status": "publish"})
            public = page.context.new_page()
            try:
                public.goto(published["link"])
                expect(public.locator("main h1")).to_have_text(title)
                expect(public.locator("main img").first).to_have_attribute("alt", title)
                public.goto(archive["link"])
                card = public.locator(".fl-case-grid article").filter(has_text=title)
                expect(card.locator("a")).to_have_attribute("href", published["link"])
                expect(card.locator("img")).to_have_attribute("alt", title)
            finally:
                public.close()
            open_entity(page, base, "pages", archive["id"])
            expect(canvas(page).get_by_role("link", name=title)).to_have_attribute("href", re.compile(f"post={project_id}&action=edit"))
        finally:
            edit.close()
            rest(page, endpoint + "?force=true", "DELETE")


def site_parts(page, base, suffix):
    theme = rest(page, "/wp/v2/themes?status=active&context=edit")[0]["stylesheet"]
    for slug, collection, selector, field, route in (
        ("footer", "template-parts", '[data-type="fiberlan/consent"], [data-type="core/html"]', "label", "/"),
        ("footer-en", "template-parts", '[data-type="fiberlan/consent"], [data-type="core/html"]', "label", "/en/"),
        ("header", "template-parts", '[data-type="fiberlan/languages"]', "plText", "/"),
        ("header-en", "template-parts", '[data-type="fiberlan/languages"]', "enText", "/en/"),
        ("page-en", "templates", '[data-type="fiberlan/breadcrumbs"]', "home", "/en/services/"),
        ("page-fiberlan", "templates", '[data-type="fiberlan/breadcrumbs"]', "home", "/uslugi/"),
        ("404", "templates", '[data-type="fiberlan/not-found"]', "heading", "/qa-missing-" + suffix),
        ("404-en", "templates", '[data-type="fiberlan/not-found"]', "heading", "/en/qa-missing-" + suffix),
    ):
        entity_id = theme + "//" + slug
        endpoint = f"/wp/v2/{collection}/{entity_id}"
        before = rest(page, endpoint + "?context=edit")
        text = f"{slug} • {suffix}"
        menus = {m["id"]: m["content"]["raw"] for m in rest(page, "/wp/v2/navigation?context=edit&per_page=100")}
        try:
            open_entity(page, base, collection, entity_id)
            block = canvas(page).locator(selector)
            if slug == "404-en":
                expect(block.locator('[data-fl-copy="heading"]')).to_have_text("Page not found")
            fill_copy(block, field, text)
            if slug.startswith("footer"):
                copyright = canvas(page).locator('[data-type="fiberlan/copyright"]')
                fill_copy(copyright, "rights", "Rights " + text)
            if slug.startswith("page-"):
                index = canvas(page).locator('[data-type="fiberlan/service-links"]')
                fill_copy(index, "heading", "Index " + text)
            if slug.startswith("404"):
                fill_copy(block, "button", "Home " + text)
                block.locator(".fl-editor-url input").fill(base + "/?qa=" + suffix)
                block.locator(".fl-editor-url input").press("Tab")
            if slug.startswith("header"):
                # The navigation block can hold links inline or reference a wp_navigation entity.
                link = canvas(page).locator('[data-type="core/navigation-link"] [contenteditable="true"]').first
                link.click()
                link.fill("Menu " + text)
                link.press("Tab")
            save(page)
            if slug.startswith("header"):
                page.evaluate("""async () => {
                    const menus = wp.data.select('core').__experimentalGetDirtyEntityRecords()
                        .filter(record => record.name === 'wp_navigation');
                    for (const {kind, name, key} of menus)
                        await wp.data.dispatch('core').saveEditedEntityRecord(kind, name, key);
                }""")
            open_entity(page, base, collection, entity_id)
            expect(block.locator(f'[data-fl-copy="{field}"]')).to_have_text(text)
            public = page.context.new_page()
            try:
                public.goto(base + route)
                if slug.startswith("page-"):
                    expect(public.locator(".fl-service-index h2")).to_have_text("Index " + text)
                    expect(public.locator(".fl-crumbs li a").first).to_have_text(text)
                elif slug.startswith("404"):
                    expect(public.locator("main h1")).to_have_text(text)
                    expect(public.get_by_role("link", name="Home " + text)).to_have_attribute("href", base + "/?qa=" + suffix)
                elif slug.startswith("footer"):
                    expect(public.locator(".fl-consent-settings")).to_have_text(text)
                    expect(public.locator(".fl-copyright")).to_contain_text("Rights " + text)
                else:
                    lang = "en" if slug.endswith("-en") else "pl"
                    expect(public.locator(f'.fl-languages [hreflang="{lang}"]').first).to_have_text(text)
                    expect(public.locator("header nav").get_by_text("Menu " + text, exact=True).first).to_have_count(1)
            finally:
                public.close()
        finally:
            current = rest(page, endpoint + "?context=edit")
            if current.get("wp_id") and before["content"]["raw"] != current["content"]["raw"]:
                if before["source"] == "custom":
                    rest(page, endpoint, "POST", {"content": before["content"]["raw"]})
                else:
                    rest(page, endpoint + "?force=true", "DELETE")
            if slug.startswith("header"):
                for menu in rest(page, "/wp/v2/navigation?context=edit&per_page=100"):
                    if menu["id"] in menus and menus[menu["id"]] != menu["content"]["raw"]:
                        rest(page, f"/wp/v2/navigation/{menu['id']}", "POST", {"content": menus[menu["id"]]})
                    elif menu["id"] not in menus and text in menu["content"]["raw"]:
                        rest(page, f"/wp/v2/navigation/{menu['id']}?force=true", "DELETE")
        print(f"Site Editor: {slug} saved, re-opened and rendered", flush=True)


def cards(page, base, suffix):
    home_id = rest(page, "/wp/v2/settings")["page_on_front"]
    home = rest(page, f"/wp/v2/pages/{home_id}?context=edit")
    patterns = rest(page, "/wp/v2/block-patterns/patterns")
    card = next(p["content"] for p in patterns if p["name"] == "fiberlan-child/service-card")
    check_blocks(page, card, "service card")
    entity = rest(page, "/wp/v2/pages", "POST", {"title": suffix, "status": "draft", "content": home["content"]["raw"]})
    endpoint = f"/wp/v2/pages/{entity['id']}"
    try:
        open_entity(page, base, "pages", entity["id"])
        order = page.evaluate("""raw => {
            const editor = wp.data.select('core/block-editor');
            const edit = wp.data.dispatch('core/block-editor');
            const grid = editor.getClientIdsWithDescendants().map(id => editor.getBlock(id))
                .find(b => b.innerBlocks.some(child => child.attributes.className === 'fl-service-card'));
            const [first] = grid.innerBlocks;
            edit.insertBlocks(wp.blocks.parse(raw), 0, grid.clientId);
            edit.removeBlock(first.clientId);
            const sections = editor.getBlocks();
            edit.moveBlockToPosition(sections[2].clientId, '', '', 0);
            return [grid.innerBlocks.length, sections[2].attributes.className];
        }""", card)
        new_card = canvas(page).locator(".fl-service-card").first
        new_card.locator('h3[contenteditable=true]').fill(suffix)
        new_card.locator('p[contenteditable=true]').fill("Opis " + suffix)
        save(page)
        open_entity(page, base, "pages", entity["id"])
        expect(new_card.locator('h3[contenteditable=true]')).to_have_text(suffix)
        count = page.evaluate("""() => {
            const editor = wp.data.select('core/block-editor');
            return editor.getClientIdsWithDescendants().filter(id => editor.getBlockAttributes(id).className === 'fl-service-card').length;
        }""")
        assert count == order[0], (count, order)
        assert page.evaluate("wp.data.select('core/block-editor').getBlocks()[0].attributes.className") == order[1]
        check_blocks(page, rest(page, endpoint + "?context=edit")["content"]["raw"], "reordered home")
    finally:
        rest(page, endpoint + "?force=true", "DELETE")


def shared_content(page, base, suffix):
    theme = rest(page, "/wp/v2/themes?status=active&context=edit")[0]["stylesheet"]
    original_logo = rest(page, "/wp/v2/settings")["site_logo"]
    photo = rest(page, "/wp/v2/media?search=VIAVI")[0]
    parts = rest(page, "/wp/v2/blocks?context=edit&search=FIBERLAN")
    contact = next(item for item in parts if "kontaktowe" in item["title"]["raw"])
    try:
        open_entity(page, base, "template-parts", theme + "//header")
        logo = canvas(page).locator('[data-type="core/site-logo"]').first
        logo.click()
        page.get_by_role("button", name=re.compile("^(Replace|Zastąp)$")).click()
        page.get_by_role("menuitem", name=re.compile("Media Library|Bibliotek", re.I)).click()
        modal = page.locator(".media-modal:visible")
        modal.locator(f'.attachment[data-id="{photo["id"]}"]').click()
        modal.locator(".media-button-select").click()
        page.evaluate("""async () => {
            const store = wp.data.select('core');
            for (const {kind, name, key} of store.__experimentalGetDirtyEntityRecords())
                if (kind === 'root' && name === 'site')
                    await wp.data.dispatch('core').saveEditedEntityRecord(kind, name, key);
        }""")
        assert rest(page, "/wp/v2/settings")["site_logo"] == photo["id"]
        open_entity(page, base, "blocks", contact["id"])
        address = canvas(page).locator('.fl-address[contenteditable=true]')
        address.click()
        address.fill("Adres " + suffix)
        address.press("Tab")
        save(page)
        open_entity(page, base, "blocks", contact["id"])
        expect(address).to_have_text("Adres " + suffix)
        public = page.context.new_page()
        try:
            for path in ["/kontakt/", "/en/contact/"]:
                public.goto(base + path)
                for area in ["header", "footer"]:
                    expect(public.locator(area + " .fl-address")).to_have_text("Adres " + suffix)
                    expect(public.locator(area + " .custom-logo")).to_have_attribute("alt", photo["alt_text"])
                expect(public.locator("main .fl-address")).to_have_text("Adres " + suffix)
        finally:
            public.close()
    finally:
        rest(page, "/wp/v2/settings", "POST", {"site_logo": original_logo})
        rest(page, f"/wp/v2/blocks/{contact['id']}", "POST", {"content": contact["content"]["raw"]})


def legacy_footer(page, base, suffix):
    raw = '<!-- wp:html --><button type="button" class="fl-consent-settings" hidden>Consent settings</button><!-- /wp:html -->'
    draft = rest(page, "/wp/v2/pages", "POST", {"title": suffix, "status": "draft", "content": raw})
    endpoint = f"/wp/v2/pages/{draft['id']}"
    try:
        open_entity(page, base, "pages", draft["id"])
        old = canvas(page).locator('[data-type="core/html"]')
        text = "Zgody & cookies " + suffix
        fill_copy(old, "label", text)
        save(page)
        open_entity(page, base, "pages", draft["id"])
        expect(old.locator('[data-fl-copy="label"]')).to_have_text(text)
        saved = rest(page, endpoint + "?context=edit")["content"]
        assert "fl-consent-settings" in saved["rendered"] and "Zgody &amp; cookies " + suffix in saved["rendered"]
        check_blocks(page, saved["raw"], "4.0 footer control")
    finally:
        rest(page, endpoint + "?force=true", "DELETE")


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("base_url")
    parser.add_argument("password_file", type=Path)
    parser.add_argument("--only", choices=["forms", "new_projects", "site_parts", "cards", "shared_content", "legacy_footer"])
    args = parser.parse_args()
    base = args.base_url.rstrip("/")
    if urlparse(base).hostname not in {"localhost", "127.0.0.1", "::1"}:
        parser.error("Use a disposable local installation.")
    with sync_playwright() as pw:
        browser = pw.chromium.launch(executable_path=shutil.which("chromium"))
        page = browser.new_context(viewport={"width": 1440, "height": 1000}).new_page()
        page.goto(base + "/wp-login.php")
        page.locator("#user_login").fill("fiberlan-editor")
        page.locator("#user_pass").fill(args.password_file.read_text().strip())
        page.locator("#wp-submit").click()
        page.wait_for_url("**/wp-admin/**")
        page.goto(base + "/wp-admin/post-new.php?post_type=page")
        wait_editor(page)
        for test in (forms, new_projects, site_parts, cards, shared_content, legacy_footer):
            if not args.only or test.__name__ == args.only:
                test(page, base, uuid4().hex[:8])
                print(test.__name__, "OK", flush=True)
        browser.close()
