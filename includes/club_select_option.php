<?php  
$query = "SELECT id, nombre_corto FROM clubes";
$query_run = mysqli_query($connection,$query); 
$select = "<label for='club'>Club</label>";
$select .= "<select name='id_club' id='club' class='form-control'>";
if(mysqli_num_rows($query_run) > 0){
	while ($row = mysqli_fetch_assoc($query_run)) {	    
		if(intval($_POST['id_club']) == $row['id']){
			$select .= "<option selected value=".$row['id'].">".$row['nombre_corto']."</option>";
		}
		else{
			$select .= "<option value=".$row['id'].">".$row['nombre_corto']."</option>";
		}

	}
}
$select .= "</select>"; 
echo $select;  
?>