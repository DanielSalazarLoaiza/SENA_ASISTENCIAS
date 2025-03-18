<?php
// Evitar redeclaración de la clase
if (!class_exists('Coordinator')) {
    require_once 'User.php';
    require_once 'Database.php';

    class Coordinator extends User
    {
        public function crearPrograma($nombre, $centro_id)
        {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("INSERT INTO programas_formacion (nombre, centro_id) VALUES (?, ?)");
            $stmt->bind_param("si", $nombre, $centro_id);
            return $stmt->execute();
        }

        public function crearAmbiente($nombre, $centro_id)
        {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("INSERT INTO ambientes (nombre, centro_id) VALUES (?, ?)");
            $stmt->bind_param("si", $nombre, $centro_id);
            return $stmt->execute();
        }

        public function crearFicha($codigo, $programa_id)
        {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("INSERT INTO fichas (codigo, programa_id) VALUES (?, ?)");
            $stmt->bind_param("si", $codigo, $programa_id);
            return $stmt->execute();
        }

        // Obtener programas
        public function getProgramas()
        {
            $db = Database::getInstance()->getConnection();
            $query = "SELECT p.id, p.nombre, c.nombre as centro 
                     FROM programas_formacion p 
                     JOIN centros c ON p.centro_id = c.id";
            $result = $db->query($query);
            return $result->fetch_all(MYSQLI_ASSOC);
        }

        // Obtener ambientes
        public function getAmbientes()
        {
            $db = Database::getInstance()->getConnection();
            $query = "SELECT a.id, a.nombre, c.nombre as centro 
                     FROM ambientes a 
                     JOIN centros c ON a.centro_id = c.id";
            $result = $db->query($query);
            return $result->fetch_all(MYSQLI_ASSOC);
        }

        // Obtener fichas
        public function getFichas()
        {
            $db = Database::getInstance()->getConnection();
            $query = "SELECT f.id, f.codigo, p.nombre as programa 
                     FROM fichas f 
                     JOIN programas_formacion p ON f.programa_id = p.id";
            $result = $db->query($query);
            return $result->fetch_all(MYSQLI_ASSOC);
        }

        // Obtener instructores
        public function getInstructores()
        {
            $db = Database::getInstance()->getConnection();
            $query = "SELECT u.id, u.username, p.nombre as programa 
                     FROM users u 
                     LEFT JOIN instructores_programas ip ON u.id = ip.instructor_id 
                     LEFT JOIN programas_formacion p ON ip.programa_id = p.id 
                     WHERE u.role = 'instructor'";
            $result = $db->query($query);
            return $result->fetch_all(MYSQLI_ASSOC);
        }

        // Nueva función para crear instructores
        public function crearInstructor($username, $password, $programa_id)
        {
            $db = Database::getInstance()->getConnection();
            $db->begin_transaction();

            try {
                // Crear el usuario con rol instructor
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $role = 'instructor';

                $stmt = $db->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $username, $hashedPassword, $role);
                $stmt->execute();

                $instructor_id = $db->insert_id;

                // Asignar el instructor al programa
                $stmt = $db->prepare("INSERT INTO instructores_programas (instructor_id, programa_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $instructor_id, $programa_id);
                $stmt->execute();

                $db->commit();
                return true;
            } catch (Exception $e) {
                $db->rollback();
                return false;
            }
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