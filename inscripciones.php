<?php
include('security.php');
include('includes/header.php');
?>
<link rel="stylesheet" href="vendor/tom-select/tom-select.default.min.css">
<style>
/* Estilos Flotantes TomSelect (Opción 2) */
.ts-wrapper.tom-select-nadadora {
    width: 100% !important;
}
.ts-wrapper.tom-select-nadadora .ts-control {
    background-color: #ffffff !important;
    border: 1.5px solid #bfdbfe !important;
    border-radius: 0.75rem !important;
    padding: 0.65rem 1rem !important;
    font-size: 0.8125rem !important;
    font-weight: 700 !important;
    color: #1e293b !important;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
    min-height: 44px !important;
    display: flex !important;
    align-items: center !important;
    cursor: text !important;
}
.ts-wrapper.tom-select-nadadora.focus .ts-control {
    border-color: #2563eb !important;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15) !important;
}
.ts-wrapper.tom-select-nadadora .ts-control input {
    font-size: 0.8125rem !important;
    font-weight: 700 !important;
    color: #1e293b !important;
}
.ts-wrapper.tom-select-nadadora .ts-control input::placeholder {
    color: #94a3b8 !important;
    font-weight: 500 !important;
}
.ts-wrapper.tom-select-nadadora.ts-typing .item {
    display: none !important;
}
.ts-dropdown {
    background-color: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 1rem !important;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
    padding: 0.375rem !important;
    font-family: inherit !important;
    font-size: 0.8125rem !important;
    max-height: 240px !important;
    overflow-y: auto !important;
    z-index: 999999 !important;
}
.ts-dropdown .option {
    padding: 0.625rem 0.875rem !important;
    border-radius: 0.5rem !important;
    color: #1e293b !important;
    font-weight: 600 !important;
    cursor: pointer !important;
}
.ts-dropdown .option:hover,
.ts-dropdown .option.active {
    background-color: #eff6ff !important;
    color: #1d4ed8 !important;
}
.ts-dropdown .option[data-selectable="false"],
.ts-dropdown .option.disabled {
    opacity: 0.45 !important;
    background-color: #f8fafc !important;
    color: #94a3b8 !important;
    cursor: not-allowed !important;
}
</style>
<?php
include('includes/navbar.php');

// Sincronizar ID de competición (prioridad POST/GET, luego sesión)
if (isset($_POST['id_competicion']) && !empty($_POST['id_competicion'])) {
    $_SESSION['id_competicion_usuario'] = intval($_POST['id_competicion']);
    $_SESSION['id_competicion_activa'] = intval($_POST['id_competicion']);
} elseif (isset($_GET['id_competicion']) && !empty($_GET['id_competicion'])) {
    $_SESSION['id_competicion_usuario'] = intval($_GET['id_competicion']);
    $_SESSION['id_competicion_activa'] = intval($_GET['id_competicion']);
}

$id_comp = (isset($_SESSION['id_competicion_usuario']) && !empty($_SESSION['id_competicion_usuario'])) ? (int)$_SESSION['id_competicion_usuario'] : (int)($_SESSION['id_competicion_activa'] ?? 0);

if ($id_comp == 0) {
    echo "<div class='p-10 text-center font-lexend bg-white rounded-[2.5rem] shadow-sm border border-slate-200 mt-10 max-w-2xl mx-auto'>
            <div class='w-20 h-20 bg-blue-50 text-blue-500 rounded-[2rem] flex items-center justify-center text-3xl mx-auto mb-6 shadow-sm border border-blue-100'>
                <i class='fas fa-clipboard-list'></i>
            </div>
            <h2 class='text-2xl font-black text-slate-800 tracking-tighter'>Ninguna Competición Seleccionada</h2>
            <p class='text-slate-500 mt-2 font-medium'>Para gestionar las inscripciones, selecciona una competición desde el panel principal.</p>
            <a href='index.php' class='mt-8 inline-flex items-center gap-3 px-8 py-4 bg-slate-800 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg hover:bg-black transition-all'>
                <i class='fas fa-home'></i> Ir al Dashboard
            </a>
          </div>";
    include('includes/footer.php');
    exit();
}

// 1. Obtener datos de la competición
$res_c = mysqli_query($connection, "SELECT * FROM competiciones WHERE id = '$id_comp'");
$comp_data = mysqli_fetch_assoc($res_c);
if (!$comp_data) {
    die("Competición no válida.");
}
$_SESSION['nombre_competicion_usuario'] = $comp_data['nombre'];
$_SESSION['competicion_figuras_usuario'] = $comp_data['figuras'];
$fecha_evento = new DateTime($comp_data['fecha']);

// 2. Comprobar disciplinas presentes (Figuras vs Rutinas)
$res_fig_count = mysqli_query($connection, "SELECT COUNT(*) as c FROM fases WHERE id_competicion = '$id_comp' AND id_figura > 0");
$num_figuras_fases = mysqli_fetch_assoc($res_fig_count)['c'] ?? 0;

$res_rut_count = mysqli_query($connection, "SELECT COUNT(*) as c FROM fases WHERE id_competicion = '$id_comp' AND id_modalidad > 0");
$num_rutinas_fases = mysqli_fetch_assoc($res_rut_count)['c'] ?? 0;

$has_figuras = ($num_figuras_fases > 0 || in_array($comp_data['figuras'], ['si', 'mixta']));
$has_rutinas = ($num_rutinas_fases > 0 || in_array($comp_data['figuras'], ['no', 'mixta']));
$is_mixta = ($has_figuras && $has_rutinas);

// Pestaña inicial
if ($is_mixta) {
    $current_tab = (isset($_GET['tab']) && $_GET['tab'] === 'rutinas') ? 'rutinas' : 'figuras';
} elseif ($has_rutinas) {
    $current_tab = 'rutinas';
} else {
    $current_tab = 'figuras';
}

// 3. Contexto de club
$condicion_club = '';
$condicion_club_rut = '';
$nombre_contexto = "General";
if(isset($_SESSION['club']) && $_SESSION['club'] > 0){
    $condicion_club = " AND n.club = ".$_SESSION['club'];
    $condicion_club_rut = " AND rutinas.id_club = ".$_SESSION['club'];
    $nombre_contexto = $_SESSION['nombre_club'] ?? "Mi Club";
}

// 4. Plazos Técnicos
$dias_cierre = $comp_data['dias_fin_inscripcion'] ?? 7;
$fecha_cierre = clone $fecha_evento; $fecha_cierre->modify("-$dias_cierre days");
$fecha_cierre_visible = clone $fecha_cierre; $fecha_cierre_visible->modify("-1 day");

$d_cc = $comp_data['dias_coach_card'] ?? 7;
$f_cc = clone $fecha_evento; $f_cc->modify("-$d_cc days");
$f_cc_visible = clone $f_cc; $f_cc_visible->modify("-1 day");

$d_musica = $comp_data['dias_musica'] ?? 7;
$f_musica = clone $fecha_evento; $f_musica->modify("-$d_musica days");
$f_musica_visible = clone $f_musica; $f_musica_visible->modify("-1 day");

