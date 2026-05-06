<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-surface">
    
    <?php include('includes/topbar.php'); ?>

    <!-- Contenido de la Página -->
    <div class="p-6 md:p-10 max-w-5xl mx-auto w-full">
        
        <!-- Header de Sección -->
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <h1 class="text-4xl font-black text-slate-800 tracking-tighter mb-2 flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-shield-halved text-lg"></i></span>
                    Editar Club
                </h1>
                <p class="text-slate-500 font-medium">Actualización de datos y escudo de la entidad.</p>
            </div>
            <div class="flex gap-3">
                <a href="clubes.php" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition-all flex items-center gap-2 shadow-sm">
                    <i class="fas fa-arrow-left text-xs"></i> Volver al listado
                </a>
            </div>
        </div>

        <?php
        if(isset($_POST['edit_btn'])):
            $id = mysqli_real_escape_string($connection, $_POST['edit_id']);
            $query = "SELECT * FROM clubes WHERE id = '$id'";
            $query_run = mysqli_query($connection, $query);

            if(!$query_run || mysqli_num_rows($query_run) == 0):
                echo '<div class="p-10 text-center"><h2 class="text-2xl font-black text-slate-400 mb-4">Error: Registro no encontrado</h2><p class="text-slate-500 mb-8">El club solicitado no existe o no se ha seleccionado correctamente.</p><a href="clubes.php" class="px-8 py-3 bg-slate-800 text-white font-black rounded-xl uppercase text-xs tracking-widest">Volver al listado</a></div>';
            else:
                while ($row = mysqli_fetch_assoc($query_run)):
                    $logo = !empty($row['logo']) ? $row['logo'] : 'img/undraw_posting_photo.svg';
                    $activo = isset($row['activo']) ? $row['activo'] : 1;
        ?>
            <form action="clubes_code.php" method="POST" enctype="multipart/form-data" class="space-y-8 animate-fade-in">
                <input type="hidden" name="edit_id" value="<?php echo $row['id']?>">
                <input type="hidden" name="old_logo" value="<?php echo $row['logo']; ?>">

                <!-- Bloque: Identidad y Escudo -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    <!-- Escudo -->
                    <div class="lg:col-span-4">
                        <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-200 flex flex-col items-center text-center h-full border-t-[6px] border-t-blue-600">
                            <h3 class="text-xs font-black uppercase text-slate-400 tracking-widest mb-8">Escudo Actual</h3>
                            <div class="w-40 h-40 rounded-3xl bg-slate-50 p-6 border border-slate-100 mb-8 flex items-center justify-center shadow-inner overflow-hidden group">
                                <img src="<?php echo $logo; ?>" class="max-h-full max-w-full object-contain transition-transform group-hover:scale-110 duration-500">
                            </div>
                            <div class="space-y-4 w-full text-left">
                                <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Cambiar Imagen</label>
                                <input type="file" name="edit_logo" accept="image/*" class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 border border-slate-100 text-[10px] font-bold file:mr-3 file:py-1 file:px-3 file:rounded-full file:border-0 file:bg-blue-600 file:text-white">
                                <p class="text-[9px] text-slate-400 italic px-1">PNG o SVG recomendado.</p>
                            </div>

                            <!-- Estado Activo/Inactivo -->
                            <div class="mt-8 pt-8 border-t border-slate-50 w-full">
                                <label class="flex items-center justify-between cursor-pointer group">
                                    <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Estado del Club</span>
                                    <div class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="activo" value="1" class="sr-only peer" <?php echo ($activo == 1) ? 'checked' : ''; ?>>
                                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                                    </div>
                                </label>
                                <p class="text-[10px] text-slate-400 mt-2 text-left italic"><?php echo ($activo == 1) ? 'El club está visible y operativo.' : 'El club está desactivado.'; ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Datos Generales -->
                    <div class="lg:col-span-8">
                        <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-sm border border-slate-200 h-full relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                            <h2 class="text-xl font-black text-slate-800 mb-10 flex items-center gap-3"><i class="fas fa-info-circle text-blue-600"></i> Información Técnica</h2>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="md:col-span-2 space-y-2">
                                    <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Nombre Completo de la Entidad</label>
                                    <input type="text" name="edit_nombre" value="<?php echo $row['nombre']?>" required class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all text-sm font-bold text-slate-700 shadow-inner">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Siglas / Nombre Corto</label>
                                    <input type="text" name="edit_nombre_corto" value="<?php echo $row['nombre_corto']?>" required class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold text-slate-700">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Código RFEN</label>
                                    <input type="text" name="edit_codigo" value="<?php echo $row['codigo']?>" class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold text-slate-700">
                                </div>
                                <div class="md:col-span-2 space-y-2">
                                    <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest text-blue-600">Federación Autonómica</label>
                                    <div class="relative">
                                        <?php 
                                        $id_fed_actual = $row['federacion'];
                                        ob_start();
                                        include('./includes/federacion_select_option.php');
                                        $fed_html = ob_get_clean();
                                        $fed_html = str_replace('<select', '<select name="federacion"', $fed_html);
                                        $fed_html = str_replace('class="form-control"', 'class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-blue-100 focus:border-blue-500 transition-all text-sm font-bold appearance-none text-blue-700"', $fed_html);
                                        echo $fed_html;
                                        ?>
                                        <div class="absolute right-5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-300"><i class="fas fa-chevron-down text-xs"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="pt-6 flex flex-col md:flex-row md:items-center justify-between gap-6 border-t border-slate-200">
                    <div class="text-[10px] font-black uppercase text-slate-400 tracking-widest">ID Registro: #<?php echo $row['id']; ?></div>
                    <div class="flex gap-4">
                        <a href="clubes.php" class="px-8 py-4 text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-colors">Cancelar</a>
                        <button type="submit" name="update_btn" class="px-12 py-4 bg-blue-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg shadow-blue-500/20 hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </div>

            </form>
        <?php 
                endwhile; 
            endif;
        else:
            echo '<div class="p-10 text-center"><h2 class="text-2xl font-black text-slate-400 mb-4">Acceso directo no permitido</h2><p class="text-slate-500 mb-8">Por favor, selecciona un club del listado para editar.</p><a href="clubes.php" class="px-8 py-3 bg-slate-800 text-white font-black rounded-xl uppercase text-xs tracking-widest">Ir al listado</a></div>';
        endif;
        ?>

    </div>
</main>

<?php include('includes/scripts.php'); ?>
<?php include('includes/footer.php'); ?>
