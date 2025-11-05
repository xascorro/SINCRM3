<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');

if(isset($_GET['id_fase']))
	$_POST['id_fase'] = $_GET['id_fase'];
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
			<?php
        $query = "SELECT categorias.nombre as categoria, modalidades.nombre as modalidad FROM fases, categorias, modalidades WHERE fases.id=".$_POST['id_fase']." and categorias.id = fases.id_categoria and modalidades.id = fases.id_modalidad";
        $nombres = mysqli_fetch_assoc(mysqli_query($connection,$query));
        $nombre_modalidad = $nombres['modalidad'];
        $nombre_categoria = $nombres['categoria'];

        ?>
			<div class="d-sm-flex align-items-center justify-content-between mb-4">
				<h4 class="mb-0 font-weight-bold text-primary"><i class="fas fa-fw fa-flag-checkered"></i>Puntuar <?php echo $nombre_modalidad." ".$nombre_categoria;?>
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
                $query = "SELECT rutinas.id, rutinas.dd_total, rutinas.orden as orden, rutinas.nombre as nombre_rutina, rutinas.id_club, nota_final, baja, clubes.nombre_corto as nombre_club, modalidades.nombre as nombre_modalidad, categorias.nombre as nombre_categoria, rutinas.id_fase, fases.elementos_coach_card FROM rutinas, fases, modalidades, categorias, clubes WHERE fases.id = ".$_POST['id_fase'] ." and rutinas.id_fase = fases.id and fases.id_modalidad = modalidades.id and fases.id_categoria = categorias.id and rutinas.id_club = clubes.id and fases.id_competicion = ".$_SESSION['id_competicion_activa']." ORDER BY rutinas.orden, rutinas.id, fases.orden, fases.id";

            $query_run = mysqli_query($connection,$query);
            ?>
					<table class="table " id="dataTable" width="100%" cellspacing="0">
						<thead>
							<tr>
								<th scope="col"><i class="fa-solid fa-list-ol"></i></th>
								<th scope="col">#</th>
								<th scope="col">Rutina</th>
								<th scope="col">Puntos</th>
								<th scope="col">Coach Card</th>
								<th scope="col">Baja</th>
								<th scope="col">Puntuar</th>
								<!--                  <th scope="col">Borrar</th>-->
							</tr>
						</thead>
						<tbody>
							<?php
                if(mysqli_num_rows($query_run) > 0){
                  while ($row = mysqli_fetch_assoc($query_run)) {
					  $nombres = "SELECT group_concat(nadadoras.nombre SEPARATOR ', ') FROM rutinas, rutinas_participantes, nadadoras WHERE nadadoras.id = rutinas_participantes.id_nadadora and rutinas.id = rutinas_participantes.id_rutina and rutinas_participantes.reserva = 'no' and id_rutina = ".$row['id'];
					$nombres = mysqli_result(mysqli_query($connection,$nombres));
					  if($row['baja'] == 'si')
						  $class_baja = ' class="table-danger"';
					  else
						  $class_baja = '';
                    ?>
							<tr <?php echo $class_baja;?>>
								<?php
					  if ($row['orden'] == -1)
						$row['orden'] = 'PS';
					  ?>
								<th scope="row" class="align-middle"> <?php echo $row['orden']; ?> </th>
								<th scope="row" class="align-middle"> <?php echo $row['id']; ?> </th>
								<td class="align-middle">
									<blockquote class="blockquote"><?php echo $row['nombre_modalidad']." ".$row['nombre_categoria']." ". $row['nombre_club'].$preswimmer.'</blockquote> <figcaption class="blockquote-footer">('.$nombres.')</figcaption>'; ?>
								</td>
								<td class="align-middle"><?php echo $row['nota_final'];?></td>

								<?php
                      if($row['elementos_coach_card']>0){
                          ?>
								<td class="align-middle">
									<a target="_blank" href="./coach_card_composer.php?id_rutina=<?php echo $row['id']; ?>&id_fase=<?php echo $row['id_fase'];?>" class=" btn btn-warning btn-circle btn">
										<i class="fa-solid fa-puzzle-piece"></i>
									</a>
									<span class="badge text-bg-secondary font-size-sm"><?php echo $row['dd_total'];?></span>

								</td>
								<td class="align-middle">
									<?php
						  if($row['baja']=='no'){


						  ?>
									<a target="_self" href="./rutinas_code.php?id_rutina=<?php echo $row['id'];?>&dar_baja=si&id_fase=<?php echo $_POST['id_fase'];?>" class=" btn btn-danger btn-circle btn">
										<i class="fa-regular fa-thumbs-down"></i>
									</a>
									<?php
						  }else{
								  ?>
									<a target="_self" href="./rutinas_code.php?id_rutina=<?php echo $row['id'];?>&dar_baja=no&id_fase=<?php echo $_POST['id_fase'];?>" class=" btn btn-info btn-circle btn">
										<i class="fa-regular fa-thumbs-up"></i>
									</a>
									<?php
							  }
							  ?>
								</td>
								<?php
                      } else
                          echo "<td></td>";
                      ?>
								<td class="align-middle">
									<form target="_blank" action="puntuaciones_rutina.php" method="post">
										<input type="hidden" name="id_rutina" value="<?php echo $row['id']; ?>">
										<input type="hidden" name="id_fase" value="<?php echo $row['id_fase']; ?>">
										<input type="hidden" name="id_club" value="<?php echo $row['id_club']; ?>">
										<input type="hidden" name="nombre_modalidad" value="<?php echo $row['nombre_modalidad']; ?>">
										<input type="hidden" name="nombre_categoria" value="<?php echo $row['nombre_categoria']; ?>">
										<input type="hidden" name="nombre_club" value="<?php echo $row['nombre_club']; ?>">
										<input type="hidden" name="nombre_rutina" value="<?php echo $row['nombre_rutina']; ?>">
										<button class="btn btn-success" type="submit" name="edit_btn">Puntuar</btn>
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
				<a class="btn btn-primary" target="_blank" href="./puntuaciones_fases_rutinas_puntuar_categorias.php?id_fase=<?php echo $_POST['id_fase'];?>"><i class="fa fa-calculator"></i> Calcular</a> &nbsp;
				<a class="btn btn-primary" target="_blank" href="./informes/informe_puntuaciones.php?titulo=Clasificaci%C3%B3n%20detallada&id_fase=<?php echo $_POST['id_fase'];?>"><i class="fa fa-file-pdf"></i> Descargar</a>
			</div>


			<!-- template -->
			<?php
            include('includes/scripts.php');
            include('includes/footer.php');
            ?>
