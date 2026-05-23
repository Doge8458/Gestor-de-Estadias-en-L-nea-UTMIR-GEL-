document.addEventListener('DOMContentLoaded', () => {
    
    // --- TEMAS ---
    const toggleSwitches = document.querySelectorAll('.theme-checkbox');
    const currentTheme = localStorage.getItem('theme');
    if (currentTheme) {
        document.documentElement.setAttribute('data-theme', currentTheme);
        if (currentTheme === 'light') toggleSwitches.forEach(sw => sw.checked = true);
    }
    toggleSwitches.forEach(sw => sw.addEventListener('change', e => {
        const isChecked = e.target.checked;
        toggleSwitches.forEach(s => s.checked = isChecked);
        document.documentElement.setAttribute('data-theme', isChecked ? 'light' : 'dark');
        localStorage.setItem('theme', isChecked ? 'light' : 'dark');
    }));

    // --- MANEJO DE MODALES ---
    let idEliminarGlobal = null;
    
    window.abrirModalDelete = function(id, matricula) {
        idEliminarGlobal = id;
        document.getElementById('motivoEliminacion').value = '';
        document.getElementById('motivoOtro').value = '';
        document.getElementById('comentarioEliminacion').value = '';
        document.getElementById('deleteAdminStatus').innerText = '';
        document.getElementById('grupoMotivoOtro').classList.add('is-hidden');
        document.getElementById('modalMatriculaTexto').innerText = matricula;
        document.getElementById('modalConfirmacion').classList.add('active');
    };

    window.cerrarModal = function(idModal) {
        document.getElementById(idModal).classList.remove('active');
        if (idModal === 'modalConfirmacion') idEliminarGlobal = null;
    };

    document.querySelectorAll('[data-modal-close]').forEach(button => {
        button.addEventListener('click', () => cerrarModal(button.dataset.modalClose));
    });

    document.getElementById('btnAbrirModalCalendario')?.addEventListener('click', () => {
        document.getElementById('modalCalendario').classList.add('active');
    });

    document.getElementById('btnModificarNotificaciones')?.addEventListener('click', () => {
        document.getElementById('modalNotificaciones').classList.add('active');
    });

    // --- LÓGICA DE ELIMINACIÓN ---
    document.getElementById('motivoEliminacion')?.addEventListener('change', event => {
        const esOtro = event.target.value === 'otro';
        document.getElementById('grupoMotivoOtro').classList.toggle('is-hidden', !esOtro);
        document.getElementById('deleteAdminStatus').innerText = '';
    });

    document.getElementById('btnConfirmarEliminacion')?.addEventListener('click', async () => {
        if (!idEliminarGlobal) return;
        const motivo = document.getElementById('motivoEliminacion').value;
        const motivoOtro = document.getElementById('motivoOtro').value.trim();
        const comentario = document.getElementById('comentarioEliminacion').value.trim();
        const status = document.getElementById('deleteAdminStatus');

        if (!motivo) { status.innerText = 'Selecciona el motivo de eliminación.'; return; }
        if (motivo === 'otro' && !motivoOtro) { status.innerText = 'Por favor, especifica el motivo.'; return; }

        const formData = new FormData();
        formData.append('id', idEliminarGlobal);
        formData.append('motivo_select', motivo);
        formData.append('motivo_otro', motivoOtro);
        formData.append('comentario', comentario);

        status.innerText = 'Eliminando y enviando notificación...';

        try {
            const response = await fetch('../api/delete_entrega.php', { method: 'POST', body: formData });
            const data = await response.json();
            if (!response.ok || data.status !== 'success') {
                status.innerText = data.message || 'Error al eliminar.'; return;
            }
            window.location.reload();
        } catch (error) {
            status.innerText = 'Error de conexión.';
        }
    });

    // --- CENTRO DE MENSAJES ---
    document.getElementById('formEnviarAviso')?.addEventListener('submit', async event => {
        event.preventDefault();
        const status = document.getElementById('avisoAdminStatus');
        status.innerText = 'Enviando mensaje...';
        try {
            const response = await fetch('../api/enviar_aviso.php', { method: 'POST', body: new FormData(event.currentTarget) });
            const data = await response.json();
            if (data.status === 'success') {
                status.innerText = data.message;
                event.currentTarget.reset();
            } else {
                status.innerText = data.message;
            }
        } catch (error) { status.innerText = 'Error de conexión.'; }
    });

    // --- FORMULARIO NOTIFICACIONES ---
    document.getElementById('formNotificaciones')?.addEventListener('submit', async event => {
        event.preventDefault();
        const status = document.getElementById('statusNotif');
        status.innerText = 'Guardando configuración...';
        try {
            const response = await fetch('../api/guardar_periodo_notif.php', { method: 'POST', body: new FormData(event.currentTarget) });
            const data = await response.json();
            if (data.status === 'success') {
                status.innerText = data.message;
                setTimeout(() => window.location.reload(), 1500);
            } else { status.innerText = data.message; }
        } catch (error) { status.innerText = 'Error de conexión.'; }
    });

    // --- DETALLES DE TABLA Y BOTONES DE ELIMINAR ---
    document.querySelectorAll('.main-row[data-detail-target]').forEach(row => {
        row.addEventListener('click', () => {
            const detailsPanel = document.getElementById(row.dataset.detailTarget);
            if (detailsPanel.style.display === "table-row") {
                detailsPanel.style.display = "none"; row.classList.remove("active");
            } else {
                document.querySelectorAll('.detail-row').forEach(el => el.style.display = 'none');
                document.querySelectorAll('.main-row').forEach(el => el.classList.remove("active"));
                detailsPanel.style.display = "table-row"; row.classList.add("active");
            }
        });
    });

    document.querySelectorAll('.detail-action').forEach(action => {
        action.addEventListener('click', event => event.stopPropagation());
    });

    document.querySelectorAll('.btn-delete-entrega').forEach(button => {
        button.addEventListener('click', event => {
            event.stopPropagation();
            abrirModalDelete(button.dataset.idEntrega, button.dataset.matricula);
        });
    });

    // --- CALENDARIOS Y CONTADOR ---
    try {
        const dataElement = document.getElementById('admin-panel-data');
        if (dataElement) {
            const adminData = JSON.parse(dataElement.textContent);
            
            flatpickr("#rango_fechas", { mode: "range", minDate: "today", showMonths: 2, locale: "es", defaultDate: [adminData.fechaInicio, adminData.fechaFin] });
            flatpickr("#rango_fechas_notif", { mode: "range", minDate: "today", showMonths: 2, locale: "es", defaultDate: [adminData.notifInicio, adminData.notifFin] });

            if (adminData.fechaFin && adminData.fechaInicio) {
                flatpickr("#calendario_admin_preview", { mode: "range", inline: true, showMonths: 1, locale: "es", defaultDate: [adminData.fechaInicio, adminData.fechaFin] });
                
                const countDownDate = new Date(adminData.fechaFin.replace(/-/g, "/")).getTime();
                const startDate = new Date(adminData.fechaInicio.replace(/-/g, "/")).getTime();
                
                setInterval(() => {
                    const now = new Date().getTime();
                    if (now >= startDate && now <= countDownDate) {
                        const dist = countDownDate - now;
                        document.getElementById("cd-dias").innerText = Math.floor(dist / (1000 * 60 * 60 * 24)).toString().padStart(2, '0');
                        document.getElementById("cd-horas").innerText = Math.floor((dist % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)).toString().padStart(2, '0');
                        document.getElementById("cd-mins").innerText = Math.floor((dist % (1000 * 60 * 60)) / (1000 * 60)).toString().padStart(2, '0');
                        document.getElementById("cd-segs").innerText = Math.floor((dist % (1000 * 60)) / 1000).toString().padStart(2, '0');
                    }
                }, 1000);
            }
        }
    } catch (e) { console.warn("Error inicializando calendarios", e); }

    // --- LÓGICA DE EXCEL ---
    let alumnosCargados = [];
    document.getElementById('archivo_excel')?.addEventListener('change', e => {
        document.getElementById('fileNameDisplay').innerText = e.target.files[0] ? e.target.files[0].name : 'Haz clic o arrastra tu archivo';
    });

    document.getElementById('formCargaExcel')?.addEventListener('submit', async event => {
        event.preventDefault();
        const status = document.getElementById('excelStatusOutput');
        status.innerText = 'Leyendo archivo Excel...';
        try {
            const response = await fetch('../api/leer_excel.php', { method: 'POST', body: new FormData(event.currentTarget) });
            const result = await response.json();
            if (result.status === 'success' && result.data.length > 0) {
                alumnosCargados = result.data;
                const tbody = document.getElementById('cuerpoTabla');
                tbody.innerHTML = '';
                alumnosCargados.forEach((al, i) => {
                    tbody.innerHTML += `<tr><td><input type="checkbox" class="chk-alumno" data-index="${i}" checked></td>
                    <td><strong>${al.matricula}</strong></td><td>${al.nombre}</td><td>${al.programa}</td><td>${al.correo}</td><td>Pendiente</td></tr>`;
                });
                status.innerText = '';
                document.getElementById('panelResultados').style.display = 'block';
            } else { status.innerText = 'Error: ' + result.message; }
        } catch (error) { status.innerText = 'Error de conexión.'; }
    });

    document.getElementById('btnHabilitarSeleccionados')?.addEventListener('click', async () => {
        const checkboxes = document.querySelectorAll('.chk-alumno:checked');
        if (checkboxes.length === 0) return alert("Selecciona alumnos.");
        document.getElementById('btnHabilitarSeleccionados').disabled = true;
        const seleccionados = Array.from(checkboxes).map(chk => alumnosCargados[chk.dataset.index]);
        try {
            const response = await fetch('../api/guardar_alumnos.php', { method: 'POST', body: JSON.stringify({ alumnos: seleccionados }) });
            const result = await response.json();
            alert(result.message);
            window.location.reload(); 
        } catch (error) { alert("Error al guardar."); document.getElementById('btnHabilitarSeleccionados').disabled = false; }
    });
});