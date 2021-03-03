<?php
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

		<!-- Begin Page Content -->
		<div class="container-fluid">

			<!-- Titulo página y pdf -->
			<div class="d-sm-flex align-items-center justify-content-between mb-12">
      <h2 class="font-weight-bold text-primary">Conexión a la Base de Datos</h2>
      <h2 class="font-weight-bold text-white bg-gradient-danger">Cuidado, ponte en contacto con tu administrador si no sabes lo que tienes que hacer aquí</h2>
			</div>

			<div class="card-body">
						<form action="db_code.php" method="POST">
							<div class="row">
								<div class="form-group col-6">
									<label for="servername">Servidor</label>
									<input type="text" class="form-control" name="servername" value="<?php echo $servername?>" placeholder="Dirección del servidor">
								</div>
								<div class="form-group col-6">
									<label for="db_name">Base de datos</label>
									<input type="text" class="form-control form-control-user" name="db_name" value="<?php echo $db_name?>"placeholder="Nombre de la base de datos">
								</div>
                </div>
							<div class="row">
								<div class="form-group col-6">
									<label for="db_username">Usuario</label>
									<input type="text" class="form-control form-control-user" name="db_username" value="<?php echo $db_username?>"placeholder="Nombre de usuario">
								</div>
						
								<div class="form-group col-6">
									<label for="db_password">Contraseña</label>
									<input type="password" class="form-control" name="db_password" value="<?php echo $db_password?>"placeholder="Contraseña">
								</div>
								
							</div>
							<div class="row">
              <div class="form-group col-6">
                <label for="accept">Sobreescribir</label>
                <input type="checkbox" name="accept" value="1">
              </div>
              <div class="form-group col-6">
							<a href="index.php" class="btn btn-danger"> Cancelar </a>
							<button type="submit" name="update_btn" class="btn btn-primary">Actualizar</button>
              </div>
              </div>
						</form>

				
				



			</div>


			<!-- template -->
			<?php
			include('includes/scripts.php');
			include('includes/footer.php');
			?>