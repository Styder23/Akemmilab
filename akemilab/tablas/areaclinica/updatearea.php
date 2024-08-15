<?php
// Incluye la conexión a la base de datos
include('../../conexion/conn.php');
// Verifica si se recibieron los datos requeridos en la solicitud POST
// if (isset($_POST['_id'], $_POST['_idarea'], $_POST['_area'])) {
//     echo json_encode(['status' => 'false', 'message' => 'Faltan datos en la solicitud']);
//     exit;
// }

// Obtiene los valores del POST de forma segura
$idarea = intval($_POST['idarea']);
$area = $_POST['nomarea'];


// Manejo de excepciones para la consulta
try {
    // Prepara la declaración
    $sql = "call editar_area(?,?)";
    $stmt = $con->prepare($sql);

    // Vincula los parámetros
    $stmt->bind_param('is',$idarea, $area);
    // Ejecuta la consulta
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        if ($row) {
            if ($row['mensaje'] === "Esa área ya existe") {
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
