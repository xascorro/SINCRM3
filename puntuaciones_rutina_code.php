<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('security.php');

$id_rutina = intval($_POST['id_rutina'] ?? $_GET['id_rutina'] ?? 0);
$id_fase = intval($_POST['id_fase'] ?? $_GET['id_fase'] ?? 0);
$id_competicion = intval($_POST['id_competicion'] ?? $_SESSION['id_competicion_activa'] ?? 0);
$reload = 'no';

// Quitar penalización
if (isset($_GET['id_penalizaciones_rutinas'])) {
    $id_pr = intval($_GET['id_penalizaciones_rutinas']);
    $query = "DELETE FROM penalizaciones_rutinas WHERE id = $id_pr";
    mysqli_query($connection, $query);
    $reload = 'si';
    header("Location: puntuaciones_rutina.php?id_rutina=$id_rutina&id_fase=$id_fase&reload=$reload");
    exit();
}

// Añadir penalización
if (isset($_POST['id_penalizacion']) && intval($_POST['id_penalizacion']) > 0) {
    $id_penalizacion = intval($_POST['id_penalizacion']);
    $query = "INSERT INTO penalizaciones_rutinas (id_rutina, id_penalizacion) VALUES ($id_rutina, $id_penalizacion)";
    mysqli_query($connection, $query);
}

