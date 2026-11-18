# FIBERLAN SC · Motyw blokowy WordPress

Pierwszy etap serwisu FIBERLAN obejmuje polską stronę główną, nawigację, stopkę i edytowalne wzorce. Motyw znajduje się w `theme/fiberlan`. Używa natywnych bloków WordPressa. Do instalacji motywu nie są potrzebne Node.js, kompilator ani płatne wtyczki.

## Lokalna instalacja deweloperska

W katalogu repozytorium, na maszynie z Dockerem, wtyczką Docker Compose, Bash, curl i OpenSSL, wykonaj poniższe polecenie.

```sh
./dev/start.sh
```

Skrypt przygotowuje izolowaną bazę, uruchamia WordPressa na porcie 8080 interfejsu pętli zwrotnej, aktywuje motyw i instaluje stronę główną. Zmiany motywu są widoczne przez montowanie katalogu z kodem. Treści edytora i media pozostają w woluminach Dockera. Hasło lokalnego administratora `fiberlan-editor` znajduje się w prywatnym, ignorowanym przez Git pliku `.local/admin-password`. Logowanie odbywa się pod ścieżką `/wp-admin/`. Ten zestaw usług służy do pracy deweloperskiej.

Aby zatrzymać usługi z zachowaniem danych, wykonaj poniższe polecenie.

```sh
docker compose --env-file .local/compose.env stop
```

## Instalacja w standardowym WordPressie

1. Przygotuj paczkę zawierającą folder `fiberlan` z plikiem `style.css` bezpośrednio w tym folderze. Z katalogu repozytorium możesz wykonać `(cd theme && zip -rq ../fiberlan-theme.zip fiberlan)`.
2. Na witrynie testowej otwórz **Wygląd → Motywy → Dodaj motyw → Wyślij motyw na serwer**. Wskaż paczkę i aktywuj motyw.
3. Ustaw język witryny na polski i jej nazwę na **FIBERLAN SC**. Wybierz **Wygląd → FIBERLAN → Przygotuj stronę główną**. Strona powstaje w bazie jako zwykła strona z blokami. Instalator zachowuje zapisane zmiany przy kolejnym uruchomieniu i zgłasza komunikat, jeżeli witryna ma już inną statyczną stronę główną.
4. W istniejącym serwisie możesz również wstawiać poszczególne wzorce do nowej strony roboczej. Przed zmianą ustawień czytania sprawdź podgląd i zrób kopię bazy.
5. Instalator importuje oryginalne logo i trzy fotografie klienta do biblioteki mediów. Ustawia logo na nowej witrynie, a bloki zdjęć korzystają z responsywnych wariantów generowanych przez WordPress. Sprawdź kadry w podglądzie i potwierdź dane kontaktowe przed publikacją.

Podczas migracji bazy między domenami zastosuj narzędzie migracyjne WordPressa obsługujące serializację i adresy mediów. Przy instalacji z samego motywu wzorce otrzymują adresy plików bieżącej witryny.

## Co edytuje administrator

Treść strony znajduje się w edytorze stron. Elementy wspólne są w Edytorze witryny. Nagłówek i stopka odwołują się do jednego synchronizowanego wzorca danych kontaktowych. Administrator może zmienić każdy obraz, podpis, tekst lub odnośnik bez modyfikacji plików motywu.

[Instrukcja edycji](docs/EDYCJA.md) · [Status projektu i wyniki testów](PROJECT_STATUS.md)
