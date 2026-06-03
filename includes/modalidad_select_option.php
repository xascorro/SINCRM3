<?php
$query_mod = "SELECT id, nombre, numero_participantes, numero_reservas FROM modalidades";
$query_run_mod = mysqli_query($connection, $query_mod);
$select = "<label for='modalidades'>Modalidad</label>";
$select .= "<select name='modalidad' id='modalidad' class='form-control'>";
if(mysqli_num_rows($query_run_mod) > 0){
	while ($row_mod = mysqli_fetch_assoc($query_run_mod)) {
        $current_val = $id_modalidad_actual ?? @$_POST['id_modalidad'] ?? 0;
		if(intval($current_val) == $row_mod['id']){
			$select .= "<option selected value=".$row_mod['id'].">".$row_mod['nombre']." (".$row_mod['numero_participantes']."P - ".$row_mod['numero_reservas']."R)</option>";
		}
		else{
			$select .= "<option value=".$row_mod['id'].">".$row_mod['nombre']." (".$row_mod['numero_participantes']."P -".$row_mod['numero_reservas']."R)</option>";
		}

	}
}
$select .= "</select>";
echo $select;
?>
