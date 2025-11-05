<?php

if(isset($_GET['tipo_elemento']) or isset($_GET['tipo_basemark'])){
	include('../security.php');
	include('../database/dbconfig.php');
}

//configurar select para dd
if(isset($_GET['tipo_elemento']))
	$tipo_elemento = $_GET['tipo_elemento'];
if(isset($_GET['id_tipo_hibrido']))
	$_POST['id_tipo_hibrido'] = $_GET['id_tipo_hibrido'];

if($tipo_elemento == 'dd'){
	if($_POST['id_tipo_hibrido']==1){
        $query = "SELECT * FROM dificultad_hibridos WHERE (subagrupar is NULL or subagrupar not like 'no') and version = '2425' order by orden_select";
		$class = 'id_tipo_hibrido';
    }else if($_POST['id_tipo_hibrido']==2){
        $query = "SELECT * from dificultad_tre  where version = '2425'";
		$class = 'id_tipo_hibrido';
    }else if($_POST['id_tipo_hibrido']==4 or isset($_GET['tipo_acro'])){
		$where = '';
		if(isset($_GET['tipo_acro']) ){
			$tipo_acro = $_GET['tipo_acro'];
			switch ($tipo_acro) {
				case 3:
					$tipo_acro = 'ACROPAIR';
					break;
				case 8:
					$tipo_acro = 'Grupo-A';
					break;
				case 9:
					$tipo_acro = 'Grupo-B';
					break;
				case 11:
					$tipo_acro = 'Grupo-P';
					break;
				case 12:
					$tipo_acro = 'Grupo-C';
					break;
			}
			$where = ' WHERE agrupar like "%'.$_GET['tipo_acro'].'%"';
			$where = ' WHERE agrupar like "%'.$tipo_acro.'%"';
		}
        $query = "SELECT * from dificultad_acrobacias".$where." and version = '2425' order by orden_select";
		$class = 'id_tipo_hibrido';
    }
}


if(isset($_GET['tipo_basemark'])){
	$tipo_elemento = 'Basemark';
	$tipo_basemark = $_GET['tipo_basemark'];
}
$basemark = '%';

if(@$tipo_basemark==2){
	$basemark = 'sin_basemark';
}else if(@$tipo_basemark==4){
	$basemark = "%Grupo%' or codigo like '%ACROPAIR%";
}else if(@$tipo_basemark==1){
	$basemark = '%HYBRID%';
}

if($tipo_elemento == 'Basemark'){
    $query = "SELECT * from dificultad_basemark WHERE codigo like '".$basemark."'";
	$class = 'tipo_basemark';
}elseif($tipo_elemento == 'bonus'){
    $query = "SELECT * FROM dificultad_hibridos WHERE agrupar like 'Bonus' and version = '2425' ";
	$class = 'bonus_tipo_hibrido';
}
$query_run2 = mysqli_query($connection,$query);
$select = "<label for='id_dificultad_hibrido$x'></label>";
$select .= "<select name='$tipo_elemento$x' id='$tipo_elemento$x' class='form-control $class'>";
if(mysqli_num_rows($query_run2) > 0){

	$agrupar = '';
	if(mysqli_num_rows($query_run2) > 1)
    	$select .= "<option value=''> Selecciona valor </option>";
	while ($row = mysqli_fetch_assoc($query_run2)) {
		$row2['codigo'] = str_replace("'", "XX", $row['codigo']);
		$codigoValor = $row['codigo'] . "(". $row['valor'] .")";
        $longitudCodigoValor = strlen($codigoValor);
        $espaciosNecesarios = max(0, 25 - $longitudCodigoValor);
        $espacios = str_repeat("&nbsp;", $espaciosNecesarios);
		$tabulacionesNecesarias = max(0, ceil((20 - $longitudCodigoValor) / 4)); // Asumiendo 8 caracteres por tabulación
        $tabulaciones = str_repeat("\t", $tabulacionesNecesarias);

		if($agrupar != @$row['agrupar'].@$row['subagrupar']){
				$select .= '</optgroup><optgroup label="'.$row['agrupar'].' '.@$row['subagrupar'].'">';
			}
		if($id_dificultad == $row['id'] or $texto == $row['codigo']){
			$select .= "<option selected value=".$row['id'].">".$row['codigo']." +".$row['valor']."</option>";
		}
		else{
			$select .= "<option value='".$row['id']."'>".$row['codigo'].$tabulaciones."(".$row['valor'].")</option>";
		}
		$agrupar = @$row['agrupar'].@$row['subagrupar'];


	}
}
$select .= "</select>";
echo $select;
?>
