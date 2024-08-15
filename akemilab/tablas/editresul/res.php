<?php

// Seguridad de sesiones
session_start();
if(!isset($_SESSION['idusuario'])){
    header("Location: ./login/login.php");
 }
// Incluye la conexión a la base de datos
include('../../conexion/conn.php');

// Realiza la consulta a la base de datos
$query = $con->query("SELECT idarea, nomarea FROM area");

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
    

    <title>Exámenes</title>
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
    <h1 class="text-center">Resultados de los Exámenes</h1>
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
                                    <th>Paciente</th>
                                    <th>Código</th>
                                    <th>Fecha</th>
                                    <th>Exámen</th>
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
                url: './vresulta.php',
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

        // Cargar datos para editar examen
        $(document).on('click', '.verBtn', function() {
            var id = $(this).data('idexamen');

            // Crear el formulario dinámicamente
            var form = $('<form action="../Resultados/represult.php" method="post">' +
                '<input type="hidden" name="idexamen" value="' + id + '" />' +
                '</form>');

            // Agregar el formulario al cuerpo del documento
            $('body').append(form);

            // Enviar el formulario
            form.submit();

            // Remover el formulario después de enviarlo
            form.remove();
        });

        $(document).on('click', '.editBtn', function() {
            var id = $(this).data('idexamen'); // Obtener el ID del examen

            // Realizar una solicitud AJAX para obtener los datos completos del examen
            $.ajax({
                url: '../asignaexam/cargafec.php', // Ruta al archivo PHP que carga los datos del examen
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
                            window.location.href = './resultados.php?id=' + id;
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

        // Botón para asignar otro examen
        document.getElementById("nuevoExamenBtn").addEventListener("click", function() {
            window.location.href = "../asignaexam/asigna.php";
        });

        //EL BOTON SEGUIR ASIGNANDO Y REDIRIGIR
        document.getElementById("nuevoExamenBtn").addEventListener("click", function() {
            window.location.href =
                "../asignaexam/asigna.php"; // Reemplaza "otro.php" con la URL de la página a la que deseas redirigir
        });

    });
    </script>
</body>

</html>