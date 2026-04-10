<?php
session_start();
require_once '../../config/database.php';

if (isset($_GET['id'])) {
    $database = new Database();
    $db = $database->getConnection();

    // Actualizamos el estado a 0 (Inactivo/Dado de baja) en lugar de eliminar el registro
    $query = "UPDATE pacientes SET estado = 0 WHERE id_paciente = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $_GET['id']);
    
    $stmt->execute();
}
header("Location: index.php");
?>