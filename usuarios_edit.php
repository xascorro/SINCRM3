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
				<h4 class="mb-0 font-weight-bold text-primary">Editar usuario</h4>
			</div>

			<div class="card-body">
				<?php
//Editar nadadora
				if(isset($_POST['edit_btn'])){
					$id = $_POST['edit_id'];
					$query = "SELECT * from usuarios WHERE id = '$id'";
					$query_run = mysqli_query($connection,$query);
					foreach ($query_run as $row) {
						?>
						<form action="usuarios_code.php" method="POST">
							<div class="row">
								<div class="form-group col-4">
									<input type="hidden" name="edit_id" value="<?php echo $row['id']?>">
									<label for="edit_username">Nombre de usuario</label>
									<input type="text" class="form-control" name="edit_username" value="<?php echo $row['username']?>" placeholder="Nombre de usuario">
								</div>
								<div class="form-group col-4">
									<label for="edit_email">Email</label>
									<input type="email" class="form-control form-control-user" name="edit_email" value="<?php echo $row['email']?>"placeholder="Email">
								</div>
								<div class="form-group col-4">
									<label for="edit_telefono">Teléfono</label>
									<input type="phone" class="form-control form-control-user" name="edit_telefono" value="<?php echo $row['telefono']?>"placeholder="Teléfono">
								</div>
							</div>
							<div class="row">
								<div class="form-group col-3">
									<label for="edit_password">...</label>
									<input type="password" class="form-control" name="edit_password" value="<?php echo $row['hash']?>"placeholder="...">
								</div>
								<div class="form-group col-3">
									<label for="edit_r_password">...</label>
									<input type="password" class="form-control" name="edit_r_password"  value="<?php echo $row['hash']?>"placeholder="...">
								</div>
								<div class="form-group col-3">
									<?php
									include('./includes/rol_select_option.php');
									?>
								</div>
								<div class="form-group col-3">
									<?php
								    include('./includes/club_select_option.php');
									?>
								</div>
							</div>
							<div class="row">
								<div class="form-group col-12">
									<label for="edit_comentario">Comentario</label>
									<input type="text" class="form-control form-control-user" name="edit_comentario" value="<?php echo $row['comentario'];?>"placeholder="Comentario">
								</div>
							</div>
							<a href="usuarios.php" class="btn btn-danger"> Cancelar </a>
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
