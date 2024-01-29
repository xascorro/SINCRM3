<?php
$query = "SELECT id, nombre, numero_participantes, numero_reservas FROM modalidades";
$query_run = mysqli_query($connection,$query);
$select = "<label for='modalidades'>Modalidad</label>";
$select .= "<select name='modalidad' id='modalidad' class='form-control'>";
if(mysqli_num_rows($query_run) > 0){
	while ($row = mysqli_fetch_assoc($query_run)) {
		if(intval($_POST['id_modalidad']) == $row['id']){
			$select .= "<option selected value=".$row['id'].">".$row['nombre']." (".$row['numero_participantes']."P - ".$row['numero_reservas']."R)</option>";
		}
		else{
			$select .= "<option value=".$row['id'].">".$row['nombre']." (".$row['numero_participantes']."P -".$row['numero_reservas']."R)</option>";
		}

	}
}
$select .= "</select>";
echo $select;
?>
