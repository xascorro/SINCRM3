<?php
include('security.php');

$id_rutina = $_SESSION['id_rutina_usuario'] ?? 0;
$id_competicion = $_SESSION['id_competicion_usuario'] ?? 0;

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
        $_SESSION['estado'] = "Hubo errores en $error_count asignaciones.";
    }
    
    header('Location: rutinas_participantes.php');
    exit();
}

// 2. Añadir nadadora individual (Existente)
if(isset($_POST['save_btn'])){
	$id_nadadora = intval($_POST['id_nadadora']);
	if($id_nadadora > 0 && $id_rutina > 0){
		$reserva = $_POST['reserva'];

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
