"""Check SEO and consent-gated conversions in an isolated local WordPress."""
import argparse
import json
import shutil
from urllib.parse import urlsplit
from uuid import uuid4

from playwright.sync_api import expect, sync_playwright
from stage_two_smoke import PAIRS


def check_seo(browser, base, public, rank_math):
    page = browser.new_page()
    titles, descriptions = set(), set()
    for pair in PAIRS:
        for path in pair:
            page.goto(base + path)
            headings = page.locator("main :is(h1,h2,h3)").evaluate_all("nodes => nodes.map(n => Number(n.tagName[1]))")
            assert headings and headings[0] == 1 and headings.count(1) == 1, (path, headings)
            assert all(current <= previous + 1 for previous, current in zip(headings, headings[1:])), (path, headings)
            title = page.title()
            description = page.locator('meta[name="description"]')
            expect(description).to_have_count(1)
            summary = description.get_attribute('content')
            assert title and summary and title not in titles and summary not in descriptions, path
            titles.add(title)
            descriptions.add(summary)
            if path in ("/", "/uslugi/"):
                assert "spawanie światłowodów" in title.casefold() and "spawanie światłowodów" in summary.casefold(), path
                search_heading = "h1" if path == "/" else "#spawanie h2"
                expect(page.locator("main " + search_heading)).to_have_text("Spawanie światłowodów")
            policy = path in ('/polityka-prywatnosci/', '/polityka-cookies/', '/en/privacy-policy/', '/en/cookie-policy/')
            canonical = page.locator('link[rel="canonical"]')
            if not (rank_math and policy and not canonical.count()):
                expect(canonical).to_have_attribute('href', base + path)
            expect(page.locator('link[rel="alternate"][hreflang]')).to_have_count(3)
            expect(page.locator('meta[property="og:title"]')).to_have_count(1)
            expect(page.locator('meta[property="og:locale"]')).to_have_attribute('content', 'en_GB' if path.startswith('/en/') else 'pl_PL')
            expect(page.locator('meta[name="twitter:card"]')).to_have_count(1)
            graph = page.locator('script[type="application/ld+json"]')
            expect(graph).to_have_count(1)
            data = json.loads(graph.text_content())
            assert data['@context'] == 'https://schema.org', path
            types = {kind for node in data['@graph'] for kind in ([node['@type']] if isinstance(node['@type'], str) else node['@type'])}
            assert {'Organization', 'LocalBusiness', 'WebPage', 'WebSite'} <= types, (path, types)
            if path not in ('/', '/en/'):
                assert 'BreadcrumbList' in types
                expect(page.locator('.fl-crumbs')).to_have_count(1)
            if path in ('/uslugi/', '/en/services/'):
                assert 'Service' in types
                sections = page.locator('main section[id]:has(h2)').evaluate_all('nodes => nodes.map(n => n.id)')
                assert {'spawanie', 'instalacje', 'pomiary', 'lan', 'data-center'} <= set(sections)
                links = page.locator('.fl-service-index a').evaluate_all('nodes => nodes.map(a => a.hash.slice(1))')
                assert links == sections, (path, sections, links)
            if path in ('/kontakt/', '/en/contact/'):
                response = page.request.get(base + path)
                assert 'no-store' in response.headers['cache-control']
            robots = (page.locator('meta[name="robots"]').first.get_attribute('content') or '') if page.locator('meta[name="robots"]').count() else ''
            assert ('noindex' in robots) == (policy or not public), (path, robots)
    for prefix, label in (('', 'Nie znaleziono strony'), ('/en', 'Page not found')):
        response = page.goto(base + prefix + '/fiberlan-404-invalid-check/')
        assert response.status == 404
        expect(page.locator('h1')).to_have_text(label)
        assert label in page.title()
    if public:
        sitemap_url = '/page-sitemap.xml' if rank_math else '/wp-sitemap-posts-page-1.xml'
        sitemap = page.request.get(base + sitemap_url)
        assert sitemap.ok
        for path in ('/uslugi/', '/en/services/'):
            assert base + path in sitemap.text()
        for path in ('/polityka-prywatnosci/', '/en/privacy-policy/', '/polityka-cookies/', '/en/cookie-policy/'):
            assert base + path not in sitemap.text()
        assert page.request.get(base + '/robots.txt').ok
    page.close()
    print(f'{len(titles)} unique titles, descriptions and schema graphs; 404/robots/metadata OK', flush=True)


