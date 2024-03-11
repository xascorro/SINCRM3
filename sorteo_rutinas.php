<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
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
                    <form action="sorteo_rutinas_code.php" method="POST" enctype="multipart/form-data">
                        <div class="modal-body">
                            <div class="row">

                                <div class="form-group col-8">
                                    <?php
                        include('includes/fases_competicion_select_option.php');
                  ?>
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
                    <form action="sorteo_rutinas_code.php" method="POST" enctype="multipart/form-data">
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
                        <h4 class=" font-weight-bold text-primary"><i class="fa-solid fa-wand-magic-sparkles"></i> Orden de salida para rutinas </h4>
                    </div>
                    <div class="col col-6 ">
                    <div class="row">
                    <button type="button" class="col col-3 btn btn-primary" data-toggle="modal" data-target="#addUserProfile"><i class="fa-solid fa-random"></i> Sortear </button>
                    <div class="col col-1"></div>
                    <button type="button" class="col col-3 btn btn-danger" data-toggle="modal" data-target="#anularSorteo"><i class="fa-solid fa-delete-left"></i> Anular </button>
                    <div class="col col-1"></div>

                    <div class="col col-4">
                        <a href="./informes/inscripciones_numericas_rutinas.php?id_competicion=52&titulo=Orden de salida" class="d-none d-sm-inline-block btn btn-success shadow-sm" target="_blank"><i class="fas fa-download fa-sm"></i> Descargar </a>
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
            $query = "SELECT DISTINCT fases.id as id_fase, fases.id_categoria, categorias.nombre as categoria, modalidades.nombre as modalidad FROM fases, categorias, modalidades WHERE fases.id_modalidad=modalidades.id and fases.id_categoria = categorias.id and fases.id_competicion = ".$_SESSION['id_competicion_activa']." order by orden";
            $query_categorias = mysqli_query($connection,$query);
            $numero_categorias = $query_categorias->num_rows;
            while ($row_fases = mysqli_fetch_assoc($query_categorias)) {
                $query = "SELECT id FROM fases WHERE id_categoria = ".$row_fases['id_categoria']." and id_competicion = ".$_SESSION['id_competicion_activa']   ;
                $numero_fases = mysqli_query($connection,$query)->num_rows;
//                $numero_categorias=1;
                    ?>
                    <table class="table table-striped table-hover table-sm" id="nodataTable" width="100%" cellspacing="0">
                        <thead class="thead-light">
                            <tr>
                                <th colspan=4>
                                    <h2 class="text-center"> <?php echo $row_fases['modalidad'].' '.$row_fases['categoria'];?></h2>
                                </th>
                            </tr>
                            <tr>
                                <th scope="col" class="col col-1  text-center"><i class="fa-solid fa-list-ol"></i></th>
                                <th scope="col" class="col col-1 ">#</th>
                                <th scope="col" class="col col-2 ">Club</th>
                                <th scope="col" class="col col-8 ">Nadadoras</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                $query = "SELECT rutinas.id as id_rutina, rutinas.orden, clubes.nombre_corto as nombre_club FROM rutinas, clubes WHERE rutinas.id_fase = ".$row_fases['id_fase']." and rutinas.id_club = clubes.id ORDER BY orden";
                $query_run = mysqli_query($connection,$query);
                if(mysqli_num_rows($query_run) > 0){
					while ($row = mysqli_fetch_assoc($query_run)) {
						$nombres = "SELECT group_concat(nadadoras.nombre SEPARATOR ', ') FROM rutinas, rutinas_participantes, nadadoras WHERE nadadoras.id = rutinas_participantes.id_nadadora and rutinas.id = rutinas_participantes.id_rutina and rutinas_participantes.reserva = 'no' and id_rutina = ".$row['id_rutina'];
						$nombres = mysqli_result(mysqli_query($connection,$nombres));
						$class_orden='';
						if($row['orden'] == '-1'){
							$row['orden'] = 'PRESWIMMER';
							$class_orden = 'table-warning';
						}
						else if($row['orden'] == '-2')
						  $row['orden'] = 'EXHIBICIÓN';
						else if($row['orden'] == '1')
						  $class_orden = 'table-success';

                    ?>
                            <tr class="<?php echo $class_orden;?>">
 							   <th class="table-success text-center" scope="row"> <?php echo $row['orden']; ?> </th>
 							   <td class="" scope="row"> <?php echo $row['id_rutina']; ?> </td>
 							   <td class="" scope="row"> <?php echo $row['nombre_club']; ?> </td>
                                <td class=""> <?php echo $nombres;?> </td>
                            </tr>
                            <?php
                      }
                    }
                    else{
                      echo "<tr><td colspan='4'>No se han encontrado registros en la base de datos</td></tr>";
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
