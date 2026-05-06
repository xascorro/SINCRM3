<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
setlocale(LC_ALL,'es_ES');
require_once('../tcpdf/tcpdf.php');
include('../database/dbconfig.php');
include('../lib/my_functions.php');

session_start();

// Verificación de conexión a BD
if (!$connection) {
    die("Error de conexión a la base de datos. Por favor, revisa la configuración.");
}

// Verificación de sesión unificada v3.0
if(!$_SESSION['email'] && !$_SESSION['username']){
    header('Location: login.php');
    exit();
}

// Helper para obtener un único valor de forma segura
function safe_mysqli_result($conn, $query, $col = 0) {
    $res = mysqli_query($conn, $query);
    if ($res && mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_array($res);
        return $row[$col];
    }
    return false;
}

// 1. Obtener datos de la competición
$id_competicion = isset($_GET['id_competicion']) ? intval($_GET['id_competicion']) : ($_SESSION['id_competicion_activa'] ?? 0);
$query = "SELECT * FROM competiciones WHERE id = '$id_competicion'";
$result = mysqli_query($connection, $query);
$comp = $result ? mysqli_fetch_assoc($result) : null;

if(!$comp) {
    // Si no hay id_competicion, intentamos la activa
    $query = "SELECT * FROM competiciones WHERE activo = 'si' LIMIT 1";
    $result = mysqli_query($connection, $query);
    $comp = $result ? mysqli_fetch_assoc($result) : null;
}

if(!$comp) {
    die("Competición no encontrada.");
}

$GLOBALS["comp_data"] = [
    "nombre" => $comp['nombre'],
    "lugar" => $comp['lugar'],
    "fecha" => dateAFecha($comp['fecha']),
    "organizador" => $comp['organizador'],
    "header" => "../" . $comp['header_informe'],
    "footer" => "../" . $comp['footer_informe']
];

$titulo = $_GET['titulo'] ?? 'Resultados de Figuras por Categorías';
$nombre_archivo = "Resultados_Categorias_" . str_replace(' ', '_', $comp['nombre']) . ".pdf";

// --- CONFIGURACIÓN DE COLORES ---
$primary_pink = [233, 38, 98]; // #e92662
$light_pink = [252, 228, 236];  // #FCE4EC
$slate_600 = [71, 85, 105];
$slate_800 = [30, 41, 59];

