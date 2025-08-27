<?php
// Ajustamos la ruta usando __DIR__ para que funcione en Docker
require_once __DIR__ . '/../models/EntregaModel.php';

class EntregaController {
    private $entregaModel;

    public function __construct() {
        $this->entregaModel = new EntregaModel();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function subirTarea() {
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["archivo"])) {
            $tareaId = $_POST["tarea_id"];
            $estudianteId = $_SESSION["estudiante_id"] ?? null;

            if (!$estudianteId) {
                echo json_encode(["status" => "error", "message" => "Usuario no identificado."]);
                return;
            }

            // Ruta relativa dentro del proyecto
            $directorioSubida = __DIR__ . '/../../uploads/';
            if (!is_dir($directorioSubida)) {
                mkdir($directorioSubida, 0777, true);
            }

            $nombreArchivo = basename($_FILES["archivo"]["name"]);
            $rutaArchivo = $directorioSubida . $nombreArchivo;

            if (move_uploaded_file($_FILES["archivo"]["tmp_name"], $rutaArchivo)) {
                if ($this->entregaModel->registrarEntrega($tareaId, $estudianteId, $nombreArchivo)) {
                    echo json_encode(["status" => "success", "message" => "Tarea entregada correctamente."]);
                } else {
                    echo json_encode(["status" => "error", "message" => "Error al registrar la entrega en la base de datos."]);
                }
            } else {
                echo json_encode(["status" => "error", "message" => "Error al subir el archivo."]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "No se ha recibido ningún archivo."]);
        }
    }
}

// Manejo de la solicitud AJAX
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $controller = new EntregaController();
    $controller->subirTarea();
}
?>