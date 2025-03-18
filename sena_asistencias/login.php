<?php
session_start([
    'cookie_lifetime' => 86400,
    'cookie_httponly' => true,
    'cookie_secure' => true,
    'cookie_samesite' => 'Strict'
]);

require_once 'includes/Auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = $_POST['password'];

    $user = Auth::login($username, $password);

    if ($user) {
        session_regenerate_id(true);
        $_SESSION['user'] = $user;
        header("Location: dashboard.php");
    } else {
        $_SESSION['error'] = "Credenciales inválidas";
        header("Location: index.php");
    }
}
?>