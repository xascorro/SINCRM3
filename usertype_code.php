<?php
include('security.php');

session_start();
//Añadir registro
if(isset($_POST['save_btn'])){
	$usertype_nombre = $_POST['usertype_nombre'];
	$level = $_POST['level'];
	$query="INSERT INTO usertype (usertype_nombre, level) VALUES ('".$usertype_nombre."','".$level."')";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
			$_SESSION['correcto'] = 'Registro añadido con éxito';
			header('Location: usertype.php');
	}else{
			$_SESSION['estado '] = 'Error, registro no añadido <br>'.mysqli_error($connection);
			header('Location: usertype.php');	
	}
	
	
}

//Actualizar registro
if(isset($_POST['update_btn'])){
	$id = $_POST['edit_id'];
	$usertype_nombre = $_POST['edit_usertype_nombre'];
	$level = $_POST['edit_level'];
	
	
	$query = "UPDATE usertype SET usertype_nombre ='$usertype_nombre', level='$level' WHERE id='$id'"; 
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Datos actualizados con éxito';
		header('Location: usertype.php');	
	}else{
		$_SESSION['estado'] = 'Error, los datos no se han actualizado <br>'.mysqli_error($connection);
		header('Location: usertype.php');	
	}
	
	
}

//Borrar registro
if(isset($_POST['delete_btn'])){
	$id = $_POST['delete_id'];

	$query = "DELETE FROM usertype WHERE id ='$id'"; 
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Registro eliminado con éxito';
		header('Location: usertype.php');
	}else{
		$_SESSION['estado'] = 'Error. El Registro no se ha eliminado <br>'.mysqli_error($connection);
		header('Location: usertype.php');	
	}
}
?>
