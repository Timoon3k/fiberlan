# Edycja witryny FIBERLAN

## Panel edycji Gutenberga — wszystkie podstrony

Otwórz **Strony → Wszystkie strony → Edytuj** i wybierz dowolną stronę FIBERLAN, np. O nas, Usługi, Sprzęt, Realizacje, Kontakt, polityki lub stronę projektu — w języku PL albo EN. Panel **FIBERLAN · Edycja strony** otwiera się automatycznie. Na dużym ekranie pojawi się po lewej, na węższym w standardowym układzie WordPressa. W panelu rozwijaj poszczególne sekcje i zmieniaj nagłówki, opisy, odnośniki, zdjęcia, ich teksty ALT oraz shortcode opinii. Kliknij **Zapisz**. Treści pozostają zapisane w natywnych blokach, nie w osobnej kopii.

Bloki z formatowaniem (np. link w nagłówku lub pogrubienia) oraz specjalne bloki FIBERLAN (formularz, katalog) edytuj po wybraniu bloku w podglądzie — panel daje przycisk **Edytuj na podglądzie** i nie niszczy formatowania. Współdzielone dane kontaktowe edytuje się w oryginalnym wzorcu, nie osobno na każdej stronie.

W sekcji **SEO strony** możesz zmienić tytuł i opis meta, gdy Rank Math nie zarządza tym widokiem. Te same pola są dostępne przez REST i zapisywane razem ze stroną. Dolny metabox **FIBERLAN – SEO** nie jest już pokazywany w Gutenbergu — nie usuwa to jego dotychczasowych wartości. W edytorze klasycznym metabox pozostaje dostępny.

Opinie z Google na stronie angielskiej zachowują oryginalne wypowiedzi klientów. Skrypt tłumaczy wyłącznie dokładnie rozpoznane etykiety interfejsu Trustindex. Jeśli widget zostanie załadowany wewnątrz ramki iframe lub udostępnia własny język EN, skonfiguruj język angielski w samej wtyczce Trustindex i sprawdź podgląd. W razie dynamicznych zmian widżetu nie edytuj tekstów klientów.

Sekcja Kontakt ma węższy, wyśrodkowany układ danych firmy względem formularza; na ekranach mobilnych przechodzi w jedną kolumnę.

## Strona i jej sekcje

W panelu WordPress wybierz **Strony → Strona główna → Edytuj**. Otwórz widok listy bloków. W widoku listy zaznacz grupę, aby podświetlić całą sekcję na stronie. Kliknij tekst w podglądzie, wprowadź zmianę i wybierz **Zapisz**. Układ oraz kolejność sekcji możesz zmieniać w widoku listy.

- **Wprowadzenie**. Edytuj nagłówek H1, opis i oba przyciski. Po zaznaczeniu przycisku kliknij ikonę linku i podaj adres docelowy. Domyślne przyciski wyceny i kontaktu prowadzą do formularza.
- **Doświadczenie**. Każda pozycja paska to grupa z osobnymi polami tekstowymi. Wyróżnienie dotyczące 20 lat odnosi się do roku 2026.
- **Usługi**. Każda karta to grupa zawierająca obraz, nagłówek z odnośnikiem i opis. W menu grupy wybierz **Powiel** lub **Usuń**. W motywie potomnym możesz też wstawić wzorzec **Karta usługi · Service card** wewnątrz grupy z kartami. Uzupełnij obraz, tytuł i opis. Zaznacz tytuł i użyj ikony odnośnika, aby podać adres usługi.
- **Realizacje**. Zaktualizuj nazwy obszarów i lokalizacje w poszczególnych kartach. Zastępuj ilustracje wyłącznie zdjęciami z danej realizacji.
- **Opinie z Google**. W widoku listy otwórz grupę **Widżet opinii z Google** lub **Google reviews widget**. Wstaw blok z zainstalowanej wtyczki opinii. Jeśli wtyczka udostępnia shortcode, wstaw go do bloku **Krótki kod** w tej grupie. Powtórz dla strony angielskiej. Do czasu podłączenia widżetu sekcja zawiera odnośnik do wyszukania firmy w Mapach Google.

