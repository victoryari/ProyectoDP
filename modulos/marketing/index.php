<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';

$db = (new database())->getConnection();
$prospectos = $db->query("SELECT * FROM prospectos_marketing WHERE estado = 1 ORDER BY fecha_registro DESC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestión de Prospectos (Marketing)</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevoProspectoModal">
        <i class="fas fa-bullseye"></i> Registrar Contacto
    </button>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Nombre / Familia</th>
                        <th>Medio</th>
                        <th>Interés / Comentarios</th>
                        <th>Estado de Seguimiento</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($prospectos as $p): ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($p['fecha_registro'])); ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($p['nombre_contacto']); ?></strong><br>
                            <small class="text-muted"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($p['telefono']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($p['medio_contacto']); ?></td>
                        <td><?php echo htmlspecialchars($p['interes_mostrado']); ?></td>
                        <td>
                            <?php 
                                $badge = 'bg-secondary';
                                if($p['estado_seguimiento'] == 'Contactado') $badge = 'bg-info text-dark';
                                if($p['estado_seguimiento'] == 'Convertido') $badge = 'bg-success';
                                if($p['estado_seguimiento'] == 'Descartado') $badge = 'bg-danger';
                            ?>
                            <span class="badge <?php echo $badge; ?>"><?php echo $p['estado_seguimiento']; ?></span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-warning btn-editar-prospecto" data-id="<?php echo $p['id_prospecto']; ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="eliminar.php?id=<?php echo $p['id_prospecto']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar prospecto?');">
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

<div class="modal fade" id="nuevoProspectoModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Nuevo Contacto Comercial</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="guardar.php" method="POST">
          <div class="modal-body">
            <div class="mb-3">
                <label>Nombre del Contacto / Familiar</label>
                <input type="text" name="nombre_contacto" class="form-control" required>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Teléfono</label>
                    <input type="text" name="telefono" class="form-control">
                </div>
                <div class="col-md-6">
                    <label>Correo Electrónico</label>
                    <input type="email" name="correo" class="form-control">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Medio de Contacto</label>
                    <select name="medio_contacto" class="form-select">
                        <option>Redes Sociales</option>
                        <option>Llamada</option>
                        <option>Presencial</option>
                        <option>Recomendación</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Estado Inicial</label>
                    <select name="estado_seguimiento" class="form-select">
                        <option>Pendiente</option>
                        <option>Contactado</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label>Interés Mostrado / Notas</label>
                <textarea name="interes_mostrado" class="form-control" rows="3" placeholder="Ej: Pregunta por costos mensuales, necesita cuidados especiales..."></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Guardar Prospecto</button>
          </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="editarProspectoModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title">Actualizar Seguimiento</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="actualizar.php" method="POST">
          <input type="hidden" name="id_prospecto" id="edit_id_prospecto">
          <div class="modal-body">
            <div class="mb-3">
                <label>Estado de Seguimiento</label>
                <select name="estado_seguimiento" id="edit_estado_seguimiento" class="form-select border-warning shadow-sm">
                    <option value="Pendiente">Pendiente</option>
                    <option value="Contactado">Contactado</option>
                    <option value="Convertido">Convertido (Pasó a ser residente)</option>
                    <option value="Descartado">Descartado</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Actualizar Notas / Interés</label>
                <textarea name="interes_mostrado" id="edit_interes_mostrado" class="form-control" rows="4"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Actualizar</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.btn-editar-prospecto').forEach(btn => {
    btn.addEventListener('click', async () => {
        const id = btn.getAttribute('data-id');
        const response = await fetch(`obtener.php?id=${id}`);
        const data = await response.json();
        
        document.getElementById('edit_id_prospecto').value = data.id_prospecto;
        document.getElementById('edit_estado_seguimiento').value = data.estado_seguimiento;
        document.getElementById('edit_interes_mostrado').value = data.interes_mostrado;
        
        const modal = new bootstrap.Modal(document.getElementById('editarProspectoModal'));
        modal.show();
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?>