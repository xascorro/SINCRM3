<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');

/**
 * LÓGICA DE AGREGACIÓN MACRO
 */
$q_ranking = "
    SELECT 
        nombre_entidad as nombre,
        id_juez,
        group_key,
        SUM(total_notas) as total_notas_global,
        AVG(precision_aqua) as precision_media,
        AVG(bias_score) as bias_medio,
        COUNT(DISTINCT id_competicion) as total_eventos,
        SUM(bajas) as total_bajas,
        SUM(altas) as total_altas
    FROM auditoria_jueces_stats
    WHERE entidad_tipo = 'GLOBAL'
    GROUP BY group_key
    HAVING total_notas_global > 0
    ORDER BY precision_media DESC, ABS(AVG(bias_score)) ASC
";
$res_ranking = mysqli_query($connection, $q_ranking);

// Obtener nombres e IDs de competiciones auditadas
$q_comps = "SELECT DISTINCT c.id, c.nombre 
            FROM auditoria_jueces_stats a 
            JOIN competiciones c ON a.id_competicion = c.id";
$res_comps = mysqli_query($connection, $q_comps);
$audited_comps = [];
while($c = mysqli_fetch_assoc($res_comps)) $audited_comps[] = ['id' => $c['id'], 'nombre' => $c['nombre']];

// KPIs Macro
$total_jueces = mysqli_num_rows($res_ranking);
$best_judge = null;
$worst_bias = null;
$total_notas_season = 0;

$stats_array = [];
while ($row = mysqli_fetch_assoc($res_ranking)) {
    $total_notas_season += $row['total_notas_global'];
    
    // LOGICA DE ANONIMIZACIÓN PARA EL ROL JUEZ (id_rol = 4)
    $es_el_juez_logueado = ($_SESSION['id_rol'] == 4 && $row['id_juez'] == $_SESSION['id_juez_v3']);
    $nombre_display = $row['nombre'];
    
    if($_SESSION['id_rol'] == 4 && !$es_el_juez_logueado) {
        $nombre_display = "Juez Anónimo";
        $row['group_key'] = 'ANON'; // Deshabilitar link a perfil si es anónimo
    }
    
    $row['nombre_real'] = $row['nombre']; // Guardar original para lógica interna
    $row['nombre'] = $nombre_display; // Sobreescribir para visualización

    if (!$best_judge || $row['precision_media'] > $best_judge['precision']) {
        $best_judge = ['nombre' => $nombre_display, 'precision' => $row['precision_media']];
    }
    if (!$worst_bias || abs($row['bias_medio']) > abs($worst_bias['bias'])) {
        $worst_bias = ['nombre' => $nombre_display, 'bias' => $row['bias_medio']];
    }
    $stats_array[] = $row;
}

// Hall of Fame (Top 3)
$top3 = array_slice($stats_array, 0, 3);

?>

