<?php
//include('security.php');

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    setlocale(LC_ALL,'es_ES');

//incluimos conexión base de datos
include('database/dbconfig.php');
include('./lib/my_functions.php');

/**
 * Confirmación de Email vía Token
 */
if (isset($_GET['confirmar_email'])) {
    @session_start();
    $token = mysqli_real_escape_string($connection, $_GET['confirmar_email']);
    
    $query = "SELECT id, email FROM usuarios WHERE token_confirmacion = '$token' AND email_confirmado = 0";
    $result = mysqli_query($connection, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $user_id = $row['id'];
        $user_email = $row['email'];
        
        $update = "UPDATE usuarios SET email_confirmado = 1, token_confirmacion = NULL WHERE id = '$user_id'";
        if (mysqli_query($connection, $update)) {
            write_log("Email verificado con éxito: $user_email", "SUCCESS");
            $_SESSION['correcto'] = "¡Email verificado con éxito! Ahora un administrador debe aprobar tu acceso.";
            header('Location: login.php');
            exit();
        } else {
            $_SESSION['estado'] = "Error al verificar el email. Inténtalo de nuevo.";
            header('Location: login.php');
            exit();
        }
    } else {
        $_SESSION['estado'] = "El enlace de confirmación es inválido o ya ha sido utilizado.";
        header('Location: login.php');
        exit();
    }
}

/**
 * Reenvío de Email de Verificación
 */
if (isset($_POST['reenviar_verificacion'])) {
    @session_start();
    $email = mysqli_real_escape_string($connection, $_POST['email']);
    
    $query = "SELECT id, username FROM usuarios WHERE email = '$email' AND email_confirmado = 0";
    $result = mysqli_query($connection, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $username = $row['username'];
        $token = bin2hex(random_bytes(32));
        
        $update = "UPDATE usuarios SET token_confirmacion = '$token' WHERE email = '$email'";
        if (mysqli_query($connection, $update)) {
            require_once 'includes/email_functions.php';
            $subject = 'Confirma tu email - SINCRM (Reenvío)';
            $link = "https://" . $_SERVER['HTTP_HOST'] . "/login_code.php?confirmar_email=" . $token;
            $body = "
            <h2>Hola $username,</h2>
            <p>Has solicitado el reenvío del enlace de confirmación para tu cuenta en SINCRM.</p>
            <div style='text-align: center; margin: 30px 0;'>
                <a href='$link' style='background-color: #4e73df; color: white; padding: 12px 24px; text-decoration: none; border-radius: 10px; font-weight: bold;'>Confirmar mi Email</a>
            </div>
            <p>Si no has solicitado esto, puedes ignorar este email.</p>";
            
            if (enviar_email($email, $subject, $body)) {
                write_log("Reenvío de verificación solicitado para $email", "INFO");
                sendResponse('success', "Email de confirmación enviado. Revisa tu bandeja de entrada.");
            } else {
                sendResponse('error', "No se pudo enviar el email. Contacta con soporte.");
            }
        } else {
            sendResponse('error', "Error al generar el token.");
        }
    } else {
        sendResponse('error', "El email no existe o ya está verificado.");
    }
}

/**
 * Helper to send response (AJAX or Redirect)
 * @param string $status 'success' or 'error'
 * @param string $message Text to show
 * @param string $redirect Redirect URL
 * @param string|null $icon Optional club logo path
 */
function sendResponse($status, $message, $redirect = 'login.php', $icon = null) {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => $status,
            'message' => $message,
            'redirect' => $redirect,
            'icon' => $icon
        ]);
        exit();
    } else {
        if ($status != 'success') {
            if (session_status() === PHP_SESSION_NONE) @session_start();
            $_SESSION['estado'] = $message;
        }
        header('Location: ' . $redirect);
        exit();
    }
}

	//Login
