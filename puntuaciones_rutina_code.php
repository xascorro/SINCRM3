<?php

include('security.php');
extract($_POST, EXTR_PREFIX_SAME, "wddx");
//Añadir registro
if(isset($_POST['save_btn'])){
	$licencia = $_POST['licencia'];
	$apellidos = $_POST['apellidos'];
	$nombre = $_POST['nombre'];
	$fecha_nacimiento = $_POST['fecha_nacimiento'];
	$id_club = $_POST['id_club'];

//	$query="INSERT INTO puntuaciones_jueces (id_panel_juez, id_rutina, id_elemento, nota) VALUES ('".$id_panel_juez1."','".$id_rutina."','".$id_elemento."','".$notaE1J1."')";
    $query = "DELETE FROM puntuaciones_jueces WHERE id_rutina = $id_rutina";
    $query_run = mysqli_query($connection,$query);

	$query="INSERT INTO puntuaciones_jueces (id_panel_juez, id_rutina, id_elemento, nota) VALUES ('".$id_panel_juez1."','".$id_rutina."','1','".$notaE1J1."'),('".$id_panel_juez2."','".$id_rutina."','1','".$notaE1J2."'),('".$id_panel_juez3."','".$id_rutina."','1','".$notaE1J3."'),
    ('".$id_panel_juez4."','".$id_rutina."','1','".$notaE1J4."'),
    ('".$id_panel_juez5."','".$id_rutina."','1','".$notaE1J5."');";
    $query_run = mysqli_query($connection,$query);
    $query = "DELETE FROM puntuaciones_jueces WHERE tipo_ia is Null and nota=0.00 and id_elemento>0 and id_rutina=$id_rutina";
   // mysqli_query($connection,$query);
    $query = "SELECT min(nota), max(nota) FROM puntuaciones_jueces WHERE id_rutina=$id_rutina and id_elemento=1";
    $min_nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['min(nota)'];
    $max_nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['max(nota)'];
    $query = "UPDATE puntuaciones_jueces SET nota_menor = 'si' WHERE nota=$min_nota and id_rutina=$id_rutina and id_elemento=1 limit 1";
    mysqli_query($connection,$query);
    $query = "UPDATE puntuaciones_jueces SET nota_mayor = 'si' WHERE nota=$max_nota and id_rutina=$id_rutina and id_elemento=1 limit 1";
    mysqli_query($connection,$query);
    $query = "DELETE FROM puntuaciones_jueces WHERE tipo_ia is Null and nota=0.00 and id_elemento>0 and id_rutina=$id_rutina";
    //mysqli_query($connection,$query);

    $query="INSERT INTO puntuaciones_jueces (id_panel_juez, id_rutina, id_elemento, nota) VALUES ('".$id_panel_juez1."','".$id_rutina."','2','".$notaE2J1."'),('".$id_panel_juez2."','".$id_rutina."','2','".$notaE2J2."'),('".$id_panel_juez3."','".$id_rutina."','2','".$notaE2J3."'),
    ('".$id_panel_juez4."','".$id_rutina."','2','".$notaE2J4."'),
    ('".$id_panel_juez5."','".$id_rutina."','2','".$notaE2J5."');";
    $query_run = mysqli_query($connection,$query);
    $query = "SELECT min(nota), max(nota) FROM puntuaciones_jueces WHERE id_rutina=$id_rutina and id_elemento=2";
    $min_nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['min(nota)'];
    $max_nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['max(nota)'];
    $query = "UPDATE puntuaciones_jueces SET nota_menor = 'si' WHERE nota=$min_nota and id_rutina=$id_rutina and id_elemento=2 limit 1";
    mysqli_query($connection,$query);
    $query = "UPDATE puntuaciones_jueces SET nota_mayor = 'si' WHERE nota=$max_nota and id_rutina=$id_rutina and id_elemento=2 limit 1";
    mysqli_query($connection,$query);
    $query = "DELETE FROM puntuaciones_jueces WHERE tipo_ia is Null and nota=0.00 and id_elemento>0 and id_rutina=$id_rutina";
    //mysqli_query($connection,$query);

    $query="INSERT INTO puntuaciones_jueces (id_panel_juez, id_rutina, id_elemento, nota) VALUES ('".$id_panel_juez1."','".$id_rutina."','3','".$notaE3J1."'),('".$id_panel_juez2."','".$id_rutina."','3','".$notaE3J2."'),('".$id_panel_juez3."','".$id_rutina."','3','".$notaE3J3."'),
    ('".$id_panel_juez4."','".$id_rutina."','3','".$notaE3J4."'),
    ('".$id_panel_juez5."','".$id_rutina."','3','".$notaE3J5."');";
    $query_run = mysqli_query($connection,$query);
    $query = "SELECT min(nota), max(nota) FROM puntuaciones_jueces WHERE id_rutina=$id_rutina and id_elemento=3";
    $min_nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['min(nota)'];
    $max_nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['max(nota)'];
    $query = "UPDATE puntuaciones_jueces SET nota_menor = 'si' WHERE nota=$min_nota and id_rutina=$id_rutina and id_elemento=3 limit 1";
    mysqli_query($connection,$query);
    $query = "UPDATE puntuaciones_jueces SET nota_mayor = 'si' WHERE nota=$max_nota and id_rutina=$id_rutina and id_elemento=3 limit 1";
    mysqli_query($connection,$query);
    $query = "DELETE FROM puntuaciones_jueces WHERE tipo_ia is Null and nota=0.00 and id_elemento>0 and id_rutina=$id_rutina";
   // mysqli_query($connection,$query);

    $query="INSERT INTO puntuaciones_jueces (id_panel_juez, id_rutina, id_elemento, nota) VALUES ('".$id_panel_juez1."','".$id_rutina."','4','".$notaE4J1."'),('".$id_panel_juez2."','".$id_rutina."','4','".$notaE4J2."'),('".$id_panel_juez3."','".$id_rutina."','4','".$notaE4J3."'),
    ('".$id_panel_juez4."','".$id_rutina."','4','".$notaE4J4."'),
    ('".$id_panel_juez5."','".$id_rutina."','4','".$notaE4J5."');";
    $query_run = mysqli_query($connection,$query);
    $query = "SELECT min(nota), max(nota) FROM puntuaciones_jueces WHERE id_rutina=$id_rutina and id_elemento=4";
    $min_nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['min(nota)'];
    $max_nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['max(nota)'];
    $query = "UPDATE puntuaciones_jueces SET nota_menor = 'si' WHERE nota=$min_nota and id_rutina=$id_rutina and id_elemento=4 limit 1";
    mysqli_query($connection,$query);
    $query = "UPDATE puntuaciones_jueces SET nota_mayor = 'si' WHERE nota=$max_nota and id_rutina=$id_rutina and id_elemento=4 limit 1";
    mysqli_query($connection,$query);
    $query = "DELETE FROM puntuaciones_jueces WHERE tipo_ia is Null and nota=0.00 and id_elemento>0 and id_rutina=$id_rutina";
    //mysqli_query($connection,$query);

    $query="INSERT INTO puntuaciones_jueces (id_panel_juez, id_rutina, id_elemento, nota) VALUES ('".$id_panel_juez1."','".$id_rutina."','5','".$notaE5J1."'),('".$id_panel_juez2."','".$id_rutina."','5','".$notaE5J2."'),('".$id_panel_juez3."','".$id_rutina."','5','".$notaE5J3."'),
    ('".$id_panel_juez4."','".$id_rutina."','5','".$notaE5J4."'),
    ('".$id_panel_juez5."','".$id_rutina."','5','".$notaE5J5."');";
    $query_run = mysqli_query($connection,$query);
    $query = "SELECT min(nota), max(nota) FROM puntuaciones_jueces WHERE id_rutina=$id_rutina and id_elemento=5";
    $min_nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['min(nota)'];
    $max_nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['max(nota)'];
    $query = "UPDATE puntuaciones_jueces SET nota_menor = 'si' WHERE nota=$min_nota and id_rutina=$id_rutina and id_elemento=5 limit 1";
    mysqli_query($connection,$query);
    $query = "UPDATE puntuaciones_jueces SET nota_mayor = 'si' WHERE nota=$max_nota and id_rutina=$id_rutina and id_elemento=5 limit 1";
    mysqli_query($connection,$query);
    $query = "DELETE FROM puntuaciones_jueces WHERE tipo_ia is Null and nota=0.00 and id_elemento>0 and id_rutina=$id_rutina";
    //mysqli_query($connection,$query);
    /////////////////////arreglar lo de arriba con un bucle
    if($notaE6J1 != ''){
        $query="INSERT INTO puntuaciones_jueces (id_panel_juez, id_rutina, id_elemento, nota) VALUES ('".$id_panel_juez1."','".$id_rutina."','6','".$notaE6J1."'),('".$id_panel_juez2."','".$id_rutina."','6','".$notaE6J2."'),('".$id_panel_juez3."','".$id_rutina."','6','".$notaE6J3."'),
        ('".$id_panel_juez4."','".$id_rutina."','6','".$notaE6J4."'),
        ('".$id_panel_juez5."','".$id_rutina."','6','".$notaE6J5."');";
        $query_run = mysqli_query($connection,$query);
        $query = "SELECT min(nota), max(nota) FROM puntuaciones_jueces WHERE id_rutina=$id_rutina and id_elemento=6";
        $min_nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['min(nota)'];
        $max_nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['max(nota)'];
        $query = "UPDATE puntuaciones_jueces SET nota_menor = 'si' WHERE nota=$min_nota and id_rutina=$id_rutina and id_elemento=6 limit 1";
        mysqli_query($connection,$query);
        $query = "UPDATE puntuaciones_jueces SET nota_mayor = 'si' WHERE nota=$max_nota and id_rutina=$id_rutina and id_elemento=6 limit 1";
        mysqli_query($connection,$query);
        $query = "DELETE FROM puntuaciones_jueces WHERE tipo_ia is Null and nota=0.00 and id_elemento>0 and id_rutina=$id_rutina";
    }
    //mysqli_query($connection,$query);
    if($notaE7J1 != ''){
        $query="INSERT INTO puntuaciones_jueces (id_panel_juez, id_rutina, id_elemento, nota) VALUES ('".$id_panel_juez1."','".$id_rutina."','7','".$notaE7J1."'),('".$id_panel_juez2."','".$id_rutina."','7','".$notaE7J2."'),('".$id_panel_juez3."','".$id_rutina."','7','".$notaE7J3."'),
        ('".$id_panel_juez4."','".$id_rutina."','7','".$notaE7J4."'),
        ('".$id_panel_juez5."','".$id_rutina."','7','".$notaE7J5."');";
        $query_run = mysqli_query($connection,$query);
        $query = "SELECT min(nota), max(nota) FROM puntuaciones_jueces WHERE id_rutina=$id_rutina and id_elemento=7";
        $min_nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['min(nota)'];
        $max_nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['max(nota)'];
        $query = "UPDATE puntuaciones_jueces SET nota_menor = 'si' WHERE nota=$min_nota and id_rutina=$id_rutina and id_elemento=7 limit 1";
        mysqli_query($connection,$query);
        $query = "UPDATE puntuaciones_jueces SET nota_mayor = 'si' WHERE nota=$max_nota and id_rutina=$id_rutina and id_elemento=7 limit 1";
        mysqli_query($connection,$query);
        $query = "DELETE FROM puntuaciones_jueces WHERE tipo_ia is Null and nota=0.00 and id_elemento>0 and id_rutina=$id_rutina";
        //mysqli_query($connection,$query);
    }
     if($notaE8J1 != ''){
        $query="INSERT INTO puntuaciones_jueces (id_panel_juez, id_rutina, id_elemento, nota) VALUES ('".$id_panel_juez1."','".$id_rutina."','8','".$notaE8J1."'),('".$id_panel_juez2."','".$id_rutina."','8','".$notaE8J2."'),('".$id_panel_juez3."','".$id_rutina."','8','".$notaE8J3."'),
        ('".$id_panel_juez4."','".$id_rutina."','8','".$notaE8J4."'),
        ('".$id_panel_juez5."','".$id_rutina."','8','".$notaE8J5."');";
        $query_run = mysqli_query($connection,$query);
        $query = "SELECT min(nota), max(nota) FROM puntuaciones_jueces WHERE id_rutina=$id_rutina and id_elemento=8";
        $min_nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['min(nota)'];
        $max_nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['max(nota)'];
        $query = "UPDATE puntuaciones_jueces SET nota_menor = 'si' WHERE nota=$min_nota and id_rutina=$id_rutina and id_elemento=8 limit 1";
        mysqli_query($connection,$query);
        $query = "UPDATE puntuaciones_jueces SET nota_mayor = 'si' WHERE nota=$max_nota and id_rutina=$id_rutina and id_elemento=8 limit 1";
        mysqli_query($connection,$query);
        $query = "DELETE FROM puntuaciones_jueces WHERE tipo_ia is Null and nota=0.00 and id_elemento>0 and id_rutina=$id_rutina";
        //mysqli_query($connection,$query);
    }

    if($notaE9J1 != ''){
        $query="INSERT INTO puntuaciones_jueces (id_panel_juez, id_rutina, id_elemento, nota) VALUES ('".$id_panel_juez1."','".$id_rutina."','9','".$notaE9J1."'),('".$id_panel_juez2."','".$id_rutina."','9','".$notaE9J2."'),('".$id_panel_juez3."','".$id_rutina."','9','".$notaE9J3."'),
        ('".$id_panel_juez4."','".$id_rutina."','9','".$notaE9J4."'),
        ('".$id_panel_juez5."','".$id_rutina."','9','".$notaE9J5."');";
        $query_run = mysqli_query($connection,$query);
        $query = "SELECT min(nota), max(nota) FROM puntuaciones_jueces WHERE id_rutina=$id_rutina and id_elemento=9";
        $min_nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['min(nota)'];
        $max_nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['max(nota)'];
        $query = "UPDATE puntuaciones_jueces SET nota_menor = 'si' WHERE nota=$min_nota and id_rutina=$id_rutina and id_elemento=9 limit 1";
        mysqli_query($connection,$query);
        $query = "UPDATE puntuaciones_jueces SET nota_mayor = 'si' WHERE nota=$max_nota and id_rutina=$id_rutina and id_elemento=9 limit 1";
        mysqli_query($connection,$query);
        $query = "DELETE FROM puntuaciones_jueces WHERE tipo_ia is Null and nota=0.00 and id_elemento>0 and id_rutina=$id_rutina";
        //mysqli_query($connection,$query);
    }

    /////////////////////
    $query = "DELETE FROM puntuaciones_elementos WHERE id_rutina = $id_rutina";
    $query_run = mysqli_query($connection,$query);
    if($BM1 != ''){
            $dd1=$BM1;
            $llevado_BM = "llevado_BM='si',";
    }else
        $llevado_BM = "";
    $query="INSERT puntuaciones_elementos SET $llevado_BM nota_media = (SELECT (sum(nota)-min(nota)-max(nota))/(count(nota)-2) FROM puntuaciones_jueces WHERE id_rutina=$id_rutina and id_elemento=1), nota=(SELECT (sum(nota)-min(nota)-max(nota))/(count(nota)-2) FROM puntuaciones_jueces WHERE id_rutina=$id_rutina and id_elemento=1)*$dd1*$factor1, id_rutina=$id_rutina, elemento=1";
    mysqli_query($connection,$query);
    if($BM2 != ''){
            $dd2=$BM2;
            $llevado_BM = "llevado_BM='si',";
}else
        $llevado_BM = "";
    $query="INSERT puntuaciones_elementos SET $llevado_BM nota_media = (SELECT (sum(nota)-min(nota)-max(nota))/(count(nota)-2) FROM puntuaciones_jueces WHERE id_rutina=$id_rutina and id_elemento=2), nota=(SELECT (sum(nota)-min(nota)-max(nota))/(count(nota)-2) FROM puntuaciones_jueces WHERE id_rutina=$id_rutina and id_elemento=2)*$dd2*$factor2, id_rutina=$id_rutina, elemento=2";
    mysqli_query($connection,$query);
    if($BM3 != ''){
            $dd3=$BM3;
            $llevado_BM = "llevado_BM='si',";
    }else
        $llevado_BM = "";
    $query="INSERT puntuaciones_elementos SET $llevado_BM nota_media = (SELECT (sum(nota)-min(nota)-max(nota))/(count(nota)-2) FROM puntuaciones_jueces WHERE id_rutina=$id_rutina and id_elemento=3), nota=(SELECT (sum(nota)-min(nota)-max(nota))/(count(nota)-2) FROM puntuaciones_jueces WHERE id_rutina=$id_rutina and id_elemento=3)*$dd3*$factor3, id_rutina=$id_rutina, elemento=3";
    mysqli_query($connection,$query);
    if($BM4 != ''){
            $dd4=$BM4;
            $llevado_BM = "llevado_BM='si',";
    }else
        $llevado_BM = "";
    $query="INSERT puntuaciones_elementos SET $llevado_BM nota_media = (SELECT (sum(nota)-min(nota)-max(nota))/(count(nota)-2) FROM puntuaciones_jueces WHERE id_rutina=$id_rutina and id_elemento=4), nota=(SELECT (sum(nota)-min(nota)-max(nota))/(count(nota)-2) FROM puntuaciones_jueces WHERE id_rutina=$id_rutina and id_elemento=4)*$dd4*$factor4, id_rutina=$id_rutina, elemento=4";
    mysqli_query($connection,$query);
    if($BM5 != ''){
            $dd5=$BM5;
            $llevado_BM = "llevado_BM='si',";
    }else
        $llevado_BM = "";
    $query="INSERT puntuaciones_elementos SET $llevado_BM nota_media = (SELECT (sum(nota)-min(nota)-max(nota))/(count(nota)-2) FROM puntuaciones_jueces WHERE id_rutina=$id_rutina and id_elemento=5), nota=(SELECT (sum(nota)-min(nota)-max(nota))/(count(nota)-2) FROM puntuaciones_jueces WHERE id_rutina=$id_rutina and id_elemento=5)*$dd5*$factor5, id_rutina=$id_rutina, elemento=5";
    mysqli_query($connection,$query);
    if($BM6 != ''){
            $dd6=$BM6;
            $llevado_BM = "llevado_BM='si',";
    }else
        $llevado_BM = "";
    $query="INSERT puntuaciones_elementos SET $llevado_BM nota_media = (SELECT (sum(nota)-min(nota)-max(nota))/(count(nota)-2) FROM puntuaciones_jueces WHERE id_rutina=$id_rutina and id_elemento=6), nota=(SELECT (sum(nota)-min(nota)-max(nota))/(count(nota)-2) FROM puntuaciones_jueces WHERE id_rutina=$id_rutina and id_elemento=6)*$dd6*$factor6, id_rutina=$id_rutina, elemento=6";
    mysqli_query($connection,$query);
    if($BM7 != ''){
        $dd7=$BM7;
        $llevado_BM = "llevado_BM='si',";
    }else
        $llevado_BM = "";
    $query="INSERT puntuaciones_elementos SET $llevado_BM nota_media = (SELECT (sum(nota)-min(nota)-max(nota))/(count(nota)-2) FROM puntuaciones_jueces WHERE id_rutina=$id_rutina and id_elemento=7), nota=(SELECT (sum(nota)-min(nota)-max(nota))/(count(nota)-2) FROM puntuaciones_jueces WHERE id_rutina=$id_rutina and id_elemento=7)*$dd7*$factor7, id_rutina=$id_rutina, elemento=7";
    mysqli_query($connection,$query);
    if($BM8 != ''){
        $dd8=$BM8;
        $llevado_BM = "llevado_BM='si',";
    }else
        $llevado_BM = "";
    $query="INSERT puntuaciones_elementos SET $llevado_BM nota_media = (SELECT (sum(nota)-min(nota)-max(nota))/(count(nota)-2) FROM puntuaciones_jueces WHERE id_rutina=$id_rutina and id_elemento=8), nota=(SELECT (sum(nota)-min(nota)-max(nota))/(count(nota)-2) FROM puntuaciones_jueces WHERE id_rutina=$id_rutina and id_elemento=8)*$dd8*$factor8, id_rutina=$id_rutina, elemento=8";
    mysqli_query($connection,$query);
    if($BM9 != ''){
        $dd9=$BM9;
        $llevado_BM = "llevado_BM='si',";
    }else
        $llevado_BM = "";
    $query="INSERT puntuaciones_elementos SET $llevado_BM nota_media = (SELECT (sum(nota)-min(nota)-max(nota))/(count(nota)-2) FROM puntuaciones_jueces WHERE id_rutina=$id_rutina and id_elemento=9), nota=(SELECT (sum(nota)-min(nota)-max(nota))/(count(nota)-2) FROM puntuaciones_jueces WHERE id_rutina=$id_rutina and id_elemento=9)*$dd9*$factor9, id_rutina=$id_rutina, elemento=9";
    mysqli_query($connection,$query);
//    SINCRONIZACIÓN
    $query = "DELETE FROM errores_sincronizacion WHERE id_rutina='$id_rutina'";
    mysqli_query($connection,$query);
    $nota = $errores_pequenos*0.1+$errores_obvios*0.5+$errores_mayores*3;
    $query = "INSERT INTO errores_sincronizacion (id_rutina, errores_pequenos, errores_obvios, errores_mayores, nota) VALUES ('$id_rutina', '$errores_pequenos', '$errores_obvios', '$errores_mayores', '$nota')";
    mysqli_query($connection,$query);
// IMPRESIÓN ARTÍSTICA
//    ChoMu
    $query="INSERT INTO puntuaciones_jueces (id_panel_juez, id_rutina, tipo_ia, nota) VALUES ('".$id_panel_juez_ChoMu1."','".$id_rutina."','ChoMu','".$notaChoMuJ1."'),('".$id_panel_juez_ChoMu2."','".$id_rutina."','ChoMu','".$notaChoMuJ2."'),('".$id_panel_juez_ChoMu3."','".$id_rutina."','ChoMu','".$notaChoMuJ3."'),
    ('".$id_panel_juez_ChoMu4."','".$id_rutina."','ChoMu','".$notaChoMuJ4."'),
    ('".$id_panel_juez_ChoMu5."','".$id_rutina."','ChoMu','".$notaChoMuJ5."');";
    mysqli_query($connection,$query);
    $query = "SELECT min(nota), max(nota) FROM puntuaciones_jueces WHERE id_rutina=$id_rutina and tipo_ia like 'ChoMu'";
    $min_nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['min(nota)'];
    $max_nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['max(nota)'];
    $query = "UPDATE puntuaciones_jueces SET nota_menor = 'si' WHERE nota=$min_nota and id_rutina=$id_rutina and tipo_ia like 'Chomu' limit 1";
    mysqli_query($connection,$query);
    $query = "UPDATE puntuaciones_jueces SET nota_mayor = 'si' WHERE nota=$max_nota and id_rutina=$id_rutina and tipo_ia like 'ChoMu' limit 1";
    mysqli_query($connection,$query);
    $query="INSERT puntuaciones_elementos SET nota=(SELECT (sum(nota)-$min_nota-$max_nota) FROM puntuaciones_jueces WHERE id_rutina=$id_rutina and tipo_ia='ChoMu')*$f_chomu, id_rutina=$id_rutina, tipo_ia='ChoMu'";
    mysqli_query($connection,$query);
//    PERFORMANCE
    $query="INSERT INTO puntuaciones_jueces (id_panel_juez, id_rutina, tipo_ia, nota) VALUES ('".$id_panel_juez_Performance1."','".$id_rutina."','Performance','".$notaPerformanceJ1."'),('".$id_panel_juez_Performance2."','".$id_rutina."','Performance','".$notaPerformanceJ2."'),('".$id_panel_juez_Performance3."','".$id_rutina."','Performance','".$notaPerformanceJ3."'),
    ('".$id_panel_juez_Performance4."','".$id_rutina."','Performance','".$notaPerformanceJ4."'),
    ('".$id_panel_juez_Performance5."','".$id_rutina."','Performance','".$notaPerformanceJ5."');";

    mysqli_query($connection,$query);
    $query = "SELECT min(nota), max(nota) FROM puntuaciones_jueces WHERE id_rutina=$id_rutina and tipo_ia like 'Performance'";
    $min_nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['min(nota)'];
    $max_nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['max(nota)'];
    $query = "UPDATE puntuaciones_jueces SET nota_menor = 'si' WHERE nota=$min_nota and id_rutina=$id_rutina and tipo_ia like 'Performance' limit 1";
    mysqli_query($connection,$query);
    $query = "UPDATE puntuaciones_jueces SET nota_mayor = 'si' WHERE nota=$max_nota and id_rutina=$id_rutina and tipo_ia like 'Performance' limit 1";
    mysqli_query($connection,$query);
    $query="INSERT puntuaciones_elementos SET nota=(SELECT (sum(nota)-$min_nota-$max_nota) FROM puntuaciones_jueces WHERE id_rutina=$id_rutina and tipo_ia='Performance')*$factor_Performance, id_rutina=$id_rutina, tipo_ia='Performance'";
    mysqli_query($connection,$query);
//    TRANSITIONS
    $query="INSERT INTO puntuaciones_jueces (id_panel_juez, id_rutina, tipo_ia, nota) VALUES ('".$id_panel_juez_Transitions1."','".$id_rutina."','Transitions','".$notaTransitionsJ1."'),('".$id_panel_juez_Transitions2."','".$id_rutina."','Transitions','".$notaTransitionsJ2."'),('".$id_panel_juez_Transitions3."','".$id_rutina."','Transitions','".$notaTransitionsJ3."'),
    ('".$id_panel_juez_Transitions4."','".$id_rutina."','Transitions','".$notaTransitionsJ4."'),
    ('".$id_panel_juez_Transitions5."','".$id_rutina."','Transitions','".$notaTransitionsJ5."');";
    mysqli_query($connection,$query);
    $query = "SELECT min(nota), max(nota) FROM puntuaciones_jueces WHERE id_rutina=$id_rutina and tipo_ia like 'Transitions'";
    $min_nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['min(nota)'];
    $max_nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['max(nota)'];
    $query = "UPDATE puntuaciones_jueces SET nota_menor = 'si' WHERE nota=$min_nota and id_rutina=$id_rutina and tipo_ia like 'Transitions' limit 1";
    mysqli_query($connection,$query);
    $query = "UPDATE puntuaciones_jueces SET nota_mayor = 'si' WHERE nota=$max_nota and id_rutina=$id_rutina and tipo_ia like 'Transitions' limit 1";
    mysqli_query($connection,$query);
    $query="INSERT puntuaciones_elementos SET nota=(SELECT (sum(nota)-$min_nota-$max_nota) FROM puntuaciones_jueces WHERE id_rutina=$id_rutina and tipo_ia='Transitions')*$factor_Transitions, id_rutina=$id_rutina, tipo_ia='Transitions'";
    mysqli_query($connection,$query);
    //penalizaciones
    $query = "SELECT sum(puntos) FROM penalizaciones, penalizaciones_rutinas WHERE penalizaciones_rutinas.id_penalizacion = penalizaciones.id and penalizaciones_rutinas.id_rutina=$id_rutina";
    $penalizaciones = mysqli_fetch_assoc(mysqli_query($connection,$query))['sum(puntos)'];
    if($penalizaciones == '')
        $penalizaciones = 0;
    //nota final
    $query = "SELECT sum(nota) - $penalizaciones FROM puntuaciones_elementos WHERE id_rutina=$id_rutina)";
    $nota_elementos = mysqli_fetch_assoc(mysqli_query($connection,$query))['sum(nota)'];
    $query = "SELECT nota from errores_sincronizacion WHERE id_rutina=$id_rutina";
    $errores_sincro = mysqli_fetch_assoc(mysqli_query($connection,$query))['nota'];
    $query = "SELECT sum(nota) - $penalizaciones as nota FROM puntuaciones_elementos WHERE id_rutina=$id_rutina and elemento > 0";
    $nota_elementos = mysqli_fetch_assoc(mysqli_query($connection,$query))['nota'];
    $compensacion = 0;
    if($nota_elementos < $errores_sincro)
        $compensacion = $errores_sincro-$nota_elementos;




    $query ="UPDATE rutinas SET nota_final = (SELECT sum(nota) - $penalizaciones FROM puntuaciones_elementos WHERE id_rutina=$id_rutina)-(SELECT nota from errores_sincronizacion WHERE id_rutina=$id_rutina) + $compensacion WHERE id=$id_rutina ";
    mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Notas guardadas';
		header("Location: puntuaciones_rutina.php?id_rutina=$id_rutina&id_fase=$id_fase");
	}else{
		$_SESSION['estado'] = 'Error al guardar las notas <br>'.mysqli_error($connection);
		header("Location: puntuaciones_rutina.php?id_rutina=$id_rutina");
	}
}

	?>
