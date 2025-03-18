<?php
// Evitar redeclaración de la clase
if (!class_exists('Instructor')) {
    require_once 'User.php';
    require_once 'Database.php';

    class Instructor extends User
    {
        public function getFichasAsignadas()
        {
            $db = Database::getInstance()->getConnection();
            $instructor_id = $this->getId();

            $query = "
                SELECT f.id, f.codigo, pf.nombre as programa 
                FROM fichas f
                JOIN programas_formacion pf ON f.programa_id = pf.id
                JOIN instructores_programas ip ON pf.id = ip.programa_id
                WHERE ip.instructor_id = $instructor_id
            ";

            $result = $db->query($query);
            return $result->fetch_all(MYSQLI_ASSOC);
        }

        public function getAprendicesPorFicha($ficha_id)
        {
            $db = Database::getInstance()->getConnection();
            $query = "
                SELECT a.id, a.nombre, f.codigo as ficha
                FROM aprendices a
                JOIN fichas f ON a.ficha_id = f.id
                WHERE a.ficha_id = $ficha_id
            ";

            $result = $db->query($query);
            return $result->fetch_all(MYSQLI_ASSOC);
        }

        public function registrarAprendiz($nombre, $ficha_id)
        {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("INSERT INTO aprendices (nombre, ficha_id) VALUES (?, ?)");
            $stmt->bind_param("si", $nombre, $ficha_id);
            return $stmt->execute();
        }

        public function tomarLista($ficha_id, $fecha, $asistencias)
        {
            $db = Database::getInstance()->getConnection();
            $db->begin_transaction();

            try {
                foreach ($asistencias as $aprendiz_id => $asistio) {
                    // Verificar si ya existe un registro para este aprendiz en esta fecha
                    $check = $db->prepare("SELECT id FROM asistencias WHERE fecha = ? AND aprendiz_id = ?");
                    $check->bind_param("si", $fecha, $aprendiz_id);
                    $check->execute();
                    $result = $check->get_result();

                    if ($result->num_rows > 0) {
                        // Actualizar el registro existente
                        $stmt = $db->prepare("UPDATE asistencias SET asistio = ? WHERE fecha = ? AND aprendiz_id = ?");
                        $stmt->bind_param("isi", $asistio, $fecha, $aprendiz_id);
                    } else {
                        // Insertar nuevo registro
                        $stmt = $db->prepare("INSERT INTO asistencias (fecha, aprendiz_id, asistio) VALUES (?, ?, ?)");
                        $stmt->bind_param("sii", $fecha, $aprendiz_id, $asistio);
                    }
                    $stmt->execute();
                }

                $db->commit();
                return true;
            } catch (Exception $e) {
                $db->rollback();
                return false;
            }
        }

        public function getReporteAsistencias($ficha_id = null)
        {
            $db = Database::getInstance()->getConnection();
            $instructor_id = $this->getId();

            $query = "
                SELECT a.id, a.nombre, f.codigo as ficha, COUNT(asist.id) AS faltas
                FROM aprendices a
                JOIN fichas f ON a.ficha_id = f.id
                JOIN programas_formacion pf ON f.programa_id = pf.id
                JOIN instructores_programas ip ON pf.id = ip.programa_id
                LEFT JOIN asistencias asist ON a.id = asist.aprendiz_id AND asist.asistio = 0
                WHERE ip.instructor_id = $instructor_id
            ";

            if ($ficha_id) {
                $query .= " AND f.id = $ficha_id";
            }

            $query .= " GROUP BY a.id HAVING faltas >= 3";

            $result = $db->query($query);
            return $result->fetch_all(MYSQLI_ASSOC);
        }

        // Métodos para serialización
        public function __sleep()
        {
            return ['id', 'username', 'password', 'role'];
        }

        public function __wakeup()
        {
            // No es necesario hacer nada especial aquí
        }
    }
}
?>