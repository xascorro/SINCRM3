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
		if(isset($_POST['id_competicion'])){
			$_SESSION['id_competicion_activa'] = $_POST['id_competicion'];
		}else if(isset($_SESSION['id_competicion_usuario'])){
			$_SESSION['id_competicion_activa'] = $_SESSION['id_competicion_usuario'];
		}
		if(isset($_POST['id_rutina'])){
			$id_rutina = $_POST['id_rutina'];
		}elseif(isset($_SESSION['id_rutina'])){
			$id_rutina = $_SESSION['id_rutina'];
		}
		$_SESSION['id_rutina'] = $id_rutina;;

		if(isset($_POST['id_fase'])){
			$id_fase = $_POST['id_fase'];
		}elseif(isset($_SESSION['id_fase'])){
			$id_fase = $_SESSION['id_fase'];
		}
				$_SESSION['id_fase'] = $id_fase;


    ?>
    <!-- template -->
    <!-- Tu código empieza aquí -->






    <!-- Begin Page Content -->
    <div class="container-fluid">

      <!-- Titulo página y pdf -->
      <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h4 class="mb-0 font-weight-bold text-primary"><i class="fas fa-fw fa-users"></i> Participantes de las Rutinas
         <form action="rutinas.php" method="post" class="form d-inline">
         	<input type="hidden" name="id_fase" value="<?php echo $_SESSION['id_fase']?>">
         	<input type="hidden" name="id_competicion" value="<?php echo $_SESSION['id_competicion_activa']?>">
         	<input type="hidden" name="club" value="<?php echo $club?>">
			 <button type="submit" class="btn btn-primary"><i class='fa fa-chevron-left' aria-hidden='true'></i> Volver</button>
         </form>
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
            $query = "SELECT rutinas.id, rutinas.nombre as nombre_rutina, rutinas.id_fase, rutinas.id_club, clubes.nombre_corto as nombre_club, modalidades.nombre as nombre_modalidad, categorias.nombre as nombre_categoria, rutinas.id_fase, fases.elementos_coach_card, modalidades.numero_participantes, numero_reservas FROM rutinas, fases, modalidades, categorias, clubes WHERE rutinas.id=$id_rutina and rutinas.id_fase = fases.id and fases.id_modalidad = modalidades.id and fases.id_categoria = categorias.id and rutinas.id_club = clubes.id and fases.id_competicion = ".$_SESSION['id_competicion_activa']." ORDER BY rutinas.id, fases.orden, fases.id";
            $query_run = mysqli_query($connection,$query);
            ?>
            <table class="table " id="dataTable" width="100%" cellspacing="0">

                <?php
                if(mysqli_num_rows($query_run) > 0){
                    $data = mysqli_fetch_assoc(mysqli_query($connection,$query));
                    $nombre_modalidad = $data['nombre_modalidad'];
                    $nombre_categoria = $data['nombre_categoria'];
                    $nombre_club = $data['nombre_club'];
                    $nombre_rutina = $data['nombre_rutina'];
                    $numero_participantes = $data['numero_participantes'];
                    $numero_reservas = $data['numero_reservas'];
                    $id_club = $data['id_club'];
                    $id_fase = $data['id_fase'];

                    ?>
                    <thead>
                <tr>
                    <td colspan="3"><h4> <?php echo $nombre_modalidad." ".$nombre_categoria." - ". $nombre_club.' '.$nombre_rutina; ?> </h4></td>
                </tr>
              </thead>
              <tbody>



                        <?php
                    for ($x=0;$x<$numero_participantes;$x++){
                        ?>
                        <form action="rutinas_participantes_code.php" method="post">
                        <?php
                        $query="SELECT * FROM rutinas_participantes WHERE id_rutina=$id_rutina and reserva like 'no' limit $x,1";
                        $participante = mysqli_fetch_assoc(mysqli_query($connection,$query));
                        $id_nadadora = @$participante['id_nadadora'];
                        $id = @$participante['id'];
                        echo "<tr><td>Titular</td><td>";
                        include('./includes/nadadoras_select_option.php');
                        echo "</td>";
                        ?>
                          <input type="hidden" name="id" value="<?php echo $id; ?>">
                          <input type="hidden" name="id_rutina" value="<?php echo $id_rutina; ?>">
                          <input type="hidden" name="reserva" value="no">
                          <input type="hidden" name="id_competicion" value="<?php echo $_SESSION['id_competicion_activa']; ?>">
                          <?php
                        if ($id_nadadora > 0){
                            echo '<td><div class="row"><button class="btn btn-success" type="submit" name="update_btn"><i class="fas fa-edit"></i></btn>
                            <button class="btn btn-danger" type="submit" name="delete_btn"><i class="fas fa-trash"></i></btn></div></td>';
                        }else
                            echo '<td><button class="btn btn-success" type="submit" name="save_btn"><i class="fas fa-save"></i></btn></td>';
                          ?>
                        </form>
                    </tr>

                        <?php
                    }
                     for ($x=0;$x<$numero_reservas;$x++){
                         ?>
                        <form action="rutinas_participantes_code.php" method="POST">
                        <?php
                        $query="SELECT * FROM rutinas_participantes WHERE id_rutina=$id_rutina and reserva like 'si' limit $x,1";
                        $participante = mysqli_fetch_assoc(mysqli_query($connection,$query));
                        $id_nadadora = @$participante['id_nadadora'];
                        $id = @$participante['id'];
                        echo "<tr><td>Reserva</td><td>";
                        include('./includes/nadadoras_select_option.php');
                        echo "</td>";
                        ?>
                          <input type="hidden" name="id" value="<?php echo $id; ?>">
                          <input type="hidden" name="id_rutina" value="<?php echo $id_rutina; ?>">
                          <input type="hidden" name="reserva" value="si">
                          <?php
                        if ($id_nadadora > 0){
                            echo '<td><div class="row"><button class="btn btn-success" type="submit" name="update_btn"><i class="fas fa-edit"></i></btn>&nbsp;
                            <button class="btn btn-danger" type="submit" name="delete_btn"><i class="fas fa-trash"></i></btn></div></td></tr>';
                        }else
                            echo '<td><button class="btn btn-success" type="submit" name="save_btn"><i class="fas fa-save"></i></btn></td></tr>';
                          ?>
                        </form>
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



