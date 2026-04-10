<?php
session_start();
require_once '../../config/database.php';

if (isset($_GET['id'])) {
    $db = (new Database())->getConnection();
    $id = $_GET['id'];
    
    // Anulamos la factura, sus detalles y el ingreso en caja asociado
    $db->query("UPDATE facturas SET estado = 0 WHERE id_factura = $id");
    $db->query("UPDATE detalle_factura SET estado = 0 WHERE id_factura = $id");
    $db->query("UPDATE ingresos SET estado = 0 WHERE id_factura = $id");
}
header("Location: index.php");
?>