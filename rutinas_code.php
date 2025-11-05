<?php
include('security.php');
include('./lib/my_functions.php');
//if(isset($_POST['id_competicion'])){
//	$id_competicion = $_POST['id_competicion'];
//	$_SESSION['id_competicion_usuario'] = $_POST['id_competicion'];
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

//session_start();
//Añadir rutina
if(isset($_POST['save_btn'])){
	$id_fase = $_POST['id_fase'];
	$id_club = $_POST['club'];
	if(isset($_POST['id_competicion']))
		  $id_competicion = $_POST['id_competicion'];
	else
		  $id_competicion = $_SESSION['id_competicion_activa'];
	$query="INSERT INTO rutinas (id_fase, id_club, id_competicion) VALUES ('".$id_fase."','".$id_club."','".$id_competicion."')";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Rutina añadida con éxito';
		header('Location: rutinas.php');
	}else{
		$_SESSION['estado'] = 'Error, rutina no añadida <br>'.mysqli_error($connection);
		header('Location: rutinas.php');
	}
}

//Actualizar rutina
if(isset($_POST['update_btn'])){
//	$id = $_POST['edit_id'];
//	$id_fase = $_POST['id_fase'];
	$id_club = $_POST['club'];
	$orden = $_POST['orden'];
	$tematica = $_POST['tematica'];

	$query = "UPDATE rutinas SET id_fase ='$id_fase', id_club='$id_club', orden='$orden', tematica='$tematica' WHERE id='$id_rutina'";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Rutina actualizada con éxito';
	}else{
		$_SESSION['estado'] = 'Error, la Rutina no se ha actualizado <br>'.mysqli_error($connection);
	}
}

//Borrar rutina
if(isset($_POST['delete_btn'])){
	$id = $_POST['delete_id'];
	$query = "SELECT id_competicion, music_name FROM rutinas WHERE id=$id";
//	$query_run = mysqli_query($connection,$query);
	$archivo_a_borrar = './public/music/'.mysqli_result($query_run,0,0).'/'.$id.'.mp3';
	//borro participantes
	$query = "DELETE FROM rutinas_participantes WHERE id_rutina ='$id'";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Participantes eliminados con éxito<br>';
	}else{
		$_SESSION['estado'] = 'Error al eliminar los participantes<br>'.mysqli_error($connection);
	}
	//borro rutina
	$query = "DELETE FROM rutinas WHERE id ='$id'";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] .= 'Rutina eliminada con éxito<br>';
	}else{
		$_SESSION['estado'] .= 'Error al eliminar la Rutina<br>'.mysqli_error($connection);
	}
	//borro coach card
    $query = "DELETE FROM hibridos_rutina WHERE id_rutina ='$id'";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] .= 'Coach Card eliminada con éxito<br>';
	}else{
		$_SESSION['estado'] .= 'Error al eliminar la Coach Card o alguno de sus elementos<br>'.mysqli_error($connection);
	}
	//borro archivo
	if (!unlink($archivo_a_borrar)) {
			$_SESSION['estado'] .= 'Error al eliminar el archivo de música<br>'.mysqli_error($connection);
	}
	else {
			$_SESSION['correcto'] .= 'Archivo de música eliminado<br>';
	}
}

//Subir o actualizar musica
if(isset($_POST['upload_music'])){
	$id = $_POST['edit_id'];
	$id_club = $_POST['club'];
	$music_name = $_POST['music_name'];
	if(isset($_POST['id_competicion']))
		  $id_competicion = $_POST['id_competicion'];
	else
		  $id_competicion = $_SESSION['id_competicion_activa'];
function stripAccents($str) {
    return strtr(utf8_decode($str), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
}
	$query = "UPDATE rutinas SET music_name='$music_name', music_original_name='".stripAccents($_FILES['musica']['name'])."' WHERE id='$id'";
	echo $query;
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Acompañamiento músical añadido';
	}else{
		$_SESSION['estado'] = 'Error, la música no se ha actualizado <br>'.mysqli_error($connection);
	}
	if($_FILES["musica"] != ''){
		$path = './public/music/'.$id_competicion.'/';
		if (!is_dir($path)) {
    		mkdir($path, 0777, true);
		}else{
			move_uploaded_file($_FILES["musica"]["tmp_name"],$path.$id.'.mp3');
			echo $path.$id;
		}
	}
}

//Dar de baja/alta rutina
if(isset($_GET['dar_baja'])){
	$dar_baja = $_GET['dar_baja'];
	$id_rutina = $_GET['id_rutina'];
	$query = "UPDATE rutinas SET baja ='$dar_baja' WHERE id='$id_rutina'";

	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Rutina actualizada con éxito';
	}else{
		$_SESSION['estado'] = 'Error, la Rutina no se ha actualizado <br>'.mysqli_error($connection);
	}
	header('Location: puntuaciones_lista_rutinas.php?id_fase='.$_GET['id_fase']);
	exit();
}

header('Location: rutinas.php');

?>
