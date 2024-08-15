<?php
include '../../conexion/conn.php';

// Función para buscar exámenes según el término de búsqueda
function buscarExamenes($con, $query) {
    // Escapar caracteres especiales y evitar inyección SQL
    $query = mysqli_real_escape_string($con, $query);

    // Consulta SQL para buscar exámenes
    $sql = "SELECT idtipoexamen AS id, tipoexam AS nombre, precio, nomarea AS area, 'examen' AS tipo
            FROM tipoexamen t INNER JOIN area a ON a.idarea = t.fk_idarea
            WHERE tipoexam LIKE '%$query%' AND disponibilidad = 1";

    $result = mysqli_query($con, $sql);

    if (!$result) {
        echo json_encode(array('error' => 'Error en la consulta SQL de exámenes: ' . mysqli_error($con)));
        exit();
    }

    $examenes = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $examenes[] = $row;
    }

    return $examenes;
}

// Función para buscar perfiles según el término de búsqueda
function buscarPerfiles($con, $query) {
    // Escapar caracteres especiales y evitar inyección SQL
    $query = mysqli_real_escape_string($con, $query);

    // Consulta SQL para buscar perfiles
    $sql = "SELECT idperfil AS id, nomperfil AS nombre, precioperfil AS precio, 'perfil' AS area, 'perfil' AS tipo
            FROM perfiles
            WHERE nomperfil LIKE '%$query%'";

    $result = mysqli_query($con, $sql);

    if (!$result) {
        echo json_encode(array('error' => 'Error en la consulta SQL de perfiles: ' . mysqli_error($con)));
        exit();
    }

    $perfiles = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $perfiles[] = $row;
    }

    // Añadir exámenes de cada perfil
    foreach ($perfiles as &$perfil) {
        $idperfil = $perfil['id'];
        $precioPerfil = $perfil['precio']; // Precio del perfil
        $sqlExamenes = "SELECT t.idtipoexamen AS id, t.tipoexam AS nombre, $precioPerfil AS precio
                        FROM perfilxexam pxe
                        INNER JOIN tipoexamen t ON t.idtipoexamen = pxe.fk_idtipoex
                        WHERE pxe.fk_idperfil = $idperfil";

        $resultExamenes = mysqli_query($con, $sqlExamenes);

        if (!$resultExamenes) {
            echo json_encode(array('error' => 'Error en la consulta SQL de exámenes del perfil: ' . mysqli_error($con)));
            exit();
        }

        $examenes = array();
        while ($rowExamen = mysqli_fetch_assoc($resultExamenes)) {
            $examenes[] = $rowExamen;
        }

        $perfil['examenes'] = $examenes;
    }

    return $perfiles;
}

// Verificar si se recibió un término de búsqueda
if (isset($_GET['query'])) {
    $query = $_GET['query'];

    // Realizar la búsqueda de exámenes y perfiles
    $examenes = buscarExamenes($con, $query);
    $perfiles = buscarPerfiles($con, $query);

    // Devolver resultados como JSON
    echo json_encode(array('examenes' => $examenes, 'perfiles' => $perfiles));
} else {
    echo json_encode(array('error' => 'No se recibió ningún término de búsqueda.'));
}

// Cerrar conexión a la base de datos al finalizar
mysqli_close($con);
?>
