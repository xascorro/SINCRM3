<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
    error_reporting(E_ALL);
include('security.php');
include('./lib/my_functions.php');
session_start();
//Sortear todo

if(isset($_POST['save_btn'])){
    $fase = $_POST['fase'];
if(isset($_POST['desbloquear'])){
    $query = "UPDATE fases set sorteado='no' WHERE id='$fase'";
    mysqli_query($connection,$query);
    $order_by = " order by orden";
}else{
    $order_by = " order by rand()";
}
    if($fase == '0'){ //sorteo todas las categorias
//        $query = "UPDATE fases SET sorteado='si' WHERE id_competicion=".$_SESSION['id_competicion_activa'];
//        mysqli_query($connection,$query);
        $query = "SELECT id FROM fases WHERE id_competicion=".$_SESSION['id_competicion_activa']." ORDER by orden";

    }else{ //sorteo una única categoría
//        $query = "UPDATE fases SET sorteado='si' WHERE id_categoria='$categoria' and id_competicion=".$_SESSION['id_competicion_activa'];
//        mysqli_query($connection,$query);
        $query = "SELECT id FROM fases WHERE id=$fase and id_competicion=".$_SESSION['id_competicion_activa']." ORDER by orden";
    }


    $result = mysqli_query($connection, $query);
//    if(count($result)>0){
//        $corte_fase= 1;
        $id_categoria_anterior = 0;
        while ($fase = mysqli_fetch_array($result)){
//            if($id_categoria_anterior != $fase['id_categoria']){
//                $id_categoria_anterior = $fase['id_categoria'];
//                $corte_fase = 1;
//            }
            $orden_rand = 0;
			$query = "SELECT id FROM rutinas WHERE orden >= '0' and id_fase=".$fase['id']." $order_by";
		    $ordenes = mysqli_query($connection,$query);
		    $orden_listado = "";
		    while ($orden = mysqli_fetch_array($ordenes)){
			    $orden_rand++;
			    $query = "UPDATE rutinas SET orden='$orden_rand' where id='".$orden['id']."'";
			    mysqli_query($connection,$query);
				echo $query;

			}
        //actualiz las fases como sorteadas
        $query = "UPDATE fases SET sorteado='si' WHERE id=".$fase['id'];
        mysqli_query($connection,$query);

	}

    //+++++++++++++++++++++++++++++++++
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Sorteo realizado';
	}else{
		$_SESSION['estado'] = 'Error, algo ha salido mal durante el sorteo <br>'.mysqli_error($connection);
	}






}
//
////Actualizar registro
//if(isset($_POST['update_btn'])){
//	$id = $_POST['edit_id'];
//	$nombre = $_POST['edit_nombre'];
//	$nombre_corto = $_POST['edit_nombre_corto'];
//	$codigo = $_POST['edit_codigo'];
//	$logo = $_POST['edit_logo'];
//
//	if($password != $r_password){
//		$_SESSION['estado'] = 'Error, los datos no se han actualizado <br>La contraseña no coincide';
//		header('Location: usuarios.php');
//	}else{
//		$query = "UPDATE clubes SET nombre ='$nombre', nombre_corto='$nombre_corto', codigo='$codigo', logo='$logo' WHERE id='$id'";
//		$query_run = mysqli_query($connection,$query);
//		if(mysqli_error($connection) == ''){
//			$_SESSION['correcto'] = 'Datos actualizados con éxito';
//			header('Location: clubes.php');
//		}else{
//			$_SESSION['estado'] = 'Error, los datos no se han actualizado <br>'.mysqli_error($connection);
//			header('Location: clubes.php');
//		}
//	}
//
//}
//
////Borrar registro
if(isset($_POST['delete_btn'])){
	$query = "UPDATE rutinas SET orden = '0' WHERE orden > '0' and id_competicion = '".$_SESSION['id_competicion_activa']."';
    UPDATE fases SET sorteado = 'no' WHERE id_competicion = '".$_SESSION['id_competicion_activa']."'";
	$query_run = mysqli_multi_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Sorteo eliminado con éxito';
	}else{
		$_SESSION['estado'] = 'Error. El Sorteo no se ha eliminado <br>'.mysqli_error($connection);
	}
}
		header('Location: sorteo_rutinas.php');

?>
