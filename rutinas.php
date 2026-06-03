<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');

// Fetch deadlines
$query_fechas = 'SELECT date_add(fecha, interval -dias_musica day) as fecha_musica, 
                        date_add(fecha, interval -dias_coach_card day) as fecha_coach_card, 
                        date_add(fecha, interval -dias_sorteo day) as fecha_sorteo, 
                        date_add(fecha, interval -dias_inicio_inscripcion day) as fecha_inicio_inscripcion, 
                        date_add(fecha, interval -dias_fin_inscripcion day) as fecha_fin_inscripcion 
                 FROM competiciones WHERE id='.$id_competicion;
$fechas = mysqli_fetch_assoc(mysqli_query($connection, $query_fechas));

$fecha_musica = $fechas['fecha_musica'] ?? '';
$enable_musica = (date('Y-m-d') >= $fecha_musica && $_SESSION['id_rol'] != 1) ? 'disabled' : '';

if($figuras == 'si') {
    $fecha_coach_card = $fechas['fecha_fin_inscripcion'] ?? '';
} else {
    $fecha_coach_card = $fechas['fecha_coach_card'] ?? '';
}
$enable_coach_card = (date('Y-m-d') >= $fecha_coach_card && $_SESSION['id_rol'] != 1) ? 'disabled' : '';

$fecha_fin_inscripcion = $fechas['fecha_fin_inscripcion'] ?? '';
$enable_inscripcion = (date('Y-m-d') >= $fecha_fin_inscripcion && $_SESSION['id_rol'] != 1) ? 'disabled' : '';

// Condición por club
$condicion = '';
if(isset($_SESSION['club']) && $_SESSION['club'] > 0 && $_SESSION['id_rol'] == 5)
    $condicion = ' AND rutinas.id_club ='.$_SESSION['club'];

// Stats para KPIs
$q_stats = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN music_name IS NOT NULL AND music_name != '' THEN 1 ELSE 0 END) as con_musica,
            SUM(CASE WHEN dd_total > 0 THEN 1 ELSE 0 END) as con_coach_card
            FROM rutinas, fases 
            WHERE rutinas.id_fase = fases.id AND fases.id_competicion = $id_competicion $condicion";
$res_stats = mysqli_query($connection, $q_stats);
$stats = mysqli_fetch_assoc($res_stats);
?>

