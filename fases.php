<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');

$comp_figuras = $_SESSION['competicion_figuras_usuario'] ?? 'no';
$is_mixta = ($comp_figuras == 'mixta');
$is_figuras = ($comp_figuras == 'si');
$id_comp = $_SESSION['id_competicion_usuario'];

// 1. Cálculos de KPIs para Fases
$q_f = "SELECT COUNT(*) as total, SUM(CASE WHEN puntuada = 'si' THEN 1 ELSE 0 END) as puntuadas FROM fases WHERE id_competicion = '$id_comp'";
$stats_f = mysqli_fetch_assoc(mysqli_query($connection, $q_f));

if($is_mixta) {
    $q_p = "SELECT 
                (SELECT COUNT(DISTINCT id_nadadora) FROM (
                    SELECT id_nadadora FROM inscripciones_figuras WHERE id_fase IN (SELECT id FROM fases WHERE id_competicion = '$id_comp')
                    UNION
                    SELECT rp.id_nadadora FROM rutinas_participantes rp JOIN rutinas r ON rp.id_rutina = r.id WHERE r.id_competicion = '$id_comp'
                ) as u) as unicas,
                ((SELECT COUNT(*) FROM inscripciones_figuras WHERE id_fase IN (SELECT id FROM fases WHERE id_competicion = '$id_comp')) + 
                 (SELECT COUNT(*) FROM rutinas WHERE id_competicion = '$id_comp')) as total_ins";
} elseif($is_figuras) {
    $q_p = "SELECT COUNT(DISTINCT id_nadadora) as unicas, COUNT(*) as total_ins FROM inscripciones_figuras WHERE id_fase IN (SELECT id FROM fases WHERE id_competicion = '$id_comp')";
} else {
    $q_p = "SELECT COUNT(DISTINCT rp.id_nadadora) as unicas, COUNT(*) as total_ins FROM rutinas_participantes rp JOIN rutinas r ON rp.id_rutina = r.id WHERE r.id_competicion = '$id_comp'";
}
$res_p = mysqli_query($connection, $q_p);
$stats_p = mysqli_fetch_assoc($res_p);

$p_unicas = $stats_p['unicas'] ?? 0;
$total_fases = $stats_f['total'] ?? 0;
$f_puntuadas = $stats_f['puntuadas'] ?? 0;

