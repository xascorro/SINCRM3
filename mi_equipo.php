<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');

/**
 * LÓGICA DE CONTEXTO (Admin vs Club vs Otros)
 */
$is_admin = ($_SESSION['id_rol'] == 1);
$my_club_id = $_SESSION['club'] ?? 0;
$my_role_id = $_SESSION['id_rol'];

// Definir el Club Objetivo (Prioridad: GET > Session)
$target_club_id = (isset($_GET['club_id']) && $is_admin) ? intval($_GET['club_id']) : $my_club_id;

// Determinar Modo de Visualización
// 1. Modo Club (Tiene club o Admin seleccionó uno)
// 2. Modo Rol (No tiene club y no es Admin seleccionando)
$view_mode = ($target_club_id > 0) ? 'CLUB' : 'ROLE';

/**
 * OBTENCIÓN DE DATOS SEGÚN MODO
 */
if ($view_mode == 'CLUB') {
    $query_club = "SELECT * FROM clubes WHERE id = '$target_club_id'";
    $run_club = mysqli_query($connection, $query_club);
    $club_data = mysqli_fetch_array($run_club);
    
    $title = "Mi Equipo";
    $subtitle = "Gestión y censo de " . ($club_data['nombre'] ?? 'Club Desconocido');
    $logo = $club_data['logo'] ?? null;

    // KPI 1: Staff
    $q_count_users = "SELECT COUNT(*) as total FROM usuarios WHERE club = '$target_club_id' AND activo = 1";
    $total_staff = mysqli_fetch_assoc(mysqli_query($connection, $q_count_users))['total'];

    // KPI 2: Atletas
    $q_count_nad = "SELECT COUNT(*) as total FROM nadadoras WHERE club = '$target_club_id' AND activo = 1";
    $total_athletes = mysqli_fetch_assoc(mysqli_query($connection, $q_count_nad))['total'];

    // KPI 3: Media Edad
    $q_avg_age = "SELECT AVG(YEAR(CURDATE()) - año_nacimiento) as media FROM nadadoras WHERE club = '$target_club_id' AND activo = 1";
    $avg_age = round(mysqli_fetch_assoc(mysqli_query($connection, $q_avg_age))['media'], 1);

    // Listado Staff
    $q_staff = "SELECT u.*, r.nombre as rol_nombre FROM usuarios u JOIN roles r ON u.id_rol = r.id WHERE u.club = '$target_club_id' AND u.activo = 1 ORDER BY r.id ASC, u.username ASC";
} else {
    // Modo ROL (Mismo nivel que yo)
    $title = "Compañeros";
    $subtitle = "Usuarios con rol " . $_SESSION['rol'];
    $logo = null;

    // KPI 1: Misma categoría
    $q_count_users = "SELECT COUNT(*) as total FROM usuarios WHERE id_rol = '$my_role_id' AND activo = 1";
    $total_staff = mysqli_fetch_assoc(mysqli_query($connection, $q_count_users))['total'];

    // KPI 2: Sin atletas en este modo
    $total_athletes = 0;
    $avg_age = 0;

    // Listado de pares
    $q_staff = "SELECT u.*, r.nombre as rol_nombre FROM usuarios u JOIN roles r ON u.id_rol = r.id WHERE u.id_rol = '$my_role_id' AND u.activo = 1 ORDER BY u.username ASC";
}
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-surface">
    
    <?php include('includes/topbar.php'); ?>

    <!-- Contenido -->
    <div class="p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend">
        
        <!-- Page Heading -->
        <div class="mb-12 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div class="space-y-2">
                <h1 class="text-5xl font-black text-slate-800 tracking-tighter italic text-primary"><?php echo $title; ?></h1>
                <p class="text-lg text-slate-500 font-medium"><?php echo $subtitle; ?></p>
            </div>
            
            <div class="flex flex-col md:flex-row items-center gap-6">
                <!-- Selector para Admins -->
                <?php if($is_admin): ?>
                <div class="w-full md:w-72">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 block">Cambiar Club (Vista Admin)</label>
                    <form action="mi_equipo.php" method="GET">
                        <select name="club_id" onchange="this.form.submit()" class="v3-select-fix">
                            <option value="0">--- Seleccionar Club ---</option>
                            <?php 
                            $q_clubs_all = "SELECT id, nombre FROM clubes WHERE activo = 1 ORDER BY nombre ASC";
                            $r_clubs_all = mysqli_query($connection, $q_clubs_all);
                            while($c_opt = mysqli_fetch_assoc($r_clubs_all)):
                            ?>
                                <option value="<?php echo $c_opt['id']; ?>" <?php echo ($target_club_id == $c_opt['id']) ? 'selected' : ''; ?>>
                                    <?php echo $c_opt['nombre']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </form>
                </div>
                <?php endif; ?>

                <?php 
                if($logo): 
                    $final_logo = $logo;
                    // Si el logo no existe tal cual y no empieza por la ruta correcta, intentamos arreglarlo
                    if (!file_exists($final_logo) && !str_starts_with($final_logo, 'images/clubes/')) {
                        $final_logo = 'images/clubes/' . $logo;
                    }
                ?>
                    <img src="<?php echo $final_logo; ?>" alt="Logo Club" class="h-20 w-auto object-contain bg-white p-2 rounded-2xl shadow-sm border border-slate-100">
                <?php endif; ?>
            </div>
        </div>

        <!-- KPIs -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
            <!-- Card Usuarios -->
            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-200 border-t-[10px] border-t-emerald-500 transition-all">
                <div class="flex justify-between items-start mb-6">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 shadow-sm"><i class="fas fa-users-gear text-2xl"></i></div>
                    <span class="text-[11px] font-black uppercase text-slate-400 tracking-[0.2em] italic">
                        <?php echo ($view_mode == 'CLUB') ? 'Staff' : 'Compañeros'; ?>
                    </span>
                </div>
                <h3 class="text-4xl font-black text-slate-800 leading-none"><?php echo $total_staff; ?></h3>
                <p class="text-sm font-bold text-slate-400 mt-2 uppercase tracking-tighter">Usuarios activos</p>
            </div>

            <?php if($view_mode == 'CLUB'): ?>
            <!-- Card Nadadoras -->
            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-200 border-t-[10px] border-t-blue-500 transition-all">
                <div class="flex justify-between items-start mb-6">
                    <div class="w-14 h-14 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 shadow-sm"><i class="fas fa-swimmer text-2xl"></i></div>
                    <span class="text-[11px] font-black uppercase text-slate-400 tracking-[0.2em] italic">Atletas</span>
                </div>
                <h3 class="text-4xl font-black text-slate-800 leading-none"><?php echo $total_athletes; ?></h3>
                <p class="text-sm font-bold text-slate-400 mt-2 uppercase tracking-tighter">Nadadoras en censo</p>
            </div>

            <!-- Card Edad Media -->
            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-200 border-t-[10px] border-t-purple-500 transition-all">
                <div class="flex justify-between items-start mb-6">
                    <div class="w-14 h-14 rounded-2xl bg-purple-50 flex items-center justify-center text-purple-600 shadow-sm"><i class="fas fa-birthday-cake text-2xl"></i></div>
                    <span class="text-[11px] font-black uppercase text-slate-400 tracking-[0.2em] italic">Edad Media</span>
                </div>
                <h3 class="text-4xl font-black text-slate-800 leading-none"><?php echo $avg_age; ?> <span class="text-sm">años</span></h3>
                <p class="text-sm font-bold text-slate-400 mt-2 uppercase tracking-tighter">Promedio del equipo</p>
            </div>
            <?php else: ?>
            <!-- Info Complementaria para Otros Roles -->
            <div class="md:col-span-2 bg-slate-900/5 p-8 rounded-[2.5rem] border border-dashed border-slate-300 flex items-center justify-center text-center">
                <div class="max-w-md">
                    <i class="fas fa-circle-info text-slate-300 text-3xl mb-4"></i>
                    <p class="text-slate-500 font-bold italic text-sm leading-relaxed">
                        Esta sección muestra a otros usuarios con tu mismo rango. Para ver el censo de nadadoras es necesario pertenecer a un club específico.
                    </p>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-1 <?php echo ($view_mode == 'CLUB') ? 'lg:grid-cols-2' : ''; ?> gap-12">
            
            <!-- Listado de Usuarios (Staff) -->
            <div class="space-y-6">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center shadow-lg"><i class="fas fa-user-tie"></i></div>
                    <h2 class="text-2xl font-black text-slate-800 italic uppercase tracking-tighter">
                        <?php echo ($view_mode == 'CLUB') ? 'Staff del Club' : 'Miembros del Rol'; ?>
                    </h2>
                </div>
                
                <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50">
                                <th class="px-8 py-5 text-[11px] font-black uppercase text-slate-400 tracking-widest">Usuario</th>
                                <th class="px-8 py-5 text-[11px] font-black uppercase text-slate-400 tracking-widest">Rol</th>
                                <th class="px-8 py-5 text-[11px] font-black uppercase text-slate-400 tracking-widest">Contacto</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php
                            $r_staff = mysqli_query($connection, $q_staff);
                            if(mysqli_num_rows($r_staff) > 0):
                                while($staff = mysqli_fetch_assoc($r_staff)):
                            ?>
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 font-bold group-hover:bg-blue-100 group-hover:text-blue-600 transition-all">
                                            <?php echo strtoupper(substr($staff['username'], 0, 1)); ?>
                                        </div>
                                        <span class="font-bold text-slate-700"><?php echo $staff['username']; ?></span>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="px-3 py-1 bg-slate-100 text-slate-600 text-[10px] font-black rounded-full uppercase italic"><?php echo $staff['rol_nombre']; ?></span>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-slate-500"><?php echo $staff['email']; ?></span>
                                        <?php if($staff['telefono']): ?>
                                            <span class="text-[10px] font-medium text-slate-400"><?php echo $staff['telefono']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                                endwhile; 
                            else:
                            ?>
                                <tr><td colspan="3" class="p-12 text-center text-slate-400 font-bold italic">No se encontraron miembros.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if($view_mode == 'CLUB'): ?>
            <!-- Listado de Nadadoras -->
            <div class="space-y-6">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-lg"><i class="fas fa-person-swimming"></i></div>
                    <h2 class="text-2xl font-black text-slate-800 italic uppercase tracking-tighter">Nadadoras</h2>
                </div>

                <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden">
                    <div class="max-h-[600px] overflow-y-auto no-scrollbar">
                        <table class="w-full text-left border-collapse">
                            <thead class="sticky top-0 bg-white z-10 shadow-sm">
                                <tr class="bg-slate-50">
                                    <th class="px-8 py-5 text-[11px] font-black uppercase text-slate-400 tracking-widest">Nombre</th>
                                    <th class="px-8 py-5 text-[11px] font-black uppercase text-slate-400 tracking-widest">Año</th>
                                    <th class="px-8 py-5 text-[11px] font-black uppercase text-slate-400 tracking-widest">Licencia</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php
                                $q_nadadoras = "SELECT * FROM nadadoras WHERE club = '$target_club_id' AND activo = 1 ORDER BY apellidos ASC, nombre ASC";
                                $r_nadadoras = mysqli_query($connection, $q_nadadoras);
                                if(mysqli_num_rows($r_nadadoras) > 0):
                                    while($nad = mysqli_fetch_assoc($r_nadadoras)):
                                ?>
                                <tr class="hover:bg-blue-50/30 transition-colors group">
                                    <td class="px-8 py-5">
                                        <div class="flex flex-col">
                                            <span class="font-black text-slate-800 uppercase text-sm leading-tight"><?php echo $nad['apellidos']; ?></span>
                                            <span class="font-bold text-slate-500 text-xs"><?php echo $nad['nombre']; ?></span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5 text-center">
                                        <span class="text-sm font-black text-slate-400 italic"><?php echo $nad['año_nacimiento']; ?></span>
                                    </td>
                                    <td class="px-8 py-5">
                                        <span class="text-[10px] font-black text-slate-400 tracking-widest"><?php echo $nad['licencia'] ?: 'N/D'; ?></span>
                                    </td>
                                </tr>
                                <?php 
                                    endwhile; 
                                else:
                                ?>
                                    <tr><td colspan="3" class="p-12 text-center text-slate-400 font-bold italic">No hay nadadoras registradas en este club.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>

    </div>
</main>

<?php 
include('includes/scripts.php');
include('includes/footer.php'); 
?>