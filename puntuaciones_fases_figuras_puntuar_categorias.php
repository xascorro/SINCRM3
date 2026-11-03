<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('security.php');
include('./lib/my_functions.php');

session_start();
$id_competicion = $_SESSION['id_competicion_activa'];
$query = "DELETE FROM resultados_figuras_categorias WHERE id_competicion='".$id_competicion."'";
echo $query;
mysqli_query($connection,$query);
//variables
$posicion = 1;
//obtengo las categorias de la competicion
$query = "select * from fases where id_competicion='$id_competicion' group by id_categoria order by orden asc ";
echo $query;
$fases = mysqli_query($connection,$query);
while($fase_figuras = mysqli_fetch_array($fases)){
    // Cálculo de GD Acumulado (Soporte para Figuras y TRE)
    if($fase_figuras['elementos_coach_card'] > 0) {
        // Para TRE, obtenemos el sumatorio de DDs de una inscripción de muestra de esta fase
        $q_sample = "SELECT id FROM inscripciones_figuras WHERE id_fase IN (SELECT id FROM fases WHERE id_categoria='".$fase_figuras['id_categoria']."' AND id_competicion='$id_competicion') LIMIT 1";
        $id_sample = mysqli_result(mysqli_query($connection, $q_sample), 0);
        $gd_acumulado = mysqli_result(mysqli_query($connection, "SELECT sum(valor) FROM hibridos_rutina WHERE id_rutina = '$id_sample' AND tipo = 'dd'"), 0);
    } else {
	    $gd_acumulado = mysqli_result(mysqli_query($connection,"select sum(grado_dificultad) from figuras where id in (select id_figura from fases where id_categoria='".$fase_figuras['id_categoria']."')"),0);
    }

	$query = "select id_nadadora, sum(nota_final) as sum_nota_final, baja, preswimmer, id from inscripciones_figuras where id_fase in (select id from fases where id_categoria='".$fase_figuras['id_categoria']."' and id_competicion='$id_competicion') group by id_nadadora";
    echo '<br>GD Acumulado Categoría: '.$gd_acumulado.'<br>'.$query.'<br><br>';
	$resultados_nadadoras = mysqli_query($connection,$query);
	while($resultado_nadadora = mysqli_fetch_array($resultados_nadadoras)){
		$anio_nadadora = mysqli_result(mysqli_query($connection,"select año_nacimiento from nadadoras where id='".$resultado_nadadora['id_nadadora']."'"),0);
		$query = "select sum(puntos) from penalizaciones where id in (select id_penalizacion from penalizaciones_rutinas where id_inscripcion_figuras in (select id from inscripciones_figuras where id_nadadora='".$resultado_nadadora['id_nadadora']."' and id_competicion='".$id_competicion."'))";
		$puntos_penalizacion = mysqli_result(mysqli_query($connection,$query),0);

        if($gd_acumulado > 0){
		    $nota_final_calculada = (($resultado_nadadora['sum_nota_final']/$gd_acumulado)*10) - $puntos_penalizacion;
        } else {
            $nota_final_calculada = 0;
        }

		echo '<br>Nadadora ID: '.$resultado_nadadora['id_nadadora'].' | Nota Final: '.$resultado_nadadora['sum_nota_final'].' | GD: '.$gd_acumulado.' | Calc: '.$nota_final_calculada;
        
        $fase = $fase_figuras['id_categoria'];
        $query = "insert into resultados_figuras_categorias (id_nadadora, id_categoria, año, gd_acumulado, puntos_penalizacion, nota_final, nota_final_calculada, baja, preswimmer, id_competicion) values ('".$resultado_nadadora['id_nadadora']."', '".$fase."', '$anio_nadadora', '$gd_acumulado"."', '$puntos_penalizacion', '".$resultado_nadadora['sum_nota_final']."', '$nota_final_calculada', '".$resultado_nadadora['baja']."','".$resultado_nadadora['preswimmer']."','$id_competicion' )";
		echo "<br>SQL: ".$query;
		mysqli_query($connection,$query);
	}
}

