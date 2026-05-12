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

// Acción: Obtener lista de competiciones
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'get_competiciones_list') {
    $q = "SELECT id, nombre, fecha FROM competiciones ORDER BY fecha DESC";
    $res = mysqli_query($connection, $q);
    $comps = [];
    while($r = mysqli_fetch_assoc($res)) { $comps[] = $r; }
    echo json_encode(['status' => 'success', 'competiciones' => $comps]);
    exit;
}

// Acción: Simular borrado en cascada
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'simular_cascada') {
    $ids = json_decode($_POST['ids'], true);
    if (!$ids) exit;
    $id_list = implode(',', array_map('intval', $ids));
    
    $conteo = [];
    $total_global = 0;

    $tablas = [
        ['entidad' => 'Competiciones', 'sql' => "SELECT COUNT(*) FROM competiciones WHERE id IN ($id_list)"],
        ['entidad' => 'Fases Técnicas', 'sql' => "SELECT COUNT(*) FROM fases WHERE id_competicion IN ($id_list)"],
        ['entidad' => 'Inscripciones (Figuras)', 'sql' => "SELECT COUNT(*) FROM inscripciones_figuras WHERE id_competicion IN ($id_list)"],
        ['entidad' => 'Rutinas / Equipos', 'sql' => "SELECT COUNT(*) FROM rutinas WHERE id_competicion IN ($id_list)"],
        ['entidad' => 'Paneles de Jueces', 'sql' => "SELECT COUNT(*) FROM paneles WHERE id_competicion IN ($id_list)"],
        ['entidad' => 'Vínculos de Jueces', 'sql' => "SELECT COUNT(*) FROM panel_jueces WHERE id_competicion IN ($id_list)"],
        ['entidad' => 'Participantes Rutinas', 'sql' => "SELECT COUNT(*) FROM rutinas_participantes WHERE id_competicion IN ($id_list)"],
        ['entidad' => 'Notas de Jueces', 'sql' => "SELECT COUNT(*) FROM puntuaciones_jueces WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list)) OR id_inscripcion_figuras IN (SELECT id FROM inscripciones_figuras WHERE id_competicion IN ($id_list))"],
        ['entidad' => 'Notas Elementos/TRE', 'sql' => "SELECT COUNT(*) FROM puntuaciones_elementos WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list))"],
        ['entidad' => 'Notas Paneles', 'sql' => "SELECT COUNT(*) FROM puntuaciones_paneles WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list)) OR id_inscripcion_figuras IN (SELECT id FROM inscripciones_figuras WHERE id_competicion IN ($id_list))"],
        ['entidad' => 'Híbridos / Coach Cards', 'sql' => "SELECT COUNT(*) FROM hibridos_rutina WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list))"],
        ['entidad' => 'Resultados & Stats', 'sql' => "SELECT (SELECT COUNT(*) FROM resultados_figuras WHERE id_competicion IN ($id_list)) + (SELECT COUNT(*) FROM resultados_figuras_categorias WHERE id_competicion IN ($id_list)) + (SELECT COUNT(*) FROM resultados_rutinas_categorias WHERE id_competicion IN ($id_list)) + (SELECT COUNT(*) FROM auditoria_jueces_stats WHERE id_competicion IN ($id_list)) + (SELECT COUNT(*) FROM auditoria_jueces_puntos WHERE id_competicion IN ($id_list))"]
    ];

    foreach ($tablas as $t) {
        $res = mysqli_query($connection, $t['sql']);
        $count = mysqli_fetch_array($res)[0];
        $conteo[] = ['entidad' => $t['entidad'], 'total' => (int)$count];
        $total_global += $count;
    }

    echo json_encode(['status' => 'success', 'conteo' => $conteo, 'total_global' => $total_global]);
    exit;
}

// Acción: Ejecutar borrado en cascada
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ejecutar_cascada') {
    $ids = json_decode($_POST['ids'], true);
    if (!$ids) exit;
    $id_list = implode(',', array_map('intval', $ids));

    mysqli_begin_transaction($connection);
    try {
        $total_borrado = 0;

        // 1. Borrar Notas y dependencias profundas (usando subqueries)
        $queries_dep = [
            "DELETE FROM puntuaciones_jueces WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list)) OR id_inscripcion_figuras IN (SELECT id FROM inscripciones_figuras WHERE id_competicion IN ($id_list))",
            "DELETE FROM puntuaciones_elementos WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list))",
            "DELETE FROM puntuaciones_paneles WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list)) OR id_inscripcion_figuras IN (SELECT id FROM inscripciones_figuras WHERE id_competicion IN ($id_list))",
            "DELETE FROM penalizaciones_rutinas WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list)) OR id_inscripcion_figuras IN (SELECT id FROM inscripciones_figuras WHERE id_competicion IN ($id_list))",
            "DELETE FROM penalizaciones_artistico WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list))",
            "DELETE FROM penalizaciones_elementos WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list))",
            "DELETE FROM hibridos_rutina WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list))"
        ];

        // 2. Borrar entidades vinculadas directamente a id_competicion
        $queries_direct = [
            "DELETE FROM panel_jueces WHERE id_competicion IN ($id_list)",
            "DELETE FROM paneles WHERE id_competicion IN ($id_list)",
            "DELETE FROM rutinas_participantes WHERE id_competicion IN ($id_list)",
            "DELETE FROM rutinas WHERE id_competicion IN ($id_list)",
            "DELETE FROM inscripciones_figuras WHERE id_competicion IN ($id_list)",
            "DELETE FROM fases WHERE id_competicion IN ($id_list)",
            "DELETE FROM resultados_figuras WHERE id_competicion IN ($id_list)",
            "DELETE FROM resultados_figuras_categorias WHERE id_competicion IN ($id_list)",
            "DELETE FROM resultados_rutinas_categorias WHERE id_competicion IN ($id_list)",
            "DELETE FROM auditoria_jueces_stats WHERE id_competicion IN ($id_list)",
            "DELETE FROM auditoria_jueces_puntos WHERE id_competicion IN ($id_list)",
            "DELETE FROM calculos_clasificacion WHERE id_competicion IN ($id_list)",
            "DELETE FROM competiciones WHERE id IN ($id_list)"
        ];

        foreach (array_merge($queries_dep, $queries_direct) as $q) {
            if (mysqli_query($connection, $q)) {
                $total_borrado += mysqli_affected_rows($connection);
            } else {
                throw new Exception(mysqli_error($connection));
            }
        }

        mysqli_commit($connection);
        write_log("LIMPIEZA EN CASCADA: Eliminadas competiciones [$id_list]. Total registros borrados: $total_borrado", "SECURITY");
        echo json_encode(['status' => 'success', 'total_borrado' => $total_borrado]);

    } catch (Exception $e) {
        mysqli_rollback($connection);
        echo json_encode(['status' => 'error', 'message' => "Fallo en cascada: ".$e->getMessage()]);
    }
    exit;
}
?>