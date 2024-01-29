<?php
include('security.php');
include('./lib/my_functions.php');
session_start();
//Sortear todo

if(isset($_POST['save_btn'])){
    $categoria = $_POST['categoria'];
	$corte = $_POST['corte'];
    $redondeo = $_POST['redondeo'];
if(isset($_POST['desbloquear'])){
    $query = "UPDATE fases set sorteado='no' WHERE id='$id_fase'";
    mysqli_query($connection,$query);
    $order_by = " order by orden";
}else{
    $order_by = " order by rand()";
}
    if($categoria == '0'){ //sorteo todas las categorias
//        $query = "UPDATE fases SET sorteado='si' WHERE id_competicion=".$_SESSION['id_competicion_activa'];
//        mysqli_query($connection,$query);
        $query = "SELECT id, id_categoria FROM fases WHERE id_competicion=".$_SESSION['id_competicion_activa']." ORDER by orden";;
    }else{ //sorteo una única categoría
//        $query = "UPDATE fases SET sorteado='si' WHERE id_categoria='$categoria' and id_competicion=".$_SESSION['id_competicion_activa'];
//        mysqli_query($connection,$query);
        $query = "SELECT id, id_categoria FROM fases WHERE id_categoria='$categoria' and id_competicion=".$_SESSION['id_competicion_activa']." ORDER by orden";
    }
    $result = mysqli_query($connection,$query);
    if(count($result)>0){
        $corte_fase= 1;
        $id_categoria_anterior = 0;
        while ($fase = mysqli_fetch_array($result)){
            if($id_categoria_anterior != $fase['id_categoria']){
                $id_categoria_anterior = $fase['id_categoria'];
                $corte_fase = 1;
            }
            $orden_rand = 0;
			$query = "SELECT id, id_nadadora FROM inscripciones_figuras WHERE orden >= '0' and id_fase=".$fase['id']." $order_by";
		    $ordenes = mysqli_query($connection,$query);
		    $orden_listado = "";
		    while ($orden = mysqli_fetch_array($ordenes)){
			    $orden_rand++;
			    $query = "UPDATE inscripciones_figuras SET orden='$orden_rand' where id='".$orden['id']."'";
			    mysqli_query($connection,$query);

			    $query = "select * from inscripciones_figuras where id_competicion = '".$_SESSION["id_competicion_activa"]."' and id_nadadora = '".$orden['id_nadadora']."'";
			    $salidas_nadadora = mysqli_query($connection,$query);
			    $i = 0;
			    while ($salida_nadadora = mysqli_fetch_assoc($salidas_nadadora)){
                    $query ="SELECT count(id) FROM inscripciones_figuras WHERE id_fase=".$fase['id']." and orden >= 0";
				    $orden_maximo = mysqli_result(mysqli_query($connection,$query),0);
                    if ($redondeo == "floor"){
                            $corte = floor($orden_maximo/4);
                    }else {
                            $corte = ceil($orden_maximo/4);
                    }
				        $orden_corte = $orden_rand-($i*$corte);
				    if($orden_corte < 0)
				        $orden_corte += $orden_maximo;
				    if($orden_corte == 0)
				        $orden_corte = $orden_maximo;
				    $query = "UPDATE inscripciones_figuras SET orden='$orden_corte' WHERE id='".$salida_nadadora['id']."'";
				    mysqli_query($connection,$query);
				    $i++;
			    }
			}
        //meto cortes a las fases
        $query = "UPDATE fases SET corte=$corte_fase, sorteado='si' WHERE id=".$fase['id'];
        mysqli_query($connection,$query);
        $corte_fase += $corte;
            if ($corte_fase > $orden_maximo)
                    $corte_fase = $corte_fase-$orden_maximo;
		}
	}

    //+++++++++++++++++++++++++++++++++
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Sorteo realizado';
		header('Location: sorteo_figuras.php');
	}else{
		$_SESSION['estado'] = 'Error, algo ha salido mal durante el sorteo <br>'.mysqli_error($connection);
		header('Location: sorteo_figuras.php');
	}
    echo $_SESSION['correcto'];
    echo $_SESSION['estado'];



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
	$query = "UPDATE inscripciones_figuras SET orden = '0' WHERE orden > '0' and id_competicion = '".$_SESSION['id_competicion_activa']."';
    UPDATE fases SET sorteado = 'no', corte='0' WHERE id_competicion = '".$_SESSION['id_competicion_activa']."'";
	$query_run = mysqli_multi_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Registro eliminado con éxito';
		header('Location: sorteo_figuras.php');
	}else{
		$_SESSION['estado'] = 'Error. El Registro no se ha eliminado <br>'.mysqli_error($connection);
		header('Location: sorteo_figuras.php');
	}
}
?>
