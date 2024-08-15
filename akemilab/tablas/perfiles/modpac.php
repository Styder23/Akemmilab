<?php
session_start();
include('../../conexion/conn.php');

// Verifica si el usuario está autenticado
// if (!isset($_SESSION['idusuarios'])) {
//     echo json_encode(['status' => 'false', 'message' => 'Usuario no autenticado']);
//     exit();
// }

// Extraer y sanitizar los datos del formulario
$idperfil = intval($_POST['idperfil']);
$perfil = $_POST['perfil'];
$precio = $_POST['precio'];
$especialidad = isset($_POST['examenes']) ? $_POST['examenes'] : [];

// Variable para rastrear si se realizaron cambios en las especialidades
$especialidadesActualizadas = false;

try {
    // Obtener las especialidades actuales del médico
    $sql = "SELECT fk_idtipoex FROM perfilxexam WHERE fk_idperfil = ?";
    $stmt = $con->prepare($sql);
    if (!$stmt) {
        throw new Exception('Error preparando la consulta SQL para obtener examenes actuales');
    }
    $stmt->bind_param('i', $idperfil);
    if (!$stmt->execute()) {
        throw new Exception('Error al obtener examenes actuales del perfil');
    }
    $result = $stmt->get_result();
    $especialidadesActuales = [];
    while ($row = $result->fetch_assoc()) {
        $especialidadesActuales[] = $row['fk_idtipoex'];
    }
    $stmt->close();

    // Comparar las especialidades actuales con las nuevas especialidades
    sort($especialidadesActuales);
    sort($especialidad);
    if ($especialidadesActuales != $especialidad) {
        // Las especialidades son diferentes, realizar actualización
        $sql = "DELETE FROM perfilxexam WHERE fk_idperfil = ?";
        $stmt = $con->prepare($sql);
        if (!$stmt) {
            throw new Exception('Error preparando la consulta SQL para eliminar examenes');
        }
        $stmt->bind_param('i', $idperfil);
        if (!$stmt->execute()) {
            throw new Exception('Error al eliminar examenes del perfil');
        }
        $stmt->close();

        foreach ($especialidad as $espec) {
            $sql = "INSERT INTO perfilxexam (fk_idtipoex, fk_idperfil) VALUES (?, ?)";
            $stmt = $con->prepare($sql);
            if (!$stmt) {
                throw new Exception('Error preparando la consulta SQL para insertar examenes');
            }
            $stmt->bind_param('ii', $espec, $idperfil);
            if (!$stmt->execute()) {
                throw new Exception('Error al insertar examenes al perfil');
            }
            $stmt->close();
        }
        $especialidadesActualizadas = true;
    }

    // Prepara la declaración para actualizar el médico
    $sql = "CALL p_updateperfil(?, ?, ?)";
    $stmt = $con->prepare($sql);
    if (!$stmt) {
        throw new Exception('Error preparando la consulta SQL');
    }

    // Vincula los parámetros
    $stmt->bind_param('iss', $idperfil, $perfil, $precio);

    // Ejecuta la consulta
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        if ($row) {
            if ($row['mensaje'] === "El perfil se actualizó correctamente") {
                echo json_encode(['status' => 'true', 'message' => $row['mensaje']]);
            } elseif ($row['mensaje'] === "No existen cambios") {
                if ($especialidadesActualizadas) {
                    echo json_encode(['status' => 'true', 'message' => 'Exámenes del perfil actualizadas correctamente.']);
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
        throw new Exception('Error al actualizar el perfil');
    }

    // Cierra la declaración de actualización
    $stmt->close();

} catch (Exception $e) {
    echo json_encode(['status' => 'false', 'message' => $e->getMessage()]);
}

// Cierra la conexión a la base de datos
$con->close();
?>
