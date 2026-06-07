<?php
include('security.php');

// AÑADIR PÁGINA
if(isset($_POST['save_btn'])){
    $archivo = mysqli_real_escape_string($connection, $_POST['archivo']);
    $nombre = mysqli_real_escape_string($connection, $_POST['nombre']);
    $grupo = mysqli_real_escape_string($connection, $_POST['grupo']);

    $query = "INSERT INTO paginas_sistema (archivo, nombre, grupo) VALUES ('$archivo', '$nombre', '$grupo')";
    if(mysqli_query($connection, $query)){
        write_log("Nueva página registrada en catálogo: $archivo ($nombre)", "SUCCESS");
        $_SESSION['correcto'] = 'Página registrada correctamente';
    } else {
        $_SESSION['estado'] = 'Error al registrar: ' . mysqli_error($connection);
    }
    header('Location: paginas.php');
    exit();
}

// ACTUALIZAR PÁGINA
if(isset($_POST['update_btn'])){
    $id = (int)$_POST['edit_id'];
    $archivo = mysqli_real_escape_string($connection, $_POST['edit_archivo']);
    $nombre = mysqli_real_escape_string($connection, $_POST['edit_nombre']);
    $grupo = mysqli_real_escape_string($connection, $_POST['edit_grupo']);

    $query = "UPDATE paginas_sistema SET archivo='$archivo', nombre='$nombre', grupo='$grupo' WHERE id='$id'";
    if(mysqli_query($connection, $query)){
        write_log("Página del catálogo actualizada (ID: $id): $archivo", "INFO");
        $_SESSION['correcto'] = 'Cambios guardados correctamente';
    } else {
        $_SESSION['estado'] = 'Error al actualizar: ' . mysqli_error($connection);
    }
    header('Location: paginas.php');
    exit();
}

// BORRAR PÁGINA
if(isset($_POST['delete_btn'])){
    $id = (int)$_POST['delete_id'];
    
    $query = "DELETE FROM paginas_sistema WHERE id ='$id'";
    if(mysqli_query($connection, $query)){
        write_log("Página eliminada del catálogo técnico (ID: $id)", "WARNING");
        $_SESSION['correcto'] = 'Página eliminada del catálogo';
    } else {
        $_SESSION['estado'] = 'No se pudo eliminar la página.';
    }
    header('Location: paginas.php');
    exit();
}
?>
