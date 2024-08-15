<?php
session_start();
include('../../conexion/conn.php');

// Verifica si el usuario está autenticado
// if (!isset($_SESSION['idusuarios'])) {
//     echo json_encode(['status' => 'false', 'message' => 'Usuario no autenticado']);
//     exit();
// }

// Extraer y sanitizar los datos del formulario
$idpaciente = intval($_POST['idpaciente']);
$dni = $_POST['dni'];
$nombres = $_POST['nombres'];
$apellidos = $_POST['apellidos'];
$fecha_nacimiento = $_POST['fecha_nacimiento'];
$direccion = $_POST['direccion'];
$genero = intval($_POST['genero']);
$correo = $_POST['correo'];
$colegiatura = $_POST['colegiatura'];
$celular = $_POST['celular'];
$lugar = $_POST['lugar'];
$especialidad = isset($_POST['especialidad']) ? $_POST['especialidad'] : [];

// Variable para rastrear si se realizaron cambios en las especialidades
$especialidadesActualizadas = false;

try {
    // Obtener las especialidades actuales del médico
    $sql = "SELECT fk_especialidad FROM medico_especialidad WHERE fk_medico = ?";
    $stmt = $con->prepare($sql);
    if (!$stmt) {
        throw new Exception('Error preparando la consulta SQL para obtener especialidades actuales');
    }
    $stmt->bind_param('i', $idpaciente);
    if (!$stmt->execute()) {
        throw new Exception('Error al obtener especialidades actuales del médico');
    }
    $result = $stmt->get_result();
    $especialidadesActuales = [];
    while ($row = $result->fetch_assoc()) {
        $especialidadesActuales[] = $row['fk_especialidad'];
    }
    $stmt->close();

    // Comparar las especialidades actuales con las nuevas especialidades
    sort($especialidadesActuales);
    sort($especialidad);
    if ($especialidadesActuales != $especialidad) {
        // Las especialidades son diferentes, realizar actualización
        $sql = "DELETE FROM medico_especialidad WHERE fk_medico = ?";
        $stmt = $con->prepare($sql);
        if (!$stmt) {
            throw new Exception('Error preparando la consulta SQL para eliminar especialidades');
        }
        $stmt->bind_param('i', $idpaciente);
        if (!$stmt->execute()) {
            throw new Exception('Error al eliminar especialidades del médico');
        }
        $stmt->close();

        foreach ($especialidad as $espec) {
            $sql = "INSERT INTO medico_especialidad (fk_medico, fk_especialidad) VALUES (?, ?)";
            $stmt = $con->prepare($sql);
            if (!$stmt) {
                throw new Exception('Error preparando la consulta SQL para insertar especialidades');
            }
            $stmt->bind_param('ii', $idpaciente, $espec);
            if (!$stmt->execute()) {
                throw new Exception('Error al insertar especialidad del médico');
            }
            $stmt->close();
        }
        $especialidadesActualizadas = true;
    }

    // Prepara la declaración para actualizar el médico
    $sql = "CALL p_upmedico(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $con->prepare($sql);
    if (!$stmt) {
        throw new Exception('Error preparando la consulta SQL');
    }

    // Vincula los parámetros
    $stmt->bind_param('isssssssiss', $idpaciente, $dni, $nombres, $apellidos, $fecha_nacimiento, $correo, $celular, $direccion, $genero, $colegiatura, $lugar);

    // Ejecuta la consulta
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        if ($row) {
            if ($row['mensaje'] === "El médico se actualizó correctamente") {
                echo json_encode(['status' => 'true', 'message' => $row['mensaje']]);
            } elseif ($row['mensaje'] === "No existen cambios") {
                if ($especialidadesActualizadas) {
                    echo json_encode(['status' => 'true', 'message' => 'Especialidades del médico actualizadas correctamente.']);
                } else {
                    echo json_encode(['status' => 'false', 'message' => $row['mensaje']]);
                }
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
