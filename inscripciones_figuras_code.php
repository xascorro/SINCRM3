<?php
include('security.php');

// Añadir registro (Mantenemos la lógica de guardado múltiple si existe)
if(isset($_POST['save_btn'])){
	$id_competicion = intval($_POST['id_competicion']);
	$id_nadadora = intval($_POST['id_nadadora']);
	$id_fase = intval($_POST['id_fase']);
    $redirect = !empty($_POST['return_to']) ? $_POST['return_to'] : 'inscripciones_figuras.php';

    if ($id_nadadora <= 0) {
        $_SESSION['estado'] = 'Error: Debes seleccionar una nadadora válida.';
        header("Location: $redirect");
        exit();
    }

    // Buscamos todas las fases de la misma categoría en esta competición
    $q_cat = "SELECT id_categoria FROM fases WHERE id = '$id_fase'";
    $res_cat = mysqli_query($connection, $q_cat);
    $row_cat = mysqli_fetch_row($res_cat);
    $id_cat = intval($row_cat[0] ?? 0);

    // Evitar registros duplicados para la misma nadadora en esta categoría y competición
    $q_check_dup = "SELECT 1 FROM inscripciones_figuras i 
                    JOIN fases f ON i.id_fase = f.id 
                    WHERE i.id_nadadora = '$id_nadadora' 
                      AND f.id_categoria = '$id_cat' 
                      AND f.id_competicion = '$id_competicion' 
                    LIMIT 1";
    $res_dup = mysqli_query($connection, $q_check_dup);
    if ($res_dup && mysqli_num_rows($res_dup) > 0) {
        $_SESSION['estado'] = 'Aviso: La nadadora ya está inscrita en esta categoría.';
        header("Location: $redirect");
        exit();
    }
    
    $q_fases = "SELECT id FROM fases WHERE id_categoria = '$id_cat' AND id_competicion = '$id_competicion'";
    $res_fases = mysqli_query($connection, $q_fases);
    
    while($f = mysqli_fetch_assoc($res_fases)){
        $fid = $f['id'];
        $query = "INSERT INTO inscripciones_figuras (id_fase, id_nadadora, id_competicion, orden) VALUES ('$fid', '$id_nadadora', '$id_competicion', 0)";
        mysqli_query($connection, $query);
    }
    
    // Log detallado
    $q_n = mysqli_query($connection, "SELECT nombre, apellidos FROM nadadoras WHERE id = '$id_nadadora'");
    $nad = mysqli_fetch_assoc($q_n);
    $nombre_completo = ($nad) ? $nad['apellidos'].", ".$nad['nombre'] : "ID #$id_nadadora";

    write_log("Inscripción realizada para $nombre_completo en competición #$id_competicion", "SUCCESS");
	$_SESSION['correcto'] = 'Inscripción completada en todas las figuras de la categoría.';
	header("Location: $redirect");
    exit();
}

// ACTUALIZAR ORDEN MANUAL (EXHIBICIÓN / PRESWIMMER)
if(isset($_POST['update_orden_btn'])){
    $id_registro = mysqli_real_escape_string($connection, $_POST['update_id']);
    $nuevo_orden = mysqli_real_escape_string($connection, $_POST['update_orden']);

    // 1. Identificar nadadora, competición y categoría del registro
    $q_info = "SELECT i.id_nadadora, f.id_competicion, f.id_categoria 
               FROM inscripciones_figuras i 
               JOIN fases f ON i.id_fase = f.id 
               WHERE i.id = '$id_registro'";
    $res_info = mysqli_query($connection, $q_info);
    
    if($res_info && $info = mysqli_fetch_assoc($res_info)){
        $nadadora = $info['id_nadadora'];
        $comp = $info['id_competicion'];
        $cat = $info['id_categoria'];
        
        // 2. Actualizar el orden en todas las figuras de esa categoría para esa nadadora
        $q_upd = "UPDATE inscripciones_figuras 
                  SET orden = '$nuevo_orden' 
                  WHERE id_nadadora = '$nadadora' 
                  AND id_fase IN (SELECT id FROM fases WHERE id_competicion = '$comp' AND id_categoria = '$cat')";
        
        if(mysqli_query($connection, $q_upd)){
            // Log detallado
            $q_n = mysqli_query($connection, "SELECT nombre, apellidos FROM nadadoras WHERE id = '$nadadora'");
            $nad = mysqli_fetch_assoc($q_n);
            $nombre_completo = $nad['apellidos'].", ".$nad['nombre'];

            write_log("Orden manual actualizado a $nuevo_orden para $nombre_completo (Figuras, Competición #$comp)", "INFO");
            $_SESSION['correcto'] = 'Orden actualizado correctamente.';
        } else {
            $_SESSION['estado'] = 'Error al actualizar orden: ' . mysqli_error($connection);
        }
    }
    header('Location: inscripciones_figuras.php');
    exit();
}

