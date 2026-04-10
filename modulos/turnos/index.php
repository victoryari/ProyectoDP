<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';

$database = new Database();
$db = $database->getConnection();

// Obtener turnos programados (estado = 1) cruzando datos con personal y usuarios
$query_turnos = "SELECT t.id_asignacion, t.fecha_turno, t.hora_inicio, t.hora_fin, 
                        p.nombres, p.apellidos, p.cargo, 
                        u.nombre_usuario 
                 FROM asignacion_turnos t
                 JOIN personal p ON t.id_personal = p.id_personal
                 JOIN usuarios u ON t.id_usuario_asigno = u.id_usuario
                 WHERE t.estado = 1 
                 ORDER BY t.fecha_turno DESC, t.hora_inicio ASC";
$stmt_turnos = $db->prepare($query_turnos);
$stmt_turnos->execute();
$turnos = $stmt_turnos->fetchAll();

// Obtener personal activo para el select del formulario
$personal_activo = $db->query("SELECT id_personal, nombres, apellidos, cargo FROM personal WHERE estado = 1 ORDER BY apellidos")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Asignación de Turnos</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevoTurnoModal">
        <i class="fas fa-calendar-plus"></i> Programar Turno
    </button>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Horario</th>
                        <th>Personal Asignado</th>
                        <th>Cargo</th>
                        <th>Registrado por</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($turnos as $t): ?>
                    <tr>
                        <td><strong><?php echo date('d/m/Y', strtotime($t['fecha_turno'])); ?></strong></td>
                        <td><?php echo date('H:i', strtotime($t['hora_inicio'])) . ' - ' . date('H:i', strtotime($t['hora_fin'])); ?></td>
                        <td><?php echo htmlspecialchars($t['apellidos'] . ', ' . $t['nombres']); ?></td>
                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($t['cargo']); ?></span></td>
                        <td><small class="text-muted"><?php echo htmlspecialchars($t['nombre_usuario']); ?></small></td>
                        <td>
                            <a href="eliminar.php?id=<?php echo $t['id_asignacion']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Cancelar este turno programado?');">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="nuevoTurnoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Programar Nuevo Turno</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="guardar.php" method="POST">
          <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Seleccionar Personal</label>
                <select name="id_personal" class="form-select" required>
                    <option value="">-- Seleccione un trabajador --</option>
                    <?php foreach($personal_activo as $p): ?>
                        <option value="<?php echo $p['id_personal']; ?>">
                            <?php echo htmlspecialchars($p['apellidos'] . ', ' . $p['nombres'] . ' (' . $p['cargo'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Fecha del Turno</label>
                <input type="date" name="fecha_turno" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Hora de Inicio</label>
                    <input type="time" name="hora_inicio" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Hora de Fin</label>
                    <input type="time" name="hora_fin" class="form-control" required>
                </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Guardar Turno</button>
          </div>
      </form>
    </div>
  </div>
</div>

<?php require_once '../../includes/footer.php'; ?>