<main class="flex-1 flex flex-col min-w-0 bg-surface">
    <?php include('includes/topbar.php'); ?>

    <div class="p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend">
        
        <!-- Header -->
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <h1 class="text-4xl font-black text-slate-800 tracking-tighter mb-2 flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-flag-checkered text-lg"></i></span>
                    Registro de Rutinas
                </h1>
                <p class="text-slate-500 font-medium">Gestión de inscripciones, música y Coach Cards.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <?php if($_SESSION['id_rol'] != 5): ?>
                    <a target="_blank" href="./informes/inscripciones_numericas_rutinas.php?id_competicion=<?php echo $id_competicion?>&titulo=Inscripciones" class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-black text-slate-600 hover:bg-slate-50 transition-all flex items-center gap-2 shadow-sm">
                        <i class="fas fa-file-pdf text-red-500"></i> PDF INSCRIPCIONES
                    </a>
                    <a target="_blank" href="./informes/informe_coach_card.php?titulo=Coach%20Card%20Composer&id_competicion=<?php echo $id_competicion;?>" class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-black text-slate-600 hover:bg-slate-50 transition-all flex items-center gap-2 shadow-sm">
                        <i class="fas fa-puzzle-piece text-amber-500"></i> PDF COACH CARDS
                    </a>
                    <a target="_blank" href="./download_music.php?id_competicion=<?php echo $id_competicion;?>" class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-black text-slate-600 hover:bg-slate-50 transition-all flex items-center gap-2 shadow-sm">
                        <i class="fas fa-music text-blue-500"></i> ZIP MÚSICA
                    </a>
                <?php else: ?>
                    <a target="_blank" href="./informes/inscripciones_numericas_rutinas.php?id_competicion=<?php echo $id_competicion?>&club=<?php echo $_SESSION['club']?>&titulo=Inscripciones <?php echo $_SESSION['nombre_club']?>" class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-black text-slate-600 hover:bg-slate-50 transition-all flex items-center gap-2 shadow-sm">
                        <i class="fas fa-file-pdf text-red-500"></i> MI INSCRIPCIÓN
                    </a>
                    <a target="_blank" href="./informes/informe_coach_card.php?titulo=Coach%20Card%20Composer&id_club=<?php echo $_SESSION['club'];?>&id_competicion=<?php echo $id_competicion;?>" class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-black text-slate-600 hover:bg-slate-50 transition-all flex items-center gap-2 shadow-sm">
                        <i class="fas fa-puzzle-piece text-amber-500"></i> MIS COACH CARDS
                    </a>
                    <a target="_blank" href="./download_music.php?id_competicion=<?php echo $id_competicion.'&id_club='.$_SESSION['club'];?>" class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-black text-slate-600 hover:bg-slate-50 transition-all flex items-center gap-2 shadow-sm">
                        <i class="fas fa-music text-blue-500"></i> MI MÚSICA
                    </a>
                <?php endif; ?>
                
                <button onclick="toggleAddRutinaPanel()" <?php echo $enable_inscripcion ?> class="px-6 py-3 bg-blue-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg hover:scale-105 active:scale-95 transition-all flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-plus text-xs"></i> Añadir Rutina
                </button>
            </div>
        </div>

        <?php include('includes/alertas_v4.php'); ?>

        <!-- Plazos & KPIs -->
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6 mb-12">
            <!-- Plazo Inscripción -->
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-200 border-l-[6px] <?php echo ($enable_inscripcion == 'disabled') ? 'border-l-red-500' : 'border-l-blue-500'; ?> group hover:shadow-lg transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Cierre Inscripción</p>
                <h3 class="text-xl font-black <?php echo ($enable_inscripcion == 'disabled') ? 'text-red-600' : 'text-slate-800'; ?>"><?php echo dateAFecha($fecha_fin_inscripcion); ?></h3>
                <p class="text-[10px] font-bold text-slate-400 mt-2 uppercase"><?php echo ($enable_inscripcion == 'disabled') ? 'Cerrado' : 'Abierto'; ?></p>
            </div>
            <!-- Plazo Música -->
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-200 border-l-[6px] <?php echo ($enable_musica == 'disabled') ? 'border-l-red-500' : 'border-l-indigo-500'; ?> group hover:shadow-lg transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Límite Música</p>
                <h3 class="text-xl font-black <?php echo ($enable_musica == 'disabled') ? 'text-red-600' : 'text-slate-800'; ?>"><?php echo dateAFecha($fecha_musica); ?></h3>
                <p class="text-[10px] font-bold text-slate-400 mt-2 uppercase"><?php echo ($enable_musica == 'disabled') ? 'Cerrado' : 'Abierto'; ?></p>
            </div>
            <!-- Plazo Coach Card -->
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-200 border-l-[6px] <?php echo ($enable_coach_card == 'disabled') ? 'border-l-red-500' : 'border-l-amber-500'; ?> group hover:shadow-lg transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Límite Coach Card</p>
                <h3 class="text-xl font-black <?php echo ($enable_coach_card == 'disabled') ? 'text-red-600' : 'text-slate-800'; ?>"><?php echo dateAFecha($fecha_coach_card); ?></h3>
                <p class="text-[10px] font-bold text-slate-400 mt-2 uppercase"><?php echo ($enable_coach_card == 'disabled') ? 'Cerrado' : 'Abierto'; ?></p>
            </div>
            <!-- KPI Total -->
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-200 border-l-[6px] border-l-purple-500 group hover:shadow-lg transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Total Rutinas</p>
                <h3 class="text-xl font-black text-purple-600"><?php echo $stats['total'] ?? 0; ?></h3>
                <p class="text-[10px] font-bold text-slate-400 mt-2 uppercase">Inscritas</p>
            </div>
            <!-- KPI Completado -->
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-200 border-l-[6px] border-l-emerald-500 group hover:shadow-lg transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Música / CC</p>
                <div class="flex items-center gap-2">
                    <h3 class="text-xl font-black text-emerald-600"><?php echo $stats['con_musica'] ?? 0; ?></h3>
                    <span class="text-slate-300">/</span>
                    <h3 class="text-xl font-black text-emerald-600"><?php echo $stats['con_coach_card'] ?? 0; ?></h3>
                </div>
                <p class="text-[10px] font-bold text-slate-400 mt-2 uppercase">Subido / Definido</p>
            </div>
        </div>

        <!-- Panel Añadir -->
        <div id="addRutinaPanel" class="hidden mb-12 animate-fade-in-down">
            <div class="bg-white rounded-[2.5rem] p-8 shadow-xl border border-blue-100 relative overflow-hidden">
                <h2 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3"><i class="fas fa-plus-circle text-blue-600"></i> Registrar Rutina</h2>
                <form action="rutinas_code.php" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-6">
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
                    <div class="md:col-span-12 flex justify-end gap-3 pt-4">
                        <input type="hidden" name="id_competicion" value="<?php echo $id_competicion?>">
                        <button type="button" onclick="toggleAddRutinaPanel()" class="px-8 py-3 bg-slate-100 text-slate-500 font-black uppercase text-xs tracking-widest rounded-2xl hover:bg-slate-200 transition-all">Cancelar</button>
                        <button type="submit" name="save_btn" class="px-8 py-3 bg-blue-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg hover:scale-105 transition-all">Guardar Rutina</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- LISTADO DE RUTINAS -->
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-8 border-b border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4">
                <h3 class="text-lg font-black text-slate-800 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm"><i class="fas fa-list-ul"></i></span>
                    Detalle de Inscripciones
                </h3>
                <div class="relative w-full md:w-80">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"><i class="fas fa-search text-xs"></i></span>
                    <input type="text" id="rutinaSearchInput" placeholder="Buscar por club, modalidad o nadadora..." 
                           class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-transparent rounded-2xl text-xs font-bold text-slate-700 focus:bg-white focus:border-blue-500 transition-all outline-none"
                           onkeyup="filterRutinas()">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" id="rutinasTable">
                    <thead>
                        <tr class="bg-slate-50/50">
                            <th class="px-8 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest">ID</th>
                            <th class="px-8 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest">Modalidad / Categoría</th>
                            <?php if($_SESSION['id_rol'] != 5): ?>
                                <th class="px-8 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest">Club</th>
                            <?php endif; ?>
                            <th class="px-8 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php
                        $query = "SELECT rutinas.id, rutinas.dd_total, rutinas.nombre as nombre_rutina, rutinas.orden, rutinas.preswimmer, rutinas.id_fase, rutinas.id_club, rutinas.music_name, rutinas.music_original_name, clubes.nombre_corto as nombre_club, clubes.logo, modalidades.nombre as nombre_modalidad, categorias.nombre as nombre_categoria, fases.elementos_coach_card 
                                  FROM rutinas, fases, modalidades, categorias, clubes 
                                  WHERE rutinas.id_fase = fases.id 
                                  AND fases.id_modalidad = modalidades.id 
                                  AND fases.id_categoria = categorias.id 
                                  AND rutinas.id_club = clubes.id 
                                  AND fases.id_competicion = $id_competicion $condicion 
                                  ORDER BY fases.orden ASC, fases.id ASC, clubes.nombre_corto ASC, rutinas.id ASC";
                        $res = mysqli_query($connection, $query);

                        if($res && mysqli_num_rows($res) > 0):
                            $current_fase = null;
                            while($row = mysqli_fetch_assoc($res)):
                                if($current_fase !== $row['id_fase']):
                                    $current_fase = $row['id_fase'];
                                    ?>
                                    <tr class="bg-slate-100/80">
                                        <td colspan="10" class="px-8 py-4">
                                            <div class="flex items-center gap-3">
                                                <span class="w-2 h-6 bg-blue-600 rounded-full"></span>
                                                <span class="text-xs font-black text-slate-700 uppercase tracking-widest"><?php echo $row['nombre_modalidad'].' '.$row['nombre_categoria']; ?></span>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php
                                endif;

                                $q_nombres = "SELECT group_concat(nadadoras.nombre, ' ', nadadoras.apellidos separator ', ') 
                                              FROM rutinas_participantes, nadadoras 
                                              WHERE nadadoras.id = rutinas_participantes.id_nadadora 
                                              AND rutinas_participantes.id_rutina = ".$row['id']." 
                                              AND rutinas_participantes.reserva = 'no'";
                                $nombres = mysqli_result(mysqli_query($connection, $q_nombres), 0);
                                
                                $preswimmer_label = '';
                                if($row['orden'] == -1) $preswimmer_label = ' <span class="px-2 py-0.5 bg-amber-50 text-amber-600 text-[8px] font-black rounded uppercase ml-2">PRESWIMMER</span>';
                                else if($row['orden'] == -2) $preswimmer_label = ' <span class="px-2 py-0.5 bg-purple-50 text-purple-600 text-[8px] font-black rounded uppercase ml-2">EXHIBICIÓN</span>';
                                ?>
                                <tr class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="px-8 py-6 text-sm font-black text-slate-400">#<?php echo $row['id']; ?></td>
                                    <td class="px-8 py-6">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-black text-slate-800 flex items-center"><?php echo $row['nombre_modalidad'].' '.$row['nombre_categoria']; ?><?php echo $preswimmer_label; ?></span>
                                            <span class="text-[11px] font-bold text-slate-400 mt-1 italic"><?php echo $nombres ?: '<span class="text-red-300">Sin participantes</span>'; ?></span>
                                        </div>
                                    </td>
                                    <?php if($_SESSION['id_rol'] != 5): ?>
                                        <td class="px-8 py-6">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-slate-100 flex-shrink-0 flex items-center justify-center p-1.5 overflow-hidden">
                                                    <img src="<?php echo $row['logo'] ?: './images/default_club.png'; ?>" class="w-full h-full object-contain opacity-70 group-hover:opacity-100 transition-opacity">
                                                </div>
                                                <span class="text-xs font-black text-slate-600"><?php echo $row['nombre_club']; ?></span>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                    <td class="px-8 py-6">
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
                                            <?php 
                                            $has_music = (!empty($row['music_name']));
                                            $btn_music_class = $has_music ? 'bg-indigo-50 text-indigo-600 hover:bg-indigo-600' : 'bg-slate-50 text-slate-400 hover:bg-indigo-600';
                                            ?>
                                            <button onclick="openMusicPlayer(<?php echo $row['id']; ?>, '<?php echo addslashes($row['nombre_modalidad'].' '.$row['nombre_categoria']); ?>', '<?php echo addslashes($row['nombre_club']); ?>', '<?php echo addslashes($nombres); ?>', '<?php echo $row['music_name'] ? './public/music/'.$id_competicion.'/'.$row['id'].'.mp3' : ''; ?>', '<?php echo addslashes($row['music_original_name']); ?>', '<?php echo $row['id_club']; ?>', '<?php echo $row['logo'] ?: './images/default_club.png'; ?>')" 
                                                    class="w-10 h-10 rounded-xl <?php echo $btn_music_class; ?> flex items-center justify-center hover:text-white transition-all shadow-sm group/btn" title="Acompañamiento Musical">
                                                <i class="fas <?php echo $has_music ? 'fa-play' : 'fa-music'; ?> text-xs group-hover/btn:scale-110"></i>
                                            </button>

                                            <!-- Coach Card -->
                                            <?php if($row['elementos_coach_card'] > 0): ?>
                                                <form action="coach_card_composer.php" method="POST">
                                                    <input type="hidden" name="id_rutina" value="<?php echo $row['id']; ?>">
                                                    <input type="hidden" name="id_fase" value="<?php echo $row['id_fase']; ?>">
                                                    <input type="hidden" name="id_competicion" value="<?php echo $id_competicion; ?>">
                                                    <button type="submit" <?php echo $enable_coach_card; ?> class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center hover:bg-amber-600 hover:text-white transition-all shadow-sm group/btn relative" title="Coach Card Composer">
                                                        <i class="fas fa-puzzle-piece text-xs group-hover/btn:scale-110"></i>
                                                        <span class="absolute -top-2 -right-2 bg-slate-800 text-white text-[8px] font-black px-1.5 py-0.5 rounded-full border-2 border-white"><?php echo $row['dd_total']; ?></span>
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
                                            <button onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo addslashes($row['nombre_modalidad'].' '.$row['nombre_categoria']); ?>', '<?php echo addslashes($row['nombre_club']); ?>', '<?php echo addslashes($nombres); ?>')" 
                                                    <?php echo $enable_inscripcion ?> class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-600 hover:text-white transition-all shadow-sm group/btn disabled:opacity-30" title="Eliminar Rutina">
                                                <i class="fas fa-trash-alt text-xs group-hover/btn:scale-110"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
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
                <div class="bg-slate-50 p-6 rounded-3xl border border-slate-100 mb-6 flex flex-col items-center text-center">
                    <div class="w-24 h-24 rounded-2xl bg-white shadow-sm border border-slate-100 p-3 mb-6 overflow-hidden">
                        <img id="playerLogo" src="./images/default_club.png" class="w-full h-full object-contain">
                    </div>
                    <p id="playerRoutine" class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1"></p>
                    <h4 id="playerClub" class="text-lg font-black text-slate-800 mb-4"></h4>
                    <audio id="audioElement" controls class="w-full h-12"></audio>
                    <p id="fileName" class="text-[10px] font-bold text-indigo-400 mt-4 italic break-all"></p>
                </div>
            </div>

            <div id="noMusicAlert" class="mb-8 hidden">
                <div class="bg-amber-50 p-6 rounded-3xl border border-amber-100 flex items-center gap-4 text-amber-700">
                    <i class="fas fa-exclamation-triangle text-2xl"></i>
                    <p class="text-sm font-bold leading-tight">Esta rutina aún no dispone de acompañamiento musical cargado.</p>
                </div>
            </div>

            <?php if($enable_musica == ''): ?>
                <form action="rutinas_code.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <div class="relative group">
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 px-1">Subir nuevo archivo (.mp3)</label>
                        <input type="file" name="musica" accept=".mp3" required 
                               class="w-full px-5 py-4 bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl text-xs font-bold text-slate-500 group-hover:border-indigo-400 transition-all cursor-pointer">
                    </div>
                    <input type="hidden" name="edit_id" id="music_edit_id">
                    <input type="hidden" name="club" id="music_club_id">
                    <input type="hidden" name="music_name" id="music_name_val">
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
    function toggleAddRutinaPanel() {
        const panel = document.getElementById('addRutinaPanel');
        panel.classList.toggle('hidden');
        if(!panel.classList.contains('hidden')) panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function openMusicPlayer(id, routine, club, participants, fileUrl, originalName, clubId, logoUrl) {
        document.getElementById('music_edit_id').value = id;
        document.getElementById('music_club_id').value = clubId;
        document.getElementById('music_name_val').value = routine + ' - ' + club + ' - ' + participants;
        document.getElementById('playerRoutine').textContent = routine;
        document.getElementById('playerClub').textContent = club;
        document.getElementById('playerLogo').src = logoUrl || './images/default_club.png';
        
        const audio = document.getElementById('audioElement');
        const playerSec = document.getElementById('playerSection');
        const noMusic = document.getElementById('noMusicAlert');
        const fileName = document.getElementById('fileName');

        if(fileUrl && fileUrl !== '') {
            audio.src = fileUrl + '?v=' + new Date().getTime();
            playerSec.classList.remove('hidden');
            noMusic.classList.add('hidden');
            fileName.textContent = 'Archivo: ' + (originalName || 'sin nombre');
        } else {
            audio.src = '';
            playerSec.classList.add('hidden');
            noMusic.classList.remove('hidden');
            fileName.textContent = '';
        }

        document.getElementById('musicModal').classList.remove('hidden');
    }

    function closeMusicModal() {
        const audio = document.getElementById('audioElement');
        audio.pause();
        audio.currentTime = 0;
        document.getElementById('musicModal').classList.add('hidden');
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
        const filter = input.value.toUpperCase();
        const table = document.getElementById('rutinasTable');
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
</script>

<?php 
include('includes/scripts.php');
include('includes/footer.php'); 
?>
