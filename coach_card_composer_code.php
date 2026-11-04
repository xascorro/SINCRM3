<?php
include('security.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//actualizar tema rutina
if(isset($_POST['update_tematica_btn'])){
	foreach($_POST as $nombre_campo => $valor){
		$asignacion = "\$" . $nombre_campo . "='" . $valor . "';";
		eval($asignacion);
	}
	$query = "UPDATE rutinas SET tematica = '$tematica' WHERE id = $id_rutina";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Tema actualizado con éxito. ';
	}else{
		$_SESSION['estado'] = 'Error, el tema no se ha actualizado <br>'.mysqli_error($connection);
	}
}
//Actualizar registro
if(isset($_POST['update_btn'])){
	foreach($_POST as $nombre_campo => $valor){
		$asignacion = "\$" . $nombre_campo . "='" . $valor . "';";
		eval($asignacion);
	}

	//actualizar TIME_INICIO
	$query = "UPDATE hibridos_rutina SET texto = '$time_inicio' WHERE tipo='time_inicio' and id_rutina ='$id_rutina' and elemento='$elemento'";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Inicio actualizado con éxito. ';
	}else{
		$_SESSION['estado'] = 'Error, el Time no se ha actualizado <br>'.mysqli_error($connection);
	}
	//actualizar TIME_FIN
	$query = "UPDATE hibridos_rutina SET texto = '$time_fin' WHERE tipo='time_fin' and id_rutina ='$id_rutina' and elemento='$elemento'";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Inicio actualizado con éxito. ';
	}else{
		$_SESSION['estado'] = 'Error, el Time no se ha actualizado <br>'.mysqli_error($connection);
	}
	//actualizar TIPO DE HIBRIDO (PART)
    $id_th = (int)$id_tipo_hibrido;
	$query = "UPDATE hibridos_rutina SET id_dificultad = '$id_th', texto = '$id_th' WHERE tipo='part' and id_rutina ='$id_rutina' and elemento='$elemento' and texto not like '3'";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] .= 'PART actualizada con éxito. ';
	}else{
		$_SESSION['estado'] .= 'Error, la PART no se ha actualizado <br>'.mysqli_error($connection);
	}

	//actualizar TIPO DE BASEMARK (Por ID con fallback a código)
    $bm_val = trim($Basemark0 ?? '');
    $texto_bm = '';
    $valor_bm = 'NULL';
    $id_dif_bm = 'NULL';

    if(!empty($bm_val)){
        if(is_numeric($bm_val)){
            $bm_id_int = (int)$bm_val;
            $q_bm = mysqli_query($connection, "SELECT id, codigo, valor FROM dificultad_basemark WHERE id = '$bm_id_int'");
            if($r_bm = mysqli_fetch_assoc($q_bm)){
                $texto_bm = $r_bm['codigo'];
                $valor_bm = "'" . number_format((float)$r_bm['valor'], 3, '.', '') . "'";
                $id_dif_bm = (int)$r_bm['id'];
            }
        } else {
            $bm_code_safe = mysqli_real_escape_string($connection, $bm_val);
            $q_bm = mysqli_query($connection, "SELECT id, codigo, valor FROM dificultad_basemark WHERE codigo = '$bm_code_safe' LIMIT 1");
            if($r_bm = mysqli_fetch_assoc($q_bm)){
                $texto_bm = $r_bm['codigo'];
                $valor_bm = "'" . number_format((float)$r_bm['valor'], 3, '.', '') . "'";
                $id_dif_bm = (int)$r_bm['id'];
            }
        }
    }

	$query = "UPDATE hibridos_rutina SET id_dificultad = $id_dif_bm, texto = '$texto_bm', valor = $valor_bm WHERE tipo='basemark' and id_rutina ='$id_rutina' and elemento='$elemento' limit 1";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] .= 'Basemark actualizado con éxito. ';
	}else{
		$_SESSION['estado'] .= 'Error, el Basemark no se ha actualizado <br>'.mysqli_error($connection);
	}

	//actualizar TIPO DE HIBRIDO (DD) - Por ID con fallback a código
	if($id_tipo_hibrido == 1)
		$tabla = 'dificultad_hibridos';
	else if($id_tipo_hibrido == 2)
		$tabla = 'dificultad_tre';
	else if($id_tipo_hibrido == 4)
		$tabla = 'dificultad_acrobacias';
    else
        $tabla = 'dificultad_hibridos';

	if ($id_tipo_hibrido != 3) {
		$ddArray = array($dd0, $dd1, $dd2, $dd3, $dd4, $dd5, $dd6, $dd7, $dd8, $dd9, $dd10, $dd11, $dd12, $dd13, $dd14);

    	for ($i = 0; $i < count($ddArray); $i++) {
        	$ddValue = trim($ddArray[$i] ?? '');
			$ddValue = str_replace("XX", "'", $ddValue);
            
            $texto_dd = '';
            $valor_dd = 'NULL';
            $id_dif_dd = 'NULL';

            if(!empty($ddValue)){
                if(is_numeric($ddValue)){
                    $dd_id_int = (int)$ddValue;
                    $q_item = mysqli_query($connection, "SELECT id, codigo, valor FROM $tabla WHERE id = '$dd_id_int'");
                    if($r_item = mysqli_fetch_assoc($q_item)){
                        $texto_dd = $r_item['codigo'];
                        $valor_dd = "'" . number_format((float)$r_item['valor'], 3, '.', '') . "'";
                        $id_dif_dd = (int)$r_item['id'];
                    }
                } else {
                    $dd_code_safe = mysqli_real_escape_string($connection, $ddValue);
                    $q_item = mysqli_query($connection, "SELECT id, codigo, valor FROM $tabla WHERE codigo = '$dd_code_safe' LIMIT 1");
                    if($r_item = mysqli_fetch_assoc($q_item)){
                        $texto_dd = $r_item['codigo'];
                        $valor_dd = "'" . number_format((float)$r_item['valor'], 3, '.', '') . "'";
                        $id_dif_dd = (int)$r_item['id'];
                    }
                }
            }

            // Localizar el registro exacto del slot en hibridos_rutina
            $query_row = "SELECT id FROM hibridos_rutina WHERE tipo='dd' AND id_rutina ='$id_rutina' AND elemento='$elemento' ORDER BY id ASC LIMIT $i, 1";
            $res_row = mysqli_query($connection, $query_row);
            if($res_row && $row_target = mysqli_fetch_assoc($res_row)){
                $target_id = (int)$row_target['id'];
                $q_upd_dd = "UPDATE hibridos_rutina SET id_dificultad = $id_dif_dd, texto = '$texto_dd', valor = $valor_dd WHERE id = '$target_id'";
                mysqli_query($connection, $q_upd_dd);
            }
        }
		if(mysqli_error($connection) == ''){
			$_SESSION['correcto'] .= 'DD actualizado con éxito. ';
		}else{
			$_SESSION['estado'] .= 'Error, el DD no se ha actualizado <br>'.mysqli_error($connection);
		}
	}

        //actualizar TIPO DE HIBRIDO (BONUS)
			$query = "SELECT codigo, valor from dificultad_hibridos WHERE id = '$bonus0'";
			$query_run2 = mysqli_query($connection,$query);
			$valor = mysqli_fetch_assoc($query_run2);
			$texto = @$valor['codigo'];
			$valor = @$valor['valor'];
			if($bonus0 == '')
				$valor = " NULL ";
			$query = "UPDATE hibridos_rutina SET texto = '$bonus0', valor = $valor WHERE tipo='bonus' and id_rutina ='$id_rutina' and elemento='$elemento' limit 1";
			$query = "UPDATE hibridos_rutina SET id_dificultad = '$bonus0', texto = '$texto', valor = '$valor' WHERE tipo='bonus' and id_rutina ='$id_rutina' and elemento='$elemento' limit 1";
