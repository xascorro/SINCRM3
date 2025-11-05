<?php
include('security.php');


$_SESSION['correcto'] = '';
$_SESSION['estado'] = '';


$id = $_POST['id'];
$id_fase = $_POST['id_fase'];
$id_nadadora = $_POST['id_nadadora'];
$id_categoria = $_POST['delete_id_categoria'];
$orden = $_POST['orden'];
$id_competicion = $_POST['id_competicion'];
$orden = 0;
//if(isset($_SESSION['id_competicion_usuario']))
//	$id_competicion=$_SESSION['id_competicion_usuario'];
//else
//	$id_competicion = $_SESSION['id_competicion_activa'];
//Añadir registro
if(isset($_POST['save_btn'])){
//$resultado=mysqli_query($connection,$query) or die (mysqli_error());
	$query = "select id from fases where id_categoria in (select id_categoria from fases where id = '$id_fase' and id_competicion = '$id_competicion') and id_competicion ='$id_competicion'";
	$fases_figuras = mysqli_query($connection,$query);
	echo $query;
	while($fase = mysqli_fetch_assoc($fases_figuras)){
		$query = "insert into inscripciones_figuras (id_fase, id_nadadora, orden, id_competicion) values ('".$fase['id']."', '$id_nadadora', '$orden', '$id_competicion')";
		mysqli_query($connection,$query);
        if(mysqli_error($connection) == ''){
            $_SESSION['correcto'] = 'Participante añadida con éxito';
			//escribo log
			$logFile = fopen("./log/log.txt", 'a') or die("Error creando archivo");
			fwrite($logFile, "\n".date("d/m/Y H:i:s")." El usuario ".$_SESSION['email']." a creado una nueva inscripción de figuras: ".$query) or die("Error escribiendo en el archivo");fclose($logFile);
		}else{
            $_SESSION['estado'] .= 'Error, participante no añadida <br>'.mysqli_error($connection);
			$logFile = fopen("./log/log.txt", 'a') or die("Error creando archivo");
			fwrite($logFile, "\n".date("d/m/Y H:i:s")." A fallado la inscripción de figuras dell usuario ".$_SESSION['email'].": ".$query) or die("Error escribiendo en el archivo");fclose($logFile);               }
    }
    header('Location: inscripciones_figuras.php');
}
//Borrar registro
if(isset($_POST['delete_btn'])){
	$id = $_POST['delete_id'];
	$query = "DELETE FROM inscripciones_figuras WHERE id_nadadora ='$id_nadadora' and id_fase in (SELECT id FROM fases where id_categoria='$id_categoria')";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Participación eliminada con éxito<br>';
	}else{
		$_SESSION['estado'] = 'Error al eliminar la participación<br>'.mysqli_error($connection);
	}
    $query = "DELETE FROM hibridos_rutina WHERE id_rutina ='$id'";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] .= 'Coach Card eliminada con éxito<br>';
	}else{
		$_SESSION['estado'] .= 'Error al eliminar la Coach Card o alguno de sus elementos<br>'.mysqli_error($connection);
	}
	//escribo log
	$logFile = fopen("./log/log.txt", 'a') or die("Error creando archivo");
	fwrite($logFile, "\n".date("d/m/Y H:i:s")." El usuario ".$_SESSION['email']." a eliminado la inscripción de figuras con id: ".$id) or die("Error escribiendo en el archivo");fclose($logFile);

    header('Location: inscripciones_figuras.php');
}


////Actualizar registro
if(isset($_POST['update_btn'])){
	$id = $_POST['edit_id'];
	$id_fase = $_POST['id_fase'];
	$id_club = $_POST['id_club'];


	if($password != $r_password){
		$_SESSION['estado'] = 'Error, los datos no se han actualizado <br>La contraseña no coincide';
		//header('Location: usuarios.php');
	}else{
		  $query = "UPDATE inscripciones_figuras SET id_fase ='$id_fase', id_club='$id_club' WHERE id='$id'";
		$query_run = mysqli_query($connection,$query);
		if(mysqli_error($connection) == ''){
			$_SESSION['correcto'] = 'Rutina actualizada con éxito';
			//header('Location: inscripciones_figuras.php');
		}else{
			$_SESSION['estado'] = 'Error, la Rutina no se ha actualizado <br>'.mysqli_error($connection);
			//header('Location: inscripciones_figuras.php');
		}
	}

}
//Dar de baja
if(isset($_POST['baja_btn'])){
	$id = $_POST['baja_id'];
	$query = "UPDATE inscripciones_figuras SET baja ='si' WHERE id='$id'";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Inscripción dada de baja';
	}else{
		$_SESSION['estado'] = 'Error, la Rutina no se ha actualizado <br>'.mysqli_error($connection);
	}
}
if(isset($_POST['alta_btn'])){
	$id = $_POST['alta_id'];
	$query = "UPDATE inscripciones_figuras SET baja = NULL WHERE id='$id'";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Inscripción dada de alta';
	}else{
		$_SESSION['estado'] = 'Error, la Rutina no se ha actualizado <br>'.mysqli_error($connection);
	}
}

		header('Location: inscripciones_figuras.php');

?>
