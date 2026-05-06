<?php
include('security.php');
include('./lib/my_functions.php');

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

            // Marcar fase como sorteada
            $q_fase_upd = "UPDATE fases SET sorteado = 'si', corte = 1 WHERE id = '$id_fase'";
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
    header('Location: sorteo_figuras.php');
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
    header('Location: sorteo_figuras.php');
    exit();
}

header('Location: sorteo_figuras.php');
exit();
?>
