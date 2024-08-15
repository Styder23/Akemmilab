<?php
include('../../conexion/conn.php');

$id = $_POST['id'];

$query = $con->prepare("SELECT * FROM medicos m inner join personas p on p.idpersonas=m.fk_personas inner join genero g on g.idgenero=p.fk_idgenero WHERE idmedicos = ?");
$query->bind_param("i", $id);
$query->execute();
$result = $query->get_result();
$medico = $result->fetch_assoc();

if ($medico) {
    $medico['especialidades'] = [];

    $queryEspecialidades = $con->prepare("SELECT e.idespecialidad, e.nombre, (CASE WHEN me.fk_medico IS NOT NULL THEN 1 ELSE 0 END) AS selected FROM especialidades e LEFT JOIN medico_especialidad me ON e.idespecialidad = me.fk_especialidad AND me.fk_medico = ?");
    $queryEspecialidades->bind_param("i", $id);
    $queryEspecialidades->execute();
    $resultEspecialidades = $queryEspecialidades->get_result();

    while ($especialidad = $resultEspecialidades->fetch_assoc()) {
        $especialidad['selected'] = (bool)$especialidad['selected'];
        $medico['especialidades'][] = $especialidad;
    }

    echo json_encode($medico);
} else {
    echo json_encode([]);
}

$query->close();
$con->close();
?>
