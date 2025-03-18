<?php
// Cargar las clases necesarias
require_once 'includes/Database.php';
require_once 'includes/User.php';
require_once 'includes/Coordinator.php';

// Iniciar sesión
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

// Recuperar el objeto User de la sesión
$user = $_SESSION['user'];

// Verificar que el usuario sea un coordinador
if (!($user instanceof User) || $user->getRole() !== 'coordinator') {
    // Si no es una instancia de User o no es coordinador, intentar recrear el objeto
    if (isset($user->id) && isset($user->username) && isset($user->password) && isset($user->role) && $user->role === 'coordinator') {
        $user = new Coordinator($user->id, $user->username, $user->password, $user->role);
        $_SESSION['user'] = $user;
    } else {
        // Si no podemos recrear el objeto o no es coordinador, redirigir al dashboard
        header("Location: dashboard.php");
        exit();
    }
}

// Obtener los programas de formación
$db = Database::getInstance()->getConnection();
$query = "SELECT p.id, p.nombre, c.nombre as centro 
         FROM programas_formacion p 
         JOIN centros c ON p.centro_id = c.id";
$result = $db->query($query);
$programas = $result->fetch_all(MYSQLI_ASSOC);

// Procesar el formulario si se envió
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $programa_id = $_POST['programa_id'];

    // Verificar si el usuario ya existe
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $mensaje = "El nombre de usuario ya existe. Por favor, elija otro.";
        $tipo_mensaje = "error";
    } else {
        // Crear el instructor
        if ($user->crearInstructor($username, $password, $programa_id)) {
            $mensaje = "Instructor creado correctamente.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error al crear el instructor.";
            $tipo_mensaje = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Instructor - SENA Asistencias</title>
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
        <h2 class="text-xl font-bold mb-4">Crear Instructor</h2>

        <?php if (!empty($mensaje)): ?>
            <div
                class="mb-4 p-4 <?php echo $tipo_mensaje === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700'; ?> rounded border">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($programas)): ?>
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 p-4 rounded mb-4">
                No hay programas de formación registrados. Primero debe crear un programa.
            </div>
        <?php else: ?>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <form action="crear_instructor.php" method="POST">
                    <div class="mb-4">
                        <label for="username" class="block text-sm font-medium text-gray-700">Nombre de Usuario</label>
                        <input type="text" name="username" id="username" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                        <input type="password" name="password" id="password" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="mb-6">
                        <label for="programa_id" class="block text-sm font-medium text-gray-700">Programa de
                            Formación</label>
                        <select name="programa_id" id="programa_id" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Seleccione un programa</option>
                            <?php foreach ($programas as $programa): ?>
                                <option value="<?php echo $programa['id']; ?>">
                                    <?php echo $programa['nombre'] . ' - ' . $programa['centro']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit"
                        class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Crear
                        Instructor</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>