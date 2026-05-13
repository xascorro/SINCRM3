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

    <div class="p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend">
        
        <!-- Header -->
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6 text-primary">
            <div>
                <h1 class="text-4xl font-black text-slate-800 tracking-tighter mb-2 flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-flag-checkered text-lg"></i></span>
                    Competiciones
                </h1>
                <p class="text-slate-500 font-medium italic">Gestión del calendario y configuración de eventos regionales.</p>
            </div>
            <div class="flex flex-col md:flex-row gap-4 items-center">
                <!-- Buscador Dinámico -->
                <div class="relative w-full md:w-80">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </span>
                    <input type="text" id="compSearchInput" placeholder="Buscar por nombre, lugar o tipo..." 
                           class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl text-xs font-bold text-slate-700 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all outline-none shadow-sm"
                           onkeyup="filterCompTable()">
                </div>
                <button onclick="toggleAddCompPanel()" class="px-6 py-3 bg-blue-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
                    <i class="fas fa-plus text-xs"></i> Crear Evento
                </button>
            </div>
        </div>

        <?php if(isset($_SESSION['correcto'])): ?>
            <div class="mb-8 p-6 bg-white border-l-[6px] border-l-emerald-500 text-slate-700 rounded-r-3xl shadow-sm flex items-center gap-4 animate-fade-in">
                <div class="w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-500 shadow-sm"><i class="fas fa-check-circle text-xl"></i></div>
                <span class="text-base font-bold"><?php echo $_SESSION['correcto']; unset($_SESSION['correcto']); ?></span>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['estado'])): ?>
            <div class="mb-8 p-6 bg-white border-l-[6px] border-l-red-500 text-slate-700 rounded-r-3xl shadow-sm flex items-center gap-4 animate-fade-in">
                <div class="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center text-red-500 shadow-sm"><i class="fas fa-exclamation-triangle text-xl"></i></div>
                <span class="text-base font-bold"><?php echo $_SESSION['estado']; unset($_SESSION['estado']); ?></span>
            </div>
        <?php endif; ?>

        <!-- Panel Añadir -->
        <div id="addCompPanel" class="hidden mb-10 animate-fade-in-down">
            <div class="bg-white rounded-[2.5rem] p-8 shadow-xl border border-blue-100 relative overflow-hidden">
                <h2 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3"><i class="fas fa-calendar-plus text-blue-600"></i> Nueva Competición</h2>
                <form action="competiciones_code.php" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-6">
                    <div class="md:col-span-12 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Nombre del Evento</label>
                        <input type="text" name="nombre" required class="w-full px-5 py-3.5 rounded-2xl bg-slate-50 border border-slate-100 text-sm font-bold shadow-inner">
                    </div>
                    <div class="md:col-span-8 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Sede / Municipio</label>
                        <input type="text" name="lugar" required class="w-full px-5 py-3.5 rounded-2xl bg-slate-50 border border-slate-100 text-sm font-bold shadow-inner">
                    </div>
                    <div class="md:col-span-4 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Fecha</label>
                        <input type="date" name="fecha" required class="w-full px-5 py-3.5 rounded-2xl bg-slate-50 border border-slate-100 text-sm font-bold">
                    </div>
                    <div class="md:col-span-12 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Enlace Google Maps</label>
                        <input type="url" name="maps" placeholder="https://goo.gl/maps/..." class="w-full px-5 py-3.5 rounded-2xl bg-slate-50 border border-slate-100 text-sm font-bold shadow-inner">
                    </div>
                    <div class="md:col-span-12 pt-6 flex justify-end gap-4">
                        <button type="button" onclick="toggleAddCompPanel()" class="text-xs font-black uppercase text-slate-400">Cancelar</button>
                        <button type="submit" name="save_btn" class="px-10 py-3.5 bg-blue-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg hover:bg-blue-700 transition-all">Crear</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- DASHBOARD: KPIs ESTILO "TANTAS/CUANTAS" -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
            <!-- Tipología de Eventos -->
            <div class="md:col-span-2 bg-white p-7 rounded-[2rem] shadow-sm border border-slate-200 border-l-[6px] border-l-blue-600 group hover:shadow-xl transition-all">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Distribución Técnica</p>
                        <h3 class="text-3xl font-black text-slate-800">
                            <?php echo $total_tecnico; ?> <span class="text-sm text-slate-300 uppercase tracking-widest">Competiciones</span>
                        </h3>
                    </div>
                    <div class="flex flex-wrap justify-end gap-2 max-w-[200px]">
                        <span class="px-2 py-1 bg-blue-50 text-blue-600 text-[8px] font-black rounded-lg border border-blue-100 uppercase tracking-tighter shadow-sm whitespace-nowrap"><?php echo $n_figuras; ?> Figuras</span>
                        <span class="px-2 py-1 bg-purple-50 text-purple-600 text-[8px] font-black rounded-lg border border-purple-100 uppercase tracking-tighter shadow-sm whitespace-nowrap"><?php echo $n_rutinas; ?> Rutinas</span>
                        <span class="px-2 py-1 bg-amber-50 text-amber-600 text-[8px] font-black rounded-lg border border-amber-100 uppercase tracking-tighter shadow-sm whitespace-nowrap"><?php echo $n_pases; ?> Pases</span>
                    </div>
                </div>
                <div class="flex h-4 bg-slate-100 rounded-full overflow-hidden mb-4 shadow-inner border border-slate-200/50 p-0.5">
                    <div class="h-full bg-blue-500 shadow-lg shadow-blue-500/20 transition-all duration-1000 flex items-center justify-center text-[8px] font-black text-white rounded-l-full" style="width: <?php echo $pct_figuras; ?>%"><?php echo $pct_figuras > 10 ? $pct_figuras.'%' : ''; ?></div>
                    <div class="h-full bg-purple-500 shadow-lg shadow-purple-500/20 transition-all duration-1000 flex items-center justify-center text-[8px] font-black text-white" style="width: <?php echo $pct_rutinas; ?>%"><?php echo $pct_rutinas > 10 ? $pct_rutinas.'%' : ''; ?></div>
                    <div class="h-full bg-amber-500 shadow-lg shadow-amber-500/20 transition-all duration-1000 flex items-center justify-center text-[8px] font-black text-white rounded-r-full" style="width: <?php echo $pct_pases; ?>%"><?php echo $pct_pases > 10 ? $pct_pases.'%' : ''; ?></div>
                </div>
                <p class="text-[9px] font-bold text-slate-400 uppercase italic">Clasificación: Rutinas (sin figuras), Figuras (técnicas) y Pases de Nivel.</p>
            </div>

            <!-- Recientes -->
            <div class="bg-white p-7 rounded-[2rem] shadow-sm border border-slate-200 border-l-[6px] border-l-purple-500 group hover:shadow-xl transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Actividad</p>
                <h3 class="text-2xl font-black text-purple-600"><?php echo $stats['temporada']; ?></h3>
                <p class="text-[10px] font-bold text-slate-400 mt-2 uppercase italic">Eventos esta temporada</p>
            </div>

            <!-- Sedes -->
            <div class="bg-white p-7 rounded-[2rem] shadow-sm border border-slate-200 border-l-[6px] border-l-amber-500 group hover:shadow-xl transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Cobertura</p>
                <h3 class="text-2xl font-black text-amber-600"><?php echo $stats['sedes']; ?></h3>
                <p class="text-[10px] font-bold text-slate-400 mt-2 uppercase italic">Municipios sede</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" id="compGrid">
            <?php
            $query = "SELECT * FROM competiciones ORDER BY fecha DESC";
            $query_run = mysqli_query($connection, $query); 
            $meses_es = ['Jan'=>'ENE','Feb'=>'FEB','Mar'=>'MAR','Apr'=>'ABR','May'=>'MAY','Jun'=>'JUN','Jul'=>'JUL','Aug'=>'AGO','Sep'=>'SEP','Oct'=>'OCT','Nov'=>'NOV','Dec'=>'DIC'];
            
            while ($row = mysqli_fetch_assoc($query_run)):
                $is_active = ($row['activo'] == 'si');
                $dia = date("d", strtotime($row['fecha']));
                $mes_raw = date("M", strtotime($row['fecha']));
                $mes = $meses_es[$mes_raw] ?? $mes_raw;
                $año = date("Y", strtotime($row['fecha']));
                $color = (!empty($row['color'])) ? $row['color'] : '#3b82f6';
                
                // Lógica de detección de Pase de Nivel por nombre
                $es_pase_nivel = (stripos($row['nombre'], 'pase') !== false && stripos($row['nombre'], 'nivel') !== false);
            ?>
            <div class="comp-card bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-200 group hover:shadow-2xl hover:-translate-y-1 transition-all relative flex flex-col border-t-[10px] <?php echo !$is_active ? 'grayscale-[0.5] opacity-80' : ''; ?>" style="border-top-color: <?php echo $color; ?>;">
                
                <!-- ID Flotante -->
                <div class="absolute top-4 right-8 text-[10px] font-black text-slate-300 italic tracking-widest uppercase">ID: #<?php echo $row['id']; ?></div>

                <div class="flex justify-between items-start mb-8 mt-2">
                    <div class="flex items-center gap-5">
                        <!-- Fecha en Español + Año -->
                        <div class="flex flex-col items-center justify-center w-20 h-20 rounded-[1.8rem] text-white shadow-xl transition-transform group-hover:scale-110 duration-500 shrink-0" style="background-color: <?php echo $color; ?>; box-shadow: 0 10px 25px -5px <?php echo $color; ?>44;">
                            <span class="text-[9px] font-black uppercase tracking-[0.2em] mb-1 opacity-70"><?php echo $mes; ?></span>
                            <span class="text-2xl font-black leading-none"><?php echo $dia; ?></span>
                            <span class="text-[9px] font-black mt-1 opacity-70"><?php echo $año; ?></span>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-slate-800 leading-tight group-hover:text-blue-600 transition-colors uppercase tracking-tighter italic"><?php echo $row['nombre']; ?></h3>
                            <p class="text-xs font-bold text-slate-400 flex items-center gap-2 mt-2 uppercase tracking-tighter"><i class="fas fa-location-dot" style="color: <?php echo $color; ?>;"></i> <?php echo $row['lugar']; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Badges de Tipo (Lógica Inteligente) -->
                <div class="flex flex-wrap gap-2 mb-8">
                    <?php if($es_pase_nivel): ?>
                        <span class="px-4 py-1.5 bg-amber-500 text-white text-[9px] font-black rounded-xl uppercase tracking-widest shadow-lg shadow-amber-500/20 italic">Pase de Nivel</span> 
                    <?php else: ?>
                        <?php if($row['figuras'] == 'si'): ?> 
                            <span class="px-4 py-1.5 bg-blue-500 text-white text-[9px] font-black rounded-xl uppercase tracking-widest shadow-lg shadow-blue-500/20 italic">Figuras</span> 
                        <?php endif; ?>
                        <?php if($row['niveles'] == 'si'): ?> 
                            <span class="px-4 py-1.5 bg-purple-500 text-white text-[9px] font-black rounded-xl uppercase tracking-widest shadow-lg shadow-purple-500/20 italic">Rutinas</span> 
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php if($row['no_federado'] == 'si'): ?> 
                        <span class="px-4 py-1.5 bg-rose-500 text-white text-[9px] font-black rounded-xl uppercase tracking-widest shadow-lg shadow-rose-500/20 italic">Escolar</span> 
                    <?php endif; ?>
                </div>

                <!-- Botonera Acciones "Colorinchi" -->
                <div class="mt-auto flex items-center justify-between pt-6 border-t border-slate-50">
                    <form action="competiciones_code.php" method="POST">
                        <input type="hidden" name="activar_id" value="<?php echo $row['id']; ?>">
                        <button type="submit" name="activar_btn" class="flex items-center gap-3 group/btn no-underline">
                            <div class="w-10 h-10 rounded-2xl <?php echo $is_active ? 'bg-emerald-500 text-white shadow-emerald-500/30' : 'bg-slate-100 text-slate-400'; ?> flex items-center justify-center transition-all group-hover/btn:scale-110 shadow-lg"><i class="fas <?php echo $is_active ? 'fa-toggle-on text-xl' : 'fa-toggle-off text-xl'; ?>"></i></div>
                            <span class="text-[10px] font-black uppercase tracking-widest <?php echo $is_active ? 'text-emerald-600' : 'text-slate-400'; ?>"><?php echo $is_active ? 'Activa' : 'Pausada'; ?></span>
                        </button>
                    </form>

                    <div class="flex gap-3">
                        <!-- Configuración -->
                        <form action="competiciones_edit.php" method="POST">
                            <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="edit_btn" class="w-11 h-11 rounded-2xl bg-amber-50 text-amber-500 hover:bg-amber-500 hover:text-white hover:shadow-xl hover:shadow-amber-500/30 transition-all border border-amber-100 flex items-center justify-center">
                                <i class="fas fa-sliders text-sm"></i>
                            </button>
                        </form>
                        
                        <!-- Eliminar -->
                        <form action="competiciones_code.php" method="POST" onsubmit="return confirm('¿Borrar permanentemente esta competición?');">
                            <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="delete_btn" class="w-11 h-11 rounded-2xl bg-red-50 text-red-400 hover:bg-red-500 hover:text-white hover:shadow-xl hover:shadow-red-500/30 transition-all border border-red-100 flex items-center justify-center">
                                <i class="fas fa-trash-can text-sm"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</main>

<script>
function toggleAddCompPanel() { document.getElementById('addCompPanel').classList.toggle('hidden'); }

function filterCompTable() {
    const input = document.getElementById('compSearchInput');
    const filter = input.value.toLowerCase();
    const grid = document.getElementById('compGrid');
    const cards = grid.getElementsByClassName('comp-card');

    for (let i = 0; i < cards.length; i++) {
        let textContent = cards[i].textContent.toLowerCase();
        if (textContent.indexOf(filter) > -1) {
            cards[i].style.display = "";
        } else {
            cards[i].style.display = "none";
        }
    }
}
</script>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
