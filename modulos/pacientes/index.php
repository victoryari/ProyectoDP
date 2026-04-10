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

<?php require_once '../../includes/footer.php'; ?>