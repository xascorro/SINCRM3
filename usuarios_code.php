<?php
include('security.php');

// AÑADIR REGISTRO
if(isset($_POST['save_btn'])){
	$username = mysqli_real_escape_string($connection, $_POST['username']);
	$email = mysqli_real_escape_string($connection, $_POST['email']);
	$password = $_POST['password'];
	$r_password = $_POST['r_password'];
	$club = mysqli_real_escape_string($connection, $_POST['club']);
	$id_rol = mysqli_real_escape_string($connection, $_POST['id_rol']);
    
	if($password != $r_password){
		$_SESSION['estado'] = 'Las contraseñas no coinciden.';
		header('Location: usuarios.php');
        exit();
	} else {
        // SEGURIDAD: Siempre hashear la contraseña
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
		$query = "INSERT INTO usuarios (username, email, hash, club, id_rol, email_confirmado) VALUES ('$username', '$email', '$hashed_password', '$club', '$id_rol', 1)";
		$query_run = mysqli_query($connection, $query);
        
		if($query_run){
            write_log("Nuevo usuario creado: $email ($username)", "SUCCESS");
			$_SESSION['correcto'] = 'Usuario creado con éxito';

            // ENVIO EMAIL AL USUARIO (Bienvenida y Estamento)
            if($id_rol != 6) { // Si no es invitado
                require_once 'includes/email_functions.php';
                
                $q_rol = mysqli_query($connection, "SELECT nombre FROM roles WHERE id = '$id_rol'");
                $rol_data = mysqli_fetch_assoc($q_rol);
                $nombre_rol = $rol_data['nombre'] ?? 'Usuario';

                $subjectUser = 'Bienvenido a SINCRM - Acceso Configurado';
                $bodyUser = "
                <h2 style='color: #3b82f6;'>¡Bienvenido a la Plataforma!</h2>
                <p>Hola <strong>".$username."</strong>,</p>
                <p>Un administrador ha creado tu cuenta en SINCRM y ya puedes acceder al sistema.</p>
                <div style='background: #f8fafc; padding: 20px; border-radius: 15px; border-left: 5px solid #3b82f6; margin: 20px 0;'>
                    <p style='margin: 0; font-weight: bold; color: #1e293b;'>Estamento asignado: <span style='color: #2563eb;'>".$nombre_rol."</span></p>
                    <p style='margin: 5px 0 0 0; font-size: 13px; color: #64748b;'>Utiliza tu email y la contraseña proporcionada por el administrador.</p>
                </div>
                <div style='text-align: center; margin-top: 30px;'>
                    <a href='https://" . $_SERVER['HTTP_HOST'] . "/login.php' style='display: inline-block; padding: 12px 30px; background-color: #1e293b; color: white; text-decoration: none; border-radius: 12px; font-weight: bold;'>Entrar en SINCRM</a>
                </div>";
                enviar_email($email, $subjectUser, $bodyUser);
            }
		} else {
            write_log("Error al crear usuario ($email): " . mysqli_error($connection), "ERROR");
			$_SESSION['estado'] = 'Error al registrar en la base de datos.';
		}
        header('Location: usuarios.php');
        exit();
	}
}

