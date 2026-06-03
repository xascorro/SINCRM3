<?php
/**
 * Informe de Puntuaciones V2 - Robusto
 * Corregido para evitar errores 500 por acceso a offsets en nulos.
 */

// Habilitar errores para depuración si es necesario
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

require_once('../tcpdf/tcpdf.php');
include('../security.php');
include('../lib/my_functions.php');

mysqli_query($connection, "SET NAMES 'utf8'");

// --- Cargar Datos de la Competición ---
$GLOBALS["id_competicion_activa"] = 0;
$query_comp = "SELECT * FROM competiciones WHERE activo='si' LIMIT 1";
$res_comp = mysqli_query($connection, $query_comp);
if ($res_comp && $registro = mysqli_fetch_array($res_comp)) {
    $GLOBALS["id_competicion_activa"] = $registro['id'];
    $GLOBALS["nombre_competicion_activa"] = $registro['nombre'];
    $GLOBALS["lugar"] = $registro['lugar'];
    $GLOBALS["fecha"] = dateAFecha($registro['fecha']);
    $GLOBALS["header_image"] = '../' . $registro['header_informe'];
    $GLOBALS["footer_image"] = '../' . $registro['footer_informe'];
} else {
    $GLOBALS["nombre_competicion_activa"] = "Competición";
    $GLOBALS["header_image"] = "";
    $GLOBALS["footer_image"] = "";
    $GLOBALS["fecha"] = "";
    $GLOBALS["lugar"] = "";
}

$titulo = $_GET['titulo'] ?? 'Clasificación';
$titulo_documento = $GLOBALS['nombre_competicion_activa'] . "<br>" . $titulo;
$nombre_documento = $titulo . ' ' . $GLOBALS['nombre_competicion_activa'];
$GLOBALS['footer_substring'] = "Sede: " . $GLOBALS['lugar'] . " | Fecha: " . $GLOBALS['fecha'];

