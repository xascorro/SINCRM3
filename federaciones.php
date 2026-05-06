<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');

// 1. Cálculos de KPIs para Federaciones
$q_stats = "SELECT 
            COUNT(*) as total,
            (SELECT COUNT(*) FROM clubes) as total_clubes,
            (SELECT COUNT(*) FROM nadadoras WHERE activo = 1) as total_nadadoras
            FROM federaciones";
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
                    <span class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-landmark text-lg"></i></span>
                    Federaciones
                </h1>
                <p class="text-slate-500 font-medium italic">Gestión de organismos territoriales y censo regional.</p>
            </div>
            <div class="flex flex-col md:flex-row gap-4 items-center">
                <!-- Buscador Dinámico -->
                <div class="relative w-full md:w-80">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </span>
                    <input type="text" id="fedSearchInput" placeholder="Buscar federación..." 
                           class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl text-xs font-bold text-slate-700 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all outline-none shadow-sm"
                           onkeyup="filterFedCards()">
                </div>
                <button onclick="toggleAddFedPanel()" class="px-8 py-3 bg-blue-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg hover:scale-105 active:scale-95 transition-all flex items-center gap-3">
                    <i class="fas fa-plus"></i> Nueva Federación
                </button>
            </div>
        </div>

        <!-- DASHBOARD: KPIs -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <div class="bg-white p-7 rounded-[2rem] shadow-sm border border-slate-200 border-l-[6px] border-l-blue-600 group hover:shadow-xl transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Organismos</p>
                <h3 class="text-3xl font-black text-slate-800 leading-none"><?php echo $stats['total']; ?></h3>
                <p class="text-[10px] font-bold text-slate-400 mt-2 uppercase italic tracking-tighter">Entidades territoriales</p>
            </div>
            <div class="bg-white p-7 rounded-[2rem] shadow-sm border border-slate-200 border-l-[6px] border-l-purple-500 group hover:shadow-xl transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Cobertura</p>
                <h3 class="text-3xl font-black text-purple-600 leading-none"><?php echo $stats['total_clubes']; ?></h3>
                <p class="text-[10px] font-bold text-slate-400 mt-2 uppercase italic tracking-tighter">Clubes vinculados</p>
            </div>
            <div class="bg-white p-7 rounded-[2rem] shadow-sm border border-slate-200 border-l-[6px] border-l-emerald-500 group hover:shadow-xl transition-all">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Impacto</p>
                <h3 class="text-3xl font-black text-emerald-600 leading-none"><?php echo $stats['total_nadadoras']; ?></h3>
                <p class="text-[10px] font-bold text-slate-400 mt-2 uppercase italic tracking-tighter">Atletas federadas</p>
            </div>
        </div>

        <!-- Panel Añadir Federación (Colapsable) -->
        <div id="addFedPanel" class="hidden mb-10 animate-fade-in-down">
            <div class="bg-white rounded-[2.5rem] p-8 shadow-xl border border-blue-100 relative overflow-hidden">
                <h2 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3"><i class="fas fa-plus-circle text-blue-600"></i> Registro de Organismo</h2>
                <form action="federaciones_code.php" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-12 gap-6">
                    <div class="md:col-span-8 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Nombre Completo</label>
                        <input type="text" name="nombre" required class="w-full px-5 py-3.5 rounded-2xl bg-slate-50 border border-slate-100 text-sm font-bold shadow-inner" placeholder="Ej: Federación de Natación de la Región de Murcia">
                    </div>
                    <div class="md:col-span-4 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Siglas</label>
                        <input type="text" name="nombre_corto" required class="w-full px-5 py-3.5 rounded-2xl bg-slate-50 border border-slate-100 text-sm font-bold shadow-inner" placeholder="Ej: FNRM">
                    </div>
                    <div class="md:col-span-4 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Código</label>
                        <input type="text" name="codigo" class="w-full px-5 py-3.5 rounded-2xl bg-slate-50 border border-slate-100 text-sm font-bold shadow-inner">
                    </div>
                    <div class="md:col-span-8 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Escudo / Logo</label>
                        <input type="file" name="logo" class="w-full px-4 py-2.5 bg-slate-50 rounded-2xl border border-slate-100 text-xs font-bold file:mr-4 file:py-1 file:px-4 file:rounded-full file:border-0 file:bg-blue-600 file:text-white">
                    </div>
                    <div class="md:col-span-12 pt-6 border-t border-slate-50 flex justify-end gap-4">
                        <button type="button" onclick="toggleAddFedPanel()" class="text-xs font-black uppercase text-slate-400">Cancelar</button>
                        <button type="submit" name="save_btn" class="px-10 py-3.5 bg-blue-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg">Guardar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Alertas -->
        <?php if(isset($_SESSION['correcto'])): ?>
            <div class="mb-8 p-4 bg-white border-l-4 border-green-500 text-slate-700 rounded-r-2xl shadow-sm flex items-center gap-4">
                <i class="fas fa-check-circle text-green-500"></i>
                <span class="text-sm font-bold"><?php echo $_SESSION['correcto']; unset($_SESSION['correcto']); ?></span>
            </div>
        <?php endif; ?>

        <!-- Rejilla de Federaciones -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8" id="fedGrid">
            <?php
            $query = "SELECT * FROM federaciones ORDER BY nombre ASC";
            $query_run = mysqli_query($connection, $query); 
            while ($row = mysqli_fetch_assoc($query_run)):
                $logo = !empty($row['logo']) ? $row['logo'] : 'img/undraw_posting_photo.svg';
                
                // Conteo de clubes por federación
                $q_c = "SELECT COUNT(*) as n FROM clubes WHERE federacion = ".$row['id'];
                $num_clubes = mysqli_fetch_assoc(mysqli_query($connection, $q_c))['n'] ?? 0;
            ?>
            <div class="fed-card bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-200 group hover:shadow-2xl hover:-translate-y-2 transition-all relative flex flex-col items-center text-center">
                <!-- Escudo GIGANTE -->
                <div class="w-32 h-32 md:w-36 md:h-36 rounded-3xl bg-slate-50 flex items-center justify-center p-6 mb-8 border border-slate-100 group-hover:bg-white group-hover:scale-105 transition-all duration-500 overflow-hidden shadow-inner">
                    <img src="<?php echo $logo; ?>" class="max-h-full max-w-full object-contain drop-shadow-sm">
                </div>

                <h3 class="text-lg font-black text-slate-800 leading-tight mb-1 truncate w-full px-2 uppercase tracking-tighter"><?php echo $row['nombre']; ?></h3>
                <p class="text-blue-600 font-bold text-xs uppercase tracking-[0.2em] mb-4"><?php echo $row['nombre_corto']; ?></p>
                
                <div class="mt-auto w-full">
                    <div class="mb-8 border-t border-slate-50 pt-6 flex flex-col gap-2">
                        <div class="flex justify-between items-center px-4">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Clubes:</span>
                            <span class="text-xs font-black text-slate-800"><?php echo $num_clubes; ?></span>
                        </div>
                        <div class="flex justify-between items-center px-4">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Jueces:</span>
                            <?php
                            $q_j = "SELECT COUNT(*) as n FROM jueces WHERE federacion = ".$row['id'];
                            $num_jueces = mysqli_fetch_assoc(mysqli_query($connection, $q_j))['n'] ?? 0;
                            ?>
                            <span class="text-xs font-black text-slate-800"><?php echo $num_jueces; ?></span>
                        </div>
                    </div>

                    <div class="flex items-center justify-center gap-4 w-full">
                        <form action="federaciones_edit.php" method="POST"><input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>"><button type="submit" name="edit_btn" class="w-11 h-11 rounded-2xl bg-slate-50 text-slate-400 hover:bg-emerald-50 hover:text-emerald-600 hover:shadow-lg transition-all flex items-center justify-center border border-transparent hover:border-emerald-100"><i class="fas fa-edit text-sm"></i></button></form>
                        <button type="button" onclick="launchConfirmDeleteFed(<?php echo $row['id'];?>, '<?php echo addslashes($row['nombre']);?>')" class="w-11 h-11 rounded-2xl bg-slate-50 text-slate-300 hover:bg-red-50 hover:text-red-600 hover:shadow-lg transition-all flex items-center justify-center border border-transparent hover:border-red-100"><i class="fas fa-trash-can text-sm"></i></button>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

    </div>
