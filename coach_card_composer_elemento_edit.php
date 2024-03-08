
<?php
include('security.php');
include('includes/header.php');

?>

<?php

if(isset($_POST['tipo_acro']))
	$_GET['tipo_acro'] = $_POST['tipo_acro'];
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

		<!-- Begin Page Content -->
		<div class="container-fluid">

			<!-- Titulo página y pdf -->
			<div class="d-sm-flex align-items-center justify-content-between mb-4">
			<?php
//Editar elemento
				if(isset($_POST['edit_btn'])){
//					$id_rutina = $_POST['edit_id_rutina'];
//					$id_fase = $_POST['edit_id_fase'];
                    @$id_elemento = $_POST['edit_id_elemento'];
                    $elemento = $_POST['edit_elemento'];
					 if(isset($_POST['id_competicion'])){
//		  $id_competicion = $_POST['id_competicion'];
//		  $_SESSION['id_competicion_activa'] = $_POST['id_competicion'];
//		}else{
//			$id_competicion=$_SESSION['id_competicion_activa'];
		}

				?>
				<h4 class="mb-0 font-weight-bold text-primary">Editar Elemento <?php echo $elemento ;?> Coach Card</h4> <form display="dp-inline" action="./coach_card_composer.php" method="post">
<!--							<input type="hidden" name="id_rutina" value="<?php echo $id_rutina;?>"/>-->
<!--							<input type="hidden" name="id_fase" value="<?php echo $id_fase;?>"/>-->
<!--							<input type="hidden" name="id_competicion" value="<?php echo $id_competicion;?>"/>-->
								<button class="btn btn-primary"><i class='fa fa-chevron-left' aria-hidden='true'></i> Volver</button>
				</form>
			</div>

			<div class="card-body" id="contenedor">

						<form action="coach_card_composer_code.php" method="POST">
							<div class="row">
							<?php
                    $query = "SELECT * from hibridos_rutina WHERE id_rutina = '$id_rutina' and tipo='time_inicio' and elemento = $elemento";
                    $query_run = mysqli_query($connection,$query);
                    $elementos = mysqli_fetch_assoc($query_run);
                    ?>
								<div class="form-group col-6 col-md-2">

									<label for="edit_time">Inicio</label>
									<input type="time" class="form-control" name="time_inicio" value="<?php echo $elementos['texto']?>" placeholder="00:00">
								</div>
							<?php
                    $query = "SELECT * from hibridos_rutina WHERE id_rutina = '$id_rutina' and tipo='time_fin' and elemento = $elemento";
                    $query_run = mysqli_query($connection,$query);
                    $elementos = mysqli_fetch_assoc($query_run);
                    ?>
								<div class="form-group col-6 col-md-2">
									<label for="edit_time">Fin</label>
									<input type="time" class="form-control" name="time_fin" value="<?php echo $elementos['texto']?>" placeholder="00:00">
								</div>
								<div class="form-group col-6 col-md-2">
									<?php
									include('includes/tipo_hibridos_select_option.php');
									?>
								</div>

								<div class="form-group col-6 col-md-2">
                                    <?php
								    $tipo_elemento='Basemark';
									echo "<label for=edit_basemark>$tipo_elemento</label>";
                                    $query = "SELECT texto from hibridos_rutina WHERE id_rutina = '$id_rutina' and tipo='$tipo_elemento' and elemento = $elemento";



                                    $query_run = mysqli_query($connection,$query);
                                    if(mysqli_num_rows($query_run) > 0){
                                        $x=0;
                                        while ($row = mysqli_fetch_array($query_run)) {
                                            $texto = $row['texto'];
                                            include('includes/dificultad_hibridos_select_option.php');
                                            $x++;
                                        }
                                    }
									$bm_max = 1;
                                    for($x=$x+1; $x<=$bm_max; $x++){
                                        $texto='';
                                        include('includes/dificultad_hibridos_select_option.php');
                                    }

									?>
								</div>
								<div class="form-group col-6 col-md-2">
									<label for="edit_dd">DD</label>
									<?php
                                    $tipo_elemento='dd';
                                    $query = "SELECT texto from hibridos_rutina WHERE id_rutina = '$id_rutina' and tipo='$tipo_elemento' and elemento = $elemento";
                                    $query_run = mysqli_query($connection,$query);
                                    if(mysqli_num_rows($query_run) > 0){
                                        $x=0;
                                        while ($row = mysqli_fetch_array($query_run)) {
                                            $texto = $row['texto'];
                                            include('includes/dificultad_hibridos_select_option.php');
                                            $x++;
                                        }
                                    }
					$dd_max = 15;
                                    for($x=$x; $x<$dd_max; $x++){
                                        $texto='';
                                        include('includes/dificultad_hibridos_select_option.php');
                                    }
									?>
								</div>
								<div class="form-group col-6 col-md-2">
									<label for="edit_bonus">Bonus</label>
									<?php
                                    $tipo_elemento='bonus';
                                    $query = "SELECT texto from hibridos_rutina WHERE id_rutina = '$id_rutina' and tipo='$tipo_elemento' and elemento = $elemento";
                                    $query_run = mysqli_query($connection,$query);
                                    if(mysqli_num_rows($query_run) > 0){
                                        $x=0;
                                        while ($row = mysqli_fetch_array($query_run)) {
                                            $texto = $row['texto'];
//                                           		$texto = str_replace("XX", "'", $texto);
 include('includes/dificultad_hibridos_select_option.php');
                                            $x++;
                                        }
                                    }
									$bonus_max = 4;
                                    for($x; $x<$bonus_max; $x++){
                                        $texto='';
                                        include('includes/dificultad_hibridos_select_option.php');
                                    }
									?>
								</div>
							</div>
							<input type="hidden" name="elemento" value="<?php echo $elemento;?>"/>
							<a href="coach_card_composer.php" class="btn btn-danger"> Cancelar </a>
							<button type="submit" name="update_btn" class="btn btn-primary">Actualizar</button>
						</div>
					</div>

				</form>
				<?php

		}
		?>




	</div>


	<!-- template -->
	<?php
	include('includes/scripts.php');
	include('includes/footer.php');
	?>
