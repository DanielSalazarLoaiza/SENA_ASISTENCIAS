<?php
require_once 'Database.php';

class Auth
{
    public static function login($username, $password)
    {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $userData = $result->fetch_assoc();
            if (password_verify($password, $userData['password'])) {
                session_regenerate_id(true);
                return self::createUserObject($userData);
            }
        }

        self::logSecurityEvent(null, 'Intento de login fallido', $username);
        return null;
    }

    private static function createUserObject($userData)
    {
        switch ($userData['role']) {
            case 'super_admin':
                require_once 'SuperAdmin.php';
                return new SuperAdmin($userData['id'], $userData['username'], $userData['password'], $userData['role']);
            case 'coordinator':
                require_once 'Coordinator.php';
                return new Coordinator($userData['id'], $userData['username'], $userData['password'], $userData['role']);
            case 'instructor':
                require_once 'Instructor.php';
                return new Instructor($userData['id'], $userData['username'], $userData['password'], $userData['role']);
            default:
                return new User($userData['id'], $userData['username'], $userData['password'], $userData['role']);
        }
    }

    public static function logSecurityEvent($userId, $action, $username = null)
    {
        $db = Database::getInstance()->getConnection();
        $ip = $_SERVER['REMOTE_ADDR'];

        $stmt = $db->prepare("
            INSERT INTO security_logs 
            (user_id, username, action, ip, timestamp) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("isss", $userId, $username, $action, $ip);
        $stmt->execute();
    }
}
?>