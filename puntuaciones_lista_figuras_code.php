<?php
include('security.php');
include('./lib/my_functions.php');

$id_fase = isset($_POST['id_fase']) ? $_POST['id_fase'] : '';
$id_inscripcion_figuras = isset($_POST['id_inscripcion_figuras']) ? $_POST['id_inscripcion_figuras'] : '';
$GD = isset($_POST['grado_dificultad']) ? $_POST['grado_dificultad'] : '';

$is_ajax = !empty($_POST['ajax']);

function puntuaciones_figuras_json_exit($ok, $payload = [])
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(['ok' => $ok], $payload));
    exit;
}

$pen_action = $_POST['action'] ?? $_GET['action'] ?? '';

// -------------------------------------------------------------
// GESTIÓN DE CIERRE / REAPERTURA DE FASE VÍA AJAX
// -------------------------------------------------------------
if ($pen_action === 'cerrar_fase' || $pen_action === 'toggle_cerrar_fase') {
    $id_fase_action = (int)($_POST['id_fase'] ?? 0);
    $nuevo_estado = $_POST['estado'] ?? ''; // 'si' o 'no'

    if ($id_fase_action <= 0) {
        puntuaciones_figuras_json_exit(false, ['message' => 'Fase no válida']);
    }

    $q_fase = mysqli_query($connection, "SELECT id, puntuada, id_competicion, id_categoria FROM fases WHERE id = $id_fase_action");
    if (!$q_fase || mysqli_num_rows($q_fase) === 0) {
        puntuaciones_figuras_json_exit(false, ['message' => 'Fase no encontrada']);
    }
    $fase_row = mysqli_fetch_assoc($q_fase);
    $estado_actual = $fase_row['puntuada'] ?? 'no';

    if ($nuevo_estado !== 'si' && $nuevo_estado !== 'no') {
        $nuevo_estado = ($estado_actual === 'si') ? 'no' : 'si';
    }

    $u_ok = mysqli_query($connection, "UPDATE fases SET puntuada = '$nuevo_estado' WHERE id = $id_fase_action");
    if (!$u_ok) {
        puntuaciones_figuras_json_exit(false, ['message' => 'Error al actualizar estado en BD: ' . mysqli_error($connection)]);
    }

    // Si se cierra la fase ('si'), recalcular resultados generales de figuras para la competición
    $id_comp = intval($fase_row['id_competicion'] ?? 0);
    if ($nuevo_estado === 'si' && $id_comp > 0) {
        $_SESSION['id_competicion_activa'] = $id_comp;
        ob_start();
        @include(__DIR__ . '/puntuaciones_fases_figuras_puntuar.php');
        @include(__DIR__ . '/puntuaciones_fases_figuras_puntuar_categorias.php');
        ob_end_clean();
    }

    // Estadísticas actualizadas de la fase
    $q_cnt = mysqli_query($connection, "SELECT 
        COUNT(*) as total, 
        SUM(CASE WHEN (nota_final IS NOT NULL AND nota_final != '') OR baja = 'si' THEN 1 ELSE 0 END) as puntuadas 
        FROM inscripciones_figuras WHERE id_fase = $id_fase_action");
    $cnt = $q_cnt ? mysqli_fetch_assoc($q_cnt) : ['total' => 0, 'puntuadas' => 0];
    $total_nad = (int)($cnt['total'] ?? 0);
    $punt_nad = (int)($cnt['puntuadas'] ?? 0);

    puntuaciones_figuras_json_exit(true, [
        'puntuada' => $nuevo_estado,
        'total' => $total_nad,
        'puntuadas' => $punt_nad,
        'pendientes' => max(0, $total_nad - $punt_nad),
        'message' => ($nuevo_estado === 'si') ? 'Fase cerrada y completada con éxito' : 'Fase reabierta para edición'
    ]);
}

// -------------------------------------------------------------
// GESTIÓN DE PENALIZACIONES DE FIGURAS VÍA AJAX
// -------------------------------------------------------------
if ($pen_action === 'get_penalizaciones') {
    $id_insc = (int)($_POST['id_inscripcion_figuras'] ?? $_GET['id_inscripcion_figuras'] ?? 0);
    $q_pen = "SELECT pr.id as id_pr, p.id as id_penalizacion, p.codigo, p.resumen, p.puntos, pt.nombre as tipo 
              FROM penalizaciones_rutinas pr 
              JOIN penalizaciones p ON pr.id_penalizacion = p.id 
              LEFT JOIN paneles_tipo pt ON p.id_paneles_tipo = pt.id 
              WHERE pr.id_inscripcion_figuras = $id_insc 
              ORDER BY pr.id ASC";
    $res = mysqli_query($connection, $q_pen);
    $items = [];
    $total_puntos = 0;
    if ($res) {
        while ($r = mysqli_fetch_assoc($res)) {
            $items[] = $r;
            $total_puntos += abs(floatval($r['puntos']));
        }
    }
    puntuaciones_figuras_json_exit(true, ['items' => $items, 'total_puntos' => $total_puntos]);
}

if ($pen_action === 'add_penalizacion') {
    $id_insc = (int)($_POST['id_inscripcion_figuras'] ?? 0);
    $id_pen = (int)($_POST['id_penalizacion'] ?? 0);
    if ($id_insc <= 0 || $id_pen <= 0) {
        puntuaciones_figuras_json_exit(false, ['message' => 'Por favor selecciona una penalización válida']);
    }
    $query = "INSERT INTO penalizaciones_rutinas (id_inscripcion_figuras, id_penalizacion) VALUES ($id_insc, $id_pen)";
    if (!mysqli_query($connection, $query)) {
        puntuaciones_figuras_json_exit(false, ['message' => 'Error al guardar penalización: ' . mysqli_error($connection)]);
    }
    // Devolver lista actualizada
    $q_pen = "SELECT pr.id as id_pr, p.id as id_penalizacion, p.codigo, p.resumen, p.puntos, pt.nombre as tipo 
              FROM penalizaciones_rutinas pr 
              JOIN penalizaciones p ON pr.id_penalizacion = p.id 
              LEFT JOIN paneles_tipo pt ON p.id_paneles_tipo = pt.id 
              WHERE pr.id_inscripcion_figuras = $id_insc 
              ORDER BY pr.id ASC";
    $res = mysqli_query($connection, $q_pen);
    $items = [];
    $total_puntos = 0;
    if ($res) {
        while ($r = mysqli_fetch_assoc($res)) {
            $items[] = $r;
            $total_puntos += abs(floatval($r['puntos']));
        }
    }
    puntuaciones_figuras_json_exit(true, ['items' => $items, 'total_puntos' => $total_puntos]);
}

if ($pen_action === 'del_penalizacion') {
    $id_pr = (int)($_POST['id_pr'] ?? 0);
    $id_insc = (int)($_POST['id_inscripcion_figuras'] ?? 0);
    if ($id_pr <= 0) {
        puntuaciones_figuras_json_exit(false, ['message' => 'ID de penalización no válido']);
    }
    $query = "DELETE FROM penalizaciones_rutinas WHERE id = $id_pr";
    if (!mysqli_query($connection, $query)) {
        puntuaciones_figuras_json_exit(false, ['message' => 'Error al eliminar penalización: ' . mysqli_error($connection)]);
    }
    // Devolver lista actualizada
    $q_pen = "SELECT pr.id as id_pr, p.id as id_penalizacion, p.codigo, p.resumen, p.puntos, pt.nombre as tipo 
              FROM penalizaciones_rutinas pr 
              JOIN penalizaciones p ON pr.id_penalizacion = p.id 
              LEFT JOIN paneles_tipo pt ON p.id_paneles_tipo = pt.id 
              WHERE pr.id_inscripcion_figuras = $id_insc 
              ORDER BY pr.id ASC";
    $res = mysqli_query($connection, $q_pen);
    $items = [];
    $total_puntos = 0;
    if ($res) {
        while ($r = mysqli_fetch_assoc($res)) {
            $items[] = $r;
            $total_puntos += abs(floatval($r['puntos']));
        }
    }
    puntuaciones_figuras_json_exit(true, ['items' => $items, 'total_puntos' => $total_puntos]);
}

if (isset($_POST['puntuar_btn'])) {
    if ($id_fase === '' || $id_inscripcion_figuras === '' || $GD === '') {
        if ($is_ajax) {
            puntuaciones_figuras_json_exit(false, ['message' => 'Faltan datos requeridos']);
        }
        exit;
    }

    try {
        $query = "SELECT numero_juez FROM panel_jueces WHERE id_fase=" . $id_fase;
        $numero_de_jueces = mysqli_query($connection, $query);
        if (!$numero_de_jueces) {
            throw new Exception(mysqli_error($connection));
        }
        $numero_jueces = mysqli_num_rows($numero_de_jueces);
        if (!$is_ajax) {
            echo '<br>numero_jueces = ' . $numero_jueces;
        }

        $i = 1;
        while ($numero_de_juez = mysqli_fetch_assoc($numero_de_jueces)) {
            $nota = isset($_POST['nota'][$i]['nota']) ? $_POST['nota'][$i]['nota'] : 0;
            if ($nota > 10) {
                $nota = $nota / 10;
            }
            $id_panel_jueces = $_POST['nota'][$i]['id_panel_jueces'];
            $id_juez = $_POST['nota'][$i]['id_juez'];
            $query = "SELECT id FROM puntuaciones_jueces WHERE id_inscripcion_figuras='$id_inscripcion_figuras' and id_panel_juez='$id_panel_jueces'";
            $resultado = mysqli_query($connection, $query);
            if (!$resultado) {
                throw new Exception(mysqli_error($connection));
            }
            if (mysqli_num_rows($resultado) > 0) {
                $query = "UPDATE puntuaciones_jueces SET nota='$nota', nota_menor = 0, nota_mayor = 0 WHERE id_inscripcion_figuras= '$id_inscripcion_figuras' and id_panel_juez='$id_panel_jueces'";
            } else {
                $query = "INSERT INTO puntuaciones_jueces (id_inscripcion_figuras, id_panel_juez, nota, nota_menor, nota_mayor) values ('$id_inscripcion_figuras', '$id_panel_jueces', '$nota', 0, 0)";
            }
            if (!mysqli_query($connection, $query)) {
                throw new Exception(mysqli_error($connection));
            }
            $i++;
        }

        if ($numero_jueces > 3) {
            $query = "SELECT min(nota) FROM puntuaciones_jueces WHERE id_inscripcion_figuras = $id_inscripcion_figuras";
            if (!$is_ajax) {
                echo '<br>' . $query;
            }
            $nota_menor = mysqli_result(mysqli_query($connection, $query), 0);
            $query = "UPDATE puntuaciones_jueces set nota_menor = 1 WHERE id_inscripcion_figuras = $id_inscripcion_figuras and nota = $nota_menor limit 1";
            if (!$is_ajax) {
                echo '<br>' . $query;
            }
            mysqli_query($connection, $query);

            $query = "SELECT max(nota) FROM puntuaciones_jueces WHERE id_inscripcion_figuras = $id_inscripcion_figuras";
            if (!$is_ajax) {
                echo '<br>' . $query;
            }
            $nota_mayor = mysqli_result(mysqli_query($connection, $query), 0);
            $query = "UPDATE puntuaciones_jueces set nota_mayor = 1 WHERE id_inscripcion_figuras = $id_inscripcion_figuras and nota = $nota_mayor and nota_menor = 0 limit 1";
            if (!$is_ajax) {
                echo '<br>' . $query;
            }
            mysqli_query($connection, $query);

            $query = "SELECT nota FROM puntuaciones_jueces WHERE nota > 0 and id_inscripcion_figuras = $id_inscripcion_figuras";
            $notas_no_cero = mysqli_num_rows(mysqli_query($connection, $query));
            if ($notas_no_cero > 0) {
                $query = "SELECT sum(nota) FROM puntuaciones_jueces WHERE nota > 0 and id_inscripcion_figuras = $id_inscripcion_figuras";
                if (!$is_ajax) {
                    echo '<br>' . $query;
                }
                $nota_media = mysqli_result(mysqli_query($connection, $query), 0) / $notas_no_cero;
                $nota_media = round($nota_media, 1, PHP_ROUND_HALF_UP);
                $query = "UPDATE puntuaciones_jueces set nota = $nota_media WHERE id_inscripcion_figuras = $id_inscripcion_figuras and nota = 0";
                if (!$is_ajax) {
                    echo '<br>' . $query;
                }
                mysqli_query($connection, $query);
            }
        }

        $query = "SELECT sum(nota) FROM puntuaciones_jueces WHERE id_inscripcion_figuras = $id_inscripcion_figuras and (nota_mayor = 0 and nota_menor = 0) limit 1";
        if (!$is_ajax) {
            echo '<br>' . $query;
        }
        $nota_total = mysqli_result(mysqli_query($connection, $query), 0);
        $query = "SELECT sum(nota)/3 FROM puntuaciones_jueces WHERE id_inscripcion_figuras = $id_inscripcion_figuras and (nota_mayor = 0 and nota_menor = 0)";
        if (!$is_ajax) {
            echo '<br>' . $query;
        }
        $nota_media = mysqli_result(mysqli_query($connection, $query), 0);
        $nota_final = $nota_media * $GD;

        if (!$is_ajax) {
            echo '<br><b>nota_total</b> es la suma de todas las notas menos menor y mayor';
            echo '<br><b>nota_media</b> es la media de la nota_total';
            echo '<br><b>nota_final</b> es la nota_media por el grado de dificultad';
        }

        $query = "UPDATE inscripciones_figuras SET nota_media = $nota_media, nota_total = $nota_total, nota_final= $nota_final WHERE id = $id_inscripcion_figuras";
        if (!$is_ajax) {
            echo '<br>' . $query;
        }
        if (!mysqli_query($connection, $query)) {
            throw new Exception(mysqli_error($connection));
        }

        $q_sum = "SELECT COALESCE(SUM(nota), 0) AS s FROM puntuaciones_jueces WHERE id_inscripcion_figuras = " . (int) $id_inscripcion_figuras;
        $r_sum = mysqli_query($connection, $q_sum);
        $sumatorio = $r_sum ? mysqli_fetch_assoc($r_sum)['s'] : 0;

        $notas_juez = [];
        if ($is_ajax) {
            $q_nj = "SELECT pj.numero_juez, COALESCE(pn.nota, 0) AS nota FROM panel_jueces pj
                LEFT JOIN puntuaciones_jueces pn ON pn.id_panel_juez = pj.id AND pn.id_inscripcion_figuras = " . (int) $id_inscripcion_figuras . "
                WHERE pj.id_fase = " . (int) $id_fase . "
                ORDER BY pj.numero_juez";
            if ($r_nj = mysqli_query($connection, $q_nj)) {
                while ($nj = mysqli_fetch_assoc($r_nj)) {
                    $notas_juez[(string) (int) $nj['numero_juez']] = round((float) $nj['nota'], 1);
                }
            }
        }

        if ($is_ajax) {
            puntuaciones_figuras_json_exit(true, [
                'id_inscripcion_figuras' => (int) $id_inscripcion_figuras,
                'sumatorio' => round((float) $sumatorio, 1),
                'nota_total' => $nota_total !== null && $nota_total !== '' ? round((float) $nota_total, 1) : 0,
                'nota_media' => $nota_media !== null && $nota_media !== '' ? round((float) $nota_media, 4) : 0,
                'nota_final' => $nota_final !== null && $nota_final !== '' ? round((float) $nota_final, 4) : 0,
                'notas_juez' => $notas_juez,
            ]);
        }
    } catch (Exception $e) {
        if ($is_ajax) {
            puntuaciones_figuras_json_exit(false, ['message' => $e->getMessage()]);
        }
        echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        exit;
    }

    echo '<script>window.close();</script>';
}
