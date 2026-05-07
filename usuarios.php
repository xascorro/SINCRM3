<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-surface">
    
    <?php include('includes/topbar.php'); ?>

    <!-- Contenido de la Página -->
    <div class="p-6 md:p-10 max-w-7xl mx-auto w-full">
        
        <!-- Header de Sección -->
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <h1 class="text-4xl font-black text-slate-800 tracking-tighter mb-2 flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-users-gear text-lg"></i></span>
                    Usuarios
                </h1>
                <p class="text-slate-500 font-medium">Control de acceso y perfiles del sistema.</p>
            </div>
            <div class="flex gap-3">
                <button onclick="toggleAddUserPanel()" class="px-6 py-3 bg-blue-600 text-white font-black uppercase text-xs tracking-[0.2em] rounded-2xl shadow-lg shadow-blue-500/20 hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
                    <i class="fas fa-user-plus text-xs"></i> Nuevo Usuario
                </button>
            </div>
        </div>

        <!-- Panel Añadir Usuario (Colapsable) -->
        <div id="addUserPanel" class="hidden mb-10 animate-fade-in-down">
            <div class="bg-white rounded-[2.5rem] p-8 shadow-xl border border-blue-100 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                <h2 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3">
                    <i class="fas fa-id-card text-blue-600"></i> Registro de Nuevo Usuario
                </h2>
                <form action="usuarios_code.php" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Nombre Completo</label>
                        <input type="text" name="username" required class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all text-sm font-bold" placeholder="Ej: Juan Pérez">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Email de Acceso</label>
                        <input type="email" name="email" required class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all text-sm font-bold" placeholder="usuario@sincrm.es">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Asignar Club</label>
                        <div class="relative">
                            <?php 
                            ob_start();
                            include("includes/club_select_option.php");
                            $select = ob_get_clean();
                            echo str_replace(["class='form-control'", 'class="form-control"'], 'class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all text-sm font-bold appearance-none"', $select);
                            ?>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Asignar Rol</label>
                        <div class="relative">
                            <?php 
                            ob_start();
                            include("includes/rol_select_option.php");
                            $select_rol = ob_get_clean();
                            // Limpieza del include antiguo para adaptarlo al diseño v3
                            $select_rol = preg_replace('/<label.*?>.*?<\/label>/i', '', $select_rol);
                            echo str_replace(["class='form-control'", 'class="form-control"', "name='edit_rol'", "name=\"edit_rol\""], ['class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all text-sm font-bold appearance-none"', 'class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all text-sm font-bold appearance-none"', 'name="id_rol"', 'name="id_rol"'], $select_rol);
                            ?>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Contraseña</label>
                        <input type="password" name="password" required class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all text-sm font-bold">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Repetir Contraseña</label>
                        <input type="password" name="r_password" required class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all text-sm font-bold">
                    </div>
                    <div class="flex items-end pb-1">
                        <button type="submit" name="save_btn" class="w-full py-3.5 bg-slate-800 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg hover:bg-slate-900 transition-all flex items-center justify-center gap-2">
                            <i class="fas fa-check"></i> Finalizar Alta
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Alertas de Sesión -->
        <?php if(isset($_SESSION['correcto'])): ?>
            <div class="mb-8 p-4 bg-white border-l-4 border-green-500 text-slate-700 rounded-r-2xl shadow-sm flex items-center gap-4 animate-fade-in">
                <i class="fas fa-check-circle text-green-500"></i>
                <span class="text-sm font-bold"><?php echo $_SESSION['correcto']; unset($_SESSION['correcto']); ?></span>
            </div>
        <?php endif; ?>
        <?php if(isset($_SESSION['estado'])): ?>
            <div class="mb-8 p-4 bg-white border-l-4 border-red-500 text-slate-700 rounded-r-2xl shadow-sm flex items-center gap-4 animate-fade-in">
                <i class="fas fa-exclamation-triangle text-red-500"></i>
                <span class="text-sm font-bold"><?php echo $_SESSION['estado']; unset($_SESSION['estado']); ?></span>
            </div>
        <?php endif; ?>

        <!-- KPIs -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
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
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200 border-l-[6px] border-l-slate-400">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Censo Usuarios</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-2xl font-black text-slate-800"><?php echo $stats['activos']; ?></h3>
                    <span class="text-xs font-bold text-slate-400">/ <?php echo $stats['total']; ?> registrados</span>
                </div>
            </div>
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200 border-l-[6px] border-l-blue-500">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Administradores</p>
                <div class="flex items-baseline gap-2">
                    <?php 
                    $q_a_act = "SELECT COUNT(*) as activos FROM usuarios WHERE id_rol = 1 AND activo = 1";
                    $a_act = mysqli_fetch_assoc(mysqli_query($connection, $q_a_act))['activos'];
                    ?>
                    <h3 class="text-2xl font-black text-blue-600"><?php echo $a_act; ?></h3>
                    <span class="text-xs font-bold text-slate-400">/ <?php echo $stats['admins']; ?> registrados</span>
                </div>
            </div>
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200 border-l-[6px] border-l-purple-500">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Cuerpo Técnico Jueces</p>
                <div class="flex items-baseline gap-2">
                    <?php 
                    $q_j_act = "SELECT COUNT(*) as activos FROM usuarios WHERE (id_rol = 4 OR id_juez_v3 IS NOT NULL) AND activo = 1";
                    $j_act = mysqli_fetch_assoc(mysqli_query($connection, $q_j_act))['activos'];
                    ?>
                    <h3 class="text-2xl font-black text-purple-600"><?php echo $j_act; ?></h3>
                    <span class="text-xs font-bold text-slate-400">/ <?php echo $stats['jueces']; ?> vinculados</span>
                </div>
            </div>
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200 border-l-[6px] border-l-amber-500">
                <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mb-1">Delegados Clubes</p>
                <div class="flex items-baseline gap-2">
                    <?php 
                    $q_c_act = "SELECT COUNT(*) as activos FROM usuarios WHERE id_rol = 5 AND activo = 1";
                    $c_act = mysqli_fetch_assoc(mysqli_query($connection, $q_c_act))['activos'];
                    ?>
                    <h3 class="text-2xl font-black text-amber-600"><?php echo $c_act; ?></h3>
                    <span class="text-xs font-bold text-slate-400">/ <?php echo $stats['clubes']; ?> registrados</span>
                </div>
            </div>
        </div>

        <!-- Tabla -->
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-8 py-6 bg-slate-50/50 border-b border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4">
                <h2 class="text-lg font-black text-slate-800 uppercase tracking-tighter italic">Directorio de Usuarios</h2>
                
                <!-- Buscador Dinámico -->
                <div class="relative w-full md:w-80">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </span>
                    <input type="text" id="userSearchInput" placeholder="Buscar por nombre, email, rol o club..." 
                           class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-2xl text-xs font-bold text-slate-700 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all outline-none"
                           onkeyup="filterUsersTable()">
                </div>
            </div>
            
            <div class="overflow-x-auto no-scrollbar">
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
                        <?php
                        $query = "SELECT u.id, u.username, u.email, u.telefono, u.activo, u.id_juez_v3, r.nombre AS rol, r.id as id_rol, c.nombre_corto AS club_nombre 
                                  FROM usuarios u 
                                  LEFT JOIN roles r ON u.id_rol = r.id 
                                  LEFT JOIN clubes c ON u.club = c.id 
                                  ORDER BY u.id DESC";
                        $res = mysqli_query($connection, $query);
                        while ($row = mysqli_fetch_assoc($res)):
                            $is_active = ($row['activo'] == 1);
                            $is_judge = ($row['id_rol'] == 4);
                            $is_linked = ($row['id_juez_v3'] > 0);
                            $rol_class = ($row['id_rol'] == 1) ? 'bg-blue-50 text-blue-700' : (($row['id_rol'] == 5) ? 'bg-purple-50 text-purple-700' : (($row['id_rol'] == 4) ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600'));
                        ?>
                        <tr class="hover:bg-slate-50/50 transition-colors <?php echo !$is_active ? 'opacity-50' : ''; ?>">
                            <td class="px-8 py-5 text-xs font-black text-slate-300 italic">#<?php echo $row['id']; ?></td>
                            <td class="px-4 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-2xl oceanic-gradient flex items-center justify-center text-white font-black text-xs shadow-md">
                                        <?php echo strtoupper(substr($row['username'] ?? 'U',0,1)); ?>
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-slate-700 leading-tight"><?php echo $row['username']; ?> <?php if(!$is_active) echo '<span class="ml-2 text-[8px] text-red-500 font-black uppercase italic">Baja</span>'; ?></p>
                                        <p class="text-xs font-medium text-slate-400"><?php echo $row['email']; ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-5">
                                <div class="flex items-center gap-2">
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest <?php echo $rol_class; ?>">
                                        <?php echo $row['rol']; ?>
                                    </span>
                                    <?php if($is_judge && !$is_linked): ?>
                                        <i class="fas fa-link-slash text-amber-500 text-xs animate-pulse" title="Sin vinculación oficial"></i>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-4 py-5 text-center text-xs font-bold text-slate-500 italic"><?php echo $row['club_nombre'] ?: '-'; ?></td>
                            <td class="px-4 py-5">
                                <div class="flex items-center justify-center gap-2">
                                    <form action="usuarios_edit.php" method="POST">
                                        <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="edit_btn" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 hover:bg-white hover:text-emerald-500 hover:shadow-md transition-all border border-transparent hover:border-emerald-100"><i class="fas fa-pen-to-square text-sm"></i></button>
                                    </form>
                                    <form action="usuarios_code.php" method="POST" onsubmit="return confirm('¿Eliminar usuario?');">
                                        <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="delete_btn" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 hover:bg-white hover:text-red-500 hover:shadow-md transition-all border border-transparent hover:border-red-100"><i class="fas fa-trash-can text-sm"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<script>
function toggleAddUserPanel() { document.getElementById('addUserPanel').classList.toggle('hidden'); }

function filterUsersTable() {
    const input = document.getElementById('userSearchInput');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('userTable');
    const tr = table.getElementsByTagName('tr');

    for (let i = 1; i < tr.length; i++) {
        let textContent = tr[i].textContent.toLowerCase();
        if (textContent.indexOf(filter) > -1) {
            tr[i].style.display = "";
        } else {
            tr[i].style.display = "none";
        }
    }
}
</script>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
