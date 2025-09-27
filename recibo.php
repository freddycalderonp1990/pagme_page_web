<?php
error_reporting(E_ERROR | E_PARSE);
require __DIR__ . '/vendor/setasign/fpdf/fpdf.php';

// Función para soportar acentos/ñ en FPDF
function texto($str) {
    return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $str);
}

// Capturar datos enviados por POST
$id_empresa    = $_POST['id_empresa']    ?? 'N/A';
$order_id    = $_POST['order_id']    ?? 'N/A';
$payer_id    = $_POST['payer_id']    ?? 'N/A';
$payer_nombre  = $_POST['payer_nombre']  ?? 'Cliente';
$payer_email = $_POST['payer_email'] ?? '---';
$producto    = $_POST['producto_titulo'] ?? '---';
$descripcion = $_POST['producto_descripcion'] ?? '---';
$precio      = $_POST['producto_precio'] ?? '0.00';
$duracion    = $_POST['producto_duracion'] ?? '---';
$moneda      = $_POST['moneda'] ?? 'USD';
$fecha_pago  = $_POST['fecha_pago'] ?? '';
$merchantIdVendedor               = $_POST['merchantIdVendedor'] ?? '';



// Clase PDF con cabecera y pie
class PDF extends FPDF {
    function Header() {
        // Franja verde
        $this->SetFillColor(22, 125, 66);
        $this->Rect(0, 0, $this->w, 25, 'F');

        // Logo
        $logoPath = __DIR__ . '/assets/img/logo.png';
        if (file_exists($logoPath)) {
            $this->Image($logoPath, 10, 5, 15); 
        }

        // Texto centrado
        $this->SetFont('Arial', 'B', 15);
        $this->SetTextColor(255, 255, 255);
        $this->SetY(9);
        $this->Cell(0, 10, texto('Recibo de Pago - PagMe'), 0, 1, 'C');
    }

    function Footer() {
        $this->SetY(-20);
        $this->SetFont('Arial', 'I', 9);
        $this->SetTextColor(1, 57, 23); // color institucional
        $this->MultiCell(0, 6, texto("Este recibo confirma que el pago ha sido procesado correctamente a través de PayPal.\nGracias por confiar en PagMe."), 0, 'C');
    }
}

// Crear PDF
$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(0,0,0);

// Encabezado de tabla
$pdf->Ln(10);
$pdf->SetFont('Arial','B',12);
$pdf->SetFillColor(22, 125, 66);
$pdf->SetTextColor(255,255,255);
$pdf->Cell(60, 10, texto('Campo'), 1, 0, 'C', true);
$pdf->Cell(120, 10, texto('Detalle'), 1, 1, 'C', true);

// Filas con datos
$pdf->SetFont('Arial','',12);
$pdf->SetTextColor(0,0,0);
$pdf->SetDrawColor(38, 38, 38); // bordes gris oscuro

$rows = [
     'ID Empresa:'      => $id_empresa,
    'ID de Pago:'      => $order_id,
    'ID del Cliente:'  => $payer_id,
    'ID del Vendedor:'  => $merchantIdVendedor,
    'Nombre:'          => $payer_nombre,
    'Email:'           => $payer_email,
    'Producto:'        => $producto,
    'Descripción:'     => $descripcion,
    'Precio:'          => $precio . " " . $moneda,
    'Duración:'        => $duracion,
    'Fecha de pago:'   => $fecha_pago,
];

foreach ($rows as $campo => $valor) {
    $pdf->Cell(60, 10, texto($campo), 1);
    $pdf->Cell(120, 10, texto($valor), 1, 1);
}

// Descargar
$pdf->Output("I", "Recibo_Pago_{$order_id}.pdf");
