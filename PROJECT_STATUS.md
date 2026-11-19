# FIBERLAN SC · Status projektu

**19 listopada 2026 · Etap 3 zweryfikowany lokalnie.**

## Etap 3

- Dodano unikalne tytuły i opisy dla 22 adresów, edycję SEO w WordPressie, karty Open Graph i Twitter, ścieżki nawigacji, spis usług, 404 w obu językach oraz 301 po zmianie ścieżki. Mapy witryny uwzględniają oba języki i pomijają robocze polityki. Test obejmował również przekierowanie strony potomnej z UTM i GCLID.
- Dane Schema.org obejmują Organization, LocalBusiness, Service i BreadcrumbList. Na stronach instalatora graf pochodzi z jednego źródła. Z Rank Math 1.0.282 sprawdzono metadane, canonical, hreflang, pojedynczy graf, mapę i `robots.txt` na 22 adresach w odizolowanej instalacji. Dla repozytorium źródłowego Rank Math wygenerowano ponownie mapę autoloadera Composer. Wersja użytkowa powinna pochodzić z dystrybucji WordPressa.
- Przygotowano dwa wzorce stron kampanii i przenoszenie parametrów UTM/GCLID. Testowa strona EN z wzorca miała jeden H1, formularz poza cache i zapisane nadpisania SEO. Po dostarczeniu wiadomości do lokalnego SMTP test ze zdarzeniem odpowiadającym CookieYes potwierdził jeden wpis `fiberlan_lead` po zgodzie, bez danych z formularza. Sprawdzono też brak zdarzenia przy odmowie i po odświeżeniu.
- Oryginalne zdjęcia pozostały w bibliotece. Motyw serwuje responsywne WebP dla trzech oryginalnych fotografii oraz plik WOFF2 o identycznym zestawie glifów. Zmiany zdjęć w Gutenberg nadal zapisują się jako bloki WordPressa.
- Test z działającym cache Nginx sprawdził osobne wejścia PL/EN oraz filtry realizacji. Odpowiedzi formularza miały `no-store` i przy drugim odwiedzeniu nadal omijały cache. Motyw używa w WordPressie sygnału wykluczenia LiteSpeed. Ustawienia LiteSpeed i jednego CDN są w instrukcji wdrożenia.
- Regresja obejmowała 22 adresy w 390 i 1440 px, automatyczne reguły WCAG dla strony głównej, realizacji i formularzy, walidację 22 stron i 11 wzorców w Gutenberg oraz zapis i ponowne otwarcie testowych kopii stron i części szablonu.

| Obszar | Pliki |
| --- | --- |
| SEO, Rank Math, Schema, 404 i 301 | `plugin/fiberlan-site/inc/seo.php`, `seo-copy.php`, `schema.php`, `navigation.php`, szablony `404*.html` |
| Strony kampanii i konwersje | `plugin/fiberlan-site/inc/marketing.php`, `contact.php`, `assets/site.js`, `assets/lead.js` |
| Optymalizacja | `theme/fiberlan/inc/responsive-media.php`, `style.css`, `assets/webp/`, `assets/fonts/BRITANIC.woff2` |
| Testy i instrukcje | `tests/stage_three_smoke.py`, [Pomiary Lighthouse](docs/POMIARY-ETAP-3.md), [SEO, kampanie i cache](docs/SEO-KAMPANIE-WYDAJNOSC.md) |

Przed uruchomieniem produkcyjnym pozostały pomiary PageSpeed Insights na publicznym adresie wdrożenia, weryfikacja LiteSpeed i QUIC.cloud na hostingu, rzeczywisty test CookieYes i Google Consent Mode z identyfikatorami klienta, próba SMTP na właściwej skrzynce oraz zatwierdzenie polityk. Testy Lighthouse dotyczą lokalnego WordPressa. Dane INP i wyniki produkcyjne nie zostały zmierzone.

## Archiwum etapu 2

**19 listopada 2026 · Etap 2 gotowy do odbioru na środowisku testowym.**

