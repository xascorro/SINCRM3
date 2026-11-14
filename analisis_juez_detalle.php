<?php
$start_time = microtime(true);
include('security.php');
include('includes/header.php');
include('includes/navbar.php');

$pjs_str = $_GET['pjs'] ?? '';
$competicion_id = $_GET['comp_id'] ?? $_SESSION['id_competicion_usuario'] ?? 0;

if (!$pjs_str || !$competicion_id) {
    header('Location: analisis_jueces.php');
    exit();
}

$pjs_array = explode(',', $pjs_str);
$pjs_clean = implode(',', array_map('intval', $pjs_array));

// 1. Verificar si existen datos grabados
$q_check = "SELECT * FROM auditoria_jueces_stats WHERE id_competicion = $competicion_id AND pjs_asociados = '".mysqli_real_escape_string($connection, $pjs_str)."' AND entidad_tipo = 'GLOBAL' LIMIT 1";
$res_check = mysqli_query($connection, $q_check);
$saved_global = mysqli_fetch_assoc($res_check);
$has_data = (bool)$saved_global;

// Intentar obtener metadatos del juez
$q_meta = "SELECT j.* FROM panel_jueces p JOIN jueces j ON p.id_juez = j.id WHERE p.id IN ($pjs_clean) LIMIT 1";
$res_meta = mysqli_query($connection, $q_meta);
$juez = mysqli_fetch_assoc($res_meta);
$id_juez = $juez ? (int)$juez['id'] : 0;
$nombre_completo = ($juez) ? ($juez['nombre'] . ' ' . $juez['apellidos']) : "Juez Desconocido (ID $pjs_clean)";
if (in_array(108, $pjs_array)) $nombre_completo = "Juez MEDIA (Automático)";

$es_admin_audit = in_array($_SESSION['id_rol'] ?? 0, [1, 2, 3]);
$es_usuario_juez = (($_SESSION['id_rol'] ?? 0) == 4 || (!empty($_SESSION['id_juez_v3']) && !$es_admin_audit));

if ($es_usuario_juez && $id_juez != ($_SESSION['id_juez_v3'] ?? 0)) {
    $nombre_completo = "Juez Anónimo";
}

write_log("Consulta de Auditoría BIAS (Detalle Juez) para: $nombre_completo", "INFO");

/**
 * CARGA DE DATOS (Persistente vs Live)
 */
$tree = []; $club_bias = []; $severity_sum = 0; $severity_count = 0; $penalization_points = 0; $precision_count = 0;
$scale_bins = ['< 4.0' => 0, '4.0 - 4.9' => 0, '5.0 - 5.9' => 0, '6.0 - 6.9' => 0, '7.0 - 7.9' => 0, '≥ 8.0' => 0];
$max_individual_deviation = null;
$chronological_notes = [];
$type_stats = [
    'Figura' => ['count' => 0, 'precision' => 0, 'bias_sum' => 0],
    'Rutina' => ['count' => 0, 'precision' => 0, 'bias_sum' => 0]
];

$query_base = "
    (SELECT 
        pj.id as id_puntuacion, pj.nota, 'Figura' as tipo, cat.nombre as categoria_nombre,
        COALESCE(CONCAT(cat.nombre, ' - ', modal.nombre), 'Fase Figuras') as fase_nombre,
        COALESCE(
            (SELECT texto FROM hibridos_rutina hr WHERE hr.id_rutina = i.id AND hr.elemento = pj.id_elemento AND hr.tipo = 'dd' AND hr.texto != '' LIMIT 1),
            (SELECT texto FROM hibridos_rutina hr WHERE hr.id_rutina = i.id AND hr.elemento = pj.id_elemento AND hr.tipo = 'basemark' AND hr.texto != '' LIMIT 1),
            CONCAT(fig.numero, ' ', fig.nombre),
            CONCAT('Figura ', pj.id_elemento)
        ) as elemento_nombre,
        CONCAT(nad.apellidos, ', ', nad.nombre) as nadadora_nombre, cl.nombre_corto as club_nombre,
        pj.id_inscripcion_figuras, pj.id_elemento, pj.id_rutina, pj.tipo_ia
    FROM puntuaciones_jueces pj
    JOIN inscripciones_figuras i ON pj.id_inscripcion_figuras = i.id
    JOIN nadadoras nad ON i.id_nadadora = nad.id
    LEFT JOIN clubes cl ON nad.club = cl.id
    LEFT JOIN fases f ON i.id_fase = f.id
    LEFT JOIN categorias cat ON f.id_categoria = cat.id
    LEFT JOIN modalidades modal ON f.id_modalidad = modal.id
    LEFT JOIN figuras fig ON (CASE WHEN pj.id_elemento > 0 AND f.elementos_coach_card = 0 THEN pj.id_elemento ELSE f.id_figura END) = fig.id
    WHERE pj.id_panel_juez IN ($pjs_clean) AND i.id_competicion = '$competicion_id' AND pj.id_inscripcion_figuras IS NOT NULL)
    
    UNION ALL

    (SELECT 
        pj.id as id_puntuacion, pj.nota, 'Rutina' as tipo, cat.nombre as categoria_nombre,
        COALESCE(CONCAT(cat.nombre, ' - ', modal.nombre), 'Fase Rutina') as fase_nombre,
        COALESCE(
            (SELECT texto FROM hibridos_rutina hr WHERE hr.id_rutina = pj.id_rutina AND hr.elemento = pj.id_elemento AND hr.tipo = 'dd' AND hr.texto != '' LIMIT 1),
            (SELECT texto FROM hibridos_rutina hr WHERE hr.id_rutina = pj.id_rutina AND hr.elemento = pj.id_elemento AND hr.tipo = 'basemark' AND hr.texto != '' LIMIT 1),
            pj.tipo_ia
        ) as elemento_nombre,
        COALESCE((SELECT GROUP_CONCAT(CONCAT(n.apellidos, ', ', n.nombre) SEPARATOR ' / ') FROM rutinas_participantes rp JOIN nadadoras n ON rp.id_nadadora = n.id WHERE rp.id_rutina = pj.id_rutina), 'Equipo/Dúo') as nadadora_nombre,
        cl.nombre_corto as club_nombre,
        pj.id_inscripcion_figuras, pj.id_elemento, pj.id_rutina, pj.tipo_ia
    FROM puntuaciones_jueces pj
    JOIN rutinas r ON pj.id_rutina = r.id
    LEFT JOIN clubes cl ON r.id_club = cl.id
    LEFT JOIN fases f ON r.id_fase = f.id
    LEFT JOIN categorias cat ON f.id_categoria = cat.id
    LEFT JOIN modalidades modal ON f.id_modalidad = modal.id
    WHERE pj.id_panel_juez IN ($pjs_clean) AND r.id_competicion = '$competicion_id' AND pj.id_rutina IS NOT NULL)
    
    ORDER BY categoria_nombre, nadadora_nombre, elemento_nombre
";

$res_meta_notes = mysqli_query($connection, $query_base);

