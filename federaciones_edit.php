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
			<div class="d-sm-flex align-items-center justify-content-between mb-4">
				<h4 class="mb-0 font-weight-bold text-primary">Editar Federación</h4>
			</div>

			<div class="card-body">
				<?php
//Editar nadadora
				if(isset($_POST['edit_btn'])){
					$id = $_POST['edit_id'];
					$query = "SELECT * from federaciones WHERE id = '$id'";
					$query_run = mysqli_query($connection,$query);
					foreach ($query_run as $row) {
						?>
						<form action="federaciones_code.php" method="POST">
							<div class="row">
								<div class="form-group col">
									<input type="hidden" name="edit_id" value="<?php echo $row['id']?>">
									<label for="edit_nombre">Nombre</label>
									<input type="text" class="form-control" name="edit_nombre" value="<?php echo $row['nombre']?>" placeholder="Nombr">
								</div>
								<div class="form-group col-3">
									<label for="edit_nombre_corto">Nombre corto</label>
									<input type="text" class="form-control form-control-user" name="edit_nombre_corto" value="<?php echo $row['nombre_corto']?>"placeholder="Siglas">
								</div>
							</div>
							<div class="row">
								<div class="form-group col">
									<label for="edit_logo">Logo</label>
									<input type="file" class="form-control form-control-user" name="edit_logo" value="<?php echo $row['logo']?>"placeholder="Imagen logo">
								</div>
								<div class="form-group col-3">
									<label for="edit_telefono">Código</label>
									<input type="text" class="form-control form-control-user" name="edit_codigo" value="<?php echo $row['codigo']?>"placeholder="Código">
								</div>
							</div>
							<a href="federaciones.php" class="btn btn-danger"> Cancelar </a>
							<button type="submit" name="update_btn" class="btn btn-primary">Actualizar</button>
						</div>
					</div>		
				</form>
				<?php
			}

		}
		?>




	</div>


	<!-- template -->
	<?php
	include('includes/scripts.php');
	include('includes/footer.php');
	?>