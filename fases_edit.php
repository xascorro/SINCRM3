<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-surface">
    
    <?php include('includes/topbar.php'); ?>

    <!-- Contenido de la Página -->
    <div class="p-6 md:p-10 max-w-6xl mx-auto w-full font-lexend">
        
        <!-- Header -->
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <h1 class="text-4xl font-black text-slate-800 tracking-tighter mb-2 flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-sliders text-lg"></i></span>
                    Ajustes de Fase
                </h1>
                <p class="text-slate-500 font-medium">Configuración técnica de factores y deducciones.</p>
            </div>
            <a href="fases.php" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition-all flex items-center gap-2 shadow-sm italic"><i class="fas fa-arrow-left text-xs"></i> Volver</a>
        </div>

        <?php
        if(isset($_POST['edit_btn'])):
            $id = mysqli_real_escape_string($connection, $_POST['edit_id']);
            
            // Renombramos el recurso de consulta para evitar colisiones con los includes
            $query_fase = "SELECT * FROM fases WHERE id = '$id'";
            $res_fase = mysqli_query($connection, $query_fase);
            
            if(mysqli_num_rows($res_fase) > 0):
                // Renombramos $row a $row_fase para que los includes no lo machaquen
                while($row_fase = mysqli_fetch_assoc($res_fase)):
                    // DETECCIÓN AUTOMÁTICA: Si tiene id_figura, es una fase de figuras
                    $is_fase_figura = (!empty($row_fase['id_figura']) && $row_fase['id_figura'] > 0);
        ?>
            <form action="fases_code.php" method="POST" class="animate-fade-in space-y-10">
                <input type="hidden" name="edit_id" value="<?php echo $row_fase['id']?>">

                <!-- BLOQUE 1: IDENTIFICACIÓN -->
                <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-sm border border-slate-200 border-t-[6px] border-t-blue-600 relative overflow-hidden">
                    <h2 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3"><i class="fas fa-tag text-blue-600"></i> Clasificación</h2>
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1">Orden</label>
                            <input type="number" name="edit_orden" value="<?php echo $row_fase['orden']?>" required class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 text-sm font-bold shadow-inner">
                        </div>
                        <div class="md:col-span-5 space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1">Categoría</label>
                            <?php 
                            $id_categoria_actual = $row_fase['id_categoria'];
                            ob_start(); include('includes/categoria_select_option.php');
                            $select_cat = ob_get_clean();
                            $select_cat = preg_replace('/<label.*?>.*?<\/label>/i', '', $select_cat);
                            $select_cat = preg_replace('/class=[\'"].*?[\'"]/i', '', $select_cat);
                            echo str_replace('<select', '<select name="id_categoria" class="v3-select-fix"', $select_cat);
                            ?>
                        </div>
                        <div class="md:col-span-5 space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1"><?php echo $is_fase_figura ? 'Figura' : 'Modalidad'; ?></label>
                            <?php 
                            if($is_fase_figura) {
                                $id_figura_actual = $row_fase['id_figura'];
                                ob_start(); include('includes/figura_select_option.php');
                            } else {
                                $id_modalidad_actual = $row_fase['id_modalidad'];
                                ob_start(); include('includes/modalidad_select_option.php');
                            }
                            $select_mod = ob_get_clean();
                            $select_mod = preg_replace('/<label.*?>.*?<\/label>/i', '', $select_mod);
                            $select_mod = preg_replace('/class=[\'"].*?[\'"]/i', '', $select_mod);
                            $name_attr = $is_fase_figura ? 'name="id_figura"' : 'name="id_modalidad"';
                            echo str_replace('<select', '<select '.$name_attr.' class="v3-select-fix"', $select_mod);
                            ?>
                        </div>
                    </div>
                </div>

                <!-- BLOQUE 2: FACTORES DE PUNTUACIÓN (SOLO RUTINAS) -->
                <?php if(!$is_fase_figura): ?>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-200 border-l-[6px] border-l-emerald-500">
                        <h2 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3"><i class="fas fa-calculator text-emerald-600"></i> Factores de Multiplicación</h2>
                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[9px] font-black uppercase text-slate-400 px-1">ChoMu</label>
                                <input type="number" step="0.1" name="edit_f_chomu" value="<?php echo $row_fase['f_chomu']?>" class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-100 font-bold text-sm">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[9px] font-black uppercase text-slate-400 px-1">Performance</label>
                                <input type="number" step="0.1" name="edit_f_performance" value="<?php echo $row_fase['f_performance']?>" class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-100 font-bold text-sm">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[9px] font-black uppercase text-slate-400 px-1">Transitions</label>
                                <input type="number" step="0.1" name="edit_f_transitions" value="<?php echo $row_fase['f_transitions']?>" class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-100 font-bold text-sm">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[9px] font-black uppercase text-slate-400 px-1">Hybrid</label>
                                <input type="number" step="0.1" name="edit_f_hybrid" value="<?php echo $row_fase['f_hybrid']?>" class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-100 font-bold text-sm">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[9px] font-black uppercase text-slate-400 px-1">Acro</label>
                                <input type="number" step="0.1" name="edit_f_acro" value="<?php echo $row_fase['f_acro']?>" class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-100 font-bold text-sm">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[9px] font-black uppercase text-slate-400 px-1">TRE</label>
                                <input type="number" step="0.1" name="edit_f_tre" value="<?php echo $row_fase['f_tre']?>" class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-100 font-bold text-sm">
                            </div>
                        </div>
                    </div>

                    <!-- Penalizaciones -->
                    <div class="bg-slate-900 rounded-[2.5rem] p-8 shadow-xl border border-slate-800 text-white">
                        <h2 class="text-xl font-black mb-8 flex items-center gap-3"><i class="fas fa-exclamation-circle text-red-500"></i> Penalizaciones</h2>
                        <div class="space-y-6">
                            <div class="flex items-center justify-between p-4 bg-white/5 rounded-2xl border border-white/5">
                                <span class="text-xs font-black uppercase tracking-widest text-slate-400">Error XS</span>
                                <input type="number" step="0.1" name="edit_error_xs" value="<?php echo $row_fase['error_xs']?>" class="w-20 px-3 py-2 rounded-lg bg-white/10 border-0 text-center font-bold text-red-400">
                            </div>
                            <div class="flex items-center justify-between p-4 bg-white/5 rounded-2xl border border-white/5">
                                <span class="text-xs font-black uppercase tracking-widest text-slate-400">Error OB</span>
                                <input type="number" step="0.1" name="edit_error_ob" value="<?php echo $row_fase['error_ob']?>" class="w-20 px-3 py-2 rounded-lg bg-white/10 border-0 text-center font-bold text-red-400">
                            </div>
                            <div class="flex items-center justify-between p-4 bg-white/5 rounded-2xl border border-white/5">
                                <span class="text-xs font-black uppercase tracking-widest text-slate-400">Error XL</span>
                                <input type="number" step="0.1" name="edit_error_xl" value="<?php echo $row_fase['error_xl']?>" class="w-20 px-3 py-2 rounded-lg bg-white/10 border-0 text-center font-bold text-red-400">
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- BLOQUE 3: CONFIGURACIÓN AVANZADA -->
                <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-sm border border-slate-200">
                    <h2 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3"><i class="fas fa-cogs text-slate-400"></i> Parámetros Adicionales</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1">Elementos Coach Card</label>
                            <input type="number" name="edit_elementos_coach_card" value="<?php echo $row_fase['elementos_coach_card']?>" class="w-full px-5 py-3.5 rounded-2xl bg-slate-50 border border-slate-100 text-sm font-bold shadow-inner">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1">Hora Inicio Estimada</label>
                            <input type="text" name="edit_hora_inicio_estimada" value="<?php echo $row_fase['hora_inicio_estimada']?>" class="w-full px-5 py-3.5 rounded-2xl bg-slate-50 border border-slate-100 text-sm font-bold" placeholder="HH:MM">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1">Corte Clasificación</label>
                            <input type="number" name="edit_corte" value="<?php echo $row_fase['corte']?>" class="w-full px-5 py-3.5 rounded-2xl bg-slate-50 border border-slate-100 text-sm font-bold">
                        </div>
                    </div>

                    <!-- Toggles -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-10 pt-8 border-t border-slate-100">
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-between">
                            <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Técnico</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="edit_tecnico" value="si" <?php echo ($row_fase['tecnico'] == 'si') ? 'checked' : ''; ?> class="sr-only peer">
                                <div class="w-10 h-5 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-between">
                            <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Puntuada</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="edit_puntuada" value="si" <?php echo ($row_fase['puntuada'] == 'si') ? 'checked' : ''; ?> class="sr-only peer">
                                <div class="w-10 h-5 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                            </label>
                        </div>
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-between">
                            <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Memorial</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="edit_puntua_memorial" value="si" <?php echo ($row_fase['puntua_memorial'] == 'si') ? 'checked' : ''; ?> class="sr-only peer">
                                <div class="w-10 h-5 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-purple-600"></div>
                            </label>
                        </div>
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-between">
                            <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Sorteado</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="edit_sorteado" value="si" <?php echo ($row_fase['sorteado'] == 'si') ? 'checked' : ''; ?> class="sr-only peer">
                                <div class="w-10 h-5 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-amber-500"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="pt-6 flex justify-end gap-4">
                    <a href="fases.php" class="px-8 py-4 text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-colors">Cancelar</a>
                    <button type="submit" name="update_btn" class="px-12 py-4 bg-blue-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg shadow-blue-500/20 hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
                        <i class="fas fa-save"></i> Guardar Ajustes
                    </button>
                </div>

            </form>
        <?php 
                endwhile;
            endif;
        endif;
        ?>
    </div>
</main>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
