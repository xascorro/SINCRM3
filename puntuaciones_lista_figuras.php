<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');

// Obtener ID de la fase
$id_fase = (int)($_POST['id_fase'] ?? $_GET['id_fase'] ?? 0);

if ($id_fase <= 0) {
    echo "<div class='p-20 text-center font-black uppercase italic text-slate-400'>Error: No se ha especificado una fase válida.</div>";
    include('includes/footer.php');
    exit;
}

// 1. Datos de Cabecera
$query = "SELECT c.nombre as categoria, m.nombre as modalidad, fig.nombre as figura, fig.numero, fig.grado_dificultad, f.puntuada 
          FROM fases f
          LEFT JOIN categorias c ON f.id_categoria = c.id 
          LEFT JOIN modalidades m ON f.id_modalidad = m.id 
          LEFT JOIN figuras fig ON f.id_figura = fig.id 
          WHERE f.id = $id_fase";
$res_header = mysqli_query($connection, $query);
$nombres = mysqli_fetch_assoc($res_header);

$nombre_modalidad = $nombres['modalidad'] ?? '';
$nombre_categoria = $nombres['categoria'] ?? '';
$numero_figura = $nombres['numero'] ?? '';
$nombre_figura = $nombres['figura'] ?? '';
$grado_dificultad = $nombres['grado_dificultad'] ?? '0.0';
$is_puntuada = (($nombres['puntuada'] ?? '') === 'si');

// Estadísticas de progreso en esta figura
$q_stats = "SELECT 
              COUNT(*) as total, 
              SUM(CASE WHEN (nota_final IS NOT NULL AND nota_final != '') OR baja = 'si' THEN 1 ELSE 0 END) as puntuadas 
            FROM inscripciones_figuras 
            WHERE id_fase = $id_fase";
$res_stats = mysqli_query($connection, $q_stats);
$row_stats = $res_stats ? mysqli_fetch_assoc($res_stats) : null;
$total_nadadoras = (int)($row_stats['total'] ?? 0);
$puntuadas_nadadoras = (int)($row_stats['puntuadas'] ?? 0);
$pendientes_nadadoras = max(0, $total_nadadoras - $puntuadas_nadadoras);

// 2. Obtener Jueces del Panel
$query_p = "SELECT id, id_panel, numero_juez, id_juez FROM panel_jueces WHERE id_fase = $id_fase ORDER BY numero_juez ASC";
$res_jueces = mysqli_query($connection, $query_p);
$jueces_array = [];
while ($j = mysqli_fetch_assoc($res_jueces)) {
    $jueces_array[] = $j;
}
$num_jueces = count($jueces_array);

// 3. Obtener Penalizaciones Activas de esta Fase (agrupadas por inscripción)
$penalizaciones_fase = [];
$q_pens = "SELECT pr.id as id_pr, pr.id_inscripcion_figuras, p.codigo, p.resumen, p.puntos, pt.nombre as tipo 
           FROM penalizaciones_rutinas pr 
           JOIN penalizaciones p ON pr.id_penalizacion = p.id 
           LEFT JOIN paneles_tipo pt ON p.id_paneles_tipo = pt.id 
           WHERE pr.id_inscripcion_figuras IN (SELECT id FROM inscripciones_figuras WHERE id_fase = $id_fase)
           ORDER BY pr.id ASC";
$res_pens = mysqli_query($connection, $q_pens);
if ($res_pens) {
    while ($pen = mysqli_fetch_assoc($res_pens)) {
        $penalizaciones_fase[$pen['id_inscripcion_figuras']][] = $pen;
    }
}