// ACTUALIZAR REGISTRO
if(isset($_POST['update_btn'])){
	$id = mysqli_real_escape_string($connection, $_POST['edit_id']);
	$username = mysqli_real_escape_string($connection, $_POST['edit_username']);
	$email = mysqli_real_escape_string($connection, $_POST['edit_email']);
	$telefono = mysqli_real_escape_string($connection, $_POST['edit_telefono']);
	$comentario = mysqli_real_escape_string($connection, $_POST['edit_comentario']);
	$id_rol = mysqli_real_escape_string($connection, $_POST['edit_rol']);
	$id_juez_v3 = mysqli_real_escape_string($connection, $_POST['id_juez_v3'] ?? '');
	$id_club = mysqli_real_escape_string($connection, $_POST['club']); // del select include
    
	$password = $_POST['edit_password'];
	$r_password = $_POST['edit_r_password'];

    // Lógica de actualización de contraseña
    $query_parts = [
        "username='$username'",
        "email='$email'",
        "telefono='$telefono'",
        "comentario='$comentario'",
        "id_rol='$id_rol'",
        "id_juez_v3=" . ($id_juez_v3 != '' ? "'$id_juez_v3'" : "NULL"),
        "club='$id_club'"
    ];

    // Obtener estado anterior para ver si hay cambios que requieran notificación
    $q_old = mysqli_query($connection, "SELECT id_rol, activo FROM usuarios WHERE id = '$id'");
    $old_data = mysqli_fetch_assoc($q_old);
    $old_rol = $old_data['id_rol'];
    $old_activo = $old_data['activo'];
    $new_activo = isset($_POST['activo']) ? 1 : 0;

    if(!empty($password)){
        if($password != $r_password){
            $_SESSION['estado'] = 'Las nuevas contraseñas no coinciden.';
            header('Location: usuarios.php');
            exit();
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $query_parts[] = "hash='$hashed'";
        }
    }

    $query = "UPDATE usuarios SET " . implode(", ", $query_parts) . ", activo = " . $new_activo . " WHERE id='$id'";
    $query_run = mysqli_query($connection, $query);

    if($query_run){
        write_log("Usuario actualizado (ID: $id): $email", "INFO");

        // NOTIFICACIÓN POR EMAIL SI SE CAMBIA EL ROL O SE ACTIVA
        // Se notifica si:
        // 1. Pasa de Inactivo a Activo
        // 2. Cambia de rol (y ya estaba activo o se está activando) y el nuevo rol no es Invitado (6)
        if (($old_activo == 0 && $new_activo == 1) || ($new_activo == 1 && $old_rol != $id_rol && $id_rol != 6)) {
            require_once 'includes/email_functions.php';
            
            // Obtener nombre del nuevo rol
            $q_rol = mysqli_query($connection, "SELECT nombre FROM roles WHERE id = '$id_rol'");
            $rol_data = mysqli_fetch_assoc($q_rol);
            $nombre_rol = $rol_data['nombre'] ?? 'Usuario';

            $subjectUser = '¡Actualización de Acceso! - SINCRM';
            $bodyUser = "
            <h2 style='color: #3b82f6;'>Acceso Actualizado</h2>
            <p>Hola <strong>".$username."</strong>,</p>
            <p>Te informamos que se ha actualizado tu perfil de acceso en la plataforma SINCRM.</p>
            <div style='background: #f8fafc; padding: 20px; border-radius: 15px; border-left: 5px solid #3b82f6; margin: 20px 0;'>
                <p style='margin: 0; font-weight: bold; color: #1e293b;'>Nuevo Estamento: <span style='color: #2563eb;'>".$nombre_rol."</span></p>
                <p style='margin: 5px 0 0 0; font-size: 13px; color: #64748b;'>Ya puedes acceder con tus credenciales habituales.</p>
            </div>
            <div style='text-align: center; margin-top: 30px;'>
                <a href='https://" . $_SERVER['HTTP_HOST'] . "/login.php' style='display: inline-block; padding: 12px 30px; background-color: #1e293b; color: white; text-decoration: none; border-radius: 12px; font-weight: bold;'>Entrar en SINCRM</a>
            </div>";
            enviar_email($email, $subjectUser, $bodyUser);
            write_log("Email de actualización enviado a $email (Rol: $nombre_rol)", "INFO");
        }

        $_SESSION['correcto'] = 'Perfil de usuario actualizado';
    } else {
        write_log("Error al actualizar usuario (ID: $id): " . mysqli_error($connection), "ERROR");
        $_SESSION['estado'] = 'Error al actualizar base de datos.';
    }
    header('Location: usuarios.php');
    exit();
}

