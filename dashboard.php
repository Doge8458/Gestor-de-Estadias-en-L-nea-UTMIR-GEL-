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

// LÓGICA 
$mostrarModalSuccess = false;
$rutaDriveModal = "";
if (isset($_GET['status']) && $_GET['status'] == 'success') {
    $mostrarModalSuccess = true;
    $carpetaNivel = isset($_GET['nivel']) ? $_GET['nivel'] : '';
    $carpetaCarrera = isset($_GET['carrera']) ? $_GET['carrera'] : '';
    $rutaDriveModal = htmlspecialchars($carpetaNivel . " > " . $carpetaCarrera);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Memoria de Estadía</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <style>
        /* Estilos */
        body { font-family: 'Roboto', sans-serif; margin: 0; padding: 20px; background-color: #f4f7f6; color: #333; }
        
        /* Header */
        header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #eee; padding-bottom: 20px; margin-bottom: 30px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .header-left h1 { margin-top: 0; color: #2c3e50; }
        
        /* Caja de estados */
        .status-box { background-color: #fff; border: 1px solid #e1e8ed; border-radius: 10px; padding: 20px; width: 350px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .status-title { margin-top: 0; margin-bottom: 15px; font-size: 18px; color: #2c3e50; text-align: center; border-bottom: 2px solid #f0f2f5; padding-bottom: 10px; font-weight: 700; }
        .status-item { margin-bottom: 12px; padding: 12px; border-radius: 8px; background: #f8f9fa; border: 1px solid #e9ecef; transition: all 0.3s ease; }
        .status-item:hover { transform: translateY(-2px); box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .dot { height: 12px; width: 12px; background-color: #cbd5e0; border-radius: 50%; display: inline-block; margin-right: 8px; }
        .dot.green { background-color: #28a745; box-shadow: 0 0 5px rgba(40, 167, 69, 0.5); }
        .status-text { font-weight: 600; font-size: 14px; }
        .green-text { color: #28a745; }
        .gray-text { color: #718096; }
        .drive-link { display: inline-block; margin-top: 8px; color: #3498db; text-decoration: none; font-size: 13px; font-weight: 500; }
        .drive-link:hover { text-decoration: underline; color: #2980b9; }
        .file-name { display: block; font-size: 12px; color: #4a5568; margin-top: 4px; font-weight: 600; word-break: break-all; background: #edf2f7; padding: 4px 8px; border-radius: 4px; }
        .upload-date { display: block; font-size: 11px; color: #718096; margin-top: 4px; font-style: italic; }

        /* Formulario y Secciones */
        main { background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        h2 { color: #2c3e50; border-bottom: 2px solid #f0f2f5; padding-bottom: 10px; margin-top: 0; }
        ul li { margin-bottom: 8px; color: #555; }
        select, button { padding: 12px 15px; border-radius: 6px; border: 1px solid #dce4ec; font-size: 14px; width: 100%; max-width: 400px; box-sizing: border-box; transition: border-color 0.3s; }
        select:focus { border-color: #3498db; outline: none; }
        label { font-weight: 600; color: #2c3e50; margin-bottom: 8px; display: block; }
        button { background-color: #3498db; color: white; border: none; font-weight: 600; cursor: pointer; transition: background-color 0.3s, transform 0.2s; }
        button:hover { background-color: #2980b9; transform: translateY(-1px); }
        .header-left button { width: auto; background-color: #e74c3c; }
        .header-left button:hover { background-color: #c0392b; }

        /* Drag & Drop Funcional */
        #drop-zone { border: 3px dashed #cbd5e0; border-radius: 12px; padding: 40px 20px; text-align: center; color: #718096; cursor: pointer; transition: all 0.3s ease; background: #f8f9fa; }
        #drop-zone:hover { border-color: #3498db; background: #ebf5fb; }
        #drop-zone.drag-over { background-color: #d6eaf8; border-color: #3498db; color: #2c3e50; }
        #drop-zone i { font-size: 40px; color: #3498db; margin-bottom: 15px; display: block; }
        #file-name-display { margin-top: 15px; font-weight: 700; color: #28a745; background: #d4edda; padding: 8px 15px; border-radius: 20px; display: inline-block; }

        /* Banner de Felicidades */
        .success-banner { background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); color: #155724; padding: 50px; text-align: center; border-radius: 15px; margin-top: 20px; box-shadow: 0 10px 25px rgba(40, 167, 69, 0.15); }
        .success-banner h2 { font-size: 32px; margin-bottom: 15px; border: none; }
        .success-banner p { font-size: 20px; }

        
           /* PANTALLA DE CARGA */
        
        #loading-overlay {
            position: fixed; /* Fijo sobre en la pantalla */
            top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(255, 255, 255, 0.95); /* Fondo blanco casi opaco */
            display: flex;
            flex-direction: column;
            justify-content: center; /* Centrado vertical */
            align-items: center; /* Centrado horizontal */
            z-index: 9999; /* Encima de todo */
            visibility: hidden; /* Oculto por defecto */
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        /* Clase para mostrar el overlay */
        #loading-overlay.active { visibility: visible; opacity: 1; }

        .loading-content {
            text-align: center;
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 90%;
        }

        .loading-logo {
            width: 150px; /* Ajusta el tamaño del logo */
            margin-bottom: 30px;
        }

        .loading-text {
            font-size: 22px; font-weight: 600; color: #2c3e50; margin-bottom: 25px;
        }

        /* Contenedor de la barra de progreso */
        .progress-container {
            width: 100%; background-color: #e9ecef; border-radius: 20px; height: 25px; overflow: hidden; box-shadow: inset 0 2px 5px rgba(0,0,0,0.05);
        }

        /* La barra que se llena */
        .progress-bar {
            height: 100%; width: 0%; /* Empieza en 0% */
            background: linear-gradient(90deg, #3498db, #2ecc71); /* Degradado azul a verde */
            border-radius: 20px;
            text-align: center; line-height: 25px; color: white; font-weight: bold; font-size: 14px;
            transition: width 0.4s ease; /* Animación suave */
        }

        /* Modal de Éxito Final */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.6); display: flex; justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background-color: white; padding: 30px; border-radius: 10px; width: 400px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.3); border: 2px solid #28a745; }
        .modal-icon { font-size: 40px; margin-bottom: 10px; }
        .modal-btn { background-color: #28a745; color: white; border: none; padding: 10px 25px; border-radius: 5px; font-size: 16px; margin-top: 20px; }
        .modal-btn:hover { background-color: #218838; }
    </style>
</head>
<body>

    <div id="loading-overlay">
        <div class="loading-content">
            <img src="assets/images/raptor.jpg" alt="Logo Universidad" class="loading-logo">
            
            <div class="loading-text">Subiendo tu memoria, por favor espera...</div>
            
            <div class="progress-container">
                <div id="progress-bar-fill" class="progress-bar">0%</div>
            </div>
            <p style="margin-top: 15px; color: #7f8c8d; font-size: 14px;">No cierres esta ventana.</p>
        </div>
    </div>
    <?php if ($mostrarModalSuccess): ?>
    <div class="modal-overlay">
        <div class="modal-content">
            <div class="modal-icon"></div>
            <h2>¡Archivo subido con éxito!</h2>
            <p>Tu memoria se subió a la carpeta de Drive:</p>
            <p style="font-weight: bold; color: #555; background: #f0f0f0; padding: 10px; border-radius: 5px;">
                <?php echo $rutaDriveModal; ?>
            </p>
            <a href="dashboard.php"><button class="modal-btn">Aceptar</button></a>
        </div>
    </div>
    <?php endif; ?>

    <header>
        <div class="header-left">
            <h1>Plataforma de Memorias</h1>
            <p style="color: #555; font-size: 16px;">Bienvenido, <b><?php echo htmlspecialchars($nombre_alumno); ?></b></p>
            <a href="api/logout.php"><button>Cerrar Sesión</button></a>
        </div>

        <div class="status-box">
            <h3 class="status-title"> Mis Entregas</h3>
            <div class="status-item">
                <?php if ($entrega_tsu): ?>
                    <span class="dot green"></span> <span class="status-text green-text">TSU (6to): Archivo Alojado</span>
                    <span class="file-name">📄 <?php echo htmlspecialchars($entrega_tsu['nombre_archivo_subido']); ?></span>
                    <span class="upload-date">📅 <?php echo date("d/m/Y h:i A", strtotime($entrega_tsu['fecha_subida'])); ?></span>
                    <?php if ($entrega_tsu['link_google_drive']): ?>
                        <a href="<?php echo $entrega_tsu['link_google_drive']; ?>" target="_blank" class="drive-link">🔗 Ver en Google Drive</a>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="dot"></span> <span class="status-text gray-text">TSU (6to): Pendiente</span>
                <?php endif; ?>
            </div>
            <div class="status-item">
                <?php if ($entrega_ing): ?>
                    <span class="dot green"></span> <span class="status-text green-text">ING (11vo): Archivo Alojado</span>
                    <span class="file-name">📄 <?php echo htmlspecialchars($entrega_ing['nombre_archivo_subido']); ?></span>
                    <span class="upload-date">📅 <?php echo date("d/m/Y h:i A", strtotime($entrega_ing['fecha_subida'])); ?></span>
                    <?php if ($entrega_ing['link_google_drive']): ?>
                        <a href="<?php echo $entrega_ing['link_google_drive']; ?>" target="_blank" class="drive-link">🔗 Ver en Google Drive</a>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="dot"></span> <span class="status-text gray-text">ING (11vo): Pendiente</span>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main>
        <?php if ($haTerminadoTodo): ?>
            <div class="success-banner">
                <h2>¡Felicidades!</h2>
                <p>Has concluido exitosamente con la entrega de tus memorias de TSU e Ingeniería.</p>
                <p style="font-size: 16px; margin-top: 20px;">Tu expediente digital está completo.</p>
            </div>
        <?php else: ?>
            <section>
                <h2><span style="color: #3498db;">Paso 1:</span> Prepara tu archivo</h2>
                <ul>
                    <li>Asegúrate de que el nombre cumpla el formato: <b>Matricula_ESTADIA_NIVEL_"Carrera".pdf</b></li>
                    <li>Ejemplo TSU: <code>2403322_ESTADIA_TEC_"TIeID".pdf</code></li>
                </ul>
            </section>
            <br>
            <section>
                <h2><span style="color: #3498db;">Paso 2:</span> Sube tu memoria</h2>
                
                <form id="upload-form" enctype="multipart/form-data">
                    
                    <label for="cuatrimestre">1. Selecciona tu nivel:</label>
                    <select id="cuatrimestre" name="cuatrimestre" required>
                        <option value="">-- Elige una opción --</option>
                        <?php if (!$yaSubioTSU): ?><option value="6to cuatri">6to Cuatrimestre (TSU)</option><?php endif; ?>
                        <?php if (!$yaSubioING): ?><option value="11vo cuatri">11vo Cuatrimestre (Ingeniería/Licenciatura)</option><?php endif; ?>
                    </select>
                    <br><br>

                    <label for="carrera">2. Selecciona tu Programa Educativo:</label>
                    <select id="carrera" name="programa_educativo" required>
                        <option value="">-- Elige una opción --</option>
                        <option value="TIeID">TIeID</option>
                        <option value="Ing. Civil">Ing. Civil</option>
                        <option value="Gastronomia">Gastronomía</option>
                        <option value="Turismo">Turismo</option>
                        <option value="Agrobiotecnologia">Agrobiotecnología</option>
                        <option value="Administracion">Administración</option>
                    </select>
                    <br><br>

                    <label>3. Sube tu archivo (solo PDF):</label>
                    <div id="drop-zone">
                        <i style="font-style: normal;">☁️</i>
                        Arrastra tu archivo PDF aquí o haz clic para seleccionar
                        <div id="file-name-display"></div>
                    </div>
                    <input type="file" id="memoria" name="memoria_archivo" accept=".pdf" required style="display: none;">
                    <br>
                    <button type="submit" id="submit-btn" style="width: 100%; font-size: 18px; padding: 15px;">Subir Archivo</button>
                </form>
            </section>
        <?php endif; ?>
    </main>

    <footer><hr><p style="text-align: center; color: #777;">&copy; 2025 - Universidad Tecnológica de Mineral de la Reforma</p></footer>

    <?php if (!$haTerminadoTodo): ?>
    <script>
        // Elementos del DOM
        const form = document.getElementById('upload-form');
        const loadingOverlay = document.getElementById('loading-overlay');
        const progressBarFill = document.getElementById('progress-bar-fill');
        const dropZone = document.getElementById('drop-zone');
        const fileInput = document.getElementById('memoria');
        const fileNameDisplay = document.getElementById('file-name-display');

        // --- LÓGICA DE DRAG & DROP ---
        dropZone.addEventListener('click', () => fileInput.click());
        fileInput.addEventListener('change', () => { if (fileInput.files.length > 0) handleFile(fileInput.files[0]); });
        dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('drag-over'); });
        dropZone.addEventListener('dragleave', (e) => { e.preventDefault(); dropZone.classList.remove('drag-over'); });
        dropZone.addEventListener('drop', (e) => { e.preventDefault(); dropZone.classList.remove('drag-over'); if (e.dataTransfer.files.length > 0) { fileInput.files = e.dataTransfer.files; handleFile(e.dataTransfer.files[0]); } });
        function handleFile(file) {
            if (file.name.toLowerCase().endsWith('.pdf')) { fileNameDisplay.textContent = "Archivo seleccionado: " + file.name; } 
            else { alert("Error: Solo se permiten archivos PDF."); fileInput.value = ""; fileNameDisplay.textContent = ""; }
        }
        
        // --- NUEVA LÓGICA DE SUBIDA CON AJAX ---
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // 1. EVITAMOS que el formulario recargue la página

            // Validar que haya archivo
            if(fileInput.files.length === 0) { alert("Por favor, selecciona un archivo PDF."); return; }

            // 2. MOSTRAR la pantalla de carga
            loadingOverlay.classList.add('active');
            progressBarFill.style.width = "0%";
            progressBarFill.textContent = "0%";

            // 3. PREPARAR los datos para enviar
            const formData = new FormData(form);

            // 4. CREAR la petición AJAX (XMLHttpRequest)
            const xhr = new XMLHttpRequest();

            // --- Escuchar el progreso de la subida ---
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    // Calcular porcentaje
                    const percentComplete = Math.round((e.loaded / e.total) * 100);
                    // Actualizar la barra y el texto
                    progressBarFill.style.width = percentComplete + "%";
                    progressBarFill.textContent = percentComplete + "%";
                }
            });

            // 5. CONFIGURAR qué pasa cuando termina
            xhr.addEventListener('load', function() {
                if (xhr.status === 200) {
                    // La subida terminó. (moviendo a Drive, BD, etc.)
                    progressBarFill.textContent = "Procesando...";
                    progressBarFill.style.backgroundColor = "#f39c12"; // Cambiar a naranja mientras procesa

                    // Parsear la respuesta JSON del servidor
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.status === 'success') {
                            // Redirigir a la URL para mostrar el modal final
                            window.location.href = response.redirect_url;
                        } else {
                            throw new Error(response.message || "Error desconocido en el servidor.");
                        }
                    } catch (error) {
                        alert("Error: " + error.message);
                        loadingOverlay.classList.remove('active'); // Ocultar pantalla de carga
                    }
                } else {
                    alert("Error al subir el archivo. Código: " + xhr.status);
                    loadingOverlay.classList.remove('active');
                }
            });

            // Manejar errores de red
            xhr.addEventListener('error', function() {
                alert("Error de conexión. Inténtalo de nuevo.");
                loadingOverlay.classList.remove('active');
            });

            // 6. ENVIAR la petición al PHP
            xhr.open('POST', 'api/upload.php', true);
            xhr.send(formData);
        });
    </script>
    <?php endif; ?>
</body>
</html>