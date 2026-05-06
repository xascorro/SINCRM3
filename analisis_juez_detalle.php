<?php
$start_time = microtime(true);
include('security.php');
include('includes/header.php');
include('includes/navbar.php');

$pjs_str = $_GET['pjs'] ?? '';
$competicion_id = $_GET['comp_id'] ?? $_SESSION['id_competicion_usuario'] ?? 0;

if (!$pjs_str || !$competicion_id) {
    header('Location: analisis_jueces.php');
    exit();
}

$pjs_array = explode(',', $pjs_str);
$pjs_clean = implode(',', array_map('intval', $pjs_array));

// 1. Verificar si existen datos grabados
$q_check = "SELECT * FROM auditoria_jueces_stats WHERE id_competicion = $competicion_id AND pjs_asociados = '".mysqli_real_escape_string($connection, $pjs_str)."' AND entidad_tipo = 'GLOBAL' LIMIT 1";
$res_check = mysqli_query($connection, $q_check);
$saved_global = mysqli_fetch_assoc($res_check);
$has_data = (bool)$saved_global;

// Intentar obtener metadatos del juez
$q_meta = "SELECT j.* FROM panel_jueces p JOIN jueces j ON p.id_juez = j.id WHERE p.id IN ($pjs_clean) LIMIT 1";
$res_meta = mysqli_query($connection, $q_meta);
$juez = mysqli_fetch_assoc($res_meta);
$nombre_completo = ($juez) ? ($juez['nombre'] . ' ' . $juez['apellidos']) : "Juez Desconocido (ID $pjs_clean)";
if (in_array(108, $pjs_array)) $nombre_completo = "Juez MEDIA (Automático)";

write_log("Consulta de Auditoría BIAS (Detalle Juez) para: $nombre_completo", "INFO");

/**
 * CARGA DE DATOS (Persistente vs Live)
 */
$tree = []; $club_bias = []; $severity_sum = 0; $severity_count = 0; $penalization_points = 0; $precision_count = 0;

$query_base = "
    (SELECT 
        pj.id as id_puntuacion, pj.nota, 'Figura' as tipo, cat.nombre as categoria_nombre,
        COALESCE(CONCAT(cat.nombre, ' - ', modal.nombre), 'Fase Figuras') as fase_nombre,
        COALESCE(
            (SELECT texto FROM hibridos_rutina hr WHERE hr.id_rutina = i.id AND hr.elemento = pj.id_elemento AND hr.tipo = 'dd' AND hr.texto != '' LIMIT 1),
            (SELECT texto FROM hibridos_rutina hr WHERE hr.id_rutina = i.id AND hr.elemento = pj.id_elemento AND hr.tipo = 'basemark' AND hr.texto != '' LIMIT 1),
            CONCAT(fig.numero, ' ', fig.nombre),
            CONCAT('Figura ', pj.id_elemento)
        ) as elemento_nombre,
        CONCAT(nad.apellidos, ', ', nad.nombre) as nadadora_nombre, cl.nombre_corto as club_nombre,
        pj.id_inscripcion_figuras, pj.id_elemento, pj.id_rutina, pj.tipo_ia
    FROM puntuaciones_jueces pj
    JOIN inscripciones_figuras i ON pj.id_inscripcion_figuras = i.id
    JOIN nadadoras nad ON i.id_nadadora = nad.id
    LEFT JOIN clubes cl ON nad.club = cl.id
    LEFT JOIN fases f ON i.id_fase = f.id
    LEFT JOIN categorias cat ON f.id_categoria = cat.id
    LEFT JOIN modalidades modal ON f.id_modalidad = modal.id
    LEFT JOIN figuras fig ON (CASE WHEN pj.id_elemento > 0 AND f.elementos_coach_card = 0 THEN pj.id_elemento ELSE f.id_figura END) = fig.id
    WHERE pj.id_panel_juez IN ($pjs_clean) AND i.id_competicion = '$competicion_id' AND pj.id_inscripcion_figuras IS NOT NULL)
    
    UNION ALL

    (SELECT 
        pj.id as id_puntuacion, pj.nota, 'Rutina' as tipo, cat.nombre as categoria_nombre,
        COALESCE(CONCAT(cat.nombre, ' - ', modal.nombre), 'Fase Rutina') as fase_nombre,
        COALESCE(
            (SELECT texto FROM hibridos_rutina hr WHERE hr.id_rutina = pj.id_rutina AND hr.elemento = pj.id_elemento AND hr.tipo = 'dd' AND hr.texto != '' LIMIT 1),
            (SELECT texto FROM hibridos_rutina hr WHERE hr.id_rutina = pj.id_rutina AND hr.elemento = pj.id_elemento AND hr.tipo = 'basemark' AND hr.texto != '' LIMIT 1),
            pj.tipo_ia
        ) as elemento_nombre,
        COALESCE((SELECT GROUP_CONCAT(CONCAT(n.apellidos, ', ', n.nombre) SEPARATOR ' / ') FROM rutinas_participantes rp JOIN nadadoras n ON rp.id_nadadora = n.id WHERE rp.id_rutina = pj.id_rutina), 'Equipo/Dúo') as nadadora_nombre,
        cl.nombre_corto as club_nombre,
        pj.id_inscripcion_figuras, pj.id_elemento, pj.id_rutina, pj.tipo_ia
    FROM puntuaciones_jueces pj
    JOIN rutinas r ON pj.id_rutina = r.id
    LEFT JOIN clubes cl ON r.id_club = cl.id
    LEFT JOIN fases f ON r.id_fase = f.id
    LEFT JOIN categorias cat ON f.id_categoria = cat.id
    LEFT JOIN modalidades modal ON f.id_modalidad = modal.id
    WHERE pj.id_panel_juez IN ($pjs_clean) AND r.id_competicion = '$competicion_id' AND pj.id_rutina IS NOT NULL)
    
    ORDER BY categoria_nombre, nadadora_nombre, elemento_nombre
