<?php  
$query_fig = "SELECT id, numero, nombre FROM figuras";
$query_run_fig = mysqli_query($connection, $query_fig); 
$select = "<label for='figura'>Figura</label>";
$select .= "<select name='figura' id='figura' class='form-control'>";
if(mysqli_num_rows($query_run_fig) > 0){
	while ($row_fig = mysqli_fetch_assoc($query_run_fig)) {	    
        $current_val = $id_figura_actual ?? @$_POST['id_figura'] ?? 0;
		if(intval($current_val) == $row_fig['id']){
			$select .= "<option selected value=".$row_fig['id'].">".$row_fig['numero'].' - '.$row_fig['nombre']."</option>";
		}
		else{
			$select .= "<option value=".$row_fig['id'].">".$row_fig['numero'].' - '.$row_fig['nombre']."</option>";
		}

	}
}
$select .= "</select>"; 
echo $select;  
?>
