<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
// Include the main TCPDF liary (search for installation path).
require_once('../tcpdf/tcpdf.php');
include('../database/dbconfig.php');
include('../lib/my_functions.php');
session_start();
if(!$_SESSION['username']){
	header('Location: login.php');
}else{
	$query = "SELECT id, nombre, color FROM competiciones WHERE activo = 'si'";
	$query_run = mysqli_query($connection,$query);
	$competicion = mysqli_fetch_assoc($query_run);
	$_SESSION['id_competicion_activa'] = $competicion['id'];
	$_SESSION['nombre_competicion_activa']= $competicion['nombre'];
	$_SESSION['color_competicion_activa']= $competicion['color'];
}

    $GLOBALS["id_competicion_activa"] = 0;
	$GLOBALS["nombre_competicion_activa"] = "No hay competición activa";
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
	    $GLOBALS["header"] = $registro['header_informe'];
	    $GLOBALS["footer"] = $registro['footer_informe'];
	    $GLOBALS["clave_liga"] = $registro['clave_liga'];
	    $GLOBALS["temporada"] = $registro['temporada'];
	    $GLOBALS["año"] = '';
	}
//****************************//
@$titulo = $_GET['titulo'];
$titulo_documento = $GLOBALS['temporada'].'<br>'.$GLOBALS['clave_liga']." de Natación Artística FNRM<br>$titulo";
$nombre_documento = $titulo.' '.$GLOBALS['nombre_competicion_activa'];
$GLOBALS['footer_substring'] = "Sede: ".$GLOBALS['lugar']."<br> Fecha de la competición: ".$GLOBALS['fecha'];
$logo_header_width= 100;
$GLOBALS['header_image'] = '../'.$GLOBALS['header'];
$GLOBALS['footer_image'] = '../'.$GLOBALS['footer'];

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {

    //Page header
    public function Header() {
        // Logo
        $this->SetFont('helvetica', 16);
        $this->WriteHTML('<img style="border-bottom:1px #e92662;" src="'.$GLOBALS['header_image'].'">', false, false, false, false, '');
        $this->SetXY(25,12);
        $this->WriteHTML('<div style="text-align:center; font-size:19px; font-weight:bold">'.$GLOBALS['titulo_documento']." ".$GLOBALS['ano']."</div>", false, false, false, false, '');

    }

    //Page footer
    public function Footer() {
        // Logo
        $this->SetFont('helvetica', 12);
$x = 100;
        $y = 180;
$w = '180';
$h = '';
        $this->Image($GLOBALS['footer_image'], $x, $y, $w, $h, 'JPG', '', '', false, 300, '', false, false, 0, 'L', false, false);



        $this->SetXY(6,190);
		$pagenumtxt = $this->PageNo().' de '.($this->getAliasNbPages());
        $this->WriteHTML('<div style="text-align:right; font-size:large; font-weight:bold">Página '.$pagenumtxt.'</div>', false, false, false, false, '');
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
$pdf->SetFont('helvetica', 'B', 14);

// -----------------------------------------------------------------------------
    $j1_color = '#FFCDD2';
    $j2_color = '#F8BBD0';
    $j3_color = '#E1BEE7';
    $j4_color = '#C5CAE9';
    $total_color = '#DCEDC8';
	$clave_liga = $GLOBALS['clave_liga'];
	$query = "select año from resultados_figuras where id_competicion in (select id from competiciones where clave_liga like '$clave_liga') group by año order by año desc";
	$años = mysqli_query($connection,$query);
	while($año = mysqli_fetch_array($años)){
        $query = "select nombre_corto, fecha, lugar, color from competiciones where clave_liga like '$clave_liga' order by id asc";
	$jornadas_liga = mysqli_query($connection,$query);
        // add a page
        $GLOBALS['ano'] = $año['año'];
	$pdf->AddPage('L');
	$pdf->SetFont('helvetica', '', 14);
	$html = "<br>";
	$pdf->writeHTML($html, true, false, true, false, '');
	$pdf->SetFont('helvetica', '', 12);
	$html = '<table align="center" border="1" cellpadding="4">';
	$html.= '<tr style= "font-weight:bold;"><th style="width:3%;"><span>&nbsp;</span><br><span style="font-size:18px">P</span></th><th style="width:36%"><span>&nbsp;</span><br><span style="font-size:18px">Nadadora</span></th><th width="23%"><span>&nbsp;</span><br><span style="font-size:18px">Club</span></th>';
	while($jornada_liga = mysqli_fetch_array($jornadas_liga)){
	   $html .= '<th width="10%" style="background-color:'.$jornada_liga['color'].'">'.$jornada_liga['nombre_corto'].'<br><span style="font-size:10px">'.dateAFecha($jornada_liga['fecha']).'</span><br><span style="font-size:10px">'.$jornada_liga['lugar'].'</span></th>';
	}
	$html .= '<th width="8%" style="background-color:'.$total_color.'"><span>&nbsp;</span><br><span style="font-size:18px">Total</span></th></tr>';
	 $i = 0;
		  $clasificacion_nadadoras[][] = "";
		  $query = "select distinct(id_nadadora) from resultados_figuras where año = '".$año['año']."'  and id_competicion in (select id from competiciones where clave_liga like '$clave_liga')";
		  $nadadoras = mysqli_query($connection,$query);

		  $numero_jornadas_liga = mysqli_result(mysqli_query($connection,"select count(id) from competiciones where clave_liga like '$clave_liga'"), 0);
		  while($nadadora = mysqli_fetch_array($nadadoras)){
			$puntos_totales = 0;
			$query = "select apellidos from nadadoras where id = '".$nadadora['id_nadadora']."'";
			$nombre_nadadora = mysqli_result(mysqli_query($connection,$query),0);
			$query = "select nombre from nadadoras where id = '".$nadadora['id_nadadora']."'";
			$nombre_nadadora .= ", ".mysqli_result(mysqli_query($connection,$query),0);
			$clasificacion_nadadoras[$i]['nombre'] =$nombre_nadadora;
			$query = "select nombre from clubes where id in (select club from nadadoras where id = '".$nadadora['id_nadadora']."')";
			$nombre_club = mysqli_result(mysqli_query($connection,$query),0);
			$clasificacion_nadadoras[$i]['nombre_club'] =$nombre_club;
			$query = "select id, color from competiciones where clave_liga like '$clave_liga'";
			$jornadas_liga = @mysqli_query($connection,$query);
			while($jornada_liga = mysqli_fetch_array($jornadas_liga)){
				$id_jornada_liga = $jornada_liga['id'];
				$query = "select coalesce(nota_final_calculada, puntos, 0) as nota_real, baja from resultados_figuras where id_nadadora = '".$nadadora['id_nadadora']."' and id_competicion = '".$id_jornada_liga."'";
				$res_puntos_j = mysqli_query($connection,$query);
				if($res_puntos_j && mysqli_num_rows($res_puntos_j) > 0) {
					$row_pj = mysqli_fetch_assoc($res_puntos_j);
					$puntos = (float)$row_pj['nota_real'];
					$puntos_display = ($puntos > 0) ? number_format($puntos, 4) : '0.0000';
				} else {
					$puntos = 0.0000;
					$puntos_display = 'NP';
				}
				if($id_jornada_liga > $GLOBALS['id_competicion_activa']){
					$puntos = 0.0000;
					$puntos_display = '-';
				}
				$clasificacion_nadadoras[$i]['c'.$id_jornada_liga] = $jornada_liga['color'];
				$clasificacion_nadadoras[$i][$id_jornada_liga] = $puntos_display;
				$puntos_totales = (float)$puntos_totales + (float)$puntos;
			}
      if(isset($_GET['resta_peor_puntuacion'])){
        $query = "select coalesce(min(nota_final_calculada), min(puntos), 0) from resultados_figuras where id_competicion in (select id from competiciones where clave_liga like '$clave_liga') and id_nadadora = '".$nadadora['id_nadadora']."'";
  			$puntos_minimos = (float)@mysqli_result(mysqli_query($connection,$query),0);
      }else{
        $puntos_minimos = 0.0000;
      }
      $query = "select count(puntos) from resultados_figuras where id_competicion in (select id from competiciones where clave_liga like '$clave_liga') and id_nadadora = '".$nadadora['id_nadadora']."'";
			$participacion_nadadora = @mysqli_result(mysqli_query($connection,$query),0);
			if($participacion_nadadora < $numero_jornadas_liga )
				$puntos_minimos = 0.0000;
			$clasificacion_nadadoras[$i]['c_puntos_totales'] = $total_color ;
			$clasificacion_nadadoras[$i]['puntos_totales'] = (float)$puntos_totales;
			$puntos_final_calc = (float)$puntos_totales - (float)$puntos_minimos;
			$clasificacion_nadadoras[$i]['puntos_clasificacion'] = $puntos_final_calc;
			$clasificacion_nadadoras[$i]['Total'] = number_format($puntos_final_calc, 4);
			if($clasificacion_nadadoras[$i]['puntos_totales'] == 0)
				unset($clasificacion_nadadoras[$i]);
		  $i++;
		  }
		  @usort($clasificacion_nadadoras,"cmpPuntosDesc");

		 $puntos_anterior = 1000;
		 $posicion = 0;
        $bgcolor1= '#cecece';
        $bgcolor2 = '#ffffff';
		 foreach($clasificacion_nadadoras as $clasificacion_nadadora){
		 	$puntos_liga_final = $clasificacion_nadadora['puntos_clasificacion'];
		 	unset($clasificacion_nadadora["puntos_totales"]);

		  	if(abs((float)$puntos_liga_final - (float)$puntos_anterior) > 0.0001){
		  		$posicion++;
                $bgcolor = $bgcolor1;
                $bgcolor1 = $bgcolor2;
                $bgcolor2 = $bgcolor;
            }
            $html .= '<tr style="background-color:'.$bgcolor.'">';
		  	$html .= '<td style="width:3%; font-size:12px; font-weight:bold; background-color:'.$bgcolor.'">'.$posicion.'</td>';
		  	$puntos_anterior = $puntos_liga_final;
            $color = '';
		  	foreach($clasificacion_nadadora as $k => $v){
                if(strpos($v, '#') === false and $v != ''){
                    $html .= '<td style="font-size:11px; background-color:'.$color.'">'.$v.'</td>';
                    $color = '';
                }
			}

		   $html .= '</tr>';

		  }
		   unset($clasificacion_nadadoras);
		   	$html .= '</table>';
	$pdf->writeHTML($html, true, false, false, false, '');
    /* Explicación normativa FNRM Punto 9 */
	$html = '<p style="font-size:10px; line-height:1.4;"><strong>Normativa de Clasificación (FNRM - Punto 9):</strong><br>
9.1 El resultado se obtiene del 100% de la puntuación oficial de las figuras y rutinas técnicas en cada jornada.<br>
9.2 Los resultados de cada nadadora se obtienen de la suma de las figuras y rutinas de las 3 mejores jornadas de las 4 disputadas (descartando la peor jornada).<br>
9.4 No se contempla el desempate en caso de puntuaciones idénticas (posiciones compartidas <em>ex aequo</em>).</p>';
	$pdf->SetFont('helvetica', 10);
	$pdf->writeHTML($html, true, false, false, false, '');


	}




//Función para ordenar clasificacion descendentemente
function cmpPuntosDesc($jugador1, $jugador2)
{
     $diff = (float)$jugador2["puntos_clasificacion"] - (float)$jugador1["puntos_clasificacion"];
     if(abs($diff) < 0.0001){
         // 9.4 No se contempla el desempate (ex aequo)
         return 0;
     }
     return ($diff > 0) ? 1 : -1;
}
// -----------------------------------------------------------------------------

//Close and output PDF document
$pdf->Output($nombre_documento.'.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+
