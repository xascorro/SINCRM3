<?php
include('security.php');
if(isset($_GET['id_inscripcion_figuras'])){
	$query = "SELECT nota_total, nota_media, nota_final FROM inscripciones_figuras WHERE id =".$_GET['id_inscripcion_figuras'];
	$notas = mysqli_fetch_assoc(mysqli_query($connection, $query));
	echo "<td>".mysqli_result($notas,'nota_total')."</td>";
	echo "<td>".$row['nota_media']."</td>";
	echo "<td>".$row['nota_final']."</td>";
}
?>
