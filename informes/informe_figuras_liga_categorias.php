<?php
// Desactivar display_errors para evitar que warnings de PHP corrompan el flujo binario del PDF
error_reporting(E_ALL);
ini_set('display_errors', 0);
setlocale(LC_ALL, 'es_ES');

require_once('../tcpdf/tcpdf.php');
include('../database/dbconfig.php');
include('../lib/my_functions.php');

session_start();

// Verificación de conexión a BD
if (!$connection) {
    die("Error de conexión a la base de datos.");
}

// 1. Verificación de sesión unificada o Token de Cron interno
$cron_token_expected = "internal_cron_" . md5("SincrmInternalCron" . date('Y-m-d'));
$cron_token_provided = $_GET['cron_token'] ?? '';
$is_authenticated = isset($_SESSION['email']) || isset($_SESSION['username']);

if (!$is_authenticated && $cron_token_provided !== $cron_token_expected) {
    header('Location: ../login.php');
    exit();
}

// Helper seguro para obtener un único valor
function safe_mysqli_result($conn, $query, $col = 0) {
    $res = mysqli_query($conn, $query);
    if ($res && mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_array($res);
        return $row[$col];
    }
    return false;
}

// 2. Obtener datos de la competición solicitada (o fallback a la activa)
$id_competicion = isset($_GET['id_competicion']) ? intval($_GET['id_competicion']) : (int)($_SESSION['id_competicion_activa'] ?? 0);
$query_comp = "SELECT * FROM competiciones WHERE id = '$id_competicion'";
$result_comp = mysqli_query($connection, $query_comp);
$comp = $result_comp ? mysqli_fetch_assoc($result_comp) : null;

if (!$comp) {
    $query_act = "SELECT * FROM competiciones WHERE activo = 'si' LIMIT 1";
    $result_act = mysqli_query($connection, $query_act);
    $comp = $result_act ? mysqli_fetch_assoc($result_act) : null;
}

if (!$comp) {
    die("Error: No se ha encontrado la competición.");
}

// 3. Preparación de variables globales de diseño y metadatos
$header_img = !empty($comp['header_informe']) ? '../' . ltrim($comp['header_informe'], '/') : '';
$footer_img = !empty($comp['footer_informe']) ? '../' . ltrim($comp['footer_informe'], '/') : '';

$GLOBALS['comp_data'] = [
    'id' => (int)$comp['id'],
    'nombre' => $comp['nombre'],
    'nombre_corto' => !empty($comp['nombre_corto']) ? $comp['nombre_corto'] : $comp['nombre'],
    'lugar' => $comp['lugar'] ?? '',
    'piscina' => $comp['piscina'] ?? '',
    'fecha' => dateAFecha($comp['fecha'] ?? ''),
    'organizador' => $comp['organizador'] ?? '',
    'organizador_tipo' => $comp['organizador_tipo'] ?? '',
    'header_image' => $header_img,
    'footer_image' => $footer_img,
    'clave_liga' => !empty($comp['clave_liga']) ? trim($comp['clave_liga']) : trim($comp['nombre']),
    'temporada' => $comp['temporada'] ?? '',
    'color' => $comp['color'] ?? '#3b82f6'
];

$titulo_param = $_GET['titulo'] ?? 'Clasificación General Liga';
$GLOBALS['titulo_doc'] = $titulo_param;
$GLOBALS['nombre_categoria'] = '';

$nombre_documento = 'Clasificacion_Liga_' . preg_replace('/[^A-Za-z0-9_]/', '_', $GLOBALS['comp_data']['clave_liga']) . '.pdf';

// 4. Clase TCPDF personalizada v4 para informes de Liga en vertical (Portrait)
class SINCRM_LIGA_PDF extends TCPDF {
    public $current_category_name = '';

