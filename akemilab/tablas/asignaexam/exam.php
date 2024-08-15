<?php

// Seguridad de sesiones
session_start();
// Incluye la conexión a la base de datos
include('../../conexion/conn.php');

// Realiza la consulta a la base de datos
$query = $con->query("SELECT idtipoexamen, tipoexam FROM tipoexamen");

// Verifica que la consulta sea exitosa
if (!$query) {
    die('Error en la consulta a la base de datos');
}
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
    <link rel="stylesheet" href="tabla.css">
    <title>Exámenes</title>
    <style>
    .green {
        background-color: green;
        color: #fff;
    }

    .reed {
        background-color: red;
        color: #fff;
    }

    .dt-column-order {
        display: none;
    }
    </style>
</head>

<body>
    <h1 class="text-center">Exámenes por paciente</h1>
    <div class="container-fluid">
        <div class="row">
            <div class="container">
                <div class="row">
                    <div class="col-md-2"></div>
                    <div class="col-md-8">
                        <button type="button" style="margin-bottom: 40px;" class="btn btn-primary" id="nuevoExamenBtn">
                            Asignar Otro
                        </button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2"></div>
                    <div class="col-md-8">
                        <table id="datatable" class="table">
                            <thead>
                                <tr>
                                    <th>N°</th>
                                    <th>DNI</th>
                                    <th>Codigo paciente</th>
                                    <th>Paciente</th>
                                    <th>Código</th>
                                    <th>Fecha</th>
                                    <th>Exámen</th>
                                    <th>Perfil</th>
                                    <th>Estado</th>
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
                url: './obtenertabla.php',
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

        $(document).on('click', '.editbtn', function() {
        var id = $(this).data('idexamen');
        $.ajax({
            url: 'cargafec.php',
            method: 'POST',
            data: { id: id },
            success: function(data) {
                try {
                    var json = JSON.parse(data);
                    if (json) {
                        $('#idexa').val(json.idexamen);
                        $('#fec').val(json.fecha);
                        $('#tipoexamen').val(json.fk_idtipoexamen); // Asegúrate de que el campo coincide con la respuesta del servidor
                        $('#editasigna').modal('show');
                    } else {
                        alert('No se encontraron datos para este examen');
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

        // Editar examen
        $(document).on('submit', '#editExaForm', function(event) {
            event.preventDefault();

            // Verificar que todas las variables estén definidas
            var id = $('#idexa').val();
            var fecha = $('#fec').val();
            var examen = $('#tipoexamen').val();

            if (!id || !fecha || !examen) {
                alert('Complete todos los campos');
                return;
            }

            $.ajax({
                url: 'updatefecha.php',
                method: 'POST',
                data: {
                    idexamen: id,
                    fecha: fecha,
                    examen: examen
                },
                success: function(data) {
                    try {
                        var json = JSON.parse(data);
                        if (json.status === 'true') {
                            table.draw();
                            alert('Datos editados correctamente');
                            $('#editasigna').modal('hide');
                        } else {
                            alert('Error al editar el examen');
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


        //FUNCION PARA ELIMINAR EL EXAMEN
        $(document).on('click', '.deleteBtn', function() {
            // Obtener el ID del paciente a eliminar
            var idUsuario = $(this).data('idexamen');
            console.log(idUsuario);
            // Confirmar la eliminación
            if (confirm("¿Estás seguro de que deseas eliminar este paciente?")) {
                // Realizar la solicitud AJAX para eliminar el paciente
                $.ajax({
                    url: './deleteexm.php', // Cambia esto por la URL de tu script PHP para eliminar pacientes
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
                                alert('Examen eliminado correctamente');
                            } else {
                                alert('Error al eliminar el Examen');
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

        $(document).on('click', '.analizaBtn', function() {
            var id = $(this).data('idexamen'); // Obtener el ID del examen

            // Realizar una solicitud AJAX para obtener los datos completos del examen
            $.ajax({
                url: 'cargafec.php', // Ruta al archivo PHP que carga los datos del examen
                method: 'POST', // Método de la solicitud
                data: {
                    id: id
                }, // Datos a enviar al servidor (el ID del examen)
                success: function(data) {
                    // Manejar la respuesta del servidor
                    try {
                        console.log(
                            data); // Imprimir los datos en la consola para verificar
                        var examen = JSON.parse(
                            data); // Convertir la cadena JSON en un objeto

                        // Verificar que examen.fk_tipoexamen esté definido y no sea null o undefined
                        if (examen && typeof examen.fk_idtipoexamen !== 'undefined' &&
                            examen
                            .fk_idtipoexamen !== null) {
                            var tipoExamen = examen.fk_idtipoexamen;

                            // Redirigir siempre a ejemplo.php sin importar el valor de tipoExamen
                            window.location.href = '../Resultados/ejemplo.php?id=' + id;
                        } else {
                            // En caso de que no se pueda determinar el tipo de examen, manejar el caso según tu lógica o mostrar un mensaje de error
                            console.error(
                                'No se pudo determinar el tipo de examen en la respuesta del servidor'
                            );
                            alert('Error al procesar la respuesta del servidor');
                        }
                    } catch (error) {
                        console.error('Error al analizar la respuesta del servidor:',
                            error);
                        alert('Error al procesar la respuesta del servidor');
                    }
                },
                error: function() {
                    console.error('Error en la solicitud AJAX');
                    alert('Error al cargar datos del examen');
                }
            });
        });

        //EL BOTON SEGUIR ASIGNANDO Y REDIRIGIR
        document.getElementById("nuevoExamenBtn").addEventListener("click", function() {
            window.location.href =
                "./asigna.php"; // Reemplaza "otro.php" con la URL de la página a la que deseas redirigir
        });

    });
    </script>

    <!-- Modal agregar examen puedes agregar modal -->


    <!-- Modal editar examen -->
    <div class="modal fade" id="editasigna" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="editExamModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editExamModalLabel">Editar fecha de entrega</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form id="editExaForm" action="javascript:void(0);" method="POST">
                    <div class="modal-body">
                        <input type="hidden" id="idexa" name="idexa">
                        <div class="mb-3 row">
                            <label for="_examen" class="col-sm-2 col-form-label">Fecha Entrega</label>
                            <div class="col-sm-10">
                                <input type="datetime-local" name="fec" class="form-control" id="fec">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="tipoexamen" class="col-sm-2 col-form-label">Tipo Examen</label>
                            <div class="col-sm-10">
                                <select name="tipoexamen" id="tipoexamen" class="form-control">
                                    <option value="">--Seleccione--</option>
                                    <?php
                                        while ($row = $query->fetch_assoc()) {
                                            echo '<option value="' . $row['idtipoexamen'] . '">' . $row['tipoexam'] . '</option>';
                                        }
                                    ?>
                                </select>
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