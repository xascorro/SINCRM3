<?php
include('security.php');

$roles_permitidos = [1, 2, 3, 4]; // Admin, Editor, Consulta, Juez
// Permitir también a usuarios con perfil de juez asignado (ej: Club + Juez)
if (!in_array($_SESSION['id_rol'], $roles_permitidos) && empty($_SESSION['id_juez_v3'])) {
    header('Location: index.php');
    exit();
}

$es_admin = in_array($_SESSION['id_rol'], [1, 2, 3]);
$my_user_id = (int)$_SESSION['id_usario'];
$my_juez_id = (int)($_SESSION['id_juez_v3'] ?? 0);
$my_username = $_SESSION['username'] ?? '';

// --- 1. RESOLUCIÓN DE IDENTIDAD DEL JUEZ SELECCIONADO ---
$selected_juez_id = 0;
$selected_user_id = 0;

if ($es_admin && isset($_GET['juez_id']) && (int)$_GET['juez_id'] > 0) {
    $selected_juez_id = (int)$_GET['juez_id'];
} elseif ($es_admin && isset($_GET['usuario_id']) && (int)$_GET['usuario_id'] > 0) {
    $selected_user_id = (int)$_GET['usuario_id'];
    $q_u = "SELECT id_juez_v3 FROM usuarios WHERE id = '$selected_user_id' LIMIT 1";
    $ru = mysqli_query($connection, $q_u);
    if ($ru && $row_u = mysqli_fetch_assoc($ru)) {
        $selected_juez_id = (int)($row_u['id_juez_v3'] ?? 0);
    }
}

// Si aún no está seleccionado:
if ($selected_juez_id == 0) {
    if ($my_juez_id > 0) {
        $selected_juez_id = $my_juez_id;
    } else {
        // Intentar buscar juez por nombre de usuario
        $clean_user = mysqli_real_escape_string($connection, $my_username);
        $q_match = "SELECT id FROM jueces WHERE (nombre LIKE '%$clean_user%' OR apellidos LIKE '%$clean_user%') AND activo = 1 LIMIT 1";
        $res_m = mysqli_query($connection, $q_match);
        if ($res_m && $row_m = mysqli_fetch_assoc($res_m)) {
            $selected_juez_id = (int)$row_m['id'];
        } elseif ($es_admin) {
            // Como fallback para admin sin vinculación directa, tomar el juez con mayor volumen de notas
            $q_top = "SELECT id_juez FROM auditoria_jueces_stats WHERE entidad_tipo = 'GLOBAL' ORDER BY total_notas DESC LIMIT 1";
            $res_top = mysqli_query($connection, $q_top);
            if ($res_top && $row_top = mysqli_fetch_assoc($res_top)) {
                $selected_juez_id = (int)$row_top['id_juez'];
            } else {
                $selected_juez_id = 1;
            }
        }
    }
}

// --- 2. OBTENER DATOS BÁSICOS DEL JUEZ ---
$q_juez = "SELECT j.*, f.nombre as federacion_nombre 
           FROM jueces j 
           LEFT JOIN federaciones f ON j.federacion = f.id 
           WHERE j.id = '$selected_juez_id' LIMIT 1";
$res_juez = mysqli_query($connection, $q_juez);
$juez_data = mysqli_fetch_assoc($res_juez);

$group_key = 'J_' . $selected_juez_id;
if (!$juez_data) {
    $q_audit_name = "SELECT id_juez, nombre_entidad FROM auditoria_jueces_stats WHERE group_key = '$group_key' OR id_juez = '$selected_juez_id' LIMIT 1";
    $res_an = mysqli_query($connection, $q_audit_name);
    if ($res_an && $row_an = mysqli_fetch_assoc($res_an)) {
        $nombre_display = $row_an['nombre_entidad'];
    } else {
        $nombre_display = "Juez #" . $selected_juez_id;
    }
} else {
    $nombre_display = trim($juez_data['nombre'] . ' ' . $juez_data['apellidos']);
}

// Buscar datos del usuario vinculado (foto, email, rol)
$clean_name = mysqli_real_escape_string($connection, $nombre_display);
$q_user = "SELECT u.*, r.nombre as rol_nombre 
           FROM usuarios u 
           LEFT JOIN roles r ON u.id_rol = r.id 
           WHERE u.id_juez_v3 = '$selected_juez_id' 
              OR u.username = '$clean_name' 
           LIMIT 1";
$res_user = mysqli_query($connection, $q_user);
$user_data = mysqli_fetch_assoc($res_user);

// --- 3. DATOS MACRO DE AUDITORÍA (World Aquatics) ---
$q_macro = "
    SELECT 
        SUM(total_notas) as total_notas,
        AVG(precision_aqua) as precision_media,
        AVG(bias_score) as bias_medio,
        SUM(bajas) as total_bajas,
        SUM(altas) as total_altas,
        COUNT(DISTINCT id_competicion) as total_eventos
    FROM auditoria_jueces_stats
    WHERE (group_key = '$group_key' OR id_juez = '$selected_juez_id') 
      AND entidad_tipo = 'GLOBAL'
";
$res_macro = mysqli_query($connection, $q_macro);
$macro = mysqli_fetch_assoc($res_macro);

$has_audit_data = ($macro && (int)$macro['total_notas'] > 0);
if (!$has_audit_data) {
    $macro = [
        'total_notas' => 0,
        'precision_media' => 0,
        'bias_medio' => 0,
        'total_bajas' => 0,
        'total_altas' => 0,
        'total_eventos' => 0
    ];
}

// --- 4. PARTICIPACIÓN TÉCNICA (Puestos y Paneles) ---
$q_puestos = "SELECT p.nombre as puesto, COUNT(*) as cantidad 
              FROM puesto_juez pj 
              JOIN puestos_juez p ON pj.id_puestos_juez = p.id 
              WHERE pj.id_juez = '$selected_juez_id' 
              GROUP BY p.nombre 
              ORDER BY cantidad DESC";
$res_puestos = mysqli_query($connection, $q_puestos);
$puestos_stats = [];
$total_roles = 0;
while($p = mysqli_fetch_assoc($res_puestos)){
    $puestos_stats[] = $p;
    $total_roles += (int)$p['cantidad'];
}

// Conteo de paneles de puntuación (panel_jueces)
$q_scoring_panels = "SELECT COUNT(*) as total FROM panel_jueces WHERE id_juez = '$selected_juez_id'";
$res_scoring = mysqli_query($connection, $q_scoring_panels);
$total_scoring_panels = ($res_scoring) ? (int)mysqli_fetch_assoc($res_scoring)['total'] : 0;

if ($total_scoring_panels > 0) {
    $puestos_stats[] = [
        'puesto' => 'Juez Puntuador (Figuras / Rutinas)',
        'cantidad' => $total_scoring_panels
    ];
    $total_roles += $total_scoring_panels;
}

// --- 5. SESGO POR CLUB Y CÁLCULO DE NEUTRALIDAD ---
$q_club_bias = "
    SELECT 
        nombre_entidad,
        SUM(total_notas) as notas,
        AVG(bias_score) as bias_club,
        SUM(altas) as altas,
        SUM(bajas) as bajas
    FROM auditoria_jueces_stats
    WHERE (group_key = '$group_key' OR id_juez = '$selected_juez_id') 
      AND entidad_tipo = 'CLUB'
    GROUP BY nombre_entidad
    HAVING notas >= 4
    ORDER BY notas DESC
    LIMIT 8
