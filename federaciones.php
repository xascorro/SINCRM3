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
            <h5 class="modal-title" id="exampleModalLabel">Añadir federación</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form action="federaciones_code.php" method="POST" enctype="multipart/form-data">
            <div class="modal-body">
              <div class="form-group">
                <label for="nombre">Nombre</label>
                <input type="text" class="form-control" name="nombre">
              </div>
              <div class="form-group">
                <label for="nombre_corto">Nombre corto</label>
                <input type="text" class="form-control" name="nombre_corto">
              </div>
              <div class="form-group">
                <label for="codigo">Código</label>
                <input type="text" class="form-control" name="codigo">
              </div>
              <div class="form-group">
                <label for="logo">Logo</label>
                <input type="file" class="form-control" id="logo" name="logo">
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
          <h4 class="mb-0 font-weight-bold text-primary"><i class="fas fa-fw fa-flag-checkered"></i>Registro de Federaciones
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addUserProfile">Añadir Federación</button> </h4>
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
              $query = "SELECT * FROM federaciones"; 
              $query_run = mysqli_query($connection,$query); 
              ?>
              <table class="table table-striped" id="dataTable" width="100%" cellspacing="0">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Nombre</th>
                    <th scope="col">Nombre corto</th>
                    <th scope="col">Código</th>
                    <th scope="col">Logo</th>
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
                        <td> <?php echo $row['nombre_corto']; ?> </td>
                        <td> <?php echo $row['codigo']; ?> </td>
                        <td> <?php echo '<img width="100px" alt="Imagen" src="'.$row[logo].'">';?></td>
                        <td>
                          <form action="federaciones_edit.php" method="post">
                            <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                            <button class="btn btn-success" type="submit" name="edit_btn">Editar</btn>
                            </form>
                          </td>
                          <td>
                            <form action="federaciones_code.php" method="POST">
                              <input type="hidden" name="delete_id" value="<?php echo $row['id'];?>">
                              <button class="btn btn-danger" type="submit" name="delete_btn">Borrar</btn>
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



