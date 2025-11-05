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
			$orden = '';
			$orden_ran='';
		    while ($orden = mysqli_fetch_array($ordenes)){
			    $orden_rand++;
			    $query = "UPDATE rutinas SET orden='$orden_rand' where id='".$orden['id']."'";
			    mysqli_query($connection,$query);
				//echo $query;

			}
			$change='no';
			//echo $change.' '.$orden_rand.' '.$fase['id'];
			if($change=='si' & $fase['id'] == 253){
				//echo 'ent<br><br>entro<br><br>';
					$query = "select id from rutinas where orden=5 and id_fase=".$fase['id'];
				//echo $query;
					$rutina_change = mysqli_result(mysqli_query($connection,$query));
					$query = "select orden from rutinas where id=429";
				//echo $query;
					$orden_mx = mysqli_result(mysqli_query($connection,$query));
				$query = "UPDATE rutinas set orden = $orden_mx WHERE id = $rutina_change";
				//echo $query;
					mysqli_query($connection,$query);
				$query="UPDATE rutinas set orden = 5 WHERE id = 429";
				//echo $query;
					mysqli_query($connection,$query);


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
