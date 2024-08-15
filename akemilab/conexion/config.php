<?php 

$conectar = new mysqli("localhost", "root", "", "akemilab2");
if ($conectar->error) {
    echo 'Error de conexion ' . $conectar->error;
    exit;
}

?>

