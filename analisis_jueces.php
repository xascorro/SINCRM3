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

$es_admin_audit = in_array($_SESSION['id_rol'] ?? 0, [1, 2, 3]);
$es_usuario_juez = ($_SESSION['id_rol'] == 4 || (!empty($_SESSION['id_juez_v3']) && !$es_admin_audit));

if ($has_data) {
    // CARGA DE ASIMETRÍAS POR CLUB
    $judge_asymmetries = [];
    $q_asym = "SELECT pjs_asociados, nombre_entidad, altas, bajas, total_notas,
                      (CASE WHEN bajas > 0 THEN altas/bajas ELSE altas END) as ratio
               FROM auditoria_jueces_stats
               WHERE id_competicion = $competicion_id AND entidad_tipo = 'CLUB'
               HAVING total_notas >= 4 AND ((altas >= 4 AND (bajas <= 1 OR ratio >= 2.5)) OR (bajas >= 4 AND (altas <= 1 OR ratio <= 0.4)))";
    $res_asym = mysqli_query($connection, $q_asym);
    if ($res_asym) {
        while ($ra = mysqli_fetch_assoc($res_asym)) {
            $p_key = $ra['pjs_asociados'];
            if (!isset($judge_asymmetries[$p_key])) {
                $is_altas = ((float)$ra['ratio'] >= 2.0 || $ra['altas'] > $ra['bajas']);
                $judge_asymmetries[$p_key] = [
                    'club' => $ra['nombre_entidad'],
                    'altas' => (int)$ra['altas'],
                    'bajas' => (int)$ra['bajas'],
                    'ratio' => round((float)$ra['ratio'], 1),
                    'tipo' => $is_altas ? 'altas' : 'bajas'
                ];
            }
        }
    }

    // CARGA RÁPIDA DESDE BD
    $q_saved = "SELECT * FROM auditoria_jueces_stats WHERE id_competicion = $competicion_id AND entidad_tipo = 'GLOBAL' ORDER BY nombre_entidad ASC";
    $res_saved = mysqli_query($connection, $q_saved);
    while ($s = mysqli_fetch_assoc($res_saved)) {
        // LOGICA DE ANONIMIZACIÓN PARA JUECES (Rol 4 o usuarios con id_juez_v3 que no sean Administradores)
        $es_el_juez_logueado = ($es_usuario_juez && $s['id_juez'] == ($_SESSION['id_juez_v3'] ?? 0));
        $nombre_display = $s['nombre_entidad'];
        if($es_usuario_juez && !$es_el_juez_logueado) {
            $nombre_display = "Juez Anónimo";
        }

        $stats_jueces[] = [
            'nombre' => $nombre_display,
            'id_juez' => (int)$s['id_juez'],
            'is_orphan' => (strpos($s['group_key'], 'P_') !== false),
            'pjs' => explode(',', $s['pjs_asociados']),
            'total' => $s['total_notas'],
            'bajas' => $s['bajas'],
            'altas' => $s['altas'],
            'pct' => ($s['total_notas'] > 0) ? round((($s['bajas'] + $s['altas']) / $s['total_notas']) * 100, 1) : 0,
            'desviacion' => $s['bias_score'],
            'precision' => $s['precision_aqua'],
            'asimetria' => $judge_asymmetries[$s['pjs_asociados']] ?? null
        ];
    }
}
 else {
    // CÁLCULO EN VIVO OPTIMIZADO (Consenso oficial World Aquatics y rango sin extremos)
    // 1. Mapeo de consenso y rangos para Figuras
    $fig_map = [];
    $q_fig = "SELECT id_inscripcion_figuras, COALESCE(id_elemento, 0) as el,
                     MIN(nota) as min_nota, MAX(nota) as max_nota,
                     ROUND(AVG(CASE WHEN (nota_menor IS NULL OR nota_menor = '' OR nota_menor = '0' OR nota_menor = 'no') 
                                    AND (nota_mayor IS NULL OR nota_mayor = '' OR nota_mayor = '0' OR nota_mayor = 'no') 
                               THEN nota ELSE NULL END), 2) as media_consenso
              FROM puntuaciones_jueces
              WHERE id_inscripcion_figuras IN (SELECT id FROM inscripciones_figuras WHERE id_competicion = '$competicion_id')
              GROUP BY id_inscripcion_figuras, COALESCE(id_elemento, 0)";
    $res_fig = mysqli_query($connection, $q_fig);
    if ($res_fig) {
        while ($rf = mysqli_fetch_assoc($res_fig)) {
            $fig_map[$rf['id_inscripcion_figuras'] . '_' . $rf['el']] = $rf;
        }
    }

    // 2. Mapeo de consenso y rangos para Rutinas
    $rut_map = [];
    $q_rut = "SELECT id_rutina, COALESCE(id_elemento, 0) as el, COALESCE(tipo_ia, '') as tipo_ia,
                     MIN(nota) as min_nota, MAX(nota) as max_nota,
                     ROUND(AVG(CASE WHEN (nota_menor IS NULL OR nota_menor = '' OR nota_menor = '0' OR nota_menor = 'no') 
                                    AND (nota_mayor IS NULL OR nota_mayor = '' OR nota_mayor = '0' OR nota_mayor = 'no') 
                               THEN nota ELSE NULL END), 2) as media_consenso
              FROM puntuaciones_jueces
              WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion = '$competicion_id')
              GROUP BY id_rutina, COALESCE(id_elemento, 0), COALESCE(tipo_ia, '')";
    $res_rut = mysqli_query($connection, $q_rut);
    if ($res_rut) {
        while ($rr = mysqli_fetch_assoc($res_rut)) {
            $rut_map[$rr['id_rutina'] . '_' . $rr['el'] . '_' . $rr['tipo_ia']] = $rr;
        }
    }

    // 3. Mapeo de paneles y jueces asignados
    $q_map = "SELECT DISTINCT pj.id_panel_juez, p.id_juez, j.nombre, j.apellidos 
              FROM puntuaciones_jueces pj 
              LEFT JOIN inscripciones_figuras i ON pj.id_inscripcion_figuras = i.id 
              LEFT JOIN rutinas r ON pj.id_rutina = r.id 
              LEFT JOIN panel_jueces p ON pj.id_panel_juez = p.id 
              LEFT JOIN jueces j ON p.id_juez = j.id 
              WHERE (i.id_competicion = '$competicion_id' OR r.id_competicion = '$competicion_id') 
                AND (p.id_juez != 108 OR p.id_juez IS NULL)";
    $res_map = mysqli_query($connection, $q_map);
    $jueces_data = []; 
    while ($row = mysqli_fetch_assoc($res_map)) {
        $id_pj = (int)$row['id_panel_juez']; 
        $id_juez = (int)($row['id_juez'] ?? 0);
        $group_key = ($id_juez > 0) ? "J_$id_juez" : "P_$id_pj";
        if (!isset($jueces_data[$group_key])) {
            $es_el_juez_logueado = ($es_usuario_juez && $id_juez == ($_SESSION['id_juez_v3'] ?? 0));
            $nombre_display = ($id_juez > 0) ? ($row['nombre'] . ' ' . $row['apellidos']) : "Juez Desconocido (ID $id_pj)";
            if ($id_juez == 108) $nombre_display = "Juez MEDIA (Automático)";
            if ($es_usuario_juez && !$es_el_juez_logueado) {
                $nombre_display = "Juez Anónimo";
            }

            $jueces_data[$group_key] = [
                'nombre' => $nombre_display, 
                'is_orphan' => ($id_juez == 0), 
                'id_juez' => $id_juez, 
                'pjs' => [], 
                'total' => 0, 
                'bajas' => 0, 
                'altas' => 0, 
                'desv_sum' => 0, 
                'desv_count' => 0, 
                'precision_count' => 0
            ];
        }
        $jueces_data[$group_key]['pjs'][] = $id_pj;
    }

    // 4. Cálculo de métricas por juez
    foreach ($jueces_data as $key => &$data) {
        $pjs_str = implode(',', $data['pjs']);
        $q_stats = "SELECT id, id_panel_juez, id_inscripcion_figuras, id_rutina, id_elemento, tipo_ia, nota 
                    FROM puntuaciones_jueces 
                    WHERE id_panel_juez IN ($pjs_str)";
        $res_stats = mysqli_query($connection, $q_stats);
        while($s = mysqli_fetch_assoc($res_stats)) {
            $data['total']++; 
            $nota = (float)$s['nota']; 
            $el = (int)($s['id_elemento'] ?? 0);
            $tipo_ia = $s['tipo_ia'] ?? '';
            
            $min = 0; $max = 10; $consenso = null;
            if (!empty($s['id_inscripcion_figuras'])) {
                $k = $s['id_inscripcion_figuras'] . '_' . $el;
                if (isset($fig_map[$k])) {
                    $min = (float)$fig_map[$k]['min_nota'];
                    $max = (float)$fig_map[$k]['max_nota'];
                    $consenso = ($fig_map[$k]['media_consenso'] !== null) ? (float)$fig_map[$k]['media_consenso'] : null;
                }
            } elseif (!empty($s['id_rutina'])) {
                $k = $s['id_rutina'] . '_' . $el . '_' . $tipo_ia;
                if (isset($rut_map[$k])) {
                    $min = (float)$rut_map[$k]['min_nota'];
                    $max = (float)$rut_map[$k]['max_nota'];
                    $consenso = ($rut_map[$k]['media_consenso'] !== null) ? (float)$rut_map[$k]['media_consenso'] : null;
                }
            }

            if ($min < $max) {
                if ($nota <= $min && $min > 0) $data['bajas']++;
                elseif ($nota >= $max && $max < 10) $data['altas']++;
            }

            if ($consenso !== null) { 
                $diff = $nota - $consenso; 
                $data['desv_sum'] += abs($diff); 
                $data['desv_count']++; 
                if (abs($diff) <= 0.2) $data['precision_count']++; 
            }
        }
        $pct = ($data['total'] > 0) ? round((($data['bajas'] + $data['altas']) / $data['total']) * 100, 1) : 0;
        $desv = ($data['desv_count'] > 0) ? round($data['desv_sum'] / $data['desv_count'], 3) : 0;
        $precision = ($data['desv_count'] > 0) ? round(($data['precision_count'] / $data['desv_count']) * 100, 1) : 0;
        $stats_jueces[] = [
            'nombre' => $data['nombre'], 
            'is_orphan' => $data['is_orphan'], 
            'pjs' => $data['pjs'], 
            'total' => $data['total'], 
            'bajas' => $data['bajas'], 
            'altas' => $data['altas'], 
            'pct' => $pct, 
            'desviacion' => $desv, 
            'precision' => $precision
        ];
    }
    usort($stats_jueces, function($a, $b) { return strcmp($a['nombre'], $b['nombre']); });
}

