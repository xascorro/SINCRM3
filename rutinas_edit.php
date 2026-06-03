<?php
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

		<!-- Begin Page Content -->
		<div class="container-fluid">

			<!-- Titulo página y pdf -->
			<div class="d-sm-flex align-items-center justify-content-between mb-4">
				<h4 class="mb-0 font-weight-bold text-primary">Editar rutina</h4>
			</div>

			<div class="card-body">
				<?php
//Editar rutina
				if(isset($_POST['edit_btn'])){
					$club = $_POST['club'];
					$query = "SELECT id, id_fase, id_club as club, orden, preswimmer, tematica, musica FROM rutinas WHERE id = '$id_rutina'";
					$query_run = mysqli_query($connection,$query);
					foreach ($query_run as $rutina) {
						?>
				<form action="rutinas_code.php" method="POST" enctype='multipart/form-data'>
					<div class="row form-group">
						<div class="col-12 col-md-2">
							<label for="edit_orden">Orden</label>
							<input type="number" class="form-control" name="orden" value="<?php echo $rutina['orden']?>" placeholder="orden">
						</div>

						<div class="col-12 col-md-5">
							<?php
									include('includes/fases_select_option.php');
									?>
						</div>

						<div class="col-12 col-md-5">
							<?php
                                    include('includes/club_select_option.php');
									?>
						</div>
						<div class="col-12 col-md-6">
							<label for="tematica">Temática</label>
							<input class="form-control" type="text" name="tematica" placeholder="Tema de la rutina" value="<?php echo $rutina['tematica']?>">
						</div>
						<div class="border-top border-info my-5"></div>
						<div class="col-12 col-md-6">
							<label for="musica">Música</label>
							<input type="file" class="custom-file" name="musica">
						</div>

					</div>

					<button type="submit" name="cancel_btn" class="btn btn-danger">Cancelar</button>
					<button type="submit" name="update_btn" class="btn btn-primary">Actualizar</button>
				</form>
				<?php
			}

		}
		?>




			</div>


			<!-- template -->
			<?php
	include('includes/scripts.php');
	include('includes/footer.php');
	?>
