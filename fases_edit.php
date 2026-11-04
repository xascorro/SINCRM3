<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-surface">
    
    <?php include('includes/topbar.php'); ?>

    <!-- Contenido de la Página -->
    <div class="px-3 py-4 sm:p-6 md:p-10 max-w-5xl mx-auto w-full font-lexend text-primary">
        
        <!-- Header -->
        <div class="mb-6 sm:mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-6">
            <div>
                <h1 class="text-xl sm:text-2xl md:text-3xl font-black text-slate-800 tracking-tight flex items-center gap-2.5 sm:gap-3">
                    <span class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shadow-xs border border-blue-100">
                        <i class="fas fa-sliders text-sm sm:text-base"></i>
                    </span>
                    Ajustes de Fase
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 font-medium mt-1">Configuración técnica de factores, música y deducciones oficiales</p>
            </div>
            <a href="fases.php" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-700 text-xs sm:text-sm font-bold rounded-xl hover:bg-slate-50 active:scale-[0.98] transition-all shadow-xs self-start sm:self-auto">
                <i class="fas fa-arrow-left text-xs"></i> Volver a Fases
            </a>
        </div>

        <?php
        $id = isset($_POST['edit_id']) ? mysqli_real_escape_string($connection, $_POST['edit_id']) : (isset($_GET['id']) ? mysqli_real_escape_string($connection, $_GET['id']) : null);
        
        if($id):
            // Renombramos el recurso de consulta para evitar colisiones con los includes
            $query_fase = "SELECT * FROM fases WHERE id = '$id'";
            $res_fase = mysqli_query($connection, $query_fase);
            
            if($res_fase && mysqli_num_rows($res_fase) > 0):
                while($row_fase = mysqli_fetch_assoc($res_fase)):
                    // DETECCIÓN AUTOMÁTICA: Si tiene id_figura, es una fase de figuras
                    $is_fase_figura = (!empty($row_fase['id_figura']) && $row_fase['id_figura'] > 0);

                    // Consultar duración y factores oficiales por defecto del reglamento maestro
                    $default_regla = null;
                    if (!$is_fase_figura && !empty($row_fase['id_modalidad']) && !empty($row_fase['id_categoria'])) {
                        $q_def = "SELECT * FROM reglas_competicion WHERE id_categoria = '{$row_fase['id_categoria']}' AND id_modalidad = '{$row_fase['id_modalidad']}' LIMIT 1";
                        $r_def = mysqli_query($connection, $q_def);
                        if ($r_def && mysqli_num_rows($r_def) > 0) {
                            $default_regla = mysqli_fetch_assoc($r_def);
                        }
                    }
        ?>
            <form action="fases_code.php" method="POST" class="animate-fade-in space-y-6 sm:space-y-8">
                <input type="hidden" name="edit_id" value="<?php echo $row_fase['id']?>">

                <!-- BLOQUE 1: IDENTIFICACIÓN Y CLASIFICACIÓN -->
                <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-6 md:p-8 shadow-sm border border-slate-200/80 border-t-4 border-t-blue-600 relative z-20">
                    <h2 class="text-base sm:text-lg font-black text-slate-800 mb-4 sm:mb-6 flex items-center gap-2.5">
                        <i class="fas fa-tag text-blue-600"></i> Clasificación e Identificación
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-4 sm:gap-6">
                        <div class="sm:col-span-1 md:col-span-2 space-y-1.5">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Orden</label>
                            <input type="number" name="edit_orden" value="<?php echo $row_fase['orden']?>" required class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-xs sm:text-sm font-bold text-slate-800 outline-none focus:border-blue-500 focus:bg-white transition-all tabular-nums">
                        </div>
                        <div class="sm:col-span-1 md:col-span-4 space-y-1.5">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Categoría</label>
                            <?php 
                            $id_categoria_actual = $row_fase['id_categoria'];
                            ob_start(); include('includes/categoria_select_option.php');
                            $select_cat = ob_get_clean();
                            $select_cat = preg_replace('/<label.*?>.*?<\/label>/i', '', $select_cat);
                            $select_cat = preg_replace('/<select[^>]*>/i', '<select name="id_categoria" id="categoria" class="v3-select-fix">', $select_cat);
                            echo $select_cat;
                            ?>
                        </div>
                        <div class="sm:col-span-1 md:col-span-3 space-y-1.5">
                            <div class="flex items-center justify-between px-1">
                                <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest"><?php echo $is_fase_figura ? 'Figura' : 'Modalidad'; ?></label>
                                <?php if($is_fase_figura): ?>
                                    <span id="figuraCount" class="text-[9px] font-bold text-blue-600">Buscar</span>
                                <?php endif; ?>
                            </div>
                            <?php if($is_fase_figura): ?>
                                <input type="text" id="figuraFilter" placeholder="🔍 Escribe número o nombre (ej: 301, barracuda)..." 
                                    class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 border border-slate-200 text-slate-700 placeholder-slate-400 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all font-medium mb-1.5">
                            <?php endif; ?>
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
                            $name_attr = $is_fase_figura ? 'name="id_figura" id="figura"' : 'name="id_modalidad" id="modalidad"';
                            $select_mod = preg_replace('/<select[^>]*>/i', '<select '.$name_attr.' class="v3-select-fix">', $select_mod);
                            echo $select_mod;
                            ?>
                        </div>
                        <div class="sm:col-span-1 md:col-span-3 space-y-1.5">
                            <div class="flex items-center justify-between px-1">
                                <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Género</label>
                                <span id="editGeneroBadge" class="text-[10px] font-bold <?php echo ($row_fase['genero'] ?? 'femenino') === 'masculino' ? 'text-blue-600' : 'text-rose-500'; ?>">
                                    <?php echo ($row_fase['genero'] ?? 'femenino') === 'masculino' ? 'Masculino' : 'Femenino'; ?>
                                </span>
                            </div>
                            <div class="flex items-center p-1 bg-slate-50 border border-slate-200 rounded-xl">
                                <label class="flex-1 relative cursor-pointer text-center">
                                    <input type="radio" name="edit_genero" value="femenino" <?php echo ($row_fase['genero'] ?? 'femenino') !== 'masculino' ? 'checked' : ''; ?> class="peer sr-only" onchange="document.getElementById('editGeneroBadge').textContent='Femenino'; document.getElementById('editGeneroBadge').className='text-[10px] font-bold text-rose-500';">
                                    <span class="flex items-center justify-center gap-1.5 py-2.5 text-xs font-black rounded-lg transition-all peer-checked:bg-white peer-checked:text-rose-600 peer-checked:shadow-sm text-slate-400 hover:text-slate-600">
                                        <i class="fas fa-venus text-rose-500"></i> Fem
                                    </span>
                                </label>
                                <label class="flex-1 relative cursor-pointer text-center">
                                    <input type="radio" name="edit_genero" value="masculino" <?php echo ($row_fase['genero'] ?? 'femenino') === 'masculino' ? 'checked' : ''; ?> class="peer sr-only" onchange="document.getElementById('editGeneroBadge').textContent='Masculino'; document.getElementById('editGeneroBadge').className='text-[10px] font-bold text-blue-600';">
                                    <span class="flex items-center justify-center gap-1.5 py-2.5 text-xs font-black rounded-lg transition-all peer-checked:bg-white peer-checked:text-blue-600 peer-checked:shadow-sm text-slate-400 hover:text-slate-600">
                                        <i class="fas fa-mars text-blue-500"></i> Masc
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BLOQUE 2: MÚSICA Y PARÁMETROS TÉCNICOS (Mismo orden que la tabla general) -->
                <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-6 md:p-8 shadow-sm border border-slate-200/80 border-t-4 border-t-indigo-500">
                    <h2 class="text-base sm:text-lg font-black text-slate-800 mb-5 sm:mb-6 flex items-center gap-2.5">
                        <i class="fas fa-sliders text-indigo-500"></i> Música Oficial y Parámetros Técnicos
                    </h2>

                    <?php if(!$is_fase_figura): 
                        $fase_dur_str = (!empty($row_fase['duracion_musica'])) ? sprintf('%d:%02d', floor($row_fase['duracion_musica'] / 60), $row_fase['duracion_musica'] % 60) : '';
                        $regla_dur_str = ($default_regla && !empty($default_regla['duracion_musica'])) ? sprintf('%d:%02d min', floor($default_regla['duracion_musica'] / 60), $default_regla['duracion_musica'] % 60) : 'Sin definir';
                        $regla_margen = ($default_regla && isset($default_regla['margen_musica'])) ? $default_regla['margen_musica'] : 5;
                    ?>
                    <!-- 1. Duración Reglamentaria de Música -->
                    <div class="mb-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                            <h3 class="text-xs sm:text-sm font-black text-slate-700 uppercase tracking-wider flex items-center gap-2">
                                <i class="fas fa-music text-indigo-500"></i> Duración Reglamentaria de Música
                            </h3>
                            <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-3 py-1 rounded-full self-start sm:self-auto">
                                Regla General: <strong class="text-indigo-600"><?php echo $regla_dur_str; ?> (±<?php echo $regla_margen; ?>s)</strong>
                            </span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black uppercase text-slate-400 px-1">Tiempo Oficial de la Rutina (Personalizado)</label>
                                <input type="text" name="edit_duracion_musica" value="<?php echo $fase_dur_str; ?>" 
                                       placeholder="<?php echo ($default_regla && !empty($default_regla['duracion_musica'])) ? sprintf('%d:%02d', floor($default_regla['duracion_musica'] / 60), $default_regla['duracion_musica'] % 60) : 'ej: 2:20 o 140'; ?>" 
                                       class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-xs sm:text-sm font-bold text-slate-800 placeholder-slate-400 focus:bg-white focus:border-indigo-500 outline-none">
                                <p class="text-[10px] text-slate-400 px-1">Formato <strong>mm:ss</strong> o segundos. Déjalo en blanco para usar la regla general.</p>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black uppercase text-slate-400 px-1">Margen de Tolerancia (Segundos)</label>
                                <input type="number" name="edit_margen_musica" value="<?php echo $row_fase['margen_musica'] ?? ''; ?>" 
                                       placeholder="<?php echo $regla_margen; ?>" 
                                       class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-xs sm:text-sm font-bold text-slate-800 placeholder-slate-400 focus:bg-white focus:border-indigo-500 outline-none">
                                <p class="text-[10px] text-slate-400 px-1">Margen reglamentario permitido (por defecto ±5 segundos).</p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- 2. Parámetros Técnicos y Coach Card -->
                    <div class="<?php echo !$is_fase_figura ? 'pt-6 border-t border-slate-100' : ''; ?>">
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6">
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black uppercase text-indigo-600 px-1 tracking-widest flex items-center gap-1">
                                    <i class="fas fa-clipboard-check"></i> Elementos Coach Card
                                </label>
                                <input type="number" name="edit_elementos_coach_card" value="<?php echo $row_fase['elementos_coach_card']?>" class="w-full px-4 py-3 rounded-xl bg-indigo-50/50 border border-indigo-200 text-xs sm:text-sm font-black text-indigo-700 outline-none tabular-nums focus:bg-white focus:border-indigo-500">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Hora Inicio Estimada</label>
                                <input type="text" name="edit_hora_inicio_estimada" value="<?php echo $row_fase['hora_inicio_estimada']?>" class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-xs sm:text-sm font-bold text-slate-800 outline-none placeholder-slate-400 focus:bg-white focus:border-blue-500" placeholder="HH:MM">
                            </div>
                            <div class="space-y-1.5 sm:col-span-2 md:col-span-1">
                                <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Corte Clasificación</label>
                                <input type="number" name="edit_corte" value="<?php echo $row_fase['corte']?>" class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-xs sm:text-sm font-bold text-slate-800 outline-none tabular-nums focus:bg-white focus:border-blue-500">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BLOQUE 3: FACTORES DE PUNTUACIÓN Y DEDUCCIONES (Mismo orden WA: Acro, Hyb, TRE, ChoMu, Perf, Trans) -->
                <?php if(!$is_fase_figura): ?>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Factores de Multiplicación (Orden exacto de la tabla oficial) -->
                    <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-6 md:p-8 shadow-sm border border-slate-200/80 border-l-4 border-l-emerald-500 flex flex-col justify-between">
                        <div>
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 mb-5 sm:mb-6">
                                <h2 class="text-base sm:text-lg font-black text-slate-800 flex items-center gap-2">
                                    <i class="fas fa-calculator text-emerald-600"></i> Factores de Multiplicación
                                </h2>
                                <?php if($default_regla): ?>
                                    <button type="button" onclick="cargarValoresReglamento()" class="self-start sm:self-auto px-3 py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shadow-xs active:scale-[0.98]" title="Restablecer a los valores oficiales de World Aquatics">
                                        <i class="fas fa-rotate-left text-[10px]"></i> Cargar Oficiales
                                    </button>
                                <?php endif; ?>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-4">
                                <div class="space-y-1.5">
                                    <label class="text-[9px] font-black uppercase text-emerald-700 px-1">Acro</label>
                                    <input type="number" step="0.01" name="edit_f_acro" value="<?php echo $row_fase['f_acro']?>" class="w-full px-3 py-2.5 rounded-xl bg-emerald-50/50 border border-emerald-200 font-black text-xs sm:text-sm text-emerald-800 tabular-nums focus:border-emerald-500 focus:bg-white outline-none">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[9px] font-black uppercase text-emerald-700 px-1">Hybrid</label>
                                    <input type="number" step="0.01" name="edit_f_hybrid" value="<?php echo $row_fase['f_hybrid']?>" class="w-full px-3 py-2.5 rounded-xl bg-emerald-50/50 border border-emerald-200 font-black text-xs sm:text-sm text-emerald-800 tabular-nums focus:border-emerald-500 focus:bg-white outline-none">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[9px] font-black uppercase text-emerald-700 px-1">TRE</label>
                                    <input type="number" step="0.01" name="edit_f_tre" value="<?php echo $row_fase['f_tre']?>" class="w-full px-3 py-2.5 rounded-xl bg-emerald-50/50 border border-emerald-200 font-black text-xs sm:text-sm text-emerald-800 tabular-nums focus:border-emerald-500 focus:bg-white outline-none">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[9px] font-black uppercase text-slate-400 px-1">ChoMu</label>
                                    <input type="number" step="0.01" name="edit_f_chomu" value="<?php echo $row_fase['f_chomu']?>" class="w-full px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 font-bold text-xs sm:text-sm text-slate-800 tabular-nums focus:border-emerald-500 focus:bg-white outline-none">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[9px] font-black uppercase text-slate-400 px-1">Performance</label>
                                    <input type="number" step="0.01" name="edit_f_performance" value="<?php echo $row_fase['f_performance']?>" class="w-full px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 font-bold text-xs sm:text-sm text-slate-800 tabular-nums focus:border-emerald-500 focus:bg-white outline-none">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[9px] font-black uppercase text-slate-400 px-1">Transitions</label>
                                    <input type="number" step="0.01" name="edit_f_transitions" value="<?php echo $row_fase['f_transitions']?>" class="w-full px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 font-bold text-xs sm:text-sm text-slate-800 tabular-nums focus:border-emerald-500 focus:bg-white outline-none">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Deducciones de Sincro (XS, OB, XL) -->
                    <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-6 md:p-8 shadow-sm border border-slate-200/80 border-l-4 border-l-rose-500 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-5 sm:mb-6">
                                <h2 class="text-base sm:text-lg font-black text-slate-800 flex items-center gap-2">
                                    <i class="fas fa-triangle-exclamation text-rose-500"></i> Deducciones de Sincro
                                </h2>
                                <span class="text-[10px] font-black uppercase text-rose-600 bg-rose-50 px-2.5 py-1 rounded-full border border-rose-100">
                                    Penalizaciones
                                </span>
                            </div>
                            <div class="space-y-3 sm:space-y-3.5">
                                <div class="flex items-center justify-between p-3.5 sm:p-4 bg-rose-50/40 rounded-xl sm:rounded-2xl border border-rose-100 transition-all">
                                    <div>
                                        <span class="text-xs font-black uppercase tracking-wider text-rose-900 block">Error XS (Minor)</span>
                                        <span class="text-[10px] text-slate-500 font-medium">Desviaciones leves de sincronización</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <input type="number" step="0.01" name="edit_error_xs" value="<?php echo $row_fase['error_xs']?>" class="w-20 px-3 py-2 rounded-xl bg-white border border-rose-200 font-mono font-black text-rose-700 text-sm text-center tabular-nums outline-none focus:border-rose-500 focus:ring-2 focus:ring-rose-200 shadow-xs">
                                    </div>
                                </div>
                                <div class="flex items-center justify-between p-3.5 sm:p-4 bg-rose-50/40 rounded-xl sm:rounded-2xl border border-rose-100 transition-all">
                                    <div>
                                        <span class="text-xs font-black uppercase tracking-wider text-rose-900 block">Error OB (Obvious)</span>
                                        <span class="text-[10px] text-slate-500 font-medium">Falta de sincronía evidente</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <input type="number" step="0.01" name="edit_error_ob" value="<?php echo $row_fase['error_ob']?>" class="w-20 px-3 py-2 rounded-xl bg-white border border-rose-200 font-mono font-black text-rose-700 text-sm text-center tabular-nums outline-none focus:border-rose-500 focus:ring-2 focus:ring-rose-200 shadow-xs">
                                    </div>
                                </div>
                                <div class="flex items-center justify-between p-3.5 sm:p-4 bg-rose-50/40 rounded-xl sm:rounded-2xl border border-rose-100 transition-all">
                                    <div>
                                        <span class="text-xs font-black uppercase tracking-wider text-rose-900 block">Error XL (Major)</span>
                                        <span class="text-[10px] text-slate-500 font-medium">Desfase grave o elemento fallido</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <input type="number" step="0.01" name="edit_error_xl" value="<?php echo $row_fase['error_xl']?>" class="w-20 px-3 py-2 rounded-xl bg-white border border-rose-200 font-mono font-black text-rose-700 text-sm text-center tabular-nums outline-none focus:border-rose-500 focus:ring-2 focus:ring-rose-200 shadow-xs">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- BLOQUE 4: OPCIONES Y ESTADOS DE LA FASE (TOGGLES) -->
                <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-6 md:p-8 shadow-sm border border-slate-200/80">
                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest block mb-4">Opciones y Estados de la Fase</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2.5 sm:gap-3">
                        <!-- Toggle AQUA -->
                        <label class="p-3 sm:p-3.5 bg-slate-50/70 hover:bg-slate-100/70 rounded-xl sm:rounded-2xl border border-slate-200/80 flex items-center justify-between cursor-pointer transition-colors active:scale-[0.98]">
                            <span class="text-[10px] sm:text-[11px] font-black uppercase text-slate-700 tracking-tight">Sistema AQUA</span>
                            <div class="relative inline-flex items-center">
                                <input type="checkbox" name="edit_aqua" value="si" <?php echo ($row_fase['obsoleto'] != 'si') ? 'checked' : ''; ?> class="sr-only peer">
                                <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                            </div>
                        </label>

                        <!-- Toggle Técnico -->
                        <label class="p-3 sm:p-3.5 bg-slate-50/70 hover:bg-slate-100/70 rounded-xl sm:rounded-2xl border border-slate-200/80 flex items-center justify-between cursor-pointer transition-colors active:scale-[0.98]">
                            <span class="text-[10px] sm:text-[11px] font-black uppercase text-slate-700 tracking-tight">Técnico</span>
                            <div class="relative inline-flex items-center">
                                <input type="checkbox" name="edit_tecnico" value="si" <?php echo ($row_fase['tecnico'] == 'si') ? 'checked' : ''; ?> class="sr-only peer">
                                <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                            </div>
                        </label>

                        <!-- Toggle Puntuada -->
                        <label class="p-3 sm:p-3.5 bg-slate-50/70 hover:bg-slate-100/70 rounded-xl sm:rounded-2xl border border-slate-200/80 flex items-center justify-between cursor-pointer transition-colors active:scale-[0.98]">
                            <span class="text-[10px] sm:text-[11px] font-black uppercase text-slate-700 tracking-tight">Puntuada</span>
                            <div class="relative inline-flex items-center">
                                <input type="checkbox" name="edit_puntuada" value="si" <?php echo ($row_fase['puntuada'] == 'si') ? 'checked' : ''; ?> class="sr-only peer">
                                <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                            </div>
                        </label>

                        <!-- Toggle Memorial -->
                        <label class="p-3 sm:p-3.5 bg-slate-50/70 hover:bg-slate-100/70 rounded-xl sm:rounded-2xl border border-slate-200/80 flex items-center justify-between cursor-pointer transition-colors active:scale-[0.98]">
                            <span class="text-[10px] sm:text-[11px] font-black uppercase text-slate-700 tracking-tight">Memorial</span>
                            <div class="relative inline-flex items-center">
                                <input type="checkbox" name="edit_puntua_memorial" value="si" <?php echo ($row_fase['puntua_memorial'] == 'si') ? 'checked' : ''; ?> class="sr-only peer">
                                <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-purple-600"></div>
                            </div>
                        </label>

                        <!-- Toggle Sorteado -->
                        <label class="col-span-2 sm:col-span-1 p-3 sm:p-3.5 bg-slate-50/70 hover:bg-slate-100/70 rounded-xl sm:rounded-2xl border border-slate-200/80 flex items-center justify-between cursor-pointer transition-colors active:scale-[0.98]">
                            <span class="text-[10px] sm:text-[11px] font-black uppercase text-slate-700 tracking-tight">Sorteado</span>
                            <div class="relative inline-flex items-center">
                                <input type="checkbox" name="edit_sorteado" value="si" <?php echo ($row_fase['sorteado'] == 'si') ? 'checked' : ''; ?> class="sr-only peer">
                                <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-amber-500"></div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Botones de Acción (Espaciados para Bottom Nav móvil) -->
                <div class="pt-4 sm:pt-6 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 pb-24 sm:pb-8">
                    <a href="fases.php" class="px-6 py-3.5 text-center text-xs font-black uppercase tracking-wider text-slate-500 hover:text-slate-800 bg-white border border-slate-200 rounded-xl transition-all active:scale-[0.98]">
                        Cancelar
                    </a>
                    <button type="submit" name="update_btn" class="px-8 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-black uppercase text-xs tracking-wider rounded-xl shadow-lg shadow-blue-500/20 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-save"></i> Guardar Ajustes
                    </button>
                </div>

            </form>
        <?php 
                endwhile;
            else:
        ?>
            <div class="bg-white rounded-2xl p-8 text-center text-slate-400 font-bold border border-slate-200 shadow-sm">
                <i class="fas fa-exclamation-circle text-3xl mb-3 text-amber-500"></i>
                <p>No se encontró la fase solicitada.</p>
                <a href="fases.php" class="inline-block mt-4 px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold text-xs">Volver a Fases</a>
            </div>
        <?php
            endif;
        else:
        ?>
            <div class="bg-white rounded-2xl p-8 text-center text-slate-400 font-bold border border-slate-200 shadow-sm">
                <i class="fas fa-triangle-exclamation text-3xl mb-3 text-rose-500"></i>
                <p>No se ha especificado ninguna fase para editar.</p>
                <a href="fases.php" class="inline-block mt-4 px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold text-xs">Volver a Fases</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filter = document.getElementById('figuraFilter');
    const select = document.getElementById('figura');
    const count = document.getElementById('figuraCount');
    
    if (select) {
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
                if (o.selected) opt.selected = true;
                select.appendChild(opt);
            });
            
            if (count) {
                count.textContent = matched.length + ' de ' + originalOptions.length;
            }
        });
    }
});

