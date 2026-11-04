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

            // Obtener rutinas de la competición
            $q_rutinas = "SELECT r.id, r.tematica, r.dd_total, r.id_fase, r.id_club,
                                 cl.nombre_corto as nombre_club, cl.logo as logo_club,
                                 m.nombre as nombre_modalidad, c.nombre as nombre_categoria,
                                 f.elementos_coach_card, f.genero,
                                 (SELECT COUNT(*) FROM hibridos_rutina hr WHERE hr.id_rutina = r.id AND hr.tipo = 'part' AND hr.texto != '3') as total_partes
                          FROM rutinas r
                          JOIN fases f ON r.id_fase = f.id
                          LEFT JOIN modalidades m ON f.id_modalidad = m.id
                          LEFT JOIN categorias c ON f.id_categoria = c.id
                          LEFT JOIN clubes cl ON r.id_club = cl.id
                          WHERE r.id_competicion = '$id_comp' $club_filter
                          ORDER BY f.orden ASC, r.orden ASC, r.id ASC";
            $res_rutinas = mysqli_query($connection, $q_rutinas);
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
                        Verificación técnica automática bajo normativa World Aquatics (AQUA) / RFEN.
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

            <!-- Buscador Dinámico -->
            <div class="mb-6 bg-white p-4 rounded-2xl shadow-sm border border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="relative w-full sm:w-80">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"><i class="fas fa-search"></i></span>
                    <input type="text" id="filterRutinasAuditor" placeholder="Buscar por club, modalidad o ID..." 
                           class="w-full pl-9 pr-4 py-2 bg-slate-50 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 focus:bg-white focus:border-blue-500 transition-all">
                </div>
                <div class="text-[11px] font-black uppercase tracking-widest text-slate-400">
                    <?php echo mysqli_num_rows($res_rutinas); ?> Rutinas Registradas
                </div>
            </div>

            <!-- Listado de Rutinas para Auditar -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="rutinasGridAuditor">
                <?php while ($r = mysqli_fetch_assoc($res_rutinas)): 
                    $mod_title = $r['nombre_modalidad'];
                    if (($r['genero'] ?? '') === 'masculino') $mod_title .= ' Masc';
                    $dd_val = (float)($r['dd_total'] ?? 0);
                    $has_elements = ($r['total_partes'] > 0);
                ?>
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200 hover:border-blue-300 transition-all flex flex-col justify-between gap-4 card-rutina-auditor"
                         data-search="<?php echo strtolower($r['id'].' '.$mod_title.' '.$r['nombre_categoria'].' '.$r['nombre_club'].' '.$r['tematica']); ?>">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <span class="px-2.5 py-0.5 rounded-lg bg-slate-100 text-slate-700 text-[10px] font-black uppercase tracking-wider border border-slate-200">
                                    #<?php echo $r['id']; ?> · <?php echo htmlspecialchars($r['nombre_club']); ?>
                                </span>
                                <span class="px-2.5 py-0.5 rounded-lg <?php echo $has_elements ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-amber-50 text-amber-700 border-amber-100'; ?> text-[10px] font-black uppercase tracking-wider border">
                                    <?php echo $has_elements ? 'Compuesta' : 'Pendiente'; ?>
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

                            <a href="coach_card_auditor.php?id_rutina=<?php echo $r['id']; ?>" 
                               class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-black text-xs uppercase tracking-wider rounded-xl shadow-md shadow-blue-500/20 active:scale-[0.98] transition-all flex items-center gap-1.5">
                                <i class="fa-solid fa-microscope text-xs"></i> Auditar
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <script>
            document.getElementById('filterRutinasAuditor').addEventListener('input', function() {
                const term = this.value.toLowerCase().trim();
                const cards = document.querySelectorAll('.card-rutina-auditor');
                cards.forEach(c => {
                    const txt = c.getAttribute('data-search') || '';
                    c.style.display = txt.includes(term) ? '' : 'none';
                });
            });
            </script>

        <?php else: ?>
            <!-- ========================================================================= -->
            <!-- VISTA 2: INFORME DE AUDITORÍA COMPLETO DE UNA RUTINA                      -->
            <!-- ========================================================================= -->
            <?php
            // 1. Obtener datos de la rutina, fase y reglas
            $q_info = "SELECT r.*, c.nombre as categoria, m.nombre as modalidad, cl.nombre_corto as nombre_club, cl.logo as logo_club,
                              f.elementos_coach_card, f.genero, f.duracion_musica as f_duracion, f.margen_musica as f_margen,
                              rc.max_hibridos, rc.max_acrobacias, rc.max_tre, rc.req_hibrido_creativo, rc.max_apnea,
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

            // Duraciones oficiales
            $duracion_esperada = (int)($rutina_info['rc_duracion'] ?? ($rutina_info['f_duracion'] ?? 0));
            $margen_esperado = (int)($rutina_info['rc_margen'] ?? ($rutina_info['f_margen'] ?? 5));
            $elementos_max = (int)($rutina_info['elementos_coach_card'] ?? 0);

            // Nadadoras
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

            // 2. Extraer mapa de familias desde dificultad_hibridos
            $mapa_familias = [];
            $q_fam = mysqli_query($connection, "SELECT codigo, agrupar FROM dificultad_hibridos");
            if ($q_fam) {
                while ($f = mysqli_fetch_assoc($q_fam)) {
                    $c = trim($f['codigo'] ?? '');
                    $a = trim($f['agrupar'] ?? '');
                    if ($c !== '') $mapa_familias[$c] = $a;
                }
            }

            // 3. Extraer elementos de hibridos_rutina
            $q_elementos = "SELECT * FROM hibridos_rutina WHERE id_rutina = $id_rutina ORDER BY elemento ASC, id ASC";
            $res_elementos = mysqli_query($connection, $q_elementos);

            $elementos = [];
            $totales = ['HYBRID' => 0, 'ACRO' => 0, 'TRE' => 0];
            $transiciones_count = 0;
            $infracciones = [];
            $advertencias = [];

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
                        'time_inicio' => '',
                        'time_fin' => '',
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

                // Tipo Part (si basemark estuviese sin definir)
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
                        }
                    }
                }

                // Total de DD
                if ($row['tipo'] == 'total') {
                    $elementos[$num_elem]['dd_total'] = (float)($row['valor'] ?? 0);
                }
            }

            // Conteo estructural
            foreach ($elementos as $elem) {
                if ($elem['tipo_general'] == 'HYBRID') $totales['HYBRID']++;
                elseif ($elem['tipo_general'] == 'ACROBATIC') $totales['ACRO']++;
                elseif ($elem['tipo_general'] == 'TRE') $totales['TRE']++;
            }

            // =========================================================================
            // VALIDACIÓN DTC REGLAMENTARIA (APÉNDICE III & AQUA RULES)
            // =========================================================================

            $max_hib = (int)($rutina_info['max_hibridos'] ?? 0);
            $max_acro = (int)($rutina_info['max_acrobacias'] ?? 0);
            $max_tre = (int)($rutina_info['max_tre'] ?? 0);
            $is_tecnica = (strpos(strtolower($rutina_info['modalidad'] ?? ''), 'técnic') !== false);

            $penalizacion_pts = 0.0;

            // 1. Exceso de Híbridos Libres (-2.0 pts por elemento)
            if ($max_hib > 0 && $totales['HYBRID'] > $max_hib) {
                $exceso = $totales['HYBRID'] - $max_hib;
                $pen = $exceso * 2.0;
                $penalizacion_pts += $pen;
                $infracciones[] = "Exceso de Híbridos Libres declarados: {$totales['HYBRID']} de máximo $max_hib permitido. Penalización aplicable: <b>-$pen pts</b>.";
            }

            // 2. Exceso de Acrobacias (-2.0 pts por elemento)
            if ($max_acro > 0 && $totales['ACRO'] > $max_acro) {
                $exceso = $totales['ACRO'] - $max_acro;
                $pen = $exceso * 2.0;
                $penalizacion_pts += $pen;
                $infracciones[] = "Exceso de Acrobacias declaradas: {$totales['ACRO']} de máximo $max_acro permitido. Penalización aplicable: <b>-$pen pts</b>.";
            }

            // 3. Comprobación de TRE en Rutinas Técnicas
            if ($is_tecnica) {
                if ($totales['TRE'] > $max_tre) {
                    $infracciones[] = "Exceso de Elementos Técnicos Obligatorios: {$totales['TRE']} declarados para un cupo de $max_tre.";
                } elseif ($totales['TRE'] < $max_tre) {
                    $advertencias[] = "Faltan Elementos Técnicos Obligatorios: se han declarado {$totales['TRE']} de los $max_tre requeridos.";
                }
            }

            // 4. Verificación interna por elemento
            foreach ($elementos as $n => &$el) {
                if ($el['tipo_general'] == 'HYBRID' && !$el['is_creativo']) {
                    // Regla de familias: máx 5 movimientos por familia
                    foreach ($el['familias'] as $fam => $cant) {
                        if ($cant > 5) {
                            $el['infracciones_elem'][] = "Familia '$fam' declarada $cant veces (Límite máximo permitido: 5).";
                            $infracciones[] = "Elemento #$n: Excede el límite de 5 movimientos en la familia '$fam' ($cant movimientos).";
                        }
                    }

                    // Regla de repetición de técnica: máx 3 idénticas
                    $rep_counts = array_count_values($el['dificultades']);
                    foreach ($rep_counts as $tec => $veces) {
                        if ($veces > 3) {
                            $el['infracciones_elem'][] = "Técnica '$tec' repetida $veces veces (Límite máximo permitido: 3).";
                            $infracciones[] = "Elemento #$n: Técnica '$tec' repetida $veces veces en el mismo híbrido (Máximo 3).";
                        }
                    }

                    // Límite Alevín de seguridad
                    if (strpos(strtolower($rutina_info['categoria'] ?? ''), 'alev') !== false) {
                        $lim_alev = (strpos(strtolower($rutina_info['modalidad']), 'equipo') !== false || strpos(strtolower($rutina_info['modalidad']), 'combo') !== false) ? 4.0 : 5.0;
                        if ($el['dd_total'] > $lim_alev) {
                            $el['infracciones_elem'][] = "DD declarada ({$el['dd_total']}) supera el límite de seguridad Alevín ($lim_alev).";
                            $infracciones[] = "Elemento #$n: Supera el límite de dificultad de seguridad Alevín ({$el['dd_total']} > $lim_alev).";
                        }
                    }
                }

                if ($el['tipo_general'] == 'ACROBATIC') {
                    $el['avisos_elem'][] = "Verificar que la construcción y posiciones no se repitan con otra acrobacia del mismo grupo.";
                }

                if ($el['tipo_general'] == 'TRE') {
                    $el['avisos_elem'][] = "Los TRE deben ejecutarse en el orden reglamentario. Alterar el orden invalida el elemento (DD = 0).";
                }
            }
            unset($el);

            // Estado Global
            $estado_veredicto = 'aprobado';
            if (!empty($infracciones)) {
                $estado_veredicto = 'infraccion';
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
                       class="px-4 py-2.5 bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all flex items-center gap-2 border border-blue-100">
                        <i class="fas fa-edit"></i> Editar en Composer
                    </a>
                    <a target="_blank" href="./informes/informe_coach_card.php?titulo=Coach%20Card&id_rutina=<?php echo $id_rutina;?>&id_competicion=<?php echo $id_comp;?>" 
                       class="px-4 py-2.5 bg-white border border-slate-200 text-slate-700 hover:text-rose-600 rounded-xl text-xs font-black uppercase tracking-wider shadow-xs transition-all flex items-center gap-2">
                        <i class="fas fa-file-pdf text-rose-500"></i> PDF Oficial
                    </a>
                    <button onclick="window.print()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-black uppercase tracking-wider transition-all flex items-center gap-2">
                        <i class="fas fa-print"></i> Imprimir
                    </button>
                    <a href="coach_card_auditor.php" class="px-4 py-2.5 bg-slate-800 hover:bg-black text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all flex items-center gap-2">
                        <i class="fas fa-list"></i> Directorio
                    </a>
                </div>
            </div>

            <!-- TARJETA PRINCIPAL: SEMÁFORO Y VEREDICTO DTC -->
            <?php if ($estado_veredicto === 'infraccion'): ?>
                <div class="mb-8 p-6 sm:p-8 rounded-[2rem] bg-gradient-to-br from-rose-500 to-rose-700 text-white shadow-xl shadow-rose-500/20 animate-fade-in">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <span class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-2xl shrink-0">
                                <i class="fas fa-triangle-exclamation"></i>
                            </span>
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-widest text-rose-200 block">Dictamen Técnico</span>
                                <h2 class="text-xl sm:text-2xl font-black tracking-tight">NO CONFORME · PENALIZACIONES APLICABLES</h2>
                            </div>
                        </div>
                        <?php if ($penalizacion_pts > 0): ?>
                            <div class="px-4 py-2 rounded-xl bg-black/20 backdrop-blur-md border border-white/20 text-center">
                                <span class="text-[9px] font-black uppercase tracking-widest text-rose-200 block">Penalización Total</span>
                                <span class="text-xl font-black tabular-nums">-<?php echo number_format($penalizacion_pts, 1); ?> PTS</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mt-6 pt-6 border-t border-white/20 space-y-2">
                        <?php foreach ($infracciones as $inf): ?>
                            <div class="flex items-start gap-2.5 text-xs font-medium text-rose-50">
                                <i class="fas fa-circle-xmark text-sm mt-0.5 shrink-0 text-white"></i>
                                <span><?php echo $inf; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            <?php elseif ($estado_veredicto === 'aviso'): ?>
                <div class="mb-8 p-6 sm:p-8 rounded-[2rem] bg-gradient-to-br from-amber-500 to-amber-600 text-white shadow-xl shadow-amber-500/20 animate-fade-in">
                    <div class="flex items-center gap-4">
                        <span class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-2xl shrink-0">
                            <i class="fas fa-bell"></i>
                        </span>
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-widest text-amber-200 block">Dictamen Técnico</span>
                            <h2 class="text-xl sm:text-2xl font-black tracking-tight">CONFORME CON ADVERTENCIAS PARA MESA</h2>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-white/20 space-y-2">
                        <?php foreach ($advertencias as $adv): ?>
                            <div class="flex items-start gap-2.5 text-xs font-medium text-amber-50">
                                <i class="fas fa-circle-info text-sm mt-0.5 shrink-0 text-white"></i>
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
                            <p class="text-xs text-emerald-100 font-medium mt-0.5">Todos los límites estructurales, familias y códigos cumplen el reglamento AQUA.</p>
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

            <!-- SECCIÓN 1: REVISIÓN ESTRUCTURAL (APÉNDICE III) -->
            <div class="mb-8">
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 mb-4 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-[11px]"><i class="fas fa-list-check"></i></span>
                    1. Revisión Estructural Reglamentaria (Apéndice III World Aquatics)
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
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
                                <?php echo $hib_ok ? 'Dentro del cupo reglamentario' : 'Exceso sancionado con -2.0 pts c/u'; ?>
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
                                <?php echo $acro_ok ? 'Dentro del cupo reglamentario' : 'Exceso sancionado con -2.0 pts c/u'; ?>
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
                                <?php echo $tre_ok ? 'Cupo de TREs completo' : 'Debe coincidir exactamente con los requeridos'; ?>
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
                                                if ($el['tipo_general'] == 'HYBRID') echo 'bg-sky-50 text-sky-700 border border-sky-100';
                                                elseif ($el['tipo_general'] == 'ACROBATIC') echo 'bg-rose-50 text-rose-700 border border-rose-100';
                                                elseif ($el['tipo_general'] == 'TRE') echo 'bg-amber-50 text-amber-700 border border-amber-100';
                                                else echo 'bg-slate-100 text-slate-600';
                                            ?>">
                                                <?php echo $el['tipo_general']; ?>
                                                <?php echo !empty($el['grupo_acro']) ? ' · ' . $el['grupo_acro'] : ''; ?>
                                            </span>
                                            <?php if ($el['is_creativo']): ?>
                                                <span class="px-2 py-0.5 rounded-lg bg-purple-50 text-purple-700 text-[10px] font-black uppercase border border-purple-100">
                                                    Creativo
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-4">
                                    <div class="text-right">
                                        <span class="text-[9px] font-black uppercase text-slate-400 block tracking-widest">Tiempos</span>
                                        <span class="font-mono text-xs font-bold text-slate-600 tabular-nums">
                                            <?php echo !empty($el['time_inicio']) ? $el['time_inicio'] : '--:--'; ?> - 
                                            <?php echo !empty($el['time_fin']) ? $el['time_fin'] : '--:--'; ?>
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
                                            <span class="text-xs text-slate-400 italic">Sin movimientos declarados</span>
                                        <?php endif; ?>
                                    </div>
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
                                            <span>Elemento conforme: límites de repetición y familias respetados.</span>
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
