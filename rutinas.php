<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');

// Sincronizar ID de competición (prioridad usuario, fallback solo si es admin)
$id_competicion = (isset($_SESSION['id_competicion_usuario']) && !empty($_SESSION['id_competicion_usuario'])) ? $_SESSION['id_competicion_usuario'] : 0;

if ($id_competicion == 0 && $_SESSION['id_rol'] == 1 && isset($_SESSION['id_competicion_activa'])) {
    $id_competicion = $_SESSION['id_competicion_activa'];
}

if ($id_competicion == 0) {
    echo "<div class='p-10 text-center font-lexend bg-white rounded-[2.5rem] shadow-sm border border-slate-200 mt-10 max-w-2xl mx-auto'>
            <div class='w-20 h-20 bg-blue-50 text-blue-500 rounded-[2rem] flex items-center justify-center text-3xl mx-auto mb-6 shadow-sm border border-blue-100'>
                <i class='fas fa-trophy'></i>
            </div>
            <h2 class='text-2xl font-black text-slate-800 tracking-tighter'>Ninguna Competición Seleccionada</h2>
            <p class='text-slate-500 mt-2 font-medium'>Para gestionar rutinas, primero debes seleccionar una competición desde el panel principal.</p>
            <a href='index.php' class='mt-8 inline-flex items-center gap-3 px-8 py-4 bg-slate-800 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg hover:bg-black transition-all'>
                <i class='fas fa-home'></i> Ir al Dashboard
            </a>
          </div>";
    include('includes/footer.php');
    exit();
}

// Fetch deadlines
$query_fechas = 'SELECT date_add(fecha, interval -dias_musica day) as fecha_musica, 
                        date_add(fecha, interval -dias_coach_card day) as fecha_coach_card, 
                        date_add(fecha, interval -dias_sorteo day) as fecha_sorteo, 
                        date_add(fecha, interval -dias_inicio_inscripcion day) as fecha_inicio_inscripcion, 
                        date_add(fecha, interval -dias_fin_inscripcion day) as fecha_fin_inscripcion 
                 FROM competiciones WHERE id='.$id_competicion;
$res_fechas = mysqli_query($connection, $query_fechas);
$fechas = ($res_fechas) ? mysqli_fetch_assoc($res_fechas) : [];

$fecha_musica_raw = $fechas['fecha_musica'] ?? '';
// Restamos 1 día para mostrar el último día real de plazo
$fecha_musica = !empty($fecha_musica_raw) ? date('Y-m-d', strtotime('-1 day', strtotime($fecha_musica_raw))) : '';
$enable_musica = (date('Y-m-d') > $fecha_musica && $_SESSION['id_rol'] != 1) ? 'disabled' : '';

if($figuras == 'si') {
    $fecha_coach_card_raw = $fechas['fecha_fin_inscripcion'] ?? '';
} else {
    $fecha_coach_card_raw = $fechas['fecha_coach_card'] ?? '';
}
$fecha_coach_card = !empty($fecha_coach_card_raw) ? date('Y-m-d', strtotime('-1 day', strtotime($fecha_coach_card_raw))) : '';
$enable_coach_card = (date('Y-m-d') > $fecha_coach_card && $_SESSION['id_rol'] != 1) ? 'disabled' : '';

$fecha_fin_inscripcion_raw = $fechas['fecha_fin_inscripcion'] ?? '';
$fecha_fin_inscripcion = !empty($fecha_fin_inscripcion_raw) ? date('Y-m-d', strtotime('-1 day', strtotime($fecha_fin_inscripcion_raw))) : '';
$enable_inscripcion = (date('Y-m-d') > $fecha_fin_inscripcion && $_SESSION['id_rol'] != 1) ? 'disabled' : '';

// Condición por club
$condicion_club = '';
if(isset($_SESSION['club']) && $_SESSION['club'] > 0 && $_SESSION['id_rol'] == 5)
    $condicion_club = ' AND rutinas.id_club ='.$_SESSION['club'];

// Stats para KPIs
$q_stats = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN music_name IS NOT NULL AND music_name != '' THEN 1 ELSE 0 END) as con_musica,
            SUM(CASE WHEN dd_total > 0 THEN 1 ELSE 0 END) as con_coach_card,
            SUM(CASE WHEN fases.elementos_coach_card > 0 THEN 1 ELSE 0 END) as requieren_coach_card
            FROM rutinas, fases 
            WHERE rutinas.id_fase = fases.id AND fases.id_competicion = $id_competicion $condicion_club";
$res_stats = mysqli_query($connection, $q_stats);
$stats = mysqli_fetch_assoc($res_stats);

$has_coach_cards = (($stats['requieren_coach_card'] ?? 0) > 0);
?>

