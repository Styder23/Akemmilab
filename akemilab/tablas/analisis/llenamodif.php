
<?php
// Incluye la conexión a la base de datos
include('../../conexion/conn.php');

// Verifica si se recibió un ID
// if (!isset($_POST['id']) || empty($_POST['id'])) {
//     echo json_encode(['error' => 'ID no proporcionado']);
//     exit;
// }

// Obtiene el ID del POST
$id = intval($_POST['id']);

// Consulta para obtener el examen
$sql = "SELECT m.idnommedible, m.nommedi, m.rangomin,m.rangomax, 
       t.idtipoexamen,n.idnombretit,u.idunidades
FROM nommedible m
INNER JOIN tipoexamen t ON t.idtipoexamen = m.fk_idtipoexamen
left JOIN nombretit n ON n.idnombretit = m.fk_idnombretit
left JOIN unidades u ON u.idunidades=m.fk_idunidades where idnommedible= ? LIMIT 1";
$stmt = $con->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

// Si no se encuentra ningún examen
if ($result->num_rows === 0) {
    echo json_encode(['error' => 'No se encontró el Usuario']);
    exit;
}

// Obtiene el resultado como un array asociativo
$row = $result->fetch_assoc();

// Devuelve el resultado como JSON
echo json_encode($row);

// Cierra la conexión
$stmt->close();
?>
