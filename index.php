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
		<div class="container-fluid">
			<?php
		if(isset($_SESSION['no_acceso']) && $_SESSION['no_acceso'] != ''){
            echo '<div class="alert alert-danger" role="alert">'.$_SESSION['no_acceso'].'</div>';
            unset($_SESSION['no_acceso']);
          }

		?>
			<!-- Page Heading -->
			<div class="d-sm-flex align-items-center justify-content-between mb-4">
				<h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
			</div>

			<!-- Content Row -->
			<div class="row">

				<!-- mi perfil -->
				<div class=" col-md-4 mb-4">
					<div class="card border-left-warning shadow h-100 py-2">
						<div class="card-body">
							<div class="row no-gutters align-items-center">
								<div class="col mr-2">
									<div class="font-weight-bold text-warning text-uppercase mb-1">Mi perfil</div>
									<?php
                  $query = "SELECT * FROM usuarios where id=".$_SESSION['id_usario']."";
                  $query_run = mysqli_query($connection,$query);
                  $usuario = mysqli_fetch_array($query_run);
                  ?>
									<div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $usuario['username']?></div>
									<div class="h5 mb-0 font-weight-bold text-gray-800"><i class="fa-regular fa-envelope"></i> <?php echo @$usuario['email']?></div>
									<div class="h5 mb-0 font-weight-bold text-gray-800"><i class="fa-solid fa-phone"></i> <?php echo @$usuario['telefono']?></div>
								</div>
								<div class="col-auto">
									<i class="fas fa-user fa-2x text-gray-300"></i>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- mi club -->
				<?php
		if($_SESSION['id_rol'] == 5){
		?>
				<div class=" col-md-4 mb-4">
					<div class="card border-left-danger shadow h-100 py-2">
						<div class="card-body">
							<div class="row no-gutters align-items-center">
								<div class="col mr-2">
									<div class="font-weight-bold text-primary text-uppercase mb-1">Mi club</div>
									<?php
                  $query = "SELECT * FROM clubes where id=".$_SESSION['club']."";
                  $query_run = mysqli_query($connection,$query);
                  $club = mysqli_fetch_array($query_run);
                  ?>
									<div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $club['nombre']?></div>
									<div class="h5 mb-0 font-weight-bold text-gray-800">Nombre corto: <?php echo $club['nombre_corto']?></div>
									<div class="h5 mb-0 font-weight-bold text-gray-800">Código RFEN: <?php echo $club['codigo']?></div>
								</div>
								<div class="col-auto">
									<i class="fas fa-flag fa-2x text-gray-300"></i>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php
		}
		?>
				<!--       mis nadadoras-->
				<div class=" col-md-4 mb-4">
					<a href="nadadoras.php">
						<div class="card border-left-primary shadow h-100 py-2">
							<div class="card-body">
								<div class="row no-gutters align-items-center">
									<div class="col mr-2">
										<div class="font-weight-bold text-primary text-uppercase mb-1">Nadadoras</div>
										<?php
					//filtro por club si el rol es club
					if($_SESSION['id_rol'] == 5){
						$condicion_club = ' and club='.$_SESSION['club'].' ';
					}else{
						$condicion_club = '';
					}
                  $query = "SELECT id FROM nadadoras where baja not like 'si' $condicion_club";
                  $query_run = mysqli_query($connection,$query);
                  $numero_nadadoras_activas = mysqli_num_rows($query_run);
                  $query = "SELECT id FROM nadadoras where baja like 'si' $condicion_club";
                  $query_run = mysqli_query($connection,$query);
                  $numero_nadadoras_baja = mysqli_num_rows($query_run);
                  ?>
										<div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $numero_nadadoras_activas?> nadadoras activas</div>
										<div class="h5 text-warning mb-0 font-weight-bold text-gray-800"><?php echo $numero_nadadoras_baja?> nadadoras baja</div>
									</div>
									<div class="col-auto">
										<i class="fas fa-person-drowning fa-2x text-gray-300"></i>
									</div>
								</div>
							</div>
						</div>
					</a>
				</div>


				<!-- Proximas competiciones -->
				<div class="col col-12 mb-4">
					<div class="card border-left-info shadow h-100 py-2">
						<div class="card-body">
							<div class="row no-gutters align-items-center">
								<div class="col mr-2">
									<div class="font-weight-bold text-info text-uppercase mb-1">Próxima competición</div>
									<?php
