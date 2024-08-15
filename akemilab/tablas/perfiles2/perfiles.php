<?php

// Seguridad de sesiones
session_start();
if (!isset($_SESSION['idusuario'])) {
    header("Location: ./login/login.php");
}
// Incluye la conexión a la base de datos
include('../../conexion/conn.php');


$query2 = $con->query("SELECT * FROM tipoexamen");


?>
<?php include("../perfiles2/conbd.php") ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Médicos</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link href="https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.0.7/datatables.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/habibmhamadi/multi-select-tag@3.1.0/dist/css/multi-select-tag.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Ocultar el span ícono del ordenamiento del datatable -->
    <style>
        .dt-column-order {
            display: none;
        }

        .row {
            margin: 20px;
        }
    </style>
    <!-- Fin Ocultar el span ícono del ordenamiento del datatable -->
</head>

<body>
    <div class="row">
        <div class="col-5">
            <form action="" method="post" id="miFormulario">
                <div class="card">
                    <div class="card-header">
                        Perfiles
                    </div>
                    <div class="card-body">
                        <div class="mb-3 d-none">
                            <label for="id" class="form-label">ID</label>
                            <input type="text" class="form-control" name="id" id="id" aria-describedby="helpId" value="<?php echo $id ?>" />
                        </div>
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="nombre" id="nombre" aria-describedby="helpId" placeholder="Escriba el nombre del perfil" value="<?php echo $nombre ?>" required />
                        </div>
                        <div class="mb-3">
                            <label for="precio" class="form-label">Precio</label>
                            <input type="text" class="form-control" name="precio" id="precio" aria-describedby="helpId" placeholder="Escriba el precio" value="<?php echo $precio ?>" required />
                        </div>
                        <div class="mb-3">
                            <label for="" class="form-label">
                                Exámenes del perfil:
                            </label>
                            <select multiple name="examenes[]" id="listaExamenes">
                                <?php foreach ($examenes as $examen) { ?>
                                    <option <?php
                                            if (!empty($arregloExamenes)) :
                                                if (in_array($examen["idtipoexamen"], $arregloExamenes)) :
                                                    echo "selected";
                                                endif;
                                            endif;
                                            ?> value="<?php echo $examen["idtipoexamen"]; ?>">
                                        <?php echo $examen["tipoexam"]; ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <span id="errorMensaje" class="error-message" style="display:none;color:red">Seleccione como mínimo un examen.</span>
                        </div>

                        <div class="btn-group" role="group" aria-label="">
                            <button type="submit" name="accion" value="agregar" class="btn btn-success">
                                Agregar
                            </button>
                            <button type="button" id="btnEditar" class="btn btn-warning">
                                Editar
                            </button>
                            <button type="button" id="btnEliminar" class="btn btn-danger">
                                Eliminar
                            </button>
                            <button type="button" id="btnCancelar" class="btn btn-dark" style="display:none">
                                Cancelar
                            </button>
                        </div>

                    </div>
                </div>
            </form>
        </div>
        <div class="col-7">
            <table class="table">
                <thead>
                    <tr>
                        <th>Perfil</th>
                        <th>Precio</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($perfiles  as $perfil) : ?>
                        <tr>
                            <td>
                                <?php echo $perfil["nomperfil"]; ?>
                            </td>
                            <td>
                                S/. <?php echo $perfil["precioperfil"]; ?>
                            </td>
                            <td>
                                <form action="" method="post">
                                    <input type="hidden" name="id" value="<?php echo $perfil["idperfil"]; ?>">
                                    <input type="submit" value="Seleccionar" name="accion" class="btn btn-info">
                                </form>
                                
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
    <script src="https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.0.7/datatables.min.js"></script>



    <script src="https://cdn.jsdelivr.net/gh/habibmhamadi/multi-select-tag@3.1.0/dist/js/multi-select-tag.js"></script>
    <script>
        $('#precio').on('input', function() {
                var monto = $(this).val();
                
                // Permitir solo números, un punto, y hasta dos decimales
                monto = monto.replace(/[^0-9.]/g, ''); // Eliminar caracteres no numéricos y no punto
                if (monto.split('.').length > 2) { // Si hay más de un punto
                    monto = monto.replace(/\.+$/, ""); // Eliminar el último punto
                }
                
                var parts = monto.split('.');
                if (parts[1]) {
                    parts[1] = parts[1].substring(0, 2); // Limitar a dos decimales
                }
                
                monto = parts.join('.');
                $(this).val(monto);
        });



        document.addEventListener('DOMContentLoaded', function() {
            new MultiSelectTag('listaExamenes', {
                rounded: true, // default true
                shadow: false, // default false
                placeholder: 'Buscar', // default Search...
                tagColor: {
                    textColor: '#327b2c',
                    borderColor: '#92e681',
                    bgColor: '#eaffe6',
                },
                onChange: function(values) {
                    console.log(values);
                    // Ocultar el mensaje de error si hay al menos una opción seleccionada
                    if (values.length > 0) {
                        document.getElementById('errorMensaje').style.display = 'none';
                    }
                }
            });

            document.getElementById('miFormulario').addEventListener('submit', function(event) {
                // Obtener los valores seleccionados
                var listaExamenes = document.getElementById('listaExamenes');
                var selectedOptions = listaExamenes.selectedOptions;

                if (selectedOptions.length === 0) {
                    // Mostrar el mensaje de error
                    document.getElementById('errorMensaje').style.display = 'block';
                    event.preventDefault(); // Prevenir el envío del formulario
                } else {
                    // Ocultar el mensaje de error
                    document.getElementById('errorMensaje').style.display = 'none';
                }
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var success = <?php echo json_encode($success); ?>;
            var mensaje = <?php echo json_encode($mensaje); ?>;
            if(mensaje!=""){
                if (success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: mensaje
                    }).then(function() {
                        $('#precio').val("");
                        $('#nombre').val("");
                        $('#id').val("");
                    });        
                } else if(mensaje="El perfil ya existe"){
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: mensaje
                    }).then(function() {
                        $('#precio').val("");
                        $('#nombre').val("");
                        $('#id').val("");
                    });
                } else{
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: mensaje
                    }).then(function() {
                        $('#precio').val("");
                        $('#nombre').val("");
                        $('#id').val("");
                    }); 
                }
            }        
        });
        document.getElementById('btnEditar').addEventListener('click', function(event) {
            event.preventDefault(); // Previene el envío inmediato del formulario
            // Obtén el valor del input
            var id = document.getElementById('id').value.trim();
            var success = <?php echo json_encode($success); ?>;
            var mensaje = <?php echo json_encode($mensaje); ?>;
            if (id === '') {
                Swal.fire({
                    icon: 'warning',
                    title: '¡Advertencia!',
                    text: 'Selecciona un elemento.'
                });
            }else{

                        // Cambia el valor del botón 'accion' a 'borrar' y envía el formulario
                        var form = document.getElementById('miFormulario');
                        var input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'accion';
                        input.value = 'editar';
                        form.appendChild(input);
                        form.submit();


                        if(mensaje!=""){
                            if (success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Éxito',
                                    text: mensaje
                                }).then(function() {
                                    $('#precio').val("");
                                    $('#nombre').val("");
                                    $('#id').val("");
                                }); 
                            } else{
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: "Error al editar"
                                });
                            }
                        }    

            }            
        });
        document.getElementById('btnEliminar').addEventListener('click', function(event) {
            event.preventDefault(); // Previene el envío inmediato del formulario

            // Obtén el valor del input
            var id = document.getElementById('id').value.trim();
            if (id === '') {
                Swal.fire({
                    icon: 'warning',
                    title: '¡Advertencia!',
                    text: 'Selecciona un elemento.'
                });
            }else{
                Swal.fire({
                title: '¿Estás seguro?',
                text: 'Los registros relacionados a este perfil se eliminarán.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Cambia el valor del botón 'accion' a 'borrar' y envía el formulario
                        var form = document.getElementById('miFormulario');
                        var input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'accion';
                        input.value = 'borrar';
                        form.appendChild(input);
                        form.submit();


                        if(mensaje!=""){
                            if (success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Éxito',
                                    text: mensaje
                                }).then(function() {
                                    $('#precio').val("");
                                    $('#nombre').val("");
                                    $('#id').val("");
                                }); 
                            } else{
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: "Error al eliminar"
                                });
                            }
                        }    
                    }

                });
            }            
        });
        document.getElementById('btnCancelar').addEventListener('click', function(event) {
            $('#precio').val("");
            $('#nombre').val("");
            $('#id').val("");
            $('#listaExamenes').val("");
        })
    </script>
    <script>
            $(document).ready(function(){
                $("table").DataTable({
                    "pageLength":5,
                    lengthMenu:[
                        [5,10,25,50],
                        [5,10,25,50]
                    ],
                    "language":{
                        "url":"https://cdn.datatables.net/plug-ins/1.13.2/i18n/es-MX.json"
                    }
                });
            });
    </script>


</body>

</html>