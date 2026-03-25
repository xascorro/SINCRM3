<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//    error_reporting(E_ALL);
setlocale(LC_ALL,'es_ES');
require_once('../tcpdf/tcpdf.php');
include('../database/dbconfig.php');
include('../lib/my_functions.php');
session_start();
if(!$_SESSION['email']){
	header('Location: ../login.php');
}

    $GLOBALS["id_competicion_activa"] = 0;
	$GLOBALS["nombre_competicion_activa"] = "No hay competición activa";
    $query = "select * from competiciones where activo='si'";     // Esta linea hace la consulta
$result= mysqli_query($connection,$query);
    while ($registro = mysqli_fetch_array($result)){
	    $GLOBALS["id_competicion_activa"] = $registro['id'];
	    $GLOBALS["nombre_competicion_activa"] = $registro['nombre'];
	    $GLOBALS["lugar"] = $registro['lugar'];
	    $GLOBALS["fecha"] = dateAFecha($registro['fecha']);
	    $GLOBALS["organizador"] = $registro['organizador'];
	    $GLOBALS["header"] = $registro['header_informe'];
	    $GLOBALS["footer"] = $registro['footer_informe'];	}
//****************************//
$extricto = @$_GET['extricto'];
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
        $this->WriteHTML('<img style="border-bottom:1px #cecece;" src="'.$GLOBALS['header_image'].'">', false, false, false, false, '');
        $this->SetXY(25,12);
        $this->WriteHTML('<div style="text-align:center; font-size:large; font-weight:bold">'.$GLOBALS['titulo_documento']."</div>", false, false, false, false, '');

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



       $this->SetXY(25,278);
		$pagenumtxt = $this->PageNo().' de '.($this->getAliasNbPages());
        $this->WriteHTML('<div style="text-align:right; font-size:large; font-weight:bold">'.$GLOBALS['footer_substring'].'<br>Página '.$pagenumtxt.'</div>', false, false, false, false, '');
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
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page eaks
$pdf->SetAutoPagebreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
    require_once(dirname(__FILE__).'/lang/eng.php');
    $pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

// set font
$pdf->SetFont('helvetica', 'B', 12);

// -----------------------------------------------------------------------------

//$pdf->Write(0, $titulo_documento, '', 0, 'L', true, 0, false, false, 0);

//$pdf->writeHTML($html, true, false, true, false, '');

