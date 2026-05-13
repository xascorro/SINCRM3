<?php
//funciona bien x 4
//if($_SESSION['competicion_figuras'] == 'si'){
if($figuras == 'si'){
	$query_fase = "SELECT fs.id, fs.id_categoria, m.nombre as nombre_modalidad, c.nombre as nombre_categoria 
              FROM fases fs 
              JOIN modalidades m ON fs.id_modalidad = m.id 
              JOIN categorias c ON fs.id_categoria = c.id 
              WHERE fs.id_competicion = $id_competicion 
              GROUP BY fs.id_categoria 
              ORDER BY c.orden ASC";
}else{
	$query_fase = "SELECT fs.id, fs.id_categoria, m.nombre as nombre_modalidad, c.nombre as nombre_categoria 
              FROM fases fs 
              JOIN modalidades m ON fs.id_modalidad = m.id 
              JOIN categorias c ON fs.id_categoria = c.id 
              WHERE fs.id_competicion = $id_competicion";
}
$query_run_fase = mysqli_query($connection, $query_fase);
$select = "<label for='id_fase'>Fase</label>";
$select .= "<select name='id_fase' id='id_fase' class='form-control'>";
if(mysqli_num_rows($query_run_fase) > 0){
	while ($row_fase = mysqli_fetch_assoc($query_run_fase)) {
		if(intval(@$id_fase) == $row_fase['id']){
			$select .= "<option selected value=".$row_fase['id']." data-categoria='".$row_fase['id_categoria']."'>".$row_fase['nombre_modalidad']." ".$row_fase['nombre_categoria']."</option>";
		}
		else{
			$select .= "<option value=".$row_fase['id']." data-categoria='".$row_fase['id_categoria']."'>".$row_fase['nombre_modalidad']." ".$row_fase['nombre_categoria']."</option>";
		}

	}
}
$select .= "</select>";
echo $select;
?>
