
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
$sql = "SELECT u.idusuario, 
       p.dni, 
       p.Nombre, 
       p.Apellido, 
       g.idgenero,
       p.fecha_nacimiento,
       p.celular, 
       p.direccion, 
       p.correo, 
       t.idtiposusaurio, 
       u.nomusu, 
       u.psw AS pass
FROM roles r
INNER JOIN usuario u ON u.idusuario = r.fk_idusuario
INNER JOIN personas p ON p.idpersonas = u.fk_idpersonas
INNER JOIN genero g ON g.idgenero = p.fk_idgenero
INNER JOIN tiposusaurio t ON t.idtiposusaurio = r.fk_idtiposusaurio
WHERE r.fk_idusuario = ? LIMIT 1";
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
