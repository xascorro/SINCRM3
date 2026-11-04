<?php
include('security.php');

// Añadir registro
if(isset($_POST['save_btn'])){
    $id_competicion = $_SESSION['id_competicion_usuario'];
    $id_categoria = mysqli_real_escape_string($connection, $_POST['id_categoria']);
    $orden = mysqli_real_escape_string($connection, $_POST['orden']);
    
    // Detectar si es figura o modalidad
    $tipo_fase = $_POST['tipo_fase'] ?? '';
    $genero = (!empty($_POST['genero']) && strtolower($_POST['genero']) === 'masculino') ? 'masculino' : 'femenino';
    if ($tipo_fase === 'figura') {
        $id_figura = !empty($_POST['id_figura']) ? "'".mysqli_real_escape_string($connection, $_POST['id_figura'])."'" : "NULL";
        $id_modalidad = "'8'"; // ID 8 corresponde a Figuras en la tabla modalidades
    } elseif ($tipo_fase === 'rutina') {
        $id_figura = "NULL";
        $id_modalidad = !empty($_POST['id_modalidad']) ? "'".mysqli_real_escape_string($connection, $_POST['id_modalidad'])."'" : "NULL";
    } else {
        $id_figura = !empty($_POST['id_figura']) ? "'".mysqli_real_escape_string($connection, $_POST['id_figura'])."'" : "NULL";
        $id_modalidad = !empty($_POST['id_modalidad']) ? "'".mysqli_real_escape_string($connection, $_POST['id_modalidad'])."'" : "NULL";
    }

    // Herencia automática del reglamento maestro (reglas_competicion)
    $f_chomu = 1.0; $f_performance = 1.0; $f_transitions = 1.0;
    $f_hybrid = 1.0; $f_acro = 1.0; $f_tre = 1.0;
    $error_xs = -0.1; $error_ob = -0.5; $error_xl = -3.0;
    $duracion_musica = "NULL"; $margen_musica = "NULL";
    $elementos_cc = 0; $tecnico = 'no';

    if ($id_modalidad !== "NULL" && $id_modalidad !== "'8'") {
        $mod_clean = str_replace("'", "", $id_modalidad);
        $q_regla = "SELECT * FROM reglas_competicion WHERE id_categoria = '$id_categoria' AND id_modalidad = '$mod_clean' LIMIT 1";
        $res_regla = mysqli_query($connection, $q_regla);
        if ($res_regla && $r_regla = mysqli_fetch_assoc($res_regla)) {
            $f_chomu = $r_regla['f_chomu'] ?? 1.0;
            $f_performance = $r_regla['f_performance'] ?? 1.0;
            $f_transitions = $r_regla['f_transitions'] ?? 1.0;
            $f_hybrid = $r_regla['f_hybrid'] ?? 1.0;
            $f_acro = $r_regla['f_acro'] ?? 1.0;
            $f_tre = $r_regla['f_tre'] ?? 1.0;
            $error_xs = $r_regla['error_xs'] ?? -0.1;
            $error_ob = $r_regla['error_ob'] ?? -0.5;
            $error_xl = $r_regla['error_xl'] ?? -3.0;
            if (!empty($r_regla['duracion_musica'])) $duracion_musica = "'" . intval($r_regla['duracion_musica']) . "'";
            if (!empty($r_regla['margen_musica'])) $margen_musica = "'" . intval($r_regla['margen_musica']) . "'";
            $elementos_cc = (!empty($r_regla['elementos_coach_card'])) 
                ? intval($r_regla['elementos_coach_card']) 
                : intval(($r_regla['max_hibridos'] ?? 0) + ($r_regla['max_acrobacias'] ?? 0) + ($r_regla['max_tre'] ?? 0) + ($r_regla['req_hibrido_creativo'] ?? 0));
            if (intval($r_regla['max_tre'] ?? 0) > 0) $tecnico = 'si';
        }
    }

    $query = "INSERT INTO fases (id_competicion, id_categoria, id_figura, id_modalidad, genero, orden, 
                                f_chomu, f_performance, f_transitions, f_hybrid, f_acro, f_tre, 
                                error_xs, error_ob, error_xl, duracion_musica, margen_musica, 
                                elementos_coach_card, tecnico) 
              VALUES ('$id_competicion', '$id_categoria', $id_figura, $id_modalidad, '$genero', '$orden',
                      '$f_chomu', '$f_performance', '$f_transitions', '$f_hybrid', '$f_acro', '$f_tre',
                      '$error_xs', '$error_ob', '$error_xl', $duracion_musica, $margen_musica,
                      '$elementos_cc', '$tecnico')";
    
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
    $genero = (!empty($_POST['edit_genero']) && strtolower($_POST['edit_genero']) === 'masculino') ? 'masculino' : 'femenino';
    
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
    $obsoleto = (isset($_POST['edit_aqua']) && $_POST['edit_aqua'] == 'si') ? 'no' : 'si';
    $hora_inicio = mysqli_real_escape_string($connection, $_POST['edit_hora_inicio_estimada'] ?? '');
    $elementos_cc = intval($_POST['edit_elementos_coach_card'] ?? 0);
    $corte = intval($_POST['edit_corte'] ?? 0);

    // Duración de música personalizada para esta fase
    $duracion_musica_raw = trim($_POST['edit_duracion_musica'] ?? '');
    if ($duracion_musica_raw !== '') {
        if (strpos($duracion_musica_raw, ':') !== false) {
            $dur_parts = explode(':', $duracion_musica_raw);
            $dur_sec = (intval($dur_parts[0]) * 60) + intval($dur_parts[1] ?? 0);
        } else {
            $dur_sec = intval($duracion_musica_raw);
        }
        $duracion_musica_sql = ($dur_sec > 0) ? "'$dur_sec'" : "NULL";
    } else {
        $duracion_musica_sql = "NULL";
    }

    $margen_musica_raw = trim($_POST['edit_margen_musica'] ?? '');
    $margen_musica_sql = ($margen_musica_raw !== '') ? "'" . intval($margen_musica_raw) . "'" : "NULL";
    
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
                genero = '$genero',
                f_chomu = '$f_chomu',
                f_performance = '$f_performance',
                f_transitions = '$f_transitions',
                f_hybrid = '$f_hybrid',
                f_acro = '$f_acro',
                f_tre = '$f_tre',
                error_xs = '$error_xs',
                error_ob = '$error_ob',
                error_xl = '$error_xl',
                obsoleto = '$obsoleto',
                hora_inicio_estimada = '$hora_inicio',
                elementos_coach_card = '$elementos_cc',
                duracion_musica = $duracion_musica_sql,
                margen_musica = $margen_musica_sql,
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
