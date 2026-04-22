<?php
    // Configuración general y de depuración
    include_once('includes/config.php');

    setlocale(LC_ALL,'es_ES');
    @session_start();

//incluimos conexión base de datos
include('database/dbconfig.php');

//si no esta logeado
if(!isset($_SESSION['email'])){
	header('Location: login.php');
    exit();
//si esta logeado
}else{
    //obtenemos datos
    if($connection){
		$query = "SELECT id, nombre, color, figuras FROM competiciones WHERE activo = 'si'";
		$query_run = mysqli_query($connection,$query);
        if($query_run){
		    $competicion = mysqli_fetch_assoc($query_run);
		    $_SESSION['id_competicion_activa'] = $competicion['id'];
		    $_SESSION['nombre_competicion_activa']= $competicion['nombre'];
		    $_SESSION['color_competicion_activa']= $competicion['color'];
		    $_SESSION['figuras']= $competicion['figuras'];
        }
	
        if(isset($_POST['id_competicion'])){
            $id_competicion = $_POST['id_competicion'];
            $_SESSION['id_competicion_usuario'] = $_POST['id_competicion'];
        }elseif(isset($_SESSION['id_competicion_usuario'])){
            $id_competicion=$_SESSION['id_competicion_usuario'];
            $query = "SELECT nombre, color, figuras FROM competiciones WHERE id = $id_competicion";
            $query_run = mysqli_query($connection,$query);
            if($query_run){
                $competicion = mysqli_fetch_assoc($query_run);
                $_SESSION['nombre_competicion_usuario']= $competicion['nombre'];
            }
        }else{
            $id_competicion= isset($_SESSION['id_competicion_activa']) ? $_SESSION['id_competicion_activa'] : null;
        }

        if(isset($_POST['competicion_figuras'])){
            $figuras = $_POST['competicion_figuras'];
            $_SESSION['competicion_figuras_usuario'] = $_POST['competicion_figuras'];
        }elseif(isset($_SESSION['competicion_figuras_usuario'])){
            $figuras=$_SESSION['competicion_figuras_usuario'];
        }


        if(isset($_POST['id_rutina'])){
            $id_rutina = $_POST['id_rutina'];
            $_SESSION['id_rutina_usuario'] = $_POST['id_rutina'];
        }else if(isset($_SESSION['id_rutina_usuario'])){
            $id_rutina = $_SESSION['id_rutina_usuario'];
        }

        if(isset($_POST['id_fase'])){
            $id_fase = $_POST['id_fase'];
            $_SESSION['id_fase_usuario'] = $_POST['id_fase'];
        }else if(isset($_SESSION['id_fase_usuario'])){
            $id_fase = $_SESSION['id_fase_usuario'];
        }
    }


    //redireccionamos si el rol no tiene acceso a esta página, administrador tiene acceso a todo
    if(@$_SESSION['paginas_permitidas'] != '*' and $_SESSION['username'] != 'registrando'){
		if(!in_array(basename(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH)),$_SESSION['paginas_permitidas'])){
			echo basename(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH));
			$_SESSION['no_acceso'] = 'No tienes acceso a la página solicitada, consulta con el administrador.';
			header('Location: '.$_SESSION['startPage']);
    	}
    }

}
?>
