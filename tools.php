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
			<div class="d-sm align-items-center mb-12 border-bottom-primary">
				<h2 class="font-weight-bold text-primary text-center"><i class="fa-solid fa-screwdriver-wrench"></i> Herramientas</h2>

			</div>


			<div class="card-body">
				<div class="d-sm-flex align-items-center justify-content-between mb-12">
					<a target="_blank" href="https://sincrm.pedrodiaz.eu/login.php?logout_btn=yes">
						<h4 class="font-weight-bold text-gray-900"><i class="fa-solid fa-arrow-right-from-bracket"></i> Forzar logout y limpiar cookies</h4>
					</a>
				</div>
				<div class="d-sm-flex align-items-center justify-content-between mb-12">
					<a target="_blank" href="https://sincrm.pedrodiaz.eu/log.php">
						<h4 class="font-weight-bold text-gray-900"><i class="fa-regular fa-file-lines"></i> Log</h4>
					</a>
				</div>

				<hr class="sidebar-divider">



			</div>
			<hr class="sidebar-divider">




			<!-- template -->
			<?php
            include('includes/scripts.php');
            include('includes/footer.php');
			?>
