<?php
// Evitar redeclaración de la clase
if (!class_exists('SuperAdmin')) {
    require_once 'User.php';
    require_once 'Database.php';

    class SuperAdmin extends User
    {
        public function crearCoordinador($username, $password)
        {
            $db = Database::getInstance()->getConnection();

            // Validar contraseña
            if (strlen($password) < 8) {
                throw new Exception("La contraseña debe tener al menos 8 caracteres");
            }

            // Verificar usuario único
            $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();

            if ($stmt->get_result()->num_rows > 0) {
                throw new Exception("El usuario ya existe");
            }

            // Hashear contraseña
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $role = 'coordinator';

            // Insertar coordinador
            $stmt = $db->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $hashedPassword, $role);

            if (!$stmt->execute()) {
                throw new Exception("Error de base de datos: " . $db->error);
            }

            return true;
        }

        public function crearRegional($nombre)
        {
            require_once 'Database.php';
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("INSERT INTO regionales (nombre) VALUES (?)");
            $stmt->bind_param("s", $nombre);
            return $stmt->execute();
        }

        public function crearCentro($nombre, $regional_id)
        {
            require_once 'Database.php';
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("INSERT INTO centros (nombre, regional_id) VALUES (?, ?)");
            $stmt->bind_param("si", $nombre, $regional_id);
            return $stmt->execute();
        }

        // Obtener regionales
        public function getRegionales()
        {
            require_once 'Database.php';
            $db = Database::getInstance()->getConnection();
            $result = $db->query("SELECT * FROM regionales");
            return $result->fetch_all(MYSQLI_ASSOC);
        }

        // Obtener centros
        public function getCentros()
        {
            require_once 'Database.php';
            $db = Database::getInstance()->getConnection();
            $query = "SELECT c.id, c.nombre, r.nombre as regional 
                     FROM centros c 
                     JOIN regionales r ON c.regional_id = r.id";
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