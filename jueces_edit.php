<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');
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
                    <span class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-user-pen text-lg"></i></span>
                    Editar Juez
                </h1>
                <p class="text-slate-500 font-medium">Actualización de expediente técnico y vinculación territorial.</p>
            </div>
            <div class="flex gap-3">
                <a href="jueces.php" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition-all flex items-center gap-2 shadow-sm">
                    <i class="fas fa-arrow-left text-xs"></i> Volver al censo
                </a>
            </div>
        </div>

        <?php
        if(isset($_POST['edit_btn'])):
            $id = mysqli_real_escape_string($connection, $_POST['edit_id']);
            $query_juez = "SELECT * FROM jueces WHERE id = '$id'";
            $res_juez = mysqli_query($connection, $query_juez);
            
            if(mysqli_num_rows($res_juez) > 0):
                while($row_juez = mysqli_fetch_assoc($res_juez)):
        ?>
            <form action="jueces_code.php" method="POST" class="animate-fade-in space-y-8">
                <input type="hidden" name="edit_id" value="<?php echo $row_juez['id']?>">

                <!-- Bloque: Identidad -->
                <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-sm border border-slate-200 border-t-[6px] border-t-blue-600 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                    <h2 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3"><i class="fas fa-id-card text-blue-600"></i> Información de Identidad</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Nombre</label>
                            <input type="text" name="edit_nombre" value="<?php echo $row_juez['nombre']?>" required class="w-full px-5 py-3.5 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all text-sm font-bold text-slate-700 shadow-inner">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Apellidos</label>
                            <input type="text" name="edit_apellidos" value="<?php echo $row_juez['apellidos']?>" required class="w-full px-5 py-3.5 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all text-sm font-bold text-slate-700 shadow-inner">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Número de Licencia</label>
                            <input type="text" name="edit_licencia" value="<?php echo $row_juez['licencia']?>" class="w-full px-5 py-3.5 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold text-slate-700">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Federación</label>
                            <?php 
                            $_POST['id_federacion'] = $row_juez['federacion'];
                            ob_start();
                            include('./includes/federacion_select_option.php');
                            $select_fed = ob_get_clean();
                            $select_fed = preg_replace('/<label.*?>.*?<\/label>/i', '', $select_fed);
                            $select_fed = preg_replace('/class=[\'"].*?[\'"]/i', '', $select_fed);
                            echo str_replace('<select', '<select name="edit_federacion" class="v3-select-fix"', $select_fed);
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Bloque: Estado de Disponibilidad -->
                <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-sm border border-slate-200 border-l-[6px] border-l-emerald-500">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-8">
                        <div class="flex-1 text-center md:text-left">
                            <h2 class="text-xl font-black text-slate-800 mb-2 flex items-center gap-3 justify-center md:justify-start">
                                <i class="fas fa-toggle-on text-emerald-600"></i> Estado de la Ficha
                            </h2>
                            <p class="text-xs text-slate-400 font-medium italic">Si el juez está de baja, no aparecerá en los selectores de paneles ni puntuaciones.</p>
                        </div>
                        <div class="flex items-center gap-4 px-6 py-4 bg-slate-50 rounded-2xl border border-slate-100 shadow-inner">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="activo" value="1" <?php echo ($row_juez['activo'] == 1) ? 'checked' : ''; ?> class="sr-only peer">
                                <div class="w-14 h-7 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-emerald-500 shadow-sm"></div>
                                <span class="ml-3 text-xs font-black uppercase tracking-widest text-slate-400 peer-checked:text-emerald-600"><?php echo ($row_juez['activo'] == 1) ? 'ACTIVO' : 'BAJA'; ?></span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="pt-6 flex justify-end gap-4 border-t border-slate-200">
                    <a href="jueces.php" class="px-8 py-4 text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-colors">Cancelar</a>
                    <button type="submit" name="update_btn" class="px-12 py-4 bg-blue-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg shadow-blue-500/20 hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>

            </form>
        <?php 
                endwhile;
            endif;
        endif;
        ?>
    </div>
</main>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
