<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-surface">
    
    <?php include('includes/topbar.php'); ?>

    <!-- Contenido de la Página -->
    <div class="p-6 md:p-10 max-w-5xl mx-auto w-full font-lexend">
        
        <!-- Header de Sección -->
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <h1 class="text-4xl font-black text-slate-800 tracking-tighter mb-2 flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-edit text-lg"></i></span>
                    Editar Federación
                </h1>
                <p class="text-slate-500 font-medium">Actualización de datos institucionales y escudo.</p>
            </div>
            <div class="flex gap-3">
                <a href="federaciones.php" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition-all flex items-center gap-2 shadow-sm">
                    <i class="fas fa-arrow-left text-xs"></i> Volver al listado
                </a>
            </div>
        </div>

        <?php
        if(isset($_POST['edit_btn'])):
            $id = mysqli_real_escape_string($connection, $_POST['edit_id']);
            $query = "SELECT * FROM federaciones WHERE id = '$id'";
            $query_run = mysqli_query($connection, $query);
            while ($row = mysqli_fetch_assoc($query_run)):
                $logo = !empty($row['logo']) ? $row['logo'] : 'img/undraw_posting_photo.svg';
        ?>
            <form action="federaciones_code.php" method="POST" enctype="multipart/form-data" class="space-y-8 animate-fade-in">
                <input type="hidden" name="edit_id" value="<?php echo $row['id']?>">

                <!-- Bloque: Identidad y Escudo -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    <!-- Escudo -->
                    <div class="lg:col-span-4">
                        <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-200 flex flex-col items-center text-center h-full border-t-[6px] border-t-blue-600">
                            <h3 class="text-xs font-black uppercase text-slate-400 tracking-widest mb-8">Logo Oficial</h3>
                            <div class="w-40 h-40 rounded-3xl bg-slate-50 p-6 border border-slate-100 mb-8 flex items-center justify-center shadow-inner overflow-hidden group">
                                <img src="<?php echo $logo; ?>" class="max-h-full max-w-full object-contain transition-transform group-hover:scale-110 duration-500">
                            </div>
                            <div class="space-y-4 w-full text-left">
                                <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Cambiar Imagen</label>
                                <input type="file" name="edit_logo" accept="image/*" class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 border border-slate-100 text-[10px] font-bold file:mr-3 file:py-1 file:px-3 file:rounded-full file:border-0 file:bg-blue-600 file:text-white">
                            </div>
                        </div>
                    </div>

                    <!-- Datos Generales -->
                    <div class="lg:col-span-8">
                        <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-sm border border-slate-200 h-full relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                            <h2 class="text-xl font-black text-slate-800 mb-10 flex items-center gap-3"><i class="fas fa-info-circle text-blue-600"></i> Información Institucional</h2>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="md:col-span-2 space-y-2">
                                    <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Nombre Completo</label>
                                    <input type="text" name="edit_nombre" value="<?php echo $row['nombre']?>" required class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all text-sm font-bold text-slate-700 shadow-inner">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Siglas</label>
                                    <input type="text" name="edit_nombre_corto" value="<?php echo $row['nombre_corto']?>" required class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold text-slate-700 shadow-inner">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Código de Sistema</label>
                                    <input type="text" name="edit_codigo" value="<?php echo $row['codigo']?>" class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold text-slate-700 shadow-inner">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="pt-6 flex justify-end gap-4 border-t border-slate-200">
                    <a href="federaciones.php" class="px-8 py-4 text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-colors">Descartar</a>
                    <button type="submit" name="update_btn" class="px-12 py-4 bg-blue-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg shadow-blue-500/20 hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
                        <i class="fas fa-save"></i> Guardar Cambios
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
