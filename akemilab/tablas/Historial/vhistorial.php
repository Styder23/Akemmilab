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
    'idexamen',
    'codmuestra',
    'muestra',
    'fecha',
    'codigo',
    'dni',
    'Paciente',
    'tipoexam',
    'estadoexam',
];

// Consulta base
$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM v_historial";
$count_sql = "SELECT COUNT(*) as total FROM v_historial"; // Contar total de registros

// Manejo de búsqueda
$search_value = '';
if (isset($_POST['search']['value']) && $_POST['search']['value'] != '') {
    $search_value = mysqli_real_escape_string($con, $_POST['search']['value']);
    $sql .= " WHERE dni LIKE '%" . $search_value . "%'
              OR codigo LIKE '%" . $search_value . "%'
              OR Paciente LIKE '%" . $search_value . "%'";
}

// Manejo de orden
if (isset($_POST['order'])) {
    $column_index = intval($_POST['order'][0]['column']);
    $column_order = mysqli_real_escape_string($con, $_POST['order'][0]['dir']);
    if (isset($columns[$column_index])) {
        $sql .= " ORDER BY " . $columns[$column_index] . " " . $column_order;
    }
} else {
    $sql .= " ORDER BY idexamen DESC";
}

// Obtener el total de registros filtrados
$total_filtered_result = mysqli_query($con, $count_sql);
$total_filtered = mysqli_fetch_assoc($total_filtered_result)['total'];

// Manejo de paginación
$start = 0;
$length = 10;
if (isset($_POST['start']) && isset($_POST['length']) && $_POST['length'] != -1) {
    $start = intval($_POST['start']);
    $length = intval($_POST['length']);
    $sql .= " LIMIT " . $start . ", " . $length;
}

// Calcular el número de página actual y el rango de registros mostrados
$current_page = ($start / $length) + 1;
$records_start = $start + 1;
$records_end = $start + $length;
if ($records_end > $total_filtered) {
    $records_end = $total_filtered;
}

// Ejecuta la consulta
$result = mysqli_query($con, $sql);
if (!$result) {
    echo json_encode(['error' => 'Error en la consulta: ' . mysqli_error($con)]);
    exit;
}

// Construye el array de datos
$data = [];
$count = $records_start;
while ($row = mysqli_fetch_assoc($result)) {
    $estado_class = '';
    if ($row['estadoexam'] == 'Pendiente') {
        $estado_class = 'estado-pendiente';
    } elseif ($row['estadoexam'] == 'Completo') {
        $estado_class = 'estado-completo';
    }

    $data[] = [
        $row['idexamen'],
        $count,
        $row['codmuestra'],
        $row['muestra'],
        $row['fecha'],
        $row['codigo'],
        $row['dni'],
        $row['Paciente'],
        $row['tipoexam'],
        '<td class="estado-cell"><span class="badge ' . $estado_class . '">' . $row['estadoexam'] . '</span></td>',
        '<td><button class="btn btn-info verBtn" data-idexamen="' . $row['idexamen'] . '">Ver</button></td>',
    ];
    $count++;
}

// Obtener el total de registros sin filtros
$total_result = mysqli_query($con, "SELECT COUNT(*) as total FROM v_historial");
$total_records = mysqli_fetch_assoc($total_result)['total'];

// Prepara la respuesta
$output = [
    'draw' => intval($_POST['draw']),
    'recordsTotal' => $total_records,
    'recordsFiltered' => $total_filtered,
    'data' => $data,
    'pageInfo' => [
        'currentPage' => $current_page,
        'recordsStart' => $records_start,
        'recordsEnd' => $records_end,
        'totalPages' => ceil($total_filtered / $length)
    ]
];

// Devuelve la respuesta como JSON
echo json_encode($output);
?>
