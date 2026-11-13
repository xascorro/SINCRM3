<?php
//============================================================+
// File name   : hojas_puntuacion_rutinas_elementos.php
// Description : Hojas de puntuación para el Panel de Elementos
//               Diseñado para impresión A4 (4 fichas por hoja en rejilla 2x2)
//               y corte milimétrico con guillotina.
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
$pdf->SetTitle('Hojas de Puntuación - Elementos - ' . $nombre_competicion_activa);
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
    // 1. Líneas centrales discontinuas (muy sutiles para no ensuciar la ficha)
    $pdf->SetLineStyle(array('width' => 0.15, 'cap' => 'butt', 'join' => 'miter', 'dash' => '2,3', 'color' => array(190, 200, 210)));
    $pdf->Line(105, 0, 105, 297);
    $pdf->Line(0, 148.5, 210, 148.5);

    // 2. Marcas de alineación sólidas en los 4 bordes del papel (para guillotina)
    $pdf->SetLineStyle(array('width' => 0.45, 'cap' => 'square', 'join' => 'miter', 'dash' => 0, 'color' => array(71, 85, 105)));
    // Borde superior e inferior (X = 105mm)
    $pdf->Line(105, 0, 105, 4.0);
    $pdf->Line(105, 293.0, 105, 297);
    // Borde izquierdo y derecho (Y = 148.5mm)
    $pdf->Line(0, 148.5, 4.0, 148.5);
    $pdf->Line(206.0, 148.5, 210, 148.5);

    // 3. Símbolo sutil de corte en los extremos
    $pdf->SetFont('helvetica', '', 7);
    $pdf->SetTextColor(100, 116, 139);
    $pdf->Text(106, 1.2, '✂');
    $pdf->Text(1.2, 149.2, '✂');
}

/**
 * Genera el HTML de una ficha individual de puntuación
 */
