<?php

include('../../conexion/conn.php');

// Extraer los datos del formulario
$id = isset($_POST['idPaciente']) ? intval($_POST['idPaciente']) : '';
$dni = isset($_POST['dni']) ? $_POST['dni'] : '';
$nombres = isset($_POST['nombres']) ? $_POST['nombres'] : '';
$apellidos = isset($_POST['apellidos']) ? $_POST['apellidos'] : '';
$genero = isset($_POST['genero']) ? intval($_POST['genero']) : '';
$fecha_nacimiento = isset($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : '';
$telefono = isset($_POST['telefono']) ? $_POST['telefono'] : '';
$direccion = isset($_POST['direccion']) ? $_POST['direccion'] : '';
$correo = isset($_POST['correo']) ? $_POST['correo'] : '';
$cargo = isset($_POST['cargo']) ? intval($_POST['cargo']) : '';
$usuario = isset($_POST['usuario']) ? $_POST['usuario'] : '';
$contraseña = isset($_POST['contraseña']) ? $_POST['contraseña'] : '';
// Manejo de excepciones para la consulta
try {
    // Prepara la declaración
    $sql = "CALL p_updusu(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $con->prepare($sql);

    if (!$stmt) {
        throw new Exception('Error preparando la consulta SQL');
    }

    // Vincula los parámetros
    $stmt->bind_param('isssssssissi', $id, $dni, $nombres, $apellidos, $fecha_nacimiento, $correo, $telefono, $direccion, $genero, $usuario, $contraseña, $cargo);

    // Ejecuta la consulta
    if ($stmt->execute()) {
        // Obtener el resultado
        $result = $stmt->get_result();

        // Inicializar una variable para almacenar los mensajes
        $messages = [];

        // Procesar cada fila de resultado
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row['mensaje'];
        }

        // Verificar los mensajes y determinar la respuesta
        $success_message = 'El usuario se actualizó correctamente';
        if (in_array($success_message, $messages)) {
            echo json_encode(['status' => 'true', 'message' => $success_message]);
        } else {
            echo json_encode(['status' => 'false', 'message' => implode(' ', $messages)]);
        }

        // Cierra el resultado
        $result->free();
    } else {
        throw new Exception('Error al actualizar el usuario');
    }

    // Cierra la declaración
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['status' => 'false', 'message' => $e->getMessage()]);
}

// Cierra la conexión a la base de datos
$con->close();
?>
