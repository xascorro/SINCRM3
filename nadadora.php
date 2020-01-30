<?php
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
    ?>
    <!-- template -->
    <!-- Tu código empieza aquí -->


    <!-- Modal -->
    <div class="modal fade" id="addUserProfile" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Añadir nadadora</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form action="code.php" method="POST">
            <div class="modal-body">
              <div class="form-group">
                <label for="licencia">Licencia</label>
                <input type="text" class="form-control" name="licencia">
              </div>
              <div class="form-group">
                <label for="apellidos">Apellidos</label>
                <input type="text" class="form-control" name="apellidos">
              </div>
              <div class="form-group">
                <label for="nombre">Nombre</label>
                <input type="text" class="form-control" name="nombre">
              </div>
              <div class="form-group">
                <label for="fechadenacimiento">Fecha de Nacimiento</label>
                <input type="text" class="form-control" name="fechadenacimiento">
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
              <button type="submit" class="btn btn-primary" name="guardarNadadora">Guardar</button>
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
        <h4 class="mb-0 font-weight-bold text-primary">Registro de nadadoras
          <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addUserProfile">Añadir nadadora</button> </h4>
          <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i class="fas fa-download fa-sm text-white-50"></i> Generar PDF</a>
        </div>

        <div class="card-body">

          <?php
          if(isset($_SESSION['correcto']) && $_SESSION['correcto'] != ''){
            echo '<h2>'.$_SESSION['correcto'].'</h2>';
            unset($_SESSION['correcto']);
          }

          if(isset($_SESSION['estado']) && $_SESSION['estado'] != ''){
            echo '<h2>'.$_SESSION['estado'].'</h2>';
            unset($_SESSION['estado']);
          }
          ?>

          <div class="table-responsive">
            <?php
            $connection = mysqli_connect('localhost','root','xas','sincrm3');
            $query = "SELECT * FROM nadadoras"; 
            $query_run = mysqli_query($connection,$query); 
            ?>
            <table class="table table-striped" id="dataTable" width="100%" cellspacing="0">
              <thead>
                <tr>
                  <th scope="col">#</th>
                  <th scope="col">Licencia</th>
                  <th scope="col">Apellidos</th>
                  <th scope="col">Nombre</th>
                  <th scope="col">Fecha de nacimiento</th>
                  <th scope="col">Club</th>
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
                      <td> <?php echo $row['licencia']; ?> </td>
                      <td> <?php echo $row['apellidos']; ?> </td>
                      <td> <?php echo $row['nombre']; ?> </td>
                      <td> <?php echo $row['fechadenacimiento']; ?> </td>
                      <td> <?php echo $row['club']; ?> </td>
                      <td>
                        <form action="nadadora_edit.php" method="post">
                          <input type="hidden" name="edit_id" value=" <?php echo $row['id']; ?> ">
                          <button class="btn btn-success" type="submit" name="edit_btn">Editar</btn>
                          </form>
                        </td>
                        <td><button class="btn btn-danger" type="submit">Borrar</btn></td>
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
            </div>


            <!-- template -->
            <?php
            include('includes/scripts.php');
            include('includes/footer.php');
            ?>