// 1.3 Calcular siguiente número de orden
$q_next_order = "SELECT MAX(orden) as max_o FROM fases WHERE id_competicion = '$id_comp'";
$res_next = mysqli_query($connection, $q_next_order);
$row_next = mysqli_fetch_assoc($res_next);
$next_order = ($row_next['max_o'] ?? 0) + 1;
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-surface">
    
    <?php include('includes/topbar.php'); ?>

    <div class="p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend">
        
        <!-- Header -->
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <h1 class="text-4xl font-black text-slate-800 tracking-tighter mb-2 flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-list-check text-lg"></i></span>
                    Fases
                </h1>
                <p class="text-slate-500 font-medium">Configuración técnica de la competición activa.</p>
            </div>
            <div class="flex gap-3">
                <!-- Botón de Conmutación de Vista -->
                <button onclick="toggleView()" id="viewBtn" class="px-5 py-3 bg-white border border-slate-200 text-slate-400 font-black uppercase text-[10px] tracking-widest rounded-2xl shadow-sm hover:bg-slate-50 transition-all flex items-center gap-2 italic">
                    <i class="fas fa-table-list" id="viewIcon"></i> <span id="viewText">Vista Verificación</span>
                </button>
                <button onclick="toggleAddFasePanel()" class="px-6 py-3 bg-blue-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
                    <i class="fas fa-plus text-xs"></i> Añadir Fase
                </button>
            </div>
        </div>

        <!-- Panel Añadir Fase -->
        <div id="addFasePanel" class="hidden mb-10 relative z-30 scroll-mt-28">
            <div class="bg-white rounded-[2.5rem] p-8 shadow-xl border border-blue-100 relative">
                <h2 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3"><i class="fas fa-plus-circle text-blue-600"></i> Nueva Fase</h2>
                <form action="fases_code.php" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-6">
                    <div class="md:col-span-4 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Categoría</label>
                        <?php 
                        ob_start(); include('includes/categoria_select_option.php');
                        $cat_html = ob_get_clean();
                        $cat_html = preg_replace('/<label.*?>.*?<\/label>/i', '', $cat_html);
                        $cat_html = preg_replace('/<select[^>]*>/i', '<select name="id_categoria" id="categoria" onchange="onCategoriaChange(this)" class="v3-select-fix">', $cat_html);
                        echo $cat_html;
                        ?>
                    </div>
                    <div class="md:col-span-4 space-y-2">
                        <?php if($is_mixta): ?>
                            <!-- Selector dinámico en competición Mixta -->
                            <div class="flex items-center justify-between px-1 mb-1">
                                <label class="text-[10px] font-black uppercase text-slate-400">Tipo de Fase</label>
                                <div class="inline-flex rounded-xl border border-slate-200 bg-slate-50 p-0.5 text-[9px] font-bold">
                                    <button type="button" id="btn_tipo_figura" onclick="setTipoFase('figura')" class="px-2.5 py-1 rounded-lg bg-blue-600 text-white shadow-sm transition-all">Figura</button>
                                    <button type="button" id="btn_tipo_rutina" onclick="setTipoFase('rutina')" class="px-2.5 py-1 rounded-lg text-slate-500 hover:text-slate-800 transition-all">Rutina Téc.</button>
                                </div>
                            </div>
                            <input type="hidden" name="tipo_fase" id="tipo_fase" value="figura">
                            
                            <div id="wrapper_figura" class="space-y-1">
                                <div class="flex items-center justify-between px-1">
                                    <span class="text-[10px] font-black uppercase text-slate-400">Figura</span>
                                    <span id="figuraCount" class="text-[9px] font-bold text-blue-500">Filtrar búsqueda</span>
                                </div>
                                <input type="text" id="figuraFilter" placeholder="🔍 Escribe número o nombre (ej: 301, barracuda)..." 
                                    class="w-full px-4 py-2.5 text-xs rounded-xl bg-slate-50 border border-slate-200 text-slate-700 placeholder-slate-400 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all font-medium mb-1">
                                <?php 
                                ob_start(); include('includes/figura_select_option.php');
                                $fig_html = ob_get_clean();
                                $fig_html = preg_replace('/<label.*?>.*?<\/label>/i', '', $fig_html);
                                $fig_html = preg_replace('/<select[^>]*>/i', '<select name="id_figura" id="figura" class="v3-select-fix">', $fig_html);
                                echo $fig_html;
                                ?>
                            </div>

                            <div id="wrapper_rutina" class="space-y-1 hidden">
                                <label class="text-[10px] font-black uppercase text-slate-400 px-1">Modalidad Técnica</label>
                                <?php 
                                ob_start(); include('includes/modalidad_select_option.php');
                                $mod_html = ob_get_clean();
                                $mod_html = preg_replace('/<label.*?>.*?<\/label>/i', '', $mod_html);
                                $mod_html = preg_replace('/<select[^>]*>/i', '<select name="id_modalidad" id="modalidad" class="v3-select-fix" disabled>', $mod_html);
                                echo $mod_html;
                                ?>
                            </div>
                        <?php elseif($is_figuras): ?>
                            <div class="flex items-center justify-between px-1">
                                <label class="text-[10px] font-black uppercase text-slate-400">Figura</label>
                                <span id="figuraCount" class="text-[9px] font-bold text-blue-500">Filtrar búsqueda</span>
                            </div>
                            <input type="text" id="figuraFilter" placeholder="🔍 Escribe número o nombre (ej: 301, barracuda)..." 
                                class="w-full px-4 py-2.5 text-xs rounded-xl bg-slate-50 border border-slate-200 text-slate-700 placeholder-slate-400 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all font-medium mb-1">
                            <?php 
                            ob_start(); include('includes/figura_select_option.php');
                            $fig_html = ob_get_clean();
                            $fig_html = preg_replace('/<label.*?>.*?<\/label>/i', '', $fig_html);
                            $fig_html = preg_replace('/<select[^>]*>/i', '<select name="id_figura" id="figura" class="v3-select-fix">', $fig_html);
                            echo $fig_html;
                            ?>
                        <?php else: ?>
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1">Modalidad</label>
                            <?php 
                            ob_start(); include('includes/modalidad_select_option.php');
                            $mod_html = ob_get_clean();
                            $mod_html = preg_replace('/<label.*?>.*?<\/label>/i', '', $mod_html);
                            $mod_html = preg_replace('/<select[^>]*>/i', '<select name="id_modalidad" id="modalidad" class="v3-select-fix">', $mod_html);
                            echo $mod_html;
                            ?>
                        <?php endif; ?>
                    </div>
                    <div class="md:col-span-2 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Orden</label>
                        <input type="number" name="orden" value="<?php echo $next_order; ?>" required class="w-full px-5 py-3.5 rounded-2xl bg-slate-50 border border-slate-100 text-sm font-bold shadow-inner">
                    </div>
                    <div class="md:col-span-2 flex items-end gap-3">
                        <button type="button" onclick="toggleAddFasePanel()" class="w-1/2 py-4 bg-slate-100 text-slate-500 font-black uppercase text-[10px] tracking-widest rounded-2xl border border-slate-200 hover:bg-slate-200 transition-all">Cancelar</button>
                        <button type="submit" name="save_btn" class="w-1/2 py-4 bg-blue-600 text-white font-black uppercase text-[10px] tracking-widest rounded-2xl shadow-lg hover:bg-blue-700 transition-all">Crear</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- DASHBOARD: KPIs -->
        <div id="kpiContainer" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
            <div class="md:col-span-2 bg-white p-7 rounded-[2rem] shadow-sm border border-slate-200 border-l-[6px] border-l-blue-600 group hover:shadow-xl transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Censo de Participación</p>
                <h3 class="text-3xl font-black text-slate-800">Participantes: <?php echo $p_unicas; ?></h3>
            </div>
            <div class="bg-white p-7 rounded-[2rem] shadow-sm border border-slate-200 border-l-[6px] border-l-emerald-500 group hover:shadow-xl transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Estado Jornada</p>
                <h3 class="text-2xl font-black text-emerald-600"><?php echo $f_puntuadas; ?> <span class="text-xs text-slate-300">/ <?php echo $total_fases; ?></span></h3>
            </div>
            <div class="bg-white p-7 rounded-[2rem] shadow-sm border border-slate-200 border-l-[6px] border-l-purple-500 group hover:shadow-xl transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Carga Técnica</p>
                <h3 class="text-2xl font-black text-purple-600"><?php echo $stats_p['total_ins'] ?? 0; ?></h3>
            </div>
        </div>

        <!-- VISTA 1: TARJETAS -->
        <div id="cardsView" class="space-y-6">
            <?php
            $query = "SELECT f.*, c.nombre as cat_nombre, 
                      fig.nombre as fig_nombre, fig.numero as fig_num, fig.grado_dificultad as fig_gd,
                      m.nombre as mod_nombre,
                      (CASE 
                         WHEN f.id_figura > 0 THEN (SELECT COUNT(*) FROM inscripciones_figuras WHERE id_fase = f.id)
                         ELSE (SELECT COUNT(*) FROM rutinas WHERE id_fase = f.id)
                       END) as num_items 
                      FROM fases f 
                      JOIN categorias c ON f.id_categoria = c.id 
                      LEFT JOIN figuras fig ON f.id_figura = fig.id 
                      LEFT JOIN modalidades m ON f.id_modalidad = m.id 
                      WHERE f.id_competicion = '$id_comp' 
                      ORDER BY f.orden ASC";
            $res = mysqli_query($connection, $query);
            while ($row = mysqli_fetch_assoc($res)):
                $isEmpty = ($row['num_items'] == 0);
                $isOldSystem = ($row['obsoleto'] == 'si');
                $is_phase_figura = (!empty($row['id_figura']) && $row['id_figura'] > 0);
                
                // Definición de estilos dinámicos
                if ($isEmpty) {
                    $card_border = 'border-amber-400 bg-amber-50/30';
                    $order_bg = 'bg-amber-100 border-amber-200';
                    $order_text = 'text-amber-600';
                } elseif ($isOldSystem) {
                    $card_border = 'border-slate-200 bg-slate-50/50 grayscale-[0.5]';
                    $order_bg = 'bg-slate-100 border-slate-200';
                    $order_text = 'text-slate-400';
                } else {
                    $card_border = 'border-slate-200 bg-white';
                    $order_bg = 'bg-blue-50 border-blue-100';
                    $order_text = 'text-blue-600';
                }
            ?>
            <div class="<?php echo $card_border; ?> rounded-[2rem] p-6 shadow-sm border group hover:shadow-xl transition-all relative overflow-hidden flex flex-col md:flex-row md:items-center gap-8">
                <!-- ID Más Visible -->
                <div class="absolute top-4 right-8 text-[10px] font-black text-slate-400 italic tracking-widest uppercase opacity-40">ID: #<?php echo $row['id']; ?></div>

                <div class="flex-shrink-0 flex flex-col items-center justify-center w-16 h-16 rounded-2xl <?php echo $order_bg; ?> border shadow-inner group-hover:scale-110 transition-transform duration-500">
                    <span class="text-[9px] font-black opacity-40 uppercase leading-none mb-1">Orden</span>
                    <span class="text-xl font-black <?php echo $order_text; ?> leading-none"><?php echo $row['orden']; ?></span>
                </div>
                <div class="flex-1">
                    <div class="mb-2 flex flex-wrap items-center gap-2">
                        <span class="px-3 py-1 bg-slate-100 text-slate-500 text-[10px] font-black rounded-lg border border-slate-200 uppercase tracking-widest"><?php echo $row['cat_nombre']; ?></span>
                        
                        <?php if($is_phase_figura): ?>
                            <span class="px-2.5 py-1 bg-blue-50 text-blue-600 text-[9px] font-black rounded-lg border border-blue-100 uppercase tracking-widest"><i class="fas fa-person-swimming mr-1"></i> Figura</span>
                        <?php else: ?>
                            <span class="px-2.5 py-1 bg-purple-50 text-purple-600 text-[9px] font-black rounded-lg border border-purple-100 uppercase tracking-widest"><i class="fas fa-music mr-1"></i> Rutina Técnica</span>
                        <?php endif; ?>

                        <!-- Badges de Sistema -->
                        <?php if($isOldSystem): ?>
                            <span class="px-3 py-1 bg-slate-200 text-slate-600 text-[9px] font-black rounded-lg uppercase tracking-widest flex items-center gap-1 border border-slate-300">
                                <i class="fas fa-clock-rotate-left"></i> OBSOLETO
                            </span>
                        <?php else: ?>
                            <span class="px-3 py-1 bg-blue-50 text-blue-600 text-[9px] font-black rounded-lg uppercase tracking-widest flex items-center gap-1 border border-blue-100">
                                <i class="fas fa-certificate"></i> AQUA
                            </span>
                        <?php endif; ?>

                        <?php if($isEmpty): ?>
                            <span class="px-3 py-1 bg-amber-500 text-white text-[9px] font-black rounded-lg shadow-lg shadow-amber-500/20 uppercase tracking-widest animate-pulse">Fase Vacía</span>
                        <?php endif; ?>
                    </div>
                    <div class="flex items-center gap-3 mb-1">
                        <h3 class="text-xl font-black <?php echo $isOldSystem ? 'text-slate-500' : 'text-slate-800'; ?> tracking-tight">
                            <?php echo $is_phase_figura ? "Figura ".$row['fig_num']." - ".$row['fig_nombre'] : $row['mod_nombre']; ?>
                        </h3>
                    </div>
                    <?php if($is_phase_figura): ?>
                        <div class="flex items-center gap-4">
                            <p class="text-xs font-medium text-slate-400 uppercase tracking-tighter italic"><i class="fas fa-chart-line text-blue-500 mr-1"></i> G.D: <span class="text-blue-600 font-black"><?php echo $row['fig_gd']; ?></span></p>
                            <p class="text-xs font-medium <?php echo $isEmpty ? 'text-amber-600' : 'text-slate-400'; ?> uppercase tracking-tighter italic"><i class="fas fa-users text-blue-400 mr-1"></i> <?php echo $row['num_items']; ?> inscritas.</p>
                        </div>
                    <?php else: ?>
                        <p class="text-xs font-medium <?php echo $isEmpty ? 'text-amber-600' : 'text-slate-400'; ?> uppercase tracking-tighter italic"><i class="fas fa-swimmer text-purple-500 mr-1"></i> <?php echo $row['num_items']; ?> rutinas registradas.</p>
                    <?php endif; ?>
                    
                    <?php if($isEmpty): ?>
                        <p class="text-[10px] font-bold text-amber-700 mt-2 flex items-center gap-2 italic"><i class="fas fa-info-circle"></i> Puedes eliminar esta fase si no es necesaria antes del sorteo.</p>
                    <?php endif; ?>
                </div>
                <div class="flex items-center gap-3">
                    <form action="fases_edit.php" method="POST"><input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>"><button type="submit" name="edit_btn" class="px-5 py-2.5 bg-blue-50 text-blue-600 font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-blue-600 hover:text-white hover:shadow-lg hover:shadow-blue-500/30 transition-all flex items-center gap-2"><i class="fas fa-cog"></i> Configurar</button></form>
                    <form action="fases_code.php" method="POST" onsubmit="return confirm('¿Borrar?');"><input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>"><button type="submit" name="delete_btn" class="w-10 h-10 rounded-xl <?php echo $isEmpty ? 'bg-red-500 text-white animate-bounce-slow' : 'bg-red-50 text-red-400'; ?> hover:bg-red-600 hover:text-white hover:shadow-lg hover:shadow-red-500/30 transition-all flex items-center justify-center border border-red-100/50 shadow-sm" title="Borrar Fase Vacía"><i class="fas fa-trash-alt text-sm"></i></button></form>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <!-- VISTA 2: LISTA DE VERIFICACIÓN -->
        <div id="listView" class="hidden animate-fade-in">
            <?php
            $q_cat_list = "SELECT DISTINCT c.id, c.nombre FROM fases f JOIN categorias c ON f.id_categoria = c.id WHERE f.id_competicion = '$id_comp' ORDER BY c.orden, c.id";
            $res_cat_list = mysqli_query($connection, $q_cat_list);
            while($c_row = mysqli_fetch_assoc($res_cat_list)):
                $id_cat_row = $c_row['id'];
            ?>
            <div class="bg-white rounded-[1.5rem] border border-slate-200 overflow-hidden mb-4 shadow-sm">
                <div class="bg-slate-50 px-8 py-2 border-b border-slate-100 flex items-center gap-3">
                    <div class="w-2 h-2 rounded-full bg-blue-600"></div>
                    <h3 class="text-[10px] font-black uppercase text-slate-500 tracking-widest italic">Categoría: <?php echo $c_row['nombre']; ?></h3>
                </div>
                <table class="w-full text-left border-collapse">
                    <thead class="hidden md:table-header-group">
                        <tr class="text-[8px] font-black text-slate-400 uppercase tracking-widest italic border-b border-slate-50">
                            <th class="px-8 py-2 w-20">ORDEN</th>
                            <th class="px-4 py-2"><?php echo $is_figuras ? 'Identificación de la Figura' : 'Modalidad de Rutina'; ?></th>
                            <th class="px-4 py-2 text-center">G.D.</th>
                            <th class="px-8 py-2 text-right w-32">INSCRIPCIONES</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php
                        $q_f_list = "SELECT f.*, 
                                     fig.nombre as f_nom, fig.numero as f_num, fig.grado_dificultad as f_gd, 
                                     m.nombre as m_nom,
                                     (CASE 
                                        WHEN f.id_figura > 0 THEN (SELECT COUNT(*) FROM inscripciones_figuras WHERE id_fase = f.id)
                                        ELSE (SELECT COUNT(*) FROM rutinas WHERE id_fase = f.id)
                                      END) as num_ins
                                     FROM fases f 
                                     LEFT JOIN figuras fig ON f.id_figura = fig.id 
                                     LEFT JOIN modalidades m ON f.id_modalidad = m.id 
                                     WHERE f.id_competicion = '$id_comp' AND f.id_categoria = '$id_cat_row' 
                                     ORDER BY f.orden ASC";
                        $res_f_list = mysqli_query($connection, $q_f_list);
                        while($fl = mysqli_fetch_assoc($res_f_list)):
                            $is_fl_fig = (!empty($fl['id_figura']) && $fl['id_figura'] > 0);
                            $fl_nom = $is_fl_fig ? ($fl['f_num'].' - '.$fl['f_nom']) : ($fl['m_nom'] ?? 'Rutina');
                            $fl_gd = $is_fl_fig ? $fl['f_gd'] : '-';
                        ?>
                        <tr class="hover:bg-slate-50/50 transition-colors flex flex-col md:table-row p-4 md:p-0">
                            <!-- Mobile: Línea 1 (Orden + Nombre) | Desktop: Col Orden -->
                            <td class="px-0 md:px-8 py-0 md:py-2 font-black text-slate-300 italic mb-1 md:mb-0">
                                <span class="md:hidden text-slate-400 mr-1">#<?php echo $fl['orden']; ?></span>
                                <span class="hidden md:inline">#<?php echo $fl['orden']; ?></span>
                                <!-- Nombre integrado en móvil -->
                                <span class="md:hidden text-xs font-black text-slate-800 uppercase italic tracking-tighter ml-2 leading-none">
                                    <?php echo $fl_nom; ?>
                                </span>
                            </td>

                            <!-- Desktop Only: Nombre -->
                            <td class="hidden md:table-cell px-4 py-2">
                                <p class="text-xs font-black text-slate-800 italic uppercase tracking-tighter">
                                    <?php echo $fl_nom; ?>
                                </p>
                            </td>

                            <!-- Línea 2 en Mobile (Badges GD e INS) | Desktop: Celdas separadas -->
                            <td class="px-0 md:px-4 py-0 md:py-2 md:text-center">
                                <div class="flex items-center gap-3">
                                    <span class="text-[10px] font-black text-blue-600 bg-blue-50 md:bg-transparent px-2 py-0.5 md:px-0 rounded-md border border-blue-100 md:border-0">
                                        <span class="md:hidden opacity-50 mr-1">GD:</span><?php echo $fl_gd; ?>
                                    </span>
                                    <span class="md:hidden w-1 h-1 rounded-full bg-slate-200"></span>
                                    <span class="md:hidden px-2 py-0.5 bg-slate-100 text-slate-700 text-[10px] font-black rounded-md border border-slate-200">
                                        <span class="opacity-50 mr-1">INS:</span><?php echo $fl['num_ins']; ?>
                                    </span>
                                </div>
                            </td>

                            <!-- Desktop Only: Inscripciones -->
                            <td class="hidden md:table-cell px-8 py-2 text-right">
                                <span class="px-3 py-0.5 bg-blue-50 text-blue-700 text-[10px] font-black rounded-lg border border-blue-100">
                                    <?php echo $fl['num_ins']; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</main>

