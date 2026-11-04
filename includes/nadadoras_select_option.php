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

// Cachear límites de categoría una sola vez para no repetir query en cada nadadora
$cat_min_edad = null;
$cat_max_edad = null;
if (isset($temporada_año) && isset($id_categoria_limite)) {
    $q_cat = mysqli_query($connection, "SELECT edad_minima, edad_maxima FROM categorias WHERE id = '$id_categoria_limite'");
    if ($cat = mysqli_fetch_assoc($q_cat)) {
        $cat_min_edad = intval($cat['edad_minima']);
        $cat_max_edad = intval($cat['edad_maxima']);
    }
}

// Nadadoras ya inscritas en esta categoría y competición (para deshabilitar y prevenir duplicados)
$nadadoras_ya_inscritas = [];
$check_comp = isset($id_comp) ? intval($id_comp) : (isset($id_competicion) ? intval($id_competicion) : intval($_SESSION['id_competicion_activa'] ?? 0));
if ($check_comp > 0 && isset($id_categoria_limite) && $id_categoria_limite > 0) {
    $q_ins = mysqli_query($connection, "SELECT DISTINCT i.id_nadadora 
                                        FROM inscripciones_figuras i 
                                        JOIN fases f ON i.id_fase = f.id 
                                        WHERE f.id_categoria = '$id_categoria_limite' 
                                          AND f.id_competicion = '$check_comp'");
    if ($q_ins) {
        while ($r_ins = mysqli_fetch_row($q_ins)) {
            $nadadoras_ya_inscritas[] = intval($r_ins[0]);
        }
    }
}

if(mysqli_num_rows($query_run111) > 0){
	while ($row111 = mysqli_fetch_assoc($query_run111)) {
        $id_swimmer = intval($row111['id']);
        $año_nac = intval($row111['año_nacimiento']);
        $dishabilitada = "";
        $badge_edad = "";
        
        // 1. Comprobar si ya está inscrita en esta categoría y competición
        if (in_array($id_swimmer, $nadadoras_ya_inscritas)) {
            $dishabilitada = "disabled";
            $badge_edad = " [YA INSCRITA]";
        } 
        // 2. Comprobar elegibilidad por edad si hay contexto
        elseif (isset($temporada_año) && $cat_min_edad !== null && $cat_max_edad !== null) {
            $edad = intval($temporada_año) - $año_nac;
            if ($edad < $cat_min_edad || $edad > $cat_max_edad) {
                $dishabilitada = "disabled";
                $badge_edad = " [FUERA DE EDAD: $edad años]";
            } else {
                $badge_edad = " [$edad años]";
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