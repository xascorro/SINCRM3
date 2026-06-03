<?php
$query = "SELECT id, nombre FROM paneles_tipo";
$query_run = mysqli_query($connection,$query);
$select = "<label for='id_paneles_tipo'>Tipo de panel</label>";
$select .= "<select name='id_paneles_tipo' id='id_paneles_tipo' class='form-control'>";
if(mysqli_num_rows($query_run) > 0){
	while ($row_panel = mysqli_fetch_assoc($query_run)) {
		if(intval(@$_POST['id_paneles_tipo']) == $row_panel['id']){
			$select .= "<option selected value=".$row_panel['id'].">".$row_panel['nombre']."</option>";
		}
		else{
			$select .= "<option value=".$row_panel['id'].">".$row_panel['nombre']."</option>";
		}

	}
}
$select .= "</select>";
echo $select;
?>
