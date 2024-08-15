<?php
// Incluye la conexión a la base de datos
include('../../conexion/conn.php');


// Obtiene los valores del POST de forma segura
$idexamen = intval($_POST['_idexamen']);
$examen = $_POST['_examen'];
$area = intval($_POST['_area']);
$precio =  floatval($_POST['_precio']);
$pass = $_POST['_pass'];
// Manejo de excepciones para la consulta
try {
    // Prepara la declaración
    $sql = "call editar_tipo_examen(?,?,?,?,?)"; 
    $stmt = $con->prepare($sql);

    // Vincula los parámetros
    $stmt->bind_param('isdis', $idexamen,$examen,$precio, $area,$pass);

    // Ejecuta la consulta
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        if ($row) {
            if ($row['mensaje'] === "Examen editado correctamente") {
                echo json_encode(['status' => 'true', 'message' => $row['mensaje']]);
            } else {
                echo json_encode(['status' => 'false', 'message' => $row['mensaje']]);
            }
        } else {
            throw new Exception('Error al obtener el resultado del procedimiento almacenado');
        }
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
