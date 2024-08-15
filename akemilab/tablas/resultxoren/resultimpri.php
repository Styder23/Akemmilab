<?php
require('../../fpdf184/fpdf.php');
include('../../conexion/conn.php');
include('../../phpqrcode/qrlib.php');

class PDF extends FPDF {
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_decode('Página ' . $this->PageNo()), 0, 0, 'C');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['codorden'])) {
        $codorden = $_POST['codorden'];
        generarReporte($codorden, $con);
    } else {
        echo "No se recibió el código de orden.";
    }
}

function generarReporte($codorden, $conexion) {
    // Obtener datos generales del examen
    $sql_datos_generales = "SELECT o.idorden AS ID, o.codorden, o.fecha, pe.dni, pe.codigo,
        CONCAT_WS(' ', pe.Nombre, pe.Apellido) AS paciente,
        g.genero,
        CASE
            WHEN TIMESTAMPDIFF(YEAR, pe.fecha_nacimiento, CURDATE()) >= 1 THEN CONCAT(TIMESTAMPDIFF(YEAR, pe.fecha_nacimiento, CURDATE()), ' años')
            WHEN TIMESTAMPDIFF(MONTH, pe.fecha_nacimiento, CURDATE()) >= 1 THEN CONCAT(TIMESTAMPDIFF(MONTH, pe.fecha_nacimiento, CURDATE()), ' meses')
            ELSE CONCAT(TIMESTAMPDIFF(DAY, pe.fecha_nacimiento, CURDATE()), ' días')
        END AS edad,
        CONCAT_WS(' ', p2.Nombre, p2.Apellido) AS medico,
        tipoexam AS EXAMEN, muestra AS muestra, e.fecha AS fecha_exam, r.fecharesul AS fecha_resul, nm.nommedi, r.valores, u.unidades,
        CONCAT_WS('-', nm.rangomin, nm.rangomax) AS Valores, a.nomtit, r.medoto,
        CONCAT_WS(' ', p3.Nombre, p3.Apellido) AS Analista
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
        LEFT JOIN resultados r ON e.idexamen = r.fk_idexamen
        JOIN usuario us ON us.idusuario = r.fk_idUsuario
        JOIN personas p3 ON p3.idpersonas = us.fk_idpersonas
        INNER JOIN nommedible nm ON nm.idnommedible = r.fk_idnommedible
        LEFT JOIN nombretit a ON a.idnombretit = nm.fk_idnombretit
        LEFT JOIN unidades u ON u.idunidades = nm.fk_idunidades
        WHERE o.codorden = '$codorden'";

    $result_datos_generales = mysqli_query($conexion, $sql_datos_generales);
    $row_datos_generales = mysqli_fetch_assoc($result_datos_generales);

    if (!$row_datos_generales) {
        echo "No se encontraron datos para el código de orden: $codorden";
        return;
    }

    $pdf = new PDF();
    $pdf->AddPage();

    $logo = '../../imagenes/logo-PRINCIPAL.png';
    $pdf->Image($logo, 10, 10, 45);
    
    $pdf->SetFont('Arial', 'I', 9);
    $pdf->SetXY(-50, 10);
    $pdf->Cell(0, 10, utf8_decode('Fecha: ') . date('d/m/Y'), 0, 1, 'R');
    
    $pdf->Ln(20);
    $pdf->SetTextColor(0, 102, 204);
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetXY(10, 20);
    $pdf->MultiCell(190, 10, utf8_decode('Resultados de los Análisis'), 0, 'C');
    
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetTextColor(0, 0, 0);

    $pdf->SetXY(10, 40);
    $pdf->Cell(15, 8, utf8_decode('Paciente: '), 0, 0, 'L');
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(65, 8, utf8_decode($row_datos_generales['paciente']), 0, 0, 'L');
    $pdf->SetFont('Arial', 'B', 9);
    
    $pdf->SetXY(90, 40);
    $pdf->Cell(10, 8, utf8_decode('DNI: '), 0, 0, 'R');
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(20, 8, utf8_decode($row_datos_generales['dni'] ? $row_datos_generales['dni'] : $row_datos_generales['codigo']), 0, 0, 'L');
    
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(10, 8, utf8_decode('Edad: '), 0, 0, 'L');
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(20, 8, utf8_decode($row_datos_generales['edad']), 0, 0, 'L');
    
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(15, 8, utf8_decode('Muestra: '), 0, 0, 'L');
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(0, 8, utf8_decode($row_datos_generales['muestra']), 0, 1, 'L');
    
    // Información en la misma línea
	$pdf->SetFont('Arial', 'B', 9);
	$pdf->SetXY(10, 50); // Ajusta la posición vertical según tu diseño

	$pdf->Cell(15, 8, utf8_decode('Género: '), 0, 0, 'L');
	$pdf->SetFont('Arial', '', 9);
	$pdf->Cell(25, 8, utf8_decode($row_datos_generales['genero']), 0, 0, 'L');

	$pdf->SetFont('Arial', 'B', 9);
	$pdf->Cell(30, 8, utf8_decode('Fecha de Examen: '), 0, 0, 'L');
	$pdf->SetFont('Arial', '', 9);
	$pdf->Cell(40, 8, utf8_decode($row_datos_generales['fecha_exam']), 0, 0, 'L');

	$pdf->SetFont('Arial', 'B', 9);
	$pdf->Cell(35, 8, utf8_decode('Fecha de Resultado: '), 0, 0, 'L');
	$pdf->SetFont('Arial', '', 9);
	$pdf->Cell(40, 8, utf8_decode($row_datos_generales['fecha_resul']), 0, 1, 'L');
    
    $pdf->Ln(10); // Mover a la siguiente línea después de las fechas
    
    // Nombre del Médico
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetXY(10, 60);
    $pdf->Cell(0, 8, utf8_decode('Médico: ' . $row_datos_generales['medico']), 0, 1, 'L');

    // Consultar los resultados
    $sql_resultados = "SELECT 
                        nm.nommedi AS Nombre_Medicion, 
                        r.valores AS Valor, 
                        u.unidades AS Unidades, 
                        CONCAT_WS('-', nm.rangomin, nm.rangomax) AS Valores_Referencia, 
                        r.medoto AS Metodo, 
                        a.nomtit AS Nombre_Analisis,
                        t.tipoexam AS Nombre_Examen
                    FROM 
                        resultados r
                    INNER JOIN 
                        nommedible nm ON nm.idnommedible = r.fk_idnommedible
                    LEFT JOIN 
                        nombretit a ON a.idnombretit = nm.fk_idnombretit
                    LEFT JOIN 
                        unidades u ON u.idunidades = nm.fk_idunidades
                    JOIN 
                        examen e ON e.idexamen = r.fk_idexamen
                    JOIN 
                        tipoexamen t ON t.idtipoexamen = e.fk_idtipoexamen
                    WHERE 
                        e.idexamen IN (SELECT e.idexamen FROM examen e JOIN detalle_orden d ON e.idexamen = d.fk_exam WHERE d.fk_orde = (SELECT idorden FROM ordenclinico WHERE codorden = '$codorden'))";

    $result_resultados = mysqli_query($conexion, $sql_resultados);

    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetFillColor(220, 220, 220);
    $pdf->Cell(70, 8, utf8_decode('Nombre del Examen'), 0, 0, 'C', true);
    $pdf->Cell(30, 8, utf8_decode('Valor'), 0, 0, 'C', true);
    $pdf->Cell(15, 8, utf8_decode('Unidades'), 0, 0, 'C', true);
    $pdf->Cell(35, 8, utf8_decode('Valores de Referencia'), 0, 0, 'C', true);
    $pdf->Cell(40, 8, utf8_decode('Método'), 0, 1, 'C', true);

    $pdf->SetFont('Arial', '', 9);

    $current_exam = '';
    while ($row_resultados = mysqli_fetch_assoc($result_resultados)) {
        if ($current_exam != $row_resultados['Nombre_Examen']) {
            $current_exam = $row_resultados['Nombre_Examen'];
            
            // Si el nombre del análisis (nomtit) está vacío, imprimir algo genérico
            $nombre_analisis = $row_resultados['Nombre_Analisis'] ? utf8_decode($row_resultados['Nombre_Analisis']) : 'Análisis no especificado';
            
            // Escribir el título del examen
            $pdf->Ln(10);
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(0, 8, utf8_decode('Examen: ') . $current_exam, 0, 1, 'L');
            $pdf->SetFont('Arial', '', 9);
        }
        
        // Imprimir los datos de cada resultado
        $pdf->Cell(70, 8, utf8_decode($row_resultados['Nombre_Medicion']), 0, 0, 'L');
        $pdf->Cell(30, 8, utf8_decode($row_resultados['Valor']), 0, 0, 'C');
        $pdf->Cell(15, 8, utf8_decode($row_resultados['Unidades']), 0, 0, 'C');
        $pdf->Cell(35, 8, utf8_decode($row_resultados['Valores_Referencia']), 0, 0, 'C');
        $pdf->Cell(40, 8, utf8_decode($row_resultados['Metodo']), 0, 1, 'C');
    }
    
    // Datos del Analista
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(35, 8, utf8_decode('Analista Responsable: '), 0, 0, 'L');
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(0, 8, utf8_decode($row_datos_generales['Analista']), 0, 1, 'L');

    // Generar código QR
    $qr_content = 'Datos del Examen: ' . $codorden;
    $qr_temp_file = '../../imagenes/temp_qr.png';
    QRcode::png($qr_content, $qr_temp_file, QR_ECLEVEL_L, 3);
    $pdf->Image($qr_temp_file, 10, $pdf->GetY(), 25, 25);
    
    // Ruta de la imagen de la firma
    $firma = '../../imagenes/firma.jpeg';
    
    // Ajustar las coordenadas y dimensiones de la imagen
    $xPos = 135;
    $altoImagen = 30;
    $anchoImagen = 70;
    $yPos = $pdf->GetPageHeight() - $altoImagen - 10;
    
    // Función para agregar la firma en cada página
    function agregarFirma($pdf, $firma, $xPos, $yPos, $anchoImagen, $altoImagen) {
        $pdf->Image($firma, $xPos, $yPos, $anchoImagen, $altoImagen);
    }
    
    // Agregar la firma en la página actual
    agregarFirma($pdf, $firma, $xPos, $yPos, $anchoImagen, $altoImagen);
    
    $pdf->Output('I', 'Resultados_Análisis_' . $codorden . '.pdf');
}
?>

