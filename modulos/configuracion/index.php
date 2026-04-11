<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';

// Verificamos que solo el Administrador acceda
if($_SESSION['nombre_rol'] != 'Administrador') {
    echo "<script>window.location.href='/casahogar/index.php';</script>";
    exit;
}

$db = (new Database())->getConnection();

// Lógica para guardar cambios si se envía el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $query_upd = "UPDATE configuracion_empresa SET 
                    ruc = :ruc, razon_social = :razon_social, nombre_comercial = :nombre_comercial, 
                    direccion = :direccion, telefono = :telefono, correo = :correo WHERE id_config = 1";
    $stmt_upd = $db->prepare($query_upd);
    $stmt_upd->execute([
        ':ruc' => $_POST['ruc'], ':razon_social' => $_POST['razon_social'],
        ':nombre_comercial' => $_POST['nombre_comercial'], ':direccion' => $_POST['direccion'],
        ':telefono' => $_POST['telefono'], ':correo' => $_POST['correo']
    ]);
    $mensaje = "Datos de la empresa actualizados correctamente.";
}

// Obtener los datos actuales
$config = $db->query("SELECT * FROM configuracion_empresa LIMIT 1")->fetch();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Configuración de la Institución</h2>
</div>

<?php if(isset($mensaje)): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $mensaje; ?></div>
<?php endif; ?>

<div class="card shadow-sm max-w-800">
    <div class="card-header bg-white">
        <h5 class="mb-0">Datos para Comprobantes y Reportes</h5>
    </div>
    <div class="card-body">
        <form action="index.php" method="POST">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">RUC</label>
                    <input type="text" name="ruc" class="form-control" value="<?php echo htmlspecialchars($config['ruc'] ?? ''); ?>" required>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Razón Social</label>
                    <input type="text" name="razon_social" class="form-control" value="<?php echo htmlspecialchars($config['razon_social'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nombre Comercial</label>
                    <input type="text" name="nombre_comercial" class="form-control" value="<?php echo htmlspecialchars($config['nombre_comercial'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Teléfono de Contacto</label>
                    <input type="text" name="telefono" class="form-control" value="<?php echo htmlspecialchars($config['telefono'] ?? ''); ?>">
                </div>
                <div class="col-md-12">
                    <label class="form-label">Dirección Fiscal / Sede</label>
                    <input type="text" name="direccion" class="form-control" value="<?php echo htmlspecialchars($config['direccion'] ?? ''); ?>" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Correo Electrónico</label>
                    <input type="email" name="correo" class="form-control" value="<?php echo htmlspecialchars($config['correo'] ?? ''); ?>">
                </div>
            </div>
            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Configuración</button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>