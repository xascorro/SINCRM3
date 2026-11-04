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
                    <span class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shadow-xs border border-blue-100">
                        <i class="fas fa-person-swimming text-base sm:text-lg"></i>
                    </span>
                    Modalidades
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 font-medium italic">Gestión de tipos de rutinas, cupos de atletas y requerimientos técnicos.</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2.5 sm:gap-4 items-stretch sm:items-center">
                <!-- Buscador Dinámico -->
                <div class="relative w-full sm:w-80">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </span>
                    <input type="text" id="modalidadSearchInput" placeholder="Buscar por nombre, código..." 
                           class="w-full pl-9 pr-3.5 py-2.5 sm:py-3 bg-white border border-slate-200 rounded-xl sm:rounded-2xl text-xs font-bold text-slate-700 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all outline-none shadow-sm"
                           onkeyup="filterModalidadesTable()">
                </div>
                <button onclick="toggleAddModalidadPanel()" class="px-5 sm:px-6 py-2.5 sm:py-3 bg-blue-600 text-white font-black uppercase text-xs tracking-wider rounded-xl sm:rounded-2xl shadow-lg shadow-blue-500/20 hover:scale-105 active:scale-[0.98] transition-all flex items-center justify-center gap-2 shrink-0">
                    <i class="fas fa-plus text-xs"></i> Crear Modalidad
                </button>
            </div>
        </div>

        <!-- Panel Añadir Modalidad (Colapsable) -->
        <div id="addModalidadPanel" class="hidden mb-6 sm:mb-10 animate-fade-in-down">
            <div class="bg-white rounded-2xl sm:rounded-[2.5rem] p-4 sm:p-8 shadow-xl border border-blue-100 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                <h2 class="text-base sm:text-xl font-black text-slate-800 mb-4 sm:mb-8 flex items-center gap-2.5 sm:gap-3">
                    <i class="fas fa-plus-circle text-blue-600"></i> Nueva Definición de Modalidad
                </h2>
                <form action="modalidades_code.php" method="POST" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3 sm:gap-6">
                    <div class="sm:col-span-2 md:col-span-6 space-y-1 sm:space-y-2">
                        <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest italic">Denominación Oficial</label>
                        <input type="text" name="nombre" required class="w-full px-4 sm:px-5 py-2.5 sm:py-3.5 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-200 focus:border-blue-500 transition-all text-xs sm:text-sm font-bold text-slate-800 shadow-inner" placeholder="Ej: Dúo Mixto Libre">
                    </div>
                    <div class="sm:col-span-1 md:col-span-3 space-y-1 sm:space-y-2">
                        <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest italic">Cód. Corto</label>
                        <input type="text" name="nombre_corto" required maxlength="5" class="w-full px-4 sm:px-5 py-2.5 sm:py-3.5 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-200 focus:border-blue-500 transition-all text-xs sm:text-sm font-bold text-slate-800 uppercase shadow-inner" placeholder="DML">
                    </div>
                    <div class="sm:col-span-1 md:col-span-3 space-y-1 sm:space-y-2">
                        <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest italic">Orden Visual</label>
                        <input type="number" name="orden" required value="1" class="w-full px-4 sm:px-5 py-2.5 sm:py-3.5 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-200 focus:border-blue-500 transition-all text-xs sm:text-sm font-bold text-slate-800 shadow-inner">
                    </div>
                    <div class="sm:col-span-1 md:col-span-4 space-y-1 sm:space-y-2">
                        <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest italic">Nadadoras Titulares</label>
                        <input type="number" name="numero_participantes" required value="1" min="1" max="14" class="w-full px-4 sm:px-5 py-2.5 sm:py-3.5 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-200 focus:border-blue-500 transition-all text-xs sm:text-sm font-bold text-slate-800 shadow-inner" placeholder="1">
                        <span class="text-[9px] text-slate-400 px-1">Titulares en el agua</span>
                    </div>
                    <div class="sm:col-span-1 md:col-span-4 space-y-1 sm:space-y-2">
                        <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest italic">Máximo de Reservas</label>
                        <input type="number" name="numero_reservas" required value="1" min="0" max="4" class="w-full px-4 sm:px-5 py-2.5 sm:py-3.5 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-200 focus:border-blue-500 transition-all text-xs sm:text-sm font-bold text-slate-800 shadow-inner" placeholder="1">
                        <span class="text-[9px] text-slate-400 px-1">Reservas permitidas</span>
                    </div>
                    <div class="sm:col-span-2 md:col-span-4 space-y-1 sm:space-y-2">
                        <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest italic">¿Requiere Coach Card?</label>
                        <select name="coach_card" class="w-full px-4 sm:px-5 py-2.5 sm:py-3.5 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-200 focus:border-blue-500 transition-all text-xs sm:text-sm font-bold text-slate-800 shadow-inner">
                            <option value="si">Sí (Obligatoria)</option>
                            <option value="no">No requerida</option>
                        </select>
                        <span class="text-[9px] text-slate-400 px-1">Control técnico WA</span>
                    </div>
                    <div class="sm:col-span-2 md:col-span-12 pt-4 sm:pt-6 border-t border-slate-100 flex justify-end gap-3 sm:gap-4">
                        <button type="button" onclick="toggleAddModalidadPanel()" class="text-xs font-black uppercase text-slate-400 hover:text-slate-600 transition-colors italic px-3 py-2">Cancelar</button>
                        <button type="submit" name="save_btn" class="px-6 sm:px-10 py-2.5 sm:py-3.5 bg-blue-600 text-white font-black uppercase text-xs tracking-wider rounded-xl sm:rounded-2xl shadow-lg hover:bg-blue-700 active:scale-[0.98] transition-all">
                            Guardar Modalidad
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Alertas -->
        <?php include('includes/alertas_v4.php'); ?>

        <!-- KPIs de Modalidades -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-6 mb-6 sm:mb-10">
            <?php
            $q_stats = "SELECT 
                            COUNT(*) as total, 
                            SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as activas,
                            SUM(CASE WHEN coach_card = 'si' AND activo = 1 THEN 1 ELSE 0 END) as con_cc,
                            SUM(CASE WHEN numero_participantes >= 8 AND activo = 1 THEN 1 ELSE 0 END) as grupales
                        FROM modalidades";
            $res_stats = mysqli_query($connection, $q_stats);
            $stats = mysqli_fetch_assoc($res_stats);
            ?>
            <div class="bg-white p-3.5 sm:p-6 rounded-2xl sm:rounded-[2rem] shadow-sm border border-slate-200 border-l-4 sm:border-l-[6px] border-l-blue-600 group hover:shadow-md transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1 truncate">Total Modalidades</p>
                <h3 class="text-xl sm:text-2xl font-black text-slate-800 tabular-nums"><?php echo $stats['total'] ?? 0; ?></h3>
                <p class="text-[9px] sm:text-[10px] font-bold text-slate-400 mt-1 sm:mt-2 uppercase italic truncate">Registradas en sistema</p>
            </div>
            <div class="bg-white p-3.5 sm:p-6 rounded-2xl sm:rounded-[2rem] shadow-sm border border-slate-200 border-l-4 sm:border-l-[6px] border-l-emerald-500 group hover:shadow-md transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1 truncate">Activas</p>
                <h3 class="text-xl sm:text-2xl font-black text-emerald-600 tabular-nums"><?php echo $stats['activas'] ?? 0; ?></h3>
                <p class="text-[9px] sm:text-[10px] font-bold text-emerald-500 mt-1 sm:mt-2 uppercase italic truncate">Disponibles en eventos</p>
            </div>
            <div class="bg-white p-3.5 sm:p-6 rounded-2xl sm:rounded-[2rem] shadow-sm border border-slate-200 border-l-4 sm:border-l-[6px] border-l-indigo-500 group hover:shadow-md transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1 truncate">Con Coach Card</p>
                <h3 class="text-xl sm:text-2xl font-black text-indigo-600 tabular-nums"><?php echo $stats['con_cc'] ?? 0; ?></h3>
                <p class="text-[9px] sm:text-[10px] font-bold text-indigo-400 mt-1 sm:mt-2 uppercase italic truncate">Requieren tarjeta oficial</p>
            </div>
            <div class="bg-white p-3.5 sm:p-6 rounded-2xl sm:rounded-[2rem] shadow-sm border border-slate-200 border-l-4 sm:border-l-[6px] border-l-purple-500 group hover:shadow-md transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1 truncate">Modalidades Grupales</p>
                <h3 class="text-xl sm:text-2xl font-black text-purple-600 tabular-nums"><?php echo $stats['grupales'] ?? 0; ?></h3>
                <p class="text-[9px] sm:text-[10px] font-bold text-purple-400 mt-1 sm:mt-2 uppercase italic truncate">Equipos y Combos (≥8)</p>
            </div>
        </div>

        <?php
        $query = "SELECT * FROM modalidades ORDER BY orden ASC, id ASC";
        $res = mysqli_query($connection, $query);
        $modalidades_list = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $modalidades_list[] = $row;
        }
        ?>

        <!-- Catálogo de Modalidades -->
        <div class="bg-white rounded-2xl sm:rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden mb-10">
            <div class="px-4 sm:px-8 py-3.5 sm:py-6 bg-slate-50/50 border-b border-slate-100 flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <h2 class="text-sm sm:text-lg font-black text-slate-800 uppercase tracking-tighter italic">Catálogo de Modalidades</h2>
                    <span class="px-2 py-0.5 bg-blue-50 text-blue-700 text-[10px] font-black rounded-md border border-blue-100">WA Oficial</span>
                </div>
                <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest tabular-nums"><?php echo count($modalidades_list); ?> total</span>
            </div>
            
            <!-- Vista Móvil: Cards Fluidas (md:hidden) -->
            <div class="md:hidden divide-y divide-slate-100" id="modalidadesMobileContainer">
                <?php if (empty($modalidades_list)): ?>
                    <div class="p-8 text-center text-slate-400 text-xs font-bold uppercase tracking-widest">No hay modalidades registradas</div>
                <?php else: ?>
                    <?php foreach ($modalidades_list as $row): 
                        $is_active = ($row['activo'] == 1);
                        $has_cc = ($row['coach_card'] == 'si');
                    ?>
                    <div class="modalidad-mobile-card p-3.5 sm:p-4 hover:bg-slate-50/60 transition-colors <?php echo !$is_active ? 'opacity-40 grayscale bg-slate-50' : ''; ?>"
                         data-search="<?php echo htmlspecialchars(strtolower($row['orden'].' '.$row['nombre'].' '.$row['nombre_corto'].' '.$row['numero_participantes'].' '.$row['coach_card'])); ?>">
                        <div class="flex items-start justify-between gap-2.5 mb-2">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-black text-xs shadow-xs shrink-0">
                                    <?php echo strtoupper(substr($row['nombre_corto'] ?: $row['nombre'], 0, 2)); ?>
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[10px] font-black text-slate-300 italic tabular-nums">#<?php echo $row['orden']; ?></span>
                                        <h3 class="text-xs sm:text-sm font-black text-slate-800 uppercase tracking-tight truncate"><?php echo $row['nombre']; ?></h3>
                                    </div>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span class="px-2 py-0.5 bg-slate-100 text-slate-600 text-[9px] font-black rounded border border-slate-200 uppercase tracking-wider"><?php echo $row['nombre_corto']; ?></span>
                                        <?php if($has_cc): ?>
                                            <span class="px-1.5 py-0.5 bg-indigo-50 text-indigo-600 text-[8px] font-black rounded border border-indigo-100 uppercase">Coach Card</span>
                                        <?php endif; ?>
                                        <?php if($is_active): ?>
                                            <span class="px-1.5 py-0.5 bg-emerald-50 text-emerald-600 text-[8px] font-black rounded border border-emerald-100 uppercase">Activo</span>
                                        <?php else: ?>
                                            <span class="px-1.5 py-0.5 bg-rose-50 text-rose-600 text-[8px] font-black rounded border border-rose-100 uppercase">Inactivo</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <a href="modalidades_edit.php?id=<?php echo $row['id']; ?>" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 bg-white shadow-xs border border-slate-200 hover:text-blue-600 active:scale-[0.98] transition-all" title="Editar">
                                    <i class="fas fa-edit text-xs"></i>
                                </a>
                                <form action="modalidades_code.php" method="POST" onsubmit="return confirm('¿Desactivar o eliminar esta modalidad?');" class="inline">
                                    <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="delete_btn" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 bg-white shadow-xs border border-slate-200 hover:text-rose-600 active:scale-[0.98] transition-all" title="Eliminar / Desactivar">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 pt-1 text-[10px] tabular-nums">
                            <span class="px-2 py-0.5 bg-blue-50 text-blue-700 font-bold rounded-md">
                                <i class="fas fa-users text-[8px] mr-1 text-blue-400"></i>Titulares: <?php echo $row['numero_participantes']; ?>
                            </span>
                            <span class="px-2 py-0.5 bg-slate-100 text-slate-600 font-bold rounded-md">
                                <i class="fas fa-user-plus text-[8px] mr-1 text-slate-400"></i>Reservas: <?php echo $row['numero_reservas']; ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Vista Escritorio: Tabla (hidden md:block) -->
            <div class="hidden md:block overflow-x-auto no-scrollbar">
                <table class="w-full text-left border-collapse" id="modalidadesTable">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-400 text-[10px] font-black uppercase tracking-[0.2em] border-b border-slate-100">
                            <th class="px-6 py-4 w-16">ORD</th>
                            <th class="px-4 py-4">Denominación</th>
                            <th class="px-4 py-4 text-center">Cód. Corto</th>
                            <th class="px-4 py-4 text-center">Titulares</th>
                            <th class="px-4 py-4 text-center">Reservas</th>
                            <th class="px-4 py-4 text-center">Coach Card</th>
                            <th class="px-4 py-4 text-center">Estado</th>
                            <th class="px-6 py-4 text-center w-32">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($modalidades_list as $row): 
                            $is_active = ($row['activo'] == 1);
                            $has_cc = ($row['coach_card'] == 'si');
                        ?>
                        <tr class="hover:bg-slate-50/50 transition-colors <?php echo !$is_active ? 'opacity-40 grayscale bg-slate-50' : ''; ?>"
                            data-search="<?php echo htmlspecialchars(strtolower($row['orden'].' '.$row['nombre'].' '.$row['nombre_corto'].' '.$row['numero_participantes'].' '.$row['coach_card'])); ?>">
                            <td class="px-6 py-4 text-xs font-black text-slate-300 italic tabular-nums">#<?php echo $row['orden']; ?></td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-black text-xs shadow-xs shrink-0">
                                        <?php echo strtoupper(substr($row['nombre_corto'] ?: $row['nombre'], 0, 2)); ?>
                                    </div>
                                    <div>
                                        <span class="text-sm font-black text-slate-800 leading-tight uppercase italic tracking-tight block"><?php echo $row['nombre']; ?></span>
                                        <span class="text-[10px] text-slate-400 font-mono">ID #<?php echo $row['id']; ?></span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="px-2.5 py-1 bg-slate-100 text-slate-700 text-[10px] font-black rounded-lg border border-slate-200 uppercase tracking-widest"><?php echo $row['nombre_corto']; ?></span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="px-2.5 py-1 bg-blue-50 text-blue-700 text-xs font-black rounded-lg border border-blue-100 tabular-nums">
                                    <i class="fas fa-users text-[9px] mr-1 opacity-60"></i><?php echo $row['numero_participantes']; ?>
                                </span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="px-2.5 py-1 bg-slate-100 text-slate-600 text-xs font-bold rounded-lg border border-slate-200 tabular-nums">
                                    +<?php echo $row['numero_reservas']; ?>
                                </span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <?php if($has_cc): ?>
                                    <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 text-[9px] font-black rounded-full border border-indigo-200 uppercase tracking-tight">
                                        <i class="fas fa-check-circle text-[9px] mr-0.5"></i> Obligatoria
                                    </span>
                                <?php else: ?>
                                    <span class="px-2.5 py-1 bg-slate-100 text-slate-400 text-[9px] font-bold rounded-full border border-slate-200 uppercase tracking-tight">
                                        No Requerida
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <form action="modalidades_code.php" method="POST" class="inline">
                                    <input type="hidden" name="toggle_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="current_status" value="<?php echo $is_active ? 1 : 0; ?>">
                                    <button type="submit" name="toggle_status_btn" class="cursor-pointer transition-transform active:scale-95" title="Click para cambiar estado">
                                        <?php if($is_active): ?>
                                            <span class="px-3 py-1 bg-emerald-50 text-emerald-600 text-[9px] font-black rounded-full border border-emerald-100 uppercase tracking-tighter shadow-xs">Activo</span>
                                        <?php else: ?>
                                            <span class="px-3 py-1 bg-rose-50 text-rose-600 text-[9px] font-black rounded-full border border-rose-100 uppercase tracking-tighter shadow-xs">Inactivo</span>
                                        <?php endif; ?>
                                    </button>
                                </form>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="modalidades_edit.php?id=<?php echo $row['id']; ?>" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 bg-white shadow-xs border border-slate-200 hover:text-blue-600 hover:border-blue-300 transition-all active:scale-[0.98]" title="Editar">
                                        <i class="fas fa-edit text-xs"></i>
                                    </a>
                                    <form action="modalidades_code.php" method="POST" onsubmit="return confirm('¿Desactivar o eliminar esta modalidad?');" class="inline">
                                        <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="delete_btn" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 bg-white shadow-xs border border-slate-200 hover:text-rose-600 hover:border-rose-300 transition-all active:scale-[0.98]" title="Eliminar / Desactivar">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
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
function toggleAddModalidadPanel() { 
    const p = document.getElementById('addModalidadPanel');
    p.classList.toggle('hidden');
    if(!p.classList.contains('hidden')) p.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function filterModalidadesTable() {
    const input = document.getElementById('modalidadSearchInput');
    const filter = input.value.toLowerCase().trim();
    
    // Filtrar tabla escritorio
    const table = document.getElementById('modalidadesTable');
    if (table) {
        const tr = table.getElementsByTagName('tr');
        for (let i = 1; i < tr.length; i++) {
            let searchAttr = tr[i].getAttribute('data-search') || tr[i].textContent.toLowerCase();
            tr[i].style.display = searchAttr.indexOf(filter) > -1 ? "" : "none";
        }
    }
    
    // Filtrar cards móvil
    const mobileCards = document.querySelectorAll('.modalidad-mobile-card');
    mobileCards.forEach(card => {
        let searchAttr = card.getAttribute('data-search') || card.textContent.toLowerCase();
        card.style.display = searchAttr.indexOf(filter) > -1 ? "" : "none";
    });
}
</script>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