## Etap 2

- Zbudowano sześć stron na język (strona główna, O nas, Usługi, Sprzęt, Realizacje i Kontakt), cztery robocze polityki oraz trzy opisy realizacji w dwóch językach. Każdy adres ma własną treść w Gutenbergu, jeden H1 oraz parę PL/EN. Polityki robocze są wyłączone z indeksowania i mapy witryny.
- Wersja PL ma adresy w katalogu głównym. Wersja EN korzysta z odrębnych stron pod `/en/` i oddzielnych części szablonu. Własna wtyczka łączy strony po ID. Przełącznik pamięta ręczny wybór i zachowuje UTM oraz GCLID. Wstępna sugestia uwzględnia język przeglądarki i polską strefę czasową. Bezpośrednie adresy działają bez przekierowania językowego. Dane z geolokalizacji IP nie są zbierane.
- Katalog korzysta ze stron potomnych, kategorii i branż WordPressa. Filtr działa przez odnośniki i parametr adresu. Projekt można dodać w edytorze, a jego tłumaczenie powiązać przez wspólny końcowy slug. Przykłady pokazują diagramy do czasu otrzymania zdjęć z realizacji. Do uzupełnienia pozostają potwierdzone logotypy klientów, zdjęcia projektów i modele urządzeń wymagające potwierdzenia.
- Formularz ma edytowalne etykiety i komunikaty PL/EN. Sprawdza obowiązkowe pola i adres e-mail, nonce, pole przeciw automatom oraz limit prób. Wysyła przez `wp_mail()` na `info@fiberlan.pl` i ustawia zweryfikowany adres użytkownika w nagłówku Reply-To. W testowej konfiguracji SMTP kieruje wszystkie wiadomości do lokalnego Mailpit. Zgłoszenie nie jest zapisywane w WordPressie.
- Moduł „20 lat” i Ribbon jest dostępny jako edytowalna sekcja z natywnym panelem rozwijanym. Zawiera lata 2006–2026 i opis metody. Oświadczenie o wyłączności w Polsce wymaga potwierdzenia.
- Wtyczka obsługuje opcjonalny identyfikator CookieYes. Przy aktywnym API ustawienia zgód można ponownie otworzyć ze stopki. Pełny baner i Consent Mode v2 wymagają konfiguracji i testu z kontem CookieYes. Robocze polityki wymagają zatwierdzenia przez administratora danych.

### Pliki i testy

| Obszar | Pliki i wynik |
| --- | --- |
| Motyw i bloki | `theme/fiberlan`, `plugin/fiberlan-site`. Nowe szablony i style FSE, instalator treści, trzy niestandardowe bloki dla formularza, katalogu i języków. |
| Instalacja | `compose.yaml`, `dev/`. WordPress 7.1.5, PHP 8.3.36, MariaDB 11.4.13 i Mailpit. Instalacja z pustej bazy utworzyła 22 strony. Drugi start zachował identyfikatory i zapisaną zmianę redaktora. |
| Przeglądarka | `tests/stage_two_smoke.py`. 22 adresy × 2 szerokości (390 i 1440 px) przeszły kontrole H1, języka, canonical, hreflang, obrazów, menu i poziomego przepełnienia. Sprawdzono filtrowanie w obu językach, wybór języka z parametrami kampanii i obsługę panelu Ribbon za pomocą Enter/Escape. |
| Dostępność | Wynik automatyczny axe-core 4.10.3 dla strony głównej, realizacji i formularza w obu językach. Zero zgłoszeń dla uruchomionych reguł WCAG A/AA na 390 i 1440 px. |
| Poczta | Oba formularze dostarczyły wiadomości do Mailpit przez SMTP. Odczytano właściwy adres odbiorcy, treść i Reply-To. Sprawdzono błędny nonce, pole przeciw automatom, walidację i HTTP 429. |
| Edytor | `tests/editor_smoke.py --stage-two`. Wszystkie 22 strony i dziewięć wzorców przeszły parsowanie. Zapisano i ponownie otwarto kopie sześciu stron PL, dwóch stron EN oraz nagłówków i stopek obu języków. Zmieniono między innymi wszystkie etykiety i komunikaty formularzy w PL i EN. |
| Mapa witryny | Na odizolowanej kopii z włączonym indeksowaniem WordPress uwzględnił oba języki i wykluczył niezatwierdzone polityki. Instancja deweloperska ma wyłączone indeksowanie. |
| CookieYes | Sprawdzono wywołanie funkcji ponownego otwarcia panelu z testowym callbackiem. Produkcyjny skrypt, zgody i blokowanie tagów oczekują na dane klienta. |

