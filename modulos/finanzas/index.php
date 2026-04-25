<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';

$db = (new database())->getConnection();

// Consulta combinada de Ingresos y Egresos activos (estado = 1)
$movimientos = $db->query("
    (SELECT 'Ingreso' as tipo, concepto, monto, fecha_ingreso as fecha FROM ingresos WHERE estado = 1)
    UNION ALL
    (SELECT 'Egreso' as tipo, concepto, monto, fecha_egreso as fecha FROM egresos WHERE estado = 1)
    ORDER BY fecha DESC
")->fetchAll();

$total_ingresos = 0;
$total_egresos = 0;

foreach($movimientos as $m) {
    if($m['tipo'] == 'Ingreso') {
        $total_ingresos += $m['monto'];
    } else {
        $total_egresos += $m['monto'];
    }
}
$saldo_actual = $total_ingresos - $total_egresos;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Control de Caja</h2>
    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalEgreso">
        <i class="fas fa-minus-circle"></i> Registrar Egreso (Gasto)
    </button>
</div>

<?php if(isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> El registro se ha guardado correctamente.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-left-success shadow-sm py-2 border-0 border-start border-success border-4">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Ingresos</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">S/ <?php echo number_format($total_ingresos, 2); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-left-danger shadow-sm py-2 border-0 border-start border-danger border-4">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Egresos</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">S/ <?php echo number_format($total_egresos, 2); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-left-primary shadow-sm py-2 border-0 border-start border-primary border-4 bg-light">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Saldo Actual</div>
                <div class="h4 mb-0 fw-bold <?php echo $saldo_actual >= 0 ? 'text-primary' : 'text-danger'; ?>">
                    S/ <?php echo number_format($saldo_actual, 2); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <h5 class="card-title mb-3 text-muted">Historial de Movimientos</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
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
                        <td><strong><?php echo date('d/m/Y', strtotime($m['fecha'])); ?></strong> <br><small class="text-muted"><?php echo date('H:i', strtotime($m['fecha'])); ?></small></td>
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
                    
                    <?php if(empty($movimientos)): ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">No hay movimientos registrados en caja.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEgreso" tabindex="-1">
    <div class="modal-dialog">
        <form action="guardar_egreso.php" method="POST">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-minus-circle"></i> Registrar Gasto / Egreso</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Concepto / Detalle del Gasto</label>
                        <input type="text" name="concepto" class="form-control" required placeholder="Ej: Pago de luz mensual...">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Categoría</label>
                            <select name="categoria" class="form-select" required>
                                <option value="Servicios Básicos">Servicios Básicos (Luz, Agua)</option>
                                <option value="Insumos Médicos">Insumos Médicos</option>
                                <option value="Alimentación">Alimentación</option>
                                <option value="Planilla / Personal">Planilla / Personal</option>
                                <option value="Mantenimiento">Mantenimiento</option>
                                <option value="Otros Gastos">Otros Gastos</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Monto (S/)</label>
                            <input type="number" name="monto" class="form-control" step="0.01" min="0.1" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Método de Pago</label>
                            <select name="metodo_pago" class="form-select" required>
                                <option value="Efectivo">Efectivo</option>
                                <option value="Transferencia">Transferencia Bancaria</option>
                                <option value="Yape/Plin">Yape / Plin</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha y Hora</label>
                            <input type="datetime-local" name="fecha_egreso" class="form-control" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nro. de Comprobante / Referencia (Opcional)</label>
                        <input type="text" name="comprobante_referencia" class="form-control" placeholder="Ej: Factura F001-1234">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Guardar Egreso</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>