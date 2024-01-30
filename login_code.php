<?php
include('security.php');
	//Login
if(isset($_POST['login_btn'])){
	$login_username = $_POST['username'];
	@$login_email = $_POST['email'];
	$login_password = $_POST['password'];
	$query = "SELECT usuarios.id as id_usuario, id_rol, club, roles.nombre as nombre_rol, icono FROM usuarios, roles WHERE username ='$login_username' and password='$login_password' and usuarios.id_rol = roles.id";
	$query_run = mysqli_query($connection,$query);
	$usuario = mysqli_fetch_array($query_run);
	if($usuario != NULL){
        $_SESSION['username'] = $login_username;
        $_SESSION['id_usario'] = $usuario['id_usuario'];
        $_SESSION['rol'] = $usuario['nombre_rol'];
        $_SESSION['id_rol'] = $usuario['id_rol'];
        $_SESSION['club'] = $usuario['club'];
        $_SESSION['icono'] = $usuario['icono'];

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

		}else{
			$_SESSION['estado'] = "Estas registrado como Invitado, debes de esperar a que el administrador te otorgue un rol";
			header('Location: login.php');

		}
	}else{
		unset($_SESSION['username']);
		$_SESSION['estado'] = "Usuario y contraseña incorrecto";
		header('Location: login.php');
	}

}elseif (isset($_POST['logout_btn'])) {
		unset($_SESSION);
		$_SESSION['estado'] = "Usuario desconectado";
		header('Location: login.php');


}
?>	
