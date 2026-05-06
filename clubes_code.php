<?php
include('security.php');

// Directorio de destino para escudos de clubes
$target_dir = "images/clubes/";

/**
 * Función auxiliar para gestionar la subida de logos (Clubes)
 */
function handle_club_logo($file_array, $target_dir, $old_logo = null) {
    if (!isset($file_array) || $file_array['error'] == UPLOAD_ERR_NO_FILE) {
        return $old_logo;
    }

    $file_name = basename($file_array["name"]);
    $file_name = preg_replace("/[^a-zA-Z0-9.]/", "_", $file_name);
    $file_name = "club_" . time() . "_" . $file_name;
    if (!is_dir($target_dir)) {
        @mkdir($target_dir, 0755, true);
    }
    $target_file = $target_dir . $file_name;
    
    $check = @getimagesize($file_array["tmp_name"]);
    if($check !== false) {
        if (move_uploaded_file($file_array["tmp_name"], $target_file)) {
            // Borrar antiguo si existe y no es el genérico
            if ($old_logo && file_exists($old_logo) && strpos($old_logo, 'undraw_posting_photo') === false) {
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
        $id_federacion = mysqli_real_escape_string($connection, $_POST['federacion']);

    $logo_path = handle_club_logo($_FILES['logo'], $target_dir);

        $query = "INSERT INTO clubes (nombre, nombre_corto, codigo, logo, federacion, activo) 
              VALUES ('$nombre', '$nombre_corto', '$codigo', '$logo_path', '$id_federacion', 1)";
        $query_run = mysqli_query($connection, $query);

        if($query_run){
                write_log("Nuevo club registrado: $nombre_corto ($nombre)", "SUCCESS");
        $_SESSION['correcto'] = 'Club registrado con éxito';
        } else {
                write_log("Error al registrar club: " . mysqli_error($connection), "ERROR");
        $_SESSION['estado'] = 'Error técnico al guardar el club.';
        }
    header('Location: clubes.php');
    exit();
}

// ACTUALIZAR REGISTRO
if(isset($_POST['update_btn'])){
        $id = mysqli_real_escape_string($connection, $_POST['edit_id']);
        $nombre = mysqli_real_escape_string($connection, $_POST['edit_nombre']);
        $nombre_corto = mysqli_real_escape_string($connection, $_POST['edit_nombre_corto']);
        $codigo = mysqli_real_escape_string($connection, $_POST['edit_codigo']);
    $id_federacion = mysqli_real_escape_string($connection, $_POST['federacion']);
    $old_logo = $_POST['old_logo'];
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    $logo_path = handle_club_logo($_FILES['edit_logo'], $target_dir, $old_logo);

    $query = "UPDATE clubes SET nombre='$nombre', nombre_corto='$nombre_corto', codigo='$codigo', logo='$logo_path', federacion='$id_federacion', activo='$activo' WHERE id='$id'"; 
    $query_run = mysqli_query($connection, $query);

    if($query_run){
        write_log("Club actualizado (ID: $id): $nombre_corto", "INFO");
        $_SESSION['correcto'] = 'Información del club actualizada';
    } else {
        write_log("Error al actualizar club (ID: $id): " . mysqli_error($connection), "ERROR");
        $_SESSION['estado'] = 'No se pudieron actualizar los datos.';
    }
        header('Location: clubes.php');
    exit();
}

// BORRAR REGISTRO
if(isset($_POST['delete_btn'])){
        $id = mysqli_real_escape_string($connection, $_POST['delete_id']);

    $q_data = mysqli_query($connection, "SELECT nombre_corto, logo FROM clubes WHERE id = '$id'");
    $club_data = mysqli_fetch_assoc($q_data);
    
        $query = "DELETE FROM clubes WHERE id ='$id'"; 
        $query_run = mysqli_query($connection, $query);

        if($query_run){
        if (!empty($club_data['logo']) && file_exists($club_data['logo'])) {
            @unlink($club_data['logo']);
        }
                write_log("Club eliminado: " . $club_data['nombre_corto'] . " (ID: $id)", "WARNING");
        $_SESSION['correcto'] = 'Club eliminado correctamente';
        } else {
                write_log("Error al eliminar club (ID: $id): " . mysqli_error($connection), "ERROR");
        $_SESSION['estado'] = 'Error al intentar borrar el registro.';
        }
    header('Location: clubes.php');
    exit();
}
?>
