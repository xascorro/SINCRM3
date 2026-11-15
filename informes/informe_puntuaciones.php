<?php
// Silenciar warnings en generación PDF para evitar corromper la salida binaria
ini_set('display_errors', 0);
error_reporting(0);
setlocale(LC_ALL, 'es_ES');

require_once(__DIR__ . '/../tcpdf/tcpdf.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar acceso (Sesión o Token de Cron)
$cron_token_expected = "internal_cron_" . md5("SincrmInternalCron" . date('Y-m-d'));
$cron_token_provided = $_GET['cron_token'] ?? '';

if (!isset($_SESSION['email']) && !isset($_SESSION['username']) && $cron_token_provided !== $cron_token_expected) {
    include(__DIR__ . '/../security.php');
} else {
    include_once(__DIR__ . '/../database/dbconfig.php');
}
include(__DIR__ . '/../lib/my_functions.php');

mysqli_query($connection, "SET NAMES 'utf8'");

// 1. Obtener datos de la competición
$id_competicion_req = isset($_GET['id_competicion']) ? intval($_GET['id_competicion']) : (int)($_SESSION['id_competicion_activa'] ?? 0);

if ($id_competicion_req > 0) {
    $q_comp = "SELECT * FROM competiciones WHERE id = $id_competicion_req";
} else {
    $q_comp = "SELECT * FROM competiciones WHERE activo = 'si' LIMIT 1";
}
$res_comp = mysqli_query($connection, $q_comp);
$comp = mysqli_fetch_assoc($res_comp);

if (!$comp) {
    die("No hay una competición activa configurada.");
}

$GLOBALS["id_competicion_activa"] = (int)$comp['id'];
$GLOBALS["nombre_competicion_activa"] = $comp['nombre'];
$GLOBALS["lugar"] = $comp['lugar'];
$GLOBALS["piscina"] = $comp['piscina'];
$GLOBALS["fecha"] = dateAFecha($comp['fecha']);
$GLOBALS["organizador"] = $comp['organizador'];
$GLOBALS["organizador_tipo"] = $comp['organizador_tipo'];
$GLOBALS["header_image"] = !empty($comp['header_informe']) ? '../' . $comp['header_informe'] : '';
$GLOBALS["footer_image"] = !empty($comp['footer_informe']) ? '../' . $comp['footer_informe'] : '';

$titulo = $_GET['titulo'] ?? 'Clasificación Detallada';
$GLOBALS["titulo_documento"] = mb_strtoupper($titulo, 'UTF-8');
$GLOBALS["subtitulo_documento"] = $GLOBALS["nombre_competicion_activa"];
$GLOBALS["footer_substring"] = "Sede: " . $GLOBALS['lugar'] . " · Piscina: " . $GLOBALS['piscina'] . " · Fecha: " . $GLOBALS['fecha'];

$nombre_documento = 'Resultados_' . str_replace(' ', '_', $GLOBALS["nombre_competicion_activa"]) . '.pdf';

// Colores corporativos v4
$primary_color = '#e92662'; // Rosa corporativo
$bg_header = '#f8fafc';
$bg_row_par = '#ffffff';
$bg_row_impar = '#f8fafc';
$border_color = '#e2e8f0';

// Clase personalizada TCPDF v4
class SINCRM_PUNTUACIONES_PDF extends TCPDF {
    public function Header() {
        if (!empty($GLOBALS['header_image']) && file_exists($GLOBALS['header_image'])) {
            $this->Image($GLOBALS['header_image'], 12, 5, 186, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
            $this->SetY(6.5);
        } else {
            $this->SetY(6.5);
        }

        $this->SetFont('helvetica', 'B', 14);
        $this->SetTextColor(233, 38, 98);
        $this->Cell(0, 6, $GLOBALS['titulo_documento'], 0, 1, 'C');

        $this->SetFont('helvetica', 'B', 8.5);
        $this->SetTextColor(71, 85, 105);
        $this->Cell(0, 4, mb_strtoupper($GLOBALS['subtitulo_documento'], 'UTF-8'), 0, 1, 'C');

        // Línea divisoria suave bajo la cabecera
        $this->SetDrawColor(226, 232, 240);
        $this->SetLineWidth(0.3);
        $this->Line(12, 24.5, 198, 24.5);
    }

    public function Footer() {
        if (!empty($GLOBALS['footer_image']) && file_exists($GLOBALS['footer_image'])) {
            $this->SetY(275);
            $this->Image($GLOBALS['footer_image'], 12, 275, 186, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
        $this->SetY(276);
        $this->SetFont('helvetica', 'B', 7);
        $this->SetTextColor(71, 85, 105);
        $this->setCellPaddings(0, 0, 0, 0);

        $txt_sede = "Sede: " . trim($GLOBALS['lugar'] ?? '');
        $txt_fecha = "Fecha: " . trim(dateAFecha($GLOBALS['fecha'] ?? ''));
        $txt_pag = 'Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages();

        $this->MultiCell(186, 3.8, $txt_sede, 0, 'R', false, 1, 12, '', true, 0, false, true, 3.8, 'M', true);
        $this->MultiCell(186, 3.8, $txt_fecha, 0, 'R', false, 1, 12, '', true, 0, false, true, 3.8, 'M', true);
        $this->MultiCell(186, 3.8, $txt_pag, 0, 'R', false, 1, 12, '', true, 0, false, true, 3.8, 'M', true);
    }
}

$pdf = new SINCRM_PUNTUACIONES_PDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator('SINCRM v4');
$pdf->SetAuthor('Pedro Díaz');
$pdf->SetTitle($GLOBALS["titulo_documento"]);
$pdf->SetSubject($GLOBALS["nombre_competicion_activa"]);

$pdf->SetMargins(12, 27, 12);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(15);
$pdf->SetAutoPagebreak(TRUE, 18);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// =============================================================================
// SECCIÓN 1: HOJA TÉCNICA OFICIAL (Si se solicita)
// =============================================================================
if (isset($_GET['hoja_tecnica']) && $_GET['hoja_tecnica'] == 'si') {
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 8);

    $organizador = '';
    if ($GLOBALS['organizador_tipo'] == 'Federación') {
        $q_org = mysqli_query($connection, "SELECT nombre FROM federaciones WHERE id = '" . $GLOBALS['organizador'] . "'");
        $organizador = $q_org ? (mysqli_fetch_assoc($q_org)['nombre'] ?? '') : '';
    } elseif ($GLOBALS['organizador_tipo'] == 'Club') {
        $q_org = mysqli_query($connection, "SELECT nombre FROM clubes WHERE id = '" . $GLOBALS['organizador'] . "'");
        $organizador = $q_org ? (mysqli_fetch_assoc($q_org)['nombre'] ?? '') : '';
    }

    $q_clubs = "SELECT nombre FROM clubes WHERE id IN (SELECT DISTINCT id_club FROM rutinas WHERE id_competicion = '" . $GLOBALS['id_competicion_activa'] . "')";
    $res_clubs = mysqli_query($connection, $q_clubs);
    $arr_clubs = [];
    while ($cl = mysqli_fetch_assoc($res_clubs)) {
        $arr_clubs[] = $cl['nombre'];
    }
    $clubs_str = !empty($arr_clubs) ? implode(' · ', $arr_clubs) : 'Todos los clubes inscritos';

    $html_tec = '<table cellpadding="4" style="border:1px solid #e2e8f0; font-size:8pt; border-collapse:collapse; width:100%;">';
    $html_tec .= '<tr style="background-color:#f8fafc;"><th colspan="2" style="font-size:9pt; font-weight:bold; color:#1e293b; border-bottom:1px solid #e2e8f0;">DATOS DE LA COMPETICIÓN</th></tr>';
    $html_tec .= '<tr><td width="30%" style="font-weight:bold; color:#64748b; border-bottom:1px solid #f1f5f9;">Competición:</td><td width="70%" style="border-bottom:1px solid #f1f5f9;">' . htmlspecialchars($GLOBALS["nombre_competicion_activa"]) . '</td></tr>';
    $html_tec .= '<tr><td style="font-weight:bold; color:#64748b; border-bottom:1px solid #f1f5f9;">Fecha y Sede:</td><td style="border-bottom:1px solid #f1f5f9;">' . htmlspecialchars($GLOBALS["fecha"]) . ' · ' . htmlspecialchars($GLOBALS["lugar"]) . ' (' . htmlspecialchars($GLOBALS["piscina"]) . ')</td></tr>';
    $html_tec .= '<tr><td style="font-weight:bold; color:#64748b; border-bottom:1px solid #f1f5f9;">Organizador:</td><td style="border-bottom:1px solid #f1f5f9;">' . htmlspecialchars($organizador) . '</td></tr>';
    $html_tec .= '<tr><td style="font-weight:bold; color:#64748b;">Clubes Participantes:</td><td>' . htmlspecialchars($clubs_str) . '</td></tr>';
    $html_tec .= '</table><br><br>';

    // Puestos oficiales
    $html_tec .= '<table cellpadding="4" style="border:1px solid #e2e8f0; font-size:8pt; border-collapse:collapse; width:100%;">';
    $html_tec .= '<tr style="background-color:#f8fafc;"><th colspan="3" style="font-size:9pt; font-weight:bold; color:#1e293b; border-bottom:1px solid #e2e8f0;">OFICIALES Y COMITÉ TÉCNICO</th></tr>';

    $q_pj = "SELECT pj.id, pj.id_juez, pj.id_puestos_juez, pu.nombre as puesto_nombre, j.nombre, j.apellidos, f.nombre_corto as fed_nombre 
             FROM puesto_juez pj 
             JOIN puestos_juez pu ON pj.id_puestos_juez = pu.id 
             JOIN jueces j ON pj.id_juez = j.id 
             LEFT JOIN federaciones f ON j.federacion = f.id 
             WHERE pj.id_competicion = '" . $GLOBALS['id_competicion_activa'] . "' 
             ORDER BY pu.id ASC";
    $res_pj = mysqli_query($connection, $q_pj);
    while ($p = mysqli_fetch_assoc($res_pj)) {
        $html_tec .= '<tr>';
        $html_tec .= '<td width="35%" style="font-weight:bold; color:#475569; border-bottom:1px solid #f1f5f9;">' . htmlspecialchars($p['puesto_nombre']) . '</td>';
        $html_tec .= '<td width="50%" style="border-bottom:1px solid #f1f5f9;">' . htmlspecialchars($p['nombre'] . ' ' . $p['apellidos']) . '</td>';
        $html_tec .= '<td width="15%" style="color:#64748b; text-align:right; border-bottom:1px solid #f1f5f9;">' . htmlspecialchars($p['fed_nombre'] ?? '') . '</td>';
        $html_tec .= '</tr>';
    }
    $html_tec .= '</table><br><br>';

    $html_tec .= '<table cellpadding="4" style="border:1px solid #e2e8f0; font-size:8pt; border-collapse:collapse; width:100%;">';
    $html_tec .= '<tr style="background-color:#f8fafc;"><th colspan="2" style="font-size:9pt; font-weight:bold; color:#1e293b; border-bottom:1px solid #e2e8f0;">JUECES DE PUNTUACIÓN</th></tr>';
    $q_jp = "SELECT DISTINCT j.id, j.nombre, j.apellidos, f.nombre_corto as fed_nombre 
             FROM panel_jueces pj 
             JOIN jueces j ON pj.id_juez = j.id 
             LEFT JOIN federaciones f ON j.federacion = f.id 
             WHERE pj.id_competicion = '" . $GLOBALS['id_competicion_activa'] . "' AND j.id != 108 
             ORDER BY j.apellidos ASC";
    $res_jp = mysqli_query($connection, $q_jp);
    while ($j = mysqli_fetch_assoc($res_jp)) {
        $html_tec .= '<tr>';
        $html_tec .= '<td width="70%" style="border-bottom:1px solid #f1f5f9;">' . htmlspecialchars($j['apellidos'] . ', ' . $j['nombre']) . '</td>';
        $html_tec .= '<td width="30%" style="color:#64748b; text-align:right; border-bottom:1px solid #f1f5f9;">' . htmlspecialchars($j['fed_nombre'] ?? '') . '</td>';
        $html_tec .= '</tr>';
    }
    $html_tec .= '</table><br><br>';
    $html_tec .= '<div style="text-align:right; font-size:8pt; font-weight:bold; color:#64748b;"><br><br>Fdo. El Juez Árbitro</div>';

    $pdf->writeHTML($html_tec, true, false, true, false, '');
}

// =============================================================================
// SECCIÓN 2: CLASIFICACIÓN GENERAL / MEMORIAL (Si se solicita)
// =============================================================================
if (isset($_GET['memorial']) && $_GET['memorial'] == 'si') {
    $puntos_tabla = [0, 19, 16, 14, 13, 12, 11, 10, 9, 8, 7, 6, 5, 4, 3, 2, 1];
    $pdf->AddPage('L');
    $pdf->SetFont('helvetica', '', 8);

    $html_mem = '<div style="font-size:12pt; font-weight:bold; color:#1e293b; margin-bottom:8px;">CLASIFICACIÓN GENERAL POR CLUBES</div>';

    $q_fases_mem = "SELECT DISTINCT f.*, m.nombre as nombre_modalidad, m.nombre_corto as mod_corto, m.numero_participantes, c.nombre_corto as cat_corto 
                    FROM fases f 
                    JOIN modalidades m ON f.id_modalidad = m.id 
                    JOIN categorias c ON f.id_categoria = c.id 
                    WHERE f.puntua_memorial = 'si' AND f.id_competicion = '" . $GLOBALS['id_competicion_activa'] . "' 
                    ORDER BY m.numero_participantes, f.id_modalidad, f.id_categoria";
    $res_fases_mem = mysqli_query($connection, $q_fases_mem);
    $fases_arr = [];
    while ($f = mysqli_fetch_assoc($res_fases_mem)) {
        $fases_arr[] = $f;
    }

    $q_clubs_mem = "SELECT id, nombre, nombre_corto FROM clubes WHERE id IN (SELECT DISTINCT id_club FROM rutinas WHERE id_competicion = '" . $GLOBALS['id_competicion_activa'] . "') ORDER BY nombre_corto ASC";
    $res_clubs_mem = mysqli_query($connection, $q_clubs_mem);
    $clubs_puntos = [];

    while ($cl = mysqli_fetch_assoc($res_clubs_mem)) {
        $total_cl = 0;
        foreach ($fases_arr as $f) {
            $lim = ($f['numero_participantes'] <= 2) ? 2 : 1;
            $q_r_pts = "SELECT posicion FROM rutinas WHERE id_fase = " . $f['id'] . " AND id_club = " . $cl['id'] . " AND orden > 0 AND baja != 'si' ORDER BY posicion ASC LIMIT $lim";
            $res_r_pts = mysqli_query($connection, $q_r_pts);
            while ($r = mysqli_fetch_assoc($res_r_pts)) {
                $pos = intval($r['posicion']);
                $pts = ($pos > 0 && $pos <= 16) ? ($puntos_tabla[$pos] ?? 0) : 0;
                if ($f['numero_participantes'] > 2) $pts *= 2;
                $total_cl += $pts;
            }
        }
        $clubs_puntos[] = [
            'id' => $cl['id'],
            'nombre' => $cl['nombre_corto'] ?: $cl['nombre'],
            'puntos' => $total_cl
        ];
    }

    // Ordenar de mayor a menor puntuación
    usort($clubs_puntos, function($a, $b) {
        return $b['puntos'] <=> $a['puntos'];
    });

    $html_mem .= '<table cellpadding="4" style="border:1px solid #cbd5e1; border-collapse:collapse; width:100%; font-size:8pt;">';
    $html_mem .= '<tr style="background-color:#0f172a; color:#ffffff; font-weight:bold;">';
    $html_mem .= '<th width="10%">PUESTO</th>';
    $html_mem .= '<th width="65%">CLUB</th>';
    $html_mem .= '<th width="25%" style="text-align:right;">PUNTOS TOTALES</th>';
    $html_mem .= '</tr>';

    $p_rank = 1;
    foreach ($clubs_puntos as $cp) {
        $bg = ($p_rank % 2 == 0) ? '#f8fafc' : '#ffffff';
        $badge_color = ($p_rank == 1) ? '#eab308' : (($p_rank == 2) ? '#94a3b8' : (($p_rank == 3) ? '#cd7f32' : '#64748b'));
        $html_mem .= '<tr style="background-color:' . $bg . '; border-bottom:1px solid #e2e8f0;">';
        $html_mem .= '<td style="font-weight:bold; color:' . $badge_color . ';">' . $p_rank . 'º</td>';
        $html_mem .= '<td style="font-weight:bold; color:#1e293b;">' . htmlspecialchars($cp['nombre']) . '</td>';
        $html_mem .= '<td style="text-align:right; font-weight:bold; font-size:9pt; color:#2563eb;">' . number_format($cp['puntos'], 0) . ' pts</td>';
        $html_mem .= '</tr>';
        $p_rank++;
    }
    $html_mem .= '</table>';
    $pdf->writeHTML($html_mem, true, false, true, false, '');
}

// =============================================================================
// SECCIÓN 3: RESULTADOS DETALLADOS POR FASE, RUTINA Y FIGURA
// =============================================================================
$condicion_fase = "";
$id_fase_param = (isset($_GET['id_fase']) && intval($_GET['id_fase']) > 0) ? intval($_GET['id_fase']) : 0;

// Obtener oficiales principales (Juez Árbitro, Secretario, etc.) desde puesto_juez
$q_puestos_oficiales = "SELECT pj.*, pt.nombre as puesto_nombre, j.nombre, j.apellidos, f.nombre_corto as fed_nombre 
                        FROM puesto_juez pj 
                        JOIN puestos_juez pt ON pj.id_puestos_juez = pt.id 
                        JOIN jueces j ON pj.id_juez = j.id 
                        LEFT JOIN federaciones f ON j.federacion = f.id 
                        WHERE pj.id_competicion = '" . $GLOBALS['id_competicion_activa'] . "' 
                        ORDER BY pt.id ASC";
$res_puestos_oficiales = mysqli_query($connection, $q_puestos_oficiales);
$puestos_oficiales_arr = [];
if ($res_puestos_oficiales) {
    while ($po = mysqli_fetch_assoc($res_puestos_oficiales)) {
        $fed_str = !empty($po['fed_nombre']) ? ' (' . htmlspecialchars($po['fed_nombre']) . ')' : '';
        $puestos_oficiales_arr[] = '<b style="color:#0f172a;">' . htmlspecialchars($po['puesto_nombre']) . ':</b> ' . htmlspecialchars($po['nombre'] . ' ' . $po['apellidos']) . $fed_str;
    }
}
$puestos_oficiales_str = implode(' &nbsp;·&nbsp; ', $puestos_oficiales_arr);

// -----------------------------------------------------------------------------
// HELPER 1: RENDERIZAR RESUMEN GENERAL DE FIGURAS POR CATEGORÍA
// -----------------------------------------------------------------------------
if (!function_exists('render_resumen_figuras_categoria')) {
    function render_resumen_figuras_categoria($cat_info, $connection, $pdf) {
        $id_categoria = intval($cat_info['id']);
        $nombre_categoria = $cat_info['nombre'];

        // Obtener figuras oficiales de esta categoría
        $q_figs_cat = "SELECT f.id as id_fase, f.orden as fase_orden, fig.id as id_figura, fig.numero, fig.nombre as fig_nom, fig.grado_dificultad as gd 
                       FROM fases f 
                       JOIN figuras fig ON f.id_figura = fig.id 
                       WHERE f.id_competicion = '" . $GLOBALS['id_competicion_activa'] . "' AND f.id_categoria = $id_categoria 
                       ORDER BY f.orden ASC, f.id ASC";
        $res_figs_cat = mysqli_query($connection, $q_figs_cat);
        $figs_arr = [];
        $fases_cat_ids = [];
        $total_gd = 0.0;
        if ($res_figs_cat) {
            while ($fg = mysqli_fetch_assoc($res_figs_cat)) {
                $figs_arr[] = $fg;
                $fases_cat_ids[] = intval($fg['id_fase']);
                $total_gd += floatval($fg['gd']);
            }
        }

        if (empty($figs_arr)) {
            return;
        }

        // Obtener nadadoras y clasificación oficial de la categoría
        $q_sw = "SELECT rfc.*, n.nombre, n.apellidos, n.año_nacimiento, cl.nombre_corto as club_nom 
                 FROM resultados_figuras_categorias rfc 
                 JOIN nadadoras n ON rfc.id_nadadora = n.id 
                 LEFT JOIN clubes cl ON n.club = cl.id 
                 WHERE rfc.id_competicion = '" . $GLOBALS['id_competicion_activa'] . "' AND rfc.id_categoria = $id_categoria 
                 ORDER BY (rfc.baja = 'si') ASC, (rfc.posicion IS NULL OR rfc.posicion = 0) ASC, rfc.posicion ASC, rfc.nota_final_calculada DESC";
        $res_sw = mysqli_query($connection, $q_sw);
        $swimmers_arr = [];
        if ($res_sw) {
            while ($sw = mysqli_fetch_assoc($res_sw)) {
                $swimmers_arr[] = $sw;
            }
        }

        // Fallback en caso de que no existan registros computados en resultados_figuras_categorias
        if (empty($swimmers_arr) && !empty($fases_cat_ids)) {
            $q_sw_fallback = "SELECT DISTINCT i.id_nadadora, n.nombre, n.apellidos, n.año_nacimiento, cl.nombre_corto as club_nom, 
                                     0 as posicion, 0 as nota_final_calculada, 0 as diferencia, i.baja 
                              FROM inscripciones_figuras i 
                              JOIN nadadoras n ON i.id_nadadora = n.id 
                              LEFT JOIN clubes cl ON n.club = cl.id 
                              WHERE i.id_fase IN (" . implode(',', $fases_cat_ids) . ") 
                              ORDER BY (i.baja = 'si') ASC, n.apellidos ASC";
            $res_sw_fallback = mysqli_query($connection, $q_sw_fallback);
            if ($res_sw_fallback) {
                while ($sw = mysqli_fetch_assoc($res_sw_fallback)) {
                    $swimmers_arr[] = $sw;
                }
            }
        }

        if (empty($swimmers_arr)) {
            return;
        }

        // Mapa de puntuaciones de inscripciones_figuras
        $scores_map = [];
        if (!empty($fases_cat_ids)) {
            $q_sc = "SELECT id_nadadora, id_fase, nota_media, nota_final, baja 
                     FROM inscripciones_figuras 
                     WHERE id_fase IN (" . implode(',', $fases_cat_ids) . ")";
            $res_sc = mysqli_query($connection, $q_sc);
            if ($res_sc) {
                while ($sc = mysqli_fetch_assoc($res_sc)) {
                    $scores_map[intval($sc['id_nadadora'])][intval($sc['id_fase'])] = $sc;
                }
            }
        }

        // Iniciar página para la categoría
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 8);

        // Cabecera de Categoría
        $html = '<table cellpadding="4" style="border:1px solid #cbd5e1; background-color:#f8fafc; border-collapse:collapse; width:100%; margin-bottom:5px;">';
        $html .= '<tr>';
        $html .= '<td width="65%">';
        $html .= '<b style="font-size:11pt; color:#1e293b;">CLASIFICACIÓN GENERAL DE FIGURAS</b><br>';
        $html .= '<span style="font-size:8.5pt; color:#e92662; font-weight:bold;">CATEGORÍA: ' . mb_strtoupper($nombre_categoria, 'UTF-8') . '</span>';
        $html .= '</td>';
        $html .= '<td width="35%" style="text-align:right; font-size:8pt; color:#64748b;">';
        $html .= '<b>Figuras: <span style="color:#0f172a; font-size:9pt;">' . count($figs_arr) . '</span> &nbsp;|&nbsp; ';
        $html .= 'Suma GD: <span style="color:#0284c7; font-size:10pt;">' . number_format($total_gd, 1) . '</span></b>';
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '</table>';

        // Leyenda descriptiva de figuras
        $figs_legend = [];
        foreach ($figs_arr as $fg) {
            $figs_legend[] = '<b>' . $fg['numero'] . '</b> ' . htmlspecialchars($fg['fig_nom']) . ' <span style="color:#0284c7;">(GD ' . number_format($fg['gd'], 1) . ')</span>';
        }
        $html .= '<table cellpadding="2.5" style="border:1px solid #e2e8f0; background-color:#ffffff; font-size:6.5pt; color:#475569; width:100%; margin-bottom:6px;">';
        $html .= '<tr><td width="10%"><b style="color:#0f172a;">Figuras:</b></td><td width="90%">' . implode(' &nbsp;·&nbsp; ', $figs_legend) . '</td></tr>';
        $html .= '</table>';

        // Anchos de columnas adaptativos
        $num_figs = count($figs_arr);
        $w_pos = 5;
        $w_club = 13;
        $w_tot = 10;
        $w_dif = 7;
        $w_figs_total = 40;
        $w_fig_col = ($num_figs > 0) ? floor($w_figs_total / $num_figs) : 10;
        $w_figs_usado = $w_fig_col * $num_figs;
        $w_nad = 100 - ($w_pos + $w_club + $w_tot + $w_dif + $w_figs_usado);

        // Tabla de puntuaciones
        $html .= '<table cellpadding="2.5" style="border:1px solid #cbd5e1; border-collapse:collapse; width:100%; font-size:7.5pt;">';
        $html .= '<thead>';
        $html .= '<tr style="background-color:#0f172a; color:#ffffff; font-weight:bold;">';
        $html .= '<th width="' . $w_pos . '%" align="center">POS</th>';
        $html .= '<th width="' . $w_nad . '%">NADADORA</th>';
        $html .= '<th width="' . $w_club . '%">CLUB</th>';
        foreach ($figs_arr as $idx => $fg) {
            $html .= '<th width="' . $w_fig_col . '%" align="center">';
            $html .= 'FIG. ' . ($idx + 1) . '<br>';
            $html .= '<span style="font-size:6pt; font-weight:normal; color:#94a3b8;">' . htmlspecialchars($fg['numero']) . ' (' . number_format($fg['gd'], 1) . ')</span>';
            $html .= '</th>';
        }
        $html .= '<th width="' . $w_tot . '%" align="right">TOTAL<br><span style="font-size:6pt; font-weight:normal; color:#94a3b8;">Puntos</span></th>';
        $html .= '<th width="' . $w_dif . '%" align="right">DIF<br><span style="font-size:6pt; font-weight:normal; color:#94a3b8;">1º Puesto</span></th>';
        $html .= '</tr>';
        $html .= '</thead><tbody>';

        $row_idx = 0;
        $pos_counter = 0;
        $prev_tot = null;

        foreach ($swimmers_arr as $sw) {
            $id_nad = intval($sw['id_nadadora']);
            $is_baja = ($sw['baja'] == 'si');

            $tot_calc = floatval($sw['nota_final_calculada'] ?? 0);
            if ($tot_calc == 0 && $total_gd > 0 && isset($scores_map[$id_nad])) {
                $sum_fin = 0;
                foreach ($figs_arr as $fg_item) {
                    $sc_it = $scores_map[$id_nad][intval($fg_item['id_fase'])] ?? null;
                    if ($sc_it && isset($sc_it['nota_final'])) {
                        $sum_fin += floatval($sc_it['nota_final']);
                    }
                }
                if ($sum_fin > 0) {
                    $tot_calc = ($sum_fin / $total_gd) * 10;
                }
            }

            if ($is_baja) {
                $pos_badge = '<span style="color:#ef4444; font-weight:bold;">BAJA</span>';
                $tot_cell = '<span style="color:#ef4444; font-weight:bold;">BAJA</span>';
                $dif_cell = '<span style="color:#94a3b8;">-</span>';
            } else {
                $pos = intval($sw['posicion'] ?? 0);
                if ($pos <= 0) {
                    if ($prev_tot !== null && abs($tot_calc - $prev_tot) < 0.0001) {
                        // Empate
                    } else {
                        $pos_counter++;
                    }
                    $pos = $pos_counter;
                    $prev_tot = $tot_calc;
                }
                $pos_badge = '<span class="tabular-nums" style="font-weight:bold; color:#0f172a;">' . $pos . 'º</span>';

                $tot_cell = '<span class="tabular-nums" style="font-weight:bold; font-size:8pt; color:#047857;">' . number_format($tot_calc, 4) . '</span>';
                
                $dif_val = floatval($sw['diferencia'] ?? 0);
                if ($pos == 1 || $dif_val == 0) {
                    $dif_cell = '<span class="tabular-nums" style="color:#64748b; font-size:7pt;">0.0000</span>';
                } else {
                    $dif_cell = '<span class="tabular-nums" style="color:#64748b; font-size:7pt;">+' . number_format($dif_val, 4) . '</span>';
                }
            }

            $bg_row = ($row_idx % 2 == 0) ? '#ffffff' : '#f8fafc';
            $html .= '<tr nobr="true" style="background-color:' . $bg_row . '; border-bottom:1px solid #e2e8f0;">';
            $html .= '<td width="' . $w_pos . '%" align="center">' . $pos_badge . '</td>';

            $nom_sw = htmlspecialchars($sw['apellidos'] . ', ' . $sw['nombre']);
            if (!empty($sw['año_nacimiento'])) {
                $nom_sw .= ' <span style="font-size:6.5pt; color:#94a3b8; font-weight:normal;">(' . $sw['año_nacimiento'] . ')</span>';
            }
            $html .= '<td width="' . $w_nad . '%"><b style="color:#0f172a; font-size:7.5pt;">' . $nom_sw . '</b></td>';
            $html .= '<td width="' . $w_club . '%" style="color:#475569; font-size:7.5pt;">' . htmlspecialchars($sw['club_nom'] ?? '') . '</td>';

            foreach ($figs_arr as $fg) {
                $sc = $scores_map[$id_nad][intval($fg['id_fase'])] ?? null;
                if ($is_baja || ($sc && isset($sc['baja']) && $sc['baja'] == 'si')) {
                    $cell_fig = '<span style="color:#ef4444; font-size:6.5pt; font-weight:bold;">BAJA</span>';
                } else {
                    $val_fin = ($sc && isset($sc['nota_final']) && $sc['nota_final'] !== '' && $sc['nota_final'] !== null) ? floatval($sc['nota_final']) : 0.0;
                    $cell_fig = '<span class="tabular-nums" style="font-weight:bold; color:#0f172a; font-size:8pt;">' . number_format($val_fin, 4) . '</span>';
                }
                $html .= '<td width="' . $w_fig_col . '%" align="center">' . $cell_fig . '</td>';
            }

            $html .= '<td width="' . $w_tot . '%" align="right">' . $tot_cell . '</td>';
            $html .= '<td width="' . $w_dif . '%" align="right">' . $dif_cell . '</td>';
            $html .= '</tr>';

            $row_idx++;
        }

        $html .= '</tbody></table>';
        $pdf->writeHTML($html, true, false, true, false, '');
    }
}

// -----------------------------------------------------------------------------
// HELPER 2: RENDERIZAR DETALLE DE UNA FIGURA INDIVIDUAL
// -----------------------------------------------------------------------------
if (!function_exists('render_puntuaciones_figura')) {
    function render_puntuaciones_figura($fase, $connection, $pdf, $puestos_oficiales_str) {
        $id_fase = intval($fase['id']);
        $nombre_categoria = $fase['nombre_categoria'];

        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 8);

        $num_fig = $fase['fig_num'] ?? '';
        $nom_fig = $fase['fig_nom'] ?? '';
        $gd_fig = !empty($fase['fig_gd']) ? number_format((float)$fase['fig_gd'], 1) : '0.0';
        $nombre_figura_title = "FIGURA " . $num_fig . " - " . $nom_fig;

        // Cabecera de la fase de figuras
        $html_fase = '<table cellpadding="4" style="border:1px solid #cbd5e1; background-color:#f8fafc; border-collapse:collapse; width:100%; margin-bottom:6px;">';
        $html_fase .= '<tr>';
        $html_fase .= '<td width="72%"><b style="font-size:11pt; color:#1e293b;">' . mb_strtoupper($nombre_figura_title, 'UTF-8') . '</b><br><span style="font-size:8.5pt; color:#64748b; font-weight:bold;">CATEGORÍA: ' . mb_strtoupper($nombre_categoria, 'UTF-8') . '</span></td>';
        $html_fase .= '<td width="28%" style="text-align:right; font-size:8pt; color:#64748b;">';
        $html_fase .= '<b>Grado Dificultad (GD): <span style="color:#0284c7; font-size:11pt;">' . $gd_fig . '</span></b>';
        $html_fase .= '</td>';
        $html_fase .= '</tr>';
        $html_fase .= '</table><br>';

        // Jueces del panel para esta figura
        $q_jf = "SELECT pj.id, pj.numero_juez, j.nombre, j.apellidos, f.nombre_corto as fed_nom 
                 FROM panel_jueces pj 
                 JOIN jueces j ON pj.id_juez = j.id 
                 LEFT JOIN federaciones f ON j.federacion = f.id 
                 WHERE pj.id_fase = $id_fase 
                 ORDER BY pj.numero_juez ASC";
        $res_jf = mysqli_query($connection, $q_jf);
        $jueces_fig = [];
        $jueces_fig_names = [];
        if ($res_jf) {
            while ($jf = mysqli_fetch_assoc($res_jf)) {
                $jueces_fig[] = $jf;
                $jueces_fig_names[] = 'J' . $jf['numero_juez'] . ': ' . $jf['nombre'] . ' ' . $jf['apellidos'];
            }
        }
        $num_jueces = count($jueces_fig);

        // Controladores Técnicos para figuras si los hubiera (id_paneles_tipo = 4)
        $q_ct_fig = "SELECT pj.numero_juez, j.nombre, j.apellidos FROM panel_jueces pj JOIN jueces j ON pj.id_juez = j.id WHERE pj.id_fase = $id_fase AND pj.id_panel IN (SELECT id FROM paneles WHERE id_paneles_tipo = 4 AND id_competicion = " . $GLOBALS['id_competicion_activa'] . ") ORDER BY pj.numero_juez ASC";
        $res_ct_fig = mysqli_query($connection, $q_ct_fig);
        $ct_fig_names = [];
        if ($res_ct_fig) {
            while ($j = mysqli_fetch_assoc($res_ct_fig)) {
                $ct_fig_names[$j['numero_juez']] = 'CT' . $j['numero_juez'] . ': ' . $j['nombre'] . ' ' . $j['apellidos'];
            }
        }

        if (!empty($jueces_fig_names) || !empty($puestos_oficiales_str) || !empty($ct_fig_names)) {
            $html_fase .= '<table cellpadding="2" style="font-size:6.5pt; color:#475569; background-color:#ffffff; border:1px solid #e2e8f0; width:100%; margin-bottom:6px;">';
            if (!empty($puestos_oficiales_str)) {
                $html_fase .= '<tr><td width="15%"><b style="color:#0f172a;">Oficiales:</b></td><td width="85%">' . $puestos_oficiales_str . '</td></tr>';
            }
            if (!empty($ct_fig_names)) {
                $html_fase .= '<tr><td width="15%"><b style="color:#d97706;">CT:</b></td><td width="85%">' . implode(' &nbsp;·&nbsp; ', $ct_fig_names) . '</td></tr>';
            }
            if (!empty($jueces_fig_names)) {
                $html_fase .= '<tr><td width="15%"><b style="color:#e92662;">Panel Jueces:</b></td><td width="85%">' . implode(' &nbsp;·&nbsp; ', $jueces_fig_names) . '</td></tr>';
            }
            $html_fase .= '</table><br>';
        }

        $pdf->writeHTML($html_fase, true, false, true, false, '');

        // Tabla de puntuaciones de la figura
        $html_fig = '<table cellpadding="3" style="border:1px solid #cbd5e1; border-collapse:collapse; width:100%; font-size:7.5pt;">';
        $html_fig .= '<thead><tr style="background-color:#0f172a; color:#ffffff; font-weight:bold;">';
        $html_fig .= '<th width="5%" align="center">POS</th>';
        $html_fig .= '<th width="32%">NADADORA</th>';
        $html_fig .= '<th width="16%">CLUB</th>';

        $ancho_jueces_total = 22;
        $ancho_j_col = ($num_jueces > 0) ? floor($ancho_jueces_total / $num_jueces) : 4;
        foreach ($jueces_fig as $jf) {
            $html_fig .= '<th width="' . $ancho_j_col . '%" align="center">J' . $jf['numero_juez'] . '</th>';
        }
        $ancho_jueces_usado = $ancho_j_col * $num_jueces;
        $resto_ancho = $ancho_jueces_total - $ancho_jueces_usado;

        $html_fig .= '<th width="6%" align="center">MEDIA</th>';
        $html_fig .= '<th width="6%" align="center">PEN.</th>';
        $html_fig .= '<th width="7%" align="right">FINAL</th>';
        $html_fig .= '<th width="' . (6 + $resto_ancho) . '%" align="right">DIF</th>';
        $html_fig .= '</tr></thead><tbody>';

        // Obtener fase base de la categoría para dorsal de sorteo oficial
        $q_fase_base = "SELECT id FROM fases 
                        WHERE id_competicion = (SELECT id_competicion FROM fases WHERE id = $id_fase) 
                          AND id_categoria = (SELECT id_categoria FROM fases WHERE id = $id_fase) 
                          AND id_figura > 0 
                        ORDER BY orden ASC LIMIT 1";
        $res_fase_base = mysqli_query($connection, $q_fase_base);
        $id_fase_base = $res_fase_base ? (int)mysqli_fetch_assoc($res_fase_base)['id'] : $id_fase;

        $q_insc_fig = "SELECT i.*, n.nombre, n.apellidos, n.año_nacimiento, cl.nombre_corto as club_nom,
                              COALESCE(
                                  (SELECT ifb.orden FROM inscripciones_figuras ifb WHERE ifb.id_fase = $id_fase_base AND ifb.id_nadadora = i.id_nadadora LIMIT 1),
                                  i.orden
                              ) as dorsal_base,
                              COALESCE(
                                  (SELECT SUM(ABS(p.puntos)) 
                                   FROM penalizaciones_rutinas pr 
                                   JOIN penalizaciones p ON pr.id_penalizacion = p.id 
                                   WHERE pr.id_inscripcion_figuras = i.id), 
                                  0
                              ) as penalizacion_puntos,
                              (SELECT GROUP_CONCAT(p.codigo SEPARATOR ' ') 
                               FROM penalizaciones_rutinas pr 
                               JOIN penalizaciones p ON pr.id_penalizacion = p.id 
                               WHERE pr.id_inscripcion_figuras = i.id) as penalizacion_codigos
                       FROM inscripciones_figuras i 
                       JOIN nadadoras n ON i.id_nadadora = n.id 
                       LEFT JOIN clubes cl ON n.club = cl.id 
                       WHERE i.id_fase = $id_fase 
                       ORDER BY (i.baja = 'si') ASC, (i.orden < 1) ASC, 
                                (i.nota_final IS NULL OR i.nota_final = 0) ASC, 
                                (i.nota_final - COALESCE((SELECT SUM(ABS(p.puntos)) FROM penalizaciones_rutinas pr JOIN penalizaciones p ON pr.id_penalizacion = p.id WHERE pr.id_inscripcion_figuras = i.id), 0)) DESC, 
                                i.orden ASC";
        $res_insc_fig = mysqli_query($connection, $q_insc_fig);
        $row_idx = 0;
        $top_nota = null;
        $prev_fin_float = null;
        $actual_pos = 0;

        if ($res_insc_fig) {
            while ($inf = mysqli_fetch_assoc($res_insc_fig)) {
                $id_inf = (int)$inf['id'];
                $is_baja = ($inf['baja'] == 'si');
                $is_ps = ($inf['preswimmer'] == 'si' || (int)$inf['orden'] < 1);
                $has_nota = ($inf['nota_final'] !== null && $inf['nota_final'] !== '') || ($inf['nota_total'] !== null && $inf['nota_total'] !== '');
                $pen_pts = floatval($inf['penalizacion_puntos'] ?? 0);
                $pen_cods = trim($inf['penalizacion_codigos'] ?? '');

                if ($has_nota) {
                    $med_val = ((float)$inf['nota_media'] == 0) ? '0' : number_format((float)$inf['nota_media'], 4);
                    $fin_float = max(0, (float)$inf['nota_final'] - $pen_pts);
                    $fin_val = ($fin_float == 0) ? '0' : number_format($fin_float, 4);

                    if (!$is_baja && !$is_ps) {
                        if ($prev_fin_float !== null && abs($fin_float - $prev_fin_float) < 0.0001) {
                            // Empate correlativo
                        } else {
                            $actual_pos++;
                        }
                        $prev_fin_float = $fin_float;

                        if ($top_nota === null) {
                            $top_nota = $fin_float;
                        }
                        $dif = max(0, $top_nota - $fin_float);
                        $dif_val = number_format($dif, 4);
                        $pos_badge = $actual_pos;
                    } else {
                        $dif_val = '-';
                        $pos_badge = $is_baja ? '<span style="color:#ef4444; font-weight:bold;">BAJA</span>' : '<span style="color:#3b82f6; font-weight:bold;">PS</span>';
                    }
                } else {
                    $med_val = '-';
                    $fin_val = '-';
                    $dif_val = '-';
                    $pos_badge = $is_baja ? '<span style="color:#ef4444; font-weight:bold;">BAJA</span>' : ($is_ps ? '<span style="color:#3b82f6; font-weight:bold;">PS</span>' : '-');
                }

                if ($pen_pts > 0) {
                    $pen_cell = '<b style="color:#e11d48;">-' . number_format($pen_pts, 1) . '</b>';
                    if (!empty($pen_cods)) {
                        $pen_cell .= '<br><span style="font-size:6pt; color:#be123c;">' . htmlspecialchars($pen_cods) . '</span>';
                    }
                } else {
                    $pen_cell = '<span style="color:#94a3b8;">-</span>';
                }

                $bg_row = ($row_idx % 2 == 0) ? '#ffffff' : '#f8fafc';
                $html_fig .= '<tr style="background-color:' . $bg_row . '; border-bottom:1px solid #e2e8f0;">';
                $html_fig .= '<td width="5%" align="center" style="font-weight:bold; color:#1e293b;">' . $pos_badge . '</td>';
                
                $nom_swimmer = htmlspecialchars($inf['apellidos'] . ', ' . $inf['nombre']) . (!empty($inf['año_nacimiento']) ? ' (' . $inf['año_nacimiento'] . ')' : '');
                $html_fig .= '<td width="32%" style="font-weight:bold; color:#1e293b;">' . $nom_swimmer . '</td>';
                $html_fig .= '<td width="16%" style="color:#475569;">' . htmlspecialchars($inf['club_nom'] ?? '') . '</td>';

                // Notas de jueces
                foreach ($jueces_fig as $jf) {
                    $q_nj = "SELECT nota, nota_menor, nota_mayor FROM puntuaciones_jueces WHERE id_panel_juez = " . (int)$jf['id'] . " AND id_inscripcion_figuras = $id_inf LIMIT 1";
                    $r_nj = mysqli_query($connection, $q_nj);
                    $d_nj = $r_nj ? mysqli_fetch_assoc($r_nj) : null;

                    if ($d_nj && isset($d_nj['nota']) && $d_nj['nota'] !== '') {
                        $nota_val = number_format((float)$d_nj['nota'], 1);
                        $is_min = (!empty($d_nj['nota_menor']) && $d_nj['nota_menor'] != '0');
                        $is_max = (!empty($d_nj['nota_mayor']) && $d_nj['nota_mayor'] != '0');

                        if ($is_min || $is_max) {
                            $cell_nota = '<span style="text-decoration:line-through; color:#94a3b8; font-weight:bold;">' . $nota_val . '</span>';
                        } else {
                            $cell_nota = '<span style="color:#0f172a; font-weight:bold;">' . $nota_val . '</span>';
                        }
                    } else {
                        $cell_nota = '<span style="color:#cbd5e1;">-</span>';
                    }
                    $html_fig .= '<td width="' . $ancho_j_col . '%" align="center">' . $cell_nota . '</td>';
                }

                $html_fig .= '<td width="6%" align="center" style="color:#64748b; font-size:7pt;">' . $med_val . '</td>';
                $html_fig .= '<td width="6%" align="center" style="font-size:7pt;">' . $pen_cell . '</td>';
                $html_fig .= '<td width="7%" align="right" style="font-weight:bold; color:#059669; font-size:8.5pt;">' . $fin_val . '</td>';
                $html_fig .= '<td width="' . (6 + $resto_ancho) . '%" align="right" style="color:#64748b; font-size:7pt;">' . $dif_val . '</td>';
                $html_fig .= '</tr>';

                $row_idx++;
            }
        }

        if ($row_idx === 0) {
            $html_fig .= '<tr><td colspan="' . (7 + $num_jueces) . '" align="center" style="color:#94a3b8; padding:12px;">Sin inscripciones registradas en esta fase de figuras.</td></tr>';
        }

        $html_fig .= '</tbody></table><br>';
        $pdf->writeHTML($html_fig, true, false, true, false, '');
    }
}

// -----------------------------------------------------------------------------
// HELPER 3: RENDERIZAR FASE DE RUTINAS
// -----------------------------------------------------------------------------
if (!function_exists('render_puntuaciones_rutina')) {
    function render_puntuaciones_rutina($fase, $connection, $pdf, $puestos_oficiales_str) {
        $id_fase = intval($fase['id']);
        $nombre_categoria = $fase['nombre_categoria'];

        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 8);

        $nombre_modalidad = ($fase['nombre_modalidad'] ?? 'Rutina') . (($fase['genero'] ?? '') === 'masculino' ? ' Masc' : '');

        // Cabecera de la fase
        $html_fase = '<table cellpadding="3" style="border:1px solid #cbd5e1; background-color:#f8fafc; border-collapse:collapse; width:100%; margin-bottom:5px;">';
        $html_fase .= '<tr>';
        $html_fase .= '<td width="100%"><b style="font-size:11pt; color:#1e293b;">' . mb_strtoupper($nombre_modalidad . ' ' . $nombre_categoria, 'UTF-8') . '</b></td>';
        $html_fase .= '</tr>';
        $html_fase .= '</table>';

        // Jueces del panel para esta fase
        $q_j1 = "SELECT pj.numero_juez, j.nombre, j.apellidos FROM panel_jueces pj JOIN jueces j ON pj.id_juez = j.id WHERE pj.id_fase = $id_fase AND pj.id_panel IN (SELECT id FROM paneles WHERE id_paneles_tipo = 1 AND id_competicion = " . $GLOBALS['id_competicion_activa'] . ") ORDER BY pj.numero_juez ASC";
        $res_j1 = mysqli_query($connection, $q_j1);
        $jueces1_names = [];
        if ($res_j1) {
            while ($j = mysqli_fetch_assoc($res_j1)) {
                $jueces1_names[$j['numero_juez']] = 'J' . $j['numero_juez'] . ': ' . $j['nombre'] . ' ' . $j['apellidos'];
            }
        }

        $q_j2 = "SELECT pj.numero_juez, j.nombre, j.apellidos FROM panel_jueces pj JOIN jueces j ON pj.id_juez = j.id WHERE pj.id_fase = $id_fase AND pj.id_panel IN (SELECT id FROM paneles WHERE id_paneles_tipo = 2 AND id_competicion = " . $GLOBALS['id_competicion_activa'] . ") ORDER BY pj.numero_juez ASC";
        $res_j2 = mysqli_query($connection, $q_j2);
        $jueces2_names = [];
        if ($res_j2) {
            while ($j = mysqli_fetch_assoc($res_j2)) {
                $jueces2_names[$j['numero_juez']] = 'J' . $j['numero_juez'] . ': ' . $j['nombre'] . ' ' . $j['apellidos'];
            }
        }

        // Jueces de Sincronización (id_paneles_tipo = 3)
        $q_js = "SELECT pj.numero_juez, j.nombre, j.apellidos FROM panel_jueces pj JOIN jueces j ON pj.id_juez = j.id WHERE pj.id_fase = $id_fase AND pj.id_panel IN (SELECT id FROM paneles WHERE id_paneles_tipo = 3 AND id_competicion = " . $GLOBALS['id_competicion_activa'] . ") ORDER BY pj.numero_juez ASC";
        $res_js = mysqli_query($connection, $q_js);
        $jueces_sinc_names = [];
        if ($res_js) {
            while ($j = mysqli_fetch_assoc($res_js)) {
                $jueces_sinc_names[$j['numero_juez']] = 'J' . $j['numero_juez'] . ': ' . $j['nombre'] . ' ' . $j['apellidos'];
            }
        }

        // Controladores Técnicos (id_paneles_tipo = 4)
        $q_ct = "SELECT pj.numero_juez, j.nombre, j.apellidos FROM panel_jueces pj JOIN jueces j ON pj.id_juez = j.id WHERE pj.id_fase = $id_fase AND pj.id_panel IN (SELECT id FROM paneles WHERE id_paneles_tipo = 4 AND id_competicion = " . $GLOBALS['id_competicion_activa'] . ") ORDER BY pj.numero_juez ASC";
        $res_ct = mysqli_query($connection, $q_ct);
        $ct_names = [];
        if ($res_ct) {
            while ($j = mysqli_fetch_assoc($res_ct)) {
                $ct_names[$j['numero_juez']] = 'CT' . $j['numero_juez'] . ': ' . $j['nombre'] . ' ' . $j['apellidos'];
            }
        }

        // Orden de jueces: Oficiales, CT, Sincro, Paneles
        $html_fase .= '<table cellpadding="2" style="font-size:6.5pt; color:#475569; background-color:#ffffff; border:1px solid #e2e8f0; width:100%; margin-bottom:6px;">';
        if (!empty($puestos_oficiales_str)) {
            $html_fase .= '<tr><td width="15%"><b style="color:#0f172a;">Oficiales:</b></td><td width="85%">' . $puestos_oficiales_str . '</td></tr>';
        }
        if (!empty($ct_names)) {
            $html_fase .= '<tr><td width="15%"><b style="color:#d97706;">CT:</b></td><td width="85%">' . implode(' &nbsp;·&nbsp; ', $ct_names) . '</td></tr>';
        }
        if (!empty($jueces_sinc_names)) {
            $html_fase .= '<tr><td width="15%"><b style="color:#9333ea;">Sincro:</b></td><td width="85%">' . implode(' &nbsp;·&nbsp; ', $jueces_sinc_names) . '</td></tr>';
        }
        if (!empty($jueces1_names)) {
            $html_fase .= '<tr><td width="15%"><b style="color:#0284c7;">P1 (Elementos):</b></td><td width="85%">' . implode(' &nbsp;·&nbsp; ', $jueces1_names) . '</td></tr>';
        }
        if (!empty($jueces2_names)) {
            $html_fase .= '<tr><td width="15%"><b style="color:#059669;">P2 (Imp. Artística):</b></td><td width="85%">' . implode(' &nbsp;·&nbsp; ', $jueces2_names) . '</td></tr>';
        }
        $html_fase .= '</table>';

        $pdf->writeHTML($html_fase, true, false, true, false, '');

        // Listado de rutinas ordenadas por nota final
        $q_rutinas = "SELECT r.*, c.nombre_corto as nombre_club 
                      FROM rutinas r 
                      JOIN clubes c ON r.id_club = c.id 
                      WHERE r.id_fase = $id_fase 
                      ORDER BY (r.baja = 'si') ASC, (r.orden < 1) ASC, r.nota_final DESC, r.orden ASC";
        $res_rutinas = mysqli_query($connection, $q_rutinas);

        if ($res_rutinas) {
            while ($rutina = mysqli_fetch_assoc($res_rutinas)) {
                $id_rutina = intval($rutina['id']);
                $baja = ($rutina['baja'] == 'si');

                // Participantes
                $q_part = "SELECT n.nombre, n.apellidos, YEAR(n.año_nacimiento) as anio, rp.reserva 
                           FROM rutinas_participantes rp 
                           JOIN nadadoras n ON rp.id_nadadora = n.id 
                           WHERE rp.id_rutina = $id_rutina 
                           ORDER BY rp.reserva ASC, n.apellidos ASC";
                $res_part = mysqli_query($connection, $q_part);
                $part_arr = [];
                if ($res_part) {
                    while ($p = mysqli_fetch_assoc($res_part)) {
                        $r_mark = ($p['reserva'] == 'si') ? ' <span style="color:#94a3b8; font-size:6pt;">(R)</span>' : '';
                        $part_arr[] = $p['apellidos'] . ', ' . $p['nombre'] . $r_mark;
                    }
                }
                $participantes_str = implode('; ', $part_arr);

                // Posición
                $pos_badge = $rutina['posicion'] ?? '-';
                if ($rutina['orden'] == -1) $pos_badge = 'PS';
                elseif ($rutina['orden'] == -2) $pos_badge = 'EX';
                elseif ($baja) $pos_badge = 'BAJA';

                // Rutina HTML con nobr="true" para evitar cortes
                $html_r = '<table nobr="true" cellpadding="2.5" style="border:1px solid #cbd5e1; border-collapse:collapse; width:100%; margin-bottom:5px;">';

                // CABECERA RUTINA
                $html_r .= '<tr style="background-color:#0f172a; color:#ffffff;">';
                $html_r .= '<td width="7%" align="center" style="font-size:10pt; font-weight:bold; color:#38bdf8;">' . $pos_badge . '</td>';
                $html_r .= '<td width="53%">';
                $html_r .= '<b style="font-size:9pt;">' . htmlspecialchars($rutina['nombre_club']) . '</b>';
                if (!empty($rutina['nombre'])) {
                    $html_r .= ' <span style="color:#94a3b8; font-size:8pt; font-style:italic;">"' . htmlspecialchars($rutina['nombre']) . '"</span>';
                }
                $html_r .= '<br><span style="font-size:6.5pt; color:#cbd5e1;">' . $participantes_str . '</span>';
                $html_r .= '</td>';
                $html_r .= '<td width="22%" align="right" style="font-size:7.5pt; color:#94a3b8;">';
                if (!$baja) {
                    $html_r .= 'Elem: ' . number_format($rutina['nota_final_panel_elementos'] ?? 0, 4) . '<br>';
                    $html_r .= 'IA: ' . number_format($rutina['nota_final_panel_ia'] ?? 0, 4);
                }
                $html_r .= '</td>';
                $html_r .= '<td width="18%" align="right" style="font-size:11pt; font-weight:bold; color:#34d399;">';
                if ($baja) {
                    $html_r .= '<span style="color:#f87171; font-size:9pt;">BAJA</span>';
                } else {
                    $html_r .= number_format($rutina['nota_final'] ?? 0, 4);
                    $dif_val = floatval($rutina['diferencia'] ?? 0);
                    if ($dif_val > 0) {
                        $html_r .= '<br><span style="font-size:6.5pt; color:#94a3b8; font-weight:normal;">Dif: +' . number_format($dif_val, 4) . '</span>';
                    }
                }
                $html_r .= '</td>';
                $html_r .= '</tr>';

                if (!$baja) {
                    // DETALLE ELEMENTOS
                    $html_r .= '<tr style="background-color:#f1f5f9; font-size:6.5pt; font-weight:bold; color:#475569;">';
                    $html_r .= '<td colspan="4">';
                    $html_r .= '<table cellpadding="2" style="width:100%; border-collapse:collapse;">';
                    $html_r .= '<tr style="color:#64748b; border-bottom:1px solid #cbd5e1;">';
                    $html_r .= '<th width="12%">ELEMENTO</th>';
                    $html_r .= '<th width="12%" align="center">DD / BM</th>';
                    $html_r .= '<th width="8%" align="center">FACTOR</th>';
                    $html_r .= '<th width="44%">NOTAS JUECES (P1)</th>';
                    $html_r .= '<th width="10%" align="center">MEDIA</th>';
                    $html_r .= '<th width="14%" align="right">PUNTOS</th>';
                    $html_r .= '</tr>';

                    // Elementos de la rutina
                    $q_elem_r = "SELECT h.elemento, t.nombre as tipo_nombre, t.color, pe.llevado_BM, pe.nota_media, pe.factor, pe.nota 
                                 FROM hibridos_rutina h 
                                 JOIN tipo_hibridos t ON h.texto = t.id 
                                 LEFT JOIN puntuaciones_elementos pe ON (pe.id_rutina = $id_rutina AND pe.elemento = h.elemento AND pe.tipo_ia IS NULL) 
                                 WHERE h.id_rutina = $id_rutina AND h.tipo = 'part' AND h.texto != '3' 
                                 ORDER BY h.elemento ASC";
                    $res_elem_r = mysqli_query($connection, $q_elem_r);

                    if ($res_elem_r) {
                        while ($el = mysqli_fetch_assoc($res_elem_r)) {
                            $elem_num = intval($el['elemento']);
                            $is_bm = ($el['llevado_BM'] === 'si');

                            // DD o BM valor
                            if ($is_bm) {
                                $q_bm_val = mysqli_query($connection, "SELECT valor FROM hibridos_rutina WHERE tipo = 'basemark' AND id_rutina = $id_rutina AND elemento = $elem_num");
                                $val_dif = $q_bm_val ? floatval(mysqli_fetch_assoc($q_bm_val)['valor'] ?? 0.5) : 0.5;
                                $dif_badge = '<span style="color:#dc2626; font-weight:bold;">BM: ' . number_format($val_dif, 2) . '</span>';
                            } else {
                                $q_dd_val = mysqli_query($connection, "SELECT valor FROM hibridos_rutina WHERE tipo = 'total' AND id_rutina = $id_rutina AND elemento = $elem_num");
                                $val_dif = $q_dd_val ? floatval(mysqli_fetch_assoc($q_dd_val)['valor'] ?? 0) : 0;
                                $dif_badge = 'DD: ' . number_format($val_dif, 3);
                            }

                            // Notas jueces
                            $q_jn = "SELECT pj.nota, pj.nota_menor, pj.nota_mayor 
                                     FROM puntuaciones_jueces pj 
                                     WHERE pj.id_rutina = $id_rutina AND pj.id_elemento = $elem_num AND pj.tipo_ia IS NULL 
                                     ORDER BY pj.id_panel_juez ASC";
                            $res_jn = mysqli_query($connection, $q_jn);
                            $notas_j_str = [];
                            if ($res_jn) {
                                while ($jn = mysqli_fetch_assoc($res_jn)) {
                                    $n_fmt = number_format(floatval($jn['nota']), 2);
                                    $es_desc = ($jn['nota_menor'] === 'si' || $jn['nota_menor'] === '1' || $jn['nota_mayor'] === 'si' || $jn['nota_mayor'] === '1');
                                    if ($es_desc) {
                                        $notas_j_str[] = '<span style="text-decoration:line-through; color:#94a3b8;">' . $n_fmt . '</span>';
                                    } else {
                                        $notas_j_str[] = '<span style="color:#0f172a; font-weight:bold;">' . $n_fmt . '</span>';
                                    }
                                }
                            }

                            $puntos_elem = floatval($el['nota'] ?? 0);
                            $bg_el = ($elem_num % 2 == 0) ? '#ffffff' : '#f8fafc';

                            $html_r .= '<tr style="border-bottom:1px solid #f1f5f9; background-color:' . $bg_el . ';">';
                            $html_r .= '<td><b>#' . $elem_num . ' ' . htmlspecialchars($el['tipo_nombre']) . '</b></td>';
                            $html_r .= '<td align="center">' . $dif_badge . '</td>';
                            $html_r .= '<td align="center">' . number_format(floatval($el['factor'] ?? 1.0), 2) . '</td>';
                            $html_r .= '<td>' . (!empty($notas_j_str) ? implode('  ', $notas_j_str) : '-') . '</td>';
                            $html_r .= '<td align="center">' . number_format(floatval($el['nota_media'] ?? 0), 2) . '</td>';
                            $html_r .= '<td align="right" style="font-weight:bold; color:#0284c7;">' . number_format($puntos_elem, 4) . '</td>';
                            $html_r .= '</tr>';
                        }
                    }

                    // Sincronización
                    $pen_sinc_val = floatval($rutina['penalizacion_sincro'] ?? 0);
                    if ($pen_sinc_val != 0) {
                        $html_r .= '<tr style="background-color:#faf5ff; border-bottom:1px solid #e9d5ff;">';
                        $html_r .= '<td colspan="5"><b style="color:#9333ea;">Errores de Sincronización</b></td>';
                        $html_r .= '<td align="right" style="font-weight:bold; color:#9333ea;">-' . number_format(abs($pen_sinc_val), 4) . '</td>';
                        $html_r .= '</tr>';
                    }

                    // Subtotal Elementos
                    $html_r .= '<tr style="background-color:#eff6ff; border-bottom:1px solid #bfdbfe;">';
                    $html_r .= '<td colspan="5" align="right"><b style="color:#1d4ed8; text-transform:uppercase;">Total Panel Elementos:</b></td>';
                    $html_r .= '<td align="right" style="font-size:7.5pt; font-weight:bold; color:#1d4ed8;">' . number_format(floatval($rutina['nota_final_panel_elementos'] ?? 0), 4) . '</td>';
                    $html_r .= '</tr>';

                    // DETALLE IMPRESIÓN ARTÍSTICA
                    $html_r .= '<tr style="color:#64748b; border-bottom:1px solid #cbd5e1; background-color:#f8fafc;">';
                    $html_r .= '<th colspan="2">IMPRESIÓN ARTÍSTICA</th>';
                    $html_r .= '<th align="center">FACTOR</th>';
                    $html_r .= '<th>NOTAS JUECES (P2)</th>';
                    $html_r .= '<th align="center">SUMA C.</th>';
                    $html_r .= '<th align="right">PUNTOS</th>';
                    $html_r .= '</tr>';

                    $ia_components = [
                        'chomu' => ['label' => 'Coreografía y Música', 'factor' => $fase['f_chomu']],
                        'performance' => ['label' => 'Performance', 'factor' => $fase['f_performance']],
                        'transitions' => ['label' => 'Transiciones', 'factor' => $fase['f_transitions']]
                    ];

                    foreach ($ia_components as $tipo_ia_key => $ia_meta) {
                        $q_ia_j = "SELECT pj.nota, pj.nota_menor, pj.nota_mayor 
                                   FROM puntuaciones_jueces pj 
                                   WHERE pj.id_rutina = $id_rutina AND pj.tipo_ia = '$tipo_ia_key' 
                                   ORDER BY pj.id_panel_juez ASC";
                        $res_ia_j = mysqli_query($connection, $q_ia_j);
                        $notas_ia_str = [];
                        $sum_c_ia = 0;
                        if ($res_ia_j) {
                            while ($iaj = mysqli_fetch_assoc($res_ia_j)) {
                                $n_fmt = number_format(floatval($iaj['nota']), 2);
                                $es_desc = ($iaj['nota_menor'] === 'si' || $iaj['nota_menor'] === '1' || $iaj['nota_mayor'] === 'si' || $iaj['nota_mayor'] === '1');
                                if ($es_desc) {
                                    $notas_ia_str[] = '<span style="text-decoration:line-through; color:#94a3b8;">' . $n_fmt . '</span>';
                                } else {
                                    $notas_ia_str[] = '<span style="color:#0f172a; font-weight:bold;">' . $n_fmt . '</span>';
                                    $sum_c_ia += floatval($iaj['nota']);
                                }
                            }
                        }

                        $q_pe_ia = mysqli_query($connection, "SELECT nota FROM puntuaciones_elementos WHERE id_rutina = $id_rutina AND tipo_ia = '$tipo_ia_key' LIMIT 1");
                        $nota_ia_val = $q_pe_ia ? floatval(mysqli_fetch_assoc($q_pe_ia)['nota'] ?? 0) : 0;

                        $html_r .= '<tr style="border-bottom:1px solid #f1f5f9; background-color:#ffffff;">';
                        $html_r .= '<td colspan="2"><b>' . htmlspecialchars($ia_meta['label']) . '</b></td>';
                        $html_r .= '<td align="center">' . number_format(floatval($ia_meta['factor']), 2) . '</td>';
                        $html_r .= '<td>' . (!empty($notas_ia_str) ? implode('  ', $notas_ia_str) : '-') . '</td>';
                        $html_r .= '<td align="center" style="color:#64748b;">∑ ' . number_format($sum_c_ia, 2) . '</td>';
                        $html_r .= '<td align="right" style="font-weight:bold; color:#059669;">' . number_format($nota_ia_val, 4) . '</td>';
                        $html_r .= '</tr>';
                    }

                    // Penalizaciones IA
                    $pen_ia_val = floatval($rutina['penalizaciones_ia'] ?? 0);
                    if ($pen_ia_val != 0) {
                        $html_r .= '<tr style="background-color:#fef2f2; border-bottom:1px solid #fee2e2;">';
                        $html_r .= '<td colspan="5"><b style="color:#b91c1c;">Penalizaciones Impresión Artística</b></td>';
                        $html_r .= '<td align="right" style="font-weight:bold; color:#b91c1c;">' . number_format($pen_ia_val, 4) . '</td>';
                        $html_r .= '</tr>';
                    }

                    // Subtotal IA
                    $html_r .= '<tr style="background-color:#ecfdf5; border-bottom:1px solid #a7f3d0;">';
                    $html_r .= '<td colspan="5" align="right"><b style="color:#047857; text-transform:uppercase;">Total Panel Impresión Artística:</b></td>';
                    $html_r .= '<td align="right" style="font-size:7.5pt; font-weight:bold; color:#047857;">' . number_format(floatval($rutina['nota_final_panel_ia'] ?? 0), 4) . '</td>';
                    $html_r .= '</tr>';

                    // Penalizaciones Rutina / Árbitro
                    $pen_rut_val = floatval($rutina['penalizaciones_rutina'] ?? 0);
                    if ($pen_rut_val != 0) {
                        $q_pens_txt = mysqli_query($connection, "SELECT p.codigo, p.puntos FROM penalizaciones_rutinas pr JOIN penalizaciones p ON pr.id_penalizacion = p.id WHERE pr.id_rutina = $id_rutina AND (p.id_paneles_tipo = 0 OR p.id_paneles_tipo IS NULL)");
                        $p_txt_arr = [];
                        if ($q_pens_txt) {
                            while ($pt = mysqli_fetch_assoc($q_pens_txt)) {
                                $p_txt_arr[] = $pt['codigo'] . ' (' . $pt['puntos'] . ')';
                            }
                        }
                        $cod_str = !empty($p_txt_arr) ? ' [' . implode(', ', $p_txt_arr) . ']' : '';

                        $html_r .= '<tr style="background-color:#fff1f2; border-bottom:1px solid #fecdd3;">';
                        $html_r .= '<td colspan="5"><b style="color:#e11d48;">Penalizaciones de Rutina / Juez Árbitro' . $cod_str . '</b></td>';
                        $html_r .= '<td align="right" style="font-weight:bold; color:#e11d48;">' . number_format($pen_rut_val, 4) . '</td>';
                        $html_r .= '</tr>';
                    }

                    $html_r .= '</table>';
                    $html_r .= '</td>';
                    $html_r .= '</tr>';
                }

                $html_r .= '</table>';
                $pdf->writeHTML($html_r, true, false, true, false, '');
            }
        }
    }
}

