<?php
$query_puesto = "SELECT id, nombre FROM puestos_juez";
$query_run_puesto = mysqli_query($connection, $query_puesto);
$select = "<label for='puestos_juez'>Puesto juez</label>";
$select .= "<select name='id_puestos_juez' id='id_puestos_juez' class='form-control'>";
if(mysqli_num_rows($query_run_puesto) > 0){
	while ($row_puesto = mysqli_fetch_assoc($query_run_puesto)) {
		if(intval(@$_POST['id_puestos_juez']) == $row_puesto['id']){
			$select .= "<option selected value=".$row_puesto['id'].">".$row_puesto['nombre']."</option>";
		}
		else{
			$select .= "<option value=".$row_puesto['id'].">".$row_puesto['nombre']."</option>";
		}

	}
}
$select .= "</select>";
echo $select;
?>
