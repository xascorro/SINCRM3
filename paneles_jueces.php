<?php

include('security.php');
//control de acceso
$allowedRoles = ['Administrador','Secretario'];
if (!array_key_exists('rol', $_SESSION) || !in_array($_SESSION['rol'], $allowedRoles)) {
   header('Location: '.$_SESSION['startPage']);
   die;
}
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
				include('./includes/puestos_select_option.php');
                ?>
              </div>
              <div class="form-group">
                <?php
                echo "<label for='id_juez'>Juez</label>";
                include('./includes/juez_select_option.php');
                ?>
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
              <div class="form-group row">
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
                  <label for="color">Color</label>
                  <input type="text" class="form-control" name="color" placeholder="#CECECE">
                </div>
              </div>
              <div class="form-group">
                <label for="descripcion">Descripción</label>
                <input type="text" class="form-control" name="descripcion">
              </div>
              
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
              <button type="submit" class="btn btn-primary" name="save_btn_panel">Guardar</button>
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
              $query = "select puesto_juez.id, jueces.nombre, jueces.apellidos, jueces.licencia, puestos_juez.id as id_puestos_juez, puestos_juez.nombre as nombre_puestos_juez, federaciones.nombre_corto from puesto_juez, jueces, puestos_juez, federaciones where puesto_juez.id_juez = jueces.id and puesto_juez.id_puestos_juez = puestos_juez.id and jueces.federacion = federaciones.id and puesto_juez.id_competicion ='".$_SESSION['id_competicion_activa']."'";
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
                      <th scope="row"> <?php echo $row['nombre_puestos_juez']; ?> </th>
                      <td> <?php echo $row['nombre']; ?> </td>
                      <td> <?php echo $row['apellidos']; ?> </td>
                      <td> <?php echo $row['licencia']; ?> </td>
                      <td> <?php echo $row['nombre_corto']; ?> </td>
                      <td>
                        <form action="paneles_jueces_edit.php" method="post">
                          <input type="hidden" name="edit_id" value=" <?php echo $row['id']; ?> ">
                          <input type="hidden" name="id_puestos_juez" value=" <?php echo @$row['id_puestos_juez']; ?> ">
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
                  $query = "SELECT paneles.color, paneles.nombre, paneles.id, paneles.peso, paneles.descripcion, paneles.numero_jueces, paneles_tipo.nombre as tipo_panel, paneles_tipo.id as id_tipo from paneles, paneles_tipo where paneles.id_paneles_tipo = paneles_tipo.id and id_competicion = '".$_SESSION['id_competicion_activa']."'";
                  $query_run = mysqli_query($connection,$query); 
                  ?>
                  <table class="table table-striped" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                      <tr>
                        <th scope="col">#</th>
                        <th scope="col">Nombre</th>
                        <th scope="col">Tipo</th>
                        <th scope="col">Nº Jueces</th>
                        <th scope="col">% Nota</th>
                        <th scope="col">Color</th>
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
                            <td> <?php echo $row['tipo_panel']; ?> </td>
                            <td> <?php echo $row['numero_jueces']; ?> </td>
                            <td> <?php echo $row['peso']; ?> </td>
                            <td style="background-color:<?php echo $row['color']; ?>"> <?php echo $row['color']; ?> </td>
                            <td> <?php echo $row['descripcion']; ?> </td>
                            <td>
                              <form action="paneles_jueces_edit.php" method="post">
                                <input type="hidden" name="edit_id" value=" <?php echo $row['id']; ?> ">
                                <input type="hidden" name="id_paneles_tipo" value=" <?php echo $row['id_tipo']; ?> ">
                                <button class="btn btn-success" type="submit" name="edit_btn_panel">Editar</btn>
                                </form>
                              </td>
                              <td>
                                <form action="paneles_jueces_code.php" method="POST">
                                  <input type="hidden" name="delete_id" value="<?php echo $row['id'] ?>">
                                  <button class="btn btn-danger" type="submit" name="delete_btn_panel">Borrar</btn>
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


