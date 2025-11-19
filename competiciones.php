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
						<h5 class="modal-title" id="exampleModalLabel">Añadir competición</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<form action="competiciones_code.php" method="POST">
						<div class="modal-body">
							<div class="form-group">
								<label for="nombre">Nombre</label>
								<input type="text" class="form-control" name="nombre">
							</div>
							<div class="form-group">
								<label for="lugar">Lugar</label>
								<input type="text" class="form-control" name="lugar">
							</div>
							<div class="form-group">
								<label for="fecha">Fecha</label>
								<input type="text" class="form-control" name="fecha">
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
							<button type="submit" class="btn btn-primary" name="save_btn">Guardar</button>
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
				<h4 class="mb-0 font-weight-bold text-primary"><i class="fas fa-fw fa-flag"></i>Registro de Competiciones
					<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addUserProfile">Añadir competición</button>
				</h4>
				<a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i class="fas fa-download fa-sm text-white-50"></i> Generar PDF</a>
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
            $query = "SELECT * FROM competiciones"; 
            $query_run = mysqli_query($connection,$query); 
            ?>
					<table class="table table-striped" id="dataTable" width="100%" cellspacing="0">
						<thead>
							<tr>
								<th scope="col">#</th>
								<th scope="col">Nombre</th>
								<th scope="col">Sede</th>
								<th scope="col">Fecha</th>
								<th scope="col">Activo</th>
								<th scope="col">Editar</th>
								<th scope="col">Borrar</th>
							</tr>
						</thead>
						<tbody>
							<?php
                if(mysqli_num_rows($query_run) > 0){
                  while ($row = mysqli_fetch_assoc($query_run)) {
                    if($row['activo']=='si')
                      $row['activo'] = '<button class="btn"><span style="color: mediumseagreen;"><i class="fas fa-toggle-on fa-2x"></i></span></button>';
                    else
                      $row['activo'] = '<form action="competiciones_code.php" method="POST">
                    <input type="hidden" name="activar_id" value="'.$row['id'].'">
                    <button type="submit" class="btn" name="activar_btn"><span style="color: orangered;"><i class="fas fa-toggle-off fa-2x"></i></span></button>
                    </form>';

                    ?>

							<tr>
								<th scope="row"> <?php echo $row['id']; ?> </th>
								<td> <?php echo $row['nombre']; ?> </td>
								<td> <?php echo $row['lugar']; ?> </td>
								<td> <?php echo $row['fecha']; ?> </td>
								<td> <?php echo $row['activo']; ?> </td>
								<td>
									<form action="competiciones_edit.php" method="post">
										<input type="hidden" name="edit_id" value="<?php echo $row['id']; ?> ">
										<button class="btn btn-success" type="submit" name="edit_btn"><i class="fas fa-edit"></i></btn>
									</form>
								</td>
								<td>
									<form action="competiciones_code.php" method="POST">
										<input type="hidden" name="delete_id" value="<?php echo $row['id'] ?>">
										<button class="btn btn-danger" type="submit" name="delete_btn"><i class="fas fa-backspace"></i></btn>
									</form>
								</td>
							</tr>
							<?php
                      }
                    }else{
                      echo "<tr><td>No se han encontrado registros en la base de datos</td></tr>";
                    }
                    ?>
						</tbody>
					</table>
				</div>
				<h4><button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addUserProfile">Añadir competición</button> </h4>

			</div>



			<!-- template -->
			<?php
            include('includes/scripts.php');
            include('includes/footer.php');
            ?>