</main>

<!-- Formulario de Borrado Oculto -->
<form id="deleteFedForm" action="federaciones_code.php" method="POST">
    <input type="hidden" name="delete_id" id="deleteFedID">
    <input type="hidden" name="delete_btn" value="1">
</form>
<script>
function toggleAddFedPanel() { document.getElementById('addFedPanel').classList.toggle('hidden'); }

function filterFedCards() {
    const input = document.getElementById('fedSearchInput');
    const filter = input.value.toLowerCase();
    const grid = document.getElementById('fedGrid');
    const cards = grid.getElementsByClassName('fed-card');

    for (let i = 0; i < cards.length; i++) {
        let textContent = cards[i].textContent.toLowerCase();
        if (textContent.indexOf(filter) > -1) {
            cards[i].style.display = "";
        } else {
            cards[i].style.display = "none";
        }
    }
}

function launchConfirmDeleteFed(id, name) {
    Swal.fire({
        title: '¿Eliminar Federación?',
        html: `Vas a eliminar <b>${name}</b>.<br><small class='text-slate-400'>Asegúrate de que no tenga <b>clubes ni jueces</b> vinculados, de lo contrario podrías causar inconsistencias en los listados.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('deleteFedID').value = id;
            document.getElementById('deleteFedForm').submit();
        }
    });
}
</script>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
