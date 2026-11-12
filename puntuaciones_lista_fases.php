<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');

$id_comp = $_SESSION['id_competicion_usuario'];
$comp_figuras = $_SESSION['competicion_figuras_usuario'] ?? 'no';
$is_mixta = ($comp_figuras == 'mixta');
$is_figuras = ($comp_figuras == 'si');

// Preferencia de vista (Cookie)
$vista_actual = $_COOKIE['pref_vista_fases'] ?? 'cards';

// 1. Datos de Contexto y KPIs
$q_comp = "SELECT nombre, lugar, fecha FROM competiciones WHERE id = '$id_comp'";
$comp_info = mysqli_fetch_assoc(mysqli_query($connection, $q_comp));

// 1.1 Fases y Progreso
$q_fases_stats = "SELECT COUNT(*) as total, SUM(CASE WHEN puntuada = 'si' THEN 1 ELSE 0 END) as puntuadas FROM fases WHERE id_competicion = '$id_comp'";
$f_stats = mysqli_fetch_assoc(mysqli_query($connection, $q_fases_stats));
$total_fases = $f_stats['total'] ?: 0;
$puntuadas = $f_stats['puntuadas'] ?: 0;
$pct_progreso = ($total_fases > 0) ? round(($puntuadas / $total_fases) * 100) : 0;

