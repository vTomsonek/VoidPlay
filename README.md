<div align="center">

# 🌌 Voidplay

**Prywatna, zamknięta platforma do oglądania filmów.**

Dostęp tylko przez klucz w linku — kosmiczny ekran logowania, elegancki player
i automatyczna biblioteka budowana z plików w folderze `media`.

</div>

---

## ✨ Funkcje

- **Dostęp na klucz** (`?key=...`) — wchodzą tylko osoby, którym udostępnisz link; klucz zapamiętywany w ciasteczku.
- **Wiele kluczy** — osobny dla każdej osoby, łatwo odebrać dostęp jednej bez ruszania pozostałych.
- **Automatyczna biblioteka** — lista filmów budowana z folderu `media` (miniatury z klatki, oczyszczone tytuły).
- **Data dodania i licznik odtworzeń** na każdym kafelku (liczniki zapisywane atomowo w `data/views.json`).
- **Player [Plyr](https://plyr.io)** — przewijanie, prędkość odtwarzania, pełny ekran.
- **Streaming z obsługą HTTP Range** — płynne przewijanie dużych plików mp4.
- **Bezpieczne pliki** — wideo niedostępne bezpośrednio, serwowane wyłącznie przez chroniony `stream.php`.
- **Kosmiczny gate** — animowane gwiezdne tło (canvas), „oddychająca" poświata, logo Orbita, pole z podglądem klucza, stany błędu / ładowania / sukcesu. Respektuje `prefers-reduced-motion`.

## 🚀 Szybki start

```bash
git clone https://github.com/UZYTKOWNIK/voidplay.git
cd voidplay
cp config.example.php config.php      # ustaw własne klucze dostępu
```

Wrzuć pliki `.mp4` do folderu `media/`, uruchom serwer (np. XAMPP) i wejdź pod:

```
http://localhost/voidplay/?key=TWOJ_KLUCZ
```

Wygenerowanie losowego klucza:

```bash
php -r "echo bin2hex(random_bytes(12)).PHP_EOL;"
```

## ⚙️ Konfiguracja

Wszystko ustawiasz w `config.php` (plik lokalny, poza repo):

| Ustawienie | Opis |
|---|---|
| `$ACCESS_KEYS` | Klucze dostępu w formacie `'klucz' => 'opis osoby'`. |
| `$MEDIA_DIR` | Folder z filmami. |
| `$ALLOWED_EXT` | Dozwolone rozszerzenia (`mp4`, `m4v`, `webm`, `mov`). |
| `$SITE_TITLE` | Nazwa platformy. |

## 🛠️ Wymagania

- PHP 8.x (działa też na XAMPP).
- Apache (zalecane) lub inny serwer z PHP.

## 📦 Wdrożenie produkcyjne

Pełny przewodnik dla **Ubuntu 24.04 + Apache2 + PHP 8.3-FPM** znajdziesz w
[`DEPLOY.md`](DEPLOY.md). Najważniejsze:

- Blokadę dostępu do `media/` i `data/` ustaw w konfiguracji vhosta — na Apache2
  `.htaccess` bywa domyślnie ignorowany (gotowy plik: [`player-vhost.conf`](player-vhost.conf)).
- Używaj **HTTPS** (klucz leci w URL).
- Stosuj osobne klucze per osoba.

## 🗂️ Struktura

```
index.php            Gate (logowanie) + biblioteka + player + tło
stream.php           Serwowanie wideo z obsługą Range (chronione kluczem)
view.php             Rejestracja odtworzeń (licznik, chronione kluczem)
auth.php             Dostęp, listowanie plików, liczniki, formatowanie
config.php           Klucze i ustawienia (lokalny, poza repo)
config.example.php   Wzór konfiguracji
media/               Pliki wideo (poza repo)
data/                Liczniki odtworzeń (poza repo)
player-vhost.conf    Przykładowy vhost Apache
DEPLOY.md            Przewodnik wdrożenia
```

## 🔒 Bezpieczeństwo

- `config.php` (klucze), pliki w `media/` oraz `data/` są wykluczone z repozytorium.
- Wideo serwuje wyłącznie `stream.php` po weryfikacji klucza; foldery `media/` i `data/` są zablokowane przed dostępem z zewnątrz.
- Klucz w URL trafia do logów serwera i jest widoczny bez HTTPS — **używaj HTTPS**.

---

<div align="center">
<sub>Zbudowane na PHP • bez zewnętrznych zależności serwerowych • <a href="https://plyr.io">Plyr</a> do odtwarzania</sub>
</div>
