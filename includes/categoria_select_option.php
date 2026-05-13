<?php  
$query_cat = "SELECT id, nombre, edad_minima, edad_maxima FROM categorias";
$query_run_cat = mysqli_query($connection, $query_cat); 
$select = "<label for='categorias'>Categoría</label>";
$select .= "<select name='categoria' id='categoria' class='form-control'>";
if(mysqli_num_rows($query_run_cat) > 0){
	while ($row_cat = mysqli_fetch_assoc($query_run_cat)) {	    
        $current_val = $id_categoria_actual ?? @$_POST['id_categoria'] ?? 0;
		if(intval($current_val) == $row_cat['id']){
			$select .= "<option selected value=".$row_cat['id'].">".$row_cat['nombre']." (".$row_cat['edad_minima']."-".$row_cat['edad_maxima'].")</option>";
		}
		else{
			$select .= "<option value=".$row_cat['id'].">".$row_cat['nombre']." (".$row_cat['edad_minima']."-".$row_cat['edad_maxima'].")</option>";
		}

	}
}
$select .= "</select>"; 
echo $select;  
?>
