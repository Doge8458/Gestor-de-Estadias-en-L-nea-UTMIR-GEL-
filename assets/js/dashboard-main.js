const toggleSwitches = document.querySelectorAll('.theme-checkbox');
const currentTheme = localStorage.getItem('theme');
const dashboardDataElement = document.getElementById('dashboard-data');
const dashboardData = dashboardDataElement ? JSON.parse(dashboardDataElement.textContent) : {};

if (currentTheme) {
    document.documentElement.setAttribute('data-theme', currentTheme);
    if (currentTheme === 'light') {
        toggleSwitches.forEach(sw => sw.checked = true);
    }
}

function switchTheme(e) {
    const isChecked = e.target.checked;
    toggleSwitches.forEach(sw => sw.checked = isChecked);

    if (isChecked) {
        document.documentElement.setAttribute('data-theme', 'light');
        localStorage.setItem('theme', 'light');
    } else {
        document.documentElement.setAttribute('data-theme', 'dark');
        localStorage.setItem('theme', 'dark');
    }
}

toggleSwitches.forEach(sw => sw.addEventListener('change', switchTheme, false));

function actualizarNombreArchivo(input) {
    const display = document.getElementById('fileNameDisplay');
    const dropArea = document.getElementById('dropArea');
    const icon = document.getElementById('uploadIcon');

    if (input.files && input.files[0]) {
        display.innerHTML = `<span class="selected-file-name">${input.files[0].name}</span>`;
        dropArea.style.borderColor = 'var(--utmir-verde)';
        icon.innerHTML = `<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><polyline points="9 15 12 18 15 15"></polyline><line x1="12" y1="18" x2="12" y2="12"></line>`;
        icon.style.stroke = 'var(--utmir-verde)';
    } else {
        display.innerHTML = 'Haga clic o arrastre el archivo aquí';
        dropArea.style.borderColor = 'rgba(255,255,255,0.2)';
        icon.innerHTML = `<path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline>`;
        icon.style.stroke = 'var(--texto-mutado)';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('currentYear').textContent = new Date().getFullYear();

    const data = dashboardData;
    const fechaFinStr = data.fechaFin || '';
    const fechaInicioStr = data.fechaInicio || '';

    if (fechaFinStr !== '' && fechaInicioStr !== '') {
        flatpickr('#calendario_alumno', {
            mode: 'range',
            inline: true,
            showMonths: 1,
            locale: 'es',
            defaultDate: [fechaInicioStr, fechaFinStr]
        });

        const countDownDate = new Date(fechaFinStr.replace(/-/g, '/')).getTime();
        const startDate = new Date(fechaInicioStr.replace(/-/g, '/')).getTime();

        const x = setInterval(function () {
            const now = new Date().getTime();

            if (now < startDate) {
                document.getElementById('cd-dias').innerText = '--';
                document.getElementById('cd-horas').innerText = '--';
                document.getElementById('cd-mins').innerText = '--';
                document.getElementById('cd-segs').innerText = '--';
            } else {
                const distance = countDownDate - now;

                if (distance < 0) {
                    clearInterval(x);
                    document.getElementById('cd-dias').innerText = '00';
                    document.getElementById('cd-horas').innerText = '00';
                    document.getElementById('cd-mins').innerText = '00';
                    document.getElementById('cd-segs').innerText = '00';
                } else {
                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    document.getElementById('cd-dias').innerText = days.toString().padStart(2, '0');
                    document.getElementById('cd-horas').innerText = hours.toString().padStart(2, '0');
                    document.getElementById('cd-mins').innerText = minutes.toString().padStart(2, '0');
                    document.getElementById('cd-segs').innerText = seconds.toString().padStart(2, '0');
                }
            }
        }, 1000);
    }

    if (data.canUpload) {
        configurarFormularioSubida();
    }

    const fileInput = document.getElementById('archivo_pdf');
    if (fileInput) {
        fileInput.addEventListener('change', () => actualizarNombreArchivo(fileInput));
    }

    const btnMessageOk = document.getElementById('btnMessageOk');
    if (btnMessageOk && typeof cerrarMensajeYRecargar === 'function') {
        btnMessageOk.addEventListener('click', cerrarMensajeYRecargar);
    }

    configurarNotificaciones();
});

