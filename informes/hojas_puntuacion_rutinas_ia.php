<?php
//============================================================+
// File name   : hojas_puntuacion_rutinas_ia.php
// Description : Hojas de puntuación para el Panel de Impresión Artística
//               Diseñado para impresión A4 (4 fichas por hoja en rejilla 2x2)
//               y corte milimétrico con guillotina.
//               Casillas de nota maximizadas y juez asignado con firma.
// Author      : Pedro Díaz / SINCRM
//============================================================+

require_once('../tcpdf/tcpdf.php');
require_once('../database/dbconfig.php');
require_once('../lib/my_functions.php');
session_start();

// 1. Obtener parámetros de entrada
$id_competicion = isset($_GET['id_competicion']) ? intval($_GET['id_competicion']) : 0;
$id_fase = isset($_GET['id_fase']) ? intval($_GET['id_fase']) : 0;
$id_categoria = isset($_GET['id_categoria']) ? intval($_GET['id_categoria']) : 0;

// 2. Consulta de la competición
if ($id_competicion > 0) {
    $query = "SELECT * FROM competiciones WHERE id = $id_competicion LIMIT 1";
} else {
    $query = "SELECT * FROM competiciones WHERE activo = 'si' LIMIT 1";
}

$result = mysqli_query($connection, $query);
if (!$result || mysqli_num_rows($result) == 0) {
    die("Competición no encontrada.");
}

$competicion = mysqli_fetch_assoc($result);
$id_competicion_activa = $competicion['id'];
$nombre_competicion_activa = $competicion['nombre'];
$lugar = $competicion['lugar'];
$piscina = $competicion['piscina'];
$fecha = dateAFecha($competicion['fecha']);

// 3. Configuración del documento TCPDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('SINCRM');
$pdf->SetAuthor('Pedro Díaz');
$pdf->SetTitle('Hojas de Puntuación - Impresión Artística - ' . $nombre_competicion_activa);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(0, 0, 0);
$pdf->SetAutoPageBreak(false);

/**
 * Dibuja las guías de corte y marcas de centrado para guillotina
 * Eje vertical central: X = 105.0 mm
 * Eje horizontal central: Y = 148.5 mm
 */
function drawGuillotineGuides($pdf) {
    // 1. Líneas centrales discontinuas
    $pdf->SetLineStyle(array('width' => 0.15, 'cap' => 'butt', 'join' => 'miter', 'dash' => '2,3', 'color' => array(190, 200, 210)));
    $pdf->Line(105, 0, 105, 297);
    $pdf->Line(0, 148.5, 210, 148.5);

    // 2. Marcas de alineación sólidas en los 4 bordes exteriores del papel
    $pdf->SetLineStyle(array('width' => 0.45, 'cap' => 'square', 'join' => 'miter', 'dash' => 0, 'color' => array(71, 85, 105)));
    $pdf->Line(105, 0, 105, 4.0);
    $pdf->Line(105, 293.0, 105, 297);
    $pdf->Line(0, 148.5, 4.0, 148.5);
    $pdf->Line(206.0, 148.5, 210, 148.5);

    // 3. Símbolos de tijera
    $pdf->SetFont('helvetica', '', 7);
    $pdf->SetTextColor(100, 116, 139);
    $pdf->Text(106, 1.2, '✂');
    $pdf->Text(1.2, 149.2, '✂');
}

/**
 * Genera el HTML de la ficha de Impresión Artística
 * Con 3 criterios principales (ChoMu, Performance, Transitions) en casillas amplias de ~30mm de altura
 */
