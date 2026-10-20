<?php
    session_start();
    if (!isset($_SESSION['usuario'])) {
        header("Location: index.php");
        exit();
    }
    include "conexion_db.php";

    // ============================================================
    // 1) PRÓXIMA CITA (solo pendientes)
    // ============================================================
    $sqlProxima = "
        SELECT c.id, c.fecha, c.id_paciente, c.notas,
            h.hora,
            p.nombre, p.apellido
        FROM citas c
        INNER JOIN pacientes p ON c.id_paciente = p.id_paciente
        INNER JOIN horarios h ON c.id_horario = h.id
        WHERE
            (c.fecha > CURDATE())
            OR
            (c.fecha = CURDATE() AND NOT EXISTS (
                SELECT 1 FROM historia hi
                WHERE hi.id_paciente = c.id_paciente
                AND hi.fecha = c.fecha
            ))
        ORDER BY c.fecha ASC, h.hora ASC
        LIMIT 1
    ";
    $resProxima = $conexion->query($sqlProxima);
    $proxima = $resProxima ? $resProxima->fetch_assoc() : null;

    // ============================================================
    // 2) CITAS DEL DÍA
    // ============================================================
    $hoy = date("Y-m-d");
    $resTotalDia = $conexion->query("SELECT COUNT(*) AS total FROM citas WHERE fecha = '$hoy'");
    $totalDia = $resTotalDia ? ($resTotalDia->fetch_assoc()['total'] ?? 0) : 0;

    $resAtendidasDia = $conexion->query("
        SELECT COUNT(*) AS total
        FROM citas c
        INNER JOIN historia hi ON hi.id_paciente = c.id_paciente AND hi.fecha = c.fecha
        WHERE c.fecha = '$hoy'
    ");
    $atendidasDia = $resAtendidasDia ? ($resAtendidasDia->fetch_assoc()['total'] ?? 0) : 0;
    $faltantesDia = max(0, $totalDia - $atendidasDia);

    // ============================================================
    // 3) ÚLTIMOS PACIENTES ATENDIDOS
    // ============================================================
    $sqlUltimosAtendidos = "
        SELECT
            hi.id_paciente,
            MAX(hi.fecha) AS ultima_fecha,
            (SELECT h2.motivo FROM historia h2 WHERE h2.id_paciente = hi.id_paciente ORDER BY h2.fecha DESC, h2.id DESC LIMIT 1) AS ultimo_motivo,
            p.nombre, p.apellido
        FROM historia hi
        INNER JOIN pacientes p ON p.id_paciente = hi.id_paciente
        GROUP BY hi.id_paciente, p.nombre, p.apellido
        ORDER BY ultima_fecha DESC
        LIMIT 5
    ";
    $resUltimosAtendidos = $conexion->query($sqlUltimosAtendidos);
    $ultimosAtendidos = $resUltimosAtendidos ? $resUltimosAtendidos->fetch_all(MYSQLI_ASSOC) : [];

   // ============================================================
// 4) BLOQUEOS DE HORARIOS (últimos 10)
// ============================================================
$sqlBloqueos = "
    SELECT 
        b.fecha,
        b.motivo,
        MIN(h.hora) AS desde,
        MAX(h.hora) AS hasta,
        COUNT(b.id_horario) AS cant
    FROM bloqueos_horarios b
    LEFT JOIN horarios h ON h.id = b.id_horario
    WHERE b.fecha >= CURDATE()
    GROUP BY b.fecha, b.motivo, (CASE WHEN b.id_horario IS NULL THEN 1 ELSE 0 END)
    ORDER BY b.fecha DESC
    LIMIT 10
";

$resBloqueos = $conexion->query($sqlBloqueos);
$bloqueos = $resBloqueos ? $resBloqueos->fetch_all(MYSQLI_ASSOC) : [];

    // ============================================================
    // 5) SOLICITUDES PENDIENTES
    // ============================================================
    $resSolPendientes = $conexion->query("SELECT COUNT(*) AS total FROM solicitudes_citas");
    $solPendientes = $resSolPendientes ? ($resSolPendientes->fetch_assoc()['total'] ?? 0) : 0;

    $sqlSolicitudes = "
        SELECT s.id_solicitud, s.id_paciente, s.fecha_solicitud, s.motivo_solicitud,
            h.hora, p.nombre, p.apellido
        FROM solicitudes_citas s
        INNER JOIN pacientes p ON p.id_paciente = s.id_paciente
        INNER JOIN horarios h ON h.id = s.id_horario
        ORDER BY s.fecha_solicitud DESC, h.hora ASC
        LIMIT 5
    ";
    $resSolicitudes = $conexion->query($sqlSolicitudes);
    $listaSolicitudes = $resSolicitudes ? $resSolicitudes->fetch_all(MYSQLI_ASSOC) : [];

    // ============================================================
    // 6) MÉTRICAS GENERALES
    // ============================================================
    $resPac = $conexion->query("SELECT COUNT(*) AS total FROM pacientes");
    $totalPacientes = $resPac ? ($resPac->fetch_assoc()['total'] ?? 0) : 0;

    $resCitasMes = $conexion->query("SELECT COUNT(*) AS total FROM citas WHERE YEAR(fecha)=YEAR(CURDATE()) AND MONTH(fecha)=MONTH(CURDATE())");
    $citasMes = $resCitasMes ? ($resCitasMes->fetch_assoc()['total'] ?? 0) : 0;

    $resHist = $conexion->query("SELECT COUNT(*) AS total FROM historia");
    $totalHistorias = $resHist ? ($resHist->fetch_assoc()['total'] ?? 0) : 0;

    $resReprog = $conexion->query("SELECT COUNT(*) AS total FROM citas WHERE reprogramado = 1 AND YEAR(fecha)=YEAR(CURDATE()) AND MONTH(fecha)=MONTH(CURDATE())");
    $reprogMes = $resReprog ? ($resReprog->fetch_assoc()['total'] ?? 0) : 0;
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./styles/pacientes.css">
    <title>Sistema Esperanza</title>
    <style>
        html, body { margin: 0; padding: 0;}

        /* ==================== DASHBOARD RESPONSIVE ==================== */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr; /* 3 columnas en escritorio */
            gap: 20px;
            padding: 10px;
width:87%;
        }

