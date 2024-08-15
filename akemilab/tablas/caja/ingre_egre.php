<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Ingreso/Egreso</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2>Registrar Ingreso/Egreso</h2> 

        <?php
        // Conectar a la base de datos y obtener el ID de la caja abierta
        require_once '../../conexion/conn.php'; // Asegúrate de ajustar el nombre de tu archivo de conexión

        $id_caja_abierta = null;
        $sql = "SELECT idcaja FROM caja WHERE hora_cierre IS NULL ORDER BY hora_apertura DESC LIMIT 1";
        $result = $con->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $id_caja_abierta = $row['idcaja'];
        } else {
            echo "<div class='alert alert-warning'>No hay ninguna caja abierta actualmente.</div>";
        }
        ?>

        <!-- Solo mostrar el formulario si hay una caja abierta -->
        <?php if ($id_caja_abierta): ?>
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
                <!-- Este campo oculto enviará el ID de la caja actualmente abierta -->
                <input type="hidden" name="idcaja" value="<?php echo $id_caja_abierta; ?>">
                <button type="submit" class="btn btn-primary">Registrar Movimiento</button>
            </form>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS y dependencias opcionales -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
</body>
</html>