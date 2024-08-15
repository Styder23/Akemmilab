<?php

include('../../conexion/conn.php');

// Extraer los datos del formulario
$id = isset($_POST['idPaciente']) ? intval($_POST['idPaciente']) : '';
$nommedi = isset($_POST['nommedi']) ? $_POST['nommedi'] : '';
$rangomin = isset($_POST['rangomin']) ? $_POST['rangomin'] : '';
$rangomax = isset($_POST['rangomax']) ? $_POST['rangomax'] : '';
$idtipoexamen = isset($_POST['idtipoexamen']) ? intval($_POST['idtipoexamen']) : '';
$idunidades = $_POST['idunidades'] ? intval($_POST['idunidades']) : null;
$idnombretit = $_POST['idnombretit'] ? intval($_POST['idnombretit']) : null;
try {
    // Prepara la declaración
    $sql = "CALL p_updato(?, ?, ?, ?, ?, ?, ?)";
    $stmt = $con->prepare($sql);

    if (!$stmt) {
        throw new Exception('Error preparando la consulta SQL');
    }

    // Vincula los parámetros
    $stmt->bind_param('isssiii', $id, $nommedi, $rangomin, $rangomax, $idtipoexamen, $idnombretit,$idunidades);

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
        $success_message = 'El dato se actualizó correctamente';
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
