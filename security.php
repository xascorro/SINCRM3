<?php
session_start();
include('database/dbconfig.php');

if(!$_SESSION['username']){
	header('Location: login.php');
}else{
	$query = "SELECT id, nombre, color FROM competiciones WHERE activo = 'si'";
	$query_run = mysqli_query($connection,$query); 
	$competicion = mysqli_fetch_assoc($query_run);
	$_SESSION['id_competicion_activa'] = $competicion['id'];
	$_SESSION['nombre_competicion_activa']= $competicion['nombre'];
	$_SESSION['color_competicion_activa']= $competicion['color'];
}
?>
