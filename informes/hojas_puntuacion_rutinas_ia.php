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
    //saco numeros de juez del panel tipo 2 (Artístico) y quito si hay un juez de con nombre Media
   	$query = "select paneles.nombre, numero_juez from panel_jueces, paneles, jueces where id_fase = '".$fase['id']."' and id_panel = paneles.id and paneles.id_paneles_tipo = 2 and jueces.id = panel_jueces.id_juez and jueces.nombre not like 'Media' order by numero_juez";

   	$jueces = mysqli_query($connection,$query);
   	while($juez = mysqli_fetch_array($jueces)){
	   	//saco orden de salida nadadoras
	   	$query = "select orden, id from rutinas where id_fase = '".$fase['id']."' order by orden";
	   	$ordenes = mysqli_query($connection,$query);
		$query = "select orden, id from rutinas where id_fase = '".$fase['id']."' and orden > 0 order by orden";
		$orden_maximo = mysqli_num_rows(mysqli_query($connection,$query));
	   	while($orden = mysqli_fetch_assoc($ordenes)){
			if($orden['orden'] == '-1')
				$orden['orden'] = 'PS';
			//imprimo
			$pdf->SetFont('helvetica', '', 18);
            $html = '<br>';
			$html .= '<table align="center" border="2" width="100%">';
			$html .= '<tr><th colspan="2"><span style="font-size:24px">'.$GLOBALS["nombre_competicion_activa"].'</span></th></tr>';
			$html .= '<tr><th colspan="2">'.'<span style="font-size:14">J'.$juez['numero_juez'].' - '.$nombre_modalidad.' '.$nombre_categoria.' - Orden '.$orden['orden'].' de '.$orden_maximo.'</span>'.'</th></tr>';
			$html .= '<tr><th>'.'<span>IMP. ARTÍSTICA</span></th><th>'.'<span>NOTA</span>'.'</th></tr>';

            $html .= "<tr><td style='align:left'>ChoMu<br></td><td></td></tr>";
            $html .= "<tr><td style='align:left'>Performance<br></td><td></td></tr>";
            $html .= "<tr><td style='align:left'>Transitions<br></td><td></td></tr>";
			$rowspan = '';
			for ($x=0;$x<=6;$x++){
				$rowspan .='<br>';
			}
			$html .= '<tr><td colspan="2">'.$rowspan.'</td></tr>';
			$html .= '</table>';			//diseño 2 x 2
			if($contador_hojas_por_pagina == 0){
				$pdf->AddPage();
				$html2 .= '<table><tr><td  width="48%">'.$html.'</td><td width="4%"></td>';
				$contador_hojas_por_pagina++;
//			}elseif ($contador_hojas_por_pagina < 2){
//				$html2 .= '<td>'.$html.'</td>';
//				$contador_hojas_por_pagina++;
			}elseif ($contador_hojas_por_pagina == 1){
				$html2 .= '<td  width="48%">'.$html.'</td></tr><tr><td colspan=3></td></tr>';
				$contador_hojas_por_pagina++;
			}elseif ($contador_hojas_por_pagina == 2){
				$html2 .= '<tr><td  width="48%">'.$html.'</td><td width="4%"></td>';
				$contador_hojas_por_pagina++;
//			}elseif ($contador_hojas_por_pagina == 4){
//				$html2 .= '<td>'.$html.'</td>';
//				$contador_hojas_por_pagina++;
			}elseif ($contador_hojas_por_pagina == 3){
				$html2 .= '<td  width="48%">'.$html.'</td></tr></table>';
				$contador_hojas_por_pagina = 0;
				$pdf->writeHTML($html2, true, false, false, false, '');
				$html2 = "";
			}

		}
	}
}
				$html_extra = "";
				if($contador_hojas_por_pagina == 1)
					$html_extra = '<td></td></tr></table>';
				$pdf->writeHTML($html2.$html_extra, true, false, false, false, '');

// -----------------------------------------------------------------------------

//Close and output PDF document
$pdf->Output('test', 'I');

//============================================================+
// END OF FILE
//============================================================+
