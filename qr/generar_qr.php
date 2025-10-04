<?php
require __DIR__ . '/../vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Logo\Logo;

// 🔹 Recibir el ID del pago (por GET o ejemplo fijo)
$idPago = isset($_GET['id']) ? (String)$_GET['id'] : "12345";



// 🔹 Contenido del QR (usa el enlace o un texto claro)
$url =  $idPago;

// 🔹 Crear el QR
$qr = QrCode::create($url)
    ->setEncoding(new Encoding('UTF-8'))
    ->setSize(400) // más grande para escaneo más fácil
    ->setMargin(12) // margen mayor mejora lectura
    ->setForegroundColor(new Color(22, 125, 66)) // Verde institucional #167D42
    ->setBackgroundColor(new Color(255, 255, 255)); // Fondo blanco

// 🔹 Añadir logo institucional pequeño
$logoPath = __DIR__ . '/../assets/img/logo.png';
$logo = null;

if (file_exists($logoPath)) {
    $logo = Logo::create($logoPath)
        ->setResizeToWidth(40) // tamaño más pequeño para permitir lectura
        ->setPunchoutBackground(true);
}

// 🔹 Generar QR
$writer = new PngWriter();
$result = $writer->write($qr, $logo);

// 🔹 Mostrar el QR directamente
header('Content-Type: image/png');
echo $result->getString();
exit;
