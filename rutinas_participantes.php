<?php
//include('security.php');
include('includes/header.php');
include('includes/navbar.php');

?>
<!-- Content Wrapper -->
<div id="content-wrapper" class="d-flex flex-column">

  <!-- Main Content -->
  	<div id="content">
    <?php
	  include('includes/topbar.php');
//		if(isset($_POST['id_competicion'])){
//			$_SESSION['id_competicion_activa'] = $_POST['id_competicion'];
//		}else if(isset($_SESSION['id_competicion_usuario'])){
//			$_SESSION['id_competicion_activa'] = $_SESSION['id_competicion_usuario'];
//		}
//		if(isset($_POST['id_rutina'])){
//			$id_rutina = $_POST['id_rutina'];
//		}elseif(isset($_SESSION['id_rutina'])){
//			$id_rutina = $_SESSION['id_rutina'];
//		}
//		$_SESSION['id_rutina'] = $id_rutina;;
//
//		if(isset($_POST['id_fase'])){
//			$id_fase = $_POST['id_fase'];
//		}elseif(isset($_SESSION['id_fase'])){
//			$id_fase = $_SESSION['id_fase'];
//		}
//				$_SESSION['id_fase'] = $id_fase;


    ?>
    <!-- template -->
    <!-- Tu código empieza aquí -->






    <!-- Begin Page Content -->
    <div class="container-fluid">

      <!-- Titulo página y pdf -->
      <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h4 class="mb-0 font-weight-bold text-primary"><i class="fas fa-fw fa-users"></i> Participantes de las Rutinas
         <form action="rutinas.php" method="post" class="form d-inline">
         	<input type="hidden" name="club" value="<?php echo $club?>">
			 <button type="submit" class="btn btn-primary"><i class='fa fa-chevron-left' aria-hidden='true'></i> Volver</button>
         </form>
		</h4>

        </div>

        <div class="card-body">

          <?php
