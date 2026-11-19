# Odbiór etapu 4 · FIBERLAN SC

19 listopada 2026. Zakres obejmuje punkty 4A i 4B przekazanego briefu oraz wspólne elementy witryny. Punktem odniesienia była zaakceptowana makieta pokazana w rozmowie. Porównanie objęło pełne zrzuty sześciu stron w obu językach, przed zmianami i po nich, przy szerokości 390 i 1440 px.

## Zmiany w układzie

| Strona | Wynik oględzin i wprowadzona zmiana |
| --- | --- |
| Główna | Utrzymano granatowe otwarcie z dwoma przyciskami i fotografią po prawej, pasek doświadczenia, cztery usługi i ciemną sekcję realizacji. Skrócono nagłówek nawigacji. W kartach kadr 4∶3 pokazuje całe obudowy obu mierników Fluke. |
| O nas | Tytuł i historię wyrównano do osi logo. Na szerokim ekranie historia zajmuje dwie kolumny, a branże tworzą listę z liniami podziału. Moduł Ribbon zachował edytowalne lata i rozwijane objaśnienie. |
| Usługi | Wprowadzenie ma osobną kolumnę na opis. Zdjęcia i schematy towarzyszą kolejnym usługom. Spis na końcu ma większe pola odnośników. Skok do kotwicy uwzględnia wysokość przypiętego menu. |
| Sprzęt | Pierwsza pozycja zajmuje całą szerokość. Następne urządzenia można porównać parami, na telefonie w jednej kolumnie. Po zmianie kolejności wyróżnienie przechodzi na pierwszą pozycję. |
| Realizacje | Ilustracje, nazwy i lokalizacje mają wspólną oś. Filtry zachowują własne adresy. W edytorze katalog pokazuje rzeczywiste tytuły opublikowanych stron potomnych. |
| Kontakt | Formularz ma wyróżnioną górną krawędź, widoczny fokus i oddzielny blok danych kontaktowych. Etykiety oraz komunikaty formularza można zmieniać w osobnych panelach. |

Nawigacja pozostaje przy górnej krawędzi po przewinięciu paska kontaktowego. Przy bardzo niskim oknie cały nagłówek przewija się wraz ze stroną. Sugestia języka mieści się w pasku kontaktowym. Obie stopki mają oryginalne logo, synchronizowane dane firmy i odnośnik powrotu na górę dostępny z klawiatury. Sekcje poniżej pierwszego ekranu wykonują jednorazowe przesunięcie o 0,4 rem. Preferencja ograniczenia ruchu wyłącza efekt.

## Typografia i fotografie

Britannic Bold pozostał w głównych nagłówkach i wyróżniku 20 lat. Jego plik WOFF2 zajmuje 15 632 bajty. Dostarczony krój zawiera Ó/ó i podstawowy alfabet angielski. Brakujące polskie litery uzupełnia Manrope. Uprawnienia do osadzenia Britannic w witrynie i zapisania go w repozytorium potwierdził klient.

Do opisów, nawigacji, formularzy i tytułów kart wybrano Manrope. W próbce 16 px o wadze 600 tekst „Pomiary i certyfikacja” zajął w nim 163 px, a w Inter 168 px. Dla „Fibre optic splicing” wyniki wyniosły odpowiednio 143 i 147 px. Lokalny plik Manrope ma 53 600 bajtów, obejmuje polski alfabet i wagi 200–800. Fonty są wspólne dla witryny i edytora.

Sprawdzono sumy SHA-256 czterech JPEG względem archiwum klienta. Wszystkie są zgodne. Zastosowano kadrowanie w blokach i istniejące warianty WebP. Zachowano tło fotografii, ponieważ obejmuje pasy zestawu Fluke, torbę VIAVI, akcesoria i ich naturalne cienie. Blok **Obraz** udostępnia pełny kadr i wariant 16∶9, a blok **Galeria** ma styl z podpisami pod zdjęciami. W katalogu projektów pozostają schematy do czasu przekazania fotografii z realizacji.

## Weryfikacja

