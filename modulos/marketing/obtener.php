<?php
require_once '../../config/database.php';
if (isset($_GET['id'])) {
    $db = (new database())->getConnection();
    $stmt = $db->prepare("SELECT id_prospecto, estado_seguimiento, interes_mostrado FROM prospectos_marketing WHERE id_prospecto = :id");
    $stmt->execute([':id' => $_GET['id']]);
    header('Content-Type: application/json');
    echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
}
?>