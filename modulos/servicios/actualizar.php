<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['id_servicio'])) {
    $db = (new database())->getConnection();

    $query = "UPDATE servicios SET 
                codigo_servicio = :codigo, 
                nombre_servicio = :nombre, 
                descripcion = :desc, 
                precio_referencial = :precio 
              WHERE id_servicio = :id";
              
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':codigo' => $_POST['codigo_servicio'],
        ':nombre' => $_POST['nombre_servicio'],
        ':desc'   => $_POST['descripcion'],
        ':precio' => $_POST['precio_referencial'],
        ':id'     => $_POST['id_servicio']
    ]);
    
    header("Location: index.php?msg=success");
}
?>