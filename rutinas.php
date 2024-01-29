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
                    if($_SESSION['competicion_figuras'] == 'si')
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
        <h4 class="mb-0 font-weight-bold text-primary"><i class="fas fa-fw fa-flag-checkered"></i>Registro de rutinas
          <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addUserProfile">Añadir rutina</button> </h4>
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
                $query = "SELECT rutinas.id, rutinas.nombre as nombre_rutina, rutinas.id_fase, rutinas.id_club, clubes.nombre_corto as nombre_club, modalidades.nombre as nombre_modalidad, categorias.nombre as nombre_categoria, rutinas.id_fase, fases.elementos_coach_card FROM rutinas, fases, modalidades, categorias, clubes WHERE rutinas.id_fase = fases.id and fases.id_modalidad = modalidades.id and fases.id_categoria = categorias.id and rutinas.id_club = clubes.id and fases.id_competicion = ".$_SESSION['id_competicion_activa']." ORDER BY rutinas.id_club, fases.orden, fases.orden, fases.id";

            $query_run = mysqli_query($connection,$query);
            ?>
            <table class="table " id="dataTable" width="100%" cellspacing="0">
              <thead>
                <tr>
                  <th scope="col">#</th>
                  <th scope="col">Fase</th>
                 <th scope="col">Club</th>
                 <th scope="col">Coach Card</th>
                 <th scope="col">Participantes</th>
                  <th scope="col">Editar</th>
                  <th scope="col">Borrar</th>
                </tr>
              </thead>
              <tbody>
                <?php
                if(mysqli_num_rows($query_run) > 0){
                  while ($row = mysqli_fetch_assoc($query_run)) {
                    ?>
                    <tr>
                      <th scope="row"> <?php echo $row['id']; ?> </th>
                      <td> <?php echo $row['nombre_modalidad']." ".$row['nombre_categoria']; ?> </td>
                      <td> <?php echo $row['nombre_club'].' '.$row['nombre_rutina']; ?> </td>
                      <?php
                      if($row['elementos_coach_card']>0){
                          ?>
                        <td><a href="./coach_card_composer.php?id_rutina=<?php echo $row['id']; ?>&id_fase=<?php echo $row['id_fase'];?>"  class=" btn btn-warning btn-circle btn">
                    <i class="fas fa-file"></i>
                      </a>  </td>


                      <?php
                      } else
                          echo "<td></td>";
                      ?>
                      <td><a target="_blank" href="./rutinas_participantes.php?id_rutina=<?php echo $row['id']; ?>&id_fase=<?php echo $row['id_fase'];?>"  class=" btn btn-primary btn-circle"><i class="fas fa-users"></i></a></td>
                      <td>
                        <form action="rutinas_edit.php" method="post">
                          <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                          <input type="hidden" name="id_fase" value="<?php echo $row['id_fase']; ?>">
                          <input type="hidden" name="id_club" value="<?php echo $row['id_club']; ?>">
                          <button class="btn btn-success" type="submit" name="edit_btn"><i class="fas fa-edit"></i></btn>
                          </form>
                        </td>
                        <td>
                          <form action="rutinas_code.php" method="POST">
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