// Inserción de resultados para fases de rutinas técnicas (Junior / Absoluta) en competiciones mixtas
$q_fases_rut = "SELECT f.id, f.id_categoria FROM fases f WHERE f.id_competicion = '$id_competicion' AND (f.id_figura IS NULL OR f.id_figura = 0) AND f.id_modalidad > 0 AND f.id_modalidad != 8";
$res_fases_rut = mysqli_query($connection, $q_fases_rut);
if($res_fases_rut && mysqli_num_rows($res_fases_rut) > 0) {
    while($fr = mysqli_fetch_assoc($res_fases_rut)) {
        $id_fase_rut = $fr['id'];
        $id_cat_rut = $fr['id_categoria'];
        
        $q_insert_rut_results = "INSERT INTO resultados_figuras_categorias (id_nadadora, id_categoria, año, gd_acumulado, puntos_penalizacion, nota_final, nota_final_calculada, baja, preswimmer, id_competicion)
            SELECT rp.id_nadadora, '$id_cat_rut', n.año_nacimiento, r.dd_total, 0, r.nota_final, r.nota_final, r.baja, r.preswimmer, '$id_competicion'
            FROM rutinas r
            JOIN rutinas_participantes rp ON rp.id_rutina = r.id AND rp.reserva = 'no'
            JOIN nadadoras n ON rp.id_nadadora = n.id
            WHERE r.id_fase = '$id_fase_rut' AND r.id_competicion = '$id_competicion'";
        mysqli_query($connection, $q_insert_rut_results);
    }
}

// Ordenación y asignación de notas oficiales (Regla 9 FNRM: 100% nota real)
$query = "select id_categoria from resultados_figuras_categorias where id_competicion='$id_competicion' group by id_categoria order by id_categoria";
$categorias = mysqli_query($connection,$query);
while($categoria = mysqli_fetch_array($categorias)){
    $posicion = 1;
    $empate = false;
    $nota_ganador = 0;
    $nota_anterior = -1;
    
    $query = "select * from resultados_figuras_categorias where id_categoria='".$categoria['id_categoria']."' and id_competicion='$id_competicion' order by nota_final_calculada desc";
    $resultados_nadadoras = mysqli_query($connection,$query);
    $idx = 0;
    
    while($resultado_nadadora = mysqli_fetch_array($resultados_nadadoras)){
        if($idx == 0){
            $nota_ganador = (float)$resultado_nadadora['nota_final_calculada'];
        }
        
        $nota_actual = (float)$resultado_nadadora['nota_final_calculada'];
        if($idx > 0 && abs($nota_actual - $nota_anterior) < 0.0001){
            // Empate exacto: mantiene posición (ex aequo)
        } else {
            $posicion = $idx + 1;
        }
        $nota_anterior = $nota_actual;
        
        // Puntos al 100% de la nota real directa
        $puntos_nadadora = ($resultado_nadadora['baja'] == 'si' || $resultado_nadadora['preswimmer'] == 'si') ? 0.0000 : $nota_actual;
        $diferencia = max(0, $nota_ganador - $nota_actual);

        $query_up = "UPDATE resultados_figuras_categorias SET posicion='$posicion', diferencia='$diferencia', puntos='$puntos_nadadora' WHERE id = ".$resultado_nadadora['id'];
        mysqli_query($connection, $query_up);
        $idx++;
    }
}

$query = "update resultados_figuras_categorias set puntos='0' where (baja='si' or preswimmer='si') and id_competicion='$id_competicion'";
mysqli_query($connection,$query);





$query = "UPDATE fases SET puntuada='si' WHERE id_competicion='$id_competicion'";
echo "<br>".$query;
mysqli_query($connection,$query);

?>