.layout {
    display: flex;
    min-height: calc(100vh - 140px); /* header + footer aprox */
}

.layout nav {
    width: 180px;
    flex-shrink: 0;
}
.dashboard-grid {
    flex: 1;
    width: 100%;
}



        /* Tablet: 2 columnas */
        @media (max-width: 992px) {
            .dashboard-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        /* Móvil: 1 columna */
        @media (max-width: 600px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
                padding: 10px;
                gap: 15px;
            }
        }

        /* Tarjeta base */
        .dash-card {
            background: #ffffffd9;
    border: 1px solid #dcdcdc;
    border-radius: 10px;
    padding: 18px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.12);
    min-width: 0;

    /* NUEVO */
    max-height: 300px;         /* ajustá este valor a gusto */
    overflow-y: auto;          /* activa el scroll vertical si se necesita */
    overflow-x: hidden;        /* evita barras horizontales */
        }

        .dash-card h3 {
            margin: 0 0 12px;
            font-size: 1.15rem;
            color: #0d6efd;
            font-weight: 600;
        }

        .dash-card p {
            margin: 4px 0;
            font-size: 0.95rem;
            color: #333;
        }

        .muted {
            color: #777;
            font-style: italic;
        }

        .dash-list {
            margin: 8px 0 0;
            padding-left: 18px;
            font-size: 0.95rem;
        }

        .dash-list li {
            margin: 6px 0;
        }

        .mini-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-top: 10px;
        }

        .mini-card {
            background: #f8f9fa;
            border: 1px solid #e2e2e2;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
        }

        .mini-label {
            font-size: 0.8rem;
            color: #555;
        }

        .mini-value {
            font-size: 1.25rem;
            color: #0d6efd;
            font-weight: bold;
        }

        .btn {
            margin-top: 12px;
            display: inline-block;
            padding: 8px 14px;
            background: #0d6efd;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .btn:hover {
            background: #0b5ed7;
        }
    </style>
    </head>
    <body style="background-image: url('./assets/img/fondo1.png'); background-size: cover;">

    <header>
        <div class="logo">
            <img src="./assets/img/logo.png" alt="logo">
            <h1>Sistema Esperanza</h1>
        </div>
        <div class="radio">
            <h2 class="radio-title">Radio online InD'Mix</h2>
            <audio src="https://server.laradio.online:58919/live" controls preload></audio>
        </div>
    </header>

