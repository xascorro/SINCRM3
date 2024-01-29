<?php

include('security.php');
session_start();
extract($_POST, EXTR_PREFIX_SAME, "wddx");
//
//if ($notaE1J5 == 0){
//    $res = ($notaE1J1+$notaE1J2+$notaE1J3+$notaE1J4)/4;
//    $res = $res * 4;
//    $res = round($res);
//    $notaE1J5 = $res /4;
//}
//if ($notaE2J5 == 0){
//    $res = ($notaE2J1+$notaE2J2+$notaE2J3+$notaE2J4)/4;
//    $res = $res * 4;
//    $res = round($res);
//    $notaE2J5 = $res /4;
//}
//if ($notaE3J5 == 0){
//    $res = ($notaE3J1+$notaE3J2+$notaE3J3+$notaE3J4)/4;
//    $res = $res * 4;
//    $res = round($res);
//    $notaE3J5 = $res /4;
//}
//if ($notaE4J5 == 0){
//    $res = ($notaE4J1+$notaE4J2+$notaE4J3+$notaE4J4)/4;
//    $res = $res * 4;
//    $res = round($res);
//    $notaE4J5 = $res /4;
//}
//if ($notaE5J5 == 0){
//    $res = ($notaE5J1+$notaE5J2+$notaE5J3+$notaE5J4)/4;
//    $res = $res * 4;
//    $res = round($res);
//    $notaE5J5 = $res /4;
//}

