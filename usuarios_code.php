<?php
include('security.php');
//include('./lib/my_functions.php');
$query ='';

session_start();
//Añadir registro
if(isset($_POST['save_btn'])){
	$username = $_POST['username'];
	$email = $_POST['email'];
	$password = $_POST['password'];
	$r_password = $_POST['r_password'];
	$club = $_POST['club'];
	if($password != $r_password){
		$_SESSION['estado'] = 'Error, los datos no se han actualizado <br>La contraseña no coincide';
		header('Location: usuarios.php');
	}else{
		$query="INSERT INTO usuarios (username,email,password,club) VALUES ('".$username."','".$email."','".$password."','".$club."')";
		$query_run = mysqli_query($connection,$query);
		if(mysqli_error($connection) == ''){
			$_SESSION['correcto'] = 'Registro añadido con éxito';
			header('Location: usuarios.php');
		}else{
			$_SESSION['estado '] = 'Error, registro no añadido <br>'.mysqli_error($connection);
			header('Location: usuarios.php');
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
	$edit_rol = $_POST['edit_rol'];

	if($password != $r_password){
		$_SESSION['estado'] = 'Error, los datos no se han actualizado <br>La contraseña no coincide';
		header('Location: usuarios.php');
	}else if($password == ''){
		$query = "UPDATE usuarios SET username ='$username', email='$email', telefono='$telefono', club='$club', telefono='$telefono', comentario='$comentario', id_rol='$edit_rol' WHERE id='$id'";
	}else{
		$query = "UPDATE usuarios SET username ='$username', email='$email', telefono='$telefono', hash='".password_hash($password, PASSWORD_DEFAULT)."', club='$club', comentario='$comentario', id_rol='$edit_rol' WHERE id='$id'";
	}
		$query_run = mysqli_query($connection,$query);
		if(mysqli_error($connection) == ''){
			$_SESSION['correcto'] = 'Datos actualizados con éxito';
		}else{
			$_SESSION['estado'] = 'Error, los datos no se han actualizado <br>'.mysqli_error($connection);
		}
		header('Location: usuarios.php');


}

//Borrar registro
if(isset($_POST['delete_btn'])){
	$id = $_POST['delete_id'];

	$query = "DELETE FROM usuarios WHERE id ='$id'";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Registro eliminado con éxito';
		header('Location: usuarios.php');
	}else{
		$_SESSION['estado'] = 'Error. El Registro no se ha eliminado <br>'.mysqli_error($connection);
		header('Location: usuarios.php');
	}
}
?>
