<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
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
//		$licencia = substr_replace($licencia, str_repeat('X',$_SESSION["enmascarar_licencia"]), sizeof($licencia)-$_SESSION['enmascarar_licencia']-1);

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
//		$licencia_juez = substr_replace($licencia_juez, str_repeat('X',$_SESSION["enmascarar_licencia"]), sizeof($licencia_juez)-$_SESSION['enmascarar_licencia']-1);
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
$query = "SELECT resultados_figuras_categorias.id_categoria FROM resultados_figuras_categorias, fases, categorias WHERE categorias.id = fases.id_categoria and fases.id_categoria = resultados_figuras_categorias.id_categoria and fases.elementos_coach_card < 1 and fases.id_competicion ='".$_SESSION["id_competicion_activa"]."' GROUP BY resultados_figuras_categorias.id_categoria ORDER BY categorias.orden";
$categorias = mysqli_query($connection,$query);
while($categoria = mysqli_fetch_array($categorias)){
	if($categoria['id_categoria'] == '234' or $categoria['id_categoria'] == '235')
		$id_categoria = '241';
	else
		$id_categoria = $categoria['id_categoria'];
	$query = "select nombre, id from categorias where id = '".$id_categoria."'";
    $nombre_categoria=mysqli_result(mysqli_query($connection,$query),0);
	// add a page
	$pdf->AddPage();
	$pdf->SetFont('helvetica', '', 14);
	$html = "<h2> $nombre_categoria </h2>";
	$pdf->writeHTML($html, true, false, true, false, '');
	$pdf->SetFont('helvetica', '', 8);
// obtengo las figuras y el grado de dificultad
	$query = "select id_figura from fases where id_categoria='".$id_categoria."' and id_competicion='".$_SESSION['id_competicion_activa']."'";
	$figuras = mysqli_query($connection,$query);
	$figuras_texto = '';
	while($figuras2 = mysqli_fetch_array($figuras)){
		$query = "select numero, grado_dificultad from figuras where id ='".$figuras2['id_figura']."'";
		$figuras3 = mysqli_query($connection,$query);
		$figuras4 = mysqli_fetch_row($figuras3);
		$figuras_texto .= $figuras4[0].' GD:'.$figuras4[1].'<br>';
	}
	$html = "";


$html .= '<table style="margin-top=10px">';
	$html .= '<thead><tr style="background-color:'.$rutina_color_par.'"><th style="width:5%">Pos.</th><th style="width:34%">Nadadora</th><th style="width:11%">Figura</th><th style="width:18%">Jueces</th><th style="width:7%">Nota</th><th style="width:8%">Pen.</th><th style="width:8%">Total</th><th style="width:7%">Dif</th><th style="width:7%">Puntos</th></tr></thead>';
	$par=0;
	$query = "select * from resultados_figuras_categorias where id_categoria='".$id_categoria."' and id_competicion = '".$_SESSION["id_competicion_activa"]."' order by posicion asc, nota_final_calculada desc";
	$resultados_figuras_categorias = mysqli_query($connection,$query);
	if(mysqli_num_rows($resultados_figuras_categorias)>16)
		$pdf->SetFont('helvetica', '', 7);
	else
		$pdf->SetFont('helvetica', '', 8);

	while($resultado_figuras = mysqli_fetch_array($resultados_figuras_categorias)){
		$baja=$resultado_figuras['baja'];
		$par++;
		if($par%2==0)
			$rutina_color = $rutina_color_par;
		else
			$rutina_color = $rutina_color_impar;
		$query = mysqli_query($connection,"select apellidos, nombre, licencia, club from nadadoras where id='".$resultado_figuras['id_nadadora']."'");
		$nombre_nadadora = mysqli_result($query, 0).", ".mysqli_result($query,0,1);
		$licencia_nadadora = mysqli_result($query, 0,2);
//		$licencia_nadadora = substr_replace($licencia_nadadora, str_repeat('X',$_SESSION["enmascarar_licencia"]), sizeof($licencia_nadadora)-$_SESSION['enmascarar_licencia']-1);
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
                    $celda_puntuaciones_jueces .= '<span style="text-decoration:line-through">'.substr($puntuacion_juez['nota'],0,3)."</span> ";
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
// -----------------------------------------------------------------------------


//RESULTADO PARA RUTINAS TECNICAS DE FIGURAS
/*************************/
$query = "select resultados_figuras_categorias.id_categoria, fases.id as id_fase from resultados_figuras_categorias, fases WHERE fases.id_categoria = resultados_figuras_categorias.id_categoria and fases.elementos_coach_card > 0 and fases.id_competicion ='".$_SESSION["id_competicion_activa"]."' group by resultados_figuras_categorias.id_categoria";
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
	$query = "select id_categoria from resultados_figuras_categorias where id_categoria='".$categoria['id_categoria']."' and id_competicion = '".$_SESSION["id_competicion_activa"]."' group by id_categoria order by id_categoria asc";
	$id_categorias = mysqli_query($connection,$query);
	while($id_categoria = mysqli_fetch_array($id_categorias)){


$html .= '<table style="margin-top=10px">';
	$html .= '<thead><tr style="background-color:'.$rutina_color_par.'"><th style="width:5%">Pos.</th><th style="width:31%">Nadadora</th><th style="width:14%">T-TRE</th><th style="width:18%">Jueces</th><th style="width:7%">Nota</th><th style="width:8%">Pen.</th><th style="width:8%">Total</th><th style="width:7%">Dif</th><th style="width:7%">Puntos</th></tr></thead>';
	$par=0;
	$query = "select * from resultados_figuras_categorias where id_categoria='".$categoria['id_categoria']."' and id_competicion = '".$_SESSION["id_competicion_activa"]."' order by posicion asc, nota_final_calculada desc";
	$resultados_figuras_categorias = mysqli_query($connection,$query);
	if(mysqli_num_rows($resultados_figuras_categorias)>16)
		$pdf->SetFont('helvetica', '', 7);
	else
		$pdf->SetFont('helvetica', '', 8);

	while($resultado_figuras = mysqli_fetch_array($resultados_figuras_categorias)){
		$baja=$resultado_figuras['baja'];
		$par++;
		if($par%2==0)
			$rutina_color = '#FCE4EC';
		else
			$rutina_color = '#FCE4EC';
		$query = mysqli_query($connection,"select apellidos, nombre, licencia, club, id from nadadoras where id='".$resultado_figuras['id_nadadora']."'");
		$nombre_nadadora = mysqli_result($query, 0).", ".mysqli_result($query,0,1);
		$licencia_nadadora = mysqli_result($query, 0,2);
//		$licencia_nadadora = substr_replace($licencia_nadadora, str_repeat('X',$_SESSION["enmascarar_licencia"]), sizeof($licencia_nadadora)-$_SESSION['enmascarar_licencia']-1);
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
                    $celda_puntuaciones_jueces .= '<span style="text-decoration:line-through">'.substr($puntuacion_juez['nota'],0,3)." </span>";
                else
                    $celda_puntuaciones_jueces .= substr($puntuacion_juez['nota'],0,3).' ';
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
