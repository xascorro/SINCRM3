<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
//    error_reporting(E_ALL);
setlocale(LC_ALL,'es_ES');
require_once('../tcpdf/tcpdf.php');
include('../database/dbconfig.php');
include('../lib/my_functions.php');
session_start();

if(!$_SESSION['username']){
	header('Location: ../login.php');
}
$query = "SELECT * FROM competiciones WHERE activo = 'si'";
if(isset($_GET['id_competicion']))
	$query = "SELECT * FROM competiciones WHERE id = ".$_GET['id_competicion'];
$result= mysqli_query($connection,$query);
$competicion = mysqli_fetch_assoc($result);
$_SESSION['id_competicion_activa'] = $competicion['id'];
$_SESSION['nombre_competicion_activa'] = $competicion['nombre'];
$_SESSION['color_competicion_activa'] = $competicion['color'];
//$GLOBALS["nombre_competicion_activa"] = $registro['nombre'];
$GLOBALS["lugar"] = $competicion['lugar'];
$GLOBALS["fecha"] = $competicion['fecha'];
$GLOBALS["organizador"] = $competicion['organizador'];;
$GLOBALS["header"] = $competicion['header_informe'];
$GLOBALS["footer"] = $competicion['footer_informe'];
$id_rutina=$_GET['id_rutina'];
$id_competicion=$_GET['id_competicion'];
////****************************//
$titulo = 'Coach Card Composer';
$titulo_documento = $_SESSION['nombre_competicion_activa']."<br>".$titulo;
$titulo_documento = $_GET['id_rutina'].' '.$titulo;
$nombre_documento = $_GET['id_rutina'].' Coach Card Composer.pdf';
$GLOBALS['footer_substring'] = "Sede: ".$GLOBALS['lugar']."\n <br> Fecha: ".dateAFecha($GLOBALS['fecha']);
$logo_header_width= 100;
$GLOBALS['header_image'] = '../'.$GLOBALS['header'];
$GLOBALS['footer_image'] = '../'.$GLOBALS['footer'];

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {

    //Page header
    public function Header() {
        // Logo
        $this->SetFont('helvetica', 8);
        $this->WriteHTML('<img style="border-bottom:1px #e92662;" src="'.$GLOBALS['header_image'].'">', true, false, true, false, '');
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
        $this->WriteHTML('<div style="text-align:right; font-size:large; font-weight:bold">'.$GLOBALS['footer_substring'].'</div>', true, false, true, false, '');
    }
}


// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
//define ('PDF_HEADER_LOGO', 'kki.jpg');
// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Pedro Díaz');
$pdf->SetTitle($titulo_documento);
//$pdf->SetSubject($GLOBALS["nombre_competicion_activa"]);
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
$rutina_color_par = "#FCE4EC";
$rutina_color_impar = "#F7F7F7";

