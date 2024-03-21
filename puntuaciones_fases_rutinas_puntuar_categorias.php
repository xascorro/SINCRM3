<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('security.php');
include('./lib/my_functions.php');

session_start();
//$id_competicion = $_SESSION['id_competicion_activa'];
//$query = "DELETE FROM resultados_rutinas_fases WHERE id_competicion='".$id_competicion."'";
//echo '<br>'.$query.'<br>';
////mysqli_query($connection,$query);
////variables
$condicion = "";
if(isset($_GET['id_fase'])){
	$id_fase = $_GET['id_fase'];
	$condicion = " and id_fase = $id_fase";
}
$posicion = 1;


//ordeno y reparto puntos

$puntos = array("0", "19", "16", "14", "13","12", "11", "10", "9", "8","7", "6", "5", "4", "3","2", "1", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0",);

$query = "select id_fase from rutinas where rutinas.id_competicion='$id_competicion' $condicion group by id_fase order by id_fase";
$fases = mysqli_query($connection,$query);
while($fase = mysqli_fetch_array($fases)){
	echo '<h1>Fase '.$fase['id_fase'].'</h1>';
	$posicion = 1;
	$empatados = 0;
	$empate = false;
	$nota_ganador;
	$query = "SELECT * FROM rutinas WHERE id_fase='".$fase['id_fase']."' and rutinas.id_competicion='$id_competicion' and orden >= 1 and baja = 'no' order by nota_final desc";
	echo $query;
	$resultados_rutinas = mysqli_query($connection,$query);
	while($resultado_rutina = mysqli_fetch_array($resultados_rutinas)){
		$id_fase = $resultado_rutina['id_fase'];
		if($posicion == 1)
			$nota_ganador = $resultado_rutina['nota_final'];
			$result = mysqli_query($connection,"select nota_final from rutinas where id_fase='".$fase['id_fase']."' and id_competicion='$id_competicion' and nota_final = '".$resultado_rutina['nota_final']."'");

		if(mysqli_num_rows($result) == '1'){
			echo "<br>Posicion $posicion -> ".$resultado_rutina['id'];
			$empate = false;
		}else{
			if($empate)
				$posicion--;
			$empate=true;
			echo "<br><b>Empate posicion $posicion -> ".$resultado_rutina['id']." - empatados - ".$empatados."</b>";
		}
		if($resultado_rutina['baja'] != 'no' and $resultado_rutina['preswimmer'] != 'no' and $posicion <= '16'){
			$puntos_nadadora = $puntos[$posicion];
		}else{
			$puntos_nadadora = "0";
		}
		$puntos_nadadora = $puntos[$posicion];

		$query = "UPDATE rutinas SET posicion='".($posicion-$empatados)."', diferencia='".($nota_ganador - $resultado_rutina['nota_final'])."', puntos='$puntos_nadadora' WHERE id = ".$resultado_rutina['id'];
		echo "<br>dif.....".$query."<br>";
		$posicion++;
		mysqli_query($connection,$query);
	}
	$query = "UPDATE rutinas SET puntos='0', posicion='100' WHERE (baja='si' or preswimmer='si' or orden < 1) and id_fase=".$id_fase." and id_competicion='$id_competicion'";
	echo "<br>".$query;
	mysqli_query($connection,$query);

	$query = "UPDATE fases SET puntuada='si' WHERE id=".$id_fase." and id_competicion='$id_competicion'";
	echo "<br>".$query;
	mysqli_query($connection,$query);
}

?>
