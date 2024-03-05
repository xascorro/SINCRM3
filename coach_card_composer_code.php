<?php
include('security.php');

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
	$query = "UPDATE hibridos_rutina SET texto = '$id_tipo_hibrido' WHERE tipo='part' and id_rutina ='$id_rutina' and elemento='$elemento' and texto not like '3'";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] .= 'PART actualizada con éxito. ';
	}else{
		$_SESSION['estado'] .= 'Error, la PART no se ha actualizado <br>'.mysqli_error($connection);
	}
	//actualizar TIPO DE BASEMARK
	$query = "SELECT valor from dificultad_basemark WHERE codigo = '$Basemark0'";
	$query_run2 = mysqli_query($connection,$query);
	$valor = mysqli_fetch_assoc($query_run2);
	$valor = @$valor['valor'];
	if($valor != '')
			$valor = ", valor = '$valor' ";
	if($Basemark0 == '')
		$valor = ", valor = NULL ";
	$query = "UPDATE hibridos_rutina SET texto = '$Basemark0' $valor WHERE tipo='basemark' and id_rutina ='$id_rutina' and elemento='$elemento' limit 1";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] .= 'Basemark actualizado con éxito. ';
	}else{
		$_SESSION['estado'] .= 'Error, el Basemark no se ha actualizado <br>'.mysqli_error($connection);
	}
	//actualizar TIPO DE HIBRIDO (DD)
	if($id_tipo_hibrido == 1)
		$tabla = ' dificultad_hibridos ';
	else if($id_tipo_hibrido == 2)
		$tabla = ' dificultad_tre ';
	else if($id_tipo_hibrido == 4)
		$tabla = ' dificultad_acrobacias ';
	if ($id_tipo_hibrido != 3) {
    	$ddArray = array($dd0, $dd1, $dd2, $dd3, $dd4, $dd5, $dd6, $dd7, $dd8, $dd9);
    	for ($i = 0; $i < count($ddArray); $i++) {
        	$ddValue = $ddArray[$i];
//        	if ($ddValue != '') {
				$query = "SELECT valor from $tabla WHERE codigo = '$ddValue'";
				$query_run2 = mysqli_query($connection,$query);
				$valor = mysqli_fetch_assoc($query_run2);
				$valor = @$valor['valor'];
				if($valor == '')
					$valor = 'NULL';
				$query = "SELECT id from hibridos_rutina WHERE tipo='dd' and id_rutina ='$id_rutina' and elemento='$elemento' limit $i,1";
				$query_run2 = mysqli_query($connection,$query);
				$id = mysqli_fetch_assoc($query_run2);
				$id = @$id['id'];
				$query = "UPDATE hibridos_rutina SET texto = '$ddValue', valor=$valor WHERE id='$id'";
				$query_run = mysqli_query($connection,$query);
//           }
		}
		if(mysqli_error($connection) == ''){
			$_SESSION['correcto'] .= 'DD actualizado con éxito. ';
		}else{
			$_SESSION['estado'] .= 'Error, el DD no se ha actualizado <br>'.mysqli_error($connection);
		}
	}

        //actualizar TIPO DE HIBRIDO (BONUS)
			$query = "SELECT valor from dificultad_hibridos WHERE codigo = '$bonus0'";
			$query_run2 = mysqli_query($connection,$query);
			$valor = mysqli_fetch_assoc($query_run2);
			$valor = @$valor['valor'];
			if($bonus0 == '')
				$valor = " NULL ";
			$query = "UPDATE hibridos_rutina SET texto = '$bonus0', valor = $valor WHERE tipo='bonus' and id_rutina ='$id_rutina' and elemento='$elemento' limit 1";
							echo '<br>'.$query;

			$query_run = @mysqli_query($connection,$query);
			$query = "SELECT valor from dificultad_hibridos WHERE codigo = '$bonus1'";
			$query_run2 = mysqli_query($connection,$query);
			$valor = mysqli_fetch_assoc($query_run2);
			$valor = @$valor['valor'];
			if($bonus1 == '')
				$valor = " NULL ";
			$query = "SELECT id from hibridos_rutina WHERE tipo='bonus' and id_rutina ='$id_rutina' and elemento='$elemento' limit 1,1";
			$query_run2 = mysqli_query($connection,$query);
			$id = mysqli_fetch_assoc($query_run2);
			$id = $id['id'];
			$query = "UPDATE hibridos_rutina SET texto = '$bonus1', valor = $valor WHERE id='$id'";
			$query_run = @mysqli_query($connection,$query);
		if(mysqli_error($connection) == ''){
			$_SESSION['correcto'] .= 'Bonus actualizado con éxito. ';
		}else{
			$_SESSION['estado'] .= 'Error, el Bonus no se ha actualizado <br>'.mysqli_error($connection);
		}
        //actualizar TIPO DE HIBRIDO (TOTAL)
        $query = "SELECT sum(valor) as total from hibridos_rutina WHERE tipo!='total' and id_rutina ='$id_rutina' and elemento='$elemento' and texto not like 'ACROPAIR'";
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
//		header('Location: coach_card_composer.php');
}


//Añadir transición
if(isset($_POST['save_btn'])){
	$elemento = $_POST['elemento_transicion'];
	$id_tipo_hibrido = 3;

	$query="INSERT INTO hibridos_rutina (id_rutina, elemento, tipo, texto) VALUES ('$id_rutina','$elemento', 'part', '$id_tipo_hibrido')";
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
//	header('Location: coach_card_composer.php?id_rutina='.$id_rutina.'&id_fase='.$id_fase);

}
	header('Location: coach_card_composer.php?id_rutina='.$id_rutina.'&id_fase='.$id_fase);

?>
