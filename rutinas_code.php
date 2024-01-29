<?php
include('security.php');

session_start();
//Añadir registro
if(isset($_POST['save_btn'])){
	$id_fase = $_POST['id_fase'];
	$id_club = $_POST['id_club'];

$query="INSERT INTO rutinas (id_fase, id_club) VALUES ('".$id_fase."','".$id_club."')";

	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Rutina añadida con éxito';
		header('Location: rutinas.php');
	}else{
		$_SESSION['estado'] = 'Error, rutina no añadida <br>'.mysqli_error($connection);
		header('Location: rutinas.php');
	}


}

//Actualizar registro
if(isset($_POST['update_btn'])){
	$id = $_POST['edit_id'];
	$id_fase = $_POST['id_fase'];
	$id_club = $_POST['id_club'];


	if($password != $r_password){
		$_SESSION['estado'] = 'Error, los datos no se han actualizado <br>La contraseña no coincide';
		header('Location: usuarios.php');
	}else{
		  $query = "UPDATE rutinas SET id_fase ='$id_fase', id_club='$id_club' WHERE id='$id'";
		$query_run = mysqli_query($connection,$query);
		if(mysqli_error($connection) == ''){
			$_SESSION['correcto'] = 'Rutina actualizada con éxito';
			header('Location: rutinas.php');
		}else{
			$_SESSION['estado'] = 'Error, la Rutina no se ha actualizado <br>'.mysqli_error($connection);
			header('Location: rutinas.php');
		}
	}

}

//Borrar registro
if(isset($_POST['delete_btn'])){
	$id = $_POST['delete_id'];
	$query = "DELETE FROM rutinas WHERE id ='$id'";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Rutina eliminada con éxito<br>';
	}else{
		$_SESSION['estado'] = 'Error al eliminar la Rutina<br>'.mysqli_error($connection);
	}
    $query = "DELETE FROM hibridos_rutina WHERE id_rutina ='$id'";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] .= 'Coach Card eliminada con éxito<br>';
		header('Location: rutinas.php');
	}else{
		$_SESSION['estado'] .= 'Error al eliminar la Coach Card o alguno de sus elementos<br>'.mysqli_error($connection);
		header('Location: rutinas.php');
	}
}
?>