$hoy = new DateTime();
$inscripcion_abierta = ($hoy <= $fecha_cierre_visible || $_SESSION['id_rol'] == 1);
$musica_abierta = ($hoy <= $f_musica_visible || $_SESSION['id_rol'] == 1);
$cc_abierta = ($hoy <= $f_cc_visible || $_SESSION['id_rol'] == 1);

$enable_musica = ($musica_abierta) ? '' : 'disabled';
$enable_coach_card = ($cc_abierta) ? '' : 'disabled';
$enable_inscripcion = ($inscripcion_abierta) ? '' : 'disabled';

// Extraer año de referencia de la temporada según normativa RFEN/World Aquatics
// (año en que finaliza la temporada, ej: "2026-2027" o "2026/2027" -> 2027)
$temporada_raw = trim($comp_data['temporada'] ?? '');
$temporada_año = 0;
if(preg_match('/[\/\-](\d{4})/', $temporada_raw, $matches)){
    $temporada_año = intval($matches[1]);
} elseif(preg_match('/^\d{4}$/', $temporada_raw)) {
    $temporada_año = intval($temporada_raw);
} else {
    // Si no está definida la temporada, calcular por fecha de competición (temporada sep-ago)
    $mes_evento = intval($fecha_evento->format('n'));
    $año_evento = intval($fecha_evento->format('Y'));
    $temporada_año = ($mes_evento >= 9) ? ($año_evento + 1) : $año_evento;
}

// 5. KPIs Combinados para la Competición
$cond_club_u1 = (isset($_SESSION['club']) && $_SESSION['club'] > 0) ? " AND n.club = ".$_SESSION['club'] : "";
$cond_club_u2 = (isset($_SESSION['club']) && $_SESSION['club'] > 0) ? " AND r.id_club = ".$_SESSION['club'] : "";

$q_total_atletas = "SELECT COUNT(DISTINCT id_nadadora) as total FROM (
    SELECT i.id_nadadora FROM inscripciones_figuras i 
    JOIN fases f ON i.id_fase = f.id 
    JOIN nadadoras n ON i.id_nadadora = n.id 
    WHERE f.id_competicion = '$id_comp' $cond_club_u1
    UNION
    SELECT rp.id_nadadora FROM rutinas_participantes rp 
    JOIN rutinas r ON rp.id_rutina = r.id 
    JOIN nadadoras n ON rp.id_nadadora = n.id 
    WHERE r.id_competicion = '$id_comp' $cond_club_u2
) as u";
$res_tot_atl = mysqli_query($connection, $q_total_atletas);
$total_atletas_global = mysqli_fetch_assoc($res_tot_atl)['total'] ?? 0;

$q_tot_ruts = "SELECT COUNT(*) as total,
               SUM(CASE WHEN music_name IS NOT NULL AND music_name != '' THEN 1 ELSE 0 END) as con_musica,
               SUM(CASE WHEN dd_total > 0 THEN 1 ELSE 0 END) as con_cc,
               SUM(CASE WHEN fases.elementos_coach_card > 0 THEN 1 ELSE 0 END) as req_cc
               FROM rutinas, fases 
               WHERE rutinas.id_fase = fases.id AND fases.id_competicion = $id_comp $condicion_club_rut";
$res_tot_ruts = mysqli_query($connection, $q_tot_ruts);
$stats_rut = mysqli_fetch_assoc($res_tot_ruts);
$total_rutinas_global = $stats_rut['total'] ?? 0;
$total_musicas_subidas = $stats_rut['con_musica'] ?? 0;
$total_cc_completadas = $stats_rut['con_cc'] ?? 0;
$total_cc_requeridas = $stats_rut['req_cc'] ?? 0;

