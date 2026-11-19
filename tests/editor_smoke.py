"""Round-trip editable blocks on disposable copies in a local Gutenberg editor.

Usage: python tests/editor_smoke.py http://127.0.0.1:8080 .local/admin-password
Requires Python Playwright, Chromium and the installed FIBERLAN starter.
"""
import argparse
import json
import shutil
from pathlib import Path
from urllib.parse import quote, urlparse
from uuid import uuid4

from playwright.sync_api import sync_playwright


def rest(page, path, method="GET", data=None):
    return page.evaluate(
        """async ([path, method, data]) => {
            try { return await wp.apiFetch({path, method, data}); }
            catch (error) { throw new Error(error.code + ': ' + error.message); }
        }""",
        [path, method, data],
    )


def wait_editor(page):
    page.wait_for_function("window.wp?.apiFetch && wp.data.select('core/block-editor')")


def check_blocks(page, raw, label):
    result = page.evaluate(
        """raw => {
            const invalid = [], names = [];
            function visit(blocks) {
                for (const block of blocks) {
                    names.push(block.name);
                    if (!block.isValid || block.name === 'core/missing' || block.name === 'core/freeform')
                        invalid.push(block.name + ' ' + (block.originalContent || '').slice(0, 120));
                    visit(block.innerBlocks);
                }
            }
            visit(wp.blocks.parse(raw));
            return {names, invalid};
        }""",
        raw,
    )
    assert result["names"] and not result["invalid"], (label, result)
    print(f"{label}: {len(result['names'])} native blocks valid", flush=True)


def open_entity(page, base, path, entity_id):
    if path in ("template-parts", "templates"):
        entity = "wp_template_part" if path == "template-parts" else "wp_template"
        location = f"/wp-admin/site-editor.php?p=/{entity}/{quote(str(entity_id), safe='')}&canvas=edit"
    else:
        location = f"/wp-admin/post.php?post={entity_id}&action=edit"
    page.goto(base + location, wait_until="domcontentloaded")
    wait_editor(page)
    page.wait_for_function("""id => {
        const store = wp.data.select('core/editor');
        return String(store?.getCurrentPostId()) === String(id) &&
            wp.data.select('core/block-editor').getBlockOrder().length > 0;
    }""", arg=entity_id, timeout=30000)


