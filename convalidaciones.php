<?php
/**
 * Convalidaciones de Figuras para Pases de Nivel
 * Sistema SINCRM v4 - FNRM / RFEN
 */
include('security.php');

// 1. Control de Acceso: Administradores (1, 2, 3) y Usuarios Club (5)
$rol_usuario = (int)($_SESSION['id_rol'] ?? 0);
$es_admin = in_array($rol_usuario, [1, 2, 3]);
$es_club = ($rol_usuario === 5);

if (!$es_admin && !$es_club) {
    header('Location: index.php');
    exit();
}

// 1.1 Filtrado de Club (Estricto para Club, Opcional para Admin)
$club_id_filtro = 0;
$club_nombre_filtro = '';

if ($es_club) {
    // Si es rol Club, se fija obligatoriamente su club de sesión
    $club_id_filtro = (int)($_SESSION['club'] ?? 0);
    // Asignación explícita para Elena Cerón
    if ($club_id_filtro === 0 && (($_SESSION['email'] ?? '') === 'elenacerondiaz@gmail.com' || ($_SESSION['username'] ?? '') === 'Elena Cerón')) {
        $club_id_filtro = 1; // Club Sincro Alhama
    }
} elseif ($es_admin && isset($_GET['club']) && intval($_GET['club']) > 0) {
    $club_id_filtro = intval($_GET['club']);
}

if ($club_id_filtro > 0) {
    $res_cl_info = mysqli_query($connection, "SELECT nombre, nombre_corto FROM clubes WHERE id = '$club_id_filtro'");
    if ($res_cl_info && $cl_row = mysqli_fetch_assoc($res_cl_info)) {
        $club_nombre_filtro = !empty($cl_row['nombre_corto']) ? $cl_row['nombre_corto'] : $cl_row['nombre'];
    }
}

// 2. Definición Oficial de Ternas de Figuras y Baremos RFEN / FNRM (Normativa de Niveles)
$config_ternas = [
    'Alevin I' => [
        'nombre' => 'Alevín I',
        'subtitulo' => 'Hasta 11 años (2016 y posteriores) · Normativa Niveles RFEN',
        'corte' => 4.0000,
        'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'accent' => 'emerald',
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
        'badge' => 'bg-amber-50 text-amber-700 border-amber-200',
        'accent' => 'amber',
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
        'badge' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
        'accent' => 'indigo',
        'figuras' => [
            '367'  => ['ejercicio' => 10, 'nombre' => 'Barracuda Rodilla Doblada Twirl Tirabuzón 180º', 'gd' => 2.3],
            '356J' => ['ejercicio' => 11, 'nombre' => 'Whip Tirabuzón Combinado 360º', 'gd' => 3.4],
            '407'  => ['ejercicio' => 12, 'nombre' => 'Pez Espada Pierna Estirada con Rotación de Ariana', 'gd' => 2.6]
        ]
    ]
];

// 3. Parámetros de Selección
$id_comp_pase = isset($_GET['comp_pase']) ? intval($_GET['comp_pase']) : 83;
$vista_censo = isset($_GET['censo']) && $_GET['censo'] === 'todos' ? 'todos' : 'inscritas';

// Endpoint AJAX: Enviar informe a Telegram
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_telegram') {
    header('Content-Type: application/json; charset=utf-8');
    
    $bot_token = "8614746466:AAGQh05ui9E5eoUkhp2SV14mOF7cNbUqTC4";
    $chat_id = "129347404";
    $telegram_text = $_POST['telegram_text'] ?? '';
    
    if (empty($telegram_text)) {
        echo json_encode(['status' => 'error', 'message' => 'El texto del informe está vacío']);
        exit();
    }
    
    $url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
    $postData = http_build_query([
        'chat_id' => $chat_id,
        'text' => $telegram_text,
        'parse_mode' => 'Markdown'
    ]);
    
    $opts = [
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'content' => $postData,
            'timeout' => 10
        ]
    ];
    $context  = stream_context_create($opts);
    $result = @file_get_contents($url, false, $context);
    
    if ($result !== false) {
        echo json_encode(['status' => 'ok', 'message' => 'Informe enviado correctamente a Telegram']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo conectar con la API de Telegram']);
    }
    exit();
}

