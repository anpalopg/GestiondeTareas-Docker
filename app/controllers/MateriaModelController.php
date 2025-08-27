<?php
if (!defined('ROOT_PATH')) {
    require_once(__DIR__ . '/../config/dirs.php'); // Ruta segura relativa
}

require_once(MODELS_PATH . '/MateriaModel.php');

class MateriaModelController {
    private $materiaModel;

    public function __construct() {
        try {
            $this->materiaModel = new MateriaModel();
        } catch (Exception $e) {
            error_log("Error al inicializar MateriaModelController: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene materias asignadas a un estudiante específico
     * @param int $estudiante_id
     * @return array
     */
    public function obtenerMateriasEstudiante(int $estudiante_id): array {
        try {
            return $this->materiaModel->obtenerMateriasPorEstudiante($estudiante_id);
        } catch (Exception $e) {
            error_log("Error obteniendo materias del estudiante: " . $e->getMessage());
            return [];
        }
    }
}
?>