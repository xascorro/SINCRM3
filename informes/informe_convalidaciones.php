<?php
/**
 * Informe Oficial de Convalidaciones de Figuras para Pases de Nivel (PDF)
 * Sistema SINCRM v4 - FNRM / RFEN
 */
ini_set('display_errors', 0);
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

// 1. Control de Acceso: Administradores (1, 2, 3) y Usuarios Club (5) o Token Cron
$cron_token_expected = "internal_cron_" . md5("SincrmInternalCron" . date('Y-m-d'));
$cron_token_provided = $_GET['cron_token'] ?? '';

$rol_usuario = (int)($_SESSION['id_rol'] ?? 0);
$es_admin = in_array($rol_usuario, [1, 2, 3]);
$es_club = ($rol_usuario === 5);

if (!$es_admin && !$es_club && $cron_token_provided !== $cron_token_expected) {
    header('Location: ../login.php');
    exit();
}

// 1.1 Filtrado de Club (Estricto para Club, Opcional para Admin)
$club_id_filtro = 0;
$club_nombre_filtro = '';

if ($es_club) {
    $club_id_filtro = (int)($_SESSION['club'] ?? 0);
    if ($club_id_filtro === 0 && (($_SESSION['email'] ?? '') === 'elenacerondiaz@gmail.com' || ($_SESSION['username'] ?? '') === 'Elena Cerón')) {
        $club_id_filtro = 1; // Club Sincro Alhama
    }
} elseif (isset($_GET['club']) && intval($_GET['club']) > 0) {
    $club_id_filtro = intval($_GET['club']);
}

if ($club_id_filtro > 0) {
    $res_cl_info = mysqli_query($connection, "SELECT nombre, nombre_corto FROM clubes WHERE id = '$club_id_filtro'");
    if ($res_cl_info && $cl_row = mysqli_fetch_assoc($res_cl_info)) {
        $club_nombre_filtro = !empty($cl_row['nombre_corto']) ? $cl_row['nombre_corto'] : $cl_row['nombre'];
    }
}

// 2. Parámetros de la Competición y Referencias
$id_comp_pase = isset($_GET['comp_pase']) ? intval($_GET['comp_pase']) : 83;
$vista_censo = isset($_GET['censo']) && $_GET['censo'] === 'todos' ? 'todos' : 'inscritas';

// Obtener datos del Pase de Nivel
$res_pase = mysqli_query($connection, "SELECT * FROM competiciones WHERE id = '$id_comp_pase'");
$comp_pase = mysqli_fetch_assoc($res_pase);

if (!$comp_pase) {
    die("Competición de Pase de Nivel no encontrada.");
}

$temporada_actual = $comp_pase['temporada'] ?? '2026-2027';

if (!isset($_GET['comps_ref'])) {
    $q_def_refs = "SELECT id FROM competiciones 
                   WHERE figuras IN ('si', 'mixta') 
                     AND LOWER(nombre) NOT LIKE '%pase%' 
                     AND LOWER(nombre) NOT LIKE '%nivel%'
                     AND temporada = '$temporada_actual'
                     AND fecha <= CURDATE()
                   ORDER BY fecha DESC";
    $res_def_refs = mysqli_query($connection, $q_def_refs);
    $comps_referencia = [];
    while ($dr = mysqli_fetch_assoc($res_def_refs)) {
        $comps_referencia[] = (int)$dr['id'];
    }
    if (empty($comps_referencia)) $comps_referencia = [81, 82];
} else {
    $comps_referencia = array_map('intval', (array)$_GET['comps_ref']);
}
if (empty($comps_referencia)) $comps_referencia = [81, 82];

// Obtener datos de competiciones de referencia
$ref_ids_str = implode(',', $comps_referencia);
$nombres_ref = [];
$res_ref_names = mysqli_query($connection, "SELECT id, nombre, fecha FROM competiciones WHERE id IN ($ref_ids_str) ORDER BY fecha ASC");
while ($r_ref = mysqli_fetch_assoc($res_ref_names)) {
    $nombres_ref[] = $r_ref['nombre'] . ' (#' . $r_ref['id'] . ')';
}
$nombres_ref_txt = implode(' y ', $nombres_ref);

