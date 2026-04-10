<div class="col-md-2 sidebar d-none d-md-block">
                <div class="p-3 text-center border-bottom border-secondary">
                    <h5 class="m-0">Divina Providencia</h5>
                    <small class="text-muted"><?php echo $_SESSION['nombre_rol']; ?></small>
                </div>
                <nav class="mt-3">
                    <a href="/casahogar/index.php"><i class="fas fa-home me-2"></i> Dashboard</a>
                    <a href="/casahogar/modulos/pacientes/index.php"><i class="fas fa-user-injured me-2"></i> Pacientes</a>
                    <a href="#"><i class="fas fa-user-md me-2"></i> Personal</a>
                    <a href="#"><i class="fas fa-calendar-alt me-2"></i> Turnos</a>
                    <a href="#"><i class="fas fa-file-invoice-dollar me-2"></i> Facturación</a>
                    <a href="#"><i class="fas fa-chart-line me-2"></i> Ingresos/Egresos</a>
                    <a href="#"><i class="fas fa-bullhorn me-2"></i> Marketing</a>
                    <?php if($_SESSION['nombre_rol'] == 'Administrador'): ?>
                    <div class="border-top border-secondary mt-2 pt-2">
                        <small class="px-3 text-muted text-uppercase">Configuración</small>
                        <a href="/casahogar/modulos/usuarios/index.php"><i class="fas fa-users-cog me-2"></i> Gestión de Usuarios</a>
                    </div>
                    <?php endif; ?>
                </nav>
            </div>
            <div class="col-md-10 content">
                <nav class="navbar navbar-expand-lg navbar-custom px-4 py-3">
                    <div class="container-fluid">
                        <span class="navbar-brand mb-0 h1">Sistema de Gestión</span>
                        <div class="d-flex align-items-center">
                            <span class="me-3"><i class="fas fa-user-circle me-1"></i> <?php echo $_SESSION['nombre_usuario']; ?></span>
                            <a href="/casahogar/logout.php" class="btn btn-outline-danger btn-sm">Salir</a>
                        </div>
                    </div>
                </nav>
                <div class="p-4">