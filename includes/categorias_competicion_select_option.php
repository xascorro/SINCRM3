<?php
$query = "SELECT id, nombre, edad_minima, edad_maxima FROM categorias WHERE categorias.id IN (SELECT id_categoria FROM fases WHERE id_competicion = ".$_SESSION['id_competicion_activa'].")";
$query_run = mysqli_query($connection,$query);
$select = "<label for='categorias'>Categoría</label>";
$select .= "<select name='categoria' id='categoria' class='form-control'>";
$select .= "<option value='0'>Todas las categorías</option>";
if(mysqli_num_rows($query_run) > 0){
	while ($row = mysqli_fetch_assoc($query_run)) {
		if(intval($_POST['id_categoria']) == $row['id']){
			$select .= "<option selected value=".$row['id'].">".$row['nombre']." (".$row['edad_minima']."-".$row['edad_maxima'].")</option>";
		}
		else{
$select .= "<option value=".$row['id'].">".$row['nombre']." (".$row['edad_minima']."-".$row['edad_maxima'].")</option>";
		}
	}
}
$select .= "</select>";
echo $select;
?>
