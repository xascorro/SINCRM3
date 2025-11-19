<?php
include('security.php');
//Añadir registro
if(isset($_POST['save_btn'])){
	$nombre = $_POST['nombre'];
	$lugar = $_POST['lugar'];
	$fecha = $_POST['fecha'];

	$query="INSERT INTO competiciones (nombre,lugar,fecha) VALUES ('".$nombre."','".$lugar."','".$fecha."')";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Registro añadido con éxito';
		header('Location: competiciones.php');
	}else{
		$_SESSION['estado'] = 'Error. Registro no añadido <br>'.mysqli_error($connection);
		header('Location: competiciones.php');	
	}
}

//Actualizar registro
if(isset($_POST['update_btn'])){
	$id = $_POST['edit_id'];
	$nombre = $_POST['edit_nombre'];
	$lugar = $_POST['edit_lugar'];	
	$piscina = $_POST['edit_piscina'];	
	$fecha = $_POST['edit_fecha'];	
	$hora_inicio = $_POST['edit_hora_inicio'];	
	$hora_fin = $_POST['edit_hora_fin'];	
	$temporada = $_POST['edit_temporada'];
	$no_federado = $_POST['edit_no_federado'];	
	$figuras = $_POST['edit_figuras'];	
	$clave_liga = $_POST['edit_clave_liga'];	
	$nombre_corto = $_POST['edit_nombre_corto'];	
	$color = $_POST['edit_color'];	
	$header_informe = $_POST['edit_header_informe'];	
	$footer_informe = $_POST['edit_footer_informe'];	
	$mascara_licencia = $_POST['edit_mascara_licencia'];	


	$query = "UPDATE competiciones SET nombre ='$nombre', lugar='$lugar', piscina='$piscina', fecha='$fecha', hora_inicio='$hora_inicio', hora_fin = '$hora_fin', temporada='$temporada', no_federado='$no_federado', figuras='$figuras', clave_liga='$clave_liga', nombre_corto='$nombre_corto', color='$color', header_informe='$header_informe', footer_informe='$footer_informe', mascara_licencia='$mascara_licencia'  WHERE id='$id'"; 
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Datos actualizados con éxito';
		header('Location: competiciones.php');
	}else{
		$_SESSION['estado'] = 'Error. Los datos no se han actualizado <br>'.mysqli_error($connection);
		header('Location: competiciones.php');	
	}
}

//Borrar registro
if(isset($_POST['delete_btn'])){
	$id = $_POST['delete_id'];

	$query = "DELETE FROM competiciones WHERE id ='$id'";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Registro eliminado con éxito';
		header('Location: competiciones.php');
	}else{
		$_SESSION['estado'] = 'Error. El Registro no se ha eliminado <br>'.mysqli_error($connection);
		header('Location: competiciones.php');
	}
}

//Activar registro
if(isset($_POST['activar_btn'])){
	$id = $_POST['activar_id'];	

	$query = "UPDATE competiciones SET activo ='no'"; 
	$query_run = mysqli_query($connection,$query);
	$query = "UPDATE competiciones SET activo ='si' WHERE id='$id'"; 
	$query_run = mysqli_query($connection,$query);

	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Datos actualizados con éxito';
		header('Location: competiciones.php');
	}else{
		$_SESSION['estado'] = 'Error. Los datos no se han actualizado <br>'.mysqli_error($connection);
		header('Location: competiciones.php');	
	}
}
?>
