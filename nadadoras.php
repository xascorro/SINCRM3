<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');
$condicion_club = '';
if(isset($_SESSION['club'])){
    $condicion_club = " and nadadoras.club = ".$_SESSION['club'];
}
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
          <form action="nadadoras_code.php" method="POST">
            <div class="modal-body">
              <div class="form-group">
                <label for="apellidos">Apellidos</label>
                <input type="text" class="form-control" name="apellidos">
              </div>
              <div class="form-group">
                <label for="nombre">Nombre</label>
                <input type="text" class="form-control" name="nombre">
              </div>
              <div class="form-group">
                <div class="row">
                  <div class="col">
                    <label for="licencia">Licencia</label>
                    <input type="text" class="form-control" name="licencia">
                  </div>
                  <div class="col">
                    <label for="fecha_nacimiento">Año</label>
                    <input type="text" class="form-control" name="fecha_nacimiento">
                  </div>
                </div>
                <div class="form-group">
                  <?php
                  include('./includes/club_select_option.php');
                  ?>
                </div>
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
        <h4 class="mb-0 font-weight-bold text-primary"><i class="fas fa-fw fa-female"></i> Registro de Nadadoras
          <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addUserProfile">Añadir nadadora</button>
          <a href="./index_club.php" class="btn  btn-primary shadow"><i class="fa fa-chevron-left" aria-hidden="true"></i>
</i> Volver</a>
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
            $query = "SELECT nadadoras.id, licencia, apellidos, nadadoras.nombre, año_nacimiento, club, clubes.nombre_corto, baja FROM nadadoras, clubes where nadadoras.club = clubes.id".$condicion_club." ORDER BY apellidos, nombre";
            $query_run = mysqli_query($connection,$query); 
            ?>
            <table class="table table-striped table-sm" id="dataTable" width="100%" cellspacing="0">
              <thead>
                <tr>
                 <?php
                    if(!isset($_SESSION['club'])){
                    echo '<th scope="col">#</th>';
                    }
                ?>
                  <th scope="col">Licencia</th>
                  <th scope="col">Apellidos</th>
                  <th scope="col">Nombre</th>
                  <th scope="col">Año</th>
                  <?php
                    if(!isset($_SESSION['club'])){
                    echo '<th scope="col">Club</th>';
                    }
                ?>
                  <th scope="col" colspan="2" class="text-center">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php
                if(mysqli_num_rows($query_run) > 0){
                  while ($row = mysqli_fetch_assoc($query_run)) {
                      $clase = '';
                      if($row['baja']=='si')
                          $clase = 'class= bg-warning' ;
                    echo '<tr '.$clase.'>';
                    if(!isset($_SESSION['club'])){
                        echo '<th scope="row">'.$row['id'].'</th>';
                    }
                    ?>

                      <td> <?php echo $row['licencia']; ?> </td>
                      <td> <?php echo $row['apellidos']; ?> </td>
                      <td> <?php echo $row['nombre']; ?> </td>
                      <td> <?php echo $row['año_nacimiento']; ?> </td>
                    <?php
                    if(!isset($_SESSION['club'])){
                        echo '<th scope="row">'.$row['nombre_corto'].'</th>';
                    }
                    ?>
                       <td class="center">
                        <form action="nadadoras_edit.php" method="post">
                          <input type="hidden" name="edit_id" value=" <?php echo $row['id']; ?> ">
                          <input type="hidden" name="id_club" value=" <?php echo $row['club']; ?> ">
                          <button class="btn btn-success" type="submit" name="edit_btn"><i class="fas fa-edit"></i></btn>
                          </form>
                        </td>
                        <td class="text-center">
                          <form action="nadadoras_code.php" method="POST">
                            <input type="hidden" name="id_nadadora" value="<?php echo $row['id'] ?>">
                            <?php
                    if(!isset($_SESSION['club'])){
                        echo '<button class="btn btn-danger" type="submit" name="delete_btn"><i class="fas fa-trash"></i></btn>';
                    }else{
                        if($row['baja'] == 'si')
                            echo '<button class="btn btn-info" type="submit" name="alta_btn"><i class="fas fa-circle-up"></i></btn>';
                        else
                            echo '<button class="btn btn-warning" type="submit" name="baja_btn"><i class="fas fa-circle-down"></i></btn>';
                    }
                    ?>



                            </form>
                          </td>
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



