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
                    <span class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-user-shield text-base sm:text-lg"></i></span>
                    Roles
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 font-medium">Jerarquía y niveles de acceso al sistema.</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2.5 sm:gap-4 items-stretch sm:items-center">
                <!-- Buscador Dinámico -->
                <div class="relative w-full sm:w-80">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </span>
                    <input type="text" id="roleSearchInput" placeholder="Buscar por rol o nivel..." 
                           class="w-full pl-9 pr-3.5 py-2.5 sm:py-3 bg-white border border-slate-200 rounded-xl sm:rounded-2xl text-xs font-bold text-slate-700 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all outline-none shadow-sm"
                           onkeyup="filterRolesTable()">
                </div>
                <button onclick="toggleAddRolPanel()" class="px-5 sm:px-6 py-2.5 sm:py-3 bg-blue-600 text-white font-black uppercase text-xs tracking-wider rounded-xl sm:rounded-2xl shadow-lg shadow-blue-500/20 hover:scale-105 active:scale-[0.98] transition-all flex items-center justify-center gap-2 shrink-0 tap-press">
                    <i class="fas fa-plus text-xs"></i> Nuevo Rol
                </button>
            </div>
        </div>

        <!-- Panel Añadir Rol (Colapsable) -->
        <div id="addRolPanel" class="hidden mb-6 sm:mb-10 animate-fade-in-down">
            <div class="bg-white rounded-2xl sm:rounded-[2.5rem] p-4 sm:p-8 shadow-xl border border-blue-100 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -mr-16 -mt-16 blur-2xl pointer-events-none"></div>
                <h2 class="text-base sm:text-xl font-black text-slate-800 mb-4 sm:mb-8 flex items-center gap-2.5 sm:gap-3">
                    <i class="fas fa-user-tag text-blue-600"></i> Definir Nuevo Rol
                </h2>
                <form action="roles_code.php" method="POST" class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <div class="space-y-1.5 sm:space-y-2">
                        <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1">Nombre del Rol</label>
                        <input type="text" name="roles_nombre" required class="w-full px-4 sm:px-5 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all text-xs sm:text-sm font-bold" placeholder="Ej: Juez Nacional">
                    </div>
                    <div class="space-y-1.5 sm:space-y-2">
                        <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1">Nivel de Acceso (0-100)</label>
                        <input type="number" name="level" required min="0" max="100" class="w-full px-4 sm:px-5 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all text-xs sm:text-sm font-bold" placeholder="Ej: 50">
                    </div>
                    <div class="sm:col-span-2 pt-3 sm:pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" onclick="toggleAddRolPanel()" class="text-xs font-black uppercase text-slate-400 hover:text-slate-600 transition-colors px-3 py-2">Cancelar</button>
                        <button type="submit" name="save_btn" class="px-6 sm:px-8 py-2.5 sm:py-3 bg-blue-600 text-white font-black uppercase text-xs tracking-wider rounded-xl sm:rounded-2xl shadow-lg hover:bg-blue-700 active:scale-[0.98] transition-all flex items-center justify-center gap-2 tap-press">
                            <i class="fas fa-check"></i> Guardar Rol
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Alertas -->
        <?php include('includes/alertas_v4.php'); ?>

        <!-- KPIs -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 sm:gap-6 mb-6 sm:mb-10">
            <?php
            $q_rstats = "SELECT 
                COUNT(*) as total, 
                MAX(level) as max_level, 
                (SELECT COUNT(DISTINCT id_pagina) FROM permisos_roles) as paginas_asignadas 
                FROM roles";
            $res_rstats = mysqli_query($connection, $q_rstats);
            $rstats = mysqli_fetch_assoc($res_rstats);
            ?>
            <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200 border-l-4 sm:border-l-[6px] border-l-slate-400">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1 truncate">Roles Definidos</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-xl sm:text-2xl font-black text-slate-800 tabular-nums"><?php echo $rstats['total']; ?></h3>
                    <span class="text-[10px] sm:text-xs font-bold text-slate-400">niveles</span>
                </div>
            </div>
            <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200 border-l-4 sm:border-l-[6px] border-l-blue-500">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1 truncate">Nivel Máximo</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-xl sm:text-2xl font-black text-blue-600 tabular-nums"><?php echo $rstats['max_level']; ?></h3>
                    <span class="text-[10px] sm:text-xs font-bold text-slate-400">SuperAdmin</span>
                </div>
            </div>
            <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200 border-l-4 sm:border-l-[6px] border-l-purple-500">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1 truncate">Páginas Con Permiso</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-xl sm:text-2xl font-black text-purple-600 tabular-nums"><?php echo $rstats['paginas_asignadas']; ?></h3>
                    <span class="text-[10px] sm:text-xs font-bold text-slate-400">modulares</span>
                </div>
            </div>
        </div>

        <?php
        $query = "SELECT r.*, (SELECT COUNT(*) FROM permisos_roles WHERE id_rol = r.id) as num_permisos FROM roles r ORDER BY level DESC";
        $res = mysqli_query($connection, $query);
        $roles_list = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $roles_list[] = $row;
        }
        ?>

        <!-- Directorio de Roles -->
        <div class="bg-white rounded-2xl sm:rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-4 sm:px-8 py-3.5 sm:py-6 bg-slate-50/50 border-b border-slate-100 flex justify-between items-center">
                <h2 class="text-sm sm:text-lg font-black text-slate-800 uppercase tracking-tighter italic">Niveles de Permisos</h2>
                <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest tabular-nums"><?php echo count($roles_list); ?> configurados</span>
            </div>

            <!-- Vista Móvil: Tarjetas (md:hidden) -->
            <div class="md:hidden divide-y divide-slate-100">
                <?php if (empty($roles_list)): ?>
                    <div class="p-8 text-center text-slate-400 text-xs font-medium">No hay roles configurados.</div>
                <?php else: ?>
                    <?php foreach ($roles_list as $row): 
                        $lvl = $row['level'];
                        $lvl_color = 'bg-slate-100 text-slate-600 border border-slate-200';
                        if ($lvl >= 90) $lvl_color = 'bg-red-50 text-red-700 border border-red-100';
                        elseif ($lvl >= 50) $lvl_color = 'bg-blue-50 text-blue-700 border border-blue-100';
                        elseif ($lvl >= 10) $lvl_color = 'bg-emerald-50 text-emerald-700 border border-emerald-100';
                        $search_string = strtolower($row['nombre'] . ' ' . $row['level'] . ' ' . $row['num_permisos']);
                    ?>
                    <div class="role-mobile-card p-4 space-y-3" data-search="<?php echo htmlspecialchars($search_string); ?>">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-600 font-black text-xs shrink-0 border border-slate-200">
                                    <i class="fas fa-shield-halved text-sm text-blue-600"></i>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-sm font-black text-slate-800 truncate"><?php echo htmlspecialchars($row['nombre']); ?></h3>
                                    <span class="text-[10px] font-bold text-slate-400 tabular-nums">ID #<?php echo $row['id']; ?></span>
                                </div>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-lg text-xs font-black tabular-nums shrink-0 <?php echo $lvl_color; ?>">
                                Nivel <?php echo $lvl; ?>
                            </span>
                        </div>

                        <div class="flex items-center justify-between pt-1 text-xs">
                            <div>
                                <?php if($row['id'] == 1): ?>
                                    <span class="px-2.5 py-1 bg-blue-50 text-blue-600 rounded-lg text-[10px] font-black border border-blue-100">ACCESO TOTAL (*)</span>
                                <?php else: ?>
                                    <span class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg text-[10px] font-bold border border-slate-100 tabular-nums"><?php echo $row['num_permisos']; ?> páginas</span>
                                <?php endif; ?>
                            </div>

                            <div class="flex items-center gap-1 shrink-0">
                                <form action="roles_edit.php" method="POST">
                                    <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="edit_btn" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition-colors" title="Editar">
                                        <i class="fas fa-pen-to-square text-xs"></i>
                                    </button>
                                </form>
                                <?php if($row['id'] != 1): ?>
                                <form action="roles_code.php" method="POST" onsubmit="return confirm('¿Borrar el rol <?php echo htmlspecialchars($row['nombre']); ?>?');">
                                    <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="delete_btn" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors" title="Eliminar">
                                        <i class="fas fa-trash-can text-xs"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Vista Escritorio: Tabla (hidden md:block) -->
            <div class="hidden md:block overflow-x-auto no-scrollbar">
                <table class="w-full text-left border-collapse" id="rolesTable">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-400 text-[10px] font-black uppercase tracking-[0.2em]">
                            <th class="px-8 py-4 w-16">ID</th>
                            <th class="px-4 py-4">Denominación del Rol</th>
                            <th class="px-4 py-4 text-center">Nivel</th>
                            <th class="px-4 py-4 text-center">Permisos Asignados</th>
                            <th class="px-4 py-4 text-center w-32">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($roles_list as $row):
                            $lvl = $row['level'];
                            $lvl_color = 'bg-slate-100 text-slate-600 border border-slate-200';
                            if ($lvl >= 90) $lvl_color = 'bg-red-50 text-red-700 border border-red-100';
                            elseif ($lvl >= 50) $lvl_color = 'bg-blue-50 text-blue-700 border border-blue-100';
                            elseif ($lvl >= 10) $lvl_color = 'bg-emerald-50 text-emerald-700 border border-emerald-100';
                        ?>
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-8 py-5 text-xs font-black text-slate-300 italic tabular-nums">#<?php echo $row['id']; ?></td>
                            <td class="px-4 py-5 font-black text-slate-700"><?php echo htmlspecialchars($row['nombre']); ?></td>
                            <td class="px-4 py-5 text-center">
                                <span class="px-4 py-1 rounded-lg text-xs font-black tabular-nums <?php echo $lvl_color; ?>">
                                    <?php echo $lvl; ?>
                                </span>
                            </td>
                            <td class="px-4 py-5 text-center">
                                <?php if($row['id'] == 1): ?>
                                    <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-lg text-[10px] font-black border border-blue-100">ACCESO TOTAL (*)</span>
                                <?php else: ?>
                                    <span class="px-3 py-1 bg-slate-50 text-slate-500 rounded-lg text-[10px] font-black border border-slate-100 italic tabular-nums"><?php echo $row['num_permisos']; ?> PÁGINAS</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-5">
                                <div class="flex items-center justify-center gap-2">
                                    <form action="roles_edit.php" method="POST">
                                        <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="edit_btn" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 hover:bg-white hover:text-emerald-500 hover:shadow-md transition-all border border-transparent hover:border-emerald-100" title="Editar Rol">
                                            <i class="fas fa-pen-to-square text-sm"></i>
                                        </button>
                                    </form>
                                    <?php if($row['id'] != 1): ?>
                                    <form action="roles_code.php" method="POST" onsubmit="return confirm('¿Borrar el rol <?php echo htmlspecialchars($row['nombre']); ?>?');">
                                        <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="delete_btn" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 hover:bg-white hover:text-red-500 hover:shadow-md transition-all border border-transparent hover:border-red-100" title="Eliminar">
                                            <i class="fas fa-trash-can text-sm"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
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
function toggleAddRolPanel() {
    const p = document.getElementById('addRolPanel');
    p.classList.toggle('hidden');
}

function filterRolesTable() {
    const input = document.getElementById('roleSearchInput');
    const filter = input.value.toLowerCase().trim();

    // Filtrar tabla escritorio
    const table = document.getElementById('rolesTable');
    if (table) {
        const tr = table.getElementsByTagName('tr');
        for (let i = 1; i < tr.length; i++) {
            let textContent = tr[i].textContent.toLowerCase();
            tr[i].style.display = textContent.indexOf(filter) > -1 ? "" : "none";
        }
    }

    // Filtrar tarjetas móvil
    const mobileCards = document.querySelectorAll('.role-mobile-card');
    mobileCards.forEach(card => {
        const searchAttr = card.getAttribute('data-search') || card.textContent.toLowerCase();
        card.style.display = searchAttr.indexOf(filter) > -1 ? "" : "none";
    });
}
</script>

<?php 
include('includes/scripts.php'); 
include('includes/footer.php'); 
?>
