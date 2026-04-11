<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';

// Inicializar la conexión
$db = (new Database())->getConnection();

// Calcular el próximo correlativo para mostrarlo en el modal
$stmt_corr = $db->query("SELECT MAX(CAST(correlativo AS UNSIGNED)) as max_corr FROM facturas WHERE serie = 'F001'");
$row_corr = $stmt_corr->fetch();
$max_corr = isset($row_corr['max_corr']) ? intval($row_corr['max_corr']) : 0;
$proximo_correlativo = str_pad(($max_corr + 1), 7, "0", STR_PAD_LEFT);

// Obtener los datos necesarios para la vista
$facturas = $db->query("SELECT f.*, p.nombres, p.apellidos FROM facturas f JOIN pacientes p ON f.id_paciente = p.id_paciente WHERE f.estado = 1 ORDER BY f.fecha_emision DESC")->fetchAll();
$pacientes = $db->query("SELECT id_paciente, nombres, apellidos FROM pacientes WHERE estado = 1")->fetchAll();
$servicios = $db->query("SELECT * FROM servicios WHERE estado = 1")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Facturación de Servicios</h2>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#nuevaFacturaModal">
        <i class="fas fa-file-invoice"></i> Nueva Factura
    </button>
</div>

<?php if(isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> La factura ha sido generada y el ingreso registrado en caja exitosamente.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Nro Factura</th>
                        <th>Paciente</th>
                        <th>Fecha Emisión</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($facturas as $f): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($f['serie'] . '-' . $f['correlativo']); ?></strong></td>
                        <td><?php echo htmlspecialchars($f['apellidos'] . ', ' . $f['nombres']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($f['fecha_emision'])); ?></td>
                        <td class="text-success fw-bold">S/ <?php echo number_format($f['total'], 2); ?></td>
                        <td><span class="badge bg-success">Pagado</span></td>
                        <td>
                            <a href="anular.php?id=<?php echo $f['id_factura']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Está seguro de anular esta factura? Esto también extornará el ingreso de caja.');">
                                <i class="fas fa-ban"></i> Anular
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($facturas)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No hay facturas emitidas activas.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="nuevaFacturaModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <form action="guardar_factura.php" method="POST" id="formFactura">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Generar Comprobante de Pago</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3 bg-light p-3 rounded mx-1">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Paciente / Residente</label>
                            <select name="id_paciente" class="form-select" required>
                                <option value="">-- Seleccione --</option>
                                <?php foreach($pacientes as $p): ?>
                                    <option value="<?php echo $p['id_paciente']; ?>"><?php echo htmlspecialchars($p['apellidos'] . ', ' . $p['nombres']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Serie</label>
                            <input type="text" name="serie" class="form-control text-center font-monospace text-primary fw-bold" value="F001" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Nro. Correlativo</label>
                            <input type="text" class="form-control text-center font-monospace bg-white text-danger fw-bold" value="<?php echo $proximo_correlativo; ?>" readonly disabled>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Fecha Emisión</label>
                            <input type="date" name="fecha" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mt-4 mb-2">
                        <h6 class="mb-0 fw-bold text-uppercase">Detalle de Servicios</h6>
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="agregarServicio()">
                            <i class="fas fa-plus"></i> Agregar Línea
                        </button>
                    </div>
                    
                    <div id="contenedor_servicios">
                        <div class="row g-2 mb-2 item-servicio">
                            <div class="col-md-6">
                                <select name="servicio[]" class="form-select" required onchange="actualizarPrecio(this)">
                                    <option value="">-- Seleccione un servicio --</option>
                                    <?php foreach($servicios as $s): ?>
                                        <option value="<?php echo $s['id_servicio']; ?>" data-precio="<?php echo $s['precio_referencial']; ?>">
                                            <?php echo htmlspecialchars($s['nombre_servicio']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="number" name="cantidad[]" class="form-control" value="1" min="1" step="0.01" required placeholder="Cant.">
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="precio[]" class="form-control precio-input" placeholder="Precio Unit. S/" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-secondary w-100" disabled><i class="fas fa-ban"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-lg px-4"><i class="fas fa-save"></i> Emitir Factura</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Guardamos las opciones de servicios en una variable de JS para clonarlas dinámicamente
const opcionesServicios = `
    <option value="">-- Seleccione un servicio --</option>
    <?php foreach($servicios as $s): ?>
        <option value="<?php echo $s['id_servicio']; ?>" data-precio="<?php echo $s['precio_referencial']; ?>">
            <?php echo htmlspecialchars($s['nombre_servicio']); ?>
        </option>
    <?php endforeach; ?>
`;

function agregarServicio() {
    const contenedor = document.getElementById('contenedor_servicios');
    const fila = document.createElement('div');
    fila.className = 'row g-2 mb-2 item-servicio';
    
    fila.innerHTML = `
        <div class="col-md-6">
            <select name="servicio[]" class="form-select" required onchange="actualizarPrecio(this)">
                ${opcionesServicios}
            </select>
        </div>
        <div class="col-md-2">
            <input type="number" name="cantidad[]" class="form-control" value="1" min="1" step="0.01" required placeholder="Cant.">
        </div>
        <div class="col-md-3">
            <input type="number" name="precio[]" class="form-control precio-input" placeholder="Precio Unit. S/" step="0.01" min="0" required>
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-danger w-100" onclick="this.closest('.item-servicio').remove()">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
    contenedor.appendChild(fila);
}

function actualizarPrecio(selectElement) {
    const precio = selectElement.options[selectElement.selectedIndex].getAttribute('data-precio');
    const inputPrecio = selectElement.closest('.item-servicio').querySelector('.precio-input');
    if (precio) {
        inputPrecio.value = precio;
    }
}
</script>

<?php require_once '../../includes/footer.php'; ?>