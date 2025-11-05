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
			<div class="d-sm align-items-center mb-12 border-bottom-danger">
				<h2 class="font-weight-bold text-danger text-center"><i class="fas fa-fw fa-bomb"></i> Cuidado, levanta las manos del teclado y piensa.</h2>
				<h4 class="font-weight-bold text-danger text-center">Puedes romper algo si no sabes lo que estas haciendo</h4>
			</div>
			<div class="d-sm-flex align-items-center justify-content-between mb-12">
				<h2 class="font-weight-bold text-primary"><i class="fas fa-exclamation-triangle"></i> Ajustes de Visualización de Errores</h2>
			</div>
			<div class="card-body">
				sin implementar
				<div class="row">

					<form action="db_code.php" method="POST">
						<label for="show_errors">Mostrar Errores:</label>
						<input type="checkbox" id="show_errors" name="show_errors" value="1" <?php echo (isset($_POST['show_errors'])) ? 'checked' : ''; ?>>
						<br>
						<input class="btn btn-primary" type="submit" value="Guardar Ajustes">
					</form>
				</div>
			</div>


			<div class="d-sm-flex align-items-center justify-content-between mb-12">
				<h2 class="font-weight-bold text-primary"><i class="fas fa-fw fa-database"></i> Conexión a la Base de Datos</h2>
			</div>
			<div class="card-body">
				<form action="db_code.php" method="POST">
					<div class="row">
						<div class="form-group col-6">
							<label for="servername">Servidor</label>
							<input type="text" class="form-control" name="servername" value="<?php echo $servername ?>" placeholder="Dirección del servidor">
						</div>
						<div class="form-group col-6">
							<label for="db_name">Base de datos</label>
							<input type="text" class="form-control form-control-user" name="db_name" value="<?php echo $db_name ?>" placeholder="Nombre de la base de datos">
						</div>
					</div>
					<div class="row">
						<div class="form-group col-6">
							<label for="db_username">Usuario</label>
							<input type="text" class="form-control form-control-user" name="db_username" value="<?php echo $db_username ?>" placeholder="Nombre de usuario">
						</div>

						<div class="form-group col-6">
							<label for="db_password">Contraseña</label>
							<input type="password" class="form-control" name="db_password" value="<?php echo $db_password ?>" placeholder="Contraseña">
						</div>

					</div>
					<div class="row">
						<div class="form-group col-6">
							<label for="accept">Sobreescribir</label>
							<input type="checkbox" name="accept" value="1">
						</div>
						<div class="form-group col-6">
							<a href="index.php" class="btn btn-danger"> Cancelar </a>
							<button type="submit" name="update_btn" class="btn btn-primary">Actualizar</button>
						</div>
					</div>
				</form>
			</div>
			<hr class="sidebar-divider">
			<div class="d-sm-flex align-items-center justify-content-between mb-12">
				<h2 class="font-weight-bold text-primary"><i class="fas fa-fw fa-save"></i> Backup de la Base de Datos</h2>
			</div>
			<div class="card-body">
				<form action="db_code.php" method="POST">
					<div class="row">
						<div class="form-group col-6">
							<label for="descripcion">Nombre de archivo</label>
							<input type="text" class="form-control" name="descripcion" placeholder="Descripción del archivo de backup">
						</div>
					</div>
					<div class="row">
						<div class="form-group col-6">
							<a href="index.php" class="btn btn-danger"> Cancelar </a>
							<button type="submit" name="backup_btn" class="btn btn-primary">Backup</button>
						</div>
					</div>
				</form>
			</div>
			<hr class="sidebar-divider">
			<div class="d-sm-flex align-items-center justify-content-between mb-12">
				<h2 class="font-weight-bold text-primary"><i class="fas fa-fw fa-th"></i> Cargar desde archivo</h2>
			</div>
			<div class="card-body">
				<form action="db_code.php" method="POST">
					<div class="row">
						<div class="form-group col-6">
							<label for="">Nombre de archivo</label>
							<input type="text" class="form-control" name="" value="" placeholder="">
						</div>
					</div>
					<div class="row">
						<div class="form-group col-6">
							<a href="index.php" class="btn btn-danger"> Cancelar </a>
							<button type="submit" name="backup_btn" class="btn btn-primary">Cargar</button>
						</div>
					</div>
				</form>
			</div>

			<!-- template -->
			<?php
			include('includes/scripts.php');
			include('includes/footer.php');
			?>
