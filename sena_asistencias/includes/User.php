<?php
// Evitar redeclaración de la clase
if (!class_exists('User')) {
    class User
    {
        protected $id;
        protected $username;
        protected $password;
        protected $role;

        public function __construct($id, $username, $password, $role)
        {
            $this->id = $id;
            $this->username = $username;
            $this->password = $password;
            $this->role = $role;
        }

        public function getId()
        {
            return $this->id;
        }

        public function getUsername()
        {
            return $this->username;
        }

        public function getPassword()
        {
            return $this->password;
        }

        public function getRole()
        {
            return $this->role;
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

        public static function authenticate($username, $password)
        {
            require_once 'Database.php';
            $db = Database::getInstance()->getConnection();

            $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                if (password_verify($password, $user['password'])) {
                    // Crear una instancia básica de User primero
                    $userObj = new User($user['id'], $user['username'], $user['password'], $user['role']);

                    // Luego, según el rol, devolver la instancia específica
                    switch ($user['role']) {
                        case 'super_admin':
                            require_once 'SuperAdmin.php';
                            return new SuperAdmin($user['id'], $user['username'], $user['password'], $user['role']);
                        case 'coordinator':
                            require_once 'Coordinator.php';
                            return new Coordinator($user['id'], $user['username'], $user['password'], $user['role']);
                        case 'instructor':
                            require_once 'Instructor.php';
                            return new Instructor($user['id'], $user['username'], $user['password'], $user['role']);
                        default:
                            return $userObj;
                    }
                }
            }

            return null;
        }
    }
}
?>