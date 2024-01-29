<?php
//============================================================+
// File name   : example_048.php
// Begin       : 2009-03-20
// Last Update : 2013-05-14
//
// Description : Example 048 for TCPDF class
//               HTML tables and table headers
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com LTD
//               www.tecnick.com
//               info@tecnick.com
//============================================================+

/**
 * Creates an example PDF TEST document using TCPDF
 * @package com.tecnick.tcpdf
 * @abstract TCPDF - Example: HTML tables and table headers
 * @author Nicola Asuni
 * @since 2009-03-20
 */

// Include the main TCPDF liary (search for installation path).
require_once('../tcpdf/tcpdf.php');
include('../database/dbconfig.php');
session_start();

if(!$_SESSION['username']){
	header('Location: login.php');
}else{
	$query = "SELECT id, nombre FROM competiciones WHERE activo = 'si'";
	$query_run = mysqli_query($connection,$query);
	$competicion = mysqli_fetch_assoc($query_run);
	$_SESSION['id_competicion_activa'] = $competicion['id'];
	$_SESSION['nombre_competicion_activa']= $competicion['nombre'];
	$_SESSION['color_competicion_activa']= $competicion['color'];
}
include('../lib/my_functions.php');

$query = "SELECT * from rutinas WHERE id = ".$_SESSION['id_competicion_activa'];
	$query_run = mysqli_query($connection,$query);

//    mysqli_query("SET NAMES 'utf8'");
    $GLOBALS["id_competicion_activa"] = 0;
	$GLOBALS["nombre_competicion_activa"] = "No hay competición activa";
    $query = "select * from competiciones where activo='si'";     // Esta linea hace la consulta
	$query_run = mysqli_query($connection,$query);
    while ($registro = mysqli_fetch_array($query_run)){
	    $id_competicion_activa = $registro['id'];
	    $GLOBALS["nombre_competicion_activa"] = $registro['nombre'];
	    $GLOBALS["lugar"] = $registro['lugar'];
	    $GLOBALS["fecha"] = $registro['fecha'];
	    $GLOBALS["organizador"] = $registro['organizador'];
	    $GLOBALS["header"] = $registro['header_informe'];
	    $GLOBALS["footer"] = $registro['footer_informe'];
	}
////****************************//
$titulo = $_GET['titulo'];
$titulo_documento = $GLOBALS['nombre_competicion_activa']."<br>$titulo";
$nombre_documento = $titulo.' '.$GLOBALS['nombre_competicion_activa'].'.pdf';
$GLOBALS['footer_substring'] = "Sede: ".$GLOBALS['lugar']."\n <br> Fecha: ".dateAFecha($GLOBALS['fecha']);
$logo_header_width= 100;
$GLOBALS['header_image'] = '../'.$GLOBALS['header'];
$GLOBALS['footer_image'] = '../'.$GLOBALS['footer'];

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {

    //Page header
    public function Header() {
        // Logo
        $this->SetFont('helvetica', 12);
        $this->WriteHTML('<img style="border-bottom:1px #cecece;" src="'.$GLOBALS['header_image'].'">', true, false, true, false, '');
        $this->SetXY(25,12);
        $this->WriteHTML('<div style="text-align:center; font-size:large; font-weight:bold">'.$GLOBALS['titulo_documento']."</div>", true, false, true, false, '');

    }

    //Page footer
    public function Footer() {
        // Logo
        $this->SetFont('helvetica', 12);
$x = 15;
$y = 273;
$w = '180';
$h = '';
        $this->Image($GLOBALS['footer_image'], $x, $y, $w, $h, 'JPG', '', '', false, 300, '', false, false, 0, 'L', false, false);



        $this->SetXY(16,275);
		$pagenumtxt = $this->getAliasNumPage().' de '.$this->getAliasNbPages();
        $this->WriteHTML('<div style="text-align:right; font-size:large; font-weight:bold">'.$GLOBALS['footer_substring'].'<br>'.$pagenumtxt.'</div>', true, false, true, false, '');
    }
}


// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
//define ('PDF_HEADER_LOGO', 'kki.jpg');
// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Pedro Díaz');
$pdf->SetTitle($titulo_documento);
$pdf->SetSubject($GLOBALS["nombre_competicion_activa"]);
$pdf->SetKeywords('sincro, PDF, alhama, murcia, lorca');

// set default header data
//$pdf->SetHeaderData($logo_campeonato, $logo_header_width, $titulo_documento, $header_substring);

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page eaks
$pdf->SetAutoPagebreak(TRUE, 50);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
    require_once(dirname(__FILE__).'/lang/eng.php');
    $pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

// set font
$pdf->SetFont('helvetica', 'B', 14);

// -----------------------------------------------------------------------------



$error_color = "#E65B5E";
$rutina_color_par = "#EFEFEF";
$rutina_color_impar = "#FFFFFF";
// add a page
$pdf->AddPage();