class MYPDF extends TCPDF {
    public function Header() {
        if (!empty($GLOBALS['header_image']) && file_exists($GLOBALS['header_image'])) {
            $this->Image($GLOBALS['header_image'], 15, 10, 180, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
        $this->SetY(12);
        $this->SetFont('helvetica', 'B', 13);
        $this->writeHTMLCell(0, 0, '', '', '<div style="text-align:center;">'.$GLOBALS['titulo_documento'].'</div>', 0, 1, 0, true, 'C', true);
    }
    public function Footer() {
        if (!empty($GLOBALS['footer_image']) && file_exists($GLOBALS['footer_image'])) {
            $this->Image($GLOBALS['footer_image'], 15, 275, 180, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $pagenumtxt = 'Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages();
        $this->Cell(0, 10, $GLOBALS['footer_substring'] . ' | ' . $pagenumtxt, 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
}

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetMargins(15, 40, 15);
$pdf->SetAutoPageBreak(TRUE, 30);

$style = '
<style>
    .tabla-principal { border-collapse: collapse; width: 100%; }
    .header-tabla { background-color: #333333; color: #ffffff; font-weight: bold; text-align: center; font-size: 8pt; }
    .pos-cell { font-size: 12pt; font-weight: bold; text-align: center; }
    .club-nombre { font-size: 9pt; font-weight: bold; color: #003366; }
    .nadadoras-lista { font-size: 7pt; color: #444; }
    .item-label { font-size: 6.5pt; color: #666; font-weight: bold; }
    .nota-valor { font-size: 7pt; text-align: right; }
    .nota-jueces { font-family: courier; font-size: 7pt; }
    .nota-final { font-size: 10pt; font-weight: bold; text-align: right; color: #000; }
    .penalizacion { color: #b00; font-size: 6.5pt; font-style: italic; }
</style>';

$condicion_fase = isset($_GET['id_fase']) ? " AND id = '" . intval($_GET['id_fase']) . "'" : "";
$query_fases = "SELECT * FROM fases WHERE id_competicion = '" . $GLOBALS["id_competicion_activa"] . "' $condicion_fase ORDER BY orden";
$res_fases = mysqli_query($connection, $query_fases);

if ($res_fases) {
    while ($fase = mysqli_fetch_array($res_fases)) {
        $id_fase = $fase['id'];
        $nombre_cat = mysqli_result(mysqli_query($connection, "SELECT nombre FROM categorias WHERE id = '".$fase['id_categoria']."'"), 0);
        $nombre_mod = mysqli_result(mysqli_query($connection, "SELECT nombre FROM modalidades WHERE id = '".$fase['id_modalidad']."'"), 0);

        $pdf->AddPage();
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 10, strtoupper("$nombre_mod - $nombre_cat"), 0, 1, 'L');

        $html = $style . '<table cellpadding="4" class="tabla-principal">';
        $html .= '<tr class="header-tabla">
                    <th width="5%">Pos</th>
                    <th width="30%">Club / Participantes</th>
                    <th width="20%">Desglose Panel</th>
                    <th width="22%">Notas Jueces</th>
                    <th width="8%" align="right">Panel</th>
                    <th width="15%" align="right">Total / Dif</th>
                  </tr>';

        $res_rutinas = mysqli_query($connection, "SELECT * FROM rutinas WHERE id_fase='$id_fase' ORDER BY posicion ASC");
        $count = 0;

        while ($res_rutinas && $rutina = mysqli_fetch_array($res_rutinas)) {
            $bgcolor = ($count++ % 2 == 0) ? '#FFFFFF' : '#F2F2F2';
            $pos = ($rutina['baja'] == 'si') ? '-' : (($rutina['orden'] < 0) ? ($rutina['orden'] == -1 ? "PS" : "EX") : $rutina['posicion']);
            
            $q_club = mysqli_query($connection, "SELECT nombre_corto FROM clubes WHERE id = '".$rutina['id_club']."'");
            $nombre_club = ($q_club) ? mysqli_result($q_club, 0) : "Club";
            
            // Nadadoras
            $nad_html = "";
            $res_nad = mysqli_query($connection, "SELECT n.apellidos, n.nombre, n.año_nacimiento, rp.reserva FROM nadadoras n JOIN rutinas_participantes rp ON n.id = rp.id_nadadora WHERE rp.id_rutina = '".$rutina['id']."'");
            while ($res_nad && $nad = mysqli_fetch_array($res_nad)) {
                $nad_html .= $nad['apellidos'] . ", " . $nad['nombre'] . ($nad['reserva'] == 'si' ? ' (R)' : '') . " <span style=\"color:#888\">(".substr($nad['año_nacimiento'],0,4).")</span><br>";
            }

            $html .= '<tr bgcolor="'.$bgcolor.'" nobr="true">';
            $html .= '<td class="pos-cell" width="5%">'.$pos.'</td>';
            $html .= '<td width="30%"><span class="club-nombre">'.$nombre_club.' '.$rutina['nombre'].'</span><br><span class="nadadoras-lista">'.$nad_html.'</span></td>';

            if ($rutina['baja'] != 'si') {
                $col_paneles = ""; $col_notas_j = ""; $col_puntos_p = "";

                if ($fase['obsoleto'] == 'no') {
                    // --- NUEVO SISTEMA ---
                    $res_ele = mysqli_query($connection, "SELECT elemento FROM hibridos_rutina, tipo_hibridos WHERE id_rutina = '".$rutina['id']."' AND tipo='part' AND texto != '3' AND texto=tipo_hibridos.id ORDER BY elemento");
                    while ($res_ele && $ele = mysqli_fetch_array($res_ele)) {
                        $e_idx = $ele['elemento'];
                        $col_paneles .= '<span class="item-label">Elemento '.$e_idx.'</span><br>';
                        
                        $res_j = mysqli_query($connection, "SELECT pj.nota, pj.nota_menor, pj.nota_mayor FROM puntuaciones_jueces pj JOIN panel_jueces p on pj.id_panel_juez = p.id WHERE pj.id_rutina='".$rutina['id']."' AND pj.id_elemento='$e_idx' AND p.id_panel IN (SELECT id FROM paneles WHERE id_paneles_tipo=1 AND id_competicion='".$GLOBALS['id_competicion_activa']."') ORDER BY p.numero_juez");
                        while ($res_j && $nj = mysqli_fetch_array($res_j)) {
                            $n_txt = ($nj['nota_menor'] == 1 || $nj['nota_mayor'] == 1) ? '<del>'.$nj['nota'].'</del>' : $nj['nota'];
                            $col_notas_j .= '<span class="nota-jueces">'.$n_txt.' </span>';
                        }
                        $col_notas_j .= '<br>';
                        
                        $q_n_ele = mysqli_query($connection, "SELECT nota FROM puntuaciones_elementos WHERE elemento='$e_idx' AND id_rutina='".$rutina['id']."'");
                        $n_ele = ($q_n_ele && $row_n = mysqli_fetch_assoc($q_n_ele)) ? $row_n['nota'] : '-';
                        $col_puntos_p .= '<span class="nota-valor">'.$n_ele.'</span><br>';
                    }

                    if ($fase['id_modalidad'] != 1 && $fase['id_modalidad'] != 5) {
                        $col_paneles .= '<span class="item-label">Sincronización</span><br>';
                        $col_notas_j .= '<span class="nadadoras-lista">(Sincro)</span><br>';
                        $col_puntos_p .= '<span class="nota-valor">'.$rutina['nota_panel_sincro'].'</span><br>';
                    }
                    $col_paneles .= '<b>Total Elementos</b><br>';
                    $col_notas_j .= '<br>';
                    $col_puntos_p .= '<b>'.$rutina['nota_final_panel_elementos'].'</b><br>';

                    $ia_types = ['ChoMu' => 'Choreo', 'Performance' => 'Perf.', 'Transitions' => 'Trans.'];
                    foreach ($ia_types as $db_type => $label) {
                        $col_paneles .= '<span class="item-label">'.$label.'</span><br>';
                        $res_j_ia = mysqli_query($connection, "SELECT pj.nota, pj.nota_menor, pj.nota_mayor FROM puntuaciones_jueces pj JOIN panel_jueces p on pj.id_panel_juez = p.id WHERE pj.id_rutina='".$rutina['id']."' AND pj.tipo_ia='$db_type' AND p.id_panel IN (SELECT id FROM paneles WHERE id_paneles_tipo=2 AND id_competicion='".$GLOBALS['id_competicion_activa']."') ORDER BY p.numero_juez");
                        while ($res_j_ia && $nj = mysqli_fetch_array($res_j_ia)) {
                            $n_txt = ($nj['nota_menor'] == 1 || $nj['nota_mayor'] == 1) ? '<del>'.$nj['nota'].'</del>' : $nj['nota'];
                            $col_notas_j .= '<span class="nota-jueces">'.$n_txt.' </span>';
                        }
                        $col_notas_j .= '<br>';
                        $q_n_ia = mysqli_query($connection, "SELECT nota FROM puntuaciones_elementos WHERE tipo_ia='$db_type' AND id_rutina='".$rutina['id']."'");
                        $n_ia = ($q_n_ia && $row_n = mysqli_fetch_assoc($q_n_ia)) ? $row_n['nota'] : '-';
                        $col_puntos_p .= '<span class="nota-valor">'.$n_ia.'</span><br>';
                    }
                    $col_paneles .= '<b>Total Artística</b>';
                    $col_notas_j .= '<br>';
                    $col_puntos_p .= '<b>'.$rutina['nota_final_panel_ia'].'</b>';

                } else {
                    // --- ANTIGUO ---
                    $res_p = mysqli_query($connection, "SELECT * FROM paneles WHERE id_competicion='".$GLOBALS["id_competicion_activa"]."' AND puntua='si' AND obsoleto='si'");
                    while ($res_p && $p = mysqli_fetch_array($res_p)) {
                        $col_paneles .= '<span class="item-label">'.$p['nombre'].'</span><br>';
                        $res_j = mysqli_query($connection, "SELECT pj.nota, pj.nota_menor, pj.nota_mayor FROM puntuaciones_jueces pj JOIN panel_jueces pj_p on pj.id_panel_juez = pj_p.id WHERE pj_p.id_panel='".$p['id']."' AND pj.id_rutina='".$rutina['id']."' ORDER BY pj_p.numero_juez");
                        while ($res_j && $nj = mysqli_fetch_array($res_j)) {
                            $n_txt = ($nj['nota_menor'] == 1 || $nj['nota_mayor'] == 1) ? '<del>'.$nj['nota'].'</del>' : $nj['nota'];
                            $col_notas_j .= '<span class="nota-jueces">'.$n_txt.' </span>';
                        }
                        $col_notas_j .= '<br>';
                        $q_n_p = mysqli_query($connection, "SELECT nota_calculada FROM puntuaciones_paneles WHERE id_rutina='".$rutina['id']."' AND id_panel='".$p['id']."'");
                        $n_p = ($q_n_p && $row_n = mysqli_fetch_assoc($q_n_p)) ? $row_n['nota_calculada'] : '-';
                        $col_puntos_p .= '<span class="nota-valor">'.$n_p.'</span><br>';
                    }
                }

                $html .= '<td width="20%">'.$col_paneles.'</td>';
                $html .= '<td width="22%">'.$col_notas_j.'</td>';
                $html .= '<td width="8%" align="right">'.$col_puntos_p.'</td>';
                $html .= '<td width="15%" align="right"><span class="nota-final">'.$rutina['nota_final'].'</span><br><span class="nadadoras-lista">Dif: '.$rutina['diferencia'].'</span></td>';
            } else {
                $html .= '<td colspan="4" align="center" style="color:#666; font-style:italic; padding:20px;">BAJA</td>';
            }
            $html .= '</tr>';

            $res_pen = mysqli_query($connection, "SELECT p.codigo FROM penalizaciones p JOIN penalizaciones_rutinas pr ON p.id = pr.id_penalizacion WHERE pr.id_rutina = '".$rutina['id']."'");
            if ($res_pen && mysqli_num_rows($res_pen) > 0) {
                $pens = []; while($p = mysqli_fetch_array($res_pen)) $pens[] = $p['codigo'];
                $html .= '<tr bgcolor="'.$bgcolor.'"><td></td><td colspan="5" class="penalizacion">Penalizaciones: '.implode(', ', $pens).' ('.$rutina['penalizaciones_rutina'].' pts)</td></tr>';
            }
        }
        $html .= '</table>';
        $pdf->writeHTML($html, true, false, false, false, '');
    }
}

$pdf->Output($nombre_documento . '.pdf', 'I');
?>