Aby powtórzyć testy, uruchom `./dev/start.sh` i wykonaj poniższe polecenia z zainstalowanymi Python Playwright i Chromium. Flaga `--mail` wysyła dwie wiadomości do lokalnej skrzynki. Limit formularza obejmuje pięć prób w ciągu dziesięciu minut z jednego adresu.

```sh
python tests/stage_two_smoke.py --mail
python tests/editor_smoke.py http://127.0.0.1:8080 .local/admin-password --stage-two
```

Instrukcje edycji i uruchomienia na hostingu znajdują się w [EDYCJA.md](docs/EDYCJA.md) oraz [HOSTING.md](docs/HOSTING.md). Etap 3 obejmuje końcowe metadane i dane strukturalne, Rank Math, analitykę, konfigurację cache i pomiary wydajności. Użycie lekkiej wtyczki projektowej zamiast Polylang umożliwiło niezależną edycję treści bez dodatkowej zależności. Integrację z Rank Math należy zweryfikować na etapie 3.

## Archiwum etapu 1

**19 listopada 2026 · Etap 1 gotowy do odbioru wizualnego.** W bieżącej instancji działa polska strona główna z rzeczywistym zapleczem WordPress. Gałąź projektu to `feature/fiberlan-gutenberg-premium`.

## Zakres i architektura

| Pliki | Rola |
| --- | --- |
| `theme/fiberlan/theme.json`, `style.css` | Paleta, typografia, skala odstępów, wygląd witryny i edytora, układy responsywne oraz obsługa ograniczenia ruchu |
| `templates/`, `parts/` w motywie | Szablony blokowe, główny obszar treści, nagłówek i stopka FSE |
| `patterns/` w motywie | Pięć edytowalnych sekcji strony oraz dwa wzorce części szablonu |
| `functions.php`, `inc/` w motywie | Rejestracja stylów i kategorii, responsywne zdjęcia, opcjonalny instalator strony, media i synchronizowane dane kontaktowe |
| `assets/` w motywie | Logo i fotografie klienta, osadzony Britannic Bold, ilustracje techniczne. Źródła opisuje MATERIALY.md |
| `compose.yaml`, `dev/` | Powtarzalne środowisko WordPress, PHP, Apache i MariaDB oraz proponowane ustawienia startu chmury |
| `tests/` | Testy Chromium dla widoku publicznego i edycji w Gutenbergu |
| `README.md`, `docs/EDYCJA.md` | Instrukcja instalacji i instrukcja dla redaktora |

Treść pięciu sekcji jest zapisywana jako bloki w zwykłej stronie w bazie. Przyciski wyceny i zapytań otwierają program pocztowy. Menu zawiera sześć wymaganych nazw. Pięć pozycji prowadzi przejściowo do sekcji podglądu. W etapie 2 otrzymają osobne strony i odnośniki. W głównej sekcji znajduje się zdjęcie spawarki z obcinarką. Oryginalne logo zachowuje proporcje i kolory. Dla trzech przykładów realizacji zastosowano ilustracje branżowe.

## Wyniki odbioru technicznego

Sprawdzono WordPress 7.1.5, PHP 8.3.36 i MariaDB 11.4.13. Środowisko deweloperskie nasłuchuje na interfejsie lokalnym i ma wyłączone indeksowanie.

