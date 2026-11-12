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

// 4. Clase TCPDF personalizada v4 para informes de Liga en horizontal (Landscape)
class SINCRM_LIGA_PDF extends TCPDF {
    public $current_category_name = '';

    public function Header() {
        $comp = $GLOBALS['comp_data'];

        // Cabecera institucional
        if (!empty($comp['header_image']) && file_exists($comp['header_image'])) {
            $this->Image($comp['header_image'], 15, 7, 267, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
            $this->SetY(24);
        } else {
            $this->SetY(10);
        }

        // Título principal
        $this->SetFont('helvetica', 'B', 15);
        $this->SetTextColor(233, 38, 98); // #e92662
        $titulo_header = mb_strtoupper($GLOBALS['titulo_doc'] ?? 'CLASIFICACIÓN GENERAL LIGA', 'UTF-8');
        if (!empty($this->current_category_name)) {
            $titulo_header .= ' · ' . mb_strtoupper($this->current_category_name, 'UTF-8');
        }
        $this->Cell(0, 6, $titulo_header, 0, 1, 'C');

        // Subtítulo con temporada y clave de liga
        $this->SetFont('helvetica', 'B', 8.5);
        $this->SetTextColor(100, 116, 139); // Slate-500
        $sub = ($comp['temporada'] ? $comp['temporada'] . ' · ' : '') . $comp['clave_liga'] . ' de Natación Artística FNRM';
        $this->Cell(0, 4, mb_strtoupper($sub, 'UTF-8'), 0, 1, 'C');

        // Línea divisoria suave
        $this->SetDrawColor(233, 38, 98);
        $this->SetLineWidth(0.4);
        $this->Line(15, $this->GetY() + 2, 282, $this->GetY() + 2);
    }

    public function Footer() {
        $comp = $GLOBALS['comp_data'];

        // Imagen de pie de página a la izquierda
        if (!empty($comp['footer_image']) && file_exists($comp['footer_image'])) {
            $this->SetY(-22);
            $this->Image($comp['footer_image'], 15, $this->GetY(), 180, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }

        // Metadatos a la derecha en multilínea (requisito FNRM v4)
        $this->SetFont('helvetica', 'B', 7);
        $this->SetTextColor(71, 85, 105);
        $this->setCellPaddings(0, 0, 0, 0);

        $txt_sede = "Sede: " . trim($comp['lugar']);
        $txt_fecha = "Fecha: " . trim($comp['fecha']);
        $txt_pag = "Página " . $this->PageNo() . " de " . $this->getAliasNbPages();

        $this->SetY(-20);
        $this->MultiCell(75, 3.8, $txt_sede, 0, 'R', false, 1, 207, '', true, 0, false, true, 4, 'M', true);
        $this->MultiCell(75, 3.8, $txt_fecha, 0, 'R', false, 1, 207, '', true, 0, false, true, 4, 'M', true);
        $this->MultiCell(75, 3.8, $txt_pag, 0, 'R', false, 1, 207, '', true, 0, false, true, 4, 'M', true);
    }
}

// 5. Inicialización del documento PDF
$pdf = new SINCRM_LIGA_PDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator('SINCRM v4.0');
$pdf->SetAuthor('Pedro Díaz');
$pdf->SetTitle($GLOBALS['titulo_doc'] . ' - ' . $GLOBALS['comp_data']['clave_liga']);
$pdf->SetSubject($GLOBALS['comp_data']['nombre']);
$pdf->SetKeywords('sincro, liga, figuras, clasificacion, fnrm');

$pdf->SetMargins(15, 34, 15);
$pdf->SetHeaderMargin(6);
$pdf->SetFooterMargin(12);
$pdf->SetAutoPageBreak(TRUE, 24);
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
    $pdf->AddPage('L');

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
    <table cellpadding="6" cellspacing="0" width="100%" style="margin-top: 10px;">
        <tr>
            <td style="background-color: #fef3c7; border: 1px solid #f59e0b; border-radius: 6px; color: #92400e;">
                <div style="font-size: 13pt; font-weight: bold; color: #b45309;">
                    ℹ CLASIFICACIÓN GENERAL EN ESPERA DE PUNTUACIONES
                </div>
                <div style="font-size: 10pt; line-height: 1.5; color: #78350f; margin-top: 4px;">
                    Actualmente no constan resultados oficiales computados para la <b>' . htmlspecialchars($clave_liga) . '</b>.<br>
                    La clasificación general acumulada por categorías y el cómputo del descarte de la peor jornada (según la Normativa FNRM Regla 9) se generarán y actualizarán de forma automática una vez se hayan registrado las notas y cerrado las actas de figuras de las jornadas disputadas.
                </div>
            </td>
        </tr>
    </table>
    <br><br>

    <table width="100%" cellpadding="5" cellspacing="0">
        <tr valign="top">
            <td width="55%">
                <div style="font-size: 11pt; font-weight: bold; color: #e92662; border-bottom: 2px solid #e92662; padding-bottom: 3px;">
                    CALENDARIO OFICIAL DE JORNADAS DE LA LIGA
                </div>
                <br>
                <table width="100%" border="1" cellpadding="5" style="border-collapse: collapse; font-size: 9pt; border: 1px solid #cbd5e1;">
                    <tr style="background-color: #f1f5f9; font-weight: bold; color: #334155;">
                        <th width="30%">Jornada</th>
                        <th width="25%">Fecha</th>
                        <th width="25%">Sede / Lugar</th>
                        <th width="20%" align="center">Estado</th>
                    </tr>';
    
    foreach ($jornadas_liga_arr as $jl) {
        $is_activa = ((int)$jl['id'] === $GLOBALS['comp_data']['id']);
        $has_res = ($jornadas_con_resultados_map[(int)$jl['id']] ?? 0) > 0;
        $bg_jl = $is_activa ? '#fef2f2' : '#ffffff';
        $color_badge = $has_res ? '#15803d' : ($is_activa ? '#e92662' : '#64748b');
        $estado_txt = $has_res ? 'Disputada' : ($is_activa ? 'En curso / Activa' : 'Programada');

        $html .= '<tr style="background-color: '.$bg_jl.';">
            <td style="font-weight: bold; color: #1e293b;">' . htmlspecialchars($jl['nombre']) . '</td>
            <td>' . dateAFecha($jl['fecha']) . '</td>
            <td>' . htmlspecialchars($jl['lugar']) . '</td>
            <td align="center" style="font-weight: bold; color: '.$color_badge.';">' . $estado_txt . '</td>
        </tr>';
    }

    $html .= '</table>
            </td>
            <td width="5%"></td>
            <td width="40%">
                <div style="font-size: 11pt; font-weight: bold; color: #e92662; border-bottom: 2px solid #e92662; padding-bottom: 3px;">
                    CENSO DE CATEGORÍAS (Jornada Activa)
                </div>
                <br>
                <table width="100%" border="1" cellpadding="5" style="border-collapse: collapse; font-size: 9pt; border: 1px solid #cbd5e1;">
                    <tr style="background-color: #f1f5f9; font-weight: bold; color: #334155;">
                        <th width="70%">Categoría</th>
                        <th width="30%" align="center">Inscripciones</th>
                    </tr>';

    if (!empty($censo_categorias)) {
        foreach ($censo_categorias as $cc) {
            $html .= '<tr>
                <td style="font-weight: bold; color: #334155;">' . htmlspecialchars($cc['nombre']) . '</td>
                <td align="center" style="font-weight: bold; color: #e92662;">' . (int)$cc['num_inscr'] . '</td>
            </tr>';
        }
    } else {
        $html .= '<tr><td colspan="2" align="center" style="color: #94a3b8;">Sin fases de figuras registradas</td></tr>';
    }

    $html .= '</table>
                <br>
                <div style="background-color: #f8fafc; border: 1px dashed #cbd5e1; padding: 8px; border-radius: 4px; font-size: 8pt; color: #64748b; line-height: 1.4;">
                    <strong>Normativa de Clasificación (FNRM - Punto 9):</strong><br>
                    • Puntúan las ' . max(1, $numero_jornadas_liga - 1) . ' mejores jornadas de las ' . $numero_jornadas_liga . ' disputadas.<br>
                    • Por no competir en una jornada se asigna 0.0000 pts (NP).<br>
                    • No se contempla el desempate en caso de puntuaciones idénticas (<em>ex aequo</em>).
                </div>
            </td>
        </tr>
    </table>';

    $pdf->writeHTML($html, true, false, true, false, '');

} else {
    // 9. ESCENARIO B: Existen resultados para generar la clasificación por categorías
    $total_color = '#DCEDC8'; // Verde pastel clásico

    foreach ($categorias_liga as $categoria) {
        // Obtener nombre oficial de la categoría
        $query_cat_nom = "SELECT nombre FROM categorias WHERE id = '".$categoria."'";
        $nombre_categoria = safe_mysqli_result($connection, $query_cat_nom, 0) ?: ("Categoría " . $categoria);
        
        $pdf->current_category_name = $nombre_categoria;
        $pdf->AddPage('L');

        // Construcción de la tabla
        $html = '<table align="center" border="1" cellpadding="4" style="border-collapse: collapse; border: 1px solid #cbd5e1;">';
        $html .= '<tr style="font-weight: bold; background-color: #f1f5f9; color: #1e293b;">
                    <th style="width: 4%; text-align: center;">P</th>
                    <th style="width: 25%; text-align: left;">Nadadora</th>
                    <th style="width: 21%; text-align: left;">Club</th>';

        // Columnas de jornadas
        $ancho_jornada = floor(42 / max(1, $numero_jornadas_liga));
        foreach ($jornadas_liga_arr as $jornada_liga) {
            $col_color = !empty($jornada_liga['color']) ? $jornada_liga['color'] : '#e2e8f0';
            $html .= '<th style="width: '.$ancho_jornada.'%; background-color: '.$col_color.'; text-align: center;">'
                  . htmlspecialchars($jornada_liga['nombre_corto']) . '<br>'
                  . '<span style="font-size: 8pt; font-weight: normal;">' . dateAFecha($jornada_liga['fecha']) . '</span><br>'
                  . '<span style="font-size: 7pt; font-weight: normal;">' . htmlspecialchars($jornada_liga['lugar']) . '</span></th>';
        }

        $html .= '<th style="width: 8%; background-color: '.$total_color.'; text-align: center;">Total</th></tr>';

        // Obtener nadadoras con resultados en esta categoría
        $filtro_cat_sql = ($categoria == '241') ? "id_categoria IN ('241', '234', '235')" : "id_categoria = '".$categoria."'";
        $query_nad = "SELECT DISTINCT id_nadadora 
                      FROM resultados_figuras_categorias 
                      WHERE {$filtro_cat_sql} 
                        AND id_competicion IN (SELECT id FROM competiciones WHERE clave_liga LIKE '".mysqli_real_escape_string($connection, $clave_liga)."')";
        $res_nad = mysqli_query($connection, $query_nad);

        $clasificacion_nadadoras = [];
        $idx = 0;

        if ($res_nad) {
            while ($nad = mysqli_fetch_assoc($res_nad)) {
                $id_nad = (int)$nad['id_nadadora'];

                // Datos de la nadadora y club en una sola consulta
                $q_info = "SELECT n.nombre, n.apellidos, n.año_nacimiento, c.nombre AS nombre_club 
                           FROM nadadoras n 
                           LEFT JOIN clubes c ON c.id = n.club 
                           WHERE n.id = '$id_nad'";
                $r_info = mysqli_query($connection, $q_info);
                $d_info = $r_info ? mysqli_fetch_assoc($r_info) : null;

                $nombre_completo = $d_info ? ($d_info['apellidos'] . ', ' . $d_info['nombre']) : 'Nadadora #' . $id_nad;
                $anio_nac = $d_info['año_nacimiento'] ?? '';
                $club_nom = $d_info['nombre_club'] ?? 'Sin Club';

                $puntos_totales = 0.0;
                $scores_nadadora = [];
                $display_nadadora = [];

                foreach ($jornadas_liga_arr as $jornada_liga) {
                    $id_j = (int)$jornada_liga['id'];
                    $jornada_tiene_resultados = ($jornadas_con_resultados_map[$id_j] ?? 0) > 0;

                    // Consulta de nota de la nadadora en esta jornada
                    $q_pts = "SELECT COALESCE(nota_final_calculada, puntos, 0) AS nota_real 
                              FROM resultados_figuras_categorias 
                              WHERE id_nadadora = '$id_nad' AND id_competicion = '$id_j' LIMIT 1";
                    $r_pts = mysqli_query($connection, $q_pts);

                    if ($r_pts && mysqli_num_rows($r_pts) > 0) {
                        $p_num = (float)mysqli_fetch_row($r_pts)[0];
                        $p_disp = ($p_num > 0) ? number_format($p_num, 4) : '0.0000';
                    } else {
                        if ($jornada_tiene_resultados) {
                            // Jornada disputada pero la nadadora no participó
                            $p_num = 0.0000;
                            $p_disp = 'NP';
                        } else {
                            // Jornada no disputada aún
                            $p_num = 0.0000;
                            $p_disp = '-';
                        }
                    }

                    $scores_nadadora[$id_j] = $p_num;
                    $display_nadadora[$id_j] = $p_disp;
                    $puntos_totales += $p_num;
                }

                // Regla 9.2 FNRM: Descarte de la peor jornada entre las jornadas disputadas
                $jornada_descarte_id = null;
                $puntos_descarte = 0.0000;

                if ($jornadas_disputadas_count >= 2 && !isset($_GET['sin_descarte'])) {
                    // Consideramos solo las jornadas que ya se han disputado
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

                // Solo incluir si tiene puntos o participó en alguna jornada
                if ($puntos_totales > 0 || in_array('NP', $display_nadadora)) {
                    $clasificacion_nadadoras[$idx] = [
                        'nombre' => $nombre_completo . ($anio_nac ? ' (' . $anio_nac . ')' : ''),
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

        // Generar filas de la tabla
        $posicion = 0;
        $puntos_anterior = -1;
        $row_count = 0;

        foreach ($clasificacion_nadadoras as $cn) {
            $pts = $cn['puntos_clasificacion'];
            if (abs($pts - $puntos_anterior) > 0.0001) {
                $posicion++;
                $puntos_anterior = $pts;
            }

            $bg = ($row_count % 2 === 0) ? '#ffffff' : '#f8fafc';
            $html .= '<tr style="background-color: '.$bg.';">
                <td style="width: 4%; font-weight: bold; text-align: center; font-size: 9pt;">' . $posicion . '</td>
                <td style="width: 25%; text-align: left; font-size: 8.5pt;">' . htmlspecialchars($cn['nombre']) . '</td>
                <td style="width: 21%; text-align: left; font-size: 8.5pt;">' . htmlspecialchars($cn['nombre_club']) . '</td>';

            foreach ($cn['scores_display'] as $sd) {
                if ($sd['descarte']) {
                    $cell_val = '<span style="text-decoration: line-through; color: #94a3b8; font-weight: bold;">' . $sd['val'] . '</span>';
                } else {
                    $cell_val = '<span style="color: #1e293b; font-weight: 500;">' . $sd['val'] . '</span>';
                }
                $html .= '<td style="width: '.$ancho_jornada.'%; text-align: center; font-size: 8.5pt;">' . $cell_val . '</td>';
            }

            $html .= '<td style="width: 8%; text-align: center; font-weight: bold; font-size: 9pt; background-color: '.$total_color.';">' . $cn['total_format'] . '</td>
            </tr>';

            $row_count++;
        }

        if (empty($clasificacion_nadadoras)) {
            $html .= '<tr><td colspan="' . (4 + $numero_jornadas_liga) . '" align="center" style="color: #94a3b8; padding: 12px;">No constan nadadoras con puntuación calculada en esta categoría.</td></tr>';
        }

        $html .= '</table>';
        $pdf->writeHTML($html, true, false, false, false, '');

        // Pie explicativo oficial FNRM Punto 9
        $mejores_cnt = max(1, $numero_jornadas_liga - 1);
        $html_norma = '<div style="font-size: 8pt; color: #64748b; line-height: 1.4; margin-top: 6px;">
            <strong>Normativa de Clasificación (FNRM - Punto 9):</strong><br>
            9.1 El resultado se obtiene del 100% de la puntuación oficial de las figuras y rutinas técnicas en cada jornada.<br>
            9.2 Los resultados de cada nadadora se obtienen de la suma de las ' . $mejores_cnt . ' mejores jornadas de las ' . $numero_jornadas_liga . ' en las que puede participar (descartando la peor jornada, indicada con <span style="text-decoration: line-through;">tachado</span>; por no competir se asigna 0.0000 pts - NP).<br>
            9.3 Sistema de puntuación y baremación según normativa técnica FNRM.<br>
            9.4 No se contempla el desempate en caso de puntuaciones idénticas (posiciones compartidas <em>ex aequo</em>).
        </div>';
        $pdf->writeHTML($html_norma, true, false, false, false, '');
    }
}

// 10. Salida limpia del documento PDF
$pdf->Output($nombre_documento, 'I');
exit();
