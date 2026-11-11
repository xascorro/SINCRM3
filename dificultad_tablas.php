<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');

$id_rol_user = (int)($_SESSION['id_rol'] ?? 0);
$puede_editar = in_array($id_rol_user, [1, 2, 3]);

$tablas_disponibles = [
    'dificultad_hibridos' => [
        'nombre' => 'Híbridos',
        'icono' => 'fa-water',
        'color' => 'blue',
        'desc' => 'Spins, Thrusts, Rotations, Flexibilidad, Airborne, Connectors y Bonus'
    ],
    'dificultad_acrobacias' => [
        'nombre' => 'Acrobacias Equipos',
        'icono' => 'fa-person-falling',
        'color' => 'indigo',
        'desc' => 'Acrobacias de grupo (Grupos A, B, C, P + Addendum oficial)'
    ],
    'dificultad_acropair' => [
        'nombre' => 'Acrobacias Parejas',
        'icono' => 'fa-people-arrows',
        'color' => 'purple',
        'desc' => 'Lifts, Throws y Platforms específicas de Dúo Femenino y Dúo Mixto'
    ],
    'dificultad_tre' => [
        'nombre' => 'Elementos Técnicos (TRE)',
        'icono' => 'fa-certificate',
        'color' => 'amber',
        'desc' => 'Elementos requeridos obligatorios (Solo, Dúo y Equipos Técnicos)'
    ],
    'dificultad_basemark' => [
        'nombre' => 'Base Mark (BM)',
        'icono' => 'fa-shield-halved',
        'color' => 'red',
        'desc' => 'Penalizaciones de dificultad mínima por fallo en ejecución técnica'
    ]
];

$tabla_activa = $_GET['tabla'] ?? 'dificultad_hibridos';
if (!array_key_exists($tabla_activa, $tablas_disponibles)) {
    $tabla_activa = 'dificultad_hibridos';
}

// Obtener temporadas/versiones disponibles en esta tabla (con verificación de columna temporada)
$check_temp_col = mysqli_query($connection, "SHOW COLUMNS FROM $tabla_activa LIKE 'temporada'");
$has_temporada = ($check_temp_col && mysqli_num_rows($check_temp_col) > 0);

if ($has_temporada) {
    $q_vers = "SELECT version, temporada, COUNT(*) as total 
               FROM $tabla_activa 
               GROUP BY version, temporada 
               ORDER BY version DESC";
} else {
    $q_vers = "SELECT version, '' as temporada, COUNT(*) as total 
               FROM $tabla_activa 
               GROUP BY version 
               ORDER BY version DESC";
}
$res_vers = mysqli_query($connection, $q_vers);
$versiones = [];
while ($v = mysqli_fetch_assoc($res_vers)) {
    if (empty($v['temporada'])) {
        $v['temporada'] = match($v['version']) {
            '2627' => '2026-2027',
            '2425' => '2024-2025',
            '2324' => '2023-2024',
            default => 'v' . $v['version']
        };
    }
    $versiones[] = $v;
}

$version_activa = $_GET['version'] ?? ($versiones[0]['version'] ?? '2627');

// Obtener datos de la tabla activa y versión seleccionada
$q_elem = "SELECT * FROM $tabla_activa WHERE version = '$version_activa' ORDER BY agrupar ASC, valor ASC, codigo ASC";
$res_elem = mysqli_query($connection, $q_elem);
$elementos = [];
$familias = [];
$min_dd = 999;
$max_dd = 0;

while ($row = mysqli_fetch_assoc($res_elem)) {
    $elementos[] = $row;
    $agr = trim($row['agrupar'] ?? 'General');
    if (!empty($agr) && !in_array($agr, $familias)) {
        $familias[] = $agr;
    }
    $v_num = floatval($row['valor'] ?? 0);
    if ($v_num < $min_dd) $min_dd = $v_num;
    if ($v_num > $max_dd) $max_dd = $v_num;
}
if (empty($elementos)) {
    $min_dd = 0;
    $max_dd = 0;
}

