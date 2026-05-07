<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');
?>
<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-surface font-lexend">

    <?php include('includes/topbar.php'); ?>

    <div class="p-6 md:p-10 max-w-7xl mx-auto w-full">
        <!-- Titulo página -->
        <?php
        $id_fase_post = (int)($_POST['id_fase'] ?? 0);
        $query = "SELECT categorias.nombre as categoria, modalidades.nombre as modalidad, figuras.nombre as figura, numero, grado_dificultad FROM fases, categorias, modalidades, figuras WHERE fases.id=$id_fase_post and categorias.id = fases.id_categoria and modalidades.id = fases.id_modalidad and figuras.id = fases.id_figura";
        $res_header = mysqli_query($connection, $query);
        $nombres = mysqli_fetch_assoc($res_header);
        $nombre_modalidad = $nombres['modalidad'] ?? '';
        $nombre_categoria = $nombres['categoria'] ?? '';
        $numero_figura = $nombres['numero'] ?? '';
        $nombre_figura = $nombres['figura'] ?? '';
        $grado_dificultad = $nombres['grado_dificultad'] ?? '';
        ?>

        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <span class="px-3 py-1 bg-blue-50 text-blue-600 text-[10px] font-black rounded-lg border border-blue-100 uppercase tracking-widest italic shadow-sm"><?php echo $nombre_modalidad." ".$nombre_categoria ?></span>
                </div>
                <h1 class="text-3xl font-black text-slate-800 tracking-tighter italic uppercase flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-flag-checkered text-lg"></i></span>
                    Gestión de Puntuaciones
                </h1>
                <p class="text-slate-500 font-medium mt-1">Figura <?php echo $numero_figura; ?> - <span class="font-black text-slate-700 italic"><?php echo $nombre_figura; ?></span> · GD: <span class="text-blue-600 font-black italic"><?php echo $grado_dificultad; ?></span></p>
            </div>
            <div class="flex gap-3">
                <a href="./puntuaciones_fases_puntuar.php?id_fase=<?php echo $id_fase_post;?>" target="_blank" class="px-6 py-3 bg-emerald-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg shadow-emerald-500/20 hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
                    <i class="fas fa-calculator"></i> Calcular
                </a>
                <a href="./informes/informe_puntuaciones.php?titulo=Clasificaci%C3%B3n%20detallada&id_fase=<?php echo $id_fase_post;?>" target="_blank" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 font-black uppercase text-xs tracking-widest rounded-2xl shadow-sm hover:bg-slate-50 transition-all flex items-center gap-2 italic">
                    <i class="fas fa-file-pdf text-red-500"></i> Descargar
                </a>
            </div>
        </div>

        <?php
        if(isset($_SESSION['correcto']) && $_SESSION['correcto'] != ''){
            echo '<div class="mb-8 p-6 bg-white border-l-[6px] border-l-emerald-500 text-slate-700 rounded-r-3xl shadow-sm flex items-center gap-4 animate-fade-in">
                    <div class="w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-500 shadow-sm"><i class="fas fa-check-circle text-xl"></i></div>
                    <span class="text-base font-bold">'.$_SESSION['correcto'].'</span>
                  </div>';
            unset($_SESSION['correcto']);
        }
        if(isset($_SESSION['estado']) && $_SESSION['estado'] != ''){
            echo '<div class="mb-8 p-6 bg-white border-l-[6px] border-l-red-500 text-slate-700 rounded-r-3xl shadow-sm flex items-center gap-4 animate-fade-in">
                    <div class="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center text-red-500 shadow-sm"><i class="fas fa-exclamation-triangle text-xl"></i></div>
                    <span class="text-base font-bold">'.$_SESSION['estado'].'</span>
                  </div>';
            unset($_SESSION['estado']);
        }

        if (!function_exists('puntuaciones_fmt_hasta4')) {
            function puntuaciones_fmt_hasta4($v) {
                if ($v === null || $v === '') return '';
                return number_format((float)$v, 4, '.', '');
            }
        }
        ?>

        <style>
            @keyframes puntuacionMediaNotaFlash {
                0% { background-color: rgba(59, 130, 246, 0.2); }
                100% { background-color: transparent; }
            }
            .puntuacion-celda-flash {
                animation: puntuacionMediaNotaFlash 1.5s ease-out forwards;
            }
            .v3-table-input {
                @apply w-16 px-2 py-1.5 rounded-lg border-2 border-slate-100 bg-slate-50 text-center font-black text-slate-700 transition-all focus:border-blue-500 focus:bg-white outline-none;
            }
            /* Ocultar tiradores de inputs number */
            input::-webkit-outer-spin-button,
            input::-webkit-inner-spin-button {
                -webkit-appearance: none;
                margin: 0;
            }
            input[type=number] {
                -moz-appearance: textfield;
            }
        </style>

        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto no-scrollbar">
                <?php
                $id_fase = (int) $id_fase_post;
                $query = "SELECT inscripciones_figuras.id, inscripciones_figuras.baja,
                    inscripciones_figuras.orden AS orden_fase_actual,
                    COALESCE(
                        (SELECT if2.orden FROM inscripciones_figuras if2
                         WHERE if2.id_nadadora = inscripciones_figuras.id_nadadora
                           AND if2.id_fase = (
                               SELECT f0.id FROM fases f0
                               WHERE f0.id_competicion = fases.id_competicion
                                 AND f0.id_categoria = fases.id_categoria
                                 AND IFNULL(f0.id_modalidad,'') = IFNULL(fases.id_modalidad,'')
                               ORDER BY f0.orden ASC, f0.id ASC
                               LIMIT 1
                           )
                         LIMIT 1),
                        inscripciones_figuras.orden
                    ) AS orden_primera_fase,
                    inscripciones_figuras.id_nadadora, nota_total, nota_media, nota_final,
                    nadadoras.nombre as nombre_nadadora, nadadoras.apellidos as apellidos_nadadora,
                    categorias.nombre as nombre_categoria, inscripciones_figuras.id_fase, fases.elementos_coach_card
                    FROM inscripciones_figuras, fases, modalidades, categorias, nadadoras
                    WHERE fases.id = ".$id_fase."
                    AND inscripciones_figuras.id_fase = fases.id
                    AND fases.id_modalidad = modalidades.id
                    AND fases.id_categoria = categorias.id
                    AND inscripciones_figuras.id_nadadora = nadadoras.id
                    AND fases.id_competicion = ".$_SESSION['id_competicion_activa']."
                    ORDER BY inscripciones_figuras.orden, inscripciones_figuras.id, fases.orden, fases.id";

                $query_run = mysqli_query($connection, $query);
                ?>
                <table class="w-full text-left border-collapse" id="puntuacionesTable">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-400 text-[10px] font-black uppercase tracking-[0.2em]">
                            <th class="px-6 py-5 w-24 text-center uppercase tracking-tighter italic leading-tight">Orden<br><span class="text-[8px] opacity-50">#ID</span></th>
                            <th class="px-6 py-5">Nadadora / Deportista</th>
                            <?php
                            $query_j = "SELECT id FROM panel_jueces WHERE id_fase=".$id_fase;
                            $jueces_res = mysqli_query($connection,$query_j);
                            $num_jueces = 0;
                            while($juez = mysqli_fetch_assoc($jueces_res)){
                                $num_jueces++;
                                echo '<th class="px-1 py-5 text-center uppercase tracking-tighter">J'.$num_jueces.'</th>';
                            }
                            ?>
                            <th class="px-2 py-5 text-center bg-slate-100/50 text-[9px]">SUM</th>
                            <th class="px-2 py-5 text-center text-[9px]">TOTAL</th>
                            <th class="px-2 py-5 text-center text-[9px]">MEDIA</th>
                            <th class="px-2 py-5 text-center bg-blue-50 text-blue-600 text-[9px]">NOTA FINAL</th>
                            <th class="px-4 py-5 text-center w-20">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    <?php
                    if(mysqli_num_rows($query_run) > 0){
                        while ($row = mysqli_fetch_assoc($query_run)) {
                            $es_baja = ($row['baja'] == 'si');
                            $tr_class = $es_baja ? 'bg-amber-50 opacity-60 italic' : 'hover:bg-slate-50/50 transition-colors';
                            $form_id = 'notas-row-' . (int) $row['id'];
                    ?>
                        <tr id="<?php echo $row['id'];?>" class="<?php echo $tr_class; ?>">
                            <td class="px-6 py-4 text-center">
                                <div class="flex flex-col items-center">
                                    <span class="text-sm font-black text-slate-800 italic"><?php echo $row['orden_primera_fase']; ?></span>
                                    <span class="text-xs font-black text-slate-400 italic opacity-70">#<?php echo $row['id']; ?></span>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-black text-slate-700 uppercase tracking-tighter leading-tight"><?php echo $row['apellidos_nadadora'].', '.$row['nombre_nadadora']; ?></span>
                                    <?php if($es_baja): ?>
                                        <span class="text-[8px] font-black text-amber-600 uppercase tracking-widest italic flex items-center gap-1"><i class="fas fa-user-slash"></i> Baja Confirmada</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <?php
                            $query_p = "SELECT id, id_panel, numero_juez, id_juez FROM panel_jueces WHERE id_fase=".$id_fase;
                            $jueces_p = mysqli_query($connection,$query_p);
                            $sumatorio = 0;
                            $judge_hiddens = '';
                            while($juez = mysqli_fetch_assoc($jueces_p)){
                                $query_n = "SELECT nota FROM puntuaciones_jueces WHERE id_panel_juez= ".$juez['id']." and id_inscripcion_figuras = ".$row['id'];
                                $nota = mysqli_result(mysqli_query($connection,$query_n),0);
                                $sumatorio += $nota;
                                $nj = (int) $juez['numero_juez'];
                                
                                // Clases para compatibilidad con JS antiguo
                                $is_auditor = ($juez['id_juez'] == '108');
                                $js_compat_classes = 'form-control form-control-sm ' . ($is_auditor ? 'table-warning' : '');
                                
                                // Look v3.0 - Aumentado tamaño de w-14 a w-16 y text-sm a text-lg
                                $v3_look_classes = $is_auditor ? 'border-amber-300 bg-amber-50 text-amber-700 shadow-inner' : 'border-slate-100 bg-slate-50 text-slate-700';
                                
                                echo '<td class="px-1 py-4 text-center">
                                        <input form="'.htmlspecialchars($form_id, ENT_QUOTES, 'UTF-8').'" 
                                               class="w-16 px-1 py-2.5 rounded-xl border-2 font-black text-center text-lg transition-all focus:border-blue-500 focus:bg-white outline-none '.$js_compat_classes.' '.$v3_look_classes.'" 
                                               name="nota['.$nj.'][nota]" step="0.1" type="number" value="'.$nota.'">
                                      </td>';
                                
                                $judge_hiddens .= '<input type="hidden" name="nota['.$nj.'][id_juez]" value="'.htmlspecialchars($juez['id_juez'], ENT_QUOTES, 'UTF-8').'">';
                                $judge_hiddens .= '<input type="hidden" name="nota['.$nj.'][id_panel_jueces]" value="'.(int) $juez['id'].'">';
                            }
                            ?>
                            <td class="px-2 py-4 text-center font-bold text-slate-400 js-sum-s bg-slate-50/30 italic text-xs"><?php echo $sumatorio; ?></td>
                            <td class="px-2 py-4 text-center font-black text-slate-700 js-nota-total italic text-sm"><?php echo $row['nota_total']; ?></td>
                            <td class="px-2 py-4 text-center font-black text-slate-400 js-nota-media italic text-xs"><?php echo htmlspecialchars(puntuaciones_fmt_hasta4($row['nota_media']), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="px-2 py-4 text-center font-black text-blue-600 bg-blue-50/30 js-nota-final italic text-lg"><?php echo htmlspecialchars(puntuaciones_fmt_hasta4($row['nota_final']), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="px-4 py-4 text-center">
                                <form id="<?php echo htmlspecialchars($form_id, ENT_QUOTES, 'UTF-8'); ?>" class="notas" action="puntuaciones_lista_figuras_code.php" method="post" onsubmit="return false;">
                                    <input type="hidden" name="ajax" value="1">
                                    <input type="hidden" name="id_inscripcion_figuras" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="id_fase" value="<?php echo $row['id_fase']; ?>">
                                    <input type="hidden" name="grado_dificultad" value="<?php echo htmlspecialchars($grado_dificultad, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo $judge_hiddens; ?>
                                </form>
                                <button class="w-10 h-10 rounded-xl bg-emerald-500 text-white shadow-lg shadow-emerald-500/20 hover:scale-110 active:scale-95 transition-all flex items-center justify-center btn-puntuar-fila" 
                                        type="button" data-form-id="<?php echo htmlspecialchars($form_id, ENT_QUOTES, 'UTF-8'); ?>" id="puntuar_btn<?php echo $row['id']; ?>">
                                    <i class="fa-solid fa-calculator text-xs"></i>
                                </button>
                            </td>
                        </tr>
                    <?php
                        }
                    } else {
                        echo "<tr><td colspan='20' class='p-20 text-center text-slate-400 font-bold uppercase italic tracking-widest'>No se han encontrado registros en la base de datos</td></tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<?php include('includes/scripts.php'); ?>
<script src="puntuaciones_lista_figuras.js?v=5"></script>
<?php include('includes/footer.php'); ?>

