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

// 1. Determinar Identidad del Juez
$id_juez = 0;
if (strpos($group_key, 'J_') === 0) {
    $id_juez = (int)substr($group_key, 2);
}

// Obtener datos básicos del Juez (Ficha Oficial)
$q_juez = "SELECT * FROM jueces WHERE id = '$id_juez'";
$res_juez = mysqli_query($connection, $q_juez);
$juez_data = mysqli_fetch_assoc($res_juez);

if (!$juez_data) {
    // Si no está en jueces, buscamos en auditoria (posiblemente un nombre sin ID vinculado)
    $q_audit_name = "SELECT id_juez, nombre_entidad FROM auditoria_jueces_stats WHERE group_key = '$group_key' LIMIT 1";
    $res_audit_name = mysqli_query($connection, $q_audit_name);
    $audit_name = mysqli_fetch_assoc($res_audit_name);
    
    if (!$audit_name) {
        echo "No hay datos para este juez.";
        exit();
    }
    $id_juez = $audit_name['id_juez'];
    $nombre_display = $audit_name['nombre_entidad'];
} else {
    $nombre_display = $juez_data['nombre'] . ' ' . $juez_data['apellidos'];
}

// 2. Obtener Datos Macro de Auditoría (Opcional)
$q_macro = "
    SELECT 
        SUM(total_notas) as total_notas,
        AVG(precision_aqua) as precision_media,
        AVG(bias_score) as bias_medio,
        SUM(bajas) as total_bajas,
        SUM(altas) as total_altas,
        COUNT(DISTINCT id_competicion) as total_eventos
    FROM auditoria_jueces_stats
    WHERE group_key = '$group_key' AND entidad_tipo = 'GLOBAL'
";
$res_macro = mysqli_query($connection, $q_macro);
$macro = mysqli_fetch_assoc($res_macro);

// Valores por defecto si no tiene auditoría
if (!$macro || $macro['total_eventos'] == 0) {
    $macro = [
        'total_notas' => 0,
        'precision_media' => 0,
        'bias_medio' => 0,
        'total_bajas' => 0,
        'total_altas' => 0,
        'total_eventos' => 0
    ];
}

$macro['nombre'] = $nombre_display;

// Datos del usuario (foto y rol de sistema)
$q_user = "SELECT u.*, r.nombre as rol_nombre FROM usuarios u LEFT JOIN roles r ON u.id_rol = r.id WHERE u.id_juez_v3 = '$id_juez' OR u.username LIKE '%" . mysqli_real_escape_string($connection, $nombre_display) . "%' LIMIT 1";
$user_data = mysqli_fetch_assoc(mysqli_query($connection, $q_user));

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
    'Velocidad' => rand(60, 95), 
    'Alineación (Bias)' => $macro['precision_media'],
    'Experiencia' => min(100, ($macro['total_eventos'] + $total_paneles/5) * 10)
];

