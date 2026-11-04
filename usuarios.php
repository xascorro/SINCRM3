<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-surface">
    
    <?php include('includes/topbar.php'); ?>

    <!-- Contenido de la Página -->
    <div class="px-3 py-4 sm:p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend">
        
        <!-- Header de Sección -->
        <div class="mb-6 sm:mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4 sm:gap-6">
            <div>
                <h1 class="text-2xl sm:text-4xl font-black text-slate-800 tracking-tighter mb-1 sm:mb-2 flex items-center gap-2.5 sm:gap-3">
                    <span class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-users-gear text-base sm:text-lg"></i></span>
                    Usuarios
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 font-medium">Control de acceso y perfiles del sistema.</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2.5 sm:gap-4 items-stretch sm:items-center">
                <!-- Buscador Dinámico -->
                <div class="relative w-full sm:w-80">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </span>
                    <input type="text" id="userSearchInput" placeholder="Buscar por nombre, email, rol o club..." 
                           class="w-full pl-9 pr-3.5 py-2.5 sm:py-3 bg-white border border-slate-200 rounded-xl sm:rounded-2xl text-xs font-bold text-slate-700 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all outline-none shadow-sm"
                           onkeyup="filterUsersTable()">
                </div>
                <button onclick="toggleAddUserPanel()" class="px-5 sm:px-6 py-2.5 sm:py-3 bg-blue-600 text-white font-black uppercase text-xs tracking-wider rounded-xl sm:rounded-2xl shadow-lg shadow-blue-500/20 hover:scale-105 active:scale-[0.98] transition-all flex items-center justify-center gap-2 shrink-0 tap-press">
                    <i class="fas fa-user-plus text-xs"></i> Nuevo Usuario
                </button>
            </div>
        </div>

        <!-- Panel Añadir Usuario (Colapsable) -->
        <div id="addUserPanel" class="hidden mb-6 sm:mb-10 animate-fade-in-down">
            <div class="bg-white rounded-2xl sm:rounded-[2.5rem] p-4 sm:p-8 shadow-xl border border-blue-100 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -mr-16 -mt-16 blur-2xl pointer-events-none"></div>
                <h2 class="text-base sm:text-xl font-black text-slate-800 mb-4 sm:mb-8 flex items-center gap-2.5 sm:gap-3">
                    <i class="fas fa-id-card text-blue-600"></i> Registro de Nuevo Usuario
                </h2>
                <form action="usuarios_code.php" method="POST" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6">
                    <div class="space-y-1.5 sm:space-y-2">
                        <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1">Nombre Completo</label>
                        <input type="text" name="username" required class="w-full px-4 sm:px-5 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all text-xs sm:text-sm font-bold" placeholder="Ej: Juan Pérez">
                    </div>
                    <div class="space-y-1.5 sm:space-y-2">
                        <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1">Email de Acceso</label>
                        <input type="email" name="email" required class="w-full px-4 sm:px-5 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all text-xs sm:text-sm font-bold" placeholder="usuario@sincrm.es">
                    </div>
                    <div class="space-y-1.5 sm:space-y-2">
                        <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1">Asignar Club</label>
                        <div class="relative">
                            <?php 
                            ob_start();
                            include("includes/club_select_option.php");
                            $select = ob_get_clean();
                            echo str_replace(["class='form-control'", 'class="form-control"'], 'class="w-full px-4 sm:px-5 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all text-xs sm:text-sm font-bold appearance-none"', $select);
                            ?>
                        </div>
                    </div>
                    <div class="space-y-1.5 sm:space-y-2">
                        <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1">Asignar Rol</label>
                        <div class="relative">
                            <?php 
                            ob_start();
                            include("includes/rol_select_option.php");
                            $select_rol = ob_get_clean();
                            $select_rol = preg_replace('/<label.*?>.*?<\/label>/i', '', $select_rol);
                            echo str_replace(["class='form-control'", 'class="form-control"', "name='edit_rol'", "name=\"edit_rol\""], ['class="w-full px-4 sm:px-5 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all text-xs sm:text-sm font-bold appearance-none"', 'class="w-full px-4 sm:px-5 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all text-xs sm:text-sm font-bold appearance-none"', 'name="id_rol"', 'name="id_rol"'], $select_rol);
                            ?>
                        </div>
                    </div>
                    <div class="space-y-1.5 sm:space-y-2">
                        <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1">Contraseña</label>
                        <input type="password" name="password" required class="w-full px-4 sm:px-5 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all text-xs sm:text-sm font-bold">
                    </div>
                    <div class="space-y-1.5 sm:space-y-2">
                        <label class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 px-1">Repetir Contraseña</label>
                        <input type="password" name="r_password" required class="w-full px-4 sm:px-5 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all text-xs sm:text-sm font-bold">
                    </div>
                    <div class="sm:col-span-2 md:col-span-3 pt-3 sm:pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" onclick="toggleAddUserPanel()" class="text-xs font-black uppercase text-slate-400 hover:text-slate-600 transition-colors px-3 py-2">Cancelar</button>
                        <button type="submit" name="save_btn" class="px-6 sm:px-8 py-2.5 sm:py-3 bg-blue-600 text-white font-black uppercase text-xs tracking-wider rounded-xl sm:rounded-2xl shadow-lg hover:bg-blue-700 active:scale-[0.98] transition-all flex items-center justify-center gap-2 tap-press">
                            <i class="fas fa-check"></i> Finalizar Alta
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Alertas de Sesión -->
        <?php include('includes/alertas_v4.php'); ?>

        <!-- KPIs -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-6 mb-6 sm:mb-10">
            <?php
            $q_stats = "SELECT 
                COUNT(*) as total, 
                SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as activos, 
                SUM(CASE WHEN id_rol = 4 OR id_juez_v3 IS NOT NULL THEN 1 ELSE 0 END) as jueces,
                SUM(CASE WHEN id_rol = 5 THEN 1 ELSE 0 END) as clubes, 
                SUM(CASE WHEN id_rol = 1 THEN 1 ELSE 0 END) as admins 
                FROM usuarios";
            $res_stats = mysqli_query($connection, $q_stats);
            $stats = mysqli_fetch_assoc($res_stats);
            ?>
            <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200 border-l-4 sm:border-l-[6px] border-l-slate-400">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1 truncate">Censo Usuarios</p>
                <div class="flex items-baseline gap-1.5 sm:gap-2">
                    <h3 class="text-xl sm:text-2xl font-black text-slate-800 tabular-nums"><?php echo $stats['activos']; ?></h3>
                    <span class="text-[10px] sm:text-xs font-bold text-slate-400 tabular-nums truncate">/ <?php echo $stats['total']; ?></span>
                </div>
            </div>
            <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200 border-l-4 sm:border-l-[6px] border-l-blue-500">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1 truncate">Administradores</p>
                <div class="flex items-baseline gap-1.5 sm:gap-2">
                    <?php 
                    $q_a_act = "SELECT COUNT(*) as activos FROM usuarios WHERE id_rol = 1 AND activo = 1";
                    $a_act = mysqli_fetch_assoc(mysqli_query($connection, $q_a_act))['activos'];
                    ?>
                    <h3 class="text-xl sm:text-2xl font-black text-blue-600 tabular-nums"><?php echo $a_act; ?></h3>
                    <span class="text-[10px] sm:text-xs font-bold text-slate-400 tabular-nums truncate">/ <?php echo $stats['admins']; ?></span>
                </div>
            </div>
            <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200 border-l-4 sm:border-l-[6px] border-l-purple-500">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1 truncate">Cuerpo Jueces</p>
                <div class="flex items-baseline gap-1.5 sm:gap-2">
                    <?php 
                    $q_j_act = "SELECT COUNT(*) as activos FROM usuarios WHERE (id_rol = 4 OR id_juez_v3 IS NOT NULL) AND activo = 1";
                    $j_act = mysqli_fetch_assoc(mysqli_query($connection, $q_j_act))['activos'];
                    ?>
                    <h3 class="text-xl sm:text-2xl font-black text-purple-600 tabular-nums"><?php echo $j_act; ?></h3>
                    <span class="text-[10px] sm:text-xs font-bold text-slate-400 tabular-nums truncate">/ <?php echo $stats['jueces']; ?></span>
                </div>
            </div>
            <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200 border-l-4 sm:border-l-[6px] border-l-amber-500">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1 truncate">Delegados Clubes</p>
                <div class="flex items-baseline gap-1.5 sm:gap-2">
                    <?php 
                    $q_c_act = "SELECT COUNT(*) as activos FROM usuarios WHERE id_rol = 5 AND activo = 1";
                    $c_act = mysqli_fetch_assoc(mysqli_query($connection, $q_c_act))['activos'];
                    ?>
                    <h3 class="text-xl sm:text-2xl font-black text-amber-600 tabular-nums"><?php echo $c_act; ?></h3>
                    <span class="text-[10px] sm:text-xs font-bold text-slate-400 tabular-nums truncate">/ <?php echo $stats['clubes']; ?></span>
                </div>
            </div>
        </div>

        <?php
        $query = "SELECT u.id, u.username, u.email, u.telefono, u.activo, u.id_juez_v3, u.email_confirmado, u.foto, r.nombre AS rol, r.id as id_rol, c.nombre_corto AS club_nombre 
                  FROM usuarios u 
                  LEFT JOIN roles r ON u.id_rol = r.id 
                  LEFT JOIN clubes c ON u.club = c.id 
                  ORDER BY u.id DESC";
        $res = mysqli_query($connection, $query);
        $users_list = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $users_list[] = $row;
        }
        ?>

        <!-- Directorio de Usuarios -->
        <div class="bg-white rounded-2xl sm:rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-4 sm:px-8 py-3.5 sm:py-6 bg-slate-50/50 border-b border-slate-100 flex justify-between items-center">
                <h2 class="text-sm sm:text-lg font-black text-slate-800 uppercase tracking-tighter italic">Directorio de Usuarios</h2>
                <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest tabular-nums"><?php echo count($users_list); ?> total</span>
            </div>

            <!-- Vista Móvil: Tarjetas (md:hidden) -->
            <div class="md:hidden divide-y divide-slate-100">
                <?php if (empty($users_list)): ?>
                    <div class="p-8 text-center text-slate-400 text-xs font-medium">No hay usuarios registrados.</div>
                <?php else: ?>
                    <?php foreach ($users_list as $row): 
                        $is_active = ($row['activo'] == 1);
                        $is_verified = ($row['email_confirmado'] == 1);
                        $is_judge = ($row['id_rol'] == 4);
                        $is_linked = ($row['id_juez_v3'] > 0);
                        $rol_class = ($row['id_rol'] == 1) ? 'bg-blue-50 text-blue-700 border border-blue-100' : (($row['id_rol'] == 5) ? 'bg-purple-50 text-purple-700 border border-purple-100' : (($row['id_rol'] == 4) ? 'bg-amber-50 text-amber-700 border border-amber-100' : 'bg-slate-100 text-slate-600'));
                        $has_photo = !empty($row['foto']) && file_exists(__DIR__ . '/' . $row['foto']);
                        $search_string = strtolower($row['username'] . ' ' . $row['email'] . ' ' . ($row['rol'] ?? '') . ' ' . ($row['club_nombre'] ?? ''));
                    ?>
                    <div class="user-mobile-card p-4 space-y-3 <?php echo !$is_active ? 'opacity-50 bg-slate-50/50' : ''; ?>" data-search="<?php echo htmlspecialchars($search_string); ?>">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <?php if ($has_photo): ?>
                                    <img src="<?php echo htmlspecialchars($row['foto']); ?>" class="w-10 h-10 rounded-2xl object-cover shadow-sm border border-slate-100 shrink-0" alt="Avatar">
                                <?php else: ?>
                                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-blue-600 to-cyan-500 flex items-center justify-center text-white font-black text-xs shadow-sm shrink-0">
                                        <?php echo strtoupper(substr($row['username'] ?? 'U', 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-sm font-black text-slate-800 truncate"><?php echo htmlspecialchars($row['username']); ?></h3>
                                        <span class="text-[10px] font-bold text-slate-300 tabular-nums">#<?php echo $row['id']; ?></span>
                                    </div>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <p class="text-xs text-slate-400 truncate"><?php echo htmlspecialchars($row['email']); ?></p>
                                        <?php if ($is_verified): ?>
                                            <i class="fas fa-circle-check text-emerald-500 text-[10px]" title="Email Verificado"></i>
                                        <?php else: ?>
                                            <i class="fas fa-circle-xmark text-red-400 text-[10px]" title="Email No Verificado"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-wider shrink-0 <?php echo $is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-rose-50 text-rose-700 border border-rose-100'; ?>">
                                <?php echo $is_active ? 'Activo' : 'Baja'; ?>
                            </span>
                        </div>

                        <div class="flex items-center justify-between pt-1 text-xs">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-bold <?php echo $rol_class; ?>">
                                    <?php echo htmlspecialchars($row['rol'] ?? 'Sin rol'); ?>
                                </span>
                                <?php if ($row['club_nombre']): ?>
                                    <span class="px-2.5 py-0.5 rounded-lg bg-slate-100 text-slate-600 text-[10px] font-semibold flex items-center gap-1">
                                        <i class="fas fa-shield text-[9px] text-slate-400"></i> <?php echo htmlspecialchars($row['club_nombre']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="flex items-center gap-1 shrink-0">
                                <?php if (!$is_verified): ?>
                                <form action="usuarios_code.php" method="POST" title="Verificar Manualmente">
                                    <input type="hidden" name="verify_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="verify_btn" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors">
                                        <i class="fas fa-envelope-circle-check text-xs"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                                <form action="usuarios_edit.php" method="POST">
                                    <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="edit_btn" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition-colors">
                                        <i class="fas fa-pen-to-square text-xs"></i>
                                    </button>
                                </form>
                                <form action="usuarios_code.php" method="POST" onsubmit="return confirm('¿Eliminar usuario <?php echo htmlspecialchars($row['username']); ?>?');">
                                    <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="delete_btn" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                        <i class="fas fa-trash-can text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Vista Escritorio: Tabla (hidden md:block) -->
            <div class="hidden md:block overflow-x-auto no-scrollbar">
                <table class="w-full text-left border-collapse" id="userTable">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-400 text-[10px] font-black uppercase tracking-[0.2em]">
                            <th class="px-8 py-4 w-16">ID</th>
                            <th class="px-4 py-4">Usuario</th>
                            <th class="px-4 py-4">Perfil</th>
                            <th class="px-4 py-4 text-center">Club</th>
                            <th class="px-4 py-4 text-center w-32">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($users_list as $row):
                            $is_active = ($row['activo'] == 1);
                            $is_verified = ($row['email_confirmado'] == 1);
                            $is_judge = ($row['id_rol'] == 4);
                            $is_linked = ($row['id_juez_v3'] > 0);
                            $rol_class = ($row['id_rol'] == 1) ? 'bg-blue-50 text-blue-700 border border-blue-100' : (($row['id_rol'] == 5) ? 'bg-purple-50 text-purple-700 border border-purple-100' : (($row['id_rol'] == 4) ? 'bg-amber-50 text-amber-700 border border-amber-100' : 'bg-slate-100 text-slate-600'));
                            $has_photo = !empty($row['foto']) && file_exists(__DIR__ . '/' . $row['foto']);
                        ?>
                        <tr class="hover:bg-slate-50/50 transition-colors <?php echo !$is_active ? 'opacity-50' : ''; ?>">
                            <td class="px-8 py-5 text-xs font-black text-slate-300 italic tabular-nums">#<?php echo $row['id']; ?></td>
                            <td class="px-4 py-5">
                                <div class="flex items-center gap-4">
                                    <?php if ($has_photo): ?>
                                        <img src="<?php echo htmlspecialchars($row['foto']); ?>" class="w-10 h-10 rounded-2xl object-cover shadow-sm border border-slate-100 shrink-0" alt="Avatar">
                                    <?php else: ?>
                                        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-blue-600 to-cyan-500 flex items-center justify-center text-white font-black text-xs shadow-md shrink-0">
                                            <?php echo strtoupper(substr($row['username'] ?? 'U', 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <p class="text-sm font-black text-slate-700 leading-tight flex items-center gap-2">
                                            <?php echo htmlspecialchars($row['username']); ?>
                                            <?php if(!$is_active): ?>
                                                <span class="text-[8px] text-red-500 font-black uppercase italic bg-red-50 px-2 py-0.5 rounded-full border border-red-100">Baja</span>
                                            <?php endif; ?>
                                        </p>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <p class="text-xs font-medium text-slate-400"><?php echo htmlspecialchars($row['email']); ?></p>
                                            <?php if($is_verified): ?>
                                                <i class="fas fa-circle-check text-emerald-500 text-[10px]" title="Email Verificado"></i>
                                            <?php else: ?>
                                                <i class="fas fa-circle-xmark text-red-400 text-[10px]" title="Email NO Verificado"></i>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-5">
                                <div class="flex items-center gap-2">
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest <?php echo $rol_class; ?>">
                                        <?php echo htmlspecialchars($row['rol'] ?? 'Sin rol'); ?>
                                    </span>
                                    <?php if($is_judge && !$is_linked): ?>
                                        <i class="fas fa-link-slash text-amber-500 text-xs animate-pulse" title="Sin vinculación oficial a ficha de juez"></i>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-4 py-5 text-center text-xs font-bold text-slate-500 italic"><?php echo htmlspecialchars($row['club_nombre'] ?: '-'); ?></td>
                            <td class="px-4 py-5">
                                <div class="flex items-center justify-center gap-2">
                                    <?php if(!$is_verified): ?>
                                    <form action="usuarios_code.php" method="POST" title="Verificar Email Manualmente">
                                        <input type="hidden" name="verify_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="verify_btn" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 hover:bg-white hover:text-blue-500 hover:shadow-md transition-all border border-transparent hover:border-blue-100">
                                            <i class="fas fa-envelope-circle-check text-sm"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                    <form action="usuarios_edit.php" method="POST">
                                        <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="edit_btn" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 hover:bg-white hover:text-emerald-500 hover:shadow-md transition-all border border-transparent hover:border-emerald-100">
                                            <i class="fas fa-pen-to-square text-sm"></i>
                                        </button>
                                    </form>
                                    <form action="usuarios_code.php" method="POST" onsubmit="return confirm('¿Eliminar usuario <?php echo htmlspecialchars($row['username']); ?>?');">
                                        <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="delete_btn" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 hover:bg-white hover:text-red-500 hover:shadow-md transition-all border border-transparent hover:border-red-100">
                                            <i class="fas fa-trash-can text-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<script>
function toggleAddUserPanel() { 
    document.getElementById('addUserPanel').classList.toggle('hidden'); 
}

function filterUsersTable() {
    const input = document.getElementById('userSearchInput');
    const filter = input.value.toLowerCase().trim();

    // Filtrar tabla escritorio
    const table = document.getElementById('userTable');
    if (table) {
        const tr = table.getElementsByTagName('tr');
        for (let i = 1; i < tr.length; i++) {
            let textContent = tr[i].textContent.toLowerCase();
            tr[i].style.display = textContent.indexOf(filter) > -1 ? "" : "none";
        }
    }

    // Filtrar tarjetas móvil
    const mobileCards = document.querySelectorAll('.user-mobile-card');
    mobileCards.forEach(card => {
        const searchAttr = card.getAttribute('data-search') || card.textContent.toLowerCase();
        card.style.display = searchAttr.indexOf(filter) > -1 ? "" : "none";
    });
}
</script>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
