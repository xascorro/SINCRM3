<?php
include('security.php');
$id_competicion = $_SESSION['id_competicion_activa'];
//Añadir puesto juez
if(isset($_POST['save_btn'])){
	$id_puestos_juez = $_POST['id_puestos_juez'];
	$id_juez = $_POST['id_juez'];

	$query="INSERT INTO puesto_juez (id_puestos_juez, id_juez, id_competicion) VALUES ('".$id_puestos_juez."','".$id_juez."', '".$id_competicion."')";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
        write_log("AÑADIDO: Juez a Dirección - Puesto ID: $id_puestos_juez, Juez ID: $id_juez", "SUCCESS");
		$_SESSION['correcto'] = 'Juez añadido con éxito';
		header('Location: paneles_jueces.php');
	}else{
        write_log("FALLO AÑADIR: Juez a Dirección - Error: ".mysqli_error($connection), "ERROR");
		$_SESSION['estado'] = 'Error. Registro no añadido <br>'.mysqli_error($connection);
		header('Location: paneles_jueces.php');	
	}
    exit();
}

//Actualizar registro
if(isset($_POST['update_btn'])){
	$id = $_POST['edit_id'];
	$id_juez = $_POST['id_juez'];	
    $id_puestos_juez = $_POST['id_puestos_juez'];

	$query = "UPDATE puesto_juez SET id_puestos_juez ='$id_puestos_juez', id_juez='$id_juez' WHERE id='$id'";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
        write_log("ACTUALIZADO: Juez de Dirección (ID Vinculo: $id) - Nuevo Puesto: $id_puestos_juez, Nuevo Juez: $id_juez", "SUCCESS");
		$_SESSION['correcto'] = 'Datos actualizados con éxito';
		header('Location: paneles_jueces.php');
	}else{
        write_log("FALLO ACTUALIZAR: Juez de Dirección (ID Vinculo: $id) - Error: ".mysqli_error($connection), "ERROR");
		$_SESSION['estado'] = 'Error. Los datos no se han actualizado <br>'.mysqli_error($connection);
		header('Location: paneles_jueces.php');	
	}
    exit();
}

//Borrar registro (Puesto Juez en Dirección)
if(isset($_POST['delete_btn'])){
	$id = mysqli_real_escape_string($connection, $_POST['delete_id']);
    
    // Obtener info para el log antes de borrar
    $q_info = "SELECT j.nombre, j.apellidos, p.nombre as puesto FROM puesto_juez pj JOIN jueces j ON pj.id_juez = j.id JOIN puestos_juez p ON pj.id_puestos_juez = p.id WHERE pj.id = '$id'";
    $info = mysqli_fetch_assoc(mysqli_query($connection, $q_info));
    $detalles = "{$info['puesto']}: {$info['nombre']} {$info['apellidos']} (ID Vinculo: $id)";

	$query = "DELETE FROM puesto_juez WHERE id ='$id'"; 
	if(mysqli_query($connection, $query)){
        write_log("ELIMINADO: Juez de Dirección - $detalles", "SECURITY");
		$_SESSION['correcto'] = 'Juez eliminado de la dirección';
	}else{
        write_log("FALLO ELIMINAR: Juez de Dirección - $detalles - Error: ".mysqli_error($connection), "ERROR");
		$_SESSION['estado'] = 'Error técnico al eliminar';
	}
    header('Location: paneles_jueces.php');
    exit();
}

//Añadir panel técnico
if(isset($_POST['save_btn_panel'])){
	$nombre = mysqli_real_escape_string($connection, $_POST['nombre']);
	$id_paneles_tipo = mysqli_real_escape_string($connection, $_POST['id_paneles_tipo']);
	$numero_jueces = intval($_POST['numero_jueces']);
    $peso = intval($_POST['peso']);
    $descripcion = mysqli_real_escape_string($connection, $_POST['descripcion'] ?? '');
    $color = mysqli_real_escape_string($connection, $_POST['color'] ?? '#3b82f6');

	$query="INSERT INTO paneles (nombre, id_paneles_tipo, numero_jueces, peso, descripcion, color, id_competicion) 
            VALUES ('$nombre', '$id_paneles_tipo', '$numero_jueces', '$peso', '$descripcion', '$color', '$id_competicion')";
	
    if(mysqli_query($connection, $query)){
        write_log("CREADO: Panel Técnico '$nombre' - $numero_jueces Jueces, Peso: $peso%", "SUCCESS");
		$_SESSION['correcto'] = 'Panel técnico añadido con éxito';
	}else{
        write_log("FALLO CREAR: Panel Técnico '$nombre' - Error: ".mysqli_error($connection), "ERROR");
		$_SESSION['estado'] = 'Error técnico al crear el panel';
	}
    header('Location: paneles_jueces.php');
    exit();
}

