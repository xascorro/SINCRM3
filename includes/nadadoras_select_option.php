<?php
if(isset($_SESSION['club']) && $_SESSION['club'] > 0)
    $query = "SELECT id, nombre, apellidos, año_nacimiento, licencia FROM nadadoras WHERE activo = 1 AND club = ".$_SESSION['club']." ORDER BY apellidos, nombre";
else
    $query = "SELECT id, nombre, apellidos, año_nacimiento, licencia FROM nadadoras WHERE activo = 1 ORDER BY apellidos, nombre";

$query_run111 = mysqli_query($connection,$query);
$select = "<select name='id_nadadora' class='form-control'>";
$select .= "<option value=' '> --- </option>";
if(mysqli_num_rows($query_run111) > 0){
	while ($row111 = mysqli_fetch_assoc($query_run111)) {
		if(@intval($id_nadadora) == $row111['id']){
			$select .= "<option selected value=".$row111['id'].">".$row111['apellidos'].", " .$row111['nombre']." (".$row111['año_nacimiento'].")</option>";
		}
		else{
			$select .= "<option value=".$row111['id'].">".$row111['apellidos'].", " .$row111['nombre'].' ('.$row111['año_nacimiento'].")</option>";
		}

	}
}
$select .= "</select>";
echo $select;
?>
