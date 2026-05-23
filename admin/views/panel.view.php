<?php
// Agrupación de Resultados y Motor de Búsqueda
$alumnos_agrupados = [];
$busqueda_activa = isset($_GET['q']) ? trim($_GET['q']) : '';

// Función para resaltar resultados con animación amarilla
function resaltaBusqueda($texto, $busqueda) {
    if (empty($busqueda)) return htmlspecialchars($texto);
    $pattern = '/' . preg_quote($busqueda, '/') . '/i';
    return preg_replace($pattern, '<mark class="highlight-anim">$0</mark>', htmlspecialchars($texto));
}

// Convertir nomenclatura antigua a la oficial
function formatearCuatrimestre($cuatrimestre) {
    if (strpos($cuatrimestre, '6to') !== false || strpos($cuatrimestre, '6º') !== false) {
        return '6º cuatrimestre (Técnico Superior Universitario)';
    } elseif (strpos($cuatrimestre, '11vo') !== false || strpos($cuatrimestre, '10º') !== false) {
        return '10º cuatrimestre (Ingeniería/Licenciatura)';
    }
    return htmlspecialchars($cuatrimestre);
}

if (isset($resultado) && $resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $mat = $fila['matricula'];
        if (!isset($alumnos_agrupados[$mat])) {
            $alumnos_agrupados[$mat] = [
                'matricula' => $mat,
                'nombre_completo' => $fila['nombre_completo'],
                'entregas' => []
            ];
        }
        if (!empty($fila['id_entrega'])) {
            $alumnos_agrupados[$mat]['entregas'][] = $fila;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - UTMIR</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="../assets/css/admin-panel.css">
    <style>
        /* DISEÑO DE MENÚ INSTITUCIONAL SÓLIDO (Sin Glassmorphism) */
        :root { --sidebar-width: 280px; --navbar-height: 70px; }
        
        .sidebar {
            position: fixed; top: 0; left: 0; width: var(--sidebar-width); height: 100vh;
            display: flex; flex-direction: column; overflow: hidden; z-index: 1000;
            background: var(--bg-sidebar, #111827); border-right: 1px solid var(--borde-color, #374151);
            transition: width 0.3s ease, height 0.3s ease;
        }
        .main-content {
            margin-left: var(--sidebar-width);
            transition: margin 0.3s ease, padding 0.3s ease;
        }
        .sidebar-actions { margin-top: auto; display: flex; flex-direction: column; align-items: center; gap: 20px; width: 100%; transition: all 0.3s ease; padding-bottom: 20px; }
        .profile-card { transition: opacity 0.2s ease; opacity: 1; }
        
        /* Estado Scrolled: Barra superior sólida y formal */
        body.is-scrolled .sidebar {
            width: 100%; height: var(--navbar-height);
            background: #0f172a; /* Azul marino institucional oscuro */
            border-right: none; border-bottom: 3px solid #00a859; /* Identidad UTMiR */
            flex-direction: row; justify-content: space-between; align-items: center;
            padding: 0 40px; box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        body.is-scrolled .main-content { margin-left: 0; padding-top: var(--navbar-height); }
        body.is-scrolled .profile-card { display: none; }
        body.is-scrolled .brand-logo { flex-direction: row; align-items: center; margin: 0; padding: 0; }
        body.is-scrolled .brand-logo h1 { color: #00a859; font-size: 1.5rem; margin: 0 10px 0 0; }
        body.is-scrolled .brand-logo span { color: #ffffff; font-weight: 600; font-size: 1.1rem; }
        body.is-scrolled .sidebar-actions { flex-direction: row; margin-top: 0; padding-bottom: 0; width: auto; gap: 20px; }
        body.is-scrolled .btn-logout { padding: 8px 20px; background-color: #e67e22; border-radius: 4px; }
        body.is-scrolled .btn-logout:hover { background-color: #d67118; }
        
        /* HOVER NARANJA PARA ARCHIVOS */
        a.file-link { transition: all 0.2s ease; color: #00a859; font-weight: 600; text-decoration: none; }
        a.file-link:hover { color: #e67e22 !important; text-decoration: underline !important; }
        
        /* ANIMACIÓN DE RESALTADO DE BÚSQUEDA (ESTILO MARCADOR AMARILLO) */
        mark.highlight-anim {
            background: linear-gradient(to right, rgba(241, 196, 15, 0) 50%, rgba(241, 196, 15, 0.8) 50%);
            background-size: 200% 100%; background-position: 100% 0;
            animation: highlight-slide 0.6s cubic-bezier(0.25, 1, 0.5, 1) forwards;
            color: #000; padding: 0 4px; border-radius: 2px; font-weight: bold; box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        @keyframes highlight-slide { to { background-position: 0 0; } }
        
        .admin-input, .admin-textarea { color: var(--texto-principal, #fff); background: var(--bg-input, #1f2937); border: 1px solid var(--borde-color, #374151); }
        .detail-row { display: none; } /* Oculto por defecto estrictamente */
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="brand-logo"><h1>UTMIR</h1><span>Panel Administrador</span></div>
        <div class="profile-card"><div class="profile-avatar">AD</div><h2>Administrador</h2><div class="matricula-badge">Depto. Vinculación</div></div>
        
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
            <a href="../api/logout_admin.php" class="btn-logout">Cerrar Sesión</a>
        </div>
    </aside>

    <main class="main-content">
        
        <div class="hero-banner" style="text-align: center; margin-bottom: 30px;">
            <h1 class="hero-title">Gestión de Expedientes</h1>
            <p class="hero-subtitle">Visualiza, busca y administra los documentos oficiales subidos por los estudiantes.</p>
        </div>
        
        <div class="top-row" style="display: flex; gap: 20px; align-items: stretch; margin-bottom: 20px; flex-wrap: wrap;">
            
            <div class="admin-message-card" style="flex: 2; min-width: 350px; margin-bottom: 0;">
                <div class="admin-message-copy" style="margin-bottom: 15px;">
                    <h3 style="display: flex; align-items: center; gap: 8px; font-size: 1.1rem; margin-bottom: 5px;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--utmir-naranja)" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                        Centro de mensajes rápidos
                    </h3>
                    <p style="font-size: 0.85rem; color: var(--texto-mutado);">Aviso directo al panel del alumno y a su correo institucional.</p>
                </div>
                <form id="formEnviarAviso" class="admin-message-form" style="display: flex; flex-direction: column; gap: 10px;">
                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <label style="flex: 1; min-width: 120px; color: var(--texto-principal);">Matrícula<input type="number" name="matricula" class="admin-input" required></label>
                        <label style="flex: 2; min-width: 180px; color: var(--texto-principal);">Asunto<input type="text" name="asunto" class="admin-input" maxlength="150" required></label>
                    </div>
                    <label style="color: var(--texto-principal);">Mensaje<textarea name="mensaje" class="admin-textarea" rows="3" required></textarea></label>
                    <div class="admin-message-actions" style="justify-content: space-between; align-items: center; display: flex;">
                        <span id="avisoAdminStatus" class="admin-form-status" style="margin: 0;"></span>
                        <button type="submit" class="btn-action-small btn-action-orange">Enviar</button>
                    </div>
                </form>
            </div>

            <div class="periodo-modulo" style="flex: 1; min-width: 250px; margin-bottom: 0; border-left: 4px solid #3498db; display: flex; flex-direction: column; justify-content: space-between;">
                <div class="periodo-info">
                    <h3 style="color: #3498db; display: flex; align-items: center; gap: 8px; font-size: 1rem;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#3498db" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                        Programador de Notificaciones
                    </h3>
                    <p style="margin-top: 10px; font-size: 0.85rem; color: var(--texto-mutado);">Define el periodo en el que el sistema enviará recordatorios masivos por correo.</p>
                </div>
                <div class="periodo-actions" style="margin-top: 20px;">
                    <button class="btn-action-small periodo-btn" style="background-color: #3498db; color: white; width: 100%;" id="btnModificarNotificaciones">Configurar Envíos</button>
                </div>
            </div>

        </div>

        <div class="periodo-modulo" style="margin-bottom: 20px;">
            <div class="periodo-info-wrapper">
                <div class="periodo-info">
                    <h3>Programador de Periodo de Recepción</h3>
                    <p>Establece el rango de fechas en el que la plataforma permitirá la carga de memorias.</p>
                </div>
                <div class="periodo-actions">
                    <div class="countdown-box">
                        <div class="cd-item"><span class="cd-number" id="cd-dias">00</span><span class="cd-label">DÍAS</span></div>
                        <div class="cd-item"><span class="cd-number" id="cd-horas">00</span><span class="cd-label">HORAS</span></div>
                        <div class="cd-item"><span class="cd-number" id="cd-mins">00</span><span class="cd-label">MINS</span></div>
                        <div class="cd-item"><span class="cd-number" id="cd-segs">00</span><span class="cd-label">SEGS</span></div>
                    </div>
                    <button class="btn-action-small btn-action-orange periodo-btn" id="btnAbrirModalCalendario">Modificar Fechas</button>
                </div>
            </div>
            <div class="calendario-wrapper"><input type="text" id="calendario_admin_preview" class="hidden-input"></div>
        </div>

        <div class="search-container">
            <form action="" method="GET" class="search-form">
                <input type="text" name="q" class="search-input" placeholder="Análisis profundo: Buscar por Matrícula, Nombre, Título de Proyecto o Palabras Clave..." value="<?php echo htmlspecialchars($busqueda_activa); ?>">
                <button type="submit" class="btn-search">Buscar</button>
                <?php if(!empty($busqueda_activa)): ?><a href="panel.php" class="btn-search btn-search-clear">Limpiar Filtro</a><?php endif; ?>
            </form>
        </div>

        <div class="data-table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th># Matrícula</th>
                        <th>Nombre Alumno</th>
                        <th>Estado General</th>
                        <th>Archivos</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($alumnos_agrupados)): ?>
                        <?php foreach ($alumnos_agrupados as $mat => $alumno): ?>
                            <?php 
                                $num_entregas = count($alumno['entregas']);
                                $uniqueRowId = 'alum_' . $mat;
                                $estado_texto = 'Pendiente';
                                $estado_clase = 'estado-pendiente';
                                
                                if ($num_entregas == 1) {
                                    $estado_texto = 'Parcial (1/2)';
                                    $estado_clase = 'estado-pendiente';
                                } elseif ($num_entregas >= 2) {
                                    $estado_texto = 'Completado';
                                    $estado_clase = 'estado-completado';
                                }
                            ?>
                            <tr class="main-row" id="row-<?php echo $uniqueRowId; ?>" data-detail-target="details-<?php echo $uniqueRowId; ?>">
                                <td><span class="badge-matricula"><?php echo resaltaBusqueda($mat, $busqueda_activa); ?></span></td>
                                <td class="student-name-cell"><?php echo resaltaBusqueda($alumno['nombre_completo'], $busqueda_activa); ?></td>
                                <td><span class="estado-badge <?php echo $estado_clase; ?>"><?php echo $estado_texto; ?></span></td>
                                <td class="muted-cell"><?php echo $num_entregas > 0 ? "Archivos subidos: $num_entregas" : 'Aún no realiza la carga'; ?></td>
                                <td><button class="btn-action-small">Ver Expediente</button></td>
                            </tr>
                            <tr id="details-<?php echo $uniqueRowId; ?>" class="detail-row">
                                <td colspan="5">
                                    <div class="detail-content" style="display: flex; flex-direction: column; gap: 15px; padding: 20px;">
                                        <?php if ($num_entregas == 0): ?>
                                            <div class="detail-item pending-detail">
                                                <strong class="pending-title">Proceso Pendiente</strong>
                                                <p class="pending-description">El alumno no ha subido memorias técnicas a la plataforma.</p>
                                            </div>
                                        <?php else: ?>
                                            <?php foreach ($alumno['entregas'] as $entrega): ?>
                                                <div class="detail-info-group" style="background: var(--bg-card); border: 1px solid var(--borde-color); border-left: 4px solid var(--utmir-naranja); border-radius: 6px; padding: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                                    
                                                    <div style="display: flex; flex: 1; flex-wrap: wrap; gap: 20px; align-items: flex-start;">
                                                        <div style="flex: 1; min-width: 250px;">
                                                            <span style="font-size: 0.75rem; color: var(--texto-mutado); text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 4px;">Proceso</span>
                                                            <strong style="font-size: 1rem; color: var(--texto-principal);"><?php echo formatearCuatrimestre($entrega['cuatrimestre_subido']); ?></strong>
                                                        </div>
                                                        <div style="flex: 1; min-width: 200px;">
                                                            <span style="font-size: 0.75rem; color: var(--texto-mutado); text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 4px;">Programa Educativo</span>
                                                            <strong style="font-size: 1rem; color: var(--texto-principal);"><?php echo resaltaBusqueda($entrega['programa_educativo_subido'], $busqueda_activa); ?></strong>
                                                        </div>
                                                        <div style="flex: 2; min-width: 250px;">
                                                            <span style="font-size: 0.75rem; color: var(--texto-mutado); text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 4px;">Archivo Registrado (Análisis de Proyecto)</span>
                                                            <a href="<?php echo htmlspecialchars($entrega['link_google_drive']); ?>" target="_blank" class="file-link" style="font-size: 1rem; display: flex; align-items: flex-start; gap: 6px;">
                                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; margin-top:2px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                                                                <span style="word-break: break-all;"><?php echo resaltaBusqueda($entrega['nombre_archivo_subido'], $busqueda_activa); ?></span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    
                                                    <div style="flex-shrink: 0;">
                                                        <button class="btn-action-small btn-action-delete detail-action btn-delete-entrega" style="margin: 0; padding: 10px 20px; font-size: 0.9rem;" data-id-entrega="<?php echo $entrega['id_entrega']; ?>" data-matricula="<?php echo htmlspecialchars($mat); ?>">Rechazar Documento</button>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5"><div class="empty-state"><h3>No se encontraron registros</h3><p>Verifica los términos de búsqueda.</p></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="txt-module-card">
            <h3 class="txt-module-title">1. Subir lista de alumnos (.xlsx)</h3>
            <form id="formCargaExcel">
                <div class="txt-dropzone">
                    <span id="fileNameDisplay" class="txt-file-name">Haz clic o arrastra tu archivo de Excel (.xlsx) aquí</span>
                    <input type="file" name="archivo_excel" id="archivo_excel" accept=".xlsx, .xls" required class="txt-file-input">
                </div>
                <button type="submit" class="txt-btn-submit">Leer Excel</button>
            </form>
            <span id="excelStatusOutput" style="display:block; margin-top:15px; text-align:center; font-weight:600; font-size:14px;"></span>
        </div>

        <div id="panelResultados" class="txt-results-panel">
            <h3 class="txt-module-title">2. Alumnos Encontrados</h3>
            <table class="txt-results-table">
                <thead>
                    <tr class="txt-results-header">
                        <th class="txt-cell"><input type="checkbox" id="chkTodos"></th>
                        <th class="txt-cell">Matrícula</th>
                        <th class="txt-cell">Nombre Completo</th>
                        <th class="txt-cell">Programa / Nivel</th>
                        <th class="txt-cell">Correo</th>
                        <th class="txt-cell">Estatus Actual</th>
                    </tr>
                </thead>
                <tbody id="cuerpoTabla"></tbody>
            </table>
            <div class="txt-enable-toolbar"><button class="txt-btn-enable" id="btnHabilitarSeleccionados">Habilitar Alumnos Seleccionados</button></div>
        </div>
        
        <footer class="dashboard-footer">
            <div class="footer-bottom">
            PROYECTA • INNOVA • ALCANZA<br><br>
            © <span id="currentYear"></span>. Universidad Tecnológica de Mineral de la Reforma. Todos los derechos reservados.
            </div>
        </footer>
    </main>

    <div class="modal-overlay" id="modalConfirmacion">
        <div class="modal-box modal-delete-box">
            <h3 class="modal-title">¿Rechazar Documento?</h3>
            <p class="modal-text">Estás a punto de rechazar y eliminar un archivo del alumno <b id="modalMatriculaTexto" class="modal-highlight"></b>.</p>
            <div class="delete-reason-form">
                <label>Motivo obligatorio
                    <select id="motivoEliminacion" class="admin-input" required>
                        <option value="">Selecciona una opcion</option>
                        <option value="paginas_no_enumeradas">Paginas no enumeradas</option>
                        <option value="falta_documento_autorizacion">Falta documento de autorizacion</option>
                        <option value="otro">Otro (Especificar)</option>
                    </select>
                </label>
                <label id="grupoMotivoOtro" class="is-hidden">Especificar<input type="text" id="motivoOtro" class="admin-input"></label>
                <label>Observaciones (Se enviarán por correo)<textarea id="comentarioEliminacion" class="admin-textarea" rows="3"></textarea></label>
                <span id="deleteAdminStatus" class="admin-form-status admin-form-status-error"></span>
            </div>
            <div class="modal-actions">
                <button class="btn-cancel" data-modal-close="modalConfirmacion">Cancelar</button>
                <button class="btn-confirm" id="btnConfirmarEliminacion">Sí, Eliminar</button>
            </div>
        </div>
    </div>
    
    <div class="modal-overlay" id="modalCalendario">
        <div class="modal-box modal-box-wide">
            <h3 class="modal-title modal-title-green">Programar Periodo de Recepción</h3>
            <form action="" method="POST">
                <input type="text" name="rango_fechas" id="rango_fechas" placeholder="Selecciona Inicio y Fin..." class="date-range-input" required readonly>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" data-modal-close="modalCalendario">Cancelar</button>
                    <button type="submit" class="btn-ok">Guardar Periodo</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="modalNotificaciones">
        <div class="modal-box modal-box-wide" style="border-top: 4px solid #3498db;">
            <h3 class="modal-title" style="color: #3498db;">Programar Periodo de Notificaciones</h3>
            <form id="formNotificaciones">
                <input type="text" name="rango_fechas_notif" id="rango_fechas_notif" placeholder="Selecciona Inicio y Fin..." class="date-range-input" required readonly>
                <div style="margin-top: 15px; font-weight: 600; text-align: center; font-size: 14px;" id="statusNotif"></div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" data-modal-close="modalNotificaciones">Cancelar</button>
                    <button type="submit" class="btn-ok" style="background-color: #3498db; color: white;">Guardar Periodo</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>

    <script type="application/json" id="admin-panel-data">
        <?php 
            $p_db = new mysqli("localhost", "root", "", "portal_estadias");
            $r_per = $p_db->query("SELECT fecha_inicio, fecha_fin FROM configuracion_periodo LIMIT 1")->fetch_assoc();
            $r_notif = $p_db->query("SELECT fecha_inicio, fecha_fin FROM configuracion_notificaciones LIMIT 1")->fetch_assoc();
            echo json_encode([
                'fechaInicio' => $r_per ? $r_per['fecha_inicio'] : '',
                'fechaFin' => $r_per ? $r_per['fecha_fin'] : '',
                'notifInicio' => $r_notif ? $r_notif['fecha_inicio'] : '',
                'notifFin' => $r_notif ? $r_notif['fecha_fin'] : ''
            ]); 
        ?>
    </script>
    <script src="../assets/js/admin-panel-main.js"></script>
    <script>
        // Diseño de transición sólida al hacer scroll
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) { document.body.classList.add('is-scrolled'); } 
            else { document.body.classList.remove('is-scrolled'); }
        });
    </script>
</body>
</html>