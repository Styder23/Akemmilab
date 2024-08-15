<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../conexion/conec.php';
$obj = new ConexionDB("localhost", "root", "root", "akemilab2");
$db = $obj->conectar();

if (isset($_POST['dni'])) {
    $dni = $_POST['dni'];
    $valor = array();
    $valor['existe'] = "0";

    // Consulta SQL para obtener los datos del paciente según el DNI
    $query = "SELECT p.*, g.*, m.*, u.*
    FROM personas p
    LEFT JOIN genero g ON g.idgenero = p.fk_idgenero
    LEFT JOIN medicos m ON p.idpersonas = m.fk_personas
    LEFT JOIN usuario u ON p.idpersonas = u.fk_idpersonas
    WHERE p.dni = '$dni'";

    // Imprimir la consulta para depuración
    error_log("Consulta SQL: " . $query);

    $result = mysqli_query($db, $query);

    if ($result) {
        // Imprimir el número de filas obtenidas
        error_log("Número de filas obtenidas: " . mysqli_num_rows($result));

        if (mysqli_num_rows($result) > 0) {
            $consulta = mysqli_fetch_assoc($result);
            $valor['existe'] = "1";
            $valor['Nombre'] = $consulta['Nombre'];
            $valor['Apellido'] = $consulta['Apellido'];
            $valor['direccion'] = $consulta['direccion'];
            $valor['genero'] = $consulta['genero'];
            $valor['correo'] = $consulta['correo'];
            $valor['celular'] = $consulta['celular'];
            $valor['ruc'] = $consulta['ruc'];
            $valor['razon'] = $consulta['razon'];
            $valor['dir_emp'] = $consulta['dir_emp'];
        } else {
            $valor['error'] = "DNI no encontrado.";
        }
    } else {
        $valor['error'] = "Error en la consulta: " . mysqli_error($db);
    }

    echo json_encode($valor);
} else {
    $valor = array('error' => 'No se proporcionó un DNI.');
    echo json_encode($valor);
}
?>