while ($n = mysqli_fetch_assoc($res_meta_notes)) {
    $cat = $n['categoria_nombre'] ?: 'Sin Categoría';
    $swimmer = $n['nadadora_nombre'] . ' [' . $n['club_nombre'] . ']';
    $club = $n['club_nombre'] ?: 'S/C';
    $id_p = $n['id_puntuacion'];

    if ($has_data) {
        $q_p = "SELECT * FROM auditoria_jueces_puntos WHERE id_puntuacion = $id_p LIMIT 1";
        $saved_p = mysqli_fetch_assoc(mysqli_query($connection, $q_p));
        $consenso = (float)($saved_p['valor_consenso'] ?? 0);
        $bias = (float)($saved_p['bias_score'] ?? 0);
        $estado_int = (int)($saved_p['estado'] ?? 0);
        $n['media_consenso'] = $consenso; $n['bias_score'] = $bias;
        $n['min_fila'] = ($estado_int == 1) ? $n['nota'] : 0;
        $n['max_fila'] = ($estado_int == 2) ? $n['nota'] : 10;
    } else {
        $q_live = "SELECT 
            (SELECT MIN(pj2.nota) FROM puntuaciones_jueces pj2 WHERE pj2.id_inscripcion_figuras = '{$n['id_inscripcion_figuras']}' AND COALESCE(pj2.id_elemento, 0) = COALESCE('{$n['id_elemento']}', 0) AND '{$n['id_inscripcion_figuras']}' > 0) as min_live_f,
            (SELECT MAX(pj2.nota) FROM puntuaciones_jueces pj2 WHERE pj2.id_inscripcion_figuras = '{$n['id_inscripcion_figuras']}' AND COALESCE(pj2.id_elemento, 0) = COALESCE('{$n['id_elemento']}', 0) AND '{$n['id_inscripcion_figuras']}' > 0) as max_live_f,
            (SELECT MIN(pj2.nota) FROM puntuaciones_jueces pj2 WHERE pj2.id_rutina = '{$n['id_rutina']}' AND COALESCE(pj2.id_elemento, 0) = COALESCE('{$n['id_elemento']}', 0) AND COALESCE(pj2.tipo_ia,'') = COALESCE('{$n['tipo_ia']}','') AND '{$n['id_rutina']}' > 0) as min_live_r,
            (SELECT MAX(pj2.nota) FROM puntuaciones_jueces pj2 WHERE pj2.id_rutina = '{$n['id_rutina']}' AND COALESCE(pj2.id_elemento, 0) = COALESCE('{$n['id_elemento']}', 0) AND COALESCE(pj2.tipo_ia,'') = COALESCE('{$n['tipo_ia']}','') AND '{$n['id_rutina']}' > 0) as max_live_r,
            (SELECT ROUND(AVG(pj3.nota), 2) FROM puntuaciones_jueces pj3 WHERE (('{$n['id_inscripcion_figuras']}' > 0 AND pj3.id_inscripcion_figuras = '{$n['id_inscripcion_figuras']}' AND COALESCE(pj3.id_elemento, 0) = COALESCE('{$n['id_elemento']}', 0)) OR ('{$n['id_rutina']}' > 0 AND pj3.id_rutina = '{$n['id_rutina']}' AND COALESCE(pj3.id_elemento, 0) = COALESCE('{$n['id_elemento']}', 0) AND COALESCE(pj3.tipo_ia,'') = COALESCE('{$n['tipo_ia']}',''))) AND (pj3.nota_menor IS NULL OR pj3.nota_menor = '' OR pj3.nota_menor = '0' OR pj3.nota_menor = 'no') AND (pj3.nota_mayor IS NULL OR pj3.nota_mayor = '' OR pj3.nota_mayor = '0' OR pj3.nota_mayor = 'no')) as consensus_live";
        $res_l = mysqli_fetch_assoc(mysqli_query($connection, $q_live));
        $consenso = (float)($res_l['consensus_live'] ?? 0);
        $bias = (float)$n['nota'] - $consenso;
        $n['media_consenso'] = $consenso; $n['bias_score'] = $bias;
        $n['min_fila'] = $res_l['min_live_f'] ?? $res_l['min_live_r'];
        $n['max_fila'] = $res_l['max_live_f'] ?? $res_l['max_live_r'];
    }

    if (!isset($tree[$cat])) $tree[$cat] = ['total_n' => 0, 'sum_n' => 0, 'bajas' => 0, 'altas' => 0, 'd_sum' => 0, 'd_count' => 0, 'swimmers' => []];
    if (!isset($tree[$cat]['swimmers'][$swimmer])) $tree[$cat]['swimmers'][$swimmer] = ['nombre' => $n['nadadora_nombre'], 'club' => $club, 'total_n' => 0, 'sum_n' => 0, 'bajas' => 0, 'altas' => 0, 'd_sum' => 0, 'd_count' => 0, 'items' => []];

    $nota = (float)$n['nota']; $min = (float)$n['min_fila']; $max = (float)$n['max_fila'];
    if (abs($bias) <= 0.2) $precision_count++;
    if (abs($bias) >= 0.5) $penalization_points++;
    $severity_sum += $bias; $severity_count++;

    // Desglose por tipo (Figuras vs Rutinas)
    $tipo_item = ($n['tipo'] == 'Rutina') ? 'Rutina' : 'Figura';
    $type_stats[$tipo_item]['count']++;
    $type_stats[$tipo_item]['bias_sum'] += $bias;
    if (abs($bias) <= 0.2) $type_stats[$tipo_item]['precision']++;

    // Desviación Máxima Individual (Outlier)
    if (!$max_individual_deviation || abs($bias) > abs($max_individual_deviation['bias'])) {
        $max_individual_deviation = [
            'swimmer' => $n['nadadora_nombre'],
            'club' => $club,
            'elemento' => $n['elemento_nombre'],
            'fase' => $n['fase_nombre'],
            'nota' => $nota,
            'consenso' => $consenso,
            'bias' => $bias
        ];
    }

    // Distribución del uso de la escala
    if ($nota < 4.0) $scale_bins['< 4.0']++;
    elseif ($nota < 5.0) $scale_bins['4.0 - 4.9']++;
    elseif ($nota < 6.0) $scale_bins['5.0 - 5.9']++;
    elseif ($nota < 7.0) $scale_bins['6.0 - 6.9']++;
    elseif ($nota < 8.0) $scale_bins['7.0 - 7.9']++;
    else $scale_bins['≥ 8.0']++;

    if (!isset($club_bias[$club])) $club_bias[$club] = ['sum' => 0, 'count' => 0, 'bajas' => 0, 'altas' => 0];
    $club_bias[$club]['sum'] += $bias; $club_bias[$club]['count']++;
    if ($nota <= $min && $min > 0) { $club_bias[$club]['bajas']++; $tree[$cat]['swimmers'][$swimmer]['bajas']++; $tree[$cat]['bajas']++; }
    elseif ($nota >= $max && $max < 10) { $club_bias[$club]['altas']++; $tree[$cat]['swimmers'][$swimmer]['altas']++; $tree[$cat]['altas']++; }

    $tree[$cat]['swimmers'][$swimmer]['total_n']++; $tree[$cat]['swimmers'][$swimmer]['sum_n'] += $nota;
    $tree[$cat]['swimmers'][$swimmer]['d_sum'] += abs($bias); $tree[$cat]['swimmers'][$swimmer]['d_count']++;
    $tree[$cat]['swimmers'][$swimmer]['items'][] = $n;
    $tree[$cat]['total_n']++; $tree[$cat]['sum_n'] += $nota; $tree[$cat]['d_sum'] += abs($bias); $tree[$cat]['d_count']++;
    $chronological_notes[] = [
        'nota' => $nota,
        'consenso' => $consenso,
        'bias' => $bias,
        'abs_bias' => abs($bias),
        'is_aqua' => (abs($bias) <= 0.2)
    ];
}

// Análisis de Fatiga Arbitral y Deriva Temporal (Panel Drift)
$fatigue_data = null;
$total_chrono = count($chronological_notes);
if ($total_chrono >= 12) {
    $slice_size = max(4, (int)floor($total_chrono * 0.25));
    $first_slice = array_slice($chronological_notes, 0, $slice_size);
    $last_slice = array_slice($chronological_notes, -$slice_size);

    $prec_first = round((count(array_filter($first_slice, function($x) { return $x['is_aqua']; })) / $slice_size) * 100, 1);
    $prec_last = round((count(array_filter($last_slice, function($x) { return $x['is_aqua']; })) / $slice_size) * 100, 1);

    $bias_first = round(array_sum(array_column($first_slice, 'bias')) / $slice_size, 3);
    $bias_last = round(array_sum(array_column($last_slice, 'bias')) / $slice_size, 3);

    $drift_bias = round($bias_last - $bias_first, 3);
    $drift_prec = round($prec_last - $prec_first, 1);

    $fatigue_status = "Criterio Estable";
    $fatigue_color = "text-emerald-700 bg-emerald-50 border-emerald-200";
    $fatigue_desc = "Criterio y precisión homogéneos entre el inicio y el final de la sesión.";

    if ($drift_prec <= -15.0) {
        $fatigue_status = "Fatiga Detectada";
        $fatigue_color = "text-rose-700 bg-rose-50 border-rose-200";
        $fatigue_desc = "La precisión descendió un " . abs($drift_prec) . "% entre el inicio y el tramo final.";
    } elseif ($drift_bias >= 0.25) {
        $fatigue_status = "Deriva Benévola";
        $fatigue_color = "text-amber-700 bg-amber-50 border-amber-200";
        $fatigue_desc = "Apertura progresiva de escala (+". $drift_bias ." ptos al final vs inicio).";
    } elseif ($drift_bias <= -0.25) {
        $fatigue_status = "Deriva Rigurosa";
        $fatigue_color = "text-amber-700 bg-amber-50 border-amber-200";
        $fatigue_desc = "Mayor exigencia técnica en el tramo final (-". abs($drift_bias) ." ptos vs inicio).";
    }

    $fatigue_data = [
        'slice_size' => $slice_size,
        'prec_first' => $prec_first,
        'prec_last' => $prec_last,
        'bias_first' => $bias_first,
        'bias_last' => $bias_last,
        'drift_bias' => $drift_bias,
        'drift_prec' => $drift_prec,
        'status' => $fatigue_status,
        'color' => $fatigue_color,
        'desc' => $fatigue_desc
    ];
}

// Cálculo de Asimetrías por Club
$asymmetry_alert_club = null;
$major_bias = null;
foreach ($club_bias as $c => &$c_data) {
    $avg = $c_data['sum'] / max(1, $c_data['count']);
    $c_data['avg'] = $avg;
    $ratio_ab = ($c_data['bajas'] > 0) ? round($c_data['altas'] / $c_data['bajas'], 1) : ($c_data['altas'] > 0 ? (float)$c_data['altas'] : 1.0);
    $c_data['ratio_ab'] = $ratio_ab;
    $c_data['asimetria'] = 'Equilibrado';
    $c_data['asimetria_badge'] = 'text-slate-500 bg-slate-50 border-slate-200';

    if ($c_data['count'] >= 4) {
        if ($c_data['altas'] >= 4 && ($c_data['bajas'] <= 1 || $c_data['altas'] >= 2.5 * $c_data['bajas'])) {
            $c_data['asimetria'] = 'Asimetría Altas';
            $c_data['asimetria_badge'] = 'text-amber-700 bg-amber-50 border-amber-200';
            if (!$asymmetry_alert_club) {
                $asymmetry_alert_club = ['club' => $c, 'altas' => $c_data['altas'], 'bajas' => $c_data['bajas'], 'ratio' => $ratio_ab];
            }
        } elseif ($c_data['bajas'] >= 4 && ($c_data['altas'] <= 1 || $c_data['bajas'] >= 2.5 * $c_data['altas'])) {
            $c_data['asimetria'] = 'Asimetría Bajas';
            $c_data['asimetria_badge'] = 'text-rose-700 bg-rose-50 border-rose-200';
            if (!$asymmetry_alert_club) {
                $asymmetry_alert_club = ['club' => $c, 'altas' => $c_data['altas'], 'bajas' => $c_data['bajas'], 'ratio' => $ratio_ab];
            }
        }
    }

    if (abs($avg) >= 0.35 && $c_data['count'] >= 3) {
        if (!$major_bias || abs($avg) > abs($major_bias['avg'])) $major_bias = ['club' => $c, 'avg' => $avg];
    }
}
unset($c_data);

