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
    'idcaja',
    'hora_apertura',
    'hora_cierre',
    'montoini',
    'importe',
    'estadocj',
    'nomusu',
];

// Consulta base
$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM v_caja";

// Manejo de búsqueda
if (isset($_POST['search']['value']) && $_POST['search']['value'] != '') {
    $search_value = mysqli_real_escape_string($con, $_POST['search']['value']);
    $sql .= " WHERE hora_apertura LIKE '%" . $search_value . "%'                 
             OR estadocj LIKE '%" . $search_value . "%'                 
             OR nomusu LIKE '%" . $search_value. "%'";
}

// Manejo de orden
if (isset($_POST['order'])) {
    $column_index = intval($_POST['order'][0]['column']);
    $column_order = mysqli_real_escape_string($con, $_POST['order'][0]['dir']);
    if (isset($columns[$column_index])) {
        $sql .= " ORDER BY " . $columns[$column_index] . " " . $column_order;
    }
} else {
    $sql .= " ORDER BY idcaja DESC";
}

// Manejo de paginación
if (isset($_POST['start']) && isset($_POST['length']) && $_POST['length'] != -1) {
    $start = intval($_POST['start']);
    $length = intval($_POST['length']);
    $sql .= " LIMIT " . $start . ", " . $length;
}

// Ejecuta la consulta para obtener los datos
$result = mysqli_query($con, $sql);
if (!$result) {
    echo json_encode(['error' => 'Error en la consulta: ' . mysqli_error($con)]);
    exit;
}

// Construir el array de datos
$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = [
        $row['idcaja'],
        $row['hora_apertura'],
        $row['hora_cierre'],
        $row['montoini'],
        $row['importe'],
        $row['estadocj'],
        $row['nomusu'],
        '<button type="button" class="btn btn-success btn-sm verBtn" data-idcaja="' . $row['idcaja'] . '">Ver</button>'
    ];
}

// Obtener el total de registros sin filtrar
$totalRecordsResult = mysqli_query($con, "SELECT COUNT(*) AS total FROM v_caja");
$totalRecordsCount = mysqli_fetch_assoc($totalRecordsResult)['total'];

// Preparar la respuesta para DataTables
$output = [
    'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
    'recordsTotal' => intval($totalRecordsCount),
    'recordsFiltered' => intval($totalRecordsCount), // Misma cantidad si no hay filtro
    'data' => $data
];

// Devolver la respuesta como JSON
echo json_encode($output);

// Cerrar la conexión
mysqli_close($con);
?>
