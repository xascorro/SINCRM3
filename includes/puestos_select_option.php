<?php
$query = "SELECT id, nombre FROM puestos_juez";
$query_run = mysqli_query($connection,$query);
$select = "<label for='puestos_juez'>Puesto juez</label>";
$select .= "<select name='id_puestos_juez' id='id_puestos_juez' class='form-control'>";
if(mysqli_num_rows($query_run) > 0){
	while ($row = mysqli_fetch_assoc($query_run)) {
		if(intval(@$_POST['id_puestos_juez']) == $row['id']){
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
