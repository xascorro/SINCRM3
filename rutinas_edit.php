<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');

$id_rutina = $_POST['id_rutina'] ?? $_GET['id_rutina'] ?? null;

if (!$id_rutina) {
    header("Location: rutinas.php");
    exit();
}

$query = "SELECT id, id_fase, id_club as club, orden, preswimmer, tematica, musica FROM rutinas WHERE id = '$id_rutina'";
$query_run = mysqli_query($connection, $query);
$rutina = mysqli_fetch_assoc($query_run);

if (!$rutina) {
    header("Location: rutinas.php");
    exit();
}
?>

<main class="flex-1 flex flex-col min-w-0 bg-surface">
    <?php include('includes/topbar.php'); ?>

    <div class="p-6 md:p-10 max-w-5xl mx-auto w-full font-lexend text-primary">
        
        <!-- Header -->
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <h1 class="text-4xl font-black text-slate-800 tracking-tighter mb-2 flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-edit text-lg"></i></span>
                    Editar Rutina
                </h1>
                <p class="text-slate-500 font-medium">Actualiza la configuración de la rutina #<?php echo $rutina['id']; ?></p>
            </div>
            <div class="flex gap-3">
                <a href="rutinas.php" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition-all flex items-center gap-2">
                    <i class="fas fa-arrow-left text-xs"></i> Volver
                </a>
            </div>
        </div>

        <form action="rutinas_code.php" method="POST" enctype="multipart/form-data" class="animate-fade-in space-y-8">
            <input type="hidden" name="edit_id" value="<?php echo $rutina['id']; ?>">

            <div class="bg-white rounded-[2.5rem] p-8 md:p-12 shadow-sm border border-slate-200 border-t-[6px] border-t-emerald-500 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/5 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                
                <div class="grid grid-cols-1 md:grid-cols-12 gap-8 relative z-10">
                    
                    <div class="md:col-span-2 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Orden</label>
                        <input type="number" name="orden" value="<?php echo $rutina['orden']; ?>" required class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/5 transition-all text-sm font-bold text-slate-700 shadow-inner" placeholder="0">
                        <p class="text-[9px] font-bold text-slate-400 px-1 mt-1 leading-tight italic">Usa -1 para Preswimmer y -10 o inferior para Exhibición.</p>
                    </div>

                    <div class="md:col-span-5 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Fase / Categoría</label>
                        <div class="relative">
                            <?php 
                            $id_fase = $rutina['id_fase'];
                            ob_start(); 
                            include('includes/fases_select_option.php'); 
                            $select_fases = ob_get_clean();
                            $select_fases = preg_replace('/<label.*?>.*?<\/label>/i', '', $select_fases);
                            $select_fases = preg_replace('/class=[\'"].*?[\'"]/i', 'class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-emerald-500 appearance-none text-sm font-bold text-slate-700 shadow-inner"', $select_fases);
                            echo $select_fases;
                            ?>
                            <div class="absolute right-5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-300"><i class="fas fa-chevron-down text-xs"></i></div>
                        </div>
                    </div>

                    <div class="md:col-span-5 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Club</label>
                        <div class="relative">
                            <?php 
                            $id_club_actual = $rutina['club'];
                            ob_start(); 
                            include('includes/club_select_option.php'); 
                            $select_club = ob_get_clean();
                            $select_club = preg_replace('/<label.*?>.*?<\/label>/i', '', $select_club);
                            $select_club = preg_replace('/class=[\'"].*?[\'"]/i', 'class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-emerald-500 appearance-none text-sm font-bold text-slate-700 shadow-inner"', $select_club);
                            echo $select_club;
                            ?>
                            <div class="absolute right-5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-300"><i class="fas fa-chevron-down text-xs"></i></div>
                        </div>
                    </div>

                    <div class="md:col-span-12 space-y-2 pt-4">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Temática de la Rutina</label>
                        <input type="text" name="tematica" value="<?php echo htmlspecialchars($rutina['tematica']); ?>" class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-emerald-500 transition-all text-sm font-bold text-slate-700 shadow-inner" placeholder="Ej: Piratas del Caribe, Rock Clásico...">
                    </div>

                </div>
            </div>

            <!-- BOTONES ACCIÓN -->
            <div class="bg-slate-900 rounded-[2.5rem] p-8 md:p-10 shadow-2xl flex flex-col md:flex-row items-center justify-between gap-6 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-64 h-64 bg-emerald-500/10 rounded-full -ml-32 -mt-32 blur-3xl"></div>
                <div class="relative z-10 text-center md:text-left">
                    <p class="text-[10px] font-black uppercase text-emerald-400 tracking-[0.2em] mb-1">Actualizar Datos</p>
                    <p class="text-sm font-medium text-slate-400">Verifica los cambios antes de guardarlos.</p>
                </div>
                <div class="relative z-10 w-full md:w-auto flex flex-col sm:flex-row gap-4 items-center">
                    <button type="submit" name="update_btn" class="w-full sm:w-auto px-12 py-5 bg-emerald-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-xl shadow-emerald-500/20 hover:bg-emerald-500 hover:scale-105 active:scale-95 transition-all flex items-center justify-center gap-3">
                        <i class="fas fa-save text-lg"></i> Guardar Cambios
                    </button>
                </div>
            </div>
        </form>

    </div>
</main>

<?php 
include('includes/scripts.php'); 
include('includes/footer.php'); 
?>