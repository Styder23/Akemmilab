<?php
require('../../fpdf184/fpdf.php');
include '../../conexion/conn.php';

// Obtener el idorden desde el parámetro GET
$idorden = isset($_GET['idorden']) ? intval($_GET['idorden']) : 0;

// Consulta SQL
$sql = "SELECT idorden as ID, codorden, o.fecha, pe.dni, 
               concat_ws(' ', pe.Nombre, pe.Apellido) as Paciente, g.genero,
               CASE
                   WHEN TIMESTAMPDIFF(YEAR, pe.fecha_nacimiento, CURDATE()) >= 1 THEN CONCAT(TIMESTAMPDIFF(YEAR, pe.fecha_nacimiento, CURDATE()), ' años')
                   WHEN TIMESTAMPDIFF(MONTH, pe.fecha_nacimiento, CURDATE()) >= 1 THEN CONCAT(TIMESTAMPDIFF(MONTH, pe.fecha_nacimiento, CURDATE()), ' meses')
                   ELSE CONCAT(TIMESTAMPDIFF(DAY, pe.fecha_nacimiento, CURDATE()), ' días')
               END AS edad,
               concat_ws(' ', p2.Nombre, p2.Apellido) as Medico, tipoexam as EXAMEN, muestra
        FROM ordenclinico o 
        JOIN detalle_orden d ON o.idorden = d.fk_orde
        JOIN examen e ON e.idexamen = d.fk_exam
        JOIN tipoexamen t ON t.idtipoexamen = e.fk_idtipoexamen
        JOIN muestra m ON m.idmuestra = e.fk_muestra
        JOIN pacientes p ON p.idpacientes = e.fk_idpacientes
        JOIN personas pe ON pe.idpersonas = p.fk_idpersonas
        JOIN genero g ON g.idgenero = pe.fk_idgenero
        JOIN medicos me ON me.idmedicos = e.fk_medico
        JOIN personas p2 ON p2.idpersonas = me.fk_personas
        LEFT JOIN perfiles pf ON pf.idperfil = e.fk_perfil
        WHERE idorden = $idorden";

$result = $con->query($sql);

// Verificar si hay resultados
if ($result->num_rows > 0) {
    $ordenData = $result->fetch_assoc();
} else {
    die('No se encontraron datos para la orden especificada.');
}

class PDF extends FPDF {
    function Header() {
        // Logo
        $this->Image('../../imagenes/logo-PRINCIPAL.png', 10, 6, 40);
        // Título
        $this->SetFont('Arial', 'B', 15);
        $this->SetTextColor(0, 123, 255); // Color celeste
        $this->Cell(0, 10, utf8_decode('Orden Clínico'), 0, 1, 'C');
        // Salto de línea
        $this->Ln(10);
    }

    function Footer() {
        // Posición a 1.5 cm del final
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        // Número de página
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo(), 0, 0, 'C');
    }
}

// Creación del PDF
$pdf = new PDF();
$pdf->AddPage();

// Datos generales
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(0, 0, 0); // Color negro

$pdf->Cell(22, 10, utf8_decode('N° Orden:'), 0, 0, 'L');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, $ordenData['codorden'], 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(18, 10, 'Fecha:', 0, 0, 'L');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, $ordenData['fecha'], 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(25, 10, 'Paciente:', 0, 0, 'L');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(60, 10, utf8_decode($ordenData['Paciente']), 0, 0, 'R');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(20, 10, 'DNI:', 0, 0, 'R');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(30, 10, $ordenData['dni'], 0, 0, 'L');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(15, 10, 'Edad:', 0, 0, 'L');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, utf8_decode($ordenData['edad']), 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(20, 10, 'Genero:', 0, 0, 'L');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, $ordenData['genero'], 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(20, 10, utf8_decode('Médico:'), 0, 0, 'L');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, utf8_decode($ordenData['Medico']), 0, 1, 'L');

// Salto de línea
$pdf->Ln(10);

// Título de los exámenes
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(0, 123, 255); // Color celeste
$pdf->Cell(0, 10, 'Examenes y Muestras', 0, 1, 'L');

// Detalle de los exámenes
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(0, 0, 0); // Color negro

$pdf->SetFillColor(200, 220, 255); // Encabezado celeste
$pdf->Cell(115, 10, 'Examen', 0, 0, 'C', true);
$pdf->Cell(75, 10, 'Muestra', 0, 1, 'C', true);

do {
    $pdf->Cell(115, 10, $ordenData['EXAMEN'], 0, 0, 'L');
    $pdf->Cell(75, 10, $ordenData['muestra'], 0, 1, 'C');
} while ($ordenData = $result->fetch_assoc());

// Cerrar conexión
$con->close();

// Salida del PDF
$pdf->Output();
?>