<main class="flex-1 flex flex-col min-w-0 bg-surface">
    <?php include('includes/topbar.php'); ?>
    <div class="p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend">

        <div class="mb-10 flex flex-col md:flex-row justify-between items-start md:items-end gap-6">
            <div>
                <h1 class="text-5xl font-black text-slate-800 tracking-tighter mb-2 italic text-primary uppercase">BIAS Analizer</h1>
                <p class="text-lg text-slate-500 font-medium italic">Ranking de Excelencia Técnica - Temporada Vigente</p>
            </div>
            
            <!-- Badge de Competiciones Auditadas (CLICKABLES) -->
            <div class="flex flex-wrap gap-2 max-w-md justify-end">
                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block w-full text-right mb-1">Ver Auditoría de Evento:</span>
                <?php foreach($audited_comps as $ac): ?>
                    <a href="analisis_jueces.php?comp_id=<?php echo $ac['id']; ?>" class="px-3 py-1 bg-white border border-slate-200 text-blue-600 hover:bg-blue-50 hover:border-blue-200 transition-all text-[9px] font-black rounded-lg shadow-sm italic uppercase no-underline flex items-center gap-2 group">
                        <i class="fas fa-eye text-[8px] opacity-40 group-hover:opacity-100 transition-opacity"></i>
                        <?php echo $ac['nombre']; ?>
                    </a>
                <?php endforeach; ?>
                <?php if(empty($audited_comps)): ?>
                    <span class="text-[10px] text-red-400 font-bold italic">Ninguna competición grabada todavía</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Hall of Fame -->
        <?php if(count($top3) > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
            <?php 
            $medals = [
                ['color' => 'from-amber-300 to-amber-500', 'text' => 'text-amber-600', 'icon' => 'fa-crown', 'label' => 'Oro'],
                ['color' => 'from-slate-300 to-slate-400', 'text' => 'text-slate-500', 'icon' => 'fa-medal', 'label' => 'Plata'],
                ['color' => 'from-orange-300 to-orange-500', 'text' => 'text-orange-600', 'icon' => 'fa-medal', 'label' => 'Bronce']
            ];
            foreach($top3 as $idx => $top): 
                $m = $medals[$idx];
            ?>
            <div class="relative bg-white p-8 rounded-[3rem] shadow-xl border border-slate-100 flex flex-col items-center text-center group hover:-translate-y-2 transition-all duration-500">
                <div class="absolute -top-6 w-14 h-14 rounded-2xl bg-gradient-to-br <?php echo $m['color']; ?> flex items-center justify-center text-white shadow-lg shadow-black/10">
                    <i class="fas <?php echo $m['icon']; ?> text-2xl"></i>
                </div>
                <div class="mt-6">
                    <p class="text-[10px] font-black uppercase tracking-[0.3em] <?php echo $m['text']; ?> mb-2"><?php echo $m['label']; ?> de la Temporada</p>
                    <h3 class="text-2xl font-black text-slate-800 leading-tight mb-4"><?php echo $top['nombre']; ?></h3>
                    <div class="flex gap-4 items-center justify-center">
                        <div class="text-center">
                            <p class="text-[9px] font-black text-slate-400 uppercase">Precisión</p>
                            <p class="text-lg font-black text-emerald-500"><?php echo number_format($top['precision_media'], 1); ?>%</p>
                        </div>
                        <div class="w-px h-8 bg-slate-100"></div>
                        <div class="text-center">
                            <p class="text-[9px] font-black text-slate-400 uppercase">Bias</p>
                            <p class="text-lg font-black text-blue-500">±<?php echo number_format(abs($top['bias_medio']), 2); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Visualizaciones Macro -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-16">
            <!-- Gráfico 1: Burbujas de Dispersión -->
            <div class="bg-white p-8 rounded-[3rem] shadow-sm border border-slate-200">
                <h3 class="text-lg font-black text-slate-800 mb-2 uppercase tracking-tighter italic flex items-center gap-3">
                    <i class="fas fa-chart-bubble text-blue-600"></i> Mapa de Dispersión Global
                </h3>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-8">Eje X: Bias | Eje Y: Precisión | Tamaño: Inconsistencias</p>
                <div class="relative h-80">
                    <canvas id="bubbleChart"></canvas>
                </div>
            </div>

            <!-- Gráfico 2: Radar de Excelencia (Top 5) -->
            <div class="bg-white p-8 rounded-[3rem] shadow-sm border border-slate-200">
                <h3 class="text-lg font-black text-slate-800 mb-2 uppercase tracking-tighter italic flex items-center gap-3">
                    <i class="fas fa-compass text-emerald-600"></i> Radar de Consistencia (Top 5)
                </h3>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-8">Comparativa de los líderes del ranking</p>
                <div class="relative h-80">
                    <canvas id="radarChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Ranking Table -->
        <div class="bg-white rounded-[3rem] shadow-sm border border-slate-200 overflow-hidden mb-16">
            <div class="p-10 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <h2 class="text-2xl font-black text-slate-800 italic uppercase tracking-tighter flex items-center gap-4">
                    <i class="fas fa-list-ol text-primary"></i> Escalafón de Jueces
                </h2>
                <div class="px-6 py-2 bg-white rounded-2xl shadow-sm border border-slate-200 text-[10px] font-black text-slate-400 uppercase tracking-widest italic">
                    <?php echo $total_jueces; ?> Jueces Auditados
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-10 py-6 text-[11px] font-black uppercase text-slate-400 tracking-widest">Puesto</th>
                            <th class="px-10 py-6 text-[11px] font-black uppercase text-slate-400 tracking-widest">Juez</th>
                            <th class="px-10 py-6 text-[11px] font-black uppercase text-slate-400 tracking-widest text-center">Eventos</th>
                            <th class="px-10 py-6 text-[11px] font-black uppercase text-slate-400 tracking-widest text-center">Precisión Media</th>
                            <th class="px-10 py-6 text-[11px] font-black uppercase text-slate-400 tracking-widest text-center">Bias Medio</th>
                            <th class="px-10 py-6 text-[11px] font-black uppercase text-slate-400 tracking-widest text-center">Estabilidad</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php 
                        $pos = 0;
                        foreach($stats_array as $row): 
                            $pos++;
                            $bg_p = ($row['precision_media'] >= 70) ? 'bg-emerald-50 text-emerald-600' : (($row['precision_media'] >= 50) ? 'bg-amber-50 text-amber-600' : 'bg-red-50 text-red-600');
                            $bg_b = (abs($row['bias_medio']) >= 0.3) ? 'bg-red-50 text-red-600' : 'bg-blue-50 text-blue-600';
                            $inc_total = $row['total_bajas'] + $row['total_altas'];
                            $inc_pct = round(($inc_total / $row['total_notas_global']) * 100, 1);
                        ?>
                        <tr class="hover:bg-slate-50 transition-all duration-300 group">
                            <td class="px-10 py-8">
                                <span class="w-10 h-10 rounded-2xl bg-slate-900 text-white flex items-center justify-center font-black italic text-sm shadow-lg group-hover:scale-110 transition-transform"><?php echo $pos; ?></span>
                            </td>
                            <td class="px-10 py-8">
                                <div class="flex items-center gap-5">
                                    <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 font-black border-2 border-white shadow-md">
                                        <?php echo ($row['group_key'] == 'ANON') ? '?' : strtoupper(substr($row['nombre'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <?php if($row['group_key'] == 'ANON'): ?>
                                            <span class="font-black text-slate-400 uppercase text-sm tracking-tight block italic"><?php echo $row['nombre']; ?></span>
                                        <?php else: ?>
                                            <a href="perfil_juez.php?key=<?php echo $row['group_key']; ?>" class="font-black text-slate-800 uppercase text-sm tracking-tight block hover:text-blue-600 transition-colors no-underline"><?php echo $row['nombre']; ?></a>
                                        <?php endif; ?>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase italic">Participación activa</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-10 py-8 text-center font-black text-slate-800"><?php echo $row['total_eventos']; ?></td>
                            <td class="px-10 py-8 text-center">
                                <span class="px-4 py-2 rounded-xl text-xs font-black uppercase italic <?php echo $bg_p; ?> border border-current opacity-80"><?php echo number_format($row['precision_media'], 1); ?>%</span>
                            </td>
                            <td class="px-10 py-8 text-center">
                                <span class="px-4 py-2 rounded-xl text-xs font-black italic <?php echo $bg_b; ?> border border-current opacity-80">±<?php echo number_format($row['bias_medio'], 3); ?></span>
                            </td>
                            <td class="px-10 py-8 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-full max-w-[100px] h-1.5 bg-slate-100 rounded-full overflow-hidden mb-2">
                                        <div class="h-full bg-blue-500" style="width: <?php echo 100 - $inc_pct; ?>%"></div>
                                    </div>
                                    <span class="text-[10px] font-black text-slate-400 uppercase italic"><?php echo 100 - $inc_pct; ?>% Consistente</span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(count($stats_array) == 0): ?>
                            <tr><td colspan="6" class="p-20 text-center text-slate-400 font-bold italic">No hay datos de auditoría grabados. Ejecuta el proceso desde la vista de competición.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Guía Técnica del Sistema de Auditoría -->
        <div class="space-y-12 bg-white p-10 md:p-16 rounded-[3rem] shadow-sm border border-slate-100 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-blue-600 via-emerald-500 to-amber-400"></div>
            <div class="max-w-4xl">
                <h2 class="text-3xl font-black text-slate-800 tracking-tighter mb-6 uppercase italic">Metodología del BIAS Analizer</h2>
                <p class="text-slate-600 leading-relaxed font-medium mb-10">El sistema de auditoría del software **SINCRM** ha sido diseñado bajo los protocolos técnicos de **World Aquatics (AQUA)** para garantizar la imparcialidad y excelencia técnica del cuerpo arbitral. A continuación se detalla la base científica del ranking:</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                    <div class="space-y-4"><div class="flex items-center gap-3 text-blue-600 mb-2"><i class="fas fa-bullseye-arrow text-xl"></i><h4 class="font-black uppercase tracking-widest text-sm">El Consenso Oficial</h4></div><p class="text-xs text-slate-500 leading-relaxed">Para cada elemento juzgado, el sistema establece un **Estándar de Verdad (Consenso)**. Este se obtiene mediante una media truncada: se eliminan las notas más alta y más baja del panel y se promedian las restantes, redondeando el resultado a la **décima más cercana (0.1)**. Este valor representa la "nota perfecta" acordada por el panel.</p></div>
                    <div class="space-y-4"><div class="flex items-center gap-3 text-emerald-600 mb-2"><i class="fas fa-microscope text-xl"></i><h4 class="font-black uppercase tracking-widest text-sm">Precisión AQUA (%)</h4></div><p class="text-xs text-slate-500 leading-relaxed">Es el principal indicador de calidad. Mide el porcentaje de notas de un juez que caen dentro del **margen de error aceptable de ±0.2 puntos** respecto al consenso. Un porcentaje elevado indica un juez con un criterio técnico altamente sincronizado con los estándares internacionales.</p></div>
                    <div class="space-y-4"><div class="flex items-center gap-3 text-amber-500 mb-2"><i class="fas fa-scale-balanced text-xl"></i><h4 class="font-black uppercase tracking-widest text-sm">Bias Score (Sesgo)</h4></div><p class="text-xs text-slate-500 leading-relaxed">Mide la tendencia de comportamiento del juez. Un valor negativo (ej. -0.35) clasifica al juez como **Severo**, indicando que sus notas son sistemáticamente más bajas que las de sus compañeros. Un valor positivo (ej. +0.40) le clasifica como **Generoso** o con sesgo de favoritismo.</p></div>
                    <div class="space-y-4"><div class="flex items-center gap-3 text-red-500 mb-2"><i class="fas fa-triangle-exclamation text-xl"></i><h4 class="font-black uppercase tracking-widest text-sm">Inconsistencias (B/A)</h4></div><p class="text-xs text-slate-500 leading-relaxed">Contabiliza las veces que un juez se sitúa en los límites del panel (**Baja o Alta**). Nuestro algoritmo es estricto: si tres jueces dan la misma nota mínima, los tres reciben una marca de inconsistencia. Este dato revela a los jueces "Outliers" que tienden a forzar los extremos de la campana de Gauss.</p></div>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const data_raw = <?php echo json_encode($stats_array); ?>;
    
    // 1. Bubble Chart (Dispersión Global)
    const bubbleData = data_raw.map(row => ({
        x: parseFloat(row.bias_medio),
        y: parseFloat(row.precision_media),
        r: (parseInt(row.total_bajas) + parseInt(row.total_altas)) / (parseInt(row.total_notas_global) || 1) * 50,
        label: row.nombre
    }));

    new Chart(document.getElementById('bubbleChart'), {
        type: 'bubble',
        data: { datasets: [{ label: 'Jueces', data: bubbleData, backgroundColor: 'rgba(59, 130, 246, 0.5)', borderColor: '#3b82f6' }] },
        options: {
            responsive: true, maintainAspectRatio: false,
            scales: {
                x: { title: { display: true, text: 'Bias Score (Tendencia)' }, grid: { color: '#f1f5f9' } },
                y: { title: { display: true, text: 'Precisión Media (%)' }, min: 0, max: 100 }
            },
            plugins: { tooltip: { callbacks: { label: (ctx) => ctx.raw.label + ': Prec ' + ctx.raw.y.toFixed(1) + '% | Bias ' + ctx.raw.x.toFixed(3) } } }
        }
    });

    // 2. Radar Chart (Top 5)
    const top5 = data_raw.slice(0, 5);
    new Chart(document.getElementById('radarChart'), {
        type: 'radar',
        data: {
            labels: ['Precisión', 'Neutralidad', 'Consistencia', 'Experiencia', 'Estabilidad'],
            datasets: top5.map((j, i) => ({
                label: j.nombre,
                data: [
                    j.precision_media, 
                    100 - (Math.min(Math.abs(j.bias_medio), 1) * 100), 
                    100 - ((parseInt(j.total_bajas) + parseInt(j.total_altas)) / (parseInt(j.total_notas_global) || 1) * 100),
                    Math.min(j.total_eventos * 20, 100),
                    95 // Estabilidad base
                ],
                fill: true,
                backgroundColor: `rgba(${(i*50)%255}, 130, 246, 0.2)`,
                borderColor: `rgb(${(i*50)%255}, 130, 246)`
            }))
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            scales: { r: { angleLines: { display: true }, suggestMin: 0, suggestMax: 100 } }
        }
    });
});
</script>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>