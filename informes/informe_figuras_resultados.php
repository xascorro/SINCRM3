<?php
require_once('../tcpdf/tcpdf.php');
include('../database/dbconfig.php');
include('../lib/my_functions.php');

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

$GLOBALS["id_competicion_activa"] = 0;
$GLOBALS["nombre_competicion_activa"] = "No hay competición activa";
$query = "select * from competiciones where activo='si'";     // Esta linea hace la consulta
$result = mysqli_query($connection,$query);
    while ($registro = mysqli_fetch_array($result)){
	    $GLOBALS["id_competicion_activa"] = $registro['id'];
	    $GLOBALS["nombre_competicion_activa"] = $registro['nombre'];
	    $_SESSION["lugar"] = $registro['lugar'];
	    $_SESSION["piscina"] = $registro['piscina'];
	    $_SESSION["fecha"] = dateAFecha($registro['fecha']);
	    $_SESSION["hora_inicio"] = $registro['hora_inicio'];
	    $_SESSION["hora_fin"] = $registro['hora_fin'];
	    $_SESSION["organizador_tipo"] = $registro['organizador_tipo'];
	    $_SESSION["organizador"] = $registro['organizador'];
        $GLOBALS["header_image"] = "../".$registro['header_informe'];
	    $GLOBALS["footer_image"] = "../".$registro['footer_informe'];
	    $GLOBALS["enmascarar_licencia"] = $registro['enmascarar_licencia'];
}
//****************************//
$titulo = $_GET['titulo'];
$titulo_documento = $GLOBALS['nombre_competicion_activa']."<br>$titulo";
$nombre_documento = $titulo.' '.$GLOBALS['nombre_competicion_activa'];
$GLOBALS['footer_substring'] = "Sede: ".$_SESSION['lugar']."<br> Fecha: ".$_SESSION['fecha'];
$logo_header_width= 100;

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {

    //Page header
    public function Header() {
        // Logo
        $this->SetFont('helvetica', 12);
        $this->WriteHTML('<img style="border-bottom:1px #e92662;" src="'.$GLOBALS['header_image'].'">', false, false, false, false, '');
        $this->SetXY(25,12);
        $this->WriteHTML('<div style="text-align:center; font-size:large; font-weight:bold">'.$GLOBALS['titulo_documento']."</div>", false, false, false, false, '');

    }

    //Page footer
    public function Footer() {
        // Logo
        $this->SetFont('helvetica', 12);
$x = 15;
$y = 275;
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
$pdf->SetFont('helvetica', 'B', 14);

// -----------------------------------------------------------------------------

//se imprime hoja tecnica si se necesita
if(isset($_GET['hoja_tecnica'])){
	$pdf->AddPage();
	$pdf->SetFont('helvetica', '', 14);
	$html_tec = '<table  nobr="true" style="margin-top=10px">';
	$html_tec .= "<tr><th></th><th></th><th></th><th></th></tr>";

	$html_tec .= '<tr><th style="background-color:#FCE4EC">Competición: </th><th colspan="3">'.$_SESSION["nombre_competicion_activa"].'</th></tr>';
	$html_tec .= '<tr><th style="background-color:#FCE4EC">Fecha y hora: </th><th colspan="3">'.$_SESSION["fecha"].' de '.$_SESSION['hora_inicio'].' a '.$_SESSION['hora_fin'].' horas</th></tr>';
	$html_tec .= '<tr><th style="background-color:#FCE4EC">Lugar celebración: </th><th colspan="3">'.$_SESSION["lugar"].'</th></tr>';
	$html_tec .= '<tr><th style="background-color:#FCE4EC">Piscina: </th><th colspan="3">'.$_SESSION["piscina"].'</th></tr>';
	if($_SESSION['organizador_tipo'] == 'Federación')
		$organizador = mysqli_result(mysqli_query($connection,"select nombre from federaciones where id='".$_SESSION['organizador']."'"),0) ;
	$html_tec .= '<tr><th style="background-color:#FCE4EC">Organizador: </th><th colspan="3">'.$organizador.'</th></tr>';
	$query = "select nombre, codigo from clubes where id in (select distinct club from nadadoras where id in (select distinct id_nadadora from inscripciones_figuras where id_competicion='".$_SESSION['id_competicion_activa']."'))";


	$clubes_participantes = mysqli_query($connection,$query);
	$clubes="";
	while($club = mysqli_fetch_array($clubes_participantes)){
		$clubes .= $club['nombre']." (".$club['codigo'].") - ";
	}
	$clubes = substr($clubes,0,-2);
	$clubes ="".
	$html_tec .= '<tr><th style="background-color:#FCE4EC">clubes participanes: </th><th colspan="3">'.$clubes.'</th></tr>';
	//obtengo datos de puesto jueces
	$html_tec .= '<tr><th colspan="4"></th></tr>';
	$query = "select * from puesto_juez, puestos_juez where id_puestos_juez=puestos_juez.id and id_competicion='".$_SESSION['id_competicion_activa']."'";
	$puesto_jueces = mysqli_query($connection,$query);
	$puesto_anterior = '';
	while($puesto_juez = mysqli_fetch_array($puesto_jueces)){
		$nombre = mysqli_result(mysqli_query($connection,"select nombre from jueces where id = '".$puesto_juez['id_juez']."'"), 0);
		$apellidos = mysqli_result(mysqli_query($connection,"select apellidos from jueces where id = '".$puesto_juez['id_juez']."'"), 0);
		$licencia = mysqli_result(mysqli_query($connection,"select licencia from jueces where id = '".$puesto_juez['id_juez']."'"), 0);
		$licencia = substr_replace($licencia, str_repeat('X',$_SESSION["enmascarar_licencia"]), sizeof($licencia)-$_SESSION['enmascarar_licencia']-1);

		$id_federacion = mysqli_result(mysqli_query($connection,"select federacion from jueces where id = '".$puesto_juez['id_juez']."'"), 0);
		$federacion = mysqli_query($connection,"select nombre_corto from federaciones where id = '".$id_federacion."'");
		$federacion = mysqli_result($federacion,0);
		if($puesto_anterior != $puesto_juez['nombre'])
			$html_tec .= '<tr style="background-color:#FCE4EC"><th colspan="4">'.$puesto_juez['nombre'].'</th></tr>';
		$puesto_anterior = $puesto_juez['nombre'];
		$html_tec .= '<tr><th>'.$licencia.'</th><th colspan="2">'.$nombre.' '.$apellidos.'</th><th>'.$federacion.'</th></tr>';

	}
		$html_tec .= '<tr style="background-color:#FCE4EC"><th colspan="4">Jueces de puntuación: </th></tr>';

	//obtengo datos jueces puntuacion
	$query = "select distinct id_juez from panel_jueces where id_competicion='".$_SESSION['id_competicion_activa']."' order by numero_juez";
	$jueces = mysqli_query($connection,$query);
	while($juez = mysqli_fetch_array($jueces)){
		$licencia_juez = mysqli_result(mysqli_query($connection,"select licencia from jueces where id = '".$juez['id_juez']."'"), 0);
		$licencia_juez = substr_replace($licencia_juez, str_repeat('X',$_SESSION["enmascarar_licencia"]), sizeof($licencia_juez)-$_SESSION['enmascarar_licencia']-1);
		$nombre_juez = mysqli_result(mysqli_query($connection,"select nombre from jueces where id = '".$juez['id_juez']."'"),0).' '.mysqli_result(mysqli_query($connection,"select apellidos from jueces where id = '".$juez['id_juez']."'"), 0);
		$federacion = mysqli_result(mysqli_query($connection,"select federacion from jueces where id = '".$juez['id_juez']."'"),0);
		$federacion = mysqli_result(mysqli_query($connection,"select nombre_corto from federaciones where id = '".$federacion."'"),0);
        if($nombre_juez != 'MEDIA ')
            $html_tec .= '<tr><th>'.$licencia_juez.'</th><th colspan="2">'.$nombre_juez.'</th><th>'.$federacion.'</th></tr>';

	}



	$html_tec .= '<tr><th colspan="3"></th><th><br>Fdo. Juez Árbitro<br></th></tr>';
	$html_tec .= "</table>";
	$pdf->writeHTML($html_tec, true, false, true, false, '');
}
//
//
//if(isset($_GET['ver_liga'])){
//	$clave_liga = mysqli_result(mysqli_query($connection,"select clave_liga from competiciones where id = '".$_SESSION['id_competicion_activa']."'"),0);
//	// add a page
//	$pdf->AddPage();
//	$pdf->SetFont('helvetica', '', 11);
//	$html = "<h3> Clasificación $clave_liga </h3>";
//	$pdf->writeHTML($html, true, false, true, false, '');
//	$pdf->SetFont('helvetica', '', 5);
//	$html = '<table align="center" border="1">';
//	$html.= '<tr style="width:5%; font-size:14; font-weight:bold;"><th style="width:5%;">P</th><th width="30%">Nadadora</th><th width="10%">Club</th>';
//	$query = "select nombre_corto from competiciones where clave_liga like '$clave_liga' order by id asc";
//	$jornadas_liga = mysqli_query($connection,$query);
//	while($jornada_liga = mysqli_fetch_array($jornadas_liga)){
//	$html .= '<th width="10%">'.$jornada_liga['nombre_corto'].'</th>';
//	}
//	$html .= '<th>Total</th></tr>';
//	$query = "select año from resultados_figuras where id_competicion in (select id from competiciones where clave_liga like '$clave_liga') group by año order by año";
//	$años = mysqli_query($connection,$query);
//	while($año = mysqli_fetch_array($años)){
//		  $i = 0;
//		  $clasificacion_nadadoras[] = "";
//		  $query = "select distinct(id_nadadora) from resultados_figuras where año = '".$año['año']."'  and id_competicion in (select id from competiciones where clave_liga like '$clave_liga')";
//		  $nadadoras = mysqli_query($connection,$query);
//			   $html .= '<tr style="background-color:#dedede;"><td colspan="8"><h2>Clasificación año '.$año['año'].'</h2></td>';
//			   $html .= "</tr>";
//		  $numero_jornadas_liga = mysqli_result(mysqli_query($connection,"select count(id) from competiciones where clave_liga like '$clave_liga'"), 0);
//		  while($nadadora = mysqli_fetch_array($nadadoras)){
//			$puntos_totales = 0;
//			$query = "select apellidos from nadadoras where id = '".$nadadora['id_nadadora']."'";
//			$nombre_nadadora = mysqli_result(mysqli_query($connection,$query),0);
//			$query = "select nombre from nadadoras where id = '".$nadadora['id_nadadora']."'";
//			$nombre_nadadora .= ", ".mysqli_result(mysqli_query($connection,$query),0);
//			$clasificacion_nadadoras[$i]['nombre'] =$nombre_nadadora;
//			$query = "select nombre_corto from clubes where id in (select club from nadadoras where id = '".$nadadora['id_nadadora']."')";
//			$nombre_club = mysqli_result(mysqli_query($connection,$query),0);
//			//$html .= '<tr><td>'.$nombre_nadadora.'</td><td>'.$nombre_club.'</td>';
//			$clasificacion_nadadoras[$i]['nombre_club'] =$nombre_club;
//			$query = "select id from competiciones where clave_liga like '$clave_liga%'";
//			$jornadas_liga = @mysqli_query($connection,$query);
//			while($jornada_liga = mysqli_fetch_array($jornadas_liga)){
//				$id_jornada_liga = $jornada_liga['id'];
//				$query = "select coalesce(puntos,0) from resultados_figuras where id_nadadora = '".$nadadora['id_nadadora']."' and id_competicion = '".$id_jornada_liga."'";
//				$puntos =@mysqli_result(mysqli_query($connection,$query), 0);
//				if($puntos == "" or $id_jornada_liga > $_SESSION['id_competicion_activa'])
//					$puntos = 0;
//				$clasificacion_nadadoras[$i][$id_jornada_liga] =$puntos;
//				$puntos_totales = $puntos_totales+$puntos;
//
//			}
//      if(isset($_GET['resta_peor_puntuacion'])){
//        $query = "select coalesce(min(puntos),0) from resultados_figuras where id_competicion in (select id from competiciones where clave_liga like '$clave_liga') and id_nadadora = '".$nadadora['id_nadadora']."'";
//  			$puntos_minimos = @mysqli_result(mysqli_query($connection,$query),0);
//      }else{
//        $puntos_minimos = 0;
//      }
//      $query = "select count(puntos) from resultados_figuras where id_competicion in (select id from competiciones where clave_liga like '$clave_liga') and id_nadadora = '".$nadadora['id_nadadora']."'";
//			$participacion_nadadora = @mysqli_result(mysqli_query($connection,$query),0);
//			//echo 'puntos-minimos'.$puntos_minimos.'participacion'.$participacion_nadadora.'jornadas'.$numero_jornadas_liga.'<br>';
//			if($participacion_nadadora < $numero_jornadas_liga )
//				$puntos_minimos = 0;
//			//$query = "select coalesce(sum(nota_final_calculada),0) - coalesce(min(nota_final_calculada),0) from resultados_figuras where id_competicion in (select id from competiciones where clave_liga like '%$clave_liga%') and id_nadadora = '".$nadadora['id_nadadora']."'";
//			//$nota_totalisima = mysqli_result(mysqli_query($connection,$query),0);
//			//$clasificacion_nadadoras[$i]['notas_totales'] = $nota_totalisima;
//			$clasificacion_nadadoras[$i]['puntos_totales'] = $puntos_totales;
//			$clasificacion_nadadoras[$i]['puntos_clasificacion'] = $puntos_totales-$puntos_minimos;
//			if($clasificacion_nadadoras[$i]['puntos_totales'] == 0)
//				unset($clasificacion_nadadoras[$i]);
//			//$html .= "<td>$puntos_totales</td></tr>";
//		  $i++;
//		  }
//		  @usort($clasificacion_nadadoras,"cmpPuntosDesc");
//		 //print_r($clasificacion_nadadoras);
//
//		 $puntos_anterior = 1000;
//		 $posicion = 0;
//		 foreach($clasificacion_nadadoras as $clasificacion_nadadora){
//		 	unset($clasificacion_nadadora["puntos_totales"]);
//
//		  	 $html .= '<tr>';
//		  	if($clasificacion_nadadora['puntos_clasificacion']!=$puntos_anterior)
//		  		$posicion++;
//		  	$html .= '<td style="width:5%; font-size:10px; font-weight:bold;">'.$posicion.'</td>';
//		  	$puntos_anterior=$clasificacion_nadadora['puntos_clasificacion'];
//		  	foreach($clasificacion_nadadora as $k => $v){
//			  $html .= '<td style="font-size:10px;">'.$v.'</td>';
//			}
//
//		   $html .= '</tr>';
//
//		  }
//		   unset($clasificacion_nadadoras);
//
//	  }
//	$html .= '</table>';
///*	explicar metodo puntuacion
//	$pdf->writeHTML($html, true, false, false, false, '');
//	$html = "En cada jornada de liga se repartirán 19, 16, 14, 13, 12, 11, 10, 9, 8, 7, 6, 5, 4, 3, 2, 1 y 0 puntos en función de la clasificación de dicha jornada (Primer puesto 19 puntos, segundo puesto 16 puntos ...).<br>La puntuación final de cada nadadora en la liga se obtendrá sumando los puntos obtenidos en cada jornada a excepción de la jornada con peor puntuación.<br>Si ocurriera un empate en la puntuación final se sumaran las notas finales calculadas dadas por los jueces en cada jornada de liga exceptuando la peor nota final calculada.";
//	$pdf->SetFont('helvetica', 14);*/
//	$pdf->writeHTML($html, true, false, false, false, '');
//
//}
//
//
//
////Función para ordenar clasificacion descendentemente
//function cmpPuntosDesc($jugador1, $jugador2){
//     //Si son iguales se devuelve 0
//     if($jugador1["puntos_clasificacion"]==$jugador2["puntos_clasificacion"]){
//   	    if($jugador1["puntos_totales"]==$jugador2["puntos_totales"])
//	         return 0;
//   	     if($jugador1["puntos_totales"]<$jugador2["puntos_totales"])
//	         return 1;
//	     return -1;
//     }
//     //Si jugador1 > 2 se devuelve 1 y por lo contrario -1
//     if($jugador1["puntos_clasificacion"]<$jugador2["puntos_clasificacion"])
//          return 1;
//     return -1;
//}


$error_color = "#E65B5E";
$rutina_color_par = '#FCE4EC';
$rutina_color_impar = '#FCE4EC';


//$condicion_ampliada = "";
//if(isset($_GET['id_fase'])){
//	$condicion_ampliada = "and id = '".$_GET['id_fase']."'";
//	$id_fase = $_GET['id_fase'];
//}

//RESULTADO PARA FIGURAS, NO RUTINAS TECNICAS DE FIGURAS
/*************************/
$query = "select resultados_figuras.id_categoria from resultados_figuras, fases WHERE fases.id_categoria = resultados_figuras.id_categoria and fases.elementos_coach_card < 1 and fases.id_competicion ='".$_SESSION["id_competicion_activa"]."' group by resultados_figuras.id_categoria";
$categorias = mysqli_query($connection,$query);
while($categoria = mysqli_fetch_array($categorias)){
	$query = "select nombre, id from categorias where id = '".$categoria['id_categoria']."'";
    $nombre_categoria=mysqli_result(mysqli_query($connection,$query),0);
	// add a page
	$pdf->AddPage();
	$pdf->SetFont('helvetica', '', 14);
	$html = "<h2> $nombre_categoria </h2>";
	$pdf->writeHTML($html, true, false, true, false, '');
	$pdf->SetFont('helvetica', '', 8);
// obtengo las figuras y el grado de dificultad
	$query = "select id_figura from fases where id_categoria='".$categoria['id_categoria']."' and id_competicion='".$_SESSION['id_competicion_activa']."'";
	$figuras = mysqli_query($connection,$query);
	$figuras_texto = '';
	while($figuras2 = mysqli_fetch_array($figuras)){
		$query = "select numero, grado_dificultad from figuras where id ='".$figuras2['id_figura']."'";
		$figuras3 = mysqli_query($connection,$query);
		$figuras4 = mysqli_fetch_row($figuras3);
		$figuras_texto .= $figuras4[0].' GD:'.$figuras4[1].'<br>';
	}
	$query = "select año from resultados_figuras where id_categoria='".$categoria['id_categoria']."' and id_competicion = '".$_SESSION["id_competicion_activa"]."' group by año order by año desc";
	$años = mysqli_query($connection,$query);
	while($año = mysqli_fetch_array($años)){
	$html = "<h2> Año ".$año['año']."</h2>";


$html .= '<table style="margin-top=10px">';
	$html .= '<thead><tr style="background-color:'.$rutina_color_par.'"><th style="width:5%">Pos.</th><th style="width:34%">Nadadora</th><th style="width:11%">Figura</th><th style="width:18%">Jueces</th><th style="width:7%">Nota</th><th style="width:8%">Pen.</th><th style="width:8%">Total</th><th style="width:7%">Dif</th><th style="width:7%">Puntos</th></tr></thead>';
	$par=0;
	$query = "select * from resultados_figuras where id_categoria='".$categoria['id_categoria']."' and id_competicion = '".$_SESSION["id_competicion_activa"]."' and año='".$año['año']."' order by posicion asc, nota_final_calculada desc";
	$resultados_figuras = mysqli_query($connection,$query);
	if(mysqli_num_rows($resultados_figuras)>16)
		$pdf->SetFont('helvetica', '', 7);
	else
		$pdf->SetFont('helvetica', '', 8);

	while($resultado_figuras = mysqli_fetch_array($resultados_figuras)){
		$baja=$resultado_figuras['baja'];
		$par++;
		if($par%2==0)
			$rutina_color = $rutina_color_par;
		else
			$rutina_color = $rutina_color_impar;
		$query = mysqli_query($connection,"select apellidos, nombre, licencia, club from nadadoras where id='".$resultado_figuras['id_nadadora']."'");
		$nombre_nadadora = mysqli_result($query, 0).", ".mysqli_result($query,0,1);
		$licencia_nadadora = mysqli_result($query, 0,2);
		$licencia_nadadora = substr_replace($licencia_nadadora, str_repeat('X',$_SESSION["enmascarar_licencia"]), sizeof($licencia_nadadora)-$_SESSION['enmascarar_licencia']-1);
		$club_nadadora = mysqli_result(mysqli_query($connection,"select nombre_corto from clubes where id = '".mysqli_result($query, 0,3)."'"),0);
		$html .= '<tr><td style="width:5%; font-size:14; font-weight:bold;">'.$resultado_figuras['posicion'].'</td>';
//		$html .= '<td style="width:34%">'.$nombre_nadadora.' ('.$licencia_nadadora.')<br>'.$club_nadadora.'</td>';
		$html .= '<td style="width:34%">'.$nombre_nadadora.'<br>'.$club_nadadora.'</td>';
		$html .= '<td style="width:11%">';
		$html .= $figuras_texto;

		$html .= '</td>';
		$celda_puntuaciones_jueces = '<td style="width:18%">';
		$celda_puntuaciones_paneles = '<td style="width:7%">';
		$celda_penalizacion_paneles = '<td style="width:8%">';
		$id_inscripcion_figuras = "";
	    $query = "select * from paneles where puntua='si' and id_competicion='".$_SESSION['id_competicion_activa']."'";
	    $id_panel = mysqli_result(mysqli_query($connection,$query), 0);
	    $fases = mysqli_query($connection,"select * from fases where id_categoria='".$categoria['id_categoria']."' and id_competicion='".$_SESSION['id_competicion_activa']."'");
	    while($fase = mysqli_fetch_array($fases)){
		    $query = "select * from inscripciones_figuras where id_nadadora='".$resultado_figuras['id_nadadora']."' and id_fase='".$fase['id']."' and id_competicion='".$id_competicion_activa."'";
		    $query = "select * from puntuaciones_jueces where id_inscripcion_figuras in (select id from inscripciones_figuras where id_nadadora='".$resultado_figuras['id_nadadora']."' and id_fase='".$fase['id']."') order by id_inscripcion_figuras, id_elemento, id_panel_juez";
		    $puntuaciones_jueces = mysqli_query($connection,$query);
		    while($puntuacion_juez = mysqli_fetch_array($puntuaciones_jueces)){
                if($puntuacion_juez['nota_menor'] == 'si' or $puntuacion_juez['nota_mayor'] == 'si')
                    $celda_puntuaciones_jueces .= '<span style="text-decoration:line-through">'.substr($puntuacion_juez['nota'],0,3)." </span>";
                else
                    $celda_puntuaciones_jueces .= substr($puntuacion_juez['nota'],0,3).' ';

		    }
		    $celda_puntuaciones_jueces .= "<br>";

		    $query = "select * from inscripciones_figuras where id_nadadora='".$resultado_figuras['id_nadadora']."' and id_fase='".$fase['id']."'";
		    $puntuaciones_jueces = mysqli_query($connection,$query);
		    while($puntuacion_juez = mysqli_fetch_array($puntuaciones_jueces)){
			    $celda_puntuaciones_paneles .= $puntuacion_juez['nota_final']." ";
		    }
		    $celda_puntuaciones_paneles .= "<br>";

		    $query = "select * from penalizaciones_rutinas where id_inscripcion_figuras in (select id from inscripciones_figuras where id_nadadora='".$resultado_figuras['id_nadadora']."' and id_fase='".$fase['id']."')";
		    $penalizacion_figura = mysqli_query($connection,$query);
		    while($penalizacion = mysqli_fetch_array($penalizacion_figura)){
			    $query = "select codigo as puntos from penalizaciones where id = ".$penalizacion['id_penalizacion'];
			    $puntos = mysqli_fetch_array(mysqli_query($connection,$query));
			    $celda_penalizacion_paneles .= $puntos['puntos'];
		    }
		    $celda_penalizacion_paneles.= "&nbsp;<br>";

		}
		$celda_puntuaciones_jueces .= '</td>';
		$celda_puntuaciones_paneles .= '</td>';
		$celda_penalizacion_paneles .= '</td>';
		$html .= $celda_puntuaciones_jueces.$celda_puntuaciones_paneles.$celda_penalizacion_paneles;
		//$html .= '<td style="width:8%">'.$resultado_figuras['puntos_penalizacion'].'</td>';
		if($resultado_figuras['baja']=='si'){
			$html .= '<td style="width:8%"></td>';
			$html .= '<td style="width:7%"></td>';
			$html .= '<td style="width:7%">BAJA</td></tr>';
        }elseif($resultado_figuras['preswimmer']=='si'){
			$html .= '<td style="width:8%">'.$resultado_figuras['nota_final_calculada'].'</td>';
			$html .= '<td style="width:7%">'.$resultado_figuras['diferencia'].'</td>';
			$html .= '<td style="width:7%">PRESWIMMER</td></tr>';
		}else{
			$html .= '<td style="width:8%; font-weight:bold">'.$resultado_figuras['nota_final_calculada'].'</td>';
			$html .= '<td style="width:7%">'.$resultado_figuras['diferencia'].'</td>';
			$html .= '<td style="width:7%; font-weight:bold">'.$resultado_figuras['puntos'].'</td></tr>';
		}




	}
	$html .= '</table>';
	$pdf->writeHTML($html, true, false, false, false, '');
}



	}
// -----------------------------------------------------------------------------


//RESULTADO PARA RUTINAS TECNICAS DE FIGURAS
/*************************/
$query = "select resultados_figuras.id_categoria, fases.id as id_fase from resultados_figuras, fases WHERE fases.id_categoria = resultados_figuras.id_categoria and fases.elementos_coach_card > 0 and fases.id_competicion ='".$_SESSION["id_competicion_activa"]."' group by resultados_figuras.id_categoria";
$categorias = mysqli_query($connection,$query);
while($categoria = mysqli_fetch_array($categorias)){
	$query = "select nombre, id from categorias where id = '".$categoria['id_categoria']."'";
    $nombre_categoria=mysqli_result(mysqli_query($connection,$query),0);
	// add a page
	$pdf->AddPage();
	$pdf->SetFont('helvetica', '', 14);
	$html = "<h2> $nombre_categoria </h2>";
	$pdf->writeHTML($html, true, false, true, false, '');
	$pdf->SetFont('helvetica', '', 8);
//obtengo los años
	$query = "select año from resultados_figuras where id_categoria='".$categoria['id_categoria']."' and id_competicion = '".$_SESSION["id_competicion_activa"]."' group by año order by año desc";
	$años = mysqli_query($connection,$query);
	while($año = mysqli_fetch_array($años)){
	$html = "<h2> Año ".$año['año']."</h2>";


$html .= '<table style="margin-top=10px">';
	$html .= '<thead><tr style="background-color:'.$rutina_color_par.'"><th style="width:5%">Pos.</th><th style="width:31%">Nadadora</th><th style="width:14%">T-TRE</th><th style="width:18%">Jueces</th><th style="width:7%">Nota</th><th style="width:8%">Pen.</th><th style="width:8%">Total</th><th style="width:7%">Dif</th><th style="width:7%">Puntos</th></tr></thead>';
	$par=0;
	$query = "select * from resultados_figuras where id_categoria='".$categoria['id_categoria']."' and id_competicion = '".$_SESSION["id_competicion_activa"]."' and año='".$año['año']."' order by posicion asc, nota_final_calculada desc";
	$resultados_figuras = mysqli_query($connection,$query);
	if(mysqli_num_rows($resultados_figuras)>16)
		$pdf->SetFont('helvetica', '', 7);
	else
		$pdf->SetFont('helvetica', '', 8);

	while($resultado_figuras = mysqli_fetch_array($resultados_figuras)){
		$baja=$resultado_figuras['baja'];
		$par++;
		if($par%2==0)
			$rutina_color = '#FCE4EC';
		else
			$rutina_color = '#FCE4EC';
		$query = mysqli_query($connection,"select apellidos, nombre, licencia, club, id from nadadoras where id='".$resultado_figuras['id_nadadora']."'");
		$nombre_nadadora = mysqli_result($query, 0).", ".mysqli_result($query,0,1);
		$licencia_nadadora = mysqli_result($query, 0,2);
		$licencia_nadadora = substr_replace($licencia_nadadora, str_repeat('X',$_SESSION["enmascarar_licencia"]), sizeof($licencia_nadadora)-$_SESSION['enmascarar_licencia']-1);
		$club_nadadora = mysqli_result(mysqli_query($connection,"select nombre_corto from clubes where id = '".mysqli_result($query, 0,3)."'"),0);


        $query = "select * from inscripciones_figuras where id_nadadora='".mysqli_result($query,0,4)."' and id_fase='".$categoria['id_fase']."' and id_competicion='".$id_competicion_activa."'";
        $inscripciones_figuras = mysqli_query($connection,$query);
        while($inscripcion_figuras = mysqli_fetch_array($inscripciones_figuras)){
                // obtengo los TRE y el grado de dificultad
                $query = "SELECT texto, valor FROM hibridos_rutina WHERE valor > 0 and tipo like 'dd' and id_rutina = ".$inscripcion_figuras['id'];
                $figuras = mysqli_query($connection,$query);
                $figuras_texto = '';
                while($figuras2 = mysqli_fetch_array($figuras)){
                    $figuras_texto .= $figuras2[0].' GD:'.$figuras2[1].'<br>';
                }
		    }



		$html .= '<tr><td style="width:5%; font-size:14; font-weight:bold;">'.$resultado_figuras['posicion'].'</td>';
//		$html .= '<td style="width:34%">'.$nombre_nadadora.' ('.$licencia_nadadora.')<br>'.$club_nadadora.'</td>';
		$html .= '<td style="width:31%">'.$nombre_nadadora.'<br>'.$club_nadadora.'</td>';
		$html .= '<td style="width:14%">';
		$html .= $figuras_texto;

		$html .= '</td>';
		$celda_puntuaciones_jueces = '<td style="width:18%">';
		$celda_puntuaciones_paneles = '<td style="width:7%">';
		$celda_penalizacion_paneles = '<td style="width:8%">';
		$id_inscripcion_figuras = "";
	    $query = "select * from paneles where puntua='si' and id_competicion='".$_SESSION['id_competicion_activa']."'";
	    $id_panel = mysqli_result(mysqli_query($connection,$query), 0);
	    $fases = mysqli_query($connection,"select * from fases where id_categoria='".$categoria['id_categoria']."' and id_competicion='".$_SESSION['id_competicion_activa']."'");
	    while($fase = mysqli_fetch_array($fases)){
		    $query = "select * from inscripciones_figuras where id_nadadora='".$resultado_figuras['id_nadadora']."' and id_fase='".$fase['id']."' and id_competicion='".$id_competicion_activa."'";
		    $query = "select * from puntuaciones_jueces where id_inscripcion_figuras in (select id from inscripciones_figuras where id_nadadora='".$resultado_figuras['id_nadadora']."' and id_fase='".$fase['id']."') order by id_inscripcion_figuras, id_elemento, id_panel_juez";
		    $puntuaciones_jueces = mysqli_query($connection,$query);
            $numero_jueces = "SELECT count(id) FROM panel_jueces WHERE id_elemento =  '1' and ".$inscripcion_figuras['id'];
            $numero_jueces = mysqli_result(mysqli_query($connection,$query));
            $i=0;
		    while($puntuacion_juez = mysqli_fetch_array($puntuaciones_jueces)){
                $i++;
                if($puntuacion_juez['nota_menor'] == 'si' or $puntuacion_juez['nota_mayor'] == 'si')
                    $celda_puntuaciones_jueces .= '<span style="text-decoration:line-through">'.substr($puntuacion_juez['nota'],0,4)." </span>";
                else
                    $celda_puntuaciones_jueces .= substr($puntuacion_juez['nota'],0,4).' ';
//                if($i == 5){
                if($i == 3){
                    $celda_puntuaciones_jueces .= "<br>";
                    $i=0;
                }

            }


		    $celda_puntuaciones_jueces .= "<br>";
		    $query = "select nota from puntuaciones_elementos where id_rutina in (select id from inscripciones_figuras where id_nadadora='".$resultado_figuras['id_nadadora']."' and id_fase='".$fase['id']."')";
		    $puntuacion_final = mysqli_result(mysqli_query($connection,$query),0);
		    $celda_puntuaciones_paneles .= $puntuacion_final."<br>";
            $puntuacion_final = mysqli_result(mysqli_query($connection,$query),1);
		    $celda_puntuaciones_paneles .= $puntuacion_final."<br>";
            $puntuacion_final = mysqli_result(mysqli_query($connection,$query),2);
		    $celda_puntuaciones_paneles .= $puntuacion_final."<br>";
            $puntuacion_final = mysqli_result(mysqli_query($connection,$query),3);
		    $celda_puntuaciones_paneles .= $puntuacion_final."<br>";
            $puntuacion_final = mysqli_result(mysqli_query($connection,$query),4);
		    $celda_puntuaciones_paneles .= $puntuacion_final."<br>";

            //penalizaciones
		    $query = "select * from penalizaciones_rutinas where id_inscripcion_figuras in (select id from inscripciones_figuras where id_nadadora='".$resultado_figuras['id_nadadora']."' and id_fase='".$fase['id']."')";
		    $penalizacion_figura = mysqli_query($connection,$query);
		    while($penalizacion = mysqli_fetch_array($penalizacion_figura)){
			    $query = "select codigo as puntos from penalizaciones where id = ".$penalizacion['id_penalizacion'];
			    $puntos = mysqli_fetch_array(mysqli_query($connection,$query));
			    $celda_penalizacion_paneles .= $puntos['puntos'];
		    }
		    $celda_penalizacion_paneles.= "&nbsp;<br>";

		}
		$celda_puntuaciones_jueces .= '</td>';
		$celda_puntuaciones_paneles .= '</td>';
		$celda_penalizacion_paneles .= '</td>';
		$html .= $celda_puntuaciones_jueces.$celda_puntuaciones_paneles.$celda_penalizacion_paneles;
		//$html .= '<td style="width:8%">'.$resultado_figuras['puntos_penalizacion'].'</td>';
		if($resultado_figuras['baja']=='si'){
			$html .= '<td style="width:8%"></td>';
			$html .= '<td style="width:7%"></td>';
			$html .= '<td style="width:7%">BAJA</td></tr>';
        }elseif($resultado_figuras['preswimmer']=='si'){
			$html .= '<td style="width:8%">'.$resultado_figuras['nota_final_calculada'].'</td>';
			$html .= '<td style="width:7%">'.$resultado_figuras['diferencia'].'</td>';
			$html .= '<td style="width:7%">PRESWIMMER</td></tr>';
		}else{
			$html .= '<td style="width:8%; font-weight:bold">'.$resultado_figuras['nota_final_calculada'].'</td>';
			$html .= '<td style="width:7%">'.$resultado_figuras['diferencia'].'</td>';
			$html .= '<td style="width:7%; font-weight:bold">'.$resultado_figuras['puntos'].'</td></tr>';
		}




	}
	$html .= '</table>';
	$pdf->writeHTML($html, true, false, false, false, '');
}



	}
// -----------------------------------------------------------------------------

//Close and output PDF document
$pdf->Output($nombre_documento.'.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+
