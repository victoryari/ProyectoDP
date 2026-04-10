<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';

$database = new Database();
$db = $database->getConnection();

// Listar personal activo
$query = "SELECT * FROM personal WHERE estado = 1 ORDER BY apellidos ASC";
$stmt = $db->prepare($query);
$stmt->execute();
$personal = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestión de Personal</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevoPersonalModal">
        <i class="fas fa-user-plus"></i> Registrar Personal
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
                        <th>Cargo</th>
                        <th>Modalidad</th>
                        <th>Teléfono</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($personal as $p): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($p['tipo_documento'] . ': ' . $p['numero_documento']); ?></td>
                        <td><strong><?php echo htmlspecialchars($p['apellidos'] . ', ' . $p['nombres']); ?></strong></td>
                        <td><?php echo htmlspecialchars($p['cargo']); ?></td>
                        <td>
                            <?php if($p['modalidad_contrato'] == 'Planilla Fija'): ?>
                                <span class="badge bg-success">Planilla Fija</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Turno Rotativo</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($p['telefono']); ?></td>
                        <td>
                            <a href="eliminar.php?id=<?php echo $p['id_personal']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Está seguro de dar de baja a este trabajador?');">
                                <i class="fas fa-user-slash"></i> Baja
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="nuevoPersonalModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Registrar Nuevo Trabajador</h5>
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
                        <option value="Pasaporte">Pasaporte</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nro. Documento</label>
                    <input type="text" name="numero_documento" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="telefono" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Cargo (Ej. Cuidador, Enfermera, Limpieza)</label>
                    <input type="text" name="cargo" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Modalidad de Contrato</label>
                    <select name="modalidad_contrato" class="form-select" required>
                        <option value="Planilla Fija">Planilla Fija</option>
                        <option value="Turno Rotativo">Turno Rotativo</option>
                    </select>
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