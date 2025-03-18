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

// Obtener reportes de asistencias
$reportes = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ficha_id = $_POST['ficha_id'];

    $query = "
        SELECT a.id, a.nombre, f.codigo as ficha, COUNT(asist.id) AS faltas
        FROM aprendices a
        JOIN fichas f ON a.ficha_id = f.id
        JOIN programas_formacion pf ON f.programa_id = pf.id
        JOIN instructores_programas ip ON pf.id = ip.programa_id
        LEFT JOIN asistencias asist ON a.id = asist.aprendiz_id AND asist.asistio = 0
        WHERE ip.instructor_id = $instructor_id AND f.id = $ficha_id
        GROUP BY a.id
        HAVING faltas >= 3
    ";
    $result = $db->query($query);
    if ($result) {
        $reportes = $result->fetch_all(MYSQLI_ASSOC);
    }
} else {
    // Mostrar todos los aprendices con faltas por defecto
    $query = "
        SELECT a.id, a.nombre, f.codigo as ficha, COUNT(asist.id) AS faltas
        FROM aprendices a
        JOIN fichas f ON a.ficha_id = f.id
        JOIN programas_formacion pf ON f.programa_id = pf.id
        JOIN instructores_programas ip ON pf.id = ip.programa_id
        LEFT JOIN asistencias asist ON a.id = asist.aprendiz_id AND asist.asistio = 0
        WHERE ip.instructor_id = $instructor_id
        GROUP BY a.id
        HAVING faltas >= 3
    ";
    $result = $db->query($query);
    if ($result) {
        $reportes = $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Reportes de Asistencias</title>
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
        <h1 class="text-2xl font-bold mb-6 text-center">Reportes de Asistencias</h1>
        <form action="ver_reportes.php" method="POST" class="mb-6">
            <div class="mb-4">
                <label for="ficha_id" class="block text-sm font-medium text-gray-700">Ficha</label>
                <select name="ficha_id" id="ficha_id"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    required>
                    <option value="">Seleccione una ficha</option>
                    <?php foreach ($fichas as $ficha): ?>
                        <option value="<?php echo $ficha['id']; ?>"><?php echo $ficha['codigo']; ?> -
                            <?php echo $ficha['programa']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit"
                class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Filtrar
                por Ficha</button>
        </form>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-bold mb-4">Aprendices con 3 o más faltas</h2>
            <?php if (empty($reportes)): ?>
                <p class="text-gray-500">No hay aprendices con 3 o más faltas.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr>
                                <th class="px-4 py-2">Nombre</th>
                                <th class="px-4 py-2">Ficha</th>
                                <th class="px-4 py-2">Faltas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reportes as $reporte): ?>
                                <tr>
                                    <td class="border px-4 py-2"><?php echo $reporte['nombre']; ?></td>
                                    <td class="border px-4 py-2"><?php echo $reporte['ficha']; ?></td>
                                    <td class="border px-4 py-2"><?php echo $reporte['faltas']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>