def check_campaign(browser, base, send_mail):
    page = browser.new_page(viewport={'width': 390, 'height': 1000})
    page.add_init_script("window.getCkyConsent = () => ({ categories: { analytics: false, advertisement: false } });")
    campaign = '?utm_source=fiberlan-qa&gclid=test-campaign&gbraid=test-app&wbraid=test-web'
    page.goto(base + '/' + campaign)
    picture = page.locator('.fl-hero-figure picture img')
    picture.evaluate('img => img.decode()')
    assert picture.evaluate('img => img.currentSrc.endsWith(".webp")')
    assert page.evaluate("document.fonts.check('700 30px FIBERLAN Britannic')")
    page.locator('.fl-hero .wp-block-button__link').first.click()
    expect(page).to_have_url(base + '/kontakt/' + campaign)
    assert not page.evaluate("(window.dataLayer || []).some(x => x.event === 'fiberlan_lead')")
    if send_mail:
        for lang, route, accepted in (('pl', '/kontakt/', 'analytics'), ('en', '/en/contact/', 'advertisement')):
            page.goto(base + route + campaign)
            subject = 'FIBERLAN conversion QA ' + uuid4().hex[:8]
            for key, val in dict(name='Local QA', email='qa@example.test', subject=subject, message='Synthetic conversion test').items():
                page.locator(f'[name="fl_{key}"]').fill(val)
            page.locator('[name="fl_privacy"]').check()
            page.locator('.fl-form button').click()
            expect(page.locator('.fl-form-status.is-success')).to_be_visible()
            assert 'received=' not in page.url and campaign in page.url, page.url
            assert not page.evaluate("(window.dataLayer || []).some(x => x.event === 'fiberlan_lead')")
            events = page.evaluate("""accepted => {
                for (let i = 0; i < 2; i++) {
                    document.dispatchEvent(new CustomEvent('cookieyes_consent_update', {
                        detail: { accepted: ['necessary', accepted] }
                    }));
                }
                return (window.dataLayer || []).filter(x => x.event === 'fiberlan_lead');
            }""", accepted)
            expected = dict(event='fiberlan_lead', form_id='fiberlan_contact', form_language=lang,
                            analytics_consent=accepted == 'analytics', advertising_consent=accepted == 'advertisement')
            assert events == [expected], events
            page.reload()
            expect(page.locator('[data-fiberlan-lead]')).to_have_count(0)
        print('SMTP acknowledged in PL/EN; one consent-gated conversion per receipt with no submitted data', flush=True)
    page.close()
    print('Campaign parameters and responsive WebP verified', flush=True)


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--url', default='http://127.0.0.1:8080')
    parser.add_argument('--public', action='store_true', help='Check crawl policies on an isolated, index-enabled fixture.')
    parser.add_argument('--rank-math', action='store_true')
    parser.add_argument('--mail', action='store_true')
    args = parser.parse_args()
    if urlsplit(args.url).hostname not in {'localhost', '127.0.0.1', '::1'}:
        parser.error('Use the local development or QA WordPress.')
    with sync_playwright() as runner:
        browser = runner.chromium.launch(executable_path=shutil.which('chromium'))
        check_seo(browser, args.url.rstrip('/'), args.public, args.rank_math)
        check_campaign(browser, args.url.rstrip('/'), args.mail)
        browser.close()
