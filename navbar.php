<?php
if(isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == '1'){
    $current_page = basename($_SERVER['PHP_SELF']);
    
    // Agrupación de páginas para estados activos
    $group_users = ['usuarios.php', 'roles.php', 'usertype.php', 'usuarios_edit.php', 'roles_edit.php'];
    $group_datos = ['competiciones.php', 'federaciones.php', 'clubes.php', 'nadadoras.php', 'categorias.php', 'jueces.php', 'figuras.php', 'clubes_edit.php', 'nadadoras_edit.php', 'figuras_edit.php', 'categorias_edit.php', 'competiciones_edit.php'];
    $group_operativa = ['fases.php', 'paneles_jueces.php', 'rutinas.php', 'sorteo_rutinas.php', 'inscripciones_figuras.php', 'sorteo_figuras.php', 'puntuaciones_lista_fases.php', 'fases_edit.php'];
?>
<aside id="sidebar" class="sidebar-transition fixed top-0 left-0 z-50 w-72 lg:w-20 h-screen bg-primary text-white flex flex-col shadow-2xl transform -translate-x-full lg:translate-x-0 overflow-x-hidden overflow-y-auto no-scrollbar border-r border-white/10 font-lexend">
    
    <!-- BRAND AREA -> HOME LINK (ADMIN) -->
    <a href="index.php" class="flex-shrink-0 h-24 flex items-center px-6 overflow-hidden bg-white/5 border-b border-white/5 hover:bg-white/10 transition-colors no-underline group">
        <div class="w-10 h-10 bg-white/10 rounded-xl flex-shrink-0 flex items-center justify-center shadow-lg shadow-black/10 group-hover:scale-110 transition-transform">
            <i class="fas fa-house-chimney text-white text-lg"></i>
        </div>
        <div class="sidebar-text hidden ml-4">
            <span class="text-xl font-black tracking-tighter text-white uppercase italic">Inicio</span>
            <p class="text-[8px] font-bold text-blue-100 uppercase tracking-widest leading-none opacity-80">Dashboard Principal</p>
        </div>
    </a>

    <!-- MAIN NAVIGATION -->
    <nav class="flex-1 px-3 space-y-6 mt-6">
        
        <!-- 1. Datos Maestros -->
        <div class="space-y-1">
            <div class="sidebar-text hidden px-4 text-[9px] font-black uppercase text-white/50 mb-2">Configuración</div>

            <button onclick="toggleV3Submenu('sub-datos')" class="w-full flex items-center gap-4 p-3.5 rounded-2xl transition-all duration-300 hover:bg-white/10 group <?php echo in_array($current_page, $group_datos) ? 'nav-item-active' : 'text-blue-50'; ?>" title="Datos Maestros">
                <i class="fas fa-database w-8 text-center text-lg group-hover:text-white transition-colors"></i>
                <span class="sidebar-text hidden font-bold text-sm flex-1 text-left whitespace-nowrap">Datos Maestros</span>
                <i class="fas fa-chevron-right text-[9px] sidebar-text hidden opacity-40 transition-transform duration-300" id="arrow-sub-datos"></i>
            </button>
            <div id="sub-datos" class="submenu-closed sidebar-text pl-10 mt-1 space-y-1">
                <a href="competiciones.php" class="flex items-center gap-3 px-4 py-2 text-sm font-medium rounded-xl transition-all <?php echo $current_page == 'competiciones.php' ? 'sub-nav-item-active' : 'text-blue-50/70'; ?> hover:text-white hover:bg-white/5 whitespace-nowrap">
                    <i class="fas fa-trophy w-4 text-center"></i> Competiciones
                </a>
                <a href="federaciones.php" class="flex items-center gap-3 px-4 py-2 text-sm font-medium rounded-xl transition-all <?php echo $current_page == 'federaciones.php' ? 'sub-nav-item-active' : 'text-blue-50/70'; ?> hover:text-white hover:bg-white/5 whitespace-nowrap">
                    <i class="fas fa-landmark w-4 text-center"></i> Federaciones
                </a>
                <a href="clubes.php" class="flex items-center gap-3 px-4 py-2 text-sm font-medium rounded-xl transition-all <?php echo $current_page == 'clubes.php' ? 'sub-nav-item-active' : 'text-blue-50/70'; ?> hover:text-white hover:bg-white/5 whitespace-nowrap">
                    <i class="fas fa-shield-halved w-4 text-center"></i> Clubes
                </a>
                <a href="nadadoras.php" class="flex items-center gap-3 px-4 py-2 text-sm font-medium rounded-xl transition-all <?php echo $current_page == 'nadadoras.php' ? 'sub-nav-item-active' : 'text-blue-50/70'; ?> hover:text-white hover:bg-white/5 whitespace-nowrap">
                    <i class="fas fa-person-swimming w-4 text-center"></i> Nadadoras
                </a>
                <a href="categorias.php" class="flex items-center gap-3 px-4 py-2 text-sm font-medium rounded-xl transition-all <?php echo $current_page == 'categorias.php' ? 'sub-nav-item-active' : 'text-blue-50/70'; ?> hover:text-white hover:bg-white/5 whitespace-nowrap">
                    <i class="fas fa-layer-group w-4 text-center"></i> Categorías
                </a>
                <a href="jueces.php" class="flex items-center gap-3 px-4 py-2 text-sm font-medium rounded-xl transition-all <?php echo $current_page == 'jueces.php' ? 'sub-nav-item-active' : 'text-blue-50/70'; ?> hover:text-white hover:bg-white/5 whitespace-nowrap">
                    <i class="fas fa-gavel w-4 text-center"></i> Jueces
                </a>
                <a href="figuras.php" class="flex items-center gap-3 px-4 py-2 text-sm font-medium rounded-xl transition-all <?php echo $current_page == 'figuras.php' ? 'sub-nav-item-active' : 'text-blue-50/70'; ?> hover:text-white hover:bg-white/5 whitespace-nowrap">
                    <i class="fas fa-shapes w-4 text-center"></i> Figuras
                </a>
            </div>
        </div>

        <!-- 2. Competición -->
        <div class="space-y-1">
            <div class="sidebar-text hidden px-4 text-[9px] font-black uppercase text-white/50 mb-2">Operativa</div>

            <button onclick="toggleV3Submenu('sub-comp')" class="w-full flex items-center gap-4 p-3.5 rounded-2xl transition-all duration-300 hover:bg-white/10 group <?php echo in_array($current_page, $group_operativa) ? 'nav-item-active' : 'text-blue-50'; ?>" title="Competición">
                <i class="fas fa-flag-checkered w-8 text-center text-lg group-hover:text-white transition-colors"></i>
                <span class="sidebar-text hidden font-bold text-sm flex-1 text-left whitespace-nowrap">Gestión Activa</span>
                <i class="fas fa-chevron-right text-[9px] sidebar-text hidden opacity-40 transition-transform duration-300" id="arrow-sub-comp"></i>
            </button>
            <div id="sub-comp" class="submenu-closed sidebar-text pl-10 mt-1 space-y-1">
                <a href="fases.php" class="flex items-center gap-3 px-4 py-2 text-sm font-medium rounded-xl transition-all <?php echo $current_page == 'fases.php' ? 'sub-nav-item-active' : 'text-blue-50/70'; ?> hover:text-white hover:bg-white/5 whitespace-nowrap">
                    <i class="fas fa-list-check w-4 text-center"></i> Fases
                </a>
                <a href="paneles_jueces.php" class="flex items-center gap-3 px-4 py-2 text-sm font-medium rounded-xl transition-all <?php echo $current_page == 'paneles_jueces.php' ? 'sub-nav-item-active' : 'text-blue-50/70'; ?> hover:text-white hover:bg-white/5 whitespace-nowrap">
                    <i class="fas fa-scale-balanced w-4 text-center"></i> Paneles
                </a>
                
                <?php if (@$_SESSION['competicion_figuras_usuario'] == 'no'): ?>
                    <a href="rutinas.php" class="flex items-center gap-3 px-4 py-2 text-sm font-medium rounded-xl transition-all <?php echo $current_page == 'rutinas.php' ? 'sub-nav-item-active' : 'text-blue-50/70'; ?> hover:text-white hover:bg-white/5 whitespace-nowrap">
                        <i class="fas fa-users-line w-4 text-center"></i> Rutinas
                    </a>
                    <a href="sorteo_rutinas.php" class="flex items-center gap-3 px-4 py-2 text-sm font-medium rounded-xl transition-all <?php echo $current_page == 'sorteo_rutinas.php' ? 'sub-nav-item-active' : 'text-blue-50/70'; ?> hover:text-white hover:bg-white/5 whitespace-nowrap">
                        <i class="fas fa-wand-magic-sparkles w-4 text-center"></i> Sorteo
                    </a>
                <?php elseif (@$_SESSION['competicion_figuras_usuario'] == 'si'): ?>
                    <a href="inscripciones_figuras.php" class="flex items-center gap-3 px-4 py-2 text-sm font-medium rounded-xl transition-all <?php echo $current_page == 'inscripciones_figuras.php' ? 'sub-nav-item-active' : 'text-blue-50/70'; ?> hover:text-white hover:bg-white/5 whitespace-nowrap">
                        <i class="fas fa-clipboard-list w-4 text-center"></i> Inscripciones
                    </a>
                    <a href="sorteo_figuras.php" class="flex items-center gap-3 px-4 py-2 text-sm font-medium rounded-xl transition-all <?php echo $current_page == 'sorteo_figuras.php' ? 'sub-nav-item-active' : 'text-blue-50/70'; ?> hover:text-white hover:bg-white/5 whitespace-nowrap">
                        <i class="fas fa-wand-magic-sparkles w-4 text-center"></i> Sorteo
                    </a>
                <?php endif; ?>
                
                <a href="puntuaciones_lista_fases.php" class="flex items-center gap-3 px-4 py-2 text-sm font-medium rounded-xl transition-all <?php echo $current_page == 'puntuaciones_lista_fases.php' ? 'sub-nav-item-active' : 'text-blue-50/70'; ?> hover:text-white hover:bg-white/5 whitespace-nowrap font-bold border-t border-white/5 mt-2 pt-2">
                    <i class="fas fa-calculator w-4 text-center"></i> Puntuaciones
                </a>
            </div>
        </div>

        <!-- 3. Informes -->
        <div class="space-y-1">
            <div class="sidebar-text hidden px-4 text-[9px] font-black uppercase text-white/50 mb-2">Documentación</div>
            <a href="informes_competicion.php" class="flex items-center gap-4 p-3.5 rounded-2xl transition-all duration-300 hover:bg-white/10 group <?php echo $current_page == 'informes_competicion.php' ? 'nav-item-active' : 'text-blue-50'; ?>" title="Informes">
                <i class="fas fa-file-pdf w-8 text-center text-lg group-hover:text-white transition-colors"></i>
                <span class="sidebar-text hidden font-bold text-sm">Informes</span>
            </a>
        </div>

        <!-- 4. Usuarios -->
        <div class="space-y-1">
            <div class="sidebar-text hidden px-4 text-[9px] font-black uppercase text-white/50 mb-2">Seguridad</div>
            <div class="relative">
                <button onclick="toggleV3Submenu('sub-users')" class="w-full flex items-center gap-4 p-3.5 rounded-2xl transition-all duration-300 hover:bg-white/10 group <?php echo in_array($current_page, $group_users) ? 'nav-item-active' : 'text-blue-50'; ?>" title="Usuarios">
                    <i class="fas fa-user-shield w-8 text-center text-lg group-hover:text-white transition-colors"></i>
                    <span class="sidebar-text hidden font-bold text-sm flex-1 text-left">Usuarios</span>
                    <i class="fas fa-chevron-right text-[9px] sidebar-text hidden opacity-40 transition-transform duration-300" id="arrow-sub-users"></i>
                </button>
                <div id="sub-users" class="submenu-closed sidebar-text pl-10 mt-1 space-y-1">
                    <a href="usuarios.php" class="flex items-center gap-3 px-4 py-2 text-sm font-medium rounded-xl transition-all <?php echo $current_page == 'usuarios.php' ? 'sub-nav-item-active' : 'text-blue-50/70'; ?> hover:text-white hover:bg-white/5 transition-colors">
                        <i class="fas fa-users-gear w-4 text-center"></i> Directorio
                    </a>
                    <a href="roles.php" class="flex items-center gap-3 px-4 py-2 text-sm font-medium rounded-xl transition-all <?php echo $current_page == 'roles.php' ? 'sub-nav-item-active' : 'text-blue-50/70'; ?> hover:text-white hover:bg-white/5 transition-colors">
                        <i class="fas fa-user-lock w-4 text-center"></i> Roles
                    </a>
                </div>
            </div>
        </div>

        <!-- 5. Mantenimiento -->
        <div class="space-y-1">
            <div class="sidebar-text hidden px-4 text-[9px] font-black uppercase text-white/50 mb-2">Sistema</div>
            
            <a href="mantenimiento.php" class="flex items-center gap-4 p-3.5 rounded-2xl transition-all duration-300 hover:bg-white/10 group <?php echo $current_page == 'mantenimiento.php' ? 'nav-item-active' : 'text-blue-50'; ?>" title="Estado">
                <i class="fas fa-chart-line w-8 text-center text-lg group-hover:text-white transition-colors"></i>
                <span class="sidebar-text hidden font-bold text-sm">Estado del Sistema</span>
            </a>

            <a href="configuracion_sistema.php" class="flex items-center gap-4 p-3.5 rounded-2xl transition-all duration-300 hover:bg-white/10 group <?php echo $current_page == 'configuracion_sistema.php' ? 'nav-item-active' : 'text-blue-50'; ?>" title="Configuración">
                <i class="fas fa-sliders w-8 text-center text-lg group-hover:text-white transition-colors"></i>
                <span class="sidebar-text hidden font-bold text-sm">Configuración Técnica</span>
            </a>
        </div>

    </nav>

    <!-- FOOTER / TOGGLE -->
    <div class="flex-shrink-0 p-4 bg-white/5 border-t border-white/10">
        <button onclick="toggleV3Sidebar()" class="w-full h-12 flex items-center justify-center rounded-xl bg-white/5 hover:bg-white/10 transition-all group overflow-hidden">
            <i class="fas fa-angles-right sidebar-transition text-blue-100 group-hover:text-white" id="v3-toggle-icon"></i>
            <span class="sidebar-text hidden ml-3 text-[10px] font-black uppercase tracking-widest text-blue-50">Compactar</span>
        </button>
    </div>
</aside>

<!-- Overlay global -->
<div id="sidebar-overlay" onclick="toggleV3Sidebar()" class="fixed inset-0 bg-black/40 backdrop-blur-[2px] z-40 hidden transition-all duration-500 opacity-0"></div>

<style>
    @media (min-width: 1024px) {
        main { margin-left: 80px !important; }
    }
</style>

<script>
    function toggleV3Submenu(id) {
        const sidebar = document.getElementById('sidebar');
        if (window.innerWidth >= 1024 && sidebar.classList.contains('lg:w-20')) {
            toggleV3Sidebar();
            setTimeout(() => actualToggle(id), 100);
        } else {
            actualToggle(id);
        }
    }

    function actualToggle(id) {
        const submenu = document.getElementById(id);
        const arrow = document.getElementById('arrow-' + id);
        if (submenu.classList.contains('submenu-closed')) {
            submenu.classList.replace('submenu-closed', 'submenu-open');
            if(arrow) arrow.classList.add('rotate-90');
        } else {
            submenu.classList.replace('submenu-open', 'submenu-closed');
            if(arrow) arrow.classList.remove('rotate-90');
        }
    }

    function toggleV3Sidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const icon = document.getElementById('v3-toggle-icon');
        const texts = document.querySelectorAll('.sidebar-text');
        
        if (window.innerWidth < 1024) {
            // LÓGICA MÓVIL
            const isHidden = sidebar.classList.contains('-translate-x-full');
            if (isHidden) {
                sidebar.classList.remove('-translate-x-full');
                texts.forEach(t => t.classList.remove('hidden')); 
                overlay.classList.remove('hidden');
                setTimeout(() => overlay.classList.add('opacity-100'), 10);
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.remove('opacity-100');
                setTimeout(() => overlay.classList.add('hidden'), 500);
            }
        } else {
            // LÓGICA ESCRITORIO (Ahora con Overlay)
            if (sidebar.classList.contains('lg:w-20')) {
                // Abrir
                sidebar.classList.replace('lg:w-20', 'lg:w-72');
                icon.classList.replace('fa-angles-right', 'fa-angles-left');
                texts.forEach(t => t.classList.remove('hidden'));
                overlay.classList.remove('hidden');
                setTimeout(() => overlay.classList.add('opacity-100'), 10);
            } else {
                // Cerrar
                sidebar.classList.replace('lg:w-72', 'lg:w-20');
                icon.classList.replace('fa-angles-left', 'fa-angles-right');
                texts.forEach(t => t.classList.add('hidden'));
                overlay.classList.remove('opacity-100');
                setTimeout(() => overlay.classList.add('hidden'), 500);
                
                document.querySelectorAll('.submenu-open').forEach(el => {
                    el.classList.replace('submenu-open', 'submenu-closed');
                    const a = document.getElementById('arrow-' + el.id);
                    if(a) a.classList.remove('rotate-90');
                });
            }
        }
    }

    document.addEventListener('click', function(event) {
        const sidebar = document.getElementById('sidebar');
        if (!sidebar) return;
        if (window.innerWidth >= 1024 && sidebar.classList.contains('lg:w-72')) {
            if (!sidebar.contains(event.target)) {
                toggleV3Sidebar();
            }
        }
    });
</script>
<?php
}
?>