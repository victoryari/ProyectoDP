<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['usuario_id'])) {
    $database = new Database();
    $db = $database->getConnection();

    $query = "INSERT INTO asignacion_turnos (id_personal, id_usuario_asigno, fecha_turno, hora_inicio, hora_fin, estado) 
              VALUES (:id_personal, :id_usuario, :fecha_turno, :hora_inicio, :hora_fin, 1)";
    
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(':id_personal', $_POST['id_personal']);
    $stmt->bindParam(':id_usuario', $_SESSION['usuario_id']); // El usuario logueado en la sesión
    $stmt->bindParam(':fecha_turno', $_POST['fecha_turno']);
    $stmt->bindParam(':hora_inicio', $_POST['hora_inicio']);
    $stmt->bindParam(':hora_fin', $_POST['hora_fin']);
    
    if($stmt->execute()) {
        header("Location: index.php?msg=success");
    } else {
        header("Location: index.php?msg=error");
    }
}
?>