//Añadir registro
if(isset($_POST['save_btn'])){
	$licencia = $_POST['licencia'];
	$apellidos = $_POST['apellidos'];
	$nombre = $_POST['nombre'];
	$fecha_nacimiento = $_POST['fecha_nacimiento'];
	$id_club = $_POST['id_club'];

    $query = "DELETE FROM puntuaciones_jueces WHERE id_inscripcion_figuras = $id_inscripcion_figuras";
    $query_run = mysqli_query($connection,$query);

	$query="INSERT INTO puntuaciones_jueces (id_panel_juez, id_inscripcion_figuras, id_elemento, nota) VALUES
    ('".$id_panel_juez1."','".$id_inscripcion_figuras."','1','".$notaE1J1."'),
    ('".$id_panel_juez2."','".$id_inscripcion_figuras."','1','".$notaE1J2."'),
    ('".$id_panel_juez3."','".$id_inscripcion_figuras."','1','".$notaE1J3."'),
    ('".$id_panel_juez4."','".$id_inscripcion_figuras."','1','".$notaE1J4."'),
    ('".$id_panel_juez5."','".$id_inscripcion_figuras."','1','".$notaE1J5."');";
    $query="INSERT INTO puntuaciones_jueces (id_panel_juez, id_inscripcion_figuras, id_elemento, nota) VALUES ('".$id_panel_juez1."','".$id_inscripcion_figuras."','1','".$notaE1J1."'),('".$id_panel_juez2."','".$id_inscripcion_figuras."','1','".$notaE1J2."'),('".$id_panel_juez3."','".$id_inscripcion_figuras."','1','".$notaE1J3."');";
    $query_run = mysqli_query($connection,$query);
    $query = "SELECT min(nota), max(nota) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=1";
    $min_nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['min(nota)'];
    $max_nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['max(nota)'];
    $query = "UPDATE puntuaciones_jueces SET nota_menor = 'si' WHERE nota=$min_nota and id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=1 limit 1";
    //mysqli_query($connection,$query);
    $query = "UPDATE puntuaciones_jueces SET nota_mayor = 'si' WHERE nota=$max_nota and id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=1 limit 1";
   // mysqli_query($connection,$query);

    $query="INSERT INTO puntuaciones_jueces (id_panel_juez, id_inscripcion_figuras, id_elemento, nota) VALUES ('".$id_panel_juez1."','".$id_inscripcion_figuras."','2','".$notaE2J1."'),('".$id_panel_juez2."','".$id_inscripcion_figuras."','2','".$notaE2J2."'),('".$id_panel_juez3."','".$id_inscripcion_figuras."','2','".$notaE2J3."'),
    ('".$id_panel_juez4."','".$id_inscripcion_figuras."','2','".$notaE2J4."'),
    ('".$id_panel_juez5."','".$id_inscripcion_figuras."','2','".$notaE2J5."');"; $query="INSERT INTO puntuaciones_jueces (id_panel_juez, id_inscripcion_figuras, id_elemento, nota) VALUES ('".$id_panel_juez1."','".$id_inscripcion_figuras."','2','".$notaE2J1."'),('".$id_panel_juez2."','".$id_inscripcion_figuras."','2','".$notaE2J2."'),('".$id_panel_juez3."','".$id_inscripcion_figuras."','2','".$notaE2J3."');";
    $query_run = mysqli_query($connection,$query);
    $query = "SELECT min(nota), max(nota) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=2";
    $min_nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['min(nota)'];
    $max_nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['max(nota)'];
    $query = "UPDATE puntuaciones_jueces SET nota_menor = 'si' WHERE nota=$min_nota and id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=2 limit 1";
   // mysqli_query($connection,$query);
    $query = "UPDATE puntuaciones_jueces SET nota_mayor = 'si' WHERE nota=$max_nota and id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=2 limit 1";
   // mysqli_query($connection,$query);

    $query="INSERT INTO puntuaciones_jueces (id_panel_juez, id_inscripcion_figuras, id_elemento, nota) VALUES ('".$id_panel_juez1."','".$id_inscripcion_figuras."','3','".$notaE3J1."'),('".$id_panel_juez2."','".$id_inscripcion_figuras."','3','".$notaE3J2."'),('".$id_panel_juez3."','".$id_inscripcion_figuras."','3','".$notaE3J3."'),
    ('".$id_panel_juez4."','".$id_inscripcion_figuras."','3','".$notaE3J4."'),
    ('".$id_panel_juez5."','".$id_inscripcion_figuras."','3','".$notaE3J5."');"; $query="INSERT INTO puntuaciones_jueces (id_panel_juez, id_inscripcion_figuras, id_elemento, nota) VALUES ('".$id_panel_juez1."','".$id_inscripcion_figuras."','3','".$notaE3J1."'),('".$id_panel_juez2."','".$id_inscripcion_figuras."','3','".$notaE3J2."'),('".$id_panel_juez3."','".$id_inscripcion_figuras."','3','".$notaE3J3."');";
    $query_run = mysqli_query($connection,$query);
    $query = "SELECT min(nota), max(nota) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=3";
    $min_nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['min(nota)'];
    $max_nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['max(nota)'];
    $query = "UPDATE puntuaciones_jueces SET nota_menor = 'si' WHERE nota=$min_nota and id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=3 limit 1";
   // mysqli_query($connection,$query);
    $query = "UPDATE puntuaciones_jueces SET nota_mayor = 'si' WHERE nota=$max_nota and id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=3 limit 1";
    //mysqli_query($connection,$query);

    $query="INSERT INTO puntuaciones_jueces (id_panel_juez, id_inscripcion_figuras, id_elemento, nota) VALUES ('".$id_panel_juez1."','".$id_inscripcion_figuras."','4','".$notaE4J1."'),('".$id_panel_juez2."','".$id_inscripcion_figuras."','4','".$notaE4J2."'),('".$id_panel_juez3."','".$id_inscripcion_figuras."','4','".$notaE4J3."'),
    ('".$id_panel_juez4."','".$id_inscripcion_figuras."','4','".$notaE4J4."'),
    ('".$id_panel_juez5."','".$id_inscripcion_figuras."','4','".$notaE4J5."');";
    $query="INSERT INTO puntuaciones_jueces (id_panel_juez, id_inscripcion_figuras, id_elemento, nota) VALUES ('".$id_panel_juez1."','".$id_inscripcion_figuras."','4','".$notaE4J1."'),('".$id_panel_juez2."','".$id_inscripcion_figuras."','4','".$notaE4J2."'),('".$id_panel_juez3."','".$id_inscripcion_figuras."','4','".$notaE4J3."');";
    $query_run = mysqli_query($connection,$query);
    $query = "SELECT min(nota), max(nota) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=4";
    $min_nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['min(nota)'];
    $max_nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['max(nota)'];
    $query = "UPDATE puntuaciones_jueces SET nota_menor = 'si' WHERE nota=$min_nota and id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=4 limit 1";
   // mysqli_query($connection,$query);
    $query = "UPDATE puntuaciones_jueces SET nota_mayor = 'si' WHERE nota=$max_nota and id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=4 limit 1";
   // mysqli_query($connection,$query);

    $query="INSERT INTO puntuaciones_jueces (id_panel_juez, id_inscripcion_figuras, id_elemento, nota) VALUES ('".$id_panel_juez1."','".$id_inscripcion_figuras."','5','".$notaE5J1."'),('".$id_panel_juez2."','".$id_inscripcion_figuras."','5','".$notaE5J2."'),('".$id_panel_juez3."','".$id_inscripcion_figuras."','5','".$notaE5J3."'),
    ('".$id_panel_juez4."','".$id_inscripcion_figuras."','5','".$notaE5J4."'),
    ('".$id_panel_juez5."','".$id_inscripcion_figuras."','5','".$notaE5J5."');"; $query="INSERT INTO puntuaciones_jueces (id_panel_juez, id_inscripcion_figuras, id_elemento, nota) VALUES ('".$id_panel_juez1."','".$id_inscripcion_figuras."','5','".$notaE5J1."'),('".$id_panel_juez2."','".$id_inscripcion_figuras."','5','".$notaE5J2."'),('".$id_panel_juez3."','".$id_inscripcion_figuras."','5','".$notaE5J3."');";
    $query_run = mysqli_query($connection,$query);
    $query = "SELECT min(nota), max(nota) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=5";
    $min_nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['min(nota)'];
    $max_nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['max(nota)'];
    $query = "UPDATE puntuaciones_jueces SET nota_menor = 'si' WHERE nota=$min_nota and id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=5 limit 1";
   // mysqli_query($connection,$query);
    $query = "UPDATE puntuaciones_jueces SET nota_mayor = 'si' WHERE nota=$max_nota and id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=5 limit 1";
   // mysqli_query($connection,$query);
    /////////////////////arreglar lo de arriba con un bucle
    if($notaE6J1 != ''){
        $query="INSERT INTO puntuaciones_jueces (id_panel_juez, id_inscripcion_figuras, id_elemento, nota) VALUES ('".$id_panel_juez1."','".$id_inscripcion_figuras."','6','".$notaE6J1."'),('".$id_panel_juez2."','".$id_inscripcion_figuras."','6','".$notaE6J2."'),('".$id_panel_juez3."','".$id_inscripcion_figuras."','6','".$notaE6J3."'),
        ('".$id_panel_juez4."','".$id_inscripcion_figuras."','6','".$notaE6J4."'),
        ('".$id_panel_juez5."','".$id_inscripcion_figuras."','6','".$notaE6J5."');";
        $query_run = mysqli_query($connection,$query);
        $query = "SELECT min(nota), max(nota) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=6";
        $min_nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['min(nota)'];
        $max_nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['max(nota)'];
        $query = "UPDATE puntuaciones_jueces SET nota_menor = 'si' WHERE nota=$min_nota and id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=6 limit 1";
        mysqli_query($connection,$query);
        $query = "UPDATE puntuaciones_jueces SET nota_mayor = 'si' WHERE nota=$max_nota and id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=6 limit 1";
        mysqli_query($connection,$query);
    }
    if($notaE7J1 != ''){
        $query="INSERT INTO puntuaciones_jueces (id_panel_juez, id_inscripcion_figuras, id_elemento, nota) VALUES ('".$id_panel_juez1."','".$id_inscripcion_figuras."','7','".$notaE7J1."'),('".$id_panel_juez2."','".$id_inscripcion_figuras."','7','".$notaE7J2."'),('".$id_panel_juez3."','".$id_inscripcion_figuras."','7','".$notaE7J3."'),
        ('".$id_panel_juez4."','".$id_inscripcion_figuras."','7','".$notaE7J4."'),
        ('".$id_panel_juez5."','".$id_inscripcion_figuras."','7','".$notaE7J5."');";
        $query_run = mysqli_query($connection,$query);
        $query = "SELECT min(nota), max(nota) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=7";
        $min_nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['min(nota)'];
        $max_nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['max(nota)'];
        $query = "UPDATE puntuaciones_jueces SET nota_menor = 'si' WHERE nota=$min_nota and id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=7 limit 1";
        mysqli_query($connection,$query);
        $query = "UPDATE puntuaciones_jueces SET nota_mayor = 'si' WHERE nota=$max_nota and id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=7 limit 1";
        //mysqli_query($connection,$query);
        $query = "DELETE FROM puntuaciones_jueces WHERE tipo_ia is Null and nota=0.00 and id_elemento>0 and id_inscripcion_figuras=$id_inscripcion_figuras";
        //mysqli_query($connection,$query);
    }
     if($notaE8J1 != ''){
        $query="INSERT INTO puntuaciones_jueces (id_panel_juez, id_inscripcion_figuras, id_elemento, nota) VALUES ('".$id_panel_juez1."','".$id_inscripcion_figuras."','8','".$notaE8J1."'),('".$id_panel_juez2."','".$id_inscripcion_figuras."','8','".$notaE8J2."'),('".$id_panel_juez3."','".$id_inscripcion_figuras."','8','".$notaE8J3."'),
        ('".$id_panel_juez4."','".$id_inscripcion_figuras."','8','".$notaE8J4."'),
        ('".$id_panel_juez5."','".$id_inscripcion_figuras."','8','".$notaE8J5."');";
        $query_run = mysqli_query($connection,$query);
        $query = "SELECT min(nota), max(nota) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=8";
        $min_nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['min(nota)'];
        $max_nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['max(nota)'];
        $query = "UPDATE puntuaciones_jueces SET nota_menor = 'si' WHERE nota=$min_nota and id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=8 limit 1";
        mysqli_query($connection,$query);
        $query = "UPDATE puntuaciones_jueces SET nota_mayor = 'si' WHERE nota=$max_nota and id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=8 limit 1";
        mysqli_query($connection,$query);
        $query = "DELETE FROM puntuaciones_jueces WHERE tipo_ia is Null and nota=0.00 and id_elemento>0 and id_inscripcion_figuras=$id_inscripcion_figuras";
    }

    if($notaE9J1 != ''){
        $query="INSERT INTO puntuaciones_jueces (id_panel_juez, id_inscripcion_figuras, id_elemento, nota) VALUES ('".$id_panel_juez1."','".$id_inscripcion_figuras."','9','".$notaE9J1."'),('".$id_panel_juez2."','".$id_inscripcion_figuras."','9','".$notaE9J2."'),('".$id_panel_juez3."','".$id_inscripcion_figuras."','9','".$notaE9J3."'),
        ('".$id_panel_juez4."','".$id_inscripcion_figuras."','9','".$notaE9J4."'),
        ('".$id_panel_juez5."','".$id_inscripcion_figuras."','9','".$notaE9J5."');";
        $query_run = mysqli_query($connection,$query);
        $query = "SELECT min(nota), max(nota) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=9";
        $min_nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['min(nota)'];
        $max_nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['max(nota)'];
        $query = "UPDATE puntuaciones_jueces SET nota_menor = 'si' WHERE nota=$min_nota and id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=9 limit 1";
        //mysqli_query($connection,$query);
        $query = "UPDATE puntuaciones_jueces SET nota_mayor = 'si' WHERE nota=$max_nota and id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=9 limit 1";
        //mysqli_query($connection,$query);
    }

    /////////////////////
    $query = "DELETE FROM puntuaciones_elementos WHERE id_rutina = $id_inscripcion_figuras";
    $query_run = mysqli_query($connection,$query);
    if($BM1 != ''){
            $dd1=$BM1;
            $llevado_BM = "llevado_BM='si',";
    }else
        $llevado_BM = "";
    $query="INSERT puntuaciones_elementos SET $llevado_BM
    nota_media = (SELECT (sum(nota)-min(nota)-max(nota))/(count(nota)-2) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=1),
    nota_total = (SELECT (sum(nota)-min(nota)-max(nota)) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=1),
    nota=(SELECT (sum(nota)-min(nota)-max(nota))/(count(nota)-2) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=1)*$dd1*$factor1, id_rutina=$id_inscripcion_figuras, elemento=1";
    $query="INSERT puntuaciones_elementos SET $llevado_BM
    nota_media = (SELECT (sum(nota))/(count(nota)) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=1),
    nota_total = (SELECT (sum(nota)) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=1),
    nota=(SELECT (sum(nota))/(count(nota)) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=1)*$dd1*$factor1, id_rutina=$id_inscripcion_figuras, elemento=1";
    mysqli_query($connection,$query);
    if($BM2 != ''){
            $dd2=$BM2;
            $llevado_BM = "llevado_BM='si',";
}else
        $llevado_BM = "";
    $query="INSERT puntuaciones_elementos SET $llevado_BM
    nota_media = (SELECT (sum(nota)-min(nota)-max(nota))/(count(nota)-2) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=2),
    nota_total = (SELECT (sum(nota)-min(nota)-max(nota)) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=2),
    nota=(SELECT (sum(nota)-min(nota)-max(nota))/(count(nota)-2) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=2)*$dd2*$factor2, id_rutina=$id_inscripcion_figuras, elemento=2";
    $query="INSERT puntuaciones_elementos SET $llevado_BM
    nota_media = (SELECT (sum(nota))/(count(nota)) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=2),
    nota_total = (SELECT (sum(nota)) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=2),
    nota=(SELECT (sum(nota))/(count(nota)) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=2)*$dd2*$factor2, id_rutina=$id_inscripcion_figuras, elemento=2";
    mysqli_query($connection,$query);
    if($BM3 != ''){
            $dd3=$BM3;
            $llevado_BM = "llevado_BM='si',";
    }else
        $llevado_BM = "";
    $query="INSERT puntuaciones_elementos SET $llevado_BM
    nota_media = (SELECT (sum(nota)-min(nota)-max(nota))/(count(nota)-2) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=3),
    nota_total = (SELECT (sum(nota)-min(nota)-max(nota)) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=3),
    nota=(SELECT (sum(nota)-min(nota)-max(nota))/(count(nota)-2) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=3)*$dd3*$factor3, id_rutina=$id_inscripcion_figuras, elemento=3";
    $query="INSERT puntuaciones_elementos SET $llevado_BM
    nota_media = (SELECT (sum(nota))/(count(nota)) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=3),
    nota_total = (SELECT (sum(nota)) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=3),
    nota=(SELECT (sum(nota))/(count(nota)) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=3)*$dd3*$factor3, id_rutina=$id_inscripcion_figuras, elemento=3";
    mysqli_query($connection,$query);
    if($BM4 != ''){
            $dd4=$BM4;
            $llevado_BM = "llevado_BM='si',";
    }else
        $llevado_BM = "";
    $query="INSERT puntuaciones_elementos SET $llevado_BM
    nota_media = (SELECT (sum(nota)-min(nota)-max(nota))/(count(nota)-2) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=4),
    nota_total = (SELECT (sum(nota)-min(nota)-max(nota)) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=4),
    nota=(SELECT (sum(nota)-min(nota)-max(nota))/(count(nota)-2) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=4)*$dd4*$factor4, id_rutina=$id_inscripcion_figuras, elemento=4";
    $query="INSERT puntuaciones_elementos SET $llevado_BM
    nota_media = (SELECT (sum(nota))/(count(nota)) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=4),
    nota_total = (SELECT (sum(nota)) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=4),
    nota=(SELECT (sum(nota))/(count(nota)) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=4)*$dd4*$factor4, id_rutina=$id_inscripcion_figuras, elemento=4";
    mysqli_query($connection,$query);
    if($BM5 != ''){
            $dd5=$BM5;
            $llevado_BM = "llevado_BM='si',";
    }else
        $llevado_BM = "";
    $query="INSERT puntuaciones_elementos SET $llevado_BM nota_media = (SELECT (sum(nota)-min(nota)-max(nota))/(count(nota)-2) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=5),
    nota_total = (SELECT (sum(nota)-min(nota)-max(nota)) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=5),
    nota=(SELECT (sum(nota)-min(nota)-max(nota))/(count(nota)-2) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=5)*$dd5*$factor5, id_rutina=$id_inscripcion_figuras, elemento=5";
    $query="INSERT puntuaciones_elementos SET $llevado_BM nota_media = (SELECT (sum(nota))/(count(nota)) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=5),
    nota_total = (SELECT (sum(nota)) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=5),
    nota=(SELECT (sum(nota))/(count(nota)) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=5)*$dd5*$factor5, id_rutina=$id_inscripcion_figuras, elemento=5";
    mysqli_query($connection,$query);
    if($BM6 != ''){
            $dd6=$BM6;
            $llevado_BM = "llevado_BM='si',";
    }else
        $llevado_BM = "";
    $query="INSERT puntuaciones_elementos SET $llevado_BM
    nota_media = (SELECT (sum(nota)-min(nota)-max(nota))/(count(nota)-2) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=6),
    nota_total = (SELECT (sum(nota)-min(nota)-max(nota)) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=6),
    nota=(SELECT (sum(nota)-min(nota)-max(nota))/(count(nota)-2) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=6)*$dd6*$factor6, id_rutina=$id_inscripcion_figuras, elemento=6";
    mysqli_query($connection,$query);
    if($BM7 != ''){
        $dd7=$BM7;
        $llevado_BM = "llevado_BM='si',";
    }else
        $llevado_BM = "";
    $query="INSERT puntuaciones_elementos SET $llevado_BM
    nota_media = (SELECT (sum(nota)-min(nota)-max(nota))/(count(nota)-2) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=7),
    nota_total = (SELECT (sum(nota)-min(nota)-max(nota)) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=7),
    nota=(SELECT (sum(nota)-min(nota)-max(nota))/(count(nota)-2) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=7)*$dd7*$factor7, id_rutina=$id_inscripcion_figuras, elemento=7";
    mysqli_query($connection,$query);
    if($BM8 != ''){
        $dd8=$BM8;
        $llevado_BM = "llevado_BM='si',";
    }else
        $llevado_BM = "";
    $query="INSERT puntuaciones_elementos SET $llevado_BM
    nota_media = (SELECT (sum(nota)-min(nota)-max(nota))/(count(nota)-2) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=8),
    nota_total = (SELECT (sum(nota)-min(nota)-max(nota)) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=8),
    nota=(SELECT (sum(nota)-min(nota)-max(nota))/(count(nota)-2) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=8)*$dd8*$factor8, id_rutina=$id_inscripcion_figuras, elemento=8";
    mysqli_query($connection,$query);
    if($BM9 != ''){
        $dd9=$BM9;
        $llevado_BM = "llevado_BM='si',";
    }else
        $llevado_BM = "";
    $query="INSERT puntuaciones_elementos SET $llevado_BM
    nota_media = (SELECT (sum(nota)-min(nota)-max(nota))/(count(nota)-2) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=9),
    nota_total = (SELECT (sum(nota)-min(nota)-max(nota)) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=9),
    nota=(SELECT (sum(nota)-min(nota)-max(nota))/(count(nota)-2) FROM puntuaciones_jueces WHERE id_inscripcion_figuras=$id_inscripcion_figuras and id_elemento=9)*$dd9*$factor9, id_rutina=$id_inscripcion_figuras, elemento=9";
    mysqli_query($connection,$query);

    //penalizaciones
    $query = "SELECT sum(puntos) FROM penalizaciones, penalizaciones_rutinas WHERE penalizaciones_rutinas.id_penalizacion = penalizaciones.id and penalizaciones_rutinas.id_inscripcion_figuras=$id_inscripcion_figuras";
    $penalizaciones = mysqli_fetch_assoc(mysqli_query($connection,$query))['sum(puntos)'];
    if($penalizaciones == '')
        $penalizaciones = 0;
    //nota final
    $query = "SELECT sum(nota) - $penalizaciones FROM puntuaciones_elementos WHERE id_rutina=$id_inscripcion_figuras)";
    $nota_elementos = mysqli_fetch_assoc(mysqli_query($connection,$query))['sum(nota)'];
    $query = "SELECT nota from errores_sincronizacion WHERE id_inscripcion_figuras=$id_inscripcion_figuras";
    $errores_sincro = mysqli_fetch_assoc(mysqli_query($connection,$query))['nota'];
    $query = "SELECT sum(nota) - $penalizaciones as nota FROM puntuaciones_elementos WHERE id_rutina=$id_inscripcion_figuras and elemento > 0";
    $nota_elementos = mysqli_fetch_assoc(mysqli_query($connection,$query))['nota'];
    $compensacion = 0;
    if($nota_elementos < $errores_sincro)
        $compensacion = $errores_sincro-$nota_elementos;




    $query ="UPDATE inscripciones_figuras SET nota_final = (SELECT sum(nota) - $penalizaciones FROM puntuaciones_elementos WHERE id_rutina=$id_inscripcion_figuras) WHERE id=$id_inscripcion_figuras ";
    mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Notas guardadas';
		header("Location: puntuaciones_figuras_rutina_tecnica.php?id_inscripcion_figuras=$id_inscripcion_figuras&id_fase=$id_fase");
	}else{
		$_SESSION['estado'] = 'Error al guardar las notas <br>'.mysqli_error($connection);
		header("Location: puntuaciones_figuras_rutina_tecnica.php?id_inscripcion_figuras=$id_inscripcion_figuras&id_fase=$id_fase");
	}
}

	?>
