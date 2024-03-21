<?php
$query = "SELECT penalizaciones.id, penalizaciones.codigo, penalizaciones.puntos, penalizaciones.resumen, penalizaciones.aplicable_a, paneles_tipo.nombre FROM penalizaciones, paneles_tipo WHERE penalizaciones.id_paneles_tipo = paneles_tipo.id";
$query_run = mysqli_query($connection,$query);
$select = "<label for='id_penalizacion'>Penalizaciones</label>";
$select .= "<select tabindex=900 name='id_penalizacion' id='id_penalizacion' class='form-control'>";
$select .= "<option value='0'>Sin penalizacion</option>";

if(mysqli_num_rows($query_run) > 0){
	while ($row = mysqli_fetch_assoc($query_run)) {
			$select .= "<option value=".$row['id'].">".$row['codigo']."  ".$row['puntos']." '".$row['aplicable_a']."'  '".$row['nombre']."' ->   ".$row['resumen']."</option>";
	}
}
$select .= "</select>";
echo $select;
?>
