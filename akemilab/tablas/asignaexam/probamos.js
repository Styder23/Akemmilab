document.getElementById('perfil').addEventListener('change', function() {
    const perfilId = this.value;
    if (perfilId) {
        fetch(`./obt_examenes.php?perfil_id=${perfilId}`)
            .then(response => response.json())
            .then(data => {
                console.log('Exámenes:', data);
                const examenesContainer = document.getElementById('examenesContainer');
                examenesContainer.innerHTML = '';

                if (data.length > 0) {
                    const precioperfil = parseFloat(data[0].precioperfil);
                    document.getElementById('precio').value = precioperfil.toFixed(2);
                    document.getElementById('preciofinal').value = precioperfil.toFixed(2);
                }

                if (data.length === 0) {
                    examenesContainer.innerHTML = '<p>No hay exámenes disponibles para este perfil.</p>';
                } else {
                    data.forEach(examen => {
                        const div = document.createElement('div');
                        div.classList.add('form-group', 'row');
                        div.innerHTML = `
                            <div class="col-md-6">
                                <label>${examen.tipoexam}</label>
                                <input type="hidden" class="examen-id" value="${examen.idtipoexamen}">
                            </div>
                            <div class="col-md-6">
                                <select class="form-control muestras-select">
                                    <option value="">--Seleccione Muestra--</option>
                                </select>
                            </div>
                        `;
                        examenesContainer.appendChild(div);

                        fetch('./obt_muestras.php')
                            .then(response => response.json())
                            .then(muestras => {
                                console.log('Muestras:', muestras);
                                const select = div.querySelector('.muestras-select');
                                muestras.forEach(muestra => {
                                    const option = document.createElement('option');
                                    option.value = muestra.idmuestra;
                                    option.textContent = muestra.muestra;
                                    select.appendChild(option);
                                });

                                select.addEventListener('change', function() {
                                    const muestraId = this.value;
                                    const examenId = div.querySelector('.examen-id').value;
                                    const examenNombre = div.querySelector('label').textContent;
                                    const muestraNombre = this.options[this.selectedIndex].textContent;

                                    // Dentro de select.addEventListener('change', function() {...})
                                if (muestraId) {
                                    const tabla = document.getElementById('tablaDinamica').querySelector('tbody');
                                    const row = tabla.insertRow();
                                    const cell1 = row.insertCell(0);
                                    const cell2 = row.insertCell(1);
                                    const cell3 = row.insertCell(2);
                                    cell1.textContent = examenNombre;
                                    cell1.setAttribute('data-examen-id', examenId); // Añadir el atributo data-examen-id
                                    cell2.textContent = muestraNombre;
                                    cell2.setAttribute('data-muestra-id', muestraId); // Añadir el atributo data-muestra-id

                                    const eliminarBtn = document.createElement('button');
                                    eliminarBtn.classList.add('btn', 'btn-danger');
                                    eliminarBtn.textContent = 'Eliminar';
                                    eliminarBtn.addEventListener('click', function() {
                                    tabla.deleteRow(row.rowIndex - 1);
                                });
                                    cell3.appendChild(eliminarBtn);
                                }
                                });
                            })
                            .catch(error => {
                                console.error('Error al obtener muestras:', error);
                                alert('Error al obtener muestras.');
                            });
                    });
                }
            })
            .catch(error => {
                console.error('Error al obtener exámenes:', error);
                alert('Error al obtener exámenes.');
            });
    } else {
        document.getElementById('examenesContainer').innerHTML = '<p>Seleccione un perfil para ver los exámenes.</p>';
    }
});

// Función para agregar exámenes y muestras al textarea y tabla dinámica
document.getElementById('btnAgregarExamen').addEventListener('click', function() {
    const tabla = document.getElementById('tablaDinamica').querySelector('tbody');
    const filas = tabla.querySelectorAll('tr');
    let examenesMuestrasText = '';

    filas.forEach((fila) => {
        const examenNombre = fila.cells[0].textContent;
        const muestraNombre = fila.cells[1].textContent;
        const examenId = fila.cells[0].getAttribute('data-examen-id');
        const muestraId = fila.cells[1].getAttribute('data-muestra-id');
        examenesMuestrasText += `${examenNombre} (ID: ${examenId}) - ${muestraNombre} (ID: ${muestraId})\n`;
    });

    document.getElementById('examenesMuestras').value = examenesMuestrasText;

    $('#modalExamenes').modal('hide');
});