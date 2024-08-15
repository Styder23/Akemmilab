<?php
session_start();

// Incluye la conexión a la base de datos
include('../../conexion/conn.php');

// Verifica si el usuario está autenticado
// if (!isset($_SESSION['idusuario'])) {
//     echo json_encode(['status' => 'false', 'message' => 'Usuario no autenticado']);
//     exit(); 
// }

// // Obtén el ID del usuario de la sesión
// $id_usuario = $_SESSION['idusuario'];
// $persona = $_SESSION['Persona'];

// Verificar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger los datos del formulario
    $area = isset($_POST['area']) ? $_POST['area'] : '';

    // Llamar al procedimiento almacenado
    $sql = "call insertar_area('$area')";

    if (mysqli_multi_query($con, $sql)) {
        // Capturar los resultados del procedimiento almacenado
        do {
            if ($result = mysqli_store_result($con)) {
                while ($row = mysqli_fetch_assoc($result)) {
                    if ($row['mensaje'] == 'Área ingresada correctamente') {
                        $mensaje = $row['mensaje'];
                        echo json_encode(['status' => 'true','message'=>$mensaje]);
                    } else {
                        echo json_encode(['status' => 'false', 'message' => $row['mensaje']]);
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