<?php
include('security.php');

// Directorio de destino para logos
$target_dir = "images/federaciones/";

/**
 * Función auxiliar para gestionar la subida de logos
 */
function handle_logo_upload($file_array, $target_dir, $old_logo = null) {
    if (!isset($file_array) || $file_array['error'] == UPLOAD_ERR_NO_FILE) {
        return $old_logo; // Si no hay archivo nuevo, devolver el antiguo
    }

    $file_name = basename($file_array["name"]);
    // Limpiar nombre de archivo (quitar espacios y caracteres raros)
    $file_name = preg_replace("/[^a-zA-Z0-9.]/", "_", $file_name);
    // Añadir prefijo único para evitar colisiones
    $file_name = time() . "_" . $file_name;
    $target_file = $target_dir . $file_name;
    
    // Verificar si es una imagen real
    $check = getimagesize($file_array["tmp_name"]);
    if($check !== false) {
        if (move_uploaded_file($file_array["tmp_name"], $target_file)) {
            // Si había un logo antiguo y no era el placeholder, intentar borrarlo
            if ($old_logo && file_exists($old_logo) && strpos($old_logo, 'placeholder') === false) {
                @unlink($old_logo);
            }
            return $target_file;
        }
    }
    return $old_logo;
}

// AÑADIR REGISTRO
if(isset($_POST['save_btn'])){
	$nombre = mysqli_real_escape_string($connection, $_POST['nombre']);
	$nombre_corto = mysqli_real_escape_string($connection, $_POST['nombre_corto']);
	$codigo = mysqli_real_escape_string($connection, $_POST['codigo']);
	
    // Gestionar logo
    $logo_path = handle_logo_upload($_FILES['logo'], $target_dir);
	
	$query = "INSERT INTO federaciones (nombre, nombre_corto, codigo, logo) VALUES ('$nombre', '$nombre_corto', '$codigo', '$logo_path')";
	$query_run = mysqli_query($connection, $query);

	if($query_run){
		write_log("Nueva federación añadida: $nombre_corto ($nombre)", "SUCCESS");
        $_SESSION['correcto'] = 'Federación añadida con éxito';
	} else {
		write_log("Error al añadir federación: " . mysqli_error($connection), "ERROR");
        $_SESSION['estado'] = 'Error al guardar en la base de datos.';
	}
    header('Location: federaciones.php');
    exit();
}

// ACTUALIZAR REGISTRO
if(isset($_POST['update_btn'])){
	$id = $_POST['edit_id'];
	$nombre = mysqli_real_escape_string($connection, $_POST['edit_nombre']);
	$nombre_corto = mysqli_real_escape_string($connection, $_POST['edit_nombre_corto']);
	$codigo = mysqli_real_escape_string($connection, $_POST['edit_codigo']);
    $old_logo = $_POST['old_logo'];
    
    // Gestionar logo (subir nuevo si existe)
    $logo_path = handle_logo_upload($_FILES['edit_logo'], $target_dir, $old_logo);
	
    $query = "UPDATE federaciones SET nombre='$nombre', nombre_corto='$nombre_corto', codigo='$codigo', logo='$logo_path' WHERE id='$id'"; 
    $query_run = mysqli_query($connection, $query);

    if($query_run){
        write_log("Federación actualizada (ID: $id): $nombre_corto", "INFO");
        $_SESSION['correcto'] = 'Datos actualizados con éxito';
    } else {
        write_log("Error al actualizar federación (ID: $id): " . mysqli_error($connection), "ERROR");
        $_SESSION['estado'] = 'Error al actualizar la base de datos.';
    }
	header('Location: federaciones.php');
    exit();
}

// BORRAR REGISTRO
if(isset($_POST['delete_btn'])){
	$id = $_POST['delete_id'];

    // Obtener datos antes de borrar para el log y borrar el logo físico
    $q_data = mysqli_query($connection, "SELECT nombre_corto, logo FROM federaciones WHERE id = '$id'");
    $fed_data = mysqli_fetch_assoc($q_data);
    
	$query = "DELETE FROM federaciones WHERE id ='$id'"; 
	$query_run = mysqli_query($connection, $query);

	if($query_run){
        // Borrar el logo físico si existe
        if (!empty($fed_data['logo']) && file_exists($fed_data['logo'])) {
            @unlink($fed_data['logo']);
        }
		write_log("Federación eliminada: " . $fed_data['nombre_corto'] . " (ID: $id)", "WARNING");
        $_SESSION['correcto'] = 'Registro y archivos eliminados con éxito';
	} else {
		write_log("Error al eliminar federación (ID: $id): " . mysqli_error($connection), "ERROR");
        $_SESSION['estado'] = 'No se pudo eliminar el registro.';
	}
    header('Location: federaciones.php');
    exit();
}
?>