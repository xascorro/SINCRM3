<?php
require_once(__DIR__ . '/../tcpdf/tcpdf.php');
include_once(__DIR__ . '/../database/dbconfig.php');
include_once(__DIR__ . '/../lib/my_functions.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Control de acceso: sesión activa o cron token
$cron_token_expected = "internal_cron_" . md5("SincrmInternalCron" . date('Y-m-d'));
$cron_token_provided = $_GET['cron_token'] ?? '';

if (!isset($_SESSION['email']) && !isset($_SESSION['username']) && $cron_token_provided !== $cron_token_expected) {
    header('Location: ../login.php');
    exit();
}

// Obtener competición
$id_competicion = isset($_GET['id_competicion']) ? (int)$_GET['id_competicion'] : (isset($_SESSION['id_competicion_usuario']) ? (int)$_SESSION['id_competicion_usuario'] : (isset($_SESSION['id_competicion_activa']) ? (int)$_SESSION['id_competicion_activa'] : 0));

if ($id_competicion > 0) {
    $q_comp = "SELECT * FROM competiciones WHERE id = '$id_competicion' LIMIT 1";
} else {
    $q_comp = "SELECT * FROM competiciones WHERE activo = 'si' LIMIT 1";
}
$res_comp = mysqli_query($connection, $q_comp);
$comp = ($res_comp) ? mysqli_fetch_assoc($res_comp) : null;

if (!$comp) {
    die("Error: No se ha encontrado la competición solicitada.");
}

$GLOBALS["id_competicion_activa"] = (int)$comp['id'];
$GLOBALS["nombre_competicion_activa"] = $comp['nombre'];
$GLOBALS["lugar"] = !empty($comp['lugar']) ? $comp['lugar'] : 'Sede Oficial';
$GLOBALS["fecha"] = !empty($comp['fecha']) ? dateAFecha($comp['fecha']) : date('d/m/Y');
$GLOBALS["header_image"] = !empty($comp['header_informe']) ? __DIR__ . '/../' . $comp['header_informe'] : '';
$GLOBALS["footer_image"] = !empty($comp['footer_informe']) ? __DIR__ . '/../' . $comp['footer_informe'] : '';

$titulo = !empty($_GET['titulo']) ? $_GET['titulo'] : 'Anotadores de Rutinas';
$GLOBALS['titulo_documento'] = 'HOJAS PARA ANOTADORES DE RUTINAS';
$GLOBALS['subtitulo_documento'] = $GLOBALS['nombre_competicion_activa'];
$GLOBALS['footer_substring'] = "Sede: " . $GLOBALS['lugar'] . " · Fecha: " . $GLOBALS['fecha'];

$nombre_documento = 'Anotadores_Rutinas_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $GLOBALS['nombre_competicion_activa']);

// Clase PDF con diseño moderno v4 (cabecera superpuesta y márgenes optimizados)
class SINCRM_ANOTADORES_RUTINAS_PDF extends TCPDF {

    public function Header() {
        if (!empty($GLOBALS['header_image']) && file_exists($GLOBALS['header_image'])) {
            $this->Image($GLOBALS['header_image'], 12, 5, 186, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
            $this->SetY(6.5);
        } else {
            $this->SetY(6.5);
        }

        $this->SetFont('helvetica', 'B', 13);
        $this->SetTextColor(233, 38, 98);
        $this->Cell(0, 6, $GLOBALS['titulo_documento'], 0, 1, 'C');

        $this->SetFont('helvetica', 'B', 8.5);
        $this->SetTextColor(71, 85, 105);
        $this->Cell(0, 4, mb_strtoupper($GLOBALS['subtitulo_documento'], 'UTF-8'), 0, 1, 'C');

        // Línea divisoria suave bajo la cabecera
        $this->SetDrawColor(226, 232, 240);
        $this->SetLineWidth(0.3);
        $this->Line(12, 24.5, 198, 24.5);
    }

    public function Footer() {
        if (!empty($GLOBALS['footer_image']) && file_exists($GLOBALS['footer_image'])) {
            $this->Image($GLOBALS['footer_image'], 12, 275, 186, '', 'JPG', '', '', false, 300, '', false, false, 0, false, false, false);
        }
        $this->SetY(278);
        $this->SetFont('helvetica', 'B', 8);
        $this->SetTextColor(100, 116, 139);
        $pagenumtxt = 'Página ' . $this->PageNo() . ' de ' . $this->getAliasNbPages();
        $this->Cell(0, 4, $GLOBALS['footer_substring'] . ' · ' . $pagenumtxt, 0, 0, 'R');
    }
}

// Crear instancia del PDF en orientación vertical
$pdf = new SINCRM_ANOTADORES_RUTINAS_PDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Pedro Díaz');
$pdf->SetTitle($GLOBALS["titulo_documento"]);
$pdf->SetSubject($GLOBALS["nombre_competicion_activa"]);

$pdf->SetMargins(12, 27, 12);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(15);
$pdf->SetAutoPagebreak(TRUE, 22);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

if (@file_exists(dirname(__FILE__).'/lang/spa.php')) {
    require_once(dirname(__FILE__).'/lang/spa.php');
    $pdf->setLanguageArray($l);
}

// Consultar exclusivamente las fases de RUTINAS de esta competición
$q_fases = "
    SELECT f.*, m.nombre as nombre_modalidad, c.nombre as nombre_categoria 
    FROM fases f
    LEFT JOIN modalidades m ON f.id_modalidad = m.id
    LEFT JOIN categorias c ON f.id_categoria = c.id
    WHERE f.id_competicion = " . $GLOBALS['id_competicion_activa'] . "
      AND f.id_modalidad IS NOT NULL 
      AND f.id_modalidad > 0
    ORDER BY f.orden ASC, f.id ASC
";
$res_fases = mysqli_query($connection, $q_fases);

$total_fases_rutina = ($res_fases) ? mysqli_num_rows($res_fases) : 0;

if ($total_fases_rutina === 0) {
    // Si no hay fases de rutina en la competición
    $pdf->AddPage();
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor(100, 116, 139);
    $html_vacio = '<div style="text-align:center; padding:50px 20px; color:#64748b;">
        <h3 style="color:#0f172a;">No hay fases de rutinas configuradas</h3>
        <p>Esta competición no contiene pruebas de rutinas o no han sido registradas aún.</p>
    </div>';
    $pdf->writeHTML($html_vacio, true, false, true, false, '');
} else {
    // Recorrer cada fase de rutina
    while ($fase = mysqli_fetch_assoc($res_fases)) {
        $id_fase = (int)$fase['id'];
        $nombre_modalidad = trim(($fase['nombre_modalidad'] ?? 'Rutina') . (!empty($fase['genero']) && $fase['genero'] === 'masculino' ? ' Masc' : ''));
        $nombre_categoria = trim($fase['nombre_categoria'] ?? 'General');

        // Consultar cuántos jueces están asignados en el panel para dimensionar las columnas
        $q_jueces = "SELECT numero_juez FROM panel_jueces WHERE id_fase = $id_fase ORDER BY numero_juez ASC";
        $res_jueces = mysqli_query($connection, $q_jueces);
        $num_jueces = ($res_jueces) ? mysqli_num_rows($res_jueces) : 0;
        if ($num_jueces < 3) {
            $num_jueces = 5; // Mínimo estándar de 5 jueces para la hoja de anotación
        }

        // Consultar rutinas configuradas en esta fase
        $q_rutinas = "
            SELECT r.*, c.nombre as club_nombre, c.nombre_corto as club_corto 
            FROM rutinas r 
            LEFT JOIN clubes c ON r.id_club = c.id 
            WHERE r.id_fase = $id_fase 
            ORDER BY 
                CASE 
                    WHEN r.orden < 0 THEN r.orden 
                    ELSE 0 
                END ASC, 
                r.orden ASC, 
                r.id ASC
        ";
        $res_rutinas = mysqli_query($connection, $q_rutinas);

        $pdf->AddPage();

        // 1. Tarjeta de Cabecera de la Fase
        $html_fase = '<table cellpadding="3" style="border:1px solid #cbd5e1; background-color:#f8fafc; width:100%; margin-bottom:6px;">';
        $html_fase .= '<tr>';
        $html_fase .= '<td width="60%"><b style="font-size:11pt; color:#1e293b;">' . mb_strtoupper($nombre_modalidad . ' - ' . $nombre_categoria, 'UTF-8') . '</b></td>';
        $html_fase .= '<td width="40%" style="text-align:right; font-size:7pt; color:#64748b;">';
        $html_fase .= 'Fase #' . $id_fase . ' · Orden Competición: ' . $fase['orden'];
        $html_fase .= '</td>';
        $html_fase .= '</tr>';
        $html_fase .= '</table>';

        // 2. Línea para firma del anotador
        $html_fase .= '<div style="font-size:8pt; color:#475569; margin-bottom:8px; padding:3px 0;">';
        $html_fase .= '<strong>Anotador / Secretario de Panel:</strong> __________________________________________________ &nbsp;&nbsp;&nbsp; <strong>Firma:</strong> _______________';
        $html_fase .= '</div><br>';

        // 3. Tabla de Puntuaciones para Anotadores
        // Distribución de anchos de columna
        $w_orden = 9;   // 9%
        $w_atleta = 36; // 36%
        $w_total = 13;  // 13%
        $w_pen = 10;    // 10%
        $w_jueces_total = 100 - ($w_orden + $w_atleta + $w_total + $w_pen); // 32%
        $w_cada_juez = round($w_jueces_total / $num_jueces, 2);

        $html_fase .= '<table cellpadding="4" border="1" style="border-collapse:collapse; border:1px solid #94a3b8; font-size:7pt; width:100%;">';
        $html_fase .= '<tr style="background-color:#0f172a; color:#ffffff; font-weight:bold; text-align:center;">';
        $html_fase .= '<td width="' . $w_orden . '%">ORDEN</td>';
        $html_fase .= '<td width="' . $w_atleta . '%" style="text-align:left;">&nbsp;ATLETAS / CLUB</td>';
        
        for ($j = 1; $j <= $num_jueces; $j++) {
            $html_fase .= '<td width="' . $w_cada_juez . '%">J' . $j . '</td>';
        }

        $html_fase .= '<td width="' . $w_pen . '%">PENAL.</td>';
        $html_fase .= '<td width="' . $w_total . '%">TOTAL NOTA</td>';
        $html_fase .= '</tr>';

        $num_fila = 0;
        $hay_rutinas = false;

        if ($res_rutinas && mysqli_num_rows($res_rutinas) > 0) {
            $hay_rutinas = true;
            while ($rutina = mysqli_fetch_assoc($res_rutinas)) {
                $num_fila++;
                $bg_color = ($num_fila % 2 === 0) ? '#f8fafc' : '#ffffff';

                // Formato de orden
                $orden_val = (int)$rutina['orden'];
                $orden_str = (string)$orden_val;
                if ($orden_val < 0 && $orden_val >= -9) {
                    $orden_str = 'PS';
                } elseif ($orden_val <= -10) {
                    $orden_str = 'EX';
                }

                // Consultar participantes de la rutina
                $id_rutina = (int)$rutina['id'];
                $q_part = "
                    SELECT n.nombre, n.apellidos 
                    FROM rutinas_participantes rp 
                    JOIN nadadoras n ON rp.id_nadadora = n.id 
                    WHERE rp.id_rutina = $id_rutina 
                    ORDER BY n.apellidos ASC, n.nombre ASC
                ";
                $res_part = mysqli_query($connection, $q_part);
                $participantes_arr = [];
                if ($res_part) {
                    while ($p = mysqli_fetch_assoc($res_part)) {
                        $participantes_arr[] = $p['apellidos'] . ', ' . $p['nombre'];
                    }
                }

                $club_str = !empty($rutina['club_corto']) ? $rutina['club_corto'] : (!empty($rutina['club_nombre']) ? $rutina['club_nombre'] : 'Sin Club');
                
                $html_fase .= '<tr style="background-color:' . $bg_color . ';" nobr="true">';
                
                // Orden destacado
                $html_fase .= '<td width="' . $w_orden . '%" style="text-align:center; font-weight:bold; font-size:9pt; vertical-align:middle; line-height:22px;">' . $orden_str . '</td>';
                
                // Atletas y club
                $html_fase .= '<td width="' . $w_atleta . '%" style="vertical-align:middle; line-height:12px;">';
                if (!empty($participantes_arr)) {
                    $html_fase .= '<strong style="color:#0f172a;">' . implode(' · ', $participantes_arr) . '</strong><br>';
                } else {
                    $html_fase .= '<strong style="color:#0f172a;">' . (!empty($rutina['nombre']) ? htmlspecialchars($rutina['nombre']) : 'Rutina #' . $id_rutina) . '</strong><br>';
                }
                $html_fase .= '<span style="color:#2563eb; font-weight:bold; font-size:6.5pt;">' . htmlspecialchars($club_str) . '</span>';
                $html_fase .= '</td>';

                // Celdas en blanco para que el anotador escriba las notas de los jueces
                for ($j = 1; $j <= $num_jueces; $j++) {
                    $html_fase .= '<td width="' . $w_cada_juez . '%" style="vertical-align:middle; height:24px;"></td>';
                }

                // Penalizaciones y total
                $html_fase .= '<td width="' . $w_pen . '%" style="vertical-align:middle; height:24px;"></td>';
                $html_fase .= '<td width="' . $w_total . '%" style="vertical-align:middle; height:24px;"></td>';

                $html_fase .= '</tr>';
            }
        }

        // Si no había rutinas o hay menos de 5, rellenar algunas filas vacías de cortesía
        if (!$hay_rutinas) {
            for ($k = 1; $k <= 8; $k++) {
                $bg_color = ($k % 2 === 0) ? '#f8fafc' : '#ffffff';
                $html_fase .= '<tr style="background-color:' . $bg_color . ';" nobr="true">';
                $html_fase .= '<td width="' . $w_orden . '%" style="text-align:center; font-weight:bold; height:24px;">' . $k . '</td>';
                $html_fase .= '<td width="' . $w_atleta . '%" style="color:#94a3b8; font-style:italic;">&nbsp;Disponible</td>';
                for ($j = 1; $j <= $num_jueces; $j++) {
                    $html_fase .= '<td width="' . $w_cada_juez . '%" style="height:24px;"></td>';
                }
                $html_fase .= '<td width="' . $w_pen . '%" style="height:24px;"></td>';
                $html_fase .= '<td width="' . $w_total . '%" style="height:24px;"></td>';
                $html_fase .= '</tr>';
            }
        }

        $html_fase .= '</table>';

        $pdf->writeHTML($html_fase, true, false, true, false, '');
    }
}

// Salida directa del documento PDF
$dest = $_GET['dest'] ?? 'I';
if ($dest === 'S') {
    echo $pdf->Output($nombre_documento . '.pdf', 'S');
} else {
    $pdf->Output($nombre_documento . '.pdf', 'I');
}
