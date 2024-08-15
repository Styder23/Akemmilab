<?php
session_start();
include('../../conexion/conn.php');

// Verifica si el usuario está autenticado
// if (!isset($_SESSION['idusuarios'])) {
//     echo json_encode(['status' => 'false', 'message' => 'Usuario no autenticado']);
//     exit();
// }


// Extraer y sanitizar los datos del formulario
$idpaciente = isset($_POST['idpaciente']) ? intval($_POST['idpaciente']) : '';
$dni = isset($_POST['dni']) ? $_POST['dni'] : '';
$nombres = isset($_POST['nombres']) ? $_POST['nombres'] : '';
$apellidos = isset($_POST['apellidos']) ? $_POST['apellidos'] : '';
$fecha_nacimiento = isset($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : '';
$direccion = isset($_POST['direccion']) ? $_POST['direccion'] : '';
$genero = isset($_POST['genero']) ? intval($_POST['genero']) : '';
$correo = isset($_POST['correo']) ? $_POST['correo'] : '';
$ruc = isset($_POST['ruc']) ? $_POST['ruc'] : '';
$celular = isset($_POST['celular']) ? $_POST['celular'] : '';
$razon=isset($_POST['razon']) ? $_POST['razon'] : '';
$dir_emp=isset($_POST['dir_emp']) ? $_POST['dir_emp'] : '';
// Manejo de excepciones para la consulta
try {
    // Prepara la declaración
    $sql = "CALL p_updpaciente(?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?)";
    $stmt = $con->prepare($sql);

    if (!$stmt) {
        throw new Exception('Error preparando la consulta SQL');
    }

    // Vincula los parámetros
    $stmt->bind_param('isssssssisss', $idpaciente, $dni, $nombres, $apellidos, $fecha_nacimiento, $correo, $celular, $direccion, $genero, $ruc,$razon,$dir_emp);

    // Ejecuta la consulta
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        if ($row) {
            if ($row['mensaje'] === "El paciente se actualizó correctamente") {
                echo json_encode(['status' => 'true', 'message' => $row['mensaje']]);
            } else {
                echo json_encode(['status' => 'false', 'message' => $row['mensaje']]);
            }
        } else {
            throw new Exception('Error al obtener el resultado del procedimiento almacenado');
        }
    } else {
        throw new Exception('Error al actualizar el usuario');
    }

    // Cierra la declaración de actualización
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['status' => 'false', 'message' => $e->getMessage()]);
}

// Cierra la conexión a la base de datos
$con->close();
?>