//	echo $query;
			$query_run = @mysqli_query($connection,$query);
//			$query = "SELECT codigo, valor from dificultad_hibridos WHERE id = '$bonus1'";
//			$query_run2 = mysqli_query($connection,$query);
//			$valor = mysqli_fetch_assoc($query_run2);
//			$texto = @$valor['codigo'];
//			$valor = @$valor['valor'];
//			if($bonus1 == '')
//				$valor = " NULL ";
//			$query = "SELECT id from hibridos_rutina WHERE tipo='bonus' and id_rutina ='$id_rutina' and elemento='$elemento' limit 1,1";
//			$query_run2 = mysqli_query($connection,$query);
//			$id = mysqli_fetch_assoc($query_run2);
//			$id = $id['id'];
//			$query = "UPDATE hibridos_rutina SET id_dificultad = '$bonus1', texto = '$texto', valor = '$valor' WHERE tipo='bonus' and id_rutina ='$id_rutina' and elemento='$elemento' limit 1";
//			$query_run = @mysqli_query($connection,$query);
//
//			$query = "SELECT codigo, valor from dificultad_hibridos WHERE id = '$bonus2'";
//			$query_run2 = mysqli_query($connection,$query);
//			$valor = mysqli_fetch_assoc($query_run2);
//			$texto = @$valor['codigo'];
//			$valor = @$valor['valor'];
//			if($bonus2 == '')
//				$valor = " NULL ";
//			$query = "SELECT id from hibridos_rutina WHERE tipo='bonus' and id_rutina ='$id_rutina' and elemento='$elemento' limit 2,1";
//			$query_run2 = mysqli_query($connection,$query);
//			$id = mysqli_fetch_assoc($query_run2);
//			$id = $id['id'];
//			$query = "UPDATE hibridos_rutina SET id_dificultad = '$bonus2', texto = '$texto', valor = '$valor' WHERE tipo='bonus' and id_rutina ='$id_rutina' and elemento='$elemento' limit 1";
//			$query_run = @mysqli_query($connection,$query);
//
//			$query = "SELECT codigo, valor from dificultad_hibridos WHERE id = '$bonus3'";
//			$query_run2 = mysqli_query($connection,$query);
//			$valor = mysqli_fetch_assoc($query_run2);
//			$texto = @$valor['codigo'];
//			$valor = @$valor['valor'];
//			if($bonus3 == '')
//				$valor = " NULL ";
//			$query = "SELECT id from hibridos_rutina WHERE tipo='bonus' and id_rutina ='$id_rutina' and elemento='$elemento' limit 3,1";
//			$query_run2 = mysqli_query($connection,$query);
//			$id = mysqli_fetch_assoc($query_run2);
//			$id = $id['id'];
//			$query = "UPDATE hibridos_rutina SET id_dificultad = '$bonus3', texto = '$texto', valor = '$valor' WHERE tipo='bonus' and id_rutina ='$id_rutina' and elemento='$elemento' limit 1";
//			$query_run = @mysqli_query($connection,$query);
//		if(mysqli_error($connection) == ''){
//			$_SESSION['correcto'] .= 'Bonus actualizado con éxito. ';
//		}else{
//			$_SESSION['estado'] .= 'Error, el Bonus no se ha actualizado <br>'.mysqli_error($connection);
//		}
        //actualizar TIPO DE HIBRIDO (TOTAL)
        $query = "SELECT sum(valor) as total from hibridos_rutina WHERE tipo!='total' and id_rutina ='$id_rutina' and elemento='$elemento'";
        $query_run = mysqli_query($connection,$query);
        $valor = mysqli_fetch_assoc($query_run);
		if($valor['total'] == '')
			$valor['total'] = 0;
        $valor = $valor['total'];
        $query = "UPDATE hibridos_rutina SET valor = '$valor' WHERE tipo='total' and id_rutina ='$id_rutina' and elemento='$elemento'";
		$query_run = @mysqli_query($connection,$query);
		if(mysqli_error($connection) == ''){
			$_SESSION['correcto'] .= 'Total actualizado con éxito. ';
		}else{
			$_SESSION['estado'] .= 'Error, el Total no se ha actualizado <br>'.mysqli_error($connection);
		}
		//actualizar dd_total de la rutina
        $query = "UPDATE rutinas SET dd_total = (SELECT sum(valor) FROM hibridos_rutina WHERE id_rutina = '$id_rutina' and tipo = 'total') WHERE id='$id_rutina'";
        $query_run = mysqli_query($connection,$query);
		$query_run = @mysqli_query($connection,$query);
		if(mysqli_error($connection) == ''){
			$_SESSION['correcto'] .= 'DD rutina actualizado con éxito. ';
		}else{
			$_SESSION['estado'] .= 'Error, el DD de la rutina no se ha actualizado <br>'.mysqli_error($connection);
		}
}