$q_clubs_fig = "SELECT COUNT(DISTINCT n.club) as total FROM inscripciones_figuras i JOIN nadadoras n ON i.id_nadadora = n.id JOIN fases f ON i.id_fase = f.id WHERE f.id_competicion = '$id_comp' $condicion_club";
$total_clubs = mysqli_fetch_assoc(mysqli_query($connection, $q_clubs_fig))['total'] ?? 0;
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-surface">
    
    <?php include('includes/topbar.php'); ?>

    <div class="px-3 py-4 sm:p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend text-primary">
        
        <!-- Header Unificado -->
        <div class="mb-6 sm:mb-8 flex flex-col lg:flex-row lg:items-end justify-between gap-4 sm:gap-6">
            <div>
                <h1 class="text-2xl sm:text-4xl font-black text-slate-800 tracking-tight mb-1 sm:mb-2 flex items-center gap-2.5 sm:gap-3">
                    <span class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-xs border border-slate-200 shrink-0">
                        <i class="fas fa-clipboard-list text-base sm:text-lg"></i>
                    </span>
                    <span>Inscripciones <span class="text-slate-300 font-light mx-1 sm:mx-2">/</span> <?php echo htmlspecialchars($comp_data['nombre']); ?></span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 font-medium">
                    Gestionando participación para: <span class="text-blue-600 font-bold"><?php echo htmlspecialchars($nombre_contexto); ?></span>
                    &bull; Sede: <span class="text-slate-700 font-bold"><?php echo htmlspecialchars($comp_data['lugar'] ?? 'Por determinar'); ?></span>
                </p>
            </div>

            <!-- Plazos y Enlace a PDF Oficial -->
            <div class="flex flex-wrap items-center gap-2 sm:gap-3 w-full lg:w-auto">
                <div class="flex items-center gap-2">
                    <div class="flex flex-col items-center px-3 py-1.5 sm:px-4 sm:py-2 bg-white rounded-xl sm:rounded-2xl border border-slate-200/80 shadow-xs tabular-nums">
                        <span class="text-[8px] sm:text-[9px] font-black uppercase text-slate-400 leading-tight">Inscripción</span>
                        <span class="text-[11px] sm:text-xs font-black <?php echo !$inscripcion_abierta ? 'text-red-500' : 'text-slate-700'; ?>"><?php echo $fecha_cierre_visible->format('d/m/Y'); ?></span>
                    </div>
                    <?php if($has_rutinas): ?>
                    <div class="flex flex-col items-center px-3 py-1.5 sm:px-4 sm:py-2 bg-white rounded-xl sm:rounded-2xl border border-slate-200/80 shadow-xs tabular-nums">
                        <span class="text-[8px] sm:text-[9px] font-black uppercase text-slate-400 leading-tight">Música</span>
                        <span class="text-[11px] sm:text-xs font-black <?php echo !$musica_abierta ? 'text-red-500' : 'text-slate-700'; ?>"><?php echo $f_musica_visible->format('d/m/Y'); ?></span>
                    </div>
                    <div class="flex flex-col items-center px-3 py-1.5 sm:px-4 sm:py-2 bg-white rounded-xl sm:rounded-2xl border border-slate-200/80 shadow-xs tabular-nums">
                        <span class="text-[8px] sm:text-[9px] font-black uppercase text-slate-400 leading-tight">Coach Card</span>
                        <span class="text-[11px] sm:text-xs font-black <?php echo !$cc_abierta ? 'text-red-500' : 'text-slate-700'; ?>"><?php echo $f_cc_visible->format('d/m/Y'); ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <?php 
                $txt_btn_insc = (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 5) ? 'MIS INSCRIPCIONES' : 'INSCRIPCIONES';
                $title_btn_insc = (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 5) ? 'Descargar PDF de inscripciones de mi club (borrador privado)' : 'Ver listado de inscripciones en PDF';
                ?>
                <a href="./informes/informe_figuras.php?id_competicion=<?php echo $id_comp; ?>&titulo=Inscripciones&v=<?php echo time(); ?>" target="_blank" 
                   title="<?php echo $title_btn_insc; ?>"
                   class="px-4 py-2.5 sm:px-5 sm:py-3 bg-emerald-600 text-white font-bold text-[11px] sm:text-xs uppercase tracking-wider rounded-xl sm:rounded-2xl hover:bg-emerald-700 active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-md no-underline">
                    <i class="fas fa-file-pdf text-xs"></i> <span><?php echo $txt_btn_insc; ?></span>
                </a>
            </div>
        </div>

        <!-- Alertas de Sesión -->
        <?php include('includes/alertas_v4.php'); ?>

        <!-- KPIs Unificados -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-6 mb-6 sm:mb-8">
            <div class="bg-white rounded-xl sm:rounded-[2rem] p-4 sm:p-6 shadow-sm border border-slate-200 border-l-[6px] border-l-blue-600 flex items-center gap-3 sm:gap-4">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 shadow-inner shrink-0">
                    <i class="fas fa-person-swimming text-lg sm:text-xl"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="text-xl sm:text-2xl font-black text-slate-800 leading-none mb-1 tabular-nums"><?php echo $total_atletas_global; ?></h3>
                    <p class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 tracking-wider truncate">Atletas Inscritas</p>
                </div>
            </div>

            <?php if($has_rutinas): ?>
            <div class="bg-white rounded-xl sm:rounded-[2rem] p-4 sm:p-6 shadow-sm border border-slate-200 border-l-[6px] border-l-purple-500 flex items-center gap-3 sm:gap-4">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600 shadow-inner shrink-0">
                    <i class="fa-solid fa-water text-lg sm:text-xl"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="text-xl sm:text-2xl font-black text-slate-800 leading-none mb-1 tabular-nums"><?php echo $total_rutinas_global; ?></h3>
                    <p class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 tracking-wider truncate">Rutinas Creadas</p>
                </div>
            </div>

            <div class="bg-white rounded-xl sm:rounded-[2rem] p-4 sm:p-6 shadow-sm border border-slate-200 border-l-[6px] border-l-amber-500 flex items-center gap-3 sm:gap-4">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600 shadow-inner shrink-0">
                    <i class="fas fa-puzzle-piece text-lg sm:text-xl"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="text-xl sm:text-2xl font-black text-slate-800 leading-none mb-1 tabular-nums"><?php echo $total_cc_completadas; ?> <span class="text-xs text-slate-400 font-bold">/ <?php echo $total_cc_requeridas; ?></span></h3>
                    <p class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 tracking-wider truncate">Coach Cards</p>
                </div>
            </div>

            <div class="bg-white rounded-xl sm:rounded-[2rem] p-4 sm:p-6 shadow-sm border border-slate-200 border-l-[6px] border-l-emerald-500 flex items-center gap-3 sm:gap-4">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 shadow-inner shrink-0">
                    <i class="fas fa-music text-lg sm:text-xl"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="text-xl sm:text-2xl font-black text-slate-800 leading-none mb-1 tabular-nums"><?php echo $total_musicas_subidas; ?> <span class="text-xs text-slate-400 font-bold">/ <?php echo $total_rutinas_global; ?></span></h3>
                    <p class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 tracking-wider truncate">Músicas Subidas</p>
                </div>
            </div>
            <?php else: ?>
            <div class="bg-white rounded-xl sm:rounded-[2rem] p-4 sm:p-6 shadow-sm border border-slate-200 border-l-[6px] border-l-emerald-500 flex items-center gap-3 sm:gap-4">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 shadow-inner shrink-0">
                    <i class="fas fa-shield-halved text-lg sm:text-xl"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="text-xl sm:text-2xl font-black text-slate-800 leading-none mb-1 tabular-nums"><?php echo $total_clubs; ?></h3>
                    <p class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 tracking-wider truncate">Clubes Participantes</p>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <?php if($is_mixta): ?>
        <!-- Segmented Tab Selector (Estilo Emil Kowalski / Zero-Gray) -->
        <div class="mb-6 sm:mb-8">
            <div class="inline-flex p-1.5 bg-slate-200/80 rounded-2xl border border-slate-300/70 shadow-inner">
                <button type="button" id="btnTabInscFiguras" onclick="setInscTab('figuras')" 
                        class="px-5 sm:px-7 py-2.5 rounded-xl sm:rounded-2xl font-black text-xs sm:text-sm uppercase tracking-wider transition-all duration-300 flex items-center gap-2.5 <?php echo $current_tab === 'figuras' ? 'bg-white text-emerald-600 shadow-md ring-1 ring-black/5' : 'text-slate-600 hover:text-slate-900'; ?>">
                    <i class="fa-solid fa-shapes text-sm"></i>
                    <span>Figuras <span class="hidden sm:inline font-normal text-[11px] opacity-80">(Alevín / Infantil)</span></span>
                </button>
                <button type="button" id="btnTabInscRutinas" onclick="setInscTab('rutinas')" 
                        class="px-5 sm:px-7 py-2.5 rounded-xl sm:rounded-2xl font-black text-xs sm:text-sm uppercase tracking-wider transition-all duration-300 flex items-center gap-2.5 <?php echo $current_tab === 'rutinas' ? 'bg-white text-blue-600 shadow-md ring-1 ring-black/5' : 'text-slate-600 hover:text-slate-900'; ?>">
                    <i class="fa-solid fa-water text-sm"></i>
                    <span>Rutinas Técnicas <span class="hidden sm:inline font-normal text-[11px] opacity-80">(Junior / Absoluto)</span></span>
                </button>
            </div>
        </div>
        <?php endif; ?>

        <!-- ========================================================================= -->
        <!-- PESTAÑA: FIGURAS                                                          -->
        <!-- ========================================================================= -->
        <?php if($has_figuras): ?>
        <div id="tabContentInscFiguras" class="<?php echo $current_tab === 'figuras' ? '' : 'hidden'; ?> space-y-6 sm:space-y-8 pb-36">
            
            <?php
            $q_cat = "SELECT DISTINCT f.id_categoria, c.nombre, c.edad_minima, c.edad_maxima 
                      FROM fases f, categorias c 
                      WHERE f.id_categoria = c.id AND f.id_competicion = '$id_comp' AND f.id_figura > 0 
                      ORDER BY c.edad_minima ASC";
            $res_cats = mysqli_query($connection, $q_cat);
            $cat_idx = 0;
            $cat_colors = ['emerald', 'blue', 'purple', 'amber', 'rose'];

            while ($row_cat = mysqli_fetch_assoc($res_cats)):
                $id_cat = $row_cat['id_categoria'];
                $color_cat = $cat_colors[$cat_idx % count($cat_colors)];
                $cat_idx++;

                // Fases de figuras de esta categoría
                $q_fases_cat = "SELECT fs.id, fs.id_figura, fig.nombre as fig_nom, fig.numero as fig_num
                                FROM fases fs 
                                LEFT JOIN figuras fig ON fs.id_figura = fig.id
                                WHERE fs.id_competicion = '$id_comp' AND fs.id_categoria = '$id_cat' AND fs.id_figura > 0
                                ORDER BY fs.orden ASC";
                $res_fases_cat = mysqli_query($connection, $q_fases_cat);
                $nombres_figuras = [];
                $id_fase_ref = 0;
                $num_figuras_cat = 0;
                while($f_row = mysqli_fetch_assoc($res_fases_cat)) {
                    if($id_fase_ref == 0) $id_fase_ref = $f_row['id'];
                    if(!empty($f_row['fig_nom'])) {
                        $nombres_figuras[] = "<span class='text-slate-700 font-bold'>".$f_row['fig_num']."</span> <span class='text-slate-500'>".$f_row['fig_nom']."</span>";
                        $num_figuras_cat++;
                    }
                }
                $str_figuras = implode("<span class='mx-1.5 text-slate-300'>|</span>", $nombres_figuras);

                // Nadadoras inscritas en la categoría
                $q_ins = "SELECT i.*, n.nombre as n_nom, n.apellidos as n_ape, n.año_nacimiento as n_año, cl.nombre_corto as c_nom 
                          FROM inscripciones_figuras i 
                          JOIN nadadoras n ON i.id_nadadora = n.id 
                          JOIN clubes cl ON n.club = cl.id 
                          WHERE i.id_fase = '$id_fase_ref' $condicion_club 
                          ORDER BY i.orden ASC, cl.nombre_corto ASC, n.apellidos ASC";
                $res_ins = mysqli_query($connection, $q_ins);
                $inscritos = [];
                if($res_ins) {
                    while($row = mysqli_fetch_assoc($res_ins)) $inscritos[] = $row;
                }
                $total_cat = count($inscritos);
            ?>
            <div id="cat-fig-<?php echo $id_cat; ?>" class="bg-white rounded-2xl sm:rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden border-t-[8px] border-t-<?php echo $color_cat; ?>-500">
                <div class="px-4 py-4 sm:px-8 sm:py-6 bg-slate-50/50 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2 sm:gap-4 mb-1 sm:mb-2">
                            <h2 class="text-lg sm:text-xl font-black text-slate-800 tracking-tight italic">
                                <?php echo htmlspecialchars($row_cat['nombre']); ?> 
                                <span class="text-xs font-bold text-slate-400 not-italic">(<?php echo $row_cat['edad_minima']; ?>-<?php echo $row_cat['edad_maxima']; ?> años)</span>
                            </h2>
                            <?php if($inscripcion_abierta): ?>
                            <button onclick="toggleQuickInscribe(<?php echo $id_cat; ?>)" class="px-3 py-1.5 sm:px-4 sm:py-1.5 bg-blue-600 text-white text-[9px] sm:text-[10px] font-black rounded-xl uppercase tracking-widest shadow-md shadow-blue-500/20 hover:bg-blue-700 active:scale-[0.98] transition-all flex items-center gap-1.5">
                                <i class="fas fa-plus text-[8px]"></i> Inscribir deportista
                            </button>
                            <?php endif; ?>
                        </div>
                        <div class="text-[10px] uppercase tracking-wide flex flex-wrap items-center gap-1.5 leading-relaxed">
                            <?php echo $str_figuras; ?>
                        </div>
                    </div>
                    <span class="self-start sm:self-center px-3 py-1 sm:px-4 sm:py-1 bg-white text-slate-500 text-[10px] font-black rounded-lg border border-slate-200 shadow-xs uppercase tabular-nums shrink-0">
                        <?php echo $total_cat; ?> Participantes
                    </span>
                </div>

                <!-- Formulario Rápido de Inscripción de Nadadora -->
                <?php if($inscripcion_abierta): ?>
                <div id="quick-inscribe-<?php echo $id_cat; ?>" class="hidden px-4 py-4 sm:px-8 sm:py-6 bg-blue-50/40 border-b border-blue-100">
                    <form action="inscripciones_figuras_code.php" method="POST" onsubmit="return validateInscribeFig(this);" class="grid grid-cols-1 md:grid-cols-12 gap-3 sm:gap-4 items-end">
                        <input type="hidden" name="id_competicion" value="<?php echo $id_comp; ?>">
                        <input type="hidden" name="id_fase" value="<?php echo $id_fase_ref; ?>">
                        <input type="hidden" name="return_to" value="inscripciones.php?tab=figuras&open_cat=<?php echo $id_cat; ?>">
                        <input type="hidden" name="save_btn" value="1">
                        
                        <div class="md:col-span-10">
                            <label class="text-[9px] font-black uppercase text-slate-400 px-1 mb-1 block">Seleccionar nadadora de tu club apta para <?php echo htmlspecialchars($row_cat['nombre']); ?></label>
                            <?php 
                            $id_categoria_limite = $id_cat; 
                            $temporada_año = $temporada_año; 
                            ob_start(); include('includes/nadadoras_select_option.php');
                            $raw_select = ob_get_clean();
                            $clean_select = preg_replace('/<label.*?>.*?<\/label>/i', '', $raw_select);
                            $clean_select = preg_replace(
                                '/<select[^>]*>/i', 
                                '<select name="id_nadadora" id="select_nadadora_' . $id_cat . '" required class="tom-select-nadadora w-full" placeholder="Escribe para buscar nadadora por apellidos o nombre...">', 
                                $clean_select
                            );
                            $clean_select = preg_replace('/<option[^>]*value=[\'"]\s*[\'"][^>]*>.*?<\/option>/i', '<option value=""></option>', $clean_select);
                            echo $clean_select;
                            ?>
                        </div>

                        <div class="md:col-span-2">
                            <button type="submit" name="save_btn" class="w-full py-2.5 sm:py-3 bg-blue-600 text-white font-black uppercase text-xs tracking-wider rounded-xl shadow-md shadow-blue-500/20 active:scale-[0.98] hover:bg-blue-700 transition-all">
                                Registrar
                            </button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

                <!-- Listado de Nadadoras Inscritas -->
                <?php if(empty($inscritos)): ?>
                    <div class="p-8 text-center text-xs text-slate-400 font-bold italic">No hay nadadoras inscritas en esta categoría de figuras.</div>
                <?php else: ?>
                    <div class="overflow-x-auto no-scrollbar">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50 text-slate-400 text-[10px] font-black uppercase tracking-widest border-b border-slate-100">
                                    <th class="px-6 py-3.5 w-20 text-center">Orden</th>
                                    <th class="px-6 py-3.5">Deportista</th>
                                    <th class="px-4 py-3.5 text-center w-24">Año</th>
                                    <th class="px-4 py-3.5">Club</th>
                                    <th class="px-6 py-3.5 text-center w-28">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <?php foreach($inscritos as $row): ?>
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="px-6 py-3.5 text-center font-black text-slate-700 tabular-nums">
                                        <?php echo ($row['orden'] == 0 || $row['orden'] === null) ? '-' : $row['orden']; ?>
                                    </td>
                                    <td class="px-6 py-3.5 font-bold text-slate-800">
                                        <?php echo htmlspecialchars($row['n_ape'] . ', ' . $row['n_nom']); ?>
                                        <?php if($row['baja'] === 'si'): ?><span class="ml-2 px-2 py-0.5 bg-red-50 text-red-600 rounded text-[9px] font-black uppercase">Baja</span><?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3.5 text-center tabular-nums text-slate-500 font-bold"><?php echo $row['n_año']; ?></td>
                                    <td class="px-4 py-3.5 font-bold text-blue-600 uppercase text-xs"><?php echo htmlspecialchars($row['c_nom']); ?></td>
                                    <td class="px-6 py-3.5 text-center">
                                        <?php if($inscripcion_abierta || $_SESSION['id_rol'] == 1): ?>
                                        <button type="button" onclick="launchConfirmDeleteFig(<?php echo $row['id']; ?>, '<?php echo addslashes($row['n_nom'].' '.$row['n_ape']); ?>')" 
                                                class="w-8 h-8 rounded-xl bg-slate-50 text-slate-400 hover:bg-red-50 hover:text-red-500 active:scale-95 transition-all inline-flex items-center justify-center border border-slate-200/60 shadow-xs" title="Eliminar inscripción">
                                            <i class="fas fa-trash-can text-xs"></i>
                                        </button>
                                        <?php else: ?>
                                        <span class="text-[10px] text-slate-400 italic font-bold">Bloqueado</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <?php endwhile; ?>

        </div>
        <?php endif; ?>

        <!-- ========================================================================= -->
        <!-- PESTAÑA: RUTINAS                                                          -->
        <!-- ========================================================================= -->
        <?php if($has_rutinas): ?>
        <div id="tabContentInscRutinas" class="<?php echo $current_tab === 'rutinas' ? '' : 'hidden'; ?> space-y-6 sm:space-y-8">
            
            <!-- Barra de Acciones de Rutinas -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 bg-white p-4 sm:p-6 rounded-2xl sm:rounded-[2rem] border border-slate-200 shadow-sm">
                <div>
                    <h2 class="text-base sm:text-lg font-black text-slate-800 tracking-tight flex items-center gap-2">
                        <i class="fa-solid fa-water text-blue-600"></i> Rutinas Técnicas y Libres
                    </h2>
                    <p class="text-xs text-slate-500 font-medium">Registra rutinas, asigna deportistas, carga música y completa Coach Cards.</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <?php if($enable_inscripcion == ''): ?>
                    <button type="button" onclick="toggleAddRutinaPanel()" class="px-4 py-2.5 sm:px-6 sm:py-3 bg-blue-600 text-white font-black uppercase text-[11px] sm:text-xs tracking-wider rounded-xl sm:rounded-2xl shadow-md shadow-blue-500/20 active:scale-[0.98] hover:bg-blue-700 transition-all flex items-center gap-2">
                        <i class="fas fa-plus text-xs"></i> Inscribir Rutina
                    </button>
                    <?php endif; ?>
                    <form action="./download_music.php" method="POST" target="_blank" class="inline">
                        <input type="hidden" name="id_competicion" value="<?php echo $id_comp; ?>">
                        <button type="submit" class="px-3.5 py-2.5 sm:px-5 sm:py-3 bg-slate-100 text-slate-700 font-bold text-[11px] sm:text-xs uppercase tracking-wider rounded-xl sm:rounded-2xl hover:bg-slate-200 active:scale-[0.98] transition-all flex items-center gap-1.5 border border-slate-200">
                            <i class="fas fa-file-zipper text-purple-600"></i> Músicas
                        </button>
                    </form>
                    <a href="./informes/informe_coach_card.php?id_competicion=<?php echo $id_comp; ?>&titulo=Coach%20Card%20Composer&v=<?php echo time(); ?>" target="_blank" 
                       class="px-3.5 py-2.5 sm:px-5 sm:py-3 bg-slate-100 text-slate-700 font-bold text-[11px] sm:text-xs uppercase tracking-wider rounded-xl sm:rounded-2xl hover:bg-slate-200 active:scale-[0.98] transition-all flex items-center gap-1.5 border border-slate-200">
                        <i class="fas fa-puzzle-piece text-amber-500"></i> Coach Cards
                    </a>
                </div>
            </div>

            <!-- Panel Inscribir Nueva Rutina (Colapsable) -->
            <div id="addRutinaPanel" class="hidden animate-fade-in-down bg-white rounded-2xl sm:rounded-[2.5rem] p-4 sm:p-8 shadow-xl border border-blue-100">
                <h3 class="text-lg font-black text-slate-800 mb-4 sm:mb-6 flex items-center gap-2">
                    <i class="fas fa-plus-circle text-blue-600"></i> Nueva Rutina para la Competición
                </h3>
                <form action="rutinas_code.php" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <input type="hidden" name="id_competicion" value="<?php echo $id_comp; ?>">
                    
                    <div class="md:col-span-6 space-y-1">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Fase / Modalidad</label>
                        <?php 
                        $include_todas_fases = false;
                        ob_start();
                        include('includes/fases_competicion_select_option.php');
                        $raw_fase_select = ob_get_clean();
                        $clean_fase_select = preg_replace('/<label.*?>.*?<\/label>/i', '', $raw_fase_select);
                        $clean_fase_select = preg_replace('/<select[^>]*>/i', '<select name="id_fase" required class="w-full text-xs font-bold px-3 py-2.5 rounded-xl border border-blue-200 bg-white">', $clean_fase_select);
                        echo $clean_fase_select;
                        ?>
                    </div>

                    <?php if($_SESSION['id_rol'] == 1 || empty($_SESSION['club'])): ?>
                    <div class="md:col-span-6 space-y-1">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Club Participante</label>
                        <?php 
                        $label_sin_club = "--- Seleccionar Club ---";
                        ob_start();
                        include('includes/club_select_option.php');
                        $raw_club_select = ob_get_clean();
                        $clean_club_select = preg_replace('/<label.*?>.*?<\/label>/i', '', $raw_club_select);
                        $clean_club_select = preg_replace('/<select[^>]*>/i', '<select name="club" required class="w-full text-xs font-bold px-3 py-2.5 rounded-xl border border-blue-200 bg-white">', $clean_club_select);
                        $clean_club_select = str_replace("value='0'", "value=''", $clean_club_select);
                        $clean_club_select = str_replace('value="0"', 'value=""', $clean_club_select);
                        echo $clean_club_select;
                        ?>
                    </div>
                    <?php else: ?>
                        <input type="hidden" name="club" value="<?php echo $_SESSION['club']; ?>">
                        <input type="hidden" name="id_club" value="<?php echo $_SESSION['club']; ?>">
                    <?php endif; ?>
                    <input type="hidden" name="return_to" value="inscripciones.php?id_competicion=<?php echo $id_comp; ?>&tab=rutinas">

                    <div class="md:col-span-12 flex justify-end gap-3 pt-2">
                        <button type="button" onclick="toggleAddRutinaPanel()" class="px-5 py-2.5 bg-slate-100 text-slate-500 font-black uppercase text-xs rounded-xl hover:bg-slate-200 transition-all">Cancelar</button>
                        <button type="submit" name="save_btn" class="px-6 py-2.5 bg-blue-600 text-white font-black uppercase text-xs rounded-xl shadow-md hover:bg-blue-700 transition-all">Guardar Rutina</button>
                    </div>
                </form>
            </div>

            <!-- Listado de Rutinas Registradas -->
            <?php
            $query_ruts = "SELECT rutinas.id, rutinas.nombre, rutinas.musica, rutinas.orden, rutinas.music_name, rutinas.music_original_name,
                                  rutinas.music_duration, rutinas.tematica, rutinas.dd_total, rutinas.id_fase, rutinas.id_club,
                                  clubes.nombre_corto as nombre_club, clubes.logo, modalidades.nombre as nombre_modalidad, categorias.nombre as nombre_categoria,
                                  fases.elementos_coach_card, fases.id_modalidad, fases.id_categoria, fases.genero as fase_genero,
                                  (SELECT GROUP_CONCAT(CONCAT(nadadoras.nombre, ' ', nadadoras.apellidos) SEPARATOR ', ') 
                                   FROM rutinas_participantes, nadadoras 
                                   WHERE rutinas_participantes.id_nadadora = nadadoras.id AND rutinas_participantes.id_rutina = rutinas.id) as nombres
                           FROM rutinas
                           JOIN fases ON rutinas.id_fase = fases.id
                           JOIN clubes ON rutinas.id_club = clubes.id
                           JOIN modalidades ON fases.id_modalidad = modalidades.id
                           JOIN categorias ON fases.id_categoria = categorias.id
                           WHERE fases.id_competicion = $id_comp $condicion_club_rut
                           ORDER BY fases.orden ASC, rutinas.orden ASC, clubes.nombre_corto ASC";
            $res_ruts_list = mysqli_query($connection, $query_ruts);
            $total_ruts_encontradas = ($res_ruts_list) ? mysqli_num_rows($res_ruts_list) : 0;
            ?>

            <?php if($total_ruts_encontradas == 0): ?>
                <div class="bg-white p-10 rounded-2xl sm:rounded-[2.5rem] border border-slate-200 text-center">
                    <p class="text-sm font-bold text-slate-400 italic">No hay rutinas registradas para esta competición aún.</p>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-2xl sm:rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden">
                    <div class="overflow-x-auto no-scrollbar">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50 text-slate-400 text-[10px] font-black uppercase tracking-widest border-b border-slate-100">
                                    <th class="px-6 py-3.5 w-16 text-center">Ord</th>
                                    <th class="px-6 py-3.5">Fase & Modalidad</th>
                                    <th class="px-4 py-3.5">Club</th>
                                    <th class="px-6 py-3.5">Deportistas</th>
                                    <th class="px-4 py-3.5 text-center w-28">Música</th>
                                    <th class="px-4 py-3.5 text-center w-28">Coach Card</th>
                                    <th class="px-6 py-3.5 text-center w-36">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <?php while($r_row = mysqli_fetch_assoc($res_ruts_list)): 
                                    $mod_sufijo = ($r_row['fase_genero'] === 'masculino') ? ' Masc' : '';
                                    $has_audio = (!empty($r_row['music_name']) || !empty($r_row['music_original_name']));
                                    $has_cc = ($r_row['dd_total'] > 0);
                                ?>
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="px-6 py-3.5 text-center font-black text-slate-700 tabular-nums">
                                        <?php echo ($r_row['orden'] == 0 || $r_row['orden'] === null) ? '-' : $r_row['orden']; ?>
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <div class="font-black text-slate-800 text-xs uppercase tracking-tight">
                                            <?php echo htmlspecialchars($r_row['nombre_modalidad'] . $mod_sufijo . ' ' . $r_row['nombre_categoria']); ?>
                                        </div>
                                        <?php if(!empty($r_row['tematica'])): ?>
                                            <span class="text-[10px] font-bold text-slate-400 italic block">&laquo;<?php echo htmlspecialchars($r_row['tematica']); ?>&raquo;</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3.5 font-bold text-blue-600 uppercase text-xs">
                                        <?php echo htmlspecialchars($r_row['nombre_club']); ?>
                                    </td>
                                    <td class="px-6 py-3.5 text-xs text-slate-700 font-medium">
                                        <?php echo !empty($r_row['nombres']) ? htmlspecialchars($r_row['nombres']) : '<span class="text-red-500 font-bold italic">Sin participantes</span>'; ?>
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        <button onclick='openMusicPlayer(<?php echo $r_row["id"]; ?>, <?php echo json_encode($r_row["nombre_modalidad"].$mod_sufijo." ".$r_row["nombre_categoria"]); ?>, <?php echo json_encode($r_row["nombre_club"]); ?>, <?php echo json_encode($r_row["nombres"]); ?>, <?php echo json_encode($r_row["music_name"] ? "./public/music/".$id_comp."/".$r_row["id"].".mp3" : ""); ?>, <?php echo json_encode($r_row["music_original_name"]); ?>, <?php echo json_encode($r_row["id_club"]); ?>, <?php echo json_encode($r_row["logo"] ?: "./images/default_club.png"); ?>, 120, 5)'
                                                class="px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider inline-flex items-center gap-1.5 transition-all <?php echo $has_audio ? 'bg-emerald-50 text-emerald-600 border border-emerald-200 hover:bg-emerald-100' : 'bg-red-50 text-red-600 border border-red-200 hover:bg-red-100'; ?>">
                                            <i class="fas <?php echo $has_audio ? 'fa-play' : 'fa-upload'; ?>"></i>
                                            <span><?php echo $has_audio ? 'Audio' : 'Subir'; ?></span>
                                        </button>
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        <?php if($r_row['elementos_coach_card'] > 0): ?>
                                            <a href="./coach_card_composer.php?id_rutina=<?php echo $r_row['id']; ?>&id_fase=<?php echo $r_row['id_fase']; ?>" 
                                               class="px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider inline-flex items-center gap-1.5 transition-all <?php echo $has_cc ? 'bg-blue-50 text-blue-600 border border-blue-200 hover:bg-blue-100' : 'bg-amber-50 text-amber-600 border border-amber-200 hover:bg-amber-100'; ?>">
                                                <i class="fas fa-puzzle-piece"></i>
                                                <span><?php echo $has_cc ? 'DD: ' . number_format($r_row['dd_total'], 3) : 'Crear'; ?></span>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-[10px] text-slate-400 font-bold italic">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-3.5 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <form action="./rutinas_participantes.php" method="POST" class="inline">
                                                <input type="hidden" name="id_rutina" value="<?php echo $r_row['id']; ?>">
                                                <input type="hidden" name="id_competicion" value="<?php echo $id_comp; ?>">
                                                <input type="hidden" name="from_inscripciones" value="1">
                                                <button type="submit" class="w-8 h-8 rounded-xl bg-slate-50 text-slate-500 hover:bg-blue-50 hover:text-blue-600 transition-all border border-slate-200 inline-flex items-center justify-center" title="Asignar participantes">
                                                    <i class="fas fa-users text-xs"></i>
                                                </button>
                                            </form>
                                            <form action="rutinas_edit.php" method="POST" class="inline">
                                                <input type="hidden" name="id_rutina" value="<?php echo $r_row['id']; ?>">
                                                <input type="hidden" name="from_inscripciones" value="1">
                                                <button type="submit" class="w-8 h-8 rounded-xl bg-slate-50 text-slate-500 hover:bg-slate-100 transition-all border border-slate-200 inline-flex items-center justify-center" title="Editar detalles">
                                                    <i class="fas fa-edit text-xs"></i>
                                                </button>
                                            </form>
                                            <?php if($enable_inscripcion == ''): ?>
                                            <button type="button" onclick="confirmDeleteRutina(<?php echo $r_row['id']; ?>, '<?php echo addslashes($r_row['nombre_modalidad'].' '.$r_row['nombre_categoria']); ?>', '<?php echo addslashes($r_row['nombre_club']); ?>')"
                                                    class="w-8 h-8 rounded-xl bg-slate-50 text-slate-400 hover:bg-red-50 hover:text-red-500 transition-all border border-slate-200 inline-flex items-center justify-center" title="Eliminar rutina">
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

        </div>
        <?php endif; ?>

    </div>
