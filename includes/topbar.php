<?php
include('security.php');

// Obtener nombre e iniciales para avatar fallback
if (!isset($_SESSION['nombre_usuario']) && !empty($_SESSION['id_usario'])) {
    include_once('database/dbconfig.php');
    $u_id_top = intval($_SESSION['id_usario']);
    $q_u_top = mysqli_query($connection, "SELECT username, foto FROM usuarios WHERE id = $u_id_top");
    if ($q_u_top && $u_top_data = mysqli_fetch_assoc($q_u_top)) {
        $_SESSION['nombre_usuario'] = $u_top_data['username'];
        if (!empty($u_top_data['foto'])) $_SESSION['foto'] = $u_top_data['foto'];
    }
}

$user_display_name = !empty($_SESSION['nombre_usuario']) ? $_SESSION['nombre_usuario'] : ($_SESSION['username'] ?? 'Usuario');
$clean_name = (strpos($user_display_name, '@') !== false) ? explode('@', $user_display_name)[0] : $user_display_name;
$words = preg_split('/[\s._-]+/', trim($clean_name), -1, PREG_SPLIT_NO_EMPTY);
$top_initials = '';
if (count($words) >= 2) {
    $top_initials = mb_strtoupper(mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1));
} elseif (count($words) === 1) {
    $top_initials = mb_strtoupper(mb_substr($words[0], 0, 2));
} else {
    $top_initials = 'US';
}
?>
<!-- Topbar v4 (Prerelease) (Estética Refinada) -->
<header class="h-20 bg-white border-b border-slate-200 flex items-center justify-between px-4 md:px-6 sticky top-0 z-40 font-lexend">
    
    <div class="flex items-center gap-3 md:gap-4 flex-1 min-w-0">
        <!-- Navegación Mobile -->
        <?php if(isset($_SESSION['id_rol']) && in_array($_SESSION['id_rol'], ['1', '2', '3', '4', '5'])): ?>
            <button onclick="toggleV3Sidebar()" class="lg:hidden w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-2xl bg-slate-50 text-secondary border border-slate-100 hover:bg-slate-100 transition-all active:scale-95 group">
                <i class="fas fa-bars text-lg group-hover:scale-110 transition-transform"></i>
            </button>
        <?php endif; ?>

        <!-- Logo as Main Link (Hidden for Admin) -->
        <div class="flex items-center gap-3 md:gap-6 ml-1 min-w-0 flex-1">
            <?php if (defined('DEBUG_MODE') && DEBUG_MODE): ?>
                <div class="flex-shrink-0 flex items-center justify-center w-8 h-8 bg-red-50 text-red-600 rounded-xl border border-red-100 animate-pulse" title="Modo Depuración Activo">
                    <i class="fas fa-bug text-xs"></i>
                </div>
            <?php endif; ?>
            
            <?php if($_SESSION['id_rol'] != '1'): ?>
            <a href="index.php" class="flex items-center no-underline shrink-0">
                <img src="./images/logo_sincrm_removebg.png" alt="SINCRM" class="h-10 md:h-14 transition-transform hover:scale-105 block">
            </a>
            <?php endif; ?>
            
            <div class="flex flex-col border-l border-slate-200 pl-3 md:pl-4 h-10 justify-center min-w-0 flex-1">
                <span class="text-[8px] md:text-[9px] font-black uppercase text-slate-400 tracking-widest leading-none mb-1 truncate">
                    <?php 
                    if (!empty($_SESSION['nombre_competicion_usuario'])) {
                        echo 'Gestionando';
                    } elseif ($_SESSION['id_rol'] == '1') {
                        echo 'Sistema';
                    } else {
                        echo 'Gestionando';
                    }
                    ?>
                </span>
                <p class="text-[11px] md:text-sm font-black text-slate-800 truncate mb-0 leading-none">
                    <?php 
                    if(!empty($_SESSION['nombre_competicion_usuario'])) {
                        echo $_SESSION['id_competicion_usuario'] . ' - ' . $_SESSION['nombre_competicion_usuario'];
                    } elseif($_SESSION['id_rol'] == '1' && !empty($_SESSION['nombre_competicion_activa'])) {
                        echo $_SESSION['id_competicion_activa'] . ' - ' . $_SESSION['nombre_competicion_activa'];
                    } else {
                        echo '<span class="text-red-400 italic font-black uppercase tracking-tighter">Sin Competición</span>';
                    }
                    ?>
                </p>
            </div>
        </div>
    </div>

    <!-- User Section -->
    <div class="flex items-center gap-3 ml-2">
        <div class="relative group" id="userMenuContainer">
            <button onclick="toggleUserMenu()" class="flex items-center gap-2 md:gap-3 px-2 md:px-3 py-2 rounded-2xl hover:bg-slate-50 transition-all border border-transparent hover:border-slate-100 tap-press">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-black text-primary leading-none mb-1"><?php echo htmlspecialchars($user_display_name); ?></p>
                    <p class="text-[10px] font-bold text-secondary opacity-70 uppercase tracking-widest"><?php echo htmlspecialchars($_SESSION['rol'] ?? ''); ?></p>
                </div>
                <div class="h-10 w-10 md:h-11 md:w-11 rounded-2xl flex-shrink-0 flex items-center justify-center text-white shadow-lg shadow-blue-500/15 border-2 border-white overflow-hidden transition-transform group-hover:scale-105">
                    <?php 
                    if(!empty($_SESSION['foto']) && file_exists($_SESSION['foto'])) {
                        echo '<img src="'.htmlspecialchars($_SESSION['foto']).'" alt="User" class="w-full h-full object-cover">';
                    } else {
                        echo '<div class="w-full h-full bg-gradient-to-tr from-blue-600 via-sky-500 to-cyan-400 flex items-center justify-center text-white font-black text-xs md:text-sm select-none tracking-wider">';
                        echo htmlspecialchars($top_initials);
                        echo '</div>';
                    }
                    ?>
                </div>
            </button>
            
            <!-- Dropdown Menú Usuario -->
            <div id="user-dropdown-v3" class="hidden absolute right-0 mt-3 w-64 bg-white rounded-[2rem] shadow-2xl border border-slate-100 p-3 z-50 transform origin-top-right transition-all">
                <div class="px-4 py-3 mb-2 border-b border-slate-50 sm:hidden">
                    <p class="text-sm font-black text-primary leading-none mb-1"><?php echo htmlspecialchars($user_display_name); ?></p>
                    <p class="text-[10px] font-bold text-secondary uppercase tracking-widest"><?php echo htmlspecialchars($_SESSION['rol'] ?? ''); ?></p>
                </div>
                
                <a href="perfil.php" class="flex items-center gap-3 px-5 py-3 rounded-2xl text-slate-600 hover:bg-slate-50 hover:text-secondary transition-all group">
                    <div class="w-8 h-8 rounded-xl bg-slate-50 flex items-center justify-center group-hover:bg-white transition-colors">
                        <i class="fas fa-user-circle text-lg"></i>
                    </div>
                    <span class="text-sm font-bold">Mi Perfil</span>
                </a>

                <?php if(!empty($_SESSION['id_juez_v3']) || $_SESSION['id_rol'] == 4): ?>
                <a href="mi_auditoria.php" class="flex items-center gap-3 px-5 py-3 rounded-2xl text-blue-600 hover:bg-blue-50 transition-all group">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 flex items-center justify-center group-hover:bg-blue-100 transition-colors">
                        <i class="fas fa-magnifying-glass-chart text-lg"></i>
                    </div>
                    <span class="text-sm font-bold">Mi Auditoría BIAS</span>
                </a>
                <?php endif; ?>

                <a href="log_usuario.php" class="flex items-center gap-3 px-5 py-3 rounded-2xl text-slate-600 hover:bg-slate-50 hover:text-secondary transition-all group">
                    <div class="w-8 h-8 rounded-xl bg-slate-50 flex items-center justify-center group-hover:bg-white transition-colors">
                        <i class="fas fa-history text-lg"></i>
                    </div>
                    <span class="text-sm font-bold">Mi Actividad</span>
                </a>

                <a href="help/index.html" class="flex items-center gap-3 px-5 py-3 rounded-2xl text-slate-600 hover:bg-slate-50 hover:text-secondary transition-all group">
                    <div class="w-8 h-8 rounded-xl bg-slate-50 flex items-center justify-center group-hover:bg-white transition-colors">
                        <i class="fas fa-question-circle text-lg"></i>
                    </div>
                    <span class="text-sm font-bold">Ayuda</span>
                </a>
                
                <?php if($_SESSION['id_rol'] == 1): ?>
                <a href="help_admin/index.php" class="flex items-center gap-3 px-5 py-3 rounded-2xl text-slate-600 hover:bg-slate-50 hover:text-secondary transition-all group">
                    <div class="w-8 h-8 rounded-xl bg-slate-50 flex items-center justify-center group-hover:bg-white transition-colors">
                        <i class="fas fa-book-open text-lg"></i>
                    </div>
                    <span class="text-sm font-bold">Ayuda Admin</span>
                </a>

                <a href="log.php" class="flex items-center gap-3 px-5 py-3 rounded-2xl text-slate-600 hover:bg-slate-50 hover:text-secondary transition-all group">
                    <div class="w-8 h-8 rounded-xl bg-slate-50 flex items-center justify-center group-hover:bg-white transition-colors">
                        <i class="fas fa-list-ul text-lg"></i>
                    </div>
                    <span class="text-sm font-bold">Log del Sistema</span>
                </a>

                <!-- Opción para restablecer avisos silenciados (Solo si hay alguno temporal) -->
                <?php
                include_once('database/dbconfig.php');
                $user_id_check = (int)($_SESSION['id_usuario'] ?? ($_SESSION['id_usario'] ?? 0));
                $q_has_sil = "SELECT COUNT(*) as total FROM avisos_silenciados WHERE id_usuario = '$user_id_check' AND silencio_hasta > NOW() AND es_descarte = 0";
                $res_has_sil = mysqli_query($connection, $q_has_sil);
                $has_sil = mysqli_fetch_assoc($res_has_sil)['total'] > 0;
                
                if($has_sil):
                ?>
                <button onclick="restablecerAvisos()" class="w-full flex items-center gap-3 px-5 py-3 rounded-2xl text-amber-600 hover:bg-amber-50 transition-all group">
                    <div class="w-8 h-8 rounded-xl bg-amber-50 flex items-center justify-center group-hover:bg-white transition-colors">
                        <i class="fas fa-bell-slash text-lg"></i>
                    </div>
                    <span class="text-sm font-bold">Recuperar Avisos</span>
                </button>
                <?php endif; ?>
                <?php endif; ?>
                
                <div class="h-px bg-slate-100 my-3 mx-4"></div>
                
                <form action="login_code.php" method="POST">
                    <button type="submit" name="logout_btn" class="w-full flex items-center gap-3 px-5 py-3 rounded-2xl text-error hover:bg-error/5 transition-all group">
                        <div class="w-8 h-8 rounded-xl bg-error/5 flex items-center justify-center group-hover:bg-white transition-colors">
                            <i class="fas fa-power-off text-lg"></i>
                        </div>
                        <span class="text-sm font-black uppercase tracking-wider">Cerrar Sesión</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
<script>
    function toggleUserMenu() {
        const menu = document.getElementById('user-dropdown-v3');
        menu.classList.toggle('hidden');
    }

    function restablecerAvisos() {
        if(!confirm('¿Quieres volver a mostrar todos los avisos silenciados?')) return;

        const formData = new FormData();
        formData.append('action', 'restablecer');

        fetch('avisos_code.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                location.reload();
            } else {
                alert('Error al restablecer: ' + data.message);
            }
        });
    }

    document.addEventListener('click', function(e) {
        const menu = document.getElementById('user-dropdown-v3');
        const container = document.getElementById('userMenuContainer');
        if (menu && container && !container.contains(e.target)) {
            menu.classList.add('hidden');
        }
    });
</script>
