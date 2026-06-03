<?php
include('security.php');

// Añadir registro
if(isset($_POST['save_btn'])){
	$nombre = mysqli_real_escape_string($connection, $_POST['nombre']);
	$lugar = mysqli_real_escape_string($connection, $_POST['lugar']);
	$fecha = mysqli_real_escape_string($connection, $_POST['fecha']);
	$maps = mysqli_real_escape_string($connection, $_POST['maps'] ?? '');

	$query="INSERT INTO competiciones (nombre,lugar,fecha,maps) VALUES ('$nombre','$lugar','$fecha','$maps')";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Registro añadido con éxito';
		header('Location: competiciones.php');
	}else{
		$_SESSION['estado'] = 'Error. Registro no añadido <br>'.mysqli_error($connection);
		header('Location: competiciones.php');	
	}
}

// Actualizar registro
if(isset($_POST['update_btn'])){
	$id = $_POST['edit_id'];
	$nombre = mysqli_real_escape_string($connection, $_POST['edit_nombre'] ?? '');
	$lugar = mysqli_real_escape_string($connection, $_POST['edit_lugar'] ?? '');	
	$piscina = mysqli_real_escape_string($connection, $_POST['edit_piscina'] ?? '');	
	$fecha = mysqli_real_escape_string($connection, $_POST['edit_fecha'] ?? '');	
	$hora_inicio = mysqli_real_escape_string($connection, $_POST['edit_hora_inicio'] ?? '');	
	$hora_fin = mysqli_real_escape_string($connection, $_POST['edit_hora_fin'] ?? '');	
	$temporada = mysqli_real_escape_string($connection, $_POST['edit_temporada'] ?? '');
	
	$no_federado = isset($_POST['edit_no_federado']) ? 'si' : 'no';	
	
    // Lógica Excluyente: Figuras vs Rutinas
    $figuras = 'no'; // Por defecto Rutinas
    if (isset($_POST['edit_figuras'])) {
        $figuras = 'si';
    } elseif (isset($_POST['edit_rutinas'])) {
        $figuras = 'no';
    }
    
    // El campo niveles ya no se usa como toggle independiente
    $niveles = ($figuras == 'no') ? 'si' : 'no'; 
	
	$clave_liga = mysqli_real_escape_string($connection, $_POST['edit_clave_liga'] ?? '');	
	$nombre_corto = mysqli_real_escape_string($connection, $_POST['edit_nombre_corto'] ?? '');	
	$color = mysqli_real_escape_string($connection, $_POST['edit_color'] ?? '');	
	$maps = mysqli_real_escape_string($connection, $_POST['edit_maps'] ?? '');	
	$mascara_licencia = !empty($_POST['edit_mascara_licencia']) ? intval($_POST['edit_mascara_licencia']) : 0;
	$enlace_sorteo = mysqli_real_escape_string($connection, $_POST['edit_enlace_sorteo'] ?? '');	
	$mensaje = mysqli_real_escape_string($connection, $_POST['edit_mensaje'] ?? '');

    // GESTIÓN DE SUBIDA DE IMÁGENES (CABECERA / PIE)
    $header_informe = mysqli_real_escape_string($connection, $_POST['edit_header_informe'] ?? '');
    $footer_informe = mysqli_real_escape_string($connection, $_POST['edit_footer_informe'] ?? '');

    if (!empty($_FILES['new_header']['name'])) {
        $target_dir = "images/";
        $file_name = "header_" . time() . "_" . basename($_FILES["new_header"]["name"]);
        $target_file = $target_dir . $file_name;
        if (move_uploaded_file($_FILES["new_header"]["tmp_name"], $target_file)) {
            $header_informe = $target_file;
        }
    }

    if (!empty($_FILES['new_footer']['name'])) {
        $target_dir = "images/";
        $file_name = "footer_" . time() . "_" . basename($_FILES["new_footer"]["name"]);
        $target_file = $target_dir . $file_name;
        if (move_uploaded_file($_FILES["new_footer"]["tmp_name"], $target_file)) {
            $footer_informe = $target_file;
        }
    }

    // GESTIÓN DE SUBIDA DE DOCUMENTOS OFICIALES (PDF)
    $docs_keys = ['normativa', 'nadadoras', 'inscripciones', 'orden', 'resultados', 'liga'];
    $docs_dir = "docs/";
    if (!is_dir($docs_dir)) {
        mkdir($docs_dir, 0777, true);
    }
    
    foreach ($docs_keys as $key) {
        $input_name = "doc_" . $key;
        if (!empty($_FILES[$input_name]['name']) && $_FILES[$input_name]['error'] == 0) {
            $target_file = $docs_dir . $id . "-" . $key . ".pdf";
            move_uploaded_file($_FILES[$input_name]["tmp_name"], $target_file);
        }
    }

	$dias_inicio = !empty($_POST['edit_dias_inicio_inscripcion']) ? intval($_POST['edit_dias_inicio_inscripcion']) : 30;
	$dias_fin = !empty($_POST['edit_dias_fin_inscripcion']) ? intval($_POST['edit_dias_fin_inscripcion']) : 7;
	$dias_sorteo = !empty($_POST['edit_dias_sorteo']) ? intval($_POST['edit_dias_sorteo']) : 3;
	$dias_coach = !empty($_POST['edit_dias_coach_card']) ? intval($_POST['edit_dias_coach_card']) : 7;
	$dias_musica = !empty($_POST['edit_dias_musica']) ? intval($_POST['edit_dias_musica']) : 0;

	$query = "UPDATE competiciones SET 
				nombre ='$nombre', 
				lugar='$lugar', 
				piscina='$piscina', 
				fecha='$fecha', 
				hora_inicio='$hora_inicio', 
				hora_fin = '$hora_fin', 
				temporada='$temporada', 
				no_federado='$no_federado', 
				figuras='$figuras', 
				niveles='$niveles',
				clave_liga='$clave_liga', 
				nombre_corto='$nombre_corto', 
				color='$color', 
				maps='$maps',
				header_informe='$header_informe', 
				footer_informe='$footer_informe', 
				mascara_licencia='$mascara_licencia',
				enlace_sorteo='$enlace_sorteo',
				mensaje='$mensaje',
				dias_inicio_inscripcion='$dias_inicio',
				dias_fin_inscripcion='$dias_fin',
				dias_sorteo='$dias_sorteo',
				dias_coach_card='$dias_coach',
				dias_musica='$dias_musica'
			  WHERE id='$id'"; 

	if(mysqli_query($connection, $query)){
        // Actualizar sesión si es la actual
        if($id == $_SESSION['id_competicion_usuario']){
            $_SESSION['nombre_competicion_usuario'] = $nombre;
            $_SESSION['competicion_figuras_usuario'] = $figuras;
        }
		$_SESSION['correcto'] = 'Datos actualizados con éxito';
	}else{
		$_SESSION['estado'] = 'Error al actualizar datos';
	}
    header('Location: competiciones.php');
}

