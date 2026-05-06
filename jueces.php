<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-surface">
    
    <?php include('includes/topbar.php'); ?>

    <!-- Contenido de la Página -->
    <div class="p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend">
        
        <!-- Header de Sección -->
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <h1 class="text-4xl font-black text-slate-800 tracking-tighter mb-2 flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-gavel text-lg"></i></span>
                    Jueces
                </h1>
                <p class="text-slate-500 font-medium font-lexend">Directorio técnico y licencias de arbitraje.</p>
            </div>
            <div class="flex gap-3">
                <button onclick="toggleAddJuezPanel()" class="px-6 py-3 bg-blue-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg shadow-blue-500/20 hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
                    <i class="fas fa-plus text-xs"></i> Añadir Juez
                </button>
            </div>
        </div>

        <!-- Panel Añadir Juez (Colapsable) -->
        <div id="addJuezPanel" class="hidden mb-10 animate-fade-in-down">
            <div class="bg-white rounded-[2.5rem] p-8 shadow-xl border border-blue-100 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                <h2 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3">
                    <i class="fas fa-user-plus text-blue-600"></i> Nuevo Juez
                </h2>
                <form action="jueces_code.php" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-6">
                    <div class="md:col-span-4 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Nombre</label>
                        <input type="text" name="nombre" required class="w-full px-5 py-3.5 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold">
                    </div>
                    <div class="md:col-span-8 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Apellidos</label>
                        <input type="text" name="apellidos" required class="w-full px-5 py-3.5 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold">
                    </div>
                    <div class="md:col-span-4 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Nº Licencia</label>
                        <input type="text" name="licencia" class="w-full px-5 py-3.5 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold">
                    </div>
                    <div class="md:col-span-8 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Federación Autonómica</label>
                        <?php 
                        ob_start();
                        include('./includes/federacion_select_option.php');
                        echo str_replace('<select', '<select name="federacion" class="v3-select-fix"', preg_replace('/<label.*?>.*?<\/label>/i', '', ob_get_clean()));
                        ?>
                    </div>
                    <div class="md:col-span-12 pt-6 border-t border-slate-50 flex justify-end gap-4">
                        <button type="button" onclick="toggleAddJuezPanel()" class="text-xs font-black uppercase text-slate-400 hover:text-slate-600 transition-colors">Cancelar</button>
                        <button type="submit" name="save_btn" class="px-10 py-3.5 bg-blue-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg hover:bg-blue-700 transition-all">Guardar Ficha</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Alertas -->
        <?php if(isset($_SESSION['correcto'])): ?>
            <div class="mb-8 p-4 bg-white border-l-4 border-green-500 text-slate-700 rounded-r-2xl shadow-sm flex items-center gap-4 animate-fade-in">
                <i class="fas fa-check-circle text-green-500"></i>
                <span class="text-sm font-bold"><?php echo $_SESSION['correcto']; unset($_SESSION['correcto']); ?></span>
            </div>
        <?php endif; ?>

        <!-- KPIs -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <?php
            $q_j = "SELECT COUNT(*) as total, SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as activos, COUNT(DISTINCT federacion) as feds FROM jueces";
            $res_j = mysqli_query($connection, $q_j);
            $stats = mysqli_fetch_assoc($res_j);
            ?>
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200 border-l-[6px] border-l-slate-400">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Censo Oficial</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-2xl font-black text-slate-800"><?php echo $stats['activos']; ?></h3>
                    <span class="text-xs font-bold text-slate-400">/ <?php echo $stats['total']; ?> jueces disponibles</span>
                </div>
            </div>
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200 border-l-[6px] border-l-blue-500">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Federaciones</p>
                <h3 class="text-2xl font-black text-blue-600"><?php echo $stats['feds']; ?></h3>
            </div>
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200 border-l-[6px] border-l-emerald-500">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Estado de Licencias</p>
                <h3 class="text-2xl font-black text-emerald-600">Auditadas</h3>
            </div>
        </div>

        <!-- Tabla -->
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-8 py-6 bg-slate-50/50 border-b border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4">
                <h2 class="text-lg font-black text-slate-800 uppercase tracking-tighter italic">Censo Oficial de Jueces</h2>
                
                <!-- Buscador Dinámico -->
                <div class="relative w-full md:w-80">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </span>
                    <input type="text" id="juezSearchInput" placeholder="Buscar por nombre, licencia o federación..." 
                           class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-2xl text-xs font-bold text-slate-700 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all outline-none"
                           onkeyup="filterJuecesTable()">
                </div>
            </div>
            
            <div class="overflow-x-auto no-scrollbar">
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
                        <?php
                        $query = "SELECT j.*, f.nombre_corto as fed_siglas FROM jueces j LEFT JOIN federaciones f ON j.federacion = f.id ORDER BY j.apellidos ASC";
                        $res = mysqli_query($connection, $query);
                        while ($row = mysqli_fetch_assoc($res)):
                            $is_active = ($row['activo'] == 1);
                        ?>
                        <tr class="hover:bg-slate-50/50 transition-colors <?php echo !$is_active ? 'opacity-40 bg-slate-50' : ''; ?>">
                            <td class="px-8 py-5 text-xs font-black text-slate-300 italic">#<?php echo $row['id']; ?></td>
                            <td class="px-4 py-5 font-black text-slate-700">
                                <?php echo $row['apellidos']; ?>, <?php echo $row['nombre']; ?>
                                <?php if(!$is_active): ?>
                                    <span class="ml-2 px-2 py-0.5 bg-red-100 text-red-600 text-[8px] font-black rounded uppercase italic">Baja</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-5 text-xs font-medium text-slate-400"><?php echo $row['licencia'] ?: '-'; ?></td>
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
                        <?php endwhile; ?>
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
    const filter = input.value.toLowerCase();
    const table = document.getElementById('juecesTable');
    const tr = table.getElementsByTagName('tr');

    for (let i = 1; i < tr.length; i++) {
        let textContent = tr[i].textContent.toLowerCase();
        if (textContent.indexOf(filter) > -1) {
            tr[i].style.display = "";
        } else {
            tr[i].style.display = "none";
        }
    }
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

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
