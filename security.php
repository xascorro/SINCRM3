<?php
if(!isset($_SESSION)) {
     session_start();
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    setlocale(LC_ALL,'es_ES');
}
//incluimos conexión base de datos
include('database/dbconfig.php');
//si no esta logeado
if(!$_SESSION['username']){
	header('Location: login.php');
//si esta logeado
}else{
    //obtenemos datos
	$query = "SELECT id, nombre, color, figuras FROM competiciones WHERE activo = 'si'";
	$query_run = mysqli_query($connection,$query); 
	$competicion = mysqli_fetch_assoc($query_run);
	$_SESSION['id_competicion_activa'] = $competicion['id'];
	$_SESSION['nombre_competicion_activa']= $competicion['nombre'];
	$_SESSION['color_competicion_activa']= $competicion['color'];
	$_SESSION['competicion_figuras']= $competicion['figuras'];

    //redireccionamos si el rol no tiene acceso a esta página, administrador tiene acceso a todo
    if($_SESSION['paginas_permitidas'] != '*'){
        if(!in_array(@array_pop(array_filter(explode('/',     $_SERVER['REQUEST_URI']))),$_SESSION['paginas_permitidas'])){
			header('Location: '.$_SESSION['startPage']);
    }
    }

}
?>