// Datos globales para Header y Footer TCPDF
$header_path = !empty($comp_pase['header_informe']) ? __DIR__ . '/../' . $comp_pase['header_informe'] : '';
$footer_path = !empty($comp_pase['footer_informe']) ? __DIR__ . '/../' . $comp_pase['footer_informe'] : '';
if (empty($footer_path) || !file_exists($footer_path)) {
    $footer_path = __DIR__ . '/../images/footer_reg20262027.jpg';
}

$GLOBALS["comp_data"] = [
    "nombre" => $comp_pase['nombre'],
    "lugar" => $comp_pase['lugar'],
    "fecha" => dateAFecha($comp_pase['fecha']),
    "organizador" => $comp_pase['organizador'],
    "header" => $header_path,
    "footer" => $footer_path,
    "club_subtitulo" => $club_nombre_filtro
];

$sufijo_archivo = !empty($club_nombre_filtro) ? '_' . str_replace(' ', '_', $club_nombre_filtro) : '';
$nombre_archivo = "Convalidaciones_Figuras" . $sufijo_archivo . "_" . str_replace(' ', '_', $comp_pase['nombre']) . ".pdf";

// 3. Configuración Oficial de Ternas según Normativa RFEN / FNRM 2026/2027
$config_ternas = [
    'Alevin I' => [
        'nombre' => 'Alevín I',
        'subtitulo' => 'Hasta 11 años (2016 y posteriores) · Normativa Niveles RFEN',
        'corte' => 4.0000,
        'color' => '#059669', // Emerald
        'bg_color' => '#ecfdf5',
        'border_color' => '#a7f3d0',
        'figuras' => [
            '140k' => ['ejercicio' => 12, 'nombre' => 'Flamenco Rodilla Doblada Tirabuzón Continuo 360º', 'gd' => 2.5],
            '423'  => ['ejercicio' => 13, 'nombre' => 'Ariana', 'gd' => 2.5],
            '355a' => ['ejercicio' => 14, 'nombre' => 'Marsopa Medio Giro', 'gd' => 2.2]
        ]
    ],
    'Alevin II' => [
        'nombre' => 'Alevín II',
        'subtitulo' => '12 años (Nacidas en 2015) · Normativa Niveles RFEN',
        'corte' => 4.5000,
        'color' => '#d97706', // Amber
        'bg_color' => '#fffbeb',
        'border_color' => '#fde68a',
        'figuras' => [
            '140k' => ['ejercicio' => 11, 'nombre' => 'Flamenco Rodilla Doblada Tirabuzón Continuo 360º', 'gd' => 2.5],
            '423'  => ['ejercicio' => 12, 'nombre' => 'Ariana', 'gd' => 2.5],
            '301h' => ['ejercicio' => 13, 'nombre' => 'Barracuda Tirabuzón Ascendente 180º', 'gd' => 2.4]
        ]
    ],
    'Infantil' => [
        'nombre' => 'Infantil',
        'subtitulo' => '13 a 15 años (Nacidas 2012 - 2014) · Normativa Niveles RFEN',
        'corte' => 5.0000,
        'color' => '#4f46e5', // Indigo
        'bg_color' => '#eef2ff',
        'border_color' => '#c7d2fe',
        'figuras' => [
            '367'  => ['ejercicio' => 10, 'nombre' => 'Barracuda Rodilla Doblada Twirl 180º', 'gd' => 2.3],
            '356J' => ['ejercicio' => 11, 'nombre' => 'Whip Tirabuzón Combinado 360º', 'gd' => 3.4],
            '407'  => ['ejercicio' => 12, 'nombre' => 'Pez Espada Pierna Estirada con Rotación de Ariana', 'gd' => 2.6]
        ]
    ]
];

// Censo oficial de nadadoras inscritas al Pase de Nivel 1 (Comp. 83)
$inscritas_comp_83 = [
    'Alevin I'  => [459, 608],
    'Alevin II' => [483, 85, 399, 400, 438, 283],
    'Infantil'  => [94, 83, 279, 264, 84, 278, 485, 90, 514]
];

// 4. Motor de Cálculo de Convalidaciones
$calculo_categorias = [];
$total_nadadoras = 0;
$total_convalidadas_full = 0;
$total_parciales = 0;
$total_pendientes = 0;

