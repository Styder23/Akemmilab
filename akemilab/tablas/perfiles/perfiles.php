<?php

// Seguridad de sesiones
session_start();
if(!isset($_SESSION['idusuario'])){
    header("Location: ./login/login.php");
 }
// Incluye la conexión a la base de datos
include('../../conexion/conn.php');


$query2 = $con->query("SELECT idtipoexamen, tipoexam FROM tipoexamen");

$query3 = $con->query("SELECT idtipoexamen, tipoexam FROM tipoexamen");

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Médicos</title>
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
    <h1 class="text-center">Registro Perfiles</h1>
    <div class="container-fluid">
        <div class="row">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <button type="button" style="margin-bottom: 40px;" class="btn btn-primary"
                            data-bs-toggle="modal" data-bs-target="#registroModal">
                            Nuevo Perfil
                        </button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <table id="datatable" class="table">
                            <thead>
                                <tr>
                                    <th>Perfil</th>
                                    <th>Precio</th>
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

        // Agregar paciente
        $(document).on('submit', '#registroForm', function(event) {
            event.preventDefault();
            // Obtener los valores de los campos
            var perfil = $('#dni').val();
            var precio = $('#nombres').val();
            var examenes = $('#especialidad').val();
            // Enviar datos mediante AJAX
            $.ajax({
                url: './crudpaciente.php',
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    try {
                        var data = JSON.parse(response);
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
                            $('#_ide').val(json.idperfil);
                            $('#_dni').val(json.nomperfil);
                            $('#_nombres').val(json.precioperfil);

                            // $('#_especialidad').empty();

                            // Agrega todas las especialidades al select y selecciona las correspondientes
                            // json.especialidades.forEach(function(especialidad) {
                            //     var option = new Option(especialidad.tipoexam,
                            //         especialidad.idtipoexamen);
                            //     option.selected =
                            //         true; // Marca la opción como seleccionada
                            //     $('#_especialidad').append(option);
                            // });

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
        // Validación al enviar el formulario de edición
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
                        console.log(json);
                        if (json) {
                            $('#_ide').val(json.idperfil);
                            $('#_dni').val(json.nomperfil);
                            $('#_nombres').val(json.precioperfil);

                            // Limpiar y cargar las especialidades
                            var $especialidadSelect = $('#_especialidad');
                            $especialidadSelect.empty();

                            json.examenes.forEach(function(especialidad) {
                                var isSelected = especialidad.selected;
                                var option = new Option(especialidad.tipoexam, especialidad.idtipoexamen, isSelected, isSelected);
                                $especialidadSelect.append(option);
                            });


                            $('#editModal').modal('show');
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
        //--------Fin Editar registro-------//

        // Validación al enviar el formulario de edición
        $(document).on('submit', '#editForm', function(event) {
            event.preventDefault();
            var idperfil = $('#_ide').val();
            var perfil = $('#_dni').val();
            var precio = $('#_nombres').val();
            var examenes = $('#_especialidad').val();
            $.ajax({
                url: './modpac.php',
                method: 'POST',
                data:{
                    idperfil:idperfil,
                    perfil:perfil,
                    precio:precio,
                    examenes:examenes
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




        // Eliminar paciente-------//
        $(document).on('click', '.deleteBtn', function() {
            var idPaciente = $(this).data('idpacientes');
            var dni = $(this).data('dni');
            console.log(idPaciente);
            Swal.fire({
                title: '¿Estás seguro de eliminar el perfil?',
                text: "Ten en cuenta que se eliminarán los registros relacionados a este perfil.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
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
                                        'Perfil eliminado correctamente.',
                                        'success'
                                    );
                                } else {
                                    Swal.fire(
                                        'Error!',
                                        'Error al eliminar al perfil.',
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
                                <label for="inputDNI">Perfil</label>
                                <input type="text" class="form-control" id="dni" name="dni" required>
                                <div id="error-message-dni" class="text-danger mt-2 small"></div>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="inputNombres">Precio</label>
                                <input type="text" class="form-control" id="nombres" name="nombres" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="especialidad">Especialidad</label>
                                <select class="form-control" id="especialidad" name="especialidad[]" required multiple>

                                    <option value="">Seleccionar</option>
                                    <?php
                                    // Recorre los resultados de la consulta
                                    while ($row = $query2->fetch_assoc()) {
                                        echo '<option value="' . $row['idtipoexamen'] . '">' . $row['tipoexam'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>               
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
                                <input type="text" class="form-control" id="_dni" name="_dni" required>
                                <div id="error-message-_dni" class="text-danger mt-2 small"></div>
                                <input type="hidden" id="_ide" name="_ide" value="">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="inputNombres">Nombres:</label>
                                <input type="text" class="form-control" id="_nombres" name="_nombres" required>
                            </div>                      
                            <div class="form-group col-md-6">
                                <label for="_especialidad" class="form-label">Especialidad</label>
                                <select multiple class="form-control" id="_especialidad" name="especialidad[]">
                                    <!-- Las opciones se cargarán aquí dinámicamente -->
                                </select>
                            </div>

                        </div>                     
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