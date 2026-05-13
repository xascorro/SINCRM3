<?php  
$query = "SELECT id, nombre, edad_minima, edad_maxima FROM categorias";
$query_run = mysqli_query($connection,$query); 
$select = "<label for='categorias'>Categoría</label>";
$select .= "<select name='categoria' id='categoria' class='form-control'>";
if(mysqli_num_rows($query_run) > 0){
	while ($row = mysqli_fetch_assoc($query_run)) {	    
        $current_val = $id_categoria_actual ?? @$_POST['id_categoria'] ?? 0;
		if(intval($current_val) == $row['id']){
			$select .= "<option selected value=".$row['id'].">".$row['nombre']." (".$row['edad_minima']."-".$row['edad_maxima'].")</option>";
		}
		else{
			$select .= "<option value=".$row['id'].">".$row['nombre']." (".$row['edad_minima']."-".$row['edad_maxima'].")</option>";
		}

	}
}
$select .= "</select>"; 
echo $select;  
?>
