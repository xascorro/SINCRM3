<?php
include('security.php');
include('./lib/my_functions.php');

if(isset($_POST['sorteo_completo_btn'])){
    $id_competicion = $_SESSION['id_competicion_activa'] ?? ($_SESSION['id_competicion_usuario'] ?? 0);
    $redondeo = $_POST['redondeo'] ?? 'ceil';
    $total_figuras_sorteadas = 0;
    $total_rutinas_sorteadas = 0;

    // 1. Sorteo de todas las categorías de Figuras
    $q_cats = "SELECT DISTINCT f.id_categoria FROM fases f WHERE f.id_competicion = '$id_competicion' AND f.id_figura > 0";
    $res_cats = mysqli_query($connection, $q_cats);

    while($row_cat = mysqli_fetch_assoc($res_cats)) {
        $id_cat = $row_cat['id_categoria'];
        $q_fases = "SELECT id FROM fases WHERE id_competicion = '$id_competicion' AND id_categoria = '$id_cat' AND id_figura > 0 ORDER BY orden ASC";
        $res_fases = mysqli_query($connection, $q_fases);
        $fases = [];
        while($f = mysqli_fetch_assoc($res_fases)) $fases[] = $f['id'];
        $num_fases = count($fases);
        if($num_fases == 0) continue;

        $q_nads = "SELECT DISTINCT id_nadadora FROM inscripciones_figuras 
                   WHERE id_fase IN (".implode(',', $fases).") 
                   AND (orden >= 0 OR orden IS NULL)";
        $res_nads = mysqli_query($connection, $q_nads);
        $nadadoras_sorteo = [];
        while($n = mysqli_fetch_assoc($res_nads)) $nadadoras_sorteo[] = $n['id_nadadora'];
        $num_nads_sorteo = count($nadadoras_sorteo);
        if($num_nads_sorteo == 0) continue;

        $total_figuras_sorteadas += $num_fases;
        shuffle($nadadoras_sorteo);

        $salto = ($redondeo == "floor") ? floor($num_nads_sorteo / $num_fases) : ceil($num_nads_sorteo / $num_fases);
        if($salto < 1) $salto = 1;

        foreach($fases as $index_fase => $id_fase) {
            $desplazamiento = $index_fase * $salto;
            foreach($nadadoras_sorteo as $index_nadadora => $id_nadadora) {
                $posicion_relativa = ($index_nadadora - $desplazamiento) % $num_nads_sorteo;
                if($posicion_relativa < 0) $posicion_relativa += $num_nads_sorteo;
                $nuevo_orden = $posicion_relativa + 1;
                $q_upd = "UPDATE inscripciones_figuras SET orden = '$nuevo_orden' WHERE id_fase = '$id_fase' AND id_nadadora = '$id_nadadora'";
                mysqli_query($connection, $q_upd);
            }
            $corte_inicio = 1 + ($index_fase * $salto);
            mysqli_query($connection, "UPDATE fases SET sorteado = 'si', corte = '$corte_inicio' WHERE id = '$id_fase'");
        }
    }

    // 2. Sorteo de todas las fases de Rutinas
    $q_fases_rut = "SELECT id FROM fases WHERE id_competicion = '$id_competicion' AND id_modalidad > 0 ORDER BY orden ASC";
    $res_fases_rut = mysqli_query($connection, $q_fases_rut);
    while($fase_r = mysqli_fetch_assoc($res_fases_rut)) {
        $id_fase_r = $fase_r['id'];
        $orden_rand = 0;
        $q_rut = "SELECT id FROM rutinas WHERE orden >= 0 AND id_fase = '$id_fase_r' ORDER BY RAND()";
        $res_rut = mysqli_query($connection, $q_rut);
        $has_rutinas = false;
        while($rutina = mysqli_fetch_assoc($res_rut)) {
            $has_rutinas = true;
            $orden_rand++;
            mysqli_query($connection, "UPDATE rutinas SET orden = '$orden_rand' WHERE id = '{$rutina['id']}'");
        }
        if($has_rutinas) $total_rutinas_sorteadas++;
        mysqli_query($connection, "UPDATE fases SET sorteado = 'si' WHERE id = '$id_fase_r'");
    }

    $_SESSION['correcto'] = "Sorteo integral realizado con éxito: $total_figuras_sorteadas figuras y $total_rutinas_sorteadas fases de rutinas sorteadas.";
    write_log("Sorteo integral completado para la competición #$id_competicion ($total_figuras_sorteadas figuras, $total_rutinas_sorteadas rutinas)", "SUCCESS");
    header('Location: sorteo.php');
    exit();
}

if(isset($_POST['delete_completo_btn'])){
    $id_competicion = $_SESSION['id_competicion_activa'] ?? ($_SESSION['id_competicion_usuario'] ?? 0);
    mysqli_query($connection, "UPDATE inscripciones_figuras SET orden = 0 WHERE id_competicion = '$id_competicion' AND (orden >= 0 OR orden IS NULL)");
    mysqli_query($connection, "UPDATE rutinas SET orden = 0 WHERE orden > 0 AND id_competicion = '$id_competicion'");
    mysqli_query($connection, "UPDATE fases SET sorteado = 'no', corte = 0 WHERE id_competicion = '$id_competicion'");
    $_SESSION['correcto'] = 'Se han anulado todos los sorteos de la competición (Figuras y Rutinas).';
    write_log("Sorteo integral ANULADO para la competición #$id_competicion", "WARNING");
    header('Location: sorteo.php');
    exit();
}

