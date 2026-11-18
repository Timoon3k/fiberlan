# FIBERLAN SC · Status projektu

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
