<?php
//FACTORIZACION
$f_chomu = 1.0; //leer de la DB más adelante
$factor_Performance = 1;
$factor_Transitions = 1;
$factor_hybrid = 1.0;
$factor_acro = 0.5;
$factor_tre = 0.5;
$factor_tre = 1;
//FIN FACTORIZACION


include('security.php');
include('includes/header.php');
include('includes/navbar.php');
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

    <?php
    if(isset($_POST['id_inscripcion_figuras']))
        $id_inscripcion_figuras = $_POST['id_inscripcion_figuras'];
    else{
        $id_inscripcion_figuras = $_GET['id_inscripcion_figuras'];
    }
    if(isset($_POST['id_fase']))
        $id_fase = $_POST['id_fase'];
    else{
        $id_fase = $_GET['id_fase'];
    }

     if(isset($_POST['id_modalidad']))
        $id_modalidad = $_POST['id_modalidad'];
    else
        $iid_modalidad = $_GET['id_modalidad'];

    $nombre_modalidad = $_POST['nombre_modalidad'];
    $nombre_categoria = $_POST['nombre_categoria'];
    $nombre_club = $_POST['nombre_club'];
    $nombre_nadadora = $_POST['nombre_nadadora'];
    $apellidos_nadadora = $_POST['apellidos_nadadora'];
      ?>

    <!-- Begin Page Content -->
    <div class="container-fluid">

      <!-- Titulo página y pdf -->
      <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h4 class="mb-0 font-weight-bold text-primary"><i class="fas fa-fw fa-flag-checkered"></i>Puntuar <?php echo $nombre_modalidad.' '.$nombre_categoria.' '.$nombre_club.' - '.$apellidos_nadadora.', '.$nombre_nadadora; ?>
           </h4>

        </div>

        <div class="card-body">

          <?php
