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
							<div class="form-group">
								<input type="hidden" name="edit_id" value="<?php echo $row['id']?>">
								<label for="edit_username">Nombre de usuario</label>
								<input type="text" class="form-control" name="edit_username" value="<?php echo $row['username']?>" placeholder="Nombre de usuario">
							</div>
							<div class="form-group">
								<label for="edit_email">Email</label>
								<input type="email" class="form-control form-control-user" name="edit_email" value="<?php echo $row['email']?>"placeholder="Email">
							</div>
							<div class="form-group">
								<label for="edit_password">Contraseña</label>
								<input type="password" class="form-control" name="edit_password" value="<?php echo $row['password']?>"placeholder="Contraseña">
							</div>
							<div class="form-group">
								<label for="edit_r_password">Repite la contraseña</label>
								<input type="password" class="form-control" name="edit_r_password"  value="<?php echo $row['password']?>"placeholder="Repite la contraseña">
							</div>
							<div class="form-group">
								<label for="usertype">Usertype</label>
								<select name="edit_usertype" class="form-control">
									<option value="user">Usuario</option>
									<option value="admin">Administrador</option>
								</select>
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