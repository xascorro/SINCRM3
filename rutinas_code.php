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
	$id_rutina = (int)$_POST['edit_id'];
	$id_fase = (int)$_POST['id_fase'];
	$id_club = (int)$_POST['club'];
	$orden = (int)$_POST['orden'];
	$tematica = mysqli_real_escape_string($connection, $_POST['tematica'] ?? '');

    $preswimmer = ($orden <= -1) ? 'si' : 'no';

	$query = "UPDATE rutinas SET id_fase ='$id_fase', id_club='$id_club', orden='$orden', tematica='$tematica', preswimmer='$preswimmer' WHERE id='$id_rutina'";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
        write_log("Rutina actualizada (ID: $id_rutina) - Orden: $orden, Preswimmer: $preswimmer", "INFO");
		$_SESSION['correcto'] = 'Rutina actualizada con éxito';
	}else{
        write_log("Error al actualizar la rutina (ID: $id_rutina): " . mysqli_error($connection), "ERROR");
		$_SESSION['estado'] = 'Error, la Rutina no se ha actualizado <br>'.mysqli_error($connection);
	}
    header('Location: rutinas.php');
    exit();
}

//Borrar rutina
if(isset($_POST['delete_btn'])){
	$id = $_POST['delete_id'];
	$query = "SELECT id_competicion, music_name FROM rutinas WHERE id=$id";
	$query_run_del = mysqli_query($connection,$query);
    $row_del = mysqli_fetch_assoc($query_run_del);
    $id_comp_del = $row_del['id_competicion'];
	$archivo_a_borrar = './public/music/'.$id_comp_del.'/'.$id.'.mp3';
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
	if (file_exists($archivo_a_borrar)) {
        if (!unlink($archivo_a_borrar)) {
			    $_SESSION['estado'] .= 'Error al eliminar el archivo de música<br>';
	    }
	    else {
			    $_SESSION['correcto'] .= 'Archivo de música eliminado<br>';
	    }
	}
}

//Subir o actualizar musica
if(isset($_POST['upload_music'])){
	$id = (int)$_POST['edit_id'];
	$music_name = mysqli_real_escape_string($connection, $_POST['music_name']);
	if(isset($_POST['id_competicion']))
		  $id_competicion = (int)$_POST['id_competicion'];
	else
		  $id_competicion = (int)$_SESSION['id_competicion_activa'];

    function sanitizeFileName($str) {
        // Intentar convertir a ASCII (quita tildes y caracteres raros)
        $str = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str);
        // Quitar caracteres no permitidos en sistemas de archivos
        $str = preg_replace('/[^a-zA-Z0-9\._\- ]/', '', $str);
        return trim($str);
    }

    $original_name = mysqli_real_escape_string($connection, sanitizeFileName($_FILES['musica']['name']));

    try {
        $query = "UPDATE rutinas SET music_name='$music_name', music_original_name='$original_name' WHERE id='$id'";
        $query_run = mysqli_query($connection, $query);
        $_SESSION['correcto'] = 'Acompañamiento músical añadido';
    } catch (Exception $e) {
        write_log("Error DB al subir música: " . $e->getMessage(), "ERROR");
        $_SESSION['estado'] = 'Error, la música no se ha podido registrar en la base de datos.';
    }

	if(isset($_FILES["musica"]) && $_FILES["musica"]["tmp_name"] != ''){
		$path = './public/music/'.$id_competicion.'/';
		if (!is_dir($path)) {
    		mkdir($path, 0777, true);
		}
		$target_file = $path.$id.'.mp3';
		if(move_uploaded_file($_FILES["musica"]["tmp_name"], $target_file)){
			write_log("Música subida con éxito: $target_file (Original: ".$_FILES['musica']['name'].")", "SUCCESS");
		} else {
			$error_code = $_FILES['musica']['error'];
			write_log("Error al mover el archivo subido a $target_file. Código error PHP: $error_code", "ERROR");
			$_SESSION['estado'] .= " Error al guardar el archivo físico (Código: $error_code).";
		}
	} else {
		if(isset($_POST['upload_music'])) {
			$error_code = $_FILES['musica']['error'] ?? 'N/A';
			write_log("No se recibió archivo o tmp_name está vacío. Código error PHP: $error_code", "WARNING");
		}
	}
}

// Borrar música de rutina
if(isset($_POST['delete_music']) && isset($_POST['del_music_id'])){
    $id_rutina = (int)$_POST['del_music_id'];
    $id_competicion = (int)$_POST['del_music_comp'];
    
    // Primero, limpiar base de datos
    $query = "UPDATE rutinas SET music_name = NULL, music_original_name = NULL WHERE id = $id_rutina AND id_competicion = $id_competicion";
    if(mysqli_query($connection, $query)){
        // Luego, intentar borrar el archivo físico
        $file_path = './public/music/' . $id_competicion . '/' . $id_rutina . '.mp3';
        if(file_exists($file_path)) {
            if(unlink($file_path)){
                write_log("Música eliminada con éxito (Físico y DB): $file_path", "INFO");
                $_SESSION['correcto'] = 'Archivo de audio eliminado correctamente.';
            } else {
                write_log("No se pudo eliminar el archivo físico: $file_path", "ERROR");
                $_SESSION['estado'] = 'Audio desenlazado, pero no se pudo borrar el archivo físico.';
            }
        } else {
            write_log("Se borró la referencia en DB, pero el archivo físico no existía: $file_path", "WARNING");
            $_SESSION['correcto'] = 'Acompañamiento musical eliminado.';
        }
    } else {
        $_SESSION['estado'] = 'Error al desenlazar el audio de la base de datos: ' . mysqli_error($connection);
    }
    header('Location: rutinas.php');
    exit();
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
