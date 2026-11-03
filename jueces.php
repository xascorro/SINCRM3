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
                    <span class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-gavel text-base sm:text-lg"></i></span>
                    Jueces
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 font-medium">Directorio técnico y licencias de arbitraje.</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2.5 sm:gap-4 items-stretch sm:items-center">
                <!-- Buscador Dinámico -->
                <div class="relative w-full sm:w-80">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </span>
                    <input type="text" id="juezSearchInput" placeholder="Buscar por nombre, licencia o federación..." 
                           class="w-full pl-9 pr-3.5 py-2.5 sm:py-3 bg-white border border-slate-200 rounded-xl sm:rounded-2xl text-xs font-bold text-slate-700 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all outline-none shadow-sm"
                           onkeyup="filterJuecesTable()">
                </div>
                <button onclick="toggleAddJuezPanel()" class="px-5 sm:px-6 py-2.5 sm:py-3 bg-blue-600 text-white font-black uppercase text-xs tracking-wider rounded-xl sm:rounded-2xl shadow-lg shadow-blue-500/20 hover:scale-105 active:scale-[0.98] transition-all flex items-center justify-center gap-2 shrink-0">
                    <i class="fas fa-plus text-xs"></i> Añadir Juez
                </button>
            </div>
        </div>

        <!-- Panel Añadir Juez (Colapsable) -->
        <div id="addJuezPanel" class="hidden mb-6 sm:mb-10 animate-fade-in-down">
            <div class="bg-white rounded-2xl sm:rounded-[2.5rem] p-4 sm:p-8 shadow-xl border border-blue-100 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                <h2 class="text-base sm:text-xl font-black text-slate-800 mb-4 sm:mb-8 flex items-center gap-2.5 sm:gap-3">
                    <i class="fas fa-user-plus text-blue-600"></i> Nuevo Juez
                </h2>
                <form action="jueces_code.php" method="POST" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3 sm:gap-6">
                    <div class="sm:col-span-1 md:col-span-4 space-y-1 sm:space-y-2">
                        <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1">Nombre</label>
                        <input type="text" name="nombre" required class="w-full px-4 sm:px-5 py-2.5 sm:py-3.5 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-xs sm:text-sm font-bold">
                    </div>
                    <div class="sm:col-span-1 md:col-span-8 space-y-1 sm:space-y-2">
                        <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1">Apellidos</label>
                        <input type="text" name="apellidos" required class="w-full px-4 sm:px-5 py-2.5 sm:py-3.5 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-xs sm:text-sm font-bold">
                    </div>
                    <div class="sm:col-span-1 md:col-span-4 space-y-1 sm:space-y-2">
                        <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1">Nº Licencia</label>
                        <input type="text" name="licencia" class="w-full px-4 sm:px-5 py-2.5 sm:py-3.5 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-xs sm:text-sm font-bold">
                    </div>
                    <div class="sm:col-span-1 md:col-span-8 space-y-1 sm:space-y-2">
                        <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1">Federación Autonómica</label>
                        <?php 
                        ob_start();
                        include('./includes/federacion_select_option.php');
                        echo str_replace('<select', '<select name="federacion" class="v3-select-fix"', preg_replace('/<label.*?>.*?<\/label>/i', '', ob_get_clean()));
                        ?>
                    </div>
                    <div class="sm:col-span-2 md:col-span-12 pt-4 sm:pt-6 border-t border-slate-50 flex justify-end gap-3 sm:gap-4">
                        <button type="button" onclick="toggleAddJuezPanel()" class="text-xs font-black uppercase text-slate-400 hover:text-slate-600 transition-colors px-3 py-2">Cancelar</button>
                        <button type="submit" name="save_btn" class="px-6 sm:px-10 py-2.5 sm:py-3.5 bg-blue-600 text-white font-black uppercase text-xs tracking-wider rounded-xl sm:rounded-2xl shadow-lg hover:bg-blue-700 active:scale-[0.98] transition-all">Guardar Ficha</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Alertas -->
        <?php include('includes/alertas_v4.php'); ?>

        <!-- KPIs -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 sm:gap-6 mb-6 sm:mb-10">
            <?php
            $q_j = "SELECT COUNT(*) as total, SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as activos, COUNT(DISTINCT federacion) as feds FROM jueces";
            $res_j = mysqli_query($connection, $q_j);
            $stats = mysqli_fetch_assoc($res_j);
            ?>
            <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200 border-l-4 sm:border-l-[6px] border-l-slate-400">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1 truncate">Censo Oficial</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-xl sm:text-2xl font-black text-slate-800 tabular-nums"><?php echo $stats['activos']; ?></h3>
                    <span class="text-[10px] sm:text-xs font-bold text-slate-400 tabular-nums">/ <?php echo $stats['total']; ?> disponibles</span>
                </div>
            </div>
            <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200 border-l-4 sm:border-l-[6px] border-l-blue-500">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1 truncate">Federaciones</p>
                <h3 class="text-xl sm:text-2xl font-black text-blue-600 tabular-nums"><?php echo $stats['feds']; ?></h3>
                <p class="text-[9px] sm:text-[10px] font-bold text-blue-400 mt-1 uppercase italic truncate">Organismos</p>
            </div>
            <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200 border-l-4 sm:border-l-[6px] border-l-emerald-500">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1 truncate">Estado Licencias</p>
                <h3 class="text-xl sm:text-2xl font-black text-emerald-600">Auditadas</h3>
                <p class="text-[9px] sm:text-[10px] font-bold text-emerald-600/60 mt-1 uppercase italic truncate">Normativa v4</p>
            </div>
        </div>

        <?php
        $query = "SELECT j.*, f.nombre_corto as fed_siglas FROM jueces j LEFT JOIN federaciones f ON j.federacion = f.id ORDER BY j.apellidos ASC";
        $res = mysqli_query($connection, $query);
        $jueces_list = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $jueces_list[] = $row;
        }
        ?>

        <!-- Directorio de Jueces -->
        <div class="bg-white rounded-2xl sm:rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-4 sm:px-8 py-3.5 sm:py-6 bg-slate-50/50 border-b border-slate-100 flex justify-between items-center">
                <h2 class="text-sm sm:text-lg font-black text-slate-800 uppercase tracking-tighter italic">Censo Oficial de Jueces</h2>
                <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest tabular-nums"><?php echo count($jueces_list); ?> total</span>
            </div>
            
            <!-- Vista Móvil: Cards Fluidas (md:hidden) -->
            <div class="md:hidden divide-y divide-slate-100" id="juecesMobileContainer">
                <?php if (empty($jueces_list)): ?>
                    <div class="p-8 text-center text-slate-400 text-xs font-bold uppercase tracking-widest">No hay jueces registrados</div>
                <?php else: ?>
                    <?php foreach ($jueces_list as $row): 
                        $is_active = ($row['activo'] == 1);
                    ?>
                    <div class="juez-mobile-card p-3.5 sm:p-4 hover:bg-slate-50/60 transition-colors <?php echo !$is_active ? 'opacity-40 bg-slate-50' : ''; ?>"
                         data-search="<?php echo htmlspecialchars(strtolower($row['id'].' '.$row['apellidos'].' '.$row['nombre'].' '.$row['licencia'].' '.$row['fed_siglas'])); ?>">
                        <div class="flex items-start justify-between gap-2.5 mb-2">
                            <div class="min-w-0 flex-1">
                                <h3 class="text-xs sm:text-sm font-black text-slate-800 leading-snug">
                                    <?php echo $row['apellidos']; ?>, <?php echo $row['nombre']; ?>
                                </h3>
                                <div class="flex flex-wrap items-center gap-1.5 mt-1">
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider tabular-nums">ID #<?php echo $row['id']; ?></span>
                                    <?php if (!$is_active): ?>
                                        <span class="px-1.5 py-0.5 bg-red-50 text-red-500 text-[8px] font-black rounded border border-red-100 uppercase italic">Baja</span>
                                    <?php else: ?>
                                        <span class="px-1.5 py-0.5 bg-emerald-50 text-emerald-600 text-[8px] font-black rounded border border-emerald-100 uppercase">Activo</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <form action="jueces_edit.php" method="POST" class="inline">
                                    <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="edit_btn" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-500 bg-slate-100 hover:bg-emerald-50 hover:text-emerald-600 active:scale-[0.98] transition-all">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                </form>
                                <button type="button" onclick="launchConfirmDeleteJuez(<?php echo $row['id'];?>, '<?php echo addslashes($row['nombre'].' '.$row['apellidos']);?>')" class="w-8 h-8 rounded-lg bg-slate-100 text-slate-400 hover:bg-red-50 hover:text-red-500 active:scale-[0.98] transition-all flex items-center justify-center">
                                    <i class="fas fa-trash-can text-xs"></i>
                                </button>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-1.5 pt-1 text-[10px]">
                            <span class="px-2 py-0.5 bg-blue-50 text-blue-600 font-black rounded-md uppercase tracking-wider text-[9px]">
                                <?php echo $row['fed_siglas'] ?: 'Sin federación'; ?>
                            </span>
                            <?php if (!empty($row['licencia'])): ?>
                                <span class="px-2 py-0.5 bg-slate-100 text-slate-600 font-bold rounded-md tabular-nums">
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
                <table class="w-full text-left border-collapse" id="juecesTable">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-400 text-[10px] font-black uppercase tracking-[0.2em]">
                            <th class="px-8 py-4 w-16">ID</th>
                            <th class="px-4 py-4">Apellidos y Nombre</th>
                            <th class="px-4 py-4">Licencia</th>
                            <th class="px-4 py-4 text-center">Federación</th>
                            <th class="px-4 py-4 text-center w-32">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($jueces_list as $row): 
                            $is_active = ($row['activo'] == 1);
                        ?>
                        <tr class="hover:bg-slate-50/50 transition-colors <?php echo !$is_active ? 'opacity-40 bg-slate-50' : ''; ?>">
                            <td class="px-8 py-5 text-xs font-black text-slate-300 italic tabular-nums">#<?php echo $row['id']; ?></td>
                            <td class="px-4 py-5 font-black text-slate-700">
                                <?php echo $row['apellidos']; ?>, <?php echo $row['nombre']; ?>
                                <?php if(!$is_active): ?>
                                    <span class="ml-2 px-2 py-0.5 bg-red-100 text-red-600 text-[8px] font-black rounded uppercase italic">Baja</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-5 text-xs font-medium text-slate-400 tabular-nums"><?php echo $row['licencia'] ?: '-'; ?></td>
                            <td class="px-4 py-5 text-center">
                                <span class="px-3 py-1 bg-slate-100 text-slate-500 text-[9px] font-black rounded-lg border border-slate-200"><?php echo $row['fed_siglas'] ?: 'N/A'; ?></span>
                            </td>
                            <td class="px-4 py-5">
                                <div class="flex items-center justify-center gap-2">
                                    <form action="jueces_edit.php" method="POST">
                                        <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="edit_btn" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 hover:bg-white hover:text-emerald-500 hover:shadow-md transition-all border border-transparent hover:border-emerald-100"><i class="fas fa-edit text-sm"></i></button>
                                    </form>
                                    <button type="button" onclick="launchConfirmDeleteJuez(<?php echo $row['id'];?>, '<?php echo addslashes($row['nombre'].' '.$row['apellidos']);?>')" class="w-9 h-9 rounded-xl bg-slate-50 text-slate-300 hover:bg-red-50 hover:text-red-500 transition-all flex items-center justify-center border border-transparent hover:border-red-100 shadow-sm"><i class="fas fa-trash-can text-xs"></i></button>
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

