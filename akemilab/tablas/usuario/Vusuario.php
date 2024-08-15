<?php
include('../../conexion/conn.php');

// Define las columnas disponibles
$columns = [
    'idusuario', 
    'dni', 
    'usuario', 
    'genero',
    'fecha_nacimiento', 
    'edad',
    'celular', 
    'direccion', 
    'correo',
    'tipousu',
    'nomusu',
];

// Consulta base
$sql = "SELECT * FROM Vusuario";

// Manejo de búsqueda
if (isset($_POST['search']['value'])) {
    $search_value = mysqli_real_escape_string($con, $_POST['search']['value']);
    $sql .= " WHERE dni LIKE '%" . $search_value . "%' OR usuario LIKE '%" . $search_value . "%' OR tipousu LIKE '%" . $search_value . "%' OR usuario LIKE '%" . $search_value . "%'";
}

// Obtener el total de registros sin filtros
$total_result = mysqli_query($con, "SELECT COUNT(*) as total FROM Vusuario");
$total_records = mysqli_fetch_assoc($total_result)['total'];

// Obtener el total de registros filtrados
$count_sql = "SELECT COUNT(*) as total FROM Vusuario";
if (isset($_POST['search']['value']) && $_POST['search']['value'] != '') {
    $count_sql .= " WHERE dni LIKE '%" . $search_value . "%' OR usuario LIKE '%" . $search_value . "%' OR tipousu LIKE '%" . $search_value . "%' OR usuario LIKE '%" . $search_value . "%'";
}
$total_filtered_result = mysqli_query($con, $count_sql);
$total_filtered = mysqli_fetch_assoc($total_filtered_result)['total'];

// Manejo de orden
if (isset($_POST['order'])) {
    $column_index = intval($_POST['order'][0]['column']);
    $column_order = mysqli_real_escape_string($con, $_POST['order'][0]['dir']);
    $sql .= " ORDER BY " . $columns[$column_index] . " " . $column_order;
} else {
    $sql .= " ORDER BY idusuario DESC";
}

// Manejo de paginación
$start = 0;
$length = 10; // Default length
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
$count = $start + 1; // Enumeración de datos desde el inicio de la página actual
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = [
        $count,
        $row['dni'],
        $row['usuario'],
        $row['genero'],
        $row['fecha_nacimiento'],
        $row['edad'],
        $row['celular'],
        $row['direccion'],
        $row['correo'],
        $row['tipousu'],
        $row['nomusu'],
        '<button class="btn btn-info btn-sm editbtn" data-idusuario="' . $row['idusuario'] . '">Editar</button> ' .
        '<button class="btn btn-danger btn-sm deleteBtn" data-idusuario="' . $row['idusuario'] . '">Eliminar</button>'
    ];
    $count++;
}

// Prepara la respuesta
$output = [
    'draw' => intval($_POST['draw']),
    'recordsTotal' => $total_records,
    'recordsFiltered' => $total_filtered,
    'data' => $data
];

// Imprime la salida JSON para depuración
echo json_encode($output);
?>
