<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_paciente'])) {
    $database = new database();
    $db = $database->getConnection();

    $query = "UPDATE pacientes SET 
                nombres = :nombres, 
                apellidos = :apellidos, 
                tipo_documento = :tipo_documento, 
                numero_documento = :numero_documento,
                fecha_nacimiento = :fecha_nacimiento,
                contacto_emergencia = :contacto_emergencia, 
                telefono_emergencia = :telefono_emergencia,
                fecha_ingreso = :fecha_ingreso,
                condicion_medica = :condicion_medica
              WHERE id_paciente = :id_paciente";
              
    $stmt = $db->prepare($query);
    
    $stmt->execute([
        ':nombres' => $_POST['nombres'],
        ':apellidos' => $_POST['apellidos'],
        ':tipo_documento' => $_POST['tipo_documento'],
        ':numero_documento' => $_POST['numero_documento'],
        ':fecha_nacimiento' => $_POST['fecha_nacimiento'],
        ':contacto_emergencia' => $_POST['contacto_emergencia'],
        ':telefono_emergencia' => $_POST['telefono_emergencia'],
        ':fecha_ingreso' => $_POST['fecha_ingreso'],
        ':condicion_medica' => $_POST['condicion_medica'],
        ':id_paciente' => $_POST['id_paciente']
    ]);
    
    header("Location: index.php?update=success");
} else {
    header("Location: index.php?update=error");
}
?>