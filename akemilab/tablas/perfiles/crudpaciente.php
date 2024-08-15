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
    $perfil = isset($_POST['dni']) ? $_POST['dni'] : '';
    $precio = isset($_POST['nombres']) ? $_POST['nombres'] : '';
    $examenes= isset($_POST['especialidad']) ? $_POST['especialidad'] : '';
    // Preparar el valor para el procedimiento almacenado
    $sql = "CALL p_inperfil('$perfil', '$precio')";

    if (mysqli_multi_query($con, $sql)) {
        // Capturar los resultados del procedimiento almacenado
        do {
            if ($result = mysqli_store_result($con)) {
                while ($row = mysqli_fetch_assoc($result)) {
                    if ($row['mensaje'] == 'El perfil se ingresó correctamente') {
                        $mensaje = $row['mensaje'];
                        echo json_encode(['status' => 'true', 'message' => $mensaje]);
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
    foreach($examenes as $examen){
        $sql2="call in_per_exa($examen)";
        if (mysqli_multi_query($con, $sql2)) {
            // Capturar los resultados del procedimiento almacenado
            do {
                if ($result = mysqli_store_result($con)) {
                    while ($row = mysqli_fetch_assoc($result)) {
                    }
                    // Liberar el resultado al final del ciclo while
                    mysqli_free_result($result);
                }
            } while (mysqli_next_result($con));    
        } else {
            exit();
        }
    }
    
}

// Cerrar conexión
mysqli_close($con);
?>