// CAMBIO RÁPIDO DE ESTADO (ACTIVAR/DESACTIVAR)
if(isset($_POST['toggle_status_btn'])){
    $user_id = mysqli_real_escape_string($connection, $_POST['user_id']);
    $new_status = ($_POST['current_status'] == 1) ? 0 : 1;
    
    $query = "UPDATE usuarios SET activo = $new_status WHERE id = '$user_id'";
    $query_run = mysqli_query($connection, $query);
    
    if($query_run){
        $msg = $new_status ? "Usuario activado" : "Usuario desactivado";
        write_log("$msg (ID: $user_id)", "WARNING");

        // ENVIO EMAIL AL USUARIO CUANDO SE ACTIVA
        if($new_status == 1){
            $u_query = mysqli_query($connection, "SELECT u.email, u.username, r.nombre as rol FROM usuarios u LEFT JOIN roles r ON u.id_rol = r.id WHERE u.id = '$user_id'");
            $u_row = mysqli_fetch_assoc($u_query);
            if($u_row){
                require_once 'includes/email_functions.php';
                $toUser = $u_row['email'];
                $nombre_rol = $u_row['rol'] ?? 'Usuario';
                $subjectUser = '¡Cuenta Activada! - SINCRM';
                $bodyUser = "
                <h2 style='color: #10b981;'>Acceso Concedido</h2>
                <p>Hola <strong>".$u_row['username']."</strong>,</p>
                <p>Te informamos que un administrador ha aprobado tu solicitud y ya puedes acceder a la plataforma.</p>
                <div style='background: #f0fdf4; padding: 20px; border-radius: 15px; border-left: 5px solid #10b981; margin: 20px 0;'>
                    <p style='margin: 0; font-weight: bold; color: #064e3b;'>Estamento asignado: <span style='color: #059669;'>".$nombre_rol."</span></p>
                    <p style='margin: 5px 0 0 0; font-size: 13px; color: #065f46;'>Ya puedes empezar a gestionar tus competiciones y nadadoras.</p>
                </div>
                <div style='text-align: center; margin-top: 30px;'>
                    <a href='https://" . $_SERVER['HTTP_HOST'] . "/login.php' style='display: inline-block; padding: 12px 30px; background-color: #1e293b; color: white; text-decoration: none; border-radius: 12px; font-weight: bold;'>Entrar en SINCRM</a>
                </div>";
                enviar_email($toUser, $subjectUser, $bodyUser);
            }
        }

        $_SESSION['correcto'] = $msg . " con éxito.";
    } else {
        $_SESSION['estado'] = "Error al cambiar el estado del usuario.";
    }
    header('Location: usuarios.php');
    exit();
}

// BORRAR REGISTRO
if(isset($_POST['delete_btn'])){
	$id = mysqli_real_escape_string($connection, $_POST['delete_id']);

    // Obtener info para el log antes de borrar
    $q_data = mysqli_query($connection, "SELECT email FROM usuarios WHERE id = '$id'");
    $u_data = mysqli_fetch_assoc($q_data);
    $email_borrado = $u_data['email'] ?? 'ID '.$id;

	$query = "DELETE FROM usuarios WHERE id ='$id'";
	$query_run = mysqli_query($connection, $query);
    
	if($query_run){
        write_log("Cuenta de usuario eliminada: $email_borrado", "WARNING");
		$_SESSION['correcto'] = 'Usuario eliminado correctamente';
	} else {
        write_log("Error al eliminar usuario ($email_borrado): " . mysqli_error($connection), "ERROR");
		$_SESSION['estado'] = 'No se pudo eliminar el usuario.';
	}
    header('Location: usuarios.php');
    exit();
}

// VERIFICAR EMAIL MANUALMENTE
if(isset($_POST['verify_btn'])){
    $id = mysqli_real_escape_string($connection, $_POST['verify_id']);
    
    $query = "UPDATE usuarios SET email_confirmado = 1, token_confirmacion = NULL WHERE id = '$id'";
    $query_run = mysqli_query($connection, $query);
    
    if($query_run){
        write_log("Email verificado manualmente por admin (ID: $id)", "SUCCESS");
        $_SESSION['correcto'] = 'Email verificado manualmente.';
    } else {
        write_log("Error al verificar email manualmente (ID: $id): " . mysqli_error($connection), "ERROR");
        $_SESSION['estado'] = 'No se pudo verificar el email.';
    }
    header('Location: usuarios.php');
    exit();
}
?>