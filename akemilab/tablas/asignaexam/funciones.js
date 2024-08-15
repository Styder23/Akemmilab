document.addEventListener('DOMContentLoaded', function () {
    // Borrar los datos del localStorage al recargar la página
    localStorage.removeItem('jsonData');

    let examenSeleccionado = null;
    let totalParcial = 0;
    let perfilesAgregados = new Set(); // Conjunto para mantener los perfiles añadidos
    let resultadosBusquedaSeleccionado = -1; // Índice del resultado seleccionado

    
    // Función para buscar exámenes y perfiles
    function buscarExamenes(query) {
        $.ajax({
            url: 'busexamen.php',
            type: 'GET',
            data: { query: query },
            success: function (response) {
                const resultados = JSON.parse(response).examenes.concat(JSON.parse(response).perfiles);
                mostrarResultadosBusqueda(resultados);
            },
            error: function () {
                console.error('Error al buscar exámenes');
            }
        });
    }

    // Función para mostrar resultados de búsqueda
    function mostrarResultadosBusqueda(resultados) {
        const resultadosBusqueda = document.getElementById('resultadosBusqueda');
        resultadosBusqueda.innerHTML = '';
        resultados.forEach((examen, index) => {
            const item = document.createElement('a');
            item.href = '#';
            item.className = 'list-group-item list-group-item-action';
            item.textContent = examen.nombre;
            item.dataset.id = examen.id;
            item.dataset.precio = examen.precio;
            item.dataset.area = examen.area;
            item.dataset.tipo = examen.tipo;
            item.dataset.index = index;
            item.addEventListener('click', function (e) {
                e.preventDefault();
                seleccionarExamen(examen);
            });
            resultadosBusqueda.appendChild(item);
        });
    }

    // Función para seleccionar un examen
    function seleccionarExamen(examen) {
        examenSeleccionado = examen;
        document.getElementById('buscarExamen').value = examen.nombre;
        document.getElementById('precio').value = examen.precio;
        document.getElementById('preciofinal').value = examen.precio;
        document.getElementById('resultadosBusqueda').innerHTML = '';
        resultadosBusquedaSeleccionado = -1;
    }

    // funcion para calcular los descuentos

    function calcularDescuento() {
        const precio = parseFloat(document.getElementById('precio').value) || 0;
        const descuento = parseFloat(document.getElementById('descuento').value) || 0;

        if (isNaN(descuento)) {
            console.error('El descuento no es un número válido.');
            return;
        }

        const precioFinal = precio - (precio * (descuento / 100));
        document.getElementById('preciofinal').value = precioFinal.toFixed(2);
    }

    function calcularDescuentoTotal() {
        const descuentoTotal = parseFloat(document.getElementById('descuentotot').value) || 0;

        if (isNaN(descuentoTotal)) {
            console.error('El descuento total no es un número válido.');
            return;
        }

        const totalConDescuento = totalParcial - (totalParcial * (descuentoTotal / 100));
        document.getElementById('totalConDescuento').textContent = totalConDescuento.toFixed(2);
    }

    document.getElementById('descuento').addEventListener('input', calcularDescuento);
    document.getElementById('descuentotot').addEventListener('input', calcularDescuentoTotal);




    // Manejar evento de entrada en el campo de búsqueda
    document.getElementById('buscarExamen').addEventListener('input', function (e) {
        const query = e.target.value;
        if (query.length > 2) {
            buscarExamenes(query);
        } else {
            document.getElementById('resultadosBusqueda').innerHTML = '';
        }
    });

    // Manejar eventos de teclado para la búsqueda de exámenes
    document.getElementById('buscarExamen').addEventListener('keydown', function (e) {
        const resultadosBusqueda = document.querySelectorAll('#resultadosBusqueda .list-group-item');
        if (e.key === 'ArrowDown') {
            resultadosBusquedaSeleccionado = (resultadosBusquedaSeleccionado + 1) % resultadosBusqueda.length;
            resaltarResultadoBusqueda(resultadosBusqueda);
        } else if (e.key === 'ArrowUp') {
            resultadosBusquedaSeleccionado = (resultadosBusquedaSeleccionado - 1 + resultadosBusqueda.length) % resultadosBusqueda.length;
            resaltarResultadoBusqueda(resultadosBusqueda);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (resultadosBusquedaSeleccionado >= 0 && resultadosBusquedaSeleccionado < resultadosBusqueda.length) {
                resultadosBusqueda[resultadosBusquedaSeleccionado].click();
            } else if (examenSeleccionado) {
                agregarFila();
            }
        }
    });

    // Función para resaltar el resultado de búsqueda seleccionado
    function resaltarResultadoBusqueda(resultadosBusqueda) {
        resultadosBusqueda.forEach((item, index) => {
            item.classList.toggle('active', index === resultadosBusquedaSeleccionado);
        });
        if (resultadosBusquedaSeleccionado >= 0 && resultadosBusquedaSeleccionado < resultadosBusqueda.length) {
            document.getElementById('buscarExamen').value = resultadosBusqueda[resultadosBusquedaSeleccionado].textContent;
        }
    }

    document.getElementById('guardar').addEventListener('click', function () {
        verCaja(); // Primero, verificar el estado de la caja
    });
    
    function verCaja() {
        $.ajax({
            url: 'ver_caja.php', // Ruta al script PHP que verifica el estado de la caja
            type: 'GET',
            success: function (response) {
                console.log('Respuesta del servidor:', response); // Añade este log para depuración
    
                try {
                    // Verificar si la respuesta ya es un objeto JSON
                    const data = typeof response === 'object' ? response : JSON.parse(response);
    
                    if (data.success) {
                        // Si hay una caja abierta, proceder con el guardado
                        guardarEnJSON();
                        enviarDatosAlServidor();
                        mostrarDatosJSON();
                        // Mostrar alerta de éxito
                        Swal.fire({
                            icon: 'success',
                            title: 'Datos guardados',
                            text: 'Los datos han sido guardados exitosamente.',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    } else {
                        // Mostrar alerta de error si no hay caja abierta
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'No se puede guardar. No hay ninguna caja abierta.',
                            showConfirmButton: true
                        });
                    }
                } catch (error) {
                    console.error('Error al procesar la respuesta de la caja:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Hubo un problema al procesar la respuesta del estado de la caja.',
                        showConfirmButton: true
                    });
                }
            },
            error: function (error) {
                console.error('Error al verificar el estado de la caja:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Hubo un problema al verificar el estado de la caja.',
                    showConfirmButton: true
                });
            }
        });
    }
    
    function enviarDatosAlServidor() {
        let jsonData = localStorage.getItem('jsonData');
        if (jsonData) {
            console.log('JSON a enviar:', JSON.parse(jsonData)); // Asegúrate de que el total esté presente aquí
    
            $.ajax({
                url: 'guarda.php', // Ruta al script PHP que procesa los datos
                type: 'POST',
                contentType: 'application/json', // Aseguramos que el tipo de contenido sea JSON
                data: jsonData, // Enviamos directamente el JSON
                success: function (response) {
                    try {
                        const data = JSON.parse(response); // Asegúrate de que la respuesta esté en formato JSON
    
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Datos guardados',
                                text: 'Los datos han sido guardados exitosamente.',
                                showCancelButton: true,
                                confirmButtonText: 'Imprimir comprobante y orden',
                                cancelButtonText: 'Cerrar'
                            }).then((resultConfirm) => {
                                if (resultConfirm.isConfirmed) {
                                    const idComprobante = data.id_comprobante;
                                    const tipoComprobante = data.tipo_comprobante; // Suponiendo que tipo_comprobante también se envía en la respuesta
                                    const idOrden = data.id_orden; // Capturar el id de la orden
    
                                    // Imprimir comprobante
                                    if (tipoComprobante == '1') {
                                        window.location.href = `boleta.php?idcomprobante=${idComprobante}`;
                                    }else if(tipoComprobante == '2'){
                                        window.location.href = `factura.php?idcomprobante=${idComprobante}`;
                                    }
    
                                    // Imprimir orden en una nueva ventana
                                    window.open(`orden.php?idorden=${idOrden}`, '_blank');
                                }
                            });
                            limpiarCamposYTabla(); // Limpiar campos y tabla después de guardar
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message
                            });
                        }
                    } catch (error) {
                        console.error('Error al procesar la respuesta del servidor:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un problema al procesar la respuesta del servidor.',
                            showConfirmButton: true
                        });
                    }
                },
                error: function (error) {
                    console.error('Error al guardar los datos en la base de datos:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Hubo un problema al guardar los datos en la base de datos.',
                        showConfirmButton: true
                    });
                }
            });
        } else {
            console.log("No hay datos en el JSON para enviar.");
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No hay datos para enviar.',
                showConfirmButton: true
            });
        }
    }

    // Función para mostrar datos JSON en la consola
    function mostrarDatosJSON() {
        let jsonData = localStorage.getItem('jsonData');
        if (jsonData) {
            console.log(JSON.parse(jsonData));
        } else {
            console.log("No hay datos en el JSON.");
        }
    }

     // Función para limpiar campos y tabla
     function limpiarCamposYTabla() {
        // Limpiar campos de texto
        document.getElementById('dni').value = '';
        document.getElementById('paciente').value = '';
        document.getElementById('tipocomprobante').value = '';
        document.getElementById('descuento').value = '';
        document.getElementById('idpaciente').value = '';
        document.getElementById('medico_resultado').value = '';
    
        // Limpiar select2
        $('#medico_resultado').val(null).trigger('change');
    
        // Limpiar radios
        const boletaRadio = document.getElementById('boletaRadio');
        const facturaRadio = document.getElementById('facturaRadio');
        if (boletaRadio) {
            boletaRadio.checked = false;
        }
        if (facturaRadio) {
            facturaRadio.checked = false;
        }
    
        // Limpiar select de tipo de pago
        const tipoPagoSelect = document.getElementById('tipo_pago');
        if (tipoPagoSelect) {
            tipoPagoSelect.value = '';
        }
    
        // Limpiar select de estado de pago
        const estadoPagoSelect = document.getElementById('estado_pago');
        if (estadoPagoSelect) {
            estadoPagoSelect.value = '';
        }
    
        // Limpiar la tabla de exámenes asignados
        document.getElementById('examenesAsignados').innerHTML = '';
    
        // Resetear los totales
        totalParcial = 0;
        document.getElementById('totalParcial').textContent = totalParcial.toFixed(2);
        document.getElementById('totalConDescuento').textContent = totalParcial.toFixed(2);
    }

    // Función para agregar exámenes o perfil a la tabla
    function agregarFila() {
    const dni = document.getElementById('dni').value;
    const paciente = document.getElementById('paciente').value;
    const tipoComprobante = document.getElementById('tipocomprobante').value;
    const descuento = document.getElementById('descuento').value;
    const idpaciente = document.getElementById('idpaciente').value;
    const idmedico = document.getElementById('medico_resultado').value;

    if (!dni || !paciente || !tipoComprobante) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Complete todos los campos requeridos: DNI, Paciente, Tipo de Comprobante.'
        });
        return;
    }

    if (!examenSeleccionado) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Seleccione un examen o perfil antes de agregar.'
        });
        return;
    }

    const fecha = document.getElementById('fecha').value;
    const muestraInput = `<input type="text" class="form-control muestra" placeholder="Muestra" oninput="buscarMuestra(this)">`;
    const idExamen = examenSeleccionado.id;
    const nombreExamen = examenSeleccionado.nombre;
    const precioExamen = document.getElementById('preciofinal').value;

    if (examenSeleccionado.tipo === 'perfil') {
        if (!perfilesAgregados.has(examenSeleccionado.id)) {
            perfilesAgregados.add(examenSeleccionado.id);
            totalParcial += parseFloat(examenSeleccionado.precio);
        }

        examenSeleccionado.examenes.forEach(examen => {
            const nuevaFila = `
                <tr data-perfil-id="${examenSeleccionado.id}" data-examen-id="${examen.id}">
                    <td>${dni}</td>
                    <td>${paciente}</td>
                    <td>${examenSeleccionado.nombre}: ${examen.nombre}</td>
                    <td>${fecha}</td>
                    <td>${muestraInput}</td>
                    <td>${examenSeleccionado.precio}</td>
                    <td><input type="hidden" class="form-control" placeholder="Descuento" value="${descuento}"></td>
                    <td><button type="button" class="btn btn-danger btn-sm eliminar">Eliminar</button></td>
                </tr>
            `;
            document.getElementById('examenesAsignados').insertAdjacentHTML('beforeend', nuevaFila);
        });
    } else {
        const nuevaFila = `
            <tr data-examen-id="${idExamen}">
                <td>${dni}</td>
                <td>${paciente}</td>
                <td>${nombreExamen}</td>
                <td>${fecha}</td>
                <td>${muestraInput}</td>
                <td>${precioExamen}</td>
                <td><input type="hidden" class="form-control" placeholder="Descuento" value="${descuento}"></td>
                <td><button type="button" class="btn btn-danger btn-sm eliminar">Eliminar</button></td>
            </tr>
        `;
        document.getElementById('examenesAsignados').insertAdjacentHTML('beforeend', nuevaFila);

        totalParcial += parseFloat(precioExamen);
    } 

    document.getElementById('totalParcial').textContent = totalParcial.toFixed(2);
    document.getElementById('totalConDescuento').textContent = totalParcial.toFixed(2);
    calcularDescuento();
    document.getElementById('buscarExamen').value = '';
    document.getElementById('precio').value = '';
    document.getElementById('preciofinal').value = '';
    examenSeleccionado = null;
    document.getElementById('buscarExamen').focus();
    }

    // Función para guardar en JSON
    function guardarEnJSON() {
        const filas = document.querySelectorAll('#examenesAsignados tr');
        let jsonData = [];
        
        filas.forEach(fila => {
            const dni = fila.querySelector('td:nth-child(1)').textContent;
            const paciente = fila.querySelector('td:nth-child(2)').textContent;
            const nombreExamen = fila.querySelector('td:nth-child(3)').textContent;
            const fecha = fila.querySelector('td:nth-child(4)').textContent;
            const muestraNombre = fila.querySelector('td:nth-child(5) input').value;
            const muestraId = fila.querySelector('td:nth-child(5) input').dataset.id || ""; // ID de la muestra
    
            const idpaciente = document.getElementById('idpaciente').value;
            const idmedico = document.getElementById('medico_resultado').value;
            const tipoComprobante = document.getElementById('tipocomprobante').value;
            const descuento = fila.querySelector('td:nth-child(7) input').value || 0; // Descuento específico del examen
            const descuentotot = document.getElementById('descuentotot').value;
            const tipo_pago = document.getElementById('tipo_pago').value;
            const estado_pago = document.getElementById('estado_pago').value;
            const totalConDescuento = document.getElementById('totalConDescuento').textContent; // Cambio aquí
            const totalParcial = document.getElementById('totalParcial').textContent; // Cambio aquí
    
            const perfilId = fila.dataset.perfilId || ""; // ID del perfil si existe
    
            const examen = {
                id: fila.dataset.examenId || "", // ID del examen
                nombre: nombreExamen,
                precio: fila.querySelector('td:nth-child(6)').textContent,
                descuento: descuento, // Descuento específico del examen
                perfilId: perfilId // ID del perfil
            };
    
            jsonData.push({
                dni: dni,
                paciente: paciente,
                idpaciente: idpaciente,
                idmedico: idmedico,
                tipoComprobante: tipoComprobante,
                descuentotot: descuentotot,
                tipo_pago: tipo_pago,
                estado_pago: estado_pago,
                totalConDescuento: totalConDescuento,
                totalParcial: totalParcial,
                examen: examen,
                fecha: fecha,
                muestras: [{ id: muestraId, nombre: muestraNombre }]
            });
        });
    
        localStorage.setItem('jsonData', JSON.stringify(jsonData));
    }

    // Event listener para el botón "Agregar"
    document.getElementById('agregar').addEventListener('click', function () {
        agregarFila();
    });

    // Manejar evento de click para eliminar filas
    document.getElementById('examenesAsignados').addEventListener('click', function (e) {
        if (e.target.classList.contains('eliminar')) {
            const fila = e.target.closest('tr');
            const precio = parseFloat(fila.querySelector('td:nth-child(6)').textContent);
            const perfilId = fila.dataset.perfilId;
    
            // Si la fila pertenece a un perfil
            if (perfilId) {
                // Verificar si hay más exámenes del perfil
                const exámenesDelPerfil = document.querySelectorAll(`tr[data-perfil-id="${perfilId}"]`);
                if (exámenesDelPerfil.length === 1) {
                    // Si es la última fila del perfil, restar el precio del perfil del total
                    totalParcial -= parseFloat(fila.querySelector('td:nth-child(6)').textContent);
                    perfilesAgregados.delete(perfilId);
                }
            } else {
                // Si la fila no pertenece a un perfil, restar el precio del total
                totalParcial -= precio;
            }
    
            document.getElementById('totalParcial').textContent = totalParcial.toFixed(2);
            document.getElementById('totalConDescuento').textContent = totalParcial.toFixed(2);
            calcularDescuento();
            fila.remove();
        }
    });

    // Función para buscar muestras
    window.buscarMuestra = function (input) {
        const query = input.value;
        if (query.length > 1) {
            $.ajax({
                url: 'busmuestra.php',
                type: 'GET',
                data: { query: query },
                success: function (response) {
                    const resultados = JSON.parse(response).muestras;
                    mostrarResultadosMuestra(resultados, input);
                },
                error: function () {
                    console.error('Error al buscar muestras');
                }
            });
        } else {
            limpiarResultadosMuestra(input);
        }
    };
    
    // Función para mostrar resultados de búsqueda de muestras
    function mostrarResultadosMuestra(resultados, input) {
        limpiarResultadosMuestra(input); // Limpiar resultados anteriores
        const contenedorResultados = document.createElement('div');
        contenedorResultados.className = 'list-group';
        contenedorResultados.style.position = 'absolute';
        contenedorResultados.style.zIndex = '1000';
        contenedorResultados.style.width = input.offsetWidth + 'px';
    
        resultados.forEach(muestra => {
            const item = document.createElement('a');
            item.href = '#';
            item.className = 'list-group-item list-group-item-action';
            item.textContent = muestra.nombre;
            item.dataset.id = muestra.id;
            item.addEventListener('click', function (e) {
                e.preventDefault();
                input.value = muestra.nombre;
                input.dataset.id = muestra.id;
                limpiarResultadosMuestra(input);
            });
            contenedorResultados.appendChild(item);
        });
    
        input.parentNode.appendChild(contenedorResultados);
    
        // Añadir manejador de eventos para teclas
        input.addEventListener('keydown', function (e) {
            manejarTeclas(e, input, contenedorResultados);
        });
    }
    
    // Función para manejar la navegación y selección con teclas
    function manejarTeclas(e, input, contenedorResultados) {
        const items = contenedorResultados.querySelectorAll('.list-group-item');
        let index = Array.prototype.indexOf.call(items, contenedorResultados.querySelector('.active'));
    
        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                if (index < items.length - 1) {
                    if (index >= 0) items[index].classList.remove('active');
                    items[++index].classList.add('active');
                } else if (index === -1) {
                    items[0].classList.add('active');
                }
                break;
            case 'ArrowUp':
                e.preventDefault();
                if (index > 0) {
                    items[index].classList.remove('active');
                    items[--index].classList.add('active');
                }
                break;
            case 'Enter':
                e.preventDefault();
                if (index >= 0) {
                    items[index].click();
                }
                break;
        }
    }
    
    // Función para limpiar resultados de búsqueda de muestras
    function limpiarResultadosMuestra(input) {
        const contenedor = input.parentNode.querySelector('.list-group');
        if (contenedor) {
            contenedor.remove();
        }
    }
    
    // Añadir un evento de entrada para buscar muestras mientras se escribe
    document.querySelector('#inputMuestra').addEventListener('input', function () {
        buscarMuestra(this);
    });
    
    // Añadir un evento para cerrar los resultados cuando se hace clic fuera del input o lista
    document.addEventListener('click', function (e) {
        const input = document.querySelector('#inputMuestra');
        const contenedorResultados = input.parentNode.querySelector('.list-group');
        if (contenedorResultados && !contenedorResultados.contains(e.target) && e.target !== input) {
            limpiarResultadosMuestra(input);
        }
    });

    // Event listener para manejar el cálculo de descuento
    document.getElementById('descuento').addEventListener('input', calcularDescuento);
});