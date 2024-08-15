<?php
// Incluye la conexión a la base de datos
include('../../conexion/conn.php');

// Construye la consulta SQL
$sql = "SELECT * FROM muestra";

// Ejecuta la consulta
$resultado = mysqli_query($con, $sql);

if (!$resultado) {
    die(json_encode(['error' => 'Error en la consulta a la base de datos: ' . mysqli_error($con)]));
}

// Prepara los datos para DataTables
$data = [];
while ($row = mysqli_fetch_assoc($resultado)) {
    $data[] = [
        $row['muestra'],
        '<button class="btn btn-info btn-sm editbtn" data-idarea="' . $row['idmuestra'] . '">Editar</button> ' .
        '<button class="btn btn-danger btn-sm deleteBtn" data-idarea="' . $row['idmuestra'] . '">Eliminar</button>'
    ];
}

// Prepara la respuesta para DataTables
$response = [
    'draw' => intval($_POST['draw']),
    'recordsTotal' => count($data), // Total de registros sin filtrar
    'recordsFiltered' => count($data), // Total de registros después de aplicar el filtro
    'data' => $data // Datos para mostrar en la tabla
];

// Devuelve la respuesta como JSON
echo json_encode($response);
?>