<script>
//	cuando cambia el select de tipo de hibrido
	$('#id_tipo_hibrido').on('change', function() {
		//cambio el select de dd
		$(".id_tipo_hibrido").load("./includes/dificultad_hibridos_select_option.php?tipo_elemento=dd&id_tipo_hibrido="+$(this).find(":selected").val());
		//cambio el select de basemark
		$(".tipo_basemark").load("./includes/dificultad_hibridos_select_option.php?tipo_basemark="+$(this).find(":selected").val());
		//habilito los select de basemark, dd y bonus si estoy en un hibrido
		if($(this).find(":selected").val() == 1){
			$(".tipo_basemark").attr("disabled",false);
			$(".id_tipo_hibrido").attr("disabled",false);
			$(".bonus_tipo_hibrido").attr("disabled",false);
		}
		//habilito los select de basemark, dd si estoy en un TRE
		else if($(this).find(":selected").val() == 2){
			$(".tipo_basemark").attr("disabled",false);
			$(".id_tipo_hibrido").attr("disabled",false);
			$(".bonus_tipo_hibrido").val('')
			$(".bonus_tipo_hibrido").attr("disabled",true);
		}
		//desabilito el select de bonus si estoy en una acro
		else if($(this).find(":selected").val() == 4){
			$(".bonus_tipo_hibrido").val('')
			$(".bonus_tipo_hibrido").attr("disabled",true);
			$(".id_tipo_hibrido").attr("disabled",true);
		}

});
//cuando cambia el select de basemark
	$('#Basemark0').on('change', function() {
		//cambio el select de dd
		$(".id_tipo_hibrido").attr("disabled",false);

		$(".id_tipo_hibrido").load("./includes/dificultad_hibridos_select_option.php?tipo_elemento=dd&tipo_acro="+$(this).find(":selected").val());
});


		// A $( document ).ready() block.
$( document ).ready(function() {
	if($("#id_tipo_hibrido").val() == 4){
			$(".bonus_tipo_hibrido").attr("disabled",true);
		}
})
</script>