</main>

<!-- ========================================================================= -->
<!-- MODALES Y SCRIPTS                                                         -->
<!-- ========================================================================= -->

<!-- Modal Reproductor y Carga de Música -->
<div id="musicModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-[2.5rem] w-full max-w-lg shadow-2xl overflow-hidden animate-zoom-in">
        <div class="p-6 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-base font-black text-slate-800 flex items-center gap-2">
                <i class="fas fa-music text-blue-600"></i> Gestión de Acompañamiento Musical
            </h3>
            <button onclick="closeMusicModal()" class="w-8 h-8 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center hover:bg-slate-300">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>

        <div class="p-6">
            <div id="playerSection" class="mb-6 hidden">
                <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 text-center relative">
                    <?php if($enable_musica == ''): ?>
                    <form action="rutinas_code.php" method="POST" class="absolute top-3 right-3" onsubmit="return confirm('¿Eliminar este audio?');">
                        <input type="hidden" name="delete_music" value="1">
                        <input type="hidden" name="del_music_id" id="del_music_id">
                        <input type="hidden" name="del_music_comp" value="<?php echo $id_comp; ?>">
                        <input type="hidden" name="return_to" value="inscripciones.php?tab=rutinas">
                        <button type="submit" class="w-7 h-7 rounded-full bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-all flex items-center justify-center border border-red-100" title="Eliminar Audio">
                            <i class="fas fa-trash-alt text-xs"></i>
                        </button>
                    </form>
                    <?php endif; ?>

                    <p id="playerRoutine" class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1"></p>
                    <h4 id="playerClub" class="text-base font-black text-slate-800 mb-3"></h4>
                    <audio id="audioElement" controls class="w-full h-11 mb-2"></audio>
                    <p id="fileName" class="text-[10px] font-bold text-blue-500 italic break-all"></p>
                </div>
            </div>

            <div id="noMusicAlert" class="mb-6 hidden">
                <div class="bg-amber-50 p-4 rounded-2xl border border-amber-200 flex items-center gap-3 text-amber-800 text-xs font-bold">
                    <i class="fas fa-exclamation-triangle text-lg text-amber-500"></i>
                    <span>Esta rutina no tiene música cargada.</span>
                </div>
            </div>

            <input type="hidden" id="music_edit_id">
            <input type="hidden" id="music_club_id">
            <input type="hidden" id="music_name_val">

            <?php if($enable_musica == ''): ?>
                <form action="rutinas_code.php" method="POST" enctype="multipart/form-data" class="space-y-3" onsubmit="syncMusicInputs(this)">
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-1.5 px-1">Subir nuevo archivo (.mp3)</label>
                        <input type="file" name="musica" id="musicFileInput" accept=".mp3" required 
                               class="w-full px-4 py-3 bg-slate-50 border-2 border-dashed border-slate-200 rounded-xl text-xs font-bold text-slate-500 hover:border-blue-400 transition-all cursor-pointer">
                    </div>
                    <input type="hidden" name="edit_id" id="form_music_edit_id">
                    <input type="hidden" name="club" id="form_music_club_id">
                    <input type="hidden" name="music_name" id="form_music_name_val">
                    <input type="hidden" name="id_competicion" value="<?php echo $id_comp; ?>">
                    <input type="hidden" name="return_to" value="inscripciones.php?tab=rutinas">
                    <button type="submit" name="upload_music" class="w-full py-3 bg-blue-600 text-white font-black uppercase text-xs tracking-wider rounded-xl shadow-md hover:bg-blue-700 transition-all">
                        Guardar Audio
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Borrado Rutina -->
<div id="deleteRutinaModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-[2.5rem] w-full max-w-md shadow-2xl p-6 text-center">
        <div class="w-16 h-16 bg-red-50 text-red-500 rounded-2xl flex items-center justify-center text-2xl mx-auto mb-4 border border-red-100">
            <i class="fas fa-trash-alt"></i>
        </div>
        <h3 class="text-xl font-black text-slate-800 mb-2">¿Eliminar esta rutina?</h3>
        <p class="text-xs text-slate-500 font-medium mb-6 leading-relaxed">
            Se eliminará la rutina <b id="delRutName"></b> del club <b id="delRutClub"></b>, incluyendo deportistas y Coach Card vinculadas.
        </p>
        <form action="rutinas_code.php" method="POST">
            <input type="hidden" name="delete_id" id="delRutId">
            <input type="hidden" name="return_to" value="inscripciones.php?id_competicion=<?php echo $id_comp; ?>&tab=rutinas">
            <div class="flex gap-3">
                <button type="button" onclick="closeDeleteRutinaModal()" class="flex-1 py-3 bg-slate-100 text-slate-600 font-black uppercase text-xs rounded-xl hover:bg-slate-200">Cancelar</button>
                <button type="submit" name="delete_btn" class="flex-1 py-3 bg-red-600 text-white font-black uppercase text-xs rounded-xl shadow-md hover:bg-red-700">Eliminar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Borrado Nadadora Figuras -->