<main class="flex-1 flex flex-col min-w-0 bg-surface">
    <?php include('includes/topbar.php'); ?>

    <div class="px-3 py-4 sm:p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend text-primary">
        
        <!-- Header -->
        <div class="mb-6 sm:mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4 sm:gap-6">
            <div>
                <h1 class="text-2xl sm:text-4xl font-black text-slate-800 tracking-tight mb-1 sm:mb-2 flex items-center gap-2.5 sm:gap-3">
                    <span class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-flag-checkered text-base sm:text-lg"></i></span>
                    Registro de Rutinas
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 font-medium">Gestión de inscripciones, música<?php echo $has_coach_cards ? ' y Coach Cards.' : '.'; ?></p>
            </div>
            <div class="flex flex-wrap items-center gap-2 sm:gap-3 w-full sm:w-auto">
                <?php if($_SESSION['id_rol'] != 5): ?>
                    <a target="_blank" href="./informes/inscripciones_numericas_rutinas.php?id_competicion=<?php echo $id_competicion?>&titulo=Inscripciones" class="px-3 sm:px-4 py-2 bg-white border border-slate-200 rounded-xl text-[11px] sm:text-xs font-black text-slate-600 hover:bg-slate-50 active:scale-[0.98] transition-all flex items-center gap-1.5 shadow-sm">
                        <i class="fas fa-file-pdf text-red-500"></i> <span class="hidden sm:inline">PDF</span> INSCRIPCIONES
                    </a>
                    <?php if($has_coach_cards): ?>
                        <a target="_blank" href="./informes/informe_coach_card.php?titulo=Coach%20Card%20Composer&id_competicion=<?php echo $id_competicion;?>" class="px-3 sm:px-4 py-2 bg-white border border-slate-200 rounded-xl text-[11px] sm:text-xs font-black text-slate-600 hover:bg-slate-50 active:scale-[0.98] transition-all flex items-center gap-1.5 shadow-sm">
                            <i class="fas fa-puzzle-piece text-amber-500"></i> <span class="hidden sm:inline">PDF</span> CC
                        </a>
                    <?php endif; ?>
                    <form action="./download_music.php" method="POST" target="_blank" class="inline">
                        <input type="hidden" name="id_competicion" value="<?php echo $id_competicion; ?>">
                        <button type="submit" class="px-3 sm:px-4 py-2 bg-white border border-slate-200 rounded-xl text-[11px] sm:text-xs font-black text-slate-600 hover:bg-slate-50 active:scale-[0.98] transition-all flex items-center gap-1.5 shadow-sm">
                            <i class="fas fa-music text-blue-500"></i> ZIP MÚSICA
                        </button>
                    </form>
                <?php else: ?>
                    <a target="_blank" href="./informes/inscripciones_numericas_rutinas.php?id_competicion=<?php echo $id_competicion?>&club=<?php echo $_SESSION['club']?>&titulo=Inscripciones <?php echo $_SESSION['nombre_club']?>" class="px-3 sm:px-4 py-2 bg-white border border-slate-200 rounded-xl text-[11px] sm:text-xs font-black text-slate-600 hover:bg-slate-50 active:scale-[0.98] transition-all flex items-center gap-1.5 shadow-sm">
                        <i class="fas fa-file-pdf text-red-500"></i> INSCRIPCIÓN
                    </a>
                    <?php if($has_coach_cards): ?>
                        <a target="_blank" href="./informes/informe_coach_card.php?titulo=Coach%20Card%20Composer&id_club=<?php echo $_SESSION['club'];?>&id_competicion=<?php echo $id_competicion;?>" class="px-3 sm:px-4 py-2 bg-white border border-slate-200 rounded-xl text-[11px] sm:text-xs font-black text-slate-600 hover:bg-slate-50 active:scale-[0.98] transition-all flex items-center gap-1.5 shadow-sm">
                            <i class="fas fa-puzzle-piece text-amber-500"></i> COACH CARDS
                        </a>
                    <?php endif; ?>
                    <form action="./download_music.php" method="POST" target="_blank" class="inline">
                        <input type="hidden" name="id_competicion" value="<?php echo $id_competicion; ?>">
                        <input type="hidden" name="id_club" value="<?php echo $_SESSION['club']; ?>">
                        <button type="submit" class="px-3 sm:px-4 py-2 bg-white border border-slate-200 rounded-xl text-[11px] sm:text-xs font-black text-slate-600 hover:bg-slate-50 active:scale-[0.98] transition-all flex items-center gap-1.5 shadow-sm">
                            <i class="fas fa-music text-blue-500"></i> MI MÚSICA
                        </button>
                    </form>
                <?php endif; ?>
                
                <button onclick="toggleAddRutinaPanel()" <?php echo $enable_inscripcion ?> class="flex-1 sm:flex-initial justify-center px-4 sm:px-6 py-2.5 sm:py-3 bg-blue-600 text-white font-black uppercase text-[11px] sm:text-xs tracking-wider rounded-xl sm:rounded-2xl shadow-lg active:scale-[0.98] transition-all flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-plus text-xs"></i> Añadir Rutina
                </button>
            </div>
        </div>

        <?php include('includes/alertas_v4.php'); ?>

        <!-- Plazos & KPIs -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:<?php echo $has_coach_cards ? 'grid-cols-5' : 'grid-cols-3'; ?> gap-2.5 sm:gap-6 mb-6 sm:mb-12">
            <!-- Plazo Inscripción -->
            <div class="bg-white p-3.5 sm:p-6 rounded-xl sm:rounded-[2rem] shadow-sm border border-slate-200/80 border-l-4 sm:border-l-[6px] <?php echo ($enable_inscripcion == 'disabled') ? 'border-l-red-500' : 'border-l-blue-500'; ?> group hover:shadow-md transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-wider mb-1">Inscripción</p>
                <h3 class="text-sm sm:text-xl font-black <?php echo ($enable_inscripcion == 'disabled') ? 'text-red-600' : 'text-slate-800'; ?> tabular-nums"><?php echo dateAFecha($fecha_fin_inscripcion); ?></h3>
                <p class="text-[9px] sm:text-[10px] font-bold text-slate-400 mt-1 sm:mt-2 uppercase"><?php echo ($enable_inscripcion == 'disabled') ? 'Cerrado' : 'Abierto'; ?></p>
            </div>
            <!-- Plazo Música -->
            <div class="bg-white p-3.5 sm:p-6 rounded-xl sm:rounded-[2rem] shadow-sm border border-slate-200/80 border-l-4 sm:border-l-[6px] <?php echo ($enable_musica == 'disabled') ? 'border-l-red-500' : 'border-l-indigo-500'; ?> group hover:shadow-md transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-wider mb-1">Música</p>
                <h3 class="text-sm sm:text-xl font-black <?php echo ($enable_musica == 'disabled') ? 'text-red-600' : 'text-slate-800'; ?> tabular-nums"><?php echo dateAFecha($fecha_musica); ?></h3>
                <p class="text-[9px] sm:text-[10px] font-bold text-slate-400 mt-1 sm:mt-2 uppercase"><?php echo ($enable_musica == 'disabled') ? 'Cerrado' : 'Abierto'; ?></p>
            </div>
            <!-- Plazo Coach Card -->
            <?php if($has_coach_cards): ?>
                <div class="bg-white p-3.5 sm:p-6 rounded-xl sm:rounded-[2rem] shadow-sm border border-slate-200/80 border-l-4 sm:border-l-[6px] <?php echo ($enable_coach_card == 'disabled') ? 'border-l-red-500' : 'border-l-amber-500'; ?> group hover:shadow-md transition-all">
                    <p class="text-[9px] font-black uppercase text-slate-400 tracking-wider mb-1">Coach Card</p>
                    <h3 class="text-sm sm:text-xl font-black <?php echo ($enable_coach_card == 'disabled') ? 'text-red-600' : 'text-slate-800'; ?> tabular-nums"><?php echo dateAFecha($fecha_coach_card); ?></h3>
                    <p class="text-[9px] sm:text-[10px] font-bold text-slate-400 mt-1 sm:mt-2 uppercase"><?php echo ($enable_coach_card == 'disabled') ? 'Cerrado' : 'Abierto'; ?></p>
                </div>
            <?php endif; ?>
            <!-- KPI Total -->
            <div class="bg-white p-3.5 sm:p-6 rounded-xl sm:rounded-[2rem] shadow-sm border border-slate-200/80 border-l-4 sm:border-l-[6px] border-l-purple-500 group hover:shadow-md transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-wider mb-1">Total Rutinas</p>
                <h3 class="text-sm sm:text-xl font-black text-purple-600 tabular-nums"><?php echo $stats['total'] ?? 0; ?></h3>
                <p class="text-[9px] sm:text-[10px] font-bold text-slate-400 mt-1 sm:mt-2 uppercase">Inscritas</p>
            </div>
            <!-- KPI Completado -->
            <div class="bg-white p-3.5 sm:p-6 rounded-xl sm:rounded-[2rem] shadow-sm border border-slate-200/80 border-l-4 sm:border-l-[6px] border-l-emerald-500 group hover:shadow-md transition-all col-span-2 sm:col-span-1">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-wider mb-1"><?php echo $has_coach_cards ? 'Música / CC' : 'Música'; ?></p>
                <div class="flex items-center gap-2">
                    <h3 class="text-sm sm:text-xl font-black text-emerald-600 tabular-nums" title="Música subida / Total rutinas">
                        <?php echo ($stats['con_musica'] ?? 0); ?><span class="text-slate-300 text-xs sm:text-sm font-medium">/<?php echo ($stats['total'] ?? 0); ?></span>
                    </h3>
                    <?php if($has_coach_cards): ?>
                        <span class="text-slate-200">|</span>
                        <h3 class="text-sm sm:text-xl font-black text-emerald-600 tabular-nums" title="Coach Cards definidas / Total necesarias">
                            <?php echo ($stats['con_coach_card'] ?? 0); ?><span class="text-slate-300 text-xs sm:text-sm font-medium">/<?php echo ($stats['requieren_coach_card'] ?? 0); ?></span>
                        </h3>
                    <?php endif; ?>
                </div>
                <p class="text-[9px] sm:text-[10px] font-bold text-slate-400 mt-1 sm:mt-2 uppercase"><?php echo $has_coach_cards ? 'Subidas / Definidas' : 'Subidas'; ?></p>
            </div>
        </div>

        <!-- Panel Añadir -->
        <div id="addRutinaPanel" class="hidden mb-6 sm:mb-12 animate-fade-in-down">
            <div class="bg-white rounded-2xl sm:rounded-[2.5rem] p-4 sm:p-8 shadow-xl border border-blue-100 relative overflow-hidden">
                <h2 class="text-lg sm:text-xl font-black text-slate-800 mb-4 sm:mb-8 flex items-center gap-2.5 sm:gap-3"><i class="fas fa-plus-circle text-blue-600"></i> Registrar Rutina</h2>
                <form action="rutinas_code.php" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-4 sm:gap-6">
                    <div class="md:col-span-6 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1"><?php echo ($figuras == 'si') ? 'Nadadora' : 'Club'; ?></label>
                        <?php 
                        if($figuras == 'si') {
                            ob_start(); include('includes/nadadoras_select_option.php');
                            echo str_replace('<select', '<select name="club" class="v3-select-fix"', preg_replace('/<label.*?>.*?<\/label>/i', '', ob_get_clean()));
                        } else {
                            ob_start(); include('includes/club_select_option.php');
                            echo str_replace('<select', '<select name="club" class="v3-select-fix"', preg_replace('/<label.*?>.*?<\/label>/i', '', ob_get_clean()));
                        }
                        ?>
                    </div>
                    <div class="md:col-span-6 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Fase / Categoría / Modalidad</label>
                        <?php 
                        ob_start(); include('includes/fases_select_option.php');
                        echo str_replace('<select', '<select name="id_fase" class="v3-select-fix"', preg_replace('/<label.*?>.*?<\/label>/i', '', ob_get_clean()));
                        ?>
                    </div>
                    <div class="md:col-span-12 flex justify-end gap-3 pt-3 sm:pt-4">
                        <input type="hidden" name="id_competicion" value="<?php echo $id_competicion?>">
                        <button type="button" onclick="toggleAddRutinaPanel()" class="px-5 sm:px-8 py-2.5 sm:py-3 bg-slate-100 text-slate-500 font-black uppercase text-xs tracking-wider rounded-xl sm:rounded-2xl hover:bg-slate-200 active:scale-[0.98] transition-all">Cancelar</button>
                        <button type="submit" name="save_btn" class="px-5 sm:px-8 py-2.5 sm:py-3 bg-blue-600 text-white font-black uppercase text-xs tracking-wider rounded-xl sm:rounded-2xl shadow-lg hover:bg-blue-700 active:scale-[0.98] transition-all">Guardar Rutina</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- LISTADO DE RUTINAS -->
        <div class="bg-white rounded-2xl sm:rounded-[2.5rem] shadow-sm border border-slate-200/80 overflow-hidden">
            <div class="p-4 sm:p-8 border-b border-slate-100 flex flex-col md:flex-row justify-between items-stretch md:items-center gap-3 sm:gap-4 bg-slate-50/40">
                <h3 class="text-base sm:text-lg font-black text-slate-800 flex items-center gap-2.5 sm:gap-3">
                    <span class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs sm:text-sm"><i class="fas fa-list-ul"></i></span>
                    Detalle de Inscripciones
                </h3>
                <div class="relative w-full md:w-80">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"><i class="fas fa-search text-xs"></i></span>
                    <input type="text" id="rutinaSearchInput" placeholder="Buscar por club, modalidad o nadadora..." 
                           class="w-full pl-9 pr-4 py-2 sm:py-2.5 bg-white border border-slate-200 rounded-xl sm:rounded-2xl text-xs font-bold text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all outline-none"
                           onkeyup="filterRutinas()">
                </div>
            </div>

            <?php
            function getReglamentaryRoutineDuration($nombre_modalidad, $nombre_categoria) {
                $mod = mb_strtolower($nombre_modalidad ?? '', 'UTF-8');
                $cat = mb_strtolower($nombre_categoria ?? '', 'UTF-8');
                $margin = 5;
                $targetSec = 0;
                $targetStr = '';

                if (strpos($mod, 'solo') !== false) {
                    if (strpos($mod, 'técnico') !== false || strpos($mod, 'tecnico') !== false) {
                        $targetSec = 120;
                        $targetStr = '2:00 min';
                    } else {
                        if (strpos($cat, 'junior') !== false || strpos($cat, 'absolut') !== false || strpos($cat, 'senior') !== false) {
                            $targetSec = 135;
                            $targetStr = '2:15 min';
                        } else {
                            $targetSec = 120;
                            $targetStr = '2:00 min';
                        }
                    }
                } elseif (strpos($mod, 'dúo') !== false || strpos($mod, 'duo') !== false) {
                    if (strpos($mod, 'técnico') !== false || strpos($mod, 'tecnico') !== false) {
                        $targetSec = 140;
                        $targetStr = '2:20 min';
                    } else {
                        if (strpos($cat, 'junior') !== false || strpos($cat, 'absolut') !== false || strpos($cat, 'senior') !== false) {
                            $targetSec = 165;
                            $targetStr = '2:45 min';
                        } else {
                            $targetSec = 150;
                            $targetStr = '2:30 min';
                        }
                    }
                } elseif (strpos($mod, 'equipo') !== false) {
                    if (strpos($mod, 'técnico') !== false || strpos($mod, 'tecnico') !== false) {
                        $targetSec = 170;
                        $targetStr = '2:50 min';
                    } else {
                        if (strpos($cat, 'junior') !== false || strpos($cat, 'absolut') !== false || strpos($cat, 'senior') !== false) {
                            $targetSec = 210;
                            $targetStr = '3:30 min';
                        } else {
                            $targetSec = 180;
                            $targetStr = '3:00 min';
                        }
                    }
                } elseif (strpos($mod, 'combo') !== false) {
                    if (strpos($cat, 'junior') !== false || strpos($cat, 'absolut') !== false) {
                        $targetSec = 210;
                        $targetStr = '3:30 min';
                    } else {
                        $targetSec = 180;
                        $targetStr = '3:00 min';
                    }
                } elseif (strpos($mod, 'acrobátic') !== false || strpos($mod, 'acrobatic') !== false || strpos($mod, 'highlight') !== false) {
                    $targetSec = 180;
                    $targetStr = '3:00 min';
                }

                return [
                    'targetSec' => $targetSec,
                    'targetStr' => $targetStr,
                    'marginSec' => $margin
                ];
            }

            $query = "SELECT rutinas.id, rutinas.dd_total, rutinas.nombre as nombre_rutina, rutinas.orden, rutinas.preswimmer, 
                             rutinas.id_fase, rutinas.id_club, rutinas.music_name, rutinas.music_original_name, rutinas.music_duration, 
                             clubes.nombre_corto as nombre_club, clubes.logo, modalidades.nombre as nombre_modalidad, categorias.nombre as nombre_categoria, 
                             fases.elementos_coach_card, fases.id_modalidad, fases.id_categoria, fases.genero as fase_genero,
                             COALESCE(NULLIF(fases.duracion_musica, 0), reglas.duracion_musica, 0) as duracion_oficial_db,
                             COALESCE(NULLIF(fases.margen_musica, 0), reglas.margen_musica, 5) as margen_oficial_db
                      FROM rutinas
                      JOIN fases ON rutinas.id_fase = fases.id
                      JOIN modalidades ON fases.id_modalidad = modalidades.id
                      JOIN categorias ON fases.id_categoria = categorias.id
                      JOIN clubes ON rutinas.id_club = clubes.id
                      LEFT JOIN reglas_competicion reglas ON (fases.id_categoria = reglas.id_categoria AND fases.id_modalidad = reglas.id_modalidad)
                      WHERE fases.id_competicion = $id_competicion $condicion_club
                      ORDER BY fases.orden ASC, fases.id ASC, rutinas.orden ASC, clubes.nombre_corto ASC, rutinas.id ASC";
            $res = mysqli_query($connection, $query);

            $rutinas_items = [];
            if($res && mysqli_num_rows($res) > 0) {
                while($r = mysqli_fetch_assoc($res)) {
                    $q_n = "SELECT group_concat(nadadoras.nombre, ' ', nadadoras.apellidos separator ', ') as atletas
                            FROM rutinas_participantes, nadadoras
                            WHERE nadadoras.id = rutinas_participantes.id_nadadora
                            AND rutinas_participantes.id_rutina = ".$r['id']."
                            AND rutinas_participantes.reserva = 'no'";
                    $res_n = mysqli_query($connection, $q_n);
                    $r['nombres'] = ($res_n && mysqli_num_rows($res_n) > 0) ? (mysqli_fetch_assoc($res_n)['atletas'] ?? '') : '';
                    $rutinas_items[] = $r;
                }
            }
            ?>

            <!-- VISTA MÓVIL (Tarjetas Fluidas) -->
            <div class="md:hidden p-3 sm:p-4 space-y-3" id="rutinasMobileContainer">
                <?php if (!empty($rutinas_items)): 
                    $curr_fase_m = null;
                    foreach ($rutinas_items as $row):
                        $isPS = ($row['orden'] < 0 && $row['orden'] > -10);
                        $isExh = ($row['orden'] <= -10);
                        if ($isExh) {
                            $orden_display = 'EX';
                        } elseif ($isPS) {
                            $orden_display = 'PS';
                        } else {
                            $orden_display = $row['orden'] == 0 ? '-' : $row['orden'];
                        }
                        $has_music = (!empty($row['music_name']));
                        $music_time_warning = false;
                        $music_warning_msg = '';

                        // Duración reglamentaria: prioridad BD (fase o regla maestra), fallback a función código
                        if (!empty($row['duracion_oficial_db']) && (int)$row['duracion_oficial_db'] > 0) {
                            $tSec = (int)$row['duracion_oficial_db'];
                            $mSec = (int)($row['margen_oficial_db'] ?: 5);
                            $time_rule = [
                                'targetSec' => $tSec,
                                'targetStr' => sprintf('%d:%02d min', floor($tSec / 60), $tSec % 60),
                                'marginSec' => $mSec
                            ];
                        } else {
                            $time_rule = getReglamentaryRoutineDuration($row['nombre_modalidad'], $row['nombre_categoria']);
                        }

                        if ($has_music && !empty($row['music_duration'])) {
                            if ($time_rule['targetSec'] > 0) {
                                $diff = abs((int)$row['music_duration'] - $time_rule['targetSec']);
                                if ($diff > $time_rule['marginSec']) {
                                    $music_time_warning = true;
                                    $dur_m = floor($row['music_duration'] / 60);
                                    $dur_s = $row['music_duration'] % 60;
                                    $dur_fmt = sprintf('%d:%02d', $dur_m, $dur_s);
                                    $music_warning_msg = "Duración {$dur_fmt} min: no cumple tiempo oficial ({$time_rule['targetStr']} ±{$time_rule['marginSec']}s)";
                                }
                            }
                        }

                        if ($music_time_warning) {
                            $btn_music_class = 'bg-amber-50 text-amber-600 border border-amber-300';
                            $btn_music_title = $music_warning_msg;
                        } elseif ($has_music) {
                            $btn_music_class = 'bg-indigo-50 text-indigo-600 border border-indigo-100';
                            $btn_music_title = 'Música';
                        } else {
                            $btn_music_class = 'bg-slate-100 text-slate-400 border border-slate-200';
                            $btn_music_title = 'Sin música';
                        }

                        if ($curr_fase_m !== $row['id_fase']):
                            $curr_fase_m = $row['id_fase'];
                ?>
                            <div class="pt-2 pb-1">
                                <div class="flex items-center gap-2 px-1">
                                    <span class="w-1.5 h-4 bg-blue-600 rounded-full"></span>
                                    <span class="text-xs font-black text-slate-700 uppercase tracking-wide"><?php echo $row['nombre_modalidad'] . (($row['fase_genero'] ?? '') === 'masculino' ? ' Masc' : '') . ' ' . $row['nombre_categoria']; ?></span>
                                    <?php if(($row['fase_genero'] ?? '') === 'masculino'): ?>
                                        <span class="px-1.5 py-0.5 rounded text-[8px] font-black uppercase tracking-wider bg-blue-100 text-blue-700 border border-blue-200">
                                            <i class="fas fa-mars mr-0.5"></i> Masc
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="rutina-mobile-card bg-white rounded-xl p-3.5 border border-slate-200/90 shadow-xs space-y-2.5">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-800 font-black text-xs tabular-nums border border-slate-200">
                                        Ord <?php echo $orden_display; ?>
                                    </span>
                                    <span class="text-[10px] font-black text-slate-400 tabular-nums">#<?php echo $row['id']; ?></span>
                                    <?php if ($isExh): ?>
                                        <span class="px-1.5 py-0.5 bg-amber-100 text-amber-700 text-[8px] font-black rounded uppercase">Exh</span>
                                    <?php elseif ($isPS): ?>
                                        <span class="px-1.5 py-0.5 bg-purple-100 text-purple-700 text-[8px] font-black rounded uppercase">PS</span>
                                    <?php endif; ?>
                                </div>
                                <div class="flex items-center gap-1.5 flex-shrink-0">
                                    <span class="text-xs font-black text-blue-700 uppercase"><?php echo $row['nombre_club']; ?></span>
                                    <?php if (!empty($row['logo'])): ?>
                                        <img src="<?php echo $row['logo']; ?>" class="w-5 h-5 object-contain">
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-xs font-black text-slate-800 uppercase tracking-tight"><?php echo $row['nombre_modalidad'].' '.$row['nombre_categoria']; ?></h4>
                                <?php if ($row['nombre_rutina']): ?>
                                    <p class="text-xs font-bold text-slate-500 italic mt-0.5"><i class="fas fa-quote-left text-slate-300 mr-1 text-[9px]"></i><?php echo $row['nombre_rutina']; ?></p>
                                <?php endif; ?>
                                <p class="text-[11px] font-medium text-slate-500 italic mt-1 leading-snug">
                                    <?php echo $row['nombres'] ?: '<span class="text-red-400">Sin participantes asignadas</span>'; ?>
                                </p>
                            </div>

                            <!-- Botones táctiles -->
                            <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-1.5">
                                <div class="flex items-center gap-1.5">
                                    <!-- Participantes -->
                                    <form action="./rutinas_participantes.php" method="POST">
                                        <input type="hidden" name="id_rutina" value="<?php echo $row['id']; ?>">
                                        <input type="hidden" name="id_fase" value="<?php echo $row['id_fase']; ?>">
                                        <input type="hidden" name="club" value="<?php echo $row['id_club']; ?>">
                                        <input type="hidden" name="id_competicion" value="<?php echo $id_competicion; ?>">
                                        <button type="submit" <?php echo $enable_inscripcion ?> class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center active:scale-[0.98] border border-blue-100" title="Participantes">
                                            <i class="fas fa-users text-xs"></i>
                                        </button>
                                    </form>

                                    <!-- Música -->
                                    <button onclick='openMusicPlayer(<?php echo $row["id"]; ?>, <?php echo json_encode($row["nombre_modalidad"]." ".$row["nombre_categoria"]); ?>, <?php echo json_encode($row["nombre_club"]); ?>, <?php echo json_encode($row["nombres"]); ?>, <?php echo json_encode($row["music_name"] ? "./public/music/".$id_competicion."/".$row["id"].".mp3" : ""); ?>, <?php echo json_encode($row["music_original_name"]); ?>, <?php echo json_encode($row["id_club"]); ?>, <?php echo json_encode($row["logo"] ?: "./images/default_club.png"); ?>, <?php echo (int)($time_rule["targetSec"] ?? 0); ?>, <?php echo (int)($time_rule["marginSec"] ?? 5); ?>)' 
                                            class="w-9 h-9 rounded-lg <?php echo $btn_music_class; ?> flex items-center justify-center active:scale-[0.98] relative" title="<?php echo htmlspecialchars($btn_music_title); ?>">
                                        <i class="fas <?php echo $has_music ? 'fa-play' : 'fa-music'; ?> text-xs"></i>
                                        <?php if ($music_time_warning): ?>
                                            <span class="absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full bg-amber-500 text-white flex items-center justify-center text-[8px] font-black shadow-md border-2 border-white pointer-events-none" title="<?php echo htmlspecialchars($music_warning_msg); ?>">
                                                <i class="fas fa-exclamation"></i>
                                            </span>
                                        <?php endif; ?>
                                    </button>

                                    <!-- Coach Card -->
                                    <?php if($row['elementos_coach_card'] > 0): ?>
                                        <form action="coach_card_composer.php" method="POST">
                                            <input type="hidden" name="id_rutina" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="id_fase" value="<?php echo $row['id_fase']; ?>">
                                            <input type="hidden" name="id_competicion" value="<?php echo $id_competicion; ?>">
                                            <button type="submit" <?php echo $enable_coach_card; ?> class="w-9 h-9 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center active:scale-[0.98] border border-amber-200 relative" title="Coach Card Composer">
                                                <i class="fas fa-puzzle-piece text-xs"></i>
                                                <span class="absolute -top-1.5 -right-1.5 bg-slate-800 text-white text-[7px] font-black px-1 rounded-full tabular-nums"><?php echo $row['dd_total']; ?></span>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>

                                <div class="flex items-center gap-1.5">
                                    <!-- Editar -->
                                    <?php if($_SESSION['id_rol'] != 5): ?>
                                        <form action="rutinas_edit.php" method="POST">
                                            <input type="hidden" name="id_rutina" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="id_fase" value="<?php echo $row['id_fase']; ?>">
                                            <input type="hidden" name="club" value="<?php echo $row['id_club']; ?>">
                                            <button type="submit" class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center active:scale-[0.98] border border-emerald-100" title="Editar">
                                                <i class="fas fa-edit text-xs"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <!-- Borrar -->
                                    <button onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo addslashes($row['nombre_modalidad'].' '.$row['nombre_categoria']); ?>', '<?php echo addslashes($row['nombre_club']); ?>', '<?php echo addslashes($row['nombres']); ?>')" 
                                            <?php echo $enable_inscripcion ?> class="w-9 h-9 rounded-lg bg-red-50 text-red-500 flex items-center justify-center active:scale-[0.98] border border-red-100 disabled:opacity-30" title="Eliminar">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; else: ?>
                    <div class="p-8 text-center bg-slate-50 rounded-xl border border-slate-200">
                        <p class="text-xs font-black uppercase text-slate-400">No hay rutinas registradas</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- VISTA ESCRITORIO (Tabla Clásica) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse" id="rutinasTable">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="px-8 py-4 text-[10px] font-black uppercase text-slate-400 tracking-widest text-center w-24">Orden</th>
                            <th class="px-8 py-4 text-[10px] font-black uppercase text-slate-400 tracking-widest w-24">ID</th>
                            <th class="px-8 py-4 text-[10px] font-black uppercase text-slate-400 tracking-widest">Modalidad / Categoría</th>
                            <?php if($_SESSION['id_rol'] != 5): ?>
                                <th class="px-8 py-4 text-[10px] font-black uppercase text-slate-400 tracking-widest">Club</th>
                            <?php endif; ?>
                            <th class="px-8 py-4 text-[10px] font-black uppercase text-slate-400 tracking-widest text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if(!empty($rutinas_items)): 
                            $curr_fase_d = null;
                            foreach($rutinas_items as $row):
                                if($curr_fase_d !== $row['id_fase']):
                                    $curr_fase_d = $row['id_fase'];
                                    ?>
                                    <tr class="bg-slate-100/80">
                                        <td colspan="10" class="px-8 py-3.5">
                                            <div class="flex items-center gap-3">
                                                <span class="w-2 h-5 bg-blue-600 rounded-full"></span>
                                                <span class="text-xs font-black text-slate-700 uppercase tracking-widest"><?php echo $row['nombre_modalidad'] . (($row['fase_genero'] ?? '') === 'masculino' ? ' Masc' : '') . ' ' . $row['nombre_categoria']; ?></span>
                                                <?php if(($row['fase_genero'] ?? '') === 'masculino'): ?>
                                                    <span class="px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider bg-blue-100 text-blue-700 border border-blue-200">
                                                        <i class="fas fa-mars mr-1"></i> Masculino
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php
                                endif;

                                $isPS = ($row['orden'] < 0 && $row['orden'] > -10);
                                $isExh = ($row['orden'] <= -10);
                                if ($isExh) {
                                    $orden_display = 'EX';
                                } elseif ($isPS) {
                                    $orden_display = 'PS';
                                } else {
                                    $orden_display = $row['orden'] == 0 ? '-' : $row['orden'];
                                }
                                $has_music = (!empty($row['music_name']));
                                $music_time_warning = false;
                                $music_warning_msg = '';

                                // Duración reglamentaria: prioridad BD (fase o regla maestra), fallback a función código
                                if (!empty($row['duracion_oficial_db']) && (int)$row['duracion_oficial_db'] > 0) {
                                    $tSec = (int)$row['duracion_oficial_db'];
                                    $mSec = (int)($row['margen_oficial_db'] ?: 5);
                                    $time_rule = [
                                        'targetSec' => $tSec,
                                        'targetStr' => sprintf('%d:%02d min', floor($tSec / 60), $tSec % 60),
                                        'marginSec' => $mSec
                                    ];
                                } else {
                                    $time_rule = getReglamentaryRoutineDuration($row['nombre_modalidad'], $row['nombre_categoria']);
                                }

                                if ($has_music && !empty($row['music_duration'])) {
                                    if ($time_rule['targetSec'] > 0) {
                                        $diff = abs((int)$row['music_duration'] - $time_rule['targetSec']);
                                        if ($diff > $time_rule['marginSec']) {
                                            $music_time_warning = true;
                                            $dur_m = floor($row['music_duration'] / 60);
                                            $dur_s = $row['music_duration'] % 60;
                                            $dur_fmt = sprintf('%d:%02d', $dur_m, $dur_s);
                                            $music_warning_msg = "Duración {$dur_fmt} min: no cumple tiempo oficial ({$time_rule['targetStr']} ±{$time_rule['marginSec']}s)";
                                        }
                                    }
                                }

                                if ($music_time_warning) {
                                    $btn_music_class = 'bg-amber-50 text-amber-600 border border-amber-300 hover:bg-amber-600 hover:text-white shadow-xs';
                                    $btn_music_title = $music_warning_msg;
                                } elseif ($has_music) {
                                    $btn_music_class = 'bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white border border-indigo-100 shadow-xs';
                                    $btn_music_title = 'Acompañamiento Musical';
                                } else {
                                    $btn_music_class = 'bg-slate-50 text-slate-400 hover:bg-indigo-600 hover:text-white border border-slate-100';
                                    $btn_music_title = 'Sin música';
                                }
                                ?>
                                <tr class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="px-4 py-5">
                                        <div class="w-10 h-10 mx-auto rounded-xl bg-slate-50 text-slate-400 group-hover:bg-blue-50 group-hover:text-blue-600 flex items-center justify-center font-black text-sm shadow-inner transition-colors border border-white/50 tabular-nums">
                                            <?php echo $orden_display; ?>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5 text-sm font-black text-slate-400 tabular-nums">#<?php echo $row['id']; ?></td>
                                    <td class="px-8 py-5">
                                        <div class="flex flex-col">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="text-sm font-black text-slate-800 uppercase tracking-tight"><?php echo $row['nombre_modalidad'].' '.$row['nombre_categoria']; ?></span>
                                                <?php if ($isExh): ?>
                                                    <span class="px-2 py-0.5 bg-amber-100 text-amber-600 text-[8px] font-black rounded uppercase tracking-widest">Exhibición</span>
                                                <?php elseif ($isPS): ?>
                                                    <span class="px-2 py-0.5 bg-purple-100 text-purple-600 text-[8px] font-black rounded uppercase tracking-widest">Preswimmer</span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($row['nombre_rutina']): ?>
                                                <p class="text-xs font-bold text-slate-500 italic mb-1"><i class="fas fa-quote-left text-slate-300 mr-1"></i> <?php echo $row['nombre_rutina']; ?></p>
                                            <?php endif; ?>
                                            <span class="text-[11px] font-bold text-slate-400 mt-1 italic"><?php echo $row['nombres'] ?: '<span class="text-red-300">Sin participantes</span>'; ?></span>
                                        </div>
                                    </td>

                                    <?php if($_SESSION['id_rol'] != 5): ?>
                                        <td class="px-8 py-5">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-slate-100 flex-shrink-0 flex items-center justify-center p-1.5 overflow-hidden">
                                                    <img src="<?php echo $row['logo'] ?: './images/default_club.png'; ?>" class="w-full h-full object-contain opacity-70 group-hover:opacity-100 transition-opacity">
                                                </div>
                                                <span class="text-xs font-black text-slate-600"><?php echo $row['nombre_club']; ?></span>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                    <td class="px-8 py-5">
                                        <div class="flex items-center justify-center gap-2">
                                            <!-- Participantes -->
                                            <form action="./rutinas_participantes.php" method="POST">
                                                <input type="hidden" name="id_rutina" value="<?php echo $row['id']; ?>">
                                                <input type="hidden" name="id_fase" value="<?php echo $row['id_fase']; ?>">
                                                <input type="hidden" name="club" value="<?php echo $row['id_club']; ?>">
                                                <input type="hidden" name="id_competicion" value="<?php echo $id_competicion; ?>">
                                                <button type="submit" <?php echo $enable_inscripcion ?> class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-600 hover:text-white transition-all shadow-sm group/btn" title="Gestionar Participantes">
                                                    <i class="fas fa-users text-xs group-hover/btn:scale-110"></i>
                                                </button>
                                            </form>

                                            <!-- Música -->
                                            <button onclick='openMusicPlayer(<?php echo $row["id"]; ?>, <?php echo json_encode($row["nombre_modalidad"]." ".$row["nombre_categoria"]); ?>, <?php echo json_encode($row["nombre_club"]); ?>, <?php echo json_encode($row["nombres"]); ?>, <?php echo json_encode($row["music_name"] ? "./public/music/".$id_competicion."/".$row["id"].".mp3" : ""); ?>, <?php echo json_encode($row["music_original_name"]); ?>, <?php echo json_encode($row["id_club"]); ?>, <?php echo json_encode($row["logo"] ?: "./images/default_club.png"); ?>, <?php echo (int)($time_rule["targetSec"] ?? 0); ?>, <?php echo (int)($time_rule["marginSec"] ?? 5); ?>)' 
                                                    class="w-10 h-10 rounded-xl <?php echo $btn_music_class; ?> flex items-center justify-center transition-all shadow-xs active:scale-95 group/btn relative" title="<?php echo htmlspecialchars($btn_music_title); ?>">
                                                <i class="fas <?php echo $has_music ? 'fa-play' : 'fa-music'; ?> text-xs group-hover/btn:scale-110"></i>
                                                <?php if ($music_time_warning): ?>
                                                    <span class="absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full bg-amber-500 text-white flex items-center justify-center text-[8px] font-black shadow-md border-2 border-white pointer-events-none" title="<?php echo htmlspecialchars($music_warning_msg); ?>">
                                                        <i class="fas fa-exclamation"></i>
                                                    </span>
                                                <?php endif; ?>
                                            </button>

                                            <!-- Coach Card -->
                                            <?php if($row['elementos_coach_card'] > 0): ?>
                                                <form action="coach_card_composer.php" method="POST">
                                                    <input type="hidden" name="id_rutina" value="<?php echo $row['id']; ?>">
                                                    <input type="hidden" name="id_fase" value="<?php echo $row['id_fase']; ?>">
                                                    <input type="hidden" name="id_competicion" value="<?php echo $id_competicion; ?>">
                                                    <button type="submit" <?php echo $enable_coach_card; ?> class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center hover:bg-amber-600 hover:text-white transition-all shadow-sm group/btn relative" title="Coach Card Composer">
                                                        <i class="fas fa-puzzle-piece text-xs group-hover/btn:scale-110"></i>
                                                        <span class="absolute -top-2 -right-2 bg-slate-800 text-white text-[8px] font-black px-1.5 py-0.5 rounded-full border-2 border-white tabular-nums"><?php echo $row['dd_total']; ?></span>
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <!-- Editar (Solo Admin) -->
                                            <?php if($_SESSION['id_rol'] != 5): ?>
                                                <form action="rutinas_edit.php" method="POST">
                                                    <input type="hidden" name="id_rutina" value="<?php echo $row['id']; ?>">
                                                    <input type="hidden" name="id_fase" value="<?php echo $row['id_fase']; ?>">
                                                    <input type="hidden" name="club" value="<?php echo $row['id_club']; ?>">
                                                    <button type="submit" class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-600 hover:text-white transition-all shadow-sm group/btn" title="Editar Rutina">
                                                        <i class="fas fa-edit text-xs group-hover/btn:scale-110"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <!-- Borrar -->
                                            <button onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo addslashes($row['nombre_modalidad'].' '.$row['nombre_categoria']); ?>', '<?php echo addslashes($row['nombre_club']); ?>', '<?php echo addslashes($row['nombres']); ?>')" 
                                                    <?php echo $enable_inscripcion ?> class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-600 hover:text-white transition-all shadow-sm group/btn disabled:opacity-30" title="Eliminar Rutina">
                                                <i class="fas fa-trash-alt text-xs group-hover/btn:scale-110"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="px-8 py-20 text-center">
                                    <div class="flex flex-col items-center opacity-20">
                                        <i class="fas fa-folder-open text-6xl mb-4"></i>
                                        <p class="text-xl font-black uppercase tracking-widest">No hay rutinas registradas</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Modals & Overlays -->
