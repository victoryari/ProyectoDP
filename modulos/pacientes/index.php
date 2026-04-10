<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';

$database = new Database();
$db = $database->getConnection();

// Excluimos registros inactivos (estado = 0)
$query = "SELECT * FROM pacientes WHERE estado = 1 ORDER BY apellidos ASC";
$stmt = $db->prepare($query);
$stmt->execute();
$pacientes = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestión de Pacientes</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-toggle="modal" data-bs-target="#nuevoPacienteModal">
        <i class="fas fa-plus"></i> Nuevo Paciente
    </button>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Documento</th>
                        <th>Apellidos y Nombres</th>
                        <th>Fecha Ingreso</th>
                        <th>Contacto Emergencia</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pacientes as $p): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($p['numero_documento']); ?></td>
                        <td><?php echo htmlspecialchars($p['apellidos'] . ', ' . $p['nombres']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($p['fecha_ingreso'])); ?></td>
                        <td><?php echo htmlspecialchars($p['contacto_emergencia'] . ' (' . $p['telefono_emergencia'] . ')'); ?></td>
                        <td>
                            <a href="eliminar.php?id=<?php echo $p['id_paciente']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro de dar de baja a este paciente?');">
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

<div class="modal fade" id="nuevoPacienteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Registrar Nuevo Paciente</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="guardar.php" method="POST">
          <div class="modal-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nombres</label>
                    <input type="text" name="nombres" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Apellidos</label>
                    <input type="text" name="apellidos" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tipo Documento</label>
                    <select name="tipo_documento" class="form-select">
                        <option value="DNI">DNI</option>
                        <option value="CE">CE</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nro. Documento</label>
                    <input type="text" name="numero_documento" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Fecha Nacimiento</label>
                    <input type="date" name="fecha_nacimiento" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contacto Emergencia</label>
                    <input type="text" name="contacto_emergencia" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Teléfono Emergencia</label>
                    <input type="text" name="telefono_emergencia" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Fecha Ingreso</label>
                    <input type="date" name="fecha_ingreso" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Condición Médica</label>
                    <input type="text" name="condicion_medica" class="form-control">
                </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Guardar Registro</button>
          </div>
      </form>
    </div>
  </div>
</div>
<div class="modal fade" id="editarPacienteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Editar Información del Paciente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="actualizar.php" method="POST">
                <input type="hidden" name="id_paciente" id="edit_id">
                
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombres</label>
                            <input type="text" name="nombres" id="edit_nombres" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Apellidos</label>
                            <input type="text" name="apellidos" id="edit_apellidos" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tipo Documento</label>
                            <select name="tipo_documento" id="edit_tipo_documento" class="form-select">
                                <option value="DNI">DNI</option>
                                <option value="CE">CE</option>
                                <option value="Pasaporte">Pasaporte</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nro. Documento</label>
                            <input type="text" name="numero_documento" id="edit_numero_documento" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fecha Nacimiento</label>
                            <input type="date" name="fecha_nacimiento" id="edit_fecha_nacimiento" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contacto Emergencia</label>
                            <input type="text" name="contacto_emergencia" id="edit_contacto_emergencia" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Teléfono Emergencia</label>
                            <input type="text" name="telefono_emergencia" id="edit_telefono_emergencia" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fecha Ingreso</label>
                            <input type="date" name="fecha_ingreso" id="edit_fecha_ingreso" class="form-control" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Condición Médica</label>
                            <input type="text" name="condicion_medica" id="edit_condicion_medica" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.btn-editar').forEach(btn => {
    btn.addEventListener('click', async () => {
        const id = btn.getAttribute('data-id');
        
        try {
            // Hacemos la petición al backend para traer los datos del paciente
            const response = await fetch(`obtener.php?id=${id}`);
            const data = await response.json();
            
            if(data) {
                // Llenamos el formulario con los datos recibidos
                document.getElementById('edit_id').value = data.id_paciente;
                document.getElementById('edit_nombres').value = data.nombres;
                document.getElementById('edit_apellidos').value = data.apellidos;
                document.getElementById('edit_tipo_documento').value = data.tipo_documento;
                document.getElementById('edit_numero_documento').value = data.numero_documento;
                document.getElementById('edit_fecha_nacimiento').value = data.fecha_nacimiento;
                document.getElementById('edit_contacto_emergencia').value = data.contacto_emergencia;
                document.getElementById('edit_telefono_emergencia').value = data.telefono_emergencia;
                document.getElementById('edit_fecha_ingreso').value = data.fecha_ingreso;
                document.getElementById('edit_condicion_medica').value = data.condicion_medica;
                
                // Mostramos el modal
                const modal = new bootstrap.Modal(document.getElementById('editarPacienteModal'));
                modal.show();
            }
        } catch (error) {
            console.error("Error al obtener los datos:", error);
            alert("Ocurrió un error al cargar la información del paciente.");
        }
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?>