<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estado de Caja en Tiempo Real</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .status-box {
            background-color: #e3f2fd;
            padding: 20px;
            border-radius: 8px;
        }
        .table thead {
            background-color: #007bff;
            color: white;
        }
        .table tbody tr:nth-child(even) {
            background-color: #f2f2f2; 
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>Estado de Caja en Tiempo Real</h2>

        <?php
        session_start();
        if(!isset($_SESSION['idusuario'])){
            header("Location: ./login/login.php");
         }
        require_once '../../conexion/conn.php';

        // Obtener la caja abierta
        $sqlCaja = "SELECT idcaja, montoini FROM caja WHERE hora_cierre IS NULL ORDER BY hora_apertura DESC LIMIT 1";
        $resultCaja = $con->query($sqlCaja);

        if ($resultCaja->num_rows > 0) {
            $rowCaja = $resultCaja->fetch_assoc();
            $idCaja = $rowCaja['idcaja'];
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
        ?>
            <div class="status-box">
                <p><strong>Monto Inicial:</strong> <?php echo number_format($montoInicial, 2); ?></p>
                <p><strong>Total Ventas:</strong> <?php echo number_format($totalVentas, 2); ?></p>
                <p><strong>Total Ingresos:</strong> <?php echo number_format($totalIngresos, 2); ?></p>
                <p><strong>Total Egresos:</strong> <?php echo number_format($totalEgresos, 2); ?></p>
                <h4><strong>Importe Total en Caja:</strong> <?php echo number_format($importeTotal, 2); ?></h4>
            </div>

            <!-- Mostrar todos los movimientos -->
            <h3 class="mt-4">Movimientos</h3>
            <table class="table table-striped table-bordered mt-3">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Detalle</th>
                        <th>Tipo</th>
                        <th>Monto</th>
                        <th>Usuario</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Movimientos de ventas
                    $sqlMovimientos = "SELECT c.fechacompro as fecha, concat_ws(' ','Comprobante: ', c.idcomprobante) as detalle,'Ingreso' as tipo,c.total as monto,ca.fk_idusuario as usuario
                    FROM comprobante c
                    INNER JOIN caja ca ON ca.idcaja = c.fk_idcaja
                    INNER JOIN usuario u ON u.idusuario = ca.fk_idusuario
                    INNER JOIN personas p ON p.idpersonas = u.fk_idpersonas
                    WHERE c.fk_idcaja = $idCaja
                    UNION ALL
                    SELECT fecha as fecha,concepto as detalle,'Ingreso' as tipo,monto as monto,u.nomusu as usuario
                    FROM igre_egre i
                    inner join caja ca on ca.idcaja=i.fk_idcaja
                    inner join usuario u on u.idusuario=ca.fk_idusuario                    
                    WHERE fk_idcaja = $idCaja AND fk_idtipmovi = 1
                    UNION ALL
                    SELECT fecha as fecha, concepto as detalle, 'Egreso' as tipo, monto as monto,u.nomusu as usuario
                    FROM igre_egre i
                    inner join caja ca on ca.idcaja=i.fk_idcaja
                    inner join usuario u on u.idusuario=ca.fk_idusuario 
                    WHERE fk_idcaja = $idCaja AND fk_idtipmovi = 2
                    ORDER BY fecha;";
                    $resultMovimientos = $con->query($sqlMovimientos);

                    if ($resultMovimientos->num_rows > 0) {
                        while ($rowMov = $resultMovimientos->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $rowMov['fecha'] . "</td>";
                            echo "<td>" . $rowMov['detalle'] . "</td>";
                            echo "<td>" . $rowMov['tipo'] . "</td>";
                            echo "<td>" . number_format($rowMov['monto'], 2) . "</td>";
                            echo "<td>" . $rowMov['usuario'] . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>No hay movimientos registrados.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        <?php
        } else {
            echo "<div class='alert alert-warning'>No hay ninguna caja abierta actualmente.</div>";
        }

        $con->close();
        ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
