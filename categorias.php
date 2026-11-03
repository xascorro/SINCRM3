<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-surface">
    
    <?php include('includes/topbar.php'); ?>

    <!-- Contenido de la Página -->
    <div class="px-3 py-4 sm:p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend">
        
        <!-- Header de Sección -->
        <div class="mb-6 sm:mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4 sm:gap-6">
            <div>
                <h1 class="text-2xl sm:text-4xl font-black text-slate-800 tracking-tighter mb-1 sm:mb-2 flex items-center gap-2.5 sm:gap-3">
                    <span class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-layer-group text-base sm:text-lg"></i></span>
                    Categorías
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 font-medium italic">Gestión de niveles y rangos de edad de competición.</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2.5 sm:gap-4 items-stretch sm:items-center">
                <!-- Buscador Dinámico -->
                <div class="relative w-full sm:w-80">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </span>
                    <input type="text" id="categoriaSearchInput" placeholder="Buscar por nombre, código..." 
                           class="w-full pl-9 pr-3.5 py-2.5 sm:py-3 bg-white border border-slate-200 rounded-xl sm:rounded-2xl text-xs font-bold text-slate-700 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all outline-none shadow-sm"
                           onkeyup="filterCategoriasTable()">
                </div>
                <button onclick="toggleAddCatPanel()" class="px-5 sm:px-6 py-2.5 sm:py-3 bg-blue-600 text-white font-black uppercase text-xs tracking-wider rounded-xl sm:rounded-2xl shadow-lg shadow-blue-500/20 hover:scale-105 active:scale-[0.98] transition-all flex items-center justify-center gap-2 shrink-0">
                    <i class="fas fa-plus text-xs"></i> Crear Categoría
                </button>
            </div>
        </div>

        <!-- Panel Añadir Categoría (Colapsable) -->
        <div id="addCatPanel" class="hidden mb-6 sm:mb-10 animate-fade-in-down">
            <div class="bg-white rounded-2xl sm:rounded-[2.5rem] p-4 sm:p-8 shadow-xl border border-blue-100 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                <h2 class="text-base sm:text-xl font-black text-slate-800 mb-4 sm:mb-8 flex items-center gap-2.5 sm:gap-3">
                    <i class="fas fa-plus-circle text-blue-600"></i> Nueva Definición de Nivel
                </h2>
                <form action="categorias_code.php" method="POST" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3 sm:gap-6">
                    <div class="sm:col-span-2 md:col-span-6 space-y-1 sm:space-y-2">
                        <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest italic">Nombre Completo</label>
                        <input type="text" name="nombre" required class="w-full px-4 sm:px-5 py-2.5 sm:py-3.5 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-xs sm:text-sm font-bold shadow-inner" placeholder="Ej: INFANTIL B">
                    </div>
                    <div class="sm:col-span-1 md:col-span-3 space-y-1 sm:space-y-2">
                        <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest italic">Cód. Corto</label>
                        <input type="text" name="nombre_corto" required maxlength="8" class="w-full px-4 sm:px-5 py-2.5 sm:py-3.5 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-xs sm:text-sm font-bold shadow-inner" placeholder="INF-B">
                    </div>
                    <div class="sm:col-span-1 md:col-span-3 space-y-1 sm:space-y-2">
                        <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest italic">Orden Visual</label>
                        <input type="number" name="orden" required value="1" class="w-full px-4 sm:px-5 py-2.5 sm:py-3.5 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-xs sm:text-sm font-bold shadow-inner">
                    </div>
                    <div class="sm:col-span-1 md:col-span-6 space-y-1 sm:space-y-2">
                        <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest italic">Edad Mínima</label>
                        <input type="number" name="edad_minima" required class="w-full px-4 sm:px-5 py-2.5 sm:py-3.5 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-xs sm:text-sm font-bold shadow-inner" placeholder="0">
                    </div>
                    <div class="sm:col-span-1 md:col-span-6 space-y-1 sm:space-y-2">
                        <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest italic">Edad Máxima</label>
                        <input type="number" name="edad_maxima" required class="w-full px-4 sm:px-5 py-2.5 sm:py-3.5 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-xs sm:text-sm font-bold shadow-inner" placeholder="99">
                    </div>
                    <div class="sm:col-span-2 md:col-span-12 pt-4 sm:pt-6 border-t border-slate-50 flex justify-end gap-3 sm:gap-4">
                        <button type="button" onclick="toggleAddCatPanel()" class="text-xs font-black uppercase text-slate-400 hover:text-slate-600 transition-colors italic px-3 py-2">Cancelar</button>
                        <button type="submit" name="save_btn" class="px-6 sm:px-10 py-2.5 sm:py-3.5 bg-blue-600 text-white font-black uppercase text-xs tracking-wider rounded-xl sm:rounded-2xl shadow-lg hover:bg-blue-700 active:scale-[0.98] transition-all">
                            Guardar Categoría
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Alertas -->
        <?php include('includes/alertas_v4.php'); ?>

        <!-- KPIs de Categorías -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-6 mb-6 sm:mb-10">
            <?php
            $q_c = "SELECT COUNT(*) as total, MIN(edad_minima) as min_e, MAX(edad_maxima) as max_e FROM categorias WHERE activo = 1";
            $res_c = mysqli_query($connection, $q_c);
            $stats = mysqli_fetch_assoc($res_c);
            ?>
            <div class="bg-white p-3.5 sm:p-6 rounded-2xl sm:rounded-[2rem] shadow-sm border border-slate-200 border-l-4 sm:border-l-[6px] border-l-slate-400 group hover:shadow-xl transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1 truncate">Censo Técnico</p>
                <h3 class="text-xl sm:text-2xl font-black text-slate-800 tabular-nums"><?php echo $stats['total'] ?? 0; ?></h3>
                <p class="text-[9px] sm:text-[10px] font-bold text-slate-400 mt-1 sm:mt-2 uppercase italic truncate">Categorías Activas</p>
            </div>
            <div class="bg-white p-3.5 sm:p-6 rounded-2xl sm:rounded-[2rem] shadow-sm border border-slate-200 border-l-4 sm:border-l-[6px] border-l-blue-500 group hover:shadow-xl transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1 truncate">Rango Edad</p>
                <h3 class="text-xl sm:text-2xl font-black text-blue-600 tabular-nums"><?php echo $stats['min_e'] ?? 0; ?> - <?php echo $stats['max_e'] ?? 0; ?></h3>
                <p class="text-[9px] sm:text-[10px] font-bold text-blue-400 mt-1 sm:mt-2 uppercase italic truncate">Años Permitidos</p>
            </div>
            <div class="bg-white p-3.5 sm:p-6 rounded-2xl sm:rounded-[2rem] shadow-sm border border-slate-200 border-l-4 sm:border-l-[6px] border-l-purple-500 group hover:shadow-xl transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1 truncate">Estructura</p>
                <h3 class="text-xl sm:text-2xl font-black text-purple-600 uppercase italic">Jerárquica</h3>
                <p class="text-[9px] sm:text-[10px] font-bold text-purple-400 mt-1 sm:mt-2 uppercase italic truncate">Según orden</p>
            </div>
            <div class="bg-white p-3.5 sm:p-6 rounded-2xl sm:rounded-[2rem] shadow-sm border border-slate-200 border-l-4 sm:border-l-[6px] border-l-amber-500 group hover:shadow-xl transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1 truncate">Estado</p>
                <h3 class="text-xl sm:text-2xl font-black text-amber-600 uppercase italic">Sincro</h3>
                <p class="text-[9px] sm:text-[10px] font-bold text-amber-400 mt-1 sm:mt-2 uppercase italic truncate">Base de datos v4</p>
            </div>
        </div>

        <?php
        $query = "SELECT * FROM categorias ORDER BY orden ASC, activo DESC";
        $res = mysqli_query($connection, $query);
        $categorias_list = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $categorias_list[] = $row;
        }
        ?>

        <!-- Catálogo de Niveles -->
        <div class="bg-white rounded-2xl sm:rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-4 sm:px-8 py-3.5 sm:py-6 bg-slate-50/50 border-b border-slate-100 flex justify-between items-center">
                <h2 class="text-sm sm:text-lg font-black text-slate-800 uppercase tracking-tighter italic">Catálogo de Niveles</h2>
                <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest tabular-nums"><?php echo count($categorias_list); ?> total</span>
            </div>
            
            <!-- Vista Móvil: Cards Fluidas (md:hidden) -->
            <div class="md:hidden divide-y divide-slate-100" id="categoriasMobileContainer">
                <?php if (empty($categorias_list)): ?>
                    <div class="p-8 text-center text-slate-400 text-xs font-bold uppercase tracking-widest">No hay categorías registradas</div>
                <?php else: ?>
                    <?php foreach ($categorias_list as $row): 
                        $is_active = $row['activo'] == 1;
                    ?>
                    <div class="categoria-mobile-card p-3.5 sm:p-4 hover:bg-slate-50/60 transition-colors <?php echo !$is_active ? 'opacity-40 grayscale bg-slate-50' : ''; ?>"
                         data-search="<?php echo htmlspecialchars(strtolower($row['orden'].' '.$row['nombre'].' '.$row['nombre_corto'].' '.$row['edad_minima'].' '.$row['edad_maxima'])); ?>">
                        <div class="flex items-start justify-between gap-2.5 mb-2">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <div class="w-8 h-8 rounded-xl <?php echo $is_active ? 'oceanic-gradient' : 'bg-slate-200'; ?> flex items-center justify-center text-white font-black text-xs shadow-sm shrink-0">
                                    <?php echo strtoupper(substr($row['nombre'],0,1)); ?>
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[10px] font-black text-slate-300 italic tabular-nums">#<?php echo $row['orden']; ?></span>
                                        <h3 class="text-xs sm:text-sm font-black text-slate-800 uppercase tracking-tight truncate"><?php echo $row['nombre']; ?></h3>
                                    </div>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span class="px-2 py-0.5 bg-slate-100 text-slate-600 text-[9px] font-black rounded border border-slate-200 uppercase tracking-wider"><?php echo $row['nombre_corto']; ?></span>
                                        <?php if($is_active): ?>
                                            <span class="px-1.5 py-0.5 bg-emerald-50 text-emerald-600 text-[8px] font-black rounded border border-emerald-100 uppercase">Activo</span>
                                        <?php else: ?>
                                            <span class="px-1.5 py-0.5 bg-red-50 text-red-600 text-[8px] font-black rounded border border-red-100 uppercase">Inactivo</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <form action="categorias_edit.php" method="POST" class="inline">
                                    <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="edit_btn" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 bg-white shadow-sm border border-slate-100 hover:text-emerald-500 active:scale-[0.98] transition-all"><i class="fas fa-edit text-xs"></i></button>
                                </form>
                                <form action="categorias_code.php" method="POST" onsubmit="return confirm('¿Eliminar/Desactivar?');" class="inline">
                                    <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="delete_btn" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 bg-white shadow-sm border border-slate-100 hover:text-red-500 active:scale-[0.98] transition-all"><i class="fas fa-trash text-xs"></i></button>
                                </form>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 pt-1 text-[10px]">
                            <span class="px-2 py-0.5 bg-blue-50 text-blue-600 font-bold rounded-md tabular-nums">
                                <i class="fas fa-calendar-alt text-[8px] mr-1 text-blue-400"></i>Edad: <?php echo $row['edad_minima']; ?> a <?php echo $row['edad_maxima']; ?> años
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Vista Escritorio: Tabla (hidden md:block) -->
            <div class="hidden md:block overflow-x-auto no-scrollbar">
                <table class="w-full text-left border-collapse" id="categoriasTable">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-400 text-[10px] font-black uppercase tracking-[0.2em]">
                            <th class="px-8 py-4 w-16">ORD</th>
                            <th class="px-4 py-4">Denominación</th>
                            <th class="px-4 py-4">Cód. Corto</th>
                            <th class="px-4 py-4 text-center">Edad Mín.</th>
                            <th class="px-4 py-4 text-center">Edad Máx.</th>
                            <th class="px-4 py-4 text-center">Estado</th>
                            <th class="px-4 py-4 text-center w-32">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($categorias_list as $row): 
                            $is_active = $row['activo'] == 1;
                        ?>
                        <tr class="hover:bg-slate-50/50 transition-colors <?php echo !$is_active ? 'opacity-40 grayscale bg-slate-50' : ''; ?>">
                            <td class="px-8 py-5 text-xs font-black text-slate-300 italic tabular-nums">#<?php echo $row['orden']; ?></td>
                            <td class="px-4 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-2xl <?php echo $is_active ? 'oceanic-gradient' : 'bg-slate-200'; ?> flex items-center justify-center text-white font-black text-xs shadow-md">
                                        <?php echo strtoupper(substr($row['nombre'],0,1)); ?>
                                    </div>
                                    <span class="text-sm font-black text-slate-700 leading-tight uppercase italic tracking-tighter"><?php echo $row['nombre']; ?></span>
                                </div>
                            </td>
                            <td class="px-4 py-5">
                                <span class="px-3 py-1 bg-slate-100 text-slate-600 text-[10px] font-black rounded-lg border border-slate-200 uppercase tracking-widest"><?php echo $row['nombre_corto']; ?></span>
                            </td>
                            <td class="px-4 py-5 text-center">
                                <span class="text-xs font-black text-slate-500 tabular-nums"><?php echo $row['edad_minima']; ?></span>
                            </td>
                            <td class="px-4 py-5 text-center">
                                <span class="text-xs font-black text-blue-500 tabular-nums"><?php echo $row['edad_maxima']; ?></span>
                            </td>
                            <td class="px-4 py-5 text-center">
                                <?php if($is_active): ?>
                                    <span class="px-3 py-1 bg-emerald-50 text-emerald-600 text-[9px] font-black rounded-full border border-emerald-100 uppercase tracking-tighter shadow-sm">Activo</span>
                                <?php else: ?>
                                    <span class="px-3 py-1 bg-red-50 text-red-600 text-[9px] font-black rounded-full border border-red-100 uppercase tracking-tighter shadow-sm">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-5">
                                <div class="flex items-center justify-center gap-2">
                                    <form action="categorias_edit.php" method="POST">
                                        <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="edit_btn" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 bg-white shadow-sm border border-slate-100 hover:text-emerald-500 hover:shadow-md transition-all"><i class="fas fa-edit"></i></button>
                                    </form>
                                    <form action="categorias_code.php" method="POST" onsubmit="return confirm('¿Eliminar/Desactivar?');">
                                        <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="delete_btn" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 bg-white shadow-sm border border-slate-100 hover:text-red-500 hover:shadow-md transition-all"><i class="fas fa-trash"></i></button>
                                    </form>
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
function toggleAddCatPanel() { document.getElementById('addCatPanel').classList.toggle('hidden'); }

function filterCategoriasTable() {
    const input = document.getElementById('categoriaSearchInput');
    const filter = input.value.toLowerCase().trim();
    
    // Filtrar tabla escritorio
    const table = document.getElementById('categoriasTable');
    if (table) {
        const tr = table.getElementsByTagName('tr');
        for (let i = 1; i < tr.length; i++) {
            let textContent = tr[i].textContent.toLowerCase();
            tr[i].style.display = textContent.indexOf(filter) > -1 ? "" : "none";
        }
    }
    
    // Filtrar cards móvil
    const mobileCards = document.querySelectorAll('.categoria-mobile-card');
    mobileCards.forEach(card => {
        const searchAttr = card.getAttribute('data-search') || card.textContent.toLowerCase();
        card.style.display = searchAttr.indexOf(filter) > -1 ? "" : "none";
    });
}
</script>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
