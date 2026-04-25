<?php
session_start();
require_once '../../config/database.php';

if (!isset($_GET['id']) || $_SESSION['nombre_rol'] == 'Especialista') {
    die("Acceso denegado o comprobante no encontrado.");
}

$db = (new database())->getConnection();
$id_factura = intval($_GET['id']);

// 1. Obtener datos de la empresa
$empresa = $db->query("SELECT * FROM configuracion_empresa LIMIT 1")->fetch();

// 2. Obtener cabecera de la factura y datos del paciente
$query_factura = "SELECT f.*, p.nombres, p.apellidos, p.numero_documento, p.tipo_documento 
                  FROM facturas f 
                  JOIN pacientes p ON f.id_paciente = p.id_paciente 
                  WHERE f.id_factura = $id_factura";
$factura = $db->query($query_factura)->fetch();

// 3. Obtener el detalle de los servicios
$query_detalle = "SELECT d.*, s.nombre_servicio 
                  FROM detalle_factura d 
                  JOIN servicios s ON d.id_servicio = s.id_servicio 
                  WHERE d.id_factura = $id_factura AND d.estado = 1";
$detalles = $db->query($query_detalle)->fetchAll();

if (!$factura) { die("La factura no existe o ha sido anulada."); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante <?php echo $factura['serie'].'-'.$factura['correlativo']; ?></title>
    <style>
        body { font-family: 'Arial', sans-serif; color: #333; font-size: 14px; }
        .container { width: 800px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #0d6efd; padding-bottom: 10px; margin-bottom: 20px; }
        .empresa-info h2 { margin: 0; color: #0d6efd; }
        .factura-box { border: 1px solid #0d6efd; padding: 15px; text-align: center; border-radius: 5px; }
        .factura-box h3 { margin: 0; font-size: 18px; }
        .factura-box h1 { margin: 5px 0 0 0; font-size: 24px; color: #dc3545; }
        .info-cliente { margin-bottom: 20px; }
        .info-cliente table { width: 100%; }
        .info-cliente td { padding: 5px; }
        .tabla-detalles { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .tabla-detalles th, .tabla-detalles td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        .tabla-detalles th { background-color: #f8f9fa; }
        .text-right { text-align: right; }
        .totales { width: 300px; float: right; border-collapse: collapse; }
        .totales td { padding: 5px 10px; border: 1px solid #ddd; }
        .totales .total-final { font-weight: bold; background-color: #f8f9fa; font-size: 16px; }
        .clear { clear: both; }
        .footer { text-align: center; margin-top: 50px; font-size: 12px; color: #777; }
        
        /* Ocultar botones al imprimir */
        @media print {
            .no-print { display: none; }
            .container { border: none; width: 100%; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align:center; margin-bottom:20px;">
        <button onclick="window.print()" style="padding:10px 20px; background:#0d6efd; color:#fff; border:none; cursor:pointer; font-size:16px;">🖨️ Imprimir / Guardar como PDF</button>
        <button onclick="window.close()" style="padding:10px 20px; background:#6c757d; color:#fff; border:none; cursor:pointer; font-size:16px;">Cerrar</button>
    </div>

    <div class="container">
        <div class="header">
            <div class="empresa-info">
                <h2><?php echo htmlspecialchars($empresa['nombre_comercial'] ?? 'CASA HOGAR'); ?></h2>
                <p><strong>Razón Social:</strong> <?php echo htmlspecialchars($empresa['razon_social'] ?? '-'); ?><br>
                <strong>Dirección:</strong> <?php echo htmlspecialchars($empresa['direccion'] ?? '-'); ?><br>
                <strong>Teléfono:</strong> <?php echo htmlspecialchars($empresa['telefono'] ?? '-'); ?></p>
            </div>
            <div class="factura-box">
                <h3>R.U.C. <?php echo htmlspecialchars($empresa['ruc'] ?? '00000000000'); ?></h3>
                <h3>COMPROBANTE DE PAGO</h3>
                <h1><?php echo $factura['serie'] . '-' . $factura['correlativo']; ?></h1>
            </div>
        </div>

        <div class="info-cliente">
            <table>
                <tr>
                    <td width="15%"><strong>Paciente:</strong></td>
                    <td width="55%"><?php echo htmlspecialchars($factura['apellidos'] . ', ' . $factura['nombres']); ?></td>
                    <td width="15%"><strong>Fecha Emisión:</strong></td>
                    <td width="15%"><?php echo date('d/m/Y', strtotime($factura['fecha_emision'])); ?></td>
                </tr>
                <tr>
                    <td><strong>Documento:</strong></td>
                    <td><?php echo $factura['tipo_documento'] . ' ' . $factura['numero_documento']; ?></td>
                    <td><strong>Moneda:</strong></td>
                    <td>Soles (PEN)</td>
                </tr>
            </table>
        </div>

        <table class="tabla-detalles">
            <thead>
                <tr>
                    <th width="10%">Cant.</th>
                    <th width="60%">Descripción del Servicio</th>
                    <th width="15%" class="text-right">P. Unitario</th>
                    <th width="15%" class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($detalles as $d): ?>
                <tr>
                    <td class="text-center"><?php echo floatval($d['cantidad']); ?></td>
                    <td><?php echo htmlspecialchars($d['nombre_servicio']); ?></td>
                    <td class="text-right">S/ <?php echo number_format($d['precio_unitario'], 2); ?></td>
                    <td class="text-right">S/ <?php echo number_format($d['subtotal'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <table class="totales">
            <tr class="total-final">
                <td class="text-right">TOTAL A PAGAR:</td>
                <td class="text-right">S/ <?php echo number_format($factura['total'], 2); ?></td>
            </tr>
        </table>
        <div class="clear"></div>

        <div class="footer">
            <p>Gracias por confiar en nuestros servicios.</p>
            <p>Este documento es un comprobante de control administrativo interno.</p>
        </div>
    </div>
    
    <script>
        // Opcional: Abrir la ventana de impresión automáticamente al cargar
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>