function buildArtisticSlipHTML($comp_nombre, $juez_num, $juez_nombre, $modalidad, $categoria, $orden, $orden_max) {
    $orden_badge = ($orden === '-1' || $orden === 'PS') ? 'PRE-SWIMMER' : 'ORDEN ' . $orden . ' / ' . $orden_max;

    $criterios = [
        [
            'num' => '1',
            'titulo' => 'COREOGRAFÍA Y MÚSICA',
            'sub' => 'Choreo & Music (Uso de música, dinámica, fluidez)'
        ],
        [
            'num' => '2',
            'titulo' => 'PERFORMANCE',
            'sub' => 'Actuación (Expresividad, interpretación, sincronía)'
        ],
        [
            'num' => '3',
            'titulo' => 'TRANSICIONES',
            'sub' => 'Transitions (Variedad, conexiones, creatividad)'
        ]
    ];

    $html = '<div style="border: 1.4px solid #334155; padding: 4.5px; font-family: helvetica;">';

    // Cabecera compacta
    $html .= '<table cellpadding="1.5" cellspacing="0" style="width: 100%; border-bottom: 1.5px solid #0f172a;">';
    $html .= '<tr>';
    $html .= '<td style="width: 65%; font-size: 7pt; font-weight: bold; color: #475569; text-transform: uppercase;">' . htmlspecialchars($comp_nombre) . '</td>';
    $html .= '<td style="width: 35%; text-align: right; font-size: 6.5pt; font-weight: bold; color: #9333ea; text-transform: uppercase;">IMPRESIÓN ARTÍSTICA</td>';
    $html .= '</tr>';
    $html .= '<tr>';
    $html .= '<td style="width: 55%; font-size: 9.5pt; font-weight: bold; color: #0f172a;">' . htmlspecialchars($modalidad . ' ' . $categoria) . '</td>';
    $html .= '<td style="width: 45%; text-align: right;">';
    $html .= '<span style="background-color: #6b21a8; color: #ffffff; font-weight: bold; font-size: 8.5pt; padding: 2px 5px;">&nbsp;JUEZ ' . $juez_num . '&nbsp;</span> ';
    $html .= '<span style="background-color: #e2e8f0; color: #0f172a; font-weight: bold; font-size: 8.5pt; padding: 2px 5px;">&nbsp;' . $orden_badge . '&nbsp;</span>';
    $html .= '</td>';
    $html .= '</tr>';
    $html .= '</table>';

    // Tabla de criterios artísticos maximizados (3 filas amplias de ~90px / ~32mm cada una)
    $html .= '<table cellpadding="3" cellspacing="0" style="width: 100%; margin-top: 4px; border: 1.2px solid #334155;">';
    $html .= '<thead>';
    $html .= '<tr style="background-color: #0f172a; color: #ffffff; font-weight: bold; font-size: 8pt; height: 20px;">';
    $html .= '<th style="width: 9%; text-align: center; border-right: 1px solid #475569;">#</th>';
    $html .= '<th style="width: 53%; text-align: left; border-right: 1px solid #475569;">&nbsp;CRITERIO ARTÍSTICO</th>';
    $html .= '<th style="width: 38%; text-align: center;">PUNTUACIÓN (0 - 10)</th>';
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';

    $row_height_px = 94; // ~33mm por fila
    foreach ($criterios as $idx => $crit) {
        $bg = ($idx % 2 === 0) ? '#ffffff' : '#f8fafc';
        $border_b = ($idx === count($criterios) - 1) ? '' : 'border-bottom: 1.2px solid #cbd5e1;';

        $html .= '<tr style="background-color: ' . $bg . ';">';
        $html .= '<td height="' . $row_height_px . '" style="width: 9%; text-align: center; font-size: 11pt; font-weight: bold; color: #475569; ' . $border_b . ' border-right: 1px solid #cbd5e1; vertical-align: middle;">' . $crit['num'] . '</td>';
        $html .= '<td height="' . $row_height_px . '" style="width: 53%; ' . $border_b . ' border-right: 1.2px solid #334155; vertical-align: middle;">';
        $html .= '<span style="font-size: 9.5pt; font-weight: bold; color: #0f172a;">&nbsp;' . $crit['titulo'] . '</span><br>';
        $html .= '<span style="font-size: 7pt; color: #64748b;">&nbsp;' . $crit['sub'] . '</span>';
        $html .= '</td>';
        // Casilla gigante para escribir la nota
        $html .= '<td height="' . $row_height_px . '" style="width: 38%; background-color: #ffffff; text-align: center; font-size: 16pt; ' . $border_b . ' vertical-align: middle;">&nbsp;</td>';
        $html .= '</tr>';
    }

    $html .= '</tbody>';
    $html .= '</table>';

    // Pie de ficha: Nombre del juez y firma
    $html .= '<table cellpadding="2" cellspacing="0" style="width: 100%; margin-top: 5px;">';
    $html .= '<tr>';
    if (!empty($juez_nombre)) {
        $html .= '<td style="width: 54%; font-size: 7.5pt; color: #1e293b; vertical-align: bottom;">Juez: <b>' . htmlspecialchars($juez_nombre) . '</b></td>';
        $html .= '<td style="width: 46%; text-align: right; font-size: 7.5pt; color: #475569; vertical-align: bottom;">Firma: ____________________</td>';
    } else {
        $html .= '<td style="width: 65%; font-size: 7.5pt; color: #475569; vertical-align: bottom;">Firma Juez ' . $juez_num . ': ___________________________</td>';
        $html .= '<td style="width: 35%; text-align: right; font-size: 6.5pt; font-weight: bold; color: #94a3b8; vertical-align: bottom;">SINCRM</td>';
    }
    $html .= '</tr>';
    $html .= '</table>';

    $html .= '</div>';
    return $html;
}

