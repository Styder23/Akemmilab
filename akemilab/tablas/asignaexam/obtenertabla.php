<?php
include('../../conexion/conn.php');

// Define las columnas disponibles
$columns = [
    'ID',
    'dni',
    'Código_pa',
    'Paciente',
    'codmuestra',
    'fecha',
    'tipoexam',
    'Perfil',
    'estadoexam',
];

// Inicializa las variables de paginación
$start = 0;
$length = 10;

// Verifica los parámetros de paginación de DataTables
if (isset($_POST['start']) && $_POST['start'] != '') {
    $start = intval($_POST['start']);
}

if (isset($_POST['length']) && $_POST['length'] != -1) {
    $length = intval($_POST['length']);
}

// Consulta base
$sql = "SELECT * FROM v_exam";

// Manejo de búsqueda
if (isset($_POST['search']['value']) && $_POST['search']['value'] != '') {
    $search_value = mysqli_real_escape_string($con, $_POST['search']['value']);
    $sql .= " WHERE dni LIKE '%" . $search_value . "%' 
              OR Paciente LIKE '%" . $search_value . "%' 
              OR Código_pa LIKE '%" . $search_value . "%' 
              OR codmuestra LIKE '%" . $search_value . "%'
              OR Perfil LIKE '%" . $search_value . "%' 
              OR tipoexam LIKE '%" . $search_value . "%'";
}

// Manejo de orden
if (isset($_POST['order'])) {
    $column_index = intval($_POST['order'][0]['column']);
    $column_order = mysqli_real_escape_string($con, $_POST['order'][0]['dir']);
    $sql .= " ORDER BY " . $columns[$column_index] . " " . $column_order;
} else {
    $sql .= " ORDER BY ID DESC";
}

// Agrega límites para paginación
$sql .= " LIMIT " . $start . ", " . $length;

// Ejecuta la consulta
$result = mysqli_query($con, $sql);
if (!$result) {
    echo json_encode(['error' => 'Error en la consulta: ' . mysqli_error($con)]);
    exit;
}

// Inicializa un contador
$counter = $start + 1;

// Construye el array de datos
$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Agregar estilo condicional para la columna ESTADO
    $estado_class = ($row['estadoexam'] == 'Pendiente') ? 'text-success fw-bold' : '';
    // Construir la fila de datos con el estilo condicional aplicado
    $data[] = [
        $counter++, // Incrementa y muestra el contador
        $row['dni'],
        $row['Código_pa'],
        $row['Paciente'],
        $row['codmuestra'],
        $row['fecha'],
        $row['tipoexam'],
        $row['Perfil'],
        '<span class="' . $estado_class . '">' . $row['estadoexam'] . '</span>',
        '<button class="btn btn-info btn-sm editbtn" data-idexamen="' . $row['ID'] . '">Editar</button> ' .
        '<button class="btn btn-danger btn-sm deleteBtn" data-idexamen="' . $row['ID'] . '">Eliminar</button> ' .
        '<button class="btn btn-warning btn-sm analizaBtn" data-idexamen="' . $row['ID'] . '">Analizar</button>'
    ];
}

// Obtener el total de registros sin filtro
$totalRecordsResult = mysqli_query($con, "SELECT COUNT(*) AS total FROM v_exam");
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
