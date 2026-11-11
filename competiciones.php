<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');

// 1. Cálculos de KPIs para Competiciones
$q_stats = "SELECT 
            COUNT(*) as total_db,
            SUM(CASE WHEN figuras = 'si' AND (nombre LIKE '%Pase%' OR nombre LIKE '%Nivel%') THEN 1 ELSE 0 END) as con_pase,
            SUM(CASE WHEN figuras = 'si' AND NOT (nombre LIKE '%Pase%' OR nombre LIKE '%Nivel%') THEN 1 ELSE 0 END) as con_figuras,
            SUM(CASE WHEN figuras = 'no' OR figuras IS NULL THEN 1 ELSE 0 END) as con_rutinas,
            SUM(CASE WHEN fecha >= DATE_SUB(NOW(), INTERVAL 1 YEAR) THEN 1 ELSE 0 END) as temporada,
            COUNT(DISTINCT lugar) as sedes
            FROM competiciones";
$res_stats = mysqli_query($connection, $q_stats);
$stats = mysqli_fetch_assoc($res_stats);

$n_figuras = $stats['con_figuras'] ?: 0;
$n_rutinas = $stats['con_rutinas'] ?: 0;
$n_pases = $stats['con_pase'] ?: 0;
$total_tecnico = $n_figuras + $n_rutinas + $n_pases;

