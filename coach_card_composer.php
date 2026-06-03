<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');

if(isset($_GET['id_rutina'])){
	$id_rutina = $_GET['id_rutina'];
}else if(isset($_POST['id_rutina'])){
	$id_rutina = $_POST['id_rutina'];
}
if(isset($_GET['id_fase'])){
	$id_fase = $_GET['id_fase'];
}else if(isset($_POST['id_fase'])){
	$id_fase = $_POST['id_fase'];
}
//si no existen elementos de coach card los crea
$query = "SELECT * FROM hibridos_rutina where id_rutina = '$id_rutina'";
$query_run = mysqli_query($connection,$query);
if(mysqli_num_rows($query_run) == 0){
	$query = "SELECT elementos_coach_card FROM fases where id = '$id_fase'";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_num_rows($query_run) > 0){
		$n_elementos = mysqli_fetch_assoc($query_run);
		$n_elementos = $n_elementos['elementos_coach_card'];
		for($x=1; $x<=$n_elementos; $x++){
			//creo registros para declarar transiciones
			$query= "insert into hibridos_rutina set id_rutina = '$id_rutina', elemento='$x', tipo='part', texto='3'";
			$query_run = mysqli_query($connection,$query);
			//creo registros para declarar time_inicio
			$query= "insert into hibridos_rutina set id_rutina = '$id_rutina', elemento='$x', tipo='time_inicio';";
			$query_run = mysqli_query($connection,$query);
			//creo registros para declarar time_fin
			$query= "insert into hibridos_rutina set id_rutina = '$id_rutina', elemento='$x', tipo='time_fin';";
			$query_run = mysqli_query($connection,$query);
			//creo registros para declarar part
			$query= "insert into hibridos_rutina set id_rutina = '$id_rutina', elemento='$x', tipo='part'";
			$query_run = mysqli_query($connection,$query);
			//creo registros para declarar basemark
			$bm_max = 1;
			for ($i = 1; $i <= $bm_max; $i++) {
				$query = "insert into hibridos_rutina set id_rutina = '$id_rutina', elemento='$x', tipo='basemark'";
				$query_run = mysqli_query($connection,$query);
			}
			//creo registros para declarar dd
			$dd_max = 15;
			for ($i = 1; $i <= $dd_max; $i++) {
				$query = "insert into hibridos_rutina set id_rutina = '$id_rutina', elemento='$x', tipo='dd'";
				$query_run = mysqli_query($connection,$query);
			}
			//creo registros para declarar bonus
//			elimino bonus
//			$bonus_max = 1;
//			for ($i = 1; $i <= $bonus_max; $i++) {
//				$query = "insert into hibridos_rutina set id_rutina = '$id_rutina', elemento='$x', tipo='bonus'";
//				$query_run = mysqli_query($connection,$query);
//			}
			//creo registro para declarar total
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

		if(isset($_GET['id_rutina'])){
	$id_rutina = $_GET['id_rutina'];
}else if(isset($_POST['id_rutina'])){
	$id_rutina = $_POST['id_rutina'];
}
if(isset($_GET['id_fase'])){
	$id_fase = $_GET['id_fase'];
}else if(isset($_POST['id_fase'])){
	$id_fase = $_POST['id_fase'];
}
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
									Antes del elemento
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
								<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
								<button type="submit" class="btn btn-primary" name="save_btn">Guardar</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>

		<!-- Begin Page Content -->
		<div class="container-fluid">
			<!-- Titulo página y pdf -->
			<div class="d-sm-flex align-items-center justify-content-between mb-4">
				<h4 class="mb-0 font-weight-bold text-primary"><i class="fa-solid fa-puzzle-piece"></i> Coach Card Composer
					<a target="_blank" href="./informes/informe_coach_card.php?titulo=Coach%20Card&id_rutina=<?php echo $id_rutina;?>&id_competicion=<?php echo $id_competicion;?>" class="btn btn-warning shadow"><i class="fa fa-solid fa-puzzle-piece"></i> PDF</a>
					<?php
							  		if(!isset($_SESSION['club']) || $_SESSION['club'] == 0){
										?>
									<form target='_blank' action="../coach_card_auditor.php" method="post" style="display:inline-block; margin: 0 2px;">
										<button class="btn btn-warning" type="submit" name="audit_btn" title="Auditar Normativa">
											<span style="position: relative; display: inline-block; width: 1.2em; text-align: center;">
												<i class="fa fa-solid fa-puzzle-piece text-dark" aria-hidden="true"></i>
												<i class="fa fa-solid fa-magnifying-glass text-white" style="position: absolute; bottom: -4px; right: -6px; font-size: 0.65em; -webkit-text-stroke: 2px;" aria-hidden="true"></i>
											</span>
										</button>

										<input type="hidden" name="id_rutina" value="<?php echo $id_rutina;?>">
<!--
										<input type="hidden" name="id_fase" value="354">
										<input type="hidden" name="id_competicion" value="78">
-->
									</form>
									<?php
							  			
							  		}
									?>
				</h4>

				<?php
				if (@$figuras == 'si')
					$link = './inscripciones_figuras.php';
				else
					$link = './rutinas.php';
				;?>
				<form action="<?php echo $link;?>" method="post" class="form d-inline">
					<input type="hidden" name="id_fase" value="<?php echo $id_fase?>">
					<input type="hidden" name="id_competicion" value="<?php echo $id_competicion?>">
					<input type="hidden" name="club" value="<?php echo $club?>">
					<button type="submit" class="btn btn-primary"><i class='fa fa-chevron-left' aria-hidden='true'></i> Volver</button>
				</form>
			</div>
			<div class="card-body">



				<?php

