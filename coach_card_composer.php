<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');
?>

<?php
//si no existen elementos de coach card los crea
//	if(isset($_SESSION['id_rutina'])){
//		$id_rutina = $_SESSION['id_rutina'];
//		unset($_SESSION['id_rutina']);
//	}
//    else
//		$id_rutina = $_POST['id_rutina'];
//    if(isset($_SESSION['id_fase']))
//		$id_fase = $_SESSION['id_fase'];
//	else
//		$id_fase = $_POST['id_fase'];
// if(isset($_POST['id_competicion'])){
//		  $id_competicion = $_POST['id_competicion'];
//		  $_SESSION['id_competicion_activa'] = $_POST['id_competicion'];
//		}else{
//			$id_competicion=$_SESSION['id_competicion_activa'];
//		}
// if(isset($_SESSION['id_competicion_activa_from_code']))
//		  $id_competicion = $_SESSION['id_competicion_activa_from_code'];

    $query = "SELECT * FROM hibridos_rutina where id_rutina = '$id_rutina'";
    $query_run = mysqli_query($connection,$query);
    if(mysqli_num_rows($query_run) == 0){
        $query = "SELECT elementos_coach_card FROM fases where id = '$id_fase'";
        $query_run = mysqli_query($connection,$query);
        if(mysqli_num_rows($query_run) > 0){
            $n_elementos = mysqli_fetch_assoc($query_run);
            $n_elementos = $n_elementos['elementos_coach_card'];
            for($x=1; $x<=$n_elementos; $x++){
                $query= "insert into hibridos_rutina set id_rutina = '$id_rutina', elemento='$x', tipo='time_inicio';";
                $query_run = mysqli_query($connection,$query);
                $query= "insert into hibridos_rutina set id_rutina = '$id_rutina', elemento='$x', tipo='time_fin';";
                $query_run = mysqli_query($connection,$query);
                $query= "insert into hibridos_rutina set id_rutina = '$id_rutina', elemento='$x', tipo='part'";
                $query_run = mysqli_query($connection,$query);
                $query = "insert into hibridos_rutina set id_rutina = '$id_rutina', elemento='$x', tipo='basemark'";
                $query_run = mysqli_query($connection,$query);
                $query = "insert into hibridos_rutina set id_rutina = '$id_rutina', elemento='$x', tipo='basemark'";
                $query_run = mysqli_query($connection,$query);
                $query = "insert into hibridos_rutina set id_rutina = '$id_rutina', elemento='$x', tipo='dd'";
                $query_run = mysqli_query($connection,$query);
                $query = "insert into hibridos_rutina set id_rutina = '$id_rutina', elemento='$x', tipo='dd'";
                $query_run = mysqli_query($connection,$query);
                $query = "insert into hibridos_rutina set id_rutina = '$id_rutina', elemento='$x', tipo='dd'";
                $query_run = mysqli_query($connection,$query);
                $query = "insert into hibridos_rutina set id_rutina = '$id_rutina', elemento='$x', tipo='dd'";
                $query_run = mysqli_query($connection,$query);
                $query = "insert into hibridos_rutina set id_rutina = '$id_rutina', elemento='$x', tipo='dd'";
                $query_run = mysqli_query($connection,$query);
                $query = "insert into hibridos_rutina set id_rutina = '$id_rutina', elemento='$x', tipo='dd'";
                $query_run = mysqli_query($connection,$query);
                $query = "insert into hibridos_rutina set id_rutina = '$id_rutina', elemento='$x', tipo='bonus'";
                $query_run = mysqli_query($connection,$query);
                $query = "insert into hibridos_rutina set id_rutina = '$id_rutina', elemento='$x', tipo='bonus'";
                $query_run = mysqli_query($connection,$query);
                $query = "insert into hibridos_rutina set id_rutina = '$id_rutina', elemento='$x', tipo='total'";
                $query_run = mysqli_query($connection,$query);
            }
        }
    }
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
						<h5 class="modal-title" id="exampleModalLabel">Añadir transición</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<form action="coach_card_composer_code.php" method="POST" enctype="multipart/form-data">
						<div class="modal-body">
							<div class="row">
								<div class="col">
								</div>
								<div class="col">
									<?php
                                        $query = "SELECT max(elemento) as elementos_coach_card FROM hibridos_rutina where id_rutina ='".$id_rutina."'";
                                        $query_run = mysqli_query($connection,$query);
                                        $n_elementos = mysqli_fetch_assoc($query_run);
                                        $n_elementos = $n_elementos['elementos_coach_card'];
                                        $select = "<select name='elemento_transicion' id='elemento_transicion' class='form-control'>";
                                        for($x=1; $x<=$n_elementos; $x++){
                                            $select .= "<option value=".$x.">".$x."</option>";
                                        }
                                        $select .= "</select>";
                                        echo $select;
                  ?>
								</div>
							</div>
							<div class="modal-footer">
								<input type="hidden" name="id_rutina" value="<?php echo $id_rutina;?>">
								<input type="hidden" name="id_fase" value="<?php echo $id_fase;?>">
									Antes del elemento
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
				<h4 class="mb-0 font-weight-bold text-primary"><i class="fa-solid fa-puzzle-piece"></i> Coach Card Composer
				</h4>
				<button type="button" class="btn btn-success" data-toggle="modal" data-target="#addUserProfile">Añadir transición</button>
			<form action="./rutinas.php" method="post" class="form d-inline">
         	<input type="hidden" name="id_fase" value="<?php echo $id_fase?>">
         	<input type="hidden" name="id_competicion" value="<?php echo $id_competicion?>">
         	<input type="hidden" name="club" value="<?php echo $club?>">
			 <button type="submit" class="btn btn-primary"><i class='fa fa-chevron-left' aria-hidden='true'></i> Volver</button>
         </form>
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
//				$id_rutina=$_POST['id_rutina'];

				if($figuras == 'si'){
					$query = "SELECT inscripciones_figuras.id, inscripciones_figuras.id_fase, inscripciones_figuras.id_nadadora, nadadoras.nombre as nombre_nadadora, nadadoras.apellidos as apellidos_nadadora, modalidades.nombre as nombre_modalidad, categorias.nombre as nombre_categoria, clubes.nombre_corto as nombre_club, inscripciones_figuras.id_fase, fases.elementos_coach_card FROM inscripciones_figuras, fases, modalidades, categorias, nadadoras, clubes WHERE inscripciones_figuras.id = '$id_rutina' and inscripciones_figuras.id_fase = fases.id and fases.id_modalidad = modalidades.id and fases.id_categoria = categorias.id and inscripciones_figuras.id_nadadora = nadadoras.id and nadadoras.club = clubes.id and fases.id_competicion = ".$id_competicion." ORDER BY fases.orden, fases.id";
				}else{
					$query = "SELECT rutinas.id, rutinas.id_fase, rutinas.id_club, clubes.nombre_corto as nombre_club, modalidades.nombre as nombre_modalidad, categorias.nombre as nombre_categoria, rutinas.id_fase, fases.elementos_coach_card FROM rutinas, fases, modalidades, categorias, clubes WHERE rutinas.id = '$id_rutina' and rutinas.id_fase = fases.id and fases.id_modalidad = modalidades.id and fases.id_categoria = categorias.id and rutinas.id_club = clubes.id and fases.id_competicion = ".$id_competicion." ORDER BY fases.orden, fases.id";
					$nombres = "SELECT group_concat(nadadoras.nombre SEPARATOR ', ') FROM rutinas, rutinas_participantes, nadadoras WHERE nadadoras.id = rutinas_participantes.id_nadadora and rutinas.id = rutinas_participantes.id_rutina and rutinas_participantes.reserva = 'no' and id_rutina = $id_rutina";
					$nombres = mysqli_result(mysqli_query($connection,$nombres));
				}
				$query_run = mysqli_query($connection,$query);
            ?>



					<?php
                if(mysqli_num_rows($query_run) > 0){
                  while ($row = mysqli_fetch_assoc($query_run)) {
                      ?>
					<div class="row">
						<div class="col-6 col-md-2">
							<h5>#<?php echo $id_rutina;?></h5>
						</div>
						<div class="col-6 col-md-3">
							<h5><?php echo $row['nombre_modalidad']." ".$row['nombre_categoria'];
                        ?></h5>
						</div>
						<div class="col-12 col-md-7">
							<h5><?php if(isset($nombres))
											echo $row['nombre_club'].' ('.$nombres.')';
										else
											echo $row['nombre_club'].' - '.$row['nombre_nadadora'].' '.$row['apellidos_nadadora'];
								?>
										 </h5>
						</div>
					</div>
					<table class="table table-striped" id="dataTable" width="100%" cellspacing="0">
						<thead>
							<tr>
								<th scope="col" id="1">#</th>
								<th scope="col">Inicio</th>
								<th scope="col">Fin</th>
								<th scope="col">Part</th>
								<th scope="col">Basemark</th>
								<th scope="col">Declared Difficulty</th>
								<th scope="col">Bonus</th>
								<th scope="col">Total</th>
								<th scope="col">Editar</th>
							</tr>
						</thead>

						<tbody>
							<?php
                      $i = 1;
                      for($row['elementos_coach_card'];$i<=$row['elementos_coach_card'];$i++){

                        $query = "SELECT nombre, color, tipo_hibridos.id from hibridos_rutina, tipo_hibridos where hibridos_rutina.texto = tipo_hibridos.id and tipo='part' and texto = 3 and id_rutina=$id_rutina and elemento = $i";
                        $query_elementos = mysqli_query($connection,$query);
                            while ($elemento = mysqli_fetch_assoc($query_elementos)) {
                                echo "<tr><td colspan=8 style='background-color:".$elemento['color']."'>";
                                echo $elemento['nombre'];
                                $id_tipo_hibrido = $elemento['id'];
                                echo "</td>";
                                echo "<td style='background-color:".$elemento['color']."'>";
//								td.
                                ?>
							<form action="coach_card_composer_code.php" method="post">
								<input type="hidden" name="id_rutina" value="<?php echo $id_rutina; ?>">
								<input type="hidden" name="elemento" value="<?php echo $i; ?>">
								<button class="btn btn-warning" type="submit" name="dlt_btn_transicion"><i class="fa fa-trash"></i></btn>
							</form>
							<?php
                                echo "</td>";
                                echo "</tr>";
                            }


                          echo "<tr>";
                          echo "<th>$i</th>";
                          $query = "SELECT texto from hibridos_rutina where tipo='time_inicio' and id_rutina=$id_rutina and elemento = $i";

                        $query_elementos = mysqli_query($connection,$query);
                          echo "<td>";
                            while ($elemento = mysqli_fetch_assoc($query_elementos)) {
                                echo $elemento['texto'];
                            }
                            echo "</td>";
                          $query = "SELECT texto from hibridos_rutina where tipo='time_fin' and id_rutina=$id_rutina and elemento = $i";

                        $query_elementos = mysqli_query($connection,$query);
                          echo "<td>";
                            while ($elemento = mysqli_fetch_assoc($query_elementos)) {
                                echo $elemento['texto'];
                            }
                            echo "</td>";
                          $query = "SELECT nombre, color, tipo_hibridos.id from hibridos_rutina, tipo_hibridos where hibridos_rutina.texto = tipo_hibridos.id and tipo='part' and texto <> 3 and id_rutina=$id_rutina and elemento = $i";
                        $query_elementos = mysqli_query($connection,$query);
//						  	echo '<td';
                            while ($elemento = mysqli_fetch_assoc($query_elementos)) {
                                echo "<td style='background-color:".$elemento['color']."'>";
                                echo $elemento['nombre'].'</td>';
                                $id_tipo_hibrido = $elemento['id'];
                            }
//                            echo "</td>";

                          $query = "SELECT texto, valor from hibridos_rutina where tipo='basemark' and id_rutina=$id_rutina and elemento = $i and valor>0";
                        $query_elementos = mysqli_query($connection,$query);
                           echo "<td>";
                          if(@$elemento['valor'] != '')
                                $elemento['valor'] = "(".$elemento['valor'].") ";
                            while ($elemento = mysqli_fetch_assoc($query_elementos)) {
                                echo $elemento['texto']." +".$elemento['valor']."<br>";
                            }
                         echo  "</td>";
                          $query = "SELECT texto, valor from hibridos_rutina where tipo='dd' and id_rutina=$id_rutina and elemento = $i and valor>0";
                        $query_elementos = mysqli_query($connection,$query);
                           echo "<td>";
                            while ($elemento = mysqli_fetch_assoc($query_elementos)) {
                                echo $elemento['texto']." +".$elemento['valor']."<br>";
                            }
                            echo  "</td>";

                          $query = "SELECT texto, valor from hibridos_rutina where tipo='bonus' and id_rutina=$id_rutina and elemento = $i and valor>0";
                        $query_elementos = mysqli_query($connection,$query);
                          echo "<td>";
                          if(@$elemento['valor'] != '')
                                $elemento['valor'] = "(".$elemento['valor'].") ";
                            while ($elemento = mysqli_fetch_assoc($query_elementos)) {
                                echo $elemento['texto']." +".$elemento['valor']."<br>";
                            }
                                                      echo  "</td>";
                          $query = "SELECT valor from hibridos_rutina where tipo='total' and id_rutina=$id_rutina and elemento = $i";
                          echo "<th>";
                        $query_elementos = mysqli_query($connection,$query);
                            while ($elemento = mysqli_fetch_assoc($query_elementos)) {
                                echo $elemento['valor'];
                            }
                            echo "</th>";


                          echo "<td>";
                          ?>
							<form action="coach_card_composer_elemento_edit.php" method="post">
<!--								<input type="hidden" name="edit_id_rutina" value="<?php echo $row['id']; ?>">-->
<!--								<input type="hidden" name="id_competicion" value="<?php echo $id_competicion; ?>">-->
<!--								<input type="hidden" name="edit_id_fase" value="<?php echo $id_fase; ?>">-->
								<input type="hidden" name="edit_elemento" value="<?php echo $i; ?>">
								<input type="hidden" name="id_tipo_hibrido" value="<?php echo $id_tipo_hibrido; ?>">
								<button class="btn btn-success" type="submit" name="edit_btn"><i class="fas fa-edit"></i></btn>
									<?php
                        echo "</td>";
                        echo "</tr>";
                        ?>
							</form>
							<?php


                      }
                      ?>

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
