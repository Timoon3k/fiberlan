# Edycja strony głównej FIBERLAN

## Strona i jej sekcje

W panelu WordPress wybierz **Strony → Strona główna → Edytuj**. Otwórz widok listy bloków. Pięć sekcji odpowiada kolejno za wprowadzenie, doświadczenie, usługi, realizacje międzynarodowe i zaproszenie do kontaktu. Kliknij tekst w podglądzie, wprowadź zmianę i wybierz **Zapisz**. Układ oraz kolejność sekcji możesz zmieniać w widoku listy.

- **Wprowadzenie**. Edytuj nagłówek H1, opis i oba przyciski. Po zaznaczeniu przycisku kliknij ikonę linku i podaj adres docelowy. Domyślne przyciski wyceny i kontaktu prowadzą do formularza.
- **Doświadczenie**. Każda pozycja paska to grupa z osobnymi polami tekstowymi. Wyróżnienie dotyczące 20 lat odnosi się do roku 2026.
- **Usługi**. Każda karta to grupa zawierająca obraz, nagłówek z odnośnikiem i opis. Możesz powielić grupę lub zmienić kolejność kart. Po dodaniu podstrony usługi zmień odnośnik w nagłówku.
- **Realizacje**. Zaktualizuj nazwy obszarów i lokalizacje w poszczególnych kartach. Zastępuj ilustracje wyłącznie zdjęciami z danej realizacji.
- **Kontakt**. Zmień treść końcowego nagłówka i przycisku. Domyślny odnośnik prowadzi do podstrony kontaktowej.

Aby wstawić kolejną sekcję, otwórz wstawiacz bloków i wybierz **Wzorce → FIBERLAN**. Nowa sekcja jest niezależną kopią. Jeżeli powielasz sekcję z kotwicą, nadaj jej unikalną kotwicę w ustawieniach zaawansowanych.

## Zdjęcia, podpisy i logo

Wybierz blok **Obraz → Zastąp**. Wskaż plik z biblioteki albo prześlij nowy. Sprawdź kadr, podpis i tekst alternatywny. Dekoracyjna ilustracja obok pełnego opisu może mieć pusty tekst alternatywny. Po wymianie fotografii spawarki w pierwszej sekcji zaktualizuj również jej podpis. WordPress tworzy warianty rozmiarów dla obsługiwanych fotografii w bibliotece mediów.

W **Wygląd → Edytor → Wzorce → Części szablonu → Nagłówek** wybierz blok **Logo witryny** z widoku listy. Instalator wybiera oryginalne logo z pełnym napisem SC. W tym bloku możesz zastąpić je późniejszą wersją. Motyw zachowuje proporcje grafiki. Przed dodaniem pliku nagłówek pokazuje tytuł witryny. Ustaw go na „FIBERLAN SC” w ustawieniach ogólnych WordPressa.

## Dane kontaktowe i nawigacja

W **Wygląd → Edytor → Wzorce** wyszukaj synchronizowany wzorzec **FIBERLAN – dane kontaktowe**. Zmiana w tym wzorcu aktualizuje pasek nagłówka i stopkę. Przy zmianie telefonu lub poczty popraw zarówno widoczną treść, jak i odnośnik (tel lub mailto). Adresy docelowe przycisków sprzedażowych w treści strony edytuje się osobno. Przed publikacją potwierdź z klientem adres i oba numery.

Menu edytujesz w bloku **Nawigacja** nagłówka. Po aktualizacji etapu 1 w zapisanym wcześniej menu zamień kotwice strony głównej na odnośniki podstron. Przewidziane adresy to /o-nas/, /uslugi/, /sprzet/, /realizacje/ i /kontakt/. Własny układ stopki możesz zapisać po otwarciu części szablonu **Stopka**.

## Typografia i kolory

W **Wygląd → Edytor → Style** możesz zmienić paletę, typografię i globalny wygląd przycisków. Nagłówki korzystają z oryginalnego pliku Britannic Bold dostarczonego przez klienta. Motyw serwuje go z własnej domeny. Dla znaków nieobecnych w pliku przeglądarka wybiera krój zastępczy.

## Kontrola po zapisaniu

Otwórz stronę ponownie w edytorze. Sprawdź obrazy, podpisy, przyciski i podgląd mobilny. Po zmianach globalnych otwórz również stronę jako wylogowany użytkownik. Historię zmian treści strony znajdziesz w panelu wersji. Poniżej opisano edycję obu języków i katalogu realizacji.

Dostarczony plik BRITANIC.ttf nie zawiera m.in. znaków ą, ć, ę, ł, ń, ś, ź i ż. Do jednolitego składu wszystkich polskich nagłówków potrzebna będzie licencjonowana odmiana z pełnym polskim zestawem znaków.

## Podstrony, języki i realizacje

- Każda wersja językowa to osobna strona WordPress. W **Strony** wyszukaj polską lub angielską nazwę, otwórz edytor i zmieniaj bloki. Przełącznik PL/EN pokazuje powiązany adres bieżącej strony. Angielskie strony korzystają z szablonu **FIBERLAN EN** oraz oddzielnego nagłówka i stopki.
- Domyślna nawigacja prowadzi do odrębnych stron. Edytuj etykiety i adresy w blokach **Nawigacja** nagłówków PL i EN. Dane firmy pozostają synchronizowane. Etykiety przełącznika języka są w ustawieniach bloku **Przełącznik języka**.
- Otwórz stronę **Kontakt** lub **Contact** i zaznacz blok **Formularz zapytania**. W panelu **Teksty bloku** ustaw etykiety pól, tekst przycisku i komunikaty. Adres odbiorcy pozostaje ustawiony na `info@fiberlan.pl`. Możesz go zmienić w wtyczce przy zmianie obsługiwanej skrzynki.
- Aby dodać projekt, utwórz stronę z rodzicem **Realizacje** albo **Projects** i wybierz odpowiedni szablon FIBERLAN. Dodaj tytuł, opis w blokach, lokalizację w polu **Zajawka**, obrazek wyróżniający i kategorie. Fotografie dodaj blokiem **Galeria**. Datę możesz dodać w treści, a branżę przypisać w panelu **Branże**. Katalog pobiera opublikowane strony potomne.
- Aby powiązać nowy projekt w dwóch językach, nadaj obu stronom ten sam uproszczony adres końcowy, np. `sieci-optyczne-2026`, przy dwóch różnych stronach nadrzędnych. Wybieraj kategorie języka projektu. Dla nowej kategorii zastosuj prefiks `pl-` lub `en-` w adresie terminu.
- Sekcja Ribbon jest zbudowana z grup i natywnego bloku **Szczegóły**. Edytuj liczbę, lata, opis i tytuł rozwijanego panelu w widoku listy. Sekcje na stronie głównej i podstronie **O nas** są edytowane osobno.
