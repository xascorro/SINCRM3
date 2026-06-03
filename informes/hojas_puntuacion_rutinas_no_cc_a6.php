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
$pdf->SetMargins(1,5,5);
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

$hojas_por_pagina = 4;
$contador_hojas_por_pagina = 0;
//$pdf->AddPage();
$html = "";

// -----------------------------------------------------------------------------
//saco fases_figuras
$query = "select id_categoria, id_modalidad, id, orden, elementos_coach_card from fases where id_competicion ='".$GLOBALS["id_competicion_activa"]."' order by orden limit 1,2";
$query = "select id_categoria, id_modalidad, id, orden, elementos_coach_card from fases where id_competicion ='".$GLOBALS["id_competicion_activa"]."' and elementos_coach_card = 0 order by orden";
$fases = mysqli_query($connection,$query);
while($fase = mysqli_fetch_array($fases)){
	$query = "select nombre from categorias where id = '".$fase['id_categoria']."'";
    $nombre_categoria=mysqli_result(mysqli_query($connection,$query),0);
	$query = "select nombre from modalidades where id = '".$fase['id_modalidad']."'";
    $nombre_modalidad=mysqli_result(mysqli_query($connection,$query),0);
    //saco numeros de juez del panel tipo 1 (Elementos) y quito si hay un juez de con nombre Media
   	$query = "select paneles.nombre, numero_juez from panel_jueces, paneles, jueces where id_fase = '".$fase['id']."' and id_panel = paneles.id and paneles.id_paneles_tipo = 2 and jueces.id = panel_jueces.id_juez and jueces.nombre not like 'Media' order by numero_juez";

   	$jueces = mysqli_query($connection,$query);
   	while($juez = mysqli_fetch_array($jueces)){
		$tipo_hojas = ['DIFICULTAD','EJECUCIÓN','ARTÍSTIC0'];
			foreach($tipo_hojas as $tipo_hoja){
	   	//saco orden de salida nadadoras
	   	$query = "select orden, id from rutinas where id_fase = '".$fase['id']."' order by orden";
	   	$ordenes = mysqli_query($connection,$query);
	   	while($orden = mysqli_fetch_assoc($ordenes)){
			if($orden['orden'] == '-1')
				$orden['orden'] = 'PS';
			//imprimo
			$pdf->SetFont('helvetica', '', 18);

			$html = '<table align="center" border="2" width="100%" cellpadding="5">';
			$html .= '<tr><th colspan="2"><span style="font-size:24px">'.$GLOBALS["nombre_competicion_activa"].'</span></th></tr>';
			$html .= '<tr><th colspan="2">'.'<span style="font-size:16">J'.$juez['numero_juez'].' - '.$nombre_modalidad.' '.$nombre_categoria.' - Orden '.$orden['orden'].'</span>'.'</th></tr>';
			$html .= '<tr><th width="60%">'.'<span>'.$tipo_hoja.'</span></th><th width="40%">'.'<span>NOTA</span>'.'</th></tr>';
			$x=1;
            for ($x;$x<=$fase['elementos_coach_card'];$x++){
                $query = "SELECT nombre, texto FROM hibridos_rutina, tipo_hibridos WHERE tipo like 'part' and texto=tipo_hibridos.id and nombre not like 'TRANSITION' and id_rutina = ".$orden['id']." and elemento=$x";
                $tipo_elemento = mysqli_result(mysqli_query($connection,$query),0);
                $tipo_acro = mysqli_result(mysqli_query($connection,$query),1,2);
                if($tipo_elemento == 'TRE'){
                    $query = "select texto from hibridos_rutina where texto like '%-TRE%' and id_rutina = ".$orden['id']." and elemento=$x";
                    $nombre = mysqli_result(mysqli_query($connection,$query),0);
                    $nombre = substr($nombre,2,5);
                }else if($tipo_elemento == 'ACROBATIC'){
                    $query = "SELECT texto FROM hibridos_rutina WHERE tipo like 'basemark' and id_rutina = ".$orden['id']." and elemento=$x";
                    $nombre = mysqli_result(mysqli_query($connection,$query),0);
                }else{
                    $nombre = $tipo_elemento;
                }
                $html .= '<tr align="left"><td> '.$x.' - '.$nombre.'</td><td></td></tr>';
            }

			if($x<8){
				$rowspan = '<br>';
			}else{
				$rowspan = '';
			}
			if($x<=9){
				for ($x;$x<=9;$x++){
					$rowspan .='<br>';
				}
				$html .= '<tr><td colspan="2">'.$rowspan.'</td></tr>';
			}
			$html .= '</table>';
			$pdf->AddPage('P','A6');
			$pdf->writeHTML($html, true, false, false, false, '');
		}
		}
	}
}

// -----------------------------------------------------------------------------

//Close and output PDF document
$pdf->Output('test', 'I');

//============================================================+
// END OF FILE
//============================================================+
