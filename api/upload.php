<?php
session_start();
// Apagamos errores HTML para no romper la respuesta JSON a JavaScript
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

require '../vendor/autoload.php';

if (!isset($_SESSION['matricula'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesión expirada.']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["memoria_archivo"])) {
    
    $archivo = $_FILES["memoria_archivo"];
    
    $limite_bytes = 30 * 1024 * 1024; // 30 Megabytes
    if ($archivo['size'] > $limite_bytes) {
        echo json_encode(['status' => 'error', 'message' => 'El archivo supera el límite de 30MB permitidos.']);
        exit;
    }

    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    if ($extension != "pdf") {
        echo json_encode(['status' => 'error', 'message' => 'Solo se permiten archivos en formato .pdf']);
        exit;
    }

    $matricula = $_SESSION['matricula'];
    $nombre_limpio = str_replace(' ', '', $_SESSION['nombre']); 
    $programa = $_POST['programa_educativo'];
    $cuatrimestre = $_POST['cuatrimestre']; 
    
    // Nomenclatura corregida para las carpetas internas
    $nivel_carpeta = (strpos(strtolower($cuatrimestre), '6º') !== false || strpos(strtolower($cuatrimestre), '6to') !== false) ? "6to" : "10mo";

    $nuevo_nombre_pdf = "{$matricula}_{$nombre_limpio}_{$programa}_{$nivel_carpeta}.pdf";

    // --- MOTOR DE EXTRACCIÓN DE TEXTO (Para el filtro tipo Bandcamp) ---
    $contenido_texto = "";
    try {
        if (class_exists('\Smalot\PdfParser\Parser')) {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf_parseado = $parser->parseFile($archivo['tmp_name']);
            $contenido_texto = $pdf_parseado->getText();
            $contenido_texto = preg_replace('/\s+/', ' ', $contenido_texto); // Limpiar saltos de línea
        }
    } catch(Exception $e) {
        $contenido_texto = "Texto no disponible o PDF protegido.";
    }

    // Configurar Google Drive
    $client = new Google_Client();
    $client->setAuthConfig('../client_secret.json');
    $client->addScope(Google_Service_Drive::DRIVE);
    
    if (file_exists('token.json')) {
        $accessToken = json_decode(file_get_contents('token.json'), true);
        $client->setAccessToken($accessToken);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Falta el token de Google. Ejecuta auth.php primero.']);
        exit;
    }

    if ($client->isAccessTokenExpired()) {
        if ($client->getRefreshToken()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            file_put_contents('token.json', json_encode($client->getAccessToken()));
        }
    }

    $driveService = new Google_Service_Drive($client);

    function obtenerCrearCarpeta($driveService, $nombreCarpeta, $idPadre = null) {
        $q = "mimeType='application/vnd.google-apps.folder' and name='" . $nombreCarpeta . "' and trashed=false";
        if ($idPadre != null) { $q .= " and '" . $idPadre . "' in parents"; }
        $optParams = ['q' => $q, 'spaces' => 'drive', 'fields' => 'files(id, name)'];
        $resultados = $driveService->files->listFiles($optParams);
        
        if (count($resultados->getFiles()) == 0) {
            $carpetaMetadata = new Google_Service_Drive_DriveFile(['name' => $nombreCarpeta, 'mimeType' => 'application/vnd.google-apps.folder']);
            if ($idPadre != null) { $carpetaMetadata->setParents([$idPadre]); }
            $carpeta = $driveService->files->create($carpetaMetadata, ['fields' => 'id']);
            return $carpeta->id;
        } else {
            return $resultados->getFiles()[0]->getId();
        }
    }

    try {
        $anio_actual = date('Y');
        $mes_actual = (int)date('n');
        
        if ($mes_actual >= 1 && $mes_actual <= 4) { $periodo_actual = "Enero-Abril"; } 
        elseif ($mes_actual >= 5 && $mes_actual <= 8) { $periodo_actual = "Mayo-Agosto"; } 
        else { $periodo_actual = "Septiembre-Diciembre"; }

        $id_utmir = obtenerCrearCarpeta($driveService, 'UTMIR');
        $id_anio = obtenerCrearCarpeta($driveService, $anio_actual, $id_utmir);
        $id_periodo = obtenerCrearCarpeta($driveService, $periodo_actual, $id_anio);
        $id_programa = obtenerCrearCarpeta($driveService, $programa, $id_periodo);
        $id_nivel = obtenerCrearCarpeta($driveService, $nivel_carpeta, $id_programa);

        $fileMetadata = new Google_Service_Drive_DriveFile(['name' => $nuevo_nombre_pdf, 'parents' => [$id_nivel]]);
        $content = file_get_contents($archivo['tmp_name']);
        
        $file = $driveService->files->create($fileMetadata, ['data' => $content, 'mimeType' => 'application/pdf', 'uploadType' => 'multipart', 'fields' => 'id, webViewLink']);
        $permission = new Google_Service_Drive_Permission(['type' => 'anyone', 'role' => 'reader']);
        $driveService->permissions->create($file->id, $permission);
        
        $link_drive = $file->webViewLink;

        $conexion = new mysqli("localhost", "root", "", "portal_estadias");
        
        // Inserción incluyendo el texto analizado
        $stmt = $conexion->prepare("INSERT INTO entregas (matricula_alumno, nombre_archivo_subido, cuatrimestre_subido, programa_educativo_subido, link_google_drive, contenido_texto) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $matricula, $nuevo_nombre_pdf, $cuatrimestre, $programa, $link_drive, $contenido_texto);
        $stmt->execute();
        $stmt->close();
        $conexion->close();

        echo json_encode(['status' => 'success', 'message' => 'Archivo subido y guardado con éxito.']);
        
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error Drive: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Petición inválida.']);
}
?>