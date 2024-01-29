<?php
include('security.php');
include('./lib/my_functions.php');

session_start();
//Recojo notas de $_POST
$id_fase = $_POST['id_fase'];
$id_inscripcion_figuras = $_POST['id_inscripcion_figuras'];
$GD = $_POST['grado_dificultad'];


if(isset($_POST['puntuar_btn'])){
    $query="SELECT numero_juez FROM panel_jueces WHERE id_fase=".$id_fase;
    $numero_de_jueces = mysqli_query($connection,$query);
    $nota_total = '';
    $i=1;
    while($numero_de_juez = mysqli_fetch_assoc($numero_de_jueces)){
        $nota = $_POST['nota'][$i]['nota'];

        if($nota > 10)
            $nota = $nota/10;
        $id_panel_jueces = $_POST['nota'][$i]['id_panel_jueces'];
        $id_juez = $_POST['nota'][$i]['id_juez'];
        //comprobamos si existe o es nuevo
        $query="SELECT id FROM puntuaciones_jueces WHERE id_inscripcion_figuras='$id_inscripcion_figuras' and id_panel_juez='$id_panel_jueces'";
        $resultado=mysqli_query($connection,$query) or die (mysqli_error());
        if (mysqli_num_rows($resultado)>0){
            $query = "UPDATE puntuaciones_jueces SET nota='$nota', nota_menor = NULL, nota_mayor = NULL WHERE id_inscripcion_figuras= '$id_inscripcion_figuras' and id_panel_juez='$id_panel_jueces'";
        }else{
            $query = "INSERT INTO puntuaciones_jueces (id_inscripcion_figuras, id_panel_juez, nota) values ('$id_inscripcion_figuras', '$id_panel_jueces', '$nota')";
        }
        mysqli_query($connection,$query);
        $i++;
    }
    if($numero_jueces>3){
    //saco minima
    $query = "SELECT min(nota) FROM puntuaciones_jueces WHERE id_inscripcion_figuras = $id_inscripcion_figuras";
    $nota_menor = mysqli_result(mysqli_query($connection,$query),0);
    $query = "UPDATE puntuaciones_jueces set nota_menor = 'si' WHERE id_inscripcion_figuras = $id_inscripcion_figuras and nota = $nota_menor limit 1";
    mysqli_query($connection,$query);
    //saco máxima
    $query = "SELECT max(nota) FROM puntuaciones_jueces WHERE id_inscripcion_figuras = $id_inscripcion_figuras";
    $nota_mayor = mysqli_result(mysqli_query($connection,$query),0);
    $query = "UPDATE puntuaciones_jueces set nota_mayor = 'si' WHERE id_inscripcion_figuras = $id_inscripcion_figuras and nota = $nota_mayor and nota_menor IS NULL limit 1";
    mysqli_query($connection,$query);
    //saco media si es necesario
    $query = "SELECT nota FROM puntuaciones_jueces WHERE nota > 0 and id_inscripcion_figuras = $id_inscripcion_figuras";
    $notas_no_cero = mysqli_num_rows(mysqli_query($connection,$query));
    if ($notas_no_cero > 0){
        $query = "SELECT sum(nota) FROM puntuaciones_jueces WHERE nota > 0 and id_inscripcion_figuras = $id_inscripcion_figuras";
        $nota_media = mysqli_result(mysqli_query($connection,$query),0)/$notas_no_cero;
        $nota_media = round($nota_media, 1, PHP_ROUND_HALF_UP);
        $query = "UPDATE puntuaciones_jueces set nota = $nota_media WHERE id_inscripcion_figuras = $id_inscripcion_figuras and nota = 0";
        mysqli_query($connection,$query);
    }
    }
    //actualizo nota de figura
        $query = "SELECT sum(nota) FROM puntuaciones_jueces WHERE id_inscripcion_figuras = $id_inscripcion_figuras and (nota_mayor IS NULL and nota_menor IS NULL) limit 1";
        $nota_total = mysqli_result(mysqli_query($connection,$query),0);
        $query = "SELECT sum(nota)/3 FROM puntuaciones_jueces WHERE id_inscripcion_figuras = $id_inscripcion_figuras and (nota_mayor IS NULL and nota_menor IS NULL)";
        $nota_media = mysqli_result(mysqli_query($connection,$query),0);
        $nota_final = $nota_media*$GD;
        $query = "UPDATE inscripciones_figuras SET nota_media = $nota_media,nota_total = $nota_total, nota_final= $nota_final WHERE id = $id_inscripcion_figuras";
        mysqli_query($connection,$query);



    echo "<script>window.close();</script>";


}
if(mysqli_error($connection) == ''){
			$_SESSION['correcto'] = 'Datos actualizados con éxito';
			header('Location: puntuaciones_lista_figuras.php');
		}else{
			$_SESSION['estado'] = 'Error, los datos no se han actualizado <br>'.mysqli_error($connection);
			header('Location: puntuaciones_lista_figuras.php');
		}
?>
