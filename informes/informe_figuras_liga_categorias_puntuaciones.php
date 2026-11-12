<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
setlocale(LC_ALL, 'es_ES');

require_once('../tcpdf/tcpdf.php');
include('../database/dbconfig.php');
include('../lib/my_functions.php');

session_start();

$cron_token_expected = "internal_cron_" . md5("SincrmInternalCron" . date('Y-m-d'));
$cron_token_provided = $_GET['cron_token'] ?? '';
$is_authenticated = isset($_SESSION['email']) || isset($_SESSION['username']);

if (!$is_authenticated && $cron_token_provided !== $cron_token_expected) {
    header('Location: ../login.php');
    exit();
}

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
    die("Competición no encontrada.");
}

$header_img = !empty($comp['header_informe']) ? '../' . ltrim($comp['header_informe'], '/') : '';
$footer_img = !empty($comp['footer_informe']) ? '../' . ltrim($comp['footer_informe'], '/') : '';

$GLOBALS['comp_data'] = [
    'id' => (int)$comp['id'],
    'nombre' => $comp['nombre'],
    'nombre_corto' => !empty($comp['nombre_corto']) ? $comp['nombre_corto'] : $comp['nombre'],
    'lugar' => $comp['lugar'] ?? '',
    'piscina' => $comp['piscina'] ?? '',
    'fecha' => dateAFecha($comp['fecha'] ?? ''),
    'header_image' => $header_img,
    'footer_image' => $footer_img,
    'clave_liga' => !empty($comp['clave_liga']) ? trim($comp['clave_liga']) : trim($comp['nombre']),
    'temporada' => $comp['temporada'] ?? '',
    'color' => $comp['color'] ?? '#3b82f6'
];

$titulo_param = $_GET['titulo'] ?? 'Nota Media de Liga';
$GLOBALS['titulo_doc'] = $titulo_param;
$nombre_documento = 'Nota_Media_Liga_' . preg_replace('/[^A-Za-z0-9_]/', '_', $GLOBALS['comp_data']['clave_liga']) . '.pdf';

class SINCRM_LIGA_MEDIA_PDF extends TCPDF {
    public $current_category_name = '';

