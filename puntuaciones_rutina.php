<?php
//FACTORIZACION
//se lee de la base de datos
//FIN FACTORIZACION
//VALOR ERRORES SINCRONIZACIÓN
//	se lee de la base de datos
//FIN VALOR ERRORES SINCRONIZACIÓN


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
	$query = "SELECT fases.id, fases.f_chomu, f_performance, f_transitions, f_hybrid, f_acro, f_tre, error_xs, error_ob, error_xl FROM fases, rutinas WHERE rutinas.id='$id_rutina' and fases.id=id_fase";
	$fase = mysqli_query($connection, $query);
	$fase = mysqli_fetch_assoc($fase);
	$id_fase = $fase['id'];
	$f_chomu = $fase['f_chomu'];
	$f_performance = $fase['f_performance'];
	$f_transitions = $fase['f_transitions'];
	$f_hybrid = $fase['f_hybrid'];
	$f_acro = $fase['f_acro'];
	$f_tre = $fase['f_tre'];
	$error_xs = $fase['error_xs'];
	$error_ob = $fase['error_ob'];
	$error_xl = $fase['error_xl'];

//    if(isset($_POST['id_fase']))
//        $id_fase = $_POST['id_fase'];
//    else
//        $id_fase = $_GET['id_fase'];
     if(isset($_POST['id_modalidad']))
        $id_modalidad = $_POST['id_modalidad'];
    else
        $id_modalidad = $_GET['id_modalidad'];
    $nombre_modalidad = $_POST['nombre_modalidad'];
    $nombre_categoria = $_POST['nombre_categoria'];
    $nombre_club = $_POST['nombre_club'];
    $nombre_rutina = $_POST['nombre_rutina'];

		$query = "SELECT rutinas.id, rutinas.orden as orden, rutinas.nombre as nombre_rutina, rutinas.id_club, baja, clubes.nombre_corto as nombre_club, modalidades.nombre as nombre_modalidad, categorias.nombre as nombre_categoria, rutinas.id_fase, fases.elementos_coach_card, modalidades.id as id_modalidad, rutinas.dd_total FROM rutinas, fases, modalidades, categorias, clubes WHERE rutinas.id = ".$id_rutina." and rutinas.id_fase = fases.id and fases.id_modalidad = modalidades.id and fases.id_categoria = categorias.id and rutinas.id_club = clubes.id and fases.id_competicion = ".$_SESSION['id_competicion_activa']." ORDER BY rutinas.orden, rutinas.id, fases.orden, fases.id";

        $query_run = mysqli_query($connection,$query);
		$nombre_modalidad = mysqli_result($query_run,0,6);
		$nombre_categoria = mysqli_result($query_run,0,7);
		$nombre_club = mysqli_result($query_run,0,5);
		$id_modalidad = mysqli_result($query_run,0,10);
		$dd_total = mysqli_result($query_run,0,11);
		$orden = mysqli_result($query_run,0,1);
      ?>

		<!-- Begin Page Content -->
		<div class="container-fluid">

			<!-- Titulo página y pdf -->
			<?php
			$nombres = "SELECT group_concat(nadadoras.nombre SEPARATOR ', ') FROM rutinas, rutinas_participantes, nadadoras WHERE nadadoras.id = rutinas_participantes.id_nadadora and rutinas.id = rutinas_participantes.id_rutina and rutinas_participantes.reserva = 'no' and id_rutina = ".$id_rutina;
			$nombres = mysqli_result(mysqli_query($connection,$nombres));
			?>
			<div class="d-sm-flex align-items-center justify-content-between mb-4">
				<h4 class="mb-0 font-weight-bold text-primary"><i class="fas fa-fw fa-flag-checkered"></i>Puntuar <td class="align-middle">
						<blockquote class="blockquote"><?php echo ' Orden:'.$orden. ' #'.$id_rutina.' '.$nombre_modalidad.' ' .$nombre_categoria.' '.$nombre_club.' DD:'.$dd_total.'</blockquote> <figcaption class="blockquote-footer">('.$nombres.')</figcaption>';
										?>
					</td>


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
              //selecciono los elementos excluyendo hibridos de tipo 3 = transiciones
            $query = "SELECT hibridos_rutina.id, id_rutina , elemento, tipo, texto, nombre, color FROM hibridos_rutina, tipo_hibridos WHERE id_rutina = '$id_rutina' and tipo like 'part' and texto not like '3' and texto=tipo_hibridos.id order by elemento";

            $query_run = mysqli_query($connection,$query);
            ?>
					<table class="table text-center" id="NOdataTable" width="100%" cellspacing="0">
						<thead>
							<tr class="table-info">
								<th colspan=10>
									<h4>Elementos</h4>
									<code>Factor de corrección del elemento * (BM 0 DD) * X̅</code>

								</th>
							</tr>
							<tr>
								<th scope="col">Elemento</th>
								<th scope="col">BM</th>
								<th scope="col">DD</th>
								<?php
                    $query_jueces = "SELECT * from panel_jueces WHERE id_fase=$id_fase and id_panel in (SELECT id from paneles where id_paneles_tipo = 1 and id_competicion=".$_SESSION['id_competicion_activa'].") order by numero_juez";
                    $query_run_jueces = mysqli_query($connection,$query_jueces);
                    while ($row_jueces = mysqli_fetch_assoc($query_run_jueces)) {
                        echo  "<th scope='col'>J".$row_jueces['numero_juez']."</th>";

                    }

                    ?>
								<th scope="col">X̅</th>
								<th scope="col">Puntos</th>
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
                        $factor = $f_hybrid;
                      elseif ($row['nombre'] == 'ACROBATIC')
                        $factor = $f_acro;
                      elseif ($row['nombre'] == 'TRE')
                        $factor = $f_tre;
                      ?>
								<th scope="row" style="background-color:<?php echo $row['color'];?>"> <?php echo $row['elemento'].' '.$row['nombre'].' F:'.$factor; ?> </th>

								<form action="puntuaciones_rutina_code.php" enctype="multipart/form-data" method="post" id="formulario">
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
									<td>





										<label class="" for="BM<?php echo $row['elemento'];?>"><?php echo $basemark;?></label><input class="" type="checkbox" <?php echo $check_BM;?> value='<?php echo $basemark;?>' name="BM<?php echo $row['elemento'];?>">
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
						if($row_jueces['id_juez'] == '108'){
							$class = ' table-warning';
						}else
							$class = '';
                        echo  "<th class='$class' scope='col'><input name='notaE".$row['elemento']."J".$row_jueces['numero_juez']."' id='notaE".$row['elemento']."J".$row_jueces['numero_juez']."' tabindex='".($tab_index+($row_jueces['numero_juez']*15))."' type=number step=0.25 class='form-control $class' value=".$nota." ".$style."></th>";
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

							<?php
					$class = '';
					if($id_modalidad == 1 or $id_modalidad == 5){
						$class = 'd-none';
					}
