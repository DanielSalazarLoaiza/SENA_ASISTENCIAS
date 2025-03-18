<?php
// Cargar las clases necesarias antes de iniciar la sesión
require_once 'includes/Database.php';
require_once 'includes/User.php';
require_once 'includes/Instructor.php';

// Iniciar la sesión después de cargar las clases
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

// Recuperar el objeto User de la sesión
$user = $_SESSION['user'];

// Verificar que el objeto sea una instancia de User o recrearlo si es necesario
if (!($user instanceof User)) {
    // Si no es una instancia de User pero tiene los datos necesarios, recreamos el objeto
    if (isset($user->id) && isset($user->username) && isset($user->password) && isset($user->role) && $user->role === 'instructor') {
        $user = new Instructor($user->id, $user->username, $user->password, $user->role);
        $_SESSION['user'] = $user;
    } else {
        // Si no podemos recrear el objeto, redirigir al login
        session_destroy();
        header("Location: index.php");
        exit();
    }
}

// Verificar el rol del usuario
if ($user->getRole() !== 'instructor') {
    header("Location: index.php");
    exit();
}

// Verificar que se haya proporcionado un ID de ficha
if (!isset($_GET['ficha_id'])) {
    header("Location: dashboard.php");
    exit();
}

$ficha_id = $_GET['ficha_id'];
$db = Database::getInstance()->getConnection();

// Obtener los aprendices de la ficha
$query = "
    SELECT a.id, a.nombre, f.codigo as ficha
    FROM aprendices a
    JOIN fichas f ON a.ficha_id = f.id
    WHERE a.ficha_id = $ficha_id
";
$result = $db->query($query);
$aprendices = [];
if ($result) {
    $aprendices = $result->fetch_all(MYSQLI_ASSOC);
}

// Obtener información de la ficha
$ficha_info = $db->query("
    SELECT f.codigo, p.nombre as programa
    FROM fichas f
    JOIN programas_formacion p ON f.programa_id = p.id
    WHERE f.id = $ficha_id
")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Aprendices</title>
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
        <h1 class="text-2xl font-bold mb-6">Aprendices de la Ficha <?php echo $ficha_info['codigo']; ?></h1>
        <p class="mb-4"><strong>Programa:</strong> <?php echo $ficha_info['programa']; ?></p>

        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
            <h2 class="text-xl font-bold mb-4">Lista de Aprendices</h2>
            <?php if (empty($aprendices)): ?>
                <p class="text-gray-500">No hay aprendices registrados en esta ficha.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left">ID</th>
                                <th class="px-4 py-2 text-left">Nombre</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($aprendices as $aprendiz): ?>
                                <tr>
                                    <td class="border px-4 py-2"><?php echo $aprendiz['id']; ?></td>
                                    <td class="border px-4 py-2"><?php echo $aprendiz['nombre']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="flex justify-between">
            <a href="registrar_aprendiz.php"
                class="bg-green-500 text-white py-2 px-4 rounded-md hover:bg-green-600">Registrar Nuevo Aprendiz</a>
            <a href="tomar_lista.php?ficha_id=<?php echo $ficha_id; ?>"
                class="bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600">Tomar Lista</a>
        </div>
    </div>
</body>

</html>