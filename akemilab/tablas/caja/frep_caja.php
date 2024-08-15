<?php

// Seguridad de sesiones
session_start();
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

    <title>Caja</title>
    <style>
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
    <h1 class="text-center">Cajas</h1>
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
                                    <th>No</th>
                                    <th>Hora inicio</th>
                                    <th>Hora Fin</th>
                                    <th>Monto inicial</th>
                                    <th>Importe</th>
                                    <th>Estado</th>
                                    <th>Usuario</th>
                                    <th>Reporte</th>
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
        var table = $('#datatable').DataTable({
            serverSide: true,
            processing: true,
            ajax: {
                url: './vcaja.php',
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

        $(document).on('click', '.verBtn', function() {
            var id = $(this).data('idcaja');
            console.log(id);
            // Crear formulario dinámicamente
            var form = $('<form action="./Rep_cajas.php" method="post">' +
                '<input type="hidden" name="idcaja" value="' + id + '" />' +
                '</form>');

            // Agregar formulario al cuerpo del documento
            $('body').append(form);

            // Enviar formulario
            form.submit();

            // Remover formulario después de enviarlo
            form.remove();
        });
    });
    </script>
</body>

</html>