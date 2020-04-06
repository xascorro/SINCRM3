<?php  
$query = "SELECT id, nombre, apellidos FROM jueces";
$query_run = mysqli_query($connection,$query); 
$select = "<label for='id_juez'>Juez</label>";
$select .= "<select name='id_juez' class='form-control'>";
if(mysqli_num_rows($query_run) > 0){
	while ($row = mysqli_fetch_assoc($query_run)) {	    
		if(intval($_POST['id_juez']) == $row['id']){
			$select .= "<option selected value=".$row['id'].">".$row['nombre'].' '.$row['apellidos']."</option>";
		}
		else{
			$select .= "<option value=".$row['id'].">".$row['nombre'].' '.$row['apellidos']."</option>";
		}

	}
}
$select .= "</select>"; 
echo $select;  
?>