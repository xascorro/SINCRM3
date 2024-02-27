<?php
include('security.php');
//if(isset($_POST['id_competicion'])){
//	$id_competicion = $_POST['id_competicion'];
//	$_SESSION['id_competicion_usuario'] = $_POST['id_competicion'];
//}else{
//	$id_competicion = $_SESSION['id_competicion_usuario'];
//}
//if(isset($_POST['id_rutina'])){
//	$id_rutina = $_POST['id_rutina'];
//	$_SESSION['id_rutina'] = $id_rutina;
//}elseif(isset($_SESSION['id_rutina'])){
//	$id_rutina = $_SESSION['id_rutina'];
//}
//if(isset($_POST['id_fase'])){
//	$id_fase = $_POST['id_fase'];
//}elseif(isset($_SESSION['id_fase'])){
//	$id_fase = $_SESSION['id_fase'];
//}

//Añadir nadadora a rutina
if(isset($_POST['save_btn'])){
	$id = $_POST['id'];
	$id_nadadora = $_POST['id_nadadora'];
//	$id_rutina = $_POST['id_rutina'];
	$reserva = $_POST['reserva'];

	$query="INSERT INTO rutinas_participantes (id_nadadora, id_rutina, reserva, id_competicion) VALUES ('".$id_nadadora."','".$id_rutina."', '".$reserva."', '".$id_competicion."')";
	echo $query;
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Participante añadida con éxito';
	}else{
		$_SESSION['estado'] = 'Error. Registro no añadido <br>'.mysqli_error($connection);
	}
	header('Location: rutinas_participantes.php');

}

//Actualizar nadadora de rutina
if(isset($_POST['update_btn'])){
	$id = $_POST['id'];
	$id_nadadora = $_POST['id_nadadora'];
//	$id_rutina = $_POST['id_rutina'];
	$reserva = $_POST['reserva'];


	$query = "UPDATE rutinas_participantes SET id_nadadora ='$id_nadadora' WHERE id='$id'";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Participante actualizada con éxito';
	}else{
		$_SESSION['estado'] = 'Error. Los datos no se han actualizado <br>'.mysqli_error($connection);
	}
	header('Location: rutinas_participantes.php');

}

//Borrar nadadora de rutina
if(isset($_POST['delete_btn'])){
	$id = $_POST['id'];
//	$id_rutina = $_POST['id_rutina'];

	$query = "DELETE FROM rutinas_participantes WHERE id ='$id'";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Participante eliminada con éxito';
	}else{
		$_SESSION['estado'] = 'Error. El Registro no se ha eliminado <br>'.mysqli_error($connection);
	}
	header('Location: rutinas_participantes.php');
}




	?>