// BORRADO MASIVO INTELIGENTE (4 FIGURAS DE GOLPE)
if(isset($_POST['delete_btn'])){
	$id_registro = mysqli_real_escape_string($connection, $_POST['delete_id']);
    
    // 1. Identificar nadadora y competición a partir del ID que queremos borrar
    $q_info = "SELECT i.id_nadadora, f.id_competicion, f.id_categoria 
               FROM inscripciones_figuras i 
               JOIN fases f ON i.id_fase = f.id 
               WHERE i.id = '$id_registro'";
    $res_info = mysqli_query($connection, $q_info);
    
    if($res_info && $info = mysqli_fetch_assoc($res_info)){
        $nadadora = $info['id_nadadora'];
        $comp = $info['id_competicion'];
        $cat = $info['id_categoria'];
        
        // Obtener nombre antes del borrado para el log
        $q_n = mysqli_query($connection, "SELECT nombre, apellidos FROM nadadoras WHERE id = '$nadadora'");
        $nad = mysqli_fetch_assoc($q_n);
        $nombre_completo = $nad['apellidos'].", ".$nad['nombre'];

        // 2. Borrar todos los registros de esa nadadora en las fases de esa competición/categoría
        $q_del = "DELETE FROM inscripciones_figuras 
                  WHERE id_nadadora = '$nadadora' 
                  AND id_fase IN (SELECT id FROM fases WHERE id_competicion = '$comp' AND id_categoria = '$cat')";
        
        if(mysqli_query($connection, $q_del)){
            write_log("Inscripción completa eliminada para $nombre_completo en competición #$comp", "WARNING");
            $_SESSION['correcto'] = 'Se ha eliminado la inscripción completa de la nadadora.';
        } else {
            $_SESSION['estado'] = 'Error al eliminar: ' . mysqli_error($connection);
        }
    }
    $redirect = !empty($_POST['return_to']) ? $_POST['return_to'] : 'inscripciones_figuras.php';
	header("Location: $redirect");
    exit();
}

// Alta / Baja (Individual o Masiva según prefieras, mantendré la lógica de individual por ahora para ajustes finos)
if(isset($_POST['baja_btn'])){
	$id = $_POST['baja_id'];
	$query = "UPDATE inscripciones_figuras SET baja = 'si' WHERE id = '$id'";
	mysqli_query($connection, $query);
    write_log("Baja registrada para inscripción de figuras #$id", "INFO");
    $redirect = !empty($_POST['return_to']) ? $_POST['return_to'] : 'inscripciones_figuras.php';
	header("Location: $redirect");
    exit();
}

if(isset($_POST['alta_btn'])){
	$id = $_POST['alta_id'];
	$query = "UPDATE inscripciones_figuras SET baja = 'no' WHERE id = '$id'";
	mysqli_query($connection, $query);
    write_log("Alta registrada para inscripción de figuras #$id", "INFO");
    $redirect = !empty($_POST['return_to']) ? $_POST['return_to'] : 'inscripciones_figuras.php';
	header("Location: $redirect");
    exit();
}

// Fallback de seguridad: Si no se presionó ningún botón reconocido, redirigir sin quedar en blanco
$fallback = !empty($_POST['return_to']) ? $_POST['return_to'] : (!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'inscripciones.php');
header("Location: $fallback");
exit();
?>