<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');
//include('./lib/my_functions.php');
?>
<!-- Content Wrapper -->
<div id="content-wrapper" class="d-flex flex-column">

  <!-- Main Content -->
  <div id="content">
    <?php
    include('includes/topbar.php');
    ?>
    <!-- template -->
    <!-- Tu código empieza aquí -->



    <!-- Begin Page Content -->
    <div class="container-fluid">
      <!-- Titulo página y pdf -->
      <?php
        $query = "SELECT categorias.nombre as categoria, modalidades.nombre as modalidad, figuras.nombre as figura, numero, grado_dificultad FROM fases, categorias, modalidades, figuras WHERE fases.id=".$_POST['id_fase']." and categorias.id = fases.id_categoria and modalidades.id = fases.id_modalidad and figuras.id = fases.id_figura";
        $nombres = mysqli_fetch_assoc(mysqli_query($connection, $query));
        $nombre_modalidad = $nombres['modalidad'];
        $nombre_categoria = $nombres['categoria'];
        $numero_figura = $nombres['numero'];
        $nombre_figura = $nombres['figura'];
        $grado_dificultad = $nombres['grado_dificultad'];

        ?>
      <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h4 class="mb-0 font-weight-bold text-primary"><i class="fas fa-fw fa-flag-checkered"></i>Puntuar: <?php echo $nombre_modalidad." ".$nombre_categoria ?></h4>
        <h4 class="mb-0 text-gray-800"><?php echo $numero_figura." - ".$nombre_figura." GD:".$grado_dificultad;?></h4>
      </div>

      <div class="plf-v2">

          <?php
