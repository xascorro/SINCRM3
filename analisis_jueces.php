<?php
$start_time = microtime(true);
include('security.php');
include('includes/header.php');
include('includes/navbar.php');

// Obtener la competición activa (Prioridad: GET > Session)
$competicion_id = intval($_GET['comp_id'] ?? $_SESSION['id_competicion_usuario'] ?? $_SESSION['id_competicion_activa'] ?? 0);

if (!$competicion_id) {
    echo "<script>
        Swal.fire({ icon: 'info', title: 'Selecciona una competición', text: 'Debes seleccionar una competición en el Dashboard para ver el análisis.', confirmButtonText: 'Ir al Dashboard' }).then(() => { window.location.href = 'index.php'; });
    </script>";
    exit();
}

$q_comp = "SELECT nombre FROM competiciones WHERE id = '$competicion_id'";
$res_comp = mysqli_query($connection, $q_comp);
$comp_data = mysqli_fetch_assoc($res_comp);

write_log("Consulta de Auditoría BIAS (Global) para: " . $comp_data['nombre'], "INFO");

/**
 * CONSULTA DE PERSISTENCIA
 */
$q_check = "SELECT COUNT(*) as total FROM auditoria_jueces_stats WHERE id_competicion = $competicion_id";
$has_data = mysqli_fetch_assoc(mysqli_query($connection, $q_check))['total'] > 0;

$stats_jueces = [];