$pct_figuras = ($total_tecnico > 0) ? round(($n_figuras / $total_tecnico) * 100) : 0;
$pct_rutinas = ($total_tecnico > 0) ? round(($n_rutinas / $total_tecnico) * 100) : 0;
$pct_pases = ($total_tecnico > 0) ? round(($n_pases / $total_tecnico) * 100) : 0;
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-surface">
    
    <?php include('includes/topbar.php'); ?>

    <div class="px-3 py-4 sm:p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend">
        
        <!-- Header -->
        <div class="mb-6 sm:mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4 sm:gap-6 text-primary">
            <div>
                <h1 class="text-2xl sm:text-4xl font-black text-slate-800 tracking-tight mb-1 sm:mb-2 flex items-center gap-2.5 sm:gap-3">
                    <span class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-flag-checkered text-base sm:text-lg"></i></span>
                    Competiciones
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 font-medium italic">Gestión del calendario y configuración de eventos regionales.</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2.5 sm:gap-3 items-stretch sm:items-center w-full md:w-auto">
                <!-- Buscador Dinámico -->
                <div class="relative w-full sm:w-64 md:w-80">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </span>
                    <input type="text" id="compSearchInput" placeholder="Buscar por nombre, lugar o tipo..." 
                           class="w-full pl-9 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl sm:rounded-2xl text-xs font-bold text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all outline-none shadow-xs"
                           onkeyup="filterCompTable()">
                </div>

                <div class="flex items-center gap-2 sm:gap-3 justify-between sm:justify-start">
                    <!-- Selector Segmentado de Vista (Mosaico / Lista) -->
                    <div class="inline-flex p-1 bg-slate-100 rounded-xl sm:rounded-2xl border border-slate-200/80 shadow-2xs shrink-0" role="group" aria-label="Cambiar vista">
                        <button type="button" id="viewBtnGrid" onclick="setCompView('grid')" title="Vista en Mosaico (Tarjetas)"
                                class="px-3 py-1.5 rounded-lg sm:rounded-xl text-xs font-black uppercase tracking-wider flex items-center gap-1.5 transition-all duration-200 active:scale-[0.98] bg-white text-blue-600 shadow-xs border border-slate-200/80">
                            <i class="fas fa-table-cells-large text-xs"></i>
                            <span class="hidden md:inline">Mosaico</span>
                        </button>
                        <button type="button" id="viewBtnList" onclick="setCompView('list')" title="Vista en Lista"
                                class="px-3 py-1.5 rounded-lg sm:rounded-xl text-xs font-bold uppercase tracking-wider flex items-center gap-1.5 transition-all duration-200 active:scale-[0.98] text-slate-500 hover:text-slate-800 border border-transparent">
                            <i class="fas fa-list-ul text-xs"></i>
                            <span class="hidden md:inline">Lista</span>
                        </button>
                    </div>

                    <!-- Botón Crear Evento -->
                    <button onclick="toggleAddCompPanel()" class="flex-1 sm:flex-initial px-5 sm:px-6 py-2.5 sm:py-3 bg-blue-600 text-white font-black uppercase text-[11px] sm:text-xs tracking-wider rounded-xl sm:rounded-2xl shadow-lg active:scale-[0.98] transition-all flex items-center justify-center gap-2 shrink-0">
                        <i class="fas fa-plus text-xs"></i> Crear Evento
                    </button>
                </div>
            </div>
        </div>

        <?php include('includes/alertas_v4.php'); ?>

        <!-- Panel Añadir -->
        <div id="addCompPanel" class="hidden mb-6 sm:mb-10 animate-fade-in-down">
            <div class="bg-white rounded-2xl sm:rounded-[2.5rem] p-4 sm:p-8 shadow-xl border border-blue-100 relative overflow-hidden">
                <h2 class="text-lg sm:text-xl font-black text-slate-800 mb-4 sm:mb-8 flex items-center gap-2.5 sm:gap-3"><i class="fas fa-calendar-plus text-blue-600"></i> Nueva Competición</h2>
                <form action="competiciones_code.php" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-4 sm:gap-6">
                    <div class="md:col-span-12 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Nombre del Evento</label>
                        <input type="text" name="nombre" required class="w-full px-4 sm:px-5 py-3 sm:py-3.5 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-100 text-sm font-bold shadow-inner">
                    </div>
                    <div class="md:col-span-8 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Sede / Municipio</label>
                        <input type="text" name="lugar" required class="w-full px-4 sm:px-5 py-3 sm:py-3.5 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-100 text-sm font-bold shadow-inner">
                    </div>
                    <div class="md:col-span-4 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Fecha</label>
                        <input type="date" name="fecha" required class="w-full px-4 sm:px-5 py-3 sm:py-3.5 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-100 text-sm font-bold">
                    </div>
                    <div class="md:col-span-12 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Enlace Google Maps</label>
                        <input type="url" name="maps" placeholder="https://goo.gl/maps/..." class="w-full px-4 sm:px-5 py-3 sm:py-3.5 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-100 text-sm font-bold shadow-inner">
                    </div>
                    <div class="md:col-span-12 pt-4 sm:pt-6 flex justify-end gap-3 sm:gap-4 items-center">
                        <button type="button" onclick="toggleAddCompPanel()" class="px-4 py-2.5 text-xs font-black uppercase text-slate-400 hover:text-slate-600">Cancelar</button>
                        <button type="submit" name="save_btn" class="px-6 sm:px-10 py-3 sm:py-3.5 bg-blue-600 text-white font-black uppercase text-xs tracking-wider rounded-xl sm:rounded-2xl shadow-lg hover:bg-blue-700 active:scale-[0.98] transition-all">Crear</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- DASHBOARD: KPIs ESTILO "TANTAS/CUANTAS" -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 sm:gap-6 mb-6 sm:mb-10">
            <!-- Tipología de Eventos -->
            <div class="sm:col-span-2 bg-white p-4 sm:p-7 rounded-2xl sm:rounded-[2rem] shadow-sm border border-slate-200/80 border-l-[6px] border-l-blue-600 group hover:shadow-md transition-all">
                <div class="flex justify-between items-start mb-4 sm:mb-6">
                    <div>
                        <p class="text-[9px] font-black uppercase text-slate-400 tracking-wider mb-1">Distribución Técnica</p>
                        <h3 class="text-xl sm:text-3xl font-black text-slate-800 tabular-nums">
                            <?php echo $total_tecnico; ?> <span class="text-xs sm:text-sm text-slate-300 uppercase tracking-wider">Competiciones</span>
                        </h3>
                    </div>
                    <div class="flex flex-wrap justify-end gap-1.5 sm:gap-2 max-w-[200px]">
                        <span class="px-2 py-0.5 bg-blue-50 text-blue-600 text-[8px] font-black rounded-lg border border-blue-100 uppercase tabular-nums"><?php echo $n_figuras; ?> Figuras</span>
                        <span class="px-2 py-0.5 bg-purple-50 text-purple-600 text-[8px] font-black rounded-lg border border-purple-100 uppercase tabular-nums"><?php echo $n_rutinas; ?> Rutinas</span>
                        <span class="px-2 py-0.5 bg-amber-50 text-amber-600 text-[8px] font-black rounded-lg border border-amber-100 uppercase tabular-nums"><?php echo $n_pases; ?> Pases</span>
                    </div>
                </div>
                <div class="flex h-3.5 sm:h-4 bg-slate-100 rounded-full overflow-hidden mb-3 sm:mb-4 shadow-inner border border-slate-200/50 p-0.5">
                    <div class="h-full bg-blue-500 shadow-sm transition-all duration-1000 flex items-center justify-center text-[8px] font-black text-white rounded-l-full tabular-nums" style="width: <?php echo $pct_figuras; ?>%"><?php echo $pct_figuras > 10 ? $pct_figuras.'%' : ''; ?></div>
                    <div class="h-full bg-purple-500 shadow-sm transition-all duration-1000 flex items-center justify-center text-[8px] font-black text-white tabular-nums" style="width: <?php echo $pct_rutinas; ?>%"><?php echo $pct_rutinas > 10 ? $pct_rutinas.'%' : ''; ?></div>
                    <div class="h-full bg-amber-500 shadow-sm transition-all duration-1000 flex items-center justify-center text-[8px] font-black text-white rounded-r-full tabular-nums" style="width: <?php echo $pct_pases; ?>%"><?php echo $pct_pases > 10 ? $pct_pases.'%' : ''; ?></div>
                </div>
                <p class="text-[9px] font-bold text-slate-400 uppercase italic">Clasificación: Rutinas, Figuras técnicas y Pases de Nivel.</p>
            </div>

            <!-- Recientes -->
            <div class="bg-white p-4 sm:p-7 rounded-2xl sm:rounded-[2rem] shadow-sm border border-slate-200/80 border-l-[6px] border-l-purple-500 group hover:shadow-md transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-wider mb-1">Actividad</p>
                <h3 class="text-xl sm:text-2xl font-black text-purple-600 tabular-nums"><?php echo $stats['temporada']; ?></h3>
                <p class="text-[10px] font-bold text-slate-400 mt-1 sm:mt-2 uppercase italic">Eventos esta temporada</p>
            </div>

            <!-- Sedes -->
            <div class="bg-white p-4 sm:p-7 rounded-2xl sm:rounded-[2rem] shadow-sm border border-slate-200/80 border-l-[6px] border-l-amber-500 group hover:shadow-md transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-wider mb-1">Cobertura</p>
                <h3 class="text-xl sm:text-2xl font-black text-amber-600 tabular-nums"><?php echo $stats['sedes']; ?></h3>
                <p class="text-[10px] font-bold text-slate-400 mt-1 sm:mt-2 uppercase italic">Municipios sede</p>
            </div>
        </div>

        <!-- Script y Estilos Anti-Parpadeo (FOUC) para Vista Persistente -->
        <script>
        (function() {
            try {
                var v = localStorage.getItem('sincrm_competiciones_view');
                if (v === 'list') {
                    document.documentElement.classList.add('comp-view-list');
                }
            } catch(e) {}
        })();
        </script>
        <style>
            html.comp-view-list #compGrid { display: none !important; }
            html.comp-view-list #compList { display: block !important; }
        </style>

        <?php
        $query = "SELECT * FROM competiciones ORDER BY fecha DESC, id DESC";
        $query_run = mysqli_query($connection, $query); 
        $meses_es = ['Jan'=>'ENE','Feb'=>'FEB','Mar'=>'MAR','Apr'=>'ABR','May'=>'MAY','Jun'=>'JUN','Jul'=>'JUL','Aug'=>'AGO','Sep'=>'SEP','Oct'=>'OCT','Nov'=>'NOV','Dec'=>'DIC'];
        $competiciones = [];
        while ($r = mysqli_fetch_assoc($query_run)) {
            $competiciones[] = $r;
        }
        ?>

        <!-- VISTA 1: CUADRÍCULA / MOSAICO DE TARJETAS -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-8" id="compGrid">
            <?php
            foreach ($competiciones as $row):
                $is_active = ($row['activo'] == 'si');
                $dia = date("d", strtotime($row['fecha']));
                $mes_raw = date("M", strtotime($row['fecha']));
                $mes = $meses_es[$mes_raw] ?? $mes_raw;
                $año = date("Y", strtotime($row['fecha']));
                $color = (!empty($row['color'])) ? $row['color'] : '#3b82f6';
                
                // Lógica de detección de Pase de Nivel por nombre
                $es_pase_nivel = (stripos($row['nombre'], 'pase') !== false && stripos($row['nombre'], 'nivel') !== false);
                $tipo_search = $es_pase_nivel ? 'pase de nivel' : ($row['figuras'] == 'mixta' ? 'mixta' : ($row['figuras'] == 'si' ? 'figuras' : 'rutinas'));
                $search_text = htmlspecialchars(strtolower($row['nombre'] . ' ' . $row['lugar'] . ' ' . $row['id'] . ' ' . $dia . ' ' . $mes . ' ' . $año . ' ' . $tipo_search));
            ?>
            <div class="comp-card bg-white rounded-2xl sm:rounded-[2.5rem] p-4 sm:p-8 shadow-sm border border-slate-200/80 group hover:shadow-lg transition-all relative flex flex-col border-t-[8px] sm:border-t-[10px] <?php echo !$is_active ? 'grayscale-[0.5] opacity-80' : ''; ?>" 
                 style="border-top-color: <?php echo $color; ?>;"
                 data-search="<?php echo $search_text; ?>">
                
                <!-- ID Flotante -->
                <div class="absolute top-3 right-4 sm:top-4 sm:right-8 text-[10px] font-black text-slate-300 italic tracking-wider uppercase tabular-nums">ID: #<?php echo $row['id']; ?></div>

                <div class="flex justify-between items-start mb-4 sm:mb-8 mt-1 sm:mt-2">
                    <div class="flex items-start gap-3 sm:gap-5 min-w-0 flex-1 pr-12 sm:pr-14">
                        <!-- Fecha en Español + Año -->
                        <div class="flex flex-col items-center justify-center w-14 h-14 sm:w-20 sm:h-20 rounded-xl sm:rounded-[1.8rem] text-white shadow-md transition-transform group-hover:scale-105 duration-300 shrink-0" style="background-color: <?php echo $color; ?>; box-shadow: 0 8px 20px -4px <?php echo $color; ?>44;">
                            <span class="text-[8px] sm:text-[9px] font-black uppercase tracking-wider mb-0.5 opacity-80"><?php echo $mes; ?></span>
                            <span class="text-lg sm:text-2xl font-black leading-none tabular-nums"><?php echo $dia; ?></span>
                            <span class="text-[8px] sm:text-[9px] font-black mt-0.5 opacity-80 tabular-nums"><?php echo $año; ?></span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-sm sm:text-lg lg:text-xl font-black text-slate-800 leading-snug group-hover:text-blue-600 transition-colors uppercase tracking-tight italic break-words"><?php echo $row['nombre']; ?></h3>
                            <p class="text-xs font-bold text-slate-400 flex items-center gap-1.5 mt-1 sm:mt-2 uppercase tracking-tight"><i class="fas fa-location-dot" style="color: <?php echo $color; ?>;"></i> <?php echo $row['lugar']; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Badges de Tipo (Lógica Inteligente) -->
                <div class="flex flex-wrap gap-1.5 sm:gap-2 mb-4 sm:mb-8">
                    <?php if($es_pase_nivel): ?>
                        <span class="px-3 sm:px-4 py-1 sm:py-1.5 bg-amber-500 text-white text-[8px] sm:text-[9px] font-black rounded-lg sm:rounded-xl uppercase tracking-wider shadow-xs italic">Pase de Nivel</span> 
                    <?php else: ?>
                        <?php if($row['figuras'] == 'mixta'): ?>
                            <span class="px-3 sm:px-4 py-1 sm:py-1.5 bg-gradient-to-r from-blue-600 to-purple-600 text-white text-[8px] sm:text-[9px] font-black rounded-lg sm:rounded-xl uppercase tracking-wider shadow-xs italic">Mixta (Figuras + Rutinas)</span>
                        <?php elseif($row['figuras'] == 'si'): ?> 
                            <span class="px-3 sm:px-4 py-1 sm:py-1.5 bg-blue-500 text-white text-[8px] sm:text-[9px] font-black rounded-lg sm:rounded-xl uppercase tracking-wider shadow-xs italic">Figuras</span> 
                        <?php elseif($row['figuras'] == 'no' || $row['niveles'] == 'si'): ?> 
                            <span class="px-3 sm:px-4 py-1 sm:py-1.5 bg-purple-500 text-white text-[8px] sm:text-[9px] font-black rounded-lg sm:rounded-xl uppercase tracking-wider shadow-xs italic">Rutinas</span> 
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php if($row['no_federado'] == 'si'): ?> 
                        <span class="px-3 sm:px-4 py-1 sm:py-1.5 bg-rose-500 text-white text-[8px] sm:text-[9px] font-black rounded-lg sm:rounded-xl uppercase tracking-wider shadow-xs italic">Escolar</span> 
                    <?php endif; ?>
                </div>

                <!-- Botonera Acciones "Colorinchi" -->
                <div class="mt-auto flex items-center justify-between pt-4 sm:pt-6 border-t border-slate-100">
                    <form action="competiciones_code.php" method="POST">
                        <input type="hidden" name="activar_id" value="<?php echo $row['id']; ?>">
                        <button type="submit" name="activar_btn" class="flex items-center gap-2 sm:gap-3 group/btn no-underline active:scale-[0.98]">
                            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl sm:rounded-2xl <?php echo $is_active ? 'bg-emerald-500 text-white shadow-emerald-500/30' : 'bg-slate-100 text-slate-400'; ?> flex items-center justify-center transition-all group-hover/btn:scale-105 shadow-sm"><i class="fas <?php echo $is_active ? 'fa-toggle-on text-base sm:text-xl' : 'fa-toggle-off text-base sm:text-xl'; ?>"></i></div>
                            <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-wider <?php echo $is_active ? 'text-emerald-600' : 'text-slate-400'; ?>"><?php echo $is_active ? 'Activa' : 'Pausada'; ?></span>
                        </button>
                    </form>

                    <div class="flex gap-2 sm:gap-3">
                        <!-- Configuración -->
                        <form action="competiciones_edit.php" method="POST">
                            <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="edit_btn" class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl sm:rounded-2xl bg-amber-50 text-amber-500 hover:bg-amber-500 hover:text-white active:scale-[0.98] transition-all border border-amber-100 flex items-center justify-center shadow-xs" title="Configurar Competición">
                                <i class="fas fa-sliders text-xs sm:text-sm"></i>
                            </button>
                        </form>
                        
                        <!-- Eliminar -->
                        <form action="competiciones_code.php" method="POST" onsubmit="return confirm('¿Borrar permanentemente esta competición?');">
                            <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="delete_btn" class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl sm:rounded-2xl bg-red-50 text-red-400 hover:bg-red-500 hover:text-white active:scale-[0.98] transition-all border border-red-100 flex items-center justify-center shadow-xs" title="Eliminar Competición">
                                <i class="fas fa-trash-can text-xs sm:text-sm"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <div id="compGridNoResults" class="col-span-full p-12 text-center text-slate-400 font-bold italic hidden">No se encontraron competiciones que coincidan con la búsqueda.</div>
        </div>

        <!-- VISTA 2: LISTA DETALLADA (TABLA / FILAS) -->
        <div id="compList" class="bg-white rounded-2xl sm:rounded-[2.5rem] shadow-sm border border-slate-200/80 overflow-hidden hidden animate-fade-in">
            
            <!-- Vista Móvil: Cards en Lista Compacta (md:hidden) -->
            <div class="md:hidden divide-y divide-slate-100" id="compListMobileContainer">
                <?php
                foreach ($competiciones as $row):
                    $is_active = ($row['activo'] == 'si');
                    $dia = date("d", strtotime($row['fecha']));
                    $mes_raw = date("M", strtotime($row['fecha']));
                    $mes = $meses_es[$mes_raw] ?? $mes_raw;
                    $año = date("Y", strtotime($row['fecha']));
                    $color = (!empty($row['color'])) ? $row['color'] : '#3b82f6';
                    $es_pase_nivel = (stripos($row['nombre'], 'pase') !== false && stripos($row['nombre'], 'nivel') !== false);
                    $tipo_search = $es_pase_nivel ? 'pase de nivel' : ($row['figuras'] == 'mixta' ? 'mixta' : ($row['figuras'] == 'si' ? 'figuras' : 'rutinas'));
                    $search_text = htmlspecialchars(strtolower($row['nombre'] . ' ' . $row['lugar'] . ' ' . $row['id'] . ' ' . $dia . ' ' . $mes . ' ' . $año . ' ' . $tipo_search));
                ?>
                <div class="comp-list-row p-4 hover:bg-slate-50/70 transition-colors <?php echo !$is_active ? 'grayscale-[0.5] opacity-75' : ''; ?>"
                     data-search="<?php echo $search_text; ?>">
                    <div class="flex items-start justify-between gap-3 mb-2.5">
                        <div class="flex items-start gap-3 min-w-0 flex-1">
                            <!-- Barra Indicadora de Color -->
                            <div class="w-1.5 self-stretch rounded-full shrink-0 my-0.5" style="background-color: <?php echo $color; ?>;"></div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest tabular-nums">#<?php echo $row['id']; ?></span>
                                    <span class="text-[10px] font-black text-slate-600 uppercase tracking-wider tabular-nums"><?php echo "$dia $mes $año"; ?></span>
                                </div>
                                <h4 class="text-xs sm:text-sm font-black text-slate-800 leading-snug uppercase italic truncate mt-0.5"><?php echo $row['nombre']; ?></h4>
                                <p class="text-[11px] font-bold text-slate-400 flex items-center gap-1.5 mt-1 uppercase truncate">
                                    <i class="fas fa-location-dot text-[9px]" style="color: <?php echo $color; ?>;"></i>
                                    <span><?php echo $row['lugar']; ?></span>
                                    <?php if(!empty($row['maps'])): ?>
                                        <a href="<?php echo htmlspecialchars($row['maps']); ?>" target="_blank" rel="noopener noreferrer" class="text-blue-500 hover:text-blue-700 ml-1" title="Ver en Google Maps"><i class="fas fa-arrow-up-right-from-square text-[9px]"></i></a>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="flex items-center gap-1.5 shrink-0">
                            <form action="competiciones_edit.php" method="POST" class="inline">
                                <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="edit_btn" class="w-8 h-8 rounded-xl bg-amber-50 text-amber-500 border border-amber-100 hover:bg-amber-500 hover:text-white active:scale-[0.98] transition-all flex items-center justify-center shadow-2xs" title="Configurar">
                                    <i class="fas fa-sliders text-xs"></i>
                                </button>
                            </form>
                            <form action="competiciones_code.php" method="POST" onsubmit="return confirm('¿Borrar permanentemente esta competición?');" class="inline">
                                <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="delete_btn" class="w-8 h-8 rounded-xl bg-red-50 text-red-400 border border-red-100 hover:bg-red-500 hover:text-white active:scale-[0.98] transition-all flex items-center justify-center shadow-2xs" title="Eliminar">
                                    <i class="fas fa-trash-can text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Pie de Tarjeta Móvil: Badges + Toggle Estado -->
                    <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-slate-100 mt-2">
                        <div class="flex flex-wrap gap-1.5">
                            <?php if($es_pase_nivel): ?>
                                <span class="px-2 py-0.5 bg-amber-500 text-white text-[8px] font-black rounded-lg uppercase tracking-wider italic">Pase de Nivel</span> 
                            <?php else: ?>
                                <?php if($row['figuras'] == 'mixta'): ?>
                                    <span class="px-2 py-0.5 bg-gradient-to-r from-blue-600 to-purple-600 text-white text-[8px] font-black rounded-lg uppercase tracking-wider italic">Mixta</span>
                                <?php elseif($row['figuras'] == 'si'): ?> 
                                    <span class="px-2 py-0.5 bg-blue-500 text-white text-[8px] font-black rounded-lg uppercase tracking-wider italic">Figuras</span> 
                                <?php elseif($row['figuras'] == 'no' || $row['niveles'] == 'si'): ?> 
                                    <span class="px-2 py-0.5 bg-purple-500 text-white text-[8px] font-black rounded-lg uppercase tracking-wider italic">Rutinas</span> 
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if($row['no_federado'] == 'si'): ?> 
                                <span class="px-2 py-0.5 bg-rose-500 text-white text-[8px] font-black rounded-lg uppercase tracking-wider italic">Escolar</span> 
                            <?php endif; ?>
                        </div>

                        <form action="competiciones_code.php" method="POST">
                            <input type="hidden" name="activar_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="activar_btn" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg <?php echo $is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500 border border-slate-200'; ?> active:scale-[0.98] transition-all text-[9px] font-black uppercase tracking-wider">
                                <i class="fas <?php echo $is_active ? 'fa-toggle-on text-emerald-600' : 'fa-toggle-off text-slate-400'; ?>"></i>
                                <span><?php echo $is_active ? 'Activa' : 'Pausada'; ?></span>
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Vista Escritorio: Tabla Completa (hidden md:block) -->
            <div class="hidden md:block overflow-x-auto no-scrollbar">
                <table class="w-full text-left border-collapse" id="compTable">
                    <thead>
                        <tr class="bg-slate-50/70 border-b border-slate-100 text-slate-400 text-[10px] font-black uppercase tracking-[0.2em]">
                            <th class="px-6 py-4">Fecha / ID</th>
                            <th class="px-6 py-4">Competición y Sede</th>
                            <th class="px-6 py-4">Modalidad</th>
                            <th class="px-6 py-4 text-center w-36">Estado</th>
                            <th class="px-6 py-4 text-center w-28">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        <?php
                        foreach ($competiciones as $row):
                            $is_active = ($row['activo'] == 'si');
                            $dia = date("d", strtotime($row['fecha']));
                            $mes_raw = date("M", strtotime($row['fecha']));
                            $mes = $meses_es[$mes_raw] ?? $mes_raw;
                            $año = date("Y", strtotime($row['fecha']));
                            $color = (!empty($row['color'])) ? $row['color'] : '#3b82f6';
                            $es_pase_nivel = (stripos($row['nombre'], 'pase') !== false && stripos($row['nombre'], 'nivel') !== false);
                            $tipo_search = $es_pase_nivel ? 'pase de nivel' : ($row['figuras'] == 'mixta' ? 'mixta' : ($row['figuras'] == 'si' ? 'figuras' : 'rutinas'));
                            $search_text = htmlspecialchars(strtolower($row['nombre'] . ' ' . $row['lugar'] . ' ' . $row['id'] . ' ' . $dia . ' ' . $mes . ' ' . $año . ' ' . $tipo_search));
                        ?>
                        <tr class="comp-list-row hover:bg-slate-50/70 transition-colors group <?php echo !$is_active ? 'grayscale-[0.5] opacity-75' : ''; ?>"
                            data-search="<?php echo $search_text; ?>">
                            
                            <!-- Columna Fecha e ID -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-1.5 h-10 rounded-full shrink-0 shadow-2xs" style="background-color: <?php echo $color; ?>;"></div>
                                    <div class="flex flex-col">
                                        <span class="text-xs font-black text-slate-800 tabular-nums leading-none"><?php echo "$dia $mes $año"; ?></span>
                                        <span class="text-[9px] font-bold text-slate-400 tabular-nums uppercase mt-1">ID #<?php echo $row['id']; ?></span>
                                    </div>
                                </div>
                            </td>

                            <!-- Columna Competición y Lugar -->
                            <td class="px-6 py-4">
                                <div class="min-w-0 max-w-lg">
                                    <h4 class="text-xs sm:text-sm font-black text-slate-800 leading-snug group-hover:text-blue-600 transition-colors uppercase italic truncate" title="<?php echo htmlspecialchars($row['nombre']); ?>">
                                        <?php echo $row['nombre']; ?>
                                    </h4>
                                    <p class="text-[11px] font-bold text-slate-400 flex items-center gap-1.5 mt-1 uppercase tracking-tight truncate">
                                        <i class="fas fa-location-dot text-[10px]" style="color: <?php echo $color; ?>;"></i>
                                        <span><?php echo $row['lugar']; ?></span>
                                        <?php if(!empty($row['maps'])): ?>
                                            <a href="<?php echo htmlspecialchars($row['maps']); ?>" target="_blank" rel="noopener noreferrer" class="text-blue-500 hover:text-blue-700 ml-1 active:scale-95 transition-transform" title="Ver en Google Maps">
                                                <i class="fas fa-arrow-up-right-from-square text-[9px]"></i>
                                            </a>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </td>

                            <!-- Columna Modalidad / Badges -->
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <?php if($es_pase_nivel): ?>
                                        <span class="px-2.5 py-1 bg-amber-500 text-white text-[8px] font-black rounded-lg uppercase tracking-wider italic shadow-2xs">Pase de Nivel</span> 
                                    <?php else: ?>
                                        <?php if($row['figuras'] == 'mixta'): ?>
                                            <span class="px-2.5 py-1 bg-gradient-to-r from-blue-600 to-purple-600 text-white text-[8px] font-black rounded-lg uppercase tracking-wider italic shadow-2xs">Mixta</span>
                                        <?php elseif($row['figuras'] == 'si'): ?> 
                                            <span class="px-2.5 py-1 bg-blue-500 text-white text-[8px] font-black rounded-lg uppercase tracking-wider italic shadow-2xs">Figuras</span> 
                                        <?php elseif($row['figuras'] == 'no' || $row['niveles'] == 'si'): ?> 
                                            <span class="px-2.5 py-1 bg-purple-500 text-white text-[8px] font-black rounded-lg uppercase tracking-wider italic shadow-2xs">Rutinas</span> 
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    
                                    <?php if($row['no_federado'] == 'si'): ?> 
                                        <span class="px-2.5 py-1 bg-rose-500 text-white text-[8px] font-black rounded-lg uppercase tracking-wider italic shadow-2xs">Escolar</span> 
                                    <?php endif; ?>
                                </div>
                            </td>

                            <!-- Columna Estado -->
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <form action="competiciones_code.php" method="POST" class="inline-block">
                                    <input type="hidden" name="activar_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="activar_btn" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl <?php echo $is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border border-slate-200 hover:bg-slate-200'; ?> active:scale-[0.98] transition-all text-[10px] font-black uppercase tracking-wider shadow-2xs">
                                        <i class="fas <?php echo $is_active ? 'fa-toggle-on text-emerald-600 text-sm' : 'fa-toggle-off text-slate-400 text-sm'; ?>"></i>
                                        <span><?php echo $is_active ? 'Activa' : 'Pausada'; ?></span>
                                    </button>
                                </form>
                            </td>

                            <!-- Columna Acciones -->
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-2">
                                    <!-- Configuración -->
                                    <form action="competiciones_edit.php" method="POST" class="inline-block">
                                        <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="edit_btn" class="w-8 h-8 rounded-xl bg-amber-50 text-amber-500 hover:bg-amber-500 hover:text-white active:scale-[0.98] transition-all border border-amber-100 flex items-center justify-center shadow-2xs" title="Configurar Competición">
                                            <i class="fas fa-sliders text-xs"></i>
                                        </button>
                                    </form>
                                    
                                    <!-- Eliminar -->
                                    <form action="competiciones_code.php" method="POST" onsubmit="return confirm('¿Borrar permanentemente esta competición?');" class="inline-block">
                                        <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="delete_btn" class="w-8 h-8 rounded-xl bg-red-50 text-red-400 hover:bg-red-500 hover:text-white active:scale-[0.98] transition-all border border-red-100 flex items-center justify-center shadow-2xs" title="Eliminar Competición">
                                            <i class="fas fa-trash-can text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div id="compListNoResults" class="p-12 text-center text-slate-400 font-bold italic hidden">No se encontraron competiciones que coincidan con la búsqueda.</div>
        </div>
    </div>
