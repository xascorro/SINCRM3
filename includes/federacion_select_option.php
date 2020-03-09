<?php  
$query = "SELECT id, nombre, nombre_corto FROM federaciones";
$query_run = mysqli_query($connection,$query); 
$select = "<label for='federacion'>Federación</label>";
$select .= "<select name='federacion' id='federacion' class='form-control'>";
if(mysqli_num_rows($query_run) > 0){
	while ($row = mysqli_fetch_assoc($query_run)) {	    
		if(intval($_POST['id_federacion']) == $row['id']){
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