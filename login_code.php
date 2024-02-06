<?php
include('security.php');
	//Login
if(isset($_POST['login_btn'])){
	$login_username = $_POST['username'];
	$login_password = $_POST['password'];
	@$login_email = $_POST['email'];
	$query = "SELECT usuarios.id as id_usuario, id_rol, club, hash, roles.nombre as nombre_rol, icono FROM usuarios, roles WHERE (username ='$login_username' or email='$login_username') and usuarios.id_rol = roles.id";
	echo $query;
	$query_run = mysqli_query($connection,$query);
	$usuario = mysqli_fetch_array($query_run);
	if($usuario != NULL){
        $_SESSION['username'] = $login_username;
        $_SESSION['id_usario'] = $usuario['id_usuario'];
        $_SESSION['rol'] = $usuario['nombre_rol'];
        $_SESSION['id_rol'] = $usuario['id_rol'];
        $_SESSION['club'] = $usuario['club'];
        $_SESSION['icono'] = $usuario['icono'];
		if (password_verify($login_password, $usuario['hash'])) {
			//admin
			if($_SESSION['id_rol'] == '1'){
				$_SESSION['startPage'] = 'index.php';
				$_SESSION['paginas_permitidas'] = '*';
				header('Location: '.$_SESSION['startPage']);
			//club
			}elseif($_SESSION['id_rol'] == '5'){
				$_SESSION['startPage'] = 'index_club.php';
				$_SESSION['club'] = $usuario['club'];
				//paginas con acceso para el rol club
				$_SESSION['paginas_permitidas'] = array(
					'login.php',
					'login_code.php',
					'index_club.php',
					'nadadoras.php',
					'inscripciones_figuras.php',
					'inscripciones_figuras_code.php',
					'coach_card_composer.php',
					'coach_card_composer_edit.php',
					'coach_card_composer_code.php',
					'coach_card_composer_elemento_edit.php'
				);
				//redirecciono a su pagina inicial
				header('Location: '.$_SESSION['startPage']);

			}else if($_SESSION['id_rol'] == '6'){
				$_SESSION['estado'] = "Estas registrado como Invitado, debes de esperar a que el administrador te otorgue un rol";
				header('Location: login.php');

			}
		}else{
			$_SESSION['estado'] = "La contraseña no coincide";
			header('Location: login.php');
		}
	}else{
		unset($_SESSION['username']);
		$_SESSION['estado'] = "Este usuario no está registrado";
		header('Location: login.php');
	}

}elseif (isset($_POST['logout_btn'])) {
		unset($_SESSION);
		$_SESSION['estado'] = "Usuario desconectado";
		header('Location: login.php');


}elseif (isset($_POST['register_btn'])) {
	$register_username = $_POST['username'];
	$register_email = $_POST['email'];
	$register_telefono = $_POST['telefono'];
	$register_comentario = $_POST['comentario'];
	$register_password = $_POST['password'];
	$register_password_r = $_POST['password_r'];
	$query = "SELECT * FROM usuarios WHERE username ='$register_username'";
	$query_run = mysqli_query($connection, $query);
	$usuario = mysqli_fetch_assoc($query_run);
	if (mysqli_num_rows($query_run) > 0 ){
		$_SESSION['estado'] = "Ya existe un usuario registrado con ese nombre";
		header('Location: register.php');
	}else{
		$query = "SELECT * FROM usuarios WHERE email ='$register_email'";
		$query_run = mysqli_query($connection,$query);
		if (mysqli_num_rows($query_run) > 0 ){
			$_SESSION['estado'] = "Ya existe un usuario registrado con ese email";
			header('Location: register.php');
			exit();
		}
	}
	if ($register_password != $register_password_r){
			$_SESSION['estado'] = "Las contraseñas no coinciden";
			header('Location: register.php');
			exit();
	}
	$register_password_hash = password_hash($register_password, PASSWORD_DEFAULT);
	$query = "INSERT INTO usuarios (username, email, telefono, hash, comentario) values ('$register_username', '$register_email', '$register_telefono', '$register_password_hash', '$register_comentario')";
	if(!mysqli_query($connection, $query)){
		$_SESSION['estado_registro'] = "Ups!, ha ocurrido un error inesperado";
		header('Location: register.php');
		exit();
	}else{
		//envio email con confirmación de email
		$toAddress = 'admin@sincrm.pedrodiaz.eu';
		$toName = 'Pedro Diaz';
		$subject = 'Nuevo usuario registrado';
		$body = 'Se ha registrado el usuario <b>'.$_POST['username'].'</b> con el email <b>'.$_POST['email'].'</b>. Termina de configurar el usuario desde el  <a href="https://sincrm.pedrodiaz.eu/usuarios.php">Panel de usuarios.</a>';
		$altbody = 'Se ha registrado el usuario '.$_POST['username'].' con el email '.$_POST['username'].'. Termina de configurar el usuario desde el <a href="https://sincrm.pedrodiaz.eu/usuarios.php">Panel de usuarios.</a>';
		include('enviar_email.php');
		//envio mensaje de confirmación
		$_SESSION['estado_registro'] = "Enhorabuena, has completado el registro con éxito!! <br>Un administrador debe de aprobar tu registro y configurar tu usuario para comenzar a usar este sitio web.";
		header('Location: login.php');
	}
}
?>	
