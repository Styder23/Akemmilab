<?php
include('../../conexion/conn.php');
// Define las columnas disponibles
$columns = [
    'idmedicos', 'dni', 'medico', 'colegiatura', 'genero', 'fecha_nacimiento','edad', 'celular', 'direccion', 'correo','lugares'
];

// Consulta base
$sql = "SELECT * FROM v_medicos";
$count_sql = "SELECT COUNT(*) as total FROM v_medicos"; // Contar total de registros

// Manejo de búsqueda
$search_value = '';
if (isset($_POST['search']['value']) && !empty($_POST['search']['value'])) {
    $search_value = mysqli_real_escape_string($con, $_POST['search']['value']);
    $sql .= " WHERE dni LIKE '%" . $search_value . "%' OR medico LIKE '%" . $search_value . "%' OR colegiatura LIKE '%" . $search_value . "%' OR edad LIKE '%" . $search_value . "%'";
    $count_sql .= " WHERE dni LIKE '%" . $search_value . "%' OR medico LIKE '%" . $search_value . "%' OR colegiatura LIKE '%" . $search_value . "%' OR edad LIKE '%" . $search_value . "%'";
}

// Manejo de orden
if (isset($_POST['order'])) {
    $column_index = intval($_POST['order'][0]['column']);
    $column_order = mysqli_real_escape_string($con, $_POST['order'][0]['dir']);
    $sql .= " ORDER BY " . $columns[$column_index] . " " . $column_order;
} else {
    $sql .= " ORDER BY idmedicos DESC";
}

// Obtener el total de registros filtrados
$total_filtered_result = mysqli_query($con, $count_sql);
$total_filtered = mysqli_fetch_assoc($total_filtered_result)['total'];

// Manejo de paginación
if (isset($_POST['length']) && $_POST['length'] != -1) {
    $start = intval($_POST['start']);
    $length = intval($_POST['length']);
    $sql .= " LIMIT " . $start . ", " . $length;
}

// Ejecuta la consulta
$result = mysqli_query($con, $sql);
if (!$result) {
    echo json_encode(['error' => 'Error en la consulta: ' . mysqli_error($con)]);
    exit;
}

// Construye el array de datos
$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = [
        $row['dni'],
        $row['medico'],
        $row['colegiatura'],
        $row['lugares'],
        $row['fecha_nacimiento'],
        $row['edad'],
        $row['genero'],
        $row['correo'],
        $row['celular'],
        '<div class="button-container" style="display: flex; gap: 5px;">' .
        '<button class="btn btn-info btn-sm editbtn" data-idpacientes="' . $row['idmedicos'] . '" data-dni="'.$row['dni'].'">Editar</button> ' .
        '<button class="btn btn-danger btn-sm deleteBtn" data-idpacientes="' . $row['idmedicos'] .  '" data-dni="'.$row['dni'].'">Eliminar</button>'
    ];
}

// Obtener el total de registros sin filtros
$total_result = mysqli_query($con, "SELECT COUNT(*) as total FROM v_medicos");
$total_records = mysqli_fetch_assoc($total_result)['total'];

// Prepara la respuesta
$output = [
    'draw' => intval($_POST['draw']),
    'recordsTotal' => $total_records,
    'recordsFiltered' => $total_filtered,
    'data' => $data
];

// Devuelve la respuesta como JSON
echo json_encode($output);
?>


