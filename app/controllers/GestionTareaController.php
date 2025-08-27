<?php
// Rutas relativas usando __DIR__
require_once __DIR__ . '/TareaController.php';
require_once __DIR__ . '/MateriaController.php';
require_once __DIR__ . '/GrupoController.php';
require_once __DIR__ . '/../models/TareaModel.php';

class GestionTareaController {
    private $tareaController;
    private $materiaController;
    private $grupoController;
    private $tareaModel;
    
    public function __construct() {
        $this->tareaController = new TareaController();
        $this->materiaController = new MateriaController();
        $this->grupoController = new GrupoController();
        $this->tareaModel = new TareaModel();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function procesarCreacionTarea($datos) {
        if (!isset($_SESSION['user_id'])) {
            return ['error' => 'Usuario no autenticado'];
        }
        $datos['profesor_id'] = $_SESSION['user_id'];
        return $this->tareaController->crearTarea($datos);
    }

    public function procesarAccion() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return null;
        $accion = $_POST['accion'] ?? '';
        $resultado = ['error' => 'Acción no válida'];

        switch($accion) {
            case 'crear':
                $resultado = $this->crearTarea($_POST);
                break;
            case 'calificar':
                $resultado = $this->calificarEntrega($_POST);
                break;
            case 'cambiarEstado':
                $resultado = $this->cambiarEstadoTarea($_POST);
                break;
            case 'actualizar':
                $resultado = $this->actualizarTarea($_POST);
                break;
            case 'eliminar':
                $resultado = $this->eliminarTarea($_POST['id'] ?? null);
                break;
        }

        return $resultado;
    }

    private function eliminarTarea($tareaId) {
        if (!isset($_SESSION['user_id'])) return ['error' => 'Usuario no autenticado'];
        return $this->tareaController->eliminarTarea($tareaId);
    }

    private function actualizarTarea($datos) {
        if (!isset($_SESSION['user_id'])) return ['error' => 'Usuario no autenticado'];
        return $this->tareaController->actualizarTarea($datos);
    }

    private function crearTarea($datos) {
        if (!isset($_SESSION['user_id'])) return ['error' => 'Usuario no autenticado'];
        $datos['profesor_id'] = $_SESSION['user_id'];
        return $this->tareaController->crearTarea($datos);
    }

    private function calificarEntrega($datos) {
        return $this->tareaController->calificarEntrega($datos);
    }

    private function cambiarEstadoTarea($datos) {
        return $this->tareaController->cambiarEstadoTarea($datos);
    }

    public function getDatosFormulario() {
        $profesorId = $_SESSION['user_id'] ?? null;
        if (!$profesorId) return ['error'=>'Usuario no autenticado', 'grupos'=>[], 'materias'=>[]];
        $grupos = $this->grupoController->obtenerGruposPorProfesor($profesorId);
        $materias = $this->materiaController->obtenerMateriasPorProfesor($profesorId);
        return ['grupos'=>$grupos, 'materias'=>$materias];
    }

    public function obtenerTareasActivas() {
        return $this->tareaModel->getTareasActivas();
    }

    public function obtenerTodasLasTareas() {
        return $this->tareaModel->getTodasLasTareasConDetalles();
    }
}

// Procesar solicitudes AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    $gestionTareaController = new GestionTareaController();
    $resultado = $gestionTareaController->procesarAccion();
    echo json_encode($resultado);
    exit;
}
?>
