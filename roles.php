<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-surface">
    
    <?php include('includes/topbar.php'); ?>

    <!-- Contenido de la Página -->
    <div class="p-6 md:p-10 max-w-5xl mx-auto w-full">
        
        <!-- Header de Sección -->
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <h1 class="text-4xl font-black text-slate-800 tracking-tighter mb-2 flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-user-shield text-lg"></i></span>
                    Roles
                </h1>
                <p class="text-slate-500 font-medium">Jerarquía y niveles de acceso al sistema.</p>
            </div>
            <div class="flex gap-3">
                <button onclick="toggleAddRolPanel()" class="px-6 py-3 bg-blue-600 text-white font-black uppercase text-xs tracking-[0.2em] rounded-2xl shadow-lg shadow-blue-500/20 hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
                    <i class="fas fa-plus text-xs"></i> Nuevo Rol
                </button>
            </div>
        </div>

        <!-- Panel Añadir Rol (Colapsable) -->
        <div id="addRolPanel" class="hidden mb-10 animate-fade-in-down">
            <div class="bg-white rounded-[2.5rem] p-8 shadow-xl border border-blue-100 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                <h2 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3">
                    <i class="fas fa-user-tag text-blue-600"></i> Definir Nuevo Rol
                </h2>
                <form action="roles_code.php" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Nombre del Rol</label>
                        <input type="text" name="roles_nombre" required class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all text-sm font-bold" placeholder="Ej: Juez Nacional">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Nivel de Acceso (0-100)</label>
                        <input type="number" name="level" required class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all text-sm font-bold" placeholder="Ej: 50">
                    </div>
                    <div class="md:col-span-2 flex items-center justify-end gap-4 pt-4 border-t border-slate-50">
                        <button type="button" onclick="toggleAddRolPanel()" class="text-xs font-black uppercase text-slate-400 hover:text-slate-600 transition-colors">Cancelar</button>
                        <button type="submit" name="save_btn" class="px-8 py-3 bg-slate-800 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg hover:bg-slate-900 transition-all">
                            Guardar Rol
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Alertas -->
        <?php include('includes/alertas_v4.php'); ?>

        <!-- Tabla de Roles -->
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-8 py-6 bg-slate-50/50 border-b border-slate-100 flex justify-between items-center">
                <h2 class="text-lg font-black text-slate-800 uppercase tracking-tighter italic">Niveles de Permisos</h2>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Configuración de Seguridad</span>
            </div>
            
            <div class="overflow-x-auto no-scrollbar">
                <table class="w-full text-left border-collapse" id="dataTable">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-400 text-[10px] font-black uppercase tracking-[0.2em]">
                            <th class="px-8 py-4 w-16">ID</th>
                            <th class="px-4 py-4">Denominación del Rol</th>
                            <th class="px-4 py-4 text-center">Nivel</th>
                            <th class="px-4 py-4 text-center w-32">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php
                        $query = "SELECT * FROM roles ORDER BY level DESC";
                        $res = mysqli_query($connection, $query);
                        while ($row = mysqli_fetch_assoc($res)):
                            $lvl = $row['level'];
                            $lvl_color = 'bg-slate-100 text-slate-600';
                            if ($lvl >= 90) $lvl_color = 'bg-red-50 text-red-700 border border-red-100';
                            elseif ($lvl >= 50) $lvl_color = 'bg-blue-50 text-blue-700 border border-blue-100';
                            elseif ($lvl >= 10) $lvl_color = 'bg-emerald-50 text-emerald-700 border border-emerald-100';
                        ?>
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-8 py-5 text-xs font-black text-slate-300 italic">#<?php echo $row['id']; ?></td>
                            <td class="px-4 py-5 font-black text-slate-700"><?php echo $row['nombre']; ?></td>
                            <td class="px-4 py-5 text-center">
                                <span class="px-4 py-1 rounded-lg text-xs font-black <?php echo $lvl_color; ?>">
                                    <?php echo $lvl; ?>
                                </span>
                            </td>
                            <td class="px-4 py-5">
                                <div class="flex items-center justify-center gap-2">
                                    <form action="roles_edit.php" method="POST">
                                        <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="edit_btn" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 hover:bg-white hover:text-emerald-500 hover:shadow-md transition-all border border-transparent hover:border-emerald-100">
                                            <i class="fas fa-pen-to-square text-sm"></i>
                                        </button>
                                    </form>
                                    <form action="roles_code.php" method="POST" onsubmit="return confirm('¿Borrar rol?');">
                                        <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="delete_btn" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 hover:bg-white hover:text-red-500 hover:shadow-md transition-all border border-transparent hover:border-red-100">
                                            <i class="fas fa-trash-can text-sm"></i>
                                        </button>
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

<script>
function toggleAddRolPanel() {
    const p = document.getElementById('addRolPanel');
    p.classList.toggle('hidden');
}
</script>

<?php 
include('includes/scripts.php'); 
include('includes/footer.php'); 
?>
