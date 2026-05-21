<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Académico - UTMIR</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
        <link rel="stylesheet" href="assets/css/dashboard.css">

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

        <div class="mascot-container">
            <img src="assets/images/robin_utmir.png" alt="Robin Mascota UTMIR" class="mascot-img">
        </div>

        <div class="theme-switch-wrapper">
            <label class="theme-switch" for="checkbox-pc">
                <input type="checkbox" id="checkbox-pc" class="theme-checkbox" />
                <div class="slider round">
                    <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
                    <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
                </div>
            </label>
        </div>

        <a href="api/logout.php" class="btn-logout">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
            <span>Cerrar Sesión</span>
        </a>


    </aside>

    <main class="main-content">
        <button class="notification-fab" id="btnOpenNotifications" type="button" title="Abrir notificaciones">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
            <?php if ($notificaciones_sin_leer > 0): ?>
                <span class="notification-badge" id="notificationBadge"><?php echo (int)$notificaciones_sin_leer; ?></span>
            <?php endif; ?>
        </button>
        
        <div class="top-row">
            <div class="hero-banner">
                <h1 class="hero-title">Recepción de Documentos Oficiales</h1>
                <p class="hero-subtitle">Bienvenido(a), <b class="text-on-dark"><?php echo htmlspecialchars($nombre_alumno); ?></b>. Este portal institucional está destinado a la carga exclusiva de las versiones finales y autorizadas de las memorias de estadía.</p>
            </div>
            
            <div class="notifications-panel">
                <div class="notif-header">
                    <svg class="icon-bell" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                    <h3>Avisos de Administración</h3>
                </div>
                <?php if (!empty($notificaciones_alumno)): ?>
                    <?php foreach (array_slice($notificaciones_alumno, 0, 3) as $notificacion): ?>
                        <button type="button" class="notif-item notif-item-button <?php echo ((int)$notificacion['leida'] === 0) ? 'notif-unread' : ''; ?>" data-open-notifications>
                            <span class="notif-date">
                                <?php echo htmlspecialchars(date("d/m/Y h:i A", strtotime($notificacion['fecha_creacion']))); ?>
                                <?php if ((int)$notificacion['leida'] === 0): ?><b>Nuevo</b><?php endif; ?>
                            </span>
                            <strong><?php echo htmlspecialchars($notificacion['asunto']); ?></strong>
                            <span><?php echo htmlspecialchars(strlen($notificacion['mensaje']) > 110 ? substr($notificacion['mensaje'], 0, 110) . '...' : $notificacion['mensaje']); ?></span>
                        </button>
                    <?php endforeach; ?>
                <?php else: ?>
                <div class="notif-item">
                    <span class="notif-date">Mensaje Automático - Sistema Activo</span>
                    Mantente al tanto. Si tu documento requiere correcciones de formato, se te notificará en este panel.
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="periodo-modulo">
            <div class="periodo-info-wrapper">
                <div class="periodo-info">
                    <h3><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--utmir-naranja)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg> 
                    Periodo de Recepción</h3>
                    <p>Mantente al tanto de la fecha límite para la carga de tu memoria. La plataforma se cerrará automáticamente al finalizar el contador.</p>
                </div>
                <div class="countdown-box">
                    <div class="cd-item"><span class="cd-number" id="cd-dias">00</span><span class="cd-label">Días</span></div>
                    <div class="cd-item"><span class="cd-number" id="cd-horas">00</span><span class="cd-label">Horas</span></div>
                    <div class="cd-item"><span class="cd-number" id="cd-mins">00</span><span class="cd-label">Mins</span></div>
                    <div class="cd-item"><span class="cd-number" id="cd-segs">00</span><span class="cd-label">Segs</span></div>
                </div>
            </div>
            
            <div class="calendario-wrapper">
                <input type="text" id="calendario_alumno" class="hidden-input">
            </div>
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
                    <span class="badge-status <?php echo $yaSubioTSU ? 'badge-success' : 'badge-pending'; ?>">
                        <?php echo $yaSubioTSU ? 'Entregado' : 'Pendiente'; ?>
                    </span>
                    <h3>Memoria TSU (6to)</h3>
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
                    <span class="badge-status <?php echo $yaSubioING ? 'badge-success' : 'badge-pending'; ?>">
                        <?php echo $yaSubioING ? 'Entregado' : 'Pendiente'; ?>
                    </span>
                    <h3>Memoria ING/LIC (11vo)</h3>
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
                                    <?php if (!$yaSubioTSU): ?><option value="6to Cuatrimestre (TSU)">TSU (6to Cuatrimestre)</option><?php endif; ?>
                                    <?php if (!$yaSubioING): ?><option value="11vo Cuatrimestre (ING/LIC)">ING/LIC (11vo Cuatrimestre)</option><?php endif; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>2. Programa Educativo</label>
                                <select id="carrera" name="programa_educativo" class="form-select" required>
                                    <option value="">-- Seleccione su carrera --</option>
                                    <option value="TIeID">Licenciatura en Ingeniería en Tecnologías de la Información e Innovación Digital</option>
                                    <option value="Ing. Civil">Licenciatura en Ingeniería Civil</option>
                                    <option value="Gastronomia">Licenciatura en Gastronomía</option>
                                    <option value="Turismo">Licenciatura en Gestión y Desarrollo Turístico</option>
                                    <option value="Agrobiotecnologia">Licenciatura en Ingeniería en Agrobiotecnología</option>
                                    <option value="Administracion">Licenciatura en Administración</option>
                                    <option value="Contaduria">Licenciatura en Contaduria</option>
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
                        <li>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"></path></svg>
                            <b>Formato Estricto:</b> El sistema está configurado para admitir únicamente archivos con la extensión <code>.pdf</code>.
                        </li>
                        <li>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"></path></svg>
                            <b>Nomenclatura Requerida:</b> Es imperativo nombrar el archivo correctamente antes de la carga institucional:
                            <span class="code-snippet">Matricula_Nombres_Carrera_Cuatrimestre.pdf</span>
                            <span class="example-text">Ej: 20210001_JuanPerez_TIeID_6to.pdf</span>
                        </li>
                        <li>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"></path></svg>
                            <b>Documento Definitivo:</b> Asegúrate de cargar la versión final con firmas. No se permiten modificaciones posteriores sin autorización administrativa.
                        </li>
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
                <h3>Notificaciones de administracion</h3>
                <button type="button" class="notif-close" id="btnCloseNotifications" title="Cerrar">x</button>
            </div>
            <?php if (!empty($notificaciones_alumno)): ?>
                <div class="notif-modal-list">
                    <?php foreach ($notificaciones_alumno as $notificacion): ?>
                        <article class="notif-modal-item <?php echo ((int)$notificacion['leida'] === 0) ? 'notif-unread' : ''; ?>">
                            <div class="notif-modal-meta">
                                <span><?php echo htmlspecialchars(date("d/m/Y h:i A", strtotime($notificacion['fecha_creacion']))); ?></span>
                                <span><?php echo htmlspecialchars($notificacion['tipo'] === 'eliminacion' ? 'Archivo eliminado' : 'Mensaje'); ?></span>
                            </div>
                            <h4><?php echo htmlspecialchars($notificacion['asunto']); ?></h4>
                            <?php if (!empty($notificacion['motivo'])): ?>
                                <p><b>Motivo:</b> <?php echo htmlspecialchars($notificacion['motivo']); ?></p>
                            <?php endif; ?>
                            <p><?php echo nl2br(htmlspecialchars($notificacion['mensaje'])); ?></p>
                            <?php if (!empty($notificacion['comentario'])): ?>
                                <p><b>Comentario:</b> <?php echo nl2br(htmlspecialchars($notificacion['comentario'])); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($notificacion['detalle'])): ?>
                                <p><b>Detalle:</b> <?php echo nl2br(htmlspecialchars($notificacion['detalle'])); ?></p>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="message-text">No tienes notificaciones pendientes.</p>
            <?php endif; ?>
            <button class="btn-ok" id="btnMarkNotifications">Entendido</button>
        </div>
    </div>

    <div class="modal-overlay" id="loadingModal">
        <div class="modal-box">
            <svg class="anim-float modal-icon-spaced" width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="var(--utmir-verde)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 16 12 12 8 16"></polyline><line x1="12" y1="12" x2="12" y2="21"></line><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"></path><polyline points="16 16 12 12 8 16"></polyline></svg>
            <h3 id="loadingTitle" class="modal-title-loading">Procesando Documento...</h3>
            <p class="modal-text-muted">Transfiriendo a la nube institucional.</p>
            <div class="progress-bar-bg"><div class="progress-bar-fill" id="progressBar"></div></div>
            <p id="progressText" class="progress-text">0%</p>
        </div>
    </div>

    <div class="modal-overlay" id="messageModal">
        <div class="modal-box">
            <div id="msgIconContainer" class="message-icon-container"></div>
            <h3 id="msgTitle" class="message-title">Título</h3>
            <p id="msgText" class="message-text">Mensaje</p>
            <button class="btn-ok" id="btnMessageOk">Entendido</button>
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


    <!-- MODAL DEL VIDEO OBLIGATORIO -->
    <div class="modal-overlay video-modal <?php echo ($video_visto == 0) ? 'active' : ''; ?>" id="videoModal">
        <div class="modal-box video-modal-box">
            <h3 class="video-title">Bienvenido al Portal GEL</h3>
            <p class="video-text">Por favor, mira el siguiente tutorial completo para aprender a subir tu memoria de estadía. <b class="video-warning">No podrás cerrar esta ventana hasta que el video termine.</b></p>
            
            <!-- Contenedor donde la API de YouTube inyectará el video -->
            <div id="youtubePlayer" class="youtube-player"></div>
            
            <button id="btnCerrarVideo" class="btn-submit video-close-btn <?php echo ($video_visto == 1) ? '' : 'is-hidden'; ?>">Entendido, ir al panel</button>
        </div>
    </div>

    <!-- SCRIPT DE LA API DE YOUTUBE -->
    <script src="https://www.youtube.com/iframe_api"></script>
        <script src="assets/js/dashboard-video.js"></script>


</body>
</html>
