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
