<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-surface">
    
    <?php include('includes/topbar.php'); ?>

    <!-- Contenido de la Página -->
    <div class="p-6 md:p-10 max-w-7xl mx-auto w-full">
        
        <!-- Header de Sección -->
        <div class="mb-12 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <h1 class="text-4xl font-black text-primary tracking-tighter mb-2">Log del Sistema</h1>
                <p class="text-slate-500 font-medium">Historial técnico de eventos y seguridad en tiempo real.</p>
            </div>
            <div class="flex gap-3">
                <a href="mantenimiento.php" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition-all flex items-center gap-2">
                    <i class="fas fa-arrow-left text-xs"></i> Volver
                </a>
                <button onclick="window.location.reload()" class="px-5 py-2.5 oceanic-gradient text-white font-bold rounded-xl shadow-lg shadow-oceanic/20 hover:scale-105 transition-all flex items-center gap-2">
                    <i class="fas fa-sync text-xs"></i> Actualizar
                </button>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white p-4 rounded-3xl border border-slate-200 shadow-sm mb-8 flex flex-wrap gap-2 items-center">
            <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest px-4">Filtrar por nivel:</span>
            <a href="log.php" class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors">TODOS</a>
            <a href="?level=SUCCESS" class="px-4 py-2 rounded-xl text-xs font-bold bg-green-50 text-green-600 hover:bg-green-100 transition-colors">SUCCESS</a>
            <a href="?level=ERROR" class="px-4 py-2 rounded-xl text-xs font-bold bg-red-50 text-red-600 hover:bg-red-100 transition-colors">ERROR</a>
            <a href="?level=WARNING" class="px-4 py-2 rounded-xl text-xs font-bold bg-amber-50 text-amber-600 hover:bg-amber-100 transition-colors">WARNING</a>
            <a href="?level=INFO" class="px-4 py-2 rounded-xl text-xs font-bold bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">INFO</a>
            <a href="?level=SECURITY" class="px-4 py-2 rounded-xl text-xs font-bold bg-purple-50 text-purple-600 hover:bg-purple-100 transition-colors">SECURITY</a>
        </div>

        <!-- Visor de Log -->
        <div class="bg-[#001629] rounded-[2.5rem] p-8 shadow-2xl border border-white/5 relative overflow-hidden">
            <div class="flex items-center gap-3 mb-6 pb-6 border-b border-white/5">
                <div class="w-3 h-3 rounded-full bg-red-500"></div>
                <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                <div class="w-3 h-3 rounded-full bg-green-500"></div>
                <span class="ml-4 text-[10px] font-black text-slate-500 uppercase tracking-[0.3em]">sincrm-v3.0.log</span>
            </div>

            <div class="font-mono text-sm leading-relaxed overflow-x-auto space-y-1 max-h-[700px] overflow-y-auto custom-scrollbar pr-4 text-slate-300">
                <?php
                $archivo_log = './log/log.txt';
                $level_filter = $_GET['level'] ?? null;
                $pagina_actual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
                $lineas_por_pagina = 100;

                if (file_exists($archivo_log)) {
                    $lineas = file($archivo_log);
                    if ($level_filter) {
                        $lineas = array_filter($lineas, function($line) use ($level_filter) {
                            return strpos($line, "[$level_filter]") !== false;
                        });
                    }
                    $lineas = array_reverse($lineas);
                    $total_lineas = count($lineas);
                    $total_paginas = ceil($total_lineas / $lineas_por_pagina);
                    $inicio = ($pagina_actual - 1) * $lineas_por_pagina;
                    $pagina_lineas = array_slice($lineas, $inicio, $lineas_por_pagina);

                    if (empty($pagina_lineas)) {
                        echo '<div class="text-center py-20 opacity-30 italic">No hay registros para este nivel.</div>';
                    }

                    foreach ($pagina_lineas as $linea) {
                        $linea = htmlspecialchars($linea);
                        $linea = str_replace('[SUCCESS]', '<span class="text-green-400 font-bold">[SUCCESS]</span>', $linea);
                        $linea = str_replace('[ERROR]', '<span class="text-red-400 font-bold">[ERROR]</span>', $linea);
                        $linea = str_replace('[WARNING]', '<span class="text-amber-400 font-bold">[WARNING]</span>', $linea);
                        $linea = str_replace('[INFO]', '<span class="text-blue-400 font-bold">[INFO]</span>', $linea);
                        $linea = str_replace('[SECURITY]', '<span class="text-purple-400 font-bold">[SECURITY]</span>', $linea);
                        $linea = preg_replace('/^\[(.*?)\]/', '<span class="text-slate-600">[$1]</span>', $linea);

                        echo '<div class="hover:bg-white/5 py-1 px-2 rounded-lg transition-colors">' . $linea . '</div>';
                    }
                } else {
                    echo '<div class="text-center py-20 opacity-30 italic">Archivo de log no encontrado.</div>';
                }
                ?>
            </div>

            <!-- Paginación -->
            <?php if (isset($total_paginas) && $total_paginas > 1): ?>
            <div class="mt-10 flex justify-center gap-2">
                <?php
                $url_params = $level_filter ? "&level=$level_filter" : "";
                for ($i = 1; $i <= min($total_paginas, 10); $i++): 
                ?>
                    <a href="?pagina=<?php echo $i . $url_params; ?>" class="w-10 h-10 flex items-center justify-center rounded-xl font-bold text-xs transition-all <?php echo ($i == $pagina_actual) ? 'oceanic-gradient text-white shadow-lg shadow-oceanic/20' : 'bg-white/5 text-slate-500 hover:bg-white/10 hover:text-slate-300'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                <?php if($total_paginas > 10): ?>
                    <span class="w-10 h-10 flex items-center justify-center text-slate-700">...</span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

    </div>
</main>

</div> <!-- Cierre wrapper -->

<?php include('includes/scripts.php'); ?>
<?php include('includes/footer.php'); ?>
