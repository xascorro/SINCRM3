<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');

// Consultar todas las reglas con categoría y modalidad
$query_reglas = "
    SELECT r.*, 
           c.nombre as categoria_nombre, 
           c.nombre_corto as cat_corto, 
           m.nombre as modalidad_nombre, 
           m.nombre_corto as mod_corto
    FROM reglas_competicion r
    LEFT JOIN categorias c ON r.id_categoria = c.id
    LEFT JOIN modalidades m ON r.id_modalidad = m.id
    ORDER BY c.orden ASC, c.id ASC, m.id ASC
";
$res_reglas = mysqli_query($connection, $query_reglas);
$reglas_list = [];
$total_con_musica = 0;
$cat_set = [];
$mod_set = [];

if($res_reglas && mysqli_num_rows($res_reglas) > 0) {
    while($row = mysqli_fetch_assoc($res_reglas)) {
        if(!empty($row['duracion_musica']) && $row['duracion_musica'] > 0) {
            $total_con_musica++;
        }
        if(!empty($row['id_categoria'])) $cat_set[$row['id_categoria']] = true;
        if(!empty($row['id_modalidad'])) $mod_set[$row['id_modalidad']] = true;
        $reglas_list[] = $row;
    }
}

// Consultar opciones para el formulario de nueva regla
$q_cats = "SELECT id, nombre, nombre_corto FROM categorias WHERE activo = 1 ORDER BY orden ASC, nombre ASC";
$res_cats = mysqli_query($connection, $q_cats);

$q_mods = "SELECT id, nombre, nombre_corto FROM modalidades WHERE id != 8 AND id != 11 ORDER BY id ASC";
$res_mods = mysqli_query($connection, $q_mods);
?>

