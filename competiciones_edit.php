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
				<h4 class="mb-0 font-weight-bold text-primary">Editar Competición</h4>
			</div>

			<div class="card-body">
				<div class="card-title text-primary text-center">Detalles de la Competición</div>
				<?php
//Editar competición
				if(isset($_POST['edit_btn'])){
					$id = $_POST['edit_id'];
					$query = "SELECT * from competiciones WHERE id = '$id'";
					$query_run = mysqli_query($connection,$query);
					foreach ($query_run as $row) {
						?>
						<form action="competiciones_code.php" method="POST">
							<div class="row">
								<div class="form-group col-6">
									<label for="edit_nombre">Nombre</label><input type="text" class="form-control" name="edit_nombre" value="<?php echo $row['nombre']?>"placeholder="Nombre">
								</div>
								<div class="form-group col-6">
									<input type="hidden" name="edit_id" value="<?php echo $row['id']?>">
									<label for="edit_licencia">Lugar</label><input type="text" class="form-control" name="edit_lugar" value="<?php echo $row['lugar']?>" placeholder="Municipio de la competición">
								</div>
							</div>
							<div class="row">
								<div class="form-group col-3">
									<label for="edit_piscina">Piscina</label><input type="text" class="form-control" name="edit_piscina" value="<?php echo $row['piscina']?>"placeholder="Piscina">
								</div>
								<div class="form-group col-3">
									<label for="edit_fecha">Fecha</label><input type="text" class="form-control" name="edit_fecha" value="<?php echo $row['fecha']?>"placeholder="Fecha">
								</div>

								<div class="form-group col-3">
									<label for="edit_hora_inicio">Hora inicio</label><input type="text" class="form-control" name="edit_hora_inicio" value="<?php echo $row['hora_inicio']?>" placeholder="HH:MM">
								</div>
								<div class="form-group col-3">
									<label for="edit_hora_fin">Hora fin</label><input type="text" class="form-control" name="edit_hora_fin" value="<?php echo $row['hora_fin']?>" placeholder="HH:MM">
								</div>
							</div>
							<hr>
							<div class="card-title text-primary text-center">Datos de la jornada</div>
							<div class="row">
								<div class="form-group col-4">
									<label for="edit_temporada">Temporada</label><input type="text" class="form-control" name="edit_temporada" value="<?php echo $row['temporada']?>" placeholder="Temporada">
								</div>
								<?php 
								if($row['no_federado'] == 'si'){
									$check1 = 'checked';

								}
								if($row['figuras'] == 'si'){
									$check2 = 'checked';

								}
								?>
								<label class="col-4"><input name="edit_no_federado" type="checkbox" value="si" <?php echo $check1; ?>> Competición No Federada</label>
								<label class="col-4"><input name="edit_figuras" type="checkbox" value="si" <?php echo $check2; ?>> Competición de figuras</label>

							</div>
							<div class="row">

								<div class="form-group col-4">
									<label for="edit_clave_liga">Clave de la Liga</label><input type="text" class="form-control" name="edit_clave_liga" value="<?php echo $row['clave_liga']?>"placeholder="Clave para identificar la liga">
								</div>
								<div class="form-group col-4">
									<label for="edit_nombre_corto">Nombre corto</label><input type="text" class="form-control" name="edit_nombre_corto" value="<?php echo $row['nombre_corto']?>"placeholder="Nombre corto">
								</div>
								<div class="form-group col-4">
									<label for="edit_color">Color</label><input type="text" class="form-control" name="edit_color" value="<?php echo $row['color']?>" placeholder="#color" style="background-color:<?php echo $row['color']?>">
								</div>
							</div>
							<hr>
							<hr>
							<div class="card-title text-primary text-center">Configuración de informes</div>
							<div class="row">
								<div class="form-group col-5">
									<label for="edit_header_informe">Imagen cabecera informes</label><input type="text" class="form-control" name="edit_header_informe" value="<?php echo $row['header_informe']?>" placeholder="Ruta al archivo">
									<img src="<?php echo $row['header_informe']?>" alt="La imagen no existe" class="img-thumbnail">
								</div>							
								<div class="form-group col-5">
									<label for="edit_footer_informe">Imagen pie informes</label><input type="text" class="form-control" name="edit_footer_informe" value="<?php echo $row['footer_informe']?>"placeholder="Ruta al archivo">
									<img src="<?php echo $row['footer_informe']?>" alt="La imagen no existe" class="img-thumbnail">
								</div>
								<div class="form-group col-2">
									<label for="edit_mascara_licencia">Mascara licencia</label><input type="text" class="form-control" name="edit_mascara_licencia" value="<?php echo $row['mascara_licencia']?>" placeholder="">
								</div>
							</div>

						</div>
						<a href="competiciones.php" class="btn btn-danger"> Cancelar </a>
						<button type="submit" name="update_btn" class="btn btn-primary">Actualizar</button>
					</form>
				</div>
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