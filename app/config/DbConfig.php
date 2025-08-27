<?php
/**
 * Configuración de la base de datos para Docker
 */

if (!defined('ROOT_PATH')) {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/GestiondeTareas/app/config/dirs.php');
}

// Definir constantes de conexión
if (!defined('DB_HOST')) {
    define('DB_HOST', 'db'); // nombre del servicio MySQL en Docker
}
if (!defined('DB_USER')) {
    define('DB_USER', 'user'); // usuario definido en docker-compose
}
if (!defined('DB_PASS')) {
    define('DB_PASS', 'password'); // contraseña definida en docker-compose
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'gestion_tareas'); // nombre de la base de datos
}
?>
