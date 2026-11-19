# FIBERLAN · SEO, kampanie i wydajność

## Metadane, indeksowanie i Rank Math

1. Na kopii testowej zaktualizuj motyw i wtyczkę FIBERLAN do wersji 3.0.0. Po aktualizacji domyślne szablony zawierają ścieżkę nawigacji na podstronach i spis odnośników do sekcji na stronach usług. W zapisanej wcześniej kopii szablonu można wstawić bloki **Ścieżka nawigacji** i **Spis usług** w Edytorze witryny.
2. Zainstaluj Rank Math z dystrybucji WordPressa, uruchom kreator i włącz moduł mapy witryny. Konto Rank Math jest opcjonalne dla metadanych i mapy. Po zakończeniu instalacji zapisz ponownie ustawienia bezpośrednich odnośników. W Rank Math wybierz jeden adres docelowy HTTPS i sprawdź adresy wygenerowane w kodzie strony.
3. Domyślne tytuły i opisy są przypisane do 22 adresów PL/EN. Redaktor może je nadpisać w edytorze każdej strony. Gdy działa Rank Math, użyj jego edytora podglądu wyniku wyszukiwania. Przy samodzielnej pracy wtyczki FIBERLAN użyj panelu **FIBERLAN – SEO** pod treścią. Oba edytory korzystają z tych samych pól w bazie. Dodając nową stronę, ustaw własny opis i obrazek wyróżniający do podglądu w mediach społecznościowych.
4. Na stronach instalatora FIBERLAN generuje jeden graf Schema.org. Zawiera profil firmy, a tam, gdzie ma to zastosowanie, także listę usług i ścieżkę nawigacji. Adres, telefon i e-mail pochodzą z synchronizowanego wzorca danych kontaktowych. Rank Math zapewnia metadane, karty społecznościowe i mapę. Schema Rank Math można używać na innych stronach.
5. Na stagingu zostaw zaznaczoną opcję **Proś wyszukiwarki o nieindeksowanie tej witryny**. Gdy witryna ma zostać opublikowana, odznacz ją i sprawdź `/robots.txt` oraz `/sitemap_index.xml` w trybie Rank Math lub `/wp-sitemap.xml` przy standardowej mapie WordPressa. Oczekiwane adresy to strona polska i jej angielski odpowiednik, każdy z właściwym canonical i wzajemnymi `hreflang`. Polityki robocze pozostają poza mapą i mają `noindex` do zatwierdzenia w ustawieniach czytania.
6. Gdy zmienisz uproszczony adres opublikowanej strony, wtyczka zapamięta starą ścieżkę dla niej i opublikowanych stron potomnych. Dawna ścieżka zwróci 301 do bieżącego adresu, o ile sama nie służy już innej stronie. Błędne adresy zwracają 404, z osobnym widokiem dla ścieżek pod `/en/`.

## Landing pages i zdarzenie konwersji

W WordPressie dodaj stronę, wybierz szablon **FIBERLAN PL** lub **FIBERLAN EN** i wstaw wzorzec z kategorii **FIBERLAN**. Warianty **Strona kampanii – spawanie światłowodów (PL)** i **Campaign page – fibre optic splicing (EN)** mają jeden H1, zaproszenie do wyceny i formularz. Stronę EN umieść pod rodzicem **Home** w katalogu `/en/`. Ustal ofertę, opis i przeznaczenie strony w kampanii przed ustawieniem indeksowania. Na stronie sezonowej albo o treści powielającej opis usługi można ustawić `noindex` w Rank Math.

Odnośniki wewnętrzne zachowują parametry UTM i GCLID w trakcie zwykłej nawigacji. Formularz zachowuje je również w odpowiedzi po wysłaniu. Po skutecznym przyjęciu wiadomości przez SMTP strona usuwa z adresu jednorazowy identyfikator potwierdzenia. Po uzyskaniu zgody analitycznej albo reklamowej integracja może dodać do `dataLayer` jedno zdarzenie `fiberlan_lead`. Zawiera ono `form_id`, `form_language`, `analytics_consent` i `advertising_consent`. Dane podane w formularzu pozostają w poczcie.