// Coherencia de Clasificación (Spearman Rank Correlation) y Mayor Salto de Puesto
$all_jumps = [];
$global_spearman_sum = 0;
$global_spearman_count = 0;

foreach ($tree as $cat_nombre => $cat_data) {
    $sw_ranks = [];
    foreach ($cat_data['swimmers'] as $sw_name => $sw_data) {
        $consensus_sum = 0;
        $consensus_count = 0;
        foreach ($sw_data['items'] as $item) {
            if ($item['media_consenso'] !== null) {
                $consensus_sum += (float)$item['media_consenso'];
                $consensus_count++;
            }
        }
        $sw_ranks[$sw_name] = [
            'nombre' => $sw_data['nombre'],
            'club' => $sw_data['club'],
            'judge_avg' => $sw_data['sum_n'] / max(1, $sw_data['total_n']),
            'consensus_avg' => ($consensus_count > 0) ? ($consensus_sum / $consensus_count) : 0
        ];
    }

    if (count($sw_ranks) >= 3) {
        uasort($sw_ranks, function($a, $b) { return $b['judge_avg'] <=> $a['judge_avg']; });
        $rj = 1;
        foreach ($sw_ranks as $k => $v) { $sw_ranks[$k]['rj'] = $rj++; }

        uasort($sw_ranks, function($a, $b) { return $b['consensus_avg'] <=> $a['consensus_avg']; });
        $rc = 1;
        foreach ($sw_ranks as $k => $v) { $sw_ranks[$k]['rc'] = $rc++; }

        $n = count($sw_ranks);
        $d2 = 0;
        foreach ($sw_ranks as $k => $v) {
            $diff_r = $v['rj'] - $v['rc'];
            $d2 += ($diff_r * $diff_r);
            $jump = $v['rc'] - $v['rj']; // positivo = subió puestos con este juez
            $all_jumps[] = [
                'nombre' => $v['nombre'],
                'club' => $v['club'],
                'categoria' => $cat_nombre,
                'jump' => $jump,
                'rj' => $v['rj'],
                'rc' => $v['rc']
            ];
        }
        $spearman_rho = 1 - ((6 * $d2) / ($n * ($n * $n - 1)));
        $global_spearman_sum += $spearman_rho;
        $global_spearman_count++;
    }
}

$spearman_pct = ($global_spearman_count > 0) ? round(($global_spearman_sum / $global_spearman_count) * 100, 1) : 100;
uasort($all_jumps, function($a, $b) { return abs($b['jump']) <=> abs($a['jump']); });
$biggest_jump = !empty($all_jumps) ? reset($all_jumps) : null;
$total_severity_avg = ($severity_count > 0) ? ($severity_sum / $severity_count) : 0;
$precision_pct = ($severity_count > 0) ? round(($precision_count / $severity_count) * 100, 1) : 0;
$severity_label = "Criterio Equilibrado"; $severity_class = "text-blue-700 bg-blue-50 border-blue-200";
if ($total_severity_avg <= -0.18) { $severity_label = "Criterio Exigente"; $severity_class = "text-rose-700 bg-rose-50 border-rose-200"; }
elseif ($total_severity_avg >= 0.18) { $severity_label = "Criterio Benévolo"; $severity_class = "text-emerald-700 bg-emerald-50 border-emerald-200"; }

// Carga de desglose por FIGURA / ELEMENTO
$q_fig_stats = "SELECT * FROM auditoria_jueces_stats 
                WHERE id_competicion = $competicion_id 
                  AND pjs_asociados = '".mysqli_real_escape_string($connection, $pjs_str)."' 
                  AND entidad_tipo = 'FIGURA' 
                ORDER BY total_notas DESC";
$res_fig_stats = mysqli_query($connection, $q_fig_stats);
$figuras_audit = [];
while($fa = mysqli_fetch_assoc($res_fig_stats)) {
    $figuras_audit[] = $fa;
}

