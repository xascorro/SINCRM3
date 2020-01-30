<?php
session_start();
$connection = mysqli_connect('localhost','root','xas','sincrm3');

//Añadir nadadora
if(isset($_POST['guardarNadadora'])){
	$apellidos = $_POST['apellidos'];
	$nombre = $_POST['nombre'];
	$licencia = $_POST['licencia'];
	$fechadenacimiento = $_POST['fechadenacimiento'];

	$query="INSERT INTO nadadoras (apellidos,nombre,licencia,fechadenacimiento) VALUES ('".$apellidos."','".$nombre."','".$licencia."','".$fechadenacimiento."')";
	$query_run = mysqli_query($connection,$query);
	if(query_run){
		$_SESSION['correcto'] = 'Nadadora añadida con éxito';
		header('Location: register.php');
	}else{
		$_SESSION['estado '] = 'Error. Nadadora no añadida';
		header('Location: register.php');	}
}else	{	
	header('Location: register.php');	}


//Editar nadadora
	if(isset($_POST['edit_btn'])){
		$id = $_POST['id'];
		$query = "SELECT * from nadadoras WHERE id = '$id'";
		$query_run = mysqli_query($connection,$query);

	}



?>
