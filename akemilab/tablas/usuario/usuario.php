<?php

// Seguridad de sesiones
session_start();
if(!isset($_SESSION['idusuario'])){
    header("Location: ./login/login.php");
 }
// Incluye la conexión a la base de datos
include('../../conexion/conn.php');

// Realiza la consulta a la base de datos
$query = $con->query("SELECT idgenero, genero FROM genero");
$query1 = $con->query("SELECT idgenero, genero FROM genero");
// establecimieto
$query2 = $con->query("SELECT idtiposusaurio, tipousu FROM tiposusaurio");
$query3 = $con->query("SELECT idtiposusaurio, tipousu FROM tiposusaurio");

// Verifica que la consulta sea exitosa
if (!$query) {
    die('Error en la consulta a la base de datos');
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link href="https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.0.7/datatables.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .dt-column-order {
            display: none;
        }
    </style>
</head>

<body>
    <h1 class="text-center">USUARIOS</h1>
    <div class="container-fluid">
        <div class="row">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <button type="button" style="margin-bottom: 40px;" class="btn btn-primary"
                            data-bs-toggle="modal" data-bs-target="#registroModal">
                            Nuevo Usuario
                        </button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">

                        <!-- Tabla de resultados -->
                        <table id="datatable" class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>DNI</th>
                                    <th>Usuario</th>
                                    <th>GENERO</th>
                                    <th>Fecha de nacimiento</th>
                                    <th>EDAD</th>
                                    <th>TELEFONO</th>
                                    <th>Direccion</th>
                                    <th>correo</th>
                                    <th>CARGO</th>
                                    <th>USER</th>
                                    <th>Acciones</th>

                                </tr>
                            </thead>
                            <tbody>
                                <!-- Los datos se insertarán aquí -->
                            </tbody>
                        </table>

                    </div>
                    <div class="col-md-2"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
    <script src="https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.0.7/datatables.min.js"></script>


    <script type="text/javascript">
    $(document).ready(function() {
        // Inicializar DataTable
        var table = $('#datatable').DataTable({
            serverSide: true,
            processing: true,
            ajax: {
                url: 'Vusuario.php',
                type: 'POST',
            },
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.2/i18n/es-MX.json"
            },
            columnDefs: [{
                    orderable: false,
                    targets: "_all"
                } // Escribe las columnas en las que quieres quitar el ordenamiento[]
            ]
        });
        // Solo permitir números y limitar a 8 caracteres en el campo dni
        $('#dni').on('input', function() {
            var dni = $(this).val().replace(/[^0-9]/g, '').substring(0, 8);
            $(this).val(dni);
        });
        $('#telefono').on('input', function() {
            var celular = $(this).val();
            // Eliminar caracteres no numéricos
            celular = celular.replace(/[^0-9]/g, '');
            // Limitar a 9 caracteres
            if (celular.length > 9) {
                celular = celular.substring(0, 9);
            }
            $(this).val(celular);
        });
        // Agregar Usuario
        $(document).on('submit', '#registroForm', function(event) {
            event.preventDefault();
            var dni = $('#dni').val();
            var nombre = $('#nombres').val();
            var apellido = $('#apellidos').val();
            var genero = $('#genero').val();
            var fecha_nacimiento = $('#edad').val();
            var telefono = $('#telefono').val();
            var direccion = $('#direccion').val();
            var cargo = $('#cargo').val();
            var usuario = $('#usuario').val();
            var contraseña = $('#contraseña').val();
            var correo = $('#correo').val();

            if (dni.length > 0 && dni.length < 8) {
                $('#error-message-dni').text('El DNI debe tener exactamente 8 dígitos.');
                return;
            } else {
                $('#error-message-dni').text('');
            }

            $.ajax({
                url: 'insertusu.php',
                method: 'POST',
                data: {
                    dni: dni,
                    nombre: nombre,
                    apellido: apellido,
                    genero: genero,
                    fecha_nacimiento: fecha_nacimiento,
                    telefono: telefono,
                    direccion: direccion,
                    correo: correo,
                    cargo: cargo,
                    usuario: usuario,
                    contraseña: contraseña,
                },

                success: function(data) {
                    try {
                        var json = JSON.parse(data);
                        
                        if (json.status === 'true') {
                            table.draw();
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: json.message,
                            });
                            $('#dni').val('');
                            $('#nombres').val('');
                            $('#apellidos').val('');
                            $('#genero').val('');
                            $('#edad').val('');
                            $('#telefono').val('');
                            $('#direccion').val('');
                            $('#correo').val('');
                            $('#cargo').val('');
                            $('#usuario').val('');
                            $('#contraseña').val('');
                            $('#registroModal').modal('hide');
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: json.message,
                            });
                        }
                    } catch (e) {
                        console.error('Error al analizar JSON:', e);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error al procesar respuesta del servidor',
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error en la solicitud AJAX',
                    });
                }
            });
        });
        // Cargar datos para editar Usuario
        $(document).on('click', '.editbtn', function() {
            var id = $(this).data('idusuario');
            $.ajax({
                url: 'llenamodif.php',
                method: 'POST',
                data: {
                    id: id
                },
                success: function(data) {
                    try {
                        var json = JSON.parse(data);
                        if (json) {
                            console.log(json); // Verifica los datos recibidos en la consola
                            $('#_id').val(json.idusuario);
                            $('#_dni').val(json.dni);
                            $('#_nombres').val(json.Nombre);
                            $('#_apellidos').val(json.Apellido);
                            $('#_genero').val(json.idgenero);
                            $('#_edad').val(json.fecha_nacimiento);
                            $('#_telefono').val(json.celular);
                            $('#_direccion').val(json.direccion);
                            $('#_correo').val(json.correo);
                            $('#_cargo').val(json.idtiposusaurio);
                            $('#_usuario').val(json.nomusu);
                            $('#_contraseña').val(json.pass);


                            $('#editModal').modal('show'); // Muestra el modal con los datos
                        } else {
                            alert('No se encontraron datos para este Usuario');
                        }
                    } catch (e) {
                        console.error('Error al analizar JSON:', e);
                        alert('Error al procesar respuesta del servidor');
                    }
                },
                error: function() {
                    alert('Error al cargar datos para edición');
                }
            });
        });
        // Solo permitir números y limitar a 8 caracteres en el campo dni
        $('#_dni').on('input', function() {
            var dni = $(this).val().replace(/[^0-9]/g, '').substring(0, 8);
            $(this).val(dni);
        });
        $('#_telefono').on('input', function() {
            var celular = $(this).val();
            // Eliminar caracteres no numéricos
            celular = celular.replace(/[^0-9]/g, '');
            // Limitar a 9 caracteres
            if (celular.length > 9) {
                celular = celular.substring(0, 9);
            }
            $(this).val(celular);
        });
        // Editar paciente
        $(document).on('submit', '#editForm', function(event) {
            event.preventDefault();

            // Obtén los valores de los campos del formulario
            var idPaciente = $('#_id').val();
            var dn = $('#_dni').val();
            var nom = $('#_nombres').val();
            var ape = $('#_apellidos').val();
            var fecha_nacimiento = $('#_edad').val();
            var direc = $('#_direccion').val();
            var gen = $('#_genero').val();
            var cor = $('#_correo').val();
            var tel = $('#_telefono').val();
            var car = $('#_cargo').val();
            var usu = $('#_usuario').val();
            var con = $('#_contraseña').val();
            if (dni.length > 0 && dni.length < 8) {
                $('#error-message-_dni').text('El DNI debe tener exactamente 8 dígitos.');
                return;
            } else {
                $('#error-message-_dni').text('');
            }

            // Realiza la solicitud AJAX para modificar el paciente
            $.ajax({
                url: './updateusu.php',
                method: 'POST',
                data: {
                    idPaciente: idPaciente,
                    dni: dn,
                    nombres: nom,
                    apellidos: ape,
                    fecha_nacimiento: fecha_nacimiento,
                    direccion: direc,
                    genero: gen,
                    correo: cor,
                    telefono: tel,
                    cargo: car,
                    usuario: usu,
                    contraseña: con
                },
                success: function(data) {
                    console.log(data);
                    try {
                        var json = JSON.parse(data);
                        if (json.status === 'true') {
                            table.draw();
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: json.message,
                            });
                            $('#editModal').modal('hide');
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: json.message,
                            });
                        }
                    } catch (e) {
                        console.error('Error al analizar JSON:', e);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error al procesar respuesta del servidor',
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error en la solicitud AJAX',
                    });
                }
            });
        });

        // Función para eliminar un paciente
        $(document).on('click', '.deleteBtn', function() {
            // Obtener el ID del paciente a eliminar
            var idUsuario = $(this).data('idusuario');
            console.log(idUsuario);
            // Confirmar la eliminación
            if (confirm("¿Estás seguro de que deseas eliminar este usuario?")) {
                // Realizar la solicitud AJAX para eliminar el paciente
                $.ajax({
                    url: './deleteusu.php', // Cambia esto por la URL de tu script PHP para eliminar pacientes
                    method: 'POST',
                    data: {
                        //elprimero es la variable de eliminapa.php, el segundo es la variable del inicio
                        id: idUsuario
                    },
                    success: function(data) {
                        try {
                            var json = JSON.parse(data);
                            if (json.status === 'true') {
                                // Eliminación exitosa, volver a cargar los datos de la tabla
                                table.ajax.reload(null, false);
                                alert('Usuario eliminado correctamente');
                            } else {
                                alert('Error al eliminar el paciente');
                            }
                        } catch (e) {
                            console.error('Error al analizar JSON:', e);
                            alert('Error al procesar respuesta del servidor');
                        }
                    },
                    error: function() {
                        alert('Error en la solicitud AJAX');
                    }
                });
            }
        });

    });
    </script>

    <!-- Modal agregar-->
    <div class="modal fade" id="registroModal" tabindex="-1" aria-labelledby="registroModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="registroModalLabel">Nuevo Registro</h5>

                </div>
                <div class="modal-body">
                    <!-- Formulario dentro del modal -->
                    <form id="registroForm" action="javascript:void(0);" method="POST">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="inputDNI">DNI</label>
                                <input type="text" class="form-control" id="dni" name="dni">
                                <div id="error-message-dni" class="text-danger mt-2 small"></div>
                                <input type="hidden" name="id" value="<?php $idUsuario ?>">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="inputNombres">Nombres:</label>
                                <input type="text" class="form-control" id="nombres" name="nombres" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="inputNombres">Apellidos:</label>
                                <input type="text" class="form-control" id="apellidos" name="apellidos" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="genero">Género</label>
                                <select class="form-control" id="genero" name="genero" required>
                                    <option value="">Seleccionar Género</option>
                                    <?php
                                    // Recorre los resultados de la consulta
                                    while ($row = $query->fetch_assoc()) {
                                        echo '<option value="' . $row['idgenero'] . '">' . $row['genero'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="edad">Fecha de nacimiento</label>
                                <input type="date" class="form-control" id="edad" name="edad" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="edad">telefono</label>
                                <input type="number" class="form-control" id="telefono" name="telefono">
                            </div>

                            <div class="form-group col-md-6">
                                <label for="direccion">direccion</label>
                                <input type="text" class="form-control" id="direccion" name="direccion">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="correo">Correo (OPCIONAL)</label>
                                <input type="email" class="form-control" id="correo" name="correo">
                            </div>
                        </div>
                        <!-- Reorganización de los combos -->
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="genero">Roles</label>
                                <select class="form-control" id="cargo" name="cargo" required>
                                    <option value="">Seleccionar Cargo</option>
                                    <?php
                                    // Recorre los resultados de la consulta
                                    while ($row = $query2->fetch_assoc()) {
                                        echo '<option value="' . $row['idtiposusaurio'] . '">' . $row['tipousu'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="direccion">usuario</label>
                                <input type="text" class="form-control" id="usuario" name="usuario" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="direccion">contraseña</label>
                                <input type="text" class="form-control" id="contraseña" name="contraseña" required>
                            </div>
                        </div><br>

                        <div class="form-row">
                            <div class="col-md-12 text-end">
                                <button type="submit" class="btn btn-primary" name="registro">Registrar</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Salir</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar-->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="registroModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="registroModalLabel">Guardar Cambios</h5>

                </div>
                <div class="modal-body">
                    <!-- Formulario dentro del modal -->
                    <form id="editForm" action="javascript:void(0);" method="POST">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="inputDNI">DNI</label>
                                <input type="text" class="form-control" id="_dni" name="_dni">
                                <div id="error-message-_dni" class="text-danger mt-2 small"></div>
                                <input type="hidden" name="_id" id="_id" value="<?php $idusuario ?>">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="inputNombres">Nombres:</label>
                                <input type="text" class="form-control" id="_nombres" name="_nombres" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="inputNombres">Apellidos:</label>
                                <input type="text" class="form-control" id="_apellidos" name="_apellidos" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="genero">Género</label>
                                <select class="form-control" id="_genero" name="_genero" required>
                                    <option value="">Seleccionar Género</option>
                                    <?php
                                    // Recorre los resultados de la consulta
                                    while ($row = $query1->fetch_assoc()) {
                                        echo '<option value="' . $row['idgenero'] . '">' . $row['genero'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="edad">Fecha de nacimiento</label>
                                <input type="date" class="form-control" id="_edad" name="_edad" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="edad">telefono</label>
                                <input type="number" class="form-control" id="_telefono" name="_telefono">
                            </div>

                            <div class="form-group col-md-6">
                                <label for="direccion">direccion</label>
                                <input type="text" class="form-control" id="_direccion" name="_direccion">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="correo">Correo (OPCIONAL)</label>
                                <input type="email" class="form-control" id="_correo" name="_correo">
                            </div>
                        </div>
                        <!-- Reorganización de los combos -->
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="genero">Roles</label>
                                <select class="form-control" id="_cargo" name="_cargo" required>
                                    <option value="">Seleccionar Cargo</option>
                                    <?php
                                    // Recorre los resultados de la consulta
                                    while ($row = $query3->fetch_assoc()) {
                                        echo '<option value="' . $row['idtiposusaurio'] . '">' . $row['tipousu'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="direccion">usuario</label>
                                <input type="text" class="form-control" id="_usuario" name="_usuario" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="direccion">contraseña</label>
                                <input type="text" class="form-control" id="_contraseña" name="_contraseña" required>
                            </div>
                        </div><br>

                        <div class="form-row">
                            <div class="col-md-12 text-end">
                                <button type="submit" class="btn btn-primary" name="registro">Registrar</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Salir</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>

</html>