<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');

/**
 * Helper para extraer iniciales de un nombre
 */
if (!function_exists('get_team_initials')) {
    function get_team_initials($name) {
        $parts = preg_split('/\s+/', trim((string)$name));
        if (count($parts) >= 2) {
            return mb_strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[1], 0, 1));
        } elseif (count($parts) == 1 && !empty($parts[0])) {
            return mb_strtoupper(mb_substr($parts[0], 0, 2));
        }
        return 'U';
    }
}

/**
 * Helper para calcular categoría estimada según el año de nacimiento
 */
if (!function_exists('get_swimmer_category_badge')) {
    function get_swimmer_category_badge($year, $season_year = null) {
        if (!$year || intval($year) <= 1900) {
            return ['nombre' => 'S/D', 'class' => 'bg-slate-100 text-slate-500 border-slate-200'];
        }
        if (!$season_year) {
            $cur_m = intval(date('n'));
            $cur_y = intval(date('Y'));
            $season_year = ($cur_m >= 9) ? ($cur_y + 1) : $cur_y;
        }
        $edad = $season_year - intval($year);

        if ($edad <= 8) {
            return ['nombre' => 'Benjamín', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200'];
        } elseif ($edad <= 12) {
            return ['nombre' => 'Alevín', 'class' => 'bg-sky-50 text-sky-700 border-sky-200'];
        } elseif ($edad <= 15) {
            return ['nombre' => 'Infantil', 'class' => 'bg-violet-50 text-violet-700 border-violet-200'];
        } elseif ($edad <= 18) {
            return ['nombre' => 'Junior', 'class' => 'bg-amber-50 text-amber-700 border-amber-200'];
        } else {
            return ['nombre' => 'Absoluto', 'class' => 'bg-rose-50 text-rose-700 border-rose-200'];
        }
    }
}

/**
 * LÓGICA DE CONTEXTO (Admin vs Club vs Otros)
 */
$is_admin = ($_SESSION['id_rol'] == 1);
$my_club_id = $_SESSION['club'] ?? 0;
$my_role_id = $_SESSION['id_rol'];

// Definir el Club Objetivo (Prioridad: GET > Session)
$target_club_id = (isset($_GET['club_id']) && $is_admin) ? intval($_GET['club_id']) : intval($my_club_id);

// Determinar Modo de Visualización
// 1. Modo Club (Tiene club o Admin seleccionó uno)
// 2. Modo Rol (No tiene club y no es Admin seleccionando)
$view_mode = ($target_club_id > 0) ? 'CLUB' : 'ROLE';

/**
 * OBTENCIÓN DE DATOS SEGÚN MODO
 */
$total_staff = 0;
$total_athletes = 0;
$avg_age = 0;
$total_licencias = 0;
$club_data = null;
$staff_list = [];
$nadadoras_list = [];

if ($view_mode == 'CLUB') {
    $query_club = "SELECT c.*, f.nombre as fed_nombre, f.nombre_corto as fed_corto 
                   FROM clubes c 
                   LEFT JOIN federaciones f ON c.federacion = f.id 
                   WHERE c.id = '$target_club_id'";
    $run_club = mysqli_query($connection, $query_club);
    $club_data = mysqli_fetch_array($run_club);
    
    $title = "Mi Equipo";
    $club_nombre = $club_data['nombre'] ?? 'Club Desconocido';
    $subtitle = "Gestión, plantilla técnica y censo de " . $club_nombre;
    $logo = $club_data['logo'] ?? null;

    // KPI 1: Staff
    $q_count_users = "SELECT COUNT(*) as total FROM usuarios WHERE club = '$target_club_id' AND activo = 1";
    $total_staff = intval(mysqli_fetch_assoc(mysqli_query($connection, $q_count_users))['total'] ?? 0);

    // KPI 2: Atletas
    $q_count_nad = "SELECT COUNT(*) as total FROM nadadoras WHERE club = '$target_club_id' AND activo = 1";
    $total_athletes = intval(mysqli_fetch_assoc(mysqli_query($connection, $q_count_nad))['total'] ?? 0);

    // Año de referencia de temporada deportiva (RFEN: temporada finaliza al año siguiente si mes >= septiembre)
    $cur_m = intval(date('n'));
    $cur_y = intval(date('Y'));
    $season_year = ($cur_m >= 9) ? ($cur_y + 1) : $cur_y;

    // KPI 3: Media Edad
    $q_avg_age = "SELECT AVG($season_year - año_nacimiento) as media FROM nadadoras WHERE club = '$target_club_id' AND activo = 1 AND año_nacimiento > 1900";
    $res_avg = mysqli_query($connection, $q_avg_age);
    $avg_age = $res_avg ? round(floatval(mysqli_fetch_assoc($res_avg)['media'] ?? 0), 1) : 0;

    // KPI 4: Licencias
    $q_count_lic = "SELECT COUNT(*) as total FROM nadadoras WHERE club = '$target_club_id' AND activo = 1 AND licencia IS NOT NULL AND TRIM(licencia) != ''";
    $total_licencias = intval(mysqli_fetch_assoc(mysqli_query($connection, $q_count_lic))['total'] ?? 0);

    // Listado Staff
    $q_staff = "SELECT u.*, r.nombre as rol_nombre 
                FROM usuarios u 
                JOIN roles r ON u.id_rol = r.id 
                WHERE u.club = '$target_club_id' AND u.activo = 1 
                ORDER BY r.id ASC, u.username ASC";
    $r_staff = mysqli_query($connection, $q_staff);
    while ($row = mysqli_fetch_assoc($r_staff)) {
        $staff_list[] = $row;
    }

    // Listado Nadadoras
    $q_nad = "SELECT n.*, ($season_year - n.año_nacimiento) as edad 
              FROM nadadoras n 
              WHERE n.club = '$target_club_id' AND n.activo = 1 
              ORDER BY n.apellidos ASC, n.nombre ASC";
    $r_nad = mysqli_query($connection, $q_nad);
    while ($row = mysqli_fetch_assoc($r_nad)) {
        $nadadoras_list[] = $row;
    }
} else {
    // Modo ROL (Mismo nivel que yo)
    $title = "Compañeros";
    $subtitle = "Directorio de usuarios con rango " . ($_SESSION['rol'] ?? 'Invitado');
    $logo = null;

    // KPI 1: Misma categoría
    $q_count_users = "SELECT COUNT(*) as total FROM usuarios WHERE id_rol = '$my_role_id' AND activo = 1";
    $total_staff = intval(mysqli_fetch_assoc(mysqli_query($connection, $q_count_users))['total'] ?? 0);

    // Listado de pares
    $q_staff = "SELECT u.*, r.nombre as rol_nombre 
                FROM usuarios u 
                JOIN roles r ON u.id_rol = r.id 
                WHERE u.id_rol = '$my_role_id' AND u.activo = 1 
                ORDER BY u.username ASC";
    $r_staff = mysqli_query($connection, $q_staff);
    while ($row = mysqli_fetch_assoc($r_staff)) {
        $staff_list[] = $row;
    }
}

// Resolver ruta de logo de club con comprobación de archivo
$final_logo = null;
if (!empty($logo)) {
    $candidate = $logo;
    if (!file_exists($candidate) && !str_starts_with($candidate, 'images/clubes/')) {
        $candidate = 'images/clubes/' . $logo;
    }
    if (file_exists($candidate)) {
        $final_logo = $candidate;
    }
}
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-[#f8fafc]">
    
    <?php include('includes/topbar.php'); ?>

    <!-- Contenido de la Página -->
    <div class="px-3 py-4 sm:p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend space-y-6 sm:space-y-8">
        
        <!-- Header de Sección -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pb-2 border-b border-slate-200/80">
            <div class="flex items-start sm:items-center gap-3.5 sm:gap-4 min-w-0">
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-br from-blue-600 via-sky-500 to-cyan-500 text-white flex items-center justify-center shadow-lg shadow-blue-500/20 shrink-0">
                    <i class="fas fa-shield-halved text-xl sm:text-2xl"></i>
                </div>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-2xl sm:text-3xl md:text-4xl font-black text-slate-800 tracking-tight">
                            <?php echo htmlspecialchars($title); ?>
                        </h1>
                        <?php if ($view_mode == 'CLUB' && !empty($club_data['codigo'])): ?>
                            <span class="px-2.5 py-0.5 rounded-lg bg-blue-50 text-blue-700 border border-blue-200 text-[11px] font-black tracking-wider uppercase tabular-nums">
                                COD: <?php echo htmlspecialchars($club_data['codigo']); ?>
                            </span>
                        <?php endif; ?>
                        <?php if ($view_mode == 'CLUB' && !empty($club_data['fed_corto'])): ?>
                            <span class="px-2.5 py-0.5 rounded-lg bg-slate-100 text-slate-600 border border-slate-200 text-[11px] font-bold uppercase">
                                <?php echo htmlspecialchars($club_data['fed_corto']); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-500 font-medium mt-1 truncate">
                        <?php echo htmlspecialchars($subtitle); ?>
                    </p>
                </div>
            </div>
            
            <div class="flex flex-wrap items-center gap-3 shrink-0">
                <!-- Selector para Administrador -->
                <?php if($is_admin): ?>
                <div class="w-full sm:w-72">
                    <label class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5 block">
                        <i class="fas fa-building text-slate-400 mr-1"></i> Club Objetivo (Vista Admin)
                    </label>
                    <form action="mi_equipo.php" method="GET" class="relative">
                        <select name="club_id" onchange="this.form.submit()" 
                                class="w-full pl-3.5 pr-8 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all outline-none cursor-pointer">
                            <option value="0">--- Seleccionar Club para Inspeccionar ---</option>
                            <?php 
                            $q_clubs_all = "SELECT id, nombre, codigo FROM clubes WHERE activo = 1 ORDER BY nombre ASC";
                            $r_clubs_all = mysqli_query($connection, $q_clubs_all);
                            while($c_opt = mysqli_fetch_assoc($r_clubs_all)):
                            ?>
                                <option value="<?php echo $c_opt['id']; ?>" <?php echo ($target_club_id == $c_opt['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($c_opt['nombre']) . (!empty($c_opt['codigo']) ? ' (' . $c_opt['codigo'] . ')' : ''); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </form>
                </div>
                <?php endif; ?>

                <!-- Logo / Escudo del Club -->
                <?php if($view_mode == 'CLUB'): ?>
                    <div class="flex items-center gap-3 bg-white p-2 sm:p-2.5 rounded-2xl shadow-sm border border-slate-200/80">
                        <?php if($final_logo): ?>
                            <img src="<?php echo htmlspecialchars($final_logo); ?>" alt="Logo Club" class="h-12 sm:h-14 w-auto max-w-[120px] object-contain">
                        <?php else: ?>
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 border border-slate-200 text-slate-600 font-black text-sm flex items-center justify-center uppercase tracking-wider shadow-inner">
                                <?php echo htmlspecialchars(get_team_initials($club_nombre)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php include('includes/alertas_v4.php'); ?>

        <!-- KPIs Métricas -->
        <div class="grid grid-cols-2 <?php echo ($view_mode == 'CLUB') ? 'lg:grid-cols-4' : 'lg:grid-cols-2'; ?> gap-3 sm:gap-5">
            <!-- Card 1: Staff -->
            <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200/80 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 relative overflow-hidden group">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-400 to-teal-500"></div>
                <div class="flex justify-between items-start mb-3 sm:mb-4">
                    <span class="text-[10px] sm:text-[11px] font-black uppercase text-slate-400 tracking-wider">
                        <?php echo ($view_mode == 'CLUB') ? 'Staff Técnico' : 'Compañeros Activos'; ?>
                    </span>
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm shadow-sm">
                        <i class="fas fa-user-tie"></i>
                    </div>
                </div>
                <h3 class="text-2xl sm:text-4xl font-black text-slate-800 leading-none tabular-nums" id="kpiStaffTotal">
                    <?php echo $total_staff; ?>
                </h3>
                <p class="text-[10px] sm:text-xs font-bold text-slate-400 mt-2 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                    Usuarios con credenciales
                </p>
            </div>

            <?php if($view_mode == 'CLUB'): ?>
            <!-- Card 2: Nadadoras -->
            <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200/80 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 relative overflow-hidden group">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 to-indigo-600"></div>
                <div class="flex justify-between items-start mb-3 sm:mb-4">
                    <span class="text-[10px] sm:text-[11px] font-black uppercase text-slate-400 tracking-wider">
                        Censo Nadadoras
                    </span>
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm shadow-sm">
                        <i class="fas fa-person-swimming"></i>
                    </div>
                </div>
                <h3 class="text-2xl sm:text-4xl font-black text-slate-800 leading-none tabular-nums" id="kpiSwimmerTotal">
                    <?php echo $total_athletes; ?>
                </h3>
                <p class="text-[10px] sm:text-xs font-bold text-slate-400 mt-2 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 inline-block"></span>
                    Atletas en plantilla activa
                </p>
            </div>

            <!-- Card 3: Edad Media -->
            <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200/80 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 relative overflow-hidden group">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-500 to-fuchsia-600"></div>
                <div class="flex justify-between items-start mb-3 sm:mb-4">
                    <span class="text-[10px] sm:text-[11px] font-black uppercase text-slate-400 tracking-wider">
                        Edad Promedio
                    </span>
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-sm shadow-sm">
                        <i class="fas fa-cake-candles"></i>
                    </div>
                </div>
                <h3 class="text-2xl sm:text-4xl font-black text-slate-800 leading-none tabular-nums">
                    <?php echo $avg_age; ?> <span class="text-xs sm:text-sm font-bold text-slate-400">años</span>
                </h3>
                <p class="text-[10px] sm:text-xs font-bold text-slate-400 mt-2 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-purple-500 inline-block"></span>
                    Promedio demográfico
                </p>
            </div>

            <!-- Card 4: Licencias -->
            <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200/80 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 relative overflow-hidden group">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-400 to-orange-500"></div>
                <div class="flex justify-between items-start mb-3 sm:mb-4">
                    <span class="text-[10px] sm:text-[11px] font-black uppercase text-slate-400 tracking-wider">
                        Con Licencia
                    </span>
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm shadow-sm">
                        <i class="fas fa-id-card-clip"></i>
                    </div>
                </div>
                <h3 class="text-2xl sm:text-4xl font-black text-slate-800 leading-none tabular-nums">
                    <?php echo $total_licencias; ?> 
                    <span class="text-xs sm:text-sm font-bold text-slate-400">/ <?php echo $total_athletes; ?></span>
                </h3>
                <p class="text-[10px] sm:text-xs font-bold text-slate-400 mt-2 flex items-center gap-1.5">
                    <?php 
                    $pct = ($total_athletes > 0) ? round(($total_licencias / $total_athletes) * 100) : 0;
                    ?>
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 inline-block"></span>
                    <span class="tabular-nums font-black text-amber-600"><?php echo $pct; ?>%</span> de cobertura oficial
                </p>
            </div>
            <?php else: ?>
            <!-- Card informativa para otros roles -->
            <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200/80 flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
                    <i class="fas fa-circle-info"></i>
                </div>
                <div>
                    <h4 class="text-xs sm:text-sm font-bold text-slate-800">Modo Directorio por Rol</h4>
                    <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">
                        Estás visualizando a los miembros asignados a tu mismo rango operativo. Para acceder al censo deportivo, inicia sesión como club o selecciona un club como Administrador.
                    </p>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- GRID DE CONTENIDO PRINCIPAL -->
        <div class="grid grid-cols-1 <?php echo ($view_mode == 'CLUB') ? 'lg:grid-cols-12' : ''; ?> gap-6 sm:gap-8 items-start">
            
            <!-- SECCIÓN: STAFF / MIEMBROS -->
            <div class="<?php echo ($view_mode == 'CLUB') ? 'lg:col-span-5' : 'w-full'; ?> space-y-4">
                
                <!-- Encabezado de Staff y Buscador -->
                <div class="bg-white rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200/80 p-4 sm:p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-3.5">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm shadow-sm">
                                <i class="fas fa-users-gear"></i>
                            </span>
                            <h2 class="text-sm sm:text-base font-black text-slate-800 uppercase tracking-tight">
                                <?php echo ($view_mode == 'CLUB') ? 'Staff del Club' : 'Miembros del Rol'; ?>
                            </h2>
                        </div>
                        <span id="staffCountBadge" class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-emerald-100 text-emerald-800 tabular-nums">
                            <?php echo count($staff_list); ?> usuarios
                        </span>
                    </div>

                    <!-- Input Buscador de Staff -->
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                            <i class="fas fa-search text-xs"></i>
                        </span>
                        <input type="text" id="staffSearchInput" onkeyup="filterStaff()" 
                               placeholder="Filtrar staff por nombre, email o rol..." 
                               class="w-full pl-9 pr-3.5 py-2 bg-slate-50 border border-slate-200/80 rounded-xl text-xs font-bold text-slate-700 placeholder-slate-400 focus:bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all outline-none">
                    </div>
                </div>

                <!-- Tarjeta Contenedora de Listado Staff -->
                <div class="bg-white rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden">
                    
                    <!-- Vista Móvil de Staff (md:hidden) -->
                    <div class="md:hidden divide-y divide-slate-100" id="staffMobileContainer">
                        <?php if (empty($staff_list)): ?>
                            <div class="p-8 text-center text-slate-400 text-xs font-bold">No se encontraron usuarios vinculados.</div>
                        <?php else: ?>
                            <?php foreach ($staff_list as $st): 
                                $st_foto = $st['foto'] ?? null;
                                $has_foto = (!empty($st_foto) && file_exists($st_foto));
                                $initials = get_team_initials($st['username']);
                                $search_str = htmlspecialchars(strtolower($st['username'] . ' ' . $st['email'] . ' ' . ($st['telefono'] ?? '') . ' ' . ($st['rol_nombre'] ?? '')));
                            ?>
                            <div class="staff-mobile-card p-3.5 hover:bg-slate-50/60 transition-colors" data-search="<?php echo $search_str; ?>">
                                <div class="flex items-start gap-3">
                                    <?php if ($has_foto): ?>
                                        <img src="<?php echo htmlspecialchars($st_foto); ?>" alt="Avatar" class="w-10 h-10 rounded-full object-cover ring-2 ring-emerald-500/20 shadow-sm shrink-0">
                                    <?php else: ?>
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-600 via-sky-500 to-cyan-400 text-white font-black text-xs flex items-center justify-center shadow-sm shrink-0 ring-2 ring-blue-500/10">
                                            <?php echo $initials; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between gap-2">
                                            <h3 class="text-xs font-black text-slate-800 truncate"><?php echo htmlspecialchars($st['username']); ?></h3>
                                            <span class="px-2 py-0.5 bg-slate-100 text-slate-600 text-[9px] font-black rounded-md uppercase tracking-wider shrink-0">
                                                <?php echo htmlspecialchars($st['rol_nombre']); ?>
                                            </span>
                                        </div>

                                        <div class="mt-1 space-y-0.5 text-[11px]">
                                            <a href="mailto:<?php echo htmlspecialchars($st['email']); ?>" class="text-slate-500 hover:text-blue-600 flex items-center gap-1.5 truncate">
                                                <i class="fas fa-envelope text-[10px] text-slate-400"></i>
                                                <span class="truncate"><?php echo htmlspecialchars($st['email']); ?></span>
                                            </a>
                                            <?php if (!empty($st['telefono'])): ?>
                                                <a href="tel:<?php echo htmlspecialchars($st['telefono']); ?>" class="text-slate-600 font-bold hover:text-emerald-600 flex items-center gap-1.5 tabular-nums">
                                                    <i class="fas fa-phone text-[10px] text-slate-400"></i>
                                                    <?php echo htmlspecialchars($st['telefono']); ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Vista Escritorio de Staff (hidden md:block) -->
                    <div class="hidden md:block overflow-x-auto no-scrollbar">
                        <table class="w-full text-left border-collapse" id="staffTable">
                            <thead>
                                <tr class="bg-slate-50/70 border-b border-slate-100 text-slate-400 text-[10px] font-black uppercase tracking-wider">
                                    <th class="px-5 py-3.5">Usuario</th>
                                    <th class="px-5 py-3.5">Rol</th>
                                    <th class="px-5 py-3.5">Contacto</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs">
                                <?php if (empty($staff_list)): ?>
                                    <tr>
                                        <td colspan="3" class="px-5 py-8 text-center text-slate-400 font-bold italic">No se encontraron miembros.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($staff_list as $st): 
                                        $st_foto = $st['foto'] ?? null;
                                        $has_foto = (!empty($st_foto) && file_exists($st_foto));
                                        $initials = get_team_initials($st['username']);
                                        $search_str = htmlspecialchars(strtolower($st['username'] . ' ' . $st['email'] . ' ' . ($st['telefono'] ?? '') . ' ' . ($st['rol_nombre'] ?? '')));
                                    ?>
                                    <tr class="hover:bg-slate-50/70 transition-colors group" data-search="<?php echo $search_str; ?>">
                                        <td class="px-5 py-3.5">
                                            <div class="flex items-center gap-3">
                                                <?php if ($has_foto): ?>
                                                    <img src="<?php echo htmlspecialchars($st_foto); ?>" alt="Avatar" class="w-9 h-9 rounded-full object-cover ring-2 ring-emerald-500/20 shadow-sm shrink-0">
                                                <?php else: ?>
                                                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-600 via-sky-500 to-cyan-400 text-white font-black text-xs flex items-center justify-center shadow-sm shrink-0 ring-2 ring-blue-500/10">
                                                        <?php echo $initials; ?>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="min-w-0">
                                                    <span class="font-bold text-slate-800 group-hover:text-blue-600 transition-colors block truncate">
                                                        <?php echo htmlspecialchars($st['username']); ?>
                                                    </span>
                                                    <span class="text-[10px] text-slate-400 block tabular-nums">
                                                        ID #<?php echo $st['id']; ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-3.5">
                                            <span class="px-2.5 py-1 bg-slate-100 text-slate-700 text-[10px] font-black rounded-lg uppercase tracking-wider">
                                                <?php echo htmlspecialchars($st['rol_nombre']); ?>
                                            </span>
                                        </td>
                                        <td class="px-5 py-3.5">
                                            <div class="flex flex-col space-y-0.5">
                                                <a href="mailto:<?php echo htmlspecialchars($st['email']); ?>" class="text-slate-500 hover:text-blue-600 font-medium truncate flex items-center gap-1.5" title="<?php echo htmlspecialchars($st['email']); ?>">
                                                    <i class="fas fa-envelope text-[10px] text-slate-400"></i>
                                                    <span class="truncate max-w-[150px]"><?php echo htmlspecialchars($st['email']); ?></span>
                                                </a>
                                                <?php if (!empty($st['telefono'])): ?>
                                                    <a href="tel:<?php echo htmlspecialchars($st['telefono']); ?>" class="text-slate-600 hover:text-emerald-600 font-bold tabular-nums flex items-center gap-1.5">
                                                        <i class="fas fa-phone text-[10px] text-slate-400"></i>
                                                        <?php echo htmlspecialchars($st['telefono']); ?>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

            <?php if($view_mode == 'CLUB'): ?>
            <!-- SECCIÓN: CENSO DE NADADORAS -->
            <div class="lg:col-span-7 space-y-4">
                
                <!-- Encabezado de Nadadoras y Filtros -->
                <div class="bg-white rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200/80 p-4 sm:p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-3.5">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm shadow-sm">
                                <i class="fas fa-person-swimming"></i>
                            </span>
                            <h2 class="text-sm sm:text-base font-black text-slate-800 uppercase tracking-tight">
                                Censo de Atletas
                            </h2>
                        </div>
                        <div class="flex items-center gap-2">
                            <span id="swimmerCountBadge" class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-blue-100 text-blue-800 tabular-nums">
                                <?php echo count($nadadoras_list); ?> deportistas
                            </span>
                            <?php if ($is_admin || ($my_role_id == 5)): ?>
                                <a href="nadadoras.php" class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-[11px] font-black uppercase tracking-wider shadow-sm active:scale-[0.98] transition-all flex items-center gap-1.5">
                                    <i class="fas fa-plus text-[10px]"></i> Alta
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Buscador y Chips de Filtro -->
                    <div class="flex flex-col sm:flex-row gap-2.5">
                        <div class="relative flex-1">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                                <i class="fas fa-search text-xs"></i>
                            </span>
                            <input type="text" id="swimmerSearchInput" onkeyup="filterSwimmers()" 
                                   placeholder="Buscar nadadora por apellidos, nombre, año o licencia..." 
                                   class="w-full pl-9 pr-3.5 py-2 bg-slate-50 border border-slate-200/80 rounded-xl text-xs font-bold text-slate-700 placeholder-slate-400 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all outline-none">
                        </div>

                        <!-- Filtro por Estado de Licencia -->
                        <div class="flex items-center gap-1.5 shrink-0 bg-slate-100 p-1 rounded-xl">
                            <button type="button" onclick="setLicenseFilter('all')" id="btnFilterAll" 
                                    class="px-2.5 py-1 text-[10px] font-black rounded-lg uppercase tracking-wider transition-all bg-white text-slate-800 shadow-sm">
                                Todas
                            </button>
                            <button type="button" onclick="setLicenseFilter('licensed')" id="btnFilterLicensed" 
                                    class="px-2.5 py-1 text-[10px] font-black rounded-lg uppercase tracking-wider text-slate-500 hover:text-slate-800 transition-all">
                                Con Licencia
                            </button>
                            <button type="button" onclick="setLicenseFilter('unlicensed')" id="btnFilterUnlicensed" 
                                    class="px-2.5 py-1 text-[10px] font-black rounded-lg uppercase tracking-wider text-slate-500 hover:text-slate-800 transition-all">
                                Sin Licencia
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta Contenedora de Listado Nadadoras con Scroll Suave -->
                <div class="bg-white rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden">
                    
                    <!-- Vista Móvil de Nadadoras (md:hidden) -->
                    <div class="md:hidden divide-y divide-slate-100 max-h-[640px] overflow-y-auto no-scrollbar" id="swimmersMobileContainer">
                        <?php if (empty($nadadoras_list)): ?>
                            <div class="p-8 text-center text-slate-400 text-xs font-bold">No hay nadadoras registradas en este club.</div>
                        <?php else: ?>
                            <?php foreach ($nadadoras_list as $nad): 
                                $cat = get_swimmer_category_badge($nad['año_nacimiento']);
                                $has_lic = (!empty($nad['licencia']) && trim($nad['licencia']) !== '');
                                $search_str = htmlspecialchars(strtolower($nad['apellidos'] . ' ' . $nad['nombre'] . ' ' . ($nad['año_nacimiento'] ?? '') . ' ' . ($nad['licencia'] ?? '') . ' ' . $cat['nombre']));
                                $lic_state = $has_lic ? 'licensed' : 'unlicensed';
                            ?>
                            <div class="swimmer-mobile-card p-3.5 hover:bg-slate-50/60 transition-colors" 
                                 data-search="<?php echo $search_str; ?>" 
                                 data-license="<?php echo $lic_state; ?>">
                                <div class="flex items-start justify-between gap-2.5">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-6 h-6 rounded-md bg-blue-50 text-blue-600 font-black text-[10px] flex items-center justify-center shrink-0">
                                                <?php echo mb_substr($nad['nombre'] ?? 'N', 0, 1); ?>
                                            </span>
                                            <h3 class="text-xs font-black text-slate-800 uppercase leading-snug truncate">
                                                <?php echo htmlspecialchars($nad['apellidos']); ?>, <?php echo htmlspecialchars($nad['nombre']); ?>
                                            </h3>
                                        </div>

                                        <div class="flex flex-wrap items-center gap-1.5 mt-2">
                                            <!-- Categoría -->
                                            <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider border <?php echo $cat['class']; ?>">
                                                <?php echo $cat['nombre']; ?>
                                            </span>

                                            <!-- Año y Edad -->
                                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[10px] font-bold tabular-nums">
                                                <i class="fas fa-cake-candles text-[8px] mr-1 text-slate-400"></i>
                                                <?php echo $nad['año_nacimiento'] ?: 'S/D'; ?> 
                                                <?php if (!empty($nad['edad']) && $nad['año_nacimiento'] > 1900): ?>
                                                    (<?php echo $nad['edad']; ?>a)
                                                <?php endif; ?>
                                            </span>

                                            <!-- Licencia -->
                                            <?php if ($has_lic): ?>
                                                <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-black tabular-nums">
                                                    Lic: <?php echo htmlspecialchars($nad['licencia']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="px-2 py-0.5 rounded-md bg-slate-50 text-slate-400 text-[9px] font-bold">
                                                    Sin Licencia
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <?php if ($is_admin || ($my_role_id == 5)): ?>
                                    <div class="shrink-0 pt-0.5">
                                        <form action="nadadoras_edit.php" method="POST">
                                            <input type="hidden" name="edit_id" value="<?php echo $nad['id']; ?>">
                                            <button type="submit" name="edit_btn" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-blue-50 text-slate-500 hover:text-blue-600 active:scale-[0.98] transition-all flex items-center justify-center shadow-xs">
                                                <i class="fas fa-pen-to-square text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Vista Escritorio de Nadadoras (hidden md:block) con Altura Cómoda -->
                    <div class="hidden md:block max-h-[640px] overflow-y-auto no-scrollbar">
                        <table class="w-full text-left border-collapse" id="swimmersTable">
                            <thead class="sticky top-0 bg-white z-10 shadow-xs">
                                <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-400 text-[10px] font-black uppercase tracking-wider">
                                    <th class="px-5 py-3.5">Deportista</th>
                                    <th class="px-5 py-3.5">Categoría</th>
                                    <th class="px-5 py-3.5">Año / Edad</th>
                                    <th class="px-5 py-3.5">Licencia</th>
                                    <?php if ($is_admin || ($my_role_id == 5)): ?>
                                        <th class="px-4 py-3.5 text-right">Acción</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs">
                                <?php if (empty($nadadoras_list)): ?>
                                    <tr>
                                        <td colspan="5" class="px-5 py-8 text-center text-slate-400 font-bold italic">No hay nadadoras registradas en este club.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($nadadoras_list as $nad): 
                                        $cat = get_swimmer_category_badge($nad['año_nacimiento']);
                                        $has_lic = (!empty($nad['licencia']) && trim($nad['licencia']) !== '');
                                        $search_str = htmlspecialchars(strtolower($nad['apellidos'] . ' ' . $nad['nombre'] . ' ' . ($nad['año_nacimiento'] ?? '') . ' ' . ($nad['licencia'] ?? '') . ' ' . $cat['nombre']));
                                        $lic_state = $has_lic ? 'licensed' : 'unlicensed';
                                    ?>
                                    <tr class="hover:bg-blue-50/20 transition-colors group" 
                                        data-search="<?php echo $search_str; ?>" 
                                        data-license="<?php echo $lic_state; ?>">
                                        <td class="px-5 py-3">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 font-black text-xs flex items-center justify-center shrink-0 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                                    <?php echo mb_substr($nad['nombre'] ?? 'N', 0, 1); ?>
                                                </div>
                                                <div class="min-w-0">
                                                    <span class="font-black text-slate-800 uppercase block truncate leading-tight">
                                                        <?php echo htmlspecialchars($nad['apellidos']); ?>
                                                    </span>
                                                    <span class="font-semibold text-slate-500 block truncate text-[11px]">
                                                        <?php echo htmlspecialchars($nad['nombre']); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-3">
                                            <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider border <?php echo $cat['class']; ?>">
                                                <?php echo $cat['nombre']; ?>
                                            </span>
                                        </td>
                                        <td class="px-5 py-3">
                                            <span class="text-slate-700 font-black tabular-nums">
                                                <?php echo $nad['año_nacimiento'] ?: 'S/D'; ?>
                                            </span>
                                            <?php if (!empty($nad['edad']) && $nad['año_nacimiento'] > 1900): ?>
                                                <span class="text-[10px] font-bold text-slate-400 tabular-nums ml-1">
                                                    (<?php echo $nad['edad']; ?> años)
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-5 py-3">
                                            <?php if ($has_lic): ?>
                                                <span class="px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-black tracking-wider tabular-nums">
                                                    <?php echo htmlspecialchars($nad['licencia']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-slate-300 font-bold text-[11px]">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <?php if ($is_admin || ($my_role_id == 5)): ?>
                                        <td class="px-4 py-3 text-right">
                                            <form action="nadadoras_edit.php" method="POST" class="inline-block">
                                                <input type="hidden" name="edit_id" value="<?php echo $nad['id']; ?>">
                                                <button type="submit" name="edit_btn" 
                                                        class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-blue-600 hover:text-white text-slate-400 active:scale-[0.98] transition-all inline-flex items-center justify-center shadow-xs" 
                                                        title="Editar Nadadora">
                                                    <i class="fas fa-pencil text-[10px]"></i>
                                                </button>
                                            </form>
                                        </td>
                                        <?php endif; ?>
                                    </tr>
                                    <?php endforeach; ?>
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

<script>
/**
 * Filtro reactivo para Staff del Club / Compañeros
 */
function filterStaff() {
    const input = document.getElementById('staffSearchInput');
    const term = input ? input.value.toLowerCase().trim() : '';
    
    // Tabla escritorio
    const tableRows = document.querySelectorAll('#staffTable tbody tr[data-search]');
    let visibleCount = 0;
    tableRows.forEach(row => {
        const searchVal = row.getAttribute('data-search') || '';
        if (searchVal.includes(term)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    // Cards móvil
    const mobileCards = document.querySelectorAll('#staffMobileContainer .staff-mobile-card');
    let mobileVisible = 0;
    mobileCards.forEach(card => {
        const searchVal = card.getAttribute('data-search') || '';
        if (searchVal.includes(term)) {
            card.style.display = '';
            mobileVisible++;
        } else {
            card.style.display = 'none';
        }
    });

    const badge = document.getElementById('staffCountBadge');
    if (badge) {
        badge.textContent = visibleCount + ' usuarios';
    }
}

/**
 * Filtro reactivo para Nadadoras (Texto + Chips de Licencia)
 */
let currentLicenseFilter = 'all';

function setLicenseFilter(type) {
    currentLicenseFilter = type;
    
    const btnAll = document.getElementById('btnFilterAll');
    const btnLic = document.getElementById('btnFilterLicensed');
    const btnUnlic = document.getElementById('btnFilterUnlicensed');

    const activeClasses = ['bg-white', 'text-slate-800', 'shadow-sm'];
    const inactiveClasses = ['text-slate-500', 'hover:text-slate-800'];

    [btnAll, btnLic, btnUnlic].forEach(btn => {
        if (!btn) return;
        btn.classList.remove('bg-white', 'text-slate-800', 'shadow-sm');
        btn.classList.add('text-slate-500', 'hover:text-slate-800');
    });

    let targetBtn = btnAll;
    if (type === 'licensed') targetBtn = btnLic;
    if (type === 'unlicensed') targetBtn = btnUnlic;

    if (targetBtn) {
        targetBtn.classList.remove('text-slate-500', 'hover:text-slate-800');
        targetBtn.classList.add('bg-white', 'text-slate-800', 'shadow-sm');
    }

    filterSwimmers();
}

function filterSwimmers() {
    const input = document.getElementById('swimmerSearchInput');
    const term = input ? input.value.toLowerCase().trim() : '';

    // Tabla escritorio
    const tableRows = document.querySelectorAll('#swimmersTable tbody tr[data-search]');
    let visibleCount = 0;
    tableRows.forEach(row => {
        const searchVal = row.getAttribute('data-search') || '';
        const licVal = row.getAttribute('data-license') || '';

        const matchesText = searchVal.includes(term);
        const matchesLic = (currentLicenseFilter === 'all') || (licVal === currentLicenseFilter);

        if (matchesText && matchesLic) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    // Cards móvil
    const mobileCards = document.querySelectorAll('#swimmersMobileContainer .swimmer-mobile-card');
    mobileCards.forEach(card => {
        const searchVal = card.getAttribute('data-search') || '';
        const licVal = card.getAttribute('data-license') || '';

        const matchesText = searchVal.includes(term);
        const matchesLic = (currentLicenseFilter === 'all') || (licVal === currentLicenseFilter);

        if (matchesText && matchesLic) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });

    const badge = document.getElementById('swimmerCountBadge');
    if (badge) {
        badge.textContent = visibleCount + ' deportistas';
    }
}
</script>

<?php 
include('includes/scripts.php');
include('includes/footer.php'); 
?>
