<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');

// 1. Cálculos de KPIs para Figuras
$q_stats = "SELECT 
            COUNT(*) as total,
            AVG(grado_dificultad) as gd_medio,
            MAX(grado_dificultad) as gd_max
            FROM figuras WHERE activo = 1";
$res_stats = mysqli_query($connection, $q_stats);
$stats = mysqli_fetch_assoc($res_stats);
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
                    <span class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-shapes text-lg"></i></span>
                    Catálogo de Figuras
                </h1>
                <p class="text-slate-500 font-medium">Librería técnica oficial y Grados de Dificultad (GD).</p>
            </div>
            <div class="flex gap-3">
                <button onclick="toggleAddFiguraPanel()" class="px-6 py-3 bg-blue-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
                    <i class="fas fa-plus text-xs"></i> Añadir Figura
                </button>
            </div>
        </div>

        <!-- DASHBOARD: KPIs -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <div class="bg-white p-7 rounded-[2rem] shadow-sm border border-slate-200 border-l-[6px] border-l-slate-400 group hover:shadow-xl transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Censo Técnico</p>
                <h3 class="text-3xl font-black text-slate-800 leading-none"><?php echo $stats['total'] ?? 0; ?></h3>
                <p class="text-[10px] font-bold text-slate-400 mt-2 uppercase italic">Figuras activas</p>
            </div>
            <div class="bg-white p-7 rounded-[2rem] shadow-sm border border-slate-200 border-l-[6px] border-l-blue-500 group hover:shadow-xl transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Dificultad Media</p>
                <h3 class="text-3xl font-black text-blue-600 leading-none"><?php echo round($stats['gd_medio'] ?? 0, 2); ?></h3>
                <p class="text-[10px] font-bold text-slate-400 mt-2 uppercase italic">GD Promedio</p>
            </div>
            <div class="bg-white p-7 rounded-[2rem] shadow-sm border border-slate-200 border-l-[6px] border-l-amber-500 group hover:shadow-xl transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Reto Máximo</p>
                <h3 class="text-3xl font-black text-amber-600 leading-none"><?php echo $stats['gd_max'] ?? 0; ?></h3>
                <p class="text-[10px] font-bold text-slate-400 mt-2 uppercase italic">GD más alto</p>
            </div>
        </div>

        <?php include('includes/alertas_v4.php'); ?>

        <!-- Panel Añadir Figura (Colapsable) -->
        <div id="addFiguraPanel" class="hidden mb-10 animate-fade-in-down">
            <div class="bg-white rounded-[2.5rem] p-8 shadow-xl border border-blue-100 relative overflow-hidden">
                <h2 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3"><i class="fas fa-plus-circle text-blue-600"></i> Registro de Figura</h2>
                <form action="figuras_code.php" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-6">
                    <div class="md:col-span-2 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Número</label>
                        <input type="text" name="numero" required class="w-full px-5 py-3.5 rounded-2xl bg-slate-50 border border-slate-100 text-sm font-bold shadow-inner focus:border-blue-500 outline-none transition-all" placeholder="Ej: 101">
                    </div>
                    <div class="md:col-span-8 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Nombre de la Figura</label>
                        <input type="text" name="nombre" required class="w-full px-5 py-3.5 rounded-2xl bg-slate-50 border border-slate-100 text-sm font-bold shadow-inner focus:border-blue-500 outline-none transition-all" placeholder="Ej: Ballet Leg Single">
                    </div>
                    <div class="md:col-span-2 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">G.D.</label>
                        <input type="number" step="0.1" name="grado_dificultad" required class="w-full px-5 py-3.5 rounded-2xl bg-slate-50 border border-slate-100 text-sm font-bold shadow-inner focus:border-blue-500 outline-none transition-all">
                    </div>
                    <div class="md:col-span-12 pt-6 border-t border-slate-50 flex justify-end gap-4">
                        <button type="button" onclick="toggleAddFiguraPanel()" class="text-xs font-black uppercase text-slate-400">Cancelar</button>
                        <button type="submit" name="save_btn" class="px-10 py-3.5 bg-blue-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg">Guardar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de Figuras -->
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-8 py-6 bg-slate-50/50 border-b border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4">
                <h2 class="text-lg font-black text-slate-800 uppercase tracking-tighter italic">Librería Técnica</h2>
                
                <!-- Buscador Dinámico -->
                <div class="relative w-full md:w-80">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </span>
                    <input type="text" id="figuraSearchInput" placeholder="Buscar por número, nombre o GD..." 
                           class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-2xl text-xs font-bold text-slate-700 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all outline-none"
                           onkeyup="filterFigurasTable()">
                </div>
            </div>
            
            <div class="overflow-x-auto no-scrollbar">
                <table class="w-full text-left border-collapse" id="figurasTable">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-400 text-[10px] font-black uppercase tracking-[0.2em]">
                            <th class="px-8 py-5 w-24 text-center">Nº</th>
                            <th class="px-4 py-5">Denominación Oficial</th>
                            <th class="px-4 py-5 text-center">GD</th>
                            <th class="px-4 py-5 text-center">Estado</th>
                            <th class="px-4 py-5 text-center w-32">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php
                        $query = "SELECT * FROM figuras ORDER BY activo DESC, numero ASC";
                        $res = mysqli_query($connection, $query);
                        while ($row = mysqli_fetch_assoc($res)):
                            $is_active = $row['activo'] == 1;
                        ?>
                        <tr class="hover:bg-slate-50/50 transition-colors group <?php echo !$is_active ? 'opacity-50' : ''; ?>">
                            <td class="px-8 py-5 text-center">
                                <span class="px-3 py-1 <?php echo $is_active ? 'bg-slate-100 text-slate-800 border-slate-200' : 'bg-slate-50 text-slate-400 border-slate-100'; ?> text-xs font-black rounded-lg border"><?php echo $row['numero']; ?></span>
                            </td>
                            <td class="px-4 py-5 font-black text-slate-700">
                                <?php echo $row['nombre']; ?>
                            </td>
                            <td class="px-4 py-5 text-center">
                                <span class="text-sm font-black text-blue-600"><?php echo $row['grado_dificultad']; ?></span>
                            </td>
                            <td class="px-4 py-5 text-center">
                                <?php if($is_active): ?>
                                    <span class="px-3 py-1 bg-emerald-50 text-emerald-600 text-[9px] font-black rounded-full border border-emerald-100 uppercase tracking-tighter">Activo</span>
                                <?php else: ?>
                                    <span class="px-3 py-1 bg-red-50 text-red-600 text-[9px] font-black rounded-full border border-red-100 uppercase tracking-tighter">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-5">
                                <div class="flex items-center justify-center gap-2">
                                    <form action="figuras_edit.php" method="POST">
                                        <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="edit_btn" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 hover:bg-white hover:text-emerald-500 hover:shadow-md transition-all border border-transparent hover:border-emerald-100"><i class="fas fa-edit text-sm"></i></button>
                                    </form>
                                    <?php if($is_active): ?>
                                    <button type="button" onclick="launchConfirmDeleteFigura(<?php echo $row['id'];?>, '<?php echo addslashes($row['nombre']);?>')" class="w-9 h-9 rounded-xl bg-slate-50 text-slate-300 hover:bg-red-50 hover:text-red-500 transition-all flex items-center justify-center border border-transparent hover:border-red-100 shadow-sm"><i class="fas fa-trash-can text-xs"></i></button>
                                    <?php endif; ?>
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
<form id="deleteFiguraForm" action="figuras_code.php" method="POST">
    <input type="hidden" name="delete_id" id="deleteFiguraID">
    <input type="hidden" name="delete_btn" value="1">
</form>

<script>
function toggleAddFiguraPanel() { document.getElementById('addFiguraPanel').classList.toggle('hidden'); }

function filterFigurasTable() {
    const input = document.getElementById('figuraSearchInput');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('figurasTable');
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

function launchConfirmDeleteFigura(id, name) {
    Swal.fire({
        title: '¿Desactivar Figura?',
        html: `Vas a desactivar <b>${name}</b> de la librería oficial.<br><small class='text-slate-400'>Esta acción mantendrá el histórico pero no permitirá nuevas inscripciones.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Sí, desactivar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('deleteFiguraID').value = id;
            document.getElementById('deleteFiguraForm').submit();
        }
    });
}
</script>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
