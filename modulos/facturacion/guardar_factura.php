<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['usuario_id'])) {
    $database = new Database();
    $db = $database->getConnection();

    try {
        // Iniciamos la transacción
        $db->beginTransaction();

        $id_paciente = $_POST['id_paciente'];
        $serie = $_POST['serie'];
        $fecha = $_POST['fecha'];
        $id_usuario = $_SESSION['usuario_id'];
        
        // Arrays de detalles recibidos del formulario
        $servicios = $_POST['servicio'];
        $cantidades = $_POST['cantidad'];
        $precios = $_POST['precio'];

        // 1. Generar Correlativo Automático (Ej: 0000001)
        $stmt_corr = $db->query("SELECT MAX(CAST(correlativo AS UNSIGNED)) as max_corr FROM facturas WHERE serie = '$serie'");
        $row_corr = $stmt_corr->fetch();
        $correlativo = str_pad(($row_corr['max_corr'] + 1), 7, "0", STR_PAD_LEFT);

        // Calcular Totales
        $subtotal_general = 0;
        foreach ($precios as $index => $precio) {
            $subtotal_general += ($precio * $cantidades[$index]);
        }
        $impuestos = 0; // Si aplicara IGV, sería $subtotal_general * 0.18
        $total = $subtotal_general + $impuestos;

        // 2. Insertar Cabecera de Factura
        $query_fact = "INSERT INTO facturas (id_paciente, id_usuario_registro, serie, correlativo, fecha_emision, subtotal, impuestos, total, estado) 
                       VALUES (:id_paciente, :id_usuario, :serie, :correlativo, :fecha, :subtotal, :impuestos, :total, 1)";
        $stmt = $db->prepare($query_fact);
        $stmt->execute([
            ':id_paciente' => $id_paciente, ':id_usuario' => $id_usuario, ':serie' => $serie, 
            ':correlativo' => $correlativo, ':fecha' => $fecha, ':subtotal' => $subtotal_general, 
            ':impuestos' => $impuestos, ':total' => $total
        ]);
        
        $id_factura = $db->lastInsertId();

        // 3. Insertar Detalle de Factura
        $query_det = "INSERT INTO detalle_factura (id_factura, id_servicio, cantidad, precio_unitario, subtotal, estado) 
                      VALUES (:id_factura, :id_servicio, :cantidad, :precio, :subtotal, 1)";
        $stmt_det = $db->prepare($query_det);

        foreach ($servicios as $index => $id_servicio) {
            $cantidad = $cantidades[$index];
            $precio = $precios[$index];
            $subtotal_linea = $cantidad * $precio;

            $stmt_det->execute([
                ':id_factura' => $id_factura, ':id_servicio' => $id_servicio, 
                ':cantidad' => $cantidad, ':precio' => $precio, ':subtotal' => $subtotal_linea
            ]);
        }

        // 4. Registrar Automáticamente el Ingreso en Finanzas
        $concepto_ingreso = "Pago Factura " . $serie . "-" . $correlativo;
        $query_ingreso = "INSERT INTO ingresos (id_factura, id_usuario_registro, concepto, monto, metodo_pago, fecha_ingreso, estado) 
                          VALUES (:id_factura, :id_usuario, :concepto, :monto, 'Efectivo', :fecha, 1)";
        $stmt_ing = $db->prepare($query_ingreso);
        $stmt_ing->execute([
            ':id_factura' => $id_factura, ':id_usuario' => $id_usuario, 
            ':concepto' => $concepto_ingreso, ':monto' => $total, ':fecha' => $fecha
        ]);

        // Confirmar Transacción
        $db->commit();
        header("Location: index.php?msg=success");

    } catch (Exception $e) {
        $db->rollBack(); // Revertir todo si hay error
        die("Error al guardar: " . $e->getMessage());
    }
}
?>