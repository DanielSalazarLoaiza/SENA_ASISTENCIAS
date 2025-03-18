<?php
require_once 'Database.php';

class Report {
    public static function getAprendicesConMasDeTresFaltas() {
        $db = Database::getInstance()->getConnection();
        $query = "
            SELECT a.nombre, f.codigo, COUNT(asist.id) AS faltas
            FROM aprendices a
            JOIN asistencias asist ON a.id = asist.aprendiz_id
            JOIN fichas f ON a.ficha_id = f.id
            WHERE asist.asistio = FALSE
            GROUP BY a.id
            HAVING faltas > 3
        ";
        $result = $db->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>