<?php
include('../../conexion/conn.php');

$id = $_POST['id'];

// Preparar la consulta para obtener el perfil
$query = $con->prepare("SELECT * FROM perfiles WHERE idperfil = ?");
$query->bind_param("i", $id);
$query->execute();
$result = $query->get_result();
$perfil = $result->fetch_assoc();

if ($perfil) {
    $perfil['examenes'] = [];

    // Preparar la consulta para obtener los exámenes del perfil
    $queryExamenes = $con->prepare("
        SELECT t.idtipoexamen, t.tipoexam, 
               (CASE WHEN p.fk_idtipoex IS NOT NULL THEN 1 ELSE 0 END) AS selected 
        FROM tipoexamen t 
        LEFT JOIN perfilxexam p ON t.idtipoexamen = p.fk_idtipoex AND p.fk_idperfil = ?
    ");
    $queryExamenes->bind_param("i", $id);
    $queryExamenes->execute();
    $resultExamenes = $queryExamenes->get_result();

    while ($examen = $resultExamenes->fetch_assoc()) {
        $examen['selected'] = (bool)$examen['selected'];
        $perfil['examenes'][] = $examen;
    }

    echo json_encode($perfil);

    $queryExamenes->close();
} else {
    echo json_encode([]);
}

$query->close();
$con->close();
?>
