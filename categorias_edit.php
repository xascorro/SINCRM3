<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-surface">
    
    <?php include('includes/topbar.php'); ?>

    <!-- Contenido de la Página -->
    <div class="p-6 md:p-10 max-w-4xl mx-auto w-full font-lexend">
        
        <!-- Header de Sección -->
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <h1 class="text-4xl font-black text-slate-800 tracking-tighter mb-2 flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-layer-group text-lg"></i></span>
                    Editar Categoría
                </h1>
                <p class="text-slate-500 font-medium italic">Ajuste de rangos y denominación técnica.</p>
            </div>
            <div class="flex gap-3">
                <a href="categorias.php" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition-all flex items-center gap-2 shadow-sm italic no-underline">
                    <i class="fas fa-arrow-left text-xs"></i> Volver
                </a>
            </div>
        </div>

        <?php
        if(isset($_POST['edit_btn'])):
            $id = mysqli_real_escape_string($connection, $_POST['edit_id']);
            $query = "SELECT * FROM categorias WHERE id = '$id'";
            $query_run = mysqli_query($connection, $query);
            foreach ($query_run as $row):
        ?>
            <form action="categorias_code.php" method="POST" class="animate-fade-in space-y-8">
                <input type="hidden" name="edit_id" value="<?php echo $row['id']?>">

                <!-- Bloque Principal -->
                <div class="bg-white rounded-[2.5rem] p-8 md:p-12 shadow-sm border border-slate-200 border-t-[6px] border-t-blue-600 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
                        <div class="md:col-span-8 space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest italic">Nombre Completo</label>
                            <input type="text" name="edit_nombre" value="<?php echo $row['nombre']?>" required class="w-full px-6 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all text-lg font-black text-slate-700 shadow-inner uppercase italic">
                        </div>

                        <div class="md:col-span-4 space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest italic">Cód. Corto</label>
                            <input type="text" name="edit_nombre_corto" value="<?php echo $row['nombre_corto']?>" required maxlength="8" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold text-slate-700 uppercase italic">
                        </div>
                        
                        <div class="md:col-span-4 space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest italic">Orden Visual</label>
                            <input type="number" name="edit_orden" value="<?php echo $row['orden']?>" required class="w-full px-6 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold text-slate-700">
                        </div>

                        <div class="md:col-span-4 space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest italic">Edad Mínima</label>
                            <div class="relative">
                                <input type="number" name="edit_edad_minima" value="<?php echo $row['edad_minima']?>" required class="w-full px-6 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold text-slate-700">
                                <span class="absolute right-6 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-300 uppercase">Años</span>
                            </div>
                        </div>

                        <div class="md:col-span-4 space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest italic">Edad Máxima</label>
                            <div class="relative">
                                <input type="number" name="edit_edad_maxima" value="<?php echo $row['edad_maxima']?>" required class="w-full px-6 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold text-slate-700">
                                <span class="absolute right-6 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-300 uppercase">Años</span>
                            </div>
                        </div>

                        <!-- Estado Activo -->
                        <div class="md:col-span-12 pt-4">
                            <label class="flex items-center gap-4 cursor-pointer group w-fit">
                                <div class="relative">
                                    <input type="checkbox" name="edit_activo" class="sr-only peer" <?php echo $row['activo'] == 1 ? 'checked' : ''; ?>>
                                    <div class="w-14 h-8 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:start-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-emerald-500"></div>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-black text-slate-700 uppercase tracking-tight group-hover:text-emerald-600 transition-colors">Categoría Activa</span>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase italic">Determina si este nivel es visible en el sistema</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="mt-12 p-6 bg-blue-50/50 rounded-2xl border border-blue-100 flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-blue-600 shadow-sm"><i class="fas fa-info-circle"></i></div>
                        <p class="text-xs text-blue-800 font-medium leading-relaxed italic">
                            Los rangos de edad determinan automáticamente la elegibilidad de las nadadoras para este nivel durante el proceso de inscripción.
                        </p>
                    </div>

                    <!-- Footer del Panel -->
                    <div class="mt-12 pt-8 border-t border-slate-100 flex items-center justify-between gap-4 flex-wrap">
                        <div class="text-[10px] font-black uppercase text-slate-300 tracking-[0.2em]">Referencia: CAT_ID_<?php echo $row['id']; ?></div>
                        
                        <button type="submit" name="update_btn" class="px-12 py-4 bg-blue-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg shadow-blue-500/20 hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </div>
            </form>
        <?php 
            endforeach; 
        endif;
        ?>
    </div>
</main>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