<main class="flex-1 flex flex-col min-w-0 bg-surface">
    <?php include('includes/topbar.php'); ?>

    <div class="px-3 py-4 sm:p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend text-primary">
        
        <!-- Header de Sección -->
        <div class="mb-6 sm:mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4 sm:gap-6">
            <div>
                <h1 class="text-2xl sm:text-4xl font-black text-slate-800 tracking-tight mb-1 sm:mb-2 flex items-center gap-2.5 sm:gap-3">
                    <span class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shadow-sm border border-indigo-100">
                        <i class="fas fa-stopwatch text-base sm:text-lg"></i>
                    </span>
                    Reglamento y Tiempos de Música
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 font-medium">Gestión oficial de duraciones de audio, factores de puntuación y límites técnicos (World Aquatics / FNRM).</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2.5 sm:gap-4 items-stretch sm:items-center">
                <!-- Buscador Dinámico -->
                <div class="relative w-full sm:w-80">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </span>
                    <input type="text" id="reglasSearchInput" placeholder="Buscar por categoría, prueba..." 
                           class="w-full pl-9 pr-3.5 py-2.5 sm:py-3 bg-white border border-slate-200 rounded-xl sm:rounded-2xl text-xs font-bold text-slate-700 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all outline-none shadow-sm"
                           onkeyup="filterReglasTable()">
                </div>
                <button onclick="toggleAddReglaPanel()" class="px-5 sm:px-6 py-2.5 sm:py-3 bg-indigo-600 text-white font-black uppercase text-xs tracking-wider rounded-xl sm:rounded-2xl shadow-lg shadow-indigo-500/20 hover:scale-105 active:scale-[0.98] transition-all flex items-center justify-center gap-2 shrink-0">
                    <i class="fas fa-plus text-xs"></i> Nueva Regla
                </button>
            </div>
        </div>

        <!-- Panel Añadir Regla (Colapsable) -->
        <div id="addReglaPanel" class="hidden mb-6 sm:mb-10 animate-fade-in-down">
            <div class="bg-white rounded-2xl sm:rounded-[2.5rem] p-5 sm:p-8 shadow-xl border border-indigo-100 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/5 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                <h2 class="text-base sm:text-xl font-black text-slate-800 mb-4 sm:mb-6 flex items-center gap-2.5 sm:gap-3">
                    <i class="fas fa-plus-circle text-indigo-600"></i> Configurar Nueva Ficha Reglamentaria
                </h2>
                <form action="reglas_competicion_code.php" method="POST" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-4 sm:gap-6">
                    <div class="sm:col-span-1 md:col-span-6 space-y-1.5">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Categoría</label>
                        <select name="id_categoria" required class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 font-bold text-xs sm:text-sm text-slate-700 outline-none focus:border-indigo-500">
                            <option value="">Selecciona Categoría...</option>
                            <?php if($res_cats): mysqli_data_seek($res_cats, 0); while($c = mysqli_fetch_assoc($res_cats)): ?>
                                <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['nombre']); ?></option>
                            <?php endwhile; endif; ?>
                        </select>
                    </div>
                    <div class="sm:col-span-1 md:col-span-6 space-y-1.5">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Modalidad</label>
                        <select name="id_modalidad" required class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 font-bold text-xs sm:text-sm text-slate-700 outline-none focus:border-indigo-500">
                            <option value="">Selecciona Modalidad...</option>
                            <?php if($res_mods): mysqli_data_seek($res_mods, 0); while($m = mysqli_fetch_assoc($res_mods)): ?>
                                <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['nombre']); ?></option>
                            <?php endwhile; endif; ?>
                        </select>
                    </div>

                    <!-- Tiempos Oficiales -->
                    <div class="sm:col-span-1 md:col-span-3 space-y-1.5">
                        <label class="text-[10px] font-black uppercase text-indigo-600 px-1 tracking-widest flex items-center gap-1">
                            <i class="fas fa-clock"></i> Duración Música
                        </label>
                        <input type="text" name="duracion_musica" placeholder="ej: 2:20 o 140" class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 font-bold text-xs sm:text-sm text-slate-700 outline-none focus:border-indigo-500">
                        <span class="text-[9px] text-slate-400 px-1">Formato mm:ss o seg.</span>
                    </div>
                    <div class="sm:col-span-1 md:col-span-3 space-y-1.5">
                        <label class="text-[10px] font-black uppercase text-indigo-600 px-1 tracking-widest flex items-center gap-1">
                            <i class="fas fa-arrows-left-right-to-line"></i> Margen (±seg)
                        </label>
                        <input type="number" name="margen_musica" value="5" class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 font-bold text-xs sm:text-sm text-slate-700 outline-none focus:border-indigo-500">
                        <span class="text-[9px] text-slate-400 px-1">Tolerancia oficial (±5s)</span>
                    </div>

                    <!-- Límites Apéndice III -->
                    <div class="sm:col-span-1 md:col-span-2 space-y-1.5">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Max Acros</label>
                        <input type="number" name="max_acrobacias" id="new_max_acrobacias" value="0" min="0" oninput="calcNewCoachCard()" class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 font-bold text-xs sm:text-sm text-slate-700 outline-none">
                    </div>
                    <div class="sm:col-span-1 md:col-span-2 space-y-1.5">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Max Híbridos</label>
                        <input type="number" name="max_hibridos" id="new_max_hibridos" value="0" min="0" oninput="calcNewCoachCard()" class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 font-bold text-xs sm:text-sm text-slate-700 outline-none">
                    </div>
                    <div class="sm:col-span-1 md:col-span-2 space-y-1.5">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Max TRE</label>
                        <input type="number" name="max_tre" id="new_max_tre" value="0" min="0" oninput="calcNewCoachCard()" class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 font-bold text-xs sm:text-sm text-slate-700 outline-none">
                    </div>

                    <div class="sm:col-span-1 md:col-span-2 space-y-1.5">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Max Apnea (seg)</label>
                        <input type="number" step="0.01" name="max_apnea" placeholder="ej: 18.99" class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 font-bold text-xs sm:text-sm text-slate-700 outline-none">
                    </div>
                    <div class="sm:col-span-1 md:col-span-2 space-y-1.5">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Híbrido Creativo</label>
                        <select name="req_hibrido_creativo" id="new_req_hibrido_creativo" onchange="calcNewCoachCard()" class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 font-bold text-xs sm:text-sm text-slate-700 outline-none">
                            <option value="0">No Requerido</option>
                            <option value="1">Sí (Obligatorio)</option>
                        </select>
                    </div>
                    <div class="sm:col-span-1 md:col-span-2 space-y-1.5">
                        <label class="text-[10px] font-black uppercase text-indigo-600 px-1 tracking-widest flex items-center gap-1">
                            <i class="fas fa-clipboard-check"></i> Elementos CC
                        </label>
                        <input type="number" name="elementos_coach_card" id="new_elementos_coach_card" value="0" min="0" class="w-full px-4 py-3 rounded-xl bg-indigo-50/50 border border-indigo-200 font-black text-xs sm:text-sm text-indigo-700 outline-none">
                    </div>

                    <!-- Factores de Puntuación (Mismo orden que la tabla oficial: Acro, Hyb, TRE, ChMu, Perf, Trans) -->
                    <div class="sm:col-span-2 md:col-span-12 pt-3 border-t border-slate-100">
                        <p class="text-xs font-black uppercase tracking-wider text-slate-600 mb-3 flex items-center gap-1.5">
                            <i class="fas fa-calculator text-emerald-500"></i> Factores Oficiales de Puntuación
                        </p>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3">
                            <div class="space-y-1">
                                <label class="text-[9px] font-black uppercase text-emerald-700 px-1">Acro</label>
                                <input type="number" step="0.01" name="f_acro" value="1.00" class="w-full px-3 py-2 rounded-xl bg-emerald-50/50 border border-emerald-200 font-black text-xs text-emerald-800">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[9px] font-black uppercase text-emerald-700 px-1">Hybrid</label>
                                <input type="number" step="0.01" name="f_hybrid" value="1.00" class="w-full px-3 py-2 rounded-xl bg-emerald-50/50 border border-emerald-200 font-black text-xs text-emerald-800">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[9px] font-black uppercase text-emerald-700 px-1">TRE</label>
                                <input type="number" step="0.01" name="f_tre" value="1.00" class="w-full px-3 py-2 rounded-xl bg-emerald-50/50 border border-emerald-200 font-black text-xs text-emerald-800">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[9px] font-black uppercase text-slate-400 px-1">ChoMu</label>
                                <input type="number" step="0.01" name="f_chomu" value="1.00" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 font-bold text-xs">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[9px] font-black uppercase text-slate-400 px-1">Performance</label>
                                <input type="number" step="0.01" name="f_performance" value="1.00" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 font-bold text-xs">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[9px] font-black uppercase text-slate-400 px-1">Transitions</label>
                                <input type="number" step="0.01" name="f_transitions" value="1.00" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 font-bold text-xs">
                            </div>
                        </div>
                    </div>

                    <!-- Deducciones -->
                    <div class="sm:col-span-2 md:col-span-12 pt-2 border-t border-slate-100">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="space-y-1">
                                <label class="text-[9px] font-black uppercase text-rose-500 px-1">Error XS (Minor)</label>
                                <input type="number" step="0.01" name="error_xs" value="-0.10" class="w-full px-3 py-2 rounded-xl bg-rose-50/50 border border-rose-200 font-bold text-xs text-rose-700">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[9px] font-black uppercase text-rose-500 px-1">Error OB (Obvious)</label>
                                <input type="number" step="0.01" name="error_ob" value="-0.50" class="w-full px-3 py-2 rounded-xl bg-rose-50/50 border border-rose-200 font-bold text-xs text-rose-700">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[9px] font-black uppercase text-rose-500 px-1">Error XL (Major)</label>
                                <input type="number" step="0.01" name="error_xl" value="-3.00" class="w-full px-3 py-2 rounded-xl bg-rose-50/50 border border-rose-200 font-bold text-xs text-rose-700">
                            </div>
                        </div>
                    </div>

                    <div class="sm:col-span-2 md:col-span-12 pt-4 border-t border-slate-100 flex justify-end gap-3">
                        <button type="button" onclick="toggleAddReglaPanel()" class="px-5 py-2.5 text-xs font-black uppercase text-slate-400 hover:text-slate-600 transition-colors">Cancelar</button>
                        <button type="submit" name="save_btn" class="px-8 py-3 bg-indigo-600 text-white font-black uppercase text-xs tracking-wider rounded-xl shadow-lg hover:bg-indigo-700 active:scale-[0.98] transition-all">
                            Guardar Regla
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Alertas -->
        <?php include('includes/alertas_v4.php'); ?>

        <!-- KPIs Resumen -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-6 mb-6 sm:mb-10">
            <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-[2rem] shadow-sm border border-slate-200/80 border-l-4 border-l-indigo-500">
                <p class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 tracking-wider mb-1">Reglas Activas</p>
                <h3 class="text-lg sm:text-2xl font-black text-slate-800 tabular-nums"><?php echo count($reglas_list); ?></h3>
                <p class="text-[9px] text-slate-400 font-medium mt-1">Combinaciones oficiales</p>
            </div>
            <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-[2rem] shadow-sm border border-slate-200/80 border-l-4 border-l-purple-500">
                <p class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 tracking-wider mb-1">Tiempos de Música</p>
                <h3 class="text-lg sm:text-2xl font-black text-purple-600 tabular-nums"><?php echo $total_con_musica; ?></h3>
                <p class="text-[9px] text-slate-400 font-medium mt-1">Con duración oficial asignada</p>
            </div>
            <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-[2rem] shadow-sm border border-slate-200/80 border-l-4 border-l-blue-500">
                <p class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 tracking-wider mb-1">Categorías</p>
                <h3 class="text-lg sm:text-2xl font-black text-slate-800 tabular-nums"><?php echo count($cat_set); ?></h3>
                <p class="text-[9px] text-slate-400 font-medium mt-1">Niveles con normativa</p>
            </div>
            <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-[2rem] shadow-sm border border-slate-200/80 border-l-4 border-l-emerald-500">
                <p class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 tracking-wider mb-1">Modalidades</p>
                <h3 class="text-lg sm:text-2xl font-black text-slate-800 tabular-nums"><?php echo count($mod_set); ?></h3>
                <p class="text-[9px] text-slate-400 font-medium mt-1">Solos, Dúos, Equipos, Acro</p>
            </div>
        </div>

        <!-- Tabla de Reglas (Desktop) -->
        <div class="hidden lg:block bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden mb-10">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" id="reglasTable">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/75 text-[10px] font-black uppercase tracking-wider text-slate-400">
                            <th class="py-4 px-5">ID</th>
                            <th class="py-4 px-5">Categoría</th>
                            <th class="py-4 px-5">Modalidad</th>
                            <th class="py-4 px-5 text-center text-indigo-600 bg-indigo-50/40">Duración Música</th>
                            <th class="py-4 px-5 text-center">Límites Ap. III</th>
                            <th class="py-4 px-5 text-center text-emerald-700 bg-emerald-50/30">Factores Nota</th>
                            <th class="py-4 px-5 text-center text-rose-700 bg-rose-50/30">Sincro (XS/OB/XL)</th>
                            <th class="py-4 px-5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs font-bold text-slate-600">
                        <?php if(!empty($reglas_list)): foreach($reglas_list as $row): 
                            $dur_sec = (int)($row['duracion_musica'] ?? 0);
                            $dur_str = ($dur_sec > 0) ? sprintf('%d:%02d min', floor($dur_sec / 60), $dur_sec % 60) : '<span class="text-slate-300 italic font-normal">Sin asignar</span>';
                            $margen_str = ($row['margen_musica'] !== null) ? '&plusmn;' . $row['margen_musica'] . 's' : '&plusmn;5s';
                            $search_haystack = strtolower(($row['categoria_nombre'] ?? '') . ' ' . ($row['modalidad_nombre'] ?? ''));
                        ?>
                            <tr class="hover:bg-slate-50/60 transition-colors regla-row" data-search="<?php echo htmlspecialchars($search_haystack); ?>">
                                <td class="py-4 px-5 font-mono text-[11px] text-slate-400 tabular-nums">#<?php echo $row['id_regla']; ?></td>
                                <td class="py-4 px-5">
                                    <span class="px-2.5 py-1 bg-slate-100 text-slate-700 rounded-lg text-[11px] font-black uppercase tracking-tight">
                                        <?php echo htmlspecialchars($row['categoria_nombre'] ?: 'Cat #'.$row['id_categoria']); ?>
                                    </span>
                                </td>
                                <td class="py-4 px-5 font-black text-slate-800">
                                    <?php echo htmlspecialchars($row['modalidad_nombre'] ?: 'Mod #'.$row['id_modalidad']); ?>
                                </td>
                                
                                <!-- Duración Música -->
                                <td class="py-4 px-5 text-center bg-indigo-50/20">
                                    <?php if($dur_sec > 0): ?>
                                        <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-indigo-100/70 text-indigo-700 rounded-full font-black text-xs tabular-nums shadow-xs">
                                            <i class="fas fa-music text-[10px]"></i> <?php echo $dur_str; ?> <span class="text-[10px] opacity-75 font-bold">(<?php echo $margen_str; ?>)</span>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-slate-300 text-[11px] italic">No fijado</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Límites Técnicos (Orden: Acros, Híbridos, TRE, Creativo, CC) -->
                                <td class="py-4 px-5 text-center">
                                    <div class="flex items-center justify-center gap-1 text-[11px] tabular-nums font-bold">
                                        <span class="px-1.5 py-0.5 bg-rose-50 text-rose-700 rounded" title="Max Acros">A: <?php echo $row['max_acrobacias']; ?></span>
                                        <span class="px-1.5 py-0.5 bg-blue-50 text-blue-700 rounded" title="Max Híbridos">H: <?php echo $row['max_hibridos']; ?></span>
                                        <span class="px-1.5 py-0.5 bg-amber-50 text-amber-700 rounded" title="Max TRE">T: <?php echo $row['max_tre']; ?></span>
                                        <?php if($row['req_hibrido_creativo'] == 1): ?>
                                            <span class="px-1 py-0.5 bg-emerald-50 text-emerald-700 rounded text-[9px] font-black" title="Híbrido Creativo Requerido">✓Cr</span>
                                        <?php endif; ?>
                                        <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 rounded font-black border border-indigo-100" title="Elementos Coach Card (Total)">CC: <?php echo $row['elementos_coach_card']; ?></span>
                                    </div>
                                </td>

                                <!-- Factores de Nota (Orden oficial WA: Acro, Hyb, TRE, ChoMu, Perf, Trans) -->
                                <td class="py-4 px-5 text-center bg-emerald-50/15">
                                    <div class="flex items-center justify-center gap-1.5 text-[11px] font-mono tabular-nums">
                                        <span class="px-1.5 py-0.5 bg-emerald-50 text-emerald-800 rounded font-black" title="Acro">Ac: <?php echo number_format($row['f_acro'], 1); ?></span>
                                        <span class="px-1.5 py-0.5 bg-emerald-50 text-emerald-800 rounded font-black" title="Hybrid">Hy: <?php echo number_format($row['f_hybrid'], 1); ?></span>
                                        <span class="px-1.5 py-0.5 bg-emerald-50 text-emerald-800 rounded font-black" title="TRE">TR: <?php echo number_format($row['f_tre'], 1); ?></span>
                                        <span class="px-1.5 py-0.5 bg-white border border-slate-200 rounded text-slate-700" title="Choreography & Musicality">CM: <strong><?php echo number_format($row['f_chomu'], 1); ?></strong></span>
                                        <span class="px-1.5 py-0.5 bg-white border border-slate-200 rounded text-slate-700" title="Performance">P: <strong><?php echo number_format($row['f_performance'], 1); ?></strong></span>
                                        <span class="px-1.5 py-0.5 bg-white border border-slate-200 rounded text-slate-700" title="Transitions">Tr: <strong><?php echo number_format($row['f_transitions'], 1); ?></strong></span>
                                    </div>
                                </td>

                                <!-- Sincronización -->
                                <td class="py-4 px-5 text-center bg-rose-50/15 text-[10px] font-mono tabular-nums text-rose-700">
                                    <span class="font-bold"><?php echo $row['error_xs']; ?></span> / <span class="font-bold"><?php echo $row['error_ob']; ?></span> / <span class="font-bold"><?php echo $row['error_xl']; ?></span>
                                </td>

                                <!-- Acciones -->
                                <td class="py-4 px-5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button onclick='openEditModal(<?php echo json_encode($row); ?>)' 
                                                class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all flex items-center justify-center shadow-xs" title="Editar Ficha Técnica">
                                            <i class="fas fa-pencil-alt text-xs"></i>
                                        </button>
                                        <form action="reglas_competicion_code.php" method="POST" onsubmit="return confirm('¿Eliminar esta regla técnica?');" class="inline">
                                            <input type="hidden" name="delete_id" value="<?php echo $row['id_regla']; ?>">
                                            <button type="submit" name="delete_btn" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-all flex items-center justify-center shadow-xs" title="Eliminar">
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr>
                                <td colspan="8" class="py-8 text-center text-slate-400 font-bold">No hay reglas configuradas actualmente.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tarjetas en Móvil -->
        <div class="block lg:hidden space-y-3 mb-10" id="reglasMobileList">
            <?php if(!empty($reglas_list)): foreach($reglas_list as $row): 
                $dur_sec = (int)($row['duracion_musica'] ?? 0);
                $dur_str = ($dur_sec > 0) ? sprintf('%d:%02d min', floor($dur_sec / 60), $dur_sec % 60) : 'Sin asignar';
                $margen_str = ($row['margen_musica'] !== null) ? '±' . $row['margen_musica'] . 's' : '±5s';
                $search_haystack = strtolower(($row['categoria_nombre'] ?? '') . ' ' . ($row['modalidad_nombre'] ?? ''));
            ?>
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80 regla-card" data-search="<?php echo htmlspecialchars($search_haystack); ?>">
                    <div class="flex items-start justify-between gap-3 mb-2.5">
                        <div>
                            <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[10px] font-black uppercase">
                                <?php echo htmlspecialchars($row['categoria_nombre']); ?>
                            </span>
                            <h3 class="text-sm font-black text-slate-800 mt-1"><?php echo htmlspecialchars($row['modalidad_nombre']); ?></h3>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <button onclick='openEditModal(<?php echo json_encode($row); ?>)' class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center active:scale-95">
                                <i class="fas fa-pencil-alt text-xs"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Fila de Duración -->
                    <div class="p-2.5 bg-indigo-50/50 rounded-xl border border-indigo-100/60 flex items-center justify-between mb-3">
                        <span class="text-[10px] font-black text-indigo-800 uppercase flex items-center gap-1.5">
                            <i class="fas fa-music text-indigo-500"></i> Duración Oficial
                        </span>
                        <div class="text-right">
                            <span class="text-xs font-black text-indigo-700 tabular-nums"><?php echo $dur_str; ?></span>
                            <span class="text-[10px] text-indigo-500 font-bold ml-1">(<?php echo $margen_str; ?>)</span>
                        </div>
                    </div>

                    <!-- Métricas Técnicas (Orden: Acros, Híbridos, TRE, CC) -->
                    <div class="grid grid-cols-4 gap-1.5 text-center text-[10px] font-bold mb-2">
                        <div class="p-2 bg-slate-50 rounded-lg">
                            <span class="block text-[8px] uppercase text-slate-400 font-black">Acros</span>
                            <span class="text-slate-800 font-black tabular-nums"><?php echo $row['max_acrobacias']; ?></span>
                        </div>
                        <div class="p-2 bg-slate-50 rounded-lg">
                            <span class="block text-[8px] uppercase text-slate-400 font-black">Híbridos</span>
                            <span class="text-slate-800 font-black tabular-nums"><?php echo $row['max_hibridos']; ?></span>
                        </div>
                        <div class="p-2 bg-slate-50 rounded-lg">
                            <span class="block text-[8px] uppercase text-slate-400 font-black">TRE</span>
                            <span class="text-slate-800 font-black tabular-nums"><?php echo $row['max_tre']; ?></span>
                        </div>
                        <div class="p-2 bg-indigo-50/70 rounded-lg border border-indigo-100">
                            <span class="block text-[8px] uppercase text-indigo-600 font-black">CC</span>
                            <span class="text-indigo-800 font-black tabular-nums"><?php echo $row['elementos_coach_card']; ?></span>
                        </div>
                    </div>

                    <!-- Factores Principales (Orden oficial WA) -->
                    <div class="p-2 bg-emerald-50/40 rounded-xl border border-emerald-100 text-[10px] flex items-center justify-between font-mono tabular-nums text-emerald-800">
                        <span>Ac: <strong><?php echo number_format($row['f_acro'], 1); ?></strong></span>
                        <span>Hy: <strong><?php echo number_format($row['f_hybrid'], 1); ?></strong></span>
                        <span>TR: <strong><?php echo number_format($row['f_tre'], 1); ?></strong></span>
                        <span>CM: <strong><?php echo number_format($row['f_chomu'], 1); ?></strong></span>
                        <span>Pe: <strong><?php echo number_format($row['f_performance'], 1); ?></strong></span>
                        <span>Tr: <strong><?php echo number_format($row['f_transitions'], 1); ?></strong></span>
                    </div>
                </div>
            <?php endforeach; endif; ?>
        </div>

    </div>
