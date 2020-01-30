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


				<div class="form-group">
					<label for="licencia">Licencia</label>
					<input type="text" class="form-control" name="licencia" placeholder="Número de licencia, NIF para nadadoras sin licencia federativa">
				</div>
				<div class="form-group">
					<label for="apellidos">Apellidos</label>
					<input type="text" class="form-control" name="apellidos" placeholder="Apellidos">
				</div>
				<div class="form-group">
					<label for="nombre">Nombre</label>
					<input type="text" class="form-control" name="nombre" placeholder="Nombre">
				</div>
				<div class="form-group">
					<label for="fechadenacimiento">Fecha de Nacimiento</label>
					<input type="text" class="form-control" name="fechadenacimiento" placeholder="DD-MM-AAAA">
				</div>



			</div>


			<!-- template -->
			<?php
			include('includes/scripts.php');
			include('includes/footer.php');
			?>