<?php
include('security.php');

$id_rutina = $_SESSION['id_rutina_usuario'] ?? 0;
$id_competicion = $_SESSION['id_competicion_usuario'] ?? 0;

//Añadir nadadora a rutina
if(isset($_POST['save_btn'])){
	$id_nadadora = $_POST['id_nadadora'];
	if($id_nadadora > 0 && $id_rutina > 0){
		$reserva = $_POST['reserva'];

		$query="INSERT INTO rutinas_participantes (id_nadadora, id_rutina, reserva, id_competicion) VALUES ('".$id_nadadora."','".$id_rutina."', '".$reserva."', '".$id_competicion."')";
		$query_run = mysqli_query($connection,$query);
		if(mysqli_error($connection) == ''){
            write_log("Participante #$id_nadadora añadida a rutina #$id_rutina (Competición #$id_competicion)", "SUCCESS");
			$_SESSION['correcto'] = 'Participante añadida con éxito';
		}else{
			$_SESSION['estado'] = 'Error. Registro no añadido <br>'.mysqli_error($connection);
		}
	}
	header('Location: rutinas_participantes.php');
    exit();
}

//Actualizar nadadora de rutina
if(isset($_POST['update_btn'])){
	$id = $_POST['id'];
	$id_nadadora = $_POST['id_nadadora'];
	if($id_nadadora > 0){

	//	$id_rutina = $_POST['id_rutina'];
		$reserva = $_POST['reserva'];


		$query = "UPDATE rutinas_participantes SET id_nadadora ='$id_nadadora' WHERE id='$id'";
		$query_run = mysqli_query($connection,$query);
		if(mysqli_error($connection) == ''){
            write_log("Participante actualizada en registro #$id (Nueva nadadora: #$id_nadadora)", "INFO");
			$_SESSION['correcto'] = 'Participante actualizada con éxito';
		}else{
			$_SESSION['estado'] = 'Error. Los datos no se han actualizado <br>'.mysqli_error($connection);
		}
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
        write_log("Participante eliminada del registro #$id", "WARNING");
		$_SESSION['correcto'] = 'Participante eliminada con éxito';
	}else{
		$_SESSION['estado'] = 'Error. El Registro no se ha eliminado <br>'.mysqli_error($connection);
	}
	header('Location: rutinas_participantes.php');
}




	?>
