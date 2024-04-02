<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');
//include('./lib/my_functions.php');

$condicion_club = '';
if(isset($_SESSION['club'])){
    $condicion_club = " and nadadoras.club = ".$_SESSION['club'];
}

$query = 'SELECT date_add(fecha, interval -dias_musica day) as fecha_musica, date_add(fecha, interval -dias_coach_card day) as fecha_coach_card, date_add(fecha, interval -dias_sorteo day) as fecha_sorteo, date_add(fecha, interval -dias_inicio_inscripcion day) as fecha_inicio_inscripcion, date_add(fecha, interval -dias_fin_inscripcion day) as fecha_fin_inscripcion FROM competiciones WHERE id='.$id_competicion;
		$fechas = mysqli_fetch_assoc(mysqli_query($connection, $query));
		//habilito o deshabilito coach_card
		$fecha_coach_card = $fechas['fecha_coach_card'];
		if(date('Y-m-d') > $fecha_coach_card & $_SESSION['id_rol'] != 1 )
			$enable_coach_card = 'disabled';
		else
			$enable_coach_card = '';
		$fecha_sorteo = $fechas['fecha_sorteo'];
		if(date('Y-m-d') > $fecha_sorteo)
			$enable_sorteo = 'disabled';
		else
			$enable_sorteo = '';
		//habilito o deshabilito inscripciones (añadir participantes y borrar)
		$fecha_inicio_inscripcion = $fechas['fecha_inicio_inscripcion'];
		$fecha_fin_inscripcion = $fechas['fecha_fin_inscripcion'];
		if(date('Y-m-d') >= $fecha_fin_inscripcion & $_SESSION['id_rol'] != 1 )
			$enable_inscripcion = 'disabled';
		else
			$enable_inscripcion = '';
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
                        <h5 class="modal-title" id="exampleModalLabel">Nueva participante</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="inscripciones_figuras_code.php" method="POST" enctype="multipart/form-data">
                        <div class="modal-body">
                            <div class="row">

                                <div class="form-group col">
                                    <?php
                    if($figuras == 'si')
                        include('includes/nadadoras_select_option.php');
                    else
                        include('includes/club_select_option.php');
                  ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <?php
                    include('includes/fases_select_option.php');
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
        <div class="container-fluid" style="padding:0px 0px 0px 5px">

            <!-- Titulo página y pdf -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h4 class="mb-0 font-weight-bold text-primary"><i class="fa-regular fa-flag"></i> Inscripciones en figuras
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addUserProfile" <?php echo $enable_inscripcion?>>Añadir</button>
                    <?php if($_SESSION['id_rol'] != 5){
	?>
                    	<a target="_blank" href="./informes/informe_figuras_preinscripciones.php?id_competicion=<?php echo $_SESSION['id_competicion_activa']?>&titulo=Inscripciones" class="btn btn-primary shadow"><i class="fas fa-download fa-sm text-white-50"></i> PDF</a>
							<?php
                    }else{
					?>
                    <a target="_blank" href="./informes/informe_figuras_preinscripciones.php?id_competicion=<?php echo $_SESSION['id_competicion_activa']?>&club=<?php echo $_SESSION['club']?>&titulo=Inscripciones <?php echo $_SESSION['nombre_club']?>" class="btn btn-primary shadow"><i class="fas fa-download fa-sm text-white-50"></i> PDF</a>
                    <?php

					}
					?>
                    <a href="./index.php" class="btn  btn-primary shadow"><i class="fa fa-chevron-left" aria-hidden="true"></i> Volver</a>

                </h4>

            </div>

            <div class="card-body" style="padding:0px">

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

                    <?php
            $query = "SELECT DISTINCT fases.id_categoria, categorias.nombre, categorias.edad_minima, categorias.edad_maxima FROM fases, categorias WHERE fases.id_categoria = categorias.id and fases.id_competicion = ".$_SESSION['id_competicion_activa'];
            $query_categorias = mysqli_query($connection,$query);
            while ($row_categorias = mysqli_fetch_assoc($query_categorias)) {
                ?>
                    <div class="table-responsive">

                    <table class="table table-striped table-sm" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th colspan=10>
                                    <h2 class="primary"><?php echo $row_categorias['nombre'];
//mostar edades de categorias
//								echo ' (de '.$row_categorias['edad_minima'].' a '.$row_categorias['edad_maxima'].' años)';
										?>
                               </h2>
                                </th>
                            </tr>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col" colspan="4">Nadadora</th>
                                <th scope="col">Año</th>
                                <th scope="col">Club</th>
                                <th scope="col">CC</th>
                                <th scope="col" colspan="2" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                $query = "SELECT id FROM fases WHERE id_categoria = ".$row_categorias['id_categoria']." and id_competicion = ".$_SESSION['id_competicion_activa']." LIMIT 1";
                $id_fase = mysqli_result(mysqli_query($connection,$query),0);

                $query = "SELECT inscripciones_figuras.id, inscripciones_figuras.id_fase, inscripciones_figuras.id_nadadora, nadadoras.nombre as nombre_nadadora, nadadoras.apellidos as apellidos_nadadora, nadadoras.año_nacimiento as año, clubes.nombre_corto as nombre_club, inscripciones_figuras.id_fase, fases.elementos_coach_card FROM inscripciones_figuras, fases, nadadoras, clubes WHERE inscripciones_figuras.id_fase = fases.id and inscripciones_figuras.id_nadadora= nadadoras.id and nadadoras.club = clubes.id and fases.id = $id_fase $condicion_club ORDER BY nadadoras.club, nadadoras.apellidos, nadadoras.nombre";
                $query_run = mysqli_query($connection,$query);
                if(mysqli_num_rows($query_run) > 0){
                  while ($row = mysqli_fetch_assoc($query_run)) {
                    ?>
                            <tr>
                                <th scope="row"> <?php echo $row['id']; ?> </th>
                                <td colspan="4"> <?php echo $row['apellidos_nadadora'].', '.$row['nombre_nadadora']; ?> </td>
                                <td> <?php echo $row['año'];?> </td>
                                <td> <?php echo $row['nombre_club'];?> </td>
                                <?php
                      if($row['elementos_coach_card']>0){
                          ?>
                                <td><a href="./coach_card_composer.php?id_rutina=<?php echo $row['id']; ?>&id_fase=<?php echo $row['id_fase'];?>" class=" btn btn-warning btn-circle btn" <?php echo $enable_inscripcion;?>>
                                        <i class="fa-solid fa-puzzle-piece"></i>
                                    </a> </td>


                                <?php
                      } else
                          echo "<td></td>";
                      ?>
<!--
                                <td class="text-center">
                                    <form action="rutinas_edit.php" method="post">
                                        <input type="hidden" name="edit_id" value="<?php //echo $row['id']; ?>">
                                        <input type="hidden" name="id_fase" value="<?php //echo $row['id_fase']; ?>">
                                        <input type="hidden" name="id_club" value="<?php //echo @$row['id_club']; ?>">
                                        <button class="btn btn-success" type="submit" name="edit_btn"><i class="fas fa-edit"></i></btn>
                                    </form>
                                </td>
-->
                                <td class="text-center">
                                    <form action="inscripciones_figuras_code.php" method="POST">
                                        <input type="hidden" name="delete_id" value="<?php echo $row['id'];?>">
                                        <input type="hidden" name="id_nadadora" value="<?php echo $row['id_nadadora'];?>">
                                        <input type="hidden" name="delete_id_categoria" value="<?php echo $row_categorias['id_categoria'];?>">
                                        <button class="btn btn-danger" type="submit" name="delete_btn" <?php echo $enable_inscripcion;?>><i class="fas fa-trash"></i></btn>
                                    </form>
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
                    <?php
            }


            ?>

            </div>


            <!-- template -->
            <?php
            include('includes/scripts.php');
            include('includes/footer.php');
            ?>
