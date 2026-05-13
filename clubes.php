<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');

// 1. Cálculos de KPIs para Clubes
$q_stats = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as activos,
            COUNT(DISTINCT federacion) as total_feds,
            (SELECT COUNT(*) FROM nadadoras WHERE activo = 1) as total_nadadoras
            FROM clubes";
$res_stats = mysqli_query($connection, $q_stats);
$stats = mysqli_fetch_assoc($res_stats);

$total_c = $stats['total'] ?: 0;
$activos_c = $stats['activos'] ?: 0;
$pct_activos = ($total_c > 0) ? round(($activos_c / $total_c) * 100) : 0;
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-surface">
    
    <?php include('includes/topbar.php'); ?>

    <div class="p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend">
        
        <!-- Header -->
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6 text-primary">
            <div>
                <h1 class="text-4xl font-black text-slate-800 tracking-tighter mb-2 flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-shield-halved text-lg"></i></span>
                    Clubes
                </h1>
                <p class="text-slate-500 font-medium">Gestión de entidades y censo de participación.</p>
            </div>
            <div class="flex flex-col md:flex-row gap-4 items-center">
                <!-- Buscador Dinámico -->
                <div class="relative w-full md:w-80">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </span>
                    <input type="text" id="clubSearchInput" placeholder="Buscar club por nombre, siglas o región..." 
                           class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl text-xs font-bold text-slate-700 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all outline-none shadow-sm"
                           onkeyup="filterClubesCards()">
                </div>
                <button onclick="toggleAddClubPanel()" class="px-6 py-3 bg-blue-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
                    <i class="fas fa-plus text-xs"></i> Nuevo Club
                </button>
            </div>
        </div>

        <?php include('includes/alertas_v4.php'); ?>

        <!-- DASHBOARD -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
            <div class="md:col-span-2 bg-white p-7 rounded-[2rem] shadow-sm border border-slate-200 border-l-[6px] border-l-purple-500 group hover:shadow-xl transition-all">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Entidades Activas</p>
                        <h3 class="text-3xl font-black text-slate-800"><?php echo $activos_c; ?> <span class="text-lg text-slate-300">/ <?php echo $total_c; ?></span></h3>
                    </div>
                    <div class="px-3 py-1 bg-purple-50 text-purple-600 text-[10px] font-black rounded-lg border border-purple-100"><?php echo $pct_activos; ?>%</div>
                </div>
                <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden mt-4">
                    <div class="h-full bg-purple-500 rounded-full transition-all duration-1000" style="width: <?php echo $pct_activos; ?>%"></div>
                </div>
            </div>
            <div class="bg-white p-7 rounded-[2rem] shadow-sm border border-slate-200 border-l-[6px] border-l-blue-500 group hover:shadow-xl transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Regiones</p>
                <h3 class="text-2xl font-black text-blue-600"><?php echo $stats['total_feds']; ?></h3>
                <p class="text-[10px] font-bold text-slate-400 mt-2 uppercase italic tracking-tighter">Federaciones</p>
            </div>
            <div class="bg-white p-7 rounded-[2rem] shadow-sm border border-slate-200 border-l-[6px] border-l-emerald-500 group hover:shadow-xl transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Licencias</p>
                <h3 class="text-2xl font-black text-emerald-600"><?php echo $stats['total_nadadoras']; ?></h3>
                <p class="text-[10px] font-bold text-slate-400 mt-2 uppercase italic tracking-tighter">Atletas Activas</p>
            </div>
        </div>

        <!-- Panel Añadir -->
        <div id="addClubPanel" class="hidden mb-12 animate-fade-in-down">
            <div class="bg-white rounded-[2.5rem] p-8 shadow-xl border border-blue-100 relative overflow-hidden">
                <h2 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3"><i class="fas fa-plus-circle text-blue-600"></i> Registrar Entidad</h2>
                <form action="clubes_code.php" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-12 gap-6">
                    <div class="md:col-span-8 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Nombre Oficial</label>
                        <input type="text" name="nombre" required class="w-full px-5 py-3.5 rounded-2xl bg-slate-50 border border-slate-100 text-sm font-bold shadow-inner">
                    </div>
                    <div class="md:col-span-4 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Siglas</label>
                        <input type="text" name="nombre_corto" required class="w-full px-5 py-3.5 rounded-2xl bg-slate-50 border border-slate-100 text-sm font-bold shadow-inner">
                    </div>
                    <div class="md:col-span-4 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Código RFEN</label>
                        <input type="text" name="codigo" class="w-full px-5 py-3.5 rounded-2xl bg-slate-50 border border-slate-100 text-sm font-bold">
                    </div>
                    <div class="md:col-span-4 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Federación</label>
                        <?php 
                        ob_start(); include('./includes/federacion_select_option.php');
                        echo str_replace('<select', '<select name="federacion" class="v3-select-fix"', preg_replace('/<label.*?>.*?<\/label>/i', '', ob_get_clean()));
                        ?>
                    </div>
                    <div class="md:col-span-4 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Logo / Escudo</label>
                        <input type="file" name="logo" class="w-full px-4 py-2.5 bg-slate-50 rounded-2xl border border-slate-100 text-xs font-bold file:mr-4 file:py-1 file:px-4 file:rounded-full file:border-0 file:bg-blue-600 file:text-white">
                    </div>
                    <div class="md:col-span-12 pt-6 flex justify-end gap-4 border-t border-slate-50">
                        <button type="button" onclick="toggleAddClubPanel()" class="text-xs font-black uppercase text-slate-400 hover:text-slate-600 transition-colors">Cancelar</button>
                        <button type="submit" name="save_btn" class="px-10 py-3.5 bg-blue-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg hover:bg-blue-700 transition-all">Guardar Club</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Rejilla de Clubes -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8" id="clubesGrid">
            <?php
            $query = "SELECT c.id, c.nombre, c.nombre_corto, c.codigo, c.logo, c.activo, f.nombre_corto AS fed_siglas FROM clubes c LEFT JOIN federaciones f ON c.federacion = f.id ORDER BY c.nombre ASC";
            $query_run = mysqli_query($connection, $query); 
            while ($row = mysqli_fetch_assoc($query_run)):
                $logo = !empty($row['logo']) ? $row['logo'] : 'img/undraw_posting_photo.svg';
                $is_active = isset($row['activo']) ? $row['activo'] : 1;
                
                // Cálculo individual por club: Activas / Totales
                $q_act = "SELECT COUNT(*) as n FROM nadadoras WHERE club = ".$row['id']." AND activo = 1";
                $q_tot = "SELECT COUNT(*) as n FROM nadadoras WHERE club = ".$row['id'];
                $nad_activas = mysqli_fetch_assoc(mysqli_query($connection, $q_act))['n'] ?? 0;
                $nad_totales = mysqli_fetch_assoc(mysqli_query($connection, $q_tot))['n'] ?? 0;
            ?>
            <div class="club-card bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-200 group hover:shadow-2xl hover:-translate-y-2 transition-all relative flex flex-col items-center text-center <?php echo ($is_active == 0) ? 'opacity-60 grayscale-[0.5]' : ''; ?>">
                <div class="absolute top-6 right-6 flex gap-2">
                    <?php if($is_active == 0): ?>
                        <span class="px-2 py-0.5 bg-red-50 text-red-500 text-[8px] font-black uppercase tracking-widest rounded border border-red-100">Inactivo</span>
                    <?php endif; ?>
                    <span class="px-3 py-1 bg-slate-50 text-slate-400 text-[9px] font-black uppercase tracking-widest rounded-lg border border-slate-100 shadow-sm"><?php echo $row['fed_siglas'] ?: 'N/A'; ?></span>
                </div>

                <div class="w-32 h-32 md:w-36 md:h-32 rounded-3xl bg-slate-50 flex items-center justify-center p-6 mb-8 border border-slate-100 group-hover:bg-white group-hover:scale-110 transition-all duration-500 overflow-hidden shadow-inner">
                    <img src="<?php echo $logo; ?>" class="max-h-full max-w-full object-contain drop-shadow-sm">
                </div>

                <h3 class="text-lg font-black text-slate-800 leading-tight mb-1 truncate w-full px-2 uppercase tracking-tighter"><?php echo $row['nombre']; ?></h3>
                <p class="text-blue-600 font-bold text-xs uppercase tracking-[0.2em] mb-6"><?php echo $row['nombre_corto']; ?></p>
                
                <div class="mt-auto w-full">
                    <!-- KPI ACTIVA / TOTAL -->
                    <div class="mb-8 border-t border-slate-50 pt-6">
                        <div class="flex justify-center items-center gap-2">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Nadadoras:</span>
                            <span class="text-sm font-black text-slate-800"><?php echo $nad_activas; ?> <span class="text-xs text-slate-300">/ <?php echo $nad_totales; ?></span></span>
                        </div>
                    </div>

                    <div class="flex items-center justify-center gap-4 w-full">
                        <form action="clubes_edit.php" method="POST"><input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>"><button type="submit" name="edit_btn" class="w-11 h-11 rounded-2xl bg-slate-50 text-slate-400 hover:bg-emerald-50 hover:text-emerald-600 hover:shadow-lg transition-all flex items-center justify-center border border-transparent hover:border-emerald-100"><i class="fas fa-edit text-sm"></i></button></form>
                        <form action="clubes_code.php" method="POST" onsubmit="return confirm('¿Eliminar permanentemente este club?');"><input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>"><button type="submit" name="delete_btn" class="w-11 h-11 rounded-2xl bg-slate-50 text-slate-300 hover:bg-red-50 hover:text-red-600 hover:shadow-lg transition-all flex items-center justify-center border border-transparent hover:border-red-100"><i class="fas fa-trash-alt text-sm"></i></button></form>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</main>

<script>
function toggleAddClubPanel() { document.getElementById('addClubPanel').classList.toggle('hidden'); }

function filterClubesCards() {
    const input = document.getElementById('clubSearchInput');
    const filter = input.value.toLowerCase();
    const grid = document.getElementById('clubesGrid');
    const cards = grid.getElementsByClassName('club-card');

    for (let i = 0; i < cards.length; i++) {
        let textContent = cards[i].textContent.toLowerCase();
        if (textContent.indexOf(filter) > -1) {
            cards[i].style.display = "";
        } else {
            cards[i].style.display = "none";
        }
    }
}
</script>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