//                    $query = 'SELECT * FROM competiciones WHERE activo like "si" and fecha >= now() ORDER BY fecha asc';
                    	$query = 'SELECT * FROM competiciones WHERE fecha >= now() ORDER BY fecha asc limit 2';
                        $query_run = mysqli_query($connection,$query);
                        if(mysqli_num_rows($query_run) > 0){
                            while ($row = mysqli_fetch_assoc($query_run)) {
                                echo '<div class="row"><div class="col col-12 col-md-6 h5 mb-0 font-weight-bold text-gray-800">'.$row['nombre'].'</div>';
                                echo '<div class="col col-12 col-md-3 h6 mb-0 font-weight-bold text-gray-800">'.$row['lugar'].'</div>';
                                echo '<div class="col col-12 col-md-2 font-weight-bold text-gray-800">'.
                                date("d M y",strtotime( $row['fecha'] ))
                                .'</div>';
                                echo '<div class="col col-1">
                                <a href="'.$row['maps'].'" target=_blank><i class="fa-solid fa-map-location-dot fa-2x text-gray-300"></i></a>
                                </div>';
                                echo '</div>';
                                ?>
									<div class="col col-12" style="padding-left: 0px; padding-right: 0px;">
										<table class="table table-striped table-sm" id="noDataTable" width="100%" cellspacing="0">
											<thead>
												<tr>
													<?php
				if($row['figuras']=='si'){
                	$query = "SELECT fases.id, id_categoria, categorias.nombre as nombre_categoria, edad_minima, edad_maxima, id_figura, figuras.nombre as nombre_figura, numero, orden, grado_dificultad FROM fases, categorias, figuras WHERE fases.id_categoria = categorias.id and fases.id_figura = figuras.id and fases.id_competicion = ".$row['id']." ORDER BY orden, fases.id";?>
													<th scope="col">Categoria</th>
													<th scope="col">Figura</th>
													<th class="d-none d-sm-block" scope="col">GD</th>
													<?php
				}else if($row['figuras']=='no'){
                	$query = "SELECT fases.id, id_categoria, categorias.nombre as nombre_categoria, edad_minima, edad_maxima, id_modalidad, modalidades.nombre as nombre_modalidad, orden, elementos_coach_card, numero_participantes, numero_reservas, f_chomu FROM fases, categorias, modalidades WHERE fases.id_categoria = categorias.id and fases.id_modalidad = modalidades.id and fases.id_competicion = ".$row['id']." ORDER BY orden, fases.id";?>
													<th scope="col">Categoria</th>
													<th scope="col">Modalidad</th>
													<th scope="col">Participantes</th>
<!--
													<th scope="col">CC</th>
													<th scope="col">F_CHOMU</th>
-->
													<?php
            }
//obtengo las fases y datos asociados
            $query_run2 = mysqli_query($connection,$query);
            ?>
												</tr>
											</thead>
											<tbody>
												<?php
								if(mysqli_num_rows($query_run2) > 0){
					while ($row2 = mysqli_fetch_assoc($query_run2)) {
                    ?>
												<tr>
												<?php
										if($row['figuras']=='si'){
											$enlace_inscripcion="./inscripciones_figuras.php";
?>
													<td> <?php echo $row2['nombre_categoria']; ?> </td>
													<td> <?php echo $row2['numero']." - ".$row2['nombre_figura']; ?> </td>
													<td class="d-none d-sm-block" > <?php echo $row2['grado_dificultad']; ?> </td>
												</tr>
												<?php
										}else if($row['figuras']=='no'){
											$enlace_inscripcion="./rutinas.php";
?>
													<td> <?php echo $row2['nombre_categoria']; ?> </td>
													<td> <?php echo $row2['nombre_modalidad']; ?> </td>
													<td scope="col"><?php echo $row2['numero_participantes'].' + '.$row2['numero_reservas'].'R'; ?> </td>
<!--
													<td scope="col"><?php echo $row2['elementos_coach_card']; ?> </td>
													<td scope="col"><?php echo $row2['f_chomu']; ?> </td>
-->
												</tr>
												<?php
										}
                      }
                }else{
                      echo "<tr><td colspan='10'>No se han encontrado registros en la base de datos</td></tr>";
                    }
                    ?>
											</tbody>
										</table>
									</div>
									<div class="row">
										<?php
//					if((date("Y-m-d") >= $row['fecha_inicio_inscripcion']) & (date("Y-m-d") <= $row['fecha_fin_inscripcion'])){
					if((date("Y-m-d") >= $row['fecha_inicio_inscripcion'])){
					?>
										<div class="col col-12 col-md-6 mb-4">
										<form action="<?php echo $enlace_inscripcion;?>" method="post">
										<label for="inscripciones">Del <?php echo dateAFecha($row['fecha_inicio_inscripcion']).' al '.dateAFecha($row['fecha_fin_inscripcion']).'<br>';?></label>
										<input type="hidden" name="id_competicion" value="<?php echo $row['id'];?>">
										<input type="hidden" name="nombre_competicion" value="<?php echo $row['nombre'];?>">
										<input type="hidden" name="competicion_figuras" value="<?php echo $row['figuras'];?>">
											<input name="inscripciones" class="btn btn-info form-control" type="submit" value="Inscribirse">
										</form>
<!--											<a href="./inscripciones_figuras.php" class="btn btn-info">Inscripciones</a> Del <?php echo dateAFecha($row['fecha_inicio_inscripcion']).' al '.dateAFecha($row['fecha_fin_inscripcion']).'<br>';?>-->
										</div>
										<?php

					}else if((date("Y-m-d") <= $row['fecha_inicio_inscripcion'])){
						?>
										<div class="col col-12 col-md-6 mb-4">
											<span class="text text-info">La inscripción se abrirá del <?php echo dateAFecha($row['fecha_inicio_inscripcion']).' al '.dateAFecha($row['fecha_fin_inscripcion'])?>
											</span>
										</div>

										<?php
					}
								?>
										<div class="col col-12 col-md-6 mb-4">
										<label for="sorteo">El sorteo se realizará el <?php echo dateAFecha($row['fecha_sorteo']);?></label>
											<a href="<?php echo $row['enlace_sorteo'];?>" class="btn btn-info form-control" name="sorteo">Unirse <i class="fa-solid fa-video"></i></a>
										</div>
									</div>
									<?php
								echo '<div class="row">';
                                $filename = './docs/'.$row['id'].'-inscripciones.pdf';
                                if (file_exists($filename)) {
                                    echo '<div class="col col-12 col-md-3"><a href="'.$filename.'" target="_blank"><i class="fa fa-2x fa-file-arrow-down"></i> Inscripciones</a></div>';
                                }
                                $filename = './docs/'.$row['id'].'-orden.pdf';
                                if (file_exists($filename)) {
                                    echo '<div class="col col-12 col-md-3"><a href="'.$filename.'" target="_blank"><i class="fa fa-2x fa-file-arrow-down"></i> Orden</a></div>';
                                }
                                $filename = './docs/'.$row['id'].'-resultados.pdf';
                                if (file_exists($filename)) {
                                    echo '<div class="col col-12 col-md-3"><a href="'.$filename.'" target="_blank"><i class="fa fa-2x fa-file-arrow-down"></i> Resultados</a></div>';
                                }
                                $filename = './docs/'.$row['id'].'-liga.pdf';
                                if (file_exists($filename)) {
                                    echo '<div class="col col-12 col-md-3"><a href="'.$filename.'" target="_blank"><i class="fa fa-2x fa-file-arrow-down"></i> Liga</a></div>';
                                }
                                echo '</div>';
								echo '<div class="border-top border-info my-5"></div>';

							}
                        }else{
                            echo '<div class="row"><div class="col col-12 h5 mb-0 font-weight-bold text-gray-800">No existen competiciones programadas</div></div>';
                        }
                        ?>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!--
			</div>
		</div>
