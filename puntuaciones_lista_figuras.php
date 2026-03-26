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

            <!-- FORMULARIO DE PENALIZACIONES -->
            <div class="card mt-4">
              <div class="card-header">
                <h5 class="mb-0">Penalizaciones</h5>
              </div>
              <div class="card-body">
                <!-- Formulario para agregar penalizaciones -->
                <div class="card card-light mb-3">
                  <div class="card-header">
                    <h6 class="mb-0">Agregar penalización</h6>
                  </div>
                  <div class="card-body">
                    <form id="form-agregar-penalizacion" action="puntuaciones_lista_figuras_code.php" method="POST">
                      <input type="hidden" name="id_fase" value="<?php echo $id_fase; ?>">
                      <div class="row">
                        <div class="col-md-4">
                          <label for="select-nadadora">Nadadora</label>
                          <select id="select-nadadora" name="id_inscripcion_figuras" class="form-control form-control-sm" required>
                            <option value="">-- Seleccionar nadadora --</option>
                            <?php
                              $query_nadadoras = "SELECT inscripciones_figuras.id, 
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
                                  ) AS orden_display,
                                  nadadoras.nombre, nadadoras.apellidos
                                  FROM inscripciones_figuras, fases, nadadoras
                                  WHERE inscripciones_figuras.id_fase = ".$id_fase."
                                  AND inscripciones_figuras.id_fase = fases.id
                                  AND inscripciones_figuras.id_nadadora = nadadoras.id
                                  AND fases.id_competicion = ".$_SESSION['id_competicion_activa']."
                                  ORDER BY orden_display, nadadoras.apellidos, nadadoras.nombre";
                              $result_nadadoras = mysqli_query($connection, $query_nadadoras);
                              while($nadadora = mysqli_fetch_assoc($result_nadadoras)) {
                                echo '<option value="'.$nadadora['id'].'">'.$nadadora['orden_display'].' - '.$nadadora['apellidos'].', '.$nadadora['nombre'].'</option>';
                              }
                            ?>
                          </select>
                        </div>
                        <div class="col-md-4">
                          <label for="select-penalizacion">Penalización</label>
                          <select id="select-penalizacion" name="id_penalizacion" class="form-control form-control-sm" required>
                            <option value="">-- Seleccionar penalización --</option>
                            <?php
                              $query_pen_tipos = "SELECT id, codigo, resumen, puntos FROM penalizaciones WHERE id_paneles_tipo=6 ORDER BY codigo";
                              $result_pen_tipos = mysqli_query($connection, $query_pen_tipos);
                              while($pen_tipo = mysqli_fetch_assoc($result_pen_tipos)) {
                                echo '<option value="'.$pen_tipo['id'].'" data-puntos="'.$pen_tipo['puntos'].'">'.$pen_tipo['codigo'].' - '.$pen_tipo['resumen'].' ('.$pen_tipo['puntos'].' pts)</option>';
                              }
                            ?>
                          </select>
                        </div>
                        <div class="col-md-4">
                          <label>&nbsp;</label>
                          <button type="submit" class="btn btn-warning btn-sm btn-block" name="penalizacion_aplicar" value="1">
                            <i class="fas fa-exclamation-triangle"></i> Aplicar penalización
                          </button>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>

                <!-- Listado de penalizaciones aplicadas -->
                <div class="table-responsive">
                  <table class="table table-sm" id="penalizacionesAplicadasTable">
                    <thead>
                      <tr>
                        <th scope="col">Orden</th>
                        <th scope="col">#</th>
                        <th scope="col">Nadadora</th>
                        <th scope="col">Penalización</th>
                        <th scope="col">Puntos</th>
                        <th scope="col" class="text-center">Acción</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                        $query_penalizadas = "SELECT pn.id as id_penalizacion_registro,
                            inscripciones_figuras.id as id_inscripcion,
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
                            ) AS orden,
                            nadadoras.nombre, nadadoras.apellidos,
                            penalizaciones.codigo, penalizaciones.resumen, penalizaciones.puntos
                            FROM penalizaciones_rutinas pn
                            INNER JOIN inscripciones_figuras ON pn.id_inscripcion_figuras = inscripciones_figuras.id
                            INNER JOIN fases ON inscripciones_figuras.id_fase = fases.id
                            INNER JOIN nadadoras ON inscripciones_figuras.id_nadadora = nadadoras.id
                            INNER JOIN penalizaciones ON pn.id_penalizacion = penalizaciones.id
                            WHERE inscripciones_figuras.id_fase = ".$id_fase."
                            AND fases.id_competicion = ".$_SESSION['id_competicion_activa']."
                            ORDER BY orden, nadadoras.apellidos, nadadoras.nombre";
                        $result_penalizadas = mysqli_query($connection, $query_penalizadas);
                        
                        if(mysqli_num_rows($result_penalizadas) > 0) {
                          while($penalizada = mysqli_fetch_assoc($result_penalizadas)) {
                      ?>
                      <tr>
                        <td><?php echo $penalizada['orden']; ?></td>
                        <td><?php echo $penalizada['id_inscripcion']; ?></td>
                        <td><?php echo $penalizada['apellidos'].', '.$penalizada['nombre']; ?></td>
                        <td><?php echo $penalizada['codigo'].' - '.$penalizada['resumen']; ?></td>
                        <td><?php echo $penalizada['puntos']; ?> pts</td>
                        <td class="text-center">
                          <form class="form-borrar-penalizacion" action="puntuaciones_lista_figuras_code.php" method="POST" style="display:inline;">
                            <input type="hidden" name="id_penalizacion_registro" value="<?php echo $penalizada['id_penalizacion_registro']; ?>">
                            <input type="hidden" name="id_fase" value="<?php echo $id_fase; ?>">
                            <button type="submit" class="btn btn-danger btn-sm btn-borrar-penalizacion" name="penalizacion_borrar" onclick="return confirm('¿Borrar esta penalización?');">
                              <i class="fas fa-trash"></i>
                            </button>
                          </form>
                        </td>
                      </tr>
                      <?php
                          }
                        } else {
                          echo '<tr><td colspan="6" class="text-center">No hay penalizaciones aplicadas en esta fase</td></tr>';
                        }
                      ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- template -->
            <?php
            include('includes/scripts.php');
            ?>
			<script src="puntuaciones_lista_figuras.js?v=5"></script>
            <script>
              // Lógica AJAX para agregar penalizaciones
              document.getElementById('form-agregar-penalizacion').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const nadadora = document.getElementById('select-nadadora').value;
                const penalizacion = document.getElementById('select-penalizacion').value;
                const idFase = document.querySelector('input[name="id_fase"]').value;
                
                if(!nadadora || !penalizacion) {
                  alert('Por favor selecciona nadadora y penalización');
                  return false;
                }
                
                const formData = new FormData(this);
                formData.append('penalizacion_aplicar', '1');
                
                fetch('puntuaciones_lista_figuras_code.php', {
                  method: 'POST',
                  body: formData
                })
                .then(response => response.json())
                .then(data => {
                  if(data.success) {
                    // Limpiar los selects
                    document.getElementById('select-nadadora').value = '';
                    document.getElementById('select-penalizacion').value = '';
                    
                    // Actualizar la tabla de penalizaciones
                    const formDataTabla = new FormData();
                    formDataTabla.append('get_penalizaciones_tabla', '1');
                    formDataTabla.append('id_fase', idFase);
                    
                    fetch('puntuaciones_lista_figuras_code.php', {
                      method: 'POST',
                      body: formDataTabla
                    })
                    .then(response => response.json())
                    .then(dataTabla => {
                      if(dataTabla.success) {
                        document.querySelector('#penalizacionesAplicadasTable').innerHTML = dataTabla.html;
                        // Re-inicializar listeners en los botones de borrar
                        initDeletePenalizacionesListeners(idFase);
                      }
                    });
                  } else {
                    alert('Error: ' + (data.message || 'No se pudo aplicar la penalización'));
                  }
                })
                .catch(error => {
                  console.error('Error:', error);
                  alert('Error al aplicar la penalización: ' + error.message);
                });
              });

              // Función para inicializar listeners de borrado
              function initDeletePenalizacionesListeners(idFase) {
                document.querySelectorAll('.form-borrar-penalizacion').forEach(function(form) {
                  form.removeEventListener('submit', handleDeletePenalizacion);
                  form.addEventListener('submit', handleDeletePenalizacion);
                });
              }
              
              // Handler para borrar penalizaciones
              function handleDeletePenalizacion(e) {
                e.preventDefault();
                
                if(!confirm('¿Está seguro de que desea borrar esta penalización?')) {
                  return false;
                }
                
                const idFase = document.querySelector('input[name="id_fase"]').value;
                const formData = new FormData(this);
                formData.append('penalizacion_borrar', '1');
                
                fetch('puntuaciones_lista_figuras_code.php', {
                  method: 'POST',
                  body: formData
                })
                .then(response => response.json())
                .then(data => {
                  if(data.success) {
                    // Actualizar la tabla de penalizaciones
                    const formDataTabla = new FormData();
                    formDataTabla.append('get_penalizaciones_tabla', '1');
                    formDataTabla.append('id_fase', idFase);
                    
                    fetch('puntuaciones_lista_figuras_code.php', {
                      method: 'POST',
                      body: formDataTabla
                    })
                    .then(response => response.json())
                    .then(dataTabla => {
                      if(dataTabla.success) {
                        document.querySelector('#penalizacionesAplicadasTable').innerHTML = dataTabla.html;
                        // Re-inicializar listeners
                        initDeletePenalizacionesListeners(idFase);
                      }
                    });
                  } else {
                    alert('Error: ' + (data.message || 'No se pudo borrar la penalización'));
                  }
                })
                .catch(error => {
                  console.error('Error:', error);
                  alert('Error al borrar la penalización: ' + error.message);
                });
              }
              
              // Inicializar listeners al cargar
              document.addEventListener('DOMContentLoaded', function() {
                const idFase = document.querySelector('input[name="id_fase"]').value;
                initDeletePenalizacionesListeners(idFase);
              });
            </script>
            <?php
            include('includes/footer.php');
            ?>
