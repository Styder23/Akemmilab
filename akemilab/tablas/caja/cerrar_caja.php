<?php
date_default_timezone_set('America/Lima');
require_once '../../conexion/conn.php';

// Verificar si hay una caja abierta
$sql = "SELECT idcaja, hora_apertura, montoini FROM caja WHERE hora_cierre IS NULL ORDER BY hora_apertura DESC LIMIT 1";
$result = $con->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $idCajaAbierta = $row['idcaja']; 

    // Calcular el importe total de las ventas
    $sqlImporte = "SELECT SUM(total) as total_ventas FROM comprobante WHERE fk_idcaja = $idCajaAbierta";
    $resultImporte = $con->query($sqlImporte);
    $totalVentas = 0;
    if ($resultImporte->num_rows > 0) {
        $rowImporte = $resultImporte->fetch_assoc();
        $totalVentas = $rowImporte['total_ventas'];
    }

    // Calcular el importe total de los movimientos (ingresos y egresos)
    $sqlIngresos = "SELECT SUM(monto) as total_ingresos FROM igre_egre WHERE fk_idcaja = $idCajaAbierta AND fk_idtipmovi = 1"; // 1 para ingresos
    $resultIngresos = $con->query($sqlIngresos);
    $totalIngresos = 0;
    if ($resultIngresos->num_rows > 0) {
        $rowIngresos = $resultIngresos->fetch_assoc();
        $totalIngresos = $rowIngresos['total_ingresos'];
    }

    $sqlEgresos = "SELECT SUM(monto) as total_egresos FROM igre_egre WHERE fk_idcaja = $idCajaAbierta AND fk_idtipmovi = 2"; // 2 para egresos
    $resultEgresos = $con->query($sqlEgresos);
    $totalEgresos = 0;
    if ($resultEgresos->num_rows > 0) {
        $rowEgresos = $resultEgresos->fetch_assoc();
        $totalEgresos = $rowEgresos['total_egresos'];
    }

    // Calcular el importe total sumando ventas, ingresos y restando egresos
    $importeTotal = $row['montoini'] + $totalVentas + $totalIngresos - $totalEgresos;

    // Imprimir valores para depuración
    echo "Monto Inicial: " . $row['montoini'] . "<br>";
    echo "Total Ventas: " . $totalVentas . "<br>";
    echo "Total Ingresos: " . $totalIngresos . "<br>";
    echo "Total Egresos: " . $totalEgresos . "<br>";
    echo "Importe Total: " . $importeTotal . "<br>";

    // Actualizar el registro de la caja abierta con la hora de cierre y el importe total
    $horaCierre = date('Y-m-d H:i:s');
    $sqlCerrarCaja = "UPDATE caja SET hora_cierre = '$horaCierre', importe = $importeTotal,fk_idestado=2 WHERE idcaja = $idCajaAbierta";

    if ($con->query($sqlCerrarCaja) === TRUE) {
        header('Location: caja.php?status=success');
    } else {
        echo "Error al cerrar la caja: " . $con->error;
    }
} else {
    echo "No hay ninguna caja abierta actualmente.";
}

$con->close();
?>