";
$res_club_bias = mysqli_query($connection, $q_club_bias);
$clubs_bias_data = [];
$avg_club_bias_abs = 0;
while($cb = mysqli_fetch_assoc($res_club_bias)) {
    $clubs_bias_data[] = $cb;
}
if (!empty($clubs_bias_data)) {
    $sum_c_bias = 0;
    foreach ($clubs_bias_data as $cb) {
        $sum_c_bias += abs((float)$cb['bias_club']);
    }
    $avg_club_bias_abs = $sum_c_bias / count($clubs_bias_data);
}

// --- 6. TOP FIGURAS Y ELEMENTOS EVALUADOS EN LA TEMPORADA ---
$q_top_figures = "
    SELECT 
        nombre_entidad,
        SUM(total_notas) as total_notas,
        ROUND(AVG(precision_aqua), 1) as precision_media,
        ROUND(AVG(bias_score), 3) as bias_medio,
        SUM(bajas) as total_bajas,
        SUM(altas) as total_altas
    FROM auditoria_jueces_stats
    WHERE (group_key = '$group_key' OR id_juez = '$selected_juez_id')
      AND entidad_tipo = 'FIGURA'
    GROUP BY nombre_entidad
    ORDER BY total_notas DESC
    LIMIT 6
";
$res_top_figures = mysqli_query($connection, $q_top_figures);
$season_figures = [];
while($sf = mysqli_fetch_assoc($res_top_figures)) {
    $season_figures[] = $sf;
}

// --- 7. PERFIL DE SEVERIDAD Y RADAR 100% DINÁMICO ---
$inconsistencias = (int)$macro['total_bajas'] + (int)$macro['total_altas'];
$total_notas = max(1, (int)$macro['total_notas']);
$estabilidad_val = ($has_audit_data) ? max(0, min(100, 100 - (($inconsistencias / $total_notas) * 100))) : 85;

$radar_severidad = ($has_audit_data) ? min(100, max(25, round(100 - abs($macro['bias_medio'] * 120)))) : 85;
$radar_consistencia = ($has_audit_data) ? min(100, max(25, round(100 - ($inconsistencias / $total_notas) * 250))) : 85;
$radar_neutralidad = ($has_audit_data && !empty($clubs_bias_data)) ? max(40, min(100, round(100 - ($avg_club_bias_abs * 120)))) : 90;
$radar_precision = ($has_audit_data) ? round($macro['precision_media']) : 75;
$radar_experiencia = min(100, max(25, round(($macro['total_eventos'] * 12) + ($total_roles * 2))));

$radar_stats = [
    'Equilibrio' => $radar_severidad,
    'Consistencia' => $radar_consistencia,
    'Neutralidad' => $radar_neutralidad,
    'Precisión AQUA' => $radar_precision,
    'Experiencia' => $radar_experiencia
];

$severity_label = "Criterio Equilibrado";
$severity_desc = "Criterio alineado con el consenso normativo del panel (±0.2 ptos).";
$severity_badge = "text-blue-700 bg-blue-50 border-blue-200";
$severity_dot = "bg-blue-500";

if (!$has_audit_data) {
    $severity_label = "En Evaluación";
    $severity_desc = "Sin notas de figuras registradas en la temporada activa.";
    $severity_badge = "text-slate-600 bg-slate-100 border-slate-200";
    $severity_dot = "bg-slate-400";
} elseif ($macro['bias_medio'] <= -0.18) {
    $severity_label = "Criterio Exigente";
    $severity_desc = "Tendencia a calificar con mayor rigurosidad técnica que la mediana del panel.";
    $severity_badge = "text-rose-700 bg-rose-50 border-rose-200";
    $severity_dot = "bg-rose-500";
} elseif ($macro['bias_medio'] >= 0.18) {
    $severity_label = "Criterio Benévolo";
    $severity_desc = "Tendencia a valorar elementos por encima de la mediana del panel.";
    $severity_badge = "text-emerald-700 bg-emerald-50 border-emerald-200";
    $severity_dot = "bg-emerald-500";
}