def roundtrip(page, base, path, raw, suffix, slug, image, theme=None):
    # Use unpublished copies for page content and distinct, unreferenced slugs for parts.
    data = {"title": f"Test FIBERLAN {suffix}", "content": raw}
    if path == "template-parts":
        data.update({"slug": f"fiberlan-qa-{slug}-{suffix}", "theme": theme, "area": slug.split("-")[0]})
    else:
        data["status"] = "draft"
    entity = rest(page, f"/wp/v2/{path}", "POST", data)
    key = quote(str(entity["id"]), safe="/")
    endpoint = f"/wp/v2/{path}/{key}"
    try:
        open_entity(page, base, path, entity["id"])
        counts = page.evaluate(
            """async ([suffix,image]) => {
                const editor = wp.data.select('core/block-editor');
                const change = wp.data.dispatch('core/block-editor');
                const counts = {};
                function walk(blocks) {
                    for (const block of blocks) {
                        const {attributes: a, name, clientId} = block;
                        const updates = {};
                        if (['core/heading', 'core/paragraph', 'core/details'].includes(name)) {
                            const field = name === 'core/details' ? 'summary' : 'content';
                            updates[field] = String(a[field] || '') + ' ' + suffix;
                        } else if (name === 'core/image') {
                            updates.id = image.id;
                            updates.url = image.url;
                            updates.alt = 'QA ' + suffix;
                            updates.caption = 'QA ' + suffix;
                        } else if (name === 'core/button') {
                            updates.url = '#qa-' + suffix;
                            updates.text = String(a.text) + ' ' + suffix;
                        } else if (name.startsWith('fiberlan/')) {
                            const fields = window.fiberlanEditor.copy.pl[name.split('/')[1]];
                            for (const key of Object.keys(fields)) {
                                updates[key] = key.toLowerCase().endsWith('url')
                                    ? '#qa-' + suffix : String(a[key] || fields[key]) + ' ' + suffix;
                            }
                        } else if (['core/home-link', 'core/navigation-link'].includes(name)) {
                            updates.label = String(a.label || '') + ' ' + suffix;
                        }
                        if (Object.keys(updates).length) {
                            change.updateBlockAttributes(clientId, updates);
                            counts[name] = (counts[name] || 0) + 1;
                        }
                        walk(block.innerBlocks);
                    }
                }
                walk(editor.getBlocks());
                if (!Object.keys(counts).length) throw new Error('No editable blocks found.');
                await wp.data.dispatch('core/editor').savePost();
                return counts;
            }""",
            [suffix, image],
        )
        saved = rest(page, endpoint + "?context=edit")["content"]["raw"]
        assert suffix in saved, f"{slug}: edited content not present in saved record"
        open_entity(page, base, path, entity["id"])
        check_blocks(page, saved, slug + " after save and reopen")
        verified = page.evaluate(
            """([suffix,image]) => {
                const counts = {};
                function walk(blocks) {
                    for (const b of blocks) {
                        if (!b.isValid) throw new Error('Invalid block: ' + b.name);
                        const a = b.attributes;
                        const fields = b.name.startsWith('fiberlan/') ? Object.keys(window.fiberlanEditor.copy.pl[b.name.split('/')[1]]) : {
                            'core/image': ['alt','caption'], 'core/button': ['url','text'],
                            'core/heading': ['content'], 'core/paragraph': ['content'],
                            'core/details': ['summary'], 'core/navigation-link': ['label'], 'core/home-link': ['label']
                        }[b.name];
                        if (b.name === 'core/image' && (a.url !== image.url || a.id !== image.id))
                            throw new Error('Image replacement was not persisted');
                        if (fields?.every(key => String(a[key]).includes(suffix)))
                            counts[b.name] = (counts[b.name] || 0) + 1;
                        walk(b.innerBlocks);
                    }
                }
                walk(wp.data.select('core/block-editor').getBlocks());
                return counts;
            }""",
            [suffix, image],
        )
        assert counts == verified, (slug, counts, verified)
        print(f"{slug}: saved and re-opened {json.dumps(verified)}", flush=True)
    finally:
        rest(page, endpoint + "?force=true", "DELETE")


def check_image_styles(page, base):
    raw = page.evaluate("""async () => {
        const [photo] = await wp.apiFetch({path: '/wp/v2/media?search=Fluke'});
        const image = className => wp.blocks.createBlock('core/image', {
            id: photo.id, url: photo.source_url, alt: 'Fluke Networks Versiv 2',
            caption: 'Zestaw pomiarowy '.repeat(6), className
        });
        return wp.blocks.serialize([
            image('fl-section-image is-style-fiberlan-full'),
            image('fl-section-image is-style-fiberlan-wide'),
            wp.blocks.createBlock('core/gallery', {className: 'is-style-fiberlan-gallery'}, [image(), image()])
        ]);
    }""")
    check_blocks(page, raw, "Gallery and image styles")
    preview = page.context.new_page()
    draft = rest(page, "/wp/v2/pages", "POST", {"title": "QA kadrowanie", "status": "draft", "content": raw})
    try:
        preview.set_viewport_size({"width": 390, "height": 840})
        preview.goto(f"{base}/?page_id={draft['id']}&preview=true")
        for style, expected in (("full", 3 / 4), ("wide", 16 / 9)):
            image = preview.locator(f".is-style-fiberlan-{style} img")
            image.scroll_into_view_if_needed()
            image.evaluate("img => img.decode()")
            ratio = image.evaluate("img => img.clientWidth / img.clientHeight")
            assert abs(ratio - expected) < 0.02, (style, ratio)
        caption = preview.locator(".is-style-fiberlan-gallery figcaption").first
        assert caption.evaluate("node => getComputedStyle(node).position") == "static"
    finally:
        preview.close()
        rest(page, f"/wp/v2/pages/{draft['id']}?force=true", "DELETE")


