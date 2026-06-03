<?php
include('security.php');

// Añadir registro
if(isset($_POST['save_btn'])){
    $nombre = mysqli_real_escape_string($connection, mb_strtoupper($_POST['nombre'], 'UTF-8'));
    $nombre_corto = mysqli_real_escape_string($connection, mb_strtoupper($_POST['nombre_corto'], 'UTF-8'));
    $orden = mysqli_real_escape_string($connection, $_POST['orden']);
    $edad_minima = mysqli_real_escape_string($connection, $_POST['edad_minima']);
    $edad_maxima = mysqli_real_escape_string($connection, $_POST['edad_maxima']);

    $query = "INSERT INTO categorias (nombre, nombre_corto, orden, edad_minima, edad_maxima, activo) VALUES ('$nombre', '$nombre_corto', '$orden', '$edad_minima', '$edad_maxima', 1)";
    $query_run = mysqli_query($connection, $query);

    if($query_run){
        write_log("Categoría creada: $nombre [$nombre_corto] (Orden: $orden)", 'SUCCESS');
        $_SESSION['correcto'] = 'Categoría técnica registrada con éxito.';
    } else {
        write_log("Fallo al crear categoría $nombre: " . mysqli_error($connection), 'ERROR');
        $_SESSION['estado'] = 'Error al procesar el alta técnica.';
    }
    header('Location: categorias.php');
}

// Actualizar registro
if(isset($_POST['update_btn'])){
    $id = mysqli_real_escape_string($connection, $_POST['edit_id']);
    $nombre = mysqli_real_escape_string($connection, mb_strtoupper($_POST['edit_nombre'], 'UTF-8'));
    $nombre_corto = mysqli_real_escape_string($connection, mb_strtoupper($_POST['edit_nombre_corto'], 'UTF-8'));
    $orden = mysqli_real_escape_string($connection, $_POST['edit_orden']);
    $edad_minima = mysqli_real_escape_string($connection, $_POST['edit_edad_minima']);
    $edad_maxima = mysqli_real_escape_string($connection, $_POST['edit_edad_maxima']);
    $activo = isset($_POST['edit_activo']) ? 1 : 0;

    $query = "UPDATE categorias SET nombre='$nombre', nombre_corto='$nombre_corto', orden='$orden', edad_minima='$edad_minima', edad_maxima='$edad_maxima', activo='$activo' WHERE id='$id'"; 
    $query_run = mysqli_query($connection, $query);

    if($query_run){
        write_log("Categoría ID#$id actualizada: $nombre [$nombre_corto]", 'SUCCESS');
        $_SESSION['correcto'] = 'Parámetros actualizados correctamente.';
    } else {
        write_log("Fallo al actualizar categoría ID#$id: " . mysqli_error($connection), 'ERROR');
        $_SESSION['estado'] = 'Error al actualizar la configuración.';
    }
    header('Location: categorias.php');
}

// Borrado lógico (Desactivar)
if(isset($_POST['delete_btn'])){
    $id = mysqli_real_escape_string($connection, $_POST['delete_id']);

    $query = "UPDATE categorias SET activo = 0 WHERE id ='$id'"; 
    $query_run = mysqli_query($connection, $query);

    if($query_run){
        write_log("Categoría ID#$id desactivada del sistema", 'WARNING');
        $_SESSION['correcto'] = 'Categoría marcada como inactiva.';
    } else {
        write_log("Fallo al desactivar categoría ID#$id: " . mysqli_error($connection), 'ERROR');
        $_SESSION['estado'] = 'Error al modificar el estado del nivel.';
    }
    header('Location: categorias.php');
}
?>
