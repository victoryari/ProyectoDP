<?php
session_start();
require_once '../../config/database.php';

// Validamos que exista el ID en la petición
if (isset($_GET['id'])) {
    $database = new database();
    $db = $database->getConnection();

    $query = "SELECT * FROM pacientes WHERE id_paciente = :id AND estado = 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $_GET['id']);
    
    if ($stmt->execute()) {
        $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Devolvemos el resultado como un objeto JSON
        header('Content-Type: application/json');
        echo json_encode($paciente);
        exit;
    }
}

// Si hay error o no hay ID, devolvemos un JSON vacío o un mensaje de error
header('Content-Type: application/json');
echo json_encode(["error" => "No se encontraron datos"]);
?>