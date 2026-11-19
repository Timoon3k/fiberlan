# Edycja witryny FIBERLAN w Gutenbergu

## Strona i jej sekcje

W panelu WordPress wybierz **Strony → Strona główna → Edytuj**. Otwórz widok listy bloków. W widoku listy zaznacz grupę, aby podświetlić całą sekcję na stronie. Kliknij tekst w podglądzie, wprowadź zmianę i wybierz **Zapisz**. Układ oraz kolejność sekcji możesz zmieniać w widoku listy.

- **Wprowadzenie**. Edytuj nagłówek H1, opis i oba przyciski. Po zaznaczeniu przycisku kliknij ikonę linku i podaj adres docelowy. Domyślne przyciski wyceny i kontaktu prowadzą do formularza.
- **Doświadczenie**. Każda pozycja paska to grupa z osobnymi polami tekstowymi. Wyróżnienie dotyczące 20 lat odnosi się do roku 2026.
- **Usługi**. Każda karta to grupa zawierająca obraz, nagłówek z odnośnikiem i opis. W menu grupy wybierz **Powiel** lub **Usuń**. W motywie potomnym możesz też wstawić wzorzec **Karta usługi · Service card** wewnątrz grupy z kartami. Uzupełnij obraz, tytuł i opis. Zaznacz tytuł i użyj ikony odnośnika, aby podać adres usługi.
- **Realizacje**. Zaktualizuj nazwy obszarów i lokalizacje w poszczególnych kartach. Zastępuj ilustracje wyłącznie zdjęciami z danej realizacji.
- **Kontakt**. Zmień treść końcowego nagłówka i przycisku. Domyślny odnośnik prowadzi do podstrony kontaktowej.

Aby wstawić kolejną sekcję, otwórz wstawiacz bloków i wybierz **Wzorce → FIBERLAN**. Nowa sekcja jest niezależną kopią. Jeżeli powielasz sekcję z kotwicą, nadaj jej unikalną kotwicę w ustawieniach zaawansowanych.

## Zdjęcia, podpisy i logo

Wybierz blok **Obraz → Zastąp**. Wskaż plik z biblioteki albo prześlij nowy. Sprawdź kadr, podpis i tekst alternatywny. Dekoracyjna ilustracja obok pełnego opisu może mieć pusty tekst alternatywny. Po wymianie fotografii spawarki w pierwszej sekcji zaktualizuj również jej podpis. WordPress tworzy warianty rozmiarów dla obsługiwanych fotografii w bibliotece mediów.

W **Wygląd → Edytor → Wzorce → Części szablonu → Nagłówek** wybierz blok **Logo witryny** z widoku listy. Instalator wybiera oryginalne logo z pełnym napisem SC. W tym bloku możesz zastąpić je późniejszą wersją. Motyw zachowuje proporcje grafiki. Przed dodaniem pliku nagłówek pokazuje tytuł witryny. Ustaw go na „FIBERLAN SC” w ustawieniach ogólnych WordPressa.

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

Link powrotu na górę w stopce prowadzi do `#fl-page-top`. Etykietę możesz zmienić w części szablonu **Stopka** lub **Footer (EN)**. Jeżeli masz już własną zapisaną stopkę, dodaj w niej zwykły odnośnik do tej kotwicy. Klasa akapitu `fl-back-top` nadaje mu wygląd przycisku.

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
