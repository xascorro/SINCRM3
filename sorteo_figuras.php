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
                        <h5 class="modal-title" id="exampleModalLabel"><i class="fa-solid fa-wand-magic-sparkles"></i> Nuevo sorteo</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="sorteo_figuras_code.php" method="POST" enctype="multipart/form-data">
                        <div class="modal-body">
                            <div class="row">

                                <div class="form-group col-8">
                                    <?php
                    if($_SESSION['competicion_figuras'] == 'si')
                        include('includes/categorias_competicion_select_option.php');
                    else
                        include('includes/club_select_option.php');
                  ?>
                                </div>
                                <!--
                                <div class="form-group col-4">
                                    <label for="corte">Corte</label>
                                    <input disabled name="corte" class="form-control" type="number" size="2" value="0">
                                    </label>
                                </div>
-->
                                <div class="form-group col-4">
                                    <label for="redondeo">Tipo de redondeo</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="redondeo" value="ceil" checked>
                                        <label class="form-check-label">
                                            Ceil <i class="fa-solid fa-turn-up"></i>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="redondeo" value="floor">
                                        <label class="form-check-label" for="flexRadioDefault1">
                                            Floor <i class="fa-solid fa-turn-down"></i>
                                        </label>
                                    </div>
                                    </label>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                <button type="submit" class="btn btn-primary" name="save_btn"><i class="fa-solid fa-random"></i> Sortear</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal fade" id="anularSorteo" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel"><i class="fa-solid fa-delete-left"></i> Anular sorteo</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="sorteo_figuras_code.php" method="POST" enctype="multipart/form-data">
                        <div class="modal-body">
                            <div class="row">
                                <div class="form-group col-12">
                                    <label for="mensaje">Se va a eliminar el sorteo de la competición completa. <br>Una vez pulsado "Eliminar" no podrá deshacerse.<br>¿Estás seguro? </label>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                <button type="submit" class="btn btn-danger" name="delete_btn"><i class="fa-solid fa-delete-left"></i> Eliminar</button>
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
            <div class=" align-items-center justify-content-between">
                <div class="row">
                    <div class="col col-6">
                        <h4 class=" font-weight-bold text-primary"><i class="fa-solid fa-wand-magic-sparkles"></i> Orden de salida para figuras </h4>
                    </div>
                    <div class="col col-6 ">
                    <div class="row">
                    <button type="button" class="col col-3 btn btn-primary" data-toggle="modal" data-target="#addUserProfile"><i class="fa-solid fa-random"></i> Sortear </button>
                    <div class="col col-1"></div>
                    <button type="button" class="col col-3 btn btn-danger" data-toggle="modal" data-target="#anularSorteo"><i class="fa-solid fa-delete-left"></i> Anular </button>
                    <div class="col col-1"></div>

                    <div class="col col-4">
                        <a href="./informes/informe_figuras_orden_salida_cortes.php?titulo=Orden de salida" class="d-none d-sm-inline-block btn btn-success shadow-sm" target="_blank"><i class="fas fa-download fa-sm"></i> Descargar </a>
                    </div>
                    </div>
                    </div>
                </div>

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
            $query = "SELECT DISTINCT fases.id_categoria, categorias.nombre FROM fases, categorias WHERE fases.id_categoria = categorias.id and fases.id_competicion = ".$_SESSION['id_competicion_activa'];
            $query_categorias = mysqli_query($connection,$query);
            $numero_categorias = $query_categorias->num_rows;
            while ($row_categorias = mysqli_fetch_assoc($query_categorias)) {
                $query = "SELECT id FROM fases WHERE id_categoria = ".$row_categorias['id_categoria']." and id_competicion = ".$_SESSION['id_competicion_activa']   ;
                $numero_categorias = mysqli_query($connection,$query)->num_rows;
                $numero_categorias=1;
                    ?>
                    <table class="table table-striped table-hover table-sm" id="dataTable" width="100%" cellspacing="0">
                        <thead class="thead-light">
                            <tr>
                                <th colspan=5>
                                    <h2 class="text-center"> <?php echo $row_categorias['nombre'];?> <span class=table-success>C1</span> <span class=table-info>C2</span> <span class=table-warning>C3</span> <span class=table-danger>C4</span></h2>
                                </th>
                            </tr>
                            <tr>
                                <th scope="col" colspan="<?php echo $numero_categorias;?>" class="text-center">Orden</th>
                                <th scope="col">#</th>
                                <th scope="col">Nadadora</th>
                                <th scope="col">Año</th>
                                <th scope="col">Club</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                $query = "SELECT id FROM fases WHERE id_categoria = ".$row_categorias['id_categoria']." and id_competicion = ".$_SESSION['id_competicion_activa']." LIMIT 1";
                $id_fase = mysqli_result(mysqli_query($connection,$query),0);

                $query = "SELECT inscripciones_figuras.orden, inscripciones_figuras.id, inscripciones_figuras.id_fase, inscripciones_figuras.id_nadadora, nadadoras.nombre as nombre_nadadora, nadadoras.apellidos as apellidos_nadadora, nadadoras.año_nacimiento as año, clubes.nombre_corto as nombre_club, fases.elementos_coach_card, fases.sorteado FROM inscripciones_figuras, fases, nadadoras, clubes WHERE inscripciones_figuras.id_fase = fases.id and inscripciones_figuras.id_nadadora= nadadoras.id and nadadoras.club = clubes.id and fases.id = $id_fase ORDER BY orden";
                $query_run = mysqli_query($connection,$query);
                if(mysqli_num_rows($query_run) > 0){
                  while ($row = mysqli_fetch_assoc($query_run)) {
                    ?>
                            <tr>
                                <?php
                      $query = "SELECT orden FROM inscripciones_figuras WHERE id_nadadora =".$row['id_nadadora']." and id_fase in (SELECT fases.id FROM fases WHERE id_categoria = ".$row_categorias['id_categoria'].") and id_competicion = ". $_SESSION['id_competicion_activa'];
                      $orden_nadadora = mysqli_query($connection,$query);
                      $orden_impreso = "";
                      $th_class=array(
                            0  => 'table-success',
                            1 => 'table-info',
                            2  => 'table-warning',
                            3  => 'table-danger',
                        );
                      $i=0;
                      $class2 = '';
                      $primer_orden = 0;
                      while($orden_n = mysqli_fetch_assoc($orden_nadadora)){
                          if($orden_n['orden'] == 1){
                              $es_corte = true;
                              $class2 = $th_class[$i];

                          }
                         if($i < 1) {
                      ?>
                                <th class="<?php echo $class2;?> text-center" scope="row"> <?php echo $orden_n['orden']; ?></th>
                                <?php
                         }
                          $i++;
                    }


                      ?>
                                <td class="<?php echo $class2;?>" scope="row"> <?php echo $row['id']; ?> </td>
                                <td class="<?php echo $class2;?>"> <?php echo $row['apellidos_nadadora'].', '.$row['nombre_nadadora']; ?> </td>
                                <td class="<?php echo $class2;?>"> <?php echo $row['año'];?> </td>
                                <td class="<?php echo $class2;?>"> <?php echo $row['nombre_club'];?> </td>
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
                    <?php
            }
            ?>
                </div>
            </div>
            <!-- template -->
            <?php
            include('includes/scripts.php');
            include('includes/footer.php');
            ?>
