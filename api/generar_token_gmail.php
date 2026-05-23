<?php
// api/generar_token_gmail.php
require_once __DIR__ . '/../vendor/autoload.php';
session_start();

$client = new Google_Client();
$client->setApplicationName('Raptor GEL - Gestor de Estadías');

// FUNDAMENTO: Solicitamos MÚLTIPLES "Scopes" en un arreglo (Array)
// Esto fusiona los permisos de envío de correos y la gestión de archivos en Drive
$client->setScopes([
    Google_Service_Gmail::GMAIL_SEND,
    Google_Service_Drive::DRIVE
]);

$client->setAuthConfig(__DIR__ . '/../client_secret.json');
$client->setAccessType('offline');
$client->setPrompt('select_account consent');

$httpClient = new \GuzzleHttp\Client(['verify' => false]);
$client->setHttpClient($httpClient);

$redirect_uri = 'http://localhost/Proyecto_GEL/api/generar_token_gmail.php';
$client->setRedirectUri($redirect_uri);

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    
    if (!isset($token['error'])) {
        file_put_contents(__DIR__ . '/token.json', json_encode($token));
        
        echo "<div style='font-family: Montserrat, sans-serif; text-align: center; margin-top: 50px;'>";
        echo "<h2 style='color: #00a859;'>¡Token Maestro Generado!</h2>";
        echo "<p>El archivo <b>token.json</b> ahora tiene permisos combinados para <b>Gmail</b> y <b>Google Drive</b>.</p>";
        echo "<p>Ya puedes cerrar esta pestaña y volver a subir tus archivos.</p>";
        echo "</div>";
    } else {
        echo "<h2>Error al obtener el token:</h2><pre>" . print_r($token, true) . "</pre>";
    }
    exit();
}

$authUrl = $client->createAuthUrl();
echo "<div style='font-family: Montserrat, sans-serif; text-align: center; margin-top: 50px;'>";
echo "<h2 style='color: #2c3e50;'>Actualización de Permisos Requerida</h2>";
echo "<p>Raptor GEL requiere renovar los permisos para gestionar Drive y Gmail simultáneamente.</p>";
echo "<a href='" . filter_var($authUrl, FILTER_SANITIZE_URL) . "' style='display: inline-block; background-color: #3498db; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-weight: bold; margin-top: 20px;'>Autorizar Cuenta de Google</a>";
echo "</div>";
?>