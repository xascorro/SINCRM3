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
$titulo = $_GET['titulo'];
$titulo_documento = $_SESSION['nombre_competicion_activa']."<br>$titulo";
$nombre_documento = $titulo.' '.$_SESSION['nombre_competicion_activa'].'.pdf';
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
// add a page
$pdf->AddPage();
//$html ="<table  nobr=true style='border-right:0.1px'>";
//$html = '<table border="1" align="center" bordercolor="blue" cellspacing="0">';
$html = '<table border="1" cellspacing="0" paddig="1">';
$query = "SELECT categorias.nombre as categoria, modalidades.nombre as modalidad FROM fases, categorias, modalidades WHERE fases.id=".$_GET['id_fase']." and categorias.id = fases.id_categoria and modalidades.id = fases.id_modalidad";
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
//	$html .= "<thead>";
	$html .= "<tr>";
	$html .= '<td colspan="2" width="20%">Club</td>';
	$html .= '<td colspan="6" width="80%">'.$row['nombre_club'].'</td>';
	$html .= "</tr>";
//	//nombre participantes
	$html .= '<tr>';
	$html .= '<td colspan="2" width="20%">Competición</td>';
	$html .= '<td colspan="6" width="80%">'.$_SESSION['nombre_competicion_activa'].'</td>';
	$html .= "</tr>";
//
//	//nombre fase
//	$html .= "<tr>";
//	$html .= "<td colspan='2'>Evento</td>";
//	$html .= "<td colspan='6'>".$row['nombre_modalidad']." ".$row['nombre_categoria']."</td>";
//	$html .= "</tr>";
//
//	//tema
//	$html .= "<tr>";
//	$html .= "<td colspan='2'>Tema</td>";
//	$html .= "<td colspan='6'>".$row['tematica']."</td>";
//	$html .= "</tr>";
//
//	//nombres participantes
//	$html .= "<tr>";
//	$html .= "<td colspan='2'>Participantes</td>";
//	$html .= "<td colspan='6'>".$nombres."</td>";
//	$html .= "</tr>";
//	$html .= "</head>";

//	//texto
//	$html .= "<tr>";
//	$html .= "<td colspan='8'>ELEMENTOS EN ORDER DE EJECUCIÓN</td>";
//	$html .= "</tr>";
//	$html .= "<tr>";
//	$html .= "<td>TIME</td><td>PART</td><td>EL</td><td>BASEMARK</td><td>DIFFICULTAD DECLARADA</td><td>BONUS</td><td>DD</td><td>TC</td>";
//	$html .= "</tr>";

//	$html .= "<h5>#".$id_rutina.$row['nombre_modalidad']." ".$row['nombre_categoria']."</h5>";
//	$html .= $row['nombre_club'].' (.....'.$nombres.')';




	}
	$html .= "</table>";

//$html .= "<table><tr><td>hola</td><td>adios</td></tr></table>";
    $pdf->writeHTML($html, true, false, false, false, '');

$pdf->Output($nombre_documento, 'I');






?>
