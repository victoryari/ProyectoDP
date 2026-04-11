<?php
require_once '../../config/database.php';
if (isset($_GET['id'])) {
    $db = (new Database())->getConnection();
    $stmt = $db->prepare("UPDATE prospectos_marketing SET estado = 0 WHERE id_prospecto = :id");
    $stmt->execute([':id' => $_GET['id']]);
    header("Location: index.php");
}
?>