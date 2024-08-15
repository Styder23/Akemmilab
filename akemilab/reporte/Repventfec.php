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

// Consulta SQL para obtener la suma total de las ventas en el rango de fechas especificado
$query_sum = "
SELECT SUM(total) AS total_ventas
FROM comprobante
WHERE DATE(fechacompro) BETWEEN '$start_date' AND '$end_date'";

$result_sum = $con->query($query_sum);
$total_ventas = 0;

if ($result_sum && $row_sum = $result_sum->fetch_assoc()) {
    $total_ventas = $row_sum['total_ventas'];
}

// Consulta SQL para obtener las ventas en el rango de fechas especificado y agrupar exámenes
$query = "
SELECT 
    c.idcomprobante as ID, 
    c.total, 
    dv.Codigo,  
    tc.tipo,  
    tg.tipopag,  
    GROUP_CONCAT(DISTINCT te.tipoexam SEPARATOR ', ') AS examenes,
    c.fechacompro
FROM comprobante c
INNER JOIN detalle_venta dv ON c.idcomprobante = dv.fk_idcomprob
INNER JOIN tipocomprobante tc ON tc.idtipocom = c.fk_tipocom
INNER JOIN tipopago tg ON tg.idtipopago = dv.fk_tipopg
INNER JOIN examen e ON e.idexamen = dv.fk_idexamen
INNER JOIN tipoexamen te ON te.idtipoexamen = e.fk_idtipoexamen
WHERE DATE(c.fechacompro) BETWEEN '$start_date' AND '$end_date'
GROUP BY c.idcomprobante, c.total, dv.Codigo, tc.tipo, tg.tipopag, c.fechacompro
ORDER BY c.fechacompro ASC";

$result = $con->query($query);

if (!$result) {
    echo 'Error en la consulta SQL: ' . $con->error;
    exit();
}

class PDF extends FPDF {
    function Header() {
        global $start_date, $end_date, $total_ventas;

        // Logo de la empresa
        $this->Image('../imagenes/LOGONEGRO.png', 165, 10, 40);
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(80);
        $this->Cell(30, 10, 'Reporte de Ventas', 0, 0, 'C');
        $this->Ln(20);
        
        // Fechas de visualización del reporte
        $this->Cell(80);
        $this->SetFont('Arial', '', 12);
        $this->Cell(30, 10, 'Desde: ' . $start_date . ' - Hasta: ' . $end_date, 0, 0, 'C');
        $this->Ln(10);

        // Total de ventas
        $this->Cell(80);
        $this->SetFont('Arial', '', 12);
        $this->Cell(30, 10, 'Total Ventas: ' . number_format($total_ventas, 2), 0, 0, 'C');
        $this->Ln(10);

        // Encabezados de la tabla con color de fondo morado
        $this->SetFillColor(173, 120, 220); // Color morado
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(10, 10, 'ID', 1, 0, 'C', true);
        $this->Cell(40, 10, 'Fecha', 1, 0, 'C', true);
        $this->Cell(20, 10, 'Total', 1, 0, 'C', true);
        $this->Cell(25, 10, utf8_decode('Código'), 1, 0, 'C', true);
        $this->Cell(20, 10, 'Tipo', 1, 0, 'C', true);
        $this->Cell(25, 10, 'Tipo Pago', 1, 0, 'C', true);
        $this->Cell(50, 10, utf8_decode('Exámenes'), 1, 0, 'C', true);
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
    $pdf->Cell(10, 10, $row['ID'], 1, 0, 'C');
    $pdf->Cell(40, 10, $row['fechacompro'], 1, 0, 'C');
    $pdf->Cell(20, 10, $row['total'], 1, 0, 'C');
    $pdf->Cell(25, 10, $row['Codigo'], 1, 0, 'C');
    $pdf->Cell(20, 10, $row['tipo'], 1, 0, 'C');
    $pdf->Cell(25, 10, $row['tipopag'], 1, 0, 'C');
    $pdf->Cell(50, 10, $row['examenes'], 1, 0, 'C');
    $pdf->Ln();
}

// Salida del PDF
$pdf->Output();
?>
