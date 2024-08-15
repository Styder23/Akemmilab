<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Manejo de Caja</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <style>
        .table thead th {
            background-color: #00BFFF;
            color: white;
        }
        .table tbody tr:nth-child(odd) {
            background-color: #f2f2f2;
        }
        .table tbody tr:nth-child(even) {
            background-color: #d9d9d9;
        }
        .table .current-caja {
            background-color: #d4edda; /* Verde claro */
        }
        .table .closed-caja {
            background-color: #f8d7da; /* Rojo claro */
        }
        .dt-column-order {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>Manejo de Caja</h2> 

        <?php
        session_start();
        if (!isset($_SESSION['idusuario'])) {
            header("Location: ./login/login.php");
        }

        // Conectar a la base de datos y obtener el ID de la caja abierta
        require_once '../../conexion/conn.php';

        $idCajaAbierta = null;
        $montoInicial = 0;
        $horaApertura = '';
        $sql = "SELECT idcaja, hora_apertura, montoini FROM caja WHERE hora_cierre IS NULL ORDER BY hora_apertura DESC LIMIT 1";
        $result = $con->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $idCajaAbierta = $row['idcaja'];
            $horaApertura = $row['hora_apertura'];
            $montoInicial = $row['montoini'];
        } else {
            // Obtener el último monto de cierre si no hay caja abierta
            $sql = "SELECT importe FROM caja WHERE hora_cierre IS NOT NULL ORDER BY hora_cierre DESC LIMIT 1";
            $result = $con->query($sql);

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $montoInicial = 0; // Establece el monto inicial a 0 después de cerrar la caja
            }
        }
        ?>

        <!-- Mostrar ID de la caja abierta, si existe -->
        <?php if ($idCajaAbierta): ?>
            <div class="alert alert-info">
                Caja Abierta ID: <?php echo $idCajaAbierta; ?> (Hora de Apertura: <?php echo $horaApertura; ?>, Monto Inicial: <?php echo $montoInicial; ?>)
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                No hay ninguna caja abierta actualmente.
            </div>
        <?php endif; ?>

        <!-- Formulario para abrir/cerrar caja -->
        <form id="cajaForm" method="post" class="mt-4">
            <div class="form-group">
                <label for="monto_inicial">Importe Inicial:</label>
                <input type="number" step="0.01" class="form-control" id="monto_inicial" name="monto_inicial" value="<?php echo $montoInicial; ?>" <?php if ($idCajaAbierta) echo 'readonly'; ?> required>
            </div>
            <?php if (!$idCajaAbierta): ?>
                <div class="form-group">
                    <label for="monto_agregado">Monto Adicional:</label>
                    <input type="number" step="0.01" class="form-control" id="monto_agregado" name="monto_agregado">
                </div>
            <?php endif; ?>
            <button type="button" class="btn btn-primary" onclick="submitForm('abrir')" <?php if ($idCajaAbierta) echo 'disabled'; ?>>Abrir Caja</button>
            <button type="button" class="btn btn-danger" onclick="submitForm('cerrar')" <?php if (!$idCajaAbierta) echo 'disabled'; ?>>Cerrar Caja</button>
        </form>

        <hr>

        <!-- Tabla para mostrar registros de apertura y cierre de caja -->
        <h3>Registros de Apertura/Cierre de Caja</h3>
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>Fecha Inicio</th>
                    <th>Detalle</th>
                    <th>Tipo</th>
                    <th>Monto</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT idcaja, hora_apertura, montoini, hora_cierre, importe FROM caja ORDER BY hora_apertura DESC";
                $result = $con->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $isCurrent = $row['idcaja'] == $idCajaAbierta;
                        $rowClass = $isCurrent ? 'current-caja' : ($row['hora_cierre'] ? 'closed-caja' : '');
                        
                        echo "<tr class='$rowClass'>";
                        echo "<td>" . $row['hora_apertura'] . "</td>";
                        echo "<td>Caja Abierta</td>";
                        echo "<td>Ingreso</td>";
                        echo "<td>" . $row['montoini'] . "</td>";
                        echo "</tr>";
                        
                        if ($row['hora_cierre']) {
                            echo "<tr class='$rowClass'>";
                            echo "<td>" . $row['hora_cierre'] . "</td>";
                            echo "<td>Caja Cerrada</td>";
                            echo "<td>Ingreso</td>";
                            echo "<td>" . $row['importe'] . "</td>";
                        }
                    }
                } else {
                    echo "<tr><td colspan='4'>No hay registros de apertura de caja.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Bootstrap JS y dependencias opcionales -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function submitForm(action) {
            const form = document.getElementById('cajaForm');
            if (action === 'abrir') {
                form.action = './abrir_caja.php';
            } else if (action === 'cerrar') {
                form.action = './cerrar_caja.php';
            }
            form.submit();
        }
    </script>
</body>
</html>
