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
				<h2 class="font-weight-bold text-primary text-center"><i class="fas fa-fw fa-bomb"></i> Informes Rutinas</h2>

			</div>


			<div class="card-body">
				<div class="d-sm-flex align-items-center justify-content-between mb-12">
					<a target="_blank" href="informes/inscripciones_numericas_rutinas.php?titulo=Rutinas">
						<h4 class="font-weight-bold text-gray-900"><i class="fa-solid fa-users-line"></i> Inscripciones númericas de Rútinas</h4>
					</a>
				</div>

				<hr class="sidebar-divider">

				<div class="d-sm-flex align-items-center justify-content-between mb-12">
					<a target="_blank" href="./informes/inscripciones_numericas_rutinas.php?titulo=Orden%20de%20salida">
						<h4 class="font-weight-bold text-gray-900"><i class="fa-solid fa-list-ol"></i> Orden de salida</h4>
					</a>
				</div>
				<div class="d-sm-flex align-items-center justify-content-between mb-12">
					<a target="_blank" href="informes/hojas_puntuacion_rutinas.php">
						<h4 class="font-weight-bold text-gray-900"><i class="fas fa-fw fa-file-pdf"></i> Hojas puntuación Ejecución</h4>
					</a>
				</div>
				<div class="d-sm-flex align-items-center justify-content-between mb-12">
					<a target="_blank" href="informes/hojas_puntuacion_rutinas_sincronizacion.php">
						<h4 class="font-weight-bold text-gray-900"><i class="fas fa-fw fa-file-pdf"></i> Hojas puntuación Sincronización</h4>
					</a>
				</div>
				<div class="d-sm-flex align-items-center justify-content-between mb-12">
					<a target="_blank" href="informes/hojas_puntuacion_rutinas_ia.php">
						<h4 class="font-weight-bold text-gray-900"><i class="fas fa-fw fa-file-pdf"></i> Hojas puntuación IA</h4>
					</a>
				</div>
				<hr class="sidebar-divider">
				<div class="d-sm-flex align-items-center justify-content-between mb-12">
					<a target="_blank" href="./informes/informe_puntuaciones.php?titulo=Árbitros&hoja_tecnica=si&id_fase=0">
						<h4 class="font-weight-bold text-gray-900"><i class="fa-solid fa-gavel"></i> Árbitros</h4>
					</a>
				</div>
				<div class="d-sm-flex align-items-center justify-content-between mb-12">
					<a target="_blank" href="./informes/informe_puntuaciones.php?titulo=Clasificación%20por%20Clubes&memorial=si&id_fase=0">
						<h4 class="font-weight-bold text-gray-900"><i class="fa-solid fa-trophy"></i> Clasificación por Clubes</h4>
					</a>
				</div>
				<div class="d-sm-flex align-items-center justify-content-between mb-12">
					<a target="_blank" href="./informes/informe_puntuaciones.php?titulo=Clasificación%20detallada&hoja_tecnica=si&memorial=si">
						<h4 class="font-weight-bold text-gray-900"><i class="fa-solid fa-medal"></i> Resultados completos</h4>
					</a>
				</div>
			</div>

			<div class="d-sm align-items-center mb-12 border-bottom-primary">
				<h2 class="font-weight-bold text-primary text-center"><i class="fab fa-xing"></i> Informes Figuras</h2>

			</div>
			<div class="card-body">

				<div class="d-sm-flex align-items-center justify-content-between mb-12">
					<a target="_blank" href="informes/informe_figuras_preinscripciones.php?titulo=Inscripciones">
						<h4 class="font-weight-bold text-gray-900"><i class="fas fa-fw fa-file-pdf"></i> Inscripciones Figuras</h4>
					</a>
				</div>
				<div class="d-sm-flex align-items-center justify-content-between mb-12">
					<a target="_blank" href="./informes/informe_figuras_orden_salida_cortes.php?titulo=Orden%20de%20salida">
						<h4 class="font-weight-bold text-gray-900"><i class="fas fa-fw fa-file-pdf"></i> Orden de salida</h4>
					</a>
				</div>
				<div class="d-sm-flex align-items-center justify-content-between mb-12">
					<a target="_blank" href="informes/informe_figuras_notas_anotadores.php?titulo=Anotadores">
						<h4 class="font-weight-bold text-gray-900"><i class="fas fa-fw fa-file-pdf"></i> Hojas anotadores</h4>
					</a>
				</div>
				<div class="d-sm-flex align-items-center justify-content-between mb-12">
					<a target="_blank" href="informes/informe_figuras_notas_figuras_junior.php?titulo=Notas%20Rutina%20Técnica">
						<h4 class="font-weight-bold text-gray-900"><i class="fas fa-fw fa-file-pdf"></i> Hojas puntuación Rutina Técnica</h4>
					</a>
				</div>
				<hr class="sidebar-divider">
				<div class="d-sm align-items-center mb-12 ">
					<h4 class="font-weight-bold text-primary text-center"><i class="fas fa-fw fa-file-pdf"></i> Informes Figuras por año</h4>
				</div>
				<div class="d-sm-flex align-items-center justify-content-between mb-12">
					<a target="_blank" href="./puntuaciones_fases_figuras_puntuar.php">
						<h4 class="font-weight-bold text-gray-900"><i class="fa-solid fa-calculator"></i> Calcular puntuaciones Competición de Figuras (año)</h4>
					</a>
				</div>
				<div class="d-sm-flex align-items-center justify-content-between mb-12">
					<a target="_blank" href="./informes/informe_figuras_resultados.php?titulo=Resultados&hoja_tecnica=si">
						<h4 class="font-weight-bold text-gray-900"><i class="fas fa-fw fa-file-pdf"></i> Resultados Competición de Figuras</h4>
					</a>
				</div>
				<div class="d-sm-flex align-items-center justify-content-between mb-12">
					<a target="_blank" href="./informes/informe_figuras_liga.php">
						<h4 class="font-weight-bold text-gray-900"><i class="fas fa-fw fa-file-pdf"></i> Resultados Liga de Figuras</h4>
					</a>
				</div>
				<hr class="sidebar-divider">
				<div class="d-sm align-items-center mb-12 ">
					<h4 class="font-weight-bold text-primary text-center"><i class="fas fa-fw fa-file-pdf"></i> Informes Figuras por categoría</h4>
				</div>
				<div class="d-sm-flex align-items-center justify-content-between mb-12">
					<a target="_blank" href="./puntuaciones_fases_figuras_puntuar_categorias.php">
						<h4 class="font-weight-bold text-gray-900"><i class="fa-solid fa-calculator"></i> Calcular puntuaciones Competición de Figuras (categoría)</h4>
					</a>
				</div>
				<div class="d-sm-flex align-items-center justify-content-between mb-12">
					<a target="_blank" href="./informes/informe_figuras_resultados_categorias.php?titulo=Resultados por categorias&hoja_tecnica=si">
						<h4 class="font-weight-bold text-gray-900"><i class="fas fa-fw fa-file-pdf"></i> Resultados Competición de Figuras</h4>
					</a>
				</div>
				<div class="d-sm-flex align-items-center justify-content-between mb-12">
					<a target="_blank" href="./informes/informe_figuras_liga_categorias.php">
						<h4 class="font-weight-bold text-gray-900"><i class="fas fa-fw fa-file-pdf"></i> Resultados Liga de Figuras</h4>
					</a>
				</div>
				<div class="d-sm-flex align-items-center justify-content-between mb-12">
					<a target="_blank" href="./informes/informe_figuras_liga_categorias_puntuaciones.php">
						<h4 class="font-weight-bold text-gray-900"><i class="fas fa-fw fa-file-pdf"></i> Resultados Liga de Figuras Puntuaciones</h4>
					</a>
				</div>
			</div>
			<hr class="sidebar-divider">




			<!-- template -->
			<?php
            include('includes/scripts.php');
            include('includes/footer.php');
			?>
