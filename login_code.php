<?php
include('security.php');
	//Login
if(isset($_POST['login_btn'])){
	$login_username = $_POST['username'];
	$login_email = $_POST['email'];
	$login_password = $_POST['password'];
	$query = "SELECT * FROM usuarios WHERE username ='$login_username' and password='$login_password'";
	$query_run = mysqli_query($connection,$query);
	$usertypes = mysqli_fetch_array($query_run);

	if($usertypes['usertype'] == 'admin'){
		$_SESSION['username'] = $login_username;
		header('Location: index.php');

	}elseif($usertypes['usertype'] == 'user'){
		$_SESSION['username'] = $login_username;
		header('Location: nadadoras.php');
		
	}else{
		$_SESSION['estado'] = "No coincide el Usuario y la Contraseña";
		header('Location: login.php');

	}
}elseif (isset($_POST['logout_btn'])) {
		session_destroy(); 
		unset($_SESSION['username']);
		header('Location: login.php');


}
?>	
