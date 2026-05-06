<?php
    // Configuración general y de depuración
    include_once('includes/config.php');

    setlocale(LC_ALL,'es_ES');
    @session_start();

//incluimos conexión base de datos
include('database/dbconfig.php');

//si no esta logeado
if(!isset($_SESSION['email'])){
    if (isset($_COOKIE[session_name()])) {
        $last_user = $_COOKIE['last_user'] ?? 'Desconocido';
        write_log("La sesión del usuario ($last_user) ha expirado por inactividad", "SECURITY");
    }
	header('Location: login.php');
    exit();
}else{
    if($connection){
        // 1. Obtener la competición activa del SISTEMA (la que tiene el check 'si')
		$query_sys = "SELECT id, nombre, color, figuras FROM competiciones WHERE activo = 'si' LIMIT 1";
		$res_sys = mysqli_query($connection, $query_sys);
        if($res_sys && mysqli_num_rows($res_sys) > 0){
		    $comp_sys = mysqli_fetch_assoc($res_sys);
		    $_SESSION['id_competicion_activa'] = $comp_sys['id'];
		    $_SESSION['nombre_competicion_activa']= $comp_sys['nombre'];
		    $_SESSION['color_competicion_activa']= $comp_sys['color'];
		    $_SESSION['figuras']= $comp_sys['figuras'];
        }
	
        // 2. Gestión de la competición seleccionada por el USUARIO
        if(isset($_POST['id_competicion'])){
            // Si viene por POST (desde Dashboard), actualizamos
            $_SESSION['id_competicion_usuario'] = $_POST['id_competicion'];
            if(isset($_POST['nombre_competicion'])) $_SESSION['nombre_competicion_usuario'] = $_POST['nombre_competicion'];
            if(isset($_POST['competicion_figuras'])) $_SESSION['competicion_figuras_usuario'] = $_POST['competicion_figuras'];
        }

        // 3. Persistencia y Recuperación: Si tenemos ID pero faltan los nombres, los buscamos
        if(isset($_SESSION['id_competicion_usuario'])){
            if(empty($_SESSION['nombre_competicion_usuario'])){
                $id_c = $_SESSION['id_competicion_usuario'];
                $q_rec = "SELECT nombre, figuras FROM competiciones WHERE id = $id_c";
                $res_rec = mysqli_query($connection, $q_rec);
                if($res_rec && $c_rec = mysqli_fetch_assoc($res_rec)){
                    $_SESSION['nombre_competicion_usuario'] = $c_rec['nombre'];
                    $_SESSION['competicion_figuras_usuario'] = $c_rec['figuras'];
                }
            }
        } else {
            // RESTAURADO: Si es ADMIN (Rol 1), usamos la del sistema como fallback automático
            if($_SESSION['id_rol'] == '1' && isset($_SESSION['id_competicion_activa'])){
                $_SESSION['id_competicion_usuario'] = $_SESSION['id_competicion_activa'];
                $_SESSION['nombre_competicion_usuario'] = $_SESSION['nombre_competicion_activa'];
                $_SESSION['competicion_figuras_usuario'] = $_SESSION['figuras'];
            }
        }

        // Sincronizar variables locales para compatibilidad con código antiguo
        $id_competicion = $_SESSION['id_competicion_usuario'] ?? null;
        $figuras = $_SESSION['competicion_figuras_usuario'] ?? 'no';

        // Otros datos de sesión (Rutinas, Fases)
        if(isset($_POST['id_rutina'])) $_SESSION['id_rutina_usuario'] = $_POST['id_rutina'];
        if(isset($_POST['id_fase'])) $_SESSION['id_fase_usuario'] = $_POST['id_fase'];
    }

    // Seguridad de acceso por roles
    if(@$_SESSION['paginas_permitidas'] != '*' and $_SESSION['username'] != 'registrando'){
        $current_page = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
		if(!in_array($current_page, $_SESSION['paginas_permitidas'])){
			$_SESSION['no_acceso'] = 'Acceso denegado a '.$current_page.'.';
			header('Location: '.$_SESSION['startPage']);
            exit();
    	}
    }
}
?>
