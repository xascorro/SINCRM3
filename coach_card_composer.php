<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');

$id_rutina = (int)($_GET['id_rutina'] ?? ($_POST['id_rutina'] ?? 0));
$id_fase   = (int)($_GET['id_fase'] ?? ($_POST['id_fase'] ?? 0));
$figuras   = $_GET['figuras'] ?? ($_POST['figuras'] ?? (@$figuras ?? 'no'));

// Si no se proporcionó id_fase, intentar obtenerlo de la rutina
if ($id_rutina > 0 && $id_fase == 0) {
    $q_fase_check = mysqli_query($connection, "SELECT id_fase, id_competicion FROM rutinas WHERE id = '$id_rutina' LIMIT 1");
    if ($q_fase_check && $r_fase = mysqli_fetch_assoc($q_fase_check)) {
        $id_fase = (int)$r_fase['id_fase'];
        if (!isset($id_competicion) || empty($id_competicion)) {
            $id_competicion = (int)$r_fase['id_competicion'];
        }
    }
}

if (!isset($id_competicion) || empty($id_competicion)) {
    $id_competicion = (int)($_SESSION['id_competicion_usuario'] ?? ($_SESSION['id_competicion_activa'] ?? 0));
}

// Inicialización automática de elementos en hibridos_rutina si no existen
if ($id_rutina > 0 && $id_fase > 0) {
    $q_check_hib = mysqli_query($connection, "SELECT id FROM hibridos_rutina WHERE id_rutina = '$id_rutina' LIMIT 1");
    if (mysqli_num_rows($q_check_hib) == 0) {
        $q_fase_elem = mysqli_query($connection, "SELECT elementos_coach_card FROM fases WHERE id = '$id_fase'");
        if ($q_fase_elem && mysqli_num_rows($q_fase_elem) > 0) {
            $f_row = mysqli_fetch_assoc($q_fase_elem);
            $n_elementos = (int)$f_row['elementos_coach_card'];
            for ($x = 1; $x <= $n_elementos; $x++) {
                mysqli_query($connection, "INSERT INTO hibridos_rutina SET id_rutina = '$id_rutina', elemento = '$x', tipo = 'part', texto = '3'");
                mysqli_query($connection, "INSERT INTO hibridos_rutina SET id_rutina = '$id_rutina', elemento = '$x', tipo = 'time_inicio'");
                mysqli_query($connection, "INSERT INTO hibridos_rutina SET id_rutina = '$id_rutina', elemento = '$x', tipo = 'time_fin'");
                mysqli_query($connection, "INSERT INTO hibridos_rutina SET id_rutina = '$id_rutina', elemento = '$x', tipo = 'part'");
                mysqli_query($connection, "INSERT INTO hibridos_rutina SET id_rutina = '$id_rutina', elemento = '$x', tipo = 'basemark'");
                for ($d = 1; $d <= 15; $d++) {
                    mysqli_query($connection, "INSERT INTO hibridos_rutina SET id_rutina = '$id_rutina', elemento = '$x', tipo = 'dd'");
                }
                mysqli_query($connection, "INSERT INTO hibridos_rutina SET id_rutina = '$id_rutina', elemento = '$x', tipo = 'total'");
            }
        }
    }
}

// Obtener detalles de la rutina
$routine_data = null;
$nombres_atletas = '';
$elementos_coach_card = 0;

