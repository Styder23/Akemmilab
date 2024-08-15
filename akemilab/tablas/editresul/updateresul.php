<?php
include '../../conexion/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener los datos del formulario
    $datos = $_POST['datos'];

    // Preparar la consulta para actualizar los resultados
    $stmt = $conectar->prepare("UPDATE resultados SET valores = ?, medoto = ? WHERE idresultados = ?");

    foreach ($datos as $idnommedible => $dato) {
        // Obtener los valores necesarios
        $valor_medida = $dato['valor_medida'];
        $metodo = $dato['metodo'];
        $idresultados = $dato['idresultados'];

        // Ejecutar la consulta con los datos actuales
        $stmt->bind_param('ssi', $valor_medida, $metodo, $idresultados);
        $stmt->execute();
    }

    // Verificar si la actualización fue exitosa
    if ($stmt->affected_rows > 0) {
        echo "¡Los resultados se actualizaron correctamente!";
    } else {
        echo "Hubo un error al actualizar los resultados.";
    }

    // Cerrar la conexión y liberar recursos
    $stmt->close();
    $conectar->close();
} else {
    // Método de solicitud no válido
    echo "Acceso denegado.";
}
?>