";

$res_meta_notes = mysqli_query($connection, $query_base);

while ($n = mysqli_fetch_assoc($res_meta_notes)) {
    $cat = $n['categoria_nombre'] ?: 'Sin Categoría';
    $swimmer = $n['nadadora_nombre'] . ' [' . $n['club_nombre'] . ']';
    $club = $n['club_nombre'] ?: 'S/C';
    $id_p = $n['id_puntuacion'];

    if ($has_data) {
        $q_p = "SELECT * FROM auditoria_jueces_puntos WHERE id_puntuacion = $id_p LIMIT 1";
        $saved_p = mysqli_fetch_assoc(mysqli_query($connection, $q_p));
        $consenso = (float)($saved_p['valor_consenso'] ?? 0);
        $bias = (float)($saved_p['bias_score'] ?? 0);
        $estado_int = (int)($saved_p['estado'] ?? 0);
        $n['media_consenso'] = $consenso; $n['bias_score'] = $bias;
        $n['min_fila'] = ($estado_int == 1) ? $n['nota'] : 0;
        $n['max_fila'] = ($estado_int == 2) ? $n['nota'] : 10;
    } else {
        $q_live = "SELECT 
            (SELECT MIN(pj2.nota) FROM puntuaciones_jueces pj2 WHERE pj2.id_inscripcion_figuras = '{$n['id_inscripcion_figuras']}' AND pj2.id_elemento = '{$n['id_elemento']}' AND '{$n['id_inscripcion_figuras']}' > 0) as min_live_f,
            (SELECT MAX(pj2.nota) FROM puntuaciones_jueces pj2 WHERE pj2.id_inscripcion_figuras = '{$n['id_inscripcion_figuras']}' AND pj2.id_elemento = '{$n['id_elemento']}' AND '{$n['id_inscripcion_figuras']}' > 0) as max_live_f,
            (SELECT MIN(pj2.nota) FROM puntuaciones_jueces pj2 WHERE pj2.id_rutina = '{$n['id_rutina']}' AND pj2.id_elemento = '{$n['id_elemento']}' AND COALESCE(pj2.tipo_ia,'') = COALESCE('{$n['tipo_ia']}','') AND '{$n['id_rutina']}' > 0) as min_live_r,
            (SELECT MAX(pj2.nota) FROM puntuaciones_jueces pj2 WHERE pj2.id_rutina = '{$n['id_rutina']}' AND pj2.id_elemento = '{$n['id_elemento']}' AND COALESCE(pj2.tipo_ia,'') = COALESCE('{$n['tipo_ia']}','') AND '{$n['id_rutina']}' > 0) as max_live_r,
            (SELECT ROUND(AVG(pj3.nota), 1) FROM puntuaciones_jueces pj3 WHERE (('{$n['id_inscripcion_figuras']}' > 0 AND pj3.id_inscripcion_figuras = '{$n['id_inscripcion_figuras']}' AND pj3.id_elemento = '{$n['id_elemento']}') OR ('{$n['id_rutina']}' > 0 AND pj3.id_rutina = '{$n['id_rutina']}' AND pj3.id_elemento = '{$n['id_elemento']}' AND COALESCE(pj3.tipo_ia,'') = COALESCE('{$n['tipo_ia']}',''))) AND COALESCE(pj3.nota_menor, '') != 'si' AND COALESCE(pj3.nota_mayor, '') != 'si') as consensus_live";
        $res_l = mysqli_fetch_assoc(mysqli_query($connection, $q_live));
        $consenso = (float)($res_l['consensus_live'] ?? 0);
        $bias = (float)$n['nota'] - $consenso;
        $n['media_consenso'] = $consenso; $n['bias_score'] = $bias;
        $n['min_fila'] = $res_l['min_live_f'] ?? $res_l['min_live_r'];
        $n['max_fila'] = $res_l['max_live_f'] ?? $res_l['max_live_r'];
    }

    if (!isset($tree[$cat])) $tree[$cat] = ['total_n' => 0, 'sum_n' => 0, 'bajas' => 0, 'altas' => 0, 'd_sum' => 0, 'd_count' => 0, 'swimmers' => []];
    if (!isset($tree[$cat]['swimmers'][$swimmer])) $tree[$cat]['swimmers'][$swimmer] = ['nombre' => $n['nadadora_nombre'], 'club' => $club, 'total_n' => 0, 'sum_n' => 0, 'bajas' => 0, 'altas' => 0, 'd_sum' => 0, 'd_count' => 0, 'items' => []];

    $nota = (float)$n['nota']; $min = (float)$n['min_fila']; $max = (float)$n['max_fila'];
    if (abs($bias) <= 0.2) $precision_count++;
    if (abs($bias) >= 0.5) $penalization_points++;
    $severity_sum += $bias; $severity_count++;

    if (!isset($club_bias[$club])) $club_bias[$club] = ['sum' => 0, 'count' => 0, 'bajas' => 0, 'altas' => 0];
    $club_bias[$club]['sum'] += $bias; $club_bias[$club]['count']++;
    if ($nota <= $min && $min > 0) { $club_bias[$club]['bajas']++; $tree[$cat]['swimmers'][$swimmer]['bajas']++; $tree[$cat]['bajas']++; }
    elseif ($nota >= $max && $max < 10) { $club_bias[$club]['altas']++; $tree[$cat]['swimmers'][$swimmer]['altas']++; $tree[$cat]['altas']++; }

    $tree[$cat]['swimmers'][$swimmer]['total_n']++; $tree[$cat]['swimmers'][$swimmer]['sum_n'] += $nota;
    $tree[$cat]['swimmers'][$swimmer]['d_sum'] += abs($bias); $tree[$cat]['swimmers'][$swimmer]['d_count']++;
    $tree[$cat]['swimmers'][$swimmer]['items'][] = $n;
    $tree[$cat]['total_n']++; $tree[$cat]['sum_n'] += $nota; $tree[$cat]['d_sum'] += abs($bias); $tree[$cat]['d_count']++;
}

