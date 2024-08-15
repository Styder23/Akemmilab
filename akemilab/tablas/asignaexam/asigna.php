<?php
session_start();
include('../../conexion/conn.php');

$query2 = $con->query("SELECT idtipopago, tipopag FROM tipopago");
$query3 = $con->query("SELECT idestadopa, estadopg FROM estadopago");
$query6 = "SELECT m.idmedicos, m.colegiatura, CONCAT_WS(' ', p.Nombre, p.Apellido) AS Medico
           FROM medicos m
           INNER JOIN personas p ON p.idpersonas = m.fk_personas";
$resultadoMedicos = $con->query($query6);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignación de Exámenes Clínicos</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <!--PARA EL SELECT 2-->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!--PARA LAS ALERTAS-->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!--ESTILOS PARA EL SELECT2-->
    <style>
    /* Estilo personalizado para el select2 */
    .select2-container--bootstrap4 .select2-selection--single {
        background-color: #f8f9fa;
        /* Color de fondo */
        border: 1px solid #ced4da;
        /* Color del borde */
        border-radius: 0.25rem;
        /* Bordes redondeados */
        padding: 0.375rem 0.75rem;
        /* Espaciado interno */
        height: 2.5rem;
        /* Ajustar altura */
        display: flex;
        align-items: center;
        /* Alinear verticalmente el contenido */
    }

    .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
        color: #212529;
        /* Color del texto del elemento seleccionado */
        margin-right: 0.5rem;
        /* Espacio entre el texto y el ícono */
    }

    .select2-container--bootstrap4 .select2-selection--single .select2-selection__clear {
        color: #007bff;
        /* Color del ícono de cerrar */
    }

    .select2-container--bootstrap4 .select2-results__option--highlighted {
        background-color: #007bff;
        /* Color de fondo al seleccionar */
        color: #fff;
        /* Color del texto al seleccionar */
    }

    .select2-container--bootstrap4 .select2-results__option {
        color: #495057;
        /* Color del texto de las opciones */
    }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h2 class="mb-4">Asignación de Exámenes Clínicos</h2>
        <form id="examenForm">
            <div class="form-row align-items-center mb-3">
                <div class="col-auto">
                    <label for="dni" class="col-form-label"><b>DNI/COD:</b></label>
                </div>
                <div class="col-auto">
                    <input type="text" class="form-control mb-2" name="dni" id="dni">
                    <input type="hidden" id="idpaciente" name="idpaciente" disabled>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-primary mb-2" id="Nuevo">Add</button>
                </div>
                <div class="col-auto">
                    <label for="paciente" class="col-form-label">Paciente</label>
                </div>
                <div class="col">
                    <input type="text" class="form-control mb-2" name="paciente" id="paciente" disabled>
                </div>
            </div>
            <hr>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <div class="col-auto">
                        <label for="medico_resultado" class="col-form-label"><b>SELECCIONE AL
                                MÉDICO:</b></label>
                    </div>
                    <div class="col">
                        <select class="form-control select2-bootstrap4" id="medico_resultado" name="medico_resultado">
                            <option value="">--SELECCIONE--</option>
                            <?php
                            // Iterar sobre los resultados y mostrar en el select
                            if ($resultadoMedicos && $resultadoMedicos->num_rows > 0) {
                                while ($row = $resultadoMedicos->fetch_assoc()) {
                                    echo '<option value="' . $row['idmedicos'] . '">' . $row['Medico'] . ' - ' . $row['colegiatura'] . '</option>';
                                }
                            } else {
                                echo '<option value="">No se encontraron médicos</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class=" form-group col-md-6">
                    <label for="fecha">Fecha</label>
                    <input type="datetime-local" class="form-control" name="fecha" id="fecha" value="" disabled>
                </div>
            </div>
            <hr>
            <div class="form-row">

                <div class="col-md-12">
                    <label for="buscarExamen">Buscar Examen</label>
                    <input type="text" class="form-control" name="buscarExamen" id="buscarExamen"
                        placeholder="Buscar Examen">
                    <div id="resultadosBusqueda" class="list-group"></div>
                    <div id="resultadosBusquedaExamen" class="list-group"></div>
                </div>
            </div><br>
            <div class="form-row align-items-center mb-3">
                <div class="col">
                <label for="precio">Precio Parcial</label>
                    <input type="text" class="form-control mb-2" id="precio" name="precio" placeholder="Precio"
                        value="23" disabled>
                </div>
                <div class="col">
                    <label for="descuento">Descuento por examen</label>
                    <div class="input-group mb-2">
                        <input type="text" class="form-control" id="descuento" name="descuento" placeholder="Descuento"
                            oninput="calcularDescuento()">
                        <div class="input-group-append">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <label for="descuentotot">Descuento total</label>
                    <div class="input-group mb-2">
                        <input type="text" class="form-control" id="descuentotot" name="descuentotot"
                            placeholder="Descuento total" oninput="calcularDescuentoTotal()">
                        <div class="input-group-append">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <label for="motivo_descuento">Motivo del Descuento</label>
                    <input type="text" class="form-control mb-2" id="motivo" name="motivo"
                        placeholder="Motivo del Descuento">
                </div>
                <div class="col">
                    <label for="preciodescuento">Precio Final</label>
                    <input type="text" class="form-control mb-2" id="preciofinal" name="preciofinal"
                        placeholder="Precio con Descuento" disabled>
                </div>
            </div>

            <hr>
            <div class="form-row align-items-start mb-3">
                <div class="col-md-4">
                    <label>Elija tipo de comprobante:</label><br>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="tipo_Comprobante" id="boletaRadio"
                            value="boleta">
                        <label class="form-check-label" for="boletaRadio">Boleta</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="tipo_Comprobante" id="facturaRadio"
                            value="factura">
                        <label class="form-check-label" for="facturaRadio">Factura</label>
                    </div>
                    <input type="hidden" name="tipocomprobante" id="tipocomprobante">
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="tipo_pago">Tipo Pago</label>
                        <select name="tipo_pago" id="tipo_pago" class="form-control">
                            <option value="">--Seleccione--</option>
                            <?php
                            while ($row = $query2->fetch_assoc()) {
                                echo '<option value="' . $row['idtipopago'] . '">' . $row['tipopag'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="estado_pago">Estado Pago</label>
                        <select name="estado_pago" id="estado_pago" class="form-control">
                            <option value="">--Seleccione--</option>
                            <?php
                            while ($row = $query3->fetch_assoc()) {
                                echo '<option value="' . $row['idestadopa'] . '">' . $row['estadopg'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <button type="button" class="btn btn-success" id="agregar">Agregar</button>
                <button type="button" class="btn btn-primary" id="verorden">Visualiza Orden</button>
            </div>
        </form>
        <div id="totalParcialContainer">
            <p>Total Parcial: <span id="totalParcial">0.00</span></p>
            <p>Total con Descuento: <span id="totalConDescuento">0.00</span></p>
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>DNI</th>
                    <th>PACIENTE</th>
                    <th>EXAMEN</th>
                    <th>FECHA</th>
                    <th>MUESTRA</th>
                    <th>PRECIO</th>
                    <th></th>                    
                    <th>Acción</th>
                    
                </tr>
            </thead>
            <tbody id="examenesAsignados">
                <!-- Aquí se agregarán las filas dinámicamente -->
            </tbody>
        </table>
        <button type="button" class="btn btn-success" id="guardar" name="guardar">Guardar Asignaciones</button>

    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
    document.getElementById('guardar').addEventListener('click', function() {
        // Obtener los datos almacenados en el localStorage
        const data = JSON.parse(localStorage.getItem('examenes'));

        if (!data || data.length === 0) {
            console.log('No hay datos en el JSON.');
            return;
        }

        // Mostrar los datos en la consola
        console.log(data);

        // Hacer la llamada AJAX para guardar los datos en la base de datos
        fetch('guarda.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                console.log(result);
                if (result.success) {
                    alert('Datos guardados correctamente.');
                } else {
                    alert('Error al guardar los datos: ' + result.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al guardar los datos.');
            });
    });
    </script>
    <script>
        //EL BOTON SEGUIR ASIGNANDO Y REDIRIGIR
    document.getElementById("verorden").addEventListener("click", function() {
        window.location.href =
            "../orden/orden.php"; // Reemplaza "otro.php" con la URL de la página a la que deseas redirigir
    });
    </script>
    <script src="app.js"></script>
    <script src="./funciones.js"></script>

</body>

</html>