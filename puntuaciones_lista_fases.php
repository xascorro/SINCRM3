<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');

$id_comp = $_SESSION['id_competicion_usuario'];
$is_figuras = ($_SESSION['competicion_figuras_usuario'] == 'si');

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
if($is_figuras) {
    $q_ins = "SELECT COUNT(*) as total FROM inscripciones_figuras WHERE id_fase IN (SELECT id FROM fases WHERE id_competicion = '$id_comp')";
} else {
    $q_ins = "SELECT COUNT(*) as total FROM rutinas WHERE id_competicion = '$id_comp'";
}
$total_inscripciones = mysqli_fetch_assoc(mysqli_query($connection, $q_ins))['total'] ?? 0;
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-surface">
    
    <?php include('includes/topbar.php'); ?>

    <div class="p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend">
        
        <!-- Header Dinámico -->
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div class="animate-fade-in">
                <div class="flex items-center gap-3 mb-2">
                    <span class="px-3 py-1 bg-blue-50 text-blue-600 text-[10px] font-black uppercase tracking-widest rounded-full border border-blue-100 shadow-sm">Modo Mesa Técnica</span>
                    <span class="text-slate-300">/</span>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest"><?php echo date("d M, Y", strtotime($comp_info['fecha'])); ?></span>
                </div>
                <h1 class="text-4xl font-black text-slate-800 tracking-tighter mb-1 flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-stopwatch text-lg"></i></span>
                    Guion de Puntuación
                </h1>
                <p class="text-slate-500 font-medium font-lexend max-w-xl truncate"><?php echo $comp_info['nombre']; ?> @ <span class="text-blue-600 font-bold"><?php echo $comp_info['lugar']; ?></span></p>
            </div>
            <div class="flex gap-3">
                <div class="bg-white p-1.5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-1">
                    <button onclick="setVista('cards')" id="btn-vista-cards" class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all <?php echo $vista_actual == 'cards' ? 'bg-slate-900 text-white shadow-lg' : 'text-slate-400 hover:bg-slate-50'; ?>">
                        <i class="fas fa-th-large mr-2"></i> Tarjetas
                    </button>
                    <button onclick="setVista('list')" id="btn-vista-list" class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all <?php echo $vista_actual == 'list' ? 'bg-slate-900 text-white shadow-lg' : 'text-slate-400 hover:bg-slate-50'; ?>">
                        <i class="fas fa-list mr-2"></i> Lista
                    </button>
                </div>
                <button onclick="window.location.reload()" class="w-12 h-12 rounded-2xl bg-white border border-slate-200 text-slate-400 flex items-center justify-center hover:bg-slate-50 transition-all shadow-sm"><i class="fas fa-sync-alt"></i></button>
                <a href="./informes/informe_puntuaciones_global.php?id_competicion=<?php echo $id_comp; ?>" target="_blank" class="px-6 py-3 bg-slate-800 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg hover:bg-slate-900 transition-all flex items-center gap-2"><i class="fas fa-print text-xs"></i> Global PDF</a>
            </div>
        </div>

        <!-- DASHBOARD DE PROGRESO -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-12">
            <div class="lg:col-span-8 bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-200 flex flex-col md:flex-row items-center gap-10 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/5 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                
                <div class="relative w-32 h-32 flex-shrink-0">
                    <svg viewBox="0 0 100 100" class="w-full h-full transform -rotate-90">
                        <circle cx="50" cy="50" r="45" fill="transparent" stroke="#f1f5f9" stroke-width="10" />
                        <circle cx="50" cy="50" r="45" fill="transparent" stroke="#10b981" stroke-width="10" stroke-dasharray="282.7" stroke-dashoffset="<?php echo 282.7 - (282.7 * $pct_progreso / 100); ?>" class="transition-all duration-1000" stroke-linecap="round" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-2xl font-black text-slate-800 leading-none"><?php echo $pct_progreso; ?>%</span>
                        <span class="text-[8px] font-black uppercase text-slate-400 mt-1">Listo</span>
                    </div>
                </div>

                <div class="flex-1 space-y-4">
                    <div>
                        <h3 class="text-xl font-black text-slate-800 tracking-tight italic">Progreso de la Jornada</h3>
                        <p class="text-xs text-slate-400 font-medium tracking-wide">Fases puntuadas y cerradas en el sistema.</p>
                    </div>
                    <div class="flex flex-wrap gap-4">
                        <div class="px-4 py-2 bg-slate-50 rounded-xl border border-slate-100 flex items-center gap-3">
                            <span class="text-sm font-black text-slate-700"><?php echo $puntuadas; ?> / <?php echo $total_fases; ?></span>
                            <span class="text-[9px] font-black uppercase text-slate-400 tracking-widest">Fases</span>
                        </div>
                        <div class="px-4 py-2 bg-slate-50 rounded-xl border border-slate-100 flex items-center gap-3">
                            <span class="text-sm font-black text-slate-700"><?php echo $total_inscripciones; ?></span>
                            <span class="text-[9px] font-black uppercase text-slate-400 tracking-widest">Participaciones</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-4 flex flex-col gap-6">
                <div class="flex-1 bg-white rounded-[2rem] p-6 shadow-sm border border-slate-200 border-l-[6px] border-l-blue-500 flex items-center gap-6">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 shadow-inner"><i class="fas fa-bolt"></i></div>
                    <div><h3 class="text-2xl font-black text-slate-800 leading-none mb-1">Activo</h3><p class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Modo Puntuación</p></div>
                </div>
                <div class="flex-1 bg-white rounded-[2rem] p-6 shadow-sm border border-slate-200 border-l-[6px] border-l-amber-500 flex items-center gap-6">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600 shadow-inner"><i class="fas fa-file-invoice"></i></div>
                    <div><h3 class="text-2xl font-black text-slate-800 leading-none mb-1"><?php echo ($is_figuras ? 'Figuras' : 'Rutinas'); ?></h3><p class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Especialidad</p></div>
                </div>
            </div>
        </div>

        <?php include('includes/alertas_v4.php'); ?>

        <!-- LISTADO DE FASES (EL GUION) -->
        <div id="view-container-cards" class="<?php echo $vista_actual == 'cards' ? '' : 'hidden'; ?>">
        <?php
        if($is_figuras) {
            // 1. Obtener las categorías únicas presentes en las fases de esta competición
            $q_cats = "SELECT DISTINCT c.id, c.nombre FROM fases f JOIN categorias c ON f.id_categoria = c.id WHERE f.id_competicion = '$id_comp' ORDER BY c.orden, c.id";
            $res_cats = mysqli_query($connection, $q_cats);
            
            while($cat = mysqli_fetch_assoc($res_cats)):
                $id_cat = $cat['id'];
                $cat_nom_actual = $cat['nombre'];
        ?>
                <div class="mb-16 animate-fade-in">
                    <!-- Separador y Título de Categoría -->
                    <div class="flex items-center gap-6 mb-10">
                        <h2 class="text-2xl font-black text-slate-800 uppercase tracking-tighter italic flex items-center gap-4 shrink-0">
                            <span class="w-12 h-12 rounded-2xl bg-blue-600 flex items-center justify-center text-white shadow-lg shadow-blue-500/20"><i class="fas fa-layer-group text-sm"></i></span>
                            Categoría <?php echo $cat_nom_actual; ?>
                        </h2>
                        <div class="h-[2px] flex-1 bg-gradient-to-r from-slate-200 to-transparent"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        <?php
                        $query = "SELECT f.*, c.nombre as cat_nom, fig.nombre as fig_nom, fig.numero as fig_num, fig.grado_dificultad as fig_gd 
                                  FROM fases f 
                                  JOIN categorias c ON f.id_categoria = c.id 
                                  JOIN figuras fig ON f.id_figura = fig.id 
                                  WHERE f.id_competicion = '$id_comp' AND f.id_categoria = '$id_cat'
                                  ORDER BY f.orden, f.id";
                        $res = mysqli_query($connection, $query);
                        while ($row = mysqli_fetch_assoc($res)):
                            $is_puntuada = ($row['puntuada'] == 'si');
                            $has_cc = ($row['elementos_coach_card'] > 0);
                            $isOldSystem = ($row['obsoleto'] == 'si');
                            $color_status = $is_puntuada ? 'emerald' : 'blue';
                            $target_page = $has_cc ? "puntuaciones_lista_figuras_rutinas_tecnicas.php" : "puntuaciones_lista_figuras.php";
                            $icon_puntuar = $isOldSystem ? 'fa-calculator' : 'fa-square-root-variable';
                        ?>
                        <div class="bg-white rounded-[2rem] p-7 shadow-sm border border-slate-200 border-t-[6px] border-t-<?php echo $color_status; ?>-500 relative flex flex-col group hover:shadow-xl transition-all">
                            <!-- Badge Superior -->
                            <div class="flex justify-between items-center mb-5">
                                <div class="flex flex-col items-center justify-center w-11 h-11 rounded-xl bg-slate-50 border border-slate-100 shadow-inner">
                                    <span class="text-[9px] font-black text-slate-300 uppercase leading-none mb-1">Orden</span>
                                    <span class="text-lg font-black text-slate-700 leading-none"><?php echo $row['orden']; ?></span>
                                </div>
                                <?php if($is_puntuada): ?>
                                    <span class="px-3 py-1 bg-emerald-50 text-emerald-600 text-[10px] font-black rounded-md border border-emerald-100 uppercase tracking-widest shadow-sm">Cerrada</span>
                                <?php else: ?>
                                    <span class="px-3 py-1 bg-blue-50 text-blue-600 text-[10px] font-black rounded-md border border-blue-100 uppercase tracking-widest shadow-sm animate-pulse">Abierta</span>
                                <?php endif; ?>
                            </div>

                            <h2 class="text-lg font-black text-slate-800 tracking-tight leading-tight mb-2 truncate" title="<?php echo $row['fig_nom']; ?>">
                                <?php echo $row['fig_nom']; ?>
                            </h2>
                            <div class="flex items-center gap-2 mb-8">
                                <span class="text-xs font-black text-blue-600 uppercase">Fig. <?php echo $row['fig_num']; ?> · GD <?php echo $row['fig_gd']; ?></span>
                            </div>

                            <!-- Botones de Acción -->
                            <div class="mt-auto grid grid-cols-2 gap-3 pt-5 border-t border-slate-50">
                                <form action="<?php echo $target_page; ?>" method="POST" target="_blank" class="col-span-2">
                                    <input type="hidden" name="id_fase" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="id_categoria" value="<?php echo $row['id_categoria']; ?>">
                                    <input type="hidden" name="elementos_coach_card" value="<?php echo $row['elementos_coach_card']; ?>">
                                    <button type="submit" name="edit_btn" class="w-full py-3.5 bg-<?php echo $color_status; ?>-600 text-white font-black uppercase text-xs tracking-widest rounded-xl shadow-lg shadow-<?php echo $color_status; ?>-500/10 hover:scale-[1.02] active:scale-95 transition-all flex items-center justify-center gap-2">
                                        <i class="fas <?php echo $icon_puntuar; ?>"></i>
                                        <?php echo $is_puntuada ? 'Revisar' : 'Puntuar'; ?>
                                    </button>
                                </form>
                                
                                <a href="./informes/informe_puntuaciones.php?id_fase=<?php echo $row['id']; ?>&titulo=Clasificación Detallada" target="_blank" class="col-span-1 py-2.5 bg-white border border-slate-200 text-slate-400 font-black uppercase text-[10px] tracking-widest rounded-xl hover:bg-slate-50 transition-all flex items-center justify-center gap-2">
                                    <i class="fas fa-file-pdf text-[10px]"></i> PDF
                                </a>
                                
                                <form action="fases_edit.php" method="POST" target="_blank" class="col-span-1">
                                    <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="id_figura" value="<?php echo $row['id_figura']; ?>">
                                    <button type="submit" name="edit_btn" class="w-full py-2.5 bg-slate-50 border border-slate-100 text-slate-300 font-black uppercase text-[10px] tracking-widest rounded-xl hover:text-blue-600 transition-all flex items-center justify-center gap-2">
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
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
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
                <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-200 border-t-[8px] border-t-<?php echo $color_status; ?>-500 relative flex flex-col group hover:shadow-2xl transition-all animate-fade-in">
                    <!-- Badge Superior -->
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex flex-col items-center justify-center w-12 h-12 rounded-2xl bg-slate-50 border border-slate-100 shadow-inner">
                            <span class="text-[9px] font-black text-slate-300 uppercase leading-none mb-1">Orden</span>
                            <span class="text-lg font-black text-slate-700 leading-none"><?php echo $row['orden']; ?></span>
                        </div>
                        <?php if($is_puntuada): ?>
                            <span class="px-3 py-1 bg-emerald-50 text-emerald-600 text-[9px] font-black rounded-lg border border-emerald-100 uppercase tracking-widest shadow-sm"><i class="fas fa-check-circle mr-1"></i> Cerrada</span>
                        <?php else: ?>
                            <span class="px-3 py-1 bg-blue-50 text-blue-600 text-[9px] font-black rounded-lg border border-blue-100 uppercase tracking-widest shadow-sm animate-pulse">Abierta</span>
                        <?php endif; ?>
                    </div>

                    <h2 class="text-xl font-black text-slate-800 tracking-tight leading-tight mb-2 truncate">
                        <?php echo $row['mod_nom']; ?>
                    </h2>
                    <div class="flex items-center gap-2 mb-8">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest italic"><?php echo $row['cat_nom']; ?></span>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="mt-auto grid grid-cols-2 gap-3 pt-6 border-t border-slate-50">
                        <form action="puntuaciones_lista_rutinas.php" method="POST" target="_blank" class="col-span-2">
                            <input type="hidden" name="id_fase" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="id_categoria" value="<?php echo $row['id_categoria']; ?>">
                            <button type="submit" name="edit_btn" class="w-full py-4 bg-<?php echo $color_status; ?>-600 text-white font-black uppercase text-xs tracking-[0.2em] rounded-2xl shadow-lg shadow-<?php echo $color_status; ?>-500/20 hover:scale-[1.02] active:scale-95 transition-all flex items-center justify-center gap-3">
                                <i class="fas <?php echo $icon_puntuar; ?>"></i>
                                <?php echo $is_puntuada ? 'Revisar' : 'Puntuar'; ?>
                            </button>
                        </form>
                        
                        <a href="./informes/informe_puntuaciones.php?id_fase=<?php echo $row['id']; ?>&titulo=Clasificación Detallada" target="_blank" class="col-span-1 py-3 bg-white border border-slate-200 text-slate-400 font-black uppercase text-[9px] tracking-widest rounded-2xl hover:bg-slate-50 transition-all flex items-center justify-center gap-2">
                            <i class="fas fa-file-pdf"></i> Resultados
                        </a>
                        
                        <form action="fases_edit.php" method="POST" target="_blank" class="col-span-1">
                            <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="edit_btn" class="w-full py-3 bg-slate-50 border border-slate-100 text-slate-300 font-black uppercase text-[9px] tracking-widest rounded-2xl hover:text-blue-600 transition-all flex items-center justify-center gap-2">
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
            <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto custom-scrollbar pb-4">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead>
                            <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">
                                <th class="px-6 py-4 w-16 text-center">Orden</th>
                                <th class="px-6 py-4">Fase / Categoría</th>
                                <th class="px-6 py-4 text-center">Estado</th>
                                <th class="px-6 py-4 text-center">Reglamento</th>
                                <th class="px-6 py-4 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php
                            $query = "SELECT f.*, c.nombre as cat_nom, 
                                      (CASE WHEN '$is_figuras' = 'si' THEN (SELECT nombre FROM figuras WHERE id = f.id_figura) ELSE (SELECT nombre FROM modalidades WHERE id = f.id_modalidad) END) as item_nom,
                                      (CASE WHEN '$is_figuras' = 'si' THEN (SELECT numero FROM figuras WHERE id = f.id_figura) ELSE NULL END) as item_num
                                      FROM fases f 
                                      JOIN categorias c ON f.id_categoria = c.id 
                                      WHERE f.id_competicion = '$id_comp' 
                                      ORDER BY f.orden, f.id";
                            $res = mysqli_query($connection, $query);
                            while ($row = mysqli_fetch_assoc($res)):
                                $is_puntuada = ($row['puntuada'] == 'si');
                                $isOldSystem = ($row['obsoleto'] == 'si');
                                $color_status = $is_puntuada ? 'emerald' : 'blue';
                                $icon_puntuar = $isOldSystem ? 'fa-calculator' : 'fa-square-root-variable';
                                
                                if($is_figuras) {
                                    $target_page = ($row['elementos_coach_card'] > 0) ? "puntuaciones_lista_figuras_rutinas_tecnicas.php" : "puntuaciones_lista_figuras.php";
                                    $label = "Figura " . $row['item_num'] . " - " . $row['item_nom'];
                                } else {
                                    $target_page = "puntuaciones_lista_rutinas.php";
                                    $label = $row['item_nom'];
                                }
                            ?>
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <td class="px-6 py-5 text-center">
                                    <span class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center font-black text-slate-700 shadow-inner group-hover:scale-110 transition-transform"><?php echo $row['orden']; ?></span>
                                </td>
                                <td class="px-6 py-5">
                                    <p class="text-sm font-black text-slate-800 uppercase tracking-tighter leading-tight"><?php echo $label; ?></p>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1"><?php echo $row['cat_nom']; ?></p>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <?php if($is_puntuada): ?>
                                        <span class="px-3 py-1.5 bg-emerald-50 text-emerald-600 text-[9px] font-black rounded-lg border border-emerald-100 uppercase tracking-widest">Cerrada</span>
                                    <?php else: ?>
                                        <span class="px-3 py-1.5 bg-blue-50 text-blue-600 text-[9px] font-black rounded-lg border border-blue-100 uppercase tracking-widest animate-pulse">Abierta</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <?php if($isOldSystem): ?>
                                        <span class="px-3 py-1.5 bg-slate-100 text-slate-500 text-[9px] font-black rounded-lg border border-slate-200 uppercase tracking-widest">OBSOLETO</span>
                                    <?php else: ?>
                                        <span class="px-3 py-1.5 bg-blue-50 text-blue-600 text-[9px] font-black rounded-lg border border-blue-100 uppercase tracking-widest">AQUA</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <form action="<?php echo $target_page; ?>" method="POST" target="_blank">
                                            <input type="hidden" name="id_fase" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="id_categoria" value="<?php echo $row['id_categoria']; ?>">
                                            <input type="hidden" name="elementos_coach_card" value="<?php echo $row['elementos_coach_card']; ?>">
                                            <button type="submit" name="edit_btn" class="px-5 py-2.5 bg-<?php echo $color_status; ?>-600 text-white font-black uppercase text-[10px] tracking-widest rounded-xl shadow-lg shadow-<?php echo $color_status; ?>-500/10 hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
                                                <i class="fas <?php echo $icon_puntuar; ?>"></i> <?php echo $is_puntuada ? 'Revisar' : 'Puntuar'; ?>
                                            </button>
                                        </form>
                                        
                                        <a href="./informes/informe_puntuaciones.php?id_fase=<?php echo $row['id']; ?>&titulo=Clasificación Detallada" target="_blank" class="w-10 h-10 rounded-xl bg-white border border-slate-200 text-slate-400 flex items-center justify-center hover:bg-slate-50 transition-all shadow-sm" title="PDF Resultados">
                                            <i class="fas fa-file-pdf text-xs"></i>
                                        </a>

                                        <form action="fases_edit.php" method="POST" target="_blank">
                                            <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 text-slate-300 flex items-center justify-center hover:text-blue-600 transition-all" title="Configuración">
                                                <i class="fas fa-gear text-xs"></i>
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
