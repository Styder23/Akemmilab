<?php
session_start();
// Importa la clase de conexión
include('../../conexion/conexion.php');

// Verifica si el usuario está autenticado
// if (!isset($_SESSION['idusuarios'])) {
//     echo json_encode(['status' => 'false', 'message' => 'Usuario no autenticado']);
//     exit();
// }

// Obtén el ID del usuario de la sesión
// $id_usuario = $_SESSION['idusuarios'];
// $persona = $_SESSION['Persona']; 

try {
    // Crea una instancia de la base de datos
    $conexionBD = BD::crearInstancia();

    // Verifica si se recibió una solicitud POST con el ID del usuario
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
        // Obtener el ID del usuario de la solicitud POST
        $idpacientes = intval($_POST['id']);
        // Preparar la llamada al procedimiento almacenado
        $stmt = $conexionBD->prepare("delete from medicos where idmedicos=:idpacientes");

        // Asignar el parámetro
        $stmt->bindParam(':idpacientes', $idpacientes, PDO::PARAM_INT);

        // Ejecutar el procedimiento almacenado
        $stmt->execute();

        // Recoger el resultado del procedimiento almacenado
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        // Cerrar el cursor
        $stmt->closeCursor();
        echo json_encode(['status' => 'true']);
    } else {
        // Si no se recibe un ID, devolver un mensaje de error
        echo json_encode(['error' => 'ID del paciente no proporcionado o método de solicitud no válido.']);
    }
} catch (Exception $e) {
    // Manejar excepciones y devolver el error en formato JSON
    echo json_encode(['error' => 'Hubo un error al procesar la solicitud: ' . $e->getMessage()]);
}
?>