<!-- Modal Música -->
<div id="musicModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-[2.5rem] w-full max-w-lg shadow-2xl overflow-hidden animate-zoom-in">
        <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-indigo-50/50">
            <h3 class="text-xl font-black text-slate-800 flex items-center gap-3">
                <i class="fas fa-music text-indigo-600"></i> Gestor de Audio
            </h3>
            <button onclick="closeMusicModal()" class="w-10 h-10 rounded-2xl bg-white text-slate-400 hover:text-red-500 transition-colors flex items-center justify-center shadow-sm">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="p-8">
            <div id="playerSection" class="mb-8 hidden">
                <div class="bg-slate-50 p-6 rounded-3xl border border-slate-100 mb-6 flex flex-col items-center text-center relative">
                    <!-- Formulario para borrar música -->
                    <?php if($enable_musica == ''): ?>
                    <form action="rutinas_code.php" method="POST" class="absolute top-4 right-4" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este archivo de audio? Esta acción no se puede deshacer.');">
                        <input type="hidden" name="delete_music" value="1">
                        <input type="hidden" name="del_music_id" id="del_music_id">
                        <input type="hidden" name="del_music_comp" value="<?php echo $id_competicion; ?>">
                        <button type="submit" class="w-8 h-8 rounded-full bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-all flex items-center justify-center border border-red-100 shadow-sm" title="Eliminar Audio">
                            <i class="fas fa-trash-alt text-xs"></i>
                        </button>
                    </form>
                    <?php endif; ?>
                    
                    <div class="w-24 h-24 rounded-2xl bg-white shadow-sm border border-slate-100 p-3 mb-6 overflow-hidden">
                        <img id="playerLogo" src="./images/default_club.png" class="w-full h-full object-contain">
                    </div>
                    <p id="playerRoutine" class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1"></p>
                    <h4 id="playerClub" class="text-lg font-black text-slate-800 mb-4"></h4>
                    <audio id="audioElement" controls class="w-full h-12"></audio>
                    <div id="musicDurationNotice" class="hidden mt-3 text-left"></div>
                    <p id="fileName" class="text-[10px] font-bold text-indigo-400 mt-3 italic break-all"></p>
                </div>
            </div>

            <div id="noMusicAlert" class="mb-8 hidden">
                <div class="bg-amber-50 p-6 rounded-3xl border border-amber-100 flex items-center gap-4 text-amber-700">
                    <i class="fas fa-exclamation-triangle text-2xl"></i>
                    <p class="text-sm font-bold leading-tight">Esta rutina aún no dispone de acompañamiento musical cargado.</p>
                </div>
            </div>

            <!-- Campos ocultos siempre presentes para que el JS no falle -->
            <input type="hidden" id="music_edit_id">
            <input type="hidden" id="music_club_id">
            <input type="hidden" id="music_name_val">

            <?php if($enable_musica == ''): ?>
                <form action="rutinas_code.php" method="POST" enctype="multipart/form-data" class="space-y-4" onsubmit="syncMusicInputs(this)">
                    <div class="relative group">
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 px-1">Subir nuevo archivo (.mp3)</label>
                        <input type="file" name="musica" id="musicFileInput" accept=".mp3" required 
                               class="w-full px-5 py-4 bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl text-xs font-bold text-slate-500 group-hover:border-indigo-400 transition-all cursor-pointer">
                        <div id="uploadDurationNotice" class="hidden mt-2 p-3 rounded-xl text-xs font-bold border"></div>
                    </div>
                    <input type="hidden" name="edit_id" id="form_music_edit_id">
                    <input type="hidden" name="club" id="form_music_club_id">
                    <input type="hidden" name="music_name" id="form_music_name_val">
                    <input type="hidden" name="music_duration" id="form_music_duration_val" value="0">
                    <input type="hidden" name="id_competicion" value="<?php echo $id_competicion; ?>">
                    <button type="submit" name="upload_music" class="w-full py-4 bg-indigo-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg hover:scale-[1.02] transition-all">
                        Guardar Audio
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Borrado -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-[2.5rem] w-full max-w-md shadow-2xl overflow-hidden animate-zoom-in">
        <div class="p-8 text-center">
            <div class="w-20 h-20 bg-red-50 text-red-500 rounded-[2rem] flex items-center justify-center text-3xl mx-auto mb-6 shadow-sm border border-red-100">
                <i class="fas fa-trash-alt"></i>
            </div>
            <h3 class="text-2xl font-black text-slate-800 mb-2 tracking-tighter">¿Estás seguro?</h3>
            <p class="text-slate-500 font-medium mb-8 leading-relaxed">
                Vas a eliminar la rutina de <span id="delRoutineName" class="font-black text-slate-700"></span>.
                <br><span id="delParticipants" class="text-[10px] italic text-slate-400"></span>
                <br><br>Esta acción borrará también sus participantes y Coach Card.
            </p>
            
            <form action="rutinas_code.php" method="POST">
                <input type="hidden" name="delete_id" id="delete_id_val">
                <div class="flex gap-4">
                    <button type="button" onclick="closeDeleteModal()" class="flex-1 py-4 bg-slate-100 text-slate-500 font-black uppercase text-xs tracking-widest rounded-2xl hover:bg-slate-200 transition-all">Cancelar</button>
                    <button type="submit" name="delete_btn" class="flex-1 py-4 bg-red-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg hover:bg-red-700 transition-all">Eliminar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function getReglamentaryDuration(routineStr) {
        const rLower = (routineStr || '').toLowerCase();
        let targetSec = 0;
        let targetStr = '';
        const marginSec = 5;

        if (rLower.includes('solo')) {
            if (rLower.includes('técnico') || rLower.includes('tecnico')) {
                targetSec = 120;
                targetStr = '2:00 min';
            } else {
                if (rLower.includes('junior') || rLower.includes('absolut') || rLower.includes('senior')) {
                    targetSec = 135;
                    targetStr = '2:15 min';
                } else {
                    targetSec = 120;
                    targetStr = '2:00 min';
                }
            }
        } else if (rLower.includes('dúo') || rLower.includes('duo')) {
            if (rLower.includes('técnico') || rLower.includes('tecnico')) {
                targetSec = 140;
                targetStr = '2:20 min';
            } else {
                if (rLower.includes('junior') || rLower.includes('absolut') || rLower.includes('senior')) {
                    targetSec = 165;
                    targetStr = '2:45 min';
                } else {
                    targetSec = 150;
                    targetStr = '2:30 min';
                }
            }
        } else if (rLower.includes('equipo')) {
            if (rLower.includes('técnico') || rLower.includes('tecnico')) {
                targetSec = 170;
                targetStr = '2:50 min';
            } else {
                if (rLower.includes('junior') || rLower.includes('absolut') || rLower.includes('senior')) {
                    targetSec = 210;
                    targetStr = '3:30 min';
                } else {
                    targetSec = 180;
                    targetStr = '3:00 min';
                }
            }
        } else if (rLower.includes('combo')) {
            if (rLower.includes('junior') || rLower.includes('absolut')) {
                targetSec = 210;
                targetStr = '3:30 min';
            } else {
                targetSec = 180;
                targetStr = '3:00 min';
            }
        } else if (rLower.includes('acrobátic') || rLower.includes('acrobatic') || rLower.includes('highlight')) {
            targetSec = 180;
            targetStr = '3:00 min';
        }

        return { targetSec, targetStr, marginSec };
    }

    function toggleAddRutinaPanel() {
        const panel = document.getElementById('addRutinaPanel');
        panel.classList.toggle('hidden');
        if(!panel.classList.contains('hidden')) panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function openMusicPlayer(id, routine, club, participants, fileUrl, originalName, clubId, logoUrl, expectedSec, marginSec) {
        const modalEl = document.getElementById('musicModal');
        modalEl.dataset.expectedSec = expectedSec || 0;
        modalEl.dataset.marginSec = (typeof marginSec !== 'undefined' && marginSec !== null) ? marginSec : 5;

        document.getElementById('music_edit_id').value = id;
        document.getElementById('music_club_id').value = clubId;
        document.getElementById('music_name_val').value = routine + ' - ' + club + ' - ' + participants;
        document.getElementById('playerRoutine').textContent = routine;
        document.getElementById('playerClub').textContent = club;
        document.getElementById('playerLogo').src = logoUrl || './images/default_club.png';
        
        const delBtn = document.getElementById('del_music_id');
        if(delBtn) delBtn.value = id;
        
        const audio = document.getElementById('audioElement');
        const playerSec = document.getElementById('playerSection');
        const noMusic = document.getElementById('noMusicAlert');
        const fileName = document.getElementById('fileName');

        const uploadNotice = document.getElementById('uploadDurationNotice');
        if(uploadNotice) {
            uploadNotice.classList.add('hidden');
            uploadNotice.innerHTML = '';
        }

        const warnBox = document.getElementById('musicDurationNotice');
        if(warnBox) {
            warnBox.classList.add('hidden');
            warnBox.innerHTML = '';
        }

        if(fileUrl && fileUrl !== '') {
            audio.src = fileUrl + '?v=' + new Date().getTime();
            playerSec.classList.remove('hidden');
            noMusic.classList.add('hidden');
            fileName.textContent = 'Archivo: ' + (originalName || 'sin nombre');

            audio.onloadedmetadata = function() {
                const durSec = Math.round(audio.duration);
                const m = Math.floor(durSec / 60);
                const s = durSec % 60;
                const durStr = m + ':' + (s < 10 ? '0' : '') + s;
                
                let ruleTarget = parseInt(modalEl.dataset.expectedSec || '0');
                let ruleMargin = parseInt(modalEl.dataset.marginSec || '5');
                let ruleTargetStr = '';
                if (ruleTarget > 0) {
                    const tm = Math.floor(ruleTarget / 60);
                    const ts = ruleTarget % 60;
                    ruleTargetStr = tm + ':' + (ts < 10 ? '0' : '') + ts + ' min';
                } else {
                    const fallback = getReglamentaryDuration(routine);
                    ruleTarget = fallback.targetSec;
                    ruleMargin = fallback.marginSec;
                    ruleTargetStr = fallback.targetStr;
                }

                if (warnBox && ruleTarget > 0) {
                    const diff = Math.abs(durSec - ruleTarget);
                    if (diff > ruleMargin) {
                        warnBox.innerHTML = '<div class="p-3 bg-amber-50 rounded-2xl border border-amber-200 text-amber-800 text-xs font-bold flex items-center gap-2.5 shadow-xs"><i class="fas fa-triangle-exclamation text-amber-500 text-base shrink-0"></i><div><p class="leading-snug">Duración audio: <span class="font-black">' + durStr + ' min</span></p><p class="text-[10px] text-amber-700/90 font-medium">Oficial: ' + ruleTargetStr + ' &plusmn;' + ruleMargin + 's — <span class="underline">No cumple tiempo reglamentario</span></p></div></div>';
                        warnBox.classList.remove('hidden');
                    } else {
                        warnBox.innerHTML = '<div class="p-3 bg-emerald-50 rounded-2xl border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2.5 shadow-xs"><i class="fas fa-check-circle text-emerald-500 text-base shrink-0"></i><div><p class="leading-snug">Duración audio: <span class="font-black">' + durStr + ' min</span></p><p class="text-[10px] text-emerald-700/90 font-medium">Cumple tiempo oficial (' + ruleTargetStr + ')</p></div></div>';
                        warnBox.classList.remove('hidden');
                    }
                }
            };
        } else {
            audio.src = '';
            playerSec.classList.add('hidden');
            noMusic.classList.remove('hidden');
            fileName.textContent = '';
        }

        document.getElementById('musicModal').classList.remove('hidden');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const musicInput = document.getElementById('musicFileInput');
        if(musicInput) {
            musicInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                const notice = document.getElementById('uploadDurationNotice');
                const durInput = document.getElementById('form_music_duration_val');
                if (!file || !notice) return;

                const aObj = new Audio(URL.createObjectURL(file));
                aObj.onloadedmetadata = function() {
                    const sec = Math.round(aObj.duration);
                    if(durInput) durInput.value = sec;

                    const m = Math.floor(sec / 60);
                    const s = sec % 60;
                    const durStr = m + ':' + (s < 10 ? '0' : '') + s;

                    const modalEl = document.getElementById('musicModal');
                    const routine = document.getElementById('playerRoutine')?.textContent || '';
                    let ruleTarget = parseInt(modalEl?.dataset.expectedSec || '0');
                    let ruleMargin = parseInt(modalEl?.dataset.marginSec || '5');
                    let ruleTargetStr = '';
                    if (ruleTarget > 0) {
                        const tm = Math.floor(ruleTarget / 60);
                        const ts = ruleTarget % 60;
                        ruleTargetStr = tm + ':' + (ts < 10 ? '0' : '') + ts + ' min';
                    } else {
                        const fallback = getReglamentaryDuration(routine);
                        ruleTarget = fallback.targetSec;
                        ruleMargin = fallback.marginSec;
                        ruleTargetStr = fallback.targetStr;
                    }

                    notice.classList.remove('hidden');
                    if (ruleTarget > 0) {
                        const diff = Math.abs(sec - ruleTarget);
                        if (diff > ruleMargin) {
                            notice.className = 'mt-2 p-3 rounded-xl text-xs font-bold border bg-amber-50 border-amber-200 text-amber-800 flex items-center gap-2';
                            notice.innerHTML = '<i class="fas fa-triangle-exclamation text-amber-500 text-sm shrink-0"></i> <div>Duración detectada: <span class="font-black">' + durStr + ' min</span>. <span class="font-medium text-amber-700">Aviso: fuera de tiempo reglamentario (' + ruleTargetStr + ' &plusmn;' + ruleMargin + 's).</span></div>';
                        } else {
                            notice.className = 'mt-2 p-3 rounded-xl text-xs font-bold border bg-emerald-50 border-emerald-200 text-emerald-800 flex items-center gap-2';
                            notice.innerHTML = '<i class="fas fa-check-circle text-emerald-500 text-sm shrink-0"></i> <div>Duración detectada: <span class="font-black">' + durStr + ' min</span>. ✓ Cumple tiempo oficial.</div>';
                        }
                    } else {
                        notice.className = 'mt-2 p-3 rounded-xl text-xs font-bold border bg-slate-50 border-slate-200 text-slate-700';
                        notice.innerHTML = 'Duración detectada: <span class="font-black">' + durStr + ' min</span>.';
                    }
                };
            });
        }
    });

    function closeMusicModal() {
        const audio = document.getElementById('audioElement');
        audio.pause();
        audio.currentTime = 0;
        document.getElementById('musicModal').classList.add('hidden');
    }

    function syncMusicInputs(form) {
        document.getElementById('form_music_edit_id').value = document.getElementById('music_edit_id').value;
        document.getElementById('form_music_club_id').value = document.getElementById('music_club_id').value;
        document.getElementById('form_music_name_val').value = document.getElementById('music_name_val').value;
    }

    function confirmDelete(id, routine, club, participants) {
        document.getElementById('delete_id_val').value = id;
        document.getElementById('delRoutineName').textContent = routine + ' (' + club + ')';
        document.getElementById('delParticipants').textContent = participants ? 'Participantes: ' + participants : 'Sin participantes asignados';
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }

    function filterRutinas() {
        const input = document.getElementById('rutinaSearchInput');
        const filter = input.value.toUpperCase().trim();
        
        // 1. Filtrar tabla escritorio
        const table = document.getElementById('rutinasTable');
        if (table) {
            const tr = table.getElementsByTagName('tr');
            for (let i = 1; i < tr.length; i++) {
                let td = tr[i].getElementsByTagName('td');
                let found = false;
                for (let j = 0; j < td.length; j++) {
                    if (td[j]) {
                        let textValue = td[j].textContent || td[j].innerText;
                        if (textValue.toUpperCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }
                tr[i].style.display = found ? "" : "none";
            }
        }

        // 2. Filtrar tarjetas móviles
        const cards = document.querySelectorAll('.rutina-mobile-card');
        cards.forEach(card => {
            let textValue = card.textContent || card.innerText;
            card.style.display = textValue.toUpperCase().indexOf(filter) > -1 ? "" : "none";
        });
    }
</script>

<?php 
include('includes/scripts.php');
include('includes/footer.php'); 
?>
