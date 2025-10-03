<?php
header("Content-Type: application/json");

require_once __DIR__ . '/../config/env_loader.php';
require_once __DIR__ . '/../email/Mailer.php';
require_once __DIR__ . '/../pdf/ReciboPdf.php';
require_once __DIR__ . '/../services/PagoValidator.php';
require_once __DIR__ . '/../services/PagoService.php';

use App\Email\Mailer;

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

function limpiarTexto($texto) {
    return PagoService::limpiarTexto($texto);
}


$rows = [
    'ID Empresa:'     => $data['id_empresa'],
    'ID de Pago:'     => $data['order_id'],
    'ID del Cliente:' => $data['payer_id'],
    'ID Vendedor:'    => $data['id_vendedor'],
    'Nombre:'         => $data['payer_nombre'],
    'Email:'          => $data['payer_email'],
    'Producto:'       => limpiarTexto( $data['producto_titulo']),
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
$body = "
<!DOCTYPE html>
<html lang='es'>
<head>
  <meta charset='UTF-8'>
  <style>
    body {
      font-family: Arial, Helvetica, sans-serif;
      background-color: #f4f6f8;
      margin: 0;
      padding: 0;
    }
    .container {
      max-width: 600px;
      margin: 20px auto;
      background: #ffffff;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      overflow: hidden;
    }
    .header {
      background: #195ba6; /* Azul institucional */
      padding: 20px;
      text-align: center;
      color: #fff;
    }
    .header img {
      max-height: 50px;
      margin-bottom: 10px;
    }
    .content {
      padding: 20px;
      color: #333;
      line-height: 1.6;
    }
    .content h3 {
      color: #195ba6;
      margin-top: 0;
    }
    .info {
      background: #f9fafc;
      border: 1px solid #e0e0e0;
      padding: 15px;
      border-radius: 6px;
    }
    .info p {
      margin: 8px 0;
    }
    .footer {
      background: #f4f6f8;
      text-align: center;
      padding: 15px;
      font-size: 12px;
      color: #777;
    }
  </style>
</head>
<body>
  <div class='container'>
    <div class='header'>
      <img src='https://underpropagme.xyz/assets/img/logo.png' alt='PagMe Logo'>
      <h2>PagMe</h2>
    </div>
    <div class='content'>
      <h3>Gracias por tu pago en PagMe</h3>
      <div class='info'>
        <p><strong>Cliente:</strong> {$data['payer_nombre']}</p>
        <p><strong>Producto:</strong> {$data['producto_titulo']}</p>
        <p><strong>Monto:</strong> {$data['producto_precio']} {$data['moneda']}</p>
        <p><strong>Fecha:</strong> {$data['fecha_pago']}</p>
      </div>
      <p>Adjunto encontrarás tu recibo en PDF.</p>
    </div>
    <div class='footer'>
      © ".date("Y")." PagMe. Todos los derechos reservados.
    </div>
  </div>
</body>
</html>
";


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
        "message" => "Error al registrar el pago en la API",
        "api_response" => $response ?: null,
        "emailEnviado" => $emailEnviado,
        "idPagoPaypal" => $idPagoPaypal
    ]);
}
