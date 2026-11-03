<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-surface">
    
    <?php include('includes/topbar.php'); ?>

    <!-- Contenido de la Página -->
    <div class="px-3 py-4 sm:p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend text-primary">
        
        <!-- Header de Sección -->
        <div class="mb-6 sm:mb-10 flex flex-col sm:flex-row sm:items-end justify-between gap-4 sm:gap-6">
            <div>
                <h1 class="text-2xl sm:text-4xl font-black text-slate-800 tracking-tighter mb-1 sm:mb-2 flex items-center gap-2.5 sm:gap-3">
                    <span class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-sliders text-base sm:text-lg"></i></span>
                    Configuración Técnica
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 font-medium">Gestión de credenciales, límites del servidor y herramientas de base de datos.</p>
            </div>
            <div class="flex gap-2 sm:gap-3">
                <a href="mantenimiento.php" class="w-full sm:w-auto px-5 sm:px-6 py-2.5 sm:py-3 bg-white text-slate-600 font-black uppercase text-xs tracking-wider rounded-xl sm:rounded-2xl border border-slate-200 hover:bg-slate-50 active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-sm">
                    <i class="fas fa-chart-line text-xs"></i> Ver Estado
                </a>
            </div>
        </div>

        <!-- Alertas -->
        <?php include('includes/alertas_v4.php'); ?>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-8 mb-6 sm:mb-10">
            <!-- COLUMNA IZQUIERDA: CONECTIVIDAD Y OPTIMIZACIÓN -->
            <div class="lg:col-span-7 space-y-4 sm:space-y-8">
                
                <!-- Panel: Conexión BD -->
                <div class="bg-white rounded-2xl sm:rounded-[2.5rem] p-4 sm:p-8 shadow-sm border border-slate-200 border-t-4 sm:border-t-[6px] border-t-blue-600 relative overflow-hidden">
                    <h2 class="text-base sm:text-xl font-black text-slate-800 mb-4 sm:mb-8 flex items-center gap-2.5 sm:gap-3 italic"><i class="fas fa-plug text-blue-600"></i> Configuración de Acceso</h2>
                    <form action="db_code.php" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-6">
                        <div class="space-y-1 sm:space-y-2">
                            <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Host / Servidor</label>
                            <input type="text" name="servername" value="<?php echo $servername ?>" class="w-full px-4 sm:px-5 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-100 text-xs sm:text-sm font-bold text-slate-700 shadow-inner">
                        </div>
                        <div class="space-y-1 sm:space-y-2">
                            <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Nombre Base de Datos</label>
                            <input type="text" name="db_name" value="<?php echo $db_name ?>" class="w-full px-4 sm:px-5 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-100 text-xs sm:text-sm font-bold text-slate-700 shadow-inner">
                        </div>
                        <div class="space-y-1 sm:space-y-2">
                            <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Usuario MariaDB</label>
                            <input type="text" name="db_username" value="<?php echo $db_username ?>" class="w-full px-4 sm:px-5 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-100 text-xs sm:text-sm font-bold text-slate-700 shadow-inner">
                        </div>
                        <div class="space-y-1 sm:space-y-2">
                            <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Contraseña</label>
                            <input type="password" name="db_password" value="<?php echo $db_password ?>" class="w-full px-4 sm:px-5 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-100 text-xs sm:text-sm font-bold text-slate-700 shadow-inner">
                        </div>
                        <div class="md:col-span-2 pt-4 sm:pt-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-6 border-t border-slate-100">
                            <label class="flex items-center gap-2.5 sm:gap-3 cursor-pointer group py-1">
                                <input type="checkbox" name="accept" value="1" class="w-4 h-4 sm:w-5 sm:h-5 rounded-lg border-slate-300 text-red-500">
                                <span class="text-[11px] sm:text-xs font-black uppercase text-red-500 tracking-wider">Confirmar Cambios</span>
                            </label>
                            <button type="submit" name="update_btn" class="w-full sm:w-auto px-6 sm:px-8 py-2.5 sm:py-3 bg-blue-600 text-white font-black uppercase text-xs tracking-wider rounded-xl sm:rounded-2xl shadow-lg shadow-blue-500/20 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                                <i class="fas fa-save text-xs"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Herramientas de Base de Datos -->
                <div class="bg-white rounded-2xl sm:rounded-[2.5rem] p-4 sm:p-8 shadow-sm border border-slate-200 border-l-4 sm:border-l-[6px] border-l-emerald-500">
                    <h2 class="text-sm sm:text-lg font-black text-slate-800 mb-3 sm:mb-6 flex items-center gap-2 sm:gap-3 italic">
                        <i class="fas fa-microchip text-emerald-500"></i> Optimización de Datos
                    </h2>
                    <div class="p-3.5 sm:p-6 bg-slate-50 rounded-xl sm:rounded-3xl border border-slate-100 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 sm:gap-6">
                        <div class="space-y-1">
                            <p class="text-xs sm:text-sm font-black text-slate-700 leading-tight">Reparar y Optimizar Tablas</p>
                            <p class="text-[9px] sm:text-[10px] font-medium text-slate-400 uppercase tracking-tighter italic">Ejecuta el comando OPTIMIZE para recuperar espacio y reconstruir índices.</p>
                        </div>
                        <form action="db_code.php" method="POST" class="shrink-0">
                            <button type="submit" name="optimize_db" class="w-full sm:w-auto px-6 sm:px-8 py-2.5 sm:py-3 bg-emerald-600 text-white font-black text-[9px] sm:text-[10px] uppercase tracking-wider rounded-xl sm:rounded-2xl shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                                <i class="fas fa-wrench text-xs"></i> Ejecutar Ahora
                            </button>
                        </form>
                    </div>
                </div>

            </div>

            <!-- COLUMNA DERECHA: BACKUPS Y RUNTIME -->
            <div class="lg:col-span-5 space-y-4 sm:space-y-8">
                <!-- Gestión de Backups -->
                <div class="bg-white rounded-2xl sm:rounded-[2.5rem] p-4 sm:p-8 shadow-sm border border-slate-200 border-t-4 sm:border-t-[6px] border-t-emerald-500">
                    <h2 class="text-base sm:text-xl font-black text-slate-800 mb-4 sm:mb-8 flex items-center gap-2.5 sm:gap-3 italic"><i class="fas fa-history text-emerald-500"></i> Backups</h2>
                    <form action="db_code.php" method="POST" class="mb-4 sm:mb-8">
                        <div class="relative">
                            <input type="text" name="descripcion" placeholder="Comentario descriptivo..." class="w-full px-4 sm:px-5 py-2.5 sm:py-3 pr-24 sm:pr-28 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-100 text-xs sm:text-sm font-bold shadow-inner">
                            <button type="submit" name="backup_btn" class="absolute right-1.5 top-1.5 sm:right-2 sm:top-2 px-3 sm:px-4 py-1.5 bg-emerald-600 text-white font-black text-[8px] sm:text-[9px] uppercase tracking-wider rounded-lg active:scale-[0.98] transition-transform">Crear SQL</button>
                        </div>
                    </form>
                    <div class="space-y-2.5 sm:space-y-3 max-h-[350px] sm:max-h-[450px] overflow-y-auto no-scrollbar pr-1">
                        <?php
                        $dir = './database/backup/';
                        if (is_dir($dir)) {
                            $files = [];
                            foreach (scandir($dir) as $file) {
                                if ($file != "." && $file != ".." && (substr($file, -7) == '.sql.gz' || substr($file, -4) == '.sql')) {
                                    $files[] = ['name' => $file, 'path' => $dir . $file, 'time' => @filemtime($dir . $file)];
                                }
                            }
                            usort($files, function ($a, $b) { return ($b['time'] ?? 0) - ($a['time'] ?? 0); });
                            foreach ($files as $f): 
                                $description = "";
                                if (substr($f['name'], -3) == '.gz') {
                                    $gz = @gzopen($f['path'], 'r');
                                    if ($gz) { $firstLine = gzgets($gz, 4096); gzclose($gz); if (preg_match('/\/\*(.*)\*\//', $firstLine, $m)) $description = htmlspecialchars($m[1]); }
                                } else {
                                    $fp = @fopen($f['path'], 'r');
                                    if ($fp) { $firstLine = fgets($fp, 4096); fclose($fp); if (preg_match('/\/\*(.*)\*\//', $firstLine, $m)) $description = htmlspecialchars($m[1]); }
                                }
                        ?>
                            <div class="p-3 sm:p-4 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-100 group hover:border-emerald-200 transition-all">
                                <div class="flex items-center justify-between gap-2 mb-1.5">
                                    <div class="flex items-center gap-2.5 sm:gap-3 overflow-hidden min-w-0">
                                        <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-slate-400 group-hover:text-emerald-500 shadow-sm transition-colors shrink-0"><i class="fas fa-database text-xs"></i></div>
                                        <div class="overflow-hidden min-w-0">
                                            <p class="text-[9px] font-black text-slate-400 uppercase tabular-nums"><?php echo date("d M, H:i", $f['time']); ?></p>
                                            <p class="text-xs font-bold text-slate-700 truncate"><?php echo $f['name']; ?></p>
                                        </div>
                                    </div>
                                    <div class="flex gap-1 shrink-0">
                                        <a href="<?php echo $f['path']; ?>" download class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-white hover:text-emerald-500 active:scale-[0.98] transition-all"><i class="fas fa-download text-xs"></i></a>
                                        <form action="db_code.php" method="POST" class="flex gap-1">
                                            <input type="hidden" name="backup_file" value="<?php echo $f['name']; ?>">
                                            <button type="submit" name="restore_backup" onclick="return confirm('¿Restaurar copia de seguridad? Esto sobrescribirá todos los datos actuales.')" class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-white hover:text-amber-500 active:scale-[0.98] transition-all"><i class="fas fa-undo text-xs"></i></button>
                                            <button type="submit" name="delete_backup" onclick="return confirm('¿Borrar este archivo?')" class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-white hover:text-red-500 active:scale-[0.98] transition-all"><i class="fas fa-trash text-xs"></i></button>
                                        </form>
                                    </div>
                                </div>
                                <?php if ($description): ?>
                                    <div class="mt-1.5 pl-10 text-[9px] sm:text-[10px] font-medium text-slate-500 italic flex items-center gap-1.5 truncate"><i class="fas fa-comment-dots opacity-40 shrink-0"></i> <?php echo $description; ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; } ?>
                    </div>
                </div>

                <!-- PHP y Debug -->
                <div class="space-y-4 sm:space-y-6">
                    <div class="bg-white rounded-2xl sm:rounded-[2.5rem] p-4 sm:p-8 shadow-sm border border-slate-200 border-l-4 sm:border-l-[6px] border-l-slate-400">
                        <h4 class="text-xs sm:text-sm font-black text-slate-400 uppercase mb-4 sm:mb-6 flex items-center gap-2 italic"><i class="fab fa-php text-slate-400 text-sm sm:text-base"></i> Runtime Config</h4>
                        <form action="db_code.php" method="POST" class="space-y-3 sm:space-y-4">
                            <div class="grid grid-cols-2 gap-2.5 sm:gap-4">
                                <div class="space-y-1">
                                    <label class="text-[9px] font-black uppercase text-slate-400 px-1 italic">Upload Max</label>
                                    <input type="text" name="upload_max_filesize" value="<?php echo ini_get('upload_max_filesize'); ?>" class="w-full px-3 sm:px-4 py-2 sm:py-3 rounded-xl bg-slate-50 border border-slate-100 text-xs font-bold text-slate-700 focus:border-blue-500 transition-all shadow-inner tabular-nums">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[9px] font-black uppercase text-slate-400 px-1 italic">Post Max</label>
                                    <input type="text" name="post_max_size" value="<?php echo ini_get('post_max_size'); ?>" class="w-full px-3 sm:px-4 py-2 sm:py-3 rounded-xl bg-slate-50 border border-slate-100 text-xs font-bold text-slate-700 focus:border-blue-500 transition-all shadow-inner tabular-nums">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[9px] font-black uppercase text-slate-400 px-1 italic">Memory Limit</label>
                                    <input type="text" name="memory_limit" value="<?php echo ini_get('memory_limit'); ?>" class="w-full px-3 sm:px-4 py-2 sm:py-3 rounded-xl bg-slate-50 border border-slate-100 text-xs font-bold text-slate-700 focus:border-blue-500 transition-all shadow-inner tabular-nums">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[9px] font-black uppercase text-slate-400 px-1 italic">Exec. Time (s)</label>
                                    <input type="number" name="max_execution_time" value="<?php echo ini_get('max_execution_time'); ?>" class="w-full px-3 sm:px-4 py-2 sm:py-3 rounded-xl bg-slate-50 border border-slate-100 text-xs font-bold text-slate-700 focus:border-blue-500 transition-all shadow-inner tabular-nums">
                                </div>
                                <div class="col-span-2 space-y-1">
                                    <label class="text-[9px] font-black uppercase text-slate-400 px-1 italic">Max Input Vars</label>
                                    <input type="number" name="max_input_vars" value="<?php echo ini_get('max_input_vars'); ?>" class="w-full px-3 sm:px-4 py-2 sm:py-3 rounded-xl bg-slate-50 border border-slate-100 text-xs font-bold text-slate-700 focus:border-blue-500 transition-all shadow-inner tabular-nums">
                                </div>
                            </div>
                            <button type="submit" name="save_php_settings" class="w-full py-3 sm:py-4 bg-slate-800 text-white font-black uppercase text-[9px] sm:text-[10px] tracking-wider rounded-xl sm:rounded-2xl hover:bg-black active:scale-[0.98] transition-all shadow-lg mt-3 sm:mt-4 flex items-center justify-center gap-2 sm:gap-3">
                                <i class="fas fa-bolt text-xs"></i> Aplicar Ajustes
                            </button>
                        </form>
                    </div>
                    
                    <div class="bg-white rounded-2xl sm:rounded-[2.5rem] p-4 sm:p-8 shadow-sm border border-slate-200 border-l-4 sm:border-l-[6px] border-l-red-500">
                        <h4 class="text-xs sm:text-sm font-black text-slate-400 uppercase mb-4 sm:mb-6 flex items-center gap-2 italic"><i class="fas fa-bug text-red-500"></i> Debug Mode</h4>
                        <form action="db_code.php" method="POST">
                            <div class="p-3.5 sm:p-4 bg-slate-50 rounded-xl sm:rounded-2xl border border-slate-100 mb-4 sm:mb-6 flex items-center justify-between shadow-inner">
                                <div class="flex flex-col">
                                    <span class="text-xs font-black text-slate-700 uppercase tracking-tight leading-none mb-1">Visualizar Errores</span>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase italic">Solo desarrollo</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="show_errors" value="1" <?php echo (defined('DEBUG_MODE') && DEBUG_MODE) ? 'checked' : ''; ?> class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-red-500"></div>
                                </label>
                            </div>
                            <button type="submit" name="save_debug_settings" class="w-full py-3 sm:py-4 bg-red-600 text-white font-black uppercase text-[9px] sm:text-[10px] tracking-wider rounded-xl sm:rounded-2xl shadow-lg shadow-red-500/20 hover:bg-red-700 active:scale-[0.98] transition-all flex items-center justify-center gap-2 sm:gap-3">
                                <i class="fas fa-shield-alt text-xs"></i> Actualizar Seguridad
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>

<?php include('includes/scripts.php'); ?>
<?php include('includes/footer.php'); ?>
