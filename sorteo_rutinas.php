<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
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


		<!-- Modal -->
		<div class="modal fade" id="addUserProfile" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="exampleModalLabel"><i class="fa-solid fa-wand-magic-sparkles"></i> Nuevo sorteo</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<form id="sortearForm" action="sorteo_rutinas_code.php" method="POST" enctype="multipart/form-data">
						<div class="modal-body">
							<div class="row">

								<div class="form-group col-8">
									<?php
                        include('includes/fases_competicion_select_option.php');
                  ?>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
								<!--                                <button type="submit" class="btn btn-primary" name="save_btn"><i class="fa-solid fa-random"></i> Sortear</button>-->
<!--								<button id="sortearBtn" type="button" class="btn btn-primary" name="save_btn"><i class="fa-solid fa-random"></i> Sortear modal</button>-->
					        <input type="hidden" name="save_btn" value="1">

						<button id="sortearBtn" type="submit" class="btn btn-primary" name="save_btn" value="1">
    <i class="fa-solid fa-random"></i> Sortear
</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="modal fade" id="animacionModal" tabindex="-1" role="dialog" aria-labelledby="animacionModalLabel" aria-hidden="true" style="background-color: rgba(0,0,0,0.7);">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-body text-center" style="position: relative; min-height: 200px;">
                <i id="iconoAnimacion" class="fa-solid fa-wand-magic-sparkles fa-4x" style="color: white;"></i>
                <!-- Particles and text will be added here by JavaScript -->
            </div>
        </div>
    </div>
</div>
		<div class="modal fade" id="anularSorteo" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="exampleModalLabel"><i class="fa-solid fa-delete-left"></i> Anular sorteo</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<form action="sorteo_rutinas_code.php" method="POST" enctype="multipart/form-data">
						<div class="modal-body">
							<div class="row">
								<div class="form-group col-12">
									<label for="mensaje">Se va a eliminar el sorteo de la competición completa. <br>Una vez pulsado "Eliminar" no podrá deshacerse.<br>¿Estás seguro? </label>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
								<button type="submit" class="btn btn-danger" name="delete_btn"><i class="fa-solid fa-delete-left"></i> Eliminar</button>
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
			<div class=" align-items-center justify-content-between">
				<div class="row">
					<div class="col col-6">
						<h4 class=" font-weight-bold text-primary"><i class="fa-solid fa-wand-magic-sparkles"></i> Orden de salida para rutinas </h4>
					</div>
					<div class="col col-6 ">
						<div class="row">
							<button type="button" class="col col-3 btn btn-primary" data-toggle="modal" data-target="#addUserProfile"><i class="fa-solid fa-random"></i> Sortear </button>
							<div class="col col-1"></div>
							<button type="button" class="col col-3 btn btn-danger" data-toggle="modal" data-target="#anularSorteo"><i class="fa-solid fa-delete-left"></i> Anular </button>
							<div class="col col-1"></div>

							<div class="col col-4">
								<a href="./informes/inscripciones_numericas_rutinas.php?id_competicion=<?=$_SESSION['id_competicion_activa']?>&titulo=Orden de salida" class="d-none d-sm-inline-block btn btn-success shadow-sm" target="_blank"><i class="fas fa-download fa-sm"></i> Descargar </a>
							</div>
						</div>
					</div>
				</div>

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
            $query = "SELECT DISTINCT fases.id as id_fase, fases.id_categoria, categorias.nombre as categoria, modalidades.nombre as modalidad FROM fases, categorias, modalidades WHERE fases.id_modalidad=modalidades.id and fases.id_categoria = categorias.id and fases.id_competicion = ".$_SESSION['id_competicion_activa']." order by fases.orden";
            $query_categorias = mysqli_query($connection,$query);
            $numero_categorias = $query_categorias->num_rows;
            while ($row_fases = mysqli_fetch_assoc($query_categorias)) {
                $query = "SELECT id FROM fases WHERE id_categoria = ".$row_fases['id_categoria']." and id_competicion = ".$_SESSION['id_competicion_activa']   ;
                $numero_fases = mysqli_query($connection,$query)->num_rows;
//                $numero_categorias=1;
                    ?>
					<table class="table table-striped table-hover table-sm" id="nodataTable" width="100%" cellspacing="0">
						<thead class="thead-light">
							<tr>
								<th colspan=4>
									<h2 class="text-center"> <?php echo $row_fases['modalidad'].' '.$row_fases['categoria'];?></h2>
								</th>
							</tr>
							<tr>
								<th scope="col" class="col col-1  text-center"><i class="fa-solid fa-list-ol"></i></th>
								<th scope="col" class="col col-1 ">#</th>
								<th scope="col" class="col col-2 ">Club</th>
								<th scope="col" class="col col-8 ">Nadadoras</th>
							</tr>
						</thead>
						<tbody>
							<?php
                $query = "SELECT rutinas.id as id_rutina, rutinas.orden, clubes.nombre_corto as nombre_club FROM rutinas, clubes WHERE rutinas.id_fase = ".$row_fases['id_fase']." and rutinas.id_club = clubes.id ORDER BY orden";
                $query_run = mysqli_query($connection,$query);
                if(mysqli_num_rows($query_run) > 0){
					while ($row = mysqli_fetch_assoc($query_run)) {
						$nombres = "SELECT group_concat(nadadoras.nombre SEPARATOR ', ') FROM rutinas, rutinas_participantes, nadadoras WHERE nadadoras.id = rutinas_participantes.id_nadadora and rutinas.id = rutinas_participantes.id_rutina and rutinas_participantes.reserva = 'no' and id_rutina = ".$row['id_rutina'];
						$nombres = mysqli_result(mysqli_query($connection,$nombres));
						$class_orden='';
						if($row['orden'] == '-1'){
							$row['orden'] = 'PRESWIMMER';
							$class_orden = 'table-warning';
						}
						else if($row['orden'] == '-2'){
							$row['orden'] = 'EXHIBICIÓN';
							$class_orden = 'table-warning';
						}else if($row['orden'] == '1'){
						  $class_orden = 'table-success';
					}

                    ?>
							<tr class="<?php echo $class_orden;?>">
								<th class="table-success text-center" scope="row"> <?php echo $row['orden']; ?> </th>
								<td class="" scope="row"> <?php echo $row['id_rutina']; ?> </td>
								<td class="" scope="row"> <?php echo $row['nombre_club']; ?> </td>
								<td class=""> <?php echo $nombres;?> </td>
							</tr>
							<?php
                      }
                    }
                    else{
                      echo "<tr><td colspan='4'>No se han encontrado registros en la base de datos</td></tr>";
                    }
                    ?>
						</tbody>
					</table>
					<?php
            }
            ?>
				</div>
			</div>
			<!-- template -->
			<?php
            include('includes/scripts.php');
            include('includes/footer.php');
            ?>
			<script>
