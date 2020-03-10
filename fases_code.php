<?php
include('security.php');

session_start();
//Añadir registro
if(isset($_POST['save_btn'])){
	$orden = $_POST['orden'];
	$categoria = $_POST['categoria'];
	$figura = $_POST['figura'];	
	
	$query="INSERT INTO fases (orden,id_categoria,id_figura, id_competicion) VALUES ('".$orden."','".$categoria."','".$figura."','".$_SESSION['id_competicion_activa']."')";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Registro añadido con éxito';
		header('Location: fases.php');
	}else{
		$_SESSION['estado'] = 'Error, registro no añadido <br>'.mysqli_error($connection);
		header('Location: fases.php');	
	}

	
}

//Actualizar registro
if(isset($_POST['update_btn'])){
	$id = $_POST['edit_id'];
	$orden = $_POST['edit_orden'];
	$categoria = $_POST['categoria'];
	$figura = $_POST['figura'];
	
	if($password != $r_password){
		$_SESSION['estado'] = 'Error, los datos no se han actualizado <br>La contraseña no coincide';
		header('Location: usuarios.php');
	}else{
		$query = "UPDATE fases SET orden ='$orden', id_categoria='$categoria', id_figura='$figura' WHERE id='$id'"; 
		$query_run = mysqli_query($connection,$query);
		if(mysqli_error($connection) == ''){
			$_SESSION['correcto'] = 'Datos actualizados con éxito';
			header('Location: fases.php');
		}else{
			$_SESSION['estado'] = 'Error, los datos no se han actualizado <br>'.mysqli_error($connection);
			header('Location: fases.php');	
		}
	}
	
}

//Borrar registro
if(isset($_POST['delete_btn'])){
	$id = $_POST['delete_id'];

	$query = "DELETE FROM fases WHERE id ='$id'"; 
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Registro eliminado con éxito';
		header('Location: fases.php');
	}else{
		$_SESSION['estado'] = 'Error. El Registro no se ha eliminado <br>'.mysqli_error($connection);
		header('Location: fases.php');	
	}
}
?>