// 3. Obtener Historial de Eventos y Posiciones
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
                <h1 class="text-4xl font-black text-slate-800 tracking-tighter mb-2 italic uppercase"><?php echo $macro['nombre']; ?></h1>
                <div class="flex items-center gap-3">
                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase italic <?php echo $severity_class; ?>">Juez <?php echo $severity_label; ?></span>
                    <span class="px-3 py-1 bg-white border border-slate-200 text-slate-500 text-[10px] font-black rounded-full uppercase italic">Temporada Actual</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-12">
            
            <!-- CARTA DE ROL (Izquierda) -->
            <div class="lg:col-span-4 relative perspective-1000 group">
                <div class="w-full h-full bg-white rounded-[2.5rem] p-2 shadow-xl border border-slate-200 relative overflow-hidden transition-transform duration-500 hover:scale-[1.01] hover:-translate-y-1">
                    <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-white/60 via-transparent to-transparent pointer-events-none z-20"></div>
                    <div class="absolute -top-20 -right-20 w-40 h-40 bg-blue-500/5 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="absolute -bottom-20 -left-20 w-40 h-40 bg-emerald-500/5 rounded-full blur-3xl pointer-events-none"></div>
                    
                    <div class="bg-white rounded-[2rem] p-6 relative z-10 border border-slate-100 flex flex-col items-center text-center h-full shadow-inner">
                        
                        <div class="absolute top-6 left-6 text-blue-500">
                            <i class="fas fa-star text-xl drop-shadow-[0_0_8px_rgba(59,130,246,0.3)]"></i>
                        </div>
                        <div class="absolute top-6 right-6 text-slate-200 font-black text-xl italic opacity-50 uppercase">
                            <?php echo $user_data ? substr($user_data['rol_nombre'], 0, 2) : 'JZ'; ?>
                        </div>

                        <div class="relative mb-6 mt-4 group/photo">
                            <div class="absolute inset-0 bg-gradient-to-tr from-blue-400 to-emerald-400 rounded-[2rem] rotate-3 opacity-20 transition-transform"></div>
                            <div class="relative w-36 h-36 rounded-[2rem] bg-slate-50 border-4 border-white overflow-hidden shadow-lg flex items-center justify-center">
                                <?php if (!empty($user_data['foto']) && file_exists($user_data['foto'])): ?>
                                    <img src="<?php echo $user_data['foto']; ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full bg-slate-50 flex items-center justify-center text-5xl text-slate-200 font-black">
                                        <?php echo strtoupper(substr($macro['nombre'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <h2 class="text-xl font-black text-slate-800 uppercase tracking-tighter leading-none mb-1">
                            <?php echo htmlspecialchars($macro['nombre']); ?>
                        </h2>
                        <p class="text-[9px] font-black text-blue-600 bg-blue-50 px-3 py-1 rounded-full border border-blue-100 uppercase tracking-[0.2em] mb-6 inline-block">
                            <?php 
                            if ($juez_data && !empty($juez_data['licencia'])) {
                                echo "Lic: " . $juez_data['licencia'];
                            } elseif ($user_data) {
                                echo $user_data['rol_nombre'];
                            } else {
                                echo "JUEZ OFICIAL";
                            }
                            ?>
                        </p>

                        <div class="w-full h-44 bg-slate-50/50 rounded-3xl p-2 mb-4 border border-slate-100 shadow-inner">
                            <canvas id="radarChart"></canvas>
                        </div>

                        <div class="grid grid-cols-2 gap-3 w-full mt-auto">
                            <div class="bg-white rounded-2xl p-3 border border-slate-100 shadow-sm text-left">
                                <p class="text-[8px] font-black uppercase text-slate-400 tracking-widest mb-1">Nivel Técnico</p>
                                <p class="text-lg font-black text-slate-700"><?php echo number_format($macro['precision_media'], 1); ?>%</p>
                            </div>
                            <div class="bg-white rounded-2xl p-3 border border-slate-100 shadow-sm text-left">
                                <p class="text-[8px] font-black uppercase text-slate-400 tracking-widest mb-1">Estabilidad</p>
                                <p class="text-lg font-black text-slate-700">
                                    <?php 
                                    $inconsistencias = $macro['total_bajas'] + $macro['total_altas'];
                                    $estabilidad = max(0, 100 - (($inconsistencias / max(1, $macro['total_notas'])) * 100));
                                    echo round($estabilidad); 
                                    ?>%
                                </p>
                            </div>
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
                        <div class="p-12 bg-slate-50 rounded-3xl text-center border border-dashed border-slate-200">
                            <i class="fas fa-user-slash text-slate-300 text-3xl mb-4"></i>
                            <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Sin registros de participación detallada</p>
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
                            <?php if(empty($history)): ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-slate-400 italic text-xs">No hay datos de auditoría de puntuación registrados.</td>
                                </tr>
                            <?php else: ?>
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
                            <?php endif; ?>
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
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderColor: 'rgba(59, 130, 246, 0.8)',
                pointBackgroundColor: '#fff',
                pointBorderColor: 'rgba(59, 130, 246, 1)',
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
                    angleLines: { color: 'rgba(0, 0, 0, 0.05)' },
                    grid: { color: 'rgba(0, 0, 0, 0.05)' },
                    pointLabels: {
                        color: 'rgba(100, 116, 139, 1)',
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

    if (labels.length > 0) {
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
    } else {
        // Mostrar mensaje si no hay datos para el gráfico
        const ctx = document.getElementById('rankChart').getContext('2d');
        ctx.font = "12px Lexend";
        ctx.fillStyle = "#94a3b8";
        ctx.textAlign = "center";
        ctx.fillText("Sin datos de trayectoria", ctx.canvas.width/2, ctx.canvas.height/2);
    }
});
</script>

<style>
.perspective-1000 { perspective: 1000px; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