function configurarNotificaciones() {
    const modal = document.getElementById('notificationsModal');
    const openButton = document.getElementById('btnOpenNotifications');
    const closeButton = document.getElementById('btnCloseNotifications');
    const markButton = document.getElementById('btnMarkNotifications');
    const notificationTriggers = document.querySelectorAll('[data-open-notifications]');

    if (!modal) {
        return;
    }

    const abrirNotificaciones = () => modal.classList.add('active');
    const cerrarNotificaciones = () => {
        modal.classList.remove('active');
        marcarNotificacionesVistas();
    };

    openButton?.addEventListener('click', abrirNotificaciones);
    notificationTriggers.forEach(trigger => trigger.addEventListener('click', abrirNotificaciones));
    closeButton?.addEventListener('click', cerrarNotificaciones);
    markButton?.addEventListener('click', cerrarNotificaciones);
}

async function marcarNotificacionesVistas() {
    const ids = dashboardData.notificationIds || [];
    const unread = Number(dashboardData.unreadNotifications || 0);

    if (!ids.length || unread === 0) {
        return;
    }

    const formData = new FormData();
    formData.append('ids', JSON.stringify(ids));

    try {
        const response = await fetch('api/marcar_notificaciones.php', { method: 'POST', body: formData });
        const data = await response.json();

        if (response.ok && data.status === 'success') {
            dashboardData.unreadNotifications = 0;
            document.getElementById('notificationBadge')?.remove();
            document.querySelectorAll('.notif-unread').forEach(item => item.classList.remove('notif-unread'));
        }
    } catch (error) {
        console.error('No se pudieron marcar las notificaciones como vistas.', error);
    }
}

function configurarFormularioSubida() {
    const uploadForm = document.getElementById('uploadForm');
    const loadingModal = document.getElementById('loadingModal');
    const messageModal = document.getElementById('messageModal');

    if (!uploadForm || !loadingModal || !messageModal) {
        return;
    }

    const iconSuccess = `<svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="var(--utmir-verde)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline class="anim-check" points="22 4 12 14.01 9 11.01"></polyline></svg>`;
    const iconError = `<svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="#ff6b8b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>`;

    function mostrarMensaje(tipo, titulo, mensaje) {
        loadingModal.classList.remove('active');
        setTimeout(() => {
            document.getElementById('msgIconContainer').innerHTML = tipo === 'exito' ? iconSuccess : iconError;
            document.getElementById('msgTitle').innerText = titulo;
            document.getElementById('msgTitle').style.color = tipo === 'exito' ? 'var(--utmir-verde)' : '#ff6b8b';
            document.getElementById('msgText').innerHTML = mensaje;
            messageModal.classList.add('active');
        }, 300);
    }

    window.cerrarMensajeYRecargar = function () {
        messageModal.classList.remove('active');
        if (document.getElementById('msgTitle').style.color === 'var(--utmir-verde)') {
            window.location.reload();
        }
    };

    uploadForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const fileInput = document.getElementById('archivo_pdf');
        if (fileInput.files.length === 0) return mostrarMensaje('error', 'Archivo Faltante', 'Debe seleccionar un documento.');

        const file = fileInput.files[0];
        if (!file.name.toLowerCase().endsWith('.pdf')) {
            return mostrarMensaje('error', 'Formato Inválido', 'El sistema solo admite archivos con extensión .pdf');
        }

        loadingModal.classList.add('active');
        document.getElementById('progressBar').style.width = '0%';
        document.getElementById('progressText').innerText = '0%';

        const formData = new FormData(uploadForm);
        const xhr = new XMLHttpRequest();

        xhr.upload.addEventListener('progress', function (e) {
            if (e.lengthComputable) {
                const pct = Math.round((e.loaded / e.total) * 100);
                document.getElementById('progressBar').style.width = `${pct}%`;
                document.getElementById('progressText').innerText = `${pct}%`;
            }
        });

        xhr.addEventListener('load', function () {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.status === 'success') {
                    mostrarMensaje('exito', 'Registro Exitoso', 'El documento se ha guardado correctamente en el expediente.');
                } else {
                    mostrarMensaje('error', 'Error de Sistema', response.message);
                }
            } catch (error) {
                console.error('Error PHP:', xhr.responseText);
                mostrarMensaje('error', 'Fallo del Servidor', 'El archivo excede la capacidad actual del servidor PHP.<br><br><b>Nota técnica:</b> Aumente los valores de <code>upload_max_filesize</code> y <code>post_max_size</code> en su archivo php.ini.');
            }
        });

        xhr.addEventListener('error', () => mostrarMensaje('error', 'Error de Conexión', 'No se pudo establecer comunicación con el servidor.'));
        xhr.open('POST', 'api/upload.php', true);
        xhr.send(formData);
    });
}
