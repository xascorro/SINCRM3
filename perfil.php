<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');

$user_id = intval($_SESSION['id_usario'] ?? 0);
$query = "SELECT u.*, r.nombre as rol_nombre, c.nombre as club_nombre, c.nombre_corto as club_corto, c.logo as club_logo, j.nombre as juez_nombre, j.apellidos as juez_apellidos 
          FROM usuarios u 
          LEFT JOIN roles r ON u.id_rol = r.id 
          LEFT JOIN clubes c ON u.club = c.id 
          LEFT JOIN jueces j ON u.id_juez_v3 = j.id
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
                    <div class="flex items-center justify-center gap-1.5 mt-0.5 truncate">
                        <p class="text-xs text-slate-400 font-medium truncate"><?php echo htmlspecialchars($user_data['email'] ?? ''); ?></p>
                        <?php if (!empty($user_data['email_confirmado'])): ?>
                            <span class="inline-flex items-center text-emerald-500 text-xs shrink-0" title="Email verificado">
                                <i class="fas fa-circle-check"></i>
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center text-amber-500 text-xs shrink-0" title="Email pendiente de verificación">
                                <i class="fas fa-clock"></i>
                            </span>
                        <?php endif; ?>
                    </div>

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
                        <?php if (!empty($user_data['id_juez_v3'])): ?>
                            <a href="mi_auditoria.php" class="px-3 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 text-[10px] font-black uppercase tracking-wider rounded-full border border-amber-200 flex items-center gap-1.5 transition-colors no-underline" title="Ver Mi Auditoría BIAS">
                                <i class="fas fa-gavel text-[9px]"></i> Juez Vinculado
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($user_data['telegram_chat_id'])): ?>
                            <span class="px-3 py-1 bg-sky-50 text-sky-700 text-[10px] font-black uppercase tracking-wider rounded-full border border-sky-200 flex items-center gap-1.5" title="Telegram Vinculado">
                                <i class="fab fa-telegram-plane text-[9px]"></i> <?php echo htmlspecialchars(!empty($user_data['telegram_username']) ? '@' . $user_data['telegram_username'] : 'Telegram OK'); ?>
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

                    <?php if (!empty($user_data['id_juez_v3'])): 
                        $juez_full_name = trim(($user_data['juez_nombre'] ?? '') . ' ' . ($user_data['juez_apellidos'] ?? ''));
                    ?>
                    <div class="bg-gradient-to-br from-blue-50/80 to-indigo-50/50 rounded-2xl p-4 border border-blue-200/80 mt-4 text-left">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[9px] font-black uppercase tracking-widest text-blue-600 flex items-center gap-1.5">
                                <i class="fas fa-scale-balanced"></i> Perfil Oficial Juez
                            </span>
                            <span class="px-2 py-0.5 bg-blue-600 text-white rounded-md text-[9px] font-black uppercase tabular-nums">
                                Juez #<?php echo $user_data['id_juez_v3']; ?>
                            </span>
                        </div>
                        <p class="text-xs font-black text-slate-800 truncate mb-3">
                            <?php echo htmlspecialchars($juez_full_name ?: 'Juez Asignado'); ?>
                        </p>
                        <a href="mi_auditoria.php" class="w-full py-2 px-3 bg-white hover:bg-blue-600 hover:text-white text-blue-600 border border-blue-200 text-xs font-black rounded-xl transition-all flex items-center justify-center gap-2 shadow-2xs no-underline">
                            <i class="fas fa-magnifying-glass-chart"></i> Ver Mi Auditoría BIAS
                        </a>
                    </div>
                    <?php endif; ?>
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
                                <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest flex items-center justify-between">
                                    <span>Email de Acceso</span>
                                    <?php if (!empty($user_data['email_confirmado'])): ?>
                                        <span class="inline-flex items-center gap-1 text-[10px] font-black uppercase text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100">
                                            <i class="fas fa-check-circle text-emerald-500 text-[10px]"></i> Verificado
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 text-[10px] font-black uppercase text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200">
                                            <i class="fas fa-clock text-amber-500 text-[10px]"></i> No verificado
                                        </span>
                                    <?php endif; ?>
                                </label>
                                <input type="email" name="email" id="inputUserEmail" value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" required 
                                       class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 focus:border-blue-500 focus:bg-white transition-all text-xs sm:text-sm font-bold text-slate-800 outline-none">
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Teléfono de Contacto</label>
                                <input type="text" name="telefono" value="<?php echo htmlspecialchars($user_data['telefono'] ?? ''); ?>" placeholder="ej: 600123456" 
                                       class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 focus:border-blue-500 focus:bg-white transition-all text-xs sm:text-sm font-bold text-slate-800 outline-none tabular-nums">
                            </div>

                            <!-- Banner de Verificación de Email si no está confirmado -->
                            <?php if (empty($user_data['email_confirmado'])): ?>
                                <div id="bannerVerificarEmail" class="sm:col-span-2 p-4 rounded-2xl bg-amber-50/70 border border-amber-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
                                    <div class="flex items-start gap-3 min-w-0">
                                        <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                                            <i class="fas fa-envelope-circle-check text-sm"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <h4 class="text-xs font-black text-amber-900">Tu dirección de email aún no está verificada</h4>
                                            <p class="text-[11px] text-amber-700 font-medium mt-0.5">
                                                Valida tu correo para recibir avisos oficiales de competiciones y poder restablecer tu contraseña si la olvidas.
                                            </p>
                                        </div>
                                    </div>
                                    <button type="button" id="btnEnviarVerificacion" onclick="iniciarProcesoVerificacionGuiado()" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-gradient-to-r from-amber-600 to-amber-700 hover:from-amber-700 hover:to-amber-800 active:scale-95 text-white font-black text-[11px] uppercase tracking-wider rounded-xl shadow-xs transition-all shrink-0">
                                        <i class="fas fa-paper-plane text-xs"></i>
                                        <span>Verificar con Código</span>
                                    </button>
                                </div>
                            <?php endif; ?>

                            <!-- Preferencia de Notificaciones por Email -->
                            <div class="space-y-1.5 sm:col-span-2 pt-2">
                                <div class="p-3.5 sm:p-4 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between gap-3">
                                    <div class="space-y-0.5">
                                        <label class="text-xs font-black text-slate-800 flex items-center gap-2 cursor-pointer" for="recibir_emails_toggle">
                                            <i class="fas fa-envelope text-blue-600 text-xs"></i> Recibir Notificaciones por Correo Electrónico
                                        </label>
                                        <p class="text-[11px] text-slate-500 font-medium">
                                            Recibir avisos oficiales, circulares, cierres de inscripciones y actas en tu email.
                                            <?php if (empty($user_data['email_confirmado'])): ?>
                                                <span class="text-amber-600 font-bold block mt-0.5"><i class="fas fa-triangle-exclamation text-[10px]"></i> Requiere verificar tu email previamente con un código de 6 dígitos.</span>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                        <input type="checkbox" name="recibir_emails" id="recibir_emails_toggle" value="1" 
                                               <?php echo (!empty($user_data['recibir_emails']) && $user_data['recibir_emails'] == 1) ? 'checked' : ''; ?> 
                                               onchange="manejarClickRecibirEmails(this)"
                                               class="sr-only peer">
                                        <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                                    </label>
                                </div>
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

                    <?php
                    $is_tg_linked = !empty($user_data['telegram_chat_id']);
                    $tg_username_display = !empty($user_data['telegram_username']) ? '@' . $user_data['telegram_username'] : 'Chat ID ' . ($user_data['telegram_chat_id'] ?? '');
                    $tg_prefs = json_decode($user_data['telegram_prefs'] ?? '{}', true) ?: [];
                    ?>
                    <!-- BLOQUE 3: NOTIFICACIONES OFICIALES POR TELEGRAM -->
                    <div id="telegramCard" class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-8 shadow-sm border border-slate-200/80 border-t-4 border-t-sky-500 relative overflow-hidden">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 sm:mb-6">
                            <div>
                                <h2 class="text-base sm:text-lg font-black text-slate-800 flex items-center gap-2.5">
                                    <span class="w-8 h-8 rounded-xl bg-sky-50 text-sky-500 flex items-center justify-center text-sm shadow-2xs border border-sky-100">
                                        <i class="fab fa-telegram-plane"></i>
                                    </span>
                                    Notificaciones Instantáneas vía Telegram
                                </h2>
                                <p class="text-xs text-slate-400 font-medium mt-1">Avisos oficiales de cierres de plazo, música pendiente, orden de salida y resultados en tu móvil.</p>
                            </div>
                            
                            <div id="tgStatusBadge">
                                <?php if ($is_tg_linked): ?>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-2xs">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Vinculado (<?php echo htmlspecialchars($tg_username_display); ?>)
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black bg-slate-100 text-slate-500 border border-slate-200 shadow-2xs">
                                        <i class="fas fa-circle-minus text-[9px]"></i> No Vinculado
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Contenido cuando NO está vinculado -->
                        <div id="tgUnlinkedView" class="<?php echo $is_tg_linked ? 'hidden' : ''; ?> space-y-4">
                            <div class="p-4 sm:p-5 rounded-2xl bg-gradient-to-r from-sky-50/70 to-blue-50/50 border border-sky-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                                <div class="space-y-1">
                                    <span class="text-xs sm:text-sm font-black text-slate-800 flex items-center gap-2">
                                        <i class="fas fa-bolt text-sky-500"></i> Vinculación Rápida en 1 Clic
                                    </span>
                                    <p class="text-[11px] text-slate-500 max-w-xl leading-relaxed">
                                        Al pulsar en conectar, se abrirá la aplicación de Telegram en tu dispositivo. Solo tendrás que pulsar el botón <b>Iniciar</b> (Start) para quedar vinculado inmediatamente.
                                    </p>
                                </div>
                                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 shrink-0">
                                    <button type="button" onclick="iniciarVinculacionTelegram()" class="px-5 py-3 bg-sky-500 hover:bg-sky-600 text-white font-black text-xs uppercase tracking-wider rounded-xl shadow-lg shadow-sky-500/20 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                                        <i class="fab fa-telegram-plane text-sm"></i> Conectar con Telegram
                                    </button>
                                    <button type="button" onclick="mostrarModalQRTelegram()" class="px-3.5 py-3 bg-white text-slate-600 hover:text-sky-600 border border-slate-200 hover:border-sky-200 text-xs font-bold rounded-xl active:scale-[0.98] transition-all flex items-center justify-center gap-1.5" title="Escanear con la cámara del móvil">
                                        <i class="fas fa-qrcode text-sm"></i> Ver QR
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Contenido cuando SÍ está vinculado -->
                        <div id="tgLinkedView" class="<?php echo $is_tg_linked ? '' : 'hidden'; ?> space-y-5">
                            <div class="p-4 sm:p-5 rounded-2xl bg-slate-50 border border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-sky-500 text-white flex items-center justify-center text-lg shadow-md shadow-sky-500/20 shrink-0">
                                        <i class="fab fa-telegram-plane"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs font-black text-slate-800" id="tgAccountInfo">
                                            Conectado a Telegram como <span class="text-sky-600 font-bold"><?php echo htmlspecialchars($tg_username_display); ?></span>
                                        </p>
                                        <span class="text-[10px] text-slate-400">Canal verificado para notificaciones de SINCRM</span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <button type="button" onclick="probarMensajeTelegram()" class="px-4 py-2 bg-white hover:bg-sky-50 text-sky-700 border border-sky-200 text-xs font-bold rounded-xl active:scale-[0.98] transition-all flex items-center gap-1.5 shadow-2xs">
                                        <i class="fas fa-paper-plane text-[11px]"></i> Enviar Prueba
                                    </button>
                                    <button type="button" onclick="desvincularTelegram()" class="px-3 py-2 bg-white hover:bg-rose-50 text-rose-600 border border-slate-200 hover:border-rose-200 text-xs font-bold rounded-xl active:scale-[0.98] transition-all flex items-center gap-1.5 shadow-2xs" title="Desconectar este chat">
                                        <i class="fas fa-unlink text-[11px]"></i> Desvincular
                                    </button>
                                </div>
                            </div>

                            <!-- Opciones de Notificaciones -->
                            <div class="pt-2 border-t border-slate-100">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-[10px] font-black uppercase text-slate-400 tracking-wider">Avisos Activos para tu Cuenta</span>
                                    <span class="text-[10px] text-slate-400 font-medium italic">Se actualizan automáticamente</span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                    <label class="p-3 rounded-xl bg-slate-50/70 border border-slate-200/60 flex items-center justify-between cursor-pointer hover:bg-slate-50 transition-colors">
                                        <div class="space-y-0.5">
                                            <span class="text-xs font-bold text-slate-700 block">Competiciones y Sorteos</span>
                                            <span class="text-[10px] text-slate-400 block">Publicación de orden de salida y horarios.</span>
                                        </div>
                                        <input type="checkbox" onchange="guardarPrefsTelegram()" id="pref_competiciones" <?php echo (!isset($tg_prefs['competiciones']) || $tg_prefs['competiciones']) ? 'checked' : ''; ?> class="w-4 h-4 rounded text-sky-500 border-slate-300">
                                    </label>

                                    <label class="p-3 rounded-xl bg-slate-50/70 border border-slate-200/60 flex items-center justify-between cursor-pointer hover:bg-slate-50 transition-colors">
                                        <div class="space-y-0.5">
                                            <span class="text-xs font-bold text-slate-700 block">Cierres y Avisos de Música</span>
                                            <span class="text-[10px] text-slate-400 block">Confirmaciones de cierre y música pendiente.</span>
                                        </div>
                                        <input type="checkbox" onchange="guardarPrefsTelegram()" id="pref_cierres" <?php echo (!isset($tg_prefs['cierres']) || $tg_prefs['cierres']) ? 'checked' : ''; ?> class="w-4 h-4 rounded text-sky-500 border-slate-300">
                                    </label>

                                    <label class="p-3 rounded-xl bg-slate-50/70 border border-slate-200/60 flex items-center justify-between cursor-pointer hover:bg-slate-50 transition-colors">
                                        <div class="space-y-0.5">
                                            <span class="text-xs font-bold text-slate-700 block">Resultados Oficiales</span>
                                            <span class="text-[10px] text-slate-400 block">Actas finales y clasificaciones publicadas.</span>
                                        </div>
                                        <input type="checkbox" onchange="guardarPrefsTelegram()" id="pref_resultados" <?php echo (!isset($tg_prefs['resultados']) || $tg_prefs['resultados']) ? 'checked' : ''; ?> class="w-4 h-4 rounded text-sky-500 border-slate-300">
                                    </label>

                                    <?php if (!empty($user_data['id_juez_v3'])): ?>
                                    <label class="p-3 rounded-xl bg-slate-50/70 border border-slate-200/60 flex items-center justify-between cursor-pointer hover:bg-slate-50 transition-colors">
                                        <div class="space-y-0.5">
                                            <span class="text-xs font-bold text-slate-700 block">Paneles de Jueces</span>
                                            <span class="text-[10px] text-slate-400 block">Asignación técnica a mesas y paneles.</span>
                                        </div>
                                        <input type="checkbox" onchange="guardarPrefsTelegram()" id="pref_paneles" <?php echo (!isset($tg_prefs['paneles']) || $tg_prefs['paneles']) ? 'checked' : ''; ?> class="w-4 h-4 rounded text-sky-500 border-slate-300">
                                    </label>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
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

