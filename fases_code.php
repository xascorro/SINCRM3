<?php
include('security.php');

// Añadir registro
if(isset($_POST['save_btn'])){
    $id_competicion = $_SESSION['id_competicion_usuario'];
    $id_categoria = mysqli_real_escape_string($connection, $_POST['id_categoria']);
    $orden = mysqli_real_escape_string($connection, $_POST['orden']);
    
    // Detectar si es figura o modalidad
    $id_figura = isset($_POST['id_figura']) ? mysqli_real_escape_string($connection, $_POST['id_figura']) : 'NULL';
    $id_modalidad = isset($_POST['id_modalidad']) ? mysqli_real_escape_string($connection, $_POST['id_modalidad']) : 'NULL';

    $query = "INSERT INTO fases (id_competicion, id_categoria, id_figura, id_modalidad, orden) 
              VALUES ('$id_competicion', '$id_categoria', $id_figura, $id_modalidad, '$orden')";
    
    if(mysqli_query($connection, $query)){
        $_SESSION['correcto'] = 'Fase añadida con éxito';
    } else {
        $_SESSION['estado'] = 'Error al añadir fase: ' . mysqli_error($connection);
    }
    header('Location: fases.php');
}

// Actualizar registro
if(isset($_POST['update_btn'])){
    $id = $_POST['edit_id'];
    $orden = mysqli_real_escape_string($connection, $_POST['edit_orden']);
    $id_categoria = mysqli_real_escape_string($connection, $_POST['id_categoria']);
    $id_figura = !empty($_POST['id_figura']) ? "'".mysqli_real_escape_string($connection, $_POST['id_figura'])."'" : "NULL";
    $id_modalidad = !empty($_POST['id_modalidad']) ? "'".mysqli_real_escape_string($connection, $_POST['id_modalidad'])."'" : "NULL";
    
    // Factores
    $f_chomu = mysqli_real_escape_string($connection, $_POST['edit_f_chomu'] ?? '1.0');
    $f_performance = mysqli_real_escape_string($connection, $_POST['edit_f_performance'] ?? '1.0');
    $f_transitions = mysqli_real_escape_string($connection, $_POST['edit_f_transitions'] ?? '1.0');
    $f_hybrid = mysqli_real_escape_string($connection, $_POST['edit_f_hybrid'] ?? '1.0');
    $f_acro = mysqli_real_escape_string($connection, $_POST['edit_f_acro'] ?? '1.0');
    $f_tre = mysqli_real_escape_string($connection, $_POST['edit_f_tre'] ?? '1.0');
    
    // Errores
    $error_xs = mysqli_real_escape_string($connection, $_POST['edit_error_xs'] ?? '-0.1');
    $error_ob = mysqli_real_escape_string($connection, $_POST['edit_error_ob'] ?? '-0.5');
    $error_xl = mysqli_real_escape_string($connection, $_POST['edit_error_xl'] ?? '-3.0');
    
    // Adicionales
    $hora_inicio = mysqli_real_escape_string($connection, $_POST['edit_hora_inicio_estimada'] ?? '');
    $elementos_cc = intval($_POST['edit_elementos_coach_card'] ?? 0);
    $corte = intval($_POST['edit_corte'] ?? 0);
    
    // Toggles
    $tecnico = isset($_POST['edit_tecnico']) ? 'si' : 'no';
    $puntuada = isset($_POST['edit_puntuada']) ? 'si' : 'no';
    $memorial = isset($_POST['edit_puntua_memorial']) ? 'si' : 'no';
    $sorteado = isset($_POST['edit_sorteado']) ? 'si' : 'no';

    $query = "UPDATE fases SET 
                orden = '$orden',
                id_categoria = '$id_categoria',
                id_figura = $id_figura,
                id_modalidad = $id_modalidad,
                f_chomu = '$f_chomu',
                f_performance = '$f_performance',
                f_transitions = '$f_transitions',
                f_hybrid = '$f_hybrid',
                f_acro = '$f_acro',
                f_tre = '$f_tre',
                error_xs = '$error_xs',
                error_ob = '$error_ob',
                error_xl = '$error_xl',
                hora_inicio_estimada = '$hora_inicio',
                elementos_coach_card = '$elementos_cc',
                corte = '$corte',
                tecnico = '$tecnico',
                puntuada = '$puntuada',
                puntua_memorial = '$memorial',
                sorteado = '$sorteado'
              WHERE id = '$id'";

    if(mysqli_query($connection, $query)){
        write_log("Fase #$id actualizada con todos sus parámetros técnicos", "SUCCESS");
        $_SESSION['correcto'] = 'Fase técnica actualizada con éxito';
    } else {
        $_SESSION['estado'] = 'Error al actualizar: ' . mysqli_error($connection);
    }
    header('Location: fases.php');
}

// Borrar registro
if(isset($_POST['delete_btn'])){
    $id = $_POST['delete_id'];
    if(mysqli_query($connection, "DELETE FROM fases WHERE id = '$id'")){
        $_SESSION['correcto'] = 'Fase eliminada';
    } else {
        $_SESSION['estado'] = 'No se puede eliminar una fase con rutinas vinculadas';
    }
    header('Location: fases.php');
}
?>