// --- KPIs GLOBALES ---
$global_desv_sum = 0; $global_prec_sum = 0;
$juez_hierro = ['nombre' => '-', 'pct' => 0]; $juez_generoso = ['nombre' => '-', 'pct' => 0];
$chart_labels = []; $chart_desv = []; $chart_pct = []; $chart_bajas = []; $chart_altas = []; $chart_validas = [];
foreach ($stats_jueces as $s) {
    $global_desv_sum += abs($s['desviacion']);
    $global_prec_sum += $s['precision'];
    $pct_bajas = ($s['total'] > 0) ? ($s['bajas'] / $s['total']) * 100 : 0;
    
    // El nombre ya viene anonimizado en $s['nombre'] por el bucle anterior
    if ($pct_bajas > $juez_hierro['pct']) $juez_hierro = ['nombre' => $s['nombre'], 'pct' => round($pct_bajas, 1)];
    $pct_altas = ($s['total'] > 0) ? ($s['altas'] / $s['total']) * 100 : 0;
    if ($pct_altas > $juez_generoso['pct']) $juez_generoso = ['nombre' => $s['nombre'], 'pct' => round($pct_altas, 1)];
    
    $chart_labels[] = $s['nombre']; $chart_desv[] = $s['desviacion']; $chart_pct[] = $s['pct'];
    $chart_bajas[] = round($pct_bajas, 1); $chart_altas[] = round($pct_altas, 1); $chart_validas[] = round(100 - $pct_bajas - $pct_altas, 1);
}
$avg_global_desv = (count($stats_jueces) > 0) ? ($global_desv_sum / count($stats_jueces)) : 0;
$avg_event_prec = (count($stats_jueces) > 0) ? round($global_prec_sum / count($stats_jueces), 1) : 0;
$consenso_score = max(0, min(10, 10 - ($avg_global_desv * 10)));
$consenso_color = ($consenso_score >= 8) ? 'text-emerald-500' : (($consenso_score >= 5) ? 'text-amber-500' : 'text-red-500');