// 4. Consultas auxiliares de competiciones
$comp_pase_info = null;
$q_info = "SELECT id, nombre, fecha, lugar, temporada FROM competiciones WHERE id = '$id_comp_pase'";
$res_info = mysqli_query($connection, $q_info);
if ($res_info) $comp_pase_info = mysqli_fetch_assoc($res_info);
$nombre_pase = $comp_pase_info['nombre'] ?? "Competición #$id_comp_pase";

// Temporada actual (de la competición activa o del pase)
$temporada_actual = $comp_pase_info['temporada'] ?? '2026-2027';

// Selector de Pases: Solo competiciones con nombre "pase"
$q_pases = "SELECT id, nombre, fecha, lugar, temporada FROM competiciones WHERE LOWER(nombre) LIKE '%pase%' ORDER BY fecha DESC, id DESC";
$res_pases = mysqli_query($connection, $q_pases);

// Jornadas de Referencia: Solo competiciones de figuras de la temporada actual ya realizadas
$q_refs = "SELECT id, nombre, fecha, figuras, temporada 
           FROM competiciones 
           WHERE figuras IN ('si', 'mixta') 
             AND LOWER(nombre) NOT LIKE '%pase%' 
             AND LOWER(nombre) NOT LIKE '%nivel%'
             AND temporada = '$temporada_actual'
             AND fecha <= CURDATE() 
           ORDER BY fecha DESC, id DESC";
$res_refs = mysqli_query($connection, $q_refs);

// Obtener IDs de jornadas disponibles
$disponibles_refs = [];
if ($res_refs) {
    while ($rf = mysqli_fetch_assoc($res_refs)) {
        $disponibles_refs[] = (int)$rf['id'];
    }
    mysqli_data_seek($res_refs, 0);
}

// Si no se pasaron por GET, seleccionamos por defecto todas las jornadas ya realizadas de la temporada
if (!isset($_GET['comps_ref'])) {
    $comps_referencia = !empty($disponibles_refs) ? $disponibles_refs : [81, 82];
} else {
    $comps_referencia = array_map('intval', (array)$_GET['comps_ref']);
}
if (empty($comps_referencia)) {
    $comps_referencia = !empty($disponibles_refs) ? $disponibles_refs : [81, 82];
}

// Lista oficial de nadadoras inscritas para la Competición 83 (Pase de Nivel 1)
$inscritas_comp_83 = [
    'Alevin I'  => [459, 608],
    'Alevin II' => [483, 85, 399, 400, 438, 283],
    'Infantil'  => [94, 83, 279, 264, 84, 278, 485, 90, 514]
];

// 5. Motor de Cálculo de Convalidaciones
$ref_ids_str = implode(',', $comps_referencia);
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
    
    // Obtener nadadoras a evaluar según la vista
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
        while ($row = mysqli_fetch_assoc($res_nads)) {
            $lista_nadadoras[] = $row;
        }
    } else {
        // Todas las que compitieron en las jornadas de referencia para esta categoría
        $filtro_edad = "";
        if ($cat_key === 'Alevin I') $filtro_edad = "n.año_nacimiento >= 2016";
        elseif ($cat_key === 'Alevin II') $filtro_edad = "n.año_nacimiento = 2015";
        elseif ($cat_key === 'Infantil') $filtro_edad = "n.año_nacimiento BETWEEN 2012 AND 2014";
        
        $where_edad = $filtro_edad ? " AND $filtro_edad " : "";
        $q_nads = "SELECT DISTINCT n.id, n.apellidos, n.nombre, n.año_nacimiento, cl.nombre_corto as club
                   FROM inscripciones_figuras i
                   JOIN fases f ON i.id_fase = f.id
                   JOIN nadadoras n ON i.id_nadadora = n.id
                   LEFT JOIN clubes cl ON n.club = cl.id
                   WHERE f.id_competicion IN ($ref_ids_str) $where_edad $where_club
                   ORDER BY cl.nombre_corto ASC, n.apellidos ASC";
        $res_nads = mysqli_query($connection, $q_nads);
        while ($row = mysqli_fetch_assoc($res_nads)) {
            $lista_nadadoras[] = $row;
        }
    }
    
    // Evaluar figura a figura para cada nadadora
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
            $estado_texto = "CONVALIDA TODO";
            $estado_badge = "bg-emerald-50 text-emerald-700 border-emerald-200";
            $calculo_categorias[$cat_key]['stats']['full']++;
            $total_convalidadas_full++;
        } elseif ($figs_convalidadas > 0) {
            $estado_texto = "PARCIAL ($figs_convalidadas/3)";
            $estado_badge = "bg-amber-50 text-amber-700 border-amber-200";
            $calculo_categorias[$cat_key]['stats']['parcial']++;
            $total_parciales++;
        } else {
            $estado_texto = "DEBE NADAR LAS 3";
            $estado_badge = "bg-rose-50 text-rose-700 border-rose-200";
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
            'estado_texto' => $estado_texto,
            'estado_badge' => $estado_badge
        ];
    }
}

