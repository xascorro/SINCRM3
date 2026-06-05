<?php
include('security.php');

if(isset($_POST['save_btn'])){
    $id_rutina = (int)$_POST['id_rutina'];
    $id_fase = (int)$_POST['id_fase'];
    $id_competicion = (int)$_POST['id_competicion'];

    // 1. Limpieza de datos anteriores para esta rutina
    mysqli_query($connection, "DELETE FROM puntuaciones_jueces WHERE id_rutina = $id_rutina");
    mysqli_query($connection, "DELETE FROM puntuaciones_elementos WHERE id_rutina = $id_rutina");

    // 2. Obtener Paneles y Jueces para procesar el POST
    $q_paneles = "SELECT * FROM paneles WHERE id_competicion = $id_competicion AND obsoleto = 'si' AND puntua = 'si' ORDER BY id ASC";
    $res_paneles = mysqli_query($connection, $q_paneles);
    
    $nota_final_acumulada = 0;
    $peso_total_acumulado = 0;

    while ($p = mysqli_fetch_assoc($res_paneles)) {
        $id_panel = $p['id'];
        $panel_name = $p['nombre'];
        $peso = (float)$p['peso'];
        
        // Buscar jueces asignados a este panel en esta fase
        $q_jueces = "SELECT pj.* FROM panel_jueces pj WHERE pj.id_fase = $id_fase AND pj.id_panel = $id_panel ORDER BY pj.numero_juez ASC";
        $res_jueces = mysqli_query($connection, $q_jueces);
        
        $notas_panel = [];
        $ids_puestos = [];

        while ($j = mysqli_fetch_assoc($res_jueces)) {
            $post_key = "nota_p{$id_panel}_j" . $j['id'];
            if (isset($_POST[$post_key]) && $_POST[$post_key] !== '') {
                $nota = floatval(str_replace(',', '.', $_POST[$post_key]));
                // Clamp entre 0 y 10
                $nota = max(0, min(10, $nota));
                
                $notas_panel[$j['id']] = $nota;
                $ids_puestos[$j['id']] = $j['id']; // ID de panel_jueces
            }
        }

        // --- LÓGICA DE CÁLCULO POR PANEL ---
        $usadas = $notas_panel;
        $eliminadas_ids = [];
        
        if (count($usadas) >= 5) {
            asort($usadas); // Ordenar manteniendo keys (id_panel_juez)
            
            // Obtener el ID del mínimo y el máximo
            $min_id = array_key_first($usadas);
            $max_id = array_key_last($usadas);
            
            // Sacar de la lista de usadas
            unset($usadas[$min_id]);
            unset($usadas[$max_id]);
            
            $eliminadas_ids = [$min_id, $max_id];
        }

        $suma = array_sum($usadas);
        $media_panel = count($usadas) > 0 ? ($suma / count($usadas)) : 0;

        // --- PERSISTENCIA ---
        // A. Guardar notas individuales
        foreach ($notas_panel as $id_pj => $n) {
            $is_min = in_array($id_pj, $eliminadas_ids) && ($n == min($notas_panel)) ? "'si'" : "NULL";
            $is_max = in_array($id_pj, $eliminadas_ids) && ($n == max($notas_panel)) ? "'si'" : "NULL";
            
            // Evitar duplicar marca si min y max son el mismo valor pero IDs distintos
            // (La lógica de IDs ya lo maneja bien arriba)
            
            $q_insert_nota = "INSERT INTO puntuaciones_jueces (id_panel_juez, id_rutina, nota, nota_menor, nota_mayor, tipo_ia) 
                              VALUES ($id_pj, $id_rutina, $n, " . (in_array($id_pj, $eliminadas_ids) && $n == min($notas_panel) ? "'si'" : "NULL") . ", " . (in_array($id_pj, $eliminadas_ids) && $n == max($notas_panel) ? "'si'" : "NULL") . ", '$panel_name')";
            // Nota: uso $panel_name en tipo_ia para auditoría rápida
            mysqli_query($connection, $q_insert_nota);
        }

        // B. Guardar media del panel
        $q_insert_media = "INSERT INTO puntuaciones_elementos (id_rutina, tipo_ia, nota_media, nota, factor) 
                           VALUES ($id_rutina, '$panel_name', $media_panel, $media_panel * ($peso/100) * 10, " . ($peso/100) . ")";
        mysqli_query($connection, $q_insert_media);

        $nota_final_acumulada += ($media_panel * ($peso / 100) * 10);
        $peso_total_acumulado += $peso;
    }

    // Normalización final si los pesos no suman 100
    if ($peso_total_acumulado > 0 && $peso_total_acumulado != 100) {
        $nota_final_acumulada = ($nota_final_acumulada / $peso_total_acumulado) * 100;
    }

    // 3. Actualizar Nota Final en la Rutina
    $q_update_rutina = "UPDATE rutinas SET nota_final = $nota_final_acumulada WHERE id = $id_rutina";
    if(mysqli_query($connection, $q_update_rutina)){
        $_SESSION['correcto'] = "Puntuación guardada correctamente.";
    } else {
        $_SESSION['estado'] = "Error al actualizar la nota final: " . mysqli_error($connection);
    }

    header("Location: puntuaciones_rutina_obsoleta.php?id_rutina=$id_rutina");
    exit();
}
?>