// Matriz de Concordancia Cruzada (Heatmap)
$matrix_judges = [];
foreach ($stats_jueces as $sj) {
    if (!empty($sj['id_juez'])) {
        $name_parts = explode(' ', trim($sj['nombre']));
        $short = $name_parts[0] . ' ' . (substr($name_parts[1] ?? '', 0, 1)) . '.';
        $matrix_judges[$sj['id_juez']] = [
            'id' => $sj['id_juez'],
            'nombre' => $sj['nombre'],
            'short' => $short
        ];
    }
}

$matrix_data = [];
$q_matrix = "SELECT ja, jb, SUM(shared) as total_shared, SUM(concord) as concord_aqua FROM (
    SELECT p1.id_juez as ja, p2.id_juez as jb, COUNT(*) as shared,
           SUM(CASE WHEN ABS(pj1.nota - pj2.nota) <= 0.20 THEN 1 ELSE 0 END) as concord
    FROM puntuaciones_jueces pj1
    JOIN panel_jueces p1 ON pj1.id_panel_juez = p1.id
    JOIN panel_jueces p2 ON p1.id_fase = p2.id_fase AND p2.id_juez > p1.id_juez
    JOIN puntuaciones_jueces pj2 ON pj2.id_panel_juez = p2.id AND pj1.id_inscripcion_figuras = pj2.id_inscripcion_figuras AND COALESCE(pj1.id_elemento,0) = COALESCE(pj2.id_elemento,0)
    WHERE p1.id_competicion = $competicion_id AND pj1.id_inscripcion_figuras > 0 AND p1.id_juez > 0 AND p2.id_juez > 0
    GROUP BY p1.id_juez, p2.id_juez
    
    UNION ALL
    
    SELECT p1.id_juez as ja, p2.id_juez as jb, COUNT(*) as shared,
           SUM(CASE WHEN ABS(pj1.nota - pj2.nota) <= 0.20 THEN 1 ELSE 0 END) as concord
    FROM puntuaciones_jueces pj1
    JOIN panel_jueces p1 ON pj1.id_panel_juez = p1.id
    JOIN panel_jueces p2 ON p1.id_fase = p2.id_fase AND p2.id_juez > p1.id_juez
    JOIN puntuaciones_jueces pj2 ON pj2.id_panel_juez = p2.id AND pj1.id_rutina = pj2.id_rutina AND COALESCE(pj1.id_elemento,0) = COALESCE(pj2.id_elemento,0) AND COALESCE(pj1.tipo_ia,'') = COALESCE(pj2.tipo_ia,'')
    WHERE p1.id_competicion = $competicion_id AND pj1.id_rutina > 0 AND p1.id_juez > 0 AND p2.id_juez > 0
    GROUP BY p1.id_juez, p2.id_juez
) u GROUP BY ja, jb";

