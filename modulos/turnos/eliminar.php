<?php
session_start();
require_once '../../config/database.php';

if (isset($_GET['id'])) {
    $database = new database();
    $db = $database->getConnection();

    // Cambiamos el estado a 0 para cancelar el turno sin borrar el registro histórico
    $query = "UPDATE asignacion_turnos SET estado = 0 WHERE id_asignacion = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $_GET['id']);
    $stmt->execute();
}
header("Location: index.php");
?>