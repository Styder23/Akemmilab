<?php
// Conectar a la base de datos y otras configuraciones
require_once '../../conexion/conn.php'; // Asegúrate de ajustar el nombre de tu archivo de conexión

// Verificar que el formulario ha sido enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir datos del formulario
    $detalle = $_POST['detalle'];
    $tipo_movimiento = $_POST['tipo_movimiento']; // 1 para ingreso, 2 para egreso
    $monto = $_POST['monto'];
    $id_caja = $_POST['idcaja']; // ID de la caja actual obtenido del formulario 

    // Validar los datos
    if (empty($detalle) || !is_numeric($monto) || empty($id_caja) || !is_numeric($tipo_movimiento)) {
        echo "<script>alert('Por favor, complete todos los campos correctamente.'); window.location.href='prueba.php';</script>";
        exit();
    }

    // Insertar en la tabla ingre_egre
    $sql = "INSERT INTO igre_egre (concepto, monto, fk_idtipmovi, fk_idcaja) VALUES (?, ?, ?, ?)";
    $stmt = $con->prepare($sql);

    // Verificar si la preparación de la consulta falló
    if ($stmt === false) {
        echo "<script>alert('Error al preparar la consulta: " . addslashes($con->error) . "'); window.location.href='prueba.php';</script>";
        exit();
    }

    $stmt->bind_param("sdii", $detalle, $monto, $tipo_movimiento, $id_caja);
    $execute = $stmt->execute();

    // Verificar si la ejecución de la consulta falló
    if ($execute === false) {
        echo "<script>alert('Error al ejecutar la consulta: " . addslashes($stmt->error) . "'); window.location.href='prueba.php';</script>";
        exit();
    }

    // Redireccionar o mostrar mensaje de éxito
    echo "<script>alert('Movimiento registrado exitosamente.'); window.location.href='prueba.php?success=true';</script>";
    exit();
} else {
    echo "<script>alert('Método de solicitud no válido.'); window.location.href='prueba.php';</script>";
}
?>
