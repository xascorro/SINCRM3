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
            <h5 class="modal-title" id="exampleModalLabel">Añadir fase</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form action="fases_code.php" method="POST" enctype="multipart/form-data">
            <div class="modal-body">
              <div class="row">
                <div class="form-group col-3">
                  <?php
                    $query = "SELECT figuras as competicion_figuras FROM competiciones WHERE id= '".$_SESSION['id_competicion_activa']."'";
                  $query_run = mysqli_query($connection,$query);
                $competicion_figurasl = mysqli_fetch_assoc($query_run);
                    $competicion_figuras = $competicion_figurasl['competicion_figuras'];

                  $query = "SELECT max(orden)+1 as nuevo_orden FROM fases WHERE id_competicion = '".$_SESSION['id_competicion_activa']."'";
                  $query_run = mysqli_query($connection,$query);
                  $row = mysqli_fetch_assoc($query_run);


                  ?>
                  <label for="orden">Orden</label>
                  <input type="number" class="form-control" name="orden" value="<?php echo $row['nuevo_orden']; ?>">
                </div>
                <div class="form-group col">
                  <?php
                    if($competicion_figuras == 'si')
                        include('includes/figura_select_option.php');
                    else
                        include('includes/modalidad_select_option.php');
                  ?>
                </div>
              </div>
              <div class="row">
                <div class="col">
                 <?php
                  include('includes/categoria_select_option.php');
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
        <h4 class="mb-0 font-weight-bold text-primary"><i class="fas fa-fw fa-flag-checkered"></i>Registro de fases para puntuar
          <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addUserProfile">Añadir fase</button> </h4>
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
            if($competicion_figuras == 'si')
                $query = "SELECT fases.id, fases.elementos_coach_card, id_categoria, categorias.nombre as nombre_categoria, edad_minima, edad_maxima, id_figura, id_modalidad, figuras.nombre as nombre_figura, numero, figuras.grado_dificultad, fases.orden as orden FROM fases, categorias, modalidades, figuras WHERE fases.id_categoria = categorias.id and fases.id_figura = figuras.id and fases.id_modalidad = modalidades.id and fases.id_competicion = ".$_SESSION['id_competicion_activa']." ORDER BY orden, fases.id";
            else
                $query = "SELECT fases.id, fases.elementos_coach_card, id_categoria, categorias.nombre as nombre_categoria, edad_minima, edad_maxima, id_modalidad, modalidades.nombre as nombre_modalidad, orden FROM fases, categorias, modalidades WHERE fases.id_categoria = categorias.id and fases.id_modalidad = modalidades.id and fases.id_competicion = ".$_SESSION['id_competicion_activa']." ORDER BY orden, fases.id";

            $query_run = mysqli_query($connection,$query);
            ?>
            <table class="table " id="nodataTable" width="100%" cellspacing="0">
              <thead>
                <tr>
                  <th scope="col">#</th>
                  <th scope="col"><i class="fa-solid fa-list-ol" aria-hidden="true"></i></th>

                  <?php
                    if($competicion_figuras == 'si'){
						echo '<th scope="col">Categoría</th>';
                        echo '<th scope="col">Figura</th>';
                        echo '<th scope="col">Tipo</th>';
                    }else{
                        echo '<th scope="col">Fase</th>';
//                        echo '<th scope="col">El. CC</th>';
                    }
                    ?>
                  <th colspan=2 scope="col" class="text-center">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php
                if(mysqli_num_rows($query_run) > 0){
                  while ($row = mysqli_fetch_assoc($query_run)) {
                    ?>
                    <tr>
                      <th scope="row"> <?php echo $row['id']; ?> </th>
                      <td class="text-center"> <?php echo $row['orden']; ?> </td>
                      <?php
                    if($competicion_figuras == 'si'){
						echo '<td scope="col">'.$row['nombre_categoria'].'</td>';
                        echo '<td scope="col">'.$row['numero'].' - '.$row['nombre_figura'].' GD:'.$row['grado_dificultad'].'</th>';
                        echo '<td scope="col">Tipo</td>';
						if($row['elementos_coach_card']>0){
                        	echo 'TRE';
                        	$icono = '<i class="fa-solid fa-square-root-variable"></i>';
                      	}else{
                        	echo 'FIG';
                        	$icono = '<i class="fa-solid fa-calculator"></i>';
                      	}
						//competicion rutinas
                    }else{
						if($row['elementos_coach_card']>0){
                        	$icono = '<i class="fa-solid fa-square-root-variable"></i>';
                      	}else{
                        	$icono = '<i class="fa-solid fa-calculator"></i>';
                      	}
                        echo '<td scope="col">'.$row['nombre_modalidad'].' '.$row['nombre_categoria'].'</td>';
                    }

                     echo '<td>';
                      if($competicion_figuras == 'si'){
                               if($row['elementos_coach_card'] > 0)
                                    echo '<form action="puntuaciones_lista_figuras_rutinas_tecnicas.php" target="_blank" method="post">';
                                else
                                    echo '<form action="puntuaciones_lista_figuras.php" target="_blank" method="post">';
                        }else{?>
                                <form action="puntuaciones_lista_rutinas.php" target="_blank" method="post">
                         <?php
                        }?>
                          <input type="hidden" name="id_fase" value="<?php echo $row['id']; ?>">
                          <input type="hidden" name="id_figura" value="<?php echo $row['id_figura']; ?>">
                          <input type="hidden" name="id_modalidad" value="<?php echo $row['id_modalidad']; ?>">
                          <input type="hidden" name="id_categoria" value="<?php echo $row['id_categoria']; ?>">
                          <input type="hidden" name="elementos_coach_card" value="<?php echo $row['elementos_coach_card']; ?>">
                          <button class="btn btn-success" type="submit" name="edit_btn"><?php echo $icono;?></btn>
                          </form>
                        </td>
                        <td>
                          <a class="btn btn-primary" target="_blank"href="./informes/informe_puntuaciones.php?titulo=Clasificaci%C3%B3n%20detallada&id_fase=<?php echo $row['id'];?>"><i class="fa-solid fa-file-pdf"></i></a>
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



