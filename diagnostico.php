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
        <div class="mb-6 sm:mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4 sm:gap-6">
            <div>
                <h1 class="text-2xl sm:text-4xl font-black text-slate-800 tracking-tighter mb-1 sm:mb-2 flex items-center gap-2.5 sm:gap-3">
                    <span class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-stethoscope text-base sm:text-lg"></i></span>
                    Diagnóstico de Integridad
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 font-medium italic">Análisis exhaustivo para mantener la salud técnica de la base de datos.</p>
            </div>
            <div class="flex gap-2 sm:gap-3">
                <a href="mantenimiento.php" class="w-full sm:w-auto px-4 sm:px-5 py-2 sm:py-2.5 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-sm text-xs italic"><i class="fas fa-arrow-left text-xs"></i> Volver</a>
            </div>
        </div>

        <div class="space-y-6 sm:space-y-12">
            
            <!-- BLOQUE 1: DEPORTISTAS -->
            <div>
                <div class="flex items-center gap-3 sm:gap-4 border-l-4 sm:border-l-[8px] border-blue-500 pl-3.5 sm:pl-6 py-1 sm:py-2 mb-4 sm:mb-8 uppercase tracking-widest text-blue-600 text-xs sm:text-sm font-black italic">
                    Ámbito Deportistas
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-6">
                    <!-- Nadadoras Duplicadas -->
                    <div class="bg-white rounded-2xl sm:rounded-[2.5rem] p-4 sm:p-8 shadow-sm border border-slate-200 border-l-4 sm:border-l-[6px] border-l-blue-500 flex flex-col justify-between group hover:shadow-xl transition-all">
                        <div>
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mb-3 sm:mb-6 shadow-sm border border-blue-100"><i class="fas fa-copy text-base sm:text-lg"></i></div>
                            <h3 class="text-base sm:text-xl font-black text-slate-800 mb-1 sm:mb-2">Posibles Duplicados</h3>
                            <p class="text-[11px] sm:text-xs font-medium text-slate-400 italic mb-4 sm:mb-8">Detección por coincidencia exacta de Nombre y Apellidos o Número de Licencia.</p>
                        </div>
                        <?php
                        $q_dup = "SELECT COUNT(*) as total FROM (SELECT COUNT(*) FROM nadadoras GROUP BY nombre, apellidos HAVING COUNT(*) > 1) as t";
                        $dups = mysqli_fetch_assoc(mysqli_query($connection, $q_dup))['total'];
                        ?>
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xl sm:text-2xl font-black tabular-nums <?php echo $dups > 0 ? 'text-red-500' : 'text-slate-400'; ?>"><?php echo $dups; ?> <span class="text-[10px] uppercase font-bold">coincidencias</span></span>
                            <button onclick="analizar('deportistas_duplicados')" class="px-4 sm:px-6 py-2 sm:py-2.5 bg-blue-600 text-white text-[9px] sm:text-[10px] font-black uppercase tracking-wider rounded-xl shadow-lg shadow-blue-500/20 active:scale-[0.98] transition-all">Analizar</button>
                        </div>
                    </div>

                    <!-- Nadadoras sin Actividad -->
                    <div class="bg-white rounded-2xl sm:rounded-[2.5rem] p-4 sm:p-8 shadow-sm border border-slate-200 border-l-4 sm:border-l-[6px] border-l-blue-400 flex flex-col justify-between group hover:shadow-xl transition-all">
                        <div>
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center mb-3 sm:mb-6 shadow-sm border border-slate-100"><i class="fas fa-user-slash text-base sm:text-lg"></i></div>
                            <h3 class="text-base sm:text-xl font-black text-slate-800 mb-1 sm:mb-2">Sin Inscripciones</h3>
                            <p class="text-[11px] sm:text-xs font-medium text-slate-400 italic mb-4 sm:mb-8">Nadadoras registradas que nunca han participado en fases de figuras ni en rutinas.</p>
                        </div>
                        <?php
                        $q_no_act = "SELECT COUNT(*) as total FROM nadadoras WHERE id NOT IN (SELECT id_nadadora FROM inscripciones_figuras) AND id NOT IN (SELECT id_nadadora FROM rutinas_participantes)";
                        $no_act = mysqli_fetch_assoc(mysqli_query($connection, $q_no_act))['total'];
                        ?>
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xl sm:text-2xl font-black text-slate-400 tabular-nums"><?php echo $no_act; ?> <span class="text-[10px] uppercase font-bold">inactivas</span></span>
                            <button onclick="analizar('deportistas_sin_inscripciones')" class="px-4 sm:px-6 py-2 sm:py-2.5 bg-slate-800 text-white text-[9px] sm:text-[10px] font-black uppercase tracking-wider rounded-xl shadow-lg active:scale-[0.98] transition-all">Ver Listado</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BLOQUE 2: JUECES -->
            <div>
                <div class="flex items-center gap-3 sm:gap-4 border-l-4 sm:border-l-[8px] border-amber-500 pl-3.5 sm:pl-6 py-1 sm:py-2 mb-4 sm:mb-8 uppercase tracking-widest text-amber-600 text-xs sm:text-sm font-black italic">
                    Ámbito Arbitral
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-6">
                    <!-- Jueces Duplicados -->
                    <div class="bg-white rounded-2xl sm:rounded-[2.5rem] p-4 sm:p-8 shadow-sm border border-slate-200 border-l-4 sm:border-l-[6px] border-l-amber-500 flex flex-col justify-between group hover:shadow-xl transition-all">
                        <div>
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mb-3 sm:mb-6 shadow-sm border border-amber-100"><i class="fas fa-users-viewfinder text-base sm:text-lg"></i></div>
                            <h3 class="text-base sm:text-xl font-black text-slate-800 mb-1 sm:mb-2">Jueces Duplicados</h3>
                            <p class="text-[11px] sm:text-xs font-medium text-slate-400 italic mb-4 sm:mb-8">Detección de fichas de jueces repetidas por nombre o licencia en el censo oficial.</p>
                        </div>
                        <?php
                        $q_j_dup = "SELECT COUNT(*) as total FROM (SELECT COUNT(*) FROM jueces GROUP BY nombre, apellidos HAVING COUNT(*) > 1) as t";
                        $j_dups = mysqli_fetch_assoc(mysqli_query($connection, $q_j_dup))['total'];
                        ?>
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xl sm:text-2xl font-black tabular-nums <?php echo $j_dups > 0 ? 'text-amber-600' : 'text-slate-400'; ?>"><?php echo $j_dups; ?> <span class="text-[10px] uppercase font-bold">posibles</span></span>
                            <button onclick="analizar('jueces_duplicados')" class="px-4 sm:px-6 py-2 sm:py-2.5 bg-amber-500 text-white text-[9px] sm:text-[10px] font-black uppercase tracking-wider rounded-xl shadow-lg shadow-amber-500/20 active:scale-[0.98] transition-all">Analizar</button>
                        </div>
                    </div>

                    <!-- Jueces sin Cuenta de Acceso -->
                    <div class="bg-white rounded-2xl sm:rounded-[2.5rem] p-4 sm:p-8 shadow-sm border border-slate-200 border-l-4 sm:border-l-[6px] border-l-amber-400 flex flex-col justify-between group hover:shadow-xl transition-all">
                        <div>
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center mb-3 sm:mb-6 shadow-sm border border-slate-100"><i class="fas fa-link-slash text-base sm:text-lg"></i></div>
                            <h3 class="text-base sm:text-xl font-black text-slate-800 mb-1 sm:mb-2">Sin Vinculación Web</h3>
                            <p class="text-[11px] sm:text-xs font-medium text-slate-400 italic mb-4 sm:mb-8">Jueces oficiales que aún no tienen una cuenta de usuario vinculada para acceder a sus datos.</p>
                        </div>
                        <?php
                        $q_j_no_link = "SELECT COUNT(*) as total FROM jueces WHERE id NOT IN (SELECT id_juez_v3 FROM usuarios WHERE id_juez_v3 IS NOT NULL) AND activo = 1";
                        $j_no_link = mysqli_fetch_assoc(mysqli_query($connection, $q_j_no_link))['total'];
                        ?>
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xl sm:text-2xl font-black text-slate-400 tabular-nums"><?php echo $j_no_link; ?> <span class="text-[10px] uppercase font-bold">desvinculados</span></span>
                            <button onclick="analizar('jueces_sin_cuenta')" class="px-4 sm:px-6 py-2 sm:py-2.5 bg-slate-800 text-white text-[9px] sm:text-[10px] font-black uppercase tracking-wider rounded-xl shadow-lg active:scale-[0.98] transition-all">Ver Listado</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BLOQUE 3: OPERATIVA (COMPETICIONES Y FASES) -->
            <div>
                <div class="flex items-center gap-3 sm:gap-4 border-l-4 sm:border-l-[8px] border-emerald-500 pl-3.5 sm:pl-6 py-1 sm:py-2 mb-4 sm:mb-8 uppercase tracking-widest text-emerald-600 text-xs sm:text-sm font-black italic">
                    Competición & Operativa
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-6">
                    <!-- Fases sin Nadadoras -->
                    <div class="bg-white rounded-2xl sm:rounded-[2.5rem] p-4 sm:p-8 shadow-sm border border-slate-200 border-l-4 sm:border-l-[6px] border-l-emerald-500 flex flex-col justify-between group hover:shadow-xl transition-all">
                        <div>
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-3 sm:mb-6 shadow-sm border border-emerald-100"><i class="fas fa-ghost text-base sm:text-lg"></i></div>
                            <h3 class="text-base sm:text-xl font-black text-slate-800 mb-1 sm:mb-2">Fases Vacías</h3>
                            <p class="text-[11px] sm:text-xs font-medium text-slate-400 italic mb-4 sm:mb-8">Fases activas que no tienen ninguna inscripción de deportistas registrada.</p>
                        </div>
                        <?php
                        $q_f_vacia = "SELECT COUNT(*) as total FROM fases WHERE id NOT IN (SELECT id_fase FROM inscripciones_figuras) AND id_competicion IN (SELECT id FROM competiciones WHERE activo = 'si')";
                        $f_vacias = mysqli_fetch_assoc(mysqli_query($connection, $q_f_vacia))['total'];
                        ?>
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xl sm:text-2xl font-black tabular-nums <?php echo $f_vacias > 0 ? 'text-amber-500' : 'text-slate-400'; ?>"><?php echo $f_vacias; ?> <span class="text-[10px] uppercase font-bold">fases</span></span>
                            <button onclick="analizar('fases_vacias')" class="px-4 sm:px-6 py-2 sm:py-2.5 bg-emerald-600 text-white text-[9px] sm:text-[10px] font-black uppercase tracking-wider rounded-xl shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all">Detectar</button>
                        </div>
                    </div>

                    <!-- Competiciones sin Fases -->
                    <div class="bg-white rounded-2xl sm:rounded-[2.5rem] p-4 sm:p-8 shadow-sm border border-slate-200 border-l-4 sm:border-l-[6px] border-l-emerald-400 flex flex-col justify-between group hover:shadow-xl transition-all">
                        <div>
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center mb-3 sm:mb-6 shadow-sm border border-slate-100"><i class="fas fa-calendar-xmark text-base sm:text-lg"></i></div>
                            <h3 class="text-base sm:text-xl font-black text-slate-800 mb-1 sm:mb-2">Eventos Huérfanos</h3>
                            <p class="text-[11px] sm:text-xs font-medium text-slate-400 italic mb-4 sm:mb-8">Competiciones dadas de alta que no tienen fases técnicas asociadas.</p>
                        </div>
                        <?php
                        $q_c_huerf = "SELECT COUNT(*) as total FROM competiciones WHERE id NOT IN (SELECT id_competicion FROM fases)";
                        $c_huerf = mysqli_fetch_assoc(mysqli_query($connection, $q_c_huerf))['total'];
                        ?>
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xl sm:text-2xl font-black text-slate-400 tabular-nums"><?php echo $c_huerf; ?> <span class="text-[10px] uppercase font-bold">eventos</span></span>
                            <button onclick="analizar('competiciones_sin_fases')" class="px-4 sm:px-6 py-2 sm:py-2.5 bg-slate-800 text-white text-[9px] sm:text-[10px] font-black uppercase tracking-wider rounded-xl shadow-lg active:scale-[0.98] transition-all">Verificar</button>
                        </div>
                    </div>

                    <!-- Notas Huérfanas -->
                    <div class="bg-white rounded-2xl sm:rounded-[2.5rem] p-4 sm:p-8 shadow-sm border border-slate-200 border-l-4 sm:border-l-[6px] border-l-red-500 flex flex-col justify-between group hover:shadow-xl transition-all">
                        <div>
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-red-50 text-red-600 flex items-center justify-center mb-3 sm:mb-6 shadow-sm border border-red-100"><i class="fas fa-triangle-exclamation text-base sm:text-lg"></i></div>
                            <h3 class="text-base sm:text-xl font-black text-slate-800 mb-1 sm:mb-2">Integridad de Notas</h3>
                            <p class="text-[11px] sm:text-xs font-medium text-slate-400 italic mb-4 sm:mb-8">Notas que han perdido su panel de jueces. Acción crítica requerida si hay detecciones.</p>
                        </div>
                        <?php
                        $q_n_huerf = "SELECT COUNT(*) as total FROM puntuaciones_jueces WHERE id_panel_juez NOT IN (SELECT id FROM panel_jueces)";
                        $n_huerf = mysqli_fetch_assoc(mysqli_query($connection, $q_n_huerf))['total'];
                        ?>
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xl sm:text-2xl font-black tabular-nums <?php echo $n_huerf > 0 ? 'text-red-600 animate-pulse' : 'text-slate-400'; ?>"><?php echo $n_huerf; ?> <span class="text-[10px] uppercase font-bold">críticos</span></span>
                            <button onclick="analizar('notas_huerfanas')" class="px-4 sm:px-6 py-2 sm:py-2.5 bg-red-600 text-white text-[9px] sm:text-[10px] font-black uppercase tracking-wider rounded-xl shadow-lg shadow-red-500/20 active:scale-[0.98] transition-all">Reparar</button>
                        </div>
                    </div>

                    <!-- Limpieza Selectiva y Cascada de Competiciones -->
                    <div class="bg-white rounded-2xl sm:rounded-[2.5rem] p-4 sm:p-8 shadow-sm border border-slate-200 border-l-4 sm:border-l-[6px] border-l-slate-800 flex flex-col justify-between group hover:shadow-xl transition-all">
                        <div>
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-slate-100 text-slate-800 flex items-center justify-center mb-3 sm:mb-6 shadow-sm border border-slate-200"><i class="fas fa-broom text-base sm:text-lg"></i></div>
                            <h3 class="text-base sm:text-xl font-black text-slate-800 mb-1 sm:mb-2">Limpieza Selectiva / Cascada</h3>
                            <p class="text-[11px] sm:text-xs font-medium text-slate-400 italic mb-4 sm:mb-8">Purga modular de datos de prueba (músicas, coach cards, inscripciones, notas) o eliminación total de competiciones.</p>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-[9px] sm:text-[10px] font-black uppercase text-slate-400">Control Modular</span>
                            <button onclick="abrirSelectorCascada()" class="px-4 sm:px-6 py-2 sm:py-2.5 bg-slate-800 text-white text-[9px] sm:text-[10px] font-black uppercase tracking-wider rounded-xl shadow-lg active:scale-[0.98] transition-all flex items-center gap-2"><i class="fas fa-sliders text-xs"></i> Iniciar Limpieza</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BLOQUE 4: MULTIMEDIA -->
            <div>
                <div class="flex items-center gap-3 sm:gap-4 border-l-4 sm:border-l-[8px] border-indigo-500 pl-3.5 sm:pl-6 py-1 sm:py-2 mb-4 sm:mb-8 uppercase tracking-widest text-indigo-600 text-xs sm:text-sm font-black italic">
                    Ámbito Multimedia
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-6">
                    <!-- Archivos MP3 Huérfanos -->
                    <div class="bg-white rounded-2xl sm:rounded-[2.5rem] p-4 sm:p-8 shadow-sm border border-slate-200 border-l-4 sm:border-l-[6px] border-l-indigo-500 flex flex-col justify-between group hover:shadow-xl transition-all">
                        <div>
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-3 sm:mb-6 shadow-sm border border-indigo-100"><i class="fas fa-file-audio text-base sm:text-lg"></i></div>
                            <h3 class="text-base sm:text-xl font-black text-slate-800 mb-1 sm:mb-2">Música Huérfana</h3>
                            <p class="text-[11px] sm:text-xs font-medium text-slate-400 italic mb-4 sm:mb-8">Archivos MP3 en el servidor que no están vinculados a ninguna rutina en la base de datos.</p>
                        </div>
                        <?php
                        $huerfanos_mp3 = 0;
                        $valid_rutinas = [];
                        $q_all_rutinas = "SELECT id, id_competicion FROM rutinas";
                        $res_all = mysqli_query($connection, $q_all_rutinas);
                        if ($res_all) {
                            while($row_all = mysqli_fetch_assoc($res_all)) {
                                $valid_rutinas[$row_all['id_competicion']][$row_all['id']] = true;
                            }
                        }

                        $path_base = './public/music/';
                        if (is_dir($path_base)) {
                            $items = scandir($path_base);
                            foreach ($items as $item) {
                                if ($item === '.' || $item === '..') continue;
                                $comp_path = $path_base . $item;
                                if (is_dir($comp_path)) {
                                    $id_comp = $item;
                                    $files = scandir($comp_path);
                                    foreach ($files as $file) {
                                        if (pathinfo($file, PATHINFO_EXTENSION) === 'mp3') {
                                            $id_rutina = pathinfo($file, PATHINFO_FILENAME);
                                            if (!isset($valid_rutinas[$id_comp][$id_rutina])) {
                                                $huerfanos_mp3++;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        ?>
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xl sm:text-2xl font-black tabular-nums <?php echo $huerfanos_mp3 > 0 ? 'text-amber-500' : 'text-slate-400'; ?>"><?php echo $huerfanos_mp3; ?> <span class="text-[10px] uppercase font-bold">archivos</span></span>
                            <button onclick="analizar('archivos_musica_huerfanos')" class="px-4 sm:px-6 py-2 sm:py-2.5 bg-indigo-600 text-white text-[9px] sm:text-[10px] font-black uppercase tracking-wider rounded-xl shadow-lg shadow-indigo-500/20 active:scale-[0.98] transition-all">Analizar</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</main>

<script>
    let rawCompeticiones = [];

    function abrirSelectorCascada() {
        const formData = new FormData();
        formData.append('action', 'get_competiciones_list');

        Swal.fire({
            title: 'Cargando competiciones...',
            didOpen: () => { Swal.showLoading(); }
        });

        fetch('mantenimiento_code.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                rawCompeticiones = data.competiciones || [];
                
                let html = `
                <div class="text-left font-lexend space-y-4">
                    <p class="text-xs font-bold text-slate-500 italic">Selecciona una o varias competiciones para simular su limpieza:</p>
                    
                    <div class="relative">
                        <i class="fas fa-search absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                        <input type="text" id="filter-comp-input" placeholder="Buscar por nombre o ID..." class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-800 transition-all" oninput="filtrarCompeticionesModal(this.value)">
                    </div>

                    <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-wider text-slate-400 px-1">
                        <span>Competiciones encontradas: <span id="comp-count" class="text-slate-700 font-bold">${rawCompeticiones.length}</span></span>
                        <div class="flex gap-2">
                            <button type="button" onclick="marcarTodasComps(true)" class="text-blue-600 hover:underline">Marcar todas</button>
                            <span>&bull;</span>
                            <button type="button" onclick="marcarTodasComps(false)" class="text-slate-500 hover:underline">Desmarcar</button>
                        </div>
                    </div>

                    <div id="comps-container" class="max-h-64 overflow-y-auto custom-scrollbar space-y-2 pr-1">`;
                
                rawCompeticiones.forEach(c => {
                    html += `
                    <label class="comp-label flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100 hover:bg-slate-100/80 transition-all cursor-pointer group" data-id="${c.id}" data-name="${c.nombre.toLowerCase()}">
                        <input type="checkbox" name="comp_ids[]" value="${c.id}" class="w-4 h-4 rounded border-slate-300 text-slate-900 focus:ring-slate-800 cursor-pointer">
                        <div class="flex-1 min-w-0">
                            <p class="text-[11px] font-black text-slate-800 truncate">${c.nombre}</p>
                            <p class="text-[9px] font-bold text-slate-400 uppercase italic">ID: #${c.id} | Fecha: ${c.fecha || 'Sin fecha'}</p>
                        </div>
                        <span class="text-[9px] font-black px-2 py-0.5 rounded-lg bg-white border border-slate-200 text-slate-500 tabular-nums">#${c.id}</span>
                    </label>`;
                });

                html += `</div></div>`;

                Swal.fire({
                    title: 'Seleccionar Competición',
                    html: html,
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-play text-xs mr-1"></i> Simular Limpieza',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#0f172a',
                    width: window.innerWidth < 640 ? '95%' : '600px',
                    preConfirm: () => {
                        const selected = Array.from(document.querySelectorAll('input[name="comp_ids[]"]:checked')).map(cb => cb.value);
                        if (selected.length === 0) {
                            Swal.showValidationMessage('Debes seleccionar al menos una competición');
                            return false;
                        }
                        return selected;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        simularBorrado(result.value);
                    }
                });
            } else {
                Swal.fire('Error', 'No se pudo cargar el listado de competiciones.', 'error');
            }
        })
        .catch(err => {
            Swal.fire('Error', 'Fallo de conexión al cargar competiciones.', 'error');
        });
    }

    function filtrarCompeticionesModal(query) {
        const q = query.trim().toLowerCase();
        let visibleCount = 0;
        document.querySelectorAll('#comps-container .comp-label').forEach(el => {
            const id = el.getAttribute('data-id');
            const name = el.getAttribute('data-name');
            if (!q || id.includes(q) || name.includes(q)) {
                el.style.display = 'flex';
                visibleCount++;
            } else {
                el.style.display = 'none';
            }
        });
        const counterEl = document.getElementById('comp-count');
        if (counterEl) counterEl.textContent = visibleCount;
    }

    function marcarTodasComps(check) {
        document.querySelectorAll('#comps-container .comp-label').forEach(el => {
            if (el.style.display !== 'none') {
                const cb = el.querySelector('input[type="checkbox"]');
                if (cb) cb.checked = check;
            }
        });
    }

    let currentSimData = null;
    let currentSimIds = [];

    function simularBorrado(ids) {
        currentSimIds = ids;
        const formData = new FormData();
        formData.append('action', 'simular_cascada');
        formData.append('ids', JSON.stringify(ids));

        Swal.fire({
            title: 'Calculando impacto...',
            html: '<p class="text-xs text-slate-500 font-medium">Analizando dependencias de base de datos y archivos en disco...</p>',
            didOpen: () => { Swal.showLoading(); }
        });

        fetch('mantenimiento_code.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                currentSimData = data;
                renderModalSeleccionModular(data, ids);
            } else {
                Swal.fire('Error', data.message || 'No se pudo simular el borrado.', 'error');
            }
        })
        .catch(err => {
            Swal.fire('Error', 'Fallo de conexión al simular la limpieza.', 'error');
        });
    }

    function renderModalSeleccionModular(data, ids) {
        const compsList = (data.competiciones || []).map(c => `<span class="px-2.5 py-1 bg-white border border-slate-200 rounded-lg text-slate-800 font-bold text-[10px] shadow-sm">#${c.id} ${c.nombre}</span>`).join(' ');

        let html = `
        <div class="text-left font-lexend space-y-4">
            
            <!-- Banner de Competiciones Afectadas -->
            <div class="p-3 bg-slate-50 border border-slate-200 rounded-2xl">
                <p class="text-[9px] font-black uppercase tracking-wider text-slate-400 mb-1.5 flex items-center gap-1.5"><i class="fas fa-trophy text-slate-500"></i> Evento Seleccionado:</p>
                <div class="flex flex-wrap gap-1.5">${compsList}</div>
            </div>

            <!-- Botones de Preconfiguración Rápida -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2 pt-1">
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Presets de Limpieza:</span>
                <div class="flex flex-wrap gap-1.5">
                    <button type="button" onclick="aplicarPreset('pruebas')" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-[10px] font-black uppercase tracking-wider rounded-xl border border-blue-200 active:scale-[0.98] transition-all flex items-center gap-1.5">
                        <i class="fas fa-vial-virus text-xs"></i> Solo Datos de Prueba
                    </button>
                    <button type="button" onclick="aplicarPreset('todo')" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 text-[10px] font-black uppercase tracking-wider rounded-xl border border-red-200 active:scale-[0.98] transition-all flex items-center gap-1.5">
                        <i class="fas fa-fire-flame-curved text-xs"></i> Borrar Todo (Cascada)
                    </button>
                    <button type="button" onclick="aplicarPreset('desmarcar')" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-[10px] font-black uppercase tracking-wider rounded-xl border border-slate-200 active:scale-[0.98] transition-all">
                        Desmarcar
                    </button>
                </div>
            </div>

            <!-- Aviso dinámico si se marca borrar la competición entera -->
            <div id="warning-competicion" class="hidden p-3.5 bg-red-50 border border-red-200 rounded-2xl text-red-800 text-xs font-bold flex items-center gap-3 animate-pulse">
                <i class="fas fa-triangle-exclamation text-lg text-red-500 shrink-0"></i>
                <div class="leading-tight">
                    <p class="font-black text-red-900 uppercase tracking-wide text-[10px]">¡Atención! Cascada Total Activada</p>
                    <p class="text-[11px] font-normal text-red-700">Se eliminará la ficha de la competición del calendario y todas sus fases técnicas.</p>
                </div>
            </div>

            <!-- Cuadrícula Modular de Selección -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5 max-h-80 overflow-y-auto custom-scrollbar pr-1" id="modulos-grid">`;

        const colorMap = {
            indigo: { bg: 'bg-indigo-50', text: 'text-indigo-600', border: 'border-indigo-100', badge: 'bg-indigo-100 text-indigo-700' },
            blue: { bg: 'bg-blue-50', text: 'text-blue-600', border: 'border-blue-100', badge: 'bg-blue-100 text-blue-700' },
            pink: { bg: 'bg-pink-50', text: 'text-pink-600', border: 'border-pink-100', badge: 'bg-pink-100 text-pink-700' },
            amber: { bg: 'bg-amber-50', text: 'text-amber-600', border: 'border-amber-100', badge: 'bg-amber-100 text-amber-700' },
            purple: { bg: 'bg-purple-50', text: 'text-purple-600', border: 'border-purple-100', badge: 'bg-purple-100 text-purple-700' },
            emerald: { bg: 'bg-emerald-50', text: 'text-emerald-600', border: 'border-emerald-100', badge: 'bg-emerald-100 text-emerald-700' },
            red: { bg: 'bg-red-50', text: 'text-red-600', border: 'border-red-100', badge: 'bg-red-100 text-red-700' },
            slate: { bg: 'bg-slate-100', text: 'text-slate-800', border: 'border-slate-300', badge: 'bg-slate-800 text-white' }
        };

        const modulos = data.modulos || {};
        for (const [key, m] of Object.entries(modulos)) {
            const colors = colorMap[m.color] || colorMap.slate;
            const isZero = (m.total === 0);
            const defaultChecked = (!isZero && key !== 'competicion');

            html += `
            <label class="modulo-card flex items-start gap-3 p-3 bg-white rounded-2xl border-2 border-slate-100 hover:border-slate-300 transition-all cursor-pointer relative group ${m.is_competicion ? 'border-dashed border-red-300 bg-red-50/20' : ''}" data-key="${key}" onclick="toggleModuloCard(event, '${key}')">
                <input type="checkbox" name="modulos[]" value="${key}" class="modulo-cb mt-1 w-4 h-4 rounded border-slate-300 text-slate-900 focus:ring-slate-800 cursor-pointer" ${defaultChecked ? 'checked' : ''} onchange="actualizarResumenModulos()">
                
                <div class="w-8 h-8 rounded-xl ${colors.bg} ${colors.text} flex items-center justify-center shrink-0 border ${colors.border} shadow-sm text-xs mt-0.5">
                    <i class="${m.icono}"></i>
                </div>
                
                <div class="flex-1 min-w-0 pr-1">
                    <div class="flex items-center justify-between gap-1 mb-0.5">
                        <p class="text-[11px] font-black text-slate-800 truncate">${m.titulo}</p>
                    </div>
                    <p class="text-[9.5px] font-medium text-slate-400 leading-tight mb-2">${m.descripcion}</p>
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[9px] font-black px-2 py-0.5 rounded-lg ${colors.badge} tabular-nums uppercase">${m.badge}</span>
                        <span class="text-[8.5px] font-bold text-slate-400 tabular-nums italic">${m.detalle}</span>
                    </div>
                </div>
            </label>`;
        }

        html += `
            </div>

            <!-- Barra de Impacto y Resumen -->
            <div class="p-3.5 bg-slate-900 text-white rounded-2xl flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 shadow-md">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center text-white"><i class="fas fa-chart-pie text-sm"></i></div>
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-wider text-slate-400 leading-none mb-1">Módulos Seleccionados</p>
                        <p class="text-sm font-black tabular-nums"><span id="summary-mod-count">0</span> de 8 elegidos</p>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-4 border-t sm:border-t-0 pt-2 sm:pt-0 border-white/10 text-right">
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-wider text-slate-400 leading-none mb-1">Registros / Archivos</p>
                        <p class="text-xs font-black text-blue-300 tabular-nums" id="summary-total-count">0</p>
                    </div>
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-wider text-slate-400 leading-none mb-1">Espacio Disco</p>
                        <p class="text-xs font-black text-emerald-400 tabular-nums" id="summary-disk-space">0 B</p>
                    </div>
                </div>
            </div>

        </div>`;

        Swal.fire({
            title: 'Configurar Limpieza',
            html: html,
            showCancelButton: true,
            confirmButtonText: '¡EJECUTAR LIMPIEZA SELECCIONADA!',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#ef4444',
            width: window.innerWidth < 640 ? '95%' : '760px',
            didOpen: () => {
                actualizarResumenModulos();
            },
            preConfirm: () => {
                const selected = Array.from(document.querySelectorAll('input[name="modulos[]"]:checked')).map(cb => cb.value);
                if (selected.length === 0) {
                    Swal.showValidationMessage('Debes seleccionar al menos un módulo para limpiar');
                    return false;
                }
                return selected;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const modulosSeleccionados = result.value;
                const incluyeCompeticion = modulosSeleccionados.includes('competicion');

                let confirmTitle = '¿Confirmar Limpieza?';
                let confirmText = `Se purgarán los ${modulosSeleccionados.length} módulos seleccionados.`;
                let confirmBtn = '¡Sí, proceder!';

                if (incluyeCompeticion) {
                    confirmTitle = '⚠️ ¡ELIMINACIÓN TOTAL!';
                    confirmText = 'Has marcado borrar la COMPETICIÓN COMPLETA. Se eliminarán sus fases técnicas y la ficha del calendario de forma irreversible.';
                    confirmBtn = '¡SÍ, BORRAR TODO DEFINITIVAMENTE!';
                }

                Swal.fire({
                    title: confirmTitle,
                    text: confirmText,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: confirmBtn,
                    cancelButtonText: 'Abortar',
                    confirmButtonColor: '#ef4444'
                }).then((confirmRes) => {
                    if (confirmRes.isConfirmed) {
                        ejecutarBorrado(ids, modulosSeleccionados);
                    }
                });
            }
        });
    }

    function toggleModuloCard(event, key) {
        if (event.target.tagName.toLowerCase() === 'input') return;
        const card = document.querySelector(`.modulo-card[data-key="${key}"]`);
        if (card) {
            const cb = card.querySelector('input[type="checkbox"]');
            if (cb) {
                cb.checked = !cb.checked;
                actualizarResumenModulos();
            }
        }
    }

    function aplicarPreset(preset) {
        const cbs = document.querySelectorAll('input[name="modulos[]"]');
        cbs.forEach(cb => {
            if (preset === 'desmarcar') {
                cb.checked = false;
            } else if (preset === 'todo') {
                cb.checked = true;
            } else if (preset === 'pruebas') {
                // Solo datos de prueba: conserva competición y fases
                if (cb.value === 'competicion' || cb.value === 'paneles') {
                    cb.checked = false;
                } else {
                    cb.checked = true;
                }
            }
        });
        actualizarResumenModulos();
    }

    function actualizarResumenModulos() {
        if (!currentSimData || !currentSimData.modulos) return;

        const checkedCbs = Array.from(document.querySelectorAll('input[name="modulos[]"]:checked'));
        const modCountEl = document.getElementById('summary-mod-count');
        const totalCountEl = document.getElementById('summary-total-count');
        const diskSpaceEl = document.getElementById('summary-disk-space');
        const warningComp = document.getElementById('warning-competicion');

        let totalRegistros = 0;
        let totalBytesDisco = 0;
        let incluyeCompeticion = false;

        checkedCbs.forEach(cb => {
            const k = cb.value;
            const m = currentSimData.modulos[k];
            if (m) {
                totalRegistros += (m.total || 0);
                totalBytesDisco += (m.bytes_disco || 0);
            }
            if (k === 'competicion') incluyeCompeticion = true;
        });

        if (modCountEl) modCountEl.textContent = checkedCbs.length;
        if (totalCountEl) totalCountEl.textContent = totalRegistros.toLocaleString();
        
        function formatBytes(bytes, precision = 2) {
            if (!bytes || bytes <= 0) return '0 B';
            const units = ['B', 'KB', 'MB', 'GB'];
            const pow = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
            return (bytes / Math.pow(1024, pow)).toFixed(precision) + ' ' + units[pow];
        }

        if (diskSpaceEl) diskSpaceEl.textContent = formatBytes(totalBytesDisco);

        if (warningComp) {
            if (incluyeCompeticion) {
                warningComp.classList.remove('hidden');
            } else {
                warningComp.classList.add('hidden');
            }
        }

        // Resaltar visualmente las tarjetas marcadas
        document.querySelectorAll('.modulo-card').forEach(card => {
            const cb = card.querySelector('input[type="checkbox"]');
            if (cb && cb.checked) {
                card.classList.add('border-slate-800', 'shadow-sm');
                card.classList.remove('border-slate-100');
            } else {
                card.classList.remove('border-slate-800', 'shadow-sm');
                card.classList.add('border-slate-100');
            }
        });
    }

    function ejecutarBorrado(ids, modulos) {
        const formData = new FormData();
        formData.append('action', 'ejecutar_cascada');
        formData.append('ids', JSON.stringify(ids));
        formData.append('modulos', JSON.stringify(modulos));

        Swal.fire({
            title: 'Ejecutando limpieza...',
            html: '<p class="text-xs text-slate-500 font-medium">Purgando los módulos seleccionados. Por favor, no cierres la ventana.</p>',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        fetch('mantenimiento_code.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                let detallesHtml = '';
                if (data.detalles && data.detalles.length > 0) {
                    detallesHtml = '<div class="text-left font-lexend mt-3 p-3 bg-slate-50 rounded-xl border border-slate-100 text-xs space-y-1.5">';
                    data.detalles.forEach(d => {
                        detallesHtml += `<p class="flex items-center gap-2 text-slate-700 font-medium"><i class="fas fa-check text-emerald-500 text-[10px]"></i> ${d}</p>`;
                    });
                    detallesHtml += '</div>';
                }

                Swal.fire({
                    title: '¡Limpieza Completada!',
                    html: `
                    <div class="font-lexend">
                        <p class="text-xs text-slate-600">Se han eliminado / reseteado con éxito <b>${data.total_borrado}</b> registros y archivos.</p>
                        ${detallesHtml}
                    </div>`,
                    icon: 'success',
                    confirmButtonText: 'Aceptar',
                    confirmButtonColor: '#0f172a'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', data.message || 'Fallo al ejecutar la limpieza.', 'error');
            }
        })
        .catch(err => {
            Swal.fire('Error', 'Fallo de conexión al procesar el borrado.', 'error');
        });
    }

    function analizar(tipo) {
        const formData = new FormData();
        formData.append('action', 'analisis_especifico');
        formData.append('tipo', tipo);

        fetch('mantenimiento_code.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                mostrarResultado(tipo, data);
            }
        });
    }

    function mostrarResultado(tipo, data) {
        let html = '<div class="text-left space-y-4 font-lexend">';
        
        if (data.registros.length === 0) {
            html += '<p class="p-8 text-center text-slate-400 italic font-bold uppercase tracking-widest">No se han detectado inconsistencias</p>';
        } else {
            html += `<div class="flex justify-between items-center mb-4">
                        <p class="text-xs font-bold text-slate-500">${data.mensaje}</p>
                        ${tipo === 'archivos_musica_huerfanos' ? `<button onclick="borrarTodosHuerfanos()" class="px-3 py-1.5 bg-red-50 text-red-600 text-[9px] font-black uppercase tracking-widest rounded-lg border border-red-100 hover:bg-red-600 hover:text-white transition-all">Borrar Todos</button>` : ''}
                     </div>`;
            html += '<div class="max-h-96 overflow-y-auto pr-2 space-y-2 custom-scrollbar">';
            data.registros.forEach(r => {
                html += `<div class="p-3 bg-slate-50 rounded-xl border border-slate-100 flex justify-between items-center group" id="huerfano-${r.id.replace('/', '-')}">
                            <div>
                                <p class="text-[11px] font-black text-slate-700 uppercase leading-tight">${r.nombre}</p>
                                <p class="text-[9px] font-bold text-slate-400 uppercase italic">${r.detalle}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-[10px] font-black text-blue-600 bg-blue-50 px-2 py-1 rounded-lg">ID: #${r.id}</span>
                                ${tipo === 'archivos_musica_huerfanos' ? `<button onclick="borrarHuerfano('${r.id}')" class="w-8 h-8 rounded-lg bg-white border border-slate-200 text-red-400 flex items-center justify-center hover:bg-red-50 hover:text-red-600 transition-all opacity-0 group-hover:opacity-100" title="Borrar Archivo"><i class="fas fa-trash-can text-xs"></i></button>` : ''}
                            </div>
                         </div>`;
            });
            html += '</div>';
        }
        html += '</div>';

        Swal.fire({
            title: data.titulo,
            html: html,
            icon: data.registros.length > 0 ? 'warning' : 'success',
            confirmButtonText: 'Cerrar',
            confirmButtonColor: '#1e293b',
            width: window.innerWidth < 640 ? '95%' : '650px'
        });
    }

    function borrarHuerfano(id) {
        const formData = new FormData();
        formData.append('action', 'borrar_archivo_huerfano');
        formData.append('id', id);

        const rowId = 'huerfano-' + id.replace('/', '-');
        const row = document.getElementById(rowId);
        if(row) row.style.opacity = '0.5';

        fetch('mantenimiento_code.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                if(row) row.remove();
                if(data.carpeta_borrada) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Archivo y carpeta eliminados',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            } else {
                if(row) row.style.opacity = '1';
                alert(data.message);
            }
        });
    }

    async function borrarTodosHuerfanos() {
        const buttons = document.querySelectorAll('[onclick^="borrarHuerfano("]');
        if (buttons.length === 0) return;

        if(!confirm(`¿Estás seguro de que quieres borrar los ${buttons.length} archivos detectados?`)) return;

        Swal.fire({
            title: 'Limpieza masiva...',
            html: 'Borrando archivos uno a uno...',
            didOpen: () => { Swal.showLoading(); }
        });

        for (let btn of buttons) {
            const id = btn.getAttribute('onclick').match(/'([^']+)'/)[1];
            const formData = new FormData();
            formData.append('action', 'borrar_archivo_huerfano');
            formData.append('id', id);

            await fetch('mantenimiento_code.php', { method: 'POST', body: formData });
        }

        Swal.fire('Completado', 'Se han procesado todos los archivos.', 'success').then(() => {
            location.reload();
        });
    }

    // Mantener la función de reparación de notas heredada
    function simularReparacion() { analizar('notas_huerfanas'); }
</script>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
