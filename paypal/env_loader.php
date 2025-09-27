<?php

$file = __DIR__ . '/../.env';
static $loaded = false;
if ($loaded) return; // evitar recargar varias veces


if (!file_exists($file)) {
    echo json_encode(["error" => ".env no encontrado"]);
    exit;
}

// Leer línea por línea y cargar en $_ENV
$lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

foreach ($lines as $line) {

    if (strpos(trim($line), '#') === 0) continue; // saltar comentarios
    list($name, $value) = explode('=', $line, 2);
    $_ENV[trim($name)] = trim($value);
}



$loaded = true;

function validarToken($idEmpresa,$titulo, $descripcion, $precio, $duracion)
{

    $claveSecreta = $_ENV["KEY_SECRET"]; // guárdala en .env

    $input =$idEmpresa. $titulo . $descripcion . $precio . $duracion;
    $input = $claveSecreta . $input . $claveSecreta;


    $tokenEsperado = hash('sha512', $input);

    return $tokenEsperado;
}
