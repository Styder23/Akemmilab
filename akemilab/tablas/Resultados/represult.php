<?php

require('../../fpdf184/fpdf.php');
include('../../conexion/conn.php');
include('../../phpqrcode/qrlib.php'); // Asegúrate de que esta sea la ruta correcta a phpqrcode

class PDF extends FPDF {
    function Footer() {
        // Posición a 1.5 cm desde la parte inferior
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_decode('Página ' . $this->PageNo()), 0, 0, 'C');
        
        // Agregar la imagen de la firma
        $firma = '../../imagenes/firma.png'; // Ruta a la imagen de la firma
        $this->Image($firma, 10, -30, 60); // Ajusta la posición (X, Y) y el tamaño (ancho) según sea necesario
    }
}

if (isset($_POST['idexamen'])) {
    $idexa = $_POST['idexamen'];
    generarReporte($idexa, $con);
} else {
    echo "No se recibió el ID del examen.";
}

function generarReporte($idexa, $conexion) {
    $sql_datos_generales = "SELECT p.dni, p.codigo, CONCAT_WS(' ', p.Nombre, p.Apellido) AS paciente,
        CASE
            WHEN TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) >= 1 THEN CONCAT(TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()), ' años')
            WHEN TIMESTAMPDIFF(MONTH, p.fecha_nacimiento, CURDATE()) >= 1 THEN CONCAT(TIMESTAMPDIFF(MONTH, p.fecha_nacimiento, CURDATE()), ' meses')
            ELSE CONCAT(TIMESTAMPDIFF(DAY, p.fecha_nacimiento, CURDATE()), ' días')
        END AS edad,
        e.fecha, r.fecharesul, m.muestra,
        CONCAT_WS(' ', p2.Nombre, p2.Apellido) AS Analista,
        te.tipoexam AS Examen, 
        CONCAT_WS(' ', p3.Nombre, p3.Apellido) AS medico
        FROM resultados r
        INNER JOIN examen e ON e.idexamen = r.fk_idexamen
        INNER JOIN pacientes pa ON pa.idpacientes = e.fk_idpacientes
        INNER JOIN personas p ON p.idpersonas = pa.fk_idpersonas
        INNER JOIN tipoexamen te ON te.idtipoexamen = e.fk_idtipoexamen
        INNER JOIN medicos me ON me.idmedicos = e.fk_medico
        INNER JOIN personas p3 ON p3.idpersonas = me.fk_personas
        LEFT JOIN muestra m ON m.idmuestra = e.fk_muestra
        INNER JOIN usuario u ON u.idusuario = r.fk_idUsuario
        INNER JOIN personas p2 ON p2.idpersonas = u.fk_idpersonas
        WHERE r.fk_idexamen = '$idexa'
        LIMIT 1";

    $sql_resultados = "SELECT r.idresultados, 
       nm.nommedi, 
       r.valores, 
       u.unidades, 
       CASE 
           WHEN nm.rangomin IS NOT NULL AND nm.rangomin <> '' AND nm.rangomax IS NOT NULL AND nm.rangomax <> '' 
           THEN CONCAT(nm.rangomin, '-', nm.rangomax)
           WHEN nm.rangomin IS NOT NULL AND nm.rangomin <> '' 
           THEN nm.rangomin
           WHEN nm.rangomax IS NOT NULL AND nm.rangomax <> '' 
           THEN nm.rangomax
           ELSE ''
       END AS Valores, 
       a.nomtit, 
       r.medoto
FROM resultados r
INNER JOIN nommedible nm ON nm.idnommedible = r.fk_idnommedible
LEFT JOIN nombretit a ON a.idnombretit = nm.fk_idnombretit
LEFT JOIN unidades u ON u.idunidades = nm.fk_idunidades
WHERE r.fk_idexamen = '$idexa'
  AND r.valores IS NOT NULL 
  AND r.valores <> ''
