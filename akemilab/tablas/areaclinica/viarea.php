<?php
// Incluye la conexión a la base de datos
include('../../conexion/conn.php');

// Manejo de paginación
$start = 0;
$length = 10; // Default length
if (isset($_POST['start']) && isset($_POST['length'])) {
    $start = intval($_POST['start']);
    $length = intval($_POST['length']);
}

// Construye la consulta SQL
$sql = "SELECT * FROM area LIMIT $start, $length";

// Ejecuta la consulta
$resultado = mysqli_query($con, $sql);

if (!$resultado) {
    die(json_encode(['error' => 'Error en la consulta a la base de datos: ' . mysqli_error($con)]));
}

// Prepara los datos para DataTables
$data = [];
$count = $start + 1; // Enumeración de datos desde el inicio de la página actual
while ($row = mysqli_fetch_assoc($resultado)) {
    $data[] = [
        $count,
        $row['nomarea'],
        '<button class="btn btn-info btn-sm editbtn" data-idarea="' . $row['idarea'] . '">Editar</button> ' .
        '<button class="btn btn-danger btn-sm deleteBtn" data-idarea="' . $row['idarea'] . '">Eliminar</button>'
    ];
    $count++;
}

// Obtener el total de registros sin filtros
$total_result = mysqli_query($con, "SELECT COUNT(*) as total FROM area");
$total_records = mysqli_fetch_assoc($total_result)['total'];

// Obtener el total de registros filtrados (en este caso, sin filtros adicionales)
$total_filtered = $total_records;

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
