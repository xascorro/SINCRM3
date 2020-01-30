<?php
session_start();
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
				<h4 class="mb-0 font-weight-bold text-primary">Editar nadadora</h4>
			</div>

			<div class="card-body">
				<?php
//Editar nadadora
				if(isset($_POST['edit_btn'])){
					$connection = mysqli_connect('localhost','root','xas','sincrm3');
					$id = $_POST['edit_id'];
					$query = "SELECT * from nadadoras WHERE id = '$id'";
					$query_run = mysqli_query($connection,$query);
					foreach ($query_run as $row) {
						?>
						<form action="nadadora_code.php" method="POST">
							<div class="form-group">
								<input type="hidden" name="edit_id" value=" <?php echo $row['id']?>">
								<label for="licencia">Licencia</label>
								<input type="text" class="form-control" name="edit_licencia" value=" <?php echo $row['licencia']?>" placeholder="Número de licencia, NIF para nadadoras sin licencia federativa">
							</div>
							<div class="form-group">
								<label for="apellidos">Apellidos</label>
								<input type="text" class="form-control" name="edit_apellidos" value=" <?php echo $row['apellidos']?>"placeholder="Apellidos">
							</div>
							<div class="form-group">
								<label for="nombre">Nombre</label>
								<input type="text" class="form-control" name="edit_nombre" value=" <?php echo $row['nombre']?>"placeholder="Nombre">
							</div>
							<div class="form-group">
								<label for="fechadenacimiento">Fecha de Nacimiento</label>
								<input type="text" class="form-control" name="edit_fechadenacimiento" value=" <?php echo $row['fechadenacimiento']?>" placeholder="DD-MM-AAAA">
							</div>
							<a href="nadadora.php" class="btn btn-danger"> Cancelar </a>
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