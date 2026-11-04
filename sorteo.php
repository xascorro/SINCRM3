<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');

$id_comp = $_SESSION['id_competicion_activa'] ?? ($_SESSION['id_competicion_usuario'] ?? null);
$comp_figuras = $_SESSION['competicion_figuras_usuario'] ?? 'no';
$is_mixta = ($comp_figuras == 'mixta');
$is_figuras_only = ($comp_figuras == 'si');
$is_rutinas_only = ($comp_figuras == 'no');

// Tab inicial: si es solo figuras o solo rutinas, forzar. Si es mixta, leer $_GET['tab']
if ($is_figuras_only) {
    $current_tab = 'figuras';
} elseif ($is_rutinas_only) {
    $current_tab = 'rutinas';
} else {
    $current_tab = (isset($_GET['tab']) && $_GET['tab'] === 'rutinas') ? 'rutinas' : 'figuras';
}

// Paleta de colores para cortes de figuras
$v3_colors = [
    1 => ["border" => "border-l-emerald-500", "badge" => "bg-emerald-500 text-white border-emerald-600", "bg" => "bg-emerald-50/20"],
    2 => ["border" => "border-l-blue-500", "badge" => "bg-blue-500 text-white border-blue-600", "bg" => "bg-blue-50/20"],
    3 => ["border" => "border-l-amber-500", "badge" => "bg-amber-500 text-white border-amber-600", "bg" => "bg-amber-50/20"],
    4 => ["border" => "border-l-red-500", "badge" => "bg-red-500 text-white border-red-600", "bg" => "bg-red-50/20"],
    5 => ["border" => "border-l-purple-500", "badge" => "bg-purple-500 text-white border-purple-600", "bg" => "bg-purple-50/20"],
    6 => ["border" => "border-l-pink-500", "badge" => "bg-pink-500 text-white border-pink-600", "bg" => "bg-pink-50/20"],
    7 => ["border" => "border-l-indigo-500", "badge" => "bg-indigo-500 text-white border-indigo-600", "bg" => "bg-indigo-50/20"]
];
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-surface">
    
    <?php include('includes/topbar.php'); ?>

    <div class="px-3 py-4 sm:p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend">
        
        <!-- Header de Sección -->
        <div class="mb-6 sm:mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4 sm:gap-6">
            <div>
                <h1 class="text-2xl sm:text-4xl font-black text-slate-800 tracking-tight mb-1 sm:mb-2 flex items-center gap-2.5 sm:gap-3">
                    <span class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200">
                        <i class="fa-solid fa-wand-magic-sparkles text-base sm:text-lg"></i>
                    </span>
                    <span>Orden de Salida <span class="text-slate-300 font-light mx-1 sm:mx-2">/</span> Sorteos</span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 font-medium">
                    <?php if($is_mixta): ?>
                        Gestión combinada de cortes de figuras y turnos de rutinas · Competición #<span class="tabular-nums"><?php echo $id_comp; ?></span>
                    <?php elseif($is_figuras_only): ?>
                        Distribución técnica por cortes · Competición #<span class="tabular-nums"><?php echo $id_comp; ?></span>
                    <?php else: ?>
                        Generación aleatoria y gestión de turnos de actuación · Competición #<span class="tabular-nums"><?php echo $id_comp; ?></span>
                    <?php endif; ?>
                </p>
            </div>

            <!-- Botones de Acción (Contextuales a la pestaña activa) -->
            <div class="flex flex-wrap items-center gap-2 sm:gap-3 w-full sm:w-auto">
                
                <?php if($is_mixta): ?>
                <!-- Acción Sorteo Integral (Competición Completa) -->
                <form action="sorteo_figuras_code.php" method="POST" class="inline" onsubmit="return confirm('¿Deseas sortear TODAS las fases de la competición (Figuras y Rutinas) de una sola vez?');">
                    <button type="submit" name="sorteo_completo_btn" class="px-4 sm:px-6 py-2.5 sm:py-3 bg-indigo-600 text-white font-black uppercase text-[11px] sm:text-xs tracking-wider rounded-xl sm:rounded-2xl shadow-lg shadow-indigo-500/20 active:scale-[0.98] hover:bg-indigo-700 transition-all flex items-center gap-2">
                        <i class="fas fa-dice text-xs"></i> Sortear Todo
                    </button>
                </form>
                <?php endif; ?>

                <!-- Acciones Figuras -->
                <div id="actionsFiguras" class="flex flex-wrap items-center gap-2 sm:gap-3 w-full sm:w-auto <?php echo $current_tab === 'figuras' ? '' : 'hidden'; ?>">
                    <button type="button" onclick="toggleAddSorteoFiguras()" class="flex-1 sm:flex-initial justify-center px-4 sm:px-6 py-2.5 sm:py-3 bg-blue-600 text-white font-black uppercase text-[11px] sm:text-xs tracking-wider rounded-xl sm:rounded-2xl shadow-lg shadow-blue-500/20 active:scale-[0.98] transition-all flex items-center gap-2">
                        <i class="fas fa-random text-xs"></i> Sortear Figuras
                    </button>
                    <button type="button" onclick="toggleAnularFiguras()" class="px-3.5 sm:px-5 py-2.5 sm:py-3 bg-white border border-red-200 text-red-500 font-bold text-[11px] sm:text-xs uppercase tracking-wider rounded-xl sm:rounded-2xl hover:bg-red-50 active:scale-[0.98] transition-all flex items-center gap-2 shadow-sm">
                        <i class="fas fa-trash-can text-xs"></i> Anular
                    </button>
                    <a href="./informes/informe_figuras.php?id_competicion=<?php echo $id_comp; ?>&titulo=Orden de salida" target="_blank" class="px-3.5 sm:px-5 py-2.5 sm:py-3 bg-emerald-600 text-white font-bold text-[11px] sm:text-xs uppercase tracking-wider rounded-xl sm:rounded-2xl hover:bg-emerald-700 active:scale-[0.98] transition-all flex items-center gap-2 shadow-md">
                        <i class="fas fa-file-pdf text-xs"></i> PDF Oficial
                    </a>
                </div>

                <!-- Acciones Rutinas -->
                <div id="actionsRutinas" class="flex flex-wrap items-center gap-2 sm:gap-3 w-full sm:w-auto <?php echo $current_tab === 'rutinas' ? '' : 'hidden'; ?>">
                    <button type="button" onclick="toggleAddSorteoRutinas()" class="flex-1 sm:flex-initial justify-center px-4 sm:px-6 py-2.5 sm:py-3 bg-blue-600 text-white font-black uppercase text-[11px] sm:text-xs tracking-wider rounded-xl sm:rounded-2xl shadow-lg shadow-blue-500/20 active:scale-[0.98] transition-all flex items-center gap-2">
                        <i class="fas fa-random text-xs"></i> Sortear Rutinas
                    </button>
                    <button type="button" onclick="toggleAnularRutinas()" class="px-3.5 sm:px-5 py-2.5 sm:py-3 bg-white border border-red-200 text-red-500 font-bold text-[11px] sm:text-xs uppercase tracking-wider rounded-xl sm:rounded-2xl hover:bg-red-50 active:scale-[0.98] transition-all flex items-center gap-2 shadow-sm">
                        <i class="fas fa-trash-can text-xs"></i> Anular Todo
                    </button>
                    <a href="./informes/informe_figuras.php?id_competicion=<?php echo $id_comp; ?>&titulo=Orden de salida" target="_blank" class="px-3.5 sm:px-5 py-2.5 sm:py-3 bg-emerald-600 text-white font-bold text-[11px] sm:text-xs uppercase tracking-wider rounded-xl sm:rounded-2xl hover:bg-emerald-700 active:scale-[0.98] transition-all flex items-center gap-2 shadow-md">
                        <i class="fas fa-file-pdf text-xs"></i> PDF Oficial
                    </a>
                </div>

            </div>
        </div>

        <!-- Alertas -->
        <?php include('includes/alertas_v4.php'); ?>

        <?php if($is_mixta): ?>
        <!-- Segmented Tab Selector (Estilo Emil Kowalski / Zero-Gray) -->
        <div class="mb-6 sm:mb-8">
            <div class="inline-flex p-1.5 bg-slate-200/80 rounded-2xl border border-slate-300/70 shadow-inner">
                <button type="button" id="btnTabFiguras" onclick="setSorteoTab('figuras')" 
                        class="px-5 sm:px-7 py-2.5 rounded-xl sm:rounded-2xl font-black text-xs sm:text-sm uppercase tracking-wider transition-all duration-300 flex items-center gap-2.5 <?php echo $current_tab === 'figuras' ? 'bg-white text-blue-600 shadow-md ring-1 ring-black/5' : 'text-slate-600 hover:text-slate-900'; ?>">
                    <i class="fa-solid fa-shapes text-sm"></i>
                    <span>Figuras <span class="hidden sm:inline font-normal text-[11px] opacity-80">(Cortes)</span></span>
                </button>
                <button type="button" id="btnTabRutinas" onclick="setSorteoTab('rutinas')" 
                        class="px-5 sm:px-7 py-2.5 rounded-xl sm:rounded-2xl font-black text-xs sm:text-sm uppercase tracking-wider transition-all duration-300 flex items-center gap-2.5 <?php echo $current_tab === 'rutinas' ? 'bg-white text-blue-600 shadow-md ring-1 ring-black/5' : 'text-slate-600 hover:text-slate-900'; ?>">
                    <i class="fa-solid fa-wand-magic-sparkles text-sm"></i>
                    <span>Rutinas <span class="hidden sm:inline font-normal text-[11px] opacity-80">(Fases)</span></span>
                </button>
            </div>
        </div>
        <?php endif; ?>

        <!-- ========================================== -->
        <!-- PANEL FIGURAS                              -->
        <!-- ========================================== -->
        <div id="tabContentFiguras" class="<?php echo $current_tab === 'figuras' ? '' : 'hidden'; ?>">
            
            <!-- Panel Nuevo Sorteo Figuras (Colapsable) -->
            <div id="addSorteoFigurasPanel" class="hidden mb-6 sm:mb-10 animate-fade-in-down">
                <div class="bg-white rounded-2xl sm:rounded-[2.5rem] p-4 sm:p-8 shadow-xl border border-blue-100 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                    <h2 class="text-lg sm:text-xl font-black text-slate-800 mb-4 sm:mb-8 flex items-center gap-2.5 sm:gap-3"><i class="fas fa-dice text-blue-600"></i> Configurar Sorteo de Figuras por Categoría</h2>
                    <form id="sortearFigurasForm" action="sorteo_figuras_code.php" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-4 sm:gap-6">
                        <div class="md:col-span-6 space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1">Categoría a sortear</label>
                            <?php 
                            ob_start();
                            include('includes/categorias_competicion_select_option.php');
                            $select_html = ob_get_clean();
                            $select_html = preg_replace('/<label.*?>.*?<\/label>/i', '', $select_html);
                            $select_html = str_replace("name='categoria'", "name='id_categoria'", $select_html);
                            $select_html = str_replace("class='form-control'", "class='v3-select-fix'", $select_html);
                            echo $select_html;
                            ?>
                        </div>
                        <div class="md:col-span-3 space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1">Método de Redondeo</label>
                            <select name="redondeo" class="v3-select-fix">
                                <option value="ceil">Hacia arriba (Ceil)</option>
                                <option value="floor">Hacia abajo (Floor)</option>
                            </select>
                        </div>
                        <div class="md:col-span-3 flex items-end">
                            <input type="hidden" name="save_btn" value="1">
                            <button type="submit" class="w-full py-3.5 sm:py-4 bg-blue-600 text-white font-black uppercase text-xs tracking-wider rounded-xl sm:rounded-2xl shadow-lg hover:bg-blue-700 active:scale-[0.98] transition-all">Ejecutar Sorteo</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Panel Anular Figuras (Colapsable) -->
            <div id="anularFigurasPanel" class="hidden mb-6 sm:mb-10 animate-fade-in-down">
                <div class="bg-red-50 rounded-2xl sm:rounded-[2.5rem] p-4 sm:p-8 border border-red-100 flex flex-col md:flex-row items-center justify-between gap-4 sm:gap-8">
                    <div class="flex items-center gap-4 sm:gap-6 w-full md:w-auto">
                        <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-xl sm:rounded-2xl bg-white flex items-center justify-center text-red-500 shadow-sm border border-red-100 flex-shrink-0"><i class="fas fa-trash-can text-lg sm:text-2xl"></i></div>
                        <div><h3 class="text-base sm:text-lg font-black text-red-800 leading-tight">Limpiar sorteos de figuras</h3><p class="text-xs sm:text-sm text-red-600 font-medium">Se eliminarán todos los órdenes de salida para la sesión de figuras.</p></div>
                    </div>
                    <form action="sorteo_figuras_code.php" method="POST" class="w-full md:w-auto flex gap-4">
                        <button type="button" onclick="toggleAnularFiguras()" class="px-4 py-2.5 text-xs font-black uppercase text-slate-400 hover:text-slate-600">Cancelar</button>
                        <button type="submit" name="delete_btn" class="w-full md:w-auto px-6 sm:px-10 py-3 bg-red-600 text-white font-black uppercase text-xs tracking-wider rounded-xl sm:rounded-2xl shadow-md active:scale-[0.98] transition-all">Confirmar Borrado</button>
                    </form>
                </div>
            </div>

            <!-- LISTADO DINÁMICO DE FIGURAS -->
            <div class="space-y-10 sm:space-y-16">
                <?php
                $q_cat = "SELECT DISTINCT f.id_categoria, c.nombre 
                          FROM fases f, categorias c 
                          WHERE f.id_categoria = c.id AND f.id_competicion = '$id_comp' AND f.id_figura > 0
                          ORDER BY c.edad_minima ASC";
                $res_cats = mysqli_query($connection, $q_cat);
                
                if (mysqli_num_rows($res_cats) == 0):
                ?>
                    <div class="text-center py-12 bg-white rounded-2xl border border-slate-200/80 p-8">
                        <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3 text-slate-400">
                            <i class="fa-solid fa-shapes text-2xl"></i>
                        </div>
                        <h3 class="text-base font-black text-slate-700 uppercase">Sin fases de figuras registradas</h3>
                        <p class="text-xs text-slate-400 mt-1">Esta competición no cuenta con fases configuradas con figuras técnicas.</p>
                    </div>
                <?php
                endif;

                while ($row_cat = mysqli_fetch_assoc($res_cats)):
                    $id_cat = $row_cat['id_categoria'];
                    
                    // Mapeo Dinámico de Fases de Figuras
                    $q_map = "SELECT id FROM fases WHERE id_categoria = '$id_cat' AND id_competicion = '$id_comp' AND id_figura > 0 ORDER BY orden ASC";
                    $res_map = mysqli_query($connection, $q_map);
                    $map_fases = [];
                    $pos = 1;
                    while($mf = mysqli_fetch_assoc($res_map)) {
                        $map_fases[$mf['id']] = $pos++;
                    }
                    $num_cortes = count($map_fases);
                ?>
                <div>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 sm:gap-4 mb-4 sm:mb-6 border-b border-slate-200/80 pb-3 sm:pb-4">
                        <h2 class="text-xl sm:text-3xl font-black text-slate-800 tracking-tight uppercase italic"><?php echo $row_cat['nombre'];?></h2>
                        <div class="flex flex-wrap gap-1.5 sm:gap-2">
                            <?php for($c=1; $c <= $num_cortes; $c++): 
                                $c_style = $v3_colors[$c] ?? $v3_colors[1];
                            ?>
                                <span class="px-2.5 sm:px-3 py-0.5 sm:py-1 <?php echo $c_style['badge']; ?> text-[9px] font-black rounded uppercase">C<?php echo $c; ?></span>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-2 relative">
                        <?php
                        reset($map_fases);
                        $id_fase_base = key($map_fases);

                        if($id_fase_base):
                            $q_nad = "SELECT i.orden, i.id_nadadora, n.nombre, n.apellidos, n.año_nacimiento as año, cl.nombre_corto as club 
                                      FROM inscripciones_figuras i 
                                      JOIN nadadoras n ON i.id_nadadora = n.id 
                                      JOIN clubes cl ON n.club = cl.id 
                                      WHERE i.id_fase = '$id_fase_base' 
                                      ORDER BY i.orden ASC";
                            $res_nad = mysqli_query($connection, $q_nad);
                            
                            if (mysqli_num_rows($res_nad) == 0):
                            ?>
                                <p class="text-xs text-slate-400 italic py-3">No hay nadadoras inscritas o pendientes de sortear en esta categoría.</p>
                            <?php
                            endif;

                            while ($row = mysqli_fetch_assoc($res_nad)):
                                $id_nadadora = $row['id_nadadora'];
                                
                                $q_chk = "SELECT id_fase FROM inscripciones_figuras 
                                          WHERE id_nadadora = '$id_nadadora' AND orden = 1 
                                          AND id_fase IN (".implode(',', array_keys($map_fases)).")";
                                $res_chk = mysqli_query($connection, $q_chk);
                                
                                $accent_color = "border-l-slate-200";
                                $badge_color = "hidden";
                                $bg_color = "bg-white";
                                $corte_label = "";
                                
                                if(mysqli_num_rows($res_chk) > 0) {
                                    $id_fase_corte = mysqli_fetch_assoc($res_chk)['id_fase'];
                                    $pos_corte = $map_fases[$id_fase_corte];
                                    $c_style = $v3_colors[$pos_corte] ?? $v3_colors[1];
                                    $accent_color = $c_style['border'];
                                    $badge_color = $c_style['badge'];
                                    $bg_color = $c_style['bg'];
                                    $corte_label = "C".$pos_corte;
                                }
                        ?>
                        <div class="<?php echo $bg_color; ?> rounded-xl p-3 sm:p-4 border border-slate-100/80 border-l-4 sm:border-l-[6px] <?php echo $accent_color; ?> flex items-center gap-3 sm:gap-6 hover:shadow-sm transition-all group">
                            <div class="w-9 sm:w-12 text-center flex-shrink-0">
                                <?php 
                                    $orden_display = $row['orden'];
                                    $text_size = "text-xs sm:text-sm";
                                    if($row['orden'] <= -1 && $row['orden'] >= -9) {
                                        $orden_display = "PS";
                                        $text_size = "text-[10px]";
                                    } else if($row['orden'] <= -10) {
                                        $orden_display = "E";
                                    }
                                ?>
                                <span class="<?php echo $text_size; ?> font-black text-slate-800 tabular-nums"><?php echo $orden_display; ?></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs sm:text-sm font-black text-slate-700 leading-tight truncate"><?php echo $row['apellidos'].', '.$row['nombre']; ?></p>
                                <div class="flex items-center gap-2 sm:gap-4 mt-0.5">
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider truncate"><?php echo $row['club']; ?></span>
                                    <span class="text-[9px] font-medium text-slate-300 italic tabular-nums"><?php echo $row['año']; ?></span>
                                </div>
                            </div>
                            <?php if($corte_label): ?>
                            <div class="flex-shrink-0 px-2 sm:px-3 py-0.5 sm:py-1 rounded-md sm:rounded-lg border <?php echo $badge_color; ?> shadow-xs">
                                <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-wider"><?php echo $corte_label; ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endwhile; endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- PANEL RUTINAS                              -->
        <!-- ========================================== -->
        <div id="tabContentRutinas" class="<?php echo $current_tab === 'rutinas' ? '' : 'hidden'; ?>">
            
            <!-- Panel Nuevo Sorteo Rutinas (Colapsable) -->
            <div id="addSorteoRutinasPanel" class="hidden mb-6 sm:mb-10 animate-fade-in-down">
                <div class="bg-white rounded-2xl sm:rounded-[2.5rem] p-4 sm:p-8 shadow-xl border border-blue-100 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                    <h2 class="text-lg sm:text-xl font-black text-slate-800 mb-4 sm:mb-8 flex items-center gap-2.5 sm:gap-3"><i class="fas fa-dice text-blue-600"></i> Configurar Sorteo de Rutinas por Fase</h2>
                    <form id="sortearRutinasForm" action="sorteo_rutinas_code.php" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-4 sm:gap-6">
                        <div class="md:col-span-8 space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1">Seleccionar Fase de Competición</label>
                            <?php 
                            $include_todas_fases = true;
                            ob_start();
                            include('includes/fases_competicion_select_option.php');
                            $select_html = ob_get_clean();
                            $select_html = preg_replace('/<label.*?>.*?<\/label>/i', '', $select_html);
                            $select_html = str_replace("name='fase'", "name='id_fase'", $select_html);
                            $select_html = str_replace("class='form-control'", "class='v3-select-fix'", $select_html);
                            echo $select_html;
                            ?>
                        </div>
                        <div class="md:col-span-4 flex items-end">
                            <input type="hidden" name="save_btn" value="1">
                            <button id="sortearBtnRutinas" type="submit" class="w-full py-3.5 sm:py-4 bg-blue-600 text-white font-black uppercase text-xs tracking-wider rounded-xl sm:rounded-2xl shadow-lg hover:bg-blue-700 active:scale-[0.98] transition-all flex items-center justify-center gap-2.5 group">
                                <i class="fa-solid fa-wand-sparkles group-hover:rotate-12 transition-transform"></i> Ejecutar Sorteo Mágico
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Panel Anular Rutinas (Colapsable) -->
            <div id="anularRutinasPanel" class="hidden mb-6 sm:mb-10 animate-fade-in-down">
                <div class="bg-red-50 rounded-2xl sm:rounded-[2.5rem] p-4 sm:p-8 border border-red-100 flex flex-col md:flex-row items-center justify-between gap-4 sm:gap-8">
                    <div class="flex items-center gap-4 sm:gap-6 w-full md:w-auto">
                        <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-xl sm:rounded-2xl bg-white flex items-center justify-center text-red-500 shadow-sm border border-red-100 flex-shrink-0"><i class="fas fa-triangle-exclamation text-lg sm:text-2xl"></i></div>
                        <div>
                            <h3 class="text-lg font-black text-red-800 leading-tight">¿Anular todos los sorteos de rutinas?</h3>
                            <p class="text-sm text-red-600 font-medium">Esta acción eliminará el orden de salida de TODAS las fases de rutinas. No se puede deshacer.</p>
                        </div>
                    </div>
                    <form action="sorteo_rutinas_code.php" method="POST" class="w-full md:w-auto flex items-center justify-end gap-3 sm:gap-4">
                        <button type="button" onclick="toggleAnularRutinas()" class="px-4 py-2.5 text-xs font-black uppercase text-slate-400 hover:text-slate-600">Cancelar</button>
                        <button type="submit" name="delete_btn" class="px-6 sm:px-8 py-3 bg-red-600 text-white font-black uppercase text-xs tracking-wider rounded-xl sm:rounded-2xl shadow-md active:scale-[0.98] transition-all">Confirmar Borrado</button>
                    </form>
                </div>
            </div>

            <!-- LISTADO POR CARRILES DE RUTINAS -->
            <div class="space-y-10 sm:space-y-16">
                <?php
                $query = "SELECT DISTINCT f.id as id_fase, f.id_categoria, f.genero, c.nombre as categoria, m.nombre as modalidad 
                          FROM fases f, categorias c, modalidades m 
                          WHERE f.id_modalidad=m.id AND f.id_categoria = c.id AND f.id_competicion = '$id_comp' 
                            AND m.id != 8 AND (f.id_figura IS NULL OR f.id_figura = 0)
                          ORDER BY f.orden";
                $query_fases = mysqli_query($connection, $query);

                if (mysqli_num_rows($query_fases) == 0):
                ?>
                    <div class="text-center py-12 bg-white rounded-2xl border border-slate-200/80 p-8">
                        <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3 text-slate-400">
                            <i class="fa-solid fa-users-line text-2xl"></i>
                        </div>
                        <h3 class="text-base font-black text-slate-700 uppercase">Sin fases de rutinas registradas</h3>
                        <p class="text-xs text-slate-400 mt-1">Esta competición no cuenta con fases configuradas para rutinas.</p>
                    </div>
                <?php
                endif;

                while ($row_fases = mysqli_fetch_assoc($query_fases)):
                ?>
                <div class="relative">
                    <!-- Título de Fase (Sticky Header) -->
                    <div class="sticky top-16 sm:top-20 z-20 bg-surface/90 backdrop-blur-md py-3 sm:py-4 mb-4 sm:mb-6 border-b border-slate-200/80 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5 sm:gap-4 min-w-0">
                            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg sm:rounded-xl bg-slate-800 flex items-center justify-center text-white font-black text-[11px] sm:text-xs shadow-xs flex-shrink-0">
                                <?php echo strtoupper(substr($row_fases['modalidad'],0,1)); ?>
                            </div>
                            <h2 class="text-base sm:text-2xl font-black text-slate-800 tracking-tight truncate">
                                <?php echo $row_fases['modalidad'] . (($row_fases['genero'] ?? '') === 'masculino' ? ' Masc' : '');?> <span class="text-blue-600 ml-1.5 sm:ml-2 font-medium"><?php echo $row_fases['categoria'];?></span>
                            </h2>
                        </div>
                        <?php
                        $q_count = "SELECT COUNT(*) as total FROM rutinas WHERE id_fase = ".$row_fases['id_fase'];
                        $num_r = mysqli_fetch_assoc(mysqli_query($connection, $q_count))['total'];
                        ?>
                        <span class="px-3 sm:px-4 py-1 sm:py-1.5 bg-white text-slate-500 text-[9px] sm:text-[10px] font-black uppercase tracking-wider rounded-full border border-slate-200 shadow-xs flex-shrink-0 tabular-nums"><?php echo $num_r; ?> Rutinas</span>
                    </div>

                    <!-- Carril de Salida -->
                    <div class="grid grid-cols-1 gap-2.5 sm:gap-3 relative">
                        <div class="absolute left-10 top-0 bottom-0 w-px bg-slate-200 hidden md:block"></div>

                        <?php
                        $query_r = "SELECT r.id as id_rutina, r.orden, cl.nombre_corto as nombre_club 
                                    FROM rutinas r, clubes cl 
                                    WHERE r.id_fase = ".$row_fases['id_fase']." AND r.id_club = cl.id 
                                    ORDER BY orden";
                        $res_r = mysqli_query($connection, $query_r);
                        if(mysqli_num_rows($res_r) > 0):
                            while ($row = mysqli_fetch_assoc($res_r)):
                                $nombres_q = "SELECT group_concat(n.nombre, ' ', n.apellidos SEPARATOR ', ') as atletas 
                                              FROM rutinas_participantes rp 
                                              JOIN nadadoras n ON rp.id_nadadora = n.id 
                                              WHERE rp.reserva = 'no' AND rp.id_rutina = ".$row['id_rutina'];
                                $nombres = mysqli_fetch_assoc(mysqli_query($connection, $nombres_q))['atletas'];
                                
                                $accent_color = "border-l-slate-200";
                                $bg_color = "bg-white";
                                $orden_label = $row['orden'];

                                if($row['orden'] <= -1 && $row['orden'] >= -9) {
                                    $orden_label = "PS";
                                    $accent_color = "border-l-amber-400";
                                    $bg_color = "bg-amber-50/30";
                                } else if($row['orden'] <= -10) {
                                    $orden_label = "E";
                                    $accent_color = "border-l-purple-400";
                                    $bg_color = "bg-purple-50/30";
                                } else if($row['orden'] == '1') {
                                    $accent_color = "border-l-emerald-500";
                                    $bg_color = "bg-emerald-50/30";
                                }
                        ?>
                        <!-- Tarjeta de Salida -->
                        <div class="<?php echo $bg_color; ?> rounded-xl sm:rounded-2xl p-3 sm:p-5 md:p-6 shadow-xs sm:shadow-sm border border-slate-100/90 border-l-4 sm:border-l-[8px] <?php echo $accent_color; ?> group hover:shadow-md transition-all relative z-10 ml-0 md:ml-4">
                            
                            <!-- Vista Móvil (< md) -->
                            <div class="md:hidden flex items-start gap-3">
                                <div class="flex-shrink-0 w-11 h-11 rounded-xl bg-slate-50 border border-slate-200/80 flex flex-col items-center justify-center shadow-xs">
                                    <span class="text-[8px] font-black uppercase text-slate-400 leading-none">Ord</span>
                                    <span class="text-sm font-black text-slate-800 leading-none mt-0.5 tabular-nums"><?php echo $orden_label; ?></span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2 mb-1">
                                        <span class="text-[9px] font-black text-blue-700 uppercase bg-blue-50 px-2 py-0.5 rounded border border-blue-100/60 truncate"><?php echo $row['nombre_club']; ?></span>
                                        <span class="text-[9px] font-bold text-slate-300 tabular-nums">#<?php echo $row['id_rutina']; ?></span>
                                    </div>
                                    <p class="text-xs font-bold text-slate-700 leading-snug"><?php echo $nombres; ?></p>
                                </div>
                            </div>

                            <!-- Vista Desktop (>= md) -->
                            <div class="hidden md:flex items-center gap-6">
                                <div class="flex-shrink-0 w-20 text-center">
                                    <p class="text-[9px] font-black uppercase text-slate-400 mb-0.5">Orden</p>
                                    <span class="text-xl font-black text-slate-800 tabular-nums"><?php echo $orden_label; ?></span>
                                </div>
                                <div class="flex-shrink-0 w-32 border-x border-slate-100 px-4 text-center">
                                    <p class="text-[9px] font-black uppercase text-slate-400 mb-0.5">Entidad</p>
                                    <span class="text-xs font-black text-blue-600 uppercase tracking-wider"><?php echo $row['nombre_club']; ?></span>
                                </div>
                                <div class="flex-1 text-left">
                                    <p class="text-[9px] font-black uppercase text-slate-400 mb-0.5">Participantes</p>
                                    <p class="text-sm font-bold text-slate-600 italic leading-snug"><?php echo $nombres; ?></p>
                                </div>
                                <div class="flex-shrink-0 text-right opacity-30 group-hover:opacity-100 transition-opacity">
                                    <span class="text-[10px] font-black text-slate-300 tabular-nums">ID #<?php echo $row['id_rutina']; ?></span>
                                </div>
                            </div>

                        </div>
                        <?php endwhile; else: ?>
                        <div class="bg-white rounded-xl sm:rounded-2xl p-6 sm:p-8 text-center border border-dashed border-slate-200">
                            <p class="text-xs sm:text-sm font-bold text-slate-400 uppercase tracking-wider">No hay rutinas inscritas o sorteadas en esta fase</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

    </div>
