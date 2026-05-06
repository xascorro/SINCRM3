<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-surface">
    
    <?php include('includes/topbar.php'); ?>

    <!-- Contenido de la Página -->
    <div class="p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend text-primary">
        
        <!-- Header de Sección -->
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <h1 class="text-4xl font-black text-slate-800 tracking-tighter mb-2 flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-chart-line text-lg"></i></span>
                    Estado del Sistema
                </h1>
                <p class="text-slate-500 font-medium">Monitorización en tiempo real y diagnóstico de salud del servidor.</p>
            </div>
            <div class="flex gap-3">
                <a href="configuracion_sistema.php" class="px-6 py-3 bg-blue-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg hover:bg-blue-700 transition-all flex items-center gap-2 shadow-blue-500/20">
                    <i class="fas fa-sliders"></i> Configuración
                </a>
            </div>
        </div>

        <!-- Alertas -->
        <?php if (isset($_SESSION['correcto'])): ?>
            <div class="mb-8 p-4 bg-white border-l-4 border-green-500 text-slate-700 rounded-r-2xl shadow-sm flex items-center gap-4 animate-fade-in">
                <div class="w-8 h-8 rounded-full bg-green-50 flex items-center justify-center text-green-500"><i class="fas fa-check"></i></div>
                <span class="text-sm font-bold"><?php echo $_SESSION['correcto']; unset($_SESSION['correcto']); ?></span>
            </div>
        <?php endif; ?>

        <!-- FILA 1: KPIs TÉCNICOS -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <!-- Servidor (Red y Conectividad) -->
            <div class="bg-white p-6 rounded-[2.5rem] shadow-sm border border-slate-200 border-t-[6px] border-t-purple-500 group hover:shadow-xl transition-all">
                <div class="flex justify-between items-start mb-4">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600 transition-all"><i class="fas fa-network-wired"></i></div>
                    <span class="text-[9px] font-black uppercase text-slate-400 tracking-widest italic">Conectividad</span>
                </div>
                <?php
                $ip_privada = @explode(' ', @shell_exec("hostname -I"))[0] ?? '127.0.0.1';
                $ip_publica = @file_get_contents('https://api.ipify.org') ?? 'No disponible';
                ?>
                <h3 class="text-sm font-black text-slate-800 truncate mb-3"><?php echo $_SERVER['SERVER_NAME']; ?></h3>
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">IP Pública</span>
                        <span class="text-[10px] font-black text-slate-600"><?php echo $ip_publica; ?></span>
                    </div>
                    <div class="flex items-center justify-between border-t border-slate-50 pt-1">
                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">IP Privada</span>
                        <span class="text-[10px] font-black text-slate-600"><?php echo $ip_privada; ?></span>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-[9px] font-black text-emerald-500 bg-emerald-50 px-2 py-1 rounded-lg uppercase">Online</span>
                        <span class="text-[10px] font-bold text-slate-400 italic">Status: <span class="text-emerald-600">Stable</span></span>
                    </div>
                </div>
            </div>

            <!-- Hardware (CPU, RAM, Disco y Uptime) -->
            <div class="bg-white p-6 rounded-[2.5rem] shadow-sm border border-slate-200 border-t-[6px] border-t-sky-500 group hover:shadow-xl transition-all">
                <div class="flex justify-between items-start mb-4">
                    <div class="w-10 h-10 rounded-xl bg-sky-50 flex items-center justify-center text-sky-600 transition-all"><i class="fas fa-microchip"></i></div>
                    <span class="text-[9px] font-black uppercase text-slate-400 tracking-widest italic">Hardware / Salud</span>
                </div>
                <?php
                $cpu_info = @shell_exec("grep 'model name' /proc/cpuinfo | head -1 | cut -d ':' -f 2") ?? 'CPU Desconocida';
                $mem_total = @shell_exec("free -m | grep Mem | awk '{print $2}'") ?? 0;
                $mem_used = @shell_exec("free -m | grep Mem | awk '{print $3}'") ?? 0;
                $mem_perc = ($mem_total > 0) ? round(($mem_used / $mem_total) * 100, 1) : 0;
                $mem_gb_total = round($mem_total / 1024, 1);
                $free_space = @disk_free_space(".");
                $total_space = @disk_total_space(".");
                $percent_used = ($total_space > 0) ? round((($total_space - $free_space) / $total_space) * 100, 1) : 0;
                $uptime = @shell_exec("uptime -p") ?? 'N/D';
                $uptime = str_replace('up ', '', $uptime);
                ?>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">CPU</span>
                        <span class="text-[10px] font-black text-slate-600 truncate max-w-[120px] italic"><?php echo trim($cpu_info); ?></span>
                    </div>
                    <div class="space-y-1">
                        <div class="flex justify-between items-end">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">RAM (<?php echo $mem_gb_total; ?>GB)</span>
                            <span class="text-[10px] font-black text-sky-600"><?php echo $mem_perc; ?>%</span>
                        </div>
                        <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden shadow-inner"><div class="h-full bg-sky-500 rounded-full transition-all" style="width: <?php echo $mem_perc; ?>%"></div></div>
                    </div>
                    <div class="space-y-1">
                        <div class="flex justify-between items-end">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">Disco</span>
                            <span class="text-[10px] font-black text-indigo-600"><?php echo $percent_used; ?>%</span>
                        </div>
                        <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden shadow-inner"><div class="h-full bg-indigo-500 rounded-full transition-all" style="width: <?php echo $percent_used; ?>%"></div></div>
                    </div>
                    <div class="pt-3 border-t border-slate-50 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest italic">Uptime</span>
                        </div>
                        <span class="text-[10px] font-black text-slate-600 italic"><?php echo trim($uptime); ?></span>
                    </div>
                </div>
            </div>

            <!-- Software (Stack) -->
            <div class="bg-white p-6 rounded-[2.5rem] shadow-sm border border-slate-200 border-t-[6px] border-t-emerald-500 group hover:shadow-xl transition-all">
                <div class="flex justify-between items-start mb-4">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 transition-all"><i class="fas fa-box-open"></i></div>
                    <span class="text-[9px] font-black uppercase text-slate-400 tracking-widest italic">Software / Stack</span>
                </div>
                <?php
                $so_info = @shell_exec("lsb_release -ds") ?? PHP_OS;
                $apache_ver = @shell_exec("apache2 -v | head -1 | cut -d '/' -f 2 | cut -d ' ' -f 1") ?? 'Apache';
                $db_version = "Desconocida";
                if ($connection) {
                    $db_version_q = @mysqli_query($connection, "SELECT VERSION()");
                    if ($db_version_q) { $row_v = mysqli_fetch_array($db_version_q); $db_version = explode('-', $row_v[0])[0]; }
                }
                ?>
                <div class="space-y-3">
                    <div class="flex flex-col gap-1">
                        <span class="text-[8px] font-bold text-slate-400 uppercase tracking-tighter">Sistema & Web</span>
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-black text-slate-700 truncate max-w-[100px]"><?php echo trim($so_info); ?></span>
                            <span class="text-[10px] font-bold text-indigo-500">Apache <?php echo trim($apache_ver); ?></span>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1 border-t border-slate-50 pt-2">
                        <span class="text-[8px] font-bold text-slate-400 uppercase tracking-tighter">Backend Stack</span>
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-black text-slate-700">MariaDB <?php echo $db_version; ?></span>
                            <span class="text-[10px] font-bold text-purple-600">PHP <?php echo phpversion(); ?></span>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-center p-2 bg-emerald-50/50 rounded-xl border border-emerald-100/50">
                        <span class="text-[8px] font-black text-emerald-600 uppercase tracking-[0.2em] italic">Servicios Activos</span>
                    </div>
                </div>
            </div>

            <!-- Requisitos -->
            <div class="bg-white p-6 rounded-[2.5rem] shadow-sm border border-slate-200 border-t-[6px] border-t-amber-500 group hover:shadow-xl transition-all">
                <div class="flex justify-between items-start mb-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600 transition-all"><i class="fas fa-stethoscope"></i></div>
                    <span class="text-[9px] font-black uppercase text-slate-400 tracking-widest italic">Diagnóstico</span>
                </div>
                <div class="space-y-1.5">
                    <?php
                    $reqs = ['Driver MySQLi' => extension_loaded('mysqli'), 'Librería GD' => extension_loaded('gd'), 'ZIP (Backups)' => extension_loaded('zip'), 'Config Writable' => is_writable('includes/config.php'), 'Carga Archivos' => (bool)ini_get('file_uploads')];
                    foreach($reqs as $name => $val): ?>
                        <div class="flex justify-between items-center group/item text-[9px] font-bold uppercase text-slate-500">
                            <span><?php echo $name; ?></span>
                            <?php echo $val ? '<i class="fas fa-check-circle text-emerald-500 text-xs shadow-sm"></i>' : '<i class="fas fa-times-circle text-red-500 text-xs shadow-sm"></i>'; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Salud de Tablas -->
        <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-200 border-l-[6px] border-l-emerald-500 mb-10">
            <h2 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3 italic">
                <i class="fas fa-database text-emerald-500"></i> Estructura y Salud de la Base de Datos
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 max-h-[500px] overflow-y-auto no-scrollbar pr-2">
                <?php
                if ($connection) {
                    $table_res = @mysqli_query($connection, "SELECT TABLE_NAME, TABLE_ROWS, (DATA_LENGTH + INDEX_LENGTH) as size, DATA_FREE FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$db_name' ORDER BY size DESC");
                    if ($table_res) {
                        while ($t = mysqli_fetch_assoc($table_res)): 
                            $frag = $t['DATA_FREE'] ?? 0;
                        ?>
                            <div class="flex items-center justify-between py-4 px-5 rounded-2xl bg-slate-50 border border-slate-100 hover:bg-white hover:border-emerald-200 hover:shadow-md transition-all">
                                <div class="flex flex-col gap-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 rounded-full <?php echo $frag > 0 ? 'bg-amber-400 animate-pulse' : 'bg-emerald-400'; ?>"></div>
                                        <span class="text-xs font-black text-slate-800 truncate"><?php echo $t['TABLE_NAME']; ?></span>
                                    </div>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase pl-3"><?php echo number_format($t['TABLE_ROWS'], 0, ',', '.'); ?> registros</span>
                                </div>
                                <div class="flex flex-col items-end shrink-0">
                                    <span class="text-[10px] font-black text-slate-700"><?php echo round($t['size'] / 1024, 1); ?> KB</span>
                                    <?php if($frag > 0): ?>
                                        <span class="text-[8px] font-black text-red-500 uppercase tracking-tighter italic">Frag: <?php echo round($frag / 1024, 1); ?> KB</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile;
                    }
                }
                ?>
            </div>
        </div>

        <!-- Terminal Log -->
        <div class="bg-slate-900 rounded-[2.5rem] shadow-2xl border border-slate-800 overflow-hidden">
            <div class="px-8 py-5 bg-slate-800/50 border-b border-slate-700 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex gap-1.5"><div class="w-2.5 h-2.5 rounded-full bg-red-500/80"></div><div class="w-2.5 h-2.5 rounded-full bg-amber-500/80"></div><div class="w-2.5 h-2.5 rounded-full bg-emerald-500/80"></div></div>
                    <span class="ml-4 text-[10px] font-black text-slate-500 uppercase tracking-widest italic">terminal_output.log</span>
                </div>
                <form action="db_code.php" method="POST"><button type="submit" name="clear_log" class="text-[9px] font-black uppercase text-slate-500 hover:text-red-400 transition-colors">Vaciado de Terminal</button></form>
            </div>
            <div class="p-10 font-mono text-[11px] leading-relaxed overflow-y-auto max-h-[500px] no-scrollbar text-slate-400">
                <?php
                $log_file = './log/log.txt';
                if (file_exists($log_file)) {
                    $lines = @file($log_file);
                    if ($lines) {
                        foreach (array_slice(array_reverse($lines), 0, 50) as $line) {
                            $line = htmlspecialchars($line);
                            $line = str_replace(['[SUCCESS]', '[ERROR]', '[WARNING]', '[INFO]', '[SECURITY]'], ['<span class="text-emerald-400 font-bold">[SUCCESS]</span>', '<span class="text-red-400 font-bold">[ERROR]</span>', '<span class="text-amber-400">[WARNING]</span>', '<span class="text-sky-400">[INFO]</span>', '<span class="text-purple-400 font-bold">[SECURITY]</span>'], $line);
                            $line = preg_replace('/^\[(.*?)\]/', '<span class="text-slate-600">[$1]</span>', $line);
                            echo '<div class="hover:bg-white/5 py-1 px-2 rounded transition-colors whitespace-nowrap">' . $line . '</div>';
                        }
                    }
                }
                ?>
            </div>
        </div>

    </div>
</main>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
