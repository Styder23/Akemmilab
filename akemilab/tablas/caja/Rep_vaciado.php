<?php
require('../../fpdf184/fpdf.php');
require_once '../../conexion/conn.php';

class PDF extends FPDF
{
    // Cabecera de página
    function Header()
    {
        // Logo
        $this->Image('../../imagenes/logo-PRINCIPAL.png', 10, 10, 45); // Ajusta la ruta y el tamaño del logo
        // Arial bold 15
        $this->SetFont('Arial', 'B', 15);
        // Título
        $this->SetTextColor(0, 51, 102); // Color del título (Azul oscuro)
        $this->Cell(0, 10, 'Reporte de Vaciados de Caja', 0, 1, 'C');
        // Fecha de visualización
        $this->SetFont('Arial', '', 12);
        $this->SetTextColor(0, 0, 0); // Color negro
        $this->Cell(0, 10, utf8_decode('Fecha: ') . date('d/m/Y'), 0, 1, 'C');
        // Salto de línea
        $this->Ln(10);
    }

    // Pie de página
    function Footer()
    {
        // Posición: a 1,5 cm del final
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Número de página
        $this->Cell(0, 10, 'Página ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    // Tabla simple
    function BasicTable($header, $data)
    {
        // Anchuras de las columnas
        $w = array(40, 40, 60, 40);
        $totalWidth = array_sum($w);
        $this->SetX(($this->w - $totalWidth) / 2); // Centrando la tabla

        // Colores, ancho de línea y fuente en negrita
        $this->SetFillColor(0, 51, 102);
        $this->SetTextColor(255);
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(.3);
        $this->SetFont('Arial', 'B');
        // Cabecera
        for ($i = 0; $i < count($header); $i++) {
            $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C', true);
        }
        $this->Ln();
        // Restauración de colores y fuentes
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetFont('Arial');
        // Datos
        $fill = false;
        foreach ($data as $row) {
            $this->SetX(($this->w - $totalWidth) / 2); // Centrando la tabla
            $this->Cell($w[0], 6, $row['idcaja'], 'LR', 0, 'C', $fill);
            $this->Cell($w[1], 6, number_format($row['monto_vaciado'], 2), 'LR', 0, 'R', $fill);
            $this->Cell($w[2], 6, $row['fecha_vaciado'], 'LR', 0, 'C', $fill);
            $this->Cell($w[3], 6, utf8_decode($row['usuario_vaciado']), 'LR', 0, 'C', $fill);
            $this->Ln();
            $fill = !$fill;
        }
        // Línea de cierre
        $this->SetX(($this->w - $totalWidth) / 2); // Centrando la tabla
        $this->Cell($totalWidth, 0, '', 'T');
    }
}

// Obtener las fechas de inicio y fin desde el formulario
$fechaInicio = $_POST['fecha_inicio'] . " 00:00:00";
$fechaFin = $_POST['fecha_fin'] . " 23:59:59";

// Consulta para obtener los vaciados de caja entre las fechas especificadas
$sql = "SELECT idcaja, monto_vaciado, fecha_vaciado, usuario_vaciado
        FROM vaciado_caja
        WHERE fecha_vaciado BETWEEN ? AND ?
        ORDER BY fecha_vaciado DESC";

// Preparar la consulta
$stmt = $con->prepare($sql);
$stmt->bind_param("ss", $fechaInicio, $fechaFin);

// Ejecutar la consulta
$stmt->execute();
$result = $stmt->get_result();

// Recoger los datos
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Cerrar la conexión
$stmt->close();
$con->close();

// Crear el PDF
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// Títulos de las columnas
$header = ['ID Caja', 'Monto Vaciado', 'Fecha Vaciado', 'Usuario Vaciado'];
$pdf->BasicTable($header, $data);

// Generar y descargar el PDF
$pdf->Output();
?>