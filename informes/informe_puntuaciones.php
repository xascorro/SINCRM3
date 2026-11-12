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
            $this->SetY(24.5);
        } else {
            $this->SetY(8);
        }

        $this->SetFont('helvetica', 'B', 13);
        $this->SetTextColor(233, 38, 98);
        $this->Cell(0, 5, $GLOBALS['titulo_documento'], 0, 1, 'C');

        $this->SetFont('helvetica', 'B', 8.5);
        $this->SetTextColor(100, 116, 139);
        $this->Cell(0, 3.5, mb_strtoupper($GLOBALS['subtitulo_documento'], 'UTF-8'), 0, 1, 'C');

        // Línea divisoria suave
        $this->SetDrawColor(226, 232, 240);
        $this->SetLineWidth(0.3);
        $this->Line(12, $this->GetY() + 1.2, 198, $this->GetY() + 1.2);
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

$pdf->SetMargins(12, 38, 12);
$pdf->SetHeaderMargin(6);
$pdf->SetFooterMargin(15);
$pdf->SetAutoPagebreak(TRUE, 22);
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
if (isset($_GET['id_fase']) && intval($_GET['id_fase']) > 0) {
    $condicion_fase = " AND f.id = " . intval($_GET['id_fase']);
}

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

$q_fases = "SELECT f.*, 
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
            WHERE f.id_competicion = '" . $GLOBALS['id_competicion_activa'] . "' $condicion_fase 
            ORDER BY f.orden ASC, f.id ASC";
$res_fases = mysqli_query($connection, $q_fases);