if ($id_rutina > 0) {
    if ($figuras === 'si') {
        $q_rot = "SELECT i.id, i.id_fase, i.id_nadadora, n.nombre as nombre_nadadora, n.apellidos as apellidos_nadadora, 
                         m.nombre as nombre_modalidad, c.nombre as nombre_categoria, cl.nombre_corto as nombre_club, cl.logo as logo_club,
                         f.elementos_coach_card, f.genero, f.id_competicion, 0 as dd_total, '' as tematica
                  FROM inscripciones_figuras i
                  JOIN fases f ON i.id_fase = f.id
                  LEFT JOIN modalidades m ON f.id_modalidad = m.id
                  LEFT JOIN categorias c ON f.id_categoria = c.id
                  JOIN nadadoras n ON i.id_nadadora = n.id
                  LEFT JOIN clubes cl ON n.club = cl.id
                  WHERE i.id = '$id_rutina'";
    } else {
        $q_rot = "SELECT r.id, r.tematica, r.id_fase, r.id_club, r.dd_total, r.id_competicion,
                         cl.nombre_corto as nombre_club, cl.logo as logo_club,
                         m.nombre as nombre_modalidad, c.nombre as nombre_categoria, f.elementos_coach_card, f.genero
                  FROM rutinas r
                  JOIN fases f ON r.id_fase = f.id
                  LEFT JOIN modalidades m ON f.id_modalidad = m.id
                  LEFT JOIN categorias c ON f.id_categoria = c.id
                  LEFT JOIN clubes cl ON r.id_club = cl.id
                  WHERE r.id = '$id_rutina'";
    }

    $res_rot = mysqli_query($connection, $q_rot);
    if ($res_rot && mysqli_num_rows($res_rot) > 0) {
        $routine_data = mysqli_fetch_assoc($res_rot);
        $elementos_coach_card = (int)($routine_data['elementos_coach_card'] ?? 0);
        if ($routine_data['id_competicion'] > 0) {
            $id_competicion = (int)$routine_data['id_competicion'];
        }

        // Obtener atletas si es rutina
        if ($figuras !== 'si') {
            $q_parts = "SELECT concat(n.nombre, ' ', n.apellidos) as nom_completo, rp.reserva 
                        FROM rutinas_participantes rp 
                        JOIN nadadoras n ON rp.id_nadadora = n.id 
                        WHERE rp.id_rutina = '$id_rutina' 
                        ORDER BY rp.reserva ASC, n.apellidos ASC";
            $res_parts = mysqli_query($connection, $q_parts);
            $titulares = [];
            $reservas = [];
            while ($p_row = mysqli_fetch_assoc($res_parts)) {
                if ($p_row['reserva'] === 'si') {
                    $reservas[] = $p_row['nom_completo'] . ' (R)';
                } else {
                    $titulares[] = $p_row['nom_completo'];
                }
            }
            $all_parts = array_merge($titulares, $reservas);
            $nombres_atletas = implode(', ', $all_parts);
        } else {
            $nombres_atletas = $routine_data['nombre_nadadora'] . ' ' . $routine_data['apellidos_nadadora'];
        }
    }
}

$back_url = ($figuras === 'si') ? 'inscripciones_figuras.php' : 'rutinas.php';
?>