<!--Composición de paneles-->
                  <h4 class="mb-0 font-weight-bold text-primary"><i class="fas fa-fw fa-gavel"></i> Composición de paneles</h4>

                 <div class="table-responsive">
            <?php
            if($_SESSION['figuras'] == 'si'){
                $query = "SELECT fases.id as id, id_categoria, categorias.nombre as nombre_categoria, edad_minima, edad_maxima, id_figura, figuras.nombre as nombre_figura, numero, fases.orden FROM fases, categorias, figuras WHERE fases.id_categoria = categorias.id and fases.id_figura = figuras.id and fases.id_competicion = ".$_SESSION['id_competicion_activa']." ORDER BY fases.orden, fases.id";
            }
            else{
                $query = "SELECT fases.id as id, fases.elementos_coach_card, id_categoria, categorias.nombre as nombre_categoria, id_modalidad, modalidades.nombre as nombre, fases.orden FROM fases, categorias, modalidades WHERE fases.id_categoria = categorias.id and fases.id_modalidad = modalidades.id and fases.id_competicion = ".$_SESSION['id_competicion_activa']." ORDER BY orden, fases.id";
            }
            $query_run = mysqli_query($connection,$query);
            ?>

                <?php
                if(mysqli_num_rows($query_run) > 0){
                  while ($row = mysqli_fetch_assoc($query_run)) {
                    ?>
                    <h5>Jueces
                      <?php echo @$row['nombre'].' '.$row['nombre_categoria']." - ".$row['numero']." - ".$row['nombre_figura']." #".$row['id']; ?></h5>
                    <?php
                     if(@$row['elementos_coach_card']>0 or $_SESSION['figuras'] == 'si'){
                        $query = "SELECT paneles.id, numero_jueces, paneles.nombre, paneles_tipo.nombre as panel_tipo from paneles, paneles_tipo where id_paneles_tipo=paneles_tipo.id and obsoleto like 'no' and id_competicion = '".$_SESSION['id_competicion_activa']."'";
                        $query_run2 = mysqli_query($connection,$query);
                        while ($row2 = mysqli_fetch_assoc($query_run2)) {
                        ?>
                        <table class="table " id="dataTable" width="100%" cellspacing="0">
                        <tbody>
                        <form action="paneles_jueces_code.php" action="get">
                      	<input type="hidden" name="id_panel" value="<?php echo $row2['id']?>">
                      	<input type="submit" class="btn btn-round btn-primary" name="panel_jueces_clonar_btn" value="Clonar panel" >
                      </form>
                    <tr>
                      <td> <?php echo $row2['nombre']." ".$row2['panel_tipo']; ?> </td>
                    </tr>
                       <?php
                            for($x=1;$x<=$row2['numero_jueces'];$x++){
                                $query = "SELECT * from panel_jueces WHERE id_panel = ".$row2['id']." and numero_juez = ".$x." and id_fase = ".$row['id'];
                                echo "<tr><td>";
                                $_POST['id_juez'] = @mysqli_fetch_assoc(mysqli_query($connection,$query))['id_juez'];
                                $id = @mysqli_fetch_assoc(mysqli_query($connection,$query))['id'];
                                ?>
                                <form action="paneles_jueces_code.php" method="POST">
                                <div class="form-group row">
                                <div class="col">
                                <?php
                                include('./includes/juez_select_option.php');?>
                                <input type="hidden" name="id" value=" <?php echo $id; ?> ">
                                <input type="hidden" name="numero_juez" value=" <?php echo $x; ?> ">
                                <input type="hidden" name="id_panel" value=" <?php echo $row2['id']; ?> ">
                                <input type="hidden" name="id_fase" value=" <?php echo $row['id']; ?> ">
<!--                                <input type="hidden" name="id_juez" value=" <?php echo $_POST['id_juez']; ?> ">-->
                                </div>
                                <div class="col">
                                <button type="submit" class="btn btn-primary" name="panel_jueces_save_btn">Guardar</button>
                                </div>
                            </div>
                                </form>
                                </td></tr>
                                <?php
                            }
                        ?>
                         </tbody>
                        </table>

                        <?php
                        }
                     }else {
$query = "SELECT paneles.id, numero_jueces, paneles.nombre, paneles_tipo.nombre as panel_tipo from paneles, paneles_tipo where id_paneles_tipo=paneles_tipo.id and obsoleto like 'si' and puntua like 'si' and id_competicion = '".$_SESSION['id_competicion_activa']."'";
                        $query_run2 = mysqli_query($connection,$query);
                        while ($row2 = mysqli_fetch_assoc($query_run2)) {
                        ?>
                        <table class="table " id="dataTable" width="100%" cellspacing="0">
                        <tbody>
                    <tr>
                      <td> <?php echo $row2['nombre']." ".$row2['panel_tipo']; ?> </td>
                    </tr>
                       <?php
                            for($x=1;$x<=$row2['numero_jueces'];$x++){
                                $query = "SELECT * from panel_jueces WHERE id_panel = ".$row2['id']." and numero_juez = ".$x." and id_fase = ".$row['id'];
                                echo "<tr><td>";
                                $_POST['id_juez'] = @mysqli_fetch_assoc(mysqli_query($connection,$query))['id_juez'];
                                $id = @mysqli_fetch_assoc(mysqli_query($connection,$query))['id'];
                                ?>
                                <form action="paneles_jueces_code.php" method="POST">
                                <div class="form-group row">
                                <div class="col">
                                <?php
                                include('./includes/juez_select_option.php');?>
                                <input type="hidden" name="id" value=" <?php echo $id; ?> ">
                                <input type="hidden" name="numero_juez" value=" <?php echo $x; ?> ">
                                <input type="hidden" name="id_panel" value=" <?php echo $row2['id']; ?> ">
                                <input type="hidden" name="id_fase" value=" <?php echo $row['id']; ?> ">
<!--                                <input type="hidden" name="id_juez" value=" <?php echo $_POST['id_juez']; ?> ">-->
                                </div>
                                <div class="col">
                                <button type="submit" class="btn btn-primary" name="panel_jueces_save_btn">Guardar</button>
                                </div>
                            </div>
                                </form>
                                </td></tr>
                                <?php
                            }
                        ?>
                         </tbody>
                        </table>

                        <?php
                        }                     }

                      }

                    }
                    else{
                      echo "<tr><td colspan='10'>No se han encontrado registros en la base de datos</td></tr>";
                    }
                    ?>

              </div>
                  </div>


                  <!-- template -->
                  <?php
                  include('includes/scripts.php');
                  include('includes/footer.php');
                  ?>



