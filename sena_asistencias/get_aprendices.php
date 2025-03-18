<?php
session_start();
require_once 'includes/Database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']->getRole() !== 'instructor') {
    exit();
}

$db = Database::getInstance()->getConnection();

$ficha_id = $_GET['ficha_id'];
$aprendices = $db->query("
    SELECT id, nombre 
    FROM aprendices 
    WHERE ficha_id = $ficha_id
")->fetch_all(MYSQLI_ASSOC);

foreach ($aprendices as $aprendiz): ?>
    <div class="mb-2">
        <label class="flex items-center">
            <input type="checkbox" name="asistio[<?php echo $aprendiz['id']; ?>]" class="mr-2">
            <?php echo $aprendiz['nombre']; ?>
        </label>
    </div>
<?php endforeach; ?>