<?php
require_once __DIR__ . '/../config/dirs.php';
require_once CONTROLLERS_PATH . '/AuthController.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Iniciar sesión si aún no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Por favor, complete todos los campos']);
    exit;
}

try {
    $auth = new AuthController();
    $result = $auth->login($email, $password);

    // Si el login fue exitoso, guardar información en sesión
    if (!empty($result['success']) && $result['success'] === true) {
        $_SESSION['user_id'] = $result['user']['id'] ?? null;
        $_SESSION['user_role'] = $result['user']['role'] ?? null;
    }

    echo json_encode($result);
} catch (Exception $e) {
    error_log("Error en el proceso de login: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al procesar la solicitud']);
}
