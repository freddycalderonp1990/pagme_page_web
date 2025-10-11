<?php



header("Content-Type: application/json");

require_once __DIR__ . '/../config/env_loader.php';
require_once __DIR__ . '/../email/mailer.php';
require_once __DIR__ . '/../pdf/ReciboPdf.php';
require_once __DIR__ . '/../services/PagoValidator.php';
require_once __DIR__ . '/../services/PagoService.php';


use App\Email\Mailer;


/* =======================================================
   🔐 MANEJO GLOBAL DE ERRORES Y EXCEPCIONES (RESPUESTA JSON)
======================================================= */

set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function ($e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "status_code" => 500,
        "message" => "Error: " . $e->getMessage()
            . " (Archivo: " . basename($e->getFile()) . ", línea " . $e->getLine() . ")"
    ], JSON_UNESCAPED_UNICODE);
    exit;
});

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "status_code" => 500,
            "message" => "Error fatal: " . $error['message']
                . " (Archivo: " . basename($error['file']) . ", línea " . $error['line'] . ")"
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
});






// Recibir datos
$data = json_decode(file_get_contents("php://input"), true);


if (!$data) {

    http_response_code(400);
    echo json_encode(["success" => false, "status_code" => 400, "message" => "No se recibieron datos válidos"]);
    exit;
}


// Validaciones
if (!PagoValidator::validarToken($data)) {
    http_response_code(401);
    echo json_encode(["success" => false, "status_code" => 401, "message" => "No autorizado"]);
    exit;
}
if (!PagoValidator::validarEstado($data['status'] ?? '')) {
    http_response_code(400);
    echo json_encode(["success" => false, "status_code" => 400, "message" => "Pago inválido o incompleto"]);
    exit;
}

/* =======================================================
   1. GUARDAR EN BASE DE DATOS (API)
======================================================= */
$idPagoPaypal = null;
list($httpCode, $response) = PagoService::guardarEnApi($data);

if ($httpCode === 200 && $response) {
    $apiData = json_decode($response, true);
    if (is_array($apiData) && isset($apiData['data']['idPagoPaypal'])) {
        $idPagoPaypal = $apiData['data']['idPagoPaypal'];
    }
}

/* =======================================================
   2. GENERAR RECIBO PDF (usando el ID si existe)
======================================================= */

function limpiarTexto($texto)
{
    return PagoService::limpiarTexto($texto);
}


$rows = [
    'ID Empresa:'     => $data['id_empresa'],
    'ID de Pago:'     => $data['order_id'],
    'ID del Cliente:' => $data['payer_id'],
    'ID Vendedor:'    => $data['id_vendedor'],
    'Nombre:'         => $data['payer_nombre'],
    'Email:'          => $data['payer_email'],
    'Producto:'       => limpiarTexto($data['producto_titulo']),
    'Descripción:'    => limpiarTexto($data['producto_descripcion']),
    'Precio:'         => $data['producto_precio'] . " " . $data['moneda'],
    'Duración:'       => $data['num_dias'],
    'Fecha de pago:'  => $data['fecha_pago'],
];
if ($idPagoPaypal) {
    $rows['ID Pago Interno:'] = $idPagoPaypal;
}

$reciboPath = ReciboPdf::generar($data['order_id'], $rows);

/* =======================================================
   3. ENVIAR CORREO
======================================================= */

$qrEndpoint = $_ENV['QR_ENDPOINT'];

// 1️⃣ Cargar el archivo HTML como texto
$template = file_get_contents(__DIR__ . '/templates/email_template.html');



// Construir el QR y logo desde las variables .env

$ambiente = $_ENV['AMBIENTE'] ?? 'produccion';

// URLs de entorno
if ($ambiente === 'produccion' || $ambiente === 'prod') {
    $qrUrl = $_ENV['APP_URL'] . $_ENV['QR_ENDPOINT'] . '?id=' . $data['order_id'];
    $urlLogo = $_ENV['APP_URL'] . $_ENV['URL_LOGO'];
} else {
    // xq al enviar el html por correo se necesita el dominio del servidor para visualizar la imagen o el qr
    $qrUrl = $_ENV['APP_URL_PROD'] . $_ENV['QR_ENDPOINT'] . '?id=' . $data['order_id'];
    $urlLogo = $_ENV['APP_URL_PROD'] . $_ENV['URL_LOGO'];
}


// 2️⃣ Reemplazar los valores
$reemplazos = [
    '{{order_id}}'    => $data['order_id'],
    '{{payer_nombre}}'    => $data['payer_nombre'],
    '{{producto_titulo}}' => $data['producto_titulo'],
    '{{producto_precio}}' => $data['producto_precio'],
    '{{moneda}}'          => $data['moneda'],
    '{{fecha_pago}}'      => $data['fecha_pago'],
    '{{QR_URL}}'      =>  $qrUrl,
    '{{URL_LOGO}}'      =>  $urlLogo,
    '{{anio}}'            => date('Y')
];

$body = strtr($template, $reemplazos);



$emailEnviado = Mailer::send(
    $data['payer_email'],
    $data['payer_nombre'],
    "Recibo de tu pago - Orden {$data['order_id']}",
    $body,
    $_ENV['MAIL_FROM'],
    "Soporte PagMe",
    [$reciboPath]
) === true;

/* =======================================================
   4. RESPUESTA JSON SIEMPRE
======================================================= */
if ($httpCode === 200 && $response) {
    $apiData = json_decode($response, true);
    if (is_array($apiData)) {
        $apiData['emailEnviado'] = $emailEnviado;
        if ($idPagoPaypal) {
            $apiData['idPagoPaypal'] = $idPagoPaypal; // extra seguro
        }
        echo json_encode($apiData, JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            "success" => true,
            "status_code" => 200,
            "message" => "Pago registrado, pero la API no devolvió JSON válido",
            "api_response" => $response,
            "emailEnviado" => $emailEnviado,
            "idPagoPaypal" => $idPagoPaypal
        ]);
    }
} else {
    http_response_code($httpCode ?: 500);
    echo json_encode([
        "success" => false,
        "status_code" => $httpCode ?: 500,
        "message" => "Error al registrar el pago en la API. Verifica la conexión o la URL del servicio. $response",
        "api_response" => $response ?: null,
        "emailEnviado" => $emailEnviado,
        "idPagoPaypal" => $idPagoPaypal
    ]);
}
