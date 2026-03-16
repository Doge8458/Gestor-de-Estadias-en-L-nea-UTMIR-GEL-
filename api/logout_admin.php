<?php
session_start();
session_unset();
session_destroy();

// Esta ruta te saca de la carpeta "api" y te mete a la carpeta "ADMIN" buscando el archivo "index.html"
header("Location: ../ADMIN/index.html"); 
exit();
?>