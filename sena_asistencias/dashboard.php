<?php
// Cargar las clases necesarias antes de iniciar la sesión
require_once 'includes/Database.php';
require_once 'includes/User.php';
require_once 'includes/Coordinator.php';
require_once 'includes/Instructor.php';
require_once 'includes/SuperAdmin.php';

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
    if (isset($user->id) && isset($user->username) && isset($user->password) && isset($user->role)) {
        // Recrear el objeto según el rol
        switch ($user->role) {
            case 'super_admin':
                $user = new SuperAdmin($user->id, $user->username, $user->password, $user->role);
                break;
            case 'coordinator':
                $user = new Coordinator($user->id, $user->username, $user->password, $user->role);
                break;
            case 'instructor':
                $user = new Instructor($user->id, $user->username, $user->password, $user->role);
                break;
            default:
                $user = new User($user->id, $user->username, $user->password, $user->role);
                break;
        }
        // Actualizar la sesión con el objeto recreado
        $_SESSION['user'] = $user;
    } else {
        // Si no podemos recrear el objeto, redirigir al login
        session_destroy();
        header("Location: index.php");
        exit();
    }
}

// Inicializar variables para las listas
$programas = [];
$ambientes = [];
$fichas = [];
$instructores = [];
$aprendices = [];
$reportes = [];
$regionales = [];
$centros = [];

// Obtener conexión a la base de datos
$db = Database::getInstance()->getConnection();

