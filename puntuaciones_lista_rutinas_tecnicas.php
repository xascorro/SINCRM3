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
      <?php
        $query = "SELECT categorias.nombre as categoria, modalidades.nombre as modalidad FROM fases, categorias, modalidades WHERE fases.id=".$_POST['id_fase']." and categorias.id = fases.id_categoria and modalidades.id = fases.id_modalidad";
        $nombres = mysqli_fetch_assoc(mysqli_query($connection,$query));
        $nombre_modalidad = $nombres['modalidad'];
        $nombre_categoria = $nombres['categoria'];

        ?>
      <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h4 class="mb-0 font-weight-bold text-primary"><i class="fas fa-fw fa-flag-checkered"></i>Puntuar <?echo $nombre_modalidad." ".$nombre_categoria;?>
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
                $query = "SELECT inscripciones_figuras.id, inscripciones_figuras.orden as orden, inscripciones_figuras.id_nadadora, nadadoras.nombre as nombre_nadadora, nadadoras.apellidos as apellidos_nadadora, categorias.nombre as nombre_categoria, inscripciones_figuras.id_fase, fases.elementos_coach_card FROM inscripciones_figuras, fases, modalidades, categorias, nadadoras WHERE fases.id = ".$_POST['id_fase'] ." and inscripciones_figuras.id_fase = fases.id and fases.id_modalidad = modalidades.id and fases.id_categoria = categorias.id and inscripciones_figuras.id_nadadora = nadadoras.id and fases.id_competicion = ".$_SESSION['id_competicion_activa']." ORDER BY inscripciones_figuras.orden, inscripciones_figuras.id, fases.orden, fases.id";

            $query_run = mysqli_query($connection,$query);
            ?>
            <table class="table " id="dataTable" width="100%" cellspacing="0">
              <thead>
                <tr>
                  <th scope="col">Orden</th>
                  <th scope="col">#</th>
                  <th scope="col">Fase</th>
                 <th scope="col">Club</th>
                 <th scope="col">Coach Card</th>
                  <th scope="col">Puntuar</th>
<!--                  <th scope="col">Borrar</th>-->
                </tr>
              </thead>
              <tbody>
                <?php
                if(mysqli_num_rows($query_run) > 0){
                  while ($row = mysqli_fetch_assoc($query_run)) {
                    ?>
                    <tr>
                      <th scope="row"> <?php echo $row['orden']; ?> </th>
                      <th scope="row"> <?php echo $row['id']; ?> </th>
                      <td> <?php echo $row['nombre_modalidad']." ".$row['nombre_categoria']; ?> </td>
                      <td> <?php echo $row['nombre_club'].' '.$row['nombre_rutina']; ?> </td>
                      <?php
                      if($row['elementos_coach_card']>0){
                          ?>
                        <td><a target="_blank" href="./coach_card_composer.php?id_rutina=<?php echo $row['id']; ?>&id_fase=<?php echo $row['id_fase'];?>"  class=" btn btn-warning btn-circle btn">
                    <i class="fas fa-file"></i>
                      </a>  </td>
                      <?php
                      } else
                          echo "<td></td>";
                      ?>
                      <td>
                        <form  target="_blank" action="puntuaciones_rutina.php" method="post">
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
                        <tr>
                            <td colspan="4"></td>
                            <td colspan="2">
                                <?php
                      $query = "SELECT nadadoras.nombre, apellidos FROM rutinas_participantes, nadadoras WHERE id_nadadora = nadadoras.id and id_rutina = ".$row['id'];
                      $nadadoras = mysqli_query($connection,$query);
                        while ($nadadora = mysqli_fetch_assoc($nadadoras)) {
                          echo $nadadora['nombre'].' '.$nadadora['apellidos'].' * ';
                      }
                      ?>
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



