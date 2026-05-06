<?php
include('security.php');

// Añadir registro
if(isset($_POST['save_btn'])){
    $nombre = mysqli_real_escape_string($connection, $_POST['nombre']);
    $edad_minima = mysqli_real_escape_string($connection, $_POST['edad_minima']);
    $edad_maxima = mysqli_real_escape_string($connection, $_POST['edad_maxima']);

    $query = "INSERT INTO categorias (nombre, edad_minima, edad_maxima, activo) VALUES ('$nombre', '$edad_minima', '$edad_maxima', 1)";
    $query_run = mysqli_query($connection, $query);

    if($query_run){
        write_log("Categoría creada: $nombre ($edad_minima-$edad_maxima años)", 'SUCCESS');
        $_SESSION['correcto'] = 'Registro añadido con éxito';
    } else {
        write_log("Fallo al crear categoría $nombre: " . mysqli_error($connection), 'ERROR');
        $_SESSION['estado'] = 'Error. Registro no añadido <br>'.mysqli_error($connection);
    }
    header('Location: categorias.php');
}

// Actualizar registro
if(isset($_POST['update_btn'])){
    $id = mysqli_real_escape_string($connection, $_POST['edit_id']);
    $nombre = mysqli_real_escape_string($connection, $_POST['edit_nombre']);
    $edad_minima = mysqli_real_escape_string($connection, $_POST['edit_edad_minima']);
    $edad_maxima = mysqli_real_escape_string($connection, $_POST['edit_edad_maxima']);
    $activo = isset($_POST['edit_activo']) ? 1 : 0;

    $query = "UPDATE categorias SET nombre='$nombre', edad_minima='$edad_minima', edad_maxima='$edad_maxima', activo='$activo' WHERE id='$id'"; 
    $query_run = mysqli_query($connection, $query);

    if($query_run){
        write_log("Categoría ID#$id actualizada: $nombre ($activo)", 'SUCCESS');
        $_SESSION['correcto'] = 'Datos actualizados con éxito';
    } else {
        write_log("Fallo al actualizar categoría ID#$id: " . mysqli_error($connection), 'ERROR');
        $_SESSION['estado'] = 'Error. Los datos no se han actualizado <br>'.mysqli_error($connection);
    }
    header('Location: categorias.php');
}

// Borrado lógico (Desactivar)
if(isset($_POST['delete_btn'])){
    $id = mysqli_real_escape_string($connection, $_POST['delete_id']);

    $query = "UPDATE categorias SET activo = 0 WHERE id ='$id'"; 
    $query_run = mysqli_query($connection, $query);

    if($query_run){
        write_log("Categoría ID#$id desactivada (Borrado lógico)", 'WARNING');
        $_SESSION['correcto'] = 'Registro desactivado con éxito';
    } else {
        write_log("Fallo al desactivar categoría ID#$id: " . mysqli_error($connection), 'ERROR');
        $_SESSION['estado'] = 'Error. El registro no se ha podido desactivar <br>'.mysqli_error($connection);
    }
    header('Location: categorias.php');
}
?>
