<?php
include('security.php');

$id_rutina = $_SESSION['id_rutina_usuario'] ?? 0;
$id_competicion = $_SESSION['id_competicion_usuario'] ?? 0;

// Función de validación de categoría deportiva en rutinas: Nunca bajar, máx subir 1 categoría
function validate_swimmer_routine_category($connection, $id_nadadora, $id_rutina) {
    if (!$id_nadadora || !$id_rutina) return [true, ""];

    $q_r = mysqli_query($connection, "SELECT c.nombre as cat_nombre, c.edad_minima, c.edad_maxima, comp.temporada, comp.fecha 
                                      FROM rutinas r 
                                      JOIN fases f ON r.id_fase = f.id 
                                      JOIN categorias c ON f.id_categoria = c.id 
                                      JOIN competiciones comp ON f.id_competicion = comp.id 
                                      WHERE r.id = '$id_rutina'");
    $r_data = mysqli_fetch_assoc($q_r);
    if (!$r_data) return [true, ""];

    $q_n = mysqli_query($connection, "SELECT nombre, apellidos, año_nacimiento FROM nadadoras WHERE id = '$id_nadadora'");
    $n_data = mysqli_fetch_assoc($q_n);
    if (!$n_data) return [true, ""];

    $año_nac = intval($n_data['año_nacimiento']);
    if ($año_nac <= 1900) return [true, ""];

    $t_raw = trim($r_data['temporada'] ?? '');
    $temporada_año = 0;
    if (preg_match('/[\/\-](\d{4})/', $t_raw, $m_t)) {
        $temporada_año = intval($m_t[1]);
    } elseif (preg_match('/^\d{4}$/', $t_raw)) {
        $temporada_año = intval($t_raw);
    } else {
        $f_ev = new DateTime($r_data['fecha'] ?? 'now');
        $m_ev = intval($f_ev->format('n'));
        $y_ev = intval($f_ev->format('Y'));
        $temporada_año = ($m_ev >= 9) ? ($y_ev + 1) : $y_ev;
    }

    $edad = $temporada_año - $año_nac;
    $nombre_completo = $n_data['apellidos'] . ', ' . $n_data['nombre'];
    $n_cat = strtolower($r_data['cat_nombre'] ?? '');

    // Categorías sin límite / especiales
    if (strpos($n_cat, 'especial') !== false) {
        return [true, ""];
    }

    $min_e = ($r_data['edad_minima'] !== null && $r_data['edad_minima'] !== '') ? intval($r_data['edad_minima']) : null;
    $max_e = ($r_data['edad_maxima'] !== null && $r_data['edad_maxima'] !== '') ? intval($r_data['edad_maxima']) : null;

    if ($min_e === 0 && $max_e !== null && $max_e >= 90 && strpos($n_cat, 'junior') === false && strpos($n_cat, 'absolut') === false) {
        return [true, ""];
    }

    if ($min_e !== null && $edad < $min_e) {
        return [false, "La nadadora $nombre_completo ($edad años) no puede asignarse: es demasiado joven para la categoría de esta rutina (mínimo $min_e años)."];
    }
    if ($max_e !== null && $edad > $max_e) {
        return [false, "La nadadora $nombre_completo ($edad años) no puede asignarse: es demasiado mayor para la categoría de esta rutina (máximo $max_e años)."];
    }

    return [true, ""];
}

// 1. Guardado Masivo (Bulk)
if (isset($_POST['bulk_save']) && isset($_POST['participants'])) {
    $participants = $_POST['participants'];
    $success_count = 0;
    $error_count = 0;
    
    // Obtener nadadoras actuales en la rutina para validación extra en servidor
    $q_actual = "SELECT id_nadadora FROM rutinas_participantes WHERE id_rutina = $id_rutina";
    $res_actual = mysqli_query($connection, $q_actual);
    $existing_swimmers = [];
    while($row = mysqli_fetch_assoc($res_actual)) {
        $existing_swimmers[] = $row['id_nadadora'];
    }

    foreach ($participants as $p) {
        $id_nadadora = intval($p['id_nadadora']);
        $id_registro = intval($p['id_registro']);
        $reserva = $p['reserva'];

        if ($id_nadadora > 0) {
            // Validar límites de categoría deportiva
            list($cat_valid, $cat_msg) = validate_swimmer_routine_category($connection, $id_nadadora, $id_rutina);
            if (!$cat_valid) {
                $error_count++;
                continue;
            }

            // Validar si la nadadora ya está en la rutina (excluyendo el registro actual si es un update)
            $q_check = "SELECT id FROM rutinas_participantes 
                        WHERE id_rutina = $id_rutina 
                        AND id_nadadora = $id_nadadora 
                        AND id != $id_registro";
            $res_check = mysqli_query($connection, $q_check);
            
            if (mysqli_num_rows($res_check) == 0) {
                if ($id_registro > 0) {
                    // Update
                    $query = "UPDATE rutinas_participantes SET id_nadadora = $id_nadadora WHERE id = $id_registro";
                } else {
                    // Insert
                    $query = "INSERT INTO rutinas_participantes (id_nadadora, id_rutina, reserva, id_competicion) 
                              VALUES ($id_nadadora, $id_rutina, '$reserva', $id_competicion)";
                }
                
                if (mysqli_query($connection, $query)) {
                    $success_count++;
                } else {
                    $error_count++;
                }
            }
        }
    }
    
    if ($success_count > 0) {
        $_SESSION['correcto'] = "Se han asignado $success_count participantes correctamente.";
        write_log("Asignación masiva en rutina #$id_rutina: $success_count exitos", "SUCCESS");
    }
    if ($error_count > 0) {
        $_SESSION['estado'] = "Hubo errores en $error_count asignaciones (duplicados o categorías no permitidas).";
    }
    
    header('Location: rutinas_participantes.php');
    exit();
}

// 2. Añadir nadadora individual (Existente)
if(isset($_POST['save_btn'])){
	$id_nadadora = intval($_POST['id_nadadora']);
	if($id_nadadora > 0 && $id_rutina > 0){
		$reserva = $_POST['reserva'];

        // Validar límites de categoría deportiva
        list($cat_valid, $cat_msg) = validate_swimmer_routine_category($connection, $id_nadadora, $id_rutina);
        if (!$cat_valid) {
            $_SESSION['estado'] = $cat_msg;
            header('Location: rutinas_participantes.php');
            exit();
        }

        // Validar duplicado
        $q_check = "SELECT id FROM rutinas_participantes WHERE id_rutina = $id_rutina AND id_nadadora = $id_nadadora";
        if (mysqli_num_rows(mysqli_query($connection, $q_check)) == 0) {
            $query="INSERT INTO rutinas_participantes (id_nadadora, id_rutina, reserva, id_competicion) VALUES ('".$id_nadadora."','".$id_rutina."', '".$reserva."', '".$id_competicion."')";
            if(mysqli_query($connection, $query)){
                write_log("Participante #$id_nadadora añadida a rutina #$id_rutina", "SUCCESS");
                $_SESSION['correcto'] = 'Participante añadida con éxito';
            } else {
                $_SESSION['estado'] = 'Error. Registro no añadido <br>'.mysqli_error($connection);
            }
        } else {
            $_SESSION['estado'] = 'Error: Esta nadadora ya está asignada a esta rutina.';
        }
	}
	header('Location: rutinas_participantes.php');
    exit();
}

// 3. Actualizar nadadora individual (Existente)
if(isset($_POST['update_btn'])){
	$id = intval($_POST['id']);
	$id_nadadora = intval($_POST['id_nadadora']);
	if($id_nadadora > 0){
        // Validar límites de categoría deportiva
        list($cat_valid, $cat_msg) = validate_swimmer_routine_category($connection, $id_nadadora, $id_rutina);
        if (!$cat_valid) {
            $_SESSION['estado'] = $cat_msg;
            header('Location: rutinas_participantes.php');
            exit();
        }

        // Validar duplicado (excluyendo el registro propio)
        $q_check = "SELECT id FROM rutinas_participantes WHERE id_rutina = $id_rutina AND id_nadadora = $id_nadadora AND id != $id";
        if (mysqli_num_rows(mysqli_query($connection, $q_check)) == 0) {
		    $query = "UPDATE rutinas_participantes SET id_nadadora ='$id_nadadora' WHERE id='$id'";
		    if(mysqli_query($connection, $query)){
                write_log("Participante actualizada en registro #$id", "INFO");
			    $_SESSION['correcto'] = 'Participante actualizada con éxito';
		    } else {
			    $_SESSION['estado'] = 'Error. Los datos no se han actualizado <br>'.mysqli_error($connection);
		    }
        } else {
            $_SESSION['estado'] = 'Error: Esta nadadora ya está asignada a esta rutina.';
        }
	}
	header('Location: rutinas_participantes.php');
    exit();
}

// 4. Borrar nadadora individual (Existente)
if(isset($_POST['delete_btn'])){
	$id = intval($_POST['id']);
	$query = "DELETE FROM rutinas_participantes WHERE id ='$id'";
	if(mysqli_query($connection, $query)){
        write_log("Participante eliminada del registro #$id", "WARNING");
		$_SESSION['correcto'] = 'Participante eliminada con éxito';
	} else {
		$_SESSION['estado'] = 'Error. El Registro no se ha eliminado <br>'.mysqli_error($connection);
	}
	header('Location: rutinas_participantes.php');
    exit();
}
?>
