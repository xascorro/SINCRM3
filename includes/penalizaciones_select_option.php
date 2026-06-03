<?php
$query_pen = "SELECT penalizaciones.id, penalizaciones.codigo, penalizaciones.puntos, penalizaciones.resumen, penalizaciones.aplicable_a, paneles_tipo.nombre FROM penalizaciones, paneles_tipo WHERE penalizaciones.id_paneles_tipo = paneles_tipo.id";
$query_run_pen = mysqli_query($connection, $query_pen);
$select = "<label for='id_penalizacion'>Penalizaciones</label>";
$select .= "<select tabindex=900 name='id_penalizacion' id='id_penalizacion' class='form-control'>";
$select .= "<option value='0'>Sin penalizacion</option>";

if(mysqli_num_rows($query_run_pen) > 0){
	while ($row_pen = mysqli_fetch_assoc($query_run_pen)) {
			$select .= "<option value=".$row_pen['id'].">".$row_pen['codigo']."  ".$row_pen['puntos']." '".$row_pen['aplicable_a']."'  '".$row_pen['nombre']."' ->   ".$row_pen['resumen']."</option>";
	}
}
$select .= "</select>";
echo $select;
?>
