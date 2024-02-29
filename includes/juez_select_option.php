<?php  
$query = "SELECT id, nombre, apellidos FROM jueces";
$query_run111 = mysqli_query($connection,$query);
$select = "<select name='id_juez' class='form-control'>";
$select .= "<option value=' '> --- </option>";
if(mysqli_num_rows($query_run111) > 0){
	while ($row111 = mysqli_fetch_assoc($query_run111)) {
		if(intval(@$_POST['id_juez']) == $row111['id']){
			$select .= "<option selected value=".$row111['id'].">".$row111['nombre'].' '.$row111['apellidos']."</option>";
		}
		else{
			$select .= "<option value=".$row111['id'].">".$row111['nombre'].' '.$row111['apellidos']."</option>";
		}

	}
}
$select .= "</select>"; 
echo $select;  
?>
