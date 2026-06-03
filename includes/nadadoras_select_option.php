<?php
// Filtro por club (prioridad al club de la rutina si está definido, si no al de la sesión, si no todo)
if(isset($id_club_rutina) && $id_club_rutina > 0)
    $query = "SELECT id, nombre, apellidos, año_nacimiento, licencia FROM nadadoras WHERE activo = 1 AND club = ".$id_club_rutina." ORDER BY apellidos, nombre";
elseif(isset($_SESSION['club']) && $_SESSION['club'] > 0)
    $query = "SELECT id, nombre, apellidos, año_nacimiento, licencia FROM nadadoras WHERE activo = 1 AND club = ".$_SESSION['club']." ORDER BY apellidos, nombre";
else
    $query = "SELECT id, nombre, apellidos, año_nacimiento, licencia FROM nadadoras WHERE activo = 1 ORDER BY apellidos, nombre";

$query_run111 = mysqli_query($connection,$query);
$select = "<select name='id_nadadora' id='id_nadadora' class='form-control'>";
$select .= "<option value=' '> --- Seleccionar Nadadora --- </option>";

if(mysqli_num_rows($query_run111) > 0){
	while ($row111 = mysqli_fetch_assoc($query_run111)) {
        $año_nac = intval($row111['año_nacimiento']);
        $dishabilitada = "";
        $badge_edad = "";
        
        // Lógica de Elegibilidad por Edad (si hay contexto de temporada y categoría)
        if(isset($temporada_año) && isset($id_categoria_limite)){
            $edad = intval($temporada_año) - $año_nac;
            
            // Cachear límites de categoría para no repetir query (opcional, aquí por simplicidad)
            $q_cat = mysqli_query($connection, "SELECT edad_minima, edad_maxima FROM categorias WHERE id = '$id_categoria_limite'");
            if($cat = mysqli_fetch_assoc($q_cat)){
                $min = intval($cat['edad_minima']);
                $max = intval($cat['edad_maxima']);
                
                if($edad < $min || $edad > $max){
                    $dishabilitada = "disabled";
                    $badge_edad = " [FUERA DE EDAD: $edad años]";
                } else {
                    $badge_edad = " [$edad años]";
                }
            }
        }

		if((isset($id_nadadora_actual) && $id_nadadora_actual == $row111['id']) || @intval($id_nadadora) == $row111['id']){
			$select .= "<option selected value=".$row111['id']." $dishabilitada>".$row111['apellidos'].", " .$row111['nombre']." (".$row111['año_nacimiento'].")".$badge_edad."</option>";
		}
		else{
			$select .= "<option value=".$row111['id']." $dishabilitada>".$row111['apellidos'].", " .$row111['nombre']." (".$row111['año_nacimiento'].")".$badge_edad."</option>";
		}
	}
}
$select .= "</select>";
echo $select;
?>