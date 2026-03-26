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
                $query = "UPDATE puntuaciones_jueces SET nota='$nota', nota_menor = NULL, nota_mayor = NULL WHERE id_inscripcion_figuras= '$id_inscripcion_figuras' and id_panel_juez='$id_panel_jueces'";
            } else {
                $query = "INSERT INTO puntuaciones_jueces (id_inscripcion_figuras, id_panel_juez, nota) values ('$id_inscripcion_figuras', '$id_panel_jueces', '$nota')";
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
            $query = "UPDATE puntuaciones_jueces set nota_menor = 'si' WHERE id_inscripcion_figuras = $id_inscripcion_figuras and nota = $nota_menor limit 1";
            if (!$is_ajax) {
                echo '<br>' . $query;
            }
            mysqli_query($connection, $query);

            $query = "SELECT max(nota) FROM puntuaciones_jueces WHERE id_inscripcion_figuras = $id_inscripcion_figuras";
            if (!$is_ajax) {
                echo '<br>' . $query;
            }
            $nota_mayor = mysqli_result(mysqli_query($connection, $query), 0);
            $query = "UPDATE puntuaciones_jueces set nota_mayor = 'si' WHERE id_inscripcion_figuras = $id_inscripcion_figuras and nota = $nota_mayor and nota_menor IS NULL limit 1";
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

        $query = "SELECT sum(nota) FROM puntuaciones_jueces WHERE id_inscripcion_figuras = $id_inscripcion_figuras and (nota_mayor IS NULL and nota_menor IS NULL) limit 1";
        if (!$is_ajax) {
            echo '<br>' . $query;
        }
        $nota_total = mysqli_result(mysqli_query($connection, $query), 0);
        $query = "SELECT sum(nota)/3 FROM puntuaciones_jueces WHERE id_inscripcion_figuras = $id_inscripcion_figuras and (nota_mayor IS NULL and nota_menor IS NULL)";
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

// MANEJO DE PENALIZACIONES
if (isset($_POST['penalizacion_aplicar'])) {
    // Detener cualquier buffering y establecer header JSON
    while (ob_get_level() > 0) ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    
    $id_inscripcion_figuras = isset($_POST['id_inscripcion_figuras']) ? (int) $_POST['id_inscripcion_figuras'] : 0;
    $id_penalizacion = isset($_POST['id_penalizacion']) ? (int) $_POST['id_penalizacion'] : 0;
    $id_fase = isset($_POST['id_fase']) ? (int) $_POST['id_fase'] : 0;
    
    if ($id_inscripcion_figuras > 0 && $id_penalizacion > 0 && $id_fase > 0) {
        try {
            // Verificar que no esté duplicada
            $query_check = "SELECT id FROM penalizaciones_rutinas 
                            WHERE id_inscripcion_figuras = $id_inscripcion_figuras 
                            AND id_penalizacion = $id_penalizacion";
            $result_check = mysqli_query($connection, $query_check);
            
            if (!$result_check) {
                throw new Exception("Error al verificar penalización: " . mysqli_error($connection));
            }
            
            if (mysqli_num_rows($result_check) == 0) {
                // No está duplicada, agregar
                $query_insert = "INSERT INTO penalizaciones_rutinas 
                                (id_inscripcion_figuras, id_penalizacion) 
                                VALUES ($id_inscripcion_figuras, $id_penalizacion)";
                
                if (!mysqli_query($connection, $query_insert)) {
                    throw new Exception("Error al agregar penalización: " . mysqli_error($connection));
                }
                
                echo json_encode(['success' => true, 'message' => 'Penalización aplicada correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Esta penalización ya está aplicada a esta nadadora']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
    }
    die;
}

// MANEJO DE BORRAR PENALIZACIONES
if (isset($_POST['penalizacion_borrar'])) {
    // Detener cualquier buffering y establecer header JSON
    while (ob_get_level() > 0) ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    
    $id_penalizacion_registro = isset($_POST['id_penalizacion_registro']) ? (int) $_POST['id_penalizacion_registro'] : 0;
    $id_fase = isset($_POST['id_fase']) ? (int) $_POST['id_fase'] : 0;
    
    if ($id_penalizacion_registro > 0 && $id_fase > 0) {
        try {
            $query_delete = "DELETE FROM penalizaciones_rutinas WHERE id = $id_penalizacion_registro";
            
            if (!mysqli_query($connection, $query_delete)) {
                throw new Exception("Error al borrar penalización: " . mysqli_error($connection));
            }
            
            echo json_encode(['success' => true, 'message' => 'Penalización eliminada correctamente']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
    }
    die;
}

// OBTENER TABLA ACTUALIZADA DE PENALIZACIONES
if (isset($_POST['get_penalizaciones_tabla'])) {
    header('Content-Type: application/json; charset=utf-8');
    
    $id_fase = isset($_POST['id_fase']) ? (int) $_POST['id_fase'] : 0;
    
    if ($id_fase > 0) {
        try {
            $query_penalizadas = "SELECT 
                pn.id as id_penalizacion_registro,
                COALESCE(
                    (SELECT if2.orden FROM inscripciones_figuras if2
                     WHERE if2.id_nadadora = inscripciones_figuras.id_nadadora
                       AND if2.id_fase = (
                           SELECT f0.id FROM fases f0
                           WHERE f0.id_competicion = fases.id_competicion
                             AND f0.id_categoria = fases.id_categoria
                             AND IFNULL(f0.id_modalidad,'') = IFNULL(fases.id_modalidad,'')
                           ORDER BY f0.orden ASC, f0.id ASC
                           LIMIT 1
                       )
                     LIMIT 1),
                    inscripciones_figuras.orden
                ) AS orden,
                nadadoras.nombre, nadadoras.apellidos,
                penalizaciones.codigo, penalizaciones.resumen, penalizaciones.puntos
                FROM penalizaciones_rutinas pn
                INNER JOIN inscripciones_figuras ON inscripciones_figuras.id = pn.id_inscripcion_figuras
                INNER JOIN fases ON inscripciones_figuras.id_fase = fases.id
                INNER JOIN nadadoras ON nadadoras.id = inscripciones_figuras.id_nadadora
                INNER JOIN penalizaciones ON penalizaciones.id = pn.id_penalizacion
                WHERE inscripciones_figuras.id_fase = $id_fase
                ORDER BY orden, nadadoras.apellidos";
            
            $result_penalizadas = mysqli_query($connection, $query_penalizadas);
            
            if (!$result_penalizadas) {
                throw new Exception("Error en query: " . mysqli_error($connection));
            }
            
            $html = '<thead><tr><th scope="col">Orden</th><th scope="col">#</th><th scope="col">Nadadora</th><th scope="col">Penalización</th><th scope="col">Puntos</th><th scope="col" class="text-center">Acción</th></tr></thead><tbody>';
            
            if (mysqli_num_rows($result_penalizadas) > 0) {
                while($penalizada = mysqli_fetch_assoc($result_penalizadas)) {
                    $html .= '<tr>
                        <td>'.$penalizada['orden'].'</td>
                        <td>#'.$penalizada['id_penalizacion_registro'].'</td>
                        <td>'.$penalizada['apellidos'].', '.$penalizada['nombre'].'</td>
                        <td>'.$penalizada['codigo'].' - '.$penalizada['resumen'].'</td>
                        <td>'.$penalizada['puntos'].' pts</td>
                        <td class="text-center">
                          <form class="form-borrar-penalizacion" action="puntuaciones_lista_figuras_code.php" method="POST" style="display:inline;">
                            <input type="hidden" name="id_penalizacion_registro" value="'.$penalizada['id_penalizacion_registro'].'">
                            <input type="hidden" name="id_fase" value="'.$id_fase.'">
                            <button type="submit" class="btn btn-danger btn-sm btn-borrar-penalizacion" name="penalizacion_borrar" value="1">
                              <i class="fas fa-trash"></i>
                            </button>
                          </form>
                        </td>
                      </tr>';
                }
            } else {
                $html .= '<tr><td colspan="6" class="text-center">No hay penalizaciones aplicadas en esta fase</td></tr>';
            }
            
            $html .= '</tbody>';
            
            echo json_encode(['success' => true, 'html' => $html]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
    }
    die;
}
