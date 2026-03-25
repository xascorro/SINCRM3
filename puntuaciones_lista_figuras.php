<?php
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



    <!-- Begin Page Content -->
    <div class="container-fluid">
      <!-- Titulo página y pdf -->
      <?php
        $query = "SELECT categorias.nombre as categoria, modalidades.nombre as modalidad, figuras.nombre as figura, numero, grado_dificultad FROM fases, categorias, modalidades, figuras WHERE fases.id=".$_POST['id_fase']." and categorias.id = fases.id_categoria and modalidades.id = fases.id_modalidad and figuras.id = fases.id_figura";
        $nombres = mysqli_fetch_assoc(mysqli_query($connection, $query));
        $nombre_modalidad = $nombres['modalidad'];
        $nombre_categoria = $nombres['categoria'];
        $numero_figura = $nombres['numero'];
        $nombre_figura = $nombres['figura'];
        $grado_dificultad = $nombres['grado_dificultad'];

        ?>
      <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h4 class="mb-0 font-weight-bold text-primary"><i class="fas fa-fw fa-flag-checkered"></i>Puntuar: <?php echo $nombre_modalidad." ".$nombre_categoria ?></h4>
        <h4><?php echo $numero_figura." - ".$nombre_figura." GD:".$grado_dificultad;?></h4>


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
          if (!function_exists('puntuaciones_fmt_hasta4')) {
            function puntuaciones_fmt_hasta4($v) {
              if ($v === null || $v === '') {
                return '';
              }
              $n = round((float) $v, 4);
              $s = sprintf('%.4f', $n);
              return rtrim(rtrim($s, '0'), '.');
            }
          }
          ?>

          <style>
            @keyframes puntuacionMediaNotaFlash {
              0% { background-color: rgba(255, 193, 7, 0.42); }
              45% { background-color: rgba(25, 135, 84, 0.32); }
              100% { background-color: rgba(25, 135, 84, 0.07); }
            }
            td.puntuacion-celda-flash {
              animation: puntuacionMediaNotaFlash 1s ease-out forwards;
            }
          </style>

          <div class="table-responsive">
            <?php
                $id_fase = (int) $_POST['id_fase'];
                // Columna Orden: siempre el numero de la primera fase del bloque (misma competicion/categoria/modalidad).
                // Listado: ordenado por el orden de ESTA fase (orden de salida en la fase que se puntua).
                $query = "SELECT inscripciones_figuras.id, inscripciones_figuras.baja,
                    inscripciones_figuras.orden AS orden_fase_actual,
                    COALESCE(
                        (SELECT if2.orden FROM inscripciones_figuras if2
                         WHERE if2.id_nadadora = inscripciones_figuras.id_nadadora
                           AND if2.id_fase = (
                               SELECT f0.id FROM fases f0
                               WHERE f0.id_competicion = fases.id_competicion
                                 AND f0.id_categoria = fases.id_categoria
                                 AND IFNULL(f0.id_modalidad,'') = IFNULL(fases.id_modalidad,'')
                               ORDER BY f0.orden ASC, f0.id ASC
                               LIMIT 1
                           )
                         LIMIT 1),
                        inscripciones_figuras.orden
                    ) AS orden_primera_fase,
                    inscripciones_figuras.id_nadadora, nota_total, nota_media, nota_final,
                    nadadoras.nombre as nombre_nadadora, nadadoras.apellidos as apellidos_nadadora,
                    categorias.nombre as nombre_categoria, inscripciones_figuras.id_fase, fases.elementos_coach_card
                    FROM inscripciones_figuras, fases, modalidades, categorias, nadadoras
                    WHERE fases.id = ".$id_fase."
                    AND inscripciones_figuras.id_fase = fases.id
                    AND fases.id_modalidad = modalidades.id
                    AND fases.id_categoria = categorias.id
                    AND inscripciones_figuras.id_nadadora = nadadoras.id
                    AND fases.id_competicion = ".$_SESSION['id_competicion_activa']."
                    ORDER BY inscripciones_figuras.orden, inscripciones_figuras.id, fases.orden, fases.id";
