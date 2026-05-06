<?php
include('security.php');

// Añadir registro
if(isset($_POST['save_btn'])){
    $numero = mysqli_real_escape_string($connection, $_POST['numero']);
    $nombre = mysqli_real_escape_string($connection, $_POST['nombre']);
    $grado_dificultad = mysqli_real_escape_string($connection, $_POST['grado_dificultad']);

    $query = "INSERT INTO figuras (numero, nombre, grado_dificultad, activo) VALUES ('$numero', '$nombre', '$grado_dificultad', 1)";
    $query_run = mysqli_query($connection, $query);

    if($query_run){
        write_log("Figura creada: $numero - $nombre (GD: $grado_dificultad)", 'SUCCESS');
        $_SESSION['correcto'] = 'Registro añadido con éxito';
    } else {
        write_log("Fallo al crear figura $numero: " . mysqli_error($connection), 'ERROR');
        $_SESSION['estado'] = 'Error. Registro no añadido <br>'.mysqli_error($connection);
    }
    header('Location: figuras.php');
}

// Actualizar registro
if(isset($_POST['update_btn'])){
    $id = mysqli_real_escape_string($connection, $_POST['edit_id']);
    $numero = mysqli_real_escape_string($connection, $_POST['edit_numero']);
    $nombre = mysqli_real_escape_string($connection, $_POST['edit_nombre']);
    $grado_dificultad = mysqli_real_escape_string($connection, $_POST['edit_grado_dificultad']);    
    $descripcion = mysqli_real_escape_string($connection, $_POST['descripcion']);    
    $activo = isset($_POST['edit_activo']) ? 1 : 0;

    $query = "UPDATE figuras SET numero ='$numero', nombre='$nombre', grado_dificultad='$grado_dificultad', descripcion = '$descripcion', activo='$activo' WHERE id='$id'"; 
    $query_run = mysqli_query($connection, $query);

    if($query_run){
        write_log("Figura ID#$id actualizada: $numero ($activo)", 'SUCCESS');
        $_SESSION['correcto'] = 'Datos actualizados con éxito';
    } else {
        write_log("Fallo al actualizar figura ID#$id: " . mysqli_error($connection), 'ERROR');
        $_SESSION['estado'] = 'Error. Los datos no se han actualizado <br>'.mysqli_error($connection);
    }
    header('Location: figuras.php');
}

// Borrado lógico (Desactivar)
if(isset($_POST['delete_btn'])){
    $id = mysqli_real_escape_string($connection, $_POST['delete_id']);

    $query = "UPDATE figuras SET activo = 0 WHERE id ='$id'"; 
    $query_run = mysqli_query($connection, $query);

    if($query_run){
        write_log("Figura ID#$id desactivada (Borrado lógico)", 'WARNING');
        $_SESSION['correcto'] = 'Registro desactivado con éxito';
    } else {
        write_log("Fallo al desactivar figura ID#$id: " . mysqli_error($connection), 'ERROR');
        $_SESSION['estado'] = 'Error. El registro no se ha podido desactivar <br>'.mysqli_error($connection);
    }
    header('Location: figuras.php');
}
?>
