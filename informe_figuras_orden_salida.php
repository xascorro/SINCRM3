<?php
include('security.php');
include('includes/header.php');
include('./lib/my_functions.php');
$query = "select * from competiciones where activo='si'";     // Esta linea hace la consulta
$result = mysqli_query($connection,$query);
    while ($registro = mysqli_fetch_array($result)){
	    $GLOBALS["id_competicion_activa"] = $registro['id'];
	    $_SESSION["nombre_competicion_activa"] = $registro['nombre'];
	    $GLOBALS["lugar"] = $registro['lugar'];
	    $GLOBALS["fecha"] = dateAFecha($registro['fecha']);
	    $GLOBALS["organizador"] = $registro['organizador'];
        $GLOBALS["header_image"] = "../".$registro['header_informe'];
	    $GLOBALS["footer_image"] = "../".$registro['footer_informe'];
	    $GLOBALS["enmascarar_licencia"] = $registro['enmascarar_licencia'];
    }
?>
<style>
.header {
    position: fixed;
    top: 0;
    //los demás estilos
}
@media print{
    * {
        color: inherit !important;
        background-color: inherit !important;
    }
/*
    .bg-warning{
        background-color: aqua !important;
    }
    .table td{
        background-color: transparent !important;
    }
*/
  *, *:before, *:after {
    background: transparent !important;
    color: #000 !important;
    -webkit-box-shadow: none !important;
    box-shadow: none !important;
    text-shadow: none !important;
  }

    .row{
        display: block;
    }
    .page-break {
        page-break-after: always;
    }

}

</style>


<!-- Content Wrapper -->
<div id="content-wrapper" class="d-flex flex-column">

    <!-- Main Content -->
    <div id="content">

    <div class="container header">
  <div class="row">
    <div class="col col-1">
<img src="./images/logo_sincrm.png"  class="rounded float-left" style="max-width:125px">    </div>
    <div class="col col-10 text-center">
      <h2 class="info"><?php echo $_SESSION["nombre_competicion_activa"];?></h2>
    </div>
    <div class="col col-1">
<img src="./images/federaciones/logo_fnrm.jpg" class="rounded float-right" >    </div>
  </div>
</div>
        <?php
    ?>
        <!-- template -->
        <!-- Tu código empieza aquí -->

       <!-- Begin Page Content -->
        <div class="container-fluid">
            <!-- Titulo página y pdf -->
<!--
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h4 class="mb-0 font-weight-bold text-primary"><i class="fa-solid fa-wand-magic-sparkles"></i> Orden de salida para figuras
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addUserProfile"><i class="fa-solid fa-random"></i> Sortear </button>
                </h4>
                <a href="./informes/informe_figuras_orden_salida.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" target="_blank"><i class="fas fa-download fa-sm text-white-50"></i> Descargar PDF</a>
            </div>
-->

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
            $query = "SELECT DISTINCT fases.id_categoria, categorias.nombre FROM fases, categorias WHERE fases.id_categoria = categorias.id and fases.id_competicion = ".$_SESSION['id_competicion_activa'];
            $query_categorias = mysqli_query($connection,$query);
            $numero_categorias = $query_categorias->num_rows;
            while ($row_categorias = mysqli_fetch_assoc($query_categorias)) {
                $query = "SELECT id FROM fases WHERE id_categoria = ".$row_categorias['id_categoria']." and id_competicion = ".$_SESSION['id_competicion_activa']   ;
                $numero_categorias = mysqli_query($connection,$query)->num_rows;
                    ?>
                    <table class="table table-striped table-hover table-sm page-break" id="dataTable" width="100%" cellspacing="0">
                        <thead class="thead-light">
                            <tr>
                                <th colspan=10>
                                    <h2 class="text-center"> <?php echo $row_categorias['nombre'];?></h2>
                                </th>
                            </tr>
                            <tr>
                                <th scope="col" colspan="<?php echo $numero_categorias;?>" class="text-center">Orden</th>
                                <th scope="col">#</th>
                                <th scope="col">Nadadora</th>
                                <th scope="col">Año</th>
                                <th scope="col">Club</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                $query = "SELECT id FROM fases WHERE id_categoria = ".$row_categorias['id_categoria']." and id_competicion = ".$_SESSION['id_competicion_activa']." LIMIT 1";
                $id_fase = mysqli_result(mysqli_query($connection,$query),0);

                $query = "SELECT inscripciones_figuras.orden, inscripciones_figuras.id, inscripciones_figuras.id_fase, inscripciones_figuras.id_nadadora, nadadoras.nombre as nombre_nadadora, nadadoras.apellidos as apellidos_nadadora, nadadoras.año_nacimiento as año, clubes.nombre_corto as nombre_club, fases.elementos_coach_card, fases.sorteado FROM inscripciones_figuras, fases, nadadoras, clubes WHERE inscripciones_figuras.id_fase = fases.id and inscripciones_figuras.id_nadadora= nadadoras.id and nadadoras.club = clubes.id and fases.id = $id_fase ORDER BY orden";
                $query_run = mysqli_query($connection,$query);
                if(mysqli_num_rows($query_run) > 0){
                  while ($row = mysqli_fetch_assoc($query_run)) {
                    ?>
                            <tr>
                                <?php
                      $query = "SELECT orden FROM inscripciones_figuras WHERE id_nadadora =".$row['id_nadadora']." and id_fase in (SELECT fases.id FROM fases WHERE id_categoria = ".$row_categorias['id_categoria'].")";
                      $orden_nadadora = mysqli_query($connection,$query);
                      $orden_impreso = "";
                      $th_class=array(
                            0  => 'table-success',
                            1 => 'table-info',
                            2  => 'table-warning',
                            3  => 'table-danger',
                        );
                      $i=0;
                      $class2 = '';
                      while($orden_n = mysqli_fetch_assoc($orden_nadadora)){
                          if($row['sorteado']=='si'){
                            $class = $th_class[$i];
                          }
                          else
                              $class = "table-danger";
                          if($orden_n['orden'] == 1){
                              $es_corte = true;
                              $class2 = $th_class[$i];
                          }

                      ?>
                                <th class="<?php echo $class2;?> text-center" scope="row"> <?php echo $orden_n['orden']; ?></th>
                                <?php
                          $i++;
                    }


                      ?>
                                <td class="<?php echo $class2;?>" scope="row"> <?php echo $row['id']; ?> </td>
                                <td class="<?php echo $class2;?>" > <?php echo $row['apellidos_nadadora'].', '.$row['nombre_nadadora']; ?> </td>
                                <td class="<?php echo $class2;?>" > <?php echo $row['año'];?> </td>
                                <td class="<?php echo $class2;?>" > <?php echo $row['nombre_club'];?> </td>
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