    public function Header() {
        $comp = $GLOBALS['comp_data'];
        if (!empty($comp['header_image']) && file_exists($comp['header_image'])) {
            $this->Image($comp['header_image'], 12, 7, 186, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
            $this->SetY(23);
        } else {
            $this->SetY(10);
        }

        $this->SetFont('helvetica', 'B', 14);
        $this->SetTextColor(233, 38, 98);
        $titulo_header = mb_strtoupper($GLOBALS['titulo_doc'], 'UTF-8');
        if (!empty($this->current_category_name)) {
            $titulo_header .= ' · ' . mb_strtoupper($this->current_category_name, 'UTF-8');
        }
        $this->Cell(0, 6, $titulo_header, 0, 1, 'C');

        $this->SetFont('helvetica', 'B', 8);
        $this->SetTextColor(100, 116, 139);
        $sub = ($comp['temporada'] ? $comp['temporada'] . ' · ' : '') . $comp['clave_liga'] . ' de Natación Artística FNRM';
        $this->Cell(0, 4, mb_strtoupper($sub, 'UTF-8'), 0, 1, 'C');

        $this->SetDrawColor(233, 38, 98);
        $this->SetLineWidth(0.4);
        $this->Line(12, $this->GetY() + 1.5, 198, $this->GetY() + 1.5);
    }

    public function Footer() {
        $comp = $GLOBALS['comp_data'];
        if (!empty($comp['footer_image']) && file_exists($comp['footer_image'])) {
            $this->SetY(-22);
            $this->Image($comp['footer_image'], 12, $this->GetY(), 130, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }

        $this->SetFont('helvetica', 'B', 7);
        $this->SetTextColor(71, 85, 105);
        $this->setCellPaddings(0, 0, 0, 0);

        $txt_sede = "Sede: " . trim($comp['lugar']);
        $txt_fecha = "Fecha: " . trim($comp['fecha']);
        $txt_pag = "Página " . $this->PageNo() . " de " . $this->getAliasNbPages();

        $this->SetY(-20);
        $this->MultiCell(50, 3.8, $txt_sede, 0, 'R', false, 1, 148, '', true, 0, false, true, 4, 'M', true);
        $this->MultiCell(50, 3.8, $txt_fecha, 0, 'R', false, 1, 148, '', true, 0, false, true, 4, 'M', true);
        $this->MultiCell(50, 3.8, $txt_pag, 0, 'R', false, 1, 148, '', true, 0, false, true, 4, 'M', true);
    }
}

$pdf = new SINCRM_LIGA_MEDIA_PDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator('SINCRM v4.0');
$pdf->SetAuthor('Pedro Díaz');
$pdf->SetTitle($GLOBALS['titulo_doc']);
$pdf->SetMargins(12, 34, 12);
$pdf->SetHeaderMargin(6);
$pdf->SetFooterMargin(12);
$pdf->SetAutoPageBreak(TRUE, 24);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

$clave_liga = $GLOBALS['comp_data']['clave_liga'];
$query_cats = "SELECT id_categoria FROM resultados_figuras_categorias WHERE id_competicion IN (SELECT id FROM competiciones WHERE clave_liga LIKE '".mysqli_real_escape_string($connection, $clave_liga)."') GROUP BY id_categoria ORDER BY id_categoria ASC";
$res_cats = mysqli_query($connection, $query_cats);
$categorias_liga = [];

if ($res_cats) {
    while($rc = mysqli_fetch_assoc($res_cats)) {
        $cid = (string)$rc['id_categoria'];
        if ($cid == '234' || $cid == '235') $cid = '241';
        if (!in_array($cid, $categorias_liga)) $categorias_liga[] = $cid;
    }
}

if (empty($categorias_liga)) {
    $pdf->current_category_name = 'ESTADO DE LA LIGA';
    $pdf->AddPage('P');

    $html = '<div style="background-color: #fef3c7; border: 1px solid #f59e0b; padding: 12px; border-radius: 6px; color: #92400e;">
        <h3 style="color: #b45309; margin: 0 0 6px 0;">ℹ NOTA MEDIA EN ESPERA DE PUNTUACIONES</h3>
        <p style="font-size: 10pt; line-height: 1.5; margin: 0;">Actualmente no constan resultados oficiales calculados para la <b>' . htmlspecialchars($clave_liga) . '</b>.<br>Las notas medias de las nadadoras por categoría se generarán automáticamente una vez computadas las puntuaciones de las fases de figuras.</p>
    </div>';
    $pdf->writeHTML($html, true, false, true, false, '');
} else {
    $total_color = '#DCEDC8';
    foreach ($categorias_liga as $categoria) {
        $q_nom = "SELECT nombre FROM categorias WHERE id = '".$categoria."'";
        $r_nom = mysqli_query($connection, $q_nom);
        $nom_cat = ($r_nom && mysqli_num_rows($r_nom) > 0) ? mysqli_fetch_row($r_nom)[0] : 'Categoría ' . $categoria;

        $pdf->current_category_name = $nom_cat;
        $pdf->AddPage('P');

        $query_j = "SELECT id, nombre_corto, fecha, lugar, color FROM competiciones WHERE clave_liga LIKE '".mysqli_real_escape_string($connection, $clave_liga)."' ORDER BY fecha ASC, id ASC";
        $res_j = mysqli_query($connection, $query_j);
        $jornadas_arr = [];
        while($rj = mysqli_fetch_assoc($res_j)) $jornadas_arr[] = $rj;
        $num_jornadas = count($jornadas_arr);

        $html = '<table align="center" border="1" cellpadding="4" style="border-collapse: collapse; border: 1px solid #cbd5e1;">';
        $html .= '<tr style="font-weight: bold; background-color: #f1f5f9; color: #1e293b;">
                    <th style="width: 5%; text-align: center;">P</th>
                    <th style="width: 35%; text-align: left;">Nadadora</th>
                    <th style="width: 24%; text-align: left;">Club</th>';

        $ancho_j = floor(24 / max(1, $num_jornadas));
        foreach($jornadas_arr as $jl) {
            $col_color = !empty($jl['color']) ? $jl['color'] : '#e2e8f0';
            $html .= '<th style="width: '.$ancho_j.'%; background-color: '.$col_color.'; text-align: center; font-size: 8pt;">' . htmlspecialchars($jl['nombre_corto']) . '<br>' . dateAFecha($jl['fecha']) . '</th>';
        }
        $html .= '<th style="width: 12%; background-color: '.$total_color.'; text-align: center; font-size: 8.5pt;">Nota Media</th></tr>';

        $filtro_sql = ($categoria == '241') ? "id_categoria IN ('241', '234', '235')" : "id_categoria = '".$categoria."'";
        $q_nads = "SELECT DISTINCT id_nadadora FROM resultados_figuras_categorias WHERE {$filtro_sql} AND id_competicion IN (SELECT id FROM competiciones WHERE clave_liga LIKE '".mysqli_real_escape_string($connection, $clave_liga)."')";
        $r_nads = mysqli_query($connection, $q_nads);
        $nadadoras_arr = [];

        if ($r_nads) {
            while ($nad = mysqli_fetch_assoc($r_nads)) {
                $id_nad = (int)$nad['id_nadadora'];
                $q_info = "SELECT n.nombre, n.apellidos, c.nombre_corto AS club_nom FROM nadadoras n LEFT JOIN clubes c ON c.id = n.club WHERE n.id = '$id_nad'";
                $r_info = mysqli_query($connection, $q_info);
                $d_info = $r_info ? mysqli_fetch_assoc($r_info) : null;

                $pts_tot = 0;
                $participacion = 0;
                $row_jornadas = [];

                foreach ($jornadas_arr as $jl) {
                    $id_j = (int)$jl['id'];
                    $q_p = "SELECT COALESCE(nota_final, 0) FROM resultados_figuras_categorias WHERE id_nadadora = '$id_nad' AND id_competicion = '$id_j' LIMIT 1";
                    $r_p = mysqli_query($connection, $q_p);
                    $val_p = ($r_p && mysqli_num_rows($r_p) > 0) ? (float)mysqli_fetch_row($r_p)[0] : 0.0;
                    if ($val_p > 0) {
                        $pts_tot += $val_p;
                        $participacion++;
                        $row_jornadas[$id_j] = number_format($val_p, 4);
                    } else {
                        $row_jornadas[$id_j] = '-';
                    }
                }

                $media = ($participacion > 0) ? round($pts_tot / $participacion, 4) : 0.0;
                if ($pts_tot > 0) {
                    $nadadoras_arr[] = [
                        'nombre' => $d_info ? ($d_info['apellidos'] . ', ' . $d_info['nombre']) : 'Nadadora #' . $id_nad,
                        'club' => $d_info['club_nom'] ?? '',
                        'jornadas' => $row_jornadas,
                        'media' => $media,
                        'media_format' => number_format($media, 4)
                    ];
                }
            }
        }

        usort($nadadoras_arr, function($a, $b) {
            $diff = $b['media'] - $a['media'];
            if (abs($diff) < 0.0001) return 0;
            return ($diff > 0) ? 1 : -1;
        });

        $pos = 0;
        $prev_media = -1;
        $rcnt = 0;
        foreach ($nadadoras_arr as $na) {
            if (abs($na['media'] - $prev_media) > 0.0001) {
                $pos++;
                $prev_media = $na['media'];
            }
            $bg = ($rcnt % 2 === 0) ? '#ffffff' : '#f8fafc';
            $html .= '<tr style="background-color: '.$bg.';">
                <td style="width: 5%; text-align: center; font-weight: bold; font-size: 8.5pt;">' . $pos . '</td>
                <td style="width: 35%; text-align: left; font-size: 8pt;">' . htmlspecialchars($na['nombre']) . '</td>
                <td style="width: 24%; text-align: left; font-size: 8pt;">' . htmlspecialchars($na['club']) . '</td>';
            foreach ($jornadas_arr as $jl) {
                $html .= '<td style="width: '.$ancho_j.'%; text-align: center; font-size: 8pt;">' . $na['jornadas'][$jl['id']] . '</td>';
            }
            $html .= '<td style="width: 12%; text-align: center; font-weight: bold; font-size: 8.5pt; background-color: '.$total_color.';">' . $na['media_format'] . '</td></tr>';
            $rcnt++;
        }

        if (empty($nadadoras_arr)) {
            $html .= '<tr><td colspan="' . (3 + $num_jornadas + 1) . '" align="center" style="color: #94a3b8; padding: 10px;">Sin puntuaciones calculadas.</td></tr>';
        }

        $html .= '</table>';
        $pdf->writeHTML($html, true, false, false, false, '');
    }
}

$pdf->Output($nombre_documento, 'I');
exit();
