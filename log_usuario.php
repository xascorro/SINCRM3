<?php
include('security.php'); 
include('includes/header.php');
include('includes/navbar.php');

$club_filter = "";
$club_nombre = "";
if (isset($_SESSION['club']) && $_SESSION['club'] > 0) {
    $c_id = intval($_SESSION['club']);
    $q_c = mysqli_query($connection, "SELECT nombre, nombre_corto FROM clubes WHERE id = '$c_id'");
    if($c_data = mysqli_fetch_assoc($q_c)) {
        $club_filter = $c_data['nombre_corto'];
        $club_nombre = $c_data['nombre'];
    }
}
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-surface">
    
    <?php include('includes/topbar.php'); ?>

    <!-- Contenido de la Página -->
    <div class="px-3 py-4 sm:p-6 md:p-10 max-w-6xl mx-auto w-full font-lexend text-primary">
        
        <!-- Header de Sección -->
        <div class="mb-6 sm:mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-6">
            <div>
                <h1 class="text-xl sm:text-2xl md:text-3xl font-black text-slate-800 tracking-tight flex items-center gap-2.5 sm:gap-3">
                    <span class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shadow-xs border border-blue-100">
                        <i class="fas fa-history text-sm sm:text-base"></i>
                    </span>
                    Mi Actividad
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 font-medium mt-1">Cronología de acciones y eventos registrados en tu cuenta o club.</p>
            </div>
            <div class="flex items-center gap-2.5 self-start sm:self-auto">
                <a href="perfil.php" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-700 text-xs sm:text-sm font-bold rounded-xl hover:bg-slate-50 active:scale-[0.98] transition-all shadow-xs">
                    <i class="fas fa-arrow-left text-xs"></i> Perfil
                </a>
                <button onclick="window.location.reload()" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 text-white text-xs sm:text-sm font-bold rounded-xl hover:bg-blue-700 active:scale-[0.98] transition-all shadow-md shadow-blue-500/20">
                    <i class="fas fa-rotate text-xs"></i> Actualizar
                </button>
            </div>
        </div>

        <?php
        $archivo_log = './log/log.txt';
        $user_email = $_SESSION['email'] ?? '';
        $pagina_actual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
        $nivel_filtro = isset($_GET['nivel']) ? strtoupper(trim($_GET['nivel'])) : 'TODOS';
        $lineas_por_pagina = 25;

        $lineas_usuario = [];
        $count_success = 0;
        $count_error = 0;
        $count_warn = 0;

        if (file_exists($archivo_log)) {
            $todas_lineas = @file($archivo_log) ?: [];
            
            // Filtrar líneas correspondientes al usuario o su club
            foreach ($todas_lineas as $line) {
                $match_user = (!empty($user_email) && strpos($line, $user_email) !== false);
                $match_club = (!empty($club_filter) && strpos($line, $club_filter) !== false);
                
                if ($match_user || $match_club) {
                    if (strpos($line, '[SUCCESS]') !== false) $count_success++;
                    if (strpos($line, '[ERROR]') !== false) $count_error++;
                    if (strpos($line, '[WARNING]') !== false || strpos($line, '[SECURITY]') !== false) $count_warn++;

                    // Aplicar filtro por nivel si no es TODOS
                    if ($nivel_filtro === 'TODOS' || strpos($line, "[$nivel_filtro]") !== false) {
                        $lineas_usuario[] = $line;
                    }
                }
            }
        }

        $lineas_usuario = array_reverse($lineas_usuario);
        $total_lineas = count($lineas_usuario);
        $total_paginas = max(1, ceil($total_lineas / $lineas_por_pagina));
        $inicio = ($pagina_actual - 1) * $lineas_por_pagina;
        $pagina_lineas = array_slice($lineas_usuario, $inicio, $lineas_por_pagina);
        ?>

        <!-- KPIs Resumen de Actividad -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-6 mb-6 sm:mb-8">
            <div class="bg-white p-3.5 sm:p-5 rounded-2xl shadow-sm border border-slate-200 border-l-4 border-l-blue-600">
                <span class="text-[9px] font-black uppercase text-slate-400 tracking-widest block">Total Registros</span>
                <span class="text-xl sm:text-2xl font-black text-slate-800 tabular-nums block mt-1"><?php echo count($lineas_usuario); ?></span>
                <span class="text-[10px] text-slate-400 font-medium truncate block">Acciones auditadas</span>
            </div>
            <div class="bg-white p-3.5 sm:p-5 rounded-2xl shadow-sm border border-slate-200 border-l-4 border-l-emerald-500">
                <span class="text-[9px] font-black uppercase text-slate-400 tracking-widest block">Completadas</span>
                <span class="text-xl sm:text-2xl font-black text-emerald-600 tabular-nums block mt-1"><?php echo $count_success; ?></span>
                <span class="text-[10px] text-emerald-500 font-medium truncate block">Operaciones con éxito</span>
            </div>
            <div class="bg-white p-3.5 sm:p-5 rounded-2xl shadow-sm border border-slate-200 border-l-4 border-l-rose-500">
                <span class="text-[9px] font-black uppercase text-slate-400 tracking-widest block">Errores</span>
                <span class="text-xl sm:text-2xl font-black text-rose-600 tabular-nums block mt-1"><?php echo $count_error; ?></span>
                <span class="text-[10px] text-rose-500 font-medium truncate block">Fallos o excepciones</span>
            </div>
            <div class="bg-white p-3.5 sm:p-5 rounded-2xl shadow-sm border border-slate-200 border-l-4 border-l-purple-500">
                <span class="text-[9px] font-black uppercase text-slate-400 tracking-widest block">Club Vinculado</span>
                <span class="text-sm sm:text-base font-black text-purple-700 truncate block mt-1">
                    <?php echo $club_filter ?: 'Individual'; ?>
                </span>
                <span class="text-[10px] text-slate-400 font-medium truncate block"><?php echo $club_nombre ?: 'Sin club asignado'; ?></span>
            </div>
        </div>

        <!-- Barra de Búsqueda y Chips de Filtro -->
        <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-6 shadow-sm border border-slate-200/80 mb-6 sm:mb-8">
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
                <!-- Buscador en tiempo real -->
                <div class="relative flex-1">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </span>
                    <input type="text" id="logSearchInput" placeholder="Filtrar por texto en esta página..." 
                           class="w-full pl-9 pr-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:border-blue-500 focus:bg-white transition-all outline-none"
                           onkeyup="filterLogRows()">
                </div>

                <!-- Chips de Nivel -->
                <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-1 sm:pb-0">
                    <?php
                    $niveles = [
                        'TODOS' => ['label' => 'Todos', 'icon' => 'fa-list'],
                        'SUCCESS' => ['label' => 'Éxito', 'icon' => 'fa-check'],
                        'ERROR' => ['label' => 'Errores', 'icon' => 'fa-triangle-exclamation'],
                        'WARNING' => ['label' => 'Avisos', 'icon' => 'fa-shield'],
                        'INFO' => ['label' => 'Info', 'icon' => 'fa-info']
                    ];
                    foreach ($niveles as $key => $cfg):
                        $active = ($nivel_filtro === $key);
                    ?>
                        <a href="?nivel=<?php echo $key; ?>&pagina=1" 
                           class="px-3 py-1.5 rounded-xl text-[11px] font-black uppercase tracking-tight flex items-center gap-1.5 whitespace-nowrap transition-all <?php echo $active ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200/80'; ?>">
                            <i class="fas <?php echo $cfg['icon']; ?> text-[9px]"></i> <?php echo $cfg['label']; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Contenedor Principal de Logs -->
        <div class="bg-white rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden mb-10">
            <div class="px-5 py-4 bg-slate-50/50 border-b border-slate-100 flex items-center justify-between">
                <span class="text-xs font-black uppercase text-slate-700 tracking-wider flex items-center gap-2">
                    <i class="fas fa-timeline text-blue-500"></i> Cronología de Eventos
                </span>
                <span class="text-[10px] font-bold text-slate-400 tabular-nums">
                    Mostrando <?php echo count($pagina_lineas); ?> de <?php echo $total_lineas; ?> eventos
                </span>
            </div>

            <div class="divide-y divide-slate-100" id="logRowsContainer">
                <?php if (empty($pagina_lineas)): ?>
                    <div class="py-16 sm:py-24 text-center px-4">
                        <div class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-3 text-slate-300">
                            <i class="fas fa-inbox text-2xl"></i>
                        </div>
                        <h3 class="text-sm font-black text-slate-700">Sin registros de actividad</h3>
                        <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">No se encontraron eventos registrados para los filtros seleccionados.</p>
                        <a href="log_usuario.php" class="inline-block mt-4 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all">
                            Restablecer Filtros
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($pagina_lineas as $linea): 
                        if (preg_match('/^\[(.*?)\] \[(.*?)\] \[(.*?)\] \[(.*?)\] (.*)$/', $linea, $matches)):
                            $fecha_raw = $matches[1];
                            $nivel = $matches[2];
                            $usuario_log = $matches[3];
                            $ip = $matches[4];
                            $mensaje = $matches[5];

                            $timestamp = strtotime($fecha_raw);
                            $fecha_h = date("d/m/Y", $timestamp);
                            $hora_h = date("H:i:s", $timestamp);

                            // Estilos por nivel
                            $badge_bg = "bg-blue-50 text-blue-700 border-blue-200";
                            $icon = "fa-info";
                            if($nivel == 'SUCCESS') { $badge_bg = "bg-emerald-50 text-emerald-700 border-emerald-200"; $icon = "fa-check"; }
                            if($nivel == 'ERROR') { $badge_bg = "bg-rose-50 text-rose-700 border-rose-200"; $icon = "fa-triangle-exclamation"; }
                            if($nivel == 'WARNING') { $badge_bg = "bg-amber-50 text-amber-700 border-amber-200"; $icon = "fa-shield"; }
                            if($nivel == 'SECURITY') { $badge_bg = "bg-purple-50 text-purple-700 border-purple-200"; $icon = "fa-lock"; }
                    ?>
                        <div class="log-entry-row p-3.5 sm:p-5 hover:bg-slate-50/70 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4"
                             data-search="<?php echo htmlspecialchars(strtolower($fecha_h.' '.$hora_h.' '.$nivel.' '.$mensaje.' '.$usuario_log)); ?>">
                            
                            <div class="flex items-start sm:items-center gap-3 flex-1 min-w-0">
                                <!-- Icono de nivel -->
                                <div class="shrink-0 w-8 h-8 rounded-xl <?php echo $badge_bg; ?> border flex items-center justify-center text-xs mt-0.5 sm:mt-0 shadow-2xs">
                                    <i class="fas <?php echo $icon; ?>"></i>
                                </div>

                                <!-- Mensaje y detalles -->
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs sm:text-sm font-bold text-slate-800 leading-snug break-words">
                                        <?php echo htmlspecialchars($mensaje); ?>
                                    </p>
                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1 text-[10px] text-slate-400">
                                        <span class="flex items-center gap-1 font-bold">
                                            <i class="fas fa-user-circle opacity-60"></i> <?php echo htmlspecialchars($usuario_log); ?>
                                        </span>
                                        <?php if ($ip && $ip !== '127.0.0.1' && $ip !== '::1'): ?>
                                            <span class="font-mono tabular-nums opacity-75">
                                                <i class="fas fa-network-wired text-[9px] mr-0.5"></i> <?php echo htmlspecialchars($ip); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if($club_filter && strpos($mensaje, $club_filter) !== false): ?>
                                            <span class="px-1.5 py-0.2 bg-indigo-50 text-indigo-700 font-black rounded border border-indigo-100 uppercase text-[9px]">
                                                <?php echo htmlspecialchars($club_filter); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Fecha y Hora -->
                            <div class="shrink-0 text-left sm:text-right flex sm:flex-col items-center sm:items-end justify-between sm:justify-center border-t sm:border-t-0 pt-2 sm:pt-0 border-slate-100 text-[11px] tabular-nums font-mono">
                                <span class="text-slate-800 font-bold"><?php echo $hora_h; ?></span>
                                <span class="text-slate-400 text-[10px]"><?php echo $fecha_h; ?></span>
                            </div>

                        </div>
                    <?php endif; endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Paginación -->
            <?php if ($total_paginas > 1): ?>
            <div class="px-4 sm:px-6 py-4 bg-slate-50/50 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-xs font-bold text-slate-500 tabular-nums">
                    Página <strong class="text-slate-800"><?php echo $pagina_actual; ?></strong> de <strong class="text-slate-800"><?php echo $total_paginas; ?></strong>
                </p>
                <div class="flex items-center gap-1.5">
                    <?php if($pagina_actual > 1): ?>
                        <a href="?nivel=<?php echo urlencode($nivel_filtro); ?>&pagina=<?php echo $pagina_actual - 1; ?>" 
                           class="w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-600 flex items-center justify-center hover:bg-slate-50 active:scale-[0.98] transition-all shadow-2xs">
                            <i class="fas fa-chevron-left text-xs"></i>
                        </a>
                    <?php endif; ?>

                    <?php 
                    $start_p = max(1, $pagina_actual - 2);
                    $end_p = min($total_paginas, $pagina_actual + 2);
                    for ($i = $start_p; $i <= $end_p; $i++): 
                    ?>
                        <a href="?nivel=<?php echo urlencode($nivel_filtro); ?>&pagina=<?php echo $i; ?>" 
                           class="w-9 h-9 rounded-xl flex items-center justify-center text-xs font-black transition-all <?php echo ($i == $pagina_actual) ? 'bg-blue-600 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if($pagina_actual < $total_paginas): ?>
                        <a href="?nivel=<?php echo urlencode($nivel_filtro); ?>&pagina=<?php echo $pagina_actual + 1; ?>" 
                           class="w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-600 flex items-center justify-center hover:bg-slate-50 active:scale-[0.98] transition-all shadow-2xs">
                            <i class="fas fa-chevron-right text-xs"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

    </div>
</main>

<script>
function filterLogRows() {
    const input = document.getElementById('logSearchInput');
    const filter = input.value.toLowerCase().trim();
    const rows = document.querySelectorAll('.log-entry-row');
    
    rows.forEach(row => {
        const searchAttr = row.getAttribute('data-search') || row.textContent.toLowerCase();
        row.style.display = searchAttr.indexOf(filter) > -1 ? "" : "none";
    });
}
</script>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
