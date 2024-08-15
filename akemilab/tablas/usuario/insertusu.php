<?php
// Incluye la conexión a la base de datos
include('../../conexion/conn.php');

// Obtiene los datos del formulario
$dni = isset($_POST['dni']) ? $_POST['dni'] : '';
$nombre = isset($_POST['nombre']) ? $_POST['nombre'] : '';
$apellidos = isset($_POST['apellido']) ? $_POST['apellido'] : '';
$genero = isset($_POST['genero']) ? $_POST['genero'] : '';
$fecha_nacimiento = isset($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : '';
$telefono = isset($_POST['telefono']) ? $_POST['telefono'] : '';
$direccion = isset($_POST['direccion']) ? $_POST['direccion'] : '';
$correo = isset($_POST['correo']) ? $_POST['correo'] : '';
$cargo = isset($_POST['cargo']) ? $_POST['cargo'] : '';
$usuario = isset($_POST['usuario']) ? $_POST['usuario'] : '';
$contraseña = isset($_POST['contraseña']) ? $_POST['contraseña'] : '';

// Realizamos la consulta para insertar
$sql = $con->prepare("CALL p_inseusu(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$sql->bind_param('sssssssissi', $dni, $nombre, $apellidos, $fecha_nacimiento, $correo, $telefono, $direccion, $genero, $usuario, $contraseña, $cargo);

$result = $sql->execute();
$sql->store_result();
$sql->bind_result($mensaje);

if ($result) {
    while ($sql->fetch()) {
        if ($mensaje == 'El usuario ha sido ingresado correctamente.') {
            echo json_encode(['status' => 'true', 'message' => $mensaje]);
        } else {
            echo json_encode(['status' => 'false', 'message' => $mensaje]);
        }
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error al registrar']);
}

// Cierra la conexión y la consulta
$sql->close();
$con->close();
?>

