<?php
require('../fpdf184/fpdf.php');
include '../conexion/conn.php';

// Configuración de la conexión a la base de datos
mysqli_set_charset($con, 'utf8');

// Consulta SQL para obtener la información de pacientes
$query = "
select idpacientes as ID, dni, concat_ws(' ', Nombre, Apellido) as Paciente,
DATE_FORMAT(p.fecha_nacimiento, '%d/%m/%Y') AS fecha_nacimiento,
        CASE
		WHEN TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) >= 1 THEN CONCAT(TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()), ' años')
		WHEN TIMESTAMPDIFF(MONTH, p.fecha_nacimiento, CURDATE()) >= 1 THEN CONCAT(TIMESTAMPDIFF(MONTH, p.fecha_nacimiento, CURDATE()), ' meses')
		ELSE CONCAT(TIMESTAMPDIFF(DAY, p.fecha_nacimiento, CURDATE()), ' días')
		END AS edad, correo, celular, direccion, genero
from pacientes pa 
inner join personas p on p.idpersonas=pa.fk_idpersonas
inner join genero g on g.idgenero=p.fk_idgenero
order by idpacientes";

$result = $con->query($query);

if (!$result) {
    echo 'Error en la consulta SQL: ' . $con->error;
    exit();
}

class PDF extends FPDF {
    function Header() {
        // Logo
        $this->Image('../imagenes/LOGONEGRO.png', 10, 6, 30);
        
        // Título
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, utf8_decode('Reporte de Pacientes'), 0, 1, 'C');

        // Fecha actual
        $this->SetFont('Arial', 'I', 10);
        $this->Cell(0, 10, utf8_decode('Fecha: ') . date('d-m-Y'), 0, 1, 'C');
        
        $this->Ln(10);
    }

    function Footer() {
        // Número de página
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo(), 0, 0, 'C');
    }
    
    function tablaHeader() {
        // Encabezados de la tabla
        $this->SetFillColor(173, 120, 220); // Color morado
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(10, 7, utf8_decode('ID'), 1, 0, 'C', true);
        $this->Cell(25, 7, utf8_decode('DNI'), 1, 0, 'C', true);
        $this->Cell(40, 7, utf8_decode('Paciente'), 1, 0, 'C', true);
        $this->Cell(15, 7, utf8_decode('Edad'), 1, 0, 'C', true);
        $this->Cell(35, 7, utf8_decode('Correo'), 1, 0, 'C', true);
        $this->Cell(25, 7, utf8_decode('Celular'), 1, 0, 'C', true);
        $this->Cell(40, 7, utf8_decode('Dirección'), 1, 0, 'C', true);
        $this->Ln();
    }
    
    function Row($data) {
        // Calcular la altura máxima de la fila
        $nb = 0;
        $cellWidths = [10, 25, 40, 15, 35, 25, 40];
        foreach ($data as $key => $col) {
            $nb = max($nb, $this->NbLines($cellWidths[$key], $col));
        }
        $height = 7 * $nb;
        
        // Nueva página si es necesario
        $this->CheckPageBreak($height);
        
        // Dibujar las celdas de la fila
        for ($i = 0; $i < count($data); $i++) {
            $width = $cellWidths[$i];
            $x = $this->GetX();
            $y = $this->GetY();
            $this->Rect($x, $y, $width, $height);
            $this->MultiCell($width, 7, $data[$i], 0, 'L');
            $this->SetXY($x + $width, $y);
        }
        $this->Ln($height);
    }

    function NbLines($w, $txt) {
        // Calcula el número de líneas de un MultiCell de ancho $w
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
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

    function CheckPageBreak($height) {
        // Si la altura de la fila provocará un salto de página, añadir una nueva página primero
        if ($this->GetY() + $height > $this->PageBreakTrigger) {
            $this->AddPage($this->CurOrientation);
            $this->tablaHeader();
        }
    }
}

// Creación del PDF y adición de página
$pdf = new PDF();
$pdf->AddPage();

$pdf->SetFont('Arial', '', 8);

// Inicializamos un array para almacenar los datos por género
$pacientesPorGenero = [];

while ($row = $result->fetch_assoc()) {
    $pacientesPorGenero[$row['genero']][] = $row;
}

foreach ($pacientesPorGenero as $genero => $pacientes) {
    // Mostrar el género
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 10, utf8_decode('Género: ' . $genero), 0, 1, 'L');
    
    // Encabezado de la tabla para cada grupo de pacientes
    $pdf->tablaHeader();
    
    $pdf->SetFont('Arial', '', 8);

    // Llenar la tabla con los pacientes de este género
    foreach ($pacientes as $paciente) {
        $pdf->Row([
            utf8_decode($paciente['ID']),
            utf8_decode($paciente['dni']),
            utf8_decode($paciente['Paciente']),
            utf8_decode($paciente['edad']),
            utf8_decode($paciente['correo']),
            utf8_decode($paciente['celular']),
            utf8_decode($paciente['direccion']),
        ]);
    }

    // Espacio entre secciones
    $pdf->Ln(5);
}

// Salida del PDF
$pdf->Output();
?>