Aby wstawić kolejną sekcję, otwórz wstawiacz bloków i wybierz **Wzorce → FIBERLAN**. Nowa sekcja jest niezależną kopią. Jeżeli powielasz sekcję z kotwicą, nadaj jej unikalną kotwicę w ustawieniach zaawansowanych.

## Treści klienta na istniejącej stronie

Treści z dokumentu klienta znajdują się na stronach **O nas**, **Usługi**, **Sprzęt** i **Realizacje** oraz w ich angielskich odpowiednikach. Nowa instalacja tworzy te strony od razu. Na witrynie aktualizowanej z 4.3 otwórz **Strony → Dodaj stronę** i wstaw do szkicu odpowiedni wzorzec, np. **FIBERLAN · Sprzęt (PL)** albo **FIBERLAN · Equipment (EN)**. W widoku listy skopiuj wybrane grupy ze szkicu do istniejącej strony. Możesz też zastąpić wszystkie bloki treści odpowiednikiem dla danej strony i języka. Przed zapisem sprawdź podgląd i usuń powielone sekcje. Istniejący adres strony i jej powiązanie językowe pozostają dostępne. Każdy nagłówek, opis, obraz i przycisk możesz dalej edytować na stronie.

## Zdjęcia, podpisy i logo

Wybierz blok **Obraz → Zastąp**. Wskaż plik z biblioteki albo prześlij nowy. Sprawdź kadr, podpis i tekst alternatywny. Dekoracyjna ilustracja obok pełnego opisu może mieć pusty tekst alternatywny. Zdjęcie spawarki w pierwszej sekcji ma opis ALT. Pole podpisu pozostaw puste, aby pod zdjęciem nie pojawiał się tekst. WordPress tworzy warianty rozmiarów dla obsługiwanych fotografii w bibliotece mediów.

W **Wygląd → Edytor → Wzorce → Części szablonu → Nagłówek** wybierz blok **Logo witryny** z widoku listy. Instalator wybiera oryginalne logo z pełnym napisem SC. W tym bloku możesz zastąpić je późniejszą wersją. Motyw zachowuje proporcje grafiki. Przed dodaniem pliku nagłówek pokazuje tytuł witryny. Ustaw go na „FIBERLAN SC” w ustawieniach ogólnych WordPressa.

## Nawigacja premium na komputerach i telefonach

Menu zachowuje natywne bloki WordPressa i istniejące pozycje PL/EN. Od wydania 4.5.5 linki na komputerze są typograficzne, bez tła i kształtu przycisków; bieżąca strona ma cienkie podkreślenie. Przełącznik języka PL/EN jest tekstowy, bez kapsułek.

W telefonie otwierasz menu przyciskiem z dwiema liniami w nagłówku. Po otwarciu widać pełnoekranowe, jednolite ciemnogranatowe tło, numerowane pozycje rozdzielone cienkimi liniami i osobny przełącznik języka na dole panelu. Zamknięcie działa natywnym przyciskiem WordPressa i klawiszem Escape. Animacja respektuje ograniczony ruch. Nie usunięto żadnego odnośnika ani bloku nawigacji.

Na istniejącej witrynie, która ma zapisane własne części nagłówka, wystarczy zaktualizować motyw — nowe reguły styli obejmują również wcześniej zapisane menu, o ile zachowano klasy `fl-nav` i `fl-nav-row`. Po aktualizacji motywu wyczyść cache LiteSpeed i sprawdź menu w 320, 390, 768 i 1440 px.

## Dane kontaktowe i nawigacja

W nagłówku, stopce lub na stronie kontaktowej zaznacz dane firmy. W pasku narzędzi wzorca **FIBERLAN – dane kontaktowe** wybierz **Edytuj oryginał**. Kliknij akapit z adresem, telefonem lub e-mailem i zapisz zmianę. Wszystkie wystąpienia tego wzorca w obu językach pobierają te same dane firmy. Przy zmianie telefonu lub poczty popraw zarówno widoczną treść, jak i odnośnik (tel lub mailto). Adresy docelowe przycisków sprzedażowych w treści strony edytuje się osobno. Przed publikacją potwierdź z klientem adres i oba numery.

