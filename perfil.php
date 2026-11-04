<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');

$user_id = intval($_SESSION['id_usario'] ?? 0);
$query = "SELECT u.*, r.nombre as rol_nombre, c.nombre as club_nombre, c.nombre_corto as club_corto, c.logo as club_logo 
          FROM usuarios u 
          LEFT JOIN roles r ON u.id_rol = r.id 
          LEFT JOIN clubes c ON u.club = c.id 
          WHERE u.id = '$user_id' LIMIT 1";
$query_run = mysqli_query($connection, $query);
$user_data = mysqli_fetch_assoc($query_run) ?: [];
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-surface">
    
    <?php include('includes/topbar.php'); ?>

    <!-- Contenido de la Página -->
    <div class="px-3 py-4 sm:p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend text-primary">
        
        <!-- Header de Sección -->
        <div class="mb-6 sm:mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-6">
            <div>
                <h1 class="text-xl sm:text-2xl md:text-3xl font-black text-slate-800 tracking-tight flex items-center gap-2.5 sm:gap-3">
                    <span class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shadow-xs border border-blue-100">
                        <i class="fas fa-id-badge text-sm sm:text-base"></i>
                    </span>
                    Mi Perfil
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 font-medium mt-1">Controla tu identidad, credenciales de acceso y actividad en la plataforma.</p>
            </div>
            <a href="log_usuario.php" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-700 text-xs sm:text-sm font-bold rounded-xl hover:bg-slate-50 active:scale-[0.98] transition-all shadow-xs self-start sm:self-auto">
                <i class="fas fa-history text-xs text-blue-500"></i> Ver Mi Actividad
            </a>
        </div>

        <!-- Alertas -->
        <?php include('includes/alertas_v4.php'); ?>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 items-start">
            
            <!-- Columna Resumen (En móvil aparece arriba gracias a order-1) -->
            <div class="order-1 lg:order-2 lg:col-span-4 space-y-6">
                <!-- Tarjeta Resumen de Usuario -->
                <div class="bg-white rounded-2xl sm:rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200/80 text-center relative overflow-hidden">
                    <div class="absolute -top-12 -right-12 w-32 h-32 bg-blue-500/5 rounded-full blur-2xl pointer-events-none"></div>
                    
                    <!-- Avatar / Foto -->
                    <div class="relative inline-block mx-auto mb-4">
                        <?php if (!empty($user_data['foto']) && file_exists($user_data['foto'])): ?>
                            <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl sm:rounded-3xl mx-auto shadow-md border-4 border-white overflow-hidden ring-1 ring-slate-200/60">
                                <img src="<?php echo htmlspecialchars($user_data['foto']); ?>" alt="Foto de perfil" class="w-full h-full object-cover">
                            </div>
                        <?php else: ?>
                            <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl sm:rounded-3xl bg-gradient-to-br from-blue-500 to-indigo-600 mx-auto flex items-center justify-center text-white text-3xl font-black shadow-lg shadow-blue-500/20 border-4 border-white ring-1 ring-slate-200/60">
                                <?php echo strtoupper(substr($user_data['username'] ?: 'U', 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        <span class="absolute bottom-1 right-1 w-5 h-5 bg-emerald-500 border-2 border-white rounded-full" title="Usuario activo"></span>
                    </div>

                    <h3 class="text-base sm:text-lg font-black text-slate-800 leading-snug truncate">
                        <?php echo htmlspecialchars($user_data['username'] ?? 'Usuario'); ?>
                    </h3>
                    <p class="text-xs text-slate-400 font-medium truncate mt-0.5"><?php echo htmlspecialchars($user_data['email'] ?? ''); ?></p>

                    <!-- Badges de Rol y Club -->
                    <div class="flex flex-wrap items-center justify-center gap-2 mt-4">
                        <span class="px-3 py-1 bg-blue-50 text-blue-700 text-[10px] font-black uppercase tracking-wider rounded-full border border-blue-100 flex items-center gap-1.5">
                            <i class="fas fa-shield-alt text-[9px]"></i> <?php echo htmlspecialchars($user_data['rol_nombre'] ?? 'Sin rol'); ?>
                        </span>
                        <?php if (!empty($user_data['club_corto'])): ?>
                            <span class="px-3 py-1 bg-indigo-50 text-indigo-700 text-[10px] font-black uppercase tracking-wider rounded-full border border-indigo-100 flex items-center gap-1.5">
                                <i class="fas fa-shield-halved text-[9px]"></i> <?php echo htmlspecialchars($user_data['club_corto']); ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Métricas de Cuenta -->
                    <div class="grid grid-cols-2 gap-2 pt-6 mt-6 border-t border-slate-100 text-left">
                        <div class="p-3 bg-slate-50/70 rounded-xl border border-slate-100">
                            <span class="block text-[9px] font-black uppercase text-slate-400">Estado</span>
                            <span class="text-xs font-black text-emerald-600 flex items-center gap-1 mt-0.5">
                                <i class="fas fa-circle-check text-[10px]"></i> Verificada
                            </span>
                        </div>
                        <div class="p-3 bg-slate-50/70 rounded-xl border border-slate-100">
                            <span class="block text-[9px] font-black uppercase text-slate-400">ID Usuario</span>
                            <span class="text-xs font-black text-slate-700 tabular-nums font-mono mt-0.5">
                                #<?php echo $user_id; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Actividad Reciente del Usuario -->
                <div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-6 shadow-sm border border-slate-200/80">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                        <h3 class="text-xs font-black text-slate-700 uppercase tracking-wider flex items-center gap-2">
                            <i class="fas fa-history text-blue-500"></i> Últimas Acciones
                        </h3>
                        <a href="log_usuario.php" class="text-[10px] font-black uppercase tracking-wider text-blue-600 hover:text-blue-800 transition-colors">
                            Ver Todo &rarr;
                        </a>
                    </div>
                    
                    <div class="space-y-3 max-h-[360px] overflow-y-auto no-scrollbar pr-1">
                        <?php
                        $log_file = './log/log.txt';
                        $user_email = $user_data['email'] ?? '';
                        $found_logs = 0;
                        
                        if (file_exists($log_file) && !empty($user_email)) {
                            $lines = @file($log_file);
                            if ($lines) {
                                $lines = array_reverse($lines);
                                foreach ($lines as $line) {
                                    if (strpos($line, $user_email) !== false) {
                                        $found_logs++;
                                        if (preg_match('/^\[(.*?)\] \[(.*?)\] \[(.*?)\] \[(.*?)\] (.*)$/', $line, $matches)) {
                                            $date = date("d/m H:i", strtotime($matches[1]));
                                            $level = $matches[2];
                                            $msg = $matches[5];
                                            
                                            $icon = 'fa-info text-blue-600';
                                            $bg = 'bg-blue-50 border-blue-100';
                                            if($level == 'SUCCESS') { $icon = 'fa-check text-emerald-600'; $bg = 'bg-emerald-50 border-emerald-100'; }
                                            if($level == 'ERROR') { $icon = 'fa-triangle-exclamation text-rose-600'; $bg = 'bg-rose-50 border-rose-100'; }
                                            if($level == 'WARNING' || $level == 'SECURITY') { $icon = 'fa-shield text-amber-600'; $bg = 'bg-amber-50 border-amber-100'; }
                        ?>
                                            <div class="flex items-start gap-3 p-2.5 rounded-xl hover:bg-slate-50 transition-colors">
                                                <div class="shrink-0 w-7 h-7 rounded-lg <?php echo $bg; ?> border flex items-center justify-center text-[10px] mt-0.5 shadow-2xs">
                                                    <i class="fas <?php echo $icon; ?>"></i>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-xs font-bold text-slate-800 leading-snug truncate" title="<?php echo htmlspecialchars($msg); ?>">
                                                        <?php echo htmlspecialchars($msg); ?>
                                                    </p>
                                                    <span class="text-[9px] font-bold text-slate-400 tabular-nums uppercase"><?php echo $date; ?></span>
                                                </div>
                                            </div>
                        <?php
                                        }
                                        if ($found_logs >= 5) break;
                                    }
                                }
                            }
                        }
                        
                        if ($found_logs == 0) {
                            echo '<div class="text-[11px] text-slate-400 p-6 bg-slate-50 rounded-xl text-center font-bold">No hay registros recientes para tu cuenta.</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- Columna Formulario de Edición -->
            <div class="order-2 lg:order-1 lg:col-span-8 space-y-6">
                <form action="perfil_code.php" method="POST" enctype="multipart/form-data" class="animate-fade-in space-y-6">
                    
                    <!-- BLOQUE 1: DATOS PERSONALES -->
                    <div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-8 shadow-sm border border-slate-200/80 border-t-4 border-t-blue-600">
                        <h2 class="text-base sm:text-lg font-black text-slate-800 mb-5 sm:mb-6 flex items-center gap-2.5">
                            <i class="fas fa-user text-blue-600"></i> Datos Personales y Contacto
                        </h2>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                            <div class="space-y-1.5 sm:col-span-2">
                                <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Nombre Completo</label>
                                <input type="text" name="username" value="<?php echo htmlspecialchars($user_data['username'] ?? ''); ?>" required 
                                       class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 focus:border-blue-500 focus:bg-white transition-all text-xs sm:text-sm font-bold text-slate-800 outline-none">
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Email de Acceso</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" required 
                                       class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 focus:border-blue-500 focus:bg-white transition-all text-xs sm:text-sm font-bold text-slate-800 outline-none">
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Teléfono de Contacto</label>
                                <input type="text" name="telefono" value="<?php echo htmlspecialchars($user_data['telefono'] ?? ''); ?>" placeholder="ej: 600123456" 
                                       class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 focus:border-blue-500 focus:bg-white transition-all text-xs sm:text-sm font-bold text-slate-800 outline-none tabular-nums">
                            </div>

                            <!-- Selector de Foto de Perfil -->
                            <div class="space-y-1.5 sm:col-span-2 pt-2">
                                <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Actualizar Foto de Perfil</label>
                                <div class="flex items-center gap-3">
                                    <input type="file" name="foto" id="fotoInput" accept="image/*" 
                                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-slate-700 outline-none focus:border-blue-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-black file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 file:transition-colors">
                                </div>
                                <span class="text-[10px] text-slate-400 px-1 block">Formatos permitidos: JPG, PNG, WEBP o GIF (máx. 2MB).</span>
                            </div>

                            <!-- Rol Asignado (Solo lectura) -->
                            <div class="space-y-1.5 sm:col-span-2 pt-2">
                                <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Rol Asignado en Plataforma</label>
                                <div class="w-full px-4 py-3 rounded-xl bg-slate-100 border border-slate-200 text-xs font-black text-slate-600 flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-lock text-slate-400 text-xs"></i>
                                        <span><?php echo strtoupper($user_data['rol_nombre'] ?? 'Sin rol'); ?></span>
                                    </div>
                                    <span class="text-[10px] text-slate-400 font-bold uppercase italic">Modificable solo por Administración</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- BLOQUE 2: SEGURIDAD Y CONTRASEÑA -->
                    <div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-8 shadow-sm border border-slate-200/80 border-t-4 border-t-slate-700">
                        <div class="flex items-center justify-between mb-5 sm:mb-6">
                            <h2 class="text-base sm:text-lg font-black text-slate-800 flex items-center gap-2.5">
                                <i class="fas fa-shield-halved text-slate-700"></i> Credenciales de Seguridad
                            </h2>
                            <span class="text-[10px] font-bold text-slate-400 uppercase bg-slate-100 px-2.5 py-1 rounded-full">
                                Opcional
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Nueva Contraseña</label>
                                <div class="relative">
                                    <input type="password" name="new_password" id="new_password" placeholder="Mínimo 6 caracteres..." 
                                           class="w-full pl-4 pr-10 py-3 rounded-xl bg-slate-50 border border-slate-200 focus:border-blue-500 focus:bg-white transition-all text-xs sm:text-sm font-bold text-slate-800 outline-none">
                                    <button type="button" onclick="togglePasswordVisibility('new_password', 'toggleNewPassIcon')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-1">
                                        <i class="fas fa-eye text-xs" id="toggleNewPassIcon"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Confirmar Contraseña</label>
                                <div class="relative">
                                    <input type="password" name="r_new_password" id="r_new_password" placeholder="Repite la contraseña..." 
                                           class="w-full pl-4 pr-10 py-3 rounded-xl bg-slate-50 border border-slate-200 focus:border-blue-500 focus:bg-white transition-all text-xs sm:text-sm font-bold text-slate-800 outline-none">
                                    <button type="button" onclick="togglePasswordVisibility('r_new_password', 'toggleRNewPassIcon')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-1">
                                        <i class="fas fa-eye text-xs" id="toggleRNewPassIcon"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <p class="text-[10px] text-slate-400 italic mt-3 px-1">
                            Deja ambos campos de contraseña en blanco si deseas conservar tu clave actual sin modificaciones.
                        </p>
                    </div>

                    <!-- Botones de Acción (Margen inferior para Bottom Nav móvil) -->
                    <div class="pt-2 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 pb-24 sm:pb-8">
                        <button type="reset" class="px-6 py-3.5 text-center text-xs font-black uppercase tracking-wider text-slate-500 hover:text-slate-800 bg-white border border-slate-200 rounded-xl transition-all active:scale-[0.98]">
                            Descartar
                        </button>
                        <button type="submit" name="update_profile_btn" class="px-8 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-black uppercase text-xs tracking-wider rounded-xl shadow-lg shadow-blue-500/20 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>

                </form>
            </div>

        </div>

    </div>
</main>

<script>
function togglePasswordVisibility(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (!input || !icon) return;

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
