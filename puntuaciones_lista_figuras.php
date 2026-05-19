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
$query = "SELECT c.nombre as categoria, m.nombre as modalidad, fig.nombre as figura, fig.numero, fig.grado_dificultad 
          FROM fases f
          JOIN categorias c ON f.id_categoria = c.id 
          JOIN modalidades m ON f.id_modalidad = m.id 
          JOIN figuras fig ON f.id_figura = fig.id 
          WHERE f.id = $id_fase";
$res_header = mysqli_query($connection, $query);
$nombres = mysqli_fetch_assoc($res_header);

$nombre_modalidad = $nombres['modalidad'] ?? '';
$nombre_categoria = $nombres['categoria'] ?? '';
$numero_figura = $nombres['numero'] ?? '';
$nombre_figura = $nombres['figura'] ?? '';
$grado_dificultad = $nombres['grado_dificultad'] ?? '0.0';

// 2. Obtener Jueces del Panel
$query_p = "SELECT id, id_panel, numero_juez, id_juez FROM panel_jueces WHERE id_fase = $id_fase ORDER BY numero_juez ASC";
$res_jueces = mysqli_query($connection, $query_p);
$jueces_array = [];
while ($j = mysqli_fetch_assoc($res_jueces)) {
    $jueces_array[] = $j;
}
$num_jueces = count($jueces_array);
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-surface font-lexend">

    <?php include('includes/topbar.php'); ?>

    <div class="p-6 md:p-10 max-w-full mx-auto w-full">
        
        <!-- Header de Página -->
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <span class="px-3 py-1 bg-blue-50 text-blue-600 text-[10px] font-black rounded-lg border border-blue-100 uppercase tracking-widest italic shadow-sm">
                        <?php echo $nombre_modalidad . " " . $nombre_categoria ?>
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
            <div class="flex gap-3">
                <a href="./puntuaciones_fases_puntuar.php?id_fase=<?php echo $id_fase; ?>" target="_blank" class="px-6 py-3 bg-emerald-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg shadow-emerald-500/20 hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
                    <i class="fas fa-calculator"></i> Calcular
                </a>
                <a href="./informes/informe_puntuaciones.php?titulo=Clasificaci%C3%B3n%20detallada&id_fase=<?php echo $id_fase; ?>" target="_blank" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 font-black uppercase text-xs tracking-widest rounded-2xl shadow-sm hover:bg-slate-50 transition-all flex items-center gap-2 italic">
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
                            <th class="px-6 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest text-center">Ord</th>
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
                        // Consulta de Inscripciones
                        $q_insc = "SELECT i.*, n.nombre, n.apellidos, cl.nombre_corto 
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
                        ?>
                            <tr class="<?php echo $row_class; ?>">
                                <td class="px-6 py-4 text-center">
                                    <span class="text-xs font-black text-slate-400 italic">#<?php echo $row['orden']; ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-black text-slate-700 uppercase leading-none mb-1">
                                            <?php echo $row['apellidos'] . ", " . $row['nombre']; ?>
                                            <?php if($is_baja) echo ' <span class="text-[9px] text-red-500">[BAJA]</span>'; ?>
                                        </span>
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest"><?php echo $row['nombre_corto']; ?></span>
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

    </div>
</main>

<?php include('includes/scripts.php'); ?>
<script src="puntuaciones_lista_figuras.js?v=<?php echo time(); ?>"></script>
<?php include('includes/footer.php'); ?>
