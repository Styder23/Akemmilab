<?php
require('../../fpdf184/fpdf.php');
require('../../phpqrcode/qrlib.php');
include '../../conexion/conn.php';

// Configuración de la conexión a la base de datos
mysqli_set_charset($con, 'utf8');

// Obtener datos de la base de datos
$idcomprobante = $_POST['idcomprobante']; // Puedes cambiar esto a un parámetro dinámico según tus necesidades
$query = "
SELECT idcomprobante as ID, d.Codigo as Cod, CONCAT_WS(' ', p.Nombre, p.Apellido) as Cliente, p.dni, pa.ruc, fechacompro as Fecha, precio, descuento,
subtotal, destotal,total, estadopg, tipopag, tipo, 
CASE 
    WHEN e.fk_perfil IS NULL THEN tipoexam 
    ELSE nomperfil 
END as Examen,
concat_ws(' ', p2.Nombre, p2.Apellido) as CAJA
FROM comprobante c
left JOIN caja ca on ca.idcaja=c.fk_idcaja
left JOIN usuario u ON u.idusuario=ca.fk_idusuario
left JOIN personas p2 ON p2.idpersonas=u.fk_idpersonas
INNER JOIN tipocomprobante tc ON tc.idtipocom=c.fk_tipocom
INNER JOIN detalle_venta d ON c.idcomprobante=d.fk_idcomprob
INNER JOIN tipopago pg ON pg.idtipopago=d.fk_tipopg
INNER JOIN estadopago eg ON eg.idestadopa=d.fk_estapago
INNER JOIN examen e ON e.idexamen=d.fk_idexamen
LEFT JOIN perfiles per on per.idperfil=e.fk_perfil
INNER JOIN tipoexamen te ON te.idtipoexamen=e.fk_idtipoexamen
INNER JOIN pacientes pa ON pa.idpacientes=e.fk_idpacientes
INNER JOIN personas p ON p.idpersonas=pa.fk_idpersonas
WHERE idcomprobante=$idcomprobante
AND (e.fk_perfil IS NULL OR (e.fk_perfil IS NOT NULL AND d.fk_idexamen IN (
    SELECT MAX(d2.fk_idexamen) 
    FROM detalle_venta d2 
    INNER JOIN examen e2 ON e2.idexamen = d2.fk_idexamen 
    WHERE e2.fk_perfil IS NOT NULL 
    GROUP BY e2.fk_perfil
)))";

$result = $con->query($query);

if (!$result) {
    echo 'Error en la consulta SQL: ' . $con->error;
    exit();
}

$data = $result->fetch_assoc();
if (!$data) {
    echo 'No se encontraron datos para el comprobante con ID: ' . $idcomprobante;
    exit();
}

class PDF extends FPDF {
    function Header() {
        global $data;
        
        // Ruta del logotipo
        $logo = '../../imagenes/LOGONEGRO.png';
        
        // Insertar el logotipo
        $this->Image($logo, 25, 5, 25);
        
        // Mover a la derecha
        $this->SetX(40);
        
        // Establecer la fuente para el nombre de la empresa
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(0, 6,utf8_decode(' '), 0, 1, 'C');
        
        // Establecer la fuente para los datos de la empresa
        $this->SetFont('Arial', '', 7);
        $this->Cell(0, 6, utf8_decode('RUC: 12345678901'), 0, 1, 'C');
        $this->MultiCell(0, 6, utf8_decode('Jr. José Mercedes Villanueva #1284 - esquina con Av. Ramón Castilla Soledad Baja'), 0, 'C');
        $this->Cell(0, 6, utf8_decode('Teléfono: 043 - 708096'), 0, 1, 'C');
        
        // Línea divisoria
        $this->Line(5, 42, 75, 42);
        $this->Ln(2);
    }

    function DatosCliente($data) {
        $this->SetFont('Arial', 'B', 7);
        $this->Cell(0, 6, utf8_decode('Cliente: ') . utf8_decode($data['Cliente']), 0, 1);
        
        $this->SetFont('Arial', '', 7);
        $this->Cell(0, 6, utf8_decode('DNI: ') . $data['dni'], 0, 1);
        $this->Cell(0, 6, utf8_decode('Fecha: ') . $data['Fecha'], 0, 1);
        $this->Cell(0, 6, utf8_decode('Tipo de Pago: ') . utf8_decode($data['tipopag']), 0, 1);
        $this->Cell(0, 6, utf8_decode('Tipo de Comprobante: ') . utf8_decode($data['tipo']), 0, 1);
        $this->Cell(0, 6, utf8_decode('Cajero: ') . utf8_decode($data['CAJA']), 0, 1);
        
        $this->Ln(2);
        $this->Cell(0, 0, '', 'T'); // Línea divisoria
        $this->Ln(2);
    }

