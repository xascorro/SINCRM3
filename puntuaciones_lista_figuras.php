<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');
//include('./lib/my_functions.php');
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
        $query = "SELECT categorias.nombre as categoria, modalidades.nombre as modalidad, figuras.nombre as figura, numero, grado_dificultad FROM fases, categorias, modalidades, figuras WHERE fases.id=".$_POST['id_fase']." and categorias.id = fases.id_categoria and modalidades.id = fases.id_modalidad and figuras.id = fases.id_figura";
        $nombres = mysqli_fetch_assoc(mysqli_query($connection, $query));
        $nombre_modalidad = $nombres['modalidad'];
        $nombre_categoria = $nombres['categoria'];
        $numero_figura = $nombres['numero'];
        $nombre_figura = $nombres['figura'];
        $grado_dificultad = $nombres['grado_dificultad'];

        ?>
      <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h4 class="mb-0 font-weight-bold text-primary"><i class="fas fa-fw fa-flag-checkered"></i>Puntuar: <?php echo $nombre_modalidad." ".$nombre_categoria ?></h4>
        <h4><?php echo $numero_figura." - ".$nombre_figura." GD:".$grado_dificultad;?></h4>


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
                $query = "SELECT inscripciones_figuras.id, inscripciones_figuras.baja, inscripciones_figuras.orden as orden, inscripciones_figuras.id_nadadora, nota_total, nota_media, nota_final, nadadoras.nombre as nombre_nadadora, nadadoras.apellidos as apellidos_nadadora, categorias.nombre as nombre_categoria, inscripciones_figuras.id_fase, fases.elementos_coach_card FROM inscripciones_figuras, fases, modalidades, categorias, nadadoras WHERE fases.id = ".$_POST['id_fase'] ." and inscripciones_figuras.id_fase = fases.id and fases.id_modalidad = modalidades.id and fases.id_categoria = categorias.id and inscripciones_figuras.id_nadadora = nadadoras.id and fases.id_competicion = ".$_SESSION['id_competicion_activa']." ORDER BY inscripciones_figuras.orden, inscripciones_figuras.id, fases.orden, fases.id";

            $query_run = mysqli_query($connection,$query);
            ?>
            <table class="table table-sm" id="dataTable">
                <thead>
                    <tr>
                        <th scope="col">Orden</th>
                        <th scope="col">#</th>
                        <th scope="col">Nadadora</th>
                        <?php
                        $query = "SELECT id FROM panel_jueces WHERE id_fase=".$_POST['id_fase'];
                        $jueces = mysqli_query($connection,$query);
                        $i = 0;
                        while($juez = mysqli_fetch_assoc($jueces)){
                            $i++;
                            echo '<th scope="col" style="text-align:center">J'.$i.'</th>';
                        }
                        ?>
                        <th scope="col">Total</th>
                        <th scope="col">Media</th>
                        <th scope="col">Nota</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if(mysqli_num_rows($query_run) > 0){
                  while ($row = mysqli_fetch_assoc($query_run)) {
                      if($row['baja']=='si'){
                          $class=' class=bg-danger';
                      }else{
                            $class='';
                      }
                    ?>
                    <tr <?php echo $class;?>>
                      <th scope="row" > <?php echo $row['orden']; ?> </th>
                      <th scope="row" > <?php echo $row['id']; ?> </th>
                      <td > <?php echo $row['apellidos_nadadora'].', '.$row['nombre_nadadora']; ?> </td>
                        <form  target="_blank" action="puntuaciones_lista_figuras_code.php" method="post">
                          <input type="hidden" name="id_inscripcion_figuras" value="<?php echo $row['id']; ?>">
                          <input type="hidden" name="id_fase" value="<?php echo $row['id_fase']; ?>">
                          <input type="hidden" name="grado_dificultad" value="<?php echo $grado_dificultad; ?>">
                          <?php
                        $query = "SELECT id, id_panel, numero_juez, id_juez FROM panel_jueces WHERE id_fase=".$_POST['id_fase'];
                        $jueces = mysqli_query($connection,$query);
                        $i = 0;
                        while($juez = mysqli_fetch_assoc($jueces)){
                                                  $nota = 0;

                            $query = "SELECT nota FROM puntuaciones_jueces WHERE id_panel_juez= ".$juez['id']." and id_inscripcion_figuras = ".$row['id'];
                            $nota = mysqli_result(mysqli_query($connection,$query),0);
                            $i++;
                            echo '<td><input class="form-control form-control-sm" size="2" name="nota['.$juez['numero_juez'].'][nota]"  step="0.1" type="number" value="'.$nota.'"></td>';
                            echo '<input type="hidden" name="nota['.$juez['numero_juez'].'][id_juez]" value="'.$juez['id_juez'].'">';
                            echo '<input type="hidden" name="nota['.$juez['numero_juez'].'][id_panel_jueces]" value="'.$juez['id'].'">';


                        }
                        echo "<td>".$row['nota_total']."</td>";
                        echo "<td>".$row['nota_media']."</td>";
                        echo "<td>".$row['nota_final']."</td>";
                        ?>
                         <td>
                          <button class="form-control form-control-sm btn btn-success" type="submit" name="puntuar_btn"><i class="fa-solid fa-calculator"></i></btn>
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
              <a class="btn btn-primary" target="_blank"href="./puntuaciones_fases_puntuar.php?id_fase=<?php echo $_POST['id_fase'];?>"><i class="fa fa-calculator"></i> Calcular</a> &nbsp;
          <a class="btn btn-primary" target="_blank"href="./informes/informe_puntuaciones.php?titulo=Clasificaci%C3%B3n%20detallada&id_fase=<?php echo $_POST['id_fase'];?>"><i class="fa fa-file-pdf"></i> Descargar</a>
            </div>


            <!-- template -->
            <?php
            include('includes/scripts.php');
            include('includes/footer.php');
            ?>



