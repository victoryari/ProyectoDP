<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['nombre_rol'] == 'Administrador') {
    $database = new Database();
    $db = $database->getConnection();

    $usuario = $_POST['nombre_usuario'];
    $pass_hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $id_rol = $_POST['id_rol'];

    $query = "INSERT INTO usuarios (id_rol, nombre_usuario, password_hash, estado) 
              VALUES (:rol, :user, :pass, 1)";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':rol', $id_rol);
    $stmt->bindParam(':user', $usuario);
    $stmt->bindParam(':pass', $pass_hash);
    
    if($stmt->execute()) {
        header("Location: index.php?res=ok");
    } else {
        header("Location: index.php?res=error");
    }
}
?>