<main class="flex-1 flex flex-col min-w-0 bg-surface">
    <?php include('includes/topbar.php'); ?>

    <div class="px-3 py-4 sm:p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend text-primary">
        
        <!-- Alertas de Sesión -->
        <?php if(isset($_SESSION['correcto']) && !empty($_SESSION['correcto'])): ?>
            <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-xs animate-fade-in">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center text-sm shadow-xs shrink-0"><i class="fas fa-check"></i></span>
                    <p class="text-xs sm:text-sm font-bold"><?php echo $_SESSION['correcto']; unset($_SESSION['correcto']); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['estado']) && !empty($_SESSION['estado'])): ?>
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between shadow-xs animate-fade-in">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-xl bg-rose-500 text-white flex items-center justify-center text-sm shadow-xs shrink-0"><i class="fas fa-exclamation-triangle"></i></span>
                    <p class="text-xs sm:text-sm font-bold"><?php echo $_SESSION['estado']; unset($_SESSION['estado']); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if(!$routine_data): ?>
            <!-- Estado vacío: Sin rutina seleccionada -->
            <div class="p-10 text-center bg-white rounded-3xl shadow-sm border border-slate-200 mt-6 max-w-2xl mx-auto">
                <div class="w-20 h-20 bg-amber-50 text-amber-500 rounded-3xl flex items-center justify-center text-3xl mx-auto mb-6 shadow-xs border border-amber-100">
                    <i class="fa-solid fa-puzzle-piece"></i>
                </div>
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">Rutina no encontrada</h2>
                <p class="text-slate-500 mt-2 text-sm font-medium">No se ha podido localizar la rutina especificada para componer la Coach Card.</p>
                <a href="<?php echo $back_url; ?>" class="mt-8 inline-flex items-center gap-2.5 px-6 py-3 bg-slate-800 text-white font-black uppercase text-xs tracking-wider rounded-xl shadow-md hover:bg-black active:scale-[0.98] transition-all">
                    <i class="fas fa-arrow-left"></i> Volver al Listado
                </a>
            </div>
        <?php else: 
            $nombre_modalidad = $routine_data['nombre_modalidad'] ?? 'Rutina';
            if (($routine_data['genero'] ?? '') === 'masculino') {
                $nombre_modalidad .= ' Masc';
            }
            $nombre_categoria = $routine_data['nombre_categoria'] ?? '';
            $dd_declarada = (float)($routine_data['dd_total'] ?? 0);
        ?>

            <!-- Top Header & Actions -->
            <div class="mb-6 sm:mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2.5 text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">
                        <span>Coach Card Composer</span>
                        <span>•</span>
                        <span class="text-blue-600 font-black">Ficha Técnica Oficial</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight flex items-center gap-3">
                        <span class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-lg shadow-xs border border-amber-100">
                            <i class="fa-solid fa-puzzle-piece"></i>
                        </span>
                        Coach Card Composer
                    </h1>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    <!-- PDF Oficial -->
                    <a target="_blank" href="./informes/informe_coach_card.php?titulo=Coach%20Card&id_rutina=<?php echo $id_rutina;?>&id_competicion=<?php echo $id_competicion;?>" 
                       class="px-4 py-2.5 bg-white border border-slate-200 text-slate-700 hover:text-red-600 hover:border-red-200 rounded-xl text-xs font-black uppercase tracking-wider shadow-xs hover:shadow-sm active:scale-[0.98] transition-all flex items-center gap-2">
                        <i class="fas fa-file-pdf text-red-500"></i> Descargar PDF
                    </a>

                    <!-- Auditoría (Solo Jueces / Admin) -->
                    <?php if(!isset($_SESSION['club']) || $_SESSION['club'] == 0): ?>
                        <form target="_blank" action="coach_card_auditor.php" method="POST" class="inline">
                            <input type="hidden" name="id_rutina" value="<?php echo $id_rutina;?>">
                            <button type="submit" name="audit_btn" title="Auditar Normativa AQUA"
                                    class="px-4 py-2.5 bg-amber-500 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-xs hover:bg-amber-600 active:scale-[0.98] transition-all flex items-center gap-2">
                                <i class="fa-solid fa-magnifying-glass"></i> Auditar
                            </button>
                        </form>
                    <?php endif; ?>

                    <!-- Volver -->
                    <a href="<?php echo $back_url; ?>" 
                       class="px-4 py-2.5 bg-slate-100 text-slate-600 hover:bg-slate-200 rounded-xl text-xs font-black uppercase tracking-wider active:scale-[0.98] transition-all flex items-center gap-2">
                        <i class="fas fa-chevron-left"></i> Volver
                    </a>
                </div>
            </div>

            <!-- Ficha Técnica de la Rutina (Tarjeta Principal) -->
            <div class="bg-white rounded-2xl sm:rounded-[2rem] shadow-sm border border-slate-200/80 p-5 sm:p-7 mb-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
                    
                    <!-- Info Routine -->
                    <div class="lg:col-span-8 flex flex-col sm:flex-row items-start sm:items-center gap-4 sm:gap-5">
                        <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col items-center justify-center shrink-0 shadow-xs">
                            <span class="text-[9px] font-black uppercase text-slate-400">Rutina</span>
                            <span class="text-lg sm:text-xl font-black text-slate-800 tabular-nums">#<?php echo $id_rutina; ?></span>
                        </div>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                <span class="px-2.5 py-0.5 rounded-lg bg-blue-50 text-blue-700 text-[10px] font-black uppercase tracking-wider border border-blue-100">
                                    <?php echo htmlspecialchars($nombre_modalidad); ?>
                                </span>
                                <span class="px-2.5 py-0.5 rounded-lg bg-slate-100 text-slate-700 text-[10px] font-black uppercase tracking-wider border border-slate-200">
                                    <?php echo htmlspecialchars($nombre_categoria); ?>
                                </span>
                                <?php if(!empty($routine_data['nombre_club'])): ?>
                                    <span class="px-2.5 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 text-[10px] font-black uppercase tracking-wider border border-emerald-100">
                                        <?php echo htmlspecialchars($routine_data['nombre_club']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <h2 class="text-base sm:text-lg font-black text-slate-800 tracking-tight truncate">
                                <?php echo !empty($nombres_atletas) ? htmlspecialchars($nombres_atletas) : 'Sin deportistas asignadas'; ?>
                            </h2>
                        </div>
                    </div>

                    <!-- KPI DD Total -->
                    <div class="lg:col-span-4 flex items-center justify-start lg:justify-end">
                        <div class="w-full sm:w-auto px-5 py-3.5 bg-gradient-to-br from-blue-600 to-indigo-700 text-white rounded-2xl shadow-md shadow-blue-500/20 flex items-center justify-between sm:justify-start gap-4">
                            <div>
                                <p class="text-[9px] font-black uppercase text-blue-200 tracking-widest">Dificultad Declarada</p>
                                <p class="text-2xl sm:text-3xl font-black tabular-nums tracking-tight"><?php echo number_format($dd_declarada, 3); ?></p>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-lg">
                                <i class="fa-solid fa-chart-line"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sub-barra: Temática & Botón Añadir Transición -->
                <div class="mt-6 pt-6 border-t border-slate-100 grid grid-cols-1 md:grid-cols-12 gap-3 sm:gap-4 items-center">
                    <form action="coach_card_composer_code.php" method="POST" class="md:col-span-8 flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                        <input type="hidden" name="id_rutina" value="<?php echo $id_rutina; ?>">
                        <input type="hidden" name="id_fase" value="<?php echo $id_fase; ?>">
                        <div class="relative flex-1">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"><i class="fas fa-music"></i></span>
                            <input type="text" name="tematica" value="<?php echo htmlspecialchars($routine_data['tematica'] ?? ''); ?>" 
                                   placeholder="Tema musical de la rutina (ej: El Lago de los Cisnes)" 
                                   class="w-full pl-9 pr-4 py-2 sm:py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-bold text-slate-800 placeholder-slate-400 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 transition-all">
                        </div>
                        <button type="submit" name="update_tematica_btn" class="px-4 py-2 sm:py-2.5 bg-slate-800 hover:bg-black text-white text-[11px] font-black uppercase tracking-wider rounded-xl active:scale-[0.98] transition-all shadow-xs shrink-0">
                            Guardar Tema
                        </button>
                    </form>

                    <div class="md:col-span-4 flex justify-end">
                        <button onclick="toggleTransitionModal()" class="w-full sm:w-auto px-4 py-2 sm:py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-black uppercase tracking-wider rounded-xl shadow-md shadow-emerald-500/20 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-plus text-xs"></i> Añadir Transición
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tabla de Composición de Elementos -->
            <div class="bg-white rounded-2xl sm:rounded-[2rem] shadow-sm border border-slate-200/80 overflow-hidden mb-8">
                
                <div class="p-4 sm:p-6 border-b border-slate-100 flex items-center justify-between gap-4 bg-slate-50/50">
                    <h3 class="text-sm sm:text-base font-black text-slate-800 flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs"><i class="fas fa-list-ol"></i></span>
                        Elementos Declarados (1 a <?php echo $elementos_coach_card; ?>)
                    </h3>
                    <span class="px-3 py-1 rounded-lg bg-white border border-slate-200 text-slate-500 text-[10px] font-black uppercase tracking-wider shadow-xs tabular-nums">
                        <?php echo $elementos_coach_card; ?> Elementos Reglamentarios
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 text-[10px] font-black uppercase tracking-wider text-slate-400 border-b border-slate-100">
                                <th class="py-3 px-4 text-center w-12">#</th>
                                <th class="py-3 px-4 text-center w-24">Inicio</th>
                                <th class="py-3 px-4 text-center w-24">Fin</th>
                                <th class="py-3 px-4 w-36">Tipo (Part)</th>
                                <th class="py-3 px-4 w-40">Basemark</th>
                                <th class="py-3 px-6">Dificultad Declarada (DD)</th>
                                <th class="py-3 px-4 text-right w-28">Total</th>
                                <th class="py-3 px-4 text-center w-20">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            <?php
                            $accumulated_dd = 0;
                            for ($i = 1; $i <= $elementos_coach_card; $i++):
                                
                                // 1. Verificar si existe transición antes del elemento $i
                                $q_trans = mysqli_query($connection, "SELECT hr.id, hr.elemento, th.nombre, th.color 
                                                                       FROM hibridos_rutina hr 
                                                                       JOIN tipo_hibridos th ON hr.texto = th.id 
                                                                       WHERE hr.tipo = 'part' AND hr.texto = '3' AND hr.id_rutina = '$id_rutina' AND hr.elemento = '$i'");
                                while ($trans = mysqli_fetch_assoc($q_trans)):
                            ?>
                                    <tr class="bg-emerald-50/60 border-y border-emerald-100">
                                        <td class="py-2.5 px-4 text-center">
                                            <span class="w-6 h-6 rounded-md bg-emerald-500 text-white flex items-center justify-center text-[10px] font-black mx-auto shadow-xs">
                                                <i class="fas fa-arrows-split-up-and-left"></i>
                                            </span>
                                        </td>
                                        <td colspan="6" class="py-2.5 px-4 font-black uppercase text-[11px] text-emerald-800 tracking-wider">
                                            Transición Declarada (Antes del elemento #<?php echo $i; ?>)
                                        </td>
                                        <td class="py-2.5 px-4 text-center">
                                            <form action="coach_card_composer_code.php" method="POST" class="inline" onsubmit="return confirm('¿Eliminar esta transición?');">
                                                <input type="hidden" name="id_rutina" value="<?php echo $id_rutina; ?>">
                                                <input type="hidden" name="id_fase" value="<?php echo $id_fase; ?>">
                                                <input type="hidden" name="elemento" value="<?php echo $i; ?>">
                                                <button type="submit" name="dlt_btn_transicion" class="w-8 h-8 rounded-lg bg-white border border-rose-200 text-rose-500 hover:bg-rose-500 hover:text-white flex items-center justify-center transition-all shadow-xs" title="Eliminar transición">
                                                    <i class="fas fa-trash text-xs"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                            <?php 
                                endwhile;

                                // 2. Tiempos Inicio y Fin
                                $q_ti = mysqli_query($connection, "SELECT texto FROM hibridos_rutina WHERE tipo='time_inicio' AND id_rutina='$id_rutina' AND elemento='$i'");
                                $time_inicio = ($q_ti && $r = mysqli_fetch_assoc($q_ti)) ? trim($r['texto']) : '';

                                $q_tf = mysqli_query($connection, "SELECT texto FROM hibridos_rutina WHERE tipo='time_fin' AND id_rutina='$id_rutina' AND elemento='$i'");
                                $time_fin = ($q_tf && $r = mysqli_fetch_assoc($q_tf)) ? trim($r['texto']) : '';

                                // 3. Tipo de híbrido (Part != 3)
                                $q_part = mysqli_query($connection, "SELECT th.nombre, th.color, th.id as id_th 
                                                                      FROM hibridos_rutina hr 
                                                                      JOIN tipo_hibridos th ON hr.texto = th.id 
                                                                      WHERE hr.tipo = 'part' AND hr.texto <> '3' AND hr.id_rutina = '$id_rutina' AND hr.elemento = '$i'");
                                $part_info = ($q_part && mysqli_num_rows($q_part) > 0) ? mysqli_fetch_assoc($q_part) : null;
                                $id_tipo_hibrido = $part_info['id_th'] ?? 1;

                                // 4. Basemark
                                $q_bm = mysqli_query($connection, "SELECT texto, valor FROM hibridos_rutina WHERE tipo='basemark' AND id_rutina='$id_rutina' AND elemento='$i'");
                                $bm_info = ($q_bm && $r = mysqli_fetch_assoc($q_bm)) ? $r : null;
                                $nombre_basemark = $bm_info['texto'] ?? '';
                                $valor_basemark  = (float)($bm_info['valor'] ?? 0);

                                // 5. DD Componentes
                                $q_dds = mysqli_query($connection, "SELECT texto, valor FROM hibridos_rutina WHERE tipo='dd' AND id_rutina='$id_rutina' AND elemento='$i' AND texto != '' AND texto IS NOT NULL");
                                $dd_items = [];
                                while ($d_row = mysqli_fetch_assoc($q_dds)) {
                                    $dd_items[] = $d_row;
                                }

                                // 6. Total del elemento
                                $q_tot = mysqli_query($connection, "SELECT valor FROM hibridos_rutina WHERE tipo='total' AND id_rutina='$id_rutina' AND elemento='$i'");
                                $elem_total = ($q_tot && $r = mysqli_fetch_assoc($q_tot)) ? (float)$r['valor'] : 0;
                                $accumulated_dd += $elem_total;
                            ?>
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    
                                    <!-- # Index -->
                                    <td class="py-3.5 px-4 text-center font-black text-slate-700 tabular-nums">
                                        <span class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center mx-auto text-xs font-black">
                                            <?php echo $i; ?>
                                        </span>
                                    </td>

                                    <!-- Time Inicio -->
                                    <td class="py-3.5 px-4 text-center">
                                        <?php if(!empty($time_inicio)): ?>
                                            <span class="px-2.5 py-1 bg-slate-100 rounded-md font-mono text-[11px] font-bold text-slate-700 tabular-nums"><?php echo htmlspecialchars($time_inicio); ?></span>
                                        <?php else: ?>
                                            <span class="text-slate-300 font-mono text-xs">--:--</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Time Fin -->
                                    <td class="py-3.5 px-4 text-center">
                                        <?php if(!empty($time_fin)): ?>
                                            <span class="px-2.5 py-1 bg-slate-100 rounded-md font-mono text-[11px] font-bold text-slate-700 tabular-nums"><?php echo htmlspecialchars($time_fin); ?></span>
                                        <?php else: ?>
                                            <span class="text-slate-300 font-mono text-xs">--:--</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Tipo Part -->
                                    <td class="py-3.5 px-4">
                                        <?php if($part_info): 
                                            $pill_style = 'bg-slate-100 text-slate-700 border-slate-200';
                                            if ($part_info['id_th'] == 1) $pill_style = 'bg-sky-50 text-sky-700 border-sky-200';
                                            if ($part_info['id_th'] == 2) $pill_style = 'bg-amber-50 text-amber-700 border-amber-200';
                                            if ($part_info['id_th'] == 4) $pill_style = 'bg-rose-50 text-rose-700 border-rose-200';
                                        ?>
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider border <?php echo $pill_style; ?>">
                                                <?php echo htmlspecialchars($part_info['nombre']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-[10px] font-bold uppercase text-slate-400 italic">Sin definir</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Basemark -->
                                    <td class="py-3.5 px-4">
                                        <?php if(!empty($nombre_basemark)): ?>
                                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-200 text-slate-800 text-[11px] font-black tabular-nums">
                                                <span><?php echo htmlspecialchars($nombre_basemark); ?></span>
                                                <?php if($valor_basemark > 0): ?>
                                                    <span class="text-slate-500 font-medium">(+<?php echo number_format($valor_basemark, 2); ?>)</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-slate-300 text-xs">--</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- DD Components List -->
                                    <td class="py-3.5 px-6">
                                        <?php if(!empty($dd_items)): ?>
                                            <div class="flex flex-wrap items-center gap-1.5">
                                                <?php foreach($dd_items as $dd): ?>
                                                    <span class="px-2 py-0.5 rounded-md bg-blue-50/70 border border-blue-100 text-blue-900 text-[10px] font-black tracking-tight tabular-nums">
                                                        <?php echo htmlspecialchars($dd['texto']); ?>
                                                        <?php if((float)$dd['valor'] > 0): ?>
                                                            <span class="text-blue-500 font-bold ml-0.5">+<?php echo number_format((float)$dd['valor'], 2); ?></span>
                                                        <?php endif; ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-slate-300 text-xs italic">Sin elementos declarados</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Total DD -->
                                    <td class="py-3.5 px-4 text-right">
                                        <span class="text-xs sm:text-sm font-black tabular-nums <?php echo ($elem_total > 0) ? 'text-blue-600' : 'text-slate-400'; ?>">
                                            <?php echo number_format($elem_total, 3); ?>
                                        </span>
                                    </td>

                                    <!-- Acción Editar -->
                                    <td class="py-3.5 px-4 text-center">
                                        <form action="coach_card_composer_elemento_edit.php" method="POST" class="inline">
                                            <input type="hidden" name="edit_elemento" value="<?php echo $i; ?>">
                                            <input type="hidden" name="id_tipo_hibrido" value="<?php echo $id_tipo_hibrido; ?>">
                                            <input type="hidden" name="tipo_acro" value="<?php echo htmlspecialchars($nombre_basemark); ?>">
                                            <input type="hidden" name="id_rutina" value="<?php echo $id_rutina; ?>">
                                            <input type="hidden" name="id_fase" value="<?php echo $id_fase; ?>">
                                            <button type="submit" name="edit_btn" 
                                                    class="w-9 h-9 rounded-xl bg-blue-50 hover:bg-blue-600 text-blue-600 hover:text-white flex items-center justify-center transition-all shadow-xs active:scale-[0.98]" 
                                                    title="Editar Elemento #<?php echo $i; ?>">
                                                <i class="fas fa-edit text-xs"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                        <tfoot>
                            <tr class="bg-slate-50 border-t border-slate-200/80 font-black">
                                <td colspan="6" class="py-4 px-6 text-right uppercase text-xs tracking-wider text-slate-500">
                                    Sumatorio Total Dificultad Declarada:
                                </td>
                                <td class="py-4 px-4 text-right text-base font-black text-blue-600 tabular-nums">
                                    <?php echo number_format($accumulated_dd, 3); ?>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Modal: Añadir Transición -->
            <div id="modalTransicion" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 animate-fade-in">
                <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm border border-emerald-100"><i class="fas fa-plus"></i></span>
                            <h3 class="text-base font-black text-slate-800">Añadir Transición</h3>
                        </div>
                        <button onclick="toggleTransitionModal()" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-400 hover:text-slate-600 flex items-center justify-center transition-colors">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>

                    <form action="coach_card_composer_code.php" method="POST" class="p-6 space-y-4">
                        <input type="hidden" name="id_rutina" value="<?php echo $id_rutina; ?>">
                        <input type="hidden" name="id_fase" value="<?php echo $id_fase; ?>">

                        <div>
                            <label class="text-[10px] font-black uppercase text-slate-400 tracking-wider mb-1.5 block">Posición de inserción</label>
                            <p class="text-xs text-slate-500 mb-3">Selecciona el elemento antes del cual se intercalará la nueva transición coreográfica:</p>
                            
                            <div class="relative">
                                <select name="elemento_transicion" class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-xs font-black text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 transition-all">
                                    <?php for($x = 1; $x <= $elementos_coach_card; $x++): ?>
                                        <option value="<?php echo $x; ?>">Antes del elemento #<?php echo $x; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                            <button type="button" onclick="toggleTransitionModal()" class="px-5 py-2.5 rounded-xl bg-slate-100 text-slate-600 text-xs font-black uppercase tracking-wider hover:bg-slate-200 active:scale-[0.98] transition-all">
                                Cancelar
                            </button>
                            <button type="submit" name="save_btn" class="px-6 py-2.5 rounded-xl bg-emerald-600 text-white text-xs font-black uppercase tracking-wider shadow-md shadow-emerald-500/20 hover:bg-emerald-700 active:scale-[0.98] transition-all">
                                Guardar Transición
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
            function toggleTransitionModal() {
                const modal = document.getElementById('modalTransicion');
                modal.classList.toggle('hidden');
            }
            </script>

        <?php endif; ?>

    </div>
</main>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