<div id="deleteFigModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-[2.5rem] w-full max-w-md shadow-2xl p-6 text-center">
        <div class="w-16 h-16 bg-red-50 text-red-500 rounded-2xl flex items-center justify-center text-2xl mx-auto mb-4 border border-red-100">
            <i class="fas fa-trash-can"></i>
        </div>
        <h3 class="text-xl font-black text-slate-800 mb-2">¿Eliminar nadadora?</h3>
        <p class="text-xs text-slate-500 font-medium mb-6 leading-relaxed">
            Se eliminará a <b id="delFigSwimmerName"></b> de todas las figuras de su categoría en esta competición.
        </p>
        <form action="inscripciones_figuras_code.php" method="POST">
            <input type="hidden" name="delete_id" id="delFigId">
            <input type="hidden" name="return_to" value="inscripciones.php?tab=figuras">
            <div class="flex gap-3">
                <button type="button" onclick="closeDeleteFigModal()" class="flex-1 py-3 bg-slate-100 text-slate-600 font-black uppercase text-xs rounded-xl hover:bg-slate-200">Cancelar</button>
                <button type="submit" name="delete_btn" class="flex-1 py-3 bg-red-600 text-white font-black uppercase text-xs rounded-xl shadow-md hover:bg-red-700">Eliminar</button>
            </div>
        </form>
    </div>
