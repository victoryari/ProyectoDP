<?php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();

    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = "SELECT u.id_usuario, u.nombre_usuario, u.password_hash, u.id_rol, r.nombre_rol 
              FROM usuarios u 
              JOIN roles r ON u.id_rol = r.id_rol 
              WHERE u.nombre_usuario = :username AND u.estado = 1";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch();
        if (password_verify($password, $row['password_hash'])) {
            $_SESSION['usuario_id'] = $row['id_usuario'];
            $_SESSION['nombre_usuario'] = $row['nombre_usuario'];
            $_SESSION['rol_id'] = $row['id_rol'];
            $_SESSION['nombre_rol'] = $row['nombre_rol'];
            
            header("Location: index.php");
            exit;
        }
    }
    header("Location: login.php?error=1");
    exit;
}
?>