function cargarValoresReglamento() {
    const def = <?php echo json_encode($default_regla ?: []); ?>;
    if (!def || Object.keys(def).length === 0) return;

    // Factores en orden oficial de la tabla
    if (document.getElementsByName('edit_f_acro')[0]) document.getElementsByName('edit_f_acro')[0].value = def.f_acro || '1.0';
    if (document.getElementsByName('edit_f_hybrid')[0]) document.getElementsByName('edit_f_hybrid')[0].value = def.f_hybrid || '1.0';
    if (document.getElementsByName('edit_f_tre')[0]) document.getElementsByName('edit_f_tre')[0].value = def.f_tre || '1.0';
    if (document.getElementsByName('edit_f_chomu')[0]) document.getElementsByName('edit_f_chomu')[0].value = def.f_chomu || '1.0';
    if (document.getElementsByName('edit_f_performance')[0]) document.getElementsByName('edit_f_performance')[0].value = def.f_performance || '1.0';
    if (document.getElementsByName('edit_f_transitions')[0]) document.getElementsByName('edit_f_transitions')[0].value = def.f_transitions || '1.0';

    // Deducciones de sincronización
    if (document.getElementsByName('edit_error_xs')[0]) document.getElementsByName('edit_error_xs')[0].value = def.error_xs || '-0.1';
    if (document.getElementsByName('edit_error_ob')[0]) document.getElementsByName('edit_error_ob')[0].value = def.error_ob || '-0.5';
    if (document.getElementsByName('edit_error_xl')[0]) document.getElementsByName('edit_error_xl')[0].value = def.error_xl || '-3.0';

    // Elementos Coach Card
    if (def.elementos_coach_card && document.getElementsByName('edit_elementos_coach_card')[0]) {
        document.getElementsByName('edit_elementos_coach_card')[0].value = def.elementos_coach_card;
    }

    // Duración y margen
    if (def.duracion_musica && document.getElementsByName('edit_duracion_musica')[0]) {
        const m = Math.floor(def.duracion_musica / 60);
        const s = def.duracion_musica % 60;
        document.getElementsByName('edit_duracion_musica')[0].value = m + ':' + (s < 10 ? '0' : '') + s;
    }
    if (def.margen_musica && document.getElementsByName('edit_margen_musica')[0]) {
        document.getElementsByName('edit_margen_musica')[0].value = def.margen_musica;
    }
}
</script>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
