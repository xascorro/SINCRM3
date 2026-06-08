<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');

$group_key = $_GET['key'] ?? '';
if (!$group_key) {
    header('Location: ranking_jueces.php');
    exit();
}

// SEGURIDAD: Los jueces solo pueden ver su propio perfil
if($_SESSION['id_rol'] == 4){
    // Obtener el key del juez logueado
    $my_id_juez = $_SESSION['id_juez_v3'];
    $q_my_key = "SELECT group_key FROM auditoria_jueces_stats WHERE id_juez = '$my_id_juez' LIMIT 1";
    $my_key = mysqli_fetch_assoc(mysqli_query($connection, $q_my_key))['group_key'] ?? 'NONE';
    
    if($group_key !== $my_key){
        $_SESSION['no_acceso'] = "No tienes permisos para ver perfiles de otros jueces.";
        header('Location: ranking_jueces.php');
        exit();
    }
}

// 1. Obtener Datos Macro del Juez
$q_macro = "
    SELECT 
        nombre_entidad as nombre,
        MAX(id_juez) as id_juez,
        SUM(total_notas) as total_notas,
        AVG(precision_aqua) as precision_media,
        AVG(bias_score) as bias_medio,
        SUM(bajas) as total_bajas,
        SUM(altas) as total_altas,
        COUNT(DISTINCT id_competicion) as total_eventos
    FROM auditoria_jueces_stats
    WHERE group_key = '$group_key' AND entidad_tipo = 'GLOBAL'
    GROUP BY group_key
";
$res_macro = mysqli_query($connection, $q_macro);
$macro = mysqli_fetch_assoc($res_macro);

if (!$macro) {
    echo "No hay datos para este juez.";
    exit();
}

$id_juez = $macro['id_juez'];

// Datos del usuario (foto)
$q_user = "SELECT u.*, r.nombre as rol_nombre FROM usuarios u LEFT JOIN roles r ON u.id_rol = r.id WHERE u.id_juez_v3 = '$id_juez' OR u.username LIKE '%" . mysqli_real_escape_string($connection, $macro['nombre']) . "%' LIMIT 1";
$user_data = mysqli_fetch_assoc(mysqli_query($connection, $q_user));

// Datos oficiales del Juez
$q_juez = "SELECT * FROM jueces WHERE id = '$id_juez'";
$juez_data = mysqli_fetch_assoc(mysqli_query($connection, $q_juez));

// Participaciones por puesto en paneles (Puestos de Juez)
$q_puestos = "SELECT p.nombre as puesto, COUNT(*) as cantidad 
              FROM puesto_juez pj 
              JOIN puestos_juez p ON pj.id_puestos_juez = p.id 
              WHERE pj.id_juez = '$id_juez' 
              GROUP BY p.nombre 
              ORDER BY cantidad DESC";
$res_puestos = mysqli_query($connection, $q_puestos);
$puestos_stats = [];
$total_paneles = 0;
while($p = mysqli_fetch_assoc($res_puestos)){
    $puestos_stats[] = $p;
    $total_paneles += $p['cantidad'];
}

// Generar stats para el radar
$radar_stats = [
    'Severidad' => min(100, max(0, 100 - abs($macro['bias_medio'] * 100))),
    'Consistencia' => min(100, max(0, 100 - (($macro['total_bajas'] + $macro['total_altas']) / max(1, $macro['total_notas'])) * 500)),
    'Velocidad' => rand(60, 95), // Simulado temporalmente
    'Alineación (Bias)' => $macro['precision_media'],
    'Experiencia' => min(100, $macro['total_eventos'] * 10)
];

