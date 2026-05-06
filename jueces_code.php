<?php
include('security.php');

// Añadir registro
if(isset($_POST['save_btn'])){
    $nombre = mysqli_real_escape_string($connection, $_POST['nombre']);
    $apellidos = mysqli_real_escape_string($connection, $_POST['apellidos']);
    $licencia = mysqli_real_escape_string($connection, $_POST['licencia'] ?? '');
    $id_federacion = !empty($_POST['federacion']) ? intval($_POST['federacion']) : 'NULL';

    $query = "INSERT INTO jueces (nombre, apellidos, licencia, federacion, activo) 
              VALUES ('$nombre', '$apellidos', '$licencia', $id_federacion, 1)";
    
    if(mysqli_query($connection, $query)){
        $_SESSION['correcto'] = 'Juez añadido correctamente al censo.';
        write_log("Nuevo juez creado: $nombre $apellidos", "SUCCESS");
    } else {
        $_SESSION['estado'] = 'Error al añadir juez: ' . mysqli_error($connection);
    }
    header('Location: jueces.php');
}

// Actualizar registro
if(isset($_POST['update_btn'])){
    $id = $_POST['edit_id'];
    $nombre = mysqli_real_escape_string($connection, $_POST['edit_nombre']);
    $apellidos = mysqli_real_escape_string($connection, $_POST['edit_apellidos']);
    $licencia = mysqli_real_escape_string($connection, $_POST['edit_licencia'] ?? '');
    $id_federacion = !empty($_POST['edit_federacion']) ? intval($_POST['edit_federacion']) : 'NULL';
    $activo = isset($_POST['activo']) ? 1 : 0;

    $query = "UPDATE jueces SET 
                nombre = '$nombre', 
                apellidos = '$apellidos', 
                licencia = '$licencia', 
                federacion = $id_federacion, 
                activo = $activo 
              WHERE id = '$id'";

    if(mysqli_query($connection, $query)){
        $_SESSION['correcto'] = 'Ficha de juez actualizada con éxito.';
        write_log("Juez #$id actualizado: $nombre $apellidos", "SUCCESS");
    } else {
        $_SESSION['estado'] = 'Error al actualizar: ' . mysqli_error($connection);
    }
    header('Location: jueces.php');
}

// Borrar registro
if(isset($_POST['delete_btn'])){
    $id = $_POST['delete_id'];
    $query = "DELETE FROM jueces WHERE id = '$id'";
    if(mysqli_query($connection, $query)){
        $_SESSION['correcto'] = 'Juez eliminado del sistema.';
    } else {
        $_SESSION['estado'] = 'Error al eliminar: ' . mysqli_error($connection);
    }
    header('Location: jueces.php');
}
?>