// Activar registro (Mejorado para forzar sesión)
if(isset($_POST['activar_btn'])){
	$id = $_POST['activar_id'];	
	mysqli_query($connection, "UPDATE competiciones SET activo ='no'"); 
	if(mysqli_query($connection, "UPDATE competiciones SET activo ='si' WHERE id='$id'")){
        // FORZAR ACTUALIZACIÓN DE SESIÓN INMEDIATA
        $q_res = mysqli_query($connection, "SELECT nombre, figuras FROM competiciones WHERE id = '$id'");
        $comp = mysqli_fetch_assoc($q_res);
        
        $_SESSION['id_competicion_usuario'] = $id;
        $_SESSION['nombre_competicion_usuario'] = $comp['nombre'];
        $_SESSION['competicion_figuras_usuario'] = $comp['figuras'];
        $_SESSION['id_competicion_activa'] = $id;
        $_SESSION['nombre_competicion_activa'] = $comp['nombre'];
        
        write_log("Competición #$id establecida como activa y actualizada en sesión", "SUCCESS");
		$_SESSION['correcto'] = 'Competición activada y establecida como contexto de trabajo.';
	}else{
		$_SESSION['estado'] = 'Error al activar competición';
	}
    header('Location: competiciones.php');
}

// Borrar registro
if(isset($_POST['delete_btn'])){
	$id = $_POST['delete_id'];
	if(mysqli_query($connection, "DELETE FROM competiciones WHERE id ='$id'")){
		$_SESSION['correcto'] = 'Registro eliminado';
	}else{
		$_SESSION['estado'] = 'Error al eliminar';
	}
    header('Location: competiciones.php');
}
?>
