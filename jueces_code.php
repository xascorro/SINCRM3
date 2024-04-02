<?php
include('security.php');
//Añadir registro
if(isset($_POST['save_btn'])){
	$licencia = $_POST['licencia'];
	$apellidos = mb_strtoupper($_POST['apellidos'], 'UTF-8');
	$nombre = mb_strtoupper($_POST['nombre'], 'UTF-8');
	$id_federacion = $_POST['federacion'];

	$query="INSERT INTO jueces (apellidos,nombre,licencia, federacion) VALUES ('".$apellidos."','".$nombre."','".$licencia."','".$id_federacion."')";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Registro añadido con éxito';
		header('Location: jueces.php');
	}else{
		$_SESSION['estado'] = 'Error. Registro no añadido <br>'.mysqli_error($connection);
		header('Location: jueces.php');	
	}
}

//Actualizar registro
if(isset($_POST['update_btn'])){
	$id = $_POST['edit_id'];
	$licencia = $_POST['edit_licencia'];
	$apellidos = mb_strtoupper($_POST['edit_apellidos'], 'UTF-8');
	$nombre = mb_strtoupper($_POST['edit_nombre'], 'UTF-8');
	$id_federacion = $_POST['federacion'];	

	$query = "UPDATE jueces SET licencia ='$licencia', apellidos='$apellidos', nombre='$nombre', federacion='$id_federacion' WHERE id='$id'"; 
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Datos actualizados con éxito';
		header('Location: jueces.php');
	}else{
		$_SESSION['estado'] = 'Error. Los datos no se han actualizado <br>'.mysqli_error($connection);
		header('Location: jueces.php');	
	}
}

//Borrar registro
if(isset($_POST['delete_btn'])){
	$id = $_POST['delete_id'];

	$query = "DELETE FROM jueces WHERE id ='$id'"; 
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Registro eliminado con éxito';
		header('Location: jueces.php');
	}else{
		$_SESSION['estado'] = 'Error. El Registro no se ha eliminado <br>'.mysqli_error($connection);
		header('Location: jueces.php');	
	}
}
	?>
