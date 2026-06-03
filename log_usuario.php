<?php
include('security.php'); 
include('includes/header.php');
include('includes/navbar.php');

$club_filter = "";
if (isset($_SESSION['club']) && $_SESSION['club'] > 0) {
    $c_id = $_SESSION['club'];
    $q_c = mysqli_query($connection, "SELECT nombre_corto FROM clubes WHERE id = '$c_id'");
    if($c_data = mysqli_fetch_assoc($q_c)) {
        $club_filter = $c_data['nombre_corto'];
    }
}
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
                    <span class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center text-slate-500 shadow-sm border border-slate-100"><i class="fas fa-history"></i></span>
                    Mi Actividad
                </h1>
                <p class="text-slate-500 font-medium">Cronología de acciones realizadas por tu equipo.</p>
            </div>
            <div class="flex gap-3">
                <button onclick="window.location.reload()" class="px-6 py-3 bg-white text-slate-600 font-black uppercase text-xs tracking-widest rounded-2xl border border-slate-200 hover:bg-slate-50 transition-all flex items-center gap-2 shadow-sm">
                    <i class="fas fa-sync-alt text-xs"></i> Actualizar Registro
                </button>
            </div>
        </div>

        <!-- Visor de Actividad (Estilo Usuario) -->
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-2 md:p-4">
                <div class="divide-y divide-slate-100">
                    <?php
                    $archivo_log = './log/log.txt';
                    $user_email = $_SESSION['email'];
                    $pagina_actual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
                    $lineas_por_pagina = 30;

                    if (file_exists($archivo_log)) {
                        $lineas = file($archivo_log);
                        
                        // FILTRO DUAL: Email O Nombre del Club
                        $lineas_usuario = array_filter($lineas, function($line) use ($user_email, $club_filter) {
                            $match_user = strpos($line, $user_email) !== false;
                            $match_club = ($club_filter !== "" && strpos($line, $club_filter) !== false);
                            return $match_user || $match_club;
                        });

                        $lineas_usuario = array_reverse($lineas_usuario);
                        $total_lineas = count($lineas_usuario);
                        $total_paginas = ceil($total_lineas / $lineas_por_pagina);
                        
                        $inicio = ($pagina_actual - 1) * $lineas_por_pagina;
                        $pagina_lineas = array_slice($lineas_usuario, $inicio, $lineas_por_pagina);

                        if (empty($pagina_lineas)) {
                            echo '<div class="py-24 text-center">
                                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-200">
                                        <i class="fas fa-search fa-2x"></i>
                                    </div>
                                    <p class="text-slate-400 font-bold italic">No se han encontrado registros de actividad recientemente.</p>
                                  </div>';
                        }

                        foreach ($pagina_lineas as $linea) {
                            // Parsear línea: [FECHA] [NIVEL] [USUARIO] [IP] MENSAJE
                            if (preg_match('/^\[(.*?)\] \[(.*?)\] \[(.*?)\] \[(.*?)\] (.*)$/', $linea, $matches)) {
                                $fecha_raw = $matches[1];
                                $nivel = $matches[2];
                                $usuario_log = $matches[3];
                                $ip = $matches[4];
                                $mensaje = $matches[5];

                                // Formatear fecha humana
                                $timestamp = strtotime($fecha_raw);
                                $fecha_h = date("d/m/Y", $timestamp);
                                $hora_h = date("H:i", $timestamp);

                                // Estilos por nivel
                                $bg_nivel = "bg-slate-100 text-slate-500";
                                $icon = "fa-info-circle";
                                if($nivel == 'SUCCESS') { $bg_nivel = "bg-emerald-50 text-emerald-600"; $icon = "fa-check-circle"; }
                                if($nivel == 'ERROR') { $bg_nivel = "bg-red-50 text-red-600"; $icon = "fa-exclamation-circle"; }
                                if($nivel == 'WARNING') { $bg_nivel = "bg-amber-50 text-amber-600"; $icon = "fa-triangle-exclamation"; }
                                if($nivel == 'SECURITY') { $bg_nivel = "bg-purple-50 text-purple-600"; $icon = "fa-shield-halved"; }
                    ?>
                                <div class="flex flex-col lg:flex-row lg:items-center gap-4 lg:gap-10 p-6 hover:bg-slate-50/50 transition-colors group">
                                    
                                    <!-- Header de Fila (Fecha, Hora e Icono en Mobile) -->
                                    <div class="flex flex-row items-center justify-between lg:justify-start gap-4 shrink-0 lg:w-48">
                                        <div class="flex flex-row lg:flex-col items-center lg:items-start gap-3 lg:gap-0">
                                            <p class="text-[10px] md:text-[11px] font-black text-slate-400 uppercase tracking-tighter"><?php echo $fecha_h; ?></p>
                                            <p class="text-sm font-black text-slate-700 bg-slate-100 lg:bg-transparent px-2 py-0.5 lg:px-0 rounded-md"><?php echo $hora_h; ?></p>
                                        </div>

                                        <!-- Icono visible solo en móvil en esta fila -->
                                        <div class="lg:hidden shrink-0 w-10 h-10 rounded-xl <?php echo $bg_nivel; ?> flex items-center justify-center text-sm shadow-sm">
                                            <i class="fas <?php echo $icon; ?>"></i>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-4 flex-1">
                                        <!-- Icono visible solo en desktop -->
                                        <div class="hidden lg:flex shrink-0 w-11 h-11 rounded-2xl <?php echo $bg_nivel; ?> flex items-center justify-center text-base shadow-sm transition-transform group-hover:scale-110">
                                            <i class="fas <?php echo $icon; ?>"></i>
                                        </div>

                                        <!-- Mensaje y Detalles -->
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm md:text-base font-bold text-slate-800 leading-snug mb-1 break-words">
                                                <?php echo htmlspecialchars($mensaje); ?>
                                            </p>
                                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
                                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-tighter flex items-center gap-1.5">
                                                    <i class="fas fa-user-circle opacity-50"></i> <?php echo htmlspecialchars($usuario_log); ?>
                                                </span>
                                                <?php if($club_filter && strpos($mensaje, $club_filter) !== false): ?>
                                                <span class="text-[10px] font-black text-blue-500 uppercase tracking-tighter bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-100/50">
                                                    Gestión de Club
                                                </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Flecha decorativa -->
                                    <div class="hidden lg:block opacity-0 group-hover:opacity-100 transition-opacity pr-2">
                                        <i class="fas fa-chevron-right text-slate-300 text-xs"></i>
                                    </div>
                                </div>
                    <?php
                            }
                        }
                    } else {
                        echo '<div class="py-24 text-center text-slate-400 font-bold italic">Archivo de registro no disponible.</div>';
                    }
                    ?>
                </div>
            </div>

            <!-- Paginación Elegante -->
            <?php if (isset($total_paginas) && $total_paginas > 1): ?>
            <div class="px-8 py-6 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Página <?php echo $pagina_actual; ?> de <?php echo $total_paginas; ?></p>
                <div class="flex gap-2">
                    <?php 
                    $start_p = max(1, $pagina_actual - 1);
                    $end_p = min($total_paginas, $pagina_actual + 1);
                    
                    if($pagina_actual > 1): ?>
                        <a href="?pagina=<?php echo $pagina_actual - 1; ?>" class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:bg-blue-600 hover:text-white hover:border-blue-600 transition-all shadow-sm"><i class="fas fa-chevron-left text-xs"></i></a>
                    <?php endif; ?>

                    <?php for ($i = $start_p; $i <= $end_p; $i++): ?>
                        <a href="?pagina=<?php echo $i; ?>" class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-xs transition-all <?php echo ($i == $pagina_actual) ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : 'bg-white border border-slate-200 text-slate-400 hover:bg-slate-50'; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>

                    <?php if($pagina_actual < $total_paginas): ?>
                        <a href="?pagina=<?php echo $pagina_actual + 1; ?>" class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:bg-blue-600 hover:text-white hover:border-blue-600 transition-all shadow-sm"><i class="fas fa-chevron-right text-xs"></i></a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

    </div>
</main>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
