<?php


$file = __DIR__ . '/../.env';
static $loaded = false;
if ($loaded) return; // evitar recargar varias veces

if (!file_exists($file)) {
    echo json_encode(["error" => ".env no encontrado"]);
    exit;
}

$lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

foreach ($lines as $line) {
    $line = trim($line);

    // Saltar comentarios
    if ($line === '' || str_starts_with($line, '#')) {
        continue;
    }

    // Ignorar comentarios al final de la línea: VAR=valor # comentario
    if (strpos($line, '#') !== false) {
        $line = preg_replace('/\s+#.*$/', '', $line);
    }

    // Separar clave y valor
    if (strpos($line, '=') === false) continue; // línea inválida
    list($name, $value) = explode('=', $line, 2);

    $name = trim($name);
    $value = trim($value);

    // Quitar comillas si existen
    $value = preg_replace('/^["\'](.*)["\']$/', '$1', $value);

    // Guardar en $_ENV y $_SERVER
    $_ENV[$name] = $value;
    $_SERVER[$name] = $value;
    putenv("$name=$value");
}

$loaded = true;


function generateSha512($idEmpresa,$titulo, $descripcion, $precio, $duracion)
{

    $claveSecreta = $_ENV["KEY_SECRET"]; // guárdala en .env

    $input =$idEmpresa. $titulo . $descripcion . $duracion;

        $input = trim($input);
    $input = preg_replace('/\s+/', '', $input);

    $input = $claveSecreta . $input . $claveSecreta;





    $tokenEsperado = hash('sha512', $input);


    return $tokenEsperado;
}
