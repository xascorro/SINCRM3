<?php
include('security.php');

session_start();
//Añadir registro
if(isset($_POST['save_btn'])){
	$nombre = $_POST['nombre'];
	$nombre_corto = $_POST['nombre_corto'];
	$codigo = $_POST['codigo'];
	$id_federacion = $_POST['federacion'];
	$logo = $_FILES['logo']['name'];
	if($logo == ''){
		$logo = 'custom_logo.png';
	}
	
	
	
	$query="INSERT INTO clubes (nombre,nombre_corto,codigo,logo, federacion) VALUES ('".$nombre."','".$nombre_corto."','".$codigo."','images/clubes/".$logo."','".$id_federacion."')";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Registro añadido con éxito';
		if(file_exists('images/clubes/'.$_FILES['logo']['name'])){
			$store = $_FILES['logo']['name'];
			$_SESSION['estado'] = 'La imagen ya existe.'. $store;
		}else{
					move_uploaded_file($_FILES['logo']['tmp_name'], 'images/clubes/'.$_FILES['logo']['name']);

		}
		header('Location: clubes.php');
	}else{
		$_SESSION['estado'] = 'Error, registro no añadido <br>'.mysqli_error($connection);
		header('Location: clubes.php');	
	}

	
}

//Actualizar registro
if(isset($_POST['update_btn'])){
	$id = $_POST['edit_id'];
	$nombre = $_POST['edit_nombre'];
	$nombre_corto = $_POST['edit_nombre_corto'];
	$codigo = $_POST['edit_codigo'];
	$logo = $_POST['edit_logo'];
	
	if($password != $r_password){
		$_SESSION['estado'] = 'Error, los datos no se han actualizado <br>La contraseña no coincide';
		header('Location: usuarios.php');
	}else{
		$query = "UPDATE clubes SET nombre ='$nombre', nombre_corto='$nombre_corto', codigo='$codigo', logo='$logo' WHERE id='$id'"; 
		$query_run = mysqli_query($connection,$query);
		if(mysqli_error($connection) == ''){
			$_SESSION['correcto'] = 'Datos actualizados con éxito';
			header('Location: clubes.php');
		}else{
			$_SESSION['estado'] = 'Error, los datos no se han actualizado <br>'.mysqli_error($connection);
			header('Location: clubes.php');	
		}
	}
	
}

//Borrar registro
if(isset($_POST['delete_btn'])){
	$id = $_POST['delete_id'];

	$query = "DELETE FROM clubes WHERE id ='$id'"; 
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Registro eliminado con éxito';
		header('Location: clubes.php');
	}else{
		$_SESSION['estado'] = 'Error. El Registro no se ha eliminado <br>'.mysqli_error($connection);
		header('Location: clubes.php');	
	}
}
?>
