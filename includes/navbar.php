<?php
// ROLES QUE VEN EL MENÚ COMPLETO (Admin, Secretario, Delegado)
$roles_admin = ['1', '2', '3'];

if(isset($_SESSION['id_rol']) && in_array($_SESSION['id_rol'], $roles_admin)){
    $current_page = basename($_SERVER['PHP_SELF']);
    
    // Agrupación de páginas para estados activos
    $group_users = ['usuarios.php', 'roles.php', 'paginas.php', 'usertype.php', 'usuarios_edit.php', 'roles_edit.php'];
    $group_datos = ['competiciones.php', 'federaciones.php', 'clubes.php', 'nadadoras.php', 'categorias.php', 'modalidades.php', 'jueces.php', 'figuras.php', 'reglas_competicion.php', 'clubes_edit.php', 'nadadoras_edit.php', 'figuras_edit.php', 'categorias_edit.php', 'modalidades_edit.php', 'competiciones_edit.php'];
    $group_operativa = ['fases.php', 'paneles_jueces.php', 'inscripciones.php', 'rutinas.php', 'sorteo.php', 'sorteo_rutinas.php', 'inscripciones_figuras.php', 'sorteo_figuras.php', 'puntuaciones_lista_fases.php', 'fases_edit.php'];
    $group_bias = ['analisis_jueces.php', 'analisis_juez_detalle.php', 'ranking_jueces.php', 'perfil_juez.php', 'mi_auditoria.php'];
    $group_mantenimiento = ['estado_sistema.php', 'configuracion_sistema.php', 'diagnostico.php'];
?>
<aside id="sidebar" class="sidebar-transition fixed top-0 left-0 z-50 w-72 lg:w-20 h-screen bg-white text-slate-700 flex flex-col shadow-xl transform -translate-x-full lg:translate-x-0 overflow-x-hidden overflow-y-auto no-scrollbar border-r border-slate-200/80 font-lexend">
    
    <!-- BRAND AREA -> HOME LINK (ADMIN) -->
    <a href="index.php" class="flex-shrink-0 h-20 flex items-center px-4 lg:px-5 overflow-hidden bg-slate-50/80 border-b border-slate-200/80 hover:bg-slate-100/80 transition-colors no-underline group relative" title="SINCRM Inicio">
        <div class="w-11 h-11 rounded-2xl bg-white p-1.5 flex-shrink-0 flex items-center justify-center shadow-xs border border-slate-200/90 group-hover:scale-105 group-hover:shadow-md transition-all">
            <img src="./img/logo_sincrm_icon.png" class="w-full h-full object-contain" alt="SINCRM">
        </div>
        <div class="sidebar-text hidden ml-3 min-w-0 flex-1">
            <div class="flex items-center gap-1.5 leading-none mb-1">
                <span class="text-base font-black tracking-tight text-slate-800">SINCRM</span>
                <span class="px-1.5 py-0.5 rounded-md bg-blue-50 border border-blue-200 text-blue-600 text-[9px] font-black">4</span>
            </div>
            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider leading-none truncate">
                <?php echo ($_SESSION['id_rol'] == '1') ? 'SuperAdmin' : 'Panel Técnico'; ?>
            </p>
        </div>
    </a>

    <!-- MAIN NAVIGATION -->
    <nav class="flex-1 px-3 space-y-4 mt-4">
        
        <!-- 1. Datos Maestros -->
        <div class="space-y-1">
            <div class="sidebar-text hidden px-3 text-[9px] font-black uppercase text-slate-400/90 mb-1.5 tracking-widest flex items-center gap-2">
                <span>Configuración</span>
                <div class="h-px flex-1 bg-slate-200/80"></div>
            </div>

            <button onclick="toggleV3Submenu('sub-datos')" class="w-full flex items-center gap-3 p-2 rounded-2xl transition-all duration-200 hover:bg-slate-100/80 group <?php echo in_array($current_page, $group_datos) ? 'nav-item-active' : 'text-slate-600 hover:text-slate-900'; ?>" title="Datos Maestros">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 <?php echo in_array($current_page, $group_datos) ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200/70 group-hover:text-slate-800'; ?> transition-all">
                    <i class="fas fa-database text-sm"></i>
                </div>
                <span class="sidebar-text hidden font-bold text-xs flex-1 text-left whitespace-nowrap">Datos Maestros</span>
                <i class="fas fa-chevron-right text-[9px] sidebar-text hidden text-slate-400 transition-transform duration-200 <?php echo in_array($current_page, $group_datos) ? 'rotate-90 text-blue-600' : ''; ?>" id="arrow-sub-datos"></i>
            </button>
            <div id="sub-datos" data-active="<?php echo in_array($current_page, $group_datos) ? 'true' : 'false'; ?>" class="<?php echo in_array($current_page, $group_datos) ? 'submenu-open' : 'submenu-closed'; ?> sidebar-text hidden ml-6 pl-3 my-1 space-y-1 border-l-2 border-slate-200/80">
                <a href="competiciones.php" class="flex items-center gap-2.5 px-3 py-1.5 text-xs font-semibold rounded-xl transition-all <?php echo $current_page == 'competiciones.php' ? 'sub-nav-item-active' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/70'; ?> whitespace-nowrap">
                    <i class="fas fa-trophy w-3.5 text-center text-[11px] opacity-70"></i> Competiciones
                </a>
                <a href="federaciones.php" class="flex items-center gap-2.5 px-3 py-1.5 text-xs font-semibold rounded-xl transition-all <?php echo $current_page == 'federaciones.php' ? 'sub-nav-item-active' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/70'; ?> whitespace-nowrap">
                    <i class="fas fa-landmark w-3.5 text-center text-[11px] opacity-70"></i> Federaciones
                </a>
                <a href="clubes.php" class="flex items-center gap-2.5 px-3 py-1.5 text-xs font-semibold rounded-xl transition-all <?php echo $current_page == 'clubes.php' ? 'sub-nav-item-active' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/70'; ?> whitespace-nowrap">
                    <i class="fas fa-shield-halved w-3.5 text-center text-[11px] opacity-70"></i> Clubes
                </a>
                <a href="nadadoras.php" class="flex items-center gap-2.5 px-3 py-1.5 text-xs font-semibold rounded-xl transition-all <?php echo $current_page == 'nadadoras.php' ? 'sub-nav-item-active' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/70'; ?> whitespace-nowrap">
                    <i class="fas fa-person-swimming w-3.5 text-center text-[11px] opacity-70"></i> Nadadoras
                </a>
                <a href="categorias.php" class="flex items-center gap-2.5 px-3 py-1.5 text-xs font-semibold rounded-xl transition-all <?php echo $current_page == 'categorias.php' ? 'sub-nav-item-active' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/70'; ?> whitespace-nowrap">
                    <i class="fas fa-layer-group w-3.5 text-center text-[11px] opacity-70"></i> Categorías
                </a>
                <a href="modalidades.php" class="flex items-center gap-2.5 px-3 py-1.5 text-xs font-semibold rounded-xl transition-all <?php echo $current_page == 'modalidades.php' ? 'sub-nav-item-active' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/70'; ?> whitespace-nowrap">
                    <i class="fas fa-water w-3.5 text-center text-[11px] opacity-70"></i> Modalidades
                </a>
                <a href="jueces.php" class="flex items-center gap-2.5 px-3 py-1.5 text-xs font-semibold rounded-xl transition-all <?php echo $current_page == 'jueces.php' ? 'sub-nav-item-active' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/70'; ?> whitespace-nowrap">
                    <i class="fas fa-gavel w-3.5 text-center text-[11px] opacity-70"></i> Jueces
                </a>
                <a href="figuras.php" class="flex items-center gap-2.5 px-3 py-1.5 text-xs font-semibold rounded-xl transition-all <?php echo $current_page == 'figuras.php' ? 'sub-nav-item-active' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/70'; ?> whitespace-nowrap">
                    <i class="fas fa-shapes w-3.5 text-center text-[11px] opacity-70"></i> Figuras
                </a>
                <a href="reglas_competicion.php" class="flex items-center gap-2.5 px-3 py-1.5 text-xs font-semibold rounded-xl transition-all <?php echo $current_page == 'reglas_competicion.php' ? 'sub-nav-item-active' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/70'; ?> whitespace-nowrap">
                    <i class="fas fa-stopwatch w-3.5 text-center text-[11px] opacity-70"></i> Reglamento
                </a>
            </div>
        </div>

        <!-- 2. Competición -->
        <div class="space-y-1">
            <div class="sidebar-text hidden px-3 text-[9px] font-black uppercase text-slate-400/90 mb-1.5 tracking-widest flex items-center gap-2">
                <span>Operativa</span>
                <div class="h-px flex-1 bg-slate-200/80"></div>
            </div>

            <button onclick="toggleV3Submenu('sub-comp')" class="w-full flex items-center gap-3 p-2 rounded-2xl transition-all duration-200 hover:bg-slate-100/80 group <?php echo in_array($current_page, $group_operativa) ? 'nav-item-active' : 'text-slate-600 hover:text-slate-900'; ?>" title="Competición">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 <?php echo in_array($current_page, $group_operativa) ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200/70 group-hover:text-slate-800'; ?> transition-all">
                    <i class="fas fa-flag-checkered text-sm"></i>
                </div>
                <span class="sidebar-text hidden font-bold text-xs flex-1 text-left whitespace-nowrap">Gestión Activa</span>
                <i class="fas fa-chevron-right text-[9px] sidebar-text hidden text-slate-400 transition-transform duration-200 <?php echo in_array($current_page, $group_operativa) ? 'rotate-90 text-blue-600' : ''; ?>" id="arrow-sub-comp"></i>
            </button>
            <div id="sub-comp" data-active="<?php echo in_array($current_page, $group_operativa) ? 'true' : 'false'; ?>" class="<?php echo in_array($current_page, $group_operativa) ? 'submenu-open' : 'submenu-closed'; ?> sidebar-text hidden ml-6 pl-3 my-1 space-y-1 border-l-2 border-slate-200/80">
                <a href="fases.php" class="flex items-center gap-2.5 px-3 py-1.5 text-xs font-semibold rounded-xl transition-all <?php echo $current_page == 'fases.php' ? 'sub-nav-item-active' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/70'; ?> whitespace-nowrap">
                    <i class="fas fa-list-check w-3.5 text-center text-[11px] opacity-70"></i> Fases
                </a>
                <a href="paneles_jueces.php" class="flex items-center gap-2.5 px-3 py-1.5 text-xs font-semibold rounded-xl transition-all <?php echo $current_page == 'paneles_jueces.php' ? 'sub-nav-item-active' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/70'; ?> whitespace-nowrap">
                    <i class="fas fa-scale-balanced w-3.5 text-center text-[11px] opacity-70"></i> Paneles
                </a>
                <a href="inscripciones.php" class="flex items-center gap-2.5 px-3 py-1.5 text-xs font-semibold rounded-xl transition-all <?php echo in_array($current_page, ['inscripciones.php', 'inscripciones_figuras.php']) ? 'sub-nav-item-active' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/70'; ?> whitespace-nowrap">
                    <i class="fas fa-clipboard-list w-3.5 text-center text-[11px] opacity-70"></i> Inscripciones
                </a>
                <a href="sorteo.php" class="flex items-center gap-2.5 px-3 py-1.5 text-xs font-semibold rounded-xl transition-all <?php echo in_array($current_page, ['sorteo.php', 'sorteo_figuras.php', 'sorteo_rutinas.php']) ? 'sub-nav-item-active' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/70'; ?> whitespace-nowrap">
                    <i class="fas fa-wand-magic-sparkles w-3.5 text-center text-[11px] opacity-70"></i> Sorteos
                </a>
                <a href="puntuaciones_lista_fases.php" class="flex items-center gap-2.5 px-3 py-1.5 text-xs font-semibold rounded-xl transition-all <?php echo $current_page == 'puntuaciones_lista_fases.php' ? 'sub-nav-item-active' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/70'; ?> whitespace-nowrap border-t border-slate-200/80 pt-2 mt-1">
                    <i class="fas fa-calculator w-3.5 text-center text-[11px] opacity-70"></i> Puntuaciones
                </a>
            </div>
        </div>

        <!-- 3. BIAS Analizer -->
        <div class="space-y-1">
            <div class="sidebar-text hidden px-3 text-[9px] font-black uppercase text-slate-400/90 mb-1.5 tracking-widest flex items-center gap-2">
                <span>Evaluación</span>
                <div class="h-px flex-1 bg-slate-200/80"></div>
            </div>

            <button onclick="toggleV3Submenu('sub-bias')" class="w-full flex items-center gap-3 p-2 rounded-2xl transition-all duration-200 hover:bg-slate-100/80 group <?php echo in_array($current_page, $group_bias) ? 'nav-item-active' : 'text-slate-600 hover:text-slate-900'; ?>" title="BIAS Analizer">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 <?php echo in_array($current_page, $group_bias) ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200/70 group-hover:text-slate-800'; ?> transition-all">
                    <i class="fas fa-magnifying-glass-chart text-sm"></i>
                </div>
                <span class="sidebar-text hidden font-bold text-xs flex-1 text-left whitespace-nowrap">BIAS Analizer</span>
                <i class="fas fa-chevron-right text-[9px] sidebar-text hidden text-slate-400 transition-transform duration-200 <?php echo in_array($current_page, $group_bias) ? 'rotate-90 text-blue-600' : ''; ?>" id="arrow-sub-bias"></i>
            </button>
            <div id="sub-bias" data-active="<?php echo in_array($current_page, $group_bias) ? 'true' : 'false'; ?>" class="<?php echo in_array($current_page, $group_bias) ? 'submenu-open' : 'submenu-closed'; ?> sidebar-text hidden ml-6 pl-3 my-1 space-y-1 border-l-2 border-slate-200/80">
                <a href="mi_auditoria.php" class="flex items-center gap-2.5 px-3 py-1.5 text-xs font-semibold rounded-xl transition-all <?php echo $current_page == 'mi_auditoria.php' ? 'sub-nav-item-active' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/70'; ?> whitespace-nowrap">
                    <i class="fas fa-user-check w-3.5 text-center text-[11px] opacity-70"></i> Mi Auditoría
                </a>
                <a href="ranking_jueces.php" class="flex items-center gap-2.5 px-3 py-1.5 text-xs font-semibold rounded-xl transition-all <?php echo $current_page == 'ranking_jueces.php' ? 'sub-nav-item-active' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/70'; ?> whitespace-nowrap">
                    <i class="fas fa-ranking-star w-3.5 text-center text-[11px] opacity-70"></i> Ranking Calidad
                </a>
                <a href="analisis_jueces.php" class="flex items-center gap-2.5 px-3 py-1.5 text-xs font-semibold rounded-xl transition-all <?php echo in_array($current_page, ['analisis_jueces.php', 'analisis_juez_detalle.php']) ? 'sub-nav-item-active' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/70'; ?> whitespace-nowrap">
                    <i class="fas fa-calendar-check w-3.5 text-center text-[11px] opacity-70"></i> Auditoría Evento
                </a>
            </div>
        </div>

        <!-- 4. Informes -->
        <div class="space-y-1">
            <div class="sidebar-text hidden px-3 text-[9px] font-black uppercase text-slate-400/90 mb-1.5 tracking-widest flex items-center gap-2">
                <span>Documentos</span>
                <div class="h-px flex-1 bg-slate-200/80"></div>
            </div>
            <a href="informes_competicion.php" class="w-full flex items-center gap-3 p-2 rounded-2xl transition-all duration-200 hover:bg-slate-100/80 group <?php echo $current_page == 'informes_competicion.php' ? 'nav-item-active' : 'text-slate-600 hover:text-slate-900'; ?>" title="Informes">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 <?php echo $current_page == 'informes_competicion.php' ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200/70 group-hover:text-slate-800'; ?> transition-all">
                    <i class="fas fa-file-pdf text-sm"></i>
                </div>
                <span class="sidebar-text hidden font-bold text-xs flex-1 text-left">Informes Oficiales</span>
            </a>
        </div>

        <!-- 5. Usuarios -->
        <div class="space-y-1">
            <div class="sidebar-text hidden px-3 text-[9px] font-black uppercase text-slate-400/90 mb-1.5 tracking-widest flex items-center gap-2">
                <span>Seguridad</span>
                <div class="h-px flex-1 bg-slate-200/80"></div>
            </div>
            <div class="relative">
                <button onclick="toggleV3Submenu('sub-users')" class="w-full flex items-center gap-3 p-2 rounded-2xl transition-all duration-200 hover:bg-slate-100/80 group <?php echo in_array($current_page, $group_users) ? 'nav-item-active' : 'text-slate-600 hover:text-slate-900'; ?>" title="Usuarios">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 <?php echo in_array($current_page, $group_users) ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200/70 group-hover:text-slate-800'; ?> transition-all">
                        <i class="fas fa-user-shield text-sm"></i>
                    </div>
                    <span class="sidebar-text hidden font-bold text-xs flex-1 text-left">Usuarios y Acceso</span>
                    <i class="fas fa-chevron-right text-[9px] sidebar-text hidden text-slate-400 transition-transform duration-200 <?php echo in_array($current_page, $group_users) ? 'rotate-90 text-blue-600' : ''; ?>" id="arrow-sub-users"></i>
                </button>
                <div id="sub-users" data-active="<?php echo in_array($current_page, $group_users) ? 'true' : 'false'; ?>" class="<?php echo in_array($current_page, $group_users) ? 'submenu-open' : 'submenu-closed'; ?> sidebar-text hidden ml-6 pl-3 my-1 space-y-1 border-l-2 border-slate-200/80">
                    <a href="usuarios.php" class="flex items-center gap-2.5 px-3 py-1.5 text-xs font-semibold rounded-xl transition-all <?php echo $current_page == 'usuarios.php' ? 'sub-nav-item-active' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/70'; ?> whitespace-nowrap">
                        <i class="fas fa-users-gear w-3.5 text-center text-[11px] opacity-70"></i> Directorio
                    </a>
                    <a href="roles.php" class="flex items-center gap-2.5 px-3 py-1.5 text-xs font-semibold rounded-xl transition-all <?php echo $current_page == 'roles.php' ? 'sub-nav-item-active' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/70'; ?> whitespace-nowrap">
                        <i class="fas fa-user-lock w-3.5 text-center text-[11px] opacity-70"></i> Roles y Permisos
                    </a>
                    <a href="paginas.php" class="flex items-center gap-2.5 px-3 py-1.5 text-xs font-semibold rounded-xl transition-all <?php echo $current_page == 'paginas.php' ? 'sub-nav-item-active' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/70'; ?> whitespace-nowrap">
                        <i class="fas fa-file-shield w-3.5 text-center text-[11px] opacity-70"></i> Catálogo Páginas
                    </a>
                </div>
            </div>
        </div>

        <!-- 6. Mantenimiento -->
        <div class="space-y-1">
            <div class="sidebar-text hidden px-3 text-[9px] font-black uppercase text-slate-400/90 mb-1.5 tracking-widest flex items-center gap-2">
                <span>Sistema</span>
                <div class="h-px flex-1 bg-slate-200/80"></div>
            </div>
            
            <button onclick="toggleV3Submenu('sub-maint')" class="w-full flex items-center gap-3 p-2 rounded-2xl transition-all duration-200 hover:bg-slate-100/80 group <?php echo in_array($current_page, $group_mantenimiento) ? 'nav-item-active' : 'text-slate-600 hover:text-slate-900'; ?>" title="Mantenimiento">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 <?php echo in_array($current_page, $group_mantenimiento) ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200/70 group-hover:text-slate-800'; ?> transition-all">
                    <i class="fa-solid fa-screwdriver-wrench text-sm"></i>
                </div>
                <span class="sidebar-text hidden font-bold text-xs flex-1 text-left">Mantenimiento</span>
                <i class="fas fa-chevron-right text-[9px] sidebar-text hidden text-slate-400 transition-transform duration-200 <?php echo in_array($current_page, $group_mantenimiento) ? 'rotate-90 text-blue-600' : ''; ?>" id="arrow-sub-maint"></i>
            </button>
            <div id="sub-maint" data-active="<?php echo in_array($current_page, $group_mantenimiento) ? 'true' : 'false'; ?>" class="<?php echo in_array($current_page, $group_mantenimiento) ? 'submenu-open' : 'submenu-closed'; ?> sidebar-text hidden ml-6 pl-3 my-1 space-y-1 border-l-2 border-slate-200/80">
                <a href="estado_sistema.php" class="flex items-center gap-2.5 px-3 py-1.5 text-xs font-semibold rounded-xl transition-all <?php echo $current_page == 'estado_sistema.php' ? 'sub-nav-item-active' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/70'; ?> whitespace-nowrap">
                    <i class="fas fa-chart-line w-3.5 text-center text-[11px] opacity-70"></i> Estado
                </a>
                <a href="configuracion_sistema.php" class="flex items-center gap-2.5 px-3 py-1.5 text-xs font-semibold rounded-xl transition-all <?php echo $current_page == 'configuracion_sistema.php' ? 'sub-nav-item-active' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/70'; ?> whitespace-nowrap">
                    <i class="fas fa-sliders w-3.5 text-center text-[11px] opacity-70"></i> Configuración
                </a>
                <a href="diagnostico.php" class="flex items-center gap-2.5 px-3 py-1.5 text-xs font-semibold rounded-xl transition-all <?php echo $current_page == 'diagnostico.php' ? 'sub-nav-item-active' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/70'; ?> whitespace-nowrap">
                    <i class="fas fa-stethoscope w-3.5 text-center text-[11px] opacity-70"></i> Diagnóstico
                </a>
            </div>
        </div>

    </nav>
<?php
}

