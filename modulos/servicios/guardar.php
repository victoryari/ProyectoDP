<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = (new database())->getConnection();

    $query = "INSERT INTO servicios (codigo_servicio, nombre_servicio, descripcion, precio_referencial, estado) 
              VALUES (:codigo, :nombre, :desc, :precio, 1)";
    $stmt = $db->prepare($query);
    
    $stmt->execute([
        ':codigo' => $_POST['codigo_servicio'],
        ':nombre' => $_POST['nombre_servicio'],
        ':desc'   => $_POST['descripcion'],
        ':precio' => $_POST['precio_referencial']
    ]);
    
    header("Location: index.php?msg=success");
}
?>