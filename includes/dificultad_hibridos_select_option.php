<?php
if($tipo_elemento == 'Basemark'){
    $query = "SELECT id, codigo, valor from dificultad_hibridos WHERE codigo like 'NM%' or codigo like 'TU%' or codigo like 'ACROPA%'";
}elseif($tipo_elemento == 'dd'){
//    $query = "SELECT id, codigo, valor from dificultad_hibridos WHERE codigo like 'T%' or codigo like 'R%' or codigo like 'F%' or codigo like 'AW%' or codigo like 'C%'";
    if($_POST['id_tipo_hibrido']==4){
        $query = "SELECT id, codigo, valor from dificultad_acropair";

    }else{
//        $query = "SELECT id, codigo, valor from dificultad_hibridos WHERE codigo like 'T%' or codigo like 'R%' or codigo like 'F%' or codigo like 'AW%' or codigo like 'C%'";
        $query = "SELECT id, codigo, valor from dificultad_hibridos";
    }
}elseif($tipo_elemento == 'bonus'){
    $query = "SELECT id, codigo, valor from dificultad_hibridos".@$where;
}

$query_run2 = mysqli_query($connection,$query);
$select = "<label for='id_dificultad_hibrido$x'></label>";
$select .= "<select name='$tipo_elemento$x' id='$tipo_elemento$x' class='form-control'>";
if(mysqli_num_rows($query_run2) > 0){
    $select .= "<option value=''> --- </option>";
	while ($row = mysqli_fetch_assoc($query_run2)) {
		if($texto == $row['codigo']){
			$select .= "<option selected value='".$row['codigo']."'>".$row['codigo']." +".$row['valor']."</option>";
		}
		else{
			$select .= "<option value='".$row['codigo']."'>".$row['codigo']." +".$row['valor']."</option>";
		}

	}
}
$select .= "</select>";
echo $select;
?>
