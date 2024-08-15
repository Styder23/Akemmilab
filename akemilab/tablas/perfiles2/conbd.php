<?php
include_once "../../conexion/conexion.php";
$conexionBD = BD::crearInstancia();


$id = isset($_POST['id']) ? $_POST['id'] : '';
$nombre = isset($_POST['nombre']) ? $_POST['nombre'] : '';
$precio = isset($_POST['precio']) ? $_POST['precio'] : '';

$examenes = isset($_POST['examenes']) ? $_POST['examenes'] : "";
$accion = isset($_POST['accion']) ? $_POST['accion'] : "";

$mensaje="";
$success = false; // Booleano para indicar éxito
$examenesActuales[]=[];
if ($accion != "") {
    switch ($accion) {
        case "agregar":
            $sql = "CALL p_inperfil(:nombre, :precio)";
            $consulta = $conexionBD->prepare($sql);
            $consulta->bindParam(":nombre", $nombre);
            $consulta->bindParam(":precio", $precio);
            try {
                $consulta->execute();
                $result = $consulta->fetchAll(PDO::FETCH_ASSOC);
                $consulta->closeCursor();
                if ($result) {
                    foreach ($result as $row) {
                        if ($row['mensaje'] == 'El perfil se ingresó correctamente') {
                            $sql = "SELECT obtenerIdPerfil() AS idPerfil";
                            $resultado = $conexionBD->query($sql)->fetch(PDO::FETCH_ASSOC);
                            $idPerfil = $resultado['idPerfil'];
                            foreach ($examenes as $examen) {
                                $sql = "INSERT INTO perfilxexam (fk_idtipoex, fk_idperfil) VALUES (:idexamen,:idperfil)";
                                $consulta = $conexionBD->prepare($sql);
                                $consulta->bindParam(":idexamen", $examen);
                                $consulta->bindParam(":idperfil", $idPerfil);
                                $consulta->execute();
                                $consulta->closeCursor();
                            }
                            $mensaje = $row['mensaje'];
                            $success = true;
                        } else {
                            $mensaje = $row['mensaje'];
                            $success = false;
                            $consulta->closeCursor();
                        }
                    }
                } else {
                    $mensaje = "No hay registros";
                    $success = false;
                }
            } catch (PDOException $e) {
                $mensaje = 'Error: ' . $e->getMessage();
                $success = false;
            }
            break;
        case "Seleccionar":
            $sql = "select * from perfiles where idperfil=:id";
            $consulta = $conexionBD->prepare($sql);
            $consulta->bindParam(":id", $id);
            $consulta->execute();
            
            $perfil = $consulta->fetch(PDO::FETCH_ASSOC);
            $consulta->closeCursor();
            $nombre = $perfil["nomperfil"];
            $precio = $perfil["precioperfil"];

            //Recuperar los exámenes de este perifl
            $sql = "SELECT tipoexamen.idtipoexamen FROM perfilxexam INNER JOIN tipoexamen ON tipoexamen.idtipoexamen=perfilxexam.fk_idtipoex WHERE perfilxexam.fk_idperfil=:idperfil";
            $consulta = $conexionBD->prepare($sql);
            $consulta->bindParam(":idperfil", $id);
            $consulta->execute();
            $examenesPerfil = $consulta->fetchAll(PDO::FETCH_ASSOC);
            $consulta->closeCursor();
            $arregloExamenes = [];
            foreach ($examenesPerfil as $examen) {
                $arregloExamenes[] = $examen["idtipoexamen"];
            }
            break;
        case "borrar":           
            $sql = "delete from perfiles where idperfil=:id";
            $consulta = $conexionBD->prepare($sql);
            $consulta->bindParam(":id", $id);
            $consulta->execute();
            $consulta->closeCursor();
            $mensaje = "Perfil eliminado correctamente.";
            $success = true;
            break;
        case "editar":

            // Variable para rastrear si se realizaron cambios en las especialidades
            $examenesActualizados = false;

            $sql = "SELECT fk_idtipoex FROM perfilxexam WHERE fk_idperfil = :id";
            $consulta = $conexionBD->prepare($sql);
            $consulta->bindParam(":id", $id);
            $consulta->execute();
            $examenesPerfil = $consulta->fetchAll(PDO::FETCH_ASSOC);
            $consulta->closeCursor();
            foreach ($examenesPerfil as $examen) {
                $examenesActuales[] = $examen["fk_idtipoex"];
            }
            // Asegúrate de que $examenes sea siempre un array
            $examenes = is_array($examenes) ? $examenes : [];
            sort($examenesActuales);
            sort($examenes);
            if ($examenesActuales != $examenes) {
                // Las especialidades son diferentes, realizar actualización
                $sql = "DELETE FROM perfilxexam WHERE fk_idperfil =:id";
                $consulta = $conexionBD->prepare($sql);
                $consulta->bindParam(":id", $id);
                $consulta->execute();
                $consulta->closeCursor();
                foreach ($examenes as $examen) {
                    $sql = "INSERT INTO perfilxexam (fk_idtipoex, fk_idperfil) VALUES (:idexamen,:idperfil)";
                    $consulta = $conexionBD->prepare($sql);
                    $consulta->bindParam(":idexamen", $examen);
                    $consulta->bindParam(":idperfil", $id);
                    $consulta->execute();
                    $consulta->closeCursor();
                }
                $examenesActualizados = true;   
            }
            $sql = "CALL p_updateperfil(:id,:nombre, :precio)";
            $consulta = $conexionBD->prepare($sql);
            $consulta->bindParam(":id", $id);
            $consulta->bindParam(":nombre", $nombre);
            $consulta->bindParam(":precio", $precio);
            try {
                $consulta->execute();
                $result = $consulta->fetchAll(PDO::FETCH_ASSOC);
                $consulta->closeCursor();
                if ($result) {
                    foreach ($result as $row) {
                        if ($row['mensaje'] === "El perfil se actualizó correctamente") {
                            $mensaje= $row['mensaje'];
                            $success = true;
                            
                        } elseif ($row['mensaje'] === "No existen cambios") {
                            if ($examenesActualizados) {
                                $mensaje="Exámenes de perfil actualizados correctamente";
                                $success = true;
                            } else {
                                $mensaje= $row['mensaje'];
                                $success = false;
                            }
                        } else {
                            $mensaje= $row['mensaje'];
                            $success = false;
                        }
                    }
                } else {
                    $mensaje = "No hay registros";
                    $success = false;
                }
            } catch (PDOException $e) {
                $mensaje = 'Error: ' . $e->getMessage();
                $success = false;
            }
            break;
    }
}

$sql = "SELECT * FROM v_perfiles";
$listaPerfiles = $conexionBD->query($sql);
$perfiles = $listaPerfiles->fetchAll();
$listaPerfiles->closeCursor(); 

$sql = "SELECT * FROM tipoexamen";
$listaExamenes = $conexionBD->query($sql);
$examenes = $listaExamenes->fetchAll();
$listaExamenes->closeCursor(); 
?>