// 2. Obtener Historial de Eventos y Posiciones
// Para calcular la posición, necesitamos comparar con otros jueces en cada evento
$q_history = "
    SELECT 
        s.id_competicion,
        c.nombre as comp_nombre,
        c.fecha,
        s.precision_aqua,
        s.bias_score,
        s.pjs_asociados,
        (SELECT COUNT(*) + 1 
         FROM auditoria_jueces_stats s2 
         WHERE s2.id_competicion = s.id_competicion 
         AND s2.entidad_tipo = 'GLOBAL' 
         AND (s2.precision_aqua > s.precision_aqua OR (s2.precision_aqua = s.precision_aqua AND ABS(s2.bias_score) < ABS(s.bias_score)))
        ) as posicion_evento
    FROM auditoria_jueces_stats s
    JOIN competiciones c ON s.id_competicion = c.id
    WHERE s.group_key = '$group_key' AND s.entidad_tipo = 'GLOBAL'
    ORDER BY c.fecha DESC
";
$res_history = mysqli_query($connection, $q_history);
$history = [];
while($h = mysqli_fetch_assoc($res_history)) $history[] = $h;

// Preparar Gráfico de Posición (Invertido: 1 arriba, 10 abajo)
$chart_labels = array_reverse(array_column($history, 'comp_nombre'));
$chart_positions = array_reverse(array_column($history, 'posicion_evento'));

$severity_label = "Equilibrado";
$severity_class = "text-slate-500 bg-slate-100";
if ($macro['bias_medio'] <= -0.3) { $severity_label = "Severo"; $severity_class = "text-red-600 bg-red-100"; }
elseif ($macro['bias_medio'] >= 0.3) { $severity_label = "Generoso"; $severity_class = "text-emerald-600 bg-emerald-100"; }
?>

