<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['usuario_id'])) {
        die("Error: Tu sesión ha expirado. Por favor, vuelve a iniciar sesión.");
    }

    $database = new database();
    $db = $database->getConnection();

    try {
        $db->beginTransaction();

        $id_paciente = $_POST['id_paciente'];
        $serie = $_POST['serie'];
        $fecha = $_POST['fecha'];
        $id_usuario = $_SESSION['usuario_id'];
        
        $servicios = $_POST['servicio'];
        $cantidades = $_POST['cantidad'];
        $precios = $_POST['precio'];

        // 1. Generar Correlativo Seguro
        $stmt_corr = $db->query("SELECT MAX(CAST(correlativo AS UNSIGNED)) as max_corr FROM facturas WHERE serie = '$serie'");
        $row_corr = $stmt_corr->fetch();
        $max_corr = isset($row_corr['max_corr']) ? intval($row_corr['max_corr']) : 0;
        $correlativo = str_pad(($max_corr + 1), 7, "0", STR_PAD_LEFT);

        // Calcular Totales con validación
        $subtotal_general = 0;
        foreach ($precios as $index => $precio) {
            $p = is_numeric($precio) ? floatval($precio) : 0;
            $c = is_numeric($cantidades[$index]) ? floatval($cantidades[$index]) : 1;
            $subtotal_general += ($p * $c);
        }
        
        if ($subtotal_general <= 0) {
            throw new Exception("El total de la factura debe ser mayor a 0.");
        }

        $impuestos = 0; 
        $total = $subtotal_general + $impuestos;

        // 2. Insertar Cabecera
        $query_fact = "INSERT INTO facturas (id_paciente, id_usuario_registro, serie, correlativo, fecha_emision, subtotal, impuestos, total, estado) 
                       VALUES (:id_paciente, :id_usuario, :serie, :correlativo, :fecha, :subtotal, :impuestos, :total, 1)";
        $stmt = $db->prepare($query_fact);
        $stmt->execute([
            ':id_paciente' => $id_paciente, ':id_usuario' => $id_usuario, ':serie' => $serie, 
            ':correlativo' => $correlativo, ':fecha' => $fecha, ':subtotal' => $subtotal_general, 
            ':impuestos' => $impuestos, ':total' => $total
        ]);
        
        $id_factura = $db->lastInsertId();

        // 3. Insertar Detalle
        $query_det = "INSERT INTO detalle_factura (id_factura, id_servicio, cantidad, precio_unitario, subtotal, estado) 
                      VALUES (:id_factura, :id_servicio, :cantidad, :precio, :subtotal, 1)";
        $stmt_det = $db->prepare($query_det);

        foreach ($servicios as $index => $id_servicio) {
            $p = is_numeric($precios[$index]) ? floatval($precios[$index]) : 0;
            $c = is_numeric($cantidades[$index]) ? floatval($cantidades[$index]) : 1;
            $subtotal_linea = $c * $p;

            $stmt_det->execute([
                ':id_factura' => $id_factura, ':id_servicio' => $id_servicio, 
                ':cantidad' => $c, ':precio' => $p, ':subtotal' => $subtotal_linea
            ]);
        }

        // 4. Registrar Automáticamente el Ingreso
        $concepto_ingreso = "Pago Factura " . $serie . "-" . $correlativo;
        $fecha_hora = $fecha . " " . date('H:i:s'); 
        
        $query_ingreso = "INSERT INTO ingresos (id_factura, id_usuario_registro, concepto, monto, metodo_pago, fecha_ingreso, estado) 
                          VALUES (:id_factura, :id_usuario, :concepto, :monto, 'Efectivo', :fecha_hora, 1)";
        $stmt_ing = $db->prepare($query_ingreso);
        $stmt_ing->execute([
            ':id_factura' => $id_factura, ':id_usuario' => $id_usuario, 
            ':concepto' => $concepto_ingreso, ':monto' => $total, ':fecha_hora' => $fecha_hora
        ]);

        $db->commit();
        // Redirigimos pasando el ID de la factura generada para lanzar el popup de impresión
header("Location: index.php?msg=success&print_id=" . $id_factura);
        exit;

    } catch (Throwable $e) {
        $db->rollBack();
        die("<div style='color:red; padding:20px; font-family:sans-serif;'><h3>Error al procesar:</h3><p>" . $e->getMessage() . "</p><a href='index.php'>Volver</a></div>");
    }
} else {
    header("Location: index.php");
    exit;
}
?>