<!-- Formulario de Borrado Oculto -->
<form id="deleteJuezForm" action="jueces_code.php" method="POST">
    <input type="hidden" name="delete_id" id="deleteJuezID">
    <input type="hidden" name="delete_btn" value="1">
</form>
<script>
function toggleAddJuezPanel() { document.getElementById('addJuezPanel').classList.toggle('hidden'); }

function filterJuecesTable() {
    const input = document.getElementById('juezSearchInput');
    const filter = input.value.toLowerCase().trim();
    
    // Filtrar tabla escritorio
    const table = document.getElementById('juecesTable');
    if (table) {
        const tr = table.getElementsByTagName('tr');
        for (let i = 1; i < tr.length; i++) {
            let textContent = tr[i].textContent.toLowerCase();
            tr[i].style.display = textContent.indexOf(filter) > -1 ? "" : "none";
        }
    }
    
    // Filtrar cards móvil
    const mobileCards = document.querySelectorAll('.juez-mobile-card');
    mobileCards.forEach(card => {
        const searchAttr = card.getAttribute('data-search') || card.textContent.toLowerCase();
        card.style.display = searchAttr.indexOf(filter) > -1 ? "" : "none";
    });
}

function launchConfirmDeleteJuez(id, name) {
    Swal.fire({
        title: '¿Eliminar Juez?',
        html: `Estás a punto de borrar la ficha de <b>${name}</b>.<br><small class='text-slate-400'>Esta acción es irreversible.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Mantener'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('deleteJuezID').value = id;
            document.getElementById('deleteJuezForm').submit();
        }
    });
}
</script>

function launchConfirmDeleteJuez(id, name) {
    Swal.fire({
        title: '¿Eliminar Juez?',
        html: `Estás a punto de borrar la ficha de <b>${name}</b>.<br><small class='text-slate-400'>Esta acción es irreversible.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Mantener'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('deleteJuezID').value = id;
            document.getElementById('deleteJuezForm').submit();
        }
    });
}
</script>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
