<?php
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Obtener métricas rápidas (registros activos)
$pacientes = $db->query("SELECT COUNT(*) FROM pacientes WHERE estado = 1")->fetchColumn();
$personal = $db->query("SELECT COUNT(*) FROM personal WHERE estado = 1")->fetchColumn();
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold">Dashboard Resumen</h2>
        <p class="text-muted">Bienvenido al sistema administrativo.</p>
    </div>
</div>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card border-left-primary shadow-sm h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Pacientes Residentes</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pacientes; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-injured fa-2x text-muted"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card border-left-success shadow-sm h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Personal Activo</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $personal; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-md fa-2x text-muted"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>