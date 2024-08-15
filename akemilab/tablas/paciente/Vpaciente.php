<?php
include('../../conexion/conn.php');
// Define las columnas disponibles
$columns = [
    'ID', 'dni','codigo' ,'PACIENTE', 'fecha_nacimiento','edad', 'GENERO', 'correo', 'direccion', 'celular', 'ruc'
];

// Consulta base
$sql = "SELECT * FROM v_paciente";
$count_sql = "SELECT COUNT(*) as total FROM v_paciente"; // Contar total de registros

// Manejo de búsqueda
$search_value = '';
if (isset($_POST['search']['value']) && !empty($_POST['search']['value'])) {
    $search_value = mysqli_real_escape_string($con, $_POST['search']['value']);
    $sql .= " WHERE dni LIKE '%" . $search_value . "%' OR PACIENTE LIKE '%" . $search_value . "%' OR codigo LIKE '%" . $search_value . "%' OR fecha_nacimiento LIKE '%" . $search_value . "%'";
    $count_sql .= " WHERE dni LIKE '%" . $search_value . "%' OR PACIENTE LIKE '%" . $search_value . "%' OR codigo LIKE '%" . $search_value . "%' OR fecha_nacimiento LIKE '%" . $search_value . "%'";
}

// Manejo de orden
if (isset($_POST['order'])) {
    $column_index = intval($_POST['order'][0]['column']);
    $column_order = mysqli_real_escape_string($con, $_POST['order'][0]['dir']);
    $sql .= " ORDER BY " . $columns[$column_index] . " " . $column_order;
} else {
    $sql .= " ORDER BY idpacientes DESC";
}

// Obtener el total de registros filtrados
$total_filtered_result = mysqli_query($con, $count_sql);
$total_filtered = mysqli_fetch_assoc($total_filtered_result)['total'];

// Manejo de paginación
$start = 0;
$length = 10; // Default length
if (isset($_POST['length']) && $_POST['length'] != -1) {
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
    $data[] = [
        $count,
        $row['dni'],       
        $row['PACIENTE'],
        $row['codigo'],
        $row['fecha_nacimiento'],
        $row['edad'],
        $row['GENERO'],
        $row['correo'],
        $row['celular'],
        $row['ruc'],
        '<div class="button-container" style="display: flex; gap: 5px;">' .
        '<button class="btn btn-info btn-sm editbtn" data-idpacientes="' . $row['ID'] . '" data-dni="'.$row['dni'].'">Editar</button> ' .
        '<button class="btn btn-danger btn-sm deleteBtn" data-idpacientes="' . $row['ID'] .  '" data-dni="'.$row['dni'].'">Eliminar</button>'
    ];
    $count++;
}

// Obtener el total de registros sin filtros
$total_result = mysqli_query($con, "SELECT COUNT(*) as total FROM v_paciente");
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
