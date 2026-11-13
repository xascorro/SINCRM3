<?php
//============================================================+
// File name   : informe_figuras_notas_anotadores.php
// Description : Hojas de puntuación para Anotadores / Secretarios
//               de Panel en pruebas de Figuras (SINCRM v4).
//               Diseñado para impresión A4 vertical (1 figura por hoja),
//               máxima legibilidad y comodidad de escritura a mano.
// Author      : Pedro Díaz / SINCRM
//============================================================+

require_once(__DIR__ . '/../tcpdf/tcpdf.php');
include_once(__DIR__ . '/../database/dbconfig.php');
include_once(__DIR__ . '/../lib/my_functions.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Control de acceso (sesión activa o token cron)
$cron_token_expected = "internal_cron_" . md5("SincrmInternalCron" . date('Y-m-d'));
$cron_token_provided = $_GET['cron_token'] ?? '';

if (!isset($_SESSION['email']) && !isset($_SESSION['username']) && $cron_token_provided !== $cron_token_expected) {
    header('Location: ../login.php');
    exit();
}

// 2. Obtener parámetros de entrada
$id_competicion = isset($_GET['id_competicion']) ? (int)$_GET['id_competicion'] : (isset($_SESSION['id_competicion_usuario']) ? (int)$_SESSION['id_competicion_usuario'] : (isset($_SESSION['id_competicion_activa']) ? (int)$_SESSION['id_competicion_activa'] : 0));
$id_fase_filtro = isset($_GET['id_fase']) ? (int)$_GET['id_fase'] : 0;
$id_categoria_filtro = isset($_GET['id_categoria']) ? (int)$_GET['id_categoria'] : 0;

if ($id_competicion > 0) {
    $q_comp = "SELECT * FROM competiciones WHERE id = '$id_competicion' LIMIT 1";
} else {
    $q_comp = "SELECT * FROM competiciones WHERE activo = 'si' LIMIT 1";
}
$result = mysqli_query($connection, $q_comp);
$comp = ($result) ? mysqli_fetch_assoc($result) : null;

if (!$comp) {
    die("Error: No se ha encontrado la competición solicitada.");
}

$GLOBALS["id_competicion_activa"] = (int)$comp['id'];
$GLOBALS["nombre_competicion_activa"] = $comp['nombre'];
$GLOBALS["lugar"] = !empty($comp['lugar']) ? $comp['lugar'] : 'Sede Oficial';
$GLOBALS["fecha"] = !empty($comp['fecha']) ? dateAFecha($comp['fecha']) : date('d/m/Y');
$GLOBALS["header_image"] = !empty($comp['header_informe']) ? __DIR__ . '/../' . $comp['header_informe'] : '';
$GLOBALS["footer_image"] = !empty($comp['footer_informe']) ? __DIR__ . '/../' . $comp['footer_informe'] : '';

$GLOBALS['titulo_documento'] = 'HOJA OFICIAL DE ANOTADOR · FIGURAS';
$GLOBALS['subtitulo_documento'] = $GLOBALS['nombre_competicion_activa'];
$GLOBALS['footer_substring'] = "Sede: " . $GLOBALS['lugar'] . " · Fecha: " . $GLOBALS['fecha'];

$nombre_documento = 'Anotadores_Figuras_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $GLOBALS['nombre_competicion_activa']);

// 3. Clase personalizada TCPDF con cabecera y pie oficiales SINCRM v4
class SINCRM_ANOTADORES_FIGURAS_PDF extends TCPDF {

    public function Header() {
        if (!empty($GLOBALS['header_image']) && file_exists($GLOBALS['header_image'])) {
            $this->Image($GLOBALS['header_image'], 10, 4, 190, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
            $this->SetY(5.5);
        } else {
            $this->SetY(5.5);
        }

        $this->SetFont('helvetica', 'B', 12);
        $this->SetTextColor(5, 150, 105); // Emerald-600
        $this->Cell(0, 5, $GLOBALS['titulo_documento'], 0, 1, 'C');

        $this->SetFont('helvetica', 'B', 8);
        $this->SetTextColor(71, 85, 105); // Slate-600
        $this->Cell(0, 4, mb_strtoupper($GLOBALS['subtitulo_documento'], 'UTF-8'), 0, 1, 'C');

        // Línea divisoria suave bajo la cabecera
        $this->SetDrawColor(203, 213, 225);
        $this->SetLineWidth(0.3);
        $this->Line(10, 22.5, 200, 22.5);
    }

    public function Footer() {
        if (!empty($GLOBALS['footer_image']) && file_exists($GLOBALS['footer_image'])) {
            $this->Image($GLOBALS['footer_image'], 10, 281, 190, '', 'JPG', '', '', false, 300, '', false, false, 0, false, false, false);
        }
        $this->SetY(284.5);
        $this->SetFont('helvetica', 'B', 7.5);
        $this->SetTextColor(100, 116, 139);
        $pagenumtxt = 'Página ' . $this->PageNo() . ' de ' . $this->getAliasNbPages();
        $this->Cell(0, 4, $GLOBALS['footer_substring'] . ' · ' . $pagenumtxt, 0, 0, 'R');
    }
}

// 4. Instancia TCPDF
$pdf = new SINCRM_ANOTADORES_FIGURAS_PDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('SINCRM');
$pdf->SetAuthor('Pedro Díaz');
$pdf->SetTitle('Hojas de Anotador - Figuras - ' . $GLOBALS["nombre_competicion_activa"]);
$pdf->SetSubject($GLOBALS["nombre_competicion_activa"]);

$pdf->SetMargins(10, 24, 10);
$pdf->SetHeaderMargin(4);
$pdf->SetFooterMargin(12);
$pdf->SetAutoPageBreak(TRUE, 12);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

if (@file_exists(__DIR__ . '/lang/spa.php')) {
    require_once(__DIR__ . '/lang/spa.php');
    $pdf->setLanguageArray($l);
}

// 5. Consulta de fases exclusivas de FIGURAS
$where_clauses = [
    "f.id_competicion = " . $GLOBALS['id_competicion_activa'],
    "f.id_figura > 0",
    "(f.elementos_coach_card <= 0 OR f.elementos_coach_card IS NULL)"
];

if ($id_fase_filtro > 0) {
    $where_clauses[] = "f.id = $id_fase_filtro";
}
if ($id_categoria_filtro > 0) {
    $where_clauses[] = "f.id_categoria = $id_categoria_filtro";
}

$where_sql = implode(' AND ', $where_clauses);

$q_fases = "
    SELECT f.*, c.nombre as nombre_categoria, c.orden as orden_categoria,
           fig.numero as figura_numero, fig.nombre as figura_nombre, 
           fig.grado_dificultad as figura_gd
    FROM fases f
    INNER JOIN figuras fig ON f.id_figura = fig.id
    LEFT JOIN categorias c ON f.id_categoria = c.id
    WHERE $where_sql
    ORDER BY c.orden ASC, f.orden ASC, f.id ASC
";
$res_fases = mysqli_query($connection, $q_fases);
$total_fases = ($res_fases) ? mysqli_num_rows($res_fases) : 0;

if ($total_fases === 0) {
    $pdf->AddPage();
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor(100, 116, 139);
    $html_vacio = '<div style="text-align:center; padding:50px 20px; color:#64748b;">
        <h3 style="color:#0f172a;">No hay fases de figuras configuradas</h3>
        <p>No se han encontrado pruebas de figuras con nadadoras para los filtros indicados.</p>
    </div>';
    $pdf->writeHTML($html_vacio, true, false, true, false, '');
} else {
    while ($fase = mysqli_fetch_assoc($res_fases)) {
        $id_fase = (int)$fase['id'];
        $id_cat_fase = (int)$fase['id_categoria'];
        $nombre_categoria = trim($fase['nombre_categoria'] ?? 'Figuras');
        $figura_numero = trim($fase['figura_numero'] ?? '');
        $figura_nombre = trim($fase['figura_nombre'] ?? '');
        $figura_gd = !empty($fase['figura_gd']) ? number_format((float)$fase['figura_gd'], 2, '.', '') : '0.00';
        $corte_fase = (int)($fase['corte'] ?? 1);
        if ($corte_fase <= 0) $corte_fase = 1;

        // 5.1 Encontrar la fase base de figuras de esta categoría (primera figura de la categoría)
        $q_fase_base = "
            SELECT id FROM fases 
            WHERE id_competicion = " . $GLOBALS['id_competicion_activa'] . " 
              AND id_categoria = $id_cat_fase 
              AND id_figura > 0 
            ORDER BY orden ASC, id ASC 
            LIMIT 1
        ";
        $res_fase_base = mysqli_query($connection, $q_fase_base);
        $id_fase_base = ($res_fase_base && $rfb = mysqli_fetch_assoc($res_fase_base)) ? (int)$rfb['id'] : $id_fase;

        // 5.2 Consultar jueces asignados al panel de esta fase
        $q_jueces = "
            SELECT pj.numero_juez, j.nombre, j.apellidos 
            FROM panel_jueces pj 
            LEFT JOIN jueces j ON pj.id_juez = j.id 
            WHERE pj.id_fase = $id_fase 
            ORDER BY pj.numero_juez ASC
        ";
        $res_jueces = mysqli_query($connection, $q_jueces);
        $jueces_panel = [];
        $jueces_nombres_str = [];
        if ($res_jueces) {
            while ($jrow = mysqli_fetch_assoc($res_jueces)) {
                $num_j = (int)$jrow['numero_juez'];
                $nom_completo = trim(($jrow['nombre'] ?? '') . ' ' . ($jrow['apellidos'] ?? ''));
                if (empty($nom_completo)) {
                    $nom_completo = "Juez $num_j";
                }
                $jueces_panel[$num_j] = $nom_completo;
                $jueces_nombres_str[] = "<strong>J$num_j:</strong> " . htmlspecialchars($nom_completo);
            }
        }

        // Dimensionar columnas según número de jueces (mínimo 5)
        $num_jueces_cols = max(5, count($jueces_panel));
        if ($num_jueces_cols > 7) {
            $num_jueces_cols = 7;
        }

        // 5.3 Consultar atletas inscritos en esta fase de figuras
        // Obtenemos también su orden_base en la categoría (dorsal oficial del sorteo general)
        $q_inscritos = "
            SELECT ifig.*, n.nombre, n.apellidos, n.licencia, n.año_nacimiento,
                   c.nombre as club_nombre, c.nombre_corto as club_corto,
                   COALESCE(
                       (SELECT ifb.orden FROM inscripciones_figuras ifb WHERE ifb.id_fase = $id_fase_base AND ifb.id_nadadora = ifig.id_nadadora LIMIT 1),
                       ifig.orden,
                       0
                   ) as orden_base
            FROM inscripciones_figuras ifig 
            JOIN nadadoras n ON ifig.id_nadadora = n.id 
            LEFT JOIN clubes c ON n.club = c.id 
            WHERE ifig.id_fase = $id_fase 
            ORDER BY ifig.orden ASC, ifig.id ASC
        ";
        $res_inscritos = mysqli_query($connection, $q_inscritos);
        $atletas_raw = [];
        if ($res_inscritos) {
            while ($at = mysqli_fetch_assoc($res_inscritos)) {
                $atletas_raw[] = $at;
            }
        }
        $num_inscritos = count($atletas_raw);

        // Separar preswimmers de atletas regulares
        $preswimmers = [];
        $regulares = [];
        foreach ($atletas_raw as $at) {
            $es_pre = (isset($at['preswimmer']) && strtolower(trim($at['preswimmer'])) === 'si') || ((int)$at['orden'] < 0);
            if ($es_pre) {
                $preswimmers[] = $at;
            } else {
                $regulares[] = $at;
            }
        }

        // Rotación de salida según el corte oficial de la figura
        $total_regulares = count($regulares);
        if ($total_regulares > 0 && $corte_fase > 1) {
            $corte_target = (($corte_fase - 1) % $total_regulares) + 1;
            $primer_ord_base = (int)($regulares[0]['orden_base'] ?? 0);

            // Si el primer atleta actual no es el del corte, rotamos la lista
            if ($primer_ord_base !== $corte_target) {
                $found_idx = -1;
                foreach ($regulares as $idx => $r_at) {
                    if ((int)($r_at['orden_base'] ?? 0) === $corte_target) {
                        $found_idx = $idx;
                        break;
                    }
                }
                if ($found_idx === -1 && isset($regulares[$corte_target - 1])) {
                    $found_idx = $corte_target - 1;
                }
                if ($found_idx > 0) {
                    $regulares = array_merge(array_slice($regulares, $found_idx), array_slice($regulares, 0, $found_idx));
                }
            }
        }

        $atletas_ordenados = array_merge($preswimmers, $regulares);

        // Iniciar página para esta fase
        $pdf->AddPage();

        // 5.4 Tarjeta superior de la figura y panel de jueces
        $html_fase = '<table cellpadding="2.5" style="border:1px solid #64748b; background-color:#f8fafc; width:100%;">';
        $html_fase .= '<tr>';
        $html_fase .= '<td width="72%">';
        $html_fase .= '<span style="background-color:#059669; color:#ffffff; font-weight:bold; font-size:8pt; padding:2px 5px;">&nbsp;' . mb_strtoupper($nombre_categoria, 'UTF-8') . '&nbsp;</span>&nbsp;&nbsp;';
        $html_fase .= '<strong style="font-size:10.5pt; color:#0f172a;">Figura ' . htmlspecialchars($figura_numero) . ' - ' . htmlspecialchars($figura_nombre) . '</strong>';
        if ($corte_fase > 1) {
            $html_fase .= '&nbsp;&nbsp;<span style="background-color:#eff6ff; color:#1d4ed8; font-weight:bold; font-size:7.5pt; padding:1px 4px; border:1px solid #bfdbfe;">&nbsp;CORTE: DESDE Nº ' . $corte_fase . '&nbsp;</span>';
        }
        $html_fase .= '</td>';
        $html_fase .= '<td width="28%" style="text-align:right;">';
        if (!empty($fase['orden'])) {
            $html_fase .= '<span style="font-size:8pt; color:#64748b; font-weight:bold;">Evento ' . $fase['orden'] . ' &nbsp;|&nbsp; </span>';
        }
        $html_fase .= '<span style="font-size:11.5pt; color:#2563eb; font-weight:bold;">GD: ' . $figura_gd . '</span>';
        $html_fase .= '</td>';
        $html_fase .= '</tr>';

        $html_fase .= '<tr>';
        $html_fase .= '<td colspan="2" style="font-size:6.8pt; color:#334155; border-top:1px dashed #cbd5e1; background-color:#ffffff;">';
        if (!empty($jueces_nombres_str)) {
            $html_fase .= '<strong>Panel de Jueces:</strong> ' . implode(' &nbsp;|&nbsp; ', $jueces_nombres_str);
        } else {
            $html_fase .= '<strong>Panel de Jueces:</strong> J1 · J2 · J3 · J4 · J5 (No asignados nominativamente)';
        }
        $html_fase .= '</td>';
        $html_fase .= '</tr>';

        $html_fase .= '<tr>';
        $html_fase .= '<td colspan="2" style="font-size:7.2pt; color:#475569; background-color:#f8fafc; border-top:1px solid #e2e8f0;">';
        $html_fase .= '<strong>Anotador/a de Panel:</strong> __________________________________________________ &nbsp;&nbsp;&nbsp;&nbsp; <strong>Firma:</strong> ______________________';
        $html_fase .= '</td>';
        $html_fase .= '</tr>';
        $html_fase .= '</table>';

        $html_fase .= '<div style="height:2px;"></div>';

        // 5.4 Distribución de anchos de columna
        $w_orden = 7;   // 7%
        $w_total = 11;  // 11%
        $w_cada_juez = 8;
        $w_jueces_total = $num_jueces_cols * $w_cada_juez; // p.ej. 5 * 8 = 40%
        $w_atleta = 100 - ($w_orden + $w_total + $w_jueces_total); // 42%

        // Optimización de espaciado según número de nadadoras
        if ($num_inscritos <= 16) {
            $cp = '3.2';
            $font_order_size = '10pt';
            $font_atleta_size = '8.8pt';
            $sub_size = '7pt';
            $two_lines = true;
        } elseif ($num_inscritos <= 24) {
            $cp = '2.2';
            $font_order_size = '9pt';
            $font_atleta_size = '8pt';
            $sub_size = '6.8pt';
            $two_lines = false;
        } else {
            $cp = '1.6';
            $font_order_size = '8.5pt';
            $font_atleta_size = '7.5pt';
            $sub_size = '6.5pt';
            $two_lines = false;
        }

        // 5.5 Tabla de Anotación
        $html_fase .= '<table cellpadding="' . $cp . '" border="1" style="border-collapse:collapse; border:1px solid #64748b; width:100%;">';
        $html_fase .= '<thead>';
        $html_fase .= '<tr style="background-color:#0f172a; color:#ffffff; font-weight:bold; text-align:center; font-size:7.5pt;">';
        $html_fase .= '<th width="' . $w_orden . '%">ORD.</th>';
        $html_fase .= '<th width="' . $w_atleta . '%" style="text-align:left;">&nbsp;ATLETA / CLUB</th>';
        for ($j = 1; $j <= $num_jueces_cols; $j++) {
            $html_fase .= '<th width="' . $w_cada_juez . '%">J' . $j . '</th>';
        }
        $html_fase .= '<th width="' . $w_total . '%">TOTAL / OBS</th>';
        $html_fase .= '</tr>';
        $html_fase .= '</thead>';
        $html_fase .= '<tbody>';

        if ($num_inscritos > 0) {
            $fila_i = 0;
            foreach ($atletas_ordenados as $atleta) {
                $fila_i++;
                $bg_color = ($fila_i % 2 === 0) ? '#f8fafc' : '#ffffff';

                $es_baja = (isset($atleta['baja']) && strtolower(trim($atleta['baja'])) === 'si');
                $es_preswimmer = (isset($atleta['preswimmer']) && strtolower(trim($atleta['preswimmer'])) === 'si') || ((int)$atleta['orden'] < 0);

                // El orden visible oficial para el anotador es su dorsal oficial / orden de salida general de la categoría
                if ($es_preswimmer) {
                    $orden_str = 'PS';
                } else {
                    $ord_b = (int)($atleta['orden_base'] ?? 0);
                    $orden_str = ($ord_b > 0) ? (string)$ord_b : (string)$fila_i;
                }

                $nombre_atleta = mb_strtoupper(trim(($atleta['apellidos'] ?? '') . ', ' . ($atleta['nombre'] ?? '')), 'UTF-8');
                $club_str = !empty($atleta['club_corto']) ? $atleta['club_corto'] : (!empty($atleta['club_nombre']) ? $atleta['club_nombre'] : 'Sin Club');
                $ano_str = !empty($atleta['año_nacimiento']) ? ' (' . $atleta['año_nacimiento'] . ')' : '';

                $html_fase .= '<tr style="background-color:' . $bg_color . ';" nobr="true">';

                // Columna Orden
                $html_fase .= '<td width="' . $w_orden . '%" style="text-align:center; font-weight:bold; font-size:' . $font_order_size . '; vertical-align:middle;">';
                if ($es_preswimmer) {
                    $html_fase .= '<span style="color:#d97706; font-size:7pt;">PS</span>';
                } else {
                    $html_fase .= $orden_str;
                }
                $html_fase .= '</td>';

                // Columna Atleta / Club
                $html_fase .= '<td width="' . $w_atleta . '%" style="vertical-align:middle;">';
                $html_fase .= '<strong style="font-size:' . $font_atleta_size . '; color:' . ($es_baja ? '#dc2626' : '#0f172a') . ';">' . htmlspecialchars($nombre_atleta) . '</strong>';
                if ($es_baja) {
                    $html_fase .= ' <span style="color:#dc2626; font-weight:bold; font-size:6.5pt;">[BAJA]</span>';
                }
                if ($es_preswimmer) {
                    $html_fase .= ' <span style="color:#d97706; font-weight:bold; font-size:6.5pt;">[PRE]</span>';
                }

                if ($two_lines) {
                    $html_fase .= '<br><span style="color:#2563eb; font-weight:bold; font-size:' . $sub_size . ';">' . htmlspecialchars($club_str) . '</span> <span style="color:#64748b; font-size:' . $sub_size . ';">' . $ano_str . '</span>';
                } else {
                    $html_fase .= ' &nbsp;<span style="color:#2563eb; font-weight:bold; font-size:' . $sub_size . ';">· ' . htmlspecialchars($club_str) . '</span> <span style="color:#64748b; font-size:' . $sub_size . ';">' . $ano_str . '</span>';
                }
                $html_fase .= '</td>';

                // Celdas de jueces vacías y amplias
                if ($es_baja) {
                    $html_fase .= '<td colspan="' . $num_jueces_cols . '" style="text-align:center; color:#dc2626; font-weight:bold; font-size:7.5pt; vertical-align:middle; background-color:#fee2e2;">--- BAJA ---</td>';
                } else {
                    for ($j = 1; $j <= $num_jueces_cols; $j++) {
                        $html_fase .= '<td width="' . $w_cada_juez . '%" style="vertical-align:middle;"></td>';
                    }
                }

                // Columna total / obs
                $html_fase .= '<td width="' . $w_total . '%" style="vertical-align:middle; background-color:#f1f5f9;"></td>';

                $html_fase .= '</tr>';
            }
        } else {
            // Filas vacías de cortesía si no hay nadadoras registradas
            for ($k = 1; $k <= 12; $k++) {
                $bg_color = ($k % 2 === 0) ? '#f8fafc' : '#ffffff';
                $html_fase .= '<tr style="background-color:' . $bg_color . ';" nobr="true">';
                $html_fase .= '<td width="' . $w_orden . '%" style="text-align:center; font-weight:bold; height:22px; line-height:22px;">' . $k . '</td>';
                $html_fase .= '<td width="' . $w_atleta . '%" style="color:#94a3b8; font-style:italic;">&nbsp;Disponible para anotación</td>';
                for ($j = 1; $j <= $num_jueces_cols; $j++) {
                    $html_fase .= '<td width="' . $w_cada_juez . '%" style="height:22px;"></td>';
                }
                $html_fase .= '<td width="' . $w_total . '%" style="height:22px; background-color:#f1f5f9;"></td>';
                $html_fase .= '</tr>';
            }
        }

        $html_fase .= '</tbody>';
        $html_fase .= '</table>';

        $pdf->writeHTML($html_fase, true, false, true, false, '');
    }
}

// 6. Cabeceras anti-caché y descarga del PDF
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

$pdf->Output($nombre_documento . '.pdf', 'I');
?>
