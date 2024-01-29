<?php

include('security.php');

$id_fase = $_GET['id_fase'];

if(isset($_GET['desbloquear'])){
    $query = "update fases set sorteado='no' where id='$id_fase'";
    mysqli_query($connection,$query);
    $order_by = " order by orden";
}else{
    $query = "update fases set sorteado='si' where id='$id_fase'";
    mysqli_query($connection,$query);
    $order_by = " order by rand()";
}
if(isset($_GET['id_competicion'])){
    $id_competicion = $_GET['id_competicion'];
    $query = "SELECT * FROM fases WHERE id_competicion=$id_competicion";
}else{
    $query = "select * from fases where id='$id_fase'";
}
$result = mysqli_query($connection,$query);
    if(count($result)>0){
		while ($fase = mysqli_fetch_assoc($result)){

			$orden_rand = 0;
			$query = "select id, nombre, id_club, orden from rutinas where orden >= '0' and id_fase='".$fase['id']."' $order_by";
            echo $query;
		    $ordenes = mysqli_query($connection,$query);
		      while ($orden = mysqli_fetch_array($ordenes)){
			    $orden_rand++;
			    $query = "update rutinas set orden='$orden_rand' where id='".$orden['id']."'";
                echo $query;
			    mysqli_query($connection,$query);
			}

		}
	}

?>



