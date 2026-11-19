# Odbiór FIBERLAN 4.4.0

20 listopada 2026 r. Testy wykonano w lokalnym WordPressie 7.1.5 z PHP 8.3.36, motywem potomnym FIBERLAN Child i wtyczką FIBERLAN Core.

## Treści i materiały

| Strona PL / EN | Treść z dokumentu www1.docx |
| --- | --- |
| O nas / About | Praca zespołu od 2006 roku, nadzór założycieli Andrzeja i Norberta, dyżury w Polsce, kontrakty europejskie i dokumentacja odbiorowa. |
| Usługi / Services | Spawanie SM/MM, FTTx, szafy RACK, pomiary OTDR, okablowanie miedziane, Ribbon i MPO/MTP. Sekcja serwisu 24/7 prowadzi do danych kontaktowych zespołu. |
| Sprzęt / Equipment | Cztery spawarki Fujikura FSM-62S i FSM-70S łącznie, dwa analizatory Fluke, trzy reflektometry EXFO i dwa przyrządy VIAVI. Zachowano rozróżnienie między pomiarami Tier 1 i Tier 2. |
| Realizacje / Projects | Cztery zakresy prac opisane przez klienta, w tym tory 100G/400G. Katalog zachowuje odrębne opisy Frankfurtu, Bordeaux i stoczni. |

Angielskie wersje są tłumaczeniami redakcyjnymi treści klienta. Nazwy operatorów jako referencje pozostają do potwierdzenia zgodnie z wcześniejszym briefem. W opisie Ribbon wykorzystano dostarczone fakty techniczne. Dokument określał wyłączność rynkową jako przypuszczenie.

Główna fotografia wykorzystuje maskę dopasowaną do konturów sprzętu. W zdjęciu zachowano ekran, napisy i akcesoria. Pełny kadr jest dostępny w katalogu i jako styl bloku. Karty realizacji korzystają z dotychczasowych rysunków branżowych. Dokument zawierał tekst, a fotografie w archiwum klienta przedstawiają sprzęt.

## Wyniki kontroli

| Obszar | Zakres i wynik |
| --- | --- |
| Widok publiczny | 22 adresy w PL i EN w Chromium przy 390 i 1440 px. Obrazy zostały zdekodowane, każdy adres miał jeden H1 i właściwe powiązanie językowe. Sprawdzono 90 różnych lokalnych celów odnośników, filtrów i kotwic. |
| Nawigacja i ruch | Sześć głównych stron w każdym języku przy 320, 390, 768, 1024 i 1440 px. Sprawdzono przyklejone menu, pasek administracyjny, strzałkę, obsługę klawiaturą i dotykiem, pozycję po zamknięciu menu i tryb ograniczonego ruchu. Animacje kart i zdjęć mieszczą się w przedziale 700–1000 ms. |
| Gutenberg | Walidacja bloków i zapis na kopiach 22 stron, odczyt po ponownym otwarciu, wymiana obrazu i ALT. Sprawdzono osiem wzorców z treścią klienta, obie nawigacje, stopki z regulacjami, dane kontaktowe, karty usług, kolejność sekcji i dodanie realizacji w każdym języku. |
| Formularze i zgoda | Wiadomości PL i EN odczytano w Mailpit. Odbiorcą był `info@fiberlan.pl`, a Reply-To wskazywało adres wpisany przez nadawcę. Parametry kampanii przetrwały przejście na formularz. Po testowej zgodzie każde potwierdzenie mogło uruchomić jedno zdarzenie konwersji. |
| SEO | 22 unikalne tytuły i opisy, nagłówki, canonical, hreflang, grafy Schema.org i odpowiedzi 404. Na kopiach z indeksowaniem sprawdzono mapy WordPressa i Rank Math. Obejmowały 18 adresów treści. Cztery projekty dokumentów prawnych miały noindex i pozostały poza mapami. |
| Dostępność | 44 skany axe-core dla reguł WCAG A/AA oznaczonych tagami WCAG 2, 2.1 i 2.2 przy 390 i 1440 px. Wynik wyniósł 0 wykrytych naruszeń. Klawiaturą sprawdzono m.in. menu, formularz, panel Ribbon i powrót na górę. |
| Paczki | Sprawdzono integralność i zawartość trzech ZIP-ów. Na świeżej bazie z wypakowanych plików uruchomiono motyw potomny i wtyczkę, otrzymując 22 strony oraz cztery media. Drugi start zachował strukturę. Porównano bajty plików klienta i obecność licencji Manrope. |

## Wydajność

Lighthouse 13.6 w Chromium, lokalny WordPress, domyślne profile mobilny i desktopowy. Wyniki wydajności są w skali 0–100.

| Widok | Wydajność telefon / komputer | LCP telefon / komputer | TBT telefon / komputer | CLS |
| --- | --- | --- | --- | --- |
| Strona główna PL | 98 / 100 | 2,41 s / 0,53 s | 39 ms / 0 ms | 0 |
| Usługi PL | 100 / 100 | 1,06 s / 0,49 s | 0 ms / 0 ms | 0 |
| Kontakt EN | 100 / 100 | 0,91 s / 0,26 s | 0 ms / 0 ms | 0 |

Każdy profil uzyskał wynik 100 w kategorii dostępności Lighthouse. W kategorii SEO lokalna blokada indeksowania dała wynik 69. Zachowanie udostępnionej do indeksowania kopii sprawdzono osobno. Przeglądarka zmierzyła 8–32 ms dla pierwszej interakcji przy otwieraniu mobilnego menu na 22 stronach. Rzeczywiste LCP, INP i CLS wymagają próby ruchu użytkowników na domenie docelowej.

## Przed uruchomieniem na domenie

Właściciel zatwierdza obie wersje dokumentów prawnych i ewentualne użycie nazw klientów. Na hostingu pozostają do weryfikacji dostarczenie poczty do rzeczywistej skrzynki oraz otwarcie panelu CookieYes z konfiguracją klienta i działanie tagów kampanii po zgodzie.

[Instalacja i aktualizacja](HOSTING.md) · [Edycja treści](EDYCJA.md)
