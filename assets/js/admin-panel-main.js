        const toggleSwitches = document.querySelectorAll('.theme-checkbox');
        const currentTheme = localStorage.getItem('theme');
        const adminPanelDataElement = document.getElementById('admin-panel-data');
        const adminPanelData = adminPanelDataElement ? JSON.parse(adminPanelDataElement.textContent) : {};
        if (currentTheme) {
            document.documentElement.setAttribute('data-theme', currentTheme);
            if (currentTheme === 'light') toggleSwitches.forEach(sw => sw.checked = true);
        }
        function switchTheme(e) {
            const isChecked = e.target.checked;
            toggleSwitches.forEach(sw => sw.checked = isChecked);
            if (isChecked) { document.documentElement.setAttribute('data-theme', 'light'); localStorage.setItem('theme', 'light'); } 
            else { document.documentElement.setAttribute('data-theme', 'dark'); localStorage.setItem('theme', 'dark'); }    
        }
        toggleSwitches.forEach(sw => sw.addEventListener('change', switchTheme, false));

        function toggleDetails(detailId, rowElement) {
            var detailsPanel = document.getElementById(detailId);
            if (detailsPanel.style.display === "table-row") {
                detailsPanel.style.display = "none"; rowElement.classList.remove("active");
            } else {
                document.querySelectorAll('.detail-row').forEach(el => el.style.display = 'none');
                document.querySelectorAll('.main-row').forEach(el => el.classList.remove("active"));
                detailsPanel.style.display = "table-row"; rowElement.classList.add("active");
            }
        }

        let idEliminarGlobal = null;
        function resetFormularioEliminacion() {
            document.getElementById('motivoEliminacion').value = '';
            document.getElementById('motivoOtro').value = '';
            document.getElementById('comentarioEliminacion').value = '';
            document.getElementById('detalleOtro').value = '';
            document.getElementById('deleteAdminStatus').innerText = '';
            document.getElementById('grupoMotivoOtro').classList.add('is-hidden');
            document.getElementById('grupoDetalleOtro').classList.add('is-hidden');
        }
        function abrirModalDelete(id, matricula) {
            idEliminarGlobal = id;
            resetFormularioEliminacion();
            document.getElementById('modalMatriculaTexto').innerText = matricula;
            document.getElementById('modalConfirmacion').classList.add('active');
        }
        function abrirModalCalendario() { document.getElementById('modalCalendario').classList.add('active'); }
        function cerrarModal(idModal) { document.getElementById(idModal).classList.remove('active'); if (idModal === 'modalConfirmacion') idEliminarGlobal = null; }
        async function ejecutarEliminacion() {
            if (idEliminarGlobal === null) return;

            const motivo = document.getElementById('motivoEliminacion').value;
            const motivoOtro = document.getElementById('motivoOtro').value.trim();
            const comentario = document.getElementById('comentarioEliminacion').value.trim();
            const detalleOtro = document.getElementById('detalleOtro').value.trim();
            const status = document.getElementById('deleteAdminStatus');

            if (!motivo) {
                status.innerText = 'Selecciona el motivo de eliminacion.';
                return;
            }

            if (motivo === 'otro' && (!motivoOtro || !detalleOtro)) {
                status.innerText = 'Para Otro, escribe el motivo personalizado y la explicacion.';
                return;
            }

            const formData = new FormData();
            formData.append('id', idEliminarGlobal);
            formData.append('motivo_select', motivo);
            formData.append('motivo_otro', motivoOtro);
            formData.append('comentario', comentario);
            formData.append('detalle_otro', detalleOtro);

            status.innerText = 'Eliminando y enviando notificacion...';

            try {
                const response = await fetch('../api/delete_entrega.php', { method: 'POST', body: formData });
                const data = await response.json();

                if (!response.ok || data.status !== 'success') {
                    status.innerText = data.message || 'No se pudo eliminar la entrega.';
                    return;
                }

                window.location.reload();
            } catch (error) {
                status.innerText = 'No se pudo conectar con el servidor.';
            }
        }


        document.getElementById('btnAbrirModalCalendario')?.addEventListener('click', abrirModalCalendario);
        document.getElementById('motivoEliminacion')?.addEventListener('change', event => {
            const esOtro = event.target.value === 'otro';
            document.getElementById('grupoMotivoOtro').classList.toggle('is-hidden', !esOtro);
            document.getElementById('grupoDetalleOtro').classList.toggle('is-hidden', !esOtro);
            document.getElementById('deleteAdminStatus').innerText = '';
        });
        document.querySelectorAll('[data-modal-close]').forEach(button => {
            button.addEventListener('click', () => cerrarModal(button.dataset.modalClose));
        });
        document.getElementById('btnConfirmarEliminacion')?.addEventListener('click', ejecutarEliminacion);
        document.getElementById('formEnviarAviso')?.addEventListener('submit', async event => {
            event.preventDefault();
            const form = event.currentTarget;
            const status = document.getElementById('avisoAdminStatus');
            const formData = new FormData(form);

            status.classList.remove('admin-form-status-error', 'admin-form-status-success');
            status.innerText = 'Enviando mensaje...';

            try {
                const response = await fetch('../api/enviar_aviso.php', { method: 'POST', body: formData });
                const data = await response.json();

                if (!response.ok || data.status !== 'success') {
                    status.classList.add('admin-form-status-error');
                    status.innerText = data.message || 'No se pudo enviar el mensaje.';
                    return;
                }

                form.reset();
                status.classList.add('admin-form-status-success');
                status.innerText = data.message;
            } catch (error) {
                status.classList.add('admin-form-status-error');
                status.innerText = 'No se pudo conectar con el servidor.';
            }
        });
        document.querySelectorAll('.main-row[data-detail-target]').forEach(row => {
            row.addEventListener('click', () => toggleDetails(row.dataset.detailTarget, row));
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

        window.onload = function() {
            document.getElementById('currentYear').textContent = new Date().getFullYear();
            
            flatpickr("#rango_fechas", {
                mode: "range",
                minDate: "today",
                showMonths: 2,
                locale: "es",
                dateFormat: "Y-m-d",
                conjunction: " a "
            });

            // Lógica del Contador y Calendario Visual Administrativo
            const data = adminPanelData;
            const fechaFinStr = data.fechaFin || "";
            const fechaInicioStr = data.fechaInicio || "";
            
            if(fechaFinStr !== "" && fechaInicioStr !== "") {
                // Instanciar Calendario de Previsualización (Navegable pero no seleccionable)
                flatpickr("#calendario_admin_preview", {
                    mode: "range",
                    inline: true,
                    showMonths: 1,
                    locale: "es",
                    defaultDate: [fechaInicioStr, fechaFinStr]
                });

                const countDownDate = new Date(fechaFinStr.replace(/-/g, "/")).getTime();
                const startDate = new Date(fechaInicioStr.replace(/-/g, "/")).getTime();
                
                const x = setInterval(function() {
                    const now = new Date().getTime();
                    
                    if (now < startDate) {
                        document.getElementById("cd-dias").innerText = "--";
                        document.getElementById("cd-horas").innerText = "--";
                        document.getElementById("cd-mins").innerText = "--";
                        document.getElementById("cd-segs").innerText = "--";
                    } else {
                        const distance = countDownDate - now;

                        if (distance < 0) {
                            clearInterval(x);
                            document.getElementById("cd-dias").innerText = "00";
                            document.getElementById("cd-horas").innerText = "00";
                            document.getElementById("cd-mins").innerText = "00";
                            document.getElementById("cd-segs").innerText = "00";
                        } else {
                            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                            document.getElementById("cd-dias").innerText = days.toString().padStart(2, '0');
                            document.getElementById("cd-horas").innerText = hours.toString().padStart(2, '0');
                            document.getElementById("cd-mins").innerText = minutes.toString().padStart(2, '0');
                            document.getElementById("cd-segs").innerText = seconds.toString().padStart(2, '0');
                        }
                    }
                }, 1000);
            }
        };
    
