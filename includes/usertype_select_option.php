<?php  
$query = "SELECT id, usertype_nombre FROM usertype";
$query_run_select = mysqli_query($connection,$query); 
$select = "<label for='usertype'>Tipo de Usuario</label>";
$select .= "<select name='edit_usertype' id='usertype' class='form-control'>";
if(mysqli_num_rows($query_run_select) > 0){
	while ($row_select = mysqli_fetch_assoc($query_run_select)) {	    
		if(intval($row['usertype']) == $row_select['id']){
			$select .= "<option selected value=".$row_select['id'].">".$row_select['usertype_nombre']."</option>";
		}
		else{
			$select .= "<option value=".$row_select['id'].">".$row_select['usertype_nombre']."</option>";
		}

	}
}
$select .= "</select>"; 
echo $select;  
?>