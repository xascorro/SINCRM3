<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-surface">
    
    <?php include('includes/topbar.php'); ?>

    <!-- Contenido de la Página -->
    <div class="p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend text-primary">
        
        <!-- Header de Sección -->
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <h1 class="text-4xl font-black text-slate-800 tracking-tighter mb-2 flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-user-circle text-lg"></i></span>
                    Gestión de Perfil
                </h1>
                <p class="text-slate-500 font-medium">Controla tu identidad y seguridad en la plataforma.</p>
            </div>
        </div>

        <!-- Alertas -->
        <?php if(isset($_SESSION['correcto'])): ?>
            <div class="mb-8 p-4 bg-white border-l-4 border-green-500 text-slate-700 rounded-r-2xl shadow-sm flex items-center gap-4 animate-fade-in">
                <div class="w-8 h-8 rounded-full bg-green-50 flex items-center justify-center text-green-500"><i class="fas fa-check"></i></div>
                <span class="text-sm font-bold"><?php echo $_SESSION['correcto']; unset($_SESSION['correcto']); ?></span>
            </div>
        <?php endif; ?>
        <?php if(isset($_SESSION['estado'])): ?>
            <div class="mb-8 p-4 bg-white border-l-4 border-red-500 text-slate-700 rounded-r-2xl shadow-sm flex items-center gap-4 animate-fade-in">
                <div class="w-8 h-8 rounded-full bg-red-50 flex items-center justify-center text-red-500"><i class="fas fa-exclamation-triangle"></i></div>
                <span class="text-sm font-bold"><?php echo $_SESSION['estado']; unset($_SESSION['estado']); ?></span>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
            
            <!-- Columna Izquierda: Edición -->
            <div class="lg:col-span-8 space-y-8">
                <?php
                $user_id = $_SESSION['id_usario'];
                $query = "SELECT u.*, r.nombre as rol_nombre FROM usuarios u LEFT JOIN roles r ON u.id_rol = r.id WHERE u.id = '$user_id'";
                $query_run = mysqli_query($connection, $query);
                $user_data = mysqli_fetch_assoc($query_run);
                ?>
                <form action="perfil_code.php" method="POST" class="animate-fade-in">
                    <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-sm border border-slate-200 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                        <h2 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3 italic"><i class="fas fa-id-card text-blue-600"></i> Información de Identidad</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Nombre Completo</label>
                                <input type="text" name="username" value="<?php echo $user_data['username']; ?>" required class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold text-slate-700 shadow-inner">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Email de Acceso</label>
                                <input type="email" name="email" value="<?php echo $user_data['email']; ?>" required class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold text-slate-700 shadow-inner">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Teléfono de Contacto</label>
                                <input type="text" name="telefono" value="<?php echo $user_data['telefono']; ?>" class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold text-slate-700 shadow-inner">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Rol asignado</label>
                                <div class="w-full px-5 py-4 rounded-2xl bg-slate-100 border border-slate-200 text-xs font-black text-slate-400 flex items-center gap-3">
                                    <i class="fas fa-shield-alt text-[10px]"></i>
                                    <?php echo strtoupper($user_data['rol_nombre']); ?>
                                </div>
                                <p class="text-[9px] text-slate-400 font-bold italic px-1">Tu rol solo puede ser modificado por un Administrador.</p>
                            </div>
                        </div>

                        <!-- Bloque Seguridad -->
                        <div class="mt-12 pt-8 border-t border-slate-100">
                            <h3 class="text-xs font-black text-slate-800 uppercase tracking-widest mb-6 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span> Credenciales y Seguridad
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Nueva Contraseña</label>
                                    <input type="password" name="new_password" placeholder="Mínimo 6 caracteres..." class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold text-slate-700 shadow-inner">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Confirmar Contraseña</label>
                                    <input type="password" name="r_new_password" placeholder="Repite la contraseña..." class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold text-slate-700 shadow-inner">
                                </div>
                            </div>
                            <p class="mt-4 text-[9px] text-slate-400 font-medium italic">Deja los campos de contraseña vacíos si no deseas realizar cambios en tu clave actual.</p>
                        </div>

                        <!-- Footer -->
                        <div class="mt-12 pt-8 border-t border-slate-100 flex justify-end">
                            <button type="submit" name="update_profile_btn" class="px-12 py-4 bg-blue-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg shadow-blue-500/20 hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
                                <i class="fas fa-save"></i> Guardar Cambios
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Columna Derecha: Resumen y Actividad -->
            <div class="lg:col-span-4 space-y-8">
                <!-- Tarjeta Resumen -->
                <div class="bg-white rounded-[2.5rem] p-10 shadow-sm border border-slate-200 text-center relative overflow-hidden group">
                    <div class="absolute -top-10 -left-10 w-32 h-32 bg-slate-50 rounded-full group-hover:scale-150 transition-transform duration-700"></div>
                    <div class="relative z-10">
                        <div class="w-24 h-24 rounded-[2rem] oceanic-gradient mx-auto mb-6 flex items-center justify-center text-white text-3xl shadow-xl shadow-blue-500/20 border-4 border-white">
                            <?php echo strtoupper(substr($user_data['username'], 0, 1)); ?>
                        </div>
                        <h3 class="text-xl font-black text-slate-800 leading-tight"><?php echo $user_data['username']; ?></h3>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1"><?php echo $user_data['rol_nombre']; ?></p>
                        <div class="mt-6 inline-flex px-4 py-1.5 bg-slate-50 text-slate-500 text-[10px] font-black rounded-full border border-slate-100 uppercase italic">
                            Cuenta Verificada
                        </div>
                    </div>
                </div>

                <!-- Actividad Reciente -->
                <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-200">
                    <a href="log_usuario.php" class="group/head no-underline flex items-center justify-between mb-8">
                        <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-3 italic group-hover/head:text-blue-600 transition-colors">
                            <i class="fas fa-history text-slate-300 group-hover/head:text-blue-500"></i> Mi Actividad
                        </h3>
                        <i class="fas fa-chevron-right text-[10px] text-slate-200 group-hover/head:text-blue-400 transition-all"></i>
                    </a>
                    <div class="space-y-6 max-h-[500px] overflow-y-auto no-scrollbar pr-2">
                        <?php
                        $log_file = './log/log.txt';
                        $user_email = $user_data['email'];
                        $found_logs = 0;
                        
                        if (file_exists($log_file)) {
                            $lines = @file($log_file);
                            if ($lines) {
                                $lines = array_reverse($lines);
                                foreach ($lines as $line) {
                                    if (strpos($line, $user_email) !== false) {
                                        $found_logs++;
                                        preg_match('/^\[(.*?)\] \[(.*?)\] \[(.*?)\] \[(.*?)\] (.*)$/', $line, $matches);
                                        
                                        if ($matches) {
                                            $date = date("d M, H:i", strtotime($matches[1]));
                                            $level = $matches[2];
                                            $msg = $matches[5];
                                            
                                            $icon = 'fa-info-circle text-blue-500';
                                            if($level == 'SUCCESS') $icon = 'fa-check-circle text-emerald-500';
                                            if($level == 'ERROR') $icon = 'fa-exclamation-circle text-red-500';
                                            if($level == 'WARNING' || $level == 'SECURITY') $icon = 'fa-shield-alt text-amber-500';
                        ?>
                                            <div class="flex gap-4 group/item">
                                                <div class="shrink-0 w-8 h-8 rounded-xl bg-slate-50 flex items-center justify-center text-[10px] border border-slate-100 group-hover/item:bg-white transition-all">
                                                    <i class="fas <?php echo $icon; ?>"></i>
                                                </div>
                                                <div class="space-y-1">
                                                    <p class="text-[11px] font-black text-slate-700 leading-tight"><?php echo htmlspecialchars($msg); ?></p>
                                                    <p class="text-[9px] font-bold text-slate-400 uppercase italic"><?php echo $date; ?></p>
                                                </div>
                                            </div>
                        <?php
                                        }
                                        if ($found_logs >= 10) break;
                                    }
                                }
                            }
                        }
                        
                        if ($found_logs == 0) {
                            echo '<div class="text-[10px] text-slate-400 italic p-6 bg-slate-50 rounded-2xl text-center font-bold">No hay registros de actividad para tu cuenta.</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>

        </div>

    </div>
</main>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
