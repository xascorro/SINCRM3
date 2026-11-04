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
                    <span class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-file-code text-base sm:text-lg"></i></span>
                    Catálogo de Páginas
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 font-medium">Gestión de archivos del sistema y su visibilidad en roles.</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2.5 sm:gap-4 items-stretch sm:items-center">
                <!-- Buscador Dinámico -->
                <div class="relative w-full sm:w-80">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </span>
                    <input type="text" id="pageSearchInput" placeholder="Buscar página, archivo o grupo..." 
                           class="w-full pl-9 pr-3.5 py-2.5 sm:py-3 bg-white border border-slate-200 rounded-xl sm:rounded-2xl text-xs font-bold text-slate-700 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all outline-none shadow-sm"
                           onkeyup="filterPagesTable()">
                </div>
                <button onclick="toggleAddPagePanel()" class="px-5 sm:px-6 py-2.5 sm:py-3 bg-blue-600 text-white font-black uppercase text-xs tracking-wider rounded-xl sm:rounded-2xl shadow-lg shadow-blue-500/20 hover:scale-105 active:scale-[0.98] transition-all flex items-center justify-center gap-2 shrink-0 tap-press">
                    <i class="fas fa-plus text-xs"></i> Registrar Página
                </button>
            </div>
        </div>

        <!-- Panel Añadir Página (Colapsable) -->
        <div id="addPagePanel" class="hidden mb-6 sm:mb-10 animate-fade-in-down">
            <div class="bg-white rounded-2xl sm:rounded-[2.5rem] p-4 sm:p-8 shadow-xl border border-blue-100 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -mr-16 -mt-16 blur-2xl pointer-events-none"></div>
                <h2 class="text-base sm:text-xl font-black text-slate-800 mb-4 sm:mb-8 flex items-center gap-2.5 sm:gap-3">
                    <i class="fas fa-plus-circle text-blue-600"></i> Nueva Página de Sistema
                </h2>
                <form action="paginas_code.php" method="POST" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6">
                    <div class="space-y-1.5 sm:space-y-2">
                        <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1">Archivo PHP</label>
                        <input type="text" name="archivo" required class="w-full px-4 sm:px-5 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-xs sm:text-sm font-bold font-mono" placeholder="ejemplo.php">
                    </div>
                    <div class="space-y-1.5 sm:space-y-2">
                        <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1">Nombre Descriptivo</label>
                        <input type="text" name="nombre" required class="w-full px-4 sm:px-5 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-xs sm:text-sm font-bold" placeholder="Gestión de X">
                    </div>
                    <div class="space-y-1.5 sm:space-y-2">
                        <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1">Grupo de Módulo</label>
                        <select name="grupo" class="v3-select-fix">
                            <option value="General">General</option>
                            <option value="Competiciones">Competiciones</option>
                            <option value="Puntuaciones">Puntuaciones</option>
                            <option value="Inscripciones">Inscripciones</option>
                            <option value="Seguridad">Seguridad</option>
                            <option value="Sistema">Sistema</option>
                            <option value="Maestros">Maestros</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2 md:col-span-3 pt-3 sm:pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" onclick="toggleAddPagePanel()" class="text-xs font-black uppercase text-slate-400 hover:text-slate-600 transition-colors px-3 py-2">Cancelar</button>
                        <button type="submit" name="save_btn" class="px-6 sm:px-8 py-2.5 sm:py-3 bg-blue-600 text-white font-black uppercase text-xs tracking-wider rounded-xl sm:rounded-2xl shadow-lg hover:bg-blue-700 active:scale-[0.98] transition-all flex items-center justify-center gap-2 tap-press">
                            <i class="fas fa-check"></i> Registrar Página
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Alertas de Sesión -->
        <?php include('includes/alertas_v4.php'); ?>

        <!-- KPIs -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 sm:gap-6 mb-6 sm:mb-10">
            <?php
            $q_pstats = "SELECT 
                COUNT(*) as total, 
                COUNT(DISTINCT grupo) as grupos, 
                (SELECT COUNT(*) FROM permisos_roles) as asignaciones 
                FROM paginas_sistema";
            $res_pstats = mysqli_query($connection, $q_pstats);
            $pstats = mysqli_fetch_assoc($res_pstats);
            ?>
            <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200 border-l-4 sm:border-l-[6px] border-l-slate-400">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1 truncate">Scripts Registrados</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-xl sm:text-2xl font-black text-slate-800 tabular-nums"><?php echo $pstats['total']; ?></h3>
                    <span class="text-[10px] sm:text-xs font-bold text-slate-400">rutas activas</span>
                </div>
            </div>
            <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200 border-l-4 sm:border-l-[6px] border-l-blue-500">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1 truncate">Grupos de Sistema</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-xl sm:text-2xl font-black text-blue-600 tabular-nums"><?php echo $pstats['grupos']; ?></h3>
                    <span class="text-[10px] sm:text-xs font-bold text-slate-400">módulos</span>
                </div>
            </div>
            <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200 border-l-4 sm:border-l-[6px] border-l-emerald-500">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1 truncate">Asignaciones a Roles</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-xl sm:text-2xl font-black text-emerald-600 tabular-nums"><?php echo $pstats['asignaciones']; ?></h3>
                    <span class="text-[10px] sm:text-xs font-bold text-slate-400">permisos activos</span>
                </div>
            </div>
        </div>

        <?php
        $query = "SELECT p.*, (SELECT COUNT(*) FROM permisos_roles WHERE id_pagina = p.id) as num_roles FROM paginas_sistema p ORDER BY grupo ASC, nombre ASC";
        $res = mysqli_query($connection, $query);
        $pages_list = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $pages_list[] = $row;
        }
        ?>

        <!-- Catálogo de Páginas -->
        <div class="bg-white rounded-2xl sm:rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-4 sm:px-8 py-3.5 sm:py-6 bg-slate-50/50 border-b border-slate-100 flex justify-between items-center">
                <h2 class="text-sm sm:text-lg font-black text-slate-800 uppercase tracking-tighter italic">Listado de Scripts Registrados</h2>
                <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest tabular-nums"><?php echo count($pages_list); ?> total</span>
            </div>

            <!-- Vista Móvil: Tarjetas (md:hidden) -->
            <div class="md:hidden divide-y divide-slate-100">
                <?php if (empty($pages_list)): ?>
                    <div class="p-8 text-center text-slate-400 text-xs font-medium">No hay páginas registradas.</div>
                <?php else: ?>
                    <?php foreach ($pages_list as $row): 
                        $search_string = strtolower($row['nombre'] . ' ' . $row['archivo'] . ' ' . $row['grupo']);
                    ?>
                    <div class="page-mobile-card p-4 space-y-3" data-search="<?php echo htmlspecialchars($search_string); ?>">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="text-sm font-black text-slate-800 truncate"><?php echo htmlspecialchars($row['nombre']); ?></h3>
                                <p class="text-[11px] font-mono font-medium text-slate-400 truncate mt-0.5"><?php echo htmlspecialchars($row['archivo']); ?></p>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider bg-blue-50 text-blue-600 border border-blue-100 shrink-0">
                                <?php echo htmlspecialchars($row['grupo']); ?>
                            </span>
                        </div>

                        <div class="flex items-center justify-between pt-1 text-xs">
                            <span class="px-2.5 py-1 bg-slate-100 text-slate-500 rounded-lg text-[10px] font-bold tabular-nums">
                                <?php echo $row['num_roles']; ?> roles asignados
                            </span>

                            <div class="flex items-center gap-1 shrink-0">
                                <button onclick="editPage(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['archivo'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['nombre'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['grupo'], ENT_QUOTES); ?>')" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition-colors">
                                    <i class="fas fa-pen-to-square text-xs"></i>
                                </button>
                                <form action="paginas_code.php" method="POST" onsubmit="return confirm('¿Eliminar la página <?php echo htmlspecialchars($row['nombre']); ?>?');">
                                    <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="delete_btn" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                        <i class="fas fa-trash-can text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Vista Escritorio: Tabla (hidden md:block) -->
            <div class="hidden md:block overflow-x-auto no-scrollbar">
                <table class="w-full text-left border-collapse" id="pagesTable">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-400 text-[10px] font-black uppercase tracking-[0.2em]">
                            <th class="px-8 py-4">Página / Archivo</th>
                            <th class="px-4 py-4">Grupo de Módulo</th>
                            <th class="px-4 py-4 text-center">Roles con Acceso</th>
                            <th class="px-8 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($pages_list as $row): ?>
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-8 py-4">
                                <p class="text-sm font-black text-slate-700 leading-tight"><?php echo htmlspecialchars($row['nombre']); ?></p>
                                <p class="text-[10px] font-mono font-bold text-slate-400 mt-0.5"><?php echo htmlspecialchars($row['archivo']); ?></p>
                            </td>
                            <td class="px-4 py-4">
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-100">
                                    <?php echo htmlspecialchars($row['grupo']); ?>
                                </span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-lg text-[10px] font-bold tabular-nums">
                                    <?php echo $row['num_roles']; ?> roles
                                </span>
                            </td>
                            <td class="px-8 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button onclick="editPage(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['archivo'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['nombre'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['grupo'], ENT_QUOTES); ?>')" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 hover:bg-emerald-50 hover:text-emerald-500 transition-all border border-transparent hover:border-emerald-100" title="Editar">
                                        <i class="fas fa-pen-to-square text-sm"></i>
                                    </button>
                                    <form action="paginas_code.php" method="POST" onsubmit="return confirm('¿Eliminar la página <?php echo htmlspecialchars($row['nombre']); ?> del catálogo?');">
                                        <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="delete_btn" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 hover:bg-red-50 hover:text-red-500 transition-all border border-transparent hover:border-red-100" title="Eliminar">
                                            <i class="fas fa-trash-can text-sm"></i>
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

<!-- Modal Edición -->
<div id="editModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl sm:rounded-[2.5rem] shadow-2xl w-full max-w-lg overflow-hidden animate-zoom-in border border-slate-100">
        <div class="px-6 sm:px-8 py-4 sm:py-6 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
            <h2 class="text-base sm:text-xl font-black text-slate-800 uppercase tracking-tighter italic">Editar Registro</h2>
            <button onclick="closeEditModal()" class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
        <form action="paginas_code.php" method="POST" class="p-6 sm:p-8 space-y-4 sm:space-y-6">
            <input type="hidden" name="edit_id" id="edit_id">
            <div class="space-y-1.5 sm:space-y-2">
                <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1">Archivo PHP</label>
                <input type="text" name="edit_archivo" id="edit_archivo" required class="w-full px-4 sm:px-5 py-3 sm:py-3.5 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-100 font-mono text-xs sm:text-sm font-bold">
            </div>
            <div class="space-y-1.5 sm:space-y-2">
                <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1">Nombre Descriptivo</label>
                <input type="text" name="edit_nombre" id="edit_nombre" required class="w-full px-4 sm:px-5 py-3 sm:py-3.5 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-100 text-xs sm:text-sm font-bold">
            </div>
            <div class="space-y-1.5 sm:space-y-2">
                <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1">Grupo de Módulo</label>
                <select name="edit_grupo" id="edit_grupo" class="v3-select-fix">
                    <option value="General">General</option>
                    <option value="Competiciones">Competiciones</option>
                    <option value="Puntuaciones">Puntuaciones</option>
                    <option value="Inscripciones">Inscripciones</option>
                    <option value="Seguridad">Seguridad</option>
                    <option value="Sistema">Sistema</option>
                    <option value="Maestros">Maestros</option>
                </select>
            </div>
            <div class="pt-3 sm:pt-4 border-t border-slate-100 flex justify-end gap-3 sm:gap-4">
                <button type="button" onclick="closeEditModal()" class="text-xs font-black uppercase text-slate-400 hover:text-slate-600 transition-colors px-3 py-2">Cancelar</button>
                <button type="submit" name="update_btn" class="px-6 sm:px-10 py-2.5 sm:py-3.5 bg-blue-600 text-white font-black uppercase text-xs tracking-wider rounded-xl sm:rounded-2xl shadow-lg hover:bg-blue-700 active:scale-[0.98] transition-all tap-press">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleAddPagePanel() { 
    document.getElementById('addPagePanel').classList.toggle('hidden'); 
}

function editPage(id, arch, nom, grup) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_archivo').value = arch;
    document.getElementById('edit_nombre').value = nom;
    document.getElementById('edit_grupo').value = grup;
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() { 
    document.getElementById('editModal').classList.add('hidden'); 
}

function filterPagesTable() {
    const input = document.getElementById('pageSearchInput');
    const filter = input.value.toLowerCase().trim();

    // Filtrar tabla escritorio
    const table = document.getElementById('pagesTable');
    if (table) {
        const tr = table.getElementsByTagName('tr');
        for (let i = 1; i < tr.length; i++) {
            let textContent = tr[i].textContent.toLowerCase();
            tr[i].style.display = textContent.indexOf(filter) > -1 ? "" : "none";
        }
    }

    // Filtrar tarjetas móvil
    const mobileCards = document.querySelectorAll('.page-mobile-card');
    mobileCards.forEach(card => {
        const searchAttr = card.getAttribute('data-search') || card.textContent.toLowerCase();
        card.style.display = searchAttr.indexOf(filter) > -1 ? "" : "none";
    });
}
</script>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
