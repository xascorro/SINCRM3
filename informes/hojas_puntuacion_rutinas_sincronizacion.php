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
//include('../lib/conexion_abre.php');
include('../lib/my_functions.php');
include('../database/dbconfig.php');
session_start();
$id_categoria = $_GET['id_categoria'];



    $query = "select * from competiciones where activo='si'";     // Esta linea hace la consulta
    $result = mysqli_query($connection,$query);
    while ($registro = mysqli_fetch_array($result)){
	    $GLOBALS["id_competicion_activa"] = $registro['id'];
	    $GLOBALS["nombre_competicion_activa"] = $registro['nombre'];
	    $GLOBALS["lugar"] = $registro['lugar'];
	    $GLOBALS["piscina"] = $registro['piscina'];
	    $GLOBALS["fecha"] = dateAFecha($registro['fecha']);
	    $GLOBALS["organizador"] = $registro['organizador'];
	    $GLOBALS["organizador_tipo"] = $registro['organizador_tipo'];
	    $GLOBALS["hora_inicio"] = $registro['hora_inicio'];
	    $GLOBALS["hora_fin"] = $registro['hora_fin'];
	}
//****************************//
//$titulo = $_GET['titulo'];
$titulo = 'hoja puntuacion';
$titulo_documento = $GLOBALS['nombre_competicion_activa']."<br>$titulo";
$nombre_documento = $titulo.' '.$GLOBALS['nombre_competicion_activa'];
$GLOBALS['footer_substring'] = "Sede: ".$GLOBALS['lugar']."\n-- Fecha de la competición: ".$GLOBALS['fecha'];
$logo_header_width= 100;
$GLOBALS['header_image'] = '../images/header_regional.jpg';
$GLOBALS['footer_image'] = '../images/footer_regional.jpg';

// Extend the TCPDF class to create custom Header and Footer

class MYPDF extends TCPDF {

    //Page header
    public function Header() {/*
        // Logo
        $this->SetFont('helvetica', 12);
        $this->WriteHTML('<img style="border-bottom:1px #cecece;" src="'.$GLOBALS['header_image'].'">', false, false, false, false, '');
        $this->SetXY(25,12);
        $this->WriteHTML('<div style="text-align:center; font-size:large; font-weight:bold">'.$GLOBALS['titulo_documento']."</div>", false, false, false, false, '');
*/
    }

    //Page footer
    public function Footer() {
        // Logo
        /*
        $this->SetFont('helvetica', 12);
$x = 15;
$y = 270;
$w = '180';
$h = '';
        $this->Image($GLOBALS['footer_image'], $x, $y, $w, $h, 'JPG', '', '', false, 300, '', false, false, 0, 'L', false, false);



        $this->SetXY(25,278);
		$pagenumtxt = $this->getAliasNumPage().' de '.$this->getAliasNbPages();
        $this->WriteHTML('<div style="text-align:center; font-size:large; font-weight:bold">'.$GLOBALS['footer_substring'].'<br>Página '.$pagenumtxt.'</div>', false, false, false, false, '');
    */
    }

}


// create new PDF document
$pdf = new MYPDF('portrait', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
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
//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetMargins(5,5,5);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page eaks
$pdf->SetAutoPagebreak(TRUE, 5);

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

$hojas_por_pagina = 6;
$contador_hojas_por_pagina = 0;
//$pdf->AddPage();
$html = "";

// -----------------------------------------------------------------------------
//saco fases_figuras
$query = "select id_categoria, id_modalidad, id, orden, elementos_coach_card from fases where id_competicion ='".$GLOBALS["id_competicion_activa"]."' order by orden";
$fases = mysqli_query($connection,$query);
while($fase = mysqli_fetch_array($fases)){
	$query = "select nombre from categorias where id = '".$fase['id_categoria']."'";
    $nombre_categoria=mysqli_result(mysqli_query($connection,$query),0);
	$query = "select nombre from modalidades where id = '".$fase['id_modalidad']."'";
    $nombre_modalidad=mysqli_result(mysqli_query($connection,$query),0);
    	   	//saco orden de salida nadadoras
	   	$query = "select orden, id from rutinas where id_fase = '".$fase['id']."' order by orden";
	   	$ordenes = mysqli_query($connection,$query);
	   	while($orden = mysqli_fetch_assoc($ordenes)){
			if($orden['orden'] == '-1')
				$orden['orden'] = 'PS';
			//imprimo
			$pdf->SetFont('helvetica', '', 18);
            $html = '<br>';
			$html .= '<table align="center" border="3" width="100%">';
			$html .= '<tr><th colspan="3"><span style="font-size:24px">'.$GLOBALS["nombre_competicion_activa"].'</span></th></tr>';
			$html .= '<tr><th colspan="3">'.'<span style="font-size:16">ERRORES DE SINCRONIZACIÓN</span></th></tr>';
			$html .= '<tr><th colspan="3">'.'<span style="font-size:16">'.$nombre_modalidad.' '.$nombre_categoria.' - Orden '.$orden['orden'].'</span>'.'</th></tr>';

            			$html .= '<tr><th><span>PEQUEÑOS</span></th><th><span>OBVIOS</span></th><th><span>MAYORES</span></th></tr>';

			$rowspan = '';
			for ($x=0;$x<=12;$x++){
				$rowspan .='<br>';
			}

			$html .= '<tr><td>'.$rowspan.'</td><td>'.$rowspan.'</td><td>'.$rowspan.'</td></tr>';
			$html .= '</table>';			//diseño 2 x 2
			if($contador_hojas_por_pagina == 0){
				$pdf->AddPage();
				$html2 .= '<table><tr><td>'.$html.'</td></tr><tr><td colspan="3"></td></tr><tr><td colspan="3"></td></tr>';
				$contador_hojas_por_pagina++;
			}elseif ($contador_hojas_por_pagina == 1){
				$html2 .= '<tr><td>'.$html.'</td></tr></table>';
				$contador_hojas_por_pagina = 0;
				$pdf->writeHTML($html2, true, false, false, false, '');
				$html2 = "";
			}
		}

}
				$html_extra = "";
				if($contador_hojas_por_pagina == 1)
					$html_extra = '</table>';
				$pdf->writeHTML($html2.$html_extra, true, false, false, false, '');

// -----------------------------------------------------------------------------

//Close and output PDF document
$pdf->Output('test', 'I');

//============================================================+
// END OF FILE
//============================================================+