// BARRA LATERAL PARA EL ROL JUEZ (id_rol = 4)
if(isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == '4'){
    $current_page = basename($_SERVER['PHP_SELF']);
    $group_bias = ['analisis_jueces.php', 'analisis_juez_detalle.php', 'ranking_jueces.php', 'perfil_juez.php', 'mi_auditoria.php'];
?>
<aside id="sidebar" class="sidebar-transition fixed top-0 left-0 z-50 w-72 lg:w-20 h-screen bg-white text-slate-700 flex flex-col shadow-xl transform -translate-x-full lg:translate-x-0 overflow-x-hidden overflow-y-auto no-scrollbar border-r border-slate-200/80 font-lexend">
    
    <!-- BRAND AREA -> HOME LINK (JUEZ) -->
    <a href="index.php" class="flex-shrink-0 h-20 flex items-center px-4 lg:px-5 overflow-hidden bg-slate-50/80 border-b border-slate-200/80 hover:bg-slate-100/80 transition-colors no-underline group relative" title="SINCRM Inicio">
        <div class="w-11 h-11 rounded-2xl bg-white p-1.5 flex-shrink-0 flex items-center justify-center shadow-xs border border-slate-200/90 group-hover:scale-105 group-hover:shadow-md transition-all">
            <img src="./img/logo_sincrm_icon.png" class="w-full h-full object-contain" alt="SINCRM">
        </div>
        <div class="sidebar-text hidden ml-3 min-w-0 flex-1">
            <div class="flex items-center gap-1.5 leading-none mb-1">
                <span class="text-base font-black tracking-tight text-slate-800">SINCRM</span>
                <span class="px-1.5 py-0.5 rounded-md bg-blue-50 border border-blue-200 text-blue-600 text-[9px] font-black">4</span>
            </div>
            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider leading-none truncate">Panel del Juez</p>
        </div>
    </a>

    <!-- MAIN NAVIGATION -->
    <nav class="flex-1 px-3 space-y-4 mt-4">
        
        <!-- 1. Perfil -->
        <div class="space-y-1">
            <div class="sidebar-text hidden px-3 text-[9px] font-black uppercase text-slate-400/90 mb-1.5 tracking-widest flex items-center gap-2">
                <span>Mi Cuenta</span>
                <div class="h-px flex-1 bg-slate-200/80"></div>
            </div>
            <a href="perfil.php" class="w-full flex items-center gap-3 p-2 rounded-2xl transition-all duration-200 hover:bg-slate-100/80 group <?php echo $current_page == 'perfil.php' ? 'nav-item-active' : 'text-slate-600 hover:text-slate-900'; ?>" title="Mi Perfil">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 <?php echo $current_page == 'perfil.php' ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200/70 group-hover:text-slate-800'; ?> transition-all">
                    <i class="fas fa-user-circle text-sm"></i>
                </div>
                <span class="sidebar-text hidden font-bold text-xs flex-1 text-left">Mi Perfil</span>
            </a>
        </div>

        <!-- 2. BIAS Analizer -->
        <div class="space-y-1">
            <div class="sidebar-text hidden px-3 text-[9px] font-black uppercase text-slate-400/90 mb-1.5 tracking-widest flex items-center gap-2">
                <span>Evaluación</span>
                <div class="h-px flex-1 bg-slate-200/80"></div>
            </div>

            <button onclick="toggleV3Submenu('sub-bias')" class="w-full flex items-center gap-3 p-2 rounded-2xl transition-all duration-200 hover:bg-slate-100/80 group <?php echo in_array($current_page, $group_bias) ? 'nav-item-active' : 'text-slate-600 hover:text-slate-900'; ?>" title="BIAS Analizer">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 <?php echo in_array($current_page, $group_bias) ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200/70 group-hover:text-slate-800'; ?> transition-all">
                    <i class="fas fa-magnifying-glass-chart text-sm"></i>
                </div>
                <span class="sidebar-text hidden font-bold text-xs flex-1 text-left whitespace-nowrap">BIAS Analizer</span>
                <i class="fas fa-chevron-right text-[9px] sidebar-text hidden text-slate-400 transition-transform duration-200 <?php echo in_array($current_page, $group_bias) ? 'rotate-90 text-blue-600' : ''; ?>" id="arrow-sub-bias"></i>
            </button>
            <div id="sub-bias" data-active="<?php echo in_array($current_page, $group_bias) ? 'true' : 'false'; ?>" class="<?php echo in_array($current_page, $group_bias) ? 'submenu-open' : 'submenu-closed'; ?> sidebar-text hidden ml-6 pl-3 my-1 space-y-1 border-l-2 border-slate-200/80">
                <a href="mi_auditoria.php" class="flex items-center gap-2.5 px-3 py-1.5 text-xs font-semibold rounded-xl transition-all <?php echo $current_page == 'mi_auditoria.php' ? 'sub-nav-item-active' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/70'; ?> whitespace-nowrap">
                    <i class="fas fa-user-check w-3.5 text-center text-[11px] opacity-70"></i> Mi Auditoría
                </a>
                <a href="ranking_jueces.php" class="flex items-center gap-2.5 px-3 py-1.5 text-xs font-semibold rounded-xl transition-all <?php echo $current_page == 'ranking_jueces.php' ? 'sub-nav-item-active' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/70'; ?> whitespace-nowrap">
                    <i class="fas fa-ranking-star w-3.5 text-center text-[11px] opacity-70"></i> Ranking Calidad
                </a>
                <a href="analisis_jueces.php" class="flex items-center gap-2.5 px-3 py-1.5 text-xs font-semibold rounded-xl transition-all <?php echo in_array($current_page, ['analisis_jueces.php', 'analisis_juez_detalle.php']) ? 'sub-nav-item-active' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/70'; ?> whitespace-nowrap">
                    <i class="fas fa-calendar-check w-3.5 text-center text-[11px] opacity-70"></i> Auditoría Evento
                </a>
            </div>
        </div>

        <!-- 3. Informes -->
        <div class="space-y-1">
            <div class="sidebar-text hidden px-3 text-[9px] font-black uppercase text-slate-400/90 mb-1.5 tracking-widest flex items-center gap-2">
                <span>Documentos</span>
                <div class="h-px flex-1 bg-slate-200/80"></div>
            </div>
            <a href="informes_competicion.php" class="w-full flex items-center gap-3 p-2 rounded-2xl transition-all duration-200 hover:bg-slate-100/80 group <?php echo $current_page == 'informes_competicion.php' ? 'nav-item-active' : 'text-slate-600 hover:text-slate-900'; ?>" title="Informes">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 <?php echo $current_page == 'informes_competicion.php' ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200/70 group-hover:text-slate-800'; ?> transition-all">
                    <i class="fas fa-file-pdf text-sm"></i>
                </div>
                <span class="sidebar-text hidden font-bold text-xs flex-1 text-left">Informes</span>
            </a>
        </div>

    </nav>
<?php
}

