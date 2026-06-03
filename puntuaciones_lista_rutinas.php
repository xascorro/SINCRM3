<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');

if(isset($_GET['id_fase']))
	$_POST['id_fase'] = $_GET['id_fase'];
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
        $query = "SELECT categorias.nombre as categoria, modalidades.nombre as modalidad FROM fases, categorias, modalidades WHERE fases.id=".$_POST['id_fase']." and categorias.id = fases.id_categoria and modalidades.id = fases.id_modalidad";
        $nombres = mysqli_fetch_assoc(mysqli_query($connection,$query));
        $nombre_modalidad = $nombres['modalidad'];
        $nombre_categoria = $nombres['categoria'];

        ?>
			<div class="d-sm-flex align-items-center justify-content-between mb-4">
				<h4 class="mb-0 font-weight-bold text-primary"><i class="fas fa-fw fa-flag-checkered"></i>Puntuar <?php echo $nombre_modalidad." ".$nombre_categoria;?>
				</h4>

			</div>

			<div class="card-body">

				<?php
