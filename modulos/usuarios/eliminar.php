<?php
session_start();
require_once '../../config/database.php';

if (isset($_GET['id']) && $_SESSION['nombre_rol'] == 'Administrador') {
    $database = new Database();
    $db = $database->getConnection();

    // Soft delete: Cambiamos el estado a 0 (Inactivo)
    $query = "UPDATE usuarios SET estado = 0 WHERE id_usuario = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $_GET['id']);
    
    $stmt->execute();
}
header("Location: index.php");
?>