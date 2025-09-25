<?php
header("Content-Type: application/json");

// Recibe datos de PayPal (desde JS en checkout.html)
$data = json_decode(file_get_contents("php://input"), true);


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

// Enviar al API de CodeIgniter
$apiUrl = "http://192.168.2.245:8888/pagme_new/public/pagos-paypal";

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
