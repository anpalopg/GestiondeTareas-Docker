<?php
require_once __DIR__ . '/../config/DbConfig.php';

class EntregaModel {
    private $db;

    public function __construct() {
        $this->db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($this->db->connect_error) {
            die("Error de conexión: " . $this->db->connect_error);
        }

        $this->db->set_charset("utf8mb4");
    }

    public function registrarEntrega($tareaId, $estudianteId, $archivoAdjunto) {
        $sql = "INSERT INTO entregas_tareas (tarea_id, estudiante_id, estado_id, archivo_adjunto) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $estadoId = 2; // 2 = "Entregado"
        $stmt->bind_param("iiis", $tareaId, $estudianteId, $estadoId, $archivoAdjunto);
        $resultado = $stmt->execute();

        if ($resultado) {
            $this->actualizarEstadoTarea($tareaId);
        }

        return $resultado;
    }

    private function actualizarEstadoTarea($tareaId) {
        $sql = "UPDATE tareas SET estado_id = 2 WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $tareaId);
        return $stmt->execute();
    }

    public function contarTodasLasEntregas() {
        $query = "SELECT COUNT(*) as total FROM entregas_tareas";
        $result = $this->db->query($query);
        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }

    public function contarEntregasCalificadas() {
        $sql = "DESCRIBE entregas_tareas";
        $result = $this->db->query($sql);
        $columnName = "calificacion"; // Nombre por defecto

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                if ($row['Field'] === 'nota') {
                    $columnName = "nota";
                    break;
                }
            }
        }

        $query = "SELECT COUNT(*) as total FROM entregas_tareas WHERE $columnName IS NOT NULL";
        $result = $this->db->query($query);
        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }
}
?>