</div>

<script src="vendor/tom-select/tom-select.complete.min.js"></script>
<script>
const tsInstances = {};

function initTomSelectCat(idCat) {
    const el = document.getElementById('select_nadadora_' + idCat);
    if (!el) return null;
    if (tsInstances[idCat]) return tsInstances[idCat];

    if (typeof TomSelect !== 'undefined') {
        const ts = new TomSelect(el, {
            create: false,
            maxItems: 1,
            closeAfterSelect: true,
            allowEmptyOption: false,
            dropdownParent: 'body',
            placeholder: 'Escribe para buscar nadadora por apellidos o nombre...',
            render: {
                no_results: function(data, escape) {
                    return '<div class="no-results p-3 text-xs text-slate-400 font-bold">No se encontraron nadadoras con ese nombre</div>';
                }
            }
        });

        // Ocultar el texto de item o selección previa en cuanto se empieza a escribir
        ts.on('type', function(str) {
            if (str && str.trim().length > 0) {
                ts.wrapper.classList.add('ts-typing');
            } else {
                ts.wrapper.classList.remove('ts-typing');
            }
        });
        ts.on('blur', function() {
            ts.wrapper.classList.remove('ts-typing');
        });

        // Al seleccionar nadadora: cerrar popup, liberar foco del buscador y enfocar el botón Registrar
        ts.on('change', function(value) {
            ts.wrapper.classList.remove('ts-typing');
            if (value && value.toString().trim() !== '' && value !== '0') {
                ts.close();
                ts.blur();
                const formWrap = document.getElementById('quick-inscribe-' + idCat);
                if (formWrap) {
                    const btn = formWrap.querySelector('button[name="save_btn"]');
                    if (btn) btn.focus();
                }
            }
        });

        tsInstances[idCat] = ts;
        return ts;
    }
    return null;
}

