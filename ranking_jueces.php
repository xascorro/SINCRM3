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
    
    <div class="px-3 py-4 sm:p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend">

        <!-- Header de Sección -->
        <div class="mb-6 sm:mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4 sm:gap-6">
            <div>
                <h1 class="text-2xl sm:text-4xl font-black text-slate-800 tracking-tighter mb-1 sm:mb-2 flex items-center gap-2.5 sm:gap-3">
                    <span class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 flex items-center justify-center text-primary shadow-sm border border-slate-200">
                        <i class="fas fa-ranking-star text-base sm:text-lg"></i>
                    </span>
                    BIAS Analizer
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 font-medium">Ranking de excelencia técnica y precisión AQUA — Temporada Vigente.</p>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-2.5 sm:gap-4 items-stretch sm:items-center">
                <!-- Buscador Dinámico -->
                <div class="relative w-full sm:w-72">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </span>
                    <input type="text" id="rankingSearchInput" placeholder="Buscar juez en el ranking..." 
                           class="w-full pl-9 pr-3.5 py-2.5 sm:py-3 bg-white border border-slate-200 rounded-xl sm:rounded-2xl text-xs font-bold text-slate-700 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all outline-none shadow-sm"
                           onkeyup="filterRankingList()">
                </div>
            </div>
        </div>

        <!-- Barra de Eventos Auditados -->
        <div class="bg-white p-3.5 sm:p-4 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200 mb-6 sm:mb-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2 shrink-0">
                <i class="fas fa-calendar-check text-blue-500"></i> Auditorías de Evento:
            </span>
            <div class="flex flex-wrap gap-2">
                <?php foreach($audited_comps as $ac): ?>
                    <a href="analisis_jueces.php?comp_id=<?php echo $ac['id']; ?>" class="px-3 py-1.5 bg-slate-50 border border-slate-200 text-slate-700 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 active:scale-[0.98] transition-all text-[10px] font-black rounded-xl shadow-sm uppercase no-underline flex items-center gap-1.5 group">
                        <i class="fas fa-eye text-[9px] opacity-40 group-hover:opacity-100 transition-opacity"></i>
                        <?php echo $ac['nombre']; ?>
                    </a>
                <?php endforeach; ?>
                <?php if(empty($audited_comps)): ?>
                    <span class="text-[10px] text-red-400 font-bold italic">Ninguna competición grabada todavía</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Hall of Fame (Top 3) -->
        <?php if(count($top3) > 0): ?>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 sm:gap-8 mb-8 sm:mb-14">
            <?php 
            $medals = [
                ['color' => 'from-amber-300 to-amber-500', 'text' => 'text-amber-600', 'border' => 'border-amber-200', 'icon' => 'fa-crown', 'label' => 'Oro'],
                ['color' => 'from-slate-300 to-slate-400', 'text' => 'text-slate-500', 'border' => 'border-slate-200', 'icon' => 'fa-medal', 'label' => 'Plata'],
                ['color' => 'from-orange-300 to-orange-500', 'text' => 'text-orange-600', 'border' => 'border-orange-200', 'icon' => 'fa-medal', 'label' => 'Bronce']
            ];
            foreach($top3 as $idx => $top): 
                $m = $medals[$idx];
            ?>
            <div class="relative bg-white p-5 sm:p-7 rounded-2xl sm:rounded-3xl shadow-sm border <?php echo $m['border']; ?> flex flex-col items-center text-center group hover:-translate-y-1 hover:shadow-md transition-all duration-300">
                <div class="absolute -top-5 w-11 h-11 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-gradient-to-br <?php echo $m['color']; ?> flex items-center justify-center text-white shadow-md shadow-black/10">
                    <i class="fas <?php echo $m['icon']; ?> text-lg sm:text-xl"></i>
                </div>
                <div class="mt-4 sm:mt-5 w-full">
                    <p class="text-[9px] font-black uppercase tracking-[0.2em] <?php echo $m['text']; ?> mb-1"><?php echo $m['label']; ?> de la Temporada</p>
                    <h3 class="text-base sm:text-lg font-black text-slate-800 leading-tight mb-3 truncate">
                        <?php if($top['group_key'] == 'ANON'): ?>
                            <?php echo $top['nombre']; ?>
                        <?php else: ?>
                            <a href="perfil_juez.php?key=<?php echo $top['group_key']; ?>" class="text-slate-800 hover:text-blue-600 transition-colors no-underline">
                                <?php echo $top['nombre']; ?>
                            </a>
                        <?php endif; ?>
                    </h3>
                    <div class="flex gap-4 items-center justify-center bg-slate-50 py-2 sm:py-2.5 px-4 rounded-xl sm:rounded-2xl border border-slate-100">
                        <div class="text-center">
                            <p class="text-[8px] sm:text-[9px] font-black text-slate-400 uppercase tracking-widest">Precisión</p>
                            <p class="text-sm sm:text-base font-black text-emerald-600 tabular-nums"><?php echo number_format($top['precision_media'], 1); ?>%</p>
                        </div>
                        <div class="w-px h-6 bg-slate-200"></div>
                        <div class="text-center">
                            <p class="text-[8px] sm:text-[9px] font-black text-slate-400 uppercase tracking-widest">Bias</p>
                            <p class="text-sm sm:text-base font-black text-blue-600 tabular-nums">±<?php echo number_format(abs($top['bias_medio']), 2); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Visualizaciones Macro -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-8 mb-8 sm:mb-14">
            <!-- Gráfico 1: Burbujas de Dispersión -->
            <div class="bg-white p-4 sm:p-7 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200">
                <h3 class="text-sm sm:text-base font-black text-slate-800 mb-1 uppercase tracking-tight flex items-center gap-2">
                    <i class="fas fa-chart-bubble text-blue-600"></i> Mapa de Dispersión Global
                </h3>
                <p class="text-[9px] sm:text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 sm:mb-6">Eje X: Bias | Eje Y: Precisión | Tamaño: Inconsistencias</p>
                <div class="relative h-64 sm:h-80">
                    <canvas id="bubbleChart"></canvas>
                </div>
            </div>

            <!-- Gráfico 2: Radar de Excelencia (Top 5) -->
            <div class="bg-white p-4 sm:p-7 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200">
                <h3 class="text-sm sm:text-base font-black text-slate-800 mb-1 uppercase tracking-tight flex items-center gap-2">
                    <i class="fas fa-compass text-emerald-600"></i> Radar de Consistencia (Top 5)
                </h3>
                <p class="text-[9px] sm:text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 sm:mb-6">Comparativa de los líderes del escalafón</p>
                <div class="relative h-64 sm:h-80">
                    <canvas id="radarChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Ranking Table / Mobile Cards -->
        <div class="bg-white rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200 overflow-hidden mb-8 sm:mb-14">
            <div class="px-4 sm:px-8 py-3.5 sm:py-6 bg-slate-50/50 border-b border-slate-100 flex justify-between items-center">
                <h2 class="text-sm sm:text-base font-black text-slate-800 uppercase tracking-tight flex items-center gap-2 sm:gap-3">
                    <i class="fas fa-list-ol text-primary"></i> Escalafón de Jueces
                </h2>
                <div class="px-3 py-1 bg-white rounded-xl shadow-sm border border-slate-200 text-[10px] font-black text-slate-500 uppercase tracking-widest tabular-nums">
                    <?php echo $total_jueces; ?> Jueces
                </div>
            </div>

            <!-- Vista Móvil: Cards Fluidas (md:hidden) -->
            <div class="md:hidden divide-y divide-slate-100" id="rankingMobileContainer">
                <?php if (empty($stats_array)): ?>
                    <div class="p-8 text-center text-slate-400 text-xs font-bold uppercase tracking-widest">No hay datos de auditoría grabados</div>
                <?php else: ?>
                    <?php 
                    $pos_m = 0;
                    foreach($stats_array as $row): 
                        $pos_m++;
                        $bg_p = ($row['precision_media'] >= 70) ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : (($row['precision_media'] >= 50) ? 'bg-amber-50 text-amber-600 border-amber-100' : 'bg-red-50 text-red-600 border-red-100');
                        $bg_b = (abs($row['bias_medio']) >= 0.3) ? 'bg-red-50 text-red-600 border-red-100' : 'bg-blue-50 text-blue-600 border-blue-100';
                        $inc_total = $row['total_bajas'] + $row['total_altas'];
                        $inc_pct = round(($inc_total / max(1, $row['total_notas_global'])) * 100, 1);
                        $estabilidad = max(0, 100 - $inc_pct);
                    ?>
                    <div class="ranking-mobile-card p-3.5 sm:p-4 hover:bg-slate-50/60 transition-colors"
                         data-search="<?php echo htmlspecialchars(strtolower($pos_m.' '.$row['nombre'])); ?>">
                        <div class="flex items-start justify-between gap-3 mb-2">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <span class="w-7 h-7 rounded-lg bg-slate-900 text-white flex items-center justify-center font-black text-xs shrink-0 tabular-nums">
                                    <?php echo $pos_m; ?>
                                </span>
                                <div class="min-w-0">
                                    <?php if($row['group_key'] == 'ANON'): ?>
                                        <h3 class="text-xs sm:text-sm font-black text-slate-400 uppercase truncate"><?php echo $row['nombre']; ?></h3>
                                    <?php else: ?>
                                        <a href="perfil_juez.php?key=<?php echo $row['group_key']; ?>" class="text-xs sm:text-sm font-black text-slate-800 uppercase hover:text-blue-600 transition-colors no-underline block truncate">
                                            <?php echo $row['nombre']; ?>
                                        </a>
                                    <?php endif; ?>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider tabular-nums"><?php echo $row['total_eventos']; ?> eventos</span>
                                </div>
                            </div>
                            <?php if($row['group_key'] != 'ANON'): ?>
                                <a href="perfil_juez.php?key=<?php echo $row['group_key']; ?>" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-500 bg-slate-100 hover:bg-blue-50 hover:text-blue-600 active:scale-[0.98] transition-all shrink-0">
                                    <i class="fas fa-arrow-right text-xs"></i>
                                </a>
                            <?php endif; ?>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 pt-1 text-[10px]">
                            <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase border <?php echo $bg_p; ?> tabular-nums">
                                Prec: <?php echo number_format($row['precision_media'], 1); ?>%
                            </span>
                            <span class="px-2 py-0.5 rounded-md text-[9px] font-black border <?php echo $bg_b; ?> tabular-nums">
                                Bias: ±<?php echo number_format($row['bias_medio'], 3); ?>
                            </span>
                            <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded-md text-[9px] font-bold tabular-nums">
                                <?php echo $estabilidad; ?>% consistencia
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Vista Escritorio: Tabla (hidden md:block) -->
            <div class="hidden md:block overflow-x-auto no-scrollbar">
                <table class="w-full text-left border-collapse" id="rankingTable">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-400 text-[10px] font-black uppercase tracking-[0.2em]">
                            <th class="px-6 py-4 w-16 text-center">Puesto</th>
                            <th class="px-6 py-4">Juez</th>
                            <th class="px-6 py-4 text-center">Eventos</th>
                            <th class="px-6 py-4 text-center">Precisión Media</th>
                            <th class="px-6 py-4 text-center">Bias Medio</th>
                            <th class="px-6 py-4 text-center">Estabilidad</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php 
                        $pos = 0;
                        foreach($stats_array as $row): 
                            $pos++;
                            $bg_p = ($row['precision_media'] >= 70) ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : (($row['precision_media'] >= 50) ? 'bg-amber-50 text-amber-600 border-amber-100' : 'bg-red-50 text-red-600 border-red-100');
                            $bg_b = (abs($row['bias_medio']) >= 0.3) ? 'bg-red-50 text-red-600 border-red-100' : 'bg-blue-50 text-blue-600 border-blue-100';
                            $inc_total = $row['total_bajas'] + $row['total_altas'];
                            $inc_pct = round(($inc_total / max(1, $row['total_notas_global'])) * 100, 1);
                        ?>
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-6 py-4 text-center">
                                <span class="w-8 h-8 rounded-xl bg-slate-900 text-white inline-flex items-center justify-center font-black text-xs shadow-sm group-hover:scale-105 transition-transform tabular-nums"><?php echo $pos; ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-black text-xs border border-slate-200">
                                        <?php echo ($row['group_key'] == 'ANON') ? '?' : strtoupper(substr($row['nombre'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <?php if($row['group_key'] == 'ANON'): ?>
                                            <span class="font-black text-slate-400 uppercase text-xs tracking-tight block italic"><?php echo $row['nombre']; ?></span>
                                        <?php else: ?>
                                            <a href="perfil_juez.php?key=<?php echo $row['group_key']; ?>" class="font-black text-slate-800 uppercase text-xs tracking-tight block hover:text-blue-600 transition-colors no-underline"><?php echo $row['nombre']; ?></a>
                                        <?php endif; ?>
                                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest tabular-nums"><?php echo $row['total_notas_global']; ?> notas juzgadas</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center font-black text-slate-800 tabular-nums"><?php echo $row['total_eventos']; ?></td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 rounded-xl text-xs font-black uppercase <?php echo $bg_p; ?> border tabular-nums"><?php echo number_format($row['precision_media'], 1); ?>%</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 rounded-xl text-xs font-black <?php echo $bg_b; ?> border tabular-nums">±<?php echo number_format($row['bias_medio'], 3); ?></span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-full max-w-[90px] h-1.5 bg-slate-100 rounded-full overflow-hidden mb-1">
                                        <div class="h-full bg-blue-500" style="width: <?php echo 100 - $inc_pct; ?>%"></div>
                                    </div>
                                    <span class="text-[9px] font-black text-slate-400 uppercase tabular-nums"><?php echo 100 - $inc_pct; ?>% Consistente</span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(count($stats_array) == 0): ?>
                            <tr><td colspan="6" class="p-12 text-center text-slate-400 font-bold italic">No hay datos de auditoría grabados. Ejecuta el proceso desde la vista de competición.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Guía Técnica del Sistema de Auditoría -->
        <div class="space-y-6 sm:space-y-8 bg-white p-5 sm:p-10 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-blue-600 via-emerald-500 to-amber-400"></div>
            <div class="max-w-4xl">
                <h2 class="text-xl sm:text-2xl font-black text-slate-800 tracking-tight mb-2 uppercase">Metodología del BIAS Analizer</h2>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed font-medium mb-6 sm:mb-8">El sistema de auditoría de <b>SINCRM</b> ha sido diseñado bajo los protocolos técnicos de <b>World Aquatics (AQUA)</b> para garantizar la imparcialidad y excelencia técnica del cuerpo arbitral:</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-8">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2.5 text-blue-600">
                            <i class="fas fa-bullseye-arrow text-base"></i>
                            <h4 class="font-black uppercase tracking-wider text-xs">El Consenso Oficial</h4>
                        </div>
                        <p class="text-xs text-slate-500 leading-relaxed">Media truncada eliminando la nota más alta y más baja del panel, promediando las restantes y redondeando a la décima (0.1).</p>
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2.5 text-emerald-600">
                            <i class="fas fa-microscope text-base"></i>
                            <h4 class="font-black uppercase tracking-wider text-xs">Precisión AQUA (%)</h4>
                        </div>
                        <p class="text-xs text-slate-500 leading-relaxed">Porcentaje de notas emitidas dentro del margen oficial de tolerancia de <b>±0.2 puntos</b> respecto al consenso.</p>
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2.5 text-amber-500">
                            <i class="fas fa-scale-balanced text-base"></i>
                            <h4 class="font-black uppercase tracking-wider text-xs">Bias Score (Sesgo)</h4>
                        </div>
                        <p class="text-xs text-slate-500 leading-relaxed">Tendencia sistemática: valores negativos indican perfil <b>Severo</b> y valores positivos clasifican al juez como <b>Generoso</b>.</p>
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2.5 text-red-500">
                            <i class="fas fa-triangle-exclamation text-base"></i>
                            <h4 class="font-black uppercase tracking-wider text-xs">Inconsistencias (B/A)</h4>
                        </div>
                        <p class="text-xs text-slate-500 leading-relaxed">Frecuencia en que el juez emite notas en los extremos descartados (Baja o Alta), detectando desviaciones de campana.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function filterRankingList() {
    const input = document.getElementById('rankingSearchInput');
    const filter = input.value.toLowerCase().trim();
    
    // Filtrar tabla escritorio
    const table = document.getElementById('rankingTable');
    if (table) {
        const tr = table.getElementsByTagName('tr');
        for (let i = 1; i < tr.length; i++) {
            let textContent = tr[i].textContent.toLowerCase();
            tr[i].style.display = textContent.indexOf(filter) > -1 ? "" : "none";
        }
    }
    
    // Filtrar cards móvil
    const mobileCards = document.querySelectorAll('.ranking-mobile-card');
    mobileCards.forEach(card => {
        const searchAttr = card.getAttribute('data-search') || card.textContent.toLowerCase();
        card.style.display = searchAttr.indexOf(filter) > -1 ? "" : "none";
    });
}

document.addEventListener("DOMContentLoaded", function() {
    const data_raw = <?php echo json_encode($stats_array); ?>;
    
    // 1. Bubble Chart (Dispersión Global)
    const bubbleData = data_raw.map(row => ({
        x: parseFloat(row.bias_medio),
        y: parseFloat(row.precision_media),
        r: Math.max(4, Math.min(18, ((parseInt(row.total_bajas) + parseInt(row.total_altas)) / (parseInt(row.total_notas_global) || 1)) * 30)),
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
                    95
                ],
                fill: true,
                backgroundColor: `rgba(${(i*50)%255}, 130, 246, 0.15)`,
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
