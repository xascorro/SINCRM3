<?php
include('security.php');
$id_rutina = $_POST['id_rutina'];
$id_fase = $_POST['id_fase'];
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
	header('Location: coach_card_composer.php?id_rutina='.$id_rutina.'&id_fase='.$id_fase);
}

//Actualizar registro
if(isset($_POST['update_btn'])){
    //$id_rutina = $_POST['id_rutina'];
    //$id_elemento = $_POST['edit_id_elemento'];
	$elemento = $_POST['elemento'];
    $time_inicio = $_POST['time_inicio'];
    $time_fin = $_POST['time_fin'];
    $id_tipo_hibrido = $_POST['id_tipo_hibrido'];
    $basemark0 = $_POST['Basemark0'];
    $basemark1 = $_POST['Basemark1'];
    $dd0= $_POST['dd0'];
    $dd1= $_POST['dd1'];
    $dd2= $_POST['dd2'];
    $dd3= $_POST['dd3'];
    $dd4= $_POST['dd4'];
    $dd5= $_POST['dd5'];
    $bonus0= $_POST['bonus0'];
    $bonus1= $_POST['bonus1'];

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
        $query = "UPDATE hibridos_rutina SET texto = '$id_tipo_hibrido' WHERE tipo='part' and id_rutina ='$id_rutina' and elemento='$elemento'";
		$query_run = mysqli_query($connection,$query);
		if(mysqli_error($connection) == ''){
			$_SESSION['correcto'] .= 'PART actualizada con éxito. ';
			//header('Location: coach_card_composer.php?id_rutina='.$id_rutina);
		}else{
			$_SESSION['estado'] .= 'Error, la PART no se ha actualizado <br>'.mysqli_error($connection);
			//header('Location: coach_card_composer.php?id_rutina='.$id_rutina);
		}
        //actualizar TIPO DE HIBRIDO (BASEMARK)
        $query = "SELECT valor from dificultad_hibridos WHERE codigo = '$basemark0'";
        $query_run2 = mysqli_query($connection,$query);
        $valor = mysqli_fetch_assoc($query_run2);
        $valor = @$valor['valor'];
        if($valor != '')
                $valor = ", valor = '$valor' ";
        $query = "UPDATE hibridos_rutina SET texto = '$basemark0' $valor WHERE tipo='basemark' and id_rutina ='$id_rutina' and elemento='$elemento' limit 1";
		$query_run = mysqli_query($connection,$query);
        $query = "SELECT valor from dificultad_hibridos WHERE codigo = '$basemark1'";
        $query_run2 = mysqli_query($connection,$query);
        $valor = mysqli_fetch_assoc($query_run2);
        $valor = @$valor['valor'];
        if($valor != '')
                $valor = ", valor = '$valor' ";
        $query = "SELECT id from hibridos_rutina WHERE tipo='basemark' and id_rutina ='$id_rutina' and elemento='$elemento' limit 1,1";
        $query_run2 = mysqli_query($connection,$query);
        $id = mysqli_fetch_assoc($query_run2);
        $id = $id['id'];
        $query = "UPDATE hibridos_rutina SET texto = '$basemark1' $valor WHERE id='$id'";
		$query_run = mysqli_query($connection,$query);
		if(mysqli_error($connection) == ''){
			$_SESSION['correcto'] .= 'Basemark actualizado con éxito. ';
			//header('Location: coach_card_composer.php?id_rutina='.$id_rutina);
		}else{
			$_SESSION['estado'] .= 'Error, el Basemark no se ha actualizado <br>'.mysqli_error($connection);
			//header('Location: coach_card_composer.php?id_rutina='.$id_rutina);
		}
        //actualizar TIPO DE HIBRIDO (DD)
        if($id_tipo_hibrido != 4){
			if($dd0 != ''){
				$query = "SELECT valor from dificultad_hibridos WHERE codigo = '$dd0'";
				$query_run2 = mysqli_query($connection,$query);
				$valor = mysqli_fetch_assoc($query_run2);
				$valor = $valor['valor'];
				$query = "UPDATE hibridos_rutina SET texto = '$dd0', valor = '$valor' WHERE tipo='dd' and id_rutina ='$id_rutina' and elemento='$elemento' limit 1";
				$query_run = mysqli_query($connection,$query);
			}
			if($dd1 != ''){
				$query = "SELECT valor from dificultad_hibridos WHERE codigo = '$dd1'";
				$query_run2 = mysqli_query($connection,$query);
				$valor = mysqli_fetch_assoc($query_run2);
				$valor = @$valor['valor'];
				$query = "SELECT id from hibridos_rutina WHERE tipo='dd' and id_rutina ='$id_rutina' and elemento='$elemento' limit 1,1";
				$query_run2 = mysqli_query($connection,$query);
				$id = mysqli_fetch_assoc($query_run2);
				$id = $id['id'];
				$query = "UPDATE hibridos_rutina SET texto = '$dd1', valor='$valor' WHERE id='$id'";
				$query_run = mysqli_query($connection,$query);
			}
			if($dd2 != ''){
				$query = "SELECT valor from dificultad_hibridos WHERE codigo = '$dd2'";
				$query_run2 = mysqli_query($connection,$query);
				$valor = mysqli_fetch_assoc($query_run2);
				$valor = @$valor['valor'];
				$query = "SELECT id from hibridos_rutina WHERE tipo='dd' and id_rutina ='$id_rutina' and elemento='$elemento' limit 2,1";
				$query_run2 = mysqli_query($connection,$query);
				$id = mysqli_fetch_assoc($query_run2);
				$id = $id['id'];
				$query = "UPDATE hibridos_rutina SET texto = '$dd2', valor='$valor' WHERE id='$id'";
				$query_run = mysqli_query($connection,$query);
			}
			if($dd3 != ''){
				$query = "SELECT valor from dificultad_hibridos WHERE codigo = '$dd3'";
				$query_run2 = mysqli_query($connection,$query);
				$valor = mysqli_fetch_assoc($query_run2);
				$valor = $valor['valor'];
				$query = "SELECT id from hibridos_rutina WHERE tipo='dd' and id_rutina ='$id_rutina' and elemento='$elemento' limit 3,1";
				$query_run2 = mysqli_query($connection,$query);
				$id = mysqli_fetch_assoc($query_run2);
				$id = $id['id'];
				$query = "UPDATE hibridos_rutina SET texto = '$dd3', valor='$valor' WHERE id='$id'";
				$query_run = mysqli_query($connection,$query);
			}
			if($dd4 != ''){
				$query = "SELECT valor from dificultad_hibridos WHERE codigo = '$dd4'";
				$query_run2 = mysqli_query($connection,$query);
				$valor = mysqli_fetch_assoc($query_run2);
				$valor = $valor['valor'];
				$query = "SELECT id from hibridos_rutina WHERE tipo='dd' and id_rutina ='$id_rutina' and elemento='$elemento' limit 4,1";
				$query_run2 = mysqli_query($connection,$query);
				$id = mysqli_fetch_assoc($query_run2);
				$id = $id['id'];
				$query = "UPDATE hibridos_rutina SET texto = '$dd4', valor='$valor' WHERE id='$id'";
				$query_run = mysqli_query($connection,$query);
			}
			if($dd5 != ''){
				$query = "SELECT valor from dificultad_hibridos WHERE codigo = '$dd5'";
				$query_run2 = mysqli_query($connection,$query);
				$valor = mysqli_fetch_assoc($query_run2);
				$valor = $valor['valor'];
				$query = "SELECT id from hibridos_rutina WHERE tipo='dd' and id_rutina ='$id_rutina' and elemento='$elemento' limit 5,1";
				$query_run2 = mysqli_query($connection,$query);
				$id = mysqli_fetch_assoc($query_run2);
				$id = $id['id'];
				$query = "UPDATE hibridos_rutina SET texto = '$dd5', valor='$valor' WHERE id='$id'";
				$query_run = mysqli_query($connection,$query);
			}
        }else{
            $query = "SELECT valor from dificultad_acropair WHERE codigo = '$dd0'";
            $query_run2 = mysqli_query($connection,$query);
            $valor = mysqli_fetch_assoc($query_run2);
            $valor = $valor['valor'];
            $query = "UPDATE hibridos_rutina SET texto = '$dd0', valor = '$valor' WHERE tipo='dd' and id_rutina ='$id_rutina' and elemento='$elemento' limit 1";
            $query_run = mysqli_query($connection,$query);
            $query = "SELECT valor from dificultad_acropair WHERE codigo = '$dd1'";
            $query_run2 = mysqli_query($connection,$query);
            $valor = mysqli_fetch_assoc($query_run2);
            $valor = $valor['valor'];
            $query = "SELECT id from hibridos_rutina WHERE tipo='dd' and id_rutina ='$id_rutina' and elemento='$elemento' limit 1,1";
            $query_run2 = mysqli_query($connection,$query);
        }
        if(mysqli_error($connection) == ''){
			$_SESSION['correcto'] .= 'DD actualizado con éxito. ';
			//header('Location: coach_card_composer.php?id_rutina='.$id_rutina);
		}else{
			$_SESSION['estado'] .= 'Error, el DD no se ha actualizado <br>'.mysqli_error($connection);
			//header('Location: coach_card_composer.php?id_rutina='.$id_rutina);
		}
        //actualizar TIPO DE HIBRIDO (BONUS)
        if($bonus0 != ''){
			$query = "SELECT valor from dificultad_hibridos WHERE codigo = '$bonus0'";
			$query_run2 = mysqli_query($connection,$query);
			$valor = mysqli_fetch_assoc($query_run2);
			$valor = @$valor['valor'];
			$query = "UPDATE hibridos_rutina SET texto = '$bonus0', valor = '$valor' WHERE tipo='bonus' and id_rutina ='$id_rutina' and elemento='$elemento' limit 1";
			$query_run = mysqli_query($connection,$query);
		}
		if($bonus1 != ''){
			$query = "SELECT valor from dificultad_hibridos WHERE codigo = '$bonus1'";
			$query_run2 = mysqli_query($connection,$query);
			$valor = mysqli_fetch_assoc($query_run2);
			$valor = @$valor['valor'];
			$query = "SELECT id from hibridos_rutina WHERE tipo='bonus' and id_rutina ='$id_rutina' and elemento='$elemento' limit 1,1";
			$query_run2 = mysqli_query($connection,$query);
			$id = mysqli_fetch_assoc($query_run2);
			$id = $id['id'];
			$query = "UPDATE hibridos_rutina SET texto = '$bonus1', valor='$valor' WHERE id='$id'";
			$query_run = mysqli_query($connection,$query);
		}
		if(mysqli_error($connection) == ''){
			$_SESSION['correcto'] .= 'Bonus actualizado con éxito. ';
			//header('Location: coach_card_composer.php?id_rutina='.$id_rutina);
		}else{
			$_SESSION['estado'] .= 'Error, el Bonus no se ha actualizado <br>'.mysqli_error($connection);
			//header('Location: coach_card_composer.php?id_rutina='.$id_rutina);
		}
        //actualizar TIPO DE HIBRIDO (TOTAL)
        $query = "SELECT sum(valor) as total from hibridos_rutina WHERE tipo!='total' and id_rutina ='$id_rutina' and elemento='$elemento' and texto not like 'ACROPAIR'";
        $query_run = mysqli_query($connection,$query);
        $valor = mysqli_fetch_assoc($query_run);
        $valor = @$valor['total'];
        $query = "UPDATE hibridos_rutina SET valor = '$valor' WHERE tipo='total' and id_rutina ='$id_rutina' and elemento='$elemento'";
		$query_run = mysqli_query($connection,$query);
		if(mysqli_error($connection) == ''){
			$_SESSION['correcto'] .= 'Total actualizado con éxito. ';
		}else{
			$_SESSION['estado'] .= 'Error, el Total no se ha actualizado <br>'.mysqli_error($connection);
		}
		header('Location: coach_card_composer.php?id_rutina='.$id_rutina.'&id_fase='.$id_fase);
}

//Borrar registro
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
	header('Location: coach_card_composer.php?id_rutina='.$id_rutina.'&id_fase='.$id_fase);

}
?>
