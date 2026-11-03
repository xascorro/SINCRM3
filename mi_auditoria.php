<?php
include('security.php');

$roles_permitidos = [1, 2, 3, 4]; // Admin, Editor, Consulta, Juez
if (!in_array($_SESSION['id_rol'], $roles_permitidos)) {
    header('Location: ranking_jueces.php');
    exit();
}

$es_admin = in_array($_SESSION['id_rol'], [1, 2, 3]);

$user_email = $_SESSION['email'];
$username = $_SESSION['username'];
$user_id = $_SESSION['id_usario'];

// Si el admin selecciona a un usuario en específico
if ($es_admin && isset($_GET['usuario_id']) && $_GET['usuario_id'] > 0) {
    $user_id = (int)$_GET['usuario_id'];
    $q_user = "SELECT u.*, r.nombre as rol_nombre FROM usuarios u LEFT JOIN roles r ON u.id_rol = r.id WHERE u.id = '$user_id'";
    $user_data = mysqli_fetch_assoc(mysqli_query($connection, $q_user));
    if ($user_data) {
        $username = $user_data['username'];
        $user_email = $user_data['email'];
    }
} else {
    // Usuario por defecto (él mismo)
    $q_user = "SELECT u.*, r.nombre as rol_nombre FROM usuarios u LEFT JOIN roles r ON u.id_rol = r.id WHERE u.id = '$user_id'";
    $user_data = mysqli_fetch_assoc(mysqli_query($connection, $q_user));
}

// Intentar encontrar al juez por nombre
$q_juez = "SELECT * FROM jueces WHERE nombre LIKE '%$username%' OR apellidos LIKE '%$username%' LIMIT 1";
$res_juez = mysqli_query($connection, $q_juez);
$juez_data = mysqli_fetch_assoc($res_juez);

// Buscar si tiene un informe de auditoría detallado
$q_find = "SELECT group_key FROM auditoria_jueces_stats WHERE nombre_entidad LIKE '%$username%' AND entidad_tipo = 'GLOBAL' LIMIT 1";
$res_find = mysqli_query($connection, $q_find);
$found = mysqli_fetch_assoc($res_find);
$group_key = $found ? $found['group_key'] : null;

// Generar stats de prueba para el radar
$stats = [
    'Severidad' => rand(60, 95),
    'Consistencia' => rand(70, 98),
    'Velocidad' => rand(50, 90),
    'Alineación (Bias)' => rand(65, 99),
    'Experiencia' => rand(40, 95)
];

