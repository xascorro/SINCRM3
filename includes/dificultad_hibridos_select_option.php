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
		$where_clauses = ["(version = '2425' OR version IS NULL OR version = '')"];
		if(isset($_GET['tipo_acro']) && $_GET['tipo_acro'] !== ''){
			$tipo_acro = $_GET['tipo_acro'];
			switch ($tipo_acro) {
				case 3:
				case 'ACROPAIR':
					$tipo_acro = 'ACROPAIR';
					break;
				case 4:
				case 8:
				case 'Grupo-A':
				case 'ACRO-A':
					$tipo_acro = 'ACRO-A';
					break;
				case 5:
				case 9:
				case 'Grupo-B':
				case 'ACRO-B':
					$tipo_acro = 'ACRO-B';
					break;
				case 7:
				case 11:
				case 'Grupo-P':
				case 'ACRO-P':
					$tipo_acro = 'ACRO-P';
					break;
				case 6:
				case 12:
				case 'Grupo-C':
				case 'ACRO-C':
					$tipo_acro = 'ACRO-C';
					break;
			}
			$safe_param = mysqli_real_escape_string($connection, $_GET['tipo_acro']);
			$where_clauses[] = "(agrupar LIKE '%$tipo_acro%' OR agrupar LIKE '%$safe_param%')";
		}
        $query = "SELECT * FROM dificultad_acrobacias WHERE " . implode(' AND ', $where_clauses) . " ORDER BY orden_select, id ASC";
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
	while ($row_dif = mysqli_fetch_assoc($query_run2)) {
		$codigo_safe = str_replace("'", "XX", $row_dif['codigo']);
		$codigoValor = $row_dif['codigo'] . "(". $row_dif['valor'] .")";
        $longitudCodigoValor = strlen($codigoValor);
        $espaciosNecesarios = max(0, 25 - $longitudCodigoValor);
        $espacios = str_repeat("&nbsp;", $espaciosNecesarios);
		$tabulacionesNecesarias = max(0, ceil((20 - $longitudCodigoValor) / 4)); // Asumiendo 8 caracteres por tabulación
        $tabulaciones = str_repeat("\t", $tabulacionesNecesarias);

		if($agrupar != @$row_dif['agrupar'].@$row_dif['subagrupar']){
				$select .= '</optgroup><optgroup label="'.$row_dif['agrupar'].' '.@$row_dif['subagrupar'].'">';
			}
		if((!empty($id_dificultad) && $id_dificultad == $row_dif['id']) || (!empty($texto) && $texto == $row_dif['codigo'])){
			$select .= "<option selected value=".$row_dif['id'].">".$row_dif['codigo']." +".$row_dif['valor']."</option>";
		}
		else{
			$select .= "<option value='".$row_dif['id']."'>".$row_dif['codigo'].$tabulaciones."(".$row_dif['valor'].")</option>";
		}
		$agrupar = @$row_dif['agrupar'].@$row_dif['subagrupar'];


	}
}
$select .= "</select>";
echo $select;
?>
