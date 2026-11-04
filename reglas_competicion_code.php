<?php
include('security.php');

// Helper para parsear mm:ss o segundos a entero
function parseTimeSeconds($str) {
    if ($str === null || $str === '') return null;
    $str = trim($str);
    if (strpos($str, ':') !== false) {
        $parts = explode(':', $str);
        return (intval($parts[0]) * 60) + intval($parts[1] ?? 0);
    }
    return intval($str);
}

// 1. ACTUALIZAR REGLA
if(isset($_POST['update_btn'])){
    $id_regla = intval($_POST['edit_id']);
    $max_tre = intval($_POST['max_tre'] ?? 0);
    $max_hibridos = intval($_POST['max_hibridos'] ?? 0);
    $max_acrobacias = intval($_POST['max_acrobacias'] ?? 0);
    $req_hibrido_creativo = intval($_POST['req_hibrido_creativo'] ?? 0);
    $elementos_coach_card = (isset($_POST['elementos_coach_card']) && $_POST['elementos_coach_card'] !== '') 
        ? intval($_POST['elementos_coach_card']) 
        : ($max_tre + $max_hibridos + $max_acrobacias + $req_hibrido_creativo);
    $max_apnea = !empty($_POST['max_apnea']) ? floatval($_POST['max_apnea']) : 'NULL';
    
    $dur_raw = $_POST['duracion_musica'] ?? '';
    $dur_sec = parseTimeSeconds($dur_raw);
    $dur_sql = ($dur_sec !== null && $dur_sec > 0) ? intval($dur_sec) : 'NULL';

    $margen_raw = $_POST['margen_musica'] ?? '';
    $margen_sec = ($margen_raw !== '') ? intval($margen_raw) : 5;

    // Factores y Deducciones
    $f_chomu = floatval($_POST['f_chomu'] ?? 1.0);
    $f_performance = floatval($_POST['f_performance'] ?? 1.0);
    $f_transitions = floatval($_POST['f_transitions'] ?? 1.0);
    $f_hybrid = floatval($_POST['f_hybrid'] ?? 1.0);
    $f_acro = floatval($_POST['f_acro'] ?? 1.0);
    $f_tre = floatval($_POST['f_tre'] ?? 1.0);
    $error_xs = floatval($_POST['error_xs'] ?? -0.1);
    $error_ob = floatval($_POST['error_ob'] ?? -0.5);
    $error_xl = floatval($_POST['error_xl'] ?? -3.0);

    $query = "UPDATE reglas_competicion SET 
                max_tre = $max_tre, 
                max_hibridos = $max_hibridos, 
                max_acrobacias = $max_acrobacias, 
                req_hibrido_creativo = $req_hibrido_creativo, 
                elementos_coach_card = $elementos_coach_card,
                max_apnea = $max_apnea,
                duracion_musica = $dur_sql,
                margen_musica = $margen_sec,
                f_chomu = $f_chomu,
                f_performance = $f_performance,
                f_transitions = $f_transitions,
                f_hybrid = $f_hybrid,
                f_acro = $f_acro,
                f_tre = $f_tre,
                error_xs = $error_xs,
                error_ob = $error_ob,
                error_xl = $error_xl
              WHERE id_regla = $id_regla";

    if(mysqli_query($connection, $query)){
        write_log("Regla técnica #$id_regla actualizada con factores y tiempos", "SUCCESS");
        $_SESSION['correcto'] = 'Regla del reglamento actualizada correctamente.';
    } else {
        write_log("Error al actualizar regla #$id_regla: " . mysqli_error($connection), "ERROR");
        $_SESSION['estado'] = 'Error al actualizar: ' . mysqli_error($connection);
    }
    header('Location: reglas_competicion.php');
    exit();
}

