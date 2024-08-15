<?php 
// session_start();
// if(!isset($_SESSION['idusuario'])){
//     header("Location: ./login/login.php");
//  }
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link href="https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.0.7/datatables.min.css" rel="stylesheet">

    <title>Historial</title>
    <style>
        /* Estilo para la cabecera de la tabla */
        .table thead {
            background-color: #007bff;
            /* Color azul para la cabecera */
            color: #fff;
            /* Texto blanco para contraste */
        }

        /* Estilo para filas pares del cuerpo */
        .table tbody tr:nth-child(even) {
            background-color: #f2f2f2;
            /* Color gris claro para filas pares */
        }

        /* Estilo para filas impares del cuerpo */
        .table tbody tr:nth-child(odd) {
            background-color: #e9ecef;
            /* Color gris más claro para filas impares */
        }

        /* Estilo para el estado "Pendiente" */
        .estado-pendiente {
            background-color: #dc3545;
            /* Color rojo para fondo */
            color: #fff;
            /* Texto blanco */
        }

        /* Estilo para el estado "Completo" */
        .estado-completo {
            background-color: #28a745;
            /* Color verde para fondo */
            color: #fff;
            /* Texto blanco */
        }
        .dt-column-order {
            display: none;
        }
    </style>
</head>

<body>
    <h1 class="text-center">Historial Medico</h1>
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
                                    <th>N°</th>
                                    <th>Codigo</th>
                                    <th>Muestra</th>
                                    <th>Fecha</th>
                                    <th>Cod paciente</th>
                                    <th>DNI</th>
                                    <th>Paciente</th>
                                    <th>Examen</th>
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
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.0.7/datatables.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            // Inicializar DataTable
            var table = $('#datatable').DataTable({
                serverSide: true,
                processing: true,
                ajax: {
                    url: './vhistorial.php',
                    type: 'POST',
                },
                columnDefs: [
        { targets: [0], visible: false },  // Oculta la primera columna
        { targets: [3], orderable: false }
    ],
                createdRow: function(row, data, dataIndex) {
                    // Añadir clases a las celdas de estado según su valor
                    if (data[8] === 'Pendiente') {
                        $('td', row).eq(8).addClass('estado-pendiente');
                    } else if (data[8] === 'Completo') {
                        $('td', row).eq(8).addClass('estado-completo');
                    }

                    // Añadir el botón de acción
                    var actions = '<button type="button" class="btn btn-primary verBtn" data-idexamen="' + data[0] + '">Ver</button>';
                    $('td', row).eq(9).html(actions);
                }
            });

            // Cargar datos para editar examen
            $(document).on('click', '.verBtn', function () {
                var id = $(this).data('idexamen');
                var estado = $(this).closest('tr').find('td').eq(8).text().trim();
                console.log(estado);
                if (estado === 'Completo') {
                    // Crear el formulario dinámicamente
                    var form = $('<form action="represult.php" method="post">' +
                        '<input type="hidden" name="idexamen" value="' + id + '" />' +
                        '</form>');

                    // Adjuntar el formulario al cuerpo del documento
                    $('body').append(form);

                    // Enviar el formulario
                    form.submit();

                    // Remover el formulario después de enviarlo (opcional)
                    form.remove();
                } else if (estado === 'Pendiente') {
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
                                console.log(data); // Imprimir los datos en la consola para verificar
                                var examen = JSON.parse(data); // Convertir la cadena JSON en un objeto

                                // Verificar que examen.fk_tipoexamen esté definido y no sea null o undefined
                                if (examen && typeof examen.fk_idtipoexamen !== 'undefined' && examen.fk_idtipoexamen !== null) {
                                    var tipoExamen = examen.fk_idtipoexamen;

                                    // Redirigir siempre a ejemplo.php sin importar el valor de tipoExamen
                                    window.location.href = '../Resultados/ejemplo.php?id=' + id;
                                } else {
                                    // En caso de que no se pueda determinar el tipo de examen, manejar el caso según tu lógica o mostrar un mensaje de error
                                    console.error('No se pudo determinar el tipo de examen en la respuesta del servidor');
                                    alert('Error al procesar la respuesta del servidor');
                                }
                            } catch (error) {
                                console.error('Error al analizar la respuesta del servidor:', error);
                                alert('Error al procesar la respuesta del servidor');
                            }
                        },
                        error: function() {
                            console.error('Error en la solicitud AJAX');
                            alert('Error al cargar datos del examen');
                        }
                    });
                }
            });

            // EL BOTON SEGUIR ASIGNANDO Y REDIRIGIR
            document.getElementById("nuevoExamenBtn").addEventListener("click", function () {
                window.location.href = "../asignaexam/asigna.php";
            });
        });
    </script>
</body>

</html>