function buildElementSlipHTML($comp_nombre, $juez_num, $modalidad, $categoria, $orden, $orden_max, $elementos, $num_elementos) {
    $orden_badge = ($orden === '-1' || $orden === 'PS') ? 'PRE-SWIMMER' : 'ORDEN ' . $orden . ' / ' . $orden_max;
    $total_rows = max(8, intval($num_elementos));

    $html = '<div style="border: 1.2px solid #64748b; padding: 4px; font-family: helvetica;">';

    // Cabecera de la ficha
    $html .= '<table cellpadding="1.5" cellspacing="0" style="width: 100%; border-bottom: 1.5px solid #0f172a;">';
    $html .= '<tr>';
    $html .= '<td style="width: 68%; font-size: 7pt; font-weight: bold; color: #475569; text-transform: uppercase;">' . htmlspecialchars($comp_nombre) . '</td>';
    $html .= '<td style="width: 32%; text-align: right; font-size: 7pt; font-weight: bold; color: #64748b;">PANEL ELEMENTOS</td>';
    $html .= '</tr>';
    $html .= '<tr>';
    $html .= '<td style="width: 58%; font-size: 9.5pt; font-weight: bold; color: #0f172a;">' . htmlspecialchars($modalidad . ' ' . $categoria) . '</td>';
    $html .= '<td style="width: 42%; text-align: right;">';
    $html .= '<span style="background-color: #0f172a; color: #ffffff; font-weight: bold; font-size: 8.5pt; padding: 2px 5px;">&nbsp;JUEZ ' . $juez_num . '&nbsp;</span> ';
    $html .= '<span style="background-color: #e2e8f0; color: #0f172a; font-weight: bold; font-size: 8.5pt; padding: 2px 5px;">&nbsp;' . $orden_badge . '&nbsp;</span>';
    $html .= '</td>';
    $html .= '</tr>';
    $html .= '</table>';

    // Tabla de elementos y puntuación
    $html .= '<table cellpadding="2.8" cellspacing="0" style="width: 100%; margin-top: 3.5px; border: 1px solid #94a3b8;">';
    $html .= '<thead>';
    $html .= '<tr style="background-color: #0f172a; color: #ffffff; font-weight: bold; font-size: 7.5pt;">';
    $html .= '<th style="width: 10%; text-align: center; border-right: 1px solid #475569;">#</th>';
    $html .= '<th style="width: 60%; text-align: left; border-right: 1px solid #475569;">&nbsp;ELEMENTO / TÉCNICA</th>';
    $html .= '<th style="width: 30%; text-align: center;">NOTA</th>';
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';

    for ($i = 1; $i <= $total_rows; $i++) {
        $elem = isset($elementos[$i]) ? $elementos[$i] : '';
        $bg = ($i % 2 === 0) ? '#f8fafc' : '#ffffff';
        $border_b = ($i === $total_rows) ? '' : 'border-bottom: 1px solid #cbd5e1;';

        $html .= '<tr style="background-color: ' . $bg . ';">';
        $html .= '<td style="width: 10%; text-align: center; font-size: 8.5pt; font-weight: bold; color: #475569; ' . $border_b . ' border-right: 1px solid #cbd5e1;">' . ($elem !== '' ? $i : '') . '</td>';
        $html .= '<td style="width: 60%; font-size: 8.5pt; font-weight: bold; color: #0f172a; ' . $border_b . ' border-right: 1px solid #cbd5e1;">&nbsp;' . htmlspecialchars($elem) . '</td>';
        $html .= '<td style="width: 30%; text-align: center; font-size: 11pt; ' . $border_b . '">&nbsp;</td>';
        $html .= '</tr>';
    }

    $html .= '</tbody>';
    $html .= '</table>';

    // Pie de la ficha: Firma del juez y firma del sistema
    $html .= '<table cellpadding="2" cellspacing="0" style="width: 100%; margin-top: 4px;">';
    $html .= '<tr>';
    $html .= '<td style="width: 70%; font-size: 7.5pt; color: #475569;">Firma Juez ' . $juez_num . ': ___________________________</td>';
    $html .= '<td style="width: 30%; text-align: right; font-size: 6.5pt; font-weight: bold; color: #94a3b8;">SINCRM</td>';
    $html .= '</tr>';
    $html .= '</table>';

    $html .= '</div>';
    return $html;
}

// Coordenadas fijas para los 4 cuadrantes (A4 = 210 x 297 mm)
// Con estas dimensiones, al cortar a 105mm y 148.5mm, cada ficha tiene 5mm de margen limpio en todos los lados.
$quadrant_coords = [
    [5.0, 5.0],       // Cuadrante 0: Superior Izquierda
    [110.0, 5.0],     // Cuadrante 1: Superior Derecha
    [5.0, 153.5],     // Cuadrante 2: Inferior Izquierda
    [110.0, 153.5]    // Cuadrante 3: Inferior Derecha
];

$slot = 0;

