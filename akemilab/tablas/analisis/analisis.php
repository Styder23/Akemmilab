<?php
// Seguridad de sesiones
session_start();

include('../../conexion/conn.php');
$query = $con->query("SELECT idtipoexamen, tipoexam FROM tipoexamen");
$query1 = $con->query("SELECT idunidades, unidades FROM unidades");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignar Datos</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
    /* Estilo personalizado para Select2 */
    .select2-container--bootstrap4 .select2-selection--single {
        height: calc(2.25rem + 2px);
        padding: .375rem .75rem;
        font-size: 1rem;
        line-height: 1.5;
        color: #495057;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #ced4da;
        border-radius: .25rem;
        transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
        display: flex;
        align-items: center;
    }

    .select2-container--bootstrap4 .select2-selection--single:focus {
        border-color: blue;
        outline: 0;
        box-shadow: 0 0 0 .2rem rgba(0, 123, 255, .25);
    }

    /* Estilo para el color de la letra en select2 */
    .select2-selection__rendered {
        color: #37414B;
        /* Color de la letra */
        padding-right: 1.75rem;
        /* Espacio para el icono de limpieza */
    }

    /* Estilo para el icono de limpieza */
    .select2-selection__clear {
        position: absolute;
        right: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1rem;
        color: #a1a1a1;
    }

    /* Estilo para el color de fondo de la opción seleccionada en select2 */
    .select2-results__option[aria-selected=true] {
        background-color: #007bff;
        /* Color de fondo de la opción seleccionada */
        color: #000;
        /* Color de la letra en la opción seleccionada */
    }

    /* Estilo para el color de fondo de las opciones en select2 */
    .select2-results__option {
        padding: 10px;
        /* Añadir un padding para mejor legibilidad */
        color: #495057;
        /* Color de la letra en las opciones */
        background-color: #fff;
        /* Color de fondo de las opciones */
        transition: background-color 0.3s ease;
        /* Efecto de transición suave */
    }

    /* Estilo para el hover en las opciones de select2 */
    .select2-results__option:hover {
        background-color: #93C3F3;
        /* Color de fondo al hacer hover */
        cursor: pointer;
        /* Cambiar el cursor a pointer */
    }

    /* Corregir posición del contenedor de resultados */
    .select2-container .select2-dropdown {
        z-index: 10000;
        /* Ajustar el z-index para que el dropdown se muestre sobre otros elementos */
        margin-top: -1px;
        /* Alinear el dropdown con el campo de selección */
    }
    </style>
    <script>
    function agregarFila() {
        const examen = $("#texamen").val();
        const analisis = $("#analisis").val();
        const datos = $("#datos").val();
        const valores1 = $("#valores1").val();
        const valores2 = $("#valores2").val();
        const unidades = $("#unidades").val();
        const nombreUnidad = $("#nombreUnidad").val();

        if (!examen) {
            Swal.fire({
                icon: 'warning',
                title: 'Advertencia',
                text: 'Por favor, seleccione un examen',
            });
            return;
        }

        if (!datos) {
            Swal.fire({
                icon: 'warning',
                title: 'Advertencia',
                text: 'Por favor, ingrese un dato para el análisis',
            });
            return;
        }

        const nuevaFila = `
                <tr>
                    <td>${analisis}</td>
                    <td>${datos}</td>
                    <td>${valores1}-${valores2}</td>
                    <td>${nombreUnidad}</td>
                    <td><button type="button" class="btn btn-danger" onclick="eliminarFila(this)">Eliminar</button></td>
                    <td><input type="hidden" value="${examen}" readonly></td>
                    <td><input type="hidden" value="${unidades}" readonly></td>
                </tr>
            `;

        $("#tablaDatos tbody").append(nuevaFila);
        // Limpiar los inputs
        $("#datos").val('');
        $("#valores1").val('');
        $("#valores2").val('');
        $("#unidades").val('');
        $("#nombreUnidad").val('');

    }

    function eliminarFila(btn) {
        $(btn).closest('tr').remove();
    }

    function guardarDatos() {
        const analisis = $("#analisis").val();
        const datosArray = [];
        $("#tablaDatos tbody tr").each(function() {
            const fila = $(this);
            const analisis = fila.find("td:eq(0)").text();
            const datos = fila.find("td:eq(1)").text();
            const valores = fila.find("td:eq(2)").text();
            const unidades = fila.find("td:eq(3)").text();
            const texamen = fila.find("input[type='hidden']:eq(0)").val();
            const idunidades = fila.find("input[type='hidden']:eq(1)").val();

            datosArray.push({
                analisis: analisis,
                datos: datos,
                valores: valores,
                texamen: texamen,
                unidades: idunidades
            });
        });

        if (datosArray.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Advertencia',
                text: 'No hay datos para guardar',
            });
            return;
        }

        const jsonData = {
            datosArray: datosArray
        };

        $.ajax({
            type: "POST",
            url: "guardadato.php",
            data: JSON.stringify(jsonData),
            contentType: "application/json",
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: 'Datos guardados exitosamente',
                }).then(() => {
                    // Limpiar la tabla después de mostrar la alerta
                    $("#analisis").val('');
                    $("#texamen").val('').trigger('change'); // Limpiar y resetear select2
                    $("#tablaDatos tbody").empty();
                });
            },
            error: function(err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: `Error al guardar los datos: ${err.responseText}`,
                });
            }
        });
    }

    $(document).ready(function() {
        $('#unidades').change(function() {
            var nombreUnidad = $('#unidades option:selected').text();
            if ($('#unidades').val() === "") {
                $('#nombreUnidad').val('');
            } else {
                $('#nombreUnidad').val(nombreUnidad);
            }
        });
    });
    </script>