// Conteo total por pestaña para badges
$conteos_tabs = [];
foreach ($tablas_disponibles as $t_key => $t_info) {
    $q_cnt = mysqli_query($connection, "SELECT COUNT(*) as cnt FROM $t_key WHERE version = '$version_activa'");
    $conteos_tabs[$t_key] = $q_cnt ? intval(mysqli_fetch_assoc($q_cnt)['cnt'] ?? 0) : 0;
}

$info_activa = $tablas_disponibles[$tabla_activa];
?>

<main class="flex-1 flex flex-col min-w-0 bg-surface">
    <?php include('includes/topbar.php'); ?>

    <div class="px-3 py-4 sm:p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend text-primary">

        <!-- Header de Sección -->
        <div class="mb-6 sm:mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4 sm:gap-6">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2.5 py-1 bg-blue-50 text-blue-600 font-black text-[10px] uppercase tracking-widest rounded-lg border border-blue-100 flex items-center gap-1.5">
                        <i class="fas fa-book-bookmark text-[9px]"></i> World Aquatics
                    </span>
                    <span class="px-2.5 py-1 bg-emerald-50 text-emerald-600 font-black text-[10px] uppercase tracking-widest rounded-lg border border-emerald-100">
                        Normativa Oficial
                    </span>
                </div>
                <h1 class="text-2xl sm:text-4xl font-black text-slate-800 tracking-tighter mb-1 flex items-center gap-2.5 sm:gap-3">
                    <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-500/20 shrink-0">
                        <i class="fas fa-calculator text-base sm:text-lg"></i>
                    </span>
                    Tablas de Dificultad (DD)
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 font-medium italic">
                    <?php echo htmlspecialchars($info_activa['desc']); ?>
                </p>
            </div>

            <!-- Controles Superiores: Temporada y Acciones -->
            <div class="flex flex-wrap items-center gap-2.5 sm:gap-3">
                <!-- Selector de Temporada -->
                <div class="flex items-center bg-white p-1 rounded-2xl border border-slate-200 shadow-xs">
                    <span class="text-[10px] font-black uppercase text-slate-400 px-3 tracking-wider flex items-center gap-1.5">
                        <i class="fas fa-calendar-days text-[11px]"></i> Temporada:
                    </span>
                    <div class="flex items-center gap-1">
                        <?php foreach ($versiones as $v_opt): 
                            $is_v_act = ($v_opt['version'] === $version_activa);
                            $badge_cl = $is_v_act ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100';
                        ?>
                            <a href="dificultad_tablas.php?tabla=<?php echo $tabla_activa; ?>&version=<?php echo $v_opt['version']; ?>" 
                               class="px-3 py-1.5 rounded-xl text-xs font-black transition-all <?php echo $badge_cl; ?>">
                                <?php echo htmlspecialchars($v_opt['temporada']); ?>
                                <?php if ($v_opt['version'] === '2627'): ?>
                                    <span class="ml-1 px-1.5 py-0.2 bg-white/20 text-[9px] rounded-md font-bold uppercase tracking-tighter">AQUA</span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php if ($puede_editar): ?>
                    <button onclick="toggleAddElemPanel()" class="px-4 sm:px-5 py-2.5 bg-blue-600 text-white font-black uppercase text-xs tracking-wider rounded-xl sm:rounded-2xl shadow-md hover:bg-blue-500 active:scale-[0.98] transition-all flex items-center gap-2 shrink-0">
                        <i class="fas fa-plus text-xs"></i> Añadir Elemento
                    </button>
                    <?php if ($id_rol_user === 1): ?>
                        <button onclick="openModalClonar()" class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-black uppercase text-xs tracking-wider rounded-xl sm:rounded-2xl border border-slate-200 active:scale-[0.98] transition-all flex items-center gap-2 shrink-0" title="Duplicar temporada a nuevo año">
                            <i class="fas fa-clone text-xs text-slate-500"></i> Clonar
                        </button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Alertas del Sistema -->
        <?php include('includes/alertas_v4.php'); ?>

        <!-- Panel Añadir Elemento (Colapsable) -->
        <?php if ($puede_editar): ?>
        <div id="addElemPanel" class="hidden mb-6 sm:mb-8 animate-fade-in-down">
            <div class="bg-white rounded-2xl sm:rounded-[2rem] p-5 sm:p-8 shadow-xl border border-blue-100 relative overflow-hidden">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                    <h2 class="text-base sm:text-lg font-black text-slate-800 flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center"><i class="fas fa-plus text-xs"></i></span>
                        Nuevo Elemento en <?php echo htmlspecialchars($info_activa['nombre']); ?> (v<?php echo $version_activa; ?>)
                    </h2>
                    <button type="button" onclick="toggleAddElemPanel()" class="text-slate-400 hover:text-slate-600 p-1"><i class="fas fa-times"></i></button>
                </div>

                <form action="dificultad_tablas_code.php" method="POST" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3 sm:gap-5">
                    <input type="hidden" name="tabla" value="<?php echo $tabla_activa; ?>">
                    <input type="hidden" name="version_curr" value="<?php echo $version_activa; ?>">

                    <div class="sm:col-span-1 md:col-span-3 space-y-1">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-wider">Código Oficial *</label>
                        <input type="text" name="codigo" required placeholder="Ej: T4a, r1.5L+, PC" class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-sm font-black text-slate-800 uppercase focus:bg-white focus:border-blue-500 outline-none transition-all">
                    </div>

                    <div class="sm:col-span-1 md:col-span-3 space-y-1">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-wider">Valor DD (Dificultad) *</label>
                        <input type="number" step="0.001" name="valor" required placeholder="0.450" class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-sm font-black text-blue-600 tabular-nums focus:bg-white focus:border-blue-500 outline-none transition-all">
                    </div>

                    <div class="sm:col-span-1 md:col-span-3 space-y-1">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-wider">Familia / Agrupación *</label>
                        <input type="text" name="agrupar" required list="lista_familias" placeholder="Ej: T, S, R, ACRO-A, Solo" class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-sm font-bold text-slate-700 focus:bg-white focus:border-blue-500 outline-none transition-all">
                        <datalist id="lista_familias">
                            <?php foreach ($familias as $f_item): ?>
                                <option value="<?php echo htmlspecialchars($f_item); ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                    </div>

                    <div class="sm:col-span-1 md:col-span-3 space-y-1">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-wider">Subfamilia (Opcional)</label>
                        <input type="text" name="subagrupar" placeholder="Opcional" class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-sm font-bold text-slate-700 focus:bg-white focus:border-blue-500 outline-none transition-all">
                    </div>

                    <div class="sm:col-span-2 md:col-span-8 space-y-1">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-wider">Descripción Oficial</label>
                        <input type="text" name="descripcion" placeholder="Descripción del movimiento técnico" class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-xs font-medium text-slate-700 focus:bg-white focus:border-blue-500 outline-none transition-all">
                    </div>

                    <div class="sm:col-span-1 md:col-span-2 space-y-1">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-wider">Versión</label>
                        <input type="text" name="version" required value="<?php echo $version_activa; ?>" class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-xs font-black text-slate-700 uppercase focus:bg-white focus:border-blue-500 outline-none transition-all">
                    </div>

                    <div class="sm:col-span-1 md:col-span-2 space-y-1">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-wider">Temporada</label>
                        <?php 
                        $temp_default = ($version_activa === '2627') ? '2026-2027' : (($version_activa === '2425') ? '2024-2025' : '2023-2024');
                        ?>
                        <input type="text" name="temporada" required value="<?php echo $temp_default; ?>" class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-slate-700 focus:bg-white focus:border-blue-500 outline-none transition-all">
                    </div>

                    <div class="sm:col-span-2 md:col-span-12 pt-3 flex justify-end gap-3">
                        <button type="button" onclick="toggleAddElemPanel()" class="px-4 py-2.5 text-xs font-black uppercase text-slate-400 hover:text-slate-600 transition-colors">Cancelar</button>
                        <button type="submit" name="btn_guardar_nuevo" class="px-6 py-2.5 bg-blue-600 text-white font-black uppercase text-xs tracking-wider rounded-xl shadow-md hover:bg-blue-500 active:scale-[0.98] transition-all">
                            Guardar Elemento
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Selector Segmentado de Tablas (Pestañas) -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2 sm:gap-3 mb-6">
            <?php foreach ($tablas_disponibles as $key_t => $item_t): 
                $is_sel = ($key_t === $tabla_activa);
                $cnt = $conteos_tabs[$key_t] ?? 0;
            ?>
                <a href="dificultad_tablas.php?tabla=<?php echo $key_t; ?>&version=<?php echo $version_activa; ?>" 
                   class="p-3 sm:p-4 rounded-2xl border transition-all flex flex-col justify-between gap-2 group <?php echo $is_sel ? 'bg-white border-blue-500 ring-2 ring-blue-500/10 shadow-md shadow-blue-500/5' : 'bg-white/80 hover:bg-white border-slate-200/80 hover:border-slate-300'; ?>">
                    <div class="flex items-center justify-between">
                        <span class="w-8 h-8 rounded-xl flex items-center justify-center text-xs <?php echo $is_sel ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200/70'; ?> transition-colors">
                            <i class="fas <?php echo $item_t['icono']; ?>"></i>
                        </span>
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-black tabular-nums <?php echo $is_sel ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-500'; ?>">
                            <?php echo $cnt; ?>
                        </span>
                    </div>
                    <div>
                        <h4 class="text-xs font-black <?php echo $is_sel ? 'text-blue-900' : 'text-slate-700 group-hover:text-slate-900'; ?> leading-tight">
                            <?php echo htmlspecialchars($item_t['nombre']); ?>
                        </h4>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- KPIs y Buscador de la Tabla Activa -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 mb-6">
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
                <span class="text-[9px] font-black uppercase text-slate-400 tracking-wider">Censo de Elementos</span>
                <p class="text-2xl font-black text-slate-800 tabular-nums mt-0.5"><?php echo count($elementos); ?></p>
                <span class="text-[10px] font-bold text-slate-400">Total registrados</span>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
                <span class="text-[9px] font-black uppercase text-slate-400 tracking-wider">Rango Dificultad (DD)</span>
                <p class="text-2xl font-black text-blue-600 tabular-nums mt-0.5"><?php echo number_format($min_dd, 3); ?> - <?php echo number_format($max_dd, 3); ?></p>
                <span class="text-[10px] font-bold text-blue-400">Puntos oficiales WA</span>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
                <span class="text-[9px] font-black uppercase text-slate-400 tracking-wider">Familias Técnicas</span>
                <p class="text-2xl font-black text-purple-600 tabular-nums mt-0.5"><?php echo count($familias); ?></p>
                <span class="text-[10px] font-bold text-purple-400">Categorías de movimiento</span>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
                <span class="text-[9px] font-black uppercase text-slate-400 tracking-wider">Temporada Activa</span>
                <p class="text-xl font-black text-emerald-600 mt-1 uppercase"><?php echo ($version_activa === '2627') ? '2026-2027' : $version_activa; ?></p>
                <span class="text-[10px] font-bold text-emerald-500">Versión v<?php echo $version_activa; ?></span>
            </div>
        </div>

        <!-- Barra de Búsqueda y Chips de Filtro por Familia -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs mb-6 space-y-3">
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                <div class="relative flex-1">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" id="difSearchInput" placeholder="Filtrar por código, familia, descripción o valor..." 
                           class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 placeholder-slate-400 focus:bg-white focus:border-blue-500 outline-none transition-all"
                           onkeyup="filterDificultadTable()">
                </div>
                <div class="text-right text-[11px] font-bold text-slate-400 shrink-0">
                    Mostrando <span id="contadorVisible" class="font-black text-slate-700 tabular-nums"><?php echo count($elementos); ?></span> de <?php echo count($elementos); ?> elementos
                </div>
            </div>

            <!-- Chips de Familias -->
            <?php if (!empty($familias)): ?>
            <div class="flex flex-wrap items-center gap-1.5 pt-2 border-t border-slate-100">
                <span class="text-[9px] font-black uppercase text-slate-400 mr-1 tracking-wider">Familias:</span>
                <button type="button" onclick="filterByFamily('')" class="family-chip px-2.5 py-1 rounded-lg text-[10px] font-black transition-all bg-blue-600 text-white shadow-xs" data-family="">
                    Todos
                </button>
                <?php foreach ($familias as $fam): ?>
                    <button type="button" onclick="filterByFamily('<?php echo htmlspecialchars($fam); ?>')" class="family-chip px-2.5 py-1 rounded-lg text-[10px] font-bold transition-all bg-slate-100 text-slate-600 hover:bg-slate-200" data-family="<?php echo htmlspecialchars($fam); ?>">
                        <?php echo htmlspecialchars($fam); ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Tabla de Elementos -->
        <div class="bg-white rounded-2xl sm:rounded-3xl shadow-xs border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" id="tablaDificultad">
                    <thead>
                        <tr class="bg-slate-50 text-[10px] font-black uppercase tracking-wider text-slate-400 border-b border-slate-200">
                            <th class="px-5 py-3.5 w-16">ID</th>
                            <th class="px-5 py-3.5 w-32">Código</th>
                            <th class="px-5 py-3.5 w-28 text-center">Valor DD</th>
                            <th class="px-5 py-3.5 w-36">Familia</th>
                            <?php if ($tabla_activa === 'dificultad_hibridos' || $tabla_activa === 'dificultad_acrobacias' || $tabla_activa === 'dificultad_acropair'): ?>
                                <th class="px-4 py-3.5 w-32">Subfamilia</th>
                            <?php endif; ?>
                            <th class="px-5 py-3.5">Descripción Técnica</th>
                            <th class="px-4 py-3.5 w-24 text-center">Versión</th>
                            <?php if ($puede_editar): ?>
                                <th class="px-5 py-3.5 w-24 text-right">Acciones</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs font-bold text-slate-700">
                        <?php if (empty($elementos)): ?>
                            <tr>
                                <td colspan="8" class="p-8 text-center text-slate-400 italic">
                                    No hay elementos registrados en esta tabla para la temporada seleccionada.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($elementos as $el): 
                                $id_el = intval($el['id']);
                                $cod_el = htmlspecialchars($el['codigo'] ?? '');
                                $val_el = floatval($el['valor'] ?? 0);
                                $agr_el = htmlspecialchars($el['agrupar'] ?? '');
                                $sub_el = htmlspecialchars($el['subagrupar'] ?? '');
                                $desc_el = htmlspecialchars($el['descripcion'] ?? '');
                                $ver_el = htmlspecialchars($el['version'] ?? '');
                                $temp_el = htmlspecialchars($el['temporada'] ?? '');

                                // Color de badge según familia
                                $badge_bg = 'bg-slate-100 text-slate-700 border-slate-200';
                                if (strpos($agr_el, 'T') === 0) $badge_bg = 'bg-sky-50 text-sky-700 border-sky-200';
                                elseif (strpos($agr_el, 'S') === 0) $badge_bg = 'bg-indigo-50 text-indigo-700 border-indigo-200';
                                elseif (strpos($agr_el, 'R') === 0) $badge_bg = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                elseif (strpos($agr_el, 'A') === 0) $badge_bg = 'bg-violet-50 text-violet-700 border-violet-200';
                                elseif (strpos($agr_el, 'F') === 0) $badge_bg = 'bg-amber-50 text-amber-700 border-amber-200';
                                elseif (strpos($agr_el, 'BONUS') !== false || strpos($cod_el, 'PC') !== false) $badge_bg = 'bg-rose-50 text-rose-700 border-rose-200';
                            ?>
                            <tr class="hover:bg-slate-50/80 transition-colors item-row" 
                                data-codigo="<?php echo strtolower($cod_el); ?>" 
                                data-familia="<?php echo htmlspecialchars($agr_el); ?>" 
                                data-desc="<?php echo strtolower($desc_el); ?>" 
                                data-valor="<?php echo $val_el; ?>">
                                
                                <td class="px-5 py-3 font-mono text-[11px] text-slate-400 tabular-nums">
                                    #<?php echo $id_el; ?>
                                </td>

                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-900 text-white font-mono text-xs font-black shadow-2xs tracking-wide">
                                        <?php echo $cod_el; ?>
                                    </span>
                                </td>

                                <td class="px-5 py-3 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 border border-blue-100 font-mono font-black text-xs tabular-nums">
                                        <?php echo number_format($val_el, 3); ?>
                                    </span>
                                </td>

                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-black border <?php echo $badge_bg; ?>">
                                        <?php echo $agr_el ?: '-'; ?>
                                    </span>
                                </td>

                                <?php if ($tabla_activa === 'dificultad_hibridos' || $tabla_activa === 'dificultad_acrobacias' || $tabla_activa === 'dificultad_acropair'): ?>
                                    <td class="px-4 py-3 text-[11px] text-slate-500 font-medium">
                                        <?php echo $sub_el ?: '<span class="text-slate-300">-</span>'; ?>
                                    </td>
                                <?php endif; ?>

                                <td class="px-5 py-3 text-[11px] text-slate-600 font-medium leading-relaxed max-w-md">
                                    <?php echo $desc_el ?: '<span class="text-slate-300 italic">Sin descripción</span>'; ?>
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <span class="text-[10px] font-black text-slate-400 uppercase">
                                        v<?php echo $ver_el; ?>
                                    </span>
                                </td>

                                <?php if ($puede_editar): ?>
                                    <td class="px-5 py-3 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button type="button" 
                                                    onclick="openModalEditar(<?php echo htmlspecialchars(json_encode([
                                                        'id' => $id_el,
                                                        'codigo' => $cod_el,
                                                        'valor' => $val_el,
                                                        'agrupar' => $agr_el,
                                                        'subagrupar' => $sub_el,
                                                        'descripcion' => $desc_el,
                                                        'version' => $ver_el,
                                                        'temporada' => $temp_el
                                                    ])); ?>)" 
                                                    class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-blue-50 hover:text-blue-600 text-slate-500 flex items-center justify-center transition-colors" 
                                                    title="Editar elemento">
                                                <i class="fas fa-pen text-[10px]"></i>
                                            </button>
                                            <button type="button" 
                                                    onclick="confirmarEliminar(<?php echo $id_el; ?>, '<?php echo addslashes($cod_el); ?>')" 
                                                    class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-red-50 hover:text-red-600 text-slate-500 flex items-center justify-center transition-colors" 
                                                    title="Eliminar elemento">
                                                <i class="fas fa-trash-alt text-[10px]"></i>
                                            </button>
                                        </div>
                                    </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<!-- Modal Editar Elemento -->
