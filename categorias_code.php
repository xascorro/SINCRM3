<?php
include('security.php');
//Añadir registro
if(isset($_POST['save_btn'])){
	$nombre = $_POST['nombre'];
	$edad_minima = $_POST['edad_minima'];
	$edad_maxima = $_POST['edad_maxima'];

	$query="INSERT INTO categorias (nombre,edad_minima,edad_maxima) VALUES ('".$nombre."','".$edad_minima."','".$edad_maxima."')";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Registro añadido con éxito';
		header('Location: categorias.php');
	}else{
		$_SESSION['estado'] = 'Error. Registro no añadido <br>'.mysqli_error($connection);
		header('Location: categorias.php');	
	}
}

//Actualizar registro
if(isset($_POST['update_btn'])){
	$id = $_POST['edit_id'];
	$nombre = $_POST['edit_nombre'];
	$edad_minima = $_POST['edit_edad_minima'];
	$edad_maxima = $_POST['edit_edad_maxima'];

	$query = "UPDATE categorias SET nombre='$nombre', edad_minima='$edad_minima', edad_maxima='$edad_maxima' WHERE id='$id'"; 
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Datos actualizados con éxito';
		header('Location: categorias.php');
	}else{
		$_SESSION['estado'] = 'Error. Los datos no se han actualizado <br>'.mysqli_error($connection);
		header('Location: categorias.php');	
	}
}

//Borrar registro
if(isset($_POST['delete_btn'])){
	$id = $_POST['delete_id'];

	$query = "DELETE FROM categorias WHERE id ='$id'"; 
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Registro eliminado con éxito';
		header('Location: categorias.php');
	}else{
		$_SESSION['estado'] = 'Error. El Registro no se ha eliminado <br>'.mysqli_error($connection);
		header('Location: categorias.php');	
	}
}
	?>
