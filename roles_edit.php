<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-surface">
    
    <?php include('includes/topbar.php'); ?>

    <!-- Contenido de la Página -->
    <div class="p-6 md:p-10 max-w-6xl mx-auto w-full font-lexend text-primary">
        
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
        if(isset($_POST['edit_id'])):
            $id = mysqli_real_escape_string($connection, $_POST['edit_id']);
            $query = "SELECT * FROM roles WHERE id = '$id'";
            $query_run = mysqli_query($connection, $query);
            foreach ($query_run as $row):
        ?>
            <form action="roles_code.php" method="POST" class="animate-fade-in space-y-12">
                <input type="hidden" name="edit_id" value="<?php echo $row['id']?>">

                <!-- BLOQUE BÁSICO -->
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
                </div>

                <?php if($row['id'] != 1): // Los permisos del admin no se editan, son '*' ?>
                <!-- GESTIÓN DE PERMISOS DINÁMICOS -->
                <div class="space-y-8">
                    <div class="flex items-center gap-6">
                        <div class="h-px flex-1 bg-slate-200"></div>
                        <h2 class="text-xl font-black text-slate-800 uppercase tracking-tighter flex items-center gap-3"><i class="fas fa-shield-halved text-blue-600"></i> Permisos del Sistema</h2>
                        <div class="h-px flex-1 bg-slate-200"></div>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-10">
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
                        <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden animate-fade-in-up">
                            <div class="px-8 py-5 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
                                <div class="flex items-center gap-3">
                                    <i class="fas <?php echo $icon; ?> text-slate-400 text-sm"></i>
                                    <h3 class="text-xs font-black uppercase text-slate-700 tracking-widest"><?php echo $grupo; ?></h3>
                                </div>
                                <button type="button" onclick="toggleGroup('<?php echo md5($grupo); ?>')" class="px-3 py-1.5 bg-blue-50 text-blue-600 text-[9px] font-black uppercase tracking-widest rounded-lg hover:bg-blue-600 hover:text-white transition-all border border-blue-100 uppercase">Invertir Selección</button>
                            </div>
                            
                            <div class="p-8 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                                <?php
                                $q_pages = "SELECT * FROM paginas_sistema WHERE grupo = '$grupo' ORDER BY nombre ASC";
                                $res_pages = mysqli_query($connection, $q_pages);
                                while($p = mysqli_fetch_assoc($res_pages)):
                                    $checked = in_array($p['id'], $permisos_actuales) ? 'checked' : '';
                                ?>
                                <label class="flex items-center justify-between p-4 rounded-2xl bg-slate-50 border border-slate-100 hover:border-blue-400 hover:bg-white hover:shadow-md transition-all cursor-pointer group/item">
                                    <div class="flex-1 pr-4">
                                        <p class="text-[11px] font-black text-slate-700 leading-tight uppercase tracking-tighter group-hover/item:text-blue-600 transition-colors"><?php echo $p['nombre']; ?></p>
                                        <p class="text-[8px] font-bold text-slate-400 italic mt-0.5"><?php echo $p['archivo']; ?></p>
                                    </div>
                                    <div class="relative inline-flex items-center cursor-pointer">
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

                <!-- BOTONES ACCIÓN -->
                <div class="bg-slate-900 rounded-[2.5rem] p-8 md:p-10 shadow-2xl flex flex-col md:flex-row items-center justify-between gap-6 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-64 h-64 bg-blue-500/10 rounded-full -ml-32 -mt-32 blur-3xl"></div>
                    <div class="relative z-10 text-center md:text-left">
                        <p class="text-[10px] font-black uppercase text-blue-400 tracking-[0.2em] mb-1">Confirmación Técnica</p>
                        <p class="text-sm font-medium text-slate-400">Verifica los permisos antes de aplicar los cambios al rol.</p>
                    </div>
                    <div class="relative z-10 w-full md:w-auto flex flex-col sm:flex-row gap-4 items-center">
                        <div class="text-right hidden md:block mr-4">
                            <p class="text-[10px] font-medium text-slate-500 uppercase italic">Referencia: ROL_#<?php echo $row['id']; ?></p>
                        </div>
                        <button type="submit" name="update_btn" class="w-full sm:w-auto px-12 py-5 bg-blue-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-xl shadow-blue-500/20 hover:bg-blue-500 hover:scale-105 active:scale-95 transition-all flex items-center justify-center gap-3">
                            <i class="fas fa-save text-lg"></i> Guardar Cambios
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