$major_bias = null;
foreach ($club_bias as $c => $data) {
    $avg = $data['sum'] / $data['count'];
    if (abs($avg) >= 0.4) { if (!$major_bias || abs($avg) > abs($major_bias['avg'])) $major_bias = ['club' => $c, 'avg' => $avg]; }
}
$total_severity_avg = ($severity_count > 0) ? ($severity_sum / $severity_count) : 0;
$precision_pct = ($severity_count > 0) ? round(($precision_count / $severity_count) * 100, 1) : 0;
$severity_label = "Equilibrado"; $severity_class = "text-slate-500 bg-slate-100";
if ($total_severity_avg <= -0.3) { $severity_label = "Severo"; $severity_class = "text-red-600 bg-red-100"; }
elseif ($total_severity_avg >= 0.3) { $severity_label = "Generoso"; $severity_class = "text-emerald-600 bg-emerald-100"; }
?>

<main class="flex-1 flex flex-col min-w-0 bg-surface">
    <?php include('includes/topbar.php'); ?>
    <div class="p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend">
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <a href="ranking_jueces.php" class="text-blue-600 font-bold text-xs uppercase tracking-widest mb-2 flex items-center gap-2 hover:gap-3 transition-all no-underline"><i class="fas fa-arrow-left"></i> Volver al ranking</a>
                <h1 class="text-4xl font-black text-slate-800 tracking-tighter mb-2 italic uppercase"><?php echo $nombre_completo; ?></h1>
                <div class="flex items-center gap-3">
                    <div class="px-4 py-2 bg-slate-800 text-white rounded-xl shadow-lg flex items-center gap-3 border border-white/10"><i class="fas fa-bolt text-amber-400 text-xs"></i><div class="leading-none"><p class="text-[8px] font-black uppercase text-slate-400 mb-0.5 tracking-widest">Carga</p><p class="text-xs font-black italic"><?php echo round(microtime(true) - $start_time, 3); ?> <span class="text-[9px] opacity-50">seg</span></p></div></div>
                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase italic <?php echo $severity_class; ?>">Juez <?php echo $severity_label; ?></span>
                    <?php if($penalization_points > 5): ?><span class="px-3 py-1 bg-amber-500 text-white rounded-full text-[10px] font-black uppercase italic">Alta Inconsistencia</span><?php endif; ?>
                    <?php if(!$has_data): ?><span class="text-[9px] bg-amber-100 text-amber-600 px-2 py-1 rounded-lg font-black uppercase">Modo Provisional</span><?php endif; ?>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-200"><p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Bias Score Total</p><h3 class="text-3xl font-black <?php echo ($total_severity_avg >= 0) ? 'text-emerald-500' : 'text-red-500'; ?>"><?php echo ($total_severity_avg >= 0 ? '+' : '') . number_format($total_severity_avg, 3); ?></h3><p class="text-[10px] text-slate-400 mt-2 font-bold italic">Severidad media</p></div>
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-200"><p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Precisión AQUA</p><h3 class="text-3xl font-black <?php echo ($precision_pct >= 70 ? 'text-emerald-500' : ($precision_pct >= 50 ? 'text-amber-500' : 'text-red-600')); ?>"><?php echo $precision_pct; ?>%</h3><p class="text-[10px] text-slate-400 mt-2 font-bold italic">Notas en margen ≤ 0.2</p></div>
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-200"><p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Errores Críticos</p><h3 class="text-3xl font-black <?php echo ($penalization_points > 5 ? 'text-red-600' : 'text-slate-800'); ?>"><?php echo $penalization_points; ?></h3><p class="text-[10px] text-slate-400 mt-2 font-bold italic">Desviaciones ≥ 0.5</p></div>
            <div class="bg-slate-100 p-6 rounded-[2rem] border border-slate-200 relative overflow-hidden"><p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Auditoría de Sesgo</p><?php if($major_bias): ?><div class="flex items-center gap-3"><div class="w-8 h-8 rounded-xl bg-amber-200 flex items-center justify-center text-amber-700 shadow-sm"><i class="fas fa-triangle-exclamation text-xs"></i></div><div><h4 class="font-black text-[11px] text-slate-800 uppercase leading-tight truncate max-w-[100px]"><?php echo $major_bias['club']; ?></h4><p class="text-[9px] font-bold text-amber-700">Sesgo: <?php echo ($major_bias['avg'] > 0 ? '+' : '') . number_format($major_bias['avg'], 3); ?></p></div></div><?php else: ?><div class="flex items-center gap-3"><div class="w-8 h-8 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600 shadow-sm"><i class="fas fa-check-circle text-xs"></i></div><h4 class="font-black text-xs text-slate-600 uppercase">Neutral</h4></div><?php endif; ?></div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
            <div class="bg-white p-6 md:p-8 rounded-[2.5rem] shadow-sm border border-slate-200"><h3 class="text-lg font-black text-slate-800 mb-2 uppercase tracking-tighter italic flex items-center gap-3"><i class="fas fa-chart-line text-blue-600"></i> Evolución de Desviación</h3><p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6">Tendencia vs Consenso Oficial (0 = Sin Desviación)</p><div class="relative h-64"><canvas id="evoChart"></canvas></div></div>
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden"><div class="p-6 border-b border-slate-50 bg-slate-50/50"><h3 class="text-sm font-black text-slate-800 uppercase tracking-widest italic flex items-center gap-2"><i class="fas fa-building-flag text-blue-600"></i> Desglose por Entidad</h3></div><div class="overflow-x-auto max-h-[300px] overflow-y-auto no-scrollbar"><table class="w-full text-left border-collapse"><thead><tr class="bg-slate-50 sticky top-0 z-10 shadow-sm"><th class="px-6 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest">Club</th><th class="px-6 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Notas</th><th class="px-6 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">B/A</th><th class="px-6 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Bias Score</th><th class="px-6 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Trend</th></tr></thead><tbody class="divide-y divide-slate-50"><?php arsort($club_bias); foreach($club_bias as $c_name => $c_data): $c_avg = $c_data['sum'] / $c_data['count']; $c_color = ($c_avg > 0.1) ? 'text-emerald-500' : (($c_avg < -0.1) ? 'text-red-500' : 'text-slate-400'); $c_icon = ($c_avg > 0.1) ? 'fa-arrow-trend-up' : (($c_avg < -0.1) ? 'fa-arrow-trend-down' : 'fa-minus');?><tr class="hover:bg-slate-50/50 transition-colors"><td class="px-6 py-3 font-black text-slate-700 text-[10px] uppercase"><?php echo $c_name; ?></td><td class="px-6 py-3 text-center text-[10px] font-bold text-slate-400"><?php echo $c_data['count']; ?></td><td class="px-6 py-3 text-center"><span class="text-[9px] font-black"><span class="text-red-400"><?php echo $c_data['bajas']; ?></span><span class="text-slate-200 mx-0.5">/</span><span class="text-emerald-400"><?php echo $c_data['altas']; ?></span></span></td><td class="px-6 py-3 text-center font-black <?php echo $c_color; ?> text-[10px]"><?php echo ($c_avg >= 0 ? '+' : '') . number_format($c_avg, 3); ?></td><td class="px-6 py-3 text-right"><i class="fas <?php echo $c_icon; ?> <?php echo $c_color; ?> text-[10px]"></i></td></tr><?php endforeach; ?></tbody></table></div></div>
        </div>

        <div class="space-y-16">
            <?php if(count($tree) > 0): $group_counter = 0; foreach($tree as $cat_nombre => $cat_data): $cat_avg_d = ($cat_data['d_count'] > 0) ? round($cat_data['d_sum'] / $cat_data['d_count'], 3) : 0; $cat_color_d = ($cat_avg_d > 0.5) ? 'text-red-500' : (($cat_avg_d > 0.25) ? 'text-amber-500' : 'text-emerald-500');?>
                <section class="space-y-8">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-4 border-b-4 border-slate-900 pb-4"><div class="flex items-center gap-4"><div class="w-12 h-12 rounded-xl bg-slate-900 text-white flex items-center justify-center text-xl shadow-lg"><i class="fas fa-layer-group"></i></div><h2 class="text-3xl font-black text-slate-800 uppercase tracking-tighter italic"><?php echo $cat_nombre; ?></h2></div><div class="flex items-center gap-8 bg-white px-6 py-3 rounded-2xl shadow-sm border border-slate-100"><div class="text-center"><p class="text-[9px] font-black text-slate-400 uppercase mb-1">Notas</p><p class="font-black text-slate-800 text-sm"><?php echo $cat_data['total_n']; ?></p></div><div class="text-center border-l border-slate-100 pl-8"><p class="text-[9px] font-black text-slate-400 uppercase mb-1">Desv. Cat.</p><p class="font-black text-sm <?php echo $cat_color_d; ?>">±<?php echo number_format($cat_avg_d, 3); ?></p></div></div></div>
                    <div class="space-y-4">
                        <?php foreach($cat_data['swimmers'] as $s_name => $s_data): $group_counter++; $s_avg_n = round($s_data['sum_n'] / $s_data['total_n'], 2); $s_avg_d = ($s_data['d_count'] > 0) ? round($s_data['d_sum'] / $s_data['d_count'], 3) : 0; $s_color_d = ($s_avg_d > 0.5) ? 'text-red-500' : (($s_avg_d > 0.25) ? 'text-amber-500' : 'text-emerald-500');?>
                            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden group transition-all hover:shadow-md">
                                <div class="p-6 flex flex-col md:flex-row items-center justify-between gap-6 cursor-pointer select-none" onclick="toggleGroup('node_<?php echo $group_counter; ?>')">
                                    <div class="flex items-center gap-6 flex-1 min-w-0"><div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 shadow-sm border border-blue-100 group-hover:scale-110 transition-transform"><i class="fas fa-person-swimming text-lg"></i></div><div class="flex flex-col min-w-0"><span class="font-black text-slate-800 text-base uppercase tracking-tighter leading-tight truncate"><?php echo $s_data['nombre']; ?></span><span class="text-[10px] font-bold text-blue-600 uppercase tracking-widest"><?php echo $s_data['club']; ?></span></div></div>
                                    <div class="flex items-center gap-10 text-center whitespace-nowrap"><div><p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Media</p><p class="font-black text-slate-800 text-sm"><?php echo number_format($s_avg_n, 2); ?></p></div><div><p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Desv. AQUA</p><p class="font-black text-sm <?php echo $s_color_d; ?>">±<?php echo number_format($s_avg_d, 3); ?></p></div><div class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-slate-100 transition-colors"><i class="fas fa-chevron-down transition-transform duration-300" id="icon_node_<?php echo $group_counter; ?>"></i></div></div>
                                </div>
                                <div id="node_<?php echo $group_counter; ?>" class="hidden border-t border-slate-100 bg-slate-50/30 p-4 md:p-8">
                                    <table class="w-full text-left border-collapse bg-white rounded-3xl overflow-hidden shadow-inner border border-slate-100">
                                        <thead><tr class="bg-slate-900 text-white"><th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest">Fase / Elemento</th><th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-center">Nota Juez</th><th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-center">Consenso AQUA</th><th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-center">Bias Score</th><th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-center">Estado</th></tr></thead>
                                        <tbody class="divide-y divide-slate-100">
                                            <?php foreach($s_data['items'] as $item): $bs = $item['bias_score'] ?? 0; $c_bs = (abs($bs) > 0.5) ? 'text-red-500' : ((abs($bs) > 0.25) ? 'text-amber-500' : 'text-emerald-500'); $n_val = (float)$item['nota']; $m_val = (float)$item['min_fila']; $x_val = (float)$item['max_fila'];?>
                                            <tr class="hover:bg-slate-50 transition-colors"><td class="px-6 py-4"><div class="flex flex-col"><span class="text-[9px] font-black text-slate-400 uppercase tracking-tighter"><?php echo $item['fase_nombre']; ?></span><span class="font-bold text-slate-700 text-xs"><?php echo $item['elemento_nombre']; ?></span></div></td><td class="px-6 py-4 text-center font-black text-base <?php echo ($n_val <= $m_val || $n_val >= $x_val) ? 'text-slate-300' : 'text-slate-800'; ?>"><?php echo number_format($n_val, 2); ?></td><td class="px-6 py-4 text-center font-bold text-slate-400 text-xs"><?php echo ($item['media_consenso'] !== null) ? number_format($item['media_consenso'], 2) : '-'; ?></td><td class="px-6 py-4 text-center font-black text-xs <?php echo $c_bs; ?>"><?php echo ($item['media_consenso'] !== null) ? ($bs >= 0 ? '+' : '') . number_format($bs, 2) : '-'; ?></td><td class="px-6 py-4 text-center"><?php if($n_val <= $m_val && $m_val > 0): ?><span class="px-2 py-0.5 bg-red-100 text-red-600 text-[8px] font-black rounded uppercase italic border border-red-200">Baja</span><?php elseif($n_val >= $x_val && $x_val < 10): ?><span class="px-2 py-0.5 bg-emerald-100 text-emerald-600 text-[8px] font-black rounded uppercase italic border border-emerald-200">Alta</span><?php else: ?><span class="px-2 py-0.5 bg-blue-50 text-blue-400 text-[8px] font-black rounded uppercase italic">Válida</span><?php endif; ?></td></tr><?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
            <?php else: ?>
                <div class="p-20 text-center bg-white rounded-[3rem] border border-dashed border-slate-300"><i class="fas fa-search text-slate-200 text-6xl mb-6"></i><p class="text-slate-400 font-bold italic">No se han encontrado puntuaciones.</p></div>
            <?php endif; ?>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function toggleGroup(id) { const el = document.getElementById(id); const icon = document.getElementById('icon_' + id); if (el.classList.contains('hidden')) { el.classList.remove('hidden'); icon.style.transform = 'rotate(180deg)'; } else { el.classList.add('hidden'); icon.style.transform = 'rotate(0deg)'; } }
