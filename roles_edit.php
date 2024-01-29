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
				<h4 class="mb-0 font-weight-bold text-primary">Editar Tipo de Usuario</h4>
			</div>

			<div class="card-body">
				<?php
//Editar nadadora
				if(isset($_POST['edit_btn'])){
					$id = $_POST['edit_id'];
					$query = "SELECT * from usertype WHERE id = '$id'";
					$query_run = mysqli_query($connection,$query);
					foreach ($query_run as $row) {
						?>
						<form action="usertype_code.php" method="POST">
							<div class="row">
								<div class="form-group col-4">
									<input type="hidden" name="edit_id" value="<?php echo $row['id']?>">
									<label for="edit_usertype_nombre">Rol</label>
									<input type="text" class="form-control" name="edit_usertype_nombre" value="<?php echo $row['usertype_nombre']?>" placeholder="Nombre de usuario">
								</div>
								<div class="form-group col-4">
									<label for="edit_level">Level</label>
									<input type="text" class="form-control form-control-user" name="edit_level" value="<?php echo $row['level']?>"placeholder="Level">
								</div>
							</div>

							<a href="usertype.php" class="btn btn-danger"> Cancelar </a>
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