<?php if ($puede_editar): ?>
<div id="modalEditar" class="fixed inset-0 z-50 hidden bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl border border-slate-200 animate-scale-up relative">
        <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
            <h3 class="text-base sm:text-lg font-black text-slate-800 flex items-center gap-2">
                <i class="fas fa-pen text-blue-600 text-sm"></i> Editar Elemento de Dificultad
            </h3>
            <button type="button" onclick="closeModalEditar()" class="text-slate-400 hover:text-slate-600 p-1"><i class="fas fa-times"></i></button>
        </div>

        <form action="dificultad_tablas_code.php" method="POST" class="space-y-4">
            <input type="hidden" name="tabla" value="<?php echo $tabla_activa; ?>">
            <input type="hidden" name="version_curr" value="<?php echo $version_activa; ?>">
            <input type="hidden" name="id_edit" id="edit_id">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-wider">Código Oficial</label>
                    <input type="text" name="codigo" id="edit_codigo" required class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm font-black text-slate-800 focus:bg-white focus:border-blue-500 outline-none">
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-wider">Valor DD (Dificultad)</label>
                    <input type="number" step="0.001" name="valor" id="edit_valor" required class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm font-black text-blue-600 tabular-nums focus:bg-white focus:border-blue-500 outline-none">
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-wider">Familia / Agrupación</label>
                    <input type="text" name="agrupar" id="edit_agrupar" required list="lista_familias_edit" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm font-bold text-slate-700 focus:bg-white focus:border-blue-500 outline-none">
                    <datalist id="lista_familias_edit">
                        <?php foreach ($familias as $f_item): ?>
                            <option value="<?php echo htmlspecialchars($f_item); ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-wider">Subfamilia</label>
                    <input type="text" name="subagrupar" id="edit_subagrupar" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm font-bold text-slate-700 focus:bg-white focus:border-blue-500 outline-none">
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-[10px] font-black uppercase text-slate-400 tracking-wider">Descripción Oficial</label>
                <textarea name="descripcion" id="edit_descripcion" rows="3" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-medium text-slate-700 focus:bg-white focus:border-blue-500 outline-none"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-wider">Versión</label>
                    <input type="text" name="version" id="edit_version" required class="w-full px-4 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-black text-slate-700">
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-wider">Temporada</label>
                    <input type="text" name="temporada" id="edit_temporada" required class="w-full px-4 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-slate-700">
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                <button type="button" onclick="closeModalEditar()" class="px-4 py-2.5 text-xs font-black uppercase text-slate-400 hover:text-slate-600">Cancelar</button>
                <button type="submit" name="btn_actualizar" class="px-6 py-2.5 bg-blue-600 text-white font-black uppercase text-xs tracking-wider rounded-xl shadow-md hover:bg-blue-500 active:scale-[0.98] transition-all">
                    Actualizar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Clonar Temporada (Solo Admin) -->
