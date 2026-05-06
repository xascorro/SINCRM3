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

    // 2. Obtener mapeo de jueces/paneles
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
        $id_pj = $row['id_panel_juez'];
        $id_juez = $row['id_juez'] ?? 0;
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

    // 3. Procesar cada juez/grupo
    foreach ($jueces_data as $group_key => $data) {
        $pjs_str = implode(',', $data['pjs']);
        $stats_global = ['total' => 0, 'bajas' => 0, 'altas' => 0, 'desv_sum' => 0, 'desv_count' => 0, 'prec_count' => 0];
        $club_bias = [];

        $q_stats = "
            SELECT 
                pj.id as id_puntuacion,
                pj.id_panel_juez,
                pj.nota,
                COALESCE(cl_fig.nombre_corto, cl_rut.nombre_corto, 'S/C') as club_nombre,
                COALESCE(cl_fig.id, cl_rut.id, 0) as club_id,
                (SELECT MIN(pj2.nota) FROM puntuaciones_jueces pj2 WHERE pj2.id_inscripcion_figuras = pj.id_inscripcion_figuras AND pj2.id_elemento = pj.id_elemento AND pj.id_inscripcion_figuras > 0) as min_fig,
                (SELECT MAX(pj2.nota) FROM puntuaciones_jueces pj2 WHERE pj2.id_inscripcion_figuras = pj.id_inscripcion_figuras AND pj2.id_elemento = pj.id_elemento AND pj.id_inscripcion_figuras > 0) as max_fig,
                (SELECT MIN(pj2.nota) FROM puntuaciones_jueces pj2 WHERE pj2.id_rutina = pj.id_rutina AND pj2.id_elemento = pj.id_elemento AND pj2.tipo_ia = pj.tipo_ia AND pj.id_rutina > 0) as min_rut,
                (SELECT MAX(pj2.nota) FROM puntuaciones_jueces pj2 WHERE pj2.id_rutina = pj.id_rutina AND pj2.id_elemento = pj.id_elemento AND pj2.tipo_ia = pj.tipo_ia AND pj.id_rutina > 0) as max_rut,
                (SELECT ROUND(AVG(pj3.nota), 1) FROM puntuaciones_jueces pj3 WHERE (
                    (pj.id_inscripcion_figuras > 0 AND pj3.id_inscripcion_figuras = pj.id_inscripcion_figuras AND pj3.id_elemento = pj.id_elemento)
                    OR 
                    (pj.id_rutina > 0 AND pj3.id_rutina = pj.id_rutina AND pj3.id_elemento = pj.id_elemento AND pj3.tipo_ia = pj.tipo_ia)
                ) AND COALESCE(pj3.nota_menor, '') != 'si' AND COALESCE(pj3.nota_mayor, '') != 'si') as media_consenso
            FROM puntuaciones_jueces pj
            LEFT JOIN inscripciones_figuras i ON pj.id_inscripcion_figuras = i.id
            LEFT JOIN nadadoras n_fig ON i.id_nadadora = n_fig.id
            LEFT JOIN clubes cl_fig ON n_fig.club = cl_fig.id
            LEFT JOIN rutinas r ON pj.id_rutina = r.id
            LEFT JOIN clubes cl_rut ON r.id_club = cl_rut.id
            WHERE pj.id_panel_juez IN ($pjs_str)
        ";
        
        $res_stats = mysqli_query($connection, $q_stats);
        while($s = mysqli_fetch_assoc($res_stats)) {
            $stats_global['total']++;
            $nota = (float)$s['nota'];
            $min = (float)($s['min_fig'] ?? $s['min_rut'] ?? 0);
            $max = (float)($s['max_fig'] ?? $s['max_rut'] ?? 10);
            $consenso = ($s['media_consenso'] !== null) ? (float)$s['media_consenso'] : null;
            $club_id = $s['club_id'];
            $club_name = $s['club_nombre'];

            $estado = 0; // Válida
            if ($nota <= $min && $min > 0) { $stats_global['bajas']++; $estado = 1; }
            elseif ($nota >= $max && $max < 10) { $stats_global['altas']++; $estado = 2; }

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

                // Guardar Punto Individual
                $id_p = $s['id_puntuacion'];
                mysqli_query($connection, "INSERT INTO auditoria_jueces_puntos (id_competicion, id_puntuacion, id_panel_juez, valor_consenso, bias_score, estado) 
                                           VALUES ($id_competicion, $id_p, {$s['id_panel_juez']}, $consenso, $bias, $estado)");
            }
        }

        // Guardar Stat GLOBAL del Juez
        $prec_pct = ($stats_global['desv_count'] > 0) ? round(($stats_global['prec_count'] / $stats_global['desv_count']) * 100, 2) : 0;
        $bias_avg = ($stats_global['desv_count'] > 0) ? round($stats_global['desv_sum'] / $stats_global['desv_count'], 3) : 0;
        
        // Usamos una versión simplificada de bias_score para la tabla stats (media aritmética de desviaciones con signo para severidad)
        // Pero el plan dice 'bias_score' como promedio de desviación (habitualmente se usa el absoluto para calidad, pero con signo para severidad)
        // Calcularemos el promedio con signo para detectar severidad/laxitud
        $q_sign = "SELECT AVG(bias_score) as avg_sign FROM auditoria_jueces_puntos WHERE id_competicion = $id_competicion AND id_panel_juez IN ($pjs_str)";
        $res_sign = mysqli_query($connection, $q_sign);
        $bias_sign = mysqli_fetch_assoc($res_sign)['avg_sign'] ?? 0;

        $pjs_json = mysqli_real_escape_string($connection, $pjs_str);
        $nombre_esc = mysqli_real_escape_string($connection, $data['nombre']);

        mysqli_query($connection, "INSERT INTO auditoria_jueces_stats 
            (id_competicion, group_key, id_juez, entidad_tipo, entidad_id, nombre_entidad, total_notas, bajas, altas, precision_aqua, bias_score, pjs_asociados) 
            VALUES ($id_competicion, '$group_key', {$data['id_juez']}, 'GLOBAL', 0, '$nombre_esc', {$stats_global['total']}, {$stats_global['bajas']}, {$stats_global['altas']}, $prec_pct, $bias_sign, '$pjs_json')");

        // Guardar Stats por CLUB para este juez
        foreach ($club_bias as $cid => $cb) {
            $c_bias_avg = $cb['sum'] / $cb['count'];
            $c_prec = 0; // Opcional calcular precisión por club
            $cb_name_esc = mysqli_real_escape_string($connection, $cb['nombre']);
            mysqli_query($connection, "INSERT INTO auditoria_jueces_stats 
                (id_competicion, group_key, id_juez, entidad_tipo, entidad_id, nombre_entidad, total_notas, bajas, altas, precision_aqua, bias_score, pjs_asociados) 
                VALUES ($id_competicion, '$group_key', {$data['id_juez']}, 'CLUB', $cid, '$cb_name_esc', {$cb['total']}, {$cb['bajas']}, {$cb['altas']}, 0, $c_bias_avg, '$pjs_json')");
        }
    }

    write_log("Auditoría BIAS cerrada/recalculada para: $comp_name", "SUCCESS");
    echo json_encode(['status' => 'success', 'message' => 'Auditoría guardada correctamente']);
}
?>