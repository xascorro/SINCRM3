<?php
//include('security.php');
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
		$query = 'SELECT date_add(fecha, interval -dias_musica day) as fecha_musica, date_add(fecha, interval -dias_coach_card day) as fecha_coach_card, date_add(fecha, interval -dias_sorteo day) as fecha_sorteo, date_add(fecha, interval -dias_inicio_inscripcion day) as fecha_inicio_inscripcion, date_add(fecha, interval -dias_fin_inscripcion day) as fecha_fin_inscripcion FROM competiciones WHERE id='.$id_competicion;
		//habilito o deshabilito subir musica
		$fechas = mysqli_fetch_assoc(mysqli_query($connection, $query));
		$fecha_musica = $fechas['fecha_musica'];
		if(date('Y-m-d') > $fecha_musica & $_SESSION['id_rol'] != 1 )
			$enable_musica = 'disabled';
		else
			$enable_musica = '';
		//habilito o deshabilito coach_card
		$fecha_coach_card = $fechas['fecha_coach_card'];
		if(date('Y-m-d') > $fecha_coach_card & $_SESSION['id_rol'] != 1 )
			$enable_coach_card = 'disabled';
		else
			$enable_coach_card = '';
		$fecha_sorteo = $fechas['fecha_sorteo'];
		if(date('Y-m-d') > $fecha_sorteo)
			$enable_sorteo = 'disabled';
		else
			$enable_sorteo = '';
		//habilito o deshabilito inscripciones (añadir rutinas y añadir participantes)
		$fecha_inicio_inscripcion = $fechas['fecha_inicio_inscripcion'];
		$fecha_fin_inscripcion = $fechas['fecha_fin_inscripcion'];
		if(date('Y-m-d') >= $fecha_fin_inscripcion & $_SESSION['id_rol'] != 1 )
			$enable_inscripcion = 'disabled';
		else
			$enable_inscripcion = '';

    ?>
		<!-- template -->
		<!-- Tu código empieza aquí -->


		<!-- Modal añadir rutina-->
		<div class="modal fade" id="addUserProfile" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="exampleModalLabel">Añadir rutina</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<form action="rutinas_code.php" method="POST" enctype="multipart/form-data">
						<div class="modal-body">
							<div class="row">

								<div class="form-group col">
									<?php
                    if($figuras == 'si')
                        include('includes/nadadoras_select_option.php');
                    else
                        include('includes/club_select_option.php');
                  ?>
								</div>
							</div>
							<div class="row">
								<div class="col">
									<?php
                    include('includes/fases_select_option.php');
                  ?>
								</div>
							</div>
							<div class="modal-footer">
							    <input type="hidden" name="id_competicion" value="<?php echo $id_competicion?>">

								<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
								<button type="submit" class="btn btn-primary" name="save_btn">Guardar</button>
							</div>
						</div>
					</form>


				</div>
			</div>
		</div>
		<!-- Final Modal añadir rutina -->





		<!-- multi Modal borrar rutina-->
		<?php
		$condicion = '';
		if(isset($_SESSION['club']))
			$condicion = ' and rutinas.id_club ='.$_SESSION['club'];
		$query = "SELECT rutinas.id, rutinas.nombre as nombre_rutina, rutinas.id_fase, rutinas.id_club, clubes.nombre_corto as nombre_club, modalidades.nombre as nombre_modalidad, categorias.nombre as nombre_categoria, rutinas.id_fase, fases.elementos_coach_card FROM rutinas, fases, modalidades, categorias, clubes WHERE rutinas.id_fase = fases.id and fases.id_modalidad = modalidades.id and fases.id_categoria = categorias.id and rutinas.id_club = clubes.id and fases.id_competicion = ".$id_competicion.$condicion." ORDER BY rutinas.id_club, fases.orden, fases.orden, fases.id";

		$query_run = mysqli_query($connection,$query);
		if(mysqli_num_rows($query_run) > 0){
			while ($row = mysqli_fetch_assoc($query_run)) {


			?><?php
			}
		}

		?>
		<!-- Final multi Modal borrar rutina -->

		<!-- Begin Page Content -->
		<div class="container-fluid">

			<!-- Titulo página y pdf -->
			<div class="d-sm-flex align-items-center justify-content-between mb-4">
				<h4 class="mb-0 font-weight-bold text-primary"><i class="fas fa-fw fa-flag-checkered"></i>Registro de rutinas
					<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addUserProfile" <?php echo $enable_inscripcion?>>Añadir rutina</button>
					<?php if($_SESSION['id_rol'] != 5){
	?>
					<a target="_blank" href="./informes/inscripciones_numericas_rutinas.php?id_competicion=<?php echo $id_competicion?>&titulo=Inscripciones" class="btn btn-primary shadow"><i class="fas fa-download fa-sm text-white-50"></i> PDF</a>
					<a target="_blank" href="./informes/informe_coach_card.php?titulo=Coach%20Card%20Composer&id_competicion=<?php echo $id_competicion;?>" class="btn btn-warning shadow"><i class="fa fa-solid fa-puzzle-piece"></i> PDF</a>
					<a target="_blank" href="./download_music.php?id_competicion=<?php echo $id_competicion;?>" class="btn btn-info shadow"><i class="fa-solid fa-music"></i> ZIP</a>
					<?php
                    }else{
					?>
					<a target="_blank" href="./informes/inscripciones_numericas_rutinas.php?id_competicion=<?php echo $id_competicion?>&club=<?php echo $_SESSION['club']?>&titulo=Inscripciones <?php echo $_SESSION['nombre_club']?>" class="btn btn-primary shadow"><i class="fas fa-download fa-sm text-white-50"></i> PDF</a>
					<a target="_blank" href="./informes/informe_coach_card.php?titulo=Coach%20Card%20Composer&id_club=<?php echo $_SESSION['club'];?>&id_competicion=<?php echo $id_competicion;?>" class="btn btn-warning shadow"><i class="fa fa-solid fa-puzzle-piece"></i> PDF</a>
					<?php
					}
					?>
					<a href="./index.php" class="btn  btn-primary shadow"><i class="fa fa-chevron-left" aria-hidden="true"></i> Volver</a>
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
					$condicion = '';
					if(isset($_SESSION['club']))
						$condicion = ' and rutinas.id_club ='.$_SESSION['club'];
                	$query = "SELECT rutinas.id, rutinas.nombre as nombre_rutina, rutinas.orden, rutinas.preswimmer, rutinas.id_fase, rutinas.id_club, rutinas.music_name, logo, clubes.nombre_corto as nombre_club, modalidades.nombre as nombre_modalidad, categorias.nombre as nombre_categoria, rutinas.id_fase, fases.elementos_coach_card, music_original_name FROM rutinas, fases, modalidades, categorias, clubes WHERE rutinas.id_fase = fases.id and fases.id_modalidad = modalidades.id and fases.id_categoria = categorias.id and rutinas.id_club = clubes.id and fases.id_competicion = ".$id_competicion.$condicion." ORDER BY rutinas.id_club, fases.orden, fases.orden, fases.id";

            $query_run = mysqli_query($connection,$query);









            ?>
					<table class="table " id="dataTable" width="100%" cellspacing="0">
						<thead>
							<tr>
								<th scope="col">#</th>
								<th scope="col">Fase</th>
								<?php if($_SESSION['id_rol'] != 5){
								echo '<th scope="col">Club</th>';
								}?>
								<th scope="col">P</th>
								<th scope="col">M</th>
								<th scope="col">CC</th>
								<?php if($_SESSION['id_rol'] != 5){
								echo '<th scope="col">Editar</th>';
								}?>
								<th scope="col">D</th>
							</tr>
						</thead>
						<tbody>
							<?php
                if(mysqli_num_rows($query_run) > 0){
                  while ($row = mysqli_fetch_assoc($query_run)) {



					  $nombres = "SELECT group_concat(nadadoras.nombre  separator ', ')  FROM rutinas, rutinas_participantes, nadadoras WHERE nadadoras.id = rutinas_participantes.id_nadadora and rutinas.id = rutinas_participantes.id_rutina and rutinas_participantes.reserva = 'no' and id_rutina = ".$row['id'];
					$nombres = mysqli_result(mysqli_query($connection, $nombres),0);
		?>
							<!--			Inicio modal eliminar rutina-->
							<div class="modal fade" id="delRutina<?php echo $row['id']?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
								<div class="modal-dialog" role="document">
									<div class="modal-content">
										<div class="modal-header">
											<h4 class="modal-title" id="exampleModalLabel"><?php
				if($figuras == 'si')
                        echo 'Eliminar figura';
                    else
                        echo 'Eliminar rutina';
				?>
											</h4>
											<button type="button" class="close" data-dismiss="modal" aria-label="Close">
												<span aria-hidden="true">&times;</span>
											</button>
										</div>
										<form action="rutinas_code.php" method="POST" enctype="multipart/form-data">
											<div class="modal-body">
												<div class="row">
													<div class="col">
														<h6>Confirma si quieres eliminar la rutina <?php echo $row['nombre_modalidad'].' '.$row['nombre_categoria'].' del club '.$row['nombre_club']?></h6>
														<h6><i class="fas fa-users" aria-hidden="true"></i> Se borrarán sus participantes: <?php echo $nombres ?></h6>
														<h6><i class="fa fa-solid fa-puzzle-piece" aria-hidden="true"></i> Se borrará la Coach Card asociada</h6>
														<h6><i class="fa-solid fa-radio"></i> Se borrará el archivo de audio asociado</h6>
														<h6 class="text-danger">Esta acción no se puede deshacer</h6>
													</div>
												</div>
												<div class="modal-footer">
													<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
													<input type="hidden" name="delete_id" value="<?php echo $row['id'];?>">
													<button class="btn btn-danger" type="submit" name="delete_btn">Borrar</btn>
												</div>
											</div>
										</form>
									</div>
								</div>
							</div>

							<!--		fin modal eliminar rutina			  -->
							<!--			Inicio modal musica -->
							<?php
					  $path = './public/music/'.$id_competicion.'/';
$music_name = $row['nombre_categoria'].' - '.$row['nombre_modalidad'].' - '.$row['nombre_club'].' - '.$nombres.'.mp3';
					  				if($figuras == 'no'){
?>
							<div class="modal fade" id="player<?php echo $row['id']?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
								<div class="modal-dialog" role="document">
									<div class="modal-content">
										<div class="modal-body align-center">
											<?php
if(file_exists($path.$music_name)){
?>
											<div class="card" style="">
												<img class="card-img-center" src="<?php echo $row['logo']?>" alt="Logo club">
												<div class="card-body text-center">
													<h5 class="card-title"><?php echo $row['nombre_categoria'].' - '.$row['nombre_modalidad'].' - '.$row['nombre_club']?></h5>
													<p class="card-text"><?php echo $nombres?></p>
													<div><audio src="<?php echo $path.$music_name;
?>" controls preload="none"></audio></div>
											<h7 class="card-title"><?php echo $row['music_original_name']?></h7>
												</div>
											</div>
											<?php
	$anadir_actualizar ='Actualizar';
}else{
	$anadir_actualizar ='Añadir';
}
if( $enable_musica == ''){

?>
											<form action="rutinas_code.php" method="POST" enctype="multipart/form-data">

												<div class="modal-footer">
													<input type="hidden" name="music_name" value="<?php echo $music_name?>">
													<input type="hidden" name="club" value="<?php echo $row['id_club']?>">
													<div class="col-12 ">
														<label for="musica"><?php echo $anadir_actualizar; ?> acompañamiento musical</label>
														<input type="file" class="" id="customFile" name="musica" accept=".mp3" required/>
													</div>
													<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
													<input type="hidden" name="edit_id" value="<?php echo $row['id'];?>">
													<input type="hidden" name="id_competicion" value="<?php echo $id_competicion;?>">
													<button class="btn btn-primary" type="submit" name="upload_music" <?php echo $enable_musica ?>>Guardar</button>
												</div>
										</div>
										</form>
										<?php
						}
																				 ?>
									</div>
								</div>
							</div>
							<?php
									}
					  ?>

							<!--		fin modal musica			  -->




<?php
							$preswimmer= '';
					  if($row['orden'] == -1)
						  $preswimmer = ' (PRESWIMMER)'
							?>
							<tr>
								<th scope="row"> <?php echo $row['id']; ?> </th>
								<td> <?php echo $row['nombre_modalidad']." ".$row['nombre_categoria'].$preswimmer ?> </td>
								<?php if($_SESSION['id_rol'] != 5) {

								echo '<td>'.$row['nombre_club'].' '.$row['nombre_rutina'].'</td>';
								}

                      ?>
								<td>
									<form action="./rutinas_participantes.php" method="post">
										<button class="btn btn-primary" type="submit" name="" <?php echo $enable_inscripcion ?>><i class="fas fa-users"></i></button>
										<input type="hidden" name="id_rutina" value="<?php echo $row['id']; ?>">
										<input type="hidden" name="id_fase" value="<?php echo $row['id_fase']; ?>">
										<input type="hidden" name="club" value="<?php echo $row['id_club']; ?>">
										<input type="hidden" name="id_competicion" value="<?php echo $id_competicion; ?>">
									</form>
								</td>
								<td>
									<?php
									if(file_exists($path.$music_name)){
                                		$icon = '<i class="fa-solid fa-play"></i>';
                                	}else{
                                		$icon = '<i class="fa-solid fa-file-audio"></i>';
                                	}
									?>
									<button type="button" class="btn btn-info" data-toggle="modal" data-target="#player<?php echo $row['id']?>"><?php echo $icon ?></button>
								</td>
								<?php
								if($row['elementos_coach_card']>0){
                          ?>
								<td>
									<form action="coach_card_composer.php" method="post">
										<button class="btn btn-warning btn-circle" type="submit" name="" <?php echo $enable_coach_card;?>><i class="fa fa-solid fa-puzzle-piece"></i></button>
										<input type="hidden" name="id_rutina" value="<?php echo $row['id']?>">
										<input type="hidden" name="id_fase" value="<?php echo $row['id_fase']?>">
										<input type="hidden" name="id_competicion" value="<?php echo $id_competicion?>">


									</form>
								</td>


								<?php
                      } else
                          echo "<td></td>";
								if($_SESSION['id_rol'] != 5){ ?>
								<td>
									<form action="rutinas_edit.php" method="post">
										<input type="hidden" name="id_rutina" value="<?php echo $row['id']; ?>">
										<input type="hidden" name="id_fase" value="<?php echo $row['id_fase']; ?>">
										<input type="hidden" name="club" value="<?php echo $row['id_club']; ?>">
										<button class="btn btn-success" type="submit" name="edit_btn"><i class="fas fa-edit"></i></btn>
									</form>

								</td>
								<?php
								}?>


								<td>
									<button type="button" class="btn btn-danger" data-toggle="modal" data-target="#delRutina<?php echo $row['id']?>" <?php echo $enable_inscripcion ?>><i class="fa-regular fa-trash-can"></i></button>
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