if(isset($_GET['id_rutina'])){
	$query = "SELECT id FROM rutinas WHERE id=".$_GET['id_rutina']." ORDER by id_fase";
}elseif(isset($_GET['id_club'])){
	$query = "SELECT id FROM rutinas WHERE id_competicion=$id_competicion and id_club=".$_GET['id_club']." ORDER by id_fase";
}elseif(isset($_GET['id_competicion'])){
	$query = "SELECT id FROM rutinas WHERE id_competicion=$id_competicion ORDER by id_fase";
}
$query_rutinas = mysqli_query($connection,$query);
		while ($id_rutina = mysqli_fetch_assoc($query_rutinas)) {
			$id_rutina = $id_rutina['id'];


// add a page
$pdf->AddPage();
$html = '<table border="1" cellpadding="4" style="font-size:12">';
$query = "SELECT categorias.nombre as categoria, modalidades.nombre as modalidad FROM fases, categorias, modalidades, rutinas WHERE fases.id=rutinas.id_fase and rutinas.id='$id_rutina' and categorias.id = fases.id_categoria and modalidades.id = fases.id_modalidad";
        $nombres = mysqli_fetch_assoc(mysqli_query($connection,$query));
        $nombre_modalidad = ".".$nombres['modalidad'];
        $nombre_categoria = $nombres['categoria'];



$query = "SELECT rutinas.id, tematica, rutinas.id_fase, rutinas.id_club, clubes.nombre_corto as nombre_club, modalidades.nombre as nombre_modalidad, categorias.nombre as nombre_categoria, rutinas.id_fase, fases.elementos_coach_card FROM rutinas, fases, modalidades, categorias, clubes WHERE rutinas.id = '$id_rutina' and rutinas.id_fase = fases.id and fases.id_modalidad = modalidades.id and fases.id_categoria = categorias.id and rutinas.id_club = clubes.id and fases.id_competicion = ".$id_competicion." ORDER BY fases.orden, fases.id";
$nombres = "SELECT group_concat(nadadoras.nombre SEPARATOR ', ') FROM rutinas, rutinas_participantes, nadadoras WHERE nadadoras.id = rutinas_participantes.id_nadadora and rutinas.id = rutinas_participantes.id_rutina and rutinas_participantes.reserva = 'no' and id_rutina = $id_rutina";
$nombres = mysqli_result(mysqli_query($connection,$nombres));

$query_run = mysqli_query($connection,$query);

if(mysqli_num_rows($query_run) > 0){
	$row = mysqli_fetch_assoc($query_run);
	//nombre club
	$html .= "<tr>";
	$html .= '<td colspan="2" width="20%">Club</td>';
	$html .= '<td colspan="4" width="69%">'.$row['nombre_club'].'</td>';
	$html .= '<td align="right" colspan="2" width="11%">#'.$row['id'].'</td>';
	$html .= "</tr>";

	//	//nombre participantes
	$html .= '<tr>';
	$html .= '<td colspan="2" width="20%">Competición</td>';
	$html .= '<td colspan="6" width="80%">'.$_SESSION['nombre_competicion_activa'].'</td>';
	$html .= "</tr>";

	//	//nombre fase
	$html .= '<tr>';
	$html .= '<td colspan="2">Evento</td>';
	$html .= '<td colspan="6">'.$row['nombre_modalidad'].' '.$row['nombre_categoria'].'</td>';
	$html .= '<td align="right" colspan="2">'.$row['orden'].'</td>';

	$html .= '</tr>';

	//tema
	$html .= '<tr>';
	$html .= '<td colspan="2">Tema</td>';
	$html .= '<td colspan="6">'.$row['tematica'].'</td>';
	$html .= '</tr>';

	//nombres participantes
	$html .= '<tr>';
	$html .= '<td colspan="2">Participantes</td>';
	$html .= '<td colspan="6" style="font-size:8">'.$nombres.'</td>';
	$html .= '</tr>';
	$html .= '</table>';

	$html .= '<br>&nbsp;<br>';

	//	//texto
	$html .= '<table border="1" cellpadding="4" style="margin-top:3px, font-size:12">';
	$html .= '<tr>';
	$html .= '<td colspan="8" align="center">ELEMENTOS EN ORDEN DE EJECUCIÓN</td>';
	$html .= '</tr>';
	$html .= '</table>';

	$html .= '<br>&nbsp;<br>';

	//cabecera tabla
	$html .= '<table border="1" cellpadding="4" style="margin-top:3px, font-size:12">';
	$html .= '<tr align="center" style="font-size:8">';
	$html .= '<td width="10%">TIME</td><td width="10%">PART</td><td width="4%">EL</td><td width="9%">BM</td><td width="40%">DIFFICULTAD DECLARADA</td><td width="16%">BONUS</td><td width="5%">DD</td><td width="6%" style="background-color:#CECECE">TC</td>';
	$html .= '</tr>';

	//fila de declaración del hibrido
	$i = 1;
	for($row['elementos_coach_card'];$i<=$row['elementos_coach_card'];$i++){
		$query = "SELECT nombre, color, tipo_hibridos.id from hibridos_rutina, tipo_hibridos where hibridos_rutina.texto = tipo_hibridos.id and tipo='part' and texto = 3 and id_rutina=$id_rutina and elemento = $i";
		$query_elementos = mysqli_query($connection,$query);
		while ($elemento = mysqli_fetch_assoc($query_elementos)) {
//			$html .= '<tr align="center" style="font-size:10"><td colspan="7" style="background-color:'.$elemento['color'].'">';
			$html .= '<tr align="center" style="font-size:10"><td colspan="7">';
			$html .= $elemento['nombre'];
			$id_tipo_hibrido = $elemento['id'];
			$html .= '</td>';
			$html .= '<td style="background-color:#CECECE">';
			$html .= '</td>';
			$html .= '</tr>';
		}

		//tiempos
		$html .= '<tr style="font-size:7">';
		$query = "SELECT texto from hibridos_rutina where tipo='time_inicio' and id_rutina=$id_rutina and elemento = $i";
		$query_elementos = mysqli_query($connection,$query);
		$html .= "<td>";
		while ($elemento = mysqli_fetch_assoc($query_elementos)) {
			$html .= $elemento['texto']." - ";
		}
		$query = "SELECT texto from hibridos_rutina where tipo='time_fin' and id_rutina=$id_rutina and elemento = $i";

		$query_elementos = mysqli_query($connection,$query);
		while ($elemento = mysqli_fetch_assoc($query_elementos)) {
			$html .= $elemento['texto'];
		}
		$html .= "</td>";

		//tipo de hibrido
		$query = "SELECT nombre, color, tipo_hibridos.id from hibridos_rutina, tipo_hibridos where hibridos_rutina.texto = tipo_hibridos.id and tipo='part' and texto <> 3 and id_rutina=$id_rutina and elemento = $i";
		$query_elementos = mysqli_query($connection,$query);
		$id_tipo_hibrido = '';
		while ($elemento = mysqli_fetch_assoc($query_elementos)) {
			$html .= '<td align="center" style="background-color:'.$elemento['color'].'">';
			$html .= $elemento['nombre'].'</td>';
			$id_tipo_hibrido = $elemento['id'];
		}

		//numero elemento
		$html .= '<th align="center">'.$i.'</th>';

		//basemark
		$query = "SELECT texto, valor from hibridos_rutina where tipo='basemark' and id_rutina=$id_rutina and elemento = $i and valor>0";
		$query = "SELECT texto, valor from hibridos_rutina where tipo='basemark' and id_rutina=$id_rutina and elemento = $i";
		$query_elementos = mysqli_query($connection,$query);
		$html .= '<td align="center">';
		if(@$elemento['valor'] != '')
			$elemento['valor'] = "(".$elemento['valor'].") ";
		while ($elemento = mysqli_fetch_assoc($query_elementos)) {
			$nombre_basemark = $elemento['texto'];
			$html .= $elemento['texto'];
		}
		$html .=  "</td>";

		//dificultad declarada
		$query = "SELECT texto, valor from hibridos_rutina where tipo='dd' and id_rutina=$id_rutina and elemento = $i and valor>0";
		$query = "SELECT texto, valor from hibridos_rutina where tipo='dd' and id_rutina=$id_rutina and elemento = $i and texto <> ''";
		$query_elementos = mysqli_query($connection,$query);
		$html .= '<td style="font-size:8">';
		while ($elemento = mysqli_fetch_assoc($query_elementos)) {
			$html .= $elemento['texto'].'&nbsp;';

		}
		$html .=  "</td>";

		//bonus
		$query = "SELECT texto, valor from hibridos_rutina where tipo='bonus' and id_rutina=$id_rutina and elemento = $i and valor>0";
		$query_elementos = mysqli_query($connection,$query);
		$html .= "<td>";
		if(@$elemento['valor'] != '')
		$elemento['valor'] = "(".$elemento['valor'].") ";
		while ($elemento = mysqli_fetch_assoc($query_elementos)) {
		$html .= $elemento['texto'].'&nbsp;';
		}
		$html .=  "</td>";

		//dd total
		$query = "SELECT valor from hibridos_rutina where tipo='total' and id_rutina=$id_rutina and elemento = $i";
		$html .= '<td align="center">';
		$query_elementos = mysqli_query($connection,$query);
		while ($elemento = mysqli_fetch_assoc($query_elementos)) {
		$html .= $elemento['valor'];
		}
		$html .= "</td>";

		//gris
		$html .= '<td style="background-color:#CECECE"></td>';


$html .= "</tr>";


}






}
$html .= "</table>";

$pdf->writeHTML($html, true, false, false, false, '');
}
$pdf->Output($nombre_documento, 'I');






?>
