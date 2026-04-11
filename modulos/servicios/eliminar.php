<?php
session_start();
require_once '../../config/database.php';

if (isset($_GET['id'])) {
    $db = (new Database())->getConnection();
    $stmt = $db->prepare("UPDATE servicios SET estado = 0 WHERE id_servicio = :id");
    $stmt->execute([':id' => $_GET['id']]);
}
header("Location: index.php?msg=success");
?>