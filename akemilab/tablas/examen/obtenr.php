<?php
include('../../conexion/conn.php');

// Define las columnas disponibles
$columns = [
    'idtipoexamen',
    'disponibilidad',
    'tipoexam',
    'precio',
    'nomarea'
];

// Consulta base para obtener el total de registros sin filtro
$totalRecordsSql = "SELECT COUNT(*) AS total FROM v_tipoexamen";
$totalRecordsResult = mysqli_query($con, $totalRecordsSql);
$totalRecords = mysqli_fetch_assoc($totalRecordsResult)['total'];

// Consulta base para obtener los registros filtrados
$sql = "SELECT * FROM v_tipoexamen WHERE disponibilidad = 1";

// Manejo de búsqueda
if (isset($_POST['search']['value']) && $_POST['search']['value'] != '') {
    $search_value = mysqli_real_escape_string($con, $_POST['search']['value']);
    $sql .= " AND (tipoexam LIKE '%" . $search_value . "%' OR nomarea LIKE '%" . $search_value . "%')";
}

// Obtener el total de registros filtrados
$filteredRecordsSql = str_replace("SELECT *", "SELECT COUNT(*) AS total", $sql);
$filteredRecordsResult = mysqli_query($con, $filteredRecordsSql);
$filteredRecords = mysqli_fetch_assoc($filteredRecordsResult)['total'];

// Manejo de orden
if (isset($_POST['order'])) {
    $column_index = intval($_POST['order'][0]['column']);
    $column_order = mysqli_real_escape_string($con, $_POST['order'][0]['dir']);
    $sql .= " ORDER BY " . $columns[$column_index] . " " . $column_order;
} else {
    $sql .= " ORDER BY idtipoexamen DESC";
}

// Manejo de paginación
if (isset($_POST['start'], $_POST['length']) && $_POST['length'] != -1) {
    $start = intval($_POST['start']);
    $length = intval($_POST['length']);
    $sql .= " LIMIT " . $start . ", " . $length;
}

// Ejecuta la consulta para obtener los datos paginados
$result = mysqli_query($con, $sql);
if (!$result) {
    echo json_encode(['error' => 'Error en la consulta: ' . mysqli_error($con)]);
    exit;
}

// Construye el array de datos
$data = [];
$count = $start + 1; // Enumeración correcta
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = [
        $count,
        $row['tipoexam'],
        $row['precio'],
        $row['nomarea'],
        '<button class="btn btn-info btn-sm editbtn" data-idtipoexamen="' . $row['idtipoexamen'] . '">Editar</button> ' .
        '<button class="btn btn-danger btn-sm deleteBtn" data-idtipoexamen="' . $row['idtipoexamen'] . '">Eliminar</button>'
    ];
    $count++;
}

// Prepara la respuesta
$output = [
    'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 1,
    'recordsTotal' => $totalRecords,
    'recordsFiltered' => $filteredRecords,
    'data' => $data
];

// Devuelve la respuesta como JSON
echo json_encode($output);
?>
