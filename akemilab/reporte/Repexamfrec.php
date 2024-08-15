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

// Consulta SQL para obtener los exámenes más frecuentes en el rango de fechas
$query = "
SELECT te.tipoexam, COUNT(e.idexamen) AS cantidad_realizaciones
FROM examen e
INNER JOIN tipoexamen te ON e.fk_idtipoexamen = te.idtipoexamen
WHERE e.fecha BETWEEN '$start_date' AND '$end_date'
GROUP BY te.tipoexam
ORDER BY cantidad_realizaciones DESC
LIMIT 7";

$result = $con->query($query);

if (!$result) {
    echo 'Error en la consulta SQL: ' . $con->error;
    exit();
}

class PDF extends FPDF {
    function Header() {
        // Logo de la empresa en la esquina superior izquierda
        $this->Image('../imagenes/LOGONEGRO.png', 10, 10, 40);
        
        // Título del reporte
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(51, 153, 255); // Color celeste
        $this->Cell(0, 10, utf8_decode('Reporte de Exámenes más Frecuentes'), 0, 1, 'C');
        
        // Fechas de visualización del reporte
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 10, 'Desde: ' . $GLOBALS['start_date'] . ' - Hasta: ' . $GLOBALS['end_date'], 0, 1, 'C');
        $this->Ln(10);

        // Encabezados de la tabla con color de fondo morado
        $this->SetFillColor(173, 120, 220); // Color morado
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(100, 10, 'Tipo de Examen', 1, 0, 'C', true);
        $this->Cell(60, 10, 'Cantidad Realizaciones', 1, 0, 'C', true);
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

// Centrar la tabla en la página
$pdf->SetX(($pdf->GetPageWidth() - 160) / 2); // Ajuste para centrar en función del tamaño de la página y el ancho de la tabla

// Procesamiento de los resultados de la consulta y llenado de la tabla
$pdf->SetFont('Arial', '', 12);
while ($row = $result->fetch_assoc()) {
    $pdf->Cell(100, 10, utf8_decode($row['tipoexam']), 1, 0, 'L');
    $pdf->Cell(60, 10, $row['cantidad_realizaciones'], 1, 0, 'C');
    $pdf->Ln();
}

// Salida del PDF
$pdf->Output();
?>
