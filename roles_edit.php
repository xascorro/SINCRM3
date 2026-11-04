<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-surface">
    
    <?php include('includes/topbar.php'); ?>

    <!-- Contenido de la Página -->
    <div class="px-3 py-4 sm:p-6 md:p-10 max-w-6xl mx-auto w-full font-lexend text-primary">
        
        <!-- Header de Sección -->
        <div class="mb-6 sm:mb-10 flex flex-col sm:flex-row sm:items-end justify-between gap-4 sm:gap-6">
            <div>
                <h1 class="text-2xl sm:text-4xl font-black text-slate-800 tracking-tighter mb-1 sm:mb-2 flex items-center gap-2.5 sm:gap-3">
                    <span class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-user-shield text-base sm:text-lg"></i></span>
                    Editar Rol
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 font-medium">Gestión de jerarquía y niveles de acceso al sistema.</p>
            </div>
            <div class="flex gap-3">
                <a href="roles.php" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl sm:rounded-2xl hover:bg-slate-50 transition-all flex items-center gap-2 text-xs tap-press shadow-sm">
                    <i class="fas fa-arrow-left text-xs"></i> Volver a Roles
                </a>
            </div>
        </div>

        <?php
        if(isset($_POST['edit_id'])):
            $id = mysqli_real_escape_string($connection, $_POST['edit_id']);
            $query = "SELECT * FROM roles WHERE id = '$id'";
            $query_run = mysqli_query($connection, $query);
            foreach ($query_run as $row):
        ?>
            <form action="roles_code.php" method="POST" class="animate-fade-in space-y-6 sm:space-y-10">
                <input type="hidden" name="edit_id" value="<?php echo $row['id']?>">

                <!-- BLOQUE BÁSICO -->
                <div class="bg-white rounded-2xl sm:rounded-[2.5rem] p-5 sm:p-8 md:p-10 shadow-sm border border-slate-200 border-t-4 sm:border-t-[6px] border-t-emerald-500 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/5 rounded-full -mr-16 -mt-16 blur-2xl pointer-events-none"></div>
                    
                    <h2 class="text-base sm:text-xl font-black text-slate-800 mb-6 flex items-center gap-2.5 sm:gap-3">
                        <i class="fas fa-sliders text-emerald-600"></i> Parámetros Principales
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6">
                        <div class="md:col-span-2 space-y-1.5 sm:space-y-2">
                            <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Nombre del Rol</label>
                            <input type="text" name="edit_nombre" value="<?php echo htmlspecialchars($row['nombre']); ?>" required class="w-full px-4 sm:px-5 py-2.5 sm:py-3.5 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-200 focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/5 transition-all text-xs sm:text-sm font-bold text-slate-700">
                        </div>
                        <div class="space-y-1.5 sm:space-y-2">
                            <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Nivel de Acceso (0-100)</label>
                            <input type="number" name="edit_level" value="<?php echo htmlspecialchars($row['level']); ?>" required min="0" max="100" class="w-full px-4 sm:px-5 py-2.5 sm:py-3.5 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-200 focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/5 transition-all text-xs sm:text-sm font-bold text-slate-700" placeholder="0-100">
                        </div>
                    </div>

                    <div class="mt-6 p-4 bg-emerald-50/70 rounded-xl sm:rounded-2xl border border-emerald-100">
                        <p class="text-xs text-emerald-800 font-medium leading-relaxed flex items-center gap-2">
                            <i class="fas fa-info-circle text-emerald-600 shrink-0"></i> 
                            <span>Los niveles definen jerarquía y prioridades de acceso. El nivel 100 corresponde a SuperAdministrador.</span>
                        </p>
                    </div>
                </div>

                <?php if($row['id'] != 1): // Los permisos del admin no se editan, son '*' ?>
                <!-- GESTIÓN DE PERMISOS DINÁMICOS -->
                <div class="space-y-6 sm:space-y-8">
                    <div class="flex items-center gap-3 sm:gap-6">
                        <div class="h-px flex-1 bg-slate-200"></div>
                        <h2 class="text-sm sm:text-lg font-black text-slate-800 uppercase tracking-tighter flex items-center gap-2 sm:gap-3 shrink-0">
                            <i class="fas fa-shield-halved text-blue-600"></i> Permisos Modulares del Sistema
                        </h2>
                        <div class="h-px flex-1 bg-slate-200"></div>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-6 sm:gap-8">
                        <?php
                        $group_icons = [
                            'Competiciones' => 'fa-trophy',
                            'Inscripciones' => 'fa-user-pen',
                            'Puntuaciones' => 'fa-star',
                            'Seguridad' => 'fa-lock',
                            'Sistema' => 'fa-gears',
                            'Maestros' => 'fa-database',
                            'General' => 'fa-folder-open'
                        ];

                        $permisos_actuales = [];
                        $q_curr = "SELECT id_pagina FROM permisos_roles WHERE id_rol = '".$row['id']."'";
                        $res_curr = mysqli_query($connection, $q_curr);
                        while($pc = mysqli_fetch_assoc($res_curr)) $permisos_actuales[] = $pc['id_pagina'];

                        $q_groups = "SELECT DISTINCT grupo FROM paginas_sistema ORDER BY grupo ASC";
                        $res_groups = mysqli_query($connection, $q_groups);
                        
                        while($g = mysqli_fetch_assoc($res_groups)):
                            $grupo = $g['grupo'];
                            $icon = $group_icons[$grupo] ?? 'fa-circle-dot';
                        ?>
                        <div class="bg-white rounded-2xl sm:rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden animate-fade-in-up">
                            <div class="px-4 sm:px-8 py-3.5 sm:py-5 bg-slate-50/70 border-b border-slate-100 flex justify-between items-center gap-3">
                                <div class="flex items-center gap-2.5 sm:gap-3">
                                    <span class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs">
                                        <i class="fas <?php echo $icon; ?>"></i>
                                    </span>
                                    <h3 class="text-xs font-black uppercase text-slate-700 tracking-widest"><?php echo htmlspecialchars($grupo); ?></h3>
                                </div>
                                <button type="button" onclick="toggleGroup('<?php echo md5($grupo); ?>')" class="px-3 py-1.5 bg-blue-50 text-blue-600 text-[9px] font-black uppercase tracking-wider rounded-lg hover:bg-blue-600 hover:text-white transition-all border border-blue-100 tap-press">
                                    Invertir Selección
                                </button>
                            </div>
                            
                            <div class="p-4 sm:p-6 md:p-8 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3 sm:gap-4">
                                <?php
                                $q_pages = "SELECT * FROM paginas_sistema WHERE grupo = '$grupo' ORDER BY nombre ASC";
                                $res_pages = mysqli_query($connection, $q_pages);
                                while($p = mysqli_fetch_assoc($res_pages)):
                                    $checked = in_array($p['id'], $permisos_actuales) ? 'checked' : '';
                                ?>
                                <label class="flex items-center justify-between p-3 sm:p-4 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-200/80 hover:border-blue-400 hover:bg-white hover:shadow-md transition-all cursor-pointer group/item select-none">
                                    <div class="flex-1 pr-3 min-w-0">
                                        <p class="text-[11px] font-black text-slate-700 leading-tight truncate group-hover/item:text-blue-600 transition-colors"><?php echo htmlspecialchars($p['nombre']); ?></p>
                                        <p class="text-[9px] font-mono text-slate-400 truncate mt-0.5"><?php echo htmlspecialchars($p['archivo']); ?></p>
                                    </div>
                                    <div class="relative inline-flex items-center cursor-pointer shrink-0">
                                        <input type="checkbox" name="permisos[]" value="<?php echo $p['id']; ?>" <?php echo $checked; ?> class="sr-only peer group-<?php echo md5($grupo); ?>">
                                        <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600 shadow-inner"></div>
                                    </div>
                                </label>
                                <?php endwhile; ?>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <script>
                function toggleGroup(groupId) {
                    const checks = document.querySelectorAll('.group-' + groupId);
                    const anyUnchecked = Array.from(checks).some(c => !c.checked);
                    checks.forEach(c => c.checked = anyUnchecked);
                }
                </script>
                <?php endif; ?>

                <!-- BOTONES DE ACCIÓN -->
                <div class="bg-white rounded-2xl sm:rounded-[2.5rem] p-5 sm:p-8 shadow-sm border border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-center sm:text-left">
                        <p class="text-[10px] font-black uppercase text-blue-600 tracking-wider">Confirmación Técnica</p>
                        <p class="text-xs text-slate-400 font-medium">Revisa los permisos asignados antes de aplicar los cambios.</p>
                    </div>
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <a href="roles.php" class="w-full sm:w-auto px-6 py-3.5 text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 text-center transition-colors">
                            Cancelar
                        </a>
                        <button type="submit" name="update_btn" class="w-full sm:w-auto px-8 sm:px-12 py-3.5 sm:py-4 bg-blue-600 text-white font-black uppercase text-xs tracking-widest rounded-xl sm:rounded-2xl shadow-lg shadow-blue-500/20 hover:bg-blue-700 active:scale-[0.98] transition-all flex items-center justify-center gap-2 tap-press">
                            <i class="fas fa-save text-sm"></i> Guardar Cambios
                        </button>
                    </div>
                </div>
            </form>
        <?php 
            endforeach; 
        else:
            header('Location: roles.php');
            exit();
        endif;
        ?>

    </div>
</main>

<?php 
include('includes/scripts.php'); 
include('includes/footer.php'); 
?>
