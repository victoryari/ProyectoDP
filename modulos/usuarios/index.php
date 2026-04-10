<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';

// Verificación de seguridad adicional
if($_SESSION['nombre_rol'] != 'Administrador') {
    echo "<script>window.location.href='/casahogar/index.php';</script>";
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Obtener usuarios activos con sus respectivos roles
$query_users = "SELECT u.id_usuario, u.nombre_usuario, r.nombre_rol, u.fecha_creacion 
                FROM usuarios u 
                JOIN roles r ON u.id_rol = r.id_rol 
                WHERE u.estado = 1";
$stmt_users = $db->prepare($query_users);
$stmt_users->execute();
$usuarios = $stmt_users->fetchAll();

// Obtener roles disponibles para el formulario
$roles = $db->query("SELECT id_rol, nombre_rol FROM roles WHERE estado = 1")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestión de Usuarios</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevoUsuarioModal">
        <i class="fas fa-user-plus"></i> Crear Usuario
    </button>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nombre de Usuario</th>
                        <th>Rol Asignado</th>
                        <th>Fecha Creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td><?php echo $u['id_usuario']; ?></td>
                        <td><strong><?php echo htmlspecialchars($u['nombre_usuario']); ?></strong></td>
                        <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($u['nombre_rol']); ?></span></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($u['fecha_creacion'])); ?></td>
                        <td>
                            <?php if($u['nombre_usuario'] != $_SESSION['nombre_usuario']): ?>
                            <a href="eliminar.php?id=<?php echo $u['id_usuario']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Desea desactivar este usuario?');">
                                <i class="fas fa-user-slash"></i> Desactivar
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="nuevoUsuarioModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Registrar Nuevo Acceso</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="guardar.php" method="POST">
          <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Nombre de Usuario</label>
                <input type="text" name="nombre_usuario" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Asignar Rol</label>
                <select name="id_rol" class="form-select" required>
                    <?php foreach($roles as $rol): ?>
                        <option value="<?php echo $rol['id_rol']; ?>"><?php echo $rol['nombre_rol']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            <button type="submit" class="btn btn-primary">Crear Usuario</button>
          </div>
      </form>
    </div>
  </div>
</div>

<?php require_once '../../includes/footer.php'; ?>