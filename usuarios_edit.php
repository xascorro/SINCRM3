<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-surface">
    
    <?php include('includes/topbar.php'); ?>

    <!-- Contenido de la Página -->
    <div class="px-3 py-4 sm:p-6 md:p-10 max-w-5xl mx-auto w-full font-lexend">
        
        <!-- Header de Sección -->
        <div class="mb-6 sm:mb-10 flex flex-col sm:flex-row sm:items-end justify-between gap-4 sm:gap-6">
            <div>
                <h1 class="text-2xl sm:text-4xl font-black text-slate-800 tracking-tighter mb-1 sm:mb-2 flex items-center gap-2.5 sm:gap-3">
                    <span class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-user-pen text-base sm:text-lg"></i></span>
                    Editar Perfil
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 font-medium">Modificación de credenciales, avatar y permisos de usuario.</p>
            </div>
            <div class="flex gap-3">
                <a href="usuarios.php" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl sm:rounded-2xl hover:bg-slate-50 transition-all flex items-center gap-2 text-xs tap-press shadow-sm">
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
                $has_photo = !empty($row['foto']) && file_exists(__DIR__ . '/' . $row['foto']);
        ?>
            <form action="usuarios_code.php" method="POST" enctype="multipart/form-data" class="space-y-6 sm:space-y-8 animate-fade-in">
                <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">

                <!-- Bloque 1: Identidad y Avatar -->
                <div class="bg-white rounded-2xl sm:rounded-[2.5rem] p-5 sm:p-8 md:p-10 shadow-sm border border-slate-200 border-t-4 sm:border-t-[6px] border-t-blue-600 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -mr-16 -mt-16 blur-2xl pointer-events-none"></div>
                    <h2 class="text-base sm:text-xl font-black text-slate-800 mb-6 sm:mb-8 flex items-center gap-2.5 sm:gap-3">
                        <i class="fas fa-id-card text-blue-600"></i> Información Personal y Avatar
                    </h2>
                    
                    <!-- Sección de Avatar -->
                    <div class="mb-8 p-4 sm:p-6 bg-slate-50/70 rounded-2xl border border-slate-100 flex flex-col sm:flex-row items-center gap-5 sm:gap-8">
                        <div class="relative shrink-0">
                            <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-full p-1 bg-gradient-to-tr from-blue-600 via-sky-400 to-cyan-300 shadow-xl shadow-blue-500/20">
                                <?php if ($has_photo): ?>
                                    <img id="avatarPreview" src="<?php echo htmlspecialchars($row['foto']); ?>" class="w-full h-full object-cover rounded-full bg-white" alt="Avatar Actual">
                                <?php else: ?>
                                    <img id="avatarPreview" class="hidden w-full h-full object-cover rounded-full bg-white" alt="Nuevo Avatar">
                                    <div id="avatarFallback" class="w-full h-full rounded-full bg-gradient-to-tr from-blue-600 to-cyan-500 flex items-center justify-center text-white font-black text-3xl select-none shadow-inner">
                                        <?php echo strtoupper(substr($row['username'] ?? 'U', 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flex-1 text-center sm:text-left space-y-2">
                            <h3 class="text-sm font-bold text-slate-800">Foto de Perfil / Avatar</h3>
                            <p class="text-xs text-slate-400 leading-relaxed max-w-md">
                                Sube una imagen en formato JPG, PNG, GIF o WEBP. Se mostrará en el login, reportes y barra superior.
                            </p>
                            <div class="pt-2 flex flex-wrap items-center justify-center sm:justify-start gap-3">
                                <label for="fotoInput" class="px-4 py-2 bg-blue-600 text-white font-bold text-xs rounded-xl shadow-sm hover:bg-blue-700 transition-all cursor-pointer flex items-center gap-2 tap-press">
                                    <i class="fas fa-camera text-xs"></i> Seleccionar Avatar
                                </label>
                                <input type="file" name="foto" id="fotoInput" accept="image/*" class="sr-only" onchange="previewAvatar(this)">
                                
                                <?php if ($has_photo): ?>
                                    <label class="flex items-center gap-2 cursor-pointer select-none text-xs font-semibold text-rose-600 hover:text-rose-700">
                                        <input type="checkbox" name="remove_foto" value="1" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500/20">
                                        <span>Eliminar foto actual</span>
                                    </label>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Campos Personales -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div class="space-y-1.5 sm:space-y-2">
                            <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Nombre y Apellidos</label>
                            <input type="text" name="edit_username" value="<?php echo htmlspecialchars($row['username']); ?>" required class="w-full px-4 sm:px-5 py-2.5 sm:py-3.5 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/5 transition-all text-xs sm:text-sm font-bold text-slate-700">
                        </div>
                        <div class="space-y-1.5 sm:space-y-2">
                            <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Correo Electrónico</label>
                            <input type="email" name="edit_email" value="<?php echo htmlspecialchars($row['email']); ?>" required class="w-full px-4 sm:px-5 py-2.5 sm:py-3.5 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/5 transition-all text-xs sm:text-sm font-bold text-slate-700">
                        </div>
                        <div class="space-y-1.5 sm:space-y-2">
                            <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Teléfono de Contacto</label>
                            <input type="text" name="edit_telefono" value="<?php echo htmlspecialchars($row['telefono'] ?? ''); ?>" class="w-full px-4 sm:px-5 py-2.5 sm:py-3.5 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/5 transition-all text-xs sm:text-sm font-bold text-slate-700">
                        </div>
                        <div class="space-y-1.5 sm:space-y-2">
                            <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Estado de Cuenta</label>
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

                <!-- Bloque 2: Permisos y Acceso -->
                <div class="bg-white rounded-2xl sm:rounded-[2.5rem] p-5 sm:p-8 md:p-10 shadow-sm border border-slate-200 border-t-4 sm:border-t-[6px] border-t-purple-500 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-purple-500/5 rounded-full -mr-16 -mt-16 blur-2xl pointer-events-none"></div>
                    <h2 class="text-base sm:text-xl font-black text-slate-800 mb-6 sm:mb-8 flex items-center gap-2.5 sm:gap-3">
                        <i class="fas fa-gears text-purple-600"></i> Permisos y Vinculación
                    </h2>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6">
                        <div class="space-y-1.5 sm:space-y-2">
                            <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Estamento (Rol)</label>
                            <div class="relative">
                                <?php 
                                ob_start();
                                include('./includes/rol_select_option.php');
                                $select_rol = ob_get_clean();
                                $select_rol = preg_replace('/<label.*?>.*?<\/label>/i', '', $select_rol);
                                $select_rol = preg_replace('/class=[\'"].*?[\'"]/i', '', $select_rol);
                                echo str_replace('<select', '<select class="v3-select-fix"', $select_rol);
                                ?>
                            </div>
                        </div>

                        <!-- Vinculación con Perfil de Juez -->
                        <div class="space-y-1.5 sm:space-y-2">
                            <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Ficha de Juez Vinculada</label>
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

                        <!-- Club Vinculado -->
                        <div class="space-y-1.5 sm:space-y-2">
                            <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Club Vinculado</label>
                            <?php
                            $id_club_actual = $row['club'];
                            ob_start();
                            include('./includes/club_select_option.php');
                            $select_club = ob_get_clean();
                            $select_club = preg_replace('/<label.*?>.*?<\/label>/i', '', $select_club);
                            $select_club = preg_replace('/class=[\'"].*?[\'"]/i', '', $select_club);
                            echo str_replace('<select', '<select class="v3-select-fix"', $select_club);
                            ?>
                        </div>
                    </div>
                    
                    <div class="mt-6 space-y-1.5 sm:space-y-2">
                        <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Comentarios / Observaciones</label>
                        <textarea name="edit_comentario" rows="3" class="w-full px-4 sm:px-5 py-3 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-200 focus:border-purple-500 focus:bg-white transition-all text-xs sm:text-sm font-medium text-slate-700" placeholder="Anotaciones internas del usuario..."><?php echo htmlspecialchars($row['comentario'] ?? ''); ?></textarea>
                    </div>
                </div>

                <!-- Bloque 3: Seguridad (Credenciales) -->
                <div class="bg-white rounded-2xl sm:rounded-[2.5rem] p-5 sm:p-8 md:p-10 shadow-sm border border-slate-200 border-t-4 sm:border-t-[6px] border-t-slate-700 relative overflow-hidden">
                    <h2 class="text-base sm:text-xl font-black text-slate-800 mb-2 flex items-center gap-2.5 sm:gap-3">
                        <i class="fas fa-shield-halved text-slate-700"></i> Credenciales de Acceso
                    </h2>
                    <p class="text-xs text-slate-400 mb-6 font-medium">Deja estos campos vacíos si no deseas modificar la contraseña del usuario.</p>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div class="space-y-1.5 sm:space-y-2">
                            <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Nueva Contraseña</label>
                            <input type="password" name="edit_password" autocomplete="new-password" placeholder="••••••••••••" class="w-full px-4 sm:px-5 py-2.5 sm:py-3.5 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-200 focus:border-slate-800 focus:bg-white transition-all text-xs sm:text-sm font-bold text-slate-800 placeholder:text-slate-300">
                        </div>
                        <div class="space-y-1.5 sm:space-y-2">
                            <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Confirmar Contraseña</label>
                            <input type="password" name="edit_r_password" autocomplete="new-password" placeholder="••••••••••••" class="w-full px-4 sm:px-5 py-2.5 sm:py-3.5 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-200 focus:border-slate-800 focus:bg-white transition-all text-xs sm:text-sm font-bold text-slate-800 placeholder:text-slate-300">
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="pt-4 sm:pt-6 flex flex-col sm:flex-row items-center justify-end gap-3 sm:gap-4 border-t border-slate-200">
                    <a href="usuarios.php" class="w-full sm:w-auto px-6 sm:px-8 py-3 text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 text-center transition-colors">
                        Cancelar
                    </a>
                    <button type="submit" name="update_btn" class="w-full sm:w-auto px-8 sm:px-12 py-3.5 sm:py-4 bg-blue-600 text-white font-black uppercase text-xs tracking-widest rounded-xl sm:rounded-2xl shadow-lg shadow-blue-500/20 hover:bg-blue-700 active:scale-[0.98] transition-all flex items-center justify-center gap-2 tap-press">
                        <i class="fas fa-save text-sm"></i> Guardar Cambios
                    </button>
                </div>

            </form>
        <?php 
            endforeach; 
        else:
            header('Location: usuarios.php');
            exit();
        endif;
        ?>

    </div>
</main>

<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('avatarPreview');
            const fallback = document.getElementById('avatarFallback');
            if (preview) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
            }
            if (fallback) {
                fallback.classList.add('hidden');
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php 
include('includes/scripts.php'); 
include('includes/footer.php'); 
?>