$query = "select * from fases where id_competicion = '".$GLOBALS["id_competicion_activa"]."' ORDER BY orden";
$fases = mysqli_query($connection,$query);
while($fase = mysqli_fetch_array($fases)){
	$query = "select nombre, id from categorias where id = '".$fase['id_categoria']."'";
    $nombre_categoria=mysqli_result(mysqli_query($connection,$query),0);
    $id_categoria=mysqli_result(mysqli_query($connection,$query),0,1);
	$query = "select nombre, id from modalidades where id = '".$fase['id_modalidad']."'";
    $nombre_modalidad=mysqli_result(mysqli_query($connection,$query),0);
    $id_categoria=mysqli_result(mysqli_query($connection,$query),0,1);

	$pdf->SetFont('helvetica', '', 10);
//	$html = "<h2> $nombre_modalidad $nombre_categoria </h2>";
//	$pdf->writeHTML($html, true, false, true, false, '');

	$html = '<table cellpadding="2" cellspacing="2" nobr="false">';
	//segun titulo de documento
	if($titulo =='Rutinas' or $titulo =='Orden de salida'){
        if($titulo == 'Orden de salida')
            $order_by = 'order by orden';
    $query = "select * from rutinas where id_fase='".$fase['id']."' $order_by";
	$rutinas = mysqli_query($connection,$query);
    if($titulo == 'Rutinas')
        $cantidad = ' ('.mysqli_num_rows($rutinas).')';

    if(mysqli_num_rows($rutinas)>0) {
	   $html .= '<thead><tr><th width="80%"><h1>'.$nombre_modalidad.' '.$nombre_categoria.$cantidad.'</h1></th><th></th></tr></thead>';
	   $order_by = "";
    }
}
	//fin segun titulo documento

	$par=0;
	while($rutina = mysqli_fetch_array($rutinas)){
		$par++;
		if($par%2==0)
			$rutina_color = $rutina_color_par;
		else
			$rutina_color = $rutina_color_impar;
		if(isset($_GET['musica']) && $_GET['musica'] == "si")
			$musica=$rutina['musica']." - ";
		else
			$musica = "";


			$query = "select nombre from clubes where id = '".$rutina['id_club']."'";
			$nombre_rutina=mysqli_result(mysqli_query($connection,$query),0)." ".$rutina['nombre'];


		//segun titulo de documento
		if($titulo =='Orden de salida'){
			$preswimmer = '';
			if($rutina['orden']=='-1')
				$preswimmer = " (PRESWIMMER)";
            else if($rutina['orden']=='-2')
				$preswimmer = " (EXHIBICIÓN)";
			$html .='<tr style="background-color:'.$rutina_color.'"><td width="90%"><h3>'.$nombre_rutina.$preswimmer.'</h3></td><td width="10%"><h1>'.$rutina['orden'].'</h1></td></tr>';
		}elseif($titulo =='Rutinas'){
			$preswimmer = '';
			if($rutina['orden']=='-1')
				$preswimmer = " (PRESWIMMER)";
            else if($rutina['orden']=='-2')
				$preswimmer = " (EXHIBICIÓN)";
			$html .='<tr style="background-color:'.$rutina_color.'"><td width="80%"><h3>'.$nombre_rutina.$preswimmer.'</h3></td><td width="20%"></td></tr>';
        }
		//fin segun titulo documento


		//datos participantes
		$query = "select * from rutinas_participantes where id_rutina='".$rutina['id']."'";
		$participantes = mysqli_query($connection,$query);
		while($participante = mysqli_fetch_array($participantes)){
                $titular = "";
				if($participante['reserva']=='si')
					$titular = "(RESERVA)";
				$query = "select nombre, apellidos, licencia, año_nacimiento from nadadoras where id = '".$participante['id_nadadora']."'";
                $participante = mysqli_fetch_assoc(mysqli_query($connection,$query));
				$nombre_participante=$participante['nombre'];
				$apellidos_participante=$participante['apellidos'];
				$year=$participante['año_nacimiento'];
				 //segun titulo de documento
				if($titulo =='Orden de salida'){
					$html .='<tr style="background-color:'.$rutina_color.'"><td colspan="2">&nbsp;&nbsp;'.$apellidos_participante.', '.$nombre_participante.' '.$titular.' ('.$year.')</td></tr>';
				}elseif($titulo =='Rutinas'){
					// $html .='<tr style="background-color:'.$rutina_color.'"><td width="50%">'.$apellidos_participante.', '.$nombre_participante.' '.$titular.'</td><td width="25%">'.$licencia_participante.'</td><td width="25%" style="text-align:right;">'.$fecha_nacimiento_participante.'</td></tr>';
					$html .='<tr style="background-color:'.$rutina_color.'"><td>'.$apellidos_participante.', '.$nombre_participante.' '.$titular.' ('.$year.')</td><td></td></tr>';
			}
				//fin segun titulo documento

		}

	   }
	$html .= '</table>';
	if(isset($GET['hora_inicio']))
		$html .= '<p>Hora de inicio estimada: '.$fase['hora_inicio_estimada'].'h (sujeto a modificaciones, posibles adelantos).</p>';
	$pdf->writeHTML($html, true, false, true, false, '');

}






// -----------------------------------------------------------------------------

//Close and output PDF document
$pdf->Output($nombre_documento, 'I');

//============================================================+
// END OF FILE
//============================================================+
