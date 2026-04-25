<?php
session_start();
require_once '../../config/database.php';

if (isset($_GET['id'])) {
    $database = new database();
    $db = $database->getConnection();

    $query = "UPDATE personal SET estado = 0 WHERE id_personal = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $_GET['id']);
    $stmt->execute();
}
header("Location: index.php");
?>