if ($has_data) {
    // CARGA RÁPIDA DESDE BD
    $q_saved = "SELECT * FROM auditoria_jueces_stats WHERE id_competicion = $competicion_id AND entidad_tipo = 'GLOBAL' ORDER BY nombre_entidad ASC";
    $res_saved = mysqli_query($connection, $q_saved);
    while ($s = mysqli_fetch_assoc($res_saved)) {
        // LOGICA DE ANONIMIZACIÓN PARA EL ROL JUEZ (id_rol = 4)
        $es_el_juez_logueado = ($_SESSION['id_rol'] == 4 && $s['id_juez'] == $_SESSION['id_juez_v3']);
        $nombre_display = $s['nombre_entidad'];
        if($_SESSION['id_rol'] == 4 && !$es_el_juez_logueado) {
            $nombre_display = "Juez Anónimo";
        }

        $stats_jueces[] = [
            'nombre' => $nombre_display,
            'is_orphan' => (strpos($s['group_key'], 'P_') !== false),
            'pjs' => explode(',', $s['pjs_asociados']),
            'total' => $s['total_notas'],
            'bajas' => $s['bajas'],
            'altas' => $s['altas'],
            'pct' => ($s['total_notas'] > 0) ? round((($s['bajas'] + $s['altas']) / $s['total_notas']) * 100, 1) : 0,
            'desviacion' => $s['bias_score'],
            'precision' => $s['precision_aqua']
        ];
    }
}
 else {
    // CÁLCULO EN VIVO (Lento)
    $q_map = "SELECT DISTINCT pj.id_panel_juez, p.id_juez, j.nombre, j.apellidos FROM puntuaciones_jueces pj LEFT JOIN inscripciones_figuras i ON pj.id_inscripcion_figuras = i.id LEFT JOIN rutinas r ON pj.id_rutina = r.id LEFT JOIN panel_jueces p ON pj.id_panel_juez = p.id LEFT JOIN jueces j ON p.id_juez = j.id WHERE (i.id_competicion = '$competicion_id' OR r.id_competicion = '$competicion_id') AND (p.id_juez != 108 OR p.id_juez IS NULL)";
    $res_map = mysqli_query($connection, $q_map);
    $jueces_data = []; 
    while ($row = mysqli_fetch_assoc($res_map)) {
        $id_pj = $row['id_panel_juez']; $id_juez = $row['id_juez'] ?? 0;
        $group_key = ($id_juez > 0) ? "J_$id_juez" : "P_$id_pj";
        if (!isset($jueces_data[$group_key])) {
            $nombre_display = ($id_juez > 0) ? ($row['nombre'] . ' ' . $row['apellidos']) : "Juez Desconocido (ID $id_pj)";
            if ($id_juez == 108) $nombre_display = "Juez MEDIA (Automático)";

            $jueces_data[$group_key] = [
                'nombre' => $nombre_display, 'is_orphan' => ($id_juez == 0), 'id_juez' => $id_juez, 'pjs' => [], 'total' => 0, 'bajas' => 0, 'altas' => 0, 'desv_sum' => 0, 'desv_count' => 0, 'precision_count' => 0];
        }
        $jueces_data[$group_key]['pjs'][] = $id_pj;
    }
    foreach ($jueces_data as $key => &$data) {
        $pjs_str = implode(',', $data['pjs']);
        $q_stats = "SELECT pj.nota, (SELECT MIN(pj2.nota) FROM puntuaciones_jueces pj2 WHERE pj2.id_inscripcion_figuras = pj.id_inscripcion_figuras AND pj2.id_elemento = pj.id_elemento AND pj.id_inscripcion_figuras > 0) as min_fig, (SELECT MAX(pj2.nota) FROM puntuaciones_jueces pj2 WHERE pj2.id_inscripcion_figuras = pj.id_inscripcion_figuras AND pj2.id_elemento = pj.id_elemento AND pj.id_inscripcion_figuras > 0) as max_fig, (SELECT MIN(pj2.nota) FROM puntuaciones_jueces pj2 WHERE pj2.id_rutina = pj.id_rutina AND pj2.id_elemento = pj.id_elemento AND pj2.tipo_ia = pj.tipo_ia AND pj.id_rutina > 0) as min_rut, (SELECT MAX(pj2.nota) FROM puntuaciones_jueces pj2 WHERE pj2.id_rutina = pj.id_rutina AND pj2.id_elemento = pj.id_elemento AND pj2.tipo_ia = pj.tipo_ia AND pj.id_rutina > 0) as max_rut, (SELECT ROUND(AVG(pj3.nota), 1) FROM puntuaciones_jueces pj3 WHERE ((pj3.id_inscripcion_figuras > 0 AND pj3.id_inscripcion_figuras = pj.id_inscripcion_figuras AND pj3.id_elemento = pj.id_elemento) OR (pj3.id_rutina > 0 AND pj3.id_rutina = pj.id_rutina AND pj3.id_elemento = pj.id_elemento AND pj3.tipo_ia = pj.tipo_ia)) AND pj3.nota_menor = 0 AND pj3.nota_mayor = 0) as media_consenso FROM puntuaciones_jueces pj WHERE pj.id_panel_juez IN ($pjs_str)";
        $res_stats = mysqli_query($connection, $q_stats);
        while($s = mysqli_fetch_assoc($res_stats)) {
            $data['total']++; $nota = (float)$s['nota']; $min = (float)($s['min_fig'] ?? $s['min_rut'] ?? 0); $max = (float)($s['max_fig'] ?? $s['max_rut'] ?? 10); $consenso = ($s['media_consenso'] !== null) ? (float)$s['media_consenso'] : null;
            if ($nota <= $min && $min > 0) $data['bajas']++; elseif ($nota >= $max && $max < 10) $data['altas']++;
            if ($consenso !== null) { $diff = $nota - $consenso; $data['desv_sum'] += abs($diff); $data['desv_count']++; if (abs($diff) <= 0.2) $data['precision_count']++; }
        }
        $pct = ($data['total'] > 0) ? round((($data['bajas'] + $data['altas']) / $data['total']) * 100, 1) : 0;
        $desv = ($data['desv_count'] > 0) ? round($data['desv_sum'] / $data['desv_count'], 3) : 0;
        $precision = ($data['desv_count'] > 0) ? round(($data['precision_count'] / $data['desv_count']) * 100, 1) : 0;
        $stats_jueces[] = ['nombre' => $data['nombre'], 'is_orphan' => $data['is_orphan'], 'pjs' => $data['pjs'], 'total' => $data['total'], 'bajas' => $data['bajas'], 'altas' => $data['altas'], 'pct' => $pct, 'desviacion' => $desv, 'precision' => $precision];
    }
    usort($stats_jueces, function($a, $b) { return strcmp($a['nombre'], $b['nombre']); });
}

