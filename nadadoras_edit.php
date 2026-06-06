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
                    <span class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-person-swimming text-lg"></i></span>
                    Ficha Deportista
                </h1>
                <p class="text-slate-500 font-medium">Actualización de expediente y vinculación técnica.</p>
            </div>
            <div class="flex gap-3">
                <a href="nadadoras.php" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition-all flex items-center gap-2">
                    <i class="fas fa-arrow-left text-xs"></i> Volver
                </a>
            </div>
        </div>

        <?php
        if(isset($_POST['edit_btn'])):
            $id = mysqli_real_escape_string($connection, $_POST['edit_id']);
            
            // Seguridad: Si es Rol Club (5), verificar que la nadadora le pertenece
            if ($_SESSION['id_rol'] == 5) {
                $q_permiso = "SELECT club FROM nadadoras WHERE id = '$id'";
                $res_permiso = mysqli_query($connection, $q_permiso);
                $row_permiso = mysqli_fetch_assoc($res_permiso);
                if (!$row_permiso || $row_permiso['club'] != $_SESSION['club']) {
                    write_log("Intento de acceso no autorizado a Ficha Deportista ID $id por usuario " . $_SESSION['username'], "SECURITY");
                    $_SESSION['estado'] = 'Acceso denegado. No tienes permisos para editar esta deportista.';
                    echo "<script>window.location.href = 'nadadoras.php';</script>";
                    exit();
                }
            }

            $query = "SELECT * FROM nadadoras WHERE id = '$id'";
            $query_run = mysqli_query($connection, $query);
            foreach ($query_run as $row):
        ?>
            <form action="nadadoras_code.php" method="POST" class="animate-fade-in space-y-8">
                <input type="hidden" name="edit_id" value="<?php echo $row['id']?>">
                <input type="hidden" name="id_nadadora" value="<?php echo $row['id']?>">

                <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-sm border border-slate-200 border-t-[6px] border-t-blue-600 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
                        <div class="md:col-span-4 space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Nombre</label>
                            <input type="text" name="edit_nombre" value="<?php echo $row['nombre']?>" required class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold text-slate-700 shadow-inner">
                        </div>
                        <div class="md:col-span-8 space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Apellidos</label>
                            <input type="text" name="edit_apellidos" value="<?php echo $row['apellidos']?>" required class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold text-slate-700 shadow-inner">
                        </div>
                        
                        <div class="md:col-span-4 space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Licencia / DNI</label>
                            <input type="text" name="edit_licencia" value="<?php echo $row['licencia']?>" class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold text-slate-700">
                        </div>
                        <div class="md:col-span-4 space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Año Nacimiento</label>
                            <div class="relative">
                                <?php 
                                $año_nacimiento_actual = $row['año_nacimiento'];
                                ob_start();
                                include('./includes/año_select_option.php');
                                echo str_replace('class="form-control"', 'class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 appearance-none text-sm font-bold text-slate-700"', ob_get_clean());
                                ?>
                                <div class="absolute right-5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-300"><i class="fas fa-calendar text-xs"></i></div>
                            </div>
                        </div>
                        <div class="md:col-span-4 space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Estado</label>
                            <div class="flex items-center h-full pt-2">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="activo" value="1" <?php echo ($row['activo'] == 1) ? 'checked' : ''; ?> class="sr-only peer">
                                    <div class="w-14 h-7 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-emerald-500"></div>
                                    <span class="ml-3 text-xs font-black uppercase tracking-widest text-slate-400 peer-checked:text-emerald-600"><?php echo ($row['activo'] == 1) ? 'ACTIVA' : 'BAJA'; ?></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Club y Notas -->
                <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-sm border border-slate-200 border-l-[6px] border-l-purple-500">
                    <h2 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3"><i class="fas fa-shield-halved text-purple-600"></i> Vinculación Deportiva</h2>
                    <div class="space-y-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Club de Pertenencia</label>
                            <div class="relative">
                                <?php 
                                if ($_SESSION['id_rol'] == 5):
                                    // Club usuario está bloqueado para su propio club
                                    echo "<input type='hidden' name='club' value='".$_SESSION['club']."'>";
                                    echo "<div class='w-full px-5 py-4 rounded-2xl bg-slate-100 border border-slate-200 text-sm font-bold text-slate-500 shadow-inner flex items-center gap-2'><i class='fas fa-lock opacity-50'></i> ".$_SESSION['nombre_club']."</div>";
                                else:
                                    $id_club_actual = $row['club'];
                                    ob_start();
                                    include('./includes/club_select_option.php');
                                    echo str_replace('class="form-control"', 'class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-purple-500 appearance-none text-sm font-bold text-slate-700"', ob_get_clean());
                                    echo "<div class='absolute right-5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-300'><i class='fas fa-chevron-down text-xs'></i></div>";
                                endif;
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-6 flex justify-end gap-4 border-t border-slate-100">
                    <a href="nadadoras.php" class="px-8 py-4 text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-colors">Cancelar</a>
                    <button type="submit" name="update_btn" class="px-12 py-4 bg-blue-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg shadow-blue-500/20 hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
                        <i class="fas fa-save"></i> Guardar Ficha
                    </button>
                </div>
            </form>
        <?php 
            endforeach; 
        endif;
        ?>

    </div>
</main>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
