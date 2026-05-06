<?php
//include('security.php');

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    setlocale(LC_ALL,'es_ES');
    @session_start();
//incluimos conexión base de datos
include('database/dbconfig.php');



include('./lib/my_functions.php');

/**
 * Helper to send response (AJAX or Redirect)
 */
function sendResponse($status, $message, $redirect = 'login.php') {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => $status,
            'message' => $message,
            'redirect' => $redirect
        ]);
        exit();
    } else {
        if ($status != 'success') {
            $_SESSION['estado'] = $message;
        }
        header('Location: ' . $redirect);
        exit();
    }
}

	//Login
if(isset($_POST['login_btn'])){
	$login_password = $_POST['password'];
	$login_email = $_POST['email'];
	$query = "SELECT usuarios.id as id_usuario, usuarios.username, id_rol, id_juez_v3, club, hash, roles.nombre as nombre_rol, icono, activo FROM usuarios, roles WHERE email='$login_email' and usuarios.id_rol = roles.id";
	$query_run = mysqli_query($connection,$query);
	$usuario = mysqli_fetch_array($query_run);
	if($usuario != NULL){
        // ... (check active)
		if (password_verify($login_password, $usuario['hash'])) {
            $_SESSION['username'] = $login_email;
            setcookie('last_user', $login_email, time() + (86400 * 30), '/');
            $_SESSION['email'] = $login_email;
            $_SESSION['id_usario'] = $usuario['id_usuario'];
            $_SESSION['rol'] = $usuario['nombre_rol'];
            $_SESSION['id_rol'] = $usuario['id_rol'];
            $_SESSION['id_juez_v3'] = $usuario['id_juez_v3'];
            $_SESSION['club'] = $usuario['club'];
            $_SESSION['icono'] = $usuario['icono'];
            
            if($_SESSION['id_rol'] == 5){
                $query = "SELECT nombre FROM clubes WHERE id = ".$_SESSION['club'];
                $_SESSION['nombre_club'] = mysqli_result(mysqli_query($connection,$query),0);
            }

            // Nuevo sistema de log
            write_log("Inicio de sesión exitoso", "SUCCESS");

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
					'log_usuario.php'
				);
				//redirecciono a su pagina inicial
                sendResponse('success', "Bienvenido " . $usuario['username'], $_SESSION['startPage']);

			}else if($_SESSION['id_rol'] == '6'){
                write_log("Intento de acceso: Usuario invitado no aprobado ($login_email)", "SECURITY");
                sendResponse('error', "Estas registrado como Invitado, debes de esperar a que el administrador aprueba tu registro.");
			}
		}else {
            write_log("Intento de sesión fallido: Contraseña incorrecta para $login_email", "SECURITY");
            sendResponse('error', "La contraseña no coincide");
		}
	}else{
		unset($_SESSION['email']);
        write_log("Intento de sesión fallido: Usuario no encontrado ($login_email)", "SECURITY");
        sendResponse('error', "Este usuario no está registrado");
	}

}elseif (isset($_POST['logout_btn']) or isset($_GET['logout_btn'])) {
    write_log("Cierre de sesión voluntario", "INFO");
	unset($_SESSION);
	session_destroy();
	$_SESSION['estado'] = "Usuario desconectado";
	header('Location: login.php');


}elseif (isset($_POST['register_btn'])) {
	$register_username = $_POST['username'];
	$register_email = $_POST['email'];
	$register_telefono = $_POST['telefono'];
	$register_comentario = $_POST['comentario'];
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
	$query = "INSERT INTO usuarios (username, email, telefono, hash, comentario) values ('$register_username', '$register_email', '$register_telefono', '$register_password_hash', '$register_comentario')";
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
			<a href='https://sincrm.pedrodiaz.eu/usuarios.php' class='btn'>Ir al Panel de Usuarios</a>
		</div>";
		require_once 'includes/email_functions.php';
		enviar_email($toAddress, $subject, $body);

		// 2. ENVIO EMAIL AL USUARIO (Confirmación de registro)
		$toUser = $register_email;
		$subjectUser = 'Registro Recibido - SINCRM3';
		$bodyUser = "
		<h2>¡Bienvenido a SINCRM3!</h2>
		<p>Hola <strong>$register_username</strong>,</p>
		<p>Hemos recibido tu solicitud de registro correctamente.</p>
		<div class='alert-box alert-warning'>
			<strong>Estado: Pendiente de Aprobación</strong><br>
			Un administrador debe revisar y aprobar tu cuenta antes de que puedas acceder. Recibirás otro email cuando tu acceso esté activado.
		</div>
		<p>Gracias por tu paciencia.</p>";
		enviar_email($toUser, $subjectUser, $bodyUser);

		//envio mensaje de confirmación
		$_SESSION['estado_registro'] = "Enhorabuena, has completado el registro con éxito!! <br>Un administrador debe de aprobar tu registro y configurar tu usuario para comenzar a usar este sitio web.";
		sendResponse('success', "Registro completado", 'login.php');
	}
}
?>