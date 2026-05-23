<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Académico - UTMIR</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <style>
        /* DISEÑO DE MENÚ INSTITUCIONAL SÓLIDO */
        :root { --sidebar-width: 280px; --navbar-height: 70px; }
        
        .sidebar {
            position: fixed; top: 0; left: 0; width: var(--sidebar-width); height: 100vh;
            display: flex; flex-direction: column; overflow: hidden; z-index: 1000;
            background: var(--bg-sidebar, #111827); border-right: 1px solid var(--borde-color, #374151);
            transition: width 0.3s ease, height 0.3s ease;
        }
        .main-content { margin-left: var(--sidebar-width); transition: margin 0.3s ease, padding 0.3s ease; }
        .sidebar-actions { margin-top: auto; display: flex; flex-direction: column; align-items: center; gap: 20px; width: 100%; transition: all 0.3s ease; padding-bottom: 20px;}
        .profile-card, .mascot-container { transition: opacity 0.2s ease; opacity: 1; }
        
        body.is-scrolled .sidebar {
            width: 100%; height: var(--navbar-height);
            background: #0f172a; /* Azul marino institucional */
            border-right: none; border-bottom: 3px solid #00a859; /* Identidad UTMiR */
            flex-direction: row; justify-content: space-between; align-items: center;
            padding: 0 40px; box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        body.is-scrolled .main-content { margin-left: 0; padding-top: var(--navbar-height); }
        body.is-scrolled .profile-card, body.is-scrolled .mascot-container { display: none; }
        
        body.is-scrolled .brand-logo { flex-direction: row; align-items: center; margin: 0; padding: 0; }
        body.is-scrolled .brand-logo img { width: 35px; height: 35px; margin: 0 10px 0 0; }
        body.is-scrolled .brand-logo span { color: #ffffff; font-weight: 700; font-size: 1.1rem; }
        
        body.is-scrolled .sidebar-actions { flex-direction: row; margin-top: 0; padding-bottom: 0; width: auto; gap: 20px; }
        body.is-scrolled .btn-logout { padding: 8px 20px; background-color: #e67e22; border-radius: 4px; display: flex; align-items: center; gap: 8px; font-weight: 600; color: white;}
        body.is-scrolled .btn-logout svg { display: none; }
        body.is-scrolled .btn-logout:hover { background-color: #d67118; }
        
        .notif-modal-list { max-height: 55vh; overflow-y: auto; padding-right: 10px; }
        .notif-modal-list::-webkit-scrollbar { width: 6px; }
        .notif-modal-list::-webkit-scrollbar-thumb { background: #00a859; border-radius: 4px; }
        
        .video-thumb-card {
            background: var(--bg-card, #1f2937); border: 1px solid var(--borde-color, #374151);
            border-radius: 8px; padding: 15px; display: flex; flex-direction: column; 
            align-items: center; justify-content: center; cursor: pointer; transition: 0.3s;
            text-align: center; width: 140px; flex-shrink: 0;
        }
        .video-thumb-card:hover { border-color: #00a859; transform: translateY(-3px); }
        .video-thumb-card svg { stroke: #00a859; margin-bottom: 8px; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="brand-logo">
            <img src="assets/images/utmir_logo_2026.png" alt="UTMIR Logo" class="sidebar-logo-img">
            <span>Portal Estadías</span>
        </div>
        <div class="profile-card">
            <div class="profile-avatar"><?php echo strtoupper(substr($nombre_alumno, 0, 1)); ?></div>
            <h2><?php echo htmlspecialchars($nombre_alumno); ?></h2>
            <div class="matricula-badge"><?php echo htmlspecialchars($matricula_alumno); ?></div>
        </div>
        <div class="mascot-container"><img src="assets/images/robin_utmir.png" alt="Robin Mascota UTMIR" class="mascot-img"></div>
        
        <div class="sidebar-actions">
            <div class="theme-switch-wrapper">
                <label class="theme-switch" for="checkbox-pc">
                    <input type="checkbox" id="checkbox-pc" class="theme-checkbox" />
                    <div class="slider round">
                        <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line></svg>
                        <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
                    </div>
                </label>
            </div>
            <a href="api/logout.php" class="btn-logout">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                <span>Cerrar Sesión</span>
            </a>
        </div>
    </aside>

    <main class="main-content">
        
        <div class="hero-banner" style="text-align: center; margin-bottom: 30px;">
            <h1 class="hero-title">Recepción de Documentos Oficiales</h1>
            <p class="hero-subtitle">Bienvenido(a), <b class="text-on-dark"><?php echo htmlspecialchars($nombre_alumno); ?></b>. Este portal institucional está destinado a la carga exclusiva de las versiones finales y autorizadas de las memorias de estadía.</p>
        </div>
        
        <div class="top-row" style="display: flex; gap: 20px; align-items: stretch; margin-bottom: 20px; flex-wrap: wrap;">
            
            <div class="video-thumb-card" onclick="document.getElementById('videoModal').classList.add('active'); document.getElementById('btnCerrarVideo').classList.remove('is-hidden');">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polygon points="10 8 16 12 10 16 10 8"></polygon></svg>
                <span style="font-weight: 600; font-size: 0.9rem; color: var(--texto-principal);">Ver Tutorial</span>
            </div>

            <div class="notifications-panel" id="mainNotifPanel" style="flex: 1; min-width: 300px; cursor: pointer;" onclick="document.getElementById('notificationsModal').classList.add('active');">
                <div class="notif-header">
                    <svg class="icon-bell" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                    <h3>Avisos de Administración <?php if ($notificaciones_sin_leer > 0): ?><span id="inPanelBadge" style="background: red; color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.8rem; margin-left: 10px;"><?php echo $notificaciones_sin_leer; ?> Nuevos</span><?php endif; ?></h3>
                </div>
                <div id="previewNotifContainer">
                    <?php if (!empty($notificaciones_alumno)): ?>
                        <?php foreach (array_slice($notificaciones_alumno, 0, 2) as $notificacion): ?>
                            <div class="notif-item <?php echo ((int)$notificacion['leida'] === 0) ? 'notif-unread' : ''; ?>">
                                <span class="notif-date"><?php echo htmlspecialchars(date("d/m/Y h:i A", strtotime($notificacion['fecha_creacion']))); ?></span>
                                <strong><?php echo htmlspecialchars($notificacion['asunto']); ?></strong>
                                <span><?php echo htmlspecialchars(strlen($notificacion['mensaje']) > 110 ? substr($notificacion['mensaje'], 0, 110) . '...' : $notificacion['mensaje']); ?></span>
                            </div>
                        <?php endforeach; ?>
                        <div style="text-align: center; color: #00a859; font-size: 0.85rem; margin-top: 10px; font-weight: 600;">Clic para abrir historial</div>
                    <?php else: ?>
                        <div class="notif-item">
                            <span class="notif-date">Mensaje Automático - Sistema Activo</span>
                            Mantente al tanto. Si tu documento requiere correcciones de formato, se te notificará en este panel.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="periodo-modulo">
            <div class="periodo-info-wrapper">
                <div class="periodo-info">
                    <h3><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--utmir-naranja)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg> 
                    Periodo de Recepción</h3>
                    <p>Mantente al tanto de la fecha límite para la carga de tu memoria. La plataforma se cerrará automáticamente al finalizar el contador.</p>
                </div>
                
                <div class="countdown-box" style="margin-top: 20px; display: flex; gap: 20px; justify-content: flex-start; flex-wrap: wrap;">
                    <div class="cd-item" style="background: #111827; padding: 25px 35px; border-radius: 12px; min-width: 120px; border: 2px solid #1f2937; box-shadow: 0 4px 6px rgba(0,0,0,0.3);">
                        <span class="cd-number" id="cd-dias" style="font-size: 3.5rem; font-weight: 800; color: #10b981; line-height: 1;">00</span>
                        <span class="cd-label" style="font-size: 1rem; color: #9ca3af; margin-top: 10px; letter-spacing: 2px; font-weight: 600;">DÍAS</span>
                    </div>
                    <div class="cd-item" style="background: #111827; padding: 25px 35px; border-radius: 12px; min-width: 120px; border: 2px solid #1f2937; box-shadow: 0 4px 6px rgba(0,0,0,0.3);">
                        <span class="cd-number" id="cd-horas" style="font-size: 3.5rem; font-weight: 800; color: #10b981; line-height: 1;">00</span>
                        <span class="cd-label" style="font-size: 1rem; color: #9ca3af; margin-top: 10px; letter-spacing: 2px; font-weight: 600;">HORAS</span>
                    </div>
                    <div class="cd-item" style="background: #111827; padding: 25px 35px; border-radius: 12px; min-width: 120px; border: 2px solid #1f2937; box-shadow: 0 4px 6px rgba(0,0,0,0.3);">
                        <span class="cd-number" id="cd-mins" style="font-size: 3.5rem; font-weight: 800; color: #10b981; line-height: 1;">00</span>
                        <span class="cd-label" style="font-size: 1rem; color: #9ca3af; margin-top: 10px; letter-spacing: 2px; font-weight: 600;">MINS</span>
                    </div>
                    <div class="cd-item" style="background: #111827; padding: 25px 35px; border-radius: 12px; min-width: 120px; border: 2px solid #1f2937; box-shadow: 0 4px 6px rgba(0,0,0,0.3);">
                        <span class="cd-number" id="cd-segs" style="font-size: 3.5rem; font-weight: 800; color: #10b981; line-height: 1;">00</span>
                        <span class="cd-label" style="font-size: 1rem; color: #9ca3af; margin-top: 10px; letter-spacing: 2px; font-weight: 600;">SEGS</span>
                    </div>
                </div>
            </div>
            
            <div class="calendario-wrapper"><input type="text" id="calendario_alumno" class="hidden-input"></div>
        </div>

        <div class="status-grid">
            <div class="status-card <?php echo $yaSubioTSU ? 'success' : 'pending'; ?>">
                <div class="status-icon-box">
                    <?php if ($yaSubioTSU): ?>
                      <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline class="anim-check" points="22 4 12 14.01 9 11.01"></polyline></svg>
                    <?php else: ?>
                        <svg class="anim-float" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    <?php endif; ?>
                </div>
                <div class="status-info">
                    <span class="badge-status <?php echo $yaSubioTSU ? 'badge-success' : 'badge-pending'; ?>"><?php echo $yaSubioTSU ? 'Entregado' : 'Pendiente'; ?></span>
                    <h3>6º cuatrimestre (Técnico Superior Universitario)</h3>
                    <?php if ($yaSubioTSU): ?>
                        <span class="status-meta">Fecha de registro: <?php echo date("d/m/Y", strtotime($entrega_tsu['fecha_subida'])); ?></span><br>
                        <a href="<?php echo $entrega_tsu['link_google_drive']; ?>" target="_blank" class="link-drive">Abrir PDF Institucional ↗</a>
                    <?php else: ?>
                        <span class="status-meta">Esperando archivo del alumno.</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="status-card <?php echo $yaSubioING ? 'success' : 'pending'; ?>">
                <div class="status-icon-box">
                    <?php if ($yaSubioING): ?>
                      <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline class="anim-check" points="22 4 12 14.01 9 11.01"></polyline></svg>
                    <?php else: ?>
                        <svg class="anim-float" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"></path><path d="M6 12v5c3 3 9 3 12 0v-5"></path></svg>
                    <?php endif; ?>
                </div>
                <div class="status-info">
                    <span class="badge-status <?php echo $yaSubioING ? 'badge-success' : 'badge-pending'; ?>"><?php echo $yaSubioING ? 'Entregado' : 'Pendiente'; ?></span>
                    <h3>10º cuatrimestre (Ingeniería/Licenciatura)</h3>
                    <?php if ($yaSubioING): ?>
                        <span class="status-meta">Fecha de registro: <?php echo date("d/m/Y", strtotime($entrega_ing['fecha_subida'])); ?></span><br>
                        <a href="<?php echo $entrega_ing['link_google_drive']; ?>" target="_blank" class="link-drive">Abrir PDF Institucional ↗</a>
                    <?php else: ?>
                        <span class="status-meta">Esperando archivo del alumno.</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if (!$periodo_activo): ?>
            <div class="state-card state-card--warning">
                <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="var(--utmir-naranja)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                <h3 class="state-card-title">Plataforma Cerrada</h3>
                <p class="state-card-text">El periodo de recepción de memorias ha concluido o no se encuentra activo. Contacta al Departamento de Vinculación para mayor información.</p>
            </div>
        <?php elseif ($haTerminadoTodo): ?>
            <div class="state-card state-card--success">
                <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="var(--utmir-verde)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"></path><path d="m9 12 2 2 4-4"></path></svg>
                <h3 class="state-card-title">Expediente Institucional Completo</h3>
                <p class="state-card-text">El departamento de Vinculación ha recibido exitosamente ambos documentos. Ya no hay acciones pendientes.</p>
            </div>
        <?php elseif ($acreditado == 0): ?>
            <div class="state-card state-card--warning">
                <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="var(--utmir-naranja)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <h3 class="state-card-title">Acceso Restringido</h3>
                <p class="state-card-text">Aún no estás acreditado para subir tu memoria. Tu proceso está en revisión administrativa. Por favor, espera indicaciones.</p>
            </div>
        <?php else: ?>
            <div class="content-grid">
                
                <div class="panel-card">
                    <div class="panel-header">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                        <h3>Carga de Documento Oficial</h3>
                    </div>
    
                    <form id="uploadForm" enctype="multipart/form-data">
                        <div class="form-row">
                            <div class="form-group">
                                <label>1. Nivel Educativo</label>
                                <select name="cuatrimestre" id="cuatrimestre" class="form-select" required>
                                    <option value="">-- Seleccione una opción --</option>
                                    <?php if (!$yaSubioTSU): ?><option value="6º cuatrimestre (Técnico Superior Universitario)">6º cuatrimestre (Técnico Superior Universitario)</option><?php endif; ?>
                                    <?php if (!$yaSubioING): ?><option value="10º cuatrimestre (Ingeniería/Licenciatura)">10º cuatrimestre (Ingeniería/Licenciatura)</option><?php endif; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>2. Programa Educativo</label>
                                <select id="carrera" name="programa_educativo" class="form-select" required>
                                    <option value="">-- Seleccione su carrera --</option>
                                    <option value="Licenciatura en Ingeniería en Tecnologías de la Información e Innovación Digital">Licenciatura en Ingeniería en Tecnologías de la Información e Innovación Digital</option>
                                    <option value="Licenciatura en Ingeniería Civil">Licenciatura en Ingeniería Civil</option>
                                    <option value="Licenciatura en Gastronomía">Licenciatura en Gastronomía</option>
                                    <option value="Licenciatura en Gestión y Desarrollo Turístico">Licenciatura en Gestión y Desarrollo Turístico</option>
                                    <option value="Licenciatura en Ingeniería en Agrobiotecnología">Licenciatura en Ingeniería en Agrobiotecnología</option>
                                    <option value="Licenciatura en Administración">Licenciatura en Administración</option>
                                    <option value="Licenciatura en Contaduría">Licenciatura en Contaduría</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>3. Archivo Digital (.PDF)</label>
                            <div class="file-drop-area" id="dropArea">
                                <svg class="anim-float" id="uploadIcon" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--texto-mutado)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg>
                                <span class="file-msg" id="fileNameDisplay">Haga clic o arrastre el archivo aquí</span>
                                <span class="file-submsg">Límite establecido: 5MB</span>
                                <input type="file" name="memoria_archivo" id="archivo_pdf" accept=".pdf" required>
                            </div>
                        </div>

                        <button type="submit" class="btn-submit">Procesar Entrega</button>
                    </form>
                </div>

                <div class="panel-card">
                    <div class="panel-header">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--utmir-naranja)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                        <h3>Lineamientos de Recepción</h3>
                    </div>
                    <ul class="info-list">
                        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"></path></svg><b>Formato Estricto:</b> El sistema está configurado para admitir únicamente archivos con la extensión <code>.pdf</code>.</li>
                        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"></path></svg><b>Nomenclatura Requerida:</b> Es imperativo nombrar el archivo correctamente antes de la carga institucional: <span class="code-snippet">Matricula_Nombres_Carrera_Cuatrimestre.pdf</span></li>
                        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"></path></svg><b>Documento Definitivo:</b> Asegúrate de cargar la versión final con firmas. No se permiten modificaciones posteriores sin autorización.</li>
                    </ul>
                </div>

            </div>
        <?php endif; ?>

        <footer class="dashboard-footer">
            <div class="footer-bottom">
            PROYECTA • INNOVA • ALCANZA<br><br>
            © <span id="currentYear"></span>. Universidad Tecnológica de Mineral de la Reforma. Todos los derechos reservados.
            </div>
        </footer>

    </main>

    <div class="modal-overlay notification-modal <?php echo ($notificaciones_sin_leer > 0) ? 'active' : ''; ?>" id="notificationsModal">
        <div class="modal-box notification-modal-box">
            <div class="notif-modal-header">
                <h3>Notificaciones de administración</h3>
                <button type="button" class="notif-close" id="btnCloseNotifications" title="Cerrar" onclick="document.getElementById('notificationsModal').classList.remove('active');">x</button>
            </div>
            <div class="notif-modal-list" id="modalListContainer">
                <?php if (!empty($notificaciones_alumno)): ?>
                    <?php foreach ($notificaciones_alumno as $notificacion): ?>
                        <article class="notif-modal-item <?php echo ((int)$notificacion['leida'] === 0) ? 'notif-unread' : ''; ?>">
                            <div class="notif-modal-meta">
                                <span><?php echo htmlspecialchars(date("d/m/Y h:i A", strtotime($notificacion['fecha_creacion']))); ?></span>
                                <span><?php echo htmlspecialchars($notificacion['tipo'] === 'eliminacion' ? 'Archivo rechazado' : 'Mensaje oficial'); ?></span>
                            </div>
                            <h4><?php echo htmlspecialchars($notificacion['asunto']); ?></h4>
                            <?php if (!empty($notificacion['motivo'])): ?>
                                <p><b>Motivo:</b> <?php echo htmlspecialchars($notificacion['motivo']); ?></p>
                            <?php endif; ?>
                            <p><?php echo nl2br(htmlspecialchars($notificacion['mensaje'])); ?></p>
                            <?php if (!empty($notificacion['comentario'])): ?>
                                <p><b>Observaciones:</b> <?php echo nl2br(htmlspecialchars($notificacion['comentario'])); ?></p>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="message-text">No tienes notificaciones pendientes.</p>
                <?php endif; ?>
            </div>
            <button class="btn-ok" id="btnMarkNotifications">Entendido, marcar como visto y limpiar panel</button>
        </div>
    </div>

    <div class="modal-overlay video-modal <?php echo ($video_visto == 0) ? 'active' : ''; ?>" id="videoModal">
        <div class="modal-box video-modal-box">
            <h3 class="video-title">Bienvenido al Portal GEL</h3>
            <p class="video-text">Por favor, mira el siguiente tutorial completo para aprender a subir tu memoria de estadía.</p>
            <div id="youtubePlayer" class="youtube-player"></div>
            <button id="btnCerrarVideo" class="btn-submit video-close-btn <?php echo ($video_visto == 1) ? '' : 'is-hidden'; ?>" onclick="document.getElementById('videoModal').classList.remove('active');">Entendido, ir al panel</button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>

    <script type="application/json" id="dashboard-data">
        <?php echo json_encode([
            'fechaInicio' => $fecha_inicio,
            'fechaFin' => $fecha_fin,
            'canUpload' => (!$haTerminadoTodo && $periodo_activo),
            'videoVisto' => (int)$video_visto,
            'notificationIds' => array_values(array_map('intval', array_column($notificaciones_alumno, 'id_notificacion'))),
            'unreadNotifications' => (int)$notificaciones_sin_leer
        ]); ?>
    </script>

    <script src="assets/js/dashboard-main.js"></script>
    <script src="https://www.youtube.com/iframe_api"></script>
    <script src="assets/js/dashboard-video.js"></script>

    <script>
        // Diseño de menú sólido institucional superior al scrollear
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) { document.body.classList.add('is-scrolled'); } 
            else { document.body.classList.remove('is-scrolled'); }
        });

        // Limpieza visual de notificaciones
        document.getElementById('btnMarkNotifications')?.addEventListener('click', () => {
            const badge = document.getElementById('inPanelBadge');
            if (badge) badge.style.display = 'none';
            
            document.getElementById('modalListContainer').innerHTML = '<p class="message-text" style="color:#00a859; font-weight:600;">Historial limpio. No tienes notificaciones pendientes.</p>';
            
            document.getElementById('previewNotifContainer').innerHTML = `
                <div class="notif-item">
                    <span class="notif-date">Mensaje Automático - Sistema Activo</span>
                    Mantente al tanto. Las notificaciones han sido marcadas como leídas y archivadas.
                </div>`;
            
            document.getElementById('notificationsModal').classList.remove('active');
        });
    </script>
</body>
</html>