include('includes/header.php');
include('includes/navbar.php');
?>
<main class="flex-1 flex flex-col min-w-0 bg-surface">
    <?php include('includes/topbar.php'); ?>
    
    <div class="px-3 py-4 sm:p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend flex flex-col items-center justify-center min-h-[75vh]">
        
        <!-- Header de Sección -->
        <div class="mb-6 sm:mb-8 text-center">
            <h1 class="text-2xl sm:text-4xl font-black text-slate-800 tracking-tighter mb-1 uppercase flex items-center justify-center gap-2.5 sm:gap-3">
                <span class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 flex items-center justify-center text-primary shadow-sm border border-slate-200">
                    <i class="fas fa-user-check text-base sm:text-lg"></i>
                </span>
                Mi Auditoría
            </h1>
            <p class="text-xs sm:text-sm text-emerald-600 font-bold tracking-wider uppercase">Perfil Oficial del Sistema y Autocontrol Arbitral</p>
        </div>

        <?php if ($es_admin): ?>
            <!-- Selector de Administrador -->
            <div class="w-full max-w-sm mx-auto mb-6 bg-white p-3.5 sm:p-4 rounded-2xl sm:rounded-3xl border border-slate-200 shadow-sm z-30">
                <form action="mi_auditoria.php" method="GET" class="flex flex-col gap-1.5">
                    <label class="text-[9px] font-black uppercase tracking-wider text-blue-600 px-1">Modo Administrador: Seleccionar Perfil</label>
                    <select name="usuario_id" class="w-full bg-slate-50 border border-slate-200 text-slate-700 font-bold rounded-xl px-3.5 py-2.5 text-xs focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 outline-none transition-all" onchange="this.form.submit()">
                        <option value="0">-- Mi propio perfil --</option>
                        <?php
                        $q_usuarios_jueces = "SELECT id, username FROM usuarios WHERE id_rol IN (1, 2, 3, 4) AND activo = 1 ORDER BY username ASC";
                        $res_us = mysqli_query($connection, $q_usuarios_jueces);
                        while ($us = mysqli_fetch_assoc($res_us)) {
                            $sel = ($us['id'] == $user_id && isset($_GET['usuario_id']) && $_GET['usuario_id'] > 0) ? 'selected' : '';
                            echo "<option value='{$us['id']}' $sel>{$us['username']}</option>";
                        }
                        ?>
                    </select>
                </form>
            </div>
        <?php endif; ?>

        <!-- CARTA DE ROL -->
        <div class="relative w-full max-w-sm mx-auto perspective-1000 group">
            <div class="w-full bg-slate-800 rounded-2xl sm:rounded-3xl p-2 shadow-2xl border-4 border-slate-700 relative overflow-hidden transition-all duration-300 hover:shadow-[0_20px_50px_rgba(16,185,129,0.2)]">
                <!-- Efecto brillo -->
                <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-white/10 to-transparent pointer-events-none z-20"></div>
                <div class="absolute -top-20 -right-20 w-40 h-40 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-20 -left-20 w-40 h-40 bg-blue-500/20 rounded-full blur-3xl pointer-events-none"></div>
                
                <div class="bg-slate-900 rounded-xl sm:rounded-2xl p-4 sm:p-6 relative z-10 border border-slate-700/50 flex flex-col items-center text-center">
                    
                    <!-- Rango / Nivel -->
                    <div class="absolute top-4 left-4 text-emerald-400">
                        <i class="fas fa-star text-base sm:text-lg drop-shadow-[0_0_8px_rgba(52,211,153,0.8)]"></i>
                    </div>
                    <div class="absolute top-4 right-4 text-slate-500 font-black text-lg italic uppercase">
                        <?php echo $user_data ? substr($user_data['rol_nombre'], 0, 2) : 'JZ'; ?>
                    </div>

                    <!-- FOTO -->
                    <div class="relative mb-4 mt-2">
                        <div class="absolute inset-0 bg-gradient-to-tr from-emerald-500 to-blue-500 rounded-2xl sm:rounded-3xl rotate-3 opacity-60"></div>
                        <div class="relative w-28 h-28 sm:w-32 sm:h-32 rounded-2xl sm:rounded-3xl bg-slate-800 border-4 border-slate-900 overflow-hidden shadow-inner flex items-center justify-center">
                            <?php if (!empty($user_data['foto']) && file_exists($user_data['foto'])): ?>
                                <img src="<?php echo $user_data['foto']; ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full bg-slate-800 flex items-center justify-center text-4xl text-slate-500 font-black">
                                    <?php echo strtoupper(substr($username, 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- INFO BÁSICA -->
                    <h2 class="text-lg sm:text-xl font-black text-white uppercase tracking-tight leading-none mb-1">
                        <?php echo htmlspecialchars($username); ?>
                    </h2>
                    <p class="text-[9px] font-black text-emerald-400 uppercase tracking-widest mb-4">
                        <?php echo ($juez_data && !empty($juez_data['licencia'])) ? "Licencia: " . $juez_data['licencia'] : ($user_data ? $user_data['rol_nombre'] : 'Juez Oficial'); ?>
                    </p>

                    <!-- RADAR CHART -->
                    <div class="w-full h-40 sm:h-44 bg-slate-800/50 rounded-2xl p-2 mb-4 border border-slate-700/50">
                        <canvas id="radarChart"></canvas>
                    </div>

                    <!-- ESTADÍSTICAS TABULARES -->
                    <div class="grid grid-cols-2 gap-2.5 w-full">
                        <div class="bg-slate-800/80 rounded-xl p-2.5 border border-slate-700 text-left">
                            <p class="text-[8px] font-black uppercase text-slate-400 tracking-widest mb-0.5">Precisión</p>
                            <p class="text-base sm:text-lg font-black text-white tabular-nums"><?php echo $stats['Alineación (Bias)']; ?>%</p>
                        </div>
                        <div class="bg-slate-800/80 rounded-xl p-2.5 border border-slate-700 text-left">
                            <p class="text-[8px] font-black uppercase text-slate-400 tracking-widest mb-0.5">Consistencia</p>
                            <p class="text-base sm:text-lg font-black text-white tabular-nums"><?php echo $stats['Consistencia']; ?>%</p>
                        </div>
                    </div>

                    <!-- ACCESO A INFORME COMPLETO -->
                    <?php if ($group_key): ?>
                        <div class="mt-4 w-full">
                            <a href="perfil_juez.php?key=<?php echo $group_key; ?>" class="flex items-center justify-center gap-2 w-full py-2.5 sm:py-3 bg-emerald-600 text-white font-black uppercase text-[10px] tracking-wider rounded-xl shadow-md hover:bg-emerald-500 active:scale-[0.98] transition-all no-underline">
                                <i class="fas fa-chart-pie text-xs"></i> Ver Informe Completo
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="mt-4 w-full">
                            <div class="w-full py-2.5 sm:py-3 bg-slate-800 text-slate-400 font-black uppercase text-[10px] tracking-wider rounded-xl border border-slate-700 flex items-center justify-center gap-2 italic">
                                <i class="fas fa-lock text-xs"></i> Sin datos de auditoría
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>

    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('radarChart').getContext('2d');
new Chart(ctx, {
    type: 'radar',
    data: {
        labels: ['Severidad', 'Consistencia', 'Velocidad', 'Precisión', 'Experiencia'],
        datasets: [{
            label: 'Atributos',
            data: [
                <?php echo $stats['Severidad']; ?>, 
                <?php echo $stats['Consistencia']; ?>, 
                <?php echo $stats['Velocidad']; ?>, 
                <?php echo $stats['Alineación (Bias)']; ?>, 
                <?php echo $stats['Experiencia']; ?>
            ],
            backgroundColor: 'rgba(16, 185, 129, 0.2)',
            borderColor: 'rgba(52, 211, 153, 1)',
            pointBackgroundColor: '#fff',
            pointBorderColor: 'rgba(52, 211, 153, 1)',
            pointHoverBackgroundColor: 'rgba(52, 211, 153, 1)',
            pointHoverBorderColor: '#fff',
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
</script>

<style>
.perspective-1000 { perspective: 1000px; }
</style>

<?php
include('includes/scripts.php');
include('includes/footer.php');
?>
