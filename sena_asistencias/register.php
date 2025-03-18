<?php
session_start();
require_once 'includes/Database.php';
require_once 'includes/Auth.php';
require_once 'includes/User.php';

// Verificar si el usuario está autenticado y tiene permisos
if (
    !isset($_SESSION['user']) ||
    !($_SESSION['user'] instanceof User) ||
    !in_array($_SESSION['user']->getRole(), ['super_admin', 'coordinator'])
) {
    header("Location: index.php");
    exit();
}

// Generar CSRF Token si no existe
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$error_message = '';
$success_message = '';

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar token CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error_message = "Error de seguridad: token CSRF inválido";
    } else {
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $password = $_POST['password'];
        $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);

        // Validar que no se pueda crear un super_admin
        if ($role === 'super_admin' && $_SESSION['user']->getRole() !== 'super_admin') {
            $error_message = "No tienes permisos para crear un Super Administrador";
        }
        // Validar fortaleza de contraseña
        else if (strlen($password) < 8) {
            $error_message = "La contraseña debe tener al menos 8 caracteres";
        } else {
            // Verificar si el usuario ya existe
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error_message = "El nombre de usuario ya existe";
            } else {
                // Hashear contraseña con algoritmo seguro
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Insertar usuario
                $stmt = $db->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $username, $hashedPassword, $role);

                if ($stmt->execute()) {
                    $success_message = "Usuario registrado exitosamente";
                    // Generar nuevo token CSRF para evitar reenvíos
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    $csrf_token = $_SESSION['csrf_token'];
                } else {
                    $error_message = "Error al registrar el usuario: " . $db->error;
                }
            }
        }
    }
}

// Determinar qué roles puede crear el usuario actual
$allowed_roles = [];
if ($_SESSION['user']->getRole() === 'super_admin') {
    $allowed_roles = ['coordinator', 'instructor'];
} else if ($_SESSION['user']->getRole() === 'coordinator') {
    $allowed_roles = ['instructor'];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - SENA Asistencias</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen py-6">
    <div class="bg-white p-8 rounded-lg shadow-md w-96 max-w-full">
        <h1 class="text-2xl font-bold mb-6 text-center">Registro de Usuario</h1>

        <?php if (!empty($error_message)): ?>
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

            <div class="mb-4">
                <label for="username" class="block text-sm font-medium text-gray-700">Usuario</label>
                <input type="text" name="username" id="username"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    required>
            </div>

            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                <input type="password" name="password" id="password"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    required>
                <p class="text-xs text-gray-500 mt-1">Mínimo 8 caracteres</p>
            </div>

            <div class="mb-6">
                <label for="role" class="block text-sm font-medium text-gray-700">Rol</label>
                <select name="role" id="role"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    required>
                    <?php foreach ($allowed_roles as $role): ?>
                        <option value="<?php echo $role; ?>">
                            <?php echo ucfirst($role); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit"
                class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Registrar</button>
        </form>

        <div class="mt-4 flex justify-between">
            <a href="dashboard.php" class="text-blue-500 hover:underline">Volver al Dashboard</a>
        </div>
    </div>
</body>

</html>