</main>

<!-- Modal de Animación Mágica -->
<div id="animacionModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center bg-slate-900/90 backdrop-blur-xl transition-all duration-500 opacity-0">
    <div class="text-center">
        <div class="relative inline-block mb-12">
            <div class="absolute inset-0 animate-spin-slow">
                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-3 h-3 bg-blue-400 rounded-full blur-sm"></div>
                <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-3 h-3 bg-emerald-400 rounded-full blur-sm"></div>
            </div>
            <i id="iconoAnimacion" class="fa-solid fa-wand-magic-sparkles text-7xl text-white drop-shadow-[0_0_20px_rgba(255,255,255,0.5)]"></i>
        </div>
        <h2 id="animationText" class="text-3xl font-black text-white tracking-tighter mb-4">Invocando el azar...</h2>
        <p class="text-slate-400 font-medium uppercase tracking-[0.3em] text-xs">Sistema de Sorteo SINCRM v4.0</p>
    </div>
</div>

<script>
// Manejo de paneles colapsables
function toggleAddSorteoFiguras() { document.getElementById('addSorteoFigurasPanel')?.classList.toggle('hidden'); }
function toggleAnularFiguras() { document.getElementById('anularFigurasPanel')?.classList.toggle('hidden'); }
function toggleAddSorteoRutinas() { document.getElementById('addSorteoRutinasPanel')?.classList.toggle('hidden'); }
function toggleAnularRutinas() { document.getElementById('anularRutinasPanel')?.classList.toggle('hidden'); }

