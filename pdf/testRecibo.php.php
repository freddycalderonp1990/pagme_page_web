<?php
header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/env_loader.php';
require_once __DIR__ . '/../pdf/ReciboPdf.php';

// 🔹 Obtener datos del body (por si vienes desde Postman)
$input = json_decode(file_get_contents('php://input'), true);

// 🔹 Datos de ejemplo por defecto (si no envías nada)
$data = [
    'id_empresa'            => 1,
    'order_id'              => 2321,
    'payer_id'              => 'PAYER-98765',
    'payer_nombre'          => 'Juan Pérez',
    'payer_email'           => 'juanperez@example.com',
    'producto_titulo'       => 'Plan Trimestral',
    'producto_descripcion'  => 'Suscripción de tres meses al servicio PagMe Premium.',
    'producto_precio'       => '15.00',
    'moneda'                => 'USD',
    'num_dias'              => 90,
    'fecha_pago'            => date('Y-m-d'),
    'id_vendedor'           => 1001,
];

// 🔹 Construir tabla del PDF directamente
$rows = [
    'ID Empresa:'      => $data['id_empresa'],
    'ID de Pago:'      => $data['order_id'],
    'ID del Cliente:'  => $data['payer_id'],
    'ID Vendedor:'     => $data['id_vendedor'],
    'Nombre:'          => $data['payer_nombre'],
    'Email:'           => $data['payer_email'],
    'Producto:'        => 'test api',
    'Descripción:'     => 'Suscripción de tres meses al servicio PagMe Premium.',
    'Precio:'          => $data['producto_precio'] . ' ' . $data['moneda'],
    'Duración:'        => $data['num_dias'] . ' días',
    'Fecha de pago:'   => $data['fecha_pago']
];
// (opcional)
$idPagoPaypal = $input['idPagoPaypal'] ?? null;
if ($idPagoPaypal) {
    $rows['ID Pago Interno:'] = $idPagoPaypal;
}

// 🔹 Generar el recibo PDF
$reciboPath = ReciboPdf::generar($data['order_id'], $rows);

// 🔹 Forzar la descarga del PDF al navegador
if (file_exists($reciboPath)) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="recibo_' . $data['order_id'] . '.pdf"');
    readfile($reciboPath);
    exit;
} else {
    echo "<p style='color:red;'>No se pudo generar el PDF en: <code>$reciboPath</code></p>";
}

/**
 * 🔹 Helper: limpiar texto para PDF (acentos, símbolos)
 */
function limpiarTexto($texto) {
    return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', (string)$texto);
}
