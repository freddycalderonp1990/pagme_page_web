<?php
require __DIR__ . '/../vendor/setasign/fpdf/fpdf.php';


class ReciboPdf extends FPDF
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
        $this->Cell(0, 10, $this->texto('Recibo de Pago - PagMe'), 0, 1, 'C');
    }

    function Footer()
    {
        $this->SetY(-20);
        $this->SetFont('Arial', 'I', 9);
        $this->SetTextColor(1, 57, 23);
        $this->MultiCell(0, 6, $this->texto("Este recibo confirma que el pago ha sido procesado correctamente a través de PayPal.\nGracias por confiar en PagMe."), 0, 'C');
    }

    private function texto($str) {
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $str);
    }

    public static function generar($orderId, $rows)
    {
        $pdf = new self();
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 12);
        $pdf->Ln(10);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetFillColor(22, 125, 66);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(60, 10, $pdf->texto('Campo'), 1, 0, 'C', true);
        $pdf->Cell(120, 10, $pdf->texto('Detalle'), 1, 1, 'C', true);

        $pdf->SetFont('Arial','',12);
        $pdf->SetTextColor(0,0,0);
        $pdf->SetDrawColor(38, 38, 38);

        foreach ($rows as $campo => $valor) {
            $pdf->Cell(60, 10, $pdf->texto($campo), 1);
            $pdf->Cell(120, 10, $pdf->texto($valor), 1, 1);
        }

        $dir = __DIR__ . '/../storage/recibos/';
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $path = $dir . "recibo_$orderId.pdf";
        $pdf->Output('F', $path);

        return $path;
    }
}
