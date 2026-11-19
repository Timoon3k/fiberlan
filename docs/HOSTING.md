# Uruchomienie FIBERLAN na hostingu

## Instalacja na kopii testowej

Rozpakuj pakiet wydania FIBERLAN 4.4.0. W WordPress wybierz **Wygląd → Motywy → Dodaj motyw** i wgraj osobno `fiberlan.zip` oraz `fiberlan-child.zip`. Przez **Wtyczki → Dodaj wtyczkę** wgraj `fiberlan-site.zip`. Aktywuj motyw potomny i wtyczkę. W **Ustawienia → Bezpośrednie odnośniki** wybierz strukturę **Nazwa wpisu**. Następnie otwórz **Wygląd → FIBERLAN PL/EN** i wybierz **Przygotuj strony PL i EN**. Warto zawczasu wykonać kopię bazy. Instalator zachowuje opublikowane treści i zgłasza kolizje adresów.

## Aktualizacja istniejącej witryny

Zrób kopię bazy danych oraz `wp-content/uploads`. Zaktualizuj trzy pakiety przez mechanizm zastąpienia aktualnej wersji w WordPressie. Katalogi `fiberlan`, `fiberlan-child` i `fiberlan-site` identyfikują aktualizowane komponenty. Instalator stron jest potrzebny tylko przy tworzeniu witryny. Po aktualizacji wyczyść cache LiteSpeed i CDN, a następnie sprawdź obie wersje językowe.

Na świeżej instalacji osiem stron otrzymuje treść klienta od razu. Przy aktualizacji z wersji 4.3 treść stron zapisana w bazie ma pierwszeństwo. Wstaw odpowiedni wzorzec **FIBERLAN · nazwa strony (PL/EN)** w szkicu i przenieś wybrane bloki do istniejącej strony. Kolejność sekcji strony głównej, odnośniki kart i wybór pełnego kadru opisuje [instrukcja redaktora](EDYCJA.md).

Domyślne stopki automatycznie korzystają z nowego układu. Jeżeli stopkę zapisano wcześniej jako własną część szablonu, WordPress użyje tej wersji z bazy. Otwórz ją w Edytorze witryny. Skopiuj grupę **Regulacje prawne** z odpowiedniego wzorca **Stopka FIBERLAN (PL)** lub **FIBERLAN footer (EN)**. Wklej ją obok grupy **Kontakt**, przenieś własne adresy polityk i etykietę CookieYes, a następnie usuń stary pasek z powielonymi linkami. Zapisz i sprawdź osobno PL i EN.

Na świeżej instalacji możesz od razu wybrać **FIBERLAN Child** po zainstalowaniu motywu bazowego. Aktualizacje rodzica zachowują dodatkowe style dziecka. Przed przełączeniem istniejącej instalacji porównaj zapisane szablony, nawigację i style globalne na kopii. WordPress przechowuje część tych ustawień osobno dla każdego motywu. Na serwer trafiają paczki motywów i wtyczki. Narzędzia deweloperskie, testy, pliki z hasłami i lokalny transport Mailpit należą do środowiska testowego.

## Pocztę skonfiguruj przez SMTP

1. W panelu Hostinger otwórz konfigurację używanej skrzynki. Dla usługi Hostinger Email typowe ustawienia to serwer `smtp.hostinger.com`, port 465 i szyfrowanie SSL/TLS. Jeśli panel danej skrzynki podaje inny serwer albo port 587 i STARTTLS, użyj ustawień z panelu.
2. We wtyczce SMTP skonfiguruj adres nadawcy z domeny `fiberlan.pl`, uwierzytelnienie adresem tej skrzynki i wprowadź jej hasło w ustawieniach serwera. Włącz wymuszenie adresu nadawcy, jeżeli wybrana wtyczka udostępnia takie ustawienie. Sprawdź SPF, DKIM i DMARC w panelu domeny.
3. Z formularza na testowej kopii wyślij wiadomość kontrolną. Sprawdź faktyczny odbiór przez `info@fiberlan.pl`, także w folderze spamu. Użyj funkcji odpowiedzi w poczcie i sprawdź, że prowadzi do adresu osoby wypełniającej formularz.
4. W cache witryny i CDN wyklucz `/kontakt/` oraz `/en/contact/`. Formularze wymagają aktualnego nonce, a odpowiedź na wysłanie jest indywidualna. Projekt ustawia nagłówki wyłączające cache na obu stronach.

Poczta na środowisku Docker trafia do lokalnego Mailpit na porcie 8025. Plik `dev/mail.php` jest przeznaczony dla środowiska testowego. Paczka wtyczki produkcyjnej zawiera wyłącznie katalog `plugin/fiberlan-site`.

## CookieYes i dokumenty

W **Ustawienia → Czytanie → CookieYes – Site ID** można wpisać identyfikator ze skryptu klienta CookieYes. Alternatywnie można skorzystać z oficjalnej wtyczki CookieYes i pozostawić to pole puste. Po wczytaniu panelu zgód stopka pokazuje przycisk do ponownego otwarcia ustawień.

W panelu CookieYes wybierz PL i EN, ustaw język zgodny z bieżącą wersją strony oraz dodaj przyciski akceptacji, odrzucenia i konfiguracji z równym poziomem dostępu. Przeskanuj wersję testową. Ustaw blokowanie usług opcjonalnych przed zgodą i sprawdź zmianę preferencji. Przy podłączaniu GTM lub analityki skonfiguruj Consent Mode v2 w CookieYes i sprawdź żądania przeglądarki po obu decyzjach.

Polityka prywatności i polityka cookies w PL i EN zawierają treści do zatwierdzenia. Przed publikacją uzupełnij nazwy usług, okresy przechowywania oraz faktyczne kategorie cookies. Po zatwierdzeniu usuń informację o wersji roboczej z czterech stron. W **Ustawienia → Czytanie → Dokumenty prywatności** zezwól na ich indeksowanie. Pozostałe strony obu wersji znajdą się w mapie witryny WordPressa po włączeniu indeksowania witryny.

[Konfiguracja SEO, Google Ads i cache](SEO-KAMPANIE-WYDAJNOSC.md)
