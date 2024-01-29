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
//Editar nadadora
				if(isset($_POST['edit_btn'])){
					$id = $_POST['edit_id'];
                    $figura = $_POST['id_figura'];

					$query = "SELECT * from rutinas WHERE id = '$id'";
					$query_run = mysqli_query($connection,$query);
					foreach ($query_run as $row) {
						?>
						<form action="rutinas_code.php" method="POST">
							<div class="row">
								<div class="form-group col-1">
									<input type="hidden" name="edit_id" value="<?php echo $row['id']?>">
									<label for="edit_orden">Orden</label>
									<input type="number" class="form-control" name="id_club" value="<?php echo $row['orden']?>" placeholder="orden">
								</div>

								<div class="col">
									<?php
									include('includes/fases_select_option.php');
									?>
								</div>

								<div class="col">
									<?php
                                    include('includes/club_select_option.php');
									?>
								</div>
							</div>
							<a href="rutinas.php" class="btn btn-danger"> Cancelar </a>
							<button type="submit" name="update_btn" class="btn btn-primary">Actualizar</button>
						</div>
					</div>
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
