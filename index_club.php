<?php
include('security.php');

include('includes/header.php');
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
                  <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Mi perfil</div>
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
        </div><!-- mi club -->
        <div class=" col-md-4 mb-4">
          <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
              <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                  <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Mi club</div>
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
        <div class=" col-md-4 mb-4">
        <a href="nadadoras.php">
          <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
              <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                  <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Nadadoras</div>
                  <?php
                  $query = "SELECT id FROM nadadoras where club=".$_SESSION['club']." and baja not like 'si'";
                  $query_run = mysqli_query($connection,$query);
                  $numero_nadadoras_activas = mysqli_num_rows($query_run);
                    $query = "SELECT id FROM nadadoras where club=".$_SESSION['club']." and baja like 'si'";
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


        <!-- Competición activa -->
        <?php
        $query = "SELECT * FROM competiciones where id  = ".$_SESSION['id_competicion_activa'];
        $query_run = mysqli_query($connection,$query);
        $competicion = mysqli_fetch_array($query_run);
        $fecha_sorteo = $competicion['fecha_sorteo'];
          ?>
        <div class="col col-12 mb-4">
          <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
              <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                  <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Competición actual</div>
                  <?php
                    $query = 'SELECT * FROM competiciones WHERE activo like "si" and fecha >= now() ORDER BY fecha asc';
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
                                    <div class="col col-12">
                                        <?php
            if($_SESSION['competicion_figuras']=='si'){
                $competicion_figuras ='si';
                $query = "SELECT fases.id, id_categoria, categorias.nombre as nombre_categoria, edad_minima, edad_maxima, id_figura, figuras.nombre as nombre_figura, numero, orden FROM fases, categorias, figuras WHERE fases.id_categoria = categorias.id and fases.id_figura = figuras.id and fases.id_competicion = ".$_SESSION['id_competicion_activa']." ORDER BY orden, fases.id";
            }

            $query_run = mysqli_query($connection,$query);
            ?>
            <table class="table table-striped table-sm" id="noDataTable" width="100%" cellspacing="0">
              <thead>
                <tr>
                    <th scope="col">Modalidad</th>
                    <th scope="col">Figura</th>
                </tr>
              </thead>
              <tbody>
                <?php
                if(mysqli_num_rows($query_run) > 0){
                  while ($row2 = mysqli_fetch_assoc($query_run)) {
                    ?>
                    <tr>
                      <td> <?php echo $row2['nombre_categoria']; ?> </td>
                      <td> <?php echo $row2['numero']." - ".$row2['nombre_figura']; ?> </td>
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
				<div class="row">
					<div class="col col-12 col-md-6">
					<?php
					if((date("Y-m-d") >= $row['fecha_inicio_inscripcion']) & (date("Y-m-d") <= $row['fecha_fin_inscripcion'])){
					?>
						<a href="./inscripciones_figuras.php" class="btn btn-info">Inscripciones</a> Del <?php echo dateAFecha($row['fecha_inicio_inscripcion']).' al '.dateAFecha($row['fecha_fin_inscripcion']);
					}else{
						?>
						<span class="btn btn-danger" style="text-decoration:line-through;">Inscripciones</span> Del <?php echo dateAFecha($row['fecha_inicio_inscripcion']).' al '.dateAFecha($row['fecha_fin_inscripcion']);
					}
								?>
					</div>
					<div class="col col-12 col-md-6">
						<a href="<?php echo $row['enlace_sorteo'];?>" class="btn btn-info">Sorteo <i class="fa-solid fa-video"></i></a> El <?php echo dateAFecha($row['fecha_sorteo']);?>
					</div>
				</div>
                                <?php
                            }
                        }else{
                            echo '<div class="row"><div class="col col-12 h5 mb-0 font-weight-bold text-gray-800">No existen competiciones activas</div></div>';
                        }
                        ?>
                  </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col col-12 mb-4">
          <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
              <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                  <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Próximos eventos</div>
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
                                echo '<div class="col col-1">
                  <a href="'.$row['maps'].'" target=_blank><i class="fa-solid fa-map-location-dot fa-2x text-gray-300"></i></a>
                </div>';
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
                  <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Historial</div>
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


                                echo '<hr>';
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