<main class="flex-1 flex flex-col min-w-0 bg-surface">
    <?php include('includes/topbar.php'); ?>
    <div class="p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend">
        
        <div class="mb-10 flex flex-col md:flex-row justify-between items-start md:items-end gap-6">
            <div>
                <a href="ranking_jueces.php" class="text-blue-600 font-bold text-xs uppercase tracking-widest mb-2 flex items-center gap-2 hover:gap-3 transition-all no-underline"><i class="fas fa-arrow-left"></i> Volver al Ranking</a>
                <h1 class="text-4xl font-black text-slate-800 tracking-tighter mb-2 italic"><?php echo $macro['nombre']; ?></h1>
                <div class="flex items-center gap-3">
                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase italic <?php echo $severity_class; ?>">Juez <?php echo $severity_label; ?></span>
                    <span class="px-3 py-1 bg-white border border-slate-200 text-slate-500 text-[10px] font-black rounded-full uppercase italic">Temporada 24/25</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-12">
            
            <!-- CARTA DE ROL (Izquierda) -->
            <div class="lg:col-span-4 relative perspective-1000 group">
                <div class="w-full h-full bg-slate-800 rounded-[2.5rem] p-2 shadow-2xl border-4 border-slate-700 relative overflow-hidden transition-transform duration-500 hover:scale-[1.01] hover:-translate-y-1">
                    <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-white/10 to-transparent pointer-events-none z-20"></div>
                    <div class="absolute -top-20 -right-20 w-40 h-40 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="absolute -bottom-20 -left-20 w-40 h-40 bg-blue-500/20 rounded-full blur-3xl pointer-events-none"></div>
                    
                    <div class="bg-slate-900 rounded-[2rem] p-6 relative z-10 border border-slate-700/50 flex flex-col items-center text-center h-full">
                        
                        <div class="absolute top-6 left-6 text-emerald-400">
                            <i class="fas fa-star text-xl drop-shadow-[0_0_8px_rgba(52,211,153,0.8)]"></i>
                        </div>
                        <div class="absolute top-6 right-6 text-slate-500 font-black text-xl italic opacity-50 uppercase">
                            <?php echo $user_data ? substr($user_data['rol_nombre'], 0, 2) : 'JZ'; ?>
                        </div>

                        <div class="relative mb-6 mt-4 group/photo">
                            <div class="absolute inset-0 bg-gradient-to-tr from-emerald-500 to-blue-500 rounded-[2rem] rotate-3 opacity-70"></div>
                            <div class="relative w-36 h-36 rounded-[2rem] bg-slate-800 border-4 border-slate-900 overflow-hidden shadow-inner flex items-center justify-center">
                                <?php if (!empty($user_data['foto']) && file_exists($user_data['foto'])): ?>
                                    <img src="<?php echo $user_data['foto']; ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full bg-slate-800 flex items-center justify-center text-5xl text-slate-600 font-black">
                                        <?php echo strtoupper(substr($macro['nombre'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <h2 class="text-xl font-black text-white uppercase tracking-tighter leading-none mb-1">
                            <?php echo htmlspecialchars($macro['nombre']); ?>
                        </h2>
                        <p class="text-[9px] font-black text-emerald-400 uppercase tracking-[0.3em] mb-6">
                            <?php echo ($juez_data && !empty($juez_data['licencia'])) ? "Licencia: " . $juez_data['licencia'] : ($user_data ? $user_data['rol_nombre'] : 'Juez Oficial'); ?>
                        </p>

                        <div class="w-full h-44 bg-slate-800/50 rounded-3xl p-2 mb-4 border border-slate-700/50">
                            <canvas id="radarChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PARTICIPACIONES Y MACRO STATS (Derecha) -->
            <div class="lg:col-span-8 flex flex-col gap-6">
                <!-- Macro Stats Grid -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white p-5 rounded-[2rem] shadow-sm border border-slate-200">
                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Precisión Media</p>
                        <h3 class="text-2xl font-black text-emerald-500"><?php echo number_format($macro['precision_media'], 1); ?>%</h3>
                    </div>
                    <div class="bg-white p-5 rounded-[2rem] shadow-sm border border-slate-200">
                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Bias Score</p>
                        <h3 class="text-2xl font-black <?php echo (abs($macro['bias_medio']) < 0.3) ? 'text-blue-500' : 'text-red-500'; ?>">±<?php echo number_format($macro['bias_medio'], 3); ?></h3>
                    </div>
                    <div class="bg-white p-5 rounded-[2rem] shadow-sm border border-slate-200">
                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Eventos</p>
                        <h3 class="text-2xl font-black text-slate-800"><?php echo $macro['total_eventos']; ?></h3>
                    </div>
                    <div class="bg-white p-5 rounded-[2rem] shadow-sm border border-slate-200">
                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Inconsistencias</p>
                        <h3 class="text-2xl font-black text-red-400"><?php echo $macro['total_bajas'] + $macro['total_altas']; ?></h3>
                    </div>
                </div>

                <!-- Historial de Participaciones (Paneles) -->
                <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-200 flex-1">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest italic flex items-center gap-2">
                            <i class="fas fa-clipboard-list text-blue-600"></i> Participación Técnica
                        </h3>
                        <span class="px-3 py-1 bg-blue-50 text-blue-600 text-[10px] font-black rounded-full"><?php echo $total_paneles; ?> Paneles</span>
                    </div>
                    
                    <?php if(empty($puestos_stats)): ?>
                        <div class="p-6 bg-slate-50 rounded-2xl text-center">
                            <p class="text-xs text-slate-500 font-bold italic">No hay registros detallados de participación en esta temporada.</p>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <?php foreach($puestos_stats as $ps): 
                                $pct = ($total_paneles > 0) ? round(($ps['cantidad'] / $total_paneles) * 100) : 0;
                            ?>
                                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 flex flex-col justify-between group-hover:bg-white transition-all">
                                    <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest leading-tight mb-2"><?php echo htmlspecialchars($ps['puesto']); ?></span>
                                    <div class="flex items-end justify-between mt-auto">
                                        <span class="text-2xl font-black text-slate-800 leading-none"><?php echo $ps['cantidad']; ?></span>
                                        <span class="text-[10px] font-bold text-slate-400"><?php echo $pct; ?>%</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
            <!-- Gráfico de Evolución de Puesto -->
            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-200">
                <h3 class="text-lg font-black text-slate-800 mb-2 uppercase tracking-tighter italic flex items-center gap-3">
                    <i class="fas fa-chart-line text-blue-600"></i> Trayectoria en el Ranking
                </h3>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-8">Evolución de la posición técnica por evento (Menor es mejor)</p>
                <div class="relative h-64">
                    <canvas id="rankChart"></canvas>
                </div>
            </div>

            <!-- Tabla de Historial -->
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-6 border-b border-slate-50 bg-slate-50/50">
                    <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest italic flex items-center gap-2">
                        <i class="fas fa-history text-blue-600"></i> Historial de Auditorías
                    </h3>
                </div>
                <div class="overflow-x-auto max-h-[350px] overflow-y-auto no-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 sticky top-0 z-10 shadow-sm">
                                <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Competición</th>
                                <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Precisión</th>
                                <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Puesto</th>
                                <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php foreach($history as $h): 
                                $bg_h = ($h['precision_aqua'] >= 70) ? 'text-emerald-500' : ($h['precision_aqua'] >= 50 ? 'text-amber-500' : 'text-red-500');
                            ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 font-bold text-slate-700 text-xs">
                                    <?php echo $h['comp_nombre']; ?>
                                    <p class="text-[8px] text-slate-400 uppercase"><?php echo dateAFecha($h['fecha']); ?></p>
                                </td>
                                <td class="px-6 py-4 text-center font-black <?php echo $bg_h; ?> text-xs"><?php echo number_format($h['precision_aqua'], 1); ?>%</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-lg text-[10px] font-black">#<?php echo $h['posicion_evento']; ?></span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="analisis_juez_detalle.php?pjs=<?php echo $h['pjs_asociados']; ?>&comp_id=<?php echo $h['id_competicion']; ?>" class="w-8 h-8 rounded-lg bg-slate-900 text-white flex items-center justify-center hover:bg-blue-600 transition-all shadow-sm mx-auto ml-auto">
                                        <i class="fas fa-eye text-[10px]"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // --- GRAFICO RADAR (CARTA DE ROL) ---
    const ctxRadar = document.getElementById('radarChart').getContext('2d');
    new Chart(ctxRadar, {
        type: 'radar',
        data: {
            labels: ['Severidad', 'Consistencia', 'Velocidad', 'Precisión', 'Experiencia'],
            datasets: [{
                label: 'Atributos',
                data: [
                    <?php echo $radar_stats['Severidad']; ?>, 
                    <?php echo $radar_stats['Consistencia']; ?>, 
                    <?php echo $radar_stats['Velocidad']; ?>, 
                    <?php echo $radar_stats['Alineación (Bias)']; ?>, 
                    <?php echo $radar_stats['Experiencia']; ?>
                ],
                backgroundColor: 'rgba(16, 185, 129, 0.2)',
                borderColor: 'rgba(52, 211, 153, 1)',
                pointBackgroundColor: '#fff',
                pointBorderColor: 'rgba(52, 211, 153, 1)',
                borderWidth: 2,
                pointRadius: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                r: {
                    angleLines: { color: 'rgba(255, 255, 255, 0.1)' },
                    grid: { color: 'rgba(255, 255, 255, 0.1)' },
                    pointLabels: {
                        color: 'rgba(148, 163, 184, 1)',
                        font: { size: 8, family: "'Lexend', sans-serif", weight: 'bold' }
                    },
                    ticks: { display: false, min: 0, max: 100 }
                }
            }
        }
    });

    // --- GRAFICO DE EVOLUCION ---
    const labels = <?php echo json_encode($chart_labels); ?>;
    const positions = <?php echo json_encode($chart_positions); ?>;

    new Chart(document.getElementById('rankChart'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Posición Ranking',
                data: positions,
                borderColor: '#3b82f6',
                borderWidth: 3,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#3b82f6',
                pointBorderWidth: 2,
                pointRadius: 6,
                fill: true,
                backgroundColor: 'rgba(59, 130, 246, 0.05)',
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    reverse: true, // Puesto 1 arriba
                    ticks: { precision: 0 },
                    title: { display: true, text: 'Posición' }
                },
                x: { display: false }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
});
</script>

<style>
.perspective-1000 { perspective: 1000px; }
</style>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
