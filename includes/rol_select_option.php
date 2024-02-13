<?php
$query = "SELECT id, nombre FROM roles order by level";
$query_run_select = mysqli_query($connection,$query);
$select = "<label for='rol'>Tipo de Usuario</label>";
$select .= "<select name='edit_rol' id='rol' class='form-control'>";
if(mysqli_num_rows($query_run_select) > 0){
	while ($row_select = mysqli_fetch_assoc($query_run_select)) {
		if(intval($row['id_rol']) == $row_select['id']){
			$select .= "<option selected value=".$row_select['id'].">".$row_select['nombre']."</option>";
		}
		else{
			$select .= "<option value=".$row_select['id'].">".$row_select['nombre']."</option>";
		}

	}
}
$select .= "</select>";
echo $select;
?>