?>
							<tr class="table-warning <?php echo $class;?>">
								<th colspan=10>
									<h4>Errores de Sincronización</h4>
								</th>
							</tr>
							<?php
                    $query = "SELECT * from errores_sincronizacion WHERE id_rutina=$id_rutina";
                    $errores_sincronizacion = mysqli_fetch_assoc(mysqli_query($connection,$query));
                    $penalizacion_sincronizacion = ($errores_sincronizacion['errores_pequenos']*$error_xs + $errores_sincronizacion['errores_obvios']*$error_ob + $errores_sincronizacion['errores_mayores']*$error_xl);
                    $tab_index=100;
                    ?>
							<tr class="<?php echo $class;?>">
								<td colspan="2">
									<label for='errores_pequenos'>Pequeños (<?php echo $error_xs;?>)</label>
									<input tabindex=<?php echo $tab_index;?> class=form-control type=number id='errores_pequenos' name='errores_pequenos' value='<?php echo $errores_sincronizacion['errores_pequenos'];?>'>
								</td>
								<td colspan="3">
									<label for='errores_obvios'>Obvios (<?php echo $error_ob;?>)</label>
									<input tabindex=<?php echo $tab_index;?> class=form-control type=number id='errores_obvios' name='errores_obvios' value='<?php echo $errores_sincronizacion['errores_obvios'];?>'>
								</td>
								<td colspan="2">
									<label for='errores_mayores'>Mayores (<?php echo $error_xl;?>)</label>
									<input tabindex=<?php echo $tab_index;?> class=form-control type=number id='errores_mayores' name='errores_mayores' value='<?php echo $errores_sincronizacion['errores_mayores'];?>'>
								</td>
								<td></td>
								<th>Errores</th>
								<th><?php echo $penalizacion_sincronizacion;?></th>
							</tr>


							<tr class="table-danger">
								<th colspan=10>Penalizaciones elementos</th>
							</tr>
							<?php
					$query = "SELECT penalizaciones_rutinas.id as id_penalizaciones_rutinas, codigo, resumen, puntos FROM penalizaciones_rutinas, penalizaciones WHERE penalizaciones_rutinas.id_penalizacion = penalizaciones.id and id_paneles_tipo = 1 and id_rutina=$id_rutina";
					$penalizaciones = mysqli_query($connection, $query);
					$puntos_penalizaciones_elementos = 0;
                    while ($penalizacion = mysqli_fetch_assoc($penalizaciones)) {
						$penalizaciones_tr .= '<tr>
						<td colspan=3 class="text-right"><a href="./puntuaciones_rutina_code.php?id_rutina='.$id_rutina.'&id_fase='.$id_fase.'&id_penalizaciones_rutinas='.$penalizacion['id_penalizaciones_rutinas'].'"><i class="fa-solid fa-delete-left"></i></a></td>
						<td>'.$penalizacion['codigo'].'</td>'.'<td colspan = 5>'.$penalizacion['resumen'].'</td>'.'<td>'.$penalizacion['puntos'].'</td>';
						$penalizaciones_tr .= '</td></tr>';
						$puntos_penalizaciones_elementos += $penalizacion['puntos'];
;

					}
					echo $penalizaciones_tr;
					?>
							<tr>
								<td colspan=7></td>
								<th class="text-right" colspan="2">Penalización</th>
								<th><?php echo $puntos_penalizaciones_elementos;?></th>
							</tr>
							<tr>
								<td colspan=7></td>
								<th class="text-right table-info" colspan="2">Nota Elementos</th>
								<th class="table-info"><?php
                       $nota_final_elementos= $nota_elementos+$penalizacion_sincronizacion+$puntos_penalizaciones_elementos;
					if(($nota_final_elementos)<0)
                            echo '0.0000';
                        else
                            echo $nota_final_elementos;?></th>
							</tr>
							<!--                       IMPRESIÓN ARTÍSTICA-->
							<tr class="table-success">
								<th colspan=10>
									<h4>Impresión Artística</h4>
									<code>Factor de corrección * ∑</code>
								</th>
							</tr>
							<tr>
								<td></td>
								<th></th>

								<th>F</th>
								<?php
                    $query_jueces = "SELECT * from panel_jueces WHERE id_fase=$id_fase and id_panel in (SELECT id from paneles where id_paneles_tipo = 2 and id_competicion=".$_SESSION['id_competicion_activa'].") order by numero_juez";
                    $query_run_jueces = mysqli_query($connection,$query_jueces);
                    while ($row_jueces = mysqli_fetch_assoc($query_run_jueces)) {
                        echo  "<th scope='col'>J".$row_jueces['numero_juez']."</th>";

                    }
                    echo "<th>∑</th><th>P</th>";
                    echo "<tr>";
                    echo "<th>ChoMu</th>";
                    echo "<td></td>";
                    echo "<td>".$f_chomu."</td>";
                    ?>
								<input type="hidden" name="f_chomu" value="<?php echo $f_chomu; ?>">
								<?php
                    $query_jueces = "SELECT * from panel_jueces WHERE id_fase=$id_fase and id_panel in (SELECT id from paneles where id_paneles_tipo = 2 and id_competicion=".$_SESSION['id_competicion_activa'].") order by numero_juez";
                    $query_run_jueces = mysqli_query($connection,$query_jueces);
					$nota_panel = 0;
                    while ($row_jueces = mysqli_fetch_assoc($query_run_jueces)) {
                        $query = "SELECT nota, id, nota_menor, nota_mayor FROM puntuaciones_jueces WHERE id_rutina='$id_rutina' and id_panel_juez=".$row_jueces['id']." and tipo_ia='ChoMu'";
                        $nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['nota'];
                        $id = mysqli_fetch_assoc(mysqli_query($connection,$query))['id'];
                        $nota_menor = mysqli_fetch_assoc(mysqli_query($connection,$query))['nota_menor'];
                        $nota_mayor = mysqli_fetch_assoc(mysqli_query($connection,$query))['nota_mayor'];
                        $style='';
                        if($nota_menor == 'si' or $nota_mayor == 'si'){
                            $style = 'style="text-decoration:line-through"';
                        }else{
							$nota_panel +=$nota;
                        }
						if($row_jueces['id_juez'] == '108'){
							$class = ' table-warning';
						}else
							$class = '';
                        echo  "<th class='$class' scope='col'><input name='notaChoMuJ".$row_jueces['numero_juez']."' id='notaChoMuJ".$row_jueces['numero_juez']."' tabindex='".($tab_index+($row_jueces['numero_juez']*5))."' type=number step=0.25 class='form-control $class' value=".$nota." ".$style."></th>";
                        ?>
								<input type="hidden" name="id_panel_juez_ChoMu<?php echo $row_jueces['numero_juez'];?>" value="<?php echo $row_jueces['id']; ?>">
								<?php
                    }
                    $tab_index++;
                    $query = "SELECT DISTINCT nota_media, nota FROM puntuaciones_elementos WHERE tipo_ia like 'ChoMu' and id_rutina=$id_rutina";
                    $nota = mysqli_fetch_assoc(mysqli_query($connection,$query));

                      ?>
								<td><?php echo $nota_panel;?></td>
								<td><?php echo $nota['nota'];?></td>
							</tr>


							<?php
                      $nota_ia = $nota_ia + $nota['nota'];
                    echo "<tr>";
                    echo "<th>Performance</th>";
					echo "<td></td>";
                    echo "<td>$f_performance</td>";
                    ?>
							<input type="hidden" name="factor_Performance" value="<?php echo $f_performance; ?>">
							<?php
                    $query_jueces = "SELECT * from panel_jueces WHERE id_fase=$id_fase and id_panel in (SELECT id from paneles where id_paneles_tipo = 2 and id_competicion=".$_SESSION['id_competicion_activa'].") order by numero_juez";
                    $query_run_jueces = mysqli_query($connection,$query_jueces);
					$nota_panel = 0;
                    while ($row_jueces = mysqli_fetch_assoc($query_run_jueces)) {
                        $query = "SELECT nota, id, nota_menor, nota_mayor FROM puntuaciones_jueces WHERE id_rutina='$id_rutina' and id_panel_juez=".$row_jueces['id']." and tipo_ia='Performance'";
                        $nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['nota'];
                        $id = mysqli_fetch_assoc(mysqli_query($connection,$query))['id'];
                        $nota_menor = mysqli_fetch_assoc(mysqli_query($connection,$query))['nota_menor'];
                        $nota_mayor = mysqli_fetch_assoc(mysqli_query($connection,$query))['nota_mayor'];
                        $style='';
                        if($nota_menor == 'si' or $nota_mayor == 'si'){
                            $style = 'style="text-decoration:line-through"';
                        }else{
							$nota_panel +=$nota;
                        }
						if($row_jueces['id_juez'] == '108'){
							$class = ' table-warning';
						}else
							$class = '';

                        echo  "<th class='$class' scope='col'><input name='notaPerformanceJ".$row_jueces['numero_juez']."' id='notaPerformanceJ".$row_jueces['numero_juez']."' tabindex='".($tab_index+($row_jueces['numero_juez']*5))."' type=number step=0.25 class='form-control $class' value=".$nota." ".$style."></th>";
                        ?>
							<input type="hidden" name="id_panel_juez_Performance<?php echo $row_jueces['numero_juez'];?>" value="<?php echo $row_jueces['id']; ?>">
							<?php
                    }
                    $tab_index++;
                    $query = "SELECT DISTINCT nota_media, nota FROM puntuaciones_elementos WHERE tipo_ia like 'Performance' and id_rutina=$id_rutina";
                    $nota = mysqli_fetch_assoc(mysqli_query($connection,$query));

                      ?>
							<td><?php echo $nota_panel;?></td>
							<td><?php echo $nota['nota'];?></td>
							</tr>

							<?php
                        $nota_ia = $nota_ia + $nota['nota'];


                    echo "<tr>";
                    echo "<th>Transitions</th>";
                    echo "<th></th>";
                    echo "<td>$f_transitions</td>";
                    ?>
							<input type="hidden" name="factor_Transitions" value="<?php echo $f_transitions; ?>">
							<?php
                    $query_jueces = "SELECT * from panel_jueces WHERE id_fase=$id_fase and id_panel in (SELECT id from paneles where id_paneles_tipo = 2 and id_competicion=".$_SESSION['id_competicion_activa'].") order by numero_juez";
                    $query_run_jueces = mysqli_query($connection,$query_jueces);
                    $nota_panel = 0;
					while ($row_jueces = mysqli_fetch_assoc($query_run_jueces)) {
                        $query = "SELECT nota, id, nota_menor, nota_mayor FROM puntuaciones_jueces WHERE id_rutina='$id_rutina' and id_panel_juez=".$row_jueces['id']." and tipo_ia='Transitions'";
                        $nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['nota'];
                        $id = mysqli_fetch_assoc(mysqli_query($connection,$query))['id'];
                        $nota_menor = mysqli_fetch_assoc(mysqli_query($connection,$query))['nota_menor'];
                        $nota_mayor = mysqli_fetch_assoc(mysqli_query($connection,$query))['nota_mayor'];
                        $style='';
                        if($nota_menor == 'si' or $nota_mayor == 'si'){
                            $style = 'style="text-decoration:line-through"';
                        }else{
							$nota_panel +=$nota;
                        }
						if($row_jueces['id_juez'] == '108'){
							$class = ' table-warning';
						}else
							$class = '';
                        echo  "<th class='$class' scope='col'><input name='notaTransitionsJ".$row_jueces['numero_juez']."' id='notaTransitionsJ".$row_jueces['numero_juez']."' tabindex='".($tab_index+($row_jueces['numero_juez']*5))."' type=number step=0.25 class='form-control $class' value=".$nota." ".$style."></th>";
                        ?>
							<input type="hidden" name="id_panel_juez_Transitions<?php echo $row_jueces['numero_juez'];?>" value="<?php echo $row_jueces['id']; ?>">
							<?php
                    }
                    $tab_index++;
                    $query = "SELECT DISTINCT nota_media, nota FROM puntuaciones_elementos WHERE tipo_ia like 'Transitions' and id_rutina=$id_rutina";
                    $nota = mysqli_fetch_assoc(mysqli_query($connection,$query));

                      ?>
							<td><?php echo $nota_panel;?></td>
							<td><?php echo $nota['nota'];?></td>
							<?php
                        $nota_ia = $nota_ia + $nota['nota'];
                        ?>
							</tr>
							<tr>
								<td colspan="7"></td>
								<th class="text-right" colspan="2">Nota</th>
								<th><?php echo $nota_ia;?></th>
							</tr>

							<tr class="table-danger">
								<th colspan=10>Penalizaciones Artística</th>
							</tr>
							<?php
					$query = "SELECT penalizaciones_rutinas.id as id_penalizaciones_rutinas, codigo, resumen, puntos FROM penalizaciones_rutinas, penalizaciones WHERE penalizaciones_rutinas.id_penalizacion = penalizaciones.id and id_paneles_tipo = 2 and id_rutina=$id_rutina";
					$penalizaciones = mysqli_query($connection, $query);
					$puntos_penalizaciones_elementos = 0;
                    while ($penalizacion = mysqli_fetch_assoc($penalizaciones)) {
						$penalizaciones_ia_tr .= '<tr>
						<td colspan=3 class="text-right"><a href="./puntuaciones_rutina_code.php?id_rutina='.$id_rutina.'&id_fase='.$id_fase.'&id_penalizaciones_rutinas='.$penalizacion['id_penalizaciones_rutinas'].'"><i class="fa-solid fa-delete-left"></i></a><td>'.$penalizacion['codigo'].'</td>'.'<td colspan = 5>'.$penalizacion['resumen'].'</td>'.'<td>'.$penalizacion['puntos'].'</td>';
						$penalizaciones_ia_tr .= '</td></tr>';
						$puntos_penalizaciones_ia += $penalizacion['puntos'];
;

					}
					echo $penalizaciones_ia_tr;
					?>
							<tr>
								<td colspan=7></td>
								<th class="text-right" colspan="2">Penalización</th>
								<th><?php echo $puntos_penalizaciones_ia;?></th>
							</tr>



							<tr>
								<td colspan="7"></td>
								<th class="text-right table-success" colspan="2">Nota Imp. Artística</th>
								<th class="table-success"><?php echo $nota_ia+$puntos_penalizaciones_ia;?></th>
							</tr>


							<tr class="table-danger">
								<th colspan=10>Penalizaciones Rutina</th>
							</tr>
							<?php
					$query = "SELECT penalizaciones_rutinas.id as id_penalizaciones_rutinas, codigo, resumen, puntos FROM penalizaciones_rutinas, penalizaciones WHERE penalizaciones_rutinas.id_penalizacion = penalizaciones.id and id_paneles_tipo = 0 and id_rutina=$id_rutina";
					$penalizaciones = mysqli_query($connection, $query);
					$puntos_penalizaciones_rutina = 0;
                    while ($penalizacion = mysqli_fetch_assoc($penalizaciones)) {
						$penalizaciones_rutina_tr .= '<tr>
						<td colspan=3 class="text-right"><a href="./puntuaciones_rutina_code.php?id_rutina='.$id_rutina.'&id_fase='.$id_fase.'&id_penalizaciones_rutinas='.$penalizacion['id_penalizaciones_rutinas'].'"><i class="fa-solid fa-delete-left"></i></a><td>'.$penalizacion['codigo'].'</td>'.'<td colspan = 5>'.$penalizacion['resumen'].'</td>'.'<td>'.$penalizacion['puntos'].'</td>';
						$penalizaciones_rutina_tr .= '</td></tr>';
						$puntos_penalizaciones_rutina += $penalizacion['puntos'];
;

					}
					echo $penalizaciones_rutina_tr;
					?>
							<tr>
								<td colspan=7></td>
								<th class="text-right" colspan="2">Penalización</th>
								<th><?php echo $puntos_penalizaciones_rutina;?></th>
							</tr>



							<tr>
								<th colspan=5></th>
								<th colspan="3" style="background-color:#1cc88a">
									<h1>
										Nota Rutina
									</h1>
								</th>
								<th colspan="2" style="background-color:#1cc88a">
									<h1>
										<?php
                    $query = "SELECT nota_final FROM rutinas WHERE id=$id_rutina";
                    $nota = mysqli_fetch_assoc(mysqli_query($connection,$query));
                    echo $nota['nota_final'];
                    ?>
									</h1>
								</th>
							</tr>

							<!--SELECT de penalizaciones-->
							<tr>
								<td colspan="10" class="text-center">
									<?php include('./includes/penalizaciones_select_option.php'); ?>
								</td>
							</tr>
							<tr>
								<!--GUARDAR-->
								<td colspan="8"></td>
								<td colspan=2>
									<input type="hidden" name="edit_id" value="<?php echo $id; ?>">
									<input type="hidden" name="id_fase" value="<?php echo $id_fase; ?>">
									<input type="hidden" name="id_club" value="<?php echo $row['id_club']; ?>">
									<input type="hidden" name="id_rutina" value="<?php echo $id_rutina; ?>">
									<button tabindex=1000 class="btn btn-primary text-center" type="submit" name="save_btn" id="save_btn"><i class="fa-solid fa-square-root-variable"></i> Calcular Nota</btn>
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
			<script type="text/javascript" src="./puntuaciones_rutina.js"></script>
