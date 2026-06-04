document.addEventListener('DOMContentLoaded', () => {
    const CAREER_PAGE_SIZE = 10;
    const sleepFrame = () => new Promise(resolve => requestAnimationFrame(resolve));

    function setStatus(element, message, type = '') {
        if (!element) return;
        element.textContent = message || '';
        element.classList.toggle('admin-form-status-success', type === 'success');
        element.classList.toggle('admin-form-status-error', type === 'error');
    }

    async function readJsonResponse(response) {
        const text = await response.text();
        try {
            return JSON.parse(text);
        } catch (error) {
            const start = text.indexOf('{');
            const end = text.lastIndexOf('}');
            if (start !== -1 && end !== -1 && end > start) {
                return JSON.parse(text.slice(start, end + 1));
            }
            throw error;
        }
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, char => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        }[char]));
    }
    
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

    // --- MENÚ MÓVIL ---
    const btnToggleSidebar = document.getElementById('btnToggleSidebar');
    const mainSidebar = document.getElementById('mainSidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    function toggleSidebar() {
        const isActive = mainSidebar.classList.toggle('active');
        sidebarOverlay.classList.toggle('active');
        
        if(btnToggleSidebar) {
            btnToggleSidebar.innerHTML = isActive 
                ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>'
                : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>';
        }
    }

    btnToggleSidebar?.addEventListener('click', toggleSidebar);
    sidebarOverlay?.addEventListener('click', toggleSidebar);

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

    document.getElementById('btnAbrirVideoAdmin')?.addEventListener('click', () => {
        document.getElementById('modalVideoAdmin').classList.add('active');
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
        setStatus(status, 'Enviando mensaje...');
        try {
            const response = await fetch('../api/enviar_aviso.php', { method: 'POST', body: new FormData(event.currentTarget) });
            const data = await readJsonResponse(response);
            if (response.ok && data.status === 'success') {
                setStatus(status, data.message || 'Mensaje enviado con exito.', 'success');
                event.currentTarget.reset();
            } else {
                setStatus(status, data.message || 'No se pudo enviar el mensaje.', 'error');
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

    // --- CARPETAS DE CARRERA Y PAGINACION ---
    const careerState = {};

    function closeCareerDetails(careerId) {
        document.querySelectorAll(`.detail-row[data-career-id="${careerId}"]`).forEach(row => {
            row.style.display = 'none';
        });
        document.querySelectorAll(`.main-row[data-career-id="${careerId}"]`).forEach(row => {
            row.classList.remove('active');
        });
    }

    function renderCareerPage(careerId) {
        const state = careerState[careerId];
        if (!state) return;
        const start = (state.page - 1) * CAREER_PAGE_SIZE;
        const end = start + CAREER_PAGE_SIZE;

        document.querySelectorAll(`.main-row[data-career-id="${careerId}"]`).forEach(row => {
            const index = Number(row.dataset.studentIndex || 0);
            row.style.display = state.open && index >= start && index < end ? 'table-row' : 'none';
        });

        closeCareerDetails(careerId);

        const paginationRow = document.querySelector(`[data-career-pagination="${careerId}"]`);
        if (paginationRow) {
            paginationRow.style.display = state.open && state.pages > 1 ? 'table-row' : 'none';
            const label = document.querySelector(`[data-career-page-label="${careerId}"]`);
            const prev = document.querySelector(`[data-career-prev="${careerId}"]`);
            const next = document.querySelector(`[data-career-next="${careerId}"]`);
            if (label) label.textContent = `Pagina ${state.page} de ${state.pages}`;
            if (prev) prev.disabled = state.page <= 1;
            if (next) next.disabled = state.page >= state.pages;
        }
    }

    document.querySelectorAll('[data-career-toggle]').forEach(row => {
        const careerId = row.dataset.careerToggle;
        const total = Number(row.dataset.totalStudents || 0);
        careerState[careerId] = { open: false, page: 1, pages: Math.max(1, Math.ceil(total / CAREER_PAGE_SIZE)) };
        row.addEventListener('click', () => {
            careerState[careerId].open = !careerState[careerId].open;
            row.classList.toggle('is-open', careerState[careerId].open);
            row.setAttribute('aria-expanded', careerState[careerId].open ? 'true' : 'false');
            renderCareerPage(careerId);
        });
        renderCareerPage(careerId);
    });

    document.querySelectorAll('[data-career-prev], [data-career-next]').forEach(button => {
        button.addEventListener('click', event => {
            event.stopPropagation();
            const careerId = button.dataset.careerPrev || button.dataset.careerNext;
            const state = careerState[careerId];
            if (!state) return;
            state.page += button.dataset.careerPrev ? -1 : 1;
            state.page = Math.min(Math.max(state.page, 1), state.pages);
            renderCareerPage(careerId);
        });
    });

    // --- DETALLES DE TABLA ---
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
    // --- CARGA FLUIDA DE EXCEL ---
    const modalCargaExcel = document.getElementById('modalCargaExcel');
    const excelProgressFill = document.getElementById('excelProgressFill');
    const excelProgressText = document.getElementById('excelProgressText');
    const excelLoadingTitle = document.getElementById('excelLoadingTitle');
    const excelLoadingText = document.getElementById('excelLoadingText');

    function setExcelProgress(percent, title, message) {
        const safePercent = Math.max(0, Math.min(100, Math.round(percent)));
        if (excelProgressFill) excelProgressFill.style.width = `${safePercent}%`;
        if (excelProgressText) excelProgressText.textContent = `${safePercent}%`;
        if (excelLoadingTitle && title) excelLoadingTitle.textContent = title;
        if (excelLoadingText && message) excelLoadingText.textContent = message;
    }

    function toggleExcelLoading(show, title = 'Procesando base de datos', message = 'Preparando lectura del archivo...') {
        if (!modalCargaExcel) return;
        modalCargaExcel.classList.toggle('active', show);
        if (show) setExcelProgress(5, title, message);
    }

    async function renderExcelStudents(alumnos) {
        const tbody = document.getElementById('cuerpoTabla');
        if (!tbody) return;
        tbody.innerHTML = '';
        const chunkSize = 35;
        const total = alumnos.length;

        for (let start = 0; start < total; start += chunkSize) {
            const end = Math.min(start + chunkSize, total);
            const rows = [];
            for (let i = start; i < end; i++) {
                const al = alumnos[i];
                const nombre = al.nombre || al.nombre_completo || '';
                const programa = al.programa || al.programa_educativo || '';
                const correo = al.correo || al.correo_institucional || '';
                const clave = al.clave_temporal ? '<br><small>Clave temporal: matricula</small>' : '';
                rows.push(`<tr><td><input type="checkbox" class="chk-alumno" data-index="${i}" checked></td>
                    <td><strong>${escapeHtml(al.matricula)}</strong>${clave}</td>
                    <td>${escapeHtml(nombre)}</td>
                    <td>${escapeHtml(programa)}<br><small>${escapeHtml(al.cuatrimestre || '')}</small></td>
                    <td>${escapeHtml(correo)}</td>
                    <td>Listo para habilitar</td></tr>`);
            }
            tbody.insertAdjacentHTML('beforeend', rows.join(''));
            setExcelProgress(45 + ((end / Math.max(total, 1)) * 50), 'Preparando alumnos', `${end} de ${total} registros listos para revisar.`);
            await sleepFrame();
        }
    }

    document.getElementById('chkTodos')?.addEventListener('change', event => {
        document.querySelectorAll('.chk-alumno').forEach(checkbox => {
            checkbox.checked = event.target.checked;
        });
    });

    let alumnosCargados = [];
    document.getElementById('formCargaExcel')?.addEventListener('submit', async event => {
        event.preventDefault();
        event.stopImmediatePropagation();
        const status = document.getElementById('excelStatusOutput');
        setStatus(status, 'Leyendo archivo Excel...');
        toggleExcelLoading(true, 'Leyendo base de datos', 'Esto puede tardar unos segundos si el Excel es grande.');
        try {
            const response = await fetch('../api/leer_excel.php', { method: 'POST', body: new FormData(event.currentTarget) });
            setExcelProgress(38, 'Analizando registros', 'Validando columnas, carreras, matriculas y CURP.');
            const result = await readJsonResponse(response);

            if (response.ok && result.status === 'success' && Array.isArray(result.data) && result.data.length > 0) {
                alumnosCargados = result.data;
                document.getElementById('panelResultados').style.display = 'block';
                await renderExcelStudents(alumnosCargados);
                setExcelProgress(100, 'Carga lista', 'La base ya esta disponible para habilitar alumnos.');
                setStatus(status, result.message || 'Alumnos encontrados.', 'success');
                setTimeout(() => toggleExcelLoading(false), 650);
            } else {
                setStatus(status, `Error: ${result.message || 'No se encontraron alumnos.'}`, 'error');
                toggleExcelLoading(false);
            }
        } catch (error) {
            setStatus(status, 'Error de conexion o respuesta invalida al leer el Excel.', 'error');
            toggleExcelLoading(false);
        }
    });

    document.getElementById('btnHabilitarSeleccionados')?.addEventListener('click', async event => {
        event.preventDefault();
        event.stopImmediatePropagation();
        const button = document.getElementById('btnHabilitarSeleccionados');
        const checkboxes = document.querySelectorAll('.chk-alumno:checked');
        if (checkboxes.length === 0) return alert("Selecciona alumnos.");
        button.disabled = true;
        const seleccionados = Array.from(checkboxes).map(chk => alumnosCargados[chk.dataset.index]);
        toggleExcelLoading(true, 'Habilitando alumnos', `Guardando ${seleccionados.length} registro(s) en la plataforma.`);
        try {
            const response = await fetch('../api/guardar_alumnos.php', { method: 'POST', body: JSON.stringify({ alumnos: seleccionados }) });
            const result = await readJsonResponse(response);
            setExcelProgress(100, 'Alumnos habilitados', result.message || 'Registros guardados.');
            alert(result.message || 'Alumnos habilitados.');
            window.location.reload();
        } catch (error) {
            alert("Error al guardar.");
            toggleExcelLoading(false);
            button.disabled = false;
        }
    });

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
                    const nombre = al.nombre || al.nombre_completo || '';
                    const programa = al.programa || al.programa_educativo || '';
                    const correo = al.correo || al.correo_institucional || '';
                    const clave = al.clave_temporal ? '<br><small>Clave temporal: matrícula</small>' : '';
                    tbody.innerHTML += `<tr><td><input type="checkbox" class="chk-alumno" data-index="${i}" checked></td>
                    <td><strong>${al.matricula}</strong>${clave}</td><td>${nombre}</td><td>${programa}<br><small>${al.cuatrimestre || ''}</small></td><td>${correo}</td><td>Listo para habilitar</td></tr>`;
                });
                status.innerText = result.message || '';
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