$res_matrix = mysqli_query($connection, $q_matrix);
if ($res_matrix) {
    while ($m = mysqli_fetch_assoc($res_matrix)) {
        $ja = (int)$m['ja'];
        $jb = (int)$m['jb'];
        $pct = ($m['total_shared'] > 0) ? round(($m['concord_aqua'] / $m['total_shared']) * 100, 1) : 0;
        $matrix_data[$ja][$jb] = ['shared' => (int)$m['total_shared'], 'pct' => $pct];
        $matrix_data[$jb][$ja] = ['shared' => (int)$m['total_shared'], 'pct' => $pct];
    }
}

// Top figuras con mayor dispersión para el Informe de Debriefing
$q_top_discrep = "
    SELECT nombre_entidad, SUM(total_notas) as total_notas,
           ROUND(AVG(precision_aqua), 1) as prec_media,
           SUM(bajas) as total_bajas, SUM(altas) as total_altas,
           ROUND(AVG(bias_score), 3) as bias_medio
    FROM auditoria_jueces_stats
    WHERE id_competicion = $competicion_id AND entidad_tipo = 'FIGURA'
    GROUP BY nombre_entidad
    HAVING total_notas >= 8
    ORDER BY prec_media ASC, (SUM(bajas) + SUM(altas)) DESC
    LIMIT 3
