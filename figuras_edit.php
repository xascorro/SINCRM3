<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');
include('includes/parsedown.php');
include('includes/parsedownExtra.php');
$Parsedown = new ParsedownExtra();
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-surface">
    
    <?php include('includes/topbar.php'); ?>

    <!-- Contenido de la Página -->
    <div class="p-6 md:p-10 max-w-5xl mx-auto w-full font-lexend text-primary">
        
        <!-- Header de Sección -->
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <h1 class="text-4xl font-black text-slate-800 tracking-tighter mb-2 flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-shapes text-lg"></i></span>
                    Ficha de Figura
                </h1>
                <p class="text-slate-500 font-medium">Especificación técnica y criterios de juzgamiento.</p>
            </div>
            <div class="flex gap-3">
                <a href="figuras.php" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition-all flex items-center gap-2 shadow-sm">
                    <i class="fas fa-arrow-left text-xs"></i> Librería
                </a>
            </div>
        </div>

        <?php
        if(isset($_POST['edit_btn'])):
            $id = mysqli_real_escape_string($connection, $_POST['edit_id']);
            $query = "SELECT * FROM figuras WHERE id = '$id'";
            $query_run = mysqli_query($connection, $query);
            while ($row = mysqli_fetch_assoc($query_run)):
        ?>
            <form action="figuras_code.php" method="POST" class="space-y-8 animate-fade-in">
                <input type="hidden" name="edit_id" value="<?php echo $row['id']?>">

                <!-- Bloque Principal -->
                <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-sm border border-slate-200 border-t-[6px] border-t-blue-600 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                    <h2 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3"><i class="fas fa-id-badge text-blue-600"></i> Identidad Técnica</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Número</label>
                            <input type="text" name="edit_numero" value="<?php echo $row['numero']?>" required class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-black text-slate-700 shadow-inner text-center">
                        </div>
                        <div class="md:col-span-8 space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Nombre de la Figura</label>
                            <input type="text" name="edit_nombre" value="<?php echo $row['nombre']?>" required class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold text-slate-700 shadow-inner">
                        </div>
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">G.D.</label>
                            <input type="number" step="0.1" name="edit_grado_dificultad" value="<?php echo $row['grado_dificultad']?>" required class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-black text-blue-600 shadow-inner text-center">
                        </div>

                        <!-- Editor Markdown -->
                        <div class="md:col-span-12 space-y-4">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Descripción y Criterios (Markdown)</label>
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <textarea name="descripcion" rows="12" class="w-full p-6 rounded-[2rem] bg-slate-900 text-slate-300 font-mono text-xs leading-relaxed focus:ring-4 focus:ring-blue-500/10 transition-all border-none" placeholder="Escribe aquí la descripción..."><?php echo $row['descripcion'];?></textarea>
                                <div class="w-full p-8 rounded-[2rem] bg-slate-50 border border-slate-100 prose prose-slate prose-sm max-w-none overflow-y-auto max-h-[400px]">
                                    <h4 class="text-[9px] font-black uppercase text-slate-400 mb-4 tracking-tighter italic">Vista Previa</h4>
                                    <?php echo !empty($row['descripcion']) ? $Parsedown->text($row['descripcion']) : '<p class="text-slate-300 italic">Sin descripción técnica registrada.</p>'; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Estado Activo -->
                    <div class="mt-8 pt-8 border-t border-slate-50">
                        <label class="flex items-center gap-4 cursor-pointer group w-fit">
                            <div class="relative">
                                <input type="checkbox" name="edit_activo" class="sr-only peer" <?php echo $row['activo'] == 1 ? 'checked' : ''; ?>>
                                <div class="w-14 h-8 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:start-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-emerald-500"></div>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-sm font-black text-slate-700 uppercase tracking-tight group-hover:text-emerald-600 transition-colors">Figura Activa en Librería</span>
                                <span class="text-[10px] font-bold text-slate-400 uppercase italic">Si se desactiva, no aparecerá para nuevas competiciones</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Botones -->
                <div class="pt-6 flex justify-end gap-4 border-t border-slate-200">
                    <a href="figuras.php" class="px-8 py-4 text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-colors">Descartar</a>
                    <button type="submit" name="update_btn" class="px-12 py-4 bg-blue-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg shadow-blue-500/20 hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
                        <i class="fas fa-save"></i> Actualizar Librería
                    </button>
                </div>

            </form>
        <?php 
            endwhile; 
        endif;
        ?>

    </div>
</main>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
