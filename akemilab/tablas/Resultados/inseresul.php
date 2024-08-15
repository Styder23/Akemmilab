<?php
date_default_timezone_set('America/Lima');
session_start();

// Incluir el archivo de conexión
include '../../conexion/config.php';

$fkusuario = $_SESSION['idusuario'];
// Datos del formulario
$fk_examen = $_POST['fk_examen'];
$datos = $_POST['datos']; // Suponiendo que $datos es un arreglo de resultados

// Iniciar la transacción
$conectar->begin_transaction();

try {
    // Preparar la sentencia para la inserción
    $stmt = $conectar->prepare("INSERT INTO resultados (valores, fecharesul, medoto, fk_idnommedible, fk_idUsuario, fk_idexamen) VALUES (?, ?, ?, ?, ?, ?)");
    
    if ($stmt === false) {
        throw new Exception("Error preparando la consulta: " . $conectar->error);
    }

    // Recorrer los datos y ejecutar la inserción
    foreach ($datos as $dato) {
        if (!isset($dato['idnommedible'])) {
            throw new Exception("ID de nommedible no definido.");
        }

        // Validar y sanitizar los datos si es necesario
        $valor = $dato['valor_medida']; // No usar intval, ya que ahora es VARCHAR
        $fecharesul = isset($dato['fecharesul']) ? $dato['fecharesul'] : date('Y-m-d H:i:s'); // Usa la fecha del formulario o la actual
        $metodo = !empty($dato['metodo']) ? $dato['metodo'] : null; // Permitir NULL para metodos
        $fk_idnommedible = intval($dato['idnommedible']);
        
        // Vincular parámetros y ejecutar la consulta
        $stmt->bind_param(
            "sssiii",
            $valor,
            $fecharesul,
            $metodo,
            $fk_idnommedible,
            $fkusuario,
            $fk_examen,
        );

        if (!$stmt->execute()) {
            throw new Exception("Error ejecutando la inserción: " . $stmt->error);
        }

        // Debugging
       // echo "Insertado: valor_medida = $valor_medida, unidades = $unidades, metodos = $metodo, nommedible = $fk_idnommedible, examen = $fk_examen<br>";
    }
    
    // Actualizar el estado del examen
    $update_stmt = $conectar->prepare("UPDATE examen SET fk_idestadoexam = 2 WHERE idexamen = ?");
    
    if ($update_stmt === false) {
        throw new Exception("Error preparando la actualización: " . $conectar->error);
    }
    
    $update_stmt->bind_param("i", $fk_examen);
    
    if (!$update_stmt->execute()) {
        throw new Exception("Error ejecutando la actualización: " . $update_stmt->error);
    }
    
    // Confirmar la transacción
    $conectar->commit();
    
    echo "Resultados ingresados correctamente.";
} catch (Exception $e) {
    // Revertir la transacción en caso de error
    $conectar->rollback();
    echo "Error: " . $e->getMessage();
}

// Cerrar la conexión
$conectar->close();
?>