Menu edytujesz w bloku **Nawigacja** nagłówka. Po aktualizacji etapu 1 w zapisanym wcześniej menu zamień kotwice strony głównej na odnośniki podstron. Przewidziane adresy to /o-nas/, /uslugi/, /sprzet/, /realizacje/ i /kontakt/. Własny układ stopki możesz zapisać po otwarciu części szablonu **Stopka**.

## Typografia i kolory

W **Wygląd → Edytor → Style** możesz zmienić paletę, typografię i globalny wygląd przycisków. Nagłówki korzystają z oryginalnego pliku Britannic Bold dostarczonego przez klienta. Motyw serwuje go z własnej domeny. Dla znaków nieobecnych w pliku przeglądarka wybiera krój zastępczy.

Tekst, menu, przyciski, pola i tytuły kart używają lokalnego kroju Manrope. Ma on pełny polski alfabet. Britannic Bold pozostaje w H1, nagłówkach sekcji i wyróżniku liczbowym. Przy brakujących glifach przeglądarka dobiera znaki z Manrope.

## Kadry i widok listy

Wybierz **Obraz → Style → Pełny kadr**, aby pokazać całe zdjęcie z biblioteki, lub **Kadr panoramiczny**, aby uzyskać proporcje 16∶9. Po zmianie pliku sprawdź etykiety na urządzeniu w podglądzie telefonu. W katalogu sprzętu pierwsza pozycja zajmuje pełną szerokość. Kolejne są zestawiane parami na większych ekranach. Możesz przeciągnąć inną pozycję na początek w widoku listy.

Dla zdjęć z projektu dodaj natywny blok **Galeria** i wybierz styl **Galeria realizacji**. Wpisz podpisy pod poszczególnymi zdjęciami. Liczbę kolumn, kolejność i przycinanie określasz w Gutenbergu.

Widok listy rozpoznaje m.in. grupy **Wprowadzenie**, **Karta usługi**, **Pozycja katalogu sprzętu** oraz **Doświadczenie i Ribbon**. Aby przenieść sekcję wraz z jej treścią, przeciągnij całą grupę w widoku listy lub skorzystaj ze strzałek przesuwania.

W **Wygląd → Edytor → Wzorce → Części szablonu** wybierz **Stopka** lub **Footer (EN)**. Zmieniaj opis firmy i listy odnośników jak zwykłe bloki. W grupie **Regulacje prawne** kliknij nagłówek, odnośnik do polityki lub etykietę ustawień CookieYes. Podpis i link autora znajdziesz pod główną stopką. W bloku **Prawa autorskie FIBERLAN** edytujesz nazwę firmy i treść informacji o prawach. Zakres dat rośnie automatycznie od 2026 roku.

Motyw wyświetla pływającą strzałkę po przewinięciu 600 px. Zapisany wcześniej blok powrotu na górę można usunąć w edytorze stopki. Aktualna strzałka jest dostępna także przy własnym szablonie stopki.

## Kontrola po zapisaniu

Otwórz stronę ponownie w edytorze. Sprawdź obrazy, podpisy, przyciski i podgląd mobilny. Po zmianach globalnych otwórz również stronę jako wylogowany użytkownik. Historię zmian treści strony znajdziesz w panelu wersji. Poniżej opisano edycję obu języków i katalogu realizacji.

Dostarczony plik BRITANIC.ttf nie zawiera m.in. znaków ą, ć, ę, ł, ń, ś, ź i ż. Do jednolitego składu wszystkich polskich nagłówków potrzebna będzie licencjonowana odmiana z pełnym polskim zestawem znaków.

## Podstrony, języki i realizacje

