<?php
$query_fase_comp = "SELECT fases.id as id, modalidades.nombre as modalidad, categorias.nombre as categoria FROM fases, categorias, modalidades WHERE fases.id_modalidad = modalidades.id and fases.id_categoria = categorias.id and fases.id IN (SELECT id FROM fases WHERE id_competicion = ".$_SESSION['id_competicion_activa'].")";
$query_run_fase_comp = mysqli_query($connection, $query_fase_comp);
$select = "<label for='fases'>Fases</label>";
$select .= "<select name='fase' id='fase' class='form-control'>";
$select .= "<option value='0'>Todas las fases</option>";
if(mysqli_num_rows($query_run_fase_comp) > 0){
	while ($row_fase_comp = mysqli_fetch_assoc($query_run_fase_comp)) {
		if(intval(@$_POST['id_fase']) == $row_fase_comp['id']){
			$select .= "<option selected value=".$row_fase_comp['id'].">".$row_fase_comp['modalidad']." ".$row_fase_comp['categoria']."</option>";
		}
		else{
            $select .= "<option value=".$row_fase_comp['id'].">".$row_fase_comp['categoria']." ".$row_fase_comp['modalidad']."</option>";
		}
	}
}
$select .= "</select>";
echo $select;
?>
