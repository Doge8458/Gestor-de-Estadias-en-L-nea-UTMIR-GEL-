<?php
// Ocultar avisos "Deprecated" y otros errores para no ensuciar la respuesta JSON
ini_set('display_errors', 0);
error_reporting(0);

// La respuesta será JSON
header('Content-Type: application/json');

try {
    // 1. CARGAR LA LIBRERÍA DE GOOGLE
    require_once __DIR__ . '/../vendor/autoload.php';

    session_start();

    // --- ID DE LA CARPETA RAÍZ ---
    define('GOOGLE_DRIVE_ROOT_ID', '1ZqKmNp8XkEVsEXbQQouosIjBBv-Sar5d');

    // 2. CONEXIÓN A BD
    $servidor = "localhost"; $usuario_db = "root"; $password_db = ""; $nombre_db = "portal_estadias";
    $conexion = new mysqli($servidor, $usuario_db, $password_db, $nombre_db);
    if ($conexion->connect_error) { throw new Exception("Error de conexión a la BD: " . $conexion->connect_error); }

    if (!isset($_SESSION['matricula'])) { throw new Exception("Acceso no autorizado."); }

    // --- FUNCIÓN PARA GESTIONAR CARPETAS ---
    function buscarOCrearCarpeta($service, $nombreCarpeta, $idPadre) {
        $query = "mimeType='application/vnd.google-apps.folder' and name='" . $nombreCarpeta . "' and '" . $idPadre . "' in parents and trashed=false";
        $files = $service->files->listFiles(array('q' => $query, 'fields' => 'files(id, name)'));
        if (count($files->files) == 0) {
            $fileMetadata = new Google\Service\Drive\DriveFile(array(
                'name' => $nombreCarpeta, 'mimeType' => 'application/vnd.google-apps.folder', 'parents' => array($idPadre)
            ));
            $folder = $service->files->create($fileMetadata, array('fields' => 'id'));
            return $folder->id;
        } else { return $files->files[0]->id; }
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // A. OBTENER DATOS
        $matricula_alumno = $_SESSION['matricula'];
        $cuatrimestre = $_POST['cuatrimestre'] ?? ''; 
        $programa_educativo = $_POST['programa_educativo'] ?? ''; 

        if(empty($cuatrimestre) || empty($programa_educativo)) { throw new Exception("Faltan datos del formulario."); }

        if (!isset($_FILES['memoria_archivo']) || $_FILES['memoria_archivo']['error'] != 0) { throw new Exception("No se recibió ningún archivo válido."); }
        $archivo = $_FILES['memoria_archivo'];
        $ruta_temporal = $archivo['tmp_name'];
        
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        if ($finfo->file($ruta_temporal) != "application/pdf") { throw new Exception("El archivo no es un PDF válido."); }

        // C. RENOMBRAR Y MOVER LOCALMENTE
        $nivel = (strpos($cuatrimestre, '6to') !== false) ? "TSU" : "ING";
        $carrera_saneada = preg_replace('/[^A-Za-z0-9]/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $programa_educativo));
        $nuevo_nombre_archivo = $matricula_alumno . "_ESTADIA_" . $nivel . "_" . $carrera_saneada . ".pdf";
        
        $target_dir = "../uploads/temp/";
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
        $ruta_final = $target_dir . $nuevo_nombre_archivo;

        if (move_uploaded_file($ruta_temporal, $ruta_final)) {
            $link_google_drive = NULL;
            // SUBIR A GOOGLE DRIVE
            $client = new Google\Client();
            $client->setApplicationName('ProyectoUSB Uploader');
            $client->setScopes(Google\Service\Drive::DRIVE_FILE);
            $client->setAuthConfig(__DIR__ . '/../client_secret.json');
            $client->setAccessType('offline');

            $tokenPath = __DIR__ . '/token.json';
            if (!file_exists($tokenPath)) { throw new Exception("Falta token.json de Google."); }
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
            if ($client->isAccessTokenExpired()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                file_put_contents($tokenPath, json_encode($client->getAccessToken()));
            }

            $driveService = new Google\Service\Drive($client);
            
            $nombreCarpetaNivel = (strpos($cuatrimestre, '6to') !== false) ? "6to Cuatrimestre" : "11vo Cuatrimestre";
            $idCarpetaNivel = buscarOCrearCarpeta($driveService, $nombreCarpetaNivel, GOOGLE_DRIVE_ROOT_ID);
            $idCarpetaCarrera = buscarOCrearCarpeta($driveService, $programa_educativo, $idCarpetaNivel);

            $fileMetadata = new Google\Service\Drive\DriveFile(array(
                'name' => $nuevo_nombre_archivo, 'parents' => array($idCarpetaCarrera)
            ));
            $content = file_get_contents($ruta_final);
            $gdriveFile = $driveService->files->create($fileMetadata, array(
                'data' => $content, 'mimeType' => 'application/pdf', 'uploadType' => 'multipart'
            ));

            $driveService->getClient()->setUseBatch(false);
            $fileId = $gdriveFile->id;
            $fileDetails = $driveService->files->get($fileId, array('fields' => 'webViewLink'));
            $link_google_drive = $fileDetails->getWebViewLink();
            
            unlink($ruta_final);

            // E. GUARDAR EN BD
            $stmt = $conexion->prepare("INSERT INTO entregas (matricula_alumno, nombre_archivo_subido, cuatrimestre_subido, programa_educativo_subido, link_google_drive) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $matricula_alumno, $nuevo_nombre_archivo, $cuatrimestre, $programa_educativo, $link_google_drive);

            if ($stmt->execute()) {
                
                $url_redireccion = "dashboard.php?status=success&nivel=" . urlencode($nombreCarpetaNivel) . "&carrera=" . urlencode($programa_educativo);
                
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Archivo subido y guardado correctamente.',
                    'redirect_url' => $url_redireccion
                ]);
                
            } else {
                throw new Exception("Error al guardar en la Base de Datos: " . $stmt->error);
            }
            $stmt->close();
        } else {
            throw new Exception("Error al mover el archivo temporalmente en el servidor.");
        }
    }
    $conexion->close();

} catch (Exception $e) {
    // Si hubo un error en cualquier parte del proceso (Drive, BD, Archivo),
    // devolvemos un JSON con el error.
    http_response_code(500); // Código de error del servidor
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
    if (isset($ruta_final) && file_exists($ruta_final)) { unlink($ruta_final); }
    if (isset($conexion)) { $conexion->close(); }
}
?>