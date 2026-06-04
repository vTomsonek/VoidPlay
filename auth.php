<?php
require __DIR__ . '/config.php';

/**
 * Zwraca podany klucz, jeśli jest poprawny. W przeciwnym razie null.
 * Klucz brany jest z ?key= albo z ciasteczka ustawianego po pierwszym wejściu.
 */
function current_key(array $keys): ?string
{
    $key = $_GET['key'] ?? ($_COOKIE['player_key'] ?? null);
    if ($key !== null && isset($keys[$key])) {
        return $key;
    }
    return null;
}

/** Lista plików wideo w folderze media. */
function list_videos(string $dir, array $exts): array
{
    $out = [];
    if (!is_dir($dir)) {
        return $out;
    }
    foreach (scandir($dir) as $f) {
        if ($f === '.' || $f === '..') {
            continue;
        }
        $path = $dir . DIRECTORY_SEPARATOR . $f;
        if (!is_file($path)) {
            continue;
        }
        $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
        if (!in_array($ext, $exts, true)) {
            continue;
        }
        $out[] = [
            'file'  => $f,
            'name'  => prettify_name($f),
            'size'  => filesize($path),
            'mtime' => filemtime($path),
        ];
    }
    // Najnowsze na górze
    usort($out, fn($a, $b) => $b['mtime'] <=> $a['mtime']);
    return $out;
}

/** Czyści nazwę pliku do ładnego tytułu. */
function prettify_name(string $file): string
{
    $name = pathinfo($file, PATHINFO_FILENAME);
    // usuń typowe końcówki typu [identyfikator] z pobranych plików
    $name = preg_replace('/\s*[\[\(][A-Za-z0-9_\-]{6,}[\]\)]\s*$/', '', $name);
    return trim($name);
}

function human_size(int $bytes): string
{
    $u = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    $b = $bytes;
    while ($b >= 1024 && $i < count($u) - 1) {
        $b /= 1024;
        $i++;
    }
    return round($b, $b >= 10 || $i === 0 ? 0 : 1) . ' ' . $u[$i];
}

/* ---------------- Liczniki odtworzeń ---------------- */

/** Ścieżka pliku z licznikami (poza zasięgiem WWW dzięki data/.htaccess). */
function views_file(): string
{
    return __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'views.json';
}

/** Wczytuje wszystkie liczniki jako tablicę [nazwa_pliku => liczba]. */
function read_views(): array
{
    $f = views_file();
    if (!is_file($f)) {
        return [];
    }
    $data = json_decode((string) @file_get_contents($f), true);
    return is_array($data) ? $data : [];
}

/** Zwiększa licznik dla pliku o 1 (atomowo) i zwraca nową wartość. */
function increment_view(string $file): int
{
    $f = views_file();
    $dir = dirname($f);
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    $fp = @fopen($f, 'c+');
    if (!$fp) {
        return read_views()[$file] ?? 0;
    }
    flock($fp, LOCK_EX);
    $content = stream_get_contents($fp);
    $data = json_decode((string) $content, true);
    if (!is_array($data)) {
        $data = [];
    }
    $data[$file] = (int) ($data[$file] ?? 0) + 1;
    rewind($fp);
    ftruncate($fp, 0);
    fwrite($fp, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    return $data[$file];
}

/** Liczba ze spacją jako separatorem tysięcy (np. 1 234). */
function fmt_count(int $n): string
{
    return number_format($n, 0, ',', ' ');
}

/** Data w formacie polskim, np. "4 cze 2026" (na podstawie znacznika czasu pliku). */
function human_date(int $ts): string
{
    $m = ['', 'sty', 'lut', 'mar', 'kwi', 'maj', 'cze', 'lip', 'sie', 'wrz', 'paź', 'lis', 'gru'];
    return (int) date('j', $ts) . ' ' . $m[(int) date('n', $ts)] . ' ' . date('Y', $ts);
}
