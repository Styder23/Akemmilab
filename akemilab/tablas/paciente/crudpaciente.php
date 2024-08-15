<?php


session_start();

// Incluye la conexión a la base de datos
include('../../conexion/conn.php');

// Establecer el encabezado para la respuesta JSON


// Verifica si el usuario está autenticado
// if (!isset($_SESSION['idusuarios'])) {
//     echo json_encode(['status' => 'false', 'message' => 'Usuario no autenticado']);
//     exit(); 
// }

// Obtén el ID del usuario de la sesión
// $id_usuario = $_SESSION['idusuarios'];
// $persona = $_SESSION['Persona'];

// Verificar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger los datos del formulario y sanitizarlos
    $dni = isset($_POST['dni']) ? $_POST['dni'] : '';
    $nombres = isset($_POST['nombres']) ? $_POST['nombres'] : '';
    $apellidos = isset($_POST['apellidos']) ? $_POST['apellidos'] : '';
    $fecha_nacimiento = isset($_POST['edad']) ? $_POST['edad'] : '';
    $direccion = isset($_POST['direccion']) ? $_POST['direccion'] : '';
    $genero = isset($_POST['genero']) ? $_POST['genero'] : '';
    $correo = isset($_POST['correo']) ? $_POST['correo'] : '';
    $ruc = isset($_POST['ruc']) ? $_POST['ruc'] : '';
    $celular = isset($_POST['celular']) ? $_POST['celular'] : '';
    $razon = isset($_POST['razon']) ? $_POST['razon'] : '';
    $dir_emp = isset($_POST['dir_emp']) ? $_POST['dir_emp'] : '';
    // Preparar el valor para el procedimiento almacenado
    $sql = "CALL p_Cpaciente('$dni', '$nombres', '$apellidos', '$fecha_nacimiento','$correo','$celular', '$direccion', '$genero','$ruc','$razon','$dir_emp')";

    if (mysqli_multi_query($con, $sql)) {
        // Capturar los resultados del procedimiento almacenado
        do {
            if ($result = mysqli_store_result($con)) {
                while ($row = mysqli_fetch_assoc($result)) {
                    if ($row['mensaje'] == 'Ya existe una persona con ese DNI') {
                        $mensaje = $row['mensaje'];
                        echo json_encode(['status' => 'false', 'message' => $row['mensaje']]);
                    } else {
                        echo json_encode(['status' => 'true', 'message' => $row['mensaje']]);
                        // Liberar el resultado antes de salir
                        mysqli_free_result($result);
                        exit();
                    }
                }
                // Liberar el resultado al final del ciclo while
                mysqli_free_result($result);
            }
        } while (mysqli_next_result($con));
    } else {
        // Error al llamar al procedimiento almacenado
        echo json_encode(['status' => 'false', 'message' => 'Error al llamar al procedimiento almacenado: ' . mysqli_error($con)]);
        exit();
    }
}

// Cerrar conexión
mysqli_close($con);
?>