// -----------------------------------------------------------------------------
// ENRUTAMIENTO Y EJECUCIÓN PRINCIPAL
// -----------------------------------------------------------------------------
if ($id_fase_param > 0) {
    // Modo fase individual
    $q_fase = "SELECT f.*, 
                      cat.nombre as nombre_categoria, 
                      cat.id as id_categoria, 
                      m.nombre as nombre_modalidad, 
                      m.id as id_modalidad,
                      fig.numero as fig_num,
                      fig.nombre as fig_nom,
                      fig.grado_dificultad as fig_gd,
                      COALESCE(NULLIF(f.f_chomu, 0), rc.f_chomu, 1.0) as f_chomu, 
                      COALESCE(NULLIF(f.f_performance, 0), rc.f_performance, 1.0) as f_performance, 
                      COALESCE(NULLIF(f.f_transitions, 0), rc.f_transitions, 1.0) as f_transitions, 
                      COALESCE(NULLIF(f.f_hybrid, 0), rc.f_hybrid, 1.0) as f_hybrid, 
                      COALESCE(NULLIF(f.f_acro, 0), rc.f_acro, 1.0) as f_acro, 
                      COALESCE(NULLIF(f.f_tre, 0), rc.f_tre, 1.0) as f_tre,
                      COALESCE(NULLIF(f.error_xs, 0), rc.error_xs, -0.1) as error_xs,
                      COALESCE(NULLIF(f.error_ob, 0), rc.error_ob, -0.5) as error_ob,
                      COALESCE(NULLIF(f.error_xl, 0), rc.error_xl, -3.0) as error_xl
               FROM fases f 
               JOIN categorias cat ON f.id_categoria = cat.id 
               LEFT JOIN modalidades m ON f.id_modalidad = m.id 
               LEFT JOIN figuras fig ON f.id_figura = fig.id
               LEFT JOIN reglas_competicion rc ON (f.id_categoria = rc.id_categoria AND f.id_modalidad = rc.id_modalidad)
               WHERE f.id = $id_fase_param LIMIT 1";
    $res_fase = mysqli_query($connection, $q_fase);
    if ($res_fase && $fase = mysqli_fetch_assoc($res_fase)) {
        if (!empty($fase['id_figura']) && (int)$fase['id_figura'] > 0) {
            render_puntuaciones_figura($fase, $connection, $pdf, $puestos_oficiales_str);
        } else {
            render_puntuaciones_rutina($fase, $connection, $pdf, $puestos_oficiales_str);
        }
    }
} else {
    // =========================================================================
    // MODO GLOBAL COMPLETO (Estructurado en 3 bloques oficiales)
    // =========================================================================

    // -------------------------------------------------------------------------
    // BLOQUE 1: RESUMEN DE FIGURAS POR CATEGORÍA (Alevín, Infantil...)
    // -------------------------------------------------------------------------
    $q_cats_fig = "SELECT DISTINCT c.id, c.nombre, c.orden 
                   FROM fases f 
                   JOIN categorias c ON f.id_categoria = c.id 
                   WHERE f.id_competicion = '" . $GLOBALS['id_competicion_activa'] . "' AND f.id_figura > 0 
                   ORDER BY c.orden ASC, c.id ASC";
    $res_cats_fig = mysqli_query($connection, $q_cats_fig);
    if ($res_cats_fig) {
        while ($cat_fig = mysqli_fetch_assoc($res_cats_fig)) {
            render_resumen_figuras_categoria($cat_fig, $connection, $pdf);
        }
    }

    // -------------------------------------------------------------------------
    // BLOQUE 2: CLASIFICACIÓN DETALLADA POR FIGURA INDIVIDUAL (Alevín e Infantil)
    // -------------------------------------------------------------------------
    $q_fases_fig = "SELECT f.*, 
                           cat.nombre as nombre_categoria, 
                           cat.id as id_categoria, 
                           fig.numero as fig_num, 
                           fig.nombre as fig_nom, 
                           fig.grado_dificultad as fig_gd 
                    FROM fases f 
                    JOIN categorias cat ON f.id_categoria = cat.id 
                    LEFT JOIN figuras fig ON f.id_figura = fig.id 
                    WHERE f.id_competicion = '" . $GLOBALS['id_competicion_activa'] . "' AND f.id_figura > 0 
                    ORDER BY cat.orden ASC, cat.id ASC, f.orden ASC, f.id ASC";
    $res_fases_fig = mysqli_query($connection, $q_fases_fig);
    if ($res_fases_fig) {
        while ($fase_fig = mysqli_fetch_assoc($res_fases_fig)) {
            $id_f = intval($fase_fig['id']);
            $cnt_q = mysqli_query($connection, "SELECT COUNT(*) FROM inscripciones_figuras WHERE id_fase = $id_f");
            $cnt = ($cnt_q && mysqli_num_rows($cnt_q) > 0) ? intval(mysqli_fetch_row($cnt_q)[0]) : 0;
            if ($cnt > 0) {
                render_puntuaciones_figura($fase_fig, $connection, $pdf, $puestos_oficiales_str);
            }
        }
    }

    // -------------------------------------------------------------------------
    // BLOQUE 3: RUTINAS TÉCNICAS (Solos, Dúos, Equipos...)
    // -------------------------------------------------------------------------
    $q_fases_rut = "SELECT f.*, 
                           cat.nombre as nombre_categoria, 
                           cat.id as id_categoria, 
                           m.nombre as nombre_modalidad, 
                           m.id as id_modalidad, 
                           COALESCE(NULLIF(f.f_chomu, 0), rc.f_chomu, 1.0) as f_chomu, 
                           COALESCE(NULLIF(f.f_performance, 0), rc.f_performance, 1.0) as f_performance, 
                           COALESCE(NULLIF(f.f_transitions, 0), rc.f_transitions, 1.0) as f_transitions, 
                           COALESCE(NULLIF(f.f_hybrid, 0), rc.f_hybrid, 1.0) as f_hybrid, 
                           COALESCE(NULLIF(f.f_acro, 0), rc.f_acro, 1.0) as f_acro, 
                           COALESCE(NULLIF(f.f_tre, 0), rc.f_tre, 1.0) as f_tre, 
                           COALESCE(NULLIF(f.error_xs, 0), rc.error_xs, -0.1) as error_xs, 
                           COALESCE(NULLIF(f.error_ob, 0), rc.error_ob, -0.5) as error_ob, 
                           COALESCE(NULLIF(f.error_xl, 0), rc.error_xl, -3.0) as error_xl 
                    FROM fases f 
                    JOIN categorias cat ON f.id_categoria = cat.id 
                    LEFT JOIN modalidades m ON f.id_modalidad = m.id 
                    LEFT JOIN reglas_competicion rc ON (f.id_categoria = rc.id_categoria AND f.id_modalidad = rc.id_modalidad) 
                    WHERE f.id_competicion = '" . $GLOBALS['id_competicion_activa'] . "' AND (f.id_figura IS NULL OR f.id_figura = 0) 
                    ORDER BY cat.orden ASC, f.id_modalidad ASC, f.orden ASC, f.id ASC";
    $res_fases_rut = mysqli_query($connection, $q_fases_rut);
    if ($res_fases_rut) {
        while ($fase_rut = mysqli_fetch_assoc($res_fases_rut)) {
            $id_r = intval($fase_rut['id']);
            $cnt_q = mysqli_query($connection, "SELECT COUNT(*) FROM rutinas WHERE id_fase = $id_r");
            $cnt = ($cnt_q && mysqli_num_rows($cnt_q) > 0) ? intval(mysqli_fetch_row($cnt_q)[0]) : 0;
            if ($cnt > 0) {
                render_puntuaciones_rutina($fase_rut, $connection, $pdf, $puestos_oficiales_str);
            }
        }
    }
}

$dest = $_GET['dest'] ?? 'I';
$output_target = ($dest === 'F' && !empty($_GET['save_path'])) ? $_GET['save_path'] : $nombre_documento;
$pdf->Output($output_target, $dest);
exit();
