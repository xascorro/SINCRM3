<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');

// Traductor de meses para el diseño
$meses_es = [
    'Jan' => 'ENE', 'Feb' => 'FEB', 'Mar' => 'MAR', 'Apr' => 'ABR', 
    'May' => 'MAY', 'Jun' => 'JUN', 'Jul' => 'JUL', 'Aug' => 'AGO', 
    'Sep' => 'SEP', 'Oct' => 'OCT', 'Nov' => 'NOV', 'Dec' => 'DIC'
];
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-surface">
    
    <?php include('includes/topbar.php'); ?>

    <!-- Contenido del Dashboard -->
    <div class="px-3 py-4 sm:p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend">
        
        <?php include('includes/alertas_v4.php'); ?>

        <?php
        $query_usr = "SELECT * FROM usuarios where id=".$_SESSION['id_usario']."";
        $query_run_usr = mysqli_query($connection, $query_usr);
        $usuario = mysqli_fetch_array($query_run_usr);
        ?>

        <!-- Page Heading -->
        <div class="mb-6 sm:mb-10">
            <a href="index.php" class="no-underline group/dash inline-block">
                <h1 class="text-3xl sm:text-4xl md:text-5xl font-black text-slate-800 tracking-tighter mb-1 sm:mb-2 italic text-primary group-hover/dash:text-blue-600 transition-colors">Dashboard</h1>
            </a>
            <p class="text-sm sm:text-base text-slate-500 font-medium">Panel central de gestión SINCRM</p>
        </div>

        <!-- FILA 1: Tarjetas Informativas (Grid 3 columnas equilibrado) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 lg:gap-8 mb-8 sm:mb-12">
            
            <!-- Mi Club (SÓLO CLUBES) -->
            <?php if($_SESSION['id_rol'] == 5): ?>
            <div class="bg-white p-5 sm:p-7 rounded-2xl sm:rounded-[2.5rem] shadow-sm border border-slate-200 border-t-[8px] sm:border-t-[10px] border-t-red-500 group hover:shadow-xl transition-all">
                <div class="flex justify-between items-start mb-4 sm:mb-6">
                    <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl sm:rounded-2xl bg-red-50 flex items-center justify-center text-red-600 transition-all group-hover:scale-110 shadow-sm"><i class="fas fa-flag text-xl sm:text-2xl"></i></div>
                    <span class="text-[10px] sm:text-[11px] font-black uppercase text-slate-400 tracking-[0.2em] italic">Mi Club</span>
                </div>
                <?php
                $query = "SELECT * FROM clubes where id=".$_SESSION['club']."";
                $query_run = mysqli_query($connection,$query);
                $club = mysqli_fetch_array($query_run);
                ?>
                <div class="space-y-1">
                    <h3 class="text-xl sm:text-2xl font-black text-slate-800 truncate"><?php echo $club['nombre']?></h3>
                    <p class="text-xs sm:text-sm font-bold text-slate-400 italic uppercase tracking-tighter">Nombre corto: <?php echo $club['nombre_corto']?> | RFEN: <?php echo $club['codigo']?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Mi Equipo (PARA TODOS LOS ROLES) -->
            <a href="mi_equipo.php" class="bg-white p-5 sm:p-7 rounded-2xl sm:rounded-[2.5rem] shadow-sm border border-slate-200 border-t-[8px] sm:border-t-[10px] border-t-emerald-500 group hover:shadow-xl active:scale-[0.99] transition-all no-underline block">
                <div class="flex justify-between items-start mb-4 sm:mb-6">
                    <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl sm:rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 transition-all group-hover:scale-110 shadow-sm"><i class="fas fa-users-gear text-xl sm:text-2xl"></i></div>
                    <span class="text-[10px] sm:text-[11px] font-black uppercase text-slate-400 tracking-[0.2em] italic">Mi Equipo</span>
                </div>
                <?php
                if($_SESSION['id_rol'] == 5) {
                    $q_equipo = "SELECT username FROM usuarios WHERE club = '".$_SESSION['club']."' AND activo = 1 ORDER BY username ASC";
                } else {
                    $q_equipo = "SELECT username FROM usuarios WHERE id_rol = '".$_SESSION['id_rol']."' AND activo = 1 ORDER BY username ASC";
                }
                $res_equipo = mysqli_query($connection, $q_equipo);
                $total_miembros = mysqli_num_rows($res_equipo);
                $otros_nombres = [];
                $shown = 0;
                $show_limit = 3;
                ?>
                <div class="flex items-center justify-between mb-3 sm:mb-4">
                    <h3 class="text-2xl sm:text-4xl font-black text-slate-800 leading-none tabular-nums"><?php echo $total_miembros; ?> <span class="text-xs sm:text-sm text-slate-400 uppercase tracking-widest italic font-bold">Censo</span></h3>
                    
                    <!-- Avatar Stack con Tooltip Rediseñado -->
                    <div class="flex -space-x-3 sm:-space-x-4 relative group/stack">
                        <?php 
                        mysqli_data_seek($res_equipo, 0);
                        while($m = mysqli_fetch_assoc($res_equipo)):
                            if($m['username'] != $usuario['username']) $otros_nombres[] = $m['username'];
                            if($shown < $show_limit):
                                $inicial = strtoupper(substr($m['username'], 0, 1));
                        ?>
                            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full border-2 border-white flex items-center justify-center text-[10px] sm:text-xs font-black shadow-md oceanic-gradient text-white">
                                <?php echo $inicial; ?>
                            </div>
                        <?php 
                                $shown++;
                            endif;
                        endwhile; 
                        ?>

                        <!-- Tooltip Elegante y Redondeado -->
                        <?php if(!empty($otros_nombres)): ?>
                        <div class="absolute bottom-full mb-4 right-0 opacity-0 group-hover/stack:opacity-100 transition-all duration-500 pointer-events-none translate-y-2 group-hover/stack:translate-y-0 z-50">
                            <div class="bg-slate-900/95 backdrop-blur-md text-white text-[10px] font-black py-3 px-5 rounded-[1.5rem] shadow-2xl whitespace-nowrap border border-white/10 flex items-center gap-2">
                                <i class="fas fa-users text-blue-400"></i>
                                <?php 
                                echo implode(" <span class='text-slate-600'>•</span> ", array_slice($otros_nombres, 0, 6)); 
                                if(count($otros_nombres) > 6) echo " <span class='text-slate-600'>•</span> ...";
                                ?>
                            </div>
                            <!-- Flecha del Tooltip -->
                            <div class="w-3 h-3 bg-slate-900/95 rotate-45 ml-auto mr-8 -mt-1.5 border-r border-b border-white/10 shadow-xl"></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="mt-3 sm:mt-4 pt-3 sm:pt-4 border-t border-slate-100">
                    <p class="text-[9px] sm:text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 sm:mb-2">Compartes equipo con:</p>
                    <p class="text-xs sm:text-sm font-bold text-slate-600 italic leading-snug">
                        <?php 
                        if(empty($otros_nombres)) echo "Gestión individual";
                        else {
                            echo implode(", ", array_slice($otros_nombres, 0, 4)); 
                            if(count($otros_nombres) > 4) echo " y ".(count($otros_nombres)-4)." más...";
                        }
                        ?>
                    </p>
                </div>
            </a>

            <!-- Nadadoras (OCULTO PARA JUECES) -->
            <?php if($_SESSION['id_rol'] != 4): ?>
            <a href="nadadoras.php" class="bg-white p-5 sm:p-7 rounded-2xl sm:rounded-[2.5rem] shadow-sm border border-slate-200 border-t-[8px] sm:border-t-[10px] border-t-blue-600 group hover:shadow-xl active:scale-[0.99] transition-all no-underline block">
                <div class="flex justify-between items-start mb-4 sm:mb-6">
                    <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl sm:rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 transition-all group-hover:scale-110 shadow-sm"><i class="fas fa-swimmer text-xl sm:text-2xl"></i></div>
                    <span class="text-[10px] sm:text-[11px] font-black uppercase text-slate-400 tracking-[0.2em] italic">Censo Técnico</span>
                </div>
                <?php
                if($_SESSION['id_rol'] == 5) { $condicion_club = ' and club='.$_SESSION['club'].' '; } else { $condicion_club = ''; }
                $query = "SELECT id FROM nadadoras where activo = 1 $condicion_club";
                $query_run = mysqli_query($connection,$query);
                $numero_nadadoras_activas = mysqli_num_rows($query_run);
                $query = "SELECT id FROM nadadoras where activo = 0 $condicion_club";
                $query_run = mysqli_query($connection,$query);
                $numero_nadadoras_baja = mysqli_num_rows($query_run);
                ?>
                <h3 class="text-2xl sm:text-4xl font-black text-slate-800 leading-none mb-2 tabular-nums"><?php echo $numero_nadadoras_activas?> <span class="text-xs sm:text-sm text-slate-400 uppercase tracking-widest italic font-bold">Nadadoras Activas</span></h3>
                <p class="text-xs font-bold text-orange-500 uppercase italic tracking-tighter tabular-nums"><?php echo $numero_nadadoras_baja?> nadadoras en historial</p>
            </a>
            <?php endif; ?>

            <!-- Competiciones (TERCERA TARJETA PARA ADMINS / SECRETARIOS / DELEGADOS) -->
            <?php if(in_array($_SESSION['id_rol'], [1, 2, 3])): 
                // Obtener temporada activa oficial de la competición activa o vigente
                $q_temp_kpi = mysqli_query($connection, "SELECT temporada FROM competiciones WHERE activo = 'si' LIMIT 1");
                $row_temp_kpi = ($q_temp_kpi) ? mysqli_fetch_assoc($q_temp_kpi) : null;
                $temporada_kpi = !empty($row_temp_kpi['temporada']) ? $row_temp_kpi['temporada'] : '2026-2027';

                $q_comp_kpi = "SELECT 
                    COUNT(*) as total, 
                    SUM(CASE WHEN fecha >= CURDATE() THEN 1 ELSE 0 END) as futuras,
                    SUM(CASE WHEN fecha < CURDATE() THEN 1 ELSE 0 END) as pasadas
                FROM competiciones WHERE temporada = '$temporada_kpi'";
                $res_comp_kpi = mysqli_query($connection, $q_comp_kpi);
                $comp_stats = mysqli_fetch_assoc($res_comp_kpi);
            ?>
            <a href="competiciones.php" class="bg-white p-5 sm:p-7 rounded-2xl sm:rounded-[2.5rem] shadow-sm border border-slate-200 border-t-[8px] sm:border-t-[10px] border-t-amber-500 group hover:shadow-xl active:scale-[0.99] transition-all no-underline block">
                <div class="flex justify-between items-start mb-4 sm:mb-6">
                    <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl sm:rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 transition-all group-hover:scale-110 shadow-sm"><i class="fas fa-trophy text-xl sm:text-2xl"></i></div>
                    <span class="text-[10px] sm:text-[11px] font-black uppercase text-slate-400 tracking-[0.2em] italic">Competiciones</span>
                </div>
                <h3 class="text-2xl sm:text-4xl font-black text-slate-800 leading-none mb-2 tabular-nums"><?php echo (int)($comp_stats['futuras'] ?? 0); ?> <span class="text-xs sm:text-sm text-slate-400 uppercase tracking-widest italic font-bold">Programadas</span></h3>
                <p class="text-xs font-bold text-slate-500 uppercase italic tracking-tighter tabular-nums"><?php echo (int)($comp_stats['total'] ?? 0); ?> en temporada (<?php echo (int)($comp_stats['pasadas'] ?? 0); ?> finalizadas)</p>
            </a>
            <?php endif; ?>

            <!-- BIAS Analizer (SÓLO PARA JUECES EN DASHBOARD) -->
            <?php if($_SESSION['id_rol'] == 4): ?>
            <a href="mi_auditoria.php" class="bg-white p-5 sm:p-7 rounded-2xl sm:rounded-[2.5rem] shadow-sm border border-slate-200 border-t-[8px] sm:border-t-[10px] border-t-blue-600 group hover:shadow-xl active:scale-[0.99] transition-all no-underline block">
                <div class="flex justify-between items-start mb-4 sm:mb-6">
                    <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl sm:rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 transition-all group-hover:scale-110 shadow-sm"><i class="fas fa-magnifying-glass-chart text-xl sm:text-2xl"></i></div>
                    <span class="text-[10px] sm:text-[11px] font-black uppercase text-slate-400 tracking-[0.2em] italic">Mi Auditoría</span>
                </div>
                <h3 class="text-2xl sm:text-4xl font-black text-slate-800 leading-none mb-2 italic">AQUA BIAS</h3>
                <p class="text-xs font-bold text-blue-500 uppercase italic tracking-tighter">Consulta tu rendimiento técnico oficial</p>
            </a>

            <a href="ranking_jueces.php" class="bg-white p-5 sm:p-7 rounded-2xl sm:rounded-[2.5rem] shadow-sm border border-slate-200 border-t-[8px] sm:border-t-[10px] border-t-amber-500 group hover:shadow-xl active:scale-[0.99] transition-all no-underline block">
                <div class="flex justify-between items-start mb-4 sm:mb-6">
                    <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl sm:rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 transition-all group-hover:scale-110 shadow-sm"><i class="fas fa-ranking-star text-xl sm:text-2xl"></i></div>
                    <span class="text-[10px] sm:text-[11px] font-black uppercase text-slate-400 tracking-[0.2em] italic">Global</span>
                </div>
                <h3 class="text-2xl sm:text-4xl font-black text-slate-800 leading-none mb-2 italic">RANKING</h3>
                <p class="text-xs font-bold text-amber-500 uppercase italic tracking-tighter">Comparativa anónima de calidad técnica</p>
            </a>
            <?php endif; ?>

        </div>

        <!-- SECCIÓN: TAREAS PENDIENTES (SÓLO ADMIN Y SI HAY TAREAS ACTIVAS) -->
        <?php if($_SESSION['id_rol'] == 1): ?>
        <?php
        // Obtener avisos silenciados para este usuario
        $user_id = $_SESSION['id_usario'];
        $q_sils = "SELECT tipo_aviso FROM avisos_silenciados WHERE id_usuario = '$user_id' AND silencio_hasta > NOW()";
        $res_sils = mysqli_query($connection, $q_sils);
        $silenciados = [];
        while($s = mysqli_fetch_assoc($res_sils)) { $silenciados[] = $s['tipo_aviso']; }

        // Función auxiliar para renderizar el botón de silenciar
        if (!function_exists('renderSilenciar')) {
            function renderSilenciar($tipo) {
                return '
                <div class="relative inline-block text-left ml-2" onclick="event.preventDefault(); event.stopPropagation();">
                    <button type="button" onclick="toggleSilencioMenu(\''.$tipo.'\')" class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 hover:bg-slate-200 hover:text-slate-600 transition-all active:scale-[0.95]">
                        <i class="fas fa-clock text-xs"></i>
                    </button>
                    <div id="menu-'.$tipo.'" class="hidden absolute right-0 mt-2 w-48 rounded-2xl bg-white shadow-2xl border border-slate-100 z-[100] p-2 overflow-hidden animate-fade-in-down">
                        <p class="text-[9px] font-black uppercase text-slate-400 px-3 py-2 border-b border-slate-50 mb-1 italic">Pausar Aviso</p>
                        <button onclick="silenciarAviso(\''.$tipo.'\', 1, 0)" class="w-full text-left px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition-all flex items-center gap-2"><i class="fas fa-hourglass-start opacity-50"></i> 1 hora</button>
                        <button onclick="silenciarAviso(\''.$tipo.'\', 24, 0)" class="w-full text-left px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition-all flex items-center gap-2"><i class="fas fa-calendar-day opacity-50"></i> 1 día</button>
                        <button onclick="silenciarAviso(\''.$tipo.'\', 72, 0)" class="w-full text-left px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition-all flex items-center gap-2"><i class="fas fa-calendar-days opacity-50"></i> 3 días</button>
                        <button onclick="silenciarAviso(\''.$tipo.'\', 168, 0)" class="w-full text-left px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition-all flex items-center gap-2"><i class="fas fa-moon opacity-50"></i> 1 semana</button>
                        <div class="h-px bg-slate-50 my-1"></div>
                        <button onclick="silenciarAviso(\''.$tipo.'\', 87600, 1)" class="w-full text-left px-4 py-2.5 text-xs font-black text-red-500 hover:bg-red-50 rounded-xl transition-all flex items-center gap-2 uppercase italic"><i class="fas fa-trash-can opacity-50"></i> Descartar</button>
                    </div>
                </div>';
            }
        }

        $task_cards_html = [];

        // 1. Usuarios pendientes de aprobación
        $tipo = 'REGISTROS_PENDIENTES';
        if(!in_array($tipo, $silenciados)):
            $q_pend = "SELECT COUNT(*) as total, MIN(creado) as mas_antiguo FROM usuarios WHERE id_rol = 6 AND activo = 1";
            $res_pend = mysqli_query($connection, $q_pend);
            $pend_data = mysqli_fetch_assoc($res_pend);
            $pend_users = $pend_data['total'] ?? 0;
            if($pend_users > 0):
                ob_start();
                ?>
                <div class="relative group">
                    <a href="usuarios.php" class="block bg-white p-4 sm:p-6 rounded-2xl sm:rounded-[2rem] border border-slate-200 border-l-[6px] sm:border-l-[8px] border-l-purple-500 shadow-sm hover:shadow-md active:scale-[0.99] transition-all no-underline">
                        <div class="flex justify-between items-start mb-3 sm:mb-4">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600 group-hover:scale-110 transition-all"><i class="fas fa-user-clock text-lg sm:text-xl"></i></div>
                            <div class="flex flex-col items-end">
                                <span class="text-[9px] font-black uppercase text-slate-400 tracking-widest tabular-nums">Desde <?php echo date("d-m", strtotime($pend_data['mas_antiguo'])); ?></span>
                                <?php echo renderSilenciar($tipo); ?>
                            </div>
                        </div>
                        <h4 class="text-lg sm:text-xl font-black text-slate-800 leading-tight mb-1 sm:mb-2">Registros Pendientes</h4>
                        <p class="text-xs sm:text-sm font-bold text-slate-500 italic">Hay <span class="text-purple-600 font-black tabular-nums"><?php echo $pend_users; ?></span> usuarios esperando aprobación o configuración de rol.</p>
                    </a>
                </div>
                <?php
                $task_cards_html[] = ob_get_clean();
            endif;
        endif;

        // 1b. Jueces sin vincular
        $tipo = 'JUECES_SIN_VINCULAR';
        if(!in_array($tipo, $silenciados)):
            $q_no_vinc = "SELECT COUNT(*) as total FROM usuarios WHERE id_rol = 4 AND (id_juez_v3 IS NULL OR id_juez_v3 = 0) AND activo = 1";
            $res_no_vinc = mysqli_query($connection, $q_no_vinc);
            $no_vinc_data = mysqli_fetch_assoc($res_no_vinc);
            $no_vinc_users = $no_vinc_data['total'] ?? 0;
            if($no_vinc_users > 0):
                ob_start();
                ?>
                <div class="relative group">
                    <a href="usuarios.php" class="block bg-white p-4 sm:p-6 rounded-2xl sm:rounded-[2rem] border border-slate-200 border-l-[6px] sm:border-l-[8px] border-l-amber-400 shadow-sm hover:shadow-md active:scale-[0.99] transition-all no-underline">
                        <div class="flex justify-between items-start mb-3 sm:mb-4">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-amber-50 flex items-center justify-center text-amber-500 group-hover:scale-110 transition-all"><i class="fas fa-link-slash text-lg sm:text-xl"></i></div>
                            <div class="flex flex-col items-end">
                                <span class="text-[9px] font-black uppercase text-slate-400 tracking-widest italic">Acción Requerida</span>
                                <?php echo renderSilenciar($tipo); ?>
                            </div>
                        </div>
                        <h4 class="text-lg sm:text-xl font-black text-slate-800 leading-tight mb-1 sm:mb-2">Vinculación de Jueces</h4>
                        <p class="text-xs sm:text-sm font-bold text-slate-500 italic">Hay <span class="text-amber-600 font-black tabular-nums"><?php echo $no_vinc_users; ?></span> usuarios con rol Juez sin perfil oficial vinculado.</p>
                    </a>
                </div>
                <?php
                $task_cards_html[] = ob_get_clean();
            endif;
        endif;

        // 2. Auditorías Bias Pendientes
        $q_bias = "SELECT id, nombre, fecha FROM competiciones WHERE fecha < CURDATE() AND id NOT IN (SELECT DISTINCT id_competicion FROM auditoria_jueces_stats) ORDER BY fecha DESC LIMIT 3";
        $res_bias = mysqli_query($connection, $q_bias);
        while($b = mysqli_fetch_assoc($res_bias)):
            $tipo = 'BIAS_PENDIENTE_'.$b['id'];
            if(!in_array($tipo, $silenciados)):
                ob_start();
                ?>
                <div class="relative group">
                    <a href="analisis_jueces.php?comp_id=<?php echo $b['id']; ?>" class="block bg-white p-4 sm:p-6 rounded-2xl sm:rounded-[2rem] border border-slate-200 border-l-[6px] sm:border-l-[8px] border-l-red-500 shadow-sm hover:shadow-md active:scale-[0.99] transition-all no-underline">
                        <div class="flex justify-between items-start mb-3 sm:mb-4">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-red-50 flex items-center justify-center text-red-600 group-hover:scale-110 transition-all"><i class="fas fa-magnifying-glass-chart text-lg sm:text-xl"></i></div>
                            <div class="flex flex-col items-end">
                                <span class="text-[9px] font-black uppercase text-slate-400 tracking-widest tabular-nums">Finalizó <?php echo date("d-m", strtotime($b['fecha'])); ?></span>
                                <?php echo renderSilenciar($tipo); ?>
                            </div>
                        </div>
                        <h4 class="text-lg sm:text-xl font-black text-slate-800 leading-tight mb-1 sm:mb-2">Falta Análisis BIAS</h4>
                        <p class="text-xs sm:text-sm font-bold text-slate-500 italic">La competición <span class="text-red-600 font-black"><?php echo $b['nombre']; ?></span> ha finalizado y no tiene auditoría grabada.</p>
                    </a>
                </div>
                <?php
                $task_cards_html[] = ob_get_clean();
            endif;
        endwhile;

        // 3. Plazos de Inscripción
        $q_insc = "SELECT id, nombre, fecha, dias_fin_inscripcion FROM competiciones WHERE DATE_SUB(fecha, INTERVAL (dias_fin_inscripcion - 1) DAY) <= CURDATE() AND DATE_SUB(fecha, INTERVAL dias_fin_inscripcion DAY) >= DATE_SUB(CURDATE(), INTERVAL 5 DAY) AND fecha >= CURDATE()";
        $res_insc = mysqli_query($connection, $q_insc);
        while($i = mysqli_fetch_assoc($res_insc)):
            $tipo = 'INSCRIPCION_CIERRE_'.$i['id'];
            if(!in_array($tipo, $silenciados)):
                $fecha_fin_insc = date("Y-m-d", strtotime("-".($i['dias_fin_inscripcion'] - 1)." days", strtotime($i['fecha'])));
                ob_start();
                ?>
                <div class="relative group">
                    <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-[2rem] border border-slate-200 border-l-[6px] sm:border-l-[8px] border-l-blue-500 shadow-sm hover:shadow-md transition-all group">
                        <div class="flex justify-between items-start mb-3 sm:mb-4">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 group-hover:scale-110 transition-all"><i class="fas fa-file-export text-lg sm:text-xl"></i></div>
                            <div class="flex flex-col items-end">
                                <span class="text-[9px] font-black uppercase text-slate-400 tracking-widest tabular-nums">Plazo: <?php echo date("d-m", strtotime($fecha_fin_insc)); ?></span>
                                <?php echo renderSilenciar($tipo); ?>
                            </div>
                        </div>
                        <h4 class="text-lg sm:text-xl font-black text-slate-800 leading-tight mb-1 sm:mb-2">Enviar Preinscripciones</h4>
                        <p class="text-xs sm:text-sm font-bold text-slate-500 italic">El plazo para <span class="text-blue-600 font-black"><?php echo $i['nombre']; ?></span> termina hoy. Es necesario enviar la documentación oficial.</p>
                    </div>
                </div>
                <?php
                $task_cards_html[] = ob_get_clean();
            endif;
        endwhile;

        // 4. Sorteos Próximos
        $q_sorteo_task = "SELECT id, nombre, fecha, dias_sorteo FROM competiciones WHERE DATE_SUB(fecha, INTERVAL dias_sorteo DAY) <= DATE_ADD(CURDATE(), INTERVAL 2 DAY) AND DATE_SUB(fecha, INTERVAL dias_sorteo DAY) >= CURDATE() AND fecha >= CURDATE()";
        $res_sorteo_task = mysqli_query($connection, $q_sorteo_task);
        while($s = mysqli_fetch_assoc($res_sorteo_task)):
            $tipo = 'SORTEO_PROXIMO_'.$s['id'];
            if(!in_array($tipo, $silenciados)):
                $fecha_sorteo_task = date("Y-m-d", strtotime("-".$s['dias_sorteo']." days", strtotime($s['fecha'])));
                ob_start();
                ?>
                <div class="relative group">
                    <a href="fases.php" class="block bg-white p-4 sm:p-6 rounded-2xl sm:rounded-[2rem] border border-slate-200 border-l-[6px] sm:border-l-[8px] border-l-emerald-500 shadow-sm hover:shadow-md active:scale-[0.99] transition-all no-underline">
                        <div class="flex justify-between items-start mb-3 sm:mb-4">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 group-hover:scale-110 transition-all"><i class="fas fa-dice text-lg sm:text-xl"></i></div>
                            <div class="flex flex-col items-end">
                                <span class="text-[9px] font-black uppercase text-slate-400 tracking-widest tabular-nums"><?php echo date("d-m", strtotime($fecha_sorteo_task)); ?></span>
                                <?php echo renderSilenciar($tipo); ?>
                            </div>
                        </div>
                        <h4 class="text-lg sm:text-xl font-black text-slate-800 leading-tight mb-1 sm:mb-2">Preparar Sorteo</h4>
                        <p class="text-xs sm:text-sm font-bold text-slate-500 italic">El sorteo de <span class="text-emerald-600 font-black"><?php echo $s['nombre']; ?></span> es inminente. Verifica las fases y el orden.</p>
                    </a>
                </div>
                <?php
                $task_cards_html[] = ob_get_clean();
            endif;
        endwhile;

        // Renderizado condicional: SÓLO si la sección no está silenciada y hay al menos 1 tarea activa
        if(!in_array('GLOBAL_TASKS', $silenciados) && !empty($task_cards_html)):
        ?>
        <div class="mb-8 sm:mb-12" id="tareas-pendientes-section">
            <div class="flex items-center justify-between border-l-[6px] sm:border-l-[8px] border-amber-500 pl-4 sm:pl-6 py-1.5 sm:py-2 mb-6 sm:mb-8">
                <h2 class="text-xl sm:text-2xl md:text-3xl font-black text-slate-800 uppercase tracking-tighter italic flex items-center gap-2 sm:gap-3">
                    Tareas Pendientes y Avisos
                    <span class="inline-flex items-center justify-center w-6 h-6 sm:w-8 sm:h-8 rounded-full bg-amber-100 text-amber-600 text-xs sm:text-sm animate-bounce"><i class="fas fa-bell"></i></span>
                </h2>
                
                <!-- Botón Silenciar Todo -->
                <div class="relative inline-block text-left">
                    <button onclick="toggleSilencioMenu('GLOBAL_TASKS')" class="flex items-center gap-1.5 sm:gap-2 px-3 py-1.5 sm:px-4 sm:py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl transition-all text-[9px] sm:text-[10px] font-black uppercase tracking-widest italic active:scale-[0.98]">
                        <i class="fas fa-clock"></i> <span class="hidden sm:inline">Silenciar</span> Todo
                    </button>
                    <div id="menu-GLOBAL_TASKS" class="hidden absolute right-0 mt-2 w-48 rounded-2xl bg-white shadow-2xl border border-slate-100 z-[100] p-2 overflow-hidden animate-fade-in-down">
                        <p class="text-[9px] font-black uppercase text-slate-400 px-3 py-2 border-b border-slate-50 mb-1 italic text-center">Pausar Todo el Panel</p>
                        <button onclick="silenciarAviso('GLOBAL_TASKS', 1, 0)" class="w-full text-left px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-amber-50 hover:text-amber-600 rounded-xl transition-all flex items-center gap-2"><i class="fas fa-hourglass-start opacity-50"></i> 1 hora</button>
                        <button onclick="silenciarAviso('GLOBAL_TASKS', 24, 0)" class="w-full text-left px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-amber-50 hover:text-amber-600 rounded-xl transition-all flex items-center gap-2"><i class="fas fa-calendar-day opacity-50"></i> 1 día</button>
                        <button onclick="silenciarAviso('GLOBAL_TASKS', 72, 0)" class="w-full text-left px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-amber-50 hover:text-amber-600 rounded-xl transition-all flex items-center gap-2"><i class="fas fa-calendar-days opacity-50"></i> 3 días</button>
                        <button onclick="silenciarAviso('GLOBAL_TASKS', 168, 0)" class="w-full text-left px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-amber-50 hover:text-amber-600 rounded-xl transition-all flex items-center gap-2"><i class="fas fa-moon opacity-50"></i> 1 semana</button>
                        <div class="h-px bg-slate-50 my-1"></div>
                        <button onclick="silenciarAviso('GLOBAL_TASKS', 87600, 1)" class="w-full text-left px-4 py-2.5 text-xs font-black text-red-500 hover:bg-red-50 rounded-xl transition-all flex items-center gap-2 uppercase italic"><i class="fas fa-trash-can opacity-50"></i> Descartar Todo</button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                <?php foreach($task_cards_html as $c_html) echo $c_html; ?>
            </div>
        </div>
        <?php endif; // Fin !empty task_cards_html ?>
        <?php endif; // Fin id_rol == 1 ?>


        <!-- SECCIÓN: PRÓXIMAS COMPETICIONES (EN FOCO: INSCRIPCIÓN ABIERTA O PRÓXIMA A ABRIRSE EN ≤ 7 DÍAS) -->
        <?php
        // Regla: mostrar competiciones que estén con inscripción abierta o falte ≤ 7 días para abrir, y no haya pasado 1 día desde su fecha
        $q_foco = "SELECT * FROM competiciones 
                   WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 1 DAY) 
                   AND CURDATE() >= DATE_SUB(fecha, INTERVAL (dias_inicio_inscripcion + 7) DAY) 
                   ORDER BY fecha ASC";
        $res_foco = mysqli_query($connection, $q_foco);
        $foco_list = [];
        $foco_ids = [];
        if ($res_foco) {
            while ($r = mysqli_fetch_assoc($res_foco)) {
                $foco_list[] = $r;
                $foco_ids[] = (int)$r['id'];
            }
        }

        // Fallback: Si ninguna está en esa ventana inmediata pero hay competiciones futuras, mostrar la siguiente más próxima
        if (empty($foco_list)) {
            $res_prox = mysqli_query($connection, "SELECT * FROM competiciones WHERE fecha >= CURDATE() ORDER BY fecha ASC LIMIT 1");
            if ($res_prox && ($prox = mysqli_fetch_assoc($res_prox))) {
                $foco_list[] = $prox;
                $foco_ids[] = (int)$prox['id'];
            }
        }
        ?>

        <div class="space-y-8 sm:space-y-12 mb-12 sm:mb-20">
            <div class="flex items-center gap-3 sm:gap-4 border-l-[6px] sm:border-l-[8px] border-blue-600 pl-4 sm:pl-6 py-1.5 sm:py-2">
                <h2 class="text-xl sm:text-2xl md:text-3xl font-black text-slate-800 uppercase tracking-tighter italic">
                    Próximas Competiciones
                </h2>
            </div>

            <?php
            if(!empty($foco_list)){
                foreach ($foco_list as $row):
                    $color = (!empty($row['color'])) ? $row['color'] : '#3b82f6';
            ?>
                <div class="bg-white rounded-2xl sm:rounded-[3rem] p-4 sm:p-8 md:p-12 shadow-sm border-x border-b border-slate-200 border-t-[8px] sm:border-t-[12px] relative overflow-hidden group transition-all hover:shadow-xl" style="border-top-color: <?php echo $color; ?> !important;">
                    <div class="absolute top-0 right-0 w-80 h-80 bg-slate-50 rounded-full -mr-40 -mt-40 group-hover:scale-110 transition-transform duration-700 opacity-50 pointer-events-none"></div>
                    
                    <div class="relative z-10">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 sm:gap-8 mb-6 sm:mb-10">
                            <div class="flex items-start sm:items-center gap-4 sm:gap-8">
                                <div class="w-16 h-18 sm:w-24 sm:h-24 rounded-2xl sm:rounded-[2rem] flex flex-col items-center justify-center text-white shadow-xl shadow-blue-500/20 shrink-0 p-2 tabular-nums" style="background-color: <?php echo $color; ?>;">
                                    <span class="text-[10px] sm:text-xs font-black uppercase opacity-75"><?php echo $meses_es[date('M', strtotime($row['fecha']))]; ?></span>
                                    <span class="text-2xl sm:text-4xl font-black leading-none mt-0.5 sm:mt-1"><?php echo date('d', strtotime($row['fecha'])); ?></span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-xl sm:text-3xl md:text-4xl font-black text-slate-800 tracking-tighter italic leading-tight group-hover:text-blue-600 transition-colors break-words"><?php echo $row['nombre']; ?></h3>
                                    <div class="flex flex-wrap items-center gap-2 sm:gap-4 mt-2 sm:mt-4">
                                        <p class="text-xs sm:text-base font-bold text-slate-500 flex items-center gap-1.5"><i class="fas fa-map-marker-alt text-blue-500 text-xs"></i> <?php echo $row['lugar']; ?></p>
                                        <a href="<?php echo $row['maps']; ?>" target="_blank" class="px-3 py-1.5 sm:px-4 sm:py-2 rounded-xl bg-slate-50 text-slate-600 hover:bg-slate-800 hover:text-white transition-all flex items-center gap-1.5 border border-slate-100 shadow-xs font-black text-[10px] sm:text-xs uppercase italic active:scale-[0.98]"><i class="fa-solid fa-map-location-dot"></i> Ver Mapa</a>
                                    </div>
                                    <?php if(!empty($row['mensaje'])): ?>
                                    <div class="mt-3 sm:mt-5 p-3 sm:p-4 bg-amber-50 border-l-4 border-amber-500 rounded-r-xl sm:rounded-r-2xl text-amber-800 text-xs sm:text-sm font-medium">
                                        <div class="flex items-start gap-2.5">
                                            <i class="fas fa-info-circle mt-0.5 text-amber-600 shrink-0"></i>
                                            <p><?php echo nl2br(htmlspecialchars($row['mensaje'])); ?></p>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row items-stretch sm:items-start gap-4 sm:gap-6 w-full lg:w-auto">
                                <?php
                                $fecha_inicio_inscripcion = date("Y-m-d", strtotime("-".$row['dias_inicio_inscripcion']." days", strtotime($row['fecha'])));
                                $fecha_fin_inscripcion = date("Y-m-d", strtotime("-".($row['dias_fin_inscripcion'])." days", strtotime($row['fecha'])));
                                // Ajuste para mostrar el último día real
                                $fecha_fin_inscripcion_visible = date("Y-m-d", strtotime("-1 day", strtotime($fecha_fin_inscripcion)));

                                $fecha_sorteo = date("Y-m-d", strtotime("-".$row['dias_sorteo']." days", strtotime($row['fecha'])));
                                $hoy = date("Y-m-d");
                                $enlace_inscripcion = "./inscripciones.php";

                                // Detección de fases técnicas (Coach Card y Música)
                                $id_c_row = (int)$row['id'];
                                $res_cc = mysqli_query($connection, "SELECT COUNT(*) as num FROM fases WHERE id_competicion = $id_c_row AND elementos_coach_card > 0");
                                $has_cc = ($res_cc && ($row_cc = mysqli_fetch_assoc($res_cc)) && $row_cc['num'] > 0);
                                $has_musica = ($row['figuras'] != 'si');

                                $d_musica = !empty($row['dias_musica']) ? (int)$row['dias_musica'] : 0;
                                $f_musica = date("Y-m-d", strtotime("-".$d_musica." days", strtotime($row['fecha'])));
                                $f_musica_visible = date("Y-m-d", strtotime("-1 day", strtotime($f_musica)));

                                $d_cc = !empty($row['dias_coach_card']) ? (int)$row['dias_coach_card'] : 7;
                                $f_cc = date("Y-m-d", strtotime("-".$d_cc." days", strtotime($row['fecha'])));
                                $f_cc_visible = date("Y-m-d", strtotime("-1 day", strtotime($f_cc)));

                                if($hoy >= $fecha_inicio_inscripcion && $hoy <= $fecha_fin_inscripcion_visible): ?>
                                    
                                    <!-- ESTADO: INSCRIPCIONES ABIERTAS -->
                                    <div class="flex flex-col items-center sm:items-start gap-2.5 w-full sm:w-auto">
                                        
                                        <!-- BOTÓN GESTIONAR INSCRIPCIONES (Estilo Pill Abierto con Fecha Dentro) -->
                                        <form action="<?php echo $enlace_inscripcion;?>" method="post" class="w-full sm:w-auto">
                                            <input type="hidden" name="id_competicion" value="<?php echo $row['id'];?>">
                                            <input type="hidden" name="nombre_competicion" value="<?php echo $row['nombre'];?>">
                                            <input type="hidden" name="competicion_figuras" value="<?php echo $row['figuras'];?>">
                                            <button type="submit" name="inscripciones" class="tap-press w-full sm:w-auto px-4 py-2.5 sm:px-5 sm:py-3 bg-emerald-50 hover:bg-emerald-100/90 border border-emerald-200/90 hover:border-emerald-300 text-emerald-800 rounded-2xl shadow-xs transition-all flex flex-col items-center sm:items-start text-left group">
                                                <span class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-emerald-700">
                                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
                                                    <span>Gestionar Inscripciones</span>
                                                    <i class="fas fa-arrow-right text-[10px] text-emerald-500 group-hover:translate-x-1 transition-transform ml-0.5"></i>
                                                </span>
                                                <span class="text-[10px] sm:text-[11px] font-bold text-emerald-600/90 tracking-tight tabular-nums mt-0.5 pl-4 sm:pl-4">
                                                    Hasta el <?php echo dateAFecha($fecha_fin_inscripcion_visible); ?>
                                                </span>
                                            </button>
                                        </form>

                                        <!-- PILLS DE COACH CARD Y MÚSICA (Apilados y justificados a la izquierda con colores oficiales) -->
                                        <?php if($has_cc || $has_musica): ?>
                                            <div class="flex flex-col items-start gap-1.5 w-full">
                                                <?php if($has_cc): ?>
                                                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-white hover:bg-amber-50/40 rounded-xl border border-slate-200/80 shadow-2xs text-[11px] font-bold text-slate-700 transition-colors">
                                                        <i class="fas fa-puzzle-piece text-amber-500 text-xs shrink-0"></i>
                                                        <span class="text-slate-500 font-semibold">Coach Card:</span>
                                                        <span class="font-black text-slate-800 tabular-nums"><?php echo dateAFecha($f_cc_visible); ?></span>
                                                    </div>
                                                <?php endif; ?>

                                                <?php if($has_musica): ?>
                                                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-white hover:bg-purple-50/40 rounded-xl border border-slate-200/80 shadow-2xs text-[11px] font-bold text-slate-700 transition-colors">
                                                        <i class="fas fa-file-zipper text-purple-600 text-xs shrink-0"></i>
                                                        <span class="text-slate-500 font-semibold">Música:</span>
                                                        <span class="font-black text-slate-800 tabular-nums"><?php echo dateAFecha($f_musica_visible); ?></span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>

                                    </div>

                                <?php elseif($hoy < $fecha_inicio_inscripcion): ?>
                                    
                                    <!-- ESTADO: AÚN NO ABIERTAS -->
                                    <div class="w-full sm:w-auto px-4 py-2.5 sm:px-5 sm:py-3 bg-slate-50 text-slate-500 font-bold text-xs uppercase tracking-wider rounded-2xl border border-slate-200/80 text-center italic tabular-nums flex items-center justify-center gap-2">
                                        <i class="fas fa-lock text-[11px] opacity-50"></i> Apertura: <?php echo dateAFecha($fecha_inicio_inscripcion); ?>
                                    </div>

                                <?php else: ?>
                                    
                                    <!-- ESTADO: INSCRIPCIONES CERRADAS (Solo Pill de Cerradas y Botón de Comprobación) -->
                                    <div class="flex flex-col items-center sm:items-start gap-1.5 w-full sm:w-auto">
                                        <div class="inline-flex items-center gap-1.5 text-[10px] sm:text-[11px] font-black text-rose-500 uppercase tracking-wider bg-rose-50 px-3 py-1 rounded-full border border-rose-100 shadow-xs italic">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            <span>Inscripciones Cerradas</span>
                                        </div>
                                        <p class="text-[9px] sm:text-[10px] font-bold text-slate-400 uppercase italic tabular-nums mt-0.5">
                                            Finalizó el: <?php echo dateAFecha($fecha_fin_inscripcion_visible); ?>
                                        </p>
                                        <form action="<?php echo $enlace_inscripcion;?>" method="post" class="w-full sm:w-auto mt-1">
                                            <input type="hidden" name="id_competicion" value="<?php echo $row['id'];?>">
                                            <input type="hidden" name="nombre_competicion" value="<?php echo $row['nombre'];?>">
                                            <input type="hidden" name="competicion_figuras" value="<?php echo $row['figuras'];?>">
                                            <button type="submit" name="inscripciones" class="tap-press w-full sm:w-auto px-4 py-2 sm:px-5 sm:py-2.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-600 hover:text-slate-900 font-bold text-xs uppercase tracking-wider rounded-xl sm:rounded-2xl shadow-xs transition-all flex items-center justify-center gap-2">
                                                <i class="fas fa-eye text-slate-400 text-xs"></i> <span>Comprobar Inscripciones</span>
                                            </button>
                                        </form>
                                    </div>

                                <?php endif; ?>

                                <?php if(!empty($row['enlace_sorteo'])): ?>
                                    <div class="text-center sm:text-left w-full sm:w-auto self-center">
                                        <p class="text-[10px] sm:text-xs font-black text-slate-400 uppercase mb-2 tabular-nums">Sorteo: <?php echo dateAFecha($fecha_sorteo); ?></p>
                                        <a href="<?php echo $row['enlace_sorteo'];?>" target="_blank" class="w-full sm:w-auto px-6 py-3.5 sm:px-7 sm:py-3.5 bg-blue-600 text-white font-black uppercase text-xs sm:text-sm tracking-widest rounded-xl sm:rounded-[1.5rem] shadow-lg shadow-blue-500/20 hover:bg-blue-700 active:scale-[0.98] transition-all flex items-center justify-center gap-3 no-underline">
                                            <i class="fa-solid fa-video text-base"></i> Unirse al Sorteo
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Fases de Competición (Responsive: Card-List en móvil, Tabla en Desktop) -->
                        <?php
                        $q_fases = "SELECT 
                                        fs.id,
                                        fs.orden,
                                        fs.id_figura,
                                        fs.id_modalidad,
                                        fs.genero,
                                        c.nombre as cat,
                                        f.nombre as fig,
                                        f.numero as fig_num,
                                        f.grado_dificultad as gd,
                                        m.nombre as modali,
                                        m.numero_participantes as np,
                                        m.numero_reservas as nr
                                    FROM fases fs
                                    LEFT JOIN categorias c ON fs.id_categoria = c.id
                                    LEFT JOIN figuras f ON fs.id_figura = f.id
                                    LEFT JOIN modalidades m ON fs.id_modalidad = m.id
                                    WHERE fs.id_competicion = " . (int)$row['id'] . "
                                    ORDER BY fs.orden ASC";
                        $res_fases = mysqli_query($connection, $q_fases);
                        $fases_list = [];
                        if($res_fases) {
                            while($f = mysqli_fetch_assoc($res_fases)) {
                                $fases_list[] = $f;
                            }
                        }
                        ?>
                        
                        <div class="rounded-xl sm:rounded-[2rem] border border-slate-200/80 bg-slate-50/40 overflow-hidden mb-6 sm:mb-10 shadow-inner">
                            <?php if(!empty($fases_list)): ?>
                                <!-- Vista Móvil: Lista de Fases sin cortes -->
                                <div class="block sm:hidden divide-y divide-slate-100 bg-white">
                                    <?php foreach($fases_list as $f): ?>
                                    <div class="p-3.5 space-y-2 hover:bg-slate-50/80 transition-colors">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="text-xs font-black text-slate-800 uppercase italic tracking-tight truncate">
                                                <?php echo htmlspecialchars($f['cat'] ?? 'Sin Categoría'); ?>
                                            </span>
                                            <div class="shrink-0">
                                                <?php if(!empty($f['id_figura'])): ?>
                                                    <span class="px-2.5 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded-full font-black text-[10px] tabular-nums shadow-xs">
                                                        GD <?php echo $f['gd']; ?>
                                                    </span>
                                                <?php elseif(!empty($f['id_modalidad'])): ?>
                                                    <span class="px-2.5 py-0.5 bg-blue-50 text-blue-700 border border-blue-100 rounded-full font-black text-[10px] tabular-nums shadow-xs">
                                                        <?php echo $f['np'].' + '.$f['nr'].'R'; ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-slate-400 text-xs">-</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="flex items-start gap-2">
                                            <?php if(!empty($f['id_figura']) && !empty($f['fig'])): ?>
                                                <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-md font-black text-[10px] tabular-nums shrink-0 mt-0.5">#<?php echo $f['fig_num']; ?></span>
                                                <span class="text-slate-800 font-bold text-xs leading-snug break-words"><?php echo $f['fig']; ?></span>
                                            <?php elseif(!empty($f['id_modalidad']) && !empty($f['modali'])): ?>
                                                <span class="px-2 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 rounded-md font-black text-[10px] uppercase shrink-0 mt-0.5"><i class="fas fa-water mr-1"></i>Rutina</span>
                                                <span class="text-slate-800 font-bold text-xs leading-snug break-words"><?php echo $f['modali'] . (($f['genero'] ?? '') === 'masculino' ? ' Masc' : ''); ?></span>
                                            <?php else: ?>
                                                <span class="text-slate-400 italic text-xs">No asignada</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>

                                <!-- Vista Tablet / Desktop: Tabla espaciosa y scrolleable -->
                                <div class="hidden sm:block overflow-x-auto">
                                    <table class="w-full text-left border-collapse">
                                        <thead>
                                            <tr class="bg-slate-100/60 text-xs sm:text-sm font-black text-slate-400 uppercase tracking-widest italic border-b border-slate-200/60">
                                                <th class="px-6 py-4">Categoría</th>
                                                <th class="px-6 py-4"><?php echo ($row['figuras'] == 'si') ? 'Figura' : (($row['figuras'] == 'mixta') ? 'Prueba (Figura / Rutina)' : 'Modalidad'); ?></th>
                                                <th class="px-6 py-4 text-center"><?php echo ($row['figuras'] == 'si') ? 'G.D.' : (($row['figuras'] == 'mixta') ? 'Detalle Técnico' : 'Participantes'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 bg-white/50">
                                            <?php foreach($fases_list as $f): ?>
                                                <tr class="text-sm font-bold text-slate-600 hover:bg-white transition-colors">
                                                    <td class="px-6 py-4 font-black text-slate-700 italic uppercase tracking-tighter">
                                                        <?php echo htmlspecialchars($f['cat'] ?? 'Sin Categoría'); ?>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <?php if(!empty($f['id_figura']) && !empty($f['fig'])): ?>
                                                            <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg mr-2 font-black text-xs tabular-nums">#<?php echo $f['fig_num']; ?></span>
                                                            <span class="text-slate-800 font-bold"><?php echo $f['fig']; ?></span>
                                                        <?php elseif(!empty($f['id_modalidad']) && !empty($f['modali'])): ?>
                                                            <span class="px-2.5 py-1 bg-blue-50 text-blue-700 border border-blue-200 rounded-lg mr-2 font-black text-xs uppercase"><i class="fas fa-water mr-1"></i> Rutina</span>
                                                            <span class="text-slate-800 font-bold"><?php echo $f['modali'] . (($f['genero'] ?? '') === 'masculino' ? ' Masc' : ''); ?></span>
                                                        <?php else: ?>
                                                            <span class="text-slate-400 italic">No asignada</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="px-6 py-4 text-center">
                                                        <?php if(!empty($f['id_figura'])): ?>
                                                            <span class="px-3.5 py-1 bg-emerald-50 text-emerald-700 rounded-full font-black text-xs shadow-xs border border-emerald-100 tabular-nums">
                                                                GD <?php echo $f['gd']; ?>
                                                            </span>
                                                        <?php elseif(!empty($f['id_modalidad'])): ?>
                                                            <span class="px-3.5 py-1 bg-blue-50 text-blue-700 rounded-full font-black text-xs shadow-xs border border-blue-100 tabular-nums">
                                                                <?php echo $f['np'].' + '.$f['nr'].'R'; ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="text-slate-400">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="px-6 py-8 text-center text-xs sm:text-sm italic text-slate-400 font-bold">Sin fases definidas para esta competición.</div>
                            <?php endif; ?>
                        </div>

                        <!-- Documentación Adjunta -->
                        <div class="flex flex-wrap gap-2.5 sm:gap-4">
                            <?php
                            $docs_map = [
                                'normativa' => ['label' => 'Normativa', 'icon' => 'fa-file-shield'],
                                'nadadoras' => ['label' => 'Nadadoras', 'icon' => 'fa-users-viewfinder'],
                                'inscripciones' => ['label' => 'Inscripciones', 'icon' => 'fa-file-pen'],
                                'orden' => ['label' => 'Orden Salida', 'icon' => 'fa-list-ol'],
                                'resultados' => ['label' => 'Resultados', 'icon' => 'fa-trophy'],
                                'liga' => ['label' => 'Ranking Liga', 'icon' => 'fa-ranking-star']
                            ];
                            foreach($docs_map as $file_id => $meta):
                                $filename = './docs/'.$row['id'].'-'.$file_id.'.pdf';
                                if (file_exists($filename)):
                            ?>
                                <a href="<?php echo $filename; ?>" target="_blank" class="px-3.5 py-2 sm:px-5 sm:py-2.5 bg-white text-slate-600 hover:bg-slate-800 hover:text-white rounded-xl sm:rounded-[1.25rem] border border-slate-200/80 text-[11px] sm:text-xs font-black uppercase tracking-tight transition-all flex items-center gap-2 sm:gap-3 shadow-xs active:scale-[0.98] group/doc">
                                    <i class="fa-solid <?php echo $meta['icon']; ?> text-base sm:text-lg text-slate-400 group-hover/doc:text-white transition-colors"></i> <?php echo $meta['label']; ?>
                                </a>
                            <?php 
                                endif; 
                            endforeach; 
                            ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; } else { ?>
                <div class="p-8 sm:p-16 bg-white rounded-2xl sm:rounded-[3rem] border border-slate-100 text-center shadow-sm">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 mx-auto mb-4 sm:mb-6"><i class="fas fa-calendar-times text-2xl sm:text-3xl"></i></div>
                    <p class="text-slate-400 font-black italic uppercase tracking-widest text-xs sm:text-sm">No existen competiciones programadas</p>
                </div>
            <?php } ?>
        </div>

        <!-- SECCIÓN: EVENTOS EN CALENDARIO (RESTO DE TEMPORADA - SIN DUPLICAR) -->
        <?php
        $not_in_foco = !empty($foco_ids) ? "AND id NOT IN (" . implode(',', $foco_ids) . ")" : "";
        $query_cal = "SELECT * FROM competiciones WHERE fecha >= CURDATE() $not_in_foco ORDER BY fecha ASC";
        $query_run_cal = mysqli_query($connection, $query_cal);
        $total_calendario = mysqli_num_rows($query_run_cal);
        ?>
        <div class="mb-12 sm:mb-20">
            <div class="flex items-center justify-between border-l-[6px] sm:border-l-[8px] border-emerald-500 pl-4 sm:pl-6 py-1.5 sm:py-2 mb-6 sm:mb-8">
                <h2 class="text-xl sm:text-2xl md:text-3xl font-black text-slate-800 uppercase tracking-tighter italic flex items-center gap-3">
                    <i class="fas fa-calendar-days text-emerald-500"></i> Eventos en Calendario
                </h2>
                <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest tabular-nums"><?php echo $total_calendario; ?> posteriores</span>
            </div>
            
            <div class="bg-white rounded-2xl sm:rounded-[3rem] p-4 sm:p-8 md:p-12 shadow-sm border border-slate-200 border-t-[8px] sm:border-t-[10px] border-t-emerald-500">
                <div class="space-y-4 sm:space-y-6">
                    <?php
                    if($total_calendario > 0){
                        while ($row = mysqli_fetch_assoc($query_run_cal)):
                            $color_cal = (!empty($row['color'])) ? $row['color'] : '#0284c7';
                    ?>
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 sm:p-6 md:p-8 rounded-xl sm:rounded-[2.5rem] bg-slate-50 border border-slate-100 hover:bg-white hover:shadow-lg transition-all group gap-4 sm:gap-8">
                            <div class="flex items-start sm:items-center gap-4 sm:gap-8">
                                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl sm:rounded-2xl flex flex-col items-center justify-center text-white shadow-md shrink-0 tabular-nums p-1" style="background-color: <?php echo $color_cal; ?>;">
                                    <span class="text-[10px] sm:text-[11px] font-black uppercase opacity-85"><?php echo $meses_es[date('M', strtotime($row['fecha']))]; ?></span>
                                    <span class="text-lg sm:text-xl font-black leading-none mt-0.5"><?php echo date('d', strtotime($row['fecha'])); ?></span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="text-base sm:text-xl md:text-2xl font-black text-slate-800 leading-tight group-hover:text-blue-600 transition-colors italic break-words"><?php echo $row['nombre']; ?></h4>
                                    <p class="text-xs sm:text-sm font-bold text-slate-400 uppercase tracking-tighter mt-1 sm:mt-2 flex items-center gap-2"><i class="fas fa-map-marker-alt text-blue-500 text-xs"></i> <?php echo $row['lugar']; ?></p>
                                    <?php if(!empty($row['mensaje'])): ?>
                                    <div class="mt-2.5 sm:mt-3 p-2.5 sm:p-3 bg-amber-50 border-l-4 border-amber-500 rounded-r-xl text-amber-700 text-xs font-medium max-w-lg">
                                        <div class="flex items-start gap-2">
                                            <i class="fas fa-info-circle mt-0.5 text-amber-600 shrink-0"></i>
                                            <p><?php echo nl2br(htmlspecialchars($row['mensaje'])); ?></p>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 self-end sm:self-center">
                                <a href="<?php echo $row['maps']; ?>" target="_blank" class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-white border border-slate-200 text-slate-400 hover:text-blue-500 flex items-center justify-center transition-all shadow-xs active:scale-[0.95]"><i class="fa-solid fa-map-location-dot text-base sm:text-lg"></i></a>
                            </div>
                        </div>
                    <?php endwhile; } else { ?>
                        <div class="text-center p-8 sm:p-12 text-xs sm:text-sm font-bold text-slate-400 uppercase italic">No existen más competiciones posteriores en el calendario.</div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <!-- SECCIÓN: HISTORIAL -->
        <div>
            <div class="flex items-center gap-3 sm:gap-4 border-l-[6px] sm:border-l-[8px] border-slate-400 pl-4 sm:pl-6 py-1.5 sm:py-2 mb-6 sm:mb-8">
                <h2 class="text-xl sm:text-2xl md:text-3xl font-black text-slate-800 uppercase tracking-tighter italic">
                    Historial
                </h2>
            </div>
            
            <div class="bg-white rounded-2xl sm:rounded-[3rem] shadow-sm border border-slate-200 border-t-[8px] sm:border-t-[12px] border-t-slate-400 overflow-hidden mb-8 sm:mb-12">
                <div class="min-w-0">
                    <table class="w-full text-left border-collapse">
                        <thead class="hidden md:table-header-group">
                            <tr class="bg-slate-50/50 text-xs sm:text-sm font-black text-slate-400 uppercase tracking-[0.2em] italic border-b border-slate-100">
                                <th class="px-8 py-5 w-32">Fecha</th>
                                <th class="px-6 py-5">Evento y Localidad</th>
                                <th class="px-6 py-5">Documentación Oficial</th>
                                <th class="px-8 py-5 text-center w-24">Mapa</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y-4 sm:divide-y-8 divide-slate-50">
                            <?php
                            $query = 'select * FROM competiciones WHERE fecha < (SELECT CURDATE()) ORDER BY fecha desc LIMIT 10';
                            $query_run = mysqli_query($connection,$query);
                            while ($row = mysqli_fetch_assoc($query_run)):
                                $color_hist = (!empty($row['color'])) ? $row['color'] : '#475569';
                            ?>
                            <tr class="hover:bg-slate-50/50 transition-colors flex flex-col md:table-row p-4 sm:p-6 md:p-0 border-b border-slate-100 md:border-b-0">
                                
                                <!-- Columna 1: Fecha (Al principio) -->
                                <td class="px-0 md:px-8 py-0 md:py-8 mb-4 md:mb-0">
                                    <div class="flex flex-col items-center justify-center w-16 h-20 sm:w-20 sm:h-24 rounded-xl sm:rounded-[1.5rem] shadow-md text-white tabular-nums p-2" style="background-color: <?php echo $color_hist; ?>;">
                                        <span class="text-[9px] sm:text-[10px] font-black uppercase opacity-80 leading-none mb-1"><?php echo $meses_es[date("M", strtotime($row['fecha']))]; ?></span>
                                        <span class="text-2xl sm:text-3xl font-black text-white leading-tight"><?php echo date("d", strtotime($row['fecha'])); ?></span>
                                        <span class="text-[10px] sm:text-xs font-bold opacity-75 tracking-widest leading-none mt-1 sm:mt-1.5"><?php echo date("Y", strtotime($row['fecha'])); ?></span>
                                    </div>
                                </td>

                                <!-- Columna 2: Nombre y Localidad -->
                                <td class="px-0 md:px-6 py-0 md:py-6 mb-4 md:mb-0">
                                    <div>
                                        <h4 class="text-lg sm:text-xl font-black text-slate-900 leading-tight italic uppercase tracking-tighter mb-1 sm:mb-2 break-words"><?php echo $row['nombre']; ?></h4>
                                        <div class="flex items-center gap-2">
                                            <i class="fa-solid fa-map-location-dot text-blue-500 text-xs sm:text-sm"></i>
                                            <p class="text-xs sm:text-sm font-bold text-slate-500 italic"><?php echo $row['lugar']; ?></p>
                                        </div>
                                        <?php if(!empty($row['mensaje'])): ?>
                                        <div class="mt-2.5 p-2.5 sm:p-3 bg-amber-50 border-l-4 border-amber-500 rounded-r-xl text-amber-700 text-xs font-medium max-w-lg">
                                            <div class="flex items-start gap-2">
                                                <i class="fas fa-info-circle mt-0.5 text-amber-600 shrink-0"></i>
                                                <p><?php echo nl2br(htmlspecialchars($row['mensaje'])); ?></p>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- Columna 3: Documentación (Colores Fijos) -->
                                <td class="px-0 md:px-6 py-0 md:py-6 mb-4 md:mb-0">
                                    <div class="flex gap-4 sm:gap-6 flex-wrap items-center">
                                        <?php if(file_exists('./docs/'.$row['id'].'-normativa.pdf')): ?>
                                            <a href="./docs/<?php echo $row['id']; ?>-normativa.pdf" target="_blank" title="Normativa" class="text-indigo-500 hover:scale-110 active:scale-95 transition-transform"><i class="fas fa-file-shield text-2xl sm:text-3xl"></i></a>
                                        <?php endif; ?>
                                        <?php if(file_exists('./docs/'.$row['id'].'-inscripciones.pdf')): ?>
                                            <a href="./docs/<?php echo $row['id']; ?>-inscripciones.pdf" target="_blank" title="Listado Inscripciones" class="text-blue-500 hover:scale-110 active:scale-95 transition-transform"><i class="fas fa-file-pen text-2xl sm:text-3xl"></i></a>
                                        <?php endif; ?>
                                        <?php if(file_exists('./docs/'.$row['id'].'-orden.pdf')): ?>
                                            <a href="./docs/<?php echo $row['id']; ?>-orden.pdf" target="_blank" title="Orden de Salida" class="text-slate-600 hover:scale-110 active:scale-95 transition-transform"><i class="fas fa-list-ol text-2xl sm:text-3xl"></i></a>
                                        <?php endif; ?>
                                        <?php if(file_exists('./docs/'.$row['id'].'-resultados.pdf')): ?>
                                            <a href="./docs/<?php echo $row['id']; ?>-resultados.pdf" target="_blank" title="Resultados Finales" class="text-amber-500 hover:scale-110 active:scale-95 transition-transform"><i class="fas fa-trophy text-2xl sm:text-3xl"></i></a>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- Columna 4: Ubicación (Desktop) -->
                                <td class="hidden md:table-cell px-8 py-6 text-center">
                                    <a href="<?php echo $row['maps']; ?>" target="_blank" class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-blue-50 text-blue-500 hover:bg-blue-600 hover:text-white transition-all flex items-center justify-center border border-blue-100 shadow-xs mx-auto active:scale-[0.95]"><i class="fa-solid fa-map-location-dot text-base sm:text-lg"></i></a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</main>

<script>
    function toggleSilencioMenu(tipo) {
        // Cerrar otros menús abiertos
        document.querySelectorAll('[id^="menu-"]').forEach(menu => {
            if(menu.id !== 'menu-' + tipo) menu.classList.add('hidden');
        });
        const menu = document.getElementById('menu-' + tipo);
        if (menu) menu.classList.toggle('hidden');
    }

    function silenciarAviso(tipo, horas, es_descarte = 0) {
        const formData = new FormData();
        formData.append('action', 'silenciar');
        formData.append('tipo', tipo);
        formData.append('horas', horas);
        formData.append('es_descarte', es_descarte);

        fetch('avisos_code.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                if(tipo === 'GLOBAL_TASKS') {
                    // Animación de salida para toda la sección
                    const section = document.getElementById('tareas-pendientes-section');
                    if(section) {
                        section.style.transition = 'all 0.8s ease';
                        section.style.opacity = '0';
                        section.style.transform = 'translateY(-20px)';
                        setTimeout(() => { section.style.display = 'none'; }, 800);
                    }
                } else {
                    // Animación de salida y ocultar la tarjeta individual
                    const menu = document.getElementById('menu-' + tipo);
                    if(menu) {
                        const card = menu.closest('.relative.group');
                        if (card) {
                            card.style.transition = 'all 0.5s ease';
                            card.style.opacity = '0';
                            card.style.transform = 'scale(0.9)';
                            setTimeout(() => { 
                                card.style.display = 'none';
                                // Recargar si no quedan tareas para que desaparezca la sección
                                if(document.querySelectorAll('#tareas-pendientes-section .grid > .relative.group:not([style*="display: none"])').length === 0) {
                                    location.reload();
                                }
                            }, 500);
                        }
                    }
                }
            } else {
                alert('Error al silenciar: ' + data.message);
            }
        });
    }

    // Cerrar menús al hacer clic fuera
    document.addEventListener('click', function(event) {
        if (!event.target.closest('[onclick^="toggleSilencioMenu"]')) {
            document.querySelectorAll('[id^="menu-"]').forEach(menu => menu.classList.add('hidden'));
        }
    });
</script>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
