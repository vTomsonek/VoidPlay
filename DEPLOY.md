# Wdrożenie na Ubuntu 24.04 + Apache2 + PHP 8.3-FPM

Przewodnik krok po kroku, jak przenieść Voidplay z XAMPP na serwer produkcyjny.

## Czym to się różni od XAMPP

1. **`.htaccess` w `media/` nie zadziała.** Apache2 domyślnie ma `AllowOverride None`, więc plik jest ignorowany — a wtedy filmy są pobieralne z pominięciem klucza. Blokadę trzeba ustawić w konfiguracji vhosta (gotowy plik: `player-vhost.conf`).
2. **PHP działa przez FPM**, nie jako moduł — vhost musi przekazywać `.php` do gniazda `php8.3-fpm.sock`.
3. **Uprawnienia plików** należą do `www-data`.
4. **Klucz w adresie URL** trafia do logów Apache i jest widoczny w sieci bez HTTPS — dlatego zdecydowanie zalecany jest certyfikat (Let's Encrypt).

## 1. Pakiety

```bash
sudo apt update
sudo apt install -y apache2 php8.3-fpm
sudo a2enmod proxy_fcgi setenvif rewrite
sudo a2enconf php8.3-fpm
sudo systemctl restart apache2
```

## 2. Wgranie plików

```bash
sudo mkdir -p /var/www/voidplay
# skopiuj zawartość projektu do /var/www/voidplay (np. git clone albo rsync)
sudo chown -R www-data:www-data /var/www/voidplay
sudo find /var/www/voidplay -type d -exec chmod 755 {} \;
sudo find /var/www/voidplay -type f -exec chmod 644 {} \;
cp /var/www/voidplay/config.example.php /var/www/voidplay/config.php   # ustaw klucze
```

## 3. Vhost

```bash
sudo cp /var/www/voidplay/player-vhost.conf /etc/apache2/sites-available/voidplay.conf
sudo nano /etc/apache2/sites-available/voidplay.conf   # ustaw ServerName
sudo a2ensite voidplay.conf
sudo apache2ctl configtest      # powinno zwrócić: Syntax OK
sudo systemctl reload apache2
```

Plik `player-vhost.conf` zawiera dwa warianty: **A** — własna (sub)domena,
**B** — podkatalog istniejącej strony.

## 4. HTTPS (zalecane)

```bash
sudo apt install -y certbot python3-certbot-apache
sudo certbot --apache -d filmy.twojadomena.pl
```

## 5. Streaming dużych plików

W `/etc/php/8.3/fpm/php.ini` (tylko jeśli zauważysz ucinanie transferu):

```ini
max_execution_time = 0
output_buffering = Off
```

Po zmianie: `sudo systemctl restart php8.3-fpm`.

## 6. (Opcjonalnie) Szybsze serwowanie — X-Sendfile

```bash
sudo apt install -y libapache2-mod-xsendfile
sudo a2enmod xsendfile
```

W bloku `<Directory /var/www/voidplay>` dodaj:

```apache
XSendFile On
XSendFilePath /var/www/voidplay/media
```

W `stream.php`, po sprawdzeniu klucza i istnienia pliku, można oddać wysyłkę Apache'owi:

```php
header('X-Sendfile: ' . $path);
header('Content-Type: ' . $mime);
exit;
```

Obecny `stream.php` działa też bez tego — to wyłącznie optymalizacja wydajności.

## 7. Test

- `https://filmy.twojadomena.pl/?key=TWOJ_KLUCZ` → lista filmów.
- Bez klucza lub ze złym → ekran logowania.
- `https://filmy.twojadomena.pl/media/film.mp4` → musi zwrócić **403 Forbidden**.
  Jeśli zwraca plik — blokada `/media` w vhoście nie działa.

## Uwagi bezpieczeństwa

- Używaj HTTPS — bez niego klucz leci otwartym tekstem.
- Klucz pojawia się w logach Apache. Można filtrować parametr `key` w `LogFormat`.
- Dodawaj osobne klucze per osoba w `config.php`.
