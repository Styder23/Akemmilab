<?php
date_default_timezone_set('America/Lima');
session_start();
require '../../conexion/conn.php';
// Obtén la sesión actual
$fkusuario = $_SESSION['idusuario'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $monto_inicial = isset($_POST['monto_inicial']) ? (float)$_POST['monto_inicial'] : 0;
    $monto_agregado = isset($_POST['monto_agregado']) ? (float)$_POST['monto_agregado'] : 0;

    // Calcular el monto total de apertura
    $monto_total_apertura = $monto_inicial + $monto_agregado;

    $hora_apertura = date('Y-m-d H:i:s');

    // Preparar la declaración SQL para insertar una nueva caja
    $stmt = $con->prepare("INSERT INTO caja (hora_apertura, montoini, importe, fk_idestado, fk_idusuario) VALUES (?, ?, ?, 1, ?)");
    
    // Verificar si la preparación de la declaración fue exitosa
    if ($stmt === false) {
        echo "<script>alert('Error en la preparación de la declaración: " . addslashes($con->error) . "'); window.location.href='./caja.php';</script>";
        exit();
    }
 
    // Vincular los parámetros a la declaración preparada
    $stmt->bind_param("sddi", $hora_apertura, $monto_total_apertura, $monto_total_apertura,$fkusuario);
    
    // Ejecutar la declaración
    $stmt->execute();

    // Verificar si la inserción fue exitosa
    if ($stmt->affected_rows > 0) {
        echo "<script>alert('Caja abierta exitosamente.'); window.location.href='./caja.php';</script>";
    } else {
        echo "<script>alert('Error al abrir la caja: " . addslashes($stmt->error) . "'); window.location.href='./caja.php';</script>";
    }

    $stmt->close();
}
?>
