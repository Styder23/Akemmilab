<?php
class ConexionDB {
  private $servidor;
  private $usuario;
  private $password;
  private $nombreBaseDatos;

  public function __construct($servidor, $usuario, $password, $nombreBaseDatos) {
    $this->servidor = $servidor;
    $this->usuario = $usuario;
    $this->password = $password;
    $this->nombreBaseDatos = $nombreBaseDatos;
  }

  public function conectar() {
    $link = mysqli_connect($this->servidor, $this->usuario, $this->password);

    if (!$link) {
      throw new Exception("Error al conectar con el servidor de base de datos: " . mysqli_connect_error());
    }

    if (!mysqli_select_db($link, $this->nombreBaseDatos)) {
      throw new Exception("Error al conectar al servidor de BD, la base de datos no existe.");
    }

    if (!mysqli_set_charset($link, "utf8")) {
      throw new Exception("Error cargando el conjunto de caracteres utf8: " . mysqli_error($link));
    }

    return $link;
  }
  public function create(tesis $objTesis) {
    // Verificar la conexión
    $conexion = $this->conectar();
    if ($conexion->connect_error) {
        die("Conexión fallida: " . $conexion->connect_error);
    }

    $titulo = $objTesis->getTitulo();
    $Cargo = $objTesis->getCargo();
    $Docente = $objTesis->getDocente();

    $titulo = $conexion->real_escape_string($titulo);
    $Cargo = $conexion->real_escape_string($Cargo);
    $Docente = $conexion->real_escape_string($Docente);


    // Llamada al procedimiento almacenado
    $sql = "CALL RegistrarJuarado('$titulo','$Cargo','$Docente')";
    
    $resultado = $conexion->query($sql);
if ($resultado === false) {
    die("Error en la consulta: " . $conexion->error);
}
}
public function cerrarConexion() {
  if ($this->conexion) {
      $this->conexion->close();
  }
}
}
?>