<?php
/**
 * Rejestruje jedno odtworzenie filmu i zwraca aktualny licznik (JSON).
 * Wywoływane przez JS po otwarciu filmu. Wymaga poprawnego klucza.
 */
require __DIR__ . '/auth.php';
header('Content-Type: application/json; charset=utf-8');

$key = current_key($ACCESS_KEYS);
if ($key === null) {
    http_response_code(403);
    echo json_encode(['error' => 'forbidden']);
    exit;
}

$file = basename($_GET['file'] ?? '');
$ext  = strtolower(pathinfo($file, PATHINFO_EXTENSION));
$path = $MEDIA_DIR . DIRECTORY_SEPARATOR . $file;

if (!in_array($ext, $ALLOWED_EXT, true) || !is_file($path)) {
    http_response_code(404);
    echo json_encode(['error' => 'not_found']);
    exit;
}

$count = increment_view($file);
echo json_encode(['file' => $file, 'count' => $count]);
