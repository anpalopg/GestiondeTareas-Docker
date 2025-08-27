<?php
require_once __DIR__ . '/../config/dirs.php'; // ruta relativa confiable
require_once CONTROLLERS_PATH . '/AuthController.php';

try {
    // Iniciar sesión si no está iniciada
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $auth = new AuthController();
    $auth->logout(); // destruye la sesión y redirige a login

} catch (Exception $e) {
    error_log("Error al cerrar sesión: " . $e->getMessage());
    // Redirección segura en caso de error
    header('Location: ' . BASE_URL . '/app/views/auth/login.php');
    exit;
}
