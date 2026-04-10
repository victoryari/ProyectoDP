<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';

$db = (new Database())->getConnection();

// Consulta combinada de Ingresos y Egresos para el reporte
$movimientos = $db->query("
    (SELECT 'Ingreso' as tipo, concepto, monto, fecha_ingreso as fecha FROM ingresos WHERE estado = 1)
    UNION ALL
    (SELECT 'Egreso' as tipo, concepto, monto, fecha_egreso as fecha FROM egresos WHERE estado = 1)
    ORDER BY fecha DESC
")->fetchAll();
?>

<div class="row mb-4">
    <div class="col-md-6"><h2>Control de Caja</h2></div>
    <div class="col-md-6 text-end">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalEgreso">
            <i class="fas fa-minus-circle"></i> Registrar Egreso (Gasto)
        </button>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Concepto</th>
                            <th>Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($movimientos as $m): ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($m['fecha'])); ?></td>
                            <td>
                                <span class="badge <?php echo $m['tipo'] == 'Ingreso' ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo $m['tipo']; ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($m['concepto']); ?></td>
                            <td class="fw-bold <?php echo $m['tipo'] == 'Ingreso' ? 'text-success' : 'text-danger'; ?>">
                                <?php echo $m['tipo'] == 'Ingreso' ? '+' : '-'; ?> S/ <?php echo number_format($m['monto'], 2); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>