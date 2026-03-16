<?php
session_start();
// Redirige al index.html de la carpeta ADMIN si no hay sesión
if (!isset($_SESSION['admin_logged'])) { header("Location: index.html"); exit(); }

// Conexión a BD
$conexion = new mysqli("localhost", "root", "", "portal_estadias");

// Lógica del Buscador
$busqueda = "";
$sql = "SELECT e.id_entrega, e.nombre_archivo_subido, e.cuatrimestre_subido, e.programa_educativo_subido, e.link_google_drive, e.fecha_subida, a.nombre_completo, a.matricula 
        FROM entregas e 
        JOIN alumnos a ON e.matricula_alumno = a.matricula";

if (isset($_GET['q']) && !empty($_GET['q'])) {
    $busqueda = $conexion->real_escape_string($_GET['q']);
    $sql .= " WHERE a.matricula LIKE '%$busqueda%' OR a.nombre_completo LIKE '%$busqueda%'";
}

$sql .= " ORDER BY e.fecha_subida DESC";
$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - UTMIR</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            /* Paleta Dark Institucional con Fondo Verde Oscuro (Afecta contenido principal) */
            --bg-profundo: #022919; 
            --bg-card: #141414;
            --bg-input: #0a0a0a;
            --utmir-guinda: #801336;
            --utmir-verde: #00a86b; 
            --blanco: #ffffff;
            --texto-claro: #e2e8f0;
            --texto-mutado: #94a3b8;
            --borde-sutil: rgba(255, 255, 255, 0.08);
            --peligro: #ef4444;
        }

        /* Paleta Light Theme (Afecta contenido principal) */
        [data-theme="light"] {
            --bg-profundo: #f3f4f6;
            --bg-card: #ffffff;
            --bg-input: #f8fafc;
            --blanco: #111827;
            --texto-claro: #334155;
            --texto-mutado: #64748b;
            --borde-sutil: rgba(0, 0, 0, 0.1);
            --utmir-guinda: #801336;
            --utmir-verde: #00a86b;
            --peligro: #dc2626;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            margin: 0; padding: 0; min-height: 100vh;
            background-color: var(--bg-profundo);
            color: var(--texto-claro);
            display: flex; 
            overflow-x: hidden;
            transition: background-color 0.4s ease, color 0.4s ease;
        }

        /* =========================================
           SIDEBAR INSTITUCIONAL (SIEMPRE OSCURA)
           ========================================= */
        .sidebar {
            width: 280px; 
            background: #141414; /* Fondo oscuro forzado */
            border-right: 1px solid rgba(255, 255, 255, 0.08);
            color: #ffffff; /* Texto siempre blanco */
            padding: 40px 20px; display: flex; flex-direction: column;
            position: fixed; height: 100vh; top: 0; left: 0; box-sizing: border-box; z-index: 100;
        }

        .brand-logo { text-align: center; margin-bottom: 40px; }
        .brand-logo h1 { font-size: 26px; font-weight: 800; margin: 0; letter-spacing: 1px; color: #ffffff; }
        .brand-logo span { font-size: 12px; letter-spacing: 2px; text-transform: uppercase; color: var(--utmir-verde); font-weight: 600; }

        .profile-card {
            background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px; padding: 25px 15px; text-align: center; margin-bottom: auto;
        }
        .profile-avatar {
            width: 60px; height: 60px; background: var(--utmir-guinda); border-radius: 50%; 
            margin: 0 auto 15px auto; display: flex; justify-content: center; align-items: center;
            font-size: 24px; color: #ffffff; font-weight: 800;
        }
        .profile-card h2 { font-size: 16px; margin: 0 0 10px 0; font-weight: 600; color: #ffffff; }
        .matricula-badge { 
            display: inline-block; background: rgba(0, 168, 107, 0.15); color: var(--utmir-verde);
            padding: 5px 12px; border-radius: 20px; font-family: monospace; font-size: 13px; font-weight: 700;
            border: 1px solid rgba(0, 168, 107, 0.3);
        }

        /* Toggle Switch Independiente para la Sidebar */
        .theme-switch-wrapper { display: flex; align-items: center; justify-content: center; margin-bottom: 20px; }
        .theme-switch { position: relative; display: inline-block; width: 64px; height: 32px; }
        .theme-switch input { display: none; }
        .slider.round {
            position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
            background-color: rgba(255,255,255,0.1); transition: .4s; border-radius: 34px; border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .slider.round:before {
            position: absolute; content: ""; height: 24px; width: 24px; left: 4px; bottom: 3px;
            background-color: #ffffff; transition: .4s; border-radius: 50%; z-index: 2;
        }
        input:checked + .slider.round { background-color: var(--utmir-verde); }
        input:checked + .slider.round:before { transform: translateX(30px); }
        .icon-moon, .icon-sun { position: absolute; top: 7px; width: 18px; height: 18px; z-index: 1; transition: .4s; }
        .icon-moon { right: 8px; color: #ffffff; }
        .icon-sun { left: 8px; color: #ffffff; opacity: 0; }
        input:checked + .slider.round .icon-moon { opacity: 0; }
        input:checked + .slider.round .icon-sun { opacity: 1; }

        .btn-logout {
            background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.08);
            color: #ffffff; text-decoration: none; padding: 15px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center; gap: 10px; font-weight: 600; transition: 0.3s;
        }
        .btn-logout:hover { background: var(--utmir-guinda); border-color: var(--utmir-guinda); color: #ffffff; }

        /* =========================================
           CONTENIDO PRINCIPAL ADMIN (CAMBIA DE TEMA)
           ========================================= */
        .main-content {
            flex: 1; margin-left: 280px; padding: 40px; box-sizing: border-box;
            display: flex; flex-direction: column; gap: 30px; max-width: 1400px; min-height: 100vh;
        }

        .hero-banner {
            background: var(--bg-card); border-radius: 16px; padding: 35px;
            border-left: 6px solid var(--utmir-verde); border-top: 1px solid var(--borde-sutil); 
            border-right: 1px solid var(--borde-sutil); border-bottom: 1px solid var(--borde-sutil);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: background-color 0.4s ease, border-color 0.4s ease, box-shadow 0.4s ease;
        }
        .hero-title { font-size: 26px; color: var(--blanco); margin: 0 0 10px 0; font-weight: 800; }
        .hero-subtitle { color: var(--texto-mutado); font-size: 15px; margin: 0; line-height: 1.6; }

        /* Buscador */
        .search-container {
            background: var(--bg-card); border: 1px solid var(--borde-sutil); padding: 25px; 
            border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: background-color 0.4s ease, border-color 0.4s ease;
        }
        .search-form { display: flex; gap: 15px; }
        .search-input {
            flex: 1; padding: 15px 20px; font-family: inherit; font-size: 15px;
            background: var(--bg-input); border: 1px solid var(--borde-sutil); border-radius: 8px;
            color: var(--blanco); outline: none; transition: 0.3s;
        }
        .search-input:focus { border-color: var(--utmir-verde); box-shadow: 0 0 0 3px rgba(0, 168, 107, 0.15); }
        .btn-search {
            background: var(--utmir-verde); color: #ffffff; border: none; padding: 0 30px;
            border-radius: 8px; font-weight: 700; cursor: pointer; transition: 0.3s;
        }
        .btn-search:hover { filter: brightness(1.1); transform: translateY(-2px); }

        /* Grid de Entregas */
        .submissions-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; }
        
        .submission-card {
            background: var(--bg-card); border: 1px solid var(--borde-sutil); border-radius: 12px;
            padding: 25px; transition: 0.3s; position: relative; display: flex; flex-direction: column;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .submission-card:hover { transform: translateY(-5px); border-color: var(--utmir-verde); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        
        .card-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; border-bottom: 1px solid var(--borde-sutil); padding-bottom: 15px; }
        .student-info h3 { margin: 0 0 5px 0; font-size: 16px; color: var(--blanco); font-weight: 700; }
        .matricula { background: rgba(128,128,128,0.1); padding: 4px 10px; border-radius: 6px; font-size: 12px; font-family: monospace; font-weight: 600; color: var(--texto-mutado); }
        
        .card-body { flex: 1; font-size: 13px; color: var(--texto-claro); line-height: 1.6; }
        .card-body p { margin: 0 0 10px 0; }
        .label { font-weight: 600; color: var(--texto-mutado); display: block; font-size: 11px; text-transform: uppercase; margin-bottom: 2px; }
        .value { color: var(--blanco); font-weight: 500; }
        
        .card-actions { display: grid; grid-template-columns: 1fr auto; gap: 10px; margin-top: 20px; padding-top: 15px; border-top: 1px dashed var(--borde-sutil); }
        .btn-view {
            background: rgba(0, 168, 107, 0.1); color: var(--utmir-verde); text-decoration: none;
            padding: 10px; border-radius: 6px; font-size: 13px; font-weight: 600; text-align: center;
            border: 1px solid rgba(0, 168, 107, 0.2); transition: 0.3s;
        }
        .btn-view:hover { background: var(--utmir-verde); color: #ffffff; }
        
        .btn-delete {
            background: rgba(239, 68, 68, 0.1); color: var(--peligro); border: 1px solid rgba(239, 68, 68, 0.2);
            padding: 10px 15px; border-radius: 6px; cursor: pointer; transition: 0.3s; display: flex; justify-content: center; align-items: center;
        }
        .btn-delete:hover { background: var(--peligro); color: #ffffff; }

        .empty-state { grid-column: 1 / -1; text-align: center; padding: 60px 20px; background: var(--bg-card); border-radius: 16px; border: 1px dashed var(--borde-sutil); }
        .empty-state svg { color: var(--texto-mutado); margin-bottom: 15px; opacity: 0.5; }
        .empty-state h3 { color: var(--blanco); margin-bottom: 5px; }

        /* Footer */
        .dashboard-footer { margin-top: auto; padding-top: 30px; border-top: 1px solid var(--borde-sutil); text-align: center; color: var(--texto-mutado); font-size: 13px; }

        /* Modales */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.8); backdrop-filter: blur(5px); display: flex; justify-content: center; align-items: center; z-index: 1000; opacity: 0; pointer-events: none; transition: 0.3s; }
        .modal-overlay.active { opacity: 1; pointer-events: auto; }
        .modal-box { background: var(--bg-card); border: 1px solid var(--borde-sutil); border-radius: 16px; padding: 40px; width: 90%; max-width: 400px; text-align: center; transform: translateY(20px); transition: 0.3s; box-shadow: 0 25px 50px rgba(0,0,0,0.5); }
        .modal-overlay.active .modal-box { transform: translateY(0); }
        
        .modal-icon { display: inline-flex; justify-content: center; align-items: center; width: 60px; height: 60px; border-radius: 50%; margin-bottom: 20px; }
        .icon-warning { background: rgba(239, 68, 68, 0.1); color: var(--peligro); }
        .icon-success { background: rgba(0, 168, 107, 0.1); color: var(--utmir-verde); }
        
        .modal-title { color: var(--blanco); font-size: 20px; margin: 0 0 10px 0; }
        .modal-text { color: var(--texto-claro); font-size: 14px; margin-bottom: 25px; line-height: 1.5; }
        
        .modal-actions { display: flex; gap: 10px; }
        .btn-cancel { flex: 1; background: var(--bg-input); border: 1px solid var(--borde-sutil); color: var(--blanco); padding: 12px; border-radius: 8px; cursor: pointer; font-weight: 600; transition: 0.3s; }
        .btn-cancel:hover { background: rgba(255,255,255,0.1); }
        .btn-confirm { flex: 1; background: var(--peligro); border: none; color: #ffffff; padding: 12px; border-radius: 8px; cursor: pointer; font-weight: 600; transition: 0.3s; }
        .btn-confirm:hover { filter: brightness(1.2); }
        .btn-ok { width: 100%; background: var(--utmir-verde); border: none; color: #ffffff; padding: 12px; border-radius: 8px; cursor: pointer; font-weight: 600; transition: 0.3s; }

        /* Responsive Móvil */
        .mobile-actions { display: none; }
        @media (max-width: 1000px) { 
            body { flex-direction: column; }
            .sidebar { 
                width: 100%; height: auto; position: relative; flex-direction: row; 
                justify-content: space-between; align-items: center; padding: 15px 20px; 
                border-right: none; border-bottom: 1px solid rgba(255, 255, 255, 0.08); 
                background: #141414; /* Mantener oscuro en móvil */
            }
            .profile-card, .brand-logo span { display: none; } 
            .brand-logo { margin: 0; }
            .mobile-actions { display: flex; align-items: center; gap: 15px; }
            .btn-logout { padding: 10px; margin: 0; }
            .btn-logout span { display: none; }
            .theme-switch-wrapper { margin-bottom: 0; }
            
            .sidebar > .btn-logout, .sidebar > .theme-switch-wrapper { display: none; }

            .main-content { margin-left: 0; padding: 20px; }
            .search-form { flex-direction: column; }
        }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="brand-logo">
            <h1>UTMIR</h1>
            <span>Panel Administrador</span>
        </div>
        
        <div class="profile-card">
            <div class="profile-avatar">AD</div>
            <h2>Administrador</h2>
            <div class="matricula-badge">Depto. Vinculación</div>
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

        <a href="../api/logout_admin.php" class="btn-logout">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
            <span>Cerrar Sesión</span>
        </a>

        <div class="mobile-actions">
            <div class="theme-switch-wrapper">
                <label class="theme-switch" for="checkbox-mobile">
                    <input type="checkbox" id="checkbox-mobile" class="theme-checkbox" />
                    <div class="slider round">
                        <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
                        <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
                    </div>
                </label>
            </div>
            <a href="../api/logout_admin.php" class="btn-logout" title="Cerrar Sesión">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
            </a>
        </div>
    </aside>

    <main class="main-content">
        
        <div class="hero-banner">
            <h1 class="hero-title">Gestión de Expedientes</h1>
            <p class="hero-subtitle">Visualiza, busca y administra los documentos oficiales subidos por los estudiantes. Usa el buscador para filtrar por matrícula o nombre.</p>
        </div>

        <div class="search-container">
            <form action="" method="GET" class="search-form">
                <input type="text" name="q" class="search-input" placeholder="Buscar por Matrícula o Nombre del Alumno..." value="<?php echo htmlspecialchars($busqueda); ?>">
                <button type="submit" class="btn-search">Buscar</button>
                <?php if(!empty($busqueda)): ?>
                    <a href="panel.php" class="btn-search" style="background: var(--bg-input); color: var(--blanco); border: 1px solid var(--borde-sutil); text-decoration: none; display: flex; align-items: center;">Limpiar</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="submissions-grid">
            <?php if ($resultado->num_rows > 0): ?>
                <?php while ($fila = $resultado->fetch_assoc()): ?>
                    <div class="submission-card">
                        <div class="card-header">
                            <div class="student-info">
                                <h3><?php echo htmlspecialchars($fila['nombre_completo']); ?></h3>
                                <span class="matricula"><?php echo htmlspecialchars($fila['matricula']); ?></span>
                            </div>
                        </div>
                        <div class="card-body">
                            <p><span class="label">Nivel / Cuatrimestre</span> <span class="value"><?php echo htmlspecialchars($fila['cuatrimestre_subido']); ?></span></p>
                            <p><span class="label">Carrera</span> <span class="value"><?php echo htmlspecialchars($fila['programa_educativo_subido']); ?></span></p>
                            <p><span class="label">Fecha de Carga</span> <span class="value"><?php echo date("d/m/Y h:i A", strtotime($fila['fecha_subida'])); ?></span></p>
                            <p style="margin-top: 15px;"><span class="label">Archivo Original</span> <span class="value" style="font-family: monospace; font-size: 11px; word-break: break-all; color: var(--utmir-verde);"><?php echo htmlspecialchars($fila['nombre_archivo_subido']); ?></span></p>
                        </div>
                        <div class="card-actions">
                            <a href="<?php echo htmlspecialchars($fila['link_google_drive']); ?>" target="_blank" class="btn-view">
                                Abrir Documento PDF
                            </a>
                            <button class="btn-delete" onclick="abrirModalDelete(<?php echo $fila['id_entrega']; ?>, '<?php echo htmlspecialchars($fila['matricula']); ?>')" title="Eliminar Registro">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="11" y1="8" x2="11" y2="14"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
                    <h3>No se encontraron registros</h3>
                    <p style="color: var(--texto-mutado); font-size: 14px;">No hay documentos que coincidan con la búsqueda actual o el sistema está vacío.</p>
                </div>
            <?php endif; ?>
        </div>

        <footer class="dashboard-footer">
            <div class="footer-bottom">
            PROYECTA • INNOVA • ALCANZA<br><br>
            © <span id="currentYear"></span>. Universidad Tecnológica de Mineral de la Reforma. Todos los derechos reservados.
            </div>
        </footer>

    </main>

    <div class="modal-overlay" id="modalConfirmacion">
        <div class="modal-box">
            <div class="modal-icon icon-warning">
                <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
            </div>
            <h3 class="modal-title">¿Eliminar Registro?</h3>
            <p class="modal-text">Estás a punto de eliminar la entrega del alumno <b id="modalMatriculaTexto" style="color: var(--blanco);"></b>. Esta acción removerá el acceso al link en la base de datos.</p>
            <div class="modal-actions">
                <button class="btn-cancel" onclick="cerrarModal('modalConfirmacion')">Cancelar</button>
                <button class="btn-confirm" onclick="ejecutarEliminacion()">Sí, Eliminar</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="modalExito">
        <div class="modal-box">
            <div class="modal-icon icon-success">
                <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            </div>
            <h3 class="modal-title">Eliminado Correctamente</h3>
            <p class="modal-text">El registro de la memoria ha sido removido de la base de datos institucional.</p>
            <button class="btn-ok" onclick="cerrarModal('modalExito')">Entendido</button>
        </div>
    </div>

    <script>
        // ==========================================
        // SCRIPT DEL THEME SWITCHER (DARK/LIGHT)
        // ==========================================
        const toggleSwitches = document.querySelectorAll('.theme-checkbox');
        const currentTheme = localStorage.getItem('theme');

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

        // ==========================================
        // SCRIPT DE MODALES DE ELIMINACIÓN
        // ==========================================
        let idEliminarGlobal = null;

        function abrirModalDelete(id, matricula) {
            idEliminarGlobal = id;
            document.getElementById('modalMatriculaTexto').innerText = matricula;
            document.getElementById('modalConfirmacion').classList.add('active');
        }

        function cerrarModal(idModal) {
            document.getElementById(idModal).classList.remove('active');
            if (idModal === 'modalConfirmacion') idEliminarGlobal = null;
            if (idModal === 'modalExito') {
                const url = new URL(window.location);
                url.searchParams.delete('deleted');
                window.history.replaceState({}, document.title, url);
            }
        }

        function ejecutarEliminacion() {
            if(idEliminarGlobal !== null) {
                window.location.href = "../api/delete_entrega.php?id=" + idEliminarGlobal;
            }
        }

        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('deleted') === 'true') {
                document.getElementById('modalExito').classList.add('active');
            }
            document.getElementById('currentYear').textContent = new Date().getFullYear();
        };
    </script>
</body>
</html>