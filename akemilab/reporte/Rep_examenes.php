<?php
require('../fpdf184/fpdf.php');
include '../conexion/conn.php';

// Configuración de la conexión a la base de datos
mysqli_set_charset($con, 'utf8');

// Consulta SQL para obtener la información de los exámenes
$query = "
select idtipoexamen as ID,tipoexam as Examen, nomarea as Area, precio, muestra
from tipoexamen t 
inner join muestra m on m.idmuestra=t.fk_idmuestra
inner join area a on a.idarea=t.idtipoexamen
order by idtipoexamen";

$result = $con->query($query);

if (!$result) {
    echo 'Error en la consulta SQL: ' . $con->error;
    exit();
}

class PDF extends FPDF {
    function Header() {
        // Logo o título del reporte
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'Reporte de Exámenes', 0, 1, 'C');
        $this->Ln(10);
        
        // Encabezados de la tabla
        $this->SetFillColor(173, 120, 220); // Color morado
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(15, 10, 'ID', 1, 0, 'C', true);
        $this->Cell(60, 10, 'Examen', 1, 0, 'C', true);
        $this->Cell(40, 10, 'Área', 1, 0, 'C', true);
        $this->Cell(30, 10, 'Precio', 1, 0, 'C', true);
        $this->Cell(45, 10, 'Muestra', 1, 0, 'C', true);
        $this->Ln();
    }

    function Footer() {
        // Número de página
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Página ' . $this->PageNo(), 0, 0, 'C');
    }
}

// Creación del PDF y adición de página
$pdf = new PDF();
$pdf->AddPage();

// Procesamiento de los resultados de la consulta y llenado de la tabla
$pdf->SetFont('Arial', '', 10);
while ($row = $result->fetch_assoc()) {
    $pdf->Cell(15, 10, $row['ID'], 1, 0, 'C');
    $pdf->Cell(60, 10, $row['Examen'], 1, 0, 'L');
    $pdf->Cell(40, 10, $row['Area'], 1, 0, 'L');
    $pdf->Cell(30, 10, '$' . number_format($row['precio'], 2), 1, 0, 'R');
    $pdf->Cell(45, 10, $row['muestra'], 1, 0, 'L');
    $pdf->Ln();
}

// Salida del PDF
$pdf->Output();
?>
