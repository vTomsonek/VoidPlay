<?php
/**
 * Serwuje plik wideo z obsługą HTTP Range (przewijanie, szybki start).
 * Wymaga poprawnego klucza – inaczej 403. Nie ujawnia ścieżki na dysku.
 */
require __DIR__ . '/auth.php';

$key = current_key($ACCESS_KEYS);
if ($key === null) {
    http_response_code(403);
    exit('Brak dostępu.');
}

$file = $_GET['file'] ?? '';
// zabezpieczenie przed wyjściem poza folder media
$file = basename($file);
$path = $MEDIA_DIR . DIRECTORY_SEPARATOR . $file;

$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
if (!in_array($ext, $ALLOWED_EXT, true) || !is_file($path)) {
    http_response_code(404);
    exit('Nie znaleziono pliku.');
}

$mime = [
    'mp4'  => 'video/mp4',
    'm4v'  => 'video/mp4',
    'webm' => 'video/webm',
    'mov'  => 'video/quicktime',
][$ext] ?? 'application/octet-stream';

$size = filesize($path);
$start = 0;
$end = $size - 1;

header('Content-Type: ' . $mime);
header('Accept-Ranges: bytes');
header('Cache-Control: private, max-age=0');

// obsługa zakresu
if (isset($_SERVER['HTTP_RANGE'])) {
    if (preg_match('/bytes=(\d*)-(\d*)/', $_SERVER['HTTP_RANGE'], $m)) {
        if ($m[1] !== '') {
            $start = (int) $m[1];
        }
        if ($m[2] !== '') {
            $end = (int) $m[2];
        }
        if ($start > $end || $start >= $size) {
            http_response_code(416);
            header("Content-Range: bytes */$size");
            exit;
        }
        http_response_code(206);
        header("Content-Range: bytes $start-$end/$size");
    }
}

$length = $end - $start + 1;
header('Content-Length: ' . $length);

// wyślij dane porcjami
$fp = fopen($path, 'rb');
fseek($fp, $start);
$chunk = 8192;
$remaining = $length;

// wyczyść bufory, żeby nie psuć dużych transferów
while (ob_get_level() > 0) {
    ob_end_clean();
}

while ($remaining > 0 && !feof($fp) && connection_status() === CONNECTION_NORMAL) {
    $read = ($remaining > $chunk) ? $chunk : $remaining;
    echo fread($fp, $read);
    flush();
    $remaining -= $read;
}
fclose($fp);
exit;
