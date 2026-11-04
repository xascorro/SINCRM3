<?php  
$condicion = '';
// Si es ADMIN (rol 1) o se solicita todos los clubes, no se restringe por club de usuario
if(isset($_SESSION['club']) && $_SESSION['club'] > 0 && @$_SESSION['id_rol'] != 1 && empty($todos_los_clubes)){
    $condicion = ' WHERE id = '.$_SESSION['club'];
}
if(!isset($row['club'])){
	$row['club'] = @$_SESSION['id_club_rutina'];
}
if(isset($_POST['club']))
	$row['club'] = $_POST['club'];

$where_clause = !empty($condicion) ? $condicion : "WHERE activo = 1";
$query = "SELECT id, nombre_corto FROM clubes $where_clause ORDER BY nombre_corto ASC";
$query_run_select = mysqli_query($connection,$query);
$select = "<label for='club' class='small font-weight-bold'>Club</label>";
$select .= "<select name='club' id='club' class='form-control form-control-sm'>";

// Añadir opción "Sin Club" si no hay restricción de club en la sesión
if(empty($condicion)) {
    $selected = (@$row['club'] == 0) ? 'selected' : '';
    $placeholder_text = !empty($label_sin_club) ? $label_sin_club : '-- Sin Club / Global --';
    $select .= "<option value='0' $selected>$placeholder_text</option>";
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
