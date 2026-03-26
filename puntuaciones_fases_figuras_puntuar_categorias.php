<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('security.php');
include('./lib/my_functions.php');

session_start();
$id_competicion = $_SESSION['id_competicion_activa'];

// Estilos HTML para los echos
?>
<style>
    .section-title { background:#2c3e50; color:#fff; padding:15px; margin:20px 0 10px 0; border-radius:5px; font-weight:bold; font-size:1.1em; }
    .subsection-title { background:#34495e; color:#fff; padding:10px; margin:15px 0 8px 0; border-radius:3px; font-size:0.95em; }
    .info-box { background:#ecf0f1; border-left:4px solid #3498db; padding:10px; margin:8px 0; border-radius:3px; }
    .success-box { background:#d4edda; border-left:4px solid #28a745; padding:10px; margin:8px 0; border-radius:3px; color:#155724; }
    .error-box { background:#f8d7da; border-left:4px solid #dc3545; padding:10px; margin:8px 0; border-radius:3px; color:#721c24; }
    .data-grid { background:#fff; border:1px solid #ddd; padding:10px; margin:8px 0; border-radius:3px; font-family:monospace; font-size:0.9em; }
    .counter { background:#ecf0f1; padding:10px; margin:8px 0; border-radius:3px; text-align:center; font-weight:bold; }
</style>
<?php

echo '<div class="section-title">🔄 INICIANDO CÁLCULO DE PUNTUACIONES - FIGURAS</div>';
echo '<div class="info-box">Competición ID: <strong>'.$id_competicion.'</strong></div>';

// Limpiar resultados previos
$query = "DELETE FROM resultados_figuras_categorias WHERE id_competicion='".$id_competicion."'";
echo '<div class="info-box">✓ Limpiando resultados previos...</div>';
mysqli_query($connection,$query);

// Precalcular penalizaciones por nadadora una sola vez
echo '<div class="section-title">⚠️ CARGANDO PENALIZACIONES</div>';
$penalizaciones_por_nadadora = array();
$query_pen = "SELECT DISTINCT 
    inf.id_nadadora,
    SUM(p.puntos) as total_puntos
FROM penalizaciones_rutinas pr
INNER JOIN penalizaciones p ON pr.id_penalizacion = p.id
INNER JOIN inscripciones_figuras inf ON pr.id_inscripcion_figuras = inf.id
INNER JOIN fases f ON inf.id_fase = f.id
WHERE f.id_competicion = '".$id_competicion."'
GROUP BY inf.id_nadadora";
$result_pen = mysqli_query($connection, $query_pen);
$penalizaciones_count = 0;
while($row_pen = mysqli_fetch_array($result_pen)) {
    $penalizaciones_por_nadadora[$row_pen['id_nadadora']] = $row_pen['total_puntos'];
    $penalizaciones_count++;
}
echo '<div class="success-box">✓ Se cargaron '.$penalizaciones_count.' nadadoras con penalizaciones</div>';

// Precalcular nombres de nadadoras
$nombres_nadadoras = array();
$query_nombres = "SELECT id, apellidos, nombre FROM nadadoras WHERE id IN (SELECT DISTINCT id_nadadora FROM inscripciones_figuras WHERE id_fase IN (SELECT id FROM fases WHERE id_competicion = '".$id_competicion."'))";
$result_nombres = mysqli_query($connection, $query_nombres);
while($row_nom = mysqli_fetch_array($result_nombres)) {
    $nombres_nadadoras[$row_nom['id']] = $row_nom['apellidos'].', '.$row_nom['nombre'];
}
echo '<div class="success-box">✓ Se cargaron '.count($nombres_nadadoras).' nombres de nadadoras</div>';



//variables
$posicion = 1;
$total_categorias = 0;
$total_resultados = 0;

echo '<div class="section-title">📋 PROCESANDO CATEGORÍAS</div>';

//obtengo las categorias de la competicion
$query = "select * from fases where id_competicion='$id_competicion' group by id_categoria order by orden asc ";
$fases = mysqli_query($connection,$query);
while($fase_figuras = mysqli_fetch_array($fases)){
	$total_categorias++;
	$nombre_categoria = mysqli_result(mysqli_query($connection,"select nombre from categorias where id='".$fase_figuras['id_categoria']."'"),0);
	echo '<div class="subsection-title">Categoría: '.$nombre_categoria.' (ID: '.$fase_figuras['id_categoria'].')</div>';
	$gd_acumulado = mysqli_result(mysqli_query($connection,"select sum(grado_dificultad) from figuras where id in (select id_figura from fases where id_categoria='".$fase_figuras['id_categoria']."')"),0);
	echo '<div class="info-box">GD Acumulado: <strong>'.$gd_acumulado.'</strong></div>';

	$query = "select id_nadadora, sum(nota_final) as sum_nota_final, baja, preswimmer, id from inscripciones_figuras where id_fase in (select id from fases where id_categoria='".$fase_figuras['id_categoria'].@$condicion."' and id_competicion='$id_competicion') group by id_nadadora";
	$resultados_nadadoras = mysqli_query($connection,$query);
	$nadadoras_categoria = mysqli_num_rows($resultados_nadadoras);
	echo '<div class="counter">Procesando '.$nadadoras_categoria.' nadadoras...</div>';
	
	while($resultado_nadadora = mysqli_fetch_array($resultados_nadadoras)){
		$total_resultados++;
		$nombre_completo = isset($nombres_nadadoras[$resultado_nadadora['id_nadadora']]) ? $nombres_nadadoras[$resultado_nadadora['id_nadadora']] : 'ID: '.$resultado_nadadora['id_nadadora'];
		$anio_nadadora = mysqli_result(mysqli_query($connection,"select año_nacimiento from nadadoras where id='".$resultado_nadadora['id_nadadora']."'"),0);
		// Usar penalizaciones precalculadas en lugar de recalcularlas
		$puntos_penalizacion = isset($penalizaciones_por_nadadora[$resultado_nadadora['id_nadadora']]) ? $penalizaciones_por_nadadora[$resultado_nadadora['id_nadadora']] : 0;

        $gd_acumulado = mysqli_result(mysqli_query($connection,"select sum(grado_dificultad) from figuras where id in (select id_figura from fases where id_categoria='".$fase_figuras['id_categoria']."'  and id_competicion = '".$id_competicion."' )"),0);
		
		$nota_final_calculada = (($resultado_nadadora['sum_nota_final']/$gd_acumulado)*10) - $puntos_penalizacion;
		
		echo '<div class="data-grid">';
		echo '<strong>Nadadora: '.$nombre_completo.' (ID: '.$resultado_nadadora['id_nadadora'].'):</strong> ';
		echo '(('.$resultado_nadadora['sum_nota_final'].'/'.$gd_acumulado.')*10) - '.$puntos_penalizacion.' puntos = <strong>'.$nota_final_calculada.'</strong>';
		echo '</div>';
		
        if($fase_figuras['elementos_coach_card']>1){
            $query = "select nota_final from inscripciones_figuras where id in (select id from inscripciones_figuras where id_nadadora='".$resultado_nadadora['id_nadadora']."' and id_competicion='".$id_competicion."')";
            $nota_final = mysqli_result(mysqli_query($connection,$query),0);
            $nota_final_calculada = $nota_final*$gd_acumulado/10;
            $nota_final_calculada = $nota_final;

			if(!$nota_final > 0)
				$nota_final=0;
			if(!$nota_final_calculada > 0)
				$nota_final_calculada=0;
            $query = "insert into resultados_figuras_categorias (id_nadadora, id_categoria, año, gd_acumulado, puntos_penalizacion, nota_final, nota_final_calculada, baja, preswimmer, id_competicion) values ('".$resultado_nadadora['id_nadadora']."', '".$fase_figuras['id_categoria']."', '$anio_nadadora', '$gd_acumulado"."', '$puntos_penalizacion', '$nota_final', '$nota_final_calculada', '".$resultado_nadadora['baja']."','".$resultado_nadadora['preswimmer']."','$id_competicion' )";
            mysqli_query($connection,$query);
        }else{

			if(!isset($resultado_nadadora['sum_nota_final']))
				$resultado_nadadora['sum_nota_final']=0;

			$fase = $fase_figuras['id_categoria'];

            $query = "insert into resultados_figuras_categorias (id_nadadora, id_categoria, año, gd_acumulado, puntos_penalizacion, nota_final, nota_final_calculada, baja, preswimmer, id_competicion) values ('".$resultado_nadadora['id_nadadora']."', '".$fase."', '$anio_nadadora', '$gd_acumulado"."', '$puntos_penalizacion', '".$resultado_nadadora['sum_nota_final']."', '$nota_final_calculada', '".$resultado_nadadora['baja']."','".$resultado_nadadora['preswimmer']."','$id_competicion' )";
			mysqli_query($connection,$query);
        }

	}
}

echo '<div class="section-title">🏆 CALCULANDO POSICIONES Y PUNTOS</div>';
echo '<div class="info-box">Total: '.$total_categorias.' categorías procesadas con '.$total_resultados.' resultados</div>';

//ordeno y reparto puntos

$puntos = array("0", "19", "16", "14", "13","12", "11", "10", "9", "8","7", "6", "5", "4", "3","2", "1", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0",);

$query = "select id_categoria from resultados_figuras_categorias where id_competicion='$id_competicion' group by id_categoria order by id_categoria";
$categorias = mysqli_query($connection,$query);
$posiciones_totales = 0;

while($categoria = mysqli_fetch_array($categorias)){
$nombre_categoria = mysqli_result(mysqli_query($connection,"select nombre from categorias where id='".$categoria['id_categoria']."'"),0);
echo '<div class="subsection-title">Ranking Categoría: '.$nombre_categoria.' (ID: '.$categoria['id_categoria'].')</div>';
$posicion = 1;
$empatados = 0;
$empate = false;
$nota_ganador;
	$query = "select * from resultados_figuras_categorias where id_categoria='".$categoria['id_categoria']."' and id_competicion='$id_competicion' order by nota_final_calculada desc";
	$resultados_nadadoras = mysqli_query($connection,$query);
	
	while($resultado_nadadora = mysqli_fetch_array($resultados_nadadoras)){
		if($posicion == 1)
			$nota_ganador = $resultado_nadadora['nota_final_calculada'];
	$result = mysqli_query($connection,"select nota_final_calculada from resultados_figuras_categorias where id_categoria='".$categoria['id_categoria']."' and id_competicion='$id_competicion' and nota_final_calculada = ".$resultado_nadadora['nota_final_calculada']."");
	if(mysqli_num_rows($result) == '1'){
		$posiciones_totales++;
		$nombre_nadadora = isset($nombres_nadadoras[$resultado_nadadora['id_nadadora']]) ? $nombres_nadadoras[$resultado_nadadora['id_nadadora']] : 'ID: '.$resultado_nadadora['id_nadadora'];
		$info = '<strong>Pos. '.$posicion.'</strong> - '.$nombre_nadadora.': '.$resultado_nadadora['nota_final_calculada'].' puntos';
		echo '<div class="info-box">'.$info.'</div>';
		$empate = false;
	}else{
		if($empate)
			$posicion--;
		$empate=true;
		$posiciones_totales++;
		$nombre_nadadora = isset($nombres_nadadoras[$resultado_nadadora['id_nadadora']]) ? $nombres_nadadoras[$resultado_nadadora['id_nadadora']] : 'ID: '.$resultado_nadadora['id_nadadora'];
		$info = '<strong>Pos. '.$posicion.' (EMPATE)</strong> - '.$nombre_nadadora.': '.$resultado_nadadora['nota_final_calculada'].' puntos';
		echo '<div class="info-box" style="background:#fff3cd; border-left-color:#ffc107;">'.$info.'</div>';
	}
   	if($resultado_nadadora['baja'] != 'no' and $resultado_nadadora['preswimmer'] != 'no' and $posicion <= '16'){
    	$puntos_nadadora = $puntos[$posicion];
	}else{
		$puntos_nadadora = "0";
	}
            	$puntos_nadadora = $puntos[$posicion];

	$query = "update resultados_figuras_categorias set posicion='".($posicion-$empatados)."', diferencia='".($nota_ganador - $resultado_nadadora['nota_final_calculada'])."', puntos='$puntos_nadadora' where id = ".$resultado_nadadora['id'];
	
	$posicion++;
	mysqli_query($connection,$query);
	}
}

echo '<div class="success-box">✓ Se calcularon posiciones para '.$posiciones_totales.' participantes</div>';

$query = "update resultados_figuras_categorias set puntos='0' where (baja='si' or preswimmer='si') and id_competicion='$id_competicion'";
echo '<div class="info-box">✓ Se anularon puntos para nadadoras baja/preswimmer</div>';
mysqli_query($connection,$query);

echo '<div class="section-title">✅ FINALIZANDO PROCESO</div>';

$query = "UPDATE fases SET puntuada='si' WHERE id_competicion='$id_competicion'";
echo '<div class="success-box">✓ Se marcaron todas las fases como puntuadas</div>';
mysqli_query($connection,$query);

echo '<div class="success-box" style="background:#d4edda; border:2px solid #28a745; padding:15px; text-align:center; font-size:1.1em;">
    <strong>🎉 CÁLCULO COMPLETADO CON ÉXITO</strong><br>
    Competición: '.$id_competicion.' | Categorías: '.$total_categorias.' | Resultados: '.$total_resultados.'
</div>';
?>