// Calcular y guardar notas
if (isset($_POST['save_btn']) && $id_rutina > 0) {
    // 0. Cargar parámetros técnicos de la fase y competición
    $q_fase_info = "SELECT f.*, 
                           COALESCE(NULLIF(f.f_chomu, 0), rc.f_chomu, 1.0) as f_chomu,
                           COALESCE(NULLIF(f.f_performance, 0), rc.f_performance, 1.0) as f_performance,
                           COALESCE(NULLIF(f.f_transitions, 0), rc.f_transitions, 1.0) as f_transitions,
                           COALESCE(NULLIF(f.f_hybrid, 0), rc.f_hybrid, 1.0) as f_hybrid,
                           COALESCE(NULLIF(f.f_acro, 0), rc.f_acro, 1.0) as f_acro,
                           COALESCE(NULLIF(f.f_tre, 0), rc.f_tre, 1.0) as f_tre,
                           COALESCE(NULLIF(f.error_xs, 0), rc.error_xs, -0.1) as error_xs,
                           COALESCE(NULLIF(f.error_ob, 0), rc.error_ob, -0.5) as error_ob,
                           COALESCE(NULLIF(f.error_xl, 0), rc.error_xl, -3.0) as error_xl
                    FROM fases f 
                    JOIN rutinas r ON f.id = r.id_fase 
                    LEFT JOIN reglas_competicion rc ON (f.id_categoria = rc.id_categoria AND f.id_modalidad = rc.id_modalidad)
                    WHERE r.id = $id_rutina";
    $fase_info = mysqli_fetch_assoc(mysqli_query($connection, $q_fase_info));
    if (!$id_fase && isset($fase_info['id'])) {
        $id_fase = intval($fase_info['id']);
    }

    // 1. PANEL 1: ELEMENTOS
    // Obtener jueces configurados para el panel de elementos
    $q_jueces1 = "SELECT id, numero_juez FROM panel_jueces 
                  WHERE id_fase = $id_fase 
                    AND id_panel IN (SELECT id FROM paneles WHERE id_paneles_tipo = 1 AND id_competicion = $id_competicion) 
                  ORDER BY numero_juez ASC";
    $res_jueces1 = mysqli_query($connection, $q_jueces1);
    $jueces1 = [];
    while ($j = mysqli_fetch_assoc($res_jueces1)) {
        $jueces1[] = $j;
    }

    // Limpiar puntuaciones previas de elementos de esta rutina
    mysqli_query($connection, "DELETE FROM puntuaciones_jueces WHERE id_rutina = $id_rutina AND tipo_ia IS NULL");
    mysqli_query($connection, "DELETE FROM puntuaciones_elementos WHERE id_rutina = $id_rutina AND tipo_ia IS NULL");

    // Obtener todos los elementos reales de la rutina
    $q_elem = "SELECT h.elemento, h.tipo, h.texto, t.nombre 
               FROM hibridos_rutina h 
               JOIN tipo_hibridos t ON h.texto = t.id 
               WHERE h.id_rutina = '$id_rutina' AND h.tipo = 'part' AND h.texto != '3' 
               ORDER BY h.elemento ASC";
    $res_elem = mysqli_query($connection, $q_elem);

    while ($el = mysqli_fetch_assoc($res_elem)) {
        $elem = intval($el['elemento']);

        // Insertar notas de cada juez para este elemento
        foreach ($jueces1 as $j) {
            $num_j = $j['numero_juez'];
            $pj_id = intval($j['id']);
            $input_key = "notaE{$elem}J{$num_j}";

            if (isset($_POST[$input_key]) && trim($_POST[$input_key]) !== '') {
                $nota_clean = floatval(str_replace(',', '.', trim($_POST[$input_key])));
                mysqli_query($connection, "INSERT INTO puntuaciones_jueces (id_panel_juez, id_rutina, id_elemento, nota) VALUES ($pj_id, $id_rutina, $elem, $nota_clean)");
            }
        }

        // Descarte de nota mayor y menor
        $q_mm = "SELECT MIN(nota) as min_n, MAX(nota) as max_n, COUNT(nota) as cnt 
                 FROM puntuaciones_jueces 
                 WHERE id_rutina = $id_rutina AND id_elemento = $elem AND tipo_ia IS NULL";
        $r_mm = mysqli_fetch_assoc(mysqli_query($connection, $q_mm));
        $min_nota = $r_mm['min_n'];
        $max_nota = $r_mm['max_n'];
        $count_notas = intval($r_mm['cnt']);

        if ($count_notas >= 3 && $min_nota !== null && $max_nota !== null) {
            // Descartar una nota menor
            mysqli_query($connection, "UPDATE puntuaciones_jueces SET nota_menor = 'si' WHERE nota = $min_nota AND id_rutina = $id_rutina AND id_elemento = $elem AND tipo_ia IS NULL LIMIT 1");
            // Descartar una nota mayor (asegurando no descartar dos veces el mismo registro si todas las notas son idénticas)
            mysqli_query($connection, "UPDATE puntuaciones_jueces SET nota_mayor = 'si' WHERE nota = $max_nota AND id_rutina = $id_rutina AND id_elemento = $elem AND tipo_ia IS NULL AND (nota_menor IS NULL OR nota_menor != 'si') LIMIT 1");
        }

        // Determinar DD o Basemark
        $is_bm = isset($_POST["BM{$elem}"]);
        $llevado_BM = $is_bm ? 'si' : 'no';

        if ($is_bm) {
            $dd_aplicado = floatval(str_replace(',', '.', $_POST["BM{$elem}"]));
        } else {
            $dd_aplicado = floatval(str_replace(',', '.', $_POST["dd{$elem}"] ?? 0));
        }

        $factor_aplicado = floatval(str_replace(',', '.', $_POST["factor{$elem}"] ?? 1.0));

        // Cálculo de nota media recortada (excluyendo min y max)
        if ($count_notas > 2) {
            $q_avg = "SELECT (SUM(nota) - $min_nota - $max_nota) / ($count_notas - 2) as media 
                      FROM puntuaciones_jueces 
                      WHERE id_rutina = $id_rutina AND id_elemento = $elem AND tipo_ia IS NULL";
            $nota_media = round(floatval(mysqli_fetch_assoc(mysqli_query($connection, $q_avg))['media'] ?? 0), 4);
        } elseif ($count_notas > 0) {
            $q_avg = "SELECT AVG(nota) as media 
                      FROM puntuaciones_jueces 
                      WHERE id_rutina = $id_rutina AND id_elemento = $elem AND tipo_ia IS NULL";
            $nota_media = round(floatval(mysqli_fetch_assoc(mysqli_query($connection, $q_avg))['media'] ?? 0), 4);
        } else {
            $nota_media = 0.0000;
        }

        $nota_elemento = round($nota_media * $dd_aplicado * $factor_aplicado, 4);

        mysqli_query($connection, "INSERT INTO puntuaciones_elementos (id_rutina, elemento, tipo_ia, nota_media, factor, nota, llevado_BM) 
                                   VALUES ($id_rutina, $elem, NULL, $nota_media, $factor_aplicado, $nota_elemento, '$llevado_BM')");
    }

    // 2. ERRORES DE SINCRONIZACIÓN
    mysqli_query($connection, "DELETE FROM errores_sincronizacion WHERE id_rutina = $id_rutina");

    $errores_pequenos = intval($_POST['errores_pequenos'] ?? 0);
    $errores_obvios = intval($_POST['errores_obvios'] ?? 0);
    $errores_mayores = intval($_POST['errores_mayores'] ?? 0);

    $error_xs = floatval($fase_info['error_xs'] ?? -0.1);
    $error_ob = floatval($fase_info['error_ob'] ?? -0.5);
    $error_xl = floatval($fase_info['error_xl'] ?? -3.0);

    // Garantizar signo negativo para las deducciones
    if ($error_xs > 0) $error_xs = -$error_xs;
    if ($error_ob > 0) $error_ob = -$error_ob;
    if ($error_xl > 0) $error_xl = -$error_xl;

    $nota_errores_sincronizacion = round(($errores_pequenos * $error_xs) + ($errores_obvios * $error_ob) + ($errores_mayores * $error_xl), 4);
    mysqli_query($connection, "INSERT INTO errores_sincronizacion (id_rutina, errores_pequenos, errores_obvios, errores_mayores, nota) 
                               VALUES ($id_rutina, $errores_pequenos, $errores_obvios, $errores_mayores, $nota_errores_sincronizacion)");

    // 3. PANEL 2: IMPRESIÓN ARTÍSTICA (ChoMu, Performance, Transitions)
    $q_jueces2 = "SELECT id, numero_juez FROM panel_jueces 
                  WHERE id_fase = $id_fase 
                    AND id_panel IN (SELECT id FROM paneles WHERE id_paneles_tipo = 2 AND id_competicion = $id_competicion) 
                  ORDER BY numero_juez ASC";
    $res_jueces2 = mysqli_query($connection, $q_jueces2);
    $jueces2 = [];
    while ($j = mysqli_fetch_assoc($res_jueces2)) {
        $jueces2[] = $j;
    }

    $componentes_ia = [
        'ChoMu' => floatval(str_replace(',', '.', $_POST['f_chomu'] ?? $_POST['factor_ChoMu'] ?? $fase_info['f_chomu'] ?? 1.0)),
        'Performance' => floatval(str_replace(',', '.', $_POST['factor_Performance'] ?? $fase_info['f_performance'] ?? 1.0)),
        'Transitions' => floatval(str_replace(',', '.', $_POST['factor_Transitions'] ?? $fase_info['f_transitions'] ?? 1.0))
    ];

    foreach ($componentes_ia as $tipo_ia => $factor_ia) {
        // Limpiar registros previos de este componente
        mysqli_query($connection, "DELETE FROM puntuaciones_jueces WHERE id_rutina = $id_rutina AND tipo_ia = '$tipo_ia'");
        mysqli_query($connection, "DELETE FROM puntuaciones_elementos WHERE id_rutina = $id_rutina AND tipo_ia = '$tipo_ia'");

        // Insertar notas de jueces
        foreach ($jueces2 as $j) {
            $num_j = $j['numero_juez'];
            $pj_id = intval($j['id']);
            $input_key = "nota{$tipo_ia}J{$num_j}";

            if (isset($_POST[$input_key]) && trim($_POST[$input_key]) !== '') {
                $nota_clean = floatval(str_replace(',', '.', trim($_POST[$input_key])));
                mysqli_query($connection, "INSERT INTO puntuaciones_jueces (id_panel_juez, id_rutina, id_elemento, tipo_ia, nota) 
                                           VALUES ($pj_id, $id_rutina, NULL, '$tipo_ia', $nota_clean)");
            }
        }

        // Descarte de min y max en IA
        $q_mm_ia = "SELECT MIN(nota) as min_n, MAX(nota) as max_n, COUNT(nota) as cnt 
                    FROM puntuaciones_jueces 
                    WHERE id_rutina = $id_rutina AND tipo_ia = '$tipo_ia'";
        $r_mm_ia = mysqli_fetch_assoc(mysqli_query($connection, $q_mm_ia));
        $min_nota_ia = $r_mm_ia['min_n'];
        $max_nota_ia = $r_mm_ia['max_n'];
        $count_ia = intval($r_mm_ia['cnt']);

        if ($count_ia >= 3 && $min_nota_ia !== null && $max_nota_ia !== null) {
            mysqli_query($connection, "UPDATE puntuaciones_jueces SET nota_menor = 'si' 
                                       WHERE nota = $min_nota_ia AND id_rutina = $id_rutina AND tipo_ia = '$tipo_ia' LIMIT 1");
            mysqli_query($connection, "UPDATE puntuaciones_jueces SET nota_mayor = 'si' 
                                       WHERE nota = $max_nota_ia AND id_rutina = $id_rutina AND tipo_ia = '$tipo_ia' AND (nota_menor IS NULL OR nota_menor != 'si') LIMIT 1");
        }

        // Puntuación del componente IA: suma de las 3 notas centrales * factor
        if ($count_ia > 2) {
            $q_ia_calc = "SELECT SUM(nota) as sum_central 
                          FROM puntuaciones_jueces 
                          WHERE id_rutina = $id_rutina AND tipo_ia = '$tipo_ia' 
                            AND (nota_menor IS NULL OR nota_menor != 'si') 
                            AND (nota_mayor IS NULL OR nota_mayor != 'si')";
            $sum_central = floatval(mysqli_fetch_assoc(mysqli_query($connection, $q_ia_calc))['sum_central'] ?? 0);
            $media_ia = round($sum_central / 3, 4);
        } elseif ($count_ia > 0) {
            $q_ia_calc = "SELECT SUM(nota) as sum_central, AVG(nota) as media 
                          FROM puntuaciones_jueces 
                          WHERE id_rutina = $id_rutina AND tipo_ia = '$tipo_ia'";
            $row_ia = mysqli_fetch_assoc(mysqli_query($connection, $q_ia_calc));
            $sum_central = floatval($row_ia['sum_central'] ?? 0);
            $media_ia = round(floatval($row_ia['media'] ?? 0), 4);
        } else {
            $sum_central = 0;
            $media_ia = 0;
        }

        $nota_ia_componente = round($sum_central * $factor_ia, 4);

        mysqli_query($connection, "INSERT INTO puntuaciones_elementos (id_rutina, elemento, tipo_ia, nota_media, factor, nota, llevado_BM) 
                                   VALUES ($id_rutina, NULL, '$tipo_ia', $media_ia, $factor_ia, $nota_ia_componente, 'no')");
    }

    // 4. PENALIZACIONES
    // Penalizaciones de Elementos (Panel 1)
    $q_pen_elem = "SELECT SUM(puntos) as sum_puntos FROM penalizaciones p 
                   JOIN penalizaciones_rutinas pr ON pr.id_penalizacion = p.id 
                   WHERE pr.id_rutina = $id_rutina AND p.id_paneles_tipo = 1";
    $penalizaciones_elementos = floatval(mysqli_fetch_assoc(mysqli_query($connection, $q_pen_elem))['sum_puntos'] ?? 0);
    if ($penalizaciones_elementos > 0) $penalizaciones_elementos = -$penalizaciones_elementos;

    // Penalizaciones de Impresión Artística (Panel 2)
    $q_pen_ia = "SELECT SUM(puntos) as sum_puntos FROM penalizaciones p 
                 JOIN penalizaciones_rutinas pr ON pr.id_penalizacion = p.id 
                 WHERE pr.id_rutina = $id_rutina AND p.id_paneles_tipo = 2";
    $penalizaciones_ia = floatval(mysqli_fetch_assoc(mysqli_query($connection, $q_pen_ia))['sum_puntos'] ?? 0);
    if ($penalizaciones_ia > 0) $penalizaciones_ia = -$penalizaciones_ia;

    // Penalizaciones de Rutina (Árbitro / Panel 0)
    $q_pen_rutina = "SELECT SUM(puntos) as sum_puntos FROM penalizaciones p 
                     JOIN penalizaciones_rutinas pr ON pr.id_penalizacion = p.id 
                     WHERE pr.id_rutina = $id_rutina AND (p.id_paneles_tipo = 0 OR p.id_paneles_tipo IS NULL)";
    $penalizaciones_rutina = floatval(mysqli_fetch_assoc(mysqli_query($connection, $q_pen_rutina))['sum_puntos'] ?? 0);
    if ($penalizaciones_rutina > 0) $penalizaciones_rutina = -$penalizaciones_rutina;

    // 5. CÁLCULO DE TOTALES
    // Panel Elementos
    $q_sum_elem = "SELECT SUM(nota) as sum_nota FROM puntuaciones_elementos WHERE id_rutina = $id_rutina AND tipo_ia IS NULL";
    $nota_panel_elementos = round(floatval(mysqli_fetch_assoc(mysqli_query($connection, $q_sum_elem))['sum_nota'] ?? 0), 4);
    $nota_panel_sincro = $nota_errores_sincronizacion;
    $nota_final_panel_elementos = max(0, round($nota_panel_elementos + $nota_panel_sincro + $penalizaciones_elementos, 4));

    // Panel Impresión Artística
    $q_sum_ia = "SELECT SUM(nota) as sum_nota FROM puntuaciones_elementos WHERE id_rutina = $id_rutina AND tipo_ia IS NOT NULL";
    $nota_panel_ia = round(floatval(mysqli_fetch_assoc(mysqli_query($connection, $q_sum_ia))['sum_nota'] ?? 0), 4);
    $nota_final_panel_ia = max(0, round($nota_panel_ia + $penalizaciones_ia, 4));

    // Rutina Total
    $nota_rutina = round($nota_final_panel_elementos + $nota_final_panel_ia, 4);
    $nota_final = max(0, round($nota_rutina + $penalizaciones_rutina, 4));

    // Actualizar rutina
    $q_upd_rutina = "UPDATE rutinas SET 
                        nota_panel_elementos = '$nota_panel_elementos', 
                        nota_panel_sincro = '$nota_panel_sincro', 
                        penalizaciones_elementos = '$penalizaciones_elementos', 
                        nota_final_panel_elementos = '$nota_final_panel_elementos', 
                        nota_panel_ia = '$nota_panel_ia', 
                        penalizaciones_ia = '$penalizaciones_ia', 
                        nota_final_panel_ia = '$nota_final_panel_ia', 
                        nota_rutina = '$nota_rutina', 
                        penalizaciones_rutina = '$penalizaciones_rutina', 
                        nota_final = '$nota_final' 
                     WHERE id = $id_rutina";
    mysqli_query($connection, $q_upd_rutina);

    // 6. ACTUALIZACIÓN AUTOMÁTICA DE CLASIFICACIÓN Y DIFERENCIAS EN LA FASE
    $q_fase_rutinas = "SELECT id, nota_final, orden, baja FROM rutinas 
                       WHERE id_fase = $id_fase AND baja = 'no' AND orden >= 1 
                       ORDER BY nota_final DESC, id ASC";
    $res_fase_rutinas = mysqli_query($connection, $q_fase_rutinas);
    $pos = 1;
    $nota_top = null;
    while ($r_fase = mysqli_fetch_assoc($res_fase_rutinas)) {
        if ($pos === 1) {
            $nota_top = floatval($r_fase['nota_final']);
        }
        $dif = ($nota_top !== null) ? max(0, round($nota_top - floatval($r_fase['nota_final']), 4)) : 0;
        $dif_str = number_format($dif, 4, '.', '');
        mysqli_query($connection, "UPDATE rutinas SET posicion = '$pos', diferencia = '$dif_str' WHERE id = " . intval($r_fase['id']));
        $pos++;
    }

    // Rutinas con baja o especiales
    mysqli_query($connection, "UPDATE rutinas SET posicion = '100', diferencia = NULL WHERE id_fase = $id_fase AND (baja = 'si' OR orden < 1)");

    if (mysqli_error($connection) == '') {
        $_SESSION['correcto'] = 'Puntuaciones guardadas y calculadas correctamente.';
    } else {
        $_SESSION['estado'] = 'Error al guardar las notas: ' . mysqli_error($connection);
    }
}

header("Location: puntuaciones_rutina.php?id_rutina=$id_rutina&id_fase=$id_fase&reload=$reload");
exit();