</main>

<script>
const COMP_STORAGE_KEY = 'sincrm_competiciones_view';

function toggleAddCompPanel() { 
    document.getElementById('addCompPanel').classList.toggle('hidden'); 
}

function setCompView(view) {
    const gridEl = document.getElementById('compGrid');
    const listEl = document.getElementById('compList');
    const btnGrid = document.getElementById('viewBtnGrid');
    const btnList = document.getElementById('viewBtnList');

    if (!gridEl || !listEl || !btnGrid || !btnList) return;

    if (view === 'list') {
        document.documentElement.classList.add('comp-view-list');
        gridEl.classList.add('hidden');
        listEl.classList.remove('hidden');

        // Botón Lista Activo
        btnList.className = 'px-3 py-1.5 rounded-lg sm:rounded-xl text-xs font-black uppercase tracking-wider flex items-center gap-1.5 transition-all duration-200 active:scale-[0.98] bg-white text-blue-600 shadow-xs border border-slate-200/80';
        btnGrid.className = 'px-3 py-1.5 rounded-lg sm:rounded-xl text-xs font-bold uppercase tracking-wider flex items-center gap-1.5 transition-all duration-200 active:scale-[0.98] text-slate-500 hover:text-slate-800 border border-transparent';

        try { localStorage.setItem(COMP_STORAGE_KEY, 'list'); } catch(e) {}
        filterCompTable();
    } else {
        document.documentElement.classList.remove('comp-view-list');
        listEl.classList.add('hidden');
        gridEl.classList.remove('hidden');

        // Botón Mosaico Activo
        btnGrid.className = 'px-3 py-1.5 rounded-lg sm:rounded-xl text-xs font-black uppercase tracking-wider flex items-center gap-1.5 transition-all duration-200 active:scale-[0.98] bg-white text-blue-600 shadow-xs border border-slate-200/80';
        btnList.className = 'px-3 py-1.5 rounded-lg sm:rounded-xl text-xs font-bold uppercase tracking-wider flex items-center gap-1.5 transition-all duration-200 active:scale-[0.98] text-slate-500 hover:text-slate-800 border border-transparent';

        try { localStorage.setItem(COMP_STORAGE_KEY, 'grid'); } catch(e) {}
        filterCompTable();
    }
}

