<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('security.php');
include('./lib/my_functions.php');

if(isset($_POST['id_fase']))
	$id_fase = $_POST['id_fase'];
else if(isset($_GET['id_fase']))
	$id_fase = $_GET['id_fase'];
//mysqli_query($connection,"truncate table calculos_clasificacion");
//variables
$query = "select id_modalidad from fases where id='$id_fase'";
$modalidad_fase = mysqli_result(mysqli_query($connection, $query),0);
$result = mysqli_query($connection,"select prioridad_panel_1, prioridad_panel_2 from modalidades where id = '$modalidad_fase'");
$prioridad_panel_1 = mysqli_result($result,0,0);
$prioridad_panel_2 = mysqli_result($result,0,1);
echo $prioridad_panel_1.$prioridad_panel_2."<br>";
$posicion = 1;
$nota_calculada_impresion = 0;
//obtengo las rutinas de la fase
$query = "select * from rutinas where id_fase='$id_fase' order by nota_final desc";
$rutinas = mysqli_query($connection,$query);
while($rutina = mysqli_fetch_array($rutinas)){
	$nota_final = $rutina['nota_final'];
	$id_rutina = $rutina['id'];
	echo " ".$id_rutina." ";
	$baja = $rutina['baja'];
	$preswimmer = $rutina['preswimmer'];
	$orden = $rutina['orden'];
	$nota_calculada_ejecucion = mysqli_result(mysqli_query($connection,"select nota_calculada from puntuaciones_paneles where id_panel = '1' and id_rutina='$id_rutina'"),0);
	$nota_calculada_impresion = mysqli_result(mysqli_query($connection,"select nota_calculada from puntuaciones_paneles where id_panel = '2' and id_rutina='$id_rutina'"),0);
	$nota_calculada_dificultad = mysqli_result(mysqli_query($connection,"select nota_calculada from puntuaciones_paneles where id_panel = '3' and id_rutina='$id_rutina'"),0);
	$query = "insert into calculos_clasificacion (id_rutina, orden, id_fase, nota_final, nota_calculada_ejecucion, nota_calculada_impresion, nota_calculada_dificultad, baja, preswimmer, id_competicion) values ('$id_rutina', '$orden', '$id_fase', '$nota_final', '$nota_calculada_ejecucion', '$nota_calculada_impresion', '$nota_calculada_dificultad', '$baja', '$preswimmer', '1')";
	echo $query;
	mysqli_query($connection,$query);
}

$posicion = 1;
$empatados = 0;
$empate = false;
$nota_ganador;
$query = "select * from calculos_clasificacion where id_fase = '$id_fase' order by baja, preswimmer, nota_final desc, nota_calculada_ejecucion desc";
$rutinas_calculos_clasificacion = mysqli_query($connection,$query);
echo "#".mysqli_num_rows($rutinas_calculos_clasificacion)."<br>";
while($rutina = mysqli_fetch_array($rutinas_calculos_clasificacion)){
	if($posicion == 1)
		$nota_ganador = $rutina['nota_final'];

	$result = mysqli_query($connection,"select nota_final from calculos_clasificacion where id_fase = '$id_fase' and nota_final = ".$rutina['nota_final']."");
	if(mysqli_num_rows($result) == '1'){
		echo "<br>Posicion $posicion -> ".$rutina['orden'];
		$empate = false;
	}else{
		$result = mysqli_query($connection,"select nota_final from calculos_clasificacion where id_fase = '$id_fase' and nota_final = ".$rutina['nota_final']." and nota_calculada_ejecucion=".$rutina['nota_calculada_ejecucion']);
		if(mysqli_num_rows($result) == '1'){
			echo "<br>Desempate 1 posicion $posicion -> ".$rutina['orden'];
			$empate = false;
		}else{
			$result = mysqli_query($connection,"select nota_final from calculos_clasificacion where id_fase = '$id_fase' and nota_final = ".$rutina['nota_final']." and nota_calculada_ejecucion=".$rutina['nota_calculada_ejecucion']." and nota_calculada_impresion=".$rutina['nota_calculada_impresion']);
			if(mysqli_num_rows($result) == '1'){
				echo "<br>Desempate final posicion $posicion -> ".$rutina['orden'];
				$empate = false;
			}else{
				if($empate)
					$posicion--;
				$empate=true;
				echo "<br>Empate posicion $posicion -> ".$rutina['orden']." - empatados - ".$empatados;
			}
		}
	}
	$query = "update rutinas set posicion='".($posicion-$empatados)."', diferencia='".($nota_ganador - $rutina['nota_final'])."' where id = ".$rutina['id_rutina'];
	echo "<br>".$query;
	$posicion++;
	mysqli_query($connection,$query);
}


$query = "update fases set puntuada='si' where id='$id_fase'";
echo "<br>".$query;
mysqli_query($connection,$query);

?>
