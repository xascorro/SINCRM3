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
    $q_user = "SELECT * FROM usuarios WHERE id = '$user_id'";
    $user_data = mysqli_fetch_assoc(mysqli_query($connection, $q_user));
    if ($user_data) {
        $username = $user_data['username'];
        $user_email = $user_data['email'];
    }
} else {
    // Usuario por defecto (él mismo)
    $q_user = "SELECT * FROM usuarios WHERE id = '$user_id'";
    $user_data = mysqli_fetch_assoc(mysqli_query($connection, $q_user));
}

// Intentar encontrar al juez por nombre
$q_juez = "SELECT * FROM jueces WHERE nombre LIKE '%$username%' OR apellidos LIKE '%$username%' LIMIT 1";
$res_juez = mysqli_query($connection, $q_juez);
$juez_data = mysqli_fetch_assoc($res_juez);

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
<main class="flex-1 flex flex-col min-w-0 bg-slate-900 bg-cover bg-center" style="background-image: url('https://www.transparenttextures.com/patterns/cubes.png');">
    <?php include('includes/topbar.php'); ?>
    <div class="p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend flex flex-col items-center justify-center min-h-[80vh] py-12">
        
        <div class="mb-10 text-center">
            <h1 class="text-4xl md:text-5xl font-black text-white tracking-tighter mb-2 uppercase drop-shadow-lg">Mi Auditoría</h1>
            <p class="text-emerald-400 font-bold tracking-widest text-sm uppercase">Perfil Oficial de Juez</p>
        </div>

        <?php if ($es_admin): ?>
            <!-- Selector de Administrador -->
            <div class="w-full max-w-sm mx-auto mb-8 bg-slate-800/80 p-4 rounded-3xl border border-slate-700 shadow-xl backdrop-blur-sm z-50">
                <form action="mi_auditoria.php" method="GET" class="flex flex-col gap-2">
                    <label class="text-[9px] font-black uppercase tracking-[0.2em] text-emerald-400 px-2">Modo Administrador: Seleccionar Perfil</label>
                    <select name="usuario_id" class="w-full bg-slate-900 border border-slate-600 text-white rounded-2xl px-4 py-3 text-sm focus:border-emerald-500 outline-none" onchange="this.form.submit()">
                        <option value="0">-- Mi propio perfil --</option>
                        <?php
                        $q_usuarios_jueces = "SELECT id, username FROM usuarios WHERE id_rol IN (1, 4) AND activo = 1 ORDER BY username ASC";
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
            <div class="w-full bg-slate-800 rounded-[2.5rem] p-2 shadow-2xl border-4 border-slate-700 relative overflow-hidden transition-transform duration-500 hover:scale-105 hover:-translate-y-2 hover:shadow-[0_20px_50px_rgba(16,185,129,0.2)]">
                <!-- Efecto brillo -->
                <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-white/10 to-transparent pointer-events-none z-20"></div>
                <div class="absolute -top-20 -right-20 w-40 h-40 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-20 -left-20 w-40 h-40 bg-blue-500/20 rounded-full blur-3xl pointer-events-none"></div>
                
                <div class="bg-slate-900 rounded-[2rem] p-6 relative z-10 border border-slate-700/50 flex flex-col items-center text-center">
                    
                    <!-- Rango / Nivel -->
                    <div class="absolute top-6 left-6 text-emerald-400">
                        <i class="fas fa-star text-xl drop-shadow-[0_0_8px_rgba(52,211,153,0.8)]"></i>
                    </div>
                    <div class="absolute top-6 right-6 text-slate-500 font-black text-xl italic opacity-50">
                        JZ
                    </div>

                    <!-- FOTO -->
                    <div class="relative mb-6 mt-4 group/photo">
                        <div class="absolute inset-0 bg-gradient-to-tr from-emerald-500 to-blue-500 rounded-[2rem] rotate-3 opacity-70 group-hover/photo:rotate-6 transition-transform"></div>
                        <div class="relative w-36 h-36 rounded-[2rem] bg-slate-800 border-4 border-slate-900 overflow-hidden shadow-inner flex items-center justify-center">
                            <?php if (!empty($user_data['foto']) && file_exists($user_data['foto'])): ?>
                                <img src="<?php echo $user_data['foto']; ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full bg-slate-800 flex items-center justify-center text-5xl text-slate-600 font-black">
                                    <?php echo strtoupper(substr($username, 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- INFO BASICA -->
                    <h2 class="text-2xl font-black text-white uppercase tracking-tighter leading-none mb-1">
                        <?php echo htmlspecialchars($username); ?>
                    </h2>
                    <p class="text-[10px] font-black text-emerald-400 uppercase tracking-[0.3em] mb-6">
                        <?php echo $juez_data ? "Licencia: " . $juez_data['licencia'] : "Juez en Formación"; ?>
                    </p>

                    <!-- RADAR CHART -->
                    <div class="w-full h-48 bg-slate-800/50 rounded-3xl p-2 mb-6 border border-slate-700/50">
                        <canvas id="radarChart"></canvas>
                    </div>

                    <!-- ESTADISTICAS TEXTO -->
                    <div class="grid grid-cols-2 gap-3 w-full">
                        <div class="bg-slate-800 rounded-2xl p-3 border border-slate-700 text-left">
                            <p class="text-[8px] font-black uppercase text-slate-500 tracking-widest mb-1">Precisión</p>
                            <p class="text-lg font-black text-white"><?php echo $stats['Alineación (Bias)']; ?>%</p>
                        </div>
                        <div class="bg-slate-800 rounded-2xl p-3 border border-slate-700 text-left">
                            <p class="text-[8px] font-black uppercase text-slate-500 tracking-widest mb-1">Consistencia</p>
                            <p class="text-lg font-black text-white"><?php echo $stats['Consistencia']; ?>%</p>
                        </div>
                    </div>

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
                    font: { size: 9, family: "'Lexend', sans-serif", weight: 'bold' }
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