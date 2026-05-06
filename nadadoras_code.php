<?php
include('security.php');

// AÑADIR REGISTRO
if(isset($_POST['save_btn'])){
	$licencia = mysqli_real_escape_string($connection, $_POST['licencia']);
	$apellidos = mysqli_real_escape_string($connection, mb_strtoupper($_POST['apellidos'], 'UTF-8'));
	$nombre = mysqli_real_escape_string($connection, mb_strtoupper($_POST['nombre'],  'UTF-8'));
	$fecha_nacimiento = mysqli_real_escape_string($connection, $_POST['fecha_nacimiento']);
	$club = mysqli_real_escape_string($connection, $_POST['club']);

	$query="INSERT INTO nadadoras (apellidos,nombre,licencia,año_nacimiento, club, activo) VALUES ('$apellidos','$nombre','$licencia','$fecha_nacimiento','$club', 1)";
	$query_run = mysqli_query($connection, $query);

	if($query_run){
        write_log("Nueva nadadora añadida: $nombre $apellidos (Club ID: $club)", "SUCCESS");
		$_SESSION['correcto'] = 'Nadadora añadida con éxito';
	} else {
        write_log("Error al añadir nadadora: " . mysqli_error($connection), "ERROR");
		$_SESSION['estado'] = 'Error técnico al registrar la deportista.';
	}
    header('Location: nadadoras.php');
    exit();
}

// ACTUALIZAR REGISTRO
if(isset($_POST['update_btn'])){
	$id = mysqli_real_escape_string($connection, $_POST['edit_id']);
	$licencia = mysqli_real_escape_string($connection, $_POST['edit_licencia']);
	$apellidos = mysqli_real_escape_string($connection, mb_strtoupper($_POST['edit_apellidos'], 'UTF-8'));
	$nombre = mysqli_real_escape_string($connection, mb_strtoupper($_POST['edit_nombre'],  'UTF-8'));
	$fecha_nacimiento = mysqli_real_escape_string($connection, $_POST['fecha_nacimiento']);
	$club = mysqli_real_escape_string($connection, $_POST['club']);
    $activo = isset($_POST['activo']) ? 1 : 0;

	$query = "UPDATE nadadoras SET licencia ='$licencia', apellidos='$apellidos', nombre='$nombre', año_nacimiento='$fecha_nacimiento', club='$club', activo='$activo' WHERE id='$id'";
	$query_run = mysqli_query($connection, $query);

	if($query_run){
        $estado_txt = $activo ? "ACTIVA" : "BAJA";
        write_log("Nadadora actualizada (ID: $id): $nombre $apellidos | Estado: $estado_txt", "INFO");
		$_SESSION['correcto'] = 'Datos actualizados con éxito';
	} else {
        write_log("Error al actualizar nadadora (ID: $id): " . mysqli_error($connection), "ERROR");
		$_SESSION['estado'] = 'No se pudieron actualizar los datos.';
	}
    header('Location: nadadoras.php');
    exit();
}

// BORRAR REGISTRO
if(isset($_POST['delete_btn'])){
	$id = mysqli_real_escape_string($connection, $_POST['id_nadadora']);

    $q_name = mysqli_query($connection, "SELECT nombre, apellidos FROM nadadoras WHERE id = '$id'");
    $n_data = mysqli_fetch_assoc($q_name);
    $nombre_completo = ($n_data) ? $n_data['nombre']." ".$n_data['apellidos'] : "ID ".$id;

	$query = "DELETE FROM nadadoras WHERE id ='$id'"; 
	$query_run = mysqli_query($connection, $query);

	if($query_run){
        write_log("Nadadora eliminada del sistema: $nombre_completo", "WARNING");
		$_SESSION['correcto'] = 'Registro eliminado con éxito';
	} else {
        write_log("Error al eliminar nadadora ($nombre_completo): " . mysqli_error($connection), "ERROR");
		$_SESSION['estado'] = 'No se pudo eliminar el registro.';
	}
    header('Location: nadadoras.php');
    exit();
}
?>