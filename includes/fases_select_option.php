<?php
//funciona bien x 4
//if($_SESSION['competicion_figuras'] == 'si'){
if($figuras == 'si'){
	$query = "SELECT fs.id, fs.id_categoria, m.nombre as nombre_modalidad, c.nombre as nombre_categoria 
              FROM fases fs 
              JOIN modalidades m ON fs.id_modalidad = m.id 
              JOIN categorias c ON fs.id_categoria = c.id 
              WHERE fs.id_competicion = $id_competicion 
              GROUP BY fs.id_categoria 
              ORDER BY c.orden ASC";
}else{
	$query = "SELECT fs.id, fs.id_categoria, m.nombre as nombre_modalidad, c.nombre as nombre_categoria 
              FROM fases fs 
              JOIN modalidades m ON fs.id_modalidad = m.id 
              JOIN categorias c ON fs.id_categoria = c.id 
              WHERE fs.id_competicion = $id_competicion";
}
$query_run = mysqli_query($connection,$query);
$select = "<label for='id_fase'>Fase</label>";
$select .= "<select name='id_fase' id='id_fase' class='form-control'>";
if(mysqli_num_rows($query_run) > 0){
	while ($row = mysqli_fetch_assoc($query_run)) {
		if(intval(@$id_fase) == $row['id']){
			$select .= "<option selected value=".$row['id']." data-categoria='".$row['id_categoria']."'>".$row['nombre_modalidad']." ".$row['nombre_categoria']."</option>";
		}
		else{
			$select .= "<option value=".$row['id']." data-categoria='".$row['id_categoria']."'>".$row['nombre_modalidad']." ".$row['nombre_categoria']."</option>";
		}

	}
}
$select .= "</select>";
echo $select;
?>
