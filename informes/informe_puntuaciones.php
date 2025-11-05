<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
// Include the main TCPDF liary (search for installation path).
require_once('../tcpdf/tcpdf.php');
include('../security.php');
include('../lib/my_functions.php');


mysqli_query($connection,"SET NAMES 'utf8'");

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
	    $GLOBALS["header"] = $registro['header_informe'];
	    $GLOBALS["footer"] = $registro['footer_informe'];
    }
//****************************//
$titulo = $_GET['titulo'];
$titulo_documento = $GLOBALS['nombre_competicion_activa']."<br>$titulo";
$nombre_documento = $titulo.' '.$GLOBALS['nombre_competicion_activa'];
$GLOBALS['footer_substring'] = "Sede: ".$GLOBALS['lugar']."\n <br> Fecha: ".$GLOBALS['fecha'];
$logo_header_width= 100;
$GLOBALS['header_image'] = '../'.$GLOBALS['header'];
$GLOBALS['footer_image'] = '../'.$GLOBALS['footer'];

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {

    //Page header
    public function Header() {
        // Logo
        $this->SetFont('helvetica', 12);
        $this->WriteHTML('<img style="border-bottom:1px #e92662;" src="'.$GLOBALS['header_image'].'">', true, false, true, false, '');
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
		$pagenumtxt = $this->getAliasNumPage().' de '.$this->getAliasNbPages();
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
	$html_tec = '<table style="margin-top=10px">';
	$html_tec .= "<tr><th colspan=4></th></tr>";

	$html_tec .= '<tr><th style="background-color:#cecece" width="32%">Competición: </th><th colspan="3">'.$GLOBALS["nombre_competicion_activa"].'</th></tr>';
	$html_tec .= '<tr><th style="background-color:#cecece">Fecha: </th><th colspan="3">'.$GLOBALS["fecha"].'</th></tr>';
	$html_tec .= '<tr><th style="background-color:#cecece">Lugar celebración: </th><th colspan="3">'.$GLOBALS["lugar"].'</th></tr>';
	$html_tec .= '<tr><th style="background-color:#cecece">Piscina: </th><th colspan="3">'.$GLOBALS["piscina"].'</th></tr>';
	if($GLOBALS['organizador_tipo'] == 'Federación')
		$organizador = mysqli_result(mysqli_query($connection,"select nombre from federaciones where id='".$GLOBALS['organizador']."'"),0) ;
	if($GLOBALS['organizador_tipo'] == 'Club')
		$organizador = mysqli_result(mysqli_query($connection,"select nombre from clubes where id='".$GLOBALS['organizador']."'"),0) ;
	$html_tec .= '<tr><th style="background-color:#cecece">Organizador: </th><th colspan="3">'.$organizador.'</th></tr>';
	$query = "select nombre from clubes where id in (select id_club from rutinas where id_competicion='".$GLOBALS['id_competicion_activa']."')";
	$clubs_participantes = mysqli_query($connection,$query);
	$clubs="";
	while($club = mysqli_fetch_array($clubs_participantes)){
		$clubs .= $club['nombre']." - ";
	}
	$clubs ="".
	$html_tec .= '<tr><th style="background-color:#cecece">Clubs participanes: </th><th colspan="3">'.$clubs.'</th></tr>';
	//obtengo datos de puesto jueces
	$html_tec .= '<tr><th colspan="4"></th></tr>';
	$query = "SELECT puesto_juez.id, id_juez, id_puestos_juez, nombre FROM puesto_juez, puestos_juez WHERE id_puestos_juez = puestos_juez.id AND id_competicion='".$GLOBALS['id_competicion_activa']."'";
	$puesto_jueces = mysqli_query($connection,$query);
	while($puesto_juez = mysqli_fetch_array($puesto_jueces)){
		$nombre = mysqli_result(mysqli_query($connection,"select nombre from jueces where id = '".$puesto_juez['id_juez']."'"), 0);
		$apellidos = mysqli_result(mysqli_query($connection,"select apellidos from jueces where id = '".$puesto_juez['id_juez']."'"), 0);
		$licencia = mysqli_result(mysqli_query($connection,"select licencia from jueces where id = '".$puesto_juez['id_juez']."'"), 0);
        $licencia = '';
		$id_federacion = mysqli_result(mysqli_query($connection,"select federacion from jueces where id = '".$puesto_juez['id_juez']."'"), 0);
		$federacion = mysqli_query($connection,"select nombre_corto from federaciones where id = '".$id_federacion."'");
		$federacion = mysqli_result($federacion,0);
		// $html_tec .= '<tr style="background-color:#cecece"><th colspan="4">'.$puesto_juez['nombre'].'</th></tr>';
		// $html_tec .= '<tr><th>'.$licencia.'</th><th colspan="2">'.$nombre.' '.$apellidos.'</th><th>'.$federacion.'</th></tr>';
        $puesto_nombre;
		$html_tec .= '<tr><th width="32%" style="background-color:#cecece">'.$puesto_juez['nombre'].':</th>';
		$html_tec .= '<th width="59%">'.$nombre.' '.$apellidos.'</th><th width="11%">'.$federacion.'</th></tr>';

	}
		$html_tec .= '<tr style="background-color:#cecece"><th>Jueces de puntuación: </th></tr>';

	//obtengo datos jueces puntuacion
	$query = "select * from jueces where id != '108' and id in (select id_juez from panel_jueces where id_competicion='".$GLOBALS['id_competicion_activa']."')";
	$query = "select distinct jueces.* from jueces, panel_jueces where jueces.id != '108' and jueces.id in (select id_juez from panel_jueces where id_competicion=".$GLOBALS['id_competicion_activa'].") and panel_jueces.`id_juez` = jueces.id and panel_jueces.id_competicion=".$GLOBALS['id_competicion_activa']." ";
	$jueces = mysqli_query($connection,$query);
	while($juez = mysqli_fetch_array($jueces)){
		$nombre_panel = mysqli_query($connection,"select nombre from paneles where id_competicion='".$GLOBALS['id_competicion_activa']."' and id in (select id_panel from panel_jueces where id_competicion='".$GLOBALS['id_competicion_activa']."' and id_juez = '".$juez['id']."' )");
		$nombre_panel = mysqli_result($nombre_panel, 0);
		$numero_juez = mysqli_query($connection,"select numero_juez from panel_jueces where id_competicion='".$GLOBALS['id_competicion_activa']."' and id_juez = '".$juez['id']."' ");
		$numero_juez = mysqli_result($numero_juez, 0);
		$federacion = mysqli_query($connection,"select nombre_corto from federaciones where id = '".$juez['federacion']."'");
		$federacion = mysqli_result($federacion,0);
		//$html_tec .= '<tr><th width="30%">'.$nombre_panel.' '.$numero_juez.'</th><th width="19%">'.$juez['lic	cia'].'</th><th>'.$juez['nombre'].' '.$juez['apellidos'].'</th><th>'.$federacion.'</th></tr>';
		// $html_tec .= '<tr><th width="19%">'.$juez['licencia'].'</th><th width="62%">'.$juez['nombre'].' '.$juez['apellidos'].'</th><th>'.$federacion.'</th></tr>';
		$html_tec .= '<tr><th width="32%">'.'</th><th width="59%">'.$juez['nombre'].' '.$juez['apellidos'].'</th><th width="11%">'.$federacion.'</th></tr>';

	}


	$html_tec .= '<tr><th></th><th colspan="2"><br>Fdo. Juez Árbitro<br></th></tr>';
	$html_tec .= "</table>";
	$pdf->writeHTML($html_tec, true, false, true, false, '');
}
//

if(isset($_GET['memorial'])){

//array con puntos por posicion
$puntos = array("0", "19", "16", "14", "13","12", "11", "10", "9", "8","7", "6", "5", "4", "3","2", "1", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0",);
	// add a page
	$pdf->AddPage('L');
	$pdf->SetFont('helvetica', '', 14);
	// $html = "<h2> Memorial Alfonso J. Aznar  </h2>";
	$html = "<h2> Clasificación general </h2>";
	$pdf->writeHTML('<br>'.$html, true, false, true, false, '');
	$pdf->SetFont('helvetica', '', 12);

//    $query = "select distinct count(*) as repe, fases.*, modalidades.numero_participantes, modalidades.nombre as nombre_modalidad, modalidades.id as id_modalidad, categorias.nombre as nombre_categoria from fases, modalidades, categorias where fases.id_competicion = '".$GLOBALS['id_competicion_activa']."' and modalidades.id_competicion = '".$GLOBALS['id_competicion_activa']."' and categorias.id_competicion = '".$GLOBALS['id_competicion_activa']."' and modalidades.id = fases.id_modalidad and categorias.id = fases.id_categoria group by id_modalidad order by numero_participantes, id_modalidad, id_categoria";
    $query = "SELECT DISTINCT fases.*, modalidades.numero_participantes, modalidades.nombre as nombre_modalidad, modalidades.nombre_corto as nombre_corto, modalidades.id as id_modalidad, categorias.nombre as nombre_categoria
	FROM fases, modalidades, categorias, rutinas
	WHERE puntua_memorial = 'si' and rutinas.`id_competicion` = '".$GLOBALS['id_competicion_activa']."' and fases.id_competicion = '".$GLOBALS['id_competicion_activa']."' and modalidades.id = fases.id_modalidad and categorias.id = fases.id_categoria
	GROUP BY fases.id_modalidad
	ORDER BY numero_participantes, fases.id_modalidad, id_categoria";
$id_mpodalidad='';
    $fases = mysqli_query($connection,$query);
    $html = '<table align="center" border="1" cellpadding="2" align="center">';
	$html.= '<tr><th rowspan="2" width="8%">CLUB</th>';
    while($fase = mysqli_fetch_array($fases)){
		$id_mpodalidad = $fase['id_modalidad'];
		$query = "select id from fases where id_competicion = '".$GLOBALS['id_competicion_activa']."' and id_modalidad = ".$fase['id_modalidad'];
		$colspan = mysqli_num_rows(mysqli_query($connection,$query));
//        $colspan = $fase['repe'];
        if ($fase['numero_participantes'] <= 2)
            $colspan = $colspan*2;
//        $html.= '<th colspan="'.$colspan.'">'.$fase['nombre_modalidad'].'</th>';
        $html.= '<th colspan="'.$colspan.'">'.$fase['nombre_corto'].'</th>';
    }

    $html.= '</tr><tr>';
    $query = "SELECT DISTINCT fases.*, modalidades.numero_participantes, modalidades.nombre as nombre_modalidad, categorias.nombre_corto FROM fases, modalidades, categorias
	WHERE puntua_memorial = 'si' and fases.id_competicion = '".$GLOBALS['id_competicion_activa']."' and modalidades.id = fases.id_modalidad and categorias.id = fases.id_categoria
	ORDER BY numero_participantes, id_modalidad, id_categoria";
	$fases = mysqli_query($connection,$query);
//	echo '<br>'.$query.'<br>';
    while($fase = mysqli_fetch_array($fases)){
        $colspan = 1;
        if ($fase['numero_participantes'] <= 2)
            $colspan = 2;
        $html.= '<th colspan="'.$colspan.'">'.$fase['nombre_corto'].'</th>';
    }
//    $html .= '</tr></table>';
    $html .= '</tr>';

$query = "select nombre, nombre_corto, id from clubes where id in (select id_club from rutinas where id_competicion='".$GLOBALS['id_competicion_activa']."')";
$clasificacion_clubs[] = array();
$i = 0;
$par = 1;
	$error_color = "#E65B5E";
$rutina_color_par = "#FCE4EC";
$rutina_color_impar = "#F7F7F7";
$clubs = mysqli_query($connection,$query);
while($club = mysqli_fetch_array($clubs)){
	$par++;
	if($par%2==0)
		$rutina_color = $rutina_color_par;
	else
		$rutina_color = $rutina_color_impar;
    $query = "select fases.*, modalidades.numero_participantes, modalidades.nombre as nombre_modalidad, modalidades.id as modalidad_id, categorias.nombre as nombre_categoria from fases, modalidades, categorias WHERE puntua_memorial = 'si' and fases.id_competicion = '".$GLOBALS['id_competicion_activa']."' and modalidades.id = fases.id_modalidad and categorias.id = fases.id_categoria order by numero_participantes, id_modalidad, id_categoria";
	$fases = mysqli_query($connection,$query);
	$html .= '<tr style="background-color:'.$rutina_color.';"><td style=" font-size: 10px;">'.$club['nombre_corto'].'</td>';

	$puntos_club = 0;
	while($fase = mysqli_fetch_array($fases)){
		$id_fase = $fase['id'];
//		$query = "select nombre, id from categorias where id = '".$fase['id_categoria']."'";
//	    $nombre_categoria=mysql_result(mysqli_query($connection,$query),0);
//	    $id_categoria=mysql_result(mysqli_query($connection,$query),0,1);
//		$query = "select nombre, id from modalidades where id = '".$fase['id_modalidad']."'";
//	    $nombre_modalidad=mysql_result(mysqli_query($connection,$query),0);
//	    $id_categoria=mysql_result(mysqli_query($connection,$query),0,1);
//	    $query= "select * from rutinas where id_fase=$id_fase and id_club='".$club['id']."' and orden > 0 and baja <> 'si' order by posicion asc limit 2";
		if($fase['modalidad_id'] == 3 or $fase['modalidad_id'] == 4 or $fase['modalidad_id'] == 7 or $fase['modalidad_id'] == 9 or $fase['modalidad_id'] == 10  )
        	$query= "select * from rutinas where id_fase=$id_fase and id_club='".$club['id']."' and orden > 0 and baja !='si' order by posicion asc limit 1";
        else
			$query= "select * from rutinas where id_fase=$id_fase and id_club='".$club['id']."' and orden > 0 and baja !='si' order by posicion asc limit 2";
	    $rutinas = mysqli_query($connection,$query);
	    while($rutina = mysqli_fetch_array($rutinas)){
	    	if($rutina['posicion'] > '0' and $rutina['posicion'] <= '16'){
		    	$puntos_rutina = $puntos[$rutina['posicion']];
	    	}else{
	    		$puntos_rutina = "0";
	    	}

	    	if($fase['numero_participantes'] > 2)
	    		$puntos_rutina = $puntos_rutina*2;
		    $html .= '<td style="font-size: 20px;">'.$puntos_rutina."</td>";
		    $puntos_club = $puntos_club+$puntos_rutina;
	    }
	    if(mysqli_num_rows($rutinas) == 1 and $fase['numero_participantes'] <= 2)
	    	$html .= "<td>-</td>";
        else if(mysqli_num_rows($rutinas) == 0 and $fase['numero_participantes'] <= 2)
	    	$html .= "<td>-</td><td>-</td>";
        else if(mysqli_num_rows($rutinas) == 0 and $fase['numero_participantes'] > 2)
	    	$html .= "<td>-</td>";
	}
	  $clasificacion_clubs[$i]['id'] = $club['id'];
	  $clasificacion_clubs[$i]['puntos'] = $puntos_club;


//	  $html .= "<td>$puntos_club</td>";
	  $html .= "</tr>";
	  $i++;
}

$html .= '</table><br><br>';
$pdf->writeHTML($html, true, false, false, false, '');
//	  print_r($clasificacion_clubs);
$pdf->AddPage('V');
	$pdf->SetFont('helvetica', '', 14);
	// $html = "<h2> Memorial Alfonso J. Aznar  </h2>";
	$html = "<h2> Clasificación general </h2>";
	$pdf->writeHTML($html, true, false, true, false, '');
	$pdf->SetFont('helvetica', '', 7);
usort($clasificacion_clubs,"cmpPuntosDesc");
$html = "";
$html_podium = '<table><tr><td></td><td></td><td></td></tr><tr><td></td><td></td><td></td></tr>';
$i = 1;
foreach ($clasificacion_clubs as &$club) {
    $query = "select nombre from clubes where id = '".$club['id']."'";
    $nombre_club = mysqli_result(mysqli_query($connection,$query),0);
    $query = "select logo from clubes where id = '".$club['id']."'";
    $logo_club = mysqli_result(mysqli_query($connection,$query),0);
    if($logo_club == ""){
	    $logo_club = "";
    }else{
	    $logo_club = '<img style="border-bottom:1px #cecece; width: 100px;" src="../'.$logo_club.'">';
	    $logo_club = '<img style="border-bottom:1px #cecece; heigth: 100px;" src="../'.$logo_club.'">';
    }
    $html .= "<h2>".$i."º con ".$club['puntos']." puntos $nombre_club</h2>";

    //creo tabla podium
    if($i == 1){
    	$html_podium .= '<tr><td></td><td><h1>1º '.$nombre_club.'<br>'.$logo_club.'</h1></td><td></td></tr>';
    }else if($i == 2){
    	$html_podium_2 = '<td><h1>2º '.$nombre_club.'<br><img style="border-bottom:1px #cecece;" src="'.$logo_club.'"></h1></td>';
    }else if($i == 3){
    	$html_podium .= '<tr>'.$html_podium_2.'<td></td><td><h1>3º '.$nombre_club.'<br><img style="border-bottom:1px #cecece;" src="'.$logo_club.'"></h1></td></tr>';
    }

    $i++;
}
$html_podium .= '</table>';

$pdf->writeHTML($html, true, false, false, false, '');
$pdf->writeHTML($html_podium, true, false, false, false, '');
}

//Función para ordenar descendentemente
function cmpPuntosDesc($jugador1, $jugador2)
{
     //Si son iguales se devuelve 0
     if($jugador1["puntos"]==$jugador2["puntos"])
          return 0;
     //Si jugador1 > 2 se devuelve 1 y por lo contrario -1
     if($jugador1["puntos"]<$jugador2["puntos"])
          return 1;
     return -1;
}



$condicion_ampliada = "";
if(isset($_GET['id_fase'])){
	$condicion_ampliada = "and id = '".$_GET['id_fase']."'";
	$id_fase = $_GET['id_fase'];
}



$query = "select * from fases where id_competicion = '".$GLOBALS["id_competicion_activa"]."' $condicion_ampliada order by orden";
$fases = mysqli_query($connection,$query);
while($fase = mysqli_fetch_array($fases)){
	$id_fase = $fase['id'];
	$query = "select nombre, id from categorias where id = '".$fase['id_categoria']."'";
    $nombre_categoria=mysqli_result(mysqli_query($connection,$query),0);
    $id_categoria=mysqli_result(mysqli_query($connection,$query),0,1);
	$query = "select nombre, id from modalidades where id = '".$fase['id_modalidad']."'";
    $nombre_modalidad=mysqli_result(mysqli_query($connection,$query),0);
//    $id_categoria=mysqli_result(mysqli_query($connection,$query),0,1);
    $id_modalidad=mysqli_result(mysqli_query($connection,$query),1);
	//FACTORIZACIÓN
	$query = "SELECT fases.id, fases.f_chomu, f_performance, f_transitions, f_hybrid, f_acro, f_tre FROM fases WHERE id='$id_fase'";
	$f_fase = mysqli_query($connection, $query);
	$f_fase = mysqli_fetch_assoc($f_fase);
	$id_fase = $fase['id'];
	$f_chomu = $fase['f_chomu'];
	$f_performance = $fase['f_performance'];
	$f_transitions = $fase['f_transitions'];
	$f_hybrid = $fase['f_hybrid'];
	$f_acro = $fase['f_acro'];
	$f_tre = $fase['f_tre'];
	//FIN FACTORIZACIÓN


// add a page
$pdf->AddPage();
	$pdf->SetFont('helvetica', '', 14);
	$html = "<h2> $nombre_modalidad $nombre_categoria </h2>";
	$pdf->writeHTML($html, true, false, true, false, '');
	$pdf->SetFont('helvetica', '', 8);


	//composición juez arbitro / secretario / ayudantes
	$html = '<table border="1" cellpadding="1" align="center"><tr>';
	$query = "select UPPER(CONCAT(j.nombre, ' ', j.apellidos)) AS nombre_completo,
 UPPER(puestos_juez.nombre) as puesto from puesto_juez, jueces j, puestos_juez where puesto_juez.id_competicion=".$GLOBALS['id_competicion_activa']." and id_puestos_juez=puestos_juez.id and id_juez=j.id and puestos_juez.id<=4";
	$puestos = mysqli_query($connection,$query);
	while($puesto = mysqli_fetch_array($puestos)){
		$html .= '<td  style="background-color:#FCE4EC; text-align:center;  font-size:6px">'.$puesto['puesto'].'</td><td  style="font-size:6px">'.$puesto['nombre_completo'].'</td>';
	}
	$html .= '</tr></table>';
//	composición DTCs
	$html .= '<table border="1" cellpadding="1" align="center"><tr>';
	$query = "select UPPER(CONCAT(j.nombre, ' ', j.apellidos)) AS nombre_completo,
 UPPER(puestos_juez.nombre) as puesto from puesto_juez, jueces j, puestos_juez where puesto_juez.id_competicion=".$GLOBALS['id_competicion_activa']." and id_puestos_juez=puestos_juez.id and id_juez=j.id and (puestos_juez.id=10 or puestos_juez.id=11) ";
	$puestos = mysqli_query($connection,$query);
	while($puesto = mysqli_fetch_array($puestos)){
		$html .= '<td style="width:5%; background-color:#FCE4EC; text-align:center; font-size:6px">'.$puesto['puesto'].'</td><td style="width:20%; font-size:6px" >'.$puesto['nombre_completo'].'</td>';
	}
	$html .= '</tr></table>';
//	composicion STC
		$html .= '<table border="1" cellpadding="1" align="center">';

$query = "SELECT UPPER(CONCAT(j.nombre, ' ', j.apellidos)) AS nombre_completo,
    UPPER(paneles.nombre) AS panel
FROM paneles
JOIN panel_jueces ON paneles.id = panel_jueces.id_panel
JOIN jueces j ON j.id = panel_jueces.id_juez
WHERE panel_jueces.id_fase = ".$fase['id']."
ORDER BY paneles.id, panel_jueces.numero_juez";

$puestos = mysqli_query($connection, $query);

$panel_anterior = null; // Inicializamos la variable para comparar el panel

while ($puesto = mysqli_fetch_array($puestos)) {
    if ($puesto['panel'] != $panel_anterior) {
        // Cerramos la fila anterior si no es la primera
        if ($panel_anterior !== null) {
            $html .= '</tr>';
        }
        // Creamos la fila para el nombre del panel
        $html .= '<tr><td style="width:100%; background-color:#FCE4EC; text-align:center; font-size:6px">' .$id_modalidad. $puesto['panel'] . '</td></tr><tr>';
        $panel_anterior = $puesto['panel']; // Actualizamos el panel anterior
    }
    // Añadimos la celda con el nombre del juez
    $html .= '<td style="width:20%; font-size:6px;">' . $puesto['nombre_completo'] . '</td>';
}

// Cerramos la última fila de datos si hubo jueces
if ($panel_anterior !== null) {
    $html .= '</tr>';
}

$html .= '</table>';


	$html .= '<table style="margin-top=10px">';
	//segun titulo de documento
	if($titulo =='Clasificación detallada'){
		$html .= '<thead><tr nobr="true" style="background-color:'.$rutina_color_par.'"><th style="width:5%"><b>Pos.</b></th><th style="width:30%"><b>Club</b></th><th style="width:20%"><b>Panel</b></th><th style="width:20%"><b>Notas</b></th><th align="right" style="width:8%"><b>Puntos</b></th><th align="right" style="width:10%"><b>Total</b></th><th align="right" style="width:7%"><b>Dif</b></th></tr></thead>';
	}
	//fin segun titulo documento

	$par=0;
	$query = "select * from rutinas where id_fase='".$fase['id']."' order by posicion asc";
	$rutinas = mysqli_query($connection,$query);
	if(mysqli_num_rows($rutinas)>16)
		$pdf->SetFont('helvetica', '', 7);
	else
		$pdf->SetFont('helvetica', '', 7);

	while($rutina = mysqli_fetch_array($rutinas)){
		$baja=$rutina['baja'];
		$par++;
		if($par%2==0)
			$rutina_color = $rutina_color_par;
		else
			$rutina_color = $rutina_color_impar;
			$query = "select nombre_corto from clubes where id = '".$rutina['id_club']."'";
			$nombre_rutina=mysqli_result(mysqli_query($connection,$query),0);
			$nombre_rutina = $nombre_rutina.' '.$rutina['nombre'];

		//segun titulo de documento
		if($titulo =='Clasificación detallada'){
			if( $rutina['orden']=='-1')
				$rutina['posicion'] = "PS";
			elseif( $rutina['orden']=='-2')
				$rutina['posicion'] = "EX";
            elseif( $rutina['baja']=='si')
				$rutina['posicion'] = "-";
			$html .= '<table nobr="true background-color:'.$rutina_color.'">';
			$html .='<tr nobr="true" style="background-color:'.$rutina_color.'"><td nobr=true style="width:5%; font-size:14; font-weight:bold;">'.$rutina['posicion'].'</td>';
		}
		$html.= '<td style="width:30%"><b>'.$nombre_rutina.'</b><br>';
		//leer participantes
		$query = "select * from rutinas_participantes where id_rutina='".$rutina['id']."'";
		$participantes = mysqli_query($connection,$query);
		while($participante = mysqli_fetch_array($participantes)){
            $nota_elementos = 0;
            $nota_ia = 0;
			$query = "select apellidos, nombre, año_nacimiento from nadadoras where id = '".$participante['id_nadadora']."'";
			$apellidos=@mysqli_result(mysqli_query($connection,$query),0);
			$nombre=@mysqli_result(mysqli_query($connection,$query),0,1);
			$año_nacimiento=substr(@mysqli_result(mysqli_query($connection,$query),0,2),0,4);
			$reserva='';
			if($participante['reserva'] == 'si')
				$reserva .= '(RESERVA)';
			$html .= $apellidos.', '.$nombre.' '.$reserva.'('.$año_nacimiento.')<br>';

		}
		$html.= '</td>';
	if($baja != 'si'){
		//leo paneles
		$html.='<td nobr=true style="width:20%">';
		$query = "select * from paneles where id_competicion='".$GLOBALS["id_competicion_activa"]."' and puntua='si' and tecnico = '".$fase['tecnico']."' and obsoleto = '".$fase['obsoleto']."'";
		$paneles = mysqli_query($connection,$query);
		while($panel = mysqli_fetch_array($paneles)){
			if(($fase['id_modalidad'] != 1 or $fase['id_modalidad'] != 5) && $panel['nombre'] != 'Sincronización'){
				$html .='<b>'.$panel['nombre'].'</b><br>';
			}
            if($panel['nombre'] == 'Elementos'){
                for ($x=1;$x<=$fase['elementos_coach_card'];$x++){
                    $query = "SELECT nombre, texto FROM hibridos_rutina, tipo_hibridos WHERE tipo like 'part' and texto=tipo_hibridos.id and nombre not like 'TRANSITION' and id_rutina = ".$rutina['id']." and elemento=$x";
                    $tipo_elemento = mysqli_result(mysqli_query($connection,$query),0);
                    $query = "SELECT llevado_BM FROM puntuaciones_elementos WHERE id_rutina = ".$rutina['id']." and elemento=$x";
                    $llevado_BM = mysqli_result(mysqli_query($connection,$query),0);

                    if($llevado_BM == 'si'){
                        $query = "SELECT valor FROM hibridos_rutina WHERE tipo like 'basemark' and id_rutina = ".$rutina['id']." and elemento=$x";
                        $dd = mysqli_result(mysqli_query($connection,$query),0);     $dd = ' BM:'.$dd;
                    }else{
                        $query = "SELECT valor FROM hibridos_rutina WHERE tipo like 'total' and id_rutina = ".$rutina['id']." and elemento=$x";
                        $dd = ' DD:'.mysqli_result(mysqli_query($connection,$query),0);
                    }
                    if($tipo_elemento == 'TRE'){
                        $query = "select texto from hibridos_rutina where texto like '%-TRE%' and id_rutina = ".$rutina['id']." and elemento=$x";
                        $nombre = mysqli_result(mysqli_query($connection,$query),0);
                        $nombre = substr($nombre,2,5);
                        $fm=$f_tre;
                    }elseif($tipo_elemento == 'HYBRID'){
                        $fm=$f_hybrid;
                    }elseif($tipo_elemento == 'ACROBATIC'){
                        $fm=$f_acro;
                        $tipo_elemento = 'ACRO';
                    }

                        $nombre = $tipo_elemento.$dd.' F:'.$fm;


                    $html .= "$nombre<br>";
                }
				if($fase['id_modalidad'] != 1 && $fase['id_modalidad'] != 5){
					$html .= "<b>Sincronización</b><br>";
				}
                $html .= "<b>Pen. Elementos</b><br>";
                $html .= "<br>";
            }elseif($panel['nombre']=='Artístico' and $panel['obsoleto'] == 'no' ){
                $html .= "ChoMu F:$f_chomu<br>Performance F:$f_performance<br>Transitions F:$f_transitions<br><b>Pen. Artístico</b>";
            }
		}
					$html.= "<br><br><b>Penalización Rutina</b>";

		$html.='</td>';
//leo notas puntuaciones_jueces
//		if($baja != 'si' & $rutina['orden'] != '-1'){

//SISTEMA DE PUNTUACIÓN 2022-2026
            if($fase['obsoleto']=='no'){
		      $html.='<td nobr=true style="width:20%">&nbsp;<br>';
                $query = "SELECT hibridos_rutina.id, id_rutina , elemento, tipo, texto, nombre, color FROM hibridos_rutina, tipo_hibridos WHERE id_rutina = ".$rutina['id']." and tipo like 'part' and texto not like '3' and texto=tipo_hibridos.id order by elemento";

            $query_run = mysqli_query($connection,$query);
            while ($row = mysqli_fetch_assoc($query_run)) {

                      $query_jueces = "SELECT * from panel_jueces WHERE id_fase=".$fase['id']." and id_panel in (SELECT id from paneles where id_paneles_tipo = 1 and id_competicion=".$_SESSION['id_competicion_activa'].") order by numero_juez";
                    $query_run_jueces = mysqli_query($connection,$query_jueces);
                    while ($row_jueces = mysqli_fetch_assoc($query_run_jueces)) {

                        $query = "SELECT nota, id, nota_menor, nota_mayor FROM puntuaciones_jueces WHERE id_rutina='".$rutina['id']."' and id_panel_juez=".$row_jueces['id']." and id_elemento=".$row['elemento'];
                        //echo '<br>'.$query;
                        $nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['nota'];
                        $id = mysqli_fetch_assoc(mysqli_query($connection,$query))['id'];
                        $nota_menor = mysqli_fetch_assoc(mysqli_query($connection,$query))['nota_menor'];
                        $nota_mayor = mysqli_fetch_assoc(mysqli_query($connection,$query))['nota_mayor'];
                        if($nota_menor == 'si' or $nota_mayor == 'si')
                            $nota = '<del>'.$nota.'</del>';
                        $html.=  $nota.' ';
                    }
                $html .='<br>';
            }
			if($fase['id_modalidad'] != 1 && $fase['id_modalidad'] != 5)
				$html .= '<br>';
				$html .= '<br><b>Total Elementos</b><br><br>';
//notas chomu
             $query_jueces = "SELECT * from panel_jueces WHERE id_fase=$id_fase and id_panel in (SELECT id from paneles where id_paneles_tipo = 2 and id_competicion=".$_SESSION['id_competicion_activa'].") order by numero_juez";
                    $query_run_jueces = mysqli_query($connection,$query_jueces);
                    while ($row_jueces = mysqli_fetch_assoc($query_run_jueces)) {
                        $query = "SELECT nota, id, nota_menor, nota_mayor FROM puntuaciones_jueces WHERE id_rutina='".$rutina['id']."' and id_panel_juez=".$row_jueces['id']." and tipo_ia='ChoMu'";
                        $nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['nota'];
                        $id = mysqli_fetch_assoc(mysqli_query($connection,$query))['id'];
                        $nota_menor = mysqli_fetch_assoc(mysqli_query($connection,$query))['nota_menor'];
                        $nota_mayor = mysqli_fetch_assoc(mysqli_query($connection,$query))['nota_mayor'];
                        $style='';
                        if($nota_menor == 'si' or $nota_mayor == 'si')
                            $nota = '<del>'.$nota.'</del>';
                        $html.=  $nota.' ';
                    }
            //notas performance
             $html .='<br>';
             $query_jueces = "SELECT * from panel_jueces WHERE id_fase=$id_fase and id_panel in (SELECT id from paneles where id_paneles_tipo = 2 and id_competicion=".$_SESSION['id_competicion_activa'].") order by numero_juez";
                    $query_run_jueces = mysqli_query($connection,$query_jueces);
                    while ($row_jueces = mysqli_fetch_assoc($query_run_jueces)) {
                        $query = "SELECT nota, id, nota_menor, nota_mayor FROM puntuaciones_jueces WHERE id_rutina='".$rutina['id']."' and id_panel_juez=".$row_jueces['id']." and tipo_ia='Performance'";
                        $nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['nota'];
                        $id = mysqli_fetch_assoc(mysqli_query($connection,$query))['id'];
                        $nota_menor = mysqli_fetch_assoc(mysqli_query($connection,$query))['nota_menor'];
                        $nota_mayor = mysqli_fetch_assoc(mysqli_query($connection,$query))['nota_mayor'];
                        $style='';
                        if($nota_menor == 'si' or $nota_mayor == 'si')
                            $nota = '<del>'.$nota.'</del>';
                        $html.=  $nota.' ';
                    }

//notas Transitions
             $html .='<br>';
             $query_jueces = "SELECT * from panel_jueces WHERE id_fase=$id_fase and id_panel in (SELECT id from paneles where id_paneles_tipo = 2 and id_competicion=".$_SESSION['id_competicion_activa'].") order by numero_juez";
                    $query_run_jueces = mysqli_query($connection,$query_jueces);
                    while ($row_jueces = mysqli_fetch_assoc($query_run_jueces)) {
                        $query = "SELECT nota, id, nota_menor, nota_mayor FROM puntuaciones_jueces WHERE id_rutina='".$rutina['id']."' and id_panel_juez=".$row_jueces['id']." and tipo_ia='Transitions'";
                        $nota = mysqli_fetch_assoc(mysqli_query($connection,$query))['nota'];
                        $id = mysqli_fetch_assoc(mysqli_query($connection,$query))['id'];
                        $nota_menor = mysqli_fetch_assoc(mysqli_query($connection,$query))['nota_menor'];
                        $nota_mayor = mysqli_fetch_assoc(mysqli_query($connection,$query))['nota_mayor'];
                        $style='';
                        if($nota_menor == 'si' or $nota_mayor == 'si')
                            $nota = '<del>'.$nota.'</del>';
                        $html.=  $nota.' ';
                    }
            $html .= '<br><br><b>Total Imp. Artística</b>';
                $html.='</td>';
		//leo notas puntuaciones_paneles
		$html.='<td align="right" style="width:8%">&nbsp;<br>';
//		if($baja != 'si' & $rutina['orden'] != '-1'){
		if($baja != 'si'){
            $query = "SELECT elemento FROM hibridos_rutina, tipo_hibridos WHERE id_rutina = '".$rutina['id']."' and tipo like 'part' and texto not like '3' and texto=tipo_hibridos.id order by elemento";

            $query_run = mysqli_query($connection,$query);
			 while ($row = mysqli_fetch_assoc($query_run)) {
                $query = "SELECT DISTINCT nota_media, nota FROM puntuaciones_elementos WHERE elemento= ".$row['elemento']." and id_rutina=".$rutina['id'];
                $nota = mysqli_fetch_assoc(mysqli_query($connection,$query));
                $html .= $nota['nota'].'<br>';
                $nota_elementos = $nota_elementos + $nota['nota'];
            }

            //notas sincro, no las muestro si la modalidad es solo libre o solo tecnico
				if($fase['id_modalidad'] != 1 && $fase['id_modalidad'] != 5){

                    $query = "SELECT nota from errores_sincronizacion WHERE id_rutina=".$rutina['id'];
                    $errores_sincronizacion = mysqli_fetch_assoc(mysqli_query($connection,$query));
                    $html .= $rutina['nota_panel_sincro'].'<br>';
				}
                    $html .= $rutina['penalizaciones_elementos'].'<br>';
                    $html .= '<b>'.$rutina['nota_final_panel_elementos'].'</b><br><br>';
            //notas chomu
                    $query = "SELECT DISTINCT nota FROM puntuaciones_elementos WHERE tipo_ia like 'ChoMu' and id_rutina=".$rutina['id'];
                    $nota = mysqli_fetch_assoc(mysqli_query($connection,$query));
                    $html .= $nota['nota'].'<br>';
                    $nota_ia = $nota_ia + $nota['nota'];
            //notas Performance
                    $query = "SELECT DISTINCT nota FROM puntuaciones_elementos WHERE tipo_ia like 'Performance' and id_rutina=".$rutina['id'];
                    $nota = mysqli_fetch_assoc(mysqli_query($connection,$query));
                    $html .= $nota['nota'].'<br>';
                    $nota_ia = $nota_ia + $nota['nota'];
            //notas Transitions
                    $query = "SELECT DISTINCT nota FROM puntuaciones_elementos WHERE tipo_ia like 'Transitions' and id_rutina=".$rutina['id'];
                    $nota = mysqli_fetch_assoc(mysqli_query($connection,$query));
                    $html .= $nota['nota'].'<br>';
                    $nota_ia = $nota_ia + $nota['nota'];
            //nota total impresion artistica
                    $html .= $rutina['penalizaciones_ia'].'<br>';
                    $html .= '<b>'.$rutina['nota_final_panel_ia'].'</b><br>';
			//nota final rutina
                    $html .= '<b>'.$rutina['penalizaciones_rutina'].'</b><br>';

		}
		$html.='</td>';
            }
            //SISTEMA DE PUNTUACIÓN 2017-2021
            elseif($fase['obsoleto']=='si'){
			$html.='<td nobr=true style="width:20%">';
			$query = "select * from paneles where id_competicion='".$GLOBALS["id_competicion_activa"]."' and puntua='si' and tecnico = '".$fase['tecnico']."' and obsoleto='si'";
			$paneles = mysqli_query($connection,$query);
			while($panel = mysqli_fetch_array($paneles)){
				$query = "select puntuaciones_jueces.id, id_panel_juez, id_rutina, nota, nota_menor, nota_mayor, numero_juez from puntuaciones_jueces, panel_jueces where id_panel_juez in (select id from panel_jueces where id_fase='$id_fase' and id_panel='".$panel['id']."') and id_rutina = '".$rutina['id']."' and panel_jueces.id = id_panel_juez order by id_panel_juez";
				$puntuaciones_jueces = mysqli_query($connection,$query);
				while($puntuacion_juez = mysqli_fetch_array($puntuaciones_jueces)){
					if($puntuacion_juez['nota_menor']=='si' || $puntuacion_juez['nota_mayor']=='si')
						$html .= '<span style="text-decoration: line-through;">'.$puntuacion_juez['nota'].'</span>'.' ';
					else
						$html .= '<span>'.$puntuacion_juez['nota'].'</span>'.' ';

				}
				$html .= "<br>";
			}
                $html.='</td>';
		//leo notas puntuaciones_paneles
		$html.='<td nobr=true style="width:8%">';
//		if($baja != 'si' & $rutina['orden'] != '-1'){
		if($baja != 'si'){
			$query = "select id from paneles where id_competicion='".$GLOBALS["id_competicion_activa"]."' and puntua='si' and tecnico = '".$fase['tecnico']."' and obsoleto='".$fase['obsoleto']."'";
			$paneles = mysqli_query($connection,$query);
			while($panel = mysqli_fetch_array($paneles)){
				$query = "select * from puntuaciones_paneles where id_rutina='".$rutina['id']."' and id_panel='".$panel['id']."'";
				$puntuaciones_paneles = mysqli_query($connection,$query);
				while($puntuaciones_panel = mysqli_fetch_array($puntuaciones_paneles)){
					$html .=$puntuaciones_panel['nota_calculada'].' ';
				}
				$html .= '<br>';
			}
		}
		$html.='</td>';


		}

		}


		//leo notas puntuacion en rutina
		if($baja == 'si'){
			$rutina['nota_final'] = 'BAJA';
			$rutina['diferencia'] = '';
		}

		$html.='<td align="right" style="width:10%"><b>'.$rutina['nota_final'].'</b></td>';
		//leo notas diferencia en rutina
		$html.='<td align="right" style="width:7%">'.$rutina['diferencia'].'</td>';
		$html .='</tr>';
        if($fase['tecnico'] == 'si'){
            $img_tecnicas = './img_tecnicas/r'.$rutina['id'].'.png';
            if (file_exists($img_tecnicas)) {
                $html .= '<tr><td></td><td></td><td></td><td>'.'<img style="border:10px #cecece;" src="'.$img_tecnicas.'">'.'</td></tr><tr><td></td></tr><tr><td></td></tr>';
            }
        }

		//leo notas penalizaciones_rutinas
		$penalizaciones_texto = '';
		if($baja != 'si' || $rutina['orden'] > 1){
			$query = "select group_CONCAT(codigo separator ', ') as codigos from penalizaciones_rutinas, penalizaciones where id_penalizacion = penalizaciones.id and id_rutina='".$rutina['id']."'";
			$penalizaciones = mysqli_query($connection,$query);
			while($penalizacion = mysqli_fetch_array($penalizaciones)){
				$penalizaciones_texto .= $penalizacion['codigos'].'  ';
			}
		}
		if($penalizaciones_texto != ' ')
			$html .= '<tr nobr="true" style="background-color:'.$rutina_color.'"><td></td><td></td><td colspan="5">'.$penalizaciones_texto.'</td></tr>';

		$html .= '<tr nobr="true" style="background-color:'.$rutina_color.'"><td colspan="7"></td></tr>';

					$html .= "</table>";

	}

	$html .= '</table>';
	$pdf->writeHTML($html, true, false, false, false, '');
}
// -----------------------------------------------------------------------------

//Close and output PDF document
$pdf->Output($nombre_documento.'.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+