// BARRA LATERAL PARA EL ROL CLUB (id_rol = 5)
if(isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == '5'){
    $current_page = basename($_SERVER['PHP_SELF']);
    $group_club_comp = ['inscripciones.php', 'inscripciones_figuras.php', 'rutinas.php', 'sorteo_rutinas.php', 'puntuaciones_lista_fases.php', 'coach_card_composer.php'];
?>
<aside id="sidebar" class="sidebar-transition fixed top-0 left-0 z-50 w-72 lg:w-20 h-screen bg-white text-slate-700 flex flex-col shadow-xl transform -translate-x-full lg:translate-x-0 overflow-x-hidden overflow-y-auto no-scrollbar border-r border-slate-200/80 font-lexend">
    
    <!-- BRAND AREA (CLUB) -->
    <a href="index.php" class="flex-shrink-0 h-20 flex items-center px-4 lg:px-5 overflow-hidden bg-slate-50/80 border-b border-slate-200/80 hover:bg-slate-100/80 transition-colors no-underline group relative" title="SINCRM Inicio">
        <div class="w-11 h-11 rounded-2xl bg-white p-1.5 flex-shrink-0 flex items-center justify-center shadow-xs border border-slate-200/90 group-hover:scale-105 group-hover:shadow-md transition-all">
            <img src="./img/logo_sincrm_icon.png" class="w-full h-full object-contain" alt="SINCRM">
        </div>
        <div class="sidebar-text hidden ml-3 min-w-0 flex-1">
            <div class="flex items-center gap-1.5 leading-none mb-1">
                <span class="text-base font-black tracking-tight text-slate-800">SINCRM</span>
                <span class="px-1.5 py-0.5 rounded-md bg-blue-50 border border-blue-200 text-blue-600 text-[9px] font-black">4</span>
            </div>
            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider leading-none truncate">
                <?php echo htmlspecialchars($_SESSION['nombre_club'] ?? 'Mi Club'); ?>
            </p>
        </div>
    </a>

    <!-- MAIN NAVIGATION -->
    <nav class="flex-1 px-3 space-y-4 mt-4">
        
        <!-- 1. Gestión de Equipo -->
        <div class="space-y-1">
            <div class="sidebar-text hidden px-3 text-[9px] font-black uppercase text-slate-400/90 mb-1.5 tracking-widest flex items-center gap-2">
                <span>Mi Entorno</span>
                <div class="h-px flex-1 bg-slate-200/80"></div>
            </div>
            <a href="mi_equipo.php" class="w-full flex items-center gap-3 p-2 rounded-2xl transition-all duration-200 hover:bg-slate-100/80 group <?php echo $current_page == 'mi_equipo.php' ? 'nav-item-active' : 'text-slate-600 hover:text-slate-900'; ?>" title="Mi Equipo">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 <?php echo $current_page == 'mi_equipo.php' ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200/70 group-hover:text-slate-800'; ?> transition-all">
                    <i class="fas fa-users-gear text-sm"></i>
                </div>
                <span class="sidebar-text hidden font-bold text-xs flex-1 text-left">Mi Equipo</span>
            </a>
            <a href="nadadoras.php" class="w-full flex items-center gap-3 p-2 rounded-2xl transition-all duration-200 hover:bg-slate-100/80 group <?php echo $current_page == 'nadadoras.php' ? 'nav-item-active' : 'text-slate-600 hover:text-slate-900'; ?>" title="Nadadoras">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 <?php echo $current_page == 'nadadoras.php' ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200/70 group-hover:text-slate-800'; ?> transition-all">
                    <i class="fas fa-person-swimming text-sm"></i>
                </div>
                <span class="sidebar-text hidden font-bold text-xs flex-1 text-left">Nadadoras</span>
            </a>
        </div>

        <!-- 2. Competición -->
        <div class="space-y-1">
            <div class="sidebar-text hidden px-3 text-[9px] font-black uppercase text-slate-400/90 mb-1.5 tracking-widest flex items-center gap-2">
                <span>Operativa</span>
                <div class="h-px flex-1 bg-slate-200/80"></div>
            </div>

            <button onclick="toggleV3Submenu('sub-comp-club')" class="w-full flex items-center gap-3 p-2 rounded-2xl transition-all duration-200 hover:bg-slate-100/80 group <?php echo in_array($current_page, $group_club_comp) ? 'nav-item-active' : 'text-slate-600 hover:text-slate-900'; ?>" title="Competición">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 <?php echo in_array($current_page, $group_club_comp) ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200/70 group-hover:text-slate-800'; ?> transition-all">
                    <i class="fas fa-flag-checkered text-sm"></i>
                </div>
                <span class="sidebar-text hidden font-bold text-xs flex-1 text-left whitespace-nowrap">Gestión Activa</span>
                <i class="fas fa-chevron-right text-[9px] sidebar-text hidden text-slate-400 transition-transform duration-200 <?php echo in_array($current_page, $group_club_comp) ? 'rotate-90 text-blue-600' : ''; ?>" id="arrow-sub-comp-club"></i>
            </button>
            <div id="sub-comp-club" data-active="<?php echo in_array($current_page, $group_club_comp) ? 'true' : 'false'; ?>" class="<?php echo in_array($current_page, $group_club_comp) ? 'submenu-open' : 'submenu-closed'; ?> sidebar-text hidden ml-6 pl-3 my-1 space-y-1 border-l-2 border-slate-200/80">
                <a href="inscripciones.php" class="flex items-center gap-2.5 px-3 py-1.5 text-xs font-semibold rounded-xl transition-all <?php echo in_array($current_page, ['inscripciones.php', 'inscripciones_figuras.php']) ? 'sub-nav-item-active' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/70'; ?>">
                    <i class="fas fa-clipboard-list w-3.5 text-center text-[11px] opacity-70"></i> Inscripciones
                </a>
            </div>
        </div>

    </nav>
<?php
}
?>

    <!-- FOOTER / TOGGLE (COMÚN) -->
    <?php if(isset($_SESSION['id_rol'])): ?>
    <div class="flex-shrink-0 p-3 bg-slate-50/80 border-t border-slate-200/80">
        <button onclick="toggleV3Sidebar()" class="w-full h-11 flex items-center justify-center rounded-xl bg-white hover:bg-slate-100 active:scale-[0.98] transition-all group overflow-hidden border border-slate-200/80 tap-press shadow-xs">
            <i class="fas fa-angles-right text-slate-400 group-hover:text-blue-600 text-xs transition-colors" id="v3-toggle-icon"></i>
            <span class="sidebar-text hidden ml-2.5 text-[9px] font-black uppercase tracking-widest text-slate-600 group-hover:text-blue-600 transition-colors">Compactar Menú</span>
        </button>
        <div class="sidebar-text hidden mt-2 text-center">
            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest tabular-nums">SINCRM v<?php echo htmlspecialchars(getVersion()); ?></span>
        </div>
    </div>
    <?php endif; ?>