// 2. CREAR NUEVA REGLA
if(isset($_POST['save_btn'])){
    $id_categoria = intval($_POST['id_categoria']);
    $id_modalidad = intval($_POST['id_modalidad']);
    $max_tre = intval($_POST['max_tre'] ?? 0);
    $max_hibridos = intval($_POST['max_hibridos'] ?? 0);
    $max_acrobacias = intval($_POST['max_acrobacias'] ?? 0);
    $req_hibrido_creativo = intval($_POST['req_hibrido_creativo'] ?? 0);
    $elementos_coach_card = (isset($_POST['elementos_coach_card']) && $_POST['elementos_coach_card'] !== '') 
        ? intval($_POST['elementos_coach_card']) 
        : ($max_tre + $max_hibridos + $max_acrobacias + $req_hibrido_creativo);
    $max_apnea = !empty($_POST['max_apnea']) ? floatval($_POST['max_apnea']) : 'NULL';

    $dur_raw = $_POST['duracion_musica'] ?? '';
    $dur_sec = parseTimeSeconds($dur_raw);
    $dur_sql = ($dur_sec !== null && $dur_sec > 0) ? intval($dur_sec) : 'NULL';

    $margen_raw = $_POST['margen_musica'] ?? '';
    $margen_sec = ($margen_raw !== '') ? intval($margen_raw) : 5;

    // Factores y Deducciones
    $f_chomu = floatval($_POST['f_chomu'] ?? 1.0);
    $f_performance = floatval($_POST['f_performance'] ?? 1.0);
    $f_transitions = floatval($_POST['f_transitions'] ?? 1.0);
    $f_hybrid = floatval($_POST['f_hybrid'] ?? 1.0);
    $f_acro = floatval($_POST['f_acro'] ?? 1.0);
    $f_tre = floatval($_POST['f_tre'] ?? 1.0);
    $error_xs = floatval($_POST['error_xs'] ?? -0.1);
    $error_ob = floatval($_POST['error_ob'] ?? -0.5);
    $error_xl = floatval($_POST['error_xl'] ?? -3.0);

    // Verificar si ya existe regla para esta combinación
    $q_check = "SELECT id_regla FROM reglas_competicion WHERE id_categoria = $id_categoria AND id_modalidad = $id_modalidad";
    $r_check = mysqli_query($connection, $q_check);
    if($r_check && mysqli_num_rows($r_check) > 0){
        $_SESSION['estado'] = 'Ya existe una regla configurada para esta Categoría y Modalidad. Edita la regla existente.';
        header('Location: reglas_competicion.php');
        exit();
    }

    $query = "INSERT INTO reglas_competicion (id_categoria, id_modalidad, max_tre, max_hibridos, req_hibrido_creativo, elementos_coach_card, max_acrobacias, max_apnea, duracion_musica, margen_musica, f_chomu, f_performance, f_transitions, f_hybrid, f_acro, f_tre, error_xs, error_ob, error_xl)
              VALUES ($id_categoria, $id_modalidad, $max_tre, $max_hibridos, $req_hibrido_creativo, $elementos_coach_card, $max_acrobacias, $max_apnea, $dur_sql, $margen_sec, $f_chomu, $f_performance, $f_transitions, $f_hybrid, $f_acro, $f_tre, $error_xs, $error_ob, $error_xl)";

    if(mysqli_query($connection, $query)){
        write_log("Nueva regla técnica añadida (Cat: $id_categoria, Mod: $id_modalidad)", "SUCCESS");
        $_SESSION['correcto'] = 'Nueva regla reglamentaria añadida con éxito.';
    } else {
        $_SESSION['estado'] = 'Error al insertar regla: ' . mysqli_error($connection);
    }
    header('Location: reglas_competicion.php');
    exit();
}

// 3. ELIMINAR REGLA
if(isset($_POST['delete_btn'])){
    $id_regla = intval($_POST['delete_id']);
    $query = "DELETE FROM reglas_competicion WHERE id_regla = $id_regla";
    if(mysqli_query($connection, $query)){
        write_log("Regla técnica #$id_regla eliminada", "WARNING");
        $_SESSION['correcto'] = 'Regla eliminada correctamente.';
    } else {
        $_SESSION['estado'] = 'Error al eliminar regla: ' . mysqli_error($connection);
    }
    header('Location: reglas_competicion.php');
    exit();
}

header('Location: reglas_competicion.php');
exit();