- Każda wersja językowa to osobna strona WordPress. W **Strony** wyszukaj polską lub angielską nazwę, otwórz edytor i zmieniaj bloki. Przełącznik PL/EN pokazuje powiązany adres bieżącej strony. Angielskie strony korzystają z szablonu **FIBERLAN EN** oraz oddzielnego nagłówka i stopki.
- Domyślna nawigacja prowadzi do odrębnych stron. Edytuj etykiety i adresy w blokach **Nawigacja** nagłówków PL i EN. Dane firmy pozostają synchronizowane. W bloku **Przełącznik języka** kliknij etykietę PL, EN lub zdanie z sugestią językową i wpisz nową treść. Analogicznie w stopce możesz kliknąć tekst przycisku ustawień zgód.
- Otwórz stronę **Kontakt** lub **Contact** i zaznacz blok **Formularz zapytania**. Kliknij etykietę pola, treść zgody lub napis na przycisku. Zaznaczenie bloku odsłania adres odnośnika do polityki prywatności. Pod formularzem wybierz stan z listy **Podgląd komunikatu po wysłaniu** i kliknij wyświetlony komunikat, aby go zmienić. Odbiorcą zapytań jest `info@fiberlan.pl`.
- Otwórz stronę **Realizacje** albo **Projects**. Kliknięcie tytułu w katalogu otwiera edytor tej realizacji. Przycisk **Dodaj realizację** otwiera nowy szkic z ustawionym rodzicem i szablonem językowym. Dodaj tytuł, opis i zdjęcie. W ustawieniach strony wpisz lokalizację w polu **Zajawka** i wybierz kategorie. Zdjęcie wyróżniające jest miniaturą w katalogu. Gdy go nie wybierzesz, katalog użyje pierwszego zdjęcia z treści. Fotografie dodaj blokiem **Galeria**. Datę możesz dodać w treści, a branżę przypisać w panelu **Branże**. Katalog pobiera opublikowane strony potomne.
- Aby powiązać nowy projekt w dwóch językach, nadaj obu stronom ten sam uproszczony adres końcowy, np. `sieci-optyczne-2026`, przy dwóch różnych stronach nadrzędnych. Wybieraj kategorie języka projektu. Dla nowej kategorii zastosuj prefiks `pl-` lub `en-` w adresie terminu.
- Sekcja Ribbon jest zbudowana z grup i natywnego bloku **Szczegóły**. Edytuj liczbę, lata, opis i tytuł rozwijanego panelu w widoku listy. Sekcje na stronie głównej i podstronie **O nas** są edytowane osobno.

[Metadane SEO, wzorce stron kampanii i indeksowanie](SEO-KAMPANIE-WYDAJNOSC.md)

## Szablony i strona błędu

W **Wygląd → Edytor → Szablony** wybierz szablon PL lub EN. W szablonie 404 kliknij nagłówek, opis albo przycisk. Po zaznaczeniu przycisku możesz też zmienić adres powrotu. Aby zmienić wspólny układ nowych realizacji, otwórz część **Realizacja · tytuł i treść**. Tytuł, zajawkę i zdjęcia konkretnego projektu wpisuj na jego stronie.

Na stronie głównej układ wydania 4.4 prowadzi od wprowadzenia i paska doświadczenia do usług, realizacji, sekcji Ribbon i kontaktu. Na stronie aktualizowanej możesz odtworzyć tę kolejność przez przeciągnięcie grupy Ribbon przed końcową sekcję kontaktową. W tytułach trzech kart realizacji ustaw odnośniki do odpowiednich opisów w tym samym języku. W pierwszej sekcji styl obrazu **Pełny kadr** pokazuje również tło oryginalnej fotografii.

## Motyw fotograficzny 4.7.1 — edycje PL/EN i treści ze zrzutów

Ta aktualizacja jest przeznaczona **wyłącznie dla gałęzi `preview/szata-fotograficzna`**. Zachowuje jej zdjęcia, menu mobilne, animacje, nagłówek i stylistykę 4.7.x. Nie zastępuj motywu paczką 4.6 z gałęzi main.

W **O nas / About** korygujemy dwukolumnowy układ, dodajemy edytowalne miejsce na zdjęcie zespołu i pokazujemy pełną treść dotychczasowych rozwijanych bloków jako zwykłe nagłówki i akapity. Na stronie Usługi / Services domyślne fotografie zastępcze przechodzą na neutralne obrazy `photo-placeholder.svg`. Później użyj w Gutenbergu **Obraz → Zastąp**, aby dodać zdjęcia klienta. Na podstronie Sprzęt / Equipment wprowadzamy naprzemienne, poziome sekcje sprzętu, nowy placeholder w nagłówku i skrócony lead; oryginalny rozbudowany opis jest zachowany w osobnej sekcji poniżej. W Realizacje / Projects zostają dotychczasowe filtrowane realizacje; dodane są cztery obszary wdrożeń PL/EN z nowym opisem użytkownika oraz galeria czterech edytowalnych zdjęć. Sekcja „Zakresy wdrożeń / Types of installation” zostaje usunięta.

