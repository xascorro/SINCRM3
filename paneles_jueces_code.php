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
		$_SESSION['correcto'] = 'Juez añadido con éxito';
		header('Location: paneles_jueces.php');
	}else{
		$_SESSION['estado'] = 'Error. Registro no añadido <br>'.mysqli_error($connection);
		header('Location: paneles_jueces.php');	
	}
}

//Actualizar registro
if(isset($_POST['update_btn'])){
	$id = $_POST['edit_id'];
	$nombre = $_POST['edit_nombre'];
	$id_juez = $_POST['id_juez'];	
    $id_puestos_juez = $_POST['id_puestos_juez'];


	$query = "UPDATE puesto_juez SET id_puestos_juez ='$id_puestos_juez', id_juez='$id_juez' WHERE id='$id'";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Datos actualizados con éxito';
		header('Location: paneles_jueces.php');
	}else{
		$_SESSION['estado'] = 'Error. Los datos no se han actualizado <br>'.mysqli_error($connection);
		header('Location: paneles_jueces.php');	
	}
}

//Borrar registro
if(isset($_POST['delete_btn'])){
	$id = $_POST['delete_id'];

	$query = "DELETE FROM puesto_juez WHERE id ='$id'"; 
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Registro eliminado con éxito';
		header('Location: paneles_jueces.php');
	}else{
		$_SESSION['estado'] = 'Error. El Registro no se ha eliminado <br>'.mysqli_error($connection);
		header('Location: paneles_jueces.php');	
	}
}


//Añadir panel jueces
if(isset($_POST['save_btn_panel'])){
	$nombre = $_POST['nombre'];
	$id_paneles_tipo = $_POST['id_paneles_tipo'];
	$numero_jueces = $_POST['numero_jueces'];
    $peso = $_POST['peso'];
    $descripcion = $_POST['descripcion'];
    $puntua = $_POST['puntua'];
    $color = $_POST['color'];
	$query="INSERT INTO paneles (nombre, id_paneles_tipo, numero_jueces, peso, descripcion, puntua, color, id_competicion) VALUES ('".$nombre."','".$id_paneles_tipo."','".$numero_jueces."', '".$peso."','".$descripcion."','".$puntua."','".$color."','".$id_competicion."')";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Panel añadido con éxito';
		header('Location: paneles_jueces.php');
	}else{
		$_SESSION['estado'] = 'Error. Registro no añadido <br>'.mysqli_error($connection);
		header('Location: paneles_jueces.php');
	}
}

//Actualizar panel de jueces
if(isset($_POST['update_btn_panel'])){
	$id = $_POST['edit_id'];
	$id_paneles_tipo = $_POST['id_paneles_tipo'];
	$nombre = $_POST['edit_nombre'];
	$numero_jueces = $_POST['edit_numero_jueces'];
    $peso = $_POST['edit_peso'];
	$color = $_POST['edit_color'];
	$descripcion = $_POST['edit_descripcion'];


	$query = "UPDATE paneles SET nombre ='$nombre', numero_jueces='$numero_jueces', peso ='$peso', color ='$color', descripcion ='$descripcion', id_paneles_tipo='$id_paneles_tipo' WHERE id='$id'";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Panel actualizado con éxito';
		header('Location: paneles_jueces.php');
	}else{
		$_SESSION['estado'] = 'Error. El Panel no se ha actualizado <br>'.mysqli_error($connection);
		header('Location: paneles_jueces.php');
	}
}

//Borrar panel de jueces
if(isset($_POST['delete_btn_panel'])){
	$id = $_POST['delete_id'];

	$query = "DELETE FROM paneles WHERE id ='$id'";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Panel eliminado con éxito';
		header('Location: paneles_jueces.php');
	}else{
		$_SESSION['estado'] = 'Error. El Panel no se ha eliminado <br>'.mysqli_error($connection);
		header('Location: paneles_jueces.php');
	}
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
        $_SESSION['correcto'] = "Configuración del panel guardada ($procesados jueces).";
    } else {
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
