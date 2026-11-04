<?php
header('Content-Type: application/json');
include('database/dbconfig.php');

$id_competicion = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_competicion === 0) {
    $q_comp = "SELECT id FROM competiciones WHERE activo = 'si' LIMIT 1";
    $res_comp = mysqli_query($connection, $q_comp);
    if ($res_comp && $row_comp = mysqli_fetch_assoc($res_comp)) {
        $id_competicion = (int)$row_comp['id'];
    }
}

if ($id_competicion === 0) {
    echo json_encode(['error' => 'No hay competición activa']);
    exit;
}

$q_info = "SELECT nombre, color FROM competiciones WHERE id = $id_competicion";
$res_info = mysqli_query($connection, $q_info);
$info_comp = mysqli_fetch_assoc($res_info);

$q_fases = "SELECT f.id, c.nombre as categoria, m.nombre as modalidad, f.orden, f.genero 
            FROM fases f 
            JOIN categorias c ON f.id_categoria = c.id 
            JOIN modalidades m ON f.id_modalidad = m.id 
            WHERE f.id_competicion = $id_competicion 
            ORDER BY f.orden ASC";
$res_fases = mysqli_query($connection, $q_fases);

$data = [
    'competicion' => $info_comp['nombre'],
    'color' => $info_comp['color'],
    'fases' => []
];

while ($f = mysqli_fetch_assoc($res_fases)) {
    $id_fase = $f['id'];
    $fase_data = [
        'id' => $id_fase,
        'nombre' => $f['categoria'] . ' - ' . $f['modalidad'] . (($f['genero'] ?? '') === 'masculino' ? ' Masc' : ''),
        'rutinas' => []
    ];

    $q_rutinas = "SELECT r.id, r.nota_final, cl.nombre as club_name, cl.nombre_corto as club_corto
                  FROM rutinas r 
                  JOIN clubes cl ON r.id_club = cl.id 
                  WHERE r.id_fase = $id_fase 
                  ORDER BY r.nota_final DESC, r.orden ASC";
    $res_rutinas = mysqli_query($connection, $q_rutinas);

    $rutinas_raw = [];
    $score_counts = [];
    while ($r = mysqli_fetch_assoc($res_rutinas)) {
        if ($r['nota_final'] <= 0) continue; 
        $rutinas_raw[] = $r;
        $score_str = number_format((float)$r['nota_final'], 4, '.', '');
        if (!isset($score_counts[$score_str])) $score_counts[$score_str] = 0;
        $score_counts[$score_str]++;
    }

    $actual_pos = 1;
    $display_pos = 1;
    $prev_score = -1;

    foreach ($rutinas_raw as $r) {
        $score_str = number_format((float)$r['nota_final'], 4, '.', '');
        
        if ($prev_score !== (float)$r['nota_final']) {
            $display_pos = $actual_pos;
        }
        $prev_score = (float)$r['nota_final'];
        $actual_pos++;

        $is_tie = $score_counts[$score_str] > 1;

        $participants = [];
        $q_p = "SELECT n.nombre, n.apellidos FROM rutinas_participantes rp JOIN nadadoras n ON rp.id_nadadora = n.id WHERE rp.id_rutina = " . $r['id'];
        $res_p = mysqli_query($connection, $q_p);
        while ($p_row = mysqli_fetch_assoc($res_p)) {
            $participants[] = $p_row['nombre'] . ' ' . $p_row['apellidos'];
        }

        // Obtener Penalizaciones
        $penalizaciones = [];
        $q_pen = "SELECT p.codigo, p.puntos, p.resumen FROM penalizaciones_rutinas pr JOIN penalizaciones p ON pr.id_penalizacion = p.id WHERE pr.id_rutina = " . $r['id'];
        $res_pen = mysqli_query($connection, $q_pen);
        while ($pen_row = mysqli_fetch_assoc($res_pen)) {
            $penalizaciones[] = [
                'codigo' => $pen_row['codigo'],
                'puntos' => (float)$pen_row['puntos'],
                'resumen' => $pen_row['resumen']
            ];
        }

        // Obtener Paneles y Notas de Jueces
        $paneles_notas = [];
        $q_pe = "SELECT pe.tipo_ia, pe.nota_media, pe.nota as nota_panel, pe.factor 
                  FROM puntuaciones_elementos pe 
                  WHERE pe.id_rutina = " . $r['id'] . " 
                  ORDER BY pe.id ASC";
        $res_pe = mysqli_query($connection, $q_pe);
        
        while ($pe = mysqli_fetch_assoc($res_pe)) {
            $panel_name = $pe['tipo_ia'];
            
            // Buscar notas individuales para este panel (tipo_ia)
            $jueces_notas = [];
            $q_pj = "SELECT pj.nota, pj.nota_menor, pj.nota_mayor 
                      FROM puntuaciones_jueces pj 
                      WHERE pj.id_rutina = " . $r['id'] . " 
                      AND pj.tipo_ia = '" . mysqli_real_escape_string($connection, $panel_name) . "'
                      ORDER BY pj.id ASC";
            $res_pj = mysqli_query($connection, $q_pj);
            while ($pj = mysqli_fetch_assoc($res_pj)) {
                $jueces_notas[] = [
                    'nota' => (float)$pj['nota'],
                    'min' => $pj['nota_menor'] == 'si',
                    'max' => $pj['nota_mayor'] == 'si'
                ];
            }

            $paneles_notas[] = [
                'nombre' => $panel_name,
                'media' => (float)$pe['nota_media'],
                'total_panel' => (float)$pe['nota_panel'],
                'factor' => (float)$pe['factor'],
                'jueces' => $jueces_notas
            ];
        }

        $fase_data['rutinas'][] = [
            'pos' => $display_pos,
            'empate' => $is_tie,
            'empate_count' => $score_counts[$score_str],
            'id' => $r['id'],
            'club' => $r['club_corto'] ?: $r['club_name'],
            'participantes' => implode(', ', $participants),
            'nota' => (float)$r['nota_final'],
            'paneles' => $paneles_notas,
            'penalizaciones' => $penalizaciones
        ];
    }

    if (count($fase_data['rutinas']) > 0) {
        $data['fases'][] = $fase_data;
    }
}

echo json_encode($data);
