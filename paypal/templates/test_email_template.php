<?php
header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/../../config/env_loader.php';


// Obtener JSON desde el body del POST (enviado desde Postman)
$input = json_decode(file_get_contents('php://input'), true);

// Si no viene nada, usar datos de ejemplo
$data = [
    'order_id'         => $input['order_id'] ?? 2321,
    'payer_nombre'     => $input['payer_nombre'] ?? 'Juan Pérez',
    'producto_titulo'  => $input['producto_titulo'] ?? 'Plan Trimestral',
    'producto_precio'  => $input['producto_precio'] ?? '15.00',
    'moneda'           => $input['moneda'] ?? 'USD',
    'fecha_pago'       => $input['fecha_pago'] ?? date('Y-m-d'),
];

// Construir el QR y logo desde las variables .env
$qrUrl =$_ENV['APP_URL']. $_ENV['QR_ENDPOINT'] . '?id=' . $data['order_id'];

$urlLogo=$_ENV['APP_URL']. $_ENV['URL_LOGO'];

// 🔹 Reemplazos de variables para la plantilla
$reemplazos = [
    '{{order_id}}'         => $data['order_id'],
    '{{payer_nombre}}'     => $data['payer_nombre'],
    '{{producto_titulo}}'  => $data['producto_titulo'],
    '{{producto_precio}}'  => $data['producto_precio'],
    '{{moneda}}'           => $data['moneda'],
    '{{fecha_pago}}'       => $data['fecha_pago'],
    '{{QR_URL}}'           => $qrUrl,
    '{{URL_LOGO}}'         => $urlLogo,
    '{{anio}}'             => date('Y')
];

// Cargar la plantilla HTML
$templatePath = __DIR__ . '/../templates/email_template.html';
$template = file_get_contents($templatePath);

// Reemplazar variables en la plantilla
$html = strtr($template, $reemplazos);

// Devolver como JSON para Postman
echo $html;
