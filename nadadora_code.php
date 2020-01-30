<?php
session_start();
$connection = mysqli_connect('localhost','root','xas','sincrm3');
//Añadir registro
if(isset($_POST['save_btn'])){
	$licencia = $_POST['licencia'];
	$apellidos = $_POST['apellidos'];
	$nombre = $_POST['nombre'];
	$fechadenacimiento = $_POST['fechadenacimiento'];

	$query="INSERT INTO nadadoras (apellidos,nombre,licencia,fechadenacimiento) VALUES ('".$apellidos."','".$nombre."','".$licencia."','".$fechadenacimiento."')";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Registro añadido con éxito';
		header('Location: nadadora.php');
	}else{
		$_SESSION['estado '] = 'Error. Registro no añadido <br>'.mysqli_error($connection);
		header('Location: nadadora.php');	
	}
}

//Actualizar registro
if(isset($_POST['update_btn'])){
	$id = $_POST['edit_id'];
	$licencia = $_POST['edit_licencia'];
	$apellidos = $_POST['edit_apellidos'];
	$nombre = $_POST['edit_nombre'];
	$fechadenacimiento = $_POST['edit_fechadenacimiento'];	

	$query = "UPDATE nadadoras SET licencia ='$licencia', apellidos='$apellidos', nombre='$nombre', fechadenacimiento='$fechadenacimiento' WHERE id='$id'"; 
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Datos actualizados con éxito';
		header('Location: nadadora.php');
	}else{
		$_SESSION['estado '] = 'Error. Los datos no se han actualizado <br>'.mysqli_error($connection);
		header('Location: nadadora.php');	
	}
}

//Borrar registro
if(isset($_POST['delete_btn'])){
	$id = $_POST['delete_id'];

	$query = "DELETE FROM nadadoras WHERE id ='$id'"; 
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Registro eliminado con éxito';
		header('Location: nadadora.php');
	}else{
		$_SESSION['estado'] = 'Error. El Registro no se ha eliminado <br>'.mysqli_error($connection);
		header('Location: nadadora.php');	
	}
}
	?>
