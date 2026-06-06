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
    <div class="p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend">
        
        <?php include('includes/alertas_v4.php'); ?>

        <?php
        $query_usr = "SELECT * FROM usuarios where id=".$_SESSION['id_usario']."";
        $query_run_usr = mysqli_query($connection, $query_usr);
        $usuario = mysqli_fetch_array($query_run_usr);
        ?>

        <!-- Page Heading -->
        <div class="mb-12">
            <a href="index.php" class="no-underline group/dash inline-block">
                <h1 class="text-5xl font-black text-slate-800 tracking-tighter mb-2 italic text-primary group-hover/dash:text-blue-600 transition-colors">Dashboard</h1>
            </a>
            <p class="text-lg text-slate-500 font-medium">Panel central de gestión SINCRM</p>
        </div>

        <!-- FILA 1: Tarjetas Informativas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
            
            <!-- Mi Perfil (COMENTADO POR SI SE REQUIERE VOLVER A ÉL) -->
            <?php /*
            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-200 border-t-[10px] border-t-amber-500 group hover:shadow-xl transition-all">
                <div class="flex justify-between items-start mb-6">
                    <div class="w-14 h-14 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 transition-all group-hover:scale-110 shadow-sm"><i class="fas fa-user-circle text-2xl"></i></div>
                    <span class="text-[11px] font-black uppercase text-slate-400 tracking-[0.2em] italic">Mi Perfil</span>
                </div>
                <div class="space-y-1">
                    <h3 class="text-2xl font-black text-slate-800 truncate"><?php echo @$usuario['username']?></h3>
                    <p class="text-sm font-bold text-slate-400 flex items-center gap-2 italic"><i class="fa-regular fa-envelope text-xs opacity-50"></i> <?php echo @$usuario['email']?></p>
                    <p class="text-sm font-bold text-slate-400 flex items-center gap-2 italic"><i class="fa-solid fa-phone text-xs opacity-50"></i> <?php echo @$usuario['telefono']?></p>
                </div>
            </div>
            */ ?>

            <!-- Mi Club -->
            <?php if($_SESSION['id_rol'] == 5): ?>
            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-200 border-t-[10px] border-t-red-500 group hover:shadow-xl transition-all">
                <div class="flex justify-between items-start mb-6">
                    <div class="w-14 h-14 rounded-2xl bg-red-50 flex items-center justify-center text-red-600 transition-all group-hover:scale-110 shadow-sm"><i class="fas fa-flag text-2xl"></i></div>
                    <span class="text-[11px] font-black uppercase text-slate-400 tracking-[0.2em] italic">Mi Club</span>
                </div>
                <?php
                $query = "SELECT * FROM clubes where id=".$_SESSION['club']."";
                $query_run = mysqli_query($connection,$query);
                $club = mysqli_fetch_array($query_run);
                ?>
                <div class="space-y-1">
                    <h3 class="text-2xl font-black text-slate-800 truncate"><?php echo $club['nombre']?></h3>
                    <p class="text-sm font-bold text-slate-400 italic uppercase tracking-tighter">Nombre corto: <?php echo $club['nombre_corto']?> | RFEN: <?php echo $club['codigo']?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Mi Equipo -->
            <a href="mi_equipo.php" class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-200 border-t-[10px] border-t-emerald-500 group hover:shadow-xl transition-all no-underline block">
                <div class="flex justify-between items-start mb-6">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 transition-all group-hover:scale-110 shadow-sm"><i class="fas fa-users-gear text-2xl"></i></div>
                    <span class="text-[11px] font-black uppercase text-slate-400 tracking-[0.2em] italic">Mi Equipo</span>
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
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-4xl font-black text-slate-800 leading-none"><?php echo $total_miembros; ?> <span class="text-sm text-slate-400 uppercase tracking-widest italic">Censo</span></h3>
                    
                    <!-- Avatar Stack con Tooltip Rediseñado -->
                    <div class="flex -space-x-4 relative group/stack">
                        <?php 
                        mysqli_data_seek($res_equipo, 0);
                        while($m = mysqli_fetch_assoc($res_equipo)):
                            if($m['username'] != $usuario['username']) $otros_nombres[] = $m['username'];
                            if($shown < $show_limit):
                                $inicial = strtoupper(substr($m['username'], 0, 1));
                        ?>
                            <div class="w-10 h-10 rounded-full border-2 border-white flex items-center justify-center text-xs font-black shadow-md oceanic-gradient text-white">
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
                <div class="mt-4 pt-4 border-t border-slate-100">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Compartes equipo con:</p>
                    <p class="text-sm font-bold text-slate-600 italic leading-snug">
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
            <a href="nadadoras.php" class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-200 border-t-[10px] border-t-blue-600 group hover:shadow-xl transition-all no-underline block">
                <div class="flex justify-between items-start mb-6">
                    <div class="w-14 h-14 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 transition-all group-hover:scale-110 shadow-sm"><i class="fas fa-swimmer text-2xl"></i></div>
                    <span class="text-[11px] font-black uppercase text-slate-400 tracking-[0.2em] italic">Censo Técnico</span>
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
                <h3 class="text-4xl font-black text-slate-800 leading-none mb-2"><?php echo $numero_nadadoras_activas?> <span class="text-sm text-slate-400 uppercase tracking-widest italic">Nadadoras Activas</span></h3>
                <p class="text-xs font-bold text-orange-500 uppercase italic tracking-tighter"><?php echo $numero_nadadoras_baja?> nadadoras en historial</p>
            </a>
            <?php endif; ?>

            <!-- BIAS Analizer (SÓLO PARA JUECES EN DASHBOARD) -->
            <?php if($_SESSION['id_rol'] == 4): ?>
            <a href="mi_auditoria.php" class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-200 border-t-[10px] border-t-blue-600 group hover:shadow-xl transition-all no-underline block">
                <div class="flex justify-between items-start mb-6">
                    <div class="w-14 h-14 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 transition-all group-hover:scale-110 shadow-sm"><i class="fas fa-magnifying-glass-chart text-2xl"></i></div>
                    <span class="text-[11px] font-black uppercase text-slate-400 tracking-[0.2em] italic">Mi Auditoría</span>
                </div>
                <h3 class="text-4xl font-black text-slate-800 leading-none mb-2 italic">AQUA BIAS</h3>
                <p class="text-xs font-bold text-blue-500 uppercase italic tracking-tighter">Consulta tu rendimiento técnico oficial</p>
            </a>

            <a href="ranking_jueces.php" class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-200 border-t-[10px] border-t-amber-500 group hover:shadow-xl transition-all no-underline block">
                <div class="flex justify-between items-start mb-6">
                    <div class="w-14 h-14 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 transition-all group-hover:scale-110 shadow-sm"><i class="fas fa-ranking-star text-2xl"></i></div>
                    <span class="text-[11px] font-black uppercase text-slate-400 tracking-[0.2em] italic">Global</span>
                </div>
                <h3 class="text-4xl font-black text-slate-800 leading-none mb-2 italic">RANKING</h3>
                <p class="text-xs font-bold text-amber-500 uppercase italic tracking-tighter">Comparativa anónima de calidad técnica</p>
            </a>
            <?php endif; ?>

        </div>

        <!-- SECCIÓN: TAREAS PENDIENTES (SÓLO ADMIN) -->
        <?php if($_SESSION['id_rol'] == 1): ?>
        <?php
        // Obtener avisos silenciados para este usuario
        $user_id = $_SESSION['id_usario'];
        $q_sils = "SELECT tipo_aviso FROM avisos_silenciados WHERE id_usuario = '$user_id' AND silencio_hasta > NOW()";
        $res_sils = mysqli_query($connection, $q_sils);
        $silenciados = [];
        while($s = mysqli_fetch_assoc($res_sils)) { $silenciados[] = $s['tipo_aviso']; }

        // Si la sección completa está silenciada, no mostramos nada
        if(!in_array('GLOBAL_TASKS', $silenciados)):
        ?>
        <div class="mb-12" id="tareas-pendientes-section">
            <div class="flex items-center justify-between border-l-[8px] border-amber-500 pl-6 py-2 mb-8">
                <h2 class="text-3xl font-black text-slate-800 uppercase tracking-tighter italic flex items-center gap-3">
                    Tareas Pendientes y Avisos
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-amber-100 text-amber-600 text-sm animate-bounce"><i class="fas fa-bell"></i></span>
                </h2>
                
                <!-- Botón Silenciar Todo -->
                <div class="relative inline-block text-left">
                    <button onclick="toggleSilencioMenu('GLOBAL_TASKS')" class="flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-500 rounded-xl transition-all text-[10px] font-black uppercase tracking-widest italic">
                        <i class="fas fa-clock"></i> Silenciar Todo
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

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <?php
                // Función auxiliar para renderizar el botón de silenciar
                function renderSilenciar($tipo) {
                    return '
                    <div class="relative inline-block text-left ml-2" onclick="event.preventDefault(); event.stopPropagation();">
                        <button type="button" onclick="toggleSilencioMenu(\''.$tipo.'\')" class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 hover:bg-slate-200 hover:text-slate-600 transition-all">
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
                ?>

                <!-- 1. Usuarios pendientes de aprobación -->
                <?php
                $tipo = 'REGISTROS_PENDIENTES';
                if(!in_array($tipo, $silenciados)):
                    $q_pend = "SELECT COUNT(*) as total, MIN(creado) as mas_antiguo FROM usuarios WHERE id_rol = 6 AND activo = 1";
                    $res_pend = mysqli_query($connection, $q_pend);
                    $pend_data = mysqli_fetch_assoc($res_pend);
                    $pend_users = $pend_data['total'];
                    if($pend_users > 0):
                ?>
                <div class="relative group">
                    <a href="usuarios.php" class="block bg-white p-6 rounded-[2rem] border border-slate-200 border-l-[8px] border-l-purple-500 shadow-sm hover:shadow-md transition-all no-underline">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-12 h-12 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600 group-hover:scale-110 transition-all"><i class="fas fa-user-clock text-xl"></i></div>
                            <div class="flex flex-col items-end">
                                <span class="text-[9px] font-black uppercase text-slate-400 tracking-widest">Desde <?php echo date("d-m", strtotime($pend_data['mas_antiguo'])); ?></span>
                                <?php echo renderSilenciar($tipo); ?>
                            </div>
                        </div>
                        <h4 class="text-xl font-black text-slate-800 leading-tight mb-2">Registros Pendientes</h4>
                        <p class="text-sm font-bold text-slate-500 italic">Hay <span class="text-purple-600 font-black"><?php echo $pend_users; ?></span> usuarios esperando aprobación o configuración de rol.</p>
                    </a>
                </div>
                <?php endif; endif; ?>

                <!-- 1b. Jueces sin vincular -->
                <?php
                $tipo = 'JUECES_SIN_VINCULAR';
                if(!in_array($tipo, $silenciados)):
                    $q_no_vinc = "SELECT COUNT(*) as total FROM usuarios WHERE id_rol = 4 AND (id_juez_v3 IS NULL OR id_juez_v3 = 0) AND activo = 1";
                    $res_no_vinc = mysqli_query($connection, $q_no_vinc);
                    $no_vinc_data = mysqli_fetch_assoc($res_no_vinc);
                    $no_vinc_users = $no_vinc_data['total'];
                    if($no_vinc_users > 0):
                ?>
                <div class="relative group">
                    <a href="usuarios.php" class="block bg-white p-6 rounded-[2rem] border border-slate-200 border-l-[8px] border-l-amber-400 shadow-sm hover:shadow-md transition-all no-underline">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center text-amber-500 group-hover:scale-110 transition-all"><i class="fas fa-link-slash text-xl"></i></div>
                            <div class="flex flex-col items-end">
                                <span class="text-[9px] font-black uppercase text-slate-400 tracking-widest italic">Acción Requerida</span>
                                <?php echo renderSilenciar($tipo); ?>
                            </div>
                        </div>
                        <h4 class="text-xl font-black text-slate-800 leading-tight mb-2">Vinculación de Jueces</h4>
                        <p class="text-sm font-bold text-slate-500 italic">Hay <span class="text-amber-600 font-black"><?php echo $no_vinc_users; ?></span> usuarios con rol Juez sin perfil oficial vinculado.</p>
                    </a>
                </div>
                <?php endif; endif; ?>

                <!-- 2. Auditorías Bias Pendientes -->
                <?php
                $q_bias = "SELECT id, nombre, fecha FROM competiciones WHERE fecha < CURDATE() AND id NOT IN (SELECT DISTINCT id_competicion FROM auditoria_jueces_stats) ORDER BY fecha DESC LIMIT 3";
                $res_bias = mysqli_query($connection, $q_bias);
                while($b = mysqli_fetch_assoc($res_bias)):
                    $tipo = 'BIAS_PENDIENTE_'.$b['id'];
                    if(!in_array($tipo, $silenciados)):
                ?>
                <div class="relative group">
                    <a href="analisis_jueces.php?comp_id=<?php echo $b['id']; ?>" class="block bg-white p-6 rounded-[2rem] border border-slate-200 border-l-[8px] border-l-red-500 shadow-sm hover:shadow-md transition-all no-underline">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-12 h-12 rounded-xl bg-red-50 flex items-center justify-center text-red-600 group-hover:scale-110 transition-all"><i class="fas fa-magnifying-glass-chart text-xl"></i></div>
                            <div class="flex flex-col items-end">
                                <span class="text-[9px] font-black uppercase text-slate-400 tracking-widest">Finalizó <?php echo date("d-m", strtotime($b['fecha'])); ?></span>
                                <?php echo renderSilenciar($tipo); ?>
                            </div>
                        </div>
                        <h4 class="text-xl font-black text-slate-800 leading-tight mb-2">Falta Análisis BIAS</h4>
                        <p class="text-sm font-bold text-slate-500 italic">La competición <span class="text-red-600 font-black"><?php echo $b['nombre']; ?></span> ha finalizado y no tiene auditoría grabada.</p>
                    </a>
                </div>
                <?php endif; endwhile; ?>

                <!-- 3. Plazos de Inscripción -->
                <?php
                $q_insc = "SELECT id, nombre, fecha, dias_fin_inscripcion FROM competiciones WHERE DATE_SUB(fecha, INTERVAL (dias_fin_inscripcion - 1) DAY) <= CURDATE() AND DATE_SUB(fecha, INTERVAL dias_fin_inscripcion DAY) >= DATE_SUB(CURDATE(), INTERVAL 5 DAY) AND fecha >= CURDATE()";
                $res_insc = mysqli_query($connection, $q_insc);
                while($i = mysqli_fetch_assoc($res_insc)):
                    $tipo = 'INSCRIPCION_CIERRE_'.$i['id'];
                    if(!in_array($tipo, $silenciados)):
                        $fecha_fin_insc = date("Y-m-d", strtotime("-".($i['dias_fin_inscripcion'] - 1)." days", strtotime($i['fecha'])));
                ?>
                <div class="relative group">
                    <div class="bg-white p-6 rounded-[2rem] border border-slate-200 border-l-[8px] border-l-blue-500 shadow-sm hover:shadow-md transition-all group">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 group-hover:scale-110 transition-all"><i class="fas fa-file-export text-xl"></i></div>
                            <div class="flex flex-col items-end">
                                <span class="text-[9px] font-black uppercase text-slate-400 tracking-widest">Plazo: <?php echo date("d-m", strtotime($fecha_fin_insc)); ?></span>
                                <?php echo renderSilenciar($tipo); ?>
                            </div>
                        </div>
                        <h4 class="text-xl font-black text-slate-800 leading-tight mb-2">Enviar Preinscripciones</h4>
                        <p class="text-sm font-bold text-slate-500 italic">El plazo para <span class="text-blue-600 font-black"><?php echo $i['nombre']; ?></span> termina hoy. Es necesario enviar la documentación oficial.</p>
                    </div>
                </div>
                <?php endif; endwhile; ?>

                <!-- 4. Sorteos Próximos -->
                <?php
                $q_sorteo_task = "SELECT id, nombre, fecha, dias_sorteo FROM competiciones WHERE DATE_SUB(fecha, INTERVAL dias_sorteo DAY) <= DATE_ADD(CURDATE(), INTERVAL 2 DAY) AND DATE_SUB(fecha, INTERVAL dias_sorteo DAY) >= CURDATE() AND fecha >= CURDATE()";
                $res_sorteo_task = mysqli_query($connection, $q_sorteo_task);
                while($s = mysqli_fetch_assoc($res_sorteo_task)):
                    $tipo = 'SORTEO_PROXIMO_'.$s['id'];
                    if(!in_array($tipo, $silenciados)):
                        $fecha_sorteo_task = date("Y-m-d", strtotime("-".$s['dias_sorteo']." days", strtotime($s['fecha'])));
                ?>
                <div class="relative group">
                    <a href="fases.php" class="block bg-white p-6 rounded-[2rem] border border-slate-200 border-l-[8px] border-l-emerald-500 shadow-sm hover:shadow-md transition-all no-underline">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 group-hover:scale-110 transition-all"><i class="fas fa-dice text-xl"></i></div>
                            <div class="flex flex-col items-end">
                                <span class="text-[9px] font-black uppercase text-slate-400 tracking-widest"><?php echo date("d-m", strtotime($fecha_sorteo_task)); ?></span>
                                <?php echo renderSilenciar($tipo); ?>
                            </div>
                        </div>
                        <h4 class="text-xl font-black text-slate-800 leading-tight mb-2">Preparar Sorteo</h4>
                        <p class="text-sm font-bold text-slate-500 italic">El sorteo de <span class="text-emerald-600 font-black"><?php echo $s['nombre']; ?></span> es inminente. Verifica las fases y el orden.</p>
                    </a>
                </div>
                <?php endif; endwhile; ?>

            </div>
        </div>
        <?php endif; // Fin in_array GLOBAL_TASKS ?>
        <?php endif; // Fin id_rol == 1 ?>


        <!-- SECCIÓN: PRÓXIMAS COMPETICIONES -->
        <div class="space-y-12 mb-20">
            <div class="flex items-center gap-4 border-l-[8px] border-blue-600 pl-6 py-2">
                <h2 class="text-3xl font-black text-slate-800 uppercase tracking-tighter italic">
                    Próximas Competiciones
                </h2>
            </div>

            <?php
            $query = 'SELECT * FROM competiciones WHERE fecha >= (SELECT CURDATE() AS Today) ORDER BY fecha asc';
            $query_run = mysqli_query($connection,$query);
            if(mysqli_num_rows($query_run) > 0){
                while ($row = mysqli_fetch_assoc($query_run)):
                    $color = (!empty($row['color'])) ? $row['color'] : '#3b82f6';
            ?>
                <div class="bg-white rounded-[3rem] p-8 md:p-12 shadow-sm border-x border-b border-slate-200 border-t-[12px] relative overflow-hidden group transition-all hover:shadow-xl" style="border-top-color: <?php echo $color; ?> !important;">
                    <div class="absolute top-0 right-0 w-80 h-80 bg-slate-50 rounded-full -mr-40 -mt-40 group-hover:scale-110 transition-transform duration-700 opacity-50"></div>
                    
                    <div class="relative z-10">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8 mb-12">
                            <div class="flex items-center gap-8">
                                <div class="w-24 h-24 rounded-[2rem] flex flex-col items-center justify-center text-white shadow-2xl shadow-blue-500/20 shrink-0" style="background-color: <?php echo $color; ?>;">
                                    <span class="text-xs font-black uppercase opacity-70"><?php echo $meses_es[date('M', strtotime($row['fecha']))]; ?></span>
                                    <span class="text-4xl font-black"><?php echo date('d', strtotime($row['fecha'])); ?></span>
                                </div>
                                <div>
                                    <h3 class="text-3xl md:text-4xl font-black text-slate-800 tracking-tighter italic leading-tight group-hover:text-blue-600 transition-colors"><?php echo $row['nombre']; ?></h3>
                                    <div class="flex flex-wrap items-center gap-6 mt-4">
                                        <p class="text-base font-bold text-slate-400 flex items-center gap-2"><i class="fas fa-map-marker-alt text-blue-500"></i> <?php echo $row['lugar']; ?></p>
                                        <a href="<?php echo $row['maps']; ?>" target="_blank" class="px-4 py-2 rounded-xl bg-slate-50 text-slate-500 hover:bg-slate-800 hover:text-white transition-all flex items-center gap-2 border border-slate-100 shadow-sm font-black text-xs uppercase italic"><i class="fa-solid fa-map-location-dot"></i> Ver Mapa</a>
                                    </div>
                                    <?php if(!empty($row['mensaje'])): ?>
                                    <div class="mt-6 p-4 bg-amber-50 border-l-4 border-amber-500 rounded-r-2xl text-amber-700 text-sm font-medium">
                                        <div class="flex items-start gap-3">
                                            <i class="fas fa-info-circle mt-1"></i>
                                            <p><?php echo nl2br(htmlspecialchars($row['mensaje'])); ?></p>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row items-center gap-6">
                                <?php
                                $fecha_inicio_inscripcion = date("Y-m-d", strtotime("-".$row['dias_inicio_inscripcion']." days", strtotime($row['fecha'])));
                                $fecha_fin_inscripcion = date("Y-m-d", strtotime("-".($row['dias_fin_inscripcion'])." days", strtotime($row['fecha'])));
                                // Ajuste para mostrar el último día real
                                $fecha_fin_inscripcion_visible = date("Y-m-d", strtotime("-1 day", strtotime($fecha_fin_inscripcion)));

                                $fecha_sorteo = date("Y-m-d", strtotime("-".$row['dias_sorteo']." days", strtotime($row['fecha'])));
                                $hoy = date("Y-m-d");
                                $enlace_inscripcion = ($row['figuras'] == 'si') ? "./inscripciones_figuras.php" : "./rutinas.php";

                                if($hoy >= $fecha_inicio_inscripcion && $hoy <= $fecha_fin_inscripcion_visible): ?>
                                    <form action="<?php echo $enlace_inscripcion;?>" method="post" class="w-full sm:w-auto text-center">
                                        <div class="mb-4 flex flex-col items-center gap-2">
                                            <span class="text-xs font-black text-emerald-600 uppercase tracking-widest bg-emerald-50 px-5 py-2 rounded-full border border-emerald-100 shadow-sm animate-pulse italic">Inscripciones Abiertas</span>
                                            <p class="text-[10px] font-bold text-slate-400 uppercase italic">Plazo Hasta: <?php echo dateAFecha($fecha_fin_inscripcion_visible); ?></p>
                                        </div>
                                        <input type="hidden" name="id_competicion" value="<?php echo $row['id'];?>">
                                        <input type="hidden" name="nombre_competicion" value="<?php echo $row['nombre'];?>">
                                        <input type="hidden" name="competicion_figuras" value="<?php echo $row['figuras'];?>">
                                        <button type="submit" name="inscripciones" class="w-full px-12 py-5 bg-slate-800 text-white font-black uppercase text-sm tracking-widest rounded-[2rem] shadow-xl hover:bg-black hover:scale-105 transition-all flex items-center justify-center gap-4">
                                            <i class="fas fa-user-plus text-lg"></i> Gestionar Inscripciones
                                        </button>
                                    </form>
                                <?php elseif($hoy < $fecha_inicio_inscripcion): ?>
                                    <div class="px-10 py-5 bg-slate-50 text-slate-400 font-black uppercase text-sm tracking-widest rounded-[2rem] border border-slate-100 text-center italic">
                                        <i class="fas fa-lock mr-2 opacity-50"></i> Apertura: <?php echo dateAFecha($fecha_inicio_inscripcion); ?>
                                    </div>
                                <?php else: ?>
                                    <form action="<?php echo $enlace_inscripcion;?>" method="post" class="w-full sm:w-auto text-center">
                                        <div class="mb-4 flex flex-col items-center gap-2">
                                            <span class="text-xs font-black text-red-500 uppercase tracking-widest bg-red-50 px-5 py-2 rounded-full border border-red-100 shadow-sm italic">Inscripciones Cerradas</span>
                                            <p class="text-[10px] font-bold text-slate-400 uppercase italic">Finalizó el: <?php echo dateAFecha($fecha_fin_inscripcion_visible); ?></p>
                                        </div>
                                        <input type="hidden" name="id_competicion" value="<?php echo $row['id'];?>">
                                        <input type="hidden" name="nombre_competicion" value="<?php echo $row['nombre'];?>">
                                        <input type="hidden" name="competicion_figuras" value="<?php echo $row['figuras'];?>">
                                        <button type="submit" name="inscripciones" class="w-full px-12 py-5 bg-slate-100 text-slate-500 font-black uppercase text-sm tracking-widest rounded-[2rem] shadow-sm hover:bg-slate-200 hover:text-slate-700 transition-all flex items-center justify-center gap-4 border border-slate-200">
                                            <i class="fas fa-eye text-lg"></i> Comprobar Inscripciones
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <!-- Plazos Técnicos (Música y Coach Card) -->
                                <?php 
                                    $id_c_row = $row['id'];
                                    $q_has_cc = "SELECT COUNT(*) as num FROM fases WHERE id_competicion = $id_c_row AND elementos_coach_card > 0";
                                    $has_cc = (mysqli_result(mysqli_query($connection, $q_has_cc), 0) > 0);
                                    
                                    // Determinar número de columnas
                                    $cols = 0;
                                    if ($row['figuras'] != 'si') $cols++; // Música
                                    if ($has_cc) $cols++; // Coach Card
                                ?>
                                <?php if($cols > 0): ?>
                                <div class="grid <?php echo ($cols == 1) ? 'grid-cols-1' : 'grid-cols-2'; ?> gap-4 w-full sm:w-auto">
                                    <?php 
                                    $f_musica = date("Y-m-d", strtotime("-".$row['dias_musica']." days", strtotime($row['fecha'])));
                                    if($row['figuras'] == 'si') {
                                        $f_cc = $fecha_fin_inscripcion;
                                    } else {
                                        $f_cc = date("Y-m-d", strtotime("-".$row['dias_coach_card']." days", strtotime($row['fecha'])));
                                    }
                                    ?>
                                    
                                    <?php if($row['figuras'] != 'si'): ?>
                                    <div class="p-4 rounded-3xl border border-slate-100 bg-slate-50/50 flex flex-col items-center justify-center text-center">
                                        <i class="fas fa-music text-slate-300 mb-2"></i>
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">Música</p>
                                        <p class="text-xs font-black text-slate-700"><?php echo dateAFecha($f_musica); ?></p>
                                    </div>
                                    <?php endif; ?>

                                    <?php if($has_cc): ?>
                                    <div class="p-4 rounded-3xl border border-slate-100 bg-slate-50/50 flex flex-col items-center justify-center text-center">
                                        <i class="fas fa-puzzle-piece text-slate-300 mb-2"></i>
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">Coach Card</p>
                                        <p class="text-xs font-black text-slate-700"><?php echo dateAFecha($f_cc); ?></p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>

                                <?php if(!empty($row['enlace_sorteo'])): ?>
                                    <div class="text-center">
                                        <p class="text-xs font-black text-slate-400 uppercase mb-3">Sorteo: <?php echo dateAFecha($fecha_sorteo); ?></p>
                                        <a href="<?php echo $row['enlace_sorteo'];?>" target="_blank" class="px-10 py-5 bg-blue-600 text-white font-black uppercase text-sm tracking-widest rounded-[2rem] shadow-lg shadow-blue-500/20 hover:bg-blue-700 hover:scale-105 transition-all flex items-center gap-4">
                                            <i class="fa-solid fa-video text-lg"></i> Unirse al Sorteo
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Tabla Detallada de Fases -->
                        <div class="rounded-[2rem] border border-slate-100 bg-slate-50/30 overflow-hidden mb-12 shadow-inner">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-100/50 text-sm font-black text-slate-400 uppercase tracking-widest italic border-b border-slate-200/50">
                                        <th class="px-8 py-5">Categoría</th>
                                        <th class="px-6 py-5"><?php echo ($row['figuras'] == 'si') ? 'Figura' : 'Modalidad'; ?></th>
                                        <th class="px-6 py-5 text-center"><?php echo ($row['figuras'] == 'si') ? 'G.D.' : 'Participantes'; ?></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php
                                    if($row['figuras']=='si'){
                                        $q_fases = "SELECT c.nombre as cat, f.nombre as fig, f.numero, f.grado_dificultad as gd FROM fases fs, categorias c, figuras f WHERE fs.id_categoria = c.id and fs.id_figura = f.id and fs.id_competicion = ".$row['id']." ORDER BY fs.orden";
                                    } else {
                                        $q_fases = "SELECT c.nombre as cat, m.nombre as modali, m.numero_participantes as np, m.numero_reservas as nr FROM fases fs, categorias c, modalidades m WHERE fs.id_categoria = c.id and fs.id_modalidad = m.id and fs.id_competicion = ".$row['id']." ORDER BY fs.orden";
                                    }
                                    $res_fases = mysqli_query($connection, $q_fases);
                                    if(mysqli_num_rows($res_fases) > 0){
                                        while($f = mysqli_fetch_assoc($res_fases)):
                                    ?>
                                        <tr class="text-sm md:text-base font-bold text-slate-600 hover:bg-white transition-colors">
                                            <td class="px-8 py-6 font-black text-slate-700 italic uppercase tracking-tighter"><?php echo $f['cat']; ?></td>
                                            <td class="px-6 py-6"><?php echo ($row['figuras'] == 'si') ? '<span class="px-3 py-1 bg-slate-100 rounded-lg text-slate-400 mr-3 font-black">#'.$f['numero'].'</span> '.$f['fig'] : $f['modali']; ?></td>
                                            <td class="px-6 py-6 text-center">
                                                <span class="px-5 py-2 bg-blue-50 text-blue-600 rounded-full font-black text-xs shadow-sm border border-blue-100">
                                                    <?php echo ($row['figuras'] == 'si') ? $f['gd'] : $f['np'].' + '.$f['nr'].'R'; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endwhile; } else { ?>
                                        <tr><td colspan="3" class="px-8 py-8 text-center text-sm italic text-slate-400 font-bold">Sin fases definidas para esta competición.</td></tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Documentación Adjunta -->
                        <div class="flex flex-wrap gap-5">
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
                                <a href="<?php echo $filename; ?>" target="_blank" class="px-6 py-3 bg-white text-slate-600 hover:bg-slate-800 hover:text-white rounded-[1.25rem] border border-slate-100 text-xs font-black uppercase tracking-tighter transition-all flex items-center gap-4 shadow-sm group/doc">
                                    <i class="fa-solid <?php echo $meta['icon']; ?> text-xl text-slate-300 group-hover/doc:text-white transition-colors"></i> <?php echo $meta['label']; ?>
                                </a>
                            <?php 
                                endif; 
                            endforeach; 
                            ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; } else { ?>
                <div class="p-20 bg-white rounded-[3rem] border border-slate-100 text-center shadow-sm">
                    <div class="w-24 h-24 rounded-full bg-slate-50 flex items-center justify-center text-slate-200 mx-auto mb-8"><i class="fas fa-calendar-times text-4xl"></i></div>
                    <p class="text-slate-400 font-black italic uppercase tracking-widest text-base">No existen competiciones programadas</p>
                </div>
            <?php } ?>
        </div>

        <!-- SECCIÓN: EVENTOS EN CALENDARIO (COMPACTO) -->
        <div class="mb-20">
            <h2 class="text-3xl font-black text-slate-800 flex items-center gap-4 uppercase tracking-tighter italic mb-10">
                <i class="fas fa-trophy text-emerald-500"></i> Eventos en Calendario
            </h2>
            <div class="bg-white rounded-[3rem] p-8 md:p-12 shadow-sm border border-slate-200 border-t-[10px] border-t-emerald-500">
                <div class="space-y-6">
                    <?php
                    $query = 'select * FROM competiciones WHERE fecha >= (SELECT CURDATE() + 1) ORDER BY fecha asc';
                    $query_run = mysqli_query($connection,$query);
                    if(mysqli_num_rows($query_run) > 0){
                        while ($row = mysqli_fetch_assoc($query_run)):
                    ?>
                        <div class="flex flex-col md:flex-row md:items-center justify-between p-8 rounded-[2.5rem] bg-slate-50 border border-slate-100 hover:bg-white hover:shadow-lg transition-all group gap-8">
                            <div class="flex items-center gap-8">
                                <div class="w-16 h-16 rounded-2xl oceanic-gradient flex flex-col items-center justify-center text-white shadow-lg shrink-0">
                                    <span class="text-[11px] font-black uppercase opacity-70"><?php echo $meses_es[date('M', strtotime($row['fecha']))]; ?></span>
                                    <span class="text-xl font-black"><?php echo date('d', strtotime($row['fecha'])); ?></span>
                                </div>
                                <div>
                                    <h4 class="text-xl md:text-2xl font-black text-slate-800 leading-tight group-hover:text-blue-600 transition-colors italic"><?php echo $row['nombre']; ?></h4>
                                    <p class="text-sm font-bold text-slate-400 uppercase tracking-tighter mt-2 flex items-center gap-3"><i class="fas fa-map-marker-alt text-blue-500 text-xs"></i> <?php echo $row['lugar']; ?></p>
                                    <?php if(!empty($row['mensaje'])): ?>
                                    <div class="mt-3 p-3 bg-amber-50 border-l-4 border-amber-500 rounded-r-xl text-amber-700 text-xs font-medium max-w-lg">
                                        <div class="flex items-start gap-2">
                                            <i class="fas fa-info-circle mt-0.5"></i>
                                            <p><?php echo nl2br(htmlspecialchars($row['mensaje'])); ?></p>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="flex items-center gap-4 self-end md:self-center">
                                <a href="<?php echo $row['maps']; ?>" target="_blank" class="w-12 h-12 rounded-2xl bg-white border border-slate-200 text-slate-300 hover:text-blue-500 flex items-center justify-center transition-all shadow-sm"><i class="fa-solid fa-map-location-dot text-lg"></i></a>
                            </div>
                        </div>
                    <?php endwhile; } else { ?>
                        <div class="text-center p-12 text-sm font-bold text-slate-400 uppercase italic">No existen eventos programados próximamente.</div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <!-- SECCIÓN: HISTORIAL (CON VIDILLA) -->
        <div>
            <div class="flex items-center gap-4 border-l-[8px] border-slate-400 pl-6 py-2 mb-10">
                <h2 class="text-3xl font-black text-slate-800 uppercase tracking-tighter italic">
                    Historial
                </h2>
            </div>
            
            <div class="bg-white rounded-[3rem] shadow-sm border border-slate-200 border-t-[12px] border-t-slate-400 overflow-hidden mb-12">
                <div class="min-w-0">
                    <table class="w-full text-left border-collapse">
                        <thead class="hidden md:table-header-group">
                            <tr class="bg-slate-50/50 text-sm font-black text-slate-400 uppercase tracking-[0.2em] italic border-b border-slate-100">
                                <th class="px-10 py-6 w-32">Fecha</th>
                                <th class="px-6 py-6">Evento y Localidad</th>
                                <th class="px-6 py-6">Documentación Oficial</th>
                                <th class="px-10 py-6 text-center w-24">Mapa</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y-8 divide-slate-50">
                            <?php
                            $query = 'select * FROM competiciones WHERE fecha < (SELECT CURDATE()) ORDER BY fecha desc LIMIT 10';
                            $query_run = mysqli_query($connection,$query);
                            while ($row = mysqli_fetch_assoc($query_run)):
                            ?>
                            <tr class="hover:bg-slate-50/50 transition-colors flex flex-col md:table-row p-8 md:p-0 border-b-2 border-slate-100">
                                
                                <!-- Columna 1: Fecha (Al principio) -->
                                <td class="px-0 md:px-10 py-0 md:py-10 mb-6 md:mb-0">
                                    <div class="flex flex-col items-center justify-center w-20 h-24 bg-slate-800 rounded-[1.5rem] shadow-lg border border-slate-700">
                                        <span class="text-[10px] font-black text-slate-400 uppercase leading-none mb-1"><?php echo $meses_es[date("M", strtotime($row['fecha']))]; ?></span>
                                        <span class="text-3xl font-black text-white leading-tight"><?php echo date("d", strtotime($row['fecha'])); ?></span>
                                        <span class="text-xs font-black text-slate-500 tracking-widest leading-none mt-2"><?php echo date("Y", strtotime($row['fecha'])); ?></span>
                                    </div>
                                </td>

                                <!-- Columna 2: Nombre y Localidad (Con mapa antes) -->
                                <td class="px-0 md:px-6 py-0 md:py-8 mb-6 md:mb-0">
                                    <div>
                                        <h4 class="text-xl md:text-xl font-black text-slate-900 leading-tight italic uppercase tracking-tighter mb-2"><?php echo $row['nombre']; ?></h4>
                                        <div class="flex items-center gap-2">
                                            <i class="fa-solid fa-map-location-dot text-blue-500 text-sm"></i>
                                            <p class="text-sm font-bold text-slate-500 italic"><?php echo $row['lugar']; ?></p>
                                        </div>
                                        <?php if(!empty($row['mensaje'])): ?>
                                        <div class="mt-3 p-3 bg-amber-50 border-l-4 border-amber-500 rounded-r-xl text-amber-700 text-xs font-medium max-w-lg">
                                            <div class="flex items-start gap-2">
                                                <i class="fas fa-info-circle mt-0.5"></i>
                                                <p><?php echo nl2br(htmlspecialchars($row['mensaje'])); ?></p>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- Columna 3: Documentación (Colores Fijos) -->
                                <td class="px-0 md:px-6 py-0 md:py-8 mb-8 md:mb-0">
                                    <div class="flex gap-6 flex-wrap">
                                        <?php if(file_exists('./docs/'.$row['id'].'-normativa.pdf')): ?>
                                            <a href="./docs/<?php echo $row['id']; ?>-normativa.pdf" target="_blank" title="Normativa" class="text-indigo-500 hover:scale-110 transition-transform"><i class="fas fa-file-shield text-3xl"></i></a>
                                        <?php endif; ?>
                                        <?php if(file_exists('./docs/'.$row['id'].'-inscripciones.pdf')): ?>
                                            <a href="./docs/<?php echo $row['id']; ?>-inscripciones.pdf" target="_blank" title="Listado Inscripciones" class="text-blue-500 hover:scale-110 transition-transform"><i class="fas fa-file-pen text-3xl"></i></a>
                                        <?php endif; ?>
                                        <?php if(file_exists('./docs/'.$row['id'].'-orden.pdf')): ?>
                                            <a href="./docs/<?php echo $row['id']; ?>-orden.pdf" target="_blank" title="Orden de Salida" class="text-slate-600 hover:scale-110 transition-transform"><i class="fas fa-list-ol text-3xl"></i></a>
                                        <?php endif; ?>
                                        <?php if(file_exists('./docs/'.$row['id'].'-resultados.pdf')): ?>
                                            <a href="./docs/<?php echo $row['id']; ?>-resultados.pdf" target="_blank" title="Resultados Finales" class="text-amber-500 hover:scale-110 transition-transform"><i class="fas fa-trophy text-3xl"></i></a>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- Columna 4: Ubicación (Desktop) -->
                                <td class="hidden md:table-cell px-10 py-8 text-center">
                                    <a href="<?php echo $row['maps']; ?>" target="_blank" class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-500 hover:bg-blue-600 hover:text-white transition-all flex items-center justify-center border border-blue-100 shadow-sm mx-auto"><i class="fa-solid fa-map-location-dot text-lg"></i></a>
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
        menu.classList.toggle('hidden');
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
                    section.style.transition = 'all 0.8s ease';
                    section.style.opacity = '0';
                    section.style.transform = 'translateY(-20px)';
                    setTimeout(() => { section.style.display = 'none'; }, 800);
                } else {
                    // Animación de salida y ocultar la tarjeta individual
                    const menu = document.getElementById('menu-' + tipo);
                    if(menu) {
                        const card = menu.closest('.relative.group');
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
