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
	    $GLOBALS["lugar"] = $registro['lugar'];
	    $GLOBALS["fecha"] = dateAFecha($registro['fecha']);
	    $GLOBALS["organizador"] = $registro['organizador'];
        $GLOBALS["header_image"] = "../".$registro['header_informe'];
	    $GLOBALS["footer_image"] = "../".$registro['footer_informe'];
	    $GLOBALS["enmascarar_licencia"] = $registro['enmascarar_licencia'];
}
//****************************//
$titulo = $_GET['titulo'];
$titulo_documento = $GLOBALS['nombre_competicion_activa']."<br>$titulo";
$nombre_documento = $titulo.' '.$GLOBALS['nombre_competicion_activa'];
$GLOBALS['footer_substring'] = "Sede: ".$GLOBALS['lugar']."<br> Fecha: ".$GLOBALS['fecha'];
$logo_header_width= 100;
$last_page=1;


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

// add a page
$pdf->AddPage();

$error_color = "#E65B5E";
$rutina_color_par = "#FCE4EC";
$rutina_color_impar = "#FFFFFF";


$query = "SELECT * FROM fases WHERE id_competicion = '".$GLOBALS["id_competicion_activa"]."' and elementos_coach_card > 0";
$fases = mysqli_query($connection,$query);
while($fase = mysqli_fetch_array($fases)){
	$query = "select nombre, id from categorias where id = '".$fase['id_categoria']."'";
    $nombre_categoria=mysqli_result(mysqli_query($connection,$query),0);
    $id_categoria=mysqli_result(mysqli_query($connection,$query),0,1);
	$pdf->SetFont('helvetica', '', 13);
        //segun titulo de documento
            $query = "SELECT numero, nombre, grado_dificultad, corte FROM figuras, fases WHERE fases.id_figura = figuras.id and id_categoria = ".$fase['id_categoria']." and id_competicion = ".$GLOBALS["id_competicion_activa"]." and fases.id = ".$fase['id'];
            $numero_fases = mysqli_query($connection,$query)->num_rows;
            $query_run = mysqli_query($connection,$query);
            while($figura = mysqli_fetch_assoc($query_run)){
                $figuras .= 'Figura '.$figura['numero'].' - '.$figura['nombre'].'</h2>';

            }
            $html = '<h2>'.$nombre_categoria.' - '.$figuras;
            $html .= '<h2>Nº juez:___ Nombre y firma:</h2>';

            $html .= '<table nobr="true" style="margin-top=10px" border="1px">';
            $html .='<tr><td style="text-align:center">ORDEN</td>';

            $i = 0;
            while($i < $fase['elementos_coach_card']){
                $i++;
                $html .= '<td style="text-align:center">ELEMENTO'.$i.'</td>';
            }
            $html .= '</tr>';

            $figuras = '';

        $par=1;
        $query = "select id_nadadora, apellidos, nadadoras.nombre, año_nacimiento, licencia, nombre_corto, inscripciones_figuras.id from inscripciones_figuras, nadadoras, clubes where id_fase = '".$fase['id']."' and id_nadadora = nadadoras.id and clubes.id = club order by orden";
        $rutinas = mysqli_query($connection,$query);
        while($rutina = mysqli_fetch_array($rutinas)){
            $numero_rutinas++;
            $par++;
            if($par%2==0)
                $rutina_color = $rutina_color_par;
            else
                $rutina_color = $rutina_color_impar;

            $numero = $numero_rutinas.' - ';
            $html .='<tr nobr="true" style="background-color:'.$rutina_color.'">';

            $query = "SELECT inscripciones_figuras.orden, corte FROM inscripciones_figuras, fases WHERE id_nadadora =".$rutina['id_nadadora']." and id_fase = fases.id and fases.id_competicion=".$GLOBALS['id_competicion_activa']." limit 1";
            $orden_nadadora=mysqli_result(mysqli_query($connection,$query),0);
            $html .='<th style="text-align:center; background-color:'.$rutina_color.'" rowspan="2" ><h1>'.$orden_nadadora.'</h1></th>';
            $query = "SELECT texto, valor FROM hibridos_rutina WHERE valor > 0 and tipo like 'dd' and id_rutina = ".$rutina['id'];
            $query_run = mysqli_query($connection,$query);
            while($tre = mysqli_fetch_assoc($query_run)){
            $html .= '<th style="background-color:'.$rutina_color.'; text-align:center"><h6>';
                $html .= $tre['texto'];
                $html .= '</h6></th>';

            }
            $html .= "</tr><tr>";
            $j=0;
            while($j <= $i){
                $j++;
                $html .= '<td style="background-color:#eeeeee"><br><br></td>';
            }
            $html .= "</tr>";




        }
        $html .= '</table>';
        $pdf->writeHTML($html, true, false, false, false, '');
        $pdf->AddPage();
        $pdf->setPage($pdf->getPage());
        $last_page++;
        }
// Delete last page
$pdf->deletePage($last_page);
// -----------------------------------------------------------------------------

//Close and output PDF document
$pdf->Output($nombre_documento.'.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+
?>
