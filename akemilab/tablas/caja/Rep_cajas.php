<?php
require('../../fpdf184/fpdf.php');
require_once '../../conexion/conn.php';

// Configurar la conexión para usar UTF-8
mysqli_set_charset($con, 'utf8');

// Obtener el idcaja de los parámetros o establecerlo de alguna forma en tu aplicación
$idCaja = $_POST['idcaja']; // Ejemplo de cómo recibirías el idcaja, puedes ajustar según tu lógica

// Obtener información de la caja específica
$sqlInfoCaja = "SELECT c.idcaja, p.dni, CONCAT_WS(' ', p.Nombre, p.Apellido) as Usuario_Caja, c.hora_apertura, c.hora_cierre, c.montoini, c.importe
                FROM caja c 
                INNER JOIN usuario u ON u.idusuario=c.fk_idusuario
                INNER JOIN personas p ON p.idpersonas=u.fk_idpersonas
                WHERE c.idcaja = $idCaja";
$resultInfoCaja = $con->query($sqlInfoCaja);
$infoCaja = $resultInfoCaja->fetch_assoc();

// Consultar los movimientos de la caja específica
$sqlMovimientos = "SELECT fechacompro as fecha, CONCAT('Comprobante: ', idcomprobante) as detalle, 'Ingreso' as tipo, total as monto 
                   FROM comprobante 
                   WHERE fk_idcaja = $idCaja
                   UNION ALL
                   SELECT fecha, concepto as detalle, 'Ingreso' as tipo, monto as monto 
                   FROM igre_egre 
                   WHERE fk_idcaja = $idCaja AND fk_idtipmovi = 1
                   UNION ALL
                   SELECT fecha, concepto as detalle, 'Egreso' as tipo, monto as monto 
                   FROM igre_egre 
                   WHERE fk_idcaja = $idCaja AND fk_idtipmovi = 2
                   ORDER BY fecha";
$resultMovimientos = $con->query($sqlMovimientos);

$movimientos = [];
$totalIngresos = 0;
$totalEgresos = 0;
if ($resultMovimientos->num_rows > 0) {
    while ($rowMov = $resultMovimientos->fetch_assoc()) {
        $movimientos[] = $rowMov;
        if ($rowMov['tipo'] == 'Ingreso') {
            $totalIngresos += $rowMov['monto'];
        } else if ($rowMov['tipo'] == 'Egreso') {
            $totalEgresos += $rowMov['monto'];
        }
    }
}

$montoInicial = $infoCaja['montoini'];
$importeFinal = $montoInicial + $totalIngresos - $totalEgresos;

class PDF extends FPDF
{
    function Header()
    {
        // Logo
        $this->Image('../../imagenes/LOGONEGRO.png',10,8,33);
        // Arial bold 15
        $this->SetFont('Arial','B',15);
        // Mover a la derecha
        $this->Cell(80);
        // Título
        $this->Cell(30,10,'Movimientos de Caja',0,0,'C');
        // Salto de línea
        $this->Ln(20);
    }

    function Footer()
    {
        // Posición a 1.5 cm del final
        $this->SetY(-15);
        // Arial itálica 8
        $this->SetFont('Arial','I',8);
        // Número de página
        $this->Cell(0,10,'Página '.$this->PageNo().'/{nb}',0,0,'C');
    }

    function BasicTable($header, $data)
    {
        // Encabezado
        $this->SetFillColor(200,220,255);
        $this->SetDrawColor(50,50,100);
        $this->SetFont('Arial','B',12);
        $this->Cell(45,7,$header[0],1,0,'C',true);
        $this->Cell(55,7,$header[1],1,0,'C',true);
        $this->Cell(45,7,$header[2],1,0,'C',true);
        $this->Cell(45,7,$header[3],1,0,'C',true);
        $this->Ln();
        
        // Datos
        $this->SetFont('Arial','',10);
        foreach($data as $row)
        {
            $this->Cell(45,6,utf8_decode($row['fecha']),1,0,'C');
            $this->Cell(55,6,utf8_decode($row['detalle']),1,0,'C');
            $this->Cell(45,6,utf8_decode($row['tipo']),1,0,'C');
            $this->Cell(45,6,number_format($row['monto'], 2),1,0,'R');
            $this->Ln();
        }
    }

    function CajaInfo($info)
    {
        $this->SetFont('Arial','B',12);
        $this->Cell(0,10,'Informacion de la Caja',0,1,'C');
        $this->SetFont('Arial','',10);
        $this->Cell(0,10,'ID de Caja: '.utf8_decode($info['idcaja']),0,1,'L');
        $this->Cell(0,10,'Usuario: '.utf8_decode($info['Usuario_Caja']).' (DNI: '.utf8_decode($info['dni']).')',0,1,'L');
        $this->Ln(10);
    }

    function ImporteFinal($montoInicial, $totalIngresos, $totalEgresos, $importeFinal)
    {
        $this->SetFont('Arial','B',12);
        $this->Cell(0,10,'Resumen de Importes',0,1,'C');
        $this->SetFont('Arial','',10);
        $this->Cell(0,10,'Monto Inicial: '.number_format($montoInicial, 2),0,1,'L');
        $this->Cell(0,10,'Total Ingresos (Ventas + Ingresos): '.number_format($totalIngresos, 2),0,1,'L');
        $this->Cell(0,10,'Total Egresos: '.number_format($totalEgresos, 2),0,1,'L');
        $this->Cell(0,10,'Importe Final en Caja: '.number_format($importeFinal, 2),0,1,'L');
    }
}

// Instanciación de la clase heredada
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','',12);

// Información de la caja
$pdf->CajaInfo($infoCaja);

// Movimientos
$pdf->Cell(0,10,'Movimientos de Caja',0,1,'C');
$pdf->SetX((210 - 190) / 2); // Centrar la tabla
$header = ['Fecha', 'Detalle', 'Tipo', 'Monto'];
$pdf->BasicTable($header, $movimientos);

// Resumen de importes
$pdf->Ln(10);
$pdf->ImporteFinal($montoInicial, $totalIngresos, $totalEgresos, $importeFinal);

$pdf->Output();

?>