include('includes/header.php');
include('includes/navbar.php');
?>

<main class="flex-1 flex flex-col min-w-0 bg-surface">
    <?php include('includes/topbar.php'); ?>

    <div class="px-3 py-4 sm:p-6 md:p-8 max-w-7xl mx-auto w-full font-lexend space-y-6">
        
        <!-- HEADER & INTRO -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs">
            <div class="space-y-1">
                <div class="flex flex-wrap items-center gap-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-bold border border-blue-100">
                        <i class="fas fa-stamp text-[10px]"></i> Convalidaciones de Figuras
                    </div>
                    <?php if ($es_club && !empty($club_nombre_filtro)): ?>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-800 rounded-full text-xs font-bold border border-emerald-200">
                            <i class="fas fa-shield-halved text-emerald-600 text-[10px]"></i> Club: <?php echo htmlspecialchars($club_nombre_filtro); ?>
                        </div>
                    <?php elseif ($club_id_filtro > 0 && !empty($club_nombre_filtro)): ?>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-800 rounded-full text-xs font-bold border border-amber-200">
                            <i class="fas fa-filter text-amber-600 text-[10px]"></i> Filtrado por: <?php echo htmlspecialchars($club_nombre_filtro); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight">Convalidaciones de Figuras</h1>
                <p class="text-xs sm:text-sm text-slate-500 font-medium">
                    Evaluación 1 a 1 de convalidaciones oficiales según Normativa de Niveles RFEN / FNRM.
                    <?php if ($es_club): ?>
                        <span class="text-blue-600 font-bold block sm:inline">Mostrando exclusivamente las deportistas de tu club.</span>
                    <?php endif; ?>
                </p>
            </div>

            <!-- Botones de Acción Rápida -->
            <?php
            $ref_params = '';
            foreach ($comps_referencia as $cr) {
                $ref_params .= '&comps_ref[]=' . $cr;
            }
            $club_param = ($club_id_filtro > 0) ? "&club=" . $club_id_filtro : "";
            $pdf_url = "informes/informe_convalidaciones.php?comp_pase=" . $id_comp_pase . "&censo=" . $vista_censo . $ref_params . $club_param;
            ?>
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="<?php echo $pdf_url; ?>" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-2xl text-xs font-bold transition-all shadow-xs active:scale-95 no-underline">
                    <i class="fas fa-file-pdf text-sm"></i> Exportar a PDF
                </a>
                <button type="button" onclick="copiarTexto()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-xs font-bold transition-all shadow-xs active:scale-95" title="Copiar informe al portapapeles">
                    <i class="fas fa-copy text-sm"></i> Copiar
                </button>
                <button type="button" onclick="enviarTelegram()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-sky-600 hover:bg-sky-700 text-white rounded-2xl text-xs font-bold transition-all shadow-xs active:scale-95">
                    <i class="fab fa-telegram text-sm"></i> Enviar por Telegram
                </button>
            </div>
        </div>

        <!-- PANEL DE FILTROS Y CONFIGURACIÓN -->
        <form method="GET" action="convalidaciones.php" class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-<?php echo ($es_admin) ? '4' : '3'; ?> gap-4">
                
                <!-- Selector de Competición Pase de Nivel -->
                <div>
                    <label class="block text-[11px] font-black uppercase tracking-wider text-slate-400 mb-1.5">Pase de Nivel a Evaluar</label>
                    <select name="comp_pase" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-3.5 py-2.5 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                        <?php 
                        mysqli_data_seek($res_pases, 0);
                        while ($p = mysqli_fetch_assoc($res_pases)): ?>
                            <option value="<?php echo $p['id']; ?>" <?php echo $p['id'] == $id_comp_pase ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($p['nombre']); ?> (<?php echo date('d/m/Y', strtotime($p['fecha'])); ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Competiciones de Referencia -->
                <div>
                    <label class="block text-[11px] font-black uppercase tracking-wider text-slate-400 mb-1.5">Jornadas de Liga de Referencia</label>
                    <div class="flex flex-wrap gap-2">
                        <?php 
                        mysqli_data_seek($res_refs, 0);
                        while ($r = mysqli_fetch_assoc($res_refs)): 
                            $checked = in_array((int)$r['id'], $comps_referencia) ? 'checked' : '';
                        ?>
                            <label class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 cursor-pointer hover:bg-slate-100 transition-all">
                                <input type="checkbox" name="comps_ref[]" value="<?php echo $r['id']; ?>" <?php echo $checked; ?> onchange="this.form.submit()" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <span>#<?php echo $r['id']; ?> <?php echo htmlspecialchars(mb_substr($r['nombre'], 0, 24)); ?></span>
                            </label>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- Selector de Club (Solo si es Administrador) -->
                <?php if ($es_admin): ?>
                <div>
                    <label class="block text-[11px] font-black uppercase tracking-wider text-slate-400 mb-1.5">Filtrar por Club</label>
                    <select name="club" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-3.5 py-2.5 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                        <option value="0">Todos los Clubes</option>
                        <?php
                        $res_clubes_list = mysqli_query($connection, "SELECT id, nombre, nombre_corto FROM clubes WHERE activo = 1 ORDER BY nombre_corto ASC");
                        while ($cl = mysqli_fetch_assoc($res_clubes_list)): ?>
                            <option value="<?php echo $cl['id']; ?>" <?php echo $cl['id'] == $club_id_filtro ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cl['nombre_corto'] ?: $cl['nombre']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <?php endif; ?>

                <!-- Censo a Evaluar -->
                <div>
                    <label class="block text-[11px] font-black uppercase tracking-wider text-slate-400 mb-1.5">Deportistas Mostradas</label>
                    <div class="flex items-center gap-2">
                        <label class="flex-1 text-center py-2.5 px-3 rounded-2xl border text-xs font-bold cursor-pointer transition-all <?php echo $vista_censo === 'inscritas' ? 'bg-blue-50 border-blue-200 text-blue-700 shadow-xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'; ?>">
                            <input type="radio" name="censo" value="inscritas" <?php echo $vista_censo === 'inscritas' ? 'checked' : ''; ?> onchange="this.form.submit()" class="hidden">
                            Inscritas al Pase
                        </label>
                        <label class="flex-1 text-center py-2.5 px-3 rounded-2xl border text-xs font-bold cursor-pointer transition-all <?php echo $vista_censo === 'todos' ? 'bg-blue-50 border-blue-200 text-blue-700 shadow-xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'; ?>">
                            <input type="radio" name="censo" value="todos" <?php echo $vista_censo === 'todos' ? 'checked' : ''; ?> onchange="this.form.submit()" class="hidden">
                            <?php echo $es_club ? 'Todas de mi Club' : 'Todas de la Liga'; ?>
                        </label>
                    </div>
                </div>

            </div>
        </form>

        <!-- KPI STATS CARDS -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs">
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block mb-1">Total Evaluadas</span>
                <span class="text-3xl font-black text-slate-800 tabular-nums"><?php echo $total_nadadoras; ?></span>
                <span class="text-[11px] text-slate-400 font-medium block mt-1">Nadadoras analizadas</span>
            </div>
            <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs">
                <span class="text-[10px] font-black uppercase tracking-wider text-emerald-600 block mb-1">100% Convalidadas</span>
                <span class="text-3xl font-black text-emerald-600 tabular-nums"><?php echo $total_convalidadas_full; ?></span>
                <span class="text-[11px] text-emerald-700/80 font-medium block mt-1">Exentas de nadar figuras</span>
            </div>
            <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs">
                <span class="text-[10px] font-black uppercase tracking-wider text-amber-600 block mb-1">Parciales</span>
                <span class="text-3xl font-black text-amber-600 tabular-nums"><?php echo $total_parciales; ?></span>
                <span class="text-[11px] text-amber-700/80 font-medium block mt-1">Convalidan 1 o 2 figuras</span>
            </div>
            <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs">
                <span class="text-[10px] font-black uppercase tracking-wider text-rose-600 block mb-1">Sin Convalidar</span>
                <span class="text-3xl font-black text-rose-600 tabular-nums"><?php echo $total_pendientes; ?></span>
                <span class="text-[11px] text-rose-700/80 font-medium block mt-1">Deben nadar las 3 figuras</span>
            </div>
        </div>

        <!-- FILTROS CLIENTE: PESTAÑAS POR CATEGORÍA + BUSCADOR -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar">
                <button type="button" onclick="filtrarCategoria('todas')" id="tab-todas" class="tab-btn px-4 py-2 rounded-xl text-xs font-black transition-all bg-slate-900 text-white shadow-xs">
                    Todas (<?php echo $total_nadadoras; ?>)
                </button>
                <button type="button" onclick="filtrarCategoria('Alevin I')" id="tab-Alevin I" class="tab-btn px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-all">
                    Alevín I (<?php echo count($calculo_categorias['Alevin I']['nadadoras']); ?>)
                </button>
                <button type="button" onclick="filtrarCategoria('Alevin II')" id="tab-Alevin II" class="tab-btn px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-all">
                    Alevín II (<?php echo count($calculo_categorias['Alevin II']['nadadoras']); ?>)
                </button>
                <button type="button" onclick="filtrarCategoria('Infantil')" id="tab-Infantil" class="tab-btn px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-all">
                    Infantil (<?php echo count($calculo_categorias['Infantil']['nadadoras']); ?>)
                </button>
            </div>

            <!-- Buscador en tiempo real -->
            <div class="relative w-full sm:w-64">
                <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" id="buscador-nadadoras" onkeyup="filtrarNadadoras()" placeholder="Buscar nadadora o club..." class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-3.5 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
            </div>
        </div>

        <!-- TABLAS POR CATEGORÍA -->
        <?php foreach ($calculo_categorias as $cat_key => $data_cat): 
            $info_cat = $data_cat['info'];
            $nads_cat = $data_cat['nadadoras'];
            $codigos_fig = array_keys($info_cat['figuras']);
        ?>
            <div class="cat-section bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden" data-category="<?php echo $cat_key; ?>">
                
                <!-- Encabezado de Categoría -->
                <div class="p-5 bg-slate-50/70 border-b border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-3">
                            <span class="text-lg font-black text-slate-800"><?php echo $info_cat['nombre']; ?></span>
                            <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider border <?php echo $info_cat['badge']; ?>">
                                Nota mínima: <?php echo number_format($info_cat['corte'], 2); ?>
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 font-medium mt-0.5"><?php echo $info_cat['subtitulo']; ?></p>
                    </div>

                    <!-- Resumen rápido de figuras del pase -->
                    <div class="text-[11px] font-semibold text-slate-500 flex flex-wrap gap-2">
                        <span class="text-slate-400 font-bold">Figuras:</span>
                        <?php foreach ($info_cat['figuras'] as $num => $f): ?>
                            <span class="px-2 py-0.5 bg-white border border-slate-200 rounded-md font-mono text-slate-700 flex items-center gap-1.5" title="<?php echo htmlspecialchars($f['nombre']); ?>">
                                <span class="px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 font-bold text-[9px] border border-slate-200">Ej. <?php echo $f['ejercicio']; ?></span>
                                <strong><?php echo $num; ?></strong> <span class="text-slate-400 text-[10px]">(GD <?php echo $f['gd']; ?>)</span>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Tabla de Resultados -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse tabla-convalidaciones">
                        <thead>
                            <tr class="border-b border-slate-200 text-[10px] font-black uppercase tracking-wider text-slate-400 bg-slate-50/30">
                                <th class="py-3 px-4">Deportista</th>
                                <th class="py-3 px-3">Club</th>
                                <?php foreach ($info_cat['figuras'] as $num => $f): ?>
                                    <th class="py-3 px-3 text-center">
                                        <span class="inline-block px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 text-[9px] font-black tracking-wide border border-slate-200 mb-1">
                                            EJ. <?php echo $f['ejercicio']; ?>
                                        </span>
                                        <span class="block text-slate-800 font-black"><?php echo $num; ?></span>
                                        <span class="text-[8px] font-normal text-slate-400 truncate max-w-[125px] block mx-auto" title="<?php echo htmlspecialchars($f['nombre']); ?>"><?php echo htmlspecialchars($f['nombre']); ?></span>
                                    </th>
                                <?php endforeach; ?>
                                <th class="py-3 px-4 text-center">Dictamen</th>
                                <th class="py-3 px-4 text-left">Obligación en el Pase</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                            <?php if (empty($nads_cat)): ?>
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-slate-400 italic">No hay nadadoras en esta categoría para los filtros seleccionados.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($nads_cat as $nad): ?>
                                    <tr class="hover:bg-slate-50/70 transition-colors fila-nadadora" data-texto="<?php echo strtolower(htmlspecialchars($nad['apellidos'] . ' ' . $nad['nombre'] . ' ' . $nad['club'])); ?>">
                                        
                                        <!-- Nombre -->
                                        <td class="py-3.5 px-4 font-bold text-slate-900 whitespace-nowrap">
                                            <?php echo htmlspecialchars($nad['apellidos'] . ', ' . $nad['nombre']); ?>
                                            <span class="text-[10px] text-slate-400 font-normal ml-1">(<?php echo $nad['año']; ?>)</span>
                                        </td>

                                        <!-- Club -->
                                        <td class="py-3.5 px-3 whitespace-nowrap">
                                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[10px] font-black tracking-wider uppercase border border-slate-200/60">
                                                <?php echo htmlspecialchars($nad['club']); ?>
                                            </span>
                                        </td>

                                        <!-- Las 3 Figuras -->
                                        <?php foreach ($codigos_fig as $cod_fig): 
                                            $eval = $nad['figuras'][$cod_fig];
                                            $nota = $eval['nota'];
                                            $apto = $eval['apto'];
                                        ?>
                                            <td class="py-3.5 px-3 text-center whitespace-nowrap">
                                                <?php if ($nota !== null): ?>
                                                    <span class="tabular-nums font-mono font-bold <?php echo $apto ? 'text-emerald-700' : 'text-rose-600'; ?>">
                                                        <?php echo number_format($nota, 4); ?>
                                                    </span>
                                                    <span class="ml-1 inline-flex items-center text-[11px] font-bold <?php echo $apto ? 'text-emerald-600' : 'text-rose-600'; ?>">
                                                        <?php echo $apto ? '✅ Sí' : '❌ No'; ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-slate-300 font-mono text-xs">—</span>
                                                    <span class="ml-1 text-[11px] font-bold text-rose-500">❌ No</span>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>

                                        <!-- Dictamen -->
                                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border shadow-2xs <?php echo $nad['estado_badge']; ?>">
                                                <?php echo $nad['estado_texto']; ?>
                                            </span>
                                        </td>

                                        <!-- Figuras que debe nadar -->
                                        <td class="py-3.5 px-4 text-left whitespace-nowrap">
                                            <?php if ($nad['convalidadas'] === 3): ?>
                                                <span class="inline-flex items-center gap-1.5 text-emerald-700 font-bold text-[11px]">
                                                    <i class="fas fa-circle-check text-xs"></i> Exenta de figuras
                                                </span>
                                            <?php else: ?>
                                                <span class="text-rose-700 font-bold text-[11px]">
                                                    Debe nadar: 
                                                    <span class="font-mono text-slate-800 bg-rose-50 px-2 py-0.5 rounded border border-rose-200">
                                                        <?php echo implode(', ', $nad['pendientes']); ?>
                                                    </span>
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        <?php endforeach; ?>

    </div>
</main>

<!-- Hidden Pre-formatted Text for WhatsApp & Telegram Copy -->
<textarea id="raw-report-text" class="hidden">
📋 *CONVALIDACIONES FIGURAS - <?php echo mb_strtoupper($nombre_pase); ?>*
_Sistema SINCRM Oficial - FNRM_
Evaluación oficial figura a figura según Normativa de Niveles RFEN.

<?php foreach ($calculo_categorias as $k => $c): ?>
*<?php echo mb_strtoupper($c['info']['nombre']); ?>* (Nota mínima: <?php echo number_format($c['info']['corte'], 2); ?>)
<?php foreach ($c['info']['figuras'] as $fnum => $finfo): ?>
• *Ej. <?php echo $finfo['ejercicio']; ?>* (<?php echo $fnum; ?>): <?php echo $finfo['nombre']; ?> [GD <?php echo $finfo['gd']; ?>]
<?php endforeach; ?>

<?php foreach ($c['nadadoras'] as $n): ?>
🏊‍♀️ *<?php echo $n['apellidos'] . ', ' . $n['nombre']; ?>* (<?php echo $n['club']; ?>)
<?php foreach ($n['figuras'] as $fcode => $fres): ?>
  - Ej. <?php echo $fres['ejercicio']; ?> (<?php echo $fcode; ?>): <?php echo $fres['nota'] !== null ? number_format($fres['nota'], 2) : '--'; ?> <?php echo $fres['apto'] ? '✅ Sí' : '❌ No'; ?>
<?php endforeach; ?>
<?php if ($n['convalidadas'] == 3): ?>
  👉 *CONVALIDA TODO* (3/3)
<?php elseif ($n['convalidadas'] > 0): ?>
  👉 *Convalida <?php echo $n['convalidadas']; ?>/3* (Debe nadar: <?php echo implode(', ', $n['pendientes']); ?>)
<?php else: ?>
  👉 *0 de 3* (Debe nadar las 3 figuras)
<?php endif; ?>

<?php endforeach; ?>
<?php endforeach; ?>
</textarea>

<script>
// Filtrado por categoría
function filtrarCategoria(cat) {
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('bg-slate-900', 'text-white', 'shadow-xs');
        btn.classList.add('text-slate-600', 'hover:bg-slate-100');
    });
    
    const activeTab = document.getElementById('tab-' + cat);
    if(activeTab) {
        activeTab.classList.remove('text-slate-600', 'hover:bg-slate-100');
        activeTab.classList.add('bg-slate-900', 'text-white', 'shadow-xs');
    }

    document.querySelectorAll('.cat-section').forEach(sec => {
        if (cat === 'todas' || sec.getAttribute('data-category') === cat) {
            sec.style.display = 'block';
        } else {
            sec.style.display = 'none';
        }
    });
}