// Generación de Smart Insights de la Temporada
$season_insights = [];
if ($has_audit_data) {
    $prec_m = (float)$macro['precision_media'];
    if ($prec_m >= 65) {
        $season_insights[] = ['icon' => 'fa-circle-check', 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50 border-emerald-100', 'title' => 'Excelente Precisión AQUA', 'desc' => "Concordancia media del " . number_format($prec_m, 1) . "% dentro del margen reglamentario oficial (±0.2 ptos)."];
    } elseif ($prec_m >= 50) {
        $season_insights[] = ['icon' => 'fa-circle-info', 'color' => 'text-blue-600', 'bg' => 'bg-blue-50 border-blue-100', 'title' => 'Nivel Sólido de Consistencia', 'desc' => "Precisión media del " . number_format($prec_m, 1) . "%. Se recomienda unificar criterios en figuras de transición con giros."];
    } else {
        $season_insights[] = ['icon' => 'fa-triangle-exclamation', 'color' => 'text-amber-600', 'bg' => 'bg-amber-50 border-amber-100', 'title' => 'Calibración Recomendada', 'desc' => "Concordancia del " . number_format($prec_m, 1) . "%. Útil para contraste en reuniones clínicas de jueces."];
    }

    $b_medio = (float)$macro['bias_medio'];
    if (abs($b_medio) <= 0.12) {
        $season_insights[] = ['icon' => 'fa-scale-balanced', 'color' => 'text-indigo-600', 'bg' => 'bg-indigo-50 border-indigo-100', 'title' => 'Criterio Muy Homogéneo', 'desc' => "Sesgo acumulado de " . ($b_medio >= 0 ? '+' : '') . number_format($b_medio, 3) . " puntos, demostrando excelente neutralidad."];
    } elseif ($b_medio < -0.12) {
        $season_insights[] = ['icon' => 'fa-arrow-down-wide-short', 'color' => 'text-rose-600', 'bg' => 'bg-rose-50 border-rose-100', 'title' => 'Perfil Riguroso / Estricto', 'desc' => "Tus notas se sitúan en promedio " . number_format($b_medio, 3) . " puntos por debajo del panel."];
    } else {
        $season_insights[] = ['icon' => 'fa-arrow-up-wide-short', 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50 border-emerald-100', 'title' => 'Perfil Abierto / Generoso', 'desc' => "Tus notas se sitúan en promedio +" . number_format($b_medio, 3) . " puntos por encima del panel."];
    }

    if ($avg_club_bias_abs <= 0.15) {
        $season_insights[] = ['icon' => 'fa-shield-halved', 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50 border-emerald-100', 'title' => 'Neutralidad de Club Ejemplar', 'desc' => "No se observa sesgo hacia ningún club en particular (desviación media inter-club ≤ 0.15 ptos)."];
    } else {
        $season_insights[] = ['icon' => 'fa-building-flag', 'color' => 'text-amber-600', 'bg' => 'bg-amber-50 border-amber-100', 'title' => 'Variación por Entidad', 'desc' => "Se aprecia una variación media de ±" . number_format($avg_club_bias_abs, 3) . " puntos según el club evaluado."];
    }
}

// --- 8. HISTORIAL DE EVENTOS Y TRAYECTORIA ---
$q_history = "
    SELECT 
        s.id_competicion,
        c.nombre as comp_nombre,
        c.fecha,
        s.total_notas,
        s.precision_aqua,
        s.bias_score,
        s.altas,
        s.bajas,
        s.pjs_asociados,
        (SELECT COUNT(*) + 1 
         FROM auditoria_jueces_stats s2 
         WHERE s2.id_competicion = s.id_competicion 
           AND s2.entidad_tipo = 'GLOBAL' 
           AND (s2.precision_aqua > s.precision_aqua OR (s2.precision_aqua = s.precision_aqua AND ABS(s2.bias_score) < ABS(s.bias_score)))
        ) as posicion_evento,
        (SELECT COUNT(*) 
         FROM auditoria_jueces_stats s3 
         WHERE s3.id_competicion = s.id_competicion 
           AND s3.entidad_tipo = 'GLOBAL'
        ) as total_jueces_evento
    FROM auditoria_jueces_stats s
    JOIN competiciones c ON s.id_competicion = c.id
    WHERE (s.group_key = '$group_key' OR s.id_juez = '$selected_juez_id') 
      AND s.entidad_tipo = 'GLOBAL'
    ORDER BY c.fecha DESC
";
$res_history = mysqli_query($connection, $q_history);
$history = [];
while($h = mysqli_fetch_assoc($res_history)) {
    $history[] = $h;
}

$chart_labels = array_reverse(array_column($history, 'comp_nombre'));
$chart_precision = array_reverse(array_column($history, 'precision_aqua'));
$chart_positions = array_reverse(array_column($history, 'posicion_evento'));

include('includes/header.php');
include('includes/navbar.php');
?>

<style>
@media print {
    .no-print, header, nav, #sidebar, #topbar, .topbar { display: none !important; }
    body, main { background: #ffffff !important; padding: 0 !important; margin: 0 !important; }
    .max-w-7xl { max-width: 100% !important; padding: 0 !important; }
    .card-hover, .shadow-sm { box-shadow: none !important; border: 1px solid #e2e8f0 !important; }
    .print-break { page-break-before: always; }

    /* Impresión aislada del Pasaporte Oficial */
    body.printing-pasaporte * {
        visibility: hidden !important;
    }
    body.printing-pasaporte #modalPasaporte,
    body.printing-pasaporte #modalPasaporte #pasaportePrintArea,
    body.printing-pasaporte #modalPasaporte #pasaportePrintArea * {
        visibility: visible !important;
    }
    body.printing-pasaporte #modalPasaporte {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        background: #ffffff !important;
        display: block !important;
        box-shadow: none !important;
        border: none !important;
    }
    body.printing-pasaporte #modalPasaporte > div {
        box-shadow: none !important;
        border: none !important;
        max-width: 100% !important;
        margin: 0 !important;
        border-radius: 0 !important;
    }
}
</style>

<main class="flex-1 flex flex-col min-w-0 bg-surface">
    <?php include('includes/topbar.php'); ?>
    
    <div class="px-4 py-5 sm:p-6 md:p-8 max-w-7xl mx-auto w-full font-lexend space-y-6">
        
        <!-- HEADER DE LA PÁGINA -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-2">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200/80">
                        <i class="fas fa-shield-alt text-[9px]"></i> World Aquatics BIAS
                    </span>
                    <span class="text-[11px] text-slate-400 font-semibold">• Autocontrol Arbitral</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                    <span class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center text-sm shadow-sm">
                        <i class="fas fa-user-check"></i>
                    </span>
                    Mi Auditoría Técnica
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 font-normal mt-0.5">
                    Métricas oficiales de precisión, alineación de notas y trayectoria en el censo arbitral.
                </p>
            </div>

            <!-- BOTONES DE ACCESO RÁPIDO -->
            <div class="flex items-center gap-2.5 flex-wrap no-print">
                <button onclick="openPasaporteModal()" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-xs font-bold shadow-sm hover:from-blue-700 hover:to-indigo-700 spring-press">
                    <i class="fas fa-passport text-xs"></i>
                    Pasaporte Técnico
                </button>
                <button onclick="window.print()" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-200/90 text-slate-700 text-xs font-bold shadow-sm hover:bg-slate-50 spring-press">
                    <i class="fas fa-print text-blue-600 text-xs"></i>
                    Imprimir Ficha
                </button>
                <a href="ranking_jueces.php" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-200/90 text-slate-700 text-xs font-bold shadow-sm hover:bg-slate-50 hover:border-slate-300 spring-press no-underline">
                    <i class="fas fa-trophy text-amber-500 text-xs"></i>
                    Ranking de Jueces
                </a>
                <?php if ($has_audit_data): ?>
                    <a href="perfil_juez.php?key=<?php echo $group_key; ?>" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-blue-600 text-white text-xs font-bold shadow-sm hover:bg-blue-700 spring-press no-underline">
                        <i class="fas fa-external-link-alt text-xs"></i>
                        Ficha Pública
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($es_admin): ?>
            <!-- SELECTOR PARA ADMINISTRADOR / MODO INSPECCIÓN (Zero-Gray Control) -->
            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/90 shadow-sm">
                <form action="mi_auditoria.php" method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100">
                            <i class="fas fa-user-shield text-xs"></i>
                        </div>
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block leading-tight">Control de Supervisión</span>
                            <span class="text-xs font-bold text-slate-800">Inspeccionar perfil de juez censado</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 flex-1 sm:max-w-md">
                        <div class="relative w-full">
                            <select name="juez_id" class="w-full bg-slate-50 hover:bg-slate-100/80 border border-slate-200 text-slate-800 font-bold rounded-xl pl-3.5 pr-8 py-2 text-xs focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all appearance-none cursor-pointer" onchange="this.form.submit()">
                                <?php
                                // Jueces con auditoría activa
                                $q_jueces_audit = "
                                    SELECT DISTINCT j.id, j.nombre, j.apellidos, j.licencia,
                                           (SELECT ROUND(AVG(precision_aqua), 1) FROM auditoria_jueces_stats WHERE id_juez = j.id AND entidad_tipo = 'GLOBAL') as prec
                                    FROM jueces j
                                    JOIN auditoria_jueces_stats a ON j.id = a.id_juez
                                    WHERE a.entidad_tipo = 'GLOBAL'
                                    ORDER BY j.apellidos ASC, j.nombre ASC
                                ";
                                $res_ja = mysqli_query($connection, $q_jueces_audit);
                                
                                // Resto de jueces
                                $q_jueces_otros = "
                                    SELECT j.id, j.nombre, j.apellidos, j.licencia
                                    FROM jueces j
                                    WHERE j.activo = 1 
                                      AND j.id NOT IN (SELECT DISTINCT id_juez FROM auditoria_jueces_stats WHERE entidad_tipo = 'GLOBAL')
                                    ORDER BY j.apellidos ASC, j.nombre ASC
                                ";
                                $res_jo = mysqli_query($connection, $q_jueces_otros);
                                ?>
                                <optgroup label="⭐ Jueces con Auditoría AQUA">
                                    <?php while ($ja = mysqli_fetch_assoc($res_ja)): 
                                        $sel = ($ja['id'] == $selected_juez_id) ? 'selected' : '';
                                        $label = htmlspecialchars($ja['apellidos'] . ', ' . $ja['nombre'] . ' (' . number_format($ja['prec'], 1) . '%)');
                                        echo "<option value='{$ja['id']}' $sel>{$label}</option>";
                                    endwhile; ?>
                                </optgroup>
                                <optgroup label="📋 Resto del Censo Arbitral">
                                    <?php while ($jo = mysqli_fetch_assoc($res_jo)): 
                                        $sel = ($jo['id'] == $selected_juez_id) ? 'selected' : '';
                                        $lic_txt = (!empty($jo['licencia'])) ? " [Lic: {$jo['licencia']}]" : '';
                                        $label = htmlspecialchars($jo['apellidos'] . ', ' . $jo['nombre'] . $lic_txt);
                                        echo "<option value='{$jo['id']}' $sel>{$label}</option>";
                                    endwhile; ?>
                                </optgroup>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-slate-400">
                                <i class="fas fa-chevron-down text-[10px]"></i>
                            </div>
                        </div>

                        <?php if ($my_juez_id > 0 && $selected_juez_id != $my_juez_id): ?>
                            <a href="mi_auditoria.php" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold whitespace-nowrap spring-press no-underline" title="Volver a mi propio perfil">
                                Mi Perfil
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- GRID PRINCIPAL -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- COLUMNA IZQUIERDA: CARTA DE ROL Y PANELES (4 COLS) -->
            <div class="lg:col-span-4 space-y-6">
                
                <!-- CARTA DE ROL (Light Mode Elegante) -->
                <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm relative overflow-hidden card-hover">
                    <!-- Detalle superior de acento -->
                    <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-blue-500 via-indigo-500 to-emerald-500"></div>
                    
                    <div class="flex items-start justify-between mb-4">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border <?php echo $severity_badge; ?>">
                            <span class="w-1.5 h-1.5 rounded-full <?php echo $severity_dot; ?> animate-pulse"></span>
                            <?php echo $severity_label; ?>
                        </span>
                        <span class="text-[11px] font-black text-slate-400 uppercase tracking-widest tabular-nums">
                            ID #<?php echo $selected_juez_id; ?>
                        </span>
                    </div>

                    <!-- FOTO / AVATAR -->
                    <div class="flex flex-col items-center text-center mb-5">
                        <div class="relative mb-3">
                            <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl bg-slate-100 border-2 border-slate-200/90 overflow-hidden shadow-sm flex items-center justify-center">
                                <?php if (!empty($user_data['foto']) && file_exists($user_data['foto'])): ?>
                                    <img src="<?php echo htmlspecialchars($user_data['foto']); ?>" alt="Foto Juez" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center text-3xl font-black text-white shadow-inner">
                                        <?php echo strtoupper(substr($nombre_display, 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="absolute -bottom-1 -right-1 w-7 h-7 rounded-lg bg-white border border-slate-200 shadow-sm flex items-center justify-center text-blue-600 text-xs">
                                <i class="fas fa-medal"></i>
                            </div>
                        </div>

                        <h2 class="text-lg font-black text-slate-900 uppercase tracking-tight leading-snug">
                            <?php echo htmlspecialchars($nombre_display); ?>
                        </h2>
                        
                        <div class="flex items-center gap-1.5 mt-1 flex-wrap justify-center">
                            <?php if ($juez_data && !empty($juez_data['licencia'])): ?>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-black bg-slate-100 text-slate-600 border border-slate-200 uppercase tracking-wide">
                                    Lic: <?php echo htmlspecialchars($juez_data['licencia']); ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($juez_data && !empty($juez_data['federacion_nombre'])): ?>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-black bg-blue-50 text-blue-700 border border-blue-100 uppercase tracking-wide">
                                    <?php echo htmlspecialchars($juez_data['federacion_nombre']); ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <p class="text-[11px] text-slate-500 font-medium mt-2 leading-relaxed max-w-xs">
                            <?php echo $severity_desc; ?>
                        </p>
                    </div>

                    <!-- RADAR CHART -->
                    <div class="bg-slate-50/70 rounded-2xl p-3 border border-slate-100 mb-5">
                        <div class="flex items-center justify-between mb-1 px-1">
                            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Carta de Habilidades</span>
                            <span class="text-[10px] font-bold text-blue-600">Normativa AQUA</span>
                        </div>
                        <div class="w-full h-44 sm:h-48 relative">
                            <canvas id="radarChart"></canvas>
                        </div>
                    </div>

                    <!-- SUB-KPIS TÉCNICOS TABULARES -->
                    <div class="grid grid-cols-2 gap-2.5">
                        <div class="bg-slate-50/80 rounded-xl p-3 border border-slate-100">
                            <span class="text-[9px] font-black uppercase text-slate-400 tracking-wider block mb-0.5">Alineación</span>
                            <span class="text-base sm:text-lg font-black text-slate-800 tabular-nums">
                                <?php echo ($has_audit_data) ? number_format($macro['precision_media'], 1) . '%' : 'N/D'; ?>
                            </span>
                            <span class="text-[10px] text-slate-500 block">Precisión AQUA</span>
                        </div>
                        <div class="bg-slate-50/80 rounded-xl p-3 border border-slate-100">
                            <span class="text-[9px] font-black uppercase text-slate-400 tracking-wider block mb-0.5">Estabilidad</span>
                            <span class="text-base sm:text-lg font-black text-slate-800 tabular-nums">
                                <?php echo ($has_audit_data) ? round($estabilidad_val) . '%' : 'N/D'; ?>
                            </span>
                            <span class="text-[10px] text-slate-500 block">Consistencia</span>
                        </div>
                    </div>
                </div>

                <!-- PARTICIPACIÓN TÉCNICA (Puestos y Paneles) -->
                <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm card-hover">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                            <h3 class="text-sm font-black text-slate-800 uppercase tracking-tight">Actuaciones Técnicas</h3>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-indigo-50 text-indigo-700 border border-indigo-100 tabular-nums">
                            <?php echo $total_roles; ?> Total
                        </span>
                    </div>

                    <?php if (empty($puestos_stats)): ?>
                        <div class="p-6 bg-slate-50 rounded-2xl text-center border border-dashed border-slate-200">
                            <i class="fas fa-user-clock text-slate-300 text-2xl mb-2"></i>
                            <p class="text-xs text-slate-500 font-semibold">Sin asignaciones registradas en paneles.</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-2">
                            <?php foreach ($puestos_stats as $ps): 
                                $pct = ($total_roles > 0) ? round(($ps['cantidad'] / $total_roles) * 100) : 0;
                            ?>
                                <div class="p-3 bg-slate-50/80 hover:bg-slate-100/70 rounded-xl border border-slate-100 transition-colors flex items-center justify-between">
                                    <div class="flex flex-col min-w-0 pr-2">
                                        <span class="text-xs font-bold text-slate-800 truncate"><?php echo htmlspecialchars($ps['puesto']); ?></span>
                                        <span class="text-[10px] text-slate-400"><?php echo $pct; ?>% del historial técnico</span>
                                    </div>
                                    <span class="text-sm font-black text-slate-900 tabular-nums px-2 py-0.5 rounded-md bg-white border border-slate-200/70 shadow-2xs">
                                        <?php echo $ps['cantidad']; ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

            <!-- COLUMNA DERECHA: KPIS, TRAYECTORIA, SESGO POR CLUB E HISTORIAL (8 COLS) -->
            <div class="lg:col-span-8 space-y-6">
                
                <!-- KPI CARDS GRID (Tabular Nums Obligatorio) -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                    
                    <!-- KPI 1: Precisión AQUA -->
                    <div class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-sm card-hover">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[9px] font-black uppercase tracking-wider text-slate-400">Precisión AQUA</span>
                            <span class="w-6 h-6 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-[10px]">
                                <i class="fas fa-bullseye"></i>
                            </span>
                        </div>
                        <div class="text-xl sm:text-2xl font-black text-slate-900 tabular-nums">
                            <?php echo ($has_audit_data) ? number_format($macro['precision_media'], 1) . '%' : '—'; ?>
                        </div>
                        <p class="text-[10px] text-slate-500 font-medium mt-1 truncate">Margen oficial ±0.2 ptos</p>
                    </div>

                    <!-- KPI 2: Bias Score Medio -->
                    <div class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-sm card-hover">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[9px] font-black uppercase tracking-wider text-slate-400">Sesgo Medio</span>
                            <span class="w-6 h-6 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-[10px]">
                                <i class="fas fa-balance-scale"></i>
                            </span>
                        </div>
                        <div class="text-xl sm:text-2xl font-black <?php echo ($has_audit_data && abs($macro['bias_medio']) >= 0.3) ? 'text-amber-600' : 'text-blue-600'; ?> tabular-nums">
                            <?php 
                            if ($has_audit_data) {
                                $sign = ($macro['bias_medio'] > 0) ? '+' : '';
                                echo $sign . number_format($macro['bias_medio'], 3);
                            } else {
                                echo '—';
                            }
                            ?>
                        </div>
                        <p class="text-[10px] text-slate-500 font-medium mt-1 truncate">Desviación vs panel</p>
                    </div>

                    <!-- KPI 3: Eventos Auditados -->
                    <div class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-sm card-hover">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[9px] font-black uppercase tracking-wider text-slate-400">Eventos</span>
                            <span class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-[10px]">
                                <i class="fas fa-calendar-check"></i>
                            </span>
                        </div>
                        <div class="text-xl sm:text-2xl font-black text-slate-900 tabular-nums">
                            <?php echo $macro['total_eventos']; ?>
                        </div>
                        <p class="text-[10px] text-slate-500 font-medium mt-1 truncate">Competiciones con notas</p>
                    </div>

                    <!-- KPI 4: Total Notas Registradas -->
                    <div class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-sm card-hover">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[9px] font-black uppercase tracking-wider text-slate-400">Notas Emitidas</span>
                            <span class="w-6 h-6 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-[10px]">
                                <i class="fas fa-edit"></i>
                            </span>
                        </div>
                        <div class="text-xl sm:text-2xl font-black text-slate-900 tabular-nums">
                            <?php echo number_format($macro['total_notas']); ?>
                        </div>
                        <p class="text-[10px] text-slate-500 font-medium mt-1 truncate">Calificaciones auditadas</p>
                    </div>

                </div>

                <!-- RECOMENDACIONES TÉCNICAS INTELIGENTES (SMART INSIGHTS TEMPORADA) -->
                <?php if(!empty($season_insights)): ?>
                <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-sm card-hover">
                    <div class="flex items-center justify-between mb-3.5">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs">
                                <i class="fas fa-lightbulb"></i>
                            </span>
                            <div>
                                <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight">Recomendaciones Pedagógicas de Autocontrol</h3>
                                <p class="text-[10px] text-slate-400 font-medium">Diagnóstico cualitativo para optimización arbitral y clinics técnicos</p>
                            </div>
                        </div>
                        <span class="text-[9px] font-black uppercase text-blue-700 bg-blue-50 px-2.5 py-0.5 rounded-full border border-blue-200">AQUA Mentor</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <?php foreach($season_insights as $si): ?>
                            <div class="p-3.5 rounded-2xl border <?php echo $si['bg']; ?> flex items-start gap-2.5">
                                <i class="fas <?php echo $si['icon']; ?> <?php echo $si['color']; ?> text-xs mt-0.5 shrink-0"></i>
                                <div class="min-w-0">
                                    <h4 class="text-xs font-black text-slate-800 mb-0.5"><?php echo $si['title']; ?></h4>
                                    <p class="text-[11px] text-slate-600 leading-relaxed"><?php echo $si['desc']; ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- TOP FIGURAS Y ELEMENTOS EVALUADOS -->
                <?php if(!empty($season_figures)): ?>
                <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-sm card-hover">
                    <div class="flex items-center justify-between mb-3.5">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
                                <i class="fas fa-person-swimming"></i>
                            </span>
                            <div>
                                <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight">Ejercicios Más Evaluados (Histórico)</h3>
                                <p class="text-[10px] text-slate-400 font-medium">Concordancia AQUA específica por figura y elemento técnico</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-black uppercase text-slate-400 tracking-wider tabular-nums"><?php echo count($season_figures); ?> ejercicios</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        <?php foreach($season_figures as $sf): 
                            $f_prec = (float)$sf['precision_media'];
                            $f_bias = (float)$sf['bias_medio'];
                            $f_badge = ($f_prec >= 70) ? 'text-emerald-700 bg-emerald-50 border-emerald-200' : (($f_prec >= 50) ? 'text-blue-700 bg-blue-50 border-blue-200' : 'text-amber-700 bg-amber-50 border-amber-200');
                        ?>
                            <div class="p-3 bg-slate-50/80 rounded-2xl border border-slate-100 flex flex-col justify-between">
                                <div class="mb-2">
                                    <span class="text-xs font-black text-slate-800 line-clamp-1 block"><?php echo htmlspecialchars($sf['nombre_entidad']); ?></span>
                                    <span class="text-[9px] font-bold text-slate-400 tabular-nums uppercase"><?php echo $sf['total_notas']; ?> notas</span>
                                </div>
                                <div class="flex items-center justify-between text-[10px] pt-1.5 border-t border-slate-200/50">
                                    <span class="px-2 py-0.5 rounded-md font-black border <?php echo $f_badge; ?> tabular-nums">
                                        <?php echo number_format($f_prec, 1); ?>% AQUA
                                    </span>
                                    <span class="font-black <?php echo ($f_bias >= 0 ? 'text-emerald-600' : 'text-rose-600'); ?> tabular-nums">
                                        Bias: <?php echo ($f_bias >= 0 ? '+' : '') . number_format($f_bias, 3); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- TRAYECTORIA Y EVOLUCIÓN TEMPORAL -->
                <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm card-hover">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight">Trayectoria de Precisión</h3>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">Evolución porcentual de concordancia técnica (AQUA ±0.2) en cada competición</p>
                        </div>
                        <?php if (!empty($history)): ?>
                            <span class="text-[11px] font-bold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-lg self-start sm:self-auto tabular-nums">
                                <?php echo count($history); ?> jornadas evaluadas
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="h-56 sm:h-64 relative">
                        <?php if (empty($history)): ?>
                            <div class="h-full flex flex-col items-center justify-center text-center p-6 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                                <i class="fas fa-chart-area text-slate-300 text-3xl mb-2"></i>
                                <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Sin datos de trayectoria para este juez</p>
                                <p class="text-[11px] text-slate-400 mt-1 max-w-sm">Los gráficos de evolución se generarán automáticamente a medida que actúe en paneles de figuras de la temporada.</p>
                            </div>
                        <?php else: ?>
                            <canvas id="evolutionChart"></canvas>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- SESGO POR CLUB (Club Bias) -->
                <?php if (!empty($clubs_bias_data)): ?>
                    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm card-hover">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs">
                                    <i class="fas fa-swimmer"></i>
                                </div>
                                <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight">Auditoría de Sesgo por Club (Club Bias)</h3>
                            </div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Objetividad</span>
                        </div>
                        <p class="text-xs text-slate-500 mb-4">
                            Desviación media de calificación según el club de la nadadora. Un valor cercano a <span class="font-bold text-slate-700">0.000</span> denota neutralidad técnica absoluta.
                        </p>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <?php foreach ($clubs_bias_data as $cb): 
                                $b_val = (float)$cb['bias_club'];
                                $abs_b = abs($b_val);
                                
                                if ($abs_b <= 0.15) {
                                    $c_badge = "text-slate-700 bg-slate-50 border-slate-200";
                                    $c_icon = "fa-check text-emerald-500";
                                    $c_desc = "Neutral";
                                } elseif ($b_val > 0.15) {
                                    $c_badge = "text-emerald-700 bg-emerald-50 border-emerald-200";
                                    $c_icon = "fa-arrow-up text-emerald-500";
                                    $c_desc = "Generoso";
                                } else {
                                    $c_badge = "text-rose-700 bg-rose-50 border-rose-200";
                                    $c_icon = "fa-arrow-down text-rose-500";
                                    $c_desc = "Exigente";
                                }
                            ?>
                                <div class="p-3 rounded-2xl border <?php echo $c_badge; ?> flex flex-col justify-between">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="text-xs font-black truncate"><?php echo htmlspecialchars($cb['nombre_entidad']); ?></span>
                                        <i class="fas <?php echo $c_icon; ?> text-[10px]"></i>
                                    </div>
                                    <div class="flex items-baseline justify-between mt-auto">
                                        <span class="text-base font-black tabular-nums">
                                            <?php echo ($b_val > 0 ? '+' : '') . number_format($b_val, 3); ?>
                                        </span>
                                        <span class="text-[9px] font-bold text-slate-400 tabular-nums">
                                            <?php echo $cb['notas']; ?> notas
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between text-[9px] text-slate-500 font-bold mt-1 pt-1 border-t border-slate-200/50">
                                        <span>B/A: <span class="text-rose-500"><?php echo $cb['bajas']; ?></span> / <span class="text-emerald-500"><?php echo $cb['altas']; ?></span></span>
                                        <?php 
                                        $ratio_cb = ($cb['bajas'] > 0) ? round($cb['altas'] / $cb['bajas'], 1) : (float)$cb['altas'];
                                        if ((int)$cb['notas'] >= 4 && ((int)$cb['altas'] >= 4 || (int)$cb['bajas'] >= 4) && ($ratio_cb >= 2.5 || $ratio_cb <= 0.4)): 
                                        ?>
                                            <span class="text-amber-600 font-black" title="Asimetría detectada">Asim. <?php echo $ratio_cb; ?>x</span>
                                        <?php else: ?>
                                            <span class="text-slate-400 font-semibold"><?php echo $c_desc; ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- HISTORIAL DE COMPETICIONES EVALUADAS (Tabla de Detalle) -->
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden card-hover">
                    <div class="p-5 sm:p-6 border-b border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                                <i class="fas fa-history"></i>
                            </div>
                            <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight">Competiciones Auditadas</h3>
                        </div>
                        <span class="text-[11px] font-black text-slate-400 uppercase tracking-wider">Histórico</span>
                    </div>

                    <?php if (empty($history)): ?>
                        <div class="p-8 text-center bg-slate-50/50">
                            <i class="fas fa-clipboard-check text-slate-300 text-3xl mb-2"></i>
                            <p class="text-sm font-bold text-slate-700">Sin auditorías de puntuación registradas</p>
                            <p class="text-xs text-slate-400 mt-1 max-w-md mx-auto">
                                Este juez aún no ha registrado notas de figuras o rutinas en las competiciones auditadas de la base de datos.
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50/80 text-slate-400 text-[10px] font-black uppercase tracking-wider border-b border-slate-100">
                                        <th class="px-5 py-3">Competición</th>
                                        <th class="px-4 py-3 text-center">Notas</th>
                                        <th class="px-4 py-3 text-center">Precisión AQUA</th>
                                        <th class="px-4 py-3 text-center">Sesgo (Bias)</th>
                                        <th class="px-4 py-3 text-center">Posición Evento</th>
                                        <th class="px-5 py-3 text-right">Detalle Micro</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php foreach ($history as $h): 
                                        $prec = (float)$h['precision_aqua'];
                                        if ($prec >= 60.0) {
                                            $badge_prec = "bg-emerald-50 text-emerald-700 border-emerald-200";
                                        } elseif ($prec >= 45.0) {
                                            $badge_prec = "bg-amber-50 text-amber-700 border-amber-200";
                                        } else {
                                            $badge_prec = "bg-rose-50 text-rose-700 border-rose-200";
                                        }
                                        
                                        $f_date = (!empty($h['fecha'])) ? date('d/m/Y', strtotime($h['fecha'])) : '';
                                    ?>
                                        <tr class="hover:bg-slate-50/70 transition-colors">
                                            <td class="px-5 py-3.5">
                                                <div class="font-bold text-slate-800 text-xs"><?php echo htmlspecialchars($h['comp_nombre']); ?></div>
                                                <div class="text-[10px] text-slate-400 font-medium tabular-nums"><?php echo $f_date; ?></div>
                                            </td>
                                            <td class="px-4 py-3.5 text-center font-bold text-slate-700 text-xs tabular-nums">
                                                <?php echo $h['total_notas']; ?>
                                            </td>
                                            <td class="px-4 py-3.5 text-center">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-black border <?php echo $badge_prec; ?> tabular-nums">
                                                    <?php echo number_format($prec, 1); ?>%
                                                </span>
                                            </td>
                                            <td class="px-4 py-3.5 text-center font-black text-xs text-slate-700 tabular-nums">
                                                <?php 
                                                $b = (float)$h['bias_score'];
                                                echo ($b > 0 ? '+' : '') . number_format($b, 3);
                                                ?>
                                            </td>
                                            <td class="px-4 py-3.5 text-center">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black bg-slate-100 text-slate-700 tabular-nums">
                                                    #<?php echo $h['posicion_evento']; ?> <span class="text-slate-400 font-normal ml-0.5">/ <?php echo $h['total_jueces_evento']; ?></span>
                                                </span>
                                            </td>
                                            <td class="px-5 py-3.5 text-right">
                                                <?php if (!empty($h['pjs_asociados'])): ?>
                                                    <a href="analisis_juez_detalle.php?pjs=<?php echo urlencode($h['pjs_asociados']); ?>&comp_id=<?php echo $h['id_competicion']; ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-900 text-white text-[11px] font-bold hover:bg-blue-600 active:scale-[0.98] transition-all shadow-sm no-underline" title="Ver análisis micro de cada figura">
                                                        <i class="fas fa-search-plus text-[10px]"></i> Ver Micro
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-slate-300 text-xs">—</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

        </div>

        <!-- ========================================== -->
        <!-- MODAL PASAPORTE TÉCNICO ARBITRAL (Mejora 5) -->
        <!-- ========================================== -->
        <div id="modalPasaporte" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm overflow-y-auto p-4 sm:p-6 md:p-10 flex items-center justify-center">
            <div class="relative w-full max-w-4xl bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden my-auto transform transition-all duration-300">
                
                <!-- Modal Header (no print) -->
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between no-print">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center text-xs shadow-sm">
                            <i class="fas fa-passport"></i>
                        </span>
                        <div>
                            <h3 class="text-sm font-black text-slate-800 uppercase tracking-tight">Pasaporte Técnico Arbitral</h3>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Acreditación Oficial • Normativa World Aquatics</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="printPasaporte()" class="px-3 py-1.5 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 transition-all flex items-center gap-1.5 shadow-sm spring-press">
                            <i class="fas fa-print text-xs"></i> Imprimir Pasaporte
                        </button>
                        <button type="button" onclick="closePasaporteModal()" class="w-8 h-8 rounded-xl bg-slate-200 text-slate-600 hover:bg-slate-300 flex items-center justify-center transition-all text-xs">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <!-- PASAPORTE DOCUMENT (Imprimible oficial) -->
                <div id="pasaportePrintArea" class="p-6 sm:p-10 font-lexend space-y-8 bg-white text-slate-800">
                    
                    <!-- Encabezado Oficial Federativo -->
                    <div class="border-b-2 border-slate-900 pb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-2xl bg-slate-900 text-white p-2.5 flex items-center justify-center shrink-0 shadow-md">
                                <img src="./img/logo_sincrm_icon.png" class="w-full h-full object-contain filter invert brightness-200" alt="FNRM">
                            </div>
                            <div>
                                <span class="text-[9px] font-black tracking-[0.25em] text-blue-600 uppercase block">Comité Técnico de Natación Artística</span>
                                <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight uppercase leading-tight">Pasaporte Técnico de Acreditación Arbitral</h2>
                                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">World Aquatics (AQUA) Protocol • Ficha Técnica Oficial de Desempeño</p>
                            </div>
                        </div>
                        <div class="text-right sm:border-l sm:border-slate-200 sm:pl-6 shrink-0">
                            <span class="text-[9px] font-black uppercase tracking-widest text-slate-400 block">Identificador Digital</span>
                            <span class="font-mono text-xs font-black text-slate-900 tracking-wider tabular-nums">
                                AQUA-<?php echo str_pad($selected_juez_id, 4, '0', STR_PAD_LEFT); ?>-<?php echo strtoupper(substr(md5('SINCRM-'.$selected_juez_id.'-'.$macro['total_notas']), 0, 6)); ?>
                            </span>
                            <span class="text-[9px] font-semibold text-emerald-600 block mt-0.5">● Certificado Homologado</span>
                        </div>
                    </div>

                    <!-- Tarjeta de Identificación del Juez -->
                    <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200 grid grid-cols-1 sm:grid-cols-12 gap-5 items-center">
                        <div class="sm:col-span-8 space-y-2">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider bg-slate-900 text-white">
                                    Temporada 2026/2027
                                </span>
                                <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider bg-blue-100 text-blue-800">
                                    <?php echo !empty($juez_data['nacional']) && $juez_data['nacional'] == 'si' ? 'Juez Nacional RFEN' : 'Juez Territorial'; ?>
                                </span>
                                <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider border <?php echo $severity_badge; ?>">
                                    <?php echo $severity_label; ?>
                                </span>
                            </div>
                            <h3 class="text-xl font-black text-slate-900 tracking-tight uppercase">
                                <?php echo $nombre_display; ?>
                            </h3>
                            <div class="grid grid-cols-2 gap-3 pt-1 text-xs text-slate-600">
                                <div>
                                    <span class="text-[9px] font-black uppercase text-slate-400 block tracking-wider">Licencia Federativa</span>
                                    <span class="font-bold text-slate-800 tabular-nums"><?php echo !empty($juez_data['licencia']) ? htmlspecialchars($juez_data['licencia']) : 'LIC-FNRM-' . str_pad($selected_juez_id, 4, '0', STR_PAD_LEFT); ?></span>
                                </div>
                                <div>
                                    <span class="text-[9px] font-black uppercase text-slate-400 block tracking-wider">Federación Territorial</span>
                                    <span class="font-bold text-slate-800 truncate block"><?php echo htmlspecialchars($juez_data['federacion_nombre'] ?? 'Fed. Natación Región de Murcia (FNRM)'); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="sm:col-span-4 bg-white rounded-xl p-4 border border-slate-200/80 text-center shadow-xs">
                            <span class="text-[9px] font-black uppercase tracking-widest text-slate-400 block mb-1">Calificación AQUA</span>
                            <div class="text-3xl font-black text-blue-600 tabular-nums leading-none mb-1">
                                <?php echo number_format($macro['precision_media'], 1); ?>%
                            </div>
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wide">
                                Precisión Oficial (±0.2 ptos)
                            </span>
                        </div>
                    </div>

                    <!-- Cuadrícula de Métricas Oficiales de Arbitraje -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="p-4 bg-white rounded-2xl border border-slate-200 text-center shadow-xs">
                            <span class="text-[9px] font-black uppercase tracking-widest text-slate-400 block mb-1">Eventos Auditados</span>
                            <span class="text-2xl font-black text-slate-800 tabular-nums"><?php echo $macro['total_eventos']; ?></span>
                            <span class="text-[9px] text-slate-400 block mt-0.5">Competiciones oficiales</span>
                        </div>
                        <div class="p-4 bg-white rounded-2xl border border-slate-200 text-center shadow-xs">
                            <span class="text-[9px] font-black uppercase tracking-widest text-slate-400 block mb-1">Puntuaciones Emitidas</span>
                            <span class="text-2xl font-black text-slate-800 tabular-nums"><?php echo $macro['total_notas']; ?></span>
                            <span class="text-[9px] text-slate-400 block mt-0.5">Notas registradas</span>
                        </div>
                        <div class="p-4 bg-white rounded-2xl border border-slate-200 text-center shadow-xs">
                            <span class="text-[9px] font-black uppercase tracking-widest text-slate-400 block mb-1">Sesgo Sistemático (Bias)</span>
                            <span class="text-2xl font-black <?php echo abs($macro['bias_medio']) >= 0.25 ? 'text-rose-600' : 'text-blue-600'; ?> tabular-nums">
                                <?php echo ($macro['bias_medio'] >= 0 ? '+' : '') . number_format($macro['bias_medio'], 3); ?>
                            </span>
                            <span class="text-[9px] text-slate-400 block mt-0.5">Desviación del panel</span>
                        </div>
                        <div class="p-4 bg-white rounded-2xl border border-slate-200 text-center shadow-xs">
                            <span class="text-[9px] font-black uppercase tracking-widest text-slate-400 block mb-1">Índice de Estabilidad</span>
                            <span class="text-2xl font-black text-emerald-600 tabular-nums"><?php echo round($estabilidad_val, 1); ?>%</span>
                            <span class="text-[9px] text-slate-400 block mt-0.5">Consistencia en campana</span>
                        </div>
                    </div>

                    <!-- Gráfico Pentágono + Resumen de Puestos -->
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                        <div class="md:col-span-6 bg-slate-50 p-5 rounded-2xl border border-slate-200 flex flex-col items-center">
                            <h4 class="text-xs font-black uppercase tracking-wider text-slate-700 mb-3 flex items-center gap-2">
                                <i class="fas fa-radar text-blue-600"></i> Pentágono de Competencias Arbitrales
                            </h4>
                            <div class="relative w-full max-w-[280px] h-[220px]">
                                <canvas id="pasaporteRadarChart"></canvas>
                            </div>
                        </div>
                        <div class="md:col-span-6 space-y-4">
                            <div>
                                <h4 class="text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Desglose de Desempeño por Panel</h4>
                                <div class="space-y-1.5 text-xs">
                                    <?php foreach($puestos_stats as $p): ?>
                                        <div class="flex items-center justify-between p-2 rounded-xl bg-white border border-slate-100 shadow-xs">
                                            <span class="font-bold text-slate-700"><?php echo htmlspecialchars($p['puesto']); ?></span>
                                            <span class="font-black text-slate-900 tabular-nums bg-slate-100 px-2.5 py-0.5 rounded-lg text-[11px]"><?php echo $p['cantidad']; ?> act.</span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="p-3.5 bg-blue-50/70 rounded-xl border border-blue-100 text-[11px] text-slate-600 leading-relaxed">
                                <p class="font-bold text-blue-900 mb-1 flex items-center gap-1.5">
                                    <i class="fas fa-info-circle text-blue-600"></i> Dictamen de Homologación:
                                </p>
                                <?php echo $severity_desc; ?> El evaluado cumple con los estándares exigidos para designación en fases eliminatorias y finales territoriales.
                            </div>
                        </div>
                    </div>

                    <!-- Bloque de Firmas y Validación Digital -->
                    <div class="pt-6 border-t-2 border-slate-900 grid grid-cols-3 gap-6 text-center items-end">
                        <div>
                            <div class="h-16 flex items-center justify-center">
                                <span class="font-serif italic text-slate-400 text-xs">Firma Validada Digitalmente</span>
                            </div>
                            <div class="border-t border-slate-300 pt-2">
                                <p class="text-xs font-black text-slate-800 uppercase leading-none"><?php echo $nombre_display; ?></p>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Juez Colegiado Evaluado</p>
                            </div>
                        </div>

                        <div>
                            <div class="h-16 flex flex-col items-center justify-center">
                                <span class="text-[10px] font-black text-blue-700 uppercase tracking-widest">SELLO OFICIAL</span>
                                <span class="text-[8px] font-bold text-slate-400 uppercase tracking-wider">FNRM • SINCRM v4</span>
                                <span class="font-mono text-[9px] text-slate-500 tabular-nums mt-0.5">AQUA PROTOCOL V4.1</span>
                            </div>
                            <div class="border-t border-slate-300 pt-2">
                                <p class="text-xs font-black text-slate-800 uppercase leading-none">Validación Técnica</p>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Sistema Integrado SINCRM</p>
                            </div>
                        </div>

                        <div>
                            <div class="h-16 flex items-center justify-center">
                                <span class="font-serif italic text-slate-400 text-xs">Comité Técnico de Jueces</span>
                            </div>
                            <div class="border-t border-slate-300 pt-2">
                                <p class="text-xs font-black text-slate-800 uppercase leading-none">Vº Bº Juez Árbitro</p>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Director Técnico FNRM</p>
                            </div>
                        </div>
                    </div>

                    <div class="text-[9px] text-slate-400 text-center font-bold uppercase tracking-wider pt-2">
                        Documento expedido oficialmente el <?php echo date('d/m/Y H:i'); ?> • Validez reglamentaria para el censo de la temporada vigente (Temporada 2026/2027).
                    </div>

                </div>

            </div>
        </div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    
    // --- 1. RADAR CHART (CARTA DE HABILIDADES TÉCNICAS) ---
    const ctxRadar = document.getElementById('radarChart');
    if (ctxRadar) {
        new Chart(ctxRadar.getContext('2d'), {
            type: 'radar',
            data: {
                labels: ['Equilibrio', 'Consistencia', 'Neutralidad', 'Precisión AQUA', 'Experiencia'],
                datasets: [{
                    label: 'Puntuación Técnica',
                    data: [
                        <?php echo $radar_stats['Equilibrio']; ?>, 
                        <?php echo $radar_stats['Consistencia']; ?>, 
                        <?php echo $radar_stats['Neutralidad']; ?>, 
                        <?php echo $radar_stats['Precisión AQUA']; ?>, 
                        <?php echo $radar_stats['Experiencia']; ?>
                    ],
                    backgroundColor: 'rgba(37, 99, 235, 0.12)',
                    borderColor: 'rgba(37, 99, 235, 0.9)',
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: 'rgba(37, 99, 235, 1)',
                    pointHoverBackgroundColor: 'rgba(37, 99, 235, 1)',
                    pointHoverBorderColor: '#ffffff',
                    borderWidth: 2,
                    pointRadius: 3.5,
                    pointHoverRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.raw + ' / 100';
                            }
                        }
                    }
                },
                scales: {
                    r: {
                        angleLines: { color: 'rgba(0, 0, 0, 0.06)' },
                        grid: { color: 'rgba(0, 0, 0, 0.06)' },
                        pointLabels: {
                            color: '#475569',
                            font: { size: 9, family: "'Lexend', sans-serif", weight: 'bold' }
                        },
                        ticks: { display: false, min: 0, max: 100 }
                    }
                }
            }
        });
    }

    // --- 2. EVOLUTION CHART (TRAYECTORIA DE PRECISIÓN) ---
    const ctxEvolution = document.getElementById('evolutionChart');
    if (ctxEvolution) {
        const labels = <?php echo json_encode($chart_labels); ?>;
        const precisionData = <?php echo json_encode($chart_precision); ?>;
        const positionsData = <?php echo json_encode($chart_positions); ?>;

        new Chart(ctxEvolution.getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Precisión AQUA (%)',
                    data: precisionData,
                    borderColor: '#2563eb',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#2563eb',
                    pointBorderWidth: 2,
                    pointRadius: 4.5,
                    pointHoverRadius: 6,
                    fill: true,
                    backgroundColor: 'rgba(37, 99, 235, 0.05)',
                    tension: 0.25
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const idx = context.dataIndex;
                                const prec = context.raw;
                                const pos = positionsData[idx] || '—';
                                return ['Precisión: ' + Number(prec).toFixed(1) + '%', 'Puesto en evento: #' + pos];
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        min: 0,
                        max: 100,
                        ticks: {
                            stepSize: 20,
                            callback: function(val) { return val + '%'; },
                            font: { family: "'Lexend', sans-serif", size: 10 }
                        },
                        grid: { color: 'rgba(0, 0, 0, 0.04)' }
                    },
                    x: {
                        ticks: {
                            font: { family: "'Lexend', sans-serif", size: 10 },
                            maxRotation: 20,
                            minRotation: 0
                        },
                        grid: { display: false }
                    }
                }
            }
        });
    }
});

