<?php
include('security.php');
$id_competicion = $_SESSION['id_competicion_activa'];


//Añadir nadadora a rutina
if(isset($_POST['save_btn'])){
	$id = $_POST['id'];
	$id_nadadora = $_POST['id_nadadora'];
	$id_rutina = $_POST['id_rutina'];
	$reserva = $_POST['reserva'];

	$query="INSERT INTO rutinas_participantes (id_nadadora, id_rutina, reserva, id_competicion) VALUES ('".$id_nadadora."','".$id_rutina."', '".$reserva."', '".$id_competicion."')";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Juez añadido con éxito';
		header('Location: rutinas_participantes.php?id_rutina='.$id_rutina);
	}else{
		$_SESSION['estado'] = 'Error. Registro no añadido <br>'.mysqli_error($connection);
		header('Location: rutinas_participantes.php?id_rutina='.$id_rutina);
	}
}

//Actualizar nadadora de rutina
if(isset($_POST['update_btn'])){
	$id = $_POST['id'];
	$id_nadadora = $_POST['id_nadadora'];
	$id_rutina = $_POST['id_rutina'];
	$reserva = $_POST['reserva'];


	$query = "UPDATE rutinas_participantes SET id_nadadora ='$id_nadadora' WHERE id='$id'";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Datos actualizados con éxito';
		header('Location: rutinas_participantes.php?id_rutina='.$id_rutina);
	}else{
		$_SESSION['estado'] = 'Error. Los datos no se han actualizado <br>'.mysqli_error($connection);
		header('Location: rutinas_participantes.php?id_rutina='.$id_rutina);
	}
}

//Borrar nadadora de rutina
if(isset($_POST['delete_btn'])){
	$id = $_POST['id'];
	$id_rutina = $_POST['id_rutina'];

	$query = "DELETE FROM rutinas_participantes WHERE id ='$id'";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Registro eliminado con éxito';
		header('Location: rutinas_participantes.php?id_rutina='.$id_rutina);
	}else{
		$_SESSION['estado'] = 'Error. El Registro no se ha eliminado <br>'.mysqli_error($connection);
		header('Location: rutinas_participantes.php?id_rutina='.$id_rutina);
	}
}




	?>
