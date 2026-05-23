<?php
// api/mailer_helpers.php
require_once __DIR__ . '/../vendor/autoload.php';

function obtenerClienteGoogle() {
    $client = new Google_Client();
    $client->setApplicationName('Raptor GEL - Gestor de Estadías');
    $client->setScopes(Google_Service_Gmail::GMAIL_SEND);
    
    // CORRECCIÓN DE RUTA: Subimos un nivel (/../) para encontrar el archivo en la raíz del proyecto
    $client->setAuthConfig(__DIR__ . '/../client_secret.json');
    
    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');

    // PARCHE VITAL PARA XAMPP: Evita el bloqueo "cURL error 60: SSL certificate problem"
    $httpClient = new \GuzzleHttp\Client(['verify' => false]);
    $client->setHttpClient($httpClient);

    // El token.json sí se encuentra dentro de la carpeta api/, por lo que se queda igual
    $tokenPath = __DIR__ . '/token.json';
    if (file_exists($tokenPath)) {
        $accessToken = json_decode(file_get_contents($tokenPath), true);
        $client->setAccessToken($accessToken);
    }
    
    // Verificación de expiración
    if ($client->isAccessTokenExpired()) {
        if ($client->getRefreshToken()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        } else {
            throw new Exception("El archivo token.json ha expirado o está corrupto. Falta el Refresh Token.");
        }
    }
    return $client;
}

function enviarCorreoGmail($destinatario, $asunto, $cuerpoHtml) {
    try {
        $client = obtenerClienteGoogle();
        $service = new Google_Service_Gmail($client);

        // Construir el mensaje
        $strRawMessage = "To: $destinatario\r\n";
        $strRawMessage .= "Subject: =?utf-8?B?" . base64_encode($asunto) . "?=\r\n";
        $strRawMessage .= "MIME-Version: 1.0\r\n";
        $strRawMessage .= "Content-Type: text/html; charset=utf-8\r\n\r\n";
        $strRawMessage .= $cuerpoHtml;

        $mime = rtrim(strtr(base64_encode($strRawMessage), '+/', '-_'), '=');
        $msg = new Google_Service_Gmail_Message();
        $msg->setRaw($mime);

        $service->users_messages->send("me", $msg);
        
        // Retornamos un arreglo detallado
        return ['exito' => true, 'error' => null];
    } catch (Exception $e) {
        // Capturamos el error exacto de Google
        return ['exito' => false, 'error' => $e->getMessage()];
    }
}
?>