<?php
require_once '../../config/database.php';

if (isset($_GET['id'])) {
    $db = (new Database())->getConnection();
    $stmt = $db->prepare("SELECT * FROM servicios WHERE id_servicio = :id AND estado = 1");
    $stmt->execute([':id' => $_GET['id']]);
    
    header('Content-Type: application/json');
    echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
}
?>