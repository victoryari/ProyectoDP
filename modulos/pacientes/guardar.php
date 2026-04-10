<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();

    $query = "INSERT INTO pacientes (nombres, apellidos, tipo_documento, numero_documento, fecha_nacimiento, contacto_emergencia, telefono_emergencia, condicion_medica, fecha_ingreso, estado) 
              VALUES (:nombres, :apellidos, :tipo_documento, :numero_documento, :fecha_nacimiento, :contacto_emergencia, :telefono_emergencia, :condicion_medica, :fecha_ingreso, 1)";
    
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(':nombres', $_POST['nombres']);
    $stmt->bindParam(':apellidos', $_POST['apellidos']);
    $stmt->bindParam(':tipo_documento', $_POST['tipo_documento']);
    $stmt->bindParam(':numero_documento', $_POST['numero_documento']);
    $stmt->bindParam(':fecha_nacimiento', $_POST['fecha_nacimiento']);
    $stmt->bindParam(':contacto_emergencia', $_POST['contacto_emergencia']);
    $stmt->bindParam(':telefono_emergencia', $_POST['telefono_emergencia']);
    $stmt->bindParam(':condicion_medica', $_POST['condicion_medica']);
    $stmt->bindParam(':fecha_ingreso', $_POST['fecha_ingreso']);
    
    if($stmt->execute()) {
        header("Location: index.php?msg=success");
    } else {
        header("Location: index.php?msg=error");
    }
}
?>