";
$res_discrep = mysqli_query($connection, $q_top_discrep);
$debriefing_figures = [];
if ($res_discrep) {
    while ($df = mysqli_fetch_assoc($res_discrep)) {
        $debriefing_figures[] = $df;
    }
}
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

                <!-- Botón Informe Debriefing Juez Árbitro -->
                <button onclick="openDebriefingModal()" class="px-4 py-2 sm:py-2.5 bg-white text-slate-700 border border-slate-200 font-bold uppercase text-xs tracking-wider rounded-xl sm:rounded-2xl shadow-sm hover:bg-slate-50 active:scale-[0.98] transition-all flex items-center gap-2">
                    <i class="fas fa-file-signature text-xs text-purple-600"></i>
                    <span>Debriefing Clinic</span>
                </button>

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

        <!-- KPIs del Evento (Orientación Pedagógica World Aquatics) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-5 mb-6 sm:mb-10">
            <div class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200/90 border-l-4 sm:border-l-[5px] border-l-emerald-500 card-hover">
                <div class="flex justify-between items-start mb-2">
                    <span class="text-[9px] font-black uppercase text-slate-400 tracking-wider truncate">Consenso del Panel</span>
                    <div class="w-7 h-7 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600 text-xs"><i class="fas fa-handshake"></i></div>
                </div>
                <h3 class="text-xl sm:text-2xl font-black <?php echo $consenso_color; ?> tabular-nums leading-none">
                    <?php echo number_format($consenso_score, 1); ?><span class="text-xs sm:text-sm text-slate-400 font-normal">/10</span>
                </h3>
                <p class="text-[9px] font-bold text-slate-400 mt-2 uppercase tracking-tight">Cohesión global del panel</p>
            </div>

            <div class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200/90 border-l-4 sm:border-l-[5px] border-l-blue-500 card-hover">
                <div class="flex justify-between items-start mb-2">
                    <span class="text-[9px] font-black uppercase text-slate-400 tracking-wider truncate">Precisión Media AQUA</span>
                    <div class="w-7 h-7 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 text-xs"><i class="fas fa-bullseye"></i></div>
                </div>
                <h3 class="text-xl sm:text-2xl font-black text-blue-600 tabular-nums leading-none">
                    <?php echo number_format($avg_event_prec, 1); ?>%
                </h3>
                <p class="text-[9px] font-bold text-slate-400 mt-2 uppercase tracking-tight">Notas en margen ±0.2</p>
            </div>

            <div class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200/90 border-l-4 sm:border-l-[5px] border-l-rose-500 card-hover">
                <div class="flex justify-between items-start mb-2">
                    <span class="text-[9px] font-black uppercase text-slate-400 tracking-wider truncate">Criterio Más Exigente</span>
                    <div class="w-7 h-7 rounded-lg bg-rose-50 flex items-center justify-center text-rose-600 text-xs"><i class="fas fa-arrow-down-wide-short"></i></div>
                </div>
                <h3 class="text-xs sm:text-sm font-black text-slate-800 leading-tight truncate"><?php echo $juez_hierro['nombre']; ?></h3>
                <p class="text-[9px] font-bold text-rose-500 mt-1 uppercase tracking-tight tabular-nums"><?php echo $juez_hierro['pct']; ?>% notas mínimas</p>
            </div>

            <div class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200/90 border-l-4 sm:border-l-[5px] border-l-indigo-500 card-hover">
                <div class="flex justify-between items-start mb-2">
                    <span class="text-[9px] font-black uppercase text-slate-400 tracking-wider truncate">Criterio Más Benévolo</span>
                    <div class="w-7 h-7 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 text-xs"><i class="fas fa-arrow-up-wide-short"></i></div>
                </div>
                <h3 class="text-xs sm:text-sm font-black text-slate-800 leading-tight truncate"><?php echo $juez_generoso['nombre']; ?></h3>
                <p class="text-[9px] font-bold text-indigo-500 mt-1 uppercase tracking-tight tabular-nums"><?php echo $juez_generoso['pct']; ?>% notas máximas</p>
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

        <!-- MATRIZ DE CONCORDANCIA CRUZADA DEL PANEL (HEATMAP) -->
        <?php if(!empty($matrix_judges) && !empty($matrix_data)): ?>
        <div class="bg-white rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200 overflow-hidden mb-6 sm:mb-10">
            <div class="px-4 sm:px-8 py-3.5 sm:py-5 bg-slate-50/50 border-b border-slate-100 flex flex-col sm:flex-row justify-between sm:items-center gap-3">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xs">
                        <i class="fas fa-table-cells"></i>
                    </span>
                    <div>
                        <h2 class="text-sm sm:text-base font-black text-slate-800 uppercase tracking-tight">Matriz de Concordancia Cruzada (Panel Heatmap)</h2>
                        <p class="text-[10px] text-slate-400 font-semibold">% de notas coincidentes dentro del margen oficial AQUA (≤ 0.2 ptos) entre parejas de jueces</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 text-[9px] font-black uppercase text-slate-400">
                    <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> ≥70%</span>
                    <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> 50-69%</span>
                    <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> 35-49%</span>
                    <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> &lt;35%</span>
                </div>
            </div>

            <div class="overflow-x-auto p-4 sm:p-6 no-scrollbar">
                <table class="w-full text-center border-collapse">
                    <thead>
                        <tr>
                            <th class="p-2 text-left text-[9px] font-black uppercase text-slate-400">Juez \ Juez</th>
                            <?php foreach($matrix_judges as $jid => $jinfo): ?>
                                <th class="p-2 text-[9px] font-black uppercase text-slate-600 truncate max-w-[90px]" title="<?php echo htmlspecialchars($jinfo['nombre']); ?>">
                                    <?php echo htmlspecialchars($jinfo['short']); ?>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        <?php foreach($matrix_judges as $r_id => $r_info): ?>
                            <tr>
                                <td class="p-2 text-left font-black text-slate-800 text-xs truncate max-w-[120px]" title="<?php echo htmlspecialchars($r_info['nombre']); ?>">
                                    <?php echo htmlspecialchars($r_info['short']); ?>
                                </td>
                                <?php foreach($matrix_judges as $c_id => $c_info): 
                                    if ($r_id == $c_id) {
                                        echo "<td class='p-2'><span class='px-2 py-1 rounded-lg bg-slate-100 text-slate-400 text-[10px] font-bold block'>100%</span></td>";
                                    } elseif (isset($matrix_data[$r_id][$c_id])) {
                                        $cell = $matrix_data[$r_id][$c_id];
                                        $cp = $cell['pct'];
                                        $c_badge = ($cp >= 70) ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : (($cp >= 50) ? 'bg-blue-50 text-blue-700 border-blue-200' : (($cp >= 35) ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-rose-50 text-rose-700 border-rose-200'));
                                        $title_txt = "{$cell['shared']} notas compartidas | Concordancia: {$cp}%";
                                        echo "<td class='p-2' title='{$title_txt}'><span class='px-2 py-1 rounded-lg border text-[10px] font-black tabular-nums block {$c_badge}'>{$cp}%</span></td>";
                                    } else {
                                        echo "<td class='p-2 text-slate-300 font-bold text-[10px]'>—</td>";
                                    }
                                endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

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
                            <?php if(!empty($s['asimetria'])): 
                                $as = $s['asimetria'];
                                $as_bg = ($as['tipo'] == 'altas') ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-rose-50 text-rose-700 border-rose-200';
                            ?>
                                <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase border <?php echo $as_bg; ?>">
                                    <?php echo ($as['tipo'] == 'altas' ? 'Altas' : 'Bajas'); ?> <?php echo $as['club']; ?> (<?php echo $as['ratio']; ?>x)
                                </span>
                            <?php endif; ?>
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
                            <th class="px-6 py-4 text-center">Asimetría Club</th>
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
                                <?php if(!empty($s['asimetria'])): 
                                    $as = $s['asimetria'];
                                    $as_bg = ($as['tipo'] == 'altas') ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-rose-50 text-rose-700 border-rose-200';
                                ?>
                                    <span class="px-2.5 py-1 rounded-xl text-[10px] font-black uppercase border <?php echo $as_bg; ?> tabular-nums" title="Altas: <?php echo $as['altas']; ?> / Bajas: <?php echo $as['bajas']; ?>">
                                        <i class="fas fa-scale-unbalanced text-[8px] mr-1"></i><?php echo ($as['tipo'] == 'altas' ? 'Altas' : 'Bajas'); ?> <?php echo $as['club']; ?> (<?php echo $as['ratio']; ?>x)
                                    </span>
                                <?php else: ?>
                                    <span class="text-slate-300 text-xs font-bold">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="analisis_juez_detalle.php?pjs=<?php echo implode(',', $s['pjs']); ?>&comp_id=<?php echo $competicion_id; ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-900 text-white hover:bg-blue-600 active:scale-[0.98] transition-all shadow-sm">
                                    <i class="fas fa-eye text-xs"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(count($stats_jueces) == 0): ?>
                            <tr><td colspan="6" class="p-12 text-center text-slate-400 font-bold italic">No se encontraron puntuaciones para esta competición.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<!-- MODAL DE INFORME OFICIAL DE CLINIC & DEBRIEFING ARBITRAL -->
<div id="modalDebriefing" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs hidden flex items-center justify-center p-3 sm:p-5 no-print">
    <div class="bg-white w-full max-w-3xl rounded-3xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[90vh]">
        <!-- Header Modal -->
        <div class="p-5 sm:p-6 bg-slate-900 text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-2xl bg-purple-500/20 text-purple-400 flex items-center justify-center text-base border border-purple-500/30">
                    <i class="fas fa-file-signature"></i>
                </span>
                <div>
                    <h3 class="text-base font-black uppercase tracking-tight">Acta Oficial de Debriefing Arbitral</h3>
                    <p class="text-[11px] text-slate-400">Informe técnico para clinic post-competición (World Aquatics Standard)</p>
                </div>
            </div>
            <button onclick="closeDebriefingModal()" class="w-8 h-8 rounded-xl bg-slate-800 text-slate-400 hover:text-white flex items-center justify-center">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>

        <!-- Contenido Modal -->
        <div class="p-5 sm:p-6 overflow-y-auto space-y-6 text-slate-700">
            <!-- Bloque 1: Resumen de Competición -->
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80 flex flex-col sm:flex-row justify-between gap-4">
                <div>
                    <span class="text-[9px] font-black uppercase tracking-widest text-slate-400 block mb-0.5">Competición</span>
                    <h4 class="font-black text-slate-900 text-sm"><?php echo htmlspecialchars($comp_data['nombre']); ?></h4>
                    <span class="text-xs text-slate-500">Temporada Oficial FNRM / World Aquatics</span>
                </div>
                <div class="flex items-center gap-4 text-center">
                    <div>
                        <span class="text-[9px] font-black uppercase tracking-widest text-slate-400 block mb-0.5">Consenso Panel</span>
                        <span class="text-base font-black <?php echo $consenso_color; ?> tabular-nums"><?php echo number_format($consenso_score, 1); ?>/10</span>
                    </div>
                    <div class="w-px h-8 bg-slate-200"></div>
                    <div>
                        <span class="text-[9px] font-black uppercase tracking-widest text-slate-400 block mb-0.5">Precisión AQUA</span>
                        <span class="text-base font-black text-blue-600 tabular-nums"><?php echo number_format($avg_event_prec, 1); ?>%</span>
                    </div>
                </div>
            </div>

            <!-- Bloque 2: Puntos Clave para el Clinic (Figuras con Mayor Dispersión) -->
            <div>
                <h4 class="text-xs font-black uppercase tracking-wider text-slate-800 mb-2 flex items-center gap-2">
                    <i class="fas fa-triangle-exclamation text-amber-500"></i> Puntos Clave para el Clinic (Mayor Dispersión Técnica)
                </h4>
                <div class="space-y-2">
                    <?php if(!empty($debriefing_figures)): ?>
                        <?php foreach($debriefing_figures as $df): ?>
                            <div class="p-3 bg-amber-50/50 rounded-xl border border-amber-100 flex items-center justify-between text-xs">
                                <div>
                                    <span class="font-black text-slate-800 block"><?php echo htmlspecialchars($df['nombre_entidad']); ?></span>
                                    <span class="text-[10px] text-slate-500 font-semibold"><?php echo $df['total_notas']; ?> notas | Anulaciones: <?php echo $df['total_bajas']; ?> bajas / <?php echo $df['total_altas']; ?> altas</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] font-black uppercase text-amber-700 bg-amber-100/70 px-2.5 py-0.5 rounded-md border border-amber-200 tabular-nums">
                                        <?php echo number_format($df['prec_media'], 1); ?>% Precisión
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-xs text-slate-400 italic">No se detectaron discrepancias relevantes de panel en esta competición.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Bloque 3: Asimetrías Detectadas para Revisión de Uniformidad -->
            <div>
                <h4 class="text-xs font-black uppercase tracking-wider text-slate-800 mb-2 flex items-center gap-2">
                    <i class="fas fa-scale-unbalanced-flip text-purple-600"></i> Detección de Asimetría en Calificaciones
                </h4>
                <div class="space-y-2 text-xs">
                    <?php 
                    $hay_asym = false;
                    foreach($stats_jueces as $sj): 
                        if(!empty($sj['asimetria'])): 
                            $hay_asym = true;
                            $as = $sj['asimetria'];
                    ?>
                        <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between">
                            <span class="font-bold text-slate-800"><?php echo htmlspecialchars($sj['nombre']); ?></span>
                            <span class="text-[10px] font-black uppercase text-purple-700 bg-purple-50 px-2 py-0.5 rounded-md border border-purple-200">
                                Asimetría en <?php echo $as['club']; ?> (Ratio <?php echo $as['ratio']; ?>x)
                            </span>
                        </div>
                    <?php 
                        endif;
                    endforeach; 
                    if(!$hay_asym):
                    ?>
                        <p class="text-xs text-emerald-600 font-bold flex items-center gap-1.5">
                            <i class="fas fa-check-circle"></i> Todos los jueces mantuvieron un criterio simétrico e imparcial por club.
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Bloque 4: Espacio para Observaciones y Firma del Juez Árbitro -->
            <div class="pt-4 border-t border-slate-200 space-y-4">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1">Conclusiones del Juez Árbitro</label>
                    <textarea class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 outline-none focus:border-purple-500" rows="3" placeholder="Añadir notas técnicas del debriefing presencial con el panel de jueces..."></textarea>
                </div>
                <div class="flex items-center justify-between pt-2 text-[10px] text-slate-400">
                    <span>Certificación de Acta de Clinic Arbitral</span>
                    <span class="font-bold">Firma del Juez Árbitro: ______________________</span>
                </div>
            </div>
        </div>

        <!-- Footer Modal -->
        <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
            <button onclick="closeDebriefingModal()" class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100">
                Cerrar
            </button>
            <button onclick="window.print()" class="px-4 py-2 bg-purple-600 text-white rounded-xl text-xs font-bold hover:bg-purple-700 flex items-center gap-2">
                <i class="fas fa-print text-xs"></i> Imprimir Informe Oficial
            </button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function openDebriefingModal() {
    const m = document.getElementById('modalDebriefing');
    if (m) m.classList.remove('hidden');
}

function closeDebriefingModal() {
    const m = document.getElementById('modalDebriefing');
    if (m) m.classList.add('hidden');
}

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