    public function Header() {
        $comp = $GLOBALS['comp_data'];

        // Cabecera institucional
        if (!empty($comp['header_image']) && file_exists($comp['header_image'])) {
            $this->Image($comp['header_image'], 10, 5, 190, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
            $this->SetY(6.5);
        } else {
            $this->SetY(6.5);
        }

        // Título principal con rosa corporativo v4
        $this->SetFont('helvetica', 'B', 14);
        $this->SetTextColor(233, 38, 98); // #e92662
        $titulo_header = mb_strtoupper($GLOBALS['titulo_doc'] ?? 'CLASIFICACIÓN GENERAL LIGA', 'UTF-8');
        $this->Cell(0, 6, $titulo_header, 0, 1, 'C');

        // Subtítulo con temporada y clave de liga
        $this->SetFont('helvetica', 'B', 8);
        $this->SetTextColor(100, 116, 139); // Slate-500
        $sub = ($comp['temporada'] ? $comp['temporada'] . ' · ' : '') . $comp['clave_liga'] . ' de Natación Artística FNRM';
        $this->Cell(0, 4, mb_strtoupper($sub, 'UTF-8'), 0, 1, 'C');

        // Línea divisoria suave bajo la cabecera
        $this->SetDrawColor(226, 232, 240);
        $this->SetLineWidth(0.3);
        $this->Line(10, 24.5, 200, 24.5);
    }

    public function Footer() {
        $comp = $GLOBALS['comp_data'];

        // Imagen de pie de página a la izquierda
        if (!empty($comp['footer_image']) && file_exists($comp['footer_image'])) {
            $this->SetY(282);
            $this->Image($comp['footer_image'], 10, 282, 115, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }

        // Metadatos a la derecha en multilínea (requisito FNRM v4 - SIN SEDE para Ligas generales)
        $this->SetFont('helvetica', 'B', 7);
        $this->SetTextColor(71, 85, 105);
        $this->setCellPaddings(0, 0, 0, 0);

        $txt_fecha = "Fecha: " . trim($comp['fecha']);
        $txt_pag = "Página " . $this->PageNo() . " de " . $this->getAliasNbPages();

        $this->SetY(283);
        $this->MultiCell(70, 3.8, $txt_fecha, 0, 'R', false, 1, 130, '', true, 0, false, true, 3.8, 'M', true);
        $this->MultiCell(70, 3.8, $txt_pag, 0, 'R', false, 1, 130, '', true, 0, false, true, 3.8, 'M', true);
    }
}

// 5. Inicialización del documento PDF (Vertical / Portrait)
$pdf = new SINCRM_LIGA_PDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator('SINCRM v4.0');
$pdf->SetAuthor('Pedro Díaz');
$pdf->SetTitle($GLOBALS['titulo_doc'] . ' - ' . $GLOBALS['comp_data']['clave_liga']);
$pdf->SetSubject($GLOBALS['comp_data']['nombre']);
$pdf->SetKeywords('sincro, liga, figuras, clasificacion, fnrm');

$pdf->SetMargins(10, 26, 10);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(14);
$pdf->SetAutoPageBreak(TRUE, 18);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// 6. Obtener jornadas de la liga
$clave_liga = $GLOBALS['comp_data']['clave_liga'];
$query_jornadas = "SELECT id, nombre, nombre_corto, fecha, lugar, color FROM competiciones WHERE clave_liga LIKE '".mysqli_real_escape_string($connection, $clave_liga)."' ORDER BY fecha ASC, id ASC";
$res_jornadas_all = mysqli_query($connection, $query_jornadas);
$jornadas_liga_arr = [];
$jornadas_disputadas_count = 0;
$jornadas_con_resultados_map = [];

if ($res_jornadas_all) {
    while($rj = mysqli_fetch_assoc($res_jornadas_all)) {
        $j_id = (int)$rj['id'];
        // Verificar si esta jornada tiene resultados registrados
        $q_cnt = mysqli_query($connection, "SELECT COUNT(*) FROM resultados_figuras_categorias WHERE id_competicion = '$j_id'");
        $cnt_res = ($q_cnt && mysqli_num_rows($q_cnt) > 0) ? (int)mysqli_fetch_row($q_cnt)[0] : 0;
        
        $jornadas_con_resultados_map[$j_id] = $cnt_res;
        if ($cnt_res > 0) {
            $jornadas_disputadas_count++;
        }
        $jornadas_liga_arr[] = $rj;
    }
}

if (empty($jornadas_liga_arr)) {
    $jornadas_liga_arr[] = [
        'id' => $GLOBALS['comp_data']['id'],
        'nombre' => $GLOBALS['comp_data']['nombre'],
        'nombre_corto' => $GLOBALS['comp_data']['nombre_corto'],
        'fecha' => $comp['fecha'],
        'lugar' => $GLOBALS['comp_data']['lugar'],
        'color' => $GLOBALS['comp_data']['color']
    ];
    $jornadas_con_resultados_map[$GLOBALS['comp_data']['id']] = 0;
}
$numero_jornadas_liga = count($jornadas_liga_arr);

// 7. Categorías con resultados en la liga
$query_cats = "SELECT id_categoria FROM resultados_figuras_categorias WHERE id_competicion IN (SELECT id FROM competiciones WHERE clave_liga LIKE '".mysqli_real_escape_string($connection, $clave_liga)."') GROUP BY id_categoria ORDER BY id_categoria ASC";
$res_cats = mysqli_query($connection, $query_cats);
$categorias_liga = [];

if ($res_cats) {
    while($rc = mysqli_fetch_assoc($res_cats)) {
        $cid = (string)$rc['id_categoria'];
        // Unificación de categorías Alevín I (234) y Alevín II (235) en Alevín (241)
        if ($cid == '234' || $cid == '235') {
            $cid = '241';
        }
        if (!in_array($cid, $categorias_liga)) {
            $categorias_liga[] = $cid;
        }
    }
}

// 8. ESCENARIO A: Si aún NO existen resultados computados para esta liga (p. ej. Jornada 1 antes de puntuar)
if (empty($categorias_liga)) {
    $pdf->current_category_name = 'ESTADO DE LA LIGA';
    $pdf->AddPage('P');

    // Obtener información de categorías inscritas en la competición actual
    $q_cat_inscr = "SELECT c.id, c.nombre, COUNT(i.id) as num_inscr 
                    FROM fases f 
                    JOIN categorias c ON c.id = f.id_categoria 
                    LEFT JOIN inscripciones_figuras i ON i.id_fase = f.id 
                    WHERE f.id_competicion = '".$GLOBALS['comp_data']['id']."' 
                    GROUP BY c.id 
                    ORDER BY c.orden ASC, c.id ASC";
    $res_cat_inscr = mysqli_query($connection, $q_cat_inscr);
    $censo_categorias = [];
    if ($res_cat_inscr) {
        while($rci = mysqli_fetch_assoc($res_cat_inscr)) {
            $censo_categorias[] = $rci;
        }
    }

    $html = '
    <table cellpadding="6" cellspacing="0" width="100%" style="margin-top: 5px;">
        <tr>
            <td style="background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; color: #92400e;">
                <div style="font-size: 11pt; font-weight: bold; color: #b45309;">
                    CLASIFICACIÓN GENERAL EN ESPERA DE PUNTUACIONES
                </div>
                <div style="font-size: 8.5pt; line-height: 1.5; color: #78350f; margin-top: 3px;">
                    Actualmente no constan resultados oficiales computados para la <b>' . htmlspecialchars($clave_liga) . '</b>.<br>
                    La clasificación general acumulada por categorías y el cómputo del descarte de la peor jornada (según la Normativa FNRM Regla 9) se generarán y actualizarán de forma automática una vez se hayan registrado las notas y cerrado las actas de figuras de las jornadas disputadas.
                </div>
            </td>
        </tr>
    </table>
    <br><br>

    <table width="100%" cellpadding="4" cellspacing="0">
        <tr valign="top">
            <td width="55%">
                <div style="font-size: 10pt; font-weight: bold; color: #e92662; border-bottom: 2px solid #e92662; padding-bottom: 2px; margin-bottom: 4px;">
                    CALENDARIO OFICIAL DE JORNADAS DE LA LIGA
                </div>
                <table width="100%" cellpadding="4" style="border-collapse: collapse; font-size: 8pt; border: 1px solid #cbd5e1;">
                    <tr style="background-color: #0f172a; font-weight: bold; color: #ffffff;">
                        <th width="32%">Jornada</th>
                        <th width="24%">Fecha</th>
                        <th width="26%">Sede / Lugar</th>
                        <th width="18%" align="center">Estado</th>
                    </tr>';
    
    foreach ($jornadas_liga_arr as $jl) {
        $is_activa = ((int)$jl['id'] === $GLOBALS['comp_data']['id']);
        $has_res = ($jornadas_con_resultados_map[(int)$jl['id']] ?? 0) > 0;
        $bg_jl = $is_activa ? '#fef2f2' : '#ffffff';
        $color_badge = $has_res ? '#15803d' : ($is_activa ? '#e92662' : '#64748b');
        $estado_txt = $has_res ? 'Disputada' : ($is_activa ? 'En curso / Activa' : 'Programada');

        $html .= '<tr style="background-color: '.$bg_jl.'; border-bottom: 1px solid #e2e8f0;">
            <td style="font-weight: bold; color: #1e293b;">' . htmlspecialchars($jl['nombre']) . '</td>
            <td style="color: #475569;">' . dateAFecha($jl['fecha']) . '</td>
            <td style="color: #475569;">' . htmlspecialchars($jl['lugar']) . '</td>
            <td align="center" style="font-weight: bold; color: '.$color_badge.';">' . $estado_txt . '</td>
        </tr>';
    }

    $html .= '</table>
            </td>
            <td width="5%"></td>
            <td width="40%">
                <div style="font-size: 10pt; font-weight: bold; color: #e92662; border-bottom: 2px solid #e92662; padding-bottom: 2px; margin-bottom: 4px;">
                    CENSO DE CATEGORÍAS (Jornada Activa)
                </div>
                <table width="100%" cellpadding="4" style="border-collapse: collapse; font-size: 8pt; border: 1px solid #cbd5e1;">
                    <tr style="background-color: #0f172a; font-weight: bold; color: #ffffff;">
                        <th width="70%">Categoría</th>
                        <th width="30%" align="center">Inscripciones</th>
                    </tr>';

    if (!empty($censo_categorias)) {
        foreach ($censo_categorias as $cc) {
            $html .= '<tr style="border-bottom: 1px solid #e2e8f0;">
                <td style="font-weight: bold; color: #334155;">' . htmlspecialchars($cc['nombre']) . '</td>
                <td align="center" style="font-weight: bold; color: #e92662;">' . (int)$cc['num_inscr'] . '</td>
            </tr>';
        }
    } else {
        $html .= '<tr><td colspan="2" align="center" style="color: #94a3b8; padding: 8px;">Sin fases de figuras registradas</td></tr>';
    }

    $html .= '</table>
                <br>
                <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 6px 8px; border-radius: 4px; font-size: 7.5pt; color: #64748b; line-height: 1.4;">
                    <strong style="color: #e92662;">Normativa de Clasificación (FNRM - Punto 9):</strong><br>
                    • Puntúan las ' . max(1, $numero_jornadas_liga - 1) . ' mejores jornadas de las ' . $numero_jornadas_liga . ' disputadas.<br>
                    • Por no competir en una jornada se asigna 0.0000 pts (NP).<br>
                    • No se contempla el desempate en caso de puntuaciones idénticas (<em>ex aequo</em>).
                </div>
            </td>
        </tr>
    </table>';

    $pdf->writeHTML($html, true, false, true, false, '');

} else {
    // 9. ESCENARIO B: Existen resultados para generar la clasificación por categorías (y por año en Alevín/Infantil)
    $mejores_cnt = max(1, $numero_jornadas_liga - 1);

    // Función reutilizable para renderizar una tabla oficial de clasificación de Liga
    $render_tabla_liga = function($cat_id, $filtro_cat_sql, $nombre_cat_base, $filtro_ano = null, $id_modalidad = null, $nombre_modalidad = null) use (
        $connection, $pdf, $clave_liga, $jornadas_liga_arr, $jornadas_con_resultados_map, 
        $jornadas_disputadas_count, $numero_jornadas_liga, $mejores_cnt
    ) {
        if ($id_modalidad !== null) {
            $nombre_categoria_display = $nombre_cat_base . ' · ' . $nombre_modalidad;
            $subtitulo_banner = 'CLASIFICACIÓN OFICIAL DE LIGA · ' . mb_strtoupper($nombre_modalidad, 'UTF-8');
        } else {
            $nombre_categoria_display = $nombre_cat_base . ($filtro_ano ? ' · AÑO ' . $filtro_ano : '');
            $subtitulo_banner = $filtro_ano ? ('CLASIFICACIÓN POR AÑO DE NACIMIENTO (' . $filtro_ano . ')') : 'CLASIFICACIÓN GENERAL DE LA CATEGORÍA';
        }

        $pdf->current_category_name = $nombre_categoria_display;
        $pdf->AddPage('P');

        // Banner estilizado de Categoría SINCRM v4 (Vertical / Portrait)
        $html_cat_banner = '
        <table cellpadding="3" style="width:100%; border-collapse:collapse; margin-bottom:4px; background-color:#f8fafc; border-left:4px solid #e92662; border-top:1px solid #e2e8f0; border-right:1px solid #e2e8f0; border-bottom:1px solid #e2e8f0;">
            <tr>
                <td width="64%">
                    <span style="font-size:10pt; font-weight:bold; color:#0f172a; text-transform:uppercase;">CATEGORÍA: ' . htmlspecialchars($nombre_categoria_display) . '</span><br>
                    <span style="font-size:6.5pt; color:#64748b; font-weight:normal;">' . $subtitulo_banner . '</span>
                </td>
                <td width="36%" align="right">
                    <span style="font-size:7pt; font-weight:bold; color:#64748b;">NORMATIVA FNRM · MEJORES ' . $mejores_cnt . ' DE ' . $numero_jornadas_liga . ' JORNADAS</span>
                </td>
            </tr>
        </table>';
        $pdf->writeHTML($html_cat_banner, true, false, true, false, '');

        // Anchos de columna optimizados (Portrait 100% = 190mm)
        // POS: 6%, NADADORA: 34%, CLUB: 16%, TOTAL: 12% -> Queda 32% repartido entre jornadas
        $ancho_jornadas_total = 32;
        $num_jornadas_count = max(1, $numero_jornadas_liga);
        $ancho_jornada = floor($ancho_jornadas_total / $num_jornadas_count);
        $resto_jornadas = $ancho_jornadas_total - ($ancho_jornada * $num_jornadas_count);
        $ancho_nadadora = 34 + $resto_jornadas;

        $html = '<table cellpadding="2.5" style="width:100%; border:1px solid #cbd5e1; border-collapse:collapse; font-size:7.5pt;">';
        $html .= '<thead>';
        $html .= '<tr style="background-color:#0f172a; color:#ffffff; font-weight:bold;">';
        $html .= '<th width="6%" align="center">POS</th>';
        $html .= '<th width="' . $ancho_nadadora . '%">NADADORA</th>';
        $html .= '<th width="16%">CLUB</th>';

        foreach ($jornadas_liga_arr as $jl) {
            $j_id = (int)$jl['id'];
            $has_res = ($jornadas_con_resultados_map[$j_id] ?? 0) > 0;
            $j_badge_color = $has_res ? '#38bdf8' : '#94a3b8';
            $j_nom_corto = htmlspecialchars($jl['nombre_corto'] ?: $jl['nombre']);
            $j_fecha_corta = !empty($jl['fecha']) ? date('d/m', strtotime($jl['fecha'])) : '';

            $html .= '<th width="' . $ancho_jornada . '%" align="center">';
            $html .= '<span style="color:' . $j_badge_color . '; font-weight:bold; font-size:7.5pt;">' . $j_nom_corto . '</span>';
            if (!empty($j_fecha_corta)) {
                $html .= '<br><span style="color:#94a3b8; font-weight:normal; font-size:5.5pt;">' . $j_fecha_corta . '</span>';
            }
            $html .= '</th>';
        }

        $html .= '<th width="12%" align="center" style="background-color:#047857; color:#ffffff; font-weight:bold; font-size:8pt;">TOTAL</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        // Obtener nadadoras con resultados en esta categoría (y año o modalidad si aplica)
        $sql_ano_filtro = ($filtro_ano !== null) ? " AND año = '" . intval($filtro_ano) . "'" : "";
        $sql_mod_filtro = ($id_modalidad !== null) ? " AND id_modalidad = '" . intval($id_modalidad) . "'" : " AND (id_modalidad IS NULL OR id_modalidad = 0)";

        $query_nad = "SELECT DISTINCT id_nadadora 
                      FROM resultados_figuras_categorias 
                      WHERE {$filtro_cat_sql} {$sql_ano_filtro} {$sql_mod_filtro}
                        AND id_competicion IN (SELECT id FROM competiciones WHERE clave_liga LIKE '".mysqli_real_escape_string($connection, $clave_liga)."')";
        $res_nad = mysqli_query($connection, $query_nad);

        $clasificacion_nadadoras = [];
        $idx = 0;

        if ($res_nad) {
            while ($nad = mysqli_fetch_assoc($res_nad)) {
                $id_nad = (int)$nad['id_nadadora'];

                // Datos de la nadadora y club en una sola consulta
                $q_info = "SELECT n.nombre, n.apellidos, n.año_nacimiento, c.nombre AS nombre_club, c.nombre_corto as club_nom_corto 
                           FROM nadadoras n 
                           LEFT JOIN clubes c ON c.id = n.club 
                           WHERE n.id = '$id_nad'";
                $r_info = mysqli_query($connection, $q_info);
                $d_info = $r_info ? mysqli_fetch_assoc($r_info) : null;

                $nombre_completo = $d_info ? ($d_info['apellidos'] . ', ' . $d_info['nombre']) : 'Nadadora #' . $id_nad;
                $anio_nac = $d_info['año_nacimiento'] ?? '';
                $club_nom = !empty($d_info['club_nom_corto']) ? $d_info['club_nom_corto'] : ($d_info['nombre_club'] ?? 'Sin Club');

                $puntos_totales = 0.0;
                $scores_nadadora = [];
                $display_nadadora = [];

                foreach ($jornadas_liga_arr as $jornada_liga) {
                    $id_j = (int)$jornada_liga['id'];
                    $jornada_tiene_resultados = ($jornadas_con_resultados_map[$id_j] ?? 0) > 0;

                    // Consulta de nota de la nadadora en esta jornada y modalidad
                    $q_pts = "SELECT COALESCE(nota_final_calculada, puntos, 0) AS nota_real 
                              FROM resultados_figuras_categorias 
                              WHERE id_nadadora = '$id_nad' AND id_competicion = '$id_j' {$sql_mod_filtro} LIMIT 1";
                    $r_pts = mysqli_query($connection, $q_pts);

                    if ($r_pts && mysqli_num_rows($r_pts) > 0) {
                        $p_num = (float)mysqli_fetch_row($r_pts)[0];
                        $p_disp = ($p_num > 0) ? number_format($p_num, 4) : '0.0000';
                    } else {
                        if ($jornada_tiene_resultados) {
                            $p_num = 0.0000;
                            $p_disp = 'NP';
                        } else {
                            $p_num = 0.0000;
                            $p_disp = '-';
                        }
                    }

                    $scores_nadadora[$id_j] = $p_num;
                    $display_nadadora[$id_j] = $p_disp;
                    $puntos_totales += $p_num;
                }

                // Descarte de la peor jornada (Regla 9.2 FNRM)
                $jornada_descarte_id = null;
                $puntos_descarte = 0.0000;

                if ($jornadas_disputadas_count >= 2 && !isset($_GET['sin_descarte'])) {
                    $scores_disputadas = [];
                    foreach ($scores_nadadora as $id_j => $sc) {
                        if (($jornadas_con_resultados_map[$id_j] ?? 0) > 0) {
                            $scores_disputadas[$id_j] = $sc;
                        }
                    }

                    if (!empty($scores_disputadas)) {
                        $min_val = min($scores_disputadas);
                        $puntos_descarte = $min_val;

                        foreach ($scores_disputadas as $id_j => $sc) {
                            if (abs($sc - $min_val) < 0.0001) {
                                $jornada_descarte_id = $id_j;
                                break;
                            }
                        }
                    }
                }

                $puntos_final_calc = $puntos_totales - $puntos_descarte;
                if ($puntos_final_calc < 0) $puntos_final_calc = 0.0000;

                if ($puntos_totales > 0 || in_array('NP', $display_nadadora)) {
                    $clasificacion_nadadoras[$idx] = [
                        'nombre_raw' => $nombre_completo,
                        'anio_raw' => $anio_nac,
                        'nombre_club' => $club_nom,
                        'puntos_clasificacion' => $puntos_final_calc,
                        'scores_display' => [],
                        'total_format' => number_format($puntos_final_calc, 4)
                    ];

                    foreach ($jornadas_liga_arr as $jl) {
                        $id_j = (int)$jl['id'];
                        $is_descarte = ($jornada_descarte_id !== null && $id_j === $jornada_descarte_id);
                        $clasificacion_nadadoras[$idx]['scores_display'][$id_j] = [
                            'val' => $display_nadadora[$id_j],
                            'color' => $jl['color'] ?? '#ffffff',
                            'descarte' => $is_descarte
                        ];
                    }
                    $idx++;
                }
            }
        }

        // Ordenación de nadadoras por puntuación final descendente
        usort($clasificacion_nadadoras, function($a, $b) {
            $diff = (float)$b['puntos_clasificacion'] - (float)$a['puntos_clasificacion'];
            if (abs($diff) < 0.0001) return 0;
            return ($diff > 0) ? 1 : -1;
        });

        // Generar filas con ranking denso correlativo
        $posicion = 0;
        $puntos_anterior = -1;
        $row_count = 0;

        foreach ($clasificacion_nadadoras as $cn) {
            $pts = $cn['puntos_clasificacion'];
            if ($puntos_anterior === -1 || abs($pts - $puntos_anterior) > 0.0001) {
                $posicion++;
                $puntos_anterior = $pts;
            }

            $pos_style = 'color:#1e293b; font-weight:bold; font-size:8pt;';

            $bg = ($row_count % 2 === 0) ? '#ffffff' : '#f8fafc';
            $html .= '<tr style="background-color: '.$bg.'; border-bottom:1px solid #e2e8f0;">';
            $html .= '<td width="6%" align="center" style="' . $pos_style . '">' . $posicion . '</td>';
            $html .= '<td width="' . $ancho_nadadora . '%"><b style="color:#0f172a; font-size:7.5pt;">' . htmlspecialchars($cn['nombre_raw']) . '</b>' . ($cn['anio_raw'] ? ' <span style="color:#64748b; font-size:6.5pt; font-weight:normal;">(' . $cn['anio_raw'] . ')</span>' : '') . '</td>';
            $html .= '<td width="16%" style="color:#475569; font-size:7pt; font-weight:500;">' . htmlspecialchars($cn['nombre_club']) . '</td>';

            foreach ($cn['scores_display'] as $sd) {
                if ($sd['descarte']) {
                    $cell_val = '<span style="text-decoration:line-through; color:#94a3b8; font-size:6.5pt;">' . $sd['val'] . '</span><br><span style="font-size:4.5pt; color:#cbd5e1; text-transform:uppercase;">(desc)</span>';
                } elseif ($sd['val'] === 'NP') {
                    $cell_val = '<span style="color:#dc2626; font-size:6.5pt; font-weight:bold; background-color:#fee2e2; padding:1px 2px;">NP</span>';
                } elseif ($sd['val'] === '-') {
                    $cell_val = '<span style="color:#cbd5e1; font-size:7pt;">-</span>';
                } else {
                    $cell_val = '<b style="color:#0f172a; font-size:7pt;">' . $sd['val'] . '</b>';
                }
                $html .= '<td width="' . $ancho_jornada . '%" align="center" style="font-size:7pt;">' . $cell_val . '</td>';
            }

            $html .= '<td width="12%" align="center" style="background-color:#ecfdf5; border-left:1px solid #a7f3d0; font-weight:bold; font-size:8pt; color:#047857;">' . $cn['total_format'] . '</td>';
            $html .= '</tr>';

            $row_count++;
        }

        if (empty($clasificacion_nadadoras)) {
            $html .= '<tr><td colspan="' . (4 + $num_jornadas_count) . '" align="center" style="color: #94a3b8; padding: 14px;">No constan nadadoras con puntuación calculada en este grupo.</td></tr>';
        }

        $html .= '</tbody></table>';
        $pdf->writeHTML($html, true, false, false, false, '');

        // Pie explicativo oficial FNRM Punto 9
        $html_norma = '
        <table cellpadding="3" style="width:100%; border-collapse:collapse; margin-top:5px; background-color:#f8fafc; border:1px solid #e2e8f0; border-radius:4px;">
            <tr>
                <td style="font-size:6pt; color:#475569; line-height:1.3;">
                    <b style="color:#e92662;">Normativa Oficial de Clasificación de Liga (FNRM - Punto 9):</b><br>
                    • <b>9.1</b> Puntuación obtenida del 100% de la nota oficial en figuras y rutinas técnicas en cada jornada disputada.<br>
                    • <b>9.2</b> Clasificación acumulada calculada sobre las <b>' . $mejores_cnt . ' mejores jornadas</b> de las ' . $numero_jornadas_liga . ' disputadas (descartando la peor puntuación, indicada con tachado <span style="text-decoration:line-through; color:#94a3b8;">00.0000</span>; la no participación en una jornada disputada computa como 0.0000 pts - <b>NP</b>).<br>
                    • <b>9.3</b> En caso de puntuaciones idénticas, las nadadoras comparten puesto en clasificación correlativa (*ex aequo*).
                </td>
            </tr>
        </table>';
        $pdf->writeHTML($html_norma, true, false, false, false, '');
    };

    // Obtener categorías con orden oficial
    $q_cats_orden = "SELECT id, nombre, orden FROM categorias WHERE id IN ('" . implode("','", $categorias_liga) . "') ORDER BY orden ASC, id ASC";
    $r_cats_orden = mysqli_query($connection, $q_cats_orden);
    $categorias_ordenadas = [];
    if ($r_cats_orden) {
        while ($rco = mysqli_fetch_assoc($r_cats_orden)) {
            $categorias_ordenadas[] = $rco;
        }
    }

    foreach ($categorias_ordenadas as $cat_info) {
        $categoria = (string)$cat_info['id'];
        $nombre_categoria_base = $cat_info['nombre'];
        $filtro_cat_sql = ($categoria == '241') ? "id_categoria IN ('241', '234', '235')" : "id_categoria = '" . $categoria . "'";

        // Comprobar si existen rutinas técnicas desglosadas por modalidad (Junior / Absoluta - Opción 2)
        $q_mods = "SELECT DISTINCT rfc.id_modalidad, m.nombre as mod_nom 
                   FROM resultados_figuras_categorias rfc 
                   JOIN modalidades m ON rfc.id_modalidad = m.id 
                   WHERE {$filtro_cat_sql} 
                     AND rfc.id_modalidad > 0 
                     AND rfc.id_competicion IN (SELECT id FROM competiciones WHERE clave_liga LIKE '".mysqli_real_escape_string($connection, $clave_liga)."')
                   ORDER BY m.id ASC";
        $r_mods = mysqli_query($connection, $q_mods);
        $modalidades_encontradas = [];
        if ($r_mods && mysqli_num_rows($r_mods) > 0) {
            while ($rm = mysqli_fetch_assoc($r_mods)) {
                $modalidades_encontradas[] = $rm;
            }
        }

        // Comprobar si existen resultados de figuras en la categoría
        $q_has_fig = "SELECT 1 FROM resultados_figuras_categorias 
                      WHERE {$filtro_cat_sql} AND (id_modalidad IS NULL OR id_modalidad = 0)
                        AND id_competicion IN (SELECT id FROM competiciones WHERE clave_liga LIKE '".mysqli_real_escape_string($connection, $clave_liga)."') LIMIT 1";
        $has_figures = (mysqli_num_rows(mysqli_query($connection, $q_has_fig)) > 0);

        if ($has_figures) {
            // 1. Clasificación General de la Categoría (Figuras)
            $render_tabla_liga($categoria, $filtro_cat_sql, $nombre_categoria_base, null);

            // 2. Si es Alevín (241) o Infantil (236), generar además la clasificación desglosada por año
            if ($categoria == '241' || $categoria == '236') {
                $q_anos = "SELECT DISTINCT año 
                           FROM resultados_figuras_categorias 
                           WHERE {$filtro_cat_sql} 
                             AND (id_modalidad IS NULL OR id_modalidad = 0)
                             AND id_competicion IN (SELECT id FROM competiciones WHERE clave_liga LIKE '" . mysqli_real_escape_string($connection, $clave_liga) . "')
                             AND año IS NOT NULL AND año != '' AND año != '0'
                           ORDER BY año ASC";
                $res_anos = mysqli_query($connection, $q_anos);
                if ($res_anos) {
                    while ($ra = mysqli_fetch_assoc($res_anos)) {
                        $ano_val = trim($ra['año']);
                        if (!empty($ano_val)) {
                            $render_tabla_liga($categoria, $filtro_cat_sql, $nombre_categoria_base, $ano_val);
                        }
                    }
                }
            }
        }

        // Si tiene modalidades de rutina técnica (Opción 2: Solo, Dúo, Equipo)
        if (!empty($modalidades_encontradas)) {
            foreach ($modalidades_encontradas as $me) {
                $render_tabla_liga($categoria, $filtro_cat_sql, $nombre_categoria_base, null, (int)$me['id_modalidad'], $me['mod_nom']);
            }
        }
    }
}

// 10. Salida limpia del documento PDF
$dest = $_GET['dest'] ?? 'I';
$output_target = ($dest === 'F' && !empty($_GET['save_path'])) ? $_GET['save_path'] : $nombre_documento;
$pdf->Output($output_target, $dest);
exit();
