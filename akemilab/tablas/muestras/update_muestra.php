<?php
// Incluye la conexión a la base de datos
include('../../conexion/conn.php');

// Obtiene los valores del POST de forma segura
$idmuestra = intval($_POST['idmuestra']);
$muestra = $_POST['muestra'];

// Manejo de excepciones para la consulta
try {
    // Prepara la declaración
    $sql = "CALL editar_muestra(?, ?)";
    $stmt = $con->prepare($sql);

    if (!$stmt) {
        throw new Exception('Error preparando la consulta SQL');
    }

    // Vincula los parámetros
    $stmt->bind_param('is',$idmuestra, $muestra);
    // Ejecuta la consulta
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        if ($row) {
            if ($row['mensaje'] === "Esa muestra ya existe") {
                echo json_encode(['status' => 'false', 'message' => $row['mensaje']]);
            } else {
                echo json_encode(['status' => 'true', 'message' => $row['mensaje']]);
            }
        } else {
            throw new Exception('Error al obtener el resultado del procedimiento almacenado');
        }
    } else {
        throw new Exception('Error al actualizar el paciente');
    }

    // Cierra la declaración
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['status' => 'false', 'message' => $e->getMessage()]);
}

// Cierra la conexión a la base de datos
$con->close();
?>
