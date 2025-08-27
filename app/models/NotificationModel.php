<?php
require_once __DIR__ . '/../config/DbConfig.php';

class NotificationModel {
    private $ncon;

    public function __construct() {
        date_default_timezone_set('America/Bogota');
        $this->ncon = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $this->ncon->set_charset("utf8mb4");

        if ($this->ncon->connect_error) {
            die("Conexión fallida: " . $this->ncon->connect_error);
        }

        // Configurar zona horaria de MySQL
        $this->ncon->query("SET time_zone = '-05:00'");
    }

    public function obtenerNotificaciones($usuario_id) {
        try {
            $sql = "SELECT id, titulo, mensaje, tarea_id, leida, created_at 
                    FROM notificaciones 
                    WHERE usuario_id = ? 
                    ORDER BY created_at DESC 
                    LIMIT 10";
            $stmt = $this->ncon->prepare($sql);
            if (!$stmt) throw new Exception($this->ncon->error);
            
            $stmt->bind_param("i", $usuario_id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Error obteniendo notificaciones: " . $e->getMessage());
            return [];
        }
    }

    public function marcarComoLeida($id, $usuario_id) {
        try {
            $sql = "UPDATE notificaciones SET leida = 1 WHERE id = ? AND usuario_id = ?";
            $stmt = $this->ncon->prepare($sql);
            if (!$stmt) throw new Exception($this->ncon->error);

            $stmt->bind_param("ii", $id, $usuario_id);
            $stmt->execute();
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Error marcando notificación como leída: " . $e->getMessage());
            return false;
        }
    }

    public function marcarTodasComoLeidas($usuario_id) {
        try {
            $sql = "UPDATE notificaciones SET leida = 1 WHERE usuario_id = ? AND leida = 0";
            $stmt = $this->ncon->prepare($sql);
            if (!$stmt) throw new Exception($this->ncon->error);

            $stmt->bind_param("i", $usuario_id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error marcando todas las notificaciones como leídas: " . $e->getMessage());
            return false;
        }
    }
}
?>
