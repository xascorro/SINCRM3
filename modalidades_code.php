<?php
include('security.php');

// 1. AÑADIR NUEVA MODALIDAD
if(isset($_POST['save_btn'])){
    $nombre = mysqli_real_escape_string($connection, trim($_POST['nombre']));
    $nombre_corto = mysqli_real_escape_string($connection, mb_strtoupper(trim($_POST['nombre_corto']), 'UTF-8'));
    $orden = intval($_POST['orden'] ?? 0);
    $numero_participantes = intval($_POST['numero_participantes'] ?? 1);
    $numero_reservas = intval($_POST['numero_reservas'] ?? 0);
    $coach_card = (isset($_POST['coach_card']) && $_POST['coach_card'] == 'si') ? 'si' : 'no';
    $activo = 1;

    if(empty($nombre) || empty($nombre_corto)){
        $_SESSION['estado'] = 'Por favor complete el nombre y código corto de la modalidad.';
        header('Location: modalidades.php');
        exit();
    }

    $query = "INSERT INTO modalidades (nombre, nombre_corto, orden, numero_participantes, numero_reservas, coach_card, activo) 
              VALUES ('$nombre', '$nombre_corto', $orden, $numero_participantes, $numero_reservas, '$coach_card', $activo)";
    $query_run = mysqli_query($connection, $query);

    if($query_run){
        $new_id = mysqli_insert_id($connection);
        write_log("Modalidad creada ID#$new_id: $nombre [$nombre_corto] (Titulares: $numero_participantes, Reservas: $numero_reservas)", 'SUCCESS');
        $_SESSION['correcto'] = 'Modalidad registrada con éxito.';
    } else {
        write_log("Fallo al crear modalidad $nombre: " . mysqli_error($connection), 'ERROR');
        $_SESSION['estado'] = 'Error al registrar la modalidad: ' . mysqli_error($connection);
    }
    header('Location: modalidades.php');
    exit();
}

// 2. ACTUALIZAR MODALIDAD
if(isset($_POST['update_btn'])){
    $id = intval($_POST['edit_id']);
    $nombre = mysqli_real_escape_string($connection, trim($_POST['edit_nombre']));
    $nombre_corto = mysqli_real_escape_string($connection, mb_strtoupper(trim($_POST['edit_nombre_corto']), 'UTF-8'));
    $orden = intval($_POST['edit_orden'] ?? 0);
    $numero_participantes = intval($_POST['edit_numero_participantes'] ?? 1);
    $numero_reservas = intval($_POST['edit_numero_reservas'] ?? 0);
    $coach_card = (isset($_POST['edit_coach_card']) && $_POST['edit_coach_card'] == 'si') ? 'si' : 'no';
    $activo = isset($_POST['edit_activo']) ? 1 : 0;

    if(empty($nombre) || empty($nombre_corto)){
        $_SESSION['estado'] = 'El nombre y el código corto no pueden estar vacíos.';
        header('Location: modalidades.php');
        exit();
    }

    $query = "UPDATE modalidades SET 
                nombre = '$nombre', 
                nombre_corto = '$nombre_corto', 
                orden = $orden, 
                numero_participantes = $numero_participantes, 
                numero_reservas = $numero_reservas, 
                coach_card = '$coach_card', 
                activo = $activo 
              WHERE id = $id";
    $query_run = mysqli_query($connection, $query);

    if($query_run){
        write_log("Modalidad ID#$id actualizada: $nombre [$nombre_corto] (Activo: $activo)", 'SUCCESS');
        $_SESSION['correcto'] = 'Modalidad actualizada correctamente.';
    } else {
        write_log("Fallo al actualizar modalidad ID#$id: " . mysqli_error($connection), 'ERROR');
        $_SESSION['estado'] = 'Error al actualizar la modalidad: ' . mysqli_error($connection);
    }
    header('Location: modalidades.php');
    exit();
}

// 3. CAMBIAR ESTADO ACTIVO / INACTIVO
if(isset($_POST['toggle_status_btn'])){
    $id = intval($_POST['toggle_id']);
    $current_status = intval($_POST['current_status']);
    $new_status = ($current_status == 1) ? 0 : 1;

    $query = "UPDATE modalidades SET activo = $new_status WHERE id = $id";
    $query_run = mysqli_query($connection, $query);

    if($query_run){
        $status_label = ($new_status == 1) ? 'activada' : 'desactivada';
        write_log("Modalidad ID#$id $status_label en el sistema", 'WARNING');
        $_SESSION['correcto'] = "Modalidad $status_label correctamente.";
    } else {
        write_log("Error al cambiar estado de modalidad ID#$id: " . mysqli_error($connection), 'ERROR');
        $_SESSION['estado'] = 'Error al cambiar estado: ' . mysqli_error($connection);
    }
    header('Location: modalidades.php');
    exit();
}

// 4. ELIMINAR MODALIDAD
if(isset($_POST['delete_btn'])){
    $id = intval($_POST['delete_id']);

    // Comprobar si está en uso en fases o rutinas
    $check_fases = mysqli_query($connection, "SELECT COUNT(*) as total FROM fases WHERE id_modalidad = $id");
    $count_fases = ($check_fases) ? mysqli_fetch_assoc($check_fases)['total'] : 0;

    $check_reglas = mysqli_query($connection, "SELECT COUNT(*) as total FROM reglas_competicion WHERE id_modalidad = $id");
    $count_reglas = ($check_reglas) ? mysqli_fetch_assoc($check_reglas)['total'] : 0;

    if($count_fases > 0 || $count_reglas > 0){
        // Si está en uso, hacer baja lógica (desactivar) para preservar integridad
        $query = "UPDATE modalidades SET activo = 0 WHERE id = $id";
        $query_run = mysqli_query($connection, $query);
        write_log("Modalidad ID#$id en uso ($count_fases fases, $count_reglas reglas): Desactivada lógicamente", 'WARNING');
        $_SESSION['correcto'] = 'La modalidad está en uso en fases/reglas, por lo que se ha desactivado para proteger el historial.';
    } else {
        $query = "DELETE FROM modalidades WHERE id = $id";
        $query_run = mysqli_query($connection, $query);
        if($query_run){
            write_log("Modalidad ID#$id eliminada de la base de datos", 'WARNING');
            $_SESSION['correcto'] = 'Modalidad eliminada correctamente.';
        } else {
            write_log("Error al eliminar modalidad ID#$id: " . mysqli_error($connection), 'ERROR');
            $_SESSION['estado'] = 'Error al eliminar: ' . mysqli_error($connection);
        }
    }
    header('Location: modalidades.php');
    exit();
}
?>