while ($fase = mysqli_fetch_assoc($res_fases)) {
    $id_fase = intval($fase['id']);
    $is_figura = (!empty($fase['id_figura']) && (int)$fase['id_figura'] > 0);
    $nombre_categoria = $fase['nombre_categoria'];

    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 8);

    if ($is_figura) {
        // ---------------------------------------------------------------------
        // FASE DE FIGURAS
        // ---------------------------------------------------------------------
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
        while ($jf = mysqli_fetch_assoc($res_jf)) {
            $jueces_fig[] = $jf;
            $jueces_fig_names[] = 'J' . $jf['numero_juez'] . ': ' . $jf['nombre'] . ' ' . $jf['apellidos'];
        }
        $num_jueces = count($jueces_fig);

        if (!empty($jueces_fig_names) || !empty($puestos_oficiales_str)) {
            $html_fase .= '<table cellpadding="2.5" style="font-size:6.5pt; color:#475569; background-color:#ffffff; border:1px solid #e2e8f0; width:100%; margin-bottom:8px;">';
            if (!empty($puestos_oficiales_str)) {
                $html_fase .= '<tr><td width="15%"><b style="color:#0f172a;">Oficiales:</b></td><td width="85%">' . $puestos_oficiales_str . '</td></tr>';
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
        $html_fig .= '<th width="5%" align="center">ORD</th>';
        $html_fig .= '<th width="28%">NADADORA</th>';
        $html_fig .= '<th width="14%">CLUB</th>';

        $ancho_jueces_total = 24;
        $ancho_j_col = ($num_jueces > 0) ? floor($ancho_jueces_total / $num_jueces) : 5;
        foreach ($jueces_fig as $jf) {
            $html_fig .= '<th width="' . $ancho_j_col . '%" align="center">J' . $jf['numero_juez'] . '</th>';
        }
        $ancho_jueces_usado = $ancho_j_col * $num_jueces;
        $resto_ancho = $ancho_jueces_total - $ancho_jueces_usado;

        $html_fig .= '<th width="' . (6 + $resto_ancho) . '%" align="center">SUM</th>';
        $html_fig .= '<th width="6%" align="center">MEDIA</th>';
        $html_fig .= '<th width="6%" align="center">GD</th>';
        $html_fig .= '<th width="6%" align="right">FINAL</th>';
        $html_fig .= '</tr></thead><tbody>';

        $q_insc_fig = "SELECT i.*, n.nombre, n.apellidos, n.año_nacimiento, cl.nombre_corto as club_nom 
                       FROM inscripciones_figuras i 
                       JOIN nadadoras n ON i.id_nadadora = n.id 
                       LEFT JOIN clubes cl ON n.club = cl.id 
                       WHERE i.id_fase = $id_fase 
                       ORDER BY (i.baja = 'si') ASC, (i.orden < 1) ASC, 
                                (CASE WHEN i.posicion IS NULL OR i.posicion = 0 THEN 9999 ELSE i.posicion END) ASC, 
                                (i.nota_final IS NULL OR i.nota_final = 0) ASC, 
                                i.nota_final DESC, 
                                i.orden ASC";
        $res_insc_fig = mysqli_query($connection, $q_insc_fig);
        $row_idx = 0;

        while ($inf = mysqli_fetch_assoc($res_insc_fig)) {
            $id_inf = (int)$inf['id'];
            $is_baja = ($inf['baja'] == 'si');
            $is_ps = ($inf['preswimmer'] == 'si' || (int)$inf['orden'] < 1);

            $pos_badge = !empty($inf['posicion']) ? ($inf['posicion'] . 'º') : '-';
            if ($is_baja) $pos_badge = '<span style="color:#ef4444; font-weight:bold;">BAJA</span>';
            elseif ($is_ps) $pos_badge = '<span style="color:#3b82f6; font-weight:bold;">PS</span>';

            $bg_row = ($row_idx % 2 == 0) ? '#ffffff' : '#f8fafc';
            $html_fig .= '<tr style="background-color:' . $bg_row . '; border-bottom:1px solid #e2e8f0;">';
            $html_fig .= '<td width="5%" align="center" style="font-weight:bold; color:#1e293b;">' . $pos_badge . '</td>';
            $html_fig .= '<td width="5%" align="center" style="color:#64748b;">#' . $inf['orden'] . '</td>';
            
            $nom_swimmer = htmlspecialchars($inf['apellidos'] . ', ' . $inf['nombre']) . (!empty($inf['año_nacimiento']) ? ' (' . $inf['año_nacimiento'] . ')' : '');
            $html_fig .= '<td width="28%" style="font-weight:bold; color:#1e293b;">' . $nom_swimmer . '</td>';
            $html_fig .= '<td width="14%" style="color:#475569;">' . htmlspecialchars($inf['club_nom'] ?? '') . '</td>';

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

            $sum_val = (!empty($inf['nota_total']) && (float)$inf['nota_total'] > 0) ? number_format((float)$inf['nota_total'], 1) : '-';
            $med_val = (!empty($inf['nota_media']) && (float)$inf['nota_media'] > 0) ? number_format((float)$inf['nota_media'], 4) : '-';
            $fin_val = (!empty($inf['nota_final']) && (float)$inf['nota_final'] > 0) ? number_format((float)$inf['nota_final'], 4) : '-';

            $html_fig .= '<td width="' . (6 + $resto_ancho) . '%" align="center" style="color:#64748b; font-size:7pt;">' . $sum_val . '</td>';
            $html_fig .= '<td width="6%" align="center" style="color:#64748b; font-size:7pt;">' . $med_val . '</td>';
            $html_fig .= '<td width="6%" align="center" style="color:#0284c7; font-weight:bold; font-size:7pt;">' . $gd_fig . '</td>';
            $html_fig .= '<td width="6%" align="right" style="font-weight:bold; color:#059669; font-size:8.5pt;">' . $fin_val . '</td>';
            $html_fig .= '</tr>';

            $row_idx++;
        }

        if ($row_idx === 0) {
            $html_fig .= '<tr><td colspan="' . (8 + $num_jueces) . '" align="center" style="color:#94a3b8; padding:12px;">Sin inscripciones registradas en esta fase de figuras.</td></tr>';
        }

        $html_fig .= '</tbody></table><br>';
        $pdf->writeHTML($html_fig, true, false, true, false, '');

    } else {
        // ---------------------------------------------------------------------
        // FASE DE RUTINAS
        // ---------------------------------------------------------------------
        $nombre_modalidad = ($fase['nombre_modalidad'] ?? 'Rutina') . (($fase['genero'] ?? '') === 'masculino' ? ' Masc' : '');

        // Cabecera de la fase
        $html_fase = '<table cellpadding="4" style="border:1px solid #cbd5e1; background-color:#f8fafc; border-collapse:collapse; width:100%; margin-bottom:6px;">';
        $html_fase .= '<tr>';
        $html_fase .= '<td width="70%"><b style="font-size:11pt; color:#1e293b;">' . mb_strtoupper($nombre_modalidad . ' ' . $nombre_categoria, 'UTF-8') . '</b></td>';
        $html_fase .= '<td width="30%" style="text-align:right; font-size:7pt; color:#64748b;">';
        $html_fase .= 'Factores: H:' . $fase['f_hybrid'] . ' | A:' . $fase['f_acro'] . ' | CM:' . $fase['f_chomu'] . ' | P:' . $fase['f_performance'] . ' | T:' . $fase['f_transitions'];
        $html_fase .= '</td>';
        $html_fase .= '</tr>';
        $html_fase .= '</table><br>';

        // Jueces del panel para esta fase
        $q_j1 = "SELECT pj.numero_juez, j.nombre, j.apellidos FROM panel_jueces pj JOIN jueces j ON pj.id_juez = j.id WHERE pj.id_fase = $id_fase AND pj.id_panel IN (SELECT id FROM paneles WHERE id_paneles_tipo = 1 AND id_competicion = " . $GLOBALS['id_competicion_activa'] . ") ORDER BY pj.numero_juez ASC";
        $res_j1 = mysqli_query($connection, $q_j1);
        $jueces1_names = [];
        while ($j = mysqli_fetch_assoc($res_j1)) {
            $jueces1_names[$j['numero_juez']] = 'J' . $j['numero_juez'] . ': ' . $j['nombre'] . ' ' . $j['apellidos'];
        }

        $q_j2 = "SELECT pj.numero_juez, j.nombre, j.apellidos FROM panel_jueces pj JOIN jueces j ON pj.id_juez = j.id WHERE pj.id_fase = $id_fase AND pj.id_panel IN (SELECT id FROM paneles WHERE id_paneles_tipo = 2 AND id_competicion = " . $GLOBALS['id_competicion_activa'] . ") ORDER BY pj.numero_juez ASC";
        $res_j2 = mysqli_query($connection, $q_j2);
        $jueces2_names = [];
        while ($j = mysqli_fetch_assoc($res_j2)) {
            $jueces2_names[$j['numero_juez']] = 'J' . $j['numero_juez'] . ': ' . $j['nombre'] . ' ' . $j['apellidos'];
        }

        // Jueces de Sincronización (id_paneles_tipo = 3)
        $q_js = "SELECT pj.numero_juez, j.nombre, j.apellidos FROM panel_jueces pj JOIN jueces j ON pj.id_juez = j.id WHERE pj.id_fase = $id_fase AND pj.id_panel IN (SELECT id FROM paneles WHERE id_paneles_tipo = 3 AND id_competicion = " . $GLOBALS['id_competicion_activa'] . ") ORDER BY pj.numero_juez ASC";
        $res_js = mysqli_query($connection, $q_js);
        $jueces_sinc_names = [];
        while ($j = mysqli_fetch_assoc($res_js)) {
            $jueces_sinc_names[$j['numero_juez']] = 'J' . $j['numero_juez'] . ': ' . $j['nombre'] . ' ' . $j['apellidos'];
        }

        // Controladores Técnicos (id_paneles_tipo = 4)
        $q_ct = "SELECT pj.numero_juez, j.nombre, j.apellidos FROM panel_jueces pj JOIN jueces j ON pj.id_juez = j.id WHERE pj.id_fase = $id_fase AND pj.id_panel IN (SELECT id FROM paneles WHERE id_paneles_tipo = 4 AND id_competicion = " . $GLOBALS['id_competicion_activa'] . ") ORDER BY pj.numero_juez ASC";
        $res_ct = mysqli_query($connection, $q_ct);
        $ct_names = [];
        while ($j = mysqli_fetch_assoc($res_ct)) {
            $ct_names[$j['numero_juez']] = 'CT' . $j['numero_juez'] . ': ' . $j['nombre'] . ' ' . $j['apellidos'];
        }

        $html_fase .= '<table cellpadding="2.5" style="font-size:6.5pt; color:#475569; background-color:#ffffff; border:1px solid #e2e8f0; width:100%; margin-bottom:8px;">';
        if (!empty($puestos_oficiales_str)) {
            $html_fase .= '<tr><td width="15%"><b style="color:#0f172a;">Oficiales:</b></td><td width="85%">' . $puestos_oficiales_str . '</td></tr>';
        }
        if (!empty($jueces1_names)) {
            $html_fase .= '<tr><td width="15%"><b style="color:#0284c7;">P1 (Elementos):</b></td><td width="85%">' . implode(' &nbsp;·&nbsp; ', $jueces1_names) . '</td></tr>';
        }
        if (!empty($jueces2_names)) {
            $html_fase .= '<tr><td width="15%"><b style="color:#059669;">P2 (Imp. Artística):</b></td><td width="85%">' . implode(' &nbsp;·&nbsp; ', $jueces2_names) . '</td></tr>';
        }
        if (!empty($jueces_sinc_names)) {
            $html_fase .= '<tr><td width="15%"><b style="color:#9333ea;">Sincronización:</b></td><td width="85%">' . implode(' &nbsp;·&nbsp; ', $jueces_sinc_names) . '</td></tr>';
        }
        if (!empty($ct_names)) {
            $html_fase .= '<tr><td width="15%"><b style="color:#d97706;">Control Técnicos:</b></td><td width="85%">' . implode(' &nbsp;·&nbsp; ', $ct_names) . '</td></tr>';
        }
        $html_fase .= '</table><br>';

        $pdf->writeHTML($html_fase, true, false, true, false, '');

        // Listado de rutinas ordenadas por nota final
        $q_rutinas = "SELECT r.*, c.nombre_corto as nombre_club 
                      FROM rutinas r 
                      JOIN clubes c ON r.id_club = c.id 
                      WHERE r.id_fase = $id_fase 
                      ORDER BY (r.baja = 'si') ASC, (r.orden < 1) ASC, r.nota_final DESC, r.orden ASC";
        $res_rutinas = mysqli_query($connection, $q_rutinas);

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
        while ($p = mysqli_fetch_assoc($res_part)) {
            $r_mark = ($p['reserva'] == 'si') ? ' <span style="color:#94a3b8; font-size:6pt;">(R)</span>' : '';
            $part_arr[] = $p['apellidos'] . ', ' . $p['nombre'] . $r_mark;
        }
        $participantes_str = implode('; ', $part_arr);

        // Posición
        $pos_badge = $rutina['posicion'] ?? '-';
        if ($rutina['orden'] == -1) $pos_badge = 'PS';
        elseif ($rutina['orden'] == -2) $pos_badge = 'EX';
        elseif ($baja) $pos_badge = 'BAJA';

        // Rutina HTML con nobr="true" para evitar cortes
        $html_r = '<table nobr="true" cellpadding="3" style="border:1px solid #cbd5e1; border-collapse:collapse; width:100%; margin-bottom:8px;">';

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
                while ($jn = mysqli_fetch_assoc($res_jn)) {
                    $n_fmt = number_format(floatval($jn['nota']), 2);
                    $es_desc = ($jn['nota_menor'] === 'si' || $jn['nota_menor'] === '1' || $jn['nota_mayor'] === 'si' || $jn['nota_mayor'] === '1');
                    if ($es_desc) {
                        $notas_j_str[] = '<span style="text-decoration:line-through; color:#94a3b8;">' . $n_fmt . '</span>';
                    } else {
                        $notas_j_str[] = '<span style="color:#0f172a; font-weight:bold;">' . $n_fmt . '</span>';
                    }
                }
                $str_jueces = !empty($notas_j_str) ? implode('  ', $notas_j_str) : '-';

                $html_r .= '<tr style="border-bottom:1px solid #f1f5f9; background-color:#ffffff;">';
                $html_r .= '<td><b>' . htmlspecialchars($el['tipo_nombre']) . ' #' . $elem_num . '</b></td>';
                $html_r .= '<td align="center">' . $dif_badge . '</td>';
                $html_r .= '<td align="center">' . number_format(floatval($el['factor'] ?? 1), 2) . '</td>';
                $html_r .= '<td>' . $str_jueces . '</td>';
                $html_r .= '<td align="center" style="color:#64748b;">' . number_format(floatval($el['nota_media'] ?? 0), 4) . '</td>';
                $html_r .= '<td align="right" style="font-weight:bold; color:#0284c7;">' . number_format(floatval($el['nota'] ?? 0), 4) . '</td>';
                $html_r .= '</tr>';
            }

            // Sincronización
            if ($fase['id_modalidad'] != 1 && $fase['id_modalidad'] != 5) {
                $q_sinc = mysqli_query($connection, "SELECT * FROM errores_sincronizacion WHERE id_rutina = $id_rutina");
                $sinc = $q_sinc ? mysqli_fetch_assoc($q_sinc) : null;
                $p_sinc = floatval($sinc['errores_pequenos'] ?? 0);
                $o_sinc = floatval($sinc['errores_obvios'] ?? 0);
                $m_sinc = floatval($sinc['errores_mayores'] ?? 0);
                $p_sinc_txt = ($p_sinc == intval($p_sinc)) ? intval($p_sinc) : number_format($p_sinc, 1);
                $o_sinc_txt = ($o_sinc == intval($o_sinc)) ? intval($o_sinc) : number_format($o_sinc, 1);
                $m_sinc_txt = ($m_sinc == intval($m_sinc)) ? intval($m_sinc) : number_format($m_sinc, 1);
                $ded_sinc = floatval($rutina['nota_panel_sincro'] ?? 0);

                $html_r .= '<tr style="background-color:#fef2f2; border-bottom:1px solid #fee2e2;">';
                $html_r .= '<td colspan="3"><b style="color:#b91c1c;">Sincronización</b></td>';
                $html_r .= '<td style="color:#7f1d1d; font-size:6pt;">Pequeños: ' . $p_sinc_txt . ' (' . $fase['error_xs'] . ') · Obvios: ' . $o_sinc_txt . ' (' . $fase['error_ob'] . ') · Mayores: ' . $m_sinc_txt . ' (' . $fase['error_xl'] . ')</td>';
                $html_r .= '<td align="center" style="color:#b91c1c;">Deducción</td>';
                $html_r .= '<td align="right" style="font-weight:bold; color:#b91c1c;">' . number_format($ded_sinc, 4) . '</td>';
                $html_r .= '</tr>';
            }

            // Penalizaciones Elementos
            $pen_elem_val = floatval($rutina['penalizaciones_elementos'] ?? 0);
            if ($pen_elem_val != 0) {
                $html_r .= '<tr style="background-color:#fef2f2; border-bottom:1px solid #fee2e2;">';
                $html_r .= '<td colspan="5"><b style="color:#b91c1c;">Penalizaciones Elementos</b></td>';
                $html_r .= '<td align="right" style="font-weight:bold; color:#b91c1c;">' . number_format($pen_elem_val, 4) . '</td>';
                $html_r .= '</tr>';
            }

            // Subtotal Elementos
            $html_r .= '<tr style="background-color:#e0f2fe; border-bottom:1px solid #bae6fd;">';
            $html_r .= '<td colspan="5" align="right"><b style="color:#0369a1; text-transform:uppercase;">Total Panel Elementos:</b></td>';
            $html_r .= '<td align="right" style="font-size:7.5pt; font-weight:bold; color:#0369a1;">' . number_format(floatval($rutina['nota_final_panel_elementos'] ?? 0), 4) . '</td>';
            $html_r .= '</tr>';

            // IMPRESIÓN ARTÍSTICA
            $ia_items = [
                'ChoMu' => ['label' => 'Choreography & Music', 'factor' => $fase['f_chomu']],
                'Performance' => ['label' => 'Performance', 'factor' => $fase['f_performance']],
                'Transitions' => ['label' => 'Transitions', 'factor' => $fase['f_transitions']]
            ];

            foreach ($ia_items as $k_ia => $ia_meta) {
                $q_pe_ia = mysqli_query($connection, "SELECT nota, factor FROM puntuaciones_elementos WHERE id_rutina = $id_rutina AND tipo_ia = '$k_ia'");
                $row_pe_ia = $q_pe_ia ? mysqli_fetch_assoc($q_pe_ia) : null;
                $nota_ia_val = floatval($row_pe_ia['nota'] ?? 0);

                $q_jn_ia = "SELECT pj.nota, pj.nota_menor, pj.nota_mayor 
                            FROM puntuaciones_jueces pj 
                            WHERE pj.id_rutina = $id_rutina AND pj.tipo_ia = '$k_ia' 
                            ORDER BY pj.id_panel_juez ASC";
                $res_jn_ia = mysqli_query($connection, $q_jn_ia);
                $notas_ia_str = [];
                $sum_c_ia = 0;
                while ($jn = mysqli_fetch_assoc($res_jn_ia)) {
                    $n_fmt = number_format(floatval($jn['nota']), 2);
                    $es_desc = ($jn['nota_menor'] === 'si' || $jn['nota_menor'] === '1' || $jn['nota_mayor'] === 'si' || $jn['nota_mayor'] === '1');
                    if ($es_desc) {
                        $notas_ia_str[] = '<span style="text-decoration:line-through; color:#94a3b8;">' . $n_fmt . '</span>';
                    } else {
                        $notas_ia_str[] = '<span style="color:#0f172a; font-weight:bold;">' . $n_fmt . '</span>';
                        $sum_c_ia += floatval($jn['nota']);
                    }
                }

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
                while ($pt = mysqli_fetch_assoc($q_pens_txt)) {
                    $p_txt_arr[] = $pt['codigo'] . ' (' . $pt['puntos'] . ')';
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

        $html_r .= '</table><br>';
        $pdf->writeHTML($html_r, true, false, true, false, '');
    }
}
}

$dest = $_GET['dest'] ?? 'I';
$output_target = ($dest === 'F' && !empty($_GET['save_path'])) ? $_GET['save_path'] : $nombre_documento;
$pdf->Output($output_target, $dest);
exit();
