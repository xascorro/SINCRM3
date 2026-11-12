<?php
// Silenciar warnings en generación PDF para evitar corromper la salida binaria
ini_set('display_errors', 0);
error_reporting(0);
setlocale(LC_ALL, 'es_ES');

require_once(__DIR__ . '/../tcpdf/tcpdf.php');
include(__DIR__ . '/../database/dbconfig.php');
include(__DIR__ . '/../lib/my_functions.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cabeceras estrictas anti-caché
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Validar acceso (Sesión o Token de Cron)
$cron_token_expected = "internal_cron_" . md5("SincrmInternalCron" . date('Y-m-d'));
$cron_token_provided = $_GET['cron_token'] ?? '';

if (!isset($_SESSION['email']) && !isset($_SESSION['username']) && $cron_token_provided !== $cron_token_expected) {
    header('Location: ../login.php');
    exit();
}

mysqli_query($connection, "SET NAMES 'utf8'");

// 1. Obtener competición
$id_competicion = isset($_GET['id_competicion']) ? intval($_GET['id_competicion']) : (int)($_SESSION['id_competicion_activa'] ?? 0);
$id_rutina_get = isset($_GET['id_rutina']) ? intval($_GET['id_rutina']) : 0;

if ($id_competicion > 0) {
    $q_comp = "SELECT * FROM competiciones WHERE id = $id_competicion";
} else {
    $q_comp = "SELECT * FROM competiciones WHERE activo = 'si' LIMIT 1";
}
$res_comp = mysqli_query($connection, $q_comp);
$competicion = mysqli_fetch_assoc($res_comp);

if (!$competicion) {
    die("Competición no encontrada.");
}

$GLOBALS["id_competicion_activa"] = (int)$competicion['id'];
$GLOBALS["nombre_competicion_activa"] = $competicion['nombre'];
$GLOBALS["lugar"] = $competicion['lugar'];
$GLOBALS["fecha"] = $competicion['fecha'];
$GLOBALS["header_image"] = !empty($competicion['header_informe']) ? '../' . $competicion['header_informe'] : '';
$GLOBALS["footer_image"] = !empty($competicion['footer_informe']) ? '../' . $competicion['footer_informe'] : '';
$GLOBALS["footer_substring"] = "Sede: " . $GLOBALS['lugar'] . " · Fecha: " . dateAFecha($GLOBALS['fecha']);
$GLOBALS["titulo_documento"] = "COACH CARD";

$nombre_documento = ($id_rutina_get ? 'CC_' . $id_rutina_get . '_' : 'Coach_Cards_') . str_replace(' ', '_', $competicion['nombre']) . '.pdf';

// Helper de duración reglamentaria
function getReglamentaryDurationCoachCard($mod_nombre, $cat_nombre) {
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

// Clase PDF Coach Card
class SINCRM_COACH_CARD_PDF extends TCPDF {
    public function Header() {
        if (!empty($GLOBALS['header_image']) && file_exists($GLOBALS['header_image'])) {
            $this->Image($GLOBALS['header_image'], 12, 6, 186, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
            $this->SetY(20);
        } else {
            $this->SetY(10);
        }
        $this->SetFont('helvetica', 'B', 12);
        $this->SetTextColor(233, 38, 98);
        $this->Cell(0, 5, $GLOBALS['titulo_documento'], 0, 1, 'C');
        $this->SetFont('helvetica', 'B', 8);
        $this->SetTextColor(100, 116, 139);
        $this->Cell(0, 4, mb_strtoupper($GLOBALS['nombre_competicion_activa'], 'UTF-8'), 0, 1, 'C');
        $this->SetDrawColor(226, 232, 240);
        $this->SetLineWidth(0.3);
        $this->Line(12, $this->GetY() + 1, 198, $this->GetY() + 1);
    }

    public function Footer() {
        if (!empty($GLOBALS['footer_image']) && file_exists($GLOBALS['footer_image'])) {
            $this->Image($GLOBALS['footer_image'], 12, 280, 186, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
        $this->SetY(281);
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

$pdf = new SINCRM_COACH_CARD_PDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator('SINCRM v4');
$pdf->SetAuthor('Pedro Díaz');
$pdf->SetTitle('Coach Card');
$pdf->SetSubject($GLOBALS["nombre_competicion_activa"]);

$pdf->SetMargins(12, 30, 12);
$pdf->SetHeaderMargin(6);
$pdf->SetFooterMargin(12);
$pdf->SetAutoPagebreak(TRUE, 18);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// Control de privacidad por rol
$is_rol_club = (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 5);
$club_sesion = (int)($_SESSION['club'] ?? 0);

if ($is_rol_club && $club_sesion > 0) {
    if ($id_rutina_get > 0) {
        $q_rutinas_cc = "SELECT id FROM rutinas WHERE id = $id_rutina_get AND id_club = $club_sesion ORDER BY id_fase, orden";
    } else {
        $q_rutinas_cc = "SELECT r.id FROM rutinas r 
                         JOIN fases f ON r.id_fase = f.id 
                         WHERE f.id_competicion = $id_competicion 
                           AND r.id_club = $club_sesion 
                           AND f.elementos_coach_card > 0 
                         ORDER BY f.orden, r.orden, r.id";
    }
} else {
    if ($id_rutina_get > 0) {
        $q_rutinas_cc = "SELECT id FROM rutinas WHERE id = $id_rutina_get ORDER BY id_fase, orden";
    } elseif (isset($_GET['id_club']) && intval($_GET['id_club']) > 0) {
        $id_club_req = intval($_GET['id_club']);
        $q_rutinas_cc = "SELECT r.id FROM rutinas r 
                         JOIN fases f ON r.id_fase = f.id 
                         WHERE f.id_competicion = $id_competicion 
                           AND r.id_club = $id_club_req 
                           AND f.elementos_coach_card > 0 
                         ORDER BY f.orden, r.orden, r.id";
    } else {
        $q_rutinas_cc = "SELECT r.id FROM rutinas r 
                         JOIN fases f ON r.id_fase = f.id 
                         WHERE f.id_competicion = $id_competicion 
                           AND f.elementos_coach_card > 0 
                         ORDER BY f.orden, r.orden, r.id";
    }
}

$res_rutinas_cc = mysqli_query($connection, $q_rutinas_cc);

if (!$res_rutinas_cc || mysqli_num_rows($res_rutinas_cc) == 0) {
    $pdf->AddPage();
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 20, 'No se encontraron Coach Cards con los filtros seleccionados.', 0, 1, 'C');
    $pdf->Output($nombre_documento, 'I');
    exit();
}

while ($r_cc = mysqli_fetch_assoc($res_rutinas_cc)) {
    $id_rutina = intval($r_cc['id']);

    $q_info = "SELECT r.*, 
                      c.nombre as nombre_club_largo, 
                      c.nombre_corto as nombre_club, 
                      m.nombre as nombre_modalidad, 
                      cat.nombre as nombre_categoria, 
                      f.elementos_coach_card, 
                      f.genero as fase_genero 
               FROM rutinas r 
               JOIN fases f ON r.id_fase = f.id 
               JOIN modalidades m ON f.id_modalidad = m.id 
               JOIN categorias cat ON f.id_categoria = cat.id 
               JOIN clubes c ON r.id_club = c.id 
               WHERE r.id = $id_rutina";
    $res_info = mysqli_query($connection, $q_info);
    $row = mysqli_fetch_assoc($res_info);
    if (!$row) continue;

    // Participantes
    $q_part = "SELECT n.nombre, n.apellidos, rp.reserva 
               FROM rutinas_participantes rp 
               JOIN nadadoras n ON rp.id_nadadora = n.id 
               WHERE rp.id_rutina = $id_rutina 
               ORDER BY rp.reserva ASC, n.apellidos ASC";
    $res_part = mysqli_query($connection, $q_part);
    $titulares = [];
    $reservas = [];
    while ($p = mysqli_fetch_assoc($res_part)) {
        if ($p['reserva'] === 'si') {
            $reservas[] = $p['nombre'] . ' ' . $p['apellidos'] . ' (R)';
        } else {
            $titulares[] = $p['nombre'] . ' ' . $p['apellidos'];
        }
    }
    $atletas_str = implode(', ', array_merge($titulares, $reservas));

    // Duración música
    $duracion_str = '';
    if (!empty($row['music_duration']) && $row['music_duration'] > 0) {
        $min = floor($row['music_duration'] / 60);
        $sec = $row['music_duration'] % 60;
        $duracion_str = sprintf("%02d:%02d min", $min, $sec);
    }
    $regla_duracion = getReglamentaryDurationCoachCard($row['nombre_modalidad'], $row['nombre_categoria']);

    // Orden
    $orden_txt = $row['orden'];
    if ($orden_txt == -1) $orden_txt = 'PS (Preswimmer)';
    elseif ($orden_txt == -2) $orden_txt = 'EX (Exhibición)';

    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 8);

    // FICHA DE DATOS GENERALES DE LA RUTINA
    $html = '<table cellpadding="3" style="border:1px solid #cbd5e1; border-collapse:collapse; width:100%; font-size:7.5pt; margin-bottom:5px;">';

    // Fila 1: Club y Marca de Registro
    $html .= '<tr style="background-color:#0f172a; color:#ffffff;">';
    $html .= '<td width="18%" style="font-weight:bold; color:#38bdf8;">CLUB:</td>';
    $html .= '<td width="52%" style="font-weight:bold; font-size:8.5pt;">' . htmlspecialchars($row['nombre_club_largo'] ?: $row['nombre_club']) . '</td>';
    $html .= '<td width="30%" align="right" style="color:#94a3b8; font-size:7pt;">ID: #' . $row['id'] . (!empty($row['marca_cc']) ? ' · ' . htmlspecialchars($row['marca_cc']) : '') . '</td>';
    $html .= '</tr>';

    // Fila 2: Evento y Orden
    $evento_txt = htmlspecialchars($row['nombre_modalidad']) . (($row['fase_genero'] ?? '') === 'masculino' ? ' Masc' : '') . ' ' . htmlspecialchars($row['nombre_categoria']);
    $html .= '<tr style="background-color:#ffffff; border-bottom:1px solid #e2e8f0;">';
    $html .= '<td style="font-weight:bold; color:#475569;">EVENTO:</td>';
    $html .= '<td style="font-weight:bold; color:#0f172a;">' . $evento_txt . '</td>';
    $html .= '<td align="right" style="font-weight:bold; color:#0284c7;">Orden: ' . $orden_txt . '</td>';
    $html .= '</tr>';

    // Fila 3: Temática y Duración
    $tema_txt = !empty($row['tematica']) ? htmlspecialchars($row['tematica']) : 'No especificado';
    $html .= '<tr style="background-color:#f8fafc; border-bottom:1px solid #e2e8f0;">';
    $html .= '<td style="font-weight:bold; color:#475569;">TEMÁTICA:</td>';
    $html .= '<td style="color:#1e293b; font-style:italic;">"' . $tema_txt . '"</td>';
    $html .= '<td align="right" style="color:#64748b;">';
    if (!empty($duracion_str)) $html .= 'Música: <b>' . $duracion_str . '</b>';
    if (!empty($regla_duracion)) $html .= ' (Límite: ' . $regla_duracion . ')';
    $html .= '</td>';
    $html .= '</tr>';

    // Fila 4: Participantes
    $html .= '<tr style="background-color:#ffffff;">';
    $html .= '<td style="font-weight:bold; color:#475569;">ATLETAS:</td>';
    $html .= '<td colspan="2" style="font-size:7pt; color:#334155;">' . (!empty($atletas_str) ? htmlspecialchars($atletas_str) : 'Sin atletas asignadas') . '</td>';
    $html .= '</tr>';

    $html .= '</table>';

    // TABLA DE ELEMENTOS OFICIALES
    $html .= '<table cellpadding="3" style="border:1px solid #0f172a; border-collapse:collapse; width:100%; font-size:7pt; margin-top:4px;">';

    // Cabecera de la tabla
    $html .= '<tr style="background-color:#0f172a; color:#ffffff; font-weight:bold; font-size:7.5pt; text-align:center;">';
    $html .= '<th width="5%" style="border:1px solid #334155;">EL</th>';
    $html .= '<th width="14%" style="border:1px solid #334155;">TIME</th>';
    $html .= '<th width="12%" style="border:1px solid #334155;">PART</th>';
    $html .= '<th width="10%" style="border:1px solid #334155;">BM</th>';
    $html .= '<th width="37%" style="border:1px solid #334155; text-align:left; padding-left:4px;">DIFICULTAD DECLARADA</th>';
    $html .= '<th width="9%" style="border:1px solid #334155;">BONUS</th>';
    $html .= '<th width="7%" style="border:1px solid #334155;">DD</th>';
    $html .= '<th width="6%" style="border:1px solid #334155; background-color:#334155;">TC</th>';
    $html .= '</tr>';

    $elementos_max = intval($row['elementos_coach_card']);
    $dd_total_acumulada = 0;

    for ($i = 1; $i <= $elementos_max; $i++) {
        // 1. Transición previa si existe (texto = 3)
        $q_trans = "SELECT th.nombre, th.color 
                    FROM hibridos_rutina hr 
                    JOIN tipo_hibridos th ON hr.texto = th.id 
                    WHERE hr.tipo = 'part' AND hr.texto = 3 AND hr.id_rutina = $id_rutina AND hr.elemento = $i";
        $res_trans = mysqli_query($connection, $q_trans);
        while ($tr = mysqli_fetch_assoc($res_trans)) {
            $html .= '<tr style="background-color:#f1f5f9; color:#64748b; font-size:6pt; font-weight:bold; text-align:center;">';
            $html .= '<td colspan="7" style="border:1px solid #e2e8f0; padding:2px; letter-spacing:1px;">— ' . htmlspecialchars($tr['nombre']) . ' —</td>';
            $html .= '<td style="border:1px solid #cbd5e1; background-color:#f8fafc;"></td>';
            $html .= '</tr>';
        }

        // 2. Tiempos Inicio y Fin
        $q_ti = mysqli_query($connection, "SELECT texto FROM hibridos_rutina WHERE tipo = 'time_inicio' AND id_rutina = $id_rutina AND elemento = $i LIMIT 1");
        $t_inicio = $q_ti ? (mysqli_fetch_assoc($q_ti)['texto'] ?? '') : '';

        $q_tf = mysqli_query($connection, "SELECT texto FROM hibridos_rutina WHERE tipo = 'time_fin' AND id_rutina = $id_rutina AND elemento = $i LIMIT 1");
        $t_fin = $q_tf ? (mysqli_fetch_assoc($q_tf)['texto'] ?? '') : '';

        $time_str = ($t_inicio !== '' || $t_fin !== '') ? ($t_inicio . ' - ' . $t_fin) : '-';

        // 3. Tipo de Parte (HYBRID, ACROBATIC, TRE)
        $q_part_elem = "SELECT th.nombre, th.color 
                        FROM hibridos_rutina hr 
                        JOIN tipo_hibridos th ON hr.texto = th.id 
                        WHERE hr.tipo = 'part' AND hr.texto != 3 AND hr.id_rutina = $id_rutina AND hr.elemento = $i LIMIT 1";
        $res_part_elem = mysqli_query($connection, $q_part_elem);
        $part_elem = $res_part_elem ? mysqli_fetch_assoc($res_part_elem) : null;
        $part_nombre = $part_elem['nombre'] ?? '-';
        $part_color = $part_elem['color'] ?? '#ffffff';

        // 4. Basemark
        $q_bm = "SELECT texto, valor FROM hibridos_rutina WHERE tipo = 'basemark' AND id_rutina = $id_rutina AND elemento = $i";
        $res_bm = mysqli_query($connection, $q_bm);
        $bm_textos = [];
        while ($bm = mysqli_fetch_assoc($res_bm)) {
            if (!empty($bm['texto'])) {
                $v = floatval($bm['valor']);
                $bm_textos[] = $bm['texto'] . ($v > 0 ? ' (' . number_format($v, 2) . ')' : '');
            }
        }
        $bm_str = !empty($bm_textos) ? implode(' ', $bm_textos) : '-';

        // 5. Dificultad Declarada (Secuencia de movimientos)
        $q_dd = "SELECT texto, valor FROM hibridos_rutina WHERE tipo = 'dd' AND id_rutina = $id_rutina AND elemento = $i AND texto != ''";
        $res_dd = mysqli_query($connection, $q_dd);
        $dd_textos = [];
        while ($dd_item = mysqli_fetch_assoc($res_dd)) {
            $dd_textos[] = $dd_item['texto'];
        }
        $dd_seq_str = !empty($dd_textos) ? implode(' ', $dd_textos) : '-';

        // 6. BONUS (Restaurado para normativa 2026-2027: Cambios de Formación PC..6PC)
        $q_bonus = "SELECT texto, valor FROM hibridos_rutina WHERE tipo = 'bonus' AND id_rutina = $id_rutina AND elemento = $i AND texto != ''";
        $res_bonus = mysqli_query($connection, $q_bonus);
        $bonus_textos = [];
        while ($bon = mysqli_fetch_assoc($res_bonus)) {
            $v_bon = floatval($bon['valor']);
            $bonus_textos[] = $bon['texto'] . ($v_bon > 0 ? ' (+' . number_format($v_bon, 2) . ')' : '');
        }
        $bonus_str = !empty($bonus_textos) ? implode(' ', $bonus_textos) : '-';

        // 7. DD Total del Elemento
        $q_tot = mysqli_query($connection, "SELECT valor FROM hibridos_rutina WHERE tipo = 'total' AND id_rutina = $id_rutina AND elemento = $i LIMIT 1");
        $val_tot = $q_tot ? floatval(mysqli_fetch_assoc($q_tot)['valor'] ?? 0) : 0;
        $dd_total_acumulada += $val_tot;

        $bg_elem = ($i % 2 == 0) ? '#f8fafc' : '#ffffff';

        $html .= '<tr style="background-color:' . $bg_elem . '; border-bottom:1px solid #e2e8f0;">';
        $html .= '<td align="center" style="font-weight:bold; border:1px solid #cbd5e1; font-size:8pt;">' . $i . '</td>';
        $html .= '<td align="center" style="border:1px solid #cbd5e1; font-size:6.5pt; color:#475569;">' . $time_str . '</td>';
        $html .= '<td align="center" style="border:1px solid #cbd5e1; font-weight:bold; font-size:6.5pt; background-color:' . $part_color . '; color:#0f172a;">' . htmlspecialchars($part_nombre) . '</td>';
        $html .= '<td align="center" style="border:1px solid #cbd5e1; font-size:6.5pt; color:#64748b;">' . htmlspecialchars($bm_str) . '</td>';
        $html .= '<td style="border:1px solid #cbd5e1; font-size:7.5pt; font-family:courier; font-weight:bold; color:#0f172a;">' . htmlspecialchars($dd_seq_str) . '</td>';
        $html .= '<td align="center" style="border:1px solid #cbd5e1; font-size:6.5pt; font-weight:bold; color:#0284c7;">' . htmlspecialchars($bonus_str) . '</td>';
        $html .= '<td align="center" style="border:1px solid #cbd5e1; font-size:8pt; font-weight:bold; color:#0f172a;">' . number_format($val_tot, 3) . '</td>';
        $html .= '<td style="border:1px solid #cbd5e1; background-color:#f1f5f9;"></td>'; // TC Check box
        $html .= '</tr>';
    }

    $html .= '</table>';

    // PIE RESUMEN CON DIFICULTAD TOTAL DECLARADA Y FIRMAS
    $dd_declarada_final = ($row['dd_total'] > 0) ? floatval($row['dd_total']) : $dd_total_acumulada;

    $html .= '<br>';
    $html .= '<table cellpadding="4" style="width:100%; border-collapse:collapse; margin-top:6px;">';
    $html .= '<tr>';

    // Caja DD Total Declarada
    $html .= '<td width="45%" style="border:1px solid #0f172a; background-color:#f8fafc; padding:6px;">';
    $html .= '<div style="font-size:7pt; font-weight:bold; color:#64748b; text-transform:uppercase;">DIFICULTAD TOTAL DECLARADA (DD TOTAL):</div>';
    $html .= '<div style="font-size:16pt; font-weight:bold; color:#0f172a; margin-top:2px;">' . number_format($dd_declarada_final, 3) . '</div>';
    $html .= '</td>';

    $html .= '<td width="5%"></td>';

    // Casillas de Firma / Validación
    $html .= '<td width="50%" style="border:1px solid #cbd5e1; background-color:#ffffff; font-size:6.5pt; color:#64748b; padding:6px;">';
    $html .= '<table style="width:100%;">';
    $html .= '<tr>';
    $html .= '<td width="50%" align="center" style="border-right:1px solid #e2e8f0;">';
    $html .= '<b>Firma Entrenador/a:</b><br><br><br><br>_____________________';
    $html .= '</td>';
    $html .= '<td width="50%" align="center">';
    $html .= '<b>Controlador Técnico (DTC):</b><br><br><br><br>_____________________';
    $html .= '</td>';
    $html .= '</tr>';
    $html .= '</table>';
    $html .= '</td>';

    $html .= '</tr>';
    $html .= '</table>';

    $pdf->writeHTML($html, true, false, true, false, '');
}

$dest = $_GET['dest'] ?? 'I';
$output_target = ($dest === 'F' && !empty($_GET['save_path'])) ? $_GET['save_path'] : $nombre_documento;
$pdf->Output($output_target, $dest);
exit();