document.addEventListener('DOMContentLoaded', function() {
    const sortearBtn = document.getElementById('sortearBtn');
    const animacionModal = new bootstrap.Modal(document.getElementById('animacionModal'));
    const iconoAnimacion = document.getElementById('iconoAnimacion');
    const formulario = document.getElementById('sortearForm');
    const modalBody = document.querySelector('#animacionModal .modal-body');

    // Add a container for particles
    const particlesContainer = document.createElement('div');
    particlesContainer.id = 'particles-container';
    particlesContainer.style.position = 'absolute';
    particlesContainer.style.top = '0';
    particlesContainer.style.left = '0';
    particlesContainer.style.width = '100%';
    particlesContainer.style.height = '100%';
    particlesContainer.style.pointerEvents = 'none';
    modalBody.appendChild(particlesContainer);

    // Add text element for animation messages
    const animationText = document.createElement('div');
    animationText.style.color = 'white';
    animationText.style.marginTop = '20px';
    animationText.style.fontSize = '1.5rem';
    animationText.style.fontWeight = 'bold';
    animationText.style.textShadow = '0 0 10px rgba(0,0,0,0.5)';
    modalBody.appendChild(animationText);

    // Function to create magic particles
    function createParticles() {
        for (let i = 0; i < 20; i++) {
            setTimeout(() => {
                const particle = document.createElement('div');
                particle.style.position = 'absolute';
                particle.style.width = '10px';
                particle.style.height = '10px';
                particle.style.backgroundColor = getRandomColor();
                particle.style.borderRadius = '50%';
                particle.style.filter = 'blur(1px)';
                particle.style.boxShadow = '0 0 10px ' + getRandomColor();

                // Random starting position near the wand
                const iconRect = iconoAnimacion.getBoundingClientRect();
                const modalRect = modalBody.getBoundingClientRect();

                const startX = (iconRect.left + iconRect.width/2) - modalRect.left;
                const startY = (iconRect.top + iconRect.height/2) - modalRect.top;

                particle.style.left = startX + 'px';
                particle.style.top = startY + 'px';

                particlesContainer.appendChild(particle);

                // Animate particle
                const angle = Math.random() * Math.PI * 2;
                const distance = 100 + Math.random() * 200;
                const destinationX = startX + Math.cos(angle) * distance;
                const destinationY = startY + Math.sin(angle) * distance;

                particle.animate([
                    { left: startX + 'px', top: startY + 'px', opacity: 1, transform: 'scale(0.3)' },
                    { left: destinationX + 'px', top: destinationY + 'px', opacity: 0, transform: 'scale(1.5)' }
                ], {
                    duration: 1000 + Math.random() * 1000,
                    easing: 'ease-out',
                    fill: 'forwards'
                });

                // Remove particle after animation
                setTimeout(() => {
                    if (particlesContainer.contains(particle)) {
                        particlesContainer.removeChild(particle);
                    }
                }, 2000);

            }, i * 50);
        }
    }

    function getRandomColor() {
        const colors = ['#FFD700', '#FF69B4', '#00BFFF', '#7FFF00', '#FF4500', '#9370DB'];
        return colors[Math.floor(Math.random() * colors.length)];
    }

    // Submit event handler with enhanced animations
    formulario.addEventListener('submit', function(event) {
        event.preventDefault();

        // Clear previous particles
        particlesContainer.innerHTML = '';

        // First animation - magic wand
        iconoAnimacion.className = 'fa-solid fa-wand-magic-sparkles fa-4x fa-bounce';
        iconoAnimacion.style.color = '#FFD700'; // Gold color
        animationText.textContent = 'Preparando sorteo...';
        animationText.style.opacity = '0';
        animacionModal.show();
        sortearBtn.disabled = true;

        // Fade in text
        setTimeout(() => {
            animationText.style.transition = 'opacity 0.5s ease-in';
            animationText.style.opacity = '1';
        }, 100);

        // Create magic particles
        setTimeout(() => {
            createParticles();
        }, 500);

        // Second animation - processing
        setTimeout(function() {
            iconoAnimacion.className = 'fas fa-spinner fa-spin fa-4x fa-pulse';
            iconoAnimacion.style.color = '#00BFFF'; // Deep sky blue
            animationText.style.opacity = '0';

            setTimeout(() => {
                animationText.textContent = 'Procesando participantes...';
                animationText.style.opacity = '1';
            }, 300);

            // Third animation - shuffling
            setTimeout(function() {
                iconoAnimacion.className = 'fa-solid fa-shuffle fa-4x fa-beat';
                iconoAnimacion.style.color = '#7FFF00'; // Chartreuse
                animationText.style.opacity = '0';

                setTimeout(() => {
                    animationText.textContent = '¡Mezclando!';
                    animationText.style.opacity = '1';
                    createParticles();
                }, 300);

                // Final animation - success
                setTimeout(function() {
                    iconoAnimacion.className = 'fa-solid fa-check-circle fa-4x';
                    iconoAnimacion.style.color = '#50C878'; // Emerald green
                    animationText.style.opacity = '0';

                    setTimeout(() => {
                        animationText.textContent = '¡Sorteo completado!';
                        animationText.style.opacity = '1';

                        // Add burst effect
                        const burstParticles = 40;
                        for (let i = 0; i < burstParticles; i++) {
                            const particle = document.createElement('div');
                            particle.style.position = 'absolute';
                            particle.style.width = '8px';
                            particle.style.height = '8px';
                            particle.style.backgroundColor = getRandomColor();
                            particle.style.borderRadius = '50%';
                            particle.style.boxShadow = '0 0 5px ' + getRandomColor();

                            const iconRect = iconoAnimacion.getBoundingClientRect();
                            const modalRect = modalBody.getBoundingClientRect();

                            const startX = (iconRect.left + iconRect.width/2) - modalRect.left;
                            const startY = (iconRect.top + iconRect.height/2) - modalRect.top;

                            particle.style.left = startX + 'px';
                            particle.style.top = startY + 'px';

                            particlesContainer.appendChild(particle);

                            const angle = (i / burstParticles) * Math.PI * 2;
                            const distance = 150 + Math.random() * 100;
                            const destinationX = startX + Math.cos(angle) * distance;
                            const destinationY = startY + Math.sin(angle) * distance;

                            particle.animate([
                                { left: startX + 'px', top: startY + 'px', opacity: 1, transform: 'scale(0.5)' },
                                { left: destinationX + 'px', top: destinationY + 'px', opacity: 0, transform: 'scale(0)' }
                            ], {
                                duration: 1000,
                                easing: 'ease-out',
                                fill: 'forwards'
                            });
                        }
                    }, 300);

                    // Submit form and close modal
                    setTimeout(function() {
                        animacionModal.hide();
                        formulario.submit();
                        sortearBtn.disabled = false;
                    }, 1200);

                }, 1500);
            }, 1500);
        }, 1500);
    });
});
			</script>