// 4. Consulta de fases con elementos Coach Card
$query_fases = "SELECT id_categoria, id_modalidad, id, orden, elementos_coach_card 
                FROM fases 
                WHERE id_competicion = '$id_competicion_activa' AND elementos_coach_card > 0 ";

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

    // Jueces del panel tipo 1 (Elementos) excluyendo Media
    $q_jueces = "SELECT paneles.nombre, numero_juez 
                 FROM panel_jueces, paneles, jueces 
                 WHERE id_fase = '" . $fase['id'] . "' 
                   AND id_panel = paneles.id 
                   AND paneles.id_paneles_tipo = 1 
                   AND jueces.id = panel_jueces.id_juez 
                   AND jueces.nombre NOT LIKE 'Media' 
                 ORDER BY numero_juez";
    $jueces = mysqli_query($connection, $q_jueces);

    while ($juez = mysqli_fetch_array($jueces)) {
        // Rutinas de la fase ordenadas por orden de salida
        $q_ordenes = "SELECT orden, id FROM rutinas WHERE id_fase = '" . $fase['id'] . "' ORDER BY orden";
        $ordenes = mysqli_query($connection, $q_ordenes);

        $q_max = "SELECT orden, id FROM rutinas WHERE id_fase = '" . $fase['id'] . "' AND orden > 0 ORDER BY orden";
        $orden_maximo = mysqli_num_rows(mysqli_query($connection, $q_max));

        while ($orden = mysqli_fetch_assoc($ordenes)) {
            $orden_val = ($orden['orden'] == '-1') ? 'PS' : $orden['orden'];

            // Obtener los nombres de los elementos técnicos / híbridos / acrobacias
            $elementos = [];
            for ($x = 1; $x <= $fase['elementos_coach_card']; $x++) {
                $q_elem = "SELECT nombre, texto 
                           FROM hibridos_rutina, tipo_hibridos 
                           WHERE tipo LIKE 'part' 
                             AND texto = tipo_hibridos.id 
                             AND nombre NOT LIKE 'TRANSITION' 
                             AND id_rutina = " . $orden['id'] . " 
                             AND elemento = $x";
                $res_elem = mysqli_query($connection, $q_elem);
                $tipo_elemento = ($res_elem && mysqli_num_rows($res_elem) > 0) ? mysqli_result($res_elem, 0) : '';

                if ($tipo_elemento === 'TRE') {
                    $q_tre = "SELECT texto FROM hibridos_rutina WHERE texto LIKE '%-TRE%' AND id_rutina = " . $orden['id'] . " AND elemento = $x";
                    $res_tre = mysqli_query($connection, $q_tre);
                    $nombre = ($res_tre && mysqli_num_rows($res_tre) > 0) ? substr(mysqli_result($res_tre, 0), 2, 5) : '';
                } else if ($tipo_elemento === 'ACROBATIC') {
                    $q_acro = "SELECT texto FROM hibridos_rutina WHERE tipo LIKE 'basemark' AND id_rutina = " . $orden['id'] . " AND elemento = $x";
                    $res_acro = mysqli_query($connection, $q_acro);
                    $nombre = ($res_acro && mysqli_num_rows($res_acro) > 0) ? mysqli_result($res_acro, 0) : '';

                    $q_sub = "SELECT texto FROM hibridos_rutina 
                              WHERE texto IN (SELECT codigo FROM dificultad_acropair WHERE descripcion LIKE '%Subgrupo%') 
                                AND id_rutina = " . $orden['id'] . " AND elemento = $x";
                    $res_sub = mysqli_query($connection, $q_sub);
                    $nombre_subgrupo = ($res_sub && mysqli_num_rows($res_sub) > 0) ? mysqli_result($res_sub, 0) : '';
                    if ($nombre_subgrupo !== '') {
                        $nombre .= ' (' . $nombre_subgrupo . ')';
                    }
                } else {
                    $nombre = $tipo_elemento;
                }
                $elementos[$x] = $nombre;
            }

            // Si iniciamos una nueva página, dibujamos las guías de corte
            if ($slot === 0) {
                $pdf->AddPage();
                drawGuillotineGuides($pdf);
            }

            // Construir e imprimir la ficha en el cuadrante exacto
            $slip_html = buildElementSlipHTML(
                $nombre_competicion_activa,
                $juez['numero_juez'],
                $nombre_modalidad,
                $nombre_categoria,
                $orden_val,
                $orden_maximo,
                $elementos,
                $fase['elementos_coach_card']
            );

            $pdf->writeHTMLCell(95.0, 138.5, $quadrant_coords[$slot][0], $quadrant_coords[$slot][1], $slip_html, 0, 0, false, true, 'L', true);

            $slot = ($slot + 1) % 4;
        }
    }
}

// 5. Salida del PDF
$pdf->Output('hojas_puntuacion_elementos_' . $id_competicion_activa . '.pdf', 'I');
