<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-surface">
    
    <?php include('includes/topbar.php'); ?>

    <!-- Contenido de la Página -->
    <div class="p-6 md:p-10 max-w-4xl mx-auto w-full">
        
        <!-- Header de Sección -->
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <h1 class="text-4xl font-black text-slate-800 tracking-tighter mb-2 flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-user-shield text-lg"></i></span>
                    Editar Rol
                </h1>
                <p class="text-slate-500 font-medium">Gestión de jerarquía y niveles de acceso.</p>
            </div>
            <div class="flex gap-3">
                <a href="roles.php" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition-all flex items-center gap-2">
                    <i class="fas fa-arrow-left text-xs"></i> Volver
                </a>
            </div>
        </div>

        <?php
        if(isset($_POST['edit_btn'])):
            $id = mysqli_real_escape_string($connection, $_POST['edit_id']);
            $query = "SELECT * FROM roles WHERE id = '$id'";
            $query_run = mysqli_query($connection, $query);
            foreach ($query_run as $row):
        ?>
            <form action="roles_code.php" method="POST" class="animate-fade-in">
                <input type="hidden" name="edit_id" value="<?php echo $row['id']?>">

                <div class="bg-white rounded-[2.5rem] p-8 md:p-12 shadow-sm border border-slate-200 border-t-[6px] border-t-emerald-500 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/5 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Nombre del Rol</label>
                            <input type="text" name="edit_nombre" value="<?php echo $row['nombre']?>" required class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/5 transition-all text-sm font-bold text-slate-700 shadow-inner">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Nivel de Acceso</label>
                            <input type="number" name="edit_level" value="<?php echo $row['level']?>" required class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/5 transition-all text-sm font-bold text-slate-700 shadow-inner" placeholder="0-100">
                        </div>
                    </div>

                    <div class="mt-12 p-6 bg-emerald-50 rounded-2xl border border-emerald-100">
                        <p class="text-xs text-emerald-800 font-medium leading-relaxed italic">
                            <i class="fas fa-info-circle mr-2 opacity-50"></i> 
                            Los roles definen qué partes del sistema son accesibles para el usuario. Un nivel 100 equivale a SuperAdministrador.
                        </p>
                    </div>

                    <div class="mt-12 pt-8 border-t border-slate-100 flex items-center justify-between">
                        <div class="text-xs font-medium text-slate-400">Referencia de sistema: ROL_#<?php echo $row['id']; ?></div>
                        <button type="submit" name="update_btn" class="px-10 py-4 bg-emerald-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg shadow-emerald-500/20 hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </div>
            </form>
        <?php 
            endforeach; 
        else:
            header('Location: roles.php');
        endif;
        ?>

    </div>
</main>

<?php 
include('includes/scripts.php'); 
include('includes/footer.php'); 
?>
