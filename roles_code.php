<?php
include('security.php');

// AÑADIR ROL
if(isset($_POST['save_btn'])){
    $nombre = mysqli_real_escape_string($connection, $_POST['roles_nombre']);
    $level = mysqli_real_escape_string($connection, $_POST['level']);

    $query = "INSERT INTO roles (nombre, level) VALUES ('$nombre', '$level')";
    $query_run = mysqli_query($connection, $query);

    if($query_run){
        write_log("Nuevo rol creado: $nombre (Nivel $level)", "SUCCESS");
        $_SESSION['correcto'] = 'Rol añadido correctamente';
    } else {
        write_log("Error al crear rol: " . mysqli_error($connection), "ERROR");
        $_SESSION['estado'] = 'Error al registrar el rol.';
    }
    header('Location: roles.php');
    exit();
}

// ACTUALIZAR ROL
if(isset($_POST['update_btn'])){
    $id = mysqli_real_escape_string($connection, $_POST['edit_id']);
    $nombre = mysqli_real_escape_string($connection, $_POST['edit_nombre']);
    $level = mysqli_real_escape_string($connection, $_POST['edit_level']);

    $query = "UPDATE roles SET nombre='$nombre', level='$level' WHERE id='$id'";
    $query_run = mysqli_query($connection, $query);

    if($query_run){
        write_log("Rol actualizado (ID: $id): $nombre", "INFO");
        $_SESSION['correcto'] = 'Rol actualizado correctamente';
    } else {
        write_log("Error al actualizar rol (ID: $id): " . mysqli_error($connection), "ERROR");
        $_SESSION['estado'] = 'Error al actualizar el rol.';
    }
    header('Location: roles.php');
    exit();
}

// BORRAR ROL
if(isset($_POST['delete_btn'])){
    $id = mysqli_real_escape_string($connection, $_POST['delete_id']);

    // Info para el log
    $q_name = mysqli_query($connection, "SELECT nombre FROM roles WHERE id = '$id'");
    $r_data = mysqli_fetch_assoc($q_name);
    $nombre_rol = $r_data['nombre'] ?? 'ID '.$id;

    $query = "DELETE FROM roles WHERE id ='$id'";
    $query_run = mysqli_query($connection, $query);

    if($query_run){
        write_log("Rol eliminado: $nombre_rol", "WARNING");
        $_SESSION['correcto'] = 'Rol eliminado correctamente';
    } else {
        write_log("Error al eliminar rol ($nombre_rol): " . mysqli_error($connection), "ERROR");
        $_SESSION['estado'] = 'No se pudo eliminar el rol.';
    }
    header('Location: roles.php');
    exit();
}
?>