<?php if ($id_rol_user === 1): ?>
<div id="modalClonar" class="fixed inset-0 z-50 hidden bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-200 animate-scale-up relative">
        <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
            <h3 class="text-base sm:text-lg font-black text-slate-800 flex items-center gap-2">
                <i class="fas fa-clone text-blue-600 text-sm"></i> Clonar Temporada Completa
            </h3>
            <button type="button" onclick="closeModalClonar()" class="text-slate-400 hover:text-slate-600 p-1"><i class="fas fa-times"></i></button>
        </div>

        <form action="dificultad_tablas_code.php" method="POST" class="space-y-4">
            <input type="hidden" name="tabla" value="<?php echo $tabla_activa; ?>">
            <input type="hidden" name="version_curr" value="<?php echo $version_activa; ?>">

            <div class="space-y-1">
                <label class="text-[10px] font-black uppercase text-slate-400 tracking-wider">Versión Origen (a duplicar)</label>
                <input type="text" name="version_origen" required value="<?php echo $version_activa; ?>" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-black text-slate-700">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-wider">Nueva Versión</label>
                    <input type="text" name="version_destino" required placeholder="Ej: 2728" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-black text-blue-600">
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-wider">Nombre Temporada</label>
                    <input type="text" name="temporada_destino" required placeholder="Ej: 2027-2028" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-slate-700">
                </div>
            </div>

            <div class="space-y-2 pt-2">
                <label class="text-[10px] font-black uppercase text-slate-400 tracking-wider">Tablas a duplicar:</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs font-bold text-slate-700">
                    <?php foreach ($tablas_disponibles as $tk => $ti): ?>
                        <label class="flex items-center gap-2 p-2 rounded-xl bg-slate-50 border border-slate-100 cursor-pointer">
                            <input type="checkbox" name="tablas_clonar[]" value="<?php echo $tk; ?>" checked class="rounded text-blue-600">
                            <span><?php echo htmlspecialchars($ti['nombre']); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                <button type="button" onclick="closeModalClonar()" class="px-4 py-2.5 text-xs font-black uppercase text-slate-400 hover:text-slate-600">Cancelar</button>
                <button type="submit" name="btn_clonar_temporada" class="px-6 py-2.5 bg-blue-600 text-white font-black uppercase text-xs tracking-wider rounded-xl shadow-md hover:bg-blue-500 active:scale-[0.98] transition-all">
                    Iniciar Clonación
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>

