<?php
/**
 * PRZYKŁADOWA konfiguracja.
 *
 * Skopiuj ten plik do config.php i ustaw własne klucze:
 *     cp config.example.php config.php
 *
 * config.php jest w .gitignore i NIE trafia do repozytorium (zawiera sekrety).
 */

$ACCESS_KEYS = [
    'ZMIEN_NA_WLASNY_LOSOWY_KLUCZ' => 'Główny',
    // 'inny-losowy-klucz'         => 'Jan Kowalski',
];

// Folder z plikami wideo (względem tego pliku)
$MEDIA_DIR = __DIR__ . DIRECTORY_SEPARATOR . 'media';

// Dozwolone rozszerzenia
$ALLOWED_EXT = ['mp4', 'm4v', 'webm', 'mov'];

// Nazwa platformy (wyświetlana w nagłówku)
$SITE_TITLE = 'Voidplay';
