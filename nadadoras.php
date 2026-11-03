<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');

$condicion_club = '';
if(isset($_SESSION['club']) && $_SESSION['club'] > 0){
    $condicion_club = " AND n.club = ".$_SESSION['club'];
}

// 1. Cálculos de KPIs
$q_stats = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN n.activo = 1 THEN 1 ELSE 0 END) as activas,
            AVG(YEAR(CURRENT_DATE) - n.año_nacimiento) as edad_media,
            COUNT(DISTINCT n.club) as total_clubes
            FROM nadadoras n WHERE 1=1 $condicion_club";
$res_stats = mysqli_query($connection, $q_stats);
$stats = mysqli_fetch_assoc($res_stats);
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-surface">
    
    <?php include('includes/topbar.php'); ?>

    <!-- Contenido de la Página -->
    <div class="px-3 py-4 sm:p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend">
        
        <!-- Header de Sección -->
        <div class="mb-6 sm:mb-10 flex flex-col sm:flex-row sm:items-end justify-between gap-4 sm:gap-6">
            <div>
                <h1 class="text-2xl sm:text-4xl font-black text-slate-800 tracking-tighter mb-1 sm:mb-2 flex items-center gap-2.5 sm:gap-3">
                    <span class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-person-swimming text-base sm:text-lg"></i></span>
                    Nadadoras
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 font-medium">Gestión del censo de deportistas y licencias.</p>
            </div>
            <div class="flex gap-2 sm:gap-3">
                <button onclick="toggleAddNadadoraPanel()" class="w-full sm:w-auto px-5 sm:px-6 py-2.5 sm:py-3 bg-blue-600 text-white font-black uppercase text-xs tracking-wider rounded-xl sm:rounded-2xl shadow-lg shadow-blue-500/20 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-plus text-xs"></i> Nueva Nadadora
                </button>
            </div>
        </div>

        <?php include('includes/alertas_v4.php'); ?>

        <!-- PANEL DASHBOARD: KPIs -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-6 mb-6 sm:mb-10">
            <div class="bg-white p-3.5 sm:p-6 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200 border-l-4 sm:border-l-[6px] border-l-slate-400 group hover:shadow-md transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1 truncate">Censo Total</p>
                <h3 class="text-xl sm:text-2xl font-black text-slate-800 leading-none tabular-nums"><?php echo $stats['total']; ?></h3>
                <p class="text-[9px] sm:text-[10px] font-bold text-slate-300 mt-1.5 sm:mt-2 italic truncate">Nadadoras registradas</p>
            </div>
            <div class="bg-white p-3.5 sm:p-6 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200 border-l-4 sm:border-l-[6px] border-l-emerald-500 group hover:shadow-md transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1 truncate">En Activo</p>
                <h3 class="text-xl sm:text-2xl font-black text-emerald-600 leading-none tabular-nums"><?php echo $stats['activas']; ?></h3>
                <p class="text-[9px] sm:text-[10px] font-bold text-emerald-600/60 mt-1.5 sm:mt-2 uppercase truncate">Licencias Vigentes</p>
            </div>
            <div class="bg-white p-3.5 sm:p-6 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200 border-l-4 sm:border-l-[6px] border-l-blue-500 group hover:shadow-md transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1 truncate">Demográfico</p>
                <h3 class="text-xl sm:text-2xl font-black text-blue-600 leading-none tabular-nums"><?php echo round($stats['edad_media'], 1); ?> <span class="text-xs font-bold">años</span></h3>
                <p class="text-[9px] sm:text-[10px] font-bold text-blue-400 mt-1.5 sm:mt-2 uppercase truncate">Edad Promedio</p>
            </div>
            <div class="bg-white p-3.5 sm:p-6 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200 border-l-4 sm:border-l-[6px] border-l-purple-500 group hover:shadow-md transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1 truncate">Representación</p>
                <h3 class="text-xl sm:text-2xl font-black text-purple-600 leading-none tabular-nums"><?php echo $stats['total_clubes']; ?></h3>
                <p class="text-[9px] sm:text-[10px] font-bold text-purple-400 mt-1.5 sm:mt-2 uppercase truncate">Clubes Vinculados</p>
            </div>
        </div>

        <!-- Panel Añadir -->
        <div id="addNadadoraPanel" class="hidden mb-6 sm:mb-10 animate-fade-in-down">
            <div class="bg-white rounded-2xl sm:rounded-[2.5rem] p-4 sm:p-8 shadow-xl border border-blue-100 relative overflow-hidden">
                <h2 class="text-base sm:text-xl font-black text-slate-800 mb-4 sm:mb-8 flex items-center gap-2.5 sm:gap-3"><i class="fas fa-user-plus text-blue-600"></i> Alta de Deportista</h2>
                <form action="nadadoras_code.php" method="POST" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3 sm:gap-6">
                    <div class="sm:col-span-1 md:col-span-4 space-y-1 sm:space-y-2">
                        <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1">Nombre</label>
                        <input type="text" name="nombre" required class="w-full px-4 sm:px-5 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-xs sm:text-sm font-bold shadow-inner">
                    </div>
                    <div class="sm:col-span-1 md:col-span-8 space-y-1 sm:space-y-2">
                        <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1">Apellidos</label>
                        <input type="text" name="apellidos" required class="w-full px-4 sm:px-5 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-xs sm:text-sm font-bold shadow-inner">
                    </div>
                    <div class="sm:col-span-1 md:col-span-4 space-y-1 sm:space-y-2">
                        <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1">Licencia / NIF</label>
                        <input type="text" name="licencia" class="w-full px-4 sm:px-5 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-xs sm:text-sm font-bold">
                    </div>
                    <div class="sm:col-span-1 md:col-span-3 space-y-1 sm:space-y-2">
                        <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1">Año Nacimiento</label>
                        <?php 
                        ob_start(); include('./includes/año_select_option.php');
                        echo str_replace('<select', '<select name="año_nacimiento" class="v3-select-fix"', preg_replace('/<label.*?>.*?<\/label>/i', '', ob_get_clean()));
                        ?>
                    </div>
                    <div class="sm:col-span-2 md:col-span-5 space-y-1 sm:space-y-2">
                        <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1">Club</label>
                        <?php 
                        ob_start(); include('./includes/club_select_option.php');
                        echo str_replace('<select', '<select name="club" class="v3-select-fix"', preg_replace('/<label.*?>.*?<\/label>/i', '', ob_get_clean()));
                        ?>
                    </div>
                    <div class="sm:col-span-2 md:col-span-12 pt-4 sm:pt-6 border-t border-slate-50 flex items-center justify-end gap-3 sm:gap-4">
                        <button type="button" onclick="toggleAddNadadoraPanel()" class="text-xs font-black uppercase text-slate-400 px-3 py-2">Cancelar</button>
                        <button type="submit" name="save_btn" class="px-6 sm:px-10 py-2.5 sm:py-3 bg-blue-600 text-white font-black uppercase text-xs tracking-wider rounded-xl sm:rounded-2xl shadow-lg hover:bg-blue-700 active:scale-[0.98] transition-all">Guardar Nadadora</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Directorio de Nadadoras -->
        <div class="bg-white rounded-2xl sm:rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-4 sm:px-8 py-4 sm:py-6 bg-slate-50/50 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3 sm:gap-4">
                <h2 class="text-sm sm:text-lg font-black text-slate-800 uppercase tracking-tighter italic">Directorio de Nadadoras</h2>
                
                <!-- Buscador Dinámico -->
                <div class="relative w-full sm:w-80">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </span>
                    <input type="text" id="nadadoraSearchInput" placeholder="Buscar por nombre, año, licencia o club..." 
                           class="w-full pl-9 pr-3.5 py-2 sm:py-2.5 bg-white border border-slate-200 rounded-xl sm:rounded-2xl text-xs font-bold text-slate-700 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all outline-none"
                           onkeyup="filterNadadorasTable()">
                </div>
            </div>

            <?php
            $query = "SELECT n.id, n.licencia, n.apellidos, n.nombre, n.año_nacimiento, n.club, c.nombre_corto as club_nombre, n.activo 
                      FROM nadadoras n 
                      LEFT JOIN clubes c ON n.club = c.id 
                      WHERE 1=1 $condicion_club 
                      ORDER BY n.apellidos, n.nombre LIMIT 2000";
            $res = mysqli_query($connection, $query);
            $nadadoras_list = [];
            while ($row = mysqli_fetch_assoc($res)) {
                $nadadoras_list[] = $row;
            }
            ?>
            
            <!-- Vista Móvil: Cards Fluidas (md:hidden) -->
            <div class="md:hidden divide-y divide-slate-100" id="nadadorasMobileContainer">
                <?php if (empty($nadadoras_list)): ?>
                    <div class="p-8 text-center text-slate-400 text-xs font-bold uppercase tracking-widest">No hay nadadoras registradas</div>
                <?php else: ?>
                    <?php foreach ($nadadoras_list as $row): 
                        $is_active = ($row['activo'] == 1);
                    ?>
                    <div class="nadadora-mobile-card p-3.5 sm:p-4 hover:bg-slate-50/60 transition-colors <?php echo !$is_active ? 'opacity-40 bg-slate-50' : ''; ?>"
                         data-search="<?php echo htmlspecialchars(strtolower($row['apellidos'].' '.$row['nombre'].' '.$row['id'].' '.$row['año_nacimiento'].' '.$row['licencia'].' '.$row['club_nombre'])); ?>">
                        <div class="flex items-start justify-between gap-2.5 mb-2">
                            <div class="min-w-0 flex-1">
                                <h3 class="text-xs sm:text-sm font-black text-slate-800 leading-snug">
                                    <?php echo $row['apellidos']; ?>, <?php echo $row['nombre']; ?>
                                </h3>
                                <div class="flex flex-wrap items-center gap-1.5 mt-1">
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider tabular-nums">ID #<?php echo $row['id']; ?></span>
                                    <?php if (!$is_active): ?>
                                        <span class="px-1.5 py-0.5 bg-red-50 text-red-500 text-[8px] font-black rounded border border-red-100 uppercase">Inactiva</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <form action="nadadoras_edit.php" method="POST" class="inline">
                                    <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="edit_btn" class="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 hover:bg-emerald-50 hover:text-emerald-600 active:scale-[0.98] transition-all flex items-center justify-center">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                </form>
                                <form action="nadadoras_code.php" method="POST" onsubmit="return confirm('¿Eliminar?');" class="inline">
                                    <input type="hidden" name="id_nadadora" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="delete_btn" class="w-8 h-8 rounded-lg bg-slate-100 text-slate-400 hover:bg-red-50 hover:text-red-600 active:scale-[0.98] transition-all flex items-center justify-center">
                                        <i class="fas fa-trash-can text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-1.5 pt-1 text-[10px]">
                            <span class="px-2 py-0.5 bg-blue-50 text-blue-600 font-black rounded-md uppercase tracking-wider text-[9px]">
                                <?php echo $row['club_nombre'] ?: 'Sin club'; ?>
                            </span>
                            <span class="px-2 py-0.5 bg-slate-100 text-slate-600 font-bold rounded-md tabular-nums">
                                <i class="fas fa-cake-candles text-[8px] mr-1 text-slate-400"></i><?php echo $row['año_nacimiento'] ?: 'S/D'; ?>
                            </span>
                            <?php if (!empty($row['licencia'])): ?>
                                <span class="px-2 py-0.5 bg-slate-50 text-slate-400 font-medium rounded-md tabular-nums">
                                    Lic: <?php echo $row['licencia']; ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Vista Escritorio: Tabla (hidden md:block) -->
            <div class="hidden md:block overflow-x-auto no-scrollbar">
                <table class="w-full text-left border-collapse" id="nadadorasTable">
                    <thead>
                        <tr class="bg-slate-50/20 text-slate-400 text-[10px] font-black uppercase tracking-[0.2em]">
                            <th class="px-8 py-4">Nadadora / Deportista</th>
                            <th class="px-4 py-4 text-center">Año</th>
                            <th class="px-4 py-4">Licencia</th>
                            <th class="px-4 py-4">Club</th>
                            <th class="px-4 py-4 text-center w-32">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($nadadoras_list as $row): 
                            $is_active = ($row['activo'] == 1);
                        ?>
                        <tr class="hover:bg-slate-50/50 transition-colors <?php echo !$is_active ? 'opacity-40 bg-slate-50' : ''; ?>">
                            <td class="px-8 py-4">
                                <p class="text-sm font-black text-slate-700 leading-tight"><?php echo $row['apellidos']; ?>, <?php echo $row['nombre']; ?></p>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5 tabular-nums">ID: #<?php echo $row['id']; ?></p>
                            </td>
                            <td class="px-4 py-4 text-center text-xs font-bold text-slate-500 tabular-nums"><?php echo $row['año_nacimiento']; ?></td>
                            <td class="px-4 py-4 text-xs font-medium text-slate-400"><?php echo $row['licencia'] ?: '-'; ?></td>
                            <td class="px-4 py-4">
                                <span class="px-3 py-1 bg-slate-100 text-slate-600 text-[9px] font-black rounded-lg uppercase tracking-tighter"><?php echo $row['club_nombre'] ?: 'N/A'; ?></span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <form action="nadadoras_edit.php" method="POST"><input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>"><button type="submit" name="edit_btn" class="w-9 h-9 rounded-xl bg-slate-50 text-slate-400 hover:bg-emerald-50 hover:text-emerald-600 hover:shadow-md transition-all border border-transparent hover:border-emerald-100 flex items-center justify-center"><i class="fas fa-edit text-sm"></i></button></form>
                                    <form action="nadadoras_code.php" method="POST" onsubmit="return confirm('¿Eliminar?');"><input type="hidden" name="id_nadadora" value="<?php echo $row['id']; ?>"><button type="submit" name="delete_btn" class="w-9 h-9 rounded-xl bg-slate-50 text-slate-300 hover:bg-red-50 hover:text-red-600 transition-all flex items-center justify-center border border-transparent hover:border-red-100 shadow-sm"><i class="fas fa-trash-can text-sm"></i></button></form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<script>
function toggleAddNadadoraPanel() { document.getElementById('addNadadoraPanel').classList.toggle('hidden'); }

function filterNadadorasTable() {
    const input = document.getElementById('nadadoraSearchInput');
    const filter = input.value.toLowerCase().trim();
    
    // Filtrar tabla escritorio
    const table = document.getElementById('nadadorasTable');
    if (table) {
        const tr = table.getElementsByTagName('tr');
        for (let i = 1; i < tr.length; i++) {
            let textContent = tr[i].textContent.toLowerCase();
            tr[i].style.display = textContent.indexOf(filter) > -1 ? "" : "none";
        }
    }
    
    // Filtrar cards móviles
    const mobileCards = document.querySelectorAll('.nadadora-mobile-card');
    mobileCards.forEach(card => {
        const searchAttr = card.getAttribute('data-search') || card.textContent.toLowerCase();
        card.style.display = searchAttr.indexOf(filter) > -1 ? "" : "none";
    });
}
</script>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
