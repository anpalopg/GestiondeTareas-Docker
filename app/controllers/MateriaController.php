<?php
if (!defined('ROOT_PATH')) {
    require_once(__DIR__ . '/../config/dirs.php'); // Rutas relativas seguras
}

require_once(MODELS_PATH . '/Materia.php');

class MateriaController {
    private $modelo;

    public function __construct() {
        try {
            $this->modelo = new Materia();
        } catch (Exception $e) {
            error_log("Error al inicializar MateriaController: " . $e->getMessage());
            throw $e;
        }
    }

    /** Estadísticas de materias */
    public function obtenerEstadisticas(): array {
        try {
            $materias = $this->modelo->obtenerTodas();
            $materiasActivas = array_filter($materias, fn($m) => $m['activo'] == 1);

            return [
                'total_materias'   => count($materias),
                'materias_activas' => count($materiasActivas),
                'total_grupos'     => $this->modelo->contarGruposConMaterias(),
                'total_profesores' => $this->modelo->contarProfesoresConMaterias(),
                'total_tareas'     => $this->modelo->contarTareas()
            ];
        } catch (Exception $e) {
            error_log("Error al obtener estadísticas: " . $e->getMessage());
            return ['error' => 'Error al obtener estadísticas'];
        }
    }

    /** Obtiene todas las materias */
    public function obtenerTodas(): array {
        try {
            return $this->modelo->obtenerTodas();
        } catch (Exception $e) {
            error_log("Error al obtener materias: " . $e->getMessage());
            return [];
        }
    }

    /** Obtiene materia por ID */
    public function obtenerPorId(int $id): ?array {
        try {
            return $this->modelo->obtenerPorId($id);
        } catch (Exception $e) {
            error_log("Error al obtener materia por ID: " . $e->getMessage());
            return null;
        }
    }

    /** Obtiene grupos de una materia */
    public function obtenerGrupos(int $materia_id): array {
        try {
            return $this->modelo->obtenerGrupos($materia_id);
        } catch (Exception $e) {
            error_log("Error al obtener grupos: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /** Obtiene materias asignadas a un profesor */
    public function obtenerMateriasPorProfesor(int $profesorId): array {
        try {
            return $this->modelo->obtenerMateriasPorProfesor($profesorId);
        } catch (Exception $e) {
            error_log("Error obteniendo materias por profesor: " . $e->getMessage());
            return [];
        }
    }

    /** Procesa acción desde POST */
    public function procesarAccion(): array {
        try {
            $accion = $_POST['accion'] ?? '';
            switch ($accion) {
                case 'crear': return $this->crear($_POST);
                case 'actualizar': return $this->actualizar($_POST);
                case 'cambiarEstado': return $this->cambiarEstado($_POST);
                default: return ['error' => 'Acción no válida'];
            }
        } catch (Exception $e) {
            error_log("Error al procesar acción: " . $e->getMessage());
            return ['error' => 'Error al procesar la solicitud'];
        }
    }

    /** Crear materia */
    private function crear(array $datos): array {
        if (empty($datos['nombre']) || empty($datos['codigo'])) {
            return ['error' => 'El nombre y código son obligatorios'];
        }

        try {
            $res = $this->modelo->crear([
                'nombre' => $datos['nombre'],
                'codigo' => $datos['codigo'],
                'descripcion' => $datos['descripcion'] ?? ''
            ]);

            return isset($res['success'])
                ? ['success' => 'Materia creada exitosamente']
                : ['error' => $res['error'] ?? 'Error al crear la materia'];
        } catch (Exception $e) {
            error_log("Error al crear materia: " . $e->getMessage());
            return ['error' => 'Error al crear la materia'];
        }
    }

    /** Actualizar materia */
    private function actualizar(array $datos): array {
        if (empty($datos['id']) || empty($datos['nombre']) || empty($datos['codigo'])) {
            return ['error' => 'El nombre y código son obligatorios'];
        }

        try {
            $res = $this->modelo->actualizar([
                'id' => (int)$datos['id'],
                'nombre' => $datos['nombre'],
                'codigo' => $datos['codigo'],
                'descripcion' => $datos['descripcion'] ?? ''
            ]);

            return $res
                ? ['success' => 'Materia actualizada exitosamente']
                : ['error' => 'Error al actualizar la materia'];
        } catch (Exception $e) {
            error_log("Error al actualizar materia: " . $e->getMessage());
            return ['error' => 'Error al actualizar la materia'];
        }
    }

    /** Cambiar estado de materia */
    private function cambiarEstado(array $datos): array {
        if (empty($datos['id']) || !isset($datos['activo'])) {
            return ['error' => 'Datos incompletos'];
        }

        try {
            $res = $this->modelo->cambiarEstado((int)$datos['id'], (bool)$datos['activo']);
            return $res
                ? ['success' => 'Estado actualizado correctamente']
                : ['error' => 'Error al actualizar el estado'];
        } catch (Exception $e) {
            error_log("Error al cambiar estado: " . $e->getMessage());
            return ['error' => 'Error al actualizar el estado de la materia'];
        }
    }

    /** Obtener materias de un estudiante (compatible Docker) */
    public function obtenerMateriasEstudiante(int $estudianteId): array {
        try {
            if (method_exists($this->modelo, 'obtenerMateriasEstudiante')) {
                return $this->modelo->obtenerMateriasEstudiante($estudianteId);
            }

            // Conexión compatible Docker: usar host de contenedor DB
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            if ($conn->connect_error) throw new Exception($conn->connect_error);

            $sql = "SELECT DISTINCT m.* 
                    FROM materias m
                    INNER JOIN grupo_materia gm ON m.id = gm.materia_id
                    INNER JOIN estudiante_grupo eg ON gm.grupo_id = eg.grupo_id
                    WHERE eg.estudiante_id = ? AND m.activo = 1";

            $stmt = $conn->prepare($sql);
            if (!$stmt) throw new Exception($conn->error);

            $stmt->bind_param("i", $estudianteId);
            $stmt->execute();
            $result = $stmt->get_result();
            $materias = $result->fetch_all(MYSQLI_ASSOC);

            $stmt->close();
            $conn->close();

            return $materias;
        } catch (Exception $e) {
            error_log("Error obteniendo materias del estudiante: " . $e->getMessage());
            return [];
        }
    }
}