//Añadir transición
if(isset($_POST['save_btn'])){
	$elemento = $_POST['elemento_transicion'];
	$id_tipo_hibrido = 3;

	$query="INSERT INTO hibridos_rutina (id_rutina, elemento, tipo, texto, id_dificultad) VALUES ('$id_rutina','$elemento', 'part', '$id_tipo_hibrido', '$id_tipo_hibrido')";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Transición añadida con éxito';
	}else{
		$_SESSION['estado'] = 'Error, Transición no añadida <br>'.mysqli_error($connection);
	}
}

//Borrar transicion
if(isset($_POST['dlt_btn_transicion'])){
	//$id_rutina = $_POST['id_rutina'];
	$elemento = $_POST['elemento'];

	$query = "DELETE FROM hibridos_rutina WHERE id_rutina ='$id_rutina' and elemento ='$elemento' and texto='3' and tipo='part'";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Transición eliminada con éxito';
	}else{
		$_SESSION['estado'] = 'Error. La Transición no se ha eliminado <br>'.mysqli_error($connection);
	}
}
//Añadir transiciones mágicas
//if(isset($_POST['magic_transitions'])){
//	$elemento = $_POST['elemento_transicion'];
//	$id_tipo_hibrido = 3;
//
//	$query="INSERT INTO hibridos_rutina (id_rutina, elemento, tipo, texto, id_dificultad) VALUES ('$id_rutina','$elemento', 'part', '$id_tipo_hibrido', '$id_tipo_hibrido')";
//	//$query_run = mysqli_query($connection,$query);
//	if(mysqli_error($connection) == ''){
//		$_SESSION['correcto'] = 'Transiciones añadidas con éxito';
//	}else{
//		$_SESSION['estado'] = 'Error, Transiciones no añadidas, ha fallado la magia <br>'.mysqli_error($connection);
//	}
//}
	header('Location: coach_card_composer.php?id_rutina='.$id_rutina.'&id_fase='.$id_fase);

?>
