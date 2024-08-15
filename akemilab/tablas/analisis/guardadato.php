<?php
include '../../conexion/conn.php';

// Obtener el JSON del cuerpo de la solicitud
$data = json_decode(file_get_contents('php://input'), true);

$datosArray = $data['datosArray'];

// Preparar la declaración para insertar en la tabla nommedible
$sql = "INSERT INTO nommedible (nommedi, rangomin, rangomax, fk_idtipoexamen, fk_idnombretit, fk_idunidades) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $con->prepare($sql);
if ($stmt === false) {
    die("Error en la preparación de la declaración: " . $con->error);
}

foreach ($datosArray as $dato) {
    $analisis = $dato['analisis'];
    $nommedi = $dato['datos'];
    list($rangomin, $rangomax) = explode('-', $dato['valores']);
    $fk_idtipoexamen = $dato['texamen'];
    $unidades = $dato['unidades'] !== "" ? $dato['unidades'] : NULL;
    
    // Determinar el idanalisis
    if ($analisis) {
        // Verificar si el análisis ya existe
        $sqlCheck = "SELECT idnombretit FROM nombretit WHERE nomtit = ?";
        $stmtCheck = $con->prepare($sqlCheck);
        if ($stmtCheck === false) {
            die("Error en la preparación de la declaración: " . $con->error);
        }
        $stmtCheck->bind_param('s', $analisis);
        $stmtCheck->execute();
        $result = $stmtCheck->get_result();

        if ($result->num_rows > 0) {
            // Obtener el id del análisis existente
            $row = $result->fetch_assoc();
            $idanalisis = $row['idnombretit'];
        } else {
            // Insertar el nuevo análisis
            $sqlInsert = "INSERT INTO nombretit (nomtit) VALUES (?)";
            $stmtInsert = $con->prepare($sqlInsert);
            if ($stmtInsert === false) {
                die("Error en la preparación de la declaración: " . $con->error);
            }
            $stmtInsert->bind_param('s', $analisis);
            $stmtInsert->execute();
            $idanalisis = $stmtInsert->insert_id;
        }
    } else {
        $idanalisis = NULL;
    }

    $stmt->bind_param('sssiii', $nommedi, $rangomin, $rangomax, $fk_idtipoexamen, $idanalisis, $unidades);
    $stmt->execute();
}

$con->close();

echo json_encode(['success' => true]);
?>
