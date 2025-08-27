<?php
if (!defined('ROOT_PATH')) {
    require_once(__DIR__ . '/../config/dirs.php'); // Ruta relativa segura
}

require_once(MODELS_PATH . '/NotificationModel.php');

class NotificationController {
    private $notificationModel;

    public function __construct() {
        try {
            $this->notificationModel = new NotificationModel();
        } catch (Exception $e) {
            error_log("Error al inicializar NotificationController: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene todas las notificaciones de un usuario
     * @param int $usuario_id
     * @return array
     */
    public function obtenerNotificaciones(int $usuario_id): array {
        try {
            return $this->notificationModel->obtenerNotificaciones($usuario_id);
        } catch (Exception $e) {
            error_log("Error obteniendo notificaciones: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Marca una notificación como leída
     * @param int $id ID de la notificación
     * @param int $usuario_id
     * @return bool
     */
    public function marcarNotificacionLeida(int $id, int $usuario_id): bool {
        try {
            return $this->notificationModel->marcarComoLeida($id, $usuario_id);
        } catch (Exception $e) {
            error_log("Error marcando notificación como leída: " . $e->getMessage());
            return false;
        }
    }
}
?>