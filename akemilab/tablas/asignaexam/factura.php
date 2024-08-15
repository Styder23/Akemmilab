<?php
require('../../fpdf184/fpdf.php');
require('../../phpqrcode/qrlib.php');
include '../../conexion/conn.php';

// Configuración de la conexión a la base de datos
mysqli_set_charset($con, 'utf8');

// Verifica si el parámetro idcomprobante está presente en la solicitud GET
if (!isset($_GET['idcomprobante'])) {
    echo 'Error: idcomprobante no proporcionado en la URL.';
    exit();
}

$idcomprobante = $_GET['idcomprobante'];

$query = "
SELECT idcomprobante as ID, d.Codigo as Cod, CONCAT_WS(' ', p.Nombre, p.Apellido) as Cliente, p.dni, pa.ruc,pa.razon_social,
fechacompro as Fecha, precio, descuento,
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
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(0, 8, utf8_decode('AkemiLab'), 0, 1, 'C');
        $this->SetFont('Arial', '', 7);
        $this->Cell(0, 6, utf8_decode('RUC: 12345678901'), 0, 1, 'C');
        $this->MultiCell(0, 6, utf8_decode('Jr. José Mercedes Villanueva #1284-esquina co la Av. Ramón Castilla Soledad Baja'), 0, 'C');
        $this->Cell(0, 6, utf8_decode('Teléfono: 043 - 708096'), 0, 1, 'C');
        $this->Line(5, 42, 75, 42); // Ajustar la línea
        $this->Ln(2);
    }

    function DatosCliente($data) {
        $this->SetFont('Arial', 'B', 7);
        $this->Cell(0, 6, utf8_decode('Cliente: ') . utf8_decode($data['Cliente']), 0, 1);
        
        $this->SetFont('Arial', '', 7);
        $this->Cell(0, 6, utf8_decode('DNI: ') . $data['dni'], 0, 1);
        $this->Cell(0, 6, utf8_decode('RUC: ') . $data['ruc'], 0, 1);
        $this->Cell(0, 6, utf8_decode('Razón Social: ') . $data['razon_social'], 0, 1);
        $this->Cell(0, 6, utf8_decode('Fecha: ') . $data['Fecha'], 0, 1);
        $this->Cell(0, 6, utf8_decode('Tipo de Pago: ') . utf8_decode($data['tipopag']), 0, 1);
        $this->Cell(0, 6, utf8_decode('Tipo de Comprobante: ') . utf8_decode($data['tipo']), 0, 1);
        $this->Cell(0, 6, utf8_decode('Cajero: ') . utf8_decode($data['CAJA']), 0, 1);
        
        $this->Ln(2);
        $this->Line(5, $this->GetY(), 75, $this->GetY()); // Ajustar la línea
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
        $result->data_seek(0); // Reset the result pointer to the beginning
        while ($row = $result->fetch_assoc()) {
            $this->Cell(5, 6, $index, 0, 0, 'C');
            
            // Almacenar la posición X inicial
            $x = $this->GetX();
            $y = $this->GetY();
            
            // Obtener la altura de la celda que contiene el nombre del examen
            $this->MultiCell(25, 6, utf8_decode($row['Examen']), 0, 'L');
            
            // Recuperar la altura de la última celda
            $examenHeight = $this->GetY() - $y;
            
            // Ajustar el posicionamiento del resto de celdas
            $this->SetXY($x + 25, $y);
            $this->Cell(10, $examenHeight, number_format($row['precio'], 2), 0, 0, 'R');
            $this->Cell(10, $examenHeight, number_format($row['descuento'], 2), 0, 0, 'R');
            $this->Cell(10, $examenHeight, number_format($row['subtotal'], 2), 0, 1, 'R');
            
            $totalDescuento = $row['destotal'];
            $index++;
        }
        $this->Ln(2);
        $this->Line(5, $this->GetY() + 3, 75, $this->GetY() + 3); // Ajustar la línea
        $this->Ln(2);
    
        return $totalDescuento;
    }
    
    function Totales($data, $totalDescuento) {
        $this->SetFont('Arial', 'B', 7);
        $subtotal = $data['total'] / 1.18;
        $igv = $data['total'] - $subtotal;
    
        // Guardar la posición actual X
        $x = $this->GetX();
    
        // Calcular la longitud del texto más largo para alinear a la derecha
        $totalLength = $this->GetStringWidth('Total a Pagar: ');
    
        // Alinear Subtotal
        $this->Cell(30 - $totalLength, 6, '', 0, 0); // Espacio para alinear
        $this->Cell($totalLength, 6, utf8_decode('Subtotal: '), 0, 0, 'R');
        $this->Cell(30, 6, number_format($subtotal, 2), 0, 1, 'R');
    
        // Alinear IGV
        $this->Cell(30 - $totalLength, 6, '', 0, 0); // Espacio para alinear
        $this->Cell($totalLength, 6, utf8_decode('IGV (18%): '), 0, 0, 'R');
        $this->Cell(30, 6, number_format($igv, 2), 0, 1, 'R');
    
        // Alinear Descuento
        $this->Cell(30 - $totalLength, 6, '', 0, 0); // Espacio para alinear
        $this->Cell($totalLength, 6, utf8_decode('Desc.tot Aplicado: '), 0, 0, 'R');
        $this->Cell(30, 6, number_format($totalDescuento, 2), 0, 1, 'R');
    
        // Alinear Total a Pagar
        $this->Cell(30 - $totalLength, 6, '', 0, 0); // Espacio para alinear
        $this->Cell($totalLength, 6, utf8_decode('Total a Pagar: '), 0, 0, 'R');
        $this->Cell(30, 6, number_format($data['total'], 2), 0, 1, 'R');
    
        $this->Ln(2);
        $this->Line(5, $this->GetY(), 75, $this->GetY()); // Ajustar la línea
        $this->Ln(2);
    }

    function QRCode($data) {
        $this->Ln(2);
        $this->SetFont('Arial', 'I', 7);
        $this->Cell(0, 6, utf8_decode('Gracias por su compra'), 0, 1, 'C');
        $this->Ln(2);
        $codeText = "ID: " . $data['ID'] . "\nCliente: " . utf8_decode($data['Cliente']) . "\nTotal: " . number_format($data['total'], 2);
        $fileName = tempnam(sys_get_temp_dir(), 'qr') . '.png';
        QRcode::png($codeText, $fileName, QR_ECLEVEL_L, 3);
        $this->Image($fileName, $this->GetX() + 20, $this->GetY(), 20, 0, 'PNG');
        unlink($fileName);
    }
}

$pdf = new PDF('P', 'mm', array(80, 200)); // Ajustar anchura y altura según sea necesario para caber en una página más pequeña
$pdf->AddPage();
$pdf->DatosCliente($data);
$totalDescuento = $pdf->TablaProductos($result);
$pdf->Totales($data, $totalDescuento);
$pdf->QRCode($data);
$pdf->Output();
?>


