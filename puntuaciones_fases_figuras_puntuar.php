<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('security.php');
include('./lib/my_functions.php');

session_start();
$id_competicion = $_SESSION['id_competicion_activa'];
mysqli_query($connection,"delete from resultados_figuras where id_competicion='".$id_competicion."'");
//variables
$posicion = 1;
//obtengo las categorias de la competicion
$query = "select * from fases where id_competicion='$id_competicion' group by id_categoria order by orden asc ";
$fases = mysqli_query($connection,$query);
while($fase_figuras = mysqli_fetch_array($fases)){
	$gd_acumulado = mysqli_result(mysqli_query($connection,"select sum(grado_dificultad) from figuras where id in (select id_figura from fases where id_competicion='$id_competicion' and id_categoria='".$fase_figuras['id_categoria']."')"),0);
	$query = "select id_nadadora, sum(nota_final) as sum_nota_final, baja, preswimmer, id from inscripciones_figuras where id_fase in (select id from fases where id_categoria='".$fase_figuras['id_categoria']."' and id_competicion='$id_competicion') group by id_nadadora";
    echo '<br>'.$gd_acumulado.'<br>'.$query.'<br><br>';
	$resultados_nadadoras = mysqli_query($connection,$query);
	while($resultado_nadadora = mysqli_fetch_array($resultados_nadadoras)){
		$anio_nadadora = mysqli_result(mysqli_query($connection,"select año_nacimiento from nadadoras where id='".$resultado_nadadora['id_nadadora']."'"),0);
		$query = "select sum(puntos) from penalizaciones where id in (select id_penalizacion from penalizaciones_rutinas where id_inscripcion_figuras in (select id from inscripciones_figuras where id_nadadora='".$resultado_nadadora['id_nadadora']."' and id_competicion='".$id_competicion."'))";
		$puntos_penalizacion = mysqli_result(mysqli_query($connection,$query),0);
        $gd_acumulado = mysqli_result(mysqli_query($connection,"select sum(grado_dificultad) from figuras where id in (select id_figura from fases where id_competicion='".$id_competicion."' and id_categoria='".$fase_figuras['id_categoria']."')"),0);
		$nota_final_calculada = (($resultado_nadadora['sum_nota_final']/$gd_acumulado)*10) - $puntos_penalizacion;
        if($fase_figuras['elementos_coach_card']>1){
            $query = "select nota_final from inscripciones_figuras where id in (select id from inscripciones_figuras where id_nadadora='".$resultado_nadadora['id_nadadora']."' and id_competicion='".$id_competicion."')";
            $nota_final = mysqli_result(mysqli_query($connection,$query),0);
            $nota_final_calculada = ($nota_final/$gd_acumulado)*10;
            $nota_final_calculada = $nota_final;

			if(!$nota_final > 0)
				$nota_final=0;
			if(!$nota_final_calculada > 0)
				$nota_final_calculada=0;
            $query = "insert into resultados_figuras (id_nadadora, id_categoria, año, gd_acumulado, puntos_penalizacion, nota_final, nota_final_calculada, baja, preswimmer, id_competicion) values ('".$resultado_nadadora['id_nadadora']."', '".$fase_figuras['id_categoria']."', '$anio_nadadora', '$gd_acumulado"."', '$puntos_penalizacion', '$nota_final', '$nota_final_calculada', '".$resultado_nadadora['baja']."','".$resultado_nadadora['preswimmer']."','$id_competicion' )";
            echo "<br>".$query;
            mysqli_query($connection,$query);
        }else{
			if(!isset($resultado_nadadora['sum_nota_final']))
				$resultado_nadadora['sum_nota_final']=0;
            $query = "insert into resultados_figuras (id_nadadora, id_categoria, año, gd_acumulado, puntos_penalizacion, nota_final, nota_final_calculada, baja, preswimmer, id_competicion) values ('".$resultado_nadadora['id_nadadora']."', '".$fase_figuras['id_categoria']."', '$anio_nadadora', '$gd_acumulado"."', '$puntos_penalizacion', '".$resultado_nadadora['sum_nota_final']."', '$nota_final_calculada', '".$resultado_nadadora['baja']."','".$resultado_nadadora['preswimmer']."','$id_competicion' )";
		echo "<br>".$query;
		mysqli_query($connection,$query);
        }

	}
}

//ordeno y reparto puntos

$puntos = array("0", "19", "16", "14", "13","12", "11", "10", "9", "8","7", "6", "5", "4", "3","2", "1", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0",);

$query = "select año from resultados_figuras where id_competicion='$id_competicion' group by año order by id";
$categorias = mysqli_query($connection,$query);
while($categoria = mysqli_fetch_array($categorias)){
$posicion = 1;
$empatados = 0;
$empate = false;
$nota_ganador;
	$query = "select * from resultados_figuras where año='".$categoria['año']."' and id_competicion='$id_competicion' order by nota_final_calculada desc";
	$resultados_nadadoras = mysqli_query($connection,$query);
	while($resultado_nadadora = mysqli_fetch_array($resultados_nadadoras)){
		if($posicion == 1)
			$nota_ganador = $resultado_nadadora['nota_final_calculada'];
	$result = mysqli_query($connection,"select nota_final_calculada from resultados_figuras where año='".$categoria['año']."' and id_competicion='$id_competicion' and nota_final_calculada = ".$resultado_nadadora['nota_final_calculada']."");
	if(mysqli_num_rows($result) == '1'){
		echo "<br>Posicion $posicion -> ".$resultado_nadadora['id_nadadora'];
		$empate = false;
	}else{
		if($empate)
			$posicion--;
		$empate=true;
		echo "<br><b>Empate posicion $posicion -> ".$resultado_nadadora['id_nadadora']." - empatados - ".$empatados."</b>";
	}
   	if($resultado_nadadora['baja'] != 'no' and $resultado_nadadora['preswimmer'] != 'no' and $posicion <= '16'){
    	$puntos_nadadora = $puntos[$posicion];
	}else{
		$puntos_nadadora = "0";
	}
            	$puntos_nadadora = $puntos[$posicion];

	$query = "update resultados_figuras set posicion='".($posicion-$empatados)."', diferencia='".($nota_ganador - $resultado_nadadora['nota_final_calculada'])."', puntos='$puntos_nadadora' where id = ".$resultado_nadadora['id'];
	echo "<br>".$query;
	$posicion++;
	mysqli_query($connection,$query);
	}
}
$query = "update resultados_figuras set puntos='0' where (baja='si' or preswimmer='si') and id_competicion='$id_competicion'";
echo "<br>".$query;
mysqli_query($connection,$query);





$query = "UPDATE fases SET puntuada='si' WHERE id_competicion='$id_competicion'";
echo "<br>".$query;
mysqli_query($connection,$query);

?>
