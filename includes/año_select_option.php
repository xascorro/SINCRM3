<?php

$select = "<label for='fecha_nacimiento'>Año</label>";
$select .= "<select name='edit_fecha_nacimiento' id='fecha_nacimiento' class='form-control'>";
$años = date("Y")-60;
	while ((date("Y"))-3 > $años) {
		if($años == $S_SESSION['año_nacimiento']){
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
