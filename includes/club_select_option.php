<?php  
if(isset($_SESSION['club'])){
    $condicion = ' WHERE id = '.$_SESSION['club'];
}
$query = "SELECT id, nombre_corto FROM clubes ".@$condicion;
$query_run_select = mysqli_query($connection,$query);
$select = "<label for='club'>Club</label>";
$select .= "<select name='club' id='club' class='form-control'>";
if(mysqli_num_rows($query_run_select) > 0){
	while ($row_select = mysqli_fetch_assoc($query_run_select)) {
//		if(intval($row_select['id']) == $_SESSION['club']){
		if(intval($row_select['id']) == $row['club']){
			$select .= "<option selected value=".$row_select['id'].">".$row_select['nombre_corto']."</option>";
		}
		else{
			$select .= "<option value=".$row_select['id'].">".$row_select['nombre_corto']."</option>";
		}

	}
}
$select .= "</select>"; 
echo $select;  
?>