// Buscador en tiempo real
function filtrarNadadoras() {
    const q = document.getElementById('buscador-nadadoras').value.toLowerCase().trim();
    document.querySelectorAll('.fila-nadadora').forEach(fila => {
        const texto = fila.getAttribute('data-texto') || '';
        fila.style.display = (q === '' || texto.includes(q)) ? '' : 'none';
    });
}

// Copiar informe al portapapeles
function copiarTexto() {
    const txt = document.getElementById('raw-report-text').value.trim();
    navigator.clipboard.writeText(txt).then(() => {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: '¡Copiado al portapapeles!',
                text: 'El informe formateado está en tu portapapeles listo para pegar.',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        } else {
            alert('¡Informe copiado al portapapeles!');
        }
    }).catch(err => {
        alert('Error al copiar al portapapeles: ' + err);
    });
}
function copiarWhatsApp() { copiarTexto(); }

// Enviar informe a Telegram
function enviarTelegram() {
    const txt = document.getElementById('raw-report-text').value.trim();
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Enviar por Telegram?',
            text: 'Se enviará el desglose completo de convalidaciones al bot de Telegram.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0284c7',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="fab fa-telegram"></i> Sí, enviar ahora',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                ejecutarEnvioTelegram(txt);
            }
        });
    } else {
        if(confirm('¿Enviar informe a Telegram?')) {
            ejecutarEnvioTelegram(txt);
        }
    }
}

function ejecutarEnvioTelegram(txt) {
    fetch('convalidaciones.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ action: 'send_telegram', telegram_text: txt })
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'ok') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'success', title: '¡Enviado!', text: data.message });
            } else {
                alert(data.message);
            }
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message });
            } else {
                alert('Error: ' + data.message);
            }
        }
    })
    .catch(err => {
        alert('Error en la petición: ' + err);
    });
}
</script>

<?php include('includes/footer.php'); ?>
