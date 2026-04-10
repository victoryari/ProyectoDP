<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();

    $query = "INSERT INTO personal (nombres, apellidos, tipo_documento, numero_documento, cargo, modalidad_contrato, telefono, estado) 
              VALUES (:nombres, :apellidos, :tipo_documento, :numero_documento, :cargo, :modalidad_contrato, :telefono, 1)";
    
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(':nombres', $_POST['nombres']);
    $stmt->bindParam(':apellidos', $_POST['apellidos']);
    $stmt->bindParam(':tipo_documento', $_POST['tipo_documento']);
    $stmt->bindParam(':numero_documento', $_POST['numero_documento']);
    $stmt->bindParam(':cargo', $_POST['cargo']);
    $stmt->bindParam(':modalidad_contrato', $_POST['modalidad_contrato']);
    $stmt->bindParam(':telefono', $_POST['telefono']);
    
    if($stmt->execute()) {
        header("Location: index.php?msg=success");
    } else {
        header("Location: index.php?msg=error");
    }
}
?>