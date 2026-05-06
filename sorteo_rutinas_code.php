<?php
include('security.php');
include('./lib/my_functions.php');

if(isset($_POST['save_btn'])){
    // Obligatorio usar id_competicion_activa según requerimiento
    $id_competicion = $_SESSION['id_competicion_activa'];
    $fase_req = mysqli_real_escape_string($connection, $_POST['id_fase']);

    if(isset($_POST['desbloquear'])){
        $query = "UPDATE fases set sorteado='no' WHERE id='$fase_req'";
        mysqli_query($connection,$query);
        $order_by = " order by orden";
    }else{
        $order_by = " order by rand()";
    }

    // Eliminamos el filtro 'sorteado=no' para permitir re-sorteos explícitos
    if($fase_req == '0'){ 
        $query = "SELECT id FROM fases WHERE id_competicion='$id_competicion' ORDER by orden";
    }else{ 
        $query = "SELECT id FROM fases WHERE id='$fase_req' AND id_competicion='$id_competicion' ORDER by orden";
    }

    $result = mysqli_query($connection, $query);
    $procesados = 0;
    
    while ($fase = mysqli_fetch_array($result)){
        $procesados++;
        $orden_rand = 0;
        
        // Sorteamos rutinas de esta fase
        $q_rut = "SELECT id FROM rutinas WHERE orden >= '0' AND id_fase=".$fase['id']." $order_by";
        $res_rut = mysqli_query($connection, $q_rut);
        
        while ($rutina = mysqli_fetch_array($res_rut)){
            $orden_rand++;
            $id_r = $rutina['id'];
            $q_upd = "UPDATE rutinas SET orden='$orden_rand' WHERE id='$id_r'";
            mysqli_query($connection, $q_upd);
        }

        // Marcar fase como sorteada
        $q_fase_upd = "UPDATE fases SET sorteado='si' WHERE id=".$fase['id'];
        mysqli_query($connection, $q_fase_upd);
    }

    if($procesados > 0) {
        if(mysqli_error($connection) == ''){
            $_SESSION['correcto'] = 'Sorteo de rutinas realizado con éxito (' . $procesados . ' fases).';
            write_log("Sorteo de rutinas realizado para $procesados fases en la competición #$id_competicion", "SUCCESS");
        } else {
            $_SESSION['estado'] = 'Error, algo ha salido mal durante el sorteo <br>'.mysqli_error($connection);
            write_log("Error en sorteo de rutinas: " . mysqli_error($connection), "ERROR");
        }
    } else {
        $_SESSION['estado'] = 'No se ha realizado ningún sorteo. Compruebe que existan inscripciones en la Competición Activa.';
    }
    header('Location: sorteo_rutinas.php');
    exit();
    }

    if(isset($_POST['delete_btn'])){
    $id_competicion = $_SESSION['id_competicion_activa'];

    $q1 = "UPDATE rutinas SET orden = '0' WHERE orden > '0' AND id_competicion = '$id_competicion'";
    $q2 = "UPDATE fases SET sorteado = 'no' WHERE id_competicion = '$id_competicion'";

    if(mysqli_query($connection, $q1) && mysqli_query($connection, $q2)){
        $_SESSION['correcto'] = 'Sorteo de rutinas generado con éxito.';
        write_log("Sorteo de rutinas ANULADO para la competición #$id_competicion", "WARNING");
    } else {
        $_SESSION['estado'] = 'Error al anular: ' . mysqli_error($connection);
        write_log("Error al anular sorteo de rutinas: " . mysqli_error($connection), "ERROR");
    }
    header('Location: sorteo_rutinas.php');
    exit();
    }


header('Location: sorteo_rutinas.php');
exit();
?>