if(isset($_POST['login_btn'])){
	$login_password = $_POST['password'];
	$login_email = mysqli_real_escape_string($connection, $_POST['email']);
	$query = "SELECT usuarios.id as id_usuario, usuarios.username, id_rol, id_juez_v3, club, hash, roles.nombre as nombre_rol, icono, activo, email_confirmado FROM usuarios, roles WHERE email='$login_email' and usuarios.id_rol = roles.id";
	$query_run = mysqli_query($connection,$query);
	$usuario = mysqli_fetch_array($query_run);
	if($usuario != NULL){
        // Verificación de email
        if($usuario['email_confirmado'] == 0){
            write_log("Intento de acceso sin verificar email: $login_email", "WARNING");
            $msg = "Debes confirmar tu dirección de email antes de acceder.<br><br>";
            $msg .= "<button onclick=\"reenviarVerificacion('$login_email')\" class='mt-2 px-4 py-2 bg-blue-600 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-blue-700 transition-all'>Reenviar email de confirmación</button>";
            sendResponse('error', $msg);
        }

        // Verificación de cuenta activa
        if($usuario['activo'] == 0){
            write_log("Intento de acceso: Cuenta desactivada ($login_email)", "SECURITY");
            sendResponse('error', "Tu cuenta ha sido desactivada por un administrador.");
        }

		if (password_verify($login_password, $usuario['hash'])) {
            // Persistencia de Sesión (Remember Me)
            $session_lifetime = (isset($_POST['remember'])) ? (86400 * 30) : (3600 * 4);
            session_set_cookie_params($session_lifetime, '/');
            @session_start();
            
            $_SESSION['username'] = $login_email;
            setcookie('last_user', $login_email, time() + (86400 * 30), '/');
            $_SESSION['email'] = $login_email;
            $_SESSION['id_usario'] = $usuario['id_usuario'];
            $_SESSION['rol'] = $usuario['nombre_rol'];
            $_SESSION['id_rol'] = $usuario['id_rol'];
            $_SESSION['id_juez_v3'] = $usuario['id_juez_v3'];
            $_SESSION['club'] = $usuario['club'];
            $_SESSION['icono'] = $usuario['icono'];
            
            $club_logo = null;
            if($_SESSION['id_rol'] == 5){
                $query = "SELECT nombre, logo FROM clubes WHERE id = ".$_SESSION['club'];
                $res_club = mysqli_query($connection, $query);
                if($res_club && mysqli_num_rows($res_club) > 0) {
                    $c_data = mysqli_fetch_assoc($res_club);
                    $_SESSION['nombre_club'] = $c_data['nombre'];
                    if(!empty($c_data['logo'])) $club_logo = $c_data['logo'];
                }
            }

            // Nuevo sistema de log mejorado
            $log_msg = "Inicio de sesión exitoso: " . $usuario['username'] . " (" . $usuario['nombre_rol'] . ")";
            if($_SESSION['id_rol'] == 5) $log_msg .= " - Club: " . ($_SESSION['nombre_club'] ?? 'N/A');
            write_log($log_msg, "SUCCESS");

			//admin
			if($_SESSION['id_rol'] == '1'){
				$_SESSION['startPage'] = 'index.php';
				$_SESSION['paginas_permitidas'] = '*';
                sendResponse('success', "Bienvenido " . $usuario['username'], $_SESSION['startPage']);
			//juez
			}elseif($_SESSION['id_rol'] == '4'){
				$_SESSION['startPage'] = 'index.php';
				$_SESSION['paginas_permitidas'] = array(
					'login.php',
					'login_code.php',
					'index.php',
					'security.php',
					'perfil.php',
					'perfil_code.php',
					'log_usuario.php',
                    'ranking_jueces.php',
                    'perfil_juez.php',
                    'mi_auditoria.php',
                    'analisis_jueces.php',
                    'analisis_juez_detalle.php'
				);
                sendResponse('success', "Bienvenido " . $usuario['username'], $_SESSION['startPage']);
			//club
			}elseif($_SESSION['id_rol'] == '5'){
				$_SESSION['startPage'] = 'index.php';
				$_SESSION['club'] = $usuario['club'];
				//paginas con acceso para el rol club
				$_SESSION['paginas_permitidas'] = array(
					'login.php',
					'login_code.php',
					'index.php',
					'security.php',
					'nadadoras.php',
					'nadadoras_code.php',
					'inscripciones_figuras.php',
					'inscripciones_figuras_code.php',
					'coach_card_composer.php',
					'coach_card_composer_elemento_edit.php',
					'coach_card_composer_code.php',
					'dificultad_hibridos_select_option.php',
					'rutinas.php',
					'rutinas_code.php',
					'rutinas_edit.php',
					'rutinas_participantes.php',
					'rutinas_participantes_code.php',
					'inscripciones_rutinas_participantes_code.php',
					'informe_figuras.php',
					'perfil.php',
					'perfil_code.php',
					'mi_equipo.php',
					'download_music.php',
					'descargar_fase.php',
					'log_usuario.php'
					);
				//redirecciono a su pagina inicial pasándole el logo
                sendResponse('success', "Bienvenido " . $usuario['username'], $_SESSION['startPage'], $club_logo);

			}else if($_SESSION['id_rol'] == '6'){
                write_log("Intento de acceso: Usuario invitado no aprobado ($login_email)", "SECURITY");
                sendResponse('error', "Estas registrado como Invitado, debes de esperar a que el administrador aprueba tu registro.");
			}
		}else {
            write_log("Fallo de autenticación: Contraseña incorrecta para el email $login_email", "SECURITY");
            sendResponse('error', "La contraseña no coincide");
		}
	}else{
        if (session_status() === PHP_SESSION_NONE) @session_start();
		unset($_SESSION['email']);
        write_log("Fallo de autenticación: El email $login_email no existe en el sistema", "SECURITY");
        sendResponse('error', "Este usuario no está registrado");
	}

}elseif (isset($_POST['logout_btn']) or isset($_GET['logout_btn'])) {
    @session_start();
    $user_log = $_SESSION['username'] ?? 'Usuario desconocido';
    write_log("Cierre de sesión: $user_log", "INFO");
	unset($_SESSION);
	session_destroy();
    @session_start();
	$_SESSION['estado'] = "Usuario desconectado";
	header('Location: login.php');


}elseif (isset($_POST['register_btn'])) {
    @session_start();
	$register_username = mysqli_real_escape_string($connection, $_POST['username']);
	$register_email = mysqli_real_escape_string($connection, $_POST['email']);
	$register_telefono = mysqli_real_escape_string($connection, $_POST['telefono']);
	$register_comentario = mysqli_real_escape_string($connection, $_POST['comentario']);
	$register_password = $_POST['password'];
	$register_password_r = $_POST['password_r'];

	$query = "SELECT * FROM usuarios WHERE email ='$register_email'";
	$query_run = mysqli_query($connection,$query);
	if (mysqli_num_rows($query_run) > 0 ){
		sendResponse('error', "Ya existe un usuario registrado con ese email", 'register.php');
	}

	if ($register_password != $register_password_r){
		sendResponse('error', "Las contraseñas no coinciden", 'register.php');
	}
	$register_password_hash = password_hash($register_password, PASSWORD_DEFAULT);
    $token_verificacion = bin2hex(random_bytes(32));

	$query = "INSERT INTO usuarios (username, email, telefono, hash, comentario, token_confirmacion, email_confirmado) values ('$register_username', '$register_email', '$register_telefono', '$register_password_hash', '$register_comentario', '$token_verificacion', 0)";
	if(!mysqli_query($connection, $query)){
		sendResponse('error', "Ups!, ha ocurrido un error inesperado", 'register.php');
	}else{
        write_log("Nueva solicitud de registro: $register_email", "INFO");
		// 1. ENVIO EMAIL AL ADMIN (Aviso de registro)
		$toAddress = 'sincrm@pedrodiaz.eu';
		$subject = 'Nuevo usuario registrado';
		$body = "
		<h2>Nueva Solicitud de Registro</h2>
		<p>Se ha registrado un nuevo usuario en la plataforma:</p>
		<div class='alert-box alert-info'>
			<strong>Usuario:</strong> ".$_POST['username']."<br>
			<strong>Email:</strong> ".$_POST['email']."
		</div>
		<p>Termina de configurar el usuario desde el panel administrativo.</p>
		<div style='text-align: center; margin-top: 30px;'>
			<a href='https://" . $_SERVER['HTTP_HOST'] . "/usuarios.php' class='btn'>Ir al Panel de Usuarios</a>
		</div>";
		require_once 'includes/email_functions.php';
		enviar_email($toAddress, $subject, $body);

		// 2. ENVIO EMAIL AL USUARIO (Confirmación de registro + Verificación)
		$toUser = $register_email;
		$subjectUser = 'Confirma tu email - SINCRM';
        $link_confirmacion = "https://" . $_SERVER['HTTP_HOST'] . "/login_code.php?confirmar_email=" . $token_verificacion;
		$bodyUser = "
		<h2>¡Bienvenido a SINCRM!</h2>
		<p>Hola <strong>$register_username</strong>,</p>
		<p>Gracias por registrarte. Para completar el proceso, por favor confirma tu dirección de email haciendo clic en el siguiente enlace:</p>
		<div style='text-align: center; margin: 30px 0;'>
			<a href='$link_confirmacion' style='background-color: #4e73df; color: white; padding: 12px 24px; text-decoration: none; border-radius: 10px; font-weight: bold;'>Confirmar mi Email</a>
		</div>
        <p>Si el botón no funciona, copia y pega este enlace en tu navegador:</p>
        <p style='font-size: 12px; color: #666;'>$link_confirmacion</p>
		<div class='alert-box alert-warning'>
			<strong>Nota:</strong> Una vez confirmado tu email, un administrador deberá aprobar tu cuenta antes de que puedas acceder.
		</div>";
		enviar_email($toUser, $subjectUser, $bodyUser);

		//envio mensaje de confirmación
		$_SESSION['estado_registro'] = "¡Registro completado! <br>Te hemos enviado un email para verificar tu cuenta. Por favor, revisa tu bandeja de entrada (y la carpeta de spam).";
		sendResponse('success', "Registro completado", 'login.php');
	}
}
?>