// 1.2 Carga de Trabajo
if($is_mixta) {
    $q_ins = "SELECT (
                (SELECT COUNT(*) FROM inscripciones_figuras WHERE id_fase IN (SELECT id FROM fases WHERE id_competicion = '$id_comp')) + 
                (SELECT COUNT(*) FROM rutinas WHERE id_competicion = '$id_comp')
              ) as total";
} elseif($is_figuras) {
    $q_ins = "SELECT COUNT(*) as total FROM inscripciones_figuras WHERE id_fase IN (SELECT id FROM fases WHERE id_competicion = '$id_comp')";
} else {
    $q_ins = "SELECT COUNT(*) as total FROM rutinas WHERE id_competicion = '$id_comp'";
}
$total_inscripciones = mysqli_fetch_assoc(mysqli_query($connection, $q_ins))['total'] ?? 0;
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-surface">
    
    <?php include('includes/topbar.php'); ?>

    <div class="px-3 py-4 sm:p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend">
        
        <!-- Header Dinámico -->
        <div class="mb-6 sm:mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4 sm:gap-6">
            <div class="animate-fade-in">
                <div class="flex items-center gap-2 sm:gap-3 mb-1 sm:mb-2">
                    <span class="px-2.5 sm:px-3 py-0.5 sm:py-1 bg-blue-50 text-blue-600 text-[9px] sm:text-[10px] font-black uppercase tracking-wider rounded-full border border-blue-100 shadow-xs">Modo Mesa Técnica</span>
                    <span class="text-slate-300">/</span>
                    <span class="text-[9px] sm:text-[10px] font-bold text-slate-400 uppercase tracking-wider tabular-nums"><?php echo date("d M, Y", strtotime($comp_info['fecha'])); ?></span>
                </div>
                <h1 class="text-2xl sm:text-4xl font-black text-slate-800 tracking-tight mb-1 flex items-center gap-2.5 sm:gap-3">
                    <span class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-stopwatch text-base sm:text-lg"></i></span>
                    Guion de Puntuación
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 font-medium max-w-xl truncate"><?php echo $comp_info['nombre']; ?> @ <span class="text-blue-600 font-bold"><?php echo $comp_info['lugar']; ?></span></p>
            </div>
            <div class="flex flex-wrap items-center gap-2 sm:gap-3 w-full md:w-auto">
                <div class="bg-white p-1 rounded-xl sm:rounded-2xl border border-slate-200 shadow-xs flex items-center gap-1">
                    <button onclick="setVista('cards')" id="btn-vista-cards" class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg sm:rounded-xl text-[9px] sm:text-[10px] font-black uppercase tracking-wider transition-all <?php echo $vista_actual == 'cards' ? 'bg-slate-900 text-white shadow-md' : 'text-slate-400 hover:bg-slate-50'; ?>">
                        <i class="fas fa-th-large mr-1 sm:mr-2"></i> Tarjetas
                    </button>
                    <button onclick="setVista('list')" id="btn-vista-list" class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg sm:rounded-xl text-[9px] sm:text-[10px] font-black uppercase tracking-wider transition-all <?php echo $vista_actual == 'list' ? 'bg-slate-900 text-white shadow-md' : 'text-slate-400 hover:bg-slate-50'; ?>">
                        <i class="fas fa-list mr-1 sm:mr-2"></i> Lista
                    </button>
                </div>
                <button onclick="window.location.reload()" class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl sm:rounded-2xl bg-white border border-slate-200 text-slate-500 flex items-center justify-center hover:bg-slate-50 active:scale-[0.98] transition-all shadow-xs"><i class="fas fa-sync-alt text-xs sm:text-sm"></i></button>
                <a href="./informes/informe_puntuaciones_global.php?id_competicion=<?php echo $id_comp; ?>" target="_blank" class="px-4 sm:px-6 py-2.5 sm:py-3 bg-slate-800 text-white font-black uppercase text-[11px] sm:text-xs tracking-wider rounded-xl sm:rounded-2xl shadow-md hover:bg-slate-900 active:scale-[0.98] transition-all flex items-center gap-2"><i class="fas fa-print text-xs"></i> Global PDF</a>
                <button type="button" onclick="abrirModalPublicar('clasificacion', 'Clasificación Detallada', false, <?php echo (int)$id_comp; ?>)" class="px-4 sm:px-6 py-2.5 sm:py-3 bg-blue-600 hover:bg-blue-700 text-white font-black uppercase text-[11px] sm:text-xs tracking-wider rounded-xl sm:rounded-2xl shadow-md active:scale-[0.98] transition-all flex items-center gap-2"><i class="fas fa-cloud-arrow-up text-xs"></i> Publicar en la Web</button>
            </div>
        </div>

        <!-- DASHBOARD DE PROGRESO -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 sm:gap-8 mb-6 sm:mb-12">
            <div class="lg:col-span-8 bg-white rounded-2xl sm:rounded-[2.5rem] p-4 sm:p-8 shadow-sm border border-slate-200/80 flex flex-col sm:flex-row items-center gap-4 sm:gap-10 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/5 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                
                <div class="relative w-24 h-24 sm:w-32 sm:h-32 flex-shrink-0">
                    <svg viewBox="0 0 100 100" class="w-full h-full transform -rotate-90">
                        <circle cx="50" cy="50" r="45" fill="transparent" stroke="#f1f5f9" stroke-width="10" />
                        <circle cx="50" cy="50" r="45" fill="transparent" stroke="#10b981" stroke-width="10" stroke-dasharray="282.7" stroke-dashoffset="<?php echo 282.7 - (282.7 * $pct_progreso / 100); ?>" class="transition-all duration-1000" stroke-linecap="round" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-xl sm:text-2xl font-black text-slate-800 leading-none tabular-nums"><?php echo $pct_progreso; ?>%</span>
                        <span class="text-[8px] font-black uppercase text-slate-400 mt-0.5 sm:mt-1">Listo</span>
                    </div>
                </div>

                <div class="flex-1 space-y-3 sm:space-y-4 text-center sm:text-left">
                    <div>
                        <h3 class="text-base sm:text-xl font-black text-slate-800 tracking-tight italic">Progreso de la Jornada</h3>
                        <p class="text-xs text-slate-400 font-medium">Fases puntuadas y cerradas en el sistema.</p>
                    </div>
                    <div class="flex flex-wrap justify-center sm:justify-start gap-2 sm:gap-4">
                        <div class="px-3 sm:px-4 py-1.5 sm:py-2 bg-slate-50 rounded-xl border border-slate-100 flex items-center gap-2 sm:gap-3">
                            <span class="text-xs sm:text-sm font-black text-slate-700 tabular-nums"><?php echo $puntuadas; ?> / <?php echo $total_fases; ?></span>
                            <span class="text-[8px] sm:text-[9px] font-black uppercase text-slate-400 tracking-wider">Fases</span>
                        </div>
                        <div class="px-3 sm:px-4 py-1.5 sm:py-2 bg-slate-50 rounded-xl border border-slate-100 flex items-center gap-2 sm:gap-3">
                            <span class="text-xs sm:text-sm font-black text-slate-700 tabular-nums"><?php echo $total_inscripciones; ?></span>
                            <span class="text-[8px] sm:text-[9px] font-black uppercase text-slate-400 tracking-wider">Participaciones</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-4 grid grid-cols-2 lg:flex lg:flex-col gap-2.5 sm:gap-6">
                <div class="flex-1 bg-white rounded-xl sm:rounded-[2rem] p-3.5 sm:p-6 shadow-sm border border-slate-200/80 border-l-4 sm:border-l-[6px] border-l-blue-500 flex items-center gap-3 sm:gap-6">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-lg sm:rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 shadow-inner flex-shrink-0"><i class="fas fa-bolt text-sm sm:text-base"></i></div>
                    <div class="min-w-0"><h3 class="text-base sm:text-2xl font-black text-slate-800 leading-none mb-1 truncate">Activo</h3><p class="text-[8px] sm:text-[10px] font-black uppercase text-slate-400 tracking-wider truncate">Modo Puntuación</p></div>
                </div>
                <div class="flex-1 bg-white rounded-xl sm:rounded-[2rem] p-3.5 sm:p-6 shadow-sm border border-slate-200/80 border-l-4 sm:border-l-[6px] border-l-amber-500 flex items-center gap-3 sm:gap-6">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-lg sm:rounded-xl bg-amber-50 flex items-center justify-center text-amber-600 shadow-inner flex-shrink-0"><i class="fas fa-file-invoice text-sm sm:text-base"></i></div>
                    <div class="min-w-0"><h3 class="text-base sm:text-2xl font-black text-slate-800 leading-none mb-1 truncate"><?php echo ($is_figuras ? 'Figuras' : 'Rutinas'); ?></h3><p class="text-[8px] sm:text-[10px] font-black uppercase text-slate-400 tracking-wider truncate">Especialidad</p></div>
                </div>
            </div>
        </div>

        <?php include('includes/alertas_v4.php'); ?>

        <!-- LISTADO DE FASES (EL GUION) -->
        <div id="view-container-cards" class="<?php echo $vista_actual == 'cards' ? '' : 'hidden'; ?>">
        <?php
        if($is_figuras || $is_mixta) {
            // 1. Obtener las categorías únicas presentes en las fases de esta competición
            $q_cats = "SELECT DISTINCT c.id, c.nombre FROM fases f JOIN categorias c ON f.id_categoria = c.id WHERE f.id_competicion = '$id_comp' ORDER BY c.orden, c.id";
            $res_cats = mysqli_query($connection, $q_cats);
            
            while($cat = mysqli_fetch_assoc($res_cats)):
                $id_cat = $cat['id'];
                $cat_nom_actual = $cat['nombre'];
        ?>
                <div class="mb-8 sm:mb-16 animate-fade-in">
                    <!-- Separador y Título de Categoría -->
                    <div class="flex items-center gap-3 sm:gap-6 mb-4 sm:mb-8">
                        <h2 class="text-base sm:text-2xl font-black text-slate-800 uppercase tracking-tight italic flex items-center gap-2.5 sm:gap-4 shrink-0">
                            <span class="w-8 h-8 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-blue-600 flex items-center justify-center text-white shadow-md shadow-blue-500/20"><i class="fas fa-layer-group text-xs sm:text-sm"></i></span>
                            Categoría <?php echo $cat_nom_actual; ?>
                        </h2>
                        <div class="h-[2px] flex-1 bg-gradient-to-r from-slate-200 to-transparent"></div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-6">
                        <?php
                        $query = "SELECT f.*, c.nombre as cat_nom, 
                                  fig.nombre as fig_nom, fig.numero as fig_num, fig.grado_dificultad as fig_gd,
                                  m.nombre as mod_nom 
                                  FROM fases f 
                                  JOIN categorias c ON f.id_categoria = c.id 
                                  LEFT JOIN figuras fig ON f.id_figura = fig.id 
                                  LEFT JOIN modalidades m ON f.id_modalidad = m.id 
                                  WHERE f.id_competicion = '$id_comp' AND f.id_categoria = '$id_cat'
                                  ORDER BY f.orden, f.id";
                        $res = mysqli_query($connection, $query);
                        while ($row = mysqli_fetch_assoc($res)):
                            $is_puntuada = ($row['puntuada'] == 'si');
                            $is_phase_fig = (!empty($row['id_figura']) && $row['id_figura'] > 0);
                            $has_cc = ($row['elementos_coach_card'] > 0);
                            $isOldSystem = ($row['obsoleto'] == 'si');
                            $color_status = $is_puntuada ? 'emerald' : ($is_phase_fig ? 'blue' : 'purple');
                            $icon_puntuar = $isOldSystem ? 'fa-calculator' : 'fa-square-root-variable';

                            if ($is_phase_fig) {
                                $target_page = $has_cc ? "puntuaciones_lista_figuras_rutinas_tecnicas.php" : "puntuaciones_lista_figuras.php";
                                $item_title = "Figura " . $row['fig_num'] . " - " . $row['fig_nom'];
                                $item_sub = "GD " . $row['fig_gd'] . ($has_cc ? " · Con CC" : "");
                                $badge_sub = '<span class="px-2 py-0.5 bg-blue-50 text-blue-600 text-[8px] font-black rounded uppercase">Figura</span>';
                            } else {
                                $target_page = "puntuaciones_lista_rutinas.php";
                                $item_title = $row['mod_nom'] . (($row['genero'] ?? '') === 'masculino' ? ' Masc' : '');
                                $item_sub = "Rutina Técnica";
                                $badge_sub = '<span class="px-2 py-0.5 bg-purple-50 text-purple-600 text-[8px] font-black rounded uppercase">Rutina</span>';
                            }
                        ?>
                        <div class="bg-white rounded-xl sm:rounded-[2rem] p-4 sm:p-7 shadow-sm border border-slate-200/80 border-t-4 sm:border-t-[6px] border-t-<?php echo $color_status; ?>-500 relative flex flex-col group hover:shadow-md transition-all">
                            <!-- Badge Superior -->
                            <div class="flex justify-between items-center mb-3 sm:mb-5">
                                <div class="flex flex-col items-center justify-center w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-slate-50 border border-slate-100 shadow-inner">
                                    <span class="text-[8px] sm:text-[9px] font-black text-slate-300 uppercase leading-none mb-0.5">Orden</span>
                                    <span class="text-sm sm:text-lg font-black text-slate-700 leading-none tabular-nums"><?php echo $row['orden']; ?></span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <?php echo $badge_sub; ?>
                                    <?php if($is_puntuada): ?>
                                        <span class="px-2 sm:px-3 py-0.5 sm:py-1 bg-emerald-50 text-emerald-600 text-[9px] sm:text-[10px] font-black rounded-md border border-emerald-100 uppercase tracking-wider shadow-xs">Cerrada</span>
                                    <?php else: ?>
                                        <span class="px-2 sm:px-3 py-0.5 sm:py-1 bg-blue-50 text-blue-600 text-[9px] sm:text-[10px] font-black rounded-md border border-blue-100 uppercase tracking-wider shadow-xs animate-pulse">Abierta</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <h2 class="text-sm sm:text-base font-black text-slate-800 tracking-tight leading-snug mb-1 break-words min-h-[2.5rem]" title="<?php echo $item_title; ?>">
                                <?php echo $item_title; ?>
                            </h2>
                            <div class="flex items-center gap-2 mb-4 sm:mb-8">
                                <span class="text-[11px] sm:text-xs font-black text-<?php echo $is_phase_fig ? 'blue' : 'purple'; ?>-600 uppercase tabular-nums"><?php echo $item_sub; ?></span>
                            </div>

                            <!-- Botones de Acción -->
                            <div class="mt-auto grid grid-cols-2 gap-2 sm:gap-3 pt-3 sm:pt-5 border-t border-slate-100">
                                <form action="<?php echo $target_page; ?>" method="POST" target="_blank" class="col-span-2">
                                    <input type="hidden" name="id_fase" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="id_categoria" value="<?php echo $row['id_categoria']; ?>">
                                    <input type="hidden" name="elementos_coach_card" value="<?php echo $row['elementos_coach_card']; ?>">
                                    <button type="submit" name="edit_btn" class="w-full py-2.5 sm:py-3.5 bg-<?php echo $color_status; ?>-600 text-white font-black uppercase text-[11px] sm:text-xs tracking-wider rounded-xl shadow-md active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                                        <i class="fas <?php echo $icon_puntuar; ?>"></i>
                                        <?php echo $is_puntuada ? 'Revisar' : 'Puntuar'; ?>
                                    </button>
                                </form>
                                
                                <a href="./informes/informe_puntuaciones.php?id_fase=<?php echo $row['id']; ?>&titulo=Clasificación Detallada" target="_blank" class="col-span-1 py-2 sm:py-2.5 bg-white border border-slate-200 text-slate-500 font-black uppercase text-[9px] sm:text-[10px] tracking-wider rounded-xl hover:bg-slate-50 active:scale-[0.98] transition-all flex items-center justify-center gap-1.5">
                                    <i class="fas fa-file-pdf text-[10px]"></i> PDF
                                </a>
                                
                                <form action="fases_edit.php" method="POST" target="_blank" class="col-span-1">
                                    <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="id_figura" value="<?php echo $row['id_figura']; ?>">
                                    <button type="submit" name="edit_btn" class="w-full py-2 sm:py-2.5 bg-slate-50 border border-slate-100 text-slate-400 font-black uppercase text-[9px] sm:text-[10px] tracking-wider rounded-xl hover:text-blue-600 active:scale-[0.98] transition-all flex items-center justify-center gap-1.5">
                                        <i class="fas fa-gear text-[10px]"></i> Ver
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
        <?php 
            endwhile; 
        } else { 
            // --- LAYOUT PARA RUTINAS (MANTIENE GRID ÚNICO) ---
        ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3 sm:gap-8">
                <?php
                $query = "SELECT f.*, c.nombre as cat_nom, m.nombre as mod_nom 
                          FROM fases f 
                          JOIN categorias c ON f.id_categoria = c.id 
                          JOIN modalidades m ON f.id_modalidad = m.id 
                          WHERE f.id_competicion = '$id_comp' 
                          ORDER BY f.orden, f.id";
                $res = mysqli_query($connection, $query);
                while ($row = mysqli_fetch_assoc($res)):
                    $is_puntuada = ($row['puntuada'] == 'si');
                    $isOldSystem = ($row['obsoleto'] == 'si');
                    $color_status = $is_puntuada ? 'emerald' : 'blue';
                    $icon_puntuar = $isOldSystem ? 'fa-calculator' : 'fa-square-root-variable';
                ?>
                <div class="bg-white rounded-xl sm:rounded-[2.5rem] p-4 sm:p-8 shadow-sm border border-slate-200/80 border-t-4 sm:border-t-[8px] border-t-<?php echo $color_status; ?>-500 relative flex flex-col group hover:shadow-md transition-all animate-fade-in">
                    <!-- Badge Superior -->
                    <div class="flex justify-between items-center mb-4 sm:mb-6">
                        <div class="flex flex-col items-center justify-center w-10 h-10 sm:w-12 sm:h-12 rounded-lg sm:rounded-2xl bg-slate-50 border border-slate-100 shadow-inner">
                            <span class="text-[8px] sm:text-[9px] font-black text-slate-300 uppercase leading-none mb-0.5 sm:mb-1">Orden</span>
                            <span class="text-sm sm:text-lg font-black text-slate-700 leading-none tabular-nums"><?php echo $row['orden']; ?></span>
                        </div>
                        <?php if($is_puntuada): ?>
                            <span class="px-2.5 sm:px-3 py-0.5 sm:py-1 bg-emerald-50 text-emerald-600 text-[8px] sm:text-[9px] font-black rounded-lg border border-emerald-100 uppercase tracking-wider shadow-xs"><i class="fas fa-check-circle mr-1"></i> Cerrada</span>
                        <?php else: ?>
                            <span class="px-2.5 sm:px-3 py-0.5 sm:py-1 bg-blue-50 text-blue-600 text-[8px] sm:text-[9px] font-black rounded-lg border border-blue-100 uppercase tracking-wider shadow-xs animate-pulse">Abierta</span>
                        <?php endif; ?>
                    </div>

                    <h2 class="text-base sm:text-xl font-black text-slate-800 tracking-tight leading-snug mb-1 break-words">
                        <?php echo $row['mod_nom'] . (($row['genero'] ?? '') === 'masculino' ? ' Masc' : ''); ?>
                    </h2>
                    <div class="flex items-center gap-2 mb-4 sm:mb-8">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider italic"><?php echo $row['cat_nom']; ?></span>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="mt-auto grid grid-cols-2 gap-2 sm:gap-3 pt-3 sm:pt-6 border-t border-slate-100">
                        <form action="puntuaciones_lista_rutinas.php" method="POST" target="_blank" class="col-span-2">
                            <input type="hidden" name="id_fase" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="id_categoria" value="<?php echo $row['id_categoria']; ?>">
                            <button type="submit" name="edit_btn" class="w-full py-3 sm:py-4 bg-<?php echo $color_status; ?>-600 text-white font-black uppercase text-xs tracking-wider rounded-xl sm:rounded-2xl shadow-md active:scale-[0.98] transition-all flex items-center justify-center gap-2.5">
                                <i class="fas <?php echo $icon_puntuar; ?>"></i>
                                <?php echo $is_puntuada ? 'Revisar' : 'Puntuar'; ?>
                            </button>
                        </form>
                        
                        <a href="./informes/informe_puntuaciones.php?id_fase=<?php echo $row['id']; ?>&titulo=Clasificación Detallada" target="_blank" class="col-span-1 py-2 sm:py-3 bg-white border border-slate-200 text-slate-500 font-black uppercase text-[9px] tracking-wider rounded-xl sm:rounded-2xl hover:bg-slate-50 active:scale-[0.98] transition-all flex items-center justify-center gap-1.5">
                            <i class="fas fa-file-pdf"></i> Resultados
                        </a>
                        
                        <form action="fases_edit.php" method="POST" target="_blank" class="col-span-1">
                            <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="edit_btn" class="w-full py-2 sm:py-3 bg-slate-50 border border-slate-100 text-slate-400 font-black uppercase text-[9px] tracking-wider rounded-xl sm:rounded-2xl hover:text-blue-600 active:scale-[0.98] transition-all flex items-center justify-center gap-1.5">
                                <i class="fas fa-gear"></i> Ver
                            </button>
                        </form>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php } ?>
        </div>

        <!-- VISTA DE LISTA COMPACTA -->
        <div id="view-container-list" class="<?php echo $vista_actual == 'list' ? '' : 'hidden'; ?>">
            <div class="bg-white rounded-2xl sm:rounded-[2.5rem] p-3.5 sm:p-8 shadow-sm border border-slate-200/80 overflow-hidden">
                <div class="overflow-x-auto custom-scrollbar pb-4">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-[9px] sm:text-[10px] font-black text-slate-400 uppercase tracking-wider border-b border-slate-100">
                                <th class="px-3 sm:px-6 py-3 sm:py-4 w-14 sm:w-16 text-center whitespace-nowrap">Orden</th>
                                <th class="px-3 sm:px-6 py-3 sm:py-4">Fase / Categoría</th>
                                <th class="px-3 sm:px-6 py-3 sm:py-4 text-center whitespace-nowrap">Estado</th>
                                <th class="px-3 sm:px-6 py-3 sm:py-4 text-center whitespace-nowrap">Reglamento</th>
                                <th class="px-3 sm:px-6 py-3 sm:py-4 text-right whitespace-nowrap">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php
                            $query = "SELECT f.*, c.nombre as cat_nom, 
                                      (CASE WHEN f.id_figura > 0 THEN (SELECT nombre FROM figuras WHERE id = f.id_figura) ELSE (SELECT nombre FROM modalidades WHERE id = f.id_modalidad) END) as item_nom,
                                      (CASE WHEN f.id_figura > 0 THEN (SELECT numero FROM figuras WHERE id = f.id_figura) ELSE NULL END) as item_num
                                      FROM fases f 
                                      JOIN categorias c ON f.id_categoria = c.id 
                                      WHERE f.id_competicion = '$id_comp' 
                                      ORDER BY f.orden, f.id";
                            $res = mysqli_query($connection, $query);
                            while ($row = mysqli_fetch_assoc($res)):
                                $is_puntuada = ($row['puntuada'] == 'si');
                                $is_phase_fig = (!empty($row['id_figura']) && $row['id_figura'] > 0);
                                $isOldSystem = ($row['obsoleto'] == 'si');
                                $color_status = $is_puntuada ? 'emerald' : ($is_phase_fig ? 'blue' : 'purple');
                                $icon_puntuar = $isOldSystem ? 'fa-calculator' : 'fa-square-root-variable';
                                
                                if($is_phase_fig) {
                                    $target_page = ($row['elementos_coach_card'] > 0) ? "puntuaciones_lista_figuras_rutinas_tecnicas.php" : "puntuaciones_lista_figuras.php";
                                    $label = "Figura " . $row['item_num'] . " - " . $row['item_nom'];
                                } else {
                                    $target_page = "puntuaciones_lista_rutinas.php";
                                    $label = $row['item_nom'] . (($row['genero'] ?? '') === 'masculino' ? ' Masc' : '');
                                }
                            ?>
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <td class="px-3 sm:px-6 py-3 sm:py-5 text-center whitespace-nowrap">
                                    <span class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg sm:rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center font-black text-slate-700 shadow-inner group-hover:scale-105 transition-transform tabular-nums text-xs sm:text-sm"><?php echo $row['orden']; ?></span>
                                </td>
                                <td class="px-3 sm:px-6 py-3 sm:py-5 min-w-[200px]">
                                    <p class="text-xs sm:text-sm font-black text-slate-800 uppercase tracking-tight leading-snug break-words"><?php echo $label; ?></p>
                                    <p class="text-[9px] sm:text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-0.5"><?php echo $row['cat_nom']; ?></p>
                                </td>
                                <td class="px-3 sm:px-6 py-3 sm:py-5 text-center whitespace-nowrap">
                                    <?php if($is_puntuada): ?>
                                        <span class="px-2.5 sm:px-3 py-1 bg-emerald-50 text-emerald-600 text-[8px] sm:text-[9px] font-black rounded-lg border border-emerald-100 uppercase tracking-wider">Cerrada</span>
                                    <?php else: ?>
                                        <span class="px-2.5 sm:px-3 py-1 bg-blue-50 text-blue-600 text-[8px] sm:text-[9px] font-black rounded-lg border border-blue-100 uppercase tracking-wider animate-pulse">Abierta</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-3 sm:px-6 py-3 sm:py-5 text-center whitespace-nowrap">
                                    <?php if($isOldSystem): ?>
                                        <span class="px-2.5 sm:px-3 py-1 bg-slate-100 text-slate-500 text-[8px] sm:text-[9px] font-black rounded-lg border border-slate-200 uppercase tracking-wider">OBSOLETO</span>
                                    <?php else: ?>
                                        <span class="px-2.5 sm:px-3 py-1 bg-blue-50 text-blue-600 text-[8px] sm:text-[9px] font-black rounded-lg border border-blue-100 uppercase tracking-wider">AQUA</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-3 sm:px-6 py-3 sm:py-5 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5 sm:gap-3">
                                        <form action="<?php echo $target_page; ?>" method="POST" target="_blank">
                                            <input type="hidden" name="id_fase" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="id_categoria" value="<?php echo $row['id_categoria']; ?>">
                                            <input type="hidden" name="elementos_coach_card" value="<?php echo $row['elementos_coach_card']; ?>">
                                            <button type="submit" name="edit_btn" class="px-3 sm:px-5 py-1.5 sm:py-2.5 bg-<?php echo $color_status; ?>-600 text-white font-black uppercase text-[9px] sm:text-[10px] tracking-wider rounded-lg sm:rounded-xl shadow-md active:scale-[0.98] transition-all flex items-center gap-1.5">
                                                <i class="fas <?php echo $icon_puntuar; ?>"></i> <?php echo $is_puntuada ? 'Revisar' : 'Puntuar'; ?>
                                            </button>
                                        </form>
                                        
                                        <a href="./informes/informe_puntuaciones.php?id_fase=<?php echo $row['id']; ?>&titulo=Clasificación Detallada" target="_blank" class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg sm:rounded-xl bg-white border border-slate-200 text-slate-400 flex items-center justify-center hover:bg-slate-50 active:scale-[0.98] transition-all shadow-xs" title="PDF Resultados">
                                            <i class="fas fa-file-pdf text-[10px] sm:text-xs"></i>
                                        </a>

                                        <form action="fases_edit.php" method="POST" target="_blank">
                                            <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg sm:rounded-xl bg-slate-50 border border-slate-100 text-slate-300 flex items-center justify-center hover:text-blue-600 active:scale-[0.98] transition-all" title="Configuración">
                                                <i class="fas fa-gear text-[10px] sm:text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
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

