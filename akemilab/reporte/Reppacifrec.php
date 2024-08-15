<?php
require('../fpdf184/fpdf.php');
include '../conexion/conn.php';

// Configuración de la conexión a la base de datos
mysqli_set_charset($con, 'utf8');

// Obtener las fechas del formulario
if (isset($_POST['start_date']) && isset($_POST['end_date'])) {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
} else {
    // Si no se proporcionan fechas válidas, salir o manejar el error según tu lógica
    exit('No se proporcionaron fechas válidas.');
}

// Consulta SQL para obtener los pacientes más frecuentes en el rango de fechas
$query = "
SELECT CONCAT_WS(' ', p.Nombre, p.Apellido) AS Cliente, p.dni as DNI, COUNT(e.idexamen) AS cantidad_examenes
FROM examen e
INNER JOIN pacientes pac ON e.fk_idpacientes = pac.idpacientes
INNER JOIN personas p ON pac.fk_idpersonas = p.idpersonas
WHERE e.fecha BETWEEN '$start_date' AND '$end_date'
GROUP BY p.Nombre, p.Apellido
ORDER BY cantidad_examenes DESC
LIMIT 5";

$result = $con->query($query);

if (!$result) {
    echo 'Error en la consulta SQL: ' . $con->error;
    exit();
}

class PDF extends FPDF {
    function Header() {
        // Logo de la empresa
        $this->Image('../imagenes/LOGONEGRO.png', 165, 10, 40);
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(80);
        $this->Cell(30, 10, 'Reporte de Pacientes más Frecuentes', 0, 0, 'C');
        $this->Ln(20);
        
        // Fechas de visualización del reporte
        $this->Cell(80);
        $this->SetFont('Arial', '', 12);
        $this->Cell(30, 10, 'Desde: ' . $GLOBALS['start_date'] . ' - Hasta: ' . $GLOBALS['end_date'], 0, 0, 'C');
        $this->Ln(10);

        // Encabezados de la tabla con color de fondo morado
        $this->SetFillColor(173, 120, 220); // Color morado
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(60, 10, 'Cliente', 1, 0, 'C', true);
        $this->Cell(60, 10, 'DNI', 1, 0, 'C', true);
        $this->Cell(60, 10, 'Cantidad de Exámenes', 1, 0, 'C', true);
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
$pdf->SetFont('Arial', '', 12);
while ($row = $result->fetch_assoc()) {
    $pdf->Cell(60, 10, utf8_decode($row['Cliente']), 1, 0, 'L');
    $pdf->Cell(60, 10, utf8_decode($row['DNI']), 1, 0, 'L');
    $pdf->Cell(60, 10, $row['cantidad_examenes'], 1, 0, 'C');
    $pdf->Ln();
}

// Salida del PDF
$pdf->Output();
?>
