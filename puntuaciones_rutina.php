<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');

$id_rutina = $_POST['id_rutina'] ?? $_GET['id_rutina'] ?? 0;

$query = "SELECT f.* FROM fases f JOIN rutinas r ON f.id = r.id_fase WHERE r.id='$id_rutina'";
$fase = mysqli_fetch_assoc(mysqli_query($connection, $query));
$id_fase = $fase['id'];
$isOldSystem = ($fase['obsoleto'] == 'si');

$q_rutina = "SELECT r.id, r.orden, r.nombre as nombre_rutina, r.id_club, r.baja, c.nombre_corto as nombre_club, m.nombre as nombre_modalidad, cat.nombre as nombre_categoria, r.id_fase, f.elementos_coach_card, m.id as id_modalidad, r.dd_total, r.nota_final 
             FROM rutinas r 
             JOIN fases f ON r.id_fase = f.id 
             JOIN modalidades m ON f.id_modalidad = m.id 
             JOIN categorias cat ON f.id_categoria = cat.id 
             JOIN clubes c ON r.id_club = c.id 
             WHERE r.id = $id_rutina";
$rutina = mysqli_fetch_assoc(mysqli_query($connection, $q_rutina));

$q_nombres = "SELECT GROUP_CONCAT(n.nombre SEPARATOR ', ') as nombres 
              FROM rutinas_participantes rp 
              JOIN nadadoras n ON n.id = rp.id_nadadora 
              WHERE rp.reserva = 'no' AND rp.id_rutina = $id_rutina";
$nombres = mysqli_fetch_assoc(mysqli_query($connection, $q_nombres))['nombres'] ?? '';

$orden_display = ($rutina['orden'] == -1) ? 'PS' : $rutina['orden'];
?>

