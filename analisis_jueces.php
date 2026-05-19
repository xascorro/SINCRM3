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

    <div class="p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend">

        <?php include('includes/alertas_v4.php'); ?>

        <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <a href="ranking_jueces.php" class="text-blue-600 font-bold text-xs uppercase tracking-widest mb-2 flex items-center gap-2 hover:gap-3 transition-all no-underline"><i class="fas fa-arrow-left"></i> Volver al Ranking Global</a>
                <h1 class="text-5xl font-black text-slate-800 tracking-tighter mb-2 italic text-primary">Auditoría de Jueces</h1>
                <p class="text-lg text-slate-500 font-medium"><?php echo $comp_data['nombre']; ?> <?php if(!$has_data): ?><span class="text-xs bg-amber-100 text-amber-600 px-2 py-1 rounded-lg ml-2">PROVISIONAL</span><?php endif; ?></p>
            </div>
            <div class="flex items-center gap-3">
                <div class="px-4 py-2 bg-slate-800 text-white rounded-xl shadow-lg flex items-center gap-3 border border-white/10">
                    <i class="fas fa-bolt text-amber-400 text-xs"></i>
                    <div class="leading-none">
                        <p class="text-[8px] font-black uppercase text-slate-400 mb-0.5 tracking-widest">Carga</p>
                        <p class="text-xs font-black italic"><?php echo round(microtime(true) - $start_time, 3); ?> <span class="text-[9px] opacity-50">seg</span></p>
                    </div>
                </div>
                <?php if(!$has_data): ?>
                    <button onclick="saveAudit()" class="btn-primary-v3 flex items-center gap-2 shadow-xl"><i class="fas fa-save"></i> GRABAR AUDITORÍA</button>
                <?php else: ?>
                    <button onclick="saveAudit()" class="btn-outline-v3 flex items-center gap-2 text-slate-400 hover:text-white transition-all"><i class="fas fa-sync"></i> RECALCULAR</button>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-200 border-t-[8px] border-t-emerald-500">
                <div class="flex justify-between items-start mb-4"><div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600"><i class="fas fa-hands-holding-circle text-xl"></i></div><span class="text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] italic">Consenso Global</span></div>
                <h3 class="text-4xl font-black <?php echo $consenso_color; ?> leading-none"><?php echo number_format($consenso_score, 1); ?><span class="text-lg text-slate-400">/10</span></h3>
                <p class="text-xs font-bold text-slate-400 mt-2 uppercase tracking-tighter">Índice de similitud entre jueces</p>
            </div>
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-200 border-t-[8px] border-t-red-500">
                <div class="flex justify-between items-start mb-4"><div class="w-12 h-12 rounded-xl bg-red-50 flex items-center justify-center text-red-600"><i class="fas fa-hand-fist text-xl"></i></div><span class="text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] italic">El Juez de Hierro</span></div>
                <h3 class="text-2xl font-black text-slate-800 leading-tight truncate"><?php echo $juez_hierro['nombre']; ?></h3>
                <p class="text-xs font-bold text-red-500 mt-1 uppercase tracking-tighter"><?php echo $juez_hierro['pct']; ?>% de sus notas son mínimas</p>
            </div>
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-200 border-t-[8px] border-t-blue-500">
                <div class="flex justify-between items-start mb-4"><div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600"><i class="fas fa-hand-holding-heart text-xl"></i></div><span class="text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] italic">El Juez Generoso</span></div>
                <h3 class="text-2xl font-black text-slate-800 leading-tight truncate"><?php echo $juez_generoso['nombre']; ?></h3>
                <p class="text-xs font-bold text-blue-500 mt-1 uppercase tracking-tighter"><?php echo $juez_generoso['pct']; ?>% de sus notas son máximas</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
            <div class="bg-white p-6 md:p-8 rounded-[2.5rem] shadow-sm border border-slate-200">
                <h3 class="text-lg font-black text-slate-800 mb-6 uppercase tracking-tighter italic flex items-center gap-3"><i class="fas fa-bullseye text-blue-600"></i> Calidad: Desviación vs Eliminaciones</h3>
                <div class="relative h-80"><canvas id="scatterChart"></canvas></div>
            </div>
            <div class="bg-white p-6 md:p-8 rounded-[2.5rem] shadow-sm border border-slate-200">
                <h3 class="text-lg font-black text-slate-800 mb-6 uppercase tracking-tighter italic flex items-center gap-3"><i class="fas fa-chart-pie text-emerald-600"></i> Tendencia de Puntuación por Juez</h3>
                <div class="relative h-80"><canvas id="barChart"></canvas></div>
            </div>
        </div>

        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden mb-12">
            <div class="p-8 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h2 class="text-xl font-black text-slate-800 italic uppercase tracking-tighter flex items-center gap-3"><i class="fas fa-chart-bar text-blue-600"></i> Resumen de Desempeño Consolidado</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-8 py-5 text-[11px] font-black uppercase text-slate-400 tracking-widest">Juez</th>
                            <th class="px-8 py-5 text-[11px] font-black uppercase text-slate-400 tracking-widest text-center">Total Notas</th>
                            <th class="px-8 py-5 text-[11px] font-black uppercase text-slate-400 tracking-widest text-center">Precisión AQUA</th>
                            <th class="px-8 py-5 text-[11px] font-black uppercase text-slate-400 tracking-widest text-center">Bias Score</th>
                            <th class="px-8 py-5 text-[11px] font-black uppercase text-slate-400 tracking-widest text-right">Detalle</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach($stats_jueces as $s): 
                            $bg_prec = ($s['precision'] >= 70) ? 'bg-emerald-50 text-emerald-600' : (($s['precision'] >= 50) ? 'bg-amber-50 text-amber-600' : 'bg-red-50 text-red-600');
                            $bg_desv = (abs($s['desviacion']) >= 0.3) ? 'bg-red-50 text-red-600' : 'bg-blue-50 text-blue-600';
                        ?>
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full <?php echo $s['is_orphan'] ? 'bg-red-50 text-red-400' : 'bg-slate-100 text-slate-400'; ?> flex items-center justify-center font-bold group-hover:bg-blue-100 group-hover:text-blue-600 transition-all shadow-sm border border-white"><?php echo strtoupper(substr($s['nombre'], 0, 1)); ?></div>
                                    <div><span class="font-bold text-slate-700 uppercase text-xs tracking-tight block"><?php echo $s['nombre']; ?></span><span class="text-[9px] font-bold text-slate-400 uppercase italic"><?php echo count($s['pjs']); ?> paneles asignados</span></div>
                                </div>
                            </td>
                            <td class="px-8 py-6 text-center font-black text-slate-800"><?php echo $s['total']; ?></td>
                            <td class="px-8 py-6 text-center"><span class="px-3 py-1 rounded-full text-[10px] font-black uppercase italic <?php echo $bg_prec; ?>"><?php echo $s['precision']; ?>%</span></td>
                            <td class="px-8 py-6 text-center"><span class="px-3 py-1 rounded-full text-[10px] font-black italic <?php echo $bg_desv; ?>">±<?php echo number_format($s['desviacion'], 3); ?></span></td>
                            <td class="px-8 py-6 text-right"><a href="analisis_juez_detalle.php?pjs=<?php echo implode(',', $s['pjs']); ?>&comp_id=<?php echo $competicion_id; ?>" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-slate-900 text-white hover:bg-blue-600 transition-all shadow-md"><i class="fas fa-eye text-xs"></i></a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
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

    new Chart(document.getElementById('scatterChart'), { type: 'scatter', data: { datasets: [{ label: 'Jueces', data: labels.map((label, index) => ({ x: Math.abs(desv[index]), y: pct[index], label: label })), backgroundColor: '#3b82f6' }] }, options: { responsive: true, maintainAspectRatio: false, scales: { x: { title: { display: true, text: 'Bias Score (Absoluto)' } }, y: { title: { display: true, text: '% Notas Eliminadas' }, max: 100 } } } });
    new Chart(document.getElementById('barChart'), { type: 'bar', data: { labels: labels, datasets: [{ label: 'Bajas', data: bajas, backgroundColor: '#ef4444' }, { label: 'Válidas', data: validas, backgroundColor: '#cbd5e1' }, { label: 'Altas', data: altas, backgroundColor: '#10b981' }] }, options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y', scales: { x: { stacked: true, max: 100 }, y: { stacked: true } } } });
});
</script>
<?php include('includes/scripts.php'); include('includes/footer.php'); ?>