function filterCompTable() {
    const input = document.getElementById('compSearchInput');
    if (!input) return;
    const filter = input.value.toLowerCase().trim();
    
    // 1. Filtrar tarjetas en vista Mosaico
    const gridCards = document.querySelectorAll('#compGrid .comp-card');
    let gridMatches = 0;
    gridCards.forEach(card => {
        const text = (card.getAttribute('data-search') || card.textContent).toLowerCase();
        if (text.indexOf(filter) > -1) {
            card.style.display = "";
            gridMatches++;
        } else {
            card.style.display = "none";
        }
    });

    // 2. Filtrar filas en vista Lista (tanto móvil como tabla escritorio)
    const listRows = document.querySelectorAll('#compList .comp-list-row');
    let listMatches = 0;
    listRows.forEach(row => {
        const text = (row.getAttribute('data-search') || row.textContent).toLowerCase();
        if (text.indexOf(filter) > -1) {
            row.style.display = "";
            listMatches++;
        } else {
            row.style.display = "none";
        }
    });

    // Mensaje de sin resultados
    const noResultsGrid = document.getElementById('compGridNoResults');
    if (noResultsGrid) noResultsGrid.classList.toggle('hidden', gridMatches > 0 || gridCards.length === 0);

    const noResultsList = document.getElementById('compListNoResults');
    if (noResultsList) noResultsList.classList.toggle('hidden', listMatches > 0 || listRows.length === 0);
}

function initCompView() {
    let savedView = 'grid';
    try {
        savedView = localStorage.getItem(COMP_STORAGE_KEY) || 'grid';
    } catch(e) {}
    setCompView(savedView);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCompView);
} else {
    initCompView();
}
</script>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