// 4. Catálogo de Penalizaciones Oficiales (priorizando Figuras - Panel 6 y General - Panel 0)
$q_catalogo_pens = "SELECT p.id, p.codigo, p.puntos, p.resumen, p.aplicable_a, p.id_paneles_tipo, 
                           COALESCE(pt.nombre, 'General') as panel_nombre 
                    FROM penalizaciones p 
                    LEFT JOIN paneles_tipo pt ON p.id_paneles_tipo = pt.id 
                    ORDER BY 
                      CASE 
                          WHEN p.id_paneles_tipo = 6 THEN 1 
                          WHEN p.id_paneles_tipo = 0 THEN 2 
                          ELSE 3 
                      END ASC, 
                      ABS(p.puntos) DESC, 
                      p.codigo ASC";
$res_cat = mysqli_query($connection, $q_catalogo_pens);
$grupos_catalogo = [
    6 => ['label' => '🏊 Penalizaciones de Figuras (Panel 6)', 'items' => []],
    0 => ['label' => '🏛️ Penalizaciones Generales / Árbitro (Panel 0)', 'items' => []],
    'otros' => ['label' => '⚙️ Otras Penalizaciones Oficiales', 'items' => []]
];
if ($res_cat) {
    while ($cp = mysqli_fetch_assoc($res_cat)) {
        $pt = intval($cp['id_paneles_tipo'] ?? 0);
        if ($pt === 6) {
            $grupos_catalogo[6]['items'][] = $cp;
        } elseif ($pt === 0) {
            $grupos_catalogo[0]['items'][] = $cp;
        } else {
            $grupos_catalogo['otros']['items'][] = $cp;
        }
    }
}
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-surface font-lexend">

    <?php include('includes/topbar.php'); ?>

    <div class="p-6 md:p-10 max-w-full mx-auto w-full">
        
        <!-- Header de Página -->
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <div class="flex flex-wrap items-center gap-2.5 mb-2">
                    <span class="px-3 py-1 bg-blue-50 text-blue-600 text-[10px] font-black rounded-lg border border-blue-100 uppercase tracking-widest italic shadow-2xs">
                        <?php echo trim(($nombre_modalidad ? $nombre_modalidad . " · " : "") . $nombre_categoria); ?>
                    </span>

                    <!-- Badge Dinámico de Estado de Fase -->
                    <span id="badge_fase_status" class="px-3 py-1 <?php echo $is_puntuada ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200'; ?> text-[10px] font-black rounded-lg border uppercase tracking-widest flex items-center gap-1.5 shadow-2xs transition-colors">
                        <i id="icon_fase_status" class="fa-solid <?php echo $is_puntuada ? 'fa-circle-check text-emerald-500' : 'fa-clock text-amber-500'; ?>"></i> 
                        <span id="text_fase_status"><?php echo $is_puntuada ? 'Fase Terminada' : 'Fase En Curso'; ?></span>
                    </span>

                    <!-- Badge Dinámico de Progreso -->
                    <span id="badge_fase_progreso" class="px-3 py-1 bg-slate-100 text-slate-600 text-[10px] font-black rounded-lg border border-slate-200 uppercase tracking-widest tabular-nums flex items-center gap-1.5 shadow-2xs">
                        <i class="fa-solid fa-users text-slate-400"></i>
                        <span id="text_fase_progreso"><?php echo "$puntuadas_nadadoras / $total_nadadoras"; ?> puntuadas</span>
                    </span>
                </div>
                <h1 class="text-3xl font-black text-slate-800 tracking-tighter italic uppercase flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200">
                        <i class="fas fa-flag-checkered text-lg"></i>
                    </span>
                    Gestión de Puntuaciones
                </h1>
                <p class="text-slate-500 font-medium mt-1">
                    Figura <?php echo $numero_figura; ?> - <span class="font-black text-slate-700 italic"><?php echo $nombre_figura; ?></span> 
                    · GD: <span class="text-blue-600 font-black italic"><?php echo $grado_dificultad; ?></span>
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <!-- Botón Dinámico de Cerrar / Reabrir Fase -->
                <button type="button" 
                        id="btn_cerrar_fase" 
                        data-id-fase="<?php echo $id_fase; ?>" 
                        data-puntuada="<?php echo $is_puntuada ? 'si' : 'no'; ?>" 
                        data-total="<?php echo $total_nadadoras; ?>"
                        data-puntuadas="<?php echo $puntuadas_nadadoras; ?>"
                        data-pendientes="<?php echo $pendientes_nadadoras; ?>"
                        class="px-6 py-3 <?php echo $is_puntuada ? 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-emerald-500/20' : 'bg-blue-600 hover:bg-blue-500 text-white shadow-blue-500/20'; ?> font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg active:scale-95 transition-all flex items-center gap-2 cursor-pointer tap-press">
                    <i id="btn_cerrar_icon" class="fas <?php echo $is_puntuada ? 'fa-check-circle' : 'fa-flag-checkered'; ?>"></i> 
                    <span id="btn_cerrar_text"><?php echo $is_puntuada ? 'Fase Terminada' : 'Cerrar Fase'; ?></span>
                </button>

                <a href="./informes/informe_puntuaciones.php?titulo=Clasificaci%C3%B3n%20detallada&id_fase=<?php echo $id_fase; ?>" target="_blank" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 font-black uppercase text-xs tracking-widest rounded-2xl shadow-sm hover:bg-slate-50 transition-all flex items-center gap-2 italic tap-press active:scale-95">
                    <i class="fas fa-file-pdf text-red-500"></i> Descargar
                </a>
            </div>
        </div>

        <?php include('includes/alertas_v4.php'); ?>

        <style>
            /* Quitar flechas de los inputs de número */
            .no-spinners::-webkit-outer-spin-button,
            .no-spinners::-webkit-inner-spin-button {
                -webkit-appearance: none;
                margin: 0;
            }
            .no-spinners {
                -moz-appearance: textfield;
            }
        </style>

        <!-- Tabla de Puntuaciones -->
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden mb-10">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="px-4 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest text-center" title="Dorsal Oficial de Sorteo / Turno de Salida">Dorsal / Ord</th>
                            <th class="px-6 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest">Nadadora / Club</th>
                            <?php foreach ($jueces_array as $j): ?>
                                <th class="px-1 py-5 text-[10px] font-black uppercase text-blue-600 tracking-widest text-center">Juez <?php echo $j['numero_juez']; ?></th>
                            <?php endforeach; ?>
                            <th class="px-2 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest text-center">Sum</th>
                            <th class="px-2 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest text-center">Total</th>
                            <th class="px-2 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest text-center">Media</th>
                            <th class="px-2 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest text-center">Final</th>
                            <th class="px-6 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php
                        // Obtener fase base de la categoría (Figura 1) para conocer el dorsal oficial de sorteo
                        $q_fase_base = "SELECT id FROM fases 
                                        WHERE id_competicion = (SELECT id_competicion FROM fases WHERE id = $id_fase) 
                                          AND id_categoria = (SELECT id_categoria FROM fases WHERE id = $id_fase) 
                                          AND id_figura > 0 
                                        ORDER BY orden ASC LIMIT 1";
                        $res_fase_base = mysqli_query($connection, $q_fase_base);
                        $id_fase_base = $res_fase_base ? (int)mysqli_fetch_assoc($res_fase_base)['id'] : $id_fase;

                        // Consulta de Inscripciones con dorsal_base (dorsal oficial del sorteo general)
                        $q_insc = "SELECT i.*, n.nombre, n.apellidos, cl.nombre_corto,
                                          COALESCE(
                                              (SELECT ifb.orden FROM inscripciones_figuras ifb WHERE ifb.id_fase = $id_fase_base AND ifb.id_nadadora = i.id_nadadora LIMIT 1),
                                              i.orden
                                          ) as dorsal_base
                                   FROM inscripciones_figuras i 
                                   JOIN nadadoras n ON i.id_nadadora = n.id 
                                   JOIN clubes cl ON n.club = cl.id 
                                   WHERE i.id_fase = $id_fase 
                                   ORDER BY i.orden ASC, n.apellidos ASC";
                        $res_insc = mysqli_query($connection, $q_insc);

                        if (mysqli_num_rows($res_insc) > 0) {
                            while ($row = mysqli_fetch_assoc($res_insc)) {
                                $form_id = "form_row_" . $row['id'];
                                $is_baja = ($row['baja'] == 'si');
                                $row_class = $is_baja ? 'bg-red-50/30 opacity-60' : 'hover:bg-slate-50/50 transition-colors';
                                $dorsal_val = (int)($row['dorsal_base'] ?? $row['orden']);
                        ?>
                            <tr class="<?php echo $row_class; ?>">
                                <td class="px-4 py-4 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <span class="text-sm font-black text-slate-800 tabular-nums leading-none">#<?php echo $dorsal_val; ?></span>
                                        <?php if ($dorsal_val != (int)$row['orden']): ?>
                                            <span class="text-[9px] font-black text-blue-600 bg-blue-50 border border-blue-100 px-1.5 py-0.5 rounded uppercase tracking-wider mt-1" title="Turno de salida en esta figura">Salida <?php echo $row['orden']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php
                                    $pens_nadadora = $penalizaciones_fase[$row['id']] ?? [];
                                    $tot_pen_pts = 0;
                                    foreach ($pens_nadadora as $pn) {
                                        $tot_pen_pts += floatval($pn['puntos']);
                                    }
                                    $swimmer_esc_name = addslashes($row['apellidos'] . ', ' . $row['nombre']);
                                    $club_esc_name = addslashes($row['nombre_corto']);
                                    ?>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-black text-slate-700 uppercase leading-none mb-1">
                                            <?php echo $row['apellidos'] . ", " . $row['nombre']; ?>
                                            <?php if($is_baja) echo ' <span class="text-[9px] text-red-500">[BAJA]</span>'; ?>
                                        </span>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest"><?php echo $row['nombre_corto']; ?></span>
                                            
                                            <div id="pen_badge_container_<?php echo $row['id']; ?>" class="inline-flex items-center">
                                                <?php if (!empty($pens_nadadora)): ?>
                                                    <button type="button" 
                                                            onclick="abrirModalPenalizaciones(<?php echo $row['id']; ?>, '<?php echo $swimmer_esc_name; ?>', '<?php echo $club_esc_name; ?>')" 
                                                            class="inline-flex items-center gap-1.5 px-2 py-0.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 hover:border-rose-300 rounded-md text-[10px] font-black transition-all active:scale-95 shadow-2xs cursor-pointer" 
                                                            title="Ver o editar penalizaciones">
                                                        <i class="fas fa-gavel text-rose-600 text-[10px]"></i>
                                                        <span><?php echo count($pens_nadadora); ?> Pen (<?php echo ($tot_pen_pts > 0 ? "-$tot_pen_pts" : $tot_pen_pts); ?> pts)</span>
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" 
                                                            onclick="abrirModalPenalizaciones(<?php echo $row['id']; ?>, '<?php echo $swimmer_esc_name; ?>', '<?php echo $club_esc_name; ?>')" 
                                                            class="inline-flex items-center gap-1 px-1.5 py-0.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 border border-transparent hover:border-rose-200 rounded-md text-[10px] font-bold transition-all active:scale-95 cursor-pointer" 
                                                            title="Añadir penalización">
                                                        <i class="fas fa-gavel text-[9px]"></i> + Pen
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <?php
                                $sumatorio = 0;
                                $judge_hiddens = '';
                                foreach ($jueces_array as $juez) {
                                    // Buscar nota existente
                                    $q_n = "SELECT nota FROM puntuaciones_jueces WHERE id_panel_juez = " . $juez['id'] . " AND id_inscripcion_figuras = " . $row['id'];
                                    $res_n = mysqli_query($connection, $q_n);
                                    $nota = 0;
                                    if ($res_n && mysqli_num_rows($res_n) > 0) {
                                        $nota = mysqli_fetch_assoc($res_n)['nota'];
                                    }
                                    $sumatorio += $nota;
                                    $nj = (int)$juez['numero_juez'];

                                    $is_auditor = ($juez['id_juez'] == '108'); // ID Auditor según tu código original
                                    $v3_look_classes = $is_auditor ? 'border-amber-300 bg-amber-50 text-amber-700 shadow-inner' : 'border-slate-100 bg-slate-50 text-slate-700';

                                    echo '<td class="px-1 py-4 text-center">
                                            <input form="' . htmlspecialchars($form_id, ENT_QUOTES, 'UTF-8') . '" 
                                                   class="w-20 px-2 py-3 rounded-xl border-2 font-black text-center text-lg transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 outline-none no-spinners ' . $v3_look_classes . '" 
                                                   name="nota[' . $nj . '][nota]" step="0.1" type="number" value="' . $nota . '">
                                          </td>';

                                    $judge_hiddens .= '<input type="hidden" name="nota[' . $nj . '][id_juez]" value="' . htmlspecialchars($juez['id_juez'], ENT_QUOTES, 'UTF-8') . '">';
                                    $judge_hiddens .= '<input type="hidden" name="nota[' . $nj . '][id_panel_jueces]" value="' . (int)$juez['id'] . '">';
                                }
                                ?>

                                <td class="px-2 py-4 text-center font-bold text-slate-400 js-sum-s bg-slate-50/30 italic text-xs"><?php echo $sumatorio; ?></td>
                                <td class="px-2 py-4 text-center font-black text-slate-700 js-nota-total italic text-sm"><?php echo $row['nota_total']; ?></td>
                                <td class="px-2 py-4 text-center font-black text-slate-400 js-nota-media italic text-xs">
                                    <?php echo htmlspecialchars(puntuaciones_fmt_hasta4($row['nota_media']), ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td class="px-2 py-4 text-center font-black text-blue-600 bg-blue-50/30 js-nota-final italic text-lg">
                                    <?php echo htmlspecialchars(puntuaciones_fmt_hasta4($row['nota_final']), ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                
                                <td class="px-6 py-4 text-center">
                                    <form id="<?php echo htmlspecialchars($form_id, ENT_QUOTES, 'UTF-8'); ?>" class="notas" action="puntuaciones_lista_figuras_code.php" method="post" onsubmit="return false;">
                                        <input type="hidden" name="ajax" value="1">
                                        <input type="hidden" name="id_inscripcion_figuras" value="<?php echo $row['id']; ?>">
                                        <input type="hidden" name="id_fase" value="<?php echo $id_fase; ?>">
                                        <input type="hidden" name="grado_dificultad" value="<?php echo htmlspecialchars($grado_dificultad, ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo $judge_hiddens; ?>
                                    </form>
                                    <button class="w-10 h-10 mx-auto rounded-xl bg-emerald-500 text-white shadow-lg shadow-emerald-500/20 hover:scale-110 active:scale-95 transition-all flex items-center justify-center btn-puntuar-fila" 
                                            type="button" data-form-id="<?php echo htmlspecialchars($form_id, ENT_QUOTES, 'UTF-8'); ?>" id="puntuar_btn<?php echo $row['id']; ?>">
                                        <i class="fa-solid fa-calculator text-xs"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php
                            }
                        } else {
                            echo "<tr><td colspan='20' class='p-20 text-center text-slate-400 font-bold uppercase italic tracking-widest'>No hay nadadoras inscritas en esta figura.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($num_jueces == 0): ?>
            <div class="p-8 bg-amber-50 border border-amber-100 rounded-[2rem] text-center mb-10">
                <i class="fas fa-exclamation-triangle text-amber-500 text-3xl mb-4"></i>
                <h3 class="text-amber-800 font-black uppercase italic tracking-tighter">Panel sin configurar</h3>
                <p class="text-amber-600 text-sm font-medium">No se han asignado jueces a esta fase. Por favor, configura el <a href="paneles_jueces.php" class="underline font-black">Panel de Jueces</a> primero.</p>
            </div>
        <?php endif; ?>

        <!-- Modal de Penalizaciones de Figuras -->
        <div id="modalPenalizaciones" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="relative bg-white rounded-3xl max-w-lg w-full p-6 md:p-8 shadow-2xl border border-slate-100 transform transition-all animate-fade-in" onclick="event.stopPropagation()">
                
                <!-- Header -->
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center shadow-xs">
                            <i class="fas fa-gavel text-base"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-800 tracking-tight leading-none uppercase">Penalizaciones</h3>
                            <p id="modalPenNadadora" class="text-xs font-bold text-slate-500 mt-1 uppercase italic"></p>
                        </div>
                    </div>
                    <button type="button" onclick="cerrarModalPenalizaciones()" class="w-8 h-8 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center transition-all cursor-pointer">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <!-- Lista de Penalizaciones Activas -->
                <div class="mb-6">
                    <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-3">Penalizaciones Activas en esta Figura</label>
                    <div id="modalPenLista" class="space-y-2 max-h-48 overflow-y-auto pr-1">
                        <!-- Rellenado dinámico por JS -->
                    </div>
                </div>

                <!-- Formulario Añadir Penalización -->
                <div class="bg-slate-50/70 rounded-2xl p-4 sm:p-5 border border-slate-200/80 mb-6">
                    <label for="selectPenalizacion" class="block text-[10px] font-black uppercase text-slate-500 tracking-widest mb-2 flex items-center justify-between">
                        <span>Añadir Penalización</span>
                        <span class="text-[9px] font-bold text-rose-500 uppercase">Catálogo Oficial</span>
                    </label>
                    <select id="selectPenalizacion" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2.5 text-xs font-bold text-slate-700 shadow-2xs focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 outline-none mb-3">
                        <option value="0">-- Selecciona una penalización --</option>
                        <?php foreach ($grupos_catalogo as $grp): ?>
                            <?php if (!empty($grp['items'])): ?>
                                <optgroup label="<?php echo htmlspecialchars($grp['label'], ENT_QUOTES, 'UTF-8'); ?>" class="font-bold text-slate-800 bg-slate-50">
                                    <?php foreach ($grp['items'] as $item): ?>
                                        <?php 
                                            $pts = floatval($item['puntos']);
                                            $pts_str = ($pts > 0 ? "-$pts" : "$pts") . " pts";
                                            $aplic = !empty($item['aplicable_a']) ? ' [' . $item['aplicable_a'] . ']' : '';
                                        ?>
                                        <option value="<?php echo $item['id']; ?>" class="bg-white text-slate-800 font-medium py-1">
                                            <?php echo htmlspecialchars($item['codigo'] . ' (' . $pts_str . ') ' . $item['resumen'] . $aplic, ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" id="btnAplicarPenalizacion" onclick="guardarPenalizacionModal()" class="w-full py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-black uppercase text-xs tracking-wider rounded-xl shadow-md shadow-rose-500/20 active:scale-[0.98] transition-all flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fas fa-plus text-xs"></i> Aplicar Penalización
                    </button>
                </div>

                <!-- Footer -->
                <div class="flex justify-end">
                    <button type="button" onclick="cerrarModalPenalizaciones()" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-black uppercase text-xs tracking-wider rounded-xl active:scale-95 transition-all cursor-pointer">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>

    </div>
</main>

<?php include('includes/scripts.php'); ?>
<script src="puntuaciones_lista_figuras.js?v=<?php echo time(); ?>"></script>
<?php include('includes/footer.php'); ?>