//$query = "select * from clubs";
$query = "select * from clubes where id in (select id_club from rutinas where id_competicion='".$GLOBALS["id_competicion_activa"]."') order by federacion, id";
$clubs = mysqli_query($connection, $query);
$nadadoras_totales = 0;
$numero_nadadoras_con_error = 0;
$numero_nadadoras_con_error_totales = 0;
$error_color = "#E65B5E";
$color_par = "#DEDEDE";
$color_impar = "#FFFFFF";
while($club = mysqli_fetch_array($clubs)){
	$nadadoras_con_error = '';
	$numero_nadadoras_con_error = 0;
	$contiene_error = false;

	// add a page
	$pdf->AddPage();
	// set background image
/*	$pdf->SetAlpha(0.3);
	$img_file = '../'.$club['logo'];
	$pdf->Image($img_file, 0, 0, '', '', '', '', 'center', false, 300, '', false, false, 0);
	$pdf->SetAlpha(1);*/
	// set encabezados
	$html = "<h2>".$club['nombre'].' - Cod. '.$club['codigo'].'&nbsp;&nbsp;&nbsp;<img src="../'.$club['logo'].'" width="40" border="0" /></h2>';

	$pdf->writeHTML($html, true, false, true, false, '');

	$pdf->SetFont('helvetica', '', 12);

	$html = '<table style="margin-top=10px">';
//	$html .= '<thead><tr><th><h4>Nombre</h4></th><th width="20%"></th></tr></thead>';
	// $html .= '<thead><tr><th width="60%"><h4>Nombre</h4></th><th width="20%"><h4>Licencia</h4></th><th width="20%" aling="right"><h4>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;F. nac.</h4></th></tr></thead>';
	$par = 1;

	//$query = "select * from nadadoras where club='".$club['id']."'";
	$query="select * from nadadoras where club='".$club['id']."' and id in (select id_nadadora from rutinas_participantes where id_competicion='".$GLOBALS["id_competicion_activa"]."') order by año_nacimiento desc, apellidos asc";
	$nadadoras = mysqli_query($connection,$query);

	if(mysqli_num_rows($nadadoras)>35)
		$pdf->SetFont('helvetica', '', 11);
	if(mysqli_num_rows($nadadoras)>42)
		$pdf->SetFont('helvetica', '', 10);
    if(mysqli_num_rows($nadadoras)>=50)
		$pdf->SetFont('helvetica', '', 9);
	while($nadadora = mysqli_fetch_array($nadadoras)){
		$par++;
		if($par%2==0)
			$color = $color_par;
		else
			$color = $color_impar;
		if($extricto == 'si' and (!is_numeric($nadadora['licencia']) & strpos($nadadora['licencia'] == false) or strlen($nadadora['licencia'])!=9 or $nadadora['fecha_nacimiento'] == "0000-00-00" or $nadadora['fecha_nacimiento'] == "")){
			$color = $error_color;
			$contiene_error = true;
			$nadadoras_con_error .= ' #'.$nadadora['id'];
			$numero_nadadoras_con_error++;
		}
		$html .='<tr style="background-color:'.$color.';"><td>#'.$nadadora['id'].' - '.$nadadora['apellidos'].', '.$nadadora['nombre'].' ('.$nadadora['año_nacimiento'].')</td></tr>';
		// $html .='<tr style="background-color:'.$color.';"><td width="60%">#'.$nadadora['id'].' - '.$nadadora['apellidos'].', '.$nadadora['nombre'].'</td><td width="20%">'.$nadadora['licencia'].'</td><td width="20%" style="text-align:right;">'.$nadadora['fecha_nacimiento'].'</td></tr>';
	}
	$nadadoras_por_club = mysqli_num_rows($nadadoras);
//	$nadadoras_por_club = mysqli_num_rows($nadadoras) - $nadadoras_con_error;
	$nadadoras_totales += $nadadoras_por_club;
	$html .= '</table><p style="font-size:8px">Por favor, compruebe que el número de nadadoras y sus datos son correctos.</p>';
	$pdf->writeHTML($html, true, false, false, false, '');
	if($contiene_error){
		$html = '<p style="font-size:8px">Revise y rectifique el número de licencia federativa y/o fecha de nacimiento de las nadadoras: '.$nadadoras_con_error.'</p>';
		$pdf->writeHTML($html, true, false, true, false, '');
		$html = '<h3 style="font-size:8px">Nadadoras preinscritas: '.($nadadoras_por_club-$numero_nadadoras_con_error).' - Nadadoras con error: '.$numero_nadadoras_con_error.'<h3>';
		$numero_nadadoras_con_error_totales += $numero_nadadoras_con_error;
		$pdf->writeHTML($html, true, false, true, false, '');
	}else{
		$html = '<h3 style="font-size:8px">Nadadoras preinscritas: '.$nadadoras_por_club.'<h3>';
	$pdf->writeHTML($html, true, false, true, false, '');
	}
}
$html = "<h5>Total preinscripciones : ".($nadadoras_totales-$numero_nadadoras_con_error_totales)."</h5>";
$html .= "<h5>Total preinscripciones erroneas : $numero_nadadoras_con_error_totales</h5>";
$pdf->writeHTML($html, true, false, true, false, '');





//$pdf->writeHTML($html, true, false, true, false, '');

// -----------------------------------------------------------------------------

//Close and output PDF document
$pdf->Output($nombre_documento, 'I');

//============================================================+
// END OF FILE
//============================================================+
