<?php
include('security.php');

// Función auxiliar para verificar propiedad de la nadadora (Seguridad)
function verificarPropiedadNadadora($connection, $id_nadadora) {
    if ($_SESSION['id_rol'] == 1 || $_SESSION['id_rol'] == 2 || $_SESSION['id_rol'] == 3) return true; // Admins tienen acceso total
    if ($_SESSION['id_rol'] == 5) {
        $id_club_sesion = $_SESSION['club'];
        $query = "SELECT club FROM nadadoras WHERE id = " . (int)$id_nadadora;
        $result = mysqli_query($connection, $query);
        if ($row = mysqli_fetch_assoc($result)) {
            return ($row['club'] == $id_club_sesion);
        }
    }
    return false; // Por defecto denegar
}

// AÑADIR REGISTRO
if(isset($_POST['save_btn'])){
	$licencia = mysqli_real_escape_string($connection, $_POST['licencia']);
	$apellidos = mysqli_real_escape_string($connection, mb_strtoupper($_POST['apellidos'], 'UTF-8'));
	$nombre = mysqli_real_escape_string($connection, mb_strtoupper($_POST['nombre'],  'UTF-8'));
	$fecha_nacimiento = mysqli_real_escape_string($connection, $_POST['fecha_nacimiento']);
	
    // Seguridad: Forzar club propio si es rol 5
    if ($_SESSION['id_rol'] == 5) {
        $club = (int)$_SESSION['club'];
    } else {
        $club = mysqli_real_escape_string($connection, $_POST['club']);
    }

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
	
    // Verificar permisos
    if (!verificarPropiedadNadadora($connection, $id)) {
        write_log("Intento de actualización no autorizado de nadadora ID $id por usuario " . $_SESSION['username'], "SECURITY");
        $_SESSION['estado'] = 'Acceso denegado. No tienes permisos para modificar esta deportista.';
        header('Location: nadadoras.php');
        exit();
    }

	$licencia = mysqli_real_escape_string($connection, $_POST['edit_licencia']);
	$apellidos = mysqli_real_escape_string($connection, mb_strtoupper($_POST['edit_apellidos'], 'UTF-8'));
	$nombre = mysqli_real_escape_string($connection, mb_strtoupper($_POST['edit_nombre'],  'UTF-8'));
	$fecha_nacimiento = mysqli_real_escape_string($connection, $_POST['fecha_nacimiento']);
	
    // Seguridad: Mantener el club original si es rol 5 para evitar transferencias ilícitas
    if ($_SESSION['id_rol'] == 5) {
        $club = (int)$_SESSION['club'];
    } else {
        $club = mysqli_real_escape_string($connection, $_POST['club']);
    }

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

    // Verificar permisos
    if (!verificarPropiedadNadadora($connection, $id)) {
        write_log("Intento de borrado no autorizado de nadadora ID $id por usuario " . $_SESSION['username'], "SECURITY");
        $_SESSION['estado'] = 'Acceso denegado. No tienes permisos para eliminar esta deportista.';
        header('Location: nadadoras.php');
        exit();
    }

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