</main>

<!-- Modal de Edición Completo -->
<div id="editReglaModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-2xl w-full p-5 sm:p-8 shadow-2xl border border-slate-100 relative animate-fade-in-up max-h-[90vh] overflow-y-auto no-scrollbar">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-black">
                    <i class="fas fa-sliders"></i>
                </span>
                <div>
                    <h3 class="text-base font-black text-slate-800 tracking-tight" id="modalTitle">Ficha Técnica Oficial</h3>
                    <p class="text-[11px] font-bold text-slate-400" id="modalSubtitle">Categoría y Modalidad</p>
                </div>
            </div>
            <button onclick="closeEditModal()" class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center transition-colors">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>

        <form action="reglas_competicion_code.php" method="POST" class="space-y-6">
            <input type="hidden" name="edit_id" id="modal_edit_id">

            <!-- SECCIÓN 1: TIEMPOS DE MÚSICA -->
            <div class="p-4 bg-indigo-50/40 rounded-2xl border border-indigo-100">
                <h4 class="text-xs font-black uppercase text-indigo-800 tracking-wider mb-3 flex items-center gap-1.5">
                    <i class="fas fa-music text-indigo-500"></i> Duración Reglamentaria de Música
                </h4>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase text-slate-500 px-1">Tiempo Oficial (mm:ss o seg)</label>
                        <input type="text" name="duracion_musica" id="modal_duracion_musica" placeholder="ej: 2:20 o 140" class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-200 font-black text-slate-800 text-sm outline-none focus:border-indigo-500">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase text-slate-500 px-1">Margen Tolerancia (±seg)</label>
                        <input type="number" name="margen_musica" id="modal_margen_musica" class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-200 font-black text-slate-800 text-sm outline-none focus:border-indigo-500">
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 2: LÍMITES APÉNDICE III (Orden: Acros, Híbridos, TRE) -->
            <div class="p-4 bg-slate-50/80 rounded-2xl border border-slate-200/80">
                <h4 class="text-xs font-black uppercase text-slate-700 tracking-wider mb-3 flex items-center gap-1.5">
                    <i class="fas fa-cubes text-slate-500"></i> Límites de Estructura (Apéndice III)
                </h4>
                <div class="grid grid-cols-3 gap-3 mb-3">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Max Acros</label>
                        <input type="number" name="max_acrobacias" id="modal_max_acrobacias" min="0" oninput="calcModalCoachCard()" class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 font-bold text-xs text-slate-700 outline-none">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Max Híbridos</label>
                        <input type="number" name="max_hibridos" id="modal_max_hibridos" min="0" oninput="calcModalCoachCard()" class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 font-bold text-xs text-slate-700 outline-none">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Max TRE</label>
                        <input type="number" name="max_tre" id="modal_max_tre" min="0" oninput="calcModalCoachCard()" class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 font-bold text-xs text-slate-700 outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Max Apnea (seg)</label>
                        <input type="number" step="0.01" name="max_apnea" id="modal_max_apnea" placeholder="ej: 18.99" class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 font-bold text-xs text-slate-700 outline-none">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">¿Híbrido Creativo?</label>
                        <select name="req_hibrido_creativo" id="modal_req_hibrido_creativo" onchange="calcModalCoachCard()" class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 font-bold text-xs text-slate-700 outline-none">
                            <option value="0">No (Opcional)</option>
                            <option value="1">Sí (Obligatorio)</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase text-indigo-600 px-1 flex items-center gap-1">
                            <i class="fas fa-clipboard-check"></i> Elementos CC
                        </label>
                        <input type="number" name="elementos_coach_card" id="modal_elementos_coach_card" min="0" class="w-full px-3 py-2 rounded-xl bg-indigo-50/50 border border-indigo-200 font-black text-xs text-indigo-700 outline-none" title="Total Elementos Coach Card">
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 3: FACTORES DE PUNTUACIÓN (Orden WA: Acro, Hyb, TRE, ChoMu, Perf, Trans) -->
            <div class="p-4 bg-emerald-50/40 rounded-2xl border border-emerald-100">
                <h4 class="text-xs font-black uppercase text-emerald-800 tracking-wider mb-3 flex items-center gap-1.5">
                    <i class="fas fa-calculator text-emerald-600"></i> Factores de Multiplicación Oficiales
                </h4>
                <div class="grid grid-cols-3 sm:grid-cols-6 gap-2.5">
                    <div class="space-y-1">
                        <label class="text-[9px] font-black uppercase text-emerald-700 px-0.5">Acro</label>
                        <input type="number" step="0.01" name="f_acro" id="modal_f_acro" class="w-full px-2.5 py-2 rounded-xl bg-white border border-slate-200 font-black text-xs text-emerald-800">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[9px] font-black uppercase text-emerald-700 px-0.5">Hybrid</label>
                        <input type="number" step="0.01" name="f_hybrid" id="modal_f_hybrid" class="w-full px-2.5 py-2 rounded-xl bg-white border border-slate-200 font-black text-xs text-emerald-800">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[9px] font-black uppercase text-emerald-700 px-0.5">TRE</label>
                        <input type="number" step="0.01" name="f_tre" id="modal_f_tre" class="w-full px-2.5 py-2 rounded-xl bg-white border border-slate-200 font-black text-xs text-emerald-800">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[9px] font-black uppercase text-slate-500 px-0.5">ChoMu</label>
                        <input type="number" step="0.01" name="f_chomu" id="modal_f_chomu" class="w-full px-2.5 py-2 rounded-xl bg-white border border-slate-200 font-black text-xs text-slate-800">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[9px] font-black uppercase text-slate-500 px-0.5">Performance</label>
                        <input type="number" step="0.01" name="f_performance" id="modal_f_performance" class="w-full px-2.5 py-2 rounded-xl bg-white border border-slate-200 font-black text-xs text-slate-800">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[9px] font-black uppercase text-slate-500 px-0.5">Transitions</label>
                        <input type="number" step="0.01" name="f_transitions" id="modal_f_transitions" class="w-full px-2.5 py-2 rounded-xl bg-white border border-slate-200 font-black text-xs text-slate-800">
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 4: DEDUCCIONES POR SINCRONIZACIÓN -->
            <div class="p-4 bg-rose-50/40 rounded-2xl border border-rose-100">
                <h4 class="text-xs font-black uppercase text-rose-800 tracking-wider mb-3 flex items-center gap-1.5">
                    <i class="fas fa-triangle-exclamation text-rose-500"></i> Deducciones por Errores de Sincro
                </h4>
                <div class="grid grid-cols-3 gap-3">
                    <div class="space-y-1">
                        <label class="text-[9px] font-black uppercase text-rose-600 px-1">Error XS (Minor)</label>
                        <input type="number" step="0.01" name="error_xs" id="modal_error_xs" class="w-full px-3 py-2 rounded-xl bg-white border border-rose-200 font-black text-xs text-rose-700">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[9px] font-black uppercase text-rose-600 px-1">Error OB (Obvious)</label>
                        <input type="number" step="0.01" name="error_ob" id="modal_error_ob" class="w-full px-3 py-2 rounded-xl bg-white border border-rose-200 font-black text-xs text-rose-700">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[9px] font-black uppercase text-rose-600 px-1">Error XL (Major)</label>
                        <input type="number" step="0.01" name="error_xl" id="modal_error_xl" class="w-full px-3 py-2 rounded-xl bg-white border border-rose-200 font-black text-xs text-rose-700">
                    </div>
                </div>
            </div>

            <!-- Botones -->
            <div class="pt-2 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="button" onclick="closeEditModal()" class="px-5 py-2.5 text-xs font-black uppercase text-slate-400 hover:text-slate-600 transition-colors">Cancelar</button>
                <button type="submit" name="update_btn" class="px-8 py-3 bg-indigo-600 text-white font-black uppercase text-xs tracking-wider rounded-xl shadow-lg hover:bg-indigo-700 active:scale-[0.98] transition-all">
                    Guardar Ficha Técnica
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleAddReglaPanel() {
    const p = document.getElementById('addReglaPanel');
    p.classList.toggle('hidden');
    if(!p.classList.contains('hidden')) p.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function filterReglasTable() {
    const q = document.getElementById('reglasSearchInput').value.toLowerCase().trim();
    
    // Tabla desktop
    document.querySelectorAll('.regla-row').forEach(row => {
        const text = row.dataset.search || '';
        row.style.display = text.includes(q) ? '' : 'none';
    });

    // Cards móvil
    document.querySelectorAll('.regla-card').forEach(card => {
        const text = card.dataset.search || '';
        card.style.display = text.includes(q) ? '' : 'none';
    });
}

function openEditModal(row) {
    document.getElementById('modal_edit_id').value = row.id_regla;
    document.getElementById('modalTitle').textContent = (row.modalidad_nombre || 'Modalidad') + ' — ' + (row.categoria_nombre || '');
    document.getElementById('modalSubtitle').textContent = 'Edición de Ficha Técnica Oficial (ID #' + row.id_regla + ')';

    const dur = parseInt(row.duracion_musica || 0);
    if (dur > 0) {
        const m = Math.floor(dur / 60);
        const s = dur % 60;
        document.getElementById('modal_duracion_musica').value = m + ':' + (s < 10 ? '0' : '') + s;
    } else {
        document.getElementById('modal_duracion_musica').value = '';
    }

    document.getElementById('modal_margen_musica').value = (row.margen_musica !== null && row.margen_musica !== undefined) ? row.margen_musica : 5;
    document.getElementById('modal_max_hibridos').value = row.max_hibridos || 0;
    document.getElementById('modal_max_acrobacias').value = row.max_acrobacias || 0;
    document.getElementById('modal_max_tre').value = row.max_tre || 0;
    document.getElementById('modal_max_apnea').value = row.max_apnea || '';
    document.getElementById('modal_req_hibrido_creativo').value = row.req_hibrido_creativo || 0;

    const calcCC = (parseInt(row.max_tre || 0) + parseInt(row.max_hibridos || 0) + parseInt(row.max_acrobacias || 0) + parseInt(row.req_hibrido_creativo || 0));
    document.getElementById('modal_elementos_coach_card').value = (row.elementos_coach_card !== null && row.elementos_coach_card !== undefined && parseInt(row.elementos_coach_card) > 0) ? row.elementos_coach_card : calcCC;

    // Factores y Deducciones
    document.getElementById('modal_f_chomu').value = parseFloat(row.f_chomu || 1.0).toFixed(2);
    document.getElementById('modal_f_performance').value = parseFloat(row.f_performance || 1.0).toFixed(2);
    document.getElementById('modal_f_transitions').value = parseFloat(row.f_transitions || 1.0).toFixed(2);
    document.getElementById('modal_f_hybrid').value = parseFloat(row.f_hybrid || 1.0).toFixed(2);
    document.getElementById('modal_f_acro').value = parseFloat(row.f_acro || 1.0).toFixed(2);
    document.getElementById('modal_f_tre').value = parseFloat(row.f_tre || 1.0).toFixed(2);

    document.getElementById('modal_error_xs').value = parseFloat(row.error_xs || -0.10).toFixed(2);
    document.getElementById('modal_error_ob').value = parseFloat(row.error_ob || -0.50).toFixed(2);
    document.getElementById('modal_error_xl').value = parseFloat(row.error_xl || -3.00).toFixed(2);

    document.getElementById('editReglaModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editReglaModal').classList.add('hidden');
}

function calcModalCoachCard() {
    const tre = parseInt(document.getElementById('modal_max_tre').value) || 0;
    const hyb = parseInt(document.getElementById('modal_max_hibridos').value) || 0;
    const acro = parseInt(document.getElementById('modal_max_acrobacias').value) || 0;
    const cr = parseInt(document.getElementById('modal_req_hibrido_creativo').value) || 0;
    document.getElementById('modal_elementos_coach_card').value = tre + hyb + acro + cr;
}

function calcNewCoachCard() {
    const tre = parseInt(document.getElementById('new_max_tre').value) || 0;
    const hyb = parseInt(document.getElementById('new_max_hibridos').value) || 0;
    const acro = parseInt(document.getElementById('new_max_acrobacias').value) || 0;
    const cr = parseInt(document.getElementById('new_req_hibrido_creativo').value) || 0;
    document.getElementById('new_elementos_coach_card').value = tre + hyb + acro + cr;
}
</script>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
