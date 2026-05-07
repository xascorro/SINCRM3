<?php
include('security.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'analisis_especifico') {
    $tipo = $_POST['tipo'];
    $registros = [];
    $titulo = "";
    $mensaje = "";

    switch ($tipo) {
        case 'deportistas_duplicados':
            $titulo = "Deportistas Duplicadas";
            $mensaje = "Se han detectado nadadoras con el mismo nombre y apellidos o misma licencia:";
            // Por nombre y apellidos
            $q = "SELECT GROUP_CONCAT(id) as ids, nombre, apellidos, COUNT(*) as n FROM nadadoras GROUP BY nombre, apellidos HAVING n > 1";
            $res = mysqli_query($connection, $q);
            while($r = mysqli_fetch_assoc($res)) {
                $registros[] = ['id' => $r['ids'], 'nombre' => $r['nombre']." ".$r['apellidos'], 'detalle' => "Repetida ".$r['n']." veces"];
            }
            // Por licencia (si no está vacía)
            $q2 = "SELECT GROUP_CONCAT(id) as ids, licencia, COUNT(*) as n FROM nadadoras WHERE licencia != '' AND licencia IS NOT NULL GROUP BY licencia HAVING n > 1";
            $res2 = mysqli_query($connection, $q2);
            while($r = mysqli_fetch_assoc($res2)) {
                $registros[] = ['id' => $r['ids'], 'nombre' => "Licencia: ".$r['licencia'], 'detalle' => "Compartida por ".$r['n']." perfiles"];
            }
            break;

        case 'deportistas_sin_inscripciones':
            $titulo = "Deportistas sin Actividad";
            $mensaje = "Nadadoras que no figuran en ninguna lista de salida ni de rutinas:";
            $q = "SELECT n.id, n.nombre, n.apellidos, c.nombre_corto as club 
                  FROM nadadoras n 
                  LEFT JOIN clubes c ON n.club = c.id
                  WHERE n.id NOT IN (SELECT id_nadadora FROM inscripciones_figuras) 
                  AND n.id NOT IN (SELECT id_nadadora FROM rutinas_participantes)
                  LIMIT 100";
            $res = mysqli_query($connection, $q);
            while($r = mysqli_fetch_assoc($res)) {
                $registros[] = ['id' => $r['id'], 'nombre' => $r['apellidos'].", ".$r['nombre'], 'detalle' => "Club: ".($r['club'] ?: 'Sin club')];
            }
            break;

        case 'jueces_duplicados':
            $titulo = "Jueces Duplicados";
            $mensaje = "Fichas de jueces con datos idénticos detectadas:";
            $q = "SELECT GROUP_CONCAT(id) as ids, nombre, apellidos, COUNT(*) as n FROM jueces GROUP BY nombre, apellidos HAVING n > 1";
            $res = mysqli_query($connection, $q);
            while($r = mysqli_fetch_assoc($res)) {
                $registros[] = ['id' => $r['ids'], 'nombre' => $r['nombre']." ".$r['apellidos'], 'detalle' => "Existen ".$r['n']." fichas para este nombre"];
            }
            break;

        case 'jueces_sin_cuenta':
            $titulo = "Jueces sin Vinculación Web";
            $mensaje = "Jueces del censo que no tienen un usuario web asociado para ver sus auditorías:";
            $q = "SELECT j.id, j.nombre, j.apellidos, f.nombre_corto as fed 
                  FROM jueces j 
                  LEFT JOIN federaciones f ON j.federacion = f.id
                  WHERE j.id NOT IN (SELECT id_juez_v3 FROM usuarios WHERE id_juez_v3 IS NOT NULL)
                  AND j.activo = 1";
            $res = mysqli_query($connection, $q);
            while($r = mysqli_fetch_assoc($res)) {
                $registros[] = ['id' => $r['id'], 'nombre' => $r['nombre']." ".$r['apellidos'], 'detalle' => "Fed: ".($r['fed'] ?: 'N/A')];
            }
            break;

        case 'fases_vacias':
            $titulo = "Fases sin Inscripciones";
            $mensaje = "Fases activas que no tienen ninguna nadadora inscrita:";
            $q = "SELECT f.id, cat.nombre as cat, fig.nombre as fig, c.nombre as comp 
                  FROM fases f
                  JOIN categorias cat ON f.id_categoria = cat.id
                  LEFT JOIN figuras fig ON f.id_figura = fig.id
                  JOIN competiciones c ON f.id_competicion = c.id
                  WHERE f.id NOT IN (SELECT id_fase FROM inscripciones_figuras)
                  AND c.activo = 'si'";
            $res = mysqli_query($connection, $q);
            while($r = mysqli_fetch_assoc($res)) {
                $registros[] = ['id' => $r['id'], 'nombre' => ($r['fig'] ?: 'Rutina')." (".$r['cat'].")", 'detalle' => $r['comp']];
            }
            break;

        case 'competiciones_sin_fases':
            $titulo = "Eventos sin Configuración";
            $mensaje = "Competiciones dadas de alta que no contienen ninguna fase técnica:";
            $q = "SELECT id, nombre, fecha FROM competiciones WHERE id NOT IN (SELECT id_competicion FROM fases)";
            $res = mysqli_query($connection, $q);
            while($r = mysqli_fetch_assoc($res)) {
                $registros[] = ['id' => $r['id'], 'nombre' => $r['nombre'], 'detalle' => "Fecha: ".$r['fecha']];
            }
            break;

        case 'notas_huerfanas':
            $titulo = "Integridad de Notas";
            $mensaje = "Se han encontrado notas que apuntan a paneles eliminados. SE RECOMIENDA LIMPIEZA:";
            // Figuras
            $q = "SELECT f.id as fase_id, c.nombre as comp, pj.id_panel_juez, COUNT(*) as n 
                  FROM puntuaciones_jueces pj
                  JOIN inscripciones_figuras ifig ON pj.id_inscripcion_figuras = ifig.id
                  JOIN fases f ON ifig.id_fase = f.id
                  JOIN competiciones c ON f.id_competicion = c.id
                  WHERE pj.id_panel_juez NOT IN (SELECT id FROM panel_jueces)
                  GROUP BY pj.id_panel_juez";
            $res = mysqli_query($connection, $q);
            while($r = mysqli_fetch_assoc($res)) {
                $registros[] = ['id' => $r['id_panel_juez'], 'nombre' => "Fase #".$r['fase_id']." (Figuras)", 'detalle' => $r['n']." notas huérfanas en ".$r['comp']];
            }
            // Rutinas
            $q2 = "SELECT r.id as rut_id, c.nombre as comp, pj.id_panel_juez, COUNT(*) as n 
                   FROM puntuaciones_jueces pj
                   JOIN rutinas r ON pj.id_rutina = r.id
                   JOIN competiciones c ON r.id_competicion = c.id
                   WHERE pj.id_panel_juez NOT IN (SELECT id FROM panel_jueces)
                   GROUP BY pj.id_panel_juez";
            $res2 = mysqli_query($connection, $q2);
            while($r = mysqli_fetch_assoc($res2)) {
                $registros[] = ['id' => $r['id_panel_juez'], 'nombre' => "Rutina #".$r['rut_id'], 'detalle' => $r['n']." notas huérfanas en ".$r['comp']];
            }
            break;
    }

    echo json_encode([
        'status' => 'success',
        'titulo' => $titulo,
        'mensaje' => $mensaje,
        'registros' => $registros
    ]);
    exit;
}

// Acción: Reparar Notas Huérfanas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reparar_notas') {
    $q_del = "DELETE FROM puntuaciones_jueces WHERE id_panel_juez NOT IN (SELECT id FROM panel_jueces)";
    if (mysqli_query($connection, $q_del)) {
        $affected = mysqli_affected_rows($connection);
        write_log("Reparación de integridad: Eliminadas $affected notas huérfanas.", "SECURITY");
        echo json_encode(['status' => 'success', 'affected' => $affected]);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($connection)]);
    }
    exit;
}
?>