<script>
function toggleAddFasePanel() { 
    const panel = document.getElementById('addFasePanel');
    const wasHidden = panel.classList.contains('hidden');
    panel.classList.toggle('hidden');
    if (wasHidden) {
        panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        const filterInput = document.getElementById('figuraFilter');
        if (filterInput) {
            setTimeout(() => filterInput.focus(), 300);
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const filter = document.getElementById('figuraFilter');
    const select = document.getElementById('figura');
    const count = document.getElementById('figuraCount');
    
    if (select) {
        // Al recibir foco, si está muy abajo en pantalla, desplazar para asegurar apertura hacia abajo
        select.addEventListener('focus', function() {
            const rect = this.getBoundingClientRect();
            if (window.innerHeight - rect.bottom < 350) {
                this.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    }

    if (filter && select) {
        const originalOptions = Array.from(select.options).map(opt => ({
            value: opt.value,
            text: opt.text,
            selected: opt.selected
        }));
        
        filter.addEventListener('input', function() {
            const term = this.value.toLowerCase().trim();
            const matched = originalOptions.filter(o => o.text.toLowerCase().includes(term));
            
            select.innerHTML = '';
            matched.forEach(o => {
                const opt = document.createElement('option');
                opt.value = o.value;
                opt.textContent = o.text;
                select.appendChild(opt);
            });
            
            if (matched.length > 0) {
                select.selectedIndex = 0;
            }
            if (count) {
                count.textContent = matched.length + ' de ' + originalOptions.length;
            }
        });
    }
});

function toggleView() {
    const cards = document.getElementById('cardsView');
    const list = document.getElementById('listView');
    const kpi = document.getElementById('kpiContainer');
    const btnText = document.getElementById('viewText');
    const btnIcon = document.getElementById('viewIcon');

    if (cards.classList.contains('hidden')) {
        cards.classList.remove('hidden');
        list.classList.add('hidden');
        kpi.classList.remove('hidden');
        btnText.innerText = 'Vista Verificación';
        btnIcon.className = 'fas fa-table-list';
    } else {
        cards.classList.add('hidden');
        list.classList.remove('hidden');
        kpi.classList.add('hidden');
        btnText.innerText = 'Vista Gestión';
        btnIcon.className = 'fas fa-grip-vertical';
    }
}

function setTipoFase(tipo) {
    const inputTipo = document.getElementById('tipo_fase');
    const wrapFig = document.getElementById('wrapper_figura');
    const wrapMod = document.getElementById('wrapper_rutina');
    const btnFig = document.getElementById('btn_tipo_figura');
    const btnRut = document.getElementById('btn_tipo_rutina');
    const selFig = document.getElementById('figura');
    const selMod = document.getElementById('modalidad');

    if (!inputTipo || !wrapFig || !wrapMod) return;

    inputTipo.value = tipo;

    if (tipo === 'figura') {
        wrapFig.classList.remove('hidden');
        wrapMod.classList.add('hidden');
        if (selFig) selFig.disabled = false;
        if (selMod) selMod.disabled = true;

        if (btnFig && btnRut) {
            btnFig.className = 'px-2.5 py-1 rounded-lg bg-blue-600 text-white shadow-sm transition-all';
            btnRut.className = 'px-2.5 py-1 rounded-lg text-slate-500 hover:text-slate-800 transition-all';
        }
    } else {
        wrapFig.classList.add('hidden');
        wrapMod.classList.remove('hidden');
        if (selFig) selFig.disabled = true;
        if (selMod) selMod.disabled = false;

        if (btnFig && btnRut) {
            btnRut.className = 'px-2.5 py-1 rounded-lg bg-blue-600 text-white shadow-sm transition-all';
            btnFig.className = 'px-2.5 py-1 rounded-lg text-slate-500 hover:text-slate-800 transition-all';
        }
    }
}

function onCategoriaChange(sel) {
    if (!sel) return;
    const optText = (sel.options[sel.selectedIndex]?.text || '').toLowerCase();
    // Si la categoría es Junior o Absoluta, sugerir automáticamente Rutina Técnica
    if (optText.includes('junior') || optText.includes('absolut')) {
        setTipoFase('rutina');
    } else {
        setTipoFase('figura');
    }
}
</script>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
