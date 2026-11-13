<?php
ini_set('display_errors', 0);
setlocale(LC_ALL,'es_ES');
require_once('../tcpdf/tcpdf.php');
include('../database/dbconfig.php');
include('../lib/my_functions.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cabeceras estrictas anti-caché para navegadores móviles y visualizadores PDF integrados
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Validar acceso (Sesión o Token de Cron)
$cron_token_expected = "internal_cron_" . md5("SincrmInternalCron" . date('Y-m-d'));
$cron_token_provided = $_GET['cron_token'] ?? '';

if (!isset($_SESSION['email']) && $cron_token_provided !== $cron_token_expected) {
    header('Location: login.php');
    exit();
}

// 1. Obtener datos de la competición
$id_competicion = isset($_GET['id_competicion']) ? intval($_GET['id_competicion']) : (int)($_SESSION['id_competicion_activa'] ?? ($_SESSION['id_competicion_usuario'] ?? 0));
$query = "SELECT * FROM competiciones WHERE id = '$id_competicion'";
$result = mysqli_query($connection, $query);
$comp = mysqli_fetch_assoc($result);

if(!$comp) {
    die("Competición no encontrada.");
}

// --- CONTROL DE ACCESO ESTRICTO POR CLUB ---
// Si es ROL CLUB (5), forzamos su club de sesión para proteger la privacidad antes de la competición.
$filtro_club_id = 0;
$nombre_club_filtro = '';

if (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 5) {
    $filtro_club_id = (int)($_SESSION['club'] ?? 0);
} elseif (isset($_GET['club']) && in_array((int)($_SESSION['id_rol'] ?? 0), [1, 2, 3])) {
    // Roles superiores (Admin, Secretario o Delegado) pueden filtrar por un club si lo especifican
    $filtro_club_id = (int)$_GET['club'];
}

if ($filtro_club_id > 0) {
    $res_cl_info = mysqli_query($connection, "SELECT nombre, nombre_corto FROM clubes WHERE id = '$filtro_club_id'");
    if ($res_cl_info && $cl_row = mysqli_fetch_assoc($res_cl_info)) {
        $nombre_club_filtro = !empty($cl_row['nombre_corto']) ? $cl_row['nombre_corto'] : $cl_row['nombre'];
    }
}

$GLOBALS["comp_data"] = [
    "nombre" => $comp['nombre'],
    "lugar" => $comp['lugar'],
    "fecha" => dateAFecha($comp['fecha']),
    "organizador" => $comp['organizador'],
    "header" => "../" . $comp['header_informe'],
    "footer" => "../" . $comp['footer_informe'],
    "club_subtitulo" => $nombre_club_filtro
];

$titulo_param = $_GET['titulo'] ?? 'Inscripciones';
$is_inscripciones = (stripos($titulo_param, 'Inscrip') !== false);
$titulo_display = $is_inscripciones ? 'INSCRIPCIONES' : 'ORDEN DE SALIDA';
$GLOBALS["titulo_display"] = $titulo_display;

$sufijo_archivo = !empty($nombre_club_filtro) ? '_' . str_replace(' ', '_', $nombre_club_filtro) : '';
$nombre_archivo = ($is_inscripciones ? "Inscripciones" : "Orden_Salida") . $sufijo_archivo . "_" . str_replace(' ', '_', $comp['nombre']) . ".pdf";

// --- CONFIGURACIÓN DE COLORES ---
$primary_pink = [233, 38, 98]; // #e92662
$light_pink   = [252, 228, 236]; // #FCE4EC

// Helper de duración reglamentaria
function getReglamentaryDurationText($mod_nombre, $cat_nombre) {
    $mod = mb_strtolower($mod_nombre ?? '', 'UTF-8');
    $cat = mb_strtolower($cat_nombre ?? '', 'UTF-8');
    if (strpos($mod, 'solo') !== false) {
        if (strpos($mod, 'técnico') !== false || strpos($mod, 'tecnico') !== false) return '2:00 min (±5s)';
        return (strpos($cat, 'junior') !== false || strpos($cat, 'absolut') !== false || strpos($cat, 'senior') !== false) ? '2:15 min (±5s)' : '2:00 min (±5s)';
    } elseif (strpos($mod, 'dúo') !== false || strpos($mod, 'duo') !== false) {
        if (strpos($mod, 'técnico') !== false || strpos($mod, 'tecnico') !== false) return '2:20 min (±5s)';
        return (strpos($cat, 'junior') !== false || strpos($cat, 'absolut') !== false || strpos($cat, 'senior') !== false) ? '2:45 min (±5s)' : '2:30 min (±5s)';
    } elseif (strpos($mod, 'equipo') !== false) {
        if (strpos($mod, 'técnico') !== false || strpos($mod, 'tecnico') !== false) return '2:50 min (±5s)';
        return '3:30 min (±5s)';
    } elseif (strpos($mod, 'combinado') !== false || strpos($mod, 'combo') !== false) {
        return '3:00 min (±5s)';
    } elseif (strpos($mod, 'acrobática') !== false || strpos($mod, 'acrobatica') !== false) {
        return '3:00 min (±5s)';
    }
    return '';
}

// --- CLASE PDF PERSONALIZADA v4.0 ---
class SINCRM_PDF extends TCPDF {
    public function Header() {
        $headerData = $GLOBALS["comp_data"];
        $primary_pink = [233, 38, 98];
        
        // Header Image si existe
        if (!empty($headerData['header']) && file_exists($headerData['header'])) {
            $this->Image($headerData['header'], 10, 10, 190, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
        
        // Título del Informe
        $this->SetY(12);
        $this->SetFont('helvetica', 'B', 16);
        $this->SetTextColor($primary_pink[0], $primary_pink[1], $primary_pink[2]);
        $this->Cell(0, 10, mb_strtoupper($GLOBALS["titulo_display"] ?? 'INSCRIPCIONES'), 0, 1, 'C');
        
        $this->SetFont('helvetica', 'B', 10);
        $this->SetTextColor(100, 116, 139); // Slate-500
        $subtitulo_txt = $headerData['nombre'];
        if (!empty($headerData['club_subtitulo'])) {
            $subtitulo_txt .= ' · ' . mb_strtoupper($headerData['club_subtitulo']);
        }
        $this->Cell(0, 5, $subtitulo_txt, 0, 1, 'C', 0, '', 0, false, 'T', 'T');
        
        // Línea decorativa
        $this->SetDrawColor($primary_pink[0], $primary_pink[1], $primary_pink[2]);
        $this->SetLineWidth(0.5);
        $this->Line(15, 30, 195, 30);
    }

    public function Footer() {
        $headerData = $GLOBALS["comp_data"];
        
        // Footer Image si existe
        $this->SetY(-25);
        if (!empty($headerData['footer']) && file_exists($headerData['footer'])) {
            $this->Image($headerData['footer'], 15, $this->GetY(), 180, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }

        // Información en 3 filas con control absoluto de márgenes
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
$pdf->SetCreator('SINCRM v4.0');
$pdf->SetAuthor('SINCRM System');
$pdf->SetTitle($titulo_display . ' - ' . $comp['nombre']);
$pdf->SetMargins(15, 35, 15);
$pdf->SetAutoPageBreak(TRUE, 30);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

$total_paginas_generadas = 0;

// =========================================================================
// 1. SECCIÓN: FIGURAS (Si la competición contiene fases de figuras)
// =========================================================================
$query_cats_fig = "SELECT DISTINCT f.id_categoria, c.nombre as cat_nombre, c.edad_minima, c.edad_maxima 
                   FROM fases f 
                   JOIN categorias c ON f.id_categoria = c.id 
                   WHERE f.id_competicion = '$id_competicion' AND f.id_figura > 0 
                   ORDER BY c.edad_minima ASC";
$res_cats_fig = mysqli_query($connection, $query_cats_fig);

if ($res_cats_fig && mysqli_num_rows($res_cats_fig) > 0) {
    while($cat = mysqli_fetch_assoc($res_cats_fig)) {
        $id_cat = $cat['id_categoria'];
        
        // Fase de referencia (la primera fase de figuras de esta categoría)
        $q_fase_ref = "SELECT id, orden FROM fases WHERE id_competicion = '$id_competicion' AND id_categoria = '$id_cat' AND id_figura > 0 ORDER BY orden ASC LIMIT 1";
        $fase_ref_res = mysqli_query($connection, $q_fase_ref);
        $fase_ref = mysqli_fetch_assoc($fase_ref_res);
        $id_fase_ref = $fase_ref['id'] ?? 0;

        // Consultar nadadoras inscritas (en Inscripciones se ordena alfabéticamente; en Orden de Salida por i.orden)
        $order_clause_fig = $is_inscripciones ? "cl.nombre_corto ASC, n.apellidos ASC" : "i.orden ASC, n.apellidos ASC";
        $cond_club_fig = ($filtro_club_id > 0) ? " AND n.club = '$filtro_club_id'" : "";
        $q_insc = "SELECT i.*, n.nombre, n.apellidos, n.año_nacimiento, cl.nombre_corto 
                   FROM inscripciones_figuras i 
                   JOIN nadadoras n ON i.id_nadadora = n.id 
                   JOIN clubes cl ON n.club = cl.id 
                   WHERE i.id_fase = '$id_fase_ref' $cond_club_fig 
                   ORDER BY $order_clause_fig";
        $res_insc = mysqli_query($connection, $q_insc);
        $num_insc = mysqli_num_rows($res_insc);

        // Si no hay nadadoras inscritas en esta categoría, omitimos para no generar páginas vacías
        if ($num_insc == 0) continue;

        $pdf->AddPage();
        $total_paginas_generadas++;

        // Cabecera de Categoría
        $pdf->SetFont('helvetica', 'B', 13);
        $pdf->SetFillColor(248, 250, 252);
        $pdf->SetTextColor($primary_pink[0], $primary_pink[1], $primary_pink[2]);
        $edad_txt = ($cat['edad_minima'] > 0) ? " ({$cat['edad_minima']}-{$cat['edad_maxima']} AÑOS)" : (($cat['edad_maxima'] > 0) ? " (HASTA {$cat['edad_maxima']} AÑOS)" : "");
        $pdf->Cell(0, 11, "  " . mb_strtoupper($cat['cat_nombre']) . " · FIGURAS" . $edad_txt, 0, 1, 'L', true);
        
        // Obtener figuras de la categoría
        $q_figs = "SELECT fi.numero, fi.nombre, fi.grado_dificultad, fa.id as id_fase, fa.corte, fa.orden as fase_orden 
                   FROM figuras fi 
                   JOIN fases fa ON fa.id_figura = fi.id 
                   WHERE fa.id_categoria = '$id_cat' AND fa.id_competicion = '$id_competicion' 
                   ORDER BY fa.orden ASC";
        $res_figs = mysqli_query($connection, $q_figs);
        
        $pdf->SetY($pdf->GetY() + 1);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(71, 85, 105);
        
        $html_figs = '<table cellpadding="3" style="width:100%;">';
        $res_figs_data = [];
        while($fig = mysqli_fetch_assoc($res_figs)) {
            $res_figs_data[] = $fig;
        }

        $num_figs = count($res_figs_data);
        $id_fase_base = ($num_figs > 0) ? ($res_figs_data[0]['id_fase'] ?? 0) : 0;
        $cortes_arr = [];

        // Calcular número de corte (1..N) y orden de inicio para cada figura
        foreach($res_figs_data as $idx => &$fig) {
            $corte_num = $idx + 1;
            $fig_fase_id = $fig['id_fase'];
            $inicio_orden = intval($fig['corte'] ?? 0);

            // Si el corte en BD no está calculado o todas tienen 1, calcular dinámicamente según la nadadora que tiene orden=1 en esta fase
            if ($inicio_orden <= 0 || ($idx > 0 && $inicio_orden == 1)) {
                $q_first = mysqli_query($connection, "SELECT id_nadadora FROM inscripciones_figuras WHERE id_fase = '$fig_fase_id' AND orden = 1 LIMIT 1");
                if ($q_first && $rf = mysqli_fetch_assoc($q_first)) {
                    $id_nad_first = $rf['id_nadadora'];
                    $q_base_ord = mysqli_query($connection, "SELECT orden FROM inscripciones_figuras WHERE id_fase = '$id_fase_base' AND id_nadadora = '$id_nad_first' LIMIT 1");
                    if ($q_base_ord && $rb = mysqli_fetch_assoc($q_base_ord)) {
                        $inicio_orden = intval($rb['orden'] ?? 1);
                    }
                }
            }
            if ($inicio_orden <= 0) $inicio_orden = 1;
            // Ajustar inicio de corte si excede el número de inscritas (rotación modular)
            if ($num_insc > 0 && $inicio_orden > $num_insc) {
                $inicio_orden = (($inicio_orden - 1) % $num_insc) + 1;
            }
            $fig['corte_num'] = $corte_num;
            $fig['inicio_orden'] = $inicio_orden;

            if ($num_figs > 1) {
                $cortes_arr[$inicio_orden] = $corte_num;
            }
        }
        unset($fig);
        
        // Dividir figuras en 2 columnas para maximizar legibilidad
        $chunks = array_chunk($res_figs_data, 2);
        foreach($chunks as $chunk) {
            $html_figs .= '<tr>';
            foreach($chunk as $fig) {
                $corte_label = ($num_figs > 1) ? ' <b style="color:#e92662;">[CORTE '.$fig['corte_num'].' · Desde nº '.$fig['inicio_orden'].']</b>' : '';
                $html_figs .= '<td width="50%" style="border-bottom:0.1pt solid #f1f5f9;">';
                $html_figs .= '<b style="font-size:9pt; color:#e92662;">Evento ' . $fig['fase_orden'] . ':</b> <b style="font-size:9.5pt;">' . $fig['numero'] . '</b> ' . $fig['nombre'] . ' <b style="font-size:8pt; color:#475569;">(GD: ' . $fig['grado_dificultad'] . ')</b>' . $corte_label;
                $html_figs .= '</td>';
            }
            if(count($chunk) == 1) {
                $html_figs .= '<td width="50%" style="border-bottom:0.1pt solid #f1f5f9;"></td>';
            }
            $html_figs .= '</tr>';
        }
        $html_figs .= '</table>';
        $pdf->writeHTML($html_figs, true, false, true, false, '');
        $pdf->Ln(1);

        // Tabla de Nadadoras
        $header_html = '
        <table cellpadding="4" style="width:100%; font-size:11pt;">
            <thead>
                <tr style="background-color:rgb(233, 38, 98); color:white; font-weight:bold;">
                    <th width="8%" align="center" style="border:1px solid #e92662;">ORD</th>
                    <th width="57%" align="left" style="border:1px solid #e92662;">APELLIDOS Y NOMBRE</th>
                    <th width="10%" align="center" style="border:1px solid #e92662;">AÑO</th>
                    <th width="25%" align="center" style="border:1px solid #e92662;">CLUB</th>
                </tr>
            </thead>
            <tbody>';

        $body_html = "";
        $fill = false;
        $count = 0;
        while($row = mysqli_fetch_assoc($res_insc)) {
            $count++;
            $bg_color = $fill ? 'background-color:rgb(252, 228, 236);' : 'background-color:white;';
            
            $orden_real = $row['orden'];
            if($is_inscripciones) {
                $orden = "-";
            } else {
                if($orden_real <= -1 && $orden_real >= -9) $orden = "PS";
                else if($orden_real <= -10) $orden = "EX";
                else if($orden_real == 0 || $orden_real === null) $orden = "-";
                else $orden = $orden_real;
            }

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
            
            // Resaltar inicio de corte en orden de salida
            $corte_pill = '';
            if(!$is_inscripciones && $orden_real > 0 && isset($cortes_arr[$orden_real])) {
                $num_c = $cortes_arr[$orden_real];
                $text_color = 'color:rgb(233, 38, 98);';
                $font_weight = 'font-weight:bold;';
                $bg_color = 'background-color:rgb(254, 242, 242);';
                $corte_pill = ' <span style="color:#e92662; font-size:7.5pt; font-weight:bold;">[C'.$num_c.']</span>';
            }

            $body_html .= '<tr style="' . $bg_color . $text_color . $font_weight . '">';
            $body_html .= '<td width="8%" align="center" style="border-left:1px solid #e92662; border-right:1px solid #e92662;">' . $orden . $corte_pill . '</td>';
            $body_html .= '<td width="57%" align="left" style="border-left:1px solid #e92662; border-right:1px solid #e92662;">' . $baja_marker . $nombre . '</td>';
            $body_html .= '<td width="10%" align="center" style="border-left:1px solid #e92662; border-right:1px solid #e92662;">' . $row['año_nacimiento'] . '</td>';
            $body_html .= '<td width="25%" align="center" style="border-left:1px solid #e92662; border-right:1px solid #e92662;">' . $row['nombre_corto'] . '</td>';
            $body_html .= '</tr>';
            
            $fill = !$fill;
        }
        
        $footer_html = '</tbody></table>';
        $pdf->writeHTML($header_html . $body_html . $footer_html, true, false, false, false, '');
        
        // Línea rosa de cierre de tabla
        $pdf->SetDrawColor(233, 38, 98);
        $pdf->SetY($pdf->GetY() - 0.5);
        $pdf->Cell(180, 0, '', 'T', 1); 
        
        $pdf->Ln(2);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->Cell(0, 5, "TOTAL INSCRIPCIONES EN ESTA CATEGORÍA: " . $count, 0, 1, 'R');
    }
}

// =========================================================================
// 2. SECCIÓN: RUTINAS (Si la competición contiene fases de rutinas)
// =========================================================================
$query_fases_rut = "SELECT f.id as id_fase, f.id_categoria, f.id_modalidad, f.genero, f.orden as fase_orden, 
                           f.hora_inicio_estimada, f.elementos_coach_card, f.tecnico,
                           c.nombre as cat_nombre, m.nombre as mod_nombre, m.nombre_corto as mod_corto, m.numero_participantes
                    FROM fases f 
                    JOIN categorias c ON f.id_categoria = c.id 
                    JOIN modalidades m ON f.id_modalidad = m.id 
                    WHERE f.id_competicion = '$id_competicion' AND f.id_modalidad > 0 
                    ORDER BY f.orden ASC";
$res_fases_rut = mysqli_query($connection, $query_fases_rut);

if ($res_fases_rut && mysqli_num_rows($res_fases_rut) > 0) {
    while($fase_rut = mysqli_fetch_assoc($res_fases_rut)) {
        $id_fase = $fase_rut['id_fase'];
        
        // Consultar rutinas registradas en esta fase
        $order_clause_rut = $is_inscripciones ? "cl.nombre_corto ASC, r.id ASC" : "r.orden ASC, r.id ASC";
        $cond_club_rut = ($filtro_club_id > 0) ? " AND r.id_club = '$filtro_club_id'" : "";
        $q_ruts = "SELECT r.*, cl.nombre as club_nombre, cl.nombre_corto as club_corto 
                   FROM rutinas r 
                   JOIN clubes cl ON r.id_club = cl.id 
                   WHERE r.id_fase = '$id_fase' $cond_club_rut 
                   ORDER BY $order_clause_rut";
        $res_ruts = mysqli_query($connection, $q_ruts);
        $num_ruts = mysqli_num_rows($res_ruts);

        // Si no hay rutinas inscritas en esta fase, la omitimos para evitar páginas vacías
        if ($num_ruts == 0) continue;

        $pdf->AddPage();
        $total_paginas_generadas++;

        // Nombre descriptivo con sufijo Masc si procede
        $mod_full = $fase_rut['mod_nombre'] . (($fase_rut['genero'] === 'masculino') ? ' Masc' : '');
        $duracion_text = getReglamentaryDurationText($fase_rut['mod_nombre'], $fase_rut['cat_nombre']);

        // Cabecera de Evento
        $evento_num_txt = !empty($fase_rut['fase_orden']) ? ("EVENTO " . $fase_rut['fase_orden'] . " · ") : "";
        $pdf->SetFont('helvetica', 'B', 13);
        $pdf->SetFillColor(248, 250, 252);
        $pdf->SetTextColor($primary_pink[0], $primary_pink[1], $primary_pink[2]);
        $pdf->Cell(0, 11, "  " . $evento_num_txt . mb_strtoupper($fase_rut['cat_nombre']) . " · " . mb_strtoupper($mod_full), 0, 1, 'L', true);

        // Fila de metadatos técnicos del evento
        $pdf->SetY($pdf->GetY() + 1);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(71, 85, 105);
        
        $meta_items = [];
        if (!empty($fase_rut['fase_orden'])) {
            $meta_items[] = "<b>Evento:</b> " . $fase_rut['fase_orden'];
        }
        if (!empty($duracion_text)) {
            $meta_items[] = "<b>Duración Oficial:</b> " . $duracion_text;
        }
        if ($fase_rut['elementos_coach_card'] > 0) {
            $meta_items[] = "<b>Elementos CC:</b> " . $fase_rut['elementos_coach_card'];
        }
        if (!empty($fase_rut['hora_inicio_estimada'])) {
            $meta_items[] = "<b>Hora Estimada:</b> " . $fase_rut['hora_inicio_estimada'] . "h";
        }
        
        if (!empty($meta_items)) {
            $html_meta = '<table cellpadding="3" style="width:100%; border-bottom:0.1pt solid #f1f5f9;"><tr><td>' . implode(' &nbsp;&bull;&nbsp; ', $meta_items) . '</td></tr></table>';
            $pdf->writeHTML($html_meta, true, false, true, false, '');
            $pdf->Ln(1);
        }

        // Estructura de Columnas para Rutinas
        if ($is_inscripciones) {
            $header_html = '
            <table cellpadding="4" style="width:100%; font-size:10pt;">
                <thead>
                    <tr style="background-color:rgb(233, 38, 98); color:white; font-weight:bold;">
                        <th width="6%" align="center" style="border:1px solid #e92662;">ORD</th>
                        <th width="18%" align="left" style="border:1px solid #e92662;">CLUB</th>
                        <th width="46%" align="left" style="border:1px solid #e92662;">DEPORTISTAS (TITULAR / RESERVA)</th>
                        <th width="15%" align="center" style="border:1px solid #e92662;">MÚSICA</th>
                        <th width="15%" align="center" style="border:1px solid #e92662;">COACH CARD</th>
                    </tr>
                </thead>
                <tbody>';
        } else {
            $header_html = '
            <table cellpadding="4" style="width:100%; font-size:10pt;">
                <thead>
                    <tr style="background-color:rgb(233, 38, 98); color:white; font-weight:bold;">
                        <th width="7%" align="center" style="border:1px solid #e92662;">ORD</th>
                        <th width="25%" align="left" style="border:1px solid #e92662;">CLUB & RUTINA</th>
                        <th width="50%" align="left" style="border:1px solid #e92662;">DEPORTISTAS (TITULAR / RESERVA)</th>
                        <th width="18%" align="center" style="border:1px solid #e92662;">COACH CARD</th>
                    </tr>
                </thead>
                <tbody>';
        }

        $body_html = "";
        $fill = false;
        $count_rut = 0;

        while($rut = mysqli_fetch_assoc($res_ruts)) {
            $count_rut++;
            $bg_color = $fill ? 'background-color:rgb(252, 228, 236);' : 'background-color:white;';
            
            // Turno de actuación
            $orden_real = $rut['orden'];
            if ($is_inscripciones) {
                $orden = "-";
            } else {
                if ($orden_real == -1) $orden = "PS";
                elseif ($orden_real == -2) $orden = "EX";
                elseif ($orden_real <= -10) $orden = "EX";
                elseif ($orden_real == 0 || $orden_real === null) $orden = "-";
                else $orden = $orden_real;
            }

            // Consultar deportistas de la rutina
            $q_parts = "SELECT rp.reserva, n.nombre, n.apellidos, n.año_nacimiento 
                        FROM rutinas_participantes rp 
                        JOIN nadadoras n ON rp.id_nadadora = n.id 
                        WHERE rp.id_rutina = '{$rut['id']}' 
                        ORDER BY rp.reserva ASC, n.apellidos ASC";
            $res_parts = mysqli_query($connection, $q_parts);
            
            $parts_arr = [];
            while($p = mysqli_fetch_assoc($res_parts)) {
                $is_reserva = ($p['reserva'] === 'si');
                $tag_reserva = $is_reserva ? ' <b style="color:#d97706; font-size:8pt;">(RESERVA)</b>' : '';
                $style_p = $is_reserva ? 'color:#64748b; font-style:italic;' : 'color:#0f172a;';
                $parts_arr[] = '<span style="' . $style_p . '">' . $p['apellidos'] . ', ' . $p['nombre'] . ' <small style="color:#94a3b8;">(' . $p['año_nacimiento'] . ')</small>' . $tag_reserva . '</span>';
            }
            $parts_html = !empty($parts_arr) ? implode('<br>', $parts_arr) : '<i style="color:#ef4444;">Sin deportistas asignadas</i>';

            // Temática o nombre de rutina si existe
            $club_info = '<b>' . $rut['club_corto'] . '</b>';
            if (!empty($rut['tematica'])) {
                $club_info .= '<br><span style="font-size:8.5pt; color:#475569;">&laquo;' . htmlspecialchars($rut['tematica']) . '&raquo;</span>';
            } elseif (!empty($rut['nombre'])) {
                $club_info .= '<br><span style="font-size:8.5pt; color:#475569;">' . htmlspecialchars($rut['nombre']) . '</span>';
            }

            // Detalles técnicos
            $has_music = (!empty($rut['music_name']) || !empty($rut['music_original_name']));
            $music_tag = $has_music ? '<b style="color:#10b981;">SUBIDA</b>' : '<span style="color:#ef4444; font-size:8pt;">Pendiente</span>';
            
            $has_cc = ($rut['dd_total'] > 0);
            if ($has_cc) {
                $cc_tag = '<b style="color:#10b981;">Realizada</b>';
            } else {
                $cc_tag = ($fase_rut['elementos_coach_card'] > 0) ? '<span style="color:#f59e0b; font-size:8.5pt;">Pendiente</span>' : '<span style="color:#94a3b8; font-size:8.5pt;">N/A</span>';
            }

            $body_html .= '<tr style="' . $bg_color . '">';
            if ($is_inscripciones) {
                $body_html .= '<td width="6%" align="center" style="border-left:1px solid #e92662; border-right:1px solid #e92662; font-weight:bold;">' . $orden . '</td>';
                $body_html .= '<td width="18%" align="left" style="border-left:1px solid #e92662; border-right:1px solid #e92662;">' . $club_info . '</td>';
                $body_html .= '<td width="46%" align="left" style="border-left:1px solid #e92662; border-right:1px solid #e92662;">' . $parts_html . '</td>';
                $body_html .= '<td width="15%" align="center" style="border-left:1px solid #e92662; border-right:1px solid #e92662;">' . $music_tag . '</td>';
                $body_html .= '<td width="15%" align="center" style="border-left:1px solid #e92662; border-right:1px solid #e92662;">' . $cc_tag . '</td>';
            } else {
                $body_html .= '<td width="7%" align="center" style="border-left:1px solid #e92662; border-right:1px solid #e92662; font-weight:bold;">' . $orden . '</td>';
                $body_html .= '<td width="25%" align="left" style="border-left:1px solid #e92662; border-right:1px solid #e92662;">' . $club_info . '</td>';
                $body_html .= '<td width="50%" align="left" style="border-left:1px solid #e92662; border-right:1px solid #e92662;">' . $parts_html . '</td>';
                $body_html .= '<td width="18%" align="center" style="border-left:1px solid #e92662; border-right:1px solid #e92662;">' . $cc_tag . '</td>';
            }
            $body_html .= '</tr>';

            $fill = !$fill;
        }

        $footer_html = '</tbody></table>';
        $pdf->writeHTML($header_html . $body_html . $footer_html, true, false, false, false, '');

        // Línea rosa de cierre de tabla
        $pdf->SetDrawColor(233, 38, 98);
        $pdf->SetY($pdf->GetY() - 0.5);
        $pdf->Cell(180, 0, '', 'T', 1); 

        $pdf->Ln(2);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->Cell(0, 5, "TOTAL RUTINAS EN ESTE EVENTO: " . $count_rut, 0, 1, 'R');
    }
}

// =========================================================================
// 3. CASO ESPECIAL: Si no se encontró ninguna inscripción
// =========================================================================
if ($total_paginas_generadas == 0) {
    $pdf->AddPage();
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetTextColor(100, 116, 139);
    $pdf->Cell(0, 30, 'No hay inscripciones registradas para esta competición.', 0, 1, 'C');
}

// Salida oficial del PDF
$pdf->Output($nombre_archivo, 'I');
?>