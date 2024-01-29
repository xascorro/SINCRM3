<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');
//includes para MarkDown
include ('includes/parsedown.php');
include ('includes/parsedownExtra.php');
$ParsedownExtra = new ParsedownExtra();
$ParsedownExtra->setBreaksEnabled(false);
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
            <h5 class="modal-title" id="exampleModalLabel">Añadir figura</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form action="figuras_code.php" method="POST">
            <div class="modal-body">

              <div class="form-group">
                <label for="nombre">Nombre</label>
                <input type="text" class="form-control" name="nombre">
              </div>
              <div class="form-group">
                <div class="row form-group">
                  <div class="col">
                <label for="numero">Número</label>
                <input type="text" class="form-control" name="numero">
              </div>
                  <div class="col">
                    <label for="licencia">Grado de dificultad</label>
                    <input type="text" class="form-control" name="grado_dificultad">
                  </div>
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
        <h4 class="mb-0 font-weight-bold text-primary"><i class="fas fa-fw fa-female"></i> Registro de figuras
          <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addUserProfile">Añadir figura</button> </h4>
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

            <?php
            $query = "SELECT id, numero, nombre, grado_dificultad, descripcion FROM figuras order by numero";
            $query_run = mysqli_query($connection,$query);
            ?>
            <div class="col-lg-12">





                <?php
                if(mysqli_num_rows($query_run) > 0){
                  while ($row = mysqli_fetch_assoc($query_run)) {
                    ?>
                    <!-- Collapsable Card Example -->
                            <div class="card border-left-info shadow mb-4">
                                <!-- Card Header - Accordion -->
                                <a href="#id_<?php echo $row['id']; ?>" class="d-block card-header py-3" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="id_<?php echo $row['id']; ?>" id="<?php echo $row['id'];?>">
                                    <h6 class="m-0 font-weight-bold text-info"><?php echo $row['numero']; ?> - <?php echo $row['nombre']; ?> - <?php echo $row['grado_dificultad']; ?></h6>
                                </a>
                                <!-- Card Content - Collapse -->
                                <div class="collapse hide" id="id_<?php echo $row['id']; ?>" style="collapse">
                                    <div class="card-body">
                                        <?php echo $ParsedownExtra->text($row['descripcion']); ?>
                                    </div>
                                </div>
                            </div>

                        <?php
                      }
                    }else{
                      echo "<tr><td>No se han encontrado registros en la base de datos</td></tr>";
                    }
                    ?>
              </div>
        </div>
            <!-- template -->
            <?php
            include('includes/scripts.php');
            include('includes/footer.php');
            ?>



