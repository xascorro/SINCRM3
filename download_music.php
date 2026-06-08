<?php
session_start();
set_time_limit(1000);
ini_set('memory_limit', '512M');
include('security.php'); 
include('includes/header.php');
include('includes/navbar.php');

// Prioridad a POST, fallback a GET y luego a SESSION
$id_competicion = isset($_POST['id_competicion']) ? (int)$_POST['id_competicion'] : (isset($_GET['id_competicion']) ? (int)$_GET['id_competicion'] : (isset($_SESSION['id_competicion_usuario']) ? (int)$_SESSION['id_competicion_usuario'] : null));
$id_club_input = isset($_POST['id_club']) ? (int)$_POST['id_club'] : (isset($_GET['id_club']) ? (int)$_GET['id_club'] : null);

// Definición de roles administrativos (coincidente con navbar.php)
$roles_admin = ['1', '2', '3'];
$es_admin = in_array($_SESSION['id_rol'], $roles_admin);

// --- CONTROL DE ACCESO ESTRICTO ---
// Si es ROL CLUB (5), forzamos que el ID del club sea el suyo de la sesión
if ($_SESSION['id_rol'] == 5) {
    $id_club = (int)$_SESSION['club'];
} else {
    // Si es Admin u otro, permitimos el filtro de club si viene informado
    $id_club = $id_club_input;
}

$condicion_club = '';
if ($id_club !== null && $id_club > 0) {
    $condicion_club = ' AND rutinas.id_club = ' . (int)$id_club;
}

if ($id_competicion && !isset($_SESSION['mensajes_descarga_' . $id_competicion])) {
    $_SESSION['mensajes_descarga_' . $id_competicion] = [];
}

function agregarMensajeDescarga($mensaje, $tipo = 'info') {
    global $id_competicion;
    if (!$id_competicion) return;
    $hora = date('[H:i:s] ');
    $color = ($tipo == 'error') ? 'text-red-500' : (($tipo == 'warning') ? 'text-amber-500' : 'text-emerald-500');
    $_SESSION['mensajes_descarga_' . $id_competicion][] = "<span class='$color font-mono'>$hora $mensaje</span>";
}

// --- LÓGICA DE ESCANEO E INTEGRIDAD ---
$path_base = "./public/music/" . (int)$id_competicion . "/";
$stats_fases = [];
$ids_en_disco = [];

if ($id_competicion !== null && is_dir($path_base)) {
    // 1. Escaneo físico del disco
    $dir = new DirectoryIterator($path_base);
    foreach ($dir as $file) {
        if ($file->isFile() && $file->getExtension() === 'mp3') {
            $id_archivo = $file->getBasename('.mp3');
            if (is_numeric($id_archivo)) $ids_en_disco[] = (int)$id_archivo;
        }
    }

    // 2. Consulta de base de datos
    $query = "SELECT 
                rutinas.id,
                rutinas.id_fase, 
                modalidades.nombre AS mod_nom, 
                categorias.nombre AS cat_nom, 
                clubes.nombre_corto AS nombre_club,
                fases.orden,
                (SELECT GROUP_CONCAT(CONCAT(n.nombre, ' ', n.apellidos) SEPARATOR ', ') 
                 FROM rutinas_participantes rp 
                 JOIN nadadoras n ON rp.id_nadadora = n.id 
                 WHERE rp.id_rutina = rutinas.id AND rp.reserva = 'no') as nadadoras
              FROM rutinas 
              INNER JOIN fases ON rutinas.id_fase = fases.id 
              INNER JOIN modalidades ON fases.id_modalidad = modalidades.id 
              INNER JOIN categorias ON fases.id_categoria = categorias.id 
              INNER JOIN clubes ON rutinas.id_club = clubes.id
              WHERE fases.id_competicion = $id_competicion $condicion_club
              ORDER BY fases.orden ASC";

    $result = mysqli_query($connection, $query);
    
    $ids_en_db = [];
    $ids_totales_competicion = []; // Para evitar falsos huérfanos al filtrar por club
    $rutinas_sin_musica = [];
    $arbol_fases = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $f_id = $row['id_fase'];
            $id_rutina = (int)$row['id'];
            $ids_en_db[] = $id_rutina;

            // Inicializar fase en el árbol si no existe
            if (!isset($arbol_fases[$f_id])) {
                $arbol_fases[$f_id] = [
                    'nombre' => $row['mod_nom'] . " " . $row['cat_nom'],
                    'rutinas' => []
                ];
            }

            // Inicializar fase en el array de estadísticas si no existe
            if (!isset($stats_fases[$f_id])) {
                $stats_fases[$f_id] = [
                    'nombre' => $row['mod_nom'] . " " . $row['cat_nom'],
                    'total' => 0,
                    'subidas' => 0
                ];
            }
            $stats_fases[$f_id]['total']++;

            $has_file = in_array($id_rutina, $ids_en_disco);
            
            // Añadir rutina al árbol
            $arbol_fases[$f_id]['rutinas'][] = [
                'id' => $id_rutina,
                'club' => $row['nombre_club'],
                'nadadoras' => $row['nadadoras'],
                'tiene_musica' => $has_file
            ];

            // Verificar si existe el archivo
            if ($has_file) {
                $stats_fases[$f_id]['subidas']++;
            } else {
                $info_rutina = "<strong>#" . $id_rutina . "</strong> " . $row['nombre_club'] . " (" . $row['mod_nom'] . " " . $row['cat_nom'] . ")";
                if (!empty($row['nadadoras'])) {
                    $info_rutina .= " - <span class='opacity-70'>" . $row['nadadoras'] . "</span>";
                }
                $rutinas_sin_musica[] = $info_rutina;
            }
        }
    }

    // 2.1 Consulta de control para Huérfanos (siempre toda la competición)
    if ($es_admin) {
        $q_huerfanos = "SELECT r.id FROM rutinas r INNER JOIN fases f ON r.id_fase = f.id WHERE f.id_competicion = $id_competicion";
        $res_huerfanos = mysqli_query($connection, $q_huerfanos);
        while($r_h = mysqli_fetch_assoc($res_huerfanos)) $ids_totales_competicion[] = (int)$r_h['id'];

        $huerfanos = array_diff($ids_en_disco, $ids_totales_competicion);
        foreach ($huerfanos as $h) {
            agregarMensajeDescarga("HUÉRFANO: El archivo $h.mp3 no pertenece a ninguna rutina activa en esta competición.", 'warning');
        }
    }
    foreach ($rutinas_sin_musica as $faltante) {
        agregarMensajeDescarga("FALTANTE: Sin música -> $faltante", 'error');
    }
}
?>

