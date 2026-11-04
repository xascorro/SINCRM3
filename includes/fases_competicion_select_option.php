<?php
$comp_id_target = isset($id_comp) ? intval($id_comp) : (isset($id_competicion) ? intval($id_competicion) : intval($_SESSION['id_competicion_activa'] ?? 0));
$query_fase_comp = "SELECT fases.id as id, fases.genero as genero, modalidades.nombre as modalidad, categorias.nombre as categoria 
                     FROM fases, categorias, modalidades 
                     WHERE fases.id_modalidad = modalidades.id 
                       AND fases.id_categoria = categorias.id 
                       AND fases.id_modalidad != 8 
                       AND (fases.id_figura IS NULL OR fases.id_figura = 0)
                       AND fases.id_competicion = $comp_id_target
                     ORDER BY fases.orden ASC";
$query_run_fase_comp = mysqli_query($connection, $query_fase_comp);
$select = "<label for='id_fase'>Fases</label>";
$select .= "<select name='id_fase' id='id_fase' class='form-control'>";
if (!empty($include_todas_fases)) {
    $select .= "<option value='0'>Todas las fases</option>";
} else {
    $select .= "<option value=''>--- Seleccionar Fase ---</option>";
}
if(mysqli_num_rows($query_run_fase_comp) > 0){
	while ($row_fase_comp = mysqli_fetch_assoc($query_run_fase_comp)) {
        $gen_lbl = (!empty($row_fase_comp['genero']) && $row_fase_comp['genero'] === 'masculino') ? ' Masc' : '';
        $opt_text = $row_fase_comp['modalidad'] . $gen_lbl . " " . $row_fase_comp['categoria'];
		if(intval(@$_POST['id_fase']) == $row_fase_comp['id'] || intval(@$id_fase) == $row_fase_comp['id']){
			$select .= "<option selected value=".$row_fase_comp['id'].">".$opt_text."</option>";
		}
		else{
            $select .= "<option value=".$row_fase_comp['id'].">".$opt_text."</option>";
		}
	}
}
$select .= "</select>";
echo $select;
?>
