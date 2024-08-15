<?php




// Seguridad de sesiones
session_start();
if(!isset($_SESSION['idusuario'])){
    header("Location: ./login/login.php");
 }
// Incluye la conexión a la base de datos
include('../../conexion/conn.php');

?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link href="https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.0.7/datatables.min.css" rel="stylesheet">

    <title>Exámenes</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .dt-column-order {
            display: none;
        }
    </style>
</head>

<body>
    <h1 class="text-center">Unidades</h1>
    <div class="container-fluid">
        <div class="row">
            <div class="container">
                <div class="row">
                    <div class="col-md-2"></div>
                    <div class="col-md-8">
                        <button type="button" style="margin-bottom: 40px;" class="btn btn-primary"
                            data-bs-toggle="modal" data-bs-target="#agregarexamen">
                            Nueva Unidad
                        </button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2"></div>
                    <div class="col-md-8">
                        <table id="datatable" class="table">
                            <thead>
                                <tr>
                                    <th>Unidad</th>
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
        // Inicializar DataTable
        var table = $('#datatable').DataTable({
            serverSide: true,
            processing: true,
            ajax: {
                url: './viarea.php',
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

        // Agregar area
        $(document).on('submit', '#frmexam', function(event) {
            event.preventDefault();
            var area = $('#area').val();
            if (area) {
                $.ajax({
                    url: './insertarea.php',
                    method: 'POST',
                    data: {
                        area: area,
                    },
                    success: function(response) {
                        var data = JSON.parse(response);
                        if (data.status === 'true') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: data.message
                            }).then(function() {
                                location.reload();
                            });
                            $('#area').val('');
                            $('#agregarexamen').modal('hide');
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message
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
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Advertencia',
                    text: 'Complete todos los campos'
                });
            }
        });
        //--------Fin Agregar nuevo registro-------//

        // Cargar datos para editar examen
        $(document).on('click', '.editbtn', function() {
            var id = $(this).data('idarea');
            $.ajax({
                url: './cargaarea.php',
                method: 'POST',
                data: {
                    id: id
                },
                success: function(data) {
                    try {
                        var json = JSON.parse(data);
                        if (json) {
                            $('#idexamen').val(json.idunidades);
                            $('#_area').val(json.unidades);
                            $('#editexmmodal').modal('show');
                        } else {
                            alert('No se encontraron datos para esta área clinica');
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

        $(document).on('submit', '#editExamenForm', function(event) {
            event.preventDefault();
            var idarea=$('#idexamen').val();
            var nomarea=$('#_area').val();

            $.ajax({
                url: './updatearea.php',
                method: 'POST',
                data: {
                    idarea:idarea,
                    nomarea:nomarea
                },
                success: function(data) {
                    try {
                        var json = JSON.parse(data);
                        if (json.status === 'true') {
                            table.draw();
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: json.message
                            });
                            $('#editexmmodal').modal('hide');
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: json.message
                            });
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
        });

        // eliminar area clinica
        $(document).on('click', '.deleteBtn', function() {
            var idarea = $(this).data('idarea');
            if (confirm("¿Estás seguro de que deseas eliminar esta unidad?")) {
                $.ajax({
                    url: './deleteaea.php', // Cambia esto por la URL de tu script PHP para eliminar pacientes
                    method: 'POST',
                    data: {
                        id: idarea
                    },
                    success: function(data) {
                        try {
                            var json = JSON.parse(data);
                            console.log(json);
                            if (json.status === 'true') {
                                table.ajax.reload(null, false);
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Éxito',
                                    text: 'Unidad eliminado correctamente'
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Error al eliminar el unidad'
                                });
                            }
                        } catch (e) {
                            console.error('Error al analizar JSON:', e);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Error al procesar respuesta del servidor'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error en la solicitud AJAX'
                        });
                    }
                });
            }
        });
    });
    </script>

    <!-- Modal agregar examen -->
    <div class="modal fade" id="agregarexamen" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="agregarexamenLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="agregarexamenLabel">Agregar área</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form id="frmexam" action="javascript:void(0);" method="POST">
                    <div class="modal-body">
                        <!-- Formulario -->
                        <div class="mb-3 row">
                            <label for="examen" class="col-sm-2 col-form-label">AREA</label>
                            <div class="col-sm-10">
                                <input type="text" name="area" class="form-control" id="area"
                                    placeholder="Ingrese examen" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Agregar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal editar examen -->
    <div class="modal fade" id="editexmmodal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="editExamModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editExamModalLabel">Modificar Área</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form id="editExamenForm" action="javascript:void(0);" method="POST">
                    <div class="modal-body">
                        <input type="hidden" id="idexamen" name="idexamen">

                        <div class="mb-3 row">
                            <label for="_examen" class="col-sm-2 col-form-label">Area</label>
                            <div class="col-sm-10">
                                <input type="text" name="_area" class="form-control" id="_area"
                                    placeholder="Ingrese area" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>