def run(page, base, username, password, stage_two):
    page.goto(base + "/wp-login.php")
    page.locator("#user_login").fill(username)
    page.locator("#user_pass").fill(password)
    page.locator("#wp-submit").click()
    page.wait_for_url("**/wp-admin/**")
    page.goto(base + "/wp-admin/post-new.php?post_type=page", wait_until="domcontentloaded")
    wait_editor(page)
    settings = rest(page, "/wp/v2/settings")
    home = rest(page, f"/wp/v2/pages/{settings['page_on_front']}?context=edit")
    theme = rest(page, "/wp/v2/themes?status=active&context=edit")[0]["stylesheet"]
    patterns = rest(page, "/wp/v2/block-patterns/patterns")
    theme_patterns = {x["name"]: x["content"] for x in patterns if x["name"].startswith(("fiberlan/", "fiberlan-child/"))}
    assert len(theme_patterns) >= 7, theme_patterns.keys()
    for name, raw in theme_patterns.items():
        check_blocks(page, raw, name)
    pages_to_edit = []
    if stage_two:
        check_image_styles(page, base)
        managed = [x for x in rest(page, "/wp/v2/pages?per_page=100&context=edit")
                   if x["template"] in ("page-fiberlan", "page-en", "project-pl", "project-en") or x["id"] == home["id"]]
        assert len(managed) >= 22
        for item in managed:
            check_blocks(page, item["content"]["raw"], item["link"])
        pages_to_edit = [x for x in managed if x["id"] != home["id"]]
        archive = next(x for x in pages_to_edit if urlparse(x["link"]).path == "/realizacje/")
        open_entity(page, base, "pages", archive["id"])
        canvas = page.frame_locator('iframe[name="editor-canvas"]')
        preview = canvas.locator(".fl-editor-projects li")
        preview.first.wait_for()
        assert preview.count() == 3
        fonts = canvas.locator("body").evaluate("""async () => {
            await document.fonts.ready;
            return [...document.fonts].filter(f => f.status === 'loaded').map(f => f.family);
        }""")
        assert {"Manrope", "FIBERLAN Britannic"} <= set(fonts), fonts
        print(f"{theme}: project catalogue and both editor fonts OK", flush=True)
    suffix = uuid4().hex[:8]
    # A locally created solid swatch exercises Media Library attachment replacement.
    image = page.evaluate("""async suffix => {
        const swatch = document.createElement('canvas');
        swatch.width = swatch.height = 16;
        const ctx = swatch.getContext('2d');
        ctx.fillStyle = '#0B132B'; ctx.fillRect(0, 0, 16, 16);
        const form = new FormData();
        form.append('file', await new Promise(ok => swatch.toBlob(ok)), 'qa-' + suffix + '.png');
        const media = await wp.apiFetch({path:'/wp/v2/media', method:'POST', body:form});
        return {id: media.id, url: media.source_url};
    }""", suffix)
    try:
        roundtrip(page, base, "pages", home["content"]["raw"], suffix, "home", image)
        for entry in pages_to_edit:
            roundtrip(page, base, "pages", entry["content"]["raw"], suffix, entry["link"], image)
        parts = ["header", "footer", "header-en", "footer-en"] if stage_two else ["header", "footer"]
        for slug in parts:
            roundtrip(page, base, "template-parts", theme_patterns[f"fiberlan/{slug}"], suffix, slug, image, theme)
        contacts = rest(page, "/wp/v2/blocks?context=edit&search=FIBERLAN")
        contact = next(item for item in contacts if "kontaktowe" in item["title"]["raw"])
        roundtrip(page, base, "blocks", contact["content"]["raw"], suffix, "contact", image)
    finally:
        rest(page, f"/wp/v2/media/{image['id']}?force=true", "DELETE")


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("base_url")
    parser.add_argument("password_file", type=Path)
    parser.add_argument("--user", default="fiberlan-editor")
    parser.add_argument("--stage-two", action="store_true")
    args = parser.parse_args()
    if urlparse(args.base_url).hostname not in {"localhost", "127.0.0.1", "::1"}:
        parser.error("This test requires a disposable, local WordPress installation.")
    with sync_playwright() as p:
        browser = p.chromium.launch(executable_path=shutil.which("chromium"))
        page = browser.new_context(viewport={"width": 1440, "height": 1000}).new_page()
        run(page, args.base_url.rstrip("/"), args.user, args.password_file.read_text().strip(), args.stage_two)
        browser.close()


if __name__ == "__main__":
    main()
