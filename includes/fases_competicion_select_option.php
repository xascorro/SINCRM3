<?php
$query = "SELECT fases.id as id, modalidades.nombre as modalidad, categorias.nombre as categoria FROM fases, categorias, modalidades WHERE fases.id_modalidad = modalidades.id and fases.id_categoria = categorias.id and fases.id IN (SELECT id FROM fases WHERE id_competicion = ".$_SESSION['id_competicion_activa'].")";
$query_run = mysqli_query($connection,$query);
$select = "<label for='fases'>Fases</label>";
$select .= "<select name='fase' id='fase' class='form-control'>";
$select .= "<option value='0'>Todas las fases</option>";
if(mysqli_num_rows($query_run) > 0){
	while ($row = mysqli_fetch_assoc($query_run)) {
		if(intval(@$_POST['id_fase']) == $row['id']){
			$select .= "<option selected value=".$row['id'].">".$row['modaliad']." ".$row['categoria']."</option>";
		}
		else{
$select .= "<option value=".$row['id'].">".$row['categoria']." ".$row['modalidad']."</option>";
		}
	}
}
$select .= "</select>";
echo $select;
?>
