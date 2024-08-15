<?php
include('../../conexion/conn.php');

// Habilitar la visualización de errores PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Establecer la codificación de caracteres a UTF-8
mysqli_set_charset($con, 'utf8');

// Define las columnas disponibles
$columns = [
    'idorden',
    'codorden',
    'fecha',
];

// Consulta base
$sql = "SELECT * from ordenclinico";

// Obtener el total de registros sin filtro
$totalRecordsResult = mysqli_query($con, "SELECT COUNT(*) AS total FROM ordenclinico");
$totalRecordsCount = mysqli_fetch_assoc($totalRecordsResult)['total'];

// Manejo de búsqueda
$search_sql = "";
if (isset($_POST['search']['value']) && $_POST['search']['value'] != '') {
    $search_value = mysqli_real_escape_string($con, $_POST['search']['value']);
    $search_sql = " WHERE fecha LIKE '%" . $search_value . "%' 
                    OR codorden LIKE '%" . $search_value . "%'";
    $sql .= $search_sql;
}

// Obtener el total de registros filtrados
$filteredRecordsResult = mysqli_query($con, "SELECT COUNT(*) AS total FROM ordenclinico                                  
                                             $search_sql");
$filteredRecordsCount = mysqli_fetch_assoc($filteredRecordsResult)['total'];

// Manejo de orden
$order_sql = " ORDER BY idorden DESC"; // Orden por defecto
if (isset($_POST['order'])) {
    $column_index = intval($_POST['order'][0]['column']);
    $column_order = mysqli_real_escape_string($con, $_POST['order'][0]['dir']);
    if (isset($columns[$column_index])) {
        $order_sql = " ORDER BY " . $columns[$column_index] . " " . $column_order;
    }
}
$sql .= $order_sql;

// Manejo de paginación
$limit_sql = " LIMIT 0, 10"; // Paginación por defecto
if (isset($_POST['start']) && isset($_POST['length']) && $_POST['length'] != -1) {
    $start = intval($_POST['start']);
    $length = intval($_POST['length']);
    $limit_sql = " LIMIT " . $start . ", " . $length;
}
$sql .= $limit_sql;

// Capturar cualquier salida accidental
ob_start();

// Ejecuta la consulta
$result = mysqli_query($con, $sql);
if (!$result) {
    echo json_encode(['error' => 'Error en la consulta: ' . mysqli_error($con)]);
    exit;
}

// Construye el array de datos
$data = [];
$count = $start + 1; // Enumeración de datos desde el inicio de la página actual
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = [
        $count,
        $row['codorden'],
        $row['fecha'],
        '<button class="btn btn-success btn-sm verBtn" data-codorden="' . $row['codorden'] . '">Imprimir Result</button> '
    ];
    $count++;
}

// Prepara la respuesta
$output = [
    'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
    'recordsTotal' => intval($totalRecordsCount),
    'recordsFiltered' => intval($filteredRecordsCount),
    'data' => $data
];

// Limpiar el búfer de salida y obtener su contenido
$buffer = ob_get_clean();

// Devuelve la respuesta como JSON
echo json_encode($output);

// Verificar si el búfer contiene algo y registrarlo para la depuración
if (!empty($buffer)) {
    error_log('Buffer Output: ' . $buffer);
}

mysqli_close($con);
?>
