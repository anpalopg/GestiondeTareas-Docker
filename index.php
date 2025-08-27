<?php
// Ajusta la ruta directamente a la carpeta app/config
require_once __DIR__ . '/app/config/dirs.php';

// Solo los controladores que sí existen
require_once __DIR__ . '/app/controllers/AuthController.php';

// Asegurarse de cargar TareaController antes de usarlo
if (defined('CONTROLLERS_PATH') && file_exists(CONTROLLERS_PATH . '/TareaController.php')) {
    require_once(CONTROLLERS_PATH . '/TareaController.php');
} else {
    die('Error crítico: No se encuentra el archivo TareaController.php o CONTROLLERS_PATH no está definido.');
}

// Instancia de controladores
$auth = new AuthController();

// Iniciamos la sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si hay sesión activa
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/app/views/auth/login.php');
    exit();
}

$currentUser = $auth->getCurrentUser();

// Procesar acciones específicas
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'markAllAsRead':
            if (isset($_SESSION['user_id'])) {
                $tareaController = new TareaController();
                $tareaController->marcarTodasComoLeidas($_SESSION['user_id']);
                // Redirigir a la página anterior o a notificaciones
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '?page=notifications'));
                exit();
            }
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión Escolar - Colegio San Francisco de Asís</title>
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/images/favicon.webp">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/style.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/framework.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/darkMode.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/aos.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.1/dist/aos.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/normalize.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4/bootstrap-4.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="dashboard d-flex">
        <!-- Sidebar dinámico -->
        <?php
        switch (strtolower($_SESSION['rol'] ?? '')) {
            case 'estudiante':
                require_once(LAYOUTS_PATH . '/sidebar_student.php');
                break;
            case 'administrador':
                require_once(LAYOUTS_PATH . '/sidebar_admin.php');
                break;
            case 'profesor':
                require_once(LAYOUTS_PATH . '/sidebar_teacher.php');
                break;
            default:
                echo "<p>Error: Rol no reconocido o no definido.</p>";
                break;
        }
        ?>

        <div class="content">
            <?php require_once(LAYOUTS_PATH . '/header.php'); ?>

            <?php
            $page = $_GET['page'] ?? 'dashboard';

            if (($_SESSION['rol'] ?? '') === 'estudiante' && $page === 'task_management') {
                $page = 'task_visualization';
            }

            switch ($page) {
                // Vistas profesor / admin / estudiante
                case 'dashboard':
                    if ($_SESSION['rol'] === 'administrador') {
                        require_once(VIEWS_PATH . '/admin/dashboard.php');
                    } elseif ($_SESSION['rol'] === 'profesor') {
                        require_once(VIEWS_PATH . '/teacher/dashboard.php');
                    } else {
                        echo "<h1 class='position-relative header-page'>BIENVENIDO AL SISTEMA INTERACTIVO PARA ESTUDIANTES DEL COLEGIO SAN FRANCISCO DE ASÍS</h1>";
                    }
                    break;
                case 'task_management':
                    require_once(VIEWS_PATH . '/teacher/task_management.php');
                    break;
                case 'assigned_tasks':
                    require_once(VIEWS_PATH . '/teacher/assigned_tasks.php');
                    break;
                case 'task_submissions':
                    if (!isset($_GET['tarea_id'])) {
                        require_once(VIEWS_PATH . '/teacher/all_task_submissions.php');
                    } else {
                        require_once(VIEWS_PATH . '/teacher/task_submissions.php');
                    }
                    break;
                case 'notifications':
                    if ($_SESSION['rol'] === 'estudiante') {
                        require_once(VIEWS_PATH . '/student/notifications_estu.php');
                    } else {
                        require_once(VIEWS_PATH . '/teacher/notifications.php');
                    }
                    break;
                case 'task_visualization':
                    require_once(VIEWS_PATH . '/student/task_visualization.php');
                    break;
                case 'completed_tasks':
                    require_once(VIEWS_PATH . '/student/completed_tasks.php');
                    break;
                case 'resources':
                    require_once(VIEWS_PATH . '/student/resources.php');
                    break;
                case 'user_management':
                    require_once(VIEWS_PATH . '/admin/user_management.php');
                    break;
                case 'group_management':
                    require_once(VIEWS_PATH . '/admin/group_management.php');
                    break;
                case 'subject_management':
                    require_once(VIEWS_PATH . '/admin/subject_management.php');
                    break;
                case 'system_reports':
                    require_once(VIEWS_PATH . '/admin/system_reports.php');
                    break;
                default:
                    $welcomeMessage = match ($_SESSION['rol'] ?? '') {
                        'estudiante' => 'BIENVENIDO AL SISTEMA INTERACTIVO PARA ESTUDIANTES',
                        'administrador' => 'BIENVENIDO AL PANEL DE ADMINISTRACIÓN',
                        'profesor' => 'BIENVENIDO AL SISTEMA INTERACTIVO PARA PROFESORES',
                        default => 'BIENVENIDO AL SISTEMA'
                    };
                    echo "<h1 class='position-relative header-page'>$welcomeMessage DEL COLEGIO SAN FRANCISCO DE ASÍS</h1>";
                    break;
            }
            ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/js/all.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/js/main.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/js/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.1/dist/aos.js"></script>
    <script>
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(el) { return new bootstrap.Tooltip(el) });

        var dropdownList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
        dropdownList.map(function(el) { return new bootstrap.Dropdown(el) });

        AOS.init({ duration: 800, once: true });
    </script>
    <script>
        function showAlert(title, text, icon) {
            Swal.fire({ title, text, icon, confirmButtonColor: '#0075ff' });
        }
        function confirmAction(title, text, icon) {
            return Swal.fire({
                title, text, icon,
                showCancelButton: true,
                confirmButtonColor: '#0075ff',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Confirmar',
                cancelButtonText: 'Cancelar'
            });
        }
    </script>
</body>
</html>