if(isset($_POST['save_btn'])){
    // Obligatorio usar id_competicion_activa según requerimiento
    $id_competicion = $_SESSION['id_competicion_activa'];
    $id_categoria_req = mysqli_real_escape_string($connection, $_POST['id_categoria']);
    $redondeo = $_POST['redondeo'] ?? 'ceil';

    // 1. Obtener las categorías a procesar
    if($id_categoria_req == '0'){ 
        $q_cats = "SELECT DISTINCT id_categoria FROM fases WHERE id_competicion = '$id_competicion'";
    } else {
        $q_cats = "SELECT DISTINCT id_categoria FROM fases WHERE id_competicion = '$id_competicion' AND id_categoria = '$id_categoria_req'";
    }
    $res_cats = mysqli_query($connection, $q_cats);

    $procesados = 0;
    while($row_cat = mysqli_fetch_assoc($res_cats)) {
        $id_cat = $row_cat['id_categoria'];

        // 2. Obtener las fases (figuras) de esta categoría
        // Eliminamos el filtro 'sorteado=no' porque el usuario ya confirma el re-sorteo en el front-end
        $q_fases = "SELECT id FROM fases WHERE id_competicion = '$id_competicion' AND id_categoria = '$id_cat' ORDER BY orden ASC";
        
        $res_fases = mysqli_query($connection, $q_fases);
        $fases = [];
        while($f = mysqli_fetch_assoc($res_fases)) $fases[] = $f['id'];
        $num_fases = count($fases);

        if($num_fases == 0) continue;

        // 3. Obtener listado de nadadoras a sortear
        $q_nads = "SELECT DISTINCT id_nadadora FROM inscripciones_figuras 
                   WHERE id_fase IN (".implode(',', $fases).") 
                   AND (orden >= 0 OR orden IS NULL)";
        $res_nads = mysqli_query($connection, $q_nads);
        $nadadoras_sorteo = [];
        while($n = mysqli_fetch_assoc($res_nads)) $nadadoras_sorteo[] = $n['id_nadadora'];
        
        $num_nads_sorteo = count($nadadoras_sorteo);
        if($num_nads_sorteo == 0) continue;

        // Marcamos como procesado
        $procesados += $num_fases;

        // 4. Randomizar nadadoras
        shuffle($nadadoras_sorteo);

        // 5. Calcular el "salto"
        if ($redondeo == "floor"){
            $salto = floor($num_nads_sorteo / $num_fases);
        } else {
            $salto = ceil($num_nads_sorteo / $num_fases);
        }
        if($salto < 1) $salto = 1;

        // 6. Asignar órdenes desplazados para cada figura
        foreach($fases as $index_fase => $id_fase) {
            $desplazamiento = $index_fase * $salto;
            
            foreach($nadadoras_sorteo as $index_nadadora => $id_nadadora) {
                $posicion_relativa = ($index_nadadora - $desplazamiento) % $num_nads_sorteo;
                if($posicion_relativa < 0) $posicion_relativa += $num_nads_sorteo;
                
                $nuevo_orden = $posicion_relativa + 1;

                $q_upd = "UPDATE inscripciones_figuras SET orden = '$nuevo_orden' 
                          WHERE id_fase = '$id_fase' AND id_nadadora = '$id_nadadora'";
                mysqli_query($connection, $q_upd);
            }

            // Marcar fase como sorteada con el orden de inicio del corte
            $corte_inicio = 1 + ($index_fase * $salto);
            $q_fase_upd = "UPDATE fases SET sorteado = 'si', corte = '$corte_inicio' WHERE id = '$id_fase'";
            mysqli_query($connection, $q_fase_upd);
        }
    }

    if($procesados > 0) {
        if(mysqli_error($connection) == ''){
            $_SESSION['correcto'] = 'Sorteo realizado con éxito para ' . $procesados . ' figuras.';
            write_log("Sorteo de figuras realizado para $procesados figuras en la competición #$id_competicion", "SUCCESS");
        } else {
            $_SESSION['estado'] = 'Error durante el sorteo: ' . mysqli_error($connection);
            write_log("Error en sorteo de figuras: " . mysqli_error($connection), "ERROR");
        }
    } else {
        $_SESSION['estado'] = 'No se ha realizado ningún sorteo. Compruebe que existan inscripciones en la Competición Activa.';
    }
    header('Location: sorteo.php?tab=figuras');
    exit();
}

if(isset($_POST['delete_btn'])){
    $id_competicion = $_SESSION['id_competicion_activa'];
    
    $q1 = "UPDATE inscripciones_figuras SET orden = 0 
           WHERE id_competicion = '$id_competicion' 
           AND (orden >= 0 OR orden IS NULL)";
    
    $q2 = "UPDATE fases SET sorteado = 'no', corte = 0 WHERE id_competicion = '$id_competicion'";
    
    if(mysqli_query($connection, $q1) && mysqli_query($connection, $q2)){
        $_SESSION['correcto'] = 'Sorteo de figuras anulado con éxito.';
        write_log("Sorteo de figuras ANULADO para la competición #$id_competicion", "WARNING");
    } else {
        $_SESSION['estado'] = 'Error al anular: ' . mysqli_error($connection);
        write_log("Error al anular sorteo de figuras: " . mysqli_error($connection), "ERROR");
    }
    header('Location: sorteo.php?tab=figuras');
    exit();
}

header('Location: sorteo.php?tab=figuras');
exit();
?>
