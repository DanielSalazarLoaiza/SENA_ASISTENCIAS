<?php
// Cargar las clases necesarias
require_once 'includes/Database.php';
require_once 'includes/User.php';
require_once 'includes/SuperAdmin.php';

// Iniciar sesión
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

// Recuperar el objeto User de la sesión
$user = $_SESSION['user'];

// Verificar que el usuario sea un super admin
if (!($user instanceof User) || $user->getRole() !== 'super_admin') {
    // Si no es una instancia de User o no es super admin, intentar recrear el objeto
    if (isset($user->id) && isset($user->username) && isset($user->password) && isset($user->role) && $user->role === 'super_admin') {
        $user = new SuperAdmin($user->id, $user->username, $user->password, $user->role);
        $_SESSION['user'] = $user;
    } else {
        // Si no podemos recrear el objeto o no es super admin, redirigir al dashboard
        header("Location: dashboard.php");
        exit();
    }
}

// Procesar el formulario si se envió
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];

    // Crear la regional
    if ($user->crearRegional($nombre)) {
        $mensaje = "Regional creada correctamente.";
        $tipo_mensaje = "success";
    } else {
        $mensaje = "Error al crear la regional.";
        $tipo_mensaje = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Regional - SENA Asistencias</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">
    <nav class="bg-blue-600 p-4 text-white">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold">SENA Asistencias</h1>
            <div>
                <a href="dashboard.php" class="bg-blue-500 px-4 py-2 rounded-md hover:bg-blue-700 mr-2">Dashboard</a>
                <a href="logout.php" class="bg-red-500 px-4 py-2 rounded-md hover:bg-red-600">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-4">
        <h2 class="text-xl font-bold mb-4">Crear Regional</h2>

        <?php if (!empty($mensaje)): ?>
            <div
                class="mb-4 p-4 <?php echo $tipo_mensaje === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700'; ?> rounded border">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <form action="crear_regional.php" method="POST">
                <div class="mb-6">
                    <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre de la Regional</label>
                    <input type="text" name="nombre" id="nombre" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                <button type="submit"
                    class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Crear
                    Regional</button>
            </form>
        </div>
    </div>
</body>

</html>