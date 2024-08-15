<?php
include '../../conexion/conn.php';

// Función para buscar muestras según el término de búsqueda
function buscarMuestras($con, $query) {
    // Escapar caracteres especiales y evitar inyección SQL
    $query = mysqli_real_escape_string($con, $query);

    // Consulta SQL para buscar muestras
    $sql = "SELECT idmuestra AS id, muestra AS nombre
            FROM muestra
            WHERE muestra LIKE '%$query%'";

    $result = mysqli_query($con, $sql);

    if (!$result) {
        echo json_encode(array('error' => 'Error en la consulta SQL: ' . mysqli_error($con)));
        exit();
    }

    $muestras = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $muestras[] = $row;
    }

    return $muestras;
}

// Verificar si se recibió un término de búsqueda
if (isset($_GET['query'])) {
    $query = $_GET['query'];

    // Realizar la búsqueda de muestras
    $muestras = buscarMuestras($con, $query);

    // Devolver resultados como JSON
    echo json_encode(array('muestras' => $muestras));
} else {
    echo json_encode(array('error' => 'No se recibió ningún término de búsqueda.'));
}

// Cerrar conexión a la base de datos al finalizar
mysqli_close($con);
?>
