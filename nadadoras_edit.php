<?php
include('security.php');
include('includes/header.php');
if(!isset($_SESSION['club'])){
    include('includes/navbar.php');
}else{
    $condicion_club = " and nadadoras.club = ".$_SESSION['club'];
}?>
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
					$id = $_POST['edit_id'];
					$query = "SELECT * from nadadoras WHERE id = '$id'";
					$query_run = mysqli_query($connection,$query);
					foreach ($query_run as $row) {
						?>
						<form action="nadadoras_code.php" method="POST">
							<input type="hidden" name="edit_id" value="<?php echo $row['id']?>">
							<div class="form-group">
								<div class="row">
							<div class="col-12 col-sm-6">
								<label for="edit_apellidos">Apellidos</label><input type="text" class="form-control" name="edit_apellidos" value="<?php echo $row['apellidos']?>"placeholder="Apellidos">
							</div>
							<div class="col-12 col-sm-6">
								<label for="edit_nombre">Nombre</label><input type="text" class="form-control" name="edit_nombre" value="<?php echo $row['nombre']?>"placeholder="Nombre">
							</div>
							</div>
							</div>
							<div class="form-group">
								<div class="row">
									<div class="col col-12 col-sm-4">
										<label for="edit_licencia">Licencia</label><input type="text" class="form-control" name="edit_licencia" value="<?php echo $row['licencia']?>" placeholder="Número de licencia, NIF para nadadoras sin licencia federativa">

									</div>
									<div class="col col-12 col-sm-4">
<!--										<label for="edit_fecha_nacimiento">Año</label><input type="text" class="form-control" name="edit_fecha_nacimiento" value="<?php //echo $row['año_nacimiento']?>" placeholder="AAAA">-->
                                <?php
                            $S_SESSION['año_nacimiento'] = $row['año_nacimiento'];
								include('./includes/año_select_option.php');
								?>
									</div>
									<div class="col col-12 col-sm-4">
								<?php
								include('./includes/club_select_option.php');
								?>
							</div>
								</div>
							</div>
							
							<a href="nadadoras.php" class="btn btn-danger"> Cancelar </a>
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
