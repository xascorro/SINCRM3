<?php  
if(isset($_SESSION['club']) && $_SESSION['club'] > 0){
    $condicion = ' WHERE id = '.$_SESSION['club'];
}
if(!isset($row['club'])){
	$row['club'] = @$_SESSION['id_club_rutina'];
}
if(isset($_POST['club']))
	$row['club'] = $_POST['club'];
$query = "SELECT id, nombre_corto FROM clubes ".@$condicion;
$query_run_select = mysqli_query($connection,$query);
$select = "<label for='club' class='small font-weight-bold'>Club</label>";
$select .= "<select name='club' id='club' class='form-control form-control-sm'>";

// Añadir opción "Sin Club" si no hay restricción de club en la sesión
if(!isset($condicion)) {
    $selected = (@$row['club'] == 0) ? 'selected' : '';
    $select .= "<option value='0' $selected>-- Sin Club / Global --</option>";
}

if(mysqli_num_rows($query_run_select) > 0){
	while ($row_select = mysqli_fetch_assoc($query_run_select)) {
		$selected = (intval($row_select['id']) == @$row['club']) ? 'selected' : '';
		$select .= "<option value='".$row_select['id']."' $selected>".$row_select['nombre_corto']."</option>";
	}
}
$select .= "</select>"; 
echo $select;  
?>
