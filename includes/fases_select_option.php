<?php
//funciona bien x 4
//if($_SESSION['competicion_figuras'] == 'si'){
if($figuras == 'si'){
	$query_fase = "SELECT fs.id, fs.id_categoria, fs.genero, m.nombre as nombre_modalidad, c.nombre as nombre_categoria 
              FROM fases fs 
              JOIN modalidades m ON fs.id_modalidad = m.id 
              JOIN categorias c ON fs.id_categoria = c.id 
              WHERE fs.id_competicion = $id_competicion 
              GROUP BY fs.id_categoria 
              ORDER BY c.orden ASC";
} else {
	$query_fase = "SELECT fs.id, fs.id_categoria, fs.genero, m.nombre as nombre_modalidad, c.nombre as nombre_categoria 
              FROM fases fs 
              JOIN modalidades m ON fs.id_modalidad = m.id 
              JOIN categorias c ON fs.id_categoria = c.id 
              WHERE fs.id_competicion = $id_competicion 
                AND fs.id_modalidad != 8 
                AND (fs.id_figura IS NULL OR fs.id_figura = 0)
              ORDER BY fs.orden ASC, c.orden ASC";
}
$query_run_fase = mysqli_query($connection, $query_fase);
$select = "<label for='id_fase'>Fase</label>";
$select .= "<select name='id_fase' id='id_fase' class='form-control'>";
if(mysqli_num_rows($query_run_fase) > 0){
	while ($row_fase = mysqli_fetch_assoc($query_run_fase)) {
		$gen_lbl = (!empty($row_fase['genero']) && $row_fase['genero'] === 'masculino') ? ' Masc' : '';
		$opt_text = $row_fase['nombre_modalidad'].$gen_lbl." ".$row_fase['nombre_categoria'];
		if(intval(@$id_fase) == $row_fase['id']){
			$select .= "<option selected value=".$row_fase['id']." data-categoria='".$row_fase['id_categoria']."'>".$opt_text."</option>";
		}
		else{
			$select .= "<option value=".$row_fase['id']." data-categoria='".$row_fase['id_categoria']."'>".$opt_text."</option>";
		}

	}
}
$select .= "</select>";
echo $select;
?>
