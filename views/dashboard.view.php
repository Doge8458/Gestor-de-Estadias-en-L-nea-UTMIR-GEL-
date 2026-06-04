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
        /* Diseño original de Barra Lateral y Fix Móvil */
        :root { --sidebar-width: 280px; }
        body { margin: 0; padding: 0; box-sizing: border-box; }
        .sidebar { position: fixed; top: 0; left: 0; width: var(--sidebar-width); height: 100vh; height: 100dvh; background: var(--bg-sidebar, #0B131E); border-right: 1px solid var(--borde-color, #1f2937); display: flex; flex-direction: column; z-index: 1050; transition: transform 0.4s cubic-bezier(0.25, 1, 0.5, 1); overflow: hidden; }
        .main-content { margin-left: var(--sidebar-width); padding: 30px 4%; transition: margin-left 0.4s cubic-bezier(0.25, 1, 0.5, 1); min-height: 100vh; display: flex; flex-direction: column; }
        .sidebar-overlay { display: none; }
        .mobile-header { display: none; }
        html { scroll-behavior: smooth; }
        .fade-in-up { animation: fadeInUp 0.6s cubic-bezier(0.25, 1, 0.5, 1) forwards; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); width: min(86vw, 330px); height: 100vh; height: 100dvh; overflow-y: auto; }
            .sidebar.active { transform: translateX(0); box-shadow: 5px 0 25px rgba(0,0,0,0.5); }
            .main-content { margin-left: 0; padding-top: 90px; }
            .mobile-header { display: flex; justify-content: space-between; align-items: center; position: fixed; top: 0; left: 0; width: 100%; height: 70px; background: var(--bg-sidebar, #0B131E); border-bottom: 3px solid #00a859; z-index: 1040; padding: 0 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); box-sizing: border-box;}
            .mobile-header img { width: 40px; height: 40px; }
            .mobile-header .logo-text { color: #fff; font-weight: 700; font-size: 1.2rem; display: flex; align-items: center; gap: 10px;}
            .hamburger-btn { background: none; border: none; color: #fff; cursor: pointer; padding: 5px; }
            .hamburger-btn svg { width: 30px; height: 30px; stroke: currentColor; }
            .sidebar-overlay.active { display: block; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.6); backdrop-filter: blur(3px); z-index: 1045; animation: fadeIn 0.3s ease; }
            @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

            .top-row, .status-grid, .content-grid, .countdown-box { flex-direction: column; width: 100%; }
            .status-grid, .content-grid { grid-template-columns: 1fr; }
            .countdown-box .cd-item { width: 100%; box-sizing: border-box; }
            
            /* Corrección del Video Móvil */
            .video-thumb-card { width: 100% !important; flex: 1; box-sizing: border-box; margin-bottom: 20px; }
        }

        .video-thumb-card { background: var(--bg-card, #1f2937); border: 1px solid var(--borde-color, #374151); border-radius: 8px; padding: 15px; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; transition: 0.3s; text-align: center; width: 140px; flex-shrink: 0; }
        .video-thumb-card:hover { border-color: #00a859; transform: translateY(-3px); }
        .video-thumb-card svg { stroke: #00a859; margin-bottom: 8px; }
        .sidebar-bottom { margin-top: auto; padding-bottom: 0; width: 100%; display: flex; flex-direction: column; align-items: center; gap: 10px;}
        .notification-fab { display: none !important; } 

        .notif-modal-list { max-height: 55vh; overflow-y: auto; padding-right: 10px; }
        .notif-modal-list::-webkit-scrollbar { width: 6px; }
        .notif-modal-list::-webkit-scrollbar-thumb { background: #00a859; border-radius: 4px; }
        .notif-date-group { margin-bottom: 20px; }
        .notif-date-header { font-size: 0.8rem; color: var(--texto-mutado); text-transform: uppercase; letter-spacing: 1px; font-weight: 700; margin-bottom: 10px; border-bottom: 1px solid var(--borde-color); padding-bottom: 5px; }
        .notif-modal-item { border: 1px solid var(--borde-color); border-radius: 8px; margin-bottom: 10px; padding: 15px; background: var(--bg-card); cursor: pointer; transition: 0.3s; border-left: 4px solid #00a859; }
        .notif-modal-item.notif-unread { border-left: 4px solid var(--utmir-naranja); background: rgba(231, 77, 35, 0.05); }
        .notif-modal-item.notif-read-state { border-left: 4px solid var(--texto-mutado); opacity: 0.7; background: transparent; } 
        .notif-modal-item:hover { background: var(--bg-input); }
        .notif-preview { display: flex; justify-content: space-between; align-items: center; }
        .notif-body { display: none; padding-top: 15px; margin-top: 15px; border-top: 1px dashed var(--borde-color); font-size: 0.9rem; color: var(--texto-claro); line-height: 1.6; }
        .notif-body.expanded { display: block; animation: fadeIn 0.3s ease; }
    </style>
</head>
<body>

    <header class="mobile-header">
        <div class="logo-text"><img src="assets/images/utmir_logo_2026.png" alt="UTMIR Logo"> Portal Estadías</div>
        <button class="hamburger-btn" id="btnToggleSidebarAlumno">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>
    </header>
    
    <div class="sidebar-overlay" id="sidebarOverlayAlumno"></div>

    <aside class="sidebar" id="mainSidebarAlumno">
        <div class="brand-logo">
            <img src="assets/images/utmir_logo_2026.png" alt="UTMIR Logo" class="sidebar-logo-img">
            <span>Portal Estadías</span>
        </div>
        <div class="profile-card">
            <div class="profile-avatar" id="profileAvatar">
                <?php if (!empty($foto_perfil)): ?>
                    <img src="<?php echo htmlspecialchars($foto_perfil); ?>" alt="Foto de perfil" id="profileAvatarImg">
                <?php else: ?>
                    <span id="profileAvatarInitial"><?php echo strtoupper(substr($nombre_alumno, 0, 1)); ?></span>
                <?php endif; ?>
            </div>
            <h2><?php echo htmlspecialchars($nombre_alumno); ?></h2>
            <div class="matricula-badge"><?php echo htmlspecialchars($matricula_alumno); ?></div>
            <form class="profile-photo-form" id="profilePhotoForm" enctype="multipart/form-data">
                <label class="profile-photo-btn" for="foto_perfil">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                    <span>Cambiar foto</span>
                </label>
                <input type="file" name="foto_perfil" id="foto_perfil" class="profile-photo-input" accept="image/jpeg,image/png,image/webp">
                <p class="profile-photo-status" id="profilePhotoStatus"></p>
            </form>
        </div>
        <div class="mascot-container"><img src="assets/images/robin_utmir.png" alt="Robin Mascota UTMIR" class="mascot-img"></div>
        
        <div class="sidebar-bottom">
            <div class="theme-switch-wrapper">
                <label class="theme-switch" for="checkbox-pc">
                    <input type="checkbox" id="checkbox-pc" class="theme-checkbox" />
                    <div class="slider round">
                        <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line></svg>
                        <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
                    </div>
                </label>
            </div>
            <a href="api/logout.php" class="btn-logout" style="width: 80%; justify-content: center;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                <span>Cerrar Sesión</span>
            </a>
        </div>
    </aside>

    <main class="main-content fade-in-up">
        
        <div class="hero-banner" style="text-align: center; margin-bottom: 30px;">
            <h1 class="hero-title">Recepción de Documentos Oficiales</h1>
            <p class="hero-subtitle">Bienvenido(a), <b class="text-on-dark"><?php echo htmlspecialchars($nombre_alumno); ?></b>. Este portal institucional está destinado a la carga exclusiva de las versiones finales y autorizadas de las memorias de estadía.</p>
        </div>
        
        <div class="top-row" style="display: flex; gap: 20px; align-items: stretch; margin-bottom: 20px;">
            
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
                        <div style="text-align: center; color: #00a859; font-size: 0.85rem; margin-top: 10px; font-weight: 600;">Clic para abrir historial completo</div>
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
                <div class="countdown-box">
                    <div class="cd-item">
                        <span class="cd-number" id="cd-dias">00</span>
                        <span class="cd-label">DÍAS</span>
                    </div>
                    <div class="cd-item">
                        <span class="cd-number" id="cd-horas">00</span>
                        <span class="cd-label">HORAS</span>
                    </div>
                    <div class="cd-item">
                        <span class="cd-number" id="cd-mins">00</span>
                        <span class="cd-label">MINS</span>
                    </div>
                    <div class="cd-item">
                        <span class="cd-number" id="cd-segs">00</span>
                        <span class="cd-label">SEGS</span>
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
                                <label>1. Proceso detectado</label>
                                <input type="text" class="form-select locked-field" value="<?php echo htmlspecialchars($proceso_subida); ?>" readonly>
                                <input type="hidden" name="cuatrimestre" id="cuatrimestre" value="<?php echo htmlspecialchars($proceso_subida); ?>">
                                <select class="is-hidden" aria-hidden="true">
                                    <option value="">-- Seleccione una opción --</option>
                                    <?php if (!$yaSubioTSU): ?><option value="6º cuatrimestre (Técnico Superior Universitario)">6º cuatrimestre (Técnico Superior Universitario)</option><?php endif; ?>
                                    <?php if (!$yaSubioING): ?><option value="10º cuatrimestre (Ingeniería/Licenciatura)">10º cuatrimestre (Ingeniería/Licenciatura)</option><?php endif; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>2. Carrera detectada</label>
                                <input type="text" class="form-select locked-field" value="<?php echo htmlspecialchars($programa_educativo_alumno ?: 'Carrera pendiente de asignar'); ?>" readonly>
                                <input type="hidden" id="carrera" name="programa_educativo" value="<?php echo htmlspecialchars($programa_educativo_alumno); ?>">
                                <?php if (!empty($cuatrimestre_alumno)): ?><span class="example-text">Periodo registrado: <?php echo htmlspecialchars($cuatrimestre_alumno); ?></span><?php endif; ?>
                                <select class="is-hidden" aria-hidden="true">
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
                                <span class="file-submsg">Límite establecido: 30MB</span>
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
                        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"></path></svg><b>Nomenclatura Requerida:</b> Es imperativo nombrar el archivo correctamente antes de la carga institucional.</li>
                        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"></path></svg><b>Documento Definitivo:</b> Asegúrate de cargar la versión final con firmas. No se permiten modificaciones posteriores sin autorización.</li>
                    </ul>
                </div>

            </div>
        <?php endif; ?>

        <footer class="dashboard-footer" style="margin-top: auto;">
            <div class="footer-bottom">
            PROYECTA • INNOVA • ALCANZA<br><br>
            © <span id="currentYear"></span>. Universidad Tecnológica de Mineral de la Reforma. Todos los derechos reservados.
            </div>
        </footer>

    </main>

    <div class="modal-overlay notification-modal <?php echo ($notificaciones_sin_leer > 0) ? 'active' : ''; ?>" id="notificationsModal">
        <div class="modal-box notification-modal-box">
            <div class="notif-modal-header">
                <h3>Historial de Notificaciones</h3>
                <button type="button" class="notif-close" id="btnCloseNotifications" title="Cerrar" onclick="document.getElementById('notificationsModal').classList.remove('active');">x</button>
            </div>
            
            <div class="notif-modal-list" id="modalListContainer">
                <?php 
                $grouped_notifs = [];
                if (!empty($notificaciones_alumno)) {
                    foreach($notificaciones_alumno as $notif) {
                        $fecha = date("d/m/Y", strtotime($notif['fecha_creacion']));
                        $grouped_notifs[$fecha][] = $notif;
                    }
                }
                ?>
                <?php if (!empty($grouped_notifs)): ?>
                    <?php foreach ($grouped_notifs as $fecha => $notifs_dia): ?>
                        <div class="notif-date-group">
                            <div class="notif-date-header"><?php echo htmlspecialchars($fecha); ?></div>
                            <?php foreach ($notifs_dia as $notificacion): ?>
                                <article class="notif-modal-item <?php echo ((int)$notificacion['leida'] === 0) ? 'notif-unread' : 'notif-read-state'; ?>" onclick="toggleNotif(<?php echo $notificacion['id_notificacion']; ?>, this)">
                                    <div class="notif-preview">
                                        <div style="flex:1;">
                                            <div class="notif-modal-meta">
                                                <span><?php echo htmlspecialchars(date("h:i A", strtotime($notificacion['fecha_creacion']))); ?></span>
                                                <span style="color:var(--utmir-naranja);"><?php echo htmlspecialchars($notificacion['tipo'] === 'eliminacion' ? 'Archivo rechazado' : 'Mensaje oficial'); ?></span>
                                            </div>
                                            <h4 style="margin:5px 0 0 0; color:var(--texto-principal); font-size:0.95rem;"><?php echo htmlspecialchars($notificacion['asunto']); ?></h4>
                                        </div>
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--texto-mutado);"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                    </div>
                                    <div class="notif-body">
                                        <?php if (!empty($notificacion['motivo'])): ?>
                                            <p><b>Motivo:</b> <?php echo htmlspecialchars($notificacion['motivo']); ?></p>
                                        <?php endif; ?>
                                        <p><?php echo nl2br(htmlspecialchars($notificacion['mensaje'])); ?></p>
                                        <?php if (!empty($notificacion['comentario'])): ?>
                                            <p><b>Observaciones:</b> <?php echo nl2br(htmlspecialchars($notificacion['comentario'])); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="message-text" style="color:var(--texto-mutado); text-align:center;">No tienes notificaciones en tu historial.</p>
                <?php endif; ?>
            </div>
            <button class="btn-ok" style="background:var(--bg-input); border:1px solid var(--borde-color); color:var(--texto-claro); margin-top:15px;" onclick="clearNotifHistory()">Limpiar todo el Historial</button>
        </div>
    </div>

    <div class="modal-overlay" id="loadingModal">
        <div class="modal-box">
            <svg class="anim-float modal-icon-spaced" width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="var(--utmir-verde)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 16 12 12 8 16"></polyline><line x1="12" y1="12" x2="12" y2="21"></line><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"></path><polyline points="16 16 12 12 8 16"></polyline></svg>
            <h3 class="modal-title-loading" style="color: white; margin-top:15px;">Procesando Documento...</h3>
            <p class="modal-text-muted" style="color: var(--texto-mutado);">Analizando texto y transfiriendo a la nube institucional.</p>
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
        // SCRIPT PARA MENÚ HAMBURGUESA MÓVIL ALUMNO
        const btnToggleSidebar = document.getElementById('btnToggleSidebarAlumno');
        const mainSidebar = document.getElementById('mainSidebarAlumno');
        const sidebarOverlay = document.getElementById('sidebarOverlayAlumno');

        function toggleSidebar() {
            const isActive = mainSidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
            
            btnToggleSidebar.innerHTML = isActive 
                ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>'
                : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>';
        }

        btnToggleSidebar?.addEventListener('click', toggleSidebar);
        sidebarOverlay?.addEventListener('click', toggleSidebar);

        // --- FIX SUBIDA DE ARCHIVOS ---
        // Intercepta el formulario para evitar que la página se reinicie y lo envía por fetch
        document.getElementById('uploadForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = this.querySelector('.btn-submit');
            btn.innerText = 'Subiendo...';
            btn.disabled = true;
            document.getElementById('loadingModal').classList.add('active'); // Mostrar modal de carga
            
            try {
                const formData = new FormData(this);
                const response = await fetch('api/upload.php', { method: 'POST', body: formData });
                const data = await response.json();
                
                document.getElementById('loadingModal').classList.remove('active');
                
                if(data.status === 'success') {
                    alert('Documento procesado y guardado correctamente.');
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.message || 'No se pudo procesar la entrega.'));
                    btn.innerText = 'Procesar Entrega';
                    btn.disabled = false;
                }
            } catch(err) {
                document.getElementById('loadingModal').classList.remove('active');
                alert('Error crítico de conexión al servidor.');
                btn.innerText = 'Procesar Entrega';
                btn.disabled = false;
            }
        });

        // --- FIX NOTIFICACIONES MÓVILES ---
        function toggleNotif(id, element) {
            const body = element.querySelector('.notif-body');
            body.classList.toggle('expanded');
            
            if(element.classList.contains('notif-unread')) {
                fetch('api/marcar_notificaciones.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'accion=marcar_una&id_notificacion=' + id
                }).then(() => {
                    element.classList.remove('notif-unread');
                    element.classList.add('notif-read-state');
                    const badge = document.getElementById('inPanelBadge');
                    if (badge) badge.style.display = 'none';
                });
            }
        }

        function clearNotifHistory() {
            if(confirm('¿Estás seguro de que deseas eliminar permanentemente todo tu historial?')) {
                fetch('api/marcar_notificaciones.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'accion=limpiar'
                }).then(() => {
                    document.getElementById('modalListContainer').innerHTML = '<p class="message-text" style="color:var(--texto-mutado); text-align:center;">Historial limpio. No tienes notificaciones pendientes.</p>';
                    document.getElementById('previewNotifContainer').innerHTML = `
                        <div class="notif-item">
                            <span class="notif-date">Mensaje Automático - Sistema Activo</span>
                            Mantente al tanto. Tu historial ha sido limpiado.
                        </div>`;
                });
            }
        }
    </script>
</body>
</html>
