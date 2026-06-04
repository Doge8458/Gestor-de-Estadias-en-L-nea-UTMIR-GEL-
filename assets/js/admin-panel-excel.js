// Archivo: assets/js/admin-panel-excel.js

document.addEventListener('DOMContentLoaded', () => {
    const formExcel = document.getElementById('formCargaExcel');
    const inputExcel = document.getElementById('archivo_excel');
    const displayNombre = document.getElementById('fileNameDisplay');
    const cuerpoTabla = document.getElementById('cuerpoTabla');
    const statusOutput = document.getElementById('excelStatusOutput');
    const btnHabilitar = document.getElementById('btnHabilitarSeleccionados');
    const chkTodos = document.getElementById('chkTodos');
    
    // Cambiar nombre del archivo en la UI al seleccionarlo
    inputExcel.addEventListener('change', function(e) {
        if(this.files && this.files.length > 0) {
            displayNombre.textContent = this.files[0].name;
            displayNombre.style.color = 'var(--utmir-naranja)';
        }
    });

    // Enviar Excel para lectura
    formExcel.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        if(!inputExcel.files.length) return;

        const formData = new FormData();
        formData.append('archivo_excel', inputExcel.files[0]);

        statusOutput.style.color = '#3498db';
        statusOutput.textContent = 'Analizando archivo Excel, por favor espera...';

        try {
            const response = await fetch('../api/leer_excel.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if(result.success) {
                statusOutput.style.color = '#00a859';
                statusOutput.textContent = `${result.data.length} alumnos encontrados.`;
                renderizarTabla(result.data);
            } else {
                statusOutput.style.color = '#e74c3c';
                statusOutput.textContent = result.message;
            }
        } catch (error) {
            statusOutput.style.color = '#e74c3c';
            statusOutput.textContent = 'Error de conexión con el servidor.';
        }
    });

    // Renderizar datos en la tabla
    function renderizarTabla(alumnos) {
        cuerpoTabla.innerHTML = '';
        alumnos.forEach((alumno, index) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="txt-cell"><input type="checkbox" class="chk-alumno" data-alumno='${JSON.stringify(alumno)}' checked></td>
                <td class="txt-cell"><strong>${alumno.matricula}</strong></td>
                <td class="txt-cell">${alumno.nombre_completo}</td>
                <td class="txt-cell">${alumno.programa_educativo} <br> <small>${alumno.cuatrimestre}</small></td>
                <td class="txt-cell">${alumno.correo_institucional}</td>
                <td class="txt-cell"><span class="estado-badge estado-pendiente">Listo para habilitar</span></td>
            `;
            cuerpoTabla.appendChild(tr);
        });
    }

    // Seleccionar/Deseleccionar todos
    if(chkTodos) {
        chkTodos.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.chk-alumno');
            checkboxes.forEach(chk => chk.checked = this.checked);
        });
    }

    // Habilitar seleccionados y enviarlos a la base de datos
    if(btnHabilitar) {
        btnHabilitar.addEventListener('click', async () => {
            const checkboxes = document.querySelectorAll('.chk-alumno:checked');
            if(checkboxes.length === 0) {
                alert('Selecciona al menos un alumno para habilitar.');
                return;
            }

            const alumnosSeleccionados = Array.from(checkboxes).map(chk => JSON.parse(chk.getAttribute('data-alumno')));

            btnHabilitar.textContent = 'Habilitando...';
            btnHabilitar.disabled = true;

            try {
                const response = await fetch('../api/guardar_alumnos.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(alumnosSeleccionados)
                });
                const result = await response.json();

                if(result.success) {
                    alert(result.message);
                    // Limpiar interfaz
                    cuerpoTabla.innerHTML = '';
                    inputExcel.value = '';
                    displayNombre.textContent = 'Haz clic o arrastra tu archivo de Excel (.xlsx) aquí';
                    displayNombre.style.color = '';
                    statusOutput.textContent = '';
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Ocurrió un error al intentar guardar en la base de datos.');
            } finally {
                btnHabilitar.textContent = 'Habilitar Alumnos Seleccionados';
                btnHabilitar.disabled = false;
            }
        });
    }
}); 