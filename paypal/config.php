<?php
header("Content-Type: application/json");

// Ruta del archivo .env en la raíz del proyecto
$envFile = __DIR__ . '/../.env';

if (!file_exists($envFile)) {
    echo json_encode(["error" => ".env no encontrado"]);
    exit;
}

// Leer línea por línea y cargar en $_ENV
$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue; // saltar comentarios
    list($name, $value) = explode('=', $line, 2);
    $_ENV[trim($name)] = trim($value);
}

// Responder JSON
echo json_encode([
    "client_id" => $_ENV["PAYPAL_CLIENT_ID"] ?? null,
    "mode"      => $_ENV["PAYPAL_MODE"] ?? "sandbox"
]);
