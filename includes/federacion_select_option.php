<?php  
$query_fed = "SELECT id, nombre, nombre_corto FROM federaciones";
$query_run_fed = mysqli_query($connection, $query_fed); 
$select = "<label for='federacion'>Federación</label>";
$select .= "<select name='federacion' id='federacion' class='form-control'>";
if(mysqli_num_rows($query_run_fed) > 0){
	while ($row_fed = mysqli_fetch_assoc($query_run_fed)) {	    
        $current_fed = $id_fed_actual ?? @$_POST['id_federacion'] ?? 0;
		if(intval($current_fed) == $row_fed['id']){
			$select .= "<option selected value=".$row_fed['id'].">".$row_fed['nombre']."</option>";
		}
		else{
			$select .= "<option value=".$row_fed['id'].">".$row_fed['nombre']."</option>";
		}

	}
}
$select .= "</select>"; 
echo $select;  
?>
