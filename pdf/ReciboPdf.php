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

    private function texto($str)
    {
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $str);
    }

    function NbLines($w, $txt)
    {
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', (string)$txt);
        $nb = strlen($s);
        if ($nb > 0 and $s[$nb - 1] == "\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ')
                $sep = $i;
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j)
                        $i++;
                } else
                    $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else
                $i++;
        }
        return $nl;
    }


    public static function generar($orderId, $rows)
    {
        $pdf = new self();
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 12);




        // Después del foreach que imprime las filas
        $pdf->Ln(0); // Espacio extra

        // 🔹 Generar el QR dinámico
        $qrUrl = $_ENV['APP_URL'] . $_ENV['QR_ENDPOINT'] . '?id=' . $orderId;

        try {
            // 🔹 Dimensiones del QR
            $qrWidth  = 40;
            $qrHeight = 40;

            // 🔹 Calcular posición X centrada
            $pageWidth = $pdf->GetPageWidth();
            $xCenter = ($pageWidth - $qrWidth) / 2;

            // 🔹 Posición Y (un poco debajo de la tabla actual)
            $yPos = $pdf->GetY() + 10;

            // 🔹 Dibujar el QR centrado
            $pdf->Image($qrUrl, $xCenter, $yPos, $qrWidth, $qrHeight, 'PNG');

            // 🔹 Texto centrado debajo del QR
            $pdf->SetXY($xCenter, $yPos + $qrHeight-5 );
      
          
        } catch (Exception $e) {
            $pdf->SetFont('Arial', 'I', 9);
            $pdf->SetTextColor(150, 0, 0);
                    $pdf->Cell(0, 10, $pdf->texto('QR no disponible'), 0, 1, 'C');



                    // 🔹 Mostrar el detalle del error capturado
                    $pdf->SetFont('Arial', '', 8);
                    $pdf->SetTextColor(255, 0, 0);
                    $mensajeError = "Error: " . $e->getMessage();
                    $pdf->MultiCell(0, 6, $pdf->texto($mensajeError), 0, 'C');
        }



        $pdf->Ln(10);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetFillColor(22, 125, 66);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(60, 10, $pdf->texto('Campo'), 1, 0, 'C', true);
        $pdf->Cell(120, 10, $pdf->texto('Detalle'), 1, 1, 'C', true);

        $pdf->SetFont('Arial', '', 12);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetDrawColor(38, 38, 38);
        foreach ($rows as $campo => $valor) {
            $lineHeight = 8;
            $col1Width  = 60;
            $col2Width  = 120;

            // calcular el número de líneas que ocupará cada texto
            $nb1 = $pdf->NbLines($col1Width, $pdf->texto($campo));
            $nb2 = $pdf->NbLines($col2Width, $pdf->texto($valor));
            $nb  = max($nb1, $nb2); // mayor número de líneas

            $rowHeight = $lineHeight * $nb;
            $x = $pdf->GetX();
            $y = $pdf->GetY();

            // --- Columna 1 (Campo) ---
            $pdf->Rect($x, $y, $col1Width, $rowHeight); // marco
            $pdf->MultiCell($col1Width, $lineHeight, $pdf->texto($campo), 0, 'L');

            // volver a la esquina superior derecha de la celda 1
            $pdf->SetXY($x + $col1Width, $y);

            // --- Columna 2 (Detalle) ---
            $pdf->Rect($x + $col1Width, $y, $col2Width, $rowHeight); // marco
            $pdf->MultiCell($col2Width, $lineHeight, $pdf->texto($valor), 0, 'L');

            // saltar a la siguiente fila completa
            $pdf->SetXY($x, $y + $rowHeight);
        }



        // $dir = __DIR__ . '/../storage/recibos/';

        $dir = realpath($_SERVER["DOCUMENT_ROOT"]) . "/uploads/recibos_paypal/";


        

        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $path = $dir . "recibo_$orderId.pdf";
        $pdf->Output('F', $path);

        return $path;
    }
}
