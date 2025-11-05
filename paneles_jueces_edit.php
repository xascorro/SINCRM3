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
                <h4 class="mb-0 font-weight-bold text-primary">Editar panel</h4>
            </div>

            <div class="card-body">
                <?php
//Editar panel
				if(isset($_POST['edit_btn'])){
					$id = $_POST['edit_id'];

					$query = "SELECT id, id_juez, id_puestos_juez FROM puesto_juez WHERE id = '$id'";
					$query_run = mysqli_query($connection,$query);
					foreach ($query_run as $row) {
						$_POST['id_juez'] = $row['id_juez'];
						?>
                    <form action="paneles_jueces_code.php" method="POST">
                        <input type="hidden" name="edit_id" value="<?php echo $row['id']?>">
                        <div class="form-group">
                            <div class="row">
                                <div class="col">
                                    <?php
										include('./includes/puestos_select_option.php');
										?>
                                </div>
                                <div class="col">
                                    <?php
										include('./includes/juez_select_option.php');
										?>
                                </div>

                            </div>
                        </div>


                        <a href="paneles_jueces.php" class="btn btn-danger"> Cancelar </a>
                        <button type="submit" name="update_btn" class="btn btn-primary">Actualizar</button>
                    </form>
                    <?php
					}

				}
				?>
                    <?php
				if(isset($_POST['edit_btn_panel'])){
					$id = $_POST['edit_id'];
					$query = "SELECT id, nombre, numero_jueces, peso, color, descripcion FROM paneles WHERE id = '$id'";
					$query_run = mysqli_query($connection,$query);
					foreach ($query_run as $row) {
						$_POST['id_panel'] = $row['id'];
						?>
                        <form action="paneles_jueces_code.php" method="POST">
                            <input type="hidden" name="edit_id" value="<?php echo $row['id']?>">
                            <div class="form-group row">
                                <div class="col-7">
                                    <label for="edit_nombre">Nombre</label>
                                    <input type="text" class="form-control" name="edit_nombre" value="<?php echo $row['nombre'];?>">
                                </div>
                                <div class="col-1">
                                    <label for="edit_numero_jueces">Jueces</label>
                                    <input type="number" class="form-control" name="edit_numero_jueces" value="<?php echo $row['numero_jueces'];?>">
                                </div>
                                <div class="col-2">
                                    <label for="edit_peso">% Nota</label>
                                    <input type="number" class="form-control" name="edit_peso" placeholder="%" value="<? echo $row['peso'];?>">
                                </div>
                                <div class="col-2">
                                    <label for="edit_color">Color</label>
                                    <input type="text" class="form-control" name="edit_color" placeholder="#CECECE" value="<?php echo $row['color'];?>">
                                </div>
                            </div>
                            <div class="form-group row">
                               <div class="col-3">
                                    <?php
									include('includes/paneles_tipo_select_option.php');?>
                                </div>
                                <div class="col-9">

                                <label for="edit_descripcion">Descripción</label>
                                <input type="text" class="form-control" name="edit_descripcion" value="<?php echo $row['descripcion'];?>">
                                </div>

                            </div>


                            <a href="paneles_jueces.php" class="btn btn-danger"> Cancelar </a>
                            <button type="submit" name="update_btn_panel" class="btn btn-primary">Actualizar</button>
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