// Coordenadas fijas para los 4 cuadrantes (A4 = 210 x 297 mm)
$quadrant_coords = [
    [5.0, 5.0],       // Cuadrante 0: Superior Izquierda
    [110.0, 5.0],     // Cuadrante 1: Superior Derecha
    [5.0, 153.5],     // Cuadrante 2: Inferior Izquierda
    [110.0, 153.5]    // Cuadrante 3: Inferior Derecha
];

$slot = 0;

// 4. Consulta de fases de la competición
$query_fases = "SELECT id_categoria, id_modalidad, id, orden, elementos_coach_card 
                FROM fases 
                WHERE id_competicion = '$id_competicion_activa' ";

if ($id_fase > 0) {
    $query_fases .= " AND id = $id_fase ";
}
if ($id_categoria > 0) {
    $query_fases .= " AND id_categoria = $id_categoria ";
}

$query_fases .= " ORDER BY orden";
$fases = mysqli_query($connection, $query_fases);

while ($fase = mysqli_fetch_array($fases)) {
    $q_cat = "SELECT nombre FROM categorias WHERE id = '" . $fase['id_categoria'] . "'";
    $nombre_categoria = mysqli_result(mysqli_query($connection, $q_cat), 0);

    $q_mod = "SELECT nombre FROM modalidades WHERE id = '" . $fase['id_modalidad'] . "'";
    $nombre_modalidad = mysqli_result(mysqli_query($connection, $q_mod), 0);

    // Jueces del panel tipo 2 (Impresión Artística)
    $q_jueces = "SELECT paneles.nombre as panel_nombre, panel_jueces.numero_juez, jueces.nombre as juez_nombre, jueces.apellidos as juez_apellidos 
                 FROM panel_jueces 
                 INNER JOIN paneles ON panel_jueces.id_panel = paneles.id 
                 LEFT JOIN jueces ON panel_jueces.id_juez = jueces.id AND jueces.nombre NOT LIKE 'Media' 
                 WHERE panel_jueces.id_fase = '" . $fase['id'] . "' 
                   AND paneles.id_paneles_tipo = 2 
                 ORDER BY panel_jueces.numero_juez";
    $jueces = mysqli_query($connection, $q_jueces);

    while ($juez = mysqli_fetch_array($jueces)) {
        $nombre_completo_juez = '';
        if (!empty($juez['juez_nombre'])) {
            $nombre_completo_juez = trim($juez['juez_nombre'] . ' ' . $juez['juez_apellidos']);
        }

        // Rutinas de la fase
        $q_ordenes = "SELECT orden, id FROM rutinas WHERE id_fase = '" . $fase['id'] . "' ORDER BY orden";
        $ordenes = mysqli_query($connection, $q_ordenes);

        $q_max = "SELECT orden, id FROM rutinas WHERE id_fase = '" . $fase['id'] . "' AND orden > 0 ORDER BY orden";
        $orden_maximo = mysqli_num_rows(mysqli_query($connection, $q_max));

        while ($orden = mysqli_fetch_assoc($ordenes)) {
            $orden_val = ($orden['orden'] == '-1') ? 'PS' : $orden['orden'];

            if ($slot === 0) {
                $pdf->AddPage();
                drawGuillotineGuides($pdf);
            }

            $slip_html = buildArtisticSlipHTML(
                $nombre_competicion_activa,
                $juez['numero_juez'],
                $nombre_completo_juez,
                $nombre_modalidad,
                $nombre_categoria,
                $orden_val,
                $orden_maximo
            );

            $pdf->writeHTMLCell(95.0, 138.5, $quadrant_coords[$slot][0], $quadrant_coords[$slot][1], $slip_html, 0, 0, false, true, 'L', true);

            $slot = ($slot + 1) % 4;
        }
    }
}

// 5. Salida del PDF con cabeceras anti-caché estrictas
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
$pdf->Output('hojas_puntuacion_artistica_' . $id_competicion_activa . '.pdf', 'I');
