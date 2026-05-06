<?php
ini_set('display_errors', 0);
setlocale(LC_ALL,'es_ES');
require_once('../tcpdf/tcpdf.php');
include('../database/dbconfig.php');
include('../lib/my_functions.php');

session_start();

if(!$_SESSION['email']){
    header('Location: login.php');
    exit();
}

// 1. Obtener datos de la competición
$id_competicion = isset($_GET['id_competicion']) ? intval($_GET['id_competicion']) : $_SESSION['id_competicion_activa'];
$query = "SELECT * FROM competiciones WHERE id = '$id_competicion'";
$result = mysqli_query($connection, $query);
$comp = mysqli_fetch_assoc($result);

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

$titulo = $_GET['titulo'] ?? 'Inscripciones de Figuras';
$nombre_archivo = "Inscripciones_" . str_replace(' ', '_', $comp['nombre']) . ".pdf";

// --- CONFIGURACIÓN DE COLORES Y COLUMNAS ---
$show_orden = true; // Estética unificada (siempre mostramos la estructura completa)
$col_name_width = 110;
$primary_pink = [233, 38, 98]; // #e92662
$light_pink = [252, 228, 236];  // #FCE4EC

// Determinamos la ordenación basada en el título
$is_inscripciones = (stripos($titulo, 'Inscrip') !== false);
$order_clause = ($is_inscripciones) ? "cl.nombre_corto ASC, n.apellidos ASC" : "i.orden ASC";

