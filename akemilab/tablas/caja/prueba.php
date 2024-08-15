<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Estado de Caja y Registrar Movimiento</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
    .status-box {
        background-color: #e3f2fd;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .form-box {
        background-color: #f1f8e9;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .table thead {
        background-color: #007bff;
        color: white;
    }

    .table tbody tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    .container {
        max-width: 1200px;
    }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>

<body>
    <div class="container mt-5">
        <h2 class="mb-4">Estado de Caja en Tiempo Real y Registrar Movimiento</h2>

        <?php
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
        <div class="row">
            <div class="col-md-6">
                <div class="status-box">
                    <h4>Estado de Caja</h4>
                    <p><strong>Monto Inicial:</strong> <?php echo number_format($montoInicial, 2); ?></p>
                    <p><strong>Total Ventas:</strong> <?php echo number_format($totalVentas, 2); ?></p>
                    <p><strong>Total Ingresos:</strong> <?php echo number_format($totalIngresos, 2); ?></p>
                    <p><strong>Total Egresos:</strong> <?php echo number_format($totalEgresos, 2); ?></p>
                    <h5><strong>Importe Total en Caja:</strong> <?php echo number_format($importeTotal, 2); ?></h5>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-box">
                    <h4>Registrar Ingreso/Egreso</h4>
                    <form action="procesar_movimiento.php" method="post">
                        <div class="form-group">
                            <label for="detalle">Detalle:</label>
                            <input type="text" class="form-control" id="detalle" name="detalle" required>
                        </div>
                        <div class="form-group">
                            <label for="tipo_movimiento">Tipo de Movimiento:</label>
                            <select class="form-control" id="tipo_movimiento" name="tipo_movimiento" required>
                                <option value="1">Ingreso</option>
                                <option value="2">Egreso</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="monto">Monto:</label>
                            <input type="number" step="0.01" class="form-control" id="monto" name="monto" required>
                        </div>
                        <input type="hidden" name="idcaja" value="<?php echo $idCaja; ?>">
                        <button type="submit" class="btn btn-primary">Registrar Movimiento</button>
                        <button type="button" class="btn btn-warning" onclick="vaciarCaja()"
                            <?php if (!$idCaja) echo 'disabled'; ?>>Vaciar Caja</button>

                    </form>
                </div>
            </div>
        </div>

        <h3 class="mt-4">Movimientos</h3>
        <table class="table table-striped table-bordered mt-3">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Detalle</th>
                    <th>Tipo</th>
                    <th>Monto</th>
                    <th>Usuario</th>
                    <th>Acciones</th> <!-- Añadimos una columna para las acciones -->
                </tr>
            </thead>
            <tbody>
                <?php
                // Movimientos de ventas
                $sqlMovimientos = "
    SELECT 
        c.fechacompro AS fecha, 
        CONCAT_WS(' ', 'Comprobante: ', c.idcomprobante) AS detalle, 
        'Ingreso' AS tipo, 
        c.total AS monto, 
        u.nomusu AS usuario,
        NULL AS idigre_egre -- Añadimos esta columna para que coincida con las otras subconsultas
    FROM comprobante c
    INNER JOIN caja ca ON ca.idcaja = c.fk_idcaja
    INNER JOIN usuario u ON u.idusuario = ca.fk_idusuario
    INNER JOIN personas p ON p.idpersonas = u.fk_idpersonas
    WHERE c.fk_idcaja = $idCaja
    UNION ALL
    SELECT 
        i.fecha AS fecha, 
        i.concepto AS detalle, 
        'Ingreso' AS tipo, 
        i.monto AS monto, 
        u.nomusu AS usuario,
        i.idigre_egre AS idigre_egre
    FROM igre_egre i
    INNER JOIN caja ca ON ca.idcaja = i.fk_idcaja
    INNER JOIN usuario u ON u.idusuario = ca.fk_idusuario                    
    WHERE i.fk_idcaja = $idCaja AND i.fk_idtipmovi = 1
    UNION ALL
    SELECT 
        i.fecha AS fecha, 
        i.concepto AS detalle, 
        'Egreso' AS tipo, 
        i.monto AS monto, 
        u.nomusu AS usuario,
        i.idigre_egre AS idigre_egre
    FROM igre_egre i
    INNER JOIN caja ca ON ca.idcaja = i.fk_idcaja
    INNER JOIN usuario u ON u.idusuario = ca.fk_idusuario 
    WHERE i.fk_idcaja = $idCaja AND i.fk_idtipmovi = 2
    ORDER BY fecha;
";
                $resultMovimientos = $con->query($sqlMovimientos);

                if ($resultMovimientos->num_rows > 0) {
                    while ($rowMov = $resultMovimientos->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $rowMov['fecha'] . "</td>";
                        echo "<td>" . $rowMov['detalle'] . "</td>";
                        echo "<td>" . $rowMov['tipo'] . "</td>";
                        echo "<td>" . number_format($rowMov['monto'], 2) . "</td>";
                        echo "<td>" . $rowMov['usuario'] . "</td>";
                        echo "<td><button type='button' class='btn btn-sm btn-primary' onclick=\"editarDetalle('" . $rowMov['idigre_egre'] . "', '" . $rowMov['detalle'] . "')\">Editar</button></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr>
                        <td colspan='6'>No hay movimientos registrados.</td>
                    </tr>";
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

    <script>
    function vaciarCaja() {
        if (confirm('¿Estás seguro de que quieres vaciar la caja?')) {
            const form = document.createElement('form');
            form.method = 'post';
            form.action = 'vaciar_caja.php';

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'idcaja';
            input.value = '<?php echo $idCaja; ?>';

            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    }

    function editarDetalle(id, concepto) {
        const nuevoDetalle = prompt('Ingrese el nuevo detalle:', concepto);
        if (nuevoDetalle !== null && nuevoDetalle !== '') {
            const form = document.createElement('form');
            form.method = 'post';
            form.action = 'editar_detalle.php';

            const inputId = document.createElement('input');
            inputId.type = 'hidden';
            inputId.name = 'idigre_egre';
            inputId.value = id;

            const inputDetalle = document.createElement('input');
            inputDetalle.type = 'hidden';
            inputDetalle.name = 'concepto';
            inputDetalle.value = nuevoDetalle;

            form.appendChild(inputId);
            form.appendChild(inputDetalle);
            document.body.appendChild(form);
            form.submit();
        }
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>