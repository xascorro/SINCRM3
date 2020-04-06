<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');
//includes para MarkDown
include ('includes/parsedown.php');
include ('includes/parsedownExtra.php');
$ParsedownExtra = new ParsedownExtra();
$ParsedownExtra->setBreaksEnabled(false);
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
				<h4 class="mb-0 font-weight-bold text-primary">Editar figura</h4>
			</div>

			<div class="card-body">
				<?php
//Editar nadadora
				if(isset($_POST['edit_btn'])){
					$id = $_POST['edit_id'];
					$query = "SELECT * from figuras WHERE id = '$id'";
					$query_run = mysqli_query($connection,$query);
					foreach ($query_run as $row) {
						?>
						<form action="figuras_code.php" method="POST">
							<input type="hidden" name="edit_id" value="<?php echo $row['id']?>">
							<div class="form-group">
								<div class="row">
									<div class="col-2">
										<label for="edit_numero">Número</label><input type="text" class="form-control" name="edit_numero" value="<?php echo $row['numero']?>"placeholder="Número de Figura">
									</div>
									<div class="col">
										<label for="edit_nombre">Nombre</label><input type="text" class="form-control" name="edit_nombre" value="<?php echo $row['nombre']?>"placeholder="Nombre">
									</div>
									<div class="col-2">
										<label for="edit_grado_dificultad">GD</label><input type="text" class="form-control" name="edit_grado_dificultad" value="<?php echo $row['grado_dificultad']?>" placeholder="Grado de Dificultad de la Figura">

									</div>
								</div>
								<div class="form-group">
									<label for="descripcion">Descripción (en formato <a href='https://www.markdownguide.org/'>Markdown</a>)</label>
									<textarea class="form-control" name="descripcion" rows="6"><?php echo $row['descripcion'];?></textarea>		<hr>									
									<div class="alert"> 
										<?php echo $ParsedownExtra->text($row['descripcion']); ?>
									</div>
								</div>
							</div>

							<a href="figuras.php" class="btn btn-danger"> Cancelar </a>
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