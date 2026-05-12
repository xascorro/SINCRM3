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
                    <span class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-layer-group text-lg"></i></span>
                    Categorías
                </h1>
                <p class="text-slate-500 font-medium italic">Gestión de niveles y rangos de edad de competición.</p>
            </div>
            <div class="flex flex-col md:flex-row gap-4 items-center">
                <!-- Buscador Dinámico -->
                <div class="relative w-full md:w-80">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </span>
                    <input type="text" id="categoriaSearchInput" placeholder="Buscar por nombre, código..." 
                           class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl text-xs font-bold text-slate-700 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all outline-none shadow-sm"
                           onkeyup="filterCategoriasTable()">
                </div>
                <button onclick="toggleAddCatPanel()" class="px-6 py-3 bg-blue-600 text-white font-black uppercase text-xs tracking-[0.2em] rounded-2xl shadow-lg shadow-blue-500/20 hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
                    <i class="fas fa-plus text-xs"></i> Crear Categoría
                </button>
            </div>
        </div>

        <!-- Panel Añadir Categoría (Colapsable) -->
        <div id="addCatPanel" class="hidden mb-10 animate-fade-in-down">
            <div class="bg-white rounded-[2.5rem] p-8 shadow-xl border border-blue-100 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                <h2 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3">
                    <i class="fas fa-plus-circle text-blue-600"></i> Nueva Definición de Nivel
                </h2>
                <form action="categorias_code.php" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-6">
                    <div class="md:col-span-6 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest italic">Nombre Completo</label>
                        <input type="text" name="nombre" required class="w-full px-5 py-3.5 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold shadow-inner" placeholder="Ej: INFANTIL B">
                    </div>
                    <div class="md:col-span-3 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest italic">Cód. Corto</label>
                        <input type="text" name="nombre_corto" required maxlength="8" class="w-full px-5 py-3.5 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold shadow-inner" placeholder="INF-B">
                    </div>
                    <div class="md:col-span-3 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest italic">Orden Visual</label>
                        <input type="number" name="orden" required value="1" class="w-full px-5 py-3.5 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold shadow-inner">
                    </div>
                    <div class="md:col-span-6 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest italic">Edad Mínima</label>
                        <input type="number" name="edad_minima" required class="w-full px-5 py-3.5 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold shadow-inner" placeholder="0">
                    </div>
                    <div class="md:col-span-6 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest italic">Edad Máxima</label>
                        <input type="number" name="edad_maxima" required class="w-full px-5 py-3.5 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold shadow-inner" placeholder="99">
                    </div>
                    <div class="md:col-span-12 pt-6 border-t border-slate-50 flex justify-end gap-4">
                        <button type="button" onclick="toggleAddCatPanel()" class="text-xs font-black uppercase text-slate-400 hover:text-slate-600 transition-colors italic">Cancelar</button>
                        <button type="submit" name="save_btn" class="px-10 py-3.5 bg-blue-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg hover:bg-blue-700 transition-all">
                            Guardar Categoría
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Alertas -->
        <?php if(isset($_SESSION['correcto'])): ?>
            <div class="mb-8 p-6 bg-white border-l-[6px] border-l-emerald-500 text-slate-700 rounded-r-3xl shadow-sm flex items-center gap-4 animate-fade-in">
                <div class="w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-500 shadow-sm"><i class="fas fa-check-circle text-xl"></i></div>
                <span class="text-base font-bold"><?php echo $_SESSION['correcto']; unset($_SESSION['correcto']); ?></span>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['estado'])): ?>
            <div class="mb-8 p-6 bg-white border-l-[6px] border-l-red-500 text-slate-700 rounded-r-3xl shadow-sm flex items-center gap-4 animate-fade-in">
                <div class="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center text-red-500 shadow-sm"><i class="fas fa-exclamation-triangle text-xl"></i></div>
                <span class="text-base font-bold"><?php echo $_SESSION['estado']; unset($_SESSION['estado']); ?></span>
            </div>
        <?php endif; ?>

        <!-- KPIs de Categorías -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <?php
            $q_c = "SELECT COUNT(*) as total, MIN(edad_minima) as min_e, MAX(edad_maxima) as max_e FROM categorias WHERE activo = 1";
            $res_c = mysqli_query($connection, $q_c);
            $stats = mysqli_fetch_assoc($res_c);
            ?>
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-200 border-l-[6px] border-l-slate-400 group hover:shadow-xl transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Censo Técnico</p>
                <h3 class="text-2xl font-black text-slate-800"><?php echo $stats['total'] ?? 0; ?></h3>
                <p class="text-[10px] font-bold text-slate-400 mt-2 uppercase italic">Categorías Activas</p>
            </div>
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-200 border-l-[6px] border-l-blue-500 group hover:shadow-xl transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Rango Edad</p>
                <h3 class="text-2xl font-black text-blue-600"><?php echo $stats['min_e'] ?? 0; ?> - <?php echo $stats['max_e'] ?? 0; ?></h3>
                <p class="text-[10px] font-bold text-blue-400 mt-2 uppercase italic">Años Permitidos</p>
            </div>
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-200 border-l-[6px] border-l-purple-500 group hover:shadow-xl transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Estructura</p>
                <h3 class="text-2xl font-black text-purple-600 uppercase italic">Jerárquica</h3>
                <p class="text-[10px] font-bold text-purple-400 mt-2 uppercase italic">Según orden técnico</p>
            </div>
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-200 border-l-[6px] border-l-amber-500 group hover:shadow-xl transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Estado</p>
                <h3 class="text-2xl font-black text-amber-600 uppercase italic">Sincronizado</h3>
                <p class="text-[10px] font-bold text-amber-400 mt-2 uppercase italic">Base de datos v4</p>
            </div>
        </div>

        <!-- Tabla -->
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-8 py-6 bg-slate-50/50 border-b border-slate-100 flex justify-between items-center">
                <h2 class="text-lg font-black text-slate-800 uppercase tracking-tighter italic">Catálogo de Niveles</h2>
            </div>
            
            <div class="overflow-x-auto no-scrollbar">
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
                        <?php
                        $query = "SELECT * FROM categorias ORDER BY orden ASC, activo DESC";
                        $res = mysqli_query($connection, $query);
                        while ($row = mysqli_fetch_assoc($res)):
                            $is_active = $row['activo'] == 1;
                        ?>
                        <tr class="hover:bg-slate-50/50 transition-colors <?php echo !$is_active ? 'opacity-40 grayscale bg-slate-50' : ''; ?>">
                            <td class="px-8 py-5 text-xs font-black text-slate-300 italic">#<?php echo $row['orden']; ?></td>
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
                                <span class="text-xs font-black text-slate-500"><?php echo $row['edad_minima']; ?></span>
                            </td>
                            <td class="px-4 py-5 text-center">
                                <span class="text-xs font-black text-blue-500"><?php echo $row['edad_maxima']; ?></span>
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
                        <?php endwhile; ?>
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
    const filter = input.value.toLowerCase();
    const table = document.getElementById('categoriasTable');
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
</script>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
