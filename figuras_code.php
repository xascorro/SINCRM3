<?php
include('security.php');
//Añadir registro
if(isset($_POST['save_btn'])){
	$numero = $_POST['numero'];
	$nombre = $_POST['nombre'];
	$grado_dificultad = $_POST['grado_dificultad'];

	$query="INSERT INTO figuras (numero, nombre,grado_dificultad) VALUES ('".$numero."','".$nombre."','".$grado_dificultad."')";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Registro añadido con éxito';
		header('Location: figuras.php');
	}else{
		$_SESSION['estado'] = 'Error. Registro no añadido <br>'.mysqli_error($connection);
		header('Location: figuras.php');	
	}
}

//Actualizar registro
if(isset($_POST['update_btn'])){
	$id = $_POST['edit_id'];
	$numero = $_POST['edit_numero'];
	$nombre = $_POST['edit_nombre'];
	$grado_dificultad = $_POST['edit_grado_dificultad'];	

	$query = "UPDATE figuras SET numero ='$numero', nombre='$nombre', grado_dificultad='$grado_dificultad' WHERE id='$id'"; 
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Datos actualizados con éxito';
		header('Location: figuras.php');
	}else{
		$_SESSION['estado'] = 'Error. Los datos no se han actualizado <br>'.mysqli_error($connection);
		header('Location: figuras.php');	
	}
}

//Borrar registro
if(isset($_POST['delete_btn'])){
	$id = $_POST['delete_id'];

	$query = "DELETE FROM figuras WHERE id ='$id'"; 
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Registro eliminado con éxito';
		header('Location: figuras.php');
	}else{
		$_SESSION['estado'] = 'Error. El Registro no se ha eliminado <br>'.mysqli_error($connection);
		header('Location: figuras.php');	
	}
}
	?>
