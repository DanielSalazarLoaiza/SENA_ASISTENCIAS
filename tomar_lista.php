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

$db = Database::getInstance()->getConnection();
$instructor_id = $user->getId();

// Obtener las fichas asignadas al instructor
$query = "
    SELECT f.id, f.codigo, pf.nombre as programa 
    FROM fichas f
    JOIN programas_formacion pf ON f.programa_id = pf.id
    JOIN instructores_programas ip ON pf.id = ip.programa_id
    WHERE ip.instructor_id = $instructor_id
";
$result = $db->query($query);
$fichas = [];
if ($result) {
    $fichas = $result->fetch_all(MYSQLI_ASSOC);
}

// Preseleccionar ficha si viene por parámetro
$ficha_id_selected = isset($_GET['ficha_id']) ? $_GET['ficha_id'] : '';

// Obtener aprendices si se ha seleccionado una ficha
$aprendices = [];
if ($ficha_id_selected) {
    $query = "
        SELECT a.id, a.nombre, f.codigo as ficha
        FROM aprendices a
        JOIN fichas f ON a.ficha_id = f.id
        WHERE a.ficha_id = $ficha_id_selected
    ";
    $result = $db->query($query);
    if ($result) {
        $aprendices = $result->fetch_all(MYSQLI_ASSOC);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ficha_id = $_POST['ficha_id'];
    $fecha = $_POST['fecha'];

    $db->begin_transaction();

    try {
        foreach ($_POST['aprendiz'] as $aprendiz_id => $asistio) {
            // Verificar si ya existe un registro para este aprendiz en esta fecha
            $check = $db->prepare("SELECT id FROM asistencias WHERE fecha = ? AND aprendiz_id = ?");
            $check->bind_param("si", $fecha, $aprendiz_id);
            $check->execute();
            $result = $check->get_result();

            if ($result->num_rows > 0) {
                // Actualizar el registro existente
                $stmt = $db->prepare("UPDATE asistencias SET asistio = ? WHERE fecha = ? AND aprendiz_id = ?");
                $stmt->bind_param("isi", $asistio, $fecha, $aprendiz_id);
            } else {
                // Insertar nuevo registro
                $stmt = $db->prepare("INSERT INTO asistencias (fecha, aprendiz_id, asistio) VALUES (?, ?, ?)");
                $stmt->bind_param("sii", $fecha, $aprendiz_id, $asistio);
            }
            $stmt->execute();
        }

        $db->commit();
        echo "<script>alert('Lista de asistencia registrada exitosamente'); window.location.href = 'dashboard.php';</script>";
    } catch (Exception $e) {
        $db->rollback();
        echo "<script>alert('Error al registrar la asistencia: " . $e->getMessage() . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tomar Lista de Asistencia</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">
    <nav class="bg-blue-600 p-4 text-white">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold">SENA Asistencias</h1>
            <a href="dashboard.php" class="bg-red-500 px-4 py-2 rounded-md hover:bg-red-600">Volver al Dashboard</a>
        </div>
    </nav>

    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-6 text-center">Tomar Lista de Asistencia</h1>

        <?php if (empty($aprendices) && !$ficha_id_selected): ?>
            <form action="" method="GET" class="mb-6">
                <div class="mb-4">
                    <label for="ficha_id" class="block text-sm font-medium text-gray-700">Ficha</label>
                    <select name="ficha_id" id="ficha_id"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        required>
                        <option value="">Seleccione una ficha</option>
                        <?php foreach ($fichas as $ficha): ?>
                            <option value="<?php echo $ficha['id']; ?>"><?php echo $ficha['codigo']; ?> -
                                <?php echo $ficha['programa']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit"
                    class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Seleccionar
                    Ficha</button>
            </form>
        <?php elseif (empty($aprendices) && $ficha_id_selected): ?>
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6" role="alert">
                <p>No hay aprendices registrados en esta ficha. <a href="registrar_aprendiz.php"
                        class="font-bold underline">Registrar aprendices</a></p>
            </div>
            <a href="tomar_lista.php" class="bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600">Seleccionar otra
                ficha</a>
        <?php else: ?>
            <form action="tomar_lista.php" method="POST">
                <input type="hidden" name="ficha_id" value="<?php echo $ficha_id_selected; ?>">

                <div class="mb-4">
                    <label for="fecha" class="block text-sm font-medium text-gray-700">Fecha</label>
                    <input type="date" name="fecha" id="fecha"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        required value="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="bg-white p-6 rounded-lg shadow-md mb-6">
                    <h2 class="text-xl font-bold mb-4">Lista de Aprendices</h2>
                    <div class="space-y-4">
                        <?php foreach ($aprendices as $aprendiz): ?>
                            <div class="flex items-center justify-between border-b pb-2">
                                <span><?php echo $aprendiz['nombre']; ?></span>
                                <div>
                                    <label class="inline-flex items-center mr-4">
                                        <input type="radio" name="aprendiz[<?php echo $aprendiz['id']; ?>]" value="1"
                                            class="form-radio h-5 w-5 text-blue-600" checked>
                                        <span class="ml-2 text-green-600">Asistió</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="aprendiz[<?php echo $aprendiz['id']; ?>]" value="0"
                                            class="form-radio h-5 w-5 text-red-600">
                                        <span class="ml-2 text-red-600">Faltó</span>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="flex justify-between">
                    <a href="tomar_lista.php" class="bg-gray-500 text-white py-2 px-4 rounded-md hover:bg-gray-600">Cambiar
                        Ficha</a>
                    <button type="submit"
                        class="bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Registrar
                        Asistencia</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>

</html>