**Istniejące strony:** po aktualizacji motywu **i** FIBERLAN Core do 4.7.1 oraz wejściu do administracji, jednokrotna aktualizacja dopasowuje zapisane w bazie bloki PL/EN. WordPress zachowuje historię rewizji. Jeśli edytor strony jest zablokowany, aktualizacja tej strony nastąpi przy następnej wizycie administratora. Operacja nie nadpisuje szablonów nagłówka, stopki, opinii Google, formularzy ani obrazów w innych podstronach.

**Ważne:** zrób kopię bazy WordPress przed aktualizacją. Przed publikacją sprawdź dopasowanie własnych treści i zdjęć do nowych układów na witrynie staging.


## Galerie realizacji i pola zdjęć w wersji 4.9

W sekcji **Realizacje → Galeria realizacji** można wymienić sześć zdjęć. Każdy wpis opisujący realizację ma również własną galerię z trzema polami. Otwórz stronę lub realizację w odpowiednim języku, zaznacz obraz i wybierz **Zastąp → Biblioteka mediów**. Aby dodać kolejne miejsce, zduplikuj blok obrazu w tej samej grupie. W ustawieniach obrazu wpisz opis ALT.

W **Realizacjach** nazwy operatorów są osobnymi blokami edytowanymi w obu językach. Aktualizacja istniejących stron uruchamia się po pierwszym wejściu administratora do kokpitu z aktywnym motywem bazowym 4.9 lub jego motywem potomnym oraz wtyczką FIBERLAN Core 4.9. WordPress pomija stronę otwartą przez redaktora i ponawia aktualizację po zwolnieniu blokady.


W wydaniu 4.9.1 wtyczka uzupełnia treści angielskie pasujące do starszych sekcji z kopii klienta i koryguje cele odnośników. Na stronie z tej kopii aktywny jest motyw **FIBERLAN**. Przy aktualizacji wystarczy zaktualizować aktywny motyw i wtyczkę **FIBERLAN Core**, a następnie otworzyć kokpit jako administrator. Konfiguracja recenzji pochodzi z zainstalowanej wtyczki **Widgets for Google Reviews**.

## 4.11.0 — własne fotografie, media PL/EN i zarządzanie

Zaktualizuj motyw i wtyczkę z gałęzi preview/szata-fotograficzna. W panelu **FIBERLAN → Import fotografii FIBERLAN** wgraj dostarczoną osobno paczkę fiberlan-media-4.11.zip. Import jest jawny, wymaga administratora, deduplikuje pliki po SHA-256, zapisuje ALT PL i EN, tworzy załączniki WordPressa i opcjonalnie uzupełnia pasujące placeholdery na pięciu podstronach w obu językach. Własne obrazy nie są zastępowane. Jeśli strona jest zablokowana, zamknij edytor i użyj przycisku ponownego dopasowania. W raporcie widać liczbę wstawionych zdjęć. Nie przypisujemy nowych fotografii do nazwanych lokalizacji bez potwierdzenia.

Główne menu FIBERLAN zawiera szybkie odnośniki do stron PL/EN, biblioteki i współdzielonych danych kontaktowych. Panel Gutenberga ma wyszukiwanie sekcji, edycję obrazów, ALT oraz wybór pliku wideo; galerie i wzorce pozostają natywnymi blokami. Na EN ALT wstawionych obrazów jest angielski, na PL polski. Przed publikacją sprawdź wizerunki, prawa oraz widoczne oznaczenia infrastruktury. W paczce są wyłącznie zdjęcia, nie filmy. Osobny wariant spawarki na przezroczystym tle wykorzystuje rzeczywiste zdjęcie, bez generowania elementów. Zachowaj oryginały.

## 4.12.0 — rozbudowana galeria i poprawki zdjęć

