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
    <title>Registro de Pacientes</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link href="https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.0.7/datatables.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    

    <!-- Ocultar el span ícono del ordenamiento del datatable -->
    <style>
        .dt-column-order {
            display: none;
        }
    </style>
    <!-- Fin Ocultar el span ícono del ordenamiento del datatable -->
</head>

<body>

    <h1 class="text-center">Registro Pacientes</h1>
    <div class="container-fluid">
        <div class="row">
            <div class="container">
                <div class="row">
                    <div class="col-md-8">
                        <button type="button" style="margin-bottom: 40px;" class="btn btn-primary"
                            data-bs-toggle="modal" data-bs-target="#registroModal">
                            Nuevo Paciente
                        </button>
                        <button type="button" style="margin-bottom: 40px;" class="btn btn-secondary"
                            onclick="redirect()">
                            Asignar examen
                        </button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-8">
                        <table id="datatable" class="table">
                            <thead>
                                <tr>
                                    <th>N°</th>
                                    <th>DNI</th>
                                    <th>PACIENTE</th>
                                    <th>CODIGO</th>
                                    <th>Fecha de nacimiento</th>
                                    <th>EDAD</th>
                                    <th>GENERO</th>
                                    <th>CORREO</th>
                                    <th>CELULAR</th>
                                    <th>RUC</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Datos de la tabla se cargarán desde el servidor -->
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
        //--------Cargar datos a la tabla-------// 
        var table = $('#datatable').DataTable({
            serverSide: true,
            processing: true,
            ajax: {
                url: './Vpaciente.php',
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
        //--------Fin Cargar datos a la tabla-------//

        // Solo permitir números y limitar a 8 caracteres en el campo dni
        $('#dni').on('input', function() {
            var dni = $(this).val().replace(/[^0-9]/g, '').substring(0, 8);
            $(this).val(dni);
        });

        // Solo permitir números y limitar a 11 caracteres en el campo ruc
        $('#ruc').on('input', function() {
            var ruc = $(this).val().replace(/[^0-9]/g, '').substring(0, 11);
            $(this).val(ruc);
        });
        $('#celular').on('input', function() {
            var celular = $(this).val();
            // Eliminar caracteres no numéricos
            celular = celular.replace(/[^0-9]/g, '');
            // Limitar a 9 caracteres
            if (celular.length > 9) {
                celular = celular.substring(0, 9);
            }
            $(this).val(celular);
        });

        // Agregar paciente
        $(document).on('submit', '#registroForm', function(event) {
            event.preventDefault();
            // Obtener los valores de los campos
            var dni = $('#dni').val();
            var nombres = $('#nombres').val();
            var apellidos = $('#apellidos').val();
            var fecha_nacimiento = $('#edad').val();
            var direccion = $('#direccion').val();
            var genero = $('#genero').val();
            var correo = $('#correo').val();
            var ruc = $('#ruc').val();
            var celular = $('#celular').val();
            var razon = $('#razon').val();
            var dir_emp = $('#dir_emp').val();
            if (dni.length > 0 && dni.length < 8) {
                $('#error-message-dni').text('El DNI debe tener exactamente 8 dígitos.');
                return;
            } else {
                $('#error-message-dni').text('');
            }

            // Enviar datos mediante AJAX
            $.ajax({
                url: './crudpaciente.php',
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    try {    
                        var data = JSON.parse(response);  
                        console.log(data);              
                        if (data.status === 'true') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: data.message
                            }).then(function() {
                                location.reload();
                            });

                            $('#registroForm')[0].reset();
                            $('#registroModal').modal('hide');
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message
                            });
                        }
                    } catch (e) {
                        console.error('Error al analizar JSON:', e);
                        console.error('Respuesta del servidor:', response);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error al procesar respuesta del servidor'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error en la solicitud AJAX: ' + error
                    });
                }
            });
        });

        // Cargar datos para editar paciente
        $(document).on('click', '.editbtn', function() {
            var id = $(this).data('idpacientes');
            $.ajax({
                url: 'modifica.php',
                method: 'POST',
                data: {
                    id: id
                },
                success: function(data) {
                    try {
                        var json = JSON.parse(data);
                        if (json) {
                            $('#idexamen').val(json.id);
                            $('#_dni').val(json.DNI);
                            $('#_nombres').val(json.NOMBRE);
                            $('#_apellidos').val(json.APELLIDO);
                            $('#_edad').val(json.fecha_nacimiento);
                            $('#_direccion').val(json.DIRECCIÓN);
                            $('#_genero').val(json.GENERO);
                            $('#_correo').val(json.CORREO);
                            $('#_ruc').val(json.RUC);
                            $('#_celular').val(json.CELULAR);
                            $('#_razon').val(json.razon_social);
                            $('#_dir_emp').val(json.dir_empresa);
                            $('#_id').val(json.id);
                            $('#editModal').modal('show'); // Muestra el modal con los datos
                        } else {
                            alert('No se encontraron datos para este paciente');
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

        //--------Editar registro-------//
        // Solo permitir números y limitar a 8 caracteres en el campo _dni
        $('#_dni').on('input', function() {
            var dni = $(this).val();
            // Eliminar caracteres no numéricos
            dni = dni.replace(/[^0-9]/g, '');
            // Limitar a 8 caracteres
            if (dni.length > 8) {
                dni = dni.substring(0, 8);
            }
            $(this).val(dni);
        });

        // Solo permitir números y limitar a 11 caracteres en el campo ruc
        $('#_ruc').on('input', function() {
            var ruc = $(this).val().replace(/[^0-9]/g, '').substring(0, 11);
            $(this).val(ruc);
        });
        $('#_celular').on('input', function() {
            var celular = $(this).val();
            // Eliminar caracteres no numéricos
            celular = celular.replace(/[^0-9]/g, '');
            // Limitar a 9 caracteres
            if (celular.length > 9) {
                celular = celular.substring(0, 9);
            }
            $(this).val(celular);
        });
        // Validación al enviar el formulario de edición
        $(document).on('submit', '#editForm', function(event) {
            event.preventDefault();
            var idpaciente = $('#_id').val();
            var dni = $('#_dni').val();
            var nombres = $('#_nombres').val();
            var apellidos = $('#_apellidos').val();
            var fecha_nacimiento = $('#_edad').val();
            var direccion = $('#_direccion').val();
            var genero = $('#_genero').val();
            var correo = $('#_correo').val();
            var ruc = $('#_ruc').val();
            var celular = $('#_celular').val();
            var razon = $('#_razon').val();
            var dir_emp = $('#_dir_emp').val();
            if (dni.length > 0 && dni.length < 8) {
                $('#error-message-_dni').text('El DNI debe tener exactamente 8 dígitos.');
                return;
            } else {
                $('#error-message-_dni').text('');
            }
            $.ajax({
                url: './modpac.php',
                method: 'POST',
                data:{
                    idpaciente:idpaciente,
                    dni:dni,
                    nombres:nombres,
                    apellidos:apellidos,
                    fecha_nacimiento:fecha_nacimiento,
                    direccion:direccion,
                    genero:genero,
                    correo:correo,
                    ruc:ruc,
                    celular:celular,
                    razon:razon,
                    dir_emp:dir_emp
                },
                success: function(response) {
                    try {
                        var data = JSON.parse(response);                       
                        if (data.status === 'true') {
                            table.draw();
                            Swal.fire({
                                title: 'Éxito',
                                text: data.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            });
                            $('#editModal').modal('hide');
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: data.message,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    } catch (e) {
                        console.error('Error al analizar JSON:', e);
                        Swal.fire({
                            title: 'Error',
                            text: 'Error al procesar respuesta del servidor',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        title: 'Error',
                        text: 'Error en la solicitud AJAX',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });
        //--------Fin Editar registro-------//
        // Eliminar paciente-------//
        $(document).on('click', '.deleteBtn', function() {
            var idPaciente = $(this).data('idpacientes');
            var dni = $(this).data('dni');
            Swal.fire({
                title: '¿Estás seguro?',
                text: "¿Estás seguro de que deseas eliminar este usuario?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Realizar la solicitud AJAX para eliminar el usuario
                    $.ajax({
                        url: './eliminapa.php', // Cambia esto por la URL de tu script PHP para eliminar usuarios
                        method: 'POST',
                        data: {
                            id: idPaciente,
                            dni: dni
                        },
                        success: function(data) {
                            console.log(data);
                            try {
                                var json = JSON.parse(data);
                                if (json.status === 'true') {
                                    // Eliminación exitosa, volver a cargar los datos de la tabla
                                    table.ajax.reload(null, false);
                                    Swal.fire(
                                        'Eliminado!',
                                        'Usuario eliminado correctamente.',
                                        'success'
                                    );
                                } else {
                                    Swal.fire(
                                        'Error!',
                                        'Error al eliminar al usuario.',
                                        'error'
                                    );
                                }
                            } catch (e) {
                                console.error('Error al analizar JSON:', e);
                                Swal.fire(
                                    'Error!',
                                    'Error al procesar respuesta del servidor.',
                                    'error'
                                );
                            }
                        },
                        error: function() {
                            Swal.fire(
                                'Error!',
                                'Error en la solicitud AJAX.',
                                'error'
                            );
                        }
                    });
                }
            });
        });
        //--------Fin Eliminar registro-------//
    });
    </script>
    <script>
    function redirect() {
        window.location.href = '../asignaexam/asigna.php';
    }
    </script>
    <!-- Modal -->
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
                                <label for="edad">Fecha de nacimiento</label>
                                <input type="date" class="form-control" id="edad" name="edad" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="direccion">direccion</label>
                                <input type="text" class="form-control" id="direccion" name="direccion">
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
                        </div>
                        <!-- Reorganización de los combos -->
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label for="correo">Correo</label>
                                <input type="email" class="form-control" id="correo" name="correo">
                            </div>                           
                            <div class="form-group col-md-4">
                                <label for="celular">Celular</label>
                                <input type="text" class="form-control" id="celular" name="celular">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="ruc">RUC</label>
                                <input type="text" class="form-control" id="ruc" name="ruc">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="razon">Razón social</label>
                                <input type="text" class="form-control" id="razon" name="razon">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="dir_emp">Dirección empresa</label>
                                <input type="text" class="form-control" id="dir_emp" name="dir_emp">
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


    <!-- Modal editar-->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="registroModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="registroModalLabel">Editar Registro</h5>

                </div>
                <div class="modal-body">
                    <!-- Formulario dentro del modal -->
                    <form id="editForm" action="javascript:void(0);" method="POST">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="inputDNI">DNI</label>
                                <input type="text" class="form-control" id="_dni" name="_dni">
                                <div id="error-message-_dni" class="text-danger mt-2 small"></div>
                                <input type="hidden" id="_id" name="_id" value="<?php echo $id; ?>">
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
                                <label for="_edad">Fecha de nacimiento</label>
                                <input type="date" class="form-control" id="_edad" name="_edad" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="_direccion">direccion</label>
                                <input type="text" class="form-control" id="_direccion" name="_direccion">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="_genero">Género</label>
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
                        </div>
                        <!-- Reorganización de los combos -->
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label for="_correo">Correo</label>
                                <input type="email" class="form-control" id="_correo" name="_correo">
                            </div>
                            
                            <div class="form-group col-md-4">
                                <label for="_celular">Celular</label>
                                <input type="text" class="form-control" id="_celular" name="_celular">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="_ruc">RUC</label>
                                <input type="text" class="form-control" id="_ruc" name="_ruc">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="_razon">Razón social</label>
                                <input type="text" class="form-control" id="_razon" name="_razon">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="_dir_emp">Dirección empresa</label>
                                <input type="text" class="form-control" id="_dir_emp" name="_dir_emp">
                            </div>
                        </div><br>

                        <div class="form-row">
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>

</html>