
<?php



$con  = mysqli_connect('localhost','root','','akemilab2');
if(mysqli_connect_errno())
{
    echo 'Database Connection Error';
    exit();
}

function isCajaAbierta($con) {
    // Realiza la lógica para verificar si la caja está abierta
    $query = "SELECT COUNT(*) AS total FROM caja WHERE hora_cierre IS NULL";
    $result = $con->query($query);

    if ($result) {
        $row = $result->fetch_assoc();
        $total = $row['total'];
        return ($total > 0); // Retorna true si hay al menos una caja abierta
    } else {
        return false; // En caso de error, se considera que no hay caja abierta
    }
}
?>