function validateInscribeFig(form) {
    const select = form.querySelector('select[name="id_nadadora"]');
    if (!select || !select.value || select.value.trim() === '' || select.value === '0') {
        alert('Por favor, selecciona una nadadora válida antes de registrar.');
        return false;
    }
    const btn = form.querySelector('button[name="save_btn"]');
    if (btn) {
        btn.classList.add('opacity-75', 'pointer-events-none');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Registrando...';
    }
    return true;
}

function setInscTab(tab) {
    const tabFig = document.getElementById('tabContentInscFiguras');
    const tabRut = document.getElementById('tabContentInscRutinas');
    const btnFig = document.getElementById('btnTabInscFiguras');
    const btnRut = document.getElementById('btnTabInscRutinas');

    if (tab === 'figuras') {
        if(tabFig) tabFig.classList.remove('hidden');
        if(tabRut) tabRut.classList.add('hidden');
        if(btnFig) {
            btnFig.classList.add('bg-white', 'text-emerald-600', 'shadow-md', 'ring-1', 'ring-black/5');
            btnFig.classList.remove('text-slate-600');
        }
        if(btnRut) {
            btnRut.classList.remove('bg-white', 'text-blue-600', 'shadow-md', 'ring-1', 'ring-black/5');
            btnRut.classList.add('text-slate-600');
        }
    } else {
        if(tabFig) tabFig.classList.add('hidden');
        if(tabRut) tabRut.classList.remove('hidden');
        if(btnRut) {
            btnRut.classList.add('bg-white', 'text-blue-600', 'shadow-md', 'ring-1', 'ring-black/5');
            btnRut.classList.remove('text-slate-600');
        }
        if(btnFig) {
            btnFig.classList.remove('bg-white', 'text-emerald-600', 'shadow-md', 'ring-1', 'ring-black/5');
            btnFig.classList.add('text-slate-600');
        }
    }
}