</head>

<body>
    <div class="container">
        <h1>Asignar Datos</h1>
        <form id="formDatos" onsubmit="event.preventDefault(); guardarDatos();">
            <div class="row mb-3">
                <div class="col">
                    <label for="texamen">Exámen</label>
                    <select name="texamen" id="texamen" class="form-control select2-bootstrap4">
                        <option value="">--Seleccione--</option>
                        <?php
                        // Recorre los resultados de la consulta
                        while ($row = $query->fetch_assoc()) {
                            echo '<option value="' . $row['idtipoexamen'] . '">' . $row['tipoexam'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="col">
                    <label for="analisis">Análisis</label>
                    <input type="text" class="form-control" name="analisis" id="analisis">
                </div>
                <div class="col">
                    <label for="datos">Datos</label>
                    <input type="text" class="form-control" name="datos" id="datos">
                </div>
                <div class="col">
                    <label for="valores">Valores</label>
                    <input type="text" class="form-control" name="valores1" id="valores1" placeholder="min"><br>
                    <input type="text" class="form-control" name="valores2" id="valores2" placeholder="max">
                </div>
                <div class="col">
                    <label for="unidades">Unidades</label>
                    <select name="unidades" id="unidades" class="form-control">
                        <option value="">--Seleccione--</option>
                        <?php
                        // Recorre los resultados de la consulta
                        while ($row = $query1->fetch_assoc()) {
                            echo '<option value="' . $row['idunidades'] . '">' . $row['unidades'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="col">
                    <input type="hidden" id="nombreUnidad" name="nombreUnidad" class="form-control" readonly>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col">
                    <button type="button" class="btn btn-primary" onclick="agregarFila()">Agregar</button>
                    <button type="submit" class="btn btn-success">Guardar</button>
                </div>
            </div>
        </form>
        <table class="table" id="tablaDatos">
            <thead>
                <tr>
                    <th>Analisis</th>
                    <th>Datos</th>
                    <th>Valores</th>
                    <th>Unidades</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <!-- Las filas se agregarán aquí -->
            </tbody>
        </table>
    </div>
    <script>
    $(document).ready(function() {
        $('.select2-bootstrap4').select2({
            theme: 'bootstrap4',
            placeholder: 'Seleccione un examen',
            allowClear: true
        });
    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</body>

</html>