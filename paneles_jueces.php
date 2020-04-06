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
            <h5 class="modal-title" id="exampleModalLabel">Añadir juez</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form action="paneles_jueces_code.php" method="POST">
            <div class="modal-body">
              <div class="form-group">
                <?php
                include('./includes/juez_select_option.php');
                ?>
              </div>
              <div class="form-group">
                <label for="nombre">Puesto</label>
                <input type="text" class="form-control" name="nombre">
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
    <div class="modal fade" id="addPanel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Añadir panel</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form action="paneles_jueces_code.php" method="POST">
            <div class="modal-body">
              <div class="form-group">
                <div class="col">
                  <label for="nombre">Nombre</label>
                  <input type="text" class="form-control" name="nombre">
                </div>
              </div>
              <div class="form-group row">
                <div class="col-3">
                  <label for="numero_jueces">Jueces</label>
                  <input type="number" class="form-control" name="numero_jueces">
                </div>
                <div class="col-3">
                  <label for="peso">% Nota</label>
                  <input type="number" class="form-control" name="peso" placeholder="%">
                </div>
              <div class="col">
                  <label for="numero_jueces">Color</label>
                  <input type="number" class="form-control" name="numero_jueces" placeholder="#CECECE">
                </div>
              </div>
              <div class="form-group">
                <label for="descripcion">Descripción</label>
                <input type="text" class="form-control" name="descripcion">
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
        <h4 class="mb-0 font-weight-bold text-primary"><i class="fas fa-fw fa-gavel"></i> Dirección de la competición
          <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addUserProfile">Añadir juez</button> </h4>
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
            $query = "SELECT puesto_juez.id, jueces.nombre, jueces.apellidos, jueces.licencia, federaciones.nombre_corto , puesto_juez.nombre as puesto FROM puesto_juez, jueces, federaciones where jueces.federacion = federaciones.id and puesto_juez.id_juez = jueces.id"; 
            $query_run = mysqli_query($connection,$query); 
            ?>
            <table class="table table-striped" id="dataTable" width="100%" cellspacing="0">
              <thead>
                <tr>
                  <th scope="col">Puesto</th>
                  <th scope="col">Nombre</th>
                  <th scope="col">Apellidos</th>
                  <th scope="col">Licencia</th>
                  <th scope="col">Federación</th>
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
                      <th scope="row"> <?php echo $row['puesto']; ?> </th>
                      <td> <?php echo $row['nombre']; ?> </td>
                      <td> <?php echo $row['apellidos']; ?> </td>
                      <td> <?php echo $row['licencia']; ?> </td>
                      <td> <?php echo $row['nombre_corto']; ?> </td>
                      <td>
                        <form action="paneles_jueces_edit.php" method="post">
                          <input type="hidden" name="edit_id" value=" <?php echo $row['id']; ?> ">
                          <input type="hidden" name="id_federacion" value=" <?php echo $row['federacion']; ?> ">
                          <button class="btn btn-success" type="submit" name="edit_btn">Editar</btn>
                          </form>
                        </td>
                        <td>
                          <form action="paneles_jueces_code.php" method="POST">
                            <input type="hidden" name="delete_id" value="<?php echo $row['id'] ?>">
                            <button class="btn btn-danger" type="submit" name="delete_btn">Borrar</btn>
                            </form>
                          </td>
                        </tr>
                        <?php
                      }
                    }else{
                      echo "<tr><td colspan='10'>No se han encontrado registros en la base de datos</td></tr>";
                    }
                    ?>
                  </tbody>
                </table>
              </div>
            </div>

            <div class="d-sm-flex align-items-center justify-content-between mb-4">
              <h4 class="mb-0 font-weight-bold text-primary"><i class="fas fa-fw fa-columns"></i> Paneles
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addPanel">Añadir panel</button> </h4>

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
                  $query = "SELECT * from paneles where id_competicion = '".$_SESSION['id_competicion_activa']."'"; 
                  $query_run = mysqli_query($connection,$query); 
                  ?>
                  <table class="table table-striped" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                      <tr>
                        <th scope="col">#</th>
                        <th scope="col">Nombre</th>
                        <th scope="col">Nº Jueces</th>
                        <th scope="col">% Nota</th>
                        <th scope="col">Descripción</th>
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
                            <td> <?php echo $row['nombre']; ?> </td>
                            <td> <?php echo $row['numero_jueces']; ?> </td>
                            <td> <?php echo $row['peso']; ?> </td>
                            <td> <?php echo $row['descripcion']; ?> </td>
                            <td>
                              <form action="paneles_jueces_edit.php" method="post">
                                <input type="hidden" name="edit_id" value=" <?php echo $row['id']; ?> ">
                                <input type="hidden" name="id_federacion" value=" <?php echo $row['federacion']; ?> ">
                                <button class="btn btn-success" type="submit" name="edit_btn">Editar</btn>
                                </form>
                              </td>
                              <td>
                                <form action="paneles_jueces_code.php" method="POST">
                                  <input type="hidden" name="delete_id" value="<?php echo $row['id'] ?>">
                                  <button class="btn btn-danger" type="submit" name="delete_btn">Borrar</btn>
                                  </form>
                                </td>
                              </tr>
                              <?php
                            }
                          }else{
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



