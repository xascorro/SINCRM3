<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');

$id_comp = $_SESSION['id_competicion_usuario'] ?? 0;
$condicion_club = '';
$nombre_contexto = "General";

if(isset($_SESSION['club']) && $_SESSION['club'] > 0){
    $condicion_club = " AND n.club = ".$_SESSION['club'];
    $nombre_contexto = $_SESSION['nombre_club'] ?? "Mi Club";
}

// 1. Datos para el gráfico y mapeo de colores
$q_graph = "SELECT c.id as id_cat, c.nombre, COUNT(DISTINCT i.id_nadadora) as total 
             FROM inscripciones_figuras i 
             JOIN fases f ON i.id_fase = f.id 
             JOIN categorias c ON f.id_categoria = c.id 
             JOIN nadadoras n ON i.id_nadadora = n.id
             WHERE f.id_competicion = '$id_comp' $condicion_club
             GROUP BY f.id_categoria ORDER BY c.edad_minima ASC";
$res_graph = mysqli_query($connection, $q_graph);
$graph_data = [];
$total_global = 0;
$cat_color_map = [];
$chart_colors_raw = ['emerald', 'blue', 'purple', 'amber', 'rose'];

$i = 0;
while($rg = mysqli_fetch_assoc($res_graph)) {
    $color_base = $chart_colors_raw[$i % count($chart_colors_raw)];
    $rg['color_base'] = $color_base;
    $graph_data[] = $rg;
    $cat_color_map[$rg['id_cat']] = $color_base;
    $total_global += $rg['total'];
    $i++;
}

// 2. KPIs
$q_clubs = "SELECT COUNT(DISTINCT n.club) as total 
            FROM inscripciones_figuras i 
            JOIN nadadoras n ON i.id_nadadora = n.id 
            JOIN fases f ON i.id_fase = f.id 
            WHERE f.id_competicion = '$id_comp' $condicion_club";
$total_clubs = mysqli_fetch_assoc(mysqli_query($connection, $q_clubs))['total'] ?? 0;

$q_cc_total = "SELECT COUNT(*) as total 
               FROM inscripciones_figuras i 
               JOIN nadadoras n ON i.id_nadadora = n.id
               JOIN fases f ON i.id_fase = f.id 
               WHERE f.id_competicion = '$id_comp' AND f.elementos_coach_card > 0 $condicion_club";
$total_necesitan_cc = mysqli_fetch_assoc(mysqli_query($connection, $q_cc_total))['total'] ?? 0;

$q_cc_listas = "SELECT COUNT(DISTINCT id_rutina) as total 
                FROM hibridos_rutina 
                WHERE id_rutina IN (
                    SELECT i.id 
                    FROM inscripciones_figuras i 
                    JOIN nadadoras n ON i.id_nadadora = n.id
                    JOIN fases f ON i.id_fase = f.id 
                    WHERE f.id_competicion = '$id_comp' $condicion_club
                )";
$total_cc_ok = mysqli_fetch_assoc(mysqli_query($connection, $q_cc_listas))['total'] ?? 0;
$pct_cc = ($total_necesitan_cc > 0) ? round(($total_cc_ok / $total_necesitan_cc) * 100) : 100;

// 3. Plazos y Temporada
$query = "SELECT fecha, temporada, figuras, dias_fin_inscripcion, dias_musica, dias_coach_card FROM competiciones WHERE id = '$id_comp'";
$res_comp = mysqli_query($connection, $query);
$comp_data = mysqli_fetch_assoc($res_comp);
$fecha_evento = new DateTime($comp_data['fecha']);
$is_figuras = ($comp_data['figuras'] == 'si');

// Extraer año de referencia de la temporada (ej: "2025/2026" -> 2026)
$temporada_raw = $comp_data['temporada'] ?? '';
$temporada_año = 0;
if(preg_match('/\/(\d{4})/', $temporada_raw, $matches)){
    $temporada_año = intval($matches[1]);
} elseif(is_numeric($temporada_raw)) {
    $temporada_año = intval($temporada_raw);
} else {
    $temporada_año = intval($fecha_evento->format('Y'));
}

