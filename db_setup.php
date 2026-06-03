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

		<!-- Begin Page Content -->
		<div class="container-fluid">

			<!-- Page Heading -->
			<div class="d-sm-flex align-items-center justify-content-between mb-4">
				<h1 class="h3 mb-0 text-gray-800"><i class="fas fa-database text-primary"></i> Gestión de Base de Datos</h1>
			</div>

			<!-- Mensajes de Estado -->
			<?php
        <?php include('includes/alertas_v4.php'); ?>
							</div>
							<form action="db_code.php" method="POST">
								<div class="custom-control custom-switch mb-3">
									<input type="checkbox" class="custom-control-input" id="show_errors" name="show_errors" value="1" <?php echo (defined('DEBUG_MODE') && DEBUG_MODE) ? 'checked' : ''; ?>>
									<label class="custom-control-label font-weight-bold" for="show_errors">Mostrar errores detallados</label>
									<small class="d-block text-muted">Actívalo solo durante el desarrollo para ver errores de PHP.</small>
								</div>
								<button type="submit" name="save_debug_settings" class="btn btn-info btn-sm btn-block">
									<i class="fas fa-sync-alt"></i> Guardar Ajustes
								</button>
							</form>
							<hr>
							<form action="db_code.php" method="POST">
								<button type="submit" name="test_email" class="btn btn-outline-info btn-sm btn-block">
									<i class="fas fa-paper-plane"></i> Enviar Email de Prueba
								</button>
								<small class="text-muted d-block text-center mt-1">Verifica la configuración SMTP</small>
							</form>
						</div>
					</div>

				</div>

			</div>

			<div class="row">
				<div class="col-12">
					<div class="card shadow mb-4">
						<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
							<h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-list-alt"></i> Visor de Log del Sistema</h6>
							<form action="db_code.php" method="POST" onsubmit="return confirm('¿Vaciar todo el historial de log?');">
								<button type="submit" name="clear_log" class="btn btn-sm btn-outline-danger border-0">
									<i class="fas fa-trash-alt"></i> Vaciar Log
								</button>
							</form>
						</div>
						<div class="card-body">
							<div class="bg-dark p-3 rounded" style="max-height: 300px; overflow-y: auto;">
								<code class="small" style="color: #00ff00;">
									<?php
									$log_file = './log/log.txt';
									if (file_exists($log_file)) {
										$lines = file($log_file);
										if (!empty($lines)) {
											$recent_lines = array_reverse($lines);
											foreach (array_slice($recent_lines, 0, 50) as $line) {
												echo htmlspecialchars($line) . "<br>";
											}
										} else {
											echo "El archivo de log está vacío.";
										}
									} else {
										echo "No se encontró el archivo log/log.txt";
									}
									?>
								</code>
							</div>
							<small class="text-muted mt-2 d-block">Mostrando las últimas 50 entradas (lo más reciente arriba).</small>
						</div>
					</div>
				</div>
			</div>

		</div>
		<!-- /.container-fluid -->

	</div>
	<!-- End of Main Content -->

	<?php
	include('includes/scripts.php');
	include('includes/footer.php');
	?>
</div>
<!-- End of Content Wrapper -->
