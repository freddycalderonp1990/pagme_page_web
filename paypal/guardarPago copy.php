<?php
header("Content-Type: application/json");

require_once __DIR__ . '/../config/env_loader.php';
require_once __DIR__ . '/../email/Mailer.php';

// FPDF para generar recibo

require __DIR__ . '/../vendor/setasign/fpdf/fpdf.php';



use App\Email\Mailer;

// Respuesta inicial
$responseData = [
    "success" => false,
    "status_code" => 500,
    "message" => "Error interno",
];

// Recibe datos de PayPal
$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "status_code" => 400,
        "message" => "No se recibieron datos válidos"
    ]);
    exit;
}

// Variables del payload
$idEmpresa           = $data['id_empresa'] ?? 1;
$orderId             = $data['order_id'] ?? '';
$status              = $data['status'] ?? '';
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


// Validar token si aplica
$requiredToken = filter_var($_ENV['REQUIRED_TOKEN'] ?? 'false', FILTER_VALIDATE_BOOLEAN);
if ($requiredToken) {
    $tokenEsperado = validarToken($idEmpresa, $productoTitulo, $productoDescripcion, $productoPrecio, $productoDuracion);
    if ($token !== $tokenEsperado) {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "status_code" => 401,
            "message" => "No autorizado"
        ]);
        exit;
    }
}

// Validar estado de pago
if ($status !== "COMPLETED") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "status_code" => 400,
        "message" => "Pago inválido o incompleto"
    ]);
    exit;
}

/* =======================================================
   1. GENERAR RECIBO PDF
======================================================= */
function texto($str)
{
    return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $str);
}

class PDF extends FPDF
{
    function Header()
    {
        $this->SetFillColor(22, 125, 66);
        $this->Rect(0, 0, $this->w, 25, 'F');
        $logoPath = __DIR__ . '/../assets/img/logo.png';
        if (file_exists($logoPath)) $this->Image($logoPath, 10, 5, 15);
        $this->SetFont('Arial', 'B', 15);
        $this->SetTextColor(255, 255, 255);
        $this->SetY(9);
        $this->Cell(0, 10, texto('Recibo de Pago - PagMe'), 0, 1, 'C');
    }
    function Footer()
    {
        $this->SetY(-20);
        $this->SetFont('Arial', 'I', 9);
        $this->SetTextColor(1, 57, 23);
        $this->MultiCell(0, 6, texto("Este recibo confirma que el pago ha sido procesado correctamente a través de PayPal.\nGracias por confiar en PagMe."), 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(0, 0, 0);
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(22, 125, 66);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(60, 10, texto('Campo'), 1, 0, 'C', true);
$pdf->Cell(120, 10, texto('Detalle'), 1, 1, 'C', true);
$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetDrawColor(38, 38, 38);

$rows = [
    'ID Empresa:'    => $idEmpresa,
    'ID de Pago:'    => $orderId,
    'ID del Cliente:' => $payerId,
    'Nombre:'        => $payerNombre,
    'Email:'         => $payerEmail,
    'Producto:'      => $productoTitulo,
    'Descripción:'   => $productoDescripcion,
    'Precio:'        => $productoPrecio . " " . $moneda,
    'Duración:'      => $productoDuracion,
    'Fecha de pago:' => $fechaPago,
];
foreach ($rows as $campo => $valor) {
    $pdf->Cell(60, 10, texto($campo), 1);
    $pdf->Cell(120, 10, texto($valor), 1, 1);
}

$reciboDir = __DIR__ . '/../storage/recibos/';
if (!is_dir($reciboDir)) mkdir($reciboDir, 0777, true);
$reciboPath = $reciboDir . "recibo_$orderId.pdf";
$pdf->Output('F', $reciboPath);

/* =======================================================
   2. ENVIAR CORREO
======================================================= */
$body = "
    <h3>Gracias por tu pago en PagMe</h3>
    <p><strong>Cliente:</strong> {$payerNombre}</p>
    <p><strong>Producto:</strong> {$productoTitulo}</p>
    <p><strong>Monto:</strong> {$productoPrecio} {$moneda}</p>
    <p><strong>Fecha:</strong> {$fechaPago}</p>
    <p>Adjunto encontrarás tu recibo en PDF.</p>
";
$emailEnviado=true;


$mailResult = Mailer::send(
    $payerEmail,
    $payerNombre,
    "Recibo de tu pago - Orden $orderId",
    $body,
    $_ENV['MAIL_FROM'],
    "Soporte PagMe",
    [$reciboPath]
);
if ($mailResult !== true) {
    $emailEnviado=false;
    error_log("Error enviando recibo: " . $mailResult);
}

/* =======================================================
   3. GUARDAR EN BASE DE DATOS (API)
======================================================= */
$payload = [
    "id_empresa"           => $idEmpresa,
    "order_id"             => $orderId,
    "status"               => $status,
    "producto_titulo"      => $productoTitulo,
    "producto_descripcion" => $productoDescripcion,
    "producto_precio"      => $productoPrecio,
    "producto_duracion"    => $productoDuracion,
    "moneda"               => $moneda,
    "payer_id"             => $payerId,
    "payer_nombre"         => $payerNombre,
    "payer_email"          => $payerEmail,
    "fecha_pago"           => $fechaPago,
    "estado_interno"       => $estadoInterno,
    "ip"                   => $ipCliente,
];

$apiUrl = $_ENV["API_URL_SAVE_PAGO"];
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

/* =======================================================
   4. RESPUESTA JSON SIEMPRE
======================================================= */
if ($httpCode === 200 && $response) {
    // La API ya devuelve JSON, lo decodificamos para añadir emailEnviado
    $apiData = json_decode($response, true);

    if (is_array($apiData)) {
        $apiData['emailEnviado'] = $emailEnviado;
        echo json_encode($apiData, JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            "success" => true,
            "status_code" => 200,
            "message" => "Pago registrado, pero la API no devolvió JSON válido",
            "api_response" => $response,
            "emailEnviado" => $emailEnviado
        ], JSON_UNESCAPED_UNICODE);
    }
} else {
    http_response_code($httpCode ?: 500);
    echo json_encode([
        "success" => false,
        "status_code" => $httpCode ?: 500,
        "message" => $response,
        "api_response" => $response ?: null,
        "emailEnviado" => $emailEnviado
    ], JSON_UNESCAPED_UNICODE);
}
