<?php
// Cargar las clases necesarias
require_once 'includes/Database.php';
require_once 'includes/User.php';
require_once 'includes/Instructor.php';

// Iniciar sesión
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

// Recuperar el objeto User de la sesión
$user = $_SESSION['user'];

// Verificar que el usuario sea un instructor
if (!($user instanceof User) || $user->getRole() !== 'instructor') {
    // Si no es una instancia de User o no es instructor, intentar recrear el objeto
    if (isset($user->id) && isset($user->username) && isset($user->password) && isset($user->role) && $user->role === 'instructor') {
        $user = new Instructor($user->id, $user->username, $user->password, $user->role);
        $_SESSION['user'] = $user;
    } else {
        // Si no podemos recrear el objeto o no es instructor, redirigir al dashboard
        header("Location: dashboard.php");
        exit();
    }
}

// Obtener las fichas asignadas al instructor
$db = Database::getInstance()->getConnection();
$instructor_id = $user->getId();

$query = "
    SELECT f.id, f.codigo, pf.nombre as programa 
    FROM fichas f
    JOIN programas_formacion pf ON f.programa_id = pf.id
    JOIN instructores_programas ip ON pf.id = ip.programa_id
    WHERE ip.instructor_id = $instructor_id
";
$result = $db->query($query);
$fichas = $result->fetch_all(MYSQLI_ASSOC);

// Procesar el formulario si se envió
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $ficha_id = $_POST['ficha_id'];

    // Validar que la ficha pertenezca al instructor
    $ficha_valida = false;
    foreach ($fichas as $ficha) {
        if ($ficha['id'] == $ficha_id) {
            $ficha_valida = true;
            break;
        }
    }

    if ($ficha_valida) {
        // Registrar el aprendiz
        $stmt = $db->prepare("INSERT INTO aprendices (nombre, ficha_id) VALUES (?, ?)");
        $stmt->bind_param("si", $nombre, $ficha_id);

        if ($stmt->execute()) {
            $mensaje = "Aprendiz registrado correctamente.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error al registrar el aprendiz: " . $db->error;
            $tipo_mensaje = "error";
        }
    } else {
        $mensaje = "La ficha seleccionada no está asignada a este instructor.";
        $tipo_mensaje = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Aprendiz - SENA Asistencias</title>
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
        <h2 class="text-xl font-bold mb-4">Registrar Aprendiz</h2>

        <?php if (!empty($mensaje)): ?>
            <div
                class="mb-4 p-4 <?php echo $tipo_mensaje === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700'; ?> rounded border">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($fichas)): ?>
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 p-4 rounded mb-4">
                No tiene fichas asignadas. Contacte al coordinador para que le asigne fichas.
            </div>
        <?php else: ?>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <form action="registrar_aprendiz.php" method="POST">
                    <div class="mb-4">
                        <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre del Aprendiz</label>
                        <input type="text" name="nombre" id="nombre" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="mb-6">
                        <label for="ficha_id" class="block text-sm font-medium text-gray-700">Ficha</label>
                        <select name="ficha_id" id="ficha_id" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Seleccione una ficha</option>
                            <?php foreach ($fichas as $ficha): ?>
                                <option value="<?php echo $ficha['id']; ?>">
                                    <?php echo $ficha['codigo'] . ' - ' . $ficha['programa']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit"
                        class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Registrar
                        Aprendiz</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>