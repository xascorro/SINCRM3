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
			<?php
//Editar elemento
				if(isset($_POST['edit_btn'])){
					$id_rutina = $_POST['edit_id_rutina'];
					$id_fase = $_POST['edit_id_fase'];
                    @$id_elemento = $_POST['edit_id_elemento'];
                    $elemento = $_POST['edit_elemento'];
				?>
				<h4 class="mb-0 font-weight-bold text-primary">Editar Elemento <?php echo $elemento ;?> Coach Card</h4>
			</div>

			<div class="card-body">

						<form action="coach_card_composer_code.php" method="POST">
							<div class="row">
							<?php
                    $query = "SELECT * from hibridos_rutina WHERE id_rutina = '$id_rutina' and tipo='time_inicio' and elemento = $elemento";
                    $query_run = mysqli_query($connection,$query);
                    $elementos = mysqli_fetch_assoc($query_run);
                    ?>
								<div class="form-group col-2">
									<input type="hidden" name="id_rutina" value="<?php echo $row['id_rutina']?>">
									<label for="edit_time">Inicio</label>
									<input type="time" class="form-control" name="time_inicio" value="<?php echo $elementos['texto']?>" placeholder="00:00">
								</div>
							<?php
                    $query = "SELECT * from hibridos_rutina WHERE id_rutina = '$id_rutina' and tipo='time_fin' and elemento = $elemento";
                    $query_run = mysqli_query($connection,$query);
                    $elementos = mysqli_fetch_assoc($query_run);
                    ?>
								<div class="form-group col-2">
									<input type="hidden" name="id_rutina" value="<?php echo $row['id_rutina']?>">
									<label for="edit_time">Fin</label>
									<input type="time" class="form-control" name="time_fin" value="<?php echo $elementos['texto']?>" placeholder="00:00">
								</div>
								<div class="col">
									<?php
									include('includes/tipo_hibridos_select_option.php');
									?>
								</div>

								<div class="col">
                                    <?php
								    $tipo_elemento='Basemark';
									echo "<label for=edit_basemark>$tipo_elemento</label>";
                                    $query = "SELECT texto from hibridos_rutina WHERE id_rutina = '$id_rutina' and tipo='$tipo_elemento' and elemento = $elemento";
                                    $query_run = mysqli_query($connection,$query);
                                    if(mysqli_num_rows($query_run) > 0){
                                        $x=0;
                                        while ($row = mysqli_fetch_array($query_run)) {
                                            $texto = $row['texto'];
                                            include('includes/dificultad_hibridos_select_option.php');
                                            $x++;
                                        }
                                    }

                                    for($x=$x+1; $x<=2; $x++){
                                        $texto='';
                                        include('includes/dificultad_hibridos_select_option.php');
                                    }
									?>
								</div>
								<div class="col-2">
									<label for="edit_dd">DD</label>
									<?php
                                    $tipo_elemento='dd';
                                    $query = "SELECT texto from hibridos_rutina WHERE id_rutina = '$id_rutina' and tipo='$tipo_elemento' and elemento = $elemento";
                                    $query_run = mysqli_query($connection,$query);
                                    if(mysqli_num_rows($query_run) > 0){
                                        $x=0;
                                        while ($row = mysqli_fetch_array($query_run)) {
                                            $texto = $row['texto'];
                                            include('includes/dificultad_hibridos_select_option.php');
                                            $x++;
                                        }
                                    }
                                    for($x=$x; $x<6; $x++){
                                        $texto='';
                                        include('includes/dificultad_hibridos_select_option.php');
                                    }
									?>
								</div>
								<div class="col-2">
									<label for="edit_bonus">Bonus</label>
									<?php
                                    $tipo_elemento='bonus';
                                    $query = "SELECT texto from hibridos_rutina WHERE id_rutina = '$id_rutina' and tipo='$tipo_elemento' and elemento = $elemento";
                                    $query_run = mysqli_query($connection,$query);
                                    if(mysqli_num_rows($query_run) > 0){
                                        $x=0;
                                        while ($row = mysqli_fetch_array($query_run)) {
                                            $texto = $row['texto'];
                                            include('includes/dificultad_hibridos_select_option.php');
                                            $x++;
                                        }
                                    }
                                    for($x; $x<2; $x++){
                                        $texto='';
                                        include('includes/dificultad_hibridos_select_option.php');
                                    }
									?>
								</div>
							</div>
							<input type="hidden" name="id_rutina" value="<?php echo $id_rutina;?>"/>
							<input type="hidden" name="id_fase" value="<?php echo $id_fase;?>"/>
							<input type="hidden" name="elemento" value="<?php echo $elemento;?>"/>
							<a href="coach_card_composer.php?id_rutina=<?php echo $id_rutina;?>&id_fase=<?php echo $id_fase;?>" class="btn btn-danger"> Cancelar </a>
							<button type="submit" name="update_btn" class="btn btn-primary">Actualizar</button>
						</div>
					</div>

				</form>
				<?php

		}
		?>




	</div>


	<!-- template -->
	<?php
	include('includes/scripts.php');
	include('includes/footer.php');
	?>
