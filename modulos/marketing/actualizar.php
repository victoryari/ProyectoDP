<?php
require_once '../../config/database.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = (new Database())->getConnection();
    $stmt = $db->prepare("UPDATE prospectos_marketing SET estado_seguimiento = :estado, interes_mostrado = :notas WHERE id_prospecto = :id");
    $stmt->execute([
        ':estado' => $_POST['estado_seguimiento'],
        ':notas' => $_POST['interes_mostrado'],
        ':id' => $_POST['id_prospecto']
    ]);
    header("Location: index.php");
}
?>