// --- KPIs GLOBALES ---
$global_desv_sum = 0; $juez_hierro = ['nombre' => '-', 'pct' => 0]; $juez_generoso = ['nombre' => '-', 'pct' => 0];
$chart_labels = []; $chart_desv = []; $chart_pct = []; $chart_bajas = []; $chart_altas = []; $chart_validas = [];
foreach ($stats_jueces as $s) {
    $global_desv_sum += abs($s['desviacion']);
    $pct_bajas = ($s['total'] > 0) ? ($s['bajas'] / $s['total']) * 100 : 0;
    
    // El nombre ya viene anonimizado en $s['nombre'] por el bucle anterior
    if ($pct_bajas > $juez_hierro['pct']) $juez_hierro = ['nombre' => $s['nombre'], 'pct' => round($pct_bajas, 1)];
    $pct_altas = ($s['total'] > 0) ? ($s['altas'] / $s['total']) * 100 : 0;
    if ($pct_altas > $juez_generoso['pct']) $juez_generoso = ['nombre' => $s['nombre'], 'pct' => round($pct_altas, 1)];
    
    $chart_labels[] = $s['nombre']; $chart_desv[] = $s['desviacion']; $chart_pct[] = $s['pct'];
    $chart_bajas[] = round($pct_bajas, 1); $chart_altas[] = round($pct_altas, 1); $chart_validas[] = round(100 - $pct_bajas - $pct_altas, 1);
}
$avg_global_desv = (count($stats_jueces) > 0) ? ($global_desv_sum / count($stats_jueces)) : 0;
$consenso_score = max(0, min(10, 10 - ($avg_global_desv * 10)));
$consenso_color = ($consenso_score >= 8) ? 'text-emerald-500' : (($consenso_score >= 5) ? 'text-amber-500' : 'text-red-500');
?>

