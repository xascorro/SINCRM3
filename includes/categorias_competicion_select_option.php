<?php
$query_cat_comp = "SELECT id, nombre, edad_minima, edad_maxima FROM categorias WHERE categorias.id IN (SELECT id_categoria FROM fases WHERE id_competicion = ".$_SESSION['id_competicion_activa'].")";
$query_run_cat_comp = mysqli_query($connection, $query_cat_comp);
$select = "<label for='categorias'>Categoría</label>";
$select .= "<select name='categoria' id='categoria' class='form-control'>";
$select .= "<option value='0'>Todas las categorías</option>";
if(mysqli_num_rows($query_run_cat_comp) > 0){
	while ($row_cat_comp = mysqli_fetch_assoc($query_run_cat_comp)) {
		if(intval(@$_POST['id_categoria']) == $row_cat_comp['id']){
			$select .= "<option selected value=".$row_cat_comp['id'].">".$row_cat_comp['nombre']." (".$row_cat_comp['edad_minima']."-".$row_cat_comp['edad_maxima'].")</option>";
		}
		else{
            $select .= "<option value=".$row_cat_comp['id'].">".$row_cat_comp['nombre']." (".$row_cat_comp['edad_minima']."-".$row_cat_comp['edad_maxima'].")</option>";
		}
	}
}
$select .= "</select>";
echo $select;
?>
