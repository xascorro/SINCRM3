<?php
//funciona bien x 4
//if($_SESSION['competicion_figuras'] == 'si'){
if($figuras == 'si'){
	$query = "SELECT fases.id, id_categoria, modalidades.nombre as nombre_modalidad, categorias.nombre as nombre_categoria FROM fases, modalidades, categorias where fases.id_modalidad = modalidades.id and fases.id_categoria = categorias.id and fases.id_competicion = ".$id_competicion." GROUP BY id_categoria";
}else{
	$query = "SELECT fases.id, id_categoria, modalidades.nombre as nombre_modalidad, categorias.nombre as nombre_categoria FROM fases, modalidades, categorias where fases.id_modalidad = modalidades.id and fases.id_categoria = categorias.id and fases.id_competicion = ".$id_competicion;
}
$query_run = mysqli_query($connection,$query);
$select = "<label for='id_fase'>Fase</label>";
$select .= "<select name='id_fase' id='id_fase' class='form-control'>";
if(mysqli_num_rows($query_run) > 0){
	while ($row = mysqli_fetch_assoc($query_run)) {
		if(intval(@$id_fase) == $row['id']){
			$select .= "<option selected value=".$row['id'].">".$row['nombre_modalidad']." ".$row['nombre_categoria']."</option>";
		}
		else{
			$select .= "<option value=".$row['id'].">".$row['nombre_modalidad']." ".$row['nombre_categoria']."</option>";
		}

	}
}
$select .= "</select>";
echo $select;
?>