ORDER BY a.idnombretit;";

    $result_datos_generales = mysqli_query($conexion, $sql_datos_generales);
    $row_datos_generales = mysqli_fetch_assoc($result_datos_generales);

    if (!$row_datos_generales) {
        echo "No se encontraron datos generales para el examen con ID: $idexa";
        return;
    }

    $result_resultados = mysqli_query($conexion, $sql_resultados);

    $pdf = new PDF();
    $pdf->AddPage();

    $logo = '../../imagenes/logo-PRINCIPAL.png';
    $pdf->Image($logo, 10, 10, 45);
    
    // Fecha en la esquina superior derecha
    $pdf->SetFont('Arial', 'I', 9); // Reducir tamaño de letra
    $pdf->SetXY(-50, 10);
    $pdf->Cell(0, 10, utf8_decode('Fecha: ') . date('d/m/Y'), 0, 1, 'R');
    
    // Título del informe debajo del logo y la fecha
    $pdf->Ln(20); // Ajustar el espacio después de la imagen y la fecha
    $pdf->SetTextColor(0, 102, 204);
    $pdf->SetFont('Arial', 'B', 14); // Reducir tamaño de letra
    $pdf->SetXY(10, 20); // Mover el título hacia abajo
    $pdf->MultiCell(190, 10, utf8_decode('Resultados de ' . $row_datos_generales['Examen']), 0, 'C');
    
    $pdf->Ln(5); // Reducir el espacio entre el título y los datos generales
    
    // Datos del Paciente
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetXY(10, 40); // Ajustar la posición inicial de los datos generales
    $pdf->Cell(15, 8, utf8_decode('Paciente: '), 0, 0, 'L');
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(65, 8, utf8_decode($row_datos_generales['paciente']), 0, 0, 'L');
    $pdf->SetFont('Arial', 'B', 9);
    
    // Ajuste del DNI
    $pdf->SetXY(90, 40); // Ajustar X aquí para mover más a la izquierda
    $pdf->Cell(10, 8, utf8_decode('DNI: '), 0, 0, 'R');
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(20, 8, utf8_decode($row_datos_generales['dni'] ? $row_datos_generales['dni'] : $row_datos_generales['codigo']), 0, 0, 'L');
    
    // Edad (sin cambios)
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(10, 8, utf8_decode('Edad: '), 0, 0, 'L');
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(20, 8, utf8_decode($row_datos_generales['edad']), 0, 0, 'L');
    
    // Muestra al lado de la Edad
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(15, 8, utf8_decode('Muestra: '), 0, 0, 'L');
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(0, 8, utf8_decode($row_datos_generales['muestra']), 0, 1, 'L');
    
    // Ajuste de Fecha de entrega
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetXY(10, 50); // Ajustar X aquí para mover más a la izquierda
    $pdf->Cell(30, 8, utf8_decode('Fecha de entrega: '), 0, 0, 'L');
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(40, 8, utf8_decode($row_datos_generales['fecha']), 0, 0, 'L');
    
    // Fecha de resultado (sin cambios)
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(35, 8, utf8_decode('Fecha de resultado: '), 0, 0, 'L');
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(0, 8, utf8_decode($row_datos_generales['fecharesul']), 0, 1, 'L');
    
    // Datos del Médico
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetXY(10, 58); // Ajustar X aquí para mover más a la izquierda
    $pdf->Cell(15, 8, utf8_decode('Médico: '), 0, 0, 'L');
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(0, 8, utf8_decode($row_datos_generales['medico']), 0, 1, 'L');
    
    $pdf->Ln(3); // Reducir el espacio entre los datos generales y los análisis
    
    $pdf->SetFont('Arial', 'B', 8); // Reducir tamaño de letra
    
    $current_analysis = '';

    $pdf->SetXY(60, 65); // Posición X e Y donde comenzarán los encabezados

    // Definir los encabezados una vez fuera del bucle
    $pdf->SetFont('Arial', 'B', 9); // Aumentar tamaño de letra de los encabezados
    $pdf->Cell(45, 8, utf8_decode('Valor'), 0, 0, 'L');
    $pdf->Cell(20, 8, utf8_decode('Unidades'), 0, 0, 'C');
    $pdf->Cell(30, 8, utf8_decode('Rangos normales'), 0, 0, 'C');
    $pdf->Cell(40, 8, utf8_decode('Método'), 0, 1, 'C');
    
    $pdf->SetFont('Arial', '', 9); // Reducir tamaño de letra después de los encabezados

    while ($row_resultados = mysqli_fetch_assoc($result_resultados)) {
        $yBefore = $pdf->GetY(); // Guardamos la posición Y antes de cualquier cosa
    
        if ($current_analysis != $row_resultados['nomtit']) {
            $current_analysis = $row_resultados['nomtit'];
            $nombre_analisis = $current_analysis ? utf8_decode($current_analysis) : 'Análisis no especificado';
    
            // Asegurarse de que no haya superposiciones con el siguiente nommedible
            if ($yBefore != $pdf->GetY()) {
                $pdf->SetY($yBefore + 8); // Añadir espacio si se ha movido
            }
    
            $pdf->SetFont('Arial', 'B', 9); // Negrita para el título del análisis
            $pdf->Cell(50, 8, utf8_decode('Análisis: ') . $nombre_analisis, 0, 1, 'L');
            $pdf->SetFont('Arial', '', 9); // Volver a tamaño normal después del título
        }
    
        // Celda para el nombre del medible (nommedible)
        $pdf->Cell(50, 8, utf8_decode($row_resultados['nommedi']), 0, 0, 'L');
    
        // Determinamos si hay datos en unidades, Valores, o método
        $hay_unidades = !empty($row_resultados['unidades']);
        $hay_valores_ref = !empty($row_resultados['Valores']);
        $hay_metodo = !empty($row_resultados['metodo']);
    
        $yStart = $pdf->GetY(); // Guardar la posición Y inicial
        $xStart = $pdf->GetX(); // Guardar la posición X inicial
        $altura_maxima = 0; // Para rastrear la altura máxima de la celda
    
        // Celda para valores, usando MultiCell si es necesario
        $valor_ancho = 45; // Ancho por defecto para la celda de valores
        $valor_extendido_ancho = $valor_ancho + 90; // Ancho extendido si no hay unidades, valores referenciales ni método
    
        if ($hay_unidades || $hay_valores_ref || $hay_metodo) {
            // Si hay unidades, valores referenciales o método, usamos el ancho normal
            $pdf->MultiCell($valor_ancho, 8, utf8_decode($row_resultados['valores']), 0, 'L');
        } else {
            // Si no hay unidades, valores referenciales ni método, usamos el ancho extendido
            $pdf->MultiCell($valor_extendido_ancho, 8, utf8_decode($row_resultados['valores']), 0, 'L');
        }
    
        // Obtener la altura máxima usada por la celda de valores
        $altura_valores = $pdf->GetY() - $yStart;
        $altura_maxima = max($altura_maxima, $altura_valores);
        
        // Ajustar la posición X para las siguientes celdas
        $pdf->SetXY($xStart + $valor_ancho, $yStart);
    
        // Celda para unidades (si existen)
        if ($hay_unidades) {
            $pdf->Cell(20, 8, utf8_decode($row_resultados['unidades']), 0, 0, 'C');
        } else {
            $pdf->Cell(20, 8, '', 0, 0, 'C');
        }
    
        // Celda para valores referenciales (si existen)
        if ($hay_valores_ref) {
            $pdf->Cell(30, 8, utf8_decode($row_resultados['Valores']), 0, 0, 'C');
        } else {
            $pdf->Cell(30, 8, '', 0, 0, 'C');
        }
    
        // Celda para el método (si existe)
        if ($hay_metodo) {
            $pdf->Cell(40, 8, utf8_decode($row_resultados['metodo']), 0, 1, 'C');
        } else {
            $pdf->Cell(40, 8, '', 0, 1, 'C');
        }
    
        // Ajustar la posición Y para el siguiente conjunto de celdas, asegurando no superponer
        $pdf->SetY($yStart + $altura_maxima);
    }
    
    
    // Datos del Analista
    $pdf->Ln(5); // Reducir el espacio antes de los datos del analista
    $pdf->SetFont('Arial', 'B', 8); // Reducir tamaño de letra
    $pdf->Cell(35, 8, utf8_decode('Analista Responsable: '), 0, 0, 'L'); // Ajustar el ancho de la celda
    $pdf->SetFont('Arial', '', 8); // Reducir tamaño de letra
    $pdf->Cell(0, 8, utf8_decode($row_datos_generales['Analista']), 0, 1, 'L'); // Usar la misma línea
    
    // Generar código QR
    $qr_content = 'Datos del Examen: ' . $idexa;
    $qr_temp_file = '../../imagenes/temp_qr.png';
    QRcode::png($qr_content, $qr_temp_file, QR_ECLEVEL_L, 3);
    $pdf->Image($qr_temp_file, 10, $pdf->GetY(), 25, 25);
    
    // Ruta de la imagen de la firma
    $firma = '../../imagenes/firma.jpeg';
    
    // Ajustar las coordenadas y dimensiones de la imagen
    $xPos = 135; // Coordenada X para mover a la derecha
    $altoImagen = 30; // Alto de la imagen
    $anchoImagen = 70; // Ancho de la imagen
    $yPos = -40; // Coordenada Y para colocar en la parte inferior (ajustar según sea necesario)
    
    // Función para agregar la firma en cada página
    function agregarFirma($pdf, $firma, $xPos, $yPos, $anchoImagen, $altoImagen) {
        $pdf->SetXY($xPos, $yPos);
        $pdf->Image($firma, $xPos, $pdf->GetPageHeight() - $altoImagen - 10, $anchoImagen, $altoImagen);
    }
    
    // Agregar la firma en la página actual
    agregarFirma($pdf, $firma, $xPos, $yPos, $anchoImagen, $altoImagen);
    
    $pdf->Output('I', 'Reporte_Examen_' . $idexa . '.pdf');
}    
?>