StudentNoteDashboard
by Mateusz Karczewski
====================

Opis
 - StudentNoteDashboard to wewnętrzna aplikacja dla studentów do organizacji pracy: notatki (wraz z plikami), tablica pytań/odpowiedzi, ankiety, harmonogram wydarzeń, zarządzanie semestrami/przedmiotami.
 - Moduł Notatki wspiera: wybór semestru i przedmiotu, dodawanie treści (prosty edytor), załączniki (obrazy, PDF/DOC/XLS/PPT/TXT), głosowanie (+/−), komentarze z odpowiedziami i moderacją, oraz podgląd szczegółów.
 - Panel administratora (dropdown w profilu) obejmuje: użytkowników (edycja, grupy, reset hasła) oraz słowniki (semestry, przedmioty, grupy).

Wymagania
 - PHP 8.2+
 - Composer
 - Node.js 18+ i npm
 - Baza: SQLite lub MySQL (domyślnie wspierane przez Laravel)

Szybka instalacja
1) Klonowanie i zależności
   - git clone <repo>
   - cd StudentNoteDashboard
   - composer install
   - npm install

2) Konfiguracja środowiska
   - skopiuj plik .env.example do .env i ustaw przynajmniej:
     - APP_NAME, APP_URL
     - DB_CONNECTION (sqlite lub mysql) + parametry połączenia
   - wygeneruj klucz aplikacji: php artisan key:generate

3) Migracje i link do storage
   - php artisan migrate --seed
   - php artisan storage:link

4) Budowa frontendu
   - środowisko developerskie: npm run dev
   - produkcyjna kompilacja: npm run build

5) Uruchomienie
   - php artisan serve
   - Logowanie admina (pierwsze uruchomienie):
     - login (nr albumu): 00000
     - hasło: root_admin
     - Imię i nazwisko: Admin (można zmienić po zalogowaniu)
   - Po zalogowaniu zalecana zmiana danych admina (imię i nazwisko, nr albumu, hasło) w profilu.

Seed danych (minimalny do startu)
 - AdminUserSeeder: tworzy konto administratora z loginem (nr albumu) 00000 i hasłem root_admin (imię i nazwisko: Admin; email techniczny: admin@panel.local).
 - BasicDataSeeder: dodaje podstawowe wpisy słownikowe (Semestr 1, przykładowe przedmioty Wykłady/Ćwiczenia, Grupa A), aby moduł Notatek działał od razu po instalacji.

Uwaga dot. motywów
 - W profilu użytkownika można wybrać motyw interfejsu (domyślnie: Ciemny). Dostępne: Ciemny, Jasny, Ocean, Forest, Candy.

Licencja i prawa
 - Aplikacja napisana przez Mateusz Karczewski.
 - Wykorzystywanie jej bez zgody autora może być przyczyną pociągnięcia do odpowiedzialności prawnej.

Najczęstsze problemy
 - Brak możliwości zapisu plików: upewnij się, że uruchomiono php artisan storage:link i uprawnienia do katalogu storage są prawidłowe.
 - Za duże pliki: dostosuj upload_max_filesize i post_max_size w php.ini (domyślnie walidacja akceptuje do 10 MB na plik).
 - Puste listy w Notatkach: dodaj semestr i przedmioty w Panelu admina (dropdown w profilu) lub uruchom seedy.