<div class="layout">
    <nav>
        <a href="main.php" style="background-color: #FFB703;">Inicio</a>
        <a href="pacientes.php">Pacientes</a>
        <a href="reportes.php">Evoluciones</a>
        <a href="citas.php">Citas</a>
        <a href="gestion.php">Gestión</a>
        <a href="./assets/manual.pdf" target="_blank">Manual</a>
        <a href="logout.php">Cerrar sesión</a>
    </nav>

    <!-- =============== DASHBOARD CON 6 TARJETAS BIEN DISTRIBUIDAS =============== -->
    <div class="dashboard-grid">
        <!-- 1. Próxima cita -->
        <div class="dash-card">
            <h3>Próxima cita</h3>
            <?php if ($proxima): ?>
                <p><strong><?= htmlspecialchars($proxima['nombre']." ".$proxima['apellido']) ?></strong></p>
                <p><?= date("d/m/Y", strtotime($proxima['fecha'])) ?> — <?= substr($proxima['hora'],0,5) ?></p>
                <p><?= $proxima['notas'] !== "" ? htmlspecialchars($proxima['notas']) : "<span class='muted'>Sin notas</span>" ?></p>
            <?php else: ?>
                <p class="muted">No hay próximas citas pendientes.</p>
            <?php endif; ?>
        </div>

        <!-- 2. Citas del día -->
        <div class="dash-card">
            <h3>Citas del día (<?= date("d/m/Y") ?>)</h3>
            <p>Total: <strong><?= $totalDia ?></strong></p>
            <p>Atendidas: <strong><?= $atendidasDia ?></strong></p>
            <p>Faltan: <strong><?= $faltantesDia ?></strong></p>
        </div>

        <!-- 3. Últimos atendidos -->
        <div class="dash-card">
            <h3>Últimos pacientes atendidos</h3>
            <?php if (!empty($ultimosAtendidos)): ?>
                <ul class="dash-list">
                    <?php foreach ($ultimosAtendidos as $u): ?>
                        <li>
                            <strong><?= htmlspecialchars($u['nombre']." ".$u['apellido']) ?></strong>
                            — <?= date("d/m/Y", strtotime($u['ultima_fecha'])) ?>
                            <?php if (!empty($u['ultimo_motivo'])) echo " (".htmlspecialchars($u['ultimo_motivo']).")"; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="muted">Todavía no hay atenciones registradas.</p>
            <?php endif; ?>
        </div>

<!-- 4. Horarios bloqueados -->
<div class="dash-card">
    <h3>Horarios bloqueados</h3>

    <?php if (!empty($bloqueos)): ?>
        <ul class="dash-list">
            <?php foreach ($bloqueos as $b): ?>
                <?php
                    if ($b['desde'] === null) {
                        $textoHorario = "Día completo";
                    } elseif ($b['cant'] == 1) {
                        $textoHorario = $b['desde'];
                    } else {
                        $textoHorario = $b['desde'] . " – " . $b['hasta'];
                    }
                ?>
                <li>
                    <strong><?= date("d/m/Y", strtotime($b['fecha'])) ?></strong>
                    — <?= $textoHorario ?>
                    <?php if (!empty($b['motivo'])): ?>
                        (<?= htmlspecialchars($b['motivo']) ?>)
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="muted">No hay horarios bloqueados registrados.</p>
    <?php endif; ?>
<a href="bloquear_horarios.php" class="btn">Ver bloqueos</a>

</div>

        <!-- 5. Solicitudes pendientes -->
        <div class="dash-card">
            <h3>Solicitudes pendientes (<?= $solPendientes ?>)</h3>
            <?php if (!empty($listaSolicitudes)): ?>
                <ul class="dash-list">
                    <?php foreach ($listaSolicitudes as $s): ?>
                        <li>
                            <strong><?= htmlspecialchars($s['nombre']." ".$s['apellido']) ?></strong>
                            — <?= date("d/m/Y", strtotime($s['fecha_solicitud'])) ?> | <?= substr($s['hora'],0,5) ?>
                            <?php if (!empty($s['motivo_solicitud'])) echo " (".htmlspecialchars($s['motivo_solicitud']).")"; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="muted">No hay solicitudes de turno registradas.</p>
            <?php endif; ?>
            <a href="gestion.php" class="btn">Ir a gestión</a>
        </div>

        <!-- 6. Métricas generales -->
        <div class="dash-card">
            <h3>Métricas generales</h3>
            <div class="mini-grid">
                <div class="mini-card">
                    <div class="mini-label">Pacientes</div>
                    <div class="mini-value"><?= $totalPacientes ?></div>
                </div>
                <div class="mini-card">
                    <div class="mini-label">Citas del mes</div>
                    <div class="mini-value"><?= $citasMes ?></div>
                </div>
                <div class="mini-card">
                    <div class="mini-label">Historias clínicas</div>
                    <div class="mini-value"><?= $totalHistorias ?></div>
                </div>
                <div class="mini-card">
                    <div class="mini-label">Reprogramadas</div>
                    <div class="mini-value"><?= $reprogMes ?></div>
                </div>
            </div>
        </div>
    </div>
</div>
    <footer>
        <h4>SGOE - Sistema de Gestión Odontológico Esperanza - Creado por Dilkin, Oscar y De Matos Esteban</h4>
    </footer>
    </body>
    </html>