<script>
let currentSelectedFamily = '';

function toggleAddElemPanel() {
    const p = document.getElementById('addElemPanel');
    if (p) p.classList.toggle('hidden');
}

function openModalEditar(data) {
    document.getElementById('edit_id').value = data.id || '';
    document.getElementById('edit_codigo').value = data.codigo || '';
    document.getElementById('edit_valor').value = data.valor || '';
    document.getElementById('edit_agrupar').value = data.agrupar || '';
    document.getElementById('edit_subagrupar').value = data.subagrupar || '';
    document.getElementById('edit_descripcion').value = data.descripcion || '';
    document.getElementById('edit_version').value = data.version || '';
    document.getElementById('edit_temporada').value = data.temporada || '';
    
    document.getElementById('modalEditar').classList.remove('hidden');
}

function closeModalEditar() {
    document.getElementById('modalEditar').classList.add('hidden');
}

function openModalClonar() {
    const m = document.getElementById('modalClonar');
    if (m) m.classList.remove('hidden');
}

function closeModalClonar() {
    const m = document.getElementById('modalClonar');
    if (m) m.classList.add('hidden');
}

function filterByFamily(family) {
    currentSelectedFamily = family;

    // Actualizar estilo visual de los chips
    document.querySelectorAll('.family-chip').forEach(btn => {
        if (btn.getAttribute('data-family') === family) {
            btn.className = 'family-chip px-2.5 py-1 rounded-lg text-[10px] font-black transition-all bg-blue-600 text-white shadow-xs';
        } else {
            btn.className = 'family-chip px-2.5 py-1 rounded-lg text-[10px] font-bold transition-all bg-slate-100 text-slate-600 hover:bg-slate-200';
        }
    });

    filterDificultadTable();
}