    function TablaProductos($result) {
        $this->SetFont('Arial', 'B', 6);
        $this->Cell(5, 6, utf8_decode('Nº'), 0, 0, 'C');
        $this->Cell(25, 6, utf8_decode('Examen'), 0, 0, 'L');
        $this->Cell(10, 6, 'PREC', 0, 0, 'R');
        $this->Cell(10, 6, 'DESC.', 0, 0, 'R');
        $this->Cell(10, 6, 'S.TOTAL', 0, 1, 'R');
        $this->SetFont('Arial', '', 6);
    
        $index = 1;
        $totalDescuento = 0;
        $result->data_seek(0);
        while ($row = $result->fetch_assoc()) {
            $this->Cell(5, 6, $index, 0, 0, 'C');
            
            $x = $this->GetX();
            $y = $this->GetY();
            $this->MultiCell(25, 6, utf8_decode($row['Examen']), 0, 'L');
            $examenHeight = $this->GetY() - $y;
            
            $this->SetXY($x + 25, $y);
            $this->Cell(10, $examenHeight, number_format($row['precio'], 2), 0, 0, 'R');
            $this->Cell(10, $examenHeight, number_format($row['descuento'], 2), 0, 0, 'R');
            $this->Cell(10, $examenHeight, number_format($row['subtotal'], 2), 0, 1, 'R');
            
            $totalDescuento += $row['destotal'];
            $index++;
        }
        $this->Ln(2);
        $this->Cell(0, 0, '', 'T'); // Línea divisoria
        $this->Ln(2);
    
        return $totalDescuento;
    }

    function Totales($data, $totalDescuento) {
        $this->SetFont('Arial', 'B', 7);
        $subtotal = $data['total'] / 1.18;
        $igv = $data['total'] - $subtotal;
    
        $x = $this->GetX();
        $totalLength = $this->GetStringWidth('Total a Pagar: ');
    
        $this->Cell(30 - $totalLength, 6, '', 0, 0);
        $this->Cell($totalLength, 6, utf8_decode('Subtotal: '), 0, 0, 'R');
        $this->Cell(30, 6, number_format($subtotal, 2), 0, 1, 'R');
    
        $this->Cell(30 - $totalLength, 6, '', 0, 0);
        $this->Cell($totalLength, 6, utf8_decode('IGV (18%): '), 0, 0, 'R');
        $this->Cell(30, 6, number_format($igv, 2), 0, 1, 'R');
    
        $this->Cell(30 - $totalLength, 6, '', 0, 0);
        $this->Cell($totalLength, 6, utf8_decode('Desc.Total Aplicado: '), 0, 0, 'R');
        $this->Cell(30, 6, number_format($totalDescuento, 2), 0, 1, 'R');
    
        $this->Cell(30 - $totalLength, 6, '', 0, 0);
        $this->Cell($totalLength, 6, utf8_decode('Total a Pagar: '), 0, 0, 'R');
        $this->Cell(30, 6, number_format($data['total'], 2), 0, 1, 'R');
    
        $this->Ln(2);
    }

    function QRCode($data) {
        $text_qr = $data['Cliente'] . "\nTotal: " . number_format($data['total'], 2);
        $filename = tempnam(sys_get_temp_dir(), 'qr_') . '.png';
        QRcode::png($text_qr, $filename);
        $this->Image($filename, 30, null, 20, 20, 'PNG');
        unlink($filename);
    }

    function Footer() {
        $this->SetY(-20);
        $this->SetFont('Arial', 'I', 6);
        $this->Cell(0, 6, utf8_decode('Gracias por su compra'), 0, 1, 'C');
    }
}

$pdf = new PDF('P', 'mm', array(80, 210));
$pdf->AddPage();

$pdf->DatosCliente($data);

$totalDescuento = $pdf->TablaProductos($result);

$pdf->Totales($data, $totalDescuento);

$pdf->QRCode($data);

$pdf->Output();
?>