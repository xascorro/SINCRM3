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
                    <span class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-user-pen text-lg"></i></span>
                    Editar Perfil
                </h1>
                <p class="text-slate-500 font-medium">Modificación de credenciales y permisos de usuario.</p>
            </div>
            <div class="flex gap-3">
                <a href="usuarios.php" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition-all flex items-center gap-2">
                    <i class="fas fa-arrow-left text-xs"></i> Volver al listado
                </a>
            </div>
        </div>

        <?php
        if(isset($_POST['edit_btn'])):
            $id = mysqli_real_escape_string($connection, $_POST['edit_id']);
            $query = "SELECT * FROM usuarios WHERE id = '$id'";
            $query_run = mysqli_query($connection, $query);
            foreach ($query_run as $row):
        ?>
            <form action="usuarios_code.php" method="POST" class="space-y-8 animate-fade-in">
                <input type="hidden" name="edit_id" value="<?php echo $row['id']?>">

                <!-- Bloque: Datos de Identidad -->
                <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-sm border border-slate-200 border-t-[6px] border-t-blue-600 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                    <h2 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3"><i class="fas fa-id-card text-blue-600"></i> Información Personal</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Nombre y Apellidos</label>
                            <input type="text" name="edit_username" value="<?php echo $row['username']?>" required class="w-full px-5 py-3.5 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold text-slate-700">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Correo Electrónico</label>
                            <input type="email" name="edit_email" value="<?php echo $row['email']?>" required class="w-full px-5 py-3.5 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold text-slate-700">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Teléfono de Contacto</label>
                            <input type="text" name="edit_telefono" value="<?php echo $row['telefono']?>" class="w-full px-5 py-3.5 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold text-slate-700">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Estado de Cuenta</label>
                            <div class="flex items-center gap-4 px-2 py-2">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="activo" value="1" <?php echo $row['activo'] ? 'checked' : ''; ?> class="sr-only peer">
                                    <div class="w-14 h-7 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-emerald-500"></div>
                                    <span class="ml-3 text-xs font-black uppercase tracking-widest text-slate-400 peer-checked:text-emerald-600"><?php echo $row['activo'] ? 'ACTIVO' : 'BAJA'; ?></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bloque: Configuración de Sistema -->
                <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-sm border border-slate-200 border-l-[6px] border-l-purple-500">
                    <h2 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3"><i class="fas fa-gears text-purple-600"></i> Permisos y Acceso</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Estamento (Rol)</label>
                            <div class="relative">
                                <?php 
                                ob_start();
                                include('./includes/rol_select_option.php');
                                $select_rol = ob_get_clean();
                                // Limpieza agresiva del include antiguo
                                $select_rol = preg_replace('/<label.*?>.*?<\/label>/i', '', $select_rol);
                                $select_rol = preg_replace('/class=[\'"].*?[\'"]/i', '', $select_rol);
                                echo str_replace('<select', '<select class="v3-select-fix"', $select_rol);
                                ?>
                            </div>
                        </div>

                        <!-- VINCULACIÓN CON PERFIL DE JUEZ -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Perfil Juez Vinculado</label>
                            <div class="relative">
                                <select name="id_juez_v3" class="v3-select-fix">
                                    <option value="">Ninguno / No es Juez</option>
                                    <?php
                                    $q_jueces = "SELECT id, nombre, apellidos FROM jueces ORDER BY nombre ASC";
                                    $res_jueces = mysqli_query($connection, $q_jueces);
                                    while($j = mysqli_fetch_assoc($res_jueces)){
                                        $selected = ($row['id_juez_v3'] == $j['id']) ? 'selected' : '';
                                        echo "<option value='".$j['id']."' $selected>".$j['nombre']." ".$j['apellidos']."</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Club Vinculado</label>
                            <?php
                            $id_club_actual = $row['club'];
                            ob_start();
                            include('./includes/club_select_option.php');
                            $select_club = ob_get_clean();
                            // Limpieza agresiva del include antiguo
                            $select_club = preg_replace('/<label.*?>.*?<\/label>/i', '', $select_club);
                            $select_club = preg_replace('/class=[\'"].*?[\'"]/i', '', $select_club);
                            echo str_replace('<select', '<select class="v3-select-fix"', $select_club);
                            ?>
                        </div>
                    </div>
                    <div class="mt-8 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Comentarios / Notas</label>
                        <textarea name="edit_comentario" rows="3" class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-purple-500 transition-all text-sm font-medium text-slate-600"><?php echo $row['comentario'];?></textarea>
                    </div>
                </div>

                <!-- Bloque: Seguridad -->
                <div class="bg-slate-900 rounded-[2.5rem] p-8 md:p-10 shadow-xl border border-slate-800">
                    <h2 class="text-xl font-black text-white mb-8 flex items-center gap-3"><i class="fas fa-shield-halved text-red-500"></i> Seguridad</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-500 px-1 tracking-widest">Nueva Contraseña</label>
                            <input type="password" name="edit_password" placeholder="Sin cambios..." class="w-full px-5 py-3.5 rounded-2xl bg-white/5 border border-white/10 focus:border-red-500 transition-all text-sm font-bold text-white placeholder:text-slate-700">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-500 px-1 tracking-widest">Confirmar</label>
                            <input type="password" name="edit_r_password" placeholder="Confirmar..." class="w-full px-5 py-3.5 rounded-2xl bg-white/5 border border-white/10 focus:border-red-500 transition-all text-sm font-bold text-white placeholder:text-slate-700">
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="pt-6 flex justify-end gap-4 border-t border-slate-200">
                    <a href="usuarios.php" class="px-8 py-4 text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-colors">Cancelar</a>
                    <button type="submit" name="update_btn" class="px-10 py-4 bg-blue-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg shadow-blue-500/20 hover:scale-105 active:scale-95 transition-all">
                        Guardar Cambios
                    </button>
                </div>

            </form>
        <?php 
            endforeach; 
        endif;
        ?>

    </div>
</main>

<?php 
include('includes/scripts.php'); 
include('includes/footer.php'); 
?>