| Obszar | Sprawdzenie |
| --- | --- |
| Układ i treści robocze | 12 adresów przy 320, 390, 768, 1024 i 1440 px. Podstawiono długie nagłówki i akapity w przeglądarce. Test potwierdził brak poziomego przewijania i nachodzenia opisu na H1. Stronę główną sprawdzono również przy 360, 1280 i 1920 px. |
| Dostępność | Na 12 głównych adresach w 390 i 1440 px axe nie wykrył naruszeń w użytym zestawie reguł WCAG A/AA. Oddzielnie sprawdzono mobilne menu, fokus po powrocie na górę, kotwice, ograniczenie ruchu i powrót przy wyłączonym JavaScripcie. |
| Gutenberg | Walidacja 22 stron i 11 wzorców. Na kopiach roboczych sprawdzono zapis i ponowne otwarcie tekstów, zdjęć, podpisów, przycisków, formularzy, nagłówków i stopek. W edytorze potwierdzono trzy rzeczywiste tytuły realizacji i załadowanie obu fontów z motywem bazowym oraz potomnym. |
| WordPress | Na oddzielnej świeżej instalacji z aktywnym motywem potomnym dwukrotne uruchomienie instalatora dało 22 opublikowane strony i cztery materiały w bibliotece. |
| SEO | Sprawdzono 22 zestawy metadanych i grafów Schema, języki, strony 404 oraz parametry kampanii. Na odizolowanej kopii z Rank Math i włączonym indeksowaniem sprawdzono też mapę witryny. Oba logo na stronie błędu EN prowadzą do angielskiej strony głównej. Po zmianie adresu istniejącej usługi polskie menu odczytuje jej aktualny adres. |
| Poczta i zgody | Obie wersje formularza dostarczyły wiadomość do lokalnego Mailpit. Test obejmował walidację pól, token WordPressa, pole antyspamowe i brak powtórnego potwierdzenia po odświeżeniu. Symulowane zdarzenie zgody CookieYes uruchomiło pojedyncze zdarzenie konwersji po udanej wysyłce. |

## Lighthouse na lokalnym WordPressie

Lighthouse 13.6.0 i Chromium 156. Każdy adres sprawdzono raz na każdym profilu z pustą pamięcią podręczną. LCP oznacza czas wyświetlenia największego elementu. CLS opisuje nieoczekiwane przesunięcia układu.

| Adres | Profil | Wydajność / 100 | LCP | CLS | Transfer |
| --- | --- | ---: | ---: | ---: | ---: |
| `/` | Telefon | 98 | 2,33 s | 0,000 | 310 KiB |
| `/` | Komputer | 100 | 0,50 s | 0,000 | 184 KiB |
| `/uslugi/` | Telefon | 100 | 0,96 s | 0,001 | 221 KiB |
| `/uslugi/` | Komputer | 100 | 0,40 s | 0,000 | 176 KiB |
| `/en/contact/` | Telefon | 100 | 0,76 s | 0,000 | 119 KiB |
| `/en/contact/` | Komputer | 100 | 0,21 s | 0,000 | 119 KiB |

We wszystkich sześciu przebiegach dostępność i dobre praktyki uzyskały 100/100. SEO w instalacji deweloperskiej wyniosło 69/100 przy celowo wyłączonym indeksowaniu. Na mobilnej stronie głównej LCP wyniósł 2,33 s wobec 1,89 s przy odbiorze etapu 3. Obecna wersja pobiera także lokalny krój Manrope.

## Przed publikacją na fiberlan.pl

Klient dostarcza ostateczne teksty PL/EN, potwierdza dane kontaktowe i zatwierdza dokumenty prywatności. Na docelowym hostingu pozostają do sprawdzenia doręczenie do info@fiberlan.pl, konfiguracja CookieYes i tagów kampanii z identyfikatorami klienta oraz działanie cache i CDN. Pomiary produkcyjne wymagają publicznego wdrożenia.

[Instrukcja edycji](EDYCJA.md) · [Konfiguracja hostingu](HOSTING.md) · [SEO i kampanie](SEO-KAMPANIE-WYDAJNOSC.md)