</aside>

<!-- Overlay global -->
<div id="sidebar-overlay" onclick="toggleV3Sidebar()" class="fixed inset-0 bg-slate-900/30 backdrop-blur-[2px] z-40 hidden transition-all duration-300 opacity-0"></div>

<style>
    @media (min-width: 1024px) {
        main { margin-left: 80px !important; }
    }
</style>

<script>
    function openV3Sidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const icon = document.getElementById('v3-toggle-icon');
        const texts = document.querySelectorAll('.sidebar-text');
        if (!sidebar) return;

        if (window.innerWidth < 1024) {
            sidebar.classList.remove('-translate-x-full');
            texts.forEach(t => t.classList.remove('hidden')); 
            if (overlay) {
                overlay.classList.remove('hidden');
                setTimeout(() => overlay.classList.add('opacity-100'), 10);
            }
        } else {
            sidebar.classList.replace('lg:w-20', 'lg:w-72');
            if (icon) icon.classList.replace('fa-angles-right', 'fa-angles-left');
            texts.forEach(t => t.classList.remove('hidden'));
            if (overlay) {
                overlay.classList.remove('hidden');
                setTimeout(() => overlay.classList.add('opacity-100'), 10);
            }
        }
    }

    function closeV3Sidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const icon = document.getElementById('v3-toggle-icon');
        const texts = document.querySelectorAll('.sidebar-text');
        if (!sidebar) return;

        if (window.innerWidth < 1024) {
            sidebar.classList.add('-translate-x-full');
            if (overlay) {
                overlay.classList.remove('opacity-100');
                setTimeout(() => overlay.classList.add('hidden'), 500);
            }
        } else {
            sidebar.classList.replace('lg:w-72', 'lg:w-20');
            if (icon) icon.classList.replace('fa-angles-left', 'fa-angles-right');
            texts.forEach(t => t.classList.add('hidden'));
            if (overlay) {
                overlay.classList.remove('opacity-100');
                setTimeout(() => overlay.classList.add('hidden'), 500);
            }
        }

        // Al compactar/cerrar el sidebar, restaurar submenús al estado base: el activo abierto y los inactivos cerrados
        resetSubmenusToActive();
    }

    function resetSubmenusToActive() {
        document.querySelectorAll('[id^="sub-"]').forEach(sm => {
            const arrow = document.getElementById('arrow-' + sm.id);
            const isActive = sm.getAttribute('data-active') === 'true';
            if (isActive) {
                sm.classList.replace('submenu-closed', 'submenu-open');
                if (arrow) arrow.classList.add('rotate-90');
            } else {
                sm.classList.replace('submenu-open', 'submenu-closed');
                if (arrow) arrow.classList.remove('rotate-90');
            }
        });
    }

    function toggleV3Sidebar() {
        const sidebar = document.getElementById('sidebar');
        if (!sidebar) return;

        const isClosed = (window.innerWidth < 1024)
            ? sidebar.classList.contains('-translate-x-full')
            : sidebar.classList.contains('lg:w-20');

        if (isClosed) {
            openV3Sidebar();
        } else {
            closeV3Sidebar();
        }
    }

    function toggleV3Submenu(id) {
        const sidebar = document.getElementById('sidebar');
        if (!sidebar) return;

        const isClosed = (window.innerWidth < 1024)
            ? sidebar.classList.contains('-translate-x-full')
            : sidebar.classList.contains('lg:w-20');

        const targetSubmenu = document.getElementById(id);
        if (!targetSubmenu) return;

        const isTargetActive = targetSubmenu.getAttribute('data-active') === 'true';

        // 1. Si el sidebar está cerrado, abrirlo primero
        if (isClosed) {
            openV3Sidebar();
        }

        // 2. Si se pincha sobre el elemento padre ACTIVO:
        // Debe quedarse SIEMPRE abierto (no cerrarse). Los demás cerrados.
        if (isTargetActive) {
            document.querySelectorAll('[id^="sub-"]').forEach(sm => {
                const arrow = document.getElementById('arrow-' + sm.id);
                if (sm.id === id) {
                    sm.classList.replace('submenu-closed', 'submenu-open');
                    if (arrow) arrow.classList.add('rotate-90');
                } else {
                    sm.classList.replace('submenu-open', 'submenu-closed');
                    if (arrow) arrow.classList.remove('rotate-90');
                }
            });
            return;
        }

        // 3. Si se pincha sobre un elemento INACTIVO:
        // Solo se cierra el activo y se abre este inactivo. Si ya estaba abierto, se alterna.
        const wasClosed = targetSubmenu.classList.contains('submenu-closed');

        document.querySelectorAll('[id^="sub-"]').forEach(sm => {
            const arrow = document.getElementById('arrow-' + sm.id);
            if (sm.id === id) {
                if (wasClosed) {
                    sm.classList.replace('submenu-closed', 'submenu-open');
                    if (arrow) arrow.classList.add('rotate-90');
                } else {
                    sm.classList.replace('submenu-open', 'submenu-closed');
                    if (arrow) arrow.classList.remove('rotate-90');
                }
            } else {
                sm.classList.replace('submenu-open', 'submenu-closed');
                if (arrow) arrow.classList.remove('rotate-90');
            }
        });
    }

    document.addEventListener('click', function(event) {
        const sidebar = document.getElementById('sidebar');
        if (!sidebar) return;
        if (window.innerWidth >= 1024 && sidebar.classList.contains('lg:w-72')) {
            if (!sidebar.contains(event.target)) {
                closeV3Sidebar();
            }
        }
    });
</script>
