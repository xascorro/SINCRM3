<?php
include('security.php');

$id_fase = $_GET['id_fase'] ?? 0;
$temporada_año = $_GET['temporada_año'] ?? date('Y');

if($id_fase > 0){
    // 1. Obtener la categoría de esta fase
    $q_fase = mysqli_query($connection, "SELECT id_categoria FROM fases WHERE id = '$id_fase'");
    if($fase = mysqli_fetch_assoc($q_fase)){
        $id_categoria_limite = $fase['id_categoria'];
        
        // 2. Cargar el selector de nadadoras con el contexto de edad
        // nadadoras_select_option.php usa $id_categoria_limite y $temporada_año
        include('includes/nadadoras_select_option.php');
    } else {
        echo "Error: Fase no encontrada.";
    }
} else {
    echo "Seleccione una fase válida.";
}
?>