// --- CLASE PDF PERSONALIZADA v3.0 ---
class SINCRM_PDF extends TCPDF {
    public function Header() {
        $headerData = $GLOBALS["comp_data"];
        $primary_pink = [233, 38, 98];
        
        // Header Image
        $this->Image($headerData['header'], 10, 10, 190, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        
        // Título del Informe (Ubicado en el espacio blanco entre logos)
        $this->SetY(12);
        $this->SetFont('helvetica', 'B', 16);
        $this->SetTextColor($primary_pink[0], $primary_pink[1], $primary_pink[2]);
        $this->Cell(0, 10, mb_strtoupper($_GET['titulo'] ?? 'INSCRIPCIONES'), 0, 1, 'C');
        
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
        
        // Footer Image (Logos de clubes)
        $this->SetY(-25);
        $this->Image($headerData['footer'], 15, $this->GetY(), 180, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);

        // Información en 3 filas con control absoluto de márgenes
        $this->SetFont('helvetica', 'B', 7);
        $this->SetTextColor(71, 85, 105);
        
        // Eliminamos CUALQUIER padding de celda a nivel global de página
        $this->setCellPaddings(0, 0, 0, 0);
        
        $txt_sede = "Sede: " . trim($headerData['lugar']);
        $txt_fecha = "Fecha: " . trim($headerData['fecha']);
        $txt_pag = "Página " . $this->PageNo() . " de " . $this->getAliasNbPages();

        // Imprimir cada línea forzando el ancho al máximo del margen (195 - 15 = 180)
        // Usamos un pequeño truco: MultiCell con ancho 180 empezando en 15
        $this->SetY(-22);
        $this->MultiCell(180, 4, $txt_sede, 0, 'R', false, 1, 15, '', true, 0, false, true, 4, 'M', true);
        $this->MultiCell(180, 4, $txt_fecha, 0, 'R', false, 1, 15, '', true, 0, false, true, 4, 'M', true);
        $this->MultiCell(180, 4, $txt_pag, 0, 'R', false, 1, 15, '', true, 0, false, true, 4, 'M', true);
    }
}

// Crear documento
$pdf = new SINCRM_PDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator('SINCRM v3.0');
$pdf->SetAuthor('SINCRM System');
$pdf->SetTitle($titulo);
$pdf->SetMargins(15, 35, 15);
$pdf->SetAutoPageBreak(TRUE, 30);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// --- PROCESAMIENTO DE DATOS ---
$query_fases = "SELECT f.id as id_fase, f.id_categoria, c.nombre as cat_nombre 
                FROM fases f 
                JOIN categorias c ON f.id_categoria = c.id 
                WHERE f.id_competicion = '$id_competicion' 
                AND f.orden = (
                    SELECT MIN(f2.orden) 
                    FROM fases f2 
                    WHERE f2.id_categoria = f.id_categoria 
                    AND f2.id_competicion = '$id_competicion'
                )
                GROUP BY f.id_categoria 
                ORDER BY c.edad_minima ASC";
$res_fases = mysqli_query($connection, $query_fases);

while($fase = mysqli_fetch_assoc($res_fases)) {
    $pdf->AddPage();
    $id_cat = $fase['id_categoria'];
    $id_fase = $fase['id_fase'];

    // Cabecera de Categoría
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetFillColor(248, 250, 252); // Surface
    $pdf->SetTextColor($primary_pink[0], $primary_pink[1], $primary_pink[2]);
    $pdf->Cell(0, 12, "  " . mb_strtoupper($fase['cat_nombre']), 0, 1, 'L', true);
    
    // Obtener figuras de la categoría
    $q_figs = "SELECT fi.numero, fi.nombre, fi.grado_dificultad, fa.corte, fa.orden as fase_orden 
               FROM figuras fi 
               JOIN fases fa ON fa.id_figura = fi.id 
               WHERE fa.id_categoria = '$id_cat' AND fa.id_competicion = '$id_competicion' 
               ORDER BY fa.orden ASC";
    $res_figs = mysqli_query($connection, $q_figs);
    
    // Cabecera de Figuras estructurada en 2 columnas (Lectura más fácil)
    $pdf->SetY($pdf->GetY() + 1);
    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetTextColor(71, 85, 105); // Slate-600
    
    $html_figs = '<table cellpadding="3" style="width:100%;">';
    $res_figs_data = [];
    while($fig = mysqli_fetch_assoc($res_figs)) {
        $res_figs_data[] = $fig;
    }
    
    // Dividir en 2 columnas para maximizar legibilidad
    $chunks = array_chunk($res_figs_data, 2);
    foreach($chunks as $chunk) {
        $html_figs .= '<tr>';
        foreach($chunk as $fig) {
            $corte_label = ($fig['corte'] > 0) ? ' <b style="color:#e92662;">[CORTE '.$fig['corte'].']</b>' : '';
            $html_figs .= '<td width="50%" style="border-bottom:0.1pt solid #f1f5f9;">';
            $html_figs .= '<b style="font-size:10pt;">' . $fig['fase_orden'] . '.</b> <b style="font-size:10pt;">' . $fig['numero'] . '</b> ' . $fig['nombre'] . ' <b style="font-size:8pt;">(GD: ' . $fig['grado_dificultad'] . ')</b>' . $corte_label;
            $html_figs .= '</td>';
        }
        // Rellenar celda vacía si el último chunk solo tiene 1 elemento
        if(count($chunk) == 1) {
            $html_figs .= '<td width="50%" style="border-bottom:0.1pt solid #f1f5f9;"></td>';
        }
        $html_figs .= '</tr>';
    }
    $html_figs .= '</table>';
    
    $pdf->writeHTML($html_figs, true, false, true, false, '');
    $pdf->Ln(1);

    // Re-crear array de cortes para el listado de nadadoras
    $cortes_arr = [];
    foreach($res_figs_data as $fig) {
        if($fig['corte'] > 0) $cortes_arr[$fig['corte']] = $fig['corte'];
    }

    // Tabla de Nadadoras (Iniciamos con HTML para aprovechar la repetición de cabeceras de TCPDF)
    $header_html = '
    <table cellpadding="4" style="width:100%; font-size:12pt;">
        <thead>
            <tr style="background-color:rgb(233, 38, 98); color:white; font-weight:bold;">
                <th width="8%" align="center" style="border:1px solid #e92662;">ORD</th>
                <th width="57%" align="left" style="border:1px solid #e92662;">APELLIDOS Y NOMBRE</th>
                <th width="10%" align="center" style="border:1px solid #e92662;">AÑO</th>
                <th width="25%" align="center" style="border:1px solid #e92662;">CLUB</th>
            </tr>
        </thead>
        <tbody>';

    $pdf->SetFont('helvetica', '', 12);
    
    $q_insc = "SELECT i.*, n.nombre, n.apellidos, n.año_nacimiento, cl.nombre_corto 
               FROM inscripciones_figuras i 
               JOIN nadadoras n ON i.id_nadadora = n.id 
               JOIN clubes cl ON n.club = cl.id 
               WHERE i.id_fase = '$id_fase' 
               ORDER BY $order_clause";
    $res_insc = mysqli_query($connection, $q_insc);
    
    $body_html = "";
    $fill = false;
    $count = 0;
    while($row = mysqli_fetch_assoc($res_insc)) {
        $count++;
        $bg_color = $fill ? 'background-color:rgb(252, 228, 236);' : 'background-color:white;';
        
        $orden_real = $row['orden'];
        $orden = $orden_real;
        if($orden <= -1 && $orden >= -9) $orden = "PS";
        else if($orden <= -10) $orden = "E";
        else if($orden == 0) $orden = "-";

        $text_color = 'color:rgb(30, 41, 59);';
        $font_weight = 'font-weight:normal;';
        $baja_marker = "";

        if($row['baja'] == 'si') {
            $text_color = 'color:rgb(239, 68, 68);';
            $nombre = $row['apellidos'] . ", " . $row['nombre'] . " (BAJA)";
            $baja_marker = "<b>[X] </b>";
        } else {
            $nombre = $row['apellidos'] . ", " . $row['nombre'];
        }
        
        // Resaltar inicio de corte
        if(isset($cortes_arr[$orden_real])) {
            $text_color = 'color:rgb(233, 38, 98);';
            $font_weight = 'font-weight:bold;';
        }

        $body_html .= '<tr style="' . $bg_color . $text_color . $font_weight . '">';
        $body_html .= '<td width="8%" align="center" style="border-left:1px solid #e92662; border-right:1px solid #e92662;">' . $orden . '</td>';
        $body_html .= '<td width="57%" align="left" style="border-left:1px solid #e92662; border-right:1px solid #e92662;">' . $baja_marker . $nombre . '</td>';
        $body_html .= '<td width="10%" align="center" style="border-left:1px solid #e92662; border-right:1px solid #e92662;">' . $row['año_nacimiento'] . '</td>';
        $body_html .= '<td width="25%" align="center" style="border-left:1px solid #e92662; border-right:1px solid #e92662;">' . $row['nombre_corto'] . '</td>';
        $body_html .= '</tr>';
        
        $fill = !$fill;
    }
    
    $footer_html = '</tbody></table>';
    $pdf->writeHTML($header_html . $body_html . $footer_html, true, false, false, false, '');
    
    // Cerrar la tabla con una línea rosa
    $pdf->SetDrawColor(233, 38, 98); // Rosa primario
    $pdf->SetY($pdf->GetY() - 0.5); // Ajuste fino para pegar la línea al fondo de la tabla
    $pdf->Cell(180, 0, '', 'T', 1); 
    
    $pdf->Ln(2);
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetTextColor(100, 116, 139);
    $pdf->Cell(0, 5, "TOTAL INSCRIPCIONES EN ESTA CATEGORÍA: " . $count, 0, 1, 'R');
}

// Salida
$pdf->Output($nombre_archivo, 'I');
?>
