<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['usuario_id'])) {
    $db = (new database())->getConnection();

    $query = "INSERT INTO egresos (id_usuario_registro, concepto, categoria, monto, metodo_pago, comprobante_referencia, fecha_egreso, estado) 
              VALUES (:id_usuario, :concepto, :categoria, :monto, :metodo_pago, :comprobante, :fecha, 1)";
    
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':id_usuario' => $_SESSION['usuario_id'],
        ':concepto' => $_POST['concepto'],
        ':categoria' => $_POST['categoria'],
        ':monto' => $_POST['monto'],
        ':metodo_pago' => $_POST['metodo_pago'],
        ':comprobante' => $_POST['comprobante_referencia'],
        ':fecha' => $_POST['fecha_egreso']
    ]);
    
    header("Location: index.php?msg=success");
} else {
    header("Location: index.php");
}
?>