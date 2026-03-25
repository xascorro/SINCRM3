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
        <h4 class="mb-0 text-gray-800"><?php echo $numero_figura." - ".$nombre_figura." GD:".$grado_dificultad;?></h4>
      </div>

      <div class="plf-v2">

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
            .plf-v2 .plf-card {
              border-radius: 0.5rem;
              overflow: hidden;
            }
            .plf-v2 .table-responsive {
              max-height: min(72vh, 900px);
            }
            .plf-v2 thead th {
              position: sticky;
              top: 0;
              z-index: 2;
              font-size: 0.72rem;
              text-transform: uppercase;
              letter-spacing: 0.03em;
              vertical-align: middle;
              background: #f8f9fc;
              box-shadow: inset 0 -1px 0 rgba(0,0,0,0.06);
            }
            .plf-v2 tbody tr.plf-row:hover {
              background-color: rgba(78, 115, 223, 0.05);
            }
            .plf-v2 .plf-td-ord {
              font-weight: 700;
              color: #5a5c69;
              width: 3rem;
              vertical-align: middle;
            }
            .plf-v2 .plf-td-id {
              font-family: ui-monospace, monospace;
              font-size: 0.8rem;
              color: #858796;
              vertical-align: middle;
            }
            .plf-v2 .plf-td-nombre {
              vertical-align: middle;
              min-width: 10rem;
            }
            .plf-v2 .plf-nombre strong {
              color: #2e2f37;
              font-weight: 600;
            }
            .plf-v2 .plf-td-juez {
              vertical-align: middle;
              padding: 0.35rem 0.25rem !important;
            }
            .plf-v2 .plf-input-nota {
              text-align: center;
              font-weight: 600;
              border-radius: 0.35rem;
              border: 1px solid #d1d3e2;
              max-width: 4.25rem;
              margin: 0 auto;
              display: block;
            }
            .plf-v2 .plf-input-nota:focus {
              border-color: #4e73df;
              box-shadow: 0 0 0 0.15rem rgba(78, 115, 223, 0.2);
            }
            .plf-v2 .plf-metric {
              font-family: ui-monospace, monospace;
              font-size: 0.85rem;
              font-weight: 600;
              vertical-align: middle;
              text-align: center;
              color: #3a3b45;
              border-left: 1px solid rgba(0,0,0,0.05);
            }
            .plf-v2 .plf-actions {
              vertical-align: middle;
              width: 3.5rem;
              text-align: center;
            }
            .plf-v2 .btn-plf-guardar {
              width: 2.25rem;
              height: 2.25rem;
              padding: 0;
              line-height: 2.25rem;
              border-radius: 50%;
              box-shadow: 0 0.15rem 0.35rem rgba(28, 200, 138, 0.35);
            }
            .plf-v2 .btn-plf-guardar:hover {
              transform: translateY(-1px);
              box-shadow: 0 0.25rem 0.5rem rgba(28, 200, 138, 0.45);
            }
            .plf-v2 .plf-hint {
              font-size: 0.8rem;
              color: #858796;
            }
            @keyframes puntuacionMediaNotaFlash {
              0% { background-color: rgba(255, 193, 7, 0.42); }
              45% { background-color: rgba(25, 135, 84, 0.32); }
              100% { background-color: rgba(25, 135, 84, 0.07); }
            }
            td.puntuacion-celda-flash {
              animation: puntuacionMediaNotaFlash 1s ease-out forwards;
            }
          </style>

          <div class="card shadow plf-card border-left-primary mb-4">
            <div class="card-header py-3 d-flex flex-wrap justify-content-between align-items-center">
              <div>
                <h6 class="m-0 font-weight-bold text-primary">Puntuacion por nadadora</h6>
                <span class="plf-hint mb-0">Introduce notas y pulsa <i class="fa-solid fa-calculator text-success"></i> para guardar la fila. Las celdas <span class="badge badge-warning">J</span> siguen la logica de media del juez reserva.</span>
              </div>
              <span class="badge badge-primary mt-2 mt-md-0">Grado dificultad: <?php echo htmlspecialchars($grado_dificultad, ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <div class="card-body p-0">
          <div class="table-responsive">
            <?php
                $id_fase = (int) $_POST['id_fase'];
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

            $query_run = mysqli_query($connection,$query);
            ?>
            <table class="table table-sm table-hover mb-0 plf-table" id="nodataTable">
                <thead>
                    <tr>
                        <th scope="col" class="text-center">Ord.</th>
                        <th scope="col" class="text-center">Ins.</th>
                        <th scope="col">Participante</th>
                        <?php
                        $query = "SELECT id FROM panel_jueces WHERE id_fase=".$_POST['id_fase'];
                        $jueces = mysqli_query($connection,$query);
                        $i = 0;
                        while($juez = mysqli_fetch_assoc($jueces)){
                            $i++;
                            echo '<th scope="col" class="text-center">J'.$i.'</th>';
                        }
                        ?>
                        <th scope="col" class="text-center"><span class="d-block">S</span></th>
                        <th scope="col" class="text-center"><span class="d-block">Tot.</span></th>
                        <th scope="col" class="text-center"><span class="d-block">Med.</span></th>
                        <th scope="col" class="text-center"><span class="d-block">Nota</span></th>
                        <th scope="col" class="text-center pr-3"></th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if(mysqli_num_rows($query_run) > 0){
                  while ($row = mysqli_fetch_assoc($query_run)) {
                      $tr_class = 'plf-row';
                      if ($row['baja'] == 'si') {
                          $tr_class .= ' table-warning';
                      }
                    ?>
                    <?php
                      $form_id = 'notas-row-' . (int) $row['id'];
                    ?>
                    <tr id="<?php echo $row['id'];?>" class="<?php echo $tr_class; ?>">
                      <th scope="row" class="plf-td-ord text-center"><?php echo $row['orden_primera_fase']; ?></th>
                      <th scope="row" class="plf-td-id text-center"><?php echo $row['id']; ?></th>
                      <td class="plf-td-nombre">
                        <span class="plf-nombre"><strong><?php echo htmlspecialchars($row['apellidos_nadadora'], ENT_QUOTES, 'UTF-8'); ?></strong>, <?php echo htmlspecialchars($row['nombre_nadadora'], ENT_QUOTES, 'UTF-8'); ?></span>
                      </td>
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
                            echo '<td class="plf-td-juez text-center'.$class_j.'"><input form="'.htmlspecialchars($form_id, ENT_QUOTES, 'UTF-8').'" class="form-control form-control-sm plf-input-nota'.$class_j.'" name="nota['.$nj.'][nota]" step="0.1" type="number" value="'.$nota.'"></td>';
                            $judge_hiddens .= '<input type="hidden" name="nota['.$nj.'][id_juez]" value="'.htmlspecialchars($juez['id_juez'], ENT_QUOTES, 'UTF-8').'">';
                            $judge_hiddens .= '<input type="hidden" name="nota['.$nj.'][id_panel_jueces]" value="'.(int) $juez['id'].'">';
                        }
                        echo '<td class="js-sum-s plf-metric">' . $sumatorio . '</td>';
                        echo '<td class="js-nota-total plf-metric">' . $row['nota_total'] . '</td>';
                        echo '<td class="js-nota-media plf-metric">' . htmlspecialchars(puntuaciones_fmt_hasta4($row['nota_media']), ENT_QUOTES, 'UTF-8') . '</td>';
                        echo '<td class="js-nota-final plf-metric">' . htmlspecialchars(puntuaciones_fmt_hasta4($row['nota_final']), ENT_QUOTES, 'UTF-8') . '</td>';
                        ?>
                         <td class="plf-actions pr-3">
                          <form id="<?php echo htmlspecialchars($form_id, ENT_QUOTES, 'UTF-8'); ?>" class="notas" action="puntuaciones_lista_figuras_code.php" method="post" onsubmit="return false;">
                          <input type="hidden" name="ajax" value="1">
                          <input type="hidden" name="id_inscripcion_figuras" value="<?php echo $row['id']; ?>">
                          <input type="hidden" name="id_fase" value="<?php echo $row['id_fase']; ?>">
                          <input type="hidden" name="grado_dificultad" value="<?php echo htmlspecialchars($grado_dificultad, ENT_QUOTES, 'UTF-8'); ?>">
                          <?php echo $judge_hiddens; ?>
                          </form>
                          <button type="button" class="btn btn-success btn-sm btn-plf-guardar btn-puntuar-fila" data-form-id="<?php echo htmlspecialchars($form_id, ENT_QUOTES, 'UTF-8'); ?>" id="puntuar_btn<?php echo $row['id']; ?>" title="Guardar fila"><i class="fa-solid fa-calculator"></i></button>
                        </td>
                        </tr>
                        <?php
                      }
                    }
                    else{
                      echo "<tr><td colspan='20' class='text-center text-muted py-4'>No se han encontrado registros en la base de datos</td></tr>";
                    }
                    ?>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="card-footer bg-white d-flex flex-wrap align-items-center py-3">
              <a class="btn btn-primary btn-sm mr-2 mb-2" target="_blank" href="./puntuaciones_fases_puntuar.php?id_fase=<?php echo $_POST['id_fase'];?>"><i class="fa fa-calculator"></i> Calcular fase</a>
              <a class="btn btn-primary btn-sm mb-2" target="_blank" href="./informes/informe_puntuaciones.php?titulo=Clasificaci%C3%B3n%20detallada&id_fase=<?php echo $_POST['id_fase'];?>"><i class="fa fa-file-pdf"></i> PDF clasificacion</a>
            </div>
          </div>

      </div>


            <!-- template -->
            <?php
            include('includes/scripts.php');
            ?>
			<script src="puntuaciones_lista_figuras.js?v=5"></script>
            <?php
            include('includes/footer.php');
            ?>