| Kontrola | Wynik |
| --- | --- |
| Instalacja na nowej bazie | Utworzono stronę, synchronizowany wzorzec i cztery załączniki. Wszystkie cztery oryginały w Media Library mają identyczne sumy SHA-256 jak źródła. Sprawdzono możliwość zapisu do katalogu przesyłanych plików. |
| Powtórzenie instalacji | Zachowano ID strony, treść, wzorzec i ID mediów. Osobna próba z istniejącą własną stroną główną potwierdziła zachowanie jej ustawień. |
| Gutenberg | Siedem wzorców przeszło parsowanie. Na kopii strony zmieniono 21 akapitów, 11 nagłówków, trzy przyciski i 12 obrazów. Po zapisie i ponownym otwarciu wszystkie 83 bloki strony pozostały prawidłowe. |
| Edytor witryny | Zapisano i ponownie otwarto testowe kopie nagłówka, stopki i kontaktów. Zmieniono etykiety sześciu pozycji menu. Sprawdzono wymianę logo oraz propagację zmiany adresu e-mail do obu części szablonu. |
| Chromium | Szerokości 1920, 1440, 1280, 1024, 768, 390 i 360 px. Każda miała jeden H1, 13 prawidłowo wczytanych obrazów oraz 0 px poziomego przepełnienia. Sprawdzono cele kotwic i obsługę menu klawiaturą. |
| Język, font, zdjęcia | Dokument deklaruje pl-PL. Przyciski menu i link pomijający nawigację mają polskie etykiety. Przeglądarka wczytała Britannic Bold na stronie i w edytorze. Obraz główny ma srcset, sizes i wysoki priorytet. Fotografie kart korzystają z lazy loading. |
| Dostępność | axe-core 4.10.3 przy 390 i 1440 px nie zgłosił naruszeń w automatycznych regułach WCAG A/AA (2.0, 2.1, 2.2). Pełny audyt witryny przewidziano po wdrożeniu kolejnych etapów. |
| PHP | Wszystkie pliki PHP motywu przeszły kontrolę składni w kontenerze WordPressa. |

Powtórzenie testów w działającym środowisku wymaga Pythona z Playwright oraz Chromium. Uruchom w głównym katalogu projektu:

```sh
python tests/home_smoke.py http://127.0.0.1:8080
python tests/editor_smoke.py http://127.0.0.1:8080 .local/admin-password
```

## Do decyzji i dalszego wdrożenia

1. Do akceptacji pozostają wygląd strony głównej, kadry zdjęć, robocze teksty i dane kontaktowe. Dostarczona odmiana Britannic Bold nie ma pełnego polskiego zestawu znaków. Przeglądarka dobiera brakujące litery z kroju zastępczego.
2. Po akceptacji fundamentu etap 2 obejmie pozostałe podstrony, język angielski, formularz, realizacje, moduł Ribbon i dokumenty prywatności. Dane wymagające potwierdzenia przed publikacją obejmują nazwy partnerów oraz deklarację wyłączności technologii.
3. Produkcyjny hosting wymaga osobnej weryfikacji wersji PHP i WordPressa, polskiego pakietu językowego, dostarczania poczty przez SMTP oraz konfiguracji cache. W środowisku lokalnym pobranie pełnego pakietu tłumaczeń rdzenia zwróciło HTTP 403. Część panelu administracyjnego wyświetla się po angielsku.
4. Etapy 3 i 4 obejmą docelowe SEO, CookieYes, konfigurację analityki na podstawie identyfikatorów klienta, pomiary Lighthouse i PageSpeed oraz audyt całej witryny.
5. Działającą konfigurację odtwarzają skrypty w repozytorium. Platforma odrzuciła zapis pól install_script i start_skill w bieżącym szkicu chmury. Ich proponowaną treść zawiera dev/cloud-setup.json. Po przeniesieniu projektu do edytowalnej konfiguracji można zapisać te pola i sprawdzić odtworzenie w nowym zadaniu.
