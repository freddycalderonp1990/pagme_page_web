<?php
header("Content-Type: application/json");
require_once __DIR__ . '/env_loader.php';


// Recibe datos de PayPal (desde JS en checkout.html)
$data = json_decode(file_get_contents("php://input"), true);



// Variables del payload
$idEmpresa           = $data['id_empresa'] ?? 1;
$orderId             = $data['order_id'] ?? '';

$productoTitulo      = $data['producto_titulo'] ?? 'Producto sin título';
$productoDescripcion = $data['producto_descripcion'] ?? 'Sin descripción';
$productoPrecio      = $data['producto_precio'] ?? '0.00';
$productoDuracion    = $data['producto_duracion'] ?? 'N/A';
$moneda              = $data['moneda'] ?? 'USD';
$payerId             = $data['payer_id'] ?? '';
$payerNombre         = $data['payer_nombre'] ?? '';
$payerEmail          = $data['payer_email'] ?? '---';
$fechaPago           = $data['fecha_pago'] ?? date("Y-m-d H:i:s");
$estadoInterno       = $data['estado_interno'] ?? 'pendiente';
$ipCliente           = $data['ip'] ?? $_SERVER['REMOTE_ADDR'];
$token               = $data['token'] ?? '';


$requiredToken = filter_var($_ENV['REQUIRED_TOKEN'] ?? 'false', FILTER_VALIDATE_BOOLEAN);

if ($requiredToken) {
  
$tokenEsperado=validarToken($idEmpresa,$productoTitulo,$productoDescripcion,$productoPrecio,$productoDuracion);


if ($token !== $tokenEsperado) {
  //die("❌ Error: datos inválidos o manipulados.");
      http_response_code(401);
    echo json_encode([
        "success" => false,
        "status_code" => 401,
        "message" => "No autorizado"
    ]);
   exit;
}
}

// Validar datos básicos
if (!$data || !isset($data['status']) || $data['status'] !== 'COMPLETED') {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "status_code" => 400,
        "message" => "Pago inválido o incompleto"
    ]);
   exit;
}

// Transformar JSON de PayPal → payload para tu API
$payload = $data;


$apiUrl = $_ENV["API_URL_SAVE_PAGO"] ;


$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);


// Devolver exactamente la respuesta del API
if ($httpCode === 200) {
    echo $response;
} else {
    echo json_encode([
        "success" => false,
        "status_code" => $httpCode,
        "message" => "Error al registrar el pago en la API",
        "api_response" => $response
    ]);
}
