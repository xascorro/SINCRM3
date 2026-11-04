<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');

$id = isset($_POST['edit_id']) ? intval($_POST['edit_id']) : (isset($_GET['id']) ? intval($_GET['id']) : 0);

if(!$id){
    header('Location: modalidades.php');
    exit();
}

$query = "SELECT * FROM modalidades WHERE id = $id LIMIT 1";
$query_run = mysqli_query($connection, $query);
$modalidad = mysqli_fetch_assoc($query_run);

if(!$modalidad){
    header('Location: modalidades.php');
    exit();
}
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-surface">
    
    <?php include('includes/topbar.php'); ?>

    <!-- Contenido de la Página -->
    <div class="px-3 py-4 sm:p-6 md:p-10 max-w-4xl mx-auto w-full font-lexend text-primary">
        
        <!-- Header de Sección -->
        <div class="mb-6 sm:mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-6">
            <div>
                <h1 class="text-xl sm:text-2xl md:text-3xl font-black text-slate-800 tracking-tight flex items-center gap-2.5 sm:gap-3">
                    <span class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shadow-xs border border-blue-100">
                        <i class="fas fa-edit text-sm sm:text-base"></i>
                    </span>
                    Editar Modalidad
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 font-medium mt-1">Configuración técnica de la modalidad #<?php echo $modalidad['id']; ?> (<?php echo htmlspecialchars($modalidad['nombre']); ?>)</p>
            </div>
            <a href="modalidades.php" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-700 text-xs sm:text-sm font-bold rounded-xl hover:bg-slate-50 active:scale-[0.98] transition-all shadow-xs self-start sm:self-auto">
                <i class="fas fa-arrow-left text-xs"></i> Volver a Modalidades
            </a>
        </div>

        <form action="modalidades_code.php" method="POST" class="animate-fade-in space-y-6 sm:space-y-8">
            <input type="hidden" name="edit_id" value="<?php echo $modalidad['id']; ?>">

            <!-- Bloque Principal de Configuración -->
            <div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-8 shadow-sm border border-slate-200/80 border-t-4 border-t-blue-600 relative overflow-hidden">
                <h2 class="text-base sm:text-lg font-black text-slate-800 mb-5 sm:mb-6 flex items-center gap-2.5">
                    <i class="fas fa-tag text-blue-600"></i> Parámetros Oficiales
                </h2>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-4 sm:gap-6">
                    <div class="sm:col-span-2 md:col-span-8 space-y-1.5">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest italic">Denominación Oficial</label>
                        <input type="text" name="edit_nombre" value="<?php echo htmlspecialchars($modalidad['nombre']); ?>" required 
                               class="w-full px-4 sm:px-5 py-3 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-200 focus:border-blue-500 focus:bg-white transition-all text-sm font-black text-slate-800 shadow-inner">
                    </div>

                    <div class="sm:col-span-1 md:col-span-4 space-y-1.5">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest italic">Cód. Corto</label>
                        <input type="text" name="edit_nombre_corto" value="<?php echo htmlspecialchars($modalidad['nombre_corto']); ?>" required maxlength="5" 
                               class="w-full px-4 sm:px-5 py-3 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-200 focus:border-blue-500 focus:bg-white transition-all text-sm font-black text-slate-800 uppercase shadow-inner">
                    </div>
                    
                    <div class="sm:col-span-1 md:col-span-4 space-y-1.5">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest italic">Orden Visual</label>
                        <input type="number" name="edit_orden" value="<?php echo intval($modalidad['orden']); ?>" required 
                               class="w-full px-4 sm:px-5 py-3 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-200 focus:border-blue-500 focus:bg-white transition-all text-sm font-bold text-slate-800 tabular-nums shadow-inner">
                    </div>

                    <div class="sm:col-span-1 md:col-span-4 space-y-1.5">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest italic">Nadadoras Titulares</label>
                        <input type="number" name="edit_numero_participantes" value="<?php echo intval($modalidad['numero_participantes']); ?>" required min="1" max="14" 
                               class="w-full px-4 sm:px-5 py-3 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-200 focus:border-blue-500 focus:bg-white transition-all text-sm font-bold text-slate-800 tabular-nums shadow-inner">
                    </div>

                    <div class="sm:col-span-1 md:col-span-4 space-y-1.5">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest italic">Máximo Reservas</label>
                        <input type="number" name="edit_numero_reservas" value="<?php echo intval($modalidad['numero_reservas']); ?>" required min="0" max="4" 
                               class="w-full px-4 sm:px-5 py-3 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-200 focus:border-blue-500 focus:bg-white transition-all text-sm font-bold text-slate-800 tabular-nums shadow-inner">
                    </div>

                    <!-- Toggles: Coach Card & Activo -->
                    <div class="sm:col-span-2 md:col-span-6 pt-2">
                        <label class="p-4 bg-indigo-50/50 hover:bg-indigo-50 rounded-2xl border border-indigo-100 flex items-center justify-between cursor-pointer transition-all">
                            <div>
                                <span class="text-xs font-black uppercase text-indigo-900 block tracking-tight">Requiere Coach Card</span>
                                <span class="text-[10px] text-slate-500 font-medium">Obliga a registrar y auditar los elementos AQUA</span>
                            </div>
                            <div class="relative inline-flex items-center">
                                <input type="checkbox" name="edit_coach_card" value="si" <?php echo ($modalidad['coach_card'] == 'si') ? 'checked' : ''; ?> class="sr-only peer">
                                <div class="w-10 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                            </div>
                        </label>
                    </div>

                    <div class="sm:col-span-2 md:col-span-6 pt-2">
                        <label class="p-4 bg-emerald-50/40 hover:bg-emerald-50 rounded-2xl border border-emerald-100 flex items-center justify-between cursor-pointer transition-all">
                            <div>
                                <span class="text-xs font-black uppercase text-emerald-900 block tracking-tight">Modalidad Activa</span>
                                <span class="text-[10px] text-slate-500 font-medium">Habilitada para configuración de fases y eventos</span>
                            </div>
                            <div class="relative inline-flex items-center">
                                <input type="checkbox" name="edit_activo" value="1" <?php echo ($modalidad['activo'] == 1) ? 'checked' : ''; ?> class="sr-only peer">
                                <div class="w-10 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="mt-8 p-4 bg-blue-50/50 rounded-2xl border border-blue-100/80 flex items-start gap-3">
                    <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center shrink-0 text-xs font-bold">
                        <i class="fas fa-info"></i>
                    </div>
                    <p class="text-xs text-blue-900 font-medium leading-relaxed">
                        El número de titulares y reservas define los cupos permitidos al registrar deportistas en las rutinas de esta modalidad. El indicador de <strong>Coach Card</strong> determina si el sistema requerirá la ficha de elementos acrobáticos, híbridos y técnicos.
                    </p>
                </div>
            </div>

            <!-- Botones de Acción (Margen inferior para Bottom Nav móvil) -->
            <div class="pt-4 sm:pt-6 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 pb-24 sm:pb-8">
                <a href="modalidades.php" class="px-6 py-3.5 text-center text-xs font-black uppercase tracking-wider text-slate-500 hover:text-slate-800 bg-white border border-slate-200 rounded-xl transition-all active:scale-[0.98]">
                    Cancelar
                </a>
                <button type="submit" name="update_btn" class="px-8 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-black uppercase text-xs tracking-wider rounded-xl shadow-lg shadow-blue-500/20 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</main>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
