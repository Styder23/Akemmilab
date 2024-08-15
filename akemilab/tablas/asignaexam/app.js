
    $(document).ready(function() {
        $('#examen').on('change', function() {
            var examenId = $(this).val();
            $.ajax({
                url: './obt_precio.php',
                method: 'GET',
                data: {
                    examen_id: examenId
                },
                dataType: 'json',
                success: function(muestra) {
                    $('#precio').val(muestra.precio);
                    $('#preciofinal').val(muestra.precio);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('Error: ' + textStatus, errorThrown);
                    alert('Error al obtener el precio. Por favor, inténtelo de nuevo.');
                }
            });
        });
    });
   
 
    function buscar(event) {
        var dni = $("#dni").val();

        // Verificar si es DNI (8 dígitos)
        var esDNI = /^\d{8}$/.test(buscar);

        console.log("Valor de buscar:", buscar);
        console.log("esDNI:", esDNI);

        // Realizar la búsqueda si es un DNI válido
        if (esDNI || dni.length >= 5) { // Modificado para buscar cualquier entrada mayor o igual a 5 caracteres
            var parametros = {
                "dni": dni
            };

            $.ajax({
                data: parametros,
                dataType: 'json',
                url: './obt_pacien.php',
                type: 'post',
                beforeSend: function() {
                    console.log("enviado", parametros);
                },
                error: function(e) {
                    console.log("Error en la solicitud AJAX");
                    console.log(e);
                },
                complete: function() {
                    console.log("listo");
                },
                success: function(valor) {
                    console.log("Respuesta del servidor:", valor);
                    if (valor.existe === "1") {
                        $("#idpaciente").val(valor.idpacientes);
                        $("#paciente").val(valor.Nombre + ' ' + valor.Apellido);
                        $("#ruc").val(valor.ruc);
                    } else {
                        console.log("Paciente no encontrado");
                        // Limpiar campos si no se encontró el paciente
                        $("#idpaciente").val("");
                        $("#paciente").val("");
                        $("#ruc").val("");
                    }
                }
            });
        } else {
            if (buscar.length >= 2) {
                console.log("El campo debe tener al menos 2 caracteres para buscar.");
            } else {
                console.log("El DNI debe tener 8 dígitos.");
            }

            // Limpiar campos si no cumple con la longitud mínima para buscar
            $("#idpaciente").val("");
            $("#paciente").val("");
            $("#ruc").val("");
        }
    }

    $(document).ready(function() {
        $("#dni").on('input', function(event) {
            buscar(event);
        });
    });
    
   
    let fkTipoComprobante;

    document.getElementById('boletaRadio').addEventListener('change', function() {
        if (this.checked) {
            fkTipoComprobante = 1;
            document.getElementById('tipocomprobante').value = fkTipoComprobante;
        }
    });

    document.getElementById('facturaRadio').addEventListener('change', function() {
        if (this.checked) {
            fkTipoComprobante = 2;
            document.getElementById('tipocomprobante').value = fkTipoComprobante;
        }
    });
   
    const fechaActual = new Date();
    const fechaFormateada =
        `${fechaActual.getFullYear()}-${('0' + (fechaActual.getMonth() + 1)).slice(-2)}-${('0' + fechaActual.getDate()).slice(-2)}T${('0' + fechaActual.getHours()).slice(-2)}:${('0' + fechaActual.getMinutes()).slice(-2)}`;
    document.getElementById('fecha').value = fechaFormateada;
    
    function calcularDescuento() {
        var precio = parseFloat(document.getElementById('precio').value);
        var descuento = parseFloat(document.getElementById('descuento').value);

        if (isNaN(descuento) || descuento === 0) {
            document.getElementById('preciofinal').value = precio.toFixed(2);
        } else {
            var descuentoCalculado = precio - (precio * (descuento / 100));
            document.getElementById('preciofinal').value = descuentoCalculado.toFixed(2);
        }
    }

    
    $(document).ready(function() {
        $('.select2-bootstrap4').select2({
            theme: 'bootstrap4',
            placeholder: 'Seleccione un médico',
            allowClear: true
        });
    });

    document.getElementById('Nuevo').addEventListener('click', function() {
        window.location.href =
            '../paciente/paciente.php'; // Reemplaza 'URL_DESEADA.html' con la URL a la que quieres redirigir
    });
    
    