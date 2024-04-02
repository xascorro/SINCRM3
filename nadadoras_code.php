<?php
include('security.php');
//Añadir registro
if(isset($_POST['save_btn'])){
	$licencia = $_POST['licencia'];
	$apellidos = mb_strtoupper($_POST['apellidos'], 'UTF-8');
	$nombre = mb_strtoupper($_POST['nombre'],  'UTF-8');
	$fecha_nacimiento = $_POST['fecha_nacimiento'];
	$club = $_POST['club'];

	$query="INSERT INTO nadadoras (apellidos,nombre,licencia,año_nacimiento, club) VALUES ('".$apellidos."','".$nombre."','".$licencia."','".$fecha_nacimiento."','".$club."')";
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
	$apellidos = mb_strtoupper($_POST['edit_apellidos'], 'UTF-8');
	$nombre = mb_strtoupper($_POST['edit_nombre'],  'UTF-8');
	$fecha_nacimiento = $_POST['fecha_nacimiento'];
	$club = $_POST['club'];

	$query = "UPDATE nadadoras SET licencia ='$licencia', apellidos='$apellidos', nombre='$nombre', año_nacimiento='$fecha_nacimiento', club='$club' WHERE id='$id'";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Datos actualizados con éxito';
		header('Location: nadadoras.php');
	}else{
		$_SESSION['estado'] = 'Error. Los datos no se han actualizado <br>'.mysqli_error($connection);
		header('Location: nadadoras.php');	
	}
}

//dar de baja nadadora
if(isset($_POST['baja_btn'])){
	$id = $_POST['id_nadadora'];
	$query = "UPDATE nadadoras SET baja ='si' WHERE id='$id'";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Nadadora dada de baja';
		header('Location: nadadoras.php');
	}else{
		$_SESSION['estado'] = 'Error. Los datos no se han actualizado <br>'.mysqli_error($connection);
		header('Location: nadadoras.php');
	}
}
//dar de alta nadadora
if(isset($_POST['alta_btn'])){
	$id = $_POST['id_nadadora'];
	$query = "UPDATE nadadoras SET baja ='no' WHERE id='$id'";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Nadadora dada de alta';
		header('Location: nadadoras.php');
	}else{
		$_SESSION['estado'] = 'Error. Los datos no se han actualizado <br>'.mysqli_error($connection);
		header('Location: nadadoras.php');
	}
}

//Borrar registro
if(isset($_POST['delete_btn'])){
	$id = $_POST['id_nadadora'];

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
