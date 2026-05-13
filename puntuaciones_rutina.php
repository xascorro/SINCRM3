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