//Actualizar panel técnico
if(isset($_POST['update_btn_panel'])){
	$id = mysqli_real_escape_string($connection, $_POST['edit_id']);
	$id_paneles_tipo = mysqli_real_escape_string($connection, $_POST['id_paneles_tipo']);
	$nombre = mysqli_real_escape_string($connection, $_POST['edit_nombre']);
	$numero_jueces = intval($_POST['edit_numero_jueces']);
    $peso = intval($_POST['edit_peso']);
	$color = mysqli_real_escape_string($connection, $_POST['edit_color']);
	$descripcion = mysqli_real_escape_string($connection, $_POST['edit_descripcion'] ?? '');
    
    // Lógica Excluyente: Tipo de Puntuación (AQUA vs Sincro)
    $obsoleto = 'no'; // Default AQUA
    if (isset($_POST['edit_puntuacion_sincro'])) {
        $obsoleto = 'si';
    } elseif (isset($_POST['edit_puntuacion_aqua'])) {
        $obsoleto = 'no';
    }

    // Lógica Excluyente: Contabilización (Puntúa vs DTC)
    $puntua = 'si'; // Default Puntúa
    if (isset($_POST['edit_contabilizacion_dtc'])) {
        $puntua = 'no';
    } elseif (isset($_POST['edit_contabilizacion_puntua'])) {
        $puntua = 'si';
    }

	$query = "UPDATE paneles SET nombre ='$nombre', numero_jueces='$numero_jueces', peso ='$peso', color ='$color', descripcion ='$descripcion', id_paneles_tipo='$id_paneles_tipo', obsoleto='$obsoleto', puntua='$puntua' WHERE id='$id'";
	if(mysqli_query($connection, $query)){
        write_log("ACTUALIZADO: Panel Técnico (ID: $id) - Nuevo Nombre: '$nombre', Jueces: $numero_jueces, Peso: $peso%, Obsoleto: $obsoleto, Puntua: $puntua", "SUCCESS");
		$_SESSION['correcto'] = 'Panel actualizado con éxito';
	}else{
        write_log("FALLO ACTUALIZAR: Panel Técnico (ID: $id) - Error: ".mysqli_error($connection), "ERROR");
		$_SESSION['estado'] = 'Error técnico al actualizar el panel';
	}
    header('Location: paneles_jueces.php');
    exit();
}


//Borrar panel técnico de jueces
if(isset($_POST['delete_btn_panel'])){
	$id = mysqli_real_escape_string($connection, $_POST['delete_id']);
    
    // 1. Obtener info del panel
    $q_p = "SELECT nombre FROM paneles WHERE id = '$id'";
    $p_info = mysqli_fetch_assoc(mysqli_query($connection, $q_p));
    $nombre_panel = $p_info['nombre'] ?? 'Desconocido';

    // 2. PROTECCIÓN CRÍTICA: ¿Tiene este panel puntuaciones ya grabadas en alguna fase?
    $q_check = "SELECT COUNT(*) as total FROM puntuaciones_jueces WHERE id_panel_juez IN (SELECT id FROM panel_jueces WHERE id_panel = '$id')";
    $count = mysqli_fetch_assoc(mysqli_query($connection, $q_check))['total'];

    if($count > 0){
        write_log("BLOQUEADO: Intento de borrar panel '$nombre_panel' (ID: $id) con $count notas vinculadas.", "SECURITY");
        $_SESSION['estado'] = "⚠️ BLOQUEO DE SEGURIDAD: Este panel tiene $count notas grabadas en la competición. No se puede borrar para evitar la pérdida de datos.";
    } else {
        // 3. Si no tiene notas, procedemos pero LOGUEAMOS
        $query = "DELETE FROM paneles WHERE id ='$id'";
        if(mysqli_query($connection, $query)){
            write_log("ELIMINADO: Panel Técnico '$nombre_panel' (ID: $id) - Sin notas vinculadas.", "SECURITY");
            $_SESSION['correcto'] = 'Panel eliminado con éxito';
        }else{
            write_log("FALLO ELIMINAR: Panel '$nombre_panel' (ID: $id) - Error: ".mysqli_error($connection), "ERROR");
            $_SESSION['estado'] = 'Error al eliminar el registro';
        }
    }
    header('Location: paneles_jueces.php');
    exit();
}