// --- CLASE PDF PERSONALIZADA v3.0 ---
class SINCRM_PDF extends TCPDF {
    public function Header() {
        $headerData = $GLOBALS["comp_data"];
        $primary_pink = [233, 38, 98];
        
        // Header Image
        if(file_exists($headerData['header'])) {
            $this->Image($headerData['header'], 10, 10, 190, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
        
        // Título del Informe
        $this->SetY(12);
        $this->SetFont('helvetica', 'B', 16);
        $this->SetTextColor($primary_pink[0], $primary_pink[1], $primary_pink[2]);
        $this->Cell(0, 10, mb_strtoupper($_GET['titulo'] ?? 'RESULTADOS POR CATEGORÍAS'), 0, 1, 'C');
        
        $this->SetFont('helvetica', 'B', 10);
        $this->SetTextColor(100, 116, 139); // Slate-500
        $this->Cell(0, 5, $headerData['nombre'], 0, 1, 'C', 0, '', 0, false, 'T', 'T');
        
        // Línea decorativa
        $this->SetDrawColor($primary_pink[0], $primary_pink[1], $primary_pink[2]);
        $this->SetLineWidth(0.5);
        $this->Line(15, 30, 195, 30);
    }

    public function Footer() {
        $headerData = $GLOBALS["comp_data"];
        
        // Footer Image
        if(file_exists($headerData['footer'])) {
            $this->SetY(-25);
            $this->Image($headerData['footer'], 15, $this->GetY(), 180, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }

        $this->SetFont('helvetica', 'B', 7);
        $this->SetTextColor(71, 85, 105);
        $this->setCellPaddings(0, 0, 0, 0);
        
        $txt_sede = "Sede: " . trim($headerData['lugar']);
        $txt_fecha = "Fecha: " . trim($headerData['fecha']);
        $txt_pag = "Página " . $this->PageNo() . " de " . $this->getAliasNbPages();

        $this->SetY(-22);
        $this->MultiCell(180, 4, $txt_sede, 0, 'R', false, 1, 15, '', true, 0, false, true, 4, 'M', true);
        $this->MultiCell(180, 4, $txt_fecha, 0, 'R', false, 1, 15, '', true, 0, false, true, 4, 'M', true);
        $this->MultiCell(180, 4, $txt_pag, 0, 'R', false, 1, 15, '', true, 0, false, true, 4, 'M', true);
    }
}

// Crear documento
$pdf = new SINCRM_PDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator('SINCRM v3.0');
$pdf->SetAuthor('Pedro Díaz');
$pdf->SetTitle($titulo);
$pdf->SetMargins(15, 35, 15);
$pdf->SetAutoPageBreak(TRUE, 30);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// HOJA TÉCNICA (Opcional)
if(isset($_GET['hoja_tecnica'])){
	$pdf->AddPage();
	$pdf->SetFont('helvetica', '', 10);
	
    if($comp['organizador_tipo'] == 'Federación') {
		$organizador = safe_mysqli_result($connection, "select nombre from federaciones where id='".$comp['organizador']."'");
    } else {
        $organizador = $comp['organizador'];
    }

    $query_cl = "select nombre, codigo from clubes where id in (select distinct club from nadadoras where id in (select distinct id_nadadora from inscripciones_figuras where id_competicion='".$comp['id']."'))";
	$res_cl = mysqli_query($connection,$query_cl);
	$clubes="";
    if($res_cl) {
	    while($cl = mysqli_fetch_array($res_cl)){ $clubes .= $cl['nombre']." (".$cl['codigo'].") - "; }
	    $clubes = rtrim($clubes, " - ");
    }

    $html_tec = '
    <style>
        .section-title { background-color: #e92662; color: white; font-weight: bold; font-size: 11pt; padding: 5px; }
        .data-label { color: #e92662; font-weight: bold; width: 25%; border-bottom: 1px solid #f1f5f9; }
        .data-value { width: 75%; border-bottom: 1px solid #f1f5f9; color: #334155; }
        .staff-header { background-color: #f8fafc; color: #e92662; font-weight: bold; border-bottom: 2px solid #e92662; }
    </style>

    <table cellpadding="5" width="100%">
        <tr><td class="section-title" colspan="2">DATOS DE LA COMPETICIÓN</td></tr>
        <tr><td class="data-label">Evento:</td><td class="data-value"><b>'.$comp["nombre"].'</b></td></tr>
        <tr><td class="data-label">Lugar / Sede:</td><td class="data-value">'.$comp["lugar"].' ('.$comp["piscina"].')</td></tr>
        <tr><td class="data-label">Fecha / Hora:</td><td class="data-value">'.dateAFecha($comp["fecha"]).' | '.$comp['hora_inicio'].' - '.$comp['hora_fin'].'</td></tr>
        <tr><td class="data-label">Organizador:</td><td class="data-value">'.$organizador.'</td></tr>
        <tr><td class="data-label">Clubes:</td><td class="data-value" style="font-size:8pt;">'.$clubes.'</td></tr>
    </table>

    <div style="height:15px;"></div>

    <table cellpadding="4" width="100%">
        <tr><td class="section-title" colspan="3">DIRECCIÓN DE LA COMPETICIÓN</td></tr>
        <tr class="staff-header">
            <td width="30%">Cargo / Puesto</td>
            <td width="50%">Nombre y Apellidos</td>
            <td width="20%" align="center">Fed.</td>
        </tr>';

	$query_staff = "select * from puesto_juez, puestos_juez where id_puestos_juez=puestos_juez.id and id_competicion='".$comp['id']."'";
	$res_staff = mysqli_query($connection,$query_staff);
    if($res_staff) {
        while($st = mysqli_fetch_array($res_staff)){
            $n = safe_mysqli_result($connection, "select nombre from jueces where id = '".$st['id_juez']."'");
            $ap = safe_mysqli_result($connection, "select apellidos from jueces where id = '".$st['id_juez']."'");
            $fed_id = safe_mysqli_result($connection, "select federacion from jueces where id = '".$st['id_juez']."'");
            $fed = safe_mysqli_result($connection, "select nombre_corto from federaciones where id = '".$fed_id."'");
            
            $html_tec .= '<tr>
                <td style="border-bottom:1px solid #f1f5f9; color:#64748b; font-weight:bold;">'.$st['nombre'].'</td>
                <td style="border-bottom:1px solid #f1f5f9;">'.$n.' '.$ap.'</td>
                <td style="border-bottom:1px solid #f1f5f9;" align="center">'.$fed.'</td>
            </tr>';
        }
    }
    
	$html_tec .= '</table>

    <div style="height:15px;"></div>

    <table cellpadding="4" width="100%">
        <tr><td class="section-title" colspan="3">PANELES DE JUECES</td></tr>
        <tr class="staff-header">
            <td width="20%">Juez</td>
            <td width="60%">Nombre y Apellidos</td>
            <td width="20%" align="center">Fed.</td>
        </tr>';

	$query_j = "select distinct id_juez from panel_jueces where id_competicion='".$comp['id']."' order by numero_juez";
	$res_j = mysqli_query($connection,$query_j);
    $count_j = 0;
    if($res_j) {
        while($j = mysqli_fetch_array($res_j)){
            $n_j = safe_mysqli_result($connection, "select nombre from jueces where id = '".$j['id_juez']."'") . ' ' . safe_mysqli_result($connection, "select apellidos from jueces where id = '".$j['id_juez']."'");
            $f_id = safe_mysqli_result($connection, "select federacion from jueces where id = '".$j['id_juez']."'");
            $f_n = safe_mysqli_result($connection, "select nombre_corto from federaciones where id = '".$f_id."'");
            
            if($n_j != 'MEDIA ') {
                $count_j++;
                $html_tec .= '<tr>
                    <td style="border-bottom:1px solid #f1f5f9; color:#64748b;">Juez '.$count_j.'</td>
                    <td style="border-bottom:1px solid #f1f5f9;">'.$n_j.'</td>
                    <td style="border-bottom:1px solid #f1f5f9;" align="center">'.$f_n.'</td>
                </tr>';
            }
        }
    }

	$html_tec .= '</table>
    <div style="height:30px;"></div>
    <table width="100%">
        <tr>
            <td width="70%"></td>
            <td width="30%" align="center" style="border-top:1px solid #e92662;">
                <br><b>Fdo. Juez Árbitro</b>
            </td>
        </tr>
    </table>';

	$pdf->writeHTML($html_tec, true, false, true, false, '');
}

// PROCESAMIENTO DE CATEGORÍAS (Unificado)
$query_cats = "SELECT rfc.id_categoria, c.nombre as cat_nombre, f.elementos_coach_card, f.id as id_fase 
               FROM resultados_figuras_categorias rfc 
               JOIN categorias c ON rfc.id_categoria = c.id 
               JOIN fases f ON f.id_categoria = c.id
               WHERE f.id_competicion = '".$comp["id"]."' 
               GROUP BY rfc.id_categoria 
               ORDER BY c.orden ASC";
$res_cats = mysqli_query($connection, $query_cats);

if($res_cats) {
    while($cat = mysqli_fetch_assoc($res_cats)){
        $id_categoria = $cat['id_categoria'];
        $nombre_categoria = $cat['cat_nombre'];
        $is_tre = ($cat['elementos_coach_card'] > 0);

        // --- DESACTIVAR JUNIOR/TRE PARA DEBUG ---
        if($is_tre) continue; 
        
        $pdf->AddPage();
        
        // Cabecera de Categoría v3
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetFillColor(248, 250, 252);
        $pdf->SetTextColor($primary_pink[0], $primary_pink[1], $primary_pink[2]);
        $pdf->Cell(0, 12, "  " . mb_strtoupper($nombre_categoria), 0, 1, 'L', true);
        $pdf->SetTextColor(30, 41, 59);

        // Obtener información de Figuras
        $figuras_info = [];
        $figuras_html_header = "";
        
        // Para Figuras tradicionales
        $query_figs = "select id_figura from fases where id_categoria='".$id_categoria."' and id_competicion='".$comp['id']."' order by orden asc";
        $res_figs = mysqli_query($connection, $query_figs);
        if($res_figs) {
            while($f = mysqli_fetch_assoc($res_figs)){
                $q_f = "select numero, grado_dificultad from figuras where id ='".$f['id_figura']."'";
                $f_data_res = mysqli_query($connection, $q_f);
                if($f_data_res && $f_data = mysqli_fetch_assoc($f_data_res)){
                    $figuras_info[] = $f_data;
                    $figuras_html_header .= '<b>' . $f_data['numero'] . '</b> (GD: ' . $f_data['grado_dificultad'] . ') | ';
                }
            }
        }
        $figuras_html_header = rtrim($figuras_html_header, " | ");

        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(71, 85, 105);
        $html_fig_header = '<div style="background-color:#f8fafc; padding:5px; border-left:3px solid #e92662; margin-bottom:10px;">';
        $html_fig_header .= '&nbsp; <b style="color:#e92662;">FIGURAS:</b> ' . $figuras_html_header;
        $html_fig_header .= '</div>';
        $pdf->writeHTML($html_fig_header, true, false, true, false, '');

        // Tabla de Resultados
        $html = '
        <table cellpadding="2" style="width:100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color:rgb(233, 38, 98); color:white; font-weight:bold;">
                    <th width="4%" align="center">Pos.</th>
                    <th width="26%">Nadadora / Club</th>
                    <th width="7%" align="center">Fig.</th>
                    <th width="23%">Puntuaciones Jueces</th>
                    <th width="8%" align="center">Nota</th>
                    <th width="6%" align="center">Pen.</th>
                    <th width="10%" align="center">Total</th>
                    <th width="6%" align="center">Dif</th>
                    <th width="10%" align="center">Puntos</th>
                </tr>
            </thead>
            <tbody>';

        $query_res = "select * from resultados_figuras_categorias where id_categoria='".$id_categoria."' and id_competicion = '".$comp["id"]."' order by posicion asc, nota_final_calculada desc";
        $res_res = mysqli_query($connection, $query_res);
        
        $row_count = 0;
        if($res_res) {
            while($resultado = mysqli_fetch_assoc($res_res)){
                $row_count++;
                $bg_color = ($row_count % 2 == 0) ? 'background-color:rgb(252, 228, 236);' : 'background-color:white;';
                
                $q_nad = "select apellidos, nombre, club from nadadoras where id='".$resultado['id_nadadora']."'";
                $nad_res = mysqli_query($connection, $q_nad);
                $nad = ($nad_res) ? mysqli_fetch_assoc($nad_res) : null;
                $club = $nad ? safe_mysqli_result($connection, "select nombre_corto from clubes where id = '".$nad['club']."'") : "N/A";
                
                $html .= '<tr nobr="true" style="' . $bg_color . '; border-bottom:0.5pt solid #FCE4EC;">';
                $html .= '<td width="4%" align="center" style="font-weight:bold; font-size:10pt;">' . ($resultado['posicion'] ?? '-') . '</td>';
                $html .= '<td width="26%"><b style="color:#1e293b; font-size:9pt;">' . mb_strtoupper(($nad['apellidos'] ?? '')) . ', ' . ($nad['nombre'] ?? '') . '</b><br><small style="color:#64748b; font-size:7pt;">' . $club . '</small></td>';
                
                // Columna de figuras
                $html .= '<td width="7%" align="center" style="font-size:8pt; color:#64748b;">';
                foreach($figuras_info as $fi) { 
                    $html .= ($fi['numero'] ?? '') . '<br>'; 
                }
                $html .= '</td>';

                // Detalle de puntuaciones
                $html .= '<td width="23%" style="font-size:8pt;">';
                $notas_finales_fase = "";
                $penalizaciones_fase = "";
                
                // Lógica para Figuras tradicionales
                $res_fases_cat = mysqli_query($connection,"select id from fases where id_categoria='".$id_categoria."' and id_competicion='".$comp['id']."' order by orden asc");
                if($res_fases_cat) {
                    while($fase_cat = mysqli_fetch_assoc($res_fases_cat)){
                        $q_p = "select * from puntuaciones_jueces where id_inscripcion_figuras in (select id from inscripciones_figuras where id_nadadora='".$resultado['id_nadadora']."' and id_fase='".$fase_cat['id']."') order by id_elemento, id_panel_juez";
                        $res_p = mysqli_query($connection, $q_p);
                        if($res_p) {
                            while($pj = mysqli_fetch_assoc($res_p)){
                                if($pj['nota_menor'] == 'si' || $pj['nota_mayor'] == 'si')
                                    $html .= '<span style="text-decoration:line-through; color:#64748b;">' . number_format($pj['nota'] ?? 0, 1) . "</span> ";
                                else
                                    $html .= '<b style="color:#1e293b;">' . number_format($pj['nota'] ?? 0, 1) . '</b> ';
                            }
                        }
                        $html .= "<br>";

                        $q_i = "select nota_final from inscripciones_figuras where id_nadadora='".$resultado['id_nadadora']."' and id_fase='".$fase_cat['id']."'";
                        $res_i = mysqli_query($connection, $q_i);
                        $nota_final_fase = 0;
                        if($res_i && mysqli_num_rows($res_i) > 0) {
                            $insc_data = mysqli_fetch_assoc($res_i);
                            $nota_final_fase = $insc_data['nota_final'] ?? 0;
                        }
                        $notas_finales_fase .= number_format($nota_final_fase, 4) . "<br>";

                        $q_pen = "select p.codigo from penalizaciones_rutinas pr JOIN penalizaciones p ON pr.id_penalizacion = p.id where pr.id_inscripcion_figuras in (select id from inscripciones_figuras where id_nadadora='".$resultado['id_nadadora']."' and id_fase='".$fase_cat['id']."')";
                        $res_pen = mysqli_query($connection, $q_pen);
                        $pens_this_fase = "";
                        if($res_pen) {
                            while($pen = mysqli_fetch_assoc($res_pen)){
                                $pens_this_fase .= $pen['codigo'] . " ";
                            }
                        }
                        $penalizaciones_fase .= ($pens_this_fase ?: "-") . "<br>";
                    }
                }
                
                $html .= '</td>';
                
                $html .= '<td width="8%" align="center" style="font-size:8pt; color:#64748b;">' . $notas_finales_fase . '</td>';
                $html .= '<td width="6%" align="center" style="font-size:7pt; color:#e11d48;">' . $penalizaciones_fase . '</td>';
                
                // Bloque de Resultado Final
                if(($resultado['baja'] ?? '') =='si'){
                    $html .= '<td colspan="3" width="26%" align="center" style="color:#ef4444; font-weight:bold; font-size:10pt;">BAJA</td>';
                } elseif(($resultado['preswimmer'] ?? '') =='si'){
                    $html .= '<td width="10%" align="center" style="font-weight:bold; color:#e92662; font-size:9.5pt;">' . number_format($resultado['nota_final_calculada'] ?? 0, 4) . '</td>';
                    $html .= '<td width="6%" align="center" style="font-size:7pt;">' . number_format($resultado['diferencia'] ?? 0, 4) . '</td>';
                    $html .= '<td width="10%" align="center" style="font-weight:bold; color:#64748b; font-size:9.5pt;">PRE</td>';
                } else {
                    $html .= '<td width="10%" align="center" style="font-weight:bold; color:#e92662; font-size:9.5pt;">' . number_format($resultado['nota_final_calculada'] ?? 0, 4) . '</td>';
                    $html .= '<td width="6%" align="center" style="font-size:7pt;">' . number_format($resultado['diferencia'] ?? 0, 4) . '</td>';
                    $html .= '<td width="10%" align="center" style="font-weight:bold; color:#1e293b; font-size:9.5pt;">' . (int)($resultado['puntos'] ?? 0) . '</td>';
                }
                $html .= '</tr>';
            }
        }
        $html .= '</tbody></table>';
        
        $pdf->SetFont('helvetica', '', 8);
        $pdf->writeHTML($html, true, false, false, false, '');
        
        // Línea de cierre de tabla
        $pdf->SetDrawColor(233, 38, 98);
        $pdf->SetY($pdf->GetY() - 0.5);
        $pdf->Cell(180, 0, '', 'T', 1);
    }
}

// Salida
$pdf->Output($nombre_archivo, 'I');
?>
