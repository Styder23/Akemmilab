<?php
include '../../conexion/conn.php';

// Usar la conexión establecida en conn.php
$db = $con;

if (isset($_POST['buscar1'])) {
    $input = $_POST['buscar1'];
    $valor = array();
    $valor['existe1'] = "0";

    // Verificar si la entrada es DNI (solo dígitos) o código (podría incluir letras)
    if (ctype_digit($input) && strlen($input) == 8) {
        $query = "SELECT * FROM medicos d 
                 INNER JOIN personas p ON p.idpersonas=d.fk_personas
                 WHERE p.dni = '$input'";
    } else {
        $query = "SELECT * FROM medicos d 
                    INNER JOIN personas p ON p.idpersonas=d.fk_personas
                    WHERE d.colegiatura LIKE '%$input%'";
    }

    $result = mysqli_query($db, $query);

    while ($consulta = mysqli_fetch_array($result)) {
        $valor['existe1'] = "1";
        $valor['idmedicos'] = $consulta['idmedicos'];
        $valor['Nombre'] = $consulta['Nombre'];
        $valor['Apellido'] = $consulta['Apellido'];
        // Agregar otros campos según sea necesario
    }

    echo json_encode($valor);
}
?>
