<?php
require('../fpdf184/fpdf.php');
include '../conexion/conn.php';

class PDF extends FPDF
{
    // Cabecera de página
    function Header()
    {
        // Logo
        $this->Image('../imagenes/logo-PRINCIPAL.png', 10, 6, 40); // Ajusta la ruta y tamaño del logo según sea necesario
        // Arial bold 17, color celeste
        $this->SetFont('Arial', 'B', 17);
        $this->SetTextColor(0, 123, 255); // Celeste
        // Título
        $this->Cell(0, 10, 'Reporte de Atenciones de Pacientes', 0, 1, 'C');
        // Restaurar color y fuente para la fecha de visualización
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Arial', 'I', 12);
        // Fecha de visualización alineada a la derecha
        $this->Cell(0, 10, 'Fecha: ' . date('Y-m-d'), 0, 1, 'R');
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
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    // Cargar datos
    function LoadData($startDate, $endDate)
    {
        global $con; // Asegurarse de que la conexión esté disponible globalmente
        $data = [];
        $sql = "SELECT codigo_atencion, fecha_atencion FROM atenciones WHERE fecha_atencion BETWEEN ? AND ?";
        $stmt = $con->prepare($sql);
        if ($stmt === false) {
            die('Prepare failed: ' . $con->error);
        }
        $stmt->bind_param('ss', $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    // Tabla con estilos
    function BasicTable($header, $data)
    {
        // Cabecera con estilo
        $this->SetFillColor(0, 123, 255); // Celeste
        $this->SetTextColor(255, 255, 255); // Blanco
        $this->SetDrawColor(0, 0, 0); // Negro
        $this->SetLineWidth(.3);
        $this->SetFont('', 'B');
        $w = array(90, 90); // Ancho de las columnas
        for ($i = 0; $i < count($header); $i++) {
            $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C', true);
        }
        $this->Ln();
        // Restaurar colores y fuentes para las filas
        $this->SetFillColor(224, 235, 255); // Color de relleno de las filas
        $this->SetTextColor(0);
        $this->SetFont('');
        $fill = false;
        foreach ($data as $row) {
            $this->Cell($w[0], 6, $row['codigo_atencion'], 'LR', 0, 'L', $fill);
            $this->Cell($w[1], 6, $row['fecha_atencion'], 'LR', 0, 'L', $fill);
            $this->Ln();
            $fill = !$fill; // Alternar color de relleno
        }
        // Línea de cierre de la tabla
        $this->Cell(array_sum($w), 0, '', 'T');
    }

    // Agregar número de pacientes atendidos
    function AddPatientCount($count)
    {
        $this->Ln(10);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Pacientes atendidos: ' . $count, 0, 1, 'L');
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];

    // Creación del objeto de la clase heredada
    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 12);

    // Títulos de las columnas
    $header = ['Codigo Atencion', 'Fecha Atencion'];

    // Carga de datos
    $data = $pdf->LoadData($startDate, $endDate);
    $pdf->AddPatientCount(count($data)); // Agregar número de pacientes atendidos
    $pdf->BasicTable($header, $data);

    // Salida del PDF
    $pdf->Output();
}
?>
