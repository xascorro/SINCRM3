<?php
include('security.php');

session_start();
//Añadir registro
if(isset($_POST['save_btn'])){
	$username = $_POST['nombre'];
	else{
		$query="INSERT INTO roles (nombre,level) VALUES ('".$nombre."','".$level."')";
		$query_run = mysqli_query($connection,$query);
		if(mysqli_error($connection) == ''){
			$_SESSION['correcto'] = 'Registro añadido con éxito';
			header('Location: roles.php');
		}else{
			$_SESSION['estado '] = 'Error, registro no añadido <br>'.mysqli_error($connection);
			header('Location: roles.php');
		}
	}

}

//Actualizar registro
if(isset($_POST['update_btn'])){
	$id = $_POST['edit_id'];
	$username = $_POST['edit_username'];
	$email = $_POST['edit_email'];
	$password = $_POST['edit_password'];
	$r_password = $_POST['edit_r_password'];
	$club = $_POST['club'];
	$telefono = $_POST['edit_telefono'];
	$comentario = $_POST['edit_comentario'];
	$rol = $_POST['edit_rol'];

	if($password != $r_password){
		$_SESSION['estado'] = 'Error, los datos no se han actualizado <br>La contraseña no coincide';
		header('Location: usuarios.php');
	}else{
		$query = "UPDATE roles SET nombre ='$username', email='$email', telefono='$telefono', password='$password', club='$club', telefono='$telefono', comentario='$comentario', rol='$edit_rol' WHERE id='$id'";
		$query_run = mysqli_query($connection,$query);
		if(mysqli_error($connection) == ''){
			$_SESSION['correcto'] = 'Datos actualizados con éxito';
			header('Location: usuarios.php');
		}else{
			$_SESSION['estado'] = 'Error, los datos no se han actualizado <br>'.mysqli_error($connection);
			header('Location: roles.php');
		}
	}

}

//Borrar registro
if(isset($_POST['delete_btn'])){
	$id = $_POST['delete_id'];

	$query = "DELETE FROM roles WHERE id ='$id'";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Registro eliminado con éxito';
		header('Location: roles.php');
	}else{
		$_SESSION['estado'] = 'Error. El Registro no se ha eliminado <br>'.mysqli_error($connection);
		header('Location: roles.php');
	}
}
?>