<!-- El modal de añadir fase se puede simplificar o integrar en un panel lateral -->
<script>
function toggleAddFasePanel() { 
    Swal.fire({
        title: 'Ajustes de Fase',
        text: 'Esta función te permite reordenar o modificar parámetros técnicos. ¿Deseas ir a la configuración completa?',
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Sí, configurar',
        cancelButtonText: 'Más tarde',
        borderRadius: '2rem'
    }).then((result) => { if (result.isConfirmed) window.location.href = 'fases.php'; });
}
</script>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>

<script>
function setVista(tipo) {
    // Guardar en cookie (30 días)
    const d = new Date();
    d.setTime(d.getTime() + (30*24*60*60*1000));
    document.cookie = "pref_vista_fases=" + tipo + ";expires=" + d.toUTCString() + ";path=/";

    // Cambiar contenedores
    const contCards = document.getElementById('view-container-cards');
    const contList = document.getElementById('view-container-list');
    const btnCards = document.getElementById('btn-vista-cards');
    const btnList = document.getElementById('btn-vista-list');

    if(tipo === 'cards') {
        contCards.classList.remove('hidden');
        contList.classList.add('hidden');
        
        btnCards.classList.add('bg-slate-900', 'text-white', 'shadow-lg');
        btnCards.classList.remove('text-slate-400', 'hover:bg-slate-50');
        
        btnList.classList.remove('bg-slate-900', 'text-white', 'shadow-lg');
        btnList.classList.add('text-slate-400', 'hover:bg-slate-50');
    } else {
        contCards.classList.add('hidden');
        contList.classList.remove('hidden');
        
        btnList.classList.add('bg-slate-900', 'text-white', 'shadow-lg');
        btnList.classList.remove('text-slate-400', 'hover:bg-slate-50');
        
        btnCards.classList.remove('bg-slate-900', 'text-white', 'shadow-lg');
        btnCards.classList.add('text-slate-400', 'hover:bg-slate-50');
    }
}
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const idCompeticionActiva = <?php echo (int)$id_comp; ?>;
</script>
<script src="js/publicar_modal.js?v=<?php echo time(); ?>"></script>
<?php
include('includes/footer.php');
?>
