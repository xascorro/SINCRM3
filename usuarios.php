<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');
include('./lib/my_functions.php');

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
            <h5 class="modal-title" id="exampleModalLabel">Añadir usuario</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form action="usuarios_code.php" method="POST">
            <div class="modal-body">
              <div class="form-group">
                <label for="username">Nombre de usuario</label>
                <input type="text" class="form-control" name="username">
              </div>
              <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control form-control-user" name="email">
              </div>
              <div class="form-group">
                <label for="club">Club</label>
                <?php
                  include("includes/club_select_option.php");
                  ?>
              </div>
              <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" class="form-control" name="password">
              </div>
              <div class="form-group">
                <label for="r_password">Repite la contraseña</label>
                <input type="password" class="form-control" name="r_password">
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
        <h4 class="mb-0 font-weight-bold text-primary">Registro de usuarios
          <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addUserProfile">Añadir usuario</button> </h4>
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
            $query = "SELECT usuarios.id, username, email, telefono, club, comentario, roles.nombre as rol FROM usuarios, roles where usuarios.id_rol = roles.id order by usuarios.id";
            $query_run = mysqli_query($connection,$query); 
            ?>
            <table class="table table-striped" id="dataTable" width="100%" cellspacing="0">
              <thead>
                <tr>
                  <th scope="col">#</th>
                  <th scope="col">Username</th>
                  <th scope="col">Email</th>
                  <th scope="col">Teléfono</th>
                  <th scope="col">Club</th>
                  <th scope="col">Comentario</th>
                  <th scope="col">Rol</th>
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
                      <td> <?php echo $row['username']; ?> </td>
                      <td> <?php echo $row['email']; ?> </td>
                      <td> <?php echo $row['telefono']; ?> </td>
                      <?php
                      $query = "select nombre_corto from clubes where id = '".$row['club']."'";
                $nombre_club = mysqli_result(mysqli_query($connection,$query),0);
                      ?>
                      <td> <?php echo $nombre_club; ?> </td>
                      <td> <?php echo $row['comentario']; ?> </td>
                      <td> <?php echo $row['rol']; ?> </td>
                      <td>
                        <form action="usuarios_edit.php" method="post">
                          <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                          <button class="btn btn-success" type="submit" name="edit_btn"><i class="fas fa-edit"></i></btn>
                          </form>
                        </td>
                        <td>
                          <form action="usuarios_code.php" method="POST">
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



