<?php
require('../fpdf184/fpdf.php');
include '../conexion/conexprueba.php';

$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d'); // Obtiene la fecha de la URL o usa la fecha actual

$sql = "SELECT 
            DATE(e.fechaentrega) AS Fecha, 
            concat_ws(' ', pe.nombres, pe.apellidos) AS Nombre_Paciente,
            t.nomexamen AS Examen_Realizado
        FROM 
            examen e
        INNER JOIN 
            tipoexamen t ON t.idtipoexamen = e.fk_tipoexamen
        INNER JOIN 
            pacientes p ON p.idpacientes = e.fk_paciente
        INNER JOIN 
            personas pe ON pe.idpersonas = p.fk_idpersona
        WHERE 
            DATE(e.fechaentrega) = ?
        ORDER BY 
            Fecha";

$stmt = $msqly->prepare($sql);
$stmt->bind_param("s", $fecha); // Vincula la fecha como parámetro
$stmt->execute();
$result = $stmt->get_result();

$pacientes = [];

while ($row = $result->fetch_assoc()) {
    $pacientes[$row['Nombre_Paciente']][] = $row['Examen_Realizado'];
}

class PDF extends FPDF
{
    // Cabecera de página
    function Header()
    {
        // Logo
        $this->Image('../imagenes/LOGONEGRO.png', 10, 8, 33); // Asegúrate de que la ruta y el tamaño del logo sean correctos
        // Arial bold 15
        $this->SetFont('Arial', 'B', 15);
        // Color celeste
        $this->SetTextColor(0, 123, 255);
        // Título
        $this->Cell(0, 10, 'Reporte de Pacientes Atendidos', 0, 1, 'C');
        // Restaurar color de texto a negro para el resto del documento
        $this->SetTextColor(0, 0, 0);
        // Salto de línea
        $this->Ln(10);
    }

    // Pie de página
    function Footer()
    {
        // Posición a 1,5 cm del final
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Número de página
        $this->Cell(0, 10, 'Página ' . $this->PageNo(), 0, 0, 'C');
    }

    // Tabla con ajuste de contenido y color de cabecera
    function BasicTable($header, $data, $totalPacientes)
    {
        // Cabecera
        $this->SetFillColor(173, 216, 230); // Color celeste
        foreach ($header as $col) {
            $this->Cell(95, 7, utf8_decode($col), 1, 0, 'C', true);
        }
        $this->Ln();
        // Datos
        foreach ($data as $row => $examenes) {
            $this->Cell(95, 6, utf8_decode($row), 1);
            $this->MultiCell(95, 6, utf8_decode(implode(', ', $examenes)), 1);
        }
        // Total de pacientes
        $this->Ln(10);
        $this->Cell(0, 10, 'Total de pacientes atendidos: ' . $totalPacientes, 0, 1, 'C');
    }
}

// Creación del PDF
$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// Títulos de las columnas
$header = array('Nombre del Paciente', 'Exámenes Realizados');

// Datos
$data = $pacientes;
$totalPacientes = count($pacientes);

// Fecha de visualización del reporte
$pdf->Cell(0, 10, utf8_decode('Reporte de pacientes atendidos el ') . $fecha, 0, 1, 'C');
$pdf->Cell(0, 5, 'Fecha: ' . date('Y-m-d'), 0, 1, 'L');
$pdf->Ln(10);

$pdf->BasicTable($header, $data, $totalPacientes);

$pdf->Output();

$msqly->close();
?>