### CookieYes, GTM, GA4 i Google Ads

- Wybierz jedną integrację CookieYes. W panelu CookieYes skonfiguruj PL i EN, kategorie zgód, przyciski akceptacji, odrzucenia i konfiguracji oraz Consent Mode v2 w trybie podstawowym. Ustaw domyślnie `denied` dla `analytics_storage`, `ad_storage`, `ad_user_data` i `ad_personalization`. Przy zmianie preferencji CookieYes powinien aktualizować stan zgód. Sprawdź przycisk ponownego otwarcia ustawień w stopce.
- Po otrzymaniu identyfikatorów skonfiguruj jeden kontener GTM. Dla tagu GA4 ustaw zdarzenie niestandardowe `fiberlan_lead`, parametr `analytics_consent = true` i dodatkową kontrolę `analytics_storage`. W GA4 odwzoruj zdarzenie na `generate_lead` i oznacz je jako kluczowe, jeżeli jest celem kampanii.
- Dla tagu konwersji Ads użyj tego samego zdarzenia, warunku `advertising_consent = true` i wymaganych kontroli zgody reklamowej `ad_storage` oraz `ad_user_data`. Remarketing wymaga odrębnej zgody `ad_personalization`. Gdy raportujesz konwersje z bezpośredniego tagu Ads, unikaj równoczesnego liczenia importu tego samego zdarzenia z GA4 jako drugiej konwersji głównej.
- W Tag Assistant sprawdź brak żądań tagów analitycznych i reklamowych przed zgodą, działanie pomiaru po odpowiedniej zgodzie, odrzucenie zgody i cofnięcie preferencji. Wyślij po jednej wiadomości z formularzy PL i EN w środowisku testowym. Sprawdź również błąd walidacji i odświeżenie potwierdzenia. Aktualna paczka działa bez identyfikatorów kont i bez tagów pomiarowych.

## LiteSpeed Cache i QUIC.cloud

| Ustawienie | Wartość początkowa |
| --- | --- |
| Cache stron publicznych | Włącz na serwerze LiteSpeed. PL i EN muszą mieć osobne klucze według adresu. Wersja językowa i canonical są stałe dla danej ścieżki. |
| Cache Mobile, cache zalogowanych i cache żądań POST | Wyłącz. Układ dostosowuje się w CSS. Formularz i jego potwierdzenia wymagają odpowiedzi z serwera. |
| Wykluczenia stron | Dodaj `/kontakt/`, `/en/contact/` i każdą dodatkową stronę z formularzem do wykluczeń cache serwera i CDN. Kod wysyła nagłówki `no-store` oraz sygnał LiteSpeed dla stron z blokiem formularza. |
| Parametry cache | Zachowaj rozróżnienie dla `sector` w katalogu realizacji. UTM i GCLID można pominąć w kluczu cache, o ile adres widoczny w przeglądarce zachowuje parametry i przechodzi test kampanii. |
| Obrazy i minifikacja | Motyw używa już natywnego lazy loading, wariantów WebP i priorytetu zdjęcia głównego. Zacznij bez dodatkowego przepisywania obrazów, łączenia CSS i opóźniania skryptów. Zmieniaj jedno ustawienie naraz i testuj integrację zgód, menu oraz formularz. |
| CDN | Wybierz QUIC.cloud albo jeden używany CDN. Po podłączeniu sprawdź HTTPS, nagłówki HIT/MISS, adresy logotypu, zdjęć i fontu w PL oraz EN. |

Po konfiguracji wyczyść cache. W dwóch oknach prywatnych otwórz osobno PL i EN. Zmieniaj preferencję języka, odwiedzaj stronę z bezpośredniego adresu i używaj filtrów realizacji. W narzędziach sieciowych sprawdź `X-LiteSpeed-Cache` i nagłówki CDN. Na stronach z formularzem szukaj `Cache-Control: no-store`. Sprawdź wydajność w PageSpeed Insights po udostępnieniu adresu z tą wersją witryny i po włączeniu faktycznych integracji.