foreach ($config_ternas as $cat_key => $cat_data) {
    $calculo_categorias[$cat_key] = [
        'info' => $cat_data,
        'nadadoras' => [],
        'stats' => ['full' => 0, 'parcial' => 0, 'cero' => 0]
    ];
    
    // Obtener lista de nadadoras
    $lista_nadadoras = [];
    $where_club = ($club_id_filtro > 0) ? " AND n.club = '$club_id_filtro' " : "";

    if ($vista_censo === 'inscritas' && $id_comp_pase == 83 && isset($inscritas_comp_83[$cat_key])) {
        $ids_censo = implode(',', $inscritas_comp_83[$cat_key]);
        $q_nads = "SELECT n.id, n.apellidos, n.nombre, n.año_nacimiento, cl.nombre_corto as club
                   FROM nadadoras n
                   LEFT JOIN clubes cl ON n.club = cl.id
                   WHERE n.id IN ($ids_censo) $where_club
                   ORDER BY cl.nombre_corto ASC, n.apellidos ASC";
        $res_nads = mysqli_query($connection, $q_nads);
        while ($r = mysqli_fetch_assoc($res_nads)) {
            $lista_nadadoras[] = $r;
        }
    } else {
        // Todas las que tienen puntuación en las referencias
        $codigos_in = "'" . implode("','", array_keys($cat_data['figuras'])) . "'";
        $q_nads = "SELECT DISTINCT n.id, n.apellidos, n.nombre, n.año_nacimiento, cl.nombre_corto as club
                   FROM inscripciones_figuras i
                   JOIN fases f ON i.id_fase = f.id
                   JOIN figuras fig ON f.id_figura = fig.id
                   JOIN nadadoras n ON i.id_nadadora = n.id
                   LEFT JOIN clubes cl ON n.club = cl.id
                   WHERE f.id_competicion IN ($ref_ids_str)
                     AND fig.numero IN ($codigos_in)
                     $where_club
                   ORDER BY cl.nombre_corto ASC, n.apellidos ASC";
        $res_nads = mysqli_query($connection, $q_nads);
        while ($r = mysqli_fetch_assoc($res_nads)) {
            $lista_nadadoras[] = $r;
        }
    }
    
    foreach ($lista_nadadoras as $nad) {
        $id_nad = $nad['id'];
        $eval_figs = [];
        $figs_convalidadas = 0;
        $figs_pendientes = [];
        
        foreach ($cat_data['figuras'] as $cod_fig => $info_fig) {
            $q_nota = "SELECT MAX(i.nota_media) as mejor_nota
                       FROM inscripciones_figuras i
                       JOIN fases f ON i.id_fase = f.id
                       JOIN figuras fig ON f.id_figura = fig.id
                       WHERE i.id_nadadora = '$id_nad' 
                         AND fig.numero = '$cod_fig'
                         AND f.id_competicion IN ($ref_ids_str)";
            $res_nota = mysqli_query($connection, $q_nota);
            $row_nota = mysqli_fetch_assoc($res_nota);
            $nota = ($row_nota && $row_nota['mejor_nota'] !== null) ? floatval($row_nota['mejor_nota']) : null;
            
            $es_apto = ($nota !== null && $nota >= $cat_data['corte']);
            if ($es_apto) {
                $figs_convalidadas++;
            } else {
                $figs_pendientes[] = "Ej. " . $info_fig['ejercicio'] . " (" . $cod_fig . ")";
            }
            
            $eval_figs[$cod_fig] = [
                'nota' => $nota,
                'apto' => $es_apto,
                'nombre' => $info_fig['nombre'],
                'ejercicio' => $info_fig['ejercicio'],
                'gd' => $info_fig['gd']
            ];
        }
        
        $total_nadadoras++;
        if ($figs_convalidadas === 3) {
            $dictamen = "CONVALIDA TODO";
            $dictamen_color = "#059669";
            $dictamen_bg = "#ecfdf5";
            $calculo_categorias[$cat_key]['stats']['full']++;
            $total_convalidadas_full++;
        } elseif ($figs_convalidadas > 0) {
            $dictamen = "PARCIAL (" . $figs_convalidadas . "/3)";
            $dictamen_color = "#d97706";
            $dictamen_bg = "#fffbeb";
            $calculo_categorias[$cat_key]['stats']['parcial']++;
            $total_parciales++;
        } else {
            $dictamen = "0 DE 3";
            $dictamen_color = "#dc2626";
            $dictamen_bg = "#fef2f2";
            $calculo_categorias[$cat_key]['stats']['cero']++;
            $total_pendientes++;
        }
        
        $calculo_categorias[$cat_key]['nadadoras'][] = [
            'id' => $nad['id'],
            'apellidos' => $nad['apellidos'],
            'nombre' => $nad['nombre'],
            'año' => $nad['año_nacimiento'],
            'club' => $nad['club'] ?: 'S/C',
            'figuras' => $eval_figs,
            'convalidadas' => $figs_convalidadas,
            'pendientes' => $figs_pendientes,
            'dictamen' => $dictamen,
            'dictamen_color' => $dictamen_color,
            'dictamen_bg' => $dictamen_bg
        ];
    }
}

