<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';

$db = (new database())->getConnection();
$servicios = $db->query("SELECT * FROM servicios WHERE estado = 1 ORDER BY nombre_servicio ASC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Catálogo de Servicios</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#servicioModal" onclick="prepararNuevo()">
        <i class="fas fa-plus-circle"></i> Nuevo Servicio
    </button>
</div>

<?php if(isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle"></i> Acción completada con éxito.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Código</th>
                        <th>Nombre del Servicio</th>
                        <th>Descripción</th>
                        <th>Precio Ref. (S/)</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($servicios as $s): ?>
                    <tr>
                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($s['codigo_servicio'] ?? 'S/C'); ?></span></td>
                        <td><strong><?php echo htmlspecialchars($s['nombre_servicio']); ?></strong></td>
                        <td><?php echo htmlspecialchars($s['descripcion']); ?></td>
                        <td class="text-success fw-bold"><?php echo number_format($s['precio_referencial'], 2); ?></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-warning" onclick="editarServicio(<?php echo $s['id_servicio']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="eliminar.php?id=<?php echo $s['id_servicio']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este servicio del catálogo? No afectará a las facturas ya emitidas.');">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="servicioModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white" id="modalHeader">
                <h5 class="modal-title" id="modalTitle">Registrar Servicio</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="guardar.php" method="POST" id="formServicio">
                <input type="hidden" name="id_servicio" id="form_id_servicio">
                
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Código</label>
                            <input type="text" name="codigo_servicio" id="form_codigo" class="form-control" placeholder="Ej: SRV-01">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Nombre del Servicio *</label>
                            <input type="text" name="nombre_servicio" id="form_nombre" class="form-control" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Precio Referencial (S/) *</label>
                            <input type="number" name="precio_referencial" id="form_precio" class="form-control" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Descripción Detallada</label>
                            <textarea name="descripcion" id="form_descripcion" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnSubmit">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const modalObj = new bootstrap.Modal(document.getElementById('servicioModal'));

function prepararNuevo() {
    document.getElementById('formServicio').reset();
    document.getElementById('form_id_servicio').value = '';
    document.getElementById('formServicio').action = 'guardar.php';
    
    document.getElementById('modalHeader').className = 'modal-header bg-primary text-white';
    document.getElementById('modalTitle').innerText = 'Registrar Nuevo Servicio';
    document.getElementById('btnSubmit').className = 'btn btn-primary';
    document.getElementById('btnSubmit').innerText = 'Guardar Servicio';
}

async function editarServicio(id) {
    try {
        const response = await fetch(`obtener.php?id=${id}`);
        const data = await response.json();
        
        if(data && !data.error) {
            document.getElementById('form_id_servicio').value = data.id_servicio;
            document.getElementById('form_codigo').value = data.codigo_servicio;
            document.getElementById('form_nombre').value = data.nombre_servicio;
            document.getElementById('form_precio').value = data.precio_referencial;
            document.getElementById('form_descripcion').value = data.descripcion;
            
            // Cambiamos la apariencia y destino del modal para modo Edición
            document.getElementById('formServicio').action = 'actualizar.php';
            document.getElementById('modalHeader').className = 'modal-header bg-warning text-dark';
            document.getElementById('modalTitle').innerText = 'Editar Servicio';
            document.getElementById('btnSubmit').className = 'btn btn-warning text-dark';
            document.getElementById('btnSubmit').innerText = 'Actualizar Cambios';
            
            modalObj.show();
        }
    } catch (error) {
        alert("Error al cargar los datos del servicio.");
    }
}
</script>

<?php require_once '../../includes/footer.php'; ?>