<?php
// Cargar las clases necesarias antes de iniciar la sesión
require_once 'includes/Database.php';
require_once 'includes/User.php'; // Asegúrate de que User.php esté cargado
require_once 'includes/Coordinator.php';

// Iniciar la sesión después de cargar las clases
session_start();

// Verificar si el usuario está autenticado y es un coordinador
if (!isset($_SESSION['user']) || $_SESSION['user']->getRole() !== 'coordinator') {
    header("Location: index.php");
    exit();
}

$db = Database::getInstance()->getConnection();

// Obtener la lista de centros para el select
$centros = $db->query("SELECT id, nombre FROM centros")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $centro_id = $_POST['centro_id'];

    $stmt = $db->prepare("INSERT INTO programas_formacion (nombre, centro_id) VALUES (?, ?)");
    $stmt->bind_param("si", $nombre, $centro_id);

    if ($stmt->execute()) {
        echo "<script>alert('Programa creado exitosamente'); window.location.href = 'dashboard.php';</script>";
    } else {
        echo "<script>alert('Error al crear el programa');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Programa de Formación</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-96">
        <h1 class="text-2xl font-bold mb-6 text-center">Crear Programa de Formación</h1>
        <form action="crear_programa.php" method="POST">
            <div class="mb-4">
                <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre del Programa</label>
                <input type="text" name="nombre" id="nombre" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            <div class="mb-6">
                <label for="centro_id" class="block text-sm font-medium text-gray-700">Centro</label>
                <select name="centro_id" id="centro_id" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                    <option value="">Seleccione un centro</option>
                    <?php foreach ($centros as $centro): ?>
                        <option value="<?php echo $centro['id']; ?>"><?php echo $centro['nombre']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Crear Programa</button>
        </form>
    </div>
</body>
</html>