<?php

// Seguridad de sesiones
session_start();
if(!isset($_SESSION['idusuario'])){
    header("Location: ./login/login.php");
 }
// Incluye la conexión a la base de datos
include('../../conexion/conn.php');

// Realiza la consulta a la base de datos
$query1 = $con->query("SELECT idtipoexamen, tipoexam FROM tipoexamen");

$query2 = $con->query("SELECT idunidades, unidades FROM unidades");

$query3 = $con->query("SELECT idnombretit, nomtit FROM nombretit");

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DATOS</title>
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
    <h1 class="text-center">DATOS DE EXÁMENES</h1>
    <div class="container-fluid">
        <div class="row" style="padding:50px 100px 100px 100px;">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">

                        <!-- Tabla de resultados -->
                        <table id="datatable" class="table">
                            <thead>
                                <tr>
                                    <th>Dato</th>
                                    <th>Rango Mínimo</th>
                                    <th>Rango Máximo</th>
                                    <th>Tipo de examen</th>
                                    <th>Análisis</th>
                                    <th>Unidad</th>
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
                url: './Vanalisis.php',
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

        // Cargar datos para editar
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
                            $('#_id').val(json.idnommedible);
                            $('#_dni').val(json.nommedi);
                            $('#_nombres').val(json.rangomin);
                            $('#_apellidos').val(json.rangomax);
                            $('#_genero').val(json.idtipoexamen);
                            $('#_unidad').val(json.idunidades);
                            $('#_cargo').val(json.idnombretit);
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
        // Editar paciente
        $(document).on('submit', '#editForm', function(event) {
            event.preventDefault();

            // Obtén los valores de los campos del formulario
            var idPaciente = $('#_id').val();
            var nommedi = $('#_dni').val();
            var rangomin = $('#_nombres').val();
            var rangomax = $('#_apellidos').val();
            var idtipoexamen = $('#_genero').val();
            var idunidades = $('#_unidad').val();
            var idnombretit = $('#_cargo').val();

            // Realiza la solicitud AJAX para modificar el paciente
            $.ajax({
                url: './updateanalisis.php',
                method: 'POST',
                data: {
                    idPaciente: idPaciente,
                    nommedi: nommedi,
                    rangomin: rangomin,
                    rangomax: rangomax,
                    idtipoexamen: idtipoexamen,
                    idunidades: idunidades,
                    idnombretit: idnombretit,
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
            if (confirm("¿Estás seguro de que deseas eliminar este dato?")) {
                // Realizar la solicitud AJAX para eliminar el paciente
                $.ajax({
                    url: './deleteanalisis.php', // Cambia esto por la URL de tu script PHP para eliminar pacientes
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
                                alert('Dato eliminado correctamente');
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
                            <div class="row">
                            <div class="form-group col-md-4">
                                <label for="inputDNI">Dato</label>
                                <input type="text" class="form-control" id="_dni" name="_dni">
                                <div id="error-message-_dni" class="text-danger mt-2 small"></div>
                                <input type="hidden" name="_id" id="_id" value="<?php $idusuario ?>">
                            </div>
                            <div class="form-group col-md-2">
                                <label for="_unidad">Unidades</label>
                                <select class="form-control" id="_unidad" name="_unidad">
                                    <option value="">Seleccionar unidad</option>
                                    <?php
                                    // Recorre los resultados de la consulta
                                    while ($row = $query2->fetch_assoc()) {
                                        echo '<option value="' . $row['idunidades'] . '">' . $row['unidades'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="inputNombres">Rango mínimo</label>
                                <input type="text" class="form-control" id="_nombres" name="_nombres">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="inputNombres">Rango máximo</label>
                                <input type="text" class="form-control" id="_apellidos" name="_apellidos">
                            </div>
                            </div>
                            <div class="row">
                            <div class="form-group col-md-6">
                                <label for="genero">Tipo de examen</label>
                                <select class="form-control" id="_genero" name="_genero">
                                    <option value="">Seleccionar tipo</option>
                                    <?php
                                    // Recorre los resultados de la consulta
                                    while ($row = $query1->fetch_assoc()) {
                                        echo '<option value="' . $row['idtipoexamen'] . '">' . $row['tipoexam'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="_cargo">Análisis</label>
                                <select class="form-control" id="_cargo" name="_cargo">
                                    <option value="">Seleccionar análisis</option>
                                    <?php
                                    // Recorre los resultados de la consulta
                                    while ($row = $query3->fetch_assoc()) {
                                        echo '<option value="' . $row['idnombretit'] . '">' . $row['nomtit'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            </div>
                        </div>
                        <!-- Reorganización de los combos -->
                        <br>

                        <div class="form-row">
                            <div class="col-md-12 text-end">
                                <button type="submit" class="btn btn-primary" name="registro">Registrar</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Salir</button>
                            </div>
                        </div>
                        <br>
                        </div>
                        
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>

</html>