// 5. Clase TCPDF Personalizada para Convalidaciones
class SINCRM_Convalidaciones_PDF extends TCPDF {
    public function Header() {
        $headerData = $GLOBALS["comp_data"];
        $primary_pink = [233, 38, 98];
        
        // Header Image si existe
        if (!empty($headerData['header']) && file_exists($headerData['header'])) {
            $this->Image($headerData['header'], 10, 8, 190, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
        
        // Título del Informe
        $this->SetY(12);
        $this->SetFont('helvetica', 'B', 15);
        $this->SetTextColor($primary_pink[0], $primary_pink[1], $primary_pink[2]);
        $this->Cell(0, 8, 'CONVALIDACIONES DE FIGURAS · PASE DE NIVEL', 0, 1, 'C');
        
        $this->SetFont('helvetica', 'B', 9);
        $this->SetTextColor(100, 116, 139); // Slate-500
        $this->Cell(0, 5, mb_strtoupper($headerData['nombre']) . ' · ' . $headerData['lugar'] . ' (' . $headerData['fecha'] . ')', 0, 1, 'C');
        
        // Línea decorativa
        $this->SetDrawColor($primary_pink[0], $primary_pink[1], $primary_pink[2]);
        $this->SetLineWidth(0.4);
        $this->Line(15, 27, 195, 27);
    }

    public function Footer() {
        $headerData = $GLOBALS["comp_data"];
        
        // Footer Image si existe
        $this->SetY(-22);
        if (!empty($headerData['footer']) && file_exists($headerData['footer'])) {
            $this->Image($headerData['footer'], 15, $this->GetY(), 180, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }

        // Datos del pie de página (alineados a la derecha)
        $this->SetFont('helvetica', 'B', 7);
        $this->SetTextColor(100, 116, 139);
        
        $txt_pie = "Convalidación para " . $headerData['nombre'] . " · Página " . $this->PageNo() . " de " . $this->getAliasNbPages();

        $this->SetY(-15);
        $this->SetX(15);
        $this->Cell(180, 4, $txt_pie, 0, 1, 'R');
    }
}

// 6. Instanciación y Configuración del Documento
$pdf = new SINCRM_Convalidaciones_PDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('SINCRM v4');
$pdf->SetAuthor('FNRM - Natación Artística');
$pdf->SetTitle('Convalidaciones de Figuras - ' . $comp_pase['nombre']);
$pdf->SetMargins(12, 32, 12);
$pdf->SetAutoPageBreak(TRUE, 24);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

$pdf->AddPage();
$pdf->SetFont('helvetica', '', 8);

// 7. Construcción del Contenido HTML
$html = '
<style>
    table { border-collapse: collapse; width: 100%; }
    .kpi-table { margin-bottom: 8px; }
    .kpi-cell { border: 1px solid #e2e8f0; background-color: #f8fafc; padding: 4px 6px; text-align: center; }
    .kpi-num { font-size: 11pt; font-weight: bold; color: #0f172a; }
    .kpi-lbl { font-size: 6.5pt; font-weight: bold; color: #64748b; text-transform: uppercase; }

    .cat-header { background-color: #1e293b; color: #ffffff; font-size: 8.5pt; font-weight: bold; padding: 4px 6px; }
    .cat-sub { background-color: #f1f5f9; color: #475569; font-size: 7pt; padding: 3px 6px; border-bottom: 1px solid #cbd5e1; }

    .th-col { background-color: #f8fafc; color: #475569; font-size: 7pt; font-weight: bold; border: 1px solid #cbd5e1; padding: 4px 2px; text-align: center; }
    .td-cell { border: 1px solid #e2e8f0; font-size: 7.5pt; padding: 3px 4px; vertical-align: middle; }
    .td-alt { background-color: #f8fafc; }
    .tag-ej { font-size: 6.5pt; color: #64748b; background-color: #e2e8f0; padding: 1px 3px; font-weight: bold; }
    .nota-apto { color: #059669; font-weight: bold; font-size: 8pt; }
    .nota-no { color: #dc2626; font-weight: bold; font-size: 8pt; }
</style>

<!-- Banner Resumen Técnico -->
<table class="kpi-table" cellpadding="3">
    <tr>
        <td width="25%" class="kpi-cell">
            <div class="kpi-num">' . $total_nadadoras . '</div>
            <div class="kpi-lbl">Deportistas Evaluadas</div>
        </td>
        <td width="25%" class="kpi-cell" style="background-color: #ecfdf5; border-color: #a7f3d0;">
            <div class="kpi-num" style="color: #059669;">' . $total_convalidadas_full . '</div>
            <div class="kpi-lbl" style="color: #047857;">Convalida Todo (3/3)</div>
        </td>
        <td width="25%" class="kpi-cell" style="background-color: #fffbeb; border-color: #fde68a;">
            <div class="kpi-num" style="color: #d97706;">' . $total_parciales . '</div>
            <div class="kpi-lbl" style="color: #b45309;">Convalida Parcial</div>
        </td>
        <td width="25%" class="kpi-cell" style="background-color: #fef2f2; border-color: #fecaca;">
            <div class="kpi-num" style="color: #dc2626;">' . $total_pendientes . '</div>
            <div class="kpi-lbl" style="color: #b91c1c;">Sin Convalidar (0/3)</div>
        </td>
    </tr>
</table>

<div style="font-size: 7pt; color: #64748b; margin-bottom: 8px;">
    <b>Competiciones de referencia aplicadas:</b> ' . htmlspecialchars($nombres_ref_txt) . '<br>
    <b>Criterio normativo:</b> Baremos y ejercicios/figuras oficiales de la RFEN y FNRM 2026/2027. Convalidación figura a figura (1 a 1).
</div>
';

// Renderizado por cada categoría
foreach ($calculo_categorias as $cat_key => $cat_data) {
    $c_info = $cat_data['info'];
    $nads = $cat_data['nadadoras'];
    $corte_txt = number_format($c_info['corte'], 2);
    
    // Lista de figuras en subtítulo
    $figs_desc = [];
    foreach ($c_info['figuras'] as $fcod => $fitem) {
        $figs_desc[] = "<b>Ej. " . $fitem['ejercicio'] . "</b>: " . $fcod . " (" . $fitem['nombre'] . " - GD " . $fitem['gd'] . ")";
    }
    $figs_desc_txt = implode(' | ', $figs_desc);

    $html .= '
    <div style="height: 6px;"></div>
    <table cellpadding="3">
        <tr>
            <td class="cat-header" style="background-color: ' . $c_info['color'] . ';">
                ' . mb_strtoupper($c_info['nombre']) . ' · NOTA MÍNIMA: ' . $corte_txt . '
            </td>
        </tr>
        <tr>
            <td class="cat-sub">
                ' . $figs_desc_txt . '
            </td>
        </tr>
    </table>

    <table cellpadding="3">
        <thead>
            <tr>
                <th width="26%" class="th-col" style="text-align: left; padding-left: 4px;">Deportista</th>
                <th width="9%" class="th-col">Club</th>';

    foreach ($c_info['figuras'] as $fcod => $fitem) {
        $html .= '<th width="12%" class="th-col">
            <span class="tag-ej">Ej. ' . $fitem['ejercicio'] . '</span><br>
            <b>' . $fcod . '</b>
        </th>';
    }

    $html .= '
                <th width="14%" class="th-col">Dictamen</th>
                <th width="15%" class="th-col" style="text-align: left; padding-left: 4px;">Obligación en el Pase</th>
            </tr>
        </thead>
        <tbody>';

    if (empty($nads)) {
        $html .= '<tr><td colspan="7" class="td-cell" style="text-align:center; color:#94a3b8; padding: 8px;">No hay nadadoras registradas en esta categoría para los filtros seleccionados.</td></tr>';
    } else {
        $fila_idx = 0;
        foreach ($nads as $nad) {
            $fila_idx++;
            $bg_cls = ($fila_idx % 2 === 0) ? ' class="td-cell td-alt"' : ' class="td-cell"';

            $html .= '<tr nobr="true">';
            
            // Deportista
            $html .= '<td width="26%"' . $bg_cls . ' style="text-align: left;">
                <b>' . htmlspecialchars($nad['apellidos'] . ', ' . $nad['nombre']) . '</b>
                <span style="font-size: 6.5pt; color: #64748b;">(' . $nad['año'] . ')</span>
            </td>';

            // Club
            $html .= '<td width="9%"' . $bg_cls . ' style="text-align: center;">
                <b>' . htmlspecialchars($nad['club']) . '</b>
            </td>';

            // Las 3 Figuras
            foreach ($c_info['figuras'] as $fcod => $fitem) {
                $eval = $nad['figuras'][$fcod];
                $nota = $eval['nota'];
                $apto = $eval['apto'];

                $html .= '<td width="12%"' . $bg_cls . ' style="text-align: center;">';
                if ($nota !== null) {
                    $nota_cls = $apto ? 'nota-apto' : 'nota-no';
                    $badge_txt = $apto ? 'APTO' : 'NO';
                    $badge_bg = $apto ? '#d1fae5' : '#fee2e2';
                    $badge_col = $apto ? '#065f46' : '#991b1b';
                    $html .= '<span class="' . $nota_cls . '">' . number_format($nota, 4) . '</span><br>';
                    $html .= '<span style="font-size: 6pt; font-weight: bold; background-color: ' . $badge_bg . '; color: ' . $badge_col . '; padding: 0.5px 3px; border-radius: 2px;">' . $badge_txt . '</span>';
                } else {
                    $html .= '<span style="color: #94a3b8; font-size: 6.5pt;">--<br>(No nadada)</span>';
                }
                $html .= '</td>';
            }

            // Dictamen
            $html .= '<td width="14%"' . $bg_cls . ' style="text-align: center;">
                <span style="font-size: 6.5pt; font-weight: bold; background-color: ' . $nad['dictamen_bg'] . '; color: ' . $nad['dictamen_color'] . '; padding: 1.5px 4px; border-radius: 2px;">
                    ' . $nad['dictamen'] . '
                </span>
            </td>';

            // Obligación en el Pase
            $html .= '<td width="15%"' . $bg_cls . ' style="text-align: left;">';
            if ($nad['convalidadas'] == 3) {
                $html .= '<span style="color: #059669; font-weight: bold; font-size: 7pt;">EXENTA DE FIGURAS</span>';
            } else {
                $html .= '<span style="color: #dc2626; font-weight: bold; font-size: 6.5pt;">Debe nadar:<br></span>';
                $html .= '<span style="color: #1e293b; font-size: 6.5pt;">' . implode(', ', $nad['pendientes']) . '</span>';
            }
            $html .= '</td>';

            $html .= '</tr>';
        }
    }

    $html .= '</tbody></table>';
}

// Bloque de Diligencia / Firma al pie del acta
$html .= '
<div style="height: 12px;"></div>
<table cellpadding="4" style="border: 1px solid #cbd5e1; background-color: #f8fafc;" nobr="true">
    <tr>
        <td width="33%" style="text-align: center; border-right: 1px solid #cbd5e1;">
            <span style="font-size: 7pt; font-weight: bold; color: #475569;">EL / LA JUEZ ÁRBITRO</span><br><br><br><br>
            <span style="font-size: 6.5pt; color: #94a3b8;">Fdo.: ___________________________</span>
        </td>
        <td width="34%" style="text-align: center; border-right: 1px solid #cbd5e1;">
            <span style="font-size: 7pt; font-weight: bold; color: #475569;">SECRETARÍA TÉCNICA</span><br><br><br><br>
            <span style="font-size: 6.5pt; color: #94a3b8;">Fdo.: ___________________________</span>
        </td>
        <td width="33%" style="text-align: center;">
            <span style="font-size: 7pt; font-weight: bold; color: #475569;">DELEGADO / A FEDERATIVO</span><br><br><br><br>
            <span style="font-size: 6.5pt; color: #94a3b8;">Fdo.: ___________________________</span>
        </td>
    </tr>
</table>
';

$pdf->writeHTML($html, true, false, false, false, '');

// Salida del PDF: Visualización directa en el navegador ('I')
$pdf->Output($nombre_archivo, 'I');
?>