let pasaporteRadarInstance = null;

function openPasaporteModal() {
    const modal = document.getElementById('modalPasaporte');
    if (!modal) return;
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    if (!pasaporteRadarInstance) {
        const ctxP = document.getElementById('pasaporteRadarChart');
        if (ctxP) {
            pasaporteRadarInstance = new Chart(ctxP.getContext('2d'), {
                type: 'radar',
                data: {
                    labels: ['Equilibrio', 'Consistencia', 'Neutralidad', 'Precisión AQUA', 'Experiencia'],
                    datasets: [{
                        label: 'Competencia Arbitral',
                        data: [
                            <?php echo $radar_stats['Equilibrio']; ?>, 
                            <?php echo $radar_stats['Consistencia']; ?>, 
                            <?php echo $radar_stats['Neutralidad']; ?>, 
                            <?php echo $radar_stats['Precisión AQUA']; ?>, 
                            <?php echo $radar_stats['Experiencia']; ?>
                        ],
                        backgroundColor: 'rgba(37, 99, 235, 0.18)',
                        borderColor: '#2563eb',
                        borderWidth: 2,
                        pointBackgroundColor: '#2563eb',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 1.5,
                        pointRadius: 3.5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        r: {
                            angleLines: { color: 'rgba(0, 0, 0, 0.08)' },
                            grid: { color: 'rgba(0, 0, 0, 0.08)' },
                            pointLabels: { color: '#334155', font: { size: 9, family: "'Lexend', sans-serif", weight: 'bold' } },
                            ticks: { display: false, min: 0, max: 100 }
                        }
                    }
                }
            });
        }
    }
}

function closePasaporteModal() {
    const modal = document.getElementById('modalPasaporte');
    if (!modal) return;
    modal.classList.add('hidden');
    document.body.style.overflow = '';
}

function printPasaporte() {
    document.body.classList.add('printing-pasaporte');
    window.print();
    setTimeout(() => {
        document.body.classList.remove('printing-pasaporte');
    }, 600);
}
</script>

<style>
.spring-press {
    transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.2s ease;
}
.spring-press:active {
    transform: scale(0.97);
}
.card-hover {
    transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease, border-color 0.25s ease;
}
.card-hover:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -4px rgba(0, 0, 0, 0.06), 0 4px 8px -2px rgba(0, 0, 0, 0.03);
}
</style>

<?php
include('includes/scripts.php');
include('includes/footer.php');
?>
