# Pomiar wydajności etapu 3

Pomiary z 19 listopada 2026 wykonano za pomocą Lighthouse 13.6.0 oraz Chromium 156 na lokalnym WordPressie 7.1.5. Użyto standardowego profilu mobilnego Lighthouse i presetu desktop. Poniżej zestawiono po jednym przebiegu na adres i profil przed zmianami i po zmianach.

| Adres | Profil | Performance przed → po | LCP przed → po | CLS po | Transfer przed → po |
| --- | --- | ---: | ---: | ---: | ---: |
| `/` | mobilny | 99 → 100 | 2,03 → 1,89 s | 0,000 | 282 → 254 KiB |
| `/` | desktop | 100 → 100 | 0,45 → 0,39 s | 0,005 | 246 → 128 KiB |
| `/uslugi/` | mobilny | 99 → 100 | 2,12 → 0,90 s | 0,000 | 191 → 166 KiB |
| `/uslugi/` | desktop | 100 → 100 | 0,69 → 0,37 s | 0,000 | 403 → 121 KiB |
| `/en/contact/` | mobilny | 100 → 100 | 0,76 → 0,76 s | 0,000 | 87 → 64 KiB |
| `/en/contact/` | desktop | 100 → 100 | 0,21 → 0,33 s | 0,000 | 87 → 64 KiB |

Po zmianach Lighthouse zwrócił 100 dla Performance, Accessibility i Best Practices we wszystkich sześciu przebiegach. TBT wyniósł 0 ms. Wyniki SEO osiągnęły 69 na celowo nieindeksowalnym środowisku deweloperskim. Osobny audyt SEO strony głównej na odizolowanej kopii z włączonym indeksowaniem i Rank Math dał 100. Dane strukturalne sprawdzono testem automatycznym grafu w kodzie HTML. Lighthouse pozostawia osobną ocenę rich results weryfikacji ręcznej.

Środowisko pomiarowe jest lokalne. Pomiary nie obejmują produkcyjnego LiteSpeed, QUIC.cloud, połączenia z CookieYes, tagów reklamowych ani danych rzeczywistych użytkowników. INP wymaga pomiaru interakcji lub danych terenowych. PageSpeed Insights dla aktualnego projektu wymaga adresu publicznego z wdrożoną wersją.