function toggleQuickInscribe(idCat) {
    const form = document.getElementById('quick-inscribe-' + idCat);
    if (!form) return;
    const isHidden = form.classList.contains('hidden');
    if (isHidden) {
        form.classList.remove('hidden');
        const ts = initTomSelectCat(idCat);
        form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        if (ts) {
            setTimeout(function() { ts.focus(); }, 120);
        }
    } else {
        form.classList.add('hidden');
        if (tsInstances[idCat]) {
            tsInstances[idCat].close();
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const openCat = urlParams.get('open_cat');
    if (openCat) {
        setInscTab('figuras');
        const form = document.getElementById('quick-inscribe-' + openCat);
        if (form) {
            form.classList.remove('hidden');
            const ts = initTomSelectCat(openCat);
            setTimeout(function() {
                form.scrollIntoView({ behavior: 'smooth', block: 'center' });
                if (ts) ts.focus();
            }, 250);
        }
    }
});

function toggleAddRutinaPanel() {
    const panel = document.getElementById('addRutinaPanel');
    if(panel) {
        panel.classList.toggle('hidden');
        if(!panel.classList.contains('hidden')) panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function launchConfirmDeleteFig(id, name) {
    document.getElementById('delFigId').value = id;
    document.getElementById('delFigSwimmerName').innerText = name;
    document.getElementById('deleteFigModal').classList.remove('hidden');
}

function closeDeleteFigModal() {
    document.getElementById('deleteFigModal').classList.add('hidden');
}

function confirmDeleteRutina(id, routineName, clubName) {
    document.getElementById('delRutId').value = id;
    document.getElementById('delRutName').innerText = routineName;
    document.getElementById('delRutClub').innerText = clubName;
    document.getElementById('deleteRutinaModal').classList.remove('hidden');
}

function closeDeleteRutinaModal() {
    document.getElementById('deleteRutinaModal').classList.add('hidden');
}

function openMusicPlayer(id, routine, club, swimmers, audioUrl, originalName, clubId, logoUrl, targetSec, marginSec) {
    document.getElementById('playerRoutine').innerText = routine;
    document.getElementById('playerClub').innerText = club;
    document.getElementById('del_music_id').value = id;
    document.getElementById('music_edit_id').value = id;
    document.getElementById('music_club_id').value = clubId;
    document.getElementById('music_name_val').value = routine;

    const audio = document.getElementById('audioElement');
    const playerSec = document.getElementById('playerSection');
    const noMusic = document.getElementById('noMusicAlert');
    const fileLabel = document.getElementById('fileName');

    if (audioUrl && audioUrl.length > 5) {
        audio.src = audioUrl;
        playerSec.classList.remove('hidden');
        noMusic.classList.add('hidden');
        fileLabel.innerText = originalName ? 'Archivo: ' + originalName : '';
    } else {
        audio.src = '';
        playerSec.classList.add('hidden');
        noMusic.classList.remove('hidden');
    }

    document.getElementById('musicModal').classList.remove('hidden');
}

function closeMusicModal() {
    const audio = document.getElementById('audioElement');
    if(audio) audio.pause();
    document.getElementById('musicModal').classList.add('hidden');
}

function syncMusicInputs(form) {
    document.getElementById('form_music_edit_id').value = document.getElementById('music_edit_id').value;
    document.getElementById('form_music_club_id').value = document.getElementById('music_club_id').value;
    document.getElementById('form_music_name_val').value = document.getElementById('music_name_val').value;
}
</script>

<?php 
include('includes/scripts.php');
include('includes/footer.php'); 
?>