Wgraj motyw i Core 4.12.0 z preview/szata-fotograficzna. Panel FIBERLAN pozostaje jedynym panelem zarządzania; usunięto duplikaty pod Wygląd. Po wejściu administratora wprowadzane są zmiany układu: usunięcie dodatkowego bloku Fluke w Usługach i galeria 15 miejsc. Importuj nową paczkę fiberlan-media-4.12.1.zip i zaznacz dopasowanie PL/EN. Import zachowuje poprzednie identyfikatory przypisań, wymienia wyłącznie wcześniejsze zdjęcia importera w danym slocie lub wskazane stare domyślne obrazy, i pozostawia ręczne obrazy z podpisami/linkami. Zdjęcia na nazwanych realizacjach są oznaczane jako poglądowe, jeśli nie potwierdzono dokładnej lokalizacji. Przed publikacją sprawdź podpisy i zdjęcia. Zrób kopię bazy.

Wydanie 4.12.1 poprawia obsługę wcześniejszych importów, oznaczenia zdjęć poglądowych w katalogu oraz odstępy w nagłówku Sprzęt. Paczka 4.12.1 zawiera 31 plików WebP, 45 unikalnych przypisań i oba nowe zdjęcia sprzętu.

## 4.13.0 — typografia, fotografie, logotypy i galeria

Manrope (lokalny WOFF2, licencja OFL) zastępuje Britannic w nagłówkach. Aktualizacja istniejących stron dodaje zdjęcie główne Realizacji, miejsce na fotografię zaplecza technicznego, 24 zdjęcia w galerii oraz animowaną taśmę logotypów. Zaktualizuj motyw i Core 4.13.0, wykonaj kopię bazy i zaimportuj fiberlan-media-4.13.zip z dopasowaniem PL/EN. Zdjęcia otwierają się w dialogu z klawiaturą, strzałkami, gestami dotykowymi i powrotem fokusu. Ręcznie dodane zdjęcia pozostają chronione. Logotypy są edytowalnymi blokami Obraz; źródła: Wikimedia Commons File:T-Mobile_logo.svg, File:Orange_logo.svg, File:Plus_Logo.svg, File:CDProjekt-logo.svg, File:ZUS_logo.svg. Obrazy są pobierane przez przeglądarkę z upload.wikimedia.org; w razie niedostępności widoczna jest nazwa. Zalecane zastąpienie plikami z aktualnych ksiąg znaków po uzyskaniu zgody na publikację (szczególnie ZUS: https://www.zus.pl/o-zus/o-nas/logo-zus). Logotypy nie oznaczają rekomendacji ani partnerstwa. Nie przypisujemy zdjęciom niepotwierdzonych lokalizacji.

## 4.13.1 — Suisse Intl i aktualne logo CD PROJEKT RED

Nagłówki mają mniejszą, responsywną skalę; główny H1 korzysta z szerokości faktycznej kolumny. Nie zmieniamy frazy SEO „Spawanie światłowodów” ani angielskiego odpowiednika. Suisse Intl jest komercyjny: motyw nie zawiera jego plików. Kup licencję odpowiednią dla klienta i wgraj oryginalne WOFF2 (pełna wersja z PL/EN) do wp-content/uploads/fiberlan-fonts/: suisse-intl-regular.woff2 (400), suisse-intl-medium.woff2 (500, opcjonalnie), suisse-intl-semibold.woff2 (600), suisse-intl-bold.woff2 (700). Regular oraz SemiBold lub Bold aktywują lokalne fonty automatycznie; do tego czasu widoczny jest Manrope z poprawionymi rozmiarami. Panel FIBERLAN pokazuje stan plików. Wyczyść cache. Fonty pozostają poza motywem. Źródło i licencja: https://www.swisstypefaces.com/fonts/suisse/.

Logo CD PROJEKT RED jest aktualnym czerwonym SVG z oficjalnej witryny spółki (https://www.cdprojekt.com/pl/media/logotypy/), po rebrandingu z 25.08.2026. Poprzednie domyślne logo jest wymieniane na istniejących stronach Realizacje PL/EN podczas wizyty administratora. Inne bloki, ręczne załączniki, zdjęcia i galerie pozostają bez zmian. Logo korzysta z oficjalnego zewnętrznego URL; przed publikacją sprawdź uprawnienia do użycia znaku. Nie trzeba ponownie importować paczki zdjęć.
