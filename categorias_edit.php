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
				<h4 class="mb-0 font-weight-bold text-primary">Editar categoria</h4>
			</div>

			<div class="card-body">
				<?php
//Editar categoria
				if(isset($_POST['edit_btn'])){
					$id = $_POST['edit_id'];
					$query = "SELECT * from categorias WHERE id = '$id'";
					$query_run = mysqli_query($connection,$query);
					foreach ($query_run as $row) {
						?>
						<form action="categorias_code.php" method="POST">
							<input type="hidden" name="edit_id" value="<?php echo $row['id']?>">
							<div class="form-group">
								<div class="row">
									<div class="col">
										<label for="edit_nombre">Nombre</label><input type="text" class="form-control" name="edit_nombre" value="<?php echo $row['nombre']?>"placeholder="Nombre">
									</div>
									<div class="col">
										<label for="edit_edad_minima">Edad mínima</label><input type="number" class="form-control" name="edit_edad_minima" value="<?php echo $row['edad_minima']?>"placeholder="Edad mínima">
									</div>
									<div class="col">
										<label for="edit_edad_minima">Edad máxima</label><input type="number" class="form-control" name="edit_edad_maxima" value="<?php echo $row['edad_maxima']?>"placeholder="Edad máxima">
									</div>
									
								</div>
							</div>
							
							
							<a href="categorias.php" class="btn btn-danger"> Cancelar </a>
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