//			  echo $query;

            $query_run = mysqli_query($connection,$query);
            ?>
            <table class="table table-sm" id="nodataTable">
                <thead>
                    <tr>
                        <th scope="col">Orden</th>
                        <th scope="col">#</th>
                        <th scope="col">Nadadora</th>
                        <?php
                        $query = "SELECT id FROM panel_jueces WHERE id_fase=".$_POST['id_fase'];
                        $jueces = mysqli_query($connection,$query);
                        $i = 0;
                        while($juez = mysqli_fetch_assoc($jueces)){
                            $i++;
                            echo '<th scope="col" style="text-align:center">J'.$i.'</th>';
                        }
                        ?>
                        <th scope="col">S</th>
                        <th scope="col">Total</th>
                        <th scope="col">Media</th>
                        <th scope="col">Nota</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if(mysqli_num_rows($query_run) > 0){
                  while ($row = mysqli_fetch_assoc($query_run)) {
                      if($row['baja']=='si'){
                          $class=' class=bg-warning';
                      }else{
                            $class='';
                      }
                    ?>
                    <?php
                      $form_id = 'notas-row-' . (int) $row['id'];
                    ?>
                    <tr id="<?php echo $row['id'];?>" <?php echo $class;?>>
                      <th scope="row" > <?php echo $row['orden_primera_fase']; ?> </th>
                      <th scope="row" > <?php echo $row['id']; ?> </th>
                      <td > <?php echo $row['apellidos_nadadora'].', '.$row['nombre_nadadora']; ?> </td>
                          <?php
                        $query = "SELECT id, id_panel, numero_juez, id_juez FROM panel_jueces WHERE id_fase=".$_POST['id_fase'];
                        $jueces = mysqli_query($connection,$query);
                        $i = 0;
                        $sumatorio = 0;
                        $judge_hiddens = '';
                        while($juez = mysqli_fetch_assoc($jueces)){
                            $nota = 0;
							if($juez['id_juez'] == '108'){
								$class_j = ' table-warning';
							}else
								$class_j = '';
                            $query = "SELECT nota FROM puntuaciones_jueces WHERE id_panel_juez= ".$juez['id']." and id_inscripcion_figuras = ".$row['id'];
                            $nota = mysqli_result(mysqli_query($connection,$query),0);
                            $i++;
							$sumatorio += $nota;
                            $nj = (int) $juez['numero_juez'];
                            echo '<td class='.$class_j.'><input form="'.htmlspecialchars($form_id, ENT_QUOTES, 'UTF-8').'" class="form-control form-control-sm'.$class_j.'" size="2" name="nota['.$nj.'][nota]" step="0.1" type="number" value="'.$nota.'"></td>';
                            $judge_hiddens .= '<input type="hidden" name="nota['.$nj.'][id_juez]" value="'.htmlspecialchars($juez['id_juez'], ENT_QUOTES, 'UTF-8').'">';
                            $judge_hiddens .= '<input type="hidden" name="nota['.$nj.'][id_panel_jueces]" value="'.(int) $juez['id'].'">';
                        }
                        echo '<td class="js-sum-s">' . $sumatorio . '</td>';
                        echo '<td class="js-nota-total">' . $row['nota_total'] . '</td>';
                        echo '<td class="js-nota-media">' . htmlspecialchars(puntuaciones_fmt_hasta4($row['nota_media']), ENT_QUOTES, 'UTF-8') . '</td>';
                        echo '<td class="js-nota-final">' . htmlspecialchars(puntuaciones_fmt_hasta4($row['nota_final']), ENT_QUOTES, 'UTF-8') . '</td>';
                        ?>
                         <td class="text-nowrap">
                          <form id="<?php echo htmlspecialchars($form_id, ENT_QUOTES, 'UTF-8'); ?>" class="notas" action="puntuaciones_lista_figuras_code.php" method="post" onsubmit="return false;">
                          <input type="hidden" name="ajax" value="1">
                          <input type="hidden" name="id_inscripcion_figuras" value="<?php echo $row['id']; ?>">
                          <input type="hidden" name="id_fase" value="<?php echo $row['id_fase']; ?>">
                          <input type="hidden" name="grado_dificultad" value="<?php echo htmlspecialchars($grado_dificultad, ENT_QUOTES, 'UTF-8'); ?>">
                          <?php echo $judge_hiddens; ?>
                          </form>
                          <button class="form-control form-control-sm btn btn-success btn-puntuar-fila" type="button" data-form-id="<?php echo htmlspecialchars($form_id, ENT_QUOTES, 'UTF-8'); ?>" id="puntuar_btn<?php echo $row['id']; ?>"><i class="fa-solid fa-calculator"></i></button>
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
              <a class="btn btn-primary" target="_blank"href="./puntuaciones_fases_puntuar.php?id_fase=<?php echo $_POST['id_fase'];?>"><i class="fa fa-calculator"></i> Calcular</a> &nbsp;
          <a class="btn btn-primary" target="_blank"href="./informes/informe_puntuaciones.php?titulo=Clasificaci%C3%B3n%20detallada&id_fase=<?php echo $_POST['id_fase'];?>"><i class="fa fa-file-pdf"></i> Descargar</a>
            </div>


            <!-- template -->
            <?php
            include('includes/scripts.php');
            ?>
			<script src="puntuaciones_lista_figuras.js?v=5"></script>
            <?php
            include('includes/footer.php');
            ?>
