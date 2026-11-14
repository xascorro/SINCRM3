<?php
include('security.php');

$action = $_GET['action'] ?? '';
$id_competicion = intval($_GET['id_competicion'] ?? 0);

if (!$id_competicion) {
    echo json_encode(['status' => 'error', 'message' => 'Falta ID de competición']);
    exit();
}

if ($action == 'save') {
    // 1. Limpiar datos previos si existen
    $q_comp_name = mysqli_query($connection, "SELECT nombre FROM competiciones WHERE id = $id_competicion");
    $comp_name = mysqli_fetch_assoc($q_comp_name)['nombre'] ?? "ID $id_competicion";

    mysqli_query($connection, "DELETE FROM auditoria_jueces_stats WHERE id_competicion = $id_competicion");
    mysqli_query($connection, "DELETE FROM auditoria_jueces_puntos WHERE id_competicion = $id_competicion");

    // 2. Mapeo de consenso y rangos para Figuras
    $fig_map = [];
    $q_fig = "SELECT id_inscripcion_figuras, COALESCE(id_elemento, 0) as el,
                     MIN(nota) as min_nota, MAX(nota) as max_nota,
                     ROUND(AVG(CASE WHEN (nota_menor IS NULL OR nota_menor = '' OR nota_menor = '0' OR nota_menor = 'no') 
                                    AND (nota_mayor IS NULL OR nota_mayor = '' OR nota_mayor = '0' OR nota_mayor = 'no') 
                               THEN nota ELSE NULL END), 2) as media_consenso
              FROM puntuaciones_jueces
              WHERE id_inscripcion_figuras IN (SELECT id FROM inscripciones_figuras WHERE id_competicion = $id_competicion)
              GROUP BY id_inscripcion_figuras, COALESCE(id_elemento, 0)";
    $res_fig = mysqli_query($connection, $q_fig);
    if ($res_fig) {
        while ($rf = mysqli_fetch_assoc($res_fig)) {
            $fig_map[$rf['id_inscripcion_figuras'] . '_' . $rf['el']] = $rf;
        }
    }

    // 3. Mapeo de consenso y rangos para Rutinas
    $rut_map = [];
    $q_rut = "SELECT id_rutina, COALESCE(id_elemento, 0) as el, COALESCE(tipo_ia, '') as tipo_ia,
                     MIN(nota) as min_nota, MAX(nota) as max_nota,
                     ROUND(AVG(CASE WHEN (nota_menor IS NULL OR nota_menor = '' OR nota_menor = '0' OR nota_menor = 'no') 
                                    AND (nota_mayor IS NULL OR nota_mayor = '' OR nota_mayor = '0' OR nota_mayor = 'no') 
                               THEN nota ELSE NULL END), 2) as media_consenso
              FROM puntuaciones_jueces
              WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion = $id_competicion)
              GROUP BY id_rutina, COALESCE(id_elemento, 0), COALESCE(tipo_ia, '')";
    $res_rut = mysqli_query($connection, $q_rut);
    if ($res_rut) {
        while ($rr = mysqli_fetch_assoc($res_rut)) {
            $rut_map[$rr['id_rutina'] . '_' . $rr['el'] . '_' . $rr['tipo_ia']] = $rr;
        }
    }

    // 4. Obtener mapeo de jueces/paneles
    $q_map = "
        SELECT DISTINCT pj.id_panel_juez, p.id_juez, j.nombre, j.apellidos
        FROM puntuaciones_jueces pj
        LEFT JOIN inscripciones_figuras i ON pj.id_inscripcion_figuras = i.id
        LEFT JOIN rutinas r ON pj.id_rutina = r.id
        LEFT JOIN panel_jueces p ON pj.id_panel_juez = p.id
        LEFT JOIN jueces j ON p.id_juez = j.id
        WHERE (i.id_competicion = $id_competicion OR r.id_competicion = $id_competicion) AND (p.id_juez != 108 OR p.id_juez IS NULL)
    ";
    $res_map = mysqli_query($connection, $q_map);
    
    $jueces_data = []; 
    while ($row = mysqli_fetch_assoc($res_map)) {
        $id_pj = (int)$row['id_panel_juez'];
        $id_juez = (int)($row['id_juez'] ?? 0);
        $group_key = ($id_juez > 0) ? "J_$id_juez" : "P_$id_pj";
        
        if (!isset($jueces_data[$group_key])) {
            $nombre_display = ($id_juez > 0) ? ($row['nombre'] . ' ' . $row['apellidos']) : "Juez Desconocido (ID $id_pj)";
            if ($id_juez == 108) $nombre_display = "Juez MEDIA (Automático)";
            
            $jueces_data[$group_key] = [
                'nombre' => $nombre_display,
                'id_juez' => $id_juez,
                'pjs' => [],
                'club_bias' => []
            ];
        }
        $jueces_data[$group_key]['pjs'][] = $id_pj;
    }

    // 5. Procesar cada juez/grupo
    foreach ($jueces_data as $group_key => $data) {
        $pjs_str = implode(',', $data['pjs']);
        $stats_global = ['total' => 0, 'bajas' => 0, 'altas' => 0, 'desv_sum' => 0, 'desv_count' => 0, 'prec_count' => 0];
        $club_bias = [];
        $figura_stats = [];

        $q_stats = "
            SELECT 
                pj.id as id_puntuacion,
                pj.id_panel_juez,
                pj.id_inscripcion_figuras,
                pj.id_rutina,
                pj.id_elemento,
                pj.tipo_ia,
                pj.nota,
                COALESCE(cl_fig.nombre_corto, cl_rut.nombre_corto, 'S/C') as club_nombre,
                COALESCE(cl_fig.id, cl_rut.id, 0) as club_id,
                COALESCE(fig.id, 0) as figura_id,
                COALESCE(
                    CONVERT((SELECT texto FROM hibridos_rutina hr WHERE hr.id_rutina = pj.id_rutina AND hr.elemento = pj.id_elemento AND hr.tipo = 'dd' AND hr.texto != '' LIMIT 1) USING utf8mb4),
                    CONVERT((SELECT texto FROM hibridos_rutina hr WHERE hr.id_rutina = pj.id_rutina AND hr.elemento = pj.id_elemento AND hr.tipo = 'basemark' AND hr.texto != '' LIMIT 1) USING utf8mb4),
                    CONVERT(CONCAT(fig.numero, ' ', fig.nombre) USING utf8mb4),
                    CONVERT(pj.tipo_ia USING utf8mb4),
                    CONVERT(CASE WHEN pj.id_elemento > 0 THEN CONCAT('Elemento ', pj.id_elemento) ELSE 'Rutina Técnica' END USING utf8mb4)
                ) as figura_nombre
            FROM puntuaciones_jueces pj
            LEFT JOIN inscripciones_figuras i ON pj.id_inscripcion_figuras = i.id
            LEFT JOIN nadadoras n_fig ON i.id_nadadora = n_fig.id
            LEFT JOIN clubes cl_fig ON n_fig.club = cl_fig.id
            LEFT JOIN fases f_fig ON i.id_fase = f_fig.id
            LEFT JOIN figuras fig ON (CASE WHEN pj.id_elemento > 0 AND f_fig.elementos_coach_card = 0 THEN pj.id_elemento ELSE f_fig.id_figura END) = fig.id
            LEFT JOIN rutinas r ON pj.id_rutina = r.id
            LEFT JOIN clubes cl_rut ON r.id_club = cl_rut.id
            WHERE pj.id_panel_juez IN ($pjs_str)
        ";
        
        $res_stats = mysqli_query($connection, $q_stats);
        while($s = mysqli_fetch_assoc($res_stats)) {
            $stats_global['total']++;
            $nota = (float)$s['nota'];
            $el = (int)($s['id_elemento'] ?? 0);
            $tipo_ia = $s['tipo_ia'] ?? '';
            $club_id = (int)$s['club_id'];
            $club_name = $s['club_nombre'];
            $fig_id = (int)$s['figura_id'];
            $fig_nombre = $s['figura_nombre'] ?: 'Elemento';

            $min = 0; $max = 10; $consenso = null;
            if (!empty($s['id_inscripcion_figuras'])) {
                $k = $s['id_inscripcion_figuras'] . '_' . $el;
                if (isset($fig_map[$k])) {
                    $min = (float)$fig_map[$k]['min_nota'];
                    $max = (float)$fig_map[$k]['max_nota'];
                    $consenso = ($fig_map[$k]['media_consenso'] !== null) ? (float)$fig_map[$k]['media_consenso'] : null;
                }
            } elseif (!empty($s['id_rutina'])) {
                $k = $s['id_rutina'] . '_' . $el . '_' . $tipo_ia;
                if (isset($rut_map[$k])) {
                    $min = (float)$rut_map[$k]['min_nota'];
                    $max = (float)$rut_map[$k]['max_nota'];
                    $consenso = ($rut_map[$k]['media_consenso'] !== null) ? (float)$rut_map[$k]['media_consenso'] : null;
                }
            }

            $estado = 0; // Válida
            if ($min < $max) {
                if ($nota <= $min && $min > 0) { $stats_global['bajas']++; $estado = 1; }
                elseif ($nota >= $max && $max < 10) { $stats_global['altas']++; $estado = 2; }
            }

            $bias = 0;
            if ($consenso !== null) {
                $bias = $nota - $consenso;
                $stats_global['desv_sum'] += abs($bias);
                $stats_global['desv_count']++;
                if (abs($bias) <= 0.2) $stats_global['prec_count']++;

                // Bias por Club
                if (!isset($club_bias[$club_id])) $club_bias[$club_id] = ['nombre' => $club_name, 'sum' => 0, 'count' => 0, 'bajas' => 0, 'altas' => 0, 'total' => 0];
                $club_bias[$club_id]['sum'] += $bias;
                $club_bias[$club_id]['count']++;
                $club_bias[$club_id]['total']++;
                if ($estado == 1) $club_bias[$club_id]['bajas']++;
                elseif ($estado == 2) $club_bias[$club_id]['altas']++;

                // Stats por Figura / Elemento Técnico
                if (!isset($figura_stats[$fig_nombre])) {
                    $figura_stats[$fig_nombre] = [
                        'id' => $fig_id,
                        'nombre' => $fig_nombre,
                        'sum' => 0,
                        'count' => 0,
                        'bajas' => 0,
                        'altas' => 0,
                        'total' => 0,
                        'prec_count' => 0
                    ];
                }
                $figura_stats[$fig_nombre]['sum'] += $bias;
                $figura_stats[$fig_nombre]['count']++;
                $figura_stats[$fig_nombre]['total']++;
                if (abs($bias) <= 0.2) $figura_stats[$fig_nombre]['prec_count']++;
                if ($estado == 1) $figura_stats[$fig_nombre]['bajas']++;
                elseif ($estado == 2) $figura_stats[$fig_nombre]['altas']++;

                // Guardar Punto Individual
                $id_p = (int)$s['id_puntuacion'];
                mysqli_query($connection, "INSERT INTO auditoria_jueces_puntos (id_competicion, id_puntuacion, id_panel_juez, valor_consenso, bias_score, estado) 
                                           VALUES ($id_competicion, $id_p, {$s['id_panel_juez']}, $consenso, $bias, $estado)");
            }
        }

        // Guardar Stat GLOBAL del Juez
        $prec_pct = ($stats_global['desv_count'] > 0) ? round(($stats_global['prec_count'] / $stats_global['desv_count']) * 100, 2) : 0;
        
        $q_sign = "SELECT AVG(bias_score) as avg_sign FROM auditoria_jueces_puntos WHERE id_competicion = $id_competicion AND id_panel_juez IN ($pjs_str)";
        $res_sign = mysqli_query($connection, $q_sign);
        $bias_sign = (float)(mysqli_fetch_assoc($res_sign)['avg_sign'] ?? 0);

        $pjs_json = mysqli_real_escape_string($connection, $pjs_str);
        $nombre_esc = mysqli_real_escape_string($connection, $data['nombre']);

        mysqli_query($connection, "INSERT INTO auditoria_jueces_stats 
            (id_competicion, group_key, id_juez, entidad_tipo, entidad_id, nombre_entidad, total_notas, bajas, altas, precision_aqua, bias_score, pjs_asociados) 
            VALUES ($id_competicion, '$group_key', {$data['id_juez']}, 'GLOBAL', 0, '$nombre_esc', {$stats_global['total']}, {$stats_global['bajas']}, {$stats_global['altas']}, $prec_pct, $bias_sign, '$pjs_json')");

        // Guardar Stats por CLUB para este juez
        foreach ($club_bias as $club_key_id => $cb) {
            $c_bias_avg = ($cb['count'] > 0) ? ($cb['sum'] / $cb['count']) : 0;
            $cb_name_esc = mysqli_real_escape_string($connection, $cb['nombre']);
            mysqli_query($connection, "INSERT INTO auditoria_jueces_stats 
                (id_competicion, group_key, id_juez, entidad_tipo, entidad_id, nombre_entidad, total_notas, bajas, altas, precision_aqua, bias_score, pjs_asociados) 
                VALUES ($id_competicion, '$group_key', {$data['id_juez']}, 'CLUB', $club_key_id, '$cb_name_esc', {$cb['total']}, {$cb['bajas']}, {$cb['altas']}, 0, $c_bias_avg, '$pjs_json')");
        }

        // Guardar Stats por FIGURA / ELEMENTO para este juez
        foreach ($figura_stats as $fname => $fs) {
            $f_prec = ($fs['count'] > 0) ? round(($fs['prec_count'] / $fs['count']) * 100, 2) : 0;
            $f_bias_avg = ($fs['count'] > 0) ? round($fs['sum'] / $fs['count'], 3) : 0;
            $fs_name_esc = mysqli_real_escape_string($connection, $fs['nombre']);
            mysqli_query($connection, "INSERT INTO auditoria_jueces_stats 
                (id_competicion, group_key, id_juez, entidad_tipo, entidad_id, nombre_entidad, total_notas, bajas, altas, precision_aqua, bias_score, pjs_asociados) 
                VALUES ($id_competicion, '$group_key', {$data['id_juez']}, 'FIGURA', {$fs['id']}, '$fs_name_esc', {$fs['total']}, {$fs['bajas']}, {$fs['altas']}, $f_prec, $f_bias_avg, '$pjs_json')");
        }
    }

    write_log("Auditoría BIAS cerrada/recalculada para: $comp_name", "SUCCESS");
    echo json_encode(['status' => 'success', 'message' => 'Auditoría guardada correctamente']);
}
?>