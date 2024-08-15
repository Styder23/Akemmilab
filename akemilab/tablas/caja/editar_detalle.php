<?php
require_once '../../conexion/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idigre_egre = $_POST['idigre_egre'];
    $nuevoDetalle = $_POST['concepto'];

    $stmt = $con->prepare("UPDATE igre_egre SET concepto = ? WHERE idigre_egre = ?");
    $stmt->bind_param("si", $nuevoDetalle, $idigre_egre);
    $stmt->execute();

    header("Location: prueba.php");
    exit();
}
?>
