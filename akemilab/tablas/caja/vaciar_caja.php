<?php
session_start();
require_once '../../conexion/conn.php';

// Verifica si el usuario está autenticado
if (!isset($_SESSION['idusuario'])) {
    echo json_encode(['status' => 'false', 'message' => 'Usuario no autenticado']);
    exit();
}

// Obtén el ID del usuario de la sesión
$id_usuario = $_SESSION['idusuario'];
$persona = $con->real_escape_string($_SESSION['Persona']); // Escapa el valor de la persona

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idcaja'])) {
    $idCaja = (int)$_POST['idcaja'];

    // Calcular el importe total en caja
    $sqlCaja = "SELECT montoini FROM caja WHERE idcaja = $idCaja";
    $resultCaja = $con->query($sqlCaja);

    if ($resultCaja->num_rows > 0) {
        $rowCaja = $resultCaja->fetch_assoc();
        $montoInicial = $rowCaja['montoini'];

        // Calcular el total de ventas
        $sqlVentas = "SELECT SUM(total) as total_ventas FROM comprobante WHERE fk_idcaja = $idCaja";
        $resultVentas = $con->query($sqlVentas);
        $totalVentas = $resultVentas->fetch_assoc()['total_ventas'];

        // Calcular el total de ingresos adicionales
        $sqlIngresos = "SELECT SUM(monto) as total_ingresos FROM igre_egre WHERE fk_idcaja = $idCaja AND fk_idtipmovi = 1";
        $resultIngresos = $con->query($sqlIngresos);
        $totalIngresos = $resultIngresos->fetch_assoc()['total_ingresos'];

        // Calcular el total de egresos adicionales
        $sqlEgresos = "SELECT SUM(monto) as total_egresos FROM igre_egre WHERE fk_idcaja = $idCaja AND fk_idtipmovi = 2";
        $resultEgresos = $con->query($sqlEgresos);
        $totalEgresos = $resultEgresos->fetch_assoc()['total_egresos'];

        // Calcular el importe total en caja
        $importeTotal = $montoInicial + $totalVentas + $totalIngresos - $totalEgresos;

        // Registrar el vaciado de caja
        $sqlVaciarCaja = "UPDATE caja SET hora_cierre = NOW(), montoini = 0, importe = $importeTotal WHERE idcaja = $idCaja";
        if ($con->query($sqlVaciarCaja) === TRUE) {
            // Insertar registro en vaciado_caja
            $sqlInsertVaciado = "INSERT INTO vaciado_caja (idcaja, monto_vaciado, fecha_vaciado, usuario_vaciado) VALUES ($idCaja, $importeTotal, NOW(), '$persona')";
            if ($con->query($sqlInsertVaciado) === TRUE) {
                echo "Caja vaciada correctamente.";
            } else {
                echo "Error al insertar el registro de vaciado: " . $con->error;
            }
        } else {
            echo "Error al vaciar la caja: " . $con->error;
        }
    } else {
        echo "No se encontró la caja especificada.";
    }

    $con->close();
} else {
    echo "Solicitud inválida.";
}
?>