//Añadir/Actualizar panel_jueces (INDIVIDUAL O BULK)
if(isset($_POST['panel_jueces_save_btn']) || isset($_POST['panel_jueces_bulk_save_btn'])){
    $id_competicion = $_SESSION['id_competicion_activa'];
    $id_panel = mysqli_real_escape_string($connection, $_POST['id_panel']);
    $id_fase = mysqli_real_escape_string($connection, $_POST['id_fase']);
    
    $errors = 0;
    $procesados = 0;

    // Determinar si es bulk o individual
    if(isset($_POST['panel_jueces_bulk_save_btn'])){
        $ids = $_POST['bulk_id'];
        $num_jueces = $_POST['bulk_num'];
        $id_jueces = $_POST['bulk_id_juez'];
        
        foreach($num_jueces as $index => $num){
            $id_reg = trim($ids[$index]);
            $id_juez = trim($id_jueces[$index]);
            
            if($id_juez != '' && $id_juez != ' '){
                if($id_reg != ''){
                    $query = "UPDATE panel_jueces SET id_juez = '$id_juez' WHERE id = '$id_reg'";
                } else {
                    $query = "INSERT INTO panel_jueces (id_fase, id_juez, numero_juez, id_panel, id_competicion) 
                              VALUES ('$id_fase', '$id_juez', '$num', '$id_panel', '$id_competicion')";
                }
                if(!mysqli_query($connection, $query)) $errors++;
                $procesados++;
            }
        }
    } else {
        // Individual (mantener compatibilidad si hiciera falta, aunque el front ahora usa bulk)
        $id = trim($_POST['id']);
        $id_juez = trim($_POST['id_juez']);
        $num = $_POST['numero_juez'];

        if($id_juez != ''){
            if($id != ''){
                $query = "UPDATE panel_jueces SET id_juez = '$id_juez' WHERE id = '$id'";
            } else {
                $query = "INSERT INTO panel_jueces (id_fase, id_juez, numero_juez, id_panel, id_competicion) 
                          VALUES ('$id_fase', '$id_juez', '$num', '$id_panel', '$id_competicion')";
            }
            if(!mysqli_query($connection, $query)) $errors++;
            $procesados++;
        }
    }

    if($errors == 0){
        write_log("CONFIGURADO: Composición de Panel ID $id_panel en Fase #$id_fase ($procesados jueces)", "SUCCESS");
        $_SESSION['correcto'] = "Configuración del panel guardada ($procesados jueces).";
    } else {
        write_log("FALLO CONFIGURAR: Composición de Panel ID $id_panel en Fase #$id_fase - $errors errores técnicos", "ERROR");
        $_SESSION['estado'] = "Error al guardar: $errors fallos técnicos.";
    }
    
    header('Location: paneles_jueces.php');
    exit();
}
//clonar panel de jueces
if(isset($_POST['clone_panel_btn'])){
    $id_panel = mysqli_real_escape_string($connection, $_POST['id_panel']);
    $source_fase = mysqli_real_escape_string($connection, $_POST['source_fase']);
    $target_fases = $_POST['target_fases'] ?? [];
    $id_competicion = $_SESSION['id_competicion_activa'];

    if(empty($target_fases)){
        $_SESSION['estado'] = 'No se han seleccionado fases de destino.';
        header('Location: paneles_jueces.php');
        exit();
    }

    // 1. Obtener la composición actual del panel en la fase origen
    $query_source = "SELECT numero_juez, id_juez FROM panel_jueces WHERE id_panel = '$id_panel' AND id_fase = '$source_fase'";
    $res_source = mysqli_query($connection, $query_source);
    
    $composicion = [];
    while($row = mysqli_fetch_assoc($res_source)){
        $composicion[$row['numero_juez']] = $row['id_juez'];
    }

    if(empty($composicion)){
        $_SESSION['estado'] = 'El panel de origen no tiene jueces asignados.';
        header('Location: paneles_jueces.php');
        exit();
    }

    $errors = 0;
    $clonados = 0;

    // 2. Para cada fase de destino
    foreach($target_fases as $target_id_fase){
        $target_id_fase = mysqli_real_escape_string($connection, $target_id_fase);
        
        // Para cada juez en la composición
        foreach($composicion as $num_juez => $id_juez){
            // Verificar si ya existe el registro para actualizar o insertar
            $q_check = "SELECT id FROM panel_jueces WHERE id_panel = '$id_panel' AND id_fase = '$target_id_fase' AND numero_juez = '$num_juez'";
            $res_check = mysqli_query($connection, $q_check);
            
            if(mysqli_num_rows($res_check) > 0){
                $id_reg = mysqli_fetch_assoc($res_check)['id'];
                $q_upd = "UPDATE panel_jueces SET id_juez = '$id_juez' WHERE id = '$id_reg'";
                if(!mysqli_query($connection, $q_upd)) $errors++;
            } else {
                $q_ins = "INSERT INTO panel_jueces (id_fase, id_juez, numero_juez, id_panel, id_competicion) 
                          VALUES ('$target_id_fase', '$id_juez', '$num_juez', '$id_panel', '$id_competicion')";
                if(!mysqli_query($connection, $q_ins)) $errors++;
            }
        }
        $clonados++;
    }

    if($errors == 0){
        write_log("Clonación de panel $id_panel desde fase $source_fase completada para $clonados fases", "SUCCESS");
        $_SESSION['correcto'] = "Panel clonado con éxito en $clonados fases.";
    } else {
        write_log("Errores durante la clonación de panel: $errors", "ERROR");
        $_SESSION['estado'] = "Se completó la clonación con $errors errores técnicos.";
    }

    header('Location: paneles_jueces.php');
    exit();
}

?>
