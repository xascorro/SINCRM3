<?php
include('security.php');
//Añadir registro
if(isset($_POST['save_btn'])){
	$licencia = $_POST['licencia'];
	$apellidos = $_POST['apellidos'];
	$nombre = $_POST['nombre'];
	$fecha_nacimiento = $_POST['fecha_nacimiento'];

	$query="INSERT INTO nadadoras (apellidos,nombre,licencia,fecha_nacimiento) VALUES ('".$apellidos."','".$nombre."','".$licencia."','".$fecha_nacimiento."')";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Registro añadido con éxito';
		header('Location: nadadoras.php');
	}else{
		$_SESSION['estado'] = 'Error. Registro no añadido <br>'.mysqli_error($connection);
		header('Location: nadadoras.php');	
	}
}

//Actualizar registro
if(isset($_POST['update_btn'])){
	$id = $_POST['edit_id'];
	$licencia = $_POST['edit_licencia'];
	$apellidos = $_POST['edit_apellidos'];
	$nombre = $_POST['edit_nombre'];
	$fecha_nacimiento = $_POST['edit_fecha_nacimiento'];	

	$query = "UPDATE nadadoras SET licencia ='$licencia', apellidos='$apellidos', nombre='$nombre', fecha_nacimiento='$fecha_nacimiento' WHERE id='$id'"; 
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Datos actualizados con éxito';
		header('Location: nadadoras.php');
	}else{
		$_SESSION['estado'] = 'Error. Los datos no se han actualizado <br>'.mysqli_error($connection);
		header('Location: nadadoras.php');	
	}
}

//Borrar registro
if(isset($_POST['delete_btn'])){
	$id = $_POST['delete_id'];

	$query = "DELETE FROM nadadoras WHERE id ='$id'"; 
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Registro eliminado con éxito';
		header('Location: nadadoras.php');
	}else{
		$_SESSION['estado'] = 'Error. El Registro no se ha eliminado <br>'.mysqli_error($connection);
		header('Location: nadadoras.php');	
	}
}
	?>
