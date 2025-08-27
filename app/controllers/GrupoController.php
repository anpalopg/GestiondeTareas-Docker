<?php
require_once __DIR__ . '/../models/Grupo.php';
require_once __DIR__ . '/../models/Usuario.php';

class GrupoController {
    private $modelo;
    private $usuarioModelo;

    public function __construct() {
        try {
            $this->modelo = new Grupo();
            $this->usuarioModelo = new Usuario();
            if (session_status() === PHP_SESSION_NONE) session_start();
        } catch (Exception $e) {
            error_log("Error al inicializar GrupoController: " . $e->getMessage());
            throw $e;
        }
    }

    public function obtenerProfesores() {
        try {
            return $this->usuarioModelo->obtenerProfesores();
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function obtenerGrupos() {
        try {
            return $this->modelo->obtenerTodos();
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function obtenerGruposPorProfesor($profesorId) {
        try {
            return $this->modelo->obtenerGruposPorProfesor($profesorId);
        } catch (Exception $e) {
            error_log("Error obteniendo grupos por profesor: " . $e->getMessage());
            return [];
        }
    }

    public function procesarAccion() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return null;
        $accion = $_POST['accion'] ?? '';
        $resultado = ['error' => 'Acción no válida'];

        try {
            switch($accion) {
                case 'crear': $resultado = $this->crearGrupo($_POST); break;
                case 'actualizar': $resultado = $this->actualizarGrupo($_POST); break;
                case 'cambiarEstado': $resultado = $this->cambiarEstado($_POST); break;
                case 'matricular': $resultado = $this->matricularEstudiante($_POST); break;
                case 'desmatricular': $resultado = $this->desmatricularEstudiante($_POST); break;
            }
        } catch (Exception $e) {
            $resultado = ['error' => $e->getMessage()];
        }

        return $resultado;
    }

    private function crearGrupo($datos) {
        if (empty($datos['nombre'])) return ['error' => 'El nombre del grupo es requerido'];
        try {
            $creado = $this->modelo->crearGrupo(
                $datos['nombre'],
                $datos['descripcion'] ?? '',
                $datos['profesor_id'] ?? null
            );
            return $creado ? ['success' => 'Grupo creado correctamente'] : ['error' => 'No se pudo crear el grupo'];
        } catch (Exception $e) {
            return ['error' => 'Error al crear grupo: ' . $e->getMessage()];
        }
    }

    private function actualizarGrupo($datos) {
        if (empty($datos['id']) || empty($datos['nombre'])) return ['error' => 'Datos incompletos'];
        try {
            $actualizado = $this->modelo->actualizarGrupo(
                $datos['id'],
                $datos['nombre'],
                $datos['descripcion'] ?? '',
                $datos['profesor_id'] ?? null
            );
            return $actualizado ? ['success' => 'Grupo actualizado correctamente'] : ['error' => 'No se pudo actualizar el grupo'];
        } catch (Exception $e) {
            return ['error' => 'Error al actualizar grupo: ' . $e->getMessage()];
        }
    }

    private function cambiarEstado($datos) {
        if (empty($datos['id']) || !isset($datos['activo'])) return ['error' => 'Datos incompletos'];
        try {
            $actualizado = $this->modelo->cambiarEstado($datos['id'], $datos['activo']);
            return $actualizado ? ['success' => 'Estado del grupo actualizado correctamente'] : ['error' => 'No se pudo actualizar el estado del grupo'];
        } catch (Exception $e) {
            return ['error' => 'Error al cambiar estado: ' . $e->getMessage()];
        }
    }

    private function matricularEstudiante($datos) {
        if (empty($datos['grupo_id'])) return ['error' => 'ID de grupo no especificado'];
        if (empty($datos['estudiantes'])) return ['error' => 'No se especificaron estudiantes'];

        try {
            $estudiantes = is_array($datos['estudiantes']) ? $datos['estudiantes'] : [$datos['estudiantes']];
            $matriculados = 0;
            $errores = [];

            foreach ($estudiantes as $estudiante_id) {
                if (!is_numeric($estudiante_id)) {
                    $errores[] = "ID de estudiante no válido: $estudiante_id";
                    continue;
                }
                try {
                    if ($this->modelo->matricularEstudiante(intval($datos['grupo_id']), intval($estudiante_id))) {
                        $matriculados++;
                    }
                } catch (Exception $e) {
                    $errores[] = "Error con estudiante $estudiante_id: " . $e->getMessage();
                }
            }

            if ($matriculados === 0 && !empty($errores)) return ['error' => 'Error al matricular estudiantes: ' . implode(", ", $errores)];
            if (!empty($errores)) return ['partial_success' => true, 'message' => "Se matricularon $matriculados estudiantes. Algunos errores: " . implode(", ", $errores)];
            return ['success' => true, 'message' => "Se matricularon $matriculados estudiantes correctamente"];
        } catch (Exception $e) {
            error_log("Error en matriculación: " . $e->getMessage());
            return ['error' => 'Error al matricular estudiantes: ' . $e->getMessage()];
        }
    }

    private function desmatricularEstudiante($datos) {
        if (empty($datos['grupo_id']) || empty($datos['estudiante_id'])) return ['error' => 'Datos incompletos para la desmatriculación'];
        try {
            $desmatriculado = $this->modelo->desmatricularEstudiante($datos['grupo_id'], $datos['estudiante_id']);
            return $desmatriculado ? ['success' => 'Estudiante desmatriculado correctamente'] : ['error' => 'No se pudo desmatricular al estudiante'];
        } catch (Exception $e) {
            return ['error' => 'Error al desmatricular estudiante: ' . $e->getMessage()];
        }
    }

    public function obtenerEstadisticas() {
        try {
            $grupos = $this->modelo->obtenerTodos();
            $gruposActivos = array_filter($grupos, fn($g) => $g['activo'] == 1);
            return [
                'total_grupos' => count($grupos),
                'grupos_activos' => count($gruposActivos),
                'total_estudiantes' => $this->modelo->contarEstudiantes(),
                'total_profesores' => $this->modelo->contarProfesores(),
                'total_materias' => $this->modelo->contarMaterias()
            ];
        } catch (Exception $e) {
            error_log("Error al obtener estadísticas de grupos: " . $e->getMessage());
            return ['total_grupos'=>0,'grupos_activos'=>0,'total_estudiantes'=>0,'total_profesores'=>0,'total_materias'=>0,'error'=>'Error al obtener estadísticas'];
        }
    }

    public function obtenerPorId($id) {
        try {
            return $this->modelo->obtenerPorId($id);
        } catch (Exception $e) {
            throw new Exception("Error al obtener grupo: " . $e->getMessage());
        }
    }
}

