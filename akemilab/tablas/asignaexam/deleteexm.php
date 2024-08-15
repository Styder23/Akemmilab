<?php

// Importa la clase de conexión
require_once("../../conexion/conexion.php");

try {
    // Crea una instancia de la base de datos
    $conexionBD = BD::crearInstancia();

    // Verifica si se recibió una solicitud POST con el ID del paciente
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
        // Obtener el ID del paciente de la solicitud POST
        $idusuario = intval($_POST['id']);

        // Preparar la llamada al procedimiento almacenado
        $stmt = $conexionBD->prepare("DELETE FROM examen WHERE idexamen = :idusuario");

        // Asignar el parámetro
        $stmt->bindParam(':idusuario', $idusuario, PDO::PARAM_INT);

        // Ejecutar el procedimiento almacenado
        $stmt->execute();

        // Recoger el resultado del procedimiento almacenado
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        // Devolver la respuesta en formato JSON
        echo json_encode(['status' => 'true', 'message' => 'Examen eliminado correctamente']);


        // Cerrar el cursor
        $stmt->closeCursor();
    } else {
        // Si no se recibe un ID, devolver un mensaje de error
        echo json_encode(['error' => 'ID del examen no proporcionado o método de solicitud no válido.']);
    }
} catch (Exception $e) {
    // Manejar excepciones y devolver el error en formato JSON
    echo json_encode(['error' => 'Hubo un error al procesar la solicitud: ' . $e->getMessage()]);
}

?>