function filterDificultadTable() {
    const searchVal = document.getElementById('difSearchInput').value.toLowerCase().trim();
    const rows = document.querySelectorAll('#tablaDificultad .item-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const cod = row.getAttribute('data-codigo') || '';
        const fam = row.getAttribute('data-familia') || '';
        const desc = row.getAttribute('data-desc') || '';
        const val = row.getAttribute('data-valor') || '';

        const matchSearch = (cod.indexOf(searchVal) !== -1 || fam.toLowerCase().indexOf(searchVal) !== -1 || desc.indexOf(searchVal) !== -1 || val.indexOf(searchVal) !== -1);
        const matchFamily = (currentSelectedFamily === '' || fam === currentSelectedFamily);

        if (matchSearch && matchFamily) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const cnt = document.getElementById('contadorVisible');
    if (cnt) cnt.innerText = visibleCount;
}

function confirmarEliminar(id, codigo) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Eliminar elemento?',
            text: `Se eliminará '${codigo}' de la tabla. Esta acción no se puede deshacer.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `dificultad_tablas_code.php?action=delete&id=${id}&tabla=<?php echo $tabla_activa; ?>&version_curr=<?php echo $version_activa; ?>`;
            }
        });
    } else {
        if (confirm(`¿Eliminar elemento '${codigo}'?`)) {
            window.location.href = `dificultad_tablas_code.php?action=delete&id=${id}&tabla=<?php echo $tabla_activa; ?>&version_curr=<?php echo $version_activa; ?>`;
        }
    }
}
</script>
