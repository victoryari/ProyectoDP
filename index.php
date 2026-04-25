<?php
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
require_once 'config/database.php';

$database = new database();
$db = $database->getConnection();

$rol = $_SESSION['nombre_rol'];
$username = $_SESSION['nombre_usuario']; // En el caso del especialista, usamos esto para cruzar con su DNI en 'personal'

// ==========================================
// VISTA PARA EL ROL ESPECIALISTA
// ==========================================
if ($rol == 'Especialista') {
    // 1. Buscamos el ID del trabajador asociado a este usuario (cruzando username con numero_documento)
    $stmt_emp = $db->prepare("SELECT id_personal, nombres, apellidos, cargo FROM personal WHERE numero_documento = ? AND estado = 1");
    $stmt_emp->execute([$username]);
    $empleado = $stmt_emp->fetch(PDO::FETCH_ASSOC);
    
    $id_personal = $empleado ? $empleado['id_personal'] : 0;
    
    // 2. Buscamos su próximo turno (el más cercano a partir de hoy)
    $stmt_turno = $db->prepare("SELECT * FROM asignacion_turnos WHERE id_personal = ? AND fecha_turno >= CURDATE() AND estado = 1 ORDER BY fecha_turno ASC, hora_inicio ASC LIMIT 1");
    $stmt_turno->execute([$id_personal]);
    $proximo_turno = $stmt_turno->fetch(PDO::FETCH_ASSOC);
?>
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold">Bienvenido, <?php echo $empleado ? htmlspecialchars($empleado['nombres']) : htmlspecialchars($username); ?></h2>
            <p class="text-muted">Panel de control operativo.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm border-0 border-start border-primary border-4 p-4 h-100">
                <h5 class="text-primary fw-bold mb-3"><i class="fas fa-clock me-2"></i> Mi Próximo Turno</h5>
                <?php if ($proximo_turno): ?>
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-light rounded p-3 text-center me-3" style="min-width: 80px;">
                            <span class="d-block text-danger fw-bold fs-4"><?php echo date('d', strtotime($proximo_turno['fecha_turno'])); ?></span>
                            <span class="d-block text-muted text-uppercase small"><?php echo date('M', strtotime($proximo_turno['fecha_turno'])); ?></span>
                        </div>
                        <div>
                            <p class="mb-1 text-muted"><strong>Horario:</strong></p>
                            <h5 class="mb-0 text-dark"><?php echo date('H:i', strtotime($proximo_turno['hora_inicio'])) . " a " . date('H:i', strtotime($proximo_turno['hora_fin'])); ?></h5>
                        </div>
                    </div>
                    <hr>
                    <a href="modulos/turnos/index.php" class="btn btn-outline-primary w-100"><i class="fas fa-calendar-alt me-2"></i> Ver mi rol mensual</a>
                <?php else: ?>
                    <div class="alert alert-secondary mt-2">
                        <i class="fas fa-info-circle"></i> No tienes turnos programados próximamente.
                    </div>
                    <a href="modulos/turnos/index.php" class="btn btn-outline-primary w-100 mt-3"><i class="fas fa-calendar-alt me-2"></i> Ir a mi calendario</a>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if($empleado): ?>
        <div class="col-md-6 col-lg-4 mt-4 mt-md-0">
            <div class="card shadow-sm border-0 h-100 bg-light">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small fw-bold mb-3">Mi Perfil Profesional</h6>
                    <p class="mb-1"><strong>Nombres:</strong> <?php echo htmlspecialchars($empleado['nombres'] . ' ' . $empleado['apellidos']); ?></p>
                    <p class="mb-1"><strong>Cargo:</strong> <span class="badge bg-secondary"><?php echo htmlspecialchars($empleado['cargo']); ?></span></p>
                    <p class="mb-0"><strong>Documento:</strong> <?php echo htmlspecialchars($username); ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

<?php 
} 
// ==========================================
// VISTA PARA ADMINISTRADOR Y ADMINISTRATIVO
// ==========================================
else { 
    $pacientes = $db->query("SELECT COUNT(*) FROM pacientes WHERE estado = 1")->fetchColumn();
    $personal = $db->query("SELECT COUNT(*) FROM personal WHERE estado = 1")->fetchColumn();
    $turnos_hoy = $db->query("SELECT COUNT(*) FROM asignacion_turnos WHERE estado = 1 AND fecha_turno = CURDATE()")->fetchColumn();
    $prospectos = $db->query("SELECT COUNT(*) FROM prospectos_marketing WHERE estado = 1 AND estado_seguimiento IN ('Pendiente', 'Contactado')")->fetchColumn();
    
    $ingresos_mes = $db->query("SELECT SUM(monto) FROM ingresos WHERE estado = 1 AND MONTH(fecha_ingreso) = MONTH(CURDATE()) AND YEAR(fecha_ingreso) = YEAR(CURDATE())")->fetchColumn() ?: 0;
    $egresos_mes = $db->query("SELECT SUM(monto) FROM egresos WHERE estado = 1 AND MONTH(fecha_egreso) = MONTH(CURDATE()) AND YEAR(fecha_egreso) = YEAR(CURDATE())")->fetchColumn() ?: 0;
    $saldo_mes = $ingresos_mes - $egresos_mes;
?>
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold">Dashboard Resumen</h2>
            <p class="text-muted">Bienvenido al sistema administrativo de Casa Hogar Divina Providencia.</p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow-sm h-100 py-2 border-0 border-start border-primary border-4">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Pacientes Residentes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pacientes; ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-user-injured fa-2x text-gray-300 opacity-50"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow-sm h-100 py-2 border-0 border-start border-success border-4">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Personal Activo</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $personal; ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-user-md fa-2x text-gray-300 opacity-50"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow-sm h-100 py-2 border-0 border-start border-warning border-4">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Turnos Hoy</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $turnos_hoy; ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-calendar-day fa-2x text-gray-300 opacity-50"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow-sm h-100 py-2 border-0 border-start border-info border-4">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Prospectos Activos</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $prospectos; ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-users fa-2x text-gray-300 opacity-50"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h4 class="mb-3 text-secondary">Finanzas del Mes Actual</h4>
    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card shadow-sm h-100 py-2 border-0">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Ingresos (<?php echo date('M Y'); ?>)</div>
                            <div class="h5 mb-0 font-weight-bold text-success">S/ <?php echo number_format($ingresos_mes, 2); ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-arrow-up fa-2x text-success opacity-50"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card shadow-sm h-100 py-2 border-0">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Egresos (<?php echo date('M Y'); ?>)</div>
                            <div class="h5 mb-0 font-weight-bold text-danger">S/ <?php echo number_format($egresos_mes, 2); ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-arrow-down fa-2x text-danger opacity-50"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-12 mb-4">
            <div class="card shadow-sm h-100 py-2 border-0 bg-light">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Saldo del Mes</div>
                            <div class="h4 mb-0 font-weight-bold <?php echo $saldo_mes >= 0 ? 'text-primary' : 'text-danger'; ?>">
                                S/ <?php echo number_format($saldo_mes, 2); ?>
                            </div>
                        </div>
                        <div class="col-auto"><i class="fas fa-wallet fa-2x text-primary opacity-50"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php 
} // Fin del else Administrador
require_once 'includes/footer.php'; 
?>