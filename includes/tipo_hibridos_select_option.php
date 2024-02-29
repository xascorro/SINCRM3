<?php
$query = "SELECT id, nombre from tipo_hibridos WHERE id != 3";
$query_run = mysqli_query($connection,$query);
$select = "<label for='id_tipo_hibrido'>Tipo hibrido</label>";
$select .= "<select name='id_tipo_hibrido' id='id_tipo_hibrido' class='form-control'>";
$select .= "<option value='' selected> Selecciona valor </option>";

if(mysqli_num_rows($query_run) > 0){
	while ($row = mysqli_fetch_assoc($query_run)) {
		if(intval(@$_POST['id_tipo_hibrido']) == $row['id']){
			$select .= "<option selected value=".$row['id'].">".$row['nombre']."</option>";
		}
		else{
			$select .= "<option value=".$row['id'].">".$row['nombre']."</option>";
		}

	}
}
$select .= "</select>";
echo $select;
?>
