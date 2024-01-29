<?php  
$query = "SELECT id, numero, nombre FROM figuras";
$query_run = mysqli_query($connection,$query); 
$select = "<label for='figura'>Figura</label>";
$select .= "<select name='figura' id='figura' class='form-control'>";
if(mysqli_num_rows($query_run) > 0){
	while ($row = mysqli_fetch_assoc($query_run)) {	    
		if(intval(@$_POST['id_figura']) == $row['id']){
			$select .= "<option selected value=".$row['id'].">".$row['numero'].' - '.$row['nombre']."</option>";
		}
		else{
			$select .= "<option value=".$row['id'].">".$row['numero'].' - '.$row['nombre']."</option>";
		}

	}
}
$select .= "</select>"; 
echo $select;  
?>