document.addEventListener("DOMContentLoaded", function() {
    <?php $evo_labels = []; $evo_data = []; $evo_colors = []; foreach ($tree as $cat_nombre => $cat_data) { foreach ($cat_data['swimmers'] as $sw_name => $sw_data) { foreach ($sw_data['items'] as $n) { $d = $n['bias_score'] ?? 0; $evo_labels[] = $n['nadadora_nombre'] . " (" . $n['elemento_nombre'] . ")"; $evo_data[] = round($d, 3); $evo_colors[] = ($d > 0.5) ? '#ef4444' : (($d < -0.5) ? '#ef4444' : '#3b82f6'); } } } ?>
    const labels = <?php echo json_encode($evo_labels); ?>; const data = <?php echo json_encode($evo_data); ?>; const colors = <?php echo json_encode($evo_colors); ?>; const ctx = document.getElementById('evoChart');
    if (ctx && data.length > 0) { new Chart(ctx, { type: 'line', data: { labels: labels, datasets: [{ label: 'Bias Score', data: data, borderColor: '#3b82f6', borderWidth: 2, pointBackgroundColor: colors, pointRadius: 4, pointHoverRadius: 6, fill: false, tension: 0.1 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { tooltip: { callbacks: { label: function(ctx) { return 'Bias: ' + (ctx.raw > 0 ? '+' : '') + ctx.raw; } } }, legend: { display: false } }, scales: { x: { display: false }, y: { title: { display: true, text: 'Bias Score (± Puntos)' }, grid: { color: function(ctx) { return (ctx.tick.value === 0) ? '#0f172a' : '#f1f5f9'; }, lineWidth: function(ctx) { return (ctx.tick.value === 0) ? 2 : 1; } } } } } }); }
});
</script>
<?php include('includes/scripts.php'); include('includes/footer.php'); ?>