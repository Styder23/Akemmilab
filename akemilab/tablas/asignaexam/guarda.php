<?php
date_default_timezone_set('America/Lima');
include '../../conexion/conn.php';

$con->autocommit(FALSE); // Desactivar autocommit

function getCajaAbierta($con) {
    $sql = "SELECT idcaja FROM caja WHERE hora_cierre IS NULL ORDER BY hora_apertura DESC LIMIT 1";
    $stmt = $con->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['idcaja'];
    } else {
        return false;
    }
}

$idCajaAbierta = getCajaAbierta($con);

if (!$idCajaAbierta) {
    echo json_encode(['success' => false, 'message' => 'Inicie caja antes de realizar registros.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data)) {
    echo json_encode(['success' => false, 'message' => 'No hay datos en el JSON.']);
    exit();
}

try {
    // Insertar en la tabla comprobante
    $fechacompro = $data[0]['fecha'];
    $total = $data[0]['totalConDescuento'];
    $fk_tipocom = $data[0]['tipoComprobante'];
    $destotal = $data[0]['descuentotot'] !== "" ? $data[0]['descuentotot'] : 0;
    $fk_idcaja = $idCajaAbierta;

    $stmt = $con->prepare("INSERT INTO comprobante (fechacompro, total, fk_tipocom, fk_idcaja, destotal) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('sdiii', $fechacompro, $total, $fk_tipocom, $fk_idcaja, $destotal);
    if (!$stmt->execute()) {
        throw new Exception('Error al insertar comprobante: ' . $stmt->error);
    }
    $id_comprobante = $stmt->insert_id;

    // Insertar en la tabla ordenclinico
    $result = $con->query("SELECT generar_codigo_orden() AS codorden");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $codorden = $row['codorden'];
    } else {
        throw new Exception('Error al generar código de orden: ' . $con->error);
    }

    $stmt = $con->prepare("INSERT INTO ordenclinico (codorden, fecha) VALUES (?, ?)");
    $stmt->bind_param('ss', $codorden, $fechacompro);
    if (!$stmt->execute()) {
        throw new Exception('Error al insertar orden clinico: ' . $stmt->error);
    }
    $id_orden = $stmt->insert_id;

    foreach ($data as $examen_data) {
        $fecha = $examen_data['fecha'];
        $fk_idtipoexamen = $examen_data['examen']['id'];
        $fk_idpacientes = $examen_data['idpaciente'];
        $fk_idestadoexam = 1; // Assuming initial state is 1
        $fk_muestra = $examen_data['muestras'][0]['id'];
        $fk_medico = isset($examen_data['idmedico']) ? $examen_data['idmedico'] : null;
        $fk_perfil = isset($examen_data['examen']['perfilId']) && $examen_data['examen']['perfilId'] !== "" ? $examen_data['examen']['perfilId'] : null;

        $result = $con->query("SELECT cod_muestra($fk_idpacientes) AS codmuestra");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $codmuestra = $row['codmuestra'];
        } else {
            throw new Exception('Error al generar código de muestra: ' . $con->error);
        }

        $stmt = $con->prepare("INSERT INTO examen (codmuestra, fecha, fk_idtipoexamen, fk_idpacientes, fk_idestadoexam, fk_muestra, fk_medico, fk_perfil) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssiiisis', $codmuestra, $fecha, $fk_idtipoexamen, $fk_idpacientes, $fk_idestadoexam, $fk_muestra, $fk_medico, $fk_perfil);
        if (!$stmt->execute()) {
            throw new Exception('Error al insertar examen: ' . $stmt->error);
        }
        $id_examen = $stmt->insert_id;

        // Generar código de atención
        $result = $con->query("SELECT generar_codigo_atencion() AS codigo_atencion");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $codigo_atencion = $row['codigo_atencion'];
        } else {
            throw new Exception('Error al generar código de atención: ' . $con->error);
        }

        // Insertar en la tabla atenciones
        $stmt = $con->prepare("INSERT INTO atenciones (codigo_atencion, fecha_atencion) VALUES (?, ?)");
        $stmt->bind_param('ss', $codigo_atencion, $fecha);
        if (!$stmt->execute()) {
            throw new Exception('Error al insertar atención: ' . $stmt->error);
        }

        // Insertar en la tabla detalle_venta
        $Codigo = $codmuestra;
        $subtotal = $examen_data['examen']['precio'];
        $descuento = isset($examen_data['examen']['descuento']) ? $examen_data['examen']['descuento'] : 0;
        $motivodescuento = isset($examen_data['motivodescuento']) ? $examen_data['motivodescuento'] : '';
        $fk_idcomprob = $id_comprobante;
        $fk_tipopg = $examen_data['tipo_pago'];
        $fk_estapago = $examen_data['estado_pago'];

        $stmt = $con->prepare("INSERT INTO detalle_venta (Codigo, subtotal, descuento, morivodescuento, fk_idexamen, fk_idcomprob, fk_tipopg, fk_estapago) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('sdsisiii', $Codigo, $subtotal, $descuento, $motivodescuento, $id_examen, $fk_idcomprob, $fk_tipopg, $fk_estapago);
        if (!$stmt->execute()) {
            throw new Exception('Error al insertar detalle de venta: ' . $stmt->error);
        }

        // Insertar en la tabla detalle_orden
        $stmt = $con->prepare("INSERT INTO detalle_orden (fk_exam, fk_orde) VALUES (?, ?)");
        $stmt->bind_param('ii', $id_examen, $id_orden);
        if (!$stmt->execute()) {
            throw new Exception('Error al insertar detalle de orden: ' . $stmt->error);
        }
    }

    $con->commit(); // Commit the transaction
    echo json_encode(['success' => true, 'message' => 'Datos insertados correctamente.', 'id_comprobante' => $id_comprobante, 'id_orden' => $id_orden, 'tipo_comprobante' => $fk_tipocom]);

} catch (Exception $e) {
    $con->rollback(); // Roll back the transaction on error
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$con->close();
?>
