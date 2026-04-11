<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';

$database = new Database();
$db = $database->getConnection();
$rol = $_SESSION['nombre_rol'];

// ==========================================
// VISTA PARA EL ROL ESPECIALISTA (CALENDARIO)
// ==========================================
if ($rol == 'Especialista') {
    // 1. Obtener ID del trabajador usando el username (DNI)
    $username = $_SESSION['nombre_usuario'];
    $stmt_p = $db->prepare("SELECT id_personal FROM personal WHERE numero_documento = ?");
    $stmt_p->execute([$username]);
    $personal = $stmt_p->fetch(PDO::FETCH_ASSOC);
    $id_personal = $personal ? $personal['id_personal'] : 0;

    // 2. Variables del mes y año para el calendario
    $mes = isset($_GET['mes']) ? intval($_GET['mes']) : date('m');
    $anio = isset($_GET['anio']) ? intval($_GET['anio']) : date('Y');
    
    // Controles de navegación de meses
    $mes_ant = $mes - 1; $anio_ant = $anio;
    if ($mes_ant == 0) { $mes_ant = 12; $anio_ant--; }
    $mes_sig = $mes + 1; $anio_sig = $anio;
    if ($mes_sig == 13) { $mes_sig = 1; $anio_sig++; }

    // Nombres de meses en español
    $nombres_meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
    $nombre_mes_actual = $nombres_meses[$mes - 1];

    // 3. Obtener turnos del mes seleccionado para este trabajador
    $query = "SELECT fecha_turno, hora_inicio, hora_fin FROM asignacion_turnos 
              WHERE id_personal = ? AND MONTH(fecha_turno) = ? AND YEAR(fecha_turno) = ? AND estado = 1";
    $stmt_t = $db->prepare($query);
    $stmt_t->execute([$id_personal, $mes, $anio]);
    // FETCH_GROUP organiza el array usando la primera columna (fecha_turno) como llave
    $turnos = $stmt_t->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC); 
?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Mi Rol de Turnos</h2>
        <div class="btn-group shadow-sm">
            <a href="?mes=<?php echo $mes_ant; ?>&anio=<?php echo $anio_ant; ?>" class="btn btn-outline-primary"><i class="fas fa-chevron-left"></i> Anterior</a>
            <span class="btn btn-primary fw-bold text-uppercase" style="pointer-events: none;"><?php echo $nombre_mes_actual . ' ' . $anio; ?></span>
            <a href="?mes=<?php echo $mes_sig; ?>&anio=<?php echo $anio_sig; ?>" class="btn btn-outline-primary">Siguiente <i class="fas fa-chevron-right"></i></a>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0" style="table-layout: fixed; min-width: 700px;">
                    <thead class="bg-light text-center text-uppercase small fw-bold">
                        <tr>
                            <th style="width: 14.28%;">Domingo</th>
                            <th style="width: 14.28%;">Lunes</th>
                            <th style="width: 14.28%;">Martes</th>
                            <th style="width: 14.28%;">Miércoles</th>
                            <th style="width: 14.28%;">Jueves</th>
                            <th style="width: 14.28%;">Viernes</th>
                            <th style="width: 14.28%;">Sábado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // FUNCIÓN AUXILIAR (Debe ser declarada ANTES de usarse en un bloque condicional)
                        if (!function_exists('renderizarCeldaDia')) {
                            function renderizarCeldaDia($dia, $mes, $anio, $turnos) {
                                $fecha_full = sprintf("%04d-%02d-%02d", $anio, $mes, $dia);
                                $es_hoy = ($fecha_full == date('Y-m-d'));
                                $clase_fondo = $es_hoy ? 'bg-primary bg-opacity-10' : '';
                                
                                echo "<td class='$clase_fondo p-1 border' style='height: 120px; vertical-align: top;'>";
                                
                                // Número del día
                                echo "<div class='text-end mb-1'>";
                                if ($es_hoy) {
                                    echo "<span class='badge bg-primary rounded-circle p-2'>$dia</span>";
                                } else {
                                    echo "<span class='text-secondary fw-bold me-1'>$dia</span>";
                                }
                                echo "</div>";
                                
                                // Mostrar los turnos si existen en esta fecha
                                if (isset($turnos[$fecha_full])) {
                                    foreach ($turnos[$fecha_full] as $t) {
                                        $inicio = date('H:i', strtotime($t['hora_inicio']));
                                        $fin = date('H:i', strtotime($t['hora_fin']));
                                        echo "<div class='bg-success text-white small p-2 mb-1 rounded shadow-sm' style='font-size: 0.8rem;'>";
                                        echo "<i class='fas fa-briefcase me-1'></i> $inicio - $fin";
                                        echo "</div>";
                                    }
                                }
                                echo "</td>";
                            }
                        }

                        // Lógica para renderizar el calendario
                        $primer_dia_mes = strtotime("$anio-$mes-01");
                        $dia_semana_inicio = date('w', $primer_dia_mes); // 0 (Dom) a 6 (Sab)
                        $dias_en_mes = date('t', $primer_dia_mes);
                        $dia_actual = 1;
                        
                        echo "<tr>";
                        // Celdas vacías antes del primer día del mes
                        for ($i = 0; $i < $dia_semana_inicio; $i++) {
                            echo "<td class='bg-light'></td>";
                        }
                        
                        // Renderizar los días de la primera semana
                        for ($i = $dia_semana_inicio; $i < 7; $i++) {
                            renderizarCeldaDia($dia_actual, $mes, $anio, $turnos);
                            $dia_actual++;
                        }
                        echo "</tr>";

                        // Semanas restantes
                        while ($dia_actual <= $dias_en_mes) {
                            echo "<tr>";
                            for ($i = 0; $i < 7; $i++) {
                                if ($dia_actual <= $dias_en_mes) {
                                    renderizarCeldaDia($dia_actual, $mes, $anio, $turnos);
                                    $dia_actual++;
                                } else {
                                    echo "<td class='bg-light'></td>"; // Celdas vacías al final
                                }
                            }
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php 
} 
// ==========================================
// VISTA PARA ADMINISTRADOR Y ADMINISTRATIVO (TABLA Y GESTIÓN)
// ==========================================
else { 
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

    $personal_activo = $db->query("SELECT id_personal, nombres, apellidos, cargo FROM personal WHERE estado = 1 ORDER BY apellidos")->fetchAll();
?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Asignación de Turnos</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevoTurnoModal">
            <i class="fas fa-calendar-plus"></i> Programar Turno
        </button>
    </div>

    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> Acción completada con éxito.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

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
                        <?php if(empty($turnos)): ?>
                            <tr><td colspan="6" class="text-center text-muted">No hay turnos programados activos.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="nuevoTurnoModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">Programar Nuevo Turno</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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

<?php 
} // Fin del else para Administradores
require_once '../../includes/footer.php'; 
?>