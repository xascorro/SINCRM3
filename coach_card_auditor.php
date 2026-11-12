<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');

$id_rutina = (int)($_POST['id_rutina'] ?? ($_GET['id_rutina'] ?? 0));
$id_comp = (int)($_SESSION['id_competicion_usuario'] ?? ($_SESSION['id_competicion_activa'] ?? 0));

// Restricción por rol de club
$club_filter = "";
if ($_SESSION['id_rol'] == 5) {
    $user_club = (int)$_SESSION['club'];
    $club_filter = " AND r.id_club = $user_club";
}

// Cargar mapa de familias de híbridos
$mapa_familias = [];
$q_fam = mysqli_query($connection, "SELECT codigo, agrupar FROM dificultad_hibridos");
if ($q_fam) {
    while ($f = mysqli_fetch_assoc($q_fam)) {
        $c = trim($f['codigo'] ?? '');
        $a = trim($f['agrupar'] ?? '');
        if ($c !== '') $mapa_familias[$c] = $a;
    }
}

// Cargar catálogo de acrobacias
$mapa_acrobacias = [];
$q_acro = mysqli_query($connection, "SELECT codigo, agrupar, subagrupar FROM dificultad_acrobacias");
if ($q_acro) {
    while ($a = mysqli_fetch_assoc($q_acro)) {
        $c = trim($a['codigo'] ?? '');
        if ($c !== '') $mapa_acrobacias[$c] = $a;
    }
}
?>