<main class="flex-1 flex flex-col min-w-0 bg-surface">
    <?php include('includes/topbar.php'); ?>

    <div class="px-3 py-4 sm:p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend">

        <?php include('includes/alertas_v4.php'); ?>

        <!-- Header de Sección -->
        <div class="mb-6 sm:mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4 sm:gap-6">
            <div>
                <a href="ranking_jueces.php" class="text-blue-600 font-bold text-xs uppercase tracking-widest mb-2 flex items-center gap-1.5 hover:gap-2 transition-all no-underline">
                    <i class="fas fa-arrow-left text-[10px]"></i> Volver al Ranking Global
                </a>
                <h1 class="text-2xl sm:text-4xl font-black text-slate-800 tracking-tighter mb-1 sm:mb-2 flex items-center gap-2.5 sm:gap-3">
                    <span class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 flex items-center justify-center text-primary shadow-sm border border-slate-200">
                        <i class="fas fa-calendar-check text-base sm:text-lg"></i>
                    </span>
                    Auditoría de Evento
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 font-medium flex items-center gap-2 flex-wrap">
                    <span><?php echo $comp_data['nombre']; ?></span>
                    <?php if(!$has_data): ?>
                        <span class="text-[9px] bg-amber-100 text-amber-700 font-black px-2 py-0.5 rounded-md uppercase">Provisional</span>
                    <?php endif; ?>
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 sm:gap-3">
                <!-- Badge tiempo de carga -->
                <div class="px-3.5 py-2 bg-slate-900 text-white rounded-xl sm:rounded-2xl shadow-sm flex items-center gap-2.5 border border-slate-800">
                    <i class="fas fa-bolt text-amber-400 text-xs"></i>
                    <div class="leading-none">
                        <p class="text-[8px] font-black uppercase text-slate-400 mb-0.5 tracking-widest">Carga</p>
                        <p class="text-xs font-black tabular-nums"><?php echo round(microtime(true) - $start_time, 3); ?> <span class="text-[9px] opacity-60">seg</span></p>
                    </div>
                </div>

                <!-- Botón Acción -->
                <?php if(!$has_data): ?>
                    <button onclick="saveAudit()" class="px-5 sm:px-6 py-2.5 sm:py-3 bg-blue-600 text-white font-black uppercase text-xs tracking-wider rounded-xl sm:rounded-2xl shadow-lg shadow-blue-500/20 hover:scale-105 active:scale-[0.98] transition-all flex items-center gap-2">
                        <i class="fas fa-save text-xs"></i> Grabar Auditoría
                    </button>
                <?php else: ?>
                    <button onclick="saveAudit()" class="px-5 sm:px-6 py-2.5 sm:py-3 bg-white text-slate-700 border border-slate-200 font-black uppercase text-xs tracking-wider rounded-xl sm:rounded-2xl shadow-sm hover:bg-slate-50 active:scale-[0.98] transition-all flex items-center gap-2">
                        <i class="fas fa-sync text-xs text-blue-600"></i> Recalcular
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- KPIs del Evento -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 sm:gap-6 mb-6 sm:mb-10">
            <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200 border-l-4 sm:border-l-[6px] border-l-emerald-500">
                <div class="flex justify-between items-start mb-2">
                    <span class="text-[9px] font-black uppercase text-slate-400 tracking-widest truncate">Consenso Global</span>
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600"><i class="fas fa-hands-holding-circle text-xs"></i></div>
                </div>
                <h3 class="text-2xl sm:text-3xl font-black <?php echo $consenso_color; ?> tabular-nums leading-none">
                    <?php echo number_format($consenso_score, 1); ?><span class="text-sm sm:text-base text-slate-400">/10</span>
                </h3>
                <p class="text-[9px] sm:text-[10px] font-bold text-slate-400 mt-2 uppercase tracking-tight">Índice de similitud entre jueces</p>
            </div>

            <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200 border-l-4 sm:border-l-[6px] border-l-red-500">
                <div class="flex justify-between items-start mb-2">
                    <span class="text-[9px] font-black uppercase text-slate-400 tracking-widest truncate">El Juez de Hierro</span>
                    <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center text-red-600"><i class="fas fa-hand-fist text-xs"></i></div>
                </div>
                <h3 class="text-base sm:text-xl font-black text-slate-800 leading-tight truncate"><?php echo $juez_hierro['nombre']; ?></h3>
                <p class="text-[9px] sm:text-[10px] font-bold text-red-500 mt-1 uppercase tracking-tight tabular-nums"><?php echo $juez_hierro['pct']; ?>% notas mínimas</p>
            </div>

            <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200 border-l-4 sm:border-l-[6px] border-l-blue-500">
                <div class="flex justify-between items-start mb-2">
                    <span class="text-[9px] font-black uppercase text-slate-400 tracking-widest truncate">El Juez Generoso</span>
                    <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600"><i class="fas fa-hand-holding-heart text-xs"></i></div>
                </div>
                <h3 class="text-base sm:text-xl font-black text-slate-800 leading-tight truncate"><?php echo $juez_generoso['nombre']; ?></h3>
                <p class="text-[9px] sm:text-[10px] font-bold text-blue-500 mt-1 uppercase tracking-tight tabular-nums"><?php echo $juez_generoso['pct']; ?>% notas máximas</p>
            </div>
        </div>

        <!-- Gráficos del Evento -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-8 mb-6 sm:mb-10">
            <div class="bg-white p-4 sm:p-7 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200">
                <h3 class="text-sm sm:text-base font-black text-slate-800 mb-1 uppercase tracking-tight flex items-center gap-2">
                    <i class="fas fa-bullseye text-blue-600"></i> Calidad: Desviación vs Eliminaciones
                </h3>
                <p class="text-[9px] sm:text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Dispersión de notas anuladas</p>
                <div class="relative h-64 sm:h-80"><canvas id="scatterChart"></canvas></div>
            </div>

            <div class="bg-white p-4 sm:p-7 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200">
                <h3 class="text-sm sm:text-base font-black text-slate-800 mb-1 uppercase tracking-tight flex items-center gap-2">
                    <i class="fas fa-chart-pie text-emerald-600"></i> Tendencia de Puntuación por Juez
                </h3>
                <p class="text-[9px] sm:text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Distribución: Bajas / Válidas / Altas</p>
                <div class="relative h-64 sm:h-80"><canvas id="barChart"></canvas></div>
            </div>
        </div>

        <!-- Resumen de Desempeño Consolidado -->
        <div class="bg-white rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200 overflow-hidden mb-6 sm:mb-10">
            <div class="px-4 sm:px-8 py-3.5 sm:py-6 bg-slate-50/50 border-b border-slate-100 flex flex-col sm:flex-row justify-between sm:items-center gap-3">
                <h2 class="text-sm sm:text-base font-black text-slate-800 uppercase tracking-tight flex items-center gap-2">
                    <i class="fas fa-chart-bar text-blue-600"></i> Resumen de Desempeño Consolidado
                </h2>
                <div class="flex items-center gap-3">
                    <div class="relative w-full sm:w-64">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"><i class="fas fa-search text-xs"></i></span>
                        <input type="text" id="auditSearchInput" placeholder="Buscar juez..." 
                               class="w-full pl-9 pr-3.5 py-1.5 sm:py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 outline-none focus:border-blue-500"
                               onkeyup="filterAuditJueces()">
                    </div>
                    <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest tabular-nums shrink-0"><?php echo count($stats_jueces); ?> jueces</span>
                </div>
            </div>

            <!-- Vista Móvil: Cards Fluidas (md:hidden) -->
            <div class="md:hidden divide-y divide-slate-100" id="auditMobileContainer">
                <?php if (empty($stats_jueces)): ?>
                    <div class="p-8 text-center text-slate-400 text-xs font-bold uppercase tracking-widest">No hay jueces en este evento</div>
                <?php else: ?>
                    <?php foreach($stats_jueces as $s): 
                        $bg_prec = ($s['precision'] >= 70) ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : (($s['precision'] >= 50) ? 'bg-amber-50 text-amber-600 border-amber-100' : 'bg-red-50 text-red-600 border-red-100');
                        $bg_desv = (abs($s['desviacion']) >= 0.3) ? 'bg-red-50 text-red-600 border-red-100' : 'bg-blue-50 text-blue-600 border-blue-100';
                    ?>
                    <div class="audit-mobile-card p-3.5 sm:p-4 hover:bg-slate-50/60 transition-colors"
                         data-search="<?php echo htmlspecialchars(strtolower($s['nombre'])); ?>">
                        <div class="flex items-start justify-between gap-3 mb-2">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <div class="w-8 h-8 rounded-full <?php echo $s['is_orphan'] ? 'bg-red-50 text-red-400' : 'bg-slate-100 text-slate-500'; ?> flex items-center justify-center font-bold text-xs shrink-0 border border-slate-200">
                                    <?php echo strtoupper(substr($s['nombre'], 0, 1)); ?>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-xs sm:text-sm font-black text-slate-800 uppercase truncate"><?php echo $s['nombre']; ?></h3>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider tabular-nums"><?php echo count($s['pjs']); ?> paneles asignados</span>
                                </div>
                            </div>
                            <a href="analisis_juez_detalle.php?pjs=<?php echo implode(',', $s['pjs']); ?>&comp_id=<?php echo $competicion_id; ?>" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-600 bg-slate-100 hover:bg-blue-600 hover:text-white active:scale-[0.98] transition-all shrink-0">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 pt-1 text-[10px]">
                            <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase border <?php echo $bg_prec; ?> tabular-nums">
                                Prec: <?php echo $s['precision']; ?>%
                            </span>
                            <span class="px-2 py-0.5 rounded-md text-[9px] font-black border <?php echo $bg_desv; ?> tabular-nums">
                                Bias: ±<?php echo number_format($s['desviacion'], 3); ?>
                            </span>
                            <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded-md text-[9px] font-bold tabular-nums">
                                <?php echo $s['total']; ?> notas
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Vista Escritorio: Tabla (hidden md:block) -->
            <div class="hidden md:block overflow-x-auto no-scrollbar">
                <table class="w-full text-left border-collapse" id="auditTable">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-400 text-[10px] font-black uppercase tracking-[0.2em]">
                            <th class="px-6 py-4">Juez</th>
                            <th class="px-6 py-4 text-center">Total Notas</th>
                            <th class="px-6 py-4 text-center">Precisión AQUA</th>
                            <th class="px-6 py-4 text-center">Bias Score</th>
                            <th class="px-6 py-4 text-center w-24">Detalle</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach($stats_jueces as $s): 
                            $bg_prec = ($s['precision'] >= 70) ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : (($s['precision'] >= 50) ? 'bg-amber-50 text-amber-600 border-amber-100' : 'bg-red-50 text-red-600 border-red-100');
                            $bg_desv = (abs($s['desviacion']) >= 0.3) ? 'bg-red-50 text-red-600 border-red-100' : 'bg-blue-50 text-blue-600 border-blue-100';
                        ?>
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full <?php echo $s['is_orphan'] ? 'bg-red-50 text-red-400' : 'bg-slate-100 text-slate-500'; ?> flex items-center justify-center font-bold text-xs border border-slate-200">
                                        <?php echo strtoupper(substr($s['nombre'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <span class="font-bold text-slate-800 uppercase text-xs tracking-tight block"><?php echo $s['nombre']; ?></span>
                                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest tabular-nums"><?php echo count($s['pjs']); ?> paneles asignados</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center font-black text-slate-800 tabular-nums"><?php echo $s['total']; ?></td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 rounded-xl text-xs font-black uppercase border <?php echo $bg_prec; ?> tabular-nums"><?php echo $s['precision']; ?>%</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 rounded-xl text-xs font-black border <?php echo $bg_desv; ?> tabular-nums">±<?php echo number_format($s['desviacion'], 3); ?></span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="analisis_juez_detalle.php?pjs=<?php echo implode(',', $s['pjs']); ?>&comp_id=<?php echo $competicion_id; ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-900 text-white hover:bg-blue-600 active:scale-[0.98] transition-all shadow-sm">
                                    <i class="fas fa-eye text-xs"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(count($stats_jueces) == 0): ?>
                            <tr><td colspan="5" class="p-12 text-center text-slate-400 font-bold italic">No se encontraron puntuaciones para esta competición.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function filterAuditJueces() {
    const input = document.getElementById('auditSearchInput');
    const filter = input.value.toLowerCase().trim();
    
    // Filtrar tabla escritorio
    const table = document.getElementById('auditTable');
    if (table) {
        const tr = table.getElementsByTagName('tr');
        for (let i = 1; i < tr.length; i++) {
            let textContent = tr[i].textContent.toLowerCase();
            tr[i].style.display = textContent.indexOf(filter) > -1 ? "" : "none";
        }
    }
    
    // Filtrar cards móvil
    const mobileCards = document.querySelectorAll('.audit-mobile-card');
    mobileCards.forEach(card => {
        const searchAttr = card.getAttribute('data-search') || card.textContent.toLowerCase();
        card.style.display = searchAttr.indexOf(filter) > -1 ? "" : "none";
    });
}

function saveAudit() {
    Swal.fire({
        title: 'Procesando Auditoría',
        text: 'Esto puede tardar unos segundos dependiendo del volumen de notas...',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });
    fetch('auditoria_jueces_code.php?action=save&id_competicion=<?php echo $competicion_id; ?>')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire('¡Éxito!', 'Los datos de auditoría han sido precalculados y grabados.', 'success').then(() => location.reload());
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        });
}

document.addEventListener("DOMContentLoaded", function() {
    const labels = <?php echo json_encode($chart_labels); ?>;
    const desv = <?php echo json_encode($chart_desv); ?>;
    const pct = <?php echo json_encode($chart_pct); ?>;
    const bajas = <?php echo json_encode($chart_bajas); ?>;
    const altas = <?php echo json_encode($chart_altas); ?>;
    const validas = <?php echo json_encode($chart_validas); ?>;

    new Chart(document.getElementById('scatterChart'), { 
        type: 'scatter', 
        data: { 
            datasets: [{ 
                label: 'Jueces', 
                data: labels.map((label, index) => ({ x: Math.abs(desv[index]), y: pct[index], label: label })), 
                backgroundColor: '#3b82f6' 
            }] 
        }, 
        options: { 
            responsive: true, 
            maintainAspectRatio: false, 
            scales: { 
                x: { title: { display: true, text: 'Bias Score (Absoluto)' } }, 
                y: { title: { display: true, text: '% Notas Eliminadas' }, max: 100 } 
            } 
        } 
    });

    new Chart(document.getElementById('barChart'), { 
        type: 'bar', 
        data: { 
            labels: labels, 
            datasets: [
                { label: 'Bajas', data: bajas, backgroundColor: '#ef4444' }, 
                { label: 'Válidas', data: validas, backgroundColor: '#cbd5e1' }, 
                { label: 'Altas', data: altas, backgroundColor: '#10b981' }
            ] 
        }, 
        options: { 
            responsive: true, 
            maintainAspectRatio: false, 
            indexAxis: 'y', 
            scales: { 
                x: { stacked: true, max: 100 }, 
                y: { stacked: true } 
            } 
        } 
    });
});
</script>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