// Generación de Smart Insights de Calibración
$smart_insights = [];
if ($precision_pct >= 65) {
    $smart_insights[] = ['icon' => 'fa-circle-check', 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50 border-emerald-100', 'title' => 'Excelente Alineación AQUA', 'desc' => "El {$precision_pct}% de tus calificaciones se ubicaron dentro del margen oficial de concordancia (±0.2 puntos)."];
} elseif ($precision_pct >= 50) {
    $smart_insights[] = ['icon' => 'fa-circle-info', 'color' => 'text-blue-600', 'bg' => 'bg-blue-50 border-blue-100', 'title' => 'Buen Nivel de Consistencia', 'desc' => "Alineación media del {$precision_pct}%. Se recomienda prestar atención a los detalles de altura y extensión en deducciones medias."];
} else {
    $smart_insights[] = ['icon' => 'fa-triangle-exclamation', 'color' => 'text-amber-600', 'bg' => 'bg-amber-50 border-amber-100', 'title' => 'Discrepancia a Calibrar', 'desc' => "Concordancia del {$precision_pct}% respecto a la mediana del panel. Buen punto de partida para debriefing con el árbitro."];
}

if (abs($total_severity_avg) <= 0.12) {
    $smart_insights[] = ['icon' => 'fa-scale-balanced', 'color' => 'text-indigo-600', 'bg' => 'bg-indigo-50 border-indigo-100', 'title' => 'Criterio Muy Centrado', 'desc' => "Sesgo firmado casi nulo (" . ($total_severity_avg >= 0 ? '+' : '') . number_format($total_severity_avg, 3) . " ptos), indicando neutralidad de escala."];
} elseif ($total_severity_avg < -0.12) {
    $smart_insights[] = ['icon' => 'fa-arrow-down-wide-short', 'color' => 'text-rose-600', 'bg' => 'bg-rose-50 border-rose-100', 'title' => 'Tendencia Severa / Rigurosa', 'desc' => "Tus notas se sitúan en promedio " . number_format($total_severity_avg, 3) . " puntos por debajo del consenso arbitral."];
} else {
    $smart_insights[] = ['icon' => 'fa-arrow-up-wide-short', 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50 border-emerald-100', 'title' => 'Tendencia Abierta / Benévola', 'desc' => "Tus notas se sitúan en promedio +" . number_format($total_severity_avg, 3) . " puntos por encima del consenso arbitral."];
}

if ($asymmetry_alert_club) {
    $smart_insights[] = [
        'icon' => 'fa-scale-unbalanced-flip',
        'color' => 'text-amber-600',
        'bg' => 'bg-amber-50 border-amber-200',
        'title' => 'Asimetría Detectada en ' . $asymmetry_alert_club['club'],
        'desc' => "Proporción de {$asymmetry_alert_club['altas']} notas máximas frente a {$asymmetry_alert_club['bajas']} mínimas (ratio {$asymmetry_alert_club['ratio']}x) en nadadoras de {$asymmetry_alert_club['club']}. Pauta clave para asegurar equidad entre entidades."
    ];
}

if ($biggest_jump && abs($biggest_jump['jump']) >= 3) {
    $dir_jump = ($biggest_jump['jump'] > 0) ? "ascendió {$biggest_jump['jump']} puestos" : "descendió " . abs($biggest_jump['jump']) . " puestos";
    $smart_insights[] = [
        'icon' => 'fa-arrow-up-right-dots',
        'color' => 'text-indigo-600',
        'bg' => 'bg-indigo-50 border-indigo-100',
        'title' => 'Mayor Desplazamiento de Podio',
        'desc' => "En categoría {$biggest_jump['categoria']}, {$biggest_jump['nombre']} ({$biggest_jump['club']}) {$dir_jump} en tu evaluación (Puesto #{$biggest_jump['rj']}) vs consenso general (Puesto #{$biggest_jump['rc']})."
    ];
}

if ($major_bias) {
    $smart_insights[] = ['icon' => 'fa-building-flag', 'color' => 'text-amber-600', 'bg' => 'bg-amber-50 border-amber-100', 'title' => 'Alerta de Sesgo por Entidad', 'desc' => "Se observa una desviación de " . ($major_bias['avg'] > 0 ? '+' : '') . number_format($major_bias['avg'], 3) . " puntos en notas a nadadoras de {$major_bias['club']}."];
} elseif (!$asymmetry_alert_club) {
    $smart_insights[] = ['icon' => 'fa-shield-halved', 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50 border-emerald-100', 'title' => 'Objetividad Imparcial', 'desc' => "Distribución homogénea entre todos los clubes evaluados, sin sesgo de favoritismo ni severidad."];
}

// Carga de Jueces para Comparativa Cara a Cara (Head-to-Head)
$q_other_judges = "SELECT DISTINCT p.id_juez, j.nombre, j.apellidos, GROUP_CONCAT(DISTINCT pj.id_panel_juez) as pjs
                   FROM puntuaciones_jueces pj
                   JOIN panel_jueces p ON pj.id_panel_juez = p.id
                   JOIN jueces j ON p.id_juez = j.id
                   LEFT JOIN inscripciones_figuras i ON pj.id_inscripcion_figuras = i.id
                   LEFT JOIN rutinas r ON pj.id_rutina = r.id
                   WHERE (i.id_competicion = '$competicion_id' OR r.id_competicion = '$competicion_id')
                     AND p.id_juez > 0
                   GROUP BY p.id_juez
                   HAVING p.id_juez != '$id_juez'
                   ORDER BY j.apellidos, j.nombre";
$res_oj = mysqli_query($connection, $q_other_judges);
$other_judges = [];
$anon_counter = 1;
if ($res_oj) {
    while ($oj = mysqli_fetch_assoc($res_oj)) {
        $display_oj_name = $oj['apellidos'] . ', ' . $oj['nombre'];
        if ($es_usuario_juez && $oj['id_juez'] != ($_SESSION['id_juez_v3'] ?? 0)) {
            $display_oj_name = "Juez Anónimo #" . $anon_counter++;
        }
        $other_judges[] = [
            'id_juez' => $oj['id_juez'],
            'nombre' => $display_oj_name,
            'pjs' => $oj['pjs']
        ];
    }
}

// Análisis Cara a Cara si se solicita vs_pjs
$vs_pjs = isset($_GET['vs_pjs']) ? mysqli_real_escape_string($connection, $_GET['vs_pjs']) : '';
$h2h_data = null;
if (!empty($vs_pjs)) {
    // Buscar nombre del juez rival
    $q_vs_name = "SELECT j.nombre, j.apellidos, p.id_juez FROM panel_jueces p JOIN jueces j ON p.id_juez = j.id WHERE p.id IN ($vs_pjs) LIMIT 1";
    $r_vsn = mysqli_fetch_assoc(mysqli_query($connection, $q_vs_name));
    $vs_juez_name = $r_vsn ? ($r_vsn['apellidos'] . ', ' . $r_vsn['nombre']) : "Juez Seleccionado";
    if ($es_usuario_juez && ($r_vsn['id_juez'] ?? 0) != ($_SESSION['id_juez_v3'] ?? 0)) {
        $vs_juez_name = "Juez Anónimo";
    }

    $q_h2h = "SELECT 
                pj1.nota as nota_a, pj2.nota as nota_b,
                (pj1.nota - pj2.nota) as diff,
                nad.nombre, nad.apellidos, cl.nombre_corto as club_nombre,
                COALESCE(
                    (SELECT texto FROM hibridos_rutina hr WHERE hr.id_rutina = i.id AND hr.elemento = pj1.id_elemento AND hr.tipo = 'dd' AND hr.texto != '' LIMIT 1),
                    CONCAT(fig.numero, ' ', fig.nombre),
                    CONCAT('Figura ', pj1.id_elemento)
                ) as elemento_nombre,
                COALESCE(cat.nombre, 'General') as categoria_nombre
              FROM puntuaciones_jueces pj1
              JOIN puntuaciones_jueces pj2 ON 
                  pj1.id_inscripcion_figuras = pj2.id_inscripcion_figuras 
                  AND COALESCE(pj1.id_elemento, 0) = COALESCE(pj2.id_elemento, 0)
              JOIN inscripciones_figuras i ON pj1.id_inscripcion_figuras = i.id
              JOIN nadadoras nad ON i.id_nadadora = nad.id
              LEFT JOIN clubes cl ON nad.club = cl.id
              LEFT JOIN fases f ON i.id_fase = f.id
              LEFT JOIN categorias cat ON f.id_categoria = cat.id
              LEFT JOIN figuras fig ON (CASE WHEN pj1.id_elemento > 0 AND f.elementos_coach_card = 0 THEN pj1.id_elemento ELSE f.id_figura END) = fig.id
              WHERE pj1.id_panel_juez IN ($pjs_clean) AND pj2.id_panel_juez IN ($vs_pjs)
              ORDER BY ABS(pj1.nota - pj2.nota) DESC";
    $res_h2h = mysqli_query($connection, $q_h2h);
    $h2h_items = [];
    $h2h_diff_sum = 0; $h2h_concord_2 = 0; $h2h_concord_5 = 0;
    if ($res_h2h) {
        while ($h = mysqli_fetch_assoc($res_h2h)) {
            $h2h_items[] = $h;
            $h2h_diff_sum += (float)$h['diff'];
            if (abs((float)$h['diff']) <= 0.20) $h2h_concord_2++;
            if (abs((float)$h['diff']) <= 0.50) $h2h_concord_5++;
        }
    }
    $h2h_total = count($h2h_items);
    if ($h2h_total > 0) {
        $h2h_data = [
            'vs_nombre' => $vs_juez_name,
            'total' => $h2h_total,
            'avg_diff' => round($h2h_diff_sum / $h2h_total, 3),
            'concord_2_pct' => round(($h2h_concord_2 / $h2h_total) * 100, 1),
            'concord_5_pct' => round(($h2h_concord_5 / $h2h_total) * 100, 1),
            'items' => $h2h_items
        ];
    }
}
?>

<style>
@media print {
    .no-print, header, nav, #sidebar, #topbar, .topbar { display: none !important; }
    body, main { background: #ffffff !important; padding: 0 !important; margin: 0 !important; }
    .max-w-7xl { max-width: 100% !important; padding: 0 !important; }
    .card-hover, .shadow-sm { box-shadow: none !important; border: 1px solid #e2e8f0 !important; }
    .print-break { page-break-before: always; }
}
</style>

<main class="flex-1 flex flex-col min-w-0 bg-surface">
    <?php include('includes/topbar.php'); ?>
    
    <div class="px-3 py-4 sm:p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend space-y-6 sm:space-y-8">
        
        <!-- Header de Sección -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 sm:gap-6">
            <div>
                <a href="analisis_jueces.php?comp_id=<?php echo $competicion_id; ?>" class="text-blue-600 font-bold text-xs uppercase tracking-widest mb-2 flex items-center gap-1.5 hover:gap-2 transition-all no-underline no-print">
                    <i class="fas fa-arrow-left text-[10px]"></i> Volver a Auditoría del Evento
                </a>
                <h1 class="text-2xl sm:text-4xl font-black text-slate-800 tracking-tighter mb-1 sm:mb-2 flex items-center gap-2.5 sm:gap-3">
                    <span class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 flex items-center justify-center text-primary shadow-sm border border-slate-200">
                        <i class="fas fa-user-check text-base sm:text-lg"></i>
                    </span>
                    <?php echo htmlspecialchars($nombre_completo); ?>
                </h1>
                <div class="flex flex-wrap items-center gap-2 mt-1">
                    <span class="px-2.5 py-0.5 rounded-md text-[9px] font-black uppercase border <?php echo $severity_class; ?>">
                        <?php echo $severity_label; ?>
                    </span>
                    <?php if($penalization_points > 5): ?>
                        <span class="px-2.5 py-0.5 bg-amber-50 text-amber-700 border border-amber-200 rounded-md text-[9px] font-black uppercase">
                            Frecuencia de Extremos
                        </span>
                    <?php endif; ?>
                    <?php if(!$has_data): ?>
                        <span class="px-2.5 py-0.5 bg-amber-100 text-amber-700 rounded-md text-[9px] font-black uppercase">
                            Modo Provisional
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Acciones Header -->
            <div class="flex items-center gap-2.5 self-start md:self-auto no-print">
                <button onclick="window.print()" class="px-3.5 py-2 bg-white text-slate-700 border border-slate-200 rounded-xl sm:rounded-2xl text-xs font-bold shadow-sm hover:bg-slate-50 active:scale-[0.98] transition-all flex items-center gap-2">
                    <i class="fas fa-print text-xs text-blue-600"></i>
                    <span>Imprimir Informe</span>
                </button>

                <!-- Badge Carga -->
                <div class="px-3.5 py-2 bg-slate-900 text-white rounded-xl sm:rounded-2xl shadow-sm flex items-center gap-2.5 border border-slate-800">
                    <i class="fas fa-bolt text-amber-400 text-xs"></i>
                    <div class="leading-none">
                        <p class="text-[8px] font-black uppercase text-slate-400 mb-0.5 tracking-widest">Carga</p>
                        <p class="text-xs font-black tabular-nums"><?php echo round(microtime(true) - $start_time, 3); ?> <span class="text-[9px] opacity-60">seg</span></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPIs Resumen Forense (World Aquatics) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-6">
            <!-- KPI 1: Sesgo Global -->
            <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200 card-hover flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest truncate">Sesgo Global (Bias)</p>
                        <i class="fas fa-scale-balanced text-xs text-blue-500"></i>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-black <?php echo ($total_severity_avg >= 0) ? 'text-emerald-600' : 'text-rose-600'; ?> tabular-nums">
                        <?php echo ($total_severity_avg >= 0 ? '+' : '') . number_format($total_severity_avg, 3); ?>
                    </h3>
                </div>
                <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-[9px] font-bold text-slate-500">
                    <span>Fig: <?php echo $type_stats['Figura']['count']; ?> (<?php echo ($type_stats['Figura']['count'] > 0 ? round(($type_stats['Figura']['precision'] / $type_stats['Figura']['count']) * 100) : 0); ?>%)</span>
                    <?php if($type_stats['Rutina']['count'] > 0): ?>
                        <span>Rut: <?php echo $type_stats['Rutina']['count']; ?> (<?php echo round(($type_stats['Rutina']['precision'] / $type_stats['Rutina']['count']) * 100); ?>%)</span>
                    <?php else: ?>
                        <span class="text-slate-400">Panel Único</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- KPI 2: Precisión AQUA -->
            <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200 card-hover flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest truncate">Precisión AQUA</p>
                        <i class="fas fa-bullseye text-xs text-emerald-500"></i>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-black <?php echo ($precision_pct >= 70 ? 'text-emerald-600' : ($precision_pct >= 50 ? 'text-blue-600' : 'text-amber-500')); ?> tabular-nums">
                        <?php echo $precision_pct; ?>%
                    </h3>
                </div>
                <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-[9px] font-bold text-slate-500">
                    <span>≤ 0.2 ptos oficiales</span>
                    <span class="tabular-nums"><?php echo $severity_count; ?> notas</span>
                </div>
            </div>

            <!-- KPI 3: Coherencia de Clasificación (Spearman Rank) -->
            <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200 card-hover flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest truncate">Coherencia de Podio</p>
                        <i class="fas fa-trophy text-xs text-amber-500"></i>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-black <?php echo ($spearman_pct >= 95 ? 'text-emerald-600' : ($spearman_pct >= 85 ? 'text-blue-600' : 'text-amber-600')); ?> tabular-nums">
                        <?php echo $spearman_pct; ?>%
                    </h3>
                </div>
                <div class="mt-2 pt-2 border-t border-slate-100 text-[9px] font-bold text-slate-500 truncate">
                    <?php if($biggest_jump && abs($biggest_jump['jump']) >= 2): ?>
                        <span class="text-amber-600">Salto máx: <?php echo ($biggest_jump['jump'] > 0 ? '+' : '') . $biggest_jump['jump']; ?> puestos (<?php echo htmlspecialchars($biggest_jump['club']); ?>)</span>
                    <?php else: ?>
                        <span class="text-emerald-600">Orden de podio fiel</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- KPI 4: Desviación Máxima Individual (Outlier Alert) -->
            <div class="bg-slate-50 p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-slate-200 relative overflow-hidden card-hover flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest truncate">Máx. Desvío Individual</p>
                        <i class="fas fa-triangle-exclamation text-xs text-amber-500"></i>
                    </div>
                    <?php if($max_individual_deviation): 
                        $m_bias = (float)$max_individual_deviation['bias'];
                    ?>
                        <h3 class="text-xl sm:text-2xl font-black <?php echo (abs($m_bias) >= 0.5 ? 'text-rose-600' : 'text-slate-800'); ?> tabular-nums">
                            <?php echo ($m_bias > 0 ? '+' : '') . number_format($m_bias, 2); ?> <span class="text-xs font-normal text-slate-400">ptos</span>
                        </h3>
                    <?php else: ?>
                        <h3 class="text-xl sm:text-2xl font-black text-slate-400 tabular-nums">0.00</h3>
                    <?php endif; ?>
                </div>
                <div class="mt-2 pt-2 border-t border-slate-200/60 text-[9px] font-bold text-slate-600 truncate">
                    <?php if($max_individual_deviation): ?>
                        <span title="<?php echo htmlspecialchars($max_individual_deviation['swimmer'] . ' - ' . $max_individual_deviation['elemento']); ?>">
                            <?php echo htmlspecialchars($max_individual_deviation['swimmer']); ?> (<?php echo $max_individual_deviation['club']; ?>)
                        </span>
                    <?php else: ?>
                        <span>Sin registros atípicos</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- RECOMENDACIONES TÉCNICAS INTELIGENTES (SMART FEEDBACK) -->
        <div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-6 border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xs">
                        <i class="fas fa-lightbulb"></i>
                    </span>
                    <div>
                        <h3 class="text-sm font-black text-slate-800 uppercase tracking-tight">Recomendaciones de Calibración Técnica</h3>
                        <p class="text-[10px] text-slate-400 font-semibold">Diagnóstico pedagógico automático para autocontrol y clinic arbitral</p>
                    </div>
                </div>
                <span class="text-[9px] font-black uppercase text-blue-600 bg-blue-50 px-2.5 py-1 rounded-md border border-blue-100">Feedback Pedagógico</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <?php foreach($smart_insights as $si): ?>
                    <div class="p-3.5 rounded-2xl border <?php echo $si['bg']; ?> flex items-start gap-3">
                        <i class="fas <?php echo $si['icon']; ?> <?php echo $si['color']; ?> text-sm mt-0.5 shrink-0"></i>
                        <div class="min-w-0">
                            <h4 class="text-xs font-black text-slate-800 mb-0.5"><?php echo $si['title']; ?></h4>
                            <p class="text-[11px] text-slate-600 leading-relaxed"><?php echo $si['desc']; ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- DIAGNÓSTICO TÉCNICO POR FIGURA / ELEMENTO -->
        <?php if(!empty($figuras_audit)): ?>
        <div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-6 border border-slate-200 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
                        <i class="fas fa-person-swimming"></i>
                    </span>
                    <div>
                        <h3 class="text-sm font-black text-slate-800 uppercase tracking-tight">Diagnóstico por Figura y Elemento Técnico</h3>
                        <p class="text-[10px] text-slate-400 font-semibold">Alineación específica en cada ejercicio evaluado durante el evento</p>
                    </div>
                </div>
                <span class="text-[10px] font-black uppercase text-slate-400 tracking-wider tabular-nums"><?php echo count($figuras_audit); ?> ejercicios auditados</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                <?php foreach($figuras_audit as $fa): 
                    $f_prec = (float)$fa['precision_aqua'];
                    $f_bias = (float)$fa['bias_score'];
                    $f_badge_prec = ($f_prec >= 70) ? 'text-emerald-700 bg-emerald-50 border-emerald-200' : (($f_prec >= 50) ? 'text-blue-700 bg-blue-50 border-blue-200' : 'text-amber-700 bg-amber-50 border-amber-200');
                    $f_color_bias = ($f_bias > 0.1) ? 'text-emerald-600' : (($f_bias < -0.1) ? 'text-rose-600' : 'text-slate-600');
                ?>
                    <div class="p-3.5 bg-slate-50/80 hover:bg-slate-100/70 rounded-2xl border border-slate-100 transition-colors flex flex-col justify-between">
                        <div class="mb-2">
                            <span class="text-xs font-black text-slate-800 line-clamp-2 leading-tight"><?php echo htmlspecialchars($fa['nombre_entidad']); ?></span>
                            <span class="text-[9px] font-bold text-slate-400 tabular-nums uppercase tracking-wider"><?php echo $fa['total_notas']; ?> notas emitidas</span>
                        </div>
                        <div class="space-y-1.5 pt-2 border-t border-slate-200/60 text-[10px]">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-500">Precisión AQUA:</span>
                                <span class="px-2 py-0.5 rounded-md font-black border <?php echo $f_badge_prec; ?> tabular-nums"><?php echo number_format($f_prec, 1); ?>%</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-500">Sesgo Medio:</span>
                                <span class="font-black <?php echo $f_color_bias; ?> tabular-nums"><?php echo ($f_bias >= 0 ? '+' : '') . number_format($f_bias, 3); ?></span>
                            </div>
                            <div class="flex items-center justify-between text-slate-400">
                                <span class="font-semibold">Bajas / Altas:</span>
                                <span class="font-bold tabular-nums">
                                    <span class="text-rose-500"><?php echo $fa['bajas']; ?></span> / <span class="text-emerald-600"><?php echo $fa['altas']; ?></span>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Visualizaciones: Gráficos de Evolución y Uso de Escala (Histograma) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-8">
            <div class="bg-white p-4 sm:p-7 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200">
                <h3 class="text-sm sm:text-base font-black text-slate-800 mb-1 uppercase tracking-tight flex items-center gap-2">
                    <i class="fas fa-chart-line text-blue-600"></i> Evolución de Desviación
                </h3>
                <p class="text-[9px] sm:text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Tendencia nota a nota vs Consenso Oficial (0 = Sin Desviación)</p>
                <div class="relative h-64"><canvas id="evoChart"></canvas></div>
            </div>

            <div class="bg-white p-4 sm:p-7 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200 flex flex-col justify-between">
                <div>
                    <h3 class="text-sm sm:text-base font-black text-slate-800 mb-1 uppercase tracking-tight flex items-center gap-2">
                        <i class="fas fa-chart-column text-indigo-600"></i> Distribución del Uso de la Escala
                    </h3>
                    <p class="text-[9px] sm:text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Histograma de notas otorgadas (Detección de Centrismo)</p>
                </div>
                <div class="relative h-64"><canvas id="scaleChart"></canvas></div>
            </div>
        </div>

        <!-- ANÁLISIS DE FATIGA ARBITRAL Y DERIVA TEMPORAL (PANEL DRIFT) -->
        <?php if($fatigue_data): ?>
        <div class="bg-white rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200 p-5 sm:p-6 mb-6 sm:mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xs">
                        <i class="fas fa-stopwatch-20"></i>
                    </span>
                    <div>
                        <h3 class="text-sm font-black text-slate-800 uppercase tracking-tight">Análisis de Fatiga Arbitral y Deriva Temporal (Panel Drift)</h3>
                        <p class="text-[10px] text-slate-400 font-semibold">Comparativa de consistencia entre el tramo inicial (primer 25%) y el tramo final (último 25%) del evento</p>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-black uppercase border <?php echo $fatigue_data['color']; ?>">
                    <?php echo $fatigue_data['status']; ?>
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                <!-- Tramo Inicial -->
                <div class="p-4 bg-slate-50/80 rounded-2xl border border-slate-100 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-500">Tramo Inicial (Primeras <?php echo $fatigue_data['slice_size']; ?> notas)</span>
                        <i class="fas fa-play text-blue-500 text-[10px]"></i>
                    </div>
                    <div class="flex items-baseline justify-between">
                        <span class="text-xs text-slate-500 font-semibold">Precisión AQUA:</span>
                        <span class="text-base font-black text-slate-800 tabular-nums"><?php echo $fatigue_data['prec_first']; ?>%</span>
                    </div>
                    <div class="flex items-baseline justify-between">
                        <span class="text-xs text-slate-500 font-semibold">Sesgo Medio:</span>
                        <span class="text-xs font-black <?php echo ($fatigue_data['bias_first'] >= 0 ? 'text-emerald-600' : 'text-rose-600'); ?> tabular-nums">
                            <?php echo ($fatigue_data['bias_first'] >= 0 ? '+' : '') . number_format($fatigue_data['bias_first'], 3); ?>
                        </span>
                    </div>
                </div>

                <!-- Variación / Delta -->
                <div class="p-4 bg-slate-50/80 rounded-2xl border border-slate-100 text-center space-y-1.5">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Variación Temporal (Δ)</span>
                    <div class="flex items-center justify-center gap-4">
                        <div class="text-center">
                            <span class="text-[9px] text-slate-400 block font-bold">Delta Precisión</span>
                            <span class="text-sm font-black <?php echo ($fatigue_data['drift_prec'] >= 0 ? 'text-emerald-600' : 'text-rose-600'); ?> tabular-nums">
                                <?php echo ($fatigue_data['drift_prec'] >= 0 ? '+' : '') . $fatigue_data['drift_prec']; ?>%
                            </span>
                        </div>
                        <div class="w-px h-8 bg-slate-200"></div>
                        <div class="text-center">
                            <span class="text-[9px] text-slate-400 block font-bold">Deriva de Sesgo</span>
                            <span class="text-sm font-black <?php echo (abs($fatigue_data['drift_bias']) <= 0.15 ? 'text-emerald-600' : 'text-amber-600'); ?> tabular-nums">
                                <?php echo ($fatigue_data['drift_bias'] >= 0 ? '+' : '') . number_format($fatigue_data['drift_bias'], 3); ?>
                            </span>
                        </div>
                    </div>
                    <p class="text-[10px] text-slate-500 font-medium"><?php echo $fatigue_data['desc']; ?></p>
                </div>

                <!-- Tramo Final -->
                <div class="p-4 bg-slate-50/80 rounded-2xl border border-slate-100 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-500">Tramo Final (Últimas <?php echo $fatigue_data['slice_size']; ?> notas)</span>
                        <i class="fas fa-flag-checkered text-purple-500 text-[10px]"></i>
                    </div>
                    <div class="flex items-baseline justify-between">
                        <span class="text-xs text-slate-500 font-semibold">Precisión AQUA:</span>
                        <span class="text-base font-black text-slate-800 tabular-nums"><?php echo $fatigue_data['prec_last']; ?>%</span>
                    </div>
                    <div class="flex items-baseline justify-between">
                        <span class="text-xs text-slate-500 font-semibold">Sesgo Medio:</span>
                        <span class="text-xs font-black <?php echo ($fatigue_data['bias_last'] >= 0 ? 'text-emerald-600' : 'text-rose-600'); ?> tabular-nums">
                            <?php echo ($fatigue_data['bias_last'] >= 0 ? '+' : '') . number_format($fatigue_data['bias_last'], 3); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Desglose por Entidad (Club Bias) y Detección de Asimetría -->
        <div class="bg-white rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200 overflow-hidden flex flex-col mb-6 sm:mb-10">
            <div class="p-4 sm:p-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xs">
                        <i class="fas fa-building-flag"></i>
                    </span>
                    <div>
                        <h3 class="text-sm font-black text-slate-800 uppercase tracking-tight">Desglose por Entidad (Club Bias & Ratio de Asimetría)</h3>
                        <p class="text-[10px] text-slate-400 font-semibold">Tendencia de notas y proporción de anulaciones altas/bajas por club</p>
                    </div>
                </div>
                <span class="text-[10px] font-black uppercase text-slate-400 tracking-wider tabular-nums"><?php echo count($club_bias); ?> clubes</span>
            </div>
            <div class="overflow-x-auto max-h-[300px] overflow-y-auto no-scrollbar flex-1">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 sticky top-0 z-10 shadow-sm text-slate-400 text-[9px] font-black uppercase tracking-wider">
                            <th class="px-5 py-3">Club</th>
                            <th class="px-4 py-3 text-center">Notas</th>
                            <th class="px-4 py-3 text-center">B / A (Ratio)</th>
                            <th class="px-4 py-3 text-center">Sesgo Medio</th>
                            <th class="px-4 py-3 text-center">Diagnóstico Asimetría</th>
                            <th class="px-5 py-3 text-right">Tendencia</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php 
                        uasort($club_bias, function($a, $b) { return abs($b['avg']) <=> abs($a['avg']); });
                        foreach($club_bias as $c_name => $c_data): 
                            $c_avg = $c_data['avg']; 
                            $c_color = ($c_avg > 0.1) ? 'text-emerald-500' : (($c_avg < -0.1) ? 'text-rose-500' : 'text-slate-400'); 
                            $c_icon = ($c_avg > 0.1) ? 'fa-arrow-trend-up' : (($c_avg < -0.1) ? 'fa-arrow-trend-down' : 'fa-minus');
                        ?>
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-5 py-2.5 font-black text-slate-700 text-xs uppercase"><?php echo htmlspecialchars($c_name); ?></td>
                            <td class="px-4 py-2.5 text-center text-xs font-bold text-slate-500 tabular-nums"><?php echo $c_data['count']; ?></td>
                            <td class="px-4 py-2.5 text-center">
                                <span class="text-[10px] font-black tabular-nums">
                                    <span class="text-rose-500"><?php echo $c_data['bajas']; ?></span>
                                    <span class="text-slate-200 mx-0.5">/</span>
                                    <span class="text-emerald-500"><?php echo $c_data['altas']; ?></span>
                                    <?php if($c_data['count'] >= 4 && ($c_data['altas'] > 0 || $c_data['bajas'] > 0)): ?>
                                        <span class="text-[9px] text-slate-400 ml-1 font-bold">(<?php echo $c_data['ratio_ab']; ?>x)</span>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td class="px-4 py-2.5 text-center font-black <?php echo $c_color; ?> text-xs tabular-nums">
                                <?php echo ($c_avg >= 0 ? '+' : '') . number_format($c_avg, 3); ?>
                            </td>
                            <td class="px-4 py-2.5 text-center">
                                <span class="px-2.5 py-0.5 rounded-md text-[9px] font-black uppercase border <?php echo $c_data['asimetria_badge']; ?>">
                                    <?php echo $c_data['asimetria']; ?>
                                </span>
                            </td>
                            <td class="px-5 py-2.5 text-right">
                                <i class="fas <?php echo $c_icon; ?> <?php echo $c_color; ?> text-xs"></i>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- COMPARADOR CARA A CARA (HEAD-TO-HEAD) -->
        <div class="bg-white rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200 p-5 sm:p-6 no-print">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xs">
                        <i class="fas fa-arrows-left-right-to-line"></i>
                    </span>
                    <div>
                        <h3 class="text-sm font-black text-slate-800 uppercase tracking-tight">Comparativa Cara a Cara (Head-to-Head)</h3>
                        <p class="text-[10px] text-slate-400 font-semibold">Cruce directo nota a nota contra otro juez en los mismos paneles del evento</p>
                    </div>
                </div>

                <!-- Selector de Juez a Enfrentar (Zero-Gray Control) -->
                <form action="analisis_juez_detalle.php" method="GET" class="flex items-center gap-2">
                    <input type="hidden" name="pjs" value="<?php echo htmlspecialchars($pjs_str); ?>">
                    <input type="hidden" name="comp_id" value="<?php echo $competicion_id; ?>">
                    <div class="relative min-w-[220px]">
                        <select name="vs_pjs" onchange="this.form.submit()" class="w-full bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-800 font-bold rounded-xl pl-3 pr-8 py-1.5 text-xs focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 outline-none transition-all appearance-none cursor-pointer">
                            <option value="">-- Seleccionar Juez Rival --</option>
                            <?php foreach($other_judges as $oj): 
                                $is_sel = ($vs_pjs === $oj['pjs']) ? 'selected' : '';
                            ?>
                                <option value="<?php echo htmlspecialchars($oj['pjs']); ?>" <?php echo $is_sel; ?>>
                                    vs <?php echo htmlspecialchars($oj['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-slate-400">
                            <i class="fas fa-chevron-down text-[9px]"></i>
                        </div>
                    </div>
                    <?php if(!empty($vs_pjs)): ?>
                        <a href="analisis_juez_detalle.php?pjs=<?php echo urlencode($pjs_str); ?>&comp_id=<?php echo $competicion_id; ?>" class="px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition-colors" title="Cerrar comparativa">
                            <i class="fas fa-times"></i>
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <?php if($h2h_data): ?>
                <div class="space-y-4">
                    <!-- Resumen Métricas Cara a Cara -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="p-3 bg-purple-50/50 rounded-xl border border-purple-100 text-center">
                            <p class="text-[8px] font-black uppercase tracking-wider text-purple-600 mb-0.5">Notas Coincidentes</p>
                            <p class="text-base font-black text-slate-800 tabular-nums"><?php echo $h2h_data['total']; ?></p>
                        </div>
                        <div class="p-3 bg-purple-50/50 rounded-xl border border-purple-100 text-center">
                            <p class="text-[8px] font-black uppercase tracking-wider text-purple-600 mb-0.5">Diferencia Media (A - B)</p>
                            <p class="text-base font-black <?php echo ($h2h_data['avg_diff'] >= 0 ? 'text-emerald-600' : 'text-rose-600'); ?> tabular-nums">
                                <?php echo ($h2h_data['avg_diff'] >= 0 ? '+' : '') . number_format($h2h_data['avg_diff'], 3); ?>
                            </p>
                        </div>
                        <div class="p-3 bg-purple-50/50 rounded-xl border border-purple-100 text-center">
                            <p class="text-[8px] font-black uppercase tracking-wider text-purple-600 mb-0.5">Concordancia AQUA (≤ 0.2)</p>
                            <p class="text-base font-black text-slate-800 tabular-nums"><?php echo $h2h_data['concord_2_pct']; ?>%</p>
                        </div>
                        <div class="p-3 bg-purple-50/50 rounded-xl border border-purple-100 text-center">
                            <p class="text-[8px] font-black uppercase tracking-wider text-purple-600 mb-0.5">Concordancia Amplia (≤ 0.5)</p>
                            <p class="text-base font-black text-slate-800 tabular-nums"><?php echo $h2h_data['concord_5_pct']; ?>%</p>
                        </div>
                    </div>

                    <!-- Tabla de Discrepancias Máximas Cara a Cara -->
                    <div class="overflow-x-auto max-h-[220px] overflow-y-auto no-scrollbar border border-slate-100 rounded-xl">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-50 sticky top-0 z-10 text-[9px] font-black uppercase text-slate-400 tracking-wider">
                                    <th class="px-4 py-2">Deportista</th>
                                    <th class="px-4 py-2">Ejercicio</th>
                                    <th class="px-3 py-2 text-center"><?php echo htmlspecialchars($nombre_completo); ?></th>
                                    <th class="px-3 py-2 text-center"><?php echo htmlspecialchars($h2h_data['vs_nombre']); ?></th>
                                    <th class="px-3 py-2 text-center">Diferencia</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach(array_slice($h2h_data['items'], 0, 8) as $hi): 
                                    $h_diff = (float)$hi['diff'];
                                    $h_diff_c = (abs($h_diff) >= 0.5) ? 'text-rose-600' : ((abs($h_diff) > 0.2) ? 'text-amber-600' : 'text-slate-600');
                                ?>
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-4 py-2 font-bold text-slate-800">
                                        <?php echo htmlspecialchars($hi['apellidos'] . ', ' . $hi['nombre']); ?>
                                        <span class="text-[9px] text-slate-400 font-semibold">(<?php echo $hi['club_nombre']; ?>)</span>
                                    </td>
                                    <td class="px-4 py-2 text-slate-500 text-[11px]"><?php echo htmlspecialchars($hi['elemento_nombre']); ?></td>
                                    <td class="px-3 py-2 text-center font-black tabular-nums text-slate-800"><?php echo number_format($hi['nota_a'], 2); ?></td>
                                    <td class="px-3 py-2 text-center font-black tabular-nums text-slate-800"><?php echo number_format($hi['nota_b'], 2); ?></td>
                                    <td class="px-3 py-2 text-center font-black tabular-nums <?php echo $h_diff_c; ?>">
                                        <?php echo ($h_diff >= 0 ? '+' : '') . number_format($h_diff, 2); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php elseif(!empty($vs_pjs)): ?>
                <div class="p-6 text-center bg-slate-50 rounded-xl border border-dashed border-slate-200">
                    <p class="text-xs text-slate-500 font-bold">No se encontraron actuaciones compartidas en los mismos paneles entre estos dos jueces en este evento.</p>
                </div>
            <?php else: ?>
                <p class="text-[11px] text-slate-400 italic">Selecciona un juez del listado superior para comparar calificaciones nota a nota en las pruebas que ambos juzgaron.</p>
            <?php endif; ?>
        </div>

        <!-- FILTROS INTERACTIVOS PARA EL DESGLOSE DE ACTUACIONES -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-slate-200 shadow-sm flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 no-print">
            <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap">
                <span class="text-[10px] font-black uppercase text-slate-400 tracking-wider mr-1">Filtrar:</span>
                <button onclick="filterTableNotes('all')" id="btnFilter_all" class="filter-chip px-3 py-1 rounded-xl text-xs font-bold bg-slate-900 text-white transition-all shadow-sm">Todos</button>
                <button onclick="filterTableNotes('aqua')" id="btnFilter_aqua" class="filter-chip px-3 py-1 rounded-xl text-xs font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition-all">Margen AQUA (≤ 0.2)</button>
                <button onclick="filterTableNotes('relevant')" id="btnFilter_relevant" class="filter-chip px-3 py-1 rounded-xl text-xs font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition-all">Desv. Relevante (≥ 0.5)</button>
                <button onclick="filterTableNotes('baja')" id="btnFilter_baja" class="filter-chip px-3 py-1 rounded-xl text-xs font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition-all">Bajas Anuladas</button>
                <button onclick="filterTableNotes('alta')" id="btnFilter_alta" class="filter-chip px-3 py-1 rounded-xl text-xs font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition-all">Altas Anuladas</button>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button onclick="toggleAllGroups(true)" class="px-2.5 py-1 text-[11px] font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">Expandir Todo</button>
                <button onclick="toggleAllGroups(false)" class="px-2.5 py-1 text-[11px] font-bold text-slate-500 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">Colapsar</button>
            </div>
        </div>

        <!-- Árbol de Puntuaciones por Categoría y Deportista -->
        <div class="space-y-8 sm:space-y-12">
            <?php if(count($tree) > 0): 
                $group_counter = 0; 
                foreach($tree as $cat_nombre => $cat_data): 
                    $cat_avg_d = ($cat_data['d_count'] > 0) ? round($cat_data['d_sum'] / $cat_data['d_count'], 3) : 0; 
                    $cat_color_d = ($cat_avg_d > 0.5) ? 'text-red-500' : (($cat_avg_d > 0.25) ? 'text-amber-500' : 'text-emerald-600');
            ?>
                <section class="space-y-4 sm:space-y-6">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b-2 border-slate-200 pb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-slate-900 text-white flex items-center justify-center text-sm shadow-sm">
                                <i class="fas fa-layer-group"></i>
                            </div>
                            <h2 class="text-base sm:text-xl font-black text-slate-800 uppercase tracking-tight"><?php echo $cat_nombre; ?></h2>
                        </div>
                        <div class="flex items-center gap-4 bg-white px-4 py-2 rounded-xl shadow-sm border border-slate-200 text-xs">
                            <div class="text-center">
                                <span class="text-[8px] font-black text-slate-400 uppercase tracking-wider block">Notas</span>
                                <span class="font-black text-slate-800 tabular-nums"><?php echo $cat_data['total_n']; ?></span>
                            </div>
                            <div class="w-px h-6 bg-slate-200"></div>
                            <div class="text-center">
                                <span class="text-[8px] font-black text-slate-400 uppercase tracking-wider block">Desv. Cat.</span>
                                <span class="font-black <?php echo $cat_color_d; ?> tabular-nums">±<?php echo number_format($cat_avg_d, 3); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <?php foreach($cat_data['swimmers'] as $s_name => $s_data): 
                            $group_counter++; 
                            $s_avg_n = round($s_data['sum_n'] / max(1, $s_data['total_n']), 2); 
                            $s_avg_d = ($s_data['d_count'] > 0) ? round($s_data['d_sum'] / $s_data['d_count'], 3) : 0; 
                            $s_color_d = ($s_avg_d > 0.5) ? 'text-red-500' : (($s_avg_d > 0.25) ? 'text-amber-500' : 'text-emerald-600');
                        ?>
                            <div class="bg-white rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200 overflow-hidden transition-all hover:shadow-md">
                                <div class="p-3.5 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-6 cursor-pointer select-none" onclick="toggleGroup('node_<?php echo $group_counter; ?>')">
                                    <div class="flex items-center gap-3 min-w-0 flex-1">
                                        <div class="w-8 h-8 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 shrink-0">
                                            <i class="fas fa-person-swimming text-xs"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <span class="font-black text-slate-800 text-xs sm:text-sm uppercase tracking-tight block truncate"><?php echo $s_data['nombre']; ?></span>
                                            <span class="text-[9px] font-bold text-blue-600 uppercase tracking-wider"><?php echo $s_data['club']; ?></span>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between w-full sm:w-auto gap-4 sm:gap-8 text-center shrink-0">
                                        <div>
                                            <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Media</p>
                                            <p class="font-black text-slate-800 text-xs tabular-nums"><?php echo number_format($s_avg_n, 2); ?></p>
                                        </div>
                                        <div>
                                            <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Desv. AQUA</p>
                                            <p class="font-black text-xs <?php echo $s_color_d; ?> tabular-nums">±<?php echo number_format($s_avg_d, 3); ?></p>
                                        </div>
                                        <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400">
                                            <i class="fas fa-chevron-down text-xs transition-transform duration-300" id="icon_node_<?php echo $group_counter; ?>"></i>
                                        </div>
                                    </div>
                                </div>

                                <div id="node_<?php echo $group_counter; ?>" class="hidden border-t border-slate-100 bg-slate-50/50 p-2.5 sm:p-5">
                                    <div class="overflow-x-auto no-scrollbar">
                                        <table class="w-full text-left border-collapse bg-white rounded-xl sm:rounded-2xl overflow-hidden shadow-sm border border-slate-200">
                                            <thead>
                                                <tr class="bg-slate-900 text-white text-[9px] font-black uppercase tracking-wider">
                                                    <th class="px-4 py-3">Fase / Elemento</th>
                                                    <th class="px-4 py-3 text-center">Nota Juez</th>
                                                    <th class="px-4 py-3 text-center">Consenso AQUA</th>
                                                    <th class="px-4 py-3 text-center">Bias Score</th>
                                                    <th class="px-4 py-3 text-center">Estado</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100 text-xs">
                                                <?php foreach($s_data['items'] as $item): 
                                                    $bs = $item['bias_score'] ?? 0; 
                                                    $abs_bs = abs($bs);
                                                    $c_bs = ($abs_bs > 0.5) ? 'text-red-500' : (($abs_bs > 0.25) ? 'text-amber-500' : 'text-emerald-600'); 
                                                    $n_val = (float)$item['nota']; 
                                                    $m_val = (float)$item['min_fila']; 
                                                    $x_val = (float)$item['max_fila'];
                                                    $item_status = ($n_val <= $m_val && $m_val > 0) ? 'baja' : (($n_val >= $x_val && $x_val < 10) ? 'alta' : 'valida');
                                                ?>
                                                <tr class="item-row hover:bg-slate-50 transition-colors" data-bias="<?php echo $abs_bs; ?>" data-status="<?php echo $item_status; ?>">
                                                    <td class="px-4 py-3">
                                                        <div class="flex flex-col">
                                                            <span class="text-[9px] font-black text-slate-400 uppercase"><?php echo $item['fase_nombre']; ?></span>
                                                            <span class="font-bold text-slate-700 text-xs"><?php echo $item['elemento_nombre']; ?></span>
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-3 text-center font-black text-xs <?php echo ($item_status != 'valida') ? 'text-slate-400' : 'text-slate-800'; ?> tabular-nums">
                                                        <?php echo number_format($n_val, 2); ?>
                                                    </td>
                                                    <td class="px-4 py-3 text-center font-bold text-slate-400 text-xs tabular-nums">
                                                        <?php echo ($item['media_consenso'] !== null) ? number_format($item['media_consenso'], 2) : '-'; ?>
                                                    </td>
                                                    <td class="px-4 py-3 text-center font-black text-xs <?php echo $c_bs; ?> tabular-nums">
                                                        <?php echo ($item['media_consenso'] !== null) ? ($bs >= 0 ? '+' : '') . number_format($bs, 2) : '-'; ?>
                                                    </td>
                                                    <td class="px-4 py-3 text-center">
                                                        <?php if($item_status == 'baja'): ?>
                                                            <span class="px-2 py-0.5 bg-rose-100 text-rose-700 text-[8px] font-black rounded uppercase italic border border-rose-200">Baja</span>
                                                        <?php elseif($item_status == 'alta'): ?>
                                                            <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[8px] font-black rounded uppercase italic border border-emerald-200">Alta</span>
                                                        <?php else: ?>
                                                            <span class="px-2 py-0.5 bg-blue-50 text-blue-600 text-[8px] font-black rounded uppercase italic border border-blue-100">Válida</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
            <?php else: ?>
                <div class="p-12 text-center bg-white rounded-2xl sm:rounded-3xl border border-dashed border-slate-200">
                    <i class="fas fa-search text-slate-300 text-4xl mb-3"></i>
                    <p class="text-slate-400 font-bold italic text-xs uppercase tracking-widest">No se han encontrado puntuaciones registradas para este juez.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function toggleGroup(id) { 
    const el = document.getElementById(id); 
    const icon = document.getElementById('icon_' + id); 
    if (el) {
        if (el.classList.contains('hidden')) { 
            el.classList.remove('hidden'); 
            if (icon) icon.style.transform = 'rotate(180deg)'; 
        } else { 
            el.classList.add('hidden'); 
            if (icon) icon.style.transform = 'rotate(0deg)'; 
        } 
    }
}

function toggleAllGroups(expand) {
    document.querySelectorAll('.swimmer-card .border-t').forEach(el => {
        if (expand) el.classList.remove('hidden');
        else el.classList.add('hidden');
    });
    document.querySelectorAll('.fa-chevron-down').forEach(icon => {
        icon.style.transform = expand ? 'rotate(180deg)' : 'rotate(0deg)';
    });
}

function filterTableNotes(type) {
    document.querySelectorAll('.filter-chip').forEach(btn => {
        btn.classList.remove('bg-slate-900', 'text-white', 'shadow-sm');
        btn.classList.add('bg-slate-100', 'text-slate-700');
    });
    const activeBtn = document.getElementById('btnFilter_' + type);
    if (activeBtn) {
        activeBtn.classList.add('bg-slate-900', 'text-white', 'shadow-sm');
        activeBtn.classList.remove('bg-slate-100', 'text-slate-700');
    }

    const rows = document.querySelectorAll('.item-row');
    rows.forEach(row => {
        const bias = parseFloat(row.getAttribute('data-bias') || 0);
        const status = row.getAttribute('data-status');
        let show = false;

        if (type === 'all') show = true;
        else if (type === 'aqua') show = (bias <= 0.2);
        else if (type === 'relevant') show = (bias >= 0.5);
        else if (type === 'baja') show = (status === 'baja');
        else if (type === 'alta') show = (status === 'alta');

        row.style.display = show ? '' : 'none';
    });

    if (type !== 'all') {
        toggleAllGroups(true);
    }
}

document.addEventListener("DOMContentLoaded", function() {
    <?php 
    $evo_labels = []; $evo_data = []; $evo_colors = []; 
    foreach ($tree as $cat_nombre => $cat_data) { 
        foreach ($cat_data['swimmers'] as $sw_name => $sw_data) { 
            foreach ($sw_data['items'] as $n) { 
                $d = $n['bias_score'] ?? 0; 
                $evo_labels[] = $n['nadadora_nombre'] . " (" . $n['elemento_nombre'] . ")"; 
                $evo_data[] = round($d, 3); 
                $evo_colors[] = ($d > 0.5) ? '#ef4444' : (($d < -0.5) ? '#ef4444' : '#3b82f6'); 
            } 
        } 
    } 
    ?>
    const labels = <?php echo json_encode($evo_labels); ?>; 
    const data = <?php echo json_encode($evo_data); ?>; 
    const colors = <?php echo json_encode($evo_colors); ?>; 
    
    // 1. Gráfico de Evolución de Desviación
    const ctx = document.getElementById('evoChart');
    if (ctx && data.length > 0) { 
        new Chart(ctx, { 
            type: 'line', 
            data: { 
                labels: labels, 
                datasets: [{ 
                    label: 'Bias Score', 
                    data: data, 
                    borderColor: '#3b82f6', 
                    borderWidth: 2, 
                    pointBackgroundColor: colors, 
                    pointRadius: 4, 
                    pointHoverRadius: 6, 
                    fill: false, 
                    tension: 0.1 
                }] 
            }, 
            options: { 
                responsive: true, 
                maintainAspectRatio: false, 
                plugins: { 
                    tooltip: { 
                        callbacks: { 
                            label: function(ctx) { return 'Bias: ' + (ctx.raw > 0 ? '+' : '') + ctx.raw; } 
                        } 
                    }, 
                    legend: { display: false } 
                }, 
                scales: { 
                    x: { display: false }, 
                    y: { 
                        title: { display: true, text: 'Bias Score (± Puntos)' }, 
                        grid: { 
                            color: function(ctx) { return (ctx.tick.value === 0) ? '#0f172a' : '#f1f5f9'; }, 
                            lineWidth: function(ctx) { return (ctx.tick.value === 0) ? 2 : 1; } 
                        } 
                    } 
                } 
            } 
        }); 
    }

    // 2. Gráfico de Distribución del Uso de la Escala (Histograma)
    const ctxScale = document.getElementById('scaleChart');
    if (ctxScale) {
        new Chart(ctxScale, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($scale_bins)); ?>,
                datasets: [{
                    label: 'Notas Otorgadas',
                    data: <?php echo json_encode(array_values($scale_bins)); ?>,
                    backgroundColor: ['#f43f5e', '#fb923c', '#38bdf8', '#3b82f6', '#6366f1', '#10b981'],
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) { return 'Notas en este rango: ' + ctx.raw; }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                }
            }
        });
    }
});
</script>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