// Conmutador de Pestañas
function setSorteoTab(tab) {
    const tabFig = document.getElementById('tabContentFiguras');
    const tabRut = document.getElementById('tabContentRutinas');
    const actFig = document.getElementById('actionsFiguras');
    const actRut = document.getElementById('actionsRutinas');
    const btnFig = document.getElementById('btnTabFiguras');
    const btnRut = document.getElementById('btnTabRutinas');

    if (tab === 'figuras') {
        tabFig?.classList.remove('hidden');
        tabRut?.classList.add('hidden');
        actFig?.classList.remove('hidden');
        actRut?.classList.add('hidden');
        
        btnFig?.classList.add('bg-white', 'text-blue-600', 'shadow-md', 'ring-1', 'ring-black/5');
        btnFig?.classList.remove('text-slate-600', 'hover:text-slate-900');
        btnRut?.classList.remove('bg-white', 'text-blue-600', 'shadow-md', 'ring-1', 'ring-black/5');
        btnRut?.classList.add('text-slate-600', 'hover:text-slate-900');
    } else {
        tabFig?.classList.add('hidden');
        tabRut?.classList.remove('hidden');
        actFig?.classList.add('hidden');
        actRut?.classList.remove('hidden');
        
        btnRut?.classList.add('bg-white', 'text-blue-600', 'shadow-md', 'ring-1', 'ring-black/5');
        btnRut?.classList.remove('text-slate-600', 'hover:text-slate-900');
        btnFig?.classList.remove('bg-white', 'text-blue-600', 'shadow-md', 'ring-1', 'ring-black/5');
        btnFig?.classList.add('text-slate-600', 'hover:text-slate-900');
    }

    // Actualizar URL sin recargar
    try {
        const url = new URL(window.location);
        url.searchParams.set('tab', tab);
        window.history.replaceState({}, '', url);
    } catch(e) {}
}

