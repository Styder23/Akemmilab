<?php
require('../fpdf184/fpdf.php');
include '../conexion/conn.php';

// Configuración de la conexión a la base de datos
mysqli_set_charset($con, 'utf8');

// Obtener la fecha del formulario
if (isset($_GET['fecha'])) {
    $fecha = $_GET['fecha'];
} else {
    // Si no se proporciona una fecha válida, salir o manejar el error según tu lógica
    exit('No se proporcionó una fecha válida.');
}

// Consulta SQL para obtener los pacientes atendidos en la fecha especificada
$query = "
SELECT CONCAT_WS(' ', p.Nombre, p.Apellido) AS Paciente, GROUP_CONCAT(te.tipoexam ORDER BY te.tipoexam SEPARATOR ', ') AS examenes_realizados
FROM examen e
INNER JOIN pacientes pac ON e.fk_idpacientes = pac.idpacientes
INNER JOIN personas p ON pac.fk_idpersonas = p.idpersonas
INNER JOIN tipoexamen te ON e.fk_idtipoexamen = te.idtipoexamen
WHERE DATE(e.fecha) = '$fecha'
GROUP BY p.idpersonas
ORDER BY Paciente";

$result = $con->query($query);

if (!$result) {
    echo 'Error en la consulta SQL: ' . $con->error;
    exit();
}

class PDF extends FPDF {
    private $widths;
    private $aligns;

    function Header() {
        // Logo de la empresa
        $this->Image('../imagenes/LOGONEGRO.png', 10, 10, 40);
        $this->SetFont('Arial', 'B', 16);
        
        // Título del reporte
        $this->SetTextColor(100, 100, 100); // Color gris claro
        $this->Cell(0, 20, 'Reporte de Pacientes Atendidos', 0, 1, 'C');
        $this->Ln(5);

        // Fecha de visualización del reporte
        $this->SetFont('Arial', '', 12);
        $this->SetTextColor(0, 0, 0); // Color negro
        $this->Cell(0, 10, utf8_decode('Fecha de visualización: ') . date('Y-m-d'), 0, 1, 'C');
        $this->Ln(5);

        // Encabezados de la tabla con color de fondo morado
        $this->SetFillColor(173, 120, 220); // Color morado
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(80, 10, 'Paciente', 1, 0, 'C', true);
        $this->Cell(110, 10, utf8_decode('Exámenes Realizados'), 1, 0, 'C', true);
        $this->Ln();
    }

    function Footer() {
        // Número de página
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Página ' . $this->PageNo(), 0, 0, 'C');
    }

    // Método para establecer el ancho de las columnas
    function SetWidths($w) {
        $this->widths = $w;
    }

    // Método para establecer la alineación de las columnas
    function SetAligns($a) {
        $this->aligns = $a;
    }

    // Método para obtener el número de líneas necesarias para un texto dado y un ancho específico
    function NbLines($w, $txt) {
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 && $s[$nb - 1] == "\n")
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

    // Método para imprimir una fila de la tabla
    function Row($data) {
        $nb = 0;
        for ($i = 0; $i < count($data); $i++) {
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        }
        $h = 10 * $nb;
        
        // Comprobamos si hay espacio suficiente para la fila actual
        if ($this->GetY() + $h > $this->PageBreakTrigger) {
            $this->AddPage();
        }

        // Dibujamos las celdas
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            $x = $this->GetX();
            $y = $this->GetY();
            $this->Rect($x, $y, $w, $h);
            $this->MultiCell($w, 10, $data[$i], 0, $a);
            $this->SetXY($x + $w, $y);
        }
        $this->Ln($h);
    }

    // Método para obtener el número total de pacientes atendidos
    function TotalPacientes($total) {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(80, 20, 'Total de Pacientes Atendidos:', 0, 0, 'R');
        $this->Cell(110, 20, $total, 0, 1, 'C');
    }
}

// Creación del PDF y adición de página
$pdf = new PDF();
$pdf->AddPage();

// Establecer anchos y alineaciones para las columnas
$pdf->SetWidths(array(80, 110));
$pdf->SetAligns(array('L', 'L'));

// Procesamiento de los resultados de la consulta y llenado de la tabla
$pdf->SetFont('Arial', '', 12);
$total_pacientes = 0;
while ($row = $result->fetch_assoc()) {
    $data = array(
        utf8_decode($row['Paciente']),
        utf8_decode($row['examenes_realizados'])
    );
    $pdf->Row($data);

    // Incrementamos el contador de pacientes
    $total_pacientes++;
}

// Agregamos el total de pacientes atendidos al final de la tabla
$pdf->TotalPacientes($total_pacientes);

// Salida del PDF
$pdf->Output();
?>
