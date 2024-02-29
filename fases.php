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


		<!-- Modal -->
		<div class="modal fade" id="addUserProfile" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="exampleModalLabel">Añadir fase</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<form action="fases_code.php" method="POST" enctype="multipart/form-data">
						<div class="modal-body">
							<div class="row">
								<div class="form-group col">
									<?php
                    if($_SESSION['competicion_figuras'] == 'si'){?>
									<div class="col">
										<?php include('includes/categoria_select_option.php');?>
									</div>
									<?php include('includes/figura_select_option.php');
					}else{
                        include('includes/modalidad_select_option.php');?>
									<div class="col">
										<?php include('includes/categoria_select_option.php');?>
									</div>
									<?php
									}
									?>
								</div>

							</div>
							<div class="row">
								<div class="form-group col-4">
									<?php
                  $query = "SELECT max(orden)+1 as nuevo_orden FROM fases WHERE id_competicion = '".$_SESSION['id_competicion_activa']."'";
                  $query_run = mysqli_query($connection,$query); 
                  $row = mysqli_fetch_assoc($query_run);


                  ?>
									<label for="orden">Orden</label>
									<input type="number" class="form-control" name="orden" value="<?php echo $row['nuevo_orden']; ?>">
								</div>
								<div class="col-4">
									<label for="orden">Factor ChoMu</label>
									<input type="number" step="0.1" value="1" class="form-control" name="f_chomu">
								</div>
								<div class="col-4">
									<label for="orden">Elementos CC</label>
									<input type="number" step="1" value="0" class="form-control" name="elementos_coach_card">
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
								<button type="submit" class="btn btn-primary" name="save_btn">Guardar</button>
							</div>
						</div>
					</form>


				</div>
			</div>
		</div>
		<!-- Final Modal -->



		<!-- Begin Page Content -->
		<div class="container-fluid">

			<!-- Titulo página y pdf -->
			<div class="d-sm-flex align-items-center justify-content-between mb-4">
				<h4 class="mb-0 font-weight-bold text-primary"><i class="fas fa-fw fa-flag-checkered"></i>Registro de fases
					<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addUserProfile">Añadir fase</button>
				</h4>
			</div>

			<div class="card-body">

				<?php
          if(isset($_SESSION['correcto']) && $_SESSION['correcto'] != ''){
            echo '<div class="alert alert-primary" role="alert">'.$_SESSION['correcto'].'</div>';
            unset($_SESSION['correcto']);
          }
          if(isset($_SESSION['estado']) && $_SESSION['estado'] != ''){
            echo '<div class="alert alert-danger" role="alert">'.$_SESSION['estado'].'</div>';
            unset($_SESSION['estado']);
          }
          ?>

				<div class="table-responsive">
					<?php
            if($_SESSION['figuras']=='si'){
                $competicion_figuras ='si';
                $query = "SELECT fases.id, id_categoria, categorias.nombre as nombre_categoria, edad_minima, edad_maxima, id_figura, figuras.nombre as nombre_figura, numero, orden FROM fases, categorias, figuras WHERE fases.id_categoria = categorias.id and fases.id_figura = figuras.id and fases.id_competicion = ".$_SESSION['id_competicion_activa']." ORDER BY orden, fases.id";
            }
            else
                $query = "SELECT fases.id, fases.elementos_coach_card, fases.f_chomu, id_categoria, categorias.nombre as nombre_categoria, edad_minima, edad_maxima, id_modalidad, modalidades.nombre as nombre, orden FROM fases, categorias, modalidades WHERE fases.id_categoria = categorias.id and fases.id_modalidad = modalidades.id and fases.id_competicion = ".$_SESSION['id_competicion_activa']." ORDER BY orden, fases.id";

            $query_run = mysqli_query($connection,$query); 
            ?>
					<table class="table table-striped table-sm" id="dataTable" width="100%" cellspacing="0">
						<thead>
							<tr>
								<th scope="col">#</th>
								<th scope="col">Orden</th>
								<th scope="col">Modalidad</th>

								<?php
                    if(@$competicion_figuras == 'si')
                        echo '<th scope="col">Figura</th>';
                    else{
                        echo '<th scope="col">Categoría</th>';
                        echo '<th scope="col">Elementos CC</th>';
                        echo '<th scope="col">FC</th>';
                    }
                    ?>
								<th scope="col" colspan="2" class="text-center">Acciones</th>
							</tr>
						</thead>
						<tbody>
							<?php
                if(mysqli_num_rows($query_run) > 0){
                  while ($row = mysqli_fetch_assoc($query_run)) {
                    ?>
							<tr>
								<th scope="row"> <?php echo $row['id']; ?> </th>
								<td> <?php echo $row['orden']; ?> </td>
								<td> <?php echo @$row['nombre']; ?> </td>
								<td> <?php echo $row['nombre_categoria']; ?> </td>
								<td> <?php echo @$row['elementos_coach_card']; ?> </td>
								<td> <?php echo @$row['f_chomu']; ?> </td>
								<?php
					  			if(@$competicion_figuras == 'si'){
								?>
									<td> <?php echo @$row['numero']." - ".@$row['nombre_figura']; ?> </td>
								<?php
								}
								?>
								<td class="text-center">
									<form action="fases_edit.php" method="post">
										<input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
										<input type="hidden" name="id_figura" value="<?php echo @$row['id_figura']; ?>">
										<input type="hidden" name="id_modalidad" value="<?php echo @$row['id_modalidad']; ?>">
										<input type="hidden" name="id_categoria" value="<?php echo $row['id_categoria']; ?>">
										<input type="hidden" name="edad_minima" value="<?php echo $row['edad_minima']; ?>">
										<input type="hidden" name="edad_maxima" value="<?php echo $row['edad_maxima']; ?>">
										<input type="hidden" name="elementos_coach_card" value="<?php echo @$row['elementos_coach_card']; ?>">
										<input type="hidden" name="f_chomu" value="<?php echo @$row['f_chomu']; ?>">
										<button class="btn btn-success" type="submit" name="edit_btn"><i class="fas fa-edit"></i></btn>
									</form>
								</td>
								<td class="text-center">
									<form action="fases_code.php" method="POST">
										<input type="hidden" name="delete_id" value="<?php echo $row['id'];?>">
										<button class="btn btn-danger" type="submit" name="delete_btn"><i class="fas fa-trash"></i></btn>
									</form>
								</td>
							</tr>
							<?php
                      }
                    }
                    else{
                      echo "<tr><td colspan='10'>No se han encontrado registros en la base de datos</td></tr>";
                    }
                    ?>
						</tbody>
					</table>
				</div>
			</div>


			<!-- template -->
			<?php
            include('includes/scripts.php');
            include('includes/footer.php');
            ?>
