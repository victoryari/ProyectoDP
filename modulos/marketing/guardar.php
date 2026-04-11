<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = (new Database())->getConnection();
    $query = "INSERT INTO prospectos_marketing (nombre_contacto, telefono, correo, medio_contacto, interes_mostrado, estado_seguimiento, estado) 
              VALUES (:nombre, :tel, :correo, :medio, :interes, :seguimiento, 1)";
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':nombre' => $_POST['nombre_contacto'], ':tel' => $_POST['telefono'],
        ':correo' => $_POST['correo'], ':medio' => $_POST['medio_contacto'],
        ':interes' => $_POST['interes_mostrado'], ':seguimiento' => $_POST['estado_seguimiento']
    ]);
    header("Location: index.php");
}
?>