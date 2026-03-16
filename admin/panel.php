<?php
session_start();
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
            --utmir-verde: #006b3f;
            --utmir-verde-oscuro: #013b24;
            --utmir-verde-claro: #00a86b;
            --utmir-guinda: #801336;
            --utmir-dorado: #cca052;
            --texto-oscuro: #1e293b;
            --texto-claro: #475569;
            --cristal-bg: rgba(255, 255, 255, 0.85);
        }

        body {
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background-color: var(--utmir-verde-oscuro);
            color: var(--texto-oscuro);
            position: relative;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
        }

        /* Auroras de fondo */
        .bg-shape {
            position: fixed; border-radius: 50%; filter: blur(100px); z-index: -1;
            animation: float 12s infinite alternate ease-in-out;
        }
        .shape-verde { width: 50vw; height: 50vw; background: var(--utmir-verde-claro); top: -15%; left: -5%; opacity: 0.4; }
        .shape-guinda { width: 45vw; height: 45vw; background: #b51a4b; bottom: -5%; right: -5%; opacity: 0.4; animation-delay: -4s; }

        @keyframes float { 0% { transform: translate(0, 0) scale(1); } 100% { transform: translate(40px, 20px) scale(1.05); } }

        /* =========================================
           1. HEADER DINÁMICO (STICKY NAVBAR)
           ========================================= */
        .main-header {
            position: fixed; top: 0; left: 0; width: 100%;
            display: flex; justify-content: center; align-items: center;
            padding: 50px 20px; z-index: 100;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-sizing: border-box;
        }

        .header-titles { text-align: center; transition: all 0.4s ease; }
        .header-titles h1 { color: white; font-size: 36px; margin: 0; font-weight: 800; transition: all 0.4s ease; text-shadow: 0 4px 10px rgba(0,0,0,0.3); }
        .header-titles p { color: var(--utmir-dorado); font-size: 16px; font-weight: 600; margin: 5px 0 0 0; transition: all 0.4s ease; }

        .btn-logout {
            position: absolute; right: 40px;
            padding: 10px 20px; border-radius: 10px; font-weight: 700; font-size: 13px;
            background: rgba(255,255,255,0.1); border: 2px solid rgba(255,255,255,0.5); color: white;
            text-decoration: none; transition: all 0.3s ease; backdrop-filter: blur(5px);
        }
        .btn-logout:hover { background: var(--utmir-guinda); border-color: var(--utmir-guinda); }

        .main-header.scrolled {
            padding: 15px 20px;
            background: rgba(1, 59, 36, 0.85);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border-bottom: 2px solid var(--utmir-guinda);
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
        }
        .main-header.scrolled .header-titles h1 { font-size: 20px; text-shadow: none; }
        .main-header.scrolled .header-titles p { font-size: 11px; margin-top: 2px; }
        .main-header.scrolled .btn-logout { padding: 8px 16px; right: 20px; }

        /* =========================================
           2. CONTENEDOR DE LA TABLA Y BUSCADOR
           ========================================= */
        .dashboard-wrapper {
            flex: 1; padding: 20px; margin-top: 180px;
            display: flex; justify-content: center; align-items: flex-start;
        }

        .glass-card {
            width: 100%; max-width: 1100px;
            background: var(--cristal-bg); backdrop-filter: blur(25px); -webkit-backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.6); border-radius: 20px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.4);
            padding: 40px; box-sizing: border-box;
        }

        .search-container { display: flex; justify-content: center; margin-bottom: 30px; }
        .search-form { display: flex; gap: 8px; align-items: center; }
        .search-input {
            padding: 12px 20px; background: rgba(255, 255, 255, 0.9); border: 1px solid rgba(0,0,0,0.1);
            border-radius: 12px; width: 300px; font-size: 14px; font-family: inherit; outline: none; transition: 0.3s;
        }
        .search-input:focus { border-color: var(--utmir-verde); box-shadow: 0 0 0 3px rgba(0, 107, 63, 0.15); }

        .btn-search { background: var(--utmir-verde); color: white; padding: 12px 24px; border: none; border-radius: 12px; font-weight: 700; cursor: pointer; transition: 0.3s; }
        .btn-search:hover { background: #00502f; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,107,63,0.3); }

        /* Tabla y Corrección del Hover Guinda */
        .table-responsive { width: 100%; overflow: hidden; }
        .glass-table { width: 100%; border-collapse: separate; border-spacing: 0 6px; text-align: left; }
        .glass-table th { padding: 0 15px 10px 15px; color: var(--texto-claro); font-size: 11px; text-transform: uppercase; font-weight: 700; border-bottom: 1px solid rgba(0,0,0,0.05); }
        .glass-table tbody tr { background: rgba(255, 255, 255, 0.6); transition: 0.2s; position: relative; }
        .glass-table tbody tr:hover { background: #ffffff; transform: scale(1.005) translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .glass-table td { padding: 14px 15px; font-size: 13px; vertical-align: middle; }
        
        /* FIX: Borde guinda usando pseudo-elemento para que no mueva el texto */
        .glass-table tbody tr td:first-child { border-radius: 10px 0 0 10px; position: relative; }
        .glass-table tbody tr td:first-child::before {
            content: ''; position: absolute; left: 0; top: 0; height: 100%; width: 4px;
            background: var(--utmir-guinda); border-radius: 10px 0 0 10px;
            opacity: 0; transition: opacity 0.3s ease;
        }
        .glass-table tbody tr:hover td:first-child::before { opacity: 1; }
        .glass-table tbody tr td:last-child { border-radius: 0 10px 10px 0; }
        
        .matricula { color: var(--utmir-guinda); font-weight: 700; font-family: monospace; font-size: 14px; }
        .nombre-alumno { color: var(--utmir-verde); font-weight: 700; }
        .link-drive { color: #0284c7; text-decoration: none; font-weight: 600; padding: 6px 10px; background: rgba(2, 132, 199, 0.1); border-radius: 6px; transition: 0.2s; }
        .link-drive:hover { background: #0284c7; color: white; }
        .btn-delete { background: rgba(128, 19, 54, 0.1); color: var(--utmir-guinda); border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-weight: 700; font-size: 12px; transition: 0.2s; }
        .btn-delete:hover { background: var(--utmir-guinda); color: white; }

        /* =========================================
           3. MODALES Y ANIMACIONES (Estilo Video Loader)
           ========================================= */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(1, 59, 36, 0.7); backdrop-filter: blur(10px);
            display: flex; justify-content: center; align-items: center;
            z-index: 1000; opacity: 0; pointer-events: none; transition: opacity 0.3s ease;
        }
        .modal-overlay.active { opacity: 1; pointer-events: auto; }

        .modal-box {
            background: var(--cristal-bg); backdrop-filter: blur(25px); border: 1px solid rgba(255,255,255,0.6);
            border-radius: 20px; padding: 35px 30px; width: 90%; max-width: 380px; text-align: center;
            box-shadow: 0 30px 60px rgba(0,0,0,0.5); transform: scale(0.95) translateY(20px); transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .modal-overlay.active .modal-box { transform: scale(1) translateY(0); }

        .icon-container { position: relative; width: 60px; height: 60px; margin: 0 auto 15px auto; display: flex; justify-content: center; align-items: center; }
        .modal-icon { width: 100%; height: 100%; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-size: 28px; font-weight: bold; color: white; position: relative; z-index: 2; }
        
        /* NUEVO: Animación estilo Loader Circular del Video */
        .loader-ring { 
            position: absolute; top: -6px; left: -6px; right: -6px; bottom: -6px;
            border: 3px solid transparent; border-radius: 50%; z-index: 1;
        }
        
        .modal-delete .modal-icon { background: var(--utmir-guinda); }
        .modal-delete .loader-ring { 
            border-top-color: var(--utmir-guinda);
            border-right-color: rgba(128, 19, 54, 0.3); /* Efecto de desvanecimiento del video */
            animation: spinCircle 1s cubic-bezier(0.68, -0.55, 0.265, 1.55) infinite; 
        }
        .modal-delete h3 { color: var(--utmir-guinda); margin-top: 0; font-size: 20px; font-weight: 800; }

        .modal-success .modal-icon { background: var(--utmir-verde-claro); }
        /* Para el éxito, una animación de pulso que termina fija */
        .modal-success .loader-ring { 
            border-color: var(--utmir-verde-claro);
            animation: successPop 0.5s ease-out forwards; 
        }
        .modal-success h3 { color: var(--utmir-verde-claro); margin-top: 0; font-size: 20px; font-weight: 800; }

        @keyframes spinCircle { 
            0% { transform: rotate(0deg); } 
            100% { transform: rotate(360deg); } 
        }
        @keyframes successPop {
            0% { transform: scale(1); opacity: 1; }
            100% { transform: scale(1.3); opacity: 0; }
        }

        .modal-box p { color: var(--texto-claro); font-size: 14px; margin-bottom: 25px; line-height: 1.5; }
        .modal-actions { display: flex; gap: 10px; justify-content: center; }
        
        .btn-cancel { background: #e2e8f0; color: var(--texto-oscuro); border: none; padding: 10px 20px; border-radius: 10px; cursor: pointer; font-weight: 700; transition: 0.2s; }
        .btn-cancel:hover { background: #cbd5e1; }
        .btn-confirm-delete { background: var(--utmir-guinda); color: white; border: none; padding: 10px 20px; border-radius: 10px; cursor: pointer; font-weight: 700; box-shadow: 0 4px 10px rgba(128, 19, 54, 0.2); transition: 0.2s; }
        .btn-confirm-delete:hover { background: #600e28; }
        .btn-ok { background: var(--utmir-verde-claro); color: white; border: none; padding: 10px 30px; border-radius: 10px; cursor: pointer; font-weight: 700; transition: 0.2s; box-shadow: 0 4px 10px rgba(0, 168, 107, 0.2); }
        .btn-ok:hover { background: #008f5a; }

        /* =========================================
           4. FOOTER
           ========================================= */
        .official-footer {
            background-color: rgba(0, 43, 26, 0.95); border-top: 4px solid var(--utmir-dorado);
            color: rgba(255,255,255,0.8); padding: 30px 20px 15px 20px; font-size: 13px; margin-top: auto; position: relative; z-index: 10;
        }
        .footer-bottom { max-width: 1000px; margin: 0 auto; text-align: center; font-size: 12px; }
    </style>
</head>
<body>

    <div class="bg-shape shape-verde"></div>
    <div class="bg-shape shape-guinda"></div>

    <header class="main-header" id="navbar">
        <div class="header-titles">
            <h1>Portal de Estadías</h1>
            <p>Gestión Central de Memorias y Documentos</p>
        </div>
        <a href="index.html" class="btn-logout">Cerrar Sesión</a>
    </header>

    <div class="dashboard-wrapper">
        <div class="glass-card">
            
            <div class="search-container">
                <form method="GET" class="search-form">
                    <input type="text" name="q" class="search-input" placeholder="Buscar matrícula o nombre..." value="<?php echo htmlspecialchars($busqueda); ?>">
                    <button type="submit" class="btn-search">Buscar</button>
                    <?php if($busqueda): ?><a href="panel.php" style="color:var(--utmir-guinda); font-weight:700; font-size:13px; margin-left:10px; text-decoration:none;">Limpiar</a><?php endif; ?>
                </form>
            </div>

            <div class="table-responsive">
                <table class="glass-table">
                    <thead>
                        <tr>
                            <th>Matrícula</th>
                            <th>Alumno</th>
                            <th>Programa</th>
                            <th style="text-align:center;">Cuatri</th>
                            <th style="text-align:center;">Archivo</th>
                            <th>Fecha</th>
                            <th style="text-align:center;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($resultado->num_rows > 0): ?>
                            <?php while($row = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td class="matricula"><?php echo htmlspecialchars($row['matricula']); ?></td>
                                <td class="nombre-alumno"><?php echo htmlspecialchars($row['nombre_completo']); ?></td>
                                <td><?php echo htmlspecialchars($row['programa_educativo_subido']); ?></td>
                                <td style="text-align:center;"><b><?php echo htmlspecialchars($row['cuatrimestre_subido']); ?></b></td>
                                <td style="text-align:center;"><a href="<?php echo htmlspecialchars($row['link_google_drive']); ?>" target="_blank" class="link-drive">Ver</a></td>
                                <td><?php echo date("d/m/y", strtotime($row['fecha_subida'])); ?></td>
                                <td style="text-align:center;">
                                    <button class="btn-delete" onclick="abrirModalDelete(<?php echo $row['id_entrega']; ?>, '<?php echo htmlspecialchars($row['matricula']); ?>')">
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7" style="text-align:center; padding:30px; color:var(--texto-claro);">No hay registros.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <div class="modal-overlay modal-delete" id="modalConfirmacion">
        <div class="modal-box">
            <div class="icon-container">
                <div class="loader-ring"></div>
                <div class="modal-icon">!</div>
            </div>
            <h3>Eliminar Registro</h3>
            <p>Se borrará permanentemente la entrega de la matrícula <strong id="modalMatriculaTexto" style="color:var(--utmir-guinda);"></strong>. ¿Continuar?</p>
            <div class="modal-actions">
                <button class="btn-cancel" onclick="cerrarModal('modalConfirmacion')">Cancelar</button>
                <button class="btn-confirm-delete" onclick="ejecutarEliminacion()">Eliminar</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay modal-success" id="modalExito">
        <div class="modal-box">
            <div class="icon-container">
                <div class="loader-ring"></div>
                <div class="modal-icon">✓</div>
            </div>
            <h3>¡Proceso Exitoso!</h3>
            <p>El documento ha sido eliminado correctamente de la base de datos.</p>
            <div class="modal-actions">
                <button class="btn-ok" onclick="cerrarModal('modalExito')">Aceptar</button>
            </div>
        </div>
    </div>

    <footer class="official-footer">
        <div class="footer-bottom">
            PROYECTA • INNOVA • ALCANZA<br><br>
            © <span id="currentYear"></span>. Universidad Tecnológica de Mineral de la Reforma. Todos los derechos reservados.
        </div>
    </footer>

    <script>
        // Header
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) navbar.classList.add('scrolled');
            else navbar.classList.remove('scrolled');
        });

        // Modales
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