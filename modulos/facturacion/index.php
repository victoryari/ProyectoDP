<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';

$db = (new Database())->getConnection();
// Solo facturas activas (estado = 1)
$facturas = $db->query("SELECT f.*, p.nombres, p.apellidos FROM facturas f JOIN pacientes p ON f.id_paciente = p.id_paciente WHERE f.estado = 1")->fetchAll();
$pacientes = $db->query("SELECT id_paciente, nombres, apellidos FROM pacientes WHERE estado = 1")->fetchAll();
$servicios = $db->query("SELECT * FROM servicios WHERE estado = 1")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Facturación de Servicios</h2>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#nuevaFacturaModal">
        <i class="fas fa-file-invoice"></i> Nueva Factura
    </button>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Nro Factura</th>
                    <th>Paciente</th>
                    <th>Fecha</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($facturas as $f): ?>
                <tr>
                    <td><?php echo $f['serie'] . '-' . $f['correlativo']; ?></td>
                    <td><?php echo $f['apellidos'] . ', ' . $f['nombres']; ?></td>
                    <td><?php echo date('d/m/Y', strtotime($f['fecha_emision'])); ?></td>
                    <td>S/ <?php echo number_format($f['total'], 2); ?></td>
                    <td><span class="badge bg-success">Pagado</span></td>
                    <td>
                        <button class="btn btn-sm btn-info"><i class="fas fa-eye"></i></button>
                        <a href="anular.php?id=<?php echo $f['id_factura']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Anular factura? Esto no la borrará de la base de datos.');"><i class="fas fa-ban"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="nuevaFacturaModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <form action="guardar_factura.php" method="POST">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Generar Comprobante</h5>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Paciente / Residente</label>
                            <select name="id_paciente" class="form-select" required>
                                <?php foreach($pacientes as $p): ?>
                                    <option value="<?php echo $p['id_paciente']; ?>"><?php echo $p['apellidos'] . ', ' . $p['nombres']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Serie</label>
                            <input type="text" name="serie" class="form-control" value="F001" readonly>
                        </div>
                        <div class="col-md-3">
                            <label>Fecha Emisión</label>
                            <input type="date" name="fecha" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <hr>
                    <h6>Detalle de Servicios</h6>
                    <div id="contenedor_servicios">
                        <div class="row g-2 mb-2">
                            <div class="col-md-7">
                                <select name="servicio[]" class="form-select">
                                    <?php foreach($servicios as $s): ?>
                                        <option value="<?php echo $s['id_servicio']; ?>"><?php echo $s['nombre_servicio']; ?> - S/ <?php echo $s['precio_referencial']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="number" name="cantidad[]" class="form-control" value="1">
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="precio[]" class="form-control" placeholder="Precio">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Emitir Factura</button>
                </div>
            </div>
        </form>
    </div>
</div>