<main class="flex-1 flex flex-col min-w-0 bg-surface">
    <?php include('includes/topbar.php'); ?>

    <div class="p-6 md:p-10 max-w-6xl mx-auto w-full font-lexend text-primary">
        
        <!-- Header -->
        <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-black text-slate-800 tracking-tighter mb-2 flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shadow-sm border border-indigo-100"><i class="fas fa-music text-lg"></i></span>
                    Gestor de Música
                </h1>
                <p class="text-slate-500 font-medium">
                    <?php echo ($id_club > 0) ? "Descarga del acompañamiento musical de su club." : "Exploración y descarga de archivos por fase de competición."; ?>
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <?php if ($es_admin || $_SESSION['id_rol'] == 5): ?>
                    <form action="descargar_fase.php" method="POST">
                        <input type="hidden" name="id_competicion" value="<?php echo $id_competicion; ?>">
                        <input type="hidden" name="id_club" value="<?php echo $id_club; ?>">
                        <input type="hidden" name="descargar_todo" value="1">
                        <button type="submit" class="px-6 py-3 bg-indigo-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg hover:bg-indigo-700 hover:scale-105 transition-all flex items-center gap-2">
                            <i class="fas fa-file-archive"></i> Descargar Todo (.zip)
                        </button>
                    </form>
                <?php endif; ?>
                <a href="rutinas.php" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 font-black uppercase text-xs tracking-widest rounded-2xl shadow-sm hover:bg-slate-50 transition-all flex items-center gap-2">
                    <i class="fas fa-chevron-left text-xs"></i> Volver
                </a>
            </div>
        </div>

        <!-- Filtros (Solo Admin) -->
        <?php if ($es_admin): ?>
            <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-200 mb-10">
                <form action="download_music.php" method="GET" class="flex flex-col md:flex-row items-end gap-6">
                    <input type="hidden" name="id_competicion" value="<?php echo $id_competicion; ?>">
                    <div class="flex-1 w-full space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Filtrar por Club</label>
                        <select name="id_club" class="v3-select-fix w-full" onchange="this.form.submit()">
                            <option value="0">--- Todos los Clubes ---</option>
                            <?php 
                            $q_clubes = "SELECT DISTINCT c.id, c.nombre_corto 
                                         FROM clubes c 
                                         INNER JOIN rutinas r ON r.id_club = c.id 
                                         INNER JOIN fases f ON r.id_fase = f.id 
                                         WHERE f.id_competicion = $id_competicion 
                                         ORDER BY c.nombre_corto ASC";
                            $res_clubes = mysqli_query($connection, $q_clubes);
                            while ($c = mysqli_fetch_assoc($res_clubes)) {
                                $sel = ($id_club == $c['id']) ? 'selected' : '';
                                echo "<option value='{$c['id']}' $sel>{$c['nombre_corto']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="flex-shrink-0">
                        <a href="download_music.php?id_competicion=<?php echo $id_competicion; ?>" class="px-6 py-3.5 bg-slate-100 text-slate-500 font-black uppercase text-[10px] tracking-widest rounded-xl hover:bg-slate-200 transition-all flex items-center gap-2">
                            <i class="fas fa-sync-alt"></i> Limpiar
                        </a>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <?php if (empty($stats_fases)): ?>
            <div class="bg-white rounded-[2.5rem] p-12 shadow-sm border border-slate-200 text-center">
                <div class="w-20 h-20 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-folder-open text-3xl"></i>
                </div>
                <h2 class="text-xl font-black text-slate-800 mb-2">No se han encontrado registros</h2>
                <p class="text-slate-500 max-w-sm mx-auto">No hay rutinas registradas para los criterios seleccionados en esta competición.</p>
            </div>
        <?php else: ?>
            <!-- Resumen de Progreso -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
                <?php foreach ($stats_fases as $id_f => $info): 
                    $porcentaje = ($info['total'] > 0) ? round(($info['subidas'] / $info['total']) * 100) : 0;
                    
                    $is_complete = ($porcentaje == 100);
                    $accent_color = $is_complete ? 'emerald' : 'red';
                    
                    if (!$is_complete && $porcentaje > 40) $accent_color = 'amber';
                    if (!$is_complete && $porcentaje > 89) $accent_color = 'blue';

                    $border_class = "border-l-".$accent_color."-500";
                    $bg_badge = "bg-".$accent_color."-50";
                    $text_badge = "text-".$accent_color."-600";
                    $bg_bar = "bg-".$accent_color."-500";
                ?>
                <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-200 border-l-[6px] <?php echo $border_class; ?> hover:shadow-lg transition-all group flex flex-col h-full">
                    <div class="flex justify-between items-start mb-6">
                        <div class="flex-1 min-w-0">
                            <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1 truncate">Fase Competición</p>
                            <h3 class="text-base font-black text-slate-800 leading-tight"><?php echo htmlspecialchars($info['nombre']); ?></h3>
                        </div>
                        <div class="px-3 py-1 rounded-lg <?php echo $bg_badge.' '.$text_badge; ?> text-[10px] font-black border border-current/10">
                            <?php echo $info['subidas']; ?> / <?php echo $info['total']; ?>
                        </div>
                    </div>

                    <div class="mb-8">
                        <div class="flex justify-between items-end mb-2">
                            <span class="text-[10px] font-bold text-slate-400 uppercase italic">Integridad</span>
                            <span class="text-sm font-black text-slate-700"><?php echo $porcentaje; ?>%</span>
                        </div>
                        <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden shadow-inner">
                            <div class="h-full <?php echo $bg_bar; ?> transition-all duration-1000 ease-out shadow-sm" style="width: <?php echo $porcentaje; ?>%"></div>
                        </div>
                    </div>

                    <div class="mt-auto">
                        <?php if ($info['subidas'] > 0): ?>
                            <form action="descargar_fase.php" method="POST">
                                <input type="hidden" name="id_competicion" value="<?php echo $id_competicion; ?>">
                                <input type="hidden" name="id_fase" value="<?php echo $id_f; ?>">
                                <input type="hidden" name="id_club" value="<?php echo $id_club; ?>">
                                <button type="submit" class="w-full py-3 bg-slate-800 text-white font-black uppercase text-[10px] tracking-widest rounded-xl shadow-md hover:bg-black hover:scale-[1.02] transition-all flex items-center justify-center gap-2">
                                    <i class="fas fa-cloud-download-alt"></i> Descargar ZIP
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="w-full py-3 bg-slate-50 text-slate-400 font-black uppercase text-[10px] tracking-widest rounded-xl border border-slate-100 flex items-center justify-center gap-2 italic">
                                <i class="fas fa-exclamation-circle opacity-50"></i> Sin archivos
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Diagnóstico de Integridad -->
            <?php if (!empty($_SESSION['mensajes_descarga_' . $id_competicion])): ?>
                <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden mb-10">
                    <div class="px-8 py-4 bg-slate-50/80 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                            Diagnóstico de Integridad Técnica
                        </h3>
                    </div>
                    <div class="p-8 max-h-[300px] overflow-y-auto scrollbar-thin scrollbar-thumb-slate-200 custom-scrollbar bg-slate-50/30">
                        <div class="space-y-3">
                            <?php 
                            foreach ($_SESSION['mensajes_descarga_' . $id_competicion] as $m) {
                                echo "<div class='flex gap-3 text-[11px] font-bold leading-relaxed border-l-2 border-slate-200 pl-4 py-0.5'>$m</div>";
                            }
                            unset($_SESSION['mensajes_descarga_' . $id_competicion]);
                            ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Explorador de Rutinas y Música -->
            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden mb-10">
                <div class="px-8 py-4 bg-slate-50 border-b border-slate-100">
                    <h3 class="text-xs font-black text-slate-800 uppercase tracking-widest flex items-center gap-3">
                        <i class="fas fa-sitemap text-blue-600"></i>
                        Explorador de Rutinas y Música
                    </h3>
                </div>
                <div class="p-8">
                    <div class="space-y-6">
                        <?php foreach ($arbol_fases as $id_fase => $fase): ?>
                            <div class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs">
                                        <i class="fas fa-layer-group"></i>
                                    </div>
                                    <span class="text-sm font-black text-slate-800 uppercase"><?php echo htmlspecialchars($fase['nombre']); ?></span>
                                </div>
                                <div class="ml-4 pl-7 border-l-2 border-slate-100 space-y-2">
                                    <?php foreach ($fase['rutinas'] as $rutina): ?>
                                        <div class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 transition-colors border border-transparent hover:border-slate-100 group">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <span class="text-[10px] font-black text-slate-300 group-hover:text-slate-500">#<?php echo $rutina['id']; ?></span>
                                                <div class="flex flex-col min-w-0">
                                                    <span class="text-xs font-black text-slate-700 truncate"><?php echo htmlspecialchars($rutina['club']); ?></span>
                                                    <span class="text-[10px] font-bold text-slate-400 truncate"><?php echo htmlspecialchars($rutina['nadadoras'] ?: 'Sin participantes'); ?></span>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <?php if($rutina['tiene_musica']): ?>
                                                    <button onclick="playMusic('<?php echo './public/music/'.$id_competicion.'/'.$rutina['id'].'.mp3'; ?>', '<?php echo addslashes($rutina['club']); ?>', '<?php echo addslashes($fase['nombre']); ?>')" 
                                                            class="flex items-center gap-2 px-3 py-1 bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all rounded-lg text-[9px] font-black uppercase border border-indigo-100">
                                                        <i class="fas fa-play"></i> ESCUCHAR
                                                    </button>
                                                    <span class="flex items-center gap-2 px-3 py-1 bg-emerald-50 text-emerald-600 rounded-lg text-[9px] font-black uppercase border border-emerald-100">
                                                        <i class="fas fa-check-circle"></i> OK
                                                    </span>
                                                <?php else: ?>
                                                    <span class="flex items-center gap-2 px-3 py-1 bg-red-50 text-red-500 rounded-lg text-[9px] font-black uppercase animate-pulse border border-red-100">
                                                        <i class="fas fa-times-circle"></i> SIN ARCHIVO
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>
</main>

<!-- Modal Reproductor Flotante -->
<div id="playModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-[2.5rem] w-full max-w-md shadow-2xl overflow-hidden animate-zoom-in">
        <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-indigo-50/30">
            <h3 class="text-lg font-black text-slate-800 flex items-center gap-3">
                <i class="fas fa-music text-indigo-600"></i> Previsualizar Audio
            </h3>
            <button onclick="stopMusic()" class="w-10 h-10 rounded-2xl bg-white text-slate-400 hover:text-red-500 transition-colors flex items-center justify-center shadow-sm">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-8 text-center">
            <p id="playFase" class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1"></p>
            <h4 id="playClub" class="text-xl font-black text-slate-800 mb-8"></h4>
            
            <div class="bg-slate-50 p-6 rounded-3xl border border-slate-100 mb-2">
                <audio id="audioPlayer" controls class="w-full h-12"></audio>
            </div>
            <p class="text-[9px] font-bold text-slate-400 italic">Verificando integridad del archivo .mp3</p>
        </div>
    </div>
</div>

<script>
function playMusic(url, club, fase) {
    const modal = document.getElementById('playModal');
    const audio = document.getElementById('audioPlayer');
    const clubEl = document.getElementById('playClub');
    const faseEl = document.getElementById('playFase');

    clubEl.textContent = club;
    faseEl.textContent = fase;
    audio.src = url + '?v=' + new Date().getTime();
    
    modal.classList.remove('hidden');
    audio.play();
}

function stopMusic() {
    const modal = document.getElementById('playModal');
    const audio = document.getElementById('audioPlayer');
    
    audio.pause();
    audio.currentTime = 0;
    modal.classList.add('hidden');
}
</script>

<style>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.05); border-radius: 10px; }
</style>

<?php 
include('includes/scripts.php');
include('includes/footer.php'); 
?>