// GESTIÓN DE TELEGRAM 1-CLIC
let tgPollTimer = null;

function iniciarVinculacionTelegram() {
    Swal.fire({
        title: 'Generando enlace...',
        text: 'Conectando con el bot de Telegram',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    $.post('telegram_link.php', { action: 'generar_token' }, function(res) {
        if (res.status === 'success') {
            // Abrir Telegram en nueva pestaña / app móvil
            window.open(res.link, '_blank');

            // Modal interactivo mientras el usuario pulsa Iniciar
            Swal.fire({
                title: 'Pulsa "Iniciar" en Telegram',
                html: `
                    <div class="space-y-4 my-2 text-slate-600">
                        <div class="w-16 h-16 rounded-2xl bg-sky-50 text-sky-500 mx-auto flex items-center justify-center text-3xl shadow-sm border border-sky-100">
                            <i class="fab fa-telegram-plane animate-bounce"></i>
                        </div>
                        <p class="text-xs">Se ha abierto Telegram en tu dispositivo. Pulsa en <b>Iniciar</b> o envía el mensaje inicial para completar la vinculación.</p>
                        <p class="text-[11px] text-slate-400 italic">Esperando confirmación del bot...</p>
                        <div class="pt-2">
                            <a href="${res.link}" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2 bg-sky-500 hover:bg-sky-600 text-white text-xs font-bold rounded-xl transition-all">
                                <i class="fas fa-external-link-alt text-[10px]"></i> Volver a abrir Telegram
                            </a>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                cancelButtonText: 'Cerrar',
                showConfirmButton: false,
                didOpen: () => {
                    iniciarSondeoVinculacion();
                },
                willClose: () => {
                    detenerSondeoVinculacion();
                }
            });
        } else {
            Swal.fire('Error', res.message || 'No se pudo generar el enlace', 'error');
        }
    }, 'json').fail(function() {
        Swal.fire('Error', 'Fallo de comunicación con el servidor', 'error');
    });
}

function mostrarModalQRTelegram() {
    Swal.fire({
        title: 'Generando código QR...',
        didOpen: () => { Swal.showLoading(); }
    });

    $.post('telegram_link.php', { action: 'generar_token' }, function(res) {
        if (res.status === 'success') {
            const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' + encodeURIComponent(res.link);

            Swal.fire({
                title: 'Escanea con la cámara de tu móvil',
                html: `
                    <div class="flex flex-col items-center justify-center space-y-3 my-2">
                        <div class="p-3 bg-white border border-slate-200 rounded-2xl shadow-sm">
                            <img src="${qrUrl}" alt="QR Telegram" class="w-48 h-48 rounded-lg object-contain">
                        </div>
                        <p class="text-xs text-slate-500 max-w-xs">Apunta con la cámara de tu móvil para abrir Telegram y pulsa <b>Iniciar</b>.</p>
                        <span class="text-[10px] text-slate-400 italic">Detección automática en tiempo real</span>
                    </div>
                `,
                showCancelButton: true,
                cancelButtonText: 'Cerrar',
                showConfirmButton: false,
                didOpen: () => {
                    iniciarSondeoVinculacion();
                },
                willClose: () => {
                    detenerSondeoVinculacion();
                }
            });
        } else {
            Swal.fire('Error', res.message || 'Error al generar el QR', 'error');
        }
    }, 'json');
}

function iniciarSondeoVinculacion() {
    detenerSondeoVinculacion();
    tgPollTimer = setInterval(function() {
        $.getJSON('telegram_link.php?action=comprobar_estado', function(data) {
            if (data.status === 'success' && data.vinculado) {
                detenerSondeoVinculacion();
                Swal.close();

                // Actualizar interfaz a estado conectado
                document.getElementById('tgUnlinkedView').classList.add('hidden');
                document.getElementById('tgLinkedView').classList.remove('hidden');
                
                const usernameTxt = data.telegram_username ? '@' + data.telegram_username : 'Chat ID ' + data.chat_id;
                document.getElementById('tgStatusBadge').innerHTML = `
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Vinculado (${usernameTxt})
                    </span>
                `;
                document.getElementById('tgAccountInfo').innerHTML = `
                    Conectado a Telegram como <span class="text-sky-600 font-bold">${usernameTxt}</span>
                `;

                Swal.fire({
                    icon: 'success',
                    title: '¡Vinculación Exitosa!',
                    text: 'Tu cuenta de SINCRM ha sido conectada correctamente a Telegram.',
                    timer: 3000,
                    showConfirmButton: false
                });
            }
        });
    }, 2500);
}

function detenerSondeoVinculacion() {
    if (tgPollTimer) {
        clearInterval(tgPollTimer);
        tgPollTimer = null;
    }
}

function probarMensajeTelegram() {
    Swal.fire({
        title: 'Enviando mensaje...',
        didOpen: () => { Swal.showLoading(); }
    });

    $.post('telegram_link.php', { action: 'probar_mensaje' }, function(res) {
        if (res.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: '¡Mensaje Enviado!',
                text: res.message,
                timer: 2500,
                showConfirmButton: false
            });
        } else {
            Swal.fire('Atención', res.message, 'warning');
        }
    }, 'json').fail(function() {
        Swal.fire('Error', 'Fallo de conexión al enviar prueba', 'error');
    });
}

function guardarPrefsTelegram() {
    const payload = {
        action: 'guardar_prefs',
        notif_activas: '1',
        pref_competiciones: document.getElementById('pref_competiciones')?.checked ? '1' : '0',
        pref_cierres: document.getElementById('pref_cierres')?.checked ? '1' : '0',
        pref_musica: document.getElementById('pref_musica')?.checked ? '1' : '0',
        pref_resultados: document.getElementById('pref_resultados')?.checked ? '1' : '0',
        pref_paneles: document.getElementById('pref_paneles')?.checked ? '1' : '0'
    };

    $.post('telegram_link.php', payload, function(res) {
        if (res.status === 'success') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Preferencias guardadas',
                showConfirmButton: false,
                timer: 1500
            });
        }
    }, 'json');
}

function desvincularTelegram() {
    Swal.fire({
        title: '¿Desvincular Telegram?',
        text: 'Dejarás de recibir notificaciones oficiales en tiempo real en tu aplicación.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Sí, desvincular',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('telegram_link.php', { action: 'desvincular' }, function(res) {
                if (res.status === 'success') {
                    document.getElementById('tgLinkedView').classList.add('hidden');
                    document.getElementById('tgUnlinkedView').classList.remove('hidden');
                    document.getElementById('tgStatusBadge').innerHTML = `
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black bg-slate-100 text-slate-500 border border-slate-200 shadow-2xs">
                            <i class="fas fa-circle-minus text-[9px]"></i> No Vinculado
                        </span>
                    `;
                    Swal.fire('Desvinculado', res.message, 'success');
                } else {
                    Swal.fire('Error', res.message || 'No se pudo desvincular', 'error');
                }
            }, 'json');
        }
    });
}

const userEmailAddress = <?php echo json_encode($user_data['email'] ?? ''); ?>;
let isUserEmailVerified = <?php echo !empty($user_data['email_confirmado']) ? 'true' : 'false'; ?>;

function manejarClickRecibirEmails(input) {
    if (input.checked && !isUserEmailVerified) {
        // Desmarcar visualmente hasta que complete el proceso guiado
        input.checked = false;
        iniciarProcesoVerificacionGuiado();
    } else {
        // Usuario ya verificado: actualizar directamente por AJAX
        actualizarPreferenciaEmails(input.checked ? 1 : 0);
    }
}

function actualizarPreferenciaEmails(valor) {
    $.post('perfil_code.php', {
        action: 'toggle_recibir_emails',
        recibir: valor
    }, function(res) {
        if (res.status === 'success') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: res.message,
                showConfirmButton: false,
                timer: 2000
            });
        } else if (res.status === 'requiere_verificacion') {
            document.getElementById('recibir_emails_toggle').checked = false;
            iniciarProcesoVerificacionGuiado();
        } else {
            Swal.fire('Error', res.message || 'No se pudo actualizar la preferencia', 'error');
        }
    }, 'json');
}

function iniciarProcesoVerificacionGuiado() {
    Swal.fire({
        title: 'Verificación de Correo Requerida',
        html: `
            <div class="text-left space-y-3 font-lexend p-1">
                <div class="p-3 bg-blue-50 border border-blue-100 rounded-xl text-xs text-blue-800">
                    <i class="fas fa-shield-halved text-blue-600 mr-1.5"></i>
                    Para activar la recepción de emails oficiales, primero debemos verificar que este correo te pertenece:
                    <div class="font-bold font-mono text-slate-800 mt-1 break-all">${userEmailAddress}</div>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Te enviaremos un <strong>código de 6 dígitos</strong>. Al confirmarlo, tu email quedará verificado y las notificaciones oficiales se activarán automáticamente.
                </p>
            </div>
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-paper-plane mr-1.5"></i> Enviar Código',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#94a3b8',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return fetch('perfil_code.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action: 'enviar_codigo_verificacion' })
            })
            .then(r => r.json())
            .then(data => {
                if (data.status !== 'success') {
                    throw new Error(data.message || 'Error al enviar el código de verificación.');
                }
                return data;
            })
            .catch(err => {
                Swal.showValidationMessage(err.message);
            });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            pedirCodigoVerificacion();
        }
    });
}

function pedirCodigoVerificacion() {
    Swal.fire({
        title: 'Introduce tu Código',
        html: `
            <div class="space-y-3 font-lexend text-left p-1">
                <p class="text-xs text-slate-600">
                    Introduce los 6 dígitos que acabamos de enviar a <strong>${userEmailAddress}</strong>:
                </p>
                <div class="text-center my-3">
                    <input type="text" id="inputCodigoOTP" maxlength="6" placeholder="000000" autofocus
                           class="w-52 text-center text-3xl font-mono font-black tracking-[8px] px-4 py-3 rounded-2xl border-2 border-indigo-400 focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100 focus:outline-none bg-slate-50 text-indigo-700">
                </div>
                <div class="flex items-center justify-between text-[11px] text-slate-400 pt-1">
                    <span><i class="fas fa-stopwatch mr-1"></i> Válido 15 minutos</span>
                    <button type="button" onclick="iniciarProcesoVerificacionGuiado()" class="text-indigo-600 font-bold hover:underline">Reenviar código</button>
                </div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '✓ Verificar y Activar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#16a34a',
        cancelButtonColor: '#94a3b8',
        showLoaderOnConfirm: true,
        didOpen: () => {
            const input = document.getElementById('inputCodigoOTP');
            if (input) {
                input.focus();
                input.addEventListener('keyup', (e) => {
                    if (e.key === 'Enter') Swal.clickConfirm();
                });
            }
        },
        preConfirm: () => {
            const codigo = document.getElementById('inputCodigoOTP').value.trim();
            if (!codigo || codigo.length !== 6) {
                Swal.showValidationMessage('Introduce el código completo de 6 dígitos.');
                return false;
            }
            return fetch('perfil_code.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ 
                    action: 'validar_codigo_y_activar',
                    codigo: codigo 
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.status !== 'success') {
                    throw new Error(data.message || 'Código incorrecto o caducado.');
                }
                return data;
            })
            .catch(err => {
                Swal.showValidationMessage(err.message);
            });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: '¡Todo Listo!',
                text: 'Tu correo ha sido verificado y las notificaciones oficiales han quedado activadas.',
                icon: 'success',
                confirmButtonColor: '#2563eb'
            }).then(() => {
                window.location.reload();
            });
        }
    });
}
</script>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
