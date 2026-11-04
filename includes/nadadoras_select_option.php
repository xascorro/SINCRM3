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

// Helpers para jerarquía de categorías deportivas (RFEN/AQUA)
if (!function_exists('get_sincro_cat_hierarchy_level')) {
    function get_sincro_cat_hierarchy_level($nombre, $edad_min, $edad_max) {
        $n = strtolower($nombre ?? '');
        if (strpos($n, 'especial') !== false || (intval($edad_min) == 0 && intval($edad_max) >= 90 && strpos($n, 'junior') === false && strpos($n, 'absoluto') === false)) {
            return -1; // Abierto / Mixto sin límite
        }
        if (strpos($n, 'prebenjamín') !== false || strpos($n, 'prebenjamin') !== false || strpos($n, 'benjamín') !== false || strpos($n, 'benjamin') !== false || intval($edad_max) <= 8) {
            return 0; // Benjamín / Prebenjamín (<= 8 años)
        }
        if (strpos($n, 'alevín') !== false || strpos($n, 'alevin') !== false || intval($edad_max) <= 12) {
            return 1; // Alevín (9 - 12 años)
        }
        if (strpos($n, 'infantil') !== false || intval($edad_max) <= 15) {
            return 2; // Infantil (13 - 15 años)
        }
        if (strpos($n, 'junior') !== false || intval($edad_max) <= 18) {
            return 3; // Junior (16 - 18 años)
        }
        if (strpos($n, 'absoluto') !== false || intval($edad_min) >= 18) {
            return 4; // Absoluto (19+ años)
        }
        return -1;
    }
}

if (!function_exists('get_swimmer_sincro_cat_level')) {
    function get_swimmer_sincro_cat_level($edad) {
        if ($edad <= 8) return 0;  // Benjamín
        if ($edad <= 12) return 1; // Alevín
        if ($edad <= 15) return 2; // Infantil
        if ($edad <= 18) return 3; // Junior
        return 4;                  // Absoluto
    }
}

// 1. Contexto para RUTINAS: Control según rango de edad de la categoría (permite solapamientos oficiales como Junior/Absoluto)
$rutina_cat_min = null;
$rutina_cat_max = null;
$rutina_cat_nombre = '';
if (!empty($modo_rutina) && !empty($id_categoria_rutina)) {
    $q_rcat = mysqli_query($connection, "SELECT nombre, edad_minima, edad_maxima FROM categorias WHERE id = '$id_categoria_rutina'");
    if ($rc = mysqli_fetch_assoc($q_rcat)) {
        $rutina_cat_nombre = $rc['nombre'];
        $rutina_cat_min = ($rc['edad_minima'] !== null && $rc['edad_minima'] !== '') ? intval($rc['edad_minima']) : null;
        $rutina_cat_max = ($rc['edad_maxima'] !== null && $rc['edad_maxima'] !== '') ? intval($rc['edad_maxima']) : null;
    }
}

// 2. Contexto para FIGURAS / INDIVIDUAL: Categoría exacta y control de duplicados
$cat_min_edad = null;
$cat_max_edad = null;
$nadadoras_ya_inscritas = [];

if (empty($modo_rutina)) {
    if (isset($temporada_año) && isset($id_categoria_limite)) {
        $q_cat = mysqli_query($connection, "SELECT edad_minima, edad_maxima FROM categorias WHERE id = '$id_categoria_limite'");
        if ($cat = mysqli_fetch_assoc($q_cat)) {
            $cat_min_edad = intval($cat['edad_minima']);
            $cat_max_edad = intval($cat['edad_maxima']);
        }
    }

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
}

if(mysqli_num_rows($query_run111) > 0){
	while ($row111 = mysqli_fetch_assoc($query_run111)) {
        $id_swimmer = intval($row111['id']);
        $año_nac = intval($row111['año_nacimiento']);
        $dishabilitada = "";
        $badge_edad = "";
        
        // A. REGLAS PARA RUTINAS: Control según rango de edad de la categoría (con soporte para solapamientos oficiales)
        if (!empty($modo_rutina) && isset($temporada_año) && $temporada_año > 0) {
            $edad = intval($temporada_año) - $año_nac;
            $es_especial = (strpos(strtolower($rutina_cat_nombre), 'especial') !== false) || ($rutina_cat_min === 0 && $rutina_cat_max >= 90 && strpos(strtolower($rutina_cat_nombre), 'junior') === false && strpos(strtolower($rutina_cat_nombre), 'absolut') === false);

            if (!$es_especial && $rutina_cat_min !== null && $edad < $rutina_cat_min) {
                // Menor que la edad mínima de la rutina
                $dishabilitada = "disabled";
                $badge_edad = " [DEMASIADO JOVEN: $edad años]";
            } elseif (!$es_especial && $rutina_cat_max !== null && $edad > $rutina_cat_max) {
                // Mayor que la edad máxima de la rutina
                $dishabilitada = "disabled";
                $badge_edad = " [DEMASIADO MAYOR: $edad años]";
            } else {
                // Dentro del rango de la categoría: PERMITIDO
                $badge_edad = " [$edad años]";
            }
        }
        // B. REGLAS PARA FIGURAS: Duplicados y edad exacta
        elseif (empty($modo_rutina)) {
            if (in_array($id_swimmer, $nadadoras_ya_inscritas)) {
                $dishabilitada = "disabled";
                $badge_edad = " [YA INSCRITA]";
            } elseif (isset($temporada_año) && $cat_min_edad !== null && $cat_max_edad !== null) {
                $edad = intval($temporada_año) - $año_nac;
                if ($edad < $cat_min_edad) {
                    $dishabilitada = "disabled";
                    $badge_edad = " [DEMASIADO JOVEN: $edad años]";
                } elseif ($edad > $cat_max_edad) {
                    $dishabilitada = "disabled";
                    $badge_edad = " [DEMASIADO MAYOR: $edad años]";
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