<main class="flex-1 flex flex-col min-w-0 bg-surface">
    <?php include('includes/topbar.php'); ?>

    <div class="p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend text-primary">
        
        <!-- Header -->
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <div class="flex items-center gap-3 mb-2 animate-fade-in">
                    <?php if($isOldSystem): ?>
                        <span class="px-3 py-1 bg-slate-200 text-slate-600 text-[10px] font-black uppercase tracking-widest rounded-full border border-slate-300 shadow-sm"><i class="fas fa-clock-rotate-left"></i> Sistema OBSOLETO</span>
                    <?php else: ?>
                        <span class="px-3 py-1 bg-blue-50 text-blue-600 text-[10px] font-black uppercase tracking-widest rounded-full border border-blue-100 shadow-sm"><i class="fas fa-certificate"></i> Sistema AQUA</span>
                    <?php endif; ?>
                </div>
                <h1 class="text-4xl font-black text-slate-800 tracking-tighter mb-2 flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-star text-lg"></i></span>
                    Puntuación
                </h1>
                <p class="text-slate-500 font-medium text-lg uppercase tracking-tight italic">
                    <?php echo $rutina['nombre_modalidad']." ".$rutina['nombre_categoria']; ?>
                </p>
            </div>
            
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 text-right min-w-[250px]">
                <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1">Nota Final Actual</p>
                <p class="text-3xl font-black text-emerald-600"><?php echo $rutina['nota_final'] > 0 ? $rutina['nota_final'] : '0.0000'; ?></p>
            </div>
        </div>

        <?php include('includes/alertas_v4.php'); ?>

        <!-- Rutina Info Card -->
        <div class="bg-white rounded-[2rem] p-6 md:p-8 shadow-sm border border-slate-200 mb-10 flex flex-col md:flex-row gap-8 items-center relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-slate-50 rounded-bl-full -z-0"></div>
            <div class="w-20 h-20 rounded-3xl bg-blue-50 text-blue-500 flex flex-col items-center justify-center border border-blue-100 shadow-inner flex-shrink-0 z-10">
                <span class="text-[10px] font-black uppercase opacity-40 leading-none mb-1">Orden</span>
                <span class="text-3xl font-black leading-none"><?php echo $orden_display; ?></span>
            </div>
            <div class="flex-1 z-10">
                <div class="flex items-center gap-3 mb-2">
                    <span class="px-3 py-1 bg-slate-100 text-slate-600 font-black text-[10px] uppercase tracking-widest rounded-lg border border-slate-200"><?php echo $rutina['nombre_club']; ?></span>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">ID: #<?php echo $id_rutina; ?></span>
                </div>
                <?php if($rutina['nombre_rutina']): ?>
                    <h3 class="text-xl font-black text-slate-800 italic tracking-tighter mb-1">"<?php echo $rutina['nombre_rutina']; ?>"</h3>
                <?php endif; ?>
                <p class="text-sm font-bold text-slate-500 leading-relaxed"><i class="fas fa-users text-slate-300 mr-2"></i><?php echo $nombres; ?></p>
            </div>
        </div>

        <form action="puntuaciones_rutina_code.php" enctype="multipart/form-data" method="post" id="formulario">
            <input type="hidden" name="edit_id" value="<?php echo $rutina['id']; ?>">
            <input type="hidden" name="id_fase" value="<?php echo $id_fase; ?>">
            <input type="hidden" name="id_club" value="<?php echo $rutina['id_club']; ?>">
            <input type="hidden" name="id_rutina" value="<?php echo $id_rutina; ?>">
            <input type="hidden" name="id_competicion" value="<?php echo $_SESSION['id_competicion_activa']; ?>">

            <?php if (!$isOldSystem): ?>
                <!-- ========================================== -->
                <!-- SISTEMA AQUA (MODERNO)                     -->
                <!-- ========================================== -->
                
                <!-- ELEMENTOS -->
                <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 mb-8 overflow-hidden">
                    <div class="px-8 py-6 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
                        <h2 class="text-lg font-black text-slate-800 uppercase tracking-tighter flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center"><i class="fas fa-list-ol text-sm"></i></div>
                            Elementos
                        </h2>
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Factor * (BM o DD) * X̅</span>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse whitespace-nowrap">
                            <thead>
                                <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 bg-white">
                                    <th class="px-6 py-4">Elemento / Factor</th>
                                    <th class="px-6 py-4 text-center">BM</th>
                                    <th class="px-6 py-4 text-center">DD</th>
                                    <?php
                                    $q_jueces1 = "SELECT * FROM panel_jueces WHERE id_fase=$id_fase AND id_panel IN (SELECT id FROM paneles WHERE id_paneles_tipo = 1 AND id_competicion=".$_SESSION['id_competicion_activa'].") ORDER BY numero_juez";
                                    $res_jueces1 = mysqli_query($connection, $q_jueces1);
                                    $jueces1 = [];
                                    while ($j = mysqli_fetch_assoc($res_jueces1)) {
                                        $jueces1[] = $j;
                                        echo "<th class='px-6 py-4 text-center text-blue-600 bg-blue-50/30'>J".$j['numero_juez']."</th>";
                                    }
                                    ?>
                                    <th class="px-6 py-4 text-center">X̅</th>
                                    <th class="px-6 py-4 text-right">Puntos</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <?php
                                $q_elem = "SELECT h.id, h.elemento, h.tipo, h.texto, t.nombre, t.color 
                                           FROM hibridos_rutina h 
                                           JOIN tipo_hibridos t ON h.texto = t.id 
                                           WHERE h.id_rutina = '$id_rutina' AND h.tipo = 'part' AND h.texto != '3' 
                                           ORDER BY h.elemento";
                                $res_elem = mysqli_query($connection, $q_elem);
                                $nota_elementos = 0;
                                $tab_index = 1;

                                if (mysqli_num_rows($res_elem) > 0) {
                                    while ($el = mysqli_fetch_assoc($res_elem)) {
                                        $factor = 0;
                                        if ($el['nombre'] == 'HYBRID') $factor = $fase['f_hybrid'];
                                        elseif ($el['nombre'] == 'ACROBATIC') $factor = $fase['f_acro'];
                                        elseif ($el['nombre'] == 'TRE') $factor = $fase['f_tre'];

                                        $q_dd = "SELECT valor FROM hibridos_rutina WHERE tipo = 'total' AND id_rutina=$id_rutina AND elemento=".$el['elemento'];
                                        $dd = mysqli_fetch_assoc(mysqli_query($connection, $q_dd))['valor'] ?? 0;
                                        
                                        $q_bm = "SELECT sum(valor) as val FROM hibridos_rutina WHERE tipo = 'basemark' AND id_rutina=$id_rutina AND elemento=".$el['elemento'];
                                        $bm = mysqli_fetch_assoc(mysqli_query($connection, $q_bm))['val'] ?? 0;
                                        
                                        $q_llevado = "SELECT llevado_BM FROM puntuaciones_elementos WHERE id_rutina=$id_rutina AND elemento=".$el['elemento'];
                                        $llevado = mysqli_fetch_assoc(mysqli_query($connection, $q_llevado))['llevado_BM'] ?? 'no';
                                        
                                        $q_notas = "SELECT nota_media, nota FROM puntuaciones_elementos WHERE elemento=".$el['elemento']." AND id_rutina=$id_rutina";
                                        $nota_bd = mysqli_fetch_assoc(mysqli_query($connection, $q_notas));
                                ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <span class="w-6 h-6 rounded-md flex items-center justify-center text-[10px] font-black text-white" style="background-color: <?php echo $el['color']; ?>;"><?php echo $el['elemento']; ?></span>
                                            <div>
                                                <p class="text-xs font-black text-slate-700"><?php echo $el['nombre']; ?></p>
                                                <p class="text-[9px] font-bold text-slate-400">F: <?php echo $factor; ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <label class="flex items-center justify-center gap-2 cursor-pointer group">
                                            <input type="checkbox" name="BM<?php echo $el['elemento']; ?>" value="<?php echo $bm; ?>" <?php echo $llevado == 'si' ? 'checked' : ''; ?> class="w-4 h-4 rounded text-red-500 focus:ring-red-500 border-slate-300">
                                            <span class="text-xs font-black text-slate-500 group-hover:text-red-600 transition-colors"><?php echo $bm; ?></span>
                                        </label>
                                    </td>
                                    <td class="px-6 py-4 text-center font-black text-slate-700"><?php echo $dd; ?></td>
                                    
                                    <input type="hidden" name="dd<?php echo $el['elemento']; ?>" value="<?php echo $dd; ?>">
                                    <input type="hidden" name="factor<?php echo $el['elemento']; ?>" value="<?php echo $factor; ?>">

                                    <?php foreach ($jueces1 as $j): 
                                        $q_jn = "SELECT id, nota, nota_menor, nota_mayor FROM puntuaciones_jueces WHERE id_rutina=$id_rutina AND id_panel_juez=".$j['id']." AND id_elemento=".$el['elemento'];
                                        $jn = mysqli_fetch_assoc(mysqli_query($connection, $q_jn));
                                        $tachado = ($jn['nota_menor'] == 'si' || $jn['nota_mayor'] == 'si') ? 'line-through text-slate-400 bg-slate-100' : 'text-slate-700 font-bold';
                                        $media_class = ($j['id_juez'] == '108') ? 'juez-media bg-amber-50 border-amber-200' : '';
                                        $ti = $tab_index + ($j['numero_juez']*15);
                                    ?>
                                        <td class="px-2 py-3 bg-blue-50/10">
                                            <input type="hidden" name="id_panel_juez<?php echo $j['numero_juez']; ?>" value="<?php echo $j['id']; ?>">
                                            <input type="text" inputmode="decimal" name="notaE<?php echo $el['elemento']; ?>J<?php echo $j['numero_juez']; ?>" id="notaE<?php echo $el['elemento']; ?>J<?php echo $j['numero_juez']; ?>" value="<?php echo $jn['nota'] ?? ''; ?>" tabindex="<?php echo $ti; ?>" class="w-20 px-2 py-2 text-center text-sm rounded-lg border-slate-200 shadow-inner focus:ring-blue-500 focus:border-blue-500 form-control <?php echo $tachado . ' ' . $media_class; ?>">
                                        </td>
                                    <?php endforeach; $tab_index++; ?>

                                    <td class="px-6 py-4 text-center font-bold text-slate-500"><?php echo $nota_bd['nota_media'] ?? '-'; ?></td>
                                    <td class="px-6 py-4 text-right font-black text-blue-600 text-lg bg-blue-50/30"><?php echo $nota_bd['nota'] ?? '-'; ?></td>
                                </tr>
                                <?php 
                                        $nota_elementos += ($nota_bd['nota'] ?? 0);
                                    }
                                } else {
                                    echo "<tr><td colspan='10' class='p-8 text-center text-slate-400 font-bold italic uppercase tracking-widest'>No hay elementos registrados</td></tr>";
                                }
                                ?>
                            </tbody>
                            <tfoot>
                                <tr class="bg-blue-50 border-t-2 border-blue-100">
                                    <td colspan="<?php echo 4 + count($jueces1); ?>" class="px-6 py-4 text-right text-xs font-black uppercase text-blue-500 tracking-widest">Nota Total Elementos</td>
                                    <td class="px-6 py-4 text-right text-2xl font-black text-blue-700"><?php echo number_format($nota_elementos, 4); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- ERRORES DE SINCRONIZACIÓN -->
                <?php if ($rutina['id_modalidad'] != 1 && $rutina['id_modalidad'] != 5): ?>
                <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 mb-8 overflow-hidden">
                    <div class="px-8 py-6 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
                        <h2 class="text-lg font-black text-slate-800 uppercase tracking-tighter flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-red-100 text-red-600 flex items-center justify-center"><i class="fas fa-exclamation-triangle text-sm"></i></div>
                            Errores de Sincronización
                        </h2>
                    </div>
                    <?php
                    $q_err = "SELECT * FROM errores_sincronizacion WHERE id_rutina=$id_rutina";
                    $err = mysqli_fetch_assoc(mysqli_query($connection, $q_err));
                    $pen_sincro = ($err['errores_pequenos'] * $fase['error_xs']) + ($err['errores_obvios'] * $fase['error_ob']) + ($err['errores_mayores'] * $fase['error_xl']);
                    $tab_sincro = 100;
                    ?>
                    <div class="p-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1">Pequeños (<?php echo $fase['error_xs']; ?>)</label>
                            <input type="text" inputmode="numeric" name="errores_pequenos" value="<?php echo $err['errores_pequenos'] ?? 0; ?>" tabindex="<?php echo $tab_sincro++; ?>" class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 text-lg font-black text-slate-700 shadow-inner focus:ring-red-500 focus:border-red-500 form-control">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1">Obvios (<?php echo $fase['error_ob']; ?>)</label>
                            <input type="text" inputmode="numeric" name="errores_obvios" value="<?php echo $err['errores_obvios'] ?? 0; ?>" tabindex="<?php echo $tab_sincro++; ?>" class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 text-lg font-black text-slate-700 shadow-inner focus:ring-red-500 focus:border-red-500 form-control">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1">Mayores (<?php echo $fase['error_xl']; ?>)</label>
                            <input type="text" inputmode="numeric" name="errores_mayores" value="<?php echo $err['errores_mayores'] ?? 0; ?>" tabindex="<?php echo $tab_sincro++; ?>" class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 text-lg font-black text-slate-700 shadow-inner focus:ring-red-500 focus:border-red-500 form-control">
                        </div>
                    </div>
                    <div class="px-8 py-4 bg-red-50 border-t border-red-100 flex justify-between items-center">
                        <span class="text-xs font-black uppercase text-red-500 tracking-widest">Penalización Total Sincronización</span>
                        <span class="text-2xl font-black text-red-700"><?php echo number_format($pen_sincro, 4); ?></span>
                    </div>
                </div>
                <?php else: $pen_sincro = 0; endif; ?>

                <!-- IMPRESIÓN ARTÍSTICA -->
                <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 mb-8 overflow-hidden">
                    <div class="px-8 py-6 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
                        <h2 class="text-lg font-black text-slate-800 uppercase tracking-tighter flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center"><i class="fas fa-palette text-sm"></i></div>
                            Impresión Artística
                        </h2>
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Factor * ∑</span>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse whitespace-nowrap">
                            <thead>
                                <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 bg-white">
                                    <th class="px-6 py-4">Componente</th>
                                    <th class="px-6 py-4 text-center">Factor</th>
                                    <?php
                                    $q_jueces2 = "SELECT * FROM panel_jueces WHERE id_fase=$id_fase AND id_panel IN (SELECT id FROM paneles WHERE id_paneles_tipo = 2 AND id_competicion=".$_SESSION['id_competicion_activa'].") ORDER BY numero_juez";
                                    $res_jueces2 = mysqli_query($connection, $q_jueces2);
                                    $jueces2 = [];
                                    while ($j = mysqli_fetch_assoc($res_jueces2)) {
                                        $jueces2[] = $j;
                                        echo "<th class='px-6 py-4 text-center text-emerald-600 bg-emerald-50/30'>J".$j['numero_juez']."</th>";
                                    }
                                    ?>
                                    <th class="px-6 py-4 text-center">∑</th>
                                    <th class="px-6 py-4 text-right">Puntos</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <?php 
                                $componentes_ia = [
                                    'ChoMu' => ['label' => 'Choreography & Music', 'factor' => $fase['f_chomu']],
                                    'Performance' => ['label' => 'Performance', 'factor' => $fase['f_performance']],
                                    'Transitions' => ['label' => 'Transitions', 'factor' => $fase['f_transitions']]
                                ];
                                $nota_ia = 0;
                                $tab_index_ia = 100;

                                foreach($componentes_ia as $key => $data):
                                    $q_notas = "SELECT nota_media, nota FROM puntuaciones_elementos WHERE tipo_ia = '$key' AND id_rutina=$id_rutina";
                                    $nota_bd = mysqli_fetch_assoc(mysqli_query($connection, $q_notas));
                                    $sum_jueces = 0;
                                ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <p class="text-xs font-black text-slate-700"><?php echo $data['label']; ?></p>
                                    </td>
                                    <td class="px-6 py-4 text-center font-bold text-slate-500"><?php echo $data['factor']; ?></td>
                                    <?php 
                                    $factor_name = ($key == 'ChoMu') ? 'f_chomu' : 'factor_'.$key; 
                                    ?>
                                    <input type="hidden" name="<?php echo $factor_name; ?>" value="<?php echo $data['factor']; ?>">

                                    <?php foreach ($jueces2 as $j): 
                                        $q_jn = "SELECT id, nota, nota_menor, nota_mayor FROM puntuaciones_jueces WHERE id_rutina=$id_rutina AND id_panel_juez=".$j['id']." AND tipo_ia='$key'";
                                        $jn = mysqli_fetch_assoc(mysqli_query($connection, $q_jn));
                                        $tachado = ($jn['nota_menor'] == 'si' || $jn['nota_mayor'] == 'si') ? 'line-through text-slate-400 bg-slate-100' : 'text-slate-700 font-bold';
                                        if($jn['nota_menor'] != 'si' && $jn['nota_mayor'] != 'si') $sum_jueces += ($jn['nota'] ?? 0);
                                        $media_class = ($j['id_juez'] == '108') ? 'juez-media bg-amber-50 border-amber-200' : '';
                                        $ti = $tab_index_ia + ($j['numero_juez']*5);
                                    ?>
                                        <td class="px-2 py-3 bg-emerald-50/10">
                                            <input type="hidden" name="id_panel_juez_<?php echo $key.$j['numero_juez']; ?>" value="<?php echo $j['id']; ?>">
                                            <input type="text" inputmode="decimal" name="nota<?php echo $key; ?>J<?php echo $j['numero_juez']; ?>" id="nota<?php echo $key; ?>J<?php echo $j['numero_juez']; ?>" value="<?php echo $jn['nota'] ?? ''; ?>" tabindex="<?php echo $ti; ?>" class="w-20 px-2 py-2 text-center text-sm rounded-lg border-slate-200 shadow-inner focus:ring-emerald-500 focus:border-emerald-500 form-control <?php echo $tachado . ' ' . $media_class; ?>">
                                        </td>
                                    <?php endforeach; $tab_index_ia++; ?>

                                    <td class="px-6 py-4 text-center font-bold text-slate-500"><?php echo number_format($sum_jueces, 2); ?></td>
                                    <td class="px-6 py-4 text-right font-black text-emerald-600 text-lg bg-emerald-50/30"><?php echo $nota_bd['nota'] ?? '-'; ?></td>
                                </tr>
                                <?php 
                                    $nota_ia += ($nota_bd['nota'] ?? 0);
                                endforeach; 
                                ?>
                            </tbody>
                            <tfoot>
                                <tr class="bg-emerald-50 border-t-2 border-emerald-100">
                                    <td colspan="<?php echo 3 + count($jueces2); ?>" class="px-6 py-4 text-right text-xs font-black uppercase text-emerald-600 tracking-widest">Nota Total Impresión Artística</td>
                                    <td class="px-6 py-4 text-right text-2xl font-black text-emerald-700"><?php echo number_format($nota_ia, 4); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

            <?php else: ?>
                <!-- ========================================== -->
                <!-- SISTEMA OBSOLETO (ANTIGUO)                 -->
                <!-- ========================================== -->
                <div class="bg-amber-50 rounded-[2.5rem] p-8 border border-amber-200 mb-8 flex flex-col items-center justify-center text-center">
                    <div class="w-16 h-16 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center text-2xl mb-4 shadow-sm"><i class="fas fa-clock-rotate-left"></i></div>
                    <h2 class="text-2xl font-black text-slate-800 tracking-tighter uppercase italic mb-2">Sistema Antiguo Activado</h2>
                    <p class="text-sm font-bold text-slate-500 max-w-2xl">La interfaz de puntuación específica para el sistema de reglamento antiguo está disponible en el modelo clásico. Para procesar notas bajo este reglamento, revisa los paneles asignados en la configuración de la competición.</p>
                </div>
            <?php endif; ?>

            <!-- ========================================== -->
            <!-- BLOQUE COMÚN: PENALIZACIONES Y CÁLCULO     -->
            <!-- ========================================== -->
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 mb-8 p-8 md:p-10">
                <h2 class="text-xl font-black text-slate-800 uppercase tracking-tighter flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center"><i class="fas fa-gavel text-sm"></i></div>
                    Gestión de Penalizaciones
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Listado Penalizaciones Aplicadas -->
                    <div>
                        <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-4">Penalizaciones Activas</p>
                        <div class="space-y-3">
                            <?php
                            $q_pen = "SELECT pr.id as id_pr, p.codigo, p.resumen, p.puntos, pt.nombre as tipo 
                                      FROM penalizaciones_rutinas pr 
                                      JOIN penalizaciones p ON pr.id_penalizacion = p.id 
                                      LEFT JOIN paneles_tipo pt ON p.id_paneles_tipo = pt.id 
                                      WHERE pr.id_rutina = $id_rutina";
                            $res_pen = mysqli_query($connection, $q_pen);
                            $total_pen = 0;
                            if (mysqli_num_rows($res_pen) > 0) {
                                while($pen = mysqli_fetch_assoc($res_pen)) {
                                    $total_pen += $pen['puntos'];
                            ?>
                                <div class="p-4 bg-red-50 border border-red-100 rounded-2xl flex items-center justify-between group">
                                    <div class="flex-1 pr-4">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="px-2 py-0.5 bg-red-100 text-red-600 text-[9px] font-black rounded uppercase"><?php echo $pen['codigo']; ?></span>
                                            <span class="text-[9px] font-bold text-slate-400 uppercase italic"><?php echo $pen['tipo'] ?? 'General'; ?></span>
                                        </div>
                                        <p class="text-xs font-bold text-slate-700 leading-tight"><?php echo $pen['resumen']; ?></p>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <span class="text-lg font-black text-red-600"><?php echo abs($pen['puntos']); ?></span>
                                        <a href="./puntuaciones_rutina_code.php?id_rutina=<?php echo $id_rutina; ?>&id_fase=<?php echo $id_fase; ?>&id_penalizaciones_rutinas=<?php echo $pen['id_pr']; ?>" class="w-8 h-8 rounded-lg bg-white text-slate-300 hover:bg-red-500 hover:text-white hover:border-red-500 transition-all flex items-center justify-center border border-slate-200 shadow-sm"><i class="fas fa-trash-alt text-xs"></i></a>
                                    </div>
                                </div>
                            <?php 
                                }
                            } else {
                                echo "<p class='text-sm text-slate-400 italic font-bold'>No hay penalizaciones registradas.</p>";
                            }
                            ?>
                        </div>
                    </div>

                    <!-- Añadir Penalización -->
                    <div>
                        <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-4">Añadir Penalización</p>
                        <div class="bg-slate-50 p-6 rounded-3xl border border-slate-100">
                            <?php include('./includes/penalizaciones_select_option.php'); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ACTION BAR FIJA -->
            <div class="sticky bottom-6 z-50 bg-slate-900/90 backdrop-blur-md border border-white/10 p-4 rounded-3xl shadow-2xl flex items-center justify-between">
                <div class="px-4">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Total Penalizaciones</p>
                    <p class="text-xl font-black text-red-400"><?php echo number_format(abs($total_pen), 4); ?></p>
                </div>
                <button type="submit" name="save_btn" id="save_btn" tabindex="1000" class="px-8 py-4 bg-emerald-500 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg hover:bg-emerald-400 hover:scale-105 transition-all flex items-center gap-3">
                    <i class="fa-solid fa-square-root-variable text-lg"></i> Calcular y Guardar Nota
                </button>
            </div>

        </form>
    </div>
</main>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
<script type="text/javascript" src="./puntuaciones_rutina.js?v=<?php echo time(); ?>"></script>
<script type="text/javascript">
    // Ajuste de selects para Tailwind si es necesario
    document.addEventListener("DOMContentLoaded", function() {
        const selects = document.querySelectorAll("select");
        selects.forEach(s => {
            s.classList.add("w-full", "px-5", "py-4", "rounded-2xl", "bg-white", "border", "border-slate-200", "text-sm", "font-bold", "text-slate-700", "shadow-sm", "focus:ring-blue-500", "focus:border-blue-500");
        });
    });
</script>
