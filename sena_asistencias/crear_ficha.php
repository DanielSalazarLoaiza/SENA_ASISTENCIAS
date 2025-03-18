<?php
// Cargar las clases necesarias antes de iniciar la sesión
require_once 'includes/Database.php';
require_once 'includes/User.php'; // Asegúrate de que User.php esté cargado
require_once 'includes/Coordinator.php';

// Iniciar la sesión después de cargar las clases
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

// Verificar que el objeto en la sesión sea una instancia de User
if (!($_SESSION['user'] instanceof User)) {
    die("Error: El objeto en la sesión no es una instancia de User.");
}

// Verificar el rol del usuario
if ($_SESSION['user']->getRole() !== 'coordinator') {
    header("Location: index.php");
    exit();
}

$db = Database::getInstance()->getConnection();

// Obtener la lista de programas para el select
$programas = $db->query("SELECT id, nombre FROM programas_formacion")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = $_POST['codigo'];
    $programa_id = $_POST['programa_id'];

    $stmt = $db->prepare("INSERT INTO fichas (codigo, programa_id) VALUES (?, ?)");
    $stmt->bind_param("si", $codigo, $programa_id);

    if ($stmt->execute()) {
        echo "<script>alert('Ficha creada exitosamente'); window.location.href = 'dashboard.php';</script>";
    } else {
        echo "<script>alert('Error al crear la ficha');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Ficha</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-96">
        <h1 class="text-2xl font-bold mb-6 text-center">Crear Ficha</h1>
        <form action="crear_ficha.php" method="POST">
            <div class="mb-4">
                <label for="codigo" class="block text-sm font-medium text-gray-700">Código de la Ficha</label>
                <input type="text" name="codigo" id="codigo" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            <div class="mb-6">
                <label for="programa_id" class="block text-sm font-medium text-gray-700">Programa de Formación</label>
                <select name="programa_id" id="programa_id" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                    <option value="">Seleccione un programa</option>
                    <?php foreach ($programas as $programa): ?>
                        <option value="<?php echo $programa['id']; ?>"><?php echo $programa['nombre']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Crear Ficha</button>
        </form>
    </div>
</body>
</html>