// Cargar datos según el rol
if ($user->getRole() === 'coordinator') {
    // Obtener programas directamente de la base de datos
    $query = "SELECT p.id, p.nombre, c.nombre as centro 
             FROM programas_formacion p 
             JOIN centros c ON p.centro_id = c.id";
    $result = $db->query($query);
    if ($result) {
        $programas = $result->fetch_all(MYSQLI_ASSOC);
    }

    // Obtener ambientes directamente de la base de datos
    $query = "SELECT a.id, a.nombre, c.nombre as centro 
             FROM ambientes a 
             JOIN centros c ON a.centro_id = c.id";
    $result = $db->query($query);
    if ($result) {
        $ambientes = $result->fetch_all(MYSQLI_ASSOC);
    }

    // Obtener fichas directamente de la base de datos
    $query = "SELECT f.id, f.codigo, p.nombre as programa 
             FROM fichas f 
             JOIN programas_formacion p ON f.programa_id = p.id";
    $result = $db->query($query);
    if ($result) {
        $fichas = $result->fetch_all(MYSQLI_ASSOC);
    }

    // Obtener instructores directamente de la base de datos
    $query = "SELECT u.id, u.username, p.nombre as programa 
             FROM users u 
             LEFT JOIN instructores_programas ip ON u.id = ip.instructor_id 
             LEFT JOIN programas_formacion p ON ip.programa_id = p.id 
             WHERE u.role = 'instructor'";
    $result = $db->query($query);
    if ($result) {
        $instructores = $result->fetch_all(MYSQLI_ASSOC);
    }
} elseif ($user->getRole() === 'instructor') {
    $instructor_id = $user->getId();

    // Obtener fichas asignadas al instructor directamente de la base de datos
    $query = "
        SELECT f.id, f.codigo, pf.nombre as programa 
        FROM fichas f
        JOIN programas_formacion pf ON f.programa_id = pf.id
        JOIN instructores_programas ip ON pf.id = ip.programa_id
        WHERE ip.instructor_id = $instructor_id
    ";
    $result = $db->query($query);
    if ($result) {
        $fichas = $result->fetch_all(MYSQLI_ASSOC);
    }

    // Obtener aprendices con más de 3 faltas directamente de la base de datos
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
} elseif ($user->getRole() === 'super_admin') {
    // Obtener regionales directamente de la base de datos
    $query = "SELECT * FROM regionales";
    $result = $db->query($query);
    if ($result) {
        $regionales = $result->fetch_all(MYSQLI_ASSOC);
    }

    // Obtener centros directamente de la base de datos
    $query = "SELECT c.id, c.nombre, r.nombre as regional 
             FROM centros c 
             JOIN regionales r ON c.regional_id = r.id";
    $result = $db->query($query);
    if ($result) {
        $centros = $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SENA Asistencias</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">
    <nav class="bg-blue-600 p-4 text-white">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold">SENA Asistencias</h1>
            <a href="logout.php" class="bg-red-500 px-4 py-2 rounded-md hover:bg-red-600">Cerrar Sesión</a>
        </div>
    </nav>

    <div class="container mx-auto p-4">
        <h2 class="text-xl font-bold mb-4">Bienvenido, <?php echo $user->getUsername(); ?></h2>

        <?php if ($user->getRole() === 'super_admin'): ?>
            <!-- Dashboard para Super Admin -->
            <div class="bg-white p-6 rounded-lg shadow-md mb-6">
                <h3 class="text-lg font-bold mb-4">Funciones de Super Administrador</h3>
                <ul class="space-y-2">
                    <li><a href="crear_regional.php" class="text-blue-500 hover:underline">Crear Regional</a></li>
                    <li><a href="crear_centro.php" class="text-blue-500 hover:underline">Crear Centro</a></li>
                    <li><a href="crear_coordinador.php" class="text-blue-500 hover:underline">Crear Coordinador</a></li>
                </ul>
            </div>

            <!-- Listados para Super Admin -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Regionales -->
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-bold mb-4">Regionales</h3>
                    <?php if (empty($regionales)): ?>
                        <p class="text-gray-500">No hay regionales registradas.</p>
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
                                    <?php foreach ($regionales as $regional): ?>
                                        <tr>
                                            <td class="border px-4 py-2"><?php echo $regional['id']; ?></td>
                                            <td class="border px-4 py-2"><?php echo $regional['nombre']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Centros -->
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-bold mb-4">Centros</h3>
                    <?php if (empty($centros)): ?>
                        <p class="text-gray-500">No hay centros registrados.</p>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2 text-left">ID</th>
                                        <th class="px-4 py-2 text-left">Nombre</th>
                                        <th class="px-4 py-2 text-left">Regional</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($centros as $centro): ?>
                                        <tr>
                                            <td class="border px-4 py-2"><?php echo $centro['id']; ?></td>
                                            <td class="border px-4 py-2"><?php echo $centro['nombre']; ?></td>
                                            <td class="border px-4 py-2"><?php echo $centro['regional']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif ($user->getRole() === 'coordinator'): ?>
            <!-- Dashboard para Coordinador -->
            <div class="bg-white p-6 rounded-lg shadow-md mb-6">
                <h3 class="text-lg font-bold mb-4">Funciones de Coordinador</h3>
                <ul class="space-y-2">
                    <li><a href="crear_programa.php" class="text-blue-500 hover:underline">Crear Programa de Formación</a>
                    </li>
                    <li><a href="crear_ambiente.php" class="text-blue-500 hover:underline">Crear Ambiente</a></li>
                    <li><a href="crear_ficha.php" class="text-blue-500 hover:underline">Crear Ficha</a></li>
                    <li><a href="crear_instructor.php" class="text-blue-500 hover:underline">Crear Instructor</a></li>
                <!-- </ul> class="text-blue-500 hover:underline">Crear Instructor</a></li> -->
                </ul>
            </div>

            <!-- Listados para Coordinador -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Programas -->
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-bold mb-4">Programas de Formación</h3>
                    <?php if (empty($programas)): ?>
                        <p class="text-gray-500">No hay programas registrados.</p>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2 text-left">Nombre</th>
                                        <th class="px-4 py-2 text-left">Centro</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($programas as $programa): ?>
                                        <tr>
                                            <td class="border px-4 py-2"><?php echo $programa['nombre']; ?></td>
                                            <td class="border px-4 py-2"><?php echo $programa['centro']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Ambientes -->
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-bold mb-4">Ambientes</h3>
                    <?php if (empty($ambientes)): ?>
                        <p class="text-gray-500">No hay ambientes registrados.</p>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2 text-left">Nombre</th>
                                        <th class="px-4 py-2 text-left">Centro</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ambientes as $ambiente): ?>
                                        <tr>
                                            <td class="border px-4 py-2"><?php echo $ambiente['nombre']; ?></td>
                                            <td class="border px-4 py-2"><?php echo $ambiente['centro']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Fichas -->
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-bold mb-4">Fichas</h3>
                    <?php if (empty($fichas)): ?>
                        <p class="text-gray-500">No hay fichas registradas.</p>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2 text-left">Código</th>
                                        <th class="px-4 py-2 text-left">Programa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($fichas as $ficha): ?>
                                        <tr>
                                            <td class="border px-4 py-2"><?php echo $ficha['codigo']; ?></td>
                                            <td class="border px-4 py-2"><?php echo $ficha['programa']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Instructores -->
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-bold mb-4">Instructores</h3>
                    <?php if (empty($instructores)): ?>
                        <p class="text-gray-500">No hay instructores registrados.</p>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2 text-left">Usuario</th>
                                        <th class="px-4 py-2 text-left">Programa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($instructores as $instructor): ?>
                                        <tr>
                                            <td class="border px-4 py-2"><?php echo $instructor['username']; ?></td>
                                            <td class="border px-4 py-2"><?php echo $instructor['programa']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif ($user->getRole() === 'instructor'): ?>
            <!-- Dashboard para Instructor -->
            <div class="bg-white p-6 rounded-lg shadow-md mb-6">
                <h3 class="text-lg font-bold mb-4">Funciones de Instructor</h3>
                <ul class="space-y-2">
                    <li><a href="tomar_lista.php" class="text-blue-500 hover:underline">Tomar Lista de Asistencia</a></li>
                    <li><a href="registrar_aprendiz.php" class="text-blue-500 hover:underline">Registrar Aprendiz</a></li>
                    <li><a href="ver_reportes.php" class="text-blue-500 hover:underline">Ver Reportes de Asistencias</a>
                    </li>
                </ul>
            </div>

            <!-- Fichas asignadas -->
            <div class="bg-white p-6 rounded-lg shadow-md mb-6">
                <h3 class="text-lg font-bold mb-4">Fichas Asignadas</h3>
                <?php if (empty($fichas)): ?>
                    <p class="text-gray-500">No tiene fichas asignadas.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left">Código</th>
                                    <th class="px-4 py-2 text-left">Programa</th>
                                    <th class="px-4 py-2 text-left">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($fichas as $ficha): ?>
                                    <tr>
                                        <td class="border px-4 py-2"><?php echo $ficha['codigo']; ?></td>
                                        <td class="border px-4 py-2"><?php echo $ficha['programa']; ?></td>
                                        <td class="border px-4 py-2">
                                            <a href="ver_aprendices.php?ficha_id=<?php echo $ficha['id']; ?>"
                                                class="text-blue-500 hover:underline">Ver Aprendices</a>
                                            <a href="tomar_lista.php?ficha_id=<?php echo $ficha['id']; ?>"
                                                class="text-blue-500 hover:underline ml-2">Tomar Lista</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Aprendices con más de 3 faltas -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-lg font-bold mb-4">Aprendices con 3 o más Faltas</h3>
                <?php if (empty($reportes)): ?>
                    <p class="text-gray-500">No hay aprendices con 3 o más faltas.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left">Nombre</th>
                                    <th class="px-4 py-2 text-left">Ficha</th>
                                    <th class="px-4 py-2 text-left">Faltas</th>
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
        <?php endif; ?>
    </div>
</body>

</html>