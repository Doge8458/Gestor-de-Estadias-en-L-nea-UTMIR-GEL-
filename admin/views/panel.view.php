<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - UTMIR</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
        <link rel="stylesheet" href="../assets/css/admin-panel.css">

</head>
<body>

    <aside class="sidebar">
        <div class="brand-logo"><h1>UTMIR</h1><span>Panel Administrador</span></div>
        <div class="profile-card"><div class="profile-avatar">AD</div><h2>Administrador</h2><div class="matricula-badge">Depto. Vinculación</div></div>
        <div class="theme-switch-wrapper">
            <label class="theme-switch" for="checkbox-pc">
                <input type="checkbox" id="checkbox-pc" class="theme-checkbox" />
                <div class="slider round">
                    <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
                    <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
                </div>
            </label>
        </div>
        <a href="../api/logout_admin.php" class="btn-logout" title="Cerrar Sesión">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
            <span>Cerrar Sesión</span>
        </a>
        

    </aside>

    <main class="main-content">
        
        <div class="hero-banner">
            <div>
                <h1 class="hero-title">Gestión de Expedientes</h1>
                <p class="hero-subtitle">Visualiza, busca y administra los documentos oficiales subidos por los estudiantes.</p>
            </div>
        </div>

        <div class="periodo-modulo">
            <div class="periodo-info-wrapper">
                <div class="periodo-info">
                    <h3><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--utmir-naranja)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg> 
                    Programador de Periodo</h3>
                    <p>Establece el rango de fechas en el que la plataforma permitirá la recepción de memorias. El sistema bloqueará las entregas automáticamente al concluir este tiempo.</p>
                </div>
                <div class="periodo-actions">
                    <div class="countdown-box">
                        <div class="cd-item"><span class="cd-number" id="cd-dias">00</span><span class="cd-label">Días</span></div>
                        <div class="cd-item"><span class="cd-number" id="cd-horas">00</span><span class="cd-label">Horas</span></div>
                        <div class="cd-item"><span class="cd-number" id="cd-mins">00</span><span class="cd-label">Mins</span></div>
                        <div class="cd-item"><span class="cd-number" id="cd-segs">00</span><span class="cd-label">Segs</span></div>
                    </div>
                    <button class="btn-action-small btn-action-orange periodo-btn" id="btnAbrirModalCalendario">Modificar Fechas</button>
                </div>
            </div>
            
            <div class="calendario-wrapper">
                <input type="text" id="calendario_admin_preview" class="hidden-input">
            </div>
        </div>

        <div class="search-container">
            <form action="" method="GET" class="search-form">
                <input type="text" name="q" class="search-input" placeholder="Buscar por Matrícula o Nombre del Alumno..." value="<?php echo htmlspecialchars($busqueda); ?>">
                <button type="submit" class="btn-search">Buscar</button>
                <?php if(!empty($busqueda)): ?>
                    <a href="panel.php" class="btn-search btn-search-clear">Limpiar</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="admin-message-card">
            <div class="admin-message-copy">
                <h3>Centro de mensajes para alumnos</h3>
                <p>Busca al alumno por matricula y envia un aviso directo a su panel.</p>
            </div>
            <form id="formEnviarAviso" class="admin-message-form">
                <div class="form-row-admin">
                    <label>
                        Matricula escolar
                        <input type="number" name="matricula" class="admin-input" placeholder="Ej. 2403322" required>
                    </label>
                    <label>
                        Asunto
                        <input type="text" name="asunto" class="admin-input" maxlength="150" placeholder="Asunto del mensaje" required>
                    </label>
                </div>
                <label>
                    Motivo
                    <input type="text" name="motivo" class="admin-input" maxlength="150" placeholder="Opcional">
                </label>
                <label>
                    Texto del mensaje
                    <textarea name="mensaje" class="admin-textarea" rows="4" placeholder="Escribe el aviso que vera el alumno..." required></textarea>
                </label>
                <div class="admin-message-actions">
                    <span id="avisoAdminStatus" class="admin-form-status"></span>
                    <button type="submit" class="btn-action-small btn-action-orange">Enviar mensaje</button>
                </div>
            </form>
        </div>

        <div class="data-table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th># Matrícula</th>
                        <th>Nombre Alumno</th>
                        <th>Estado</th>
                        <th>Fecha / Proceso</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado->num_rows > 0): ?>
                        <?php while ($fila = $resultado->fetch_assoc()): ?>
                            <?php 
                                $esPendiente = empty($fila['id_entrega']);
                                $uniqueRowId = $esPendiente ? 'pend_' . htmlspecialchars($fila['matricula']) : 'ent_' . htmlspecialchars($fila['id_entrega']);
                                
                                $cuatrimestreRaw = htmlspecialchars($fila['cuatrimestre_subido'] ?? '');
                                $partes = explode('(', $cuatrimestreRaw);
                                $cuatrimestreStr = trim($partes[0]);
                                
                                $nivelStr = 'Técnico Superior Universitario (TSU)'; // Por defecto
                                $nivelCortado = isset($partes[1]) ? strtoupper(trim(str_replace(')', '', $partes[1]))) : '';
                                $programaUpper = strtoupper($fila['programa_educativo_subido'] ?? '');
                                
                                if (strpos($nivelCortado, 'I') === 0 || strpos($programaUpper, 'ING') !== false) {
                                    $nivelStr = 'Ingeniería';
                                } elseif (strpos($nivelCortado, 'L') === 0 || strpos($programaUpper, 'LIC') !== false) {
                                    $nivelStr = 'Licenciatura';
                                } elseif (strpos($nivelCortado, 'TS') === 0 || strpos($programaUpper, 'TSU') !== false) {
                                    $nivelStr = 'Técnico Superior Universitario (TSU)';
                                }
                            ?>
                            
                            <tr class="main-row" id="row-<?php echo $uniqueRowId; ?>" data-detail-target="details-<?php echo $uniqueRowId; ?>">
                                <td><span class="badge-matricula"><?php echo htmlspecialchars($fila['matricula']); ?></span></td>
                                <td class="student-name-cell"><?php echo htmlspecialchars($fila['nombre_completo']); ?></td>
                                
                                <td>
                                    <?php if($esPendiente): ?>
                                        <span class="estado-badge estado-pendiente">Pendiente</span>
                                    <?php else: ?>
                                        <span class="estado-badge estado-completado">Completado</span>
                                    <?php endif; ?>
                                </td>

                                <td class="muted-cell">
                                    <?php 
                                        if($esPendiente) {
                                            echo "<span class='pending-text'>Aún no realiza la carga</span>";
                                        } else {
                                            echo date("d/m/Y h:i A", strtotime($fila['fecha_subida']));
                                        }
                                    ?>
                                </td>

                                <td>
                                    <button class="btn-action-small">Ver Detalle</button>
                                </td>
                            </tr>
                            
                            <tr id="details-<?php echo $uniqueRowId; ?>" class="detail-row">
                                <td colspan="5">
                                    <div class="detail-content">
                                        <?php if($esPendiente): ?>
                                            <div class="detail-item pending-detail">
                                                <svg class="pending-icon" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--peligro)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                                <strong class="pending-title">Proceso Pendiente</strong>
                                                <p class="pending-description">El alumno <b><?php echo htmlspecialchars($fila['nombre_completo']); ?></b> aún no ha completado el formulario ni ha subido su memoria técnica a la plataforma GEL.</p>
                                            </div>
                                        <?php else: ?>
                                            <div class="detail-info-group">
                                                <div class="detail-item"><strong>CUATRIMESTRE</strong><?php echo $cuatrimestreStr; ?></div>
                                                <div class="detail-item"><strong>NIVEL</strong><?php echo $nivelStr; ?></div>
                                                <div class="detail-item"><strong>MODELO EDUCATIVO</strong><?php echo htmlspecialchars($fila['programa_educativo_subido']); ?></div>
                                                <div class="detail-item"><strong>NOMBRE DEL ARCHIVO</strong><a href="<?php echo htmlspecialchars($fila['link_google_drive']); ?>" target="_blank" class="file-link"><?php echo htmlspecialchars($fila['nombre_archivo_subido']); ?></a></div>
                                            </div>
                                            <div class="detail-actions">
                                                <a href="<?php echo htmlspecialchars($fila['link_google_drive']); ?>" target="_blank" class="btn-action-small btn-action-orange detail-action">Abrir PDF</a>
                                                <button class="btn-action-small btn-action-delete detail-action btn-delete-entrega" data-id-entrega="<?php echo $fila['id_entrega']; ?>" data-matricula="<?php echo htmlspecialchars($fila['matricula']); ?>">Eliminar</button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="11" y1="8" x2="11" y2="14"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
                                    <h3>No se encontraron registros</h3>
                                    <p class="empty-text">No hay documentos que coincidan con la búsqueda actual o el sistema está vacío.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- ======================================================== -->
        <!-- MÓDULO DE HABILITACIÓN MASIVA (.TXT) -->
        <!-- ======================================================== -->
        <div class="txt-module-card">
            <h3 class="txt-module-title">1. Subir lista de aprobados (.txt)</h3>
            <form id="formCargaTxt">
                <div class="txt-dropzone">
                    <span id="fileNameDisplay" class="txt-file-name">Haz clic o arrastra tu archivo .txt aquí</span>
                    <input type="file" name="archivo_txt" id="archivo_txt" accept=".txt" required class="txt-file-input">
                </div>
                <button type="submit" class="txt-btn-submit">Leer Matrículas</button>
            </form>
        </div>

        <!-- TABLA DE RESULTADOS (Oculta por defecto) -->
        <div id="panelResultados" class="txt-results-panel">
            <h3 class="txt-module-title">2. Alumnos Encontrados</h3>
            <table class="txt-results-table">
                <thead>
                    <tr class="txt-results-header">
                        <th class="txt-cell"><input type="checkbox" id="chkTodos"></th>
                        <th class="txt-cell">Matrícula</th>
                        <th class="txt-cell">Nombre Completo</th>
                        <th class="txt-cell">Estatus Actual</th>
                    </tr>
                </thead>
                <tbody id="cuerpoTabla">
                    <!-- JS llenará esto -->
                </tbody>
            </table>
            <div class="txt-enable-toolbar">
                <button class="txt-btn-enable" id="btnHabilitarSeleccionados">Habilitar Alumnos Seleccionados</button>
            </div>
        </div>
        <!-- ======================================================== -->
        <footer class="dashboard-footer">
            <div class="footer-bottom">
            PROYECTA • INNOVA • ALCANZA<br><br>
            © <span id="currentYear"></span>. Universidad Tecnológica de Mineral de la Reforma. Todos los derechos reservados.
            </div>
        </footer>
    </main>

    <div class="modal-overlay" id="modalCalendario">
        <div class="modal-box modal-box-wide">
            <h3 class="modal-title modal-title-green">Programar Periodo de Recepción</h3>
            <p class="modal-text">Selecciona los días hábiles en los que los alumnos podrán subir sus memorias a la plataforma. Fuera de esta fecha, el sistema se bloqueará.</p>
            
            <form action="" method="POST">
                <input type="text" name="rango_fechas" id="rango_fechas" placeholder="Selecciona Inicio y Fin..." class="date-range-input" required readonly>
                
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" data-modal-close="modalCalendario">Cancelar</button>
                    <button type="submit" class="btn-ok">Guardar Periodo</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="modalConfirmacion">
        <div class="modal-box modal-delete-box">
            <div class="modal-icon icon-warning"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg></div>
            <h3 class="modal-title">¿Eliminar Registro?</h3>
            <p class="modal-text">Estás a punto de eliminar la entrega del alumno <b id="modalMatriculaTexto" class="modal-highlight"></b>.</p>
            <div class="delete-reason-form">
                <label>
                    Motivo obligatorio
                    <select id="motivoEliminacion" class="admin-input" required>
                        <option value="">Selecciona una opcion</option>
                        <option value="paginas_no_enumeradas">Paginas no enumeradas</option>
                        <option value="problemas_indice">Problemas con el indice</option>
                        <option value="indice_no_coincide">El indice no coincide con las paginas</option>
                        <option value="falta_documento_autorizacion">Falta del documento de autorizacion</option>
                        <option value="documento_autorizacion_no_agregado">Documento de autorizacion no agregado</option>
                        <option value="orden_paginas">Orden de las paginas</option>
                        <option value="otro">Otro</option>
                    </select>
                </label>
                <label id="grupoMotivoOtro" class="is-hidden">
                    Motivo personalizado
                    <input type="text" id="motivoOtro" class="admin-input" maxlength="150" placeholder="Escribe el motivo">
                </label>
                <label>
                    Comentario adicional
                    <textarea id="comentarioEliminacion" class="admin-textarea" rows="3" placeholder="Opcional para motivos comunes"></textarea>
                </label>
                <label id="grupoDetalleOtro" class="is-hidden">
                    Explicacion del problema
                    <textarea id="detalleOtro" class="admin-textarea" rows="3" placeholder="Obligatorio cuando el motivo es Otro"></textarea>
                </label>
                <span id="deleteAdminStatus" class="admin-form-status admin-form-status-error"></span>
            </div>
            <div class="modal-actions">
                <button class="btn-cancel" data-modal-close="modalConfirmacion">Cancelar</button>
                <button class="btn-confirm" id="btnConfirmarEliminacion">Sí, Eliminar</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>

    <script type="application/json" id="admin-panel-data">
        <?php echo json_encode([
            'fechaInicio' => isset($periodo_actual['fecha_inicio']) ? $periodo_actual['fecha_inicio'] : '',
            'fechaFin' => isset($periodo_actual['fecha_fin']) ? $periodo_actual['fecha_fin'] : ''
        ]); ?>
    </script>

    <script src="../assets/js/admin-panel-main.js"></script>

        <script src="../assets/js/admin-panel-txt.js"></script>

</body>
</html>
