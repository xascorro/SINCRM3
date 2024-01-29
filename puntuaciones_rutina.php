<?php
//FACTORIZACION
$f_chomu = 1.0; //leer de la DB más adelante
$factor_Performance = 1;
$factor_Transitions = 1;
$factor_hybrid = 1.0;
$factor_acro = 0.5;
$factor_tre = 0.5;
//FIN FACTORIZACION


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

    <?php
    if(isset($_POST['id_rutina']))
        $id_rutina = $_POST['id_rutina'];
    else{
        $id_rutina = $_GET['id_rutina'];
    }
      $query = "SELECT fases.id, fases.f_chomu FROM fases, rutinas WHERE rutinas.id='$id_rutina' and fases.id=id_fase";
    $id_fase = mysqli_query($connection,$query);
      $id_fase = mysqli_fetch_assoc($id_fase)['id'];
    $f_chomu = mysqli_query($connection,$query);
      $f_chomu = mysqli_fetch_assoc($f_chomu)['f_chomu'];

//    if(isset($_POST['id_fase']))
//        $id_fase = $_POST['id_fase'];
//    else
//        $id_fase = $_GET['id_fase'];
     if(isset($_POST['id_modalidad']))
        $id_modalidad = $_POST['id_modalidad'];
    else
        $iid_modalidad = $_GET['id_modalidad'];
    $nombre_modalidad = $_POST['nombre_modalidad'];
    $nombre_categoria = $_POST['nombre_categoria'];
    $nombre_club = $_POST['nombre_club'];
    $nombre_rutina = $_POST['nombre_rutina'];
      ?>

    <!-- Begin Page Content -->
    <div class="container-fluid">

      <!-- Titulo página y pdf -->
      <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h4 class="mb-0 font-weight-bold text-primary"><i class="fas fa-fw fa-flag-checkered"></i>Puntuar <?php echo $nombre_modalidad.' '.$nombre_categoria.' '.$nombre_club.' '.$nombre_rutina; ?>
          <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addUserProfile">Generar resultados</button> <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addUserProfile">Resultados en pdf</button> </h4>

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
              //selecciono los elementos excluyendo hibridos de tipo 3 = transiciones
            $query = "SELECT hibridos_rutina.id, id_rutina , elemento, tipo, texto, nombre, color FROM hibridos_rutina, tipo_hibridos WHERE id_rutina = '$id_rutina' and tipo like 'part' and texto not like '3' and texto=tipo_hibridos.id order by elemento";

            $query_run = mysqli_query($connection,$query);
            ?>
            <table class="table " id="dataTable" width="100%" cellspacing="0">
              <thead>
                <tr>
                    <th colspan=3></th>
                    <th colspan=3><h4>Ejecución</h4></th>
                    <th colspan=3></th>
                </tr>
                <tr>
                  <th scope="col">Elemento</th>
                  <th scope="col">Aplicar BM</th>
                  <th scope="col">DD</th>
                  <?php
                    $query_jueces = "SELECT * from panel_jueces WHERE id_fase=$id_fase and id_panel in (SELECT id from paneles where id_paneles_tipo = 1 and id_competicion=".$_SESSION['id_competicion_activa'].") order by numero_juez";
                    $query_run_jueces = mysqli_query($connection,$query_jueces);
                    while ($row_jueces = mysqli_fetch_assoc($query_run_jueces)) {
                        echo  "<th scope='col'>J".$row_jueces['numero_juez']."</th>";

                    }

                    ?>
                 <th scope="col">X̅</th>
                  <th scope="col">P</th>
                </tr>
              </thead>
              <tbody>
                <?php
                if(mysqli_num_rows($query_run) > 0){
                    $tab_index=1;
                  while ($row = mysqli_fetch_assoc($query_run)) {
                    ?>
                    <tr>
                     <?php
                      if ($row['nombre'] == 'HYBRID')
                        $factor = $factor_hybrid;
                      elseif ($row['nombre'] == 'ACROBATIC')
                        $factor = $factor_acro;
                      elseif ($row['nombre'] == 'TRE')
                        $factor = $factor_tre;
                      ?>
                      <th scope="row" style="background-color:<?php echo $row['color'];?>"> <?php echo $row['nombre'].$row['texto'].' F:'.$factor; ?> </th>

                        <form action="puntuaciones_rutina_code.php" method="post">
                      <?php
                      $query = "SELECT valor FROM hibridos_rutina WHERE tipo like 'total' and id_rutina=$id_rutina and elemento=".$row['elemento'];
                      $dd = mysqli_fetch_assoc(mysqli_query($connection,$query))['valor'];
                      $query = "SELECT sum(valor) FROM hibridos_rutina WHERE tipo like 'basemark' and id_rutina=$id_rutina and elemento=".$row['elemento'];
                      $basemark = mysqli_fetch_assoc(mysqli_query($connection,$query))['sum(valor)'];
                      $query = "SELECT llevado_BM FROM puntuaciones_elementos WHERE  id_rutina=$id_rutina and elemento=".$row['elemento'];
                      $llevado_BM = mysqli_fetch_assoc(mysqli_query($connection,$query))['llevado_BM'];
                      if($llevado_BM == 'si')
                          $check_BM = ' checked ';
                      else
                          $check_BM = ' ';

                      ?>
                        <td> <?php echo $basemark;?><input type="checkbox" <?php echo $check_BM;?> value='<?php echo $basemark;?>' name="BM<?php echo $row['elemento'];?>">
                        </td>
                        <td> <?php echo $dd;?> </td>
                        <input type="hidden" name="dd<?php echo $row['elemento'];?>" value="<?php echo $dd; ?>">
                        <input type="hidden" name="factor<?php echo $row['elemento'];?>" value="<?php echo $factor; ?>">



                      <?php
                      $query_jueces = "SELECT * from panel_jueces WHERE id_fase=$id_fase and id_panel in (SELECT id from paneles where id_paneles_tipo = 1 and id_competicion=".$_SESSION['id_competicion_activa'].") order by numero_juez";
                    $query_run_jueces = mysqli_query($connection,$query_jueces);
                    while ($row_jueces = mysqli_fetch_assoc($query_run_jueces)) {

                        $query = "SELECT nota, id, nota_menor, nota_mayor FROM puntuaciones_jueces WHERE id_rutina='$id_rutina' and id_panel_juez=".$row_jueces['id']." and id_elemento=".$row['elemento'];
                        $nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['nota'];
                        $id = mysqli_fetch_assoc(mysqli_query($connection,$query))['id'];
                        $nota_menor = mysqli_fetch_assoc(mysqli_query($connection,$query))['nota_menor'];
                        $nota_mayor = mysqli_fetch_assoc(mysqli_query($connection,$query))['nota_mayor'];
                        $style='';
                        if($nota_menor == 'si' or $nota_mayor == 'si')
                            $style = 'style="text-decoration:line-through"';
                        echo  "<th scope='col'><input name='notaE".$row['elemento']."J".$row_jueces['numero_juez']."' tabindex='".($tab_index+($row_jueces['numero_juez']*9))."' type=number step=0.25 class=form-control value=".$nota." ".$style."></th>";
                        ?>
                        <input type="hidden" name="id_panel_juez<?php echo $row_jueces['numero_juez'];?>" value="<?php echo $row_jueces['id']; ?>">
                        <?php
                    }
                    $tab_index++;

                    $query = "SELECT DISTINCT nota_media, nota FROM puntuaciones_elementos WHERE elemento= ".$row['elemento']." and id_rutina=$id_rutina";
                    $nota = mysqli_fetch_assoc(mysqli_query($connection,$query));

                      ?>
                        <td><?php echo $nota['nota_media'];?></td>
                        <td><?php echo $nota['nota'];?></td>
                        </tr>


                        <?php
                      $nota_elementos = $nota_elementos + $nota['nota'];
                      }

                    ?>
                    <tr>
                        <td colspan=8></td>
                        <th>Nota</th>
                        <th><?php echo $nota_elementos;?></th>
                    </tr>

                        <tr>
                            <th colspan=3></th>
                            <th colspan=4><h4>Errores de Sincronización</h4></th>
                            <th colspan=3></th>
                        </tr>
                    <?php
                    $query = "SELECT * from errores_sincronizacion WHERE id_rutina=$id_rutina";
                    $errores_sincronizacion = mysqli_fetch_assoc(mysqli_query($connection,$query));
                    $penalizacion_sincronizacion = -($errores_sincronizacion['errores_pequenos']*0.1 + $errores_sincronizacion['errores_obvios']*0.5 + $errores_sincronizacion['errores_mayores']*3);
                    $tab_index=100;
                    ?>
                        <tr>
                            <td colspan="2">
                            <label for='errores_pequenos'>Pequeños (0.1)</label>
                            <input tabindex=<?php echo $tab_index;?> class=form-control type=number id='errores_pequenos' name='errores_pequenos' value='<?php echo $errores_sincronizacion['errores_pequenos'];?>'>
                            </td>
                            <td colspan="3">
                            <label for='errores_obvios'>Obvios (0.5)</label>
                            <input tabindex=<?php echo $tab_index;?> class=form-control type=number id='errores_obvios' name='errores_obvios'  value='<?php echo $errores_sincronizacion['errores_obvios'];?>'>
                            </td>
                            <td colspan="2">
                            <label for='errores_mayores'>Mayores (3)</label>
                            <input tabindex=<?php echo $tab_index;?> class=form-control type=number id='errores_mayores' name='errores_mayores'  value='<?php echo $errores_sincronizacion['errores_mayores'];?>'>
                            </td>
                            <td></td>
                            <th>Errores</th>
                            <th><?php echo $penalizacion_sincronizacion;?></th>
                        </tr>
                        <tr>
                        <td colspan=7></td>
                        <th colspan="2">Nota Elementos</th>
                        <th><?php
                        if(($nota_elementos+$penalizacion_sincronizacion)<0)
                            echo '0.0000';
                        else
                            echo $nota_elementos+$penalizacion_sincronizacion;?></th>
                    </tr>
<!--                       IMPRESIÓN ARTÍSTICA-->
                <tr>
                    <th colspan=3></th>
                    <th colspan=3><h4>Impresión Artística</h4></th>
                    <th colspan=3></th>
                </tr>
                      <tr>
                      <td></td>
                      <th>F</th>
                       <?php
                    $query_jueces = "SELECT * from panel_jueces WHERE id_fase=$id_fase and id_panel in (SELECT id from paneles where id_paneles_tipo = 2 and id_competicion=".$_SESSION['id_competicion_activa'].") order by numero_juez";
                    $query_run_jueces = mysqli_query($connection,$query_jueces);
                    while ($row_jueces = mysqli_fetch_assoc($query_run_jueces)) {
                        echo  "<th scope='col'>J".$row_jueces['numero_juez']."</th>";

                    }
                    echo "<th></th><th></th><th>P</th>";
                    echo "<tr>";
                    echo "<th>ChoMu</th>";
                    echo "<td>$f_chomu</td>";
                    ?>
                    <input type="hidden" name="f_chomu" value="<?php echo $f_chomu; ?>">
                    <?php
                    $query_jueces = "SELECT * from panel_jueces WHERE id_fase=$id_fase and id_panel in (SELECT id from paneles where id_paneles_tipo = 2 and id_competicion=".$_SESSION['id_competicion_activa'].") order by numero_juez";
                    $query_run_jueces = mysqli_query($connection,$query_jueces);
                    while ($row_jueces = mysqli_fetch_assoc($query_run_jueces)) {
                        $query = "SELECT nota, id, nota_menor, nota_mayor FROM puntuaciones_jueces WHERE id_rutina='$id_rutina' and id_panel_juez=".$row_jueces['id']." and tipo_ia='ChoMu'";
                        $nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['nota'];
                        $id = mysqli_fetch_assoc(mysqli_query($connection,$query))['id'];
                        $nota_menor = mysqli_fetch_assoc(mysqli_query($connection,$query))['nota_menor'];
                        $nota_mayor = mysqli_fetch_assoc(mysqli_query($connection,$query))['nota_mayor'];
                        $style='';
                        if($nota_menor == 'si' or $nota_mayor == 'si')
                            $style = 'style="text-decoration:line-through"';
                        echo  "<th scope='col'><input name='notaChoMuJ".$row_jueces['numero_juez']."' tabindex='".($tab_index+($row_jueces['numero_juez']*5))."' type=number step=0.25 class=form-control value=".$nota." ".$style."></th>";
                        ?>
                        <input type="hidden" name="id_panel_juez_ChoMu<?php echo $row_jueces['numero_juez'];?>" value="<?php echo $row_jueces['id']; ?>">
                        <?php
                    }
                    $tab_index++;
                    $query = "SELECT DISTINCT nota_media, nota FROM puntuaciones_elementos WHERE tipo_ia like 'ChoMu' and id_rutina=$id_rutina";
                    $nota = mysqli_fetch_assoc(mysqli_query($connection,$query));

                      ?>
                        <td></td>
                        <td></td>
                        <td><?php echo $nota['nota'];?></td>
                        </tr>


                        <?php
                      $nota_ia = $nota_ia + $nota['nota'];
                    echo "<tr>";
                    echo "<th>Performance</th>";
                    echo "<td>$factor_Performance</td>";
                    ?>
                    <input type="hidden" name="factor_Performance" value="<?php echo $factor_Performance; ?>">
                    <?php
                    $query_jueces = "SELECT * from panel_jueces WHERE id_fase=$id_fase and id_panel in (SELECT id from paneles where id_paneles_tipo = 2 and id_competicion=".$_SESSION['id_competicion_activa'].") order by numero_juez";
                    $query_run_jueces = mysqli_query($connection,$query_jueces);
                    while ($row_jueces = mysqli_fetch_assoc($query_run_jueces)) {
                        $query = "SELECT nota, id, nota_menor, nota_mayor FROM puntuaciones_jueces WHERE id_rutina='$id_rutina' and id_panel_juez=".$row_jueces['id']." and tipo_ia='Performance'";
                        $nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['nota'];
                        $id = mysqli_fetch_assoc(mysqli_query($connection,$query))['id'];
                        $nota_menor = mysqli_fetch_assoc(mysqli_query($connection,$query))['nota_menor'];
                        $nota_mayor = mysqli_fetch_assoc(mysqli_query($connection,$query))['nota_mayor'];
                        $style='';
                        if($nota_menor == 'si' or $nota_mayor == 'si')
                            $style = 'style="text-decoration:line-through"';
                        echo  "<th scope='col'><input name='notaPerformanceJ".$row_jueces['numero_juez']."' tabindex='".($tab_index+($row_jueces['numero_juez']*5))."' type=number step=0.25 class=form-control value=".$nota." ".$style."></th>";
                        ?>
                        <input type="hidden" name="id_panel_juez_Performance<?php echo $row_jueces['numero_juez'];?>" value="<?php echo $row_jueces['id']; ?>">
                        <?php
                    }
                    $tab_index++;
                    $query = "SELECT DISTINCT nota_media, nota FROM puntuaciones_elementos WHERE tipo_ia like 'Performance' and id_rutina=$id_rutina";
                    $nota = mysqli_fetch_assoc(mysqli_query($connection,$query));

                      ?>
                        <td></td>
                        <td></td>
                        <td><?php echo $nota['nota'];?></td>
                        </tr>

                        <?php
                        $nota_ia = $nota_ia + $nota['nota'];


                    echo "<tr>";
                    echo "<th>Transitions</th>";
                    echo "<td>$factor_Transitions</td>";
                    ?>
                    <input type="hidden" name="factor_Transitions" value="<?php echo $factor_Transitions; ?>">
                    <?php
                    $query_jueces = "SELECT * from panel_jueces WHERE id_fase=$id_fase and id_panel in (SELECT id from paneles where id_paneles_tipo = 2 and id_competicion=".$_SESSION['id_competicion_activa'].") order by numero_juez";
                    $query_run_jueces = mysqli_query($connection,$query_jueces);
                    while ($row_jueces = mysqli_fetch_assoc($query_run_jueces)) {
                        $query = "SELECT nota, id, nota_menor, nota_mayor FROM puntuaciones_jueces WHERE id_rutina='$id_rutina' and id_panel_juez=".$row_jueces['id']." and tipo_ia='Transitions'";
                        $nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['nota'];
                        $id = mysqli_fetch_assoc(mysqli_query($connection,$query))['id'];
                        $nota_menor = mysqli_fetch_assoc(mysqli_query($connection,$query))['nota_menor'];
                        $nota_mayor = mysqli_fetch_assoc(mysqli_query($connection,$query))['nota_mayor'];
                        $style='';
                        if($nota_menor == 'si' or $nota_mayor == 'si')
                            $style = 'style="text-decoration:line-through"';
                        echo  "<th scope='col'><input name='notaTransitionsJ".$row_jueces['numero_juez']."' tabindex='".($tab_index+($row_jueces['numero_juez']*5))."' type=number step=0.25 class=form-control value=".$nota." ".$style."></th>";
                        ?>
                        <input type="hidden" name="id_panel_juez_Transitions<?php echo $row_jueces['numero_juez'];?>" value="<?php echo $row_jueces['id']; ?>">
                        <?php
                    }
                    $tab_index++;
                    $query = "SELECT DISTINCT nota_media, nota FROM puntuaciones_elementos WHERE tipo_ia like 'Transitions' and id_rutina=$id_rutina";
                    $nota = mysqli_fetch_assoc(mysqli_query($connection,$query));

                      ?>
                        <td></td>
                        <td></td>
                        <td><?php echo $nota['nota'];?></td>
                        <?php
                        $nota_ia = $nota_ia + $nota['nota'];
                        ?>
                        </tr>
                        <tr>
                        <td colspan="7"></td>
                        <th colspan="2">Nota IA</th>
                        <th><?php echo $nota_ia;?></th>
                        </tr>
                        <tr style="background-color:#1cc88a"><th colspan=5></th>
                        <th colspan="3" style="background-color:#1cc88a"><h1>
                            Nota Rutina
                        </h1></th>
                        <th colspan="2"><h1>
                            <?php
                    $query = "SELECT nota_final FROM rutinas WHERE id=$id_rutina";
                    $nota = mysqli_fetch_assoc(mysqli_query($connection,$query));
                    echo $nota['nota_final'];
                    ?>
            </h1></th></tr>

<!--GUARDAR-->
                        <tr>
                         <td colspan=2>
                          <input type="hidden" name="edit_id" value="<?php echo $id; ?>">
                          <input type="hidden" name="id_fase" value="<?php echo $id_fase; ?>">
                          <input type="hidden" name="id_club" value="<?php echo $row['id_club']; ?>">
                          <input type="hidden" name="id_rutina" value="<?php echo $id_rutina; ?>">
                          <button tabindex=1000 class="btn btn-primary" type="submit" name="save_btn"><i class="fas fa-save"></i> Guardar Notas</btn>
                        </td>
                        </form>

                        <?php

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
<script type="text/javascript" src="puntuaciones_rutina.js"></script>


