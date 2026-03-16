<?php
// 1. SESIÓN Y CONEXIÓN 
session_start();
if (!isset($_SESSION['matricula'])) { header("Location: index.html"); exit(); }
$matricula_alumno = $_SESSION['matricula'];
$nombre_alumno = $_SESSION['nombre'];
$servidor = "localhost"; $usuario_db = "root"; $password_db = ""; $nombre_db = "portal_estadias";
$conexion = new mysqli($servidor, $usuario_db, $password_db, $nombre_db);
$entrega_tsu = null; $entrega_ing = null;
$stmt = $conexion->prepare("SELECT * FROM entregas WHERE matricula_alumno = ?");
$stmt->bind_param("i", $matricula_alumno);
$stmt->execute();
$resultado = $stmt->get_result();
while ($fila = $resultado->fetch_assoc()) {
    if (strpos($fila['cuatrimestre_subido'], '6to') !== false) { $entrega_tsu = $fila; }
    if (strpos($fila['cuatrimestre_subido'], '11vo') !== false) { $entrega_ing = $fila; }
}
$stmt->close(); $conexion->close();

$yaSubioTSU = ($entrega_tsu != null);
$yaSubioING = ($entrega_ing != null);
$haTerminadoTodo = ($yaSubioTSU && $yaSubioING);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Académico - UTMIR</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            /* Paleta Dark Institucional (Default) */
            --bg-profundo: #2a0710;
            --bg-card: #141414;
            --bg-input: #0a0a0a;
            --utmir-guinda: #801336;
            --utmir-verde: #00a86b; 
            --blanco: #ffffff;
            --texto-claro: #e2e8f0;
            --texto-mutado: #94a3b8;
            --borde-sutil: rgba(255, 255, 255, 0.08);
        }

        /* --- NUEVO: Paleta Light Theme --- */
        [data-theme="light"] {
            --bg-profundo: #f3f4f6;
            --bg-card: #ffffff;
            --bg-input: #f8fafc;
            --blanco: #111827;
            --texto-claro: #334155;
            --texto-mutado: #64748b;
            --borde-sutil: rgba(0, 0, 0, 0.1);
            /* Mantenemos los colores institucionales */
            --utmir-guinda: #801336;
            --utmir-verde: #00a86b;
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
           SIDEBAR INSTITUCIONAL
           ========================================= */
        .sidebar {
            width: 280px;
            background: var(--bg-card);
            border-right: 1px solid var(--borde-sutil);
            color: var(--blanco);
            padding: 40px 20px;
            display: flex; flex-direction: column;
            position: fixed; height: 100vh;
            top: 0; left: 0;
            box-sizing: border-box; z-index: 100;
            transition: background-color 0.4s ease, border-color 0.4s ease;
        }

        .brand-logo { text-align: center; margin-bottom: 40px; }
        .brand-logo h1 { font-size: 26px; font-weight: 800; margin: 0; letter-spacing: 1px; color: var(--blanco); }
        .brand-logo span { font-size: 12px; letter-spacing: 2px; text-transform: uppercase; color: var(--utmir-verde); font-weight: 600; }

        .profile-card {
            background: rgba(128, 128, 128, 0.05);
            border: 1px solid var(--borde-sutil);
            border-radius: 12px; padding: 25px 15px; text-align: center;
            margin-bottom: auto;
        }
        .profile-avatar {
            width: 60px; height: 60px; background: var(--utmir-guinda);
            border-radius: 50%; margin: 0 auto 15px auto;
            display: flex; justify-content: center; align-items: center;
            font-size: 24px; color: #fff; font-weight: 800;
        }
        .profile-card h2 { font-size: 16px; margin: 0 0 10px 0; font-weight: 600; color: var(--blanco); }
        .matricula-badge { 
            display: inline-block; background: rgba(0, 168, 107, 0.15); color: var(--utmir-verde);
            padding: 5px 12px; border-radius: 20px; font-family: monospace; font-size: 13px; font-weight: 700;
            border: 1px solid rgba(0, 168, 107, 0.3);
        }

        /* --- NUEVO: Estilos del Toggle Switch --- */
        .theme-switch-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }
        .theme-switch {
            position: relative;
            display: inline-block;
            width: 64px;
            height: 32px;
        }
        .theme-switch input { display: none; }
        .slider.round {
            position: absolute;
            cursor: pointer;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: #1a1a1a;
            transition: .4s;
            border-radius: 34px;
            border: 1px solid var(--borde-sutil);
        }
        .slider.round:before {
            position: absolute;
            content: "";
            height: 24px; width: 24px;
            left: 4px; bottom: 3px;
            background-color: #fff;
            transition: .4s;
            border-radius: 50%;
            z-index: 2;
        }
        input:checked + .slider.round {
            background-color: #e2e8f0;
        }
        input:checked + .slider.round:before {
            transform: translateX(30px);
            background-color: #111827;
        }
        .icon-moon, .icon-sun {
            position: absolute;
            top: 7px;
            width: 18px;
            height: 18px;
            z-index: 1;
            transition: .4s;
        }
        .icon-moon { right: 8px; color: #fff; }
        .icon-sun { left: 8px; color: #f59e0b; opacity: 0; }
        input:checked + .slider.round .icon-moon { opacity: 0; }
        input:checked + .slider.round .icon-sun { opacity: 1; }
        /* ---------------------------------------- */

        .btn-logout {
            background: rgba(128,128,128,0.05);
            border: 1px solid var(--borde-sutil);
            color: var(--texto-claro); text-decoration: none; padding: 15px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            font-weight: 600; transition: 0.3s;
        }
        .btn-logout:hover { background: var(--utmir-guinda); border-color: var(--utmir-guinda); color: #fff; }

        /* =========================================
           CONTENIDO PRINCIPAL
           ========================================= */
        .main-content {
            flex: 1; margin-left: 280px; 
            padding: 40px; box-sizing: border-box;
            display: flex; flex-direction: column; gap: 30px;
            max-width: 1400px; min-height: 100vh;
        }

        /* Resto de tu CSS intacto pero adaptado con transition suave */
        .hero-banner, .notifications-panel, .status-card, .panel-card, .modal-box {
            transition: background-color 0.4s ease, border-color 0.4s ease, box-shadow 0.4s ease;
        }
        .form-select, .file-drop-area, .code-snippet {
            transition: background-color 0.4s ease, border-color 0.4s ease, color 0.4s ease;
        }

        .top-row { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }

        .hero-banner {
            background: var(--bg-card);
            border-radius: 16px; padding: 35px;
            border-left: 6px solid var(--utmir-verde);
            border-top: 1px solid var(--borde-sutil); border-right: 1px solid var(--borde-sutil); border-bottom: 1px solid var(--borde-sutil);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            display: flex; flex-direction: column; justify-content: center;
        }
        .hero-title { font-size: 26px; color: var(--blanco); margin: 0 0 10px 0; font-weight: 800; }
        .hero-subtitle { color: var(--texto-mutado); font-size: 15px; margin: 0; line-height: 1.6; }

        .notifications-panel {
            background: var(--bg-card);
            border-radius: 16px; padding: 25px;
            border: 1px solid var(--borde-sutil); box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .notif-header { display: flex; align-items: center; gap: 10px; margin-bottom: 15px; border-bottom: 1px solid var(--borde-sutil); padding-bottom: 10px; }
        .notif-header h3 { margin: 0; font-size: 16px; color: var(--blanco); }
        .icon-bell { width: 20px; height: 20px; color: var(--utmir-verde); animation: swing 4s infinite ease-in-out; }
        
        .notif-item { background: rgba(128, 128, 128, 0.05); border-left: 3px solid var(--utmir-guinda); padding: 12px 15px; border-radius: 4px; font-size: 13px; color: var(--texto-claro); line-height: 1.5; }
        .notif-date { display: block; font-size: 11px; color: var(--utmir-verde); margin-bottom: 5px; font-weight: 600; }

        .status-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .status-card { 
            background: var(--bg-card); border: 1px solid var(--borde-sutil); 
            border-radius: 16px; padding: 25px; display: flex; align-items: center; gap: 20px; 
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        }
        .status-icon-box { width: 60px; height: 60px; border-radius: 12px; display: flex; justify-content: center; align-items: center; flex-shrink: 0; background: rgba(128,128,128,0.05); border: 1px solid var(--borde-sutil); }
        .status-info h3 { margin: 0 0 5px 0; font-size: 16px; color: var(--blanco); font-weight: 700; }
        
        .status-card.success .status-icon-box { color: var(--utmir-verde); border-color: rgba(0, 168, 107, 0.3); }
        .status-card.pending .status-icon-box { color: var(--utmir-guinda); }

        .badge-status { display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 800; text-transform: uppercase; margin-bottom: 8px; }
        .badge-success { background: rgba(0, 168, 107, 0.15); color: var(--utmir-verde); border: 1px solid rgba(0, 168, 107, 0.3); }
        .badge-pending { background: rgba(128, 19, 54, 0.15); color: var(--utmir-guinda); border: 1px solid rgba(128, 19, 54, 0.3); }

        .link-drive { display: inline-flex; align-items: center; gap: 5px; margin-top: 10px; color: var(--utmir-verde); font-weight: 700; text-decoration: none; font-size: 13px; }
        .link-drive:hover { text-decoration: underline; color: var(--blanco); }

        .content-grid { display: grid; grid-template-columns: 1.5fr 1fr; gap: 20px; }
        
        .panel-card { background: var(--bg-card); border: 1px solid var(--borde-sutil); border-radius: 16px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .panel-header { display: flex; align-items: center; gap: 10px; margin-bottom: 25px; border-bottom: 1px solid var(--borde-sutil); padding-bottom: 15px; }
        .panel-header h3 { color: var(--blanco); margin: 0; font-size: 18px; font-weight: 700; }
        .panel-header svg { color: var(--utmir-verde); }

        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 13px; font-weight: 600; color: var(--texto-mutado); text-transform: uppercase; letter-spacing: 0.5px; }
        
        .form-select { 
            width: 100%; padding: 14px; border: 1px solid var(--borde-sutil); border-radius: 8px; font-size: 14px; 
            font-family: inherit; font-weight: 500; outline: none; background: var(--bg-input); color: var(--blanco); 
        }
        .form-select:focus { border-color: var(--utmir-verde); box-shadow: 0 0 0 3px rgba(0, 168, 107, 0.15); }
        
        .file-drop-area { 
            position: relative; background: var(--bg-input); border: 2px dashed rgba(128,128,128,0.4); 
            border-radius: 12px; padding: 40px 20px; text-align: center; cursor: pointer; margin-bottom: 20px;
        }
        .file-drop-area:hover { border-color: var(--utmir-verde); background: rgba(0, 168, 107, 0.05); }
        .file-drop-area input[type=file] { position: absolute; left: 0; top: 0; opacity: 0; cursor: pointer; height: 100%; width: 100%; z-index: 10; }
        .file-msg { display: block; color: var(--blanco); font-weight: 600; font-size: 15px; margin-bottom: 5px; margin-top: 15px; }
        .file-submsg { display: block; color: var(--texto-mutado); font-size: 13px; }

        .btn-submit { 
            background: var(--utmir-verde); color: #fff; border: none; padding: 16px 30px; 
            border-radius: 8px; cursor: pointer; font-weight: 800; font-size: 15px; width: 100%; transition: all 0.3s;
            text-transform: uppercase; letter-spacing: 1px;
        }
        .btn-submit:hover { filter: brightness(1.2); transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0, 168, 107, 0.4); }

        .info-list { list-style: none; padding: 0; margin: 0; }
        .info-list li { position: relative; padding-left: 25px; margin-bottom: 15px; color: var(--texto-claro); font-size: 14px; line-height: 1.6; }
        .info-list li svg { position: absolute; left: 0; top: 2px; width: 16px; height: 16px; color: var(--utmir-verde); }
        .code-snippet { display: block; background: var(--bg-input); padding: 10px; border-radius: 6px; font-family: monospace; color: var(--utmir-verde); margin-top: 8px; border: 1px solid var(--borde-sutil); font-size: 13px; }

        /* FOOTER */
        .dashboard-footer { margin-top: auto; padding-top: 30px; border-top: 1px solid var(--borde-sutil); text-align: center; color: var(--texto-mutado); font-size: 13px; }

        /* ANIMACIONES Y SVGs */
        @keyframes swing { 20% { transform: rotate(15deg); } 40% { transform: rotate(-10deg); } 60% { transform: rotate(5deg); } 80% { transform: rotate(-5deg); } 100% { transform: rotate(0deg); } }
        @keyframes float { 0% { transform: translateY(0px); } 50% { transform: translateY(-8px); } 100% { transform: translateY(0px); } }
        @keyframes drawCheck { 0% { stroke-dasharray: 50; stroke-dashoffset: 50; } 100% { stroke-dasharray: 50; stroke-dashoffset: 0; } }
        
        .anim-float { animation: float 3s ease-in-out infinite; }
        .anim-check { stroke-dasharray: 50; stroke-dashoffset: 50; animation: drawCheck 0.8s forwards ease-in-out; }

        /* MODALES */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.8); backdrop-filter: blur(5px); display: flex; justify-content: center; align-items: center; z-index: 1000; opacity: 0; pointer-events: none; transition: 0.3s; }
        .modal-overlay.active { opacity: 1; pointer-events: auto; }
        .modal-box { background: var(--bg-card); border: 1px solid var(--borde-sutil); border-radius: 16px; padding: 40px; width: 90%; max-width: 400px; text-align: center; transform: translateY(20px); transition: 0.3s; box-shadow: 0 25px 50px rgba(0,0,0,0.5); }
        .modal-overlay.active .modal-box { transform: translateY(0); }
        
        .progress-bar-bg { width: 100%; height: 6px; background: var(--bg-input); border-radius: 10px; margin-top: 25px; overflow: hidden; border: 1px solid var(--borde-sutil); }
        .progress-bar-fill { height: 100%; background: var(--utmir-verde); width: 0%; transition: width 0.3s; }
        .btn-ok { background: var(--utmir-verde); color: #fff; border: none; padding: 14px; border-radius: 8px; font-weight: 800; width: 100%; margin-top: 20px; cursor: pointer; transition: 0.3s; text-transform: uppercase;}
        .btn-ok:hover { filter: brightness(1.2); }

        .mobile-actions { display: none; }

        @media (max-width: 1000px) { 
            body { flex-direction: column; }
            .sidebar { width: 100%; height: auto; position: relative; flex-direction: row; justify-content: space-between; align-items: center; padding: 15px 20px; border-right: none; border-bottom: 1px solid var(--borde-sutil); }
            .profile-card, .brand-logo span { display: none; } 
            .brand-logo { margin: 0; }
            .mobile-actions { display: flex; align-items: center; gap: 15px; }
            .btn-logout { padding: 10px; margin: 0; }
            .btn-logout span { display: none; } /* Oculta el texto de cerrar sesión en móvil */
            .theme-switch-wrapper { margin-bottom: 0; }
            
            /* Ocultamos los botones normales de la vista PC y usamos el wrapper móvil */
            .sidebar > .btn-logout { display: none; }
            .sidebar > .theme-switch-wrapper { display: none; }

            .main-content { margin-left: 0; padding: 20px; }
            .top-row, .content-grid, .form-row { grid-template-columns: 1fr; } 
        }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="brand-logo">
            <h1>UTMIR</h1>
            <span>Portal Estadías</span>
        </div>
        
        <div class="profile-card">
            <div class="profile-avatar"><?php echo strtoupper(substr($nombre_alumno, 0, 1)); ?></div>
            <h2><?php echo htmlspecialchars($nombre_alumno); ?></h2>
            <div class="matricula-badge"><?php echo htmlspecialchars($matricula_alumno); ?></div>
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
            <a href="api/logout.php" class="btn-logout" title="Cerrar Sesión">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
            </a>
        </div>
    </aside>

    <main class="main-content">
        
        <div class="top-row">
            <div class="hero-banner">
                <h1 class="hero-title">Recepción de Documentos Oficiales</h1>
                <p class="hero-subtitle">Bienvenido(a), <b style="color: var(--blanco);"><?php echo htmlspecialchars($nombre_alumno); ?></b>. Este portal institucional está destinado a la carga exclusiva de las versiones finales y autorizadas de las memorias de estadía.</p>
            </div>
            
            <div class="notifications-panel">
                <div class="notif-header">
                    <svg class="icon-bell" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                    <h3>Avisos de Administración</h3>
                </div>
                <div class="notif-item">
                    <span class="notif-date">Mensaje Automático - Sistema Activo</span>
                    Mantente al tanto. Si tu documento requiere correcciones de formato, se te notificará en este panel.
                </div>
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
                        <span style="color: var(--texto-mutado); font-size: 12px;">Fecha de registro: <?php echo date("d/m/Y", strtotime($entrega_tsu['fecha_subida'])); ?></span><br>
                        <a href="<?php echo $entrega_tsu['link_google_drive']; ?>" target="_blank" class="link-drive">Abrir PDF Institucional ↗</a>
                    <?php else: ?>
                        <span style="color: var(--texto-mutado); font-size: 12px;">Esperando archivo del alumno.</span>
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
                        <span style="color: var(--texto-mutado); font-size: 12px;">Fecha de registro: <?php echo date("d/m/Y", strtotime($entrega_ing['fecha_subida'])); ?></span><br>
                        <a href="<?php echo $entrega_ing['link_google_drive']; ?>" target="_blank" class="link-drive">Abrir PDF Institucional ↗</a>
                    <?php else: ?>
                        <span style="color: var(--texto-mutado); font-size: 12px;">Esperando archivo del alumno.</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if ($haTerminadoTodo): ?>
            <div style="background: var(--bg-card); border: 1px solid rgba(0, 168, 107, 0.4); border-radius: 16px; padding: 40px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="var(--utmir-verde)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"></path><path d="m9 12 2 2 4-4"></path></svg>
                <h3 style="color: var(--blanco); font-size:22px; margin: 15px 0 10px 0;">Expediente Institucional Completo</h3>
                <p style="color: var(--texto-claro); margin:0;">El departamento de Vinculación ha recibido exitosamente ambos documentos. Ya no hay acciones pendientes.</p>
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
                                <input type="file" name="memoria_archivo" id="archivo_pdf" accept=".pdf" required onchange="actualizarNombreArchivo(this)">
                            </div>
                        </div>

                        <button type="submit" class="btn-submit">Procesar Entrega</button>
                    </form>
                </div>

                <div class="panel-card">
                    <div class="panel-header">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--utmir-guinda)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
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
                            <span style="font-size: 12px; color: var(--texto-mutado); margin-top:5px; display:block;">Ej: 20210001_JuanPerez_TIeID_6to.pdf</span>
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

    <div class="modal-overlay" id="loadingModal">
        <div class="modal-box">
            <svg class="anim-float" width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="var(--utmir-verde)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:15px;"><polyline points="16 16 12 12 8 16"></polyline><line x1="12" y1="12" x2="12" y2="21"></line><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"></path><polyline points="16 16 12 12 8 16"></polyline></svg>
            <h3 id="loadingTitle" style="color: var(--blanco); font-size: 20px; margin-top:0;">Procesando Documento...</h3>
            <p style="color: var(--texto-mutado); font-size: 14px; margin:0;">Transfiriendo a la nube institucional.</p>
            <div class="progress-bar-bg"><div class="progress-bar-fill" id="progressBar"></div></div>
            <p id="progressText" style="color: var(--utmir-verde); font-weight: bold; font-size: 16px; margin-top: 10px;">0%</p>
        </div>
    </div>

    <div class="modal-overlay" id="messageModal">
        <div class="modal-box">
            <div id="msgIconContainer" style="margin-bottom: 20px;"></div>
            <h3 id="msgTitle" style="font-size: 22px; margin-top:0; color: var(--blanco);">Título</h3>
            <p id="msgText" style="color: var(--texto-claro); font-size: 14px; line-height: 1.5;">Mensaje</p>
            <button class="btn-ok" onclick="cerrarMensajeYRecargar()">Entendido</button>
        </div>
    </div>

    <script>
        // ==========================================
        // SCRIPT DEL THEME SWITCHER (DARK/LIGHT)
        // ==========================================
        const toggleSwitches = document.querySelectorAll('.theme-checkbox');
        const currentTheme = localStorage.getItem('theme');

        // Si hay un tema guardado, lo aplicamos
        if (currentTheme) {
            document.documentElement.setAttribute('data-theme', currentTheme);
            if (currentTheme === 'light') {
                toggleSwitches.forEach(sw => sw.checked = true);
            }
        }

        function switchTheme(e) {
            const isChecked = e.target.checked;
            // Sincronizar todos los switches (el de PC y el de Móvil)
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

        function actualizarNombreArchivo(input) {
            const display = document.getElementById('fileNameDisplay');
            const dropArea = document.getElementById('dropArea');
            const icon = document.getElementById('uploadIcon');
            if (input.files && input.files[0]) {
                display.innerHTML = `<span style="color: var(--utmir-verde); font-size: 16px;">${input.files[0].name}</span>`;
                dropArea.style.borderColor = 'var(--utmir-verde)';
                icon.innerHTML = `<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><polyline points="9 15 12 18 15 15"></polyline><line x1="12" y1="18" x2="12" y2="12"></line>`;
                icon.style.stroke = 'var(--utmir-verde)';
            } else {
                display.innerHTML = `Haga clic o arrastre el archivo aquí`;
                dropArea.style.borderColor = 'rgba(128,128,128,0.4)';
                icon.innerHTML = `<path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline>`;
                icon.style.stroke = 'var(--texto-mutado)';
            }
        }

        <?php if (!$haTerminadoTodo): ?>
        const uploadForm = document.getElementById('uploadForm');
        const loadingModal = document.getElementById('loadingModal');
        const messageModal = document.getElementById('messageModal');

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

        function cerrarMensajeYRecargar() {
            messageModal.classList.remove('active');
            if(document.getElementById('msgTitle').style.color === 'var(--utmir-verde)'){
                window.location.reload();
            }
        }

        uploadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const fileInput = document.getElementById('archivo_pdf');
            if(fileInput.files.length === 0) return mostrarMensaje('error', 'Archivo Faltante', 'Debe seleccionar un documento.');
            
            const file = fileInput.files[0];
            if (!file.name.toLowerCase().endsWith('.pdf')) {
                return mostrarMensaje('error', 'Formato Inválido', 'El sistema solo admite archivos con extensión .pdf');
            }

            loadingModal.classList.add('active');
            document.getElementById('progressBar').style.width = '0%';
            document.getElementById('progressText').innerText = '0%';

            const formData = new FormData(uploadForm);
            const xhr = new XMLHttpRequest();

            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const pct = Math.round((e.loaded / e.total) * 100);
                    document.getElementById('progressBar').style.width = pct + '%';
                    document.getElementById('progressText').innerText = pct + '%';
                }
            });
            
            xhr.addEventListener('load', function() {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.status === 'success') {
                        mostrarMensaje('exito', 'Registro Exitoso', 'El documento se ha guardado correctamente en el expediente.');
                    } else {
                        mostrarMensaje('error', 'Error de Sistema', response.message);
                    }
                } catch (error) {
                    console.error("Error PHP:", xhr.responseText);
                    mostrarMensaje('error', 'Fallo del Servidor', `
                        El archivo excede la capacidad actual del servidor PHP.<br><br>
                        <b>Nota técnica:</b> Aumente los valores de <code>upload_max_filesize</code> y <code>post_max_size</code> en su archivo php.ini.
                    `);
                }
            });
            
            xhr.addEventListener('error', () => mostrarMensaje('error', 'Error de Conexión', "No se pudo establecer comunicación con el servidor."));
            xhr.open('POST', 'api/upload.php', true);
            xhr.send(formData);
        });
        

        <?php endif; ?>
        // Script para actualizar el año en el footer automáticamente
        document.getElementById('currentYear').textContent = new Date().getFullYear();
    </script>
</body>
</html>