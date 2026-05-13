<?php
$query_tipo_h = "SELECT id, nombre from tipo_hibridos WHERE id != 3";
$query_run_tipo_h = mysqli_query($connection, $query_tipo_h);
$select = "<label for='id_tipo_hibrido'>Tipo hibrido</label>";
$select .= "<select name='id_tipo_hibrido' id='id_tipo_hibrido' class='form-control'>";
$select .= "<option value='' selected> Selecciona valor </option>";

if(mysqli_num_rows($query_run_tipo_h) > 0){
	while ($row_tipo_h = mysqli_fetch_assoc($query_run_tipo_h)) {
		if(intval(@$_POST['id_tipo_hibrido']) == $row_tipo_h['id']){
			$select .= "<option selected value=".$row_tipo_h['id'].">".$row_tipo_h['nombre']."</option>";
		}
		else{
			$select .= "<option value=".$row_tipo_h['id'].">".$row_tipo_h['nombre']."</option>";
		}

	}
}
$select .= "</select>";
echo $select;
?>
