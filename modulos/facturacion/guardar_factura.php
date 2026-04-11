<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Verificamos explícitamente que la sesión no haya expirado
    if (!isset($_SESSION['usuario_id'])) {
        die("Error: Tu sesión ha expirado. Por favor, vuelve a iniciar sesión.");
    }

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

        // 1. Generar Correlativo Automático Seguro (Manejo de tabla vacía)
        $stmt_corr = $db->query("SELECT MAX(CAST(correlativo AS UNSIGNED)) as max_corr FROM facturas WHERE serie = '$serie'");
        $row_corr = $stmt_corr->fetch();
        $max_corr = isset($row_corr['max_corr']) ? intval($row_corr['max_corr']) : 0;
        $correlativo = str_pad(($max_corr + 1), 7, "0", STR_PAD_LEFT);

        // Calcular Totales con validación para evitar errores fatales (TypeError)
        $subtotal_general = 0;
        foreach ($precios as $index => $precio) {
            $p = is_numeric($precio) ? floatval($precio) : 0;
            $c = is_numeric($cantidades[$index]) ? floatval($cantidades[$index]) : 1;
            $subtotal_general += ($p * $c);
        }
        
        if ($subtotal_general <= 0) {
            throw new Exception("El total de la factura debe ser mayor a 0. Verifica los precios de los servicios.");
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
            $p = is_numeric($precios[$index]) ? floatval($precios[$index]) : 0;
            $c = is_numeric($cantidades[$index]) ? floatval($cantidades[$index]) : 1;
            $subtotal_linea = $c * $p;

            $stmt_det->execute([
                ':id_factura' => $id_factura, ':id_servicio' => $id_servicio, 
                ':cantidad' => $c, ':precio' => $p, ':subtotal' => $subtotal_linea
            ]);
        }

        // 4. Registrar Automáticamente el Ingreso en Finanzas
        $concepto_ingreso = "Pago Factura " . $serie . "-" . $correlativo;
        // Evitamos errores de formato en el campo DATETIME agregando la hora actual
        $fecha_hora = $fecha . " " . date('H:i:s'); 
        $query_ingreso = "INSERT INTO ingresos (id_factura, id_usuario_registro, concepto, monto, metodo_pago, fecha_ingreso, estado) 
                          VALUES (:id_factura, :id_usuario, :concepto, :monto, 'Efectivo', :fecha_hora, 1)";
        $stmt_ing = $db->prepare($query_ingreso);
        $stmt_ing->execute([
            ':id_factura' => $id_factura, ':id_usuario' => $id_usuario, 
            ':concepto' => $concepto_ingreso, ':monto' => $total, ':fecha_hora' => $fecha_hora
        ]);

        // Confirmar Transacción
        $db->commit();
        header("Location: index.php?msg=success");
        exit;

    } catch (Throwable $e) { // Throwable captura tanto Exceptions como Errores de Tipo de PHP
        $db->rollBack(); // Revertir todo si hay error
        die("<div style='color:red; font-family:sans-serif; padding:20px;'><h3>Error al procesar la factura:</h3><p>" . $e->getMessage() . "</p><a href='index.php'>Volver al módulo</a></div>");
    }
} else {
    // Si acceden directamente al archivo sin POST
    header("Location: index.php");
    exit;
}
?>