document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('animacionModal');
    const text = document.getElementById('animationText');
    const icon = document.getElementById('iconoAnimacion');

    function ejecutarAnimacionMagica(formElement, tipo) {
        if(!modal) {
            formElement.submit();
            return;
        }
        modal.classList.remove('hidden');
        setTimeout(() => modal.classList.add('opacity-100'), 10);
        
        const frasesFiguras = [
            { t: "Calculando trayectorias hidrodinámicas...", c: "text-cyan-400", i: "fa-water" },
            { t: "Mezclando el bombo de cristal...", c: "text-purple-400", i: "fa-gem" },
            { t: "Esparciendo polvos de aleatoriedad...", c: "text-pink-400", i: "fa-wand-sparkles" },
            { t: "Convenciendo a los jueces de que el azar existe...", c: "text-amber-400", i: "fa-gavel" },
            { t: "Ordenando el caos con estilo...", c: "text-blue-500", i: "fa-layer-group" },
            { t: "¡Sorteo finalizado con éxito!", c: "text-emerald-400", i: "fa-trophy" }
        ];

        const frasesRutinas = [
            { t: "Consultando al oráculo de Delfos...", c: "text-blue-400", i: "fa-spaghetti-monster-flying" },
            { t: "Mezclando los dorsales con elegancia...", c: "text-purple-400", i: "fa-shuffle" },
            { t: "Sacudiendo la chistera mágica...", c: "text-amber-400", i: "fa-hat-wizard" },
            { t: "Invocando el espíritu de la competición...", c: "text-red-400", i: "fa-ghost" },
            { t: "Alineando los astros de la piscina...", c: "text-indigo-400", i: "fa-star-and-crescent" },
            { t: "Batiendo récords de aleatoriedad...", c: "text-emerald-400", i: "fa-bolt" },
            { t: "¡Habemus Sorteo!", c: "text-white", i: "fa-check-double" }
        ];

        const frases = (tipo === 'figuras') ? frasesFiguras : frasesRutinas;
        let step = 0;
        const interval = setInterval(() => {
            if(step < frases.length) {
                if(text) text.textContent = frases[step].t;
                if(icon) icon.className = `fa-solid ${frases[step].i} text-7xl ${frases[step].c} transition-all duration-500`;
                step++;
            } else {
                clearInterval(interval);
                formElement.submit();
            }
        }, 700);
    }

    // Interceptor Sorteo Figuras
    const formFiguras = document.getElementById('sortearFigurasForm');
    if(formFiguras) {
        formFiguras.addEventListener('submit', function(e) {
            const catSelect = document.querySelector('select[name="id_categoria"]');
            const isAll = !catSelect || catSelect.value === '0';
            
            if(!isAll && typeof Swal !== 'undefined') {
                e.preventDefault();
                Swal.fire({
                    title: '¿Confirmar Sorteo de Figuras?',
                    text: "Se generará un nuevo orden para esta categoría. Si ya existía uno, será sobreescrito.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3b82f6',
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: 'Sí, Sortear',
                    cancelButtonText: 'Cancelar',
                    customClass: {
                        popup: 'rounded-[2rem]',
                        confirmButton: 'rounded-xl px-6 py-3 font-black uppercase text-xs tracking-widest',
                        cancelButton: 'rounded-xl px-6 py-3 font-black uppercase text-xs tracking-widest'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        ejecutarAnimacionMagica(formFiguras, 'figuras');
                    }
                });
            } else {
                e.preventDefault();
                ejecutarAnimacionMagica(formFiguras, 'figuras');
            }
        });
    }

    // Interceptor Sorteo Rutinas
    const formRutinas = document.getElementById('sortearRutinasForm');
    if(formRutinas) {
        formRutinas.addEventListener('submit', function(e) {
            const faseSelect = document.querySelector('select[name="id_fase"]');
            const isAll = !faseSelect || faseSelect.value === '0';
            
            if(!isAll && typeof Swal !== 'undefined') {
                e.preventDefault();
                Swal.fire({
                    title: '¿Confirmar Sorteo de Rutinas?',
                    text: "Se generará un nuevo orden de salida para esta fase. Si ya existía uno, será sobreescrito.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3b82f6',
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: 'Sí, Sortear',
                    cancelButtonText: 'Cancelar',
                    customClass: {
                        popup: 'rounded-[2rem]',
                        confirmButton: 'rounded-xl px-6 py-3 font-black uppercase text-xs tracking-widest',
                        cancelButton: 'rounded-xl px-6 py-3 font-black uppercase text-xs tracking-widest'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        ejecutarAnimacionMagica(formRutinas, 'rutinas');
                    }
                });
            } else {
                e.preventDefault();
                ejecutarAnimacionMagica(formRutinas, 'rutinas');
            }
        });
    }
});
</script>

<style>
@keyframes spin-slow { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
.animate-spin-slow { animation: spin-slow 3s linear infinite; }
</style>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
