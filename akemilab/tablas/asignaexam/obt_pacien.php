<?php
include '../../conexion/conn.php';

// Usar la conexión establecida en conn.php
$db = $con;

if (isset($_POST['dni'])) {
    $input = $_POST['dni'];
    $valor = array();
    $valor['existe'] = "0";

    // Verificar si la entrada es DNI (solo dígitos) o código (podría incluir letras)
    if (ctype_digit($input) && strlen($input) == 8) {
        $query = "SELECT * FROM pacientes d 
                  INNER JOIN personas p ON d.fk_idpersonas = p.idpersonas
                  WHERE p.dni = '$input'";
    } else {
        $query = "SELECT * FROM pacientes d 
                  INNER JOIN personas p ON d.fk_idpersonas = p.idpersonas
                  WHERE p.codigo LIKE '%$input%'";
    }

    $result = mysqli_query($db, $query);

    while ($consulta = mysqli_fetch_array($result)) {
        $valor['existe'] = "1";
        $valor['idpacientes'] = $consulta['idpacientes'];
        $valor['Nombre'] = $consulta['Nombre'];
        $valor['Apellido'] = $consulta['Apellido'];
        $valor['ruc'] = $consulta['ruc'];
        // Agregar otros campos según sea necesario
    }

    echo json_encode($valor);
}
?>
