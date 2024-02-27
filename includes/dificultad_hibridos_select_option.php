<?php
if(isset($_GET['tipo_elemento'])){
	include('../security.php');
	include('../database/dbconfig.php');
}
if(isset($_GET['tipo_elemento']))
	$tipo_elemento = $_GET['tipo_elemento'];
if(isset($_GET['id_tipo_hibrido']))
	$_POST['id_tipo_hibrido'] = $_GET['id_tipo_hibrido'];

if($tipo_elemento == 'Basemark'){
    $query = "SELECT * from dificultad_hibridos WHERE codigo like 'NM%' or codigo like 'TU%' or codigo like 'ACROPA%'";
	$class = 'basemark_tipo_hibrido';
}elseif($tipo_elemento == 'dd'){
//    $query = "SELECT id, codigo, valor from dificultad_hibridos WHERE codigo like 'T%' or codigo like 'R%' or codigo like 'F%' or codigo like 'AW%' or codigo like 'C%'";
    if($_POST['id_tipo_hibrido']==2){
        $query = "SELECT * from dificultad_tre";
		$class = 'id_tipo_hibrido';
    }else if($_POST['id_tipo_hibrido']==4){
        $query = "SELECT * from dificultad_acropair";
		$class = 'id_tipo_hibrido';
    }else if($_POST['id_tipo_hibrido']==1){
        $query = "SELECT * from dificultad_hibridos";
		$class = 'id_tipo_hibrido';
    }else{
    $query = "SELECT * from dificultad_hibridos";
		$class = 'id_tipo_hibrido';
    }
}elseif($tipo_elemento == 'bonus'){
    $query = "SELECT * from dificultad_hibridos".@$where;
	$class = 'bonus_tipo_hibrido';
}

$query_run2 = mysqli_query($connection,$query);
$select = "<label for='id_dificultad_hibrido$x'></label>";
$select .= "<select name='$tipo_elemento$x' id='$tipo_elemento$x' class='form-control $class'>";
if(mysqli_num_rows($query_run2) > 0){
	$agrupar = '';
    $select .= "<option value=''> --- </option>";
	while ($row = mysqli_fetch_assoc($query_run2)) {
		if($agrupar != @$row['agrupar']){
				$select .= '</optgroup><optgroup label="'.$row['agrupar'].'">';
			}
		if($texto == $row['codigo']){
			$select .= "<option selected value='".$row['codigo']."'>".$row['codigo']." +".$row['valor']."</option>";
		}
		else{
			$select .= "<option value='".$row['codigo']."'>".$row['codigo']." +".$row['valor']."</option>";
		}
		$agrupar = @$row['agrupar'];


	}
}
$select .= "</select>";
echo $select;
?>
