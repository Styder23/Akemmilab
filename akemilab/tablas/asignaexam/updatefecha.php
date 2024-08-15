<?php
// Incluye la conexión a la base de datos
include('../../conexion/conn.php');

// Verifica si se recibieron los datos requeridos en la solicitud POST
if (!isset($_POST['idexamen'], $_POST['fecha'], $_POST['examen'])) {
    echo json_encode(['status' => 'false', 'message' => 'Faltan datos en la solicitud']);
    exit;
}

// Obtiene los valores del POST de forma segura
$idexamen = intval($_POST['idexamen']);
$fecha = $_POST['fecha'];
$idtipoexamen = intval($_POST['examen']); // Se asume que 'examen' es el idtipoexamen

try {
    // Prepara la declaración
    $sql = "UPDATE examen SET fecha = ?, fk_idtipoexamen = ? WHERE idexamen = ?";
    $stmt = $con->prepare($sql);

    if (!$stmt) {
        throw new Exception('Error preparando la consulta SQL');
    }

    // Vincula los parámetros
    $stmt->bind_param('sii', $fecha, $idtipoexamen, $idexamen);

    // Ejecuta la consulta
    if ($stmt->execute()) {
        echo json_encode(['status' => 'true', 'message' => 'Examen actualizado con éxito']);
    } else {
        throw new Exception('Error al actualizar el examen');
    }

    // Cierra la declaración
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['status' => 'false', 'message' => $e->getMessage()]);
}

// Cierra la conexión a la base de datos
$con->close();
?>
