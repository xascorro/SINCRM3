<?php
header('Content-Type: application/json');
include('database/dbconfig.php');

$id_competicion = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_competicion === 0) {
    // Buscar la competición activa si no se pasa ID
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

// Obtener info de la competición
$q_info = "SELECT nombre, color FROM competiciones WHERE id = $id_competicion";
$res_info = mysqli_query($connection, $q_info);
$info_comp = mysqli_fetch_assoc($res_info);

// Obtener fases
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

    // Obtener rutinas de esta fase
    $q_rutinas = "SELECT r.id, r.nota_final, cl.nombre as club_name, cl.nombre_corto as club_corto, cl.logo
                  FROM rutinas r 
                  JOIN clubes cl ON r.id_club = cl.id 
                  WHERE r.id_fase = $id_fase 
                  ORDER BY r.nota_final DESC, r.orden ASC";
    $res_rutinas = mysqli_query($connection, $q_rutinas);

    $pos = 1;
    while ($r = mysqli_fetch_assoc($res_rutinas)) {
        // Solo incluir si tiene nota o si queremos ver todas
        if ($r['nota_final'] <= 0) continue; 

        // Obtener participantes
        $participants = [];
        $q_p = "SELECT n.nombre, n.apellidos FROM rutinas_participantes rp JOIN nadadoras n ON rp.id_nadadora = n.id WHERE rp.id_rutina = " . $r['id'];
        $res_p = mysqli_query($connection, $q_p);
        while ($p_row = mysqli_fetch_assoc($res_p)) {
            $participants[] = $p_row['nombre'] . ' ' . $p_row['apellidos'];
        }

        $fase_data['rutinas'][] = [
            'pos' => $pos++,
            'id' => $r['id'],
            'club' => $r['club_corto'] ?: $r['club_name'],
            'participantes' => implode(', ', $participants),
            'nota' => (float)$r['nota_final']
        ];
    }

    if (count($fase_data['rutinas']) > 0) {
        $data['fases'][] = $fase_data;
    }
}

echo json_encode($data);
