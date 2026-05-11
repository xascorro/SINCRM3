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
			if (isset($_SESSION['correcto']) && $_SESSION['correcto'] != '') {
				echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
						<strong><i class="fas fa-check-circle"></i></strong> ' . $_SESSION['correcto'] . '
						<button type="button" class="close" data-dismiss="alert" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					  </div>';
				unset($_SESSION['correcto']);
			}
			if (isset($_SESSION['error']) && $_SESSION['error'] != '') {
				echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
						<strong><i class="fas fa-exclamation-circle"></i></strong> ' . $_SESSION['error'] . '
						<button type="button" class="close" data-dismiss="alert" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					  </div>';
				unset($_SESSION['error']);
			}
			?>

			<!-- Advertencia Crítica -->
			<div class="card shadow mb-4 border-left-danger">
				<div class="card-body">
					<div class="row no-gutters align-items-center">
						<div class="col mr-2">
							<div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Zona de Peligro</div>
							<div class="h5 mb-0 font-weight-bold text-gray-800">Cuidado: Estás manipulando la configuración del núcleo del sistema.</div>
						</div>
						<div class="col-auto">
							<i class="fas fa-bomb fa-2x text-danger"></i>
						</div>
					</div>
				</div>
			</div>

			<!-- Salud del Sistema -->
			<div class="row mb-4">
				<!-- Card Almacenamiento -->
				<div class="col-xl-3 col-md-6 mb-4">
					<div class="card border-left-info shadow h-100 py-2">
						<div class="card-body">
							<div class="row no-gutters align-items-center">
								<div class="col mr-2">
									<div class="text-xs font-weight-bold text-info text-uppercase mb-1">Backups en Disco</div>
									<?php
									$backup_size = 0;
									$dir = './database/backup/';
									if (is_dir($dir)) {
										foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir)) as $file) {
											if ($file->isFile()) $backup_size += $file->getSize();
										}
									}
									?>
									<div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo round($backup_size / 1024 / 1024, 2); ?> MB</div>
								</div>
								<div class="col-auto">
									<i class="fas fa-hdd fa-2x text-gray-300"></i>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Card Software -->
				<div class="col-xl-3 col-md-6 mb-4">
					<div class="card border-left-primary shadow h-100 py-2">
						<div class="card-body">
							<div class="row no-gutters align-items-center">
								<div class="col mr-2">
									<div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Software</div>
									<div class="small font-weight-bold text-gray-800">PHP: <?php echo phpversion(); ?></div>
									<div class="small font-weight-bold text-gray-800">MySQL: <?php echo mysqli_get_server_info($connection); ?></div>
								</div>
								<div class="col-auto">
									<i class="fas fa-microchip fa-2x text-gray-300"></i>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Card Tablas -->
				<div class="col-xl-6 col-md-12 mb-4">
					<div class="card border-left-success shadow h-100 py-2">
						<div class="card-body">
							<div class="row no-gutters align-items-center">
								<div class="col mr-2">
									<div class="text-xs font-weight-bold text-success text-uppercase mb-1">Salud de la Base de Datos</div>
									<div class="row no-gutters align-items-center">
										<div class="col-auto mr-3">
											<?php
											$res = mysqli_query($connection, "SELECT count(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$db_name'");
											$num_tables = mysqli_fetch_row($res)[0];
											?>
											<div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $num_tables; ?> Tablas</div>
										</div>
										<div class="col">
											<form action="db_code.php" method="POST">
												<button type="submit" name="optimize_db" class="btn btn-sm btn-outline-success border-0 font-weight-bold">
													<i class="fas fa-magic"></i> Optimizar ahora
												</button>
											</form>
										</div>
									</div>
								</div>
								<div class="col-auto">
									<i class="fas fa-heartbeat fa-2x text-gray-300"></i>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="row">

				<!-- Columna Izquierda: Configuración -->
				<div class="col-xl-6 col-lg-6">
					
					<!-- Conexión a la Base de Datos -->
					<div class="card shadow mb-4">
						<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
							<h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-plug"></i> Configuración de Conexión</h6>
						</div>
						<div class="card-body">
							<form action="db_code.php" method="POST">
								<div class="form-group">
									<label class="small font-weight-bold">Servidor</label>
									<div class="input-group">
										<div class="input-group-prepend">
											<span class="input-group-text"><i class="fas fa-server"></i></span>
										</div>
										<input type="text" class="form-control" name="servername" value="<?php echo $servername ?>" placeholder="Ej: localhost">
									</div>
								</div>
								<div class="form-group">
									<label class="small font-weight-bold">Nombre Base de Datos</label>
									<div class="input-group">
										<div class="input-group-prepend">
											<span class="input-group-text"><i class="fas fa-hdd"></i></span>
										</div>
										<input type="text" class="form-control" name="db_name" value="<?php echo $db_name ?>" placeholder="Ej: sincrm4">
									</div>
								</div>
								<div class="row">
									<div class="form-group col-md-6">
										<label class="small font-weight-bold">Usuario</label>
										<input type="text" class="form-control" name="db_username" value="<?php echo $db_username ?>" placeholder="Usuario">
									</div>
									<div class="form-group col-md-6">
										<label class="small font-weight-bold">Contraseña</label>
										<input type="password" class="form-control" name="db_password" value="<?php echo $db_password ?>" placeholder="Contraseña">
									</div>
								</div>
								<div class="custom-control custom-checkbox mb-3">
									<input type="checkbox" class="custom-control-input" id="accept" name="accept" value="1">
									<label class="custom-control-label text-danger font-weight-bold" for="accept">Confirmar sobreescritura del archivo de configuración</label>
								</div>
								<div class="text-right">
									<a href="index.php" class="btn btn-secondary btn-icon-split">
										<span class="icon text-white-50"><i class="fas fa-arrow-left"></i></span>
										<span class="text">Cancelar</span>
									</a>
									<button type="submit" name="update_btn" class="btn btn-primary btn-icon-split">
										<span class="icon text-white-50"><i class="fas fa-save"></i></span>
										<span class="text">Actualizar</span>
									</button>
								</div>
							</form>
						</div>
					</div>

					<!-- Realizar Backup -->
					<div class="card shadow mb-4">
						<div class="card-header py-3">
							<h6 class="m-0 font-weight-bold text-success"><i class="fas fa-download"></i> Crear Nuevo Backup</h6>
						</div>
						<div class="card-body">
							<form action="db_code.php" method="POST">
								<div class="form-group">
									<label class="small font-weight-bold">Descripción / Nombre del archivo</label>
									<input type="text" class="form-control" name="descripcion" placeholder="Ej: Backup antes de importar nadadoras">
								</div>
								<button type="submit" name="backup_btn" class="btn btn-success btn-block">
									<i class="fas fa-file-export"></i> Iniciar Exportación
								</button>
							</form>
						</div>
					</div>

				</div>

				<!-- Columna Derecha: Backups Existentes -->
				<div class="col-xl-6 col-lg-6">
					
					<div class="card shadow mb-4">
						<div class="card-header py-3">
							<h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-history"></i> Historial de Backups</h6>
						</div>
						<div class="card-body">
							<div class="table-responsive">
								<table class="table table-hover table-sm" width="100%" cellspacing="0">
									<thead class="thead-light">
										<tr>
											<th>Archivo</th>
											<th>Fecha</th>
											<th class="text-center">Acciones</th>
										</tr>
									</thead>
									<tbody class="small">
										<?php
										$dir = './database/backup/';
										if (is_dir($dir)) {
											$files = scandir($dir, SCANDIR_SORT_DESCENDING);
											foreach ($files as $file) {
												if ($file != "." && $file != ".." && (str_ends_with($file, '.sql.gz') || str_ends_with($file, '.sql'))) {
													$filepath = $dir . $file;
													$filesize = round(filesize($filepath) / 1024, 2) . ' KB';
													$filedate = date("d/m/y H:i", filemtime($filepath));
										?>
													<tr>
														<td class="align-middle font-weight-bold text-dark">
															<?php echo $file; ?><br>
															<span class="badge badge-light"><?php echo $filesize; ?></span>
														</td>
														<td class="align-middle"><?php echo $filedate; ?></td>
														<td class="text-center align-middle">
															<div class="btn-group shadow-sm">
																<a href="<?php echo $filepath; ?>" class="btn btn-light btn-sm text-success" download title="Descargar"><i class="fas fa-download"></i></a>
																
																<form action="db_code.php" method="POST" style="display:inline;">
																	<input type="hidden" name="backup_file" value="<?php echo $file; ?>">
																	<button type="submit" name="email_backup" class="btn btn-light btn-sm text-info" title="Enviar por Email"><i class="fas fa-envelope"></i></button>
																</form>

																<form action="db_code.php" method="POST" style="display:inline;" onsubmit="return confirm('¿Restaurar? Esta acción no se puede deshacer.');">
																	<input type="hidden" name="backup_file" value="<?php echo $file; ?>">
																	<button type="submit" name="restore_backup" class="btn btn-light btn-sm text-warning" title="Restaurar"><i class="fas fa-undo"></i></button>
																</form>

																<form action="db_code.php" method="POST" style="display:inline;" onsubmit="return confirm('¿Borrar archivo?');">
																	<input type="hidden" name="backup_file" value="<?php echo $file; ?>">
																	<button type="submit" name="delete_backup" class="btn btn-light btn-sm text-danger" title="Borrar"><i class="fas fa-trash"></i></button>
																</form>
															</div>
														</td>
													</tr>
										<?php
												}
											}
										}
										?>
									</tbody>
								</table>
							</div>
						</div>
					</div>

					<!-- Ajustes de Errores -->
					<div class="card shadow mb-4">
						<div class="card-header py-3">
							<h6 class="m-0 font-weight-bold text-info"><i class="fas fa-bug"></i> Depuración</h6>
						</div>
						<div class="card-body">
							<div class="mb-3 text-center">
								<span class="small font-weight-bold d-block mb-1 text-uppercase text-muted">Estado del Sistema</span>
								<?php if (defined('DEBUG_MODE') && DEBUG_MODE): ?>
									<div class="h5 mb-0 font-weight-bold text-danger"><i class="fas fa-exclamation-triangle"></i> DEBUG ON</div>
								<?php else: ?>
									<div class="h5 mb-0 font-weight-bold text-success"><i class="fas fa-check-circle"></i> PRODUCTION ON</div>
								<?php endif; ?>
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