$dias_cierre = $comp_data['dias_fin_inscripcion'] ?? 7;
$fecha_cierre = clone $fecha_evento; $fecha_cierre->modify("-$dias_cierre days");

// Si es figuras, el plazo de coach card es igual al de inscripción
if($is_figuras) {
    $f_cc = clone $fecha_cierre;
} else {
    $d_cc = $comp_data['dias_coach_card'] ?? 7;
    $f_cc = clone $fecha_evento; $f_cc->modify("-$d_cc days");
}

$d_musica = $comp_data['dias_musica'] ?? 7;
$f_musica = clone $fecha_evento; $f_musica->modify("-$d_musica days");

$hoy = new DateTime();
$inscripcion_abierta = ($hoy < $fecha_cierre || $_SESSION['id_rol'] == 1);
$musica_abierta = ($hoy < $f_musica || $_SESSION['id_rol'] == 1);
$cc_abierta = ($hoy < $f_cc || $_SESSION['id_rol'] == 1);
?>

<main class="flex-1 flex flex-col min-w-0 bg-surface">
    <?php include('includes/topbar.php'); ?>

    <div class="p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend">
        
        <!-- Header Unificado -->
        <div class="mb-10 flex flex-col lg:flex-row lg:items-end justify-between gap-6">
            <div>
                <h1 class="text-4xl font-black text-slate-800 tracking-tighter mb-2 flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-clipboard-list text-lg"></i></span>
                    Inscripciones
                </h1>
                <p class="text-slate-500 font-medium">Gestionando participación de: <span class="text-blue-600 font-bold"><?php echo $nombre_contexto; ?></span></p>
            </div>

            <!-- Plazos Visuales -->
            <div class="flex flex-wrap gap-4 items-center">
                <div class="flex flex-col items-center px-4 py-2 bg-white rounded-2xl border border-slate-100 shadow-sm">
                    <span class="text-[9px] font-black uppercase text-slate-400 leading-tight">Inscripción</span>
                    <span class="text-xs font-black <?php echo !$inscripcion_abierta ? 'text-red-500' : 'text-slate-700'; ?>"><?php echo $fecha_cierre->format('d-m-Y'); ?></span>
                </div>
                <?php if(!$is_figuras): ?>
                <div class="flex flex-col items-center px-4 py-2 bg-white rounded-2xl border border-slate-100 shadow-sm">
                    <span class="text-[9px] font-black uppercase text-slate-400 leading-tight">Música</span>
                    <span class="text-xs font-black <?php echo !$musica_abierta ? 'text-red-500' : 'text-slate-700'; ?>"><?php echo $f_musica->format('d-m-Y'); ?></span>
                </div>
                <?php endif; ?>
                <div class="flex flex-col items-center px-4 py-2 bg-white rounded-2xl border border-slate-100 shadow-sm">
                    <span class="text-[9px] font-black uppercase text-slate-400 leading-tight">Coach Card</span>
                    <span class="text-xs font-black <?php echo !$cc_abierta ? 'text-red-500' : 'text-slate-700'; ?>"><?php echo $f_cc->format('d-m-Y'); ?></span>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <div class="px-4 py-2 <?php echo $inscripcion_abierta ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-red-50 text-red-600 border-red-100'; ?> text-[10px] font-black uppercase tracking-widest rounded-full border flex items-center gap-2 shadow-sm">
                    <span class="w-2 h-2 rounded-full <?php echo $inscripcion_abierta ? 'bg-emerald-500 animate-pulse' : 'bg-red-500'; ?>"></span>
                    <?php echo $inscripcion_abierta ? 'Plazo Abierto' : 'Cerrado'; ?>
                </div>
                <a href="informes/informe_figuras.php?id_competicion=<?php echo $id_comp; ?>&titulo=Inscripciones" target="_blank" class="px-5 py-3 bg-emerald-600 text-white font-bold text-xs uppercase tracking-widest rounded-2xl hover:bg-emerald-700 transition-all flex items-center gap-2 shadow-lg"><i class="fas fa-file-pdf text-xs"></i> Exportar PDF</a>
            </div>
        </div>

        <!-- DASHBOARD SUPERIOR -->
        <?php if($total_global > 0): ?>
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-12">
            <div class="lg:col-span-8 bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-200 flex flex-col md:flex-row items-center gap-10">
                <div class="relative w-40 h-40 flex-shrink-0">
                    <svg viewBox="0 0 100 100" class="w-full h-full transform -rotate-90">
                        <?php 
                        $current_offset = 0;
                        foreach($graph_data as $idx => $data):
                            $percentage = ($data['total'] / $total_global) * 100;
                            $color_hex = ($data['color_base'] == 'emerald') ? '#10b981' : (($data['color_base'] == 'blue') ? '#3b82f6' : (($data['color_base'] == 'purple') ? '#a855f7' : (($data['color_base'] == 'amber') ? '#f59e0b' : '#f43f5e')));
                        ?>
                            <circle cx="50" cy="50" r="40" fill="transparent" stroke="<?php echo $color_hex; ?>" stroke-width="18" stroke-dasharray="<?php echo (251.2 * $percentage / 100); ?> 251.2" stroke-dashoffset="<?php echo - (251.2 * $current_offset / 100); ?>" class="transition-all duration-1000" />
                        <?php $current_offset += $percentage; endforeach; ?>
                        <circle cx="50" cy="50" r="31" fill="white" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-3xl font-black text-slate-800 leading-none"><?php echo $total_global; ?></span>
                        <span class="text-[8px] font-black uppercase text-slate-400 tracking-widest mt-1">Total Atletas</span>
                    </div>
                </div>
                <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3 w-full">
                    <?php foreach($graph_data as $idx => $data): ?>
                        <a href="#cat-<?php echo $data['id_cat']; ?>" class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100 hover:border-<?php echo $data['color_base']; ?>-500 hover:bg-white transition-all group">
                            <div class="flex items-center gap-3">
                                <div class="w-2.5 h-2.5 rounded-full bg-<?php echo $data['color_base']; ?>-500 shadow-sm shadow-<?php echo $data['color_base']; ?>-500/20"></div>
                                <span class="text-[11px] font-black text-slate-600"><?php echo $data['nombre']; ?></span>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="text-[11px] font-black text-slate-800"><?php echo round(($data['total'] / $total_global) * 100); ?>%</span>
                                <span class="text-[8px] font-bold text-slate-400 uppercase group-hover:text-blue-600">Ver lista ↓</span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="lg:col-span-4 flex flex-col gap-6">
                <div class="flex-1 bg-white rounded-[2rem] p-6 shadow-sm border border-slate-200 border-l-[6px] border-l-blue-500 flex items-center gap-6">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 shadow-inner"><i class="fas fa-shield-halved"></i></div>
                    <div><h3 class="text-2xl font-black text-slate-800 leading-none mb-1"><?php echo $total_clubs; ?></h3><p class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Clubes Inscritos</p></div>
                </div>
                <div class="flex-1 bg-white rounded-[2rem] p-6 shadow-sm border border-slate-200 border-l-[6px] border-l-amber-500 flex items-center gap-6">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600 shadow-inner"><span class="text-xs font-black"><?php echo $pct_cc; ?>%</span></div>
                    <div><h3 class="text-2xl font-black text-slate-800 leading-none mb-1"><?php echo $total_cc_ok; ?> <span class="text-xs text-slate-300">/ <?php echo $total_necesitan_cc; ?></span></h3><p class="text-[10px] font-black uppercase text-slate-400 tracking-widest">CC Completadas</p></div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- LISTADOS -->
        <div class="space-y-16">
            <?php
            $q_cat = "SELECT DISTINCT f.id_categoria, c.nombre, c.edad_minima, c.edad_maxima FROM fases f, categorias c WHERE f.id_categoria = c.id AND f.id_competicion = '$id_comp' ORDER BY c.edad_minima ASC";
            $res_cats = mysqli_query($connection, $q_cat);
            while ($row_cat = mysqli_fetch_assoc($res_cats)):
                $id_cat = $row_cat['id_categoria'];
                $color_cat = $cat_color_map[$id_cat] ?? 'slate';
                $q_has_cc = "SELECT SUM(elementos_coach_card) as total_cc FROM fases WHERE id_categoria = '$id_cat' AND id_competicion = '$id_comp'";
                $has_cc = (mysqli_fetch_assoc(mysqli_query($connection, $q_has_cc))['total_cc'] > 0);
            ?>
            <div id="cat-<?php echo $id_cat; ?>" class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden border-t-[8px] border-t-<?php echo $color_cat; ?>-500 scroll-mt-24">
                
                <div class="px-8 py-6 bg-slate-50/50 border-b border-slate-100 flex justify-between items-center">
                    <div class="flex items-center gap-4">
                        <h2 class="text-xl font-black text-slate-800 tracking-tight italic"><?php echo $row_cat['nombre']; ?> <span class="ml-2 text-xs font-bold text-slate-400 not-italic">(<?php echo $row_cat['edad_minima']; ?>-<?php echo $row_cat['edad_maxima']; ?> años)</span></h2>
                        <?php if($inscripcion_abierta): ?>
                        <button onclick="toggleQuickInscribe(<?php echo $id_cat; ?>)" class="px-4 py-1.5 bg-blue-600 text-white text-[9px] font-black rounded-xl uppercase tracking-widest shadow-lg shadow-blue-500/20 hover:scale-105 transition-all flex items-center gap-2">
                            <i class="fas fa-plus text-[8px]"></i> Inscribir aquí
                        </button>
                        <?php endif; ?>
                    </div>
                    <?php
                    // Corregimos la cuenta para que siempre sea relativa al club si existe el filtro
                    $q_count = "SELECT COUNT(DISTINCT i.id_nadadora) as total 
                                FROM inscripciones_figuras i 
                                JOIN fases f ON i.id_fase = f.id 
                                JOIN nadadoras n ON i.id_nadadora = n.id 
                                WHERE f.id_categoria = '$id_cat' AND f.id_competicion = '$id_comp' $condicion_club";
                    $res_count_cat = mysqli_query($connection, $q_count);
                    $total_cat = ($res_count_cat) ? mysqli_fetch_assoc($res_count_cat)['total'] : 0;
                    ?>
                    <span class="px-4 py-1 bg-white text-slate-400 text-[10px] font-black rounded-lg border border-slate-200 shadow-sm uppercase"><?php echo $total_cat; ?> Participantes</span>
                </div>

                <!-- FORMULARIO RÁPIDO (INLINE POR CATEGORÍA) -->
                <?php if($inscripcion_abierta): ?>
                <div id="quick-inscribe-<?php echo $id_cat; ?>" class="hidden px-8 py-6 bg-blue-50/30 border-b border-blue-100 animate-fade-in">
                    <form action="inscripciones_figuras_code.php" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                        <input type="hidden" name="id_competicion" value="<?php echo $id_comp; ?>">
                        
                        <div class="md:col-span-4">
                            <label class="text-[9px] font-black uppercase text-slate-400 px-1 mb-1 block">Fase / Figura</label>
                            <select name="id_fase" class="v3-select-fix w-full text-xs font-bold px-4 py-2.5 rounded-xl border border-blue-100 shadow-sm bg-white">
                                <?php
                                // Mejoramos la query para capturar el nombre de la figura si existe
                                $q_fases_cat = "SELECT fs.id, fs.id_figura, m.nombre as m_nom, fig.nombre as fig_nom, fig.numero as fig_num
                                                FROM fases fs 
                                                LEFT JOIN modalidades m ON fs.id_modalidad = m.id 
                                                LEFT JOIN figuras fig ON fs.id_figura = fig.id
                                                WHERE fs.id_competicion = '$id_comp' AND fs.id_categoria = '$id_cat' 
                                                ORDER BY fs.orden ASC";
                                $res_fases_cat = mysqli_query($connection, $q_fases_cat);
                                while($f_row = mysqli_fetch_assoc($res_fases_cat)) {
                                    $display_name = !empty($f_row['fig_nom']) ? $f_row['fig_num'] . " - " . $f_row['fig_nom'] : $f_row['m_nom'];
                                    echo "<option value='".$f_row['id']."'>".$display_name."</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="md:col-span-6">
                            <label class="text-[9px] font-black uppercase text-slate-400 px-1 mb-1 block">Nadadora apta para <?php echo $row_cat['nombre']; ?></label>
                            <?php 
                            // Contexto de edad para el include
                            $id_categoria_limite = $id_cat; 
                            $temporada_año = $temporada_año; 
                            ob_start(); include('includes/nadadoras_select_option.php');
                            echo str_replace('<select', '<select name="id_nadadora" class="v3-select-fix w-full text-xs font-bold px-4 py-2.5 rounded-xl border border-blue-100 shadow-sm bg-white"', preg_replace('/<label.*?>.*?<\/label>/i', '', ob_get_clean()));
                            ?>
                        </div>

                        <div class="md:col-span-2">
                            <button type="submit" name="save_btn" class="w-full py-3 bg-blue-600 text-white font-black uppercase text-[10px] tracking-widest rounded-xl shadow-lg shadow-blue-500/20">Registrar</button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

                <div class="overflow-x-auto no-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/20 text-slate-400 text-[9px] font-black uppercase tracking-widest border-b border-slate-50">
                                <th class="px-8 py-4 w-10 text-center">Orden</th>
                                <th class="px-4 py-4">Nadadora</th>
                                <th class="px-4 py-4 text-center">Año</th>
                                <th class="px-4 py-4">Club</th>
                                <?php if($has_cc): ?><th class="px-4 py-4 text-center w-20">CC</th><?php endif; ?>
                                <th class="px-4 py-4 text-center w-32">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php
                            $q_fase_ref = "SELECT id FROM fases WHERE id_categoria = '$id_cat' AND id_competicion = '$id_comp' LIMIT 1";
                            $id_fase_res = mysqli_query($connection, $q_fase_ref);
                            $id_fase_ref = ($id_fase_res && mysqli_num_rows($id_fase_res) > 0) ? mysqli_fetch_row($id_fase_res)[0] : 0;
                            
                            $q_ins = "SELECT i.*, n.nombre as n_nom, n.apellidos as n_ape, n.año_nacimiento as n_año, cl.nombre_corto as c_nom, f.elementos_coach_card 
                                      FROM inscripciones_figuras i 
                                      JOIN nadadoras n ON i.id_nadadora = n.id 
                                      JOIN clubes cl ON n.club = cl.id 
                                      JOIN fases f ON i.id_fase = f.id 
                                      WHERE i.id_fase = '$id_fase_ref' $condicion_club 
                                      ORDER BY i.orden ASC, cl.nombre_corto ASC, n.apellidos ASC";
                            $res_ins = mysqli_query($connection, $q_ins);
                            if($res_ins):
                            while ($row = mysqli_fetch_assoc($res_ins)):
                            ?>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-4 py-4">
                                    <form action="inscripciones_figuras_code.php" method="POST" class="flex items-center gap-1">
                                        <input type="hidden" name="update_id" value="<?php echo $row['id']; ?>">
                                        
                                        <?php if($_SESSION['id_rol'] == 1): ?>
                                            <!-- Admin: Input libre -->
                                            <input type="number" name="update_orden" value="<?php echo $row['orden']; ?>" 
                                                class="w-14 px-2 py-1.5 rounded-lg bg-slate-100 border-none text-[11px] font-black text-center focus:ring-2 focus:ring-blue-500 transition-all">
                                        <?php else: ?>
                                            <!-- Club: Selector restringido -->
                                            <select name="update_orden" class="w-24 px-1 py-1.5 rounded-lg bg-slate-100 border-none text-[10px] font-black text-center focus:ring-2 focus:ring-blue-500 transition-all"
                                                <?php echo (!$inscripcion_abierta) ? 'disabled' : ''; ?>>
                                                <option value="0" <?php echo ($row['orden'] == 0) ? 'selected' : ''; ?>>Sorteo</option>
                                                <option value="-1" <?php echo ($row['orden'] == -1) ? 'selected' : ''; ?>>Pre-S</option>
                                                <option value="-10" <?php echo ($row['orden'] == -10) ? 'selected' : ''; ?>>Exhib.</option>
                                            </select>
                                        <?php endif; ?>

                                        <?php if($inscripcion_abierta || $_SESSION['id_rol'] == 1): ?>
                                            <button type="submit" name="update_orden_btn" class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-600 hover:text-white transition-all"><i class="fas fa-save text-[10px]"></i></button>
                                        <?php endif; ?>
                                    </form>
                                </td>
                                <td class="px-4 py-4 font-black text-slate-700 text-sm"><?php echo $row['n_ape']; ?>, <?php echo $row['n_nom']; ?></td>
                                <td class="px-4 py-4 text-center text-xs font-bold text-slate-500"><?php echo $row['n_año']; ?></td>
                                <td class="px-4 py-4 text-[10px] font-black text-blue-600 uppercase tracking-tighter"><?php echo $row['c_nom']; ?></td>
                                <?php if($has_cc): ?>
                                <td class="px-4 py-4 text-center">
                                    <?php if($row['elementos_coach_card'] > 0): ?>
                                        <a href="./coach_card_composer.php?id_rutina=<?php echo $row['id']; ?>&id_fase=<?php echo $row['id_fase']; ?>" class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center hover:bg-amber-500 hover:text-white transition-all shadow-sm mx-auto"><i class="fas fa-puzzle-piece text-[10px]"></i></a>
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                                <td class="px-4 py-4 text-center">
                                    <div class="flex items-center justify-center">
                                        <?php if($inscripcion_abierta): ?>
                                            <button type="button" onclick="launchConfirmDelete(<?php echo $row['id'];?>, '<?php echo addslashes($row['n_nom'].' '.$row['n_ape']);?>')" class="w-9 h-9 rounded-xl bg-slate-50 text-slate-300 hover:bg-red-50 hover:text-red-500 transition-all flex items-center justify-center border border-transparent hover:border-red-100 shadow-sm"><i class="fas fa-trash-can text-xs"></i></button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</main>

<form id="borradoRealForm" action="inscripciones_figuras_code.php" method="POST">
    <input type="hidden" name="delete_id" id="borradoRealID">
    <input type="hidden" name="delete_btn" value="1">
</form>

<script>
$(document).ready(function() {
    // Alertas de sesión mediante SweetAlert2
    <?php if(isset($_SESSION['correcto'])): ?>
        Swal.fire({
            icon: 'success',
            title: '<span class="swal2-title-v3">¡Éxito!</span>',
            html: '<?php echo $_SESSION['correcto']; unset($_SESSION['correcto']); ?>',
            confirmButtonColor: '#0f172a',
            customClass: { popup: 'swal2-popup-v3' }
        });
    <?php endif; ?>

    <?php if(isset($_SESSION['estado'])): ?>
        Swal.fire({
            icon: 'error',
            title: '<span class="swal2-title-v3 text-red-500">Aviso</span>',
            html: '<?php echo $_SESSION['estado']; unset($_SESSION['estado']); ?>',
            confirmButtonColor: '#0f172a',
            customClass: { popup: 'swal2-popup-v3' }
        });
    <?php endif; ?>
});

function toggleQuickInscribe(idCat) {
    const el = document.getElementById('quick-inscribe-' + idCat);
    el.classList.toggle('hidden');
}

function launchConfirmDelete(id, name) {
    Swal.fire({
        title: '¿Confirmar eliminación?',
        html: `Estás a punto de borrar la inscripción de <b>${name}</b>.<br><small class='text-slate-400'>Se eliminarán sus 4 figuras de esta competición.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Sí, borrar todo',
        cancelButtonText: 'Mantener'
    }).then((result) => { if (result.isConfirmed) { document.getElementById('borradoRealID').value = id; document.getElementById('borradoRealForm').submit(); } });
}

document.querySelectorAll('a[href^="#"]').forEach(anchor => { anchor.addEventListener('click', function (e) { e.preventDefault(); document.querySelector(this.getAttribute('href')).scrollIntoView({ behavior: 'smooth' }); }); });
</script>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>