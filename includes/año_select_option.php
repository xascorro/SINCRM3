<?php
if(!isset($_SESSION['año_nacimiento']))
	$_SESSION['año_nacimiento'] = date("Y")-6;
$select = "<label for='fecha_nacimiento'>Año</label>";
$select .= "<select name='fecha_nacimiento' id='fecha_nacimiento' class='form-control'>";
$años = date("Y")-60;
	while ((date("Y"))-3 > $años) {
		if($años == $_SESSION['año_nacimiento']){
			$select .= "<option selected value=".$años.">".$años."</option>";
		}
		else{
			$select .= "<option value=".$años.">".$años."</option>";
		}
        $años++;

	}
$select .= "</select>";
echo $select;
?>
