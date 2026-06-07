<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');
?>

<main class="flex-1 flex flex-col min-w-0 bg-surface">
    <?php include('includes/topbar.php'); ?>

    <div class="p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend text-primary">
        
        <!-- Header -->
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <h1 class="text-4xl font-black text-slate-800 tracking-tighter mb-2 flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-file-code text-lg"></i></span>
                    Catálogo de Páginas
                </h1>
                <p class="text-slate-500 font-medium">Gestión de archivos del sistema y su visibilidad en roles.</p>
            </div>
            <div class="flex gap-3">
                <button onclick="toggleAddPagePanel()" class="px-6 py-3 bg-blue-600 text-white font-black uppercase text-xs tracking-[0.2em] rounded-2xl shadow-lg shadow-blue-500/20 hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
                    <i class="fas fa-plus text-xs"></i> Registrar Página
                </button>
            </div>
        </div>

        <!-- Panel Añadir Página -->
        <div id="addPagePanel" class="hidden mb-10 animate-fade-in-down">
            <div class="bg-white rounded-[2.5rem] p-8 shadow-xl border border-blue-100 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                <h2 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3">
                    <i class="fas fa-plus-circle text-blue-600"></i> Nueva Página de Sistema
                </h2>
                <form action="paginas_code.php" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Archivo PHP</label>
                        <input type="text" name="archivo" required class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold shadow-inner" placeholder="ejemplo.php">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Nombre Descriptivo</label>
                        <input type="text" name="nombre" required class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold shadow-inner" placeholder="Gestión de X">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Grupo</label>
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
                    <div class="md:col-span-3 flex items-center justify-end gap-4 pt-4 border-t border-slate-50">
                        <button type="button" onclick="toggleAddPagePanel()" class="text-xs font-black uppercase text-slate-400">Cancelar</button>
                        <button type="submit" name="save_btn" class="px-8 py-3 bg-slate-800 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg hover:bg-slate-900 transition-all">Registrar Página</button>
                    </div>
                </form>
            </div>
        </div>

        <?php include('includes/alertas_v4.php'); ?>

        <!-- Listado -->
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-8 py-6 bg-slate-50/50 border-b border-slate-100 flex justify-between items-center">
                <h2 class="text-lg font-black text-slate-800 uppercase tracking-tighter italic">Scripts Registrados</h2>
                
                <div class="relative w-full md:w-64">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"><i class="fas fa-search text-xs"></i></span>
                    <input type="text" id="pageSearch" placeholder="Buscar página..." class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold focus:border-blue-500 transition-all" onkeyup="filterTable()">
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" id="pagesTable">
                    <thead>
                        <tr class="bg-slate-50/20 text-slate-400 text-[10px] font-black uppercase tracking-[0.2em]">
                            <th class="px-8 py-4">Página / Archivo</th>
                            <th class="px-4 py-4">Grupo</th>
                            <th class="px-4 py-4 text-center">Roles</th>
                            <th class="px-8 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php
                        $query = "SELECT p.*, (SELECT COUNT(*) FROM permisos_roles WHERE id_pagina = p.id) as num_roles FROM paginas_sistema p ORDER BY grupo ASC, nombre ASC";
                        $res = mysqli_query($connection, $query);
                        while ($row = mysqli_fetch_assoc($res)):
                        ?>
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-8 py-4">
                                <p class="text-sm font-black text-slate-700 leading-tight"><?php echo $row['nombre']; ?></p>
                                <p class="text-[10px] font-bold text-slate-400 italic mt-0.5"><?php echo $row['archivo']; ?></p>
                            </td>
                            <td class="px-4 py-4 text-xs font-black text-blue-600 uppercase tracking-widest italic"><?php echo $row['grupo']; ?></td>
                            <td class="px-4 py-4 text-center">
                                <span class="px-3 py-1 bg-slate-100 text-slate-500 rounded-lg text-[10px] font-black"><?php echo $row['num_roles']; ?> roles</span>
                            </td>
                            <td class="px-8 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <button onclick="editPage(<?php echo $row['id']; ?>, '<?php echo $row['archivo']; ?>', '<?php echo $row['nombre']; ?>', '<?php echo $row['grupo']; ?>')" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 hover:bg-emerald-50 hover:text-emerald-500 transition-all border border-transparent hover:border-emerald-100"><i class="fas fa-edit text-sm"></i></button>
                                    <form action="paginas_code.php" method="POST" onsubmit="return confirm('¿Eliminar esta página del catálogo?')">
                                        <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="delete_btn" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 hover:bg-red-50 hover:text-red-500 transition-all border border-transparent hover:border-red-100"><i class="fas fa-trash-can text-sm"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Modal Edición -->
<div id="editModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm">
    <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-lg overflow-hidden animate-zoom-in">
        <div class="px-8 py-6 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
            <h2 class="text-xl font-black text-slate-800 uppercase tracking-tighter italic">Editar Registro</h2>
            <button onclick="closeEditModal()" class="text-slate-400 hover:text-red-500"><i class="fas fa-times text-lg"></i></button>
        </div>
        <form action="paginas_code.php" method="POST" class="p-8 space-y-6">
            <input type="hidden" name="edit_id" id="edit_id">
            <div class="space-y-2">
                <label class="text-[10px] font-black uppercase text-slate-400 px-1">Archivo PHP</label>
                <input type="text" name="edit_archivo" id="edit_archivo" required class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 text-sm font-bold shadow-inner">
            </div>
            <div class="space-y-2">
                <label class="text-[10px] font-black uppercase text-slate-400 px-1">Nombre Descriptivo</label>
                <input type="text" name="edit_nombre" id="edit_nombre" required class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 text-sm font-bold shadow-inner">
            </div>
            <div class="space-y-2">
                <label class="text-[10px] font-black uppercase text-slate-400 px-1">Grupo</label>
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
            <div class="pt-4 flex justify-end gap-4">
                <button type="button" onclick="closeEditModal()" class="text-xs font-black uppercase text-slate-400">Cancelar</button>
                <button type="submit" name="update_btn" class="px-10 py-4 bg-emerald-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg hover:bg-emerald-700 transition-all">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleAddPagePanel() { document.getElementById('addPagePanel').classList.toggle('hidden'); }
function editPage(id, arch, nom, grup) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_archivo').value = arch;
    document.getElementById('edit_nombre').value = nom;
    document.getElementById('edit_grupo').value = grup;
    document.getElementById('editModal').classList.remove('hidden');
}
function closeEditModal() { document.getElementById('editModal').classList.add('hidden'); }
function filterTable() {
    const filter = document.getElementById('pageSearch').value.toLowerCase();
    const rows = document.querySelectorAll('#pagesTable tbody tr');
    rows.forEach(r => r.style.display = r.textContent.toLowerCase().includes(filter) ? '' : 'none');
}
</script>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