<main class="flex-1 flex flex-col min-w-0 bg-surface">
    <?php include('includes/topbar.php'); ?>

    <div class="px-3 py-4 sm:p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend text-primary">

        <?php if ($id_rutina === 0): ?>
            <!-- ========================================================================= -->
            <!-- VISTA 1: SELECTOR DE RUTINAS PARA AUDITAR (LANDING / DIRECTORIO)         -->
            <!-- ========================================================================= -->
            <?php
            // Obtener competición activa
            $q_comp = mysqli_query($connection, "SELECT id, nombre, lugar, fecha FROM competiciones WHERE id = '$id_comp' LIMIT 1");
            $comp_info = mysqli_fetch_assoc($q_comp);

            // Obtener rutinas de la competición con datos técnicos
            $q_rutinas = "SELECT r.id, r.tematica, r.dd_total, r.id_fase, r.id_club,
                                 cl.nombre_corto as nombre_club, cl.logo as logo_club,
                                 m.nombre as nombre_modalidad, c.nombre as nombre_categoria,
                                 f.elementos_coach_card, f.genero,
                                 rc.max_hibridos, rc.max_acrobacias, rc.max_tre, rc.req_hibrido_creativo,
                                 (SELECT COUNT(*) FROM hibridos_rutina hr WHERE hr.id_rutina = r.id AND hr.tipo = 'part' AND hr.texto != '3') as total_partes,
                                 (SELECT COUNT(DISTINCT elemento) FROM hibridos_rutina hr WHERE hr.id_rutina = r.id) as total_elementos
                          FROM rutinas r
                          JOIN fases f ON r.id_fase = f.id
                          LEFT JOIN modalidades m ON f.id_modalidad = m.id
                          LEFT JOIN categorias c ON f.id_categoria = c.id
                          LEFT JOIN clubes cl ON r.id_club = cl.id
                          LEFT JOIN reglas_competicion rc ON f.id_categoria = rc.id_categoria AND f.id_modalidad = rc.id_modalidad
                          WHERE r.id_competicion = '$id_comp' $club_filter
                          ORDER BY f.orden ASC, r.orden ASC, r.id ASC";
            $res_rutinas = mysqli_query($connection, $q_rutinas);
            $rutinas_data = [];
            while ($r_row = mysqli_fetch_assoc($res_rutinas)) {
                $rutinas_data[] = $r_row;
            }
            ?>

            <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">
                        <span>Mesa Técnica DTC</span>
                        <span>•</span>
                        <span class="text-blue-600 font-black">Auditoría Reglamentaria</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight flex items-center gap-3">
                        <span class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-lg shadow-xs border border-amber-100">
                            <i class="fa-solid fa-microscope"></i>
                        </span>
                        Auditor de Coach Cards
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 font-medium mt-1">
                        Verificación técnica automática bajo normativa oficial World Aquatics (AQUA 2024–2026) y RFEN.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <span class="px-3.5 py-1.5 rounded-xl bg-white border border-slate-200 text-slate-600 text-xs font-black uppercase tracking-wider shadow-xs flex items-center gap-2">
                        <i class="fas fa-trophy text-amber-500"></i>
                        <?php echo htmlspecialchars($comp_info['nombre'] ?? 'Competición #' . $id_comp); ?>
                    </span>
                    <a href="inscripciones.php?tab=rutinas" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-black uppercase tracking-wider rounded-xl transition-all flex items-center gap-2">
                        <i class="fas fa-chevron-left"></i> Volver
                    </a>
                </div>
            </div>

            <!-- Buscador Dinámico y Filtros Rápidos -->
            <div class="mb-6 bg-white p-4 rounded-2xl shadow-sm border border-slate-200 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="relative w-full md:w-80">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"><i class="fas fa-search"></i></span>
                    <input type="text" id="filterRutinasAuditor" placeholder="Buscar por club, modalidad, ID o tema..." 
                           class="w-full pl-9 pr-4 py-2 bg-slate-50 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 focus:bg-white focus:border-blue-500 transition-all">
                </div>

                <!-- Chips de filtro por estado -->
                <div class="flex items-center gap-2 flex-wrap">
                    <button type="button" onclick="filterByState('all')" class="chip-filter active px-3 py-1.5 rounded-xl text-xs font-black uppercase tracking-wider bg-slate-800 text-white transition-all" data-filter="all">
                        Todas (<?php echo count($rutinas_data); ?>)
                    </button>
                    <button type="button" onclick="filterByState('compuesta')" class="chip-filter px-3 py-1.5 rounded-xl text-xs font-black uppercase tracking-wider bg-slate-100 text-slate-600 hover:bg-slate-200 transition-all" data-filter="compuesta">
                        Con Declaración
                    </button>
                    <button type="button" onclick="filterByState('pendiente')" class="chip-filter px-3 py-1.5 rounded-xl text-xs font-black uppercase tracking-wider bg-slate-100 text-slate-600 hover:bg-slate-200 transition-all" data-filter="pendiente">
                        Pendientes
                    </button>
                </div>
            </div>

            <!-- Listado de Rutinas para Auditar -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="rutinasGridAuditor">
                <?php foreach ($rutinas_data as $r): 
                    $mod_title = $r['nombre_modalidad'];
                    if (($r['genero'] ?? '') === 'masculino') $mod_title .= ' Masc';
                    $dd_val = (float)($r['dd_total'] ?? 0);
                    $has_elements = ((int)$r['total_elementos'] > 0);
                    $state_class = $has_elements ? 'compuesta' : 'pendiente';
                ?>
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200 hover:border-blue-300 transition-all flex flex-col justify-between gap-4 card-rutina-auditor"
                         data-search="<?php echo strtolower($r['id'].' '.$mod_title.' '.$r['nombre_categoria'].' '.$r['nombre_club'].' '.$r['tematica']); ?>"
                         data-state="<?php echo $state_class; ?>">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <span class="px-2.5 py-0.5 rounded-lg bg-slate-100 text-slate-700 text-[10px] font-black uppercase tracking-wider border border-slate-200">
                                    #<?php echo $r['id']; ?> · <?php echo htmlspecialchars($r['nombre_club'] ?? 'Club'); ?>
                                </span>
                                <span class="px-2.5 py-0.5 rounded-lg <?php echo $has_elements ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200'; ?> text-[10px] font-black uppercase tracking-wider border">
                                    <?php echo $has_elements ? 'Compuesta (' . $r['total_elementos'] . ' elems)' : 'Pendiente'; ?>
                                </span>
                            </div>

                            <h3 class="text-sm font-black text-slate-800">
                                <?php echo htmlspecialchars($mod_title . ' ' . $r['nombre_categoria']); ?>
                            </h3>
                            <p class="text-xs text-slate-500 font-medium italic mt-0.5">
                                <?php echo !empty($r['tematica']) ? '“' . htmlspecialchars($r['tematica']) . '”' : 'Sin temática declarada'; ?>
                            </p>
                        </div>

                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-3">
                            <div>
                                <span class="text-[9px] font-black uppercase text-slate-400 block tracking-widest">DD Declarada</span>
                                <span class="text-base font-black text-blue-600 tabular-nums">
                                    <?php echo number_format($dd_val, 3); ?>
                                </span>
                            </div>

                            <div class="flex items-center gap-2">
                                <a href="coach_card_composer.php?id_rutina=<?php echo $r['id']; ?>" 
                                   class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all text-xs" title="Editar en Composer">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="coach_card_auditor.php?id_rutina=<?php echo $r['id']; ?>" 
                                   class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-black text-xs uppercase tracking-wider rounded-xl shadow-md shadow-blue-500/20 active:scale-[0.98] transition-all flex items-center gap-1.5">
                                    <i class="fa-solid fa-microscope text-xs"></i> Auditar
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <script>
            function filterByState(state) {
                document.querySelectorAll('.chip-filter').forEach(btn => {
                    if (btn.dataset.filter === state) {
                        btn.classList.add('bg-slate-800', 'text-white');
                        btn.classList.remove('bg-slate-100', 'text-slate-600');
                    } else {
                        btn.classList.remove('bg-slate-800', 'text-white');
                        btn.classList.add('bg-slate-100', 'text-slate-600');
                    }
                });

                const term = document.getElementById('filterRutinasAuditor').value.toLowerCase().trim();
                const cards = document.querySelectorAll('.card-rutina-auditor');
                cards.forEach(c => {
                    const txt = c.getAttribute('data-search') || '';
                    const cState = c.getAttribute('data-state') || '';
                    const matchesSearch = txt.includes(term);
                    const matchesState = (state === 'all') || (cState === state);
                    c.style.display = (matchesSearch && matchesState) ? '' : 'none';
                });
            }

            document.getElementById('filterRutinasAuditor').addEventListener('input', function() {
                const term = this.value.toLowerCase().trim();
                const activeChip = document.querySelector('.chip-filter.bg-slate-800');
                const state = activeChip ? activeChip.dataset.filter : 'all';
                const cards = document.querySelectorAll('.card-rutina-auditor');
                cards.forEach(c => {
                    const txt = c.getAttribute('data-search') || '';
                    const cState = c.getAttribute('data-state') || '';
                    const matchesSearch = txt.includes(term);
                    const matchesState = (state === 'all') || (cState === state);
                    c.style.display = (matchesSearch && matchesState) ? '' : 'none';
                });
            });
            </script>

        <?php else: ?>
            <!-- ========================================================================= -->
            <!-- VISTA 2: INFORME DE AUDITORÍA COMPLETO DE UNA RUTINA                      -->
            <!-- ========================================================================= -->
            <?php
            // 1. Obtener información de la rutina y sus reglas
            $q_info = "SELECT r.*, c.nombre as categoria, m.nombre as modalidad, cl.nombre_corto as nombre_club, cl.logo as logo_club,
                              f.elementos_coach_card as f_elementos_cc, f.genero, f.duracion_musica as f_duracion, f.margen_musica as f_margen,
                              rc.max_hibridos, rc.max_acrobacias, rc.max_tre, rc.req_hibrido_creativo, rc.max_apnea,
                              rc.elementos_coach_card as rc_elementos_cc,
                              rc.duracion_musica as rc_duracion, rc.margen_musica as rc_margen
                       FROM rutinas r 
                       LEFT JOIN fases f ON r.id_fase = f.id
                       LEFT JOIN categorias c ON f.id_categoria = c.id 
                       LEFT JOIN modalidades m ON f.id_modalidad = m.id 
                       LEFT JOIN clubes cl ON r.id_club = cl.id
                       LEFT JOIN reglas_competicion rc ON f.id_categoria = rc.id_categoria AND f.id_modalidad = rc.id_modalidad
                       WHERE r.id = '$id_rutina' $club_filter";
            $res_info = mysqli_query($connection, $q_info);
            $rutina_info = mysqli_fetch_assoc($res_info);

            if (!$rutina_info) {
                echo "<div class='p-10 text-center bg-white rounded-3xl shadow-sm border border-slate-200'>
                        <h2 class='text-xl font-black text-rose-600 mb-2'>Acceso Denegado o Rutina no encontrada</h2>
                        <p class='text-xs text-slate-500 font-medium mb-6'>No tienes permisos para auditar esta rutina o no existe.</p>
                        <a href='coach_card_auditor.php' class='px-6 py-2.5 bg-slate-800 text-white font-black text-xs uppercase rounded-xl'>Volver al Listado</a>
                      </div>";
                echo "</div></main>";
                include('includes/footer.php');
                include('includes/scripts.php');
                exit();
            }

            // Duraciones y tolerancias oficiales
            $duracion_esperada = (int)($rutina_info['rc_duracion'] ?? ($rutina_info['f_duracion'] ?? 0));
            $margen_esperado = (int)($rutina_info['rc_margen'] ?? ($rutina_info['f_margen'] ?? 5));
            $elementos_max = (int)($rutina_info['f_elementos_cc'] ?? ($rutina_info['rc_elementos_cc'] ?? 0));
            $max_apnea_seg = (float)($rutina_info['max_apnea'] ?? 18.99);
            if ($max_apnea_seg <= 0) $max_apnea_seg = 18.99;

            // Nadadoras participantes
            $q_parts = "SELECT concat(n.nombre, ' ', n.apellidos) as nom_completo, rp.reserva 
                        FROM rutinas_participantes rp 
                        JOIN nadadoras n ON rp.id_nadadora = n.id 
                        WHERE rp.id_rutina = '$id_rutina' 
                        ORDER BY rp.reserva ASC, n.apellidos ASC";
            $res_parts = mysqli_query($connection, $q_parts);
            $atletas = [];
            while ($p = mysqli_fetch_assoc($res_parts)) {
                $atletas[] = $p['nom_completo'] . ($p['reserva'] === 'si' ? ' (R)' : '');
            }

            // 2. Extraer elementos de hibridos_rutina
            $q_elementos = "SELECT * FROM hibridos_rutina WHERE id_rutina = $id_rutina ORDER BY elemento ASC, id ASC";
            $res_elementos = mysqli_query($connection, $q_elementos);

            $elementos = [];
            $totales = ['HYBRID' => 0, 'ACRO' => 0, 'TRE' => 0, 'CREATIVO' => 0];
            $transiciones_count = 0;
            $acro_declaradas = [];
            $familias_rutina_global = [];
            $penalizaciones_rutina = []; // Penalizaciones que restan puntos al total (Deductions)
            $riesgos_basemark = [];      // Infracciones internas que conllevan Base Mark en ese elemento (DTC BM)
            $advertencias = [];          // Observaciones para la mesa técnica
            $penalizacion_pts = 0.0;     // Sumatorio total de puntos de penalización de rutina

            while ($row = mysqli_fetch_assoc($res_elementos)) {
                $num_elem = (int)$row['elemento'];

                if (!isset($elementos[$num_elem])) {
                    $elementos[$num_elem] = [
                        'tipo_general' => 'Desconocido',
                        'dificultades' => [],
                        'familias' => [],
                        'dd_total' => 0.0,
                        'basemark' => 0.0,
                        'nombre_basemark' => '',
                        'grupo_acro' => '',
                        'time_inicio' => '',
                        'time_fin' => '',
                        'duracion_seg' => 0,
                        'is_creativo' => false,
                        'infracciones_elem' => [],
                        'avisos_elem' => []
                    ];
                }

                // Tiempos
                if ($row['tipo'] == 'time_inicio' && !empty($row['texto'])) {
                    $elementos[$num_elem]['time_inicio'] = trim($row['texto']);
                }
                if ($row['tipo'] == 'time_fin' && !empty($row['texto'])) {
                    $elementos[$num_elem]['time_fin'] = trim($row['texto']);
                }

                // Transiciones
                if ($row['tipo'] == 'part' && $row['texto'] == '3') {
                    $transiciones_count++;
                }

                // Basemark y Clasificación
                if ($row['tipo'] == 'basemark' && !empty($row['texto'])) {
                    $txt_bm = trim($row['texto']);
                    $elementos[$num_elem]['nombre_basemark'] = $txt_bm;
                    $elementos[$num_elem]['basemark'] = (float)($row['valor'] ?? 0);

                    if ($txt_bm == 'HYBRID') {
                        $elementos[$num_elem]['tipo_general'] = 'HYBRID';
                    } elseif (strpos($txt_bm, 'ACRO') !== false || strpos($txt_bm, 'Grupo') !== false) {
                        $elementos[$num_elem]['tipo_general'] = 'ACROBATIC';
                        $elementos[$num_elem]['grupo_acro'] = $txt_bm;
                    } elseif ($txt_bm == 'TRE') {
                        $elementos[$num_elem]['tipo_general'] = 'TRE';
                    }
                }

                // Si basemark estuviese sin definir, inferir de tipo part
                if ($row['tipo'] == 'part' && $row['texto'] != '3' && $elementos[$num_elem]['tipo_general'] == 'Desconocido') {
                    if ($row['texto'] == '1') $elementos[$num_elem]['tipo_general'] = 'HYBRID';
                    elseif ($row['texto'] == '4') $elementos[$num_elem]['tipo_general'] = 'ACROBATIC';
                    elseif ($row['texto'] == '2') $elementos[$num_elem]['tipo_general'] = 'TRE';
                }

                // Detectar TRE en DD
                if ($row['tipo'] == 'dd' && !empty($row['texto']) && strpos($row['texto'], 'TRE') !== false) {
                    $elementos[$num_elem]['tipo_general'] = 'TRE';
                    $elementos[$num_elem]['dificultades'][] = trim($row['texto']);
                }

                // DDs de Híbridos / Acrobacias
                if ($row['tipo'] == 'dd' && !empty($row['texto']) && strpos($row['texto'], 'TRE') === false) {
                    $codigo = trim($row['texto']);
                    if ($codigo == 'ShoHY' || $codigo == 'Creativo' || strpos(strtolower($codigo), 'hy') !== false) {
                        $elementos[$num_elem]['is_creativo'] = true;
                    } else {
                        $elementos[$num_elem]['dificultades'][] = $codigo;
                        $familia = $mapa_familias[$codigo] ?? 'Otra';
                        $ignorar = ['Bonus', 'Movimientos', 'Apnea', 'Creativo', 'no', '', 'Otra'];
                        if (!in_array($familia, $ignorar)) {
                            $elementos[$num_elem]['familias'][$familia] = ($elementos[$num_elem]['familias'][$familia] ?? 0) + 1;
                            $familias_rutina_global[$familia] = true;
                        }
                    }
                }

                // Total de DD
                if ($row['tipo'] == 'total') {
                    $elementos[$num_elem]['dd_total'] = (float)($row['valor'] ?? 0);
                }
            }

            // Calcular duraciones de tiempo por elemento
            foreach ($elementos as $n => &$el) {
                if (!empty($el['time_inicio']) && !empty($el['time_fin'])) {
                    $parts_ini = explode(':', $el['time_inicio']);
                    $parts_fin = explode(':', $el['time_fin']);
                    if (count($parts_ini) == 2 && count($parts_fin) == 2) {
                        $s_ini = (int)$parts_ini[0] * 60 + (int)$parts_ini[1];
                        $s_fin = (int)$parts_fin[0] * 60 + (int)$parts_fin[1];
                        $el['duracion_seg'] = max(0, $s_fin - $s_ini);
                    }
                }
            }
            unset($el);

            $max_hib = (int)($rutina_info['max_hibridos'] ?? 0);
            $max_acro = (int)($rutina_info['max_acrobacias'] ?? 0);
            $max_tre = (int)($rutina_info['max_tre'] ?? 0);
            $req_creat = (int)($rutina_info['req_hibrido_creativo'] ?? 0);
            $is_tecnica = (strpos(strtolower($rutina_info['modalidad'] ?? ''), 'técnic') !== false);
            $is_acrobatica = (strpos(strtolower($rutina_info['modalidad'] ?? ''), 'acrob') !== false);
            $is_alevin = (strpos(strtolower($rutina_info['categoria'] ?? ''), 'alev') !== false);
            $is_infantil = (strpos(strtolower($rutina_info['categoria'] ?? ''), 'infant') !== false);

            // Reclasificación inteligente de Híbrido Creativo:
            // Si la categoría exige creativo y no hay ShoHY explícito, pero hay un híbrido con DD=0.50
            if ($req_creat == 1) {
                $has_creat = false;
                foreach ($elementos as $el) {
                    if ($el['is_creativo']) { $has_creat = true; break; }
                }
                if (!$has_creat) {
                    // Buscar el último híbrido si su DD es 0.50 o está vacío
                    $last_hyb_idx = 0;
                    foreach ($elementos as $n => $el) {
                        if ($el['tipo_general'] == 'HYBRID') $last_hyb_idx = $n;
                    }
                    if ($last_hyb_idx > 0 && ($elementos[$last_hyb_idx]['dd_total'] == 0.500 || count($elementos[$last_hyb_idx]['dificultades']) == 0)) {
                        $elementos[$last_hyb_idx]['is_creativo'] = true;
                    }
                }
            }

            // Conteo estructural
            foreach ($elementos as $n => $elem) {
                if ($elem['tipo_general'] == 'HYBRID') {
                    if ($elem['is_creativo']) $totales['CREATIVO']++;
                    else $totales['HYBRID']++;
                } elseif ($elem['tipo_general'] == 'ACROBATIC') {
                    $totales['ACRO']++;
                    $acro_ident = !empty($elem['grupo_acro']) ? $elem['grupo_acro'] : (!empty($elem['dificultades']) ? implode('-', $elem['dificultades']) : "Acro #$n");
                    $acro_declaradas[] = ['elemento' => $n, 'id' => $acro_ident];
                } elseif ($elem['tipo_general'] == 'TRE') {
                    $totales['TRE']++;
                }
            }

            $total_elementos_declarados = $totales['HYBRID'] + $totales['CREATIVO'] + $totales['ACRO'] + $totales['TRE'];

            // =========================================================================
            // VALIDACIÓN REGLAMENTARIA OFICIAL WORLD AQUATICS (AQUA 2024–2026) / RFEN
            // =========================================================================

            // 1. PENALIZACIONES DE RUTINA: Exceso de Híbridos Libres (-2.0 pts por elemento extra) [AS 6.7.8]
            if ($max_hib > 0 && $totales['HYBRID'] > $max_hib) {
                $exceso = $totales['HYBRID'] - $max_hib;
                $pen = $exceso * 2.0;
                $penalizacion_pts += $pen;
                $penalizaciones_rutina[] = [
                    'regla' => 'AS 6.7.8',
                    'texto' => "Exceso de Híbridos Libres declarados: <b>{$totales['HYBRID']}</b> de máximo <b>$max_hib</b> autorizados.",
                    'puntos' => -$pen
                ];
            }

            // 2. PENALIZACIONES DE RUTINA: Exceso de Acrobacias (-2.0 pts por elemento extra) [AS 6.7.8]
            if ($max_acro > 0 && $totales['ACRO'] > $max_acro) {
                $exceso = $totales['ACRO'] - $max_acro;
                $pen = $exceso * 2.0;
                $penalizacion_pts += $pen;
                $penalizaciones_rutina[] = [
                    'regla' => 'AS 6.7.8',
                    'texto' => "Exceso de Acrobacias declaradas: <b>{$totales['ACRO']}</b> de máximo <b>$max_acro</b> autorizadas.",
                    'puntos' => -$pen
                ];
            }

            // 3. PENALIZACIONES DE RUTINA: Exceso Global de Elementos sobre cupo de Coach Card [AS 6.7.8]
            if ($elementos_max > 0 && $total_elementos_declarados > $elementos_max) {
                $exceso_glob = $total_elementos_declarados - $elementos_max;
                // Si no fue penalizado previamente por híbridos o acrobacias
                $pen_ya_aplicada = max(0, $totales['HYBRID'] - $max_hib) + max(0, $totales['ACRO'] - $max_acro);
                if ($exceso_glob > $pen_ya_aplicada) {
                    $diff_pen = ($exceso_glob - $pen_ya_aplicada) * 2.0;
                    $penalizacion_pts += $diff_pen;
                    $penalizaciones_rutina[] = [
                        'regla' => 'AS 6.7.8',
                        'texto' => "Elementos totales declarados excedidos: <b>$total_elementos_declarados</b> declarados para un cupo de <b>$elementos_max</b> autorizados en la fase.",
                        'puntos' => -$diff_pen
                    ];
                }
            }

            // 4. PENALIZACIONES DE RUTINA: Comprobación de Rutina Acrobática (Appendix 4 & AS 6.15.13)
            if ($is_acrobatica) {
                $grupos_esperados = ['ACRO-A', 'ACRO-B', 'ACRO-C', 'ACRO-P'];
                $grupos_encontrados = [];
                foreach ($acro_declaradas as $ac) {
                    foreach ($grupos_esperados as $gr) {
                        if (stripos($ac['id'], $gr) !== false || stripos($ac['id'], substr($gr, -1)) !== false) {
                            $grupos_encontrados[$gr] = true;
                        }
                    }
                }
                $grupos_faltantes = array_diff($grupos_esperados, array_keys($grupos_encontrados));
                if (!empty($grupos_faltantes) && count($acro_declaradas) > 0) {
                    $penalizacion_pts += 8.0;
                    $penalizaciones_rutina[] = [
                        'regla' => 'AS 6.15.13',
                        'texto' => "Infracción de grupos en Rutina Acrobática: Faltan acrobacias de los grupos requeridos (" . implode(', ', $grupos_faltantes) . "). Debe incluir al menos una acrobacia de cada grupo (A, B, C, P).",
                        'puntos' => -8.0
                    ];
                }
            }

            // 5. RUTINAS TÉCNICAS: Comprobación de TRE (Appendix 2)
            if ($is_tecnica) {
                if ($totales['TRE'] > $max_tre) {
                    $penalizaciones_rutina[] = [
                        'regla' => 'AS 6.7.8',
                        'texto' => "Exceso de Elementos Técnicos Obligatorios: <b>{$totales['TRE']}</b> de máximo <b>$max_tre</b> autorizados.",
                        'puntos' => -($totales['TRE'] - $max_tre) * 2.0
                    ];
                    $penalizacion_pts += ($totales['TRE'] - $max_tre) * 2.0;
                } elseif ($totales['TRE'] < $max_tre && $total_elementos_declarados > 0) {
                    $advertencias[] = "[AS 6.7.9] Faltan Elementos Técnicos Obligatorios: se han declarado <b>{$totales['TRE']}</b> de los <b>$max_tre</b> requeridos. En competición, los TRE omitidos reciben nota cero (0.0).";
                }

                // Comprobar secuencia de orden de los TRE
                $tre_orden = [];
                foreach ($elementos as $n => $el) {
                    if ($el['tipo_general'] == 'TRE') {
                        $tre_str = implode(' ', $el['dificultades']);
                        if (preg_match('/TRE\s*([1-5])/i', $tre_str, $m_tre)) {
                            $tre_orden[] = (int)$m_tre[1];
                        }
                    }
                }
                $sorted_tre = $tre_orden;
                sort($sorted_tre);
                if (!empty($tre_orden) && $tre_orden !== $sorted_tre) {
                    $advertencias[] = "[AS 6.7.10] Secuencia de TRE desordenada (" . implode(' → ', $tre_orden) . "). El reglamento exige que los TRE se ejecuten en orden estricto (1 a 5). Los TRE fuera de orden reciben nota cero (0.0).";
                }
            }

            // 6. HÍBRIDO CREATIVO (ShoHY): Requerido en categorías de formación (Alevín / Infantil)
            if ($req_creat == 1) {
                if ($totales['CREATIVO'] == 0 && $total_elementos_declarados > 0) {
                    $advertencias[] = "Falta el Híbrido Creativo / ShoHY obligatorio (DD = 0.50) requerido por la normativa de la categoría.";
                } elseif ($totales['CREATIVO'] > 1) {
                    $advertencias[] = "Se han declarado {$totales['CREATIVO']} Híbridos Creativos (la normativa contempla únicamente 1 ShoHY de DD fija 0.50).";
                }
            }

            // 7. INCLUSIÓN DE FAMILIAS REQUERIDAS EN RUTINAS LIBRES (AS 6.11.9 / AS 6.14.11)
            if (!$is_tecnica && !$is_acrobatica && $total_elementos_declarados > 0) {
                $familias_basicas = ['Rotaciones', 'Empujes', 'Flexibilidad', 'Airbone Weight'];
                $faltan_familias = [];
                foreach ($familias_basicas as $fb) {
                    if (!isset($familias_rutina_global[$fb])) {
                        $faltan_familias[] = $fb;
                    }
                }
                if (!empty($faltan_familias)) {
                    $advertencias[] = "[AS 6.11.9] Variedad de Familias: No se han detectado movimientos de las familias (" . implode(', ', $faltan_familias) . ") en los híbridos declarados. La normativa exige incluir habilidades de las familias principales.";
                }
            }

            // 8. VERIFICACIÓN MICRO POR ELEMENTO (RIESGOS DE BASE MARK Y SEGURIDAD RFEN)
            foreach ($elementos as $n => &$el) {
                // A) Híbridos Libres
                if ($el['tipo_general'] == 'HYBRID' && !$el['is_creativo']) {
                    // Regla de familias: máx 5 movimientos por familia
                    foreach ($el['familias'] as $fam => $cant) {
                        if ($cant > 5) {
                            $msg = "Familia '$fam' declarada $cant veces (Límite máx: 5). Se aplicará <b>Base Mark (0.50)</b>.";
                            $el['infracciones_elem'][] = $msg;
                            $riesgos_basemark[] = "Elemento #$n: $msg";
                        }
                    }

                    // Regla de repetición de técnica: máx 3 idénticas
                    $rep_counts = array_count_values($el['dificultades']);
                    foreach ($rep_counts as $tec => $veces) {
                        if ($veces > 3) {
                            $msg = "Técnica '$tec' repetida $veces veces (Límite máx: 3). Se aplicará <b>Base Mark (0.50)</b>.";
                            $el['infracciones_elem'][] = $msg;
                            $riesgos_basemark[] = "Elemento #$n: $msg";
                        }

                        // Cap de alto nivel AQUA 2024-2026: T5-T9 y R5-R9 máx 3 veces
                        if (preg_match('/^(T[5-9]|R[5-9])/i', $tec) && $veces > 3) {
                            $msg = "[AQUA Cap] Técnica de alto nivel '$tec' repetida $veces veces (Máximo reglamentario: 3). Se aplicará <b>Base Mark</b>.";
                            $el['infracciones_elem'][] = $msg;
                            $riesgos_basemark[] = "Elemento #$n: $msg";
                        }
                    }

                    // Normativa de Seguridad RFEN Alevín (12 & Under)
                    if ($is_alevin) {
                        $lim_alev = (strpos(strtolower($rutina_info['modalidad']), 'equipo') !== false) ? 4.0 : 5.0;
                        if ($el['dd_total'] > $lim_alev) {
                            $msg = "[RFEN 12U] Dificultad declarada ({$el['dd_total']}) supera el tope de seguridad Alevín ($lim_alev pts).";
                            $el['infracciones_elem'][] = $msg;
                            $riesgos_basemark[] = "Elemento #$n: $msg";
                        }

                        // Restricción de niveles técnicos en Alevín
                        foreach ($el['dificultades'] as $d_cod) {
                            if (preg_match('/^T([7-9])/i', $d_cod)) {
                                $msg = "[RFEN 12U] Empuje '$d_cod' supera nivel máximo autorizado en Alevín (máx T6).";
                                $el['infracciones_elem'][] = $msg;
                                $riesgos_basemark[] = "Elemento #$n: $msg";
                            }
                            if (preg_match('/^R([3-9])/i', $d_cod)) {
                                $msg = "[RFEN 12U] Giro '$d_cod' supera nivel máximo autorizado en Alevín (máx R2).";
                                $el['infracciones_elem'][] = $msg;
                                $riesgos_basemark[] = "Elemento #$n: $msg";
                            }
                            if (preg_match('/^F(8|9|10)/i', $d_cod)) {
                                $msg = "[RFEN 12U] Flexibilidad '$d_cod' supera nivel máximo autorizado en Alevín (máx F7).";
                                $el['infracciones_elem'][] = $msg;
                                $riesgos_basemark[] = "Elemento #$n: $msg";
                            }
                        }
                    }

                    // Comprobación de tiempo de apnea en el híbrido
                    if ($el['duracion_seg'] > 0 && $el['duracion_seg'] > $max_apnea_seg) {
                        $el['avisos_elem'][] = "Duración del híbrido ({$el['duracion_seg']}s) excede el umbral de apnea recomendado ({$max_apnea_seg}s).";
                    }
                }

                // B) Acrobacias
                if ($el['tipo_general'] == 'ACROBATIC') {
                    $el['avisos_elem'][] = "Verificar que la construcción y posiciones no se repitan con otra acrobacia de la rutina.";
                }

                // C) Elementos Técnicos (TRE)
                if ($el['tipo_general'] == 'TRE') {
                    $el['avisos_elem'][] = "Los TRE deben ejecutarse en el orden reglamentario declarado. Alterar el orden invalida el elemento (DD = 0).";
                }

                // D) Híbrido Creativo (ShoHY)
                if ($el['is_creativo']) {
                    if ($el['dd_total'] != 0.500 && $el['dd_total'] > 0) {
                        $el['avisos_elem'][] = "El Híbrido Creativo / ShoHY tiene un valor reglamentario fijo de DD = 0.500.";
                    }
                }
            }
            unset($el);

            // Estado Veredicto Global
            $estado_veredicto = 'aprobado';
            if (!empty($penalizaciones_rutina)) {
                $estado_veredicto = 'penalizacion';
            } elseif (!empty($riesgos_basemark)) {
                $estado_veredicto = 'basemark';
            } elseif (!empty($advertencias)) {
                $estado_veredicto = 'aviso';
            }
            ?>

            <!-- Top Header & Actions -->
            <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">
                        <a href="coach_card_auditor.php" class="hover:text-blue-600 transition-colors">Auditor DTC</a>
                        <span>/</span>
                        <span class="text-blue-600 font-black">Dictamen Oficial</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight flex items-center gap-3">
                        <span class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-lg shadow-xs border border-amber-100">
                            <i class="fa-solid fa-clipboard-check"></i>
                        </span>
                        Informe de Auditoría DTC
                    </h1>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    <a href="coach_card_composer.php?id_rutina=<?php echo $id_rutina; ?>" 
                       class="px-4 py-2.5 bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all flex items-center gap-2 border border-blue-100 shadow-xs">
                        <i class="fas fa-edit"></i> Editar en Composer
                    </a>
                    <a target="_blank" href="./informes/informe_coach_card.php?titulo=Coach%20Card&id_rutina=<?php echo $id_rutina;?>&id_competicion=<?php echo $id_comp;?>" 
                       class="px-4 py-2.5 bg-white border border-slate-200 text-slate-700 hover:text-rose-600 rounded-xl text-xs font-black uppercase tracking-wider shadow-xs transition-all flex items-center gap-2">
                        <i class="fas fa-file-pdf text-rose-500"></i> PDF Oficial
                    </a>
                    <button onclick="window.print()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-black uppercase tracking-wider transition-all flex items-center gap-2">
                        <i class="fas fa-print"></i> Imprimir
                    </button>
                    <a href="coach_card_auditor.php" class="px-4 py-2.5 bg-slate-800 hover:bg-black text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all flex items-center gap-2 shadow-xs">
                        <i class="fas fa-list"></i> Directorio
                    </a>
                </div>
            </div>

            <!-- TARJETA PRINCIPAL: SEMÁFORO Y VEREDICTO DTC -->
            <?php if ($estado_veredicto === 'penalizacion'): ?>
                <div class="mb-8 p-6 sm:p-8 rounded-[2rem] bg-gradient-to-br from-rose-500 via-rose-600 to-rose-700 text-white shadow-xl shadow-rose-500/20 animate-fade-in">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <span class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-2xl shrink-0">
                                <i class="fas fa-triangle-exclamation"></i>
                            </span>
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-widest text-rose-200 block">Dictamen Técnico</span>
                                <h2 class="text-xl sm:text-2xl font-black tracking-tight">NO CONFORME · PENALIZACIONES DE RUTINA APLICABLES</h2>
                                <p class="text-xs text-rose-100 font-medium mt-0.5">Se han detectado infracciones reglamentarias que conllevan deducción directa de puntos en el acta.</p>
                            </div>
                        </div>
                        <?php if ($penalizacion_pts > 0): ?>
                            <div class="px-5 py-3 rounded-2xl bg-black/25 backdrop-blur-md border border-white/20 text-center shrink-0">
                                <span class="text-[9px] font-black uppercase tracking-widest text-rose-200 block">Deducción Total</span>
                                <span class="text-2xl font-black tabular-nums">-<?php echo number_format($penalizacion_pts, 1); ?> PTS</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mt-6 pt-6 border-t border-white/20 space-y-2.5">
                        <?php foreach ($penalizaciones_rutina as $pen_item): ?>
                            <div class="flex items-start justify-between gap-3 text-xs font-medium text-rose-50 bg-white/10 p-3 rounded-xl">
                                <div class="flex items-start gap-2.5">
                                    <span class="px-2 py-0.5 rounded bg-white text-rose-700 font-black text-[10px] shrink-0"><?php echo htmlspecialchars($pen_item['regla']); ?></span>
                                    <span><?php echo $pen_item['texto']; ?></span>
                                </div>
                                <span class="font-black text-sm tabular-nums shrink-0"><?php echo number_format($pen_item['puntos'], 1); ?> pts</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            <?php elseif ($estado_veredicto === 'basemark'): ?>
                <div class="mb-8 p-6 sm:p-8 rounded-[2rem] bg-gradient-to-br from-amber-500 via-amber-600 to-amber-700 text-white shadow-xl shadow-amber-500/20 animate-fade-in">
                    <div class="flex items-center gap-4">
                        <span class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-2xl shrink-0">
                            <i class="fas fa-exclamation-circle"></i>
                        </span>
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-widest text-amber-200 block">Dictamen Técnico</span>
                            <h2 class="text-xl sm:text-2xl font-black tracking-tight">ALERTA TÉCNICA · RIESGO DE BASE MARK EN ELEMENTOS</h2>
                            <p class="text-xs text-amber-100 font-medium mt-0.5">La estructura general cumple, pero uno o más elementos superan límites de familia, técnicas repetidas o límites de edad (RFEN).</p>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-white/20 space-y-2">
                        <?php foreach ($riesgos_basemark as $bm_item): ?>
                            <div class="flex items-start gap-2.5 text-xs font-medium text-amber-50 bg-black/10 p-2.5 rounded-xl">
                                <i class="fas fa-circle-exclamation text-sm mt-0.5 shrink-0 text-amber-200"></i>
                                <span><?php echo $bm_item; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            <?php elseif ($estado_veredicto === 'aviso'): ?>
                <div class="mb-8 p-6 sm:p-8 rounded-[2rem] bg-gradient-to-br from-sky-500 via-blue-600 to-indigo-700 text-white shadow-xl shadow-blue-500/20 animate-fade-in">
                    <div class="flex items-center gap-4">
                        <span class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-2xl shrink-0">
                            <i class="fas fa-bell"></i>
                        </span>
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-widest text-sky-200 block">Dictamen Técnico</span>
                            <h2 class="text-xl sm:text-2xl font-black tracking-tight">CONFORME CON OBSERVACIONES PARA MESA TÉCNICA</h2>
                            <p class="text-xs text-sky-100 font-medium mt-0.5">La Coach Card cumple los límites normativos principales con advertencias técnicas para el control de la rutina.</p>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-white/20 space-y-2">
                        <?php foreach ($advertencias as $adv): ?>
                            <div class="flex items-start gap-2.5 text-xs font-medium text-sky-50 bg-white/10 p-2.5 rounded-xl">
                                <i class="fas fa-circle-info text-sm mt-0.5 shrink-0 text-sky-200"></i>
                                <span><?php echo $adv; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            <?php else: ?>
                <div class="mb-8 p-6 sm:p-8 rounded-[2rem] bg-gradient-to-br from-emerald-500 to-teal-700 text-white shadow-xl shadow-emerald-500/20 animate-fade-in">
                    <div class="flex items-center gap-4">
                        <span class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-2xl shrink-0">
                            <i class="fas fa-circle-check"></i>
                        </span>
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-widest text-emerald-200 block">Dictamen Técnico</span>
                            <h2 class="text-xl sm:text-2xl font-black tracking-tight">COACH CARD 100% CONFORME A NORMATIVA</h2>
                            <p class="text-xs text-emerald-100 font-medium mt-0.5">Todos los límites estructurales, cupos, familias y códigos cumplen el reglamento AQUA y RFEN.</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- FICHA TÉCNICA Y RESUMEN DE LA RUTINA -->
            <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-200/80 mb-8">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                    <div class="md:col-span-8 flex flex-col sm:flex-row items-start sm:items-center gap-4">
                        <div class="w-16 h-16 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col items-center justify-center shrink-0">
                            <span class="text-[9px] font-black uppercase text-slate-400">Rutina</span>
                            <span class="text-xl font-black text-slate-800 tabular-nums">#<?php echo $id_rutina; ?></span>
                        </div>
                        <div>
                            <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                <span class="px-2.5 py-0.5 rounded-lg bg-blue-50 text-blue-700 text-[10px] font-black uppercase border border-blue-100">
                                    <?php echo htmlspecialchars($rutina_info['modalidad']); ?>
                                </span>
                                <span class="px-2.5 py-0.5 rounded-lg bg-slate-100 text-slate-700 text-[10px] font-black uppercase border border-slate-200">
                                    <?php echo htmlspecialchars($rutina_info['categoria']); ?>
                                </span>
                                <span class="px-2.5 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 text-[10px] font-black uppercase border border-emerald-100">
                                    <?php echo htmlspecialchars($rutina_info['nombre_club']); ?>
                                </span>
                            </div>
                            <h3 class="text-base font-black text-slate-800">
                                <?php echo !empty($atletas) ? implode(', ', $atletas) : 'Sin deportistas vinculadas'; ?>
                            </h3>
                            <p class="text-xs text-slate-400 font-bold italic mt-0.5">
                                Tema: <?php echo !empty($rutina_info['tematica']) ? '“' . htmlspecialchars($rutina_info['tematica']) . '”' : 'No especificado'; ?>
                            </p>
                        </div>
                    </div>

                    <div class="md:col-span-4 flex items-center justify-start md:justify-end gap-3">
                        <div class="px-5 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-center">
                            <span class="text-[9px] font-black uppercase text-slate-400 block tracking-widest">DD Declarada</span>
                            <span class="text-2xl font-black text-blue-600 tabular-nums"><?php echo number_format((float)($rutina_info['dd_total'] ?? 0), 3); ?></span>
                        </div>
                        <div class="px-5 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-center">
                            <span class="text-[9px] font-black uppercase text-slate-400 block tracking-widest">Duración</span>
                            <span class="text-base font-black text-slate-700 tabular-nums"><?php echo gmdate("i:s", $duracion_esperada); ?> ±<?php echo $margen_esperado; ?>s</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 1: REVISIÓN ESTRUCTURAL (APÉNDICE III WORLD AQUATICS) -->
            <div class="mb-8">
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 mb-4 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-[11px]"><i class="fas fa-list-check"></i></span>
                    1. Revisión Estructural Reglamentaria (Apéndice III World Aquatics / RFEN)
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Híbridos Libres -->
                    <?php 
                    $hib_ok = ($max_hib == 0 || $totales['HYBRID'] <= $max_hib);
                    ?>
                    <div class="bg-white rounded-2xl p-5 border <?php echo $hib_ok ? 'border-slate-200' : 'border-rose-300 bg-rose-50/20'; ?> shadow-sm flex flex-col justify-between">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-black uppercase text-slate-400">Híbridos Libres</span>
                            <span class="w-6 h-6 rounded-md <?php echo $hib_ok ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-100 text-rose-600'; ?> flex items-center justify-center text-xs">
                                <i class="fas <?php echo $hib_ok ? 'fa-check' : 'fa-times'; ?>"></i>
                            </span>
                        </div>
                        <div>
                            <div class="text-3xl font-black <?php echo $hib_ok ? 'text-slate-800' : 'text-rose-600'; ?> tabular-nums">
                                <?php echo $totales['HYBRID']; ?>
                                <span class="text-sm text-slate-400 font-bold">/ máx <?php echo $max_hib; ?></span>
                            </div>
                            <p class="text-[10px] font-bold <?php echo $hib_ok ? 'text-emerald-600' : 'text-rose-600'; ?> mt-1">
                                <?php echo $hib_ok ? 'Dentro del cupo reglamentario' : 'Exceso sancionado con -2.0 pts c/u [AS 6.7.8]'; ?>
                            </p>
                        </div>
                    </div>

                    <!-- Híbrido Creativo (ShoHY) -->
                    <?php 
                    $creat_ok = ($req_creat == 0 || $totales['CREATIVO'] == 1);
                    ?>
                    <div class="bg-white rounded-2xl p-5 border <?php echo $creat_ok ? 'border-slate-200' : 'border-amber-300 bg-amber-50/20'; ?> shadow-sm flex flex-col justify-between">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-black uppercase text-slate-400">Híbrido Creativo</span>
                            <span class="w-6 h-6 rounded-md <?php echo $creat_ok ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-100 text-amber-600'; ?> flex items-center justify-center text-xs">
                                <i class="fas <?php echo $creat_ok ? 'fa-check' : 'fa-exclamation'; ?>"></i>
                            </span>
                        </div>
                        <div>
                            <div class="text-3xl font-black <?php echo $creat_ok ? 'text-slate-800' : 'text-amber-600'; ?> tabular-nums">
                                <?php echo $totales['CREATIVO']; ?>
                                <span class="text-sm text-slate-400 font-bold">/ req <?php echo $req_creat; ?></span>
                            </div>
                            <p class="text-[10px] font-bold <?php echo $creat_ok ? 'text-emerald-600' : 'text-amber-600'; ?> mt-1">
                                <?php 
                                if ($req_creat == 0) echo 'No requerido en esta categoría';
                                elseif ($totales['CREATIVO'] == 1) echo 'ShoHY declarado (DD fija 0.50)';
                                else echo 'Obligatorio en Alevín/Infantil';
                                ?>
                            </p>
                        </div>
                    </div>

                    <!-- Acrobacias -->
                    <?php 
                    $acro_ok = ($max_acro == 0 || $totales['ACRO'] <= $max_acro);
                    ?>
                    <div class="bg-white rounded-2xl p-5 border <?php echo $acro_ok ? 'border-slate-200' : 'border-rose-300 bg-rose-50/20'; ?> shadow-sm flex flex-col justify-between">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-black uppercase text-slate-400">Acrobacias</span>
                            <span class="w-6 h-6 rounded-md <?php echo $acro_ok ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-100 text-rose-600'; ?> flex items-center justify-center text-xs">
                                <i class="fas <?php echo $acro_ok ? 'fa-check' : 'fa-times'; ?>"></i>
                            </span>
                        </div>
                        <div>
                            <div class="text-3xl font-black <?php echo $acro_ok ? 'text-slate-800' : 'text-rose-600'; ?> tabular-nums">
                                <?php echo $totales['ACRO']; ?>
                                <span class="text-sm text-slate-400 font-bold">/ máx <?php echo $max_acro; ?></span>
                            </div>
                            <p class="text-[10px] font-bold <?php echo $acro_ok ? 'text-emerald-600' : 'text-rose-600'; ?> mt-1">
                                <?php echo $acro_ok ? 'Dentro del cupo reglamentario' : 'Exceso sancionado con -2.0 pts c/u [AS 6.7.8]'; ?>
                            </p>
                        </div>
                    </div>

                    <!-- Elementos Técnicos Obligatorios (TRE) -->
                    <?php 
                    $tre_ok = (!$is_tecnica || $totales['TRE'] == $max_tre);
                    ?>
                    <div class="bg-white rounded-2xl p-5 border <?php echo $tre_ok ? 'border-slate-200' : 'border-amber-300 bg-amber-50/20'; ?> shadow-sm flex flex-col justify-between">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-black uppercase text-slate-400">Elementos Técnicos (TRE)</span>
                            <span class="w-6 h-6 rounded-md <?php echo $tre_ok ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-100 text-amber-600'; ?> flex items-center justify-center text-xs">
                                <i class="fas <?php echo $tre_ok ? 'fa-check' : 'fa-exclamation'; ?>"></i>
                            </span>
                        </div>
                        <div>
                            <div class="text-3xl font-black <?php echo $tre_ok ? 'text-slate-800' : 'text-amber-600'; ?> tabular-nums">
                                <?php echo $totales['TRE']; ?>
                                <span class="text-sm text-slate-400 font-bold">/ req <?php echo $max_tre; ?></span>
                            </div>
                            <p class="text-[10px] font-bold <?php echo $tre_ok ? 'text-emerald-600' : 'text-amber-600'; ?> mt-1">
                                <?php echo $tre_ok ? ($is_tecnica ? 'Cupo de TREs completo' : 'No aplicable (Rutina Libre)') : 'Debe coincidir exactamente con los requeridos'; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 2: ANÁLISIS ELEMENTO A ELEMENTO -->
            <div class="mb-8">
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 mb-4 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-[11px]"><i class="fas fa-microscope"></i></span>
                    2. Análisis Micro y Checklist por Elemento
                </h3>

                <div class="space-y-4">
                    <?php foreach ($elementos as $n => $el): 
                        $has_inf = !empty($el['infracciones_elem']);
                        $has_adv = !empty($el['avisos_elem']);
                    ?>
                        <div class="bg-white rounded-2xl p-5 shadow-sm border <?php echo $has_inf ? 'border-rose-300 bg-rose-50/10' : 'border-slate-200'; ?> transition-all">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-xl bg-slate-100 flex items-center justify-center text-xs font-black text-slate-700">
                                        #<?php echo $n; ?>
                                    </span>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider <?php 
                                                if ($el['is_creativo']) echo 'bg-purple-50 text-purple-700 border border-purple-200';
                                                elseif ($el['tipo_general'] == 'HYBRID') echo 'bg-sky-50 text-sky-700 border border-sky-100';
                                                elseif ($el['tipo_general'] == 'ACROBATIC') echo 'bg-rose-50 text-rose-700 border border-rose-100';
                                                elseif ($el['tipo_general'] == 'TRE') echo 'bg-amber-50 text-amber-700 border border-amber-100';
                                                else echo 'bg-slate-100 text-slate-600';
                                            ?>">
                                                <?php 
                                                if ($el['is_creativo']) echo 'HÍBRIDO CREATIVO (ShoHY)';
                                                else echo $el['tipo_general']; 
                                                ?>
                                                <?php echo !empty($el['grupo_acro']) ? ' · ' . $el['grupo_acro'] : ''; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-4">
                                    <div class="text-right">
                                        <span class="text-[9px] font-black uppercase text-slate-400 block tracking-widest">Tiempos</span>
                                        <span class="font-mono text-xs font-bold text-slate-600 tabular-nums">
                                            <?php echo !empty($el['time_inicio']) ? $el['time_inicio'] : '--:--'; ?> - 
                                            <?php echo !empty($el['time_fin']) ? $el['time_fin'] : '--:--'; ?>
                                            <?php if ($el['duracion_seg'] > 0): ?>
                                                <span class="text-[10px] text-slate-400 font-semibold">(<?php echo $el['duracion_seg']; ?>s)</span>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <div class="text-right pl-4 border-l border-slate-100">
                                        <span class="text-[9px] font-black uppercase text-slate-400 block tracking-widest">DD Elemento</span>
                                        <span class="text-base font-black text-blue-600 tabular-nums">
                                            <?php echo number_format($el['dd_total'], 3); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-1 md:grid-cols-12 gap-4 items-start">
                                <!-- Códigos Declarados -->
                                <div class="md:col-span-6">
                                    <span class="text-[9px] font-black uppercase text-slate-400 block tracking-widest mb-1.5">Movimientos y Basemark</span>
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <?php if (!empty($el['nombre_basemark'])): ?>
                                            <span class="px-2 py-1 rounded-lg bg-slate-100 text-slate-700 text-[11px] font-black tabular-nums border border-slate-200">
                                                BM: <?php echo htmlspecialchars($el['nombre_basemark']); ?> (+<?php echo number_format($el['basemark'], 2); ?>)
                                            </span>
                                        <?php endif; ?>

                                        <?php if (!empty($el['dificultades'])): ?>
                                            <?php foreach ($el['dificultades'] as $d): ?>
                                                <span class="px-2 py-1 rounded-lg bg-blue-50 text-blue-800 text-[11px] font-black tabular-nums border border-blue-100">
                                                    <?php echo htmlspecialchars($d); ?>
                                                </span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="text-xs text-slate-400 italic">Sin movimientos específicos declarados</span>
                                        <?php endif; ?>
                                    </div>

                                    <?php if (!empty($el['familias'])): ?>
                                        <div class="mt-2.5 flex items-center gap-1.5 flex-wrap">
                                            <span class="text-[9px] font-black uppercase text-slate-400">Familias:</span>
                                            <?php foreach ($el['familias'] as $f_nom => $f_cant): ?>
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-50 text-slate-600 border border-slate-200">
                                                    <?php echo htmlspecialchars($f_nom); ?>: <strong><?php echo $f_cant; ?></strong>/5
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Checklist / Alertas -->
                                <div class="md:col-span-6 space-y-1.5">
                                    <?php if ($has_inf): ?>
                                        <?php foreach ($el['infracciones_elem'] as $e_inf): ?>
                                            <div class="p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-bold flex items-center gap-2">
                                                <i class="fas fa-times-circle shrink-0"></i>
                                                <span><?php echo $e_inf; ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>

                                    <?php if ($has_adv): ?>
                                        <?php foreach ($el['avisos_elem'] as $e_adv): ?>
                                            <div class="p-2.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-medium flex items-center gap-2">
                                                <i class="fas fa-info-circle shrink-0 text-amber-500"></i>
                                                <span><?php echo $e_adv; ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>

                                    <?php if (!$has_inf && !$has_adv): ?>
                                        <div class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center gap-2">
                                            <i class="fas fa-check-circle text-emerald-600"></i>
                                            <span>Elemento conforme: límites de repetición, familias y DD respetados.</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Botones de Salida -->
            <div class="mt-8 pt-6 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                <a href="coach_card_auditor.php" class="px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-black text-xs uppercase tracking-wider rounded-xl transition-all flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Auditar Otra Rutina
                </a>
                <div class="flex items-center gap-3">
                    <a href="coach_card_composer.php?id_rutina=<?php echo $id_rutina; ?>" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-black text-xs uppercase tracking-wider rounded-xl shadow-md shadow-blue-500/20 active:scale-[0.98] transition-all flex items-center gap-2">
                        <i class="fas fa-edit"></i> Modificar Elementos en Composer
                    </a>
                </div>
            </div>

        <?php endif; ?>

    </div>
</main>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
