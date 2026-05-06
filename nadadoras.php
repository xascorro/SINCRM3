<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');

$condicion_club = '';
if(isset($_SESSION['club']) && $_SESSION['club'] > 0){
    $condicion_club = " AND n.club = ".$_SESSION['club'];
}

// 1. Cálculos de KPIs
$q_stats = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN n.activo = 1 THEN 1 ELSE 0 END) as activas,
            AVG(YEAR(CURRENT_DATE) - n.año_nacimiento) as edad_media,
            COUNT(DISTINCT n.club) as total_clubes
            FROM nadadoras n WHERE 1=1 $condicion_club";
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
                    <span class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-person-swimming text-lg"></i></span>
                    Nadadoras
                </h1>
                <p class="text-slate-500 font-medium">Gestión del censo de deportistas y licencias.</p>
            </div>
            <div class="flex gap-3">
                <button onclick="toggleAddNadadoraPanel()" class="px-6 py-3 bg-blue-600 text-white font-black uppercase text-xs tracking-[0.2em] rounded-2xl shadow-lg shadow-blue-500/20 hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
                    <i class="fas fa-plus text-xs"></i> Nueva Nadadora
                </button>
            </div>
        </div>

        <!-- PANEL DASHBOARD: KPIs -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200 border-l-[6px] border-l-slate-400 group hover:shadow-md transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Censo Total</p>
                <h3 class="text-2xl font-black text-slate-800 leading-none"><?php echo $stats['total']; ?></h3>
                <p class="text-[10px] font-bold text-slate-300 mt-2 italic">Nadadoras registradas</p>
            </div>
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200 border-l-[6px] border-l-emerald-500 group hover:shadow-md transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">En Activo</p>
                <h3 class="text-2xl font-black text-emerald-600 leading-none"><?php echo $stats['activas']; ?></h3>
                <p class="text-[10px] font-bold text-emerald-600/50 mt-2 uppercase">Licencias Vigentes</p>
            </div>
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200 border-l-[6px] border-l-blue-500 group hover:shadow-md transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Perfil Demográfico</p>
                <h3 class="text-2xl font-black text-blue-600 leading-none"><?php echo round($stats['edad_media'], 1); ?> <span class="text-xs">años</span></h3>
                <p class="text-[10px] font-bold text-blue-400 mt-2 uppercase">Edad Promedio</p>
            </div>
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200 border-l-[6px] border-l-purple-500 group hover:shadow-md transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Representación</p>
                <h3 class="text-2xl font-black text-purple-600 leading-none"><?php echo $stats['total_clubes']; ?></h3>
                <p class="text-[10px] font-bold text-purple-400 mt-2 uppercase">Clubes Vinculados</p>
            </div>
        </div>

        <!-- Panel Añadir -->
        <div id="addNadadoraPanel" class="hidden mb-10 animate-fade-in-down">
            <div class="bg-white rounded-[2.5rem] p-8 shadow-xl border border-blue-100 relative overflow-hidden">
                <h2 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3"><i class="fas fa-user-plus text-blue-600"></i> Alta de Deportista</h2>
                <form action="nadadoras_code.php" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-6">
                    <div class="md:col-span-4 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Nombre</label>
                        <input type="text" name="nombre" required class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold shadow-inner">
                    </div>
                    <div class="md:col-span-8 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Apellidos</label>
                        <input type="text" name="apellidos" required class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold shadow-inner">
                    </div>
                    <div class="md:col-span-4 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Licencia / NIF</label>
                        <input type="text" name="licencia" class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold">
                    </div>
                    <div class="md:col-span-3 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Año Nacimiento</label>
                        <?php 
                        ob_start(); include('./includes/año_select_option.php');
                        echo str_replace('<select', '<select name="año_nacimiento" class="v3-select-fix"', preg_replace('/<label.*?>.*?<\/label>/i', '', ob_get_clean()));
                        ?>
                    </div>
                    <div class="md:col-span-5 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Club</label>
                        <?php 
                        ob_start(); include('./includes/club_select_option.php');
                        echo str_replace('<select', '<select name="club" class="v3-select-fix"', preg_replace('/<label.*?>.*?<\/label>/i', '', ob_get_clean()));
                        ?>
                    </div>
                    <div class="md:col-span-12 pt-6 border-t border-slate-50 flex justify-end gap-4">
                        <button type="button" onclick="toggleAddNadadoraPanel()" class="text-xs font-black uppercase text-slate-400">Cancelar</button>
                        <button type="submit" name="save_btn" class="px-10 py-3 bg-blue-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg hover:bg-blue-700 transition-all">Guardar Nadadora</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla -->
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-8 py-6 bg-slate-50/50 border-b border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4">
                <h2 class="text-lg font-black text-slate-800 uppercase tracking-tighter italic">Directorio de Nadadoras</h2>
                
                <!-- Buscador Dinámico -->
                <div class="relative w-full md:w-80">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </span>
                    <input type="text" id="nadadoraSearchInput" placeholder="Buscar por nombre, año, licencia o club..." 
                           class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-2xl text-xs font-bold text-slate-700 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all outline-none"
                           onkeyup="filterNadadorasTable()">
                </div>
            </div>
            
            <div class="overflow-x-auto no-scrollbar">
                <table class="w-full text-left border-collapse" id="nadadorasTable">
                    <thead>
                        <tr class="bg-slate-50/20 text-slate-400 text-[10px] font-black uppercase tracking-[0.2em]">
                            <th class="px-8 py-4">Nadadora / Deportista</th>
                            <th class="px-4 py-4 text-center">Año</th>
                            <th class="px-4 py-4">Licencia</th>
                            <th class="px-4 py-4">Club</th>
                            <th class="px-4 py-4 text-center w-32">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php
                        $query = "SELECT n.id, n.licencia, n.apellidos, n.nombre, n.año_nacimiento, n.club, c.nombre_corto as club_nombre, n.activo 
                                  FROM nadadoras n 
                                  LEFT JOIN clubes c ON n.club = c.id 
                                  WHERE 1=1 $condicion_club 
                                  ORDER BY n.apellidos, n.nombre LIMIT 2000";
                        $res = mysqli_query($connection, $query);
                        while ($row = mysqli_fetch_assoc($res)):
                            $is_active = ($row['activo'] == 1);
                        ?>
                        <tr class="hover:bg-slate-50/50 transition-colors <?php echo !$is_active ? 'opacity-40 bg-slate-50' : ''; ?>">
                            <td class="px-8 py-4">
                                <p class="text-sm font-black text-slate-700 leading-tight"><?php echo $row['apellidos']; ?>, <?php echo $row['nombre']; ?></p>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">ID: #<?php echo $row['id']; ?></p>
                            </td>
                            <td class="px-4 py-4 text-center text-xs font-bold text-slate-500"><?php echo $row['año_nacimiento']; ?></td>
                            <td class="px-4 py-4 text-xs font-medium text-slate-400"><?php echo $row['licencia'] ?: '-'; ?></td>
                            <td class="px-4 py-4">
                                <span class="px-3 py-1 bg-slate-100 text-slate-600 text-[9px] font-black rounded-lg uppercase tracking-tighter"><?php echo $row['club_nombre'] ?: 'N/A'; ?></span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <form action="nadadoras_edit.php" method="POST"><input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>"><button type="submit" name="edit_btn" class="w-9 h-9 rounded-xl bg-slate-50 text-slate-400 hover:bg-emerald-50 hover:text-emerald-600 hover:shadow-md transition-all border border-transparent hover:border-emerald-100 flex items-center justify-center"><i class="fas fa-edit text-sm"></i></button></form>
                                    <form action="nadadoras_code.php" method="POST" onsubmit="return confirm('¿Eliminar?');"><input type="hidden" name="id_nadadora" value="<?php echo $row['id']; ?>"><button type="submit" name="delete_btn" class="w-9 h-9 rounded-xl bg-slate-50 text-slate-300 hover:bg-red-50 hover:text-red-600 transition-all flex items-center justify-center border border-transparent hover:border-red-100 shadow-sm"><i class="fas fa-trash-can text-sm"></i></button></form>
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
function toggleAddNadadoraPanel() { document.getElementById('addNadadoraPanel').classList.toggle('hidden'); }

function filterNadadorasTable() {
    const input = document.getElementById('nadadoraSearchInput');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('nadadorasTable');
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