-->

				<div class="col col-12 mb-4">
					<div class="card border-left-success shadow h-100 py-2">
						<div class="card-body">
							<div class="row no-gutters align-items-center">
								<div class="col mr-2">
									<div class="font-weight-bold text-success text-uppercase mb-1">Competiciones programadas</div>
									<?php
                    $query = 'select * FROM competiciones WHERE activo like "no" and fecha >= now() ORDER BY fecha asc';
                        $query_run = mysqli_query($connection,$query);
                        if(mysqli_num_rows($query_run) > 0){
                            while ($row = mysqli_fetch_assoc($query_run)) {
                                echo '<div class="row"><div class="col col-12 col-md-6 h5 mb-0 font-weight-bold text-gray-800">'.$row['nombre'].'</div>';
                                echo '<div class="col col-12 col-md-3 h6 mb-0 font-weight-bold text-gray-800">'.$row['lugar'].'</div>';
                                echo '<div class="col col-12 col-md-2 font-weight-bold text-gray-800">'.
                                date("d M y",strtotime( $row['fecha'] ))
                                .'</div>';
                                echo '<div class="col col-1"><a href="'.$row['maps'].'" target=_blank><i class="fa-solid fa-map-location-dot fa-2x text-gray-300"></i></a></div>';
                                echo '</div>';
                            }
                        }else{
                            echo '<div class="row"><div class="col col-12 h5 mb-0 font-weight-bold text-gray-800">No existen eventos programados</div></div>';
                        }
                        ?>
								</div>
							</div>
						</div>
					</div>
				</div>


				<div class="col col-12 mb-4">
					<div class="card border-left-danger shadow h-100 py-2">
						<div class="card-body">
							<div class="row no-gutters align-items-center">
								<div class="col mr-2">
									<div class="font-weight-bold text-danger text-uppercase mb-1">Historial</div>
									<?php
                    $query = 'select * FROM competiciones WHERE fecha < now() ORDER BY fecha desc';
                        $query_run = mysqli_query($connection,$query);
                        if(mysqli_num_rows($query_run) > 0){
                            while ($row = mysqli_fetch_assoc($query_run)) {

                                echo '<div class="row"><div class="col col-12 col-md-6 h5 mb-0 font-weight-bold text-gray-800">'.$row['nombre'].'</div>';
                                echo '<div class="col col-12 col-md-3 h6 mb-0 font-weight-bold text-gray-800">'.$row['lugar'].'</div>';
                                echo '<div class="col col-9 col-md-2 font-weight-bold text-gray-800">'.
                                date("d M y",strtotime( $row['fecha'] ))
                                .'</div>';
                                echo '<div class="col-auto">
                  <a href="'.$row['maps'].'" target=_blank><i class="fa-solid fa-map-location-dot fa-2x text-gray-300"></i></a>
                </div>';
                                echo '</div>';

                                echo '<div class="row">';
                                $filename = './docs/'.$row['id'].'-inscripciones.pdf';
                                if (file_exists($filename)) {
                                    echo '<div class="col col-12 col-md-3"><a href="'.$filename.'" target="_blank"><i class="fa fa-2x fa-file-arrow-down"></i> Inscripciones</a></div>';
                                }
                                $filename = './docs/'.$row['id'].'-orden.pdf';
                                if (file_exists($filename)) {
                                    echo '<div class="col col-12 col-md-3"><a href="'.$filename.'" target="_blank"><i class="fa fa-2x fa-file-arrow-down"></i> Orden</a></div>';
                                }
                                $filename = './docs/'.$row['id'].'-resultados.pdf';
                                if (file_exists($filename)) {
                                    echo '<div class="col col-12 col-md-3"><a href="'.$filename.'" target="_blank"><i class="fa fa-2x fa-file-arrow-down"></i> Resultados</a></div>';
                                }
                                $filename = './docs/'.$row['id'].'-liga.pdf';
                                if (file_exists($filename)) {
                                    echo '<div class="col col-12 col-md-3"><a href="'.$filename.'" target="_blank"><i class="fa fa-2x fa-file-arrow-down"></i> Liga</a></div>';
                                }
                                echo '</div>';
                            }
                        }
                        ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>



			<!--template -->
			<?php
    include('includes/scripts.php');
    include('includes/footer.php');
    ?>
