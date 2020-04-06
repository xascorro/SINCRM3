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
				<h4 class="mb-0 font-weight-bold text-primary">Editar puesto de juez</h4>
			</div>

			<div class="card-body">
				<?php
//Editar nadadora
				if(isset($_POST['edit_btn'])){
					$id = $_POST['edit_id'];

					$query = "SELECT id, id_juez, nombre FROM puesto_juez WHERE id = '$id'";
					$query_run = mysqli_query($connection,$query);
					foreach ($query_run as $row) {
						$_POST['id_juez'] = $row['id_juez'];
						?>
						<form action="paneles_jueces_code.php" method="POST">
							<input type="hidden" name="edit_id" value="<?php echo $row['id']?>">
							<div class="form-group">
								<div class="row">
									<div class="col">
										<label for="edit_nombre">Puesto</label><input type="text" class="form-control" name="edit_nombre" value="<?php echo $row['nombre']?>"placeholder="Puesto">
									</div>
									<div class="col">
										<?php
										include('./includes/juez_select_option.php');
										?>
									</div>
									
								</div>
							</div>
							
							
							<a href="